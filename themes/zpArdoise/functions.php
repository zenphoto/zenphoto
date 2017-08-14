<?php
zp_register_filter('themeSwitcher_head', 'switcher_head');
zp_register_filter('iconColor', 'iconColor');
zp_register_filter('themeSwitcher_Controllink', 'switcher_controllink');

if (!OFFSET_PATH) {
	if ((getOption('use_galleriffic')) && !(($_zp_gallery_page == 'image.php') || ($_zp_gallery_page == 'search.php') || ($_zp_gallery_page == 'favorites.php'))) {
		setOption('image_size', '555', false);
		setOption('image_use_side', 'longest', false);
		setOption('thumb_size', '85', false);
		setOption('thumb_crop', '1', false);
		setOption('thumb_crop_width', '85', false);
		setOption('thumb_crop_height', '85', false);
	}
	setOption('personnal_thumb_width', '267', false);
	setOption('personnal_thumb_height', '133', false);

	setOption('zp_plugin_colorbox_js', 9 | THEME_PLUGIN, false); //force colorbox
	setOption('comment_form_toggle', false, false); // force this option of comment_form, to avoid JS conflits
	setOption('comment_form_pagination', false, false); // force this option of comment_form, to avoid JS conflits
	setOption('tinymce_comments', null, false); // force this option to disable tinyMCE for comment form

	$_zenpage_enabled = extensionEnabled('zenpage');
	$_zp_page_check = 'my_checkPageValidity';
}

function iconColor($icon) {
	global $themeColor;
	if (getOption('css_style') == 'dark') {
		$icon = stripSuffix($icon) . '-white.png';
	}
	return($icon);
}

$themecolors = array('light', 'dark');

