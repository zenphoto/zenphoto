<?php
/**
 * Provides a donation button to solicit contributions to Zenphoto
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\zenphotodonate
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN;
$plugin_description = gettext('Adds a Zenphoto donations block to the admin overview page.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_category = gettext('Admin');

zp_register_filter('admin_overview', 'zenphotoDonate::donate');

class zenphotoDonate {

	static function donate() {
		?>
		<div class="box overview-utility">
			<h2 class="h2_bordered"><?php echo gettext("Like using Zenphoto? Donate!"); ?></h2>
			<br />
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="FZFKU5UYSGEYE">
				<input type="submit" name="submit" alt="PayPal - The safer, easier way to pay online!" value="Donate via PayPal"
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