<?php

/**
 * Nikon Exifer
 *
 * Extracts EXIF information from digital photos.
 *
 * Copyright © 2003 Jake Olefsky
 * http://www.offsky.com/software/exif/index.php
 * jake@olefsky.com
 *
 * Please see exif.php for the complete information about this software.

 * This program is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Looks up the name of the tag for the MakerNote (Depends on Manufacturer)
 *
 * @param type $tag
 * @param type $model
 * @return string
 */
function lookup_Nikon_tag($tag, $model) {

	if ($model == 0) {
		switch ($tag) {
			case "0003": $tag = "Quality";
				break;
			case "0004": $tag = "ColorMode";
				break;
			case "0005": $tag = "ImageAdjustment";
				break;
			case "0006": $tag = "CCDSensitivity";
				break;
			case "0007": $tag = "WhiteBalance";
				break;
			case "0008": $tag = "Focus";
				break;
			case "0009": $tag = "Unknown2";
				break;
			case "000a": $tag = "DigitalZoom";
				break;
			case "000b": $tag = "Converter";
				break;

			default: $tag = "unknown:" . $tag;
				break;
		}
	} else if ($model == 1) {
		switch ($tag) {
			case "0002": $tag = "ISOSetting";
				break;
			case "0003": $tag = "ColorMode";
				break;
			case "0004": $tag = "Quality";
				break;
			case "0005": $tag = "Whitebalance";
				break;
			case "0006": $tag = "ImageSharpening";
				break;
			case "0007": $tag = "FocusMode";
				break;
			case "0008": $tag = "FlashSetting";
				break;
			case "0009": $tag = "FlashMode";
				break;
			case "000b": $tag = "WhiteBalanceFine";
				break;
			case "000c": $tag = "WB_RBLevels";
				break;
			case "000d": $tag = "ProgramShift";
				break;
			case "000e": $tag = "ExposureDifference";
				break;
			case "000f": $tag = "ISOSelection";
				break;
			case "0010": $tag = "DataDump";
				break;
			case "0011": $tag = "NikonPreview";
				break;
			case "0012": $tag = "FlashExposureComp";
				break;
			case "0013": $tag = "ISOSetting2";
				break;
			case "0014": $tag = "ColorBalanceA";
				break;
			case "0016": $tag = "ImageBoundary";
				break;
			case "0017": $tag = "FlashExposureComp";
				break;
			case "0018": $tag = "FlashExposureBracketValue";
				break;
			case "0019": $tag = "ExposureBracketValue";
				break;
			case "001a": $tag = "ImageProcessing";
				break;
			case "001b": $tag = "CropHiSpeed";
				break;
			case "001c": $tag = "ExposureTuning";
				break;
			case "001d": $tag = "SerialNumber";
				break;
			case "001e": $tag = "ColorSpace";
				break;
			case "001f": $tag = "VRInfo";
				break;
			case "0020": $tag = "ImageAuthentication";
				break;
			case "0022": $tag = "ActiveD-Lighting";
				break;
			case "0023": $tag = "PictureControl";
				break;
			case "0024": $tag = "WorldTime";
				break;
			case "0025": $tag = "ISOInfo";
				break;
			case "002a": $tag = "VignetteControl";
				break;
			case "002b": $tag = "DistortInfo";
				break;
			case "0080": $tag = "ImageAdjustment";
				break;
			case "0081": $tag = "ToneCompensation";
				break;
			case "0082": $tag = "Adapter";
				break;
			case "0083": $tag = "LensType";
				break;
			case "0084": $tag = "LensInfo";
				break;
			case "0085": $tag = "ManualFocusDistance";
				break;
			case "0086": $tag = "DigitalZoom";
				break;
			case "0087": $tag = "FlashUsed";
				break;
			case "0088": $tag = "AFFocusPosition";
				break;
			case "0089": $tag = "ShootingMode";
				break;
			case "008b": $tag = "LensFStops";
				break;
			case "008c": $tag = "ContrastCurve";
				break;
			case "008d": $tag = "ColorMode";
				break;
			case "0090": $tag = "LightType";
				break;
			case "0092": $tag = "HueAdjustment";
				break;
			case "0093": $tag = "NEFCompression";
				break;
			case "0094": $tag = "Saturation";
				break;
			case "0095": $tag = "NoiseReduction";
				break;
			case "009a": $tag = "SensorPixelSize";
				break;

			default: $tag = "unknown:" . $tag;
				break;
		}
	}

	return $tag;
}

