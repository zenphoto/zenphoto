<?php
zp_register_filter('themeSwitcher_head', 'switcher_head');
zp_register_filter('themeSwitcher_Controllink', 'switcher_controllink');
zp_register_filter('iconColor', 'iconColor');


$themecolors = array('light', 'dark');
if (extensionEnabled('themeSwitcher')) {
	$themeColor = zp_getCookie('themeSwitcher_color');
	if (isset($_GET['themeColor'])) {
		$new = $_GET['themeColor'];
		if (in_array($new, $themecolors)) {
			zp_setCookie('themeSwitcher_color', $new, false);
			$themeColor = $new;
		}
	}
	if (!empty($themeColor)) {
		setOption('zpmas_css', $themeColor, false);
	}
}

function switcher_head($ignore) {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function switchColors() {
			personality = $('#themeColor').val();
			window.location = '?themeColor=' + personality;
		}
		// ]]> -->
	</script>
	<?php
	return $ignore;
}

function switcher_controllink($ignore) {
	global $themecolors;
	$color = zp_getCookie('themeSwitcher_color');
	if (!$color) {
		$color = getOption('zpmas_css');
	}
	?>
	<span title="<?php echo gettext("Default theme color scheme."); ?>">
		<?php echo gettext('Theme Color'); ?>
		<select name="themeColor" id="themeColor" onchange="switchColors();">
			<?php generateListFromArray(array($color), $themecolors, false, false); ?>
		</select>
	</span>
	<?php
	return $ignore;
}

// set some variables for zpMasonry...

$zenpage = getOption('zp_plugin_zenpage');
setOption('zp_plugin_colorbox', false, false);
if (function_exists('printAddThis')) {
	$zpmas_social = true;
} else {
	$zpmas_social = false;
}
if ((function_exists('printGslideshow')) && (function_exists('printSlideShow'))) {
	$useGslideshow = true;
} else {
	$useGslideshow = false;
}
$galleryactive = false;
if (!is_null(getOption('zpmas_css'))) {
	$zpmas_css = getOption('zpmas_css');
} else {
	$zpmas_css = 'dark';
}
if ($zpmas_css == 'dark' && ($editorConfig = getOption('tinymce_comments'))) {
	$editorConfig = str_replace('_dark', '', stripSuffix($editorConfig)) . '_dark.php';
	setOption('tinymce_comments', $editorConfig, false);
}

if (!is_null(getOption('zpmas_finallink'))) {
	$zpmas_finallink = getOption('zpmas_finallink');
} else {
	$zpmas_finallink = 'nolink';
}
if (!is_null(getOption('zpmas_zpsearchcount'))) {
	$zpmas_zpsearchcount = getOption('zpmas_zpsearchcount');
} else {
	$zpmas_zpsearchcount = 2;
}
if (!is_null(getOption('zpmas_disablemeta'))) {
	$zpmas_disablemeta = getOption('zpmas_disablemeta');
} else {
	$zpmas_disablemeta = false;
}
if (!is_null(getOption('zpmas_logo'))) {
	$zpmas_logo = getOption('zpmas_logo');
} else {
	$zpmas_logo = '';
}
if (!is_null(getOption('zpmas_logoheight'))) {
	$zpmas_logoheight = getOption('zpmas_logoheight');
} else {
	$zpmas_logoheight = '';
}
if (!is_null(getOption('zpmas_thumbsize'))) {
	$zpmas_thumbsize = getOption('zpmas_thumbsize');
} else {
	$zpmas_thumbsize = 'small';
}
if (!is_null(getOption('zpmas_thumbcrop'))) {
	$zpmas_thumbcrop = getOption('zpmas_thumbcrop');
} else {
	$zpmas_thumbcrop = true;
}
if (!is_null(getOption('zpmas_imagetitle'))) {
	$zpmas_imagetitle = getOption('zpmas_imagetitle');
} else {
	$zpmas_imagetitle = true;
}
if (!is_null(getOption('zpmas_infscroll'))) {
	$zpmas_infscroll = getOption('zpmas_infscroll');
} else {
	$zpmas_infscroll = true;
}
if (!is_null(getOption('zpmas_fixsidebar'))) {
	$zpmas_fixsidebar = getOption('zpmas_fixsidebar');
} else {
	$zpmas_fixsidebar = true;
}
if (!is_null(getOption('zpmas_ss'))) {
	$zpmas_ss = getOption('zpmas_ss');
} else {
	$zpmas_ss = true;
}
if (!is_null(getOption('zpmas_sstype'))) {
	$zpmas_sstype = getOption('zpmas_sstype');
} else {
	$zpmas_sstype = 'random';
}
if (!is_null(getOption('zpmas_sscount'))) {
	$zpmas_sscount = getOption('zpmas_sscount');
} else {
	$zpmas_sscount = 5;
}
if (!is_null(getOption('zpmas_sseffect'))) {
	$zpmas_sseffect = getOption('zpmas_sseffect');
} else {
	$zpmas_sseffect = 'fade';
}
if (!is_null(getOption('zpmas_ssspeed'))) {
	$zpmas_ssspeed = getOption('zpmas_ssspeed');
} else {
	$zpmas_ssspeed = 4000;
}
if (!is_null(getOption('zpmas_cbtarget'))) {
	$zpmas_cbtarget = getOption('zpmas_cbtarget');
} else {
	$zpmas_cbtarget = true;
}
if (!is_null(getOption('zpmas_cbstyle'))) {
	$zpmas_cbstyle = getOption('zpmas_cbstyle');
} else {
	$zpmas_cbstyle = 'style3';
}
if (!is_null(getOption('zpmas_cbtransition'))) {
	$zpmas_cbtransition = getOption('zpmas_cbtransition');
} else {
	$zpmas_cbtransition = 'fade';
}
if (!is_null(getOption('zpmas_cbssspeed'))) {
	$zpmas_cbssspeed = getOption('zpmas_cbssspeed');
} else {
	$zpmas_cbssspeed = 3000;
}
if (!is_null(getOption('zpmas_usenews'))) {
	$zpmas_usenews = getOption('zpmas_usenews');
} else {
	$zpmas_usenews = 3000;
}

