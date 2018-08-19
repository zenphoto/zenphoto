<?php

/*
 * PHP theme editor
 *
 * <b>Note:</b> This editor is lowest priority, so will only be active if no
 * other plugin has attached the <i>theme_editor</i> filter.
 *
 * @author Stephen Billard (sbillard)
 *
 * @Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/netPhotoGraphics netPhotoGraphics and derivatives}
 *
 * @package plugins/themeEditor
 * @pluginCategory admin
 */
$plugin_is_filter = defaultExtension(900 | ADMIN_PLUGIN); // lowest priotiry so other instances will override
$plugin_description = gettext('PHP based theme editor.');

zp_register_filter('theme_editor', 'PHPThemeEdit');

function phpThemeEdit($html, $theme) {
	$html = "launchScript('" . PLUGIN_FOLDER . "/themeEditor/themes-editor.php', ['theme=" . urlencode($theme) . "'])";
	return $html;
}
