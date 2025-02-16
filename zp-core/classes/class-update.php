<?php

/**
 * 
 * Static class to check for available updates of Zenphoto
 * @since 1.1.3 (plugin checkForUpdate)
 * @since 1.6.6 - core 
 * @author Stephen Billard (sbillard) (Original plugin), modified by Malte Müller (acrylian)
 */
class update {

	/**
	 * Searches the zenphoto.org home page for the current zenphoto download
	 * locates the version number of the download and compares it to the version
	 * we are running.
	 * 
	 * Requires the native DOMDocumment PHP extension to be installed on the server
	 *
	 * @return array If there is a more current version on the WEB, returns an array with the number and a subarray of the release post article
	 */
	static function check() {
		if (!class_exists('DOMDocument')) {
			if (DEBUG_ERROR) {
				debuglog(gettext('Native PHP class DOMDocument nott available. Update check not possible'));
			}
			setOption('last_update_notice', '');
			return;
		}
		$webVersion = false;
		$v = getOption('last_update_version');
		$last = getOption('last_update_check');
		if (empty($last) || is_numeric($last)) {
			if (time() - $last > 86400) { //  + 86400 / one day
				if (is_connected()) {
					require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/class-rsslib.php');
					$recents = rssLib::retrieve("https://www.zenphoto.org/index.php?rss=news&category=changelog");
					if ($recents) {
						array_shift($recents);
						$article = array_shift($recents); //	most recent changelog article
						$v = trim(str_replace('zenphoto-', '', basename($article['link'])));
						$c = explode('-', ZENPHOTO_VERSION);
						$c = array_shift($c);
						if ($v && version_compare($c, $v, "<")) {
							$webVersion = $v;
						}
					}
				}
				setOption('last_update_check', time());
				setOption('last_update_version', $webVersion);
				if ($webVersion) {
					$updatenotice = '<p><strong>' . sprintf(gettext('Zenphoto %s is available.'), $webVersion) . '</strong>';
					$updatenotice .= ' ' . sprintf(gettext('You are running <strong>Zenphoto %1$s</strong>. Please upgrade.'), ZENPHOTO_VERSION);
					$updatenotice .= ' ' . '<a target="_blank" rel="noopener noreferrer" href="' . html_encode($article['link']) . '" title="' . gettext('Release notes') . '">' . gettext('Release notes') . '</a></p>';
					setOption('last_update_notice', $updatenotice);
				} else {
					setOption('last_update_notice', '');
				}
			}
		}
	}

	/**
	 * Prints a notice about an available update
	 * 
	 * @since 3.0
	 */
	static function printNotice() {
		update::check();
		if (getOption('last_update_notice')) {
			?>
			<div class="notebox">
				<?php echo getOption('last_update_notice'); ?>
			</div>
			<?php
		}
	}

}