<?php //================================================================================================
//================================================================================================
//================================================================================================
/*
	Exifer
	Extracts EXIF information from digital photos.

	Copyright � 2003 Jake Olefsky
	http://www.offsky.com/software/exif/index.php
	jake@olefsky.com

	Please see exif.php for the complete information about this software.

	------------

	This program is free software; you can redistribute it and/or modify it under the terms of
	the GNU General Public License as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details. http://www.gnu.org/copyleft/gpl.html
*/
//================================================================================================
//================================================================================================
//================================================================================================


//=================
// Looks up the name of the tag for the MakerNote (Depends on Manufacturer)
//====================================================================
function lookup_Fujifilm_tag($tag) {

	switch($tag) {
		case "0000": $tag = "Version";break;
		case "1000": $tag = "Quality";break;
		case "1001": $tag = "Sharpness";break;
		case "1002": $tag = "WhiteBalance";break;
		case "1003": $tag = "Color";break;
		case "1004": $tag = "Tone";break;
		case "1010": $tag = "FlashMode";break;
		case "1011": $tag = "FlashStrength";break;
		case "1020": $tag = "Macro";break;
		case "1021": $tag = "FocusMode";break;
		case "1030": $tag = "SlowSync";break;
		case "1031": $tag = "PictureMode";break;
		case "1100": $tag = "ContinuousTakingBracket";break;
		case "1200": $tag = "Unknown";break;
		case "1300": $tag = "BlurWarning";break;
		case "1301": $tag = "FocusWarning";break;
		case "1302": $tag = "AEWarning";break;

		default: $tag = "unknown:".$tag;break;
	}

	return $tag;
}

//=================
// Formats Data for the data type
//====================================================================
function formatFujifilmData($type,$tag,$intel,$data) {

	if($type=="ASCII") {


	} else if($type=="URATIONAL" || $type=="SRATIONAL") {
		$data = unRational($data,$type,$intel);

		if($tag=="1011") { //FlashStrength
			$data=$data." EV";
		}

	} else if($type=="USHORT" || $type=="SSHORT" || $type=="ULONG" || $type=="SLONG" || $type=="FLOAT" || $type=="DOUBLE") {
		$data =rational($data,$type,$intel);

		if($tag=="1001") { //Sharpness
			if($data == 1) $data = gettext("Soft");
			else if($data == 2) $data = gettext("Soft");
			else if($data == 3) $data = gettext("Normal");
			else if($data == 4) $data = gettext("Hard");
			else if($data == 5) $data = gettext("Hard");
			else $data = gettext("Unknown").": ".$data;
		}
		if($tag=="1002") { //WhiteBalance
			if($data == 0) $data = gettext("Auto");
			else if($data == 256) $data = gettext("Daylight");
			else if($data == 512) $data = gettext("Cloudy");
			else if($data == 768) $data = gettext("DaylightColor-fluorescence");
			else if($data == 769) $data = gettext("DaywhiteColor-fluorescence");
			else if($data == 770) $data = gettext("White-fluorescence");
			else if($data == 1024) $data = gettext("Incandescence");
			else if($data == 3840) $data = gettext("Custom");
			else $data = gettext("Unknown").": ".$data;
		}
		if($tag=="1003") { //Color
			if($data == 0) $data = gettext("Chroma Saturation Normal(STD)");
			else if($data == 256) $data = gettext("Chroma Saturation High");
			else if($data == 512) $data = gettext("Chroma Saturation Low(ORG)");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1004") { //Tone
			if($data == 0) $data = gettext("Contrast Normal(STD)");
			else if($data == 256) $data = gettext("Contrast High(HARD)");
			else if($data == 512) $data = gettext("Contrast Low(ORG)");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1010") { //FlashMode
			if($data == 0) $data = gettext("Auto");
			else if($data == 1) $data = gettext("On");
			else if($data == 2) $data = gettext("Off");
			else if($data == 3) $data = gettext("Red-Eye Reduction");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1020") { //Macro
			if($data == 0) $data = gettext("Off");
			else if($data == 1) $data = gettext("On");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1021") { //FocusMode
			if($data == 0) $data = gettext("Auto");
			else if($data == 1) $data = gettext("Manual");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1030") { //SlowSync
			if($data == 0) $data = gettext("Off");
			else if($data == 1) $data = gettext("On");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1031") { //PictureMode
			if($data == 0) $data = gettext("Auto");
			else if($data == 1) $data = gettext("Portrait");
			else if($data == 2) $data = gettext("Landscape");
			else if($data == 4) $data = gettext("Sports");
			else if($data == 5) $data = gettext("Night");
			else if($data == 6) $data = gettext("Program AE");
			else if($data == 256) $data = gettext("Aperture Priority AE");
			else if($data == 512) $data = gettext("Shutter Priority");
			else if($data == 768) $data = gettext("Manual Exposure");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1100") { //ContinuousTakingBracket
			if($data == 0) $data = gettext("Off");
			else if($data == 1) $data = gettext("On");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1300") { //BlurWarning
			if($data == 0) $data = gettext("No Warning");
			else if($data == 1) $data = gettext("Warning");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1301") { //FocusWarning
			if($data == 0) $data = gettext("Auto Focus Good");
			else if($data == 1) $data = gettext("Out of Focus");
			else $data = gettext("Unknown: ").$data;
		}
		if($tag=="1302") { //AEWarning
			if($data == 0) $data = gettext("AE Good");
			else if($data == 1) $data = gettext("Over Exposure");
			else $data = gettext("Unknown: ").$data;
		}
	} else if($type=="UNDEFINED") {



	} else {
		$data = bin2hex($data);
		if($intel==1) $data = intel2Moto($data);
	}

	return $data;
}



