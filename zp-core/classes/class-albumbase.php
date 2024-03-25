<?php
/**
 * Album Base Class
 * @package zpcore\classes\objects
 */
class AlbumBase extends MediaObject {

	public $name; // Folder name of the album (full path from the albums folder)
	public $linkname; // may have the .alb suffix stripped off
	public $localpath; // Latin1 full server path to the album
	public $exists = true; // Does the folder exist?
	public $images = null; // Full images array storage.
	public $parent = null; // The parent album name
	public $parentalbum = null; // The parent album's album object (lazy)
	public $parentalbums = null; // Array of objects of parent albums (lazy)
	public $urparentalbum = null; // The ur parent album's album object (lazy)
	public $sidecars = array(); // keeps the list of suffixes associated with this album
	public $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	public $manage_some_rights = ALBUM_RIGHTS;
	public $view_rights = ALL_ALBUMS_RIGHTS;
	protected $subalbums = null; // Full album array storage.
	protected $index;
	protected $lastimagesort = NULL; // remember the order for the last album/image sorts
	protected $lastsubalbumsort = NULL;
	protected $albumthumbnail = NULL; // remember the album thumb for the duration of the script
	protected $subrights = NULL; //	cache for album subrights
	protected $num_allalbums = null; // count of all subalbums of all sublevels
	protected $num_allimages = null; // count of all images of all sublevels
	protected $firstpageimages = null;
	protected $firstpageimages_oneimagepage = null;

	function __construct($folder8, $cache = true) {
		$this->linkname = $this->name = $folder8;
		$this->instantiate('albums', array('folder' => $this->name), 'folder', false, true);
		$this->exists = false;
	}
	
	/**
	 * Wrapper instantiation function for albums. Do not instantiate directly
	 * 
	 * @since 1.6 - Moved to AlbumBase class as static method
	 * 
	 * @param string $folder8 the name of the folder (inernal character set)
	 * @param bool $cache true if the album should be fetched from the cache
	 * @param bool $quiet true to supress error messages
	 * @return Album
	 */
	static function newAlbum($folder8, $cache = true, $quiet = false) {
		global $_zp_album_handlers;
		$suffix = getSuffix($folder8);
		if (!$suffix || !array_key_exists($suffix, $_zp_album_handlers) || is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($folder8))) {
			return new Album($folder8, $cache, $quiet);
		} else {
			return new $_zp_album_handlers[$suffix]($folder8, $cache, $quiet);
		}
	}

	/**
	 * Returns true if the object is a zenphoto 'album'
	 * 
	 * @since 1.6 - Moved to AlbumBase class as static method
	 *
	 * @param object $album
	 * @return bool
	 */
	static function isAlbumClass($album = NULL) {
		global $_zp_current_album;
		if (is_null($album)) {
			if (!in_context(ZP_ALBUM))
				return false;
			$album = $_zp_current_album;
		}
		return is_object($album) && ($album->table == 'albums');
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
				zp_error(gettext('An album object was instantiated without using the newAlbum() function.'), E_USER_WARNING);
			}
		}
