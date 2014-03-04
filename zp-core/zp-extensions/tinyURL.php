<?php
/*
 * This plugin implements <i>tiny URLs</i> such as used by the tweet_news plugin
 *
 * <i>Tiny URLs</i> are short unique to Zenphoto. They are short digital strings that
 * allow Zenphoto to locate the object referenced. They are prefixed by <var>tiny/<var>
 * if <i>mod_rewrite</i> is active otherwise they have the form <var>index.php?p=ddddd&t</var> .
 *
 * These can be useful if you want to minimize the length of URLs or if you want to
 * obscure the information that they might convey.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Provides short URLs to Zenphoto objects.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (MOD_REWRITE) ? '' : gettext('Shortened URLs require the <code>mod_rewrite</code> option be enabled.');

$option_interface = 'tinyURL';

if (getOption('tinyURL_agressive'))
	zp_register_filter('getLink', 'tinyURL::getTinyURL');

switch (OFFSET_PATH) {
	case 0:
		if (!$plugin_disable) {
			zp_register_filter('load_request', 'tinyURL::parse');
		}
		break;
	case 2:
		setOptionDefault('zp_plugin_tinyURL', $plugin_is_filter);
		setOptionDefault('tinyURL_agressive', 0);
		setOptionDefault('tinyURL_text', 'tiny');
		break;
	default:
		break;
}
$_zp_conf_vars['special_pages']['tiny'] = array('define'	 => '_TINY_', 'rewrite'	 => getOption('tinyURL_text'),
				'option'	 => 'tinyURL_text', 'default'	 => 'tiny/');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%TINY%([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=$1&t [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '^%TINY%([0-9]+)/([0-9]+)/?$', 'rule' => '%REWRITE% index.php?p=$1&page=$2&t [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('definition' => '%TINY%', 'rewrite' => '_TINY_');

class tinyURL {

	const albums = 1;
	const images = 2;
	const news = 4;
	const news_categories = 8;
	const pages = 16;

	static $DBassoc = array('albums' => self::albums, 'images' => self::images, 'news' => self::news, 'news_categories' => self::news_categories, 'pages' => self::pages);
	static $tableAsoc = array('1' => 'albums', '2' => 'images', '3' => 'news', '4' => 'pages', '5' => 'comments', '6' => 'news_categories');

	function __construct() {

	}

	function getOptionsSupported() {
		$options = array();
		$options[gettext('Use in themes for')] = array(
						'key'		 => 'tinyURL_agressive',
						'type'	 => OPTION_TYPE_CUSTOM,
						'order'	 => 1,
						'desc'	 => gettext('If an option is chosen, normal theme URLs will be replaced with <i>tinyURL</i>s for that object.')
		);

		return $options;
	}

	function handleOption($option, $currentValue) {
		?>
		<label class="nowrap"><input type="checkbox" name="tinyURL_albums" value="<?php echo self::albums; ?>" <?php if ($currentValue & self::albums) echo 'checked="checked" '; ?>/><?php echo gettext('albums'); ?></label>
		<label class="nowrap"><input type="checkbox" name="tinyURL_images" value="<?php echo self::images; ?>" <?php if ($currentValue & self::images) echo 'checked="checked" '; ?>/><?php echo gettext('images'); ?></label>
		<?php
		if (extensionEnabled('zenpage')) {
			?>
			<label class="nowrap"><input type="checkbox" name="tinyURL_news" value="<?php echo self::news; ?>" <?php if ($currentValue & self::news) echo 'checked="checked" '; ?>/><?php echo gettext('news'); ?></label>

			<label class="nowrap"><input type="checkbox" name="tinyURL_news_categories" value="<?php echo self::news_categories; ?>" <?php if ($currentValue & self::news_categories) echo 'checked="checked" '; ?>/><?php echo gettext('news categories'); ?></label>

			<label class="nowrap"><input type="checkbox" name="tinyURL_pages" value="<?php echo self::pages; ?>" <?php if ($currentValue & self::pages) echo 'checked="checked" '; ?>/><?php echo gettext('pages'); ?></label>
			<?php
		}
	}

	function handleOptionSave($themename, $themealbum) {
		$result = 0;
		if (isset($_POST['tinyURL_albums']))
			$result = $result | self::albums;
		if (isset($_POST['tinyURL_images']))
			$result = $result | self::images;
		if (isset($_POST['tinyURL_news']))
			$result = $result | self::news;
		if (isset($_POST['tinyURL_news_categories']))
			$result = $result | self::news_categories;
		if (isset($_POST['tinyURL_pages']))
			$result = $result | self::pages;
		setOption('tinyURL_agressive', $result);
		return false;
	}

	/**
	 *
	 * Returns a Zenphoto tiny URL to the object
	 * @param $obj object
	 */
	static function getURL($obj, $page = NULL) {
		$asoc = array_flip(self::$tableAsoc);
		$tiny = ($obj->getID() << 3) | $asoc[$obj->table];
		if (MOD_REWRITE) {
			if ($page > 1)
				$tiny.='/' . $page;
			if (class_exists('seo_locale')) {
				return seo_locale::localePath(false) . '/' . _TINY_ . $tiny;
			} else {
				return WEBPATH . '/' . _TINY_ . $tiny;
			}
		} else {
			if ($page > 1)
				$tiny.= '&page=' . $page;
			return WEBPATH . '/index.php?p=' . $tiny . '&t';
		}
	}

	static function getTinyURL($link, $obj, $page) {
		if (is_object($obj) && (self::$DBassoc[$obj->table] & getOption('tinyURL_agressive'))) {
			return self::getURL($obj, $page);
		} else {
			return $link;
		}
	}

	static function parse($success) {
		if (isset($_GET['p']) && isset($_GET['t'])) { //	Zenphoto tiny url
			unset($_GET['t']);
			$tiny = sanitize_numeric($_GET['p']);
			$tbl = $tiny & 7;

			if (array_key_exists($tbl, self::$tableAsoc)) {
				$tbl = self::$tableAsoc[$tbl];
				$id = $tiny >> 3;
				$result = query_single_row('SELECT * FROM ' . prefix($tbl) . ' WHERE `id`=' . $id);
				if ($result) {
					switch ($tbl) {
						case 'news':
						case 'pages':
							$_GET['p'] = $tbl;
							$_GET['title'] = $result['titlelink'];
							break;
						case 'news_categories';
							$_GET['p'] = 'news';
							$_GET['category'] = $result['titlelink'];
							break;
						case 'images':
							$image = $_GET['image'] = $result['filename'];
							$result = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $result['albumid']);
						case 'albums':
							$album = $_GET['album'] = $result['folder'];
							unset($_GET['p']);
							if (!empty($image)) {
								$success = zp_load_image($album, $image);
							} else if (!empty($album)) {
								$success = zp_load_album($album);
							}
							break;
						case 'comments':
							unset($_GET['p']);
							$commentid = $id;
							$type = $result['type'];
							$result = query_single_row('SELECT * FROM ' . prefix($result['type']) . ' WHERE `id`=' . $result['ownerid']);
							switch ($type) {
								case 'images':
									$image = $result['filename'];
									$result = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $result['albumid']);
									$redirect = 'index.php?album=' . $result['folder'] . '&image=' . $image;
									break;
								case 'albums':
									$album = $result['folder'];
									$redirect = 'index.php?album=' . $result['folder'];
									break;
								case 'pages':
									$redirect = 'index.php?p=pages&title=' . $result['titlelink'];
									break;
							}
							$redirect .= '#zp_comment_id_' . $commentid;
							header("HTTP/1.0 301 Moved Permanently");
							header("Status: 301 Moved Permanently");
							header('Location: ' . FULLWEBPATH . '/' . $redirect);
							exitZP();
							break;
					}
				}
			}
		}
		return $success;
	}

}
