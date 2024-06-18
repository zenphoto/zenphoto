<?php
// force UTF-8 Ø
require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');

$const_webpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$_zp_setup_serverpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
preg_match('~(.*)/(' . ZENFOLDER . ')~', $const_webpath, $matches);
if (empty($matches)) {
	$const_webpath = '';
} else {
	$const_webpath = $matches[1];
	$_zp_setup_serverpath = substr($_zp_setup_serverpath, 0, strrpos($_zp_setup_serverpath, '/' . ZENFOLDER));
}

define('SETUPLOG', $_zp_setup_serverpath . '/' . DATA_FOLDER . '/setup.log');
if (!defined('SERVERPATH'))
	define('SERVERPATH', $_zp_setup_serverpath);

require_once(dirname(dirname(__FILE__)) . '/functions/functions-config.php');

/**
 * setup support class
 * @package zpcore\setup
 */
class setup {

	/**
	 * enumerates the files in folder(s)
	 * @param $folder
	 */
	static function getResidentZPFiles($folder, $lcFilesystem = false) {
		global $_zp_resident_files;
		$dir = opendir($folder);
		while (($file = readdir($dir)) !== false) {
			$file = str_replace('\\', '/', $file);
			if ($file != '.' && $file != '..') {
				if (is_dir($folder . '/' . $file)) {
					if ($file != 'session') {
						setup::getResidentZPFiles($folder . '/' . $file, $lcFilesystem);
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

	/**
	 * 
	 * @global type $_zp_setup_primeid
	 * @param type $text
	 */
	static function primeMark($text) {
		global $_zp_setup_primeid;
		?>
		<script>
			$("#prime<?php echo $_zp_setup_primeid; ?>").remove();
		</script>
		<div id="prime<?php echo ++$_zp_setup_primeid; ?>" class="error"><?php printf(gettext('Testing %s.'), $text); ?></div>
		<?php
	}

	/**
	 * Prints an icon for success of failure of an action (it also returns the check level!)
	 * @global boolean $_zp_setup_warn 
	 * @global type $_zp_setup_moreid
	 * @global type $_zp_setup_primeid
	 * @global boolean $_zp_setup_autorun
	 * @param int $check 0 = fail, -1,-3 = warning, 1 or -2 = pass 
	 * @param type $text Success text
	 * @param type $text2 Warning text
	 * @param type $msg Detailed message
	 * @param bool $stopAutorun True if a show (setup) stopping issue
	 * @return int
	 */
	static function checkMark($check, $text, $text2, $msg, $stopAutorun = true, $display_success = false) {
		global $_zp_setup_warn, $_zp_setup_moreid, $_zp_setup_primeid, $_zp_setup_autorun;
		$classes = array(
				'fail' => gettext('Fail: '),
				'warn' => gettext('Warn: '),
				'pass' => gettext('Pass: '));
		?>
		<script>
			$("#prime<?php echo $_zp_setup_primeid; ?>").remove();
		</script>
		<?php
		$anyway = 0;
		$dsp = '';
		if ($check > 0) {
			$check = 1;
		}
		switch ($check) {
			case 0:
				$cls = "fail";
				break;
			case -1:
			case -3:
				$cls = "warn";
				$_zp_setup_warn = true;
				if ($stopAutorun && $_zp_setup_autorun) {
					$_zp_setup_autorun = false;
					$anyway = 2;
					$check = -1;
				} else {
					$anyway = 1;
				}
				break;
			case 1:
			case -2:
				$cls = "pass";
				break;
		}
		if ($check <= 0) {
			?>
			<li class="<?php echo $cls; ?>"><?php
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
							$_zp_setup_moreid++;
							?>
							<?php
							if ($check == -3) {
								?>
								<div class="warning" id="more<?php echo $_zp_setup_moreid; ?>">
									<h1><?php echo gettext('Warning!'); ?></h1>
									<?php
								} else {
									?>
									<div class="notice" id="more<?php echo $_zp_setup_moreid; ?>">
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
			$anyway = 1; // we want to log success as well
			$dsp = $text;
		}
		if ($anyway == 2) {
			$stopped = '(' . gettext('Autorun aborted') . ') ';
		} else {
			$stopped = '';
		}
		setup::log($classes[$cls] . $stopped . $dsp, $anyway);
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
	static function folderCheck($which, $path, $class, $subfolders, $recurse, $chmod, $updatechmod) {
		global $_zp_setup_serverpath, $_zp_setup_permission_names;
		$path = str_replace('\\', '/', $path);
		if (!is_dir($path) && $class == 'std') {
			mkdir_recursive($path, $chmod);
		}
		$append = '';
		switch ($class) {
			case 'std':
				$append = trim(str_replace($_zp_setup_serverpath, '', $path), '/');
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
						return setup::checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [subfolder creation failure]'), $which, $f), sprintf(gettext('Setup could not create the following subfolders:<br />%s'), substr($subfolderfailed, 2)));
					}
				}

				if (setup::isWin()) {
					$perms = fileperms($path) & 0700;
					$check = $chmod & 0700;
				} else {
					$perms = fileperms($path) & 0777;
					$check = $chmod;
				}
				if (setup::userAuthorized() && $updatechmod) {
					@chmod($path, $chmod);
					clearstatcache();
					$perms = fileperms($path) & 0777;
					if (!setup::checkPermissions($perms, $chmod)) {
						if (array_key_exists($perms & 0666 | 4, $_zp_setup_permission_names)) {
							$perms_class = $_zp_setup_permission_names[$perms & 0666 | 4];
						} else {
							$perms_class = gettext('unknown');
						}
						if (array_key_exists($chmod & 0666 | 4, $_zp_setup_permission_names)) {
							$chmod_class = $_zp_setup_permission_names[$chmod & 0666 | 4];
						} else {
							$chmod_class = gettext('unknown');
						}
						return setup::checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [permissions failure]'), $which, $f), sprintf(gettext('Setup could not change the file permissions from <em>%1$s</em> (<code>0%2$o</code>) to <em>%3$s</em> (<code>0%4$o</code>). You will have to set the permissions manually. See the <a href="https://www.zenphoto.org/news/troubleshooting-zenphoto#29">Troubleshooting guide</a> for details on Zenphoto permissions requirements.'), $perms_class, $perms, $chmod_class, $chmod));
					} else {
						if ($recurse) {
							?>
							<script>
								$.ajax({
									type: 'POST',
									cache: false,
									url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/setup/setup_permissions_changer.php',
									data: 'folder=<?php echo $path; ?>&key=<?php echo sha1(filemtime(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)); ?>'
								});
							</script>
							<?php
						}
					}
				}
				break;
			case 'in_webpath':
				$webpath = $_SERVER['SCRIPT_NAME'];
				if (empty($webpath)) {
					$serverroot = $_zp_setup_serverpath;
				} else {
					$i = strpos($webpath, '/' . ZENFOLDER);
					$webpath = substr($webpath, 0, $i);
					$serverroot = substr($_zp_setup_serverpath, 0, strpos($_zp_setup_serverpath, $webpath));
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
				return setup::checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist]'), $which, $append), $msg);
			} else {
				return setup::checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist and <strong>setup</strong> could not create it]'), $which, $append), $msg);
			}
		} else if (!is_writable($path)) {
			$msg = sprintf(gettext('Change the permissions on the <code>%1$s</code> folder to be writable by the server (<code>chmod 777 %2$s</code>)'), $which, $append);
			return setup::checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> is not writeable and <strong>setup</strong> could not make it so]'), $which, $append), $msg);
		} else {
			return setup::checkMark(true, sprintf(gettext('<em>%1$s</em> folder%2$s'), $which, $f), '', '');
		}
	}

	/**
	 * compares versions for required, desired version levels
	 * @param $required
	 * @param $desired
	 * @param $found
	 */
	static function versionCheck($required, $desired, $found) {
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
	 * file lister for setup
	 * @param $pattern
	 * @param $flags
	 */
	static function glob($pattern, $flags = 0) {
		$split = explode('/', $pattern);
		$match = array_pop($split);
		$path_return = $path = implode('/', $split);
		if (empty($path)) {
			$path = '.';
		} else {
			$path_return = $path_return . '/';
		}

		if (($dir = opendir($path)) !== false) {
			$glob = array();
			while (($file = readdir($dir)) !== false) {
				if (setup::fnmatch($match, $file)) {
					if ((is_dir("$path/$file")) || (!($flags & GLOB_ONLYDIR))) {
						if ($flags & GLOB_MARK)
							$file .= '/';
						$glob[] = $path_return . $file;
					}
				}
			}
			closedir($dir);
			if (!($flags & GLOB_NOSORT))
				sort($glob);
			return $glob;
		} else {
			return array();
		}
	}

	/**
	 * pattern match function in case it is not included in PHP
	 *
	 * @param string $pattern pattern
	 * @param string $string haystack
	 * @return bool
	 */
	static function fnmatch($pattern, $string) {
		if (!function_exists('fnmatch')) {
			return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
		} else {
			return fnmatch($pattern, $string);
		}
	}

	/**
	 * drop-down for character set selection
	 * @param $select
	 */
	static function charsetSelector($select) {
		global $_zp_utf8;
		$selector = '<select id="FILESYSTEM_CHARSET" name="FILESYSTEM_CHARSET" >';
		$selector .= '<option value ="unknown">' . gettext('Unknown') . '</option>';
		$totalsets = $_zp_utf8->charsets;
		ksort($totalsets);
		foreach ($totalsets as $key => $char) {
			$selector .= '	<option value="' . $key . '"';
			if ($key == $select) {
				$selector .= ' selected="selected"';
			}
			$selector .= '>' . $key . '</option>';
		}
		$selector .= '</select>';
		$selector .= '<span class="buttons" style="float: right"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span>';
		return $selector;
	}

	/**
	 * 
	 * @global type $_zp_utf8
	 * @param type $permission_names
	 * @param type $select
	 * @return string
	 */
	static function permissionsSelector($permission_names, $select) {
		$select = $select | 4;
		global $_zp_utf8;
		$selector = '<select id="chmod_permissions" name="chmod_permissions" >';
		$c = 0;
		foreach ($permission_names as $key => $permission) {
			$selector .= '	<option value="' . $c . '"' . ($select == $key ? ' selected="selected"' : '') . '>' . sprintf(gettext('%1$s (0%2$o)'), $permission_names[$key], $key) . '</option>';
			$c++;
		}
		$selector .= '</select>';
		$selector .= '<span class="buttons" style="float: right;"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span><br class="clearall" />';
		return $selector;
	}

	/**
	 * 
	 * @global type $_zp_setup_debug
	 * @global type $_zp_mutex
	 * @global type $_zp_setup_chmod
	 * @param type $message
	 * @param type $anyway
	 * @param type $reset
	 */
	static function log($message, $anyway = false, $reset = false) {
		global $_zp_setup_debug, $_zp_mutex, $_zp_setup_chmod;
		if ($_zp_setup_debug || $anyway) {
			if (is_object($_zp_mutex))
				$_zp_mutex->lock();
			if (!file_exists(dirname(SETUPLOG))) {
				mkdir_recursive(dirname(SETUPLOG), $_zp_setup_chmod | 0311);
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
				clearstatcache();
			}
			if (is_object($_zp_mutex))
				$_zp_mutex->unlock();
		}
	}

	/**
	 * prints the language selector
	 */
	static function languageSelector() {
		$languages = generateLanguageList();
		if (isset($_REQUEST['locale'])) {
			$locale = sanitize($_REQUEST['locale']);
			if (getOption('locale') != $locale || getOption('unsupported_' . $locale)) {
				?>
				<div class="errorbox">
					<h2>
						<?php printf(gettext('<em>%s</em> is not available.'), html_encode($languages[$locale])); ?>
						<?php printf(gettext('The locale %s is not supported on your server.'), html_encode($locale)); ?>
						<br />
						<?php echo gettext('See the <a href="https://www.zenphoto.org/news/troubleshooting-zenphoto#24">troubleshooting guide</a> on zenphoto.org for details.'); ?>
					</h2>
				</div>
				<?php
			}
		}
		?>
		<ul class="sflags">
			<?php
			$_languages = generateLanguageList();
			ksort($_languages, SORT_LOCALE_STRING);
			$currentValue = getOption('locale');
			foreach ($_languages as $text => $lang) {
				if (setup::locale($lang)) {
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
	
	/**
	 * Generates an XSRFtoken
	 * 
	 * @since 1.6
	 * 
	 * @return string
	 */
	static function getXSRFToken() {
		$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		return sha1(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE . $zp_cfg . session_id());
	}

	/**
	 * Validates an XSRFtoken
	 */
	static function XSRFDefender() {
		if (!isset($_REQUEST['xsrfToken']) || setup::getXSRFToken() != $_REQUEST['xsrfToken']) {
			?>
			<p class="errorbox" >
				<?php echo gettext('An attempt at cross site reference forgery has been blocked.') ?>
			</p>
			<?php
			exit();
		}
	}

	/**
	 * 
	 * @param type $input_string
	 * @param type $sanitize_level
	 * @return type
	 */
	static function sanitize($input_string, $sanitize_level = 3) {
		if (is_array($input_string)) {
			foreach ($input_string as $output_key => $output_value) {
				$output_string[$output_key] = setup::sanitize_string($output_value, $sanitize_level);
			}
			unset($output_key, $output_value);
		} else {
			$output_string = setup::sanitize_string($input_string, $sanitize_level);
		}
		return $output_string;
	}

	/**
	 * 
	 * @param type $input_string
	 * @param type $sanitize_level
	 * @return type
	 */
	static function sanitize_string($input_string, $sanitize_level) {
		if ($sanitize_level === 0) {
			$input_string = str_replace(chr(0), " ", $input_string);
		} else {
			$input_string = strip_tags($input_string);
		}
		return $input_string;
	}

	/**
	 * Returns true if we are running on a Windows server
	 *
	 * @return bool
	 */
	static function isWin() {
		return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
	}

	/**
	 * Returns true if we are running on a Macintosh
	 */
	static function isMac() {
		return strtoupper(PHP_OS) == 'DARWIN';
	}

	static function checkPermissions($actual, $expected) {
		if (setup::isWin()) {
			return ($actual & 0700) == ($expected & 0700); //	with windows owner==group==public
		} else {
			return ($actual & 0770) == ($expected & 0770); //	We do not care about the execute permissions
		}
	}

	static function folderPermissions($folder) {
		$files = array();
		if (($dir = opendir($folder)) !== false) {
			while (($file = readdir($dir)) !== false) {
				if ($file != '.' && $file != '..') {
					$files[] = $file;
				}
			}
			closedir($dir);
		}
		foreach ($files as $file) {
			$path = $folder . '/' . $file;
			if (is_dir($path)) {
				@chmod($path, FOLDER_MOD);
				clearstatcache();
				if (setup::checkPermissions(fileperms($path) & 0777, FOLDER_MOD)) {
					if (!setup::folderPermissions($path)) {
						return false;
					}
				} else {
					return false;
				}
			} else {
				@chmod($path, FILE_MOD);
				clearstatcache();
				if (!setup::checkPermissions(fileperms($path) & 0777, FILE_MOD)) {
					return false;
				}
			}
		}
		return true;
	}

	/*
	 * check if site is closed for proper update of .htaccess
	 */

	static function siteClosed($ht) {
		if (empty($ht)) {
			return false;
		} else {
			preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|', $ht, $matches);
			return !(empty($matches)) && strpos($matches[0], '#') === false;
		}
	}

	/**
	 * if site was closed, keep it that way....
	 */
	static function closeSite($nht) {
		preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|', $nht, $matches);
		foreach ($matches[0] as $match) {
			$nht = str_replace($match, ' ' . substr($match, 1), $nht);
		}
		return $nht;
	}

	static function acknowledge($value) {
		global $_zp_conf_vars;
		$link = WEBPATH . '/' . ZENFOLDER . '/setup/index.php?security_ack=' . ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) | $value) . '&amp;xsrfToken=' . setup::getXSRFToken();
		return sprintf(gettext('Click <a href="%s">here</a> to acknowledge that you wish to ignore this issue. It will then become a warning.'), $link);
	}

	static function configMod() {
		$mod = 0600;
		$str = '';
		while (empty($str)) {
			@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod);
			$str = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
			if ($mod == 0666) {
				break;
			}
			$mod = $mod | $mod >> 3;
		}
		return $str;
	}

	static function printFooter() {
		?>
		<br class="clearall" />
		</div><!-- content -->
		</div><!-- main -->
		<div id="footer">
			<a href="https://www.zenphoto.org" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('ZenphotoCMS - The simpler media website CMS'); ?>">zen<strong>photo</strong></a>
			| <a href="https://forum.zenphoto.org/" target="_blank" rel="noopener noreferrer" title=" <?php echo gettext('Forum'); ?>"><?php echo gettext('Forum'); ?></a>
			| <a href="https://github.com/zenphoto/zenphoto/issues" target="_blank" rel="noopener noreferrer" title="Bugtracker">Bugtracker </a> | <a href="https://www.zenphoto.org/news/category/changelog" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('View Change log'); ?>"><?php echo gettext('View Change log'); ?></a>
		</div>
		</body>
		</html>
		<?php
	}

	static function userAuthorized() {
		global $_zp_db;
		if (is_object($_zp_db) && $_zp_db->hasTable('administrators') && function_exists('zp_loggedin')) {
			return zp_loggedin(ADMIN_RIGHTS);
		} else {
			return true; //	in a primitive environment
		}
	}

	static function updateConfigfile($zp_cfg) {
		$mod1 = fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777;
		$mod2 = fileperms(SERVERPATH . '/' . DATA_FOLDER) & 0777;

		@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, 0777);
		if (is_writeable(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
			rename(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $backkup = SERVERPATH . '/' . DATA_FOLDER . '/' . str_replace(strrchr(CONFIGFILE, "."), '', CONFIGFILE) . '.bak.php');
			chmod($backkup, $mod1);
			if ($handle = fopen(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, 'w')) {
				if (fwrite($handle, $zp_cfg)) {
					setup::Log(gettext("Updated configuration file"));
					$base = true;
				}
			}
			fclose($handle);
			clearstatcache();
		}
		@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod2);
	}

	static function checkUnique($table, $unique) {
		global $_zp_setup_autorun, $_zp_db;
		$sql = 'SHOW KEYS FROM ' . $table;
		$result = $_zp_db->queryFullArray($sql);
		foreach ($result as $key) {
			if (!$key['Non_unique']) {
				unset($unique[$key['Column_name']]);
			}
		}
		if (!empty($unique)) {
			$_zp_setup_autorun = false;
			?>
			<p class="notebox">
				<?php
				printf(gettext('<strong>Warning:</strong> the <code>%s</code> table appears not to have a proper <em>UNIQUE</em> key. There are probably duplicate entries in the table which can cause unpredictable behavior. This can normally be corrected by creating a Zenphoto backup, dropping the table, running setup to restore the table, and then restoring from the backup. Note, however, that the duplicate entries will be lost.'), trim($table, '`'));
				?>
			</p>
			<?php
		} else {
			echo '<img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="">';
		}
	}

	static function mkdir_r($pathname, $mode) {
		if (!is_dir(dirname($pathname))) {
			setup::mkdir_r(dirname($pathname), $mode);
		}
		return is_dir($pathname) || @mkdir($pathname, $mode);
	}

	static function locale($locale) {
		global $_zp_rtl_css;
		$en1 = LOCAL_CHARSET;
		$en2 = str_replace('ISO-', 'ISO', $en1);
		$simple = str_replace('_', '-', $locale);
		$simple = explode('-', $simple);
		$try[$locale . '.UTF8'] = $locale . '.UTF8';
		$try[$locale . '.UTF-8'] = $locale . '.UTF-8';
		$try[$locale . '.@euro'] = $locale . '.@euro';
		$try[$locale . '.' . $en2] = $locale . '.' . $en2;
		$try[$locale . '.' . $en1] = $locale . '.' . $en1;
		$try[$locale] = $locale;
		$try[$simple[0]] = $simple[0];
		$try['NULL'] = NULL;
		$rslt = setlocale(LC_ALL, $try);
		$_zp_rtl_css = in_array(substr($rslt, 0, 2), array('fa', 'ar', 'he', 'hi', 'ur'));
		return $rslt;
	}

	static function defaultOptionsRequest($name, $type = 'plugin') {
		global $_zp_conf_vars;
		$curloptions = array();
		switch ($type) {
			case 'modrewrite':
				$uri = FULLWEBPATH . '/' . $_zp_conf_vars['special_pages']['page']['rewrite'] . '/setup_set-mod_rewrite?z=setup';
				$curloptions = array(
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 2000,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_POSTREDIR => 3
				);
				break;
			case 'plugin':
				$uri = FULLWEBPATH . '/' . ZENFOLDER . '/setup/setup_pluginOptions.php?plugin=' . $name;
				break;
			case 'theme':
				$uri = FULLWEBPATH . '/' . ZENFOLDER . '/setup/setup_themeOptions.php?theme=' . $name;
				break;
		}
		if (function_exists('curl_init')) {
			$uri .= '&returnmode';
			$success = curlRequest($uri, $curloptions);
			if ($success) {
				$image = FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png';
			} else {
				$image = FULLWEBPATH . '/' . ZENFOLDER . '/images/fail.png';
			}
			if ($success) {
				?>
				<span>
					<img src="<?php echo $image; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" height="16px" width="16px" /> 
				</span>
				<?php
			} else {
				?>
				<p class="error">
					<img src="<?php echo $image; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" height="16px" width="16px" /> <?php echo $name; ?> 
				</p>
				<?php
			}
		} else {
			?>
			<span>
				<img src="<?php echo $uri; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" height="16px" width="16px" />
			</span>
			<?php
		}
	}
	
	/**
	 * Checks if the server software is Apache or Nginx 
	 * 
	 * @since 1.5.7
	 * @return boolean
	 */
	static function checkServerSoftware() {
		$serversoftware = setup::getServerSoftware();
		if(in_array($serversoftware, array('apache', 'nginx'))) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns either "apache" or "nginx" for supported server software or the full $_SERVER['SERVER_SOFTWARE'] info
	 * 
	 * @since 1.5.7 
	 * @return string
	 */
	static function getServerSoftware() {
		if(stristr($_SERVER['SERVER_SOFTWARE'], "apache")) {
			return 'apache';
		} else if(stristr($_SERVER['SERVER_SOFTWARE'], "nginx")) {
			return 'nginx';
		} else {
			return $_SERVER['SERVER_SOFTWARE'];
		}
	}
	
	/**
	 * Returns a <ul> list with list entry containing <code> enclosed text
	 * @param array $array One dimensional array
	 */
	static function getFileList($array) {
		$list = '';
		if ($array) {
			$list .= '<ul class="setup_filelist">';
			foreach ($array as $entry) {
				$list .= '<li><code>' . $entry . '</code></li>';
			}
			$list .= '</ul>';
		}
		return $list;
	}
	
	/**
	 * Checks to see if access was through a secure protocol
	 * 
	 * @since 1.5.8 Doubles the function of the same name in functions-basic.php as not available on fresh installs
	 * 
	 * @return bool
	 */
	static function secureServer() {
		if (isset($_SERVER['HTTPS'])) {
			if ('on' == strtolower($_SERVER['HTTPS'])) {
				return true;
			}
			if ('1' == $_SERVER['HTTPS']) {
				return true;
			}
		} elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
			return true;
		} elseif (isset($_SERVER['HTTP_FORWARDED']) && preg_match("/^(.+[,;])?\s*proto=https\s*([,;].*)$/", strtolower($_SERVER['HTTP_FORWARDED']))) {
			return true;
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']))) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if we are updating from an earlier version or reinstalling and returns an array with various info for the checkmark box
	 * 
	 * @since 1.6
	 * 
	 * @return array
	 */
	static function checkPreviousVersion() {
		preg_match('/[0-9,\.]*/', ZENPHOTO_VERSION, $matches);
		$current_version = $matches[0];
		$zp_versions = setup::getVersions();
		$installsignature = getOption('zenphoto_install');
		if ($installsignature) {
			$install = unserialize($installsignature);
			$prev_version = $install['ZENPHOTO'];
		} else {
			$prev_version = '';
		}
		$skipped_releases = 0;
		$release_message = $release_text ='';
		if (empty($prev_version)) {
			$zenphoto_release = getOption('zenphoto_release');
			if (is_null($zenphoto_release)) { // freshinstall
				$prev_version = '';
				$check = -2;
				$release_text = sprintf(gettext('Installing Zenphoto v%s'), $current_version);
				$release_message = '';
				$upgrade_text = false;
			} else {
				// pre 1.4.2 release, compute the version
				$install = unserialize(getOption('zenphoto_release'));
				if (is_array($install)) {
					$prev_version = $install['ZENPHOTO'];
				}
				if (empty($prev_version)) {
					$release_text = gettext('Upgrade from before Zenphoto v1.2');
					$prev_version = '1.x';
					$skipped_releases = count($zp_versions);
					$check = -1;
					$upgrade_text = gettext('Update');
				}
			}
		} else {
			preg_match('/[0-9,\.]*/', $prev_version, $matches2); // catch old version with extra info in brackets
			$prev_version = $matches2[0];
			
			if (empty($prev_version)) { // must be fresh install
				$check = -2;
				$release_text = sprintf(gettext('Installing Zenphoto v%s'), $current_version);
				$release_message = '';
				$upgrade_text = false;
			} else if (version_compare($current_version, $prev_version, '==')) {
				// same version
				$skipped_releases = 0;
				$check = -2;
				$release_text = gettext('Reinstalling current Zenphoto release');
				$upgrade_text = gettext('reinstall');
			} else if (version_compare($current_version, $prev_version, '>')) {
				// previous version
				$skipped_releases = 0;
				$zp_versions = array_reverse($zp_versions);
				foreach ($zp_versions as $zp_version) {
					if (version_compare($prev_version, $zp_version, '==')) {
						break;
					} else {
						$skipped_releases++;
					}
				}
				$release_text_extra = '';
				if ($skipped_releases) {
					$check = -1;
					$release_text_extra = ' ' . sprintf(ngettext('[%u release skipped]', '[%u releases skipped]', $skipped_releases), $skipped_releases);
					$release_message = gettext('We do not test upgrades that skip releases. We recommend you upgrade in sequence.');
				} else {
					$check = -2;
				}
				$release_text = sprintf(gettext('Upgrade from Zenphoto v%s'), $prev_version) . $release_text_extra;
				$upgrade_text = gettext('Update');
			} else if (version_compare($current_version, $prev_version, '<')) {
				// just in case we really catch a downgrade…
				$check = 0; // fail!
				$release_text = sprintf(gettext('Downgrade to Zenphoto v%s'), $current_version);
				$release_message = gettext('Downgrades are not recommended and supported and can have serious unwanted side effects especially regarding database changes in newer versions.');
				$upgrade_text = gettext('Downgrade');
			}
		}
		return array(
				'check' => $check,
				'previous_version' => $prev_version,
				'current_version' => $current_version,
				'release_text' => $release_text,
				'message_text' => $release_message,
				'upgrade_text' => $upgrade_text,
				'skipped_release' => $skipped_releases
		);
	}

	/**
	 * Returns am array with all release versions since 1.2
	 * 
	 * Note: THis needs to be kept current and updated with the previous version with every release
	 * 
	 * @since 1.6
	 * 
	 * @return array
	 */
	static function getVersions() {
		return array(
				'1.2.0',
				'1.2.1',
				'1.2.2',
				'1.2.3',
				'1.2.4',
				'1.2.5_RC1',
				'1.2.5_RC2',
				'1.2.5',
				'1.2.6_RC1',
				'1.2.6_RC2',
				'1.2.6',
				'1.2.7',
				'1.2.8_RC1',
				'1.2.8',
				'1.2.9',
				'1.3.0',
				'1.3.1',
				'1.3.1.1',
				'1.3.1.2',
				'1.4.0',
				'1.4.0.1',
				'1.4.0.2',
				'1.4.0.3',
				'1.4.0.4',
				'1.4.1.1',
				'1.4.1.2',
				'1.4.1.3',
				'1.4.1.4',
				'1.4.1.5',
				'1.4.1.6',
				'1.4.1',
				'1.4.2',
				'1.4.2.1',
				'1.4.2.2',
				'1.4.2.3',
				'1.4.2.4',
				'1.4.3',
				'1.4.3.1',
				'1.4.3.2',
				'1.4.3.3',
				'1.4.3.4',
				'1.4.3.5',
				'1.4.4b',
				'1.4.4',
				'1.4.4.1',
				'1.4.4.1a',
				'1.4.4.1b',
				'1.4.4.2',
				'1.4.4.3',
				'1.4.4.4',
				'1.4.4.5',
				'1.4.4.6',
				'1.4.4.7',
				'1.4.4.8',
				'1.4.4.9',
				'1.4.5',
				'1.4.5.1',
				'1.4.5.2',
				'1.4.5.3',
				'1.4.5.4',
				'1.4.5.5',
				'1.4.5.6',
				'1.4.5.7',
				'1.4.5.8',
				'1.4.5.9',
				'1.4.5.10',
				'1.4.6-RC1',
				'1.4.6-RC2',
				'1.4.6',
				'1.4.7',
				'1.4.8',
				'1.4.9',
				'1.4.10',
				'1.4.11',
				'1.4.12',
				'1.4.13',
				'1.4.14',
				'1.5',
				'1.5.1',
				'1.5.2',
				'1.5.3',
				'1.5.4',
				'1.5.5',
				'1.5.6',
				'1.5.7',
				'1.5.8',
				'1.5.9',
				'1.6',
				'1.6.1',
				'1.6.2',
				'1.6.3'
		);
	}

}

// class setup end 

