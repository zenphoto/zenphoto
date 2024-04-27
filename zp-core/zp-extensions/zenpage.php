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
 * <li>{@link https://www.zenphoto.org/news/theming-tutorial/ Zenpage theming } (part 4 of the Zenphoto theming tutorial)</li>
 * </ul>
 *
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext("A CMS plugin that adds the capability to run an entire gallery focused website with zenphoto.");
$plugin_notice = gettext("<strong>Note:</strong> This feature must be integrated into your theme. It is not supported by the <em>basic</em> theme.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_category = gettext('Media');
$option_interface = 'zenpagecms';

if (OFFSET_PATH == 2) {
	setOptionDefault('zenpageNewsLink', array_key_exists('news', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news']['rewrite'] : 'news');
	setOptionDefault('zenpageCategoryLink', array_key_exists('category', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['category']['rewrite'] : '_NEWS_/category');
	setOptionDefault('zenpageNewsArchiveLink', array_key_exists('news_archive', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['news_archive']['rewrite'] : '_NEWS_/archive');
	setOptionDefault('zenpagePagesLink', array_key_exists('pages', $_zp_conf_vars['special_pages']) ? $_zp_conf_vars['special_pages']['pages']['rewrite'] : 'pages');
}

//Zenpage rewrite definitions
$_zp_conf_vars['special_pages']['news'] = array(
		'define' => '_NEWS_',
		'rewrite' => getOption('zenpageNewsLink'),
		'option' => 'zenpageNewsLink',
		'default' => 'news');
$_zp_conf_vars['special_pages']['category'] = array(
		'define' => '_CATEGORY_',
		'rewrite' => getOption('zenpageCategoryLink'),
		'option' => 'zenpageCategoryLink',
		'default' => '_NEWS_/category');
$_zp_conf_vars['special_pages']['news_archive'] = array(
		'define' => '_NEWS_ARCHIVE_',
		'rewrite' => getOption('zenpageNewsArchiveLink'),
		'option' => 'zenpageNewsArchiveLink',
		'default' => '_NEWS_/archive');
$_zp_conf_vars['special_pages']['pages'] = array(
		'define' => '_PAGES_',
		'rewrite' => getOption('zenpagePagesLink'),
		'option' => 'zenpagePagesLink',
		'default' => 'pages');

$_zp_conf_vars['special_pages'][] = array(
		'definition' => '%NEWS%',
		'rewrite' => '_NEWS_');
$_zp_conf_vars['special_pages'][] = array(
		'definition' => '%CATEGORY%',
		'rewrite' => '_CATEGORY_');
$_zp_conf_vars['special_pages'][] = array(
		'definition' => '%NEWS_ARCHIVE%',
		'rewrite' => '_NEWS_ARCHIVE_');
$_zp_conf_vars['special_pages'][] = array(
		'definition' => '%PAGES%',
		'rewrite' => '_PAGES_');

$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%PAGES%/*$',
		'rule' => '%REWRITE% index.php?p=pages [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%PAGES%/(.*)/?$',
		'rule' => '%REWRITE% index.php?p=pages&title=$1 [L, QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%CATEGORY%/(.*)/([0-9]+)/?$',
		'rule' => '%REWRITE% index.php?p=news&category=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%CATEGORY%/(.*)/?$',
		'rule' => '%REWRITE% index.php?p=news&category=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%NEWS_ARCHIVE%/(.*)/([0-9]+)/?$',
		'rule' => '%REWRITE% index.php?p=news&date=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%NEWS_ARCHIVE%/(.+)/?$',
		'rule' => '%REWRITE% index.php?p=news&date=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%NEWS%/([0-9]+)/?$',
		'rule' => '%REWRITE% index.php?p=news&page=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%NEWS%/(.+)/?$',
		'rule' => '%REWRITE% index.php?p=news&title=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array(
		'define' => false,
		'rewrite' => '^%NEWS%/*$',
		'rule' => '%REWRITE% index.php?p=news [L,QSA]');

zp_register_filter('isMyItemToView', 'zenpagecms::isMyItemToView');
zp_register_filter('admin_toolbox_global', 'zenpagecms::admin_toolbox_global');
zp_register_filter('admin_toolbox_news', 'zenpagecms::admin_toolbox_news');
zp_register_filter('admin_toolbox_pages', 'zenpagecms::admin_toolbox_pages');
zp_register_filter('themeSwitcher_head', 'zenpagecms::switcher_head');
zp_register_filter('themeSwitcher_Controllink', 'zenpagecms::switcher_controllink', 0);
zp_register_filter('load_theme_script', 'zenpagecms::switcher_setup', 99);
zp_register_filter('load_theme_script', 'zenpagecms::disableZenpageItems', 0);

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpage.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpageroot.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpageitems.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpagenews.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpagepage.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/classes/class-zenpagecategory.php');

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
			setOptionDefault('enabled-zenpage-items', 'news-and-pages');
			
			setOptionDefault('zenpage_titlelinkdate_articles', 0);
			setOptionDefault('zenpage_titlelinkdate_categories', 0);
			setOptionDefault('zenpage_titlelinkdate_pages', 0);
			setOptionDefault('zenpage_titlelinkdate_location', 'after');
			setOptionDefault('zenpage_titlelinkdate_dateformat', 'timestamp');
		}
	}

	function getOptionsSupported() {
		global $_zp_common_truncate_handler;
		$options = array(
				gettext('Enabled Zenpage items') => array(
						'key' => 'enabled-zenpage-items',
						'type' => OPTION_TYPE_RADIO,
						'order' => 7,
						'buttons' => array(
								gettext('Enable news articles and pages') => 'news-and-pages',
								gettext('Enable news') => 'news',
								gettext('Enable pages') => 'pages'
						),
						'desc' => gettext('This enables or disables the admin tabs for pages and/or news articles. To hide news and/or pages content on the front end as well, themes must be setup to use <br><code>if(ZP_NEWS_ENABLED) { … }</code> or <br><code>if(ZP_PAGES_ENABLED) { … }</code> in appropriate places. Same if disabled items should blocked as they otherwise still can be accessed via direct links. <p class="notebox"><strong>NOTE:</strong> This does not delete content and is not related to management rights.</p>')
				), // The description of the option
				gettext('Articles per page (theme)') => array(
						'key' => 'zenpage_articles_per_page',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 0,
						'desc' => gettext("How many news articles you want to show per page on the news or news category pages.")),
				gettext('News article text length') => array(
						'key' => 'zenpage_text_length',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext("The length of news article excerpts in news or news category pages. Leave empty for full text.") . '<br />' .
						gettext("You can also set a custom article shorten length for the news loop excerpts by using the standard TinyMCE <em>page break</em> plugin button (or manually using the html comment snippet <code>&lt;!-- pagebreak --&gt;</code>). If set, this will override this option.")),
				gettext('News article text shorten indicator') => array(
						'key' => 'zenpage_textshorten_indicator',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("Something that indicates that the article text is shortened, “ (...)” by default.")),
				gettext('Read more') => array(
						'key' => 'zenpage_read_more',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'order' => 3,
						'desc' => gettext("The text for the link to the full article.")),
				gettext('New titlelink with date: Item types') => array(
						'key' => 'zenpage_titlelinkdate_items',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'checkboxes' => array(
								gettext('Articles') => 'zenpage_titlelinkdate_articles',
								gettext('Categories') => 'zenpage_titlelinkdate_categories',
								gettext('Pages') => 'zenpage_titlelinkdate_pages'
						),
						'order' => 4,
						'desc' => gettext('Select the item type where the date always should be appended or prepended to the titlelink of newly created items.') . '<p class="notebox">' . gettext('A date will automatically be added if you are creating an item with an already used titlelink.') . '</p>'),
				gettext('New titlelink with date: Date location') => array(
						'key' => 'zenpage_titlelinkdate_location',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('Before') => 'before',
								gettext('After') => 'after'
						),
						'order' => 5,
						'desc' => gettext('Choose where to add the date to the titlelink of newly created items.')),
				gettext('New titlelink with date: Date format') => array(
						'key' => 'zenpage_titlelinkdate_dateformat',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								gettext('Y-m-d') => 'Y-m-d',
								gettext('Ymd') => 'Ymd',
								gettext('Y-m-d_H-i-s') => 'Y-m-d_H-i-s',
								gettext('YmdHis') => 'YmdHis',
								gettext('Unix timestamp') => 'timestamp'
						),
						'order' => 6,
						'desc' => gettext('Choose which date format to append or prepend to the titlelink of newly created items.')),
				gettext('Truncate titles*') => array(
						'key' => 'menu_truncate_string',
						'type' => OPTION_TYPE_TEXTBOX,
						'disabled' => $_zp_common_truncate_handler,
						'order' => 23,
						'desc' => gettext('Limit titles to this many characters. Zero means no limit.')),
				gettext('Truncate indicator*') => array(
						'key' => 'menu_truncate_indicator',
						'type' => OPTION_TYPE_TEXTBOX,
						'disabled' => $_zp_common_truncate_handler,
						'order' => 24,
						'desc' => gettext('Append this string to truncated titles.')),
				
		);
		if ($_zp_common_truncate_handler) {
			$options['note'] = array(
					'key' => 'menu_truncate_note',
					'type' => OPTION_TYPE_NOTE,
					'order' => 25,
					'desc' => '<p class="notebox">' . $_zp_common_truncate_handler . '</p>');
		} else {
			$_zp_common_truncate_handler = gettext('* These options may be set via the <a href="javascript:gotoName(\'zenpage\');"><em>Zenpage</em></a> plugin options.');
			$options['note'] = array(
					'key' => 'menu_truncate_note',
					'type' => OPTION_TYPE_NOTE,
					'order' => 25,
					'desc' => gettext('<p class="notebox">*<strong>Note:</strong> The setting of these options are shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}
	
	static function disableZenpageItems($script, $valid) {
		global $_zp_gallery_page;
		if ($script && $valid) {
			switch ($_zp_gallery_page) {
				case 'news.php':
					if (!ZP_NEWS_ENABLED) {
						$script = '404.php';
					}
					break;
				case 'pages.php':
					if (!ZP_PAGES_ENABLED) {
						$script = '404.php';
					}
					break;
			}
			return $script;
		}
	}

	static function switcher_head($list) {
		?>
		<script>
			function switchCMS(checked) {
				window.location = '?cmsSwitch=' + checked;
			}
		</script>
		<?php
		return $list;
	}

	static function switcher_controllink($theme) {
		global $_zp_gallery_page;
		if ($_zp_gallery_page == 'pages.php' || $_zp_gallery_page == 'news.php') {
			$disabled = ' disabled="disabled"';
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
	 * @deprecated 2.0 - Use the checkForGuest() template function instead
	 * @param string $auth
	 */
	static function checkForGuest($auth) {
		global $_zp_current_zenpage_page, $_zp_current_category;
		deprecationNotice(gettext('Use the checkForGuest() template function instead'));
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
			echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php">' . gettext('News') . '</a></li>';
			echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-categories.php?page=news&tab=categories">' . gettext('News Categories') . '</a></li>';
		}
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && ZP_PAGES_ENABLED) {
			echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-pages.php">' . gettext('Pages') . '</a></li>';
		}
		return $zf;
	}

	static function admin_toolbox_pages($redirect, $zf) {
		if (zp_loggedin(ZENPAGE_PAGES_RIGHTS) && ZP_PAGES_ENABLED) {
			$delete_page = gettext("Are you sure you want to delete this page? THIS CANNOT BE UNDONE!");
			// page is zenpage page--provide edit, delete, and add links
			echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-edit.php?page&amp;edit&amp;titlelink=' . html_encode(getPageTitlelink()) . '">' . gettext('Edit Page') . '</a></li>';
			echo '<li><a href="' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-edit.php?page&amp;add">' . gettext('New Page') . '</a></li>';
			?>
			<li>
				<button class="admin_data-delete" type="button" onclick="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/admin-pages.php?delete=<?php echo html_encode(getPageTitlelink()); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>', '<?php echo $delete_page; ?>')"><?php echo gettext('Delete Page'); ?></button>
			</li>
			<?php
		}
		return $redirect . '&title=' . urlencode(getPageTitlelink());
	}

	static function admin_toolbox_news($redirect, $zf) {
		global $_zp_current_zenpage_news, $_zp_current_category;
		if (is_NewsArticle()) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && ZP_NEWS_ENABLED) {
				$delete_article = gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!");
				// page is a NewsArticle--provide zenpage edit, delete, and Add links
				echo "<li><a href=\"" . $zf . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;edit&amp;titlelink=" . html_encode($_zp_current_zenpage_news->getName()) . "\">" . gettext("Edit Article") . "</a></li>";
				echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-edit.php?newsarticle&amp;add">' . gettext('New Article') . '</a></li>';
				?>
				<li>
					<button class="admin_data-delete" type="button" onclick="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/admin-news-articles.php?delete=<?php echo html_encode($_zp_current_zenpage_news->getName()); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>', '<?php echo $delete_article; ?>')"><?php echo gettext('Delete Article'); ?></button>
				</li>
				<?php
			}
			$redirect .= '&title=' . urlencode($_zp_current_zenpage_news->getName());
		} else {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) && ZP_NEWS_ENABLED) {
				$delete_category = gettext("Are you sure you want to delete this category? THIS CANNOT BE UNDONE!");
				if (!empty($_zp_current_category)) {
					echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-edit.php?newscategory&titlelink=' . html_encode($_zp_current_category->getName()) . '">' . gettext('Edit Category') . '</a></li>';
					echo '<li><a href="' . $zf . '/' . PLUGIN_FOLDER . '/zenpage/admin-edit.php?newscategory&add">' . gettext('New Category') . '</a></li>';
					?>
					<li>
						<button class="admin_data-delete" type="button" onclick="javascript:confirmDelete('<?php echo $zf . '/' . PLUGIN_FOLDER; ?>/zenpage/admin-categories.php?delete=<?php echo html_encode($_zp_current_category->getName()); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('delete_category'); ?>', '<?php echo $delete_category; ?>')"><?php echo gettext('Delete Category'); ?></button>
					</li>
					<?php
					$redirect .= '&category=' . $_zp_current_category->getName();
				}
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