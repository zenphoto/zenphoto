<?php
/**
 * setup support functions
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 */
// force UTF-8 Ø
require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');
require_once(dirname(dirname(__FILE__)) . '/functions-config.php');

define('SETUPLOG', SERVERPATH . '/' . DATA_FOLDER . '/setup.log');

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentZPFiles($folder, $lcFilesystem, $exclude) {
	global $_zp_resident_files;
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if ($file != '.' && $file != '..' && !in_array($file, $exclude)) {
			if (is_dir($folder . '/' . $file)) {
				if ($file != 'session') {
					getResidentZPFiles($folder . '/' . $file, $lcFilesystem, $exclude);
					$entry = $folder . '/' . $file;
					if ($lcFilesystem)
						$entry = strtolower($entry);
					$_zp_resident_files[] = $entry;
				}
			} else {
				$entry = $folder . '/' . $file;
				if ($lcFilesystem)
					$entry = strtolower($entry);
				$_zp_resident_files[] = $entry;
			}
		}
	}
	closedir($dir);
}

function primeMark($text) {
	global $primeid;
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
	</script>
	<div id="prime<?php echo ++$primeid; ?>" class="error"><?php printf(gettext('Testing %s.'), $text); ?></div>
	<?php
}

function checkMark($check, $text, $text2, $msg, $stopAutorun = true) {
	global $warn, $moreid, $primeid, $autorun, $displayLimited;
	$classes = array('fail' => gettext('Fail: '), 'warn' => gettext('Warn: '), 'pass' => gettext('Pass: '));

	$display = '';
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
	</script>
	<?php
	$check = (int) $check;
	$anyway = 0;
	$dsp = '';
	if ($check > 0) {
		$check = 1;
	}

	switch ($check) {
		case 0:
			$cls = "fail";
			$ico = CROSS_MARK_RED;
			break;
		case -1:
		case -3:
			$cls = "warn";
			$ico = WARNING_SIGN_ORANGE;
			$warn = true;
			if ($stopAutorun && $autorun) {
				$autorun = false;
				$anyway = 2;
				$check = -1;
			} else {
				$anyway = 1;
			}
			break;
		case 1:
		case -2:
			if ($displayLimited) {
				$display = ' style="display:none;"';
			}
			$cls = "pass";
			$ico = CHECKMARK_GREEN;
			break;
	}
	if ($check <= 0) {
		?>
		<li class="<?php echo $cls; ?>"<?php echo $display; ?>>
			<?php
			echo $ico . ' ';
			if (empty($text2)) {
				echo $text;
				$dsp .= trim($text);
			} else {
				echo $text2;
				$dsp .= trim($text2);
			}
			if (!empty($msg)) {
				switch ($check) {
					case 0:
						?>
						<div class="error">
							<h1><?php echo gettext('Error!'); ?></h1>
							<p><?php echo $msg; ?></p>
						</div>
						<?php
						break;
					case -1:
						$anyway = 1;
						?>
						<div class="warning">
							<h1><?php echo gettext('Warning!'); ?></h1>
							<p><?php echo $msg; ?></p>
						</div>
						<?php
						break;
					default:
						$moreid++;
						?>
						<?php
						if ($check == -3) {
							?>
							<a onclick="toggle_visibility('more<?php echo $moreid; ?>');">
								<?php echo gettext('<strong>Warning!</strong> click for details'); ?>
							</a>
							<div class="warning" id="more<?php echo $moreid; ?>" style="display: none">
								<h1><?php echo gettext('Warning!'); ?></h1>
								<?php
							} else {
								?>
								<a onclick="toggle_visibility('more<?php echo $moreid; ?>');">
									<?php echo gettext('<strong>Notice!</strong> click for details'); ?>
								</a>
								<div class="notice" id="more<?php echo $moreid; ?>" style="display: none">
									<h1><?php echo gettext('Notice!'); ?></h1>
									<?php
								}
								?>
								<p><?php echo $msg; ?></p>
							</div>
							<?php
							break;
					}
					$dsp .= ' ' . $msg;
				}
				?>
		</li>
		<?php
	} else {
		$dsp = $text;
		?>
		<li class="<?php echo $cls; ?>"<?php echo $display; ?>>
			<?php echo $ico . ' ' . $text; ?>
		</li>
		<?php
	}
	if ($anyway == 2) {
		$stopped = '(' . gettext('Autorun aborted') . ') ';
	} else {
		$stopped = '';
	}
	setupLog($classes[$cls] . $stopped . $dsp, $anyway);
	return $check;
}

