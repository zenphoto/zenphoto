<?php
/**
 * Zenphoto is already the easiest gallery management system available but it does not have any normal page management
 * capability. Therefore many people use Zenphoto in combination with another CMS.
 *
 * With Zenpage you can now extend the easy to use interface to manage an entire site with pages and news (blog)
 * Considering Zenphoto's image, video and audio management capabilites this is the ideal solution for
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

if (OFFSET_PATH == 2) {
	setOptionDefault('zenpageNewsLink', array_key_exists('news', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news']['rewrite'] : 'news');
	setOptionDefault('zenpageCategoryLink', array_key_exists('category', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['category']['rewrite'] : '_NEWS_/category');
	setOptionDefault('zenpageNewsArchiveLink', array_key_exists('news_archive', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news_archive']['rewrite'] : '_NEWS_/archive');
	setOptionDefault('zenpagePagesLink', array_key_exists('pages', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['pages']['rewrite'] : 'pages');
}

//Zenpage rewrite definitions
$_zp_conf_vars['special_pages']['news'] = array('define'	 => '_NEWS_', 'rewrite'	 => getOption('zenpageNewsLink'),
				'option'	 => 'zenpageNewsLink', 'default'	 => 'news');
$_zp_conf_vars['special_pages']['category'] = array('define'	 => '_CATEGORY_', 'rewrite'	 => getOption('zenpageCategoryLink'),
				'option'	 => 'zenpageCategoryLink', 'default'	 => '_NEWS_/category');
$_zp_conf_vars['special_pages']['news_archive'] = array('define'	 => '_NEWS_ARCHIVE_', 'rewrite'	 => getOption('zenpageNewsArchiveLink'),
				'option'	 => 'zenpageNewsArchiveLink', 'default'	 => '_NEWS_/archive');
$_zp_conf_vars['special_pages']['pages'] = array('define'	 => '_PAGES_', 'rewrite'	 => getOption('zenpagePagesLink'),
				'option'	 => 'zenpagePagesLink', 'default'	 => 'pages');

$_zp_conf_vars['special_pages'][] = array('definition' => '%NEWS%', 'rewrite' => '_NEWS_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%CATEGORY%', 'rewrite' => '_CATEGORY_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%NEWS_ARCHIVE%', 'rewrite' => '_NEWS_ARCHIVE_');
$_zp_conf_vars['special_pages'][] = array('definition' => '%PAGES%', 'rewrite' => '_PAGES_');

$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%PAGES%/*$',
				'rule'		 => '%REWRITE% index.php?p=pages [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%PAGES%/(.*)/?$',
				'rule'		 => '%REWRITE% index.php?p=pages&title=$1 [L, QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%CATEGORY%/(.*)/([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&category=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%CATEGORY%/(.*)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&category=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%NEWS_ARCHIVE%/(.*)/([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&date=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%NEWS_ARCHIVE%/(.+)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&date=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%NEWS%/([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&page=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%NEWS%/(.+)/?$',
				'rule'		 => '%REWRITE% index.php?p=news&title=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%NEWS%/*$',
				'rule'		 => '%REWRITE% index.php?p=news [L,QSA]');

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

	function __construct() {
		if (OFFSET_PATH == 2) {

			setOptionDefault('zenpage_articles_per_page', '10');
			setOptionDefault('zenpage_text_length', '500');
			setOptionDefault('zenpage_textshorten_indicator', ' (...)');
			gettext($str = 'Read more');
			setOptionDefault('zenpage_read_more', getAllTranslations($str));
			setOptionDefault('zenpage_indexhitcounter', false);
			setOptionDefault('menu_truncate_string', 0);
			setOptionDefault('menu_truncate_indicator', '');
   setOptionDefault('enabled-zenpage-items', 'news-and-pages');
		}
	}

	function getOptionsSupported() {
		global $_common_truncate_handler;

		$options = array(
      gettext('Enabled Zenpage items') => array(
										'key'			 => 'enabled-zenpage-items',
										'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 7,
										'buttons'	 => array(
														gettext('Enable news articles and pages') => 'news-and-pages',
														gettext('Enable news') => 'news',
														gettext('Enable pages') => 'pages'
										),
										'desc'		 => gettext('This enables or disables the admin tabs for pages and/or news articles. To hide news and/or pages content on the front end as well, themes must be setup to use <br><code>if(extensionEnabled("zenpage") && ZP_NEWS_ENABLED) { … }</code> or <br><code>if(extensionEnabled("zenpage") && ZP_PAGES_ENABLED) { … }</code> in appropriate places. Same if disabled items should blocked as they otherwise still can be accessed via direct links. <p class="notebox"><strong>NOTE:</strong> This does not delete content and is not related to management rights.</p>')
						), // The description of the option
      gettext('Articles per page (theme)')					 => array('key'		 => 'zenpage_articles_per_page', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 0,
										'desc'	 => gettext("How many news articles you want to show per page on the news or news category pages.")),
						gettext('News article text length')						 => array('key'		 => 'zenpage_text_length', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("The length of news article excerpts in news or news category pages. Leave empty for full text.")),
						gettext('News article text shorten indicator') => array('key'		 => 'zenpage_textshorten_indicator', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Something that indicates that the article text is shortened, “ (...)” by default.")),
						gettext('Read more')													 => array('key'					 => 'zenpage_read_more', 'type'				 => OPTION_TYPE_TEXTBOX, 'multilingual' => 1,
										'order'				 => 3,
										'desc'				 => gettext("The text for the link to the full article.")),
						gettext('Truncate titles*')										 => array('key'			 => 'menu_truncate_string', 'type'		 => OPTION_TYPE_TEXTBOX,
										'disabled' => $_common_truncate_handler,
										'order'		 => 23,
										'desc'		 => gettext('Limit titles to this many characters. Zero means no limit.')),
						gettext('Truncate indicator*')								 => array('key'			 => 'menu_truncate_indicator', 'type'		 => OPTION_TYPE_TEXTBOX,
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
  global $_zp_zenpage;
		if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && ZP_NEWS_ENABLED) {
// admin has zenpage rights, provide link to the Zenpage admin tab
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-news-articles.php\">" . gettext("News") . "</a></li>";
		}
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && ZP_PAGES_ENABLED) {
			echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-pages.php\">" . gettext("Pages") . "</a></li>";
		}
		return $zf;
	}

	static function admin_toolbox_pages($redirect, $zf) {
   global $_zp_zenpage;
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && ZP_PAGES_ENABLED) {
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
		global $_zp_zenpage, $_zp_current_category, $_zp_current_zenpage_news;
		if (is_NewsArticle()) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && ZP_NEWS_ENABLED) {



// page is a NewsArticle--provide zenpage edit, delete, and Add links
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;edit&amp;titlelink=" . html_encode($_zp_current_zenpage_news->getTitleLink()) . "\">" . gettext("Edit Article") . "</a></li>";
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
			$redirect .= '&amp;title=' . urlencode($_zp_current_zenpage_news->getTitlelink());
		} else {

			if (!empty($_zp_current_category)) {
				$redirect .= '&amp;category=' . $_zp_current_category->getTitlelink();
			}
		}
		return $redirect;
	}

}

/**
 * Returns the full path of the news index page
 * @param int $page Page number to append, default empty (page 1)
 * @return string
 */
function getNewsIndexURL($page = '') {
	$rewrite = _NEWS_ . '/';
	$plain = '/index.php?p=news';
	if ($page > 1) {
		$rewrite .= $page . '/';
		$plain .= '&page=' . $page;
	}
	return zp_apply_filter('getLink', rewrite_path($rewrite, $plain), 'news.php', $page);
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
