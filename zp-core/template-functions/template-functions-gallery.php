<?php
/**
 * Gallery related template functions
 * 
 * @since 1.7 moved to separate file from template-functions.php
 * 
 * @package zpcore\functions\template
 */

/**
 * Returns the raw title of the gallery.
 *
 * @return string
 */
function getGalleryTitle() {
	global $_zp_gallery;
	return $_zp_gallery->getTitle();
}

/**
 * Returns a text-only title of the gallery.
 *
 * @return string
 */
function getBareGalleryTitle() {
	return getBare(getGalleryTitle());
}

/**
 * Prints the title of the gallery.
 */
function printGalleryTitle() {
	echo html_encodeTagged(getGalleryTitle());
}

function printBareGalleryTitle() {
	echo html_encode(getBareGalleryTitle());
}

/**
 * Returns the raw description of the gallery.
 *
 * @return string
 */
function getGalleryDesc() {
	global $_zp_gallery;
	return $_zp_gallery->getDesc();
}

/**
 * Returns a text-only description of the gallery.
 *
 * @return string
 */
function getBareGalleryDesc() {
	return getBare(getGalleryDesc());
}

/**
 * Prints the description of the gallery.
 */
function printGalleryDesc() {
	echo html_encodeTagged(getGalleryDesc());
}

function printBareGalleryDesc() {
	echo html_encode(getBareGalleryDesc());
}

/**
 * Returns the name of the parent website as set by the "Website Title" option
 * on the gallery options tab. Use this if Zenphoto is only a part of your website.
 * 
 * @since 1.6
 * 
 * @return string
 */
function getParentSiteTitle() {
	global $_zp_gallery;
	return $_zp_gallery->getParentSiteTitle();
}

/**
 * Returns the URL of the main website as set by the "Website URL" option
 * on the gallery options tab. Use this if Zenphoto is only a part of your website.
 * 
 * @since 1.6
 * 
 * @return string
 */
function getParentSiteURL() {
	global $_zp_gallery;
	return $_zp_gallery->getParentSiteURL();
}

/**
 * @deprecated 2.0: Use getParentSiteTitle() instead
 * @return string
 */
function getMainSiteName() {
	deprecationNotice(gettext('Use getParentSiteTitle() instead'));
	return getParentSiteTitle();
}

/**
 * @deprecated 2.0: Use getParentSiteURL() instead
 * @return string
 */
function getMainSiteURL() {
	deprecationNotice(gettext('Use getParentSiteURL() instead'));
	return getParentSiteURL();
}

/**
 * Returns the URL of the main gallery index page. If a custom index page is set this returns that page.
 * So this is not necessarily the home page of the site!
 * @return string
 */
function getGalleryIndexURL() {
	global $_zp_current_album, $_zp_gallery_page;
	$page = 1;
	if (in_context(ZP_ALBUM) && $_zp_gallery_page != 'index.php') {
		$album = $_zp_current_album->getUrParent();
		$page = $album->getGalleryPage();
	}
	if (!$link = getCustomGalleryIndexURL($page)) {
		$link = getStandardGalleryIndexURL($page);
	}
	return filter::applyFilter('getLink', $link, 'index.php', NULL);
}

/**
 * Returns the url to the standard gallery index.php page
 *
 * @see getGalleryIndexURL()
 *
 * @param int $page Pagenumber to append
 * @param bool $webpath host path to be prefixed. If "false" is passed you will get a localized "WEBPATH"
 * @return string
 */
function getStandardGalleryIndexURL($page = 1, $webpath = null) {
	if ($page > 1) {
		return rewrite_path('/' . _PAGE_ . '/' . $page . '/', "/index.php?" . "page=" . $page, $webpath);
	} else {
		if (is_null($webpath)) {
			if (class_exists('seo_locale')) {
				$webpath = seo_locale::localePath();
			} else {
				$webpath = WEBPATH;
			}
		}
		return $webpath . "/";
	}
}

/**
 * Gets the custom gallery index url if one is set, otherwise false
 *
 * @see getGalleryIndexURL()
 *
 * @global array $_zp_conf_vars
 * @param int $page Pagenumber for pagination
 * @param bool $webpath host path to be prefixed. If "false" is passed you will get a localized "WEBPATH"
 * @return string
 */
