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
 * @Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/netPhotoGraphics netPhotoGraphics and derivatives}
 *
 * @package plugins/purgeOptions
 * @pluginCategory admin
 */
$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext('Provides a means to purge options for Themes and Plugins.');

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesHandler/favoritesClass.php');
if (strpos(__FILE__, ZENFOLDER) === false) {
	define("PURGEOPTIONS_FOLDER", USER_PLUGIN_FOLDER . '/purgeOptions/');
} else {
	define("PURGEOPTIONS_FOLDER", ZENFOLDER . '/' . PLUGIN_FOLDER . '/purgeOptions/');
}

zp_register_filter('admin_tabs', 'purgeOptions_admin_tabs');

function purgeOptions_admin_tabs($tabs) {
	if (zp_loggedin(ADMIN_RIGHTS))
		$tabs['options']['subtabs'][gettext("purge")] = "/" . PURGEOPTIONS_FOLDER . 'purgeOptions_tab.php?page=options&tab=purge';
	return $tabs;
}

function listOwners($owners, $nest = '') {
	global $xlate, $highlighted, $_zp_gallery;

	$currentTheme = $_zp_gallery->getCurrentTheme();

	foreach ($owners as $owner => $detail) {
		if (is_array($detail)) {
			$size = ceil(count($detail) / 25);
			?>
			<div class="purgeOptions_list">
				<?php
				$autocheck = str_replace('/', '_', $nest . $owner);
				if (array_key_exists($owner, $xlate)) {
					$ownerN = $xlate[$owner];
				} else {
					$ownerN = $owner;
				}
				?>
				<span class="purgeOptionsClass"><?php echo $ownerN; ?></span> <input type="checkbox" id="<?php echo $autocheck; ?>" onclick="$('.<?php echo $autocheck; ?>').prop('checked', $('#<?php echo $autocheck; ?>').prop('checked'));">
				<ul class="purgeOptionsBlock"<?php if ($size > 1) echo ' style="' . "column-count:$size;	-moz-column-count: $size;	-webkit-column-count: $size;" . '"'; ?>>
					<?php listOwners($detail, $nest . $owner . '/'); ?>
				</ul>
			</div>
			<?php
		} else {
			$autocheck = str_replace('/', '_', rtrim($nest, '/'));
			$active = false;

			if ($nest == THEMEFOLDER . '/') {
				$suffix = '';
			} else {
				$suffix = '.php';
			}

			if ($detail && file_exists(SERVERPATH . '/' . internalToFilesystem($nest . $detail . $suffix))) {
				$labelclass = 'none';
				$missing = '';
				$checked = false;
				if ($suffix && extensionEnabled($detail)) {
					$active = gettext('Active Extension');
				} else if ($detail == $currentTheme) {
					$active = gettext('Current Theme');
				}
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
				$labelclass = 'empty_name';
				$checked = ' checked="checked"';
			} else {
				$display = stripSuffix($detail);
			}
			?>
			<li>
				<label class="<?php echo $labelclass; ?>">
					<input type="checkbox" name="del[]" class="<?php echo $autocheck . $missing; ?>" value="<?php echo $nest . $detail; ?>"<?php echo $checked; ?> /><span <?php if ($active) echo 'class="active" title="' . $active . '"'; ?>><?php echo $display; ?></span>
				</label>
			</li>
			<?php
		}
	}
}
