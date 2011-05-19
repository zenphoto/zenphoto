<?php

require_once(SERVERPATH . "/" . ZENFOLDER . "/admin-functions.php");

class ThemeOptions {


  function ThemeOptions() {
 	  setThemeOptionDefault('Allow_search', true);
	  setThemeOptionDefault('Allow_cloud', true);
		setThemeOptionDefault('albums_per_row', 2);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOptionDefault('thumb_transition', 1);
		setThemeOptionDefault('thumb_size',85);
		setOptionDefault('colorbox_garland_image', 1);
		setOptionDefault('colorbox_garland_album', 1);
		setOptionDefault('colorbox_garland_search', 1);
		setThemeOptionDefault('garland_menu', '');
		if (getOption('zp_plugin_zenpage')) {
			setOption('custom_index_page', 'gallery', false);
		} else {
			setOption('custom_index_page', '', false);
		}
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
			createMenuIfNotExists($menuitems, 'garland');
		}
  }

  function getOptionsUnsupported() {
  	return array('thumb_size','thumb_crop','image_size','custom_index_page');
  }

  function getOptionsSupported() {
		if (!getOption('zp_plugin_print_album_menu') && (($m = getOption('effervescence_menu'))=='effervescence' || $m=='zenpage' || $m == 'garland')) {
			$note = '<p class="notebox">'.sprintf(gettext('<strong>Note:</strong> The <em>%s</em> custom menu makes use of the <em>print_album_menu</em> plugin.'),$m).'</p>';
		} else {
			$note = '';
		}
  	return array(	gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Set to enable search form.')),
						  		gettext('Allow cloud') => array('key' => 'Allow_cloud', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Set to enable tag cloud for album page.')),
									gettext('Custom menu') => array('key' => 'garland_menu', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Set this to the <em>menu_manager</em> menu set you wish to use.').$note)
						  	);
  }

	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'garland_menu':
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
		}
	}
}
?>