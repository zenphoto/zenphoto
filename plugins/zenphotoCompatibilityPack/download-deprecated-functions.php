<?php

/**
 * @deprecated
 * @since 1.4.6
 */
function printDownloadLink($file, $linktext = NULL) {
	deprecated_functions::notify(gettext('use printDownloadURL()'));
	printDownloadURL($file, $linktext);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getDownloadLink($file) {
	deprecated_functions::notify(gettext('use getDownloadURL()'));
	return getDownloadURL($file, $linktext);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function printDownloadLinkAlbumZip($file, $linktext = NULL) {
	deprecated_functions::notify(gettext('use printDownloadAlbumZipURL()'));
	printDownloadURL($file, $linktext);
}

?>
