<?php
/**
 * Generates individually sitemap.org compatible XML files for use with Google and other search engines.
 * It supports albums and images as well as optionally Zenpage pages, news articles and news categories.
 * Sitemaps need to be generated via the button on the admin overview page and then are cached as static
 * files in the /cache_html/sitemap/ folder.
 * There are individual sitemaps for all of the above item types generated as well as a sitemapindex file.
 * Album sitemaps are splitted into individual sitemaps per album (incl. all albums pages) and image sitemaps
 * into individual sitemaps per album.
 *
 * The sitemapindex file can be referenced via <var>www.yourdomain.com/zenphoto/index.php?sitemap</var> or
 * with modrewrite <var>www.yourdomain.com/zenphoto/?sitemap</var>.
 *
 * <b>IMPORTANT:</b> A multilingual sitemap requires the <var>dynamic-locale</var> plugin and either the <var>seo_locale</var> plugin or <i>language subdomains</i>.
 *
 * @author Malte Müller (acrylian) based on the plugin by Jeppe Toustrup (Tenzer) http://github.com/Tenzer/zenphoto-sitemap and on contributions by timo, Blue Dragonfly and Francois Marechal (frankm)
 * @package plugins
 * @subpackage seo
 */

$plugin_is_filter = 0|CLASS_PLUGIN;
$plugin_description = gettext('Generates sitemap.org compatible XML files for use with Google and other search engines.');
$plugin_notice = gettext('<strong>Note:</strong> The index links may not match if using the Zenpage option "news on index" that some themes provide! Also it does not "know" about "custom pages" outside Zenpage or any special custom theme setup!!');
$plugin_author = 'Malte Müller (acrylian)';

$option_interface = 'sitemap';

zp_register_filter('admin_utilities_buttons', 'sitemap::button');

$sitemapfolder = SERVERPATH.'/cache_html/sitemap';
if (!file_exists($sitemapfolder)) {
	if (!mkdir_recursive($sitemapfolder, FOLDER_MOD)) {
		die(gettext("sitemap cache folder could not be created. Please try to create it manually via FTP with chmod 0777."));
	}
}

define ('SITEMAP_CHUNK', getOption('sitemap_processing_chunk'));
define ('GOOGLE_SITEMAP', getOption('sitemap_google'));
if (getOption('multi_lingual') && defined('LOCALE_TYPE')) {
	define('SITEMAP_LOCALE_TYPE', LOCALE_TYPE);
} else {
	define('SITEMAP_LOCALE_TYPE', 0);
}

/**
 * Plugin option handling class
 *
 */
class sitemap {

	var $startmtime;
	var $disable = false; // manual disable caching a page

	function sitemap() {
		setOptionDefault('sitemap_changefreq_index', 'daily');
		setOptionDefault('sitemap_changefreq_albums', 'daily');
		setOptionDefault('sitemap_changefreq_images', 'daily');
		setOptionDefault('sitemap_changefreq_pages', 'weekly');
		setOptionDefault('sitemap_changefreq_newsindex','daily');
		setOptionDefault('sitemap_changefreq_news', 'daily');
		setOptionDefault('sitemap_changefreq_newscats', 'weekly');
		setOptionDefault('sitemap_lastmod_albums', 'mtime');
		setOptionDefault('sitemap_lastmod_images', 'mtime');
		setOptionDefault('sitemap_processing_chunk', 25);
		setOptionDefault('sitemap_galleryindex', '');
	}

