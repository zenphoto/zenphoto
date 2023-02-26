<?php

/*
 * Wrappers used as a drop in replacement for multibye/icon functions in case not present on the system
 * Include this right after class-utf8.php obly if the multibyte amd iconv extension is not available
 * 
 * @package zpcore\libs
 */

function mb_strtolower($str) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::strtolower($str);
}

function mb_strtoupper($str) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::strtoupper($str);
}

function mb_strlen($str) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::strlen($str);
}

function mb_substr($str, $start, $length = NULL) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::substr($str, $start, $length);
}

function mb_strrpos($haystack, $needle) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::strrpos($haystack, $needle);
}

function mb_strpos($haystack, $needle, $offset = 0) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::strpos($haystack, $needle, $offset);
}

function mb_substr_count($haystack, $needle) {
	deprecationNotice('Install the native PHP extensions multibyte and icon on your server or ask your host about it');
	return utf8::substr_count($haystack, $needle);
}
