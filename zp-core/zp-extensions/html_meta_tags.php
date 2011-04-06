<?php
/**
 * A plugin to print the most common html meta tags to the head of your site's pages using
 * general existing Zenphoto info like gallery description, tags or Zenpage news categories.
 *
 * Just enable the plugin and the meta data will be inserted into your <head> section.
 * You can choose on the plugin's admin option what tags you want to be printed.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */

$plugin_description = gettext("A plugin to print the most common HTML meta tags to the head of your site's pages. Tags are selected from existing Zenphoto info such as gallery description, tags, or Zenpage news categories.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.4.1';
$option_interface = 'htmlmetatags';

if (in_context(ZP_INDEX)) {
	zp_register_filter('theme_head','getHTMLMetaData'); // insert the meta tags into the <head></head> if on a theme page.
}

class htmlmetatags {

	function htmlmetatags() {
		setOptionDefault('htmlmeta_cache_control', 'no-cache');
		setOptionDefault('htmlmeta_pragma', 'no-cache');
		setOptionDefault('htmlmeta_robots', 'index');
		setOptionDefault('htmlmeta_revisit_after', '10 Days');
		setOptionDefault('htmlmeta_expires', '43200');
		setOptionDefault('htmlmeta_tags', '');

		// the html meta tag selector prechecked ones
		setOptionDefault('htmlmeta_http-equiv-language', '1');
		setOptionDefault('htmlmeta_name-language', '1');
		setOptionDefault('htmlmeta_htmlmeta_tags', '1');
		setOptionDefault('htmlmeta_http-equiv-cache-control', '1');
		setOptionDefault('htmlmeta_http-equiv-pragma', '1');
		setOptionDefault('htmlmeta_http-equiv-content-style-type','1');
		setOptionDefault('htmlmeta_name-title', '1');
		setOptionDefault('htmlmeta_name=keywords', '1');
		setOptionDefault('htmlmeta_name-description', '1');
		setOptionDefault('htmlmeta_name-robot', '1');
		setOptionDefault('htmlmeta_name-publisher', '1');
		setOptionDefault('htmlmeta_name-creator', '1');
		setOptionDefault('htmlmeta_name-author', '1');
		setOptionDefault('htmlmeta_name-copyright', '1');
		setOptionDefault('htmlmeta_name-generator', '1');
		setOptionDefault('htmlmeta_name-revisit-after', '1');
		setOptionDefault('htmlmeta_name-expires', '1');
		setOptionDefault('htmlmeta_name-generator', '1');
		setOptionDefault('htmlmeta_name-date', '1');
	}

 // Gettext calls are removed because some terms like "noindex" are fixed terms that should not be translated so user know what setting they make.
	function getOptionsSupported() {
		return array(gettext('Cache control') => array('key' => 'htmlmeta_cache_control', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array('no-cache' => "no-cache",'public' => "public", 'private' => "private",'no-store' => "no-store"),
										'desc' => gettext("If the browser cache should be used.")),
		gettext('Pragma') => array('key' => 'htmlmeta_pragma', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array('no-cache' => "no-cache",'cache' => "cache"),
										'desc' => gettext("If the pages should be allowed to be cached on proxy servers.")),
		gettext('Robots') => array('key' => 'htmlmeta_robots', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array('noindex' => "noindex", 'index' => "index",	'nofollow' => "nofollow", 'noindex,nofollow' => "noindex,nofollow",'noindex,follow' => "noindex,follow", 'index,nofollow' => "index,nofollow",	'none' => "none"),
										'desc' => gettext("If and how robots are allowed to visit the site. Default is 'index'. Note that you also should use a robot.txt file.")),
		gettext('Revisit after') => array('key' => 'htmlmeta_revisit_after', 'type' => OPTION_TYPE_TEXTBOX,
									'desc' => gettext("Request the crawler to revisit the page after x days.")),
		gettext('Expires') => array('key' => 'htmlmeta_expires', 'type' => OPTION_TYPE_TEXTBOX,
									'desc' => gettext("When the page should be loaded directly from the server and not from any cache. You can either set a date/time in international date format <em>Sat, 15 Dec 2001 12:00:00 GMT (example)</em> or a number. A number then means seconds, the default value <em>43200</em> means 12 hours.")),
		gettext('HTML meta tags') => array('key' => 'htmlmeta_tags', 'type' => OPTION_TYPE_CHECKBOX_UL,
										"checkboxes" => array(
												"http-equiv='language'" => "htmlmeta_http-equiv-language",
												"name = 'language'"=>  "htmlmeta_name-language",
												"content-language" => "htmlmeta_name-content-language",
												"http-equiv='imagetoolbar' ('false')" => "htmlmeta_http-equiv-imagetoolbar",
												"http-equiv='cache-control'" => "htmlmeta_http-equiv-cache-control",
												"http-equiv='pragma'" => "htmlmeta_http-equiv-pragma",
												"http-equiv='content-style-type'" => "htmlmeta_http-equiv-content-style-type",
												"name='title'" => "htmlmeta_name-title",
												"name='keywords'" => "htmlmeta_name-keywords",
												"name='description'" => "htmlmeta_name-description",
												"name='page-topic'" => "htmlmeta_name-page-topic",
												"name='robots'" => "htmlmeta_name-robots",
												"name='publisher'" => "htmlmeta_name-publisher",
												"name='creator'" => "htmlmeta_name-creator",
												"name='author'" => "htmlmeta_name-author",
												"name='copyright'" => "htmlmeta_name-copyright",
												"name='rights'" => "htmlmeta_name-rights",
												"name='generator' ('Zenphoto')" => "htmlmeta_name-generator",
												"name='revisit-after'" => "htmlmeta_name-revisit-after",
												"name='expires'" => "htmlmeta_name-expires",
												"name='date'" => "htmlmeta_name-date",
												"name='DC.title'" => "htmlmeta_name-DC.title",
												"name='DC.keywords'" => "htmlmeta_name-DC.keywords",
												"name='DC.description'" => "htmlmeta_name-DC.description",
												"name='DC.language'" => "htmlmeta_name-DC.language",
												"name='DC.subject'" => "htmlmeta_name-DC.subject",
												"name='DC.publisher'" => "htmlmeta_name-DC.publisher",
												"name='DC.creator'" => "htmlmeta_name-DC.creator",
												"name='DC.date'" => "htmlmeta_name-DC.date",
												"name='DC.type'" => "htmlmeta_name-DC.type",
												"name='DC.format'" => "htmlmeta_name-DC.format",
												"name='DC.identifier'" => "htmlmeta_name-DC.identifier",
												"name='DC.rights'" => "htmlmeta_name-DC.rights",
												"name='DC.source'" => "htmlmeta_name-DC.source",
												"name='DC.relation'" => "htmlmeta_name-DC.relation",
												"name='DC.Date.created'" => "htmlmeta_name-DC.Date.created"
												),
										"desc" => gettext("Which of the HTML meta tags should be used. For info about these in detail please refer to the net."))

		);
	}
}

/**
 * Prints html meta data to be used in the <head> section of a page
 *
 */
function getHTMLMetaData() {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news,
					$_zp_current_zenpage_page, $_zp_gallery_page, $_zp_current_category, $_zp_authority;
	$url = sanitize("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

	// Convert locale shorttag to allowed html meta format
	$locale = getOption("locale");
	$locale = strtr($locale,"_","-");

	// generate page title, get date
		$pagetitle = "";
		$date = strftime(DATE_FORMAT); // if we don't have a item date use current date
		$desc = getBareGalleryDesc();
	  if(is_object($_zp_current_image) AND is_object($_zp_current_album)) {
			$pagetitle = getBareImageTitle()." (". getBareAlbumTitle().") - ";
			$date = getImageDate();
			$desc = getBareImageDesc();
		}
		if(is_object($_zp_current_album) AND !is_object($_zp_current_image)) {
			$pagetitle = getBareAlbumTitle()." - ";
			$date = getAlbumDate();
			$desc = getBareAlbumDesc();
		}
		if(function_exists("is_NewsArticle")) {
			if(is_NewsArticle()) {
				$pagetitle = getBareNewsTitle()." - ";
				$date = getNewsDate();
				$desc = strip_tags(getNewsContent());
			} else 	if(is_NewsCategory()) {
				$pagetitle = $_zp_current_category->getTitlelink()." - ";
				$date = strftime(DATE_FORMAT);
				$desc = "";
			} else if(is_Pages()) {
				$pagetitle = getBarePageTitle()." - ";
				$date = getPageDate();
				$desc = strip_tags(getPageContent());
			}
		}
		// shorten desc to the allowed 200 characters if necesssary.
		if(strlen($desc) > 200) {
			$desc = substr($desc,0,200);
		}

		$pagetitle = $pagetitle.getBareGalleryTitle();

	// get master admin
	$admin = $_zp_authority->getAnAdmin(array('`user`=' => $_zp_authority->master_user, '`valid`=' => 1));
	$author = $admin->getName();
	$meta = '';
	if(getOption('htmlmeta_http-equiv-language')) { $meta .= '<meta http-equiv="language" content="'.$locale.'" />'."\n"; }
	if(getOption('htmlmeta_name-language')) { $meta .= '<meta name="language" content="'.$locale.'" />'."\n"; }
	if(getOption('htmlmeta_name-content-language')) { $meta .= '<meta name="content-language" content="'.$locale.'" />'."\n"; }
	if(getOption('htmlmeta_http-equiv-imagetoolbar')) { $meta .= '<meta http-equiv="imagetoolbar" content="false" />'."\n"; }
	if(getOption('htmlmeta_http-equiv-cache-control')) { $meta .= '<meta http-equiv="cache-control" content="'.getOption("htmlmeta_cache_control").'" />'."\n"; }
	if(getOption('htmlmeta_http-equiv-pragma')) { $meta .= '<meta http-equiv="pragma" content="'.getOption("htmlmeta_pragma").'" />'."\n"; }
	if(getOption('htmlmeta_http-equiv-content-style-type')) { $meta .= '<meta http-equiv="Content-Style-Type" content="text/css" />'."\n"; }
	if(getOption('htmlmeta_name-title')) { $meta .= '<meta name="title" content="'.$pagetitle.'" />'."\n"; }
	if(getOption('htmlmeta_name-keywords')) { $meta .= '<meta name="keywords" content="'.getMetaKeywords().'" />'."\n"; }
	if(getOption('htmlmeta_name-description')) { $meta .= '<meta name="description" content="'.$desc.'" />'."\n"; }
	if(getOption('htmlmeta_name-page-topic')) { $meta .= '<meta name="page-topic" content="'.$desc.'" />'."\n"; }
	if(getOption('htmlmeta_name-robots')) { $meta .= '<meta name="robots" content="'.getOption("htmlmeta_robots").'" />'."\n"; }
	if(getOption('htmlmeta_name-publisher')) { $meta .= '<meta name="publisher" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-creator')) { $meta .= '<meta name="creator" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-author')) { $meta .= '<meta name="author" content="'.$author.'" />'."\n"; }
	if(getOption('htmlmeta_name-copyright')) { $meta .= '<meta name="copyright" content=" (c) '.FULLWEBPATH.' - '.$author.'" />'."\n"; }
	if(getOption('htmlmeta_name-rights')) { $meta .= '<meta name="rights" content="'.$author.'" />'."\n"; }
	if(getOption('htmlmeta_name-rights')) { $meta .= '<meta name="generator" content="Zenphoto '.ZENPHOTO_VERSION . ' [' . ZENPHOTO_RELEASE . ']" />'."\n"; }
	if(getOption('htmlmeta_name-revisit-after')) { $meta .= '<meta name="revisit-after" content="'.getOption("htmlmeta_revisit_after").'" />'."\n"; }
	if(getOption('htmlmeta_name-expires')) { $meta .= '<meta name="expires" content="'.getOption("htmlmeta_expires").'" />'."\n"; }
	if(getOption('htmlmeta_name-expires')) { $meta .= '<meta name="date" content="'.$date.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.titl')) { $meta .= '<meta name="DC.title" content="'.$pagetitle.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.keywords')) { $meta .= '<meta name="DC.keywords" content="'.gettMetaKeywords().'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.description')) { $meta .= '<meta name="DC.description" content="'.$desc.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.language')) { $meta .= '<meta name="DC.language" content="'.$locale.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.subject')) { $meta .= '<meta name="DC.subject" content="'.$desc.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.publisher')) { $meta .= '<meta name="DC.publisher" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.creator')) { $meta .= '<meta name="DC.creator" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.date')) { $meta .= '<meta name="DC.date" content="'.$date.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.type')) { $meta .= '<meta name="DC.type" content="Text" /> <!-- ? -->'."\n"; }
	if(getOption('htmlmeta_name-DC.format')) { $meta .= '<meta name="DC.format" content="text/html" /><!-- What else? -->'."\n"; }
	if(getOption('htmlmeta_name-DC.identifier')) { $meta .= '<meta name="DC.identifier" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.rights')) { $meta .= '<meta name="DC.rights" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.source')) { $meta .= '<meta name="DC.source" content="'.$url.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.relation')) { $meta .= '<meta name="DC.relation" content="'.FULLWEBPATH.'" />'."\n"; }
	if(getOption('htmlmeta_name-DC.Date.created')) { $meta .= '<meta name="DC.Date.created" content="'.$date.'" />'."\n"; }

	echo $meta;
}

