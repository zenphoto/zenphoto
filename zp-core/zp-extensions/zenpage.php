<?php
/**
 * Zenphoto CMS plugin
 *
 * @package plugins
 */
$plugin_version = '1.4.1';
$plugin_description = gettext("A CMS plugin that adds the capability to run an entire gallery focused website with zenphoto.")
				."<p class='notebox'>". gettext("<strong>Note:</strong> This feature must be integrated into your theme. It is not supported by either the <em>default</em> and <em>stopdesign</em> themes.")."</p>";
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$option_interface = 'zenpagecms';
$zenpage_version = $plugin_version;

zp_register_filter('checkForGuest', 'zenpageCheckForGuest');
zp_register_filter('isMyItemToView', 'zenpageIsMyItemToView');

class zenpagecms {

	function zenpagecms() {
		setOptionDefault('zenpage_articles_per_page', '10');
		setOptionDefault('zenpage_text_length', '500');
		setOptionDefault('zenpage_textshorten_indicator', ' (...)');
		gettext($str = 'Read more');
		setOptionDefault('zenpage_read_more', getAllTranslations($str));
		setOptionDefault('zenpage_rss_items', '10');
		setOptionDefault('zenpage_rss_length', '100');
		setOptionDefault('zenpage_admin_articles', '15');
		setOptionDefault('zenpage_indexhitcounter', false);
		setOptionDefault('zenpage_combinews', false);
		setOptionDefault('zenpage_combinews_readmore', gettext('Visit gallery page'));
		setOptionDefault('zenpage_combinews_mode', 'latestimage-sizedimage');
		setOptionDefault('zenpage_combinews_imagesize', '300');
		setOptionDefault('zenpage_combinews_sortorder', 'mtime');
		setOptionDefault('zenpage_combinews_gallerylink', 'image');
		setOptionDefault('combinews-thumbnail-cropwidth','');
		setOptionDefault('combinews-thumbnail-cropheight','');
		setOptionDefault('combinews-thumbnail-width', '');
		setOptionDefault('combinews-thumbnail-height', '');
		setOptionDefault('combinews-thumbnail-cropx', '');
		setOptionDefault('combinews-thumbnail-cropy', '');
		setOptionDefault('combinews-latestimagesbyalbum-imgdesc', false);
		setOptionDefault('combinews-latestimagesbyalbum-imgtitle', false);
		gettext($str = '%1$u new item in <em>%2$s</em>: %3$s');
		setOptionDefault('combinews-customtitle-singular', getAllTranslations($str));
		gettext($str = '%1$u new items in <em>%2$s</em>: %3$s');
		setOptionDefault('combinews-customtitle-plural', getAllTranslations($str));
		setOptionDefault('combinews-customtitle-imagetitles', '6');
		setOptionDefault('menu_truncate_string', 0);
		setOptionDefault('menu_truncate_indicator', '');
	}