function getCustomGalleryIndexURL($page = 1, $webpath = null) {
	$custom_index = getOption('custom_index_page');
	if ($custom_index) {
		$link = getCustomPageURL($custom_index, '', $webpath);
		if ($page > 1) {
			if (MOD_REWRITE) {
				$link .= $page . '/';
			} else {
				$link .= "&amp;page=" . $page;
			}
		}
		return $link;
	}
	return false;
}

/**
 * Returns the name to the individual custom gallery index page name if set,
 * otherwise returns generic custom gallery page "gallery.php" that is widely supported by themes
 * If you need to check if there is an indovidual custom_index_page set use
 * `getOption('custom_index_page')` or `getCustomGalleryIndexURL()`
 *
 * @return string
 */
function getCustomGalleryIndexPage() {
	$custom_index = getOption('custom_index_page');
	if ($custom_index) {
		return $custom_index . '.php';
	}
	return 'gallery.php';
}

/**
 * If a custom gallery index page is set this first prints a link to the actual site index (home page = index.php)
 * followed by the gallery index page link. Otherwise just the gallery index link
 *
 * @since 1.4.9
 * @param string $after Text to append after and outside the link for breadcrumbs
 * @param string $text Name of the link, if NULL "Gallery" is used
 * @param bool $printHomeURL In case of a custom gallery index, display breadcrumb with home link (default is true)
 */
function printGalleryIndexURL($after = NULL, $text = NULL, $printHomeURL = true) {
	global $_zp_gallery_page;
	if (is_null($text)) {
		$text = gettext('Gallery');
	}
	$customgalleryindex = getOption('custom_index_page');
	if ($customgalleryindex && $printHomeURL) {
		printSiteHomeURL($after);
	}
	if ($_zp_gallery_page == getCustomGalleryIndexPage()) {
		$after = NULL;
	}
	if (!$customgalleryindex || ($customgalleryindex && in_array($_zp_gallery_page, array('image.php', 'album.php', getCustomGalleryIndexPage())))) {
		printLinkHTML(getGalleryIndexURL(), $text, $text, 'galleryindexurl');
		echo $after;
	}
}


/**
 * Returns the home page link (WEBPATH) to the Zenphoto theme index.php page
 * Use in breadcrumbs if the theme uses a custom gallery index page so the gallery is not the site's home page
 *
 * @since 1.4.9
 * @global string $_zp_gallery_page
 * @return string
 */
function getSiteHomeURL() {
	return WEBPATH . '/';
}

/**
 * Prints the home page link (WEBPATH with trailing slash) to a Zenphoto theme index.php page
 * Use in breadcrumbs if the theme uses a custom gallery index page so the gallery is not the site's home page
 *
 * @param string $after Text after and outside the link for breadcrumbs
 * @param string $text Text of the link, if NULL "Home"
 */
function printSiteHomeURL($after = NULL, $text = NULL) {
	global $_zp_gallery_page;
	if ($_zp_gallery_page == 'index.php') {
		$after = '';
	}
	if (is_null($text)) {
		$text = gettext('Home');
	}
	printLinkHTML(getSiteHomeURL(), $text, $text, 'homeurl');
	echo $after;
}

/**
 * If the privacy page url option is set this prints a link to it
 * @param string $before To print before the link
 * @param string $after To print after the link
 */
function printPrivacyPageLink($before = null, $after = null) {
	$data = getDataUsageNotice();
	if (!empty($data['url'])) {
		echo $before;
		printLinkHTML($data['url'], $data['linktext'], $data['linktext'], null, null);
		echo $after;
	}
}

/**
 * Returns the number of albums.
 *
 * @return int
 */
function getNumAlbums() {
	global $_zp_gallery, $_zp_current_album, $_zp_current_search;
	if (in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getNumAlbums();
	} else if (in_context(ZP_ALBUM)) {
		return $_zp_current_album->getNumAlbums();
	} else {
		return $_zp_gallery->getNumAlbums();
	}
}

/**
 * Returns the name of the currently active theme
 *
 * @return string
 */
function getCurrentTheme() {
	global $_zp_gallery;
	return $_zp_gallery->getCurrentTheme();
}

/**
 * WHILE next_album(): context switches to Album.
 * If we're already in the album context, this is a sub-albums loop, which,
 * quite simply, changes the source of the album list.
 * Switch back to the previous context when there are no more albums.

 * Returns true if there are albums, false if none
 *
 * @param bool $all true to go through all the albums
 * @param bool $mine override the password checks
 * @return bool
 * @since 0.6
 */
