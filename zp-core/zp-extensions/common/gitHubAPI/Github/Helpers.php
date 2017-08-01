<?php

namespace Milo\Github;


/**
 * Just helpers.
 *
 * The JSON encode/decode methods are stolen from Nette Utils (https://github.com/nette/utils).
 *
 * @author  David Grudl
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Helpers
{
	private static $jsonMessages = [
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Syntax error, malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
		JSON_ERROR_UTF8 => 'Invalid UTF-8 sequence',
	];


	/** @var Http\IClient */
	private static $client;


	/**
	 * @param  mixed
	 * @return string
	 *
	 * @throws JsonException
	 */
	public static function jsonEncode($value)
	{
		if (PHP_VERSION_ID < 50500) {
			set_error_handler(function($severity, $message) { // needed to receive 'recursion detected' error
				restore_error_handler();
				throw new JsonException($message);
			});
		}

		$json = json_encode($value, JSON_UNESCAPED_UNICODE);

		if (PHP_VERSION_ID < 50500) {
			restore_error_handler();
		}

		if ($error = json_last_error()) {
			$message = isset(static::$jsonMessages[$error])
				? static::$jsonMessages[$error]
				: (PHP_VERSION_ID >= 50500 ? json_last_error_msg() : 'Unknown error');

			throw new JsonException($message, $error);
		}

		$json = str_replace(array("\xe2\x80\xa8", "\xe2\x80\xa9"), array('\u2028', '\u2029'), $json);
		return $json;
	}


	/**
	 * @param  mixed
	 * @return string
	 *
	 * @throws JsonException
	 */
	public static function jsonDecode($json)
	{
		$json = (string) $json;
		if (!preg_match('##u', $json)) {
			throw new JsonException('Invalid UTF-8 sequence', 5); // PECL JSON-C
		}

		$value = json_decode($json, FALSE, 512, (defined('JSON_C_VERSION') && PHP_INT_SIZE > 4) ? 0 : JSON_BIGINT_AS_STRING);

		if ($value === NULL && $json !== '' && strcasecmp($json, 'null')) { // '' does not clear json_last_error()
			$error = json_last_error();
			throw new JsonException(isset(static::$jsonMessages[$error]) ? static::$jsonMessages[$error] : 'Unknown error', $error);
		}
		return $value;
	}


	/**
	 * @param  bool
	 * @return Http\IClient
	 */
	public static function createDefaultClient($newInstance = FALSE)
	{
		if (self::$client === NULL || $newInstance) {
			self::$client = extension_loaded('curl')
				? new Http\CurlClient
				: new Http\StreamClient;
		}

		return self::$client;
	}

}
