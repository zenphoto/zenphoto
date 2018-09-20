<?php
/*
 * QR code image generator
 *
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2016 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 *
 * @package plugins/pdpqrcode
 * @pluginCategory misc
 *
 * @Copyright 2017 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 */

$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext('Provides a function to emit a QR code image.');

/**
 * Emits a QR code image
 *
 * @param string $content the "content" of the QR image
 */
function printQRImage($content) {
	?>
	<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/qrcode/image.php?content=<?php echo html_encode($content); ?>" />
	<?php
}
