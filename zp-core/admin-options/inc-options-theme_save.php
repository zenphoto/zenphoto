<?php

$themename = sanitize($_POST['optiontheme'], 3);
$returntab = "&tab=theme";
if ($themename)
	$returntab .= '&optiontheme=' . $themename;
// all theme specific options are custom options, handled below
if (!isset($_POST['themealbum']) || empty($_POST['themealbum'])) {
	$themeswitch = urldecode(sanitize_path($_POST['old_themealbum'])) != '';
} else {
	$alb = urldecode(sanitize_path($_POST['themealbum']));
	$themealbum = $table = AlbumBase::newAlbum($alb);
	if ($themealbum->exists) {
		$table = $themealbum;
		$returntab .= '&themealbum=' . html_encode(pathurlencode($alb)) . '&tab=theme';
		$themeswitch = $alb != urldecode(sanitize_path($_POST['old_themealbum']));
	} else {
		$themealbum = NULL;
	}
}

if ($themeswitch) {
	$notify = '?switched';
} else {
	if ($_POST['savethemeoptions'] == 'reset') {
		$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `theme`=' . $_zp_db->quote($themename);
		if ($themealbum) {
			$sql .= ' AND `ownerid`=' . $themealbum->getID();
		} else {
			$sql .= ' AND `ownerid`=0';
		}
		$_zp_db->query($sql);
		$themeswitch = true;
	} else {
		$ncw = $cw = getThemeOption('thumb_crop_width', $table, $themename);
		$nch = $ch = getThemeOption('thumb_crop_height', $table, $themename);
		if (isset($_POST['image_size']))
			setThemeOption('image_size', sanitize_numeric($_POST['image_size']), $table, $themename);
		if (isset($_POST['image_use_side']))
			setThemeOption('image_use_side', sanitize($_POST['image_use_side']), $table, $themename);
		if (isset($_POST['thumb_use_side']))
			setThemeOption('thumb_use_side', sanitize($_POST['thumb_use_side']), $table, $themename);
		setThemeOption('thumb_crop', (int) isset($_POST['thumb_crop']), $table, $themename);
		if (isset($_POST['thumb_size'])) {
			$ts = sanitize_numeric($_POST['thumb_size']);
			setThemeOption('thumb_size', $ts, $table, $themename);
		} else {
			$ts = getThemeOption('thumb_size', $table, $themename);
		}
		if (isset($_POST['thumb_crop_width'])) {
			if (is_numeric($_POST['thumb_crop_width'])) {
				$ncw = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_width']) / 100);
			}
			setThemeOption('thumb_crop_width', $ncw, $table, $themename);
		}
		if (isset($_POST['thumb_crop_height'])) {
			if (is_numeric($_POST['thumb_crop_height'])) {
				$nch = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_height']) / 100);
			}
			setThemeOption('thumb_crop_height', $nch, $table, $themename);
		}

		if (isset($_POST['albums_per_page'])) {
			$albums_per_page = sanitize_numeric($_POST['albums_per_page']);
			setThemeOption('albums_per_page', $albums_per_page, $table, $themename);
		}
		if (isset($_POST['images_per_page'])) {
			$images_per_page = sanitize_numeric($_POST['images_per_page']);
			setThemeOption('images_per_page', $images_per_page, $table, $themename);
		}

		setThemeOption('thumb_transition', isset($_POST['thumb_transition']), $table, $themename);
		if (isset($_POST['thumb_transition_min'])) {
			setThemeOption('thumb_transition_min', max(1, sanitize_numeric($_POST['thumb_transition_min'])), $table, $themename);
		}
		if (isset($_POST['thumb_transition_max'])) {
			setThemeOption('thumb_transition_max', max(1, sanitize_numeric($_POST['thumb_transition_max'])), $table, $themename);
		}

		if (isset($_POST['custom_index_page']))
			setThemeOption('custom_index_page', sanitize($_POST['custom_index_page'], 3), $table, $themename);
		$otg = getThemeOption('thumb_gray', $table, $themename);
		setThemeOption('thumb_gray', (int) isset($_POST['thumb_gray']), $table, $themename);
		if ($otg = getThemeOption('thumb_gray', $table, $themename))
			$wmo = 99; // force cache clear
		$oig = getThemeOption('image_gray', $table, $themename);
		setThemeOption('image_gray', (int) isset($_POST['image_gray']), $table, $themename);
		if ($oig = getThemeOption('image_gray', $table, $themename))
			$wmo = 99; // force cache clear
		if ($nch != $ch || $ncw != $cw) { // the crop height/width has been changed
			$sql = 'UPDATE ' . $_zp_db->prefix('images') . ' SET `thumbX`=NULL,`thumbY`=NULL,`thumbW`=NULL,`thumbH`=NULL WHERE `thumbY` IS NOT NULL';
			$_zp_db->query($sql);
			$wmo = 99; // force cache clear as well.
		}
	}
}