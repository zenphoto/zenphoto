<?php

/**
 * Dummy routines so zenphoto will run on servers that do not have PHP getttext support.
 */
function bindtextdomain($domain, $path) {
	return $path;
}

function bind_textdomain_codeset($domain, $codeset) {
	return $codeset;
}

function textdomain($domain) {
	return $domain;
}

function gettext($msg) {
	return $msg;
}

function ngettext($msg1, $msg2, $n) {
	if ($n == 0 || $n > 1) {
		return $msg2;
	}
	return $msg1;
}

?>