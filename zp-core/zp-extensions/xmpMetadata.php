<?php
/**
 *
 * Enable this filter to scan images (or xmp sidecar files) for metadata.
 *
 * Relevant metadata found will be incorporated into the image (or album object)
 * see “Adobe XMP Specification" http://www.aiim.org/documents/standards/xmpspecification.pdf
 * for xmp metadata description. This plugin attempts to map the xmp metadata to IPTC fields
 *
 * If a sidecar file exists, it will take precedence (the image file will not be
 * examined.) The sidecar file should reside in the same folder, have the same prefix name as the
 * image (album), and the suffix ".xmp". Thus, the sidecar for <image>.jpg would be named <image>.xmp.
 *
 * NOTE: dynamic albums have an ".alb" suffix. Append ".xmp" to that name so
 * that the dynamic album sidecar would be named <album>.alb.xmp
 *
 * There is one option for this plugin--to enable searching within the actual image file for
 * an xmp block. This is disabled by default scanning image files can add considerably to the
 * processing time.
 *
 * All functions within this plugin are for internal use. The plugin does not present any
 * theme interface.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('Extracts <em>XMP</em> metadata from images and <code>XMP</code> sidecar files.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$option_interface = 'xmpMetadata_options';

zp_register_filter('album_instantiate', 'xmpMetadata_album_instantiate');
zp_register_filter('new_album', 'xmpMetadata_new_album');
zp_register_filter('album_refresh', 'xmpMetadata_new_album');
zp_register_filter('image_instantiate', 'xmpMetadata_image_instantiate');
zp_register_filter('image_metadata', 'xmpMetadata_new_image');
zp_register_filter('upload_filetypes', 'xmpMetadata_sidecars');

require_once(dirname(dirname(__FILE__)).'/exif/exif.php');

/**
 * Plugin option handling class
 *
 */
class xmpMetadata_options {

	/**
	 * Class instantiation function
	 *
	 * @return xmpMetadata_options
	 */
	function xmpMetadata_options() {
		setOptionDefault('xmpMetadata_suffix','xmp');
	}

