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
		setThemeOptionDefault('zenpage_zp_index_news', false);
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('Use_thickbox', true);
		setThemeOptionDefault('zenpage_homepage', 'none');
		setThemeOptionDefault('zenpage_contactpage', true);
		setThemeOptionDefault('zenpage_custommenu', false);
		setThemeOptionDefault('albums_per_row', 2);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOptionDefault('thumb_transition', 1);
		setOptionDefault('colorbox_zenpage_album', 1);
		setOptionDefault('colorbox_zenpage_image', 1);
		setOptionDefault('colorbox_zenpage_search', 1);
		if (getOption('zp_plugin_zenpage')) {
			setThemeOption('custom_index_page', 'gallery', NULL, NULL, false);
		} else {
			setThemeOption('custom_index_page', '', NULL, NULL, false);
		}

		if (function_exists('createMenuIfNotExists')) {
			$menuitems = array(
			array('type'=>'menulabel','title'=>gettext('News Articles'),'link'=>'','show'=>1,'nesting'=>0),
			array('type'=>'menufunction','title'=>gettext('All news'),'link'=>'printAllNewsCategories("All news",TRUE,"","menu-active",false,false,false,"list",false,getOption("menu_manager_truncate_string"));','show'=>1,'include_li'=>0,'nesting'=>1),
			array('type'=>'menulabel','title'=>gettext('Gallery'),'link'=>'','show'=>1,'nesting'=>0),
			array('type'=>'custompage','title'=>gettext('Gallery index'),'link'=>'gallery','show'=>1,'nesting'=>1),
			array('type'=>'menufunction','title'=>gettext('All Albums'),'link'=>'printAlbumMenuList("list",NULL,"","menu-active","submenu","menu-active","",false,false,false,false,getOption("menu_manager_truncate_string"));','show'=>1,'include_li'=>0,'nesting'=>1),
			array('type'=>'menulabel','title'=>gettext('Pages'),'link'=>'','show'=>1,'nesting'=>0),
			array('type'=>'menufunction','title'=>gettext('All pages'),'link'=>'printPageMenu("list","","menu-active","submenu","menu-active","",0,false,getOption("menu_manager_truncate_string"));','show'=>1,'include_li'=>0,'nesting'=>1),
		);
			createMenuIfNotExists($menuitems, 'zenpage');
		}
	}
	function getOptionsSupported() {
		return array(	gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
									gettext('Use Colorbox') => array('key' => 'Use_thickbox', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to display of the full size image with Colorbox.')),
									gettext('News on index page') => array('key' => 'zenpage_zp_index_news', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Enable this if you want to show the news section's first page on the <code>index.php</code> page.")),
									gettext('Homepage') => array('key' => 'zenpage_homepage', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext("Choose here any <em>un-published Zenpage page</em> (listed by <em>titlelink</em>) to act as your site's homepage instead the normal gallery index.")."<p class='notebox'>".gettext("<strong>Note:</strong> This of course overrides the <em>News on index page</em> option and your theme must be setup for this feature! Visit the theming tutorial for details.")."</p>"),
									gettext('Use standard contact page') => array('key' => 'zenpage_contactpage', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Disable this if you do not want to use the separate contact page with the contact form. You can also use the codeblock of a page for this. See the contact_form plugin documentation for more info.')),
									gettext('Use custom menu') => array('key' => 'zenpage_custommenu', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check this if you want to use the <em>menu_manager</em> plugin if enabled to build a custom menu instead of the separate standard ones. A standard menu named "zenpage" is created and used automatically.'))
									);
	}

  function getOptionsDisabled() {
  	return array('custom_index_page');
  }

	function handleOption($option, $currentValue) {
		if($option == "zenpage_homepage") {
			$unpublishedpages = query_full_array("SELECT titlelink FROM ".prefix('pages')." WHERE `show` != 1 ORDER by `sort_order`");
			if(empty($unpublishedpages)) {
				echo gettext("No unpublished pages available");
				// clear option if no unpublished pages are available or have been published meanwhile
				// so that the normal gallery index appears and no page is accidentally set if set to unpublished again.
				setOption("zenpage_homepage", "none", false);
			} else {
				echo '<input type="hidden" name="'.CUSTOM_OPTION_PREFIX.'selector-zenpage_homepage" value="0" />' . "\n";
				echo '<select id="'.$option.'" name="zenpage_homepage">'."\n";
				if($currentValue === "none") {
					$selected = " selected = 'selected'";
				} else {
					$selected = "";
				}
				echo "<option$selected>".gettext("none")."</option>";
				foreach($unpublishedpages as $page) {
					if($currentValue === $page["titlelink"]) {
						$selected = " selected = 'selected'";
					} else {
						$selected = "";
					}
					echo "<option$selected>".$page["titlelink"]."</option>";
				}
				echo "</select>\n";
			}
		}
	}
}
?>
