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
	if (basename(explode('?',getRequestURI())[0])=='admin.php') {
		$last = getOption('last_update_check');
		if (empty($last) || is_numeric($last)) {
			if (time() > $last+1728000) {
				//	check each 20 days
				setOption('last_update_check', time());
				$v = checkForUpdate();
				if (!empty($v)) {
					if ($v != 'X') {
						setOption('last_update_check','<a href="http://www.zenphoto.org" alt="'.gettext('Zenphoto download page').'">'.gettext("A new version of Zenphoto version is available.").'</a>');
					}
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
	if (!is_connected()) return 'X';
	$c = ZENPHOTO_VERSION;
	$v = @file_get_contents('http://www.zenphoto.org/files/LATESTVERSION');
	if (empty($v)) {
		$webVersion = 'X';
	} else {
		if ($i = strpos($v, 'RC')) {
			$v_candidate = intval(substr($v, $i+2));
		} else {
			$v_candidate = 9999;
		}
		if ($i = strpos($c, 'RC')) {
			$c_candidate = intval(substr($c, $i+2));
		} else {
			$c_candidate = 9999;
		}
		$pot = array(1000000000, 10000000, 100000, 1);
		$wv = explode('.', $v);
		$wvd = 0;
		foreach ($wv as $i => $d) {
			$wvd = $wvd + $d * $pot[$i];
		}
		$cv = explode('.', $c);
		$cvd = 0;
		foreach ($cv as $i => $d) {
			$cvd = $cvd + $d * $pot[$i];
		}
		if ($wvd > $cvd || (($wvd == $cvd) && ($c_candidate < $v_candidate))) {
			$webVersion = $v;
		} else {
			$webVersion = '';
		}
	}
	Return $webVersion;
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
		<h2><?php echo getOption('last_update_check'); ?></h2>
	</div>
	<?php
	return $tab;
}

?>