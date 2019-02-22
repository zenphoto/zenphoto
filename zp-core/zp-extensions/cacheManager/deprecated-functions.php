<?php

class cachemanager_internal_deprecations {

	/**
	 * @deprecated Zenphoto 1.6 - Use cacheManager::addCacheSize() instead
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeCacheSize() {
		deprecated_functions::notify(gettext('Use cacheManager::addCacheSize() instead'), E_USER_NOTICE);
	}

	/**
	 * @deprecated Zenphoto 1.6 - Use cacheManager::addDefaultThumbSize()
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeDefaultThumbSize() {
		deprecated_functions::notify(gettext("Use cacheManager::addDefaultThumbSize()"), E_USER_NOTICE);
	}

	/**
	 * @deprecated Zenphoto 1.6 - Use cacheManager::addDefaultSizedImageSize()
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeDefaultSizedImageSize() {
		deprecated_functions::notify(gettext("Use cacheManager::addDefaultSizedImageSize()"), E_USER_NOTICE);
	}

	/**
	 * @deprecated Zenphoto 1.6 - Use cacheManager::deleteCacheSizes()
	 * @since Zenphoto 1.5.1
	 */
	static function deleteThemeCacheSizes() {
		deprecated_functions::notify(gettext('Use cacheManager::deleteCacheSizes()'), E_USER_NOTICE);
	}

}

/**
 * @deprecated Zenphoto 1.6 - Use cacheManager::getTitle()
 * @since Zenphoto 1.5.1
 */
function getTitle($table, $row) {
	return cacheManager::getTitle($table, $row);
}

/**
 * @deprecated Zenphoto 1.6 - Use cacheManager::recordMissing()
 * @since Zenphoto 1.5.1
 */
function recordMissing($table, $row, $image) {
	cacheManager::recordMissing($table, $row, $image);
}

/**
 * @deprecated Zenphoto 1.6 - Use cacheManager::updateCacheName()
 * @since Zenphoto 1.5.1
 */
function updateCacheName($text, $target, $update) {
	return cacheManager::updateCacheName($text, $target, $update);
}
