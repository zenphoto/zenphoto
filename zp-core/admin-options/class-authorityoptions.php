<?php
/**
 * Option class for authority options
 * 
 * @since 1.7 Separated from authority class
 * 
 * @package zpcore\classes\authority
 */
class authorityOptions {

	/**
	 * class instantiation function
	 */
	function __construct() {
		setOptionDefault('admin_lastvisit_timeframe', 600);
		setOptionDefault('admin_lastvisit', true);
	}

	/**
	 * Declares options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$encodings = Authority::$hashList;
		unset($encodings['pbkdf2*']); // don't use this one any more
		if (!function_exists('hash')) {
			unset($encodings['pbkdf2']);
		}
		return array(
				gettext('Primary user album: Edit rights default') => array(
						'key' => 'user_album_edit_default',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check if you want <em>edit rights</em> automatically assigned when a user <em>primary album</em> is created.')),
				gettext('Primary user album: Keep on user removal') => array(
						'key' => 'user_album_keep_on_userremoval',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check if you want the user <em>primary album</em> not to be deleted if the user is removed.')),
				gettext('Minimum password strength') => array(
						'key' => 'password_strength',
						'type' => OPTION_TYPE_CUSTOM,
						'desc' => sprintf(gettext('Users must provide passwords a strength of at least %s. The repeat password field will be disabled until this floor is met.'), '<span id="password_strength_display">' . getOption('password_strength') . '</span>')),
				gettext('Password hash algorithm') => array(
						'key' => 'strong_hash',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $encodings,
						'desc' => sprintf(gettext('The hashing algorithm used by Zenphoto. In order of robustness the choices are %s'), '<code>' . implode('</code> > <code>', array_flip($encodings)) . '</code>')),
				gettext('User last visit - store') => array(
						'key' => 'admin_lastvisit',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Enable if you like to store visits of logged-in users in the database.')),
				gettext('User last visit - time frame') => array(
						'key' => 'admin_lastvisit_timeframe',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Time in seconds before the last visit of logged-in users is updated in the database. Default is 600 seconds (10 minutes).'))
		);
	}

	/**
	 * Custom option handlerå
	 */
	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'password_strength':
				?>
				<input type="hidden" size="3" id="password_strength" name="password_strength" value="<?php echo getOption('password_strength'); ?>" />
				<script>
					function sliderColor(strength) {
						var url = 'url(<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/strengths/strength' + strength + '.png)';
						$('#slider-password_strength').css('background-image', url);
					}
					$(function () {
						$("#slider-password_strength").slider({
				<?php $v = getOption('password_strength'); ?>
							startValue: <?php echo $v; ?>,
							value: <?php echo $v; ?>,
							min: 1,
							max: 30,
							slide: function (event, ui) {
								$("#password_strength").val(ui.value);
								$('#password_strength_display').html(ui.value);
								sliderColor(ui.value);
							}
						});
						var strength = $("#slider-password_strength").slider("value");
						$("#password_strength").val(strength);
						$('#password_strength_display').html(strength);
						sliderColor(strength);
					});
				</script>
				<div id="slider-password_strength"></div>
				<?php
				break;
		}
	}
}
