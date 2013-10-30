<?php
/**
 * Functions used to display content in themes.
 * @package functions
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions.php');
if (!defined('SEO_FULLWEBPATH')) {
	define('SEO_FULLWEBPATH', FULLWEBPATH);
	define('SEO_WEBPATH', WEBPATH);
}

//******************************************************************************
//*** Template Functions *******************************************************
//******************************************************************************

/* * * Generic Helper Functions ************ */
/* * *************************************** */

/**
 * Returns the zenphoto version string
 */
function getVersion() {
	return ZENPHOTO_VERSION . ' [' . ZENPHOTO_RELEASE . ']';
}

/**
 * Prints the zenphoto version string
 */
function printVersion() {
	echo getVersion();
}

/**
 * Print any Javascript required by zenphoto.
 */
function printZenJavascripts() {
	global $_zp_current_album;
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/zenphoto.js"></script>
	<?php
	if (zp_loggedin()) {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			var deleteAlbum1 = "<?php echo gettext("Are you sure you want to delete this entire album?"); ?>";
			var deleteAlbum2 = "<?php echo gettext("Are you Absolutely Positively sure you want to delete the album? THIS CANNOT BE UNDONE!"); ?>";
			var deleteImage = "<?php echo gettext("Are you sure you want to delete the image? THIS CANNOT BE UNDONE!"); ?>";
			var deleteArticle = "<?php echo gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!"); ?>";
			var deletePage = "<?php echo gettext("Are you sure you want to delete this page? THIS CANNOT BE UNDONE!"); ?>";
			// ]]> -->
		</script>
		<?php
	}
}

/**
 * Prints the clickable drop down toolbox on any theme page with generic admin helpers
 *
 */
