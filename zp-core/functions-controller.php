<?php

/**
 * Common functions used in the controller for getting/setting current classes,
 * redirecting URLs, and working with the context.
 * @package core
 */
// force UTF-8 Ø

/**
 * Creates a "REWRITE" url given the query parameters that represent the link
 *
 * @param type $query
 * @return string
 */
function zpRewriteURL($query) {
	$redirectURL = '';
	if (isset($query['p'])) {
		sanitize($query);
		switch ($query['p']) {
			case 'news':
				$redirectURL = _NEWS_;
				if (isset($query['category'])) {
					$obj = new ZenpageCategory($query['category'], false);
					if (!$obj->loaded)
						return '';
					$redirectURL = $obj->getLink();
					unset($query['category']);
				} else if (isset($query['date'])) {
					$redirectURL = _NEWS_ARCHIVE_ . '/' . $query['date'];
					unset($query['date']);
				}
				if (isset($query['title'])) {
					$obj = new ZenpageNews($query['title'], false);
					if (!$obj->loaded)
						return '';
					$redirectURL = $obj->getLink();
					unset($query['title']);
				}
				break;
			case 'pages':
				$redirectURL = _PAGES_;
				if (isset($query['title'])) {
					$obj = new ZenpagePage($query['title'], false);
					if (!$obj->loaded)
						return '';
					$redirectURL = $obj->getLink();
					unset($query['title']);
				}
				break;
			case'search':
				$redirectURL = _SEARCH_;
				if (isset($query['date'])) {
					$redirectURL = _ARCHIVE_ . '/' . $query['date'];
					unset($query['date']);
				} else if (isset($query['searchfields']) && $query['searchfields'] == 'tags') {
					$redirectURL = _TAGS_;
					unset($query['searchfields']);
				}
				if (isset($query['words'])) {
					$redirectURL .= '/' . $query['words'];
					unset($query['words']);
				}
				break;
			default:
				$redirectURL = getCustomPageURL($query['p']);
				break;
		}
		unset($query['p']);
		$redirectURL = preg_replace('~^' . WEBPATH . '/~', '', $redirectURL);
		if (isset($query['page'])) {
			$redirectURL.='/' . $query['page'];
			unset($query['page']);
		}
		$q = http_build_query($query);
		if ($q)
			$redirectURL .= '?' . $q;
	} else if (isset($query['album'])) {
		if (isset($query['image'])) {
			$obj = newImage(NULL, array('folder' => $query['album'], 'filename' => $query['image']), true);
			unset($query['image']);
		} else {
			$obj = newAlbum($query['album'], NULL, true);
		}
		unset($query['album']);
		if (!$obj->exists)
			return '';
		$redirectURL = preg_replace('~^' . WEBPATH . '/~', '', $obj->getLink());
		$q = http_build_query($query);
		if ($q)
			$redirectURL .= '?' . $q;
	}
	return $redirectURL;
}

/**
 * Checks to see if the current URL is a query string url when mod_rewrite is active.
 * If so it will redirects to the rewritten URL with a 301 Moved Permanently.
 */
function fix_path_redirect() {
	if (MOD_REWRITE) {
		$request_uri = getRequestURI();
		$parts = parse_url($request_uri);
		if (isset($parts['query'])) {
			parse_str($parts['query'], $query);
			$redirectURL = zpRewriteURL($query);
			if ($redirectURL) {
				header("HTTP/1.0 301 Moved Permanently");
				header("Status: 301 Moved Permanently");
				header('Location: ' . FULLWEBPATH . '/' . $redirectURL);
				exitZP();
			}
		}
	}
}

function zp_load_page($pagenum = NULL) {
	global $_zp_page;
	if (!is_numeric($pagenum)) {
		$_zp_page = isset($_GET['page']) ? $_GET['page'] : 1;
	} else {
		$_zp_page = round($pagenum);
	}
}

/**
 * initializes the gallery.
 */