/**
 *
 * checks presence and permissions of folders
 * @param $which
 * @param $path
 * @param $class
 * @param $relaxation
 * @param $subfolders
 */
function folderCheck($which, $path, $class, $subfolders, $recurse, $chmod, $updatechmod) {
	global $permission_names;
	$path = str_replace('\\', '/', $path);
	if (!is_dir($path) && $class == 'std') {
		mkdir_recursive($path, $chmod);
	}
	switch ($class) {
		case 'std':
			$append = trim(str_replace(SERVERPATH, '', $path), '/');
			if (($append != $which)) {
				$f = " (<em>$append</em>)";
			} else {
				$f = '';
			}
			if (!is_null($subfolders)) {
				$subfolderfailed = '';
				foreach ($subfolders as $subfolder) {
					if (!mkdir_recursive($path . $subfolder, $chmod)) {
						$subfolderfailed .= ', <code>' . $subfolder . '</code>';
					}
				}
				if (!empty($subfolderfailed)) {
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [subfolder creation failure]'), $which, $f), sprintf(gettext('Setup could not create the following subfolders:<br />%s'), substr($subfolderfailed, 2)));
				}
			}

			if (isWin()) {
				$perms = fileperms($path) & 0700;
				$check = $chmod & 0700;
			} else {
				$perms = fileperms($path) & 0777;
				$check = $chmod;
			}
			if (setupUserAuthorized() && $updatechmod) {
				@chmod($path, $chmod);
				clearstatcache();
				$perms = fileperms($path) & 0777;
				if (!checkPermissions($perms, $chmod)) {
					if (array_key_exists($perms & 0666 | 4, $permission_names)) {
						$perms_class = $permission_names[$perms & 0666 | 4];
					} else {
						$perms_class = gettext('unknown');
					}
					if (array_key_exists($chmod & 0666 | 4, $permission_names)) {
						$chmod_class = $permission_names[$chmod & 0666 | 4];
					} else {
						$chmod_class = gettext('unknown');
					}
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [permissions failure]'), $which, $f), sprintf(gettext('Setup could not change the file permissions from <em>%1$s</em> (<code>0%2$o</code>) to <em>%3$s</em> (<code>0%4$o</code>). You will have to set the permissions manually.'), $perms_class, $perms, $chmod_class, $chmod));
				} else {
					if ($recurse) {
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							$.ajax({
								type: 'POST',
								cache: false,
								url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/setup/setup_permissions_changer.php',
								data: 'folder=<?php echo $path; ?>&key=<?php echo sha1(filemtime(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)); ?>'
							});
							// ]]> -->
						</script>
						<?php
					}
				}
			}
			break;
		case 'in_webpath':
			$webpath = $_SERVER['SCRIPT_NAME'];
			if (empty($webpath)) {
				$serverroot = SERVERPATH;
			} else {
				$i = strpos($webpath, '/' . ZENFOLDER);
				$webpath = substr($webpath, 0, $i);
				$serverroot = substr(SERVERPATH, 0, strpos(SERVERPATH, $webpath));
			}
			$append = substr($path, strlen($serverroot) + 1);
			$f = " (<em>$append</em>)";
			break;
		case 'external':
			$append = $path;
			$f = " (<em>$append</em>)";
			break;
	}
	if (!is_dir($path)) {
		$msg = " " . sprintf(gettext('You must create the folder <em>%1$s</em><br /><code>mkdir(%2$s, 0777)</code>.'), $append, substr($path, 0, -1));
		if ($class != 'std') {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist]'), $which, $append), $msg);
		} else {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist and <strong>setup</strong> could not create it]'), $which, $append), $msg);
		}
	} else if (!is_writable($path)) {
		$msg = sprintf(gettext('Change the permissions on the <code>%1$s</code> folder to be writable by the server (<code>chmod 777 %2$s</code>)'), $which, $append);
		return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> is not writeable and <strong>setup</strong> could not make it so]'), $which, $append), $msg);
	} else {
		return checkMark(true, sprintf(gettext('<em>%1$s</em> folder%2$s'), $which, $f), '', '');
	}
}

/**
 *
 * compares versions for required, desired version levels
 * @param $required
 * @param $desired
 * @param $found
 */
