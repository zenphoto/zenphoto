<?php
/**
 *
 * A plugin to generate a file download list.
 * This download folder can be relative to your Zenphoto root (<i>foldername</i>) or external to it (<i>../foldername</i>).
 * By default the <var>%UPLOAD_FOLDER%</var> folder is chosen so you can use the file manager to manage those files.
 *
 * You can also override that folder by using the <var>printdownloadList()</var> function parameters directly. Additionally
 * you can set a downloadlink to a specific file directly as well using <code>printDownloadLink(<i>path-to-file</i>);<code>.
 *
 * The file names and the download path of the items are stored with the number of downloads in the database's plugin_storage table.
 *
 * The download link is something like:
 * <var>www.yourdomain.com/download.php?file=<i>id number of the download</i></var>.
 *
 * So the actual download source is not public. The list itself is generated directly from the file system. However,
 * files which no longer exist are
 * kept in the database for statistical reasons until you clear them manually via the statistics utility.
 *
 * You will need to modify your theme to use this plugin. You can use the codeblock fields if your theme supports them or
 * insert the function calls directly where you want the list to appear.
 *
 * To protect the download directory from direct linking you need to set up a proper <var>.htaccess</var> for this folder.
 *
 * The <var>printDownloadLinkAlbumZip()</var> function will create a zipfile of the album <i>on the fly</i>.
 * The source of the images may be the original
 * images from the album and its subalbums or they may be the <i>sized</i> images from the cache. Use the latter if you want
 * the images to be watermarked.
 *
 * The list has a CSS class <var>downloadList</var> attached.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage tools
 * @tags "file download", "download manager", download
 */
