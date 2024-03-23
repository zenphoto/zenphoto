<?php
/**
 * Gallery Class
 * @package zpcore\classes\objects
 */
class Gallery {

	public $albumdir = NULL;
	public $table = 'gallery';
	protected $albums = NULL;
	protected $theme;
	protected $themes;
	protected $lastalbumsort = NULL;
	protected $data = array();
	protected $unprotected_pages = array();

	/**
	 * Creates an instance of a gallery
	 *
	 * @return Gallery
	 */
	function __construct() {
// Set our album directory
		$this->albumdir = ALBUM_FOLDER_SERVERPATH;
		$data = getOption('gallery_data');
		if ($data) {
			$this->data = getSerializedArray($data);
		}
		if (isset($this->data['unprotected_pages'])) {
			$pages = getSerializedArray($this->data['unprotected_pages']);
			if (is_array($pages))
				$this->unprotected_pages = $pages; //	protect against a failure
		}
	}

	/**
	 * Returns the gallery title
	 *
	 * @return string
	 */
	function getTitle($locale = NULL) {
		$text = $this->get('gallery_title');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Returns a tag stripped title
	 * @param string $locale
	 * @return string
	 */
	function getBareTitle($locale = NULL) {
		return getBare($this->getTitle($locale));
	}

	function setTitle($title) {
		$this->set('gallery_title', tagURLs($title));
	}

	/**
	 * Returns the gallery description
	 *
	 * @return string
	 */
	function getDesc($locale = NULL) {
		$text = $this->get('Gallery_description');
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * Sets the gallery description
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $desc
	 */
	function setDesc($desc) {
		$desc = tagURLs($desc);
		$this->set('Gallery_description', $desc);
	}
	
	/**
	 * Gets the copyright notice
	 * @since 1.5.8
	 * 
	 * @param type $locale
	 * @return type
	 */
	function getCopyrightNotice($locale = null) {
		$text = $this->get('copyright_site_notice');
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * Sets the copyright notice
	 * @since 1.5.8
	 * @param type $notice
	 */
	function setCopyrightNotice($notice) {
		$notice = tagURLs($notice);
		$this->set('copyright_site_notice', $notice);
	}

	/**
	 * Gets the copyright site holder
	 * 
	 * Note: This does not fetch an actual field value but with some fallbacks
	 * 
	 * - 'copyright_site_rightsholder' field
	 * - 'copyright_site_rightsholder_custom' field
	 * - The site master user
	 * 
	 * @see getRightsholderCopyrightCustom()
	 * @see getRightsholderCopyright()
	 * 
	 * @since 1.5.8
	 */
	function getCopyrightRightsholder() {
		$rightsholder = $this->get('copyright_site_rightsholder');
		if ($rightsholder && $rightsholder != 'none') {
			if ($rightsholder == 'custom') {
				$rightsholder = $this->get('copyright_site_rightsholder_custom');
			} else {
				$rightsholder = Administrator::getNameByUser($rightsholder);
			}
		}
		if (empty($rightsholder)) {
			$authority = new Authority();
			$master = $authority->getMasterUser();
			$rightsholder = $master->getName();
		}
		return $rightsholder;
	}
	
	/**
	 * 
	 * Gets the copyright site url
	 * 
	 * @since 1.5.8 
	 * @return type
	 */
	function getCopyrightURL() {
		$url = $this->get('copyright_site_url');
		if ($url) {
			if ($url == 'custom') {
				return $this->get('copyright_site_url_custom');
			} else if ($url == 'none') {
				return null;
			} else {
				if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
					$pageobj = new ZenpagePage($url);
					if ($pageobj->exists) {
						return $pageobj->getLink();
					}
				}
			}
		}
	}

	/**
	 * Sets the copyright site url
	 * 
	 * @since 1.5.8
	 * @param type $url
	 */
	function setCopyrightURL($url) {
		$this->set('copyright_site_url', $url);
	}

	/**
	 * Returns the hashed password for guest gallery access
	 *
	 */
	function getPassword() {
		if (GALLERY_SECURITY != 'public') {
			return NULL;
		} else {
			return $this->get('gallery_password');
		}
	}

	function setPassword($value) {
		$this->set('gallery_password', $value);
	}

	/**
	 * Returns the hind associated with the gallery password
	 *
	 * @return string
	 */
	function getPasswordHint($locale = NULL) {
		$text = $this->get('gallery_hint');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	function setPasswordHint($value) {
		$this->set('gallery_hint', tagURLs($value));
	}

	function getUser() {
		return($this->get('gallery_user'));
	}

	function setUser($value) {
		$this->set('gallery_user', $value);
	}

	/**
	 * Returns the main albums directory
	 *
	 * @return string
	 */
	function getAlbumDir() {
		return $this->albumdir;
	}

	/**
	 * Returns the DB field corresponding to the album sort type desired
	 *
	 * @param string $sorttype the desired sort
	 * @return string
	 */
	function getAlbumSortKey($sorttype = null) {
		if (empty($sorttype)) {
			$sorttype = $this->getSortType();
		}
		return lookupSortKey($sorttype, 'sort_order', 'albums');
	}

	function getSortDirection() {
		return $this->get('sort_direction');
	}

	function setSortDirection($value) {
		$this->set('sort_direction', (int) ($value && true));
	}

	function getSortType() {
		$type = $this->get('gallery_sorttype');
		return $type;
	}

	function setSortType($value) {
		$this->set('gallery_sorttype', $value);
	}

	/**
	 * Get Albums will create our $albums array with a fully populated set of Album
	 * names in the correct order.
	 *
	 * Returns an array of albums (a pages worth if $page is not zero)
	 *
	 * @param int $page An option parameter that can be used to return a slice of the array.
	 * @param string $sorttype the kind of sort desired
	 * @param string $sortdirection set to a direction to override the default option
	 * @param bool $care set to false if the order of the albums does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return  array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {

		// Have the albums been loaded yet?
		if ($mine || is_null($this->albums) || $care && $sorttype . $sortdirection !== $this->lastalbumsort) {
			if (is_null($sorttype)) {
				$sorttype = $this->getSortType();
			}
			if (is_null($sortdirection)) {
				if ($this->getSortDirection()) {
					$sortdirection = 'DESC';
				} else {
					$sortdirection = '';
				}
			}
			$albumnames = $this->loadAlbumNames();
			$key = $this->getAlbumSortKey($sorttype);
			$albums = $this->sortAlbumArray(NULL, $albumnames, $key, $sortdirection, $mine);

			// Store the values
			$this->albums = $albums;
			$this->lastalbumsort = $sorttype . $sortdirection;
		}

		if ($page == 0) {
			return $this->albums;
		} else {
			return array_slice($this->albums, galleryAlbumsPerPage() * ($page - 1), galleryAlbumsPerPage());
		}
	}

	/**
	 * Load all of the albums names that are found in the Albums directory on disk.
	 * Returns an array containing this list.
	 *
	 * @return array
	 */
	private function loadAlbumNames() {
		$albumdir = $this->getAlbumDir();
		$dir = opendir($albumdir);
		$msg = '';
		if (!$dir) {
			if (!is_dir($albumdir)) {
				$msg .= sprintf(gettext('Error: The “albums” directory (%s) cannot be found.'), $this->albumdir);
			} else {
				$msg .= sprintf(gettext('Error: The “albums” directory (%s) is not readable.'), $this->albumdir);
			}
			zp_error($msg);
		}
		$albums = array();

		while ($dirname = readdir($dir)) {
			if ($dirname[0] != '.' && (is_dir($albumdir . $dirname) || hasDynamicAlbumSuffix($dirname))) {
				$albums[] = filesystemToInternal($dirname);
			}
		}
		closedir($dir);
		return zp_apply_filter('album_filter', $albums);
	}
	
	/**
	 * Gets all albums with the right order as set on the backend. 
	 * 
	 * Note while this is an very accurate result since this is using the filesystem to check
	 * and keeps the individuel sort order settings of each subalbum level it is therefore very slow on large galleries with 
	 * thousands of albums. 
	 * 
	 * Use the much faster but less accurate getAllAlbumsFromDB() instead if speed is needed. This is best used for any 
	 * albums selector.
	 * 
	 * Note unless the §rights parameter is set to ALL_ALBUMS_RIGHTS or higher or the user is full admin dynamic albums are excluded.
	 * 
	 * @since 1.5.8 - general functionality moved from the old admin function genAlbumList()
	 * 
	 * @param obj $albumobj Default null for all albums, optional albumobject to get all sublevels of
	 * @param int $rights Rights constant to check the album access by, default UPLOAD_RIGHTS. Set to null to disable rights check
	 * @param bool $includetitles If set to true (default) returns an array with the album names as keys and the titles as values, otherwise just an array with the names
	 * @return array
	 */
	function getAllAlbums($albumobj = NULL, $rights = UPLOAD_RIGHTS, $includetitles = true) {
		$allalbums = array();
		$is_fulladmin = zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS); // can see all albums
		if (AlbumBase::isAlbumClass($albumobj)) {
			$albums = $albumobj->getAlbums(0);
		} else {
			$albums = $this->getAlbums(0);
		}
		if (is_array($albums)) {
			foreach ($albums as $folder) {
				$album = AlbumBase::newAlbum($folder);
				if ($is_fulladmin || $album->isVisible($rights)) {
					if ($album->isDynamic()) {
						if ($is_fulladmin || $rights == ALL_ALBUMS_RIGHTS) {
							if ($includetitles) {
								$allalbums[$album->getName()] = $album->getTitle();
							} else {
								$allalbums[] = $album->getName();
							}
						}
					} else {
						if ($includetitles) {
							$allalbums[$album->getName()] = $album->getTitle();
						} else {
							$allalbums[] = $album->getName();
						}
						$allalbums = array_merge($allalbums, $this->getAllAlbums($album, $rights));
					}
				}
			}
		}
		return $allalbums;
	}

	/**
	 * Gets all albums from the database direclty. This is less accurate than getAllAlbums() but much faster on 
	 * large sites with thousends of albums especially if $keeplevel_sortorder is kept false.
	 * Note that the filesystem and databse may be out of sync in the moment of fetching the data.
	 * If you need to be sure to cover this use getAllAlbums();
	 * 
	 * $keeplevel_sortorder is false so the order follows their nesting but the sortorder 
	 * of individual sublevels is not kept to archieve greater speed. 
	 * Order is simply by folder name instead. Set to true each subalbum level follows its individual sort order setting.
	 * While this is also faster than getAllAlbums() this is significantly slower than the default.
	 * 
	 * Note unless the §rights parameter is set to ALL_ALBUMS_RIGHTS or higher or the user is full admin dynamic albums are excluded.
	 * 
	 * @since 1.5.8
	 * 
	 * @param bool $keeplevel_sortorder Default false, set to true if the sublevels should sorted by their individual settings (slower)
	 * @param obj $albumobj Default null for all albums, optional albumobject to get all sublevels of
	 * @param int $rights Rights constant to check the album access by, default UPLOAD_RIGHTS
	 * @param bool $includetitles If set to true (default) returns an array with the album names as keys and the titles as values, otherwise just an array with the names
	 * @return array
	 */
	function getAllAlbumsFromDB($keeplevel_sortorder = false, $albumobj = NULL, $rights = UPLOAD_RIGHTS, $includetitles = true) {
		global $_zp_db;
		$allalbums = array();
		$is_fulladmin = zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS);
		$sorttype = 'folder';
		$sortdirection = ' ASC';
		$sql = 'SELECT `folder` FROM ' . $_zp_db->prefix('albums');
		if (AlbumBase::isAlbumClass($albumobj)) {
			// subalbums of an album
			$sql .= " WHERE `folder` like '" . $albumobj->name . "/%'";
			if ($keeplevel_sortorder) {
				$sorttype = $albumobj->getSortType('album');
				if ($albumobj->getSortDirection('album')) {
					$sortdirection = ' DESC';
				} else {
					$sortdirection = ' ASC';
				}
			}
		} else {
			if ($keeplevel_sortorder) {
				$sql .= " WHERE `parentid` IS NULL";
				$sorttype = $this->getSortType();
				if ($this->getSortDirection()) {
					$sortdirection = ' DESC';
				} else {
					$sortdirection = ' ASC';
				}
			}
		}
		if ($sorttype == 'manual') {
			$sorttype = 'sort_order';
		}
		$sql .= ' ORDER BY ' . $sorttype . $sortdirection;
		$result = $_zp_db->query($sql);
		if ($result) {
			while ($row = $_zp_db->fetchAssoc($result)) {
				$album = AlbumBase::newAlbum($row['folder']);
				if ($album->exists && ($is_fulladmin || $album->isVisible($rights))) {
					if ($album->isDynamic()) {
						if ($is_fulladmin || $rights == ALL_ALBUMS_RIGHTS) {
							if ($includetitles) {
								$allalbums[$album->getName()] = $album->getTitle();
							} else {
								$allalbums[] = $album->getName();
							}
						}
					} else {
						if ($includetitles) {
							$allalbums[$album->getName()] = $album->getTitle();
						} else {
							$allalbums[] = $album->getName();
						}
						if ($keeplevel_sortorder) {
							$allalbums = array_merge($allalbums, $this->getAllAlbumsFromDB($keeplevel_sortorder, $album, $rights, $includetitles));
						}
					}
				}
			}
			$_zp_db->freeResult($result);
		}
		return $allalbums;
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
			return AlbumBase::newAlbum($this->albums[$index]);
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
	function getNumAlbums($db = false, $publishedOnly = false) {
		global $_zp_db;
		$count = -1;
		if (!$db) {
			$this->getAlbums(0, NULL, NULL, false);
			$count = count($this->albums);
		} else {
			$sql = '';
			if ($publishedOnly) {
				$sql = 'WHERE `show`=1';
			}
			$count = $_zp_db->count('albums', $sql);
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
							$themes[$dir8] = $theme_description;
						} else {
							$themes[$dir8] = array('name' => gettext('Unknown'), 'author' => gettext('Unknown'), 'version' => gettext('Unknown'), 'desc' => gettext('<strong>Missing theme info file!</strong>'), 'date' => gettext('Unknown'));
						}
					}
				}
				ksort($themes, SORT_LOCALE_STRING);
			}
			$this->themes = $themes;
		}
		return $this->themes;
	}

	/**
	 * Returns the foldername of the current theme.
	 * if no theme is set, picks the "first" theme.
	 * @return string
	 */
	function getCurrentTheme() {
		$theme = NULL;
		if (empty($this->theme)) {
			$theme = $this->get('current_theme');
			if (empty($theme) || !file_exists(SERVERPATH . "/" . THEMEFOLDER . "/$theme")) {
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
		$this->set('current_theme', $this->theme = $theme);
	}

	/**
	 * Returns the number of images in the gallery
	 * @param int $what 0: all images from the database
	 * 									1: published images from the database
	 * 									2: "viewable" images via the object model
	 * @return int
	 */
	function getNumImages($what = 0) {
		global $_zp_db;
		switch ((int) $what) {
			case 0:
				return $_zp_db->count('images', '');
				break;
			case 1:
				$rows = $_zp_db->query("SELECT `id` FROM " . $_zp_db->prefix('albums') . " WHERE `show`=0");
				$idlist = array();
				$exclude = 'WHERE `show`=1';
				if ($rows) {
					while ($row = $_zp_db->fetchAssoc($rows)) {
						$idlist[] = $row['id'];
					}
					if (!empty($idlist)) {
						$exclude .= ' AND `albumid` NOT IN (' . implode(',', $idlist) . ')';
					}
					$_zp_db->freeResult($rows);
				}
				return $_zp_db->count('images', $exclude);
				break;
			case 2:
				$count = 0;
				$albums = $this->getAlbums(0);
				foreach ($albums as $analbum) {
					$album = AlbumBase::newAlbum($analbum);
					if (!$album->isDynamic()) {
						$count = $count + $this->getImageCount($album);
					}
				}
				return $count;
				break;
		}
	}

	private function getImageCount($album) {
		$count = $album->getNumImages();
		$albums = $album->getAlbums(0);
		foreach ($albums as $analbum) {
			$album = AlbumBase::newAlbum($analbum);
			if (!$album->isDynamic()) {
				$count = $count + $this->getImageCount($album);
			}
		}
		return $count;
	}

	/**
	 * Returns the count of comments
	 *
	 * @param bool $moderated set true if you want to see moderated comments
	 * @return array
	 */
	function getNumComments($moderated = false) {
		global $_zp_db;
		$sql = '';
		if (!$moderated) {
			$sql = "WHERE `inmoderation`=0";
		}
		return $_zp_db->count('comments', $sql);
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
	function garbageCollect($cascade = true, $complete = false, $restart = '') {
		global $_zp_gallery, $_zp_db;
		if (empty($restart)) {
			setOption('last_garbage_collect', time());
			/* purge old search cache items */
			$sql = 'DELETE FROM ' . $_zp_db->prefix('search_cache');
			if (!$complete) {
				$sql .= ' WHERE `date`<' . $_zp_db->quote(date('Y-m-d H:m:s', time() - SEARCH_CACHE_DURATION * 60));
			}
			$result = $_zp_db->query($sql);

			/* clean the comments table */
			$this->commentClean('images');
			$this->commentClean('albums');
			$this->commentClean('news');
			$this->commentClean('pages');
			// clean up obj_to_tag
			$dead = array();
			$result = $_zp_db->query("SELECT `id`, `type`, `tagid`, `objectid` FROM " . $_zp_db->prefix('obj_to_tag'));
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$tbl = $row['type'];
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix('tags') . " WHERE `id`='" . $row['tagid'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix($tbl) . " WHERE `id`='" . $row['objectid'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
				}
				$_zp_db->freeResult($result);
			}
			if (!empty($dead)) {
				$dead = array_unique($dead);
				$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('obj_to_tag') . ' WHERE `id`=' . implode(' OR `id`=', $dead));
			}
			// clean up admin_to_object
			$dead = array();
			$result = $_zp_db->query("SELECT `id`, `type`, `adminid`, `objectid` FROM " . $_zp_db->prefix('admin_to_object'));
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix('administrators') . " WHERE `id`='" . $row['adminid'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
					$tbl = $row['type'];
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix($tbl) . " WHERE `id`='" . $row['objectid'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
				}
				$_zp_db->freeResult($result);
			}
			if (!empty($dead)) {
				$dead = array_unique($dead);
				$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('admin_to_object') . ' WHERE `id` IN(' . implode(',', $dead) . ')');
			}
			// clean up news2cat
			$dead = array();
			$result = $_zp_db->query("SELECT `id`, `news_id`, `cat_id` FROM " . $_zp_db->prefix('news2cat'));
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix('news') . " WHERE `id`='" . $row['news_id'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
					$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix('news_categories') . " WHERE `id`='" . $row['cat_id'] . "'", false);
					if (!$dbtag) {
						$dead[] = $row['id'];
					}
				}
				$_zp_db->freeResult($result);
			}
			if (!empty($dead)) {
				$dead = array_unique($dead);
				$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('news2cat') . ' WHERE `id` IN(' . implode(',', $dead) . ')');
			}

			// Check for the existence albums
			$set_updateddate = false;
			$dead = array();
			$live = array(''); // purge the root album if it exists
			$deadalbumthemes = array();
			// Load the albums from disk
			$result = $_zp_db->query("SELECT `id`, `folder`, `album_theme` FROM " . $_zp_db->prefix('albums'));
			while ($row = $_zp_db->fetchAssoc($result)) {
				$albumpath = internalToFilesystem($row['folder']);
				$albumpath_valid = preg_replace('~/\.*/~', '/', $albumpath);
				$albumpath_valid = ltrim(trim($albumpath_valid, '/'), './');
				$illegal = $albumpath != $albumpath_valid;
				$valid = file_exists(ALBUM_FOLDER_SERVERPATH . $albumpath_valid) && (hasDynamicAlbumSuffix($albumpath_valid) || is_dir(ALBUM_FOLDER_SERVERPATH . $albumpath_valid));
				if ($valid && $illegal) { // maybe there is only one record so we can fix it.
					$valid = $_zp_db->query('UPDATE ' . $_zp_db->prefix('albums') . ' SET `folder`=' . $_zp_db->quote($albumpath_valid) . ' WHERE `id`=' . $row['id'], false);
					debugLog(sprintf(gettext('Invalid album folder: %1$s %2$s'), $albumpath, $valid ? gettext('fixed') : gettext('discarded')));
				}
				if (!$valid || in_array($row['folder'], $live)) {
					$dead[] = $row['id'];
					if ($row['album_theme'] !== '') { // orphaned album theme options table
						$deadalbumthemes[$row['id']] = $row['folder'];
					}
				} else {
					$live[] = $row['folder'];
				}
			}
			$_zp_db->freeResult($result);

			if (count($dead) > 0) { /* delete the dead albums from the DB */
				asort($dead);
				$criteria = '(' . implode(',', $dead) . ')';
				$sql1 = "DELETE FROM " . $_zp_db->prefix('albums') . " WHERE `id` IN $criteria";
				$n = $_zp_db->query($sql1);
				if (!$complete && $n && $cascade) {
					$sql2 = "DELETE FROM " . $_zp_db->prefix('images') . " WHERE `albumid` IN $criteria";
					$_zp_db->query($sql2);
					$sql3 = "DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type`='albums' AND `ownerid` IN $criteria";
					$_zp_db->query($sql3);
					$sql4 = "DELETE FROM " . $_zp_db->prefix('obj_to_tag') . " WHERE `type`='albums' AND `objectid` IN $criteria";
					$_zp_db->query($sql4);
				}
			}
			if (count($deadalbumthemes) > 0) { // delete the album theme options tables for dead albums
				foreach ($deadalbumthemes as $id => $deadtable) {
					$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `ownerid`=' . $id;
					$_zp_db->query($sql, false);
				}
			}
			if (count($dead) > 0) {
				// Set updateddate on possible parent albums of deleted ones
				$result = $_zp_db->query("SELECT `parentid`, `folder` FROM " . $_zp_db->prefix('albums') . ' WHERE `id` IN(' . implode(',', $dead) . ')');
				while ($row = $_zp_db->fetchAssoc($result)) {
					if($row['parentid'] != 0) {
						$parentalbum = getItemByID('albums', $row['parentid']);
						$parentalbum->setUpdateddate();
						$parentalbum->save();
						$parentalbum->setUpdatedDateParents();
					}
				}
			}
		}

		if ($complete) {
			if (empty($restart)) {
				/* check album parent linkage */
				$albums = $_zp_gallery->getAlbums();
				foreach ($albums as $album) {
					checkAlbumParentid($album, NULL, 'debuglog');
				}

				/* refresh 'metadata' albums */
				$albumids = $_zp_db->query("SELECT `id`, `mtime`, `folder`, `dynamic` FROM " . $_zp_db->prefix('albums'));
				if ($albumids) {
					while ($analbum = $_zp_db->fetchAssoc($albumids)) {
						if (($mtime = filemtime(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($analbum['folder']))) > $analbum['mtime']) {
							// refresh
							$album = AlbumBase::newAlbum($analbum['folder']);
							$album->set('mtime', $mtime);
							if (empty($album->getDateTime())) {
								$album->setDateTime(date('Y-m-d H:i:s', $mtime));
							} 
							if ($album->isDynamic()) {
								$data = file_get_contents($album->localpath);
								$thumb = getOption('AlbumThumbSelect');
								$words = $fields = '';
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
									}
									if (strpos($data1, 'FIELDS=') !== false) {
										$fields = "&searchfields=" . trim(substr($data1, 7));
									}
								}
								if (!empty($words)) {
									if (empty($fields)) {
										$fields = '&searchfields=tags';
									}
								}
								$album->set('search_params', $words . $fields);
								$album->set('thumb', $thumb);
							}
							$album->save();
							zp_apply_filter('album_refresh', $album);
						}
					}
					$_zp_db->freeResult($albumids);
				}

				/* Delete all image entries that don't belong to an album at all. */
				$albumids = $_zp_db->query("SELECT `id` FROM " . $_zp_db->prefix('albums')); /* all the album IDs */
				$idsofalbums = array();
				if ($albumids) {
					while ($row = $_zp_db->fetchAssoc($albumids)) {
						$idsofalbums[] = $row['id'];
					}
					$_zp_db->freeResult($albumids);
				}
				$imageAlbums = $_zp_db->query("SELECT DISTINCT `albumid` FROM " . $_zp_db->prefix('images')); /* albumids of all the images */
				$albumidsofimages = array();
				if ($imageAlbums) {
					while ($row = $_zp_db->fetchAssoc($imageAlbums)) {
						$albumidsofimages[] = $row['albumid'];
					}
					$_zp_db->freeResult($imageAlbums);
				}
				$orphans = array_diff($albumidsofimages, $idsofalbums); /* albumids of images with no album */

				if (count($orphans) > 0) { /* delete dead images from the DB */
					$firstrow = array_pop($orphans);
					$sql = "DELETE FROM " . $_zp_db->prefix('images') . " WHERE `albumid`='" . $firstrow . "'";
					foreach ($orphans as $id) {
						$sql .= " OR `albumid`='" . $id . "'";
					}
					$_zp_db->query($sql);

					// Then go into existing albums recursively to clean them... very invasive.
					foreach ($this->getAlbums(0) as $folder) {
						$album = AlbumBase::newAlbum($folder);
						if (!$album->isDynamic()) {
							if ($this->getAlbumUseImagedate()) { // see if we can get one from an image
								$images = $album->getImages(0, 0);
								if (count($images) == 0) {
									$mtime = $album->get('mtime');
									if (!$mtime) { // in case not stored in db somehow…
										$mtime = filemtime(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($album->getName()));
									}
									$album->setDateTime(date('Y-m-d H:i:s', $mtime));
								} else {
									$image = Image::newImage($album, array_shift($images));
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

			$start = array_sum(explode(" ", microtime())); // protect against too much processing.
			if (!empty($restart)) {
				$restartwhere = ' WHERE `id`>' . $restart . ' AND `mtime`=0';
			} else {
				$restartwhere = ' WHERE `mtime`=0';
			}
			define('RECORD_LIMIT', 5);
			$sql = 'SELECT * FROM ' . $_zp_db->prefix('images') . $restartwhere . ' ORDER BY `id` LIMIT ' . (RECORD_LIMIT + 2);
			$images = $_zp_db->query($sql);
			if ($images) {
				$c = 0;
				while ($image = $_zp_db->fetchAssoc($images)) {
					$albumobj = getItemByID('albums', $image['albumid']);
					if ($albumobj->exists && file_exists($imageName = internalToFilesystem(ALBUM_FOLDER_SERVERPATH . $albumobj->name . '/' . $image['filename']))) {
						if ($image['mtime'] != $mtime = filemtime($imageName)) { // file has changed since we last saw it
							$imageobj = Image::newImage($albumobj, $image['filename']);
							$imageobj->set('mtime', $mtime);
							$imageobj->updateMetaData(); // prime the EXIF/IPTC fields
							$imageobj->updateDimensions(); // update the width/height & account for rotation
							$imageobj->save();
							zp_apply_filter('image_refresh', $imageobj);
						}
					} else {
						$sql = 'DELETE FROM ' . $_zp_db->prefix('images') . ' WHERE `id`="' . $image['id'] . '";';
						$result = $_zp_db->query($sql);
						$sql = 'DELETE FROM ' . $_zp_db->prefix('comments') . ' WHERE `type` IN (' . zp_image_types('"') . ') AND `ownerid` ="' . $image['id'] . '";';
						$result = $_zp_db->query($sql);
					}
					if (++$c >= RECORD_LIMIT) {
						return $image['id']; // avoide excessive processing
					}
				}
				$_zp_db->freeResult($images);
			}
// cleanup the tables
			$tables = $_zp_db->getTables();
			if ($tables) {
				foreach($tables as $tbl) {
					$_zp_db->query('OPTIMIZE TABLE `' . $tbl . '`');
				}
			}
		}
		return false;
	}

	function commentClean($table) {
		global $_zp_db;
		$ids = $_zp_db->query('SELECT `id` FROM ' . $_zp_db->prefix($table)); /* all the IDs */
		$idsofitems = array();
		if ($ids) {
			while ($row = $_zp_db->fetchAssoc($ids)) {
				$idsofitems[] = $row['id'];
			}
			$_zp_db->freeResult($ids);
		}
		$sql = "SELECT DISTINCT `ownerid` FROM " . $_zp_db->prefix('comments') . ' WHERE `type` =' . $_zp_db->quote($table);
		$commentOwners = $_zp_db->query($sql); /* all the comments */
		$idsofcomments = array();
		if ($commentOwners) {
			while ($row = $_zp_db->fetchAssoc($commentOwners)) {
				$idsofcomments [] = $row['ownerid'];
			}
			$_zp_db->freeResult($commentOwners);
		}
		$orphans = array_diff($idsofcomments, $idsofitems); /* owner ids of comments with no owner */

		if (count($orphans) > 0) { /* delete dead comments from the DB */
			$sql = "DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type`=" . $_zp_db->quote($table) . " AND (`ownerid`=" . implode(' OR `ownerid`=', $orphans) . ')';
			$_zp_db->query($sql);
		}
	}

	/**
	 * Cleans out the cache folder
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	static function clearCache($cachefolder = NULL) {
		if (is_null($cachefolder)) {
			$cachefolder = SERVERCACHE;
		}
		removeDir($cachefolder, true);
	}

	/**
	 * Sort the album array based on either according to the sort key.
	 * Default is to sort on the `sort_order` field.
	 *
	 * Returns an array with the albums in the desired sort order
	 *
	 * @param array $albums array of album names
	 * @param string $sortkey the sorting scheme
	 * @param string $sortdirection
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 *
	 * @author Todd Papaioannou (lucky@luckyspin.org)
	 * @since 1.0.0
	 */
	function sortAlbumArray($parentalbum, $albums, $sortkey = '`sort_order`', $sortdirection = NULL, $mine = NULL) {
		global $_zp_db;
		if (count($albums) == 0) {
			return array();
		}
		if (is_null($mine) && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$mine = true;
		}
		if (is_null($parentalbum)) {
			$albumid = ' IS NULL';
			$obj = $this;
			$viewUnpublished = $mine;
		} else {
			$albumid = '=' . $parentalbum->getID();
			$obj = $parentalbum;
			$viewUnpublished = (zp_loggedin() && $obj->albumSubRights() & (MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW));
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
		$sortkey = $_zp_db->quote($sortkey, false);
		$sql = 'SELECT * FROM ' . $_zp_db->prefix("albums") . ' WHERE `parentid`' . $albumid . ' ORDER BY ' . $sortkey . ' ' . $sortdirection;
		$result = $_zp_db->query($sql);
		$results = array();
		//	check database aganist file system
		while ($row = $_zp_db->fetchAssoc($result)) {
			$folder = $row['folder'];
			if (($key = array_search($folder, $albums)) !== false) { // album exists in filesystem
				$results[$row['folder']] = $row;
				unset($albums[$key]);
			} else { // album no longer exists
				$id = $row['id'];
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('albums') . " WHERE `id`=$id"); // delete the record
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . " WHERE `type` ='images' AND `ownerid`= '$id'"); // remove image comments
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='albums' AND `objectid`=" . $id);
				$_zp_db->query("DELETE FROM " . $_zp_db->prefix('albums') . " WHERE `id` = " . $id);
			}
		}
		$_zp_db->freeResult($result);
		foreach ($albums as $folder) { // these albums are not in the database
			$albumobj = AlbumBase::newAlbum($folder);
			if ($albumobj->exists) { // fail to instantiate?
				$results[$folder] = $albumobj->getData();
			}
		}
		//	now put the results in the right order
		$results = sortByKey($results, $sortkey, $order);
		//	albums are now in the correct order
		$albums_ordered = array();
		foreach ($results as $row) { // check for visible
			$folder = $row['folder'];
			$album = AlbumBase::newAlbum($folder);
			switch(themeObject::checkScheduledPublishing($row)) {
				case 1:
					// permanent as expired
					$album->setPublished(0);
					$album->save();
					break;
				case 2:
					// temporary as future published
					$album->setPublished(0);
					break;
			}
			if ($mine || $viewUnpublished || (!$this->isProtectedGalleryIndex() && $album->isPublic()) || $album->isVisible()) {
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
		return $this->get('hitcounter');
	}

	/**
	 * counts visits to the object
	 */
	function countHit() {
		$this->set('hitcounter', $this->get('hitcounter') + 1);
		$this->save();
	}
	
	/**
	 *  Title to be used for a parent website if Zenphoto is used as a part of it
	 * 
	 * œsince ZenphotoCMS 1.6
	 *  
	 * @param string $locale
	 * @return string
	 */
	function getParentSiteTitle($locale = NULL) {
		$text = $this->get('website_title');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Title to be used for a parent website if Zenphoto is used as a part of it
	 * 
	 * @deprecated 2.0: Use the method getParentSiteTitle() instead
	 * 
	 * @param string $locale
	 * @return string
	 */
	function getWebsiteTitle($locale = NULL) {
		deprecationNotice(gettext('Use the method getParentSiteTitle() instead'));
		return $this->getParentSiteTitle($locale);
	}

	/**
	 * Set the title to be used for a parent website if Zenphoto is used as a part of it
	 * 
	 * œsince ZenphotoCMS 1.6
	 *  
	 */
	function setParentSiteTitle($value) {
		$this->set('website_title', tagURLs($value));
	}

	/**
	 * Set the title to be used for a parent website if Zenphoto is used as a part of it
	 * @deprecated 2.0: Use the method setParentSiteTitle() instead
	 * @param type $value
	 */
	function setWebsiteTitle($value) {
		deprecationNotice(gettext('Use the method setParentSiteTitle() instead'));
		$this->setParentSiteTitle($value);
	}

	/**
	 * URL to be used for a parent website if Zenphoto is used as a part of it
	 * 
	 * œsince ZenphotoCMS 1.6
	 *  
	 * @return string
	 */
	function getParentSiteURL() {
		return $this->get('website_url');
	}

	/**
	 * The URL of the home (not Zenphoto gallery) WEBsite
	 * @deprecated 2.0: Use the method getParentSiteURL() instead
	 * 
	 * @return string
	 */
	function getWebsiteURL() {
		deprecationNotice(gettext('Use the method getParentSiteURL() instead'));
		return $this->getParentSiteURL();
	}

	/**
	 * The URL of the home (not Zenphoto gallery) WEBsite
	 * 
	 * œsince ZenphotoCMS 1.6
	 * 
	 * @return string
	 */
	function setParentSiteURL($value) {
		$this->set('website_url', $value);
	}

	/**
	 * Set URL of the home (not Zenphoto gallery) WEBsite
	 * @deprecated 2.0: Use the method setParentSiteURL() instead
	 * 
	 * @return string
	 */
	function setWebsiteURL($value) {
		deprecationNotice(gettext('Use the method setParentSiteURL() instead'));
		$this->setParentSiteURL($value);
	}

	/**
	 * Option to allow only registered users view the site
	 */
	function getSecurity() {
		return $this->get('gallery_security');
	}

	function setSecurity($value) {
		$this->set('gallery_security', $value);
	}

	/**
	 * Option to expose the user field on logon forms
	 */
	function getUserLogonField() {
		return $this->get('login_user_field');
	}

	function setUserLogonField($value) {
		$this->set('login_user_field', $value);
	}

	/**
	 * Option to update album date from date of new images
	 */
	function getAlbumUseImagedate() {
		return $this->get('album_use_new_image_date');
	}

	function setAlbumUseImagedate($value) {
		$this->set('album_use_new_image_date', $value);
	}

	/**
	 * Option to show images in the thumbnail selector
	 */
	function getThumbSelectImages() {
		return $this->get('thumb_select_images');
	}

	function setThumbSelectImages($value) {
		$this->set('thumb_select_images', $value);
	}

	/**
	 * Option to show subalbum images in the thumbnail selector
	 */
	function getSecondLevelThumbs() {
		return $this->get('multilevel_thumb_select_images');
	}

	function setSecondLevelThumbs($value) {
		$this->set('multilevel_thumb_select_images', $value);
	}

	/**
	 * Option of for gallery sessions
	 */
	function getGallerySession() {
		return $this->get('album_session');
	}

	function setGallerySession($value) {
		$this->set('album_session', $value);
	}
	
	/**
	 * Checks if the site is protected and if the gallery index is one of the unprotected pages
	 * 
	 * @since 1.6.1
	 * 
	 * @global string $_zp_gallery_page
	 * @return bool
	 */
	function isProtectedGalleryIndex() {
		global $_zp_gallery_page;
		$validindexes = array(
				'gallery',
				'index'
		);
		$current = stripSuffix($_zp_gallery_page);
		if (GALLERY_SECURITY != 'public' && !zp_loggedin()) {
			if (in_array($current, $validindexes) && in_array($current, $this->unprotected_pages)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 *
	 * Tests if a custom page is excluded from password protection
	 * @param $page
	 * @return boolean
	 */
	function isUnprotectedPage($page) {
		return (in_array($page, $this->unprotected_pages));
	}
	


	function setUnprotectedPage($page, $on) {
		if ($on) {
			array_unshift($this->unprotected_pages, $page);
			$this->unprotected_pages = array_unique($this->unprotected_pages);
		} else {
			$key = array_search($page, $this->unprotected_pages);
			if ($key !== false) {
				unset($this->unprotected_pages[$key]);
			}
		}
		$this->set('unprotected_pages', serialize($this->unprotected_pages));
	}

	function getAlbumPublish() {
		return $this->get('album_publish');
	}

	function setAlbumPublish($v) {
		$this->set('album_publish', $v);
	}

	function getImagePublish() {
		return $this->get('image_publish');
	}

	function setImagePublish($v) {
		$this->set('image_publish', $v);
	}

	/**
	 * Returns the codeblocks as an serialized array
	 *
	 * @return array
	 */
	function getCodeblock() {
		return unTagURLs($this->get("codeblock"));
	}

	/**
	 * set the codeblocks as an serialized array
	 *
	 */
	function setCodeblock($cb) {
		$this->set('codeblock', tagURLs($cb));
	}

	/**
	 * Checks if guest is loggedin for the album
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!(GALLERY_SECURITY != 'public')) {
			return false;
		}
		$hint = '';
		$pwd = $this->getPassword();
		if (!empty($pwd)) {
			return 'zpcms_auth_gallery';
		}
		return 'zp_public_access';
	}

	/**
	 *
	 * returns true if there is any protection on the gallery
	 */
	function isProtected() {
		return $this->checkforGuest() != 'zp_public_access';
	}

	function get($field) {
		if (isset($this->data[$field])) {
			return $this->data[$field];
		}
		return NULL;
	}

	function set($field, $value) {
		$this->data[$field] = $value;
	}

	function save() {
		setOption('gallery_data', serialize($this->data));
	}

	/**
	 *
	 * "Magic" function to return a string identifying the object when it is treated as a string
	 * @return string
	 */
	public function __toString() {
		return 'Gallery object';
	}

	/**
	 * registers object handlers for image varients
	 * @global array $_zp_extra_filetypes
	 * @param type $suffix
	 * @param type $objectName
	 */
	static function addImageHandler($suffix, $objectName) {
		global $_zp_extra_filetypes;
		$_zp_extra_filetypes[strtolower($suffix)] = $objectName;
	}

	/**
	 * Returns true if the file is an image
	 *
	 * @param string $filename the name of the target
	 * @return bool
	 */
	static function validImage($filename) {
		global $_zp_supported_images;
		return in_array(getSuffix($filename), $_zp_supported_images);
	}

	/**
	 * Returns true if the file is handled by an image handler plugin object
	 *
	 * @param string $filename
	 * @return bool
	 */
	static function validImageAlt($filename) {
		global $_zp_extra_filetypes;
		return @$_zp_extra_filetypes[getSuffix($filename)];
	}

	/**
	 * registers object handlers for album varients
	 * @global array $_zp_album_handlers
	 * @param type $suffix
	 * @param type $objectName
	 */
	static function addAlbumHandler($suffix, $objectName) {
		global $_zp_album_handlers;
		$_zp_album_handlers[strtolower($suffix)] = $objectName;
	}
	
	/**
	 * Gets the albums per page setting
	 * 
	 * @since 1.6
	 * 
	 * @return int
	 */
	function getAlbumsPerPage() {
		return max(1, getOption('albums_per_page'));
	}
	
	/**
	 * Gets the total album pages 
	 * 
	 * @since 1.6
	 * 
	 * @return int
	 */
	function getTotalPages() {
		return (int) ceil($this->getNumAlbums() / $this->getAlbumsPerPage());
	}
	
	/**
	 * Gets the number of images if the thumb transintion page for sharing thumbs on the last album and the first image page
	 * 
	 * @since 1.6
	 * @param obj $obj Album object (or child class object) or searchengine object
	 * @param bool $one_image_page 
	 * @return int
	 */
	static function getFirstPageImages($obj = null, $one_image_page = false) {
		$first_page_images = 0;
		if (get_class($obj) == 'SearchEngine' || is_subclass_of($obj, 'albumbase')) {
			$total_albums = $obj->getNumAlbums();
			$total_images = $obj->getNumImages();
			$albums_per_page = $obj->getAlbumsPerPage();
			$images_per_page = $obj->getImagesPerPage();
			$total_album_pages_full = $obj->getNumAlbumPages('full');
			$total_album_pages = $obj->getNumAlbumPages('total');
			if (getOption('thumb_transition') && !$one_image_page && $total_albums != 0 && $total_images != 0 && $total_album_pages_full != $total_album_pages) {
				$thumb_transition_min = max(1, getOption('thumb_transition_min'));
				$thumb_transition_max = max(1, getOption('thumb_transition_max'));
				$last_page_albums = $total_albums % $albums_per_page;
				$albums_per_page_onepercent = $albums_per_page / 100;
				$images_per_page_onepercent = $images_per_page / 100;
				if ($last_page_albums < $albums_per_page) {
					$last_page_albums_percent = $last_page_albums / $albums_per_page_onepercent;
					$last_page_albums_percent_unused = 100 - $last_page_albums_percent;
					$first_page_images = floor($images_per_page_onepercent * $last_page_albums_percent_unused);
					if ($first_page_images < $thumb_transition_min) {
						$first_page_images = $thumb_transition_min;
						$thumb_transition_minmax_active = 'min images';
					}
					if ($first_page_images > $thumb_transition_max) {
						$first_page_images = $thumb_transition_max;
						$thumb_transition_minmax_active = 'max images';
					}
					if ($first_page_images > $total_images) {
						$first_page_images = $total_images;
						$thumb_transition_minmax_active = 'overriden by total image number';
					}
				}
			}
		}
		return $first_page_images;
	}

}