function next_album($all = false, $mine = NULL) {
	global $_zp_albums, $_zp_gallery, $_zp_current_album, $_zp_page, $_zp_current_album_restore, $_zp_current_search;
	if (is_null($_zp_albums)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_albums = $_zp_current_search->getAlbums($all ? 0 : $_zp_page, NULL, NULL, true, $mine);
		} else if (in_context(ZP_ALBUM)) {
			$_zp_albums = $_zp_current_album->getAlbums($all ? 0 : $_zp_page, NULL, NULL, true, $mine);
		} else {
			$_zp_albums = $_zp_gallery->getAlbums($all ? 0 : $_zp_page, NULL, NULL, true, $mine);
		}
		if (empty($_zp_albums)) {
			return NULL;
		}
		$_zp_current_album_restore = $_zp_current_album;
		$_zp_current_album = AlbumBase::newAlbum(array_shift($_zp_albums), true, true);
		save_context();
		add_context(ZP_ALBUM);
		return true;
	} else if (empty($_zp_albums)) {
		$_zp_albums = NULL;
		$_zp_current_album = $_zp_current_album_restore;
		restore_context();
		return false;
	} else {
		$_zp_current_album = AlbumBase::newAlbum(array_shift($_zp_albums), true, true);
		return true;
	}
}

/**
 * Returns the number of the current page without printing it.
 *
 * @return int
 */
function getCurrentPage() {
	global $_zp_page;
	return $_zp_page;
}

/**
 * Gets an array of the album ids of all accessible albums (publich or user dependend)
 *
 * @param object $obj from whence to get the albums
 * @param array $albumlist collects the list
 * @param bool $scan force scan for new images in the album folder
 */
function getAllAccessibleAlbums($obj, &$albumlist, $scan) {
	global $_zp_gallery;
	$locallist = $obj->getAlbums();
 foreach ($locallist as $folder) {
		$album = AlbumBase::newAlbum($folder);
		If (!$album->isDynamic() && $album->checkAccess()) {
			if ($scan)
				$album->getImages();
			$albumlist[] = $album->getID();
			getAllAccessibleAlbums($album, $albumlist, $scan);
		}
	}
}

/**
 * Returns the number of pages for the current object
 *
 * @param bool $one_image_page set to true if your theme collapses all image thumbs
 * or their equivalent to one page. This is typical with flash viewer themes
 *
 * @return int
 */
function getTotalPages($one_image_page = false) {
	global $_zp_gallery, $_zp_zenpage, $_zp_current_category;
	if (in_context(ZP_ALBUM | ZP_SEARCH)) {
		if ($one_image_page === true) {
			return 1;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
			$imageCount = getNumImages();
			if ($one_image_page) {
				$imageCount = 0;
			}
			$images_per_page = max(1, getOption('images_per_page'));
			$pageCount = ($pageCount + ceil(($imageCount - getFirstPageImages($one_image_page)) / $images_per_page));
			return $pageCount;
		}
	} else if (in_context(ZP_INDEX)) {
		if ($_zp_gallery->getAlbumsPerPage() != 0) {
			return $_zp_gallery->getTotalPages();
		} else {
			return NULL;
		}
		return NULL;
	} else if (isset($_zp_zenpage)) {
		if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
			return $_zp_current_category->getTotalNewsPages();
		} else {
			return $_zp_zenpage->getTotalNewsPages();
		}
	}
}

/**
 * Returns the URL of the page number passed as a parameter
 *
 * @param int $page Which page is desired
 * @param int $total How many pages there are.
 * @return int
 */