function adminToolbox() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery_page, $_zp_gallery, $_zp_current_admin_obj;
	if (zp_loggedin()) {
		$protocol = SERVER_PROTOCOL;
		if ($protocol == 'https_admin') {
			$protocol = 'https';
		}
		$zf = $protocol . '://' . $_SERVER['HTTP_HOST'] . WEBPATH . "/" . ZENFOLDER;
		$id = 'admin';
		$dataid = 'admin_data';
		$page = getCurrentPage();
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function newAlbum(folder, albumtab) {
				var album = prompt('<?php echo gettext('New album name?'); ?>', '<?php echo gettext('new album'); ?>');
				if (album) {
					launchScript('<?php echo $zf; ?>/admin-edit.php', ['action=newalbum', 'album=' + encodeURIComponent(folder), 'name=' + encodeURIComponent(album), 'albumtab=' + albumtab, 'XSRFToken=<?php echo getXSRFToken('newalbum'); ?>']);
				}
			}
			// ]]> -->
		</script>
		<div id="<?php echo $id; ?>">
			<h3><a href="javascript:toggle('<?php echo $dataid; ?>');"><?php echo gettext('Admin Toolbox'); ?></a></h3>
		</div>
		<div id="<?php echo $dataid; ?>" style="display: none;">

			<ul style="list-style-type: none;" >
				<?php
				if (zp_loggedin(OVERVIEW_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin.php', gettext("Overview"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(UPLOAD_RIGHTS | FILES_RIGHTS | THEMES_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-upload.php', gettext("Upload"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(ALBUM_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-edit.php', gettext("Albums"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				zp_apply_filter('admin_toolbox_global', $zf);

				if (zp_loggedin(TAGS_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-tags.php', gettext("Tags"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(USER_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-users.php', gettext("Users"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(OPTIONS_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-options.php?tab=general', gettext("Options"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(THEMES_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-themes.php', gettext("Themes"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}
				if (zp_loggedin(ADMIN_RIGHTS)) {
					?>
					<li>
						<?php printLink($zf . '/admin-plugins.php', gettext("Plugins"), NULL, NULL, NULL); ?>
					</li>
					<li>
						<?php printLink($zf . '/admin-logs.php', gettext("Logs"), NULL, NULL, NULL); ?>
					</li>
					<?php
				}

				$gal = getOption('custom_index_page');
				if (empty($gal) || !file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . $_zp_gallery->getCurrentTheme() . '/' . internalToFilesystem($gal) . '.php')) {
					$gal = 'index.php';
				} else {
					$gal .= '.php';
				}
				$inImage = false;
				switch ($_zp_gallery_page) {
					case 'index.php':
					case $gal:
						// script is either index.php or the gallery index page
						if (zp_loggedin(ADMIN_RIGHTS)) {
							?>
							<li>
								<?php printLink($zf . '/admin-edit.php?page=edit', gettext("Sort Gallery"), NULL, NULL, NULL); ?>
							</li>
							<?php
						}
						if (zp_loggedin(UPLOAD_RIGHTS)) {
							// admin has upload rights, provide an upload link for a new album
							if (GALLERY_SESSION) { // XSRF defense requires sessions
								?>
								<li>
									<a href="javascript:newAlbum('',true);"><?php echo gettext("New Album"); ?></a>
								</li>
								<?php
							}
						}
						if ($_zp_gallery_page == 'index.php') {
							$redirect = '';
						} else {
							$redirect = "&amp;p=" . urlencode(stripSuffix($_zp_gallery_page));
						}
						if ($page > 1) {
							$redirect .= "&amp;page=$page";
						}
						zp_apply_filter('admin_toolbox_gallery', $zf);
						break;
					case 'image.php':
						$inImage = true; // images are also in albums[sic]
					case 'album.php':
						// script is album.php
						$albumname = $_zp_current_album->name;
						if ($_zp_current_album->isMyItem(ALBUM_RIGHTS)) {
							// admin is empowered to edit this album--show an edit link
							?>
							<li>
								<?php printLink($zf . '/admin-edit.php?page=edit&album=' . pathurlencode($_zp_current_album->name), gettext('Edit album'), NULL, NULL, NULL); ?>
							</li>
							<?php
							if (!$_zp_current_album->isDynamic()) {
								if ($_zp_current_album->getNumAlbums()) {
									?>
									<li>
										<?php printLink($zf . '/admin-edit.php?page=edit&album=' . pathurlencode($albumname) . '&tab=subalbuminfo', gettext("Sort subalbums"), NULL, NULL, NULL); ?>
									</li>
									<?php
								}
								if ($_zp_current_album->getNumImages() > 0) {
									?>
									<li>
										<?php printLink($zf . '/admin-albumsort.php?page=edit&album=' . pathurlencode($albumname) . '&tab=sort', gettext("Sort images"), NULL, NULL, NULL); ?>
									</li>
									<?php
								}
							}
							// and a delete link
							if (GALLERY_SESSION) { // XSRF defense requires sessions
								?>
								<li>
									<a href="javascript:confirmDeleteAlbum('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($albumname)) ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>');"
										 title="<?php echo gettext('Delete the album'); ?>"><?php echo gettext('Delete album'); ?></a>
								</li>
								<?php
							}
						}
						if ($_zp_current_album->isMyItem(UPLOAD_RIGHTS) && !$_zp_current_album->isDynamic()) {
							// provide an album upload link if the admin has upload rights for this album and it is not a dynamic album
							?>
							<li>
								<?php printLink($zf . '/admin-upload.php?album=' . pathurlencode($albumname), gettext("Upload Here"), NULL, NULL, NULL); ?>
							</li>
							<?php
							if (GALLERY_SESSION) { // XSRF defense requires sessions
								?>
								<li>
									<a href="javascript:newAlbum('<?php echo pathurlencode($albumname); ?>',true);"><?php echo gettext("New Album Here"); ?></a>
								</li>
								<?php
							}
						}
						zp_apply_filter('admin_toolbox_album', $albumname, $zf);
						if ($inImage) {
							// script is image.php
							$imagename = $_zp_current_image->filename;
							if (!$_zp_current_album->isDynamic()) { // don't provide links when it is a dynamic album
								if ($_zp_current_album->isMyItem(ALBUM_RIGHTS)) {
									// if admin has edit rights on this album, provide a delete link for the image.
									if (GALLERY_SESSION) { // XSRF defense requires sessions
										?>
										<li>
											<a href="javascript:confirmDelete('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deleteimage&amp;album=<?php echo urlencode(pathurlencode($albumname)); ?>&amp;image=<?php echo urlencode($imagename); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>',deleteImage);"
												 title="<?php echo gettext("Delete the image"); ?>"><?php echo gettext("Delete image"); ?></a>
										</li>
										<?php
									}
									?>
									<li>
										<a href="<?php echo $zf; ?>/admin-edit.php?page=edit&amp;album=<?php echo pathurlencode($albumname); ?>&amp;image=<?php echo urlencode($imagename); ?>&amp;tab=imageinfo#IT"
											 title="<?php echo gettext('Edit image'); ?>"><?php echo gettext('Edit image'); ?></a>
									</li>
									<?php
								}
								// set return to this image page
								zp_apply_filter('admin_toolbox_image', $albumname, $imagename, $zf);
							}
							$redirect = "&amp;album=" . html_encode(pathurlencode($albumname)) . "&amp;image=" . urlencode($imagename);
						} else {
							// set the return to this album/page
							$redirect = "&amp;album=" . html_encode(pathurlencode($albumname));
							if ($page > 1) {
								$redirect .= "&amp;page=$page";
							}
						}
						break;
					case 'search.php':
						$words = $_zp_current_search->getSearchWords();
						if (!empty($words)) {
							// script is search.php with a search string
							if (zp_loggedin(UPLOAD_RIGHTS)) {
								$link = $zf . '/admin-dynamic-album.php?' . substr($_zp_current_search->getSearchParams(), 1);
								// if admin has edit rights allow him to create a dynamic album from the search
								?>
								<li>
									<a href="<?php echo $link; ?>" title="<?php echo gettext('Create an album from the search'); ?>" ><?php echo gettext('Create Album'); ?></a>
								</li>
								<?php
							}
							zp_apply_filter('admin_toolbox_search', $zf);
						}
						$redirect = "&amp;p=search" . $_zp_current_search->getSearchParams() . "&amp;page=$page";
						break;
					default:
						// arbitrary custom page
						$gal = stripSuffix($_zp_gallery_page);
						$redirect = "&amp;p=" . urlencode($gal);
						if ($page > 1) {
							$redirect .= "&amp;page=$page";
						}
						$redirect = zp_apply_filter('admin_toolbox_' . $gal, $redirect, $zf);
						break;
				}
				if ($_zp_current_admin_obj->logout_link) {
					// logout link
					$sec = (int) ((SERVER_PROTOCOL == 'https') & true);
					$link = SEO_FULLWEBPATH . '/index.php?logout=' . $sec . $redirect;
					?>
					<li>
						<a href="<?php echo $link; ?>"><?php echo gettext("Logout"); ?> </a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}
}

//*** Gallery Index (album list) Context ***
//******************************************

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
	return strip_tags(getGalleryTitle());
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
 * Function to create the page title to be used within the html <head> <title></title> element.
 * Usefull if you use one header.php for the header of all theme pages instead of individual ones on the theme pages
 * It returns the title and site name in reversed breadcrumb order:
 * <title of current page> | <parent item if present> | <gallery title>
 * It supports standard gallery pages as well a custom and Zenpage news articles, categories and pages.
 *
 * @param string $separator How you wish the parts to be separated
 * @param bool $listparentalbums If the parent albums should be printed in reversed order before the current
 * @param bool $listparentpage If the parent Zenpage pages should be printed in reversed order before the current page
 */
function getHeadTitle($separator = ' | ', $listparentalbums = true, $listparentpages = true) {
	global $_zp_gallery, $_zp_galley_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news,
	$_zp_current_zenpage_page, $_zp_gallery_page, $_zp_current_category, $_zp_page;
	$mainsitetitle = html_encode(strip_tags(getMainSiteName()));
	$separator = html_encode($separator);
	if ($mainsitetitle) {
		$mainsitetitle = $separator . $mainsitetitle;
	}
	$gallerytitle = html_encode(getBareGalleryTitle());
	if ($_zp_page > 1) {
		$pagenumber = ' (' . $_zp_page . ')';
	} else {
		$pagenumber = '';
	}
	switch ($_zp_gallery_page) {
		case 'index.php':
			return $gallerytitle . $mainsitetitle;
			break;
		case 'album.php':
		case 'image.php':
			if ($listparentalbums) {
				$parents = getParentAlbums();
				$parentalbums = '';
				if (count($parents) != 0) {
					$parents = array_reverse($parents);
					foreach ($parents as $parent) {
						$parentalbums .= html_encode(strip_tags($parent->getTitle())) . $separator;
					}
				}
			} else {
				$parentalbums = '';
			}
			$albumtitle = html_encode(getBareAlbumTitle()) . $pagenumber . $separator . $parentalbums . $gallerytitle . $mainsitetitle;
			switch ($_zp_gallery_page) {
				case 'album.php':
					return $albumtitle;
					break;
				case 'image.php':
					return html_encode(getBareImageTitle()) . $separator . $albumtitle;
					break;
			}
			break;
		case 'news.php':
			if (function_exists("is_NewsArticle")) {
				if (is_NewsArticle()) {
					return html_encode(getBareNewsTitle()) . $pagenumber . $separator . gettext('News') . $separator . $gallerytitle . $mainsitetitle;
				} else if (is_NewsCategory()) {
					return html_encode(strip_tags($_zp_current_category->getTitle())) . $pagenumber . $separator . gettext('News') . $separator . $gallerytitle . $mainsitetitle;
				} else {
					return gettext('News') . $pagenumber . $separator . $gallerytitle . $mainsitetitle;
				}
			}
			break;
		case 'pages.php':
			if ($listparentpages) {
				$parents = $_zp_current_zenpage_page->getParents();
				$parentpages = '';
				if (count($parents) != 0) {
					$parents = array_reverse($parents);
					foreach ($parents as $parent) {
						$obj = new ZenpagePage($parent);
						$parentpages .= html_encode(strip_tags($obj->getTitle())) . $separator;
					}
				}
			} else {
				$parentpages = '';
			}
			return html_encode(getBarePageTitle()) . $pagenumber . $separator . $parentpages . $gallerytitle . $mainsitetitle;
			break;
		case '404.php':
			return gettext('Object not found') . $separator . $gallerytitle . $mainsitetitle;
			break;
		default: // for all other possible static custom pages
			$custompage = stripSuffix($_zp_gallery_page);
			$standard = array('contact' => gettext('Contact'), 'register' => gettext('Register'), 'search' => gettext('Search'), 'archive' => gettext('Archive view'), 'password' => gettext('Password required'));
			if (class_exists('favorites')) {
				$standard[str_replace(_PAGE_ . '/', '', favorites::getFavorites_link())] = gettext('My favorites');
			}
			if (array_key_exists($custompage, $standard)) {
				return $standard[$custompage] . $pagenumber . $separator . $gallerytitle . $mainsitetitle;
			} else {
				return $custompage . $pagenumber . $separator . $gallerytitle . $mainsitetitle;
			}
			break;
	}
}

/**
 * Function to print the html <title>title</title> within the <head> of a html page based on the current theme page
 * Usefull if you use one header.php for the header of all theme pages instead of individual ones on the theme pages
 * It prints the title and site name including the <title> tag in reversed breadcrumb order:
 * <title><title of current page> | <parent item if present> | <gallery title></title>
 * It supports standard gallery pages as well a custom and Zenpage news articles, categories and pages.
 *
 * @param string $separator How you wish the parts to be separated
 * @param bool $listparentalbums If the parent albums should be printed in reversed order before the current
 * @param bool $listparentpage If the parent Zenpage pages should be printed in reversed order before the current page
 */
function printHeadTitle($separator = ' | ', $listparentalbums = true, $listparentpages = true) {
	echo '<title>' . getHeadTitle($separator, $listparentalbums, $listparentpages) . '</title>';
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
	return strip_tags(getGalleryDesc());
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
 * Returns the name of the main website as set by the "Website Title" option
 * on the gallery options tab.
 *
 * @return string
 */
function getMainSiteName() {
	global $_zp_gallery;
	return $_zp_gallery->getWebsiteTitle();
}

/**
 * Returns the URL of the main website as set by the "Website URL" option
 * on the gallery options tab.
 *
 * @return string
 */
function getMainSiteURL() {
	global $_zp_gallery;
	return $_zp_gallery->getWebsiteURL();
}

/**
 * Returns the URL of the main gallery page containing the current album
 *
 * @param bool $relative set to false to get the true index page
 * @return string
 */
function getGalleryIndexURL($relative = true) {
	global $_zp_current_album, $_zp_gallery_page, $_zp_gallery;
	if ($relative && ($_zp_gallery_page != 'index.php') && in_context(ZP_ALBUM)) {
		$album = getUrAlbum($_zp_current_album);
		$page = $album->getGalleryPage();
	} else {
		$page = 0;
	}
	$gallink1 = '';
	$gallink2 = '';
	$specialpage = false;
	if ($relative && $specialpage = getOption('custom_index_page')) {
		if (file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . $_zp_gallery->getCurrentTheme() . '/' . internalToFilesystem($specialpage) . '.php')) {
			$gallink1 = $specialpage . '/';
			$gallink2 = 'p=' . $specialpage . '&';
		} else {
			$specialpage = false;
		}
	}
	if ($page > 1) {
		return rewrite_path('/' . _PAGE_ . '/' . $gallink1 . $page, "/index.php?" . $gallink2 . "page=" . $page);
	} else {
		if ($specialpage) {
			return rewrite_path('/' . _PAGE_ . '/' . $gallink1, '?' . substr($gallink2, 0, -1));
		}
		return WEBPATH . "/";
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

/* * * Album AND Gallery Context *********** */
/* * *************************************** */

/**
 * WHILE next_album(): context switches to Album.
 * If we're already in the album context, this is a sub-albums loop, which,
 * quite simply, changes the source of the album list.
 * Switch back to the previous context when there are no more albums.

 * Returns true if there are albums, false if none
 *
 * @param bool $all true to go through all the albums
 * @param string $sorttype overrides default sort type
 * @param string $sortdirection overrides default sort direction
 * @param bool $mine override the password checks
 * @return bool
 * @since 0.6
 */
function next_album($all = false, $sorttype = NULL, $sortdirection = NULL, $mine = NULL) {
	global $_zp_albums, $_zp_gallery, $_zp_current_album, $_zp_page, $_zp_current_album_restore, $_zp_current_search;
	if (is_null($_zp_albums)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_albums = $_zp_current_search->getAlbums($all ? 0 : $_zp_page, $sorttype, $sortdirection, true, $mine);
		} else if (in_context(ZP_ALBUM)) {
			$_zp_albums = $_zp_current_album->getAlbums($all ? 0 : $_zp_page, $sorttype, $sortdirection, true, $mine);
		} else {
			$_zp_albums = $_zp_gallery->getAlbums($all ? 0 : $_zp_page, $sorttype, $sortdirection, true, $mine);
		}
		if (empty($_zp_albums)) {
			return NULL;
		}
		$_zp_current_album_restore = $_zp_current_album;
		$_zp_current_album = newAlbum(array_shift($_zp_albums), true, true);
		save_context();
		add_context(ZP_ALBUM);
		return true;
	} else if (empty($_zp_albums)) {
		$_zp_albums = NULL;
		$_zp_current_album = $_zp_current_album_restore;
		restore_context();
		return false;
	} else {
		$_zp_current_album = newAlbum(array_shift($_zp_albums), true, true);
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
 * Returns a list of all albums decendent from an album
 *
 * @param object $album optional album. If absent the current album is used
 * @return array
 */
function getAllAlbums($album = NULL) {
	global $_zp_current_album, $_zp_gallery;
	if (is_null($album))
		$album = $_zp_current_album;
	if (!is_object($album))
		return;
	$list = array();
	$subalbums = $album->getAlbums(0);
	if (is_array($subalbums)) {
		foreach ($subalbums as $subalbum) {
			$list[] = $subalbum;
			$sub = newAlbum($subalbum);
			$list = array_merge($list, getAllAlbums($sub));
		}
	}
	return $list;
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
		$album = newAlbum($folder);
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
 * @param bool $_oneImagePage set to true if your theme collapses all image thumbs
 * or their equivalent to one page. This is typical with flash viewer themes
 *
 * @return int
 */
function getTotalPages($_oneImagePage = false) {
	global $_zp_gallery, $_zp_current_album, $_firstPageImages, $_zp_zenpage, $_zp_current_category;
	if (in_context(ZP_ALBUM | ZP_SEARCH)) {
		$albums_per_page = max(1, getOption('albums_per_page'));
		$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
		$imageCount = getNumImages();
		if ($_oneImagePage) {
			if ($_oneImagePage === true) {
				$imageCount = min(1, $imageCount);
			} else {
				$imageCount = 0;
			}
		}
		$images_per_page = max(1, getOption('images_per_page'));
		$pageCount = ($pageCount + ceil(($imageCount - $_firstPageImages) / $images_per_page));
		return $pageCount;
	} else if (get_context() == ZP_INDEX) {
		if (galleryAlbumsPerPage() != 0) {
			return (int) ceil($_zp_gallery->getNumAlbums() / galleryAlbumsPerPage());
		} else {
			return NULL;
		}
		return NULL;
	} else if (isset($_zp_zenpage)) {
		if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
			$cat = $_zp_current_category;
		} else {
			$cat = NULL;
		}
		return (int) ceil(count($_zp_zenpage->getArticles(0, NULL, true, NULL, NULL, NULL, $cat)) / ZP_ARTICLES_PER_PAGE);
	}
}

/**
 * Returns the URL of the page number passed as a parameter
 *
 * @param int $page Which page is desired
 * @param int $total How many pages there are.
 * @return int
 */
function getPageURL($page, $total = null) {
	global $_zp_current_album, $_zp_gallery, $_zp_current_search, $_zp_gallery_page;
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
		$searchpagepath = getSearchURL($searchwords, $searchdate, $searchfields, $page, array('albums' => $_zp_current_search->getAlbumList()));
		return $searchpagepath;
	} else {
		if (in_array($_zp_gallery_page, array('index.php', 'album.php', 'image.php'))) {
			if (in_context(ZP_ALBUM)) {
				$pagination1 = pathurlencode($_zp_current_album->name) . '/';
				$pagination2 = 'index.php?album=' . pathurlencode($_zp_current_album->name);
				if ($page > 1) {
					$pagination1 .=_PAGE_ . '/' . $page;
					$pagination2 .= '&page=' . $page;
				}
			} else {
				if (in_context(ZP_INDEX)) {
					$pagination1 = '/';
					$pagination2 = 'index.php';
					if ($page > 1) {
						$pagination1 .= _PAGE_ . '/' . $page;
						$pagination2 .= '?page=' . $page;
					}
				} else {
					return NULL;
				}
			}
		} else {
// handle custom page
			$pg = stripSuffix($_zp_gallery_page);
			$pagination1 = '/' . _PAGE_ . '/' . $pg . '/';
			$pagination2 = 'index.php?p=' . $pg;
			if ($page > 1) {
				$pagination1 .= $page;
				$pagination2 .= '&page=' . $page;
			}
		}
		return rewrite_path($pagination1, $pagination2);
	}
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
	return getPageURL(getCurrentPage() + 1);
}

/**
 * Prints the URL of the next page.
 *
 * @param string $text text for the URL
 * @param string $title Text for the HTML title
 * @param string $class Text for the HTML class
 * @param string $id Text for the HTML id
 */
function printNextPageLink($text, $title = NULL, $class = NULL, $id = NULL) {
	if (hasNextPage()) {
		printLink(getNextPageURL(), $text, $title, $class, $id);
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
	return getPageURL(getCurrentPage() - 1);
}

/**
 * Returns the URL of the previous page.
 *
 * @param string $text The linktext that should be printed as a link
 * @param string $title The text the html-tag "title" should contain
 * @param string $class Insert here the CSS-class name you want to style the link with
 * @param string $id Insert here the CSS-ID name you want to style the link with
 */
function printPrevPageLink($text, $title = NULL, $class = NULL, $id = NULL) {
	if (hasPrevPage()) {
		printLink(getPrevPageURL(), $text, $title, $class, $id);
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
	printPrevPageLink($prevtext, gettext("Previous Page"));
	echo " $separator ";
	printNextPageLink($nexttext, gettext("Next Page"));
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
 * @param bool $_oneImagePage set to true if there is only one image page as, for instance, in flash themes
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $current the current page
 * @param int $total total number of pages
 *
 */
function getPageNavList($_oneImagePage, $navlen, $firstlast, $current, $total) {
	$result = array();
	if (hasPrevPage()) {
		$result['prev'] = getPrevPageURL();
	} else {
		$result['prev'] = NULL;
	}
	if ($firstlast) {
		$result[1] = getPageURL(1, $total);
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
		$result[$i] = getPageURL($i, $total);
	}
	if ($firstlast) {
		$result[$total] = getPageURL($total, $total);
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
 * @param bool $_oneImagePage set to true if there is only one image page as, for instance, in flash themes
 * @param string $nextprev set to true to get the 'next' and 'prev' links printed
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printPageListWithNav($prevtext, $nexttext, $_oneImagePage = false, $nextprev = true, $class = 'pagelist', $id = NULL, $firstlast = true, $navlen = 9) {
	$current = getCurrentPage();
	$total = max(1, getTotalPages($_oneImagePage));
	$nav = getPageNavList($_oneImagePage, $navlen, $firstlast, $current, $total);
	if (count($nav) < 4) {
		$class .= ' disabled_nav';
	}
	?>
	<div <?php if ($id) echo ' id="$id"'; ?> class="<?php echo $class; ?>">
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
						printLink($prev, html_encode($prevtext), gettext('Previous Page'));
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
								printLink($nav[1], 1, gettext("Page 1"));
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
						$k1 = $i - (int) ($i - $last) / 2;
						printLink(getPageURL($k1, $total), '...', sprintf(ngettext('Page %u', 'Page %u', $k1), $k1));
						?>
					</li>
					<?php
				} else if ($d == 2) {
					?>
					<li>
						<?php
						$k1 = $last + 1;
						printLink(getPageURL($k1, $total), $k1, sprintf(ngettext('Page %u', 'Page %u', $k1), $k1));
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
						printLink($link, $i, $title);
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
						$k1 = $i - (int) ($i - $last) / 2;
						?>
						<li>
							<?php printLink(getPageURL($k1, $total), '...', sprintf(ngettext('Page %u', 'Page %u', $k1), $k1)); ?>
						</li>
						<?php
					} else if ($d == 2) {
						$k1 = $last + 1;
						?>
						<li>
							<?php printLink(getPageURL($k1, $total), $k1, sprintf(ngettext('Page %u', 'Page %u', $k1), $k1)); ?>
						</li>
						<?php
					}
					?>
					<li class="last<?php if ($current == $i) echo ' current'; ?>">
						<?php
						if ($current == $i) {
							echo $i;
						} else {
							printLink($link, $i, sprintf(ngettext('Page %u', 'Page %u', $i), $i));
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
						printLink($next, html_encode($nexttext), gettext('Next Page'));
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

//*** Album Context ************************
//******************************************

/**
 * Sets the album passed as the current album
 *
 * @param object $album the album to be made current
 */
function makeAlbumCurrent($album) {
	global $_zp_current_album;
	$_zp_current_album = $album;
	set_context(ZP_INDEX | ZP_ALBUM);
}

/**
 * Returns the raw title of the current album.
 *
 * @return string
 */
function getAlbumTitle() {
	if (!in_context(ZP_ALBUM))
		return false;
	global $_zp_current_album;
	return $_zp_current_album->getTitle();
}

/**
 * Returns a text-only title of the current album.
 *
 * @return string
 */
function getBareAlbumTitle() {
	return strip_tags(getAlbumTitle());
}

/**
 * Returns an album title taged with of Not visible or password protected status
 *
 * @return string;
 */
function getAnnotatedAlbumTitle() {
	global $_zp_current_album;
	$title = getBareAlbumTitle();
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$title .= "\n" . gettext('The album is password protected.');
	}
	if (!$_zp_current_album->getShow()) {
		$title .= "\n" . gettext('The album is un-published.');
	}
	return $title;
}

function printAnnotatedAlbumTitle() {
	echo html_encode(getAnnotatedAlbumTitle());
}

/**
 * Prints an encapsulated title of the current album.
 * If you are logged in you can click on this to modify the title on the fly.
 *
 * @author Ozh
 */
function printAlbumTitle() {
	echo html_encodeTagged(getAlbumTitle());
}

function printBareAlbumTitle() {
	echo html_encodeTagged(getBareAlbumTitle());
}

/**
 * Gets the 'n' for n of m albums
 *
 * @return int
 */
function albumNumber() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
	$name = $_zp_current_album->getFolder();
	if (in_context(ZP_SEARCH)) {
		$albums = $_zp_current_search->getAlbums();
	} else if (in_context(ZP_ALBUM)) {
		$parent = $_zp_current_album->getParent();
		if (is_null($parent)) {
			$albums = $_zp_gallery->getAlbums();
		} else {
			$albums = $parent->getAlbums();
		}
	}
	$c = 0;
	foreach ($albums as $albumfolder) {
		$c++;
		if ($name == $albumfolder) {
			return $c;
		}
	}
	return false;
}

/**
 * Returns an array of the names of the parents of the current album.
 *
 * @param object $album optional album object to use inseted of the current album
 * @return array
 */
function getParentAlbums($album = null) {
	$parents = array();
	if (in_context(ZP_ALBUM)) {
		global $_zp_current_album, $_zp_current_search, $_zp_gallery;
		if (is_null($album)) {
			if (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) {
				$album = $_zp_current_search->getDynamicAlbum();
				if (empty($album))
					return $parents;
			} else {
				$album = $_zp_current_album;
			}
		}
		while (!is_null($album = $album->getParent())) {
			array_unshift($parents, $album);
		}
	}
	return $parents;
}

/**
 * returns the breadcrumb item for the current images's album
 *
 * @param string $title Text to be used as the URL title tag
 * @return array
 */
function getAlbumBreadcrumb($title = NULL) {
	global $_zp_current_search, $_zp_gallery, $_zp_current_album, $_zp_last_album;
	$output = array();
	if (in_context(ZP_SEARCH_LINKED)) {
		$album = NULL;
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			if (!is_null($_zp_current_album)) {
				if (in_context(ZP_ALBUM_LINKED) && $_zp_last_album == $_zp_current_album->name) {
					$album = $_zp_current_album;
				}
			}
		} else {
			if (in_context(ZP_IMAGE) && in_context(ZP_ALBUM_LINKED)) {
				$album = $_zp_current_album;
			} else {
				$album = $dynamic_album;
			}
		}
	} else {
		$album = $_zp_current_album;
	}
	if ($album) {
		if (is_null($title)) {
			$title = $album->getTitle();
			if (empty($title)) {
				$title = gettext('Album Thumbnails');
			}
		}
		return array('link' => getAlbumLinkURL($album), 'title' => $title, 'text' => $album->getDesc());
	}
	return false;
}

/**
 * prints the breadcrumb item for the current images's album
 *
 * @param string $before Text to place before the breadcrumb
 * @param string $after Text to place after the breadcrumb
 * @param string $title Text to be used as the URL title tag
 */
function printAlbumBreadcrumb($before = '', $after = '', $title = NULL) {
	if ($breadcrumb = getAlbumBreadcrumb($title)) {
		if ($before) {
			$output = '<span class="beforetext">' . html_encode($before) . '</span>';
		} else {
			$output = '';
		}
		$output .= "<a href=\"" . html_encode($breadcrumb['link']) . "\">";
		$output .= html_encode($breadcrumb['title']);
		$output .= '</a>';
		if ($after) {
			$output .= '<span class="aftertext">' . html_encode($after) . '</span>';
		}
		echo $output;
	}
}

/**
 * Prints the "breadcrumb" for a search page
 * 		if the search was for a data range, the breadcrumb is "Archive"
 * 		otherwise it is "Search"
 * @param string $between Insert here the text to be printed between the links
 * @param string $class is the class for the link (if present)
 * @param string $search text for a search page title
 * @param string $archive text for an archive page title
 * @param string $format data format for archive page crumb
 */
function printSearchBreadcrumb($between = NULL, $class = NULL, $search = NULL, $archive = NULL, $format = '%B %Y') {
	global $_zp_current_search;
	if (is_null($between)) {
		$between = ' | ';
	}
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	if ($d = $_zp_current_search->getSearchDate()) {
		if (is_null($archive)) {
			$text = gettext('Archive');
			$textdecoration = true;
		} else {
			$text = strip_tags(html_encode($archive));
			$textdecoration = false;
		}
		echo "<a href=\"" . html_encode(getCustomPageURL('archive', NULL)) . "\"$class title=\"" . $text . "\">";
		printf('%s' . $text . '%s', $textdecoration ? '<em>' : '', $textdecoration ? '</em>' : '');
		echo "</a>";
		echo '<span class="betweentext">' . html_encode($between) . '</span>';
		if ($format) {
			$d = strtotime($d);
			$d = strftime($format, $d);
		}
		echo $d;
	} else {
		if (is_null($search)) {
			$text = gettext('Search');
			$textdecoration = true;
		} else {
			$text = strip_tags(html_encode($search));
			$textdecoration = false;
		}
		printf('%s' . $text . '%s', $textdecoration ? '<em>' : '', $textdecoration ? '</em>' : '');
	}
}

/**
 * returns the breadcrumb navigation for album, gallery and image view.
 *
 * @return array
 */
function getParentBreadcrumb() {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album;
	$output = array();
	if (in_context(ZP_SEARCH_LINKED)) {
		$page = $_zp_current_search->page;
		$searchwords = $_zp_current_search->getSearchWords();
		$searchdate = $_zp_current_search->getSearchDate();
		$searchfields = $_zp_current_search->getSearchFields(true);
		$search_album_list = $_zp_current_search->getAlbumList();
		if (!is_array($search_album_list)) {
			$search_album_list = array();
		}
		$searchpagepath = getSearchURL($searchwords, $searchdate, $searchfields, $page, array('albums' => $search_album_list));
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			if (empty($searchdate)) {
				$output[] = array('link' => $searchpagepath, 'title' => gettext("Return to search"), 'text' => gettext("Search"));
				if (is_null($_zp_current_album)) {
					return $output;
				} else {
					$parents = getParentAlbums();
				}
			} else {
				return array(array('link' => $searchpagepath, 'title' => gettext("Return to archive"), 'text' => gettext("Archive")));
			}
		} else {
			$album = $dynamic_album;
			$parents = getParentAlbums($album);
			if (in_context(ZP_ALBUM_LINKED)) {
				array_push($parents, $album);
			}
		}
// remove parent links that are not in the search path
		foreach ($parents as $key => $analbum) {
			$target = $analbum->name;
			if ($target !== $dynamic_album && !in_array($target, $search_album_list)) {
				unset($parents[$key]);
			}
		}
	} else {
		$parents = getParentAlbums();
	}
	$n = count($parents);
	if ($n > 0) {
		foreach ($parents as $parent) {
			$url = rewrite_path("/" . pathurlencode($parent->name) . "/", "/index.php?album=" . pathurlencode($parent->name));
//cleanup things in description for use as attribute tag
			$desc = strip_tags(preg_replace('|</p\s*>|i', '</p> ', preg_replace('|<br\s*/>|i', ' ', $parent->getDesc())));
			$output[] = array('link' => html_encode($url), 'title' => $desc, 'text' => $parent->getTitle());
		}
	}
	return $output;
}

/**
 * Prints the breadcrumb navigation for album, gallery and image view.
 *
 * @param string $before Insert here the text to be printed before the links
 * @param string $between Insert here the text to be printed between the links
 * @param string $after Insert here the text to be printed after the links
 * @param mixed $truncate if not empty, the max lenght of the description.
 * @param string $elipsis the text to append to the truncated description
 */
function printParentBreadcrumb($before = NULL, $between = NULL, $after = NULL, $truncate = NULL, $elipsis = NULL) {
	$crumbs = getParentBreadcrumb();
	if (!empty($crumbs)) {
		if (is_null($between)) {
			$between = ' | ';
		}
		if (is_null($after)) {
			$after = ' | ';
		}
		if (is_null($elipsis)) {
			$elipsis = '...';
		}
		if ($before) {
			$output = '<span class="beforetext">' . html_encode($before) . '</span>';
		} else {
			$output = '';
		}
		if ($between) {
			$between = '<span class="betweentext">' . html_encode($between) . '</span>';
		}
		$i = 0;
		foreach ($crumbs as $crumb) {
			if ($i > 0) {
				$output .= $between;
			}
//cleanup things in description for use as attribute tag
			$desc = $crumb['title'];
			if (!empty($desc) && $truncate) {
				$desc = truncate_string($desc, $truncate, $elipsis);
			}
			$output .= '<a href="' . html_encode($crumb['link']) . '"' . ' title="' . html_encode(strip_tags($desc)) . '">' . html_encode($crumb['text']) . '</a>';
			$i++;
		}
		if ($after) {
			$output .= '<span class="aftertext">' . html_encode($after) . '</span>';
		}
		echo $output;
	}
}

/**
 * Prints a link to the 'main website'
 * Only prints the link if the url is not empty and does not point back the gallery page
 *
 * @param string $before text to precede the link
 * @param string $after text to follow the link
 * @param string $title Title text
 * @param string $class optional css class
 * @param string $id optional css id
 *  */
function printHomeLink($before = '', $after = '', $title = NULL, $class = NULL, $id = NULL) {
	global $_zp_gallery;
	$site = rtrim($_zp_gallery->getWebsiteURL(), '/');
	if (!empty($site)) {
		$name = $_zp_gallery->getWebsiteTitle();
		if (empty($name)) {
			$name = gettext('Home');
		}
		if ($site != SEO_FULLWEBPATH) {
			if ($before) {
				echo '<span class="beforetext">' . html_encode(before) . '</span>';
			}
			printLink($site, $name, $title, $class, $id);
			if ($after) {
				echo '<span class="aftertext">' . html_encode($after) . '</span>';
			}
		}
	}
}

/**
 * Returns the formatted date field of the album
 *
 * @param string $format optional format string for the date
 * @return string
 */
function getAlbumDate($format = null) {
	global $_zp_current_album;
	$d = $_zp_current_album->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00')) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $format Format string for the date formatting
 */
function printAlbumDate($before = '', $format = NULL) {
	global $_zp_current_album;
	if (is_null($format)) {
		$format = DATE_FORMAT;
	}
	$date = getAlbumDate($format);
	if ($date) {
		if ($before) {
			$date = '<span class="beforetext">' . $before . '</span>' . $date;
		}
	}
	echo html_encodeTagged($date);
}

/**
 * Returns the Location of the album.
 *
 * @return string
 */
function getAlbumLocation() {
	global $_zp_current_album;
	return $_zp_current_album->getLocation();
}

/**
 * Prints the location of the album
 *
 * @author Ozh
 */
function printAlbumLocation() {
	echo html_encodeTagged(getAlbumLocation);
}

/**
 * Returns the raw description of the current album.
 *
 * @return string
 */
function getAlbumDesc() {
	if (!in_context(ZP_ALBUM))
		return false;
	global $_zp_current_album;
	return $_zp_current_album->getDesc();
}

/**
 * Returns a text-only description of the current album.
 *
 * @return string
 */
function getBareAlbumDesc() {
	return strip_tags(getAlbumDesc());
}

/**
 * Prints description of the current album
 *
 * @author Ozh
 */
function printAlbumDesc() {
	global $_zp_current_album;
	echo html_encodeTagged(getAlbumDesc());
}

function printBareAlbumDesc() {
	echo html_encode(getBareAlbumDesc());
}

/**
 * Returns the custom_data field of the current album
 *
 * @return string
 */
function getAlbumCustomData() {
	global $_zp_current_album;
	return $_zp_current_album->getCustomData();
}

/**
 * Prints the custom_data field of the current album.
 * Converts and displays line break in the admin field as <br />.
 *
 * @author Ozh
 */
function printAlbumCustomData() {
	echo html_encodeTagged(getAlbumCustomData());
}

/**
 * A composit for getting album data
 *
 * @param string $field which field you want
 * @return string
 */
function getAlbumData($field) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_album_image;
	return get_language_string($_zp_album_image->get($field));
}

/**
 * Prints arbitrary data from the album object
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field
 * @author Ozh
 */
function printAlbumData($field, $label = '') {
	global $_zp_current_album;
	echo html_encodeTagged($_zp_current_album->get($field));
}

/**
 * Returns the album page number of the current image
 *
 * @param object $album optional album object
 * @return integer
 */
function getAlbumPage($album = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_firstPageImages;
	if (is_null($album))
		$album = $_zp_current_album;
	$page = 0;
	if (in_context(ZP_IMAGE) && !in_context(ZP_SEARCH)) {
		if ($_zp_current_album->isDynamic()) {
			$search = $_zp_current_album->getSearchEngine();
			$imageindex = $search->getImageIndex($_zp_current_album->name, $_zp_current_image->filename);
			$numalbums = $search->getNumAlbums();
		} else {
			$imageindex = $_zp_current_image->getIndex();
			$numalbums = $album->getNumAlbums();
		}
		$imagepage = floor(($imageindex - $_firstPageImages) / max(1, getOption('images_per_page'))) + 1;
		$albumpages = ceil($numalbums / max(1, getOption('albums_per_page')));
		if ($albumpages == 0 && $_firstPageImages > 0)
			$imagepage++;
		$page = $albumpages + $imagepage;
	}
	return $page;
}

/**
 * Returns the album link url of the current album.
 *
 * @param object $album optional album object
 * @return string
 */
function getAlbumLinkURL($album = NULL) {
	global $_zp_current_album;
	if (is_null($album))
		$album = $_zp_current_album;
	if (in_context(ZP_IMAGE)) {
		$page = getAlbumPage($album);
		if ($page <= 1)
			$page = 0;
	} else {
		$page = 0;
	}
	return $album->getAlbumLink($page);
}

/**
 * Prints the album link url of the current album.
 *
 * @param string $text Insert the link text here.
 * @param string $title Insert the title text here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 */
function printAlbumLink($text, $title, $class = NULL, $id = NULL) {
	printLink(getAlbumLinkURL(), $text, $title, $class, $id);
}

/**
 * Returns the name of the defined album thumbnail image.
 *
 * @return string
 */
function getAlbumThumb() {
	global $_zp_current_album;
	return $_zp_current_album->getAlbumThumb();
}

/**
 * Returns an img src link to the password protect thumb substitute
 *
 * @param string $extra extra stuff to put in the HTML
 * @return string
 */
function getPasswordProtectImage($extra) {
	global $_zp_themeroot;
	$image = '';
	$themedir = SERVERPATH . '/themes/' . basename($_zp_themeroot);
	if (file_exists(internalToFilesystem($themedir . '/images/err-passwordprotected.png'))) {
		$image = $_zp_themeroot . '/images/err-passwordprotected.png';
	} else if (file_exists(internalToFilesystem($themedir . '/images/err-passwordprotected.gif'))) {
		$image = $_zp_themeroot . '/images/err-passwordprotected.gif';
	} else {
		$image = WEBPATH . '/' . ZENFOLDER . '/images/err-passwordprotected.png';
	}
	return '<img src="' . $image . '" ' . $extra . ' alt="protected" />';
}

/**
 * Prints the album thumbnail image.
 *
 * @param string $alt Insert the text for the alternate image name here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 *  */
function printAlbumThumbImage($alt, $class = NULL, $id = NULL) {
	global $_zp_current_album, $_zp_themeroot;
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}

	$class = trim($class);
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	if ($id) {
		$id = ' id="' . $id . '"';
	}
	$s = getOption('thumb_size');
	$thumb = getAlbumThumb();
	if (getOption('thumb_crop')) {
		$w = getOption('thumb_crop_width');
		$h = getOption('thumb_crop_height');
		if ($w > $h) {
			$h = round($h * $s / $w);
			$w = $s;
		} else {
			$w = round($w * $s / $w);
			$h = $s;
		}
	} else {
		$w = $h = $s;
		$thumbobj = $_zp_current_album->getAlbumThumbImage();
		getMaxSpaceContainer($w, $h, $thumbobj, true);
	}
	$size = ' width="' . $w . '" height="' . $h . '"';
	if (!getOption('use_lock_image') || $_zp_current_album->isMyItem(LIST_RIGHTS) || empty($pwd)) {
		$html = '<img src="' . html_encode(pathurlencode($thumb)) . '"' . $size . ' alt="' . html_encode($alt) . '"' . $class . $id . ' />';
		$html = zp_apply_filter('standard_album_thumb_html', $html);
		echo $html;
	} else {
		echo getPasswordProtectImage($size);
	}
}

/**
 * Returns a link to a custom sized thumbnail of the current album
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $effects image effects (e.g. set 'gray' to force grayscale)
 *
 * @return string
 */
function getCustomAlbumThumb($size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = null, $effects = NULL) {
	global $_zp_current_album;
	$thumb = $_zp_current_album->getAlbumThumbImage();
	return $thumb->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, true, $effects);
}

/**
 * Prints a link to a custom sized thumbnail of the current album
 *
 * See getCustomImageURL() for details.
 *
 * @param string $alt Alt atribute text
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw cropwidth
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param string $class css class
 * @param string $id css id
 *
 * @return string
 */
function printCustomAlbumThumbImage($alt, $size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = null, $class = NULL, $id = NULL) {
	global $_zp_current_album;
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	$class = trim($class);
	/* set the HTML image width and height parameters in case this image was "imageDefault.png" substituted for no thumbnail then the thumb layout is preserved */
	$sizing = '';
	if (is_null($width)) {
		if (!is_null($cropw) && !is_null($croph)) {
			if (empty($height)) {
				$height = $size;
			}
			$s = round($height * ($cropw / $croph));
			if (!empty($s))
				$sizing = ' width="' . $s . '"';
		}
	} else {
		$sizing = ' width="' . $width . '"';
	}
	if (is_null($height)) {
		if (!is_null($cropw) && !is_null($croph)) {
			if (empty($width)) {
				$width = $size;
			}
			$s = round($width * ($croph / $cropw));
			if (!empty($s))
				$sizing = $sizing . ' height="' . $s . '"';
		}
	} else {
		$sizing = $sizing . ' height="' . $height . '"';
	}
	if (!getOption('use_lock_image') || $_zp_current_album->isMyItem(LIST_RIGHTS) || empty($pwd)) {
		$html = '<img src="' . html_encode(pathurlencode(getCustomAlbumThumb($size, $width, $height, $cropw, $croph, $cropx, $cropy))) . '"' . $sizing . ' alt="' . html_encode($alt) . '"' .
						(($class) ? ' class="' . $class . '"' : '') . (($id) ? ' id="' . $id . '"' : '') . " />";
		$html = zp_apply_filter('custom_album_thumb_html', $html);
		echo $html;
	} else {
		echo getPasswordProtectImage($sizing);
	}
}

/**
 * Called by ***MaxSpace functions to compute the parameters to be passed to xxCustomyyy functions.
 *
 * @param int $width maxspace width
 * @param int $height maxspace height
 * @param object $image the image in question
 * @param bool $thumb true if for a thumbnail
 */
function getMaxSpaceContainer(&$width, &$height, $image, $thumb = false) {
	global $_zp_gallery;
	$upscale = getOption('image_allow_upscale');
	$imagename = $image->filename;
	if (!isImagePhoto($image) & $thumb) {
		$imgfile = $image->getThumbImageFile();
		$image = zp_imageGet($imgfile);
		$s_width = zp_imageWidth($image);
		$s_height = zp_imageHeight($image);
	} else {
		$s_width = $image->get('width');
		if ($s_width == 0)
			$s_width = max($width, $height);
		$s_height = $image->get('height');
		if ($s_height == 0)
			$s_height = max($width, $height);
	}

	$newW = round($height / $s_height * $s_width);
	$newH = round($width / $s_width * $s_height);
	if (DEBUG_IMAGE)
		debugLog("getMaxSpaceContainer($width, $height, $imagename, $thumb): \$s_width=$s_width; \$s_height=$s_height; \$newW=$newW; \$newH=$newH; \$upscale=$upscale;");
	if ($newW > $width) {
		if ($upscale || $s_height > $newH) {
			$height = $newH;
		} else {
			$height = $s_height;
			$width = $s_width;
		}
	} else {
		if ($upscale || $s_width > $newW) {
			$width = $newW;
		} else {
			$height = $s_height;
			$width = $s_width;
		}
	}
}

/**
 * Returns a link to a un-cropped custom sized version of the current album thumb within the given height and width dimensions.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomAlbumThumbMaxSpace($width, $height) {
	global $_zp_current_album;
	$albumthumb = $_zp_current_album->getAlbumThumbImage();
	getMaxSpaceContainer($width, $height, $albumthumb, true);
	return getCustomAlbumThumb(NULL, $width, $height, NULL, NULL, NULL, NULL);
}

/**
 * Prints a un-cropped custom sized album thumb within the given height and width dimensions.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param bool $thumbStandin set to true to treat as thumbnail
 */
function printCustomAlbumThumbMaxSpace($alt, $width, $height, $class = NULL, $id = NULL) {
	global $_zp_current_album;
	$albumthumb = $_zp_current_album->getAlbumThumbImage();
	getMaxSpaceContainer($width, $height, $albumthumb, true);
	printCustomAlbumThumbImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id);
}

/**
 * Returns the next album
 *
 * @return object
 */
function getNextAlbum() {
	global $_zp_current_album, $_zp_current_search, $_zp_gallery;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$nextalbum = $_zp_current_search->getNextAlbum($_zp_current_album->name);
	} else if (in_context(ZP_ALBUM)) {
		$nextalbum = $_zp_current_album->getNextAlbum();
	} else {
		return null;
	}
	return $nextalbum;
}

/**
 * Get the URL of the next album in the gallery.
 *
 * @return string
 */
function getNextAlbumURL() {
	$nextalbum = getNextAlbum();
	if ($nextalbum) {
		return rewrite_path("/" . pathurlencode($nextalbum->name), "/index.php?album=" . pathurlencode($nextalbum->name));
	}
	return false;
}

/**
 * Returns the previous album
 *
 * @return object
 */
function getPrevAlbum() {
	global $_zp_current_album, $_zp_current_search;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$prevalbum = $_zp_current_search->getPrevAlbum($_zp_current_album->name);
	} else if (in_context(ZP_ALBUM)) {
		$prevalbum = $_zp_current_album->getPrevAlbum();
	} else {
		return null;
	}
	return $prevalbum;
}

/**
 * Get the URL of the previous album in the gallery.
 *
 * @return string
 */
function getPrevAlbumURL() {
	$prevalbum = getPrevAlbum();
	if ($prevalbum) {
		return rewrite_path("/" . pathurlencode($prevalbum->name), "/index.php?album=" . pathurlencode($prevalbum->name));
	}
	return false;
}

/**
 * Returns true if this page has image thumbs on it
 *
 * @return bool
 */
function isImagePage() {
	if (getNumImages()) {
		global $_zp_page, $_firstPageImages;
		$imagestart = getTotalPages(2); // # of album pages
		if (!$_firstPageImages)
			$imagestart++; // then images start on the last album page.
		return $_zp_page >= $imagestart;
	}
	return false;
}

/**
 * Returns true if this page has album thumbs on it
 *
 * @return bool
 */
function isAlbumPage() {
	global $_zp_page;
	$pageCount = Ceil(getNumAlbums() / getOption('albums_per_page'));
	return ($_zp_page <= $pageCount);
}

/**
 * Returns the number of images in the album.
 *
 * @return int
 */
function getNumImages() {
	global $_zp_current_album, $_zp_current_search;
	if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getNumImages();
	} else {
		return $_zp_current_album->getNumImages();
	}
}

/**
 * Returns the count of all the images in the album and any subalbums
 *
 * @param object $album The album whose image count you want
 * @return int
 * @since 1.1.4
 */
function getTotalImagesIn($album) {
	global $_zp_gallery;
	$sum = $album->getNumImages();
	$subalbums = $album->getAlbums(0);
	while (count($subalbums) > 0) {
		$albumname = array_pop($subalbums);
		$album = newAlbum($albumname);
		$sum = $sum + getTotalImagesIn($album);
	}
	return $sum;
}

/**
 * Returns the next image on a page.
 * sets $_zp_current_image to the next image in the album.

 * Returns true if there is an image to be shown
 *
 * @param bool $all set to true disable pagination
 * @param int $firstPageCount the number of images which can go on the page that transitions between albums and images
 * 							Normally this parameter should be NULL so as to use the default computations.
 * @param string $sorttype overrides the default sort type
 * @param string $sortdirection overrides the default sort direction.
 * @param bool $mine overridePassword the password check
 * @return bool
 *
 * @return bool
 */
function next_image($all = false, $firstPageCount = NULL, $sorttype = null, $sortdirection = NULL, $mine = NULL) {
	global $_zp_images, $_zp_current_image, $_zp_current_album, $_zp_page, $_zp_current_image_restore,
	$_zp_current_search, $_zp_gallery, $_firstPageImages;
	if (is_null($firstPageCount)) {
		$firstPageCount = $_firstPageImages;
	}
	$imagePageOffset = getTotalPages(2); /* gives us the count of pages for album thumbs */
	if ($all) {
		$imagePage = 1;
		$firstPageCount = 0;
	} else {
		$_firstPageImages = $firstPageCount; /* save this so pagination can see it */
		$imagePage = $_zp_page - $imagePageOffset;
	}
	if ($firstPageCount > 0 && $imagePageOffset > 0) {
		$imagePage = $imagePage + 1; /* can share with last album page */
	}
	if ($imagePage <= 0) {
		return false; /* we are on an album page */
	}
	if (is_null($_zp_images)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_images = $_zp_current_search->getImages($all ? 0 : ($imagePage), $firstPageCount, $sorttype, $sortdirection, true, $mine);
		} else {
			$_zp_images = $_zp_current_album->getImages($all ? 0 : ($imagePage), $firstPageCount, $sorttype, $sortdirection, true, $mine);
		}
		if (empty($_zp_images)) {
			return NULL;
		}
		$_zp_current_image_restore = $_zp_current_image;
		$img = array_shift($_zp_images);
		$_zp_current_image = newImage($_zp_current_album, $img, true, true);
		save_context();
		add_context(ZP_IMAGE);
		return true;
	} else if (empty($_zp_images)) {
		$_zp_images = NULL;
		$_zp_current_image = $_zp_current_image_restore;
		restore_context();
		return false;
	} else {
		$img = array_shift($_zp_images);
		$_zp_current_image = newImage($_zp_current_album, $img, true, true);
		return true;
	}
}

//*** Image Context ************************
//******************************************

/**
 * Sets the image passed as the current image
 *
 * @param object $image the image to become current
 */
function makeImageCurrent($image) {
	if (!is_object($image))
		return;
	global $_zp_current_album, $_zp_current_image;
	$_zp_current_image = $image;
	$_zp_current_album = $_zp_current_image->getAlbum();
	set_context(ZP_INDEX | ZP_ALBUM | ZP_IMAGE);
}

/**
 * Returns the raw title of the current image.
 *
 * @return string
 */
function getImageTitle() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getTitle();
}

/**
 * Returns a text-only title of the current image.
 *
 * @return string
 */
function getBareImageTitle() {
	return strip_tags(getImageTitle());
}

/**
 * Returns the image title taged with not visible annotation.
 *
 * @return string
 */
function getAnnotatedImageTitle() {
	global $_zp_current_image;
	$title = getBareImageTitle();
	if (!$_zp_current_image->getShow()) {
		$title .= "\n" . gettext('The image is marked un-published.');
	}
	return $title;
}

function printAnnotatedImageTitle() {
	echo html_encode(getAnnotatedImageTitle());
}

/**
 * Prints title of the current image
 *
 * @author Ozh
 */
function printImageTitle() {
	echo html_encodeTagged(getImageTitle());
}

function printBareImageTitle() {
	echo html_encode(getBareImageTitle());
}

/**
 * Returns the 'n' of n of m images
 *
 * @return int
 */
function imageNumber() {
	global $_zp_current_image, $_zp_current_search, $_zp_current_album;
	$name = $_zp_current_image->getFileName();
	if (in_context(ZP_SEARCH) || (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED))) {
		$folder = $_zp_current_image->imagefolder;
		$images = $_zp_current_search->getImages();
		$c = 0;
		foreach ($images as $image) {
			$c++;
			if ($name == $image['filename'] && $folder == $image['folder']) {
				return $c;
			}
		}
	} else {
		return $_zp_current_image->getIndex() + 1;
	}
	return false;
}

/**
 * Returns the image date of the current image in yyyy-mm-dd hh:mm:ss format.
 * Pass it a date format string for custom formatting
 *
 * @param string $format formatting string for the data
 * @return string
 */
function getImageDate($format = null) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	$d = $_zp_current_image->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00')) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $format Format string for the date formatting
 */
function printImageDate($before = '', $format = null) {
	global $_zp_current_image;
	if (is_null($format)) {
		$format = DATE_FORMAT;
	}
	$date = getImageDate($format);
	if ($date) {
		if ($before) {
			$date = '<span class="beforetext">' . $before . '</span>' . $date;
		}
	}
	echo html_encodeTagged($date);
}

// IPTC fields
/**
 * Returns the Location field of the current image
 *
 * @return string
 */
function getImageLocation() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getLocation();
}

/**
 * Returns the City field of the current image
 *
 * @return string
 */
function getImageCity() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getcity();
}

