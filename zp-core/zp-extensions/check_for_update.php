<?php
/**
 * Provides a check for more recent Zenphoto Versions.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage utilities
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Checks if there is a Zenphoto versions that is newer than the installed version.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (!class_exists('DOMDocument')) ? gettext('PHP <em>DOM Object Model</em> is required.') : false;

if (OFFSET_PATH != 2 && !$plugin_disable) {
	$me = explode('?',getRequestURI());
	if (basename(array_shift($me))=='admin.php') {
		$v = getOption('last_update_version');
		$last = getOption('last_update_check');
		if (empty($last) || is_numeric($last)) {
			if (time() > $last+1728000) {
				//	check each 20 days
				$v = checkForUpdate();
				setOption('last_update_check', time());
				setOption('last_update_version', $v);
				if ($v) {
					setOption('last_update_msg','<a href="http://www.zenphoto.org" alt="'.gettext('Zenphoto download page').'">'.gettext("A new version of Zenphoto version is available.").'</a>');
				}
			}
		}
		if ($v) {
			zp_register_filter('admin_note', 'admin_showupdate');
		}
	}
}

/**
 * Searches the zenphoto.org home page for the current zenphoto download
 * locates the version number of the download and compares it to the version
 * we are running.
 *
 * @return string If there is a more current version on the WEB, returns its version number otherwise returns FALSE
 * @since 1.1.3
 */
function checkForUpdate() {
	$webVersion = false;
	if (is_connected() && class_exists('DOMDocument')) {
		require_once(dirname(__FILE__).'/zenphoto_news/rsslib.php');
		$recents = RSS_Retrieve("http://www.zenphoto.org/index.php?rss=news&category=changelog");
		if ($recents) {
			array_shift($recents);
			$article = array_shift($recents);	//	most recent changelog article
			$v = trim(str_replace('zenphoto-', '', basename($article['link'])));
			$c = explode('-',ZENPHOTO_VERSION);
			$c = array_shift($c);
			if ($v && version_compare($c, $v, "<")) {
				$webVersion = $v;
			}
		}
	}
	return $webVersion;
}

/**
 *
 * Displays the "new version available" message on admin pages
 * @param unknown_type$tab
 * @param unknown_type $subtab
 */
function admin_showupdate($tab, $subtab) {
	?>
	<div class="notebox">
		<h2><a href="http://www.zenphoto.org" alt="<?php echo gettext('Zenphoto download page'); ?>"><?php echo gettext("A new version of Zenphoto version is available."); ?></a></h2>
	</div>
	<?php
	setOption('last_update_check', time());
	return $tab;
}

?>