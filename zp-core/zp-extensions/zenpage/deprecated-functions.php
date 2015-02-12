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

/**
 * Publishes a page or news article
 *
 * @param object $obj
 * @param int $show the value for publishing
 * @return string
 */
function zenpagePublish($obj, $show) {
	deprecated_functions::notify(gettext('Use the setShow method directly.'));
	$obj->setShow($show);
	$obj->save();
}

?>