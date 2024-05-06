<?php
/**
 * Image Class
 * @package zpcore\classes\objects
 */
class Image extends MediaObject {

	public $filename; // true filename of the image.
	public $exists = true; // Does the image exist?
	public $webpath; // The full URL path to the original image.
	public $localpath; // Latin1 full SERVER path to the original image.
	public $displayname; // $filename with the extension stripped off.
	public $album; // An album object for the album containing this image.
	public $albumname; // The name of the album for which this image was instantiated. (MAY NOT be $this->album->name!!!!).
	public $albumnamealbum; //	An album object representing the above;
	public $albumlink; // "rewrite" verwion of the album name, eg. may not have the .alb
	public $imagefolder; // The album folder containing the image (May be different from the albumname!!!!)
	protected $index; // The index of the current image in the album array.
	protected $sortorder; // The position that this image should be shown in the album
	public $filemtime; // Last modified time of this image
	public $sidecars = array(); // keeps the list of suffixes associated with this image
	public $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	public $manage_some_rights = ALBUM_RIGHTS;
	public $view_rights = ALL_ALBUMS_RIGHTS;
	// Plugin handler support
	public $objectsThumb = NULL; // Thumbnail image for the object
	public $thumbdimensions = null;
	public $encwebpath = '';
	public $imagetype = '';

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
		$msg = false;
		if (!is_object($album) || !$album->exists) {
			$msg = gettext('Invalid image instantiation: Album does not exist');
		} else {
			if (!$this->classSetup($album, $filename) || !file_exists($this->localpath) || is_dir($this->localpath)) {
				$msg = gettext('Invalid image instantiation: file does not exist');
			}
		}
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				trigger_error($msg, E_USER_ERROR);
			}
			return;
		}

		// This is where the magic happens...
		$album_name = $album->name;
		$new = $this->instantiate('images', array('filename' => $filename, 'albumid' => $this->album->getID()), 'filename', false, empty($album_name));
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
	 * Returns a new "image" object based on the file extension
	 * 
	 * @since 1.6 - Moved to Image class as static method
	 *
	 * @param object $album the owner album
	 * @param string $filename the filename
	 * @param bool $quiet set true to supress error messages (used by loadimage)
	 * @return object
	 */
	static function newImage($album, $filename, $quiet = false) {
		global $_zp_extra_filetypes, $_zp_missing_image;
		if (is_array($filename)) {
			$xalbum = AlbumBase::newAlbum($filename['folder'], true, true);
			$filename = $filename['filename'];
		} else {
			if ($album->isDynamic()) {
				$xalbum = NULL;
				foreach ($album->getImages() as $image) {
					if ($filename == $image['filename']) {
						$xalbum = AlbumBase::newAlbum($image['folder']);
						break;
					}
				}
			} else {
				$xalbum = $album;
			}
		}
		if (!is_object($xalbum) || !$xalbum->exists || !AlbumBase::isAlbumClass($xalbum)) {
			if (!$quiet) {
				$msg = sprintf(gettext('Bad album object parameter to newImage(%s)'), $filename);
				trigger_error($msg, E_USER_NOTICE);
			}
			return $_zp_missing_image;
		}
		if ($object = Gallery::validImageAlt($filename)) {
			$image = New $object($xalbum, $filename, $quiet);
		} else {
			if (Gallery::validImage($filename)) {
				$image = New Image($xalbum, $filename, $quiet);
			} else {
				$image = NULL;
			}
		}
		if ($image) {
			if ($album && $album->isDynamic()) {
				$image->albumname = $album->name;
				$image->albumlink = $album->linkname;
				$image->albumnamealbum = $album;
			}
			zp_apply_filter('image_instantiate', $image);
			if ($image->exists) {
				return $image;
			} else {
				return $_zp_missing_image;
			}
		}

		if (!$quiet) {
			$msg = sprintf(gettext('Bad filename suffix in newImage(%s)'), $filename);
			trigger_error($msg, E_USER_NOTICE);
		}
		return $_zp_missing_image;
	}

	/**
	 * Returns true if the object is a zenphoto 'image'
	 * 
	 * @since 1.6 - Moved to Image class as static method
	 *
	 * @param object $image
	 * @return bool
	 */
	static function isImageClass($image = NULL) {
		global $_zp_current_image;
		if (is_null($image)) {
			if (!in_context(ZP_IMAGE))
				return false;
			$image = $_zp_current_image;
		}
		return is_object($image) && ($image->table == 'images');
	}

	/**
	 * (non-PHPdoc)
	 * @see PersistentObject::setDefaults()
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		$this->setPublished($_zp_gallery->getImagePublish());
		$this->set('mtime', $this->filemtime);
		$this->setLastChange();
		$this->updateDimensions(); // deal with rotation issues
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
				zp_error(gettext('An image object was instantiated without using the newImage() function.'), E_USER_WARNING);
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
		if (empty($date)) {
			$dateformatted = zpFormattedDate('Y-m-d H:i:s', $this->filemtime);
			$this->set('date', $dateformatted);
		}
		return true;
	}

	/**
	 * Returns the image filename
	 * 
	 * @since 1.6
	 * @return string
	 */
	function getName() {
		return $this->filename;
	}

	/**
	 * Returns the image filename
	 * 
	 * @deprecated 2.0 - User getName() instead
	 *
	 * @return string
	 */
	function getFileName() {
		deprecationNotice(gettext('Use getName() instead'));
		return $this->getName();
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
	 * Returns true if the image has any meta data
	 * @since 1.6.3
	 * @return bool
	 */
	function hasMetaData() {
		return $this->get('hasMetadata');
	}

	/**
	 * Returns an array of EXIF data
	 * 
	 * @since 1.6.3 Parameters $displayonly and $hide_empty added
	 * 
	 * @param string $displayonly set to true to return only the items selected for display (default true)
	 * @param bool $hide_empty Hide empty meta data fields (default true)
	 * @return array
	 */
	function getMetaData($displayonly = true, $hide_empty = true) {
		global $_zp_exifvars;
		$exif = array();
		// Put together an array of EXIF data to return
		foreach ($_zp_exifvars as $field => $exifvar) {
			$display = true;
			if ($displayonly) {
				$display = $exifvar[3];
			}
			//	only enabled image metadata
			if ($exifvar[5] && $display) {
				$value = $this->get($field);
				$hide = false;
				if ($hide_empty && !$value) {
					$hide = true;
				}
				if (!$hide) {
					$exif[$field] = $value;
				}
			}
		}
		return $exif;
	}

	/**
	 * Parses Exif/IPTC data
	 *
	 */
	function updateMetaData() {
		global $_zp_exifvars, $_zp_gallery, $_zp_graphics;
		$IPTCtags = array(
						'SKIP'								 => '2#000', //	Record Version										Size:64
						'ObjectType'					 => '2#003', //	Object Type	Ref										Size:67
						'ObjectAttr'					 => '2#004', //	Object Attribute Ref							Size:67
						'ObjectName'					 => '2#005', //	Object name												Size:64
						'EditStatus'					 => '2#007', //	Edit Status												Size:64
						'EditorialUpdate'			 => '2#008', //	Editorial Update									Size:2
						'Urgency'							 => '2#010', //	Urgency														Size:1
						'SubRef'							 => '2#012', //	Subject	Reference									Size:236
						'Category'						 => '2#015', //	Category 													Size:3
						'SuppCategory'				 => '2#020', //	Supplemental category							Size:32
						'FixtureID'						 => '2#022', //	Fixture	ID 												Size:32
						'Keywords'						 => '2#025', //	Keywords 													Size:64
						'ContentLocationCode'	 => '2#026', //	Content	Location Code							Size:3
						'ContentLocationName'	 => '2#027', //	Content	Location Name							Size:64
						'ReleaseDate'					 => '2#030', //	Release	Date 											Size:8
						'ReleaseTime'					 => '2#035', //	Release	Time											Size:11
						'ExpireDate'					 => '2#037', //	Expiration Date										Size:8
						'ExpireTime'					 => '2#038', //	Expiration Time										Size:11
						'SpecialInstru'				 => '2#040', //	Special Instructions							Size:256
						'ActionAdvised'				 => '2#042', //	Action Advised										Size:2
						'RefService'					 => '2#045', //	Reference Service									Size:10
						'RefDate'							 => '2#047', //	Reference Date										Size:8
						'RefNumber'						 => '2#050', //	Reference Number									Size:8
						'DateCreated'					 => '2#055', //	Date created											Size:8
						'TimeCreated'					 => '2#060', //	Time created											Size:11
						'DigitizeDate'				 => '2#062', //	Digital Creation Date							Size:8
						'DigitizeTime'				 => '2#063', //	Digital Creation Time							Size:11
						'OriginatingProgram'	 => '2#065', //	Originating Program								Size:32
						'ProgramVersion'			 => '2#070', //	Program version										Size:10
						'ObjectCycle'					 => '2#075', //	Object Cycle											Size:1
						'ByLine'							 => '2#080', //	ByLine 														Size:32
						'ByLineTitle'					 => '2#085', //	ByLine Title											Size:32
						'City'								 => '2#090', //	City															Size:32
						'SubLocation'					 => '2#092', //	Sublocation												Size:32
						'State'								 => '2#095', //	Province/State										Size:32
						'LocationCode'				 => '2#100', //	Country/Primary	Location Code			Size:3
						'LocationName'				 => '2#101', //	Country/Primary	Location Name			Size:64
						'TransmissionRef'			 => '2#103', //	Original Transmission Reference		Size:32
						'ImageHeadline'				 => '2#105', //	Image headline										Size:256
						'ImageCredit'					 => '2#110', //	Image credit											Size:32
						'Source'							 => '2#115', //	Source														Size:32
						'Copyright'						 => '2#116', //	Copyright Notice									Size:128
						'Contact'							 => '2#118', //	Contact														Size:128
						'ImageCaption'				 => '2#120', //	Image caption											Size:2000
						'ImageCaptionWriter'	 => '2#122', //	Image caption writer							Size:32
						'ImageType'						 => '2#130', //	Image type												Size:2
						'Orientation'					 => '2#131', //	Image	 rientation									Size:1
						'LangID'							 => '2#135', //	Language ID												Size:3
						'Subfile'							 => '8#010' //	Subfile														Size:2
		);
		$this->set('hasMetadata', 0);
		$result = array();
		if (get_class($this) == 'Image') {
			$localpath = $this->localpath;
		} else {
			$localpath = $this->getThumbImageFile();
		}
		$xdate = false;

		if (!empty($localpath)) { // there is some kind of image to get metadata from
			$exifraw = read_exif_data_protected($localpath);
			if (isset($exifraw['ValidEXIFData'])) {
				$this->set('hasMetadata', 1);
				foreach ($_zp_exifvars as $field => $exifvar) {
					$exif = NULL;
					if ($exifvar[5]) { // enabled field
						if (isset($exifraw[$exifvar[0]][$exifvar[1]])) {
							$exif = trim(sanitize($exifraw[$exifvar[0]][$exifvar[1]], 1));
						} else if (isset($exifraw[$exifvar[0]]['MakerNote'][$exifvar[1]])) {
							$exif = trim(sanitize($exifraw[$exifvar[0]]['MakerNote'][$exifvar[1]], 1));
						}
					}
					$this->set($field, $exif);
				}
			}
			/* check IPTC data */
			$iptcdata = $_zp_graphics->imageIPTC($localpath);
			if (!empty($iptcdata)) {
				$iptc = iptcparse($iptcdata);
				if ($iptc) {
					$this->set('hasMetadata', 1);
					$characterset = $this->getIPTCTag('1#090', $iptc);
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
						if ($exifvar[0] == 'IPTC') {
							if ($exifvar[5]) { // enabled field
								$datum = $this->getIPTCTag($IPTCtags[$exifvar[1]], $iptc);
								$this->set($field, $this->prepIPTCString($datum, $characterset));
							} else {
								$this->set($field, NULL);
							}
						}
					}
					/* iptc keywords (tags) */
					if ($_zp_exifvars['IPTCKeywords'][5]) {
						$datum = $this->getIPTCTagArray($IPTCtags['Keywords'], $iptc);
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
		/* "import" metadata into Zenphoto fields as makes sense */
		zp_apply_filter('image_metadata', $this);

		/* iptc date */
		$date = $this->get('IPTCDateCreated');
		if (!empty($date)) {
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
		/* EXIF date */
		if (empty($date)) {
			$date = $this->get('EXIFDateTime');
		}
		if (empty($date)) {
			$date = $this->get('EXIFDateTimeOriginal');
		}
		if (empty($date)) {
			$date = $this->get('EXIFDateTimeDigitized');
		}
		if (!empty($date)) {
			$xdate = $date;
			$this->setDateTime($date);
		}

		/* iptc title */
		$title = $this->get('IPTCObjectName');
		if (empty($title)) {
			$title = $this->get('IPTCImageHeadline');
		}
		//EXIF title [sic]
		if (empty($title)) {
			$title = $this->get('EXIFDescription');
		}
		if (!empty($title)) {
			$this->setTitle($title);
		}

		/* iptc description */
		$desc = $this->get('IPTCImageCaption');
		if (!empty($desc)) {
			if (getOption('IPTC_convert_linebreaks')) {
				$desc = nl2br(html_decode($desc));
			}
			$this->setDesc($desc);
		} 

		/* iptc location, state, country */
		$loc = $this->get('IPTCSubLocation');
		if (!empty($loc)) {
			$this->setLocation($loc);
		}
		$city = $this->get('IPTCCity');
		if (!empty($city)) {
			$this->setCity($city);
		}
		$state = $this->get('IPTCState');
		if (!empty($state)) {
			$this->setState($state);
		}
		$country = $this->get('IPTCLocationName');
		if (!empty($country)) {
			$this->setCountry($country);
		}

		/* iptc credit */
		$credit = $this->get('IPTCByLine');
		if (empty($credit)) {
			$credit = $this->get('IPTCImageCredit');
		}
		if (empty($credit)) {
			$credit = $this->get('IPTCSource');
		}
		if (!empty($credit)) {
			$this->setCredit($credit);
		}

		/* iptc copyright */
		$this->setCopyright($this->get('IPTCCopyright'));

		if (empty($xdate)) {
			$dateformatted = zpFormattedDate('Y-m-d H:i:s', $this->filemtime);
			$this->setDateTime($dateformatted);
		}
		$alb = $this->album;
		if (!is_null($alb)) {
			if (!$this->get('owner')) {
				$this->setOwner($alb->getOwner());
			}
			$save = false;
			if (strtotime(strval($alb->getUpdatedDate())) < strtotime(date('Y-m-d H:i:s'))) {
				$alb->setUpdatedDate();
				$alb->setUpdatedDateParents();
				$save = true;
			}
			$albdate = $alb->getDateTime();
			if (is_null($albdate) || ($_zp_gallery->getAlbumUseImagedate() && strtotime($albdate) < strtotime($this->getDateTime()))) {
				$alb->setDateTime($this->getDateTime()); //  not necessarily the right one, but will do. Can be changed in Admin
				$save = true;
			}
			if ($save) {
				$alb->save();
			}
		}
	}

	/**
	 * Fetches a single tag from IPTC data
	 *
	 * @param string $tag the metadata tag sought
	 * @return string
	 */
	private function getIPTCTag($tag, $iptc) {
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
		global $_zp_utf8;
		// Remove null byte at the end of the string if it exists.
		if (substr($iptcstring, -1) === 0x0) {
			$iptcstring = substr($iptcstring, 0, -1);
		}
		$outputset = LOCAL_CHARSET;
		if ($characterset == $outputset) {
			return $iptcstring;
		}
		$iptcstring = $_zp_utf8->convert($iptcstring, $characterset, $outputset);
		return trim(sanitize($iptcstring, 1));
	}
	
	/**
	 * If there is valid GPS data returns key value array with "long" and "lat" keys
	 * otherwise an empty array
	 * 
	 * @since 1.5.8 - Moved/adapted from the offical Zenphoto GoogleMap plugin by Stephen Billard (sbillard) & Vincent Bourganel (vincent3569)
	 * 
	 * @return array
	 */
	function getGeodata() {
		$gps = array();
		if (Image::isImageClass($this)) {
			$exif = $this->getMetaData(false);
			if ((!empty($exif['EXIFGPSLatitude'])) && (!empty($exif['EXIFGPSLongitude']))) {
				$lat_c = explode('.', str_replace(',', '.', $exif['EXIFGPSLatitude']) . '.0');
				$lat_f = round((float) abs($lat_c[0]) + ($lat_c[1] / pow(10, strlen($lat_c[1]))), 12);
				if (isset($exif['EXIFGPSLatitudeRef'][0]) && strtoupper($exif['EXIFGPSLatitudeRef'][0]) == 'S') {
					$lat_f = -$lat_f;
				}
				$long_c = explode('.', str_replace(',', '.', $exif['EXIFGPSLongitude']) . '.0');
				$long_f = round((float) abs($long_c[0]) + ($long_c[1] / pow(10, strlen($long_c[1]))), 12);
				if (isset($exif['EXIFGPSLongitudeRef'][0]) && strtoupper($exif['EXIFGPSLongitudeRef'][0]) == 'W') {
					$long_f = -$long_f;
				}
				//in case European comma decimals sneaked in
				$lat_f = str_replace(',', '.', $lat_f);
				$long_f = str_replace(',', '.', $long_f);
				if (($long_f > -180 && $long_f < 180) && ($lat_f > -90 && $lat_f < 90)) {
					return array(
							'lat' => $lat_f,
							'long' => $long_f
					);
				}
			}
			return $gps;
		}
	}

	/**
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		global $_zp_graphics;
		$size = $_zp_graphics->imageDims($this->localpath);
		if (is_array($size)) {
			$width = $size['width'];
			$height = $size['height'];
			if ($_zp_graphics->imageCanRotate()) {
				// Swap the width and height values if the image should be rotated
				$rotation = extractImageExifOrientation($this->get('EXIFOrientation'));
				switch ($rotation) {
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
	
	/**
	 * Returns an array with widht and height the thumb. Here this is just a wrapper for getWidth() and getHeight()
	 * 
	 * Child (base) class handlers of non image file formats (e.g. video, textobject) where the actual "image" and the sidecar thumb are not the same
	 * file need to override this and provide the actual dimensions of the thumb using zp_getImageDims($thumbfile). 
	 * Otherwise thumb generation may be distorted.
	 * 
	 * @since 1.5.8
	 * 
	 * @return array
	 */
	function getThumbDimensions() {
		if (!is_null($this->thumbdimensions)) {
			return $this->thumbdimensions;
		}
		return $this->thumbdimensions = array(
				'width' => $this->getWidth(),
				'height' => $this->getHeight()
		);
	}

	/**
	 * Returns the width of the thumb. Here just the same as getWidth(). 
	 *
	 * @see getThumbDimensions() for specific usage
	 * @since 1.5.8
	 * 
	 * @return int
	 */
	function getThumbWidth() {
		$dims = $this->getThumbDimensions();
		return $dims['width'];
	}

	/**
	 * Returns the height of the image. Here just the same as getHeight().
	 *
	 * @see getThumbDimensions() for specific usage
	 * @since 1.5.8
	 * 
	 * @return int
	 */
	function getThumbHeight() {
		$dims = $this->getThumbDimensions();
		return $dims['height'];
	}
	
	/**
	 * Returns 'is_square', 'is_landscape', 'is_portrait' if the original image's widht and height match.
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $type 'image' or 'thumb' - the latter may be different on non image "image items"
	 * @return boolean|string
	 */
	function getOrientation($type = 'image') {
		switch ($type) {
			default:
			case 'image':
				$width = $this->getWidth();
				$height = $this->getHeight();
				break;
			case 'thumb':
				$width = $this->getThumbWidth();
				$height = $this->getThumbHeight();
				break;
		}
		if ($width == $height) {
			return 'is_square';
		} else if ($width > $height) {
			return 'is_landscape';
		} else if ($width < $height) {
			return 'is_portrait';
		}
		return false;
	}

	/**
	 * Returns true if the image has landscape orientation
	 * 
	 * @since 1.5.8
	 *  
	 * @param string $type 'image' or 'thumb' - the latter may be different on non image "image items"
	 * @return bool
	 */
	function isLandscape($type = 'image') {
		return $this->getOrientation($type) == 'is_landscape';
	}

	/**
	 * Returns true if the image is a square
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $type 'image' or 'thumb' - the latter may be different on non image "image items"
	 * @return bool
	 */
	function isSquare($type = 'image') {
		return $this->getOrientation($type) == 'is_square';
	}

	/**
	 * Returns true if the image has portrait orientation
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $type 'image' or 'thumb' - the latter may be different on non image "image items"
	 * @return bool
	 */
	function isPortrait($type = 'image') {
		return $this->getOrientation($type) == 'is_portrait';
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
		$text = unTagURLs($text);
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the city field of the image
	 *
	 * @param string $city text for the city
	 */
	function setCity($city) {
		$this->set('city', tagURLs($city));
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the state field of the image
	 *
	 * @param string $state text for the state
	 */
	function setState($state) {
		$this->set('state', tagURLs($state));
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the country field of the image
	 *
	 * @param string $country text for the country filed
	 */
	function setCountry($country) {
		$this->set('country', tagURLs($country));
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the credit field of the image
	 *
	 * @param string $credit text for the credit field
	 */
	function setCredit($credit) {
		$this->set('credit', tagURLs($credit));
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
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the text for the copyright field of the image
	 *
	 * @param string $copyright text for the copyright field
	 */
	function setCopyright($copyright) {
		$this->set('copyright', tagURLs($copyright));
	}
	
	/**
	 * Returns the content of the copyright field if set.
	 * If not it tries the following fallbacks:
	 * 
	 * - IPTCCopyright field
	 * - EXIFCopyright field
	 * - "copyright_image_notice" option
	 * - Owner
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $locale
	 * @return string|null
	 */
	function getCopyrightNotice($locale = null) {
		$notice = trim(strval($this->getCopyright($locale)));
		if (empty($notice)) {
			$metadata = $this->getMetaData();
			if (isset($metadata['IPTCCopyright']) && !empty($metadata['IPTCCopyright'])) {
				$notice = $metadata['IPTCCopyright'];
			} else if (isset($metadata['EXIFCopyright']) && !empty($metadata['EXIFCopyright'])) {
				$notice = $metadata['EXIFCopyright'];
			} else if (empty($notice)) {
				$option = trim(getOption('copyright_image_notice'));
				if (!empty($option)) {
					$notice = $option;
				}
			}
		}
		if (!empty(trim($notice))) {
			$notice = unTagURLs(get_language_string($notice, $locale));
		}
		return $notice;
	}

	/**
	 * Gets the general option "copyright_image_rightsholder" respectively "copyright_image_rightsholder_custom"
	 * If set to "none" the following fallbacks are tried.
	 * 
	 * - EXIFArtist
	 * - VideoArtist (for multimedia "images")
	 * – IPTCByLine
	 * - the owner (fullname if available)
	 * 
	 * @since 1.5.8
	 */
	function getCopyrightRightsholder() {
		$rightsholder = trim(strval(getOption('copyright_image_rightsholder')));
		if ($rightsholder && $rightsholder != 'none') {
			if ($rightsholder == 'custom') {
				$rightsholder = trim(strval(getOption('copyright_image_rightsholder_custom')));
			} else {
				$rightsholder = Administrator::getNameByUser($rightsholder);
			}
		} else {
			$metadata = $this->getMetaData();
			if (isset($metadata['EXIFArtist']) && !empty($metadata['EXIFArtist'])) {
				$rightsholder = $metadata['EXIFArtist'];
			} else if (isset($metadata['VideoArtist']) && !empty($metadata['VideoArtist'])) {
				$rightsholder = $metadata['VideoArtist'];
			} else if (isset($metadata['IPTCByLine']) && !empty($metadata['IPTCByLine'])) {
				$rightsholder = $metadata['IPTCByLine'];
			}
		}
		if (empty($rightsholder)) {
			$rightsholder = $this->getOwner(true);
		}
		return $rightsholder;
	}

	/**
	 * Gets the image copyright URL
	 * 
	 * @since 1.5.8
	 * 
	 * @return string
	 */
	function getCopyrightURL() {
		$url = getOption('copyright_image_url');
		if ($url) {
			if ($url == 'custom') {
				return getOption('copyright_image_url_custom');
			} else if ($url == 'none') {
				return null;
			} else {
				if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
					$pageobj = new ZenpagePage($url);
					if ($pageobj->exists) {
						return $pageobj->getLink();
					}
				}
			}
		}
	}

	/**
   * Permanently delete this image (permanent: be careful!)
   * Returns the result of the unlink operation (whether the delete was successful)
   * @param bool $clean whether to remove the database entry.
   * @return bool
   */
  function remove() {
		global $_zp_db;
    $result = false;
    if (parent::remove()) {
      $result = true;
      $filestodelete = safe_glob(substr($this->localpath, 0, strrpos($this->localpath, '.')) . '.*');
      foreach ($filestodelete as $file) {
        @chmod($file, 0777);
        $result = $result && @unlink($file);
      }
      if ($result) {
				$this->setUpdatedDateAlbum();
        $_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='images' AND `objectid`=" . $this->id);
        $_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . "WHERE `type` ='images' AND `ownerid`=" . $this->id);
        $this->removeCacheFiles();
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
			$newalbum = AlbumBase::newAlbum($newalbum, false);
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
		}
		if ($result) {
			if (parent::move(array('filename' => $newfilename, 'albumid' => $newalbum->getID()))) {
				$this->setUpdatedDateAlbum();
				$newalbum->setUpdatedDate();
				$newalbum->save();
				$newalbum->setUpdatedDateParents(); 
				$this->set('mtime', filemtime($newpath));
				$this->save();
				$this->moveCacheFiles($newalbum, $newfilename);
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
		global $_zp_db;
		if (is_string($newalbum)) {
			$newalbum = AlbumBase::newAlbum($newalbum, false);
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
				storeTags(readTags($this->getID(), 'images'), $newID, 'images');
				$_zp_db->query('UPDATE ' . $_zp_db->prefix('images') . ' SET `mtime`=' . filemtime($newpath) . ' WHERE `filename`="' . $filename . '" AND `albumid`=' . $newalbum->getID());
				$newalbum->setUpdatedDate(); 
				$newalbum->save();
				$newalbum->setUpdatedDateParents();
				$this->copyCacheFiles($newalbum);
				return 0;
			}
		}
		return 1;
	}
	
	/**
	 * Gets the cache files of the image
	 * 
	 * @since 1.6.1
	 * @return array
	 */
	function getCacheFiles() {
		$cachepath = $this->album->getCacheFolder() . $this->filename;
    return safe_glob(substr($cachepath, 0, strrpos($cachepath, '.')) . '_*');	
	}
	
	/**
	 * Copies the cache files of the image to another cache folder
	 * 
	 * @since 1.6.1
	 * @param object $newalbum Target album object
	 */
	function copyCacheFiles($newalbum) {
		$cachefiles = $this->getCacheFiles();
		if ($cachefiles) {
			$album_target = SERVERCACHE . '/' . $newalbum->name;
			if (!file_exists($album_target)) {
				mkdir($album_target);
			}
			chmod($album_target, 0777);
			foreach ($cachefiles as $file) {
				$filecopy = $album_target . '/' . basename($file);
				if (is_link($file)) { 
					$symlink = readlink($file);
					$new_target = str_replace($this->album->localpath, $newalbum->localpath, $symlink);
					@symlink($new_target, $filecopy);
				} else {
					$filecopy = $album_target . '/' . basename($file);
					if (!file_exists($filecopy)) {
						@copy($file, $filecopy);
						@chmod($filecopy, FILE_MOD);
					}
				}
			}
			@chmod($album_target, FOLDER_MOD);
		}
	}

	/**
	 * Moves the cache files of the image to another cache folder
	 * 
	 * @since 1.6.1
	 * @param object $newalbum Target album object
	 * @param string $newfilename Album folder name of the cache folder (album/subalbum/…)
	 * @return boolean
	 */
	function moveCacheFiles($newalbum, $newfilename) {
		$cachefiles = $this->getCacheFiles();
		$newfilenname_nosuffix = stripSuffix($newfilename);
		$album_target = null;
		if (is_object($newalbum)) {
			$album_target = SERVERCACHE . '/' . $newalbum->name;
			if (!file_exists($album_target)) {
				mkdir($album_target);
			}
			@chmod($album_target, 0777);
		} 
		$skip = false;
		foreach ($cachefiles as $file) {
			if (!is_null($album_target) && $newfilename == $this->filename) { 
				// move
				$targetfile = $album_target . '/' . basename($file);
				if (is_link($file)) {
					$symlink = readlink($file);
					$new_target = str_replace($this->album->localpath, $newalbum->localpath, $symlink);
					@symlink($new_target, $targetfile);
					@unlink($file);
					$skip = true;
				} 
			} else {
				// rename
				$targetfile = str_replace(stripSuffix($this->filename) . '_', $newfilenname_nosuffix . '_', $file);
			}
			if (!file_exists($targetfile) && !$skip) {
				@chmod($targetfile, 0777);
				@rename($file, $targetfile);
				@chmod($targetfile, FILE_MOD);
			}
		}
		if (is_object($newalbum)) {
			@chmod($album_target, FOLDER_MOD);
		}
	}

	/**
	 * Renames the cache files of the image
	 * Alias of moveCacheFiles()
	 * 
	 * @since 1.6.1
	 * 
	 * @return boolean
	 */
	function renameCacheFiles($newfilename) {
		return $this->moveCacheFiles($this->album->name, $newfilename);
	}
	
	/**
	 * Removes cached files
	 * 
	 * @since 1.6.1
	 */
	function removeCacheFiles() {
		$cachefilestodelete = $this->getCacheFiles();
		foreach ($cachefilestodelete as $file) {
			@chmod($file, 0777);
			@unlink($file);
		}
	}

	/**
	 * Returns a path urlencoded image page link for the image
	 * 
	 * @param string $path Default null, optionally pass a path constant like WEBPATH or FULLWEBPATH
	 * @return string
	 */
	function getLink($path = null) {
		if (is_array($this->filename)) {
			$albumq = $album = dirname($this->filename['source']);
			$image = basename($this->filename['source']);
		} else {
			$album = $this->albumlink;
			$albumq = $this->albumnamealbum->name;
			$image = $this->filename;
		}
		return zp_apply_filter('getLink', rewrite_path(pathurlencode($album) . '/' . urlencode($image) . IM_SUFFIX, '/index.php?album=' . pathurlencode($albumq) . '&image=' . urlencode($image), $path), $this, NULL);
	}

	/**
	 * Returns a path to the original image in the original folder.
	 *
	 * @param string $path the "path" to the image. Defaults to the simple WEBPATH
	 *
	 * @return string
	 */
	function getFullImage($path = WEBPATH) {
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
	 * @param string $path the "path" to the image. Defaults to the simple WEBPATH
	 */
	function getFullImageURL($path = WEBPATH) {
		return $this->getFullImage($path);
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
		if (!$this->isPublished()) {
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
		$html = zp_apply_filter('standard_image_html', $html, $this);
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
			$custom = $cx = NULL;
			$cw = $sw;
			$ch = $sh;
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
	function getThumb($type = 'image') {
		$ts = getOption('thumb_size');
		if (getOption('thumb_crop')) {
			$sw = getOption('thumb_crop_width');
			$sh = getOption('thumb_crop_height');
			list($custom, $cw, $ch, $cx, $cy) = $this->getThumbCropping($ts, $sw, $sh);
			if ($custom) {
				$ts = null;
			}
		} else {
			$sw = $sh = $cw = $ch = $cx = $cy = null;
		}
		return $this->getCustomImage($ts, $sw, $sh, $cw, $ch, $cx, $cy, true);
	}

	/**
	 * Get the index of this image in the album, taking sorting into account.
	 * @param bool $use_realalbum If the image is wihtin a dynamic album this is the index within it, set to true to get the index of the actual physical album the image belongs
	 * @return int
	 */
	function getIndex($use_realalbum = false) {
		global $_zp_current_search, $_zp_current_album;
		if ($this->index == NULL) {
			if ($use_realalbum) {
				$album = $this->getAlbum();
			} else {
				$album = $this->albumnamealbum;
			}
			if ((!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) || $album->isDynamic()) {
				if ($album->isDynamic()) {
					$images = $album->getImages();
					for ($i = 0; $i < count($images); $i++) {
						$image = $images[$i];
						if ($this->filename == $image['filename']) {
							$this->index = $i;
							break;
						}
					}
				} else {
					$this->index = $_zp_current_search->getImageIndex($this->imagefolder, $this->filename);
				}
			} else {
				$images = $this->album->getImages(0);
				for ($i = 0; $i < count($images); $i++) {
					$image = $images[$i];
					if ($this->filename == $image) {
						$this->index = $i;
						break;
					}
				}
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
	 * Gets the owner of the image
	 * 
	 * @param bool $fullname Set to true to get the full name (if the owner is a vaild user of the site and has the full name defined)
	 * @return string
	 */
	function getOwner($fullname = false) {
		$owner = $this->get('owner');
		if (empty($owner)) {
			$owner = $this->album->getOwner();
		}
		if ($fullname) {
			return Administrator::getNameByUser($owner);
		}
		return $owner;
	}

	function setOwner($owner) {
		$this->set('owner', $owner);
	}
	/**
	 * checks access to the album
	 * @param bit $action User rights level, default LIST_RIGHTS
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action = LIST_RIGHTS) {
		$album = $this->album;
		return $album->isMyItem($action);
	}

	/**
	 * returns true if user is allowed to see the image
	 */
	function checkAccess(&$hint = NULL, &$show = NULL) {
		$album = $this->getAlbum();
		if ($album->isMyItem(LIST_RIGHTS)) {
			return $this->isPublic() || $album->albumSubRights() & (MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW);
		}
		return $album->checkforGuest($hint, $show) && $this->isPublic();
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
	 * Returns true if this image is published and also its album and all of its parents.
	 * 
	 * @since 1.5.5
	 * 
	 * @return bool
	 */
	function isPublic() {
		if (is_null($this->is_public)) {
			if (!$this->isPublished()) {
				return $this->is_public = false;
			}
			$album = $this->getAlbum();
			if(!$album->isPublic()) {
				return $this->is_public = false;
			}
			return $this->is_public = true;
		} else {
			return $this->is_public;
		}
	}
	
	/**
	 * Returns the filesize in bytes of the full image
	 * 
	 * @since 1.5.2
	 * 
	 * @return int|false
	 */
	function getFilesize() {
		$album = $this->getAlbum();
		$filesize = filesize($this->getFullImage(SERVERPATH));
		return $filesize;
	}
	
	/**
	 * Sets the current date to the images'album and all of its parent albums recursively
	 * @since 1.5.5
	 */
	function setUpdatedDateAlbum() {
		$album = $this->album;
		if($album) {
			$album->setUpdatedDate();
			$album->save();
			$album->setUpdatedDateParents();
		}
	}
	
	/**
	 * Returns true if the image is a "photo"
	 * 
	 * @since 1.6
	 * 
	 * @return bool
	 */
	function isPhoto() {
		$class = strtolower(get_class($this));
		return $class == 'image' || $class == 'transientimage';
	}

	/**
	 * Returns true if the image is an "video" file
	 * 
	 * @since 1.6
	 * 
	 * @return bool
	 */
	function isVideo() {
		return strtolower(get_class($this)) == 'video';
	}
	
	/**
	 * Calculate the aspect ratio from width and height
	 * 
	 * @source https://stackoverflow.com/a/71730390
	 * 
	 * @since 1.6.1
	 * 
	 * @param int $width
	 * @param int $height
	 * @param string $separator Separator for the aspect ratio. Defaul ":"
	 */
	static function calculateAspectRatio($width = null, $height = null, $separator = ':') {
		$ratio = [$width, $height];
		for ($x = $ratio[1]; $x > 1; $x--) {
			if (($ratio[0] % $x) == 0 && ($ratio[1] % $x) == 0) {
				$ratio = [$ratio[0] / $x, $ratio[1] / $x];
			}
		}
		return implode($separator, $ratio);
	}

}
