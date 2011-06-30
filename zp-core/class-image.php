<?php
/**
 *Image Class
 * @package classes
 */

// force UTF-8 Ø

$_zp_extra_filetypes = array(); // contains file extensions and the handler class

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
function newImage($album, $filename, $quiet=false) {
	global $_zp_extra_filetypes;
	if (is_array($filename)) {
		$xalbum = new Album(new Gallery(),$filename['folder']);
		$filename = $filename['filename'];
	} else {
		$xalbum = $album;
	}
	if (!is_object($xalbum) || strtoLower(get_class($xalbum)) != 'album' || !$xalbum->exists) {
		$msg = sprintf(gettext('Bad album object parameter to newImage(%s)'),$filename);
		debugLogBacktrace($msg);
		trigger_error(html_encode($msg), E_USER_NOTICE);
		return NULL;
	}
	if ($ext = is_valid_other_type($filename)) {
		$object = $_zp_extra_filetypes[$ext];
		$image = New $object($xalbum, $filename);
	} else {
		if (is_valid_image($filename)) {
			$image = New _Image($xalbum, $filename);
		} else {
			$image = NULL;
		}
	}
	if ($image) {
		zp_apply_filter('image_instantiate', $image);
		if ($image->exists) {
			return $image;
		} else {
			return NULL;
		}
	}

	if (!$quiet) {
		$msg = sprintf(gettext('Bad filename suffix in newImage(%s)'),$filename);
		debugLogBacktrace($msg);
		trigger_error(html_encode($msg), E_USER_NOTICE);
	}
	return NULL;
}

/**
 * Returns true if the object is a zenphoto 'image'
 *
 * @param object $image
 * @return bool
 */
function isImageClass($image=NULL) {
	global $_zp_extra_filetypes;
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE)) return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	return is_object($image) && ($image->table == 'images');
}

/**
 * handles 'picture' images
 */
class _Image extends MediaObject {

