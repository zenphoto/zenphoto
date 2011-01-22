<?php
/**
 * Gallery Class
 * @package classes
 */

// force UTF-8 Ø


class Gallery {

	var $albumdir = NULL;
	var $albums = NULL;
	var $options = NULL;
	var $theme;
	var $themes;
	var $lastalbumsort = NULL;

	/**
	 * Creates an instance of a gallery
	 *
	 * @return Gallery
	 */
	function Gallery() {

		// Set our album directory
		$this->albumdir = getAlbumFolder();

		if (!is_dir($this->albumdir) || !is_readable($this->albumdir)) {
			if (!is_dir($this->albumdir)) {
				$msg = sprintf(gettext('Error: The \'albums\' directory (%s) cannot be found.'),$this->albumdir);
			} else {
				$msg = sprintf(gettext('Error: The \'albums\' directory (%s) is not readable.'),$this->albumdir);
			}
			die($msg);
		}
		getOption('nil'); // force loading of the $options
	}

	/**
	 * Returns the gallery title
	 *
	 * @return string
	 */
	function getTitle() {
		return(get_language_string(getOption('gallery_title')));
	}

	/**
	 * Returns the gallery description
	 *
	 * @return string
	 */
	function getDesc() {
		return(get_language_string(getOption('Gallery_description')));
	}

	/**
	 * Returns the hashed password for guest gallery access
	 *
	 */
	function getPassword() {
		return(getOption('gallery_password'));
	}

	/**
	 * Returns the hind associated with the gallery password
	 *
	 * @return string
	 */
	function getPasswordHint() {
		return(get_language_string(getOption('gallery_hint')));
	}

	function getUser() {
		return(getOption('gallery_user'));
	}

	/**
	 * Returns the main albums directory
	 *
	 * @return string
	 */
	function getAlbumDir() { return $this->albumdir; }

	/**
	 * Returns the DB field corresponding to the album sort type desired
	 *
	 * @param string $sorttype the desired sort
	 * @return string
	 */
	function getAlbumSortKey($sorttype=null) {
		if (empty($sorttype)) { $sorttype = getOption('gallery_sorttype'); }
		return lookupSortKey($sorttype, 'sort_order', 'folder');
	}

	function getSortDirection() {
		return getOption('gallery_sortdirection');
	}

	/**
	 * Get Albums will create our $albums array with a fully populated set of Album
	 * names in the correct order.
	 *
	 * Returns an array of albums (a pages worth if $page is not zero)
	 *
	 * @param int $page An option parameter that can be used to return a slice of the array.
	 * @param string $sorttype the kind of sort desired
	 * @param string $direction set to a direction to override the default option
	 * @param bool $care set to false if the order of the albums does not matter
	 *
	 * @return  array
	 */
	function getAlbums($page=0, $sorttype=null, $direction=null, $care=true) {

		// Have the albums been loaded yet?
		if (is_null($this->albums) || $care && $sorttype.$direction !== $this->lastalbumsort) {

			$albumnames = $this->loadAlbumNames();
			$key = $this->getAlbumSortKey($sorttype);
			$albums = $this->sortAlbumArray(NULL, $albumnames, $key, $direction);

			// Store the values
			$this->albums = $albums;
			$this->lastalbumsort = $sorttype.$direction;
		}

		if ($page == 0) {
			return $this->albums;
		} else {
			return array_slice($this->albums, galleryAlbumsPerPage()*($page-1), galleryAlbumsPerPage());
		}
	}

	/**
	 * Load all of the albums names that are found in the Albums directory on disk.
	 * Returns an array containing this list.
	 *
	 * @return array
	 */
	function loadAlbumNames() {
		$albumdir = $this->getAlbumDir();
		if (!is_dir($albumdir) || !is_readable($albumdir)) {
			if (!is_dir($albumdir)) {
				$msg .= sprintf(gettext('Error: The \'albums\' directory (%s) cannot be found.'),$this->albumdir);
			} else {
				$msg .= sprintf(gettext('Error: The \'albums\' directory (%s) is not readable.'),$this->albumdir);
			}
			die($msg);
		}

		$dir = opendir($albumdir);
		$albums = array();

		while ($dirname = readdir($dir)) {
			if ((is_dir($albumdir.$dirname) && (substr($dirname, 0, 1) != '.')) ||
								hasDynamicAlbumSuffix($dirname)) {
				$albums[] = filesystemToInternal($dirname);
			}
		}
		closedir($dir);
		return zp_apply_filter('album_filter', $albums);
	}


