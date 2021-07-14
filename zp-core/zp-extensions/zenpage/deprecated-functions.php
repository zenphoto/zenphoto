<?php

/*
 * These are the Zenpage functions which have been deprecated
 */

class Zenpage_internal_deprecations {
	
	/**
	 * @deprecated Zenphoto 2.0
	 * @since Zenphoto  1.5.5
	 */
	public static function visibleCategory() {
		deprecated_functions::notify(gettext('Use the method isPublic() of the ZenpageCategory class instead.'));
	}
	
	/**
	 * @deprecated Zenphoto 2.0 Use if($obj->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) { … } for a equivalent check instead.
	 * @since Zenphoto  1.5.5
	 */
	public static function categoryIsVisible() {
		deprecated_functions::notify(gettext('Use <code>if($obj->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) { … }</code> for a equivalent check instead.'));
	}
	
	/**
	 * @deprecated Zenphoto 2.0 Use Zenpage::processScheduledPublishing('expiredate') instead
	 */
	public static function expiry() {
		deprecated_functions::notify(gettext('Use Zenpage::processScheduledPublishing("expiredate") instead.'));
	}
	
}
