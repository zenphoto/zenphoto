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
		global $_zp_admin_tab;
		if (!zp_loggedin(ADMIN_RIGHTS) && $_zp_admin_tab == 'users') {
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function() {
					$('select[name="showgroup"]').parent("th").remove(); 	// the "Show" dropdownn menu
					$('.box-rights').remove(); 								// Rights. (the part with all the checkboxes).
					$('.box-albums-unpadded').remove(); 					// Albums, Pages, and Categories.
					$('.notebox').remove();									// All Noteboxes
					$('label[for="admin_language_0"], ul.flags').remove(); 	// Languages
					$('td:contains("<?php echo gettext("Quota"); ?>")').parent("tr.userextrainfo").remove(); // Display of assigned quota (if the "quota_manager" plugin is enabled).
					$('tr.userextrainfo td:contains("<?php echo gettext("User group membership"); ?>")').next().andSelf().remove(); // "User group membership" information (if the user_groups plugin is enabled).
					$('tr.userextrainfo td:contains("<?php echo gettext("Street"); ?>")').parent("tr.userextrainfo").remove(); // Address information.
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