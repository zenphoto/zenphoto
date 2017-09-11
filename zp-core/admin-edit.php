<?php
/**
 * admin-edit.php editing of albums.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

/* Don't put anything before this line! */
define('OFFSET_PATH', 1);

require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tag_suggest.php');

admin_securityChecks(ALBUM_RIGHTS, $return = currentRelativeURL());
updatePublished('albums');
updatePublished('images');

if (isset($_GET['tab'])) {
	$subtab = sanitize($_GET['tab']);
} else {
	$subtab = '';
}
$is_massedit = $subtab == 'massedit';

$subalbum_nesting = 1;
$album_nesting = 1;
define('ADMIN_IMAGES_STEP', 5); //	the step for imges per page
$imagesTab_imageCount = 10;
processEditSelection($subtab);

//check for security incursions
$album = NULL;
$allow = true;
if (isset($_GET['album'])) {
	$folder = sanitize_path($_GET['album']);
	$album = newAlbum($folder, false, true);
	if ($album->exists) {
		$allow = $album->isMyItem(ALBUM_RIGHTS);
		if (!$allow) {
			if (isset($_GET['uploaded'])) { // it was an upload to an album which we cannot edit->return to sender
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-upload.php?uploaded=1');
				exitZP();
			}
		}
	} else {
		$album = NULL;
		unset($_GET['album']);
	}
}

$showDefaultThumbs = getSerializedArray(getOption('album_tab_showDefaultThumbs'));

if (!zp_apply_filter('admin_managed_albums_access', $allow, $return)) {
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $return);
	exitZP();
}

$tagsort = getTagOrder();
$mcr_errors = array();


if (isset($_GET['showthumbs'])) { // switch the display selector
	$how = sanitize($_GET['showthumbs']);
	$key = is_object($showDefaultThumbs) ? $album->name : '*';
	if ($how == 'no') {
		$showDefaultThumbs[$key] = $key;
	} else {
		unset($showDefaultThumbs[$key]);
	}
	setOption('album_tab_showDefaultThumbs', serialize($showDefaultThumbs));
}
if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	switch ($action) {
		/** reorder the tag list ***************************************************** */
		/*		 * *************************************************************************** */
		case 'savealbumorder':
			XSRFdefender('savealbumorder');
			if ($_POST['checkallaction'] == 'noaction') {
				$notify = postAlbumSort(NULL);
				if ($notify) {
					if ($notify === true) {
						$notify = '&saved';
					} else {
						$notify = '&saved' . $notify;
					}
					$_zp_gallery->setSortDirection(0);
					$_zp_gallery->setSortType('manual');
					$_zp_gallery->save();
				} else {
					$notify = '&noaction';
				}
			} else {
				$notify = processAlbumBulkActions();
				if (empty($notify)) {
					$notify = '&noaction';
				} else {
					$notify = '&bulkmessage=' . $notify;
				}
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $notify);
			exitZP();
			break;
		case 'savesubalbumorder':
			XSRFdefender('savealbumorder');
			if ($_POST['checkallaction'] == 'noaction') {
				$notify = postAlbumSort($album->getID());
				if ($notify) {
					if ($notify === true) {
						$notify = '&saved';
					} else {
						$notify = '&saved' . $notify;
					}
					$album = newAlbum($folder);
					$album->setSortType('manual', 'album');
					$album->setSortDirection(false, 'album');
					$album->save();
				} else {
					$notify = '&noaction';
				}
			} else {
				$notify = processAlbumBulkActions();
				if (empty($notify)) {
					$notify = '&noaction';
				} else {
					$notify = '&bulkmessage=' . $notify;
				}
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . $folder . '&tab=subalbuminfo' . $notify);
			exitZP();
			break;
		case 'sorttags':
			if (isset($_GET['subpage'])) {
				$pg = '&subpage=' . sanitize($_GET['subpage']);
				$tab = '&tab=imageinfo';
			} else {
				$pg = '';
				$tab = '';
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . $folder . $pg . '&tagsort=' . html_encode($tagsort) . $tab);
			exitZP();
			break;

		/** clear the cache ********************************************************** */
		/*		 * *************************************************************************** */
		case "clear_cache":
			XSRFdefender('clear_cache');
			if (isset($_GET['album'])) {
				$album = sanitize_path($_GET['album']);
			} else {
				$album = sanitize_path($_POST['album']);
			}
			Gallery::clearCache($album);
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&cleared&album=' . $album);
			exitZP();
			break;
		case 'comments':
			XSRFdefender('albumedit');
			$album = newAlbum($folder);
			$album->setCommentsAllowed(sanitize_numeric($_GET['commentson']));
			$album->save();
			$return = sanitize_path($r = $_GET['return']);
			if (!empty($return)) {
				$return = '&album=' . $return;
				if (strpos($r, '*') === 0) {
					$return .= '&tab=subalbuminfo';
				}
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $return);
			exitZP();
			break;

		/** Publish album  *********************************************************** */
		/*		 * *************************************************************************** */
		case "publish":
			XSRFdefender('albumedit');
			$album = newAlbum($folder);
			$album->setShow($_GET['value']);
			$album->save();
			$return = sanitize_path($r = $_GET['return']);
			if (!empty($return)) {
				$return = '&album=' . $return;
				if (strpos($r, '*') === 0) {
					$return .= '&tab=subalbuminfo';
				}
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $return);
			exitZP();
			break;

		/** Reset hitcounters ********************************************************** */
		/*		 * ***************************************************************************** */
		case "reset_hitcounters":
			XSRFdefender('hitcounter');
			$id = sanitize_numeric($_REQUEST['albumid']);
			$where = ' WHERE `id`=' . $id;
			$imgwhere = ' WHERE `albumid`=' . $id;
			$return = sanitize_path($r = $_GET['return']);
			if (!empty($return)) {
				$return = '&album=' . $return;
				if (strpos($r, '*') === 0) {
					$return .= '&tab=subalbuminfo';
				}
			}
			query("UPDATE " . prefix('albums') . " SET `hitcounter`= 0" . $where);
			query("UPDATE " . prefix('images') . " SET `hitcounter`= 0" . $imgwhere);
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $return . '&counters_reset');
			exitZP();
			break;

