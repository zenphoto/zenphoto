<?php

/**
 * Common functions used in the controller for getting/setting current classes,
 * redirecting URLs, and working with the context.
 * 
 * @since 1.7 Changed to class replacing functions-controller.php
 *
 * @package zpcore\classes\helpers
 */
class controller {

	/**
	 * Creates a "REWRITE" url given the query parameters that represent the link
	 * 
	 * @since 1.7 moved from functions-controller.php
	 * 
	 * @param type $query
	 * @return string
	 */
	static function rewriteURL($query) {
		$redirectURL = '';
		if (isset($query['p'])) {
			sanitize($query);
			switch ($query['p']) {
				case 'news':
					$redirectURL = _NEWS_;
					if (isset($query['category'])) {
						$obj = new ZenpageCategory($query['category'], false);
						if (!$obj->loaded) {
							return '';
						}
						$redirectURL = $obj->getLink();
						unset($query['category']);
					} else if (isset($query['date'])) {
						$redirectURL = _NEWS_ARCHIVE_ . '/' . $query['date'];
						unset($query['date']);
					}
					if (isset($query['title'])) {
						$obj = new ZenpageNews($query['title'], false);
						if (!$obj->loaded) {
							return '';
						}
						$redirectURL = $obj->getLink();
						unset($query['title']);
					}
					break;
				case 'pages':
					$redirectURL = _PAGES_;
					if (isset($query['title'])) {
						$obj = new ZenpagePage($query['title'], false);
						if (!$obj->loaded) {
							return '';
						}
						$redirectURL = $obj->getLink();
						unset($query['title']);
					}
					break;
				case'search':
					$redirectURL = _SEARCH_;
					if (isset($query['date'])) {
						$redirectURL = _ARCHIVE_ . '/' . $query['date'];
						unset($query['date']);
					} else if (isset($query['searchfields'])) {
						if ($query['searchfields'] == 'tags') {
							$redirectURL = _TAGS_;
							unset($query['searchfields']);
							if (isset($query['s'])) {
								$redirectURL .= '/' . $query['s'];
								unset($query['s']);
							}
						}
					}
				default:
					$redirectURL = getCustomPageURL($query['p']);
					break;
			}
			unset($query['p']);
			$redirectURL = preg_replace('~^' . WEBPATH . '/~', '', $redirectURL);
			if (isset($query['page'])) {
				$redirectURL .= '/' . $query['page'];
				unset($query['page']);
			}
			$q = http_build_query($query);
			if ($q) {
				$redirectURL .= '?' . $q;
			}
		} else if (isset($query['album'])) {
			if (isset($query['image'])) {
				$obj = Image::newImage(NULL, array('folder' => $query['album'], 'filename' => $query['image']), true);
				unset($query['image']);
			} else {
				$obj = Albumbase::newAlbum($query['album'], NULL, true);
				if (isset($query['page'])) {
					$redirectURL .= '/page/' . sanitize_numeric($query['page']);
					unset($query['page']);
				}
			}
			unset($query['album']);
			if (!$obj->exists) {
				return '';
			}
			$redirectURL = preg_replace('~^' . WEBPATH . '/~', '', $obj->getLink());
			$q = http_build_query($query);
			if ($q) {
				$redirectURL .= '?' . $q;
			}
		}
		return $redirectURL;
	}

	/**
	 * Checks to see if the current URL is a query string url when mod_rewrite is active.
	 * If so it will redirects to the rewritten URL with a 301 Moved Permanently.
	 * 
	 * @since 1.7 Renamed from fix_path_redirect() and moved from functions-controller.php
	 * 
	 */
	static function fixPathRedirect() {
		if (MOD_REWRITE) {
			$request_uri = getRequestURI();
			$parts = parse_url($request_uri);
			if (isset($parts['query'])) {
				parse_str($parts['query'], $query);
				$redirectURL = self::rewriteURL($query);
				if ($redirectURL) {
					redirectURL(FULLWEBPATH . '/' . $redirectURL, '301');
				}
			}
		}
	}

	/**
	 * Sets the current page number if paginatted
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @global int $_zp_page
	 * @param int $pagenum
	 */
	static function loadPage($pagenum = NULL) {
		global $_zp_page;
		if (!is_numeric($pagenum)) {
			$pagenum = isset($_GET['page']) ? intval($_GET['page']) : 1;
		}
		$_zp_page = sanitize_numeric($pagenum);
	}