$plugin_is_filter = 20 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("Plugin to generate file download lists.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

$option_interface = "downloadList";

zp_register_filter('admin_utilities_buttons', 'DownloadList::button');

/**
 * Plugin option handling class
 *
 */
class DownloadList {

	function __construct() {
		setOptionDefault('downloadList_directory', UPLOAD_FOLDER);
		setOptionDefault('downloadList_showfilesize', 1);
		setOptionDefault('downloadList_showdownloadcounter', 1);
		setOptionDefault('downloadList_user', NULL);
		setOptionDefault('downloadList_password', getOption('downloadList_pass'));
		setOptionDefault('downloadList_hint', NULL);
		setOptionDefault('downloadList_rights', NULL);
		setOptionDefault('downloadList_zipFromCache', 0);
	}

	function getOptionsSupported() {
		$options = array(gettext('Download directory')											 => array('key'		 => 'downloadList_directory', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("This download folder can be relative to your Zenphoto installation (<em>foldername</em>) or external to it (<em>../foldername</em>)! You can override this setting by using the parameter of the printdownloadList() directly on calling.")),
						gettext('Show filesize of download items')				 => array('key'		 => 'downloadList_showfilesize', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 3,
										'desc'	 => ''),
						gettext('Show download counter of download items') => array('key'		 => 'downloadList_showdownloadcounter', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 4,
										'desc'	 => ''),
						gettext('Files to exclude from the download list') => array('key'		 => 'downloadList_excludesuffixes', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 5,
										'desc'	 => gettext('A list of file suffixes to exclude. Separate with comma and omit the dot (e.g "jpg").')),
						gettext('Zip source')															 => array('key'			 => 'downloadList_zipFromCache', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 6,
										'buttons'	 => array(gettext('From album')	 => 0, gettext('From Cache')	 => 1),
										'desc'		 => gettext('Make the album zip from the album folder or from the sized images in the cache.')),
						gettext('User rights')														 => array('key'		 => 'downloadList_rights', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 1,
										'desc'	 => gettext('Check if users are required to have <em>file</em> rights to download.'))
		);
		if (GALLERY_SECURITY == 'public') {
			$options[gettext('credentials')] = array('key'		 => 'downloadlist_credentials', 'type'	 => OPTION_TYPE_CUSTOM,
							'order'	 => 0,
							'desc'	 => gettext('Provide credentials to password protect downloads'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$user = getOption('downloadList_user');
		$x = getOption('downloadList_password');
		$hint = getOption('downloadList_hint');
		?>
		<input type="hidden" name="password_enabled_downloadList" id="password_enabled_downloadList" value="0" />
		<p class="password_downloadListextrashow">
			<a href="javascript:toggle_passwords('_downloadList',true);">
				<?php echo gettext("Password:"); ?>
			</a>
			<?php
			if (empty($x)) {
				?>
				<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/lock_open.png" alt="" class="icon-postiion-top8" />
				<?php
			} else {
				$x = '          ';
				?>
				<a onclick="resetPass('_downloadList');" title="<?php echo gettext('clear password'); ?>"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/lock.png"  alt="" class="icon-postiion-top8" /></a>
				<?php
			}
			?>
		</p>
		<div class="password_downloadListextrahide" style="display:none">
			<a href="javascript:toggle_passwords('_downloadList',false);">
				<?php echo gettext("Guest user:"); ?>
			</a>
			<br />
			<input type="text" size="27" id="user_name_downloadList" name="user_downloadList"
						 onkeydown="passwordClear('_downloadList');"
						 value="<?php echo html_encode($user); ?>" />
			<br />
			<span id="strength_downloadList"><?php echo gettext("Password:"); ?></span>
			<br />
			<input type="password" size="27"
						 id="pass_downloadList" name="pass_downloadList"
						 onkeydown="passwordClear('_downloadList');"
						 onkeyup="passwordStrength('_downloadList');"
						 value="<?php echo $x; ?>" />
			<label><input type="checkbox" name="disclose_password_downloadList" id="disclose_password_downloadList" onclick="passwordClear('_downloadList');
					togglePassword('_downloadList');"><?php echo gettext('Show password'); ?></label>
			<br />
			<span class="password_field__downloadList">
				<span id="match_downloadList"><?php echo gettext("(repeat)"); ?></span>
				<br />
				<input type="password" size="27"
							 id="pass_r_downloadList" name="pass_r_downloadList" disabled="disabled"
							 onkeydown="passwordClear('_downloadList');"
							 onkeyup="passwordMatch('_downloadList');"
							 value="<?php echo $x; ?>" />
				<br />
			</span>
			<?php echo gettext("Password hint:"); ?>
			<br />
			<?php print_language_string_list($hint, 'hint_downloadList', false, NULL, 'hint_downloadList', 27); ?>
		</div>
		<?php
	}

	static function handleOptionSave($themename, $themealbum) {
		$notify = processCredentials('downloadList', '_downloadList');
		if ($notify == '?mismatch=user') {
			return gettext('You must supply a password for the DownloadList user');
		} else if ($notify) {
			return gettext('Your DownloadList passwords were empty or did not match');
		} else {
			return '';
		}
	}

	/**
	 * Updates the download count entry when processing a download. For internal use.
	 * @param string $path Path of the download item
	 * @param bool $nocountupdate false if the downloadcount should not be increased and only the entry be added to the db if it does not already exist
	 */
	static function updateListItemCount($path) {
		$checkitem = query_single_row("SELECT `data` FROM " . prefix('plugin_storage') . " WHERE `aux` = " . db_quote($path) . " AND `type` = 'downloadList'");
		if ($checkitem) {
			$downloadcount = $checkitem['data'] + 1;
			query("UPDATE " . prefix('plugin_storage') . " SET `data` = " . $downloadcount . ", `type` = 'downloadList' WHERE `aux` = " . db_quote($path) . " AND `type` = 'downloadList'");
		}
	}

	/**
	 * Adds a new download item to the database. For internal use.
	 * @param string $path Path of the download item
	 */
	static function addListItem($path) {
		$checkitem = query_single_row("SELECT `data` FROM " . prefix('plugin_storage') . " WHERE `aux` = " . db_quote($path) . " AND `type` = 'downloadList'");
		if (!$checkitem) {
			query("INSERT INTO " . prefix('plugin_storage') . " (`type`,`aux`,`data`) VALUES ('downloadList'," . db_quote($path) . ",'0')");
		}
	}

	/*	 * Gets the download items from all download items from the database. For internal use in the downloadList functions.
	 * @return array
	 */

	static function getListItemsFromDB() {
		$downloaditems = query_full_array("SELECT id, `aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'downloadList'");
		return $downloaditems;
	}

	/*	 * Gets the download items from all download items from the database. For internal use in the downloadlink functions.
	 * @return array
	 */

	static function getListItemFromDB($file) {
		$downloaditem = query_single_row($sql = "SELECT id, `aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'downloadList' AND `aux` = " . db_quote($file));
		return $downloaditem;
	}

	/**
	 * Gets the id of a download item from the database for the download link. For internal use.
	 * @param string $path Path of the download item (without WEBPATH)
	 * @return bool|string
	 */
	static function getItemID($path) {
		$path = sanitize($path);
		$downloaditem = query_single_row("SELECT id, `aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'downloadList' AND `aux` = " . db_quote($path));
		if ($downloaditem) {
			return $downloaditem['id'];
		} else {
			return false;
		}
	}

	/**
	 * @param array $array List of download items
	 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
	 * @return array
	 */
	static function printListArray($array, $listtype = 'ol') {
		if ($listtype != 'ol' || $listtype != 'ul') {
			$listtype = 'ol';
		}
		$filesize = '';
		foreach ($array as $key => $file) {
			?>
			<li>
				<?php
				if (is_array($file)) { // for sub directories
					echo $key;
				} else {
					printDownloadLink($file);
				}
				if (is_array($file)) {
					echo '<' . $listtype . '>';
					self::printListArray($file, $listtype);
					echo '</' . $listtype . '>';
				}
				?>
			</li>
			<?php
		}
	}

	/**
	 * Admin overview button for download statistics utility
	 */
	static function button($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Info'),
						'enable'			 => true,
						'button_text'	 => gettext('Download statistics'),
						'formname'		 => 'downloadstatistics_button',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php',
						'icon'				 => WEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
						'title'				 => gettext('Counts of downloads'),
						'alt'					 => '',
						'hidden'			 => '',
						'rights'			 => ADMIN_RIGHTS,
		);
		return $buttons;
	}

}

class AlbumZip {

	/**
	 * generates an array of filenames to zip
	 * recurses into the albums subalbums
	 *
	 * @param object $album album object to add
	 * @param int $base the length of the base album name
	 */
	static function AddAlbum($album, $base, $filebase) {
		global $_zp_zip_list, $zip_gallery;
		$albumbase = substr($album->name, $base) . '/';
		foreach ($album->sidecars as $suffix) {
			$f = $albumbase . $album->name . '.' . $suffix;
			if (file_exists(internalToFilesystem($f))) {
				$_zp_zip_list[$filebase . $f] = $f;
			}
		}
		$images = $album->getImages();
		foreach ($images as $imagename) {
			$image = newImage($album, $imagename);
			$f = $albumbase . $image->filename;
			$_zp_zip_list[$filebase . internalToFilesystem($f)] = $f;
			$imagebase = stripSuffix($image->filename);
			foreach ($image->sidecars as $suffix) {
				$f = $albumbase . $imagebase . '.' . $suffix;
				if (file_exists($f)) {
					$_zp_zip_list[$filebase . $f] = $f;
				}
			}
		}
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			$subalbum = newAlbum($albumname);
			if ($subalbum->exists && !$album->isDynamic()) {
				self::AddAlbum($subalbum, $base, $filebase);
			}
		}
	}

	/**
	 * generates an array of cachefilenames to zip
	 * recurses into the albums subalbums
	 *
	 * @param object $album album object to add
	 * @param int $base the length of the base album name
	 */
	static function AddAlbumCache($album, $base, $filebase) {
		global $_zp_zip_list, $zip_gallery, $defaultSize;
		$albumbase = substr($album->name, $base) . '/';
		$images = $album->getImages();
		foreach ($images as $imagename) {
			$image = newImage($album, $imagename);
			$uri = $image->getSizedImage($defaultSize);
			if (strpos($uri, 'i.php?') === false) {
				$f = $albumbase . $image->filename;
				$c = $albumbase . basename($uri);
				$_zp_zip_list[$filebase . $c] = $f;
			}
		}
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			$subalbum = newAlbum($albumname);
			if ($subalbum->exists && !$album->isDynamic()) {
				self::AddAlbumCache($subalbum, $base, $filebase);
			}
		}
	}

	/**
	 * Emits a page error. Used for attempts to bypass password protection
	 *
	 * @param string $err error code
	 * @param string $text error message
	 *
	 */
	static function pageError($err, $text) {
		header("HTTP/1.0 " . $err . ' ' . $text);
		header("Status: " . $err . ' ' . $text);
		echo "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>	<title>" . $err . " - " . $text . "</TITLE>	<META NAME=\"ROBOTS\" CONTENT=\"NOINDEX, FOLLOW\"></head>";
		echo "<BODY bgcolor=\"#ffffff\" text=\"#000000\" link=\"#0000ff\" vlink=\"#0000ff\" alink=\"#0000ff\">";
		echo "<FONT face=\"Helvitica,Arial,Sans-serif\" size=\"2\">";
		echo "<b>" . sprintf(gettext('Page error: %2$s (%1$s)'), $err, $text) . "</b><br /><br />";
		echo "</body></html>";
		exitZP();
	}

	/**
	 * Creates a zip file of the album
	 *
	 * @param string $albumname album folder
	 * @param bool fromcache if true, images will be the "sized" image in the cache file
	 */
	static function create($albumname, $fromcache) {
		global $_zp_zip_list, $_zp_gallery, $defaultSize;
		$album = newAlbum($albumname);
		if (!$album->isMyItem(LIST_RIGHTS) && !checkAlbumPassword($albumname)) {
			self::pageError(403, gettext("Forbidden"));
		}
		if (!$album->exists) {
			self::pageError(404, gettext('Album not found'));
		}
		$_zp_zip_list = array();
		if ($fromcache) {
			$opt = array('large_file_size'	 => 5 * 1024 * 1024, 'comment'					 => sprintf(gettext('Created from cached images of %1$s on %2$s.'), $album->name, zpFormattedDate(DATE_FORMAT, time())));
			loadLocalOptions(false, $_zp_gallery->getCurrentTheme());
			$defaultSize = getOption('image_size');
			self::AddAlbumCache($album, strlen($albumname), SERVERPATH . '/' . CACHEFOLDER . '/' . $albumname);
		} else {
			$opt = array('large_file_size'	 => 5 * 1024 * 1024, 'comment'					 => sprintf(gettext('Created from images in %1$s on %2$s.'), $album->name, zpFormattedDate(DATE_FORMAT, time())));
			self::AddAlbum($album, strlen($albumname), SERVERPATH . '/' . ALBUMFOLDER . '/' . $albumname);
		}
		$zip = new ZipStream($albumname . '.zip', $opt);
		foreach ($_zp_zip_list as $path => $file) {
			@set_time_limit(6000);
			$zip->add_file_from_path(internalToFilesystem($file), internalToFilesystem($path));
		}
		$zip->finish();
	}

}

$request = getRequestURI();
if (strpos($request, '?') === false) {
	define('DOWNLOADLIST_LINKPATH', FULLWEBPATH . '/' . substr($request, strlen(WEBPATH) + 1) . '?download=');
} else {
	define('DOWNLOADLIST_LINKPATH', FULLWEBPATH . '/' . substr($request, strlen(WEBPATH) + 1) . '&download=');
}
unset($request);

/**
 * Prints the actual download list included all subfolders and files
 * @param string $dir An optional different folder to generate the list that overrides the folder set on the option.
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @param array $filters an array of files to exclude from the list. Standard items are Array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn')
 * @param array $excludesuffixes an array of file suffixes (without trailing dot to exclude from the list (e.g. "jpg")
 * @param string $sort 'asc" or "desc" (default) for alphabetical ascending or descending list
 */
function printdownloadList($dir = '', $listtype = 'ol', $filters = array(), $excludesuffixes = '', $sort = 'desc') {
	if ($listtype != 'ol' || $listtype != 'ul') {
		$listtype = 'ol';
	}
	$files = getdownloadList($dir, $filters, $excludesuffixes, $sort);
	echo '<' . $listtype . ' class="downloadList">';
	DownloadList::printListArray($files, $listtype);
	echo '</' . $listtype . '>';
}

/**
 * Gets the actual download list included all subfolders and files
 * @param string $dir8 An optional different folder to generate the list that overrides the folder set on the option.
 * 										This could be a subfolder of the main download folder set on the plugin's options. You have to include the base directory as like this:
 * 										"folder" or "folder/subfolder" or "../folder"
 * 										You can also set any folder within or without the root of your Zenphoto installation as a download folder with this directly
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @param array $filters8 an array of files to exclude from the list. Standard items are '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'
 * @param array $excludesuffixes an array of file suffixes (without trailing dot to exclude from the list (e.g. "jpg")
 * @param string $sort 'asc" or "desc" (default) for alphabetical ascending or descending list
 * @return array
 */
function getdownloadList($dir8, $filters8, $excludesuffixes, $sort) {
	$filters = Array('.', '..', '.DS_Store', 'Thumbs.db', '.htaccess', '.svn');
	foreach ($filters8 as $key => $file) {
		$filters[$key] = internalToFilesystem($file);
	}
	if (empty($dir8)) {
		$dir = SERVERPATH . '/' . getOption('downloadList_directory');
	} else {
		if (substr($dir8, 0, 1) == '/' || strpos($dir8, ':') !== false) {
			$dir = internalToFilesystem($dir8);
		} else {
			$dir = SERVERPATH . '/' . internalToFilesystem($dir8);
		}
	}
	if (empty($excludesuffixes)) {
		$excludesuffixes = getOption('downloadList_excludesuffixes');
	}
	if (empty($excludesuffixes)) {
		$excludesuffixes = array();
	} elseif (!is_array($excludesuffixes)) {
		$excludesuffixes = explode(',', $excludesuffixes);
	}
	if ($sort == 'asc') {
		$direction = 0;
	} else {
		$direction = 1;
	}
	$dirs = array_diff(scandir($dir, $direction), $filters);
	$dir_array = Array();
	if ($sort == 'asc') {
		natsort($dirs);
	}
	foreach ($dirs as $file) {
		if (is_dir(internalToFilesystem($dir) . '/' . $file)) {
			$dirN = filesystemToInternal($dir) . "/" . filesystemToInternal($file);
			$dir_array[$file] = getdownloadList($dirN, $filters8, $excludesuffixes, $sort);
		} else {
			if (!in_array(getSuffix($file), $excludesuffixes)) {
				$dir_array[$file] = $dir . '/' . filesystemToInternal($file);
			}
		}
	}
	return $dir_array;
}

/**
 * Gets the download url for a file
 * @param string $file the path to a file to get a download link.
 */
function getDownloadLink($file) {
	DownloadList::addListItem($file); // add item to db if not already exists without updating the counter
	$link = '';
	if ($id = DownloadList::getItemID($file)) {
		$link = DOWNLOADLIST_LINKPATH . $id;
	}
	return $link;
}

/**
 * Prints a download link for a file, depending on the plugin options including the downloadcount and filesize
 * @param string $file the path to a file to print a download link.
 * @param string $linktext Optionally how you wish to call the link. Set/leave  to NULL to use the filename.
 */
function printDownloadLink($file, $linktext = NULL) {
	if (substr($file, 0, 1) != '/' && strpos($file, ':') === false) {
		$file = SERVERPATH . '/' . getOption('downloadList_directory') . '/' . $file;
	}

	$filesize = '';
	if (getOption('downloadList_showfilesize')) {
		$filesize = filesize(internalToFilesystem($file));
		$filesize = ' (' . byteConvert($filesize) . ')';
	}
	if (getOption('downloadList_showdownloadcounter')) {
		$downloaditem = DownloadList::getListItemFromDB($file);
		if ($downloaditem) {
			$downloadcount = ' - ' . sprintf(ngettext('%u download', '%u downloads', $downloaditem['data']), $downloaditem['data']);
		} else {
			$downloadcount = ' - 0 downloads';
		}
		$filesize .= $downloadcount;
	}
	if (empty($linktext)) {
		$filename = basename($file);
	} else {
		$filename = $linktext;
	}
	echo '<a href="' . html_encode(getDownloadLink($file)) . '" rel="nofollow">' . html_encode($filename) . '</a><small>' . $filesize . '</small>';
}

/**
 *
 * Prints a download link for an album zip of the current album (therefore to be used only on album.php/image.php).
 * This function only creates a download count and then redirects to the original Zenphoto album zip download.
 *
 * @param string $linktext
 * @param object $albumobj
 * @param bool $fromcache if true get the images from the cache
 */
function printDownloadLinkAlbumZip($linktext = NULL, $albumobj = NULL, $fromcache = NULL) {
	global $_zp_current_album;
	if (is_null($albumobj)) {
		$albumobj = $_zp_current_album;
	}
	if (!is_null($albumobj) && !$albumobj->isDynamic()) {
		$file = $albumobj->name . '.zip';
		DownloadList::addListItem($file);
		if (getOption('downloadList_showdownloadcounter')) {
			$downloaditem = DownloadList::getListItemFromDB($file);
			if ($downloaditem) {
				$downloadcount = ' - ' . sprintf(ngettext('%u download', '%u downloads', $downloaditem['data']), $downloaditem['data']);
			} else {
				$downloadcount = ' - 0 downloads';
			}
			$filesize = '<small>' . $downloadcount . '</small>';
		} else {
			$filesize = '';
		}
		if (!empty($linktext)) {
			$file = $linktext;
		}
		$link = DOWNLOADLIST_LINKPATH . pathurlencode($albumobj->name) . '&albumzip';
		if ($fromcache) {
			$link .= '&fromcache';
		}
		echo '<a href="' . html_encode($link) . '" rel="nofollow">' . html_encode($file) . '</a>' . $filesize;
	}
}

/**
 * Process any download requests
 */
if (isset($_GET['download'])) {
	$item = sanitize($_GET['download']);
	if (empty($item) OR !extensionEnabled('downloadList')) {
		zp_error(gettext('Forbidden'));
	}
	$hash = getOption('downloadList_password');
	if (GALLERY_SECURITY != 'public' || $hash) {
		//	credentials required to download
		if (!zp_loggedin((getOption('downloadList_rights')) ? FILES_RIGHTS : ALL_RIGHTS)) {
			$user = getOption('downloadList_user');
			zp_handle_password('download_auth', $hash, $user);
			if (!empty($hash) && zp_getCookie('download_auth') != $hash) {
				$show = ($user) ? true : NULL;
				$hint = get_language_string(getOption('downloadList_hint'));
				printPasswordForm($hint, true, $show, '?download=' . $item);
				exitZP();
			}
		}
	}
	if (isset($_GET['albumzip'])) {
		DownloadList::updateListItemCount($item . '.zip');
		require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-zipStream.php');
		if (isset($_GET['fromcache'])) {
			$fromcache = sanitize($isset($_GET['fromcache']));
		} else {
			$fromcache = getOption('downloadList_zipFromCache');
		}
		AlbumZip::create($item, $fromcache);
		exitZP();
	} else {
		require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-MimeTypes.php');
		$cd = getcwd();
		$item = sanitize_numeric($item);
		$path = query_single_row("SELECT `aux` FROM " . prefix('plugin_storage') . " WHERE id=" . $item);
		$file = internalToFilesystem($path['aux']);
		if (file_exists($file)) {
			DownloadList::updateListItemCount($file);
			$ext = getSuffix($file);
			$mimetype = getMimeString($ext);
			header('Content-Description: File Transfer');
			header('Content-Type: ' . $mimetype);
			header('Content-Disposition: attachment; filename=' . basename(urldecode($file)));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			flush();
			readfile($file);
			exitZP();
		}
	}
}
?>