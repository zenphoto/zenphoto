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

}
