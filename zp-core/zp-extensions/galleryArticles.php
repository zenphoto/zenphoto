<?php

/**
 * Create news articles when a gallery item is published.
 *
 * @author Stephen Billard (sbillard)

 * @package plugins
 * @subpackage theme
 */
$plugin_is_filter = 600 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('Create news articles when a gallery item is published.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = extensionEnabled('zenpage') ? '' : gettext('Gallery Articles requires Zenpage to be enabled.');

$option_interface = 'galleryArticles';

zp_register_filter('show_change', 'galleryArticles::published');
if (getOption('galleryArticles_albums'))
	zp_register_filter('new_album', 'galleryArticles::published');
if (getOption('galleryArticles_images'))
	zp_register_filter('new_image', 'galleryArticles::published');
zp_register_filter('content_macro', 'galleryArticles::macro');

class galleryArticles {

	function __construct() {
		if (OFFSET_PATH == 2) {
			//clean up the mess from previous implementation
			$sql = 'SELECT * FROM ' . prefix('options') . ' WHERE `name` LIKE "combinews_%"';
			$result = query_full_array($sql);
			foreach ($result as $option) {
				purgeOption($option['name']);
			}

			setOptionDefault('galleryArticles_images', NULL);
			setOptionDefault('galleryArticles_albums', NULL);
			setOptionDefault('galleryArticles_category', NULL);
			setOptionDefault('galleryArticles_albumCategory', 0);
			setOptionDefault('galleryArticles_album_text', getAllTranslations('New album: %1$s'));
			setOptionDefault('galleryArticles_image_text', getAllTranslations('New image: [%2$s]%1$s'));
			setOptionDefault('galleryArticles_size', 80);
			setOptionDefault('galleryArticles_protected', 0);
			if (class_exists('cacheManager')) {
				cacheManager::deleteThemeCacheSizes('galleryArticles');
				cacheManager::addThemeCacheSize('galleryArticles', getOption('galleryArticles_size'), NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL);
			}
		}
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		global $_zp_CMS;
		if ($_zp_CMS) {
			$categories = array();
			$list = $_zp_CMS->getAllCategories();
			foreach ($list as $cat) {
				$categories[get_language_string($cat['title'])] = $cat['titlelink'];
			}

			$list = array('<em>' . gettext('Albums') . '</em>' => 'galleryArticles_albums', '<em>' . gettext('Images') . '</em>' => 'galleryArticles_images');

			$options = array(gettext('Publish for') => array('key' => 'galleryArticles_items', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
							'order' => 1,
							'checkboxes' => $list,
							'desc' => gettext('If a <em>type</em> is checked, a news article will be made when an object of that <em>type</em> is published.')),
					gettext('Image title') => array('key' => 'galleryArticles_image_text', 'type' => OPTION_TYPE_TEXTBOX,
							'order' => 3,
							'multilingual' => true,
							'desc' => gettext('This text will be used as the <em>title</em> of the article. The album title will be substituted for <code>%2$s</code> and the image title for <code>%1$s</code>.')),
					gettext('Album title') => array('key' => 'galleryArticles_album_text', 'type' => OPTION_TYPE_TEXTBOX,
							'order' => 2,
							'multilingual' => true,
							'desc' => gettext('This text will be used as the <em>title</em> of the article. The album title will be substituted for <code>%1$s</code>.')),
					gettext('Size') => array('key' => 'galleryArticles_size', 'type' => OPTION_TYPE_NUMBER,
							'order' => 5,
							'desc' => gettext('Set the size the image will be displayed.')),
					gettext('Publish protected') => array('key' => 'galleryArticles_protected', 'type' => OPTION_TYPE_CHECKBOX,
							'order' => 4,
							'desc' => gettext('Unless this is checked, objects which are "protected" will not have news articles generated.')),
					gettext('Category') => array('key' => 'galleryArticles_category', 'type' => OPTION_TYPE_SELECTOR,
							'order' => 6,
							'selections' => $categories,
							'desc' => gettext('Select a category for the generated articles')),
					gettext('Use album folder') => array('key' => 'galleryArticles_albumCategory', 'type' => OPTION_TYPE_CHECKBOX,
							'order' => 7,
							'desc' => gettext('If this option is checked and a category matching the album folder exists, that will be used as the article category.'))
			);
			if (getOption('zenpage_combinews')) {
				$options[gettext('Import Combi-news')] = array('key' => 'galleryArticles_import', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 99,
						'desc' => gettext('If this option is checked, articles will be generated based on your old <em>Combi-news</em> settings.'));
			}
		} else {
			$options = array(gettext('Disabled') => array('key' => 'galleryArticles_note', 'type' => OPTION_TYPE_NOTE,
							'desc' => '<p class="notebox">' . gettext('Gallery Articles requires Zenpage to be enabled.') . '</p>'));
		}
		return $options;
	}

	/**
	 *
	 * place holder
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {

	}

	function handleOptionSave($themename, $themealbum) {
		if (getOption('galleryArticles_import')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/galleryArticles/combiNews.php');
			purgeOption('galleryArticles_import');
		}
		return false;
	}

	/**
	 *
	 * filter for the setShow() methods
	 * @param object $obj
	 */
	static function published($obj) {
		global $_seen;
		$me = array($obj->table, $obj->getID());
		if (!$_seen || !in_array($me, $_seen)) { //prevent recursive publications
			$_seen[] = $me;
			self::publishArticlesWithCheck($obj);
		}
		return $obj;
	}

	/**
	 *
	 * used by the filters to decide if to publish gallery Articles
	 * @param object $obj
	 */
	private static function publishArticlesWithCheck($obj) {
		if ($obj->getShow()) {
			if (getOption('galleryArticles_protected') || !$obj->isProtected()) {
				self::publishArticle($obj);
			}
		}
	}

	/**
	 *
	 * Creates the news article
	 * @param object $obj
	 */
	protected static function publishArticle($obj, $override = NULL) {
		global $_zp_CMS;
		$galleryitem_text = array();
		$locale = getOption('locale');
		switch ($type = $obj->table) {
			case 'albums':
				$album = $obj->name;
				$dbstring = getOption('galleryArticles_album_text');
				$localtext = get_language_string($dbstring);
				$galleryitem_text[$locale] = sprintf($localtext, $obj->getTitle($locale));
				foreach (generateLanguageList() as $key) {
					$languagetext = get_language_string($dbstring, $key);
					if ($localtext != $languagetext) {
						$galleryitem_text[$key] = sprintf($languagetext, $obj->getTitle($key));
					}
				}
				$ref = '"' . $album . '"';
				$title = $folder = $album;
				$img = $obj->getAlbumThumbImage();
				$class = 'galleryarticles-newalbum';
				break;
			case 'images':
				$album = $obj->album->name;
				$image = $obj->filename;
				$dbstring = unserialize(getOption('galleryArticles_image_text'));
				$localtext = get_language_string($dbstring);
				$galleryitem_text[$locale] = sprintf($localtext, $obj->getTitle($locale), $obj->album->getTitle($locale));
				foreach (generateLanguageList() as $key => $val) {
					$languagetext = get_language_string($dbstring, $key);
					if ($localtext != $languagetext) {
						$galleryitem_text[$key] = sprintf($localtext, $obj->getTitle($key), $obj->album->getTitle($key));
					}
				}
				$ref = '"' . $album . '" "' . $image . '"';
				$folder = $obj->imagefolder;
				$title = $folder . '-' . $image;
				$img = $obj;
				$class = 'galleryarticles-newimage';
				break;
			default:
				//not a gallery object
				return;
		}
		$article = newArticle(seoFriendly('galleryArticles-' . $title));
		$article->setTitle(serialize($galleryitem_text));
		$imglink = $img->getCustomImage(getOption('galleryArticles_size'), NULL, NULL, NULL, NULL, NULL, NULL, -1);
		$desc = '<p><a class="' . $class . '" href="' . $obj->getLink() . '"><img src="' . $imglink . '"></a></p><p>[GALLERYARTICLEDESC ' . $ref . ']</p>';

		$article->setContent($desc);
		$date = $obj->getPublishDate();
		if (!$date)
			$date = date('Y-m-d H:i:s');
		$article->setDateTime($date);
		$article->setLastchange(date('Y-m-d H:i:s'));
		$article->setAuthor('galleryArticles');
		$article->setLastchangeauthor('galleryArticles');
		$article->setShow(true);
		$article->save();

		if ($override) {
			$cat = $override;
		} else {
			$cat = getOption('galleryArticles_category');
			if (getOption('galleryArticles_albumCategory')) {
				$catlist = $_zp_CMS->getAllCategories();
				foreach ($catlist as $category) {
					if ($category['titlelink'] == $folder) {
						$cat = $category['titlelink'];
						break;
					}
				}
			}
		}
		$article->setCategories(array($cat));
	}

	static function macro($macros) {
		$macros['GALLERYARTICLEDESC'] = array(
				'class' => 'function',
				'params' => array('string', 'string*'),
				'value' => 'galleryArticles::getDesc',
				'owner' => 'galleryArticles',
				'desc' => gettext('Dynamically insert the description from a gallery object--album name (%1), image name (%2).')
		);
		return $macros;
	}

	static function getDesc($album, $image = NULL) {
		if ($image) {
			$obj = newImage(array('folder' => $album, 'filename' => $image));
		} else {
			$obj = newAlbum($album);
		}
		return $obj->getDesc();
	}

}

?>