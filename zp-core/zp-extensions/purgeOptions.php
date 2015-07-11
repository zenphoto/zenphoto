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
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext('Provides a means to purge options for Themes and Plugins.');
$plugin_author = "Stephen Billard (sbillard)";

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesHandler/favoritesClass.php');
if (strpos(__FILE__, ZENFOLDER) === false) {
	define("PURGEOPTIONS_FOLDER", USER_PLUGIN_FOLDER . '/purgeOptions/');
} else {
	define("PURGEOPTIONS_FOLDER", ZENFOLDER . '/' . PLUGIN_FOLDER . '/purgeOptions/');
}

zp_register_filter('admin_tabs', 'purgeOptions_admin_tabs');

function purgeOptions_admin_tabs($tabs) {
	if (zp_loggedin(OPTIONS_RIGHTS))
		$tabs['options']['subtabs'][gettext("purge")] = "/" . PURGEOPTIONS_FOLDER . 'purgeOptions_tab.php?page=options&tab=purge';
	return $tabs;
}

function listOwners($owners, $nest = '') {
	global $xlate, $highlighted;
	foreach ($owners as $owner => $detail) {
		?>
		<li>
			<?php
			if (is_array($detail)) {
				$autocheck = str_replace('/', '_', $nest . $owner);
				if (array_key_exists($owner, $xlate)) {
					echo $xlate[$owner];
				} else {
					echo $owner;
				}
				?>
				<input type="checkbox" id="<?php echo $autocheck; ?>" onclick="$('.<?php echo $autocheck; ?>').prop('checked', $('#<?php echo $autocheck; ?>').prop('checked'));">
				<ul>
					<?php listOwners($detail, $nest . $owner . '/'); ?>

				</ul>
				<?php
			} else {
				$autocheck = str_replace('/', '_', rtrim($nest, '/'));

				if ($detail && file_exists(SERVERPATH . '/' . internalToFilesystem($nest . $detail))) {
					$missing = '';
					$labelclass = 'none';
					$checked = false;
				} else {
					$labelclass = 'missing_owner';
					$missing = ' missing';
					$checked = ' checked="checked"';
					$highlighted = true;
					if (basename($nest) != THEMEFOLDER) {
						?>
						<input type="hidden" name="missingplugin[]" value="<?php echo $detail; ?>" />
						<?php
					}
				}
				if (empty($detail)) {
					$display = gettext('unknown');
					$labelclass = ' empty_name';
					$checked = ' checked="checked"';
				} else {
					$display = stripSuffix($detail);
				}
				?>
				<label class="<?php echo $labelclass; ?>">
					<input type="checkbox" name="del[]" class="<?php echo $autocheck . $missing; ?>" value="<?php echo $nest . $detail; ?>"<?php echo $checked; ?> /><?php echo $display; ?>
				</label>
				<?php
			}
			?>
		</li>
		<?php
	}
}
