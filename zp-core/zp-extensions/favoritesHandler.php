<?php
/**
 * Allows registered users to select and manage "favorite" Zenphoto objects.
 * Currently just images & albums are supported.
 *
 * <b>Note:</b>
 *
 * If the <i>multi mode</i> option is enabled there may be multiple instances
 * of a user's favorites. When an object is added to favorites, an identifier may
 * be specified. (The <var>Add</var> buttons will include a text field for the name of the
 * instance.) If specified that is the "name" of the favorites instance that
 * will contain the object. If the name is left empty the object will be added
 * to the <i>un-named</i> favorite instance.
 *
 * <b>Note:</b> If the <var>tag_suggest</var> plugin is enabled there will be
 * suggestions made for the text field much like the "tag suggestions" for searching.
 *
 * If an object is contained in multiple favorites there will be multiple <var>remove</var> buttons.
 * The button will have the favoirtes instance name appended if not the <i>un-named</i> favorites.
 *
 * <var>printFavoriresURL()</var> will print links to each defined favorites instance.
 *
 * Themes must be modified to use this plugin.
 * <ul>
 * 	<li>
 * 	The theme should have a custom page based on its standard <i>album</i> page. The name for this
 *  page is favorites.php.
 *  This page and the standard <i>album</i> page "next" loops should contain calls on
 *  <i>printAddToFavorites($object)</i> for each object. This provides the "remove" button.
 * 	</li>
 *
 * 	<li>
 * 	The standard <i>image</i> page should also contain a call on <i>printAddToFavorites()</i>
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

$option_interface = 'favoritesOptions';

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesHandler/favoritesClass.php');

class favoritesOptions {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('favorites_multi', 0);
			setOptionDefault('favorites_link', '_PAGE_/favorites');
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
						gettext('Multiple sets')	 => array('key'		 => 'favorites_multi', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 6,
										'desc'	 => gettext('If enabled a user may have multiple (named) favorites.')),
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
		return false;
	}

}

$_zp_conf_vars['special_pages']['favorites'] = array('define'	 => '_FAVORITES_', 'rewrite'	 => getOption('favorites_link'),
				'option'	 => 'favorites_link', 'default'	 => '_PAGE_/favorites');
$_zp_conf_vars['special_pages'][] = array('definition' => '%FAVORITES%', 'rewrite' => '_FAVORITES_');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%FAVORITES%/(.+)/([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=favorites&instance=$1&page=$2 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%FAVORITES%/([0-9]+)/?$',
				'rule'		 => '%REWRITE% index.php?p=favorites&page=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%FAVORITES%/(.+)/?$',
				'rule'		 => '%REWRITE% index.php?p=favorites&instance=$1 [L,QSA]');
$_zp_conf_vars['special_pages'][] = array('define'	 => false, 'rewrite'	 => '^%FAVORITES%/*$',
				'rule'		 => '%REWRITE% index.php?p=favorites [L,QSA]');

if (OFFSET_PATH) {
	zp_register_filter('edit_album_custom_data', 'favorites::showWatchers');
	zp_register_filter('edit_image_custom_data', 'favorites::showWatchers');
} else {
	zp_register_filter('load_theme_script', 'favorites::loadScript');
	zp_register_filter('checkPageValidity', 'favorites::pageCount');
	zp_register_filter('admin_toolbox_global', 'favorites::toolbox', 21);
	if (zp_loggedin()) {
		if (isset($_POST['addToFavorites'])) {
			$___Favorites = new favorites($_zp_current_admin_obj->getUser());
			if (isset($_POST['instance']) && $_POST['instance']) {
				$___Favorites->instance = trim(sanitize($_POST['instance']));
				unset($_POST['instance']);
			}
			$id = sanitize($_POST['id']);
			switch ($_POST['type']) {
				case 'images':
					$img = newImage(NULL, array('folder' => dirname($id), 'filename' => basename($id)));
					if ($_POST['addToFavorites']) {
						if ($img->loaded) {
							$___Favorites->addImage($img);
						}
					} else {
						$___Favorites->removeImage($img);
					}
					break;
				case 'albums':
					$alb = newAlbum($id);
					if ($_POST['addToFavorites']) {
						if ($alb->loaded) {
							$___Favorites->addAlbum($alb);
						}
					} else {
						$___Favorites->removeAlbum($alb);
					}
					break;
			}
			unset($___Favorites);
			if (isset($_instance)) {
				unset($_instance);
			}
		}
		$_myFavorites = new favorites($_zp_current_admin_obj->getUser());

		function printAddToFavorites($obj, $add = NULL, $remove = NULL) {
			global $_myFavorites, $_zp_current_admin_obj, $_zp_gallery_page, $_myFavorites_button_count;
			if (!zp_loggedin() || $_myFavorites->getOwner() != $_zp_current_admin_obj->getUser() || !is_object($obj) || !$obj->exists) {
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
			if ($_zp_gallery_page == 'favorites.php') {
				//	 only need one remove button since we know the instance
				$multi = false;
				$list = array($_myFavorites->instance);
			} else {
				if ($multi = getOption('favorites_multi')) {
					$list = $_myFavorites->list;
				} else {
					$list = array('');
				}
				if (extensionEnabled('tag_suggest') && !$_myFavorites_button_count) {
					$_myFavorites_button_count++;
					$favList = array_slice($list, 1);
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						var _favList = ['<?php echo implode("','", $favList); ?>'];
						$(function() {
							$('.favorite_instance').tagSuggest({tags: _favList})
						});
						// ]]> -->
					</script>
					<?php
				}
			}
			$seen = array_flip($list);
			switch ($table) {
				case 'images':
					$id = $obj->imagefolder . '/' . $obj->filename;
					foreach ($list as $instance) {
						$_myFavorites->instance = $instance;
						$images = $_myFavorites->getImages(0);
						$seen[$instance] = false;
						foreach ($images as $image) {
							if ($image['folder'] == $obj->imagefolder && $image['filename'] == $obj->filename) {
								$seen[$instance] = true;
								favorites::ad_removeButton($obj, $id, 0, $remove, $instance, $multi);
								break;
							}
						}
					}
					if ($multi || in_array(false, $seen))
						favorites::ad_removeButton($obj, $id, 1, $add, NULL, $multi);
					break;
				case 'albums':
					$id = $obj->name;
					foreach ($list as $instance) {
						$_myFavorites->instance = $instance;
						$albums = $_myFavorites->getAlbums(0);
						$seen[$instance] = false;
						foreach ($albums as $album) {
							if ($album == $id) {
								$seen[$instance] = true;
								favorites::ad_removeButton($obj, $id, 0, $remove, $instance, $multi);
								break;
							}
						}
					}
					if ($multi || in_array(false, $seen))
						favorites::ad_removeButton($obj, $id, 1, $add, NULL, $multi);
					break;
				default:
//We do not handle these.
					return;
			}
		}

		function getFavoritesURL() {
			global $_myFavorites;
			return $_myFavorites->getLink();
		}

		/**
		 * Prints links to the favorites "albums"
		 *
		 * @global favorites $_myFavorites
		 * @param type $text
		 */
		function printFavoritesURL($text = NULL, $before = NULL, $between = NULL, $after = NULL) {
			global $_myFavorites;
			if (zp_loggedin()) {
				if (is_null($text)) {
					$text = get_language_string(getOption('favorites_linktext'));
				}
				$list = $_myFavorites->getList();
				$betwixt = NULL;
				echo $before;
				foreach ($_myFavorites->getList()as $instance) {
					$link = $_myFavorites->getLink(NULL, $instance);
					$display = $text;
					if ($instance) {
						$display .= '[' . $instance . ']';
					}
					echo $betwixt;
					$betwixt = $between;
					?>
					<a href="<?php echo $link; ?>" class="favorite_link"><?php echo html_encode($display); ?> </a>
					<?php
				}
				echo $after;
			}
		}

	}
}
?>