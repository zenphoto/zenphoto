<?php

/**
  * UTF-8 friendly replacement functions - v0.2
  * Copyright (C) 2004-2006 Niels Leenheer & Andy Matsubara
  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.
  *
  *	Supported functions:
  * - utf8::convert()
  * - utf8::detect()
  * - utf8::strtolower()
  * - utf8::strtoupper()
  * - utf8::strlen()
  * - utf8::strwidth()
  * - utf8::substr()
  * - utf8::strimwidth()
  * - utf8::strcut()
  * - utf8::strrpos()
  * - utf8::strpos()
  * - utf8::substr_count()
  * - utf8::encode_mimeheader()
  * - utf8::send_mail()
  * - utf8::encode_javascript()
  * - utf8::encode_numericentity()
  *
  * @package core
  */

// force UTF-8 Ø

global $_zp_UTF8;
$_zp_UTF8 = new utf8();

class utf8 {
	/**
	 * Character set translation support
	 *
	 * @return utf8
	 */

	var $charsets;
	var $mb_sets;
	var $iconv_sets;

	function utf8() {
		$this->charsets = array(	"ASMO-708" => gettext("Arabic"),
															"BIG5" => gettext("Chinese Traditional"),
															"CP1026" => gettext("IBM EBCDIC (Turkish Latin-5)"),
															"cp866" => gettext("Cyrillic (DOS)"),
															"CP870" => gettext("IBM EBCDIC (Multilingual Latin-2)"),
															"CISO2022JP" => gettext("Japanese (JIS-Allow 1 byte Kana)"),
															"DOS-720" => gettext("Arabic (DOS)"),
															"DOS-862" => gettext("Hebrew (DOS)"),
															"EBCDIC-CP-US" => gettext("IBM EBCDIC (US-Canada)"),
															"EUC-CN" => gettext("Chinese Simplified (EUC)"),
															"EUC-JP" => gettext("Japanese (EUC)"),
															"EUC-KR" => gettext("Korean (EUC)"),
															"GB2312" => gettext("Chinese Simplified (GB2312)"),
															"HZ-GB-2312" => gettext("Chinese Simplified (HZ)"),
															"IBM437" => gettext("OEM United States"),
															"IBM737" => gettext("Greek (DOS)"),
															"IBM775" => gettext("Baltic (DOS)"),
															"IBM850" => gettext("Western European (DOS)"),
															"IBM852" => gettext("Central European (DOS)"),
															"IBM857" => gettext("Turkish (DOS)"),
															"IBM861" => gettext("Icelandic (DOS)"),
															"IBM869" => gettext("Greek, Modern (DOS)"),
															"ISO-2022-JP" => gettext("Japanese (JIS)"),
															"ISO-2022-JP" => gettext("Japanese (JIS-Allow 1 byte Kana - SO/SI)"),
															"ISO-2022-KR" => gettext("Korean (ISO)"),
															"ISO-8859-1" => gettext("Western European (ISO)"),
															"ISO-8859-15" => gettext("Latin 9 (ISO)"),
															"ISO-8859-2" => gettext("Central European (ISO)"),
															"ISO-8859-3" => gettext("Latin 3 (ISO)"),
															"ISO-8859-4" => gettext("Baltic (ISO)"),
															"ISO-8859-5" => gettext("Cyrillic (ISO)"),
															"ISO-8859-6" => gettext("Arabic (ISO)"),
															"ISO-8859-7" => gettext("Greek (ISO)"),
															"ISO-8859-8" => gettext("Hebrew (ISO-Visual)"),
															"ISO-8859-8-i" => gettext("Hebrew (ISO-Logical)"),
															"ISO-8859-9" => gettext("Turkish (ISO)"),
															"JOHAB" => gettext("Korean (Johab)"),
															"KOi8-R" => gettext("Cyrillic (KOI8-R)"),
															"KOi8-U" => gettext("Cyrillic (KOI8-U)"),
															"KS_C_5601-1987" => gettext("Korean"),
															"MACINTOSH" => gettext("Western European (MAC)"),
															"SHIFT_JIS" => gettext("Japanese (Shift-JIS)"),
															"UNICODE" => gettext("Unicode"),
															"UNICODEFFFE" => gettext("Unicode (Big-Endian)"),
															"US-ASCII" => gettext("US-ASCII"),
															"UTF-7" => gettext("Unicode (UTF-7)"),
															"UTF-8" => gettext("Unicode (UTF-8)"),
															"WINDOWS-1250" => gettext("Central European (Windows)"),
															"WINDOWS-1251" => gettext("Cyrillic (Windows)"),
															"WINDOWS-1252" => gettext("Western European (Windows)"),
															"WINDOWS-1253" => gettext("Greek (Windows)"),
															"WINDOWS-1254" => gettext("Turkish (Windows)"),
															"WINDOWS-1255" => gettext("Hebrew (Windows)"),
															"WINDOWS-1256" => gettext("Arabic (Windows)"),
															"WINDOWS-1257" => gettext("Baltic (Windows)"),
															"WINDOWS-1258" => gettext("Vietnamese (Windows)"),
															"WINDOWS-874" => gettext("Thai (Windows)")
															);
		// prune the list to supported character sets
		$this->iconv_sets = array();
		$this->mb_sets = array();
		if (function_exists('mb_convert_encoding')) {
			if (function_exists('mb_list_encodings')) {
				$list = mb_list_encodings();
			} else {
				$list = array("pass",  // from my PHP 5 mb_list_encodings list....
											"auto",
											"byte2be",
											"byte2le",
											"byte4be",
											"byte4le",
											"BASE64",
											"UUENCODE",
											"HTML-ENTITIES",
											"Quoted-Printable",
											"7bit",
											"8bit",
											"UCS-4",
											"UCS-4BE",
											"UCS-4LE",
											"UCS-2",
											"UCS-2BE",
											"UCS-2LE",
											"UTF-32",
											"UTF-32BE",
											"UTF-32LE",
											"UTF-16",
											"UTF-16BE",
											"UTF-16LE",
											"UTF-8",
											"UTF-7",
											"UTF7-IMAP",
											"ASCII",
											"EUC-JP",
											"SJIS",
											"eucJP-win",
											"SJIS-win",
											"CP51932",
											"JIS",
											"ISO-2022-JP",
											"ISO-2022-JP-MS",
											"Windows-1252",
											"ISO-8859-1",
											"ISO-8859-2",
											"ISO-8859-3",
											"ISO-8859-4",
											"ISO-8859-5",
											"ISO-8859-6",
											"ISO-8859-7",
											"ISO-8859-8",
											"ISO-8859-9",
											"ISO-8859-10",
											"ISO-8859-13",
											"ISO-8859-14",
											"ISO-8859-15",
											"ISO-8859-16",
											"EUC-CN",
											"CP936",
											"HZ",
											"EUC-TW",
											"BIG-5",
											"EUC-KR",
											"UHC",
											"ISO-2022-KR",
											"Windows-1251",
											"CP866",
											"KOI8-R",
											"ArmSCII-8");
			}
			foreach ($this->charsets as $key=>$encoding) {
				if (in_array($key, $list)) {
					$this->mb_sets[$key] = $encoding;
				}
			}

		}
		if (function_exists('iconv')) {
			foreach ($this->charsets as $key=>$encoding) {
				if (@iconv("UTF-8", $key, "UTF-8")!==false) {
					$this->iconv_sets[$key] = $encoding;
				}
			}
		}
	}

