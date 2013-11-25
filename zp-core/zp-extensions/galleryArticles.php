<?php

/**
 *
 * @package plugins
 * @author Stephen Billard (sbillard)
 * @subpackage tools
 */
$plugin_is_filter = 9 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('Create news articles when a gallery item is published.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'galleryArticles';

zp_register_filter('show_change', 'galleryArticles::published');
if (getOption('galleryArticles_albums'))
	zp_register_filter('new_album', 'galleryArticles::published');
if (getOption('galleryArticles_images'))
	zp_register_filter('new_image', 'galleryArticles::published');
zp_register_filter('admin_head', 'galleryArticles::scan');
zp_register_filter('load_theme_script', 'galleryArticles::scan');

/**
 *
 * Standard options interface
 * @author Stephen
 *
 */
class galleryArticles {

	function __construct() {
		setOptionDefault('galleryArticles_images', NULL);
		setOptionDefault('galleryArticles_albums', NULL);
		setOptionDefault('galleryArticles_category', NULL);
		setOptionDefault('galleryArticles_albumCategory', 0);
		$text = gettext('New album: %1$s');
		setOptionDefault('galleryArticles_album_text', getAllTranslations($text));
		$text = gettext('New image: [%2$s]%1$s');
		setOptionDefault('galleryArticles_image_text', getAllTranslations($text));
		setOptionDefault('galleryArticles_size', 80);
		setOptionDefault('galleryArticles_protected', 0);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('galleryArticles');
			cacheManager::addThemeCacheSize('galleryArticles', getOption('galleryArticles_size'), NULL, NULL, NULL, NULL, NULL, NULL, false, getOption('fullimage_watermark'), NULL, NULL);
		}
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		global $_zp_zenpage;
		$categories = array();
		$list = $_zp_zenpage->getAllCategories();
		foreach ($list as $cat) {
			$categories[get_language_string($cat['title'])] = $cat['titlelink'];
		}

		$list = array('<em>' . gettext('Albums') . '</em>' => 'galleryArticles_albums', '<em>' . gettext('Images') . '</em>' => 'galleryArticles_images');

		$options = array(gettext('Publish for')			 => array('key'				 => 'galleryArticles_items', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'order'			 => 1,
										'checkboxes' => $list,
										'desc'			 => gettext('If a <em>type</em> is checked, a news article will be made when an object of that <em>type</em> is published.')),
						gettext('Image title')			 => array('key'					 => 'galleryArticles_image_text', 'type'				 => OPTION_TYPE_TEXTBOX,
										'order'				 => 3,
										'multilingual' => true,
										'desc'				 => gettext('This text will be used as the <em>title</em> of the article. The album folder will be substituted for <code>%2$s</code> and the image title for <code>%1$s</code>.')),
						gettext('Album title')			 => array('key'					 => 'galleryArticles_album_text', 'type'				 => OPTION_TYPE_TEXTBOX,
										'order'				 => 2,
										'multilingual' => true,
										'desc'				 => gettext('This text will be used as the <em>title</em> of the article. The album title will be substituted for <code>%1$s</code>.')),
						gettext('size')							 => array('key'		 => 'galleryArticles_size', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 5,
										'desc'	 => gettext('Set the size the image will be displayed.')),
						gettext('Publish protected') => array('key'		 => 'galleryArticles_protected', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 4,
										'desc'	 => gettext('Unless this is checked, objects which are "protected" will not have news articles generated.')),
						gettext('Category')					 => array('key'				 => 'galleryArticles_category', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 6,
										'selections' => $categories,
										'desc'			 => gettext('Select a category for the generated articles')),
						gettext('Use album folder')	 => array('key'		 => 'galleryArticles_albumCategory', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 7,
										'desc'	 => gettext('If this option is checked and a category matching the album folder exists, that will be used as the article category.'))
		);

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

	/**
	 *
	 * filter for the setShow() methods
	 * @param object $obj
	 */
	static function published($obj) {
		self::publishArticlesWithCheck($obj);
		return $obj;
	}

	/**
	 *
	 * used by the filters to decide if to publish gallery Articles
	 * @param object $obj
	 */
	private static function publishArticlesWithCheck($obj) {
		$type = $obj->table;
		if (getOption('galleryArticles_' . $type)) {
			if ($obj->getShow()) {
				if (getOption('galleryArticles_protected') || !$obj->isProtected()) {
					switch ($type = $obj->table) {

						case 'albums':
							$dt = $obj->getPublishDate();
							if ($dt > date('Y-m-d H:i:s')) {
								$result = query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="galleryArticles" AND `aux`="pending_albums" AND `data`=' . db_quote($obj->name));
								if (!$result) {
									query('INSERT INTO ' . prefix('plugin_storage') . ' (`type`,`aux`,`data`) VALUES ("galleryArticles","pending_albums",' . db_quote($obj->name) . ')');
								}
							} else {
								self::publishArticle($obj);
							}
							break;
						case 'images':
							$dt = $obj->getPublishDate();
							if ($dt > date('Y-m-d H:i:s')) {
								$result = query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="galleryArticles" AND `aux`="pending_images" AND `data`=' . db_quote($obj->imagefolder . '/' . $obj->filename));
								if (!$result) {
									query('INSERT INTO ' . prefix('plugin_storage') . ' (`type`,`aux`,`data`) VALUES ("galleryArticles","pending_images",' . db_quote($obj->imagefolder . '/' . $obj->filename) . ')');
								}
							} else {
								self::publishArticle($obj);
							}
							break;
					}
				}
			}
		}
	}

	/**
	 *
	 * Formats the message and calls sendTweet() on an object
	 * @param object $obj
	 */
	private static function publishArticle($obj) {
		global $_zp_zenpage;
		switch ($type = $obj->table) {
			case 'albums':
				$text = sprintf(get_language_string(getOption('galleryArticles_album_text')), $obj->getTitle());
				$folder = $obj->name;
				$img = $obj->getAlbumThumbImage();
				break;
			case 'images':
				$text = sprintf(get_language_string(getOption('galleryArticles_image_text')), $obj->getTitle(), $obj->imagefolder);
				$folder = $obj->imagefolder;
				$img = $obj;
				break;
		}
		$article = new ZenpageNews(seoFriendly($text));
		$article->setTitle($text);

		$article->setContent('<p><a href="' . $obj->getLink() . '"><img src="' . $img->getCustomImage(getOption('galleryArticles_size'), NULL, NULL, NULL, NULL, NULL, NULL, -1) . '"></p><p>' . $obj->getDesc() . '</p>');
		$article->setShow(true);
		$date = $obj->getPublishDate();
		if (!$date)
			$date = date('Y-m-d H:i:s');
		$article->setDateTime($date);
		$article->setAuthor('galleryArticles');
		$article->save();
		$cat = getOption('galleryArticles_category');
		if (getOption('galleryArticles_albumCategory')) {
			$catlist = $_zp_zenpage->getAllCategories();
			foreach ($catlist as $category) {
				if ($category['titlelink'] == $folder) {
					$cat = $category['titlelink'];
					break;
				}
			}
		}
		$article->setCategories(array($cat));
	}

	/**
	 *
	 * filter which checks if there are any matured items to be sent
	 * @param string $script
	 * @param bool $valid will be false if the object is not found (e.g. there will be a 404 error);
	 * @return string
	 */
	static function scan($script, $valid = true) {
		if ($script && $valid) {

			$result = query_full_array('SELECT * FROM ' . prefix('albums') . ' AS album,' . prefix('plugin_storage') . ' AS store WHERE store.type="galleryArticles" AND store.aux="pending_albums" AND store.data = album.folder AND album.date <= ' . db_quote(date('Y-m-d H:i:s')));
			if ($result) {
				foreach ($result as $album) {
					query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `id`=' . $album['id']);
					$album = newAlbum($album['folder']);
					self::publishArticle($album);
				}
			}
			$result = query_full_array('SELECT * FROM ' . prefix('images') . ' AS image,' . prefix('plugin_storage') . ' AS store WHERE store.type="galleryArticles" AND store.aux="pending_images" AND store.data LIKE image.filename AND image.date <= ' . db_quote(date('Y-m-d H:i:s')));
			if ($result) {
				foreach ($result as $image) {
					query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `id`=' . $image['id']);
					$album = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $image['albumid']);
					$album = newAlbum($album['folder']);
					$image = newImage($album, $image['filename']);
					self::publishArticle($image);
				}
			}
		}
		return $script;
	}

}

?>