	function getOptionsSupported() {
			if (getOption('zp_plugin_menu_manager')) {
			$disable = gettext('* The options may be set via the <a href="javascript:gotoName(\'menu_manager\');"><em>menu_manager</em></a> plugin options.');
		} else if (getOption('zp_plugin_print_album_menu')) {
			$disable = gettext('* The options may be set via the <a href="javascript:gotoName(\'print_album_menu\');"><em>print_album_menu</em></a> plugin options.');
		} else {
			$disable = false;
		}

		$options = array(gettext('Articles per page (theme)') => array('key' => 'zenpage_articles_per_page', 'type' => OPTION_TYPE_TEXTBOX,
										'order' => 0,
										'desc' => gettext("How many news articles you want to show per page on the news or news category pages.")),
		gettext('News article text length') => array('key' => 'zenpage_text_length', 'type' => OPTION_TYPE_TEXTBOX,
									'desc' => gettext("The length of news article excerpts in news or news category pages. Leave empty for full text.")),
		gettext('News article text shorten indicator') => array('key' => 'zenpage_textshorten_indicator', 'type' => OPTION_TYPE_TEXTBOX,
									'desc' => gettext("Something that indicates that the article text is shortened, ' (...)' by default.")),
		gettext('Read more') => array('key' => 'zenpage_read_more', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'desc' => gettext("The text for the link to the full article.")),
		gettext('RSS feed item number') => array('key' => 'zenpage_rss_items', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The number of news articles you want to appear in your site's News RSS feed.")),
		gettext('RSS feed text length') => array('key' => 'zenpage_rss_length', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The text length of the Zenpage RSS feed items. No value for full length.")),
		gettext('Articles per page (admin)') => array('key' => 'zenpage_admin_articles', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("How many news articles you want to show per page on the news article admin page.")),
		gettext('CombiNews') => array('key' => 'zenpage_combinews', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Set to enable the CombiNews feature to show news articles and latest gallery items together on the news section's overview page(s).")."<p class='notebox'>".gettext("<strong>Note:</strong> Images/albums and news articles are still separate, your Zenphoto gallery is not touched in any way! <strong>IMPORTANT: This feature requires MySQL 4.1 or later.</strong>")."</p>"),
		gettext('CombiNews: Gallery page link') => array('key' => 'zenpage_combinews_readmore', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'desc' => gettext("The text for the 'read more'/'view more' link to the gallery page for images/movies/audio.")),
		gettext('CombiNews: Mode') => array('key' => 'zenpage_combinews_mode', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('latest images: sized image') => "latestimages-sizedimage", gettext('latest images: thumbnail') => "latestimages-thumbnail", gettext('latest albums: sized image') => "latestalbums-sizedimage", gettext('latest albums: thumbnail') => "latestalbums-thumbnail",gettext('latest albums: thumbnail-customcrop') => "latestalbums-thumbnail-customcrop",gettext('latest images: thumbnail-customcrop') => "latestimages-thumbnail-customcrop", gettext('latest images by album: thumbnail') => "latestimagesbyalbum-thumbnail",gettext('latest images by album: thumbnail-customcrop') => "latestimagesbyalbum-thumbnail-customcrop", gettext('latest images by album: sized image') => "latestimagesbyalbum-sizedimage"),
										'desc' => gettext("What you want to show within the CombiNews section.<br /><ul><li>Latest images: Entries for all images ever added</li><li>Latest albums: Entries for all albums ever created</li><li>Latest images by album: Entries for all images but grouped by images that have been added within a day to each album (Scheme: 'x new images in album y on date z')</li></ul>")),
		gettext('CombiNews: Sized image size') => array('key' => 'zenpage_combinews_imagesize', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The size of the sized image shown the CombiNews section <em>(only in latest images-sizedimage or latest albums-sizedimage mode)</em>.")),
		gettext('CombiNews: Sort order') => array('key' => 'zenpage_combinews_sortorder', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('date') => "date", gettext('id') => "id", gettext('mtime') => "mtime"),
										'desc' => gettext("The sort order for your gallery items within the CombiNews display except for <em>latest images by album</em> which is by date or mtime only. 'date' (date order), 'id' (added to db order), 'mtime' (upload order).")."<p class='notebox'>".gettext("<strong>Note: </strong> If you experience unexpected results, this refers only to the images that are fetched from the database. Even if they are fetched by id they will be sorted by date with the articles afterwards since articles only have a date."),"</p>"),
		gettext('CombiNews: Gallery link') => array('key' => 'zenpage_combinews_gallerylink', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('image') => "image", gettext('album') => "album"),
										'desc' => gettext("Choose if you want to link from the image entry to its image page directly or to the album page (if CombiNews mode is set for albums the link is automatically only linking to albums). This affects all links of the entry (<em>title</em>, <em>image</em> and the <em>visit gallery link</em>")),
		gettext('CombiNews: Thumbnail crop width') => array('key' => 'combinews-thumbnail-cropwidth', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Thumbnail crop height') => array('key' => 'combinews-thumbnail-cropheight', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Thumbnail width') => array('key' => 'combinews-thumbnail-width', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Thumbnail height') => array('key' => 'combinews-thumbnail-height', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Thumbnail crop x axis') => array('key' => 'combinews-thumbnail-cropx', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Thumbnail crop y axis') => array('key' => 'combinews-thumbnail-cropy', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
		gettext('CombiNews: Show image description') => array('key' => 'combinews-latestimagesbyalbum-imgdesc', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Set to show the image description with every item if using the CombiNews mode <em>latest images by album</em> only. Printed as a paragraph.")),
		gettext('CombiNews: Show image title') => array('key' => 'combinews-latestimagesbyalbum-imgtitle', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Set to show the image title with every item if using the CombiNews mode <em>latest images by album</em> only. Printed as h4-headline.")),
		gettext('CombiNews: Custom title (singular)') => array('key' => 'combinews-customtitle-singular', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
															'desc' => gettext("Custom title for the article in sprintf() syntax. %1\$u = number of new items, %2\$s = title of the album they are in, %3\$s = titles of the new items. Never leave any of these three out! (<em>latest images by album</em> option only).")),
		gettext('CombiNews: Custom title (plural)') => array('key' => 'combinews-customtitle-plural', 'type' => OPTION_TYPE_TEXTBOX,'multilingual' => 1,
															'desc' => gettext("Custom title for the article in sprintf() syntax. %1\$u = number of new items, %2\$s = title of the album they are in, %3\$s = titles of the new items. Never leave any of these three out! (<em>latest images by album</em> option only).")),
		gettext('CombiNews: Custom title - Number of image titles') => array('key' => 'combinews-customtitle-imagetitles', 'type' => OPTION_TYPE_TEXTBOX,
															'desc' => gettext("How many images titles you want to show with the custom title (<em>latest images by album</em> option only).")),
		sprintf(gettext('Truncate titles%s'),($disable)?'*':'') => array('key' => 'menu_truncate_string', 'type' => OPTION_TYPE_TEXTBOX,
															'disabled' => $disable,
															'order' => 6,
															'desc' => gettext('Limit titles to this many characters. Zero means no limit.')),
		sprintf(gettext('Truncate indicator%s'),($disable)?'*':'') => array('key' => 'menu_truncate_indicator', 'type' => OPTION_TYPE_TEXTBOX,
															'disabled' => $disable,
															'order' => 7,
															'desc' => gettext('Append this string to truncated titles.'))
		);
		if ($disable) {
			$options['<p class="notebox">'.$disable.'</p>'] = array('key' => 'menu_manager_truncate_note', 'type' => OPTION_TYPE_CUSTOM,
																															'order' => 8,
																															'desc' => '');
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
	}
}

require_once(dirname(__FILE__).'/zenpage/zenpage-class.php');
require_once(dirname(__FILE__).'/zenpage/zenpage-class-news.php');
require_once(dirname(__FILE__).'/zenpage/zenpage-class-page.php');
require_once(dirname(__FILE__).'/zenpage/zenpage-class-category.php');
require_once(dirname(__FILE__)."/zenpage/zenpage-template-functions.php");

// zenpage filters

/**
 * Handles password checks
 * @param string $auth
 */
function zenpageCheckForGuest($auth) {
	global $_zp_current_zenpage_page, $_zp_current_category;
	if (!is_null($_zp_current_zenpage_page)) { // zenpage page
		$authType = $_zp_current_zenpage_page->checkforGuest();
		return $authType;
	} else if (!is_null($_zp_current_category)) {
		$authType = $_zp_current_category->checkforGuest();
		return $authType;
	}
	return $auth;
}

/**
 * Handles item ownership
 * returns true for allowed access, false for denyed, returns original parameter if not my gallery page
 * @param bool $fail
 */
function zenpageIsMyItemToView($fail) {
	global $_zp_gallery_page,$_zp_current_zenpage_page,$_zp_current_zenpage_news,$_zp_current_category;
	switch($_zp_gallery_page) {
		case 'pages.php':
			if ($_zp_current_zenpage_page->isMyItem(LIST_RIGHTS)) {
				return true;
			}
			break;
		case 'news.php':
			if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
				if ($_zp_current_zenpage_news->isMyItem(LIST_RIGHTS)) {
					return true;
				}
			} else {	//	must be category or main news page?
				if (zp_loggedin(VIEW_NEWS_RIGHTS) || !is_object($_zp_current_category) || !$_zp_current_category->isProtected()) {
					return true;
				}
				if (is_object($_zp_current_category)) {
					if ($_zp_current_category->isMyItem(LIST_RIGHTS)) {
						return true;
					}
				}
			}
			return false;
			break;
	}
	return $fail;
}
?>