//=================
// Fujifilm Special data section
//====================================================================
function parseFujifilm($block,&$result) {

	//if($result['Endien']=="Intel") $intel=1;
	//else $intel=0;
	$intel=1;

	$model = $result['IFD0']['Model'];

	$place=8; //current place
	$offset=8;


	$num = bin2hex(substr($block,$place,4));$place+=4;
	if($intel==1) $num = intel2Moto($num);
	$result['SubIFD']['MakerNote']['Offset'] = hexdec($num);

		//Get number of tags (2 bytes)
	$num = bin2hex(substr($block,$place,2));$place+=2;
	if($intel==1) $num = intel2Moto($num);
	$result['SubIFD']['MakerNote']['MakerNoteNumTags'] = hexdec($num);

	//loop thru all tags  Each field is 12 bytes
	for($i=0;$i<hexdec($num);$i++) {

			//2 byte tag
		$tag = bin2hex(substr($block,$place,2));$place+=2;
		if($intel==1) $tag = intel2Moto($tag);
		$tag_name = lookup_Fujifilm_tag($tag);

			//2 byte type
		$type = bin2hex(substr($block,$place,2));$place+=2;
		if($intel==1) $type = intel2Moto($type);
		lookup_type($type,$size);

			//4 byte count of number of data units
		$count = bin2hex(substr($block,$place,4));$place+=4;
		if($intel==1) $count = intel2Moto($count);
		$bytesofdata = validSize($size*hexdec($count));

			//4 byte value of data or pointer to data
		$value = substr($block,$place,4);$place+=4;


		if($bytesofdata<=4) {
			$data = substr($value,0,$bytesofdata);
		} else {
			$value = bin2hex($value);
			if($intel==1) $value = intel2Moto($value);
			$data = substr($block,hexdec($value)-$offset,$bytesofdata*2);
		}
		$formated_data = formatFujifilmData($type,$tag,$intel,$data);

		if($result['VerboseOutput']==1) {
			$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
			if($type=="URATIONAL" || $type=="SRATIONAL" || $type=="USHORT" || $type=="SSHORT" || $type=="ULONG" || $type=="SLONG" || $type=="FLOAT" || $type=="DOUBLE") {
				$data = bin2hex($data);
				if($intel==1) $data = intel2Moto($data);
			}
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['RawData'] = $data;
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['Type'] = $type;
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['Bytes'] = $bytesofdata;
		} else {
			$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
		}
	}
}


?>