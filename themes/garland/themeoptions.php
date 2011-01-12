<?php

require_once(SERVERPATH . "/" . ZENFOLDER . "/admin-functions.php");

class ThemeOptions {


  function ThemeOptions() {
 	  setThemeOptionDefault('Allow_search', true);
	  setThemeOptionDefault('Allow_cloud', true);
		setThemeOptionDefault('albums_per_row', 2);
		setThemeOptionDefault('images_per_row', 5);
	  setThemeOptionDefault('thumb_size',85);
		setOptionDefault('colorbox_garland_image', 1);
		setOptionDefault('colorbox_garland_search', 1);
  }

  function getOptionsSupported() {
  	return array(	gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Set to enable search form.')),
						  						gettext('Allow cloud') => array('key' => 'Allow_cloud', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Set to enable tag cloud for album page.'))
                          );
  }
}
?>