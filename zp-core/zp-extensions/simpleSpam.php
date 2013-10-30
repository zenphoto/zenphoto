<?php

/**
 * This is a "simple" SPAM filter.
 * It uses a word black list and checks for excessive URLs
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage spam
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Simple SPAM filter.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (isset($_zp_spamFilter) && !extensionEnabled('simpleSpam')) ? sprintf(gettext('Only one SPAM handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_spamFilter->name) : '';

$option_interface = 'zpSimpleSpam';

if ($plugin_disable) {
	enableExtension('simpleSpam', 0);
} else {
	$_zp_spamFilter = new zpSimpleSpam();
}

/**
 * This implements the standard SpamFilter class for the Simple spam filter.
 *
 */
class zpSimpleSpam {

	var $name = 'simpleSpam';
	var $wordsToDieOn = array('cialis', 'ebony', 'nude', 'porn', 'porno', 'pussy', 'upskirt', 'ringtones', 'phentermine', 'viagra', 'levitra'); /* the word black list */
	var $patternsToDieOn = array('\[url=.*\]');
	var $excessiveURLCount = 5;

	/**
	 * The SpamFilter class instantiation function.
	 *
	 * @return SpamFilter
	 */
	function __construct() {
		setOptionDefault('Words_to_die_on', implode(',', $this->wordsToDieOn));
		setOptionDefault('Patterns_to_die_on', implode(' ', $this->patternsToDieOn));
		setOptionDefault('Excessive_URL_count', $this->excessiveURLCount);
		setOptionDefault('Forgiving', 0);
		setOptionDefault('Banned_IP_list', serialize(array()));
	}

	function displayName() {
		return $this->name;
	}

	/**
	 * The admin options interface
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Words to die on')		 => array('key'					 => 'Words_to_die_on', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => false,
										'desc'				 => gettext('SPAM blacklist words (separate with commas)')),
						gettext('Patterns to die on')	 => array('key'					 => 'Patterns_to_die_on', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => false,
										'desc'				 => gettext('SPAM blacklist <a href="http://en.wikipedia.org/wiki/Regular_expression">regular expressions</a> (separate with spaces)')),
						gettext('Excessive URL count') => array('key' => 'Excessive_URL_count', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Message is considered SPAM if there are more than this many URLs in it')),
						gettext('Banned IPs')					 => array('key'					 => 'Banned_IP_list', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => false,
										'desc'				 => gettext('Prevent posts from this list of IP addresses')),
						gettext('Forgiving')					 => array('key' => 'Forgiving', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Mark suspected SPAM for moderation rather than as SPAM')));
	}

	/**
	 * Handles custom formatting of options for Admin
	 *
	 * @param string $option the option name of the option to be processed
	 * @param mixed $currentValue the current value of the option (the "before" value)
	 */
	function handleOption($option, $currentValue) {

	}

	/**
	 * The function for processing a message to see if it might be SPAM
	 *       returns:
	 *         0 if the message is SPAM
	 *         1 if the message might be SPAM (it will be marked for moderation)
	 *         2 if the message is not SPAM
	 *
	 * @param string $author Author field from the posting
	 * @param string $email Email field from the posting
	 * @param string $website Website field from the posting
	 * @param string $body The text of the comment
	 * @param object $receiver The object on which the post was made
	 * @param string $ip the IP address of the comment poster
	 *
	 * @return int
	 */
	function filterMessage($author, $email, $website, $body, $receiver, $ip) {
		if (strpos(getOption('Banned_IP_list'), $ip) !== false) {
			return 0;
		}
		$forgive = getOption('Forgiving');
		$list = getOption('Words_to_die_on');
		$list = strtolower($list);
		$this->wordsToDieOn = explode(',', $list);
		$list = getOption('Patterns_to_die_on');
		$list = strtolower($list);
		$this->patternsToDieOn = explode(' ', $list);
		$this->excessiveURLCount = getOption('Excessive_URL_count');
		$die = 2; // good comment until proven bad
		foreach (array($author, $email, $website, $body) as $check) {
			if ($check) {
				if (($num = substr_count($check, 'http://')) >= $this->excessiveURLCount) { // too many links
					$die = $forgive;
				} else {
					if ($pattern = $this->hasSpamPattern($check)) {
						$die = $forgive;
					} else {
						if ($spamWords = $this->hasSpamWords($check)) {
							$die = $forgive;
						}
					}
				}
			}
		}
		return $die;
	}

	/**
	 * Tests to see if the text contains any of the SPAM trigger patterns
	 *
	 * @param string $text The message to be parsed
	 * @return bool
	 */
	function hasSpamPattern($text) {
		$patterns = $this->patternsToDieOn;
		foreach ($patterns as $pattern) {
			if (preg_match('|' . preg_quote(trim($pattern), '/') . '|i', $text)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Tests to see if the text contains any of the list of SPAM trigger words
	 *
	 * @param string $text The text of the message to be examined.
	 * @return bool
	 */
	function hasSpamWords($text) {
		$words = $this->getWords($text);
		$blacklist = $this->wordsToDieOn;
		$intersect = array_intersect($blacklist, $words);
		return $intersect;
	}

	function getWords($text, $notUnique = false) {
		if ($notUnique) {
			return preg_split("/[\W]+/", strtolower(strip_tags($text)));
		} else {
			return array_unique(preg_split("/[\W]+/", strtolower(strip_tags($text))));
		}
	}

}

?>
