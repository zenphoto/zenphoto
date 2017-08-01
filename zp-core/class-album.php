<?php

/**
 * Album Class
 * @package classes
 */
// force UTF-8 Ø

define('IMAGE_SORT_DIRECTION', $_zp_gallery->getSortDirection('image'));
define('IMAGE_SORT_TYPE', $_zp_gallery->getSortType('image'));

Gallery::addAlbumHandler('alb', 'dynamicAlbum');

/**
 * Wrapper instantiation function for albums. Do not instantiate directly
 * @param string $folder8 the name of the folder (inernal character set)
 * @param bool $cache true if the album should be fetched from the cache
 * @param bool $quiet true to supress error messages
 * @return Album
 */
function newAlbum($folder8, $cache = true, $quiet = false) {
	global $_zp_albumHandlers;
	$folder8 = sanitize_path($folder8);
	$suffix = getSuffix($folder8);
	if (!$suffix || !array_key_exists($suffix, $_zp_albumHandlers) || is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($folder8))) {
		return new Album($folder8, $cache, $quiet);
	} else {
		return new $_zp_albumHandlers[$suffix]($folder8, $cache, $quiet);
	}
}

/**
 * Returns true if the object is a zenphoto 'album'
 *
 * @param object $album
 * @return bool
 */
function isAlbumClass($album) {
	return is_object($album) && ($album->table == 'albums');
}

class AlbumBase extends MediaObject {

	var $name; // Folder name of the album (full path from the albums folder)
	var $linkname; // may have the .alb suffix stripped off
	var $localpath; // Latin1 full server path to the album
	var $exists = true; // Does the folder exist?
	var $images = NULL; // Full images array storage.
	var $parent = null; // The parent album name
	var $parentalbum = null; // The parent album's album object (lazy)
	var $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	var $manage_some_rights = ALBUM_RIGHTS;
	var $access_rights = ALL_ALBUMS_RIGHTS;
	protected $sidecars = array(); // keeps the list of suffixes associated with this album
	protected $subalbums = null; // Full album array storage.
	protected $index;
	protected $lastimagesort = NULL; // remember the order for the last album/image sorts
	protected $lastsubalbumsort = NULL;
	protected $albumthumbnail = NULL; // remember the album thumb for the duration of the script
	protected $subrights = NULL; //	cache for subrights

	function __construct($folder8, $cache = true) {
		$this->linkname = $this->name = $folder8;
		$this->instantiate('albums', array('folder' => $this->name), 'folder', false, true);
		$this->exists = false;
	}

	/**
	 *
	 * "Magic" function to return a string identifying the object when it is treated as a string
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

	/**
	 * Sets default values for a new album
	 *
	 * @return bool
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		if (TEST_RELEASE) {
			$bt = debug_backtrace();
			$good = false;
			foreach ($bt as $b) {
				if ($b['function'] == "newAlbum") {
					$good = true;
					break;
				}
			}
			if (!$good) {
				debugLogBacktrace(gettext('An album object was instantiated without using the newAlbum() function.'));
			}
		}

		// Set default data for a new Album (title and parent_id)
		$this->set('mtime', time());
		$title = trim($this->name);
		if (!is_null($parentalbum = $this->getParent())) {
			$this->set('parentid', $parentalbum->getID());
			$title = substr($title, strrpos($title, '/') + 1);
		}
		$this->set('title', $title);
		$this->setShow($_zp_gallery->getAlbumPublish());

		//	load images
		if (is_null($this->getImages())) {
			$this->images = array();
		}

		return true;
	}

	/**
	 * album validity check
	 *
	 * @param string $folder8
	 * @param string $folderFS
	 * @param bool $quiet
	 * @param bool $valid class specific check
	 * @return boolean
	 */
	static protected function albumCheck($folder8, $folderFS, $quiet, $invalid) {
		if (empty($folder8)) {
			$msg = gettext('Invalid album instantiation: No album name');
		} else if (filesystemToInternal($folderFS) != $folder8) {
			// an attempt to spoof the album name.
			$msg = sprintf(gettext('Invalid album instantiation: %1$s!=%2$s'), html_encode(filesystemToInternal($folderFS)), $folder8);
		} else if ($invalid) {
			//	class specific validity test
			$msg = sprintf(gettext('Invalid album instantiation: %s does not exist.'), $folder8);
		} else {
			$msg = false;
		}
		if ($msg) {
			if (!$quiet) {
				debugLogBacktrace($msg);
			}
			return false;
		}
		return true;
	}

	/**
	 * Returns the folder on the filesystem
	 *
	 * @return string
	 */
	function getFileName() {
		return $this->name;
	}

	/**
	 * Returns The parent Album of this Album. NULL if this is a top-level album.
	 *
	 * @return object
	 */
	function getParent() {
		if (is_null($this->parentalbum)) {
			$slashpos = strrpos($this->name, "/");
			if ($slashpos) {
				$parent = substr($this->name, 0, $slashpos);
				$parentalbum = newAlbum($parent, true, true);
				if ($parentalbum->exists) {
					return $parentalbum;
				}
			}
		} else if ($this->parentalbum->exists) {
			return $this->parentalbum;
		}
		return NULL;
	}

	function getParentID() {
		return $this->get('parentid');
	}

