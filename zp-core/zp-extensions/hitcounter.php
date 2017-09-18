<?php
/**
 * Provides automatic hitcounter counting for gallery objects
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage theme
 */
/** Reset hitcounters ********************************************************** */
/* * ***************************************************************************** */

if (!defined('OFFSET_PATH')) {
	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
	if (isset($_GET['action'])) {
		if (sanitize($_GET['action']) == 'reset_all_hitcounters') {
			if (!zp_loggedin(ADMIN_RIGHTS)) {
				// prevent nefarious access to this page.
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL());
				exitZP();
			}
			zp_session_start();
			XSRFdefender('hitcounter');
			$_zp_gallery->set('hitcounter', 0);
			$_zp_gallery->save();
			query('UPDATE ' . prefix('albums') . ' SET `hitcounter`= 0');
			query('UPDATE ' . prefix('images') . ' SET `hitcounter`= 0');
			query('UPDATE ' . prefix('news') . ' SET `hitcounter`= 0');
			query('UPDATE ' . prefix('pages') . ' SET `hitcounter`= 0');
			query('UPDATE ' . prefix('news_categories') . ' SET `hitcounter`= 0');
			purgeOption('page_hitcounters');
			query("DELETE FROM " . prefix('plugin_storage') . " WHERE `type` = 'hitcounter' AND `subtype`='rss'");
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=' . gettext('All hitcounters have been set to zero.'));
			exitZP();
		}
	}
}

