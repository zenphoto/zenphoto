<?php

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());
$htpath = SERVERPATH . '/.htaccess';
$ht = @file_get_contents($htpath);

switch (isset($_GET['siteState']) ? $_GET['siteState'] : NULL) {
	case 'closed':
		// TODO: do the same for other feeds?
		if (class_exists('RSS')) {

			class setupRSS extends RSS {

				public function getitems() {
					$this->feedtype = 'setup';
					$items = array();
					$items[] = array('title'						 => gettext('RSS suspended'),
									'link'						 => '',
									'enclosure'				 => '',
									'category'				 => '',
									'media_content'		 => '',
									'media_thumbnail'	 => '',
									'pubdate'					 => date("r", time()),
									'desc'						 => gettext('The RSS feed is currently not available.'));
					return $items;
				}

				protected function startCache() {

				}

				protected function endCache() {

				}

			}

			$rss = new setupRSS();
			ob_start();
			$rss->printFeed();
			$xml = ob_get_contents();
			ob_end_clean();
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss-closed.xml', $xml);
		}
		$report = gettext('Site is now marked in upgrade.');
		setSiteState('closed');
		break;
	case 'open':
		$report = gettext('Site is viewable.');
		setSiteState('open');
		break;
	case 'closed_for_test':
		$report = gettext('Site is avaiable for testing only.');
		setSiteState('closed_for_test');
		break;
}

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report=' . $report);
exitZP();

/**
 * updates the site status
 * @param string $state
 */
function setSiteState($state) {
	$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	$zp_cfg = updateConfigItem('site_upgrade_state', $state, $zp_cfg);
	storeConfig($zp_cfg);
	setOption('site_upgrade_state', $state);
}

?>