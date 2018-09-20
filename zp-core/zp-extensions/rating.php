<?php
/**
 * Supports a rating system for images, albums, pages, and news articles
 * using the <i>Star Rating</i> Plugin by {@link http://www.fyneworks.com/jquery/star-rating/ fyneworks.com}
 *
 * An option exists to allow viewers to recast their votes. If not set, a viewer may
 * vote only one time and may not change his mind.
 *
 * Customize the stars by placing a modified copy of <var>jquery.rating.css</var> in your theme folder.
 *
 * <b>Legal note:</b> Use the <i>Disguise IP</i> option if your country considers IP tracking a privacy violation.
 *
 * @author Stephen Billard (sbillard)and Malte Müller (acrylian)
 * @package plugins/rating
 * @pluginCategory theme
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("Adds several theme functions to enable images, album, news, or pages to be rated by users. ");

$option_interface = 'jquery_rating';

zp_register_filter('edit_album_utilities', 'jquery_rating::optionVoteStatus');
zp_register_filter('save_album_utilities_data', 'jquery_rating::optionVoteStatusSave');
zp_register_filter('admin_utilities_buttons', 'jquery_rating::rating_purgebutton');

if (!defined('OFFSET_PATH')) {
	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)) . '/functions.php');

	if (isset($_GET['action']) && $_GET['action'] == 'clear_rating') {
		if (!zp_loggedin(ADMIN_RIGHTS)) {
// prevent nefarious access to this page.
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL());
			exitZP();
		}

		require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
		if (session_id() == '') {
// force session cookie to be secure when in https
			if (secureServer()) {
				$CookieInfo = session_get_cookie_params();
				session_set_cookie_params($CookieInfo['lifetime'], $CookieInfo['path'], $CookieInfo['domain'], TRUE);
			}
			zp_session_start();
		}
		XSRFdefender('clear_rating');
		query('UPDATE ' . prefix('images') . ' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
		query('UPDATE ' . prefix('albums') . ' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
		query('UPDATE ' . prefix('news') . ' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
		query('UPDATE ' . prefix('pages') . ' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=' . gettext('All ratings have been set to <em>unrated</em>.'));
		exitZP();
	}
}

if (getOption('rating_image_individual_control')) {
	zp_register_filter('edit_image_utilities', 'jquery_rating::optionVoteStatus');
	zp_register_filter('save_image_utilities_data', 'jquery_rating::optionVoteStatusSave');
}

// register the scripts needed
if (in_context(ZP_INDEX)) {
	zp_register_filter('theme_head', 'jquery_rating::ratingJS');
}

/**
 * Option handler class
 *
 */
class jquery_rating {

	var $ratingstate;