function zp_load_gallery() {
	global $_zp_current_album, $_zp_current_album_restore, $_zp_albums,
	$_zp_current_image, $_zp_current_image_restore, $_zp_images, $_zp_current_comment,
	$_zp_comments, $_zp_current_context, $_zp_current_search, $_zp_current_zenpage_new,
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
 */
function zp_load_search() {
	global $_zp_current_search;
	zp_clearCookie("zenphoto_search_params");
	if (!is_object($_zp_current_search)) {
		$_zp_current_search = new SearchEngine();
	}
	add_context(ZP_SEARCH);
	$params = $_zp_current_search->getSearchParams();
	zp_setCookie("zenphoto_search_params", $params, SEARCH_DURATION);
	return $_zp_current_search;
}

/**
 * zp_load_album - loads the album given by the folder name $folder into the
 * global context, and sets the context appropriately.
 * @param $folder the folder name of the album to load. Ex: 'testalbum', 'test/subalbum', etc.
 * @param $force_cache whether to force the use of the global object cache.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_album($folder, $force_nocache = false) {
	global $_zp_current_album, $_zp_gallery;
	$_zp_current_album = newAlbum($folder, !$force_nocache, true);
	if (!is_object($_zp_current_album) || !$_zp_current_album->exists)
		return false;
	add_context(ZP_ALBUM);
	return $_zp_current_album;
}

/**
 * zp_load_image - loads the image given by the $folder and $filename into the
 * global context, and sets the context appropriately.
 * @param $folder is the folder name of the album this image is in. Ex: 'testalbum'
 * @param $filename is the filename of the image to load.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_image($folder, $filename) {
	global $_zp_current_image, $_zp_current_album, $_zp_current_search;
	if (!is_object($_zp_current_album) || $_zp_current_album->name != $folder) {
		$album = zp_load_album($folder, true);
	} else {
		$album = $_zp_current_album;
	}
	if (!is_object($album) || !$album->exists)
		return false;
	$_zp_current_image = newImage($album, $filename, true);
	if (is_null($_zp_current_image) || !$_zp_current_image->exists) {
		return false;
	}
	add_context(ZP_IMAGE | ZP_ALBUM);
	return $_zp_current_image;
}

/**
 * Loads a zenpage pages page
 * Sets up $_zp_current_zenpage_page and returns it as the function result.
 * @param $titlelink the titlelink of a zenpage page to setup a page object directly. Used for custom
 * page scripts based on a zenpage page.
 *
 * @return object
 */
function load_zenpage_pages($titlelink) {
	global $_zp_current_zenpage_page;
	$_zp_current_zenpage_page = new ZenpagePage($titlelink);
	if ($_zp_current_zenpage_page->loaded) {
		add_context(ZP_ZENPAGE_PAGE | ZP_ZENPAGE_SINGLE);
	} else {
		$_GET['p'] = 'PAGES:' . $titlelink;
		return NULL;
	}
	return $_zp_current_zenpage_page;
}

/**
 * Loads a zenpage news article
 * Sets up $_zp_current_zenpage_news and returns it as the function result.
 *
 * @param array $request an array with one member: the key is "date", "category", or "title" and specifies
 * what you want loaded. The value is the date or title of the article wanted
 *
 * @return object
 */
function load_zenpage_news($request) {
	global $_zp_current_zenpage_news, $_zp_current_category, $_zp_post_date;
	if (isset($request['date'])) {
		add_context(ZP_ZENPAGE_NEWS_DATE);
		$_zp_post_date = zpFunctions::removeTrailingSlash(sanitize($request['date']));
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
		$sql = 'SELECT `id` FROM ' . prefix('news') . ' WHERE `titlelink`=' . db_quote($titlelink);
		$result = query_single_row($sql);
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
 *
 * @return bool
 */
function zp_load_request() {
	if ($success = zp_apply_filter('load_request', true)) { // filter allowed the load
		zp_load_page();
		if (isset($_GET['p'])) {
			$page = str_replace(array('/', '\\', '.'), '', sanitize($_GET['p']));
			switch ($page) {
				case 'search':
					return zp_load_search();
					break;
				case 'pages':
					if (extensionEnabled('zenpage')) {
						return load_zenpage_pages(sanitize(rtrim(@$_GET['title'], '/')));
					}
					break;
				case 'news':
					if (extensionEnabled('zenpage')) {
						return load_zenpage_news(sanitize($_GET));
					}
					break;
			}
		}
		//	may need image and album parameters processed
		list($album, $image) = rewrite_get_album_image('album', 'image');
		if (!empty($image)) {
			return zp_load_image($album, $image);
		} else if (!empty($album)) {
			return zp_load_album($album);
		}
	}
	return $success;
}

/**
 *
 * sets up for loading the index page
 * @return string
 */
function prepareIndexPage() {
	global $_zp_gallery_page, $_zp_script;
	handleSearchParms('index');
	$theme = setupTheme();
	$_zp_gallery_page = basename($_zp_script = THEMEFOLDER . "/$theme/index.php");
	return $theme;
}

/**
 *
 * sets up for loading an album page
 */
function prepareAlbumPage() {
	global $_zp_current_album, $_zp_gallery_page, $_zp_script;
	if ($search = $_zp_current_album->getSearchEngine()) {
		zp_setCookie("zenphoto_search_params", $search->getSearchParams(), SEARCH_DURATION);
	} else {
		handleSearchParms('album', $_zp_current_album);
	}
	$theme = setupTheme();
	$_zp_gallery_page = "album.php";
	$_zp_script = THEMEFOLDER . "/$theme/album.php";
	return $theme;
}

/**
 *
 * sets up for loading an image page
 * @return string
 */
function prepareImagePage() {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery_page, $_zp_script;
	handleSearchParms('image', $_zp_current_album, $_zp_current_image);
	$theme = setupTheme();
	$_zp_gallery_page = basename($_zp_script = THEMEFOLDER . "/$theme/image.php");
// re-initialize video dimensions if needed
	if (isImageVideo()) {
		$_zp_current_image->updateDimensions();
	}
	return $theme;
}

/**
 *
 * sets up for loading p=page pages
 * @return string
 */
function prepareCustomPage() {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery_page, $_zp_script, $_zp_current_search;
	$searchalbums = handleSearchParms('page', $_zp_current_album, $_zp_current_image);
	$album = NULL;
	$page = str_replace(array('/', '\\', '.'), '', sanitize($_GET['p']));
	if (isset($_GET['z'])) { // system page
		if ($subfolder = sanitize($_GET['z'])) {
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
						$parent = getUrAlbum(newAlbum($analbum));
						$albums[$parent->getID()] = $parent;
					}
					if (count($albums) == 1) { // there is only one parent album for the search
						$album = array_shift($albums);
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

//force license page if not acknowledged
if (!getOption('license_accepted')) {
	if (isset($_GET['z']) && $_GET['z'] != 'setup') {
// License needs agreement
		$_GET['p'] = 'license';
		$_GET['z'] = '';
	}
}
?>
