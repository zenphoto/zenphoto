<?php
// force UTF-8 Ø

/**
 * Remove admin toolbox for all but site and album admins.
 */
if (!zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) zp_remove_filter('theme_body_close', 'adminToolbox');

/**
 * Returns an image for the home page
 *
 */
function printHomepageImage($imageRoot, $imageRandom, $titleStyle, $imageStyle) {
	global $_zp_gallery;
	if ($imageRoot == '*All Albums*') $imageRoot = '';
	if (empty($imageRoot)) {
		if ($imageRandom) {
			$titleImage = getRandomImages();
		} else {
			$titleImage = getLatestImages();
		}
	} else if (is_dir(getAlbumFolder() . $imageRoot) && (!(count(glob(getAlbumFolder() . $imageRoot . "/*")) === 0))) {
		if ($imageRandom) {
			$titleImage = getRandomImagesAlbum($imageRoot);
		} else {
			$titleImage = getLatestImagesAlbum($imageRoot);
		}
	}
	if (isset($titleImage)) {
		$title = $titleImage->getTitle();
		if (getOption('zenfluid_titletop')) {
			if ($title) {
				?>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<a href="<?php echo $titleImage->getLink();?>"><?php echo $title; ?></a>
				</div>
				<?php 
			}
		}
		echo '<a href="'.$titleImage->getLink().'"><img class="imgheight border" style="'.$imageStyle.'" src="'.$titleImage->getCustomImage(null, null, null, null, null, null, null).'" title="'.$title.'" /></a>';
		if (!getOption('zenfluid_titletop')) {
			if ($title) {
				?>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<a href="<?php echo $titleImage->getLink();?>"><?php echo $title; ?></a>
				</div>
				<?php 
			}
		}
	} else {
		debugLog('PrintHomepageImage: No images found in album path "' . $imageRoot .'"');
	}
}

/**
 * Returns latest image from the album or its subalbums. (May be NULL if none exists. Cannot be used on dynamic albums.)
 *
 * @param mixed $rootAlbum optional album object/folder from which to get the image.
 *
 * @return object
 */
function getLatestImagesAlbum($rootAlbum = '') {
	global $_zp_current_album, $_zp_gallery, $_zp_current_search;
	if (empty($rootAlbum)) {
		$album = $_zp_current_album;
	} else {
		if (is_object($rootAlbum)) {
			$album = $rootAlbum;
		} else {
			$album = newAlbum($rootAlbum);
		}
	}
	$image = NULL;

	$albumfolder = $album->getFileName();
	if ($album->isMyItem(LIST_RIGHTS)) {
		$imageWhere = '';
		$albumInWhere = '';
	} else {
		$imageWhere = " AND " . prefix('images') . ".show=1";
		$albumInWhere = prefix('albums') . ".show=1";
	}
	$query = "SELECT id FROM " . prefix('albums') . " WHERE ";
	if ($albumInWhere) $query .= $albumInWhere . ' AND ';
	$query .= "folder LIKE " . db_quote(db_LIKE_escape($albumfolder) . '%');
	$result = query($query);
	if ($result) {
		$albumInWhere = prefix('albums') . ".id IN (";
		while ($row = db_fetch_assoc($result)) {
			$albumInWhere = $albumInWhere . $row['id'] . ", ";
		}
		db_free_result($result);
		$albumInWhere = ' AND ' . substr($albumInWhere, 0, -2) . ')';
		$sql = 'SELECT `folder`, `filename` ' .
			' FROM ' . prefix('images') . ', ' . prefix('albums') .
			' WHERE ' . prefix('albums') . '.folder!="" AND ' . prefix('images') . '.albumid = ' .
			prefix('albums') . '.id ' . $albumInWhere . $imageWhere . ' ORDER BY '.prefix("images").'.date DESC LIMIT 1';
		$result = query($sql);
		$image = filterImageQuery($result, $album->name);
	}
	return $image;
}

/**
 * Returns the latest selected image from the gallery. (May be NULL if none exists)
 *
 * @return object
 */
function getLatestImages() {
	global $_zp_gallery;
	if (zp_loggedin()) {
		$imageWhere = '';
	} else {
		$imageWhere = " AND " . prefix('images') . ".show=1";
	}
	$result = query('SELECT `folder`, `filename` ' .
					' FROM ' . prefix('images') . ', ' . prefix('albums') .
					' WHERE ' . prefix('albums') . '.folder!="" AND ' . prefix('images') . '.albumid = ' .
					prefix('albums') . '.id ' . $imageWhere . ' ORDER BY '.prefix("images").'.date DESC');

	$image = filterImageQuery($result, NULL);
	if ($image) {
		return $image;
	}
	return NULL;
}

function printFormattedGalleryDesc($galleryDesc = "") {
	$galleryDescFormatted = str_replace("[br]","<br />",$galleryDesc);
	echo $galleryDescFormatted;
	return;
}

/**
 * Javascript to resize the image whenever the browser is resized.
 */
