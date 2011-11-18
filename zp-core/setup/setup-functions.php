<?php
/**
 * setup support functions
 * @package setup
 */

// force UTF-8 Ø
require_once(dirname(dirname(__FILE__)).'/global-definitions.php');

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentZPFiles($folder,  $lcFilesystem=false) {
	global $_zp_resident_files;
	$dir = opendir($folder);
	while(($file = readdir($dir)) !== false) {
		$file = str_replace('\\','/',$file);
		if (strpos($file, '.') !== 0) {
			if (is_dir($folder.'/'.$file)) {
				if ($file != 'session') {
					getResidentZPFiles($folder.'/'.$file, $lcFilesystem);
					$entry = $folder.'/'.$file;
					if ($lcFilesystem) $entry = strtolower($entry);
					$_zp_resident_files[]=$entry;
				}
			} else {
				$entry = $folder.'/'.$file;
				if ($lcFilesystem) $entry = strtolower($entry);
				$_zp_resident_files[]=$entry;
			}
		}
	}
	closedir($dir);
}

function primeMark($text) {
	global $primeid;
	$primeid++;
	?>
	<div id="prime<?php echo $primeid; ?>" class="error"><?php printf(gettext('Testing %s.'), $text); ?></div>
	<?php
}

function checkMark($check, $text, $text2, $msg, $stopAutorun=true) {
	global $warn, $moreid, $primeid,$autorun;
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
	</script>
	<?php
	$dsp = '';
	if ($check > 0) {$check = 1; }
	switch ($check) {
		case 0:
			$dsp = "fail";
			break;
		case -1:
		case -3:
			$dsp = "warn";
			$warn = true;
			if ($stopAutorun) {
				$autorun = false;
			}
			break;
		case 1:
		case -2:
			$dsp = "pass";
			break;
	}
	if ($check <= 0) {
		?>
		<li class="<?php echo $dsp; ?>"><?php
		if (!empty($text2)) {
			echo  $text2;
			$dsp .= ': '.trim($text2);
		} else {
			echo  $text;
			$dsp .= ': '.trim($text);
		}
		if (!empty($msg)) {
			switch ($check) {
				case 0:
					?>
					<div class="error">
						<h1><?php echo gettext('Error!'); ?></h1>
						<p><?php  echo $msg; ?></p>
					</div>
					<?php
					break;
				case -1:
					?>
					<div class="warning">
						<h1><?php echo gettext('Warning!'); ?></h1>
						<p><?php  echo $msg; ?></p>
					</div>
					<?php
					break;
				default:
					$moreid++;
					?>
					<a href="javascript:toggle_visibility('more<?php echo $moreid; ?>');">
						<?php echo gettext('<strong>Notice!</strong> click for details'); ?>
					</a>
					<?php
					if ($check == -3) {
						?>
						<div class="warning" id="more<?php echo $moreid; ?>" style="display: none">
						<h1><?php echo gettext('Warning!'); ?></h1>
						<?php
					} else {
						?>
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
			$dsp .= ' '.trim($msg);
		}
		?>
		</li>
		<?php
	} else {
		?>
		<li class="<?php echo $dsp; ?>"><?php echo $text; ?></li>
		<?php
		$dsp .= ': '.trim($text);
	}
	setupLog($dsp, $check<=0 && $check!=-2 );
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
function folderCheck($which, $path, $class, $relaxation=true, $subfolders=NULL) {
	global $serverpath, $chmod, $permission_names;
	$path = str_replace('\\', '/', $path);
	if (!is_dir($path) && $class == 'std') {
		mkdir_recursive($path, $chmod);
	}
	$serverpath = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
	switch ($class) {
		case 'std':
			$append = str_replace($serverpath, '', $path);
			if (substr($append,-1,1) == '/') $append = substr($append,0, -1);
			if (substr($append,0,1) == '/') $append = substr($append,1);
			if (($append != $which)) {
				$f = " (<em>$append</em>)";
			} else {
				$f = '';
			}
			if (!is_null($subfolders)) {
				$subfolderfailed = '';
				foreach ($subfolders as $subfolder) {
					if (!mkdir_recursive($path.$subfolder, $chmod)) {
						$subfolderfailed .= ', <code>'.$subfolder.'</code>';
					}
				}
				if (!empty($subfolderfailed)) {
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [subfolder creation failure]'),$which, $f), sprintf(gettext('Setup could not create the following subfolders:<br />%s'),substr($subfolderfailed,2)));
				}
			}
			if (isWin()) {
				$perms = $chmod;
			} else {
				$perms = fileperms($path)&0777;
			}
			if (zp_loggedin(ADMIN_RIGHTS) && (($chmod<$perms) || ($relaxation && $chmod!=$perms))) {
				@chmod($path,$chmod);
				clearstatcache();
				if (($perms = fileperms($path)&0777)!=$chmod) {
					if (array_key_exists($perms, $permission_names)) {
						$perms_class = $permission_names[$perms];
					} else {
						$perms_class = gettext('unknown');
					}
					if (array_key_exists($chmod, $permission_names)) {
						$chmod_class = $permission_names[$chmod];
					} else {
						$chmod_class = gettext('unknown');
					}
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [permissions failure]'),$which, $f), sprintf(gettext('Setup could not change the folder permissions from <em>%1$s</em> (<code>0%2$o</code>) to <em>%3$s</em> (<code>0%4$o</code>). You will have to set the permissions manually. See the <a href="http://www.zenphoto.org/news/troubleshooting-zenphoto#29">Troubleshooting guide</a> for details on Zenphoto permissions requirements.'),$perms_class,$perms,$chmod_class,$chmod));
				} else {
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						$.ajax({
							type: 'POST',
							url: '<?php echo WEBPATH.'/'.ZENFOLDER; ?>/setup_permissions_changer.php',
							data: 'folder=<?php echo $path; ?>&key=<?php echo sha1(filemtime(CONFIGFILE).file_get_contents(CONFIGFILE)); ?>'
						});
						// ]]> -->
					</script>
					<?php
				}
			}
			break;
		case 'in_webpath':
			$webpath = $_SERVER['SCRIPT_NAME'];
			if (empty($webpath)) {
				$serverroot = $serverpath;
			} else {
				$i = strpos($webpath, '/'.ZENFOLDER);
				$webpath = substr($webpath, 0, $i);
				$serverroot = substr($serverpath, 0, strpos($serverpath, $webpath));
			}
			$append = substr($path, strlen($serverroot)+1);
			$f = " (<em>$append</em>)";
			break;
		case 'external':
			$append = $path;
			$f = " (<em>$append</em>)";
			break;
	}
	if (!is_dir($path)) {
		$msg = " ".sprintf(gettext('You must create the folder <em>%1$s</em><br /><code>mkdir(%2$s, 0777)</code>.'),$append,substr($path,0,-1));
		if ($class != 'std') {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist]'),$which, $append), $msg);
		} else {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist and <strong>setup</strong> could not create it]'),$which, $append), $msg);
		}
	} else if (!is_writable($path)) {
		$msg =  sprintf(gettext('Change the permissions on the <code>%1$s</code> folder to be writable by the server (<code>chmod 777 %2$s</code>)'),$which,$append);
		return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> is not writeable and <strong>setup</strong> could not make it so]'),$which, $append), $msg);
	} else {
		return checkMark(true, sprintf(gettext('<em>%1$s</em> folder%2$s'),$which, $f), '', '');
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
	$vr = $nr[0]*10000 + $nr[1]*100 + $nr[2];
	$nf = explode(".", $found . '.0.0.0');
	$vf = $nf[0]*10000 + $nf[1]*100 + $nf[2];
	$nd = explode(".", $desired . '.0.0.0');
	$vd = $nd[0]*10000 + $nd[1]*100 + $nd[2];
	if ($vf < $vr) return 0;
	if ($vf < $vd) return -1;
	return 1;
}

/**
 *
 * file lister for setup
 * @param $pattern
 * @param $flags
 */
function setup_glob($pattern, $flags=0) {
	$split=explode('/',$pattern);
	$match=array_pop($split);
	$path_return = $path = implode('/',$split);
	if (empty($path)) {
		$path = '.';
	} else {
		$path_return = $path_return . '/';
	}

	if (($dir=opendir($path))!==false) {
		$glob=array();
		while(($file=readdir($dir))!==false) {
			if (fnmatch($match,$file)) {
				if ((is_dir("$path/$file"))||(!($flags&GLOB_ONLYDIR))) {
					if ($flags&GLOB_MARK) $file.='/';
					$glob[] = $path_return.$file;
				}
			}
		}
		closedir($dir);
		if (!($flags&GLOB_NOSORT)) sort($glob);
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
		return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
	}
}

/**
 *
 * drop-down for character set selection
 * @param $select
 */
function charsetSelector($select) {
	global $_zp_UTF8;
	$selector =	'<select id="FILESYSTEM_CHARSET" name="FILESYSTEM_CHARSET" >';
	$selector .= '<option value ="unknown">'.gettext('Unknown').'</option>';
	$totalsets = $_zp_UTF8->charsets;
	ksort($totalsets);
	foreach ($totalsets as $key=>$char) {
		$selector .= '	<option value="'.$key.'"';
		if ($key == $select) {
			$selector .= ' selected="selected"';
		}
		$selector .= '>'.$key.'</option>';
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right"><button type="submit" alt="'.gettext('change the definition').'"><strong>'.gettext('apply').'</strong></button></span>';
	return $selector;
}

function permissionsSelector($permission_names, $select) {
	global $_zp_UTF8;
	$selector =	'<select id="chmod_permissions" name="chmod_permissions" >';
	$c = 0;
	foreach ($permission_names as $key=>$permission) {
		$selector .= '	<option value="'.$c.'"'.($select==$key?' selected="selected"':'').'>'.sprintf(gettext('%1$s (0%2$o)'),$permission_names[$key],$key).'</option>';
		$c++;
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right;"><button type="submit" alt="'.gettext('change the definition').'"><strong>'.gettext('apply').'</strong></button></span><br clear="all" />';
	return $selector;
}

function setupLog($message, $anyway=false, $reset=false) {
	global $debug, $chmod;
	if ($debug || $anyway) {
		if (!file_exists(dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER)) {
			mkdir_recursive(dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER, $chmod);
		}
		if ($reset) { $mode = 'w'; } else { $mode = 'a'; }
		$path = dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER . '/setup.log';
		$f = fopen($path, $mode);
		if ($f) {
			fwrite($f, strip_tags($message) . "\n");
			fclose($f);
			clearstatcache();
			chmod($path, 0600);
		}
	}
}

/*
 * updates database config parameters
 */
function updateConfigItem($item, $value) {
	global $zp_cfg;
	$i = strpos($zp_cfg, $item);
	if ($i == false) {
		$i = strpos($zp_cfg, '$conf[');
		$zp_cfg = substr($zp_cfg, 0, $i)."\$conf['".$item."'] = '".$value."'; // added by setup.php\n".substr($zp_cfg,$i);
	} else {
		$i = strpos($zp_cfg, '=', $i);
		$j = strpos($zp_cfg, "\n", $i);
		$zp_cfg = substr($zp_cfg, 0, $i) . '= \'' . str_replace('\'', '\\\'',$value) . '\';' . substr($zp_cfg, $j);
	}
}

/**
 *
 * Checks for bad parentIDs from old move/copy bug
 * @param unknown_type $albumname
 * @param unknown_type $id
 */
function checkAlbumParentid($albumname, $id) {
	Global $_zp_gallery;
	$album = new Album($_zp_gallery, $albumname);
	$oldid = $album->get('parentid');
	if ($oldid !== $id) {
		$album->set('parentid', $id);
		$album->save();
		if (is_null($oldid)) $oldid = '<em>NULL</em>';
		if (is_null($id)) $id = '<em>NULL</em>';
		printf('Fixed album <strong>%1$s</strong>: parentid was %2$s should have been %3$s<br />', $albumname,$oldid, $id);
	}
	$id = $album->id;
	if (!$album->isDynamic()) {
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			checkAlbumParentid($albumname, $id);
		}
	}
}

/**
 * helper delete function for setup files.
 *
 * @param string $component
 */
function setupDeleteComponent($rslt, $component) {
	if ($rslt) {
		setupLog(sprintf(gettext('%s deleted.'),$component),true);
		return true;
	} else {
		setupLog(sprintf(gettext('failed to delete %s.'),$component),true);
		return false;
	}
}


function setupLanguageSelector() {
	global $xsrftoken;
	$languages = generateLanguageList();
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale'], 0);
		if (getOption('locale') != $locale) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'),$languages[$locale]); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), $locale); ?>
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
		krsort($_languages,SORT_LOCALE_STRING);
		$currentValue = getOption('locale');
		foreach ($_languages as $text=>$lang) {
			?>
			<li<?php if ($lang==$currentValue) echo ' class="currentLanguage"'; ?>>
				<?php
				if ($lang!=$currentValue) {
					?>
					<a href="javascript:launchScript('',['locale=<?php echo $lang; ?>']);" >
					<?php
				}
				if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png')) {
					$flag = WEBPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png';
				} else {
					$flag = WEBPATH.'/'.ZENFOLDER.'/locale/missing_flag.png';
				}
				?>
				<img src="<?php echo $flag; ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
				<?php
				if ($lang!=$currentValue) {
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
	if (!isset($_REQUEST['xsrfToken']) || $xsrftoken !=$_REQUEST['xsrfToken']) {
		?>
		<p class="errorbox" >
			<?php echo gettext('An attempt at cross site reference forgery has been blocked.')?>
		</p>
		<?php
		exit();
	}
}

function setup_sanitize($input_string, $sanitize_level=3) {
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
	if (get_magic_quotes_gpc()) $input_string = stripslashes($input_string);
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
	return (strtoupper (substr(PHP_OS, 0,3)) == 'WIN' ) ;
}

/**
 * Returns true if we are running on a Macintosh
 */
function isMac() {
	return strtoupper(PHP_OS) =='DARWIN';
}



?>