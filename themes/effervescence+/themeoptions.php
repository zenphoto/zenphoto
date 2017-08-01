<?php
// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

require_once(dirname(__FILE__) . '/functions.php');

class ThemeOptions {

	function __construct() {
		$me = basename(dirname(__FILE__));
		setThemeOptionDefault('Theme_logo', '');
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('Slideshow', true);
		setThemeOptionDefault('Graphic_logo', '*');
		setThemeOptionDefault('Watermark_head_image', true);
		setThemeOptionDefault('effervescence_personality', 'image_page');
		setThemeOptionDefault('effervescence_transition', 'slide-hori');
		setThemeOptionDefault('effervescence_caption_location', 'image');
		setThemeOptionDefault('Theme_colors', 'kish-my father');
		setThemeOptionDefault('effervescence_menu', '');
		setThemeOptionDefault('albums_per_page', 9);
		setThemeOptionDefault('albums_per_row', 3);
		setThemeOptionDefault('images_per_page', 20);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOption('image_size', 595, NULL);
		setThemeOption('image_use_side', 'longest', NULL);
		setThemeOptionDefault('thumb_transition', 1);
		setThemeOptionDefault('thumb_size', 90);
		setThemeOptionDefault('thumb_crop_width', 90);
		setThemeOptionDefault('thumb_crop_height', 90);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('gallery_index', 1);
		setThemeOptionDefault('effervescence_daily_album_image', 1);
		setThemeOptionDefault('effervescence_daily_album_image_effect', '');
		if (class_exists('colorbox')) {
			colorbox::registerScripts(array('album', 'favorites', 'image', 'search'));
		}
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, 595, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, getThemeOption('thumb_crop_width'), getThemeOption('thumb_crop_height'), NULL, NULL, true, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize($me, NULL, 180, 80, NUll, NULL, NULL, NULL, true, NULL, NULL, NULL);
		}
		if (function_exists('createMenuIfNotExists')) {
			$menuitems = array(
					array('type' => 'menulabel', 'title' => getAllTranslations('News Articles'), 'link' => '', 'show' => 1, 'nesting' => 0),
					array('type' => 'menufunction', 'title' => getAllTranslations('All news'),
							'link' => 'printAllNewsCategories("All news",TRUE,"","menu-active",false,false,false,"list",false,getOption("menu_manager_truncate_string"));',
							'show' => 1, 'include_li' => 0, 'nesting' => 1),
					array('type' => 'html', 'title' => getAllTranslations('News Articles Rule'), 'link' => '<li class="menu_rule menu_menulabel"></li>', 'show' => 1, 'include_li' => 0, 'nesting' => 0),
					array('type' => 'custompage', 'title' => getAllTranslations('Gallery'), 'link' => 'gallery', 'show' => 1, 'nesting' => 0),
					array('type' => 'menufunction', 'title' => getAllTranslations('All Albums'), 'link' => 'printAlbumMenuList("list",NULL,"","menu-active","submenu","menu-active","",false,false,false,false,getOption("menu_manager_truncate_string"));', 'show' => 1, 'include_li' => 0, 'nesting' => 1),
					array('type' => 'html', 'title' => getAllTranslations('Gallery Rule'), 'link' => '<li class="menu_rule menu_menulabel"></li>', 'show' => 1, 'include_li' => 0, 'nesting' => 0),
					array('type' => 'menulabel', 'title' => getAllTranslations('Pages'), 'link' => '', 'show' => 1, 'nesting' => 0),
					array('type' => 'menufunction', 'title' => getAllTranslations('All pages'), 'link' => 'printPageMenu("list","","menu-active","submenu","menu-active","",0,false,getOption("menu_manager_truncate_string"));', 'show' => 1, 'include_li' => 0, 'nesting' => 1, getOption("menu_manager_truncate_string")),
					array('type' => 'html', 'title' => getAllTranslations('Pages Rule'), 'link' => '<li class="menu_rule menu_menulabel"></li>', 'show' => 1, 'include_li' => 0, 'nesting' => 0),
					array('type' => 'menulabel', 'title' => getAllTranslations('Archive'), 'link' => '', 'show' => 1, 'nesting' => 0),
					array('type' => 'custompage', 'title' => getAllTranslations('Gallery and News'), 'link' => 'archive', 'show' => 1, 'nesting' => 1),
					array('type' => 'html', 'title' => getAllTranslations('Archive Rule'), 'link' => '<li class="menu_rule menu_menulabel"></li>', 'show' => 1, 'include_li' => 0, 'nesting' => 0)
			);
			if (extensionEnabled('rss')) {
				$rssItems = array(
						array('type' => 'menulabel', 'title' => getAllTranslations('RSS'), 'link' => '', 'show' => 1, 'nesting' => 0),
						array('type' => 'dynamiclink', 'title' => getAllTranslations('Gallery'), 'link' => "html_encode(getRSSLink(''))", 'show' => 1, 'nesting' => 1),
						array('type' => 'dynamiclink', 'title' => getAllTranslations('News'), 'link' => "html_encode(getRSSLink('news'))", 'show' => 1, 'nesting' => 1),
						array('type' => 'dynamiclink', 'title' => getAllTranslations('News and Gallery'), 'link' => "html_encode(getRSSLink('news')).'&amp;withimages'", 'show' => 1, 'nesting' => 1),
				);
				$menuitems = array_merge($menuitems, $rssItems);
			}
			createMenuIfNotExists($menuitems, 'effervescence');
		}
	}

	function getOptionsSupported() {
		global $personalities;
		require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_effects.php');

		$note = '<p class="notebox">' . gettext('<strong>Note:</strong> This option is valid only if the Zenpage plugin is enabled or the Separate gallery index option is checked. Of course the <em>menu_manager</em> plugin must also be enabled.') . '</p>';

		if (!extensionEnabled('print_album_menu') && (($m = getOption('effervescence_menu')) == 'effervescence' || $m == 'zenpage' || $m == 'garland')) {
			$note .= '<p class="notebox">' . sprintf(gettext('<strong>Note:</strong> The <em>%s</em> custom menu makes use of the <em>print_album_menu</em> plugin.'), $m) . '</p>';
		}
		$options = array(
				gettext('Separate gallery index') => array('key' => 'gallery_index', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 1, 'desc' => gettext('Check to move the gallery index from the home page to gallery.php.') . '<p class="notebox">' . gettext('<strong>Note:</strong> this is assumed if the zenpage plugin is enabled.') . '</p>'),
				gettext('Theme logo') => array('key' => 'Theme_logo', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'order' => 8, 'desc' => gettext('The text for the theme logo')),
				gettext('Watermark head image') => array('key' => 'Watermark_head_image', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 11, 'desc' => gettext('Check to place a watermark on the heading image. (Image watermarking must be set.)')),
				gettext('Daily image') => array('key' => 'effervescence_daily_album_image', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 3, 'desc' => gettext('If checked the heading image will change daily rather than on each page load.')),
				gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 3.5, 'desc' => gettext('Check to enable search form.')),
				gettext('Slideshow') => array('key' => 'Slideshow', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 6, 'desc' => gettext('Check to enable slideshow for the <em>Smoothgallery</em> personality.')),
				gettext('Graphic logo') => array('key' => 'Graphic_logo', 'type' => OPTION_TYPE_CUSTOM, 'order' => 4, 'desc' => sprintf(gettext('Select a logo (PNG files in the <em>%s/images</em> folder) or leave empty for text logo.'), UPLOAD_FOLDER)),
				gettext('Theme personality') => array('key' => 'effervescence_personality', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => $personalities,
						'order' => 9,
						'desc' => gettext('Select the theme personality')),
				gettext('Theme colors') => array('key' => 'Theme_colors', 'type' => OPTION_TYPE_CUSTOM, 'order' => 7, 'desc' => gettext('Select the colors of the theme')),
				gettext('Custom menu') => array('key' => 'effervescence_menu', 'type' => OPTION_TYPE_CUSTOM, 'order' => 2, 'desc' => gettext('Set this to the <em>menu_manager</em> menu you wish to use.') . $note)
		);

		if (getOption('effervescence_personality') == 'Image_gallery') {
			$options[gettext('Image gallery transition')] = array('key' => 'effervescence_transition', 'type' => OPTION_TYPE_SELECTOR,
					'selections' => array(gettext('None') => '', gettext('Fade') => 'fade', gettext('Shrink/grow') => 'resize', gettext('Horizontal') => 'slide-hori', gettext('Vertical') => 'slide-vert'),
					'order' => 10,
					'desc' => gettext('Transition effect for Image gallery'));
			$options[gettext('Image gallery caption')] = array('key' => 'effervescence_caption_location', 'type' => OPTION_TYPE_RADIO,
					'buttons' => array(gettext('On image') => 'image', gettext('Separate') => 'separate', gettext('Omit') => 'none'),
					'order' => 10.5,
					'desc' => gettext('Location for Image gallery picture caption'));
		}

		$effects = new image_effects();
		$effectOptions = $effects->getOptionsSupported();
		$effect = array_shift($effectOptions);
		while ($effect && !array_key_exists('selections', $effect)) {
			$effect = array_shift($effectOptions);
		}
		if ($effect && array_key_exists('selections', $effect)) {
			$options[gettext('Index Image')] = array('key' => 'effervescence_daily_album_image_effect', 'type' => OPTION_TYPE_SELECTOR,
					'selections' => $effect['selections'], 'null_selection' => gettext('none'),
					'order' => 5,
					'desc' => gettext('Apply this effect to the index page image.'));
			if (!extensionEnabled('image_effects')) {
				$options[gettext('Index Image')]['disabled'] = true;
				$options[gettext('Index Image')]['desc'] .= '<p class="notebox">' . gettext('This option requires the <em>image_effects</em> plugin to be enabled.') . '</p>';
			}
		}
		return $options;
	}

	function getOptionsDisabled() {
		return array('image_size');
	}

	function handleOption($option, $currentValue) {
		global $themecolors;
		switch ($option) {
			case 'Theme_colors':
				echo '<select id="EF_themeselect_colors" name="' . $option . '"' . ">\n";
				generateListFromArray(array($currentValue), $themecolors, false, false);
				echo "</select>\n";
				break;
			case 'effervescence_menu':
				$menusets = array($currentValue => $currentValue);
				echo '<select id="EF_menuset" name="effervescence_menu"';
				if (function_exists('printCustomMenu')) {
					$result = query_full_array("SELECT DISTINCT menuset FROM " . prefix('menu') . " ORDER BY menuset");
					foreach ($result as $set) {
						$menusets[$set['menuset']] = $set['menuset'];
					}
				} else {
					echo ' disabled="disabled"';
				}
				echo ">\n";
				echo '<option value="" style="background-color:LightGray">' . gettext('*standard menu') . '</option>';
				generateListFromArray(array($currentValue), $menusets, false, false);
				echo "</select>\n";
				break;
			case 'Graphic_logo':
				?>
				<select id="EF_themeselect_logo" name="Graphic_logo">
					<option value="" style="background-color:LightGray"><?php echo gettext('*no logo selected'); ?></option>';
					<option value="*"<?php if ($currentValue == '*') echo ' selected="selected"'; ?>><?php echo gettext('Effervescence'); ?></option>';
					<?php generateListFromFiles($currentValue, SERVERPATH . '/' . UPLOAD_FOLDER . '/images', '.png'); ?>
				</select>
				<?php
				break;
		}
	}

}
?>