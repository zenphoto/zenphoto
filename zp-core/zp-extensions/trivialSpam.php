<?php

/**
 * This is a shell plugin for SPAM filtering. It does almost nothing, but serves as the template
 * for more robust SPAM filters.
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage spam
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Trivial SPAM filter.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (isset($_zp_spamFilter) && !extensionEnabled('trivialSpam')) ? sprintf(gettext('Only one SPAM handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_spamFilter->name) : '';

$option_interface = 'zpTrivialSpam';

if ($plugin_disable) {
	enableExtension('trivialSpam', 0);
} else {
	setOptionDefault('zp_plugin_trivialSpam', $plugin_is_filter);
	$_zp_spamFilter = new zpTrivialSpam();
}

/**
 * This implements the standard SpamFilter class for the none spam filter.
 *
 * Note that this filter will always pass comments from users with "manage" rights
 * on the commented object.
 *
 */
class zpTrivialSpam {

	var $name = 'trivialSpam';

	/**
	 * The SpamFilter class instantiation function.
	 *
	 * @return SpamFilter
	 */
	function __construct() {
		setOptionDefault('spamFilter_none_action', 'pass');
	}

	function displayName() {
		return $this->name;
	}

	/**
	 * The admin options interface
	 * called from admin Options tab
	 *  returns an array of the option names the theme supports
	 *  the array is indexed by the option name. The value for each option is an array:
	 *          'type' => 0 says for admin to use a standard textbox for the option
	 *          'type' => 1 says for admin to use a standard checkbox for the option
	 *          'type' => OPTION_TYPE_CUSTOM will cause admin to call handleOption to generate the HTML for the option
	 *          'desc' => text to be displayed for the option description.
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Action') => array('key'				 => 'spamFilter_none_action', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('pass')			 => 'pass', gettext('moderate')	 => 'moderate', gettext('reject')		 => 'reject'),
										'desc'			 => gettext('This action will be taken for all messages.')));
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
	 * @param string $receiver The object on which the post was made
	 * @param string $ip the IP address of the comment poster
	 *
	 * @return int
	 */
	function filterMessage($author, $email, $website, $body, $receiver, $ip) {
		if (zp_loggedin($receiver->manage_rights) || $receiver->isMyItem($receiver->manage_some_rights)) { //	trust "managers"
			return 2;
		}
		$strategy = getOption('spamFilter_none_action');
		switch ($strategy) {
			case 'reject': return 0;
			case 'moderate': return 1;
		}
		return 2;
	}

}

?>