//** DELETEIMAGE **************************************************************/
		/*		 * *************************************************************************** */
		case 'deleteimage':
			XSRFdefender('delete');
			$albumname = sanitize_path($_REQUEST['album']);
			$imagename = sanitize_path($_REQUEST['image']);
			$album = newAlbum($albumname);
			$image = newImage($album, $imagename);
			if ($image->remove()) {
				$nd = 1;
			} else {
				$nd = 2;
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . pathurlencode($albumname) . '&ndeleted=' . $nd);
			exitZP();
			break;

		/** REFRESH IMAGE METADATA */
		case 'refresh':
			XSRFdefender('imagemetadata');
			$albumname = sanitize_path($_REQUEST['album']);
			$imagename = sanitize_path($_REQUEST['image']);
			$image = newImage(array('folder' => $albumname, 'filename' => $imagename));
			$image->updateMetaData();
			$image->save();
			if (isset($_GET['album'])) {
				$return = pathurlencode(sanitize_path($_GET['album']));
			} else {
				$return = pathurlencode(sanitize_path(urldecode($_POST['album'])));
			}

			$return = '?page=edit&tab=imageinfo&album=' . $return . '&metadata_refresh';
			if (isset($_REQUEST['singleimage'])) {
				$return .= '&singleimage=' . sanitize($_REQUEST['singleimage']);
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php' . $return);
			exitZP();
			break;

		/**
		 * change sort order
		 */
		case "sortorder":
			XSRFdefender('albumsortorder');
			$oldsort = strtolower($_zp_gallery->getSortType('image'));
			if ($_zp_gallery->getSortDirection('image'))
				$oldsort = $oldsort . '_DESC';
			$newsort = sanitize($_POST['albumimagesort'], 3);
			if ($newsort != $oldsort && in_array(str_replace('_DESC', '', $newsort), $_zp_sortby)) {
				if (strpos($newsort, '_DESC')) {

					echo "<br/>descending";

					$_zp_gallery->setSortType(substr($newsort, 0, -5), 'image');
					$_zp_gallery->setSortDirection('1', 'image');
				} else {
					$_zp_gallery->setSortType($newsort, 'image');
					$_zp_gallery->setSortDirection('0', 'image');
				}
				$_zp_gallery->save();
			}
			$albumname = sanitize_path($_REQUEST['album']);
			if (isset($_POST['subpage'])) {
				$pg = '&subpage=' . sanitize($_POST['subpage']);
			} else {
				$pg = false;
			}
			$filter = sanitize($_REQUEST['filter']);
			if ($filter)
				$filter = '&filter=' . $filter;

			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . $albumname . $pg . '&tagsort=' . $tagsort . '&tab=imageinfo' . $filter);
			exitZP();
			break;

		case "gallery_sortorder":
			XSRFdefender('gallery_sortorder');
			$oldsort = strtolower($_zp_gallery->getSortType('album'));
			if ($_zp_gallery->getSortDirection('albums'))
				$oldsort = $oldsort . '_DESC';
			$newsort = sanitize($_POST['gallery_sortby'], 3);
			if ($newsort != $oldsort && in_array(str_replace('_DESC', '', $newsort), $_zp_sortby)) {
				if (strpos($newsort, '_DESC')) {
					$_zp_gallery->setSortType(substr($newsort, 0, -5), 'album');
					$_zp_gallery->setSortDirection('1', 'album');
				} else {
					$_zp_gallery->setSortType($newsort, 'album');
					$_zp_gallery->setSortDirection('0', 'album');
				}
				$_zp_gallery->save();
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit');
			exitZP();
			break;

		case "subalbum_sortorder":
			XSRFdefender('subalbum_sortorder');
			$oldsort = strtolower($album->getSortType('album'));
			if ($album->getSortDirection('albums'))
				$oldsort = $oldsort . '_DESC';
			$newsort = sanitize($_POST['subalbum_sortby'], 3);
			if ($newsort != $oldsort && in_array(str_replace('_DESC', '', $newsort), $_zp_sortby)) {
				if (strpos($newsort, '_DESC')) {
					$album->setSortType(substr($newsort, 0, -5), 'albums');
					$album->setSortDirection('1', 'albums');
				} else {
					$album->setSortType($newsort, 'albums');
					$album->setSortDirection('0', 'albums');
				}
				$album->save();
			}
			$albumname = sanitize_path($_REQUEST['album']);
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . $albumname . '&tab=subalbuminfo');
			exitZP();
			break;

		/** SAVE ********************************************************************* */
		/*		 * *************************************************************************** */
		case "save":
			unset($folder);
			$returntab = '';
			XSRFdefender('albumedit');
			/** SAVE A SINGLE ALBUM ****************************************************** */
			if (isset($_POST['album'])) {
				$folder = sanitize_path($_POST['album']);
				$album = newAlbum($folder, false, true);
				$notify = '';
				$returnalbum = NULL;
				if (isset($_POST['savealbuminfo']) && $album->exists) {
					$notify = processAlbumEdit(0, $album, $returnalbum);
					$returntab = '&tagsort=' . $tagsort . '&tab=albuminfo';
				}
				if (isset($_POST['totalimages']) && $album->exists) {
					if (isset($_POST['checkForPostTruncation'])) {
						$filter = sanitize($_REQUEST['filter']);
						$returntab = '&tagsort=' . $tagsort . '&tab=imageinfo&filter=' . $filter;
						if (isset($_POST['ids'])) { //	process bulk actions, not individual image actions.
							$action = processImageBulkActions($album);
							if (!empty($action))
								$notify = '&bulkmessage=' . $action;
						} else {
							if (isset($_POST['singleimage'])) {
								$single = sanitize($_POST['singleimage']);
							}
							for ($i = 0; $i <= $_POST['totalimages']; $i++) {
								if (isset($_POST["$i-filename"])) {
									$filename = sanitize($_POST["$i-filename"]);
									$image = newImage($album, $filename, true);
									if ($image->exists) { // The file might no longer exist
										if (isset($_POST[$i . '-MoveCopyRename'])) {
											$movecopyrename_action = sanitize($_POST[$i . '-MoveCopyRename'], 3);
										} else {
											$movecopyrename_action = '';
										}
										if ($movecopyrename_action == 'delete') {
											unset($single);
											$image->remove();
										} else {
											if (isset($_POST[$i . '-reset_rating'])) {
												$image->set('total_value', 0);
												$image->set('total_votes', 0);
												$image->set('used_ips', 0);
											}
											$pubdate = $image->setPublishDate(sanitize($_POST['publishdate-' . $i]));
											$image->setExpireDate(sanitize($_POST['expirationdate-' . $i]));
											$image->setTitle(process_language_string_save("$i-title", 2));
											$image->setDesc(process_language_string_save("$i-desc", EDITOR_SANITIZE_LEVEL));
											if (isset($_POST[$i . '-oldrotation']) && isset($_POST[$i . '-rotation'])) {
												$oldrotation = (int) $_POST[$i . '-oldrotation'];
												$rotation = (int) $_POST[$i . '-rotation'];
												if ($rotation != $oldrotation) {
													$image->set('rotation', $rotation);
													$image->updateDimensions();
													$album = $image->getAlbum();
													Gallery::clearCache($album->name);
												}
											}
											$image->setCommentsAllowed(isset($_POST["$i-allowcomments"]));
											if (isset($_POST["reset_hitcounter$i"])) {
												$image->set('hitcounter', 0);
											}
											$image->set('filesize', filesize($image->localpath));
											$image->setShow(isset($_POST["$i-Visible"]));
											zp_apply_filter('save_image_custom_data', NULL, $i, $image);
											zp_apply_filter('save_image_utilities_data', $image, $i);

											$image->save();

											// Process move/copy/rename
											if ($movecopyrename_action == 'move') {
												unset($single);
												$dest = sanitize_path($_POST[$i . '-albumselect']);
												if ($dest && $dest != $folder) {
													if ($e = $image->move($dest)) {
														$notify = "&mcrerr=" . $e;
													}
												} else {
													// Cannot move image to same album.
													$notify = "&mcrerr=2";
												}
											} else if ($movecopyrename_action == 'copy') {
												$dest = sanitize_path($_POST[$i . '-albumselect']);
												if ($dest && $dest != $folder) {
													if ($e = $image->copy($dest)) {
														$notify = "&mcrerr=" . $e;
													}
												} else {
													// Cannot copy image to existing album.
													// Or, copy with rename?
													$notify = "&mcrerr=2";
												}
											} else if ($movecopyrename_action == 'rename') {
												$renameto = sanitize_path($_POST[$i . '-renameto']);
												if ($e = $image->rename($renameto)) {
													$notify = "&mcrerr=" . $e;
												} else {
													$single = $renameto;
												}
											}
										}
									}
								}
							}
						}
					} else {
						$notify = '&post_error';
					}
				}
				if (!is_null($returnalbum)) {
					$folder = $returnalbum;
				}
				$qs_albumsuffix = '';
				if (isset($single))
					$qs_albumsuffix .= '&singleimage=' . $single;
				/** SAVE MULTIPLE ALBUMS ***************************************************** */
			} else if ($_POST['totalalbums']) {
				$notify = '';
				for ($i = 1; $i <= sanitize_numeric($_POST['totalalbums']); $i++) {
					if ($i > 0) {
						$prefix = $i . "-";
					} else {
						$prefix = '';
					}
					$f = sanitize_path(trim(sanitize($_POST[$prefix . 'folder'])));
					$album = newAlbum($f);
					$returnalbum = '';
					$rslt = processAlbumEdit($i, $album, $returnalbum);
					if (!empty($rslt)) {
						$notify = $rslt;
					}
				}
				$qs_albumsuffix = '&tab=massedit';
				if (isset($_GET['album'])) {
					$qs_albumsuffix = '&album=' . sanitize($_GET['album']) . $qs_albumsuffix;
				}
			}

			// Redirect to the same album we saved.
			if (isset($folder) && !empty($folder)) {
				$qs_albumsuffix .= '&album=' . pathurlencode($folder);
			}
			if (isset($_POST['subpage'])) {
				$pg = '&subpage=' . ($subpage = sanitize($_POST['subpage']));
			} else {
				$subpage = $pg = false;
			}
			$msg = zp_apply_filter('edit_error', '');
			if ($msg) {
				$notify .= '&edit_error=' . $msg;
			}
			if ($notify == '&') {
				$notify = '';
			} else {
				if (empty($notify))
					$notify = '&saved';
			}
			if ($notify == '&saved' && $subpage && $subpage == 'object') {
				if (isset($image)) {
					$link = $image->getLink();
				} else {
					$link = $album->getLink();
				}
				header('Location: ' . $link);
				exitZP();
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $qs_albumsuffix . $notify . $pg . $returntab);
			exitZP();
			break;

		/** DELETION ***************************************************************** */
		/*		 * ************************************************************************** */
		case "deletealbum":
			XSRFdefender('delete');
			if ($folder) {
				$album = newAlbum($folder);
				if ($album->remove()) {
					$nd = 3;
				} else {
					$nd = 4;
				}
				if (isset($_GET['return'])) {
					$albumdir = sanitize($_GET['return'], 3);
				} else {
					$albumdir = dirname($folder);
				}
				if ($albumdir != '/' && $albumdir != '.') {
					$albumdir = "&album=" . pathurlencode($albumdir);
				} else {
					$albumdir = '';
				}
			} else {
				$albumdir = '';
			}

			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit" . $albumdir . "&ndeleted=" . $nd);
			exitZP();
			break;
		case 'newalbum':
			XSRFdefender('newalbum');
			$name = sanitize($_GET['name']);
			$folder = sanitize_path($_GET['folder']);
			$seoname = seoFriendly($name);
			if (empty($folder) || $folder == '/' || $folder == '.') {
				$albumdir = '';
				$folder = $seoname;
			} else {
				$albumdir = "&album=" . pathurlencode($folder);
				$folder = $folder . '/' . $seoname;
			}
			$uploaddir = $_zp_gallery->albumdir . internalToFilesystem($folder);
			if (is_dir($uploaddir)) {
				if ($name != $seoname)
					$name .= ' (' . $seoname . ')';
				if (isset($_GET['albumtab'])) {
					if (empty($albumdir)) {
						$tab = '';
					} else {
						$tab = '&tab=subalbuminfo';
					}
				} else {
					$tab = '&tab=albuminfo';
				}
				header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit$albumdir&exists=" . urlencode($name) . $tab);
				exitZP();
			} else {
				mkdir_recursive($uploaddir, FOLDER_MOD);
			}
			@chmod($uploaddir, FOLDER_MOD);

			$album = newAlbum($folder);
			if ($album->exists) {
				$album->setTitle($name);
				$album->save();
				header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit" . "&album=" . pathurlencode($folder));
				exitZP();
			} else {
				$AlbumDirName = str_replace(SERVERPATH, '', $_zp_gallery->albumdir);
				$errorbox[] = gettext("The album couldn’t be created in the “albums” folder. This is usually a permissions problem. Try setting the permissions on the albums and cache folders to be world-writable using a shell:") . " <code>chmod 777 " . $AlbumDirName . '/' . CACHEFOLDER . '/' . "</code>, "
								. gettext("or use your FTP program to give everyone write permissions to those folders.");
			}
			break;
	} // end of switch
}