	/**
	 * class instantiation function
	 *
	 * @return jquery_rating
	 */
	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('rating_recast', 1);
			setOptionDefault('rating_stars_count', 5);
			setOptionDefault('rating_split_stars', 2);
			setOptionDefault('rating_star_size', 24);
			setOptionDefault('rating_status', 3);
			setOptionDefault('rating_image_individual_control', 0);
			setOptionDefault('rating_like-dislike', 0);
		}
		$this->ratingstate = array(gettext('open') => 3, gettext('members &amp; guests') => 2, gettext('members only') => 1, gettext('closed') => 0);
	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$stars = ceil(getOption('rating_stars_count'));
		return array(gettext('Voting state') => array('key' => 'rating_status', 'type' => OPTION_TYPE_RADIO,
						'order' => 7,
						'buttons' => $this->ratingstate,
						'desc' => gettext('<em>Enable</em> state of voting.')),
				gettext('Stars') => array('key' => 'rating_stars_count', 'type' => OPTION_TYPE_NUMBER,
						'order' => 6,
						'desc' => sprintf(ngettext('Rating will use %u star.', 'Rating will use %u stars.', $stars), $stars)),
				gettext('Split stars') => array('key' => 'rating_split_stars', 'type' => OPTION_TYPE_RADIO,
						'order' => 4,
						'buttons' => array(gettext('full') => 1, gettext('half') => 2, gettext('third') => 3),
						'desc' => gettext('Show fractional stars based on rating. May cause performance problems for pages with large numbers of rating elements.')),
				gettext('Icon size') => array('key' => 'rating_star_size', 'type' => OPTION_TYPE_RADIO,
						'order' => 4.5,
						'buttons' => array(gettext('large') => 32, gettext('medium') => 24, gettext('small') => 16),
						'desc' => gettext('The size of the icon used for voting.')),
				gettext('Individual image control') => array('key' => 'rating_image_individual_control', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Enable to allow voting status control on individual images.')),
				gettext('Recast vote') => array('key' => 'rating_recast', 'type' => OPTION_TYPE_RADIO,
						'order' => 3,
						'buttons' => array(gettext('No') => 0, gettext('Show rating') => 1, gettext('Show previous vote') => 2),
						'desc' => gettext('Allow users to change their vote. If Show previous vote is chosen, the stars will reflect the last vote of the viewer. Otherwise they will reflect the current rating.')),
				gettext('Like/Dislike') => array('key' => 'rating_like', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 3.5,
						'desc' => gettext('Use like/dislike rather than stars.')),
				gettext('Allow zero') => array('key' => 'rating_zero_ok', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 5,
						'desc' => gettext('Allows rating to be zero.')),
				'' => array('key' => 'rating_js', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 9999,
						'desc' => '')
		);
	}

	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'rating_like':
				?>
				<input type="checkbox" id="__rating_like" name="rating_like-dislike" value="1"<?php if (getOption('rating_like-dislike')) echo ' checked="checked"'; ?> onclick="ratinglikebox();"/>
				<?php
				break;
			case 'rating_js':
				?>
				<script type="text/javascript">
					function ratinglikebox() {
						if ($('#__rating_like').prop('checked')) {
							$('#__rating_split_stars-1').prop('checked', 'checked');
							$('#__rating_split_stars-3').prop('disabled', true);
							$('#__rating_split_stars-2').prop('disabled', true);
							$('#__rating_split_stars-1').prop('disabled', true);
							$('#__rating_zero_ok').prop('checked', 'checked');
							$('#__rating_zero_ok').prop('disabled', true);
							$('#__rating_stars_count').val(1);
							$('#__rating_stars_count').prop('disabled', true);
						} else {
							$('#__rating_split_stars-1').prop('disabled', false);
							$('#__rating_split_stars-2').prop('disabled', false);
							$('#__rating_split_stars-3').prop('disabled', false);
							$('#__rating_zero_ok').prop('disabled', false);
							$('#__rating_stars_count').prop('disabled', false);
						}
					}

					ratinglikebox();

				</script>
				<?php
				break;
		}
	}

	function handleOptionSave($themename, $themealbum) {
		if (isset($_POST['rating_like-dislike'])) {
			setOption('rating_like-dislike', 1);
			setOption('rating_stars_count', 1);
			setOption('rating_split_stars', 1);
			setOption('rating_zero_ok', 1);
		} else {
			setOption('rating_like-dislike', 0);
		}
		return false;
	}

	static function ratingJS() {
		$ME = substr(basename(__FILE__), 0, -4);
		?>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $ME; ?>/jquery.MetaData.js"></script>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $ME; ?>/jquery.rating.js"></script>
		<?php
		$size = getOption('rating_star_size');
		if (getOption('rating_like-dislike')) {
			$css = getPlugin('rating/jquery.rating_like-' . $size . '.css', true, true);
		} else {
			$css = getPlugin('rating/jquery.rating-' . $size . '.css', true, true);
		}
		?>
		<link rel="stylesheet" href="<?php echo pathurlencode($css); ?>" type="text/css" />
		<?php
		?>

		<script type="text/javascript">
					// <!-- <![CDATA[
					$.fn.rating.options = {cancel: '<?php echo gettext('retract'); ?>', starWidth: <?php echo $size; ?>};
					// ]]> -->
		</script>
		<?php
	}

	/**
	 * Option filter handler for images and albums
	 *
	 * @param string $prior HTML from prior filters
	 * @param object $object object being rated
	 * @param string $prefix indicator if admin is processing multiple objects
	 * @return string Combined HTML
	 */
	static function optionVoteStatus($prior, $object, $prefix) {
		$me = new jquery_rating();
		$currentvalue = $object->get('rating_status');
		$output = gettext('Vote Status:') . '<br />' . "\n";
		foreach ($me->ratingstate as $text => $value) {
			if ($value == $currentvalue) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			$output .= "<label class='checkboxlabel'>\n<input type='radio' name='rating_status" . $prefix . "' id='rating_status" . $value . "-" . $prefix . "' value='" . ($value + 1) . "' " . $checked . "/> " . $text . "\n</label>" . "\n";
		}
		$output = $prior . "\n" . $output . '<br /clear="all">';
		return $output;
	}

	/**
	 * Option save handler for the filter
	 *
	 * @param object $object object being rated
	 * @param string $prefix indicator if admin is processing multiple objects
	 */
	static function optionVoteStatusSave($object, $prefix) {
		if (isset($_POST['rating_status' . $prefix])) {
			$object->set('rating_status', sanitize_numeric($_POST['rating_status' . $prefix]) - 1);
		}
		return $object;
	}

	static function rating_purgebutton($buttons) {
		$buttons[] = array(
				'category' => gettext('Database'),
				'enable' => true,
				'button_text' => gettext('Reset all ratings'),
				'formname' => 'clearrating_button',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rating.php?action=clear_rating',
				'icon' => RECYCLE_ICON,
				'title' => gettext('Sets all ratings to unrated.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="clear_rating" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'clear_rating'
		);
		return $buttons;
	}

	/**
	 * Returns the last vote rating from an IP or false if
	 * no vote on record
	 *
	 * @param string $ip
	 * @param array $usedips
	 * @param float $ratingAverage
	 * @return float
	 */
	static function getRatingByIP($ip, $usedips, $ratingAverage) {
		global $_rating_current_IPlist;
		$rating = 0;
		if (empty($_rating_current_IPlist)) {
			if (!empty($usedips)) {
				$_rating_current_IPlist = getSerializedArray($usedips);
				if (array_key_exists($ip, $_rating_current_IPlist)) {
					return $_rating_current_IPlist[$ip];
				}
			}
		}
		return false;
	}

	/**
	 * returns the $object for the current loaded page
	 *
	 * @param object $object
	 * @return object
	 */
	static function getCurrentPageObject() {
		global $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_article, $_zp_current_page;
		switch ($_zp_gallery_page) {
			case 'album.php':
				return $_zp_current_album;
			case 'image.php':
				return $_zp_current_image;
			case 'news.php':
				return $_zp_current_article;
			case 'pages.php':
				return $_zp_current_page;
			default:
				return NULL;
		}
	}

}