	/**
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 * 
	 * @global null $_zp_current_album
	 * @global null $_zp_current_album_restore
	 * @global null $_zp_albums
	 * @global null $_zp_current_image
	 * @global null $_zp_current_image_restore
	 * @global null $_zp_images
	 * @global null $_zp_current_comment
	 * @global null $_zp_comments
	 * @global int $_zp_current_context
	 * @global SearchEngine $_zp_current_search
	 * @global null $_zp_current_zenpage_news
	 * @global null $_zp_current_zenpage_page
	 * @global null $_zp_current_category
	 * @global null $_zp_post_date
	 * @global array $_zp_pre_authorization
	 */
	static function loadGallery() {
		global $_zp_current_album, $_zp_current_album_restore, $_zp_albums,
		$_zp_current_image, $_zp_current_image_restore, $_zp_images, $_zp_current_comment,
		$_zp_comments, $_zp_current_context, $_zp_current_search, $_zp_current_zenpage_news,
		$_zp_current_zenpage_page, $_zp_current_category, $_zp_post_date, $_zp_pre_authorization;
		$_zp_current_album = NULL;
		$_zp_current_album_restore = NULL;
		$_zp_albums = NULL;
		$_zp_current_image = NULL;
		$_zp_current_image_restore = NULL;
		$_zp_images = NULL;
		$_zp_current_comment = NULL;
		$_zp_comments = NULL;
		$_zp_current_context = 0;
		$_zp_current_search = NULL;
		$_zp_current_zenpage_news = NULL;
		$_zp_current_zenpage_page = NULL;
		$_zp_current_category = NULL;
		$_zp_post_date = NULL;
		$_zp_pre_authorization = array();
		set_context(ZP_INDEX);
	}

	/**
	 * Loads the search object.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @global SearchEngine $_zp_current_search
	 * @return \SearchEngine
	 */
	static function loadSearch() {
		global $_zp_current_search;
		zp_clearCookie("zpcms_search_params");
		if (!is_object($_zp_current_search)) {
			$_zp_current_search = new SearchEngine();
		}
		add_context(ZP_SEARCH);
		$params = $_zp_current_search->getSearchParams();
		zp_setCookie("zpcms_search_params", $params, SEARCH_DURATION);
		return $_zp_current_search;
	}

	/**
	 * zp_load_album - loads the album given by the folder name $folder into the
	 * global context, and sets the context appropriately.
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 * 
	 * @param $folder the folder name of the album to load. Ex: 'testalbum', 'test/subalbum', etc.
	 * @param $force_cache whether to force the use of the global object cache.
	 * @return the loaded album object on success, or (===false) on failure.
	 */
	static function loadAlbum($folder, $force_nocache = false) {
		global $_zp_current_album;
		$_zp_current_album = Albumbase::newAlbum($folder, !$force_nocache, true);
		if (!is_object($_zp_current_album) || !$_zp_current_album->exists) {
			return false;
		}
		add_context(ZP_ALBUM);
		return $_zp_current_album;
	}

	/**
	 * loads the image given by the $folder and $filename into the
	 * global context, and sets the context appropriately.
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @param $folder is the folder name of the album this image is in. Ex: 'testalbum'
	 * @param $filename is the filename of the image to load.
	 * @return the loaded album object on success, or (===false) on failure.
	 */
	static function loadImage($folder, $filename) {
		global $_zp_current_image, $_zp_current_album, $_zp_current_search;
		if (!is_object($_zp_current_album) || $_zp_current_album->name != $folder) {
			$album = self::loadAlbum($folder, true);
		} else {
			$album = $_zp_current_album;
		}
		if (!is_object($album) || !$album->exists) 
			return false;
		$_zp_current_image = Image::newImage($album, $filename, true);
		if (is_null($_zp_current_image) || !$_zp_current_image->exists) {
			return false;
		}
		add_context(ZP_IMAGE | ZP_ALBUM);
		return $_zp_current_image;
	}

