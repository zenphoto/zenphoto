<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Video_internal_deprecations {

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getBody($option) {
		deprecated_functions::notify(gettext('Use the getContent() method.'));
	}

}
