<?php
/**
 *
 * Zenphoto site cloner
 *
 * @package admin
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once(SERVERPATH.'/'.ZENFOLDER.'/reconfigure.php');

admin_securityChecks(NULL, currentRelativeURL());
XSRFdefender('cloneZenphoto');

$msg = array();
$folder = sanitize($_GET['clonePath']);
$path = str_replace(WEBPATH,'/',SERVERPATH);
$newinstall = trim(str_replace($path, '', $folder),'/').'/';

if (trim($folder,'/') == SERVERPATH) {
	$msg[] = gettext('You attempted to clone to the master install.');
	$success = false;
} else {
	$success = true;
	$targets = array(ZENFOLDER=>'dir', USER_PLUGIN_FOLDER=>'dir', 'index.php'=>'file');
	$zplist = $_zp_gallery->getThemes();
	foreach ($zplist as $theme=>$data) {
		$targets[THEMEFOLDER.'/'.$theme] = 'dir';
	}
	foreach (array(internalToFilesystem('charset_tést'),internalToFilesystem('charset.tést')) as $charset) {
		if (file_exists(SERVERPATH.'/'.DATA_FOLDER.'/'.$charset)) {
			$targets[DATA_FOLDER.'/'.$charset] = 'file';
		}
	}

	if (!is_dir($folder.DATA_FOLDER)) {
		@mkdir($folder.DATA_FOLDER);
	}
	if (!is_dir($folder.THEMEFOLDER)) {
		@mkdir($folder.THEMEFOLDER);
	}

	foreach ($targets as $target=>$type) {
		if (file_exists($folder.$target)) {
			$link = str_replace('\\', '/', @readlink($folder.$target));
			switch ($type) {
				case 'dir':
					if (empty($link) || $link == $folder.$target) {
						// an actual folder
						if (zpFunctions::removeDir($folder.$target)) {
							if (SYMLINK && @symlink(SERVERPATH.'/'.$target, $folder.$target)) {
								$msg[] = sprintf(gettext('The existing folder <code>%s</code> was replaced.'), $folder.filesystemToInternal($target))."<br />\n";
							} else {
								$msg[] = sprintf(gettext('The existing folder <code>%1$s</code> was removed but Link creation failed.'),$target)."<br />\n";
								$success = false;
							}
						} else {
							$msg[] = sprintf(gettext('The existing folder <code>%s</code> could not be removed.'), $folder.filesystemToInternal($target))."<br />\n";
							$success = false;
						}
					} else {
						// is a symlink
						@chmod($folder.$target, 0777);
						$success = @rmdir($folder.$target);
						if (!$success) {	// some systems treat it as a dir, others as a file!
							$success = @unlink($folder.$target);
						}
						if ($success) {
							if (SYMLINK && @symlink(SERVERPATH.'/'.$target, $folder.$target)) {
								$msg[] = sprintf(gettext('The existing symlink <code>%s</code> was replaced.'), $folder.filesystemToInternal($target))."<br />\n";
							} else {
								$msg[] = sprintf(gettext('The existing symlink <code>%s</code> was removed but Link creation failed.'),$target)."<br />\n";
								$success = false;
							}
						} else {
							$msg[] = sprintf(gettext('The existing symlink <code>%s</code> could not be removed.'), $folder.filesystemToInternal($target))."<br />\n";
							$success = false;
						}
					}
					break;
				case 'file':
					@chmod($folder.$target, 0777);
					if (@unlink($folder.$target)) {
						if (SYMLINK && @symlink(SERVERPATH.'/'.$target, $folder.$target)) {
							if ($folder.$target == $link) {
								$msg[] = sprintf(gettext('The existing file <code>%s</code> was replaced.'), $folder.filesystemToInternal($target))."<br />\n";
							} else {
								$msg[] = sprintf(gettext('The existing symlink <code>%s</code> was replaced.'), $folder.filesystemToInternal($target))."<br />\n";
							}
						} else {
							$msg[] = sprintf(gettext('The existing file <code>%s</code> was removed but Link creation failed.'),$target)."<br />\n";
							$success = false;
						}
					} else {
						if ($folder.$target == $link) {
							$msg[] = sprintf(gettext('The existing file <code>%s</code> could not be removed.'), $folder.filesystemToInternal($target))."<br />\n";
						} else {
							$msg[] = sprintf(gettext('The existing symlink <code>%s</code> could not be removed.'), $folder.filesystemToInternal($target))."<br />\n";
						}
						$success = false;
					}
					break;
			}
		} else {
			if (SYMLINK && @symlink(SERVERPATH.'/'.$target, $folder.$target)) {
				$msg[] = sprintf(gettext('<code>%s</code> Link created.'),$target)."<br />\n";
			} else {
				$msg[] = sprintf(gettext('<code>%s</code> Link creation failed.'),$target)."<br />\n";
				$success = false;
			}
		}
	}
}
if ($success) {
	array_unshift($msg, '<h2>'.sprintf(gettext('Successful clone to %s'),$folder).'</h2>'."\n");
	list($diff, $needs) = checkSignature(false);
	if (empty($needs)) {
		if (WEBPATH) {
			$rootpath = str_replace(WEBPATH,'/',SERVERPATH);
			$urlpath = str_replace(WEBPATH,'/',FULLWEBPATH);
		} else {
			$rootpath = SERVERPATH.'/';
			$urlpath = FULLWEBPATH.'/';
		}

		if (substr($folder,0,strlen($rootpath)) == $rootpath) {
			$msg[] = '<p><span class="buttons"><a href="'.$urlpath.$newinstall.ZENFOLDER.'/setup/index.php?autorun">'.gettext('setup the new install').'</a></span><br class="clearall" /></p>'."\n";
		}
	} else {
		$reinstall = '<p>'.sprintf(gettext('Before running setup for <code>%1$s</code> please reinstall the following setup files from the %2$s [%3$s] to this installation:'),$newinstall,ZENPHOTO_VERSION,ZENPHOTO_RELEASE).
								"\n".'<ul>'."\n";
		if (!empty($needs)) {
				foreach ($needs as $script) {
					$reinstall .= '<li>'.ZENFOLDER.'/setup/'.$script.'</li>'."\n";
			}
		}
		$reinstall .=	'</ul></p>'."\n";
		$msg[] = $reinstall;
	}
} else {
	array_unshift($msg, '<h2>'.sprintf(gettext('Clone to <code>%s</code> failed'),$folder).'</h2>');
}
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/cloneTab.php');

?>