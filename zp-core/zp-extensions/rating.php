<?php
/**
 * Supports an rating system for images, albums, pages, and news articles
 *
 * uses Star Rating Plugin by Fyneworks.com
 *
 * An option exists to allow viewers to recast their votes. If not set, a viewer may
 * vote only one time and not change his mind.
 *
 * Customize the stars by placing a modified copy of jquery.rating.css in your theme folder
 *
 * @author Stephen Billard (sbillard)and Malte Müller (acrylian)
 * @package plugins
 */
if (!defined('OFFSET_PATH')) define('OFFSET_PATH', 3);
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Adds several theme functions to enable images, album, news, or pages to be rated by users. <p class='notebox'><strong>Legal note:</strong> Use the <em>Disguise IP</em> option if your country considers IP tracking a privacy violation.</p>");
$plugin_author = "Stephen Billard (sbillard) and Malte Müller (acrylian)";
$plugin_version = '1.4.1';

require_once(dirname(dirname(__FILE__)).'/functions.php');
if (isset($_GET['action']) && $_GET['action']=='clear_rating') {
	if (!(zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))) { // prevent nefarious access to this page.
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL(__FILE__));
		exit();
	}

	require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
	if (session_id() == '') {
		// force session cookie to be secure when in https
		if(secureServer()) {
			$CookieInfo=session_get_cookie_params();
			session_set_cookie_params($CookieInfo['lifetime'],$CookieInfo['path'], $CookieInfo['domain'],TRUE);
		}
		session_start();
	}
	XSRFdefender('clear_rating');
	query('UPDATE '.prefix('images').' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
	query('UPDATE '.prefix('albums').' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
	query('UPDATE '.prefix('news').' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
	query('UPDATE '.prefix('pages').' SET total_value=0, total_votes=0, rating=0, used_ips="" ');
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg='.gettext('All ratings have been set to <em>unrated</em>.'));
	exit();
}

$option_interface = 'jquery_rating';

zp_register_filter('edit_album_utilities', 'optionVoteStatus');
zp_register_filter('save_album_utilities_data', 'optionVoteStatusSave');
zp_register_filter('admin_utilities_buttons', 'rating_purgebutton');

if (getOption('rating_image_individual_control')) {
	zp_register_filter('edit_image_utilities', 'optionVoteStatus');
	zp_register_filter('save_image_utilities_data', 'optionVoteStatusSave');
}

// register the scripts needed
if (in_context(ZP_INDEX)) {
	zp_register_filter('theme_head','ratingJS');
}
function ratingJS() {
	$ME = substr(basename(__FILE__),0,-4);
	?>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.$ME; ?>/jquery.MetaData.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.$ME; ?>/jquery.rating.js"></script>
	<?php
	$css = getPlugin('rating/jquery.rating.css', true, true);
	?>
	<link rel="stylesheet" href="<?php echo pathurlencode($css); ?>" type="text/css" />
	<script type="text/javascript">
		// <!-- <![CDATA[
		$.fn.rating.options = { cancel: '<?php echo gettext('retract'); ?>'	};
		// ]]> -->
	</script>
	<?php
}

require_once(substr(basename(__FILE__),0,-4).'/functions-rating.php');

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
	function jquery_rating() {
		setOptionDefault('rating_recast', 1);
		setOptionDefault('rating_split_stars', 1);
		setOptionDefault('rating_status', 3);
		setOptionDefault('rating_image_individual_control', 0);
		$this->ratingstate = array(gettext('open') => 3, gettext('members &amp; guests') => 2, gettext('members only') => 1, gettext('closed') => 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Voting state') => array('key' => 'rating_status', 'type' => OPTION_TYPE_RADIO,
										'buttons' => $this->ratingstate,
										'desc' => gettext('<em>Enable</em> state of voting.')),
									gettext('Split stars') =>array('key' => 'rating_split_stars', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Enable to allow rating stars to show half stars for fractional rating values. May cause performance problems for pages with large numbers of rating elements.')),
									gettext('Individual image control') =>array('key' => 'rating_image_individual_control', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Enable to allow voting status control on individual images.')),
									gettext('Recast vote') =>array('key' => 'rating_recast', 'type' => OPTION_TYPE_RADIO,
										'buttons' => array(gettext('No') => 0, gettext('Show rating') => 1, gettext('Show previous vote') => 2),
										'desc' => gettext('Allow users to change their vote. If Show previous vote is chosen, the stars will reflect the last vote of the viewer. Otherwise they will reflect the current rating.')),
									gettext('Disguise IP') => array('key'=>'rating_hash_ip', 'type'=>OPTION_TYPE_CHECKBOX,
										'desc'=> gettext('Causes the stored IP addressed to be hashed so as to avoid privacy tracking issues.'))
								);
	}

	function handleOption($option, $currentValue) {
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
function printRating($vote=3, $object=NULL, $text=true) {
	global $_zp_gallery_page;
	if (is_null($object)) {
		$object = getCurrentPageObject();
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

	$rating = $object->get('rating');
	$votes = $object->get('total_votes');
	$id = $object->get('id');
	$unique = '_'.$table.'_'.$id;
	if (getOption('rating_hash_ip')) {
		$ip = sha1(getUserIP());
	} else {
		$ip = getUserIP();
	}
	$recast = getOption('rating_recast');
	$split_stars = getOption('rating_split_stars')+1;
	$oldrating = getRatingByIP($ip,$object->get('used_ips'), $object->get('rating'));
	if ($vote && $recast==2 && $oldrating) {
		$starselector = round($oldrating*$split_stars);
	} else {
		$starselector = round($rating*$split_stars);
	}
	$disable = !$vote || ($oldrating && !$recast);
	if ($rating > 0) {
		$msg = sprintf(ngettext('Rating %2$.1f (%1$u vote)', 'Rating %2$.1f (%1$u votes)', $votes), $votes, $rating);
	} else {
		$msg = gettext('Not yet rated');
	}
	if ($split_stars>1) {
		$split = ' {split:2}';
	} else {
		$split = '';
	}
	?>
		<form name="star_rating<?php echo $unique; ?>" id="star_rating<?php echo $unique; ?>" action="submit">
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="1" title="<?php echo gettext('1 star'); ?>" />
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="2" title="<?php echo gettext('1 star'); ?>" />
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="3" title="<?php echo gettext('2 stars'); ?>" />
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="4" title="<?php echo gettext('2 stars'); ?>" />
			<input type="radio" class="star<?php echo $split; ?>" name="star_rating-value<?php echo $unique; ?>" value="5" title="<?php echo gettext('3 stars'); ?>" />
			<?php
			if ($split_stars>1) {
				?>
				<input type="radio" class="star {split:2}" name="star_rating-value<?php echo $unique; ?>" value="6" title="<?php echo gettext('3 stars'); ?>" />
				<input type="radio" class="star {split:2}" name="star_rating-value<?php echo $unique; ?>" value="7" title="<?php echo gettext('4 stars'); ?>" />
				<input type="radio" class="star {split:2}" name="star_rating-value<?php echo $unique; ?>" value="8" title="<?php echo gettext('4 stars'); ?>" />
				<input type="radio" class="star {split:2}" name="star_rating-value<?php echo $unique; ?>" value="9" title="<?php echo gettext('5 stars'); ?>" />
				<input type="radio" class="star {split:2}" name="star_rating-value<?php echo $unique; ?>" value="10" title="<?php echo gettext('5 stars'); ?>" />
				<?php
			}
			if (!$disable) {
				?>
				<span id="submit_button<?php echo $unique; ?>">
					<input type="button" value="<?php echo gettext('Submit &raquo;'); ?>" onclick="javascript:cast<?php echo $unique; ?>();" />
				</span>
				<?php
			}
			?>
		</form>
		<span style="line-height: 0em;"><br clear="all" /></span>
		<span class="vote" id="vote<?php echo $unique; ?>" <?php if (!$text) echo 'style="display:none;"'; ?>>
			<?php echo $msg; ?>
		</span>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var recast<?php echo $unique; ?> = <?php printf('%u',$recast && $oldrating); ?>;
		$(document).ready(function() {
			$('#star_rating<?php echo $unique; ?> :radio.star').rating('select','<?php echo $starselector; ?>');
			<?php
			if ($disable) {
				?>
				$('#star_rating<?php echo $unique; ?> :radio.star').rating('disable');
				<?php
			}
			?>
		});

		function cast<?php echo $unique; ?>() {
			var dataString = $('#star_rating<?php echo $unique; ?>').serialize();
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
					url: '<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.substr(basename(__FILE__),0,-4); ?>/update.php',
					data: dataString+'&id=<?php echo $id; ?>&table=<?php echo $table; ?>'
				});
				recast<?php echo $unique; ?> = <?php printf('%u',$recast); ?>;
				$('#vote<?php echo $unique; ?>').html('<?php echo gettext('Vote Submitted'); ?>');
			} else {
				$('#vote<?php echo $unique; ?>').html('<?php echo gettext('nothing to submit'); ?>');
			}
		}
		function star_click<?php echo $unique; ?>() {


alert('star click');

			$('#vote<?php echo $unique; ?>').html('<?php echo gettext('Vote pending'); ?>');
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
function getRating($object=NULL) {
	if (is_null($object)) {
		$object = getCurrentPageObject();
		if (!$object) return NULL;
	}
	return $object->get('rating');
}

/**
 * Option filter handler for images and albums
 *
 * @param string $before HTML from prior filters
 * @param object $object object being rated
 * @param string $prefix indicator if admin is processing multiple objects
 * @return string Combined HTML
 */
function optionVoteStatus($before, $object, $prefix) {
	$me = new jquery_rating();
	$currentvalue = $object->get('rating_status');
	$output = gettext('Vote Status:').'<br />'."\n";
	foreach($me->ratingstate as $text=>$value) {
		if($value == $currentvalue) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
		$output .= "<label class='checkboxlabel'>\n<input type='radio' name='rating_status".$prefix."' id='rating_status".$value."-".$prefix."' value='".($value+1)."' ".$checked."/> ".$text."\n</label>"."\n";
	}
	$output = $before.'<hr />'."\n".$output.'<br /clear="all">';
	return $output;
}

/**
 * Option save handler for the filter
 *
 * @param object $object object being rated
 * @param string $prefix indicator if admin is processing multiple objects
 */
function optionVoteStatusSave($object, $prefix) {
	if (isset($_POST['rating_status'.$prefix])) {
		$object->set('rating_status', sanitize_numeric($_POST['rating_status'.$prefix])-1);
	}
	return $object;
}

function rating_purgebutton($buttons) {
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Reset all ratings'),
								'formname'=>'clearrating_button',
								'action'=>PLUGIN_FOLDER.'/rating.php?action=clear_rating',
								'icon'=>'images/reset1.png',
								'title'=>gettext('Sets all ratings to unrated.'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="action" value="clear_rating" />',
								'rights'=> ADMIN_RIGHTS,
								'XSRFTag' => 'clear_rating'
								);
	return $buttons;
}


?>