$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN | FEATURE_PLUGIN);
$plugin_description = gettext('Automatically increments hitcounters on gallery objects viewed by a <em>visitor</em>.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'hitcounter';

zp_register_filter('load_theme_script', 'hitcounter::load_script');
zp_register_filter('admin_utilities_buttons', 'hitcounter::button');

$_scriptpage_hitcounters = getSerializedArray(getOption('page_hitcounters'));

/**
 * Plugin option handling class
 *
 */
class hitcounter {

	var $defaultbots = 'Teoma, alexa, froogle, Gigabot,inktomi, looksmart, URL_Spider_SQL, Firefly, NationalDirectory, Ask Jeeves, TECNOSEEK, InfoSeek, WebFindBot, girafabot, crawler, www.galaxy.com, Googlebot, Scooter, Slurp, msnbot, appie, FAST, WebBug, Spade, ZyBorg, rabaz ,Baiduspider, Feedfetcher-Google, TechnoratiSnoop, Rankivabot, Mediapartners-Google, Sogou web spider, WebAlta Crawler';

	function __construct() {
		global $_scriptpage_hitcounters;
		if (OFFSET_PATH == 2) {
			setOptionDefault('hitcounter_ignoreIPList_enable', 0);
			setOptionDefault('hitcounter_ignoreSearchCrawlers_enable', 0);
			setOptionDefault('hitcounter_ignoreIPList', '');
			setOptionDefault('hitcounter_searchCrawlerList', $this->defaultbots);

			$options = getOptionsLike('Page-Hitcounter-');
			foreach ($options as $option => $value) {
				if ($value) {
					$_scriptpage_hitcounters[str_replace('Page-Hitcounter-', '', $option)] = $value;
				}
				purgeOption($option);
			}
			setOptionDefault('page_hitcounters', serialize($_scriptpage_hitcounters));

			$sql = 'UPDATE ' . prefix('plugin_storage') . ' SET `type`="hitcounter",`subtype`="rss" WHERE `type`="rsshitcounter"';
			query($sql);
		}
	}

	function getOptionsSupported() {
		return array(gettext('IP Address list') => array(
						'order' => 1,
						'key' => 'hitcounter_ignoreIPList',
						'type' => OPTION_TYPE_CUSTOM,
						'desc' => gettext('Comma-separated list of IP addresses to ignore.'),
				),
				gettext('Filter') => array(
						'order' => 0,
						'key' => 'hitcounter_ignore',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'checkboxes' => array(gettext('IP addresses') => 'hitcounter_ignoreIPList_enable', gettext('Search Crawlers') => 'hitcounter_ignoreSearchCrawlers_enable'),
						'desc' => gettext('Check to enable. If a filter is enabled, viewers from in its associated list will not count hits.'),
				),
				gettext('Search Crawler list') => array(
						'order' => 2,
						'key' => 'hitcounter_searchCrawlerList',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => false,
						'desc' => gettext('Comma-separated list of search bot user agent names.'),
				),
				' ' => array(
						'order' => 3,
						'key' => 'hitcounter_set_defaults',
						'type' => OPTION_TYPE_CUSTOM,
						'desc' => gettext('Reset options to their default settings.')
				)
		);
	}

	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'hitcounter_set_defaults':
				?>
				<script type="text/javascript">
					// <!-- <![CDATA[
					var reset = "<?php echo $this->defaultbots; ?>";
					function hitcounter_defaults() {
						$('#hitcounter_ignoreIPList').val('');
						$('#hitcounter_ip_button').removeAttr('disabled');
						$('#__hitcounter_ignoreIPList_enable').prop('checked', false);
						$('#__hitcounter_ignoreSearchCrawlers_enable').prop('checked', false);
						$('#__hitcounter_searchCrawlerList').val(reset);
					}
					// ]]> -->
				</script>
				<label><input id="hitcounter_reset_button" type="button" value="<?php echo gettext('Defaults'); ?>" onclick="hitcounter_defaults();" /></label>
				<?php
				break;
			case 'hitcounter_ignoreIPList':
				?>
				<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX; ?>'text-hitcounter_ignoreIPList" value="0" />
				<input type="text" size="30" id="hitcounter_ignoreIPList" name="hitcounter_ignoreIPList" value="<?php echo html_encode($currentValue); ?>" />
				<script type="text/javascript">
					// <!-- <![CDATA[
					function hitcounter_insertIP() {
						if ($('#hitcounter_ignoreIPList').val() == '') {
							$('#hitcounter_ignoreIPList').val('<?php echo getUserIP(); ?>');
						} else {
							$('#hitcounter_ignoreIPList').val($('#hitcounter_ignoreIPList').val() + ',<?php echo getUserIP(); ?>');
						}
						$('#hitcounter_ip_button').attr('disabled', 'disabled');
					}
					jQuery(window).load(function () {
						var current = $('#hitcounter_ignoreIPList').val();
						if (current.indexOf('<?php echo getUserIP(); ?>') < 0) {
							$('#hitcounter_ip_button').removeAttr('disabled');
						}
					});
					// ]]> -->
				</script>
				<label><input id="hitcounter_ip_button" type="button" value="<?php echo gettext('Insert my IP'); ?>" onclick="hitcounter_insertIP();" disabled="disabled" /></label>
				<?php
				break;
		}
	}

	/**
	 *
	 * Counts the hitcounter for the page/object
	 * @param string $script
	 * @param bool $valid will be false if the object is not found (e.g. there will be a 404 error);
	 * @return string
	 */
	static function load_script($script, $valid) {
		if ($script && $valid) {
			if (getOption('hitcounter_ignoreIPList_enable')) {
				$ignoreIPAddressList = explode(',', str_replace(' ', '', getOption('hitcounter_ignoreIPList')));
				$skip = in_array(getUserIP(), $ignoreIPAddressList);
			} else {
				$skip = false;
			}
			if (getOption('hitcounter_ignoreSearchCrawlers_enable') && !$skip && array_key_exists('HTTP_USER_AGENT', $_SERVER) && ($agent = $_SERVER['HTTP_USER_AGENT'])) {
				$botList = explode(',', getOption('hitcounter_searchCrawlerList'));
				foreach ($botList as $bot) {
					if (stripos($agent, trim($bot))) {
						$skip = true;
						break;
					}
				}
			}

			if (!$skip) {
				global $_zp_gallery, $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_article, $_zp_current_page, $_zp_current_category, $_scriptpage_hitcounters;
				if (checkAccess()) {
					// count only if permitted to access
					switch ($_zp_gallery_page) {
						case'index.php':
							if (!zp_loggedin()) {
								$_zp_gallery->countHit();
							}
							break;
						case 'album.php':
							if (!$_zp_current_album->isMyItem(ALBUM_RIGHTS) && getCurrentPage() == 1) {
								$_zp_current_album->countHit();
							}
							break;
						case 'image.php':
							if (!$_zp_current_album->isMyItem(ALBUM_RIGHTS)) {
								//update hit counter
								$_zp_current_image->countHit();
							}
							break;
						case 'pages.php':
							if (class_exists('CMS') && !zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
								$_zp_current_page->countHit();
							}
							break;
						case 'news.php':
							if (class_exists('CMS') && !zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
								if (is_NewsArticle()) {
									$_zp_current_article->countHit();
								} else if (is_NewsCategory()) {
									$_zp_current_category->countHit();
								}
							}
							break;
						default:
							if (!zp_loggedin()) {
								$page = stripSuffix($_zp_gallery_page);
								$_scriptpage_hitcounters[$page] = @$_scriptpage_hitcounters[$page] + 1;
								setOption('page_hitcounters', serialize($_scriptpage_hitcounters));
							}
							break;
					}
				}
			}
		}
		return $script;
	}

	static function button($buttons) {
		$buttons[] = array(
				'XSRFTag' => 'hitcounter',
				'category' => gettext('Database'),
				'enable' => true,
				'button_text' => gettext('Reset all hitcounters'),
				'formname' => 'reset_all_hitcounters.php',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/hitcounter.php?action=reset_all_hitcounters',
				'icon' => RECYCLE_ICON,
				'alt' => '',
				'title' => gettext('Reset all hitcounters to zero'),
				'hidden' => '<input type="hidden" name="action" value="reset_all_hitcounters" />',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

}

/**
 * returns the hitcounter for the current page or for the object passed
 *
 * @param object $obj the album or page object for which the hitcount is desired
 * @return string
 */
function getHitcounter($obj = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery, $_zp_gallery_page, $_zp_current_article, $_zp_current_page, $_zp_current_category, $_scriptpage_hitcounters;
	if (is_null($obj)) {
		switch ($_zp_gallery_page) {
			case'index.php';
				$obj = $_zp_gallery;
				break;
			case 'album.php':
				$obj = $_zp_current_album;
				break;
			case 'image.php':
				$obj = $_zp_current_image;
				break;
			case 'pages.php':
				$obj = $_zp_current_page;
				break;
			case 'news.php':
				if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
					$obj = $_zp_current_category;
				} else {
					$obj = $_zp_current_article;
				}
				if (is_null($obj))
					return 0;
				break;
			default:
				$page = stripSuffix($_zp_gallery_page);
				return @$_scriptpage_hitcounters[$page];
		}
	}
	return $obj->getHitcounter();
}
?>