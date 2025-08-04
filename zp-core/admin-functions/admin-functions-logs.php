<?php 
/**
 * Log related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */


/**
 * Returns an array with the logtabs array, the default log tab and an array of log files to the default (current) log tab (for use in the logfile selector)
 * 
 * @since 1.6.1 - Reworked for displaying only tabs for log types
 * @return array
 */
function getLogTabs() {
	$defaulttab = $defaultlogfile = null;
	$localizer = getDefaultLogTabs();
	$subtabs = $logs = array();
	$logs = getLogFiles();
	if ($logs) {
		$currenttab = sanitize(@$_GET['tab'], 3);
		$currentlogfile = sanitize(@$_GET['logfile'], 3);
		foreach ($logs as $tab => $logfiles) {
			if (array_key_exists($tab, $localizer)) {
				$tabname = $localizer[$tab];
			} else {
				$tabname = str_replace('_', ' ', $tab);
			}
			if ($currenttab == $tab) {
				$defaulttab = $currenttab;
			}
			if (!empty($logfiles) > 0 && empty($defaulttab)) {
				$defaulttab = $tab;
			}
			$subtabs = array_merge($subtabs, array($tabname => FULLWEBPATH . '/' . ZENFOLDER . '/admin-logs.php?page=logs&tab=' . $tab));
		}
		$logsfinal = $logs[$defaulttab];
		sortArray($logsfinal, true, true);
		$logsfinal = array_values($logsfinal); // reset keys as sortArray() keeps them
		foreach ($logsfinal as $logfile) {
			if ($currentlogfile == $logfile) {
				$defaultlogfile = $currentlogfile;
			}
		}
		if (empty($defaultlogfile)) {
			$defaultlogfile = $logsfinal[0];
		}
		$return = array($subtabs, $defaulttab, $defaultlogfile, $logsfinal);
		return $return;
	}
}

/**
 * Gets an array log tab names and localized (gettexted) log titles
 * 
 * @since 1.6.1
 * @return array
 */
function getDefaultLogTabs() {
	return array(
			'setup' => gettext('setup'),
			'security' => gettext('security'),
			'debug' => gettext('debug')
	);
}

/**
 * Gets a nested array with the log type (tab name) and corresponding log files
 * 
 * @since 1.6.1
 * @return array
 */
function getLogFiles() {
	$logs = array();
	$filelist = safe_glob(SERVERPATH . "/" . DATA_FOLDER . '/*.log');
	if (count($filelist) > 0) {
		foreach ($filelist as $logfile) {
			$logfile_nosuffix = stripSuffix(basename($logfile));
			$is_newlogname = explode("_", $logfile_nosuffix);
			if (count($is_newlogname) > 1) {
				// new log name with date 
				$log_tab = $is_newlogname[0];
			} else {
				$matches = array();
				preg_match('|-(.*)|', $logfile_nosuffix, $matches);
				if ($matches) {
					// old log name with number
					$log_tab = str_replace($matches[0], '', $logfile_nosuffix);
				} else {
					// old log name without number
					$log_tab = $logfile_nosuffix;
				}
			}
			$logs[$log_tab][] = $logfile_nosuffix;
		}
	}
	return $logs;
}

/**
 * Prints the selector for logfiles of the current log tab
 * 
 * @since 1.6.1
 * 
 * @param string $currentlogtab Current log tab 
 * @param string $currentlogfile Current log file selected
 * @param array $logfiles Array of logfiles
 */
function printLogSelector($currentlogtab = '', $currentlogfile = '', $logfiles = array()) {
	if (!empty($currentlogtab) && !empty($currentlogfile) && (!empty($logfiles) && count($logfiles) > 1)) {
		?>
		<form name="logfile_selector" id="logfile_selector"	action="#">
			<p>
				<label>
					<select name="ListBoxURL" size="1" onchange="zp_gotoLink(this.form)"> 
						<?php 
						foreach($logfiles as $logfile) {
							$url = WEBPATH . '/' . ZENFOLDER . '/admin-logs.php?page=logs&tab='. html_encode($currentlogtab) . '&logfile='.$logfile; 
							$selected = '';
							if ($logfile == $currentlogfile) {
								$selected = ' selected';
							}
							?>
							<option value="<?php echo $url; ?>"<?php echo $selected; ?>><?php echo html_encode($logfile); ?></option>
							<?php
						}
					?>
					</select> <?php echo gettext('Select the logfile to view'); ?>
				</label>
				</p>
		</form>
		<?php
	}
}