	/**
	 * Returns the a specific album in the array indicated by index.
	 * Takes care of bounds checking, no need to check input.
	 *
	 * @param int $index the index of the album sought
	 * @return Album
	 */
	function getAlbum($index) {
		$this->getAlbums();
		if ($index >= 0 && $index < $this->getNumAlbums()) {
			return new Album($this, $this->albums[$index]);
		} else {
			return false;
		}
	}


	/**
	 * Returns the total number of TOPLEVEL albums in the gallery (does not include sub-albums)
	 * @param bool $db whether or not to use the database (includes ALL detected albums) or the directories
	 * @param bool $publishedOnly set to true to exclude un-published albums
	 * @return int
	 */
	function getNumAlbums($db=false, $publishedOnly=false) {
		$count = -1;
		if (!$db) {
			$this->getAlbums(0, NULL, NULL, false);
			$count = count($this->albums);
		} else {
			$sql = "SELECT count(*) FROM " . prefix('albums');
			if ($publishedOnly) {
				$sql .= ' WHERE `show`=1';
			}
			$result = query($sql);
			$count = db_result($result, 0);
		}
		return $count;
	}


	/**
	 * Populates the theme array and returns it. The theme array contains information about
	 * all the currently available themes.
	 * @return array
	 */
	function getThemes() {
		if (empty($this->themes)) {
			$themedir = SERVERPATH . "/themes";
			$themes = array();
			if ($dp = @opendir($themedir)) {
				while (false !== ($dir = readdir($dp))) {
					if (substr($dir, 0, 1) != "." && is_dir("$themedir/$dir")) {
						$themefile = $themedir . "/$dir/theme_description.php";
						$dir8 = filesystemToInternal($dir);
						if (file_exists($themefile)) {
							$theme_description = array();
							require($themefile);
							$themes[$dir8] = sanitize($theme_description, 1);
						} else if (file_exists($themedir . "/$dir/theme.txt")) {
							$themes[$dir8] = parseThemeDef($themedir . "/$dir/theme.txt");
						} else {
							$themes[$dir8] = array('name'=>gettext('Unknown'), 'author'=>gettext('Unknown'), 'version'=>gettext('Unknown'), 'desc'=>gettext('<strong>Missing theme info file!</strong>'), 'date'=>gettext('Unknown'));
						}
					}
				}
				ksort($themes,SORT_LOCALE_STRING);
			}
			$this->themes = $themes;
		}
		return $this->themes;
	}


	/**
	 * Returns the foldername of the current theme.
	 * if no theme is set, returns "default".
	 * @return string
	 */
	function getCurrentTheme() {
		$theme = NULL;
		if (empty($this->theme)) {
			$theme = getOption('current_theme');
			if (empty($theme) || !file_exists(SERVERPATH."/".THEMEFOLDER."/$theme")) {
				$themes = array_keys($this->getThemes());
				if (!empty($themes)) {
					$theme = array_shift($themes);
				}
			}
			$this->theme = $theme;
		}
		return $this->theme;
	}


	/**
	 * Sets the current theme
	 * @param string the name of the current theme
	 */
	function setCurrentTheme($theme) {
		setOption('current_theme', $theme);
	}


	/**
	 * Returns the number of images from a database SELECT count(*)
	 * Ideally one should call garbageCollect() before to make sure the database is current.
	 * @parm bool $publishedOnly set to true to count only published images.
	 * @return int
	 */
	function getNumImages($publishedOnly=false) {
		if ($publishedOnly) {
			$rows = query_full_array("SELECT `id` FROM " . prefix('albums')." WHERE `show`=0");
			if (is_array($rows)) {
				$exclude = '';
				foreach ($rows as $row)	{
					if (!empty($row['id'])) {
						$exclude .= " `albumid`!=".$row['id'].' AND ';
					}
				}
				$exclude = substr($exclude, 0, strlen($exclude)-5);
			} else {
				$exclude = '';
			}
			if ($publishedOnly) {
				if (empty($exclude)) {
					$exclude = '`show`=1';
				} else {
					$exclude = ' `show`=1 AND '.$exclude;
				}
			}
			if (!empty($exclude)) {
				$exclude = ' WHERE '.$exclude;
			}
		} else {
			$exclude = '';
		}
		$result = query_single_row("SELECT count(*) as `image_count` FROM ".prefix('images').$exclude);
		return $result['image_count'];
	}


