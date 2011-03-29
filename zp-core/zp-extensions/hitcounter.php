<?php
/**
 * Provides automatic hitcounter counting for Zenphoto objects
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_description = gettext('Automatically increments hitcounters on Zenphoto objects viewed by a "visitor".');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$option_interface = 'hitcounter_options';

zp_register_filter('load_theme_script', 'hitcounter_load_script');

/**
 * Plugin option handling class
 *
 */
class hitcounter_options {

	var $defaultbots = 'Teoma,alexa, froogle, Gigabot,inktomi, looksmart, URL_Spider_SQL,Firefly, NationalDirectory,
											Ask Jeeves,TECNOSEEK, InfoSeek, WebFindBot, girafabot, crawler,www.galaxy.com, Googlebot,
											Scooter, Slurp, msnbot, appie, FAST, WebBug, Spade, ZyBorg, rabaz ,Baiduspider, Feedfetcher-Google,
											TechnoratiSnoop, Rankivabot, Mediapartners-Google, Sogou web spider, WebAlta Crawler';


	function hitcounter_options() {
		$this->defaultbots = str_replace("\n"," ",$this->defaultbots);
		$this->defaultbots = str_replace("\t",'',$this->defaultbots);
		setOptionDefault('hitcounter_ignoreIPList_enable',0);
		setOptionDefault('hitcounter_ignoreSearchCrawlers_enable',0);
		setOptionDefault('hitcounter_ignoreIPList','');
		setOptionDefault('hitcounter_searchCrawlerList', $this->defaultbots);
	}

	function getOptionsSupported() {
		return array(	gettext('IP Address list') => array(
											'order' => 1,
											'key' => 'hitcounter_ignoreIPList',
											'type' => OPTION_TYPE_CUSTOM,
											'desc' => gettext('Comma-separated list of IP addresses to ignore.'),
									),
									gettext('Filter') => array(
											'order' => 0,
											'key' => 'hitcounter_ignore',
											'type' => OPTION_TYPE_CHECKBOX_ARRAY,
											'checkboxes' => array(gettext('IP addresses')  => 'hitcounter_ignoreIPList_enable',gettext('Search Crawlers') => 'hitcounter_ignoreSearchCrawlers_enable'),
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
				<script language="javascript" type="text/javascript">
				// <!-- <![CDATA[
				function hitcounter_defaults() {
					$('#hitcounter_ignoreIPList').val('');
					$('#hitcounter_ip_button').removeAttr('disabled');
					$('#hitcounter_ignoreIPList_enable').removeAttr('checked');
					$('#hitcounter_ignoreSearchCrawlers_enable').removeAttr('checked');
					$('#hitcounter_searchCrawlerList').val('<?php echo $this->defaultbots; ?>');
				}
				// ]]> -->
				</script>
				<label><input id="hitcounter_reset_button" type="button" value="<?php echo gettext('Defaults');?>" onclick="hitcounter_defaults();" /></label>
				<?php
				break;
			case 'hitcounter_ignoreIPList':
				?>
				<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX; ?>'text-hitcounter_ignoreIPList" value="0" />
				<input type="text" size="30" id="hitcounter_ignoreIPList" name="hitcounter_ignoreIPList" value="<?php echo html_encode($currentValue); ?>" />
				<script language="javascript" type="text/javascript">
				// <!-- <![CDATA[
				function hitcounter_insertIP() {
					if ($('#hitcounter_ignoreIPList').val() == '') {
						$('#hitcounter_ignoreIPList').val('<?php echo getUserIP(); ?>');
					} else {
						$('#hitcounter_ignoreIPList').val($('#hitcounter_ignoreIPList').val()+',<?php echo getUserIP(); ?>');
					}
					$('#hitcounter_ip_button').attr('disabled','disabled');
				}
				jQuery(window).load(function(){
					var current = $('#hitcounter_ignoreIPList').val();
					if (current.indexOf('<?php echo getUserIP(); ?>') < 0) {
						$('#hitcounter_ip_button').removeAttr('disabled');
					}
				});
				// ]]> -->
				</script>
				<label><input id="hitcounter_ip_button" type="button" value="<?php echo gettext('Insert my IP');?>" onclick="hitcounter_insertIP();" disabled="disabled" /></label>
				<?php
				break;
		}
	}

}

function hitcounter_load_script($obj) {
	if (getOption('hitcounter_ignoreIPList_enable')) {
		$ignoreIPAddressList = explode(',', str_replace(' ', '', getOption('hitcounter_ignoreIPList')));
		$skip = in_array(getUserIP(), $ignoreIPAddressList);
	} else {
		$skip = false;
	}
	if (getOption('hitcounter_ignoreSearchCrawlers_enable') && !$skip) {
		$botList = explode(',', getOption('hitcounter_searchCrawlerList'));
		foreach($botList as $bot) {
			if(stripos($_SERVER['HTTP_USER_AGENT'], trim($bot))) {
				$skip = true;
				break;
			}
		}
	}

	if (!$skip) {
		global $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
		$hint = $show = false;
		if (checkAccess($hint, $show)) { // count only if permitted to access
			switch ($_zp_gallery_page) {
				case 'album.php':
					if (!$_zp_current_album->isMyItem(ALBUM_RIGHTS) && getCurrentPage() == 1) {
						$_zp_current_album->countHit();
					}
					break;
				case 'image.php':
					if (!$_zp_current_album->isMyItem(ALBUM_RIGHTS)) { //update hit counter
						$_zp_current_image->countHit();
					}
					break;
				case 'pages.php':
					if (!zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
						$_zp_current_zenpage_page->countHit();
					}
					break;
				case 'news.php':
					if (!zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
						if(is_NewsArticle()) {
							$_zp_current_zenpage_news->countHit();
						} else if(is_NewsCategory()) {
							$_zp_current_category->countHit();
						}
					}
					break;
				default:
					if (!zp_loggedin()) {
						$page = stripSuffix($_zp_gallery_page);
						setOption('Page-Hitcounter-'.$page, getOption('Page-Hitcounter-'.$page)+1);
					}
					break;
			}
		}
	}
	return $obj;
}
?>