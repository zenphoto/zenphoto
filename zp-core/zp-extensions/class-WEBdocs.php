<?php

/**
 * Plugin handler for certain standard document formats.
 * These are displayed a <i>WEBdocs</i> provider e.g. <i>Google Docs viewer</i>. The item is displayed in an iFrame sized based on the image size option.
 * Of course, your site must be accessable by provider and your viewer must have a any required account credentials for this to work.
 *
 * The plugin is an extension of <var>TextObject</var>. For more details see the <i>class-textobject</i> plugin.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage media
 *
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext('Provides a means for showing documents using <em>WEBdocs</em> for the document rendering.');
$plugin_author = "Stephen Billard (sbillard)";


$option_interface = 'WEBdocs_Options';

if (getOption('WEBdocs_pdf_provider')) {
	addPluginType('pdf', 'WEBdocs');
}
if (getOption('WEBdocs_pps_provider')) {
	addPluginType('pps', 'WEBdocs');
	addPluginType('ppt', 'WEBdocs');
}
if (getOption('WEBdocs_tif_provider')) {
	addPluginType('tif', 'WEBdocs');
	addPluginType('tiff', 'WEBdocs');
}

/**
 * Option class for textobjects objects
 *
 */
class WEBdocs_Options {

	function WEBdocs_Options() {
		setOptionDefault('WEBdocs_pdf_provider', 'local');
		setOptionDefault('WEBdocs_pps_provider', 'google');
		setOptionDefault('WEBdocs_tif_provider', 'zoho');
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Watermark default images')	 => array('key'	 => 'WEBdocs_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Check to place watermark image on default thumbnail images.')),
						gettext('PDF')											 => array('key'			 => 'WEBdocs_pdf_provider', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('Disabled')				 => '',
														gettext('GoogleDocs')			 => 'google',
														gettext('Zoho')						 => 'zoho',
														gettext('Browser default') => 'local'
										),
										'desc'		 => gettext("Choose the WEB service to use for rendering pdf documents.") .
										'<p>' . sprintf(gettext('Select <em>google</em> to use the <a href="%s">GoogleDocs viewer</a>'), 'http://docs.google.com/viewer') . '</p>' .
										'<p>' . sprintf(gettext('Select <em>zoho</em> to use the <a href="%s">Zoho document viewer</a>'), 'http://viewer.zoho.com/home.do') . '</p>' .
										'<p>' . gettext('Select <em>Browser default</em> to use the your browser default application') . '</p>'
						),
						gettext('PowerPoint')								 => array('key'			 => 'WEBdocs_pps_provider', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('Disabled')				 => '',
														gettext('GoogleDocs')			 => 'google',
														gettext('Zoho')						 => 'zoho',
														gettext('Browser default') => 'local'
										),
										'desc'		 => gettext("Choose the WEB service to use for rendering PowerPoint document.")),
						gettext('Tiff')											 => array('key'			 => 'WEBdocs_tif_provider', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('Disabled')				 => '',
														gettext('Zoho')						 => 'zoho',
														gettext('Browser default') => 'local'
										),
										'desc'		 => gettext("Choose the WEB service to use for rendering TIFF images."))
		);
	}

}

require_once(dirname(__FILE__) . '/class-textobject/class-textobject_core.php');

class WEBdocs extends TextObject {

	/**
	 * creates a WEBdocs (image standin)
	 *
	 * @param object $album the owner album
	 * @param string $filename the filename of the text file
	 * @return TextObject
	 */
	function __construct($album, $filename, $quiet = false) {

		$this->watermark = getOption('WEBdocs_watermark');
		$this->watermarkDefault = getOption('WEBdocs_watermark_default_images');

		$this->common_instantiate($album, $filename, $quiet);
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return s
	 */
	function getThumbImageFile($path = NULL) {
		global $_zp_gallery;
		if (is_null($path)) {
			$path = SERVERPATH;
		}
		if (is_null($this->objectsThumb)) {
			switch (getSuffix($this->filename)) {
				case "pdf":
					$img = '/pdfDefault.png';
					break;
				case 'ppt':
				case 'pps':
					$img = '/ppsDefault.png';
					break;
				case 'tif':
				case 'tiff':
					$img = '/tifDefault.png';
					break;
			}
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images/' . $img;
			if (!file_exists($imgfile)) {
				$imgfile = $path . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . substr(basename(__FILE__), 0, -4) . '/' . $img;
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Returns the content of the text file
	 *
	 * @param int $w optional width
	 * @param int $h optional height
	 * @return string
	 */
	function getBody($w = NULL, $h = NULL) {
		$this->updateDimensions();
		if (is_null($w))
			$w = $this->getWidth();
		if (is_null($h))
			$h = $this->getHeight();
		$providers = array(''			 => '<img src="' . html_encode(pathurlencode($this->getThumb())) . '">',
						'google' => '<iframe src="http://docs.google.com/viewer?url=%s&amp;embedded=true" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto"></iframe>',
						'zoho'	 => '<iframe src="http://viewer.zoho.com/api/urlview.do?url=%s&amp;embed=true" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto"></iframe>',
						'local'	 => '<iframe src="%s" width="' . $w . 'px" height="' . $h . 'px" frameborder="0" border="none" scrolling="auto"></iframe>'
		);
		switch ($suffix = getSuffix($this->filename)) {
			case 'ppt':
				$suffix = 'pps';
			case 'tiff':
				$suffix = substr($suffix, 0, 3);
			case 'tif':
			case 'pps':
			case 'pdf':
				$provider = 'WEBdocs_' . $suffix . '_provider';
				return sprintf($providers[getOption($provider)], html_encode($this->getFullImage(FULLWEBPATH)));
			default: // just in case we extend and are lazy...
				return '<img src="' . html_encode(pathurlencode($this->getThumb())) . '">';
		}
	}

}

?>