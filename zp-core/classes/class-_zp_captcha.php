<?php
/**
 * Standins for when no captcha is enabled
 * 
 * @package zpcore\classes\helpers
 */
class _zp_captcha {

	public $name = NULL; // "captcha" name if no captcha plugin loaded

	function getCaptcha($prompt) {
		return array('input' => NULL, 'html' => '<p class="errorbox">' . gettext('No captcha handler is enabled.') . '</p>', 'hidden' => '');
	}

	function checkCaptcha($s1, $s2) {
		return false;
	}

}
