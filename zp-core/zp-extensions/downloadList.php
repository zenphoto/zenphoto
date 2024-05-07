<?php
/**
 *
 * A plugin to generate a file download list.
 * This download folder can be relative to your Zenphoto root (<i>foldername</i>) or external to it (<i>../foldername</i>).
 * By default the <var>%UPLOAD_FOLDER%</var> folder is chosen so you can use the file manager to manage these files.
 *
 * You can also override that folder by using the <var>printdownloadList()</var> function parameters directly. Additionally
 * you can set a downloadlink to a specific file directly by using <code>printDownloadURL(<i>path-to-file</i>);<code>.
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
 * The <var>printDownloadAlbumZipURL()</var> function will create a zipfile of the album <i>on the fly</i>.
 * The source of the images may be the original
 * images from the album and its subalbums or they may be the <i>sized</i> images from the cache. Use the latter if you want
 * the images to be watermarked.
 *
 * The list has a CSS class <var>downloadList</var> attached.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard), , Antonio Ranesi (bic)
 * @package zpcore\plugins\downloadlist
 * @tags "file download", "download manager", download
 */
$plugin_is_filter = 800 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("Plugin to generate file download lists.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard), Antonio Ranesi (bic)";
$plugin_category = gettext('Misc');

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
		setOptionDefault('downloadList_subalbums', 'none');
	}

	function getOptionsSupported() {
		$options = array(gettext('Download directory') => array(
						'key' => 'downloadList_directory',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("This download folder can be relative to your Zenphoto installation (<em>foldername</em>) or external to it (<em>../foldername</em>)! You can override this setting by using the parameter of the printdownloadList() directly on calling.")),
				gettext('Show filesize of download items') => array(
						'key' => 'downloadList_showfilesize',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => ''),
				gettext('Show download counter of download items') => array(
						'key' => 'downloadList_showdownloadcounter',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 4,
						'desc' => ''),
				gettext('Files to exclude from the download list') => array(
						'key' => 'downloadList_excludesuffixes',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => gettext('A list of file suffixes to exclude. Separate with comma and omit the dot (e.g "jpg").')),
				gettext('Zip source') => array(
						'key' => 'downloadList_zipFromCache',
						'type' => OPTION_TYPE_RADIO,
						'order' => 6,
						'buttons' => array(gettext('Full images') => 0, gettext('Resized images') => 1),
						'desc' => gettext('Make the album zip using full images from the album folder or from the sized images <strong>already existing</strong> in the cache.')
						. "<p class='notebox'>"
		        . gettext("<strong>Notice:</strong>  If you select <em>Resized images</em>, make sure that you have already cached all of the default size images for the albums to be downloaded. If some images are not cached when the download is requested, the plugin will try to cache them “on the fly” using cURL (if available), which may take a long time to process, resulting in a long wait time for users or, in the worst case, a fatal time-out error by the server.")
		        . "</p>"),
				gettext('Add images from subalbums') => array(
						'key' => 'downloadList_subalbums',
						'type' => OPTION_TYPE_RADIO,
						'order' => 7,
						'buttons' => array(gettext('None') => "none", gettext('Direct subalbums') => "direct", gettext('All subalbums') => "all"),
						'desc' => gettext('Subalbums whose images are to be included in the album zip.')),
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

	function handleOptionSave($themename, $themealbum) {
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
		global $_zp_db;
		$checkitem = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `aux` = " . $_zp_db->quote($path) . " AND `type` = 'downloadList'");
		if ($checkitem) {
			$downloadcount = $checkitem['data'] + 1;
			$_zp_db->query("UPDATE " . $_zp_db->prefix('plugin_storage') . " SET `data` = " . $downloadcount . ", `type` = 'downloadList' WHERE `aux` = " . $_zp_db->quote($path) . " AND `type` = 'downloadList'");
		}
	}

	/**
	 * Adds a new download item to the database. For internal use.
	 * @param string $path Path of the download item
	 */
	static function addListItem($path) {
		global $_zp_db;
		$checkitem = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `aux` = " . $_zp_db->quote($path) . " AND `type` = 'downloadList'");
		if (!$checkitem) {
			$_zp_db->query("INSERT INTO " . $_zp_db->prefix('plugin_storage') . " (`type`,`aux`,`data`) VALUES ('downloadList'," . $_zp_db->quote($path) . ",'0')");
		}
		zp_apply_filter('downloadlist_processdownload', $path);
	}

	/** Gets the download items from all download items from the database. For internal use in the downloadList functions.
	 * @return array
	 */
	static function getListItemsFromDB() {
		global $_zp_db;
		$downloaditems = $_zp_db->queryFullArray("SELECT id, `aux`, `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = 'downloadList'");
		return $downloaditems;
	}

	/** Gets the download items from all download items from the database. For internal use in the downloadlink functions.
	 * @return array
	 */
	static function getListItemFromDB($file) {
		global $_zp_db;
		$downloaditem = $_zp_db->querySingleRow($sql = "SELECT id, `aux`, `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = 'downloadList' AND `aux` = " . $_zp_db->quote($file));
		return $downloaditem;
	}

	/**
	 * Gets the id of a download item from the database for the download link. For internal use.
	 * @param string $path Path of the download item (without WEBPATH)
	 * @return bool|string
	 */
	static function getItemID($path) {
		global $_zp_db;
		$downloaditem = $_zp_db->querySingleRow("SELECT id, `aux`, `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = 'downloadList' AND `aux` = " . $_zp_db->quote($path));
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
		if ($listtype != 'ol' && $listtype != 'ul') {
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

	/**
	 * Admin overview button for download statistics utility
	 */
	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Info'),
				'enable' => true,
				'button_text' => gettext('Download statistics'),
				'formname' => 'downloadstatistics_button',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php',
				'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
				'title' => gettext('Counts of downloads'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS,
		);
		return $buttons;
	}
	
	/**
	 * Handles missing files
	 * 
	 * @global string $_zp_downloadfile
	 */
	static function noFile() {
		global $_zp_downloadfile;
		if (TEST_RELEASE) {
			$file = $_zp_downloadfile;
		} else {
			$file = basename($_zp_downloadfile);
		}

		$back_url = preg_replace('/[&|?]download=.+/', '', $_SERVER["REQUEST_URI"]);

		header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		zp_apply_filter('theme_headers');
		?>
		<!DOCTYPE html>
		<html<?php printLangAttribute(); ?>>
			<head>
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<meta charset="<?php echo LOCAL_CHARSET; ?>">
				<meta name="ROBOTS" content="NOINDEX, FOLLOW">
				<title><?php echo gettext("Object not found") . ': ' . $file . ' | ' . html_encode(getBareGalleryTitle()) ?></title>
				<style>
				body {
					background: white;
				}
				div {
					text-align: center;
					background: url(<?php echo WEBPATH . '/' . ZENFOLDER ?>/images/zen-logo.png) 50% calc(30vh - 70px) no-repeat;
					padding: 30vh 1em 0;
					color: #f56a6a;
					text-transform: uppercase;
				}
				a {
					color: #718086;
					margin: 0 .5em;
				}
				</style>
			</head>
			<body>
				<div>
					<?php $file = sprintf(gettext('File “%s” was not found.'), $file); ?>
					<h3><?php echo $file ?></h3>
					<a href="<?php echo $back_url; ?>"><?php echo gettext('Back') ?></a>
					<a href="<?php echo WEBPATH; ?>/"><?php echo gettext('Home') ?></a>
				</div>
			</body>
		</html>
		<?php
		exitZP();
	}

	static function checkAccess(&$hint = NULL, &$show = NULL) {
		$hash = getOption('downloadList_password');
		if (GALLERY_SECURITY != 'public' || $hash) {
			//	credentials required to download
			if (!zp_loggedin((getOption('downloadList_rights')) ? FILES_RIGHTS : ALL_RIGHTS)) {
				$user = getOption('downloadList_user');
				zp_handle_password('zpcms_auth_download', $hash, $user);
				if ((!empty($hash) && zp_getCookie('zpcms_auth_download') != $hash)) {
					$show = (!empty($user));
					$hint = get_language_string(getOption('downloadList_hint'));
					return false;
				}
			}
		}
		return true;
	}

}

class AlbumZip {

	/**
	 * Used to store the subalbum option once handled
	 * @var null
	 */
	private static $levels = null;

	/**
	 * Handles the subalbum option only the first time it is requested and
	 * stores it to make it at once available in any subsequent cycle.
	 * @return int
	 */
	static function subalbumsOption() {
		if (is_null(self::$levels)) {
			switch (getOption('downloadList_subalbums')) {
				case 'direct':
					self::$levels = 1;
					break;
				case 'all':
					self::$levels = 2;
					break;
				default:
					self::$levels = 0;
			}
		}
		return self::$levels;
	}

	/**
	 * generates an array of filenames to zip
	 * recurses into the albums subalbums on option
	 *
	 * @param object $album album object to add
	 * @param int $base the length of the base album name
	 * @param string $filebase the server directory of the album
	 * @param int $level initial value is 0, recursing into subalbums sets it to 1
	 * @param string $root_folder allows parent album's sidecars in the same folder of album
	 */
	static function AddAlbum($album, $base, $filebase, $level = 0, $root_folder = "") {
		global $_zp_zip_list;
		if ($level == 0) {
			$root_folder = basename($album->name);
		}
		foreach ($album->sidecars as $suffix) {
			if ($level == 0) { // parent album sidecars
				$file_name = basename($album->name) . '.' . $suffix;
				$path = dirname($filebase) . '/' . internalToFilesystem($file_name);
				if (file_exists($path)) {
					$_zp_zip_list[$path] = $file_name;
				}
			} else { // childern albums sidecars
				$file_name = substr($album->name, $base) . '.' . $suffix;
				$path = $filebase . internalToFilesystem($file_name);
				if (file_exists($path)) {
					$_zp_zip_list[$path] = $root_folder . $file_name;
				}
			}
		}
		$albumbase = substr($album->name, $base) . '/';
		$images = $album->getImages();
		foreach ($images as $imagename) {
			$image = Image::newImage($album, $imagename);
			$dyn_fold = "";
			if ($album->isDynamic()) {
				$filebase = $image->album->localpath;
				$albumbase = '/';
				if ($level > 0) { // dynamic subalbums need a dedicated subfolder 
					$dyn_fold = substr($album->name, $base);
				}
			}
			$file_name = $albumbase . $image->filename;
			$path = $filebase . internalToFilesystem($file_name);
			$_zp_zip_list[$path] = $root_folder . $dyn_fold . $file_name;
			$imagebase = stripSuffix($image->filename);
			foreach ($image->sidecars as $suffix) {
				$file_name = $albumbase . $imagebase . '.' . $suffix;
				$path = $filebase . internalToFilesystem($file_name);
				if (file_exists($path)) {
					$_zp_zip_list[$path] = $root_folder . $dyn_fold . $file_name;
				}
			}
		}
		if (self::subalbumsOption() > $level) { // false after first recursion but for "all" option
			$albums = $album->getAlbums();
			foreach ($albums as $albumname) {
				$subalbum = AlbumBase::newAlbum($albumname);
				if (!$subalbum->isMyItem(LIST_RIGHTS) && !$subalbum->isProtected()) {
					continue; // Skip not accessible albums
				}
				if ($subalbum->exists) {
					self::AddAlbum($subalbum, $base, $filebase, 1, $root_folder);
				}
			}
		}
	}

	/**
	 * generates an array of cachefilenames to zip
	 * recurses into the albums subalbums on option
	 *
	 * @param object $album album object to add
	 * @param int $base the length of the base album name
	 * @param string $filebase the server directory of the album
	 * @param int $level initial value is 0, recursing into subalbums sets it to 1
	 */
	static function AddAlbumCache($album, $base, $filebase, $level = 0) {
		global $_zp_zip_list, $_zp_downloadlist_defaultsize;
		$albumbase = substr($album->name, $base) . '/';
		$images = $album->getImages();
		$cache_fail = false;
		foreach ($images as $imagename) {
			$image = Image::newImage($album, $imagename);
			$dyn_fold = "";
			if ($album->isDynamic()) {
				$filebase = str_replace("albums", "cache", $image->album->localpath);
				$albumbase = '/';
				if ($level > 0) { // dynamic subalbums need a dedicated subfolder 
					$dyn_fold = substr($album->name, $base);
				}
			}
			$uri = $image->getSizedImage($_zp_downloadlist_defaultsize);
			if (strpos($uri, 'i.php?') === false) { // images already cached
				$parseurl = parse_url($albumbase . basename($uri));
				$file_name = $parseurl['path'];
				$_zp_zip_list[$filebase . $file_name] = $dyn_fold . $file_name;
			} else if (function_exists('curl_init') && generateImageCacheFile($uri)) { // images to be cached
				$uri = $image->getSizedImage($_zp_downloadlist_defaultsize);
				$parseurl = parse_url($albumbase . basename($uri));
				$file_name = $parseurl['path'];
				$_zp_zip_list[$filebase . $file_name] = $dyn_fold . $file_name;
			} else if (!$cache_fail) { // caching failed, write once on the error log
				$cache_fail = true;
				if (DEBUG_ERROR) {
					debugLog(sprintf(gettext('WARNING: Some images from %1$s were not added to %2$s by %3$s as they were not cached'), $album->name, str_replace("/", "_", $album->name) . ".zip", 'downloadList.php -> AlbumZip::AddAlbumCache()'));
				}
			}
		}
		if (self::subalbumsOption() > $level) { // false after first recursion but for "all" option
			$albums = $album->getAlbums();
			foreach ($albums as $albumname) {
				$subalbum = AlbumBase::newAlbum($albumname);
				if (!$subalbum->isMyItem(LIST_RIGHTS) && !$subalbum->isProtected()) {
					continue; // Skip not accessible albums
				}
				if ($subalbum->exists) {
					self::AddAlbumCache($subalbum, $base, $filebase, 1);
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
		echo '<html lang="' . getLangAttributeLocale() . '">';
		echo '<head>';
		echo '<title>' . $err . ' - ' . $text . '</title>';
		echo '<meta name="ROBOTS" content="NOINDEX, FOLLOW">';
		echo '</head>';
		echo '<body style="background-color: #ffffff; color: #000000">';
		echo '<p><strong>' . sprintf(gettext('Page error: %2$s (%1$s)'), $err, $text) . '</strong></p>';
		echo '</body>';
		echo '</html>';
		exitZP();
	}

	/**
	 * Creates a zip file of the album
	 *
	 * @param string $albumname album folder
	 * @param bool fromcache if true, images will be the "sized" image in the cache file
	 */
	static function create($albumname, $fromcache) {
		global $_zp_zip_list, $_zp_gallery, $_zp_downloadlist_defaultsize;
		if (!file_exists(ALBUM_FOLDER_SERVERPATH . $albumname)) {
			self::pageError(404, gettext('Album not found'));
		}
		$album = AlbumBase::newAlbum($albumname);
		if (!$album->isMyItem(LIST_RIGHTS) && !$album->isProtected()) {
			self::pageError(403, gettext("Forbidden"));
		}
		$_zp_zip_list = array();
		if ($fromcache) {
			$opt = array('large_file_size' => 5 * 1024 * 1024, 'comment' => sprintf(gettext('Created from cached images of %1$s on %2$s.'), $album->name, zpFormattedDate(DATETIME_DISPLAYFORMAT, time())));
			loadLocalOptions(false, $_zp_gallery->getCurrentTheme());
			$_zp_downloadlist_defaultsize = getOption('image_size');
			self::AddAlbumCache($album, strlen($albumname), SERVERPATH . '/' . CACHEFOLDER . '/' . $albumname);
		} else {
			$opt = array('large_file_size' => 5 * 1024 * 1024, 'comment' => sprintf(gettext('Created from images in %1$s on %2$s.'), $album->name, zpFormattedDate(DATETIME_DISPLAYFORMAT, time())));
			self::AddAlbum($album, strlen($albumname), $album->localpath);
		}
		if(!empty($_zp_zip_list)) {
			DownloadList::updateListItemCount($albumname . '.zip');
			$zip = new ZipStream($albumname . '.zip', $opt);
			zp_setCookie('zpcms_albumzip_ready', 1, 1, null, secureServer());
			foreach ($_zp_zip_list as $path => $file) {
				@set_time_limit(6000);
				$zip->add_file_from_path(internalToFilesystem($file), internalToFilesystem($path));
			}
			$zip->finish();
			exitZP();
		}
		return false;
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
	if ($listtype != 'ol' && $listtype != 'ul') {
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
		sortArray($dirs, false, true, true);
	}
	foreach ($dirs as $file) {
		if (@$file[0] != '.') { //	exclude "hidden" files
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
		$link = FULLWEBPATH . '/' . preg_replace('~^' . WEBPATH . '/~', '', $request['path']) . '?' . http_build_query($query);
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
			$downloadcount = ' - ' . sprintf(ngettext('%u download', '%u downloads', $downloaditem['data']), $downloaditem['data']);
		} else {
			$downloadcount = ' - ' . gettext('0 downloads');
		}
		$filesize .= $downloadcount;
	}
	if (empty($linktext)) {
		$filename = basename($file);
	} else {
		$filename = $linktext;
	}
	echo '<a href="' . html_encode(getDownloadURL($file)) . '" rel="nofollow" class="downloadlist_link" data-track-content data-content-piece data-content-name="' . html_encode($filename) . '">' . html_encode($filename) . '</a><small>' . $filesize . '</small>';
}

/**
 * Prints the dwnload url link for a full image
 * 
 * @since 1.5.7
 * 
 * @global type $_zp_current_image
 * @param string $linktext Linktext for the download
 * @param obj $imageobj Optional image object to use, otherwise the current image if available
 */
function printFullImageDownloadURL($linktext = null, $imageobj = null) {
	global $_zp_current_image;
	if (is_null($imageobj)) {
		$imageobj = $_zp_current_image;
	}
	if (!is_null($imageobj)) {
		printDownloadURL($imageobj->getFullImage(SERVERPATH), $linktext);
	}
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
function printDownloadAlbumZipURL($linktext = NULL, $albumobj = NULL, $fromcache = NULL) {
	global $_zp_current_album, $_zp_db;
	$request = parse_url(getRequestURI());
	if (isset($request['query'])) {
		$query = parse_query($request['query']);
	} else {
		$query = array();
	}
	if (is_null($albumobj)) {
		$albumobj = $_zp_current_album;
	}
	if (!is_null($albumobj)) {
		$file = $albumobj->name . '.zip';
		DownloadList::addListItem($file);
		if (getOption('downloadList_showdownloadcounter')) {
			$downloaditem = DownloadList::getListItemFromDB($file);
			if ($downloaditem) {
				$downloadcount = ' - ' . sprintf(ngettext('%u download', '%u downloads', $downloaditem['data']), $downloaditem['data']);
			} else {
				$downloadcount = ' - ' . gettext('0 downloads');
			}
			$filesize = '<small>' . $downloadcount . '</small>';
		} else {
			$filesize = '';
		}
		if (!empty($linktext)) {
			$file = $linktext;
		}
		$query['download'] = $albumobj->name;
		$query['albumzip'] = 'true';
		if ($fromcache) {
			$query['fromcache'] = 'true';
		}
		$link = FULLWEBPATH . '/' . preg_replace('~^' . WEBPATH . '/~', '', $request['path']) . '?' . http_build_query($query);
		echo '<a href="' . html_encode($link) . '" rel="nofollow" class="downloadlist_link" data-track-content data-content-piece data-content-name="' . html_encode($file) . '">' . html_encode($file) . '</a>' . $filesize;
	}
}

/**
 * Process any download requests
 */
if (isset($_GET['download'])) {
	$item = sanitize($_GET['download']);
	if (empty($item)) {
		if (TEST_RELEASE) {
			zp_error(gettext('Forbidden'));
		} else {
			header("HTTP/1.0 403 " . gettext("Forbidden"));
			header("Status: 403 " . gettext("Forbidden"));
			exitZP(); //	terminate the script with no output
		}
	}
	if (!DownloadList::checkAccess($hint, $show)) {
		return;
	}
	if (isset($_GET['albumzip'])) {
		require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/class-zipstream.php');
		if (isset($_GET['fromcache'])) {
			$fromcache = sanitize(isset($_GET['fromcache']));
		} else {
			$fromcache = getOption('downloadList_zipFromCache');
		}
		$success = AlbumZip::create($item, $fromcache);
		if(!$success) {
			$_zp_downloadfile = $item . '.zip';
			DownloadList::noFile();
		}
	} else {
		require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-mimetypes.php';
		$item = (int) $item;
		$path = $_zp_db->querySingleRow("SELECT `aux` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE id=" . $item);
		$_zp_downloadfile = '';
		if (isset($path['aux'])) {
			$_zp_downloadfile = internalToFilesystem($path['aux']);
		}
		if (file_exists($_zp_downloadfile)) {
			DownloadList::updateListItemCount($_zp_downloadfile);
			$ext = getSuffix($_zp_downloadfile);
			$mimetype = mimeTypes::getType($ext);
			header('Content-Description: File Transfer');
			header('Content-Type: ' . $mimetype);
			header('Content-Disposition: attachment; filename=' . basename(urldecode($_zp_downloadfile)));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($_zp_downloadfile));
			flush();
			readfile($_zp_downloadfile);
			exitZP();
		} else {
			DownloadList::noFile();
		}
	}
}
// TODO:
// 1) Include dynamic albums as well [done]
// 2) Handle properly album_name.zip files in download statistic, as for now they result missing even if the album is present. Statistics for album download get erased by pressing the "Clear outdated downloads from database".
// 3) Merge the old error page [pageError()] with the new one [noFile()]