	/**
	 * Returns the place data of an album
	 *
	 * @return string
	 */
	function getLocation($locale = NULL) {
		$text = $this->get('location');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the album place
	 *
	 * @param string $place text for the place field
	 */
	function setLocation($place) {
		$this->set('location', zpFunctions::tagURLs($place));
	}

	/**
	 * Returns either the subalbum sort direction or the image sort direction of the album
	 *
	 * @param string $what 'image_sortdirection' if you want the image direction,
	 *        'album_sortdirection' if you want it for the album
	 *
	 * @return string
	 */
	function getSortDirection($what = 'image') {
		global $_zp_gallery;
		if ($what == 'image') {
			$direction = $this->get('image_sortdirection');
			$type = $this->get('sort_type');
		} else {
			$direction = $this->get('album_sortdirection');
			$type = $this->get('subalbum_sort_type');
		}
		if (empty($type)) {
			// using inherited type, so use inherited direction
			$parentalbum = $this->getParent();
			if (is_null($parentalbum)) {
				if ($what == 'image') {
					$direction = IMAGE_SORT_DIRECTION;
				} else {
					$direction = $_zp_gallery->getSortDirection();
				}
			} else {
				$direction = $parentalbum->getSortDirection($what);
			}
		}
		return $direction;
	}

	/**
	 * sets sort directions
	 *
	 * @param bool $val the direction
	 * @param string $what 'images' if you want the image direction,
	 *        'albums' if you want it for the album
	 */
	function setSortDirection($val, $what = 'images') {
		if ($what == 'images') {
			$this->set('image_sortdirection', (int) ($val && true));
		} else {
			$this->set('album_sortdirection', (int) ($val && true));
		}
	}

	/**
	 * Returns the sort type of the album images
	 * Will return a parent sort type if the sort type for this album is empty
	 *
	 * @return string
	 */
	function getSortType($what = 'image') {
		global $_zp_gallery;
		if ($what == 'image') {
			$type = $this->get('sort_type');
		} else {
			$type = $this->get('subalbum_sort_type');
		}
		if (empty($type)) {
			$parentalbum = $this->getParent();
			if (is_null($parentalbum)) {
				if ($what == 'image') {
					$type = IMAGE_SORT_TYPE;
				} else {
					$type = $_zp_gallery->getSortType();
				}
			} else {
				$type = $parentalbum->getSortType($what);
			}
		}
		return $type;
	}

	/**
	 * Stores the sort type for the album
	 *
	 * @param string $sorttype the album sort type
	 * @param string $what 'Description'image' or 'album'
	 */
	function setSortType($sorttype, $what = 'image') {
		if ($what == 'image') {
			$this->set('sort_type', $sorttype);
		} else {
			$this->set('subalbum_sort_type', $sorttype);
		}
	}

	/**
	 * Returns the DB key associated with the image sort type
	 *
	 * @param string $sorttype the sort type
	 * @return string
	 */
	function getImageSortKey($sorttype = null) {
		if (is_null($sorttype)) {
			$sorttype = $this->getSortType();
		}
		return lookupSortKey($sorttype, 'filename', 'images');
	}

	/**
	 * Returns the DB key associated with the subalbum sort type
	 *
	 * @param string $sorttype subalbum sort type
	 * @return string
	 */
	function getAlbumSortKey($sorttype = null) {
		if (empty($sorttype)) {
			$sorttype = $this->getSortType('album');
		}
		return lookupSortKey($sorttype, 'sort_order', 'albums');
	}

	/**
	 * Returns all folder names for all the subdirectories.
	 *
	 * @param string $page  Which page of subalbums to display.
	 * @param string $sorttype The sort strategy
	 * @param bool $sortdirection The direction of the sort
	 * @param bool $care set to false if the order does not matter
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		if ($page == 0) {
			return $this->subalbums;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			return array_slice($this->subalbums, $albums_per_page * ($page - 1), $albums_per_page);
		}
	}

	function getOffspring() {
		$list = $this->subalbums;
		$mine = array();
		if (is_array($list)) {
			foreach ($list as $subalbum) {
				$obj = newAlbum($subalbum);
				$mine = array_merge($mine, $obj->getOffspring());
			}
			return(array_merge($list, $mine));
		} else {
			return array();
		}
	}

	function getSidecars() {
		return array();
	}

	function addSidecar($car) {
		$this->sidecars[$car] = $car;
	}

	/**
	 * Returns the count of subalbums
	 *
	 * @return int
	 */
	function getNumAlbums() {
		return count($this->getAlbums(0, NULL, NULL, false));
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param string $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param bool $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		// Return the cut of images based on $page. Page 0 means show all.
		if ($page == 0) {
			return $this->images;
		} else {
			// Only return $firstPageCount images if we are on the first page and $firstPageCount > 0
			if (($page == 1) && ($firstPageCount > 0)) {
				$pageStart = 0;
				$images_per_page = $firstPageCount;
			} else {
				if ($firstPageCount > 0) {
					$fetchPage = $page - 2;
				} else {
					$fetchPage = $page - 1;
				}
				$images_per_page = max(1, getOption('images_per_page'));
				$pageStart = (int) ($firstPageCount + $images_per_page * $fetchPage);
			}
			return array_slice($this->images, $pageStart, $images_per_page);
		}
	}

	/**
	 * Returns the number of images in this album (not counting its subalbums)
	 *
	 * @return int
	 */
	function getNumImages() {
		if (is_null($this->images)) {
			return count($this->getImages(0, 0, NULL, NULL, false));
		}
		return count($this->images);
	}

	/**
	 * Returns an image from the album based on the index passed.
	 *
	 * @param int $index
	 * @return int
	 */
	function getImage($index) {
		$images = $this->getImages();
		if ($index >= 0 && $index < count($images)) {
			return newImage($this, $images[$index]);
		}
		return false;
	}

	/**
	 * Gets the album's set thumbnail image from the database if one exists,
	 * otherwise, finds the first image in the album or sub-album and returns it
	 * as an Image object.
	 *
	 * $recures	array recursion loop prevention
	 * @return Image
	 */
	function getAlbumThumbImage($recurse = array()) {
		global $_zp_albumthumb_selector, $_zp_gallery;

		if (!is_null($this->albumthumbnail)) {
			return $this->albumthumbnail;
		}

		$albumdir = $this->localpath;
		$thumb = $this->get('thumb');
		if (is_null($thumb)) {
			$this->set('thumb', $thumb = getOption('AlbumThumbSelect'));
		}
		$i = strpos($thumb, '/');
		if ($root = ($i === 0)) {
			$thumb = substr($thumb, 1); // strip off the slash
			$albumdir = ALBUM_FOLDER_SERVERPATH;
		}
		if (!empty($thumb) && !is_numeric($thumb)) {
			if (file_exists($albumdir . internalToFilesystem($thumb))) {
				if ($i === false) {
					return newImage($this, $thumb);
				} else {
					$pieces = explode('/', $thumb);
					$i = count($pieces);
					$thumb = $pieces[$i - 1];
					unset($pieces[$i - 1]);
					$albumdir = implode('/', $pieces);
					if (!$root) {
						$albumdir = $this->name . "/" . $albumdir;
					} else {
						$albumdir = $albumdir . "/";
					}
					$this->albumthumbnail = newImage(newAlbum($albumdir), $thumb);
					return $this->albumthumbnail;
				}
			} else {
				$this->set('thumb', $thumb = getOption('AlbumThumbSelect'));
			}
		}
		if ($shuffle = empty($thumb)) {
			$thumbs = $this->getImages(0, 0, NULL, NULL, false);
		} else {
			$thumbs = $this->getImages(0, 0, $_zp_albumthumb_selector[(int) $thumb]['field'], $_zp_albumthumb_selector[(int) $thumb]['direction']);
		}
		if (!is_null($thumbs)) {
			if ($shuffle) {
				shuffle($thumbs);
			}
			$mine = $this->isMyItem(LIST_RIGHTS);
			$other = NULL;
			while (count($thumbs) > 0) {
				// first check for images
				$thumb = array_shift($thumbs);
				$thumb = newImage($this, $thumb);
				if ($mine || $thumb->getShow()) {
					if (isImagePhoto($thumb)) {
						// legitimate image
						$this->albumthumbnail = $thumb;
						return $this->albumthumbnail;
					} else {
						if (!is_null($thumb->objectsThumb)) {
							//	"other" image with a thumb sidecar
							$this->albumthumbnail = $thumb;
							return $this->albumthumbnail;
						} else {
							if (is_null($other)) {
								$other = $thumb;
							}
						}
					}
				}
			}
			if (!is_null($other)) {
				//	"other" image, default thumb
				$this->albumthumbnail = $other;
				return $this->albumthumbnail;
			}
		}

		// Otherwise, look in sub-albums.
		$subalbums = $this->getAlbums();
		if (!is_null($subalbums)) {
			if ($shuffle) {
				shuffle($subalbums);
			}
			while (count($subalbums) > 0) {
				$folder = array_pop($subalbums);
				if (in_array($folder, $recurse)) {
					continue;
				}
				$recurse[] = $folder;
				$subalbum = newAlbum($folder);
				$pwd = $subalbum->getPassword();
				if (($subalbum->getShow() && empty($pwd)) || $subalbum->isMyItem(LIST_RIGHTS)) {
					$thumb = $subalbum->getAlbumThumbImage($recurse);
					if (strtolower(get_class($thumb)) !== 'transientimage' && $thumb->exists) {
						$this->albumthumbnail = $thumb;
						return $thumb;
					}
				}
			}
		}

		$nullimage = SERVERPATH . '/' . ZENFOLDER . '/images/imageDefault.png';
		// check for theme imageDefault.png
		$theme = '';
		$uralbum = getUralbum($this);
		$albumtheme = $uralbum->getAlbumTheme();
		if (!empty($albumtheme)) {
			$theme = $albumtheme;
		} else {
			$theme = $_zp_gallery->getCurrentTheme();
		}
		if (!empty($theme)) {
			$themeimage = SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/images/imageDefault.png';
			if (file_exists(internalToFilesystem($themeimage))) {
				$nullimage = $themeimage;
			}
		}

		$this->albumthumbnail = new transientimage($this, $nullimage);
		return $this->albumthumbnail;
	}

	/**
	 * Gets the thumbnail URL for the album thumbnail image as returned by $this->getAlbumThumbImage();
	 * @return string
	 */
	function getThumb() {
		$image = $this->getAlbumThumbImage();
		return $image->getThumb('album');
	}

	/**
	 * Stores the thumbnail path for an album thumg
	 *
	 * @param string $filename thumbnail path
	 */
	function setThumb($filename) {
		$this->set('thumb', $filename);
	}

	/**
	 * Returns an URL to the album, including the current page number
	 *
	 * @param string $page if not null, apppend as page #
	 * @return string
	 */
	function getLink($page = NULL) {
		global $_zp_current_album;
		global $_zp_page;
		if (is_null($page) && $_zp_current_album && $_zp_current_album->name == $this->name) {
			$page = $_zp_page;
		}
		$rewrite = pathurlencode($this->linkname) . '/';
		$plain = '/index.php?album=' . pathurlencode($this->name);
		if ($page > 1) {
			$rewrite .=_PAGE_ . '/' . $page;
			$plain .= "&page=$page";
		}
		return zp_apply_filter('getLink', rewrite_path($rewrite, $plain), $this, $page);
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		$rslt = false;
		if (parent::remove()) {
			query("DELETE FROM " . prefix('options') . "WHERE `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('comments') . "WHERE `type`='albums' AND `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $this->id);
			$rslt = true;
			$filestoremove = safe_glob(substr($this->localpath, 0, -1) . '.*');
			foreach ($filestoremove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					@chmod($file, 0777);
					unlink($file);
				}
			}
		}
		return $rslt;
	}

	protected function _removeCache($folder) {
		$folder = trim($folder, '/');
		$success = true;
		$filestoremove = safe_glob(SERVERCACHE . '/' . $folder . '/*');
		foreach ($filestoremove as $file) {
			@chmod($file, 0777);
			$success = $success && @unlink($file);
		}
		@rmdir(SERVERCACHE . '/' . $folder);
		return $success;
	}

	/**
	 * common album move code
	 * @param type $newfolder
	 * @return int
	 */
	protected function _move($newfolder) {
		// First, ensure the new base directory exists.
		$dest = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($newfolder);
		// Check to see if the destination already exists
		if (file_exists($dest)) {
			// Disallow moving an album over an existing one.
			if (!(CASE_INSENSITIVE && strtolower($dest) == strtolower(rtrim($this->localpath, '/')))) {
				return 3;
			}
		}
		$oldfolders = explode('/', $this->name);
		$newfolders = explode('/', $newfolder);
		$sub = count($newfolders) > count($oldfolders);
		if ($sub) {
			for ($i = 0; $i < count($oldfolders); $i++) {
				if ($newfolders[$i] != $oldfolders[$i]) {
					$sub = false;
					break;
				}
			}
			if ($sub) {
				// Disallow moving to a subfolder of the current folder.
				return 4;
			}
		}
		$filemask = substr($this->localpath, 0, -1) . '.*';

		@chmod($this->localpath, 0777);
		$success = @rename(rtrim($this->localpath, '/'), $dest);
		@chmod($dest, FOLDER_MOD);
		if ($success) {
			//purge the cache
			$success = $success && $this->_removeCache(substr($this->localpath, strlen(ALBUM_FOLDER_SERVERPATH)));
			$this->name = $newfolder;
			$this->localpath = $dest . "/";
			$filestomove = safe_glob($filemask);
			foreach ($filestomove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$d = stripslashes($dest) . '.' . getSuffix($file);
					@chmod($file, 0777);
					$success = $success && @rename($file, $d);
					@chmod($d, FILE_MOD);
				}
			}
			clearstatcache();
			$success = self::move($newfolder);
			if ($success) {
				$this->updateParent($newfolder);
				//rename the cache folder
				$cacherename = @rename(SERVERCACHE . '/' . $this->name, SERVERCACHE . '/' . $newfolder);
				return 0;
			}
		}
		return 1;
	}

	/**
	 * Move this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to move to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function move($newfolder) {
		return parent::move(array('folder' => $newfolder));
	}

	/**
	 * Rename this album folder. Alias for move($newfoldername);
	 * @param string $newfolder the new folder name of this album (including subalbum paths)
	 * @return boolean true on success or false on failure.
	 */
	function rename($newfolder) {
		return $this->move($newfolder);
	}

	protected function succeed($dest) {
		return false;
	}

	/**
	 * Copy this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to copy to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function copy($newfolder) {
		// album name to destination folder
		if (substr($newfolder, -1, 1) != '/')
			$newfolder .= '/';
		$newfolder .= basename($this->localpath);
		// First, ensure the new base directory exists.
		$oldfolder = $this->name;
		$dest = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($newfolder);
		// Check to see if the destination directory already exists
		if (file_exists($dest)) {
			// Disallow moving an album over an existing one.
			return 3;
		}
		if (substr($newfolder, count($oldfolder)) == $oldfolder) {
			// Disallow copying to a subfolder of the current folder (infinite loop).
			return 4;
		}
		$success = $this->succeed($dest);
		$filemask = substr($this->localpath, 0, -1) . '.*';
		if ($success) {
			//	replicate the album metadata and sub-files
			$uniqueset = array('folder' => $newfolder);
			$parentname = dirname($newfolder);
			if (empty($parentname) || $parentname == '/' || $parentname == '.') {
				$uniqueset['parentid'] = NULL;
			} else {
				$parent = newAlbum($parentname);
				$uniqueset['parentid'] = $parent->getID();
			}
			$newID = parent::copy($uniqueset);
			if ($newID) {
				//	replicate the tags
				storeTags(readTags($this->getID(), 'albums', ''), $newID, 'albums');
				//	copy the sidecar files
				$filestocopy = safe_glob($filemask);
				foreach ($filestocopy as $file) {
					if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
						$success = $success && @copy($file, dirname($dest) . '/' . basename($file));
					}
				}
			}
		}
		if ($success) {
			return 0;
		} else {
			return 1;
		}
	}

	/**
	 * For every image in the album, look for its file. Delete from the database
	 * if the file does not exist. Same for each sub-directory/album.
	 *
	 * @param bool $deep set to true for a thorough cleansing
	 */
	function garbageCollect($deep = false) {

	}

	/**
	 * Load all of the filenames that are found in this Albums directory on disk.
	 * Returns an array with all the names.
	 *
	 * @param  $dirs Whether or not to return directories ONLY with the file array.
	 * @return array
	 */
	protected function loadFileNames($dirs = false) {

	}

	/**
	 * Returns true if the album is "dynamic"
	 *
	 * @return bool
	 */
	function isDynamic() {
		return false;
	}

	/**
	 * Returns the search parameters for a dynamic album
	 *
	 * @return string
	 */
	function getSearchParams() {
		return NULL;
	}

	/**
	 * Sets the search parameters of a dynamic album
	 *
	 * @param string $params The search string to produce the dynamic album
	 */
	function setSearchParams($params) {

	}

	/**
	 * Returns the search engine for a dynamic album
	 *
	 * @return object
	 */
	function getSearchEngine() {
		return NULL;
	}

	/**
	 * checks access to the album
	 * @param bit $action What the requestor wants to do
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action) {
		global $_zp_current_admin_obj;
		if ($parent = parent::isMyItem($action)) {
			return $parent;
		}
		if ($_zp_current_admin_obj && $_zp_current_admin_obj->getUser() == $this->getOwner()) {
			return true;
		}
		if (zp_loggedin($action)) {
			$subRights = $this->subRights();
			if ($subRights) {
				$rights = LIST_RIGHTS;
				if ($subRights & (MANAGED_OBJECT_RIGHTS_EDIT)) {
					$rights = $rights | ALBUM_RIGHTS;
				}
				if ($subRights & MANAGED_OBJECT_RIGHTS_UPLOAD) {
					$rights = $rights | UPLOAD_RIGHTS;
				}
				if ($action & $rights) {
					return true;
				} else {
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Checks if guest is loggedin for the album
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		return checkAlbumPassword($this, $hint);
	}

	/**
	 *
	 * returns true if there is any protection on the album
	 */
	function isProtected() {
		return $this->checkforGuest() != 'zp_public_access';
	}

	/**
	 * Owner functions
	 */
	function getOwner() {
		global $_zp_authority;
		$owner = $this->get('owner');
		if (empty($owner)) {
			$p = $this->getParent();
			if (is_object($p)) {
				$owner = $p->getOwner();
			} else {
				$admin = $_zp_authority->getMasterUser();
				$owner = $admin->getUser();
			}
		}
		return $owner;
	}

	function setOwner($owner) {
		$this->set('owner', $owner);
	}

	/**
	 *
	 * Date at which the album last discovered an image
	 */
	function getUpdatedDate() {
		return $this->get('updateddate');
	}

	function setUpdatedDate($date) {
		return $this->set('updateddate', $date);
	}

	/**
	 * Returns the theme for the album
	 *
	 * @return string
	 */
	function getAlbumTheme() {
		global $_zp_gallery;
		if (in_context(ZP_SEARCH_LINKED)) {
			return $_zp_gallery->getCurrentTheme();
		} else {
			return $this->get('album_theme');
		}
	}

	/**
	 * Sets the theme of the album
	 *
	 * @param string $theme
	 */
	function setAlbumTheme($theme) {
		$this->set('album_theme', $theme);
	}

	/**
	 * returns the album watermark
	 * @return string
	 */
	function getWatermark() {
		return $this->get('watermark');
	}

	/**
	 * Sets the album watermark
	 * @param string $wm
	 */
	function setWatermark($wm) {
		$this->set('watermark', $wm);
	}

	/**
	 * Returns the album watermark thumb
	 *
	 * @return bool
	 */
	function getWatermarkThumb() {
		return $this->get('watermark_thumb');
	}

	/**
	 * Sets the custom watermark usage
	 *
	 * @param $wm
	 */
	function setWatermarkThumb($wm) {
		$this->set('watermark_thumb', $wm);
	}

	/**
	 * returns the mitigated album rights.
	 * returns NULL if not a managed album
	 */
	function subRights() {
		global $_zp_admin_album_list;
		if (!is_null($this->subrights)) {
			return $this->subrights;
		}
		$this->subrights = 0;
		if (zp_loggedin()) {
			if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
				$this->subrights = MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW;
				return $this->subrights;
			}
			getManagedAlbumList();
			if (count($_zp_admin_album_list) > 0) {
				$uralbum = getUrAlbum($this);
				if ($uralbum->name == $this->name) {
					if (isset($_zp_admin_album_list[$uralbum->name])) {
						$this->subrights = $_zp_admin_album_list[$uralbum->name] | MANAGED_OBJECT_MEMBER;
						if (zp_loggedin(VIEW_UNPUBLISHED_RIGHTS))
							$this->subrights = $this->subrights | MANAGED_OBJECT_RIGHTS_VIEW;
					}
				} else {
					$this->subrights = $uralbum->subRights();
				}
			}
		}
		return $this->subrights;
	}

	/**
	 * sortImageArray will sort an array of Images based on the given key. The
	 * key must be one of (filename, title, sort_order) at the moment.
	 *
	 * @param array $images The array of filenames to be sorted.
	 * @param  string $sorttype optional sort type
	 * @param  bool $sortdirection optional sort direction
	 * @param bool $mine set to true/false to override ownership clause
	 * @return array
	 */
	protected function sortImageArray($images, $sorttype, $sortdirection, $mine = NULL) {
		if (is_null($mine)) {
			$mine = $this->isMyItem(LIST_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS);
		}
		if ($mine && !($mine & (MANAGE_ALL_ALBUM_RIGHTS))) {
			//	check for managed album view unpublished image rights
			$mine = $this->subRights() & (MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW);
		}
		$sortkey = $this->getImageSortKey($sorttype);
		if ((trim($sortkey . '`') == 'sort_order') || ($sortkey == 'RAND()')) {
			// manual sort is always ascending
			$order = false;
		} else {
			if (is_null($sortdirection)) {
				$order = $this->getSortDirection('image');
			} else {
				if (is_string($sortdirection)) {
					$order = $sortdirection && strtolower($sortdirection) != 'asc';
				} else {
					$order = (int) $sortdirection;
				}
			}
		}
		$sql = "SELECT * FROM " . prefix("images") . " WHERE `albumid`= " . $this->getID() . ' ORDER BY ' . $sortkey;
		if ($order)
			$sql .= ' DESC';
		$result = query($sql);
		$results = array();
		while ($row = db_fetch_assoc($result)) {
			$filename = $row['filename'];
			if (($key = array_search($filename, $images)) !== false) {
				// the image exists in the filesystem
				$results[] = $row;
				unset($images[$key]);
			} else { // the image no longer exists
				$id = $row['id'];
				query("DELETE FROM " . prefix('images') . " WHERE `id`=$id"); // delete the record
				query("DELETE FROM " . prefix('comments') . " WHERE `type` ='images' AND `ownerid`= '$id'"); // remove image comments
			}
		}
		db_free_result($result);
		foreach ($images as $filename) {
			// these images are not in the database
			$imageobj = newImage($this, $filename);
			$results[] = $imageobj->getData();
		}
		// now put the results into the right order
		$results = sortByKey($results, str_replace('`', '', $sortkey), $order);
		// the results are now in the correct order
		$images_ordered = array();
		foreach ($results as $key => $row) {
			// check for visible
			if ($row['show'] || $mine) {
				// don't display it
				$images_ordered[] = $row['filename'];
			}
		}
		return $images_ordered;
	}

	/**
	 * changes the parent of an album for move/copy
	 *
	 * @param string $newfolder The folder name of the new parent
	 */
	protected function updateParent($newfolder) {
		$this->name = $newfolder;
		$parentname = dirname($newfolder);
		if ($parentname == '/' || $parentname == '.')
			$parentname = '';
		if (empty($parentname)) {
			$this->set('parentid', NULL);
		} else {
			$parent = newAlbum($parentname);
			$this->set('parentid', $parent->getID());
		}
		$this->save();
	}

	/**
	 * Simply creates objects of all the images and sub-albums in this album to
	 * load accurate values into the database.
	 */
	function preLoad() {
		if ($this->isDynamic())
			return;
		$images = $this->getImages(0);
		$subalbums = $this->getAlbums(0);
		foreach ($subalbums as $dir) {
			$album = newAlbum($dir);
			$album->preLoad();
		}
	}

	/**
	 * Returns the album following the current album
	 *
	 * @return object
	 */
	function getNextAlbum() {
		global $_zp_gallery;
		if (is_null($parent = $this->getParent())) {
			$albums = $_zp_gallery->getAlbums(0);
		} else {
			$albums = $parent->getAlbums(0);
		}
		$inx = array_search($this->name, $albums) + 1;
		if ($inx >= 0 && $inx < count($albums)) {
			return newAlbum($albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the album prior to the current album
	 *
	 * @return object
	 */
	function getPrevAlbum() {
		global $_zp_gallery;
		if (is_null($parent = $this->getParent())) {
			$albums = $_zp_gallery->getAlbums(0);
		} else {
			$albums = $parent->getAlbums(0);
		}
		$inx = array_search($this->name, $albums) - 1;
		if ($inx >= 0 && $inx < count($albums)) {
			return newAlbum($albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the page number in the gallery or the parent album of this album
	 *
	 * @return int
	 */
	function getGalleryPage() {
		global $_zp_gallery;
		if ($this->index == null) {
			if (is_null($parent = $this->getParent())) {
				$albums = $_zp_gallery->getAlbums(0);
			} else {
				$albums = $parent->getAlbums(0);
			}
			$this->index = array_search($this->name, $albums);
		}
		return floor(($this->index / galleryAlbumsPerPage()) + 1);
	}

}

class Album extends AlbumBase {

	/**
	 * Constructor for albums
	 *
	 * @param string $folder8 folder name (UTF8) of the album
	 * @param bool $cache load from cache if present
	 * @return Album
	 */
	function __construct($folder8, $cache = true, $quiet = false) {
		$folder8 = trim($folder8, '/');
		$folderFS = internalToFilesystem($folder8);
		$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS . "/";
		$this->linkname = $this->name = $folder8;
		$this->localpath = $localpath;
		if (!$this->exists = AlbumBase::albumCheck($folder8, $folderFS, $quiet, !file_exists($this->localpath) || !(is_dir($this->localpath)) || $folder8 && $folder8{0} == '.' || preg_match('~/\.*/~', $folder8))) {
			return;
		}
		$new = $this->instantiate('albums', array('folder' => $this->name), 'folder', $cache, empty($folder8));
		$this->checkForPublish();
		if ($new) {
			$this->save();
			zp_apply_filter('new_album', $this);
		}
		zp_apply_filter('album_instantiate', $this);
	}

	/**
	 * Sets default values for a new album
	 *
	 * @return bool
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		// Set default data for a new Album (title and parent_id)
		parent::setDefaults();
		$this->set('mtime', filemtime($this->localpath));
		if (!$_zp_gallery->getAlbumUseImagedate()) {
			$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
		}
		return true;
	}

	/**
	 * Guts of fetching the subalbums
	 * @return array
	 */
	protected function _getAlbums() {
		$dirs = $this->loadFileNames(true);
		$subalbums = array();
		foreach ($dirs as $dir) {
			$dir = $this->name . '/' . $dir;
			$subalbums[] = $dir;
		}
		return $subalbums;
	}

	/**
	 * Returns all folder names for all the subdirectories.
	 *
	 * @param string $page  Which page of subalbums to display.
	 * @param string $sorttype The sort strategy
	 * @param bool $sortdirection The direction of the sort
	 * @param bool $care set to false if the order does not matter
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		global $_zp_gallery;
		if (!$this->exists)
			return array();
		if ($mine || is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			if (is_null($sorttype)) {
				$sorttype = $this->getSortType('album');
			}
			if (is_null($sortdirection)) {
				$sortdirection = $this->getSortDirection('album');
			}
			$sortdirection = $sortdirection && strtolower($sortdirection) != 'asc';
			$dirs = $this->loadFileNames(true);
			$subalbums = array();
			foreach ($dirs as $dir) {
				$dir = $this->name . '/' . $dir;
				$subalbums[] = $dir;
			}
			$key = $this->getAlbumSortKey($sorttype);
			$this->subalbums = $_zp_gallery->sortAlbumArray($this, $subalbums, $key, $sortdirection, $mine);
			$this->lastsubalbumsort = $sorttype . $sortdirection;
		}
		return parent::getAlbums($page);
	}

	/**
	 * Returns the side car files associated with the album
	 *
	 * @return array
	 */
	function getSidecars() {
		$files = safe_glob(substr($this->localpath, 0, -1) . '.*');
		$result = array();
		foreach ($files as $file) {
			if (!is_dir($file) && in_array(strtolower(getSuffix($file)), $this->sidecars)) {
				$result[basename($file)] = $file;
			}
		}
		return $result;
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param int $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param bool $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		if ($this->exists && $this->getID()) {
			if (is_null($sorttype)) {
				$sorttype = $this->getSortType();
			}
			if (is_null($sortdirection)) {
				$sortdirection = $this->getSortDirection('image');
			}
			$sortdirection = $sortdirection && strtolower($sortdirection) != 'asc';
			if ($mine || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
				$images = $this->loadFileNames();
				$this->images = array_values($this->sortImageArray($images, $sorttype, $sortdirection, $mine));
				$this->lastimagesort = $sorttype . $sortdirection;
			}
			return parent::getImages($page, $firstPageCount);
		} else {
			return array();
		}
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		$rslt = false;
		if (parent::remove()) {
			foreach ($this->getImages() as $filename) {
				$image = newImage($this, $filename, true);
				$image->remove();
			}
			foreach ($this->getAlbums() as $folder) {
				$subalbum = newAlbum($folder, true, true);
				$subalbum->remove();
			}
			$curdir = getcwd();
			chdir($this->localpath);
			$filelist = safe_glob('*');
			foreach ($filelist as $file) {
				if (($file != '.') && ($file != '..')) {
					@chmod($file, 0777);
					unlink($this->localpath . $file); // clean out any other files in the folder
				}
			}
			chdir($curdir);
			clearstatcache();
			query("DELETE FROM " . prefix('options') . "WHERE `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('comments') . "WHERE `type`='albums' AND `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $this->id);
			$success = true;
			$filestoremove = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
			foreach ($filestoremove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					@chmod($file, 0777);
					$success = $success && unlink($file);
				}
			}
			$success = $success && $this->_removeCache(substr($this->localpath, strlen(ALBUM_FOLDER_SERVERPATH)));
			@chmod($this->localpath, 0777);
			$rslt = @rmdir($this->localpath) && $success;
		}
		clearstatcache();
		return $rslt;
	}

	/**
	 * Move this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to move to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function move($newfolder) {
		$oldfolder = $this->name;
		$rslt = $this->_move($newfolder);
		if (!$rslt) {
			// Then: go through the db and change the album (and subalbum) paths. No ID changes are necessary for a move.
			// Get the subalbums.
			$sql = "SELECT id, folder FROM " . prefix('albums') . " WHERE folder LIKE " . db_quote(db_LIKE_escape($oldfolder) . '%');
			$result = query($sql);
			if ($result) {
				$len = strlen($oldfolder);
				while ($subrow = db_fetch_assoc($result)) {
					$sql = "UPDATE " . prefix('albums') . " SET folder=" . db_quote($newfolder . substr($subrow['folder'], $len)) . " WHERE id=" . $subrow['id'];
					query($sql);
				}
			}
			db_free_result($result);
			return 0;
		}
		return $rslt;
	}

	protected function succeed($dest) {
		return mkdir_recursive($dest, FOLDER_MOD) === TRUE;
	}

	/**
	 * Copy this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to copy to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function copy($newfolder) {
		$rslt = parent::copy($newfolder);
		if (!$rslt) {
			$newfolder .= '/' . basename($this->name);
			$success = true;
			//	copy the images
			$images = $this->getImages(0);
			foreach ($images as $imagename) {
				$image = newImage($this, $imagename);
				if ($rslt = $image->copy($newfolder)) {
					$success = false;
				}
			}
			// copy the subalbums.
			$subalbums = $this->getAlbums(0);
			foreach ($subalbums as $subalbumname) {
				$subalbum = newAlbum($subalbumname);
				if ($rslt = $subalbum->copy($newfolder)) {
					$success = false;
				}
			}
			if ($success) {
				return 0;
			}
			return 1;
		}
		return $rslt;
	}

	/**
	 * For every image in the album, look for its file. Delete from the database
	 * if the file does not exist. Same for each sub-directory/album.
	 *
	 * @param bool $deep set to true for a thorough cleansing
	 */
	function garbageCollect($deep = false) {
		if (is_null($this->images))
			$this->getImages();
		$result = query("SELECT * FROM " . prefix('images') . " WHERE `albumid` = '" . $this->id . "'");
		$dead = array();
		$live = array();

		$files = $this->loadFileNames();

		// Does the filename from the db row match any in the files on disk?
		while ($row = db_fetch_assoc($result)) {
			if (!in_array($row['filename'], $files)) {
				// In the database but not on disk. Kill it.
				$dead[] = $row['id'];
			} else if (in_array($row['filename'], $live)) {
				// Duplicate in the database. Kill it.
				$dead[] = $row['id'];
				// Do something else here? Compare titles/descriptions/metadata/update dates to see which is the latest?
			} else {
				$live[] = $row['filename'];
			}
		}
		db_free_result($result);

		if (count($dead) > 0) {
			$sql = "DELETE FROM " . prefix('images') . " WHERE `id` = '" . array_pop($dead) . "'";
			$sql2 = "DELETE FROM " . prefix('comments') . " WHERE `type`='albums' AND `ownerid` = '" . array_pop($dead) . "'";
			foreach ($dead as $id) {
				$sql .= " OR `id` = '$id'";
				$sql2 .= " OR `ownerid` = '$id'";
			}
			query($sql);
			query($sql2);
		}

		// Get all sub-albums and make sure they exist.
		$result = query("SELECT * FROM " . prefix('albums') . " WHERE `folder` LIKE " . db_quote(db_LIKE_escape($this->name) . '%'));
		$dead = array();
		$live = array();
		// Does the dirname from the db row exist on disk?
		while ($row = db_fetch_assoc($result)) {
			if (!is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($row['folder'])) || in_array($row['folder'], $live) || substr($row['folder'], -1) == '/' || substr($row['folder'], 0, 1) == '/') {
				$dead[] = $row['id'];
			} else {
				$live[] = $row['folder'];
			}
		}
		db_free_result($result);
		if (count($dead) > 0) {
			$sql = "DELETE FROM " . prefix('albums') . " WHERE `id` = '" . array_pop($dead) . "'";
			$sql2 = "DELETE FROM " . prefix('comments') . " WHERE `type`='albums' AND `ownerid` = '" . array_pop($dead) . "'";
			foreach ($dead as $albumid) {
				$sql .= " OR `id` = '$albumid'";
				$sql2 .= " OR `ownerid` = '$albumid'";
			}
			query($sql);
			query($sql2);
		}

		if ($deep) {
			foreach ($this->getAlbums(0) as $dir) {
				$subalbum = newAlbum($dir);
				// Could have been deleted if it didn't exist above...
				if ($subalbum->exists)
					$subalbum->garbageCollect($deep);
			}
		}
	}

	/**
	 * Load all of the filenames that are found in this Albums directory on disk.
	 * Returns an array with all the names.
	 *
	 * @param  $dirs Whether or not to return directories ONLY with the file array.
	 * @return array
	 */
	protected function loadFileNames($dirs = false) {
		clearstatcache();
		$albumdir = $this->localpath;
		$dir = @opendir($albumdir);
		if (!$dir) {
			if (is_dir($albumdir)) {
				$msg = sprintf(gettext("Error: The album %s is not readable."), html_encode($this->name));
			} else {
				$msg = sprintf(gettext("Error: The album named %s cannot be found."), html_encode($this->name));
			}
			debugLogBacktrace($msg);
			return array();
		}

		$files = array();
		$others = array();

		while (false !== ($file = readdir($dir))) {
			$file8 = filesystemToInternal($file);
			if (@$file8{0} != '.') {
				if ($dirs && (is_dir($albumdir . $file) || hasDynamicAlbumSuffix($file))) {
					$files[] = $file8;
				} else if (!$dirs && is_file($albumdir . $file)) {
					if ($handler = Gallery::imageObjectClass($file)) {
						$files[] = $file8;
						if ($handler !== 'Image') {
							$others[] = $file8;
						}
					}
				}
			}
		}
		closedir($dir);
		if (count($others) > 0) {
			$others_thumbs = array();
			foreach ($others as $other) {
				$others_root = substr($other, 0, strrpos($other, "."));
				foreach ($files as $image) {
					if ($image != $other) {
						$image_root = substr($image, 0, strrpos($image, "."));
						if ($image_root == $others_root && Gallery::imageObjectClass($image) == 'Image') {
							$others_thumbs[] = $image;
						}
					}
				}
			}
			$files = array_diff($files, $others_thumbs);
		}

		if ($dirs) {
			return zp_apply_filter('album_filter', $files);
		} else {
			return zp_apply_filter('image_filter', $files);
		}
	}

}

class dynamicAlbum extends AlbumBase {

	var $searchengine; // cache the search engine for dynamic albums
	var $imageNames; // list of images for handling duplicate file names

	function __construct($folder8, $cache = true, $quiet = false) {
		$folder8 = trim($folder8, '/');
		$folderFS = internalToFilesystem($folder8);
		$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS;
		$this->linkname = $this->name = $folder8;
		$this->localpath = rtrim($localpath, '/');
		if (!$this->exists = AlbumBase::albumCheck($folder8, $folderFS, $quiet, !file_exists($this->localpath) || is_dir($this->localpath))) {
			return;
		}
		$new = $this->instantiate('albums', array('folder' => $this->name), 'folder', $cache, empty($folder8));
		if (!is_dir(stripSuffix($this->localpath))) {
			$this->linkname = stripSuffix($folder8);
		}
		if ($new || (filemtime($this->localpath) > $this->get('mtime'))) {
			$constraints = '';
			$data = file_get_contents($this->localpath);
			while (!empty($data)) {
				$data1 = trim(substr($data, 0, $i = strpos($data, "\n")));
				if ($i === false) {
					$data1 = $data;
					$data = '';
				} else {
					$data = substr($data, $i + 1);
				}
				if (strpos($data1, 'WORDS=') !== false) {
					$words = "words=" . urlencode(substr($data1, 6));
				}
				if (strpos($data1, 'THUMB=') !== false) {
					$thumb = trim(substr($data1, 6));
					$this->set('thumb', $thumb);
				}
				if (strpos($data1, 'FIELDS=') !== false) {
					$fields = "&searchfields=" . trim(substr($data1, 7));
				}
				if (strpos($data1, 'CONSTRAINTS=') !== false) {
					$constraint = trim(substr($data1, 12));
					$constraints = '&' . $constraint;
				}
			}
			if (!empty($words)) {
				if (empty($fields)) {
					$fields = '&searchfields=tags';
				}
				$this->set('search_params', $words . $fields . $constraints);
			}
			$this->set('mtime', filemtime($this->localpath));
			if ($new) {
				$title = $this->get('title');
				$this->set('title', stripSuffix($title)); // Strip the suffix
				$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
				$this->save();
				zp_apply_filter('new_album', $this);
			}
		}
		zp_apply_filter('album_instantiate', $this);
	}

	/**
	 * Returns all folder names for all the subdirectories.
	 *
	 * @param string $page  Which page of subalbums to display.
	 * @param string $sorttype The sort strategy
	 * @param bool $sortdirection The direction of the sort
	 * @param bool $care set to false if the order does not matter
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		global $_zp_gallery;
		if (!$this->exists)
			return array();
		if (is_null($sorttype)) {
			$sorttype = $this->getSortType('album');
		}
		if (is_null($sortdirection)) {
			$sortdirection = $this->getSortDirection('album');
		}
		$sortdirection = $sortdirection && strtolower($sortdirection) != 'asc';
		if ($mine || is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			$searchengine = $this->getSearchEngine();
			$subalbums = $searchengine->getAlbums(0, $sorttype, $sortdirection, $care, $mine);
			$key = $this->getAlbumSortKey($sorttype);
			$this->subalbums = $_zp_gallery->sortAlbumArray($this, $subalbums, $key, $sortdirection, $mine);
			$this->lastsubalbumsort = $sorttype . $sortdirection;
		}
		return parent::getAlbums($page);
	}

	/**
	 * Returns the search parameters for a dynamic album
	 *
	 * @return string
	 */
	function getSearchParams() {
		return $this->get('search_params');
	}

	/**
	 * Sets the search parameters of a dynamic album
	 *
	 * @param string $params The search string to produce the dynamic album
	 */
	function setSearchParams($params) {
		$this->set('search_params', $params);
	}

	/**
	 * Returns the search engine for a dynamic album
	 *
	 * @return object
	 */
	function getSearchEngine() {
		if (!is_null($this->searchengine))
			return $this->searchengine;
		$this->searchengine = new SearchEngine(true);
		$params = $this->get('search_params');
		$this->searchengine->setSearchParams($params);
		$this->searchengine->setAlbum($this);
		return $this->searchengine;
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param int $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param bol $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		if (!$this->exists)
			return array();
		if (is_null($sorttype)) {
			$sorttype = $this->getSortType();
		}
		if (is_null($sortdirection)) {
			$sortdirection = $this->getSortDirection('image');
		}
		$sortdirection = $sortdirection && strtolower($sortdirection) != 'asc';
		if ($mine || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
			$searchengine = $this->getSearchEngine();
			$this->images = $searchengine->getImages(0, 0, $sorttype, $sortdirection, $care, $mine);
			$this->lastimagesort = $sorttype . $sortdirection;
			$this->imageNames = array();
			foreach ($this->images as $image) {
				$this->imageNames[$image['folder'] . '/' . $image['filename']] = $image['filename'];
			}
			ksort($this->imageNames);
		}
		return parent::getImages($page, $firstPageCount);
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		if ($rslt = parent::remove()) {
			@chmod($this->localpath, 0777);
			$rslt = @unlink($this->localpath);
			clearstatcache();
			$rslt = $rslt && $this->_removeCache(substr($this->localpath, strlen(ALBUM_FOLDER_SERVERPATH)));
		}
		return $rslt;
	}

	/**
	 * Move this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to move to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function move($newfolder) {
		return $this->_move($newfolder);
	}

	protected function succeed($dest) {
		return @copy($this->localpath, $dest);
	}

	/**
	 * Copy this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to copy to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function copy($newfolder) {
		return parent::copy($newfolder);
	}

	/**
	 * Simply creates objects of all the images and sub-albums in this album to
	 * load accurate values into the database.
	 */
	function preLoad() {
		return; // nothing to do
	}

	protected function loadFileNames($dirs = false) {
		return array();
	}

	function isDynamic() {
		return 'alb';
	}

}

class TransientAlbum extends AlbumBase {

	function __construct($folder8, $cache = true) {
		$this->instantiate('albums', array('folder' => $this->name), 'folder', true, true);
		$this->exists = false;
	}

}

?>
