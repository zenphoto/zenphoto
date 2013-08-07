<?php
/**
 * Zenphoto is already the easiest gallery management system available but it does not have any normal page management
 * capability. Therefore many people use Zenphoto in combination with another CMS.
 *
 * With Zenpage you can now extend the easy to use interface to manage an entire site with a news section (blog) for
 * announcements. Considering Zenphoto's image, video and audio management capabilites this is the ideal solution for
 * personal portfolio sites of artists, graphic/web designers, illustrators, musicians, multimedia/video artists,
 * photographers and many more.
 *
 * You could even run an audio or podcast blog with Zenphoto and Zenpage.
 *
 * <b>Features</b>
 * <ul>
 * <li>Fully integrated with Zenphoto</li>
 * <li>Custom page management</li>
 * <li>News section with nested categories (blog)</li>
 * <li>Tags for pages and news articles</li>
 * <li>Page and news category password protection</li>
 * <li>Scheduled publishing</li>
 * <li>RSS feed for news articles</li>
 * <li>Comments on news articles and pages incl. subscription via RSS</li>
 * <li>CombiNews feature to show the lastest gallery items like image, videos or audio within the news section as if they were news articles</li>
 * <li>Localization and multi-lingual</li>
 * <li>WSIWYG text editor {@link "http://tinymce.moxiecode.com/index.php TinyMCE} with Ajax File Manager included</li>
 * <li>TinyMCE plugin <i>tinyZenpage</i> to include Zenphoto and Zenpage items into your articles or pages</li>
 * </ul>
 * <b>Usage</b>
 * <ul>
 * <li>{@link http://www.zenphoto.org/2009/03/theming-tutorial/#part-4 Zenpage theming } (part 4 of the Zenphoto theming tutorial)</li>
 * <li>{@link http://www.zenphoto.org/documentation/li_plugins.html	Zenpage functions guide }guide is now included in the plugins section of the Zenphoto functions guide
 * </li>
 * <li>{@link http://www.zenphoto.org/2009/03/troubleshooting-zenpage/ Zenpage troubleshooting (FAQ) }</li>
 * </ul>
 *
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext("A CMS plugin that adds the capability to run an entire gallery focused website with zenphoto.");
$plugin_notice = gettext("<strong>Note:</strong> This feature must be integrated into your theme. It is not supported by either the <em>default</em> or the <em>stopdesign</em> theme.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$option_interface = 'zenpagecms';

zp_register_filter('checkForGuest', 'zenpagecms::checkForGuest');
zp_register_filter('isMyItemToView', 'zenpagecms::isMyItemToView');
zp_register_filter('admin_toolbox_global', 'zenpagecms::admin_toolbox_global');
zp_register_filter('admin_toolbox_news', 'zenpagecms::admin_toolbox_news');
zp_register_filter('admin_toolbox_pages', 'zenpagecms::admin_toolbox_pages');
zp_register_filter('themeSwitcher_head', 'zenpagecms::switcher_head');
zp_register_filter('themeSwitcher_Controllink', 'zenpagecms::switcher_controllink', 0);
zp_register_filter('load_theme_script', 'zenpagecms::switcher_setup', 99);

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-class.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-class-news.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-class-page.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-class-category.php');

$_zp_zenpage = new Zenpage();

class zenpagecms {

	function zenpagecms() {
		setOptionDefault('zenpage_articles_per_page', '10');
		setOptionDefault('zenpage_text_length', '500');
		setOptionDefault('zenpage_textshorten_indicator', ' (...)');
		gettext($str = 'Read more');
		setOptionDefault('zenpage_read_more', getAllTranslations($str));
		setOptionDefault('zenpage_indexhitcounter', false);
		setOptionDefault('zenpage_combinews', false);
		setOptionDefault('zenpage_combinews_readmore', gettext('Visit gallery page'));
		setOptionDefault('zenpage_combinews_mode', 'latestimage-sizedimage');
		setOptionDefault('zenpage_combinews_imagesize', '300');
		setOptionDefault('zenpage_combinews_sortorder', 'mtime');
		setOptionDefault('zenpage_combinews_gallerylink', 'image');
		setOptionDefault('combinews-thumbnail-cropwidth', '');
		setOptionDefault('combinews-thumbnail-cropheight', '');
		setOptionDefault('combinews-thumbnail-width', '');
		setOptionDefault('combinews-thumbnail-height', '');
		setOptionDefault('combinews-thumbnail-cropx', '');
		setOptionDefault('combinews-thumbnail-cropy', '');
		setOptionDefault('combinews-latestimagesbyalbum-imgdesc', false);
		setOptionDefault('combinews-latestimagesbyalbum-imgtitle', false);
		setOptionDefault('combinews-numberimages', '');
		gettext($str = 'New in <em>%2$s</em>%1$s: %3$s');
		setOptionDefault('combinews-customtitle', getAllTranslations($str));
		setOptionDefault('combinews-customtitle-imagetitles', '6');
		setOptionDefault('menu_truncate_string', 0);
		setOptionDefault('menu_truncate_indicator', '');
		if (class_exists('cacheManager') && getOption('zenpage_combinews')) {
			cacheManager::deleteThemeCacheSizes('combinews');
			switch (getOption('zenpage_combinews_mode')) {
				case 'latestimages-sizedimage-maxspace':
				case 'latestalbums-sizedimage-maxspace':
				case 'latestimagesbyalbum-sizedimage-maxspace':
					cacheManager::addThemeCacheSize('combinews', NULL, getOption('combinews-thumbnail-width'), getOption('combinews-thumbnail-height'), getOption('combinews-thumbnail-width'), getOption('combinews-thumbnail-height'), NULL, NULL, true, NULL, NULL, NULL);
					break;
				case 'latestimages-thumbnail-customcrop':
				case 'latestalbums-thumbnail-customcrop':
				case 'latestimagesbyalbum-thumbnail-customcrop':
					cacheManager::addThemeCacheSize('combinews', NULL, getOption('combinews-thumbnail-width'), getOption('combinews-thumbnail-height'), getOption('combinews-thumbnail-cropwidth'), getOption('combinews-thumbnail-cropheight'), getOption('combinews-thumbnail-cropx'), getOption('combinews-thumbnail-cropy'), true, NULL, NULL, NULL);
					break;
			}
		}
	}

	function getOptionsSupported() {
		global $_common_truncate_handler;

		$options = array(gettext('Articles per page (theme)')												 => array('key'		 => 'zenpage_articles_per_page', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 0,
										'desc'	 => gettext("How many news articles you want to show per page on the news or news category pages.")),
						gettext('News article text length')													 => array('key'		 => 'zenpage_text_length', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("The length of news article excerpts in news or news category pages. Leave empty for full text.")),
						gettext('News article text shorten indicator')							 => array('key'		 => 'zenpage_textshorten_indicator', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Something that indicates that the article text is shortened, ' (...)' by default.")),
						gettext('Read more')																				 => array('key'					 => 'zenpage_read_more', 'type'				 => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'order'				 => 3,
										'desc'				 => gettext("The text for the link to the full article.")),
						gettext('CombiNews')																				 => array('key'		 => 'zenpage_combinews', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 6,
										'desc'	 => gettext("Set to enable the CombiNews feature to show news articles and latest gallery items together on the news section's overview page(s).") . "<p class='notebox'>" . gettext("<strong>Note:</strong> Images/albums and news articles are still separate, your Zenphoto gallery is not touched in any way!") . "</p>"),
						gettext('CombiNews: Gallery page link')											 => array('key'					 => 'zenpage_combinews_readmore', 'type'				 => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'order'				 => 7,
										'desc'				 => gettext("The text for the 'read more'/'view more' link to the gallery page for images/movies/audio.")),
						gettext('CombiNews: Mode')																	 => array('key'				 => 'zenpage_combinews_mode', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 8,
										'selections' => array(
														gettext('latest images: sized image')										 => "latestimages-sizedimage",
														gettext('latest images: sized image maxspace')					 => "latestimages-sizedimage-maxspace",
														gettext('latest images: thumbnail')											 => "latestimages-thumbnail",
														gettext('latest images: thumbnail-customcrop')					 => "latestimages-thumbnail-customcrop",
														gettext('latest images: full image')										 => "latestimages-fullimage",
														gettext('latest albums: sized image')										 => "latestalbums-sizedimage",
														gettext('latest albums: sized image maxspace')					 => "latestalbums-sizedimage-maxspace",
														gettext('latest albums: thumbnail')											 => "latestalbums-thumbnail",
														gettext('latest albums: thumbnail-customcrop')					 => "latestalbums-thumbnail-customcrop",
														gettext('latest albums: full image')										 => "latestalbums-fullimage",
														gettext('latest images by album: thumbnail')						 => "latestimagesbyalbum-thumbnail",
														gettext('latest images by album: thumbnail-customcrop')	 => "latestimagesbyalbum-thumbnail-customcrop",
														gettext('latest images by album: sized image')					 => "latestimagesbyalbum-sizedimage",
														gettext('latest images by album: sized image maxspace')	 => "latestimagesbyalbum-sizedimage-maxspace",
														gettext('latest images by album: full image')						 => "latestimagesbyalbum-fullimage"),
										'desc'			 => gettext("What you want to show within the CombiNews section.<br /><ul><li>Latest images: Entries for all images ever added</li><li>Latest albums: Entries for all albums ever created</li><li>Latest images by album: Entries for all images but grouped by images that have been added within a day to each album (Scheme: 'x new images in album y on date z')</li></ul> <em>maxspace</em> means that an uncropped image fitting max. width x max. height as set on the thumbnail width and height options is used (not for multimedia items!).")),
						gettext('CombiNews: Sized image size')											 => array('key'		 => 'zenpage_combinews_imagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 9,
										'desc'	 => gettext("The size of the sized image shown the CombiNews section <em>(only in latest images-sizedimage or latest albums-sizedimage mode)</em>.")),
						gettext('CombiNews: Sort order')														 => array('key'				 => 'zenpage_combinews_sortorder', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 10,
										'selections' => array(gettext('date')				 => "date", gettext('id')					 => "id", gettext('mtime')			 => "mtime", gettext('publishdate') => "publishdate"),
										'desc'			 => gettext("The sort order for your gallery items within the CombiNews display except for <em>latest images by album</em> which is by date or mtime only. <ul><li>'date' (date order)</li><li>'id' (added to db order)</li><li>'mtime' (upload order)</li><li>'publishdate' (manual publish date order - you might get really funny results if this is not set!)</li></ul>") . "<p class='notebox'>" . gettext("<strong>Note: </strong> If you experience unexpected results, this refers only to the images that are fetched from the database. Even if they are fetched by id they will be sorted by date with the articles afterwards since articles only have a date."), "</p>"),
						gettext('CombiNews: Gallery link')													 => array('key'				 => 'zenpage_combinews_gallerylink', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 11,
										'selections' => array(gettext('image') => "image", gettext('album') => "album"),
										'desc'			 => gettext("Choose if you want to link from the image entry to its image page directly or to the album page (if CombiNews mode is set for albums the link is automatically only linking to albums). This affects all links of the entry (<em>title</em>, <em>image</em> and the <em>visit gallery link</em>")),
						gettext('CombiNews: Thumbnail crop width')									 => array('key'		 => 'combinews-thumbnail-cropwidth', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 12,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
						gettext('CombiNews: Thumbnail crop height')									 => array('key'		 => 'combinews-thumbnail-cropheight', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 13,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
						gettext('CombiNews: Thumbnail width')												 => array('key'		 => 'combinews-thumbnail-width', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 14,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> and <em>sized image maxspace</em> variants only. Leave empty if you don't want to use it. For <em>maxspace</em> this is the max width of the uncropped sized image.")),
						gettext('CombiNews: Thumbnail height')											 => array('key'		 => 'combinews-thumbnail-height', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 15,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> and <em>sized image maxspace</em> variants only. Leave empty if you don't want to use it. For <em>maxspace</em> this is the max height of the uncropped sized image.")),
						gettext('CombiNews: Thumbnail crop x axis')									 => array('key'		 => 'combinews-thumbnail-cropx', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 16,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
						gettext('CombiNews: Thumbnail crop y axis')									 => array('key'		 => 'combinews-thumbnail-cropy', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 16,
										'desc'	 => gettext("For <em>thumbnail custom crop</em> only. Leave empty if you don't want to use it.")),
						gettext('CombiNews: Show image description')								 => array('key'		 => 'combinews-latestimagesbyalbum-imgdesc', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 18,
										'desc'	 => gettext("Set to show the image description with every item if using the CombiNews mode <em>latest images by album</em> only. Printed as a paragraph.")),
						gettext('CombiNews: Show image title')											 => array('key'		 => 'combinews-latestimagesbyalbum-imgtitle', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 19,
										'desc'	 => gettext("Set to show the image title with every item if using the CombiNews mode <em>latest images by album</em> only. Printed as h4-headline.")),
						gettext('CombiNews: Custom title')													 => array('key'					 => 'combinews-customtitle', 'type'				 => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'order'				 => 20,
										'desc'				 => gettext('Custom title for the <em>latest images by album</em> article. The string is in <code>sprintf()</code> syntax.<br />&nbsp;&nbsp;&nbsp;&nbsp;<code>%1$u</code> = new items count indicator (may be absent if redundant)<br />&nbsp;&nbsp;&nbsp;&nbsp;<code>%2$s</code> = title of the album they are in<br />&nbsp;&nbsp;&nbsp;&nbsp;<code>%3$s</code> = titles of the new items (up to the limit defined by the option below.)<p class="notebox">Never leave any of these three out!</p>')),
						gettext('CombiNews: Custom title - Number of image titles')	 => array('key'		 => 'combinews-customtitle-imagetitles', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 21,
										'desc'	 => gettext("How many images titles you want to show with the <em>latest images by album</em> title.")),
						gettext('CombiNews: Number of images')											 => array('key'		 => 'combinews-numberimages', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 22,
										'desc'	 => gettext("How many of the new images you want to show in the <em>latest images by album</em> article. Empty for all.")),
						gettext('Truncate titles*')																	 => array('key'			 => 'menu_truncate_string', 'type'		 => OPTION_TYPE_TEXTBOX,
										'disabled' => $_common_truncate_handler,
										'order'		 => 23,
										'desc'		 => gettext('Limit titles to this many characters. Zero means no limit.')),
						gettext('Truncate indicator*')															 => array('key'			 => 'menu_truncate_indicator', 'type'		 => OPTION_TYPE_TEXTBOX,
										'disabled' => $_common_truncate_handler,
										'order'		 => 24,
										'desc'		 => gettext('Append this string to truncated titles.'))
		);
		if ($_common_truncate_handler) {
			$options['note'] = array('key'		 => 'menu_truncate_note', 'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 25,
							'desc'	 => '<p class="notebox">' . $_common_truncate_handler . '</p>');
		} else {
			$_common_truncate_handler = gettext('* These options may be set via the <a href="javascript:gotoName(\'zenpage\');"><em>Zenpage</em></a> plugin options.');
			$options['note'] = array('key'		 => 'menu_truncate_note',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 25,
							'desc'	 => gettext('<p class="notebox">*<strong>Note:</strong> The setting of these options are shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	static function switcher_head($list) {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function switchCMS(checked) {
				window.location = '?cmsSwitch=' + checked;
			}
			// ]]> -->
		</script>
		<?php
		return $list;
	}

	static function switcher_controllink($theme) {
		global $_zp_gallery_page;
		if ($_zp_gallery_page == 'pages.php' || $_zp_gallery_page == 'news.php') {
			$disabled = ' disabled="disalbed"';
		} else {
			$disabled = '';
		}
		if (getPlugin('pages.php', $theme)) { // it supports zenpage
			?>
			<span id="themeSwitcher_zenpage" title="<?php echo gettext("Enable Zenpage CMS plugin"); ?>">
				<label>
					Zenpage
					<input type="checkbox" name="cmsSwitch" id="cmsSwitch" value="1"<?php if (extensionEnabled('zenpage')) echo $disabled . ' checked="checked"'; ?> onclick="switchCMS(this.checked);" />
				</label>
			</span>
			<?php
		}
		return $theme;
	}

	static function switcher_setup($ignore) {
		global $_zp_zenpage;
		if (class_exists('themeSwitcher') && themeSwitcher::active()) {
			if (isset($_GET['cmsSwitch'])) {
				setOption('themeSwitcher_zenpage_switch', $cmsSwitch = (int) ($_GET['cmsSwitch'] == 'true'));
				if (!$cmsSwitch) {
					enableExtension('zenpage', 0, false);
				}
			}
		}
		if (extensionEnabled('zenpage')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');
		} else {
			$_zp_zenpage = NULL;
		}
		return $ignore;
	}

	// zenpage filters

	/**
	 * Handles password checks
	 * @param string $auth
	 */
	static function checkForGuest($auth) {
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
	static function isMyItemToView($fail) {
		global $_zp_gallery_page, $_zp_current_zenpage_page, $_zp_current_zenpage_news, $_zp_current_category;
		switch ($_zp_gallery_page) {
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
				} else { //	must be category or main news page?
					if (zp_loggedin(ALL_NEWS_RIGHTS) || !is_object($_zp_current_category) || !$_zp_current_category->isProtected()) {
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

	/**
	 *
	 * Zenpage admin toolbox links
	 */
	static function admin_toolbox_global($zf) {
		if (zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
			// admin has zenpage rights, provide link to the Zenpage admin tab
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-news-articles.php\">" . gettext("News") . "</a></li>";
		}
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-pages.php\">" . gettext("Pages") . "</a></li>";
		}
		return $zf;
	}

	static function admin_toolbox_pages($redirect, $zf) {
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
			// page is zenpage page--provide edit, delete, and add links
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;edit&amp;titlelink=" . urlencode(getPageTitlelink()) . "\">" . gettext("Edit Page") . "</a></li>";
			if (GALLERY_SESSION) {
				// XSRF defense requires sessions
				?>
				<li><a href="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/page-admin.php?del=<?php echo getPageID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deletePage)"
							 title="<?php echo gettext("Delete page"); ?>"><?php echo gettext("Delete Page"); ?>
					</a></li>
				<?php
			}
			echo "<li><a href=\"" . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;add\">" . gettext("Add Page") . "</a></li>";
		}
		return $redirect . '&amp;title=' . urlencode(getPageTitlelink());
	}

	static function admin_toolbox_news($redirect, $zf) {
		global $_zp_current_category;
		if (is_NewsArticle()) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
				// page is a NewsArticle--provide zenpage edit, delete, and Add links
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;edit&amp;titlelink=" . urlencode(getNewsTitlelink()) . "\">" . gettext("Edit Article") . "</a></li>";
				if (GALLERY_SESSION) {
					// XSRF defense requires sessions
					?>
					<li>
						<a href="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/admin-news-articles.php?del=<?php echo getNewsID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deleteArticle)"
							 title="<?php echo gettext("Delete article"); ?>"><?php echo gettext("Delete Article"); ?>	</a>
					</li>
					<?php
				}
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;add\">" . gettext("Add Article") . "</a></li>";
			}
			$redirect .= '&amp;title=' . urlencode(getNewsTitlelink());
		} else {

			if (!empty($_zp_current_category)) {
				$redirect .= '&amp;category=' . $_zp_current_category->getTitlelink();
			}
		}
		return $redirect;
	}

}
?>
