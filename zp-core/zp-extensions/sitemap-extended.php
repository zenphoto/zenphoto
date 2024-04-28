<?php
/**
 * Generates individually sitemap.org compatible XML files for use with Google and other search engines.
 * It supports albums and images as well as optionally Zenpage pages, news articles and news categories.
 * Sitemaps need to be generated via the button on the admin overview page and are cached as static
 * files in the <var>/%STATIC_CACHE_FOLDER%/sitemap/</var> folder.
 * Individual sitemaps are generated for all of the above item types as well as a sitemapindex file.
 * Album sitemaps are split into individual sitemaps per album (incl. all albums pages) and image sitemaps
 * into individual sitemaps per album.
 *
 * Based on the plugin by Jeppe Toustrup (Tenzer) http://github.com/Tenzer/zenphoto-sitemap 
 *
 * The sitemapindex file can be referenced via <var>www.yourdomain.com/zenphoto/index.php?sitemap</var> or
 * with modrewrite <var>www.yourdomain.com/zenphoto/?sitemap</var>.
 *
 * <b>IMPORTANT:</b> A multilingual sitemap requires the <var>dynamic-locale</var> plugin and either the <var>seo_locale</var> plugin or <i>language subdomains</i>.
 *
 * @author Malte Müller (acrylian), Jeppe Toustrup (Tenzer), timo, Blue Dragonfly and Francois Marechal (frankm)
 * @package zpcore\plugins\sitemapextended
 */
$plugin_is_filter = 0 | CLASS_PLUGIN;
$plugin_description = gettext('Generates sitemap.org compatible XML files for use with Google and other search engines.');
$plugin_notice = gettext('<strong>Note:</strong> The index links may not match if using the Zenpage option "news on index" that some themes provide! Also it does not "know" about "custom pages" outside Zenpage or any special custom theme setup!!');
$plugin_author = 'Malte Müller (acrylian), Jeppe Toustrup (Tenzer), timo, Blue Dragonfly and Francois Marechal (frankm)';
$plugin_category = gettext('SEO');
$option_interface = 'sitemapOptions';

zp_register_filter('admin_utilities_buttons', 'sitemap::button');

