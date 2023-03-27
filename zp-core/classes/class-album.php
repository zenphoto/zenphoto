<?php
/**
 * Album Class
 * @package zpcore\classes\objects
 */
class Album extends AlbumBase {

	/**
	 * Constructor for albums
	 *
	 * @param object $gallery The parent gallery: deprecated
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
		if (!$this->_albumCheck($folder8, $folderFS, $quiet))
			return;

		$new = $this->instantiate('albums', array('folder' => $this->name), 'folder', $cache, empty($folder8));

		if ($new) {
			$this->setUpdatedDateParents();
			$this->save();
			zp_apply_filter('new_album', $this);
		}
		zp_apply_filter('album_instantiate', $this);
	}

	/**
	 * album validity check
	 * @return boolean
	 */
	protected function _albumCheck($folder8, $folderFS, $quiet) {
		$msg = false;
		if (empty($folder8)) {
			$msg = gettext('Invalid album instantiation: No album name');
		} else if (filesystemToInternal($folderFS) != $folder8) {
			// an attempt to spoof the album name.
			$msg = sprintf(gettext('Invalid album instantiation: %1$s!=%2$s'), html_encode(filesystemToInternal($folderFS)), html_encode($folder8));
		} else if (!file_exists($this->localpath) || !(is_dir($this->localpath)) || $folder8[0] == '.' || preg_match('~/\.*/~', $folder8)) {
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
				} 
			}
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
			$images = $this->loadFileNames();
			$this->images = $this->sortImageArray($images, $sorttype, $sortdirection, $mine);
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
		global $_zp_db;
		$rslt = false;
		if (PersistentObject::remove()) {
			foreach ($this->getImages() as $filename) {
				$image = Image::newImage($this, $filename);
				$image->remove();
			}
			foreach ($this->getAlbums() as $folder) {
				$subalbum = AlbumBase::newAlbum($folder);
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
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('options') . "WHERE `ownerid`=" . $this->id);
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . "WHERE `type`='albums' AND `ownerid`=" . $this->id);
			$_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $this->id);
			$success = true;
			$filestoremove = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
			foreach ($filestoremove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					@chmod($file, 0777);
					$success = $success && unlink($file);
				}
			}
			@chmod($this->localpath, 0777);
			$rslt = @rmdir($this->localpath) && $success;
			$this->removeCacheFolder();
			$this->setUpdatedDateParents();
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
		global $_zp_db;
		$oldfolder = $this->name;
		$rslt = $this->_move($newfolder);
		if (!$rslt) {
			// Then: go through the db and change the album (and subalbum) paths. No ID changes are necessary for a move.
			// Get the subalbums.
			$sql = "SELECT id, folder FROM " . $_zp_db->prefix('albums') . " WHERE folder LIKE " . $_zp_db->quote($_zp_db->likeEscape($oldfolder) . '/%');
			$result = $_zp_db->query($sql);
			if ($result) {
				while ($subrow = $_zp_db->fetchAssoc($result)) {
					$newsubfolder = $subrow['folder'];
					$newsubfolder = $newfolder . substr($newsubfolder, strlen($oldfolder));
					$sql = "UPDATE " . $_zp_db->prefix('albums') . " SET folder=" . $_zp_db->quote($newsubfolder) . " WHERE id=" . $subrow['id'];
					$_zp_db->query($sql);
				}
			}
			$_zp_db->freeResult($result);
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
			//copy the images
			$images = $this->getImages(0);
			foreach ($images as $imagename) {
				$image = Image::newImage($this, $imagename);
				if ($rslt = $image->copy($newfolder)) {
					$success = false;
				}
			}
			// copy the subalbums.
			$subalbums = $this->getAlbums(0);
			foreach ($subalbums as $subalbumname) {
				$subalbum = AlbumBase::newAlbum($subalbumname);
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
		global $_zp_db;
		$set_updateddate = false;
		if (is_null($this->images))
			$this->getImages();
		$result = $_zp_db->query("SELECT `id`, `filename` FROM " . $_zp_db->prefix('images') . " WHERE `albumid` = '" . $this->id . "'");
		$dead = array();
		$live = array();

		$files = $this->loadFileNames();

		// Does the filename from the db row match any in the files on disk?
		while ($row = $_zp_db->fetchAssoc($result)) {
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
		$_zp_db->freeResult($result);

		if (count($dead) > 0) {
			$sql = "DELETE FROM " . $_zp_db->prefix('images') . " WHERE `id` IN(" . implode(',', $dead) . ")";
			$sql2 = "DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type`='albums' AND `ownerid` IN(" . implode(',', $dead) . ")";
			$_zp_db->query($sql);
			$_zp_db->query($sql2);
			$set_updateddate = true;
		}

		// Get all sub-albums and make sure they exist.
		$result = $_zp_db->query("SELECT `id`, `folder` FROM " . $_zp_db->prefix('albums') . " WHERE `folder` LIKE " . $_zp_db->quote($_zp_db->likeEscape($this->name) . '%'));
		$dead = array();
		$live = array();
		// Does the dirname from the db row exist on disk?
		while ($row = $_zp_db->fetchAssoc($result)) {
			if (!is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($row['folder'])) || in_array($row['folder'], $live) || substr($row['folder'], -1) == '/' || substr($row['folder'], 0, 1) == '/') {
				$dead[] = $row['id'];
			} else {
				$live[] = $row['folder'];
			}
		}
		$_zp_db->freeResult($result);
		if (count($dead) > 0) {
			$sql = "DELETE FROM " . $_zp_db->prefix('albums') . " WHERE `id` IN(" . implode(',', $dead) . ")";
			$sql2 = "DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type`='albums' AND `ownerid` IN(" . implode(',', $dead) . ")";
			$_zp_db->query($sql);
			$_zp_db->query($sql2);
			$set_updateddate = true;
		}
		if($set_updateddate) {
			$this->setUpdateddate();
			$this->save();
			$this->setUpdatedDateParents();
		} 

		if ($deep) {
			foreach ($this->getAlbums(0) as $dir) {
				$subalbum = AlbumBase::newAlbum($dir);
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
			trigger_error($msg, E_USER_NOTICE);
			return array();
		}

		$files = array();
		$others = array();

		while (false !== ($file = readdir($dir))) {
			$file8 = filesystemToInternal($file);
			if (@$file8[0] != '.') {
				if ($dirs && (is_dir($albumdir . $file) || hasDynamicAlbumSuffix($file))) {
					$files[] = $file8;
				} else if (!$dirs && is_file($albumdir . $file)) {
					if (Gallery::validImageAlt($file)) {
						$files[] = $file8;
						$others[] = $file8;
					} else if (Gallery::validImage($file)) {
						$files[] = $file8;
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
						if ($image_root == $others_root && Gallery::validImage($image)) {
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