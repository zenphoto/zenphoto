<?php
/**
 * General template functions
 * 
 * @since 1.7 moved to separate file from template-functions.php
 * 
 * @package zpcore\functions\template
 */

/**
 * Returns the zenphoto version string
 */
function getVersion() {
	return ZENPHOTO_VERSION;
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
	<script src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/jquery.min.js"></script>
	<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery-migrate.min.js"></script>
	<?php
	if (zp_loggedin() || extensionEnabled('tag_suggest')) {
			?>
		<script src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/zp_general.js"></script>
		<?php
	}
	if (zp_loggedin()) {
				?>
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admintoolbox.css" type="text/css" />
		<?php
	}
}

/**
 * Prints the clickable drop down toolbox on any theme page with generic admin helpers
 *
 */
function adminToolbox() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery_page, $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin, $_zp_conf_vars;
	if (zp_loggedin()) {
		$zf = FULLWEBPATH . "/" . ZENFOLDER;
		$page = getCurrentPage();
		ob_start();
		?>
		<script>
			var deleteAlbum1 = "<?php echo gettext("Are you sure you want to delete this item?"); ?>";
			var deleteAlbum2 = "<?php echo gettext("Are you Absolutely positively sure you want to delete this item? THIS CANNOT BE UNDONE!"); ?>";
			function newAlbum(folder, albumtab) {
				var album = prompt('<?php echo gettext('New album name?'); ?>', '<?php echo gettext('new album'); ?>');
				if (album) {
					launchScript('<?php echo $zf; ?>/admin-edit.php', ['action=newalbum', 'folder=' + encodeURIComponent(folder), 'name=' + encodeURIComponent(album), 'albumtab=' + albumtab, 'XSRFToken=<?php echo getXSRFToken('newalbum'); ?>']);
				}
			}
		</script>
		<div id="zp__admin_module">
			<div id="zp__admin_info">
				<span class="zp_logo">ZP</span>
				<span class="zp_user"><?php echo $_zp_current_admin_obj->getLoginName(); ?>
					<?php
					if (array_key_exists('site_upgrade_state', $_zp_conf_vars)) {
						if ($_zp_conf_vars['site_upgrade_state'] == 'closed_for_test') {
							$maintenance_link = maintenanceMode::getUtilityLinkHTML();
							echo ' | <span class="zp_sitestatus">' . gettext('Test mode') . $maintenance_link . '</span>';
						}
					}
					?>
				</span>
			</div>
			<button type="button" id="zp__admin_link" onclick="javascript:toggle('zp__admin_data');">
				<?php echo gettext('Admin'); ?>
			</button>
			<div id="zp__admin_data" style="display: none;">
				<ul>
					<li>
						<ul class="zp__admin_list-global">
							<?php
							$outputA = ob_get_contents();
							ob_end_clean();
							ob_start();

							if (zp_loggedin(OVERVIEW_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin.php', gettext("Overview"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(UPLOAD_RIGHTS | FILES_RIGHTS | THEMES_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-upload.php', gettext("Upload"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(ALBUM_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-edit.php', gettext("Albums"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							filter::applyFilter('admin_toolbox_global', $zf);
							?>
						</ul>
					</li>
					<li>
						<ul class="zp__admin_list-general">
							<?php
							if (zp_loggedin(TAGS_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-tags.php', gettext("Tags"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(USER_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-users.php', gettext("Users"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(OPTIONS_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-options.php?tab=general', gettext("Options"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(THEMES_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-themes.php', gettext("Themes"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							if (zp_loggedin(ADMIN_RIGHTS)) {
								?>
								<li>
									<?php printLinkHTML($zf . '/admin-plugins.php', gettext("Plugins"), NULL, NULL, NULL); ?>
								</li>
								<li>
									<?php printLinkHTML($zf . '/admin-logs.php', gettext("Logs"), NULL, NULL, NULL); ?>
								</li>
								<?php
							}
							?>
						</ul>
					</li>
					<li>
						<ul class="zp__admin_list-edit">
							<?php
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
											<?php printLinkHTML($zf . '/admin-edit.php?page=edit', gettext("Sort Gallery"), NULL, NULL, NULL); ?>
										</li>
										<?php
									}
									if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
										// admin has upload rights, provide an upload link for a new album
										?>
										<li>
											<button type="button" onclick="javascript:newAlbum('', true);"><?php echo gettext("New Album"); ?></button>
										</li>
										<?php
									}
									if ($_zp_gallery_page == 'index.php') {
										$redirect = '';
									} else {
										$redirect = "&;p=" . urlencode(stripSuffix($_zp_gallery_page));
									}
									if ($page > 1) {
										$redirect .= "&;page=$page";
									}
									filter::applyFilter('admin_toolbox_gallery', $zf);
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
											<?php printLinkHTML($zf . '/admin-edit.php?page=edit&album=' . pathurlencode($_zp_current_album->name), gettext('Edit album'), NULL, NULL, NULL); ?>
										</li>
										<?php
										if (!$_zp_current_album->isDynamic()) {
											if ($_zp_current_album->getNumAlbums()) {
												?>
												<li>
													<?php printLinkHTML($zf . '/admin-edit.php?page=edit&album=' . pathurlencode($albumname) . '&tab=subalbuminfo', gettext("Sort subalbums"), NULL, NULL, NULL); ?>
												</li>
												<?php
											}
											if ($_zp_current_album->getNumImages() > 0) {
												?>
												<li>
													<?php printLinkHTML($zf . '/admin-albumsort.php?page=edit&album=' . pathurlencode($albumname) . '&tab=sort', gettext("Sort images"), NULL, NULL, NULL); ?>
												</li>
												<?php
											}
										}
									}
									if ($_zp_current_album->isMyItem(UPLOAD_RIGHTS) && !$_zp_current_album->isDynamic()) {
										// provide an album upload link if the admin has upload rights for this album and it is not a dynamic album
										?>
										<li>
											<?php printLinkHTML($zf . '/admin-upload.php?album=' . pathurlencode($albumname), gettext("Upload Here"), NULL, NULL, NULL); ?>
										</li>
										<li>
											<button type="button" onclick="javascript:newAlbum('<?php echo pathurlencode($albumname); ?>', true);"><?php echo gettext("New Album Here"); ?></button>
										</li>
										<?php
									}
									if ($_zp_current_album->isMyItem(ALBUM_RIGHTS)) {
										// and a delete link
										?>
										<li>
											<button class="admin_data-delete" type="button" onclick="javascript:confirmDeleteAlbum('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($albumname)) ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>');"
															title="<?php echo gettext('Delete the album'); ?>"><?php echo gettext('Delete album'); ?></button>
										</li>
										<?php
									}
									filter::applyFilter('admin_toolbox_album', $albumname, $zf);
									if ($inImage) {
										// script is image.php
										$imagename = $_zp_current_image->filename;
										if (!$_zp_current_album->isDynamic()) { // don't provide links when it is a dynamic album
											if ($_zp_current_album->isMyItem(ALBUM_RIGHTS)) {
												?>
												<li><a href="<?php echo $zf; ?>/admin-edit.php?page=edit&amp;album=<?php echo pathurlencode($albumname); ?>&amp;singleimage=<?php echo urlencode($imagename); ?>&amp;tab=imageinfo&amp;nopagination"
															 title="<?php echo gettext('Edit image'); ?>"><?php echo gettext('Edit image'); ?></a>
												</li>
												<?php
												if ($_zp_current_album->isDynamic()) { // get folder of the corresponding static album
													$albumobj = $_zp_current_image->getAlbum();
													$albumname = $albumobj->name;
												} else {
													$delete_image = gettext("Are you sure you want to delete this image? THIS CANNOT BE UNDONE!");
													// if admin has edit rights on this album, provide a delete link for the image.
													?>
													<li>
														<button class="admin_data-delete" type="button" onclick="javascript:confirmDelete('<?php echo $zf; ?>/admin-edit.php?page=edit&amp;action=deleteimage&amp;album=<?php echo urlencode(pathurlencode($albumname)); ?>&amp;image=<?php echo urlencode($imagename); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete'); ?>', '<?php echo $delete_image; ?>');"
																		title="<?php echo gettext("Delete the image"); ?>"><?php echo gettext("Delete image"); ?></button>
													</li>
													<?php
												}
											}
											// set return to this image page
											filter::applyFilter('admin_toolbox_image', $albumname, $imagename, $zf);
										}
										$redirect = "&album=" . html_pathurlencode($albumname) . "&amp;image=" . urlencode($imagename);
									} else {
										// set the return to this album/page
										$redirect = "&album=" . html_pathurlencode($albumname);
										if ($page > 1) {
											$redirect .= "&page=$page";
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
										filter::applyFilter('admin_toolbox_search', $zf);
									}
									$redirect = '&p=search&s=' . $words;
									break;
								default:
									// arbitrary custom page
									$gal = stripSuffix($_zp_gallery_page);
									$redirect = "&p=" . urlencode($gal);
									if ($page > 1) {
										$redirect .= "&page=$page";
									}
									$redirect = filter::applyFilter('admin_toolbox_' . $gal, $redirect, $zf);
									break;
							}
							$redirect = filter::applyFilter('admin_toolbox_close', $redirect, $zf);
							?>
						</ul>
					</li>
					<li>
						<ul class="zp__admin-list-user">
							<?php
							if ($_zp_current_admin_obj->logout_link) {
								$link = Authority::getLogoutURL('frontend', $redirect);
								?>
								<li>
									<?php printLinkHTML($link, gettext("Logout"), gettext("Logout"), null, null); ?>
								</li>
								<?php
							}
							$outputB = ob_get_contents();
							ob_end_clean();
							if ($outputB) {
								echo $outputA . $outputB;
								?>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<?php
		}
	}
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
function getHeadTitle($separator = ' | ', $listparentalbums = false, $listparentpages = false) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery_page, $_zp_current_category, $_zp_page, $_zp_myfavorites;
	$mainsitetitle = html_encode(getBare(getParentSiteTitle()));
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
			return $gallerytitle . $mainsitetitle . $pagenumber;
		case 'album.php':
		case 'image.php':
			$albumtitle = $parentalbums = '';
			if ($listparentalbums) {
				$parents = getParentAlbums();
				$parentalbums = '';
				if (count($parents) != 0) {
					$parents = array_reverse($parents);
					foreach ($parents as $parent) {
						$parentalbums .= html_encode(getBare($parent->getTitle())) . $separator;
					}
				}
			} 
			//$albumtitle = html_encode(getBareAlbumTitle()) . $pagenumber . $separator . $parentalbums . $gallerytitle . $mainsitetitle;
			switch ($_zp_gallery_page) {
				case 'album.php':
					return html_encode(getBareAlbumTitle()) . $pagenumber . $separator . $parentalbums . $gallerytitle . $mainsitetitle;
				case 'image.php':
					if ($listparentalbums) {
						$albumtitle = html_encode(getBareAlbumTitle()) . $pagenumber . $separator . $parentalbums;
					} 
					return html_encode(getBareImageTitle()) . $separator . $albumtitle . $gallerytitle . $mainsitetitle;
			}
			break;
		case 'news.php':
			if (function_exists("is_NewsArticle")) {
				if (is_NewsArticle()) {
					return html_encode(getBareNewsTitle()) . $pagenumber . $separator . gettext('News') . $separator . $gallerytitle . $mainsitetitle;
				} else if (is_NewsCategory()) {
					return html_encode(getBare($_zp_current_category->getTitle())) . $pagenumber . $separator . gettext('News') . $separator . $gallerytitle . $mainsitetitle;
				} else {
					return gettext('News') . $pagenumber . $separator . $gallerytitle . $mainsitetitle;
				}
			}
			break;
		case 'pages.php':
			$parentpages = '';
			if ($listparentpages) {
				$parents = $_zp_current_zenpage_page->getParents();
				$parentpages = '';
				if (count($parents) != 0) {
					$parents = array_reverse($parents);
					foreach ($parents as $parent) {
						$obj = new ZenpagePage($parent);
						$parentpages .= html_encode(getBare($obj->getTitle())) . $separator;
					}
				}
			} 
			return html_encode(getBarePageTitle()) . $pagenumber . $separator . $parentpages . $gallerytitle . $mainsitetitle;
		case '404.php':
			return gettext('Object not found') . $separator . $gallerytitle . $mainsitetitle;
		default: // for all other possible static custom pages
			$custompage = stripSuffix($_zp_gallery_page);
			$standard = array(
					'gallery' => gettext('Gallery'), 
					'contact' => gettext('Contact'), 
					'register' => gettext('Register'), 
					'search' => gettext('Search'), 
					'archive' => gettext('Archive view'), 
					'password' => gettext('Password required'));
			if (is_object($_zp_myfavorites)) {
				$instance = '';
				if ($_zp_myfavorites->instance) {
					$instance = ' [' . $_zp_myfavorites->instance . ']';
				} 
				$favorites_title = get_language_string(getOption('favorites_title'));
				if (!$favorites_title) {
					$favorites_title =  gettext('My favorites');
				}
				$standard['favorites'] = $favorites_title . $instance;
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
function printHeadTitle($separator = ' | ', $listparentalbums = true, $listparentpages = false) {
	echo '<title>' . getHeadTitle($separator, $listparentalbums, $listparentpages) . '</title>';
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
				$links1 = "<a href=\"" . html_encode(SearchEngine::getSearchURL(SearchEngine::getSearchQuote($atag), '', 'tags', 0, array('albums' => $albumlist))) . "\" title=\"" . html_encode($atag) . "\">";
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
 * @param bool $exclude_unassigned True or false if you wish to exclude tags that are not assigne to any item (default: true)
 * @param bool $checkaccess True or false (default: false) if you wish to exclude tags that are assigned to items (or are not assigned at all) the visitor is not allowed to see
 * Beware that this may cause overhead on large sites. Usage of the static_html_cache is strongely recommended then.
 * @since 1.1
 */
function printAllTagsAs($option, $class = '', $sort = NULL, $counter = FALSE, $links = TRUE, $maxfontsize = 2, $maxcount = 50, $mincount = 1, $limit = NULL, $minfontsize = 0.8, $exclude_unassigned = true, $checkaccess = false) {
	global $_zp_current_search;
	$option = strtolower($option);
	if ($class != "") {
		$class = ' class="' . $class . '"';
	}
	$tagcount = getAllTagsCount($exclude_unassigned, $checkaccess);
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
						$link = SearchEngine::getSearchURL(SearchEngine::getSearchQuote($key), '', 'tags', 0, array('albums' => $albumlist));
						?>
						<li>
							<a href="<?php echo html_encode($link); ?>"<?php echo $size; ?>><?php echo $key . $counter; ?></a>
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
	global $_zp_db;
	$alldates = array();
	$cleandates = array();
	$sql = "SELECT `date` FROM " . $_zp_db->prefix('images');
	$hidealbums = getNotViewableAlbums();
	if ($hidealbums) {
		$sql .= ' WHERE `albumid` NOT IN (' . implode(',', $hidealbums) . ')';
	}
	$hideimages = getNotViewableImages();
	if ($hideimages) {
		if ($hidealbums) {
			$sql .= ' AND ';
		} else {
			$sql .= ' WHERE ';
		}
		$sql .= ' `id` NOT IN (' . implode(',', $hideimages) . ')';
	}
	$result = $_zp_db->query($sql);
	if ($result) {
		while ($row = $_zp_db->fetchAssoc($result)) {
			$alldates[] = $row['date'];
		}
		$_zp_db->freeResult($result);
	}
	foreach ($alldates as $adate) {
		if (!empty($adate)) {
			$cleandates[] = substr($adate, 0, 7);
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
function printAllDates($class = 'archive', $yearid = 'year', $monthid = 'month', $order = 'desc') {
	global $_zp_current_search, $_zp_gallery_page;
	if (empty($class)) {
		$classactive = 'archive_active';
	} else {
		$classactive = $class . '_active';
		$class = 'class="' . $class . '"';
	}
	if ($_zp_gallery_page == 'search.php') {
		$activedate = getSearchDate('Y-m');
	} else {
		$activedate = '';
	}
	if (!empty($yearid)) {
		$yearid = 'class="' . $yearid . '"';
	}
	if (!empty($monthid)) {
		$monthid = 'class="' . $monthid . '"';
	}
	$datecount = getAllDates($order);
	$lastyear = "";
	echo "\n<ul $class>\n";
	$nr = 0;
	foreach($datecount as $key => $val) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			if (extension_loaded('intl') && getOption('date_format_localized')) {
				$year = zpFormattedDate('yyyy', $key, true); 
				$month = zpFormattedDate('MMMM', $key, true);
			} else {
				$year = zpFormattedDate('Y', $key, false); 
				$month = zpFormattedDate('F', $key,  false);
			}
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
		echo '<li' . $cl . '><a href="' . html_encode(SearchEngine::getSearchURL('', $datekey, '', 0, array('albums' => $albumlist))) . '">' . $month . ' (' . $val . ')</a></li>' . "\n";
	}
	echo "</ul>\n</li>\n</ul>\n";
}

/**
 * Produces the url to a custom page (e.g. one that is not album.php, image.php, or index.php)
 *
 * @param string $page page name to include in URL
 * @param string $q query string to add to url
 * @param bool $webpath host path to be prefixed. If "false" is passed you will get a localized "WEBPATH"
 * @return string
 */
function getCustomPageURL($page, $q = '', $webpath = null) {
	global $_zp_conf_vars;
	if (array_key_exists($page, $_zp_conf_vars['special_pages'])) {
		$rewrite = preg_replace('~^_PAGE_/~', _PAGE_ . '/', $_zp_conf_vars['special_pages'][$page]['rewrite']) . '/';
	} else {
		$rewrite = '/' . _PAGE_ . '/' . $page . '/';
	}
	$plain = "index.php?p=$page";
	if (!empty($q)) {
		$rewrite .= "?$q";
		$plain .= "&$q";
	}
	return filter::applyFilter('getLink', rewrite_path($rewrite, $plain, $webpath), $page . '.php', null);
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
 * returns the auth type of a guest login
 *
 * @param string $hint
 * @param string $show
 * @return string
 */
function checkForGuest(&$hint = NULL, &$show = NULL) {
	global $_zp_gallery, $_zp_current_zenpage_page, $_zp_current_category, $_zp_current_zenpage_news;
	$authType = filter::applyFilter('checkForGuest', NULL);
	if (!is_null($authType)) {
		return $authType;
	}
	if (in_context(ZP_SEARCH)) { // search page
		$hash = getOption('search_password');
		$user = getOption('search_user');
		$show = (!empty($user));
		$hint = get_language_string(getOption('search_hint'));
		$authType = 'zpcms_auth_search';
		if (empty($hash)) {
			$hash = $_zp_gallery->getPassword();
			$user = $_zp_gallery->getUser();
			$show = (!empty($user));
			$hint = $_zp_gallery->getPasswordHint();
			$authType = 'zpcms_auth_gallery';
		}
		if (!empty($hash) && zp_getCookie($authType) == $hash) {
			return $authType;
		}
	} else if (!is_null($_zp_current_zenpage_news)) {
		$authType = $_zp_current_zenpage_news->checkAccess($hint, $show);
		return $authType;
	} else if (!is_null($_zp_current_category)) {
		$authType = $_zp_current_category->checkforGuest($hint, $show);
		return $authType;
	} else if (!is_null($_zp_current_zenpage_page)) {
		$authType = $_zp_current_zenpage_page->checkforGuest($hint, $show);
		return $authType;
	} else if (isset($_GET['album'])) { // album page
		list($album, $image) = rewrite_get_album_image('album', 'image');
		if ($authType = checkAlbumPassword($album, $hint)) {
			return $authType;
		} else {
			$alb = AlbumBase::newAlbum($album);
			$user = $alb->getUser();
			$show = (!empty($user));
			return false;
		}
	} else { // other page
		$hash = $_zp_gallery->getPassword();
		$user = $_zp_gallery->getUser();
		$show = (!empty($user));
		$hint = $_zp_gallery->getPasswordHint();
		if (!empty($hash) && zp_getCookie('zpcms_auth_gallery') == $hash) {
			return 'zpcms_auth_gallery';
		}
	}
	if (empty($hash)) {
		return 'zp_public_access';
	}
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
	if (isset($_GET['download']) && extensionEnabled('downloadList')) {
		return false; // Handled by downloadList extension
	}
	if (GALLERY_SECURITY != 'public') {// only registered users allowed
		$show = true; //	therefore they will need to supply their user id is something fails below
	}
	if ($_zp_gallery->isUnprotectedPage(stripSuffix($_zp_gallery_page))) {
		return true;
	}
	if (zp_loggedin()) {
		$fail = filter::applyFilter('isMyItemToView', NULL);
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
  $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news;
	if($_zp_login_error !== 2) {
		return false;
	}
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
      if (!is_null($_zp_current_zenpage_news)) {
        $action .= '&title=' . urlencode($_zp_current_zenpage_news->getName());
      }
      break;
    case 'password.php':
      $action = str_replace(SEO_WEBPATH, '', getRequestURI());
      if ($action == '/' . _PAGE_ . '/password' || $action == '/index.php?p=password') {
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

	if (is_null($_password_showuser))
		$_password_showuser = $_zp_gallery->getUserLogonField();
	?>
	<div id="passwordform">
		<?php
			if(zp_loggedin()) {
				echo '<p><strong>' . gettext('You are successfully logged in.') . '</strong></p>';
			} else {
				if ($_password_showProtected && !$_zp_login_error) {
					?>
					<p>
						<?php echo gettext("The page you are trying to view is password protected."); ?>
					</p>
					<?php
				}
				if ($loginlink = filter::applyFilter('login_link', NULL)) {
					$logintext = gettext('login');
					?>
					<a href="<?php echo $loginlink; ?>" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
					<?php
				} else {
					$_zp_authority->printLoginForm($_password_redirect, false, $_password_showuser, false, $_password_hint);
				}
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
	echo gettext('Powered by <a href="https://www.zenphoto.org" target="_blank" rel="noopener noreferrer" title="The simpler media website CMS">Zenphoto</a>');
}

/**
 * Expose some informations in a HTML comment
 *
 * @param string $obj the path to the page being loaded
 * @param array $plugins list of activated plugins
 * @param string $theme The theme being used
 */
function exposeZenPhotoInformations($obj = '', $plugins = '', $theme = '') {
	global $_zp_graphics;
	$a = basename($obj);
	if ($a != 'full-image.php') {
		echo "\n<!-- zenphoto version " . ZENPHOTO_VERSION;
		if (TEST_RELEASE) {
			echo " THEME: " . $theme . " (" . $a . ")";
			$graphics = $_zp_graphics->graphicsLibInfo();
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
 * @param mixed $what optonal object for which you want the codeblock
 *
 * @return string
 */
function getCodeblock($number = 1, $object = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery, $_zp_gallery_page;
	if (!$number) {
		setOptionDefault('codeblock_first_tab', 0);
	}
	if (!is_object($object)) {
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
	}
	if (!is_object($object)) {
		return NULL;
	}
	$codeblock = getSerializedArray($object->getcodeblock());
	$codeblock = filter::applyFilter('codeblock', @$codeblock[$number], $object, $number);
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
	$codeblock = getCodeblock($number, $what);
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
	global $_zp_gallery, $_zp_first_page_images, $_zp_one_image_page, $_zp_zenpage, $_zp_current_category;
	$count = NULL;
	switch ($gallery_page) {
		case 'album.php':
		case 'search.php':
			$albums_per_page = max(1, getOption('albums_per_page'));
			$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
			$imageCount = getNumImages();
			if ($_zp_one_image_page) {
				if ($_zp_one_image_page === true) {
					$imageCount = min(1, $imageCount);
				} else {
					$imageCount = 0;
				}
			}
			$images_per_page = max(1, getOption('images_per_page'));
			$count = ($pageCount + (int) ceil(($imageCount - $_zp_first_page_images) / $images_per_page));
			break;
		case 'index.php':
			if (galleryAlbumsPerPage() != 0) {
				$count = (int) ceil($_zp_gallery->getNumAlbums() / galleryAlbumsPerPage());
			}
			break;
		case 'news.php':
			if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$count = count($_zp_current_category->getArticles());
			} else {
				$count = count($_zp_zenpage->getArticles());
			}
			$count = (int) ceil($count / ZP_ARTICLES_PER_PAGE);
			break;
		default:
			$count = filter::applyFilter('checkPageValidity', NULL, $gallery_page, $page);
			break;
	}
	if ($page > $count) {
		$request = false; //	page is out of range
	}

	return $request;
}

function print404status($album, $image, $obj) {
	global $_zp_page;
	echo "\n<strong>" . gettext("Zenphoto Error:</strong> the requested object was not found.");
	if (isset($album)) {
		echo '<br />' . sprintf(gettext('Album: %s'), html_encode($album));

		if (isset($image)) {
			echo '<br />' . sprintf(gettext('Image: %s'), html_encode($image));
		}
	} else {
		echo '<br />' . sprintf(gettext('Page: %s'), html_encode(substr(basename($obj), 0, -4)));
	}
	if (isset($_zp_page) && $_zp_page > 1) {
		echo '/' . $_zp_page;
	}
}

/**
 * Gets current item's owner (gallery images and albums) or author (Zenpage articles and pages)
 * 
 * @since 1.5.2
 * 
 * @global obj $_zp_current_album
 * @global obj $_zp_current_image
 * @global obj $_zp_current_zenpage_page
 * @global obj $_zp_current_zenpage_news
 * @param boolean $fullname If the owner/author has a real user account and there is a full name set it is returned
 * @return boolean
 */
function getOwnerAuthor($fullname = false) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	$ownerauthor = false;
	if (in_context(ZP_IMAGE)) {
		$ownerauthor = $_zp_current_image->getOwner($fullname);
	} else if (in_context(ZP_ALBUM)) {
		$ownerauthor = $_zp_current_album->getOwner($fullname);
	} 
	if (extensionEnabled('zenpage')) {
		if (is_Pages()) {
			$ownerauthor = $_zp_current_zenpage_page->getAuthor($fullname);
		} else if (is_NewsArticle()) {
			$ownerauthor = $_zp_current_zenpage_news->getAuthor($fullname);
		} 
	} 
	if ($ownerauthor) {
		return $ownerauthor;
	} 
	return false;
}

/**
 * Prints current item's owner (gallery images and albums) or author (Zenpage articles and pages)
 * 
 * @since 1.5.2
 * 
 * @param type $fullname
 */
function printOwnerAuthor($fullname = false) {
	echo html_encode(getOwnerAuthor($fullname));
}

/**
 * Returns the search url for items the current item's owner (gallery) or author (Zenpage) is assigned to
 * 
 * This eventually may return the url to an actual user profile page in the future.
 * 
 * @since 1.5.2
 * 
 * @return type
 */
function getOwnerAuthorURL() {
	$ownerauthor = getOwnerAuthor(false); 
	if($ownerauthor) {
		if (in_context(ZP_IMAGE) || in_context(ZP_ALBUM)) {
			return getUserURL($ownerauthor, 'gallery');
		} 
		if (extensionEnabled('zenpagae') && (is_Pages() || is_NewsArticle())) {
			return getUserURL($ownerauthor, 'zenpage');
		} 
	}
}

/**
 * Prints the link to the search engine for results of all items the current item's owner (gallery) or author (Zenpage) is assigned to
 * 
 * This eventually may return the url to an actual user profile page in the future.
 * 
 * @since 1.5.2
 * 
 * @param type $fullname
 * @param type $resulttype
 * @param type $class
 * @param type $id
 * @param type $title
 */
function printOwnerAuthorURL($fullname = false, $resulttype = 'all', $class = null, $id = null, $title = null) {
	$author = $linktext = $title = getOwnerAuthor(false);
	if ($author) {
		if ($fullname) {
			$linktext = getOwnerAuthor(true);
		}
		if(is_null($title)) {
			$title = $linktext;
		}
		printUserURL($author, $resulttype, $linktext, $class, $id, $title);
	} 
}

/**
 * Returns a an url for the search engine for results of all items the user with $username is assigned to either as owner (gallery) or author (Zenpage)
 *  Note there is no check if the user name is actually a vaild user account name, owner or author! Use the *OwerAuthor() function for that instead
 * 
 * This eventually may return the url to an actual user profile page in the future.
 * 
 * @since 1.5.2
 * 
 * @param string $username The user name of a user. Note there is no check if the user name is actually valid!
 * @param string $resulttype  'all' for owner and author, 'gallery' for owner of images/albums only, 'zenpage' for author of news articles and pages
 * @return string|null
 */
function getUserURL($username, $resulttype = 'all') {
	if (empty($username)) {
		return null;
	}
	switch ($resulttype) {
		case 'all':
		default:
			$fields = array('owner', 'author');
			break;
		case 'gallery':
			$fields = array('owner');
			break;
		case 'zenpage':
			$fields = array('author');
			break;
	}
	return SearchEngine::getSearchURL(SearchEngine::getSearchQuote($username), '', $fields, 1, null);
}

/**
 * Prints the link to the search engine for results of all items $username is assigned to either as owner (gallery) or author (Zenpage)
 * Note there is no check if the user name is actually a vaild user account name, owner or author! Use the *OwerAuthor() function for that instead
 * 
 * This eventually may point to an actual user profile page in the future.
 * 
 * @since 1.5.2
 * 
 * @param string $username The user name of a user. 
 * @param string $resulttype  'all' for owner and author, 'gallery' for owner of images/albums only, 'zenpage' for author of news articles and pages
 * @param string $linktext The link text. If null the user name will be used
 * @param string $class The CSS class to attach, default null.
 * @param type $id The CSS id to attach, default null.
 * @param type $title The title attribute to attach, default null so the user name is used
 */
function printUserURL($username, $resulttype = 'all', $linktext = null, $class = null, $id = null, $title = null) {
	if ($username) {
		$url = getUserURL($username, $resulttype);
		if (is_null($linktext)) {
			$linktext = $username;
		}
		if (is_null($title)) {
			$title = $username;
		}
		printLinkHTML($url, $linktext, $title, $class, $id);
	}
}