<?php
/**
 * List “option owners” for possible purging of obsolete options
 *
 * Places a <var>purge</var> sub-tab under the <var>options</var> tab
 * This tab lists “option owners” based on the <i>theme</i> and <i>creator</i>
 * columns in the <var>options</var> table. It will flag “owners” which appear no
 * longer to exist.
 *
 * Checking the box beside the owner will select that owner's options to be purged
 * from the database.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('List option owners.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = 1.0;

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesHandler/favoritesClass.php');
if (strpos(__FILE__, ZENFOLDER) === false) {
	define("PURGEOPTIONS_FOLDER", USER_PLUGIN_FOLDER . '/purgeOptions/');
} else {
	define("PURGEOPTIONS_FOLDER", ZENFOLDER . '/' . PLUGIN_FOLDER . '/purgeOptions/');
}

if (zp_loggedin(OPTIONS_RIGHTS)) {
	zp_register_filter('admin_tabs', 'purgeOptions_admin_tabs');
}

function purgeOptions_admin_tabs($tabs) {
	$tabs['options']['subtabs'][gettext("purge")] = "/" . PURGEOPTIONS_FOLDER . 'purgeOptions_tab.php?page=options&tab=purge';
	return $tabs;
}

function listOwners($owners, $nest = '') {
	global $xlate;
	foreach ($owners as $owner => $detail) {
		?>
		<li>
			<?php
			if (is_array($detail)) {
				if (array_key_exists($owner, $xlate)) {
					echo $xlate[$owner];
				} else {
					echo $owner;
				}
				?>
				<ul>
					<?php listOwners($detail, $nest . $owner . '/'); ?>
				</ul>
				<?php
			} else {
				if (file_exists(SERVERPATH . '/' . internalToFilesystem($nest . $detail))) {
					$class = '';
				} else {
					$class = ' class="missing_owner"';
				}
				?>
				<label<?php echo $class; ?>>
					<input type="checkbox" name="del[]" value="<?php echo $nest . $detail; ?>"><?php echo stripSuffix($detail); ?>
				</label>
				<?php
			}
			?>
		</li>
		<?php
	}
}