//=================
// Formats Data for the data type
//====================================================================
function formatNikonData($type, $tag, $intel, $model, $data) {
	switch ($type) {
		case "ASCII":
			break; // do nothing!
		case "URATIONAL":
		case"SRATIONAL":
			switch ($tag) {
				case '0084': // LensInfo
					$minFL = unRational(substr($data, 0, 8), $type, $intel);
					$maxFL = unRational(substr($data, 8, 8), $type, $intel);
					$minSP = unRational(substr($data, 16, 8), $type, $intel);
					$maxSP = unRational(substr($data, 24, 8), $type, $intel);
					if ($minFL == $maxFL) {
						$data = sprintf('%0.0fmm f/%0.1f', $minFL, $minSP);
					} elseif ($minSP == $maxSP) {
						$data = sprintf('%0.0f-%0.0fmm f/%0.1f', $minFL, $maxFL, $minSP);
					} else {
						$data = sprintf('%0.0f-%0.0fmm f/%0.1f-%0.1f', $minFL, $maxFL, $minSP, $maxSP);
					}
					break;
				case "0085":
					if ($model == 1)
						$data = unRational($data, $type, $intel) . " m"; //ManualFocusDistance
					break;
				case "0086":
					if ($model == 1)
						$data = unRational($data, $type, $intel) . "x"; //DigitalZoom
					break;
				case "000a":
					if ($model == 0)
						$data = unRational($data, $type, $intel) . "x"; //DigitalZoom
					break;
				default:
					$data = unRational($data, $type, $intel);
					break;
			}
			break;
		case "USHORT":
		case $type == "SSHORT":
		case $type == "ULONG":
		case $type == "SLONG":
		case $type == "FLOAT":
		case $type == "DOUBLE":
			$data = rational($data, $type, $intel);
			switch ($tag) {
				case "0003":
					if ($model == 0) { //Quality
						switch ($data) {
							case 1: $data = '!vga basic!';
								break;
							case 2: $data = '!vga normal!';
								break;
							case 3: $data = '!vga fine!';
								break;
							case 4: $data = '!sxga basic!';
								break;
							case 5: $data = '!sxga normal!';
								break;
							case 6: $data = '!sxga fine!';
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "0004":
					if ($model == 0) { //Color
						switch ($data) {
							case 1: $data = '!color!';
								break;
							case 2: $data = '!monochrome!';
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "0005":
					if ($model == 0) { //Image Adjustment
						switch ($data) {
							case 0: $data = '!normal!';
								break;
							case 1: $data = '!bright+!';
								break;
							case 2: $data = '!bright-!';
								break;
							case 3: $data = '!contrast+!';
								break;
							case 4: $data = '!contrast-!';
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "0006":
					if ($model == 0) { //CCD Sensitivity
						switch ($data) {
							case 0: $data = "ISO-80";
								break;
							case 2: $data = "ISO-160";
								break;
							case 4: $data = "ISO-320";
								break;
							case 5: $data = "ISO-100";
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "0007":
					if ($model == 0) { //White Balance
						switch ($data) {
							case 0: $data = '!auto!';
								break;
							case 1: $data = '!preset!';
								break;
							case 2: $data = '!daylight!';
								break;
							case 3: $data = '!incandescence!';
								break;
							case 4: $data = '!fluorescence!';
								break;
							case 5: $data = '!cloudy!';
								break;
							case 6: $data = '!speedlight!';
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "000b":
					if ($model == 0) { //Converter
						switch ($data) {
							case 0: $data = '!none!';
								break;
							case 1: $data = '!fisheye!';
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
			}
			break;
		case "UNDEFINED":
			switch ($tag) {
				case "0001":
					if ($model == 1)
						$data = $data / 100;
					break; //Unknown (Version?)
					break;
				case "0088":
					if ($model == 1) { //AF Focus Position
						$temp = '!center!';
						$data = bin2hex($data);
						$data = str_replace("01", "Top", $data);
						$data = str_replace("02", "Bottom", $data);
						$data = str_replace("03", "Left", $data);
						$data = str_replace("04", "Right", $data);
						$data = str_replace("00", "", $data);
						if (strlen($data) == 0)
							$data = $temp;
					}
					break;
			}
			break;
		default:
			$data = bin2hex($data);
			if ($intel == 1)
				$data = intel2Moto($data);
			switch ($tag) {
				case "0083":
					if ($model == 1) { //Lens Type
						$data = hexdec(substr($data, 0, 2));
						switch ($data) {
							case 0: $data = '!af non d!';
								break;
							case 1: $data = '!manual!';
								break;
							case 2: $data = "AF-D or AF-S";
								break;
							case 6: $data = "AF-D G";
								break;
							case 10: $data = "AF-D VR";
								break;
							case 14: $data = "AF-D G VR";
								break;
							default: $data = '!unknown!' . ": " . $data;
								break;
						}
					}
					break;
				case "0087":
					if ($model == 1) { //Flash type
						$data = hexdec(substr($data, 0, 2));
						if ($data == 0)
							$data = '!did not fire!';
						else if ($data == 4)
							$data = '!unknown!';
						else if ($data == 7)
							$data = '!external!';
						else if ($data == 9)
							$data = '!on camera!';
						else
							$data = '!unknown!' . ": " . $data;
					}
					break;
			}
			break;
	}
	return $data;
}

/**
 * Nikon Special data section
 *
 * @param type $block
 * @param type $result
 */
function parseNikon($block, &$result) {
	if ($result['Endien'] == "Intel")
		$intel = 1;
	else
		$intel = 0;

	$model = $result['IFD0']['Model'];

	//these 6 models start with "Nikon".  Other models dont.
	if ($model == "E700\0" || $model == "E800\0" || $model == "E900\0" || $model == "E900S\0" || $model == "E910\0" || $model == "E950\0") {
		$place = 8; //current place
		$model = 0;

		//Get number of tags (2 bytes)
		$num = bin2hex(substr($block, $place, 2));
		$place+=2;
		if ($intel == 1)
			$num = intel2Moto($num);
		$result['SubIFD']['MakerNote']['MakerNoteNumTags'] = hexdec($num);

		//loop thru all tags  Each field is 12 bytes
		for ($i = 0; $i < hexdec($num); $i++) {
			//2 byte tag
			$tag = bin2hex(substr($block, $place, 2));
			$place+=2;
			if ($intel == 1)
				$tag = intel2Moto($tag);
			$tag_name = lookup_Nikon_tag($tag, $model);

			//2 byte type
			$type = bin2hex(substr($block, $place, 2));
			$place+=2;
			if ($intel == 1)
				$type = intel2Moto($type);
			lookup_type($type, $size);

			//4 byte count of number of data units
			$count = bin2hex(substr($block, $place, 4));
			$place+=4;
			if ($intel == 1)
				$count = intel2Moto($count);
			$bytesofdata = validSize($size * hexdec($count));

			//4 byte value of data or pointer to data
			$value = substr($block, $place, 4);
			$place+=4;

			//if tag is 0002 then its the ASCII value which we know is at 140 so calc offset
			//THIS HACK ONLY WORKS WITH EARLY NIKON MODELS
			if ($tag == "0002")
				$offset = hexdec($value) - 140;
			if ($bytesofdata <= 4) {
				$data = substr($value, 0, $bytesofdata);
			} else {
				$value = bin2hex($value);
				if ($intel == 1)
					$value = intel2Moto($value);
				$data = substr($block, hexdec($value) - $offset, $bytesofdata * 2);
			}
			$formated_data = formatNikonData($type, $tag, $intel, $model, $data);

			if ($result['VerboseOutput'] == 1) {
				$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['RawData'] = $data;
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['Type'] = $type;
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['Bytes'] = $bytesofdata;
			} else {
				$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
			}
		}
	} else {
		$place = 0; //current place
		$model = 1;

		$nikon = substr($block, $place, 8);
		$place+=8;
		$endien = substr($block, $place, 4);
		$place+=4;

		//2 bytes of 0x002a
		$tag = bin2hex(substr($block, $place, 2));
		$place+=2;

		//Then 4 bytes of offset to IFD0 (usually 8 which includes all 8 bytes of TIFF header)
		$offset = bin2hex(substr($block, $place, 4));
		$place+=4;
		if ($intel == 1)
			$offset = intel2Moto($offset);
		if (hexdec($offset) > 8)
			$place+=$offset - 8;

		//Get number of tags (2 bytes)
		$num = bin2hex(substr($block, $place, 2));
		$place+=2;
		if ($intel == 1)
			$num = intel2Moto($num);

		//loop thru all tags  Each field is 12 bytes
		for ($i = 0; $i < hexdec($num); $i++) {
			//2 byte tag
			$tag = bin2hex(substr($block, $place, 2));
			$place+=2;
			if ($intel == 1)
				$tag = intel2Moto($tag);
			$tag_name = lookup_Nikon_tag($tag, $model);

			//2 byte type
			$type = bin2hex(substr($block, $place, 2));
			$place+=2;
			if ($intel == 1)
				$type = intel2Moto($type);
			lookup_type($type, $size);

			//4 byte count of number of data units
			$count = bin2hex(substr($block, $place, 4));
			$place+=4;
			if ($intel == 1)
				$count = intel2Moto($count);
			$bytesofdata = validSize($size * hexdec($count));

			//4 byte value of data or pointer to data
			$value = substr($block, $place, 4);
			$place+=4;

			if ($bytesofdata <= 4) {
				$data = substr($value, 0, $bytesofdata);
			} else {
				$value = bin2hex($value);
				if ($intel == 1)
					$value = intel2Moto($value);
				$data = substr($block, hexdec($value) + hexdec($offset) + 2, $bytesofdata);
			}
			$formated_data = formatNikonData($type, $tag, $intel, $model, $data);

			if ($result['VerboseOutput'] == 1) {
				$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
				if ($type == "URATIONAL" || $type == "SRATIONAL" || $type == "USHORT" || $type == "SSHORT" || $type == "ULONG" || $type == "SLONG" || $type == "FLOAT" || $type == "DOUBLE") {
					$data = bin2hex($data);
					if ($intel == 1)
						$data = intel2Moto($data);
				}
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['RawData'] = $data;
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['Type'] = $type;
				$result['SubIFD']['MakerNote'][$tag_name . "_Verbose"]['Bytes'] = $bytesofdata;
			} else {
				$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
			}
		}
	}
}

?>