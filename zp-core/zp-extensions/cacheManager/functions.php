<?php

define("CACHE_HASH_LENGTH", strlen(sha1(HASH_SEED)) + 1);

function getImageProcessorURIFromCacheName($match, $watermarks) {
	$args = array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
	$set = array();
	$done = false;
	$params = explode('_', stripSuffix($match));
	while (!$done && count($params) > 1) {
		$check = array_pop($params);
		if (is_numeric($check)) {
			$set['s'] = $check;
			break;
		} else {
			$c = substr($check, 0, 1);
			if ($c == 'w' || $c == 'h') {
				$v = (int) substr($check, 1);
				if ($v) {
					$set[$c] = $v;
					continue;
				}
			}
			if ($c == 'c') {
				$c = substr($check, 0, 2);
				$v = (int) substr($check, 2);
				if ($v) {
					$set[$c] = $v;
					continue;
				}
			}
			if (!isset($set['w']) && !isset($set['h']) && !isset($set['s'])) {
				if (!isset($set['wm']) && in_array($check, $watermarks)) {
					$set['wm'] = $check;
				} else if ($check == 'thumb') {
					$set['t'] = true;
				} else {
					$set['effects'] = $check;
				}
			} else {
				array_push($params, $check);
				break;
			}
		}
	}
	$image = preg_replace('~.*/' . CACHEFOLDER . '/~', '', implode('_', $params)) . '.' . getSuffix($match);
	if (getOption('obfuscate_cache')) {
		$image = dirname($image) . '/' . substr(basename($image), CACHE_HASH_LENGTH);
	}
	return array($image, getImageArgs($set));
}

?>