	/**
	 * Loads a zenpage pages page
	 * Sets up $_zp_current_zenpage_page and returns it as the function result.
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @param $titlelink the titlelink of a zenpage page to setup a page object directly. Used for custom
	 * page scripts based on a zenpage page.
	 *
	 * @return object
	 */
	static function loadZenpagePages($titlelink) {
		global $_zp_current_zenpage_page;
		if (!ZP_PAGES_ENABLED) {
			return false;
		}
		$_zp_current_zenpage_page = new ZenpagePage($titlelink);
		if ($_zp_current_zenpage_page->loaded) {
			add_context(ZP_ZENPAGE_PAGE | ZP_ZENPAGE_SINGLE);
		} else {
			$_GET['p'] = 'PAGES:' . $titlelink;
			return false;
		}
		return $_zp_current_zenpage_page;
	}

	/**
	 * Loads a zenpage news article
	 * Sets up $_zp_current_zenpage_news and returns it as the function result.
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @param array $request an array with one member: the key is "date", "category", or "title" and specifies
	 * what you want loaded. The value is the date or title of the article wanted
	 *
	 * @return object
	 */
	static function loadZenpageNews($request) {
		global $_zp_current_zenpage_news, $_zp_current_category, $_zp_post_date, $_zp_db;
		if (!ZP_NEWS_ENABLED) {
			return false;
		}
		if (isset($request['date'])) {
			add_context(ZP_ZENPAGE_NEWS_DATE);
			$_zp_post_date = removeTrailingSlash(sanitize($request['date']));
		}
		if (isset($request['category'])) {
			$titlelink = sanitize(rtrim($request['category'], '/'));
			$_zp_current_category = new ZenpageCategory($titlelink);
			if ($_zp_current_category->loaded) {
				add_context(ZP_ZENPAGE_NEWS_CATEGORY);
			} else {
				$_GET['p'] = 'CATEGORY:' . $titlelink;
				unset($_GET['category']);
				return false;
			}
		}
		if (isset($request['title'])) {
			$titlelink = sanitize(rtrim($request['title'], '/'));
			$sql = 'SELECT `id` FROM ' . $_zp_db->prefix('news') . ' WHERE `titlelink`=' . $_zp_db->quote($titlelink);
			$result = $_zp_db->querySingleRow($sql);
			if (is_array($result)) {
				add_context(ZP_ZENPAGE_NEWS_ARTICLE | ZP_ZENPAGE_SINGLE);
				$_zp_current_zenpage_news = new ZenpageNews($titlelink);
			} else {
				$_GET['p'] = 'NEWS:' . $titlelink;
			}
			return $_zp_current_zenpage_news;
		}
		return true;
	}

	/**
	 * Figures out what is being accessed and calls the appropriate load function
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @return bool
	 */
	static function loadRequest() {
		if ($success = filter::applyFilter('load_request', true)) { // filter allowed the load
			self::loadPage();
			if (isset($_GET['p'])) {
				$page = str_replace(array('/', '\\', '.'), '', sanitize($_GET['p']));
				switch ($page) {
					case 'search':
						return self::loadSearch();
					case 'pages':
						if (extensionEnabled('zenpage')) {
							return self::loadZenpagePages(sanitize(rtrim(strval(@$_GET['title']), '/')));
						}
						break;
					case 'news':
						if (extensionEnabled('zenpage')) {
							return self::loadZenpageNews(sanitize($_GET));
						}
						break;
				}
			}
			//	may need image and album parameters processed
			list($album, $image) = rewrite_get_album_image('album', 'image');
			if (!empty($image)) {
				return self::loadImage($album, $image);
			} else if (!empty($album)) {
				return self::loadAlbum($album);
			}
		}
		return $success;
	}

	/**
	 * Sets up for loading the index page
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @return string
	 */
	static function prepareIndexPage() {
		global $_zp_gallery_page, $_zp_script;
		handleSearchParms('index');
		$theme = setupTheme();
		$_zp_gallery_page = basename($_zp_script = THEMEFOLDER . "/$theme/index.php");
		return $theme;
	}

