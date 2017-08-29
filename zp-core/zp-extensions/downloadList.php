<?php
/**
 *
 * Provides support for file downloads. The files may come from a download folder
 * or from the images on your site.
 *
 * The download folder can be relative to your installation  root (<i>foldername</i>) or external to it (<i>../foldername</i>).
 * By default the <var>%UPLOAD_FOLDER%</var> folder is chosen so you can use the file manager to manage those files.
 *
 * You can also override that folder by using the <var>printdownloadList()</var> function parameters directly. Additionally
 * you can set a downloadlink to a specific file directly by using <code>printDownloadURL(<i>path-to-file</i>);<code>.
 *
 * Use <var>printDownloadAlbumZipURL()</var> function to create a zipfile of an album <i>on the fly</i>.
 * The source of the images may be the original
 * images from the album and its subalbums or it may be the <i>sized</i> images from the cache. Use the latter if you want
 * the images to be watermarked (presuming you have watermarks enabled.)
 *
 * <var>printDownloadSearchZipURL()</var> is similar to <var>printDownloadAlbumZipURL()</var> but makes a zip file of the images
 * that were found by a search. This function works only if there is an active search, e.g. you are on a <var>search.php</var> script page.
 *
 * The file names and the download path of the items are stored along with the number of downloads in the database's plugin_storage table.
 *
 * The actual download source is not public. The list is generated directly from the file system but their
 * sources are not included. Files which no longer exist are
 * kept in the database for statistical reasons until cleared manually via the statistics utility.
 *
 * You will need to modify your theme to use this plugin. You can use the codeblock fields if your theme supports them or
 * insert the function calls directly where you want the list to appear.
 *
 * To protect the download directory from direct linking you need to set up a proper <var>.htaccess</var> for this folder.
 *
 * The list has a CSS class <var>downloadList</var> attached.
 *
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package plugins
 * @subpackage media
 * @tags "file download", "download manager", download
 */
$plugin_is_filter = 800 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("Plugin to generate file downloads.");
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";

$option_interface = "downloadList";

if (zp_loggedin(OVERVIEW_RIGHTS)) {
	zp_register_filter('admin_tabs', 'DownloadList::admin_tabs');
}

/**
 * Plugin option handling class
 *
 */
