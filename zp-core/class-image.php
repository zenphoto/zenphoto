<?php

/**
 * Image Class
 * @package classes
 */
// force UTF-8 Ø

define('WATERMARK_IMAGE', 1);
define('WATERMARK_THUMB', 2);
define('WATERMARK_FULL', 4);

/**
 * Returns a new "image" object based on the file extension
 *
 * @param object $album the owner album
 * @param string $filename the filename
 * @param bool $quiet set true to supress error messages (used by loadimage)
 * @return object
 */
function newImage($album, $filename = NULL, $quiet = false) {
	global $_zp_missing_image;
	if (is_array($album)) {
		$xalbum = newAlbum($album['folder'], true, true);
		$filename = $album['filename'];
		$dyn = false;
	} else if (is_array($filename)) {
		$xalbum = newAlbum($filename['folder'], true, true);
		$filename = $filename['filename'];
		$dyn = is_object($album) && $album->isDynamic();
	} else if (is_object($album) && $album->isDynamic()) {
		$dyn = true;
		$album->getImages();
		$xalbum = array_keys($album->imageNames, $filename);
		$xalbum = array_shift($xalbum);
		$xalbum = newAlbum(dirname($xalbum), true, true);
	} else {
		$xalbum = $album;
		$dyn = false;
	}
	if (!is_object($xalbum) || !$xalbum->exists || !isAlbumClass($xalbum)) {
		$msg = sprintf(gettext('Bad album object parameter to newImage(%s)'), $filename);
	} else {
		if ($object = Gallery::imageObjectClass($filename)) {
			$image = New $object($xalbum, $filename, $quiet);
			if ($album && is_subclass_of($album, 'AlbumBase') && $dyn) {
				$image->albumname = $album->name;
				$image->albumlink = $album->linkname;
				$image->albumnamealbum = $album;
			}
			zp_apply_filter('image_instantiate', $image);
			if ($image->exists) {
				return $image;
			}
			return $_zp_missing_image;
		}
		$msg = sprintf(gettext('Bad filename suffix in newImage(%s)'), $filename);
	}
	if (!$quiet) {
		debugLogBacktrace($msg);
	}
	return $_zp_missing_image;
}

/**
 * Returns true if the object is a zenphoto 'image'
 *
 * @param object $image
 * @return bool
 */
function isImageClass($image) {
	return is_object($image) && ($image->table == 'images');
}

/**
 * handles 'picture' images
 */
class Image extends MediaObject {

	var $filename; // true filename of the image.
	var $exists = true; // Does the image exist?
	var $webpath; // The full URL path to the original image.
	var $localpath; // Latin1 full SERVER path to the original image.
	var $displayname; // $filename with the extension stripped off.
	var $album; // An album object for the album containing this image.
	var $albumname; // The name of the album for which this image was instantiated. (MAY NOT be $this->album->name!!!!).
	var $albumnamealbum; //	An album object representing the above;
	var $albumlink; // "rewrite" verwion of the album name, eg. may not have the .alb
	var $imagefolder; // The album folder containing the image (May be different from the albumname!!!!)
	protected $index; // The index of the current image in the album array.
	protected $sortorder; // The position that this image should be shown in the album
	var $filemtime; // Last modified time of this image
	var $sidecars = array(); // keeps the list of suffixes associated with this image
	var $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	var $manage_some_rights = ALBUM_RIGHTS;
	var $access_rights = ALL_ALBUMS_RIGHTS;
	// Plugin handler support
	var $objectsThumb = NULL; // Thumbnail image for the object

	/**
	 * Constructor for class-image
	 *
	 * Do not call this constructor directly unless you really know what you are doing!
	 * Use instead the function newImage() which will instantiate an object of the
	 * correct class for the file type.
	 *
	 * @param object &$album the owning album
	 * @param sting $filename the filename of the image
	 * @return Image
	 */

