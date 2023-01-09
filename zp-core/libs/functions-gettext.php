<?php

/**
 * Dummy routines so zenphoto will run on servers that do not have PHP getttext support.
 * Wrappers used as a drop in replacement for the standard gettext functions
 * 
 * Only include if the native PHP extension gettext is not available!
 * 
 * @package zpcore\libs
 */
// force UTF-8 Ø
function bindtextdomain($domain, $path) {
	//return _bindtextdomain($domain, $path);
}

function bind_textdomain_codeset($domain, $codeset) {
	//return _bind_textdomain_codeset($domain, $codeset);
}

function textdomain($domain) {
	//return _textdomain($domain);
}

function gettext($msgid) {
	return $msgid;
}

function ngettext($msgid1, $msgid2, $n) {
	if ($n == 0 || $n > 1) {
		return $msgid2;
	}
	return $msgid1;
}

function dgettext($plugin, $string) {
	return $string;
}

function dngettext($theme, $msgid1, $msgid2, $n) {
	return ngettext($msgid1, $msgid2, $n);
}