	/**
	 * sets up for loading an album page
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 * 
	 */
	static function prepareAlbumPage() {
		global $_zp_current_album, $_zp_gallery_page, $_zp_script;
		$theme = setupTheme();
		$_zp_gallery_page = "album.php";
		$_zp_script = THEMEFOLDER . "/$theme/album.php";
		if ($search = $_zp_current_album->getSearchEngine()) {
			zp_setCookie("zpcms_search_params", $search->getSearchParams(), SEARCH_DURATION);
		} else {
			handleSearchParms('album', $_zp_current_album);
		}
		return $theme;
	}

	/**
	 * sets up for loading an image page
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @return string
	 */
	static function prepareImagePage() {
		global $_zp_current_album, $_zp_current_image, $_zp_gallery_page, $_zp_script;
		handleSearchParms('image', $_zp_current_album, $_zp_current_image);
		$theme = setupTheme();
		$_zp_gallery_page = basename($_zp_script = THEMEFOLDER . "/$theme/image.php");
		// re-initialize video dimensions if needed
		if ($_zp_current_image->isVideo()) {
			$_zp_current_image->updateDimensions();
		}
		return $theme;
	}

	/**
	 * sets up for loading p=page pages
	 * initializes the gallery.
	 * 
	 * @since 1.7 moved from functions-controller.php
	 *
	 * @return string
	 */
	static function prepareCustomPage() {
		global $_zp_current_album, $_zp_current_image, $_zp_gallery_page, $_zp_script, $_zp_current_search;
		$searchalbums = handleSearchParms('page', $_zp_current_album, $_zp_current_image);
		$album = NULL;
		$replace = array('/', '\\', '.');
		$page = str_replace($replace, '', sanitize($_GET['p']));
		if (isset($_GET['z'])) { // system page
			if ($subfolder = str_replace($replace, '', sanitize($_GET['z']))) {
				$subfolder .= '/';
			}
			$_zp_gallery_page = $page . '.php';
			$_zp_script = ZENFOLDER . '/' . $subfolder . $page . '.php';
		} else {
			$_zp_gallery_page = $page . '.php';
			switch ($_zp_gallery_page) {
				case 'search.php':
					if (!empty($searchalbums)) { //	we are within a search of a specific album(s)
						$albums = array();
						foreach ($searchalbums as $analbum) {
							$albumobj = Albumbase::newAlbum($analbum);
							$parent = $albumobj->getUrParent();
							$albums[$parent->getID()] = $parent;
						}
						if (count($albums) == 1) { // there is only one parent album for the search
							$album = reset($albums);
						}
					}
					break;
			}
		}
		$theme = setupTheme($album);
		if (empty($_zp_script)) {
			$_zp_script = THEMEFOLDER . "/$theme/$page.php";
		}
		return $theme;
	}

	/**
	 * Handles redirections via filter hook "redirection_handler".
	 * It is meant to perform redirections of pages that have been removed or renamed.
	 * initializes the gallery.
	 * 
	 * @since 1.5.2 
	 * @since 1.7 moved from functions-controller.php
	 * 
	 */
	static function redirectionHandler() {
		if (filter::hasFilter('redirection_handler')) {
			$url = SERVER_HTTP_HOST . getRequestURI();
			/**
			 * This filter is invoked before any item context is checked and setup for theme display.
			 * Use this to internal redirected outdated or otherwise non existing by checking the url passed and returning the url to redirect to. 
			 * Note that externam URLs will not be redirected and this also includs subdomains of your main domain.
			 * 
			 * Example:
			 * 
			 * - http://example.com/oldpage -> http://example.com/newpage will work
			 * - http://example.com/oldpage -> http://subdomain.example.com/  or http://mysite.com/ will not
			 *
			 * @param string $url The current request URL (e.g. http://exaample.com/). 
			 * @return string
			 * 
			 * @package filters
			 * @subpackage themes
			 */
			$redirect_url = filter::applyFilter('redirection_handler', $url);
			if ($redirect_url != $url) {
				redirectURL($redirect_url, '301');
			}
		}
	}

	/**
	 * force license page if not acknowledged
	 * initializes the gallery.
	 * 
	 * @since 1.7 wraps former procedural code
	 *
	 */
	static function checkLicenseAccepted() {
		if (!getOption('license_accepted')) {
			if (isset($_GET['z']) && $_GET['z'] != 'setup') {
				// License needs agreement
				$_GET['p'] = 'license';
				$_GET['z'] = '';
			}
		}
	}
}
