<?php
/**
 * read_exif_data_protected
 * @package functions
 *
 */
/**
 * Provides an [not] error protected read of image EXIF/IPTC data for PHP 4
 *
 * @param string $path image path
 * @return array
 */
function read_exif_data_protected($path) {
	if (DEBUG_EXIF) debugLog("Begin read_exif_data_protected($path)");
	$rslt = read_exif_data_raw($path, false);
	if (DEBUG_EXIF) debugLog("End read_exif_data_protected($path)");
	return $rslt;
}

function file_put_contents($file, $contents) {
	$f = fopen($file, 'w');
	if (!$f) return false;
	$r = fwrite($f, $contents);
	fclose($f);
	return $r;
}

function stripos($str,$needle,$offset=0) {
	return @strpos(strtolower($str),strtolower($needle),$offset);
}

?>