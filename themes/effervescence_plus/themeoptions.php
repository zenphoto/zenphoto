<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

class ThemeOptions {

	function ThemeOptions() {
		setThemeOptionDefault('Theme_logo', '');
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('enable_album_zipfile', false);
		setThemeOptionDefault('Slideshow', true);
		setThemeOptionDefault('Graphic_logo', '*');
		setThemeOptionDefault('Watermark_head_image', true);
		setThemeOptionDefault('Theme_personality', 'Image page');
		setThemeOptionDefault('Theme_colors', 'kish-my father');
		setThemeOptionDefault('effervescence_menu', '');
		setThemeOptionDefault('albums_per_row', 3);
		setThemeOptionDefault('images_per_row', 4);
		setThemeOptionDefault('thumb_transition', 1);
		setThemeOptionDefault('effervescence_daily_album_image', 1);
		setThemeOptionDefault('effervescence_daily_album_image_effect', '');
		setOptionDefault('zp_plugin_colorbox', 1);
		setOptionDefault('colorbox_effervescence_plus_album', 1);
		setOptionDefault('colorbox_effervescence_plus_image', 1);
		setOptionDefault('colorbox_effervescence_plus_search', 1);

		if (function_exists('createMenuIfNotExists')) {
			$menuitems = array(
										array('type'=>'menulabel','title'=>gettext('News Articles'),'link'=>'','show'=>1,'nesting'=>0),
										array('type'=>'menufunction','title'=>gettext('All news'),
															'link'=>'printAllNewsCategories("All news",TRUE,"","menu-active",false,false,false,"list",false,getOption("menu_manager_truncate_string"));',
															'show'=>1,'include_li'=>0,'nesting'=>1),
										array('type'=>'html','title'=>gettext('News Articles Rule'),'link'=>'<li class="menu_rule menu_menulabel"></li>','show'=>1,'include_li'=>0,'nesting'=>0),
										array('type'=>'custompage','title'=>gettext('Gallery'),'link'=>'gallery','show'=>1,'nesting'=>0),
										array('type'=>'menufunction','title'=>gettext('All Albums'),'link'=>'printAlbumMenuList("list",NULL,"","menu-active","submenu","menu-active","",false,false,false,false,getOption("menu_manager_truncate_string"));','show'=>1,'include_li'=>0,'nesting'=>1),
										array('type'=>'html','title'=>gettext('Gallery Rule'),'link'=>'<li class="menu_rule menu_menulabel"></li>','show'=>1,'include_li'=>0,'nesting'=>0),
										array('type'=>'menulabel','title'=>gettext('Pages'),'link'=>'','show'=>1,'nesting'=>0),
										array('type'=>'menufunction','title'=>gettext('All pages'),'link'=>'printPageMenu("list","","menu-active","submenu","menu-active","",0,false,getOption("menu_manager_truncate_string"));','show'=>1,'include_li'=>0,'nesting'=>1,getOption("menu_manager_truncate_string")),
										array('type'=>'html','title'=>gettext('Pages Rule'),'link'=>'<li class="menu_rule menu_menulabel"></li>','show'=>1,'include_li'=>0,'nesting'=>0),
										array('type'=>'menulabel','title'=>gettext('Archive'),'link'=>'','show'=>1,'nesting'=>0),
										array('type'=>'custompage','title'=>gettext('Gallery and News'),'link'=>'archive','show'=>1,'nesting'=>1),
										array('type'=>'html','title'=>gettext('Archive Rule'),'link'=>'<li class="menu_rule menu_menulabel"></li>','show'=>1,'include_li'=>0,'nesting'=>0),
										array('type'=>'menulabel','title'=>gettext('RSS'),'link'=>'','show'=>1,'nesting'=>0),
										array('type'=>'customlink','title'=>gettext('Gallery'),'link'=>WEBPATH.'/index.php?rss','show'=>1,'nesting'=>1),
										array('type'=>'customlink','title'=>gettext('News'),'link'=>WEBPATH.'/index.php?rss-news','show'=>1,'nesting'=>1),
										array('type'=>'customlink','title'=>gettext('News and Gallery'),'link'=>WEBPATH.'/index.php?rss-news.php?withimages','show'=>1,'nesting'=>1),
										);
			createMenuIfNotExists($menuitems, 'effervescence');
		}

	}