function versionCheck($required, $desired, $found) {
	$nr = explode(".", $required . '.0.0.0');
	$vr = $nr[0] * 10000 + $nr[1] * 100 + $nr[2];
	$nf = explode(".", $found . '.0.0.0');
	$vf = $nf[0] * 10000 + $nf[1] * 100 + $nf[2];
	$nd = explode(".", $desired . '.0.0.0');
	$vd = $nd[0] * 10000 + $nd[1] * 100 + $nd[2];
	if ($vf < $vr)
		return 0;
	if ($vf < $vd)
		return -1;
	return 1;
}

/**
 *
 * drop-down for character set selection
 * @param $select
 */
function charsetSelector($select) {
	global $_zp_UTF8;
	$selector = '<select id="FILESYSTEM_CHARSET" name="FILESYSTEM_CHARSET" >';
	$selector .= '<option value ="unknown">' . gettext('Unknown') . '</option>';
	$totalsets = $_zp_UTF8->charsets;
	ksort($totalsets);
	foreach ($totalsets as $key => $char) {
		$selector .= '	<option value="' . $key . '"';
		if ($key == $select) {
			$selector .= ' selected="selected"';
		}
		$selector .= '>' . $char . '</option>';
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span>';
	return $selector;
}

function permissionsSelector($permission_names, $select) {
	$select = $select | 4;
	global $_zp_UTF8;
	$selector = '<select id="chmod_permissions" name="chmod_permissions" >';
	$c = 0;
	foreach ($permission_names as $key => $permission) {
		$selector .= '	<option value="' . $c . '"' . ($select == $key ? ' selected="selected"' : '') . '>' . sprintf(gettext('%1$s (0%2$o)'), $permission_names[$key], $key) . '</option>';
		$c++;
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right;"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span><br class="clearall">';
	return $selector;
}

function setupLog($message, $anyway = false, $reset = false) {
	global $debug, $_zp_mutex, $chmod;
	if ($debug || $anyway) {
		if (is_object($_zp_mutex))
			$_zp_mutex->lock();
		if (!file_exists(dirname(SETUPLOG))) {
			mkdir_recursive(dirname(SETUPLOG), $chmod | 0311);
		}
		if ($reset) {
			$mode = 'w';
		} else {
			$mode = 'a';
		}
		$f = fopen(SETUPLOG, $mode);
		if ($f) {
			fwrite($f, strip_tags($message) . "\n");
			fclose($f);
			chmod(SETUPLOG, DATA_MOD);
			clearstatcache();
		}
		if (is_object($_zp_mutex))
			$_zp_mutex->unlock();
	}
}

function setupLanguageSelector() {
	$languages = generateLanguageList();
	$unsupported = getSerializedArray(getOption('locale_unsupported'));
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale']);
		if (getOption('locale') != $locale || isset($unsupported[$locale])) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'), html_encode($languages[$locale])); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), html_encode($locale)); ?>
					<br />
					<?php echo gettext('You can use the <em>debug</em> plugin to see which locales your server supports.'); ?>
				</h2>
			</div>
			<?php
		}
	}
	?>
	<ul class="sflags">
		<?php
		$_languages = generateLanguageList();
		krsort($_languages, SORT_LOCALE_STRING);
		$currentValue = getOption('locale');
		foreach ($_languages as $text => $lang) {
			if (i18nSetLocale($lang)) {
				?>
				<li<?php if ($lang == $currentValue) echo ' class="currentLanguage"'; ?>>
					<?php
					if ($lang != $currentValue) {
						?>
						<a href="javascript:launchScript('',['locale=<?php echo $lang; ?>']);" >
							<?php
						}
						if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/locale/' . $lang . '/flag.png')) {
							$flag = WEBPATH . '/' . ZENFOLDER . '/locale/' . $lang . '/flag.png';
						} else {
							$flag = WEBPATH . '/' . ZENFOLDER . '/locale/missing_flag.png';
						}
						?>
						<img src="<?php echo $flag; ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
						<?php
						if ($lang != $currentValue) {
							?>
						</a>
						<?php
					}
					?>
				</li>
				<?php
			}
		}
		?>
	</ul>
	<?php
}

function setupXSRFDefender($where) {
	$xsrftoken = setupXSRFToken();
	if (!isset($_REQUEST['xsrfToken']) || $xsrftoken != $_REQUEST['xsrfToken']) {
		?>
		<p class="errorbox" >
			<?php echo gettext('An attempt at cross site reference forgery has been blocked.') ?>
		</p>
		<?php
		exit();
	}
}