function switcher_head($ignore) {
	global $personalities, $themecolors, $themeColor;
	$themeColor = zp_getCookie('themeSwitcher_color');
	if (isset($_GET['themeColor'])) {
		$new = $_GET['themeColor'];
		if (in_array($new, $themecolors)) {
			zp_setCookie('themeSwitcher_color', $new, false);
			$themeColor = $new;
		}
	}
	if (!empty($themeColor)) {
		setOption('css_style', $themeColor, false);
	}
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
		$color = getOption('css_style');
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

function my_checkPageValidity($request, $gallery_page, $page) {
	if ($gallery_page == 'gallery.php') {
		$gallery_page = 'index.php';
	}
	return checkPageValidity($request, $gallery_page, $page);
}

/* zpArdoise_printRandomImages
  /*	- use improvements of zenphoto 1.4.6 on printRandomImages
  /*	- use improvements of zenphoto 1.4.5 on printRandomImages
  /*	- use improvements of zenphoto 1.4.2 on printRandomImages
  /*		- http://www.zenphoto.org/trac/ticket/1914,
  /*		- http://www.zenphoto.org/trac/ticket/2020,
  /*		- http://www.zenphoto.org/trac/ticket/2028
  /*	- implements call of colorbox (http://www.zenphoto.org/trac/ticket/1908 and http://www.zenphoto.org/trac/ticket/1909)
 */

function zpArdoise_printRandomImages($number = 5, $class = NULL, $option = 'all', $rootAlbum = '', $width = NULL, $height = NULL, $crop = NULL, $fullimagelink = false, $a_class = NULL) {
	if (is_null($crop) && is_null($width) && is_null($height)) {
		$crop = 2;
	} else {
		if (is_null($width))
			$width = 85;
		if (is_null($height))
			$height = 85;
		if (is_null($crop)) {
			$crop = 1;
		} else {
			$crop = (int) $crop && true;
		}
	}
	if (!empty($class))
		$class = ' class="' . $class . '"';

	echo "<ul" . $class . ">";
	for ($i = 1; $i <= $number; $i++) {
		switch ($option) {
			case "all":
				$randomImage = getRandomImages();
				break;
			case "album":
				$randomImage = getRandomImagesAlbum($rootAlbum);
				break;
		}
		if (is_object($randomImage) && $randomImage->exists) {
			echo "<li>\n";
			if ($fullimagelink) {
				$aa_class = ' class="' . $a_class . '"';
				$randomImageURL = $randomImage->getFullimageURL();
			} else {
				$aa_class = NULL;
				$randomImageURL = $randomImage->getLink();
			}
			echo '<a href="' . html_encode($randomImageURL) . '"' . $aa_class . ' title="' . html_encode($randomImage->getTitle()) . '">';
			switch ($crop) {
				case 0:
					$html = "<img src=\"" . html_encode(pathurlencode($randomImage->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . "\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
					break;
				case 1:
					$html = "<img src=\"" . html_encode(pathurlencode($randomImage->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . "\" alt=\"" . html_encode($randomImage->getTitle()) . "\" width=\"" . $width . "\" height=\"" . $height . "\" />\n";
					break;
				case 2:
					$html = "<img src=\"" . html_encode(pathurlencode($randomImage->getThumb())) . "\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
					break;
			}
			echo zp_apply_filter('custom_image_html', $html, false);
			echo "</a>";
			echo "</li>\n";
		} else {
			break;
		}
	}
	echo "</ul>";
}

/* zpArdoise_printImageStatistic
  /*	- use improvements of zenphoto 1.4.6 on printRandomImages
  /*	- use improvements of zenphoto 1.4.2 on printImageStatistic (http://www.zenphoto.org/trac/ticket/1914)
  /*	- implements call of colorbox (http://www.zenphoto.org/trac/ticket/1908 and http://www.zenphoto.org/trac/ticket/1909)
 */

function zpArdoise_printImageStatistic($number, $option, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0, $a_class = NULL) {
	$images = getImageStatistic($number, $option, $albumfolder, $collection, $threshold);
	if (is_null($crop) && is_null($width) && is_null($height)) {
		$crop = 2;
	} else {
		if (is_null($width))
			$width = 85;
		if (is_null($height))
			$height = 85;
		if (is_null($crop)) {
			$crop = 1;
		} else {
			$crop = (int) $crop && true;
		}
	}

	echo "\n<div id=\"$option\">\n";
	echo "<ul>";
	foreach ($images as $image) {
		if ($fullimagelink) {
			$aa_class = ' class="' . $a_class . '"';
			$imagelink = $image->getFullImageURL();
		} else {
			$aa_class = NULL;
			$imagelink = $image->getLink();
		}
		echo "<li><a href=\"" . html_encode(pathurlencode($imagelink)) . "\"" . $aa_class . " title=\"" . html_encode($image->getTitle()) . "\">\n";
		switch ($crop) {
			case 0:
				echo "<img src=\"" . html_encode(pathurlencode($image->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . "\" alt=\"" . html_encode($image->getTitle()) . "\" /></a>\n";
				break;
			case 1:
				echo "<img src=\"" . html_encode(pathurlencode($image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . "\" alt=\"" . html_encode($image->getTitle()) . "\" width=\"" . $width . "\" height=\"" . $height . "\" /></a>\n";
				break;
			case 2:
				echo "<img src=\"" . html_encode(pathurlencode($image->getThumb())) . "\" alt=\"" . html_encode($image->getTitle()) . "\" /></a>\n<br />";
				break;
		}
		if ($showtitle) {
			echo "<h3><a href=\"" . html_encode(pathurlencode($image->getLink())) . "\" title=\"" . html_encode($image->getTitle()) . "\">\n";
			echo $image->getTitle() . "</a></h3>\n";
		}
		if ($showdate) {
			echo "<p>" . zpFormattedDate(DATE_FORMAT, strtotime($image->getDateTime())) . "</p>";
		}
		if ($showstatistic === "rating" OR $showstatistic === "rating+hitcounter") {
			$votes = $image->get("total_votes");
			$value = $image->get("total_value");
			if ($votes != 0) {
				$rating = round($value / $votes, 1);
			}
			echo "<p>" . sprintf(gettext('Rating: %1$u (Votes: %2$u)'), $rating, $votes) . "</p>";
		}
		if ($showstatistic === "hitcounter" OR $showstatistic === "rating+hitcounter") {
			$hitcounter = $image->get("hitcounter");
			if (empty($hitcounter)) {
				$hitcounter = "0";
			}
			echo "<p>" . sprintf(gettext("Views: %u"), $hitcounter) . "</p>";
		}
		if ($showdesc) {
			echo html_encodeTagged(shortenContent($image->getDesc(), $desclength, ' (...)'));
		}
		echo "</li>";
	}
	echo "</ul></div>\n";
}

/* zpArdoise_printEXIF */

function zpardoise_printEXIF() {
	$Meta_data = getImageMetaData(); // put all exif data in a array
	if (!is_null($Meta_data)) {
		$Exifs_list = '';
		if (isset($Meta_data['EXIFModel'])) {
			$Exifs_list .= html_encode($Meta_data['EXIFModel']);
		};
		if (isset($Meta_data['EXIFFocalLength'])) {
			$Exifs_list .= ' &ndash; ' . html_encode($Meta_data['EXIFFocalLength']);
		};
		if (isset($Meta_data['EXIFFNumber'])) {
			$Exifs_list .= ' &ndash; ' . html_encode($Meta_data['EXIFFNumber']);
		};
		if (isset($Meta_data['EXIFExposureTime'])) {
			$Exifs_list .= ' &ndash; ' . html_encode($Meta_data['EXIFExposureTime']);
		};
		if (isset($Meta_data['EXIFISOSpeedRatings'])) {
			$Exifs_list .= ' &ndash; ' . html_encode($Meta_data['EXIFISOSpeedRatings']) . ' ISO';
		};
		echo $Exifs_list;
	}
}
?>