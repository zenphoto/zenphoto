<?php
/**
 * Provides a donation button to solicit contributions to Zenphoto
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN;
$plugin_description = gettext('Adds a Zenphoto donations block to the admin overview page.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_overview', 'zenphotoDonate::donate');

setOptionDefault('zp_plugin_zenphotoDonate', $plugin_is_filter);

class zenphotoDonate {

	static function donate() {
		?>
		<div class="box overview-utility">
			<h2 class="h2_bordered"><?php echo gettext("Like using Zenphoto? Donate!"); ?></h2>
			<br />
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="tharward@berkeley.edu">
				<input type="hidden" name="item_name" value="Zenphoto">
				<input type="hidden" name="no_note" value="1"> <input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			<br class="clearall" />
			<div style="padding-left:10px;">
				<p><?php echo gettext('Your support helps pay for the Zenphoto site server and helps development of Zenphoto. Thank you!'); ?></p>
			</div>
		</div>
		<?php
	}

}
?>