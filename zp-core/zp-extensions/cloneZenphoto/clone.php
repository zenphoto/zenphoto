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

$folder = sanitize($_GET['clonePath']);
$path = str_replace(WEBPATH,'/',SERVERPATH);
$newinstall = str_replace($path, '', $folder);

$msg = array();
$success = true;

$targets = array(ZENFOLDER=>'dir', THEMEFOLDER=>'dir', USER_PLUGIN_FOLDER=>'dir', 'index.php'=>'file');
foreach ($targets as $target=>$type) {
	if (file_exists($folder.$target)) {
		$link = str_replace('\\', '/', readlink($folder.$target));
		switch ($type) {
			case 'dir':
				if ($link == $folder.$target) {
					// an actual folder
					if (zpFunctions::removeDir($folder.$target)) {
						$msg[] = '<p>'.sprintf(gettext('The existing folder <code>%s</code> was removed.'), $folder.$target)."</p>\n";
					} else {
						$msg[] = '<p>'.sprintf(gettext('The existing folder <code>%s</code> could not be removed.'), $folder.$target)."</p>\n";
						$success = false;
					}
				} else {
					// is a symlink
					if (@rmdir($folder.$target)) {
						$msg[] = '<p>'.sprintf(gettext('The existing symlink <code>%s</code> was removed.'), $folder.$target)."</p>\n";
					} else {
						$msg[] = '<p>'.sprintf(gettext('The existing symlink <code>%s</code> could not be removed.'), $folder.$target)."</p>\n";
						$success = false;
					}
				}
				break;

			case 'file':
				if (@unlink($folder.$target)) {
					if ($folder.$target == $link) {
						$msg[] = '<p>'.sprintf(gettext('The existing file <code>%s</code> was removed.'), $folder.$target)."</p>\n";
					} else {
						$msg[] = '<p>'.sprintf(gettext('The existing symlink <code>%s</code> was removed.'), $folder.$target)."</p>\n";
					}
				} else {
					if ($folder.$target == $link) {
						$msg[] = '<p>'.sprintf(gettext('The existing file <code>%s</code> could not be removed.'), $folder.$target)."</p>\n";
					} else {
						$msg[] = '<p>'.sprintf(gettext('The existing symlink <code>%s</code> could not be removed.'), $folder.$target)."</p>\n";
					}
					$success = false;
				}
				break;
		}
	}
	if (!@symlink(SERVERPATH.'/'.$target, $folder.$target)) {
		$msg[] = '<p>'.sprintf(gettext('Link creation for the <code>%s</code> folder failed.'),$target)."</p>\n";
		$success = false;
	}
}

if ($success) {
	array_unshift($msg, '<h2>'.sprintf(gettext('Successful clone to %s'),$folder).'</h2>'."\n");
	list($diff, $needs) = checkSignature();
	if (empty($needs)) {
		$rootpath = str_replace(WEBPATH,'/',SERVERPATH);
		if (substr($folder,0,strlen($rootpath)) == $rootpath) {
			$msg[] = '<span class="buttons"><a href="/'.$newinstall.ZENFOLDER.'/setup.php">'.gettext('setup the new install').'</a></span><br clear="all">'."\n";
		}
	} else {
		$reinstall = sprintf(gettext('Before running setup for <code>%1$s</code> please reinstall the following setup files from the %2$s [%3$s] to this installation:'),$newinstall,ZENPHOTO_VERSION,ZENPHOTO_RELEASE).
								"\n".'<ul>'."\n";
		if (!file_exists(dirname(__FILE__).'/setup.php')) {
			$reinstall .= '<li>'.ZENFOLDER.'/setup.php</li>'."\n";
		}
		if (!empty($needs)) {
				foreach ($needs as $script) {
					$reinstall .= '<li>'.ZENFOLDER.'/setup/'.$script.'</li>'."\n";
			}
		}
		$reinstall .=	'</ul>'."\n";
		$msg[] = $reinstall;
	}
} else {
	array_unshift($msg, '<h2>'.sprintf(gettext('Clone to <code>%s</code> failed'),$folder).'</h2>');
}
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/cloneTab.php');

?>