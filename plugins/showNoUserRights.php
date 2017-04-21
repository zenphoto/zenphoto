<?php

/**
 * Hide the output of user rights and other info if a user does <b>NOT</b> have <var>ADMIN_RIGHTS</var>.
 *
 * To change what is hidden, comment lines you do want to display.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Hide the output of user rights and other info if user does NOT have ADMIN_RIGHTS.");
$plugin_author = "Stephen Billard (sbillard), Fred Sondaar (fretzl)";

zp_register_filter('admin_head', 'showNoUserRights::customDisplayRights');
zp_register_filter('plugin_tabs', 'showNoUserRights::tab');

class showNoUserRights {

	static function customDisplayRights() {
		global $_zp_admin_tab, $_zp_admin_subtab;
		if (!zp_loggedin(ADMIN_RIGHTS) && $_zp_admin_tab == 'admin' && $_zp_admin_subtab == 'users') {
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function () {
					$('select[name="showgroup"]').parent("th").remove(); 	// the "Show" dropdownn menu
					$('.box-rights').remove(); 								// Rights. (the part with all the checkboxes).
					$('.box-albums-unpadded').remove(); 					// Albums, Pages, and Categories.
					$('label[for="admin_language_0"], ul.flags').remove(); 	// Languages
				});
				// ]]> -->
			</script>

			<?php

		}
	}

	static function tab($xlate) {
		$xlate['demo'] = gettext('demo');
		return $xlate;
	}

}
?>