if ($zpmas_infscroll) {
	$zpmas_homelink = html_encode(getGalleryIndexURL());
} else {
	$zpmas_homelink = html_encode(getGalleryIndexURL());
}

if ($zpmas_thumbsize == 'small') {
	$zpmas_col_ss = 'colsss';
	$zpmas_col_album = 'col11';
	$zpmas_col_image = 'col4';
	$zpmas_image_size = 108;
	$zpmas_album_size_w = 248;
	$zpmas_ss_size_w = 528;
	if ($zpmas_thumbcrop) {
		$zpmas_album_size_h = 125;
		$zpmas_ss_size_h = 270;
	} else {
		$zpmas_album_size_h = $zpmas_album_size_w;
		$zpmas_ss_size_h = $zpmas_ss_size_w;
	}
} else {
	$zpmas_col_ss = 'colssl';
	$zpmas_col_album = 'col17';
	$zpmas_col_image = 'col7';
	$zpmas_image_size = 168;
	$zpmas_album_size_w = 368;
	$zpmas_ss_size_w = 768;
	$zpmas_ss_size_h = 360;
	if ($zpmas_thumbcrop) {
		$zpmas_album_size_h = 200;
		$zpmas_ss_size_h = 360;
	} else {
		$zpmas_album_size_h = $zpmas_album_size_w;
		$zpmas_ss_size_h = $zpmas_ss_size_w;
	}
}

if ($zpmas_ss) {
	switch ($zpmas_sstype) {
		case "album-latest":
			$zpmas_albumorimage = 'album';
			$zpmas_functionoption = 'latest';
			$zpmas_sstitle = gettext('Latest Albums');
			break;
		case "album-latestupdated":
			$zpmas_albumorimage = 'album';
			$zpmas_functionoption = 'latestupdated';
			$zpmas_sstitle = gettext('Latest Updated Albums');
			break;
		case "album-mostrated":
			$zpmas_albumorimage = 'album';
			$zpmas_functionoption = 'mostrated';
			$zpmas_sstitle = gettext('Most Rated Albums');
			break;
		case "album-toprated":
			$zpmas_albumorimage = 'album';
			$zpmas_functionoption = 'toprated';
			$zpmas_sstitle = gettext('Top Rated Albums');
			break;
		case "image-latest":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'latest';
			$zpmas_sstitle = gettext('Latest Images');
			break;
		case "image-latest-date":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'latest-date';
			$zpmas_sstitle = gettext('Latest Images');
			break;
		case "image-latest-mtime":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'latest-mtime';
			$zpmas_sstitle = gettext('Latest Images');
			break;
		case "image-popular":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'popular';
			$zpmas_sstitle = gettext('Popular Images');
			break;
		case "image-mostrated":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'mostrated';
			$zpmas_sstitle = gettext('Most Rated Images');
			break;
		case "image-toprated":
			$zpmas_albumorimage = 'image';
			$zpmas_functionoption = 'toprated';
			$zpmas_sstitle = gettext('Top Rated Images');
			break;
		case "random":
			$zpmas_albumorimage = '';
			$zpmas_functionoption = '';
			$zpmas_sstitle = gettext('Random Images');
			break;
	}
}

function iconColor($icon) {
	if (getOption('zpmas_css') == 'dark') {
		$icon = stripSuffix($icon) . '-white.png';
	}
	return($icon);
}

// Sets expanded titles (breadcrumbs) for Title meta
function getTitleBreadcrumb($before = ' ( ', $between = ' | ', $after = ' ) ') {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album;
	$titlebreadcrumb = '';
	if (in_context(ZP_SEARCH_LINKED)) {
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			$titlebreadcrumb .= $before . gettext("Search Result") . $after;
			if (is_null($_zp_current_album)) {
				return;
			} else {
				$parents = getParentAlbums();
			}
		} else {
			$album = newAlbum($dynamic_album);
			$parents = getParentAlbums($album);
			if (in_context(ZP_ALBUM_LINKED)) {
				array_push($parents, $album);
			}
		}
	} else {
		$parents = getParentAlbums();
	}
	$n = count($parents);
	if ($n > 0) {
		$titlebreadcrumb .= $before;
		$i = 0;
		foreach ($parents as $parent) {
			if ($i > 0)
				$titlebreadcrumb .= $between;
			$titlebreadcrumb .= $parent->getTitle();
			$i++;
		}
		$titlebreadcrumb .= $after;
	}
	return $titlebreadcrumb;
}