/**
 * Returns the State field of the current image
 *
 * @return string
 */
function getImageState() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getState();
}

/**
 * Returns the Country field of the current image
 *
 * @return string
 */
function getImageCountry() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getCountry();
}

/**
 * Returns the raw description of the current image.
 * new lines are replaced with <br /> tags
 *
 * @return string
 */
function getImageDesc() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getDesc();
}

/**
 * Returns a text-only description of the current image.
 *
 * @return string
 */
function getBareImageDesc() {
	return strip_tags(getImageDesc());
}

/**
 * Prints the description of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 */
function printImageDesc() {
	echo html_encodeTagged(getImageDesc());
}

function printBareImageDesc() {
	echo html_encode(getBareImageDesc());
}

/**
 * A composit for getting image data
 *
 * @param string $field which field you want
 * @return string
 */
function getImageData($field) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return get_language_string($_zp_current_image->get($field));
}

/**
 * Returns the custom_data field of the current image
 *
 * @return string
 */
function getImageCustomData() {
	Global $_zp_current_image;
	return $_zp_current_image->getCustomData();
}

/**
 * Prints the custom_data field of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 * @return string
 */
function printImageCustomData() {
	$data = getImageCustomData();
	$data = str_replace("\r\n", "\n", $data);
	$data = str_replace("\n", "<br />", $data);
	echo $data;
}

