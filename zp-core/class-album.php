<?php
/**
 * Album Class
 * @package classes
 */

// force UTF-8 Ø

define('IMAGE_SORT_DIRECTION',getOption('image_sortdirection'));
define('IMAGE_SORT_TYPE',getOption('image_sorttype'));

class Album extends MediaObject {

	var $name;             // Folder name of the album (full path from the albums folder)
	var $localpath;				 // Latin1 full server path to the album
	var $exists = true;    // Does the folder exist?
	var $images = null;    // Full images array storage.
	var $parent = null;    // The parent album name
	var $parentalbum = null; // The parent album's album object (lazy)
	var $gallery;
	var $searchengine;           // cache the search engine for dynamic albums
	var $sidecars = array();	// keeps the list of suffixes associated with this album
	var $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	var $manage_some_rights = ALBUM_RIGHTS;
	var $view_rights = VIEW_ALBUMS_RIGHTS;
	protected $subalbums = null; // Full album array storage.
	protected $index;
	protected $lastimagesort = NULL;  // remember the order for the last album/image sorts
	protected $lastsubalbumsort = NULL;
	protected $albumthumbnail = NULL; // remember the album thumb for the duration of the script
	protected $subrights = NULL;	//	cache for album subrights


	/**
	 * Constructor for albums
	 *
	 * @param object &$gallery The parent gallery
	 * @param string $folder8 folder name (UTF8) of the album
	 * @param bool $cache load from cache if present
	 * @return Album
	 */
	function Album(&$gallery, $folder8, $cache=true, $quiet=false) {
		if (!is_object($gallery) || strtolower(get_class($gallery)) != 'gallery') {
			$msg = sprintf(gettext('Bad gallery in instantiation of album %s.'),$folder8);
			debugLogBacktrace($msg);
			trigger_error(html_encode($msg), E_USER_NOTICE);
			$gallery = new Gallery();
		}
		$folder8 = sanitize_path($folder8);
		$folderFS = internalToFilesystem($folder8);
		$this->gallery = &$gallery;
		if (empty($folder8)) {
			$localpath = ALBUM_FOLDER_SERVERPATH;
		} else {
			$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS . "/";
		}
		if (filesystemToInternal($folderFS) != $folder8) { // an attempt to spoof the album name.
			$this->exists = false;
			$msg = sprintf(gettext('Zenphoto encountered an album name spoof attempt: %1$s=>%2$s.'),filesystemToInternal($folderFS),$folder8);
			debugLogBacktrace($msg);
			trigger_error(html_encode($msg), E_USER_NOTICE);
			return;
		}
		if ($dynamic = hasDynamicAlbumSuffix($folder8)) {
			$localpath = substr($localpath, 0, -1);
			$this->set('dynamic', 1);
		}
		// Must be a valid (local) folder:
		if(!file_exists($localpath) || !($dynamic || is_dir($localpath))) {
			$this->exists = false;
			if (!$quiet) {
				$msg = sprintf(gettext('class-album detected an invalid folder name: %s.'),$folder8);
				debugLogBacktrace($msg);
				trigger_error(html_encode($msg), E_USER_NOTICE);
			}
			return;
		}
		$this->localpath = $localpath;
		$this->name = $folder8;
		$new = parent::PersistentObject('albums', array('folder' => $this->name), 'folder', $cache, empty($folder8));

		if ($dynamic) {
			$new = !$this->get('search_params');
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
						$words = "words=".urlencode(substr($data1, 6));
					}
					if (strpos($data1, 'THUMB=') !== false) {
						$thumb = trim(substr($data1, 6));
						$this->set('thumb', $thumb);
					}
					if (strpos($data1, 'FIELDS=') !== false) {
						$fields = "&searchfields=".trim(substr($data1, 7));
					}
					if (strpos($data1, 'CONSTRAINTS=') !== false) {
						$constraints = '&'.trim(substr($data1, 12));
					}

				}
				if (!empty($words)) {
					if (empty($fields)) {
						$fields = '&searchfields=tags';
					}
					$this->set('search_params', $words.$fields.$constraints);
				}

