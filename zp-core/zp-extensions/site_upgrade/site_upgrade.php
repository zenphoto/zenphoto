<?php
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());
$htpath = SERVERPATH.'/.htaccess';
$ht = file_get_contents($htpath);
preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|',$ht,$matches);
switch (@$_GET['siteState']) {
	case 'closed':
		if (strpos($matches[0][1],'#')===0) {
			foreach ($matches[0] as $match) {
				$ht = str_replace($match, ' '.substr($match,1), $ht);
			}
			@chmod($htpath, 0777);
			file_put_contents($htpath, $ht);
			@chmod($htpath,0444);
		}
		$report = gettext('Site is now marked in upgrade.');
		setOption('site_upgrade_state', 'closed');
		break;
	case 'open':
		$report = gettext('Site is viewable.');
		setOption('site_upgrade_state', 'open');
		break;
	case 'closed_for_test':
		if (strpos($matches[0][1],'#')!==0) {
			foreach ($matches[0] as $match) {
				$ht = str_replace($match, preg_replace('/^ /','# ',$match), $ht);
			}
			@chmod($htpath, 0777);
			file_put_contents($htpath, $ht);
			@chmod($htpath, 0444);
		}
		$report = gettext('Site is avaiable for testing only.');
		setOption('site_upgrade_state', 'closed_for_test');
		break;
}

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report='.$report);
exitZP();
?>