<?php

/**
 *
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext('Extracts <em>XMP</em> metadata from images and <code>XMP</code> sidecar files.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'tagsFromMetadata';

zp_register_filter('image_metadata', 'tagsFromMetadata::new_image', -99999);
require_once(SERVERPATH . '/' . ZENFOLDER . '/exif/exifTranslations.php');

class tagsFromMetadata {

	function getOptionsSupported() {
		global $_zp_exifvars;
		$allowed = array();

		foreach ($_zp_exifvars as $key => $meta) {
			if ($meta[5] && $key != 'IPTCKeywords') {
				$allowed[$key] = 'tagsFromMetadata_tag_' . $key;
			}
		}
		$options = array(gettext('Metadata to tag') => array('key'				 => 'tagsFromMetadata_tag', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										'checkboxes' => $allowed,
										'desc'			 => gettext('Select the metadata items that will be tagged.'))
		);
		return $options;
	}

	static function getTaggingItems() {
		global $_zp_exifvars;
		$result = array();
		foreach ($_zp_exifvars as $key => $meta) {
			if ($meta[5] && $key != 'IPTCKeywords') {
				if (getOption('tagsFromMetadata_tag_' . $key)) {
					$result[] = $key;
				}
			}
		}
		return $result;
	}

	static function new_image($image) {
		global $_zp_exifvars;
		$entry_locale = getUserLocale();
		$languages = generateLanguageList();
		$element = array();
		$candidates = self::getTaggingItems();
		foreach ($candidates as $key) {
			if ($meta = $image->get($key)) {
				setupCurrentLocale('en_US');
				$en_us = $element[] = exifTranslate($meta);
				foreach ($languages as $language) {
					setupCurrentLocale($language);
					$xlated = exifTranslate($meta);
					if ($xlated != $en_us) { // the string has a translation in this language
						$element[] = $xlated;
					}
				}
			}
		}
		setupCurrentLocale($entry_locale);
		$element = array_unique(array_merge($image->getTags(), $element));
		$image->setTags($element);
		$image->save();
		return $image;
	}

}
