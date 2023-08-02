<?php
/**
 * Dynamic Album Class for "saved searches"
 * @package zpcore\classes\objects
 */
class dynamicAlbum extends AlbumBase {

	public $searchengine; // cache the search engine for dynamic albums

	function __construct($folder8, $cache = true, $quiet = false) {
		$folder8 = trim($folder8, '/');
		$folderFS = internalToFilesystem($folder8);
		$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS . "/";
		$this->linkname = $this->name = $folder8;
		$this->localpath = $localpath;
		if (!$this->_albumCheck($folder8, $folderFS, $quiet))
			return;
		$this->instantiate('albums', array('folder' => $this->name), 'folder', $cache, empty($folder8));
		$this->exists = true;
		if (!is_dir(stripSuffix($this->localpath))) {
			$this->linkname = stripSuffix($folder8);
		}

		$new = !$this->getSearchParams();
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
					$words = "s=" . urlencode(substr($data1, 6));
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
				$this->save();
				zp_apply_filter('new_album', $this);
			}
		} 
		zp_apply_filter('album_instantiate', $this);
	}

	/**
	 * album validity check
	 * @param type $folder8
	 * @return boolean
	 */
	protected function _albumCheck($folder8, $folderFS, $quiet) {
		$this->localpath = rtrim($this->localpath, '/');

		$msg = false;
		if (empty($folder8)) {
			$msg = gettext('Invalid album instantiation: No album name');
		} else if (filesystemToInternal($folderFS) != $folder8) {
			// an attempt to spoof the album name.
			$msg = sprintf(gettext('Invalid album instantiation: %1$s!=%2$s'), html_encode(filesystemToInternal($folderFS)), html_encode($folder8));
		} else if (!file_exists($this->localpath) || is_dir($this->localpath)) {
			$msg = sprintf(gettext('Invalid album instantiation: %s does not exist.'), html_encode($folder8));
		}
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				trigger_error($msg, E_USER_ERROR);
			}
			return false;
		}
		return true;
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
		global $_zp_gallery;
		if (!$this->exists)
			return array();
		if ($mine || is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			if (is_null($sorttype)) {
				$sorttype = $this->getSortType('album');
			}
			if (is_null($sortdirection)) {
				if ($this->getSortDirection('album')) {
					$sortdirection = 'DESC';
				} else {
					$sortdirection = '';
				}
			}
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
		$search = array(
				'words=', // pre 1.6
				'search=' // 1.6
		);
		$replace = 's='; // 1.6.1+
		$searchparams = str_replace($search, $replace, strval($this->get('search_params')));
		return $searchparams;
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
		$params = $this->getSearchParams();
		$params .= '&albumname=' . $this->name;
		$this->searchengine->setSearchParams($params);
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
	 * @param string $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		if (!$this->exists)
			return array();
		if ($mine || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
			if (is_null($sorttype)) {
				$sorttype = $this->getSortType();
			}
			if (is_null($sortdirection)) {
				if ($this->getSortDirection('image')) {
					$sortdirection = 'DESC';
				}
			}
			$searchengine = $this->getSearchEngine();
			$this->images = $searchengine->getImages(0, 0, $sorttype, $sortdirection, $care, $mine);
			$this->lastimagesort = $sorttype . $sortdirection;
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
	function move($newfolder,$oldfolder="") {
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
	
	/**
	 * Sets default values for a new album
	 *
	 * @return bool
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		// Set default data for a new Album (title and parent_id)
		parent::setDefaults();
		$parentalbum = $this->getParent();
		$this->set('mtime', filemtime($this->localpath));
		if (!$_zp_gallery->getAlbumUseImagedate()) {
			$date = zpFormattedDate('Y-m-d H:i:s', $this->get('mtime'));
			$this->setDateTime($date);
		}
		$title = trim($this->name);
		if (!is_null($parentalbum)) {
			$this->set('parentid', $parentalbum->getID());
			$title = substr($title, strrpos($title, '/') + 1);
		}
		$this->set('title', sanitize($title, 2));
		return true;
	}

}