	/**
	 * Option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_supported_images, $_zp_extra_filetypes;
		$list = $_zp_supported_images;
		foreach (array('gif','bmp') as $suffix) {
			$key = array_search($suffix, $list);
			if ($key !== false)	unset($list[$key]);
		}
		natcasesort($list);
		$types = array();
		foreach ($_zp_extra_filetypes as $suffix=>$type) {
			if ($type == 'Video') $types[] = $suffix;
		}
		natcasesort($types);
		$list = array_merge($list, $types);
		$listi = array();
		foreach ($list as $suffix) {
			$listi[$suffix] = 'xmpMetadata_examine_images_'.$suffix;
		}
		return array(	gettext('Sidecar file extension') => array('key' => 'xmpMetadata_suffix', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The plugin will look for files with <em>image_name.extension</em> and extract XMP metadata from them into the <em>image_name</em> record.')),
									gettext('Process extensions') => array('key' => 'xmpMetadata_examine_imagefile', 'type' => OPTION_TYPE_CHECKBOX_UL,
										'checkboxes' => $listi,
										'desc' => gettext('If no sidecar file exists and the extension is enabled, the plugin will search within that type <em>image</em> file for an <code>XMP</code> block. <strong>Warning</strong> do not set this option unless you require it. Searching image files can be computationally intensive.'))
		);
	}

	/**
	 * Custom option handler
	 *
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {
	}
}

define('XMP_EXTENSION',strtolower(getOption('xmpMetadata_suffix')));
/**
 * Parses xmp metadata for interesting tags
 *
 * @param string $xmpdata
 * @return array
 */
function xmpMetadata_extract($xmpdata) {
	$desiredtags = array(
		'EXIFLensType'					=>	'<aux:Lens>',
		'EXIFLensInfo'					=>	'<aux:LensInfo>',
		'EXIFArtist'						=>	'<dc:creator>',
		'IPTCCopyright'					=>	'<dc:rights>',
		'EXIFDescription'				=>	'<dc:description>',
		'IPTCObjectName'				=>	'<dc:title>',
		'IPTCKeywords'  				=>	'<dc:subject>',
		'EXIFExposureTime'			=>	'<exif:ExposureTime>',
		'EXIFFNumber'						=>	'<exif:FNumber>',
		'EXIFAperatureValue'		=>	'<exif:ApertureValue>',
		'EXIFExposureProgram'		=>	'<exif:ExposureProgram>',
		'EXIFISOSpeedRatings'		=>	'<exif:ISOSpeedRatings>',
		'EXIFDateTimeOriginal'	=>	'<exif:DateTimeOriginal>',
		'EXIFExposureBiasValue'	=>	'<exif:ExposureBiasValue>',
		'EXIFGPSLatitude'				=>	'<exif:GPSLatitude>',
		'EXIFGPSLongitude'			=>	'<exif:GPSLongitude>',
		'EXIFGPSAltitude'				=>	'<exif:GPSAltitude>',
		'EXIFGPSAltituedRef'		=>	'<exif:GPSAltitudeRef>',
		'EXIFMeteringMode'			=>	'<exif:MeteringMode>',
		'EXIFFocalLength'				=>	'<exif:FocalLength>',
		'EXIFContrast'					=>	'<exif:Contrast>',
		'EXIFSharpness'					=>	'<exif:Sharpness>',
		'EXIFExposureTime'			=>	'<exif:ShutterSpeedValue>',
		'EXIFSaturation'				=>	'<exif:Saturation>',
		'EXIFWhiteBalance'			=>	'<exif:WhiteBalance>',
		'IPTCLocationCode' 			=>	'<Iptc4xmpCore:CountryCode>',
		'IPTCSubLocation' 			=>	'<Iptc4xmpCore:Location>',
		'IPTCSource'						=>	'<photoshop:Source>',
		'IPTCCity' 							=>	'<photoshop:City>',
		'IPTCState' 						=>	'<photoshop:State>',
		'IPTCLocationName' 			=>	'<photoshop:Country>',
		'IPTCImageHeadline'  		=>	'<photoshop:Headline>',
		'IPTCImageCredit' 			=>	'<photoshop:Credit>',
		'EXIFMake'							=>	'<tiff:Make>',
		'EXIFModel'							=>	'<tiff:Model>',
		'EXIFOrientation'				=>	'<tiff:Orientation>',
		'EXIFImageWidth'				=>	'<tiff:ImageWidth>',
		'EXIFImageHeight'				=>	'<tiff:ImageLength>'
	);
	$xmp_parsed = array();
	while (!empty($xmpdata)) {
		$s = strpos($xmpdata, '<');
		$e = strpos($xmpdata,'>',$s);
		$tag = substr($xmpdata,$s,$e-$s+1);
		$xmpdata = substr($xmpdata,$e+1);
		$key = array_search($tag,$desiredtags);
		if ($key !== false) {
			$close = str_replace('<','</',$tag);
			$e = strpos($xmpdata,$close);
			$meta = trim(substr($xmpdata,0,$e));
			$xmpdata = substr($xmpdata,$e+strlen($close));
			if (strpos($meta, '<') === false) {
				$xmp_parsed[$key] = $meta;
			} else {
				$elements = array();
				while (!empty($meta)) {
					$s = strpos($meta, '<');
					$e = strpos($meta,'>',$s);
					$tag = substr($meta,$s,$e-$s+1);
					$meta = substr($meta,$e+1);
					if (strpos($tag,'rdf:li') !== false) {
						$e = strpos($meta,'</rdf:li>');
						$elements[] = trim(substr($meta, 0, $e));
						$meta = substr($meta,$e+9);
					}
				}
				$xmp_parsed[$key] = $elements;
			}
		} else {	// look for shorthand elements
			if (strpos($tag,'<rdf:Description')!==false) {
				$meta = substr($tag, 17);	// strip off the description tag leaving the elements
				while (preg_match('/^[a-zA-z0-9_]+\:[a-zA-z0-9_]+\=".*"/', $meta, $element)) {
						$item = $element[0];
						$meta = trim(substr($meta, strlen($item)));
						$i = strpos($item,'=');
						$tag = '<'.substr($item,0,$i).'>';
						$v = substr($item,$i+2,-1);
						$key = array_search($tag,$desiredtags);
						if ($key !== false) {
							$xmp_parsed[$key] = $v;
						}
					}
			}
		}
	}
	return ($xmp_parsed);
}

/**
 * insures that the metadata is a string
 *
 * @param mixed $meta
 * @return string
 */
function xmpMetadata_to_string($meta) {
	if (is_array($meta)) {
		$meta = implode(',',$meta);
	}
	return trim($meta);
}

/**
 * Filter called when an album object is instantiated
 * sets the sidecars to include xmp files
 *
 * @param $album album object
 * @return $object
 */
function xmpMetadata_album_instantiate($album) {
	$album->sidecars[XMP_EXTENSION] = XMP_EXTENSION;
	return $album;
}

/**
 * Filter for handling album objects
 *
 * @param object $album
 * @return object
 */
function xmpMetadata_new_album($album) {
	$metadata_path = dirname($album->localpath).'/'.basename($album->localpath).'*';
	$files = safe_glob($metadata_path);
	if (count($files)>0) {
		foreach ($files as $file) {
			if (strtolower(getSuffix($file)) == XMP_EXTENSION) {
				$source = file_get_contents($file);
				$metadata = xmpMetadata_extract($source);
				if (array_key_exists('EXIFDescription',$metadata)) {
					$album->setDesc(xmpMetadata_to_string($metadata['EXIFDescription']));
				}
				if (array_key_exists('IPTCImageHeadline',$metadata)) {
					$album->setTitle(xmpMetadata_to_string($metadata['IPTCImageHeadline']));
				}
				if (array_key_exists('IPTCLocationName',$metadata)) {
					$album->setLocation(xmpMetadata_to_string($metadata['IPTCLocationName']));
				}
				if (array_key_exists('IPTCKeywords',$metadata)) {
					$album->setTags(xmpMetadata_to_string($metadata['IPTCKeywords']));
				}
				if (array_key_exists('EXIFDateTimeOriginal',$metadata)) {
					$album->setDateTime($metadata['EXIFDateTimeOriginal']);
				}

				$album->save();
				break;
			}
		}
		return $album;
	}
}

/**
 * Finds and returns xmp metadata
 *
 * @param int $j
 * @return string
 */
function extractXMP($f) {
	if (preg_match('~<.*?xmpmeta~',$f, $m)) {
		$open = $m[0];
		$close = str_replace('<','</',$open);
		$j = strpos($f, $open);
		if ($j !== false) {
			$k = strpos($f, $close,$j+4);
			$meta = substr($f, $j, $k+14-$j);
			$l = 0;
			return $meta;
		}
	}
	return false;
}

/**
 * convert a fractional representation to something more user friendly
 *
 * @param $element string
 * @return string
 */
function rationalNum($element) {
	// deal with the fractional representation
	$n = explode('/',$element);
	$v = sprintf('%f', $n[0]/$n[1]);
	for ($i=strlen($v)-1;$i>1;$i--) {
		if (substr($v,$i,1) != '0') break;
	}
	if (substr($v,$i,1)=='.') $i--;
	return substr($v,0,$i+1);
}

function xmpMetadata_image_instantiate($image) {
	$image->sidecars[XMP_EXTENSION] = XMP_EXTENSION;
	return $image;
}

/**
 * Filter for handling image objects
 *
 * @param object $image
 * @return object
 */
function xmpMetadata_new_image($image) {
	global $_zp_exifvars;
	$source = '';
	$metadata_path = '';
	$files = safe_glob(substr($image->localpath, 0, strrpos($image->localpath, '.')).'.*');
	if (count($files)>0) {
		foreach ($files as $file) {
			if (strtolower(getSuffix($file)) == XMP_EXTENSION) {
				$metadata_path = $file;
				break;
			}
		}
	}
	if (!empty($metadata_path)) {
		$source = extractXMP(file_get_contents($metadata_path));
	} else if (getOption('xmpMetadata_examine_images_'.strtolower(substr(strrchr($image->localpath, "."), 1)))) {
		$f = file_get_contents($image->localpath);
		$l = filesize($image->localpath);
		$abort = 0;
		$i = 0;
		while ($i<$l && $abort<200 && !$source) {
			$tag = bin2hex(substr($f,$i,2));
			$size = hexdec(bin2hex(substr($f,$i+2,2)));
			switch ($tag) {
				case 'ffe1': // EXIF
				case 'ffe2': // EXIF extension
				case 'fffe': // COM
				case 'ffe0': // IPTC marker
					$source = extractXMP($f);
					$i = $i + $size+2;
					$abort = 0;
					break;
				default:
					if (substr($f,$i,1)=='<') {
						$source = extractXMP($f);
					}
					$i=$i+1;
					$abort++;
					break;
			}
		}
	}
	if (!empty($source)) {
		$metadata = xmpMetadata_extract($source);
		$image->set('hasMetadata',count($metadata>0));
		foreach ($metadata as $field=>$element) {
			$v = xmpMetadata_to_string($element);
			switch ($field) {
				case 'EXIFDateTimeOriginal':
					$image->setDateTime($element);
					break;
				case 'IPTCImageCaption':
					$image->setDesc($v);
					break;
				case 'IPTCCity':
					$image->setCity($v);
					break;
				case 'IPTCState':
					$image->setState($v);
					break;
				case 'IPTCLocationName':
					$image->setCountry($v);
					break;
				case 'IPTCSubLocation':
					$image->setLocation($v);
					break;
				case 'EXIFExposureTime':
					$v = formatExposure(rationalNum($element));
					break;
				case 'EXIFFocalLength':
					$v = rationalNum($element).' mm';
					break;
				case 'EXIFAperatureValue':
				case 'EXIFFNumber':
					$v = 'f/'.rationalNum($element);
					break;
				case 'EXIFExposureBiasValue':
				case 'EXIFGPSAltitude':
					$v = rationalNum($element);
					break;
				case 'EXIFGPSLatitude':
				case 'EXIFGPSLongitude':
					$ref = substr($element,-1,1);
					$image->set($field.'Ref', $ref);
					$element = substr($element,0,-1);
					$n = explode(',',$element);
					if (count($n)==3) {
						$v = $n[0]+($n[1]+($n[2]/60)/60);
					} else {
						$v = $n[0]+$n[1]/60;
					}
					break;
				case 'IPTCKeywords':
					$image->setTags($element);
					break;
			}
			if (array_key_exists($field,$_zp_exifvars)) {
				$image->set($field, $v);
			}
		}
		$image->save();
	}
	return $image;
}

function xmpMetadata_sidecars($types) {
	$types[] = XMP_EXTENSION;
	return $types;
}
?>