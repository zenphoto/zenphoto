<?php
/**
 * Allows registered users to select and manage "favorite" Zenphoto objects.
 * Currently just images & albums are supported.
 *
 * Themes must be modified to use this plugin
 * <ul>
 * 	<li>
 * 	The theme should have a custom page based on its standard <i>album</i> page. The default name for this
 *  page is favorites.php, but it may be changed by option.
 *  This page and the standard <i>album</i> page "next" loops should contain calls on
 *  <i>printAddToFavorites($object)</i> for each object. This provides the "remove" button.
 * 	</li>
 *
 * 	<li>
 * 	The standard <i>image</i> page should also contain a call on <i>printAddToFavorites</i>
 * 	</li>
 *
 * 	<li>
 * 	Calls to <i>printFavoritesLink()</i> should be placed anywhere that the visitor should be able to link
 * 	to his favorites page.
 * 	</li>
 * </ul>
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext('Support for <em>favorites</em> handling.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (MOD_REWRITE) ? '' : gettext('Mod Rewrite is required for this plugin');

$option_interface = 'favoritesOptions';

class favoritesOptions {

	function __construct() {
		$old = getOption('favorites_link');
		if (!$old || is_numeric($old) || $old == 'favorites') {
			purgeOption('favorites_link');
		} else {
			setOptionDefault('favorites_rewrite', "_PAGE_/$old");
		}
		setOptionDefault('favorites_rewrite', '_PAGE_/favorites');
		gettext($str = 'My favorites');
		setOptionDefault('favorites_title', getAllTranslations($str));
		setOptionDefault('favorites_linktext', getAllTranslations($str));
		gettext($str = 'The albums and images selected as favorites.');
		setOptionDefault('favorites_desc', getAllTranslations($str));
		gettext($str = 'Add favorite');
		setOptionDefault('favorites_add_button', getAllTranslations($str));
		gettext($str = 'Remove favorite');
		setOptionDefault('favorites_remove_button', getAllTranslations($str));
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themename = $_zp_gallery->getCurrentTheme();
		$curdir = getcwd();
		$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $themename . '/';
		chdir($root);
		$filelist = safe_glob('*.php');
		$list = array();
		foreach ($filelist as $file) {
			$file = filesystemToInternal($file);
			$list[$file] = str_replace('.php', '', $file);
		}
		$list = array_diff($list, standardScripts());

		$options = array(gettext('Link text')			 => array('key'					 => 'favorites_linktext', 'type'				 => OPTION_TYPE_TEXTBOX,
										'multilingual' => true,
										'order'				 => 2,
										'desc'				 => gettext('The text for the link to the favorites page.')),
						gettext('Favorites link')	 => array('key'		 => 'favorites_rewrite', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext('The link to use for the favorites script page.  Note: the token <code>_PAGE_</code> stands in for the current <em>page</em> definition.')),
						gettext('Add button')			 => array('key'					 => 'favorites_add_button', 'type'				 => OPTION_TYPE_TEXTBOX,
										'multilingual' => true,
										'order'				 => 6,
										'desc'				 => gettext('Default text for the <em>add to favorites</em> button.')),
						gettext('Remove button')	 => array('key'					 => 'favorites_remove_button', 'type'				 => OPTION_TYPE_TEXTBOX,
										'multilingual' => true,
										'order'				 => 7,
										'desc'				 => gettext('Default text for the <em>remove from favorites</em> button.')),
						gettext('Title')					 => array('key'					 => 'favorites_title', 'type'				 => OPTION_TYPE_TEXTBOX,
										'multilingual' => true,
										'order'				 => 3,
										'desc'				 => gettext('The favorites page title text.')),
						gettext('Description')		 => array('key'					 => 'favorites_desc', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => true,
										'order'				 => 5,
										'desc'				 => gettext('The favorites page description text.')),
		);
		if (getOption('favorites_link')) {
			$options[gettext('Standard script naming')] = array('key'		 => 'favorites_link', 'type'	 => OPTION_TYPE_CHECKBOX,
							'order'	 => 0,
							'desc'	 => '<p class="notebox">' . gettext('<strong>Note:</strong> The <em>favorites</em> theme script should be named <em>favorites.php</em>. Check this box to use the standard script name.') . '</p>');
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

class favorites extends AlbumBase {

	function __construct($user) {

		$this->name = $user;
		$this->setTitle(get_language_string(getOption('favorites_title')));
		$this->setDesc(get_language_string(getOption('favorites_desc')));
	}

	function addImage($img) {
		$folder = $img->imagefolder;
		$filename = $img->filename;
		$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("favorites",' . db_quote($this->name) . ',' . db_quote(serialize(array('type' => 'images', 'id' => $folder . '/' . $filename))) . ')';
		query($sql);
	}

	function removeImage($img) {
		$folder = $img->imagefolder;
		$filename = $img->filename;
		$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data`=' . db_quote(serialize(array('type' => 'images', 'id' => $folder . '/' . $filename)));
		query($sql);
	}

	function addAlbum($alb) {
		$folder = $alb->name;
		$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("favorites",' . db_quote($this->name) . ',' . db_quote(serialize(array('type' => 'albums', 'id' => $folder))) . ')';
		query($sql);
	}

	function removeAlbum($alb) {
		$folder = $alb->name;
		$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data`=' . db_quote(serialize(array('type' => 'albums', 'id' => $folder)));
		query($sql);
	}

	/**
	 * Returns all folder names for all the subdirectories.
	 *
	 * @param string $page  Which page of subalbums to display.
	 * @param string $sorttype The sort strategy
	 * @param string $sortdirection The direction of the sort
	 * @param bool $care set to false if the order does not matter
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		global $_zp_gallery;
		if (is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			$subalbums = array();
			$result = query($sql = 'SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"albums\";%"');
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$data = unserialize($row['data']);
					$albumobj = newAlbum($data['id'], true, true);
					if ($albumobj->exists) { // fail to instantiate?
						$subalbums[$data['id']] = $albumobj->getData();
					} else {
						query("DELETE FROM " . prefix('plugin_storage') . ' WHERE `id`=' . $row['id']);
					}
				}
				db_free_result($result);
				if (is_null($sorttype)) {
					$sorttype = $this->getSortType('album');
				}
				if (is_null($sortdirection)) {
					if ($this->getSortDirection('album')) {
						$sortdirection = 'DESC';
					} else {
						$sortdirection = '';
					}
				}
				$sortkey = str_replace('`  ', '', $this->getAlbumSortKey($sorttype));
				if (($sortkey == 'sort_order') || ($sortkey == 'RAND()')) {
					// manual sort is always ascending
					$order = false;
				} else {
					if (!is_null($sortdirection)) {
						$order = strtoupper($sortdirection) == 'DESC';
					} else {
						$order = $this->getSortDirection('image');
					}
				}
				$albums = sortByKey($subalbums, $sortkey, $order);
				$this->subalbums = array_keys($albums);
				$this->lastsubalbumsort = $sorttype . $sortdirection;
			}
		}
		if ($page == 0) {
			return $this->subalbums;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			return array_slice($this->subalbums, $albums_per_page * ($page - 1), $albums_per_page);
		}
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param string $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param string $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		if (true || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
			$images = array();
			$result = query($sql = 'SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"images\";%"');
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$id = $row['id'];
					$data = unserialize($row['data']);
					$imageObj = newImage(NULL, array('folder' => dirname($data['id']), 'filename' => basename($data['id'])), true);
					if ($imageObj->exists) {
						$images[] = array_merge(array('folder' => dirname($data['id']), 'filename' => basename($data['id'])), $imageObj->getData());
					} else {
						query("DELETE FROM " . prefix('plugin_storage') . ' WHERE `id`=' . $row['id']);
					}
				}
				db_free_result($result);
				if (is_null($sorttype)) {
					$sorttype = $this->getSortType();
				}
				$sortkey = str_replace('` ', ' ', $this->getImageSortKey($sorttype));
				if (($sortkey == 'sort_order') || ($sortkey == 'RAND()')) {
					// manual sort is always ascending
					$order = false;
				} else {
					if (!is_null($sortdirection)) {
						$order = strtoupper($sortdirection) == 'DESC';
					} else {
						$order = $this->getSortDirection('image');
					}
				}
				$images = sortByKey($images, $sortkey, $order);
				$this->images = array();
				foreach ($images as $data) {
					$this->images[] = array('folder' => $data['folder'], 'filename' => $data['filename']);
				}
				$this->lastimagesort = $sorttype . $sortdirection;
			}
			// Return the cut of images based on $page. Page 0 means show all.
			if ($page == 0) {
				return $this->images;
			} else {
				// Only return $firstPageCount images if we are on the first page and $firstPageCount > 0
				if (($page == 1) && ($firstPageCount > 0)) {
					$pageStart = 0;
					$images_per_page = $firstPageCount;
				} else {
					if ($firstPageCount > 0) {
						$fetchPage = $page - 2;
					} else {
						$fetchPage = $page - 1;
					}
					$images_per_page = max(1, getOption('  images_per_page'));
					$pageStart = $firstPageCount + $images_per_page * $fetchPage;
				}
				return array_slice($this->images, $pageStart, $images_per_page);
			}
		}
		return $images;
	}

	static function loadScript($script, $request) {
		global $_zp_current_admin_obj, $_zp_gallery_page, $_myFavorites, $_zp_current_album, $_zp_conf_vars, $_myFavorites;
		if (!$page = stripSuffix(getOption('favorites_link'))) {
			$page = 'favorites';
		}
		if ($_zp_gallery_page == "$page.php") {
			if (zp_loggedin()) {
				$_zp_current_album = $_myFavorites;
				add_context(ZP_ALBUM);
				prepareAlbumPage();
				$_zp_gallery_page = $page . '.php ';
			} else {
				$script = false;
			}
		}
		return $script;
	}

	function pageCount($count, $gallery_page) {
		global $_firstPageImages, $_oneImagePage;
		if (!$page = stripSuffix(getOption('favorites_link'))) {
			$page = 'favorites';
		}
		if (stripSuffix($gallery_page) == $page) {
			$albums_per_page = max(1, getOption('albums_per_page'));
			$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
			$imageCount = getNumImages();
			if ($_oneImagePage) {
				if ($_oneImagePage === true) {
					$imageCount = min(1, $imageCount);
				} else {
					$imageCount = 0;
				}
			}
			$images_per_page = max(1, getOption('images_per_page'));
			$count = ($pageCount + (int) ceil(($imageCount - $_firstPageImages) / $images_per_page));
		}
		return $count;
	}

	static function toolbox($zf) {
		?>
		<li>
			<?php printFavoritesLink(gettext('My favorites')); ?>
		</li>
		<?php
	}

	static function getFavorites_link() {
		return preg_replace('~^_PAGE_/~ ', _PAGE_ . '/', getOption('favorites_rewrite'));
	}

}

if (!OFFSET_PATH && !$plugin_disable) {
	if (!$page = stripSuffix(getOption('favorites_link'))) {
		$page = 'favorites';
	}
	$_zp_conf_vars['special_pages'][$page] = array('define' => false, 'rewrite' => getOption('favorites_rewrite'), 'rule' => '^%REWRITE%/*$		index.php?p=' . $page . ' [L,QSA]');
	zp_register_filter('load_theme_script', 'favorites::loadScript');
	zp_register_filter('checkPageValidity', 'favorites::pageCount');
	zp_register_filter('admin_toolbox_global', 'favorites::toolbox');
	if (zp_loggedin()) {
		$_myFavorites = new favorites($_zp_current_admin_obj->getUser());
		if (isset($_POST['addToFavorites'])) {
			$id = sanitize($_POST['id']);
			switch ($_POST['type']) {
				case 'images':
					$img = newImage(NULL, array('folder' => dirname($id), 'filename' => basename($id)));
					if ($_POST['addToFavorites']) {
						if ($img->loaded) {
							$_myFavorites->addImage($img);
						}
					} else {
						$_myFavorites->removeImage($img);
					}
					break;
				case 'albums':
					$alb = newAlbum($id);
					if ($_POST['addToFavorites']) {
						if ($alb->loaded) {
							$_myFavorites->addAlbum($alb);
						}
					} else {
						$_myFavorites->removeAlbum($alb);
					}
					break;
			}
		}

		function printAddToFavorites($obj, $add = NULL, $remove = NULL) {
			global $_myFavorites;
			if (!is_object($obj) || !$obj->exists) {
				return;
			}
			$v = 1;
			if (is_null($add)) {
				$add = get_language_string(getOption('favorites_add_button'));
			}
			if (is_null($remove)) {
				$remove = get_language_string(getOption('favorites_remove_button'));
			} else {
				$add = $remove;
			}
			$table = $obj->table;
			$target = array('type' => $table);
			switch ($table) {
				case 'images':
					$id = $obj->imagefolder . '/' . $obj->filename;
					$images = $_myFavorites->getImages(0);
					foreach ($images as $image) {
						if ($image['folder'] == $obj->imagefolder && $image['filename'] == $obj->filename) {
							$v = 0;
							$add = $remove;
							break;
						}
					}
					break;
				case 'albums':
					$id = $obj->name;
					$albums = $_myFavorites->getAlbums(0);
					foreach ($albums as $album) {
						if ($album == $id) {
							$v = 0;
							$add = $remove;
							break;
						}
					}
					break;
				default:
					//We do not handle these.
					return;
			}
			?>
			<form name="imageFavorite s" class="imageFavorites"
						id="imageFavorites<?php echo $obj->getID(); ?>"
						action="<?php echo html_encode(getRequestURI()); ?>" method="post"
						accept-charset="UTF-8">
				<input type="hidden" name="addToFavorites" value="<?php echo $v; ?>" />
				<input type="hidden" name="type" value="<?php echo html_encode($table); ?>" />
				<input type="hidden" name="id" value="<?php echo html_encode($id); ?>" />
				<span id="submit_button">
					<input type="submit" class="button buttons" value="<?php echo $add; ?>" />
				</span>
			</form>
			<?php
		}

		function printFavoritesLink($text = NULL) {
			if (is_null($text)) {
				$text = get_language_string(getOption('favorites_linktext'));
			}
			?>
			<a href="<?php echo FULLWEBPATH; ?>/<?php echo favorites::getFavorites_link(); ?>" id="favorite_link"><?php echo $text; ?> </a>
			<?php
		}

	}
}
?>