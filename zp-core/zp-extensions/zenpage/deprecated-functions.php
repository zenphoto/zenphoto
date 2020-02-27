<?php

/*
 * These are the Zenpage functions which have been deprecated
 */

class Zenpage_internal_deprecations {
	
	/**
	 * @deprecated ZenphotoCMS 2.0
	 * @since ZenphotoCMS 1.5.5
	 */
	public static function visibleCategory() {
		deprecated_functions::notify(gettext('Use the method isPublic() of the ZenpageCategory class instead.'));
	}
	
	/**
	 * @deprecated ZenphotoCMS 2.0 Use if($obj->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) { … } for a equivalent check instead.
	 * @since ZenphotoCMS 1.5.5
	 */
	public static function categoryIsVisible() {
		deprecated_functions::notify(gettext('Use <code>if($obj->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) { … }</code> for a equivalent check instead.'));
	}
	
	/**
	 * @deprecated ZenphotoCMS 2.0 Use Zenpage::processScheduledPublishing('expiredate') instead
	 */
	public static function expiry() {
		deprecated_functions::notify(gettext('Use Zenpage::processScheduledPublishing("expiredate") instead.'));
	}
	
}

/**
 * Wrapper function to get the author of a news article or page: Used by getNewsAuthor() and getPageAuthor().
 * 
 * @deprecated ZenphotoCMS 1.6 - Use getOwnerAuthor() instead
 * @since ZenphotoCMS 1.5.2
 *
 * @param bool $fullname False for the user name, true for the full name. If the author has no user account on this site the author name is returned
 * @return string
 */
function getAuthor($fullname = false) {
	deprecated_functions::notify(gettext('Use getOwnerAuthor() instead'));
	return getOwnerAuthor($fullname);
}

/**
 * Gets the author of a news article (if in Combinews mode for gallery items the owner)
 * 
 * @deprecated ZenphotoCMS 1.6 - Use getOwnerAuthor() instead
 * @since ZenphotoCMS 1.5.2
 * 
 * @return string
 */
function getNewsAuthor($fullname = false) {
	deprecated_functions::notify(gettext('Use getOwnerAuthor() instead'));
	return getOwnerAuthor($fullname);
}

/**
 * Prints the author of the current news article
 * 
 * @deprecated ZenphotoCMS 1.6 - Use printOwnerAuthor() instead
 * @since ZenphotoCMS 1.5.2
 */
function printNewsAuthor($fullname = false) {
	deprecated_functions::notify(gettext('Use printOwnerAuthor() instead'));
	echo html_encode(getOwnerAuthor($fullname));
}

/**
 * Returns the author of the current page
 * 
 * @deprecated ZenphotoCMS 1.6 - Use getOwnerAuthor() instead
 * @since ZenphotoCMS 1.5.2
 *
 * @param bool $fullname True if you want to get the full name if set, false if you want the login/screenname
 * @return string
 */
function getPageAuthor($fullname = false) {
	deprecated_functions::notify(gettext('Use getOwnerAuthor() instead'));
	return getOwnerAuthor($fullname);
}

/**
 * Prints the author of the current page
 * 
 * @deprecated ZenphotoCMS 1.6 – Use printOwnerAuthor() instead
 * @since ZenphotoCMS 1.5.2
 * 
 * @param bool $fullname True if you want to get the full name if set, false if you want the login/screenname
 * @return string
 */
function printPageAuthor($fullname = false) {
	deprecated_functions::notify(gettext('Use printOwnerAuthor() instead'));
	echo html_encode(getOwnerAuthor($fullname));
}