<?php

/**
 * General deprecated functions
 */
class internal_deprecations {
# all methods must be declared static
#
# example deprecated method
#	/**
#	 * @deprecated
#	 * @since 1.0.0
#	 */
#	static function PersistentObject() {
#		deprecated_functions::notify(gettext('Use the instantiate method instead'));
#	}
#
# example of method with deprecated parameters
#	/**
#	 * @deprecated
#	 * @since 1.0.0
#	 */
#	public static function next_album() {
#		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
#	}
	/**
	 * @deprecated
	 * @since 1.4.0
	 */

	static function getCustomData() {
		deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	}

	/**
	 * @deprecated
	 * @since 1.4.0
	 */
	static function setCustomData($val) {
		deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	}

}

# For other deprecated functions simply move them here.
#
#
#/**
# * @deprecated
# * @since 1.0.0
# */
#function printCustomSizedImageMaxHeight($maxheight) {
#	deprecated_functions::notify(gettext('Use printCustomSizedImageMaxSpace().'));
#	if (getFullWidth() === getFullHeight() OR getDefaultHeight() > $maxheight) {
#		printCustomSizedImage(getImageTitle(), null, null, $maxheight, null, null, null, null, null, null);
#	} else {
#		printDefaultSizedImage(getImageTitle());
#	}
#}

/**
 * @deprecated
 * @since 1.0.0
 */
function printHeadTitle($separator = ' | ', $listparentalbums = true, $listparentpages = true) {
	deprecated_functions::notify(gettext('This feature is handled in the "theme_head" filter. For parameters set the theme options.'));
}

/**
 * @deprecated
 * @since 1.0.1
 */
function getAllTagsCount($language = NULL) {
	deprecated_functions::notify(gettext('Use getAllTagsUnique()'));
	return getAllTagsUnique($language, 1, true);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getAlbumCustomData() {
	global $_zp_current_album;
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	return $_zp_current_album->getCustomData();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printAlbumCustomData() {
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	echo html_encodeTagged(getAlbumCustomData());
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getImageCustomData() {
	global $_zp_current_image;
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	return $_zp_current_image->getCustomData();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printImageCustomData() {
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	$data = getImageCustomData();
	$data = str_replace("\r\n", "\n", $data);
	$data = str_replace("\n", "<br />", $data);
	echo $data;
}

/**
 * @deprecated
 * @since 1.4.1
 */
function printSubtabs() {
	deprecated_functions::notify(gettext('Subtabs are no longer separate from tabs. If you need the current subtab use getCurrentTab() otherwise remove the call'));
	$current = getCurrentTab();
	return $current;
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getSubtabs() {
	deprecated_functions::notify(gettext('Subtabs are no longer separate from tabs. If you need the current subtab use getCurrentTab() otherwise remove the call'));
	$current = getCurrentTab();
	return $current;
}

?>
