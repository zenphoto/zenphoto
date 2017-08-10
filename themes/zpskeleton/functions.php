<?php
// Check some settings

$zenpage = getOption('zp_plugin_zenpage');
$thumbcrop = getOption('thumb_crop');
$zpskel_disablewarning = getOption('zpskel_disablewarning'); // test is disable warning is checked
if (is_null(getOption('zpskel_thumbsize'))) {
	$optionsnotsaved = true;
} else {
	$optionsnotsaved = false;
} // test this option to determine if theme options have been saved at least once
// warning about plugins
if (!$zpskel_disablewarning) {
	$plugincount = 0;
	$warning_listitem = '';
	if (getOption('zp_plugin_colorbox_js')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>Colorbox</em></strong> [ This theme uses and configures it\'s own javascript popup plugin for image previews ]</li>';
		$plugincount++;
	}
	if (getOption('zp_plugin_deprecated-functions')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>Deprecated Functions</em></strong> [ This theme should be current on core functions as of version 1.4.6 ]</li>';
		$plugincount++;
	}
	if (getOption('zp_plugin_jcarousel_thumb_nav')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>jCarousel Thumb Nav</em></strong> [ Sorry no theme support for this plugin. ]</li>';
		$plugincount++;
	}
	if (getOption('zp_plugin_paged_thumbs_nav')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>Paged Thumb Nav</em></strong> [ Sorry no theme support for this plugin. ]</li>';
		$plugincount++;
	}
	if (getOption('zp_plugin_menu_manager')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>Menu Manager</em></strong> [ Sorry no theme support for this plugin. ]</li>';
		$plugincount++;
	}
	if (getOption('zp_plugin_slideshow')) {
		$warning_listitem .= '<li>Disable Plugin: <strong><em>Slideshow</em></strong> [ This theme uses and configures it\'s own javascript popup plugin for slideshows ]</li>';
		$plugincount++;
	}

	if ($plugincount > 0) {
		$warning_message = '<div><h4>Warning!</h4><p>There are various plugins that you may have enabled that serve no purpose, but just increase load and processing...</p><ul class="square">';
		$warning_message .= $warning_listitem;
		$warning_message .= '</ul><p>These are just warnings.  You can turn off this message in the theme options.</p></div>';
	}
}

// warning about saving theme options
if ($optionsnotsaved) {
	$options_message = '<div><h4>Please note:</h4><p>You must set zpSkeleton theme options at least once to create the theme variables.  This is just a warning and can be switched off in the theme options</p></div>';
} else
	$options_message = '';

setOption('comment_form_rss', false, false); // displayed elsewhere.
setOption('albums_per_row', 3, false);
setOption('images_per_row', 8, false);

// Pull theme option variables | set if null
$zpskel_debuguser = getOption('zpskel_debuguser');
if (is_null($zpskel_debuguser))
	$zpskel_debuguser = false;
$zpskel_usenews = getOption('zpskel_usenews');
if (is_null($zpskel_usenews))
	$zpskel_usenews = true;
$zpskel_pptarget = getOption('zpskel_pptarget');
if (is_null($zpskel_pptarget))
	$zpskel_pptarget = 'sized';
$zpskel_strip = getOption('zpskel_strip');
if (is_null($zpskel_strip))
	$zpskel_strip = 'latest';
$zpskel_download = getOption('zpskel_download');
if (is_null($zpskel_download))
	$zpskel_download = true;
$zpskel_thumbsize = getOption('zpskel_thumbsize');
if (is_null($zpskel_thumbsize))
	$zpskel_thumbsize = 'large';
$zpskel_archive = getOption('zpskel_archive');
if (is_null($zpskel_archive))
	$zpskel_archive = true;
$zenpage_homepage = getOption('zenpage_homepage');
if (is_null($zenpage_homepage))
	$zenpage_homepage = 'none';

// include useragent detector, set variable for mobile users...
require_once('inc-browser.php');
$browser = new Browser();
if ($browser->isMobile()) {
	$zpskel_ismobile = true;
} else {
	$zpskel_ismobile = false;
}

// include image statistics plugin if image strip set to latest images
if ($zpskel_strip == 'latest')
	require_once (SERVERPATH . '/' . ZENFOLDER . "/zp-extensions/image_album_statistics.php");

// Sets expanded titles (breadcrumbs) for Title meta
function getTitleBreadcrumb($before = ' ( ', $between = ' / ', $after = ' ) ') {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album;
	$titlebreadcrumb = '';
	if (in_context(ZP_SEARCH_LINKED)) {
		if (empty($_zp_current_search->dynalbumname)) {
			$titlebreadcrumb .= $before . gettext("Search Result") . $after;
			if (is_null($_zp_current_album)) {
				return;
			} else {
				$parents = getParentAlbums();
			}
		} else {
			$album = newAlbum($_zp_current_search->dynalbumname);
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

$slideshow_instance = 0;

/**
 * Prints a link to call the prettyphoto slideshow (not shown if there are no images in the album)
 * Modified from core slideshow plugin to work with prettyphoto (http://www.no-margin-for-errors.com)
 *
 * @param string $linktext Text for the link
 * @param string $linkstyle Style of Text for the link
 */
function printPPSlideShowLink($linktext = '', $linkstyle = '') {
	global $_zp_gallery, $_zp_current_image, $_zp_current_album, $_zp_current_search, $slideshow_instance, $_zp_gallery_page, $zpskel_pptarget;
	$numberofimages = getNumImages();
	if ($numberofimages > 1) {
		if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
			$images = $_zp_current_search->getImages(0);
		} else {
			$images = $_zp_current_album->getImages(0);
		}
		$count = '';
		foreach ($images as $image) {
			if (is_array($image)) {
				$suffix = getSuffix($image['filename']);
			} else {
				$suffix = getSuffix($image);
			}
			$suffixes = array('jpg', 'jpeg', 'gif', 'png');
			if (in_array($suffix, $suffixes)) {
				$count++;
				$imgobj = newImage($_zp_current_album, $image);
				$style = '';

				if ($_zp_gallery_page == 'image.php' || in_context(ZP_SEARCH_LINKED)) {
					if (in_context(ZP_SEARCH_LINKED)) {
						if ($count != 1) {
							$style = ' style="display:none"';
						}
					} else {
						if ($_zp_current_image->filename != $image) {
							$style = ' style="display:none"';
						}
					}
				} elseif ($_zp_gallery_page == 'album.php' || $_zp_gallery_page == 'search.php' || $_zp_gallery_page == 'favorites.php') {
					if ($count != 1) {
						$style = ' style="display:none"';
					}
				}
				if ($zpskel_pptarget == 'sized') {
					$imagelink = $imgobj->getSizedImage(630);
				} else {
					$imagelink = $imgobj->getFullImageURL();
				}
				$imagedetaillink = $imgobj->getLink();
				?>
				<div class="ss-link noshow-mobile">
					<a class="ss button" href="<?php echo html_encode($imagelink); ?>" rel="slideshow[group]"<?php echo $style; ?> title="&lt;a href='<?php echo $imagedetaillink; ?>'&gt;<?php echo html_encode(strip_tags($imgobj->getTitle())) . ' (' . gettext('Click for Detail Page') . ')'; ?>&lt;/a&gt;"><?php echo $linktext; ?></a>
				</div>
				<?php
			}
		}
	}
}

// Prints jQuery JS to enable the toggling of search results of Zenpage plugin items
function printZDSearchToggleJS() {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function toggleExtraElements(category, show) {
			if (show) {
				jQuery('.' + category + '_showless').show();
				jQuery('.' + category + '_showmore').hide();
				jQuery('.' + category + '_extrashow').show();
			} else {
				jQuery('.' + category + '_showless').hide();
				jQuery('.' + category + '_showmore').show();
				jQuery('.' + category + '_extrashow').hide();
			}
		}
		// ]]> -->
	</script>
	<?php
}

/**
 * Prints the "Show more results link" for search results for Zenpage items
 *
 * @param string $option "news" or "pages"
 * @param int $number_to_show how many search results should be shown initially
 */
function printZDSearchShowMoreLink($option, $number_to_show) {
	$option = strtolower($option);
	$number_to_show = sanitize_numeric($number_to_show);
	switch ($option) {
		case "news":
			$num = getNumNews();
			break;
		case "pages":
			$num = getNumPages();
			break;
	}
	if ($num > $number_to_show) {
		?>
		<a class="<?php echo $option; ?>_showmore"href="javascript:toggleExtraElements('<?php echo $option; ?>',true);"><?php echo gettext('Show more results'); ?></a>
		<a class="<?php echo $option; ?>_showless" style="display: none;"	href="javascript:toggleExtraElements('<?php echo $option; ?>',false);"><?php echo gettext('Show fewer results'); ?></a>
		<?php
	}
}

/**
 * Adds the css class necessary for toggling of Zenpage items search results
 *
 * @param string $option "news" or "pages"
 * @param string $c After which result item the toggling should begin. Here to be passed from the results loop.
 */
function printZDToggleClass($option, $c, $number_to_show) {
	$option = strtolower($option);
	$c = sanitize_numeric($c);
	$number_to_show = sanitize_numeric($number_to_show);
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

//because the theme does not check!
if (!function_exists('getCommentCount')) {

	function getCommentCount() {
		return 0;
	}

}
?>