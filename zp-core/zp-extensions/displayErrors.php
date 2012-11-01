<?php
/**
 * This plugin will set the PHP error reporting level and the PHP ini setting <i>display_errors</i>
 *
 * The plugin loads at the beginning of the <i>Class</i> plugins, so this setting is not active until
 * the basic Zenphoto environment is established. It is useful for debugging themes as the errors can
 * be set to show in the WEB output obviating the need to browse the PHP error logs.
 *
 * With the plugin enabled the PHP <i>ini</i> setting <var>display_errors</var> is set to cause errors to
 * be displayed with the WEB output. Error reporting level will be set as per the options. The recommended
 * setting is for all error reporting to be selected as this provides the most useful debugging information.
 *
 * <b>Note:</b> Enabling this plugin will affect both the front-end and the admin pages of Zenphoto.
 *
 */

$plugin_is_filter = 99|CLASS_PLUGIN;
$plugin_description = gettext('Allows dynamic setting of PHP error displays.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'displayErrors';



error_reporting(getOption('display_errorsReporting'));
@ini_set('display_errors', 1);

class displayErrors {

	function __construct() {
		setOptionDefault('display_errorsReporting', E_ALL | E_STRICT);
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Error reporting') => array ('key' => 'display_errorsReporting', 'type' => OPTION_TYPE_CUSTOM,
				'desc' => gettext('Error Reporting selections.'))
		);
	}

	function handleOption($option, $currentValue) {
		$reporting = getOption('display_errorsReporting');
		$current = array();
		$reports = array(
				'E_ERROR'=>E_ERROR,
				'E_WARNING'=>E_WARNING,
				'E_PARSE'=>E_PARSE,
				'E_NOTICE'=>E_NOTICE,
				'E_CORE_ERROR'=>E_CORE_ERROR,
				'E_CORE_WARNING'=>E_CORE_WARNING,
				'E_COMPILE_ERROR'=>E_COMPILE_ERROR,
				'E_COMPILE_WARNING'=>E_COMPILE_WARNING,
				'E_USER_ERROR'=>E_USER_ERROR,
				'E_USER_NOTICE'=>E_USER_NOTICE,
				'E_USER_WARNING'=>E_USER_WARNING,
				'E_STRICT'=>E_STRICT
		);
		if (version_compare(PHP_VERSION,'5.2.0') == 1) {
			$reports['E_RECOVERABLE_ERROR'] = E_RECOVERABLE_ERROR;
		}
		if (version_compare(PHP_VERSION,'5.3.0') == 1) {
			$reports['E_DEPRECATED'] = E_DEPRECATED;
			$reports['E_USER_DEPRECATED'] = E_USER_DEPRECATED;
		}
		ksort($reports);
		$text = array();
		foreach ($reports as $name=>$er) {
			if ($reporting & $er) {
				$text[] = $name;
			}
		}
		foreach ($reports as $display=>$checkbox) {
			$v = in_array($display, $text);
			?>
			<label class="checkboxlabel"> <input type="checkbox"
				id="<?php echo $checkbox; ?>" name="displayErrors_<?php echo $checkbox; ?>" value="1"
				<?php echo checked('1', $v); ?> /> <?php echo $display; ?>
			</label>
			<?php
		}
	}

	function handleOptionSave($themename,$themealbum) {
		$reporting = 0;
		foreach ($_POST as $key=>$value) {
			if ($value) {
				preg_match('/^displayErrors_(.*)/', $key, $matches);
				if (!empty($matches)) {
					$reporting = $reporting | $matches[1];
				}
			}
		}
		setOption('display_errorsReporting', $reporting);
	}

}



?>