/**
 * Prints the rating star form and the current rating
 * Insert this function call in the page script where you
 * want the star ratings to appear.
 *
 * NOTE:
 * If $vote is false or the rating_recast option is false then
 * the stars shown will be the rating. Otherwise the stars will
 * show the value of the viewer's last vote.
 *
 * @param bool $vote set to false to disable voting
 * @param object $object optional object for the ratings target. If not set, the current page object is used
 * @param bool $text if false, no annotation text is displayed
 */
function printRating($vote = 3, $object = NULL, $text = true) {
	global $_zp_gallery_page;
	if (is_null($object)) {
		$object = jquery_rating::getCurrentPageObject();
	}
	if (!is_object($object)) {
		return;
	}
	$table = $object->table;
	$vote = min($vote, getOption('rating_status'), $object->get('rating_status'));
	switch ($vote) {
		case 1: // members only
			if (!zp_loggedin()) {
				$vote = 0;
			}
			break;
		case 2: // members & guests
			switch ($_zp_gallery_page) {
				case 'album.php':
					$album = $object;
					$hint = '';
					if (!(zp_loggedin() || checkAlbumPassword($album->name))) {
						$vote = 0;
					}
					break;
				case 'pages.php':
				case 'news.php':
					if (!zp_loggedin()) { // no guest password
						$vote = 0;
					}
					break;
				default:
					$album = $object->getAlbum();
					$hint = '';
					if (!(zp_loggedin() || checkAlbumPassword($album->name))) {
						$vote = 0;
					}
					break;
			}
	}

	$stars = ceil(getOption('rating_stars_count'));
	$recast = getOption('rating_recast');
	$split_stars = max(1, getOption('rating_split_stars'));
	$rating = $object->get('rating');
	$votes = $object->get('total_votes');
	$id = $object->getID();
	$unique = '_' . $table . '_' . $id;

	$ip = getUserID();
	$oldrating = jquery_rating::getRatingByIP($ip, $object->get('used_ips'), $object->get('rating'));
	if ($vote && $recast == 2 && $oldrating) {
		$starselector = round($oldrating * $split_stars);
	} else {
		$starselector = round($rating * $split_stars);
	}
	$disable = !$vote || ($oldrating && !$recast);
	if ($rating > 0) {
		$msg = sprintf(ngettext('Rating %2$.1f (%1$u vote)', 'Rating %2$.1f (%1$u votes)', $votes), $votes, $rating);
	} else {
		$msg = gettext('Not yet rated');
	}
	if ($split_stars > 1) {
		$step = $split_stars;
		$split = " {split:$step}";
		$step = 1 / $step;
	} else {
		$split = '';
		$step = 1;
	}
	$like = getOption('rating_like-dislike');
	?>
	<form name="star_rating<?php echo $unique; ?>" id="star_rating<?php echo $unique; ?>" action="submit">
		<?php
		$j = 0;
		for ($i = $step; $i <= $stars; $i = $i + $step) {
			$v = ceil($i);
			$j++;
			?>
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="<?php echo $j; ?>" title="<?php
		if ($like) {
			echo gettext('like');
		} else {
			printf(ngettext('%u star', '%u stars', $v), $v);
		}
			?>" />
						 <?php
					 }
					 if (!$disable) {
						 ?>
			<span id="submit_button<?php echo $unique; ?>">
				<input type="button" class="rating_button" value="<?php echo gettext('Submit »'); ?>" onclick="cast<?php echo $unique; ?>();" />
				<br class="clearall">
			</span>
			<?php
		}
		?>
	</form>
	<span class="clearall" ></span>
	<span class="vote" id="vote<?php echo $unique; ?>" <?php if (!$text) echo 'style="display:none;"'; ?>>
		<?php echo $msg; ?>
	</span>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var recast<?php echo $unique; ?> = <?php printf('%u', $recast && $oldrating); ?>;
		window.addEventListener('load', function () {
			$('#star_rating<?php echo $unique; ?> :radio.star').rating('select', '<?php echo $starselector; ?>');
	<?php
	if ($disable) {
		?>
				$('#star_rating<?php echo $unique; ?> :radio.star').rating('disable');
		<?php
	}
	?>
		}, false);

		function cast<?php echo $unique; ?>() {
			var dataString = $('#star_rating<?php echo $unique; ?>').serialize();
			if (!dataString && <?php echo getOption('rating_like-dislike') ? 'TRUE' : 'FALSE'; ?>) {
				dataString = 'star_rating-value<?php echo $unique; ?>=0';
			}

			if (dataString || recast<?php echo $unique; ?>) {
	<?php
	if ($recast) {
		?>
					if (!dataString) {
						dataString = 'star_rating-value<?php echo $unique; ?>=0';
					}
		<?php
	} else {
		?>
					$('#star_rating<?php echo $unique; ?> :radio.star').rating('disable');
					$('#submit_button<?php echo $unique; ?>').hide();
		<?php
	}
	?>
				$.ajax({
					type: 'POST',
					cache: false,
					url: '<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . substr(basename(__FILE__), 0, -4); ?>/update.php',
					data: dataString + '&id=<?php echo $id; ?>&table=<?php echo $table; ?>'
				});
				recast<?php echo $unique; ?> = <?php printf('%u', $recast); ?>;
				$('#vote<?php echo $unique; ?>').html('<?php echo gettext('Vote Submitted'); ?>');
			} else {
				$('#vote<?php echo $unique; ?>').html('<?php echo gettext('nothing to submit'); ?>');
			}
		}
		// ]]> -->
	</script>
	<?php
}

/**
 * Returns the current rating of an object
 *
 * @param object $object optional ratings target. If not supplied, the current script object is used
 * @return float
 */
function getRating($object = NULL) {
	if (is_null($object)) {
		$object = jquery_rating::getCurrentPageObject();
		if (!$object)
			return NULL;
	}
	return $object->get('rating');
}
?>