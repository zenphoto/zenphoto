<?php
/**
 * Dummy routines so zenphoto will run on servers that do not have PHP getttext support.
 * This needs to be replaced by a gettext library that actually works.
 */

// Wrappers used as a drop in replacement for the standard gettext functions
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
function ngettext($msgid1,$msgid2,$n) {
	if ($n==0 || $n>1) {
		return $msgid2;
	}
	return $msgid1;
}

?>