	function getOptionsSupported() {
		global $_common_locale_type;
		$localdesc = '<p>'.gettext('If checked links to the alternative languages will be in the form <code><em>language</em>.domain</code> where <code><em>language</em></code> is the language code, e.g. <code><em>fr</em></code> for French.').'</p>';
		if (!$_common_locale_type) {
			$localdesc .= '<p>'.gettext('This requires that you have created the appropriate subdomains pointing to your Zenphoto installation. That is <code>fr.mydomain.com/zenphoto/</code> must point to the same location as <code>mydomain.com/zenphoto/</code>. (Some providers will automatically redirect undefined subdomains to the main domain. If your provider does this, no subdomain creation is needed.)').'</p>';
		}
		$options = array(
											gettext('Gallery index page') => array('key' => 'sitemap_galleryindex', 'type' => OPTION_TYPE_TEXTBOX,
																																'order' => 10,
																																'multilingual'=>false,
																																'desc' => gettext('If your theme does not use the theme index.php page as the gallery index, enter the name of the page here. In the Zenpage theme for example this could be gallery.php. In that case you enter "gallery". If this is not empty the index.php sitemap is not generated.')),
											gettext('Album date') => array('key' => 'sitemap_lastmod_albums', 'type' => OPTION_TYPE_SELECTOR,
																		'order' => 0,
																		'selections' => array(gettext("date")=>"date",
																		gettext("mtime")=>"mtime"),
																		'desc' => gettext('Field to use for the last modification date of albums.')),
											gettext('Image date') => array('key' => 'sitemap_lastmod_images', 'type' => OPTION_TYPE_SELECTOR,
																		'order' => 1,
																		'selections' => array(gettext("date")=>"date",
																		gettext("mtime")=>"mtime"),
																		'desc' => gettext('Field to use for the last modification date of images.')),
											gettext('Change frequency - Zenphoto index') => array('key' => 'sitemap_changefreq_index', 'type' => OPTION_TYPE_SELECTOR,
																													'order' => 2,
																													'selections' => array(gettext("always")=>"always",
																																								gettext("hourly")=>"hourly",
																																								gettext("daily")=>"daily",
																																								gettext("weekly")=>"weekly",
																																								gettext("monthly")=>"monthly",
																																								gettext("yearly")=>"yearly",
																																								gettext("never")=>"never"),
																													'desc' => ''),
											gettext('Change frequency - albums') => array('key' => 'sitemap_changefreq_albums', 'type' => OPTION_TYPE_SELECTOR,
																									'order' => 3,
																									'selections' => array(gettext("always")=>"always",
																																	gettext("hourly")=>"hourly",
																																	gettext("daily")=>"daily",
																																	gettext("weekly")=>"weekly",
																																	gettext("monthly")=>"monthly",
																																	gettext("yearly")=>"yearly",
																																	gettext("never")=>"never"),
																									'desc' => ''),
											gettext('Change frequency - images') => array('key' => 'sitemap_changefreq_images', 'type' => OPTION_TYPE_SELECTOR,
																									'order' => 4,
																									'selections' => array(gettext("always")=>"always",
																																				gettext("hourly")=>"hourly",
																																				gettext("daily")=>"daily",
																																				gettext("weekly")=>"weekly",
																																				gettext("monthly")=>"monthly",
																																				gettext("yearly")=>"yearly",
																																				gettext("never")=>"never"),
																									'desc' => ''),
											gettext('Change frequency - Zenpage pages') => array('key' => 'sitemap_changefreq_pages', 'type' => OPTION_TYPE_SELECTOR,
																													'order' => 5,
																													'selections' => array(gettext("always")=>"always",
																																								gettext("hourly")=>"hourly",
																																								gettext("daily")=>"daily",
																																								gettext("weekly")=>"weekly",
																																								gettext("monthly")=>"monthly",
																																								gettext("yearly")=>"yearly",
																																								gettext("never")=>"never"),
																													'desc' => ''),
											gettext('Change frequency - Zenpage news index') => array('key' => 'sitemap_changefreq_newsindex', 'type' => OPTION_TYPE_SELECTOR,
																															'order' => 6,
																															'selections' => array(gettext("always")=>"always",
																																										gettext("hourly")=>"hourly",
																																										gettext("daily")=>"daily",
																																										gettext("weekly")=>"weekly",
																																										gettext("monthly")=>"monthly",
																																										gettext("yearly")=>"yearly",
																																										gettext("never")=>"never"),
																															'desc' => ''),
											gettext('Change frequency: Zenpage news articles') => array('key' => 'sitemap_changefreq_news', 'type' => OPTION_TYPE_SELECTOR,
																																'order' => 7,
																																'selections' => array(gettext("always")=>"always",
																																											gettext("hourly")=>"hourly",
																																											gettext("daily")=>"daily",
																																											gettext("weekly")=>"weekly",
																																											gettext("monthly")=>"monthly",
																																											gettext("yearly")=>"yearly",
																																											gettext("never")=>"never"),
																																'desc' => ''),
											gettext('Change frequency - Zenpage news categories') => array('key' => 'sitemap_changefreq_newscats', 'type' => OPTION_TYPE_SELECTOR,
																																		'order' => 8,
																																		'selections' => array(gettext("always")=>"always",
																																													gettext("hourly")=>"hourly",
																																													gettext("daily")=>"daily",
																																													gettext("weekly")=>"weekly",
																																													gettext("monthly")=>"monthly",
																																													gettext("yearly")=>"yearly",
																																													gettext("never")=>"never"),
																																		'desc' => ''),
											gettext('Enable Google image and video extension') => array('key' => 'sitemap_google', 'type' => OPTION_TYPE_CHECKBOX,
																																'order' => 9,
																																'desc' => gettext('If checked, the XML output file will be formatted using the Google XML image and video extensions where applicable.').'<p class="notebox">'.gettext('<strong>Note:</strong> Other search engines (Yahoo, Bing) might not be able to read your sitemap. Also the Google extensions cover only image and video formats. If you use custom file types that are not covered by Zenphoto standard plugins or types like .mp3, .txt and .html you should probably not use this or modify the plugin. Also, if your site is really huge think about if you really need this setting as the creation may cause extra workload of your server and result in timeouts').'</p>'),
											gettext('Google - URL to image license') => array('key' => 'sitemap_license', 'type' => OPTION_TYPE_TEXTBOX,
																																'order' => 10,
																																'multilingual'=>true,
																																'desc' => gettext('Optional. Used only if the Google extension is checked. Must be an absolute URL address of the form: http://mydomain.com/license.html')),
											gettext('Sitemap processing chunk') => array('key' => 'sitemap_processing_chunk', 'type' => OPTION_TYPE_TEXTBOX,
																																'order' => 11,
																																'desc' => gettext('The number of albums that will be processed for each sitemap file. Lower this value if you get script timeouts when creating the files.')),
											gettext('Use subdomains').'*' => array('key' => 'dynamic_locale_subdomain', 'type' => OPTION_TYPE_CHECKBOX,
																															'order' => 12,
																															'disabled' => $_common_locale_type,
																															'desc' => $localdesc)
										);
		if ($_common_locale_type) {
			$options['note'] = array('key' => 'sitemap_locale_type', 'type' => OPTION_TYPE_NOTE,
																'order' => 13,
																'desc' => '<p class="notebox">'.$_common_locale_type.'</p>');
		} else {
			$_common_locale_type = gettext('* This option may be set via the <a href="javascript:gotoName(\'sitemap-extended\');"><em>sitemap-extended</em></a> plugin options.');
			$options['note'] = array('key' => 'sitemap_locale_type',
															'type' => OPTION_TYPE_NOTE,
															'order' => 13,
															'desc' => gettext('<p class="notebox">*<strong>Note:</strong> The setting of this option is shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

	/**
	 * creates the Utilities button to purge the static sitemap cache
	 * @param array $buttons
	 * @return array
	 */
	static function button($buttons) {
		$buttons[] = array(
									'category'=>gettext('Seo'),
									'enable'=>true,
									'button_text'=>gettext('Sitemap tools'),
									'formname'=>'sitemap_button',
									'action'=>PLUGIN_FOLDER.'/sitemap-extended/sitemap-extended-admin.php',
									'icon'=>'images/cache.png',
									'title'=>gettext('Generate or purge sitemap cache files.'),
									'alt'=>'',
									'hidden'=> '',
									'rights'=> ADMIN_RIGHTS
		);
		return $buttons;
	}

}

if(isset($_GET['sitemap'])) {
	$sitemappath = SERVERPATH.'/cache_html/sitemap/sitemapindex.xml';
	if(file_exists($sitemappath)) {
		$sitemapfile = file_get_contents($sitemappath);
		echo $sitemapfile;
	}
	exitZP();
}

/**
 * Simple helper function which simply outputs a string and ends it of with a new-line.
 * @param  string $string text string
 * @return string
 */
function sitemap_echonl($string) {
	return $string . "\n";
}


/**
 * Generates a sitemap file.
 *
 * @param string $filename How the file should be named. ".xml" is appended automatically
 * @param string $data The actual sitemap data as generated by the appropiate functions
 */
function generateSitemapCacheFile($filename,$data) {
	if(!empty($data)) {
		$filepath = SERVERPATH.'/cache_html/sitemap/'.$filename.'.xml';
		$handler = fopen($filepath,'w');
		fwrite($handler,$data);
		fclose($handler);
		echo '<li>'.$filename.'</li>';
	}
}

/**
 * Generates the sitemap index file that points to the individual sitemaps from the content of the sitemap cache.
 * It is always named "sitemapindex.xml"
 */
function generateSitemapIndexCacheFile() {
	$data = '';
	$cachefolder = SERVERPATH.'/cache_html/sitemap/';
	$dirs = array_diff(scandir($cachefolder),array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'));
	if($dirs) {
		$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
		$data .= sitemap_echonl('<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		foreach($dirs as $dir) {
			$data .= sitemap_echonl("\t<sitemap>");
			$data .= sitemap_echonl("\t\t<loc>".FULLWEBPATH.'/cache_html/sitemap/'.$dir.'</loc>');
			$data .= sitemap_echonl("\t\t<lastmod>".sitemap_getISO8601Date().'</lastmod>');
			$data .= sitemap_echonl("\t</sitemap>");
		}
		$data .= sitemap_echonl('</sitemapindex>');
		$filepath = SERVERPATH.'/cache_html/sitemap/sitemapindex.xml';
		$handler = fopen($filepath,'w');
		fwrite($handler,$data);
		fclose($handler);
		echo '<p>sitemapindex.xml created.</p>';
	}
}
/**
 * Checks the changefreq value if entered manually and makes sure it is only one of the supported regarding sitemap.org
 * @param  string $changefreq One of the supported changefrequence values regarding sitemap.org. Default is empty or wrong is "daily".
 * @return string
 */
function sitemap_getChangefreq($changefreq='') {
	$changefreq = sanitize($changefreq);
	switch($changefreq) {
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
function sitemap_getDateformat($obj,$option) {
	$date = '';
	switch($option) {
		case 'date':
		default:
			$date = $obj->getDatetime();
			break;
		case 'mtime':
			$timestamp = $obj->get('mtime');
			if($timestamp == 0) {
				$date = $obj->getDatetime();
			} else {
				return gmstrftime('%Y-%m-%dT%H:%M:%SZ', $timestamp);
				// For more streamlined but PHP5-only equivalent, remove the above line and uncomment the following:
				// return gmstrftime(DATE_ISO8601, $timestamp);
			}
			break;
	}
	return sitemap_getISO8601Date($date);
	// For more streamlined but PHP5-only equivalent, remove the above line and uncomment the following:
	// return gmstrftime(DATE_ISO8601, strtotime($date));
}

/**
 * Gets the limit and offset for the db queries for sitemap splitting.
 * @param  int $items_per_sitemap Number of items per sitemap
 * @return string
 */
function sitemap_getDBLimit($items_per_sitemap=2) {
	global $sitemap_number;
	if($sitemap_number < 1) {
		$sitemap_number = 1;
	}
	$offset = ($sitemap_number - 1) * $items_per_sitemap;
	$limit = " LIMIT ".$offset.",".$items_per_sitemap;
	return $limit;
}

/**TODO index links are not splitted into several sitemaps yet
 *
 * Gets the links to the index of a Zenphoto gallery incl. index pagination
 *
 * @return string
 */
function getSitemapIndexLinks() {
	global $_zp_gallery,$sitemap_number;
	$data = '';
	if($sitemap_number < 2) {
		set_context(ZP_INDEX);
		$albums_per_page = getOption('albums_per_page');
		if(getOption('sitemap_galleryindex')) {
			$galleryindex_mod = _PAGE_.'/'.getOption('sitemap_galleryindex');
			$galleryindex_nomod = 'index.php?p='.getOption('sitemap_galleryindex').'&amp;page=';
		} else {
			$galleryindex_mod = '';
			$galleryindex_nomod = 'index.php?page=';
		}
		$toplevelpages = getTotalPages();
		$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
		$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		$sitemap_locales = generateLanguageList();
		$changefreq = sitemap_getChangefreq(getOption('sitemap_changefreq_index'));
		// normal index/homepage we need in any case always
		switch (SITEMAP_LOCALE_TYPE) {
			case 1:
				foreach($sitemap_locales as $locale) {
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".seo_locale::localePath(true, $locale)."/</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
				}
				break;
			case 2:
				foreach($sitemap_locales as $locale) {
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".dynamic_locale::fullHostPath($locale)."/</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
				}
				break;
			default:
				$data .= sitemap_echonl("\t<url>\n\t\t<loc>".FULLWEBPATH."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
				break;
		}
		// the extra ones if we have a custom gallery index
		if(getOption('sitemap_galleryindex')) {
			switch (SITEMAP_LOCALE_TYPE) {
				case 1:
					foreach($sitemap_locales as $locale) {
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".seo_locale::localePath(true, $locale).'/'.$galleryindex_mod."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					break;
				case 2:
					foreach($sitemap_locales as $locale) {
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".rewrite_path(dynamic_locale::fullHostPath($locale).'/'.$galleryindex_mod,dynamic_locale::fullHostPath($locale).'/'.$galleryindex_nomod)."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					}
					break;
				default:
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".rewrite_path($galleryindex_mod,$galleryindex_nomod, FULLWEBPATH)."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					break;
			}
		}
		// print further index pages if available
		if($toplevelpages) {
			if(getOption('sitemap_galleryindex')) {
				$galleryindex_mod = $galleryindex_mod.'/';
			} else {
				$galleryindex_mod = $galleryindex_mod._PAGE_.'/';
			}
			for($x = 2;$x <= $toplevelpages; $x++) {
				switch (SITEMAP_LOCALE_TYPE) {
					case 1:
						foreach($sitemap_locales as $locale) {
							$url = seo_locale::localePath(true, $locale).'/'.$galleryindex_mod.$x;
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					case 2:
						foreach($sitemap_locales as $locale) {
							$url = rewrite_path($galleryindex_mod.$x,$galleryindex_nomod.$x,dynamic_locale::fullHostPath($locale));
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					default:
						$url = rewrite_path($galleryindex_mod.$x,$galleryindex_nomod.$x,FULLWEBPATH);
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						break;
				}
			}
		}
		$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
		restore_context();
		return $data;
	} // sitemap number end
}

/**
 *
 * Enter description here ...
 * @param object $obj the starting point
 * @param array $albumlist the container for the results
 * @param string $gateway name of validation function
 */
function getSitemapAlbumList($obj,&$albumlist, $gateway) {
	global $_zp_gallery;
	$locallist = $obj->getAlbums();
	foreach ($locallist as $folder) {
		$album = newAlbum($folder);
		if ($album->getShow() && $gateway($album))  {
			$albumlist[] = array('folder'=>$album->name, 'date'=>$album->getDateTime(), 'title'=>$album->getTitle());
			if(!$album->isDynamic()) {
				getSitemapAlbumList($album, $albumlist, $gateway);
			}
		}
	}
}

/**
 * gateway check for albums (no refinement of the criteria)
 * @param object $album
 */
function passAlbums($album) {
	return true;
}

/**
 * gateway function for images (screens out dynamic albums and password protected albums)
 * @param object $album
 */
function passImages($album) {
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
function getSitemapAlbums() {
	global $_zp_gallery, $sitemap_number;
	$data = '';
	$sitemap_locales = generateLanguageList();
	$albumchangefreq = getOption('sitemap_changefreq_albums');
	$imagechangefreq = getOption('sitemap_changefreq_images');
	$albumlastmod = getOption('sitemap_lastmod_albums');
	$albumlastmod = sanitize($albumlastmod);
	$imagelastmod = getOption('sitemap_lastmod_images');

	$albums = array();
	getSitemapAlbumList($_zp_gallery, $albums, 'passAlbums');
	$offset = ($sitemap_number - 1);
	$albums = array_slice($albums, $offset, SITEMAP_CHUNK);
	if(!empty($albums)) {
		$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
		$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		foreach($albums as $album) {
			$albumobj = newAlbum($album['folder']);
			set_context(ZP_ALBUM);
			makeAlbumCurrent($albumobj);
			$pageCount = getTotalPages();
			//$imageCount = getNumImages();
			//$images = $albumobj->getImages();
			$date = sitemap_getDateformat($albumobj,$albumlastmod);
			switch (SITEMAP_LOCALE_TYPE) {
				case 1:
					foreach($sitemap_locales as $locale) {
						$url = seo_locale::localePath(true, $locale).'/'.pathurlencode($albumobj->name);
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
						$data .= sitemap_echonl("\t</url>");
					}
					break;
				case 2:
					foreach($sitemap_locales as $locale) {
						$url = drewrite_path('/'.pathurlencode($albumobj->name),'?album='.pathurlencode($albumobj->name),ynamic_locale::fullHostPath($locale));
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
						$data .= sitemap_echonl("\t</url>");
					}
					break;
				default:
					$url = rewrite_path(pathurlencode($albumobj->name),'?album='.pathurlencode($albumobj->name),FULLWEBPATH);
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
					$data .= sitemap_echonl("\t</url>");
					break;
			}
			// print album pages if avaiable
			if($pageCount > 1) {
				for($x = 2;$x <= $pageCount; $x++) {
				switch (SITEMAP_LOCALE_TYPE) {
					case 1:
						foreach($sitemap_locales as $locale) {
							$url = seo_locale::localePath(true, $locale).'/'.pathurlencode($albumobj->name).'/'._PAGE_.'/'.$x;
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
							$data .= sitemap_echonl("\t</url>");
						}
						break;
					case 2:
						foreach($sitemap_locales as $locale) {
							$url = rewrite_path('/'.pathurlencode($albumobj->name).'/'._PAGE_.'/'.$x,'?album='.pathurlencode($albumobj->name).'&amp;page='.$x,dynamic_locale::fullHostPath($locale));
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
							$data .= sitemap_echonl("\t</url>");
						}
						break;
					default:
						$url = rewrite_path(pathurlencode($albumobj->name).'/'._PAGE_.'/'.$x,'?album='.pathurlencode($albumobj->name).'&amp;page='.$x,FULLWEBPATH);
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$albumchangefreq."</changefreq>\n\t\t<priority>0.8</priority>\n");
						$data .= sitemap_echonl("\t</url>");
						break;
					}
				}
			}
		}
		$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
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
function getSitemapImages() {
	global $_zp_gallery, $sitemap_number;
	$data = '';
	$sitemap_locales = generateLanguageList();
	$imagechangefreq = getOption('sitemap_changefreq_images');
	$imagelastmod = getOption('sitemap_lastmod_images');
	$limit = sitemap_getDBLimit(1);
	$albums = array();
	getSitemapAlbumList($_zp_gallery, $albums, 'passImages');
	$offset = ($sitemap_number - 1);
	$albums = array_slice($albums, $offset, SITEMAP_CHUNK);
	if($albums) {
		$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
		if(GOOGLE_SITEMAP) {
			$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">');
		} else {
			$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		}
		foreach($albums as $album) {
			set_time_limit(120);	//	Extend script timeout to allow for gathering the images.
			$albumobj = newAlbum($album['folder']);
			$images = $albumobj->getImages();
			// print plain images links if available
			if($images) {
				foreach($images as $image) {
					$imageobj = newImage($albumobj,$image);
					$ext = getSuffix($imageobj->filename);
					$date = sitemap_getDateformat($imageobj,$imagelastmod);
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach($sitemap_locales as $locale) {
								$path = seo_locale::localePath(true, $locale).'/'.pathurlencode($albumobj->name).'/'.urlencode($imageobj->filename).IM_SUFFIX;
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$path."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$imagechangefreq."</changefreq>\n\t\t<priority>0.6</priority>\n");
								if(GOOGLE_SITEMAP) {
									$data .= getSitemapGoogleImageVideoExtras($albumobj,$imageobj,$locale);
								}
								$data .= sitemap_echonl("</url>");
							}
							break;
						case 2:
							foreach($sitemap_locales as $locale) {
								$path = rewrite_path(pathurlencode($albumobj->name).'/'.urlencode($imageobj->filename).IM_SUFFIX,'?album='.pathurlencode($albumobj->name).'&amp;image='.urlencode($imageobj->filename),dynamic_locale::fullHostPath($locale));
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$path."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$imagechangefreq."</changefreq>\n\t\t<priority>0.6</priority>\n");
								if(GOOGLE_SITEMAP) {
									$data .= getSitemapGoogleImageVideoExtras($albumobj,$imageobj,$locale);
								}
								$data .= sitemap_echonl("</url>");
							}
							break;
						default:
							$path = rewrite_path(pathurlencode($albumobj->name).'/'.urlencode($imageobj->filename).IM_SUFFIX,'?album='.pathurlencode($albumobj->name).'&amp;image='.urlencode($imageobj->filename),FULLWEBPATH);
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$path."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$imagechangefreq."</changefreq>\n\t\t<priority>0.6</priority>\n");
							if(GOOGLE_SITEMAP) {
								$data .= getSitemapGoogleImageVideoExtras($albumobj,$imageobj,NULL);
							}
							$data .= sitemap_echonl("</url>");
							break;
					}
				}
			}
		}
		$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
	}
	return $data;
}

/**
 * Helper function to get the loop index if the Google video extension is enabled
 */
function getSitemapGoogleLoopIndex($imageCount,$pageCount) {
	if(GOOGLE_SITEMAP) {
		$loop_index = array();
		for ($x = 1; $x <= $pageCount; $x++) {
			if ($imageCount < ($x*getOption('images_per_page')) ) {
				$val = $imageCount - (($x-1)*getOption('images_per_page'));
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
function getSitemapGoogleImageVideoExtras($albumobj,$imageobj,$locale) {
	$data = '';
	$host = SERVER_PROTOCOL.'://'.html_encode($_SERVER["HTTP_HOST"]);
	$ext = strtolower(strrchr($imageobj->filename, "."));
	$location = '';
	if ($imageobj->getLocation()) { $location .= $imageobj->getLocation($locale) . ', ' ; }
	if ($imageobj->getCity()) { $location .= $imageobj->getCity($locale) . ', ' ; }
	if ($imageobj->getState()) { $location .= $imageobj->getState($locale) .', ' ; }
	if ($imageobj->getCountry()) { $location .= $imageobj->getCountry($locale); }
	$license = get_language_string(getOption('sitemap_license'),$locale);
	if(isImageVideo($imageobj) && in_array($ext,array('.mpg','.mpeg','.mp4','.m4v','.mov','.wmv','.asf','.avi','.ra','.ram','.flv','.swf'))) { // google says it can index these so we list them even if unsupported by Zenphoto
		$data .= sitemap_echonl("\t\t<video:video>\n\t\t\t<video:thumbnail_loc>".$host.html_encode($imageobj->getThumb())."</video:thumbnail_loc>\n");
		$data .= sitemap_echonl("\t\t\t<video:title>".html_encode($imageobj->getTitle($locale))."</video:title>");
		if ($imageobj->getDesc()) {
			$data .= sitemap_echonl("\t\t\t<video:description>".html_encode(strip_tags($imageobj->getDesc($locale)))."</video:description>");
		}
		$data .= sitemap_echonl("\t\t\t<video:content_loc>".$host.pathurlencode($imageobj->getFullImageURL())."</video:content_loc>");
		$data .= sitemap_echonl("\t\t</video:video>");
	} else if (in_array($ext,array('.jpg','.jpeg','.gif','.png'))) { // this might need to be extended!
		$data .= sitemap_echonl("\t\t<image:image>\n\t\t\t<image:loc>".$host.html_encode($imageobj->getSizedImage(getOption('image_size')))."</image:loc>\n");
		// disabled for the multilingual reasons above
		$data .= sitemap_echonl("\t\t\t<image:title>".html_encode($imageobj->getTitle($locale))."</image:title>");
		if ($imageobj->getDesc()) {
			$data .= sitemap_echonl("\t\t\t<image:caption>".html_encode(strip_tags($imageobj->getDesc($locale)))."</image:caption>");
		}
		if(!empty($license)) {
			$data .= sitemap_echonl("\t\t\t<image:license>".$license."</image:license>");
		}
		// location is kept although the same multilingual issue applies
		if(!empty($location)) {
			$data .= sitemap_echonl("\t\t\t<image:geo_location>".$location."</image:geo_location>");
		}
		$data .= sitemap_echonl("\t\t</image:image>");
	}
	return $data;
}

/**
 * Gets links to all Zenpage pages
 *
 * @return string
 */
function getSitemapZenpagePages() {
	global $_zp_zenpage, $sitemap_number;
	//not splitted into several sitemaps yet
	if($sitemap_number == 1) {
		$data = '';
		$limit = sitemap_getDBLimit(2);
		$sitemap_locales = generateLanguageList();
		$changefreq = getOption('sitemap_changefreq_pages');
		$pages = $_zp_zenpage->getPages(true);
		if($pages) {
			$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach($pages as $page) {
				$pageobj = new ZenpagePage($page['titlelink']);
				$date = substr($pageobj->getDatetime(),0,10);
				$lastchange = '';
				if(!is_null($pageobj->getLastchange())) $lastchange = substr($pageobj->getLastchange(),0,10);
				if($date > $lastchange && !empty($lastchangedate)) $date = $lastchange;
				if(!$pageobj->isProtected()) {
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach($sitemap_locales as $locale) {
								$url = seo_locale::localePath(true, $locale).'/'._PAGES_.'/'.urlencode($page['titlelink']);
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						case 2:
							foreach($sitemap_locales as $locale) {
								$url = rewrite_path($locale.'/'._PAGES_.'/'.urlencode($page['titlelink']),'?p=pages&amp;title='.urlencode($page['titlelink']),FULLWEBPATH);
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						default:
							$url = rewrite_path(_PAGES_.'/'.urlencode($page['titlelink']),'?p=pages&amp;title='.urlencode($page['titlelink']),FULLWEBPATH);
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							break;
					}
				}
			}
			$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
		}
		return $data;
	}
}

/**
 * Gets links to the main Zenpage news index incl. pagination
 *
 * @return string
 */
function getSitemapZenpageNewsIndex() {
	global $_zp_zenpage,$sitemap_number;
	//not splitted into several sitemaps yet
	if($sitemap_number == 1) {
		$data = '';
		$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
		$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
		$sitemap_locales = generateLanguageList();
		$changefreq = getOption('sitemap_changefreq_newsindex');
		switch (SITEMAP_LOCALE_TYPE) {
			case 1:
				foreach($sitemap_locales as $locale) {
					$url = seo_locale::localePath(true, $locale).'/news/1';
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
				}
				Break;
			case 2:
				foreach($sitemap_locales as $locale) {
					$url = rewrite_path('news/1','?p=news&amp;page=1',dynamic_locale::fullHostPath($locale));
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
				}
				Break;
				default:
				$url = rewrite_path('news/1','?p=news&amp;page=1',FULLWEBPATH);
			$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
			Break;
		}
		// getting pages for the main news loop
		/* Not used anyway
		if(!empty($articlesperpage)) {
		$zenpage_articles_per_page = sanitize_numeric($articlesperpage);
		} else {
			$zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
		} */
		$zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
		$newspages = ceil($_zp_zenpage->getTotalArticles() / $zenpage_articles_per_page);
		if($newspages > 1) {
			for($x = 2;$x <= $newspages; $x++) {
				switch (SITEMAP_LOCALE_TYPE) {
					case 1:
						foreach($sitemap_locales as $locale) {
							$url = seo_locale::localePath(true, $locale).'/news/'.$x;
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					case 2:
						foreach($sitemap_locales as $locale) {
							$url = rewrite_path('news/'.$x,'?p=news&amp;page='.$x,dynamic_locale::fullHostPath($locale));
							$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						}
						break;
					default:
						$url =rewrite_path('news/'.$x,'?p=news&amp;page='.$x, FULLWEBPATH);
					$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".sitemap_getISO8601Date()."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
					break;
				}
			}
		}
		$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
		return $data;
	}
}

/**
 * Gets to the Zenpage news articles
 *
 * @param  string $changefreq One of the supported changefrequence values regarding sitemap.org. Default is empty or wrong is "daily".
 * @return string
 */
function getSitemapZenpageNewsArticles() {
	global $_zp_zenpage,$sitemap_number;
	//not splitted into several sitemaps yet
	if($sitemap_number == 1) {
		$data = '';
		$sitemap_locales = generateLanguageList();
		$changefreq = getOption('sitemap_changefreq_news');
		$articles = $_zp_zenpage->getArticles('','published',true,"date","desc");
		if($articles) {
			$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach($articles as $article) {
				$articleobj = new ZenpageNews($article['titlelink']);
				$date = substr($articleobj->getDatetime(),0,10);
				$lastchange = '';
				if(!is_null($articleobj->getLastchange())) $lastchange = substr($articleobj->getLastchange(),0,10);
				if($date > $lastchange && !empty($lastchangedate)) $date = $lastchange;
				if(!$articleobj->inProtectedCategory()) {
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach($sitemap_locales as $locale) {
								$url = seo_locale::localePath(true, $locale).'/news/'.urlencode($articleobj->getTitlelink());
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						case 2:
							foreach($sitemap_locales as $locale) {
								$url = rewrite_path('news/'.urlencode($articleobj->getTitlelink()),'?p=news&amp;title=' . urlencode($articleobj->getTitlelink()),dynamic_locale::fullHostPath($locale));
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						default:
							$url = rewrite_path('news/'.urlencode($articleobj->getTitlelink()),'?p=news&amp;title=' . urlencode($articleobj->getTitlelink()),FULLWEBPATH);
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<lastmod>".$date."</lastmod>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						break;
					}
				}
			}
			$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
		}
		return $data;
	}
}

/**
 * Gets links to Zenpage news categories incl. pagination
 *
 * @return string
 */
function getSitemapZenpageNewsCategories() {
	global $_zp_zenpage,$sitemap_number;
	//TODO not splitted into several sitemaps yet
	if($sitemap_number == 1) {
		$data = '';
		$sitemap_locales = generateLanguageList();
		$changefreq = getOption('sitemap_changefreq_newscats');
		$newscats = $_zp_zenpage->getAllCategories();
		if($newscats) {
			$data .= sitemap_echonl('<?xml version="1.0" encoding="UTF-8"?>');
			$data .= sitemap_echonl('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			foreach($newscats as $newscat) {
				$catobj = new ZenpageCategory($newscat['titlelink']);
				if(!$catobj->isProtected()) {
					switch (SITEMAP_LOCALE_TYPE) {
						case 1:
							foreach($sitemap_locales as $locale) {
								$url = seo_locale::localePath(true, $locale).'/news/category/'.urlencode($catobj->getTitlelink()).'/1';
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						case 2:
							foreach($sitemap_locales as $locale) {
								$url = rewrite_path('news/category/'.urlencode($catobj->getTitlelink()).'/1','?p=news&amp;category=' . urlencode($catobj->getTitlelink()).'&amp;page=1',dynamic_locale::fullHostPath($locale));
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
							}
							break;
						default:
							$url = rewrite_path('news/category/'.urlencode($catobj->getTitlelink()).'/1','?p=news&amp;category=' . urlencode($catobj->getTitlelink()).'&amp;page=1',FULLWEBPATH);
						$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
						break;
					}
					// getting pages for the categories
					/*
					if(!empty($articlesperpage)) {
						$zenpage_articles_per_page = sanitize_numeric($articlesperpage);
					} else {
						$zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
					} */
					$zenpage_articles_per_page = ZP_ARTICLES_PER_PAGE;
					$articlecount = count($catobj->getArticles());
					$catpages = ceil($articlecount / $zenpage_articles_per_page);
					if($catpages > 1) {
						for($x = 2;$x <= $catpages ; $x++) {
							switch (SITEMAP_LOCALE_TYPE) {
								case 1:
									foreach($sitemap_locales as $locale) {
										$url = seo_locale::localePath(true, $locale).'/news/category/'.urlencode($catobj->getTitlelink()).'/'.$x;
										$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
									}
									break;
								case 2:
									foreach($sitemap_locales as $locale) {
										$url = rewrite_path('news/category/'.urlencode($catobj->getTitlelink()).'/'.$x,'?p=news&amp;category=' . urlencode($catobj->getTitlelink()).'&amp;page='.$x,dynamic_locale::fullHostPath($locale));
										$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
									}
									break;
								default:
									$url = rewrite_path('news/category/'.urlencode($catobj->getTitlelink()).'/'.$x,'?p=news&amp;category=' . urlencode($catobj->getTitlelink()).'&amp;page='.$x,FULLWEBPATH);
								$data .= sitemap_echonl("\t<url>\n\t\t<loc>".$url."</loc>\n\t\t<changefreq>".$changefreq."</changefreq>\n\t\t<priority>0.9</priority>\n\t</url>");
								break;
							}
						}
					}
				}
			}
			$data .= sitemap_echonl('</urlset>');// End off the <urlset> tag
		}
		return $data;
	}
}

/**
 * Cleans out the cache folder.
 *
 */
function clearSitemapCache() {
	$cachefolder = SERVERPATH."/cache_html/sitemap/";
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
					@chmod($fullname, 0666);
					unlink($fullname);
				}
			}

		}
		closedir($handle);
	}
}

/**
 * Returns an ISO-8601 compliant date/time string for the given date/time.
 * While PHP5 can use the date format constant DATE_ISO8601, this function is designed to allow PHP4 use as well.
 * Eventually it can be deprecated, by:
 *   1. Replacing parameterless references to this function with date(DATE_ISO8601)
 *   2. Replacing references to this function in sitemap_getDateformat as documented there
 *
 */
function sitemap_getISO8601Date($date='') {
	if (empty($date)) {
		return gmstrftime('%Y-%m-%dT%H:%M:%SZ');
	} else {
		return gmstrftime('%Y-%m-%dT%H:%M:%SZ', strtotime($date));
	}
}
