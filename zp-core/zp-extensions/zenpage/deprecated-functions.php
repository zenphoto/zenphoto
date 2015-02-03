<?php

/*
 * These are the Zenpage functions which have been deprecated
 *
 * See the deprecated functions plugin for examples
 */

class Zenpage_internal_deprecations {

}

function getNextPrevNews($option = 'Next', $sortorder = 'date', $sortdirection = 'desc') {
	deprecated_functions::notify(gettext('Use the individual getPrevNewsURL() and getNextNewsURL() functions.'));
	$request = 'get' . ucfirst($option) . 'NewsURL';
	return $request($sortorder, $sortdirection);
}

?>