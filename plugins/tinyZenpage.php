<?php

/*
 * Legacy tinyZenpage plugin
 *
 * <var>tinyZenpage</var> was replaced by {@link %FULLWEBPATH%/docs/release%20notes.htm#tinyMCE:obj tinyMCE:obj} as the normal means to paste objects
 * into your <i>tinyMCE</i> enabled text fields. <var>tinyMCE:obj</var> follows conventiaonal windowed
 * application methodology rather than the convoluted menus of <i>tinyZenpage</i>. It also
 * provides full visual selection of image sizes and image cropping.
 *
 * This plugin will enable the legacy <var>tinyZenpage</i> object insertion feature.

 * @package plugins
 * @subpackage admin
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Enable legacy tinyZenpage.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'tinyZenpage';

class tinyZenpage {

	function getOptionsSupported() {
		$options = array(
						gettext('Custom image size')					 => array('key'	 => 'tinymce_tinyzenpage_customimagesize', 'type' => OPTION_TYPE_NUMBER,
										'desc' => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
						gettext('Custom image size')					 => array('key'	 => 'tinymce_tinyzenpage_customimagesize', 'type' => OPTION_TYPE_NUMBER,
										'desc' => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
						gettext('Custom thumb crop - size')		 => array('key'	 => 'tinymce_tinyzenpage_customthumb_size', 'type' => OPTION_TYPE_NUMBER,
										'desc' => gettext("Predefined size (px) for custom cropped thumb images included using tinyZenpage.")),
						gettext('Custom thumb crop - width')	 => array('key'	 => 'tinymce_tinyzenpage_customthumb_cropwidth', 'type' => OPTION_TYPE_NUMBER,
										'desc' => gettext("Predefined crop width (%) for custom cropped thumb  images included using tinyZenpage.")),
						gettext('Custom thumb crop - height')	 => array('key'	 => 'tinymce_tinyzenpage_customthumb_cropheight', 'type' => OPTION_TYPE_NUMBER,
										'desc' => gettext("Predefined crop height (%) for custom cropped thumb images included using tinyZenpage."))
		);
		return $options;
	}

}