$sitemapfolder = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap';
if (!file_exists($sitemapfolder)) {
	if (!mkdir_recursive($sitemapfolder, FOLDER_MOD)) {
		die(gettext("sitemap cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
	}
}
if (getOption('sitemap_galleryindex')) {
	purgeOption('sitemap_galleryindex');
}
define('SITEMAP_CHUNK', getOption('sitemap_processing_chunk'));
define('GOOGLE_SITEMAP', getOption('sitemap_google'));
if (getOption('multi_lingual') && defined('LOCALE_TYPE')) {
	define('SITEMAP_LOCALE_TYPE', LOCALE_TYPE);
} else {
	define('SITEMAP_LOCALE_TYPE', 0);
}

/**
 * Plugin option handling class
 *
 */
class sitemapOptions {

	public $startmtime;
	public $disable = false; // manual disable caching a page

	function __construct() {
		setOptionDefault('sitemap_changefreq_index', 'daily');
		setOptionDefault('sitemap_changefreq_albums', 'daily');
		setOptionDefault('sitemap_changefreq_images', 'daily');
		setOptionDefault('sitemap_changefreq_pages', 'weekly');
		setOptionDefault('sitemap_changefreq_newsindex', 'daily');
		setOptionDefault('sitemap_changefreq_news', 'daily');
		setOptionDefault('sitemap_changefreq_newscats', 'weekly');
		setOptionDefault('sitemap_lastmod_albums', 'mtime');
		setOptionDefault('sitemap_lastmod_images', 'mtime');
		setOptionDefault('sitemap_processing_chunk', 25);
		setOptionDefault('sitemap_google', 0);
		setOptionDefault('sitemap_google_fullimage', 0);
		setOptionDefault('sitemap_includepagination_gallery', 0);
		setOptionDefault('sitemap_includepagination_album', 0);
		setOptionDefault('sitemap_includepagination_news', 0);
		setOptionDefault('sitemap_includepaginaion_category', 0);
		setOptionDefault('sitemap_include_dynamicalbums', 0);
	}

	function getOptionsSupported() {
		global $_zp_common_locale_type;
		$localdesc = '<p>' . gettext('If checked links to the alternative languages will be in the form <code><em>language</em>.domain</code> where <code><em>language</em></code> is the language code, e.g. <code><em>fr</em></code> for French.') . '</p>';
		if (!$_zp_common_locale_type) {
			$localdesc .= '<p>' . gettext('This requires that you have created the appropriate subdomains pointing to your Zenphoto installation. That is <code>fr.mydomain.com/zenphoto/</code> must point to the same location as <code>mydomain.com/zenphoto/</code>. (Some providers will automatically redirect undefined subdomains to the main domain. If your provider does this, no subdomain creation is needed.)') . '</p>';
		}
		$update_frequencies = array(
				gettext("always") => "always",
				gettext("hourly") => "hourly",
				gettext("daily") => "daily",
				gettext("weekly") => "weekly",
				gettext("monthly") => "monthly",
				gettext("yearly") => "yearly",
				gettext("never") => "never"
		);
		$options = array(
				gettext('Album date') => array(
						'key' => 'sitemap_lastmod_albums',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => array(
								gettext("date") => "date",
								gettext("mtime") => "mtime",
								gettext("last change date") => 'lastchange'),
						'desc' => gettext('Field to use for the last modification date of albums.')),
				gettext('Image date') => array(
						'key' => 'sitemap_lastmod_images',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 1,
						'selections' => array(
								gettext("date") => "date",
								gettext("mtime") => "mtime",
								gettext("last change date") => 'lastchange'),
						'desc' => gettext('Field to use for the last modification date of images.')),
				gettext('Change frequency - Zenphoto index') => array(
						'key' => 'sitemap_changefreq_index',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 2,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency - albums') => array(
						'key' => 'sitemap_changefreq_albums',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 3,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency - images') => array(
						'key' => 'sitemap_changefreq_images',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 4,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency - Zenpage pages') => array(
						'key' => 'sitemap_changefreq_pages',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 5,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency - Zenpage news index') => array(
						'key' => 'sitemap_changefreq_newsindex',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 6,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency: Zenpage news articles') => array(
						'key' => 'sitemap_changefreq_news',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 7,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Change frequency - Zenpage news categories') => array(
						'key' => 'sitemap_changefreq_newscats',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 8,
						'selections' => $update_frequencies,
						'desc' => ''),
				gettext('Enable Google image and video extension') => array(
						'key' => 'sitemap_google',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 9,
						'desc' => gettext('If checked, the XML output file will be formatted using the Google XML image and video extensions where applicable.') . '<p class="notebox">' . gettext('<strong>Note:</strong> Other search engines (Yahoo, Bing) might not be able to read your sitemap. Also the Google extensions cover only image and video formats. If you use custom file types that are not covered by Zenphoto standard plugins or types like .mp3, .txt and .html you should probably not use this or modify the plugin. Also, if your site is really huge think about if you really need this setting as the creation may cause extra workload of your server and result in timeouts') . '</p>'),
				gettext('Google image and video extension: Link full image ') => array(
						'key' => 'sitemap_google_fullimage',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 10,
						'desc' => gettext('If checked, the original full image is referenced instead of the sized images in the cache. For image formats only.')),
				gettext('Google - URL to image license') => array(
						'key' => 'sitemap_license',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 12,
						'multilingual' => false,
						'desc' => gettext('Optional. Used only if the Google extension is checked. Must be an absolute URL address of the form: http://mydomain.com/license.html')),
				gettext('Sitemap processing chunk') => array(
						'key' => 'sitemap_processing_chunk',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 13,
						'desc' => gettext('The number of albums that will be processed for each sitemap file. Lower this value if you get script timeouts when creating the files.')),
				gettext('Use subdomains') . '*' => array(
						'key' => 'dynamic_locale_subdomain',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 14,
						'disabled' => $_zp_common_locale_type,
						'desc' => $localdesc),
				
				gettext('Include pagination') => array(
						'key' => 'sitemap_includepagination',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						"checkboxes" => array(
								gettext('Gallery pagination') => 'sitemap_includepagination_gallery',
								gettext('Album pagination') => 'sitemap_includepagination_album',
								gettext('News article pagination') => 'sitemap_includepagination_news',
								gettext('News category pagination') => 'sitemap_includepaginaion_category'
						),
						"desc" => gettext("Enable if you want to include paginated pages. For SEO best practices it is recommended to have this disabled though.")),
				gettext('Include dynamic albums') . '*' => array(
						'key' => 'sitemap_include_dynamicalbums',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 14,
						'desc' => gettext("Enable if you want to include dynamic albums. Images of dynamic albums are not included in any case.")),
		);
		
		if ($_zp_common_locale_type) {
			$options['note'] = array(
					'key' => 'sitemap_locale_type',
					'type' => OPTION_TYPE_NOTE,
					'order' => 15,
					'desc' => '<p class="notebox">' . $_zp_common_locale_type . '</p>');
		} else {
			$_zp_common_locale_type = gettext('* This option may be set via the <a href="javascript:gotoName(\'sitemap-extended\');"><em>sitemap-extended</em></a> plugin options.');
			$options['note'] = array(
					'key' => 'sitemap_locale_type',
					'type' => OPTION_TYPE_NOTE,
					'order' => 16,
					'desc' => gettext('<p class="notebox">*<strong>Note:</strong> The setting of this option is shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

}

if (isset($_GET['sitemap'])) {
	$sitemappath = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/sitemapindex.xml';
	if (file_exists($sitemappath)) {
		$sitemapfile = file_get_contents($sitemappath);
		echo $sitemapfile;
	}
	exitZP();
}

/**
 * Sitemap class
 */
class sitemap {

	/**
	 * creates the Utilities button to purge the static sitemap cache
	 * @param array $buttons
	 * @return array
	 */
	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Seo'),
				'enable' => true,
				'button_text' => gettext('Sitemap tools'),
				'formname' => 'sitemap_button',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/sitemap-extended/sitemap-extended-admin.php',
				'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/cache.png',
				'title' => gettext('Generate or purge sitemap cache files.'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

	/**
	 * Simple helper function which simply outputs a string and ends it of with a new-line.
	 * @param  string $string text string
	 * @return string
	 */
	static function echonl($string) {
		return $string . "\n";
	}

	/**
	 * Generates a sitemap file.
	 *
	 * @param string $filename How the file should be named. ".xml" is appended automatically
	 * @param string $data The actual sitemap data as generated by the appropiate functions
	 */
	static function generateCacheFile($filename, $data) {
		if (!empty($data)) {
			$filepath = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/' . $filename . '.xml';
			$handler = fopen($filepath, 'w');
			fwrite($handler, $data);
			fclose($handler);
			echo '<li>' . $filename . '</li>';
		}
	}

	/**
	 * Generates the sitemap index file that points to the individual sitemaps from the content of the sitemap cache.
	 * It is always named "sitemapindex.xml"
	 */
	static function generateIndexCacheFile() {
		$data = '';
		$cachefolder = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/';
		$dirs = array_diff(scandir($cachefolder), array('.', '..', '.DS_Store', 'Thumbs.db', '.htaccess', '.svn'));
		if ($dirs) {
			$data .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap::echonl('<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach ($dirs as $dir) {
				$data .= sitemap::echonl("\t<sitemap>");
				$data .= sitemap::echonl("\t\t<loc>" . FULLWEBPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/' . $dir . '</loc>');
				$data .= sitemap::echonl("\t\t<lastmod>" . sitemap::getISO8601Date() . '</lastmod>');
				$data .= sitemap::echonl("\t</sitemap>");
			}
			$data .= sitemap::echonl('</sitemapindex>');
			$filepath = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/sitemapindex.xml';
			$handler = fopen($filepath, 'w');
			fwrite($handler, $data);
			fclose($handler);
			echo '<p>sitemapindex.xml created.</p>';
		}
	}

	/**
	 * Checks the changefreq value if entered manually and makes sure it is only one of the supported regarding sitemap.org
	 * @param  string $changefreq One of the supported changefrequence values regarding sitemap.org. Default is empty or wrong is "daily".
	 * @return string
	 */
	static function getChangefreq($changefreq = '') {
		$changefreq = sanitize($changefreq);
		switch ($changefreq) {
			case 'always':
			case 'hourly':
			case 'daily':
			case 'weekly':
			case 'monthly':
			case 'yearly':
			case 'never':
				$changefreq = $changefreq;
				break;
			default:
				$changefreq = 'daily';
				break;
		}
		return $changefreq;
	}

	/**
	 * Gets the dateformat for images and albums only.
	 * @param object $obj image or album object
	 * @param  string $option "date" or "mtime". If "mtime" is discovered to be not set, the date values is taken instead so we don't get 1970-01-10 dates
	 * @return string
	 */
	static function getDateformat($obj, $option) {
		$date = '';
		switch ($option) {
			case 'date':
			default:
				$date = $obj->getDatetime();
				break;
			case 'mtime':
				$timestamp = $obj->get('mtime');
				if ($timestamp == 0) {
					$date = $obj->getDatetime();
				} else {
					return gmdate(DateTimeInterface::ATOM, $timestamp);
				}
				break;
			case 'lastchange':
				$date = sitemap::getLastChangeDate($obj, true);
				break;
		}
		return sitemap::getISO8601Date($date);
	}

	/**
	 * Gets the limit and offset for the db queries for sitemap splitting.
	 * @param  int $items_per_sitemap Number of items per sitemap
	 * @return string
	 */
	static function getDBLimit($items_per_sitemap = 2) {
		global $_zp_sitemap_number;
		if ($_zp_sitemap_number < 1) {
			$_zp_sitemap_number = 1;
		}
		$offset = ($_zp_sitemap_number - 1) * $items_per_sitemap;
		$limit = " LIMIT " . $offset . "," . $items_per_sitemap;
		return $limit;
	}

	/*	 * TODO index links are not splitted into several sitemaps yet
	 *
	 * Gets the links to the index of a Zenphoto gallery incl. index pagination
	 *
	 * @return string
	 */

	static function getIndexLinks() {
		global $_zp_gallery, $_zp_sitemap_number;
		$data = '';
		if ($_zp_sitemap_number < 2) {
			set_context(ZP_INDEX);
			$albums_per_page = getOption('albums_per_page');
			if (getOption('custom_index_page')) {
				$galleryindex = getCustomGalleryIndexURL(1, '');
			} else {
				$galleryindex = getStandardGalleryIndexURL(1, '');
			}
			$toplevelpages = $_zp_gallery->getTotalPages();
			$data .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			$sitemap_locales = generateLanguageList();
			$changefreq = sitemap::getChangefreq(getOption('sitemap_changefreq_index'));
			// normal index/homepage we need in any case always
			$date = sitemap::getISO8601Date();
			switch (SITEMAP_LOCALE_TYPE) {
				case 1:
					foreach ($sitemap_locales as $locale) {
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . seo_locale::localePath(true, $locale) . "/</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					break;
				case 2:
					foreach ($sitemap_locales as $locale) {
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . dynamic_locale::fullHostPath($locale) . "/</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					break;
				default:
					$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . FULLWEBPATH . "/</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					break;
			}
			// the extra ones if we have a custom gallery index
			if (getOption('custom_index_page')) {
				switch (SITEMAP_LOCALE_TYPE) {
					case 1:
						foreach ($sitemap_locales as $locale) {
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . seo_locale::localePath(true, $locale) . '/' . $galleryindex . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					case 2:
						foreach ($sitemap_locales as $locale) {
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . dynamic_locale::fullHostPath($locale) . '/' . $galleryindex . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					default:
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . FULLWEBPATH . $galleryindex . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						break;
				}
			}
			// print further index pages if available
			if (getOption('sitemap_includepagination_gallery') && $toplevelpages) {
				for ($x = 2; $x <= $toplevelpages; $x++) {
					if (getOption('custom_index_page')) {
						$galleryindex = getCustomGalleryIndexURL($x, '');
					} else {
						$galleryindex = getStandardGalleryIndexURL($x, '');
					}
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach ($sitemap_locales as $locale) {
								$url = seo_locale::localePath(true, $locale) . $galleryindex;
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						case 2:
							foreach ($sitemap_locales as $locale) {
								$url = dynamic_locale::fullHostPath($locale) . $galleryindex;
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						default:
							$url = FULLWEBPATH . $galleryindex;
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							break;
					}
				}
			}
			$data .= sitemap::echonl('</urlset>'); // End off the <urlset> tag
			restore_context();
			return $data;
		} // sitemap number end
	}

	/**
	 *
	 * Enter description here ...
	 * @param object $obj the starting point
	 * @param array $albumlist the container for the results
	 */
	static function getAlbumList($obj, &$albumlist) {
		global $_zp_gallery;
		$locallist = $obj->getAlbums();
		foreach ($locallist as $folder) {
			$album = AlbumBase::newAlbum($folder);
			if ($album->isPublic() && !$album->isProtected() && (getOption('sitemap_include_dynamicalbums') || !$album->isDynamic())) {
				$albumlist[] = array('folder' => $album->name, 'date' => $album->getDateTime(), 'title' => $album->getTitle());
				if (!$album->isDynamic()) {
					sitemap::getAlbumList($album, $albumlist);
				}
			}
		}
	}

	/**
	 * gateway check for albums (no refinement of the criteria)
	 * 
	 * @deprecated Unused
	 * @param object $album
	 */
	static function passAlbums($album) {
		return true;
	}

	/**
	 * gateway function for images (screens out dynamic albums and password protected albums)
	 * 
	 * @deprecated Unused
	 * @param object $album
	 */
	static function passImages($album) {
		return !$album->isDynamic() && !$album->getPassword();
	}

	/**
	 * Places album and all of its album pages on one sitemap
	 *
	 * Gets links to all albums incl. pagination and if the Google image video extension is enabled for images using this as well.
	 * This is independent from the images fetched by getSitemapImages().
	 *
	 * NOTE: Using the Google extension is currently NOT recommended if you have a huge gallery.
	 *
	 * @return string
	 */
	static function getAlbums() {
		global $_zp_gallery, $_zp_sitemap_number;
		$data_start = $data = '';
		$sitemap_locales = generateLanguageList();
		$albumchangefreq = getOption('sitemap_changefreq_albums');
		$imagechangefreq = getOption('sitemap_changefreq_images');
		$albumlastmod = getOption('sitemap_lastmod_albums');
		$albumlastmod = sanitize($albumlastmod);
		$imagelastmod = getOption('sitemap_lastmod_images');
		$albums = array();
		sitemap::getAlbumList($_zp_gallery, $albums);
		$offset = ($_zp_sitemap_number - 1);
		$albums = array_slice($albums, $offset, SITEMAP_CHUNK);
		if (!empty($albums)) {
			$data_start .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach ($albums as $album) {
				$albumobj = AlbumBase::newAlbum($album['folder']);
				$pageCount = $albumobj->getTotalPages();
				$date = sitemap::getDateformat($albumobj, $albumlastmod);
				switch (SITEMAP_LOCALE_TYPE) {
					case 1:
						foreach ($sitemap_locales as $locale) {
							$url = seo_locale::localePath(true, $locale) . '/' . pathurlencode($albumobj->linkname) . '/';
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
							$data .= sitemap::echonl("\t</url>");
						}
						break;
					case 2:
						foreach ($sitemap_locales as $locale) {
							$url = rewrite_path(pathurlencode($albumobj->linkname) . '/', 'index.php?album=' . pathurlencode($albumobj->name), dynamic_locale::fullHostPath($locale));
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
							$data .= sitemap::echonl("\t</url>");
						}
						break;
					default:
						$url = rewrite_path(pathurlencode($albumobj->linkname) . '/', 'index.php?album=' . pathurlencode($albumobj->name), FULLWEBPATH);
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
						$data .= sitemap::echonl("\t</url>");
						break;
				}
				// print album pages if avaiable
				if (getOption('sitemap_includepagination_album') && $pageCount > 1) {
					for ($x = 2; $x <= $pageCount; $x++) {
						switch (SITEMAP_LOCALE_TYPE) {
							case 1:
								foreach ($sitemap_locales as $locale) {
									$url = seo_locale::localePath(true, $locale) . '/' . pathurlencode($albumobj->linkname) . '/' . _PAGE_ . '/' . $x . '/';
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
									$data .= sitemap::echonl("\t</url>");
								}
								break;
							case 2:
								foreach ($sitemap_locales as $locale) {
									$url = rewrite_path(pathurlencode($albumobj->linkname) . '/' . _PAGE_ . '/' . $x . '/', 'index.php?album=' . pathurlencode($albumobj->name) . '&amp;page=' . $x, dynamic_locale::fullHostPath($locale));
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
									$data .= sitemap::echonl("\t</url>");
								}
								break;
							default:
								$url = rewrite_path(pathurlencode($albumobj->linkname) . '/' . _PAGE_ . '/' . $x . '/', 'index.php?album=' . pathurlencode($albumobj->name) . '&amp;page=' . $x, FULLWEBPATH);
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $albumchangefreq . "</changefreq>\n\t\t<priority>0.8</priority>\n");
								$data .= sitemap::echonl("\t</url>");
								break;
						}
					}
				}
			}
			if (!empty($data)) {
				$data = $data_start . $data . sitemap::echonl('</urlset>'); // End off the <urlset> tag
			}
		}
		restore_context();
		return $data;
	}

	/**
	 * currently this splitts only sitemaps for albums and its images. Spliting the images itself requires a major rework...
	 *
	 * Gets links to all images for all albums (album by album)
	 *
	 * @return string
	 */
	static function getImages() {
		global $_zp_gallery, $_zp_sitemap_number;
		$data_start = $data = '';
		$sitemap_locales = generateLanguageList();
		$imagechangefreq = getOption('sitemap_changefreq_images');
		$imagelastmod = getOption('sitemap_lastmod_images');
		$limit = sitemap::getDBLimit(1);
		$albums = array();
		sitemap::getAlbumList($_zp_gallery, $albums);
		$offset = ($_zp_sitemap_number - 1);
		$albums = array_slice($albums, $offset, SITEMAP_CHUNK);
		if ($albums) {
			$data_start .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
			if (GOOGLE_SITEMAP) {
				$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">');
			} else {
				$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			}
			foreach ($albums as $album) {
				@set_time_limit(120); //	Extend script timeout to allow for gathering the images.
				$albumobj = AlbumBase::newAlbum($album['folder']);
				$images = $albumobj->getImages();
				// print plain images links if available
				if ($images) {
					foreach ($images as $image) {
						$imageobj = Image::newImage($albumobj, $image);
						if ($imageobj->isPublic() && !$albumobj->isDynamic()) {
							$ext = getSuffix($imageobj->filename);
							$date = sitemap::getDateformat($imageobj, $imagelastmod);
							switch (SITEMAP_LOCALE_TYPE) {
								case 1:
									foreach ($sitemap_locales as $locale) {
										$path = seo_locale::localePath(true, $locale) . '/' . pathurlencode($albumobj->linkname) . '/' . urlencode($imageobj->filename) . IM_SUFFIX;
										$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $path . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $imagechangefreq . "</changefreq>\n\t\t<priority>0.6</priority>\n");
										if (GOOGLE_SITEMAP) {
											$data .= sitemap::getGoogleImageVideoExtras($albumobj, $imageobj, $locale);
										}
										$data .= sitemap::echonl("</url>");
									}
									break;
								case 2:
									foreach ($sitemap_locales as $locale) {
										$path = rewrite_path(pathurlencode($albumobj->linkname) . '/' . urlencode($imageobj->filename) . IM_SUFFIX, 'index.php?album=' . pathurlencode($albumobj->name) . '&amp;image=' . urlencode($imageobj->filename), dynamic_locale::fullHostPath($locale));
										$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $path . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $imagechangefreq . "</changefreq>\n\t\t<priority>0.6</priority>\n");
										if (GOOGLE_SITEMAP) {
											$data .= sitemap::getGoogleImageVideoExtras($albumobj, $imageobj, $locale);
										}
										$data .= sitemap::echonl("</url>");
									}
									break;
								default:
									$path = rewrite_path(pathurlencode($albumobj->linkname) . '/' . urlencode($imageobj->filename) . IM_SUFFIX, 'index.php?album=' . pathurlencode($albumobj->name) . '&amp;image=' . urlencode($imageobj->filename), FULLWEBPATH);
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $path . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $imagechangefreq . "</changefreq>\n\t\t<priority>0.6</priority>\n");
									if (GOOGLE_SITEMAP) {
										$data .= sitemap::getGoogleImageVideoExtras($albumobj, $imageobj, NULL);
									}
									$data .= sitemap::echonl("</url>");
									break;
							}
						}
					}
				}
			}
			if (!empty($data)) {
				$data = $data_start . $data . sitemap::echonl('</urlset>'); // End off the <urlset> tag
			}
		}
		return $data;
	}

	/**
	 * Helper function to get the loop index if the Google video extension is enabled
	 */
	static function getGoogleLoopIndex($imageCount, $pageCount) {
		if (GOOGLE_SITEMAP) {
			$loop_index = array();
			for ($x = 1; $x <= $pageCount; $x++) {
				if ($imageCount < ($x * getOption('images_per_page'))) {
					$val = $imageCount - (($x - 1) * getOption('images_per_page'));
				} else {
					$val = getOption('images_per_page');
				}
				array_push($loop_index, $val);
			}
			return $loop_index;
		}
		return NULL;
	}

	/**
	 * Helper function to get the image/video extra entries for albums if the Google video extension is enabled
	 * @return string
	 */
	static function getGoogleImageVideoExtras($albumobj, $imageobj, $locale) {
		$data = '';
		$host = PROTOCOL . '://' . html_encode($_SERVER["HTTP_HOST"]);
		$ext = strtolower(strrchr($imageobj->filename, "."));
		$location = '';
		if ($imageobj->getLocation()) {
			$location .= $imageobj->getLocation($locale) . ', ';
		}
		if ($imageobj->getCity()) {
			$location .= $imageobj->getCity($locale) . ', ';
		}
		if ($imageobj->getState()) {
			$location .= $imageobj->getState($locale) . ', ';
		}
		if ($imageobj->getCountry()) {
			$location .= $imageobj->getCountry($locale);
		}
		$license = getOption('sitemap_license');
		if (empty($license)) {
			$license = $imageobj->getCopyrightURL();
		}
		if ($imageobj->isVideo() && in_array($ext, array('.mpg', '.mpeg', '.mp4', '.m4v', '.mov', '.wmv', '.asf', '.avi', '.ra', '.ram', '.flv', '.swf'))) { // google says it can index these so we list them even if unsupported by Zenphoto
			if (getOption('sitemap_google_fullimage')) {
				$imagelocation = pathurlencode($imageobj->getThumbImageFile($host));
			} else {
				$imagelocation =  $host . html_encode($imageobj->getCustomImage(getOption('image_size')));
			}
			$data .= sitemap::echonl("\t\t<video:video>\n\t\t\t<video:thumbnail_loc>" . $imagelocation . "</video:thumbnail_loc>\n");
			$data .= sitemap::echonl("\t\t\t<video:title>" . html_encode($imageobj->getTitle($locale)) . "</video:title>");
			if ($imageobj->getDesc()) {
				$data .= sitemap::echonl("\t\t\t<video:description>" . html_encode(getBare($imageobj->getDesc($locale))) . "</video:description>");
			}
			$data .= sitemap::echonl("\t\t\t<video:content_loc>" . $host . pathurlencode($imageobj->getFullImageURL()) . "</video:content_loc>");
			$data .= sitemap::echonl("\t\t</video:video>");
		} else if (in_array($ext, array('.jpg', '.jpeg', '.gif', '.png'))) { // this might need to be extended!
			if (getOption('sitemap_google_fullimage')) {
				$imagelocation = $host . pathurlencode($imageobj->getFullImageURL());
			} else {
				$imagelocation = $host . html_encode($imageobj->getSizedImage(getOption('image_size')));
			}
			$data .= sitemap::echonl("\t\t<image:image>\n\t\t\t<image:loc>" . html_encode($imagelocation) . "</image:loc>\n");
			// disabled for the multilingual reasons above
			$data .= sitemap::echonl("\t\t\t<image:title>" . html_encode($imageobj->getTitle($locale)) . "</image:title>");
			if ($imageobj->getDesc()) {
				$data .= sitemap::echonl("\t\t\t<image:caption>" . html_encode(getBare($imageobj->getDesc($locale))) . "</image:caption>");
			}
			if (!empty($license)) {
				$data .= sitemap::echonl("\t\t\t<image:license>" . html_encode($license) . "</image:license>");
			}
			// location is kept although the same multilingual issue applies
			if (!empty($location)) {
				$data .= sitemap::echonl("\t\t\t<image:geo_location>" . html_encode($location) . "</image:geo_location>");
			}
			$data .= sitemap::echonl("\t\t</image:image>");
		}
		return $data;
	}

	/**
	 * Gets links to all Zenpage pages
	 *
	 * @return string
	 */
	static function getZenpagePages() {
		global $_zp_zenpage, $_zp_sitemap_number;
		//not splitted into several sitemaps yet
		if ($_zp_sitemap_number == 1) {
			$data_start = $data = '';
			$limit = sitemap::getDBLimit(2);
			$sitemap_locales = generateLanguageList();
			$changefreq = getOption('sitemap_changefreq_pages');
			$pages = $_zp_zenpage->getPages(true);
			if ($pages) {
				$data_start .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
				$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
				foreach ($pages as $page) {
					$pageobj = new ZenpagePage($page['titlelink']);
					if ($pageobj->isPublic() && !$pageobj->isProtected()) {
						$date = sitemap::getLastChangeDate($pageobj, false);
						switch (SITEMAP_LOCALE_TYPE) {
							case 1:
								foreach ($sitemap_locales as $locale) {
									$url = seo_locale::localePath(true, $locale) . '/' . _PAGES_ . '/' . urlencode($page['titlelink']) . '/';
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							case 2:
								foreach ($sitemap_locales as $locale) {
									$url = rewrite_path(_PAGES_ . '/' . urlencode($page['titlelink']) . '/', 'index.php?p=pages&amp;title=' . urlencode($page['titlelink']), dynamic_locale::fullHostPath($locale));
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							default:
								$url = rewrite_path(_PAGES_ . '/' . urlencode($page['titlelink']) . '/', 'index.php?p=pages&amp;title=' . urlencode($page['titlelink']), FULLWEBPATH);
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								break;
						}
					}
				}
				if (!empty($data)) {
					$data = $data_start . $data . sitemap::echonl('</urlset>'); // End off the <urlset> tag
				}
			}
			return $data;
		}
	}

	/**
	 * Gets links to the main Zenpage news index incl. pagination
	 *
	 * @return string
	 */
	static function getZenpageNewsIndex() {
		global $_zp_zenpage, $_zp_sitemap_number;
		$newspages = $_zp_zenpage->getTotalNewsPages();
		//not splitted into several sitemaps yet
		if ($_zp_zenpage->getTotalArticles() == 0) {
			return;
		}
		if ($_zp_sitemap_number == 1) {
			$data = '';
			$data .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			$sitemap_locales = generateLanguageList();
			$changefreq = getOption('sitemap_changefreq_newsindex');
			$date = sitemap::getISO8601Date();
			switch (SITEMAP_LOCALE_TYPE) {
				case 1:
					foreach ($sitemap_locales as $locale) {
						$url = seo_locale::localePath(true, $locale) . '/' . _NEWS_ . '/';
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					Break;
				case 2:
					foreach ($sitemap_locales as $locale) {
						$url = rewrite_path(_NEWS_ . '/', 'index.php?p=news', dynamic_locale::fullHostPath($locale));
						$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					Break;
				default:
					$url = rewrite_path(_NEWS_ . '/', 'index.php?p=news', FULLWEBPATH);
					$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					Break;
			}
			// getting pages for the main news loop
			/* Not used anyway
			  if(!empty($articlesperpage)) {
			  $zenpage_articles_per_page = sanitize_numeric($articlesperpage);
			  } else {
			  $zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
			  } */

			if (getOption('sitemap_includepagination_news') && $newspages > 1) {
				for ($x = 2; $x <= $newspages; $x++) {
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach ($sitemap_locales as $locale) {
								$url = seo_locale::localePath(true, $locale) . '/' . _NEWS_ . '/' . $x . '/';
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						case 2:
							foreach ($sitemap_locales as $locale) {
								$url = rewrite_path(_NEWS_ . '/' . $x . '/', 'index.php?p=news&amp;page=' . $x, dynamic_locale::fullHostPath($locale));
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						default:
							$url = rewrite_path(_NEWS_ . '/' . $x . '/', 'index.php?p=news&amp;page=' . $x, FULLWEBPATH);
							$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							break;
					}
				}
			}
			$data .= sitemap::echonl('</urlset>'); // End off the <urlset> tag
			return $data;
		}
	}

	/**
	 * Gets to the Zenpage news articles
	 *
	 * @param  string $changefreq One of the supported changefrequence values regarding sitemap.org. Default is empty or wrong is "daily".
	 * @return string
	 */
	static function getZenpageNewsArticles() {
		global $_zp_zenpage, $_zp_sitemap_number;
		//not splitted into several sitemaps yet
		if ($_zp_sitemap_number == 1) {
			$data_start = $data = '';
			$sitemap_locales = generateLanguageList();
			$changefreq = getOption('sitemap_changefreq_news');
			$articles = $_zp_zenpage->getArticles('', 'published', true, "date", "desc");
			if ($articles) {
				$data_start .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
				$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
				foreach ($articles as $article) {
					$articleobj = new ZenpageNews($article['titlelink']);
					if ($articleobj->isPublic() && !$articleobj->isProtected()) {
						$date = sitemap::getLastChangeDate($articleobj, false);
						switch (SITEMAP_LOCALE_TYPE) {
							case 1:
								foreach ($sitemap_locales as $locale) {
									$url = seo_locale::localePath(true, $locale) . '/' . _NEWS_ . '/' . urlencode($articleobj->getName()) . '/';
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							case 2:
								foreach ($sitemap_locales as $locale) {
									$url = rewrite_path(_NEWS_ . '/' . urlencode($articleobj->getName()) . '/', 'index.php?p=news&amp;title=' . urlencode($articleobj->getName()), dynamic_locale::fullHostPath($locale));
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							default:
								$url = rewrite_path(_NEWS_ . '/' . urlencode($articleobj->getName()) . '/', 'index.php?p=news&amp;title=' . urlencode($articleobj->getName()), FULLWEBPATH);
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								break;
						}
					}
				}
				if (!empty($data)) {
					$data = $data_start . $data . sitemap::echonl('</urlset>'); // End off the <urlset> tag
				}
			}
			return $data;
		}
	}

	/**
	 * Gets links to Zenpage news categories incl. pagination
	 *
	 * @return string
	 */
	static function getZenpageNewsCategories() {
		global $_zp_zenpage, $_zp_sitemap_number;
		//TODO not splitted into several sitemaps yet
		if ($_zp_sitemap_number == 1) {
			$data_start = $data = '';
			$sitemap_locales = generateLanguageList();
			$changefreq = getOption('sitemap_changefreq_newscats');
			$newscats = $_zp_zenpage->getAllCategories();
			if ($newscats) {
				$data_start .= sitemap::echonl('<?xml version="1.0" encoding="UTF-8"?>');
				$data_start .= sitemap::echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
				foreach ($newscats as $newscat) {
					$catobj = new ZenpageCategory($newscat['titlelink']);
					$articles = $catobj->getArticles(1, true, true, 'date', 'desc');
					if ($catobj->isPublic() && !$catobj->isProtected() && count($articles) != 0) {
						$latestarticle_obj = new ZenpageNews($articles[0]['titlelink']);
						$latestarticle_date = sitemap::getLastChangeDate($latestarticle_obj, false);
						$lastchange = $catobj->getLastChange();
						if ($latestarticle_date > $lastchange) {
							$date = substr($latestarticle_date, 0, 10);
						} else if ($latestarticle_date < $lastchange) {
							$date = substr($lastchange, 0, 10);
						} else {
							$date = sitemap::getISO8601Date();
						}
						switch (SITEMAP_LOCALE_TYPE) {
							case 1:
								foreach ($sitemap_locales as $locale) {
									$url = seo_locale::localePath(true, $locale) . '/' . _CATEGORY_ . '/' . urlencode($catobj->getName()) . '/';
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							case 2:
								foreach ($sitemap_locales as $locale) {
									$url = rewrite_path(_CATEGORY_ . '/' . urlencode($catobj->getName()) . '/', 'index.php?p=news&amp;category=' . urlencode($catobj->getName()), dynamic_locale::fullHostPath($locale));
									$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								}
								break;
							default:
								$url = rewrite_path(_CATEGORY_ . '/' . urlencode($catobj->getName()) . '/', 'index.php?p=news&amp;category=' . urlencode($catobj->getName()), FULLWEBPATH);
								$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<lastmod>" . $date . "</lastmod>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								break;
						}
						// getting pages for the categories
						/*
						  if(!empty($articlesperpage)) {
						  $zenpage_articles_per_page = sanitize_numeric($articlesperpage);
						  } else {
						  $zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
						  } */
						$catpages = $catobj->getTotalNewsPages();
						if (getOption('sitemap_includepagination_category') && $catpages > 1) {
							for ($x = 2; $x <= $catpages; $x++) {
								switch (SITEMAP_LOCALE_TYPE) {
									case 1:
										foreach ($sitemap_locales as $locale) {
											$url = seo_locale::localePath(true, $locale) . '/' . _CATEGORY_ . '/' . urlencode($catobj->getName()) . '/' . $x . '/';
											$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
										}
										break;
									case 2:
										foreach ($sitemap_locales as $locale) {
											$url = rewrite_path(_CATEGORY_ . '/' . urlencode($catobj->getName()) . '/' . $x . '/', 'index.php?p=news&amp;category=' . urlencode($catobj->getName()) . '&amp;page=' . $x, dynamic_locale::fullHostPath($locale));
											$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
										}
										break;
									default:
										$url = rewrite_path(_CATEGORY_ . '/' . urlencode($catobj->getName()) . '/' . $x . '/', 'index.php?p=news&amp;category=' . urlencode($catobj->getName()) . '&amp;page=' . $x, FULLWEBPATH);
										$data .= sitemap::echonl("\t<url>\n\t\t<loc>" . $url . "</loc>\n\t\t<changefreq>" . $changefreq . "</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
										break;
								}
							}
						}
					}
				}

				if (!empty($data)) {
					$data = $data_start . $data . sitemap::echonl('</urlset>'); // End off the <urlset> tag
				}
			}
			return $data;
		}
	}

	/**
	 * Cleans out the cache folder.
	 *
	 */
	static function clearCache() {
		$cachefolder = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/';
		if (is_dir($cachefolder)) {
			$handle = opendir($cachefolder);
			while (false !== ($filename = readdir($handle))) {
				$fullname = $cachefolder . '/' . $filename;
				if (is_dir($fullname) && !(substr($filename, 0, 1) == '.')) {
					if (($filename != '.') && ($filename != '..')) {
						RSS::clearRSSCache($fullname);
						rmdir($fullname);
					}
				} else {
					if (file_exists($fullname) && !(substr($filename, 0, 1) == '.')) {
						@chmod($fullname, 0777);
						unlink($fullname);
					}
				}
			}
			closedir($handle);
		}
	}

	/**
	 * Returns an ISO-8601 compliant date/time string for the given date/time.
	 */
	static function getISO8601Date($date = '') {
		if (empty($date)) {
			$datetime = time();
		} else {
			$datetime = strtotime($date);
		}
		return date(DateTimeInterface::ATOM, $datetime);
	}

	static function printAvailableSitemaps() {
		$cachefolder = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/sitemap/';
		$dirs = array_diff(scandir($cachefolder), array('.', '..', '.DS_Store', 'Thumbs.db', '.htaccess', '.svn'));
		echo '<h2>' . gettext('Available sitemap files:') . '</h2>';
		if (!$dirs) {
			echo '<p>' . gettext('No sitemap files available.') . '</p>';
		} else {
			echo '<ol>';
			foreach ($dirs as $dir) {
				$filemtime = filemtime($cachefolder . $dir);
				$lastchange = date('Y-m-d H:i:s', $filemtime);
				?>
				<li><a target="_blank" href="<?php echo FULLWEBPATH . '/' . STATIC_CACHE_FOLDER; ?>/sitemap/<?php echo $dir; ?>"><?php echo $dir; ?></a> (<small><?php echo $lastchange; ?>)</small>
				</li>
				<?php
			}
			echo '</ol>';
		}
	}

	/**
	 * Gets the date as Y-m-d or if available last change date of $obj
	 * 
	 * @param obj $obj
	 * @param bool $fulldate True to return the full date incl. time, otherwise the date only
	 * @return string
	 */
	static function getLastChangeDate($obj, $fulldate = false) {
		$dates = array();
		$date = $obj->getDatetime();
		if (!$fulldate) {
			$date = substr($date, 0, 10);
		}
		$dates[] = $date;
		$lastchangedate = $obj->getLastchange();
		if ($lastchangedate) {
			if (!$fulldate) {
				$lastchangedate = substr($lastchangedate, 0, 10);
			}
			$dates[] = $lastchangedate;
		}
		$updateddate = '';
		if ($obj->table == 'albums') {
			$updateddate = $obj->getUpdatedDate();
			if ($updateddate) {
				if (!$fulldate) {
					$updateddate = substr($updateddate, 0, 10);
				}
				$dates[] = $updateddate;
			}
		}
		return max($dates);
	}

}
