<?php

/**
 * Prints a paged thumbnail navigation to be used on a theme's image.php, independent of the album.php's thumbs loop
 * The function contains some predefined CSS ids you can use for styling.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */
$plugin_description = gettext("Prints a paged thumbs navigation on image.php, independent of the album.php's thumbs.");
$plugin_author = "Malte Müller (acrylian)";
$option_interface = 'pagedthumbsOptions';

/**
 * Plugin option handling class
 *
 */
class pagedthumbsOptions {

	function pagedthumbsOptions() {
		setOptionDefault('pagedthumbs_imagesperpage', '10');
		setOptionDefault('pagedthumbs_counter', '');
		gettext($str = '« prev thumbs');
		setOptionDefault('pagedthumbs_prevtext', getAllTranslations($str));
		gettext($str = 'next thumbs »');
		setOptionDefault('pagedthumbs_nexttext', getAllTranslations($str));
		setOptionDefault('pagedthumbs_width', '50');
		setOptionDefault('pagedthumbs_height', '50');
		setOptionDefault('pagedthumbs_crop', '1');
		setOptionDefault('pagedthumbs_placeholders', '');
		setOptionDefault('pagedthumbs_pagelist', '');
		setOptionDefault('pagedthumbs_pagelistprevnext', '');
		setOptionDefault('pagedthumbs_pagelistlength', '6');
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('paged_thumbs_nav');
			cacheManager::addThemeCacheSize('paged_thumbs_nav', NULL, getOption('pagedthumbs_width'), getOption('pagedthumbs_height'), NULL, NULL, NULL, NULL, true, NULL, NULL, NULL);
		}
	}

	function getOptionsSupported() {
		return array(gettext('Thumbs per page')								 => array('key'	 => 'pagedthumbs_imagesperpage', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Controls the number of images on a page. You might need to change this after switching themes to make it look better.")),
						gettext('Counter')												 => array('key'	 => 'pagedthumbs_counter', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If you want to show the counter 'x - y of z images'.")),
						gettext('Prevtext')												 => array('key'					 => 'pagedthumbs_prevtext', 'type'				 => OPTION_TYPE_TEXTBOX,
										'desc'				 => gettext("The text for the previous thumbs."), 'multilingual' => 1),
						gettext('Nexttext')												 => array('key'					 => 'pagedthumbs_nexttext', 'type'				 => OPTION_TYPE_TEXTBOX,
										'desc'				 => gettext("The text for the next thumbs."), 'multilingual' => 1),
						gettext('Crop width')											 => array('key'	 => 'pagedthumbs_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The thumb crop width is the maximum width when height is the shortest side")),
						gettext('Crop height')										 => array('key'	 => 'pagedthumbs_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("The thumb crop height is the maximum height when width is the shortest side")),
						gettext('Crop')														 => array('key'	 => 'pagedthumbs_crop', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If checked the thumbnail will be a centered portion of the image with the given width and height after being resized to thumb size (by shortest side). Otherwise, it will be the full image resized to thumb size (by shortest side).")),
						gettext('Placeholders')										 => array('key'	 => 'pagedthumbs_placeholders', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("if you want to use placeholder for layout reasons to fill up the thumbs if the number of thumbs does not match images per page. Recommended only for cropped thumbs.")),
						gettext('Page list')											 => array('key'	 => 'pagedthumbs_pagelist', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If you want the list of the pages to be shown.")),
						gettext('Pages list prev and next links')	 => array('key'	 => 'pagedthumbs_pagelistprevnext', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If you want to show previous and next thumb page links with the page list.")),
						gettext('Pages list length')							 => array('key'	 => 'pagedthumbs_pagelistlength', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("The number of links for the page list."))
		);
	}

}

class pagedThumbsNav {

	var $imagesperpage;
	var $counter;
	var $prev;
	var $next;
	var $width;
	var $height;
	var $crop;
	var $placeholders;
	var $showpagelist;
	var $pagelistprevnext;
	var $pagelistlength;
	var $totalimages;
	var $totalpages;
	var $images;
	var $currentpage;
	var $currentfloor;
	var $currentciel;
	var $currentimgnr;
	var $searchimages;
	var $prevpageimage;
	var $nextpageimage;

	/**
	 *
	 * @param unknown_type $imagesperpage
	 * @param unknown_type $counter
	 * @param unknown_type $prev
	 * @param unknown_type $next
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @param unknown_type $crop
	 * @param unknown_type $placeholders
	 * @param unknown_type $showpagelist
	 * @param unknown_type $pagelistprevnext
	 * @param unknown_type $pagelistlength
	 * @return pagedThumbsNav
	 */
	function pagedThumbsNav($imagesperpage = 0, $counter = false, $prev = '', $next = '', $width = NULL, $height = NULL, $crop = NULL, $placeholders = NULL, $showpagelist = false, $pagelistprevnext = false, $pagelistlength = 6) {
		global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
		if (is_null($crop)) {
			$this->crop = getOption("pagedthumbs_crop");
		} else {
			$this->crop = $crop;
		}
		if (empty($imagesperpage)) {
			$this->imagesperpage = getOption("pagedthumbs_imagesperpage");
		} else {
			$this->imagesperpage = $imagesperpage;
		}
		if (is_null($width)) {
			$this->width = getOption("pagedthumbs_width");
		} else {
			$this->width = $width;
		}
		if (is_null($height)) {
			$this->height = getOption("pagedthumbs_height");
		} else {
			$this->height = $height;
		}
		if (empty($prev)) {
			$this->prev = get_language_string(getOption("pagedthumbs_prevtext"));
		} else {
			$this->prev = html_decode($prev);
		}
		if (empty($next)) {
			$this->next = get_language_string(getOption("pagedthumbs_nexttext"));
		} else {
			$this->next = html_decode($next);
		}
		if (empty($counter)) {
			$this->counter = getOption("pagedthumbs_counter");
		}
		if (is_null($placeholders)) {
			$this->placeholders = getOption("pagedthumbs_placeholders");
		} else {
			$this->placeholders = $placeholders;
		}
		if (is_null($showpagelist)) {
			$this->showpagelist = getOption("pagedthumbs_pagelist");
		} else {
			$this->showpagelist = $showpagelist;
		}
		if (empty($pagelistlength)) {
			$this->pagelistlength = getOption("pagedthumbs_pagelistlength");
		} else {
			$this->pagelistlength = $pagelistlength;
		}
		if (is_null($pagelistprevnext)) {
			$this->pagelistprevnext = getOption("pagedthumbs_pagelistprevnext");
		} else {
			$this->pagelistprevnext = $pagelistprevnext;
		}
		// get the image of current album
		if (in_context(ZP_SEARCH_LINKED)) {
			if ($_zp_current_search->getNumImages() === 0) {
				$this->searchimages = false;
			} else {
				$this->searchimages = true;
			}
		} else {
			$this->searchimages = false;
		}

		if (in_context(ZP_SEARCH_LINKED) && $this->searchimages) {
			$this->images = $_zp_current_search->getImages();
		} else {
			$this->images = $_zp_current_album->getImages();
		}
		$this->currentimgnr = imageNumber();
		$this->totalimages = count($this->images);
		$this->totalpages = ceil($this->totalimages / $this->imagesperpage);
		$this->currentpage = floor(($this->currentimgnr - 1) / $this->imagesperpage) + 1;
		$this->currentciel = $this->currentpage * $this->imagesperpage - 1;
		$this->currentfloor = $this->currentciel - $this->imagesperpage + 1;
	}

// constructor end

	/**
	 * Gets the link to the previous thumbnail page
	 * @return string
	 */
	function getPrevThumbsLink() {
		global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
		$this->prevpageimage = ""; // define needed for page list
		if ($this->totalpages > 1) {
			$prevpageimagenr = ($this->currentpage * $this->imagesperpage) - ($this->imagesperpage + 1);
			if ($this->currentpage > 1) {
				if (is_array($this->images[$prevpageimagenr])) {
					$albumobj = newAlbum($this->images[$prevpageimagenr]['folder']);
					$this->prevpageimage = newImage($albumobj, $this->images[$prevpageimagenr]['filename']);
				} else {
					$this->prevpageimage = newImage($_zp_current_album, $this->images[$prevpageimagenr]);
				}
				return $this->prevpageimage->getImageLink();
			}
		}
	}

	/**
	 * Prints the link to the previous thumbnail page
	 *
	 */
	function printPrevThumbsLink() {
		if ($this->currentpage == 1) {
			echo "<div id=\"pagedthumbsnav-prevdisabled\">" . html_encode($this->prev);
		} else {
			echo "<div id=\"pagedthumbsnav-prev\">\n";
		}
		// Prev thumbnails - show only if there is a prev page
		echo "<a href=\"" . html_encode($this->getPrevThumbsLink()) . "\" title=\"" . gettext("previous thumbs") . "\">" . html_encode($this->prev) . "</a>\n";
		echo "</div>";
	}

	/**
	 * Gets the thumbnails and returns them as an array with objects
	 * @return array with objects
	 */
	function getThumbs() {
		global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
		$curimages = array_slice($this->images, $this->currentfloor, $this->imagesperpage);
		$thumbs = array();
		foreach ($curimages as $item) {
			if (is_array($item)) {
				$thumbs[] = newImage(newAlbum($item['folder']), $item['filename']);
			} else {
				$thumbs[] = newImage($_zp_current_album, $item);
			}
		}
		return $thumbs;
	}

	/**
	 * Prints the thumbnails
	 *
	 */
	function printThumbs() {
		global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
		echo "<div id='pagedthumbsimages'>";
		$thumbs = $this->getThumbs();
		//$thcount = count($thumbs); echo "thcount:".$thcount;
		$number = 0;
		foreach ($thumbs as $image) {
			if ($image->getID() == $_zp_current_image->getID()) {
				$css = " id='pagedthumbsnav-active' ";
			} else {
				$css = "";
			}
			echo "<a $css href=\"" . html_encode($image->getImageLink()) . "\" title=\"" . html_encode(strip_tags($image->getTitle())) . "\">";

			if ($this->crop) {
				$html = "<img src='" . html_encode(pathurlencode($image->getCustomImage(null, $this->width, $this->height, $this->width, $this->height, null, null, true))) . "' alt=\"" . html_encode(strip_tags($image->getTitle())) . "\" width='" . $this->width . "' height='" . $this->height . "' />";
			} else {
				$maxwidth = $this->width; // needed because otherwise getMaxSpaceContainer will use the values of the first image for all others, too
				$maxheight = $this->height;
				getMaxSpaceContainer($maxwidth, $maxheight, $image, true);
				$html = "<img src=\"" . html_encode(pathurlencode($image->getCustomImage(NULL, $maxwidth, $maxheight, NULL, NULL, NULL, NULL, true))) . "\" alt=\"" . html_encode(strip_tags($image->getTitle())) . "\" />";
			}
			echo zp_apply_filter('custom_image_html', $html, true);
			echo "</a>\n";
			$number++;
		}
		if ($this->placeholders) {
			if ($number != $this->imagesperpage) {
				$placeholdernr = $this->imagesperpage - ($number);
				for ($nr2 = 1; $nr2 <= $placeholdernr; $nr2++) {
					echo "<span class=\"placeholder\" style=\"width:" . $this->width . "px;height:" . $this->height . "px\"></span>";
				}
			}
		}
		echo "</div>";
	}

	/**
	 * Gets the link for the next page of thumbs
	 * @return string
	 */
	function getNextThumbsLink() {
		global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
		if ($this->totalpages > 1) {
			if ($this->currentpage < $this->totalpages) {
				$nextpageimagenr = $this->currentpage * $this->imagesperpage;
				if (is_array($this->images[$nextpageimagenr])) {
					$albumobj = newAlbum($this->images[$nextpageimagenr]['folder']);
					$this->nextpageimage = newImage($albumobj, $this->images[$nextpageimagenr]['filename']);
				} else {
					$this->nextpageimage = newImage($_zp_current_album, $this->images[$nextpageimagenr]);
				}
				return $this->nextpageimage->getImageLink();
			}
		}
	}

	/**
	 * Prints the link for the next page of thumbs
	 *
	 */
	function printNextThumbsLink() {
		if ($this->currentpage == $this->totalpages) {
			echo "<div id=\"pagedthumbsnav-nextdisabled\">" . html_encode($this->next);
		} else {
			echo "<div id=\"pagedthumbsnav-next\">\n";
		}
		echo "<a href=\"" . html_encode($this->getNextThumbsLink()) . "\" title=\"" . gettext("next thumbs") . "\">" . html_encode($this->next) . "</a>\n";
		echo "</div>\n";
	}

	/**
	 * Gets From image x to image y counter values
	 *
	 */
	function getCounter() {
		$fromimage = $this->currentfloor + 1;
		if ($this->totalimages < $this->currentciel) {
			$toimage = $this->totalimages;
		} else {
			$toimage = $this->currentciel + 1;
		}
		$counter = array("fromimage"	 => $fromimage, "toimage"		 => $toimage);
		return $counter;
	}

	/**
	 * Prints the counter
	 *
	 */
	function printCounter() {
		if ($this->counter) {
			$counter = $this->getCounter();
			echo "<p id=\"pagedthumbsnav-counter\">" . sprintf(gettext('Images %1$u-%2$u of %3$u (%4$u/%5$u)'), $counter["fromimage"], $counter["toimage"], $this->totalimages, $this->currentpage, $this->totalpages) . "</p>\n";
		}
	}

	/**
	 * Prints the pagelist for the thumb pages
	 *
	 */
	function printPagesList() {
		if ($this->showpagelist AND $this->totalpages > 1) {
			//$total = $this->totalpages;
			//$current = $this->currentpage;
			$navlen = sanitize_numeric($this->pagelistlength);
			$extralinks = 4;
			//$extralinks = $extralinks + 2;
			$len = floor(($navlen - $extralinks) / 2);
			$j = max(round($extralinks / 2), min($this->currentpage - $len - (2 - round($extralinks / 2)), $this->totalpages - $navlen + $extralinks - 1));
			$ilim = min($this->totalpages, max($navlen - round($extralinks / 2), $this->currentpage + floor($len)));
			$k1 = round(($j - 2) / 2) + 1;
			$k2 = $this->totalpages - round(($this->totalpages - $ilim) / 2);

			echo "<ul id=\"pagedthumbsnav-pagelist\">\n";
			// prev page
			if ($this->pagelistprevnext AND $this->totalpages > 1 AND is_object($this->prevpageimage)) {
				echo "<li><a href=\"" . html_encode($this->prevpageimage->getImageLink()) . "\" title=\"" . gettext("previous thumbs") . "\">" . html_encode($this->prev) . "</a></li>\n";
			}
			// 1st page
			$this->printPagedThumbsNavPagelink($this->imagesperpage, $this->searchimages, $this->images, $this->currentpage, 1, 1);

			// transitional page
			if ($j > 2) {
				$this->printPagedThumbsNavPagelink($this->imagesperpage, $this->searchimages, $this->images, $this->currentpage, $k1, "...");
			}
			// normal page
			for ($i = $j; $i <= $ilim; $i++) {
				$this->printPagedThumbsNavPagelink($this->imagesperpage, $this->searchimages, $this->images, $this->currentpage, $i, $i);
			}
			// transition page
			if ($i < $this->totalpages) {
				$this->printPagedThumbsNavPagelink($this->imagesperpage, $this->searchimages, $this->images, $this->currentpage, $i, "...");
			}
			// last page
			if ($i <= $this->totalpages) {
				$this->printPagedThumbsNavPagelink($this->imagesperpage, $this->searchimages, $this->images, $this->currentpage, $this->totalpages, $this->totalpages);
			}
			// next page
			if ($this->pagelistprevnext AND $this->totalpages > 1 AND is_object($this->nextpageimage)) {
				echo "<li><a href=\"" . html_encode($this->nextpageimage->getImageLink()) . "\" title=\"" . gettext("next thumbs") . "\">" . html_encode($this->next) . "</a></li>\n";
			}
			echo "</ul>\n";
		}
	}

	/* Helper function for printPagedThumbsNav(). Variables are passed from within that function! Not for standalone use!
	 *
	 *  @param int $imagesperpage How many thumbs you want to display per list page
	 *  @param bool $searchimages if we are in search and have images
	 *  @param string $images array of images
	 *  @param int $currentpage number of the current paged thumbs page
	 *  @param int $i The number of the page to print a link
	 *  @param string $li/**
	 */

	function printPagedThumbsNavPagelink($i, $linktext) {
		global $_zp_gallery, $_zp_current_album;
		$i = $i;
		$linktex = $linktext;
		$imagenr = ($i * $this->imagesperpage) - ($this->imagesperpage);
		if (is_array($this->images[$imagenr])) {
			$albumobj = newAlbum($this->images[$imagenr]['folder']);
			$pageimage = newImage($albumobj, $this->images[$imagenr]['filename']);
		} else {
			$pageimage = newImage($_zp_current_album, $this->images[$imagenr]);
		}
		if ($this->currentpage == $i) {
			echo "<li class=\"pagedthumbsnav-pagelistactive\">" . html_encode($linktext) . "</a>\n";
		} else {
			echo "<li><a href=\"" . html_encode($pageimage->getImageLink()) . "\" title=\"Seite " . $i . "\">" . html_encode($linktext) . "</a></li>\n";
		}
	}

}

// class end

/**
 * Prints a paged thumbnail navigation to be used on a theme's image.php, independent of the album.php's thumbs loop
 *
 * NOTE: With version 1.0.2 $size is no longer an option for this plugin. This plugin now uses the new maxspace function if cropping set to false.
 *
 * The function contains some predefined CSS ids you can use for styling.
 * NOTE: In 1.0.3 a extra div around the thumbnails has been added: <div id="pagedthumbsimages">.
 * The function prints the following HTML:
 *
 * <div id="pagedthumbsnav">
 *
 * <div id="pagedthumbsnav-prev">
 * <a href="">Previous thumbnail list</a>
 * </div> (if the link is inactive id="pagedthumbsnav-prevdisabled", you can hide it via CSS if needed)
 *
 * <div id="pagedthumbsimages">
 * <a href=""><img></a> (...) (the active thumb has class="pagedthumbsnav-active")
 * </div>
 *
 * <div id="pagedthumbsnav-next">
 * <a href="">Next thumbnail list</a> (if the link is inactive id="pagedthumbsnav-nextdisabled", you can hide it via CSS if needed)
 * </div>
 *
 * <p id="pagethumbsnav-counter>Images 1 - 10 of 20 (1/3)</p> (optional)
 * <ul id="pagedthumbsnav-pagelist"> (optional)
 * <li><a href=""></a></li> (active page link has css class "pagedthumbsnav-pagelistactive" attached)
 * </ul>
 *
 * </div>
 *
 * You can of course build your own order of the elements as this plugin is with version 1.2.7 based on a class. Make a custom theme function and change the order of the functions calls within.
 * Alternatively you can use also use the "get" class methods to build something completely customized.
 *
 * @param int $imagesperpage How many thumbs you want to display per list page
 * @param bool $counter If you want to show the counter of the type "Images 1 - 10 of 20 (1/3)"
 * @param string $prev The previous thumb list link text
 * @param string $next The next thumb list link text
 * @param int $width The thumbnail crop width, if set to NULL the general admin setting is used. If cropping is FALSE this is the maxwidth of the thumb
 * @param int $height The thumbnail crop height, if set to NULL the general admin setting is used. If cropping is FALSE this is the maxwheight of the thumb
 * @param bool $crop Enter 'true' or 'false' to override the admin plugin option setting, enter NULL to use the admin plugin option (default)
 * @param bool $placeholders Enter 'true' or 'false' if you want to use placeholder for layout reasons if teh the number of thumbs does not match $imagesperpage. Recommended only for cropped thumbs. This is printed as an empty <span></span> whose width and height are set via inline css. The remaining needs to be style via the css file and can be addressed via  "#pagedthumbsimages span".
 * @param bool $showpagelist Enter 'true' or 'false' if you want to a list of the pages available. Can be styled via  "#pagedthumbsnav-pagelist".
 * @param bool $showprevnext If you want to show the prev and next links with the pagelist
 * @param int $navlen How many page links should be shown (not that there will be dotted ransition links like this: 1 2 3 4 ... 30).
 *
 */
function printPagedThumbsNav($imagesperpage = '', $counter = false, $prev = '', $next = '', $width = NULL, $height = NULL, $crop = NULL, $placeholders = NULL, $showpagelist = false, $pagelistprevnext = false, $pagelistlength = 6) {
	$pagedthumbsobj = new pagedThumbsNav($imagesperpage, $counter, $prev, $next, $width, $height, $crop, $placeholders, $showpagelist, $pagelistprevnext, $pagelistlength);
	echo "<div id=\"pagedthumbsnav\">\n";
	//$thumbs = $pagedthumbsobj->getThumbs();
	//echo "<pre>"; print_r($thumbs); echo "</pre>";
	$pagedthumbsobj->printPrevThumbsLink();
	$pagedthumbsobj->printThumbs();
	$pagedthumbsobj->printNextThumbsLink();
	$pagedthumbsobj->printCounter();
	$pagedthumbsobj->printPagesList();
	echo "</div>\n";
}

?>