class DownloadList {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('downloadList_directory', UPLOAD_FOLDER);
			setOptionDefault('downloadList_showfilesize', 1);
			setOptionDefault('downloadList_showdownloadcounter', 1);
			setOptionDefault('downloadList_user', NULL);
			setOptionDefault('downloadList_password', getOption('downloadList_pass'));
			setOptionDefault('downloadList_hint', NULL);
			setOptionDefault('downloadList_rights', NULL);
			setOptionDefault('downloadList_zipFromCache', 0);
			setOptionDefault('downloadList_subAlbums', 1);
		}
	}

	function getOptionsSupported() {
		$options = array(gettext('Download directory') => array('key' => 'downloadList_directory', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("This download folder can be relative to your installation (<em>foldername</em>) or external to it (<em>../foldername</em>)! You can override this setting by using the parameter of the printdownloadList() directly on calling.")),
				gettext('Show filesize of download items') => array('key' => 'downloadList_showfilesize', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => ''),
				gettext('Show download counter of download items') => array('key' => 'downloadList_showdownloadcounter', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 4,
						'desc' => ''),
				gettext('Files to exclude from the download list') => array('key' => 'downloadList_excludesuffixes', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => gettext('A list of file suffixes to exclude. Separate with comma and omit the dot (e.g "jpg").')),
				gettext('Zip source') => array('key' => 'downloadList_zipFromCache', 'type' => OPTION_TYPE_RADIO,
						'order' => 6,
						'buttons' => array(gettext('From album') => 0, gettext('From Cache') => 1),
						'desc' => gettext('Make the album zip from the album folder or from the sized images in the cache.')),
				gettext('Zip subalbums') => array('key' => 'downloadList_subalbums', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 7,
						'desc' => gettext('The album zip will contain images from subalbums.')),
				gettext('User rights') => array('key' => 'downloadList_rights', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('Check if users are required to have <em>file</em> rights to download.'))
		);
		if (GALLERY_SECURITY == 'public') {
			$options[gettext('credentials')] = array('key' => 'downloadList_credentials', 'type' => OPTION_TYPE_CUSTOM,
					'order' => 0,
					'desc' => gettext('Provide credentials to password protect downloads'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$user = getOption('downloadList_user');
		$x = getOption('downloadList_password');
		$hint = getOption('downloadList_hint');
		?>
		<input type="hidden" name="password_enabled_downloadList" id="password_enabled_downloadList" value="0" />
		<span class="password_downloadListextrashow">
			<a onclick="toggle_passwords('_downloadList', true);">
				<?php echo gettext("Password:"); ?>
			</a>
			<?php
			if (empty($x)) {
				?>
				<?php echo LOCK_OPEN; ?>
				<?php
			} else {
				$x = '          ';
				?>
				<a onclick="resetPass('_downloadList');" title="<?php echo gettext('clear password'); ?>">
					<?php echo LOCK; ?></a>
				<?php
			}
			?>
		</span>
		<div class="password_downloadListextrahide" style="display:none">
			<a onclick="toggle_passwords('_downloadList', false);">
				<?php echo gettext("Guest user:"); ?>
			</a>
			<br />
			<input type="text" size="27" id="user_name_downloadList" name="user_downloadList"
						 class="passignore ignoredirty" autocomplete="off"
						 onkeydown="passwordClear('_downloadList');"
						 value="<?php echo html_encode($user); ?>" />
			<br />
			<span id="strength_downloadList"><?php echo gettext("Password:"); ?></span>
			<br />
			<input type="password" size="27"
						 id="pass_downloadList" name="pass_downloadList"
						 class="passignore ignoredirty" autocomplete="off"
						 onkeydown="passwordClear('_downloadList');"
						 onkeyup="passwordStrength('_downloadList');"
						 value="<?php echo $x; ?>" />
			<label>
				<input type="checkbox"
							 name="disclose_password_downloadList"
							 id="disclose_password_downloadList"
							 onclick="passwordClear('_downloadList');
											 togglePassword('_downloadList');">
							 <?php echo gettext('Show'); ?>
			</label>
			<br />
			<span class="password_field__downloadList">
				<span id="match_downloadList"><?php echo gettext("(repeat)"); ?></span>
				<br />
				<input type="password" size="27"
							 id="pass_r_downloadList" name="pass_r_downloadList" disabled="disabled"
							 class="passignore ignoredirty" autocomplete="off"
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
			return '&custom=' . gettext('You must supply a password for the DownloadList user');
		} else if ($notify) {
			return '&custom=' . gettext('Your DownloadList passwords were empty or did not match');
		}
		return false;
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

	/*
	 * Gets the download items from all download items from the database. For internal use in the downloadlink functions.
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
					echo '<' . $listtype . '>';
					self::printListArray($file, $listtype);
					echo '</' . $listtype . '>';
				} else {
					printDownloadURL($file);
				}
				?>
			</li>
			<?php
		}
	}

	static function admin_tabs($tabs) {
		$tabs['overview']['subtabs'][gettext('Download statistics')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php?tab=downloadlist';

		return $tabs;
	}

	static function noFile() {
		global $_downloadFile;
		if (TEST_RELEASE) {
			$file = $_downloadFile;
		} else {
			$file = basename($_downloadFile);
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {
				alert('<?php printf(gettext('File “%s” was not found.'), $file); ?>');
			}, false);
			// ]]> -->
		</script>
		<?php
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

	/**
	 * generates an array of filenames to zip
	 * recurses into the albums subalbums
	 *
	 * @param object $album the object containing the images
	 * @param bool $fromcache use cached images
	 * @param bool $subalbums recurese through subalbums
	 * @param int $level recursion level
	 */
	static function AddAlbum($album, $fromcache, $subalbums, $level) {
		global $_zp_zip_list, $_zp_albums_visited_albumMenu, $zip_gallery, $defaultSize;
		$_zp_albums_visited_albumMenu[] = $album->name;
		$albumfolders = explode('/', $album->name);
		$subalbums = array();
		for ($i = 0; $i < $level; $i++) {
			array_unshift($subalbums, array_pop($albumfolders));
		}
		if (empty($subalbums)) {
			$albumroot = '/';
		} else {
			$albumroot = '/' . implode('/', $subalbums) . '/';
		}

		if ($level && !$fromcache) { // we don't collect the sidecars for the base album
			foreach ($album->getSidecars() as $name => $path) {
				$_zp_zip_list[$path] = '/' . $name;
			}
		}

		$images = $album->getImages();
		foreach ($images as $imagename) {
			$image = newImage($album, $imagename);
			if ($fromcache) {
				$full = $image->getSizedImage($defaultSize);
				if (strpos($full, 'i.php?') !== false)
					continue;
				if (UTF8_IMAGE_URI) {
					$full = internalToFilesystem($full);
				}
			} else {
				$full = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($image->album->name) . '/' . internalToFilesystem($image->filename);
			}
			$f = $albumroot . $image->filename;
			$_zp_zip_list[$full] = $f;

			if (!$fromcache) {
				foreach ($image->getSidecars() as $name => $path) {
					$_zp_zip_list[$path] = $albumroot . $name;
				}
			}
		}

		if ($subalbums) {
			foreach ($album->getAlbums() as $albumname) {
				$subalbum = newAlbum($albumname);
				if (!in_array($subalbum->name, $_zp_albums_visited_albumMenu) && $subalbum->exists && $subalbum->checkAccess()) {
					self::AddAlbum($subalbum, $fromcache, $subalbums, $level + 1);
				}
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
	 * @param object $album album folder
	 * @param string $zipname name of zip file
	 * @param bool fromcache if true, images will be the "sized" image in the cache file
	 * @param bool subalbums recurse through subalbums collecting images
	 */
	static function create($album, $zipname, $fromcache, $subalbums) {
		global $_zp_zip_list, $_zp_albums_visited_albumMenu, $_zp_gallery, $defaultSize;
		if (!$album->exists) {
			self::pageError(404, gettext('Album not found'));
		}
		if (!$album->checkAccess()) {
			self::pageError(403, gettext("Forbidden"));
		}

		$_zp_albums_visited_albumMenu = $_zp_zip_list = array();
		if ($fromcache) {
			$opt = array('large_file_size' => 5 * 1024 * 1024, 'comment' => sprintf(gettext('Created from cached images of %1$s on %2$s.'), $album->name, zpFormattedDate(DATE_FORMAT, time())));
			$defaultSize = getThemeOption('image_size', NULL, $_zp_gallery->getCurrentTheme());
		} else {
			$defaultSize = NULL;
			$opt = array('large_file_size' => 5 * 1024 * 1024, 'comment' => sprintf(gettext('Created from images in %1$s on %2$s.'), $album->name, zpFormattedDate(DATE_FORMAT, time())));
		}
		self::AddAlbum($album, $fromcache, $subalbums, 0);
		if (class_exists('ZipArchive')) {
			$zipfileFS = tempnam('', 'zip');
			$zip = new ZipArchive;
			$zip->open($zipfileFS, ZipArchive::CREATE);
			foreach ($_zp_zip_list as $path => $file) {
				@set_time_limit(6000);
				$zip->addFile($path, internalToFilesystem(trim($file, '/')));
			}
			$zip->close();
			ob_get_clean();
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/zip");
			header("Content-Disposition: attachment; filename=" . basename($zipname . '.zip') . ";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . filesize($zipfileFS));
			readfile($zipfileFS);
			// remove zip file from temp path
			unlink($zipfileFS);
		} else {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-zipStream.php');
			$zip = new ZipStream(internalToFilesystem($zipname) . '.zip', $opt);
			foreach ($_zp_zip_list as $path => $file) {
				@set_time_limit(6000);
				$zip->add_file_from_path(internalToFilesystem($file), $path);
			}
			$zip->finish();
		}
	}

}

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
 * 										You can also set any folder within or without the root of your installation as a download folder with this directly
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @param array $filters8 an array of files to exclude from the list. Standard items are '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'
 * @param array $excludesuffixes an array of file suffixes (without trailing dot to exclude from the list (e.g. "jpg")
 * @param string $sort 'asc" or "desc" (default) for alphabetical ascending or descending list
 * @return array
 */
function getdownloadList($dir8, $filters8, $excludesuffixes, $sort) {
	$filters = Array('Thumbs.db');
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
		if (@$file{0} != '.') { //	exclude "hidden" files
			if (is_dir(internalToFilesystem($dir) . '/' . $file)) {
				$dirN = filesystemToInternal($dir) . "/" . filesystemToInternal($file);
				$dir_array[$file] = getdownloadList($dirN, $filters8, $excludesuffixes, $sort);
			} else {
				if (!in_array(getSuffix($file), $excludesuffixes)) {
					$dir_array[$file] = $dir . '/' . filesystemToInternal($file);
				}
			}
		}
	}
	return $dir_array;
}

/**
 * Gets the download url for a file
 * @param string $file the path to a file to get a download link.
 */
function getDownloadURL($file) {
	if (substr($file, 0, 1) != '/' && strpos($file, ':') === false) {
		$file = SERVERPATH . '/' . getOption('downloadList_directory') . '/' . $file;
	}
	$request = parse_url(getRequestURI());
	if (isset($request['query'])) {
		$query = parse_query($request['query']);
	} else {
		$query = array();
	}
	DownloadList::addListItem($file); // add item to db if not already exists without updating the counter
	$link = '';
	if ($id = DownloadList::getItemID($file)) {
		$query['download'] = $id;
		$link = FULLWEBPATH . '/' . preg_replace('~^' . WEBPATH . '/~', '', pathurlencode($request['path'])) . '?' . urldecode(http_build_query($query));
	}
	return $link;
}

/**
 * Prints a download link for a file, depending on the plugin options including the downloadcount and filesize
 * @param string $file the path to a file to print a download link.
 * @param string $linktext Optionally how you wish to call the link. Set/leave  to NULL to use the filename.
 */
function printDownloadURL($file, $linktext = NULL) {
	if (substr($file, 0, 1) != '/' && strpos($file, ':') === false) {
		$file = SERVERPATH . '/' . getOption('downloadList_directory') . '/' . $file;
	}
	$filesize = '';
	if (getOption('downloadList_showfilesize')) {
		$filesize = @filesize(internalToFilesystem($file));
		$filesize = ' (' . byteConvert($filesize) . ')';
	}
	if (getOption('downloadList_showdownloadcounter')) {
		$downloaditem = DownloadList::getListItemFromDB($file);
		if ($downloaditem) {
			$downloadcount = $downloaditem['data'];
		} else {
			$downloadcount = 0;
		}
		$filesize .= ' - ' . sprintf(ngettext('%u download', '%u downloads', $downloadcount), $downloadcount);
	}
	if (empty($linktext)) {
		$filename = basename($file);
	} else {
		$filename = $linktext;
	}
	echo '<a href="' . html_encode(getDownloadURL($file)) . '" rel="nofollow" class="downloadlist_link">' . html_encode($filename) . '</a><small>' . $filesize . '</small>';
}

/**
 *
 * Prints a download link for an album zip of the current album (therefore to be used only on album.php/image.php).
 * This function only creates a download and returns to the current page.
 *
 * @param string $linktext
 * @param object $albumobj
 * @param bool $fromcache if true get the images from the cache
 */
function printDownloadAlbumZipURL($linktext = NULL, $albumobj = NULL, $fromcache = NULL, $subalbums = true) {
	global $_zp_current_album, $_zp_current_search;
	$request = parse_url(getRequestURI());
	if (isset($request['query'])) {
		$query = parse_query($request['query']);
	} else {
		$query = array();
	}
	if (is_null($albumobj)) {
		$albumobj = $_zp_current_album;
	}
	$link = preg_replace('~^' . WEBPATH . '/~', '', $request['path']);

	if (!is_null($albumobj)) {
		switch (get_class($albumobj)) {
			case 'favorites':
				$query['download'] = $file = gettext('My favorites');
				$query['user'] = $albumobj->name;
				$instance = $query['instance'] = $albumobj->instance;
				if ($instance) {
					$file .= '[' . $instance . ']';
					$query['download'] .= '[' . $instance . ']';
				}
				$file .= '.zip';
				$query['type'] = 'albumzip';
				break;
			case'SearchEngine':
				$params = parse_query($_zp_current_search->getSearchParams(0));
				$query['download'] = $file = gettext('search') . implode('_', $params);
				$file .= '.zip';
				$query['type'] = 'searchzip';
				$query = array_merge($query, $params);
				break;
			default:
				$query['download'] = $albumobj->name;
				$file = $albumobj->name . '.zip';
				$query['type'] = 'albumzip';
				break;
		}
		if ($fromcache) {
			$query['fromcache'] = 'true';
		}
		if ($subalbums) {
			$query['subalbums'] = 'true';
		}

		DownloadList::addListItem($file);
		if (getOption('downloadList_showdownloadcounter')) {
			$downloaditem = DownloadList::getListItemFromDB($file);
			if ($downloaditem) {
				$downloadcount = $downloaditem['data'];
			} else {
				$downloadcount = 0;
			}
			$filesize = '<small> - ' . sprintf(ngettext('%u download', '%u downloads', $downloadcount), $downloadcount) . '</small>';
		} else {
			$filesize = '';
		}
		if (!empty($linktext)) {
			$file = $linktext;
		}
		echo '<a href="' . FULLWEBPATH . '/' . html_encode(pathurlencode($link)) . '?' . http_build_query($query) . '" rel="nofollow class="downloadlist_link"">' . html_encode($file) . '</a>' . $filesize;
	}
}

/**
 * Prints a download link for a zip of the current search result (therefore to be used only on search.php).
 * This function only creates a download and returns to the current page.
 *
 * @global type $_zp_current_search
 * @param type $linktext
 */
function printDownloadSearchZipURL($linktext = NULL, $fromcache = NULL) {
	global $_zp_current_search;
	printDownloadAlbumZipURL($linktext, $_zp_current_search, $fromcache, false);
}

/**
 * Process any download requests
 */
if (isset($_GET['download'])) {
	$_zp_HTML_cache->abortHTMLCache(true);
	$item = sanitize($_GET['download']);
	if (empty($item) || !extensionEnabled('downloadList')) {
		if (TEST_RELEASE) {
			zp_error(gettext('Forbidden'));
		} else {
			header("HTTP/1.0 403 " . gettext("Forbidden"));
			header("Status: 403 " . gettext("Forbidden"));
			exitZP(); //	terminate the script with no output
		}
	}
	$hash = getOption('downloadList_password');
	if (GALLERY_SECURITY != 'public' || $hash) {
		//	credentials required to download
		if (!zp_loggedin((getOption('downloadList_rights')) ? FILES_RIGHTS : ALL_RIGHTS)) {
			$user = getOption('downloadList_user');
			if (!zp_handle_password('download_auth', $hash, $user)) {
				$show = ($user) ? true : NULL;
				$hint = get_language_string(getOption('downloadList_hint'));
				$_zp_gallery_page = 'password.php';
				$_zp_script = $_zp_themeroot . '/password.php';
				if (!file_exists(internalToFilesystem($_zp_script))) {
					$_zp_script = SERVERPATH . '/' . ZENFOLDER . '/password.php';
				}
				header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
				header("HTTP/1.0 302 Found");
				header("Status: 302 Found");
				header('Last-Modified: ' . ZP_LAST_MODIFIED);
				include(internalToFilesystem($_zp_script));
				exitZP();
			}
		}
	}
	switch (@$_GET['type']) {
		case 'searchzip':
			$album = new SearchEngine();
		case 'albumzip':
			if (isset($_GET['instance'])) {
				$album = new favorites(sanitize($_GET['user']));
				if ($instance = trim(sanitize($_GET['instance']), '/')) {
					$album->instance = $instance;
				}
			} else if (!isset($album)) {
				$album = newAlbum($item, false, true);
			}

			if (!$fromcache = isset($_GET['fromcache'])) {
				$fromcache = getOption('downloadList_zipFromCache');
			}
			if (!$subalbums = isset($_GET['subalbums'])) {
				$subalbums = getOption('downloadList_subalbums');
			}

			AlbumZip::create($album, $item, $fromcache, $subalbums);
			DownloadList::updateListItemCount($item . '.zip');
			exitZP();
		default:
			$path = query_single_row("SELECT `aux` FROM " . prefix('plugin_storage') . " WHERE id=" . (int) $item);
			if (array_key_exists('aux', $path) && file_exists($_downloadFile = internalToFilesystem($path['aux']))) {
				require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-MimeTypes.php');
				DownloadList::updateListItemCount($_downloadFile);
				$ext = getSuffix($_downloadFile);
				$mimetype = getMimeString($ext);
				header('Content-Description: File Transfer');
				header('Content-Type: ' . $mimetype);
				header('Content-Disposition: attachment; filename=' . basename(urldecode($_downloadFile)));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($_downloadFile));
				flush();
				readfile($_downloadFile);
				exitZP();
			} else {
				zp_register_filter('theme_body_open', 'DownloadList::noFile');
			}
	}
}
?>