	/**
	 * Convert a foreign charset encoding from or to UTF-8
	 */
	function convert($string, $encoding = NULL, $destination = 'UTF-8') {
		if (!$encoding) $encoding = utf8::detect($string);
		if ($encoding == $destination) return $string;

		if (!empty($this->mb_sets)) {
			$encode_mb = array_key_exists($encoding, $this->mb_sets);
			$dest_mb = array_key_exists($destination, $this->mb_sets);
			if ($encode_mb && $dest_mb) {
				@mb_substitute_character('none');
				return mb_convert_encoding($string, $destination, $encoding);
			}
		} else {
			$encode_mb = $dest_mb = false;
		}

		$encode_iconv = array_key_exists($encoding, $this->iconv_sets);
		$dest_iconv = array_key_exists($destination, $this->iconv_sets);
		if ($encode_iconv && $dest_iconv) {
			return @iconv($encoding, $destination . '//IGNORE', $string);
		}
		// must use mixed conversion
		@mb_substitute_character('none');
		if ($encode_mb) {
			$instring = mb_convert_encoding($string, 'UTF-8', $encoding);
		} else if ($encode_iconv) {
			$instring = @iconv($encoding, 'UTF-8' . '//IGNORE', $string);
		} else  {
			$instring = $string;
		}
		if ($dest_mb) {
			$outstring = mb_convert_encoding($string, $destination, 'UTF-8');
		} else if ($dest_iconv) {
			$outstring = @iconv('UTF-8', $destination . '//IGNORE', $string);
		} else {
			$outstring = $string;
		}
		return $outstring;
	}

	/**
	 * Detect the encoding of the string
	 */
	static function detect($string) {
		if (function_exists('mb_detect_encoding')) {
			$encoding = mb_detect_encoding($string);
			if ($encoding) {
				return $encoding;
			}
		}
		if (!preg_match("/[\x80-\xFF]/", $string) && !preg_match("/\x1B/", $string))
			return 'US-ASCII';

		if (!preg_match("/[\x80-\xFF]/", $string) && preg_match("/\x1B/", $string))
			return 'ISO-2022-JP';

		if (preg_match("/^([\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF])+$/", $string) == 1)
			return 'UTF-8';

		if (preg_match("/^([\x01-\x7F]|\x8E[\xA0-\xDF]|\x8F[xA1-\xFE][\xA1-\xFE]|[\xA1-\xFE][\xA1-\xFE])+$/", $string) == 1)
			return 'EUC-JP';

		if (preg_match("/^([\x01-\x7F]|[\xA0-\xDF]|[\x81-\xFC][\x40-\xFC])+$/", $string) == 1)
			return 'Shift_JIS';

		return 'ISO-8859-1';
	}


	/**
	 * Determine the number of characters of a string
	 * Compatible with mb_strlen(), an UTF-8 friendly replacement for strlen()
	 */
	static function strlen($str) {
		return preg_match_all('/[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]/', $str, $arr);
	}

	/**
	 * Count the number of substring occurances
	 * Compatible with mb_substr_count(), an UTF-8 friendly replacement for substr_count()
	 */
	static function substr_count($haystack, $needle) {
		return substr_count($haystack, $needle);
	}

	/**
	 * Return part of a string, length and offset in characters
	 * Compatible with mb_substr(), an UTF-8 friendly replacement for substr()
	 */
	static function substr($str, $start , $length = NULL) {
		preg_match_all('/[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]/', $str, $arr);
		if (is_null($length)) {
			$arr = array_slice($arr[0], $start);
		} else {
			$arr = array_slice($arr[0], $start, $length);
		}
		$result = implode('', $arr);
		return $result;
	}

	/**
	 * Return part of a string, length and offset in bytes
	 * Compatible with mb_strcut()
	 */
	static function strcut($str, $start, $length = NULL) {
		if ($start < 0)	$start += strlen($str);
		$original = $start;
		while ($start > 0 && intval(ord($str[$start]) & 0xC0) == 0x80)
			$start--;

		$start = max($start, 0);
		$original = max($original, 0);

		if ($start < strlen($str))
		{
			if (is_null($length)) {
				return substr($str, $start);
			}
			elseif ($length > 0) {
				$end = $start + $length;

				while ($end > 0 && intval(ord($str[$end]) & 0xC0) == 0x80)
					$end--;

				return substr($str, $start, $end - $start);
			}
			elseif ($length < 0) {
				$end = strlen($str) + $length - ($original - $start);

				while ($end > 0 && intval(ord($str[$end]) & 0xC0) == 0x80)
					$end--;

				if ($end > 0)
					return substr($str, $start, $end - $start);
			}
		}

		return '';
	}

	/**
	 * Determine the width of a string
	 * Compatible with mb_strwidth()
	 */
	static function strwidth($str) {
		$double = preg_match_all('/[\xE2-\xEF][\x80-\xBF][\x80-\xBF]/', $str, $arr) - 			// U+2000 - U+FFFF = double width
				  preg_match_all('/\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F]/', $str, $arr);		// U+FF61 - U+FF9F = single width
		$null   = preg_match_all('/[\x00-\x19]/', $str, $arr);									// U+0000 - U+0019 = no width

		return UTF8::strlen($str) - $null + $double;
	}

	/**
	 * Get truncated string with specified width
	 * Compatible with mb_strimwidth()
	 */
	static function strimwidth($str, $start, $width, $trimmarker = '') {

		$str   = UTF8::substr($str, $start);
		$width = $width - UTF8::strwidth($trimmarker);
		$c = 0;

		for ($i = 0; $i < strlen($str); $i++)
		{
			$b1 = (int)ord($str[$i]);

			if ($b1 < 0x80 || $b1 > 0xBF)
			{
				$c++;

				if ($b1 > 0xE2)
				{
					$b2 = (int)ord($str[$i + 1]);
					$b3 = (int)ord($str[$i + 2]);

					if (($b2 == 0xBD && $b3 >= 0xA1) || ($b2 == 0xBE && $b3 <= 0x9F))
						$count++;
					else
						$count = $count + 2;
				}
				else
					$count++;
			}

			if ($count > $width) {
				return UTF8::substr($str, 0, $c - 1) . $trimmarker;
			}
		}
	}

	/**
	 * Find position of last occurance of a string in another string
	 * Compatible with mb_strrpos(), an UTF-8 friendly replacement for strrpos()
	 */
	static function strrpos($haystack, $needle) {
		$pos = strrpos($haystack, $needle);
		if ($pos === false)
			return false;
		else
			return UTF8::strlen(substr($haystack, 0, $pos));
	}

	/**
	 * Find position of first occurance of a string in another string
	 * Compatible with mb_strpos(), an UTF-8 friendly replacement for strpos()
	 */
	static function strpos($haystack, $needle, $offset = 0) {
		$comp = 0;

		while (!isset($length) || $length < $offset)
		{
			$pos = strpos($haystack, $needle, $offset + $comp);
			if ($pos === false) return false;
			$length = UTF8::strlen(substr($haystack, 0, $pos));
			if ($length < $offset) $comp = $pos - $length;
		}

		return $length;
	}

	/**
	 * Convert a string to lower case
	 * Compatible with mb_strtolower(), an UTF-8 friendly replacement for strtolower()
	 */
	static function strtolower($str) {
		global $UTF8_TABLES;
		return strtr($str, $UTF8_TABLES['strtolower']);
	}

	/**
	 * Convert a string to upper case
	 * Compatible with mb_strtoupper(), an UTF-8 friendly replacement for strtoupper()
	 */
	static function strtoupper($str) {
		global $UTF8_TABLES;
		return strtr($str, $UTF8_TABLES['strtoupper']);
	}