function getPageNumURL($page, $total = null) {
	global $_zp_current_album, $_zp_gallery, $_zp_current_search, $_zp_gallery_page, $_zp_conf_vars;
	if (is_null($total)) {
		$total = getTotalPages();
	}
	if ($page <= 0 || $page > $total) {
		return NULL;
	}
	if (in_context(ZP_SEARCH)) {
		$searchwords = $_zp_current_search->codifySearchString();
		$searchdate = $_zp_current_search->getSearchDate();
		$searchfields = $_zp_current_search->getSearchFields(true);
		$searchpagepath = SearchEngine::getSearchURL($searchwords, $searchdate, $searchfields, $page, array('albums' => $_zp_current_search->getAlbumList()));
		return $searchpagepath;
	} else if (in_context(ZP_ALBUM)) {
		return $_zp_current_album->getLink($page);
	} else if (in_array($_zp_gallery_page, array('index.php', 'album.php', 'image.php'))) {
		if (in_context(ZP_INDEX)) {
			$pagination1 = '/';
			$pagination2 = 'index.php';
			if ($page > 1) {
				$pagination1 .= _PAGE_ . '/' . $page . '/';
				$pagination2 .= '?page=' . $page;
			}
		} else {
			return NULL;
		}
	} else {
		// handle custom page
		$pg = stripSuffix($_zp_gallery_page);
		if (array_key_exists($pg, $_zp_conf_vars['special_pages'])) {
			$pagination1 = preg_replace('~^_PAGE_/~', _PAGE_ . '/', $_zp_conf_vars['special_pages'][$pg]['rewrite']) . '/';
		} else {
			$pagination1 = '/' . _PAGE_ . '/' . $pg . '/';
		}
		$pagination2 = 'index.php?p=' . $pg;
		if ($page > 1) {
			$pagination1 .= $page . '/';
			$pagination2 .= '&page=' . $page;
		}
	}
	return filter::applyFilter('getLink', rewrite_path($pagination1, $pagination2), $_zp_gallery_page, $page);
}

/**
 * Returns true if there is a next page
 *
 * @return bool
 */
function hasNextPage() {
	return (getCurrentPage() < getTotalPages());
}

/**
 * Returns the URL of the next page. Use within If or while loops for pagination.
 *
 * @return string
 */
function getNextPageURL() {
	return getPageNumURL(getCurrentPage() + 1);
}

/**
 * Prints the URL of the next page.
 *
 * @param string $text text for the URL
 * @param string $title Text for the HTML title
 * @param string $class Text for the HTML class
 * @param string $id Text for the HTML id
 */
function printNextPageURL($text, $title = NULL, $class = NULL, $id = NULL) {
	if (hasNextPage()) {
		printLinkHTML(getNextPageURL(), $text, $title, $class, $id);
	} else {
		echo "<span class=\"disabledlink\">$text</span>";
	}
}

/**
 * Returns TRUE if there is a previous page. Use within If or while loops for pagination.
 *
 * @return bool
 */
function hasPrevPage() {
	return (getCurrentPage() > 1);
}

/**
 * Returns the URL of the previous page.
 *
 * @return string
 */
function getPrevPageURL() {
	return getPageNumURL(getCurrentPage() - 1);
}

/**
 * Returns the URL of the previous page.
 *
 * @param string $text The linktext that should be printed as a link
 * @param string $title The text the html-tag "title" should contain
 * @param string $class Insert here the CSS-class name you want to style the link with
 * @param string $id Insert here the CSS-ID name you want to style the link with
 */
function printPrevPageURL($text, $title = NULL, $class = NULL, $id = NULL) {
	if (hasPrevPage()) {
		printLinkHTML(getPrevPageURL(), $text, $title, $class, $id);
	} else {
		echo "<span class=\"disabledlink\">$text</span>";
	}
}

/**
 * Prints a page navigation including previous and next page links
 *
 * @param string $prevtext Insert here the linktext like 'previous page'
 * @param string $separator Insert here what you like to be shown between the prev and next links
 * @param string $nexttext Insert here the linktext like "next page"
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 */
function printPageNav($prevtext, $separator, $nexttext, $class = 'pagenav', $id = NULL) {
	echo "<div" . (($id) ? " id=\"$id\"" : "") . " class=\"$class\">";
	printPrevPageURL($prevtext, gettext("Previous Page"));
	echo " $separator ";
	printNextPageURL($nexttext, gettext("Next Page"));
	echo "</div>\n";
}

/**
 * Prints a list of all pages.
 *
 * @param string $class the css class to use, "pagelist" by default
 * @param string $id the css id to use
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printPageList($class = 'pagelist', $id = NULL, $navlen = 9) {
	printPageListWithNav(null, null, false, false, $class, $id, false, $navlen);
}

/**
 * returns a page nav list.
 *
 * @param bool $_zp_one_image_page set to true if there is only one image page as, for instance, in flash themes
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $current the current page
 * @param int $total total number of pages
 *
 */
