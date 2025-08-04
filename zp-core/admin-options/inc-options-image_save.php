<?php

setOption('image_max_size', sanitize_numeric($_POST['image_max_size']));
setOption('image_quality', sanitize($_POST['image_quality'], 3));
setOption('thumb_quality', sanitize($_POST['thumb_quality'], 3));
setOption('image_allow_upscale', (int) isset($_POST['image_allow_upscale']));
setOption('thumb_sharpen', (int) isset($_POST['thumb_sharpen']));
setOption('image_sharpen', (int) isset($_POST['image_sharpen']));
setOption('image_interlace', (int) isset($_POST['image_interlace']));
setOption('EmbedIPTC', (int) isset($_POST['EmbedIPTC']));
setOption('metadata_refresh_behaviour', sanitize($_POST['metadata_refresh_behaviour'], 3));

setOption('copyright_image_notice', process_language_string_save('copyright_image_notice', 3));
setOption('display_copyright_image_notice', (int) isset($_POST['display_copyright_image_notice']));
setOption('copyright_image_url', sanitize($_POST['copyright_image_url']));
setOption('copyright_image_url_custom', sanitize($_POST['copyright_image_url_custom']));
setOption('copyright_image_rightsholder', sanitize($_POST['copyright_image_rightsholder']));
setOption('copyright_image_rightsholder_custom', sanitize($_POST['copyright_image_rightsholder_custom']));

setOption('sharpen_amount', sanitize_numeric($_POST['sharpen_amount']));
setOption('image_max_size', sanitize_numeric($_POST['image_max_size']));
$num = str_replace(',', '.', sanitize($_POST['sharpen_radius']));
if (is_numeric($num))
	setOption('sharpen_radius', $num);
setOption('sharpen_threshold', sanitize_numeric($_POST['sharpen_threshold']));

if (isset($_POST['fullimage_watermark'])) {
	$new = sanitize($_POST['fullimage_watermark'], 3);
	setOption('fullimage_watermark', $new);
}
if (isset($_POST['fullsizeimage_watermark'])) {
	$new = sanitize($_POST['fullsizeimage_watermark'], 3);
	setOption('fullsizeimage_watermark', $new);
}

setOption('watermark_scale', sanitize($_POST['watermark_scale'], 3));
setOption('watermark_h_offset', sanitize($_POST['watermark_h_offset'], 3));
setOption('watermark_w_offset', sanitize($_POST['watermark_w_offset'], 3));
setOption('image_cache_suffix', sanitize($_POST['image_cache_suffix']));

$imageplugins = array_unique($_zp_extra_filetypes);
$imageplugins[] = 'Image';
foreach ($imageplugins as $plugin) {
	$opt = $plugin . '_watermark';
	if (isset($_POST[$opt])) {
		$new = sanitize($_POST[$opt], 3);
		setOption($opt, $new);
	}
}

setOption('full_image_quality', sanitize($_POST['full_image_quality'], 3));
setOption('cache_full_image', (int) isset($_POST['cache_full_image']));
setOption('protect_full_image', sanitize($_POST['protect_full_image'], 3));
setOption('imageProcessorConcurrency', $_POST['imageProcessorConcurrency']);
$notify = processCredentials('protected_image');

setOption('secure_image_processor', (int) isset($_POST['secure_image_processor']));
if (isset($_POST['protected_image_cache'])) {
	setOption('protected_image_cache', 1);
	copy(SERVERPATH . '/' . ZENFOLDER . '/file-templates/cacheprotect', SERVERPATH . '/' . CACHEFOLDER . '/.htaccess');
	@chmod(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess', 0444);
} else {
	@chmod(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess', 0777);
	@unlink(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess');
	setOption('protected_image_cache', 0);
}
setOption('hotlink_protection', (int) isset($_POST['hotlink_protection']));
setOption('use_lock_image', (int) isset($_POST['use_lock_image']));
$st = sanitize($_POST['image_sorttype'], 3);
if ($st == 'custom') {
	$st = unQuote(strtolower(sanitize($_POST['customimagesort'], 3)));
}
setOption('image_sorttype', $st);
setOption('image_sortdirection', (int) isset($_POST['image_sortdirection']));
setOption('use_embedded_thumb', (int) isset($_POST['use_embedded_thumb']));
setOption('IPTC_encoding', sanitize($_POST['IPTC_encoding']));
setOption('IPTC_convert_linebreaks', (int) isset($_POST['IPTC_convert_linebreaks']));
foreach ($_zp_exifvars as $key => $item) {
	$v = sanitize_numeric($_POST[$key]);
	switch ($v) {
		case 0:
		case 1:
			setOption($key . '-disabled', 0);
			setOption($key, $v);
			break;
		case 2:
			setOption($key, 0);
			setOption($key . '-disabled', 1);
			break;
	}
}
$returntab = "&tab=image";
