<?php
/**
 *
 * Used to cache Theme pages (i.e. those pages launched by the Zenphoto index.php script.)
 *
 * Exceptions to this are the password.php and 404.php pages, any page listed in the
 * Excluded pages option, and any page whose script makes a call on the
 * static_cache_html_disable_cache() function. NOTE: this function only prevents the
 * creation of a cache image of the page being viewed. If there is already an existing
 * cached page and none of the other exclusions are in effect, the cached page will be
 * shown.
 *
 * In addition, caching does not occur for pages viewed by Zenphoto users if the user has
 * ADMIN privileges or if he is the manager of an album being viewed or whose images are
 * being viewed. Likewise, Zenpage News and Pages are not cached when viewed by the author.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */
if (!defined('OFFSET_PATH')) {
	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
	require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
}

$plugin_is_filter = 9|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Adds static HTML cache functionality to Zenphoto.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

$option_interface = 'staticCache_options';

require_once(dirname(dirname(__FILE__)).'/functions.php');

zp_register_filter('admin_utilities_buttons', 'static_cache_html_purgebutton');

$cache_path = SERVERPATH.'/'.STATIC_CACHE_FOLDER."/";
if (!file_exists($cache_path)) {
	if (!mkdir($cache_path, CHMOD_VALUE)) {
		die(gettext("Static HTML Cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
	}
}
$cachesubfolders = array("index", "albums", "images", "pages");
foreach($cachesubfolders as $cachesubfolder) {
	$cache_folder = $cache_path.$cachesubfolder.'/';
	if (!file_exists($cache_folder)) {
		if(!mkdir($cache_folder, CHMOD_VALUE)) {
			die(gettext("Static HTML Cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
		}
	}
}

if (OFFSET_PATH) {
	if (isset($_GET['action']) && $_GET['action']=='clear_html_cache' && zp_loggedin(ADMIN_RIGHTS)) {
		XSRFdefender('ClearHTMLCache');
		$_zp_HTML_cache = new staticCache();
		$_zp_HTML_cache->clearHTMLCache();
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg='.gettext('HTML cache cleared.'));
		exit();
	}
} else {	//	if the page is cached then handle it early
	$_zp_HTML_cache = new staticCache();
	$_zp_HTML_cache->startHTMLCache();
}

/**
 * Plugin option handling class
 *
 */
class staticCache_options {

	function staticCache_options() {
		setOptionDefault('static_cache_expire', 86400);
		setOptionDefault('static_cache_excludedpages', 'search.php/,contact.php/,register.php/');
	}

	function getOptionsSupported() {
		return array(	gettext('Static HTML cache expire') => array('key' => 'static_cache_expire', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("When the cache should expire in seconds. Default is 86400 seconds (1 day  = 24 hrs * 60 min * 60 sec).")),
									gettext('Excluded pages') => array('key' => 'static_cache_excludedpages', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The list of pages to excluded from cache generation. Pages that can be excluded are custom theme pages including Zenpage pages (these optionally more specific by titlelink) and the standard theme files image.php (optionally by image file name), album.php (optionally by album folder name) or index.php.<br /> If you want to exclude a page completely enter <em>page-filename.php/</em>. <br />If you want to exclude a page by a specific title, image filename, or album folder name enter <em>pagefilename.php/titlelink or image filename or album folder</em>. Separate several entries by comma.")),
		);
	}

	function handleOption($option, $currentValue) {
	}
}

class staticCache {

	var $disable = false; // manual disable caching a page
	var $pageCachePath = NULL;

	/**
	 * Checks if the current page should be excluded from caching.
	 * Pages that can be excluded are custom pages included Zenpage pages (these optionally more specific by titlelink)
	 * and the standard theme pages image.php (optionally by image file name), album.php (optionally by album folder name)
	 * or index.php
	 *
	 * @return bool
	 *
	 */
	function checkIfAllowedPage() {
		global $_zp_gallery_page, $_zp_current_image, $_zp_current_album, $_zp_current_zenpage_page,
						$_zp_current_zenpage_news, $_zp_current_admin_obj, $_zp_current_category;
		$hint = $show = '';
		if ($this->disable || zp_loggedin(ADMIN_RIGHTS)) {
			return false;	// don't cache pages the admin views!
		}
			switch ($_zp_gallery_page) {
				case "image.php": // does it really makes sense to exclude images and albums?
					$obj = $_zp_current_album;
					$title = $_zp_current_image->filename;
					break;
				case "album.php":
					$obj = $_zp_current_album;
					$title = $_zp_current_album->name;
					break;
				case 'pages.php':
					$obj = $_zp_current_zenpage_page;
					$title = $_zp_current_zenpage_page->getTitlelink();
					break;
				case 'news.php':
					if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
						$obj = $_zp_current_zenpage_news;
						$title = $obj->getTitlelink();
					} else {
						if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
							$obj = $_zp_current_category;
							$title = $obj->getTitlelink();
						} else {
							$obj = NULL;
							$title = NULL;
						}
					}
					break;
				default:
					$obj = NULL;
					if(isset($_GET['title'])) {
						$title = sanitize($_GET['title']);
					} else {
						$title = "";
					}
					break;
			}
			if ($obj) {
				if ($obj->isMyItem($obj->manage_some_rights)) {
					return false;	// don't cache manager's objects
				}
				$guestaccess = $obj->checkforGuest($hint,$show);
				if ($guestaccess && $guestaccess != 'zp_public_access') {
					return false;	// a guest is logged onto a protected item, no caching!
				}
			}
		$excludeList = array_merge(explode(",",getOption('static_cache_excludedpages')),array('404.php/','password.php/'));
		foreach($excludeList as $item) {
			$page_to_exclude = explode("/",$item);
			if ($_zp_gallery_page == trim($page_to_exclude[0])) {
				$exclude = trim($page_to_exclude[1]);
				if(empty($exclude) || $title == $exclude) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Starts the caching: Gets either an already cached file if existing or starts the output buffering.
	 *
	 * Place this function on zenphoto's root index.php file in line 75 right after the plugin loading loop
	 *
	 */
	function startHTMLCache() {
		global $_zp_gallery_page,$_zp_script_timer;
		if($this->checkIfAllowedPage()) {
			$_zp_script_timer['static cache start'] = microtime();
			$cachefilepath = $this->createCacheFilepath();
			if (!empty($cachefilepath)) {
				$cachefilepath = STATIC_CACHE_FOLDER."/".$cachefilepath;
				if(file_exists($cachefilepath) AND !isset($_POST['comment']) AND time()-filemtime($cachefilepath) < getOption("static_cache_expire")) { // don't use cache if comment is posted
					echo file_get_contents($cachefilepath); // PHP >= 4.3
					list($usec, $sec) = explode(' ', $_zp_script_timer['start']);
					$start = (float)$usec + (float)$sec;
					list($usec, $sec) = explode(' ', $_zp_script_timer['static cache start']);
					$start_cache = (float)$usec + (float)$sec;
					list($usec, $sec) = explode(' ', microtime());
					$end = (float)$usec + (float)$sec;
					echo "<!-- ".sprintf(gettext('Cached content of %3$s served by static_html_cache in %1$.4f seconds plus %2$.4f seconds unavoidable Zenphoto overhead.'),$end-$start_cache,$start_cache-$start,date('D, d M Y H:i:s',filemtime($cachefilepath)))." -->";
					db_close();
					exit();
				} else {
					$this->deleteStaticCacheFile($cachefilepath);
					if (ob_start()) {
						$this->pageCachePath = $cachefilepath;
					}
				}
			}
			unset($_zp_script_timer['static cache start']);	// leave it out of the summary page
		}
	}

	/**
	 * Ends the caching: Ends the output buffering  and writes the html cache file from the buffer
	 *
	 * Place this function on zenphoto's root index.php file in the absolute last line
	 *
	 */
	function endHTMLCache() {
		global $_zp_script_timer;
		$cachefilepath = $this->pageCachePath;
		if(!empty($cachefilepath)) {
			$pagecontent = ob_get_contents();
			ob_end_clean();
			if ($fh = fopen($cachefilepath,"w")) {
				fputs($fh, $pagecontent);
				fclose($fh);
				clearstatcache();
			}
			$this->pageCachePath = NULL;
			echo $pagecontent;
		}
	}

	/**
	 *
	 * Aborts HTML caching
	 * Used for instance, when there is a 404 error or such
	 *
	 */
	function abortHTMLCache() {
		if(!empty($this->pageCachePath)) {
			ob_end_clean();
			$this->pageCachePath = NULL;
		}
	}

	/**
	 * Creates the path and filename of the page to be cached.
	 *
	 * @return string
	 */
	function createCacheFilepath() {
		global $_zp_current_image, $_zp_current_album, $_zp_gallery_page, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
		// just make sure these are really empty
		$cachefilepath = "";
		$album = "";
		$image = "";
		$searchfields = "";
		$words = "";
		$date = "";
		$title = ""; // zenpage support
		$category = ""; // zenpage support

		// get page number
		if(isset($_GET['page'])) {
			$page = "_".sanitize($_GET['page']);
		} else {
			$page = "_1";
		}
		if(isset($_REQUEST['locale'])) {
			$locale = "_".sanitize($_REQUEST['locale']);
		} else {
			$locale = "_".getOption("locale");
		}
		switch ($_zp_gallery_page) {
			case 'index.php':
				$cachesubfolder = "index";
				$cachefilepath = "index".$page;
				break;
			case 'album.php':
			case 'image.php':
				$cachesubfolder = "albums";
				$album = $_zp_current_album->name;
				if(isset($_zp_current_image)) {
					$cachesubfolder = "images";
					$image = "-".$_zp_current_image->filename;
					$page = "";
				}
				$cachefilepath = $album.$image.$page;
				break;
			case 'pages.php':
				$cachesubfolder = "pages";
				$cachefilepath = 'page-'.$_zp_current_zenpage_page->getTitlelink();
				break;
			case 'news.php':
				$cachesubfolder = "pages";
				if(isset($_zp_current_zenpage_news)) {
					$title = "-".$_zp_current_zenpage_news->getTitlelink();
				}
				if(isset($_zp_current_category)) {
					$category = "-".$_zp_current_category->getTitlelink();
				}
				$cachefilepath = 'news'.$category.$title.$page;
				break;
			default:
				// custom pages
				$cachesubfolder = "pages";
				$custompage = $_zp_gallery_page;
				$cachefilepath = $custompage;
				break;
		}
		if (getOption('obfuscate_cache')) {
			$cachefilepath = sha1($locale.HASH_SEED.$cachefilepath).'.html';
		} else {
			// strip characters that cannot be in file names
			$cachefilepath = str_replace(array('<','>', ':','"','/','\\','|','?','*'), '_', $cachefilepath).$locale.'.html';
		}
		return $cachesubfolder."/".$cachefilepath;
	}

	/**
	 * Deletes a cache file
	 *
	 * @param string $cachefilepath Path to the cache file to be deleted
	 */
	function deleteStaticCacheFile($cachefilepath) {
		if(file_exists($cachefilepath)) {
			@unlink($cachefilepath);
		}
	}

	/**
	 * Cleans out the cache folder. (Adpated from the zenphoto image cache)
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	function clearHTMLCache($folder='') {
		$cachesubfolders = array("index", "albums","images","pages");
		foreach($cachesubfolders as $cachesubfolder) {
			$cachefolder = "../../".STATIC_CACHE_FOLDER."/".$cachesubfolder;
			if (is_dir($cachefolder)) {
				$handle = opendir($cachefolder);
				while (false !== ($filename = readdir($handle))) {
					$fullname = $cachefolder . '/' . $filename;
					if (is_dir($fullname) && !(substr($filename, 0, 1) == '.')) {
						if (($filename != '.') && ($filename != '..')) {
							$this->clearHTMLCache($fullname);
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
		//clearstatcache();
	}
} // class

/**
 * creates the Utilities button to purge the static html cache
 * @param array $buttons
 * @return array
 */
function static_cache_html_purgebutton($buttons) {
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Purge HTML cache'),
								'formname'=>'clearcache_button',
								'action'=>PLUGIN_FOLDER.'/static_html_cache.php?action=clear_html_cache',
								'icon'=>'images/edit-delete.png',
								'title'=>gettext('Clear the static HTML cache. HTML pages will be re-cached as they are viewed.'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="action" value="clear_html_cache">',
								'rights'=> ADMIN_RIGHTS,
								'XSRFTag'=>'ClearHTMLCache'
								);
	return $buttons;
}

/**
 * call to disable caching a page
 */
function static_cache_html_disable_cache() {
	global $_zp_HTML_cache;
	if(is_object($_zp_HTML_cache)) {
		$_zp_HTML_cache->disable = true;
	}
}

?>