	function __construct($album, $filename, $quiet = false) {
		global $_zp_current_admin_obj;
		// $album is an Album object; it should already be created.
		$msg = $this->invalid($album, $filename);
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				debugLogBacktrace($msg);
			}
			return;
		}

		// This is where the magic happens...
		$album_name = $album->name;
		$new = $this->instantiate('images', array('filename' => $filename, 'albumid' => $this->album->getID()), 'filename', false, empty($album_name));
		$this->checkForPublish();
		if ($new || $this->filemtime != $this->get('mtime')) {
			if ($new) {
				$this->setTitle($this->displayname);
			}
			$this->updateMetaData(); // extract info from image
			$this->updateDimensions(); // deal with rotation issues
			$this->set('mtime', $this->filemtime);
			$this->save();
			if ($new)
				zp_apply_filter('new_image', $this);
		}
	}

	/**
	 *
	 * "Magic" function to return a string identifying the object when it is treated as a string
	 * @return string
	 */
	public function __toString() {
		return $this->filename;
	}

	/**
	 * Comon validity check function
	 *
	 * @param type $album
	 * @param type $filename
	 * @return string
	 */
	function invalid($album, $filename) {
		$msg = false;
		if (!is_object($album) || !$album->exists) {
			$msg = sprintf(gettext('Invalid %s instantiation: Album does not exist'), get_class($this)) . ' (' . $album . ')';
		} else if (!$this->classSetup($album, $filename) || !file_exists($this->localpath) || is_dir($this->localpath)) {
			$msg = sprintf(gettext('Invalid %s instantiation: file does not exist'), get_class($this)) . ' (' . $album . '/' . $filename . ')';
		}
		return $msg;
	}

	/**
	 * returns the database fields used by the object
	 * @return array
	 *
	 * @author Stephen Billard
	 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
	 */
	static function getMetadataFields() {
		return array(
				// Database Field       		 => array(0:'source', 1:'Metadata Key', 2;'ZP Display Text', 3:Display?	4:size,	5:enabled, 6:type, 7:linked)
				'EXIFMake' => array('IFD0', 'Make', gettext('Camera Maker'), true, 52, true, 'string', false),
				'EXIFModel' => array('IFD0', 'Model', gettext('Camera Model'), true, 52, true, 'string', false),
				'EXIFDescription' => array('IFD0', 'ImageDescription', gettext('Image Title'), false, 52, true, 'string', false),
				'IPTCObjectName' => array('IPTC', 'ObjectName', gettext('Object Name'), false, 256, true, 'string', false),
				'IPTCImageHeadline' => array('IPTC', 'ImageHeadline', gettext('Image Headline'), false, 256, true, 'string', false),
				'IPTCImageCaption' => array('IPTC', 'ImageCaption', gettext('Image Caption'), false, 2000, true, 'string', false),
				'IPTCImageCaptionWriter' => array('IPTC', 'ImageCaptionWriter', gettext('Image Caption Writer'), false, 32, true, 'string', false),
				'EXIFDateTime' => array('SubIFD', 'DateTime', gettext('Time Taken'), true, 52, true, 'time', false),
				'EXIFDateTimeOriginal' => array('SubIFD', 'DateTimeOriginal', gettext('Original Time Taken'), true, 52, true, 'time', false),
				'EXIFDateTimeDigitized' => array('SubIFD', 'DateTimeDigitized', gettext('Time Digitized'), true, 52, true, 'time', false),
				'IPTCDateCreated' => array('IPTC', 'DateCreated', gettext('Date Created'), false, 8, true, 'time', false),
				'IPTCTimeCreated' => array('IPTC', 'TimeCreated', gettext('Time Created'), false, 11, true, 'time', false),
				'IPTCDigitizeDate' => array('IPTC', 'DigitizeDate', gettext('Digital Creation Date'), false, 8, true, 'time', false),
				'IPTCDigitizeTime' => array('IPTC', 'DigitizeTime', gettext('Digital Creation Time'), false, 11, true, 'time', false),
				'EXIFArtist' => array('IFD0', 'Artist', gettext('Artist'), false, 52, true, 'string', false),
				'IPTCImageCredit' => array('IPTC', 'ImageCredit', gettext('Image Credit'), false, 32, true, 'string', false),
				'IPTCByLine' => array('IPTC', 'ByLine', gettext('Byline'), false, 32, true, 'string', false),
				'IPTCByLineTitle' => array('IPTC', 'ByLineTitle', gettext('Byline Title'), false, 32, true, 'string', false),
				'IPTCSource' => array('IPTC', 'Source', gettext('Image Source'), false, 32, true, 'string', false),
				'IPTCContact' => array('IPTC', 'Contact', gettext('Contact'), false, 128, true, 'string', false),
				'EXIFCopyright' => array('IFD0', 'Copyright', gettext('Copyright Holder'), false, 128, true, 'string', false),
				'IPTCCopyright' => array('IPTC', 'Copyright', gettext('Copyright Notice'), false, 128, true, 'string', false),
				'IPTCKeywords' => array('IPTC', 'Keywords', gettext('Keywords'), false, 0, true, 'string', false),
				'EXIFExposureTime' => array('SubIFD', 'ExposureTime', gettext('Exposure time'), true, 52, true, 'string', false),
				'EXIFShutterSpeedValue' => array('SubIFD', 'ShutterSpeedValue', gettext('Shutter Speed'), true, 52, true, 'string', false),
				'EXIFFNumber' => array('SubIFD', 'FNumber', gettext('Aperture'), true, 20, true, 'string', false),
				'EXIFISOSpeedRatings' => array('SubIFD', 'ISOSpeedRatings', gettext('ISO Sensitivity'), true, 52, true, 'string', false),
				'EXIFExposureBiasValue' => array('SubIFD', 'ExposureBiasValue', gettext('Exposure Compensation'), true, 52, true, 'string', false),
				'EXIFMeteringMode' => array('SubIFD', 'MeteringMode', gettext('Metering Mode'), true, 52, true, 'string', false),
				'EXIFFlash' => array('SubIFD', 'Flash', gettext('Flash Fired'), true, 52, true, 'string', false),
				'EXIFImageWidth' => array('SubIFD', 'ExifImageWidth', gettext('Original Width'), false, 52, true, 'string', false),
				'EXIFImageHeight' => array('SubIFD', 'ExifImageHeight', gettext('Original Height'), false, 52, true, 'string', false),
				'EXIFOrientation' => array('IFD0', 'Orientation', gettext('Orientation'), false, 52, true, 'string', false),
				'EXIFSoftware' => array('IFD0', 'Software', gettext('Software'), false, 999, true, 'string', false),
				'EXIFContrast' => array('SubIFD', 'Contrast', gettext('Contrast Setting'), false, 52, true, 'string', false),
				'EXIFSharpness' => array('SubIFD', 'Sharpness', gettext('Sharpness Setting'), false, 52, true, 'string', false),
				'EXIFSaturation' => array('SubIFD', 'Saturation', gettext('Saturation Setting'), false, 52, true, 'string', false),
				'EXIFWhiteBalance' => array('SubIFD', 'WhiteBalance', gettext('White Balance'), false, 52, true, 'string', false),
				'EXIFSubjectDistance' => array('SubIFD', 'SubjectDistance', gettext('Subject Distance'), false, 52, true, 'string', false),
				'EXIFFocalLength' => array('SubIFD', 'FocalLength', gettext('Focal Length'), true, 20, true, 'string', false),
				'EXIFLensType' => array('SubIFD', 'LensType', gettext('Lens Type'), false, 52, true, 'string', false),
				'EXIFLensInfo' => array('SubIFD', 'LensInfo', gettext('Lens Info'), false, 52, true, 'string', false),
				'EXIFFocalLengthIn35mmFilm' => array('SubIFD', 'FocalLengthIn35mmFilm', gettext('35mm Focal Length Equivalent'), false, 52, true, 'string', false),
				'IPTCCity' => array('IPTC', 'City', gettext('City'), false, 32, true, 'string', false),
				'IPTCSubLocation' => array('IPTC', 'SubLocation', gettext('Sub-location'), false, 32, true, 'string', false),
				'IPTCState' => array('IPTC', 'State', gettext('Province/State'), false, 32, true, 'string', false),
				'IPTCLocationCode' => array('IPTC', 'LocationCode', gettext('Country/Primary Location Code'), false, 3, true, 'string', false),
				'IPTCLocationName' => array('IPTC', 'LocationName', gettext('Country/Primary Location Name'), false, 64, true, 'string', false),
				'IPTCContentLocationCode' => array('IPTC', 'ContentLocationCode', gettext('Content Location Code'), false, 3, true, 'string', false),
				'IPTCContentLocationName' => array('IPTC', 'ContentLocationName', gettext('Content Location Name'), false, 64, true, 'string', false),
				'EXIFGPSLatitude' => array('GPS', 'Latitude', gettext('Latitude'), false, 52, true, 'number', false),
				'EXIFGPSLatitudeRef' => array('GPS', 'Latitude Reference', gettext('Latitude Reference'), false, 52, false, 'string', 'EXIFGPSLatitude'),
				'EXIFGPSLongitude' => array('GPS', 'Longitude', gettext('Longitude'), false, 52, true, 'number', false),
				'EXIFGPSLongitudeRef' => array('GPS', 'Longitude Reference', gettext('Longitude Reference'), false, 52, false, 'string', 'EXIFGPSLongitude'),
				'EXIFGPSAltitude' => array('GPS', 'Altitude', gettext('Altitude'), false, 52, true, 'number', false),
				'EXIFGPSAltitudeRef' => array('GPS', 'Altitude Reference', gettext('Altitude Reference'), false, 52, false, 'string', 'EXIFGPSAltitude'),
				'IPTCOriginatingProgram' => array('IPTC', 'OriginatingProgram', gettext('Originating Program '), false, 32, true, 'string', false),
				'IPTCProgramVersion' => array('IPTC', 'ProgramVersion', gettext('Program Version'), false, 10, true, 'string', false)
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see PersistentObject::setDefaults()
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		$this->set('mtime', $this->filemtime);
		$this->updateDimensions(); // deal with rotation issues
		$this->setShow($_zp_gallery->getImagePublish());
	}

	/**
	 * generic "image" class setup code
	 * Returns true if valid image.
	 *
	 * @param object $album the images' album
	 * @param string $filename of the image
	 * @return bool
	 *
	 */
	protected function classSetup(&$album, $filename) {
		if (TEST_RELEASE) {
			$bt = debug_backtrace();
			$good = false;
			foreach ($bt as $b) {
				if ($b['function'] == "newImage") {
					$good = true;
					break;
				}
			}
			if (!$good) {
				debugLogBacktrace(gettext('An image object was instantiated without using the newImage() function.'));
			}
		}

		global $_zp_current_admin_obj;
		$fileFS = internalToFilesystem($filename);
		if ($filename != filesystemToInternal($fileFS)) { // image name spoof attempt
			return false;
		}
		$this->albumnamealbum = $this->album = &$album;
		if ($album->name == '') {
			$this->webpath = ALBUM_FOLDER_WEBPATH . $filename;
			$this->encwebpath = ALBUM_FOLDER_WEBPATH . rawurlencode($filename);
			$this->localpath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($filename);
		} else {
			$this->webpath = ALBUM_FOLDER_WEBPATH . $album->name . "/" . $filename;
			$this->encwebpath = ALBUM_FOLDER_WEBPATH . pathurlencode($album->name) . "/" . rawurlencode($filename);
			$this->localpath = $album->localpath . $fileFS;
		}
		$this->imagefolder = $this->albumlink = $this->albumname = $album->name;
		$this->filename = $filename;
		$this->displayname = substr($this->filename, 0, strrpos($this->filename, '.'));
		if (empty($this->displayname))
			$this->displayname = $this->filename;
		$this->comments = null;
		$this->filemtime = @filemtime($this->localpath);
		$this->imagetype = strtolower(get_class($this)) . 's';
		$date = $this->get('date');
		if (!$date || $date == '0000-00-00 00:00:00') {
			$this->set('date', strftime('%Y-%m-%d %H:%M:%S', $this->filemtime));
		}
		return true;
	}

	/**
	 * Returns the image filename
	 *
	 * @return string
	 */
	function getFileName() {
		return $this->filename;
	}

	/**
	 * Returns true if the file has changed since last time we looked
	 *
	 * @return bool
	 */
	protected function fileChanged() {
		$storedmtime = $this->get('mtime');
		return (empty($storedmtime) || $this->filemtime > $storedmtime);
	}

	/**
	 * Returns an array of EXIF data
	 *
	 * @return array
	 */
	function getMetaData() {
		global $_zp_exifvars;
		$exif = array();
		// Put together an array of EXIF data to return
		foreach ($_zp_exifvars as $field => $exifvar) {
			//	only enabled image metadata
			if ($_zp_exifvars[$field][EXIF_FIELD_ENABLED]) {
				$exif[$field] = $this->get($field);
			}
		}
		return $exif;
	}

	/**
	 * check if a metadata field should be used
	 * @global type $_zp_exifvars
	 * @param type $field
	 * @return type
	 */
	private function fetchMetadata($field) {
		global $_zp_exifvars;
		if ($_zp_exifvars[$field][EXIF_FIELD_ENABLED]) {
			return $this->get($field);
		} else {
			return NULL;
		}
	}

	/**
	 * Parses Exif/IPTC data
	 *
	 */
	function updateMetaData() {
		global $_zp_exifvars, $_zp_gallery;
		if ($_zp_exifvars) {
			require_once(dirname(__FILE__) . '/exif/exif.php');
			$IPTCtags = array(
					'SKIP' => '2#000', //	Record Version										Size:64
					'ObjectType' => '2#003', //	Object Type	Ref										Size:67
					'ObjectAttr' => '2#004', //	Object Attribute Ref							Size:67
					'ObjectName' => '2#005', //	Object name												Size:64
					'EditStatus' => '2#007', //	Edit Status												Size:64
					'EditorialUpdate' => '2#008', //	Editorial Update									Size:2
					'Urgency' => '2#010', //	Urgency														Size:1
					'SubRef' => '2#012', //	Subject	Reference									Size:236
					'Category' => '2#015', //	Category 													Size:3
					'SuppCategory' => '2#020', //	Supplemental category							Size:32
					'FixtureID' => '2#022', //	Fixture	ID 												Size:32
					'Keywords' => '2#025', //	Keywords 													Size:64
					'ContentLocationCode' => '2#026', //	Content	Location Code							Size:3
					'ContentLocationName' => '2#027', //	Content	Location Name							Size:64
					'ReleaseDate' => '2#030', //	Release	Date 											Size:8
					'ReleaseTime' => '2#035', //	Release	Time											Size:11
					'ExpireDate' => '2#037', //	Expiration Date										Size:8
					'ExpireTime' => '2#038', //	Expiration Time										Size:11
					'SpecialInstru' => '2#040', //	Special Instructions							Size:256
					'ActionAdvised' => '2#042', //	Action Advised										Size:2
					'RefService' => '2#045', //	Reference Service									Size:10
					'RefDate' => '2#047', //	Reference Date										Size:8
					'RefNumber' => '2#050', //	Reference Number									Size:8
					'DateCreated' => '2#055', //	Date created											Size:8
					'TimeCreated' => '2#060', //	Time created											Size:11
					'DigitizeDate' => '2#062', //	Digital Creation Date							Size:8
					'DigitizeTime' => '2#063', //	Digital Creation Time							Size:11
					'OriginatingProgram' => '2#065', //	Originating Program								Size:32
					'ProgramVersion' => '2#070', //	Program version										Size:10
					'ObjectCycle' => '2#075', //	Object Cycle											Size:1
					'ByLine' => '2#080', //	ByLine 														Size:32
					'ByLineTitle' => '2#085', //	ByLine Title											Size:32
					'City' => '2#090', //	City															Size:32
					'SubLocation' => '2#092', //	Sublocation												Size:32
					'State' => '2#095', //	Province/State										Size:32
					'LocationCode' => '2#100', //	Country/Primary	Location Code			Size:3
					'LocationName' => '2#101', //	Country/Primary	Location Name			Size:64
					'TransmissionRef' => '2#103', //	Original Transmission Reference		Size:32
					'ImageHeadline' => '2#105', //	Image headline										Size:256
					'ImageCredit' => '2#110', //	Image credit											Size:32
					'Source' => '2#115', //	Source														Size:32
					'Copyright' => '2#116', //	Copyright Notice									Size:128
					'Contact' => '2#118', //	Contact														Size:128
					'ImageCaption' => '2#120', //	Image caption											Size:2000
					'ImageCaptionWriter' => '2#122', //	Image caption writer							Size:32
					'ImageType' => '2#130', //	Image type												Size:2
					'Orientation' => '2#131', //	Image	orientation									Size:1
					'LangID' => '2#135', //	Language ID												Size:3
					'Subfile' => '8#010' //	Subfile														Size:2
			);
			$this->set('hasMetadata', 0);
			foreach ($_zp_exifvars as $field => $exifvar) {
				$this->set($field, NULL);
			}

			$result = array();
			if (get_class($this) == 'Image') {
				$localpath = $this->localpath;
			} else {
				$localpath = $this->getThumbImageFile();
			}

			if (!empty($localpath)) { // there is some kind of image to get metadata from
				$exifraw = read_exif_data_protected($localpath);
				if (isset($exifraw['ValidEXIFData'])) {
					$this->set('hasMetadata', 1);
					foreach ($_zp_exifvars as $field => $exifvar) {
						$exif = NULL;
						if (isset($exifraw[$exifvar[EXIF_SOURCE]][$exifvar[EXIF_KEY]])) {
							$exif = trim(sanitize($exifraw[$exifvar[EXIF_SOURCE]][$exifvar[EXIF_KEY]], 1));
						} else if (isset($exifraw[$exifvar[EXIF_SOURCE]]['MakerNote'][$exifvar[EXIF_KEY]])) {
							$exif = trim(sanitize($exifraw[$exifvar[EXIF_SOURCE]]['MakerNote'][$exifvar[EXIF_KEY]], 1));
						}
						$this->set($field, $exif);
					}
				}
				/* check IPTC data */
				$iptcdata = zp_imageIPTC($localpath);
				if (!empty($iptcdata)) {
					$iptc = iptcparse($iptcdata);
					if ($iptc) {
						$this->set('hasMetadata', 1);
						$characterset = self::getIPTCTag('1#090', $iptc);
						if (!$characterset) {
							$characterset = getOption('IPTC_encoding');
						} else if (substr($characterset, 0, 1) == chr(27)) { // IPTC escape encoding
							$characterset = substr($characterset, 1);
							if ($characterset == '%G') {
								$characterset = 'UTF-8';
							} else { // we don't know, need to understand the IPTC standard here. In the mean time, default it.
								$characterset = getOption('IPTC_encoding');
							}
						} else if ($characterset == 'UTF8') {
							$characterset = 'UTF-8';
						}
						// Extract IPTC fields of interest
						foreach ($_zp_exifvars as $field => $exifvar) {
							if ($exifvar[EXIF_SOURCE] == 'IPTC') {
								$datum = self::getIPTCTag($IPTCtags[$exifvar[EXIF_KEY]], $iptc);
								$this->set($field, $this->prepIPTCString($datum, $characterset));
							}
						}
						/* iptc keywords (tags) */
						if ($_zp_exifvars['IPTCKeywords'][EXIF_FIELD_ENABLED]) {
							$datum = self::getIPTCTagArray($IPTCtags['Keywords'], $iptc);
							if (is_array($datum)) {
								$tags = array();
								$result['tags'] = array();
								foreach ($datum as $item) {
									$tags[] = $this->prepIPTCString(sanitize($item, 3), $characterset);
								}
								$this->setTags($tags);
							}
						}
					}
				}
			}
			/* "import" metadata into database fields as makes sense */

			/* ZenPhoto20 Image Rotation */
			$this->set('rotation', substr(trim(self::fetchMetadata('EXIFOrientation'), '!'), 0, 1));

			/* ZenPhoto20 "date" field population */
			if ($date = self::fetchMetadata('IPTCDateCreated')) {
				if (strlen($date) > 8) {
					$time = substr($date, 8);
				} else {
					/* got date from IPTC, now must get time */
					$time = $this->get('IPTCTimeCreated');
				}
				$date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
				if (!empty($time)) {
					$date = $date . ' ' . substr($time, 0, 2) . ':' . substr($time, 2, 2) . ':' . substr($time, 4, 2);
				}
			}
			if (empty($date)) {
				$date = self::fetchMetadata('EXIFDateTime');
			}
			if (empty($date)) {
				$date = self::fetchMetadata('EXIFDateTimeOriginal');
			}
			if (empty($date)) {
				$date = self::fetchMetadata('EXIFDateTimeDigitized');
			}
			if (empty($date)) {
				$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->filemtime));
			} else {
				$this->setDateTime($date);
			}

			/* ZenPhoto20 "title" field population */
			$title = self::fetchMetadata('IPTCObjectName');
			if (empty($title)) {
				$title = self::fetchMetadata('IPTCImageHeadline');
			}
			if (empty($title)) {
				$title = self::fetchMetadata('EXIFDescription'); //EXIF title [sic]
			}

			if (!empty($title)) {
				if (getoption('transform_newlines')) {
					$title = nl2br($title);
				}
				$this->setTitle($title);
			}

			/* ZenPhoto20 "description" field population */
			$desc = self::fetchMetadata('IPTCImageCaption');
			if (!empty($desc)) {
				if (getoption('transform_newlines')) {
					$desc = nl2br($desc);
				}
				$this->setDesc($desc);
			}

			//	ZenPhoyo20 GPS data
			foreach (array('EXIFGPSLatitude', 'EXIFGPSLongitude') as $source) {
				$data = self::fetchMetadata($source);
				if (!empty($data)) {
					$ref = strtoupper($this->get($source . 'Ref'));
					$this->set($source, self::toDMS($data, $ref));
					if (in_array($ref, array('S', 'W'))) {
						$data = '-' . $data;
					}
					$this->set(substr($source, 4), $data);
				}
			}

			$alt = self::fetchMetadata('EXIFGPSAltitude');
			if (!empty($alt)) {
				if (self::fetchMetadata('EXIFGPSAltitudeRef') == '-') {
					$alt = -$alt;
				}
				$this->set('GPSAltitude', $alt);
			}

			//	simple field imports
			$import = array(
					'location' => 'IPTCSubLocation',
					'city' => 'IPTCCity',
					'state' => 'IPTCState',
					'country' => 'IPTCLocationName',
					'copyright' => 'IPTCCopyright'
			);
			foreach ($import as $key => $source) {
				$data = self::fetchMetadata($source);
				$this->set($key, $data);
			}

			/* ZenPhoto20 "credit" field population */
			$credit = self::fetchMetadata('IPTCByLine');
			if (empty($credit)) {
				$credit = self::fetchMetadata('IPTCImageCredit');
			}
			if (empty($credit)) {
				$credit = self::fetchMetadata('IPTCSource');
			}
			if (!empty($credit)) {
				$this->setCredit($credit);
			}

			zp_apply_filter('image_metadata', $this);

			$alb = $this->album;
			if (is_object($alb)) {
				if (!$this->get('owner')) {
					$this->setOwner($alb->getOwner());
				}
				$save = false;
				if (strtotime($alb->getUpdatedDate()) < strtotime($this->getDateTime())) {
					$alb->setUpdatedDate($this->getDateTime());
					$save = true;
				}
				if (is_null($albdate = $alb->getDateTime()) || ($_zp_gallery->getAlbumUseImagedate() && strtotime($albdate) < strtotime($this->getDateTime()))) {
					$alb->setDateTime($this->getDateTime()); //  not necessarily the right one, but will do. Can be changed in Admin
					$save = true;
				}
				if ($save) {
					$alb->save();
				}
			}
		}
	}

	static function toDMS($dec, $ref) {
		// strange things happen with locales, so best to be "separator blind"
		$d = preg_split('/[,\.]/', str_replace('-', '', $dec) . '.0');
		$tempma = $d[1] * pow(10, -strlen($d[1]));
		$tempma = $tempma * 3600;
		$min = floor($tempma / 60);
		$sec = $tempma - ($min * 60);
		return sprintf('%d° %d\' %d" %s', $d[0], $min, $sec, $ref);
	}

	/**
	 * Fetches a single tag from IPTC data
	 *
	 * @param string $tag the metadata tag sought
	 * @return string
	 */
	private static function getIPTCTag($tag, $iptc) {
		if (isset($iptc[$tag])) {
			$iptcTag = $iptc[$tag];
			$r = "";
			$ct = count($iptcTag);
			for ($i = 0; $i < $ct; $i++) {
				$w = $iptcTag[$i];
				if (!empty($r)) {
					$r .= ", ";
				}
				$r .= $w;
			}
			return trim($r);
		}
		return '';
	}

	/**
	 * Fetches the IPTC array for a single tag.
	 *
	 * @param string $tag the metadata tag sought
	 * @return array
	 */
	private function getIPTCTagArray($tag, $iptc) {
		if (array_key_exists($tag, $iptc)) {
			return $iptc[$tag];
		}
		return NULL;
	}

	/**
	 * Returns the IPTC data converted into UTF8
	 *
	 * @param string $iptcstring the IPTC data
	 * @param string $characterset the internal encoding of the data
	 * @return string
	 */
	private function prepIPTCString($iptcstring, $characterset) {
		global $_zp_UTF8;
		// Remove null byte at the end of the string if it exists.
		if (substr($iptcstring, -1) === 0x0) {
			$iptcstring = substr($iptcstring, 0, -1);
		}
		if ($characterset != LOCAL_CHARSET) {
			$iptcstring = $_zp_UTF8->convert($iptcstring, $characterset, LOCAL_CHARSET);
		}
		return trim(sanitize($iptcstring, 1));
	}

	/**
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		$discard = NULL;
		$size = zp_imageDims($this->localpath);
		$width = $size['width'];
		$height = $size['height'];
		if (zp_imageCanRotate()) {
			// Swap the width and height values if the image should be rotated
			switch (substr(trim($this->get('rotation'), '!'), 0, 1)) {
				case 5:
				case 6:
				case 7:
				case 8:
					$width = $size['height'];
					$height = $size['width'];
					break;
			}
		}
		$this->set('width', $width);
		$this->set('height', $height);
	}

	/**
	 * Returns the width of the image
	 *
	 * @return int
	 */
	function getWidth() {
		$w = $this->get('width');
		if (empty($w)) {
			$this->updateDimensions();
			$this->save();
			$w = $this->get('width');
		}
		return $w;
	}

	/**
	 * Returns the height of the image
	 *
	 * @return int
	 */
	function getHeight() {
		$h = $this->get('height');
		if (empty($h)) {
			$this->updateDimensions();
			$this->save();
			$h = $this->get('height');
		}
		return $h;
	}

	function getRotation() {
		return $this->get('rotation');
	}

	/**
	 * Returns the side car files associated with the image
	 *
	 * @return array
	 */
	function getSidecars() {
		$files = safe_glob(stripSuffix($this->localpath) . '.*');
		$result = array();
		foreach ($files as $file) {
			if (!is_dir($file) && in_array(strtolower(getSuffix($file)), $this->sidecars)) {
				$result[basename($file)] = $file;
			}
		}
		return $result;
	}

	function addSidecar($car) {
		$this->sidecars[$car] = $car;
	}

	/**
	 * Returns the album that holds this image
	 *
	 * @return object
	 */
	function getAlbum() {
		return $this->album;
	}

	/**
	 * Retuns the folder name of the album that holds this image
	 *
	 * @return string
	 */
	function getAlbumName() {
		return $this->albumname;
	}

	/**
	 * Returns the location field of the image
	 *
	 * @return string
	 */
	function getLocation($locale = NULL) {
		$text = $this->get('location');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the location field of the image
	 *
	 * @param string $location text for the location
	 */
	function setLocation($location) {
		$this->set('location', $location);
	}

	/**
	 * Returns the city field of the image
	 *
	 * @return string
	 */
	function getCity($locale = NULL) {
		$text = $this->get('city');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the city field of the image
	 *
	 * @param string $city text for the city
	 */
	function setCity($city) {
		$this->set('city', zpFunctions::tagURLs($city));
	}

	/**
	 * Returns the state field of the image
	 *
	 * @return string
	 */
	function getState($locale = NULL) {
		$text = $this->get('state');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the state field of the image
	 *
	 * @param string $state text for the state
	 */
	function setState($state) {
		$this->set('state', zpFunctions::tagURLs($state));
	}

	/**
	 * Returns the country field of the image
	 *
	 * @return string
	 */
	function getCountry($locale = NULL) {
		$text = $this->get('country');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the country field of the image
	 *
	 * @param string $country text for the country filed
	 */
	function setCountry($country) {
		$this->set('country', zpFunctions::tagURLs($country));
	}

	/**
	 * Returns the credit field of the image
	 *
	 * @return string
	 */
	function getCredit($locale = NULL) {
		$text = $this->get('credit');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the credit field of the image
	 *
	 * @param string $credit text for the credit field
	 */
	function setCredit($credit) {
		$this->set('credit', zpFunctions::tagURLs($credit));
	}

	/**
	 * Returns the copyright field of the image
	 *
	 * @return string
	 */
	function getCopyright($locale = NULL) {
		$text = $this->get('copyright');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the text for the copyright field of the image
	 *
	 * @param string $copyright text for the copyright field
	 */
	function setCopyright($copyright) {
		$this->set('copyright', zpFunctions::tagURLs($copyright));
	}

	/**
	 * Permanently delete this image (permanent: be careful!)
	 * Returns the result of the unlink operation (whether the delete was successful)
	 * @param bool $clean whether to remove the database entry.
	 * @return bool
	 */
	function remove() {
		$result = false;
		if (parent::remove()) {
			$result = true;
			$filestodelete = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
			foreach ($filestodelete as $file) {
				@chmod($file, 0777);
				$result = $result && @unlink($file);
			}
			if ($result) {
				query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='images' AND `objectid`=" . $this->id);
				query("DELETE FROM " . prefix('comments') . "WHERE `type` ='images' AND `ownerid`=" . $this->id);
				$filestodelete = safe_glob(SERVERCACHE . '/' . substr(dirname($this->localpath), strlen(ALBUM_FOLDER_SERVERPATH)) . '/*' . stripSuffix(basename($this->localpath)) . '*');
				foreach ($filestodelete as $file) {
					@chmod($file, 0777);
					$result = $result && @unlink($file);
				}
			}
		}
		clearstatcache();
		return $result;
	}

	/**
	 * Moves an image to a new album and/or filename (rename).
	 * Returns  0 on success and error indicator on failure.
	 * @param Album $newalbum the album to move this file to. Must be a valid Album object.
	 * @param string $newfilename the new file name of the image in the specified album.
	 * @return int
	 */
	function move($newalbum, $newfilename = null) {
		if (is_string($newalbum))
			$newalbum = newAlbum($newalbum, false);
		if ($newfilename == null) {
			$newfilename = $this->filename;
		} else {
			if (getSuffix($this->filename) != getSuffix($newfilename)) { // that is a no-no
				return 6;
			}
		}
		if ($newalbum->getID() == $this->album->getID() && $newfilename == $this->filename) {
			// Nothing to do - moving the file to the same place.
			return 2;
		}
		$newpath = $newalbum->localpath . internalToFilesystem($newfilename);
		if (file_exists($newpath)) {
			// If the file exists, don't overwrite it.
			if (!(CASE_INSENSITIVE && strtolower($newpath) == strtolower($this->localpath))) {
				return 2;
			}
		}
		$filename = basename($this->localpath);
		@chmod($filename, 0777);
		$result = @rename($this->localpath, $newpath);
		@chmod($filename, FILE_MOD);
		clearstatcache();
		if ($result) {
			$filestomove = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
			foreach ($filestomove as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$result = $result && @rename($file, stripSuffix($newpath) . '.' . getSuffix($file));
				}
			}
			//purge the cache as it is easier than figuring out what the new cache name should be
			$filestodelete = safe_glob(SERVERCACHE . '/' . substr(dirname($this->localpath), strlen(ALBUM_FOLDER_SERVERPATH)) . '/*' . stripSuffix(basename($this->localpath)) . '*');
			foreach ($filestodelete as $file) {
				@chmod($file, 0777);
				@unlink($file);
			}
		}
		$this->localpath = $newpath;
		if ($result) {
			if (parent::move(array('filename' => $newfilename, 'albumid' => $newalbum->getID()))) {
				$this->set('mtime', filemtime($newpath));
				$this->save();
				return 0;
			}
		}
		return 1;
	}

	/**
	 * Renames an image to a new filename, keeping it in the same album. Convenience for move($image->album, $newfilename).
	 * Returns  true on success and false on failure.
	 * @param string $newfilename the new file name of the image file.
	 * @return bool
	 */
	function rename($newfilename) {
		return $this->move($this->album, $newfilename);
	}

	/**
	 * Copies the image to a new album, along with all metadata.
	 *
	 * @param string $newalbum the destination album
	 */
	function copy($newalbum) {
		if (is_string($newalbum)) {
			$newalbum = newAlbum($newalbum, false);
		}
		if ($newalbum->getID() == $this->album->getID()) {
			// Nothing to do - moving the file to the same place.
			return 2;
		}
		$newpath = $newalbum->localpath . internalToFilesystem($this->filename);
		if (file_exists($newpath)) {
			// If the file exists, don't overwrite it.
			return 2;
		}
		$filename = basename($this->localpath);
		$result = @copy($this->localpath, $newpath);
		if ($result) {
			$filestocopy = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
			foreach ($filestocopy as $file) {
				if (in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$result = $result && @copy($file, $newalbum->localpath . basename($file));
				}
			}
		}
		if ($result) {
			if ($newID = parent::copy(array('filename' => $filename, 'albumid' => $newalbum->getID()))) {
				storeTags(readTags($this->getID(), 'images', ''), $newID, 'images');
				query('UPDATE ' . prefix('images') . ' SET `mtime`=' . filemtime($newpath) . ' WHERE `filename`="' . $filename . '" AND `albumid`=' . $newalbum->getID());
				return 0;
			}
		}
		return 1;
	}

	/*	 * ** Image Methods *** */

	/**
	 * Returns a path urlencoded image page link for the image
	 *
	 * @return string
	 */
	function getLink() {
		if (is_array($this->filename)) {
			$albumq = $album = dirname($this->filename['source']);
			$image = basename($this->filename['source']);
		} else {
			$album = $this->albumlink;
			$albumq = $this->albumnamealbum->name;
			$image = $this->filename;
		}
		$addl = $addl_plain = NULL;
		if ($this->albumnamealbum->isDynamic()) {
			$this->albumnamealbum->getImages();
			$matches = array_keys($this->albumnamealbum->imageNames, $image);
			if (count($matches) > 1) {
				if ($c = array_search($this->album->name . '/' . $image, $matches)) {
					$c++;
					$addl = '/' . _PAGE_ . '/' . $c;
					$addl_plain = '&page=' . $c;
				}
			}
		}

		if (UNIQUE_IMAGE) {
			$image = stripSuffix($image);
		}

		return zp_apply_filter('getLink', rewrite_path(pathurlencode($album) . '/' . urlencode($image) . IM_SUFFIX . $addl, '/index.php?album=' . pathurlencode($albumq) . '&image=' . urlencode($image) . $addl_plain), $this, NULL);
	}

	/**
	 * Returns a path to the original image in the original folder.
	 *
	 * @param string $path the "path" to the image. Defaults to the simple WEBPATH
	 *
	 * @return string
	 */
	protected function getFullImage($path = WEBPATH) {
		global $_zp_conf_vars;
		if ($path == WEBPATH && $_zp_conf_vars['album_folder_class'] == 'external') {
			return false;
		}
		if (is_array($this->filename)) {
			$album = dirname($this->filename['source']);
			$image = basename($this->filename['source']);
		} else {
			$album = $this->imagefolder;
			$image = $this->filename;
		}
		return getAlbumFolder($path) . $album . "/" . $image;
	}

	/**
	 * returns URL to the original image
	 */
	function getFullImageURL($path = WEBPATH) {
		return zp_apply_filter('getLink', $this->getFullImage($path), 'full-image.php', NULL);
	}

	/**
	 * Returns a path to a sized version of the image
	 *
	 * @param int $size how big an image is wanted
	 * @return string
	 */
	function getSizedImage($size) {
		$wmt = getWatermarkParam($this, WATERMARK_IMAGE);
		$args = getImageParameters(array($size, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $wmt), $this->album->name);
		return getImageURI($args, $this->album->name, $this->filename, $this->filemtime);
	}

	/**
	 *  Get a custom sized version of this image based on the parameters.
	 *
	 * @param int $size size
	 * @param int $width width
	 * @param int $height height
	 * @param int $cropw crop width
	 * @param int $croph crop height
	 * @param int $cropx crop x axis
	 * @param int $cropy crop y axis
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects set to desired image effect (e.g. 'gray' to force gray scale)
	 * @return string
	 */
	function getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin = false, $effects = NULL) {
		if ($thumbStandin < 0) {
			$wmt = '!';
		} else {
			if ($thumbStandin) {
				$wmt = getWatermarkParam($this, WATERMARK_THUMB);
			} else {
				$wmt = getWatermarkParam($this, WATERMARK_IMAGE);
			}
		}
		$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
		return getImageURI($args, $this->album->name, $this->filename, $this->filemtime);
	}

	/**
	 * Returns the default sized image HTML
	 *
	 * @return string
	 */
	function getContent() {
		$class = '';
		if (!$this->getShow()) {
			$class .= " not_visible";
		}
		$album = $this->getAlbum();
		$pwd = $album->getPassword();
		if (!empty($pwd)) {
			$class .= " password_protected";
		}
		$size = getOption('image_size');
		$h = $this->getHeight();
		$w = $this->getWidth();
		$side = getOption('image_use_side');
		$us = getOption('image_allow_upscale');
		$dim = $size;

		if ($w == 0) {
			$hprop = 1;
		} else {
			$hprop = round(($h / $w) * $dim);
		}
		if ($h == 0) {
			$wprop = 1;
		} else {
			$wprop = round(($w / $h) * $dim);
		}

		if (($size && ($side == 'longest' && $h > $w) || ($side == 'height') || ($side == 'shortest' && $h < $w))) {
			// Scale the height
			$newh = $dim;
			$neww = $wprop;
		} else {
			// Scale the width
			$neww = $dim;
			$newh = $hprop;
		}
		if (!$us && $newh >= $h && $neww >= $w) {
			$neww = $w;
			$newh = $h;
		}
		$html = '<img src="' . html_encode(pathurlencode($this->getSizedImage($size))) . '" alt="' . html_encode($this->getTitle()) . '"' .
						' width="' . $neww . '" height="' . $newh . '"' .
						(($class) ? " class=\"$class\"" : "") . " />";
		$html = zp_apply_filter('standard_image_html', $html);
		return $html;
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return s
	 */
	function getThumbImageFile() {
		return $local = $this->localpath;
	}

	/**
	 * Returns an array of cropping parameters. Used as a "helper" function for various
	 * inherited getThumb() methods
	 *
	 * @param string $type the type of thumb (in case it ever matters in the cropping, now it does not.)
	 */
	function getThumbCropping($ts, $sw, $sh) {
		$cy = $this->get('thumbY');
		if (is_null($cy)) {
			$custom = $cx = $cw = $ch = NULL;
		} else {
			$custom = true;
			$cx = $this->get('thumbX');
			$cw = $this->get('thumbW');
			$ch = $this->get('thumbH');
			// upscale to thumb_size proportions
			if ($sw == $sh) { // square crop, set the size/width to thumbsize
				$sw = $sh = $ts;
			} else {
				if ($sw > $sh) {
					$r = $ts / $sw;
					$sw = $ts;
					$sh = $sh * $r;
				} else {
					$r = $ts / $sh;
					$sh = $ts;
					$sh = $r * $sh;
				}
			}
		}
		return array($custom, $cw, $ch, $cx, $cy);
	}

	/**
	 * Get a default-sized thumbnail of this image.
	 *
	 * @return string
	 */
	function getThumb($type = 'image', $wmt = NULL) {
		$ts = getOption('thumb_size');
		if (getOption('thumb_crop')) {
			$sw = getOption('thumb_crop_width');
			$sh = getOption('thumb_crop_height');
			list($custom, $cw, $ch, $cx, $cy) = $this->getThumbCropping($ts, $sw, $sh);
			if ($custom) {
				return $this->getCustomImage(NULL, $sw, $sh, $cw, $ch, $cx, $cy, true);
			}
		} else {
			$sw = $sh = NULL;
		}
		if (empty($wmt))
			$wmt = getWatermarkParam($this, WATERMARK_THUMB);
		$args = getImageParameters(array($ts, NULL, NULL, $sw, $sh, NULL, NULL, NULL, true, NULL, true, $wmt, NULL, NULL), $this->album->name);

		return getImageURI($args, $this->album->name, $this->filename, $this->filemtime);
	}

	/**
	 * Get the index of this image in the album, taking sorting into account.
	 *
	 * @return int
	 */
	function getIndex() {
		global $_zp_current_search, $_zp_current_album;
		if ($this->index == NULL) {
			$album = $this->albumnamealbum;
			$filename = $this->filename;
			if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED) || $album->isDynamic()) {
				$imagefolder = $this->imagefolder;
				if ($album->isDynamic()) {
					$images = $album->getImages();
				} else {
					$images = $_zp_current_search->getImages(0);
				}
				$target = array_keys(array_filter($images, function($item) use($filename, $imagefolder) {
									return $item['filename'] == $filename && $item['folder'] == $imagefolder;
								}));
				$this->index = @$target[0];
			} else {
				$images = $this->album->getImages(0);
				$target = array_keys(array_filter($images, function($item) use ($filename) {
									return $item == $filename;
								}));
				$this->index = @$target[0];
			}
		}
		return $this->index;
	}

	/**
	 * Returns the next Image.
	 *
	 * @return object
	 */
	function getNextImage() {
		global $_zp_current_search;
		$index = $this->getIndex();
		if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
			$image = $_zp_current_search->getImage($index + 1);
		} else {
			$album = $this->albumnamealbum;
			$image = $album->getImage($index + 1);
		}
		return $image;
	}

	/**
	 * Return the previous Image
	 *
	 * @return object
	 */
	function getPrevImage() {
		global $_zp_current_search;
		$index = $this->getIndex();
		if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
			$image = $_zp_current_search->getImage($index - 1);
		} else {
			$album = $this->albumnamealbum;
			$image = $album->getImage($index - 1);
		}
		return $image;
	}

	/**
	 * Returns the disk size of the image
	 *
	 * @return string
	 */
	function getImageFootprint() {
		return filesize($this->localpath);
	}

	/**
	 * Returns the custom watermark name
	 *
	 * @return string
	 */
	function getWatermark() {
		return $this->get('watermark');
	}

	/**
	 * Set custom watermark
	 *
	 * @param string $wm
	 */
	function setWatermark($wm) {
		$this->set('watermark', $wm);
	}

	/**
	 * Returns the custom watermark usage
	 *
	 * @return bool
	 */
	function getWMUse() {
		return $this->get('watermark_use');
	}

	/**
	 * Sets the custom watermark usage
	 *
	 * @param $use
	 */
	function setWMUse($use) {
		$this->set('watermark_use', $use);
	}

	/**
	 * Owner functions
	 */
	function getOwner() {
		$owner = $this->get('owner');
		if (empty($owner)) {
			$owner = $this->album->getOwner();
		}
		return $owner;
	}

	function setOwner($owner) {
		$this->set('owner', $owner);
	}

	function isMyItem($action) {
		$album = $this->album;
		return $album->isMyItem($action);
	}

	/**
	 * returns true if user is allowed to see the image
	 */
	function checkAccess(&$hint = NULL, &$show = NULL) {
		$album = $this->getAlbum();
		if ($album->isMyItem(LIST_RIGHTS)) {
			return $this->getShow() || $album->subRights() & (MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW);
		}
		return $album->checkforGuest($hint, $show) && $this->getShow() && $album->getShow();
	}

	/**
	 * Checks if guest is loggedin for the album
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		$album = $this->getAlbum();
		return $album->checkforGuest($hint, $show);
	}

	/**
	 *
	 * returns true if there is any protection on the image
	 */
	function isProtected() {
		return $this->checkforGuest() != 'zp_public_access';
	}

}

class Transientimage extends Image {

	/**
	 * creates a transient image (that is, one that is not stored in the database)
	 *
	 * @param string $image the full path to the image
	 * @return transientimage
	 */
	function __construct($album, $image) {
		if (!is_object($album)) {
			$album = new AlbumBase('Transient');
		}
		$this->album = $album;
		$this->localpath = $image;
		$filename = makeSpecialImageName($image);
		$this->filename = $filename;
		$this->displayname = stripSuffix(basename($image));
		if (empty($this->displayname)) {
			$this->displayname = $this->filename['name'];
		}
		$this->filemtime = @filemtime($this->localpath);
		$this->comments = null;
		$this->instantiate('images', array('filename' => $filename['name'], 'albumid' => $this->album->getID()), 'filename', true, true);
		$this->exists = false;
	}

}

?>
