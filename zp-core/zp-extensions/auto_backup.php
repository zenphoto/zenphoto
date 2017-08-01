<?php

/**
 * This plugin provides a facility to periodically run the backup utility. Use it to
 * insure that database backups are done on a regular basis.
 *
 * <b>NOTE:</b> The website must be visited and live pages must be served for this
 * plugin to be able to check if it is time to run.
 *
 * Inacative or heavily cached sites may not get backed up as frequently as the
 * interval specifies. Of course, if there is no dynamic activity on the site,
 * there probably is little need to do the backup in the first place.
 *
 * Backups are run under the master administrator authority.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(2 | ADMIN_PLUGIN | THEME_PLUGIN);
$plugin_description = gettext("Periodically backup the database.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'auto_backup';
if (OFFSET_PATH == 2) {
	purgeOption('last_backup_run'); // for sure things have changed
} else {
	if ((getOption('last_backup_run') + getOption('backup_interval') * 86400) < time()) { // register if it is time for a backup
		require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
		zp_register_filter('admin_head', 'auto_backup::timer_handler');
		zp_register_filter('theme_head', 'auto_backup::timer_handler');
		$_backupMutex = new zpMutex('bK');
	}
}

/**
 * Option handler class
 *
 */
class auto_backup {

	/**
	 * class instantiation function
	 *
	 */
	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('backup_interval', 7);
			setOptionDefault('backups_to_keep', 5);
		}
	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$options = array(gettext('Run interval') => array('key' => 'backup_interval', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'limits' => array('min' => 1),
						'desc' => gettext('The run interval (in days) for auto backup.')),
				gettext('Backups to keep') => array('key' => 'backups_to_keep', 'type' => OPTION_TYPE_NUMBER,
						'order' => 0,
						'limits' => array('min' => 1),
						'desc' => gettext('Auto backup will keep only this many backup sets. Older sets will be removed.'))
		);
		if ($d = getOption('last_backup_run')) {
			$options[gettext('Last backup')] = array('key' => 'last_backup_run', 'type' => OPTION_TYPE_NOTE,
					'order' => 2,
					'desc' => '<p class="notebox">' . sprintf(gettext('Auto Backup last ran %s.'), date('Y-m-d H:i:s', $d)) . '</p>');
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	/**
	 * Handles the periodic start of the backup/restore utility to backup the database
	 * @param string $discard
	 */
	static function timer_handler($discard) {
		global $_backupMutex;
		$_backupMutex->lock();
		if ((getOption('last_backup_run') + getOption('backup_interval') * 86400) < time()) {
			//	maybe a race condition? Only need one execution
			$curdir = getcwd();
			$folder = SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER;
			if (!is_dir($folder)) {
				mkdir($folder, FOLDER_MOD);
			}
			chdir($folder);
			$filelist = safe_glob('*' . '.zdb');
			$list = array();
			foreach ($filelist as $file) {
				$list[$file] = filemtime($file);
			}
			chdir($curdir);
			asort($list);
			$list = array_flip($list);
			$keep = getOption('backups_to_keep');
			while (!empty($list) && count($list) >= $keep) {
				$file = array_shift($list);
				@chmod(SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER . '/' . $file, 0777);
				unlink(SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER . '/' . $file);
			}
			cron_starter(SERVERPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php', array('backup' => 1, 'autobackup' => 1, 'compress' => sprintf('%u', getOption('backup_compression')), 'XSRFTag' => 'backup'), 3);
			setOption('last_backup_run', time());
		}
		$_backupMutex->unlock();
		return $discard;
	}

}

?>