/**
 * Helper function to list tags/categories as keywords separated by comma.
 *
 * @param array $array the array of the tags or categories to list
 */
function getMetaKeywords() {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category, $_zp_gallery_page,$_zp_zenpage;
	$words = '';
	if(is_object($_zp_current_album) OR is_object($_zp_current_image)) {
		$tags = getTags();
		$words .= getMetaAlbumAndImageTags($tags,"gallery");
	} else if($_zp_gallery_page === "index.php") {
		$tags = array_keys(getAllTagsCount()); // get all if no specific item is set
		$words .= getMetaAlbumAndImageTags($tags,"gallery");
	}
	if(function_exists("getNewsCategories")) {
		if(is_NewsArticle()) {
			$tags = getNewsCategories(getNewsID());
			$words .= getMetaAlbumAndImageTags($tags,"zenpage");
			$tags = getTags();
			$words = $words.",".getMetaAlbumAndImageTags($tags,"gallery");
		} else if(is_Pages()) {
			$tags = getTags();
			$words = getMetaAlbumAndImageTags($tags,"gallery");
		} else if(is_News()) {
			$tags = $_zp_zenpage->getAllCategories();
			$words .= getMetaAlbumAndImageTags($tags,"zenpage");
		} else if (is_NewsCategory()) {
			$words .= $_zp_current_category->getTitle();
		}
	}
	return $words;
}
/**
 * Helper function to print the album and image tags and/or the news article categorieslist within printMetaKeywords()
 * Shortens the length to the allowed 1000 characters.
 *
 * @param array $tags the array of the tags to list
 * @param string $mode "gallery" (to process tags on all) or "zenpage" (to process news categories)
 */
function getMetaAlbumAndImageTags($tags,$mode="") {
	if(is_array($tags)) {
		$alltags = '';
		$count = "";
		$separator = ", ";
		foreach($tags as $keyword) {
			$count++;
			if($count >= count($tags)) $separator = "";
			switch($mode) {
				case "gallery":
					$alltags .= html_encode($keyword).$separator;
					break;
				case "zenpage":
					$alltags .= html_encode($keyword["titlelink"]).$separator;
					break;
			}
		}
	} else {
		$alltags = $tags;
	}
	return $alltags;
}

?>