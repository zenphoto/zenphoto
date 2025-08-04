<?php 
/**
 * Theme related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

/**
	 * Check if a theme is editable (ie not a bundled theme)
	 *
	 * @param $theme theme to check
	 * @return bool
	 * @since 1.3
	 */
	function themeIsEditable($theme) {
		if (function_exists('readlink')) {
			$link = @readlink(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme);
		} else {
			$link = '';
		}
		if (empty($link) || str_replace('\\', '/', $link) == SERVERPATH . '/' . THEMEFOLDER . '/' . $theme) {
			$zplist = getSerializedArray(getOption('Zenphoto_theme_list'));
			return (!in_array($theme, $zplist));
		} else {
			return false;
		}
	}

	function zenPhotoTheme($theme) {
		$zplist = getSerializedArray(getOption('Zenphoto_theme_list'));
		return (in_array($theme, $zplist));
	}

	/**
	 * Copy a theme directory to create a new custom theme
	 *
	 * @param $source source directory
	 * @param $target target directory
	 * @return bool|string either true or an error message
	 * @author Ozh
	 * @since 1.3
	 */
	function copyThemeDirectory($source, $target, $newname) {
		global $_zp_current_admin_obj, $_zp_graphics;
		$message = true;
		$source = str_replace(array('../', './'), '', $source);
		$target = str_replace(array('../', './'), '', $target);
		$source = SERVERPATH . '/themes/' . internalToFilesystem($source);
		$target = SERVERPATH . '/themes/' . internalToFilesystem($target);

		// If the target theme already exists, nothing to do.
		if (is_dir($target)) {
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Directory “%s” already exists!'), basename($target));
		}

		// If source dir is missing, exit too
		if (!is_dir($source)) {
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Cannot find theme directory “%s” to copy!'), basename($source));
		}

		// We must be able to write to the themes dir.
		if (!is_writable(dirname($target))) {
			return gettext('Cannot create new theme.') . ' ' . gettext('The <tt>/themes</tt> directory is not writable!');
		}

		// We must be able to create the directory
		if (!mkdir($target, FOLDER_MOD)) {
			return gettext('Cannot create new theme.') . ' ' . gettext('Could not create directory for the new theme');
		}
		@chmod($target, FOLDER_MOD);

		// Get a list of files to copy: get all files from the directory, remove those containing '/.svn/'		
		$source_files = array_filter(listDirectoryFiles($source), function ($str) {
			return strpos($str, "/.svn/") === false;
		});

		// Determine nested (sub)directories structure to create: go through each file, explode path on "/"
		// and collect every unique directory
		$dirs_to_create = array();
		foreach ($source_files as $path) {
			$path = explode('/', dirname(str_replace($source . '/', '', $path)));
			$dirs = '';
			foreach ($path as $subdir) {
				if ($subdir == '.svn' or $subdir == '.') {
					continue 2;
				}
				$dirs = "$dirs/$subdir";
				$dirs_to_create[$dirs] = $dirs;
			}
		}

		// Create new directory structure
		foreach ($dirs_to_create as $dir) {
			mkdir("$target/$dir", FOLDER_MOD);
			@chmod("$target/$dir", FOLDER_MOD);
		}

		// Now copy every file
		foreach ($source_files as $file) {
			$newfile = str_replace($source, $target, $file);
			if (!copy("$file", "$newfile"))
				return sprintf(gettext("An error occurred while copying files. Please delete manually the new theme directory “%s” and retry or copy files manually."), basename($target));
			@chmod("$newfile", FOLDER_MOD);
		}

		// Rewrite the theme header.
		if (file_exists($target . '/theme_description.php')) {
			$theme_description = array();
			require($target . '/theme_description.php');
			$theme_description['desc'] = sprintf(gettext('Your theme, based on theme %s'), $theme_description['name']);
		} else {
			$theme_description['desc'] = gettext('Your theme');
		}
		$theme_description['name'] = $newname;
		$theme_description['author'] = $_zp_current_admin_obj->getUser();
		$theme_description['version'] = '1.0';
		$theme_description['date'] = date('Y-m-d H:m:s', time());

		$description = sprintf('<' . '?php
				// Zenphoto theme definition file
				$theme_description["name"] = "%s";
				$theme_description["author"] = "%s";
				$theme_description["version"] = "%s";
				$theme_description["date"] = "%s";
				$theme_description["desc"] = "%s";
				?' . '>', html_encode($theme_description['name']), html_encode($theme_description['author']), html_encode($theme_description['version']), html_encode($theme_description['date']), html_encode($theme_description['desc']));

		$f = fopen($target . '/theme_description.php', 'w');
		if ($f !== FALSE) {
			@fwrite($f, $description);
			fclose($f);
			$message = gettext('New custom theme created successfully!');
		} else {
			$message = gettext('New custom theme created, but its description could not be updated');
		}

		// Make a slightly custom theme image
		if (file_exists("$target/theme.png")) {
			$themeimage = "$target/theme.png";
		} else if (file_exists("$target/theme.gif")) {
			$themeimage = "$target/theme.gif";
		} else if (file_exists("$target/theme.jpg")) {
			$themeimage = "$target/theme.jpg";
		} else {
			$themeimage = false;
		}
		if ($themeimage) {
			if ($im = $_zp_graphics->imageGet($themeimage)) {
				$x = $_zp_graphics->imageWidth($im) / 2 - 45;
				$y = $_zp_graphics->imageHeight($im) / 2 - 10;
				$text = "CUSTOM COPY";
				$font = $_zp_graphics->imageLoadFont();
				$ink = $_zp_graphics->colorAllocate($im, 0x0ff, 0x0ff, 0x0ff);
				// create a blueish overlay
				$overlay = $_zp_graphics->createImage($_zp_graphics->imageWidth($im), $_zp_graphics->imageHeight($im));
				$back = $_zp_graphics->colorAllocate($overlay, 0x060, 0x060, 0x090);
				$_zp_graphics->imageFill($overlay, 0, 0, $back);
				// Merge theme image and overlay
				$_zp_graphics->imageMerge($im, $overlay, 0, 0, 0, 0, $_zp_graphics->imageWidth($im), $_zp_graphics->imageHeight($im), 45);
				// Add text
				$_zp_graphics->writeString($im, $font, $x - 1, $y - 1, $text, $ink);
				$_zp_graphics->writeString($im, $font, $x + 1, $y + 1, $text, $ink);
				$_zp_graphics->writeString($im, $font, $x, $y, $text, $ink);
				// Save new theme image
				$_zp_graphics->imageOutput($im, 'png', $themeimage);
			}
		}

		return $message;
	}

	/**
	 * Deletes a theme
	 * 
	 * @param string $source  Full serverpath of the theme
	 * @return boolean
	 */
	function deleteThemeDirectory($source) {
		if (is_dir($source)) {
			$result = true;
			$handle = opendir($source);
			while (false !== ($filename = readdir($handle))) {
				$fullname = $source . '/' . $filename;
				if (is_dir($fullname)) {
					if (($filename != '.') && ($filename != '..')) {
						$result = $result && deleteThemeDirectory($fullname);
					}
				} else {
					if (file_exists($fullname)) {
						@chmod($fullname, 0777);
						$result = $result && unlink($fullname);
					}
				}
			}
			closedir($handle);
			$result = $result && rmdir($source);
			return $result;
		}
		return false;
	}
	
	
/**
 * Returns an array of "standard" theme scripts. This list is
 * normally used to exclude these scripts form various option seletors.
 *
 * @return array
 */
function standardScripts() {
	$standardlist = array(
			'themeoptions',
			'password',
			'theme_description',
			'404', 'slideshow',
			'search', 'image',
			'index', 'album',
			'customfunctions',
			'functions',
			'footer',
			'sidebar',
			'header',
			'inc-footer',
			'inc-header'
	);
	if (extensionEnabled('zenpage')) {
		$standardlist = array_merge($standardlist, array('news', 'pages'));
	}
	return $standardlist;
}

/**
 * returns an array of the theme scripts not in the exclude array
 * @param array $exclude those scripts to ignore
 * @return array
 */
function getThemeFiles($exclude) {
	global $_zp_gallery;
	$files = array();
	foreach (array_keys($_zp_gallery->getThemes()) as $theme) {
		$curdir = getcwd();
		$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/';
		chdir($root);
		$filelist = safe_glob('*.php');
		$list = array();
		foreach ($filelist as $file) {
			if (!in_array($file, $exclude)) {
				$files[$theme][] = filesystemToInternal($file);
			}
		}
		chdir($curdir);
	}
	return $files;
}


/**
 * Helper for the theme editor
 * @param type $file
 * @return type
 */
function isTextFile($file) {
	$ok_extensions = array('css', 'txt');
	if (zp_loggedin(ADMIN_RIGHTS)) {
		$ok_extensions = array('css', 'php', 'js', 'txt');
	}
	$path_info = pathinfo($file);
	$ext = (isset($path_info['extension']) ? strtolower($path_info['extension']) : '');
	return (!empty($ok_extensions) && (in_array($ext, $ok_extensions) ) );
}