/**
 * Prints arbitrary data from the image object
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field.
 * @author Ozh
 */
function printImageData($field, $label = '') {
	global $_zp_current_image;
	echo html_encodeTagged($_zp_current_image->get($field));
}

/**
 * True if there is a next image
 *
 * @return bool
 */
function hasNextImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getNextImage();
}

/**
 * True if there is a previous image
 *
 * @return bool
 */
function hasPrevImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getPrevImage();
}

/**
 * Returns the url of the next image.
 *
 * @return string
 */
function getNextImageURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$nextimg = $_zp_current_image->getNextImage();
	return rewrite_path("/" . pathurlencode($nextimg->albumname) . "/" . urlencode($nextimg->filename) . IM_SUFFIX, "/index.php?album=" . pathurlencode($nextimg->albumname) . "&image=" . urlencode($nextimg->filename));
}

/**
 * Returns the url of the previous image.
 *
 * @return string
 */
function getPrevImageURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$previmg = $_zp_current_image->getPrevImage();
	return rewrite_path("/" . pathurlencode($previmg->albumname) . "/" . urlencode($previmg->filename) . IM_SUFFIX, "/index.php?album=" . pathurlencode($previmg->albumname) . "&image=" . urlencode($previmg->filename));
}

/**
 * Returns the thumbnail of the previous image.
 *
 * @return string
 */