function ImageJS($titleMargin = 0,$stageWidth = 0, $stageImage = true) {
	return <<<EOJS
	<script type="text/javascript">
		// <!-- <![CDATA[
		/* Image resize functions */
		var viewportwidth;
		var viewportheight;
		var imgheight;
		var imgwidth;
		var headerheight = 0;
		var footerheight = 0;
		var titleheight = 0;
		function setStage(){
			viewportwidth = $(window).width();
			viewportheight = $(window).height();
			titleheight = $(".title").outerHeight(true) + 4;
			headerheight = $(".header").outerHeight(true);
			footerheight = $(".footer").outerHeight(true);
			sidebarwidth = $("#sidebar").outerWidth(false);
			sidebarheight = $("#sidebar").outerHeight(false) - 8;
			bodymarginleft = parseInt($("body").css("margin-left"));
			bodymarginright = parseInt($("body").css("margin-right"));
			imgheightmarginleft = parseInt($(".imgheight").css("margin-left"));
			imgheightmarginright = parseInt($(".imgheight").css("margin-right"));
			imgheightborderleft = parseInt($(".imgheight.border").css("border-left-width"));
			imgheightborderright = parseInt($(".imgheight.border").css("border-right-width"));
			if (footerheight > 0) { 
				footerheight = footerheight + 11;
				if ((sidebarheight + footerheight) > viewportheight) {
					footerheight = footerheight - (sidebarheight + footerheight - viewportheight);
				};
			};
			if (footerheight < 8) { footerheight = 8; };
			imgheight = viewportheight - headerheight - footerheight - $titleMargin - titleheight;
			imgwidth = viewportwidth - sidebarwidth - bodymarginleft - bodymarginright - imgheightmarginleft - imgheightmarginright - imgheightborderleft - imgheightborderright - 4;
			if ($stageImage && $stageWidth > 0 && imgwidth > $stageWidth - 4) {
				imgwidth = $stageWidth - 4;
			};
			if ($stageImage) {
				$("div.thumbstage").css({"max-width" : imgwidth + "px"});
			};
			$("img.imgheight").css({"max-height" : imgheight + "px"});
			$("img.imgheight").css({"max-width" : imgwidth + "px"});
		};
		$(document).ready(function() {
			setStage();
			window.setTimeout(setStage,500);
		});
		$(window).resize(setStage);
			window.addEventListener("orientationchange", setStage, false);
		// ]]> -->
	</script>
EOJS;
}

/**
 * Javascript to resize the video whenever the browser is resized.
 */
function vidJS($vidWidth, $vidHeight, $titleMargin = 50, $stageWidth = 0, $stageImage = true) {
	return <<<EOJS
	<script type="text/javascript">
	// <!-- <![CDATA[
		var viewportwidth;
		var viewportheight;
		var maxvidheight = $vidHeight;
		var maxvidwidth = $vidWidth;
		var vidwidth;
		var vidheight;
		var vidratio = maxvidheight / maxvidwidth;
		function setStage(){
			viewportwidth = $(window).width();
			viewportheight = $(window).height();
			headerheight = $("#header").outerHeight(true);
			vidheight = viewportheight - headerheight - $titleMargin;
			vidwidth = viewportwidth - 204;
			if (vidheight > maxvidheight) {
				vidheight = maxvidheight;
			}
			if (vidwidth > maxvidwidth) {
				vidwidth = maxvidwidth;
			}
			if (vidheight / vidratio > vidwidth) {
				vidheight = vidwidth * vidratio;
			}
			if (vidwidth * vidratio > vidheight) {
				vidwidth = vidheight / vidratio;
			}
			if ($stageImage && $stageWidth > 0 && vidwidth > $stageWidth - 4) {
				vidwidth = $stageWidth - 4;
				vidheight = vidwidth * vidratio;
			}
			$(".video").css({"max-width" : vidwidth + "px"});
			$(".video").css({"max-height" : vidheight + "px"});
			$(".video-js").css({"max-height" : vidheight + "px"});
			$(".video-js").css({"max-width" : vidwidth + "px"});
			$(".jp-video-play").css({"max-height" : vidheight + "px"});
		};
		$(document).ready(function() {
			setStage();
			window.setTimeout(setStage,500);
		});
		$(window).resize(setStage);
		window.addEventListener("orientationchange", setStage, false);
	// ]]> -->
	</script>
EOJS;
}

/**
	* Javascript to hide comments div if no comments
*/
function CommentsJS ($commentCount = 0) {
	return <<<EOJS
	<script type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function() {
			if ($commentCount == 0) {
				$("div#comments").css({"display" : "none"});
			};
		});
		// ]]> -->
	</script>
EOJS;
}

function colorBoxJS() {
	$close = gettext("Close");
	return <<<EOJS
	<script type="text/javascript">
		// <!-- <![CDATA[
		/* Colorbox */
		$(document).ready(function() {
		$(".colorbox").colorbox({
			inline: true,
			href: "#imagemetadata",
			close: "$close"
		});
		$("a.thickbox").colorbox({
			maxWidth: "98%",
			maxHeight: "98%",
			photo: true,
			close: "$close"
		});
		});
		// ]]> -->
		</script>
EOJS;
}

/**
 * Prints the "Show more results link" for search results for Zenpage items
 *
 * @param string $option "news" or "pages"
 * @param int $number_to_show how many search results should be shown initially
 */
function printZDSearchShowMoreLink($option, $number_to_show) {
	$option = strtolower($option);
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
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

/**
 * Prints jQuery JS to enable the toggling of search results of Zenpage	items
 *
 */
function printZDSearchToggleJS() {
	?>
	<script type="text/javascript">
	// <!-- <![CDATA[
	/* Search results toggle */
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
 * Records a raw Var to the debug log
 *
 * @param string $message message to insert in log [optional]
 * @param mixed $var the variable to record
 */
function debugLogRaw($message) {
	$args = func_get_args();
	if (count($args) == 1) {
		$var = $message;
		$message = '';
	} else {
		$message .= ' ';
		$var = $args[1];
	}
	ob_start();
	var_dump($var);
	$str = ob_get_contents();
	ob_end_clean();
	debugLog($message . $str);
}

?>