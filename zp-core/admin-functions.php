<?php

/**
 * Admin functions loader
 * @package zpcore\admin\functions
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions/functions.php');

define('TEXTAREA_COLUMNS', 50);
define('TEXT_INPUT_SIZE', 48);
define('TEXTAREA_COLUMNS_SHORT', 32);
define('TEXT_INPUT_SIZE_SHORT', 30);
if (!defined('EDITOR_SANITIZE_LEVEL')) {
	define('EDITOR_SANITIZE_LEVEL', 1);
}
define('CUSTOM_OPTION_PREFIX', '_ZP_CUSTOM_');
/**
 * Generates the HTML for custom options (e.g. theme options, plugin options, etc.)
 * Note: option names may not contain '.', '+', nor '%' as PHP POST handling will replace
 * these with an underscore.
 *
 * @param object $optionHandler the object to handle custom options
 * @param string $indent used to indent the option for nested options
 * @param object $album if not null, the album to which the option belongs
 * @param bool $hide set to true to hide the output (used by the plugin-options folding
 * $paran array $supportedOptions pass these in if you already have them
 * @param bool $theme set true if dealing with theme options
 * @param string $initial initila show/hide state
 *
 * Custom options:
 *    OPTION_TYPE_TEXTBOX:          A textbox
 *    OPTION_TYPE_PASSWORD:         A passowrd textbox
 *    OPTION_TYPE_CLEARTEXT:     	  A textbox, but no sanitization on save
 *    OPTION_TYPE_CHECKBOX:         A checkbox
 *    OPTION_TYPE_CUSTOM:           Handled by $optionHandler->handleOption()
 *    OPTION_TYPE_TEXTAREA:         A textarea
 *    OPTION_TYPE_RICHTEXT:         A textarea with WYSIWYG editor attached
 *    OPTION_TYPE_RADIO:            Radio buttons (button names are in the 'buttons' index of the supported options array)
 *    OPTION_TYPE_SELECTOR:         Selector (selection list is in the 'selections' index of the supported options array
 *                                  null_selection contains the text for the empty selection. If not present there
 *                                  will be no empty selection)
 *    OPTION_TYPE_CHECKBOX_ARRAY:   Checkbox array (checkbox list is in the 'checkboxes' index of the supported options array.)
 *    OPTION_TYPE_CHECKBOX_UL:      Checkbox UL (checkbox list is in the 'checkboxes' index of the supported options array.)
 *    OPTION_TYPE_COLOR_PICKER:     Color picker
 *    OPTION_TYPE_NOTE:             Places a note in the options area. The note will span all three columns
 *
 *    Types 0 and 5 support multi-lingual strings.
 */
define('OPTION_TYPE_TEXTBOX', 0);
define('OPTION_TYPE_CHECKBOX', 1);
define('OPTION_TYPE_CUSTOM', 2);
define('OPTION_TYPE_TEXTAREA', 3);
define('OPTION_TYPE_RADIO', 4);
define('OPTION_TYPE_SELECTOR', 5);
define('OPTION_TYPE_CHECKBOX_ARRAY', 6);
define('OPTION_TYPE_CHECKBOX_UL', 7);
define('OPTION_TYPE_COLOR_PICKER', 8);
define('OPTION_TYPE_CLEARTEXT', 9);
define('OPTION_TYPE_NOTE', 10);
define('OPTION_TYPE_PASSWORD', 11);
define('OPTION_TYPE_RICHTEXT', 12);

require_once dirname(__FILE__) . '/classes/class-update.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-options.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-layout.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-misc.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-logs.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-tags.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-messages.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-general.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-album.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-image.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-themes.php';