function getPrevImageThumb() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$img = $_zp_current_image->getPrevImage();
	return $img->getThumb();
}

/**
 * Returns the thumbnail of the next image.
 *
 * @return string
 */
function getNextImageThumb() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$img = $_zp_current_image->getNextImage();
	return $img->getThumb();
}

/**
 * Returns the url of the current image.
 *
 * @return string
 */
function getImageLinkURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getImageLink();
}

/**
 * Prints the link to the current  image.
 *
 * @param string $text text for the link
 * @param string $title title tag for the link
 * @param string $class optional style class for the link
 * @param string $id optional style id for the link
 */
function printImageLink($text, $title, $class = NULL, $id = NULL) {
	printLink(getImageLinkURL(), $text, $title, $class, $id);
}

/**
 * Returns the Metadata infromation from the current image
 *
 * @param $image optional image object
 * @param string $displayonly set to true to return only the items selected for display
 * @return array
 */
function getImageMetaData($image = NULL, $displayonly = true) {
	global $_zp_current_image, $_zp_exifvars;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image) || !$image->get('hasMetadata')) {
		return false;
	}
	$data = $image->getMetaData();
	if ($displayonly) {
		foreach ($data as $field => $value) { //	remove the empty or not selected to display
			if (!$value || !$_zp_exifvars[$field][3]) {
				unset($data[$field]);
			}
		}
	}
	if (count($data) > 0) {
		return $data;
	}
	return false;
}

/**
 * Prints the Metadata data of the current image
 *
 * @param string $title title tag for the class
 * @param bool $toggle set to true to get a javascript toggle on the display of the data
 * @param string $id style class id
 * @param string $class style class
 * @author Ozh
 */
function printImageMetadata($title = NULL, $toggle = true, $id = 'imagemetadata', $class = null, $span = NULL) {
	global $_zp_exifvars, $_zp_current_image;
	if (false === ($exif = getImageMetaData($_zp_current_image, true))) {
		return;
	}
	if (is_null($title)) {
		$title = gettext('Image Info');
	}
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	if (!$span) {
		$span = 'exif_link';
	}
	$dataid = $id . '_data';
	if ($id) {
		$id = ' id="' . $id . '"';
	}
	$refh = $refa = $style = '';
	if ($toggle == 'colorbox' && zp_has_filter('theme_head', 'colorbox::css')) {
		$refh = '<a href="#" class="colorbox" title="' . $title . '">';
		$refa = '</a>';
		$style = ' style="display:none"';
	} else if ($toggle) {
		$refh = '<a href="javascript:toggle(\'' . $dataid . '\');" title="' . $title . '">';
		$refa = '</a>';
		$style = ' style="display:none"';
	}
	?>
	<span id="<?php echo $span; ?>" class="metadata_title">
		<?php echo $refh; ?><?php echo $title; ?><?php echo $refa; ?>
	</span>
	<div id="<?php echo $dataid; ?>"<?php echo $style; ?>>
		<div<?php echo $id . $class; ?>>
			<table>
				<?php
				foreach ($exif as $field => $value) {
					$label = $_zp_exifvars[$field][2];
					echo "<tr><td class=\"label\">$label:</td><td class=\"value\">";
					switch ($_zp_exifvars[$field][6]) {
						case 'time':
							echo zpFormattedDate(DATE_FORMAT, strtotime($value));
							break;
						default:
							echo html_encode($value);
							break;
					}
					echo "</td></tr>\n";
				}
				?>
			</table>
		</div>
	</div>
	<?php
}

/**
 * Returns an array with the height & width
 *
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cw crop width
 * @param int $ch crop height
 * @param int $cx crop x axis
 * @param int $cy crop y axis
 * @return array
 */
function getSizeCustomImage($size, $width = NULL, $height = NULL, $cw = NULL, $ch = NULL, $cx = NULL, $cy = NULL) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$h = $_zp_current_image->getHeight();
	$w = $_zp_current_image->getWidth();
	if (isImageVideo()) { // size is determined by the player
		return array($w, $h);
	}
	$h = $_zp_current_image->getHeight();
	$w = $_zp_current_image->getWidth();
	$side = getOption('image_use_side');
	$us = getOption('image_allow_upscale');

	$args = getImageParameters(array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, NULL, NULL, NULL, NULL, NULL, NULL), $_zp_current_album->name);
	@list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $effects) = $args;
	if (!empty($size)) {
		$dim = $size;
		$width = $height = false;
	} else if (!empty($width)) {
		$dim = $width;
		$size = $height = false;
	} else if (!empty($height)) {
		$dim = $height;
		$size = $width = false;
	} else {
		$dim = 1;
	}

	if ($w == 0) {
		$hprop = 1;
	} else {
		$hprop = round(($h / $w) * $dim);
	}
	if ($h == 0) {
		$wprop = 1;
	} else {
		$wprop = round(($w / $h) * $dim);
	}

	if (($size && ($side == 'longest' && $h > $w) || ($side == 'height') || ($side == 'shortest' && $h < $w)) || $height) {
// Scale the height
		$newh = $dim;
		$neww = $wprop;
	} else {
// Scale the width
		$neww = $dim;
		$newh = $hprop;
	}
	if (!$us && $newh >= $h && $neww >= $w) {
		return array($w, $h);
	} else {
		if ($cw && $cw < $neww)
			$neww = $cw;
		if ($ch && $ch < $newh)
			$newh = $ch;
		if ($size && $ch && $cw) {
			$neww = $cw;
			$newh = $ch;
		}
		return array($neww, $newh);
	}
}

/**
 * Returns an array [width, height] of the default-sized image.
 *
 * @param int $size override the 'image_zize' option
 *
 * @return array
 */
function getSizeDefaultImage($size = NULL) {
	if (is_null($size))
		$size = getOption('image_size');
	return getSizeCustomImage($size);
}

/**
 * Returns an array [width, height] of the original image.
 *
 * @return array
 */
function getSizeFullImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return array($_zp_current_image->getWidth(), $_zp_current_image->getHeight());
}

/**
 * The width of the default-sized image (in printDefaultSizedImage)
 *
 * @param int $size override the 'image_zize' option
 *
 * @return int
 */
function getDefaultWidth($size = NULL) {
	$size_a = getSizeDefaultImage($size);
	return $size_a[0];
}

/**
 * Returns the height of the default-sized image (in printDefaultSizedImage)
 *
 * @param int $size override the 'image_zize' option
 *
 * @return int
 */
function getDefaultHeight($size = NULL) {
	$size_a = getSizeDefaultImage($size);
	return $size_a[1];
}

/**
 * Returns the width of the original image
 *
 * @return int
 */
function getFullWidth() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getWidth();
}

/**
 * Returns the height of the original image
 *
 * @return int
 */
function getFullHeight() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getHeight();
}

/**
 * Returns true if the image is landscape-oriented (width is greater than height)
 *
 * @return bool
 */
function isLandscape() {
	if (getFullWidth() >= getFullHeight())
		return true;
	return false;
}

/**
 * Returns the url to the default sized image.
 *
 * @return string
 */
function getDefaultSizedImage() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getSizedImage(getOption('image_size'));
}

/**
 * Show video player with video loaded or display the image.
 *
 * @param string $alt Alt text
 * @param string $class Optional style class
 * @param string $id Optional style id
 */
function printDefaultSizedImage($alt, $class = NULL, $id = NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	if (isImagePhoto()) { //Print images
		$html = '<img src="' . html_encode(pathurlencode(getDefaultSizedImage())) . '" alt="' . html_encode($alt) . '"' .
						' width="' . getDefaultWidth() . '" height="' . getDefaultHeight() . '"' .
						(($class) ? " class=\"$class\"" : "") .
						(($id) ? " id=\"$id\"" : "") . " />";
		$html = zp_apply_filter('standard_image_html', $html);
		echo $html;
	} else { // better be a plugin class then
		echo $_zp_current_image->getBody();
	}
}

/**
 * Returns the url to the thumbnail of the current image.
 *
 * @return string
 */
function getImageThumb() {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getThumb();
}

/**
 * @param string $alt Alt text
 * @param string $class optional class tag
 * @param string $id optional id tag
 */
function printImageThumb($alt, $class = NULL, $id = NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	$url = getImageThumb();
	$s = getOption('thumb_size');
	if (getOption('thumb_crop')) {
		$w = getOption('thumb_crop_width');
		$h = getOption('thumb_crop_height');
		if ($w > $h) {
//landscape
			$h = round($h * $s / $w);
			$w = $s;
		} else {
//portrait
			$w = round($w * $s / $h);
			$h = $s;
		}
	} else {
		$w = $h = $s;
		getMaxSpaceContainer($w, $h, $_zp_current_image, true);
	}
	$size = ' width="' . $w . '" height="' . $h . '"';
	$class = trim($class);
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	if ($id) {
		$id = ' id="' . $id . '"';
	}
	$html = '<img src="' . html_encode(pathurlencode($url)) . '"' . $size . ' alt="' . html_encode($alt) . '"' . $class . $id . " />";
	$html = zp_apply_filter('standard_image_thumb_html', $html);
	echo $html;
}

/**
 * Returns the url to original image.
 * It will return a protected image is the option "protect_full_image" is set
 *
 * @param $image optional image object
 * @return string
 */
function getFullImageURL($image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	$outcome = getOption('protect_full_image');
	if ($outcome == 'No access') {
		return NULL;
	}
	if ($outcome == 'Unprotected') {
		return $image->getFullImageURL();
	} else {
		return getProtectedImageURL($image, $outcome);
	}
}

/**
 * Returns the "raw" url to the image in the albums folder
 *
 * @param $image optional image object
 * @return string
 *
 */
function getUnprotectedImageURL($image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (!is_null($image)) {
		return $image->getFullImageURL();
	}
}

/**
 * Returns an url to the password protected/watermarked current image
 *
 * @param object $image optional image object overrides the current image
 * @param string $disposal set to override the 'protect_full_image' option
 * @return string
 * */
function getProtectedImageURL($image = NULL, $disposal = NULL) {
	global $_zp_current_image;
	if (is_null($disposal)) {
		$disposal = getOption('protect_full_image');
	}
	if ($disposal == 'No access')
		return NULL;
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		if (is_null($_zp_current_image))
			return false;
		$image = $_zp_current_image;
	}
	$album = $image->getAlbum();
	$watermark_use_image = getWatermarkParam($image, WATERMARK_FULL);
	if (!empty($watermark_use_image)) {
		$wmt = $watermark_use_image;
	} else {
		$wmt = false;
	}
	$args = array('FULL', NULL, NULL, NULL, NULL, NULL, NULL, (int) getOption('full_image_quality'), NULL, NULL, NULL, $wmt, false, NULL, NULL);
	$cache_file = getImageCacheFilename($album->name, $image->filename, $args);
	$cache_path = SERVERCACHE . $cache_file;
	if ($disposal != 'Download' && OPEN_IMAGE_CACHE && file_exists($cache_path)) {
		return WEBPATH . '/' . CACHEFOLDER . pathurlencode(imgSrcURI($cache_file));
	} else if ($disposal == 'Unprotected') {
		return getImageURI($args, $album->name, $image->filename, $image->filemtime);
	} else {
		$params = '&q=' . getOption('full_image_quality');
		if (!empty($watermark_use_image)) {
			$params .= '&wmk=' . $watermark_use_image;
		}
		if ($disposal) {
			$params .= '&dsp=' . $disposal;
		}
		$params .= '&check=' . sha1(HASH_SEED . serialize($args));
		if (is_array($image->filename)) {
			$album = dirname($image->filename['source']);
			$image = basename($image->filename['source']);
		} else {
			$album = $album->name;
			$image = $image->filename;
		}
		return WEBPATH . '/' . ZENFOLDER . '/full-image.php?a=' . $album . '&i=' . $image . $params;
	}
}

/**
 * Returns a link to the current image custom sized to $size
 *
 * @param int $size The size the image is to be
 */
function getSizedImageURL($size) {
	return getCustomImageURL($size);
}

/**
 * Returns the url to the image with the dimensions you define with this function.
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $thumbStandin set true to inhibit watermarking
 * @param bool $effects image effects (e.g. set gray to force to grayscale)
 * @return string
 *
 * $size, $width, and $height are used in determining the final image size.
 * At least one of these must be provided. If $size is provided, $width and
 * $height are ignored. If both $width and $height are provided, the image
 * will have those dimensions regardless of the original image height/width
 * ratio. (Yes, this means that the image may be distorted!)
 *
 * The $crop* parameters determine the portion of the original image that
 * will be incorporated into the final image.
 *
 * $cropw and $croph "sizes" are typically proportional. That is you can
 * set them to values that reflect the ratio of width to height that you
 * want for the final image. Typically you would set them to the final
 * height and width. These values will always be adjusted so that they are
 * not larger than the original image dimensions.
 *
 * The $cropx and $cropy values represent the offset of the crop from the
 * top left corner of the image. If these values are provided, the $croph
 * and $cropw parameters are treated as absolute pixels not proportions of
 * the image. If cropx and cropy are not provided, the crop will be
 * "centered" in the image.
 *
 * When $cropx and $cropy are not provided the crop is offset from the top
 * left proportionally to the ratio of the final image size and the crop
 * size.
 *
 * Some typical croppings:
 *
 * $size=200, $width=NULL, $height=NULL, $cropw=200, $croph=100,
 * $cropx=NULL, $cropy=NULL produces an image cropped to a 2x1 ratio which
 * will fit in a 200x200 pixel frame.
 *
 * $size=NULL, $width=200, $height=NULL, $cropw=200, $croph=100, $cropx=100,
 * $cropy=10 will will take a 200x100 pixel slice from (10,100) of the
 * picture and create a 200x100 image
 *
 * $size=NULL, $width=200, $height=100, $cropw=200, $croph=120, $cropx=NULL,
 * $cropy=NULL will produce a (distorted) image 200x100 pixels from a 1x0.6
 * crop of the image.
 *
 * $size=NULL, $width=200, $height=NULL, $cropw=180, $croph=120, $cropx=NULL, $cropy=NULL
 * will produce an image that is 200x133 from a 1.5x1 crop that is 5% from the left
 * and 15% from the top of the image.
 *
 */
function getCustomImageURL($size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $thumbStandin = false, $effects = NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects);
}