function getPageNavList($_zp_one_image_page, $navlen, $firstlast, $current, $total) {
	$result = array();
	if (hasPrevPage()) {
		$result['prev'] = getPrevPageURL();
	} else {
		$result['prev'] = NULL;
	}
	if ($firstlast) {
		$result[1] = getPageNumURL(1, $total);
	}

	if ($navlen == 0) {
		$navlen = $total;
	}
	$extralinks = 2;
	if ($firstlast)
		$extralinks = $extralinks + 2;
	$len = floor(($navlen - $extralinks) / 2);
	$j = max(round($extralinks / 2), min($current - $len - (2 - round($extralinks / 2)), $total - $navlen + $extralinks - 1));
	$ilim = min($total, max($navlen - round($extralinks / 2), $current + floor($len)));
	$k1 = round(($j - 2) / 2) + 1;
	$k2 = $total - round(($total - $ilim) / 2);

	for ($i = $j; $i <= $ilim; $i++) {
		$result[$i] = getPageNumURL($i, $total);
	}
	if ($firstlast) {
		$result[$total] = getPageNumURL($total, $total);
	}
	if (hasNextPage()) {
		$result['next'] = getNextPageURL();
	} else {
		$result['next'] = NULL;
	}
	return $result;
}

/**
 * Prints a full page navigation including previous and next page links with a list of all pages in between.
 *
 * @param string $prevtext Insert here the linktext like 'previous page'
 * @param string $nexttext Insert here the linktext like 'next page'
 * @param bool $_zp_one_image_page set to true if there is only one image page as, for instance, in flash themes
 * @param string $nextprev set to true to get the 'next' and 'prev' links printed
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printPageListWithNav($prevtext, $nexttext, $_zp_one_image_page = false, $nextprev = true, $class = 'pagelist', $id = NULL, $firstlast = true, $navlen = 9) {
	$current = getCurrentPage();
	$total = max(1, getTotalPages($_zp_one_image_page));
	$nav = getPageNavList($_zp_one_image_page, $navlen, $firstlast, $current, $total);
	if ($total > 1) {
		?>
		<div <?php if ($id) echo ' id="'.$id.'"'; ?> class="<?php echo $class; ?>">
			<ul class="<?php echo $class; ?>">
				<?php
				$prev = $nav['prev'];
				unset($nav['prev']);
				$next = $nav['next'];
				unset($nav['next']);
				if ($nextprev) {
					?>
					<li class="prev">
						<?php
						if ($prev) {
							printLinkHTML($prev, html_encode($prevtext), gettext('Previous Page'));
						} else {
							?>
							<span class="disabledlink"><?php echo html_encode($prevtext); ?></span>
							<?php
						}
						?>
					</li>
					<?php
				}
				$last = NULL;
				if ($firstlast) {
					?>
					<li class="<?php
					if ($current == 1)
						echo 'current';
					else
						echo 'first';
					?>">
								<?php
								if ($current == 1) {
									echo '1';
								} else {
									printLinkHTML($nav[1], 1, gettext("Page 1"));
								}
								?>
					</li>
					<?php
					$last = 1;
					unset($nav[1]);
				}
				foreach ($nav as $i => $link) {
					$d = $i - $last;
					if ($d > 2) {
						?>
						<li>
							<?php
							$k1 = $i - (int) (($i - $last) / 2);
							printLinkHTML(getPageNumURL($k1, $total), '...', sprintf(ngettext('Page %u', 'Page %u', $k1), $k1));
							?>
						</li>
						<?php
					} else if ($d == 2) {
						?>
						<li>
							<?php
							$k1 = $last + 1;
							printLinkHTML(getPageNumURL($k1, $total), $k1, sprintf(ngettext('Page %u', 'Page %u', $k1), $k1));
							?>
						</li>
						<?php
					}
					?>
					<li<?php if ($current == $i) echo ' class="current"'; ?>>
						<?php
						if ($i == $current) {
							echo $i;
						} else {
							$title = sprintf(ngettext('Page %1$u', 'Page %1$u', $i), $i);
							printLinkHTML($link, $i, $title);
						}
						?>
					</li>
					<?php
					$last = $i;
					unset($nav[$i]);
					if ($firstlast && count($nav) == 1) {
						break;
					}
				}
				if ($firstlast) {
					foreach ($nav as $i => $link) {
						$d = $i - $last;
						if ($d > 2) {
							$k1 = $i - (int) (($i - $last) / 2);
							?>
							<li>
								<?php printLinkHTML(getPageNumURL($k1, $total), '...', sprintf(ngettext('Page %u', 'Page %u', $k1), $k1)); ?>
							</li>
							<?php
						} else if ($d == 2) {
							$k1 = $last + 1;
							?>
							<li>
								<?php printLinkHTML(getPageNumURL($k1, $total), $k1, sprintf(ngettext('Page %u', 'Page %u', $k1), $k1)); ?>
							</li>
							<?php
						}
						?>
						<li class="last<?php if ($current == $i) echo ' current'; ?>">
							<?php
							if ($current == $i) {
								echo $i;
							} else {
								printLinkHTML($link, $i, sprintf(ngettext('Page %u', 'Page %u', $i), $i));
							}
							?>
						</li>
						<?php
					}
				}
				if ($nextprev) {
					?>
					<li class="next">
						<?php
						if ($next) {
							printLinkHTML($next, html_encode($nexttext), gettext('Next Page'));
						} else {
							?>
							<span class="disabledlink"><?php echo html_encode($nexttext); ?></span>
							<?php
						}
						?>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}
}



/**
 * Display the site or image copyright notice if defined and display is enabled
 * 
 * @since 1.5.8
 * @since 1.6 Also handles the image copyright notice
 * 
 * @global obj $_zp_gallery
 * @param string $before Text to print before it
 * @param string $after Text to print after it
 * œparam bool $linked Default true to use the copyright URL if defined
 */
