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
#	 * @since 1.4.6
#	 */
#	public static function next_album() {
#		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
#	}
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

?>
