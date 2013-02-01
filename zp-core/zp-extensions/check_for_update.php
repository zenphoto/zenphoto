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

if (OFFSET_PATH != 2) {
	$me = explode('?',getRequestURI());
	if (basename(array_shift($me))=='admin.php') {
		$last = getOption('last_update_check');
		if (empty($last) || is_numeric($last)) {
			if (time() > $last+1728000) {
				//	check each 20 days
				$v = checkForUpdate();
				setOption('last_update_check', time());
				if (!empty($v)) {
					setOption('last_update_check','<a href="http://www.zenphoto.org" alt="'.gettext('Zenphoto download page').'">'.gettext("A new version of Zenphoto version is available.").'</a>');
				}
			}
		} else {
			zp_register_filter('admin_note', 'admin_showupdate');
		}
		unset($last);
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
	if (is_connected() && class_exists('DOMDocument')) {
		require_once(dirname(__FILE__).'/zenphoto_news/rsslib.php');
		$recents = RSS_Retrieve("http://www.zenphoto.org/index.php?rss=news&category=changelog");
		if ($recents) {
			array_shift($recents);
			$article = array_shift($recents);	//	most recent changelog article
			$v = trim(str_replace('zenphoto-', '', basename($article['link'])));
			$c = explode('-',ZENPHOTO_VERSION);
			$c = array_shift($c);
			if (!empty($v)) {
				$pot = array(1000000000, 10000000, 100000, 1);
				$wv = explode('.', $v);
				$wvd = 0;
				foreach ($wv as $i => $d) {
					$wvd = $wvd + (int) $d * $pot[$i];
				}
				$cv = explode('.', $c);
				$cvd = 0;
				foreach ($cv as $i => $d) {
					$cvd = $cvd + (int) $d * $pot[$i];
				}
				if ($cvd < $wvd) {
					$webVersion = $v;
				} else {
					$webVersion = false;
				}
			}
			Return $webVersion;
		}
	}
	return false;
}

/**
 *
 * Displays the "new version available" message on admin pages
 * @param unknown_type$tab
 * @param unknown_type $subtab
 */
function admin_showupdate($tab, $subtab) {
	$current = getOption('last_update_check');
	?>
	<div class="notebox">
		<h2><?php echo $current; ?></h2>
	</div>
	<?php
	setOption('last_update_check', time());
	return $tab;
}

?>