// Set default data for a new Album (title and parent_id)
		$parentalbum = NULL;
		$this->setPublished($_zp_gallery->getAlbumPublish());
		$this->set('mtime', time());
		$this->setLastChange();
		$title = trim($this->name);
		$this->set('title', sanitize($title, 2));
		return true;
	}
	
	/**
	 * Returns the folder on the filesystem
	 * 
	 * @since 1.6
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Returns the folder on the filesystem
	 * 
	 * @deprecated 2.0 – Use getName() instead
	 *
	 * @return string
	 */
	function getFileName() {
		deprecationNotice(gettext('Use getName() instead'));
		return $this->getName();
	}

	/**
	 * Returns the folder on the filesystem
	 * 
	 * @deprecated 2.0 – Use getName() instead
	 * 
	 * @return string
	 */
	function getFolder() {
		deprecationNotice(gettext('Use getName() instead'));
		return $this->getName();
	}

	/**
	 * Returns The parent Album of this Album. NULL if this is a top-level album.
	 *
	 * @return object|null
	 */
	function getParent() {
		if (is_null($this->parentalbum)) {
			$slashpos = strrpos($this->name, "/");
			if ($slashpos) {
				$parent = substr($this->name, 0, $slashpos);
				$parentalbum = AlbumBase::newAlbum($parent, true, true);
				if ($parentalbum->exists) {
					return $this->parentalbum = $parentalbum;
				}
			}
		} else if ($this->parentalbum->exists) {
			return $this->parentalbum;
		}
		return NULL;
	}

	/**
	 * Gets an array of parent album objects
	 * 
	 * @since 1.5.5
	 * 
	 * @return array
	 */
	function getParents() {
		$parents = array();
		if (is_null($this->parentalbums)) {
			$albumarray = getAlbumArray($this->name, false);
			if (count($albumarray) == 1) {
				$parent = $this->getParent();
				if ($parent) {
					$this->urparentalbum = $parent;
					return $this->parentalbums = array($parent);
				}
				return $this->parentalbums = array();
			}
			$album = $this;
			while (!is_null($album = $album->getParent())) {
				array_unshift($parents, $album);
			}
			return $this->parentalbums = $parents;
		} else {
			return $this->parentalbums;
		}
		return $this->parentalbums = array();
	}

	function getParentID() {
		return $this->get('parentid');
	}
	
	/**
	 * Returns the oldest ancestor of an album. Returns the object of the album itself if there is no urparent
	 *
	 * @since 1.6.1 Replaces getUrAlbum() to align all classes
	 * 
	 * @return object
	 */
	function getUrParent() {
		if (is_null($this->urparentalbum)) {
			if (!$this->getParentID()) {
				return $this->urparentalbum = $this;
			}
			if (is_null($this->parentalbums)) {
				$albumarray = getAlbumArray($this->name, false);
				if (count($albumarray) == 1) {
					$urparent = $this->getParent();
					$this->parentalbums = array($urparent);
					return $this->urparentalbum = $urparent;
				}
				$urparent = AlbumBase::newAlbum($albumarray[0], true, true);
				if ($urparent->exists) {
					return $this->urparentalbum = $urparent;
				}
			} else {
				return $this->urlparentalbum = $this->parentalbums[0];
			}
		} else {
			return $this->urparentalbum;
		}
	}

	/**
	 * Returns the oldest ancestor of an alubm;
	 *
	 * @deprecated 2.0 Use getUrParent() instead
	 * @since 1.6
	 * 
	 * @return object
	 */
	function getUrAlbum() {
		deprecationNotice(gettext('Use getUrParent() instead'));
		return $this->getUrParent();
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the album place
	 *
	 * @param string $place text for the place field
	 */
	function setLocation($place) {
		$this->set('location', tagURLs($place));
	}

	/**
	 * Returns either the subalbum sort direction or the image sort direction of the album
	 *
	 * @param string $what 'image_' if you want the image direction,
	 *        'album' if you want it for the album
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
	 * sets sort directions for the album
	 *
	 * @param bool $val the direction
	 * @param string $what 'image_sortdirection' if you want the image direction,
	 *        'album_sortdirection' if you want it for the album
	 */
	function setSortDirection($val, $what = 'image') {
		if ($what == 'image') {
			$this->set('image_sortdirection', (int) ($val && true));
		} else {
			$this->set('album_sortdirection', (int) ($val && true));
		}
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
	 * @param string $sortdirection The direction of the sort
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

	/**
	 * Returns the count of direct child subalbums
	 *
	 * @return int
	 */
	function getNumAlbums() {
		return count($this->getAlbums(0, NULL, NULL, false));
	}
	
	/**
	 * Returns the count of all subalbums of all sublevels
	 * Note that dynamic albums are not counted
	 * 
	 * @since 1.5.2
	 */
	function getNumAllAlbums() {
		if (!is_null($this->num_allalbums)) {
			return $this->num_allalbums;
		} else {
			$count = $this->getNumAlbums();
			$subalbums = $this->getAlbums();
			foreach ($subalbums as $folder) {
				$subalbum = AlbumBase::newAlbum($folder);
				if (!$subalbum->isDynamic()) {
					$count += $subalbum->getNumAllAlbums();
				}
			}
			return $count;
		}
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param string $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param string $sortdirection optional sort direction
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
	 * Returns the number of images in this album and subalbums of all levels
	 * Note that dynamic albums are not counted.
	 * 
	 * @since 1.5.2
	 */
	function getNumAllImages() {
		if (!is_null($this->num_allimages)) {
			return $this->num_allimages;
		} else {
			$count = $this->getNumImages();
			$subalbums = $this->getAlbums();
			foreach ($subalbums as $folder) {
				$subalbum = AlbumBase::newAlbum($folder);
				if (!$subalbum->isDynamic()) {
					$count += $subalbum->getNumAllImages();
				}
			}
			return $count;
		}
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
			return Image::newImage($this, $this->images[$index]);
		}
		return false;
	}

	/**
	 * Gets the album's set thumbnail image from the database if one exists,
	 * otherwise, finds the first image in the album or sub-album and returns it
	 * as an Image object.
	 *
	 * @return Image
	 */
	function getAlbumThumbImage() {
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
					return Image::newImage($this, $thumb);
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
					$this->albumthumbnail = Image::newImage(AlbumBase::newAlbum($albumdir), $thumb);
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
			foreach($thumbs as $thumb) {
				// first check for images
				$thumb = Image::newImage($this, $thumb);
				if ($mine || $thumb->isPublished()) {
					if ($thumb->isPhoto()) {
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
				$subalbum = AlbumBase::newAlbum($folder);
				$pwd = $subalbum->getPassword();
				if (($subalbum->isPublished() && empty($pwd)) || $subalbum->isMyItem(LIST_RIGHTS)) {
					$thumb = $subalbum->getAlbumThumbImage();
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
		$uralbum = $this->getUrParent();
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
	 * @param string $path Default null, optionally pass a path constant like WEBPATH or FULLWEBPATH
	 * @return string
	 */
	function getLink($page = NULL, $path = null) {
		global $_zp_current_album, $_zp_page;
		if (is_null($page) && $_zp_current_album && $_zp_current_album->name == $this->name) {
			$page = $_zp_page;
		}
		$rewrite = pathurlencode($this->linkname) . '/';
		$plain = '/index.php?album=' . pathurlencode($this->name);
		if ($page > 1) {
			$rewrite .= _PAGE_ . '/' . $page . '/';
			$plain .= "&page=$page";
		}
		return zp_apply_filter('getLink', rewrite_path($rewrite, $plain, $path), $this, $page);
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		global $_zp_db;
		$rslt = false;
		if (PersistentObject::remove()) {
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('options') . "WHERE `ownerid`=" . $this->id);
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . "WHERE `type`='albums' AND `ownerid`=" . $this->id);
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $this->id);
			$rslt = true;
			$filestoremove = safe_glob(substr($this->localpath, 0, -1) . '.*');
			foreach ($filestoremove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					@chmod($file, 0777);
					unlink($file);
				}
			}
			$this->setUpdatedDateParents();
		}
		return $rslt;
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
		if(!$this->isValidMoveCopyDestination($newfolder)) {
			// Disallow moving to a subfolder of the current folder.
			return 4;
		}

		$filemask = substr($this->localpath, 0, -1) . '.*';
		$perms = FOLDER_MOD;
		@chmod($this->localpath, 0777);
		$success = @rename(rtrim($this->localpath, '/'), $dest);
		@chmod($dest, $perms);
		if ($success) {
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
				// must be first as updates below changes the object and paths
				$this->moveCacheFolder($newfolder); 
				// Update old parent(s) that "lost" an album!
				$this->setUpdatedDateParents(); 
				$this->save();
				$this->updateParent($newfolder);
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
		if (substr($newfolder, -1, 1) != '/') {
			$newfolder .= '/';
		}
		$newfolder .= basename($this->localpath);
		// First, ensure the new base directory exists.
		$dest = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($newfolder);
		// Check to see if the destination directory already exists
		if (file_exists($dest)) {
			// Disallow moving an album over an existing one.
			return 3;
		}
		if(!$this->isValidMoveCopyDestination($newfolder)) {
			// Disallow copying to a subfolder of the current folder (infinite loop).
			return 4;
		}
		$success = $this->succeed($dest);
		$filemask = substr($this->localpath, 0, -1) . '.*';
		if ($success) {
			// replicate the album metadata and sub-files
			$uniqueset = array('folder' => $newfolder);
			$parentname = dirname($newfolder);	
			if (empty($parentname) || $parentname == '/' || $parentname == '.') {
				$uniqueset['parentid'] = NULL;
			} else {
				$parent = AlbumBase::newAlbum($parentname);	
				$uniqueset['parentid'] = $parent->getID();
			}
			$newID = parent::copy($uniqueset);
			if ($newID) {
				//replicate the tags
				storeTags(readTags($this->getID(), 'albums'), $newID, 'albums');
				//copy the sidecar files
				$filestocopy = safe_glob($filemask);
				foreach ($filestocopy as $file) {
					if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
						$success = $success && @copy($file, dirname($dest) . '/' . basename($file));
					}
				}
			}
		}
		if ($success) {	
			$newalbum = AlbumBase::newAlbum($newfolder);
			$newalbum->setUpdatedDate();
			$newalbum->setUpdatedDateParents();
			$this->copyCacheFolder($newfolder);
			return 0;
		} else {
			return 1;
		}
	}
	
	/**
	 * Checks is the destination is not a subfolder of the current folder itself
	 * 
	 * @since 1.5.5
	 * 
	 * @param string $destination album name to move or copy to
	 * @return boolean
	 */
	function isValidMoveCopyDestination($destination) {
		$oldfolders = explode('/', $this->name);
		$newfolders = explode('/', $destination);
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
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Gets the SERVERPATH to the cache folder of the album with trailing slash
	 * Note that his does not check for existance!
	 * 
	 * @since 1.6.1
	 * 
	 * @return string
	 */
	function getCacheFolder() {
		return SERVERCACHE . '/' . pathurlencode($this->name) . '/';
	}

	/**
	 * Copies the cache folder of the album
	 * 
	 * @since 1.6.1
	 * 
	 * @param string $newfolder New folder path name
	 * @return bool
	 */
	function copyCacheFolder($newfolder) {
		if (file_exists($this->getCacheFolder())) {
			$foldercopy = SERVERCACHE . '/' . $newfolder . '/';
			if (!file_exists($foldercopy)) {
				return @copy($this->getCacheFolder(), $foldercopy);
			}
		}
		return false;
	}
	
	/**
	 * Moves the cache folder of the album
	 * 
	 * @since 1.6.1
	 * 
	 * @param string $newfolder New folder path name
	 * @return bool
	 */
	function moveCacheFolder($newfolder) {
		if (file_exists($this->getCacheFolder())) {
			$movedfolder = SERVERCACHE . '/' . $newfolder . '/';
			if (!file_exists($movedfolder)) {
				return @rename($this->getCacheFolder(), $movedfolder);
			}
		}
		return false;
	}

	/**
	 * Renames the cache folder of the album
	 * Alias of moveCacheFolder();
	 * 
	 * @since 1.6.1
	 * 
	 * @param string $newfolder New folder path name
	 * @return bool
	 */
	function renameCacheFolder($newfolder) {
		return $this->moveCacheFolder($newfolder);
	}
	
	/**
	 * Removes the cache folder of the album including all contents
	 * 
	 * @since 1.6.1
	 */
	function removeCacheFolder() {
		removeDir($this->getCacheFolder(), true);
	}
	
	/**
	 * Removes cache image files from this album's folder but not any subalbums or their cache files
	 * 
	 * @since 1.6.1
	 */
	function clearCacheFolder() {
		chdir($this->getCacheFolder());
		// Try tot clear the cache folder of subfolders and files
		$filelist = safe_glob('*');
		foreach ($filelist as $file) {
			if (is_file($file)) {
				@chmod($file, 0777);
				@unlink($file); 
			} 
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
	 * @param bit $action User rights level, default LIST_RIGHTS
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action = LIST_RIGHTS) {
		global $_zp_loggedin;
		if ($parent = parent::isMyItem($action)) {
			return $parent;
		}
		if (zp_loggedin($action)) {
			$subRights = $this->albumSubRights();
			if (is_null($subRights)) {
// no direct rights, but if this is a private gallery and the album is published he should be allowed to see it
				if (GALLERY_SECURITY != 'public' && $this->isPublic() && $action == LIST_RIGHTS) {
					return LIST_RIGHTS;
				}
			} else {
				$albumrights = LIST_RIGHTS;
				if ($subRights & (MANAGED_OBJECT_RIGHTS_EDIT)) {
					$albumrights = $albumrights | ALBUM_RIGHTS;
				}
				if ($subRights & MANAGED_OBJECT_RIGHTS_UPLOAD) {
					$albumrights = $albumrights | UPLOAD_RIGHTS;
				}
				if ($action & $albumrights) {
					return ($_zp_loggedin ^ (ALBUM_RIGHTS | UPLOAD_RIGHTS)) | $albumrights;
				} else {
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Checks if guest is loggedin for the album
	 * 

	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		global $_zp_pre_authorization, $_zp_gallery;
		if (isset($_zp_pre_authorization[$this->getName()])) {
			return $_zp_pre_authorization[$this->getName()];
		}
		$hash = $this->getPassword();
		if (empty($hash)) {
			$album = $this->getParent();
			while (!is_null($album)) {
				$hash = $album->getPassword();
				$authType = "zpcms_auth_album_" . $album->getID();
				$saved_auth = zp_getCookie($authType);

				if (!empty($hash)) {
					if ($saved_auth == $hash) {
						$_zp_pre_authorization[$album->getName()] = $authType;
						return $authType;
					} else {
						$hint = $album->getPasswordHint();
						return false;
					}
				}
				$album = $album->getParent();
			}
			// revert all tlhe way to the gallery
			$hash = $_zp_gallery->getPassword();
			$authType = 'zpcms_auth_gallery';
			$saved_auth = zp_getCookie($authType);
			if (empty($hash)) {
				$authType = 'zp_public_access';
			} else {
				if ($saved_auth != $hash) {
					$hint = $_zp_gallery->getPasswordHint();
					return false;
				}
			}
		} else {
			$authType = "zpcms_auth_album_" . $this->getID();
			$saved_auth = zp_getCookie($authType);
			if ($saved_auth != $hash) {
				$hint = $this->getPasswordHint();
				return false;
			}
		}
		$_zp_pre_authorization[$this->getName()] = $authType;
		return $authType;
	}

	/**
	 * Returns true if this album is published and also all of its parents.
	 * 
	 * @since 1.5.5
	 * 
	 * @return bool
	 */
	function isPublic() {
		if (is_null($this->is_public)) {
			if (!$this->isPublished()) {
				return $this->is_public = false;
			}
			$parent = $this->getParent();
			if($parent && !$parent->isPublic()) {
				return $this->is_public = false;
			}
			return $this->is_public = true;
		} else {
			return $this->is_public;
		}
	}

	/**
	 * Gets the owner of the album respectively of a parent album if not set specifically
	 * 
	 * @global obj $_zp_authority
	 * @param bool $fullname Set to true to get the full name (if the owner is a vaild user of the site and has the full name defined)
	 * @return string
	 */
	function getOwner($fullname = false) {
		global $_zp_authority;
		$owner = $this->get('owner');
		if (empty($owner)) {
			$p = $this->getParent();
			if (is_object($p)) {
				$owner = $p->getOwner();
			} else {
				$admin = $_zp_authority->getMasterUser();
				$owner = $admin->getUser();
				if ($fullname && !empty($admin->getName())) {
					return $admin->getName();
				}
			}
		} else {
			if ($fullname) {
				return Administrator::getNameByUser($owner);
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

	function setUpdatedDate($date = null) {
		if(is_null($date)) {
			$date = date('Y-m-d H:i:s');
		}
		return $this->set('updateddate', $date);
	}
	
	/**
	 * Sets the current date to all parent albums of this album recursively
	 * @since 1.5.5
	 */
	function setUpdatedDateParents() {
		$parent = $this->getParent();
		if($parent) {
			$parent->setUpdatedDate();
			$parent->save();
			$parent->setUpdatedDateParents();
		}
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
	function albumSubRights() {
		if (!is_null($this->subrights)) {
			return $this->subrights;
		}
		global $_zp_admin_album_list;
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$this->subrights = MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW;
			return $this->subrights;
		}
		if (zp_loggedin(VIEW_UNPUBLISHED_RIGHTS)) {
			$base = MANAGED_OBJECT_RIGHTS_VIEW;
		} else {
			$base = NULL;
		}
		getManagedAlbumList();
		if (count($_zp_admin_album_list) > 0) {
			$desired_folders = explode('/', $this->name);
			foreach ($_zp_admin_album_list as $adminalbum => $rights) {
// see if it is one of the managed folders or a subfolder there of
				$admin_folders = explode('/', $adminalbum);
				$level = 0;
				$ok = true;
				foreach ($admin_folders as $folder) {
					if ($level >= count($desired_folders) || $folder != $desired_folders[$level]) {
						$ok = false;
						break;
					}
					$level++;
				}
				if ($ok) {
					$this->subrights = $rights | $base;
					return $this->subrights;
				}
			}
		}
		$this->subrights = $base;
		return $this->subrights;
	}

	/**
	 * sortImageArray will sort an array of Images based on the given key. The
	 * key must be one of (filename, title, sort_order) at the moment.
	 *
	 * @param array $images The array of filenames to be sorted.
	 * @param  string $sorttype optional sort type
	 * @param  string $sortdirection optional sort direction
	 * @param bool $mine set to true/false to override ownership clause
	 * @return array
	 */
	protected function sortImageArray($images, $sorttype, $sortdirection, $mine = NULL) {
		global $_zp_db;
		if (is_null($mine)) {
			$mine = $this->isMyItem(LIST_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS);
		}
		if ($mine && !($mine & (MANAGE_ALL_ALBUM_RIGHTS))) {
			//check for managed album view unpublished image rights
			$mine = $this->albumSubRights() & (MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW);
		}
		$sortkey = $this->getImageSortKey($sorttype);
		if (($sortkey == '`sort_order`') || ($sortkey == 'RAND()')) {
			// manual sort is always ascending
			$order = false;
		} else {
			if (!is_null($sortdirection)) {
				$order = strtoupper($sortdirection) == 'DESC';
			} else {
				$order = $this->getSortDirection('image');
			}
		}
		$result = $_zp_db->query($sql = "SELECT * FROM " . $_zp_db->prefix("images") . " WHERE `albumid`= " . $this->getID() . ' ORDER BY ' . $sortkey . ' ' . $sortdirection);
		$results = array();
		while ($row = $_zp_db->fetchAssoc($result)) {
			$filename = $row['filename'];
			if (($key = array_search($filename, $images)) !== false) {
				// the image exists in the filesystem
				$results[] = $row;
				unset($images[$key]);
			} else { // the image no longer exists
				$id = $row['id'];
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('images') . " WHERE `id`=$id"); // delete the record
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type` ='images' AND `ownerid`= '$id'"); // remove image comments
			}
		}
		$_zp_db->freeResult($result);
		foreach ($images as $filename) {
			// these images are not in the database
			$imageobj = Image::newImage($this, $filename);
			$results[] = $imageobj->getData();
		}
		// now put the results into the right order
		$results = sortByKey($results, str_replace('`', '', $sortkey), $order);
		// the results are now in the correct order
		$images_ordered = array();
		foreach ($results as $key => $row) {
			// check for visible
			switch (themeObject::checkScheduledPublishing($row)) {
				case 1:
					$imageobj = Image::newImage($this, $row['filename']);
					$imageobj->setPublished(0);
					$imageobj->save();
				case 2:
					$row['show'] = 0;
					break;
			}
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
			$parent = AlbumBase::newAlbum($parentname);
			$this->set('parentid', $parent->getID());
		}
		$this->setUpdatedDateParents();
		$this->save();
	}

	/**
	 * Simply creates objects of all the images and sub-albums in this album to
	 * load accurate values into the database.
	 */
	function preLoad() {
		$images = $this->getImages(0);
		$subalbums = $this->getAlbums(0);
		foreach ($subalbums as $dir) {
			$album = AlbumBase::newAlbum($dir);
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
			return AlbumBase::newAlbum($albums[$inx]);
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
			return AlbumBase::newAlbum($albums[$inx]);
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
	
	/**
	 * 
	 * Gets the number of album pages
	 * 
	 * @since 1.6
	 * 
	 * @param string $type 'total" total pages rounded, "full" number of pages that exactly match the per page value, 
	 *		"plain" number of pages as float value
	 * @return int|float
	 */
	function getNumAlbumPages($type = 'total') {
		$album_pages = $this->getNumAlbums() / $this->getAlbumsPerPage();
		switch ($type) {
			case 'plain':
				return $album_pages;
			case 'full':
				return floor($album_pages);
			case 'total':
				return ceil($album_pages);
		}
	}

	/**
	 * Gets the number of image pages
	 * 
	 * @since 1.6
	 * 
	 * @param string $type 'total" total pages rounded, "full" number of pages that exactly match the per page value, 
	 * 							"plain" number of pages as float value
	 * @param type $type
	 * @return int|float
	 */
	function getNumImagePages($type = 'total') {
		$image_pages = $this->getNumImages() / $this->getImagesPerPage();
		switch ($type) {
			case 'plain':
				return $image_pages;
			case 'full':
				return floor($image_pages);
			case 'total':
				return ceil($image_pages);
		}
	}


	/**
	 * Gets the number of total pages of albums and images
	 * 
	 * @since 1.6
	 * 
	 * @param bool $one_image_page set to true if your theme collapses all image thumbs
	 * or their equivalent to one page. This is typical with flash viewer themes
	 * 
	 * @return int
	 */
	function getTotalPages($one_image_page = false) {
		$total_pages = $this->getNumAlbumPages('total') + $this->getNumImagePages('total');
		$first_page_images = $this->getFirstPageImages($one_image_page);
		if ($first_page_images == 0) {
			return $total_pages;
		} else {
			return ($total_pages - 1);
		}
	}
	
	/**
	 * Gets the albums per page value
	 * 
	 * @since 1.6
	 * 
	 * @return int
	 */
	function getAlbumsPerPage() {
		return max(1, getOption('albums_per_page'));
	}

	/**
	 * Gets the images per page value
	 * 
	 * @since 1.6
	 */
	function getImagesPerPage() {
		return max(1, getOption('images_per_page'));
	}
	
	/**
	 * Gets the number of images if the thumb transintion page for sharing thunbs on the last album and the first image page
	 * 
	 * @since 1.6
	 * 
	 * @param bool $one_image_page 
	 * @return int
	 */
	function getFirstPageImages($one_image_page = false) {
		if ($one_image_page) {
			if (!is_null($this->firstpageimages_oneimagepage)) {
				return $this->firstpageimages_oneimagepage;
			}
			return $this->firstpageimages_oneimagepage = Gallery::getFirstPageImages($this, $one_image_page);
		} else {
			if (!is_null($this->firstpageimages)) {
				return $this->firstpageimages;
			}
			return $this->firstpageimages = Gallery::getFirstPageImages($this, $one_image_page);
		}
	}
	
	/**
	 * Gets the level based on the folder name(s) as freshly discovered albums from the file system may not have a proper sortorder set
	 * 
	 * @since 1.6.1
	 * 
	 * @return int
	 */
	function getLevel() {
		return substr_count($this->getName(), '/') + 1;
	}

}