function setupXSRFToken() {
	if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
		$zp_cfg = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		return sha1(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE . $zp_cfg . session_id());
	} else {
		return false;
	}
}

function checkPermissions($actual, $expected) {
	if (isWin()) {
		return ($actual & 0700) == ($expected & 0700); //	with windows owner==group==public
	} else {
		return ($actual & 0770) == ($expected & 0770); //	We do not care about the execute permissions
	}
}

function acknowledge($value) {
	global $_zp_conf_vars;
	$link = WEBPATH . '/' . ZENFOLDER . '/setup/index.php?security_ack=' . ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) | $value) . '&amp;xsrfToken=' . setupXSRFToken();
	return sprintf(gettext('Click <a href="%s">here</a> to acknowledge that you wish to ignore this issue. It will then become a warning.'), $link);
}

function configMod() {
	$mod = 0600;
	$str = NULL;
	while (empty($str)) {
		@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod);
		$str = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		if ($mod == 0666) {
			break;
		}
		$mod = $mod | $mod >> 3;
	}
}

function printSetupFooter() {
	echo "<div id=\"footer\">";
	echo gettext('<span class="zen-logo"><a href="https://' . GITHUB . ' title="' . gettext('A simpler media content management system') . '"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/zen-logo-light.png" /></a></span> ');
	echo ' | <a href="https://' . GITHUB . '/issues" title="Support">' . gettext('Support') . '</a> | <a href="https://' . GITHUB . '/commits/master" title="' . gettext('View Change log') . '">' . gettext('Change log') . "</a>\n</div>";
}

function setupUserAuthorized() {
	global $_zp_authority, $_zp_loggedin;
	if ($_zp_authority && $_zp_authority->getAdministrators()) {
		return $_zp_loggedin & ADMIN_RIGHTS;
	} else {
		return true; //	in a primitive environment
	}
}

function checkUnique($table, $unique) {
	global $autorun;
	$sql = 'SHOW KEYS FROM ' . $table;
	$result = query_full_array($sql);
	foreach ($result as $key) {
		if (!$key['Non_unique']) {
			unset($unique[$key['Column_name']]);
		}
	}
	if (!empty($unique)) {
		$autorun = false;
		?>
		<p class="notebox">
			<?php
			printf(gettext('<strong>Warning:</strong> the <code>%s</code> table appears not to have a proper <em>UNIQUE</em> key. There are probably duplicate entries in the table which can cause unpredictable behavior. This can normally be corrected by creating a ZenPhoto20 backup, dropping the table, running setup to restore the table, and then restoring from the backup. Note, however, that the duplicate entries will be lost.'), trim($table, '`'));
			?>
		</p>
		<?php
	}
}

/**
 * Executes and logs database update queries
 * @param string $sql
 *
 * @author Stephen Billard
 * @Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
function setupQuery($sql, $failNotify = true, $log = true) {
	global $updateErrors;
	$result = db_table_update($sql);
	if (OFFSET_PATH == 2) { //don't write to setup log if not running setup
		if ($result) {
			setupLog(sprintf(gettext('Query Success: %s'), $sql), $log);
		} else {
			if ($failNotify) {
				$updateErrors = true;
				$error = db_error();
				setupLog(sprintf(gettext('Query Failed: %1$s ' . "\n" . ' Error: %2$s'), $sql, $error), true);
			}
		}
	}
	return $result;
}

function sendImage($external) {
	if ($external) {
		$img = 'pass_open.png';
	} else {
		$img = 'pass.png';
	}
	$fp = fopen(SERVERPATH . '/' . ZENFOLDER . '/images/' . $img, 'rb');

// send the right headers
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header("Content-Type: image/png");
	header("Content-Length: " . filesize(SERVERPATH . '/' . ZENFOLDER . '/images/' . $img));
// dump the picture and stop the script
	fpassthru($fp);
	fclose($fp);
}

function shutDownFunction() {
	global $extension;
	$error = error_get_last();
	if ($error) {
		if (version_compare(phpversion(), '7', '>=')) {
			error_clear_last(); //	it will be handled here, not on shutdown!
		}
		$msg = sprintf(gettext('Plugin:%1$s ERROR "%2$s" in %3$s on line %4$s'), $extension, $error['message'], $error['file'], $error['line']);
		setupLog($msg, true);
		if ($extension) {
			enableExtension($extension, 0);
		}
	}
}
?>