<?php 
/**
 * Various helper admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

/**
 * Unzips an image archive
 *
 * @param file $file the archive
 * @param string $dir where the images go
 */
function unzip($file, $dir) { //check if zziplib is installed
	global $_zp_current_admin_obj;
	if (class_exists('ziparchive')) {
		$zip = new ZipArchive();
		$zip_valid = $zip->open($file);
		if ($zip_valid === true) {
			for ($i = 0; $entry = $zip->statIndex($i); $i++) {
				$fname = $entry['name']; 
				$seoname = internalToFilesystem(seoFriendly($fname));
				if (stripos($seoname, '__macosx-._') === false) {
					if (Gallery::validImage($seoname) || Gallery::validImageAlt($seoname)) {
						$buf = $zip->getFromName($fname);
						$path_file = str_replace("/", DIRECTORY_SEPARATOR, $dir . '/' . $seoname);
						$fp = fopen($path_file, "w");
						fwrite($fp, $buf);
						fclose($fp);
						clearstatcache();
						$albumname = substr($dir, strlen(ALBUM_FOLDER_SERVERPATH));
						$album = AlbumBase::newAlbum($albumname);
						$image = Image::newImage($album, $seoname);
						if ($fname != $seoname) {
							$image->setTitle($fname);
							$image->setLastChangeUser($_zp_current_admin_obj->getLoginName());
							$image->save();
						}
					}
				}
			}
			return $zip->close();
		}
	} else {
		debuglog(gettext('Zip archive could not be extracted because PHP <code>ZipArchive</code> support is not available'));
		return false;
	}
}

/**
 * Checks for a zip file
 *
 * @param string $filename name of the file
 * @return bool
 */
function is_zip($filename) {
	$ext = getSuffix($filename);
	return ($ext == "zip");
}

/**
	 * Extracts and returns a 'statement' from a PHP script so that it may be 'evaled'
	 *
	 * @param string $target the assignment variable to match on
	 * @param string $str the PHP script
	 * @return string
	 */
	function isolate($target, $str) {
		if (preg_match('|' . preg_quote($target) . '\s*?=(.+?);[ \f\v\t]*[\n\r]|s', $str, $matches)) {
			return $matches[0];
		}
		return false;
	}

	/**
	 * Return an array of files from a directory and sub directories
	 *
	 * This is a non recursive function that digs through a directory. More info here:
	 * @link http://planetozh.com/blog/2005/12/php-non-recursive-function-through-directories/
	 *
	 * @param string $dir directory
	 * @return array
	 * @author Ozh
	 * @since 1.3
	 */
	function listDirectoryFiles($dir) {
		$file_list = array();
		$stack[] = $dir;
		while ($stack) {
			$current_dir = array_pop($stack);
			if ($dh = @opendir($current_dir)) {
				while (($file = @readdir($dh)) !== false) {
					if ($file !== '.' AND $file !== '..') {
						$current_file = "{$current_dir}/{$file}";
						if (is_file($current_file) && is_readable($current_file)) {
							$file_list[] = "{$current_dir}/{$file}";
						} elseif (is_dir($current_file)) {
							$stack[] = $current_file;
						}
					}
				}
			}
		}
		return $file_list;
	}
	
	/**
	 * Return URL of current admin page
	 *
	 * @return string current URL
	 * @author Ozh
	 * @since 1.3
	 *
	 * @param string $source the script file
	 */
	function currentRelativeURL() {
		$source = str_replace(SERVERPATH, WEBPATH, str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
		if (empty($_GET)) {
			$q = '';
		} else {
			$q = '?' . http_build_query($_GET);
		}
		return pathurlencode($source) . $q;
	}
	
	


/**
 * Standard admin pages checks
 * @param bit $rights
 * @param string $return--where to go after login
 */
function admin_securityChecks($rights, $return) {
	global $_zp_current_admin_obj, $_zp_loggedin;
	checkInstall();
	httpsRedirect();

	if ($_zp_current_admin_obj && $_zp_current_admin_obj->reset) {
		$_zp_loggedin = USER_RIGHTS;
	}
	if (!zp_loggedin($rights)) {
		// prevent nefarious access to this page.
		$returnurl = urldecode($return);
		if (!zp_apply_filter('admin_allow_access', false, $returnurl)) {
			$uri = explode('?', $returnurl);
			redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $uri[0], '302');
		}
	}
}


/**
 * Strips off quotes from the strng
 * @param $string
 */
function unQuote($string) {
	$string = trim($string);
	$q = $string[0];
	if ($q == '"' || $q == "'") {
		$string = trim($string, $q);
	}
	return $string;
}