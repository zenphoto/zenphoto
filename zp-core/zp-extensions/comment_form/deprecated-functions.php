<?php

/**
 * @deprecated
 * @since 1.4.6
 */
function getCommentsAllowed() {
	global $_zp_current_image, $_zp_current_album;
	deprecated_functions::notify(gettext("use the object’s getCommentsAllowed() method"));
	if (in_context(ZP_IMAGE)) {
		if (is_null($_zp_current_image))
			return false;
		return $_zp_current_image->getCommentsAllowed();
	} else {
		return $_zp_current_album->getCommentsAllowed();
	}
}
