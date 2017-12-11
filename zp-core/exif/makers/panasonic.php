<?php

/**
 * Panasonic Exifer
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
 * @return string
 */
function lookup_Panasonic_tag($tag) {

	switch ($tag) {
		case "0001": $tag = "Quality";
			break;
		case "0002": $tag = "FirmwareVersion";
			break;
		case "0003": $tag = "WhiteBalance";
			break;
		case "0007": $tag = "FocusMode";
			break;
		case "000f": $tag = "AFMode";
			break;
		case "001a": $tag = "ImageStabilizer";
			break;
		case "001c": $tag = "MacroMode";
			break;
		case "001f": $tag = "ShootingMode";
			break;
		case "0020": $tag = "Audio";
			break;
		case "0021": $tag = "DataDump";
			break;
		case "0023": $tag = "WhiteBalanceBias";
			break;
		case "0024": $tag = "FlashBias";
			break;
		case "0025": $tag = "SerialNumber";
			break;
		case "0028": $tag = "ColourEffect";
			break;
		case "002a": $tag = "BurstMode";
			break;
		case "002b": $tag = "SequenceNumber";
			break;
		case "002c": $tag = "Contrast";
			break;
		case "002d": $tag = "NoiseReduction";
			break;
		case "002e": $tag = "SelfTimer";
			break;
		case "0030": $tag = "Rotation";
			break;
		case "0032": $tag = "ColorMode";
			break;
		case "0036": $tag = "TravelDay";
			break;

		default: $tag = 'Unknown:' . $tag;
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
function formatPanasonicData($type, $tag, $intel, $data) {

	if ($type == "ASCII") {

	} else if ($type == "UBYTE" || $type == "SBYTE") {
		$data = bin2hex($data);
		if ($intel == 1)
			$data = intel2Moto($data);
		$data = hexdec($data);

		if ($tag == "000f") { //AFMode
			if ($data == 256)
				$data = "9-area-focusing";
			else if ($data == 16)
				$data = "1-area-focusing";
			else if ($data == 4096)
				$data = '!3-area-focusing (high speed)!';
			else if ($data == 4112)
				$data = '!1-area-focusing (high speed)!';
			else if ($data == 16)
				$data = '!1-area-focusing!';
			else if ($data == 1)
				$data = '!spot-focusing!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
	} else if ($type == "URATIONAL" || $type == "SRATIONAL") {
		$data = unRational($data, $type, $intel);
	} else if ($type == "USHORT" || $type == "SSHORT" || $type == "ULONG" || $type == "SLONG" || $type == "FLOAT" || $type == "DOUBLE") {
		$data = rational($data, $type, $intel);

		if ($tag == "0001") { //Image Quality
			if ($data == 2)
				$data = '!high!';
			else if ($data == 3)
				$data = '!standard!';
			else if ($data == 6)
				$data = '!very high!';
			else if ($data == 7)
				$data = '!raw!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0003") { //White Balance
			if ($data == 1)
				$data = '!auto!';
			else if ($data == 2)
				$data = '!daylight!';
			else if ($data == 3)
				$data = '!cloudy!';
			else if ($data == 4)
				$data = '!halogen!';
			else if ($data == 5)
				$data = '!manual!';
			else if ($data == 8)
				$data = '!flash!';
			else if ($data == 10)
				$data = '!black and white!';
			else if ($data == 11)
				$data = '!manual!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0007") { //Focus Mode
			if ($data == 1)
				$data = '!auto!';
			else if ($data == 2)
				$data = '!manual!';
			else if ($data == 4)
				$data = '!auto, focus button!';
			else if ($data == 5)
				$data = '!auto, continuous!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "001a") { //Image Stabilizer
			if ($data == 2)
				$data = '!mode 1!';
			else if ($data == 3)
				$data = '!off!';
			else if ($data == 4)
				$data = '!mode 2!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "001c") { //Macro mode
			if ($data == 1)
				$data = '!on!';
			else if ($data == 2)
				$data = '!off!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "001f") { //Shooting Mode
			if ($data == 1)
				$data = '!normal!';
			else if ($data == 2)
				$data = '!portrait!';
			else if ($data == 3)
				$data = '!scenery!';
			else if ($data == 4)
				$data = '!sports!';
			else if ($data == 5)
				$data = '!night portrait!';
			else if ($data == 6)
				$data = '!program!';
			else if ($data == 7)
				$data = '!aperture priority!';
			else if ($data == 8)
				$data = '!shutter priority!';
			else if ($data == 9)
				$data = '!macro!';
			else if ($data == 11)
				$data = '!manual!';
			else if ($data == 13)
				$data = '!panning!';
			else if ($data == 14)
				$data = '!simple!';
			else if ($data == 18)
				$data = '!fireworks!';
			else if ($data == 19)
				$data = '!party!';
			else if ($data == 20)
				$data = '!snow!';
			else if ($data == 21)
				$data = '!night scenery!';
			else if ($data == 22)
				$data = '!food!';
			else if ($data == 23)
				$data = '!baby!';
			else if ($data == 27)
				$data = '!high sensitivity!';
			else if ($data == 29)
				$data = '!underwater!';
			else if ($data == 33)
				$data = '!pet!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0020") { //Audio
			if ($data == 1)
				$data = '!yes!';
			else if ($data == 2)
				$data = '!no!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0023") { //White Balance Bias
			$data = $data . " EV";
		}
		if ($tag == "0024") { //Flash Bias
			$data = $data;
		}
		if ($tag == "0028") { //Colour Effect
			if ($data == 1)
				$data = '!off!';
			else if ($data == 2)
				$data = '!warm!';
			else if ($data == 3)
				$data = '!cool!';
			else if ($data == 4)
				$data = '!black and white!';
			else if ($data == 5)
				$data = '!sepia!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "002a") { //Burst Mode
			if ($data == 0)
				$data = '!off!';
			else if ($data == 1)
				$data = '!low/high quality!';
			else if ($data == 2)
				$data = '!infinite!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "002c") { //Contrast
			if ($data == 0)
				$data = '!standard!';
			else if ($data == 1)
				$data = '!low!';
			else if ($data == 2)
				$data = '!high!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "002d") { //Noise Reduction
			if ($data == 0)
				$data = '!standard!';
			else if ($data == 1)
				$data = '!low!';
			else if ($data == 2)
				$data = '!high!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "002e") { //Self Timer
			if ($data == 1)
				$data = '!off!';
			else if ($data == 2)
				$data = '10 ' . '!sec!';
			else if ($data == 3)
				$data = '2 ' . '!sec!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0030") { //Rotation
			if ($data == 1)
				$data = '!horizontal (normal)!';
			else if ($data == 6)
				$data = '!rotate 90 cw!';
			else if ($data == 8)
				$data = '!rotate 270 cw!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0032") { //Color Mode
			if ($data == 0)
				$data = '!normal!';
			else if ($data == 1)
				$data = '!natural!';
			else
				$data = '!unknown!' . " (" . $data . ")";
		}
		if ($tag == "0036") { //Travel Day
			$data = $data;
		}
	} else if ($type == "UNDEFINED") {

	} else {
		$data = bin2hex($data);
		if ($intel == 1)
			$data = intel2Moto($data);
	}

	return $data;
}

/**
 * Panasonic Special data section
 *
 * @param type $block
 * @param type $result
 */
function parsePanasonic($block, &$result) {

	//if($result['Endien']=="Intel") $intel=1;
	//else $intel=0;
	$intel = 1;

	$model = $result['IFD0']['Model'];

	$place = 8; //current place
	$offset = 8;


	$num = bin2hex(substr($block, $place, 4));
	$place+=4;
	if ($intel == 1)
		$num = intel2Moto($num);
	$result['SubIFD']['MakerNote']['Offset'] = hexdec($num);

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
		$tag_name = lookup_Panasonic_tag($tag);

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
			$data = substr($block, hexdec($value) - $offset, $bytesofdata * 2);
		}
		$formated_data = formatPanasonicData($type, $tag, $intel, $data);

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

?>