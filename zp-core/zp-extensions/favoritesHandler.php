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
 * 	Calls to <i>printFavoritesURL()</i> should be placed anywhere that the visitor should be able to link
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
		if (OFFSET_PATH == 2) {
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
			setOptionDefault('favorites_album_sort_type', 'title');
			setOptionDefault('favorites_image_sort_type', 'title');
			setOptionDefault('favorites_album_sort_direction', '');
			setOptionDefault('favorites_image_sort_direction', '');
		}
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
						gettext('Sort albums by')	 => array('key'		 => 'favorites_albumsort', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 9,
										'desc'	 => ''),
						gettext('Sort images by')	 => array('key'		 => 'favorites_imagesort', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 10,
										'desc'	 => '')
		);
		if (getOption('favorites_link')) {
			$options[gettext('Standard script naming')] = array('key'		 => 'favorites_link', 'type'	 => OPTION_TYPE_CHECKBOX,
							'order'	 => 0,
							'desc'	 => '<p class="notebox">' . gettext('<strong>Note:</strong> The <em>favorites</em> theme script should be named <em>favorites.php</em>. Check this box to use the standard script name.') . '</p>');
		}
		if (!MOD_REWRITE) {
			$options['note'] = array(
							'key'		 => 'favorites_note',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 0,
							'desc'	 => gettext('<p class="notebox">Favorites requires the <code>mod_rewrite</code> option be enabled.</p>')
			);
		}

		return $options;
	}

	function handleOption($option, $currentValue) {
		$sort = array(gettext('Filename')	 => 'filename',
						gettext('Custom')		 => 'custom',
						gettext('Date')			 => 'date',
						gettext('Title')		 => 'title',
						gettext('ID')				 => 'id',
						gettext('Filemtime') => 'mtime',
						gettext('Owner')		 => 'owner',
						gettext('Published') => 'show'
		);

		switch ($option) {
			case 'favorites_albumsort':
				?>
				<span class="nowrap">
					<select id="albumsortselect" name="subalbumsortby" onchange="update_direction(this, 'album_direction_div', 'album_custom_div');">
						<?php
						$cvt = $type = strtolower(getOption('favorites_album_sort_type'));
						if ($type && !in_array($type, $sort)) {
							$cv = array('custom');
						} else {
							$cv = array($type);
						}
						generateListFromArray($cv, $sort, false, true);
						?>
					</select>
					<?php
					if (($type == 'random') || ($type == '')) {
						$dsp = 'none';
					} else {
						$dsp = 'inline';
					}
					?>
					<label id="album_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
						<?php echo gettext("Descending"); ?>
						<input type="checkbox" name="album_sortdirection" value="1"
						<?php
						if (getOption('favorites_album_sort_direction')) {
							echo "CHECKED";
						};
						?> />
					</label>
				</span>
				<?php
				break;
			case 'favorites_imagesort':
				?>
				<span class="nowrap">
					<select id="imagesortselect" name="sortby" onchange="update_direction(this, 'image_direction_div', 'image_custom_div')">
						<?php
						$cvt = $type = strtolower(getOption('favorites_image_sort_type'));
						if ($type && !in_array($type, $sort)) {
							$cv = array('custom');
						} else {
							$cv = array($type);
						}
						generateListFromArray($cv, $sort, false, true);
						?>
					</select>
					<?php
					if (($type == 'random') || ($type == '')) {
						$dsp = 'none';
					} else {
						$dsp = 'inline';
					}
					?>
					<label id="image_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
						<?php echo gettext("Descending"); ?>
						<input type="checkbox" name="image_sortdirection" value="1"
						<?php
						if (getOption('favorites_image_sort_direction')) {
							echo ' checked="checked"';
						}
						?> />
					</label>
				</span>
				<?php
				break;
		}
	}

	function handleOptionSave($theme, $album) {
		$sorttype = strtolower(sanitize($_POST['sortby'], 3));
		if ($sorttype == 'custom') {
			$sorttype = unquote(strtolower(sanitize($_POST['customimagesort'], 3)));
		}
		setOption('favorites_image_sort_type', $sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			setOption('favorites_image_sort_direction', 0);
		} else {
			if (empty($sorttype)) {
				$direction = 0;
			} else {
				$direction = isset($_POST['image_sortdirection']);
			}
			setOption('favorites_image_sort_direction', $direction ? 'DESC' : '');
		}
		$sorttype = strtolower(sanitize($_POST['subalbumsortby'], 3));
		if ($sorttype == 'custom')
			$sorttype = strtolower(sanitize($_POST['customalbumsort'], 3));
		setOption('favorites_album_sort_type', $sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$direction = 0;
		} else {
			$direction = isset($_POST['album_sortdirection']);
		}
		setOption('favorites_album_sort_direction', $direction ? 'DESC' : '');
	}

}