	/**
	 * Encode a string for use in a MIME header
	 * Simplied replacement for mb_encode_mimeheader()
	 */
	static function encode_mimeheader($str) {
		$length = 45; $pos = 0; $max = strlen($str);
    $buffer = '';
		while ($pos < $max)
		{
			if ($pos + $length < $max)
			{
				$adjust = 0;

				while (intval(ord($str[$pos + $length + $adjust]) & 0xC0) == 0x80)
					$adjust--;

				$buffer .= ($buffer == '' ? '' : "?=\n =?UTF-8?B?") . base64_encode(substr($str, $pos, $length + $adjust));
				$pos = $pos + $length + $adjust;
			}
			else
			{
				$buffer .= ($buffer == '' ? '' : "?=\n =?UTF-8?B?") . base64_encode(substr($str, $pos));
				$pos = $max;
			}
		}

		return '=?UTF-8?B?' . $buffer . '?=';
	}

	/**
	 * Send mail
	 * Replacement for mb_send_mail(), an UTF-8 friendly replacement for mail()
	 */
	static function send_mail($to, $subject, $message , $additional_headers = '', $additional_parameter = '') {
		$subject = UTF8::encode_mimeheader($subject);
		$message = chunk_split(base64_encode($message));

		$additional_headers = trim($additional_headers);

		if ($additional_headers != '')
			$additional_headers .= "\n";

		$additional_headers .=
			"Mime-Version: 1.0\n" .
			"Content-Type: text/plain; charset=UTF-8\n" .
			"Content-Transfer-Encoding: base64";

		if(SAFE_MODE){
			return @mail($to, $subject, $message, $additional_headers);
		} else {
			return @mail($to, $subject, $message, $additional_headers, $additional_parameter);
		}
	}

	/**
	 * Prepare an UTF-8 string for use in JavaScript
	 */
	static function encode_javascript($string)
	{
		$string = str_replace ('\\', '\\\\', $string);
		$string = str_replace ('"', '\\"', $string);
		$string = str_replace ("'", "\\'", $string);
		$string = str_replace ("\n", "\\n", $string);
		$string = str_replace ("\r", "\\r", $string);
		$string = str_replace ("\t", "\\t", $string);

		$len = strlen ($string);
		$pos = 0;
		$out = '';

		while ($pos < $len)
		{
			$ascii = ord (substr ($string, $pos, 1));

			if ($ascii >= 0xF0)
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xF0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;
				$byte[3] = ord(substr ($string, $pos + 2, 1)) - 0x80;
				$byte[4] = ord(substr ($string, $pos + 3, 1)) - 0x80;


				$char_code = ($byte[1] << 18) + ($byte[2] << 12) + ($byte[3] << 6) + $byte[4];
				$pos += 4;
			}
			elseif (($ascii >= 0xE0) && ($ascii < 0xF0))
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xE0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;
				$byte[3] = ord(substr ($string, $pos + 2, 1)) - 0x80;

				$char_code = ($byte[1] << 12) + ($byte[2] << 6) + $byte[3];
				$pos += 3;
			}
			elseif (($ascii >= 0xC0) && ($ascii < 0xE0))
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xC0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;

				$char_code = ($byte[1] << 6) + $byte[2];
				$pos += 2;
			}
			else
			{
				$char_code = ord(substr ($string, $pos, 1));
				$pos += 1;
			}

			if ($char_code < 0x80)
				$out .= chr($char_code);
			else
				$out .=  '\\u'. str_pad(dechex($char_code), 4, '0', STR_PAD_LEFT);
		}

		return $out;
	}

	/**
	 * Encode an UTF-8 string with numeric entities
	 * Simplied replacement for mb_encode_numericentity()
	 */
	static function encode_numericentity($string)
	{
		$len = strlen ($string);
		$pos = 0;
		$out = '';

		while ($pos < $len)
		{
			$ascii = ord (substr ($string, $pos, 1));

			if ($ascii >= 0xF0)
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xF0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;
				$byte[3] = ord(substr ($string, $pos + 2, 1)) - 0x80;
				$byte[4] = ord(substr ($string, $pos + 3, 1)) - 0x80;

				$char_code = ($byte[1] << 18) + ($byte[2] << 12) + ($byte[3] << 6) + $byte[4];
				$pos += 4;
			}
			elseif (($ascii >= 0xE0) && ($ascii < 0xF0))
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xE0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;
				$byte[3] = ord(substr ($string, $pos + 2, 1)) - 0x80;

				$char_code = ($byte[1] << 12) + ($byte[2] << 6) + $byte[3];
				$pos += 3;
			}
			elseif (($ascii >= 0xC0) && ($ascii < 0xE0))
			{
				$byte[1] = ord(substr ($string, $pos, 1)) - 0xC0;
				$byte[2] = ord(substr ($string, $pos + 1, 1)) - 0x80;

				$char_code = ($byte[1] << 6) + $byte[2];
				$pos += 2;
			}
			else
			{
				$char_code = ord(substr ($string, $pos, 1));
				$pos += 1;
			}

			if ($char_code < 0x80)
				$out .= chr($char_code);
			else
				$out .=  '&#'. str_pad($char_code, 5, '0', STR_PAD_LEFT) . ';';
		}

		return $out;
	}
}

/*******************************************************************************************************/

global $UTF8_TABLES;

