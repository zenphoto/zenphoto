<?php
// force UTF-8 Ø

/**
 * Prints jQuery JS to enable the toggling of search results of Zenpage  items
 *
 */
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
	$option = strtolower(sanitize($option));
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
	$option = strtolower(sanitize($option));
	$c = sanitize_numeric($c);
	$number_to_show = sanitize_numeric($number_to_show);
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

function my_checkPageValidity($request, $gallery_page, $page) {
	switch ($gallery_page) {
		case 'gallery.php';
			$gallery_page = 'index.php'; //	same as an album gallery index
			break;
		case 'index.php':
			if (!extensionEnabled('zenpage')) { // only one index page if zenpage plugin is enabled
				break;
			}
		default:
			if ($page != 1) {
				return false;
			}
		case 'news.php':
		case 'album.php':
		case 'search.php':
			break;
	}
	return checkPageValidity($request, $gallery_page, $page);
}

if (!OFFSET_PATH) {
	enableExtension('print_album_menu', 1 | THEME_PLUGIN, false);
	setOption('user_logout_login_form', 2, false);
	$_zp_page_check = 'my_checkPageValidity';
}
?>