/* NO Admin-only content between this and the next check. */

/* * ********************************************************************************* */
/** End Action Handling ************************************************************ */
/* * ********************************************************************************* */

// Print our header
if (isset($_GET['album'])) {
	$folder = sanitize_path($_GET['album']);
	if ($folder == '/' || $folder == '.') {
		$parent = '';
	} else {
		$parent = '&amp;album=' . $folder . '&amp;tab=subalbuminfo';
	}
	$album = newAlbum($folder);
	$subtab = setAlbumSubtabs($album);
} else {
	$zenphoto_tabs['edit']['subtabs'][gettext('Mass-edit albums')] = "/" . ZENFOLDER . '/admin-edit.php?tab=massedit';
}
if (empty($subtab)) {
	if (isset($_GET['album'])) {
		$subtab = 'albuminfo';
	}
}

printAdminHeader('edit', $subtab);
datepickerJS();
codeblocktabsJS();

if ((!$is_massedit && !isset($_GET['album'])) || $subtab == 'subalbuminfo') {
	printSortableHead();
}
if (isset($_GET['album']) && (empty($subtab) || $subtab == 'albuminfo') || $is_massedit) {
	$result = db_list_fields('albums');
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'" . $row['Field'] . "'";
		}
	}
	sort($dbfields);
	$albumdbfields = implode(',', $dbfields);
	$result = db_list_fields('images');
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'" . $row['Field'] . "'";
		}
	}
	sort($dbfields);
	$imagedbfields = implode(',', $dbfields);
	?>
	<script type="text/javascript">
		//<!-- <![CDATA[
		var albumdbfields = [<?php echo $albumdbfields; ?>];
		$(function () {
			$('.customalbumsort').tagSuggest({
				tags: albumdbfields
			});
		});
		var imagedbfields = [<?php echo $imagedbfields; ?>];
		$(function () {
			$('.customimagesort').tagSuggest({
				tags: imagedbfields
			});
		});
		// ]]> -->
	</script>
	<?php
}
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteAlbum1 = "<?php echo gettext("Are you sure you want to delete this entire album?"); ?>";
	var deleteAlbum2 = "<?php echo gettext("Are you Absolutely Positively sure you want to delete the album? THIS CANNOT BE UNDONE!"); ?>";
	function newAlbumJS(folder, dynamic) {
		var album = prompt('<?php echo addslashes(gettext('New album name?')); ?>', '<?php echo gettext('album'); ?>.' + $.now());
		if (album) {
			if (dynamic) {
				launchScript('<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-dynamic-album.php', ['action=newalbum', 'folder=' + folder, 'name=' + encodeURIComponent(album)]);
			} else {
				launchScript('', ['action=newalbum', 'folder=' + folder, 'name=' + encodeURIComponent(album), 'XSRFToken=<?php echo getXSRFToken('newalbum'); ?>']);
			}
		}
	}

	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else if ($('#checkallaction').val() == 'deleteallalbum') {
			if (confirm(deleteAlbum1)) {
				return confirm(deleteAlbum2);
			} else {
				return false;
			}
		} else {
			return true;
		}

	}

	var extraWidth;
	function resizeTable() {
		$('.width100percent').width($('.formlayout').width() - extraWidth);
	}

	window.addEventListener('load', function () {
		extraWidth = $('.rightcolumn').width() + 30;
<?php
if ($subtab == 'imageinfo') {
	?>
			extraWidth = extraWidth + $('.bulk_checkbox').width() + $('.leftdeatil').width() + 10;
	<?php
}
?>
		resizeTable();
	}, false);
// ]]> -->
</script>

<?php
zp_apply_filter('texteditor_config', 'zenphoto');
Zenphoto_Authority::printPasswordFormJS();

echo "\n</head>";
?>

