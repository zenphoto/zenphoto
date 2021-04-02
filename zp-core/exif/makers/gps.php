<?php

/**
 * Exifer
 * Extracts EXIF information from digital photos.
 *
 * Copyright © 2003 Jake Olefsky
 * http://www.offsky.com/software/exif/index.php
 * jake@olefsky.com
 *
 * Please see exif.php for the complete information about this software.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Looks up the name of the tag
 *
 * @param type $tag
 * @return string
 */
function lookup_GPS_tag($tag) {
	switch ($tag) {
		case "0000":
			$tag = "Version";
			break;
		case "0001":
			$tag = "Latitude Reference"; //north or south
			break;
		case "0002":
			$tag = "Latitude"; //dd mm.mm or dd mm ss
			break;
		case "0003":
			$tag = "Longitude Reference"; //east or west
			break;
		case "0004":
			$tag = "Longitude"; //dd mm.mm or dd mm ss
			break;
		case "0005":
			$tag = "Altitude Reference"; //sea level or below sea level
			break;
		case "0006":
			$tag = "Altitude"; //positive rational number
			break;
		case "0007":
			$tag = "Time"; //three positive rational numbers
			break;
		case "0008":
			$tag = "Satellite"; //text string up to 999 bytes long
			break;
		case "0009":
			$tag = "ReceiveStatus"; //in progress or interop
			break;
		case "000a":
			$tag = "MeasurementMode"; //2D or 3D
			break;
		case "000b":
			$tag = "MeasurementPrecision"; //positive rational number
			break;
		case "000c":
			$tag = "SpeedUnit"; //KPH, MPH, knots
			break;
		case "000d":
			$tag = "ReceiverSpeed"; //positive rational number
			break;
		case "000e":
			$tag = "MovementDirectionRef"; //true or magnetic north
			break;
		case "000f":
			$tag = "MovementDirection"; //positive rational number
			break;
		case "0010":
			$tag = "ImageDirectionRef"; //true or magnetic north
			break;
		case "0011":
			$tag = "ImageDirection"; //positive rational number
			break;
		case "0012":
			$tag = "GeodeticSurveyData"; //text string up to 999 bytes long
			break;
		case "0013":
			$tag = "DestLatitudeRef"; //north or south
			break;
		case "0014":
			$tag = "DestinationLatitude"; //three positive rational numbers
			break;
		case "0015":
			$tag = "DestLongitudeRef"; //east or west
			break;
		case "0016":
			$tag = "DestinationLongitude"; //three positive rational numbers
			break;
		case "0017":
			$tag = "DestBearingRef"; //true or magnetic north
			break;
		case "0018":
			$tag = "DestinationBearing"; //positive rational number
			break;
		case "0019":
			$tag = "DestDistanceRef"; //km, miles, knots
			break;
		case "001a":
			$tag = "DestinationDistance"; //positive rational number
			break;
		case "001b":
			$tag = "ProcessingMethod";
			break;
		case "001c":
			$tag = "AreaInformation";
			break;
		case "001d":
			$tag = "Datestamp"; //text string 10 bytes long
			break;
		case "001e":
			$tag = "DifferentialCorrection"; //integer in range 0-65535
			break;
		default:
			$tag = "unknown:" . $tag;
			break;
	}
	return $tag;
}

/**
 * Formats Data for the data type
 *
 * @param type $type
 * @param type $tag
 * @param type $intel
 * @param type $data
 * @return type
 */