function printCopyrightNotice($before = '', $after = '', $linked = true, $type = 'gallery' ) {
	global $_zp_gallery, $_zp_current_image;
	switch($type) {
		default:
		case 'gallery': 
			$copyright_notice = $_zp_gallery->getCopyrightNotice();
			$copyrigth_url = $_zp_gallery->getCopyrightURL();
			$copyright_notice_enabled = getOption('display_copyright_notice');
			break;
		case 'image':
			if (!in_context(ZP_IMAGE)) {
				return false;
			}
			$copyright_notice = $_zp_current_image->getCopyrightNotice();
			$copyrigth_url = $_zp_current_image->getCopyrightURL();
			$copyright_notice_enabled = getOption('display_copyright_image_notice');
			break;
	}
	if (!empty($copyright_notice) && $copyright_notice_enabled) {
		$notice = $before . $copyright_notice . $after;
		if ($linked && !empty($copyrigth_url)) {
			printLinkHTML($copyrigth_url, $notice, $notice);
		} else {
			echo $notice;
		}
	}
}

/**
 * Display the site copyright notice if defined and display is enabled
 * 
 * @since 1.6 - Added as shortcut to the general printCopyRightNotice
 * 
 * @param string $before Text to print before it
 * @param string $after Text to print after it
 * œparam bool $linked Default true to use the copyright URL if defined
 */
function printGalleryCopyrightNotice($before = '', $after = '', $linked = true) {
	printCopyrightNotice($before, $after, $linked, 'gallery' );
}

/**
 * Display the image copyright notice if defined and display iss enabled
 * 
 * @since 1.6 - Added as shortcut to the general printCopyRightNotice
 * 
 * @param string $before Text to print before it
 * @param string $after Text to print after it
 * œparam bool $linked Default true to use the copyright URL if defined
 */
function printImageCopyrightNotice($before = '', $after = '', $linked = true) {
	printCopyrightNotice($before, $after, $linked, 'image' );
}

/**
 * Gets the current page number if it is larger than 1 for use on paginated pages for SEO reason to avoid duplicate titles
 * 
 * @since 1.6
 * 
 * @param string $before Text to add before the page number. Default ' (';
 * @param string $after Text to add ager the page number. Default ')';
 * @return string
 */
function getCurrentPageAppendix($before = ' (', $after =')') {
	if(getCurrentPage() > 1) {
		return $before . getCurrentPage() . $after;
	}
}
/**
 * Prints the current page number if it is larger than 1 for use on paginated pages for SEO reason to avoid duplicate titles
 * 
 * @since 1.6
 * 
 * @param string $before Text to add before the page number. Default ' (';
 * @param string $after Text to add ager the page number. Default ')';
 */
function printCurrentPageAppendix($before = ' (', $after =')') {
	echo getCurrentPageAppendix($before, $after);
}