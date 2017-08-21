<?php
/**
 * ZenPhoto20 is already the easiest gallery management system available but it does not have any normal page management
 * capability. Therefore many people use zenphoto in combination with another CMS.
 *
 * With Zenpage you can extend the easy to use interface to manage an entire site with a news section (blog) for
 * announcements. Considering zenphoto's image, video and audio management capabilites this is the ideal solution for
 * personal portfolio sites of artists, graphic/web designers, illustrators, musicians, multimedia/video artists,
 * photographers and many more.
 *
 * You could even run an audio or podcast blog with zenphoto and zenpage.
 *
 * <b>Features</b>
 * <ul>
 * <li>Fully integrated with ZenPhoto20</li>
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
 * <li>TinyMCE plugin <i>tinyZenpage</i> to include zenphoto and zenpage items into your articles or pages</li>
 * </ul>
 *
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage theme
 */
$plugin_is_filter = defaultExtension(99 | CLASS_PLUGIN);
$plugin_description = gettext("A CMS plugin that adds the capability to run an entire gallery focused website with ZenPhoto20.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$option_interface = 'cmsFilters';

if (OFFSET_PATH == 2) {
	setOptionDefault('NewsLink', array_key_exists('news', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news']['rewrite'] : 'news');
	setOptionDefault('categoryLink', array_key_exists('category', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['category']['rewrite'] : 'category');
	setOptionDefault('NewsArchiveLink', array_key_exists('news_archive', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news_archive']['rewrite'] : '_NEWS_/archive');
	setOptionDefault('PagesLink', array_key_exists('pages', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['pages']['rewrite'] : 'pages');
}

//Zenpage rewrite definitions
$_zp_conf_vars['special_pages']['news'] = array('define' => '_NEWS_', 'rewrite' => getOption('NewsLink'),
		'option' => 'NewsLink', 'default' => 'news');
$_zp_conf_vars['special_pages']['category'] = array('define' => '_CATEGORY_', 'rewrite' => getOption('categoryLink'),
		'option' => 'categoryLink', 'default' => '_NEWS_/category');
$_zp_conf_vars['special_pages']['news_archive'] = array('define' => '_NEWS_ARCHIVE_', 'rewrite' => getOption('NewsArchiveLink'),
		'option' => 'NewsArchiveLink', 'default' => '_NEWS_/archive');
$_zp_conf_vars['special_pages']['pages'] = array('define' => '_PAGES_', 'rewrite' => getOption('PagesLink'),
		'option' => 'PagesLink', 'default' => 'pages');

$_zp_conf_vars['special_pages'][] = array('definition' => '%NEWS%', 'rewrite' => '_NEWS_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%CATEGORY%', 'rewrite' => '_CATEGORY_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%NEWS_ARCHIVE%', 'rewrite' => '_NEWS_ARCHIVE_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%PAGES%', 'rewrite' => '_PAGES_');

$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%PAGES%/*$',
		'rule' => '%REWRITE% index.php?p=pages [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%PAGES%/(.+?)/*$',
		'rule' => '%REWRITE% index.php?p=pages&title=$1 [L, QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%CATEGORY%/(.+)/([0-9]+)/*$',
		'rule' => '%REWRITE% index.php?p=news&category=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%CATEGORY%/(.+?)/*$',
		'rule' => '%REWRITE% index.php?p=news&category=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%NEWS_ARCHIVE%/(.+)/([0-9]+)/*$',
		'rule' => '%REWRITE% index.php?p=news&date=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%NEWS_ARCHIVE%/(.+?)/*$',
		'rule' => '%REWRITE% index.php?p=news&date=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%NEWS%/([0-9]+)/*$',
		'rule' => '%REWRITE% index.php?p=news&page=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%NEWS%/(.+?)/*$',
		'rule' => '%REWRITE% index.php?p=news&title=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%NEWS%/*$',
		'rule' => '%REWRITE% index.php?p=news [L,QSA]');


zp_register_filter('checkForGuest', 'cmsFilters::checkForGuest');
zp_register_filter('isMyItemToView', 'cmsFilters::isMyItemToView');
zp_register_filter('admin_toolbox_global', 'cmsFilters::admin_toolbox_global');
zp_register_filter('admin_toolbox_news', 'cmsFilters::admin_toolbox_news');
zp_register_filter('admin_toolbox_pages', 'cmsFilters::admin_toolbox_pages');
zp_register_filter('themeSwitcher_head', 'cmsFilters::switcher_head');
zp_register_filter('themeSwitcher_Controllink', 'cmsFilters::switcher_controllink', 0);
zp_register_filter('load_theme_script', 'cmsFilters::switcher_setup', 99);

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-news.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-page.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-category.php');

$_zp_CMS = new CMS();

class cmsFilters {

	function __construct() {
		if (OFFSET_PATH == 2) {

			setOptionDefault('zenpage_articles_per_page', '10');
			setOptionDefault('zenpage_text_length', '500');
			setOptionDefault('zenpage_textshorten_indicator', ' (...)');
			setOptionDefault('zenpage_read_more', getAllTranslations('Read more'));
			setOptionDefault('menu_truncate_string', 0);
			setOptionDefault('menu_truncate_indicator', '');
			setOptionDefault('zenpage_enabled_items', 3);
		}
	}

	function getOptionsSupported() {

		$options = array(
				gettext('Enabled CMS items') => array(
						'key' => 'zenpage_enabled_items',
						'type' => OPTION_TYPE_RADIO,
						'order' => 7,
						'buttons' => array(
								gettext('News') => 1,
								gettext('Pages') => 2,
								gettext('Both') => 3
						),
						'desc' => gettext('Select the CMS features you wish to use on your site.')
				),
				gettext('Articles per page (theme)') => array('key' => 'zenpage_articles_per_page', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 0,
						'desc' => gettext("How many news articles you want to show per page on the news or news category pages.")),
				gettext('News article text length') => array('key' => 'zenpage_text_length', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => gettext("The length of news article excerpts in news or news category pages. Leave empty for full text.")),
				gettext('News article text shorten indicator') => array('key' => 'zenpage_textshorten_indicator', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("Something that indicates that the article text is shortened, “ (...)” by default.")),
				gettext('Read more') => array('key' => 'zenpage_read_more', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
						'order' => 3,
						'desc' => gettext("The text for the link to the full article.")),
				gettext('Truncate titles*') => array('key' => 'menu_truncate_string', 'type' => OPTION_TYPE_NUMBER,
						'order' => 23,
						'desc' => gettext('Limit titles to this many characters. Zero means no limit.')),
				gettext('Truncate indicator*') => array('key' => 'menu_truncate_indicator', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 24,
						'desc' => gettext('Append this string to truncated titles.'))
		);

		$options['note'] = array('key' => 'menu_truncate_note',
				'type' => OPTION_TYPE_NOTE,
				'order' => 25,
				'desc' => gettext('<p class="notebox">*<strong>Note:</strong> These options are shared among <em>menu_manager</em>, <em>print_album_menu</em>, and <em>zenpage</em>.</p>'));
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
		global $_zp_CMS;
		if (class_exists('themeSwitcher') && themeSwitcher::active()) {
			if (isset($_GET['cmsSwitch'])) {
				setOption('themeSwitcher_zenpage_switch', $cmsSwitch = (int) ($_GET['cmsSwitch'] == 'true'));
				if (!$cmsSwitch) {
					enableExtension('zenpage', 0, false);
				}
			}
		}
		if (extensionEnabled('zenpage')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/template-functions.php');
		} else {
			$_zp_CMS = NULL;
		}
		return $ignore;
	}

// zenpage filters

	/**
	 * Handles password checks
	 * @param string $auth
	 */
	static function checkForGuest($auth) {
		global $_zp_current_page, $_zp_current_category;
		if (!is_null($_zp_current_page)) { // zenpage page
			$authType = $_zp_current_page->checkforGuest();
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
		global $_zp_gallery_page, $_zp_current_page, $_zp_current_article, $_zp_current_category;
		switch ($_zp_gallery_page) {
			case 'pages.php':
				if (is_object($_zp_current_page)) {
					return $_zp_current_page->isMyItem(LIST_RIGHTS);
				}
				return false;
			case 'news.php':
				if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
					if ($_zp_current_article->isMyItem(LIST_RIGHTS)) {
						return true;
					}
				} else { //	must be category or main news page?
					if (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS) || !is_object($_zp_current_category) || !$_zp_current_category->isProtected()) {
						return true;
					}
					if (is_object($_zp_current_category)) {
						if ($_zp_current_category->isMyItem(LIST_RIGHTS)) {
							return true;
						}
					}
				}
				return false;
		}
		return $fail;
	}

	/**
	 *
	 * Zenpage admin toolbox links
	 */
	static function admin_toolbox_global($zf) {
		global $_zp_CMS;
		if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && $_zp_CMS && $_zp_CMS->news_enabled) {
			// admin has zenpage rights, provide link to the Zenpage admin tab
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-news.php\">" . gettext("News") . "</a></li>";
		}
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && $_zp_CMS && $_zp_CMS->pages_enabled) {
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-pages.php\">" . gettext("Pages") . "</a></li>";
		}
		return $zf;
	}

	static function admin_toolbox_pages($redirect, $zf) {
		global $_zp_CMS;
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && $_zp_CMS && $_zp_CMS->pages_enabled) {
			// page is zenpage page--provide edit, delete, and add links
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;edit&amp;titlelink=" . urlencode(getPageTitlelink()) . "&amp;subpage=object\">" . gettext("Edit Page") . "</a></li>";
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
		global $_zp_CMS, $_zp_current_category, $_zp_current_article;
		if (!empty($_zp_current_category)) {
			$cat = '&amp;category=' . $_zp_current_category->getTitlelink();
		} else {
			$cat = '';
		}

		if (is_NewsArticle()) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && $_zp_CMS && $_zp_CMS->news_enabled) {
				// page is a NewsArticle--provide zenpage edit, delete, and Add links
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;edit&amp;titlelink=" . html_encode($_zp_current_article->getTitleLink()) . $cat . "&amp;subpage=object\">" . gettext("Edit Article") . "</a></li>";
				if (GALLERY_SESSION) {
					// XSRF defense requires sessions
					?>
					<li>
						<a href="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/admin-news.php?del=<?php echo getNewsID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deleteArticle)"
							 title="<?php echo gettext("Delete article"); ?>"><?php echo gettext("Delete Article"); ?>	</a>
					</li>
					<?php
				}
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;add\">" . gettext("Add Article") . "</a></li>";
			}
			$redirect .= '&amp;title=' . urlencode($_zp_current_article->getTitlelink());
		} else {
			$redirect .= $cat;
		}
		return $redirect;
	}

}

/**
 * Returns the full path of the news index page
 *
 * @return string
 */
function getNewsIndexURL() {
	global $_zp_current_article;
	$p_rewrite = $p = '';
	if (in_context(ZP_ZENPAGE_NEWS_ARTICLE) && in_context(ZP_ZENPAGE_SINGLE)) {
		$pos = floor(($_zp_current_article->getIndex() / ZP_ARTICLES_PER_PAGE) + 1);
		if ($pos > 1) {
			$p_rewrite = $pos;
			$p = '&page=' . $pos;
		}
	}

	return zp_apply_filter('getLink', rewrite_path(_NEWS_ . '/' . $p_rewrite, "/index.php?p=news" . $p), 'news.php', NULL);
}

/**
 * Returns the full path of the news archive page
 *
 * @param string $date the date of the archive page
 * @return string
 */
function getNewsArchiveURL($date) {
	return zp_apply_filter('getLink', rewrite_path(_NEWS_ARCHIVE_ . '/' . $date . '/', "/index.php?p=news&date=$date"), 'news.php', NULL);
}
?>