				$this->set('mtime', filemtime($this->localpath));
				if ($new) {
					$title = $this->get('title');
					$this->set('title', substr($title, 0, -4)); // Strip the .'.alb' suffix
					$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
				}
				$this->set('dynamic', 1);
			}
		}
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
		// Set default data for a new Album (title and parent_id)
		$parentalbum = $this->getParent();
		$this->setShow(getOption('album_publish'));
		$this->set('mtime', filemtime($this->localpath));
		$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
		$title = trim($this->name);
		if (!is_null($parentalbum)) {
			$this->set('parentid', $parentalbum->getAlbumId());
			$title = substr($title, strrpos($title, '/')+1);
		}
		$this->set('title', sanitize($title, 2));
		return true;
	}

	/**
	 * Returns the folder on the filesystem
	 *
	 * @return string
	 */
	function getFolder() { return $this->name; }

	/**
	 * Returns the id of this album in the db
	 *
	 * @return int
	 */
	function getAlbumID() { return $this->id; }

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
				$parentalbum = new Album($this->gallery, $parent);
				if ($parentalbum->exists) {
					return $parentalbum;
				}
			}
		} else if ($this->parentalbum->exists) {
			return $this->parentalbum;
		}
		return NULL;
	}


	/**
	 * Returns the place data of an album
	 *
	 * @return string
	 */
	function getLocation() {
		return get_language_string($this->get('location'));
	}

	/**
	 * Stores the album place
	 *
	 * @param string $place text for the place field
	 */
	function setLocation($place) { $this->set('location', $place); }


	/**
	 * Returns either the subalbum sort direction or the image sort direction of the album
	 *
	 * @param string $what 'image_sortdirection' if you want the image direction,
	 *        'album_sortdirection' if you want it for the album
	 *
	 * @return string
	 */
	function getSortDirection($what) {
		if ($what == 'image') {
			$direction = $this->get('image_sortdirection');
			$type = $this->get('sort_type');
		} else {
			$direction = $this->get('album_sortdirection');
			$type = $this->get('subalbum_sort_type');
		}
		if (empty($type)) { // using inherited type, so use inherited direction
			$parentalbum = $this->getParent();
			if (is_null($parentalbum)) {
				if ($what == 'image') {
					$direction = IMAGE_SORT_DIRECTION;
				} else {
					$direction = $this->gallery->getSortDirection();
				}
			} else {
				$direction = $parentalbum->getSortDirection($what);
			}
		}
		return $direction;
	}

	/**
	 * sets sort directions for the album
	 *
	 * @param string $what 'image_sortdirection' if you want the image direction,
	 *        'album_sortdirection' if you want it for the album
	 * @param string $val the direction
	 */
	function setSortDirection($what, $val) {
		if ($what == 'image') {
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
	function getSortType() {
		$type = $this->get('sort_type');
		if (empty($type)) {
			$parentalbum = $this->getParent();
			if (is_null($parentalbum)) {
				$type = IMAGE_SORT_TYPE;
			} else {
				$type = $parentalbum->getSortType();
			}
		}
		return $type;
	}

	/**
	 * Stores the sort type for the album
	 *
	 * @param string $sorttype the album sort type
	 */
	function setSortType($sorttype) {
		$this->set('sort_type', $sorttype);
	}

	/**
	 * Returns the sort type for subalbums in this album.
	 *
	 * Will return a parent sort type if the sort type for this album is empty.
	 *
	 * @return string
	 */
	function getAlbumSortType() {
		$type = $this->get('subalbum_sort_type');
		if (empty($type)) {
			$parentalbum = $this->getParent();
			if (is_null($parentalbum)) {
				$type = $this->gallery->getSortType();
			} else {
				$type = $parentalbum->getAlbumSortType();
			}
		}
		return $type;
	}

	/**
	 * Stores the subalbum sort type for this abum
	 *
	 * @param string $sorttype the subalbum sort type
	 */
	function setSubalbumSortType($sorttype) { $this->set('subalbum_sort_type', $sorttype); }

	/**
	 * Returns the DB key associated with the image sort type
	 *
	 * @param string $sorttype the sort type
	 * @return string
	 */
	function getImageSortKey($sorttype=null) {
		if (is_null($sorttype)) { $sorttype = $this->getSortType(); }
		return lookupSortKey($sorttype, 'filename', 'filename');
	}

	/**
	 * Returns the DB key associated with the subalbum sort type
	 *
	 * @param string $sorttype subalbum sort type
	 * @return string
	 */
	function getAlbumSortKey($sorttype=null) {
		if (empty($sorttype)) { $sorttype = $this->getAlbumSortType(); }
		return lookupSortKey($sorttype, 'sort_order', 'folder');
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

	function getAlbums($page=0, $sorttype=null, $sortdirection=null, $care=true, $mine=NULL) {
		if (is_null($this->subalbums) || $care && $sorttype.$sortdirection !== $this->lastsubalbumsort ) {
			if ($this->isDynamic()) {
				$search = $this->getSearchEngine();
				$subalbums = $search->getAlbums($page,NULL,NULL,false);
			} else {
				$dirs = $this->loadFileNames(true);
				$subalbums = array();
				foreach ($dirs as $dir) {
					$dir = $this->name . '/' . $dir;
					$subalbums[] = $dir;
				}
			}
			$key = $this->getAlbumSortKey($sorttype);
			$this->subalbums = $this->gallery->sortAlbumArray($this, $subalbums, $key, $sortdirection, $mine);
			$this->lastsubalbumsort = $sorttype.$sortdirection;
		}

		if ($page == 0) {
			return $this->subalbums;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			return array_slice($this->subalbums, $albums_per_page*($page-1), $albums_per_page);
		}
	}

	/**
	 * Returns the count of subalbums
	 *
	 * @return int
	 */
	function getNumAlbums() {
		return count($this->getAlbums(0,NULL,NULL,false));
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
	function getImages($page=0, $firstPageCount=0, $sorttype=null, $sortdirection=null, $care=true, $mine=NULL) {
		if (is_null($this->images) || $care && $sorttype.$sortdirection !== $this->lastimagesort) {
			if ($this->isDynamic()) {
				$searchengine = $this->getSearchEngine();
				$images = $searchengine->getSearchImages($sorttype, $sortdirection);
			} else {
				// Load, sort, and store the images in this Album.
				$images = $this->loadFileNames();
				$images = $this->sortImageArray($images, $sorttype, $sortdirection, $mine);
			}
			$this->images = $images;
			$this->lastimagesort = $sorttype.$sortdirection;
		}
		// Return the cut of images based on $page. Page 0 means show all.
		if ($page == 0) {
			return $this->images;
		} else {
			// Only return $firstPageCount images if we are on the first page and $firstPageCount > 0
			if (($page==1) && ($firstPageCount>0)) {
				$pageStart = 0;
				$images_per_page = $firstPageCount;

			} else {
				if ($firstPageCount>0) {
					$fetchPage = $page - 2;
				} else {
					$fetchPage = $page - 1;
				}
				$images_per_page = max(1, getOption('images_per_page'));
				$pageStart = $firstPageCount + $images_per_page * $fetchPage;

			}
			$slice = array_slice($this->images, $pageStart , $images_per_page);

			return $slice;
		}
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
	function sortImageArray($images, $sorttype, $sortdirection, $mine= NULL) {
		if (is_null($mine)) {
			$mine = $this->isMyItem(LIST_RIGHTS);
		}
		if ($mine && !($mine & (VIEW_ALBUMS_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))) {	//	check for managed album view unpublished image rights
			$mine = $this->albumSubRights() & MANAGED_OBJECT_RIGHTS_VIEW_IMAGE;
		}
		$sortkey = str_replace('`','',$this->getImageSortKey($sorttype));
		if (($sortkey == '`sort_order`') || ($sortkey == 'RAND()')) { // manual sort is always ascending
			$order = false;
		} else {
			if (!is_null($sortdirection)) {
				$order = strtoupper($sortdirection) == 'DESC';
			} else {
				$order = $this->getSortDirection('image');
			}
		}

		$result = query($sql = "SELECT * FROM " . prefix("images") . " WHERE `albumid`= " . $this->id);
		$results = array();
		while ($row = db_fetch_assoc($result)) {
			$results[] = $row;
		}
		foreach ($results as $rowkey=>$row) {
			$filename = $row['filename'];
			if (($key = array_search($filename,$images)) !== false) {	// the image exists in the filesystem
				unset($images[$key]);
			} else {																									// the image no longer exists
				$id = $row['id'];
				query("DELETE FROM ".prefix('images')." WHERE `id`=$id"); // delete the record
				query("DELETE FROM ".prefix('comments')." WHERE `type` ='images' AND `ownerid`= '$id'"); // remove image comments
				unset($results[$rowkey]);
			}
		}
		foreach ($images as $filename) {	// these images are not in the database
			$imageobj = newImage($this,$filename);
			$results[] = $imageobj->data;
		}
		// now put the results into the right order
		$results = sortByKey($results,$sortkey,$order);
		// the results are now in the correct order
		$images_ordered = array();
		foreach ($results as $key=>$row) { // check for visible
			if ($row['show'] || $mine) {	// don't display it
				$images_ordered[] = $row['filename'];
			}
		}
		return $images_ordered;
	}


	/**
	 * Returns the number of images in this album (not counting its subalbums)
	 *
	 * @return int
	 */
	function getNumImages() {
		if (is_null($this->images)) {
			return count($this->getImages(0,0,NULL,NULL,false));
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
			if ($this->isDynamic()) {
				$album = new Album($this->gallery, $images[$index]['folder']);
				return newImage($album, $images[$index]['filename']);
			} else {
				return newImage($this, $this->images[$index]);
			}
			return false;
		}
	}

	/**
	 * Gets the album's set thumbnail image from the database if one exists,
	 * otherwise, finds the first image in the album or sub-album and returns it
	 * as an Image object.
	 *
	 * @return Image
	 */
	function getAlbumThumbImage() {

		if (!is_null($this->albumthumbnail)) return $this->albumthumbnail;

		$albumdir = $this->localpath;
		$thumb = $this->get('thumb');
		$i = strpos($thumb, '/');
		if ($root = ($i === 0)) {
			$thumb = substr($thumb, 1); // strip off the slash
			$albumdir = ALBUM_FOLDER_SERVERPATH;
		}
		$shuffle = empty($thumb);
		$field = getOption('AlbumThumbSelectField');
		$direction = getOption('AlbumThumbSelectDirection');
		if (!empty($thumb) && !is_numeric($thumb) && file_exists($albumdir.internalToFilesystem($thumb))) {
			if ($i===false) {
				return newImage($this, $thumb);
			} else {
				$pieces = explode('/', $thumb);
				$i = count($pieces);
				$thumb = $pieces[$i-1];
				unset($pieces[$i-1]);
				$albumdir = implode('/', $pieces);
				if (!$root) { $albumdir = $this->name . "/" . $albumdir; } else { $albumdir = $albumdir . "/";}
				$this->albumthumbnail = newImage(new Album($this->gallery, $albumdir), $thumb);
				return $this->albumthumbnail;
			}
		} else {
			$this->getImages(0, 0, $field, $direction);
			$thumbs = $this->images;
			if (!is_null($thumbs)) {
				if ($shuffle) {
					shuffle($thumbs);
				}
				$mine = $this->isMyItem(LIST_RIGHTS);
				$other = NULL;
				while (count($thumbs) > 0) {	// first check for images
					$thumb = array_shift($thumbs);
					$thumb = newImage($this, $thumb);
					if ($mine || $thumb->getShow()) {
						if (isImagePhoto($thumb)) {	// legitimate image
							$this->albumthumbnail = $thumb;
							return $this->albumthumbnail;
						} else {
							if (!is_null($thumb->objectsThumb)) {	//	"other" image with a thumb sidecar
								$this->albumthumbnail = $thumb;
								return $this->albumthumbnail;
							} else {
								if (is_null($other)) $other = $thumb;
							}
						}
					}
				}
				if (!is_null($other)) {	//	"other" image, default thumb
					$this->albumthumbnail = $other;
					return $this->albumthumbnail;
				}
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
				$subalbum = new Album($this->gallery, $folder);
				$pwd = $subalbum->getPassword();
				if (($subalbum->getShow() && empty($pwd)) || $subalbum->isMyItem(LIST_RIGHTS)) {
					$thumb = $subalbum->getAlbumThumbImage();
					if (strtolower(get_class($thumb)) !== 'transientimage' && $thumb->exists) {
						$this->albumthumbnail =  $thumb;
						return $thumb;
					}
				}
			}
		}

		$nullimage = SERVERPATH.'/'.ZENFOLDER.'/images/imageDefault.png';
		if (OFFSET_PATH == 0) { // check for theme imageDefault.png if we are in the gallery
			$theme = '';
			$uralbum = getUralbum($this);
			$albumtheme = $uralbum->getAlbumTheme();
			if (!empty($albumtheme)) {
				$theme = $albumtheme;
			} else {
				$theme = $this->gallery->getCurrentTheme();
			}
			if (!empty($theme)) {
				$themeimage = SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/images/imageDefault.png';
				if (file_exists(internalToFilesystem($themeimage))) {
					$nullimage = $themeimage;
				}
			}
		}
		$this->albumthumbnail = new transientimage($this, $nullimage);
		return $this->albumthumbnail;
	}

	/**
	 * Gets the thumbnail URL for the album thumbnail image as returned by $this->getAlbumThumbImage();
	 * @return string
	 */
	function getAlbumThumb() {
		$image = $this->getAlbumThumbImage();
		return $image->getThumb('album');
	}

	/**
	 * Stores the thumbnail path for an album thumg
	 *
	 * @param string $filename thumbnail path
	 */
	function setAlbumThumb($filename) { $this->set('thumb', $filename); }

	/**
	 * Returns an URL to the album, including the current page number
	 *
	 * @return string
	 */
	function getAlbumLink() {
		global $_zp_page;
		$rewrite = pathurlencode($this->name) . '/';
		$plain = '/index.php?album=' . pathurlencode($this->name). '/';
		if ($_zp_page > 1) {
			$rewrite .= "page/$_zp_page";
			$plain .= "&page=$_zp_page";
		}
		return rewrite_path($rewrite, $plain);
	}

	/**
	 * Returns the album following the current album
	 *
	 * @return object
	 */
	function getNextAlbum() {
		if (is_null($parent = $this->getParent())) {
			$albums = $this->gallery->getAlbums(0);
		} else {
			$albums = $parent->getAlbums(0);
		}
		$inx = array_search($this->name, $albums)+1;
		if ($inx >= 0 && $inx < count($albums)) {
			return new Album($this->gallery, $albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the album prior to the current album
	 *
	 * @return object
	 */
	function getPrevAlbum() {
		if (is_null($parent = $this->getParent())) {
			$albums = $this->gallery->getAlbums(0);
		} else {
			$albums = $parent->getAlbums(0);
		}
		$inx = array_search($this->name, $albums)-1;
		if ($inx >= 0 && $inx < count($albums)) {
			return new Album($this->gallery, $albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the page number in the gallery of this album
	 *
	 * @return int
	 */
	function getGalleryPage() {
		if ($this->index == null)
			$this->index = array_search($this->name, $this->gallery->getAlbums(0));
		return floor(($this->index / galleryAlbumsPerPage())+1);
	}

	/**
	 * changes the parent of an album for move/copy
	 *
	 * @param string $newfolder The folder name of the new parent
	 */
	protected function updateParent($newfolder) {
		$this->name = $newfolder;
		$parentname = dirname($newfolder);
		if ($parentname == '/' || $parentname == '.') $parentname = '';
		if (empty($parentname)) {
			$this->set('parentid', NULL);
		} else {
			$parent = new Album($this->gallery, $parentname);
			$this->set('parentid', $parent->getAlbumid());
		}
		$this->save();
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		if (parent::remove()) {
			if (!$this->isDynamic()) {
				foreach ($this->getAlbums() as $folder) {
					$subalbum = new Album($this->gallery, $folder);
					$subalbum->remove();
				}
				foreach($this->getImages() as $filename) {
					$image = newImage($this, $filename);
					$image->remove();
				}
				$curdir = getcwd();
				chdir($this->localpath);
				$filelist = safe_glob('*');
				foreach($filelist as $file) {
					if (($file != '.') && ($file != '..')) {
						unlink($this->localpath . $file); // clean out any other files in the folder
					}
				}
				chdir($curdir);
			}
			query("DELETE FROM " . prefix('options') . "WHERE `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('comments') . "WHERE `type`='albums' AND `ownerid`=" . $this->id);
			query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $this->id);
			$success = true;
			if ($this->isDynamic()) {
				$filestoremove = safe_glob(substr($this->localpath,0,strrpos($this->localpath,'.')).'.*');
			} else {
				$filestoremove = safe_glob(substr($this->localpath,0,-1).'.*');
			}
			foreach ($filestoremove as $file) {
				if(in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$success = $success && unlink($file);
				}
			}
			if ($this->isDynamic()) {
				return @unlink($this->localpath) && $success;
			} else {
				return @rmdir($this->localpath) && $success;
			}
		}
		return false;
	}

	/**
	 * Move this album to the location specified by $newfolder, copying all
	 * metadata, subalbums, and subalbums' metadata with it.
	 * @param $newfolder string the folder to move to, including the name of the current folder (possibly renamed).
	 * @return int 0 on success and error indicator on failure.
	 *
	 */
	function moveAlbum($newfolder) {
		// First, ensure the new base directory exists.
		$oldfolder = $this->name;
		$dest = ALBUM_FOLDER_SERVERPATH.internalToFilesystem($newfolder);
		// Check to see if the destination already exists
		if (file_exists($dest)) {
			// Disallow moving an album over an existing one.
			return 3;
		}
		$oldfolders = explode('/', $oldfolder);
		$newfolders = explode('/', $newfolder);
		$sub = count($newfolders) > count($oldfolders);
		if ($sub) {
			for ($i=0;$i<count($oldfolders);$i++) {
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
		if ($this->isDynamic()) {
			$filemask = substr($this->localpath,0,strrpos($this->localpath,'.')).'.*';
		} else {
			$filemask = substr($this->localpath,0,-1).'.*';
		}
		$success = @rename($this->localpath, $dest);
		if ($success) {
			$filestomove = safe_glob($filemask);
			foreach ($filestomove as $file) {
				if(in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$success = $success && @rename($file, dirname($dest).'/'.basename($file));
				}
			}
			$sql = "UPDATE " . prefix('albums') . " SET folder=" . db_quote($newfolder) . " WHERE `id` = ".$this->getAlbumID();
			$success = query($sql);
			if ($success) {
				zp_apply_filter('album_rename_move', $this->name, $newfolder);
				$this->updateParent($newfolder);
				if (!$this->isDynamic()) {
					//rename the cache folder
					$cacherename = @rename(SERVERCACHE . '/' . $oldfolder, SERVERCACHE . '/' . $newfolder);
					// Then: go through the db and change the album (and subalbum) paths. No ID changes are necessary for a move.
					$sql = "UPDATE " . prefix('albums') . " SET folder=" . db_quote($newfolder) . " WHERE `id` = ".$this->getAlbumID();
					$success = $success && query($sql);
					if ($success) {
						// Get the subalbums.
						$sql = "SELECT id, folder FROM " . prefix('albums') . " WHERE folder LIKE ".db_quote($oldfolder.'%');
						$result = query_full_array($sql);
						foreach ($result as $subrow) {
							$newsubfolder = $subrow['folder'];
							$newsubfolder = $newfolder . substr($newsubfolder, strlen($oldfolder));
							$sql = "UPDATE ".prefix('albums'). " SET folder=".db_quote($newsubfolder)." WHERE id=".$subrow['id'];
							if (query($sql)) {
								zp_apply_filter('album_rename_move', $subrow['folder'], $newsubfolder);
							} else {
								$success = false;
							}
						}
					}
				}
				if ($success) {
					return 0;
				}
			}
		}
		return 1;
	}

	/**
	 * Rename this album folder. Alias for moveAlbum($newfoldername);
	 * @param string $newfolder the new folder name of this album (including subalbum paths)
	 * @return boolean true on success or false on failure.
	 */
	function rename($newfolder) {
		return $this->moveAlbum($newfolder);
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
		if (substr($newfolder,-1,1) != '/') $newfolder .= '/';
		$newfolder .= basename($this->localpath);
		// First, ensure the new base directory exists.
		$oldfolder = $this->name;
		$dest = ALBUM_FOLDER_SERVERPATH.internalToFilesystem($newfolder);
		// Check to see if the destination directory already exists
		if (file_exists($dest)) {
			// Disallow moving an album over an existing one.
			return 3;
		}
		if (substr($newfolder, count($oldfolder)) == $oldfolder) {
			// Disallow copying to a subfolder of the current folder (infinite loop).
			return 4;
		}
		if ($this->isDynamic()) {
			$success = @copy($this->localpath, $dest);
			$filemask = substr($this->localpath,0,strrpos($this->localpath,'.')).'.*';
		} else {
			$success = mkdir_recursive($dest) === TRUE;
			$filemask = substr($this->localpath,0,-1).'.*';
		}
		if ($success) {
			//	replicate the album metadata and sub-files
			$uniqueset = array('folder'=>$newfolder);
			$parentname = dirname($newfolder);
			if (empty($parentname) || $parentname == '/' || $parentname == '.') {
				$uniqueset['parentid'] = NULL;
			} else {
				$parent = new Album($this->gallery, $parentname);
				$uniqueset['parentid'] =  $parent->getID();
			}
			$newID = parent::copy($uniqueset);
			if ($newID) {
				//	replicate the tags
				storeTags(readTags($this->getID(), 'albums'), $newID, 'albums');
				//	copy the sidecar files

				$filestocopy = safe_glob($filemask);

				foreach ($filestocopy as $file) {
					if(in_array(strtolower(getSuffix($file)), $this->sidecars)) {
						$success = $success && @copy($file, dirname($dest).'/'.basename($file));
					}
				}
			}
			if ($success) {
				if (!$this->isDynamic()) {
					//	copy the images
					$images = $this->getImages(0);
					foreach ($images as $imagename) {
						$image = newImage($this, $imagename);
						$success = $success && !$image->copy($newfolder);
					}
					// copy the subalbums.
					$subalbums = $this->getAlbums(0);
					foreach ($subalbums as $subalbumname) {
						$subalbum = new Album($this->gallery, $subalbumname);
						if ($subalbum->copy($newfolder)) {
							$success = false;
						}
					}
				}
				if ($success) {
					return 0;
				}
			}
		}
		return 1;
	}

	/**
	 * For every image in the album, look for its file. Delete from the database
	 * if the file does not exist. Same for each sub-directory/album.
	 *
	 * @param bool $deep set to true for a thorough cleansing
	 */
	function garbageCollect($deep=false) {
		if (is_null($this->images)) $this->getImages();
		$result = query("SELECT * FROM ".prefix('images')." WHERE `albumid` = '" . $this->id . "'");
		$dead = array();
		$live = array();

		$files = $this->loadFileNames();

		// Does the filename from the db row match any in the files on disk?
		while($row = db_fetch_assoc($result)) {
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

		if (count($dead) > 0) {
			$sql = "DELETE FROM ".prefix('images')." WHERE `id` = '" . array_pop($dead) . "'";
			$sql2 = "DELETE FROM ".prefix('comments')." WHERE `type`='albums' AND `ownerid` = '" . array_pop($dead) . "'";
			foreach ($dead as $id) {
				$sql .= " OR `id` = '$id'";
				$sql2 .= " OR `ownerid` = '$id'";
			}
			query($sql);
			query($sql2);
		}

		// Get all sub-albums and make sure they exist.
		$result = query("SELECT * FROM ".prefix('albums')." WHERE `folder` LIKE " . db_quote($this->name.'%'));
		$dead = array();
		$live = array();
		// Does the dirname from the db row exist on disk?
		while($row = db_fetch_assoc($result)) {
			if (!is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($row['folder'])) || in_array($row['folder'], $live)
			|| substr($row['folder'], -1) == '/' || substr($row['folder'], 0, 1) == '/') {
				$dead[] = $row['id'];
			} else {
				$live[] = $row['folder'];
			}
		}
		if (count($dead) > 0) {
			$sql = "DELETE FROM ".prefix('albums')." WHERE `id` = '" . array_pop($dead) . "'";
			$sql2 = "DELETE FROM ".prefix('comments')." WHERE `type`='albums' AND `ownerid` = '" . array_pop($dead) . "'";
			foreach ($dead as $albumid) {
				$sql .= " OR `id` = '$albumid'";
				$sql2 .= " OR `ownerid` = '$albumid'";
			}
			query($sql);
			query($sql2);
		}

		if ($deep) {
			foreach($this->getAlbums(0) as $dir) {
				$subalbum = new Album($this->gallery, $dir);
				// Could have been deleted if it didn't exist above...
				if ($subalbum->exists)
				$subalbum->garbageCollect($deep);
			}
		}
	}


	/**
	 * Simply creates objects of all the images and sub-albums in this album to
	 * load accurate values into the database.
	 */
	function preLoad() {
		if (!$this->isDynamic()) return; // nothing to do
		$images = $this->getImages(0);
		$subalbums = $this->getAlbums(0);
		foreach($subalbums as $dir) {
			$album = new Album($this->gallery, $dir);
			$album->preLoad();
		}
	}


	/**
	 * Load all of the filenames that are found in this Albums directory on disk.
	 * Returns an array with all the names.
	 *
	 * @param  $dirs Whether or not to return directories ONLY with the file array.
	 * @return array
	 */
	protected function loadFileNames($dirs=false) {
		if ($this->isDynamic()) {  // there are no 'real' files
			return array();
		}
		$albumdir = $this->localpath;
		if (!is_dir($albumdir) || !is_readable($albumdir)) {
			if (!is_dir($albumdir)) {
				$msg = sprintf(gettext("Error: The album named %s cannot be found."), $this->name);
			} else {
				$msg = sprintf(gettext("Error: The album %s is not readable."), $this->name);
			}
			zp_error($msg,false);
			return array();
		}
		$dir = opendir($albumdir);
		$files = array();
		$others = array();

		while (false !== ($file = readdir($dir))) {
			$file8 = filesystemToInternal($file);
			if ($dirs && (is_dir($albumdir.$file) && (substr($file, 0, 1) != '.') || hasDynamicAlbumSuffix($file))) {
				$files[] = $file8;
			} else if (!$dirs && is_file($albumdir.$file)) {
				if (is_valid_other_type($file)) {
					$files[] = $file8;
					$others[] = $file8;
				} else if (is_valid_image($file)) {
					$files[] = $file8;
				}
			}
		}
		closedir($dir);
		if (count($others) > 0) {
			$others_thumbs = array();
			foreach($others as $other) {
				$others_root = substr($other, 0, strrpos($other,"."));
				foreach($files as $image) {
					$image_root = substr($image, 0, strrpos($image,"."));
					if ($image_root == $others_root && $image != $other && is_valid_image($image)) {
						$others_thumbs[] = $image;
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

	/**
	 * Returns true if the album is "dynamic"
	 *
	 * @return bool
	 */
	function isDynamic() {
		return $this->get('dynamic');
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
		if (!$this->isDynamic()) return null;
		if (!is_null($this->searchengine)) return $this->searchengine;
		$this->searchengine = new SearchEngine(true);
		$params = $this->get('search_params');
		$params .= '&albumname='.$this->name;
		$this->searchengine->setSearchParams($params);
		return $this->searchengine;
	}

	/**
	 * Returns the theme for the album
	 *
	 * @return string
	 */
	function getAlbumTheme() {
		return $this->get('album_theme');
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
		$this->set('watermark',$wm);
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
			$this->subrights = MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW_IMAGE;
			return $this->subrights;
		}
		getManagedAlbumList();
		if (count($_zp_admin_album_list) == 0) {
			$this->subrights =  false;
			return $this->subrights;
		}
		$desired_folders = explode('/', $this->name);
		foreach ($_zp_admin_album_list as $adminalbum=>$rights) { // see if it is one of the managed folders or a subfolder there of
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
				$this->subrights =  $rights;
				return $this->subrights;
			}
		}
		$this->subrights =  NULL;
		return $this->subrights;
	}

	/**
	 * checks access to the album
	 * @param bit $action What the requestor wants to do
	 *
	 * returns true of access is allowed
	*/
	function isMyItem($action) {
		global $_zp_loggedin;
		if ($parent = parent::isMyItem($action)) {
			return $parent;
		}
		if (zp_loggedin($action)) {
			$subRights = $this->albumSubRights();
			if (!is_null($subRights)) {
				$albumrights = 0;
				if ($subRights & (MANAGED_OBJECT_RIGHTS_EDIT)) {
					$albumrights = $albumrights | ALBUM_RIGHTS;
				}
				if ($subRights & MANAGED_OBJECT_RIGHTS_UPLOAD) {
					$albumrights = $albumrights | UPLOAD_RIGHTS;
				}
				if ($subRights & MANAGED_OBJECT_RIGHTS_VIEW_IMAGE) {
					$albumrights = $albumrights | LIST_RIGHTS;
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
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint=NULL, &$show=NULL) {
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
				$owner = $_zp_authority->master_user;
			}
		}
		return $owner;
	}
	function setOwner($owner) {
		$this->set('owner',$owner);
	}

	function getUpdatedDate() {
		return $this->get('updareddate');
	}

	function setUpdatedDate($date) {
		return $this->set('updareddate',$date);
	}

}
?>