$UTF8_TABLES['strtolower'] = array(
"Ｚ"=>"ｚ","Ｙ"=>"ｙ","Ｘ"=>"ｘ","Ｗ"=>"ｗ","Ｖ"=>"ｖ","Ｕ"=>"ｕ",
"Ｔ"=>"ｔ","Ｓ"=>"ｓ","Ｒ"=>"ｒ","Ｑ"=>"ｑ","Ｐ"=>"ｐ","Ｏ"=>"ｏ",
"Ｎ"=>"ｎ","Ｍ"=>"ｍ","Ｌ"=>"ｌ","Ｋ"=>"ｋ","Ｊ"=>"ｊ","Ｉ"=>"ｉ",
"Ｈ"=>"ｈ","Ｇ"=>"ｇ","Ｆ"=>"ｆ","Ｅ"=>"ｅ","Ｄ"=>"ｄ","Ｃ"=>"ｃ",
"Ｂ"=>"ｂ","Ａ"=>"ａ","Å"=>"å","K"=>"k","Ω"=>"ω","Ώ"=>"ώ",
"Ὼ"=>"ὼ","Ό"=>"ό","Ὸ"=>"ὸ","Ῥ"=>"ῥ","Ύ"=>"ύ","Ὺ"=>"ὺ",
"Ῡ"=>"ῡ","Ῠ"=>"� ","Ί"=>"ί","Ὶ"=>"ὶ","Ῑ"=>"ῑ","Ῐ"=>"ῐ",
"Ή"=>"ή","Ὴ"=>"ὴ","Έ"=>"έ","Ὲ"=>"ὲ","Ά"=>"ά","Ὰ"=>"ὰ",
"Ᾱ"=>"ᾱ","Ᾰ"=>"ᾰ","Ὧ"=>"ὧ","Ὦ"=>"ὦ","Ὥ"=>"ὥ","Ὤ"=>"ὤ",
"Ὣ"=>"ὣ","Ὢ"=>"ὢ","Ὡ"=>"ὡ","Ὠ"=>"� ","Ὗ"=>"ὗ","Ὕ"=>"ὕ",
"Ὓ"=>"ὓ","Ὑ"=>"ὑ","Ὅ"=>"ὅ","Ὄ"=>"ὄ","Ὃ"=>"ὃ","Ὂ"=>"ὂ",
"Ὁ"=>"ὁ","Ὀ"=>"ὀ","Ἷ"=>"ἷ","Ἶ"=>"ἶ","Ἵ"=>"ἵ","Ἴ"=>"ἴ",
"Ἳ"=>"ἳ","Ἲ"=>"ἲ","Ἱ"=>"ἱ","Ἰ"=>"ἰ","Ἧ"=>"ἧ","Ἦ"=>"ἦ",
"Ἥ"=>"ἥ","Ἤ"=>"ἤ","Ἣ"=>"ἣ","Ἢ"=>"ἢ","Ἡ"=>"ἡ","Ἠ"=>"� ",
"Ἕ"=>"ἕ","Ἔ"=>"ἔ","Ἓ"=>"ἓ","Ἒ"=>"ἒ","Ἑ"=>"ἑ","Ἐ"=>"ἐ",
"Ἇ"=>"ἇ","Ἆ"=>"ἆ","Ἅ"=>"ἅ","Ἄ"=>"ἄ","Ἃ"=>"ἃ","Ἂ"=>"ἂ",
"Ἁ"=>"ἁ","Ἀ"=>"ἀ","Ỹ"=>"ỹ","Ỷ"=>"ỷ","Ỵ"=>"ỵ","Ỳ"=>"ỳ",
"Ự"=>"ự","Ữ"=>"ữ","Ử"=>"ử","Ừ"=>"ừ","Ứ"=>"ứ","Ủ"=>"ủ",
"Ụ"=>"ụ","Ợ"=>"ợ","� "=>"ỡ","Ở"=>"ở","Ờ"=>"ờ","Ớ"=>"ớ",
"Ộ"=>"ộ","Ỗ"=>"ỗ","Ổ"=>"ổ","Ồ"=>"ồ","Ố"=>"ố","Ỏ"=>"ỏ",
"Ọ"=>"ọ","Ị"=>"ị","Ỉ"=>"ỉ","Ệ"=>"ệ","Ễ"=>"ễ","Ể"=>"ể",
"Ề"=>"ề","Ế"=>"ế","Ẽ"=>"ẽ","Ẻ"=>"ẻ","Ẹ"=>"ẹ","Ặ"=>"ặ",
"Ẵ"=>"ẵ","Ẳ"=>"ẳ","Ằ"=>"ằ","Ắ"=>"ắ","Ậ"=>"ậ","Ẫ"=>"ẫ",
"Ẩ"=>"ẩ","Ầ"=>"ầ","Ấ"=>"ấ","Ả"=>"ả","� "=>"ạ","Ẕ"=>"ẕ",
"Ẓ"=>"ẓ","Ẑ"=>"ẑ","Ẏ"=>"ẏ","Ẍ"=>"ẍ","Ẋ"=>"ẋ","Ẉ"=>"ẉ",
"Ẇ"=>"ẇ","Ẅ"=>"ẅ","Ẃ"=>"ẃ","Ẁ"=>"ẁ","Ṿ"=>"ṿ","Ṽ"=>"ṽ",
"Ṻ"=>"ṻ","Ṹ"=>"ṹ","Ṷ"=>"ṷ","Ṵ"=>"ṵ","Ṳ"=>"ṳ","Ṱ"=>"ṱ",
"Ṯ"=>"ṯ","Ṭ"=>"ṭ","Ṫ"=>"ṫ","Ṩ"=>"ṩ","Ṧ"=>"ṧ","Ṥ"=>"ṥ",
"Ṣ"=>"ṣ","� "=>"ṡ","Ṟ"=>"ṟ","Ṝ"=>"ṝ","Ṛ"=>"ṛ","Ṙ"=>"ṙ",
"Ṗ"=>"ṗ","Ṕ"=>"ṕ","Ṓ"=>"ṓ","Ṑ"=>"ṑ","Ṏ"=>"ṏ","Ṍ"=>"ṍ",
"Ṋ"=>"ṋ","Ṉ"=>"ṉ","Ṇ"=>"ṇ","Ṅ"=>"ṅ","Ṃ"=>"ṃ","Ṁ"=>"ṁ",
"Ḿ"=>"ḿ","Ḽ"=>"ḽ","Ḻ"=>"ḻ","Ḹ"=>"ḹ","Ḷ"=>"ḷ","Ḵ"=>"ḵ",
"Ḳ"=>"ḳ","Ḱ"=>"ḱ","Ḯ"=>"ḯ","Ḭ"=>"ḭ","Ḫ"=>"ḫ","Ḩ"=>"ḩ",
"Ḧ"=>"ḧ","Ḥ"=>"ḥ","Ḣ"=>"ḣ","� "=>"ḡ","Ḟ"=>"ḟ","Ḝ"=>"ḝ",
"Ḛ"=>"ḛ","Ḙ"=>"ḙ","Ḗ"=>"ḗ","Ḕ"=>"ḕ","Ḓ"=>"ḓ","Ḑ"=>"ḑ",
"Ḏ"=>"ḏ","Ḍ"=>"ḍ","Ḋ"=>"ḋ","Ḉ"=>"ḉ","Ḇ"=>"ḇ","Ḅ"=>"ḅ",
"Ḃ"=>"ḃ","Ḁ"=>"ḁ","Ֆ"=>"ֆ","Օ"=>"օ","Ք"=>"ք","Փ"=>"փ",
"Ւ"=>"ւ","Ց"=>"ց","Ր"=>"ր","Տ"=>"տ","Վ"=>"վ","Ս"=>"ս",
"Ռ"=>"ռ","Ջ"=>"ջ","Պ"=>"պ","Չ"=>"չ","Ո"=>"ո","Շ"=>"շ",
"Ն"=>"ն","Յ"=>"յ","Մ"=>"մ","Ճ"=>"ճ","Ղ"=>"ղ","Ձ"=>"ձ",
"Հ"=>"հ","Կ"=>"կ","Ծ"=>"ծ","Խ"=>"խ","Լ"=>"լ","Ի"=>"ի",
"Ժ"=>"ժ","Թ"=>"թ","Ը"=>"ը","Է"=>"է","Զ"=>"զ","Ե"=>"ե",
"Դ"=>"դ","Գ"=>"գ","Բ"=>"բ","Ա"=>"ա","Ԏ"=>"ԏ","Ԍ"=>"ԍ",
"Ԋ"=>"ԋ","Ԉ"=>"ԉ","Ԇ"=>"ԇ","Ԅ"=>"ԅ","Ԃ"=>"ԃ","Ԁ"=>"ԁ",
"Ӹ"=>"ӹ","Ӵ"=>"ӵ","Ӳ"=>"ӳ","Ӱ"=>"ӱ","Ӯ"=>"ӯ","Ӭ"=>"ӭ",
"Ӫ"=>"ӫ","Ө"=>"ө","Ӧ"=>"ӧ","Ӥ"=>"ӥ","Ӣ"=>"ӣ","� "=>"ӡ",
"Ӟ"=>"ӟ","Ӝ"=>"ӝ","Ӛ"=>"ӛ","Ә"=>"ә","Ӗ"=>"ӗ","Ӕ"=>"ӕ",
"Ӓ"=>"ӓ","Ӑ"=>"ӑ","Ӎ"=>"ӎ","Ӌ"=>"ӌ","Ӊ"=>"ӊ","Ӈ"=>"ӈ",
"Ӆ"=>"ӆ","Ӄ"=>"ӄ","Ӂ"=>"ӂ","Ҿ"=>"ҿ","Ҽ"=>"ҽ","Һ"=>"һ",
"Ҹ"=>"ҹ","Ҷ"=>"ҷ","Ҵ"=>"ҵ","Ҳ"=>"ҳ","Ұ"=>"ұ","Ү"=>"ү",
"Ҭ"=>"ҭ","Ҫ"=>"ҫ","Ҩ"=>"ҩ","Ҧ"=>"ҧ","Ҥ"=>"ҥ","Ң"=>"ң",
"� "=>"ҡ","Ҟ"=>"ҟ","Ҝ"=>"ҝ","Қ"=>"қ","Ҙ"=>"ҙ","Җ"=>"җ",
"Ҕ"=>"ҕ","Ғ"=>"ғ","Ґ"=>"ґ","Ҏ"=>"ҏ","Ҍ"=>"ҍ","Ҋ"=>"ҋ",
"Ҁ"=>"ҁ","Ѿ"=>"ѿ","Ѽ"=>"ѽ","Ѻ"=>"ѻ","Ѹ"=>"ѹ","Ѷ"=>"ѷ",
"Ѵ"=>"ѵ","Ѳ"=>"ѳ","Ѱ"=>"ѱ","Ѯ"=>"ѯ","Ѭ"=>"ѭ","Ѫ"=>"ѫ",
"Ѩ"=>"ѩ","Ѧ"=>"ѧ","Ѥ"=>"ѥ","Ѣ"=>"ѣ","� "=>"ѡ","Я"=>"я",
"Ю"=>"ю","Э"=>"э","Ь"=>"ь","Ы"=>"ы","Ъ"=>"ъ","Щ"=>"щ",
"Ш"=>"ш","Ч"=>"ч","Ц"=>"ц","Х"=>"х","Ф"=>"ф","У"=>"у",
"Т"=>"т","С"=>"с","� "=>"р","П"=>"п","О"=>"о","Н"=>"н",
"М"=>"м","Л"=>"л","К"=>"к","Й"=>"й","И"=>"и","З"=>"з",
"Ж"=>"ж","Е"=>"е","Д"=>"д","Г"=>"г","В"=>"в","Б"=>"б",
"А"=>"а","Џ"=>"џ","Ў"=>"ў","Ѝ"=>"ѝ","Ќ"=>"ќ","Ћ"=>"ћ",
"Њ"=>"њ","Љ"=>"љ","Ј"=>"ј","Ї"=>"ї","І"=>"і","Ѕ"=>"ѕ",
"Є"=>"є","Ѓ"=>"ѓ","Ђ"=>"ђ","Ё"=>"ё","Ѐ"=>"ѐ","ϴ"=>"θ",
"Ϯ"=>"ϯ","Ϭ"=>"ϭ","Ϫ"=>"ϫ","Ϩ"=>"ϩ","Ϧ"=>"ϧ","Ϥ"=>"ϥ",
"Ϣ"=>"ϣ","� "=>"ϡ","Ϟ"=>"ϟ","Ϝ"=>"ϝ","Ϛ"=>"ϛ","Ϙ"=>"ϙ",
"Ϋ"=>"ϋ","Ϊ"=>"ϊ","Ω"=>"ω","Ψ"=>"ψ","Χ"=>"χ","Φ"=>"φ",
"Υ"=>"υ","Τ"=>"τ","Σ"=>"σ","Ρ"=>"ρ","� "=>"π","Ο"=>"ο",
"Ξ"=>"ξ","Ν"=>"ν","Μ"=>"μ","Λ"=>"λ","Κ"=>"κ","Ι"=>"ι",
"Θ"=>"θ","Η"=>"η","Ζ"=>"ζ","Ε"=>"ε","Δ"=>"δ","Γ"=>"γ",
"Β"=>"β","Α"=>"α","Ώ"=>"ώ","Ύ"=>"ύ","Ό"=>"ό","Ί"=>"ί",
"Ή"=>"ή","Έ"=>"έ","Ά"=>"ά","Ȳ"=>"ȳ","Ȱ"=>"ȱ","Ȯ"=>"ȯ",
"Ȭ"=>"ȭ","Ȫ"=>"ȫ","Ȩ"=>"ȩ","Ȧ"=>"ȧ","Ȥ"=>"ȥ","Ȣ"=>"ȣ",
"� "=>"ƞ","Ȟ"=>"ȟ","Ȝ"=>"ȝ","Ț"=>"ț","Ș"=>"ș","Ȗ"=>"ȗ",
"Ȕ"=>"ȕ","Ȓ"=>"ȓ","Ȑ"=>"ȑ","Ȏ"=>"ȏ","Ȍ"=>"ȍ","Ȋ"=>"ȋ",
"Ȉ"=>"ȉ","Ȇ"=>"ȇ","Ȅ"=>"ȅ","Ȃ"=>"ȃ","Ȁ"=>"ȁ","Ǿ"=>"ǿ",
"Ǽ"=>"ǽ","Ǻ"=>"ǻ","Ǹ"=>"ǹ","Ƿ"=>"ƿ","Ƕ"=>"ƕ","Ǵ"=>"ǵ",
"Ǳ"=>"ǳ","Ǯ"=>"ǯ","Ǭ"=>"ǭ","Ǫ"=>"ǫ","Ǩ"=>"ǩ","Ǧ"=>"ǧ",
"Ǥ"=>"ǥ","Ǣ"=>"ǣ","� "=>"ǡ","Ǟ"=>"ǟ","Ǜ"=>"ǜ","Ǚ"=>"ǚ",
"Ǘ"=>"ǘ","Ǖ"=>"ǖ","Ǔ"=>"ǔ","Ǒ"=>"ǒ","Ǐ"=>"ǐ","Ǎ"=>"ǎ",
"Ǌ"=>"ǌ","Ǉ"=>"ǉ","Ǆ"=>"ǆ","Ƽ"=>"ƽ","Ƹ"=>"ƹ","Ʒ"=>"ʒ",
"Ƶ"=>"ƶ","Ƴ"=>"ƴ","Ʋ"=>"ʋ","Ʊ"=>"ʊ","Ư"=>"ư","Ʈ"=>"ʈ",
"Ƭ"=>"ƭ","Ʃ"=>"ʃ","Ƨ"=>"ƨ","Ʀ"=>"ʀ","Ƥ"=>"ƥ","Ƣ"=>"ƣ",
"� "=>"ơ","Ɵ"=>"ɵ","Ɲ"=>"ɲ","Ɯ"=>"ɯ","Ƙ"=>"ƙ","Ɨ"=>"ɨ",
"Ɩ"=>"ɩ","Ɣ"=>"ɣ","Ɠ"=>"� ","Ƒ"=>"ƒ","Ɛ"=>"ɛ","Ə"=>"ə",
"Ǝ"=>"ǝ","Ƌ"=>"ƌ","Ɗ"=>"ɗ","Ɖ"=>"ɖ","Ƈ"=>"ƈ","Ɔ"=>"ɔ",
"Ƅ"=>"ƅ","Ƃ"=>"ƃ","Ɓ"=>"ɓ","Ž"=>"ž","Ż"=>"ż","Ź"=>"ź",
"Ÿ"=>"ÿ","Ŷ"=>"ŷ","Ŵ"=>"ŵ","Ų"=>"ų","Ű"=>"ű","Ů"=>"ů",
"Ŭ"=>"ŭ","Ū"=>"ū","Ũ"=>"ũ","Ŧ"=>"ŧ","Ť"=>"ť","Ţ"=>"ţ",
"� "=>"š","Ş"=>"ş","Ŝ"=>"ŝ","Ś"=>"ś","Ř"=>"ř","Ŗ"=>"ŗ",
"Ŕ"=>"ŕ","Œ"=>"œ","Ő"=>"ő","Ŏ"=>"ŏ","Ō"=>"ō","Ŋ"=>"ŋ",
"Ň"=>"ň","Ņ"=>"ņ","Ń"=>"ń","Ł"=>"ł","Ŀ"=>"ŀ","Ľ"=>"ľ",
"Ļ"=>"ļ","Ĺ"=>"ĺ","Ķ"=>"ķ","Ĵ"=>"ĵ","Ĳ"=>"ĳ","İ"=>"i",
"Į"=>"į","Ĭ"=>"ĭ","Ī"=>"ī","Ĩ"=>"ĩ","Ħ"=>"ħ","Ĥ"=>"ĥ",
"Ģ"=>"ģ","� "=>"ġ","Ğ"=>"ğ","Ĝ"=>"ĝ","Ě"=>"ě","Ę"=>"ę",
"Ė"=>"ė","Ĕ"=>"ĕ","Ē"=>"ē","Đ"=>"đ","Ď"=>"ď","Č"=>"č",
"Ċ"=>"ċ","Ĉ"=>"ĉ","Ć"=>"ć","Ą"=>"ą","Ă"=>"ă","Ā"=>"ā",
"Þ"=>"þ","Ý"=>"ý","Ü"=>"ü","Û"=>"û","Ú"=>"ú","Ù"=>"ù",
"Ø"=>"ø","Ö"=>"ö","Õ"=>"õ","Ô"=>"ô","Ó"=>"ó","Ò"=>"ò",
"Ñ"=>"ñ","Ð"=>"ð","Ï"=>"ï","Î"=>"î","Í"=>"í","Ì"=>"ì",
"Ë"=>"ë","Ê"=>"ê","É"=>"é","È"=>"è","Ç"=>"ç","Æ"=>"æ",
"Å"=>"å","Ä"=>"ä","Ã"=>"ã","Â"=>"â","Á"=>"á","À"=>"� ",
"Z"=>"z","Y"=>"y","X"=>"x","W"=>"w","V"=>"v","U"=>"u",
"T"=>"t","S"=>"s","R"=>"r","Q"=>"q","P"=>"p","O"=>"o",
"N"=>"n","M"=>"m","L"=>"l","K"=>"k","J"=>"j","I"=>"i",
"H"=>"h","G"=>"g","F"=>"f","E"=>"e","D"=>"d","C"=>"c",
"B"=>"b","A"=>"a"
);


