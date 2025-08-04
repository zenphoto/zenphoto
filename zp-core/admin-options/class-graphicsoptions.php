<?php
/**
 * Option class for graphics handlers 
 * 
 * @since 1.6
 * 
 * @package zpcore\classes\graphics
 */
class graphicsOptions {

	public static $ignore_size = 0;
	public static $info = array();
	
	function __construct() {
		global $_zp_graphics, $_zp_gd_present, $_zp_imagick_present, $_zp_imagick_version_pass;
		setOptionDefault('graphicslib_selected', 'gd', true);
		if ($_zp_graphics->imagick_version_pass) {
			setOptionDefault('magick_max_height', self::$ignore_size);
			setOptionDefault('magick_max_width', self::$ignore_size);

			if (!sanitize_numeric(getOption('magick_max_height'))) {
				setOption('magick_max_height', self::$ignore_size);
			}

			if (!sanitize_numeric(getOption('magick_max_width'))) {
				setOption('magick_max_width', self::$ignore_size);
			}
		}
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_graphics, $_zp_gd_present, $_zp_imagick_present;
		$options = array();
		
		if (!$_zp_graphics->imagick_present) {
			setOption('graphicslib_selected', 'gd', true);
		}
		$lib_buttons = array();
		if ($_zp_graphics->gd_present) {
			$lib_buttons["GDlibrary"] = 'gd';
		}
		if ($_zp_graphics->imagick_present) {
			$lib_buttons["Imagick"] = 'imagick';
		}
		$options[gettext('Select graphics library')] = array(
				'key' => 'graphicslib_selected',
				'type' => OPTION_TYPE_RADIO,
				'buttons' => $lib_buttons,
				'order' => 0,
				'desc' => gettext('Select the graphics library you wish to use for image processing and handling.')
		);
		if (defined('GD_FREETYPE') && GD_FREETYPE) {
			$options += array(
					gettext('GD TypeFace path') => array(
							'key' => 'GD_FreeType_Path',
							'type' => OPTION_TYPE_TEXTBOX,
							'desc' => gettext('Supply the full path to your TrueType fonts.'))
			);
		}
		if (getOption('graphicslib_selected') == 'imagick' && $_zp_graphics->imagick_present) {
			$options += array(
					gettext('Max height') => array(
							'key' => 'magick_max_height',
							'type' => OPTION_TYPE_TEXTBOX,
							'order' => 1,
							'desc' => sprintf(gettext('The maximum height used by the site for processed images. Set to %d for unconstrained. Default is <strong>%d</strong>'), self::$ignore_size, self::$ignore_size)
					),
					gettext('Max width') => array(
							'key' => 'magick_max_width',
							'type' => OPTION_TYPE_TEXTBOX,
							'order' => 2,
							'desc' => sprintf(gettext('The maximum width used by the site for processed images. Set to %d for unconstrained. Default is <strong>%d</strong>.'), self::$ignore_size, self::$ignore_size)
					)
			);
		}
		return $options;
	}

	function canLoadMsg() {
		global $_zp_graphics, $_zp_gd_present, $_zp_imagick_present, $_zp_imagemagick_version_pass;
		$messages = array();
		if ($_zp_graphics->imagick_present) {
			if (!$_zp_graphics->imagick_version_pass) {
				$messages[] = sprintf(gettext('The <strong><em>Imagick</em></strong> library version must be <strong>%s</strong> or later.'), IMAGICK_REQUIRED_VERSION);
			}
			if (!$_zp_graphics->imagemagick_version_pass) {
				$messages[] = sprintf(gettext('The <strong><em>ImageMagick</em></strong> binary version must be <strong>%s</strong> or later.'), IMAGEMAGICK_REQUIRED_VERSION);
			}
		} else {
			$messages[] = gettext('The <strong><em>Imagick</em></strong> extension is not available.');
		}
		if (!$_zp_graphics->gd_present) {
			$messages[] = gettext('The <strong><em>GD</em></strong> extension is not available.');
		}
		return $messages;
	}

}