	var $filename;      			// true filename of the image.
	var $exists = true; 			// Does the image exist?
	var $webpath;       			// The full URL path to the original image.
	var $localpath;     			// Latin1 full SERVER path to the original image.
	var $displayname;   			// $filename with the extension stripped off.
	var $album;         			// An album object for the album containing this image.
	var $index;         			// The index of the current image in the album array.
	var $sortorder;     			// The position that this image should be shown in the album
	var $filemtime;     			// Last modified time of this image
	var $sidecars = array();	// keeps the list of suffixes associated with this image
	var $manage_rights = MANAGE_ALL_ALBUM_RIGHTS;
	var $manage_some_rights = ALBUM_RIGHTS;
	var $view_rights = VIEW_ALBUMS_RIGHTS;


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
	function _Image(&$album, $filename) {
		global $_zp_current_admin_obj;
		// $album is an Album object; it should already be created.
		if (!is_object($album)) return NULL;
		if (!$this->classSetup($album, $filename)) { // spoof attempt
			$this->exists = false;
			return;
		}
		// Check if the file exists.
		if (!file_exists($this->localpath) || is_dir($this->localpath)) {
			$this->exists = false;
			return;
		}

		// This is where the magic happens...
		$album_name = $album->name;
		$new = parent::PersistentObject('images', array('filename'=>$filename, 'albumid'=>$this->album->id), 'filename', false, empty($album_name));
		$mtime = filemtime($this->localpath);
		if ($new || ($mtime != $this->get('mtime'))) {
			$this->setShow(getOption('image_publish'));
			$this->set('mtime', $mtime);
			$this->updateMetaData();			// extract info from image
			$this->updateDimensions();		// deal with rotation issues
			$this->save();
			if ($new) zp_apply_filter('new_image', $this);
		}
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
	function classSetup(&$album, $filename) {
		global $_zp_current_admin_obj;
		$fileFS = internalToFilesystem($filename);
		if ($filename != filesystemToInternal($fileFS)) { // image name spoof attempt
			return false;
		}
		$this->album = &$album;
		if ($album->name == '') {
			$this->webpath = ALBUM_FOLDER_WEBPATH . $filename;
			$this->encwebpath = ALBUM_FOLDER_WEBPATH . rawurlencode($filename);
			$this->localpath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($filename);
		} else {
			$this->webpath = ALBUM_FOLDER_WEBPATH . $album->name . "/" . $filename;
			$this->encwebpath = ALBUM_FOLDER_WEBPATH . pathurlencode($album->name) . "/" . rawurlencode($filename);
			$this->localpath = $album->localpath . $fileFS;
		}
		$this->filename = $filename;
		$this->displayname = substr($this->filename, 0, strrpos($this->filename, '.'));
		if (empty($this->displayname)) $this->displayname = $this->filename;
		$this->comments = null;
		$this->filemtime = @filemtime($this->localpath);
		$this->imagetype = strtolower(get_class($this)).'s';
		$date = $this->get('date');
		if (empty($date)) $this->set('date', strftime('%Y-%m-%d %H:%M:%S', $this->filemtime));
		return true;
	}

	function getDefaultTitle() {
		return $this->displayname;
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
	function fileChanged() {
		$storedmtime = $this->get('mtime');
		return (empty($storedmtime) || $this->filemtime > $storedmtime);
	}

	/**
	 * Returns an array of EXIF data
	 *
	 * @return array
	 */
	function getMetaData() {
		require_once(dirname(__FILE__).'/exif/exif.php');
		global $_zp_exifvars;
		$exif = array();
		// Put together an array of EXIF data to return
		foreach($_zp_exifvars as $field => $exifvar) {
			$exif[$field] = $this->get($field);
		}
		return $exif;
	}

	/**
	 * Parses Exif/IPTC data
	 *
	 */
	function updateMetaData() {
		require_once(dirname(__FILE__).'/exif/exif.php');
		global $_zp_exifvars;
		$IPTCtags = array(
											'SKIP'								=>	'2#000',	//	Record Version										Size:64
											'ObjectType'					=>	'2#003',	//	Object Type	Ref										Size:67
											'ObjectAttr'					=>	'2#004',	//	Object Attribute Ref							Size:67
											'ObjectName'					=>	'2#005',	//	Object name												Size:64
											'EditStatus'					=>	'2#007',	//	Edit Status												Size:64
											'EditorialUpdate'			=>	'2#008',	//	Editorial Update									Size:2
											'Urgency'							=>	'2#010',	//	Urgency														Size:1
											'SubRef'							=>	'2#012',	//	Subject	Reference									Size:236
											'Category'						=>	'2#015',	//	Category 													Size:3
											'SuppCategory'				=>	'2#020',	//	Supplemental category							Size:32
											'FixtureID'						=>	'2#022',	//	Fixture	ID 												Size:32
											'Keywords'						=>	'2#025',	//	Keywords 													Size:64
											'ContentLocCode'			=>	'2#026',	//	Content	Location Code							Size:3
											'ContentLocName'			=>	'2#027',	//	Content	Location Name							Size:64
											'ReleaseDate'					=>	'2#030',	//	Release	Date 											Size:8
											'ReleaseTime'					=>	'2#035',	//	Release	Time											Size:11
											'ExpireDate'					=>	'2#037',	//	Expiration Date										Size:8
											'ExpireTime'					=>	'2#038',	//	Expiration Time										Size:11
											'SpecialInstru'				=>	'2#040',	//	Special Instructions							Size:256
											'ActionAdvised'				=>	'2#042',	//	Action Advised										Size:2
											'RefService'					=>	'2#045',	//	Reference Service									Size:10
											'RefDate'							=>	'2#047',	//	Reference Date										Size:8
											'RefNumber'						=>	'2#050',	//	Reference Number									Size:8
											'DateCreated'					=>	'2#055',	//	Date created											Size:8
											'TimeCreated'					=>	'2#060',	//	Time created											Size:11
											'DigitizeDate'				=>	'2#062',	//	Digital Creation Date							Size:8
											'DigitizeTime'				=>	'2#063',	//	Digital Creation Time							Size:11
											'OriginatingProgram'	=>	'2#065',	//	Originating Program								Size:32
											'ProgramVersion'			=>	'2#070',	//	Program version										Size:10
											'ObjectCycle'					=>	'2#075',	//	Object Cycle											Size:1
											'ByLine'							=>	'2#080',	//	ByLine 														Size:32
											'ByLineTitle'					=>	'2#085',	//	ByLine Title											Size:32
											'City'								=>	'2#090',	//	City															Size:32
											'SubLocation'					=>	'2#092',	//	Sublocation												Size:32
											'State'								=>	'2#095',	//	Province/State										Size:32
											'LocationCode'				=>	'2#100',	//	Country/Primary	Location Code			Size:3
											'LocationName'				=>	'2#101',	//	Country/Primary	Location Name			Size:64
											'TransmissionRef'			=>	'2#103',	//	Original Transmission Reference		Size:32
											'ImageHeadline'				=>	'2#105',	//	Image headline										Size:256
											'ImageCredit'					=>	'2#110',	//	Image credit											Size:32
											'Source'							=>	'2#115',	//	Source														Size:32
											'Copyright'						=>	'2#116',	//	Copyright Notice									Size:128
											'Contact'							=>	'2#118',	//	Contact														Size:128
											'ImageCaption'				=>	'2#120',	//	Image caption											Size:2000
											'ImageCaptionWriter'	=>	'2#122',	//	Image caption writer							Size:32
											'ImageType'						=>	'2#130',	//	Image type												Size:2
											'Orientation'					=>	'2#131',	//	Image	 rientation									Size:1
											'LangID'							=>	'2#135',	//	Language ID												Size:3
											'Subfile'							=>	'8#010'		//	Subfile														Size:2
		);
		$this->set('hasMetadata',0);
		$result = array();
		if (get_class($this)=='_Image') {
			$localpath = $this->localpath;
		} else {
			$localpath = $this->getThumbImageFile();
		}
		$xdate = $this->getDateTime();

		if (!empty($localpath)) { // there is some kind of image to get metadata from
			$exifraw = read_exif_data_protected($localpath);
			if (isset($exifraw['ValidEXIFData'])) {
				$this->set('hasMetadata',1);
				foreach($_zp_exifvars as $field => $exifvar) {
					if (isset($exifraw[$exifvar[0]][$exifvar[1]])) {
						$exif = trim(sanitize($exifraw[$exifvar[0]][$exifvar[1]],1));
						$this->set($field, $exif);
					} else if (isset($exifraw[$exifvar[0]]['MakerNote'][$exifvar[1]])) {
						$exif = trim(sanitize($exifraw[$exifvar[0]]['MakerNote'][$exifvar[1]],1));
						$this->set($field, $exif);
					}
				}
			}
			/* check IPTC data */
			$iptcdata = zp_imageIPTC($localpath);
			if (!empty($iptcdata)) {
				$iptc = iptcparse($iptcdata);
				if ($iptc) {
					$this->set('hasMetadata',1);
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
					foreach ($_zp_exifvars as $field=>$exifvar) {
						if ($exifvar[0]=='IPTC') {
							$datum = $this->getIPTCTag($IPTCtags[$exifvar[1]], $iptc);
							$this->set($field, $this->prepIPTCString($datum, $characterset));
						}
					}
					/* iptc keywords (tags) */
					$datum = $this->getIPTCTagArray('2#025', $iptc);
					if (is_array($datum)) {
						$tags = array();
						$result['tags'] = array();
						foreach ($datum as $item) {
							$tags[] = $this->prepIPTCString(sanitize($item,3), $characterset);;
						}
						$this->setTags($tags);
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
			$date = substr($date, 0, 4).'-'.substr($date, 4, 2).'-'.substr($date, 6, 2);
			if (!empty($time)) {
				$date = $date.' '.substr($time, 0, 2).':'.substr($time, 2, 2).':'.substr($time, 4, 2);
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

		$x = $this->getTitle();
		if (empty($x)) {
			$this->set('title',$this->getDefaultTitle());
		}
		if (empty($xdate)) {
			$this->set('date', strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
		}
		$alb = $this->album;
		if (!is_null($alb)) {
			$save = false;
			if (strtotime($alb->getUpdatedDate()) < strtotime($this->getDateTime())) {
				$alb->setUpdatedDate($this->getDateTime());
				$save = true;
			}
			if (is_null($albdate = $alb->getDateTime()) || ($this->album->gallery->getAlbumUseImagedate() && strtotime($albdate) < strtotime($this->getDateTime()))) {
				$this->album->setDateTime($this->getDateTime());   //  not necessarily the right one, but will do. Can be changed in Admin
				$save = true;
			}
			if ($save) {
				$this->album->save();
			}
		}
	}

	/**
	 * For internal use--fetches a single tag from IPTC data
	 *
	 * @param string $tag the metadata tag sought
	 * @return string
	 */
	function getIPTCTag($tag, $iptc) {
		if (isset($iptc[$tag])) {
			$iptcTag = $iptc[$tag];
			$r = "";
			$ct = count($iptcTag);
			for ($i=0; $i<$ct; $i++) {
				$w = $iptcTag[$i];
				if (!empty($r)) { $r .= ", "; }
				$r .= $w;
			}
			return trim($r);
		}
		return '';
	}

	/**
	 * For internal use--fetches the IPTC array for a single tag.
	 *
	 * @param string $tag the metadata tag sought
	 * @return array
	 */
	function getIPTCTagArray($tag, $iptc) {
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
	function prepIPTCString($iptcstring, $characterset) {
		global $_zp_UTF8;
		// Remove null byte at the end of the string if it exists.
		if (substr($iptcstring, -1) === 0x0) {
			$iptcstring = substr($iptcstring, 0, -1);
		}
		$outputset = LOCAL_CHARSET;
		if ($characterset == $outputset) return $iptcstring;
		$iptcstring = $_zp_UTF8->convert($iptcstring, $characterset, $outputset);
		return trim(sanitize($iptcstring,1));
	}
	/**
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		$discard = NULL;
		$size = zp_imageDims($this->localpath, $discard);
		$width = $size['width'];
		$height = $size['height'];
		if (zp_imageCanRotate() && getOption('auto_rotate'))  {
			// Swap the width and height values if the image should be rotated
			$splits = preg_split('/!([(0-9)])/', $this->get('EXIFOrientation'));
			$rotation = $splits[0];
			switch ($rotation) {
				case 5:
				case 6:
				case 7:
				case 8:
					$width = $size['height'];
					$height =$size['width'];
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

	/**
	 * Returns the album that holds this image
	 *
	 * @return object
	 */
	function getAlbum() { return $this->album; }

	/**
	 * Retuns the folder name of the album that holds this image
	 *
	 * @return string
	 */
	function getAlbumName() { return $this->album->name; }

	/**
	 * Returns the location field of the image
	 *
	 * @return string
	 */
	function getLocation() {
		return get_language_string($this->get('location'));
	}

	/**
	 * Stores the location field of the image
	 *
	 * @param string $location text for the location
	 */
	function setLocation($location) { $this->set('location', $location); }

	/**
	 * Returns the city field of the image
	 *
	 * @return string
	 */
	function getCity() {
		return get_language_string($this->get('city'));
	}

	/**
	 * Stores the city field of the image
	 *
	 * @param string $city text for the city
	 */
	function setCity($city) { $this->set('city', $city); }

	/**
	 * Returns the state field of the image
	 *
	 * @return string
	 */
	function getState() {
		return get_language_string($this->get('state'));
	}

	/**
	 * Stores the state field of the image
	 *
	 * @param string $state text for the state
	 */
	function setState($state) { $this->set('state', $state); }

	/**
	 * Returns the country field of the image
	 *
	 * @return string
	 */
	function getCountry() {
		return get_language_string($this->get('country'));
	}

	/**
	 * Stores the country field of the image
	 *
	 * @param string $country text for the country filed
	 */
	function setCountry($country) { $this->set('country', $country); }

	/**
	 * Returns the credit field of the image
	 *
	 * @return string
	 */
	function getCredit() {
		return get_language_string($this->get('credit'));
	}

	/**
	 * Stores the credit field of the image
	 *
	 * @param string $credit text for the credit field
	 */
	function setCredit($credit) { $this->set('credit', $credit); }

	/**
	 * Returns the copyright field of the image
	 *
	 * @return string
	 */
	function getCopyright() {
		return get_language_string($this->get('copyright'));
	}

	/**
	 * Stores the text for the copyright field of the image
	 *
	 * @param string $copyright text for the copyright field
	 */
	function setCopyright($copyright) { $this->set('copyright', $copyright); }

	/**
	 * Permanently delete this image (permanent: be careful!)
	 * Returns the result of the unlink operation (whether the delete was successful)
	 * @param bool $clean whether to remove the database entry.
	 * @return bool
	 */

	function remove() {
		if (parent::remove()) {
			$result = true;
			$filestodelete = safe_glob(substr($this->localpath,0,strrpos($this->localpath,'.')).'.*');
			foreach ($filestodelete as $file) {
				$result = $result && @unlink($file);
			}
			if ($result) {
				query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='images' AND `objectid`=" . $this->id);
				query("DELETE FROM ".prefix('comments') . "WHERE `type` ='images' AND `ownerid`=" . $this->id);
			}
			return $result;
		}
		return false;
	}

	/**
	 * Moves an image to a new album and/or filename (rename).
	 * Returns  0 on success and error indicator on failure.
	 * @param Album $newalbum the album to move this file to. Must be a valid Album object.
	 * @param string $newfilename the new file name of the image in the specified album.
	 * @return int
	 */
	function moveImage($newalbum, $newfilename=null) {
		if (is_string($newalbum)) $newalbum = new Album($this->album->gallery, $newalbum, false);
		if ($newfilename == null) {
			$newfilename = $this->filename;
		} else {
			if (getSuffix($this->filename) != getSuffix($newfilename)) {	// that is a no-no
				return 6;
			}
		}
		if ($newalbum->id == $this->album->id && $newfilename == $this->filename) {
			// Nothing to do - moving the file to the same place.
			return 2;
		}
		$newpath = $newalbum->localpath . internalToFilesystem($newfilename);
		if (file_exists($newpath)) {
			// If the file exists, don't overwrite it.
			return 2;
		}
		$filename = basename($this->localpath);
		$result = @rename($this->localpath, $newpath);
		if ($result) {
			$filestomove = safe_glob(substr($this->localpath,0,strrpos($this->localpath,'.')).'.*');
			foreach ($filestomove as $file) {
				if(in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$result = $result && @rename($file, $newalbum->localpath . basename($file));
				}
			}
		}
		if ($result) {
			if ($this->move(array('filename'=>$newfilename, 'albumid'=>$newalbum->id))) {
				$this->set('mtime',filemtime($newpath));
				$this->save();
				return 0;
			}
		}
		return 1;
	}

	/**
	 * Renames an image to a new filename, keeping it in the same album. Convenience for moveImage($image->album, $newfilename).
	 * Returns  true on success and false on failure.
	 * @param string $newfilename the new file name of the image file.
	 * @return bool
	 */
	function rename($newfilename) {
		return $this->moveImage($this->album, $newfilename);
	}

	/**
	 * Copies the image to a new album, along with all metadata.
	 *
	 * @param string $newalbum the destination album
	 */
	function copy($newalbum) {
		if (is_string($newalbum)) {
			$newalbum = new Album($this->album->gallery, $newalbum, false);
		}
		if ($newalbum->id == $this->album->id) {
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
			$filestocopy = safe_glob(substr($this->localpath,0,strrpos($this->localpath,'.')).'.*');
			foreach ($filestocopy as $file) {
				if(in_array(strtolower(getSuffix($file)), $this->sidecars)) {
					$result = $result && @copy($file, $newalbum->localpath . basename($file));
				}
			}
		}
		if ($result) {
			if ($newID = parent::copy(array('filename'=>$filename, 'albumid'=>$newalbum->id))) {
				storeTags(readTags($this->getID(), 'images'), $newID, 'images');
				query('UPDATE '.prefix('images').' SET `mtime`='.filemtime($newpath).' WHERE `filename`="'.$filename.'" AND `albumid`='.$newalbum->id);
				return 0;
			}
		}
		return 1;
	}

	/**** Image Methods ****/

	/**
	 * Returns an image page link for the image
	 *
	 * @return string
	 */
	function getImageLink() {
		return rewrite_path('/' . pathurlencode($this->album->name) . '/' . urlencode($this->filename) . IM_SUFFIX,
			'/index.php?album=' . pathurlencode($this->album->name) . '&image=' . urlencode($this->filename));
	}

	/**
	 * Returns a path to the original image in the original folder.
	 *
	 * @param string $path the "path" to the image. Defaults to the simple WEBPATH
	 *
	 * @return string
	 */
	function getFullImage($path=WEBPATH) {
		return getAlbumFolder($path) . ($this->album->name) . "/" . ($this->filename);
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
		$cachefilename = getImageCacheFilename($this->album->name, $this->filename, $args);
		if (file_exists(SERVERCACHE . $cachefilename) && filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
			return WEBPATH . '/'.CACHEFOLDER . imgSrcURI($cachefilename);
		} else {
			return getImageProcessorURI($args,$this->album->name,$this->filename);
		}
	}

	/**
	 *  Get a custom sized version of this image based on the parameters.
	 *
	 * @param string $alt Alt text for the url
	 * @param int $size size
	 * @param int $width width
	 * @param int $height height
	 * @param int $cropw crop width
	 * @param int $croph crop height
	 * @param int $cropx crop x axis
	 * @param int $cropy crop y axis
	 * @param string $class Optional style class
	 * @param string $id Optional style id
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects set to desired image effect (e.g. 'gray' to force gray scale)
	 * @return string
	 */
	function getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin=false, $effects=NULL) {
		if ($thumbStandin < 0) {
			$wmt = '!';
		} else {
			if ($thumbStandin) {
				$wmt = getWatermarkParam($this, WATERMARK_THUMB);
			} else {
				$wmt = getWatermarkParam($this,  WATERMARK_IMAGE);
			}
		}
		$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
		$cachefilename = getImageCacheFilename($this->album->name, $this->filename,	$args);
		if (file_exists(SERVERCACHE . $cachefilename) && filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
			return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
		} else {
			return getImageProcessorURI($args, $this->album->name, $this->filename);
		}
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return s
	 */
	function getThumbImageFile($path=NULL) {
		$local = $this->localpath;
		if (!is_null($path)) {
			$local = $path.str_replace(SERVERPATH,$local);
		}
		return $local;
	}

	/**
	 * Returns an array of cropping parameters. Used as a "helper" function for various
	 * inherited getThumb() methods
	 *
	 * @param string $type the type of thumb (in case it ever matters in the cropping, now it does not.)
	 */
	function getThumbCropping($type) {
		$sw = getOption('thumb_crop_width');
		$sh = getOption('thumb_crop_height');
		if (is_null($cy = $this->get('thumbY'))) {
			$custom = $cx = $cw = $ch = NULL;
		} else {
			$custom = true;
			$cx = $this->get('thumbX');
			$cw = $this->get('thumbW');
			$ch = $this->get('thumbH');
			// upscale to thumb_size proportions
			if ($sw == $sh) { // square crop, set the size/width to thumbsize
				$sw = $sh = getOption('thumb_size');
			} else {
				if ($sw > $sh) {
					$r = $ts/$sw;
					$sw = $ts;
					$sh = $sh * $r;
				} else {
					$r = $ts/$sh;
					$sh = $ts;
					$sh = $r * $sh;
				}
			}
		}
		return array($custom, $sw, $sh, $cw, $ch, $cx, $cy);
	}

	/**
	 * Get a default-sized thumbnail of this image.
	 *
	 * @return string
	 */
	function getThumb($type='image') {
		$ts = getOption('thumb_size');
		if (getOption('thumb_crop')) {
			list($custom, $sw, $sh, $cw, $ch, $cx, $cy) = $this->getThumbCropping($type);
			if ($custom) {
				return $this->getCustomImage(NULL, $sw, $sh, $cw, $ch, $cx, $cy, true);
			}
		} else {
			$sw = $sh = NULL;
		}
		$filename = $this->filename;
		$wmt = getWatermarkParam($this, WATERMARK_THUMB);
		$args = getImageParameters(array($ts, NULL, NULL, $sw, $sh, NULL, NULL, NULL, true, NULL, true, $wmt, NULL, NULL), $this->album->name);
		$cachefilename = getImageCacheFilename($alb = $this->album->name, $filename, $args);
		if (file_exists(SERVERCACHE . $cachefilename)	&& filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
			return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
		} else {
			return getImageProcessorURI($args, $this->album->name, $this->filename);
		}
	}

	/**
	 * Get the index of this image in the album, taking sorting into account.
	 *
	 * @return int
	 */
	function getIndex() {
		global $_zp_current_search, $_zp_current_album;
		if ($this->index == NULL) {
			$album = $this->getAlbum();
			if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED) || $album->isDynamic()) {
				if ($album->isDynamic()) {
					$images = $album->getImages();
					for ($i=0; $i < count($images); $i++) {
						$image = $images[$i];
						if ($this->filename == $image['filename']) {
							$this->index = $i;
							break;
						}
					}
				} else {
					$this->index = $_zp_current_search->getImageIndex($this->album->name, $this->filename);
				}
			} else {
				$images = $this->album->getImages(0);
				for ($i=0; $i < count($images); $i++) {
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
			$image = $_zp_current_search->getImage($index+1);
		} else {
			$image = $this->album->getImage($index+1);
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
			$image = $_zp_current_search->getImage($index-1);
		} else {
			$image = $this->album->getImage($index-1);
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
		$this->set('owner',$owner);
	}

	function isMyItem($action) {
		$album = $this->album;
		return $album->isMyItem($action);
	}

	/**
	 * returns true if user is allowed to see the album
	 */
	function checkAccess(&$hint=NULL, &$show=NULL) {
		$album = $this->getAlbum();
		if ($album->isMyItem($this->view_rights)) {
			return true;
		}
		return $album->checkforGuest($hint=NULL, $show=NULL);
	}

	/**
	 * Checks if guest is loggedin for the album
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkforGuest(&$hint=NULL, &$show=NULL) {
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
?>