class favorites extends AlbumBase {

	var $imageSortDirection;
	var $albumSortDirection;
	var $imageSortType;
	var $albumSortType;

	function __construct($user) {

		$this->table = 'albums';
		$this->dynamic = true;
		$this->name = $user;
		$this->setTitle(get_language_string(getOption('favorites_title')));
		$this->setDesc(get_language_string(getOption('favorites_desc')));
		$this->imageSortDirection = getOption('favorites_image_sort_direction');
		$this->albumSortDirection = getOption('favorites_album_sort_direction');
		$this->imageSortType = getOption('favorites_image_sort_type');
		$this->albumSortType = getOption('favorites_album_sort_type');
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
		if ($mine || is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			$results = array();
			$result = query($sql = 'SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"albums\";%"');
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$data = getSerializedArray($row['data']);
					$albumobj = newAlbum($data['id'], true, true);
					if ($albumobj->exists) { // fail to instantiate?
						$results[$data['id']] = $albumobj->getData();
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
				$sortkey = $this->getAlbumSortKey($sorttype);
				if (($sortkey == '`sort_order`') || ($sortkey == 'RAND()')) { // manual sort is always ascending
					$order = false;
				} else {
					if (!is_null($sortdirection)) {
						$order = strtoupper($sortdirection) == 'DESC';
					} else {
						$order = $obj->getSortDirection('album');
					}
				}
				$results = sortByKey($results, $sortkey, $order);
				$this->subalbums = array_keys($results);
				$this->lastsubalbumsort = $sorttype . $sortdirection;
			}
		}
		return parent::getAlbums($page);
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
		if ($mine || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
			$this->images = NULL;
			$images = array();
			$result = query($sql = 'SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . db_quote($this->name) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"images\";%"');
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$id = $row['id'];
					$data = getSerializedArray($row['data']);
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
		}
		return parent::getImages($page);
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

	static function pageCount($count, $gallery_page, $page) {
		global $_firstPageImages, $_oneImagePage;
		if (!$pagename = stripSuffix(getOption('favorites_link'))) {
			$pagename = 'favorites';
		}
		if (stripSuffix($gallery_page) == $pagename) {
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
			if ($count < $page && isset($_POST['addToFavorites']) && !$_POST['addToFavorites']) {
				//We've deleted last item on page, need a place to land when we return
				global $_zp_page;
				header('location: ' . FULLWEBPATH . '/' . $this->getLink($_zp_page - 1));
				exitZP();
			}
		}
		return $count;
	}

	static function toolbox($zf) {
		?>
		<li>
			<?php printFavoritesURL(gettext('My favorites')); ?>
		</li>
		<?php
	}

	function getLink($page = NULL) {
		$link = preg_replace('~^_PAGE_/~ ', _PAGE_ . '/', getOption('favorites_rewrite'));
		if ($page > 1)
			$link .= '/' . $page . '/';
		return $link;
	}

	function getSortDirection($what = 'image') {
		if ($what == 'image') {
			return $this->imageSortDirection;
		} else {
			return $this->albumSortDirection;
		}
	}

	function getSortType($what = 'image') {
		if ($what == 'image') {
			return $this->imageSortType;
		} else {
			return $this->albumSortType;
		}
	}

	function setSortDirection($val, $what = 'image') {
		if ($what == 'image') {
			$this->imageSortDirection = $val;
		} else {
			$this->albumSortDirection = $val;
		}
	}

	function setSortType($sorttype, $what = 'image') {
		if ($what == 'image') {
			return $this->imageSortType;
		} else {
			return $this->albumSortType;
		}
	}

}

if (!$plugin_disable) {
	if (!$page = stripSuffix(getOption('favorites_link'))) {
		$page = 'favorites';
	}
	$_zp_conf_vars['special_pages']['favorites'] = array('define'	 => '_FAVORITES_', 'rewrite'	 => $page,
					'option'	 => 'favorites_link', 'default'	 => '_PAGE_/favorites');
	$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => $page, 'rule' => '^%REWRITE%/*$		index.php?p=' . $page . ' [L,QSA]');

	if (!OFFSET_PATH && !$plugin_disable) {
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

			function getFavoritesURL() {
				global $_myFavorites;
				return $_myFavorites->getLink();
			}

			function printFavoritesURL($text = NULL) {
				global $_myFavorites;
				if (is_null($text)) {
					$text = get_language_string(getOption('favorites_linktext'));
				}
				?>
				<a href="<?php echo FULLWEBPATH; ?>/<?php echo $_myFavorites->getLink(); ?>" id="favorite_link"><?php echo $text; ?> </a>
				<?php
			}

		}
	}
}
?>