	/**
	 * Returns the count of comments
	 *
	 * @param bool $moderated set true if you want to see moderated comments
	 * @return array
	 */
	function getNumComments($moderated=false) {
		$sql = "SELECT count(*) FROM ".prefix('comments');
		if (!$moderated) {
			$sql .= " WHERE `inmoderation`=0";
		}
		$result = query_single_row($sql);
		return array_shift($result);
	}

	/** For every album in the gallery, look for its file. Delete from the database
	 * if the file does not exist. Do the same for images. Clean up comments that have
	 * been left orphaned.
	 *
	 * Returns true if the operation was interrupted because it was taking too long
	 *
	 * @param bool $cascade garbage collect every image and album in the gallery.
	 * @param bool $complete garbage collect every image and album in the *database* - completely cleans the database.
	 * @param  int $restart Image ID to restart scan from
	 * @return bool
	 */
	function garbageCollect($cascade=true, $complete=false, $restart='') {
		if (empty($restart)) {
			// Check for the existence of top-level albums (subalbums handled recursively).
			$sql = "SELECT * FROM " . prefix('albums');
			$result = query($sql);
			$dead = array();
			$live = array(''); // purge the root album if it exists
			$deadalbumthemes = array();
			// Load the albums from disk
			$albumfolder = getAlbumFolder();
			while($row = db_fetch_assoc($result)) {
				$valid = file_exists($albumpath = $albumfolder.internalToFilesystem($row['folder'])) && (hasDynamicAlbumSuffix($albumpath) || (is_dir($albumpath) && strpos($albumpath,'/./') === false && strpos($albumpath,'/../') === false));
				if (!$valid || in_array($row['folder'], $live)) {
					$dead[] = $row['id'];
					if ($row['album_theme'] !== '') {  // orphaned album theme options table
						$deadalbumthemes[$row['id']] = $row['folder'];
					}
				} else {
					$live[] = $row['folder'];
				}
			}

			if (count($dead) > 0) { /* delete the dead albums from the DB */
				$first = array_pop($dead);
				$sql1 = "DELETE FROM " . prefix('albums') . " WHERE `id`='$first'";
				$sql2 = "DELETE FROM " . prefix('images') . " WHERE `albumid`='$first'";
				$sql3 = "DELETE FROM " . prefix('comments') . " WHERE `type`='albums' AND `ownerid`='$first'";
				$sql4 = "DELETE FROM " . prefix('obj_to_tag'). " WHERE `type`='albums' AND `objectid`='$first'";
				foreach ($dead as $albumid) {
					$sql1 .= " OR `id` = '$albumid'";
					$sql2 .= " OR `albumid` = '$albumid'";
					$sql3 .= " OR `ownerid` = '$albumid'";
					$sql4 .= " OR `objectid` = '$albumid'";
				}
				$n = query($sql1);
				if (!$complete && $n && $cascade) {
					query($sql2);
					query($sql3);
					query($sql4);
				}
			}
			if (count($deadalbumthemes) > 0) { // delete the album theme options tables for dead albums
				foreach ($deadalbumthemes as $id=>$deadtable) {
					$sql = 'DELETE FROM '.prefix('options').' WHERE `ownerid`='.$id;
					query($sql, false);

				}
			}
		}

		if ($complete) {
			if (empty($restart)) {
				/* refresh 'metadata' albums */
				$albumfolder = getAlbumFolder();
				$albumids = query_full_array("SELECT `id`, `mtime`, `folder`, `dynamic` FROM " . prefix('albums'));
				foreach ($albumids as $analbum) {
					if (($mtime=filemtime($albumfolder.internalToFilesystem($analbum['folder']))) > $analbum['mtime']) {  // refresh
						$album = new Album($this, $analbum['folder']);
						$album->set('mtime', $mtime);
						if ($album->isDynamic()) {
							$data = file_get_contents($album->localpath);
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
								}
								if (strpos($data1, 'FIELDS=') !== false) {
									$fields = "&searchfields=".trim(substr($data1, 7));
								}
							}
							if (!empty($words)) {
								if (empty($fields)) {
									$fields = '&searchfields=tags';
								}
							}
							$album->set('search_params',$words.$fields);
							$album->set('thumb',$thumb);
						}
						$album->save();
						zp_apply_filter('album_refresh',$album);
					}
				}

				/* Delete all image entries that don't belong to an album at all. */

				$albumids = query_full_array("SELECT `id` FROM " . prefix('albums'));                  /* all the album IDs */
				$idsofalbums = array();
				foreach($albumids as $row) { $idsofalbums[] = $row['id']; }
				$imageAlbums = query_full_array("SELECT DISTINCT `albumid` FROM " . prefix('images')); /* albumids of all the images */
				$albumidsofimages = array();
				foreach($imageAlbums as $row) { $albumidsofimages[] = $row['albumid']; }
				$orphans = array_diff($albumidsofimages, $idsofalbums);                                /* albumids of images with no album */

				if (count($orphans) > 0 ) { /* delete dead images from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM ".prefix('images')." WHERE `albumid`='" . $firstrow . "'";
					foreach($orphans as $id) {
						$sql .= " OR `albumid`='" . $id . "'";
					}
					query($sql);

					// Then go into existing albums recursively to clean them... very invasive.
					foreach ($this->getAlbums(0) as $folder) {
						$album = new Album($this, $folder);
						if (!$album->isDynamic()) {
							if(is_null($album->getDateTime())) {  // see if we can get one from an image
								$images = $album->getImages(0,0,'date','DESC');
								if(count($images)>0) {
									$image = newImage($album,array_shift($images));
									$album->setDateTime($image->getDateTime());
								}
							}
							$album->garbageCollect(true);
							$album->preLoad();
						}
						$album->save();
						zp_apply_filter('album_refresh', $album);
					}
				}
			}

			/* Look for image records where the file no longer exists. While at it, check for images with IPTC data to update the DB */

			$start = array_sum(explode(" ",microtime()));  // protect against too much processing.
			if (!empty($restart)) {
				$restartwhere = ' WHERE `id`>'.$restart.' AND `mtime`=0';
			} else {
				$restartwhere = ' WHERE `mtime`=0';
			}
			define('RECORD_LIMIT',5);
			$sql = 'SELECT * FROM ' . prefix('images').$restartwhere.' ORDER BY `id` LIMIT '.(RECORD_LIMIT+2);
			$images = query_full_array($sql);
			if (count($images) > 0) {
				$c = 0;
				foreach($images as $image) {
					$sql = 'SELECT `folder` FROM ' . prefix('albums') . ' WHERE `id`="' . $image['albumid'] . '";';
					$row = query_single_row($sql);
					$imageName = internalToFilesystem(getAlbumFolder() . $row['folder'] . '/' . $image['filename']);
					if (file_exists($imageName)) {
						$mtime = filemtime($imageName);
						if ($image['mtime'] != $mtime) { // file has changed since we last saw it
							$imageobj = newImage(new Album($this, $row['folder']), $image['filename']);
							$imageobj->set('mtime', $mtime);
							$imageobj->updateMetaData(); // prime the EXIF/IPTC fields
							$imageobj->updateDimensions(); // update the width/height & account for rotation
							$imageobj->save();
							zp_apply_filter('image_refresh', $imageobj);
						}
					} else {
						$sql = 'DELETE FROM ' . prefix('images') . ' WHERE `id`="' . $image['id'] . '";';
						$result = query($sql);
						$sql = 'DELETE FROM ' . prefix('comments') . ' WHERE `type` IN ('.zp_image_types('"').') AND `ownerid` ="' . $image['id'] . '";';
						$result = query($sql);
					}
					if (++$c >= RECORD_LIMIT) {
						return $image['id']; // avoide excessive processing
					}
				}
			}

			if (empty($restart)) {
				/* clean the comments table */
				/* do the images */
				$imageids = query_full_array('SELECT `id` FROM ' . prefix('images'));       /* all the image IDs */
				$idsofimages = array();
				foreach($imageids as $row) { $idsofimages[] = $row['id']; }
				$commentImages = query_full_array("SELECT DISTINCT `ownerid` FROM " .
				prefix('comments') . 'WHERE `type` IN ('.zp_image_types('"').')'); /* imageids of all the comments */
				$imageidsofcomments = array();
				foreach($commentImages as $row) { $imageidsofcomments [] = $row['ownerid']; }
				$orphans = array_diff($imageidsofcomments , $idsofimages );                 /* image ids of comments with no image */

				if (count($orphans) > 0 ) { /* delete dead comments from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM " . prefix('comments') . " WHERE `type` IN (".zp_image_types("'").") AND `ownerid`='" . $firstrow . "'";
					foreach($orphans as $id) {
						$sql .= " OR `ownerid`='" . $id . "'";
					}
					query($sql);
				}

				/* do the same for album comments */
				$albumids = query_full_array('SELECT `id` FROM ' . prefix('albums'));      /* all the album IDs */
				$idsofalbums = array();
				foreach($albumids as $row) { $idsofalbums[] = $row['id']; }
				$commentAlbums = query_full_array("SELECT DISTINCT `ownerid` FROM " .
				prefix('comments') . 'WHERE `type`="albums"');                         /* album ids of all the comments */
				$albumidsofcomments = array();
				foreach($commentAlbums as $row) { $albumidsofcomments [] = $row['ownerid']; }
				$orphans = array_diff($albumidsofcomments , $idsofalbums );                 /* album ids of comments with no album */
				if (count($orphans) > 0 ) { /* delete dead comments from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM " . prefix('comments') . "WHERE `type`='albums' AND `ownerid`='" . $firstrow . "'";
					foreach($orphans as $id) {
						$sql .= " OR `ownerid`='" . $id . "'";
					}
					query($sql);
				}

				/* clean the tags table */
				/* do the images */
				$tagImages = query_full_array("SELECT DISTINCT `objectid` FROM ".prefix('obj_to_tag').' WHERE `type`="images"');                         /* imageids of all the comments */
				$imageidsoftags = array();
				foreach($tagImages as $row) { $imageidsoftags [] = $row['objectid']; }
				$orphans = array_diff($imageidsoftags , $idsofimages );                 /* image ids of comments with no image */
				if (count($orphans) > 0 ) { /* delete dead tags from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM " . prefix('obj_to_tag') . ' WHERE `type`="images" AND (`objectid`="' . $firstrow . '"';
					foreach($orphans as $id) {
						$sql .= " OR `objectid`='" . $id . "'";
					}
					$sql .= ')';
					query($sql);
				}

				/* do the same for album tags */
				$tagAlbums = query_full_array("SELECT DISTINCT `objectid` FROM ".prefix('obj_to_tag').'WHERE `type`="albums"');                         /* album ids of all the comments */
				$albumidsoftags = array();
				foreach($tagAlbums as $row) { $albumidsoftags [] = $row['objectid']; }
				$orphans = array_diff($albumidsoftags , $idsofalbums );                 /* album ids of comments with no album */
				if (count($orphans) > 0 ) { /* delete dead tags from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`='" . $firstrow . "'";
					foreach($orphans as $id) {
						$sql .= " OR `objectid`='" . $id . "'";
					}
					query($sql);
				}
			}
		}
		return false;
	}

	/**
	 * Returns the size in bytes of the cache folder. WARNING: VERY SLOW.
	 * @return int
	 */
	function sizeOfCache() {
		$cachefolder = SERVERCACHE;
		if (is_dir($cachefolder)) {
			return dirsize($cachefolder);
		} else {
			return 0;
		}
	}


	/**
	 * Returns the size in bytes of the albums folder. WARNING: VERY SLOW.
	 * @return int
	 */
	function sizeOfImages() {
		$imagefolder = substr(getAlbumFolder(), 0, -1);
		if (is_dir($imagefolder)) {
			return dirsize($imagefolder);
		} else {
			return 0;
		}
	}


	/**
	 * Cleans out the cache folder
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	function clearCache($cachefolder=NULL) {
		if (is_null($cachefolder)) {
			$cachefolder = SERVERCACHE;
		}
		if (is_dir($cachefolder)) {
			$handle = opendir($cachefolder);
			while (false !== ($filename = readdir($handle))) {
				$fullname = $cachefolder . '/' . $filename;
				if (is_dir($fullname) && !(substr($filename, 0, 1) == '.')) {
					if (($filename != '.') && ($filename != '..')) {
						$this->clearCache($fullname);
						rmdir($fullname);
					}
				} else {
					if (file_exists($fullname) && !(substr($filename, 0, 1) == '.')) {
						unlink($fullname);
					}
				}

			}
			closedir($handle);
		}
	}

	/**
	 * Sort the album array based on either according to the sort key.
	 * Default is to sort on the `sort_order` field.
	 *
	 * Returns an array with the albums in the desired sort order
	 *
	 * @param  array $albums array of album names
	 * @param  string $sortkey the sorting scheme
	 * @return array
	 *
	 * @author Todd Papaioannou (lucky@luckyspin.org)
	 * @since  1.0.0
	 */
	function sortAlbumArray($parentalbum, $albums, $sortkey='`sort_order`', $sortdirection=NULL) {
		if (is_null($parentalbum)) {
			$albumid = ' IS NULL';
			$obj = $this;
		} else {
			$albumid = '='.$parentalbum->id;
			$obj = $parentalbum;
		}
		if (($sortkey == '`sort_order`') || ($sortkey == 'RAND()')) { // manual sort is always ascending
			$order = false;
		} else {
			if (!is_null($sortdirection)) {
				$order = strtoupper($sortdirection) == 'DESC';
			} else {
				$order = $obj->getSortDirection('album');
			}
		}
		if (count($albums) == 0) return array();
		$sql = 'SELECT * FROM ' .	prefix("albums") . ' WHERE `parentid`'.$albumid;
		$result = query($sql);
		$results = array();
		while ($row = db_fetch_assoc($result)) {
			$results[$row['folder']] = $row;
		}
		//	check database aganist file system
		foreach ($results as $dbrow=>$row) {
			$folder = $row['folder'];
			if (($key = array_search($folder,$albums)) !== false) {	// album exists in filesystem
				unset($albums[$key]);
			} else {																								// album no longer exists
				$id = $row['id'];
				query("DELETE FROM ".prefix('albums')." WHERE `id`=$id"); // delete the record
				query("DELETE FROM ".prefix('comments')." WHERE `type` ='images' AND `ownerid`= '$id'"); // remove image comments
				query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $id);
				query("DELETE FROM " . prefix('albums') . " WHERE `id` = " . $id);
				unset($results[$dbrow]);
			}
		}
		foreach ($albums as $folder) {	// these albums are not in the database
			$albumobj = new Album($this,$folder);
			if ($albumobj->exists) {	// fail to instantiate?
				$results[$folder] = $albumobj->data;
			}
		}
		//	now put the results in the right order
		$sortkey = str_replace('`','',$sortkey);
		switch ($sortkey) {
			case 'title':
			case 'desc':
				$results = sortByMultilingual($results, $sortkey, $order);
				break;
			case 'RAND()':
				shuffle($results);
				break;
			default:
				$results = sortMultiArray($results, $sortkey, $order);
				break;
		}
		//	albums are now in the correct order
		$albums_ordered = array();
		foreach($results as $row) { // check for visible
			$folder = $row['folder'];
			$album = new Album($this, $folder);
			if ($row['show'] || $album->isMyItem(LIST_RIGHTS)) {
				$albums_ordered[] = $folder;
			}
		}
		return $albums_ordered;
	}

	/**
	 * Returns the hitcount
	 *
	 * @return int
	 */
	function getHitcounter() {
		return getOption('Page-Hitcounter-index');
	}

	/**
	 * counts visits to the object
	 */
	function countHit() {
		$hc = getOption('Page-Hitcounter-index')+1;
		setOption('Page-Hitcounter-index', $hc);
	}

}
?>
