<?php
/**
 * setup support functions
 * @package setup
 */
// force UTF-8 Ø
require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');

$const_webpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$serverpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
preg_match('~(.*)/(' . ZENFOLDER . ')~', $const_webpath, $matches);
if (empty($matches)) {
	$const_webpath = '';
} else {
	$const_webpath = $matches[1];
	$serverpath = substr($serverpath, 0, strrpos($serverpath, '/' . ZENFOLDER));
}

define('SETUPLOG', $serverpath . '/' . DATA_FOLDER . '/setup.log');
if (!defined('SERVERPATH'))
	define('SERVERPATH', $serverpath);

require_once(dirname(dirname(__FILE__)) . '/functions-config.php');

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentZPFiles($folder, $lcFilesystem = false) {
	global $_zp_resident_files;
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if ($file != '.' && $file != '..') {
			if (is_dir($folder . '/' . $file)) {
				if ($file != 'session') {
					getResidentZPFiles($folder . '/' . $file, $lcFilesystem);
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
	<div id="prime<?php echo++$primeid; ?>" class="error"><?php printf(gettext('Testing %s.'), $text); ?></div>
	<?php
}

function checkMark($check, $text, $text2, $msg, $stopAutorun = true) {
	global $warn, $moreid, $primeid, $autorun;
	$classes = array('fail' => gettext('Fail: '), 'warn' => gettext('Warn: '), 'pass' => gettext('Pass: '));
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
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
						$moreid++;
						?>
						<?php
						if ($check == -3) {
							?>
							<a href="javascript:toggle_visibility('more<?php echo $moreid; ?>');">
								<?php echo gettext('<strong>Warning!</strong> click for details'); ?>
							</a>
							<div class="warning" id="more<?php echo $moreid; ?>" style="display: none">
								<h1><?php echo gettext('Warning!'); ?></h1>
								<?php
							} else {
								?>
								<a href="javascript:toggle_visibility('more<?php echo $moreid; ?>');">
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
		<li class="<?php echo $cls; ?>"><?php echo $text; ?></li>
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
	global $serverpath, $permission_names;
	$path = str_replace('\\', '/', $path);
	if (!is_dir($path) && $class == 'std') {
		mkdir_recursive($path, $chmod);
	}
	switch ($class) {
		case 'std':
			$append = trim(str_replace($serverpath, '', $path), '/');
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
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [permissions failure]'), $which, $f), sprintf(gettext('Setup could not change the file permissions from <em>%1$s</em> (<code>0%2$o</code>) to <em>%3$s</em> (<code>0%4$o</code>). You will have to set the permissions manually. See the <a href="http://www.zenphoto.org/news/troubleshooting-zenphoto#29">Troubleshooting guide</a> for details on Zenphoto permissions requirements.'), $perms_class, $perms, $chmod_class, $chmod));
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
				$serverroot = $serverpath;
			} else {
				$i = strpos($webpath, '/' . ZENFOLDER);
				$webpath = substr($webpath, 0, $i);
				$serverroot = substr($serverpath, 0, strpos($serverpath, $webpath));
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
 * file lister for setup
 * @param $pattern
 * @param $flags
 */
function setup_glob($pattern, $flags = 0) {
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
			if (fnmatch($match, $file)) {
				if ((is_dir("$path/$file")) || (!($flags & GLOB_ONLYDIR))) {
					if ($flags & GLOB_MARK)
						$file.='/';
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

if (!function_exists('fnmatch')) {

	/**
	 * pattern match function in case it is not included in PHP
	 *
	 * @param string $pattern pattern
	 * @param string $string haystack
	 * @return bool
	 */
	function fnmatch($pattern, $string) {
		return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*'	 => '.*', '?'	 => '.?')) . '$/i', $string);
	}

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
		$selector .= '>' . $key . '</option>';
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
	$selector .= '<span class="buttons" style="float: right;"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span><br class="clearall" />';
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
			clearstatcache();
		}
		if (is_object($_zp_mutex))
			$_zp_mutex->unlock();
	}
}

function setupLanguageSelector() {
	global $xsrftoken;
	$languages = generateLanguageList();
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale']);
		if (getOption('locale') != $locale) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'), html_encode($languages[$locale])); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), html_encode($locale)); ?>
					<br />
					<?php echo gettext('See the <a href="http://www.zenphoto.org/news/troubleshooting-zenphoto#24">troubleshooting guide</a> on zenphoto.org for details.'); ?>
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
		?>
	</ul>
	<?php
}

function setupXSRFDefender() {
	global $xsrftoken;
	if (!isset($_REQUEST['xsrfToken']) || $xsrftoken != $_REQUEST['xsrfToken']) {
		?>
		<p class="errorbox" >
			<?php echo gettext('An attempt at cross site reference forgery has been blocked.') ?>
		</p>
		<?php
		exit();
	}
}

function setup_sanitize($input_string, $sanitize_level = 3) {
	if (is_array($input_string)) {
		foreach ($input_string as $output_key => $output_value) {
			$output_string[$output_key] = setup_sanitize_string($output_value, $sanitize_level);
		}
		unset($output_key, $output_value);
	} else {
		$output_string = setup_sanitize_string($input_string, $sanitize_level);
	}
	return $output_string;
}

function setup_sanitize_string($input_string, $sanitize_level) {
	if (get_magic_quotes_gpc())
		$input_string = stripslashes($input_string);
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
function isWin() {
	return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
}

/**
 * Returns true if we are running on a Macintosh
 */
function isMac() {
	return strtoupper(PHP_OS) == 'DARWIN';
}

function checkPermissions($actual, $expected) {
	if (isWin()) {
		return ($actual & 0700) == ($expected & 0700); //	with windows owner==group==public
	} else {
		return ($actual & 0770) == ($expected & 0770); //	We do not care about the execute permissions
	}
}

/*
 * check if site is closed for proper update of .htaccess
 */

function site_closed($ht) {
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
function close_site($nht) {
	preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|', $nht, $matches);
	foreach ($matches[0] as $match) {
		$nht = str_replace($match, ' ' . substr($match, 1), $nht);
	}
	return $nht;
}

function acknowledge($value) {
	global $xsrftoken, $_zp_conf_vars;
	$link = WEBPATH . '/' . ZENFOLDER . '/setup/index.php?security_ack=' . ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) | $value) . '&amp;xsrfToken=' . $xsrftoken;
	return sprintf(gettext('Click <a href="%s">here</a> to acknowledge that you wish to ignore this issue. It will then become a warning.'), $link);
}

function configMod() {
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

function printSetupFooter() {
	echo "<div id=\"footer\">";
	echo "\n  <a href=\"http://www.zenphoto.org\" title=\"" . gettext('The simpler media website CMS') . "\">zen<strong>photo</strong></a>";
	echo " | <a href=\"http://www.zenphoto.org/support/\" title=\"" . gettext('Forum') . '">' . gettext('Forum') . "</a> | <a href=\"https://github.com/zenphoto/zenphoto/issues\" title=\"Bugtracker\">Bugtracker </a> | <a href=\"http://www.zenphoto.org/news/category/changelog\" title=\"" . gettext('View Change log') . "\">" . gettext('Change log') . "</a>\n</div>";
}

function setupUserAuthorized() {
	if (function_exists('zp_loggedin')) {
		return zp_loggedin(ADMIN_RIGHTS);
	} else {
		return true; //	in a primitive environment
	}
}

function updateConfigfile($zp_cfg) {
	global $xsrftoken;
	$mod1 = fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777;
	$mod2 = fileperms(SERVERPATH . '/' . DATA_FOLDER) & 0777;

	@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, 0666);
	if (is_writeable(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
		rename(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $backkup = SERVERPATH . '/' . DATA_FOLDER . '/' . str_replace(strrchr(CONFIGFILE, "."), '', CONFIGFILE) . '.bak.php');
		chmod($backkup, $mod1);
		if ($handle = fopen(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, 'w')) {
			if (fwrite($handle, $zp_cfg)) {
				setupLog(gettext("Updated configuration file"));
				$base = true;
			}
		}
		fclose($handle);
		clearstatcache();
	}
	@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod2);
	$str = configMod();
	$xsrftoken = sha1(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE . $str . session_id());
}
?>