$UTF8_TABLES['strtoupper'] = array(
"ｚ"=>"Ｚ","ｙ"=>"Ｙ","ｘ"=>"Ｘ","ｗ"=>"Ｗ","ｖ"=>"Ｖ","ｕ"=>"Ｕ",
"ｔ"=>"Ｔ","ｓ"=>"Ｓ","ｒ"=>"Ｒ","ｑ"=>"Ｑ","ｐ"=>"Ｐ","ｏ"=>"Ｏ",
"ｎ"=>"Ｎ","ｍ"=>"Ｍ","ｌ"=>"Ｌ","ｋ"=>"Ｋ","ｊ"=>"Ｊ","ｉ"=>"Ｉ",
"ｈ"=>"Ｈ","ｇ"=>"Ｇ","ｆ"=>"Ｆ","ｅ"=>"Ｅ","ｄ"=>"Ｄ","ｃ"=>"Ｃ",
"ｂ"=>"Ｂ","ａ"=>"Ａ","ῳ"=>"ῼ","ῥ"=>"Ῥ","ῡ"=>"Ῡ","� "=>"Ῠ",
"ῑ"=>"Ῑ","ῐ"=>"Ῐ","ῃ"=>"ῌ","ι"=>"Ι","ᾳ"=>"ᾼ","ᾱ"=>"Ᾱ",
"ᾰ"=>"Ᾰ","ᾧ"=>"ᾯ","ᾦ"=>"ᾮ","ᾥ"=>"ᾭ","ᾤ"=>"ᾬ","ᾣ"=>"ᾫ",
"ᾢ"=>"ᾪ","ᾡ"=>"ᾩ","� "=>"ᾨ","ᾗ"=>"ᾟ","ᾖ"=>"ᾞ","ᾕ"=>"ᾝ",
"ᾔ"=>"ᾜ","ᾓ"=>"ᾛ","ᾒ"=>"ᾚ","ᾑ"=>"ᾙ","ᾐ"=>"ᾘ","ᾇ"=>"ᾏ",
"ᾆ"=>"ᾎ","ᾅ"=>"ᾍ","ᾄ"=>"ᾌ","ᾃ"=>"ᾋ","ᾂ"=>"ᾊ","ᾁ"=>"ᾉ",
"ᾀ"=>"ᾈ","ώ"=>"Ώ","ὼ"=>"Ὼ","ύ"=>"Ύ","ὺ"=>"Ὺ","ό"=>"Ό",
"ὸ"=>"Ὸ","ί"=>"Ί","ὶ"=>"Ὶ","ή"=>"Ή","ὴ"=>"Ὴ","έ"=>"Έ",
"ὲ"=>"Ὲ","ά"=>"Ά","ὰ"=>"Ὰ","ὧ"=>"Ὧ","ὦ"=>"Ὦ","ὥ"=>"Ὥ",
"ὤ"=>"Ὤ","ὣ"=>"Ὣ","ὢ"=>"Ὢ","ὡ"=>"Ὡ","� "=>"Ὠ","ὗ"=>"Ὗ",
"ὕ"=>"Ὕ","ὓ"=>"Ὓ","ὑ"=>"Ὑ","ὅ"=>"Ὅ","ὄ"=>"Ὄ","ὃ"=>"Ὃ",
"ὂ"=>"Ὂ","ὁ"=>"Ὁ","ὀ"=>"Ὀ","ἷ"=>"Ἷ","ἶ"=>"Ἶ","ἵ"=>"Ἵ",
"ἴ"=>"Ἴ","ἳ"=>"Ἳ","ἲ"=>"Ἲ","ἱ"=>"Ἱ","ἰ"=>"Ἰ","ἧ"=>"Ἧ",
"ἦ"=>"Ἦ","ἥ"=>"Ἥ","ἤ"=>"Ἤ","ἣ"=>"Ἣ","ἢ"=>"Ἢ","ἡ"=>"Ἡ",
"� "=>"Ἠ","ἕ"=>"Ἕ","ἔ"=>"Ἔ","ἓ"=>"Ἓ","ἒ"=>"Ἒ","ἑ"=>"Ἑ",
"ἐ"=>"Ἐ","ἇ"=>"Ἇ","ἆ"=>"Ἆ","ἅ"=>"Ἅ","ἄ"=>"Ἄ","ἃ"=>"Ἃ",
"ἂ"=>"Ἂ","ἁ"=>"Ἁ","ἀ"=>"Ἀ","ỹ"=>"Ỹ","ỷ"=>"Ỷ","ỵ"=>"Ỵ",
"ỳ"=>"Ỳ","ự"=>"Ự","ữ"=>"Ữ","ử"=>"Ử","ừ"=>"Ừ","ứ"=>"Ứ",
"ủ"=>"Ủ","ụ"=>"Ụ","ợ"=>"Ợ","ỡ"=>"� ","ở"=>"Ở","ờ"=>"Ờ",
"ớ"=>"Ớ","ộ"=>"Ộ","ỗ"=>"Ỗ","ổ"=>"Ổ","ồ"=>"Ồ","ố"=>"Ố",
"ỏ"=>"Ỏ","ọ"=>"Ọ","ị"=>"Ị","ỉ"=>"Ỉ","ệ"=>"Ệ","ễ"=>"Ễ",
"ể"=>"Ể","ề"=>"Ề","ế"=>"Ế","ẽ"=>"Ẽ","ẻ"=>"Ẻ","ẹ"=>"Ẹ",
"ặ"=>"Ặ","ẵ"=>"Ẵ","ẳ"=>"Ẳ","ằ"=>"Ằ","ắ"=>"Ắ","ậ"=>"Ậ",
"ẫ"=>"Ẫ","ẩ"=>"Ẩ","ầ"=>"Ầ","ấ"=>"Ấ","ả"=>"Ả","ạ"=>"� ",
"ẛ"=>"� ","ẕ"=>"Ẕ","ẓ"=>"Ẓ","ẑ"=>"Ẑ","ẏ"=>"Ẏ","ẍ"=>"Ẍ",
"ẋ"=>"Ẋ","ẉ"=>"Ẉ","ẇ"=>"Ẇ","ẅ"=>"Ẅ","ẃ"=>"Ẃ","ẁ"=>"Ẁ",
"ṿ"=>"Ṿ","ṽ"=>"Ṽ","ṻ"=>"Ṻ","ṹ"=>"Ṹ","ṷ"=>"Ṷ","ṵ"=>"Ṵ",
"ṳ"=>"Ṳ","ṱ"=>"Ṱ","ṯ"=>"Ṯ","ṭ"=>"Ṭ","ṫ"=>"Ṫ","ṩ"=>"Ṩ",
"ṧ"=>"Ṧ","ṥ"=>"Ṥ","ṣ"=>"Ṣ","ṡ"=>"� ","ṟ"=>"Ṟ","ṝ"=>"Ṝ",
"ṛ"=>"Ṛ","ṙ"=>"Ṙ","ṗ"=>"Ṗ","ṕ"=>"Ṕ","ṓ"=>"Ṓ","ṑ"=>"Ṑ",
"ṏ"=>"Ṏ","ṍ"=>"Ṍ","ṋ"=>"Ṋ","ṉ"=>"Ṉ","ṇ"=>"Ṇ","ṅ"=>"Ṅ",
"ṃ"=>"Ṃ","ṁ"=>"Ṁ","ḿ"=>"Ḿ","ḽ"=>"Ḽ","ḻ"=>"Ḻ","ḹ"=>"Ḹ",
"ḷ"=>"Ḷ","ḵ"=>"Ḵ","ḳ"=>"Ḳ","ḱ"=>"Ḱ","ḯ"=>"Ḯ","ḭ"=>"Ḭ",
"ḫ"=>"Ḫ","ḩ"=>"Ḩ","ḧ"=>"Ḧ","ḥ"=>"Ḥ","ḣ"=>"Ḣ","ḡ"=>"� ",
"ḟ"=>"Ḟ","ḝ"=>"Ḝ","ḛ"=>"Ḛ","ḙ"=>"Ḙ","ḗ"=>"Ḗ","ḕ"=>"Ḕ",
"ḓ"=>"Ḓ","ḑ"=>"Ḑ","ḏ"=>"Ḏ","ḍ"=>"Ḍ","ḋ"=>"Ḋ","ḉ"=>"Ḉ",
"ḇ"=>"Ḇ","ḅ"=>"Ḅ","ḃ"=>"Ḃ","ḁ"=>"Ḁ","ֆ"=>"Ֆ","օ"=>"Օ",
"ք"=>"Ք","փ"=>"Փ","ւ"=>"Ւ","ց"=>"Ց","ր"=>"Ր","տ"=>"Տ",
"վ"=>"Վ","ս"=>"Ս","ռ"=>"Ռ","ջ"=>"Ջ","պ"=>"Պ","չ"=>"Չ",
"ո"=>"Ո","շ"=>"Շ","ն"=>"Ն","յ"=>"Յ","մ"=>"Մ","ճ"=>"Ճ",
"ղ"=>"Ղ","ձ"=>"Ձ","հ"=>"Հ","կ"=>"Կ","ծ"=>"Ծ","խ"=>"Խ",
"լ"=>"Լ","ի"=>"Ի","ժ"=>"Ժ","թ"=>"Թ","ը"=>"Ը","է"=>"Է",
"զ"=>"Զ","ե"=>"Ե","դ"=>"Դ","գ"=>"Գ","բ"=>"Բ","ա"=>"Ա",
"ԏ"=>"Ԏ","ԍ"=>"Ԍ","ԋ"=>"Ԋ","ԉ"=>"Ԉ","ԇ"=>"Ԇ","ԅ"=>"Ԅ",
"ԃ"=>"Ԃ","ԁ"=>"Ԁ","ӹ"=>"Ӹ","ӵ"=>"Ӵ","ӳ"=>"Ӳ","ӱ"=>"Ӱ",
"ӯ"=>"Ӯ","ӭ"=>"Ӭ","ӫ"=>"Ӫ","ө"=>"Ө","ӧ"=>"Ӧ","ӥ"=>"Ӥ",
"ӣ"=>"Ӣ","ӡ"=>"� ","ӟ"=>"Ӟ","ӝ"=>"Ӝ","ӛ"=>"Ӛ","ә"=>"Ә",
"ӗ"=>"Ӗ","ӕ"=>"Ӕ","ӓ"=>"Ӓ","ӑ"=>"Ӑ","ӎ"=>"Ӎ","ӌ"=>"Ӌ",
"ӊ"=>"Ӊ","ӈ"=>"Ӈ","ӆ"=>"Ӆ","ӄ"=>"Ӄ","ӂ"=>"Ӂ","ҿ"=>"Ҿ",
"ҽ"=>"Ҽ","һ"=>"Һ","ҹ"=>"Ҹ","ҷ"=>"Ҷ","ҵ"=>"Ҵ","ҳ"=>"Ҳ",
"ұ"=>"Ұ","ү"=>"Ү","ҭ"=>"Ҭ","ҫ"=>"Ҫ","ҩ"=>"Ҩ","ҧ"=>"Ҧ",
"ҥ"=>"Ҥ","ң"=>"Ң","ҡ"=>"� ","ҟ"=>"Ҟ","ҝ"=>"Ҝ","қ"=>"Қ",
"ҙ"=>"Ҙ","җ"=>"Җ","ҕ"=>"Ҕ","ғ"=>"Ғ","ґ"=>"Ґ","ҏ"=>"Ҏ",
"ҍ"=>"Ҍ","ҋ"=>"Ҋ","ҁ"=>"Ҁ","ѿ"=>"Ѿ","ѽ"=>"Ѽ","ѻ"=>"Ѻ",
"ѹ"=>"Ѹ","ѷ"=>"Ѷ","ѵ"=>"Ѵ","ѳ"=>"Ѳ","ѱ"=>"Ѱ","ѯ"=>"Ѯ",
"ѭ"=>"Ѭ","ѫ"=>"Ѫ","ѩ"=>"Ѩ","ѧ"=>"Ѧ","ѥ"=>"Ѥ","ѣ"=>"Ѣ",
"ѡ"=>"� ","џ"=>"Џ","ў"=>"Ў","ѝ"=>"Ѝ","ќ"=>"Ќ","ћ"=>"Ћ",
"њ"=>"Њ","љ"=>"Љ","ј"=>"Ј","ї"=>"Ї","і"=>"І","ѕ"=>"Ѕ",
"є"=>"Є","ѓ"=>"Ѓ","ђ"=>"Ђ","ё"=>"Ё","ѐ"=>"Ѐ","я"=>"Я",
"ю"=>"Ю","э"=>"Э","ь"=>"Ь","ы"=>"Ы","ъ"=>"Ъ","щ"=>"Щ",
"ш"=>"Ш","ч"=>"Ч","ц"=>"Ц","х"=>"Х","ф"=>"Ф","у"=>"У",
"т"=>"Т","с"=>"С","р"=>"� ","п"=>"П","о"=>"О","н"=>"Н",
"м"=>"М","л"=>"Л","к"=>"К","й"=>"Й","и"=>"И","з"=>"З",
"ж"=>"Ж","е"=>"Е","д"=>"Д","г"=>"Г","в"=>"В","б"=>"Б",
"а"=>"А","ϵ"=>"Ε","ϲ"=>"Σ","ϱ"=>"Ρ","ϰ"=>"Κ","ϯ"=>"Ϯ",
"ϭ"=>"Ϭ","ϫ"=>"Ϫ","ϩ"=>"Ϩ","ϧ"=>"Ϧ","ϥ"=>"Ϥ","ϣ"=>"Ϣ",
"ϡ"=>"� ","ϟ"=>"Ϟ","ϝ"=>"Ϝ","ϛ"=>"Ϛ","ϙ"=>"Ϙ","ϖ"=>"� ",
"ϕ"=>"Φ","ϑ"=>"Θ","ϐ"=>"Β","ώ"=>"Ώ","ύ"=>"Ύ","ό"=>"Ό",
"ϋ"=>"Ϋ","ϊ"=>"Ϊ","ω"=>"Ω","ψ"=>"Ψ","χ"=>"Χ","φ"=>"Φ",
"υ"=>"Υ","τ"=>"Τ","σ"=>"Σ","ς"=>"Σ","ρ"=>"Ρ","π"=>"� ",
"ο"=>"Ο","ξ"=>"Ξ","ν"=>"Ν","μ"=>"Μ","λ"=>"Λ","κ"=>"Κ",
"ι"=>"Ι","θ"=>"Θ","η"=>"Η","ζ"=>"Ζ","ε"=>"Ε","δ"=>"Δ",
"γ"=>"Γ","β"=>"Β","α"=>"Α","ί"=>"Ί","ή"=>"Ή","έ"=>"Έ",
"ά"=>"Ά","ʒ"=>"Ʒ","ʋ"=>"Ʋ","ʊ"=>"Ʊ","ʈ"=>"Ʈ","ʃ"=>"Ʃ",
"ʀ"=>"Ʀ","ɵ"=>"Ɵ","ɲ"=>"Ɲ","ɯ"=>"Ɯ","ɩ"=>"Ɩ","ɨ"=>"Ɨ",
"ɣ"=>"Ɣ","� "=>"Ɠ","ɛ"=>"Ɛ","ə"=>"Ə","ɗ"=>"Ɗ","ɖ"=>"Ɖ",
"ɔ"=>"Ɔ","ɓ"=>"Ɓ","ȳ"=>"Ȳ","ȱ"=>"Ȱ","ȯ"=>"Ȯ","ȭ"=>"Ȭ",
"ȫ"=>"Ȫ","ȩ"=>"Ȩ","ȧ"=>"Ȧ","ȥ"=>"Ȥ","ȣ"=>"Ȣ","ȟ"=>"Ȟ",
"ȝ"=>"Ȝ","ț"=>"Ț","ș"=>"Ș","ȗ"=>"Ȗ","ȕ"=>"Ȕ","ȓ"=>"Ȓ",
"ȑ"=>"Ȑ","ȏ"=>"Ȏ","ȍ"=>"Ȍ","ȋ"=>"Ȋ","ȉ"=>"Ȉ","ȇ"=>"Ȇ",
"ȅ"=>"Ȅ","ȃ"=>"Ȃ","ȁ"=>"Ȁ","ǿ"=>"Ǿ","ǽ"=>"Ǽ","ǻ"=>"Ǻ",
"ǹ"=>"Ǹ","ǵ"=>"Ǵ","ǳ"=>"ǲ","ǯ"=>"Ǯ","ǭ"=>"Ǭ","ǫ"=>"Ǫ",
"ǩ"=>"Ǩ","ǧ"=>"Ǧ","ǥ"=>"Ǥ","ǣ"=>"Ǣ","ǡ"=>"� ","ǟ"=>"Ǟ",
"ǝ"=>"Ǝ","ǜ"=>"Ǜ","ǚ"=>"Ǚ","ǘ"=>"Ǘ","ǖ"=>"Ǖ","ǔ"=>"Ǔ",
"ǒ"=>"Ǒ","ǐ"=>"Ǐ","ǎ"=>"Ǎ","ǌ"=>"ǋ","ǉ"=>"ǈ","ǆ"=>"ǅ",
"ƿ"=>"Ƿ","ƽ"=>"Ƽ","ƹ"=>"Ƹ","ƶ"=>"Ƶ","ƴ"=>"Ƴ","ư"=>"Ư",
"ƭ"=>"Ƭ","ƨ"=>"Ƨ","ƥ"=>"Ƥ","ƣ"=>"Ƣ","ơ"=>"� ","ƞ"=>"� ",
"ƙ"=>"Ƙ","ƕ"=>"Ƕ","ƒ"=>"Ƒ","ƌ"=>"Ƌ","ƈ"=>"Ƈ","ƅ"=>"Ƅ",
"ƃ"=>"Ƃ","ſ"=>"S","ž"=>"Ž","ż"=>"Ż","ź"=>"Ź","ŷ"=>"Ŷ",
"ŵ"=>"Ŵ","ų"=>"Ų","ű"=>"Ű","ů"=>"Ů","ŭ"=>"Ŭ","ū"=>"Ū",
"ũ"=>"Ũ","ŧ"=>"Ŧ","ť"=>"Ť","ţ"=>"Ţ","š"=>"� ","ş"=>"Ş",
"ŝ"=>"Ŝ","ś"=>"Ś","ř"=>"Ř","ŗ"=>"Ŗ","ŕ"=>"Ŕ","œ"=>"Œ",
"ő"=>"Ő","ŏ"=>"Ŏ","ō"=>"Ō","ŋ"=>"Ŋ","ň"=>"Ň","ņ"=>"Ņ",
"ń"=>"Ń","ł"=>"Ł","ŀ"=>"Ŀ","ľ"=>"Ľ","ļ"=>"Ļ","ĺ"=>"Ĺ",
"ķ"=>"Ķ","ĵ"=>"Ĵ","ĳ"=>"Ĳ","ı"=>"I","į"=>"Į","ĭ"=>"Ĭ",
"ī"=>"Ī","ĩ"=>"Ĩ","ħ"=>"Ħ","ĥ"=>"Ĥ","ģ"=>"Ģ","ġ"=>"� ",
"ğ"=>"Ğ","ĝ"=>"Ĝ","ě"=>"Ě","ę"=>"Ę","ė"=>"Ė","ĕ"=>"Ĕ",
"ē"=>"Ē","đ"=>"Đ","ď"=>"Ď","č"=>"Č","ċ"=>"Ċ","ĉ"=>"Ĉ",
"ć"=>"Ć","ą"=>"Ą","ă"=>"Ă","ā"=>"Ā","ÿ"=>"Ÿ","þ"=>"Þ",
"ý"=>"Ý","ü"=>"Ü","û"=>"Û","ú"=>"Ú","ù"=>"Ù","ø"=>"Ø",
"ö"=>"Ö","õ"=>"Õ","ô"=>"Ô","ó"=>"Ó","ò"=>"Ò","ñ"=>"Ñ",
"ð"=>"Ð","ï"=>"Ï","î"=>"Î","í"=>"Í","ì"=>"Ì","ë"=>"Ë",
"ê"=>"Ê","é"=>"É","è"=>"È","ç"=>"Ç","æ"=>"Æ","å"=>"Å",
"ä"=>"Ä","ã"=>"Ã","â"=>"Â","á"=>"Á","� "=>"À","µ"=>"Μ",
"z"=>"Z","y"=>"Y","x"=>"X","w"=>"W","v"=>"V","u"=>"U",
"t"=>"T","s"=>"S","r"=>"R","q"=>"Q","p"=>"P","o"=>"O",
"n"=>"N","m"=>"M","l"=>"L","k"=>"K","j"=>"J","i"=>"I",
"h"=>"H","g"=>"G","f"=>"F","e"=>"E","d"=>"D","c"=>"C",
"b"=>"B","a"=>"A"
);

/**
 * Multi-byte string support
 */
if (!function_exists('mb_internal_encoding')) {
	function mb_strtolower($str) {
  	return utf8::strtolower($str);
	}
	function mb_strtoupper($str) {
  	return utf8::strtoupper($str);
	}
	function mb_strlen($str) {
  	return utf8::strlen($str);
	}
	function mb_substr($str, $start, $length = NULL) {
  	return utf8::substr($str, $start, $length);
	}
	function mb_strrpos($haystack, $needle) {
  	return utf8::strrpos($haystack, $needle);
	}
	function mb_strpos($haystack, $needle, $offset = 0) {
  	return utf8::strpos($haystack, $needle, $offset);
	}
	function mb_substr_count($haystack, $needle) {
  	return utf8::substr_count($haystack, $needle);
	}

}

?>