<body onresize="resizeTable()">

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			$key = is_object($album) ? $album->name : '*';
			$showthumb = !in_array($key, $showDefaultThumbs);
			if ($showthumb) {
				$thumbshow = 'no';
				$thumbmsg = gettext('Show thumbnail stand-in');
			} else {
				$thumbshow = 'yes';
				$thumbmsg = gettext('Show album thumb');
			}
			$checkarray_images = array(
					gettext('*Bulk actions*') => 'noaction',
					gettext('Delete') => 'deleteall',
					gettext('Set to published') => 'showall',
					gettext('Set to unpublished') => 'hideall',
					gettext('Disable comments') => 'commentsoff',
					gettext('Enable comments') => 'commentson'
			);
			if (extensionEnabled('hitcounter')) {
				$checkarray_images[gettext('Reset hitcounter')] = 'resethitcounter';
			}
			$checkarray_albums = array_merge($checkarray_images, array(
					gettext('Delete') => 'deleteallalbum'
							)
			);
			$checkarray_images = array_merge($checkarray_images, array(
					gettext('Delete') => 'deleteall',
					gettext('Move') => array('name' => 'moveimages', 'action' => 'mass_movecopy_data'),
					gettext('Copy') => array('name' => 'copyimages', 'action' => 'mass_movecopy_data')
							)
			);
			$checkarray_images = zp_apply_filter('bulk_image_actions', $checkarray_images);
			$checkarray_albums = zp_apply_filter('bulk_album_actions', $checkarray_albums);

			/** EDIT ***************************************************************************
			 *
			 *  ********************************************************************************
			 */
			if (isset($_GET['album']) && !$is_massedit) {
				/** SINGLE ALBUM ******************************************************************* */
				// one time generation of this list.
				$mcr_albumlist = array();
				genAlbumList($mcr_albumlist);

				$oldalbumimagesort = $_zp_gallery->getSortType('image');
				$direction = $_zp_gallery->getSortDirection('image');

				if ($album->isDynamic()) {
					$subalbums = array();
					$allimages = array();
				} else {
					$subalbums = getNestedAlbumList($album, $subalbum_nesting);
					if (!($album->subRights() & MANAGED_OBJECT_RIGHTS_EDIT)) {
						$allimages = array();
						$requestor = $_zp_current_admin_obj->getUser();
						$albumowner = $album->getOwner();
						if ($albumowner == $requestor) {
							$retunNull = '`owner` IS NULL OR ';
						} else {
							$retunNull = '';
						}
						$sql = 'SELECT * FROM ' . prefix('images') . ' WHERE (`albumid`=' . $album->getID() . ') AND (' . $retunNull . ' `owner`="' . $requestor . '") ORDER BY `' . $oldalbumimagesort . '`';
						if ($direction)
							$sql .= ' DESC';

						$result = query($sql);
						if ($result) {
							while ($row = db_fetch_assoc($result)) {
								$allimages[] = $row['filename'];
							}
							db_free_result($result);
						}
					} else {
						$allimages = $album->getImages(0, 0, $oldalbumimagesort, $direction ? 'desc' : 'asc');
					}
				}

				if (isset($_GET['filter'])) {
					$filter = sanitize($_GET['filter']);
				} else {
					$filter = '';
				}
				switch ($filter) {
					case'unpublished':
						$sql = 'SELECT `filename` FROM ' . prefix('images') . ' WHERE (`albumid`=' . $album->getID() . ') AND `show`="0"';
						$select = query_full_array($sql);
						break;
					case'published':
						$sql = 'SELECT `filename` FROM ' . prefix('images') . ' WHERE (`albumid`=' . $album->getID() . ') AND `show`="1"';
						$select = query_full_array($sql);
						break;
					default:
						$select = false;
				}
				if (!empty($select)) {
					$include = array();
					foreach ($select as $img) {
						$include[] = $img['filename'];
					}
					$allimages = array_intersect($allimages, $include);
				}

				$allimagecount = count($allimages);
				if (isset($_GET['tab']) && $_GET['tab'] == 'imageinfo' && isset($_GET['image'])) { // directed to an image
					$target_image = urldecode(sanitize($_GET['image']));
					$imageno = array_search($target_image, $allimages);
					if ($imageno !== false) {
						$pagenum = ceil(($imageno + 1) / $imagesTab_imageCount);
					}
				} else {
					$target_image = '';
				}
				if (!isset($pagenum)) {
					if (isset($_GET['subpage'])) {
						if (is_numeric($_GET['subpage'])) {
							$pagenum = max(intval($_GET['subpage']), 1);
							if (($pagenum - 1) * $imagesTab_imageCount >= $allimagecount)
								$pagenum--;
						} else {
							$pagenum = sanitize($_GET['subpage']);
						}
					} else {
						$pagenum = 1;
					}
				}
				$images = array_slice($allimages, ($pagenum - 1) * $imagesTab_imageCount, $imagesTab_imageCount);

				$totalimages = count($images);

				$parent = dirname($album->name);
				if (($parent == '/') || ($parent == '.') || empty($parent)) {
					$parent = '';
				} else {
					$parent = "&amp;album=" . pathurlencode($parent);
				}
				if (isset($_GET['metadata_refresh'])) {
					echo '<div class="messagebox fade-message">';
					echo "<h2>" . gettext("Image metadata refreshed.") . "</h2>";
					echo '</div>';
				}

				if ($album->getParent()) {
					$link = getAlbumBreadcrumbAdmin($album);
				} else {
					$link = '';
				}
				$alb = removeParentAlbumNames($album);
				zp_apply_filter('admin_note', 'albums', $subtab);
				?>
				<h1><?php printf(gettext('Edit Album: <em>%1$s%2$s</em>'), $link, $alb); ?></h1>
				<?php
				$subtab = getCurrentTab();
				if ($subtab == 'albuminfo') {
					?>
					<!-- Album info box -->
					<div id="tab_albuminfo" class="tabbox">
						<?php consolidatedEditMessages('albuminfo'); ?>
						<form class="dirtylistening" onReset="toggle_passwords('', false);setClean('form_albumedit');$('.resetHide').hide();" name="albumedit1" id="form_albumedit" autocomplete="off" action="?page=edit&amp;action=save<?php echo "&amp;album=" . pathurlencode($album->name); ?>"	method="post" >
							<?php XSRFToken('albumedit'); ?>
							<input type="hidden" name="album"	value="<?php echo $album->name; ?>" />
							<input type="hidden"	name="savealbuminfo" value="1" />
							<?php printAlbumEditForm(0, $album); ?>
						</form>
						<br class="clearall">

					</div>
					<?php
				} else if ($subtab == 'subalbuminfo' && !$album->isDynamic()) {
					?>
					<!-- Subalbum list goes here -->
					<?php
					if (count($subalbums) > 0) {
						$enableEdit = $album->subRights() & MANAGED_OBJECT_RIGHTS_EDIT;
						?>
						<div id="tab_subalbuminfo" class="tabbox">
							<?php
							consolidatedEditMessages('subalbuminfo');
							echo gettext('Drag the albums into the order you wish them displayed.');

							printEditDropdown('subalbuminfo', array('1', '2', '3', '4', '5'), $subalbum_nesting);
							$sort = $_zp_sortby;
							foreach ($sort as $name => $action) {
								$sort[$name . ' (' . gettext('descending') . ')'] = $action . '_DESC';
							}
							?>
							<br clear="all"><br />
							<?php
							if (is_null($album->getParent())) {
								$globalsort = gettext("*gallery album sort order");
							} else {
								$globalsort = gettext("*parent album subalbum sort order");
							}
							$type = strtolower($album->get('subalbum_sort_type'));
							if ($type && !in_array($type, $sort)) {
								if ($type == 'manual') {
									$sort[gettext('Manual')] = $type;
								} else {
									$sort[gettext('Custom')] = $type = 'custom';
								}
							}
							if ($album->getSortDirection('albums')) {
								$type .= '_DESC';
							}
							$cv = array($type);
							if (($type == 'manual') || ($type == 'random') || ($type == '')) {
								$dsp = 'none';
							} else {
								$dsp = 'inline';
							}
							?>
							<form name="subalbum_sort" style="float: right;" method="post" action="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php?page=edit&album=<?php echo pathurlencode($album->name); ?>&tab=subalbuminfo&action=subalbum_sortorder" >
								<?php XSRFToken('subalbum_sortorder'); ?>
								<span class="nowrap">
									<?php echo gettext('Sort subalbums by:'); ?>
									<select id="albumsortselect" name="subalbum_sortby" onchange="this.form.submit();">
										<option value =''><?php echo $globalsort; ?></option>
										<?php generateListFromArray($cv, $sort, false, true); ?>
									</select>
								</span>
							</form>
							<br clear="all">
							<form class="dirtylistening" onReset="setClean('sortableListForm');$('#albumsort').sortable('cancel');" action="?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>&amp;action=savesubalbumorder&amp;tab=subalbuminfo" method="post" name="sortableListForm" id="sortableListForm" onsubmit="return confirmAction();" autocomplete="off" >
								<?php XSRFToken('savealbumorder'); ?>
								<p class="notebox">
									<?php echo gettext('<strong>Note:</strong> Dragging an album under a different parent will move the album. You cannot move albums under a <em>dynamic</em> album.'); ?>
								</p>
								<?php
								if ($enableEdit) {
									?>
									<p>
										<?php printf(gettext('Select an album to edit its description and data.'), pathurlencode($album->name)); ?>
									</p>
									<?php
								}
								?>
								<span class="buttons">
									<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
										<?php echo BACK_ARROW_BLUE; ?>
										<strong><?php echo gettext("Back"); ?></strong>
									</a>
									<?php
									if ($enableEdit) {
										?>
										<button class="serialize buttons" type="submit">
											<?php echo CHECKMARK_GREEN; ?>
											<strong><?php echo gettext("Apply"); ?></strong>
										</button>
										<button type="reset" value="<?php echo gettext('Reset') ?>">
											<?php echo CROSS_MARK_RED; ?>
											<strong><?php echo gettext("Reset"); ?></strong>
										</button>
										<div class="floatright">
											<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', false);">
												<img src="images/folder.png" alt="" />
												<strong><?php echo gettext('New subalbum'); ?></strong>
											</button>
											<?php if (!$album->isDynamic()) { ?>
												<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', true);">
													<img src="images/folder.png" alt="" />
													<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
												</button>
											<?php } ?>
										</div>
										<?php
									}
									?>
								</span>
								<br class="clearall"><br />

								<div class="headline" style="text-align: left;"><?php echo gettext("Edit this album"); ?>
									<?php
									if ($enableEdit) {
										printBulkActions($checkarray_albums);
									}
									?>
								</div>
								<div class="subhead">
									<label class="buttons" style="float: left;padding-top:3px;">
										<a href="admin-edit.php?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;tab=subalbuminfo&amp;showthumbs=<?php echo $thumbshow ?>" title="<?php echo addslashes(gettext('Thumbnail generation may be time consuming on slow servers or when there are a lot of images.')); ?>">
											<?php echo $thumbmsg; ?>
										</a>
									</label>
									<?php
									if ($enableEdit) {
										?>
										<label style="float: right; padding-right:20px;">
											<?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
										</label>
										<?php
									}
									?>
								</div>
								<div class="bordered">
									<ul class="page-list" id="albumsort">
										<?php
										printNestedAlbumsList($subalbums, $showthumb, $album);
										?>
									</ul>

								</div>
								<?php printAlbumLegend(); ?>
								<span id="serializeOutput"></span>
								<input name="update" type="hidden" value="Save Order" />
								<br />
								<span class="buttons">
									<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>&filter=<?php echo $filter; ?>">
										<?php echo BACK_ARROW_BLUE; ?>
										<strong><?php echo gettext("Back"); ?></strong>
									</a>
									<button class="serialize buttons" type="submit">
										<?php echo CHECKMARK_GREEN; ?>
										<strong><?php echo gettext("Apply"); ?></strong>
									</button>
									<button type="reset" value="<?php echo gettext('Reset') ?>">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Reset"); ?></strong>
									</button>
									<div class="floatright">
										<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', false);">
											<img src="images/folder.png" alt="" />
											<strong><?php echo gettext('New subalbum'); ?></strong>
										</button>
										<?php if (!$album->isDynamic()) { ?>
											<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', false);">
												<img src="images/folder.png" alt="" />
												<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
											</button>
										<?php } ?>
									</div>
								</span>
							</form>
							<br class="clearall">
						</div><!-- subalbum -->
						<?php
					}
					?>
					<?php
				} else if ($subtab == 'imageinfo') {
					require_once(SERVERPATH . '/' . ZENFOLDER . '/exif/exifTranslations.php');
					$singleimagelink = $singleimage = NULL;
					$showfilter = true;
					if (isset($_GET['singleimage']) && $_GET['singleimage'] || $totalimages == 1) {
						$showfilter = !isset($_GET['singleimage']);
						if ($totalimages == 1) {
							$_GET['singleimage'] = array_shift($images);
						}
						$singleimage = sanitize($_GET['singleimage']);
						$allimagecount = 1;
						$totalimages = 1;
						$images = array($singleimage);
						$singleimagelink = '&singleimage=' . html_encode($singleimage);
					}
					?>
					<!-- Images List -->

					<div id="tab_imageinfo" class="tabbox">
						<?php
						global $albumHeritage;
						$albumHeritage = array();
						$t = explode('/', $album->name);
						While (!empty($t)) {
							$name = implode('/', $t);
							array_pop($t);
							$albumHeritage[' ' . str_repeat('» ', count($t)) . basename($name)] = $name;
						}
						consolidatedEditMessages('imageinfo');
						?>
						<div style="padding-bottom:10px;">
							<?php
							echo gettext("Click on the image to change the thumbnail cropping.");
							if ($showfilter) {
								$numsteps = ceil(max($allimagecount, $imagesTab_imageCount) / ADMIN_IMAGES_STEP);
								if ($numsteps) {
									?>
									<?php
									$steps = array();
									for ($i = 1; $i <= $numsteps; $i++) {
										$steps[] = $i * ADMIN_IMAGES_STEP;
									}
									printEditDropdown('imageinfo', $steps, $imagesTab_imageCount, '&amp;filter=' . $filter);
									?>
									<br style="clear:both"/><br />
									<?php
								}
								?>
								<form  name="albumedit3" style="float: right;"	id="form_sortselect" action="?action=sortorder"	method="post" >
									<?php XSRFToken('albumsortorder'); ?>
									<input type="hidden" name="album"	value="<?php echo $album->name; ?>" />
									<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum); ?>" />
									<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
									<input type="hidden" name="filter" value="<?php echo html_encode($filter); ?>" />

									<?php echo gettext('Image filter'); ?>
									<select id="filter" name="filter" onchange="launchScript('<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php', ['page=edit', 'album=<?php echo html_encode($album->name); ?>', 'subpage=1', 'tab=imageinfo', 'filter=' + $('#filter').val()]);">
										<option value=""<?php if (empty($filter)) echo ' selected="selected"'; ?>><?php echo gettext('all'); ?></option>
										<option value="unpublished"<?php if ($filter == 'unpublished') echo ' selected="selected"'; ?>><?php echo gettext('unpublished'); ?></option>
										<option value="published"<?php if ($filter == 'published') echo ' selected="selected"'; ?>><?php echo gettext('published'); ?></option>
									</select>
									<?php
									$sort = $_zp_sortby;
									foreach ($sort as $key => $value) {
										$sort[sprintf(gettext('%s (descending)'), $key)] = $value . '_DESC';
									}
									$sort[gettext('Manual')] = 'manual';
									if ($direction)
										$oldalbumimagesort = $oldalbumimagesort . '_DESC';
									echo gettext("Display images by:");
									echo '<select id="albumimagesort" name="albumimagesort" onchange="this.form.submit();">';
									generateListFromArray(array($oldalbumimagesort), $sort, false, true);
									echo '</select>';
									?>
								</form>

								<?php
							} else {
								if (isset($_GET['subpage'])) {
									$parent .= '&album=' . html_encode(pathurlencode($album->name)) . '&tab=imageinfo&subpage=' . html_encode(sanitize($_GET['subpage']));
								}
							}
							?>
						</div>
						<br style='clear:both'/>
						<?php
						if ($allimagecount) {
							?>
							<form class="dirtylistening" onReset="setClean('form_imageedit');$('.resetHide').hide();" name="albumedit2"	id="form_imageedit" action="?page=edit&amp;action=save<?php echo "&amp;album=" . html_encode(pathurlencode($album->name)); ?>"	method="post" autocomplete="off" >
								<?php XSRFToken('albumedit'); ?>
								<input type="hidden" name="album"	value="<?php echo $album->name; ?>" />
								<input type="hidden" name="totalimages" value="<?php echo $totalimages; ?>" />
								<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum); ?>" />
								<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
								<input type="hidden" name="filter" value="<?php echo html_encode($filter); ?>" />
								<?php
								if ($singleimage) {
									?>
									<input type="hidden" name="singleimage" value="<?php echo html_encode($singleimage); ?>" />
									<?php
								}
								?>

								<?php $totalpages = ceil(($allimagecount / $imagesTab_imageCount)); ?>

								<div style="padding: 10px;">
									<p class="buttons">
										<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>&filter=<?php echo $filter; ?>">
											<?php echo BACK_ARROW_BLUE; ?>
											<strong><?php echo gettext("Back"); ?></strong>
										</a>
										<button type="submit">
											<?php echo CHECKMARK_GREEN; ?>
											<strong><?php echo gettext("Apply"); ?></strong>
										</button>
										<button type="reset">
											<?php echo CROSS_MARK_RED; ?>
											<strong><?php echo gettext("Reset"); ?></strong>
										</button>
									</p>
									<?php if (!$singleimage) printBulkActions($checkarray_images, true); ?>

									<?php
									$bglevels = array('#fff', '#f8f8f8', '#efefef', '#e8e8e8', '#dfdfdf', '#d8d8d8', '#cfcfcf', '#c8c8c8');

									$currentimage = (int) (!$singleimage && true);
									if (zp_imageCanRotate()) {
										$disablerotate = '';
									} else {
										$disablerotate = ' disabled="disabled"';
									}
									$target_image_nr = '';
									$thumbnail = $album->get('thumb');
									foreach ($images as $filename) {
										$image = newImage($album, $filename);
										if ($image->exists) {
											printImagePagination($album, $image, $singleimage, $allimagecount, $totalimages, $pagenum, $totalpages, $filter);
											?>
											<br />
											<input type="hidden" name="<?php echo $currentimage; ?>-filename"	value="<?php echo $image->filename; ?>" />
											<div  class="formlayout">
												<br class="clearall">
												<?php
												if ($currentimage > 0) {
													echo '<hr><br />';
												}
												?>

												<div class="floatleft leftdeatil">
													<div style="width: 135px;">
														<?php
														if ($close = (isImagePhoto($image) || !is_null($image->objectsThumb))) {
															?>
															<a href="admin-thumbcrop.php?a=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;i=<?php echo urlencode($image->filename); ?>&amp;subpage=<?php echo $pagenum; ?>&amp;singleimage=<?php echo urlencode($image->filename); ?>&amp;tagsort=<?php echo html_encode($tagsort); ?>" title="<?php html_encode(printf(gettext('crop %s'), $image->filename)); ?>">
																<?php
															}
															?>

															<img id="thumb_img-<?php echo $currentimage; ?>" src="<?php echo html_encode(pathurlencode(getAdminThumb($image, 'medium'))); ?>" alt="<?php echo html_encode($image->filename); ?>" />
															<?php
															if ($close) {
																?>
															</a>
															<?php
														}
														?>
													</div>
													<?php
													if (isImagePhoto($image)) {
														?>
														<p class="buttons"><a href="<?php echo html_encode(pathurlencode($image->getFullImageURL())); ?>" class="colorbox"><img src="images/magnify.png" alt="" /><strong><?php echo gettext('Zoom'); ?></strong></a></p><br style="clear: both" />
														<?php
													}
													?>
													<p class="buttons">
														<a href="<?php echo $image->getLink(); ?>">
															<?php echo BULLSEYE_BLUE; ?>
															<strong><?php echo gettext('View'); ?></strong>
														</a>
													</p><br style="clear: both" />
													<p>
														<?php echo gettext('<strong>Filename:</strong>'); ?>
														<br />
														<?php
														echo truncate_string($image->filename, 30);
														?>
													</p>
													<p><?php echo gettext('<strong>Image id:</strong>'); ?> <?php echo $image->getID(); ?></p>
													<p><?php echo gettext("<strong>Dimensions:</strong>"); ?><br /><?php echo $image->getWidth(); ?> x  <?php echo $image->getHeight() . ' ' . gettext('px'); ?></p>
													<p><?php echo gettext("<strong>Size:</strong>"); ?><br /><?php echo byteConvert($image->getImageFootprint()); ?></p>
												</div>

												<div class="floatright top bulk_checkbox">
													<?php
													if (!$singleimage) {
														?>
														<div class="page-list_icon">
															<input class="checkbox" type = "checkbox" name = "ids[]" value="<?php echo $image->getFileName(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
														</div>
														<?php
													}
													?>
												</div>


												<div class="floatleft">
													<table class="width100percent" id="image-<?php echo $currentimage; ?>">
														<tr>
															<td class="leftcolumn"><?php echo gettext("Title");
													?></td>
															<td class="middlecolumn">
																<?php print_language_string_list($image->getTitle('all'), $currentimage . '-title', false, NULL, '', '100%'); ?>
															</td>

														</tr>
														<tr>
															<td class="leftcolumn">
																<span class="floatright">
																	<?php echo linkPickerIcon($image, 'image_link-' . $currentimage); ?>
																</span>
															<td  class="middlecolumn">
																<?php echo linkPickerItem($image, 'image_link-' . $currentimage); ?>
															</td>

														</tr>
														<tr>
															<td class="leftcolumn"><?php echo gettext("Description"); ?></td>
															<td class="middlecolumn"><?php print_language_string_list($image->getDesc('all'), $currentimage . '-desc', true, NULL, 'texteditor', '100%'); ?></td>
														</tr>
														<?php
														if ($image->get('hasMetadata')) {
															?>
															<tr>
																<td class="leftcolumn"><?php echo gettext("Metadata"); ?></td>
																<td class="middlecolumn">
																	<?php
																	$data = '';
																	$exif = $image->getMetaData();
																	if (false !== $exif) {
																		foreach ($exif as $field => $value) {
																			if (!(empty($value) || $_zp_exifvars[$field][EXIF_FIELD_TYPE] == 'time' && $value = '0000-00-00 00:00:00')) {
																				$display = $_zp_exifvars[$field][EXIF_DISPLAY];
																				if ($display) {
																					$label = $_zp_exifvars[$field][EXIF_DISPLAY_TEXT];
																					$data .= "<tr><td class=\"medtadata_tag " . html_encode($field) . "\">$label: </td> <td>" . html_encode(exifTranslate($value)) . "</td></tr>\n";
																				}
																			}
																		}
																	}
																	if (empty($data)) {
																		echo gettext('None selected for display');
																	} else {
																		?>
																		<div class="metadata_container">
																			<table class="metadata_table" >
																				<?php echo $data; ?>
																			</table>
																		</div>
																		<?php
																	}
																	?>
																</td>
															</tr>
															<?php
														}
														echo zp_apply_filter('edit_image_custom_data', '', $image, $currentimage);
														if (!$singleimage) {
															?>
															<tr>
																<td colspan="100%" style="border-bottom:none;">
																	<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&tab=imageinfo&album=' . $album->name . '&singleimage=' . $image->filename . '&subpage=' . $pagenum; ?>&filter=<?php echo $filter; ?>">
																		<?php echo PENCIL_ICON; ?>
																		<?php echo gettext('Edit all image data'); ?>
																	</a>
																</td>
															</tr>
															<?php
														}
														?>
													</table>
												</div>

												<div class="floatleft rightcolumn">
													<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
													<div class="box-edit">
														<label class="checkboxlabel">
															<input type="checkbox" id="Visible-<?php echo $currentimage; ?>"
																		 name="<?php echo $currentimage; ?>-Visible"
																		 value="1" <?php if ($image->getShow()) echo ' checked = "checked"'; ?>
																		 onclick="$('#publishdate-<?php echo $currentimage; ?>').val('');
																				 $('#expirationdate-<?php echo $currentimage; ?>').val('');
																				 $('#publishdate-<?php echo $currentimage; ?>').css('color', 'black ');
																				 $('.expire-<?php echo $currentimage; ?>').html('');"
																		 />
																		 <?php echo gettext("Published"); ?>
														</label>
														<?php
														if (extensionEnabled('comment_form')) {
															?>
															<label class="checkboxlabel">
																<input type="checkbox" id="allowcomments-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-allowcomments" value="1" <?php
																if ($image->getCommentsAllowed()) {
																	echo ' checked = "checked"';
																}
																?> />
																			 <?php echo gettext("Allow Comments"); ?>
															</label>
															<?php
														}
														if (extensionEnabled('hitcounter')) {
															$hc = $image->get('hitcounter');
															if (empty($hc)) {
																$hc = '0';
															}
															?>
															<label class="checkboxlabel">
																<input type="checkbox" name="reset_hitcounter<?php echo $currentimage; ?>"<?php if (!$hc) echo ' disabled = "disabled"'; ?> />
																<?php echo sprintf(ngettext("Reset hitcounter (%u hit)", "Reset hitcounter (%u hits)", $hc), $hc); ?>
															</label>
															<?php
														}
														if (extensionEnabled('rating')) {
															$tv = $image->get('total_value');
															$tc = $image->get('total_votes');

															if ($tc > 0) {
																$hc = $tv / $tc;
																?>
																<label class="checkboxlabel">
																	<input type="checkbox" id="reset_rating-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-reset_rating" value="1" />
																	<?php printf(ngettext('Reset rating (%u star)', 'Reset rating (%u stars)', $hc), $hc); ?>
																</label>
																<?php
															} else {
																?>
																<label class="checkboxlabel">
																	<input type="checkbox" id="reset_rating-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-reset_rating" value="1" disabled="disabled"/>
																	<?php echo gettext('Reset rating (unrated)'); ?>
																</label>
																<?php
															}
														}
														$publishdate = $image->getPublishDate();
														$expirationdate = $image->getExpireDate();
														?>
														<script type="text/javascript">
															// <!-- <![CDATA[
															$(function () {
																$("#publishdate-<?php echo $currentimage; ?>,#expirationdate-<?php echo $currentimage; ?>").datepicker({
																	dateFormat: 'yy-mm-dd',
																	showOn: 'button',
																	buttonImage: '../zp-core/images/calendar.png',
																	buttonText: '<?php echo gettext("calendar"); ?>',
																	buttonImageOnly: true
																});
																$('#publishdate-<?php echo $currentimage; ?>').change(function () {
																	var today = new Date();
																	var pub = $('#publishdate-<?php echo $currentimage; ?>').datepicker('getDate');
																	if (pub.getTime() > today.getTime()) {
																		$("Visible-<?php echo $currentimage; ?>").removeAttr('checked');
																		$('#publishdate-<?php echo $currentimage; ?>').css('color', 'blue');
																	} else {
																		$("Visible-<?php echo $currentimage; ?>").attr('checked', 'checked');
																		$('#publishdate-<?php echo $currentimage; ?>').css('color', 'black');
																	}
																});
																$('#expirationdate-<?php echo $currentimage; ?>').change(function () {
																	var today = new Date();
																	var expiry = $('#expirationdate-<?php echo $currentimage; ?>').datepicker('getDate');
																	if (expiry.getTime() > today.getTime()) {
																		$(".expire<-<?php echo $currentimage; ?>").html('');
																	} else {
																		$(".expire-<?php echo $currentimage; ?>").html('<br /><?php echo addslashes(gettext('Expired!')); ?>');
																	}
																});
															});
															// ]]> -->
														</script>
														<br class="clearall">
														<hr />
														<p>
															<label for="publishdate-<?php echo $currentimage; ?>"><?php echo gettext('Publish date'); ?> <small>(YYYY-MM-DD)</small></label>
															<br /><input value="<?php echo $publishdate; ?>" type="text" size="20" maxlength="30" name="publishdate-<?php echo $currentimage; ?>" id="publishdate-<?php echo $currentimage; ?>" <?php if ($publishdate > date('Y-m-d H:i:s')) echo 'style="color:blue"'; ?> />
															<br /><label for="expirationdate-<?php echo $currentimage; ?>"><?php echo gettext('Expiration date'); ?> <small>(YYYY-MM-DD)</small></label>
															<br /><input value="<?php echo $expirationdate; ?>" type="text" size="20" maxlength="30" name="expirationdate-<?php echo $currentimage; ?>" id="expirationdate-<?php echo $currentimage; ?>" />
															<strong class="expire-<?php echo $currentimage; ?>" style="color:red">
																<?php
																if (!empty($expirationdate) && ($expirationdate <= date('Y-m-d H:i:s'))) {
																	echo '<br />' . gettext('Expired!');
																}
																?>
															</strong>
														</p>
													</div>

													<h2 class="h2_bordered_edit"><?php echo gettext("Utilities"); ?></h2>
													<div class="box-edit">
														<!-- Move/Copy/Rename this image -->
														<label class="checkboxlabel">
															<input type="radio" id="move-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="move" onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'move');"  /> <?php echo gettext("Move"); ?>
														</label>
														<label class="checkboxlabel">
															<input type="radio" id="copy-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="copy" onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'copy');"  /> <?php echo gettext("Copy"); ?>
														</label>
														<label class="checkboxlabel">
															<input type="radio" id="rename-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="rename" onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'rename');"  /> <?php echo gettext("Rename File"); ?>
														</label>
														<label class="checkboxlabel">
															<input type="radio" id="Delete-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="delete" onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', '');
																	deleteConfirm('Delete-<?php echo $currentimage; ?>', '<?php echo $currentimage; ?>', '<?php echo addslashes(gettext("Are you sure you want to select this image for deletion?")); ?>')" /> <?php echo gettext("Delete image") ?>
														</label>
														<br class="clearall">
														<div id="movecopydiv-<?php echo $currentimage; ?>" class="resetHide" style="padding-top: .5em; padding-left: .5em; padding-bottom: .5em; display: none;">
															<span class="nowrap">
																<?php echo gettext("to"); ?>:
																<select id="albumselectmenu-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-albumselect" onchange="">
																	<?php
																	foreach ($mcr_albumlist as $fullfolder => $albumtitle) {
																		$singlefolder = $fullfolder;
																		$saprefix = "";
																		$salevel = 0;
																		$selected = "";
																		if ($album->name == $fullfolder) {
																			$selected = " selected=\"selected\" ";
																		}
																		// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
																		while (strstr($singlefolder, '/') !== false) {
																			$singlefolder = substr(strstr($singlefolder, '/'), 1);
																			$saprefix = "&nbsp; &nbsp;&nbsp;" . $saprefix;
																			$salevel++;
																		}
																		echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: ' . $bglevels[$salevel] . ';"' : '')
																		. "$selected>" . $saprefix . $singlefolder . "</option>\n";
																	}
																	?>
																</select>
															</span>
															<p class="buttons">
																<a onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', '');">
																	<?php echo CROSS_MARK_RED; ?>
																	<?php echo gettext("Cancel"); ?>
																</a>
															</p>
														</div>
														<div id="renamediv-<?php echo $currentimage; ?>" class="resetHide" style="padding-top: .5em; padding-left: .5em; display: none;">
															<span class="nowrap">
																<?php echo gettext("to"); ?>:
																<input name="<?php echo $currentimage; ?>-renameto" type="text" value="<?php echo $image->filename; ?>" />
															</span>
															<p class="buttons">
																<a	onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', '');">
																	<?php echo CROSS_MARK_RED; ?>
																	<?php echo gettext("Cancel"); ?>
																</a>
															</p>
														</div>

														<div id="deletemsg<?php echo $currentimage; ?>" class="resetHide"	style="padding-top: .5em; padding-left: .5em; padding-bottom: .5em; color: red; display: none">
															<span class="nowrap">
																<?php echo gettext('Image will be deleted when changes are applied.'); ?>
															</span>
															<p class="buttons">
																<a	onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', '');">
																	<?php echo CROSS_MARK_RED; ?>
																	<?php echo gettext("Cancel"); ?>
																</a>
															</p>
														</div>
														<div class="clearall" ></div>

														<?php
														if (isImagePhoto($image)) {
															?>
															<hr />
															<?php echo gettext("Rotation:"); ?>
															<br />
															<?php
															$unflip = array(0 => 0, 1 => 0, 2 => 0, 3 => 3, 4 => 3, 5 => 8, 6 => 6, 7 => 6, 8 => 8);
															$rotation = @$unflip[substr(trim($image->get('rotation'), '!'), 0, 1)];
															?>
															<input type="hidden" name="<?php echo $currentimage; ?>-oldrotation" value="<?php echo $rotation; ?>" />
															<label class="checkboxlabel">
																<input type="radio" id="rotation_none-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="0" <?php
																checked(0, $rotation);
																echo $disablerotate
																?> />
																			 <?php echo gettext('none'); ?>
															</label>
															<label class="checkboxlabel">
																<input type="radio" id="rotation_90-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="6" <?php
																checked(6, $rotation);
																echo $disablerotate
																?> />
																			 <?php echo gettext('90 degrees'); ?>
															</label>
															<label class="checkboxlabel">
																<input type="radio" id="rotation_180-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="3" <?php
																checked(3, $rotation);
																echo $disablerotate
																?> />
																			 <?php echo gettext('180 degrees'); ?>
															</label>
															<label class="checkboxlabel">
																<input type="radio" id="rotation_270-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="8" <?php
																checked(8, $rotation);
																echo $disablerotate
																?> />
																			 <?php echo gettext('270 degrees'); ?>
															</label>
															<?php
														}
														?>
														<br class="clearall">
														<hr />
														<div class="button buttons tooltip" title="<?php printf(gettext('Refresh %s metadata'), $image->filename); ?>">
															<a href="admin-edit.php?action=refresh&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;image=<?php echo urlencode($image->filename); ?>&amp;subpage=<?php echo $pagenum . $singleimagelink; ?>&amp;tagsort=<?php echo html_encode($tagsort); ?>&amp;XSRFToken=<?php echo getXSRFToken('imagemetadata'); ?>" >
																<?php echo CIRCLED_BLUE_STAR; ?>
																<?php echo gettext("Refresh Metadata"); ?>
															</a>
															<br class="clearall">
														</div>
														<?php
														if (isImagePhoto($image) || !is_null($image->objectsThumb)) {
															?>
															<div class="button buttons tooltip" title="<?php printf(gettext('crop %s'), $image->filename); ?>">
																<a href="admin-thumbcrop.php?a=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;i=<?php echo urlencode($image->filename); ?>&amp;subpage=<?php echo $pagenum . $singleimagelink; ?>&amp;tagsort=<?php echo html_encode($tagsort); ?>" >
																	<img src="images/shape_handles.png" alt="" /><?php echo gettext("Crop thumbnail"); ?>
																</a>
																<br class="clearall">
															</div>
															<?php
														}
														echo zp_apply_filter('edit_image_utilities', '<!--image-->', $image, $currentimage, $pagenum, $tagsort, $singleimage); //pass space as HTML because there is already a button shown for cropimage
														?>
														<span class="clearall" ></span>
													</div>
												</div>


											</div>
											<br class="clearall">


											<?php
											$currentimage++;
										}
									}
									?>


									<p class="buttons">
										<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
											<?php echo BACK_ARROW_BLUE; ?>
											<strong><?php echo gettext("Back"); ?></strong>
										</a>
										<button type="submit">
											<?php echo CHECKMARK_GREEN; ?>
											<strong><?php echo gettext("Apply"); ?></strong>
										</button>
										<button type="reset">
											<?php echo CROSS_MARK_RED; ?>
											<strong><?php echo gettext("Reset"); ?></strong>
										</button>
									</p>



									<?php
									printImagePagination($album, $image, $singleimage, $allimagecount, $totalimages, $pagenum, $totalpages, $filter);
									?>
									<br class="clearall">
								</div>
								<input type="hidden" name="checkForPostTruncation" value="1" />
							</form>

							<?php
						}
						?>
					</div><!-- images -->
					<?php
				}

				if ($subtab != "albuminfo") {
					?>
					<!-- page trailer -->

					<?php
				}
				/*				 * * MULTI-ALBUM ************************************************************************** */
			} else

			if ($is_massedit) {
				// one time generation of this list.
				$mcr_albumlist = array();
				genAlbumList($mcr_albumlist);
				$albumdir = "";
				if (isset($_GET['album'])) {
					$folder = sanitize_path($_GET['album']);
					$album = newAlbum($folder);
					if ($album->isMyItem(ALBUM_RIGHTS)) {
						$albums = $album->getAlbums();
						$pieces = explode('/', $folder);
						$albumdir = "&album=" . pathurlencode($folder) . '&tab=subalbuminfo';
					} else {
						$albums = array();
					}
				} else {
					$albumsprime = $_zp_gallery->getAlbums();
					$albums = array();
					foreach ($albumsprime as $folder) { // check for rights
						$album = newAlbum($folder);
						if ($album->isMyItem(ALBUM_RIGHTS)) {
							$albums[] = $folder;
						}
					}
				}
				zp_apply_filter('admin_note', 'albums', $subtab);
				?>
				<h1>
					<?php echo gettext("Edit All Albums in"); ?> <?php
					if (!isset($_GET['album'])) {
						echo gettext("Gallery");
					} else {
						echo "<em>" . html_encode($album->name) . "</em>";
					}
					?>
				</h1>
				<div class="tabbox">
					<?php consolidatedEditMessages('massedit'); ?>
					<form class="dirtylistening" onReset="setClean('form_albumedit-multi');" ame="albumedit" id="form_albumedit-multi" autocomplete="off"	action="?page=edit&amp;action=save<?php echo $albumdir ?>" method="POST" >
						<?php XSRFToken('albumedit'); ?>
						<input type="hidden" name="totalalbums" value="<?php echo sizeof($albums); ?>" />
						<span class="buttons">
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php?page=edit">
								<?php echo BACK_ARROW_BLUE; ?>
								<strong><?php echo gettext("Back"); ?></strong>
							</a>
							<button type="submit">
								<?php echo CHECKMARK_GREEN; ?>
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<button type="reset" onclick="$('.deletemsg').hide();" >
								<?php echo WASTEBASKET; ?>
								<?php echo gettext('Delete'); ?>
							</button>
						</span>
						<br class = "clearall">
						<br />
						<div class = "outerbox">
							<?php
							$currentalbum = 1;
							foreach ($albums as $folder) {
								$album = newAlbum($folder);
								echo "\n<!-- " . $album->name . " -->\n";
								?>
								<div class="innerbox<?php if (!($currentalbum % 2)) echo '_dark'; ?>" style="padding-left: 15px;padding-right: 15px;">

									<a href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php?page=edit&album=<?php echo urlencode($album->name); ?>&tab=albuminfo">
										<em><strong><?php echo urlencode($album->name); ?></strong></em></a>
									<br /><br />
									<?php
									printAlbumEditForm($currentalbum, $album, false);
									$currentalbum++;
									?>
								</div>
								<?php
							}
							?>
						</div>
						<br />
						<span class="buttons">
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php?page=edit">
								<?php echo BACK_ARROW_BLUE; ?>
								<strong><?php echo gettext("Back"); ?></strong>
							</a>
							<button type="submit">
								<?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<button type="reset" onclick="$('.deletemsg').hide();" >
								<?php echo WASTEBASKET; ?>
								<?php echo gettext('Delete'); ?>
							</button>
						</span>
						<br class="clearall">

					</form>
				</div>
				<?php
				/*				 * * EDIT ALBUM SELECTION ******************************************************************** */
			}

			else { /* Display a list of albums to edit. */
				zp_apply_filter('admin_note', 'albums', $subtab);
				?>
				<h1><?php echo gettext("Albums"); ?></h1>
				<div class="tabbox">
					<?php
					consolidatedEditMessages('');
					$albums = getNestedAlbumList(NULL, $album_nesting);
					if (count($albums) > 0) {
						if (zp_loggedin(ADMIN_RIGHTS) && (count($albums)) > 1) {

							printEditDropdown('', array('1', '2', '3', '4', '5'), $album_nesting);

							$sort = $_zp_sortby;
							foreach ($sort as $name => $action) {
								$sort[$name . ' (' . gettext('descending') . ')'] = $action . '_DESC';
							}
							?>
							<br clear="all"><br />
							<?php
							$type = strtolower($_zp_gallery->getSortType());
							if ($type && !in_array($type, $sort)) {
								if ($type == 'manual') {
									$sort[gettext('Manual')] = $type;
								} else {
									$sort[gettext('Custom')] = $type = 'custom';
								}
							}
							if ($_zp_gallery->getSortDirection()) {
								$type .= '_DESC';
							}
							$cv = array($type);
							if (($type == 'manual') || ($type == 'random') || ($type == '')) {
								$dsp = 'none';
							} else {
								$dsp = 'inline';
							}
							echo gettext('Drag the albums into the order you wish them displayed.');
							?>
							<form name="gallery_sort" style="float: right;" method="post" action="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-edit.php?page=edit&action=gallery_sortorder" >
								<?php XSRFToken('gallery_sortorder'); ?>
								<span class="nowrap">
									<?php echo gettext('Sort albums by:'); ?>
									<select id="albumsortselect" name="gallery_sortby" onchange="this.form.submit();">
										<?php generateListFromArray($cv, $sort, false, true); ?>
									</select>
								</span>
							</form>
							<br clear="all">
							<p class="notebox">
								<?php echo gettext('<strong>Note:</strong> Dragging an album under a different parent will move the album. You cannot move albums under a <em>dynamic</em> album.'); ?>
							</p>
							<?php
						}
						?>
						<p>
							<?php
							echo gettext('Select an album to edit its description and data.');
							?>
						</p>

						<form class="dirtylistening" onReset="setClean('sortableListForm');$('#albumsort').sortable('cancel');" action="?page=edit&amp;action=savealbumorder" method="post" name="sortableListForm" id="sortableListForm" onsubmit="return confirmAction();" autocomplete="off" >
							<?php XSRFToken('savealbumorder'); ?>
							<span class="buttons">
								<?php
								if ($album_nesting > 1 || zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<button class="serialize buttons" type="submit" >
										<?php echo CHECKMARK_GREEN; ?>
										<strong><?php echo gettext("Apply"); ?></strong>
									</button>
									<button type="reset" value="<?php echo gettext('Reset') ?>">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Reset"); ?></strong>
									</button>
									<?php
								}
								if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<span class="floatright">
										<button type="button" onclick="newAlbumJS('', false);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong></button>
										<button type="button" onclick="newAlbumJS('', true);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New dynamic album'); ?></strong></button>
									</span>
									<?php
								}
								?>
							</span>
							<br class="clearall">
							<br />

							<div class="headline"><?php echo gettext("Edit this album"); ?>
								<?php printBulkActions($checkarray_albums); ?>
							</div>
							<div class="subhead">
								<label class="buttons" style="float: left;padding-top:3px;">
									<a href="admin-edit.php?showthumbs=<?php echo $thumbshow ?>" title="<?php echo gettext('Thumbnail generation may be time consuming on slow servers or when there are a lot of images.'); ?>">
										<?php echo $thumbmsg; ?>
									</a>
								</label>
								<label style="float: right;padding-right:20px;">
									<?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this
																	.checked);" />
								</label>
							</div>
							<div class="bordered">
								<ul class="page-list" id="albumsort">
									<?php printNestedAlbumsList($albums, $showthumb, NULL); ?>
								</ul>

							</div>
							<div>
								<?php printAlbumLegend(); ?>
							</div>

							<br class="clearall">
							<span id="serializeOutput"></span>
							<input name="update" type="hidden" value="Save Order" />

							<div class="buttons">
								<?php
								if ($album_nesting > 1 || zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<button class="serialize buttons" type="submit" >
										<?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong>
									</button>
									<button type="reset" value="<?php echo gettext('Reset') ?>">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Reset"); ?></strong>
									</button>
									<?php
								}
								if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<span class="floatright">
										<button type="button" onclick="newAlbumJS('', false);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong></button>
										<button type="button" onclick="newAlbumJS('', true);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New dynamic album'); ?></strong></button>
									</span>
									<?php
								}
								?>
							</div>

						</form>
						<br class="clearall">
					</div>

					<?php
				} else {
					echo gettext("There are no albums for you to edit.");
					if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
						?>
						<span class="floatright">
							<p class="buttons">
								<button type="button" onclick="newAlbumJS('', false);">
									<img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong>
								</button>
								<button type="button" onclick="newAlbumJS('', true);">
									<img src="images/folder.png" alt="" /><strong><?php echo gettext('New dynamic album'); ?></strong>
								</button>
							</p>
						</span>
						<?php
					}
				}
			}
			?>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
<?php
// to fool the validator
echo "\n</html>";
?>
