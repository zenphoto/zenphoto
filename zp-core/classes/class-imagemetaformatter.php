<?php

/**
 * Class to handle metadata formatting
 * 
 * Partly adapted from:
 * Exifer 1.7
 * 
 * Originally created by:
 * Copyright © 2005 Jake Olefsky
 * http:// www.offsky.com/software/exif/index.php
 * jake@olefsky.com
 * 
 * @since 1.6.5
 */
class imageMetaFormatter {

	/**
	 * Formats metadata. Meta fields not handled are returned as strings/raw
	 * 
	 * @TODO Since this traditionally formats EXIF fields only
	 * 
	 * @since 1.6.5 Partly adapted from Exifer 1.7 library 
	 * @author Jake Olefsky jake@olefsky.com, adapted by Malte Müller (acrylian)
	 * @param type $tag The metadata name of the field (not the db name!)
	 * @param mixed $data The value to format
	 * @param array $exifdata The full exif data as return by exif_read_data() 
	 * @return string
	 */
	static function formatData($tag, $data, $exifdata = array()) {
		/*
		 * Some tags are included although we don't support them by default
		 * but the original Exifer lib did handle them nevertheless
		 */
		switch ($tag) {
			default:
				if (is_array($data)) {
					$data = serialize($data); // we might not know what to do else but we can at least store it
				}
				break;
			case 'XResolution':
			case 'YResolution':
				$data = self::rationalNum($data);
				if (is_numeric($data)) {
					$data = round($data);
				}
				$data = $data . ' dots per ResolutionUnit';
				break;
			case 'ExposureTime':
				$data = self::formatExposure(self::rationalNum($data));
				break;
			case 'FNumber':
				$data = self::rationalNum($data);
				if (is_numeric($data)) {
					$data = round($data, 2);
				}
				$data = 'f/' . $data;
				break;
			case 'DateTime':
			case 'DateTimeOriginal':
			case 'DateTimeDigitized':
			case 'DateCreated': // IPTC
			case 'DigitizeDate': // IPTC
			case 'TimeCreated': // IPTC
			case 'DigitizeTime': // IPTC
				/*
				 * Datetime formats are formatted on output only traditionally!
				 */
				break;
			case 'ShutterSpeedValue':
				// The ShutterSpeedValue is given in the APEX mode. Many thanks to Matthieu Froment for this code
				// The formula is : Shutter = - log2(exposureTime) (Appendix C of EXIF spec.)
				// Where shutter is in APEX, log2(exposure) = ln(exposure)/ln(2)
				// So final formula is : exposure = exp(-ln(2).shutter)
				// The formula can be developed : exposure = 1/(exp(ln(2).shutter))
				$datum = self::rationalNum($data);
				if (is_numeric($datum)) {
					$datum = exp($datum * log(2));
					if ($datum != 0) {
						$datum = 1 / $datum;
					}
				}
				$data = self::formatExposure($datum);
				break;
			case 'ApertureValue':
			case 'MaxApertureValue':
				// ApertureValue is given in the APEX Mode. Many thanks to Matthieu Froment for this code
				// The formula is : Aperture = 2*log2(FNumber) <=> FNumber = e((Aperture.ln(2))/2)
				$datum = self::rationalNum($data);
				if (is_numeric($datum)) {
					$datum = exp(($datum * log(2)) / 2);
					$data = 'f/' . round($datum, 1); // Focal is given with a precision of 1 digit.
				} else {
					$data = 'f/' . $datum;
				}
				break;
			case 'ExposureBiasValue':
				$data = self::rationalNum($data);
				if (is_numeric($data)) {
					$data = round($data, 2);
				}
				$data = $data . ' EV';
				break;
			case 'FocalLength':
				$data = self::rationalNum($data);
				if (is_numeric($data)) {
					$data = round(floatval($data), 1) . ' mm';
				}
				break;
			case 'Orientation':
				// Example of how all of these tag formatters should be...
				switch ($data) {
					case 1 : $data = gettext('1: Horizontal (normal)');
						break;
					case 2 : $data = gettext('2: Mirror horizontal');
						break;
					case 3 : $data = gettext('3: Rotate 180 CW');
						break;
					case 4 : $data = gettext('4: Mirror vertical');
						break;
					case 5 : $data = gettext('5: Mirror horizontal and rotate 270 CW');
						break;
					case 6 : $data = gettext('6: Rotate 90 CW');
						break;
					case 7 : $data = gettext('7: Mirror horizontal and rotate 90 CW');
						break;
					case 8 : $data = gettext('8: Rotate 270 CW');
						break;
					default : $data = sprintf(gettext('%d: Unknown'), $data);
						break;
				}
				break;
			case 'ResolutionUnit':
			case 'FocalPlaneResolutionUnit':
			case 'ThumbnailResolutionUnit':
				switch ($data) {
					case 1: $data = gettext('No Unit');
						break;
					case 2: $data = gettext('Inch');
						break;
					case 3: $data = gettext('Centimeter');
						break;
					case 4: $data = gettext('Millimeter');
						break;
					case 5: $data = gettext('Micrometer');
						break;
				}
				break;
			case 'YCbCrPositioning':
				switch ($data) {
					case 1: $data = gettext('Center of Pixel Array');
						break;
					case 2: $data = gettext('Datum Point');
						break;
				}
				break;
			case 'ExposureProgram':
				switch ($data) {
					case 1: $data = gettext('Manual');
						break;
					case 2: $data = gettext('Program');
						break;
					case 3: $data = gettext('Aperture Priority');
						break;
					case 4: $data = gettext('Shutter Priority');
						break;
					case 5: $data = gettext('Program Creative');
						break;
					case 6: $data = gettext('Program Action');
						break;
					case 7: $data = gettext('Portrait');
						break;
					case 8: $data = gettext('Landscape');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'SensitivityType':
				switch ($data) {
					case 1: $data = gettext('Standard Output Sensitivity');
						break;
					case 2: $data = gettext('Recommended Exposure Index');
						break;
					case 3: $data = gettext('ISO Speed');
						break;
					case 4: $data = gettext('Standard Output Sensitivity and Recommended Exposure Index');
						break;
					case 5: $data = gettext('Standard Output Sensitivity and ISO Speed');
						break;
					case 6: $data = gettext('Recommended Exposure Index and ISO Speed');
						break;
					case 7: $data = gettext('Standard Output Sensitivity, Recommended Exposure Index and ISO Speed');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'MeteringMode':
				switch ($data) {
					case 1: $data = gettext('Average');
						break;
					case 2: $data = gettext('Center Weighted Average');
						break;
					case 3: $data = gettext('Spot');
						break;
					case 4: $data = gettext('Multi-Spot');
						break;
					case 5: $data = gettext('Pattern');
						break;
					case 6: $data = gettext('Partial');
						break;
					case 255: $data = gettext('Other');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'LightSource':
				switch ($data) {
					case 1: $data = gettext('Daylight');
						break;
					case 2: $data = gettext('Fluorescent');
						break;
					case 3: $data = gettext('Tungsten');
						break; // 3 Tungsten (Incandescent light)
					// 4 Flash
					// 9 Fine Weather
					case 10: $data = gettext('Flash');
						break; // 10 Cloudy Weather
					// 11 Shade
					// 12 Daylight Fluorescent (D 5700 - 7100K)
					// 13 Day White Fluorescent (N 4600 - 5400K)
					// 14 Cool White Fluorescent (W 3900 -4500K)
					// 15 White Fluorescent (WW 3200 - 3700K)
					// 10 Flash
					case 17: $data = gettext('Standard Light A');
						break;
					case 18: $data = gettext('Standard Light B');
						break;
					case 19: $data = gettext('Standard Light C');
						break;
					case 20: $data = gettext('D55');
						break;
					case 21: $data = gettext('D65');
						break;
					case 22: $data = gettext('D75');
						break;
					case 23: $data = gettext('D50');
						break;
					case 24: $data = gettext('ISO Studio Tungsten');
						break;
					case 255: $data = gettext('Other');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'Flash':
				switch ($data) {
					case 0:
					case 16:
					case 24:
					case 32:
					case 64:
					case 80: $data = gettext('No Flash');
						break;
					case 1: $data = gettext('Flash');
						break;
					case 5: $data = gettext('Flash, strobe return light not detected');
						break;
					case 7: $data = gettext('Flash, strobe return light detected');
						break;
					case 9: $data = gettext('Compulsory Flash');
						break;
					case 13: $data = gettext('Compulsory Flash, Return light not detected');
						break;
					case 15: $data = gettext('Compulsory Flash, Return light detected');
						break;
					case 25: $data = gettext('Flash, Auto-Mode');
						break;
					case 29: $data = gettext('Flash, Auto-Mode, Return light not detected');
						break;
					case 31: $data = gettext('Flash, Auto-Mode, Return light detected');
						break;
					case 65: $data = gettext('Red Eye');
						break;
					case 69: $data = gettext('Red Eye, Return light not detected');
						break;
					case 71: $data = gettext('Red Eye, Return light detected');
						break;
					case 73: $data = gettext('Red Eye, Compulsory Flash');
						break;
					case 77: $data = gettext('Red Eye, Compulsory Flash, Return light not detected');
						break;
					case 79: $data = gettext('Red Eye, Compulsory Flash, Return light detected');
						break;
					case 89: $data = gettext('Red Eye, Auto-Mode');
						break;
					case 93: $data = gettext('Red Eye, Auto-Mode, Return light not detected');
						break;
					case 95: $data = gettext('Red Eye, Auto-Mode, Return light detected');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'ColorSpace':
				if ($data == 1) {
					$data = gettext('sRGB');
				} else {
					$data = gettext('Uncalibrated');
				}
				break;
			case 'ExifImageWidth':
			case 'ExifImageLength': // PHP follows EXif spec and uses this instead of ExifImageHeight
				$data = $data  . ' ' . gettext('px');
				break;
			case 'Compression': // Compression
				switch ($data) {
					case 1: $data = gettext('No Compression');
						break;
					case 6: $data = gettext('Jpeg Compression');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'SensingMethod':
				switch ($data) {
					case 1: $data = gettext('Not defined');
						break;
					case 2: $data = gettext('One Chip Color Area Sensor');
						break;
					case 3: $data = gettext('Two Chip Color Area Sensor');
						break;
					case 4: $data = gettext('Three Chip Color Area Sensor');
						break;
					case 5: $data = gettext('Color Sequential Area Sensor');
						break;
					case 7: $data = gettext('Trilinear Sensor');
						break;
					case 8: $data = gettext('Color Sequential Linear Sensor');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'PhotometricInterpretation':
				switch ($data) {
					case 1: $data = gettext('Monochrome');
						break;
					case 2: $data = gettext('RGB');
						break;
					case 6: $data = gettext('YCbCr');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'ExifVersion':
			case 'FlashPixVersion':
			case 'InteroperabilityVersion':
				$data = gettext('version') . ' ' . (intval($data) / 100);
				break;
			case 'WhiteBalance':
				switch ($data) {
					case 0: $data = gettext('Auto');
						break;
					case 1: $data = gettext('Manual');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'Sharpness':
				switch ($data) {
					case 0: $data = gettext('Normal');
						break;
					case 1: $data = gettext('Soft');
						break;
					case 2: $data = gettext('Hard');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'Saturation':
			case 'Contrast':
				switch ($data) {
					case 0: $data = gettext('Normal');
						break;
					case 1: $data = gettext('Low');
						break;
					case 2: $data = gettext('High');
						break;
					default: $data = gettext('Unknown') . ': ' . $data;
						break;
				}
				break;
			case 'FocalLengthIn35mmFilm':
				$data = self::get35mmEquivFocalLength($exifdata) . ' mm';
				break;
			case 'GPSLatitudeRef':
			case 'GPSLongitudeRef':
			case 'GPSLatitude':
			case 'GPSLongitude':
			case 'GPSTimeStamp':
			case 'GPSAltitude':
			case 'GPSAltitudeRef':
				$data = self::formatGPS($tag, $data);
				break;
		}
		return $data;
	}

	/**
	 * Returns the image width from the COMPUTED array or if present for EXIFImageWidth
	 * @param array $exifdata The full Exif data as returnd by exif_read_data();
	 * @return int
	 */
	static function getImageWidth($exifdata) {
		if (isset($exifdata['COMPUTED']['Width'])) {
			return $exifdata['COMPUTED']['Width'];
		} else if (isset($exifdata['ExifImageWidth'])) {
			return $exifdata['ExifImageWidth'];
		}
		return 0;
	}

	/**
	 * Returns the image height from the COMPUTED array or if present for EXIFImageLength or EXIFImageHeight
	 * @param array $exifdata The full Exif data as returnd by exif_read_data();
	 * @param string $data Optionally set a value to skip internal
	 * @return int
	 */
	static function getImageHeight($exifdata) {
		if (isset($exifdata['COMPUTED']['Height'])) {
			return $exifdata['COMPUTED']['Height'];
		} else if (!isset($exifdata['EXIFImageLength'])) {
			return $exifdata['EXIFImageLength'];
		} else if (isset($exifdata['ExifImageHeight'])) {
			return $exifdata['ExifImageHeight'];
		}
		return 0;
	}

	/**
	 * Formats the exposure value.
	 * 
	 * @since 1.6.3 
	 * @since 1.6.3 Adapted from Exifer 1.7 to xmpMetaData plugin
	 * @since 1.6.5 Moved to class imageMeta
	 * 
	 * @author Jake Olefsky jake@olefsky.com, adapted by Malte Müller (acrylian)
	 * 
	 * @param type $data
	 * @return type
	 */
	static function formatExposure($data) {
		if (strpos($data, '/') === false) {
			$data = floatval($data);
			if ($data >= 1) {
				return round($data, 2) . ' sec';
			} else {
				$n = 0;
				$d = 0;
				self::ConvertToFraction($data, $n, $d);
				if ( $n == 0 ) {
					return;
				}
				return '1' . '/' . round($d / $n, 0) . ' sec';
			}
		} else {
			return gettext('Bulb');
		}
	}

	/**
	 * Calculates the 35mm-equivalent focal length from the reported sensor resolution
	 * 
	 * Used for output formatting.
	 * 
	 * @since 1.6.5 Adapted from Exifer 1.7
	 * 
	 * @author Tristan Harward (trisweb)
	 * 
	 * @param array $exifdata The full exif data array as returned by exif_read_data() as formatting depends on several fields
	 * @return int
	 */
	static function get35mmEquivFocalLength($exifdata) {
		if (is_numeric($exifdata['FocalLengthIn35mmFilm'])) {
			return intval($exifdata['FocalLengthIn35mmFilm']);
		}
		$width = self::getImageWidth($exifdata);
		$height = self::getImageHeight($exifdata);
		if (isset($exifdata['FocalPlaneResolutionUnit'])) {
			$units = $exifdata['FocalPlaneResolutionUnit'];
		} else {
			$units = '';
		}
		$unitfactor = 1;
		switch ($units) {
			case gettext('Inch') :
				$unitfactor = 25.4;
				break;
			case gettext('Centimeter') :
				$unitfactor = 10;
				break;
			case gettext('Millimeter') :
				$unitfactor = 1;
				break;
			case gettext('Micrometer') :
				$unitfactor = 0.001;
				break;
			case gettext('No Unit') :
				$unitfactor = 25.4;
				break;
			default :
				$unitfactor = 25.4;
		}
		if (isset($exifdata['FocalPlaneXResolution'])) {
			$xres = $exifdata['FocalPlaneXResolution'];
		} else {
			$xres = '';
		}
		if (isset($exifdata['FocalPlaneYResolution'])) {
			$yres = $exifdata['FocalPlaneYResolution'];
		} else {
			$yres = '';
		}
		if (isset($exifdata['FocalLength'])) {
			$fl = $exifdata['FocalLength'];
		} else {
			$fl = 0;
		}
		if (!empty($width) && !empty($height) && !empty($xres) && !empty($yres) && !empty($units) && !empty($fl)) {
			// Calculate CCD diagonal using Pythagoras' theorem (a² + b² = c²)
			$diagccd = sqrt(
							pow(((intval($width) * $unitfactor) / $xres), 2) + pow(((intval($height) * $unitfactor) / $yres), 2)
			);
			// Calculate 35mm diagonal using Pythagoras' theorem
			$diag35mm = sqrt(1872); // 36² + 24² = 1872
			$cropfactor = $diag35mm / ($diagccd ?: 1);
			// Workaround for locale-unaware floatval() that cannot deal with a comma as a decimal separator
			// (cp. https://stackoverflow.com/questions/7302834/)
			$decicomma = ((string) 3.1415)[1] === ',';
			$equivfl = floatval($decicomma ? str_replace(',', '.', $fl) : $fl) * $cropfactor;
			return $equivfl;
		}
		return null;
	}

	/**
	 * Formats GPS data
	 * 
	 * This is for storinng in the db because it's structure as returned cannot be stored properly 
	 * and also will conflict with geodata usages in maps and elsewhere
	 * 
	 * @since 1.6.5 Adapted from Exifer 1.7
	 * @author Jake Olefsky jake@olefsky.com, adapted by Malte Müller (acrylian)
	 * 
	 * @param string $tag Field name (not db column name!)
	 * @param mixed $data Field value
	 * @return string|integer
	 */
	static function formatGPS($tag, $data) {
		switch ($tag) {
			case 'GPSLatitudeRef':
			case 'GPSLongitudeRef':
				//$data = ($data[1] == @$data[2] && @$data[1] == @$data[3]) ? $data[0] : $data;
				if (is_array($data) && isset($data[1]) && isset($data[2]) && isset($data[3]) && ($data[1] == $data[2] && $data[1] == $data[3])) {
					$data = $data[0];
				} else {
					$data = $data;
				}
				break;
			case 'GPSLatitude':
			case 'GPSLongitude':
			case 'GPSTimeStamp':
				if (is_array($data)) {
					$hour = self::rationalNum($data[0]);
					$minutes = self::rationalNum($data[1]);
					$seconds = self::rationalNum($data[2]);
					if ($tag == 'GPSTimeStamp') { // We actually don't store this
						$data = $hour . ":" . $minutes . ":" . $seconds;
					} else {
						$data = $hour + $minutes / 60 + $seconds / 3600;
					}
				}
				break;
			case 'GPSAltitude':
				$data = self::rationalNum($data);
				if (is_numeric($data)) {
					$data = round(floatval($data), 1) . ' m';
				}
				break;
			case 'GPSAltitudeRef':
				if ($data == "00000000") {
					$data = '+';
				} else if ($data == "01000000") {
					$data = '-';
				}
				break;
		}
		return $data;
	}

	/**
	 * Converts a floating point number into a simple fraction.
	 * 
	 * @since 1.6.3 Adapted from Exifer library to xmpMetaData plugin
	 * @since 1.6.5 Moved to class imageMetaFormatter
	 * 
	 * @author Jake Olefsky jake@olefsky.com, adapted by Malte Müller (acrylian)
	 * 
	 * @param type $v
	 * @param type $n
	 * @param type $d
	 * @return type
	 */
	static function convertToFraction($v, &$n, &$d) {
		if ($v == 0) {
			$n = 0;
			$d = 1;
			return;
		}
		for ($n = 1; $n < 100; $n++) {
			$v1 = 1 / $v * $n;
			$d = round($v1, 0);
			if (abs($d - $v1) < 0.02) {
				return; // within tolerance
			}
		}
	}

	/**
	 * Convert a fractional representation to something more user friendly
	 * 
	 * @since 1.6.5 moved from xmpMetaData plugin to class imageMetaFormatter
	 * @author Stephen Billard (sbillard)
	 * @param string $data 
	 * @return string
	 */
	static function rationalNum($data) {
		// deal with the fractional representation
		$n = explode('/', $data);
		if ( $n[1] == 0 ) {
			return;
		}
		$v = sprintf('%f', $n[0] / $n[1]);
		for ($i = strlen($v) - 1; $i > 1; $i--) {
			if ($v[$i] != '0') {
				break;
			}
		}
		if ($v[$i] == '.') {
			$i--;
		}
		$value = str_replace(',','.', $v); // fix locale aware floats due to num to string conversion
		return substr($value, 0, $i + 1);
	}

}