function formatGPSData($type, $tag, $intel, $data) {
	if ($type == "ASCII") {
		if ($tag == "0001" || $tag == "0003") { // Latitude Reference, Longitude Reference
			//$data = ($data[1] == @$data[2] && @$data[1] == @$data[3]) ? $data[0] : $data;
			if (isset($data[1]) && isset($data[2]) && isset($data[3]) && ($data[1] == $data[2] && $data[1] == $data[3])) {
				$data = $data[0];
			} else {
				$data = $data;
			}
		}
	} else if ($type == "URATIONAL" || $type == "SRATIONAL") {
		if ($tag == "0002" || $tag == "0004" || $tag == '0007') { //Latitude, Longitude, Time
			$datum = array();
			for ($i = 0; $i < strlen($data); $i = $i + 8) {
				array_push($datum, substr($data, $i, 8));
			}
			$hour = unRational($datum[0], $type, $intel);
			$minutes = unRational($datum[1], $type, $intel);
			$seconds = unRational($datum[2], $type, $intel);
			if ($tag == "0007") { //Time
				$data = $hour . ":" . $minutes . ":" . $seconds;
			} else {
				$data = $hour + $minutes / 60 + $seconds / 3600;
			}
		} else {
			$data = unRational($data, $type, $intel);

			if ($tag == "0006") {
				$data .= 'm';
			}
		}
	} else if ($type == "USHORT" || $type == "SSHORT" || $type == "ULONG" || $type == "SLONG" || $type == "FLOAT" || $type == "DOUBLE") {
		$data = rational($data, $type, $intel);
	} else if ($type == "UNDEFINED") {
		
	} else if ($type == "UBYTE") {
		$data = bin2hex($data);
		if ($intel == 1) {
			$num = intel2Moto($data);
		}

		if ($tag == "0000") { // VersionID
			$data = hexdec(substr($data, 0, 2)) .
							"." . hexdec(substr($data, 2, 2)) .
							"." . hexdec(substr($data, 4, 2)) .
							"." . hexdec(substr($data, 6, 2));
		} else if ($tag == "0005") { // Altitude Reference
			if ($data == "00000000") {
				$data = '+';
			} else if ($data == "01000000") {
				$data = '-';
			}
		}
	} else {
		$data = bin2hex($data);
		if ($intel == 1) {
			$data = intel2Moto($data);
		}
	}

	return $data;
}

/**
 * GPS Special data section
 *
 * Useful websites
 *
 * -http://drewnoakes.com/code/exif/sampleOutput.html
 * - http://www.geosnapper.com
 * @param type $block
 * @param type $result
 * @param type $offset
 * @param type $seek
 * @param type $globalOffset
 * @return type
 */
function parseGPS($block, &$result, $offset, $seek, $globalOffset) {

	if ($result['Endien'] == "Intel") {
		$intel = 1;
	} else {
		$intel = 0;
	}
	$v = fseek($seek, $globalOffset + $offset); //offsets are from TIFF header which is 12 bytes from the start of the file
	if ($v == -1) {
		$result['Errors'] = $result['Errors']++;
	}

	$num = bin2hex(fread($seek, 2));
	if ($intel == 1) {
		$num = intel2Moto($num);
	}
	$num = hexdec($num);
	$result['GPS']['NumTags'] = $num;

	if ($num == 0) {
		return;
	}

	$block = fread($seek, $num * 12);
	$place = 0;

	//loop thru all tags  Each field is 12 bytes
	for ($i = 0; $i < $num; $i++) {
		//2 byte tag
		$tag = bin2hex(substr($block, $place, 2));
		$place += 2;
		if ($intel == 1) {
			$tag = intel2Moto($tag);
		}
		$tag_name = lookup_GPS_tag($tag);

		//2 byte datatype
		$type = bin2hex(substr($block, $place, 2));
		$place += 2;
		if ($intel == 1) {
			$type = intel2Moto($type);
		}
		lookup_type($type, $size);

		//4 byte number of elements
		$count = bin2hex(substr($block, $place, 4));
		$place += 4;
		if ($intel == 1) {
			$count = intel2Moto($count);
		}
		$bytesofdata = validSize($size * hexdec($count));

		//4 byte value or pointer to value if larger than 4 bytes
		$value = substr($block, $place, 4);
		$place += 4;
		if ($bytesofdata <= 4) {
			$data = substr($value, 0, $bytesofdata);
		} else {
			if (strpos('unknown', $tag_name) !== false || $bytesofdata > 1024) {
				$result['Errors'] = $result['Errors']++;
				$data = '';
				$type = 'ASCII';
			} else {
				$value = bin2hex($value);
				if ($intel == 1) {
					$value = intel2Moto($value);
				}
				$v = fseek($seek, $globalOffset + hexdec($value)); //offsets are from TIFF header which is 12 bytes from the start of the file
				if ($v == 0) {
					$data = fread($seek, $bytesofdata);
				} else {
					$result['Errors'] = $result['Errors']++;
					$data = '';
					$type = 'ASCII';
				}
			}
		}
		if ($result['VerboseOutput'] == 1) {
			$result['GPS'][$tag_name] = formatGPSData($type, $tag, $intel, $data);
			$result['GPS'][$tag_name . "_Verbose"]['RawData'] = bin2hex($data);
			$result['GPS'][$tag_name . "_Verbose"]['Type'] = $type;
			$result['GPS'][$tag_name . "_Verbose"]['Bytes'] = $bytesofdata;
		} else {
			$result['GPS'][$tag_name] = formatGPSData($type, $tag, $intel, $data);
		}
	}
}