	function getOptionsSupported() {
		require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_effects.php');
		if (getThemeOption('custom_index_page') == 'gallery') {
			$note = '';
		} else {
			$note = '<p class="notebox">'.gettext('<strong>Note:</strong> This option is valid only if you have the <em>Gallery index page link</em> option set to "gallery". Of course the <em>menu_manager</em> plugin must also be enabled.').'</p>';
		}
		if (!getOption('zp_plugin_print_album_menu') && (($m = getOption('effervescence_menu'))=='effervescence' || $m=='zenpage' || $m == 'garland')) {
			$note .= '<p class="notebox">'.sprintf(gettext('<strong>Note:</strong> The <em>%s</em> custom menu makes use of the <em>print_album_menu</em> plugin.'),$m).'</p>';
		}
		$options = array(	gettext('Theme logo') => array('key' => 'Theme_logo', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('The text for the theme logo')),
											gettext('Watermark head image') => array('key' => 'Watermark_head_image', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to place a watermark on the heading image. (Image watermarking must be set.)')),
											gettext('Daily image') => array('key' => 'effervescence_daily_album_image', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('If checked on the heading image will change daily rather than on each page load.')),
											gettext('ZIP file download') => array('key' => 'enable_album_zipfile', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable album ZIP file download link.')),
											gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
											gettext('Slideshow') => array('key' => 'Slideshow', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable slideshow for the <em>Smoothgallery</em> personality.')),
											gettext('Graphic logo') => array('key' => 'Graphic_logo', 'type' => OPTION_TYPE_CUSTOM, 'desc' => sprintf(gettext('Select a logo (PNG files in the <em>%s/images</em> folder) or leave empty for text logo.'),UPLOAD_FOLDER)),
											gettext('Theme personality') => array('key' => 'Theme_personality', 'type' => OPTION_TYPE_SELECTOR, 'selections' => array(gettext('Image page') => 'Image page', gettext('Simpleviewer') => 'Simpleviewer', gettext('Slimbox') => 'Slimbox', gettext('Smoothgallery') => 'Smoothgallery'),
															'desc' => gettext('Select the theme personality')),
											gettext('Theme colors') => array('key' => 'Theme_colors', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select the colors of the theme')),
											gettext('Custom menu') => array('key' => 'effervescence_menu', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Set this to the <em>menu_manager</em> menu set you wish to use.').$note)
											);

		if (!function_exists('printCustomMenu') || getThemeOption('custom_index_page', NULL, 'effervescence_plus') != 'gallery')	{
			$options[gettext('Custom menu')]['desc'] .= '<p class="notebox">'.gettext('This option requires the <em>menu_manager</em> plugin to be enabled and the <em>Gallery index page link</em> to be set to "gallery".').'</p>';
		}
		$effects = new image_effects();
		$effectOptions = $effects->getOptionsSupported();
		$effect = array_shift($effectOptions);
		while ($effect && !array_key_exists('selections', $effect)) {
			$effect = array_shift($effectOptions);
		}
		if ($effect && array_key_exists('selections', $effect)) {
			$options[gettext('Index Image')] = array('key'=>'effervescence_daily_album_image_effect','type'=>OPTION_TYPE_SELECTOR,
														'selections'=>$effect['selections'],'null_selection' => gettext('none'),
														'desc'=>gettext('Apply this effect to the index page image.'));
			if (!getOption('zp_plugin_image_effects')) {
				$options[gettext('Index Image')]['disabled'] = true;
				$options[gettext('Index Image')]['desc'] .= '<p class="notebox">'.gettext('This option requires the <em>image_effects</em> plugin to be enabled.').'</p>';
			}
		}
		return $options;
	}

	 function getOptionsDisabled() {
  	return array('image_size');
  }

	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'Theme_colors':
				$theme = basename(dirname(__FILE__));
				$themeroot = SERVERPATH . "/themes/$theme/styles";
				echo '<select id="EF_themeselect_colors" name="' . $option . '"' . ">\n";
				generateListFromFiles($currentValue, $themeroot , '.css');
				echo "</select>\n";
				break;
			case 'effervescence_menu':
				$menusets = array();
				echo '<select id="EF_menuset" name="effervescence_menu"';
				if (function_exists('printCustomMenu') && getThemeOption('custom_index_page', NULL, 'effervescence_plus') === 'gallery') {
					$result = query_full_array("SELECT DISTINCT menuset FROM ".prefix('menu')." ORDER BY menuset");
					foreach ($result as $set) {
						$menusets[$set['menuset']] = $set['menuset'];
					}
				} else {
					echo ' disabled="disabled"';
				}
				echo ">\n";
				echo '<option value="" style="background-color:LightGray">'.gettext('*standard menu').'</option>';
				generateListFromArray(array($currentValue), $menusets , false, false);
				echo "</select>\n";
				break;
			case 'Graphic_logo':
				?>
				<select id="EF_themeselect_logo" name="Graphic_logo">
					<option value="" style="background-color:LightGray"><?php echo gettext('*no logo selected'); ?></option>';
					<option value="*"<?php if ($currentValue == '*') echo ' selected="selected"'; ?>><?php echo gettext('Effervescence'); ?></option>';
					<?php
					generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/images' , '.png');
					?>
				</select>
				<?php
				break;
		}
	}
}

?>
