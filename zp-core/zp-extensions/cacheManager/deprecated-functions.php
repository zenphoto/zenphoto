<?php

class cachemanager_internal_deprecations {

	/**
	 * @deprecated
	 * @since 1.8.0.11
	 */
	static function addThemeCacheSize($owner, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark, $effects, $maxspace) {
		deprecated_functions::notify(gettext('Use cacheManager::addCacheSize()'), E_USER_NOTICE);
		cacheManager::addCacheSize($owner, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark, $effects, $maxspace);
	}

	/**
	 * @deprecated
	 * @since 1.8.0.11
	 */
	static function deleteThemeCacheSizes($owner) {
		deprecated_functions::notify(gettext('Use cacheManager::deleteCacheSizes()'), E_USER_NOTICE);
		cacheManager::deleteCacheSizes($owner);
	}

	/**
	 * Used to notify of legacy zenphoto cachemanager functions which are redundant in a properly implemented cache manager
	 * @param string $what the "missing" function name
	 */
	static function generalDeprecation($method) {
		$thumb = false;
		switch ($method) {
			case 'addDefaultThumbSize':
			case 'addThemeDefaultThumbSize' :
				$thumb = true;
			case 'addDefaultSizedImageSize':
			case 'addThemeDefaultSizedImageSize':
				$bt = debug_backtrace();

				if (isset($bt[1]['file'])) {
					$whom = stripSuffix(basename($bt[1]['file']));
					if (strtolower($whom) == 'themeoptions') {
						$whom = basename(dirname($bt[1]['file']));
					}
				} else {
					$whom = 'unknown';
				}
				deprecated_functions::notify_call($method, gettext('Use cacheManager::addCacheSize().'), E_USER_NOTICE);
				cacheManager::addCacheSize($whom, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $thumb);
				break;
			default:
				trigger_error(sprintf(gettext('Call to undefined method cachemanager::%1$s()'), $method), E_USER_ERROR);
		}
	}

}
