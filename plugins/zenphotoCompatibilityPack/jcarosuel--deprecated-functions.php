<?php

/**
 * @deprecated since version 1.4.6
 */
function printjCarouselThumbNav($thumbscroll = NULL, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $fullimagelink = NULL, $vertical = NULL) {
	deprecated_functions::notify(gettext('Use printThumbNav().'));
	printThumbNav(NULL, NULL, $width, $height, $cropw, $croph, $fullimagelink, $mode, NULL, $thumbscroll);
}

?>