/**
 * Print normal video or custom sized images.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * Notes on cropping:
 *
 * The $crop* parameters determine the portion of the original image that will be incorporated
 * into the final image. The w and h "sizes" are typically proportional. That is you can set them to
 * values that reflect the ratio of width to height that you want for the final image. Typically
 * you would set them to the fincal height and width.
 *
 * @param string $alt Alt text for the url
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop x axis
 * @param int $cropy crop y axis
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param bool $thumbStandin set to true to treat as thumbnail
 * @param bool $effects image effects (e.g. set gray to force grayscale)

 * */
function printCustomSizedImage($alt, $size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $class = NULL, $id = NULL, $thumbStandin = false, $effects = NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return;
	if (!$_zp_current_image->getShow()) {
		$class .= " not_visible";
	}
	$album = $_zp_current_image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	if ($size) {
		$dims = getSizeCustomImage($size);
		$sizing = ' width="' . $dims[0] . '" height="' . $dims[1] . '"';
	} else {
		$sizing = '';
		if ($width)
			$sizing .= ' width="' . $width . '"';
		if ($height)
			$sizing .= ' height="' . $height . '"';
	}
	if ($id)
		$id = ' id="' . $id . '"';
	if ($class)
		$id .= ' class="' . $class . '"';
	if (isImagePhoto() || $thumbStandin) {
		$html = '<img src="' . html_encode(pathurlencode(getCustomImageURL($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects))) . '"' .
						' alt="' . html_encode($alt) . '"' .
						$id .
						$sizing .
						' />';
		$html = zp_apply_filter('custom_image_html', $html, $thumbStandin);
		echo $html;
	} else { // better be a plugin
		echo $_zp_current_image->getBody($width, $height);
	}
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized images.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	getMaxSpaceContainer($width, $height, $_zp_current_image);
	return getCustomImageURL(NULL, $width, $height);
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized thumbnails.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageThumbMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	getMaxSpaceContainer($width, $height, $_zp_current_image, true);
	return getCustomImageURL(NULL, $width, $height, NULL, NULL, NULL, NULL, true);
}

/**
 * Creates image thumbnails which will fit un-cropped within the width & height parameters given
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 */
function printCustomSizedImageThumbMaxSpace($alt, $width, $height, $class = NULL, $id = NULL) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return;
	getMaxSpaceContainer($width, $height, $_zp_current_image, true);
	printCustomSizedImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id, true);
}

/**
 * Print normal video or un-cropped within the given height and width dimensions. Use for sized images or thumbnails in an album.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 */
function printCustomSizedImageMaxSpace($alt, $width, $height, $class = NULL, $id = NULL, $thumb = false) {
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return;
	getMaxSpaceContainer($width, $height, $_zp_current_image, $thumb);
	printCustomSizedImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id, $thumb);
}

/**
 * Prints link to an image of specific size
 * @param int $size how big
 * @param string $text URL text
 * @param string $title URL title
 * @param string $class optional URL class
 * @param string $id optional URL id
 */
function printSizedImageLink($size, $text, $title, $class = NULL, $id = NULL) {
	printLink(getSizedImageURL($size), $text, $title, $class, $id);
}

/**
 *
 * performs a query and then filters out "illegal" images returning the first "good" image
 * used by the random image functions.
 *
 * @param object $result query result
 * @param string $source album object if this is search within the album
 */
function filterImageQuery($result, $source) {
	if ($result) {
		while ($row = db_fetch_assoc($result)) {
			$image = newImage(NULL, $row);
			$album = $image->album;
			if ($album->name == $source || $album->checkAccess()) {
				if (isImagePhoto($image)) {
					if ($image->checkAccess()) {
						return $image;
					}
				}
			}
		}
		db_free_result($result);
	}
	return NULL;
}

/**
 * Returns a randomly selected image from the gallery. (May be NULL if none exists)
 * @param bool $daily set to true and the picture changes only once a day.
 *
 * @return object
 */
function getRandomImages($daily = false) {
	global $_zp_gallery;
	if ($daily) {
		$potd = unserialize(getOption('picture_of_the_day'));
		if (date('Y-m-d', $potd['day']) == date('Y-m-d')) {
			$album = newAlbum($potd['folder'], true, true);
			if ($album->exists) {
				$image = newImage($album, $potd['filename'], true);
				if ($image->exists) {
					return $image;
				}
			}
		}
	}
	if (zp_loggedin()) {
		$imageWhere = '';
	} else {
		$imageWhere = " AND " . prefix('images') . ".show=1";
	}
	$result = query('SELECT `folder`, `filename` ' .
					' FROM ' . prefix('images') . ', ' . prefix('albums') .
					' WHERE ' . prefix('albums') . '.folder!="" AND ' . prefix('images') . '.albumid = ' .
					prefix('albums') . '.id ' . $imageWhere . ' ORDER BY RAND()');

	$image = filterImageQuery($result, NULL);
	if ($image) {
		if ($daily) {
			$potd = array('day' => time(), 'folder' => $image->getAlbumName(), 'filename' => $image->getFileName());
			setThemeOption('picture_of_the_day', serialize($potd), NULL, $_zp_gallery->getCurrentTheme());
		}
		return $image;
	}
	return NULL;
}

/**
 * Returns  a randomly selected image from the album or its subalbums. (May be NULL if none exists)
 *
 * @param mixed $rootAlbum optional album object/folder from which to get the image.
 * @param bool $daily set to true to change picture only once a day.
 *
 * @return object
 */
function getRandomImagesAlbum($rootAlbum = NULL, $daily = false) {
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
	if ($daily && ($potd = getOption('picture_of_the_day:' . $album->name))) {
		$potd = unserialize($potd);
		if (date('Y-m-d', $potd['day']) == date('Y-m-d')) {
			$rndalbum = newAlbum($potd['folder']);
			$image = newImage($rndalbum, $potd['filename']);
			if ($image->exists)
				return $image;
		}
	}
	$image = NULL;
	if ($album->isDynamic()) {
		$images = $album->getImages(0);
		shuffle($images);
		while (count($images) > 0) {
			$result = array_pop($images);
			if (is_valid_image($result['filename'])) {
				$image = newImage(newAlbum($result['folder']), $result['filename']);
			}
		}
	} else {
		$albumfolder = $album->getFolder();
		if ($album->isMyItem(LIST_RIGHTS)) {
			$imageWhere = '';
			$albumInWhere = '';
		} else {
			$imageWhere = " AND " . prefix('images') . ".show=1";
			$albumInWhere = prefix('albums') . ".show=1";
		}

		$query = "SELECT id FROM " . prefix('albums') . " WHERE ";
		if ($albumInWhere)
			$query .= $albumInWhere . ' AND ';
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
							prefix('albums') . '.id ' . $albumInWhere . $imageWhere . ' ORDER BY RAND()';
			$result = query($sql);
			$image = filterImageQuery($result, $album->name);
		}
	}
	if ($image) {
		if ($daily) {
			$potd = array('day' => time(), 'folder' => $image->getAlbumName(), 'filename' => $image->getFileName());
			setThemeOption('picture_of_the_day:' . $album->name, serialize($potd), NULL, $_zp_gallery->getCurrentTheme());
		}
	}
	return $image;
}

