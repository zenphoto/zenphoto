<?php

class cachemanager_internal_deprecations {

	/**
	 * @deprecated
	 * @since 1.8.0.11
	 */
	static function addThemeCacheSize() {
		deprecated_functions::notify(gettext('Use cacheManager::addCacheSize()'), E_USER_NOTICE);
	}

	/**
	 * @deprecated
	 * @since 1.8.0.11
	 */
	static function deleteThemeCacheSizes() {
		deprecated_functions::notify(gettext('Use cacheManager::deleteCacheSizes()'), E_USER_NOTICE);
	}

	/**
	 * Used to notify of legacy zenphoto cachemanager functions which are redundant in a properly implemented cache manager
	 * @param string $what the "missing" function name
	 */
	static function generalDeprecation($what) {
		deprecated_functions::notify_call($what, gettext('The method is redundant with netPhotoGraphics. Remove the function call.'), E_USER_NOTICE);
	}

}