/**
 * Puts up random image thumbs from the gallery
 *
 * @param int $number how many images
 * @param string $class optional class
 * @param string $option what you want selected: all for all images, album for selected ones from an album
 * @param mixed $rootAlbum optional album object/folder from which to get the image.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size.
 * @param integer $height the height/cropheight of the thumb if crop=true else not used
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 */
function printRandomImages($number = 5, $class = null, $option = 'all', $rootAlbum = '', $width = NULL, $height = NULL, $crop = NULL, $fullimagelink = false) {
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
				$randomImageURL = $randomImage->getFullimageURL();
			} else {
				$randomImageURL = getURL($randomImage);
			}
			echo '<a href="' . html_encode($randomImageURL) . '" title="' . sprintf(gettext('View image: %s'), html_encode($randomImage->getTitle())) . '">';
			switch ($crop) {
				case 0:
					$html = "<img src=\"" . html_encode(pathurlencode($randomImage->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . "\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
					break;
				case 1:
					$html = "<img src=\"" . html_encode(pathurlencode($randomImage->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . "\" alt=\"" . $randomImage->getTitle() . "\" />\n";
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

/**
 * Returns a list of tags for context of the page called where called
 *
 * @return string
 * @since 1.1
 */
function getTags() {
	if (in_context(ZP_IMAGE)) {
		global $_zp_current_image;
		return $_zp_current_image->getTags();
	} else if (in_context(ZP_ALBUM)) {
		global $_zp_current_album;
		return $_zp_current_album->getTags();
	} else if (in_context(ZP_ZENPAGE_PAGE)) {
		global $_zp_current_zenpage_page;
		return $_zp_current_zenpage_page->getTags();
	} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		global $_zp_current_zenpage_news;
		return $_zp_current_zenpage_news->getTags();
	}
	return array();
}

/**
 * Prints a list of tags, editable by admin
 *
 * @param string $option links by default, if anything else the
 *               tags will not link to all other images with the same tag
 * @param string $preText text to go before the printed tags
 * @param string $class css class to apply to the div surrounding the UL list
 * @param string $separator what charactor shall separate the tags
 * @since 1.1
 */
function printTags($option = 'links', $preText = NULL, $class = NULL, $separator = ', ') {
	global $_zp_current_search;
	if (is_null($class)) {
		$class = 'taglist';
	}
	$singletag = getTags();
	$tagstring = implode(', ', $singletag);
	if ($tagstring === '' or $tagstring === NULL) {
		$preText = '';
	}
	if (in_context(ZP_IMAGE)) {
		$object = "image";
	} else if (in_context(ZP_ALBUM)) {
		$object = "album";
	} else if (in_context(ZP_ZENPAGE_PAGE)) {
		$object = "pages";
	} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		$object = "news";
	}
	if (count($singletag) > 0) {
		if (!empty($preText)) {
			echo "<span class=\"tags_title\">" . $preText . "</span>";
		}
		echo "<ul class=\"" . $class . "\">\n";
		if (is_object($_zp_current_search)) {
			$albumlist = $_zp_current_search->getAlbumList();
		} else {
			$albumlist = NULL;
		}
		$ct = count($singletag);
		$x = 0;
		foreach ($singletag as $atag) {
			if (++$x == $ct) {
				$separator = "";
			}
			if ($option === "links") {
				$links1 = "<a href=\"" . html_encode(getSearchURL(search_quote($atag), '', 'tags', 0, array('albums' => $albumlist))) . "\" title=\"" . html_encode($atag) . "\" rel=\"nofollow\">";
				$links2 = "</a>";
			} else {
				$links1 = $links2 = '';
			}
			echo "\t<li>" . $links1 . $atag . $links2 . $separator . "</li>\n";
		}
		echo "</ul>";
	} else {
		echo "$tagstring";
	}
}

/**
 * Either prints all of the galleries tgs as a UL list or a cloud
 *
 * @param string $option "cloud" for tag cloud, "list" for simple list
 * @param string $class CSS class
 * @param string $sort "results" for relevance list, "random" for random ordering, otherwise the list is alphabetical
 * @param bool $counter TRUE if you want the tag count within brackets behind the tag
 * @param bool $links set to TRUE to have tag search links included with the tag.
 * @param int $maxfontsize largest font size the cloud should display
 * @param int $maxcount the floor count for setting the cloud font size to $maxfontsize
 * @param int $mincount the minimum count for a tag to appear in the output
 * @param int $limit set to limit the number of tags displayed to the top $numtags
 * @param int $minfontsize minimum font size the cloud should display
 * @since 1.1
 */
function printAllTagsAs($option, $class = '', $sort = NULL, $counter = FALSE, $links = TRUE, $maxfontsize = 2, $maxcount = 50, $mincount = 10, $limit = NULL, $minfontsize = 0.8) {
	global $_zp_current_search;
	$option = strtolower($option);
	if ($class != "") {
		$class = ' class="' . $class . '"';
	}
	$tagcount = getAllTagsCount();
	if (!is_array($tagcount)) {
		return false;
	}
	switch ($sort) {
		case 'results':
			arsort($tagcount);
			if (!is_null($limit)) {
				$tagcount = array_slice($tagcount, 0, $limit);
			}
			break;
		case 'random':
			if (!is_null($limit)) {
				$tagcount = array_slice($tagcount, 0, $limit);
			}
			shuffle_assoc($tagcount);
			break;
		default:
			break;
	}
	?>
	<ul<?php echo $class; ?>>
		<?php
		if (count($tagcount) > 0) {
			foreach ($tagcount as $key => $val) {
				if (!$counter) {
					$counter = "";
				} else {
					$counter = " (" . $val . ") ";
				}
				if ($option == "cloud") { // calculate font sizes, formula from wikipedia
					if ($val <= $mincount) {
						$size = $minfontsize;
					} else {
						$size = min(max(round(($maxfontsize * ($val - $mincount)) / ($maxcount - $mincount), 2), $minfontsize), $maxfontsize);
					}
					$size = str_replace(',', '.', $size);
					$size = ' style="font-size:' . $size . 'em;"';
				} else {
					$size = '';
				}
				if ($val >= $mincount) {
					if ($links) {
						if (is_object($_zp_current_search)) {
							$albumlist = $_zp_current_search->getAlbumList();
						} else {
							$albumlist = NULL;
						}
						$link = getSearchURL(search_quote($key), '', 'tags', 0, array('albums' => $albumlist));
						?>
						<li>
							<a href="<?php echo html_encode($link); ?>" rel="nofollow"<?php echo $size; ?>><?php echo $key . $counter; ?></a>
						</li>
						<?php
					} else {
						?>
						<li<?php echo $size; ?>><?php echo $key . $counter; ?></li>
						<?php
					}
				}
			} // while end
		} else {
			?>
			<li><?php echo gettext('No popular tags'); ?></li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * Retrieves a list of all unique years & months from the images in the gallery
 *
 * @param string $order set to 'desc' for the list to be in descending order
 * @return array
 */
function getAllDates($order = 'asc') {
	$alldates = array();
	$cleandates = array();
	$sql = "SELECT `date` FROM " . prefix('images');
	if (!zp_loggedin()) {
		$sql .= " WHERE `show` = 1";
	}
	$hidealbums = getNotViewableAlbums();
	if (!is_null($hidealbums)) {
		if (zp_loggedin()) {
			$sql .= ' WHERE ';
		} else {
			$sql .= ' AND ';
		}
		foreach ($hidealbums as $id) {
			$sql .= '`albumid`!=' . $id . ' AND ';
		}
		$sql = substr($sql, 0, -5);
	}
	$result = query($sql);
	if ($result) {
		while ($row = db_fetch_assoc($result)) {
			$alldates[] = $row['date'];
		}
		db_free_result($result);
	}
	foreach ($alldates as $adate) {
		if (!empty($adate)) {
			$cleandates[] = substr($adate, 0, 7) . "-01";
		}
	}
	$datecount = array_count_values($cleandates);
	if ($order == 'desc') {
		krsort($datecount);
	} else {
		ksort($datecount);
	}
	return $datecount;
}

/**
 * Prints a compendum of dates and links to a search page that will show results of the date
 *
 * @param string $class optional class
 * @param string $yearid optional class for "year"
 * @param string $monthid optional class for "month"
 * @param string $order set to 'desc' for the list to be in descending order
 */
function printAllDates($class = 'archive', $yearid = 'year', $monthid = 'month', $order = 'asc') {
	global $_zp_current_search, $_zp_gallery_page;
	if (empty($class)) {
		$classactive = 'archive_active';
	} else {
		$classactive = $class . '_active';
		$class = "class=\"$class\"";
	}
	if ($_zp_gallery_page == 'search.php') {
		$activedate = getSearchDate('%Y-%m');
	} else {
		$activedate = '';
	}
	if (!empty($yearid)) {
		$yearid = "class=\"$yearid\"";
	}
	if (!empty($monthid)) {
		$monthid = "class=\"$monthid\"";
	}
	$datecount = getAllDates($order);
	$lastyear = "";
	echo "\n<ul $class>\n";
	$nr = 0;
	while (list($key, $val) = each($datecount)) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
		}

		if ($lastyear != $year) {
			$lastyear = $year;
			if ($nr != 1) {
				echo "</ul>\n</li>\n";
			}
			echo "<li $yearid>$year\n<ul $monthid>\n";
		}
		if (is_object($_zp_current_search)) {
			$albumlist = $_zp_current_search->getAlbumList();
		} else {
			$albumlist = NULL;
		}
		$datekey = substr($key, 0, 7);
		if ($activedate = $datekey) {
			$cl = ' class="' . $classactive . '"';
		} else {
			$cl = '';
		}
		echo "<li" . $cl . "><a href=\"" . html_encode(getSearchURl('', $datekey, '', 0, array('allbums' => $albumlist))) . "\" rel=\"nofollow\">$month ($val)</a></li>\n";
	}
	echo "</ul>\n</li>\n</ul>\n";
}

/**
 * Produces the url to a custom page (e.g. one that is not album.php, image.php, or index.php)
 *
 * @param string $linktext Text for the URL
 * @param string $page page name to include in URL
 * @param string $q query string to add to url
 * @return string
 */
function getCustomPageURL($page, $q = '') {
	global $_zp_current_album, $_zp_conf_vars;
	if (array_key_exists($page, $_zp_conf_vars['special_pages'])) {
		$result_r = preg_replace('~^_PAGE_/~', _PAGE_ . '/', $_zp_conf_vars['special_pages'][$page]['rewrite']);
	} else {
		$result_r = '/' . _PAGE_ . '/' . $page;
	}
	$result = "index.php?p=$page";

	if (!empty($q)) {
		$result_r .= "?$q";
		$result .= "&$q";
	}
	return rewrite_path($result_r, $result);
}

/**
 * Prints the url to a custom page (e.g. one that is not album.php, image.php, or index.php)
 *
 * @param string $linktext Text for the URL
 * @param string $page page name to include in URL
 * @param string $q query string to add to url
 * @param string $prev text to insert before the URL
 * @param string $next text to follow the URL
 * @param string $class optional class
 */
function printCustomPageURL($linktext, $page, $q = '', $prev = '', $next = '', $class = NULL) {
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	echo $prev . "<a href=\"" . html_encode(getCustomPageURL($page, $q)) . "\" $class title=\"" . html_encode($linktext) . "\">" . html_encode($linktext) . "</a>" . $next;
}

/**
 * Returns the URL to an image (This is NOT the URL for the image.php page)
 *
 * @param object $image the image
 * @return string
 */
function getURL($image) {
	return rewrite_path(pathurlencode($image->getAlbumName()) . "/" . urlencode($image->filename), "/index.php?album=" . pathurlencode($image->getAlbumName()) . "&image=" . urlencode($image->filename));
}

//*** Search functions *******************************************************
//****************************************************************************

/**
 * tests if a search page is an "archive" page
 *
 * @return bool
 */
function isArchive() {
	return isset($_REQUEST['date']);
}

/**
 * Returns a search URL
 *
 * @param mixed $words the search words target
 * @param mixed $dates the dates that limit the search
 * @param mixed $fields the fields on which to search
 * @param int $page the page number for the URL
 * @param array $object_list the list of objects to search
 * @return string
 * @since 1.1.3
 */
function getSearchURL($words, $dates, $fields, $page, $object_list = NULL) {
	if (!is_null($object_list)) {
		if (array_key_exists(0, $object_list)) { // handle old form albums list
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions.php');
			deprecated_functions::notify(gettext('getSearchURL $album_list parameter is deprecated. Pass array("albums"=>array(album, album, ...)) instead.'));
			$object_list = array('albums' => $object_list);
		}
	}
	$urls = '';
	$rewrite = false;
	if (MOD_REWRITE) {
		$rewrite = true;
		if (is_array($object_list)) {
			foreach ($object_list as $obj) {
				if ($obj) {
					$rewrite = false;
					break;
				}
			}
		}
	}

	if ($rewrite) {
		if (empty($dates)) {
			$url = SEO_WEBPATH . '/' . _SEARCH_ . '/';
		} else {
			$url = SEO_WEBPATH . '/' . _ARCHIVE_ . '/';
		}
	} else {
		$url = SEO_WEBPATH . "/index.php?p=search";
	}
	if (!empty($fields) && empty($dates)) {
		if (!is_array($fields)) {
			$fields = explode(',', $fields);
		}
		$temp = $fields;
		if ($rewrite && count($fields) == 1 && array_shift($temp) == 'tags') {
			$url = SEO_WEBPATH . '/' . _TAGS_ . '/';
		} else {
			$search = new SearchEngine();
			$urls = $search->getSearchFieldsText($fields, 'searchfields=');
		}
	}

	if (!empty($words)) {
		if (is_array($words)) {
			foreach ($words as $key => $word) {
				$words[$key] = search_quote($word);
			}
			$words = implode(',', $words);
		}
		$words = strtr($words, array('%' => '__25__', '&' => '__26__', '#' => '__23__'));
		if ($rewrite) {
			$url .= urlencode($words);
		} else {
			$url .= "&words=" . urlencode($words);
		}
	}
	if (!empty($dates)) {
		if (is_array($dates)) {
			$dates = implode(',', $dates);
		}
		if ($rewrite) {
			$url .= $dates;
		} else {
			$url .= "&date=$dates";
		}
	}
	if ($page > 1) {
		if ($rewrite) {
			$url .= "/$page";
		} else {
			if ($urls) {
				$urls .= '&';
			}
			$urls .= "page=$page";
		}
	}
	if (!empty($urls)) {
		if ($rewrite) {
			$url .= '?' . $urls;
		} else {
			$url .= '&' . $urls;
		}
	}
	if (is_array($object_list)) {
		foreach ($object_list as $key => $list) {
			if (!empty($list)) {
				$url .= '&in' . $key . '=' . html_encode(implode(',', $list));
			}
		}
	}
	return $url;
}

/**
 * Prints the search form
 *
 * Search works on a list of tokens entered into the search form.
 *
 * Tokens may be part of boolean expressions using &, |, !, and parens. (Comma is retained as a synonom of | for
 * backwords compatibility.)
 *
 * Tokens may be enclosed in quotation marks to create exact pattern matches or to include the boolean operators and
 * parens as part of the tag..
 *
 * @param string $prevtext text to go before the search form
 * @param string $id css id for the search form, default is 'search'
 * @param string $buttonSource optional path to the image for the button or if not a path to an image,
 * 											this will be the button hint
 * @param string $buttontext optional text for the button ("Search" will be the default text)
 * @param string $iconsource optional theme based icon for the search fields toggle
 * @param array $query_fields override selection for enabled fields with this list
 * @param array $objects_list optional array of things to search eg. [albums]=>[list], etc.
 * 														if the list is simply 0, the objects will be omitted from the search
 * @param string $within set to true to search within current results, false to search fresh
 * @since 1.1.3
 */
function printSearchForm($prevtext = NULL, $id = 'search', $buttonSource = NULL, $buttontext = '', $iconsource = NULL, $query_fields = NULL, $object_list = NULL, $within = NULL) {
	global $_zp_adminJS_loaded, $_zp_current_search;
	$engine = new SearchEngine();
	if (!is_null($_zp_current_search) && !$_zp_current_search->getSearchWords()) {
		$engine->clearSearchWords();
	}
	if (!is_null($object_list)) {
		if (array_key_exists(0, $object_list)) { // handle old form albums list
			trigger_error(gettext('printSearchForm $album_list parameter is deprecated. Pass array("albums"=>array(album, album, ...)) instead.'), E_USER_NOTICE);
			$object_list = array('albums' => $object_list);
		}
	}
	if (empty($buttontext)) {
		$buttontext = gettext("Search");
	}
	$zf = WEBPATH . "/" . ZENFOLDER;
	$searchwords = $engine->codifySearchString();
	if (substr($searchwords, -1, 1) == ',') {
		$searchwords = substr($searchwords, 0, -1);
	}
	if (empty($searchwords)) {
		$within = false;
		$hint = '%s';
	} else {
		$hint = gettext('%s within previous results');
	}
	if (preg_match('!\/(.*)[\.png|\.jpg|\.jpeg|\.gif]$!', $buttonSource)) {
		$buttonSource = 'src="' . $buttonSource . '" alt="' . $buttontext . '"';
		$button = 'title="' . sprintf($hint, $buttontext) . '"';
		$type = 'image';
	} else {
		$type = 'submit';
		if ($buttonSource) {
			$button = 'value="' . $buttontext . '" title="' . sprintf($hint, $buttonSource) . '"';
			$buttonSource = '';
		} else {
			$button = 'value="' . $buttontext . '" title="' . sprintf($hint, $buttontext) . '"';
		}
	}
	if (empty($iconsource)) {
		$iconsource = WEBPATH . '/' . ZENFOLDER . '/images/searchfields_icon.png';
	}
	if (is_null($within)) {
		$within = getOption('search_within');
	}
	if (MOD_REWRITE) {
		$searchurl = SEO_WEBPATH . '/' . _SEARCH_ . '/';
	} else {
		$searchurl = WEBPATH . "/index.php?p=search";
	}
	if (!$within) {
		$engine->clearSearchWords();
	}

	$fields = $engine->allowedSearchFields();
	if (!$_zp_adminJS_loaded) {
		$_zp_adminJS_loaded = true;
		?>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/admin.js"></script>
		<?php
	}
	?>
	<div id="<?php echo $id; ?>">
		<!-- search form -->
		<form method="post" action="<?php echo $searchurl; ?>" id="search_form">
			<script type="text/javascript">
		// <!-- <![CDATA[
		var within = <?php echo (int) $within; ?>;
		function search_(way) {
			within = way;
			if (way) {
				$('#search_submit').attr('title', '<?php echo sprintf($hint, $buttontext); ?>');

			} else {
				lastsearch = '';
				$('#search_submit').attr('title', '<?php echo $buttontext; ?>');
			}
			$('#search_input').val('');
		}
		$('#search_form').submit(function() {
			if (within) {
				var newsearch = $.trim($('#search_input').val());
				if (newsearch.substring(newsearch.length - 1) == ',') {
					newsearch = newsearch.substr(0, newsearch.length - 1);
				}
				if (newsearch.length > 0) {
					$('#search_input').val('(<?php echo $searchwords; ?>) AND (' + newsearch + ')');
				} else {
					$('#search_input').val('<?php echo $searchwords; ?>');
				}
			}
			return true;
		});
		// ]]> -->
			</script>
			<?php echo $prevtext; ?>
			<div>
				<input type="text" name="words" value="" id="search_input" size="10" />
				<?php if (count($fields) > 1 || $searchwords) { ?>
					<a href="javascript:toggle('searchextrashow');" ><img src="<?php echo $iconsource; ?>" title="<?php echo gettext('search options'); ?>" alt="<?php echo gettext('fields'); ?>" id="searchfields_icon" /></a>
				<?php } ?>
				<input type="<?php echo $type; ?>" <?php echo $button; ?> class="button buttons" id="search_submit" <?php echo $buttonSource; ?> />
				<?php
				if (is_array($object_list)) {
					foreach ($object_list as $key => $list) {
						?>
						<input type="hidden" name="in<?php echo $key ?>" value="<?php
						if (is_array($list))
							echo html_encode(implode(',', $list));
						else
							echo html_encode($list);
						?>" />
									 <?php
								 }
							 }
							 ?>
				<br />
				<?php
				if (count($fields) > 1 || $searchwords) {
					$fields = array_flip($fields);
					natcasesort($fields);
					$fields = array_flip($fields);
					if (is_null($query_fields)) {
						$query_fields = $engine->parseQueryFields();
					} else {
						if (!is_array($query_fields)) {
							$query_fields = $engine->numericFields($query_fields);
						}
					}
					if (count($query_fields) == 0) {
						$query_fields = $engine->allowedSearchFields();
					}
					?>
					<div style="display:none;" id="searchextrashow">
						<?php
						if ($searchwords) {
							?>
							<label>
								<input type="radio" name="search_within" id="search_within-1" value="1"<?php if ($within) echo ' checked="checked"'; ?> onclick="search_(1);" />
								<?php echo gettext('Within'); ?>
							</label>
							<label>
								<input type="radio" name="search_within" id="search_within-0" value="1"<?php if (!$within) echo ' checked="checked"'; ?> onclick="search_(0);" />
								<?php echo gettext('New'); ?>
							</label>
							<?php
						}
						if (count($fields) > 1) {
							?>
							<ul>
								<?php
								foreach ($fields as $display => $key) {
									echo '<li><label><input id="SEARCH_' . $key . '" name="SEARCH_' . $key . '" type="checkbox"';
									if (in_array($key, $query_fields)) {
										echo ' checked="checked" ';
									}
									echo ' value="' . $key . '"  /> ' . $display . "</label></li>" . "\n";
								}
								?>
							</ul>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</form>
	</div><!-- end of search form -->
	<?php
}

/**
 * Returns the a sanitized version of the search string
 *
 * @return string
 * @since 1.1
 */
function getSearchWords() {
	global $_zp_current_search;
	if (!in_context(ZP_SEARCH))
		return '';
	return stripcslashes($_zp_current_search->codifySearchString());
}

/**
 * Returns the date of the search
 *
 * @param string $format formatting of the date, default 'F Y'
 * @return string
 * @since 1.1
 */
function getSearchDate($format = '%B %Y') {
	if (in_context(ZP_SEARCH)) {
		global $_zp_current_search;
		$date = $_zp_current_search->getSearchDate();
		if (empty($date)) {
			return "";
		}
		if ($date == '0000-00') {
			return gettext("no date");
		};
		$dt = strtotime($date . "-01");
		return zpFormattedDate($format, $dt);
	}
	return false;
}

define("IMAGE", 1);
define("ALBUM", 2);

/**
 * Checks to see if comment posting is allowed for an image/album

 * Returns true if comment posting should be allowed
 *
 * @param int $what the degree of control desired allowed values: ALBUM, IMAGE, and ALBUM+IMAGE
 * @return bool
 */
function openedForComments($what = 3) {
	global $_zp_current_image, $_zp_current_album;
	$result = true;
	if (IMAGE & $what) {
		$result = $result && $_zp_current_image->getCommentsAllowed();
	}
	if (ALBUM & $what) {
		$result = $result && $_zp_current_album->getCommentsAllowed();
	}
	return $result;
}

/**
 * controls the thumbnail layout of themes.
 *
 * Uses the theme options:
 * 	albums_per_row
 * 	albums_per_page
 * 	images_per_row
 * 	images_per_page
 *
 * Computes a normalized images/albums per page and computes the number of
 * images that will fit on the "transitional" page between album thumbs and
 * image thumbs. This function is "internal" and is called from the root
 * index.php script before the theme script is loaded.
 */
function setThemeColumns() {
	global $_zp_current_album, $_firstPageImages, $_oneImagePage;
	$_firstPageImages = false;
	if (($albumColumns = getOption('albums_per_row')) <= 1)
		$albumColumns = false;
	if (($imageColumns = getOption('images_per_row')) <= 1)
		$imageColumns = false;
	$albcount = max(1, getOption('albums_per_page'));
	if (($albumColumns) && (($albcount % $albumColumns) != 0)) {
		setOption('albums_per_page', $albcount = ((floor($albcount / $albumColumns) + 1) * $albumColumns), false);
	}
	$imgcount = max(1, getOption('images_per_page'));
	if (($imageColumns) && (($imgcount % $imageColumns) != 0)) {
		setOption('images_per_page', $imgcount = ((floor($imgcount / $imageColumns) + 1) * $imageColumns), false);
	}
	if ((getOption('thumb_transition') && !$_oneImagePage) && in_context(ZP_ALBUM | ZP_SEARCH) && $albumColumns && $imageColumns) {
		$count = getNumAlbums();
		if ($count == 0) {
			$_firstPageImages = 0;
		}
		$rowssused = ceil(($count % $albcount) / $albumColumns); /* number of album rows unused */
		$leftover = floor(max(1, getOption('images_per_page')) / $imageColumns) - $rowssused;
		$_firstPageImages = max(0, $leftover * $imageColumns); /* number of images that fill the leftover rows */
		if ($_firstPageImages == $imgcount) {
			$_firstPageImages = 0;
		}
	}
}

//************************************************************************************************
// album password handling
//************************************************************************************************

/**
 * returns the auth type of a guest login
 *
 * @param string $hint
 * @param string $show
 * @return string
 */
function checkForGuest(&$hint = NULL, &$show = NULL) {
	global $_zp_gallery, $_zp_gallery_page, $_zp_current_zenpage_page, $_zp_current_category, $_zp_current_zenpage_news;
	$authType = zp_apply_filter('checkForGuest', NULL);
	if (!is_null($authType))
		return $authType;
	if (in_context(ZP_SEARCH)) { // search page
		$hash = getOption('search_password');
		$show = (getOption('search_user') != '');
		$hint = get_language_string(getOption('search_hint'));
		$authType = 'zp_search_auth';
		if (empty($hash)) {
			$hash = $_zp_gallery->getPassword();
			$show = $_zp_gallery->getUser() != '';
			$hint = $_zp_gallery->getPasswordHint();
			$authType = 'zp_gallery_auth';
		}
		if (!empty($hash) && zp_getCookie($authType) == $hash) {
			return $authType;
		}
	} else if (!is_null($_zp_current_zenpage_news)) {
		$authType = $_zp_current_zenpage_news->checkAccess($hint, $show);
		return $authType;
	} else if (isset($_GET['album'])) { // album page
		list($album, $image) = rewrite_get_album_image('album', 'image');
		if ($authType = checkAlbumPassword($album, $hint)) {
			return $authType;
		} else {
			$alb = newAlbum($album);
			$show = $alb->getUser() != '';
			return false;
		}
	} else { // other page
		$hash = $_zp_gallery->getPassword();
		$show = $_zp_gallery->getUser() != '';
		$hint = $_zp_gallery->getPasswordHint();
		if (!empty($hash) && zp_getCookie('zp_gallery_auth') == $hash) {
			return 'zp_gallery_auth';
		}
	}
	if (empty($hash))
		return 'zp_public_access';
	return false;
}

/**
 * Checks to see if a password is needed
 *
 * Returns true if access is allowed
 *
 * The password protection is hereditary. This normally only impacts direct url access to an object since if
 * you are going down the tree you will be stopped at the first place a password is required.
 *
 *
 * @param string $hint the password hint
 * @param bool $show whether there is a user associated with the password.
 * @return bool
 * @since 1.1.3
 */
function checkAccess(&$hint = NULL, &$show = NULL) {
	global $_zp_current_album, $_zp_current_search, $_zp_gallery, $_zp_gallery_page,
	$_zp_current_zenpage_page, $_zp_current_zenpage_news;
	if (GALLERY_SECURITY != 'public') // only registered users allowed
		$show = true; //	therefore they will need to supply their user id is something fails below

	if ($_zp_gallery->isUnprotectedPage(stripSuffix($_zp_gallery_page)))
		return true;
	if (zp_loggedin()) {
		$fail = zp_apply_filter('isMyItemToView', NULL);
		if (!is_null($fail)) { //	filter had something to say about access, honor it
			return $fail;
		}
		switch ($_zp_gallery_page) {
			case 'album.php':
			case 'image.php':
				if ($_zp_current_album->isMyItem(LIST_RIGHTS)) {
					return true;
				}
				break;
			case 'search.php':
				if (zp_loggedin(VIEW_SEARCH_RIGHTS)) {
					return true;
				}
				break;
			default:
				if (zp_loggedin(VIEW_GALLERY_RIGHTS)) {
					return true;
				}
				break;
		}
	}
	if (GALLERY_SECURITY == 'public' && ($access = checkForGuest($hint, $show))) {
		return $access; // public page or a guest is logged in
	}
	return false;
}

/**
 * Returns a redirection link for the password form
 *
 * @return string
 */
function getPageRedirect() {
	global $_zp_login_error, $_zp_password_form_printed, $_zp_current_search, $_zp_gallery_page,
	$_zp_current_album, $_zp_current_image;
	switch ($_zp_gallery_page) {
		case 'index.php':
			$action = '/index.php';
			break;
		case 'album.php':
			$action = '/index.php?userlog=1&album=' . pathurlencode($_zp_current_album->name);
			break;
		case 'image.php':
			$action = '/index.php?userlog=1&album=' . pathurlencode($_zp_current_album->name) . '&image=' . urlencode($_zp_current_image->filename);
			break;
		case 'pages.php':
			$action = '/index.php?userlog=1&p=pages&title=' . urlencode(getPageTitlelink());
			break;
		case 'news.php':
			$action = '/index.php?userlog=1&p=news';
			$title = getNewsTitlelink();
			if (!empty($title))
				$action .= '&title=' . urlencode(getNewsTitlelink());
			break;
		case 'password.php':
			$action = str_replace(SEO_WEBPATH, '', getRequestURI());
			if ($action == '/') {
				$action = '/index.php';
			}
			break;
		default:
			if (in_context(ZP_SEARCH)) {
				$action = '/index.php?userlog=1&p=search' . $_zp_current_search->getSearchParams();
			} else {
				$action = '/index.php?userlog=1&p=' . substr($_zp_gallery_page, 0, -4);
			}
	}
	return SEO_WEBPATH . $action;
}

/**
 * Prints the album password form
 *
 * @param string $hint hint to the password
 * @param bool $showProtected set false to supress the password protected message
 * @param bool $showuser set true to force the user name filed to be present
 * @param string $redirect optional URL to send the user to after successful login
 *
 * @since 1.1.3
 */
function printPasswordForm($_password_hint, $_password_showuser = NULL, $_password_showProtected = true, $_password_redirect = NULL) {
	global $_zp_login_error, $_zp_password_form_printed, $_zp_current_search, $_zp_gallery, $_zp_gallery_page,
	$_zp_current_album, $_zp_current_image, $theme, $_zp_current_zenpage_page, $_zp_authority;
	if ($_zp_password_form_printed)
		return;
	$_zp_password_form_printed = true;

	if (is_null($_password_redirect))
		$_password_redirect = getPageRedirect();
	?>
	<div id="passwordform">
		<?php
		if ($_password_showProtected && !$_zp_login_error) {
			?>
			<p>
				<?php echo gettext("The page you are trying to view is password protected."); ?>
			</p>
			<?php
		}
		if ($loginlink = zp_apply_filter('login_link', NULL)) {
			$logintext = gettext('login');
			?>
			<a href="<?php echo $loginlink; ?>" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
			<?php
		} else {
			$_zp_authority->printLoginForm($_password_redirect, false, $_password_showuser, false, $_password_hint);
		}
		?>
	</div>
	<?php
}

/**
 * prints the zenphoto logo and link
 *
 */
function printZenphotoLink() {
	echo gettext("Powered by <a href=\"http://www.zenphoto.org\" title=\"A simpler web album\"><span id=\"zen-part\">zen</span><span id=\"photo-part\">PHOTO</span></a>");
}

/**
 * Expose some informations in a HTML comment
 *
 * @param string $obj the path to the page being loaded
 * @param array $plugins list of activated plugins
 * @param string $theme The theme being used
 */
function exposeZenPhotoInformations($obj = '', $plugins = '', $theme = '') {
	global $_zp_filters;

	$a = basename($obj);
	if ($a != 'full-image.php') {
		echo "\n<!-- zenphoto version " . ZENPHOTO_VERSION . " [" . ZENPHOTO_FULL_RELEASE . "]";
		echo " THEME: " . $theme . " (" . $a . ")";
		$graphics = zp_graphicsLibInfo();
		$graphics = sanitize(str_replace('<br />', ', ', $graphics['Library_desc']), 3);
		echo " GRAPHICS LIB: " . $graphics . " { memory: " . INI_GET('memory_limit') . " }";
		echo ' PLUGINS: ';
		if (count($plugins) > 0) {
			sort($plugins);
			foreach ($plugins as $plugin) {
				echo $plugin . ' ';
			}
		} else {
			echo 'none ';
		}
		echo " -->";
	}
}

/**
 * Gets the content of a codeblock for an image, album or Zenpage newsarticle or page.
 *
 * The priority for codeblocks will be (based on context)
 * 	1: articles
 * 	2: pages
 * 	3: images
 * 	4: albums
 * 	5: gallery.
 *
 * This means, for instance, if we are in ZP_ZENPAGE_NEWS_ARTICLE context we will use the news article
 * codeblock even if others are available.
 *
 * Note: Echoing this array's content does not execute it. Also no special chars will be escaped.
 * Use printCodeblock() if you need to execute script code.
 *
 * @param int $number The codeblock you want to get
 *
 * @return string
 */
function getCodeblock($number = 1) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery, $_zp_gallery_page;
	if (!$number) {
		setOptionDefault('codeblock_first_tab', 0);
	}
	$object = NULL;
	if ($_zp_gallery_page == 'index.php') {
		$object = $_zp_gallery;
	}
	if (in_context(ZP_ALBUM)) {
		$object = $_zp_current_album;
	}
	if (in_context(ZP_IMAGE)) {
		$object = $_zp_current_image;
	}
	if (in_context(ZP_ZENPAGE_PAGE)) {
		if ($_zp_current_zenpage_page->checkAccess()) {
			$object = $_zp_current_zenpage_page;
		}
	}
	if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		if ($_zp_current_zenpage_news->checkAccess()) {
			$object = $_zp_current_zenpage_news;
		}
	}
	if (empty($object)) {
		return NULL;
	}
	$codeblock = unserialize($object->getcodeblock());
	$codeblock = zp_apply_filter('codeblock', @$codeblock[$number], $object, $number);
	if ($codeblock) {
		$codeblock = applyMacros($codeblock);
	}
	return $codeblock;
}

/**
 * Prints the content of a codeblock for an image, album or Zenpage newsarticle or page.
 *
 * @param int $number The codeblock you want to get
 * @param mixed $what optonal object for which you want the codeblock
 *
 * @return string
 */
function printCodeblock($number = 1, $what = NULL) {
	if (is_object($what)) {
		$codeblock = $what->getCodeblock();
		if ($codeblock) {
			$codeblocks = unserialize($codeblock);
			$codeblock = $codeblocks[$number];
			zp_apply_filter('codeblock', $codeblock, $what, $number);
			if ($codeblock) {
				$codeblock = applyMacros($codeblock);
			}
		} else {
			return;
		}
	} else {
		$codeblock = getCodeblock($number);
	}
	if ($codeblock) {
		$context = get_context();
		eval('?>' . $codeblock);
		set_context($context);
	}
}

/**
 * Checks for URL page out-of-bounds for "standard" themes
 * Note: This function assumes that an "index" page will display albums
 * and the pagination be determined by them. Any other "index" page strategy needs to be
 * handled by the theme itself.
 *
 * @param boolean $request
 * @param string $gallery_page
 * @param int $page
 * @return boolean will be true if all is well, false if a 404 error should occur
 */
function checkPageValidity($request, $gallery_page, $page) {
	global $_zp_gallery, $_firstPageImages, $_oneImagePage, $_zp_zenpage, $_zp_current_category;
	$count = NULL;
	switch ($gallery_page) {
		case 'album.php':
		case 'search.php':
			$albums_per_page = max(1, getOption('albums_per_page'));
			$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
			$imageCount = getNumImages();
			if ($_oneImagePage) {
				if ($_oneImagePage === true) {
					$imageCount = min(1, $imageCount);
				} else {
					$imageCount = 0;
				}
			}
			$images_per_page = max(1, getOption('images_per_page'));
			$count = ($pageCount + (int) ceil(($imageCount - $_firstPageImages) / $images_per_page));
			break;
		case 'index.php':
			if (galleryAlbumsPerPage() != 0) {
				$count = (int) ceil($_zp_gallery->getNumAlbums() / galleryAlbumsPerPage());
			}
			break;
		case 'news.php':
			if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$cat = $_zp_current_category;
			} else {
				$cat = NULL;
			}
			$count = (int) ceil(count($_zp_zenpage->getArticles(0, NULL, true, NULL, NULL, NULL, $cat)) / ZP_ARTICLES_PER_PAGE);
			break;
		default:
			$count = zp_apply_filter('checkPageValidity', NULL, $gallery_page);
			break;
	}
	if ($page > $count) {
		$request = false; //	page is out of range
	}

	return $request;
}

/**
 * Used as the default page url checker. Page url checking is an Opt-in for now at least.
 * It simply returns the parameter
 * @param bool $request
 * @return bool
 */
function checkPageValidityDummy($request) {
	return $request;
}

zp_register_filter('theme_head', 'printZenJavascripts', 9999);
zp_register_filter('theme_body_close', 'adminToolbox');
zp_register_filter('zenphoto_information', 'exposeZenPhotoInformations');
?>
