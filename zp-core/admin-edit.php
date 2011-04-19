<?php
/**
 * admin-edit.php editing of albums.
 * @package admin
 */

// force UTF-8 Ø

/* Don't put anything before this line! */
define('OFFSET_PATH', 1);

require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(ALBUM_RIGHTS, $return = currentRelativeURL(__FILE__));

if (isset($_GET['tab'])) {
	$subtab = sanitize($_GET['tab']);
} else {
	$subtab = '';
}

$gallery = new Gallery();
$subalbum_nesting = 1;
$gallery_nesting = 1;
$imagesTab_imageCount = 10;
processEditSelection($subtab);

//check for security incursions
if (isset($_GET['album'])) {
	$folder = sanitize_path($_GET['album']);
	$album = new Album($gallery, $folder);
	if (!$album->isMyItem(ALBUM_RIGHTS)) {
		if (isset($_GET['uploaded'])) {	// it was an upload to an album which we cannot edit->return to sender
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-upload.php?uploaded=1');
			exit();
		}
		if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $return);
			exit();
		}
	}
} else {
	$album = NULL;
}

$tagsort = getTagOrder();
$mcr_errors = array();


$gallery->garbageCollect();
if (isset($_GET['showthumbs'])) {	// switch the display selector
	$how = sanitize($_GET['showthumbs']);
	setBoolOption('album_tab_default_thumbs_'.(is_object($album)?$album->name:''), $how == 'no');
}
if (isset($_GET['action'])) {
	$action = $_GET['action'];
	switch ($action) {
		/** reorder the tag list ******************************************************/
		/******************************************************************************/
		case 'savealbumorder':
			XSRFdefender('savealbumorder');
			setOption('gallery_sorttype','manual');
			setOption('gallery_sortdirection',0);
			$notify = postAlbumSort(NULL);
			if (isset($_POST['ids'])) {
				$action = processAlbumBulkActions();
				if(!empty($action)) $action = '&bulkmessage='.$action;
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit'.$action.'&saved'.$notify);
			exit();
			break;
		case 'savesubalbumorder':
			XSRFdefender('savealbumorder');
			$album = new Album($gallery, $folder);
			$album->setSubalbumSortType('manual');
			$album->setSortDirection('album', 0);
			$album->save();
			$notify = postAlbumSort($album->get('id'));
			if (isset($_POST['ids'])) {
				$action = processAlbumBulkActions();
				if(!empty($action)) $action = '&bulkmessage='.$action;
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit'.$action.'&album='.$folder.'&tab=subalbuminfo&saved'.$notify);
			exit();
			break;
		case 'sorttags':
			if (isset($_GET['subpage'])) {
				$pg = '&subpage='.$_GET['subpage'];
				$tab = '&tab=imageinfo';
			} else {
				$pg = '';
				$tab = '';
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album='.$folder.$pg.'&tagsort='.$tagsort.$tab);
			exit();
			break;

		/** clear the cache ***********************************************************/
		/******************************************************************************/
		case "clear_cache":
			XSRFdefender('clear_cache');
			$gallery->clearCache(SERVERCACHE . '/' . sanitize_path($_POST['album']));
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&cleared&album='.$_POST['album']);
			exit();
			break;

		/** Publish album  ************************************************************/
		/******************************************************************************/
		case "publish":
			XSRFdefender('albumedit');
			$album = new Album($gallery, $folder);
			$album->setShow($_GET['value']);
			$album->save();
			$return = pathurlencode(dirname($folder));
			if (!empty($return)) {
				if ($return == '.' || $return == '/') {
					$return = '';
				} else {
					$return = '&album='.$return.'&tab=subalbuminfo';
				}
			}
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit'.$return);
			exit();
			break;

		/** Reset hitcounters ***********************************************************/
		/********************************************************************************/
		case "reset_hitcounters":
			XSRFdefender('hitcounter');
			$id = sanitize_numeric($_REQUEST['albumid']);
			$where = ' WHERE `id`='.$id;
			$imgwhere = ' WHERE `albumid`='.$id;
			$return = '?counters_reset';
			$subalbum = '';
			if (isset($_REQUEST['subalbum'])) {
				$return = pathurlencode(dirname(sanitize_path($_REQUEST['album'])));
				$subalbum = '&tab=subalbuminfo';
			} else {
				$return = pathurlencode(sanitize_path(urldecode($_POST['album'])));
			}
			if (empty($return) || $return == '.' || $return == '/') {
				$return = '?page=edit&counters_reset';
			} else {
				$return = '?page=edit&album='.$return.'&counters_reset'.$subalbum;
			}
			query("UPDATE " . prefix('albums') . " SET `hitcounter`= 0" . $where);
			query("UPDATE " . prefix('images') . " SET `hitcounter`= 0" . $imgwhere);
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php' . $return);
			exit();
			break;

		//** DELETEIMAGE **************************************************************/
		/******************************************************************************/
		case 'deleteimage':
			XSRFdefender('delete');
			$albumname = sanitize_path($_REQUEST['album']);
			$imagename = sanitize_path($_REQUEST['image']);
			$album = new Album($gallery, $albumname);
			$image = newImage($album, $imagename);
			if ($image->remove()) {
				$nd = 1;
			} else {
				$nd = 2;
			}

			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album='.pathurlencode($albumname).'&ndeleted='.$nd);
			exit();
			break;

		/** SAVE **********************************************************************/
		/******************************************************************************/
		case "save":
			unset($folder);
			$returntab = '';
			XSRFdefender('albumedit');

			/** SAVE A SINGLE ALBUM *******************************************************/
			if (isset($_POST['album'])) {
				$folder = sanitize_path($_POST['album']);
				$album = new Album($gallery, $folder);
				$notify = '';
				$returnalbum = '';
				if (isset($_POST['savealbuminfo'])) {
					$notify = processAlbumEdit(0, $album, $returnalbum);
					$returntab = '&tagsort='.$tagsort.'&tab=albuminfo';
				}

				if (isset($_POST['totalimages'])) {
					if (isset($_POST['checkForPostTruncation'])) {
						$returntab = '&tagsort='.$tagsort.'&tab=imageinfo';

						if (isset($_POST['ids'])) {	//	process bulk actions, not individual image actions.
							$action = processBulkImageActions($album);
							if(!empty($action)) $notify = '&bulkmessage='.$action;
						} else {

							if (isset($_POST['thumb'])) {
								$thumbnail = sanitize_numeric($_POST['thumb'])-1;
							} else {
								$thumbnail = -1;
							}
							$oldsort = sanitize($_POST['oldalbumimagesort'], 3);
							if (getOption('albumimagedirection')) $oldsort = $oldsort.'_desc';
							$newsort = sanitize($_POST['albumimagesort'],3);
							if ($oldsort == $newsort) {
								for ($i = 0; $i < $_POST['totalimages']; $i++) {
									$filename = sanitize($_POST["$i-filename"]);
									// The file might no longer exist
									$image = newImage($album, $filename);
									if ($image->exists) {
										if (isset($_POST[$i.'-MoveCopyRename'])) {
											$movecopyrename_action = sanitize($_POST[$i.'-MoveCopyRename'],3);
										} else {
											$movecopyrename_action = '';
										}
										if ($movecopyrename_action == 'delete') {
											$image->remove();
										} else {
											if ($thumbnail == $i) { //selected as album thumb
												$album = $image->getAlbum();
												$album->setAlbumThumb($image->filename);
												$album->save();
											}
											if (isset($_POST[$i.'-reset_rating'])) {
												$image->set('total_value', 0);
												$image->set('total_votes', 0);
												$image->set('used_ips', 0);
											}
											$image->setTitle(process_language_string_save("$i-title", 2));
											$image->setDesc(process_language_string_save("$i-desc", 1));
											$image->setLocation(process_language_string_save("$i-location", 3));
											$image->setCity(process_language_string_save("$i-city", 3));
											$image->setState(process_language_string_save("$i-state", 3));
											$image->setCountry(process_language_string_save("$i-country", 3));
											$image->setCredit(process_language_string_save("$i-credit", 1));
											$image->setCopyright(process_language_string_save("$i-copyright", 1));
											if (isset($_POST[$i.'-oldrotation'])) {
												$oldrotation = sanitize_numeric($_POST[$i.'-oldrotation']);
											} else {
												$oldrotation = 0;
											}
											if (isset($_POST[$i.'-rotation'])) {
												$rotation = sanitize_numeric($_POST[$i.'-rotation']);
											} else {
												$rotation = 0;
											}
											if ($rotation != $oldrotation) {
												$image->set('EXIFOrientation', $rotation);
												$image->updateDimensions();
												$album = $image->getAlbum();
												$gallery->clearCache(SERVERCACHE . '/' . $album->name);
											}
											$tagsprefix = 'tags_'.$i.'-';
											$tags = array();
											$l = strlen($tagsprefix);
											foreach ($_POST as $key => $value) {
												$key = postIndexDecode($key);
												if (substr($key, 0, $l) == $tagsprefix) {
													if ($value) {
														$tags[] = substr($key, $l);
													}
												}
											}
											$tags = array_unique($tags);
											$image->setTags(sanitize($tags, 3));

											$image->setDateTime(sanitize($_POST["$i-date"]));
											$image->setShow(isset($_POST["$i-Visible"]));
											$image->setCommentsAllowed(isset($_POST["$i-allowcomments"]));
											if (isset($_POST["$i-reset_hitcounter"])) {
												$image->set('hitcounter', 0);
											}
											$wmt = sanitize($_POST["$i-image_watermark"],3);
											$image->setWatermark($wmt);
											$wmuse = 0;
											if (isset($_POST['wm_image-'.$i])) $wmuse = $wmuse | WATERMARK_IMAGE;
											if (isset($_POST['wm_thumb-'.$i])) $wmuse = $wmuse | WATERMARK_THUMB;
											if (isset($_POST['wm_full-'.$i])) $wmuse = $wmuse | WATERMARK_FULL;
											$image->setWMUse($wmuse);
											$codeblock1 = sanitize($_POST['codeblock1-'.$i], 0);
											$codeblock2 = sanitize($_POST['codeblock2-'.$i], 0);
											$codeblock3 = sanitize($_POST['codeblock3-'.$i], 0);
											$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
											$image->setCodeblock($codeblock);
											if (isset($_POST[$i.'-owner'])) $image->setOwner(sanitize($_POST[$i.'-owner']));
											$image->set('filesize',filesize($image->localpath));

											$custom = process_language_string_save("$i-custom_data", 1);
											$image->setCustomData(zp_apply_filter('save_image_custom_data', $custom, $i));
											zp_apply_filter('save_image_utilities_data', $image, $i);
											$image->save();

											// Process move/copy/rename
											if ($movecopyrename_action == 'move') {
												$dest = trim(sanitize_path($_POST[$i.'-albumselect'], 3));
												if ($dest && $dest != $folder) {
													if ($e = $image->moveImage($dest)) {
														$notify = "&mcrerr=".$e;
													}
												} else {
													// Cannot move image to same album.
													$notify = "&mcrerr=2";
												}
											} else if ($movecopyrename_action == 'copy') {
												$dest = trim(sanitize_path($_POST[$i.'-albumselect'],2));
												if ($dest && $dest != $folder) {
													if($e = $image->copy($dest)) {
														$notify = "&mcrerr=".$e;
													}
												} else {
													// Cannot copy image to existing album.
													// Or, copy with rename?
													$notify = "&mcrerr=2";
												}
											} else if ($movecopyrename_action == 'rename') {
												$renameto = trim(sanitize_path($_POST[$i.'-renameto'],3));
												if ($e = $image->rename($renameto)) {
													$notify = "&mcrerr=".$e;
												}
											}
										}
									}
								}
							} else {
								if (strpos($newsort, '_desc')) {
									setOption('albumimagesort', substr($newsort, 0, -5));
									setOption('albumimagedirection', 'DESC');
								} else {
									setOption('albumimagesort', $newsort);
									setOption('albumimagedirection', '');
								}
								$notify = '&';
							}
						}

					} else {
						$notify = '&post_error';
				}
				}
				if (!empty($returnalbum)) {
					$folder = $returnalbum;
				}
				$qs_albumsuffix = '';

				/** SAVE MULTIPLE ALBUMS ******************************************************/
			} else if ($_POST['totalalbums']) {
				$notify = '';
				for ($i = 1; $i <= $_POST['totalalbums']; $i++) {
					if ($i>0) {
						$prefix = $i."-";
					} else {
						$prefix = '';
					}
					$f = sanitize_path(trim($_POST[$prefix.'folder']));
					$album = new Album($gallery, $f);
					$returnalbum = '';
					$rslt = processAlbumEdit($i, $album, $returnalbum);
					if (!empty($rslt)) {
						$notify = $rslt;
					}
				}
				$qs_albumsuffix = "&massedit";

			}
			// Redirect to the same album we saved.
			if (isset($folder)) {
				$qs_albumsuffix .= '&album='.pathurlencode($folder);
			}
			if (isset($_POST['subpage'])) {
				$pg = '&subpage='.$_POST['subpage'];
			} else {
				$pg = '';
			}
			$msg = zp_apply_filter('edit_error','');
			if ($msg) {
				$notify .= '&edit_error='.$msg;
			}
			if ($notify == '&') {
				$notify = '';
			} else {
				if (empty($notify)) $notify = '&saved';
			}
			header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$qs_albumsuffix.$notify.$pg.$returntab);
			exit();
			break;

		/** DELETION ******************************************************************/
		/*****************************************************************************/
		case "deletealbum":
			XSRFdefender('delete');
			if ($folder) {
				$album = new Album($gallery, $folder);
				if ($album->remove()) {
					$nd = 3;
				} else {
					$nd = 4;
				}
				if (isset($_GET['return'])) {
					$albumdir = pathurlencode(sanitize($_GET['return'],3));
				} else {
					$albumdir = dirname($folder);

					if ($albumdir != '/' && $albumdir != '.') {
						$albumdir = "&album=" . pathurlencode($albumdir);
					} else {
						$albumdir = '';
					}
				}
			}
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit" . $albumdir . "&ndeleted=".$nd);
			exit();
			break;
		case 'newalbum':
			XSRFdefender('newalbum');
			$name = sanitize($_GET['name']);
			$seoname = seoFriendly($name);
			if (empty($folder) || $folder == '/' || $folder == '.') {
				$albumdir = '';
				$folder = $seoname;
			} else {
				$albumdir = "&album=" . pathurlencode($folder);
				$folder = $folder.'/'.$seoname;
			}
			$uploaddir = $gallery->albumdir . internalToFilesystem($folder);
			if (is_dir($uploaddir)) {
				if ($name != $seoname) $name .= ' ('.$seoname.')';
				if (isset($_GET['albumtab'])) {
					if (empty($albumdir)) {
						$tab='';
					} else {
						$tab = '&tab=subalbuminfo';
					}
				} else {
					$tab = '&tab=albuminfo';
				}
				header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit$albumdir&exists=" . urlencode($name).$tab);
				exit();
			} else {
				mkdir_recursive($uploaddir, CHMOD_VALUE);
			}
			@chmod($uploaddir, CHMOD_VALUE);

			$album = new Album($gallery, $folder);
			if ($album->exists) {
				$album->setTitle($name);
				$album->save();
				header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-edit.php?page=edit" . "&album=" . pathurlencode($folder));
				exit();
			} else {
				$AlbumDirName = str_replace(SERVERPATH, '', $gallery->albumdir);
				zp_error(gettext("The album couldn't be created in the 'albums' folder. This is usually a permissions problem. Try setting the permissions on the albums and cache folders to be world-writable using a shell:")." <code>chmod 777 " . $AlbumDirName . '/'.CACHEFOLDER.'/' ."</code>, "
				. gettext("or use your FTP program to give everyone write permissions to those folders."));
			}
			break;
	} // end of switch
} else {
	if (isset($_GET['albumimagesort'])) {
		$newsort = sanitize($_GET['albumimagesort'],3);
		if (strpos($newsort, '_desc')) {
			setOption('albumimagesort', substr($newsort, 0, -5), false);
			setOption('albumimagedirection', 'DESC', false);
		} else {
			setOption('albumimagesort', $newsort, false);
			setOption('albumimagedirection', '', false);
		}
	}
}



/* NO Admin-only content between this and the next check. */

/************************************************************************************/
/** End Action Handling *************************************************************/
/************************************************************************************/

// Print our header
if (isset($_GET['album']) && !isset($_GET['massedit'])) {
	$folder = sanitize_path($_GET['album']);
	if ($folder == '/' || $folder == '.') {
		$parent = '';
	} else {
		$parent = '&amp;album='.$folder.'&amp;tab=subalbuminfo';
	}
	$album = new Album($gallery, $folder);
	$subtab = setAlbumSubtabs($album);
}
if (empty($subtab)) {
	if (isset($_GET['album'])) {
		$subtab = 'albuminfo';
	}
}

printAdminHeader('edit',$subtab);
datepickerJS();
codeblocktabsJS();

if ((!isset($_GET['massedit']) && !isset($_GET['album'])) || $subtab=='subalbuminfo') {
	printSortableHead();
}
if (isset($_GET['album']) && (empty($subtab) || $subtab=='albuminfo') || isset($_GET['massedit'])) {
	$result = db_list_fields('albums');
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'".$row['Field']."'";
		}
	}
	sort($dbfields);
	$albumdbfields = implode(',', $dbfields);
	$result = db_list_fields('images');
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'".$row['Field']."'";
		}
	}
	sort($dbfields);
	$imagedbfields = implode(',', $dbfields);
	?>
	<script type="text/javascript" src="js/encoder.js"></script>
	<script type="text/javascript" src="js/tag.js"></script>
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
	function newAlbum(folder,albumtab) {
		var album = prompt('<?php echo gettext('New album name?'); ?>', '<?php echo gettext('new album'); ?>');
		if (album) {
			launchScript('',['action=newalbum','album='+folder,'name='+encodeURIComponent(album),'albumtab='+albumtab,'XSRFToken=<?php echo getXSRFToken('newalbum'); ?>']);
		}
	}
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}
	// ]]> -->
</script>

<?php
zp_apply_filter('texteditor_config', '','zenphoto');


echo "\n</head>";
?>

<body>

<?php	printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php
$showthumb = !getOption('album_tab_default_thumbs_'.(is_object($album)?$album->name:''));
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
											gettext('Add tags') => 'addtags',
											gettext('Clear tags') => 'cleartags',
											gettext('Disable comments') => 'commentsoff',
											gettext('Enable comments') => 'commentson',
											gettext('Reset hitcounter') => 'resethitcounter'
											);
$checkarray_albums = array_merge($checkarray_images,
																	array(gettext('Add tags to images') => 'alltags',
																				gettext('Clear tags of images') => 'clearalltags')
																	);

/** EDIT ****************************************************************************/
/************************************************************************************/

if (isset($_GET['album']) && !isset($_GET['massedit'])) {
	/** SINGLE ALBUM ********************************************************************/
	// one time generation of this list.
	$mcr_albumlist = array();
	genAlbumUploadList($mcr_albumlist);

	$oldalbumimagesort = getOption('albumimagesort');
	$direction = getOption('albumimagedirection');
	if ($album->isDynamic()) {
		$subalbums = array();
		$allimages = array();
	} else {
		$subalbums = getNestedAlbumList($album, $subalbum_nesting);
		$allimages = $album->getImages(0, 0, $oldalbumimagesort, $direction);
		if (!($album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT)) {
			$allimages = array();
			$requestor = $_zp_current_admin_obj->getUser();
			$albumowner = $album->getOwner();
			if ($albumowner == $requestor) {
				$retunNull = '`owner` IS NULL OR ';
			} else {
				$retunNull = '';
			}
			$sql = 'SELECT * FROM '.prefix('images').' WHERE (`albumid`='.$album->id.') AND ('.$retunNull.' `owner`="'.$requestor.'") ORDER BY `'.$oldalbumimagesort.'` '.$direction;
			$result = query_full_array($sql);
			if (is_array($result)) {
				foreach ($result as $row) {
					$allimages[] = $row['filename'];
				}
			}
		}
	}
	$allimagecount = count($allimages);
	if (isset($_GET['tab']) && $_GET['tab']=='imageinfo' && isset($_GET['image'])) { // directed to an image
		$target_image = urldecode($_GET['image']);
		$imageno = array_search($target_image, $allimages);
		if ($imageno !== false) {
			$pagenum = ceil(($imageno+1) / $imagesTab_imageCount);
		}
	} else {
		$target_image = '';
	}
	if (!isset($pagenum)) {
		if (isset($_GET['subpage'])) {
			$pagenum = max(intval($_GET['subpage']),1);
			if (($pagenum-1) * $imagesTab_imageCount >= $allimagecount) $pagenum --;
		} else {
			$pagenum = 1;
		}
	}
	$images = array_slice($allimages, ($pagenum-1)*$imagesTab_imageCount, $imagesTab_imageCount);

	$totalimages = count($images);

	$parent = dirname($album->name);
	if (($parent == '/') || ($parent == '.') || empty($parent)) {
		$parent = '';
	} else {
		$parent = "&amp;album=" . pathurlencode($parent);
	}
	if (isset($_GET['counters_reset'])) {
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".gettext("Hitcounters have been reset")."</h2>";
		echo '</div>';
	}

if($album->getParent()) {
	$link = getAlbumBreadcrumbAdmin($album);
} else {
	$link = '';
}
$alb = removeParentAlbumNames($album);
?>
<h1><?php printf(gettext('Edit Album: <em>%1$s%2$s</em>'),  $link, $alb); ?></h1>


	<?php displayDeleted(); /* Display a message if needed. Fade out and hide after 2 seconds. */ ?>
	<?php
	if (isset($_GET['mismatch'])) {
		?>
		<div class="errorbox fade-message">
		<?php if ($_GET['mismatch'] == 'user') {
			echo '<h2>'.gettext("You must supply a  password.").'</h2>';
		} else {
			echo '<h2>'.gettext("Your passwords did not match.").'</h2>';
		}
		?>

		</div>
	<?php
	} else if (isset($_GET['mcrerr'])) {
		?>
		<div class="errorbox fade-message">
			<h2>
			<?php
			switch (sanitize_numeric($_GET['mcrerr'])) {
				case 2:
					echo  gettext("Image already exists.");
					break;
				case 3:
					echo  gettext("Album already exists.");
					break;
				case 4:
					echo  gettext("Cannot move, copy, or rename to a subalbum of this album.");
					break;
				case 5:
					echo  gettext("Cannot move, copy, or rename to a dynamic album.");
					break;
				case 6:
					echo	gettext('Cannot rename an image to a different suffix');
					break;
				case 7:
					echo gettext('Album delete failed');
					break;
				default:
					echo  gettext("There was an error with a move, copy, or rename operation.");
					break;
			}
			?>
			</h2>
		</div>
		<?php
	}
	if (isset($_GET['edit_error'])) {
		?>
		<div class="errorbox fade-message">
			<h2>
			<?php echo html_encode(sanitize($_GET['edit_error'])); ?>
			</h2>
		</div>
		<?php
	}
	if (isset($_GET['post_error'])) {
		?>
		<div class="errorbox fade-message">
			<h2>
			<?php echo gettext('The form post is incomplete.'); ?>
			</h2>
		</div>
		<?php
	}
	if (isset($_GET['saved'])) {
		?>
		<div class="messagebox fade-message">
			<h2>
			<?php echo gettext("Changes applied") ?>
			</h2>
		</div>
	<?php
	}


	if (isset($_GET['uploaded'])) {
		?>
		<div class="messagebox fade-message">
			<h2><?php echo gettext("Upload complete"); ?></h2>
			<?php echo zp_apply_filter('get_upload_header_text',gettext('Your files have been uploaded.')); ?>
		</div>
		<?php
	}
	if (isset($_GET['cleared'])) {
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".gettext("Album cache purged")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['exists'])) {
		echo '<div class="errorbox fade-message">';
		echo  "<h2>".sprintf(gettext("<em>%s</em> already exists."),sanitize($_GET['exists']))."</h2>";
		echo '</div>';
	}
	if (isset($_GET['bulkmessage'])) {
		$action = sanitize($_GET['bulkmessage']);
		switch($action) {
			case 'deleteall':
				$message = gettext('Selected items deleted');
				break;
			case 'showall':
				$message = gettext('Selected items published');
				break;
			case 'hideall':
				$message = gettext('Selected items unpublished');
				break;
			case 'commentson':
				$message = gettext('Comments enabled for selected items');
				break;
			case 'commentsoff':
				$message = gettext('Comments disabled for selected items');
				break;
			case 'resethitcounter':
				$message = gettext('Hitcounter for selected items');
				break;
		}
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".$message."</h2>";
		echo '</div>';
	}
	$subtab = printSubtabs();
	if ($subtab == 'albuminfo') {
	?>
		<!-- Album info box -->
		<div id="tab_albuminfo" class="tabbox">
			<form name="albumedit1" autocomplete="off" action="?page=edit&amp;action=save<?php echo "&amp;album=" . pathurlencode($album->name); ?>"	method="post">
				<?php XSRFToken('albumedit');?>
				<input type="hidden" name="album"	value="<?php echo $album->name; ?>" />
				<input type="hidden"	name="savealbuminfo" value="1" />
				<?php printAlbumEditForm(0, $album, true); ?>
			</form>
			<br clear="all" />
			<hr />

			<?php printAlbumButtons($album); ?>

		</div>
		<?php
	} else if ($subtab == 'subalbuminfo' && !$album->isDynamic())  {
		?>
		<!-- Subalbum list goes here -->
		<?php
		if (count($subalbums) > 0) {
			$enableEdit = $album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT;
			?>
		<div id="tab_subalbuminfo" class="tabbox">
		<?php printEditDropdown('subalbuminfo'); ?>
		<form action="?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>&amp;action=savesubalbumorder&amp;tab=subalbuminfo" method="post" name="sortableListForm" id="sortableListForm" onsubmit="return confirmAction();">
			<?php XSRFToken('savealbumorder'); ?>
			<p>
			<?php
				$sorttype = strtolower($album->getAlbumSortType());
				if ($sorttype != 'manual') {
					if ($album->getSortDirection('album')) {
						$dir = gettext(' descending');
					} else {
						$dir = '';
					}
					$sortNames = array_flip($sortby);
					$sorttype = $sortNames[$sorttype];
				} else {
					$dir = '';
				}
				printf(gettext('Current sort: <em>%1$s%2$s</em>. '), $sorttype, $dir);
				?>
			</p>
			<p>
				<?php	echo gettext('Drag the albums into the order you wish them displayed.'); ?>
			</p>
			<p class="notebox">
				<?php echo gettext('<strong>Note:</strong> Dragging an album under a different parent will move the album. You cannot move albums under a <em>dynamic</em> album.'); ?>
			</p>
			<?php
			if ($enableEdit) {
			?>
				<p>
					<?php	printf(gettext('Select an album to edit its description and data, or <a href="?page=edit&amp;album=%s&amp;massedit">mass-edit</a> all first level subalbums.'),pathurlencode($album->name)); ?>
				</p>
			<?php
			}
			?>
			<span class="buttons">
				<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<?php
				if ($enableEdit) {
					?>
					<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>" class="buttons">
						<img src="images/pass.png" alt="" />
						<strong><?php echo gettext("Apply"); ?></strong>
					</button>
					<div class="floatright">
					<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>',false);">
						<img src="images/folder.png" alt="" />
						<strong><?php echo gettext('New subalbum'); ?></strong>
					</button>
					</div>
					<?php
				}
				?>
			</span>
			<br clear="all" /><br />
			<div class="bordered">
			<div class="headline" style="text-align: left;"><?php echo gettext("Edit this album"); ?>
			<?php
			if ($enableEdit) {
				printBulkActions($checkarray_albums);
			}
			?>
			</div>
		 <div class="subhead">
			<label class="buttons" style="float: left">
				<a href="admin-edit.php?page=edit&album=<?php echo pathurlencode($album->name); ?>&tab=subalbuminfo&showthumbs=<?php echo $thumbshow ?>" title="<?php echo gettext('Thumbnail generation may be time consuming on slow servers on when there are a lot of images.'); ?>">
					<?php echo $thumbmsg; ?>
				</a>
			</label>
			<?php
			if ($enableEdit) {
				?>
				<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
				</label>
				<?php
			}
			?>
		</div>

			<ul class="page-list">
			<?php
			printNestedAlbumsList($subalbums, $showthumb);
			?>
			</ul>

		</div>
		 <br clear="all" /><br clear="all" />
			<?php printAlbumLedgend(); ?>
					<span id="serializeOutput" /></span>
					</div>
					<input name="update" type="hidden" value="Save Order" />
					<br />
					<span class="buttons">
					<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
					</a>
					<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>" class="buttons">
					<img src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
					</button>
					<div class="floatright">
					<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>',false);">
					<img src="images/folder.png" alt="" />
					<strong><?php echo gettext('New subalbum'); ?></strong>
					</button>
					</div>
					</span>
				</form>
				<br clear="all" />
		</div><!-- subalbum -->
		<?php
		}
		?>
	<?php
	} else if ($subtab == 'imageinfo') {
		?>
		<!-- Images List -->
		<div id="tab_imageinfo" class="tabbox">
		<?php
		$numsteps = ceil(max($allimagecount,$imagesTab_imageCount)/10);
		if ($numsteps) {
			$steps = array();
			for ($i=1;$i<=$numsteps;$i++) {
				$steps[] = $i*10;
			}
			?>
			<div style="padding-bottom:10px;">
				<?php printEditDropdown('imageinfo',$steps); ?>
			</div>
			<br style='clear:both'/>
			<?php
		}
		if ($allimagecount) {
			?>
		<form name="albumedit2"	action="?page=edit&amp;action=save<?php echo "&amp;album=" . pathurlencode($album->name); ?>"	method="post" autocomplete="off">
			<?php XSRFToken('albumedit'); ?>
			<input type="hidden" name="album"	value="<?php echo $album->name; ?>" />
			<input type="hidden" name="totalimages" value="<?php echo $totalimages; ?>" />
			<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum); ?>" />
			<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
			<input type="hidden" name="oldalbumimagesort" value="<?php echo html_encode($oldalbumimagesort); ?>" />

		<?php	$totalpages = ceil(($allimagecount / $imagesTab_imageCount));	?>
		<table class="bordered">
			<tr>
				<td><?php echo gettext("Click on the image to change the thumbnail cropping."); ?>	</td>
				<td>
				<a href="javascript:toggleExtraInfo('','image',true);"><?php echo gettext('expand all fields');?></a>
					| <a href="javascript:toggleExtraInfo('','image',false);"><?php echo gettext('collapse all fields');?></a>
				</td>
				<td align="right">
					<?php
					$sort = $sortby;
					foreach ($sort as $key=>$value) {
						$sort[sprintf(gettext('%s (descending)'),$key)] = $value.'_desc';
					}
					$sort[gettext('Manual')] = 'manual';
					ksort($sort,SORT_LOCALE_STRING);
					if ($direction) $oldalbumimagesort = $oldalbumimagesort.'_desc';
					echo gettext("Display images by:");
					echo '<select id="albumimagesort" name="albumimagesort" onchange="this.form.submit()">';
					generateListFromArray(array($oldalbumimagesort), $sort, false, true);
					echo '</select>';
					?>
				</td>
			</tr>
			<?php
			if ($allimagecount != $totalimages) { // need pagination links
			?>
			<tr>
				<td colspan="4" class="bordered" id="imagenav"><?php adminPageNav($pagenum,$totalpages,'admin-edit.php','?page=edit&amp;tagsort='.$tagsort.'&amp;album='.pathurlencode($album->name),'&amp;tab=imageinfo'); ?>
				</td>
			</tr>
			<?php
			}
		 ?>
			<tr>
				<td colspan="4">
					<p class="buttons">
						<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
						<img	src="images/arrow_left_blue_round.png" alt="" />
						<strong><?php echo gettext("Back"); ?></strong>
						</a>
						<button type="submit" title="<?php echo gettext("Apply"); ?>">
						<img src="images/pass.png" alt="" />
						<strong><?php echo gettext("Apply"); ?></strong>
						</button>
						<button type="reset" title="<?php echo gettext("Reset"); ?>">
						<img src="images/fail.png" alt="" />
						<strong><?php echo gettext("Reset"); ?></strong>
						</button>
				 </p>

				<?php printBulkActions($checkarray_images,true); ?>


				</td>
			</tr>
			<?php
			$bglevels = array('#fff','#f8f8f8','#efefef','#e8e8e8','#dfdfdf','#d8d8d8','#cfcfcf','#c8c8c8');

			$currentimage = 0;
			if (getOption('auto_rotate')) {
				$disablerotate = '';
			} else {
				$disablerotate = ' disabled="disabled"';
			}
			$target_image_nr = '';
			$thumbnail = $album->get('thumb');
			foreach ($images as $filename) {
				$image = newImage($album, $filename);
				?>

			<tr <?php echo ($currentimage % 2 == 0) ?  "class=\"alt\"" : ""; ?>>
			<?php
				if ($target_image == $filename) {
					$placemark = 'name="IT" ';
					$target_image_nr = $currentimage;
				} else {
					$placemark = '';
				}
			?>
				<td colspan="4">
				<input type="hidden" name="<?php echo $currentimage; ?>-filename"	value="<?php echo $image->filename; ?>" />
				<table border="0" class="formlayout" id="image-<?php echo $currentimage; ?>">
					<tr>
						<td valign="top" width="150" rowspan="17">

						<a <?php echo $placemark; ?>href="admin-thumbcrop.php?a=<?php echo pathurlencode($album->name); ?>&amp;i=<?php echo urlencode($image->filename); ?>&amp;subpage=<?php echo $pagenum; ?>&amp;tagsort=<?php echo $tagsort; ?>"
										title="<?php printf(gettext('crop %s'), $image->filename); ?>"  >
							<img
								id="thumb_img-<?php echo $currentimage; ?>"
								src="<?php echo $image->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, -1); ?>"
								alt="<?php printf(gettext('crop %s'), $image->filename); ?>"
								title="<?php printf(gettext('crop %s'), $image->filename); ?>"
								/>
						</a>
						<?php if(isImagePhoto($image)) { ?>
							<p class="buttons"><a href="<?php echo pathurlencode($image->getFullImage()); ?>" class="colorbox"><img src="images/magnify.png" alt="" /><strong><?php echo gettext('Zoom'); ?></strong></a></p><br style="clear: both" />
						<?php } ?>
						<p class="buttons"><a href="<?php echo $image->getImageLink();?>" title="<?php echo gettext('View image on website'); ?>"><img src="images/view.png" alt="" /><strong><?php echo gettext('View'); ?></strong></a></p><br style="clear: both" />
						<p><?php echo gettext('<strong>Filename:</strong>'); ?><br /><?php echo $image->filename; ?></p>
						<p><?php echo gettext('<strong>Image id:</strong>'); ?> <?php echo $image->get('id'); ?></p>
						<p><?php echo gettext("<strong>Dimensions:</strong>"); ?><br /><?php echo $image->getWidth(); ?> x  <?php echo $image->getHeight().' '.gettext('px'); ?></p>
						<p><?php echo gettext("<strong>Size:</strong>"); ?><br /><?php echo byteConvert($image->getImageFootprint()); ?></p>
						<?php
						if ($album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT) {
							?>
							<p>
								<label>
									<input type="radio" id="thumb-<?php echo $currentimage; ?>" name="thumb" value="<?php echo $currentimage+1; ?>"<?php if ($thumbnail == $image->filename) echo ' checked="checked"'?> />
									<?php echo ' '.gettext("Album thumbnail."); ?>
								</label>
							</p>
							<?php
						}
						?>
						</td>
						<td align="left" valign="top" width="100"><?php echo gettext("Owner:"); ?></td>
						<td>
							<?php
							if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
								?>
								<select name="<?php  echo $currentimage; ?>-owner">
									<?php  echo admin_album_list($image->getOwner()); ?>
								</select>
								<?php
							} else {
								echo $image->getOwner();
							}
							?>
						</td>
						<td style="padding-left: 1em; text-align: left;" rowspan="14" valign="top">
						<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
							<div class="box-edit">
								<label class="checkboxlabel">
									<input type="checkbox" id="Visible-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-Visible" value="1" <?php if ($image->getShow()) echo ' checked="checked"';	?> />
									<?php echo gettext("Published");?>
								</label>
								<label class="checkboxlabel">
									<input type="checkbox" id="allowcomments-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-allowcomments" value="1" <?php if ($image->getCommentsAllowed()) { echo ' checked="checked"'; } ?> />
									<?php echo gettext("Allow Comments"); ?>
								</label>
								<?php
								$hc = $image->get('hitcounter');
								if (empty($hc)) { $hc = '0'; }
								?>
								<label class="checkboxlabel">
									<input type="checkbox" name="<?php echo $currentimage; ?>reset_hitcounter" />
									<?php echo sprintf(gettext("Reset hitcounter (%u hits)"), $hc); ?>
								</label>
								<?php
								$tv = $image->get('total_value');
								$tc = $image->get('total_votes');

								if ($tc > 0) {
									$hc = $tv/$tc;
									?>
									<label class="checkboxlabel">
										<input type="checkbox" id="reset_rating-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-reset_rating" value="1" />
										<?php printf(gettext('Reset rating (%u stars)'), $hc); ?>
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
								?>

							<br clear="all" />
						</div>

						<h2 class="h2_bordered_edit"><?php echo gettext("Utilities"); ?></h2>
						<div class="box-edit">
						<!-- Move/Copy/Rename this image -->
						<label class="checkboxlabel">
								<input type="radio" id="move-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="move"
									onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'movecopy');"  /> <?php echo gettext("Move");?>
						</label>
						<label class="checkboxlabel">
								<input type="radio" id="copy-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="copy"
									onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'movecopy');"  /> <?php echo gettext("Copy");?>
						</label>
						<label class="checkboxlabel">
								<input type="radio" id="rename-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="rename"
									onclick="toggleMoveCopyRename('<?php echo $currentimage; ?>', 'rename');"  /> <?php echo gettext("Rename File");?>

						</label>
						<label class="checkboxlabel">
								<input type="radio" id="Delete-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-MoveCopyRename" value="delete"
									onclick="image_deleteconfirm(this, '<?php echo $currentimage; ?>','<?php echo gettext("Are you sure you want to select this image for deletion?"); ?>')" /> <?php echo gettext("Delete image") ?>
						</label>
						<div id="movecopydiv-<?php echo $currentimage; ?>"
							style="padding-top: .5em; padding-left: .5em; display: none;"><?php echo gettext("to"); ?>:
						<select id="albumselectmenu-<?php echo $currentimage; ?>"
							name="<?php echo $currentimage; ?>-albumselect" onchange="">
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
								echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: '.$bglevels[$salevel].';"' : '')
								. "$selected>". $saprefix . $singlefolder ."</option>\n";
							}
						?>
						</select>
						<br /><p class="buttons"><a href="javascript:toggleMoveCopyRename('<?php echo $currentimage; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a>
						</p>
						</div>
						<div id="renamediv-<?php echo $currentimage; ?>" style="padding-top: .5em; padding-left: .5em; display: none;"><?php echo gettext("to"); ?>:
						<input name="<?php echo $currentimage; ?>-renameto" type="text" value="<?php echo $image->filename;?>" /><br />
						<br /><p class="buttons"><a	href="javascript:toggleMoveCopyRename('<?php echo $currentimage; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a>
						</p>
						</div>
						<span style="line-height: 0em;"><br clear="all" /></span>
						<div id="deletemsg<?php echo $currentimage; ?>"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
						<?php echo gettext('Image will be deleted when changes are applied.'); ?>
						<p class="buttons"><a	href="javascript:toggleMoveCopyRename('<?php echo $currentimage; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a></p>
						</div>
						<span style="line-height: 0em;"><br clear="all" /></span>

						<?php
						if (isImagePhoto($image)) {
							?>
							<hr />
							<?php echo gettext("Rotation:"); ?>
							<br />
							<?php
							$splits = preg_split('/!([(0-9)])/', $image->get('EXIFOrientation'));
							$rotation = $splits[0];
							if (!in_array($rotation,array(3, 6, 8))) $rotation = 0;
							?>
							<input type="hidden" name="<?php echo $currentimage; ?>-oldrotation" value="<?php echo $rotation; ?>" />
							<label class="checkboxlabel">
									<input type="radio" id="rotation_none-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="0" <?php checked(0, $rotation); echo $disablerotate ?> />
									<?php echo gettext('none'); ?>
							</label>
							<label class="checkboxlabel">
									<input type="radio" id="rotation_90-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="8" <?php checked(8, $rotation); echo $disablerotate ?> />
									<?php echo gettext('90 degrees'); ?>
							</label>
							<label class="checkboxlabel">
									<input type="radio" id="rotation_180-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="3" <?php checked(3, $rotation); echo $disablerotate ?> />
									<?php echo gettext('180 degrees'); ?>
							</label>
							<label class="checkboxlabel">
									<input type="radio" id="rotation_270-<?php echo $currentimage; ?>"	name="<?php echo $currentimage; ?>-rotation" value="6" <?php checked(6, $rotation); echo $disablerotate ?> />
									<?php echo gettext('270 degrees'); ?>
							</label>
							<?php
						}
						?>
						<br clear="all" />
						<hr />
						<p class="buttons" style="clear: both">
							<a href="admin-thumbcrop.php?a=<?php echo pathurlencode($album->name); ?>&amp;i=<?php echo urlencode($image->filename); ?>&amp;subpage=<?php echo $pagenum; ?>&amp;tagsort=<?php echo $tagsort; ?>"
										title="<?php printf(gettext('crop %s'), $image->filename); ?>"  >
								<img src="images/shape_handles.png" alt="" /><?php echo gettext("Crop thumbnail"); ?>
							</a>
						</p>
						<span style="line-height: 0em;"><br clear="all" /></span>
						<?php
						echo zp_apply_filter('edit_image_utilities', '<!--image-->', $image, $currentimage, $pagenum, $tagsort); //pass space as HTML because there is already a button shown for cropimage
						?>
						<span style="line-height: 0em;"><br clear="all" /></span>
						</div>

						<h2 class="h2_bordered_edit imageextrainfo" style="display: none"><?php echo gettext("Tags"); ?></h2>
						<div class="box-edit-unpadded imageextrainfo" style="display: none">
							<?php	tagSelector($image, 'tags_'.$currentimage.'-', false, $tagsort);	?>
						</div>

						</td>
						<td>
							<div class="page-list_icon">
								<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $image->getFileName(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
							</div>
						</td>
					</tr>
					<tr>
						<td align="left" valign="top" width="100"><?php echo gettext("Title:"); ?></td>
						<td><?php print_language_string_list($image->get('title'), $currentimage.'-title'); ?>
					</tr>

					<tr>
						<td align="left" valign="top"><?php echo gettext("Description:"); ?></td>
						<td><?php print_language_string_list($image->get('desc'), $currentimage.'-desc', true, NULL, 'texteditor'); ?></td>
					</tr>


					<tr align="left" valign="top">
						<td valign="top"><?php echo gettext("Date:"); ?></td>
						<td>
							<script type="text/javascript">
								// <!-- <![CDATA[
								$(function() {
									$("#datepicker_<?php echo $currentimage; ?>").datepicker({
													showOn: 'button',
													buttonImage: 'images/calendar.png',
													buttonText: '<?php echo gettext('calendar'); ?>',
													buttonImageOnly: true
													});
								});
								// ]]> -->
							</script>
							<input type="text" id="datepicker_<?php echo $currentimage; ?>" size="20" name="<?php echo $currentimage; ?>-date"
								value="<?php $d=$image->getDateTime(); if ($d!='0000-00-00 00:00:00') { echo $d; } ?>" />
						</td>
					</tr>

					<?php
					$current = $image->getWatermark();
					?>
					<tr>
						<td align="left" valign="top" width="150"><?php echo gettext("Image watermark:"); ?> </td>
						<td>
							<select id="image_watermark-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-image_watermark" onclick="javascript:toggleWMUse(<?php echo $currentimage; ?>);">
								<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current==NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
								<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
								<?php
								$watermarks = getWatermarks();
								generateListFromArray(array($current), $watermarks, false, false);
								?>
							</select>
							<span id="WMUSE_<?php echo $currentimage; ?>" style="display:<?php if ($current == '') echo 'none'; else echo 'inline';?>">
								<?php $wmuse = $image->getWMUse(); ?>
								<label><input type="checkbox" value="1" id="wm_image-<?php echo $currentimage; ?>" name="wm_image-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_IMAGE) echo 'checked="checeked"';?> /><?php echo gettext('image');?></label>
								<label><input type="checkbox" value="1" id="wm_thumb-<?php echo $currentimage; ?>" name="wm_thumb-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_THUMB) echo 'checked="checeked"';?> /><?php echo gettext('thumb');?></label>
								<label><input type="checkbox" value="1" id="wm_full-<?php echo $currentimage; ?>"name="wm_full-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_FULL) echo 'checked="checeked"';?> /><?php echo gettext('full image');?></label>
							</span>
						</td>
					</tr>
		<?php
					$custom = zp_apply_filter('edit_image_custom_data', '', $image, $currentimage);
					if (empty($custom)) {
						?>
						<tr>
							<td valign="top"><?php echo gettext("Custom data:"); ?></td>
							<td><?php print_language_string_list($image->get('custom_data'), $currentimage.'-custom_data', true,NULL,'texteditor_imagecustomdata'); ?></td>
						</tr>
						<?php
						} else {
							echo $custom;
						}
					?>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("Location:"); ?></td>
						<td><?php print_language_string_list($image->get('location'), $currentimage.'-location'); ?>
						</td>
					</tr>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("City:"); ?></td>
						<td><?php print_language_string_list($image->get('city'), $currentimage.'-city'); ?>
						</td>
					</tr>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("State:"); ?></td>
						<td><?php print_language_string_list($image->get('state'), $currentimage.'-state'); ?>
						</td>
					</tr>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("Country:"); ?></td>
						<td><?php print_language_string_list($image->get('country'), $currentimage.'-country'); ?>
						</td>
					</tr>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("Credit:"); ?></td>
						<td><?php print_language_string_list($image->get('credit'), $currentimage.'-credit'); ?>
						</td>
					</tr>

					<tr class="imageextrainfo" style="display: none">
						<td valign="top"><?php echo gettext("Copyright:"); ?></td>
						<td><?php print_language_string_list($image->get('copyright'), $currentimage.'-copyright'); ?>
						</td>
					</tr>
					<?php
					if ($image->get('hasMetadata')) {
						?>
						<tr class="imageextrainfo" style="display: none">
							<td valign="top"><?php echo gettext("Metadata:"); ?></td>
							<td>
							<?php
								$data = '';
								$exif = $image->getMetaData();
								if (false !== $exif) {
									foreach ($exif as $field => $value) {
										if (!empty($value)) {
											$display = $_zp_exifvars[$field][3];
											if ($display) {
												$label = $_zp_exifvars[$field][2];
												$data .= "<tr><td align=\"right\" >$label: </td> <td>$value</td></tr>\n";
											}
										}
									}
								}
								if (empty($data)) {
									echo gettext('None selected for display');
								} else {
									echo '<table class="metadata_table" >'.$data.'</table>';
								}
								?>
							</td>
						</tr>
						<tr valign="top" class="imageextrainfo" style="display: none">
							<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
							<td>
							<br />
								<div class="tabs">
									<ul class="tabNavigation">
										<li><a href="#first-<?php echo $image->get('id'); ?>"><?php echo gettext("Codeblock 1"); ?></a></li>
										<li><a href="#second-<?php echo $image->get('id'); ?>"><?php echo gettext("Codeblock 2"); ?></a></li>
										<li><a href="#third-<?php echo $image->get('id'); ?>"><?php echo gettext("Codeblock 3"); ?></a></li>
									</ul>
							<?php
									$getcodeblock = $image->getCodeblock();
									if(!empty($getcodeblock)) {
										$codeblock = unserialize($getcodeblock);
									} else {
										$codeblock[1] = "";
										$codeblock[2] = "";
										$codeblock[3] = "";
									}
									?>
									<div id="first-<?php echo $image->get('id'); ?>">
										<textarea name="codeblock1-<?php echo $currentimage;?>" id="codeblock1-<?php echo $image->get('id'); ?>" rows="40" cols="60"><?php echo html_encode($codeblock[1]); ?></textarea>
									</div>
									<div id="second-<?php echo $image->get('id'); ?>">
										<textarea name="codeblock2-<?php echo $currentimage;?>" id="codeblock2-<?php echo $image->get('id'); ?>" rows="40" cols="60"><?php echo html_encode($codeblock[2]); ?></textarea>
									</div>
									<div id="third-<?php echo $image->get('id'); ?>">
										<textarea name="codeblock3-<?php echo $currentimage;?>" id="codeblock3-<?php echo $image->get('id'); ?>" rows="40" cols="60"><?php echo html_encode($codeblock[3]); ?></textarea>
									</div>
								</div>
							</td>
							</tr>
						<?php
					}
					?>
					<tr>
						<td colspan="2">
						<span style="display: block" class="imageextrashow">
						<a href="javascript:toggleExtraInfo('<?php echo $currentimage;?>', 'image', true);"><?php echo gettext('show more fields');?></a></span>
						<span style="display: none" class="imageextrahide">
						<a href="javascript:toggleExtraInfo('<?php echo $currentimage;?>', 'image', false);"><?php echo gettext('show fewer fields');?></a></span>
						</td>
					</tr>


				</table>
				</td>
			</tr>

			<?php
			$currentimage++;
		}
		?>
			<tr <?php echo ($currentimage % 2 == 0) ?  "class=\"alt\"" : ""; ?>>
				<td colspan="4">

				<p class="buttons">
					<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
					</a>
					<button type="submit" title="<?php echo gettext("Apply"); ?>">
					<img src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
					</button>
					<button type="reset" title="<?php echo gettext("Reset"); ?>">
					<img src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
					</button>
				</p>

			</td>
			</tr>
		<?php
		if ($allimagecount != $totalimages) { // need pagination links
			?>
			<tr>
				<td colspan="4" class="bordered" id="imagenavb"><?php adminPageNav($pagenum,$totalpages,'admin-edit.php','?page=edit&amp;album='.pathurlencode($album->name),'&amp;tab=imageinfo'); ?>
				</td>
			</tr>
			<?php
			}
			if (!empty($target_image)) {
				?>
				<script language="Javascript" type="text/javascript" >
					// <!-- <![CDATA[
					toggleExtraInfo('<?php echo $target_image_nr;?>', 'image', true);
					// ]]> -->
				</script>
				<?php
			}
			?>

		</table>
		<input type="hidden" name="checkForPostTruncation" value="1" />
		</form>

		<?php
			}
		?>
		</div><!-- images -->
<?php
	}

if($subtab != "albuminfo") {	?>
<!-- page trailer -->

<?php }

/*** MULTI-ALBUM ***************************************************************************/

} else if (isset($_GET['massedit'])) {
	// one time generation of this list.
	$mcr_albumlist = array();
	genAlbumUploadList($mcr_albumlist);

if (isset($_GET['saved'])) {
		if (isset($_GET['mismatch'])) {
			echo "\n<div class=\"errorbox fade-message\">";
			echo "\n<h2>".gettext("Your passwords did not match")."</h2>";
			echo "\n</div>";
		} else {
			echo "\n<div class=\"messagebox fade-message\">";
			echo "\n<h2>".gettext("Changes applied")."</h2>";
			echo "\n</div>";
		}
	}
	$albumdir = "";
	if (isset($_GET['album'])) {
		$folder = sanitize_path($_GET['album']);
		$album = new Album($gallery, $folder);
		if ($album->isMyItem(ALBUM_RIGHTS)) {
			$albums = $album->getAlbums();
			$pieces = explode('/', $folder);
			$albumdir = "&album=" . pathurlencode($folder).'&tab=subalbuminfo';
		} else {
			$albums = array();
		}
	} else {
		$albumsprime = $gallery->getAlbums();
		$albums = array();
		foreach ($albumsprime as $folder) { // check for rights
			$album = new Album($gallery, $folder);
			if ($album->isMyItem(ALBUM_RIGHTS)) {
				$albums[] = $folder;
			}
		}
	}
	?>
<h1><?php echo gettext("Edit All Albums in"); ?> <?php if (!isset($_GET['album'])) { echo gettext("Gallery");} else {echo "<em>" . $album->name . "</em>";}?></h1>
<div class="tabbox">

<form name="albumedit" autocomplete="off"	action="?page=edit&amp;action=save<?php echo $albumdir ?>" method="POST">
	<?php XSRFToken('albumedit');?>
	<input type="hidden" name="totalalbums" value="<?php echo sizeof($albums); ?>" />
	<?php
	$currentalbum = 1;
	foreach ($albums as $folder) {
		$album = new Album($gallery, $folder);
		echo "\n<!-- " . $album->name . " -->\n";
		?>
		<div class="innerbox" style="padding: 15px;">
		<?php
		printAlbumEditForm($currentalbum, $album, true);
		$currentalbum++;
		?>
		</div>
		<br />
		<hr />
		<?php
	}
	?>
	</form>

</div>
<?php

/*** EDIT ALBUM SELECTION *********************************************************************/

} else { /* Display a list of albums to edit. */ ?>
<h1><?php echo gettext("Edit Gallery"); ?></h1>
<?php
	displayDeleted(); /* Display a message if needed. Fade out and hide after 2 seconds. */
	if (isset($_GET['counters_reset'])) {
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".gettext("Hitcounters have been reset.")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['action']) && $_GET['action'] == 'clear_cache') {
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".gettext("Cache has been purged.")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['exists'])) {
		echo '<div class="errorbox fade-message">';
		echo  "<h2>".sprintf(gettext("<em>%s</em> already exists."),sanitize($_GET['exists']))."</h2>";
		echo '</div>';
	}
	if(isset($_GET['commentson'])) {
		enableComments('album');
	}
if (isset($_GET['bulkmessage'])) {
		$action = sanitize($_GET['bulkmessage']);
		switch($action) {
			case 'deleteall':
				$message = gettext('Selected items deleted');
				break;
			case 'showall':
				$message = gettext('Selected items published');
				break;
			case 'hideall':
				$message = gettext('Selected items unpublished');
				break;
			case 'commentson':
				$message = gettext('Comments enabled for selected items');
				break;
			case 'commentsoff':
				$message = gettext('Comments disabled for selected items');
				break;
			case 'resethitcounter':
				$message = gettext('Hitcounter for selected items');
				break;
			case 'addtags':
				$message = gettext('Tags added selected items');
				break;
			case 'cleartags':
				$message = gettext('Tags cleared for selected items');
				break;
			case 'alltags':
				$message = gettext('Tags added for images of selected items');
				break;
			case 'clearalltags':
				$message = gettext('Tags cleared for images of selected items');
				break;
		}
		echo '<div class="messagebox fade-message">';
		echo  "<h2>".$message."</h2>";
		echo '</div>';
	}
	$albums = getNestedAlbumList(NULL, $gallery_nesting);
	if (count($albums) > 0) {
		if (zp_loggedin(ADMIN_RIGHTS) && (count($albums)) > 1) {
			$sorttype = strtolower(GALLERY_SORT_TYPE);
			if ($sorttype != 'manual') {
				if (GALLERY_SORT_DIRECTION) {
					$dir = gettext(' descending');
				} else {
					$dir = '';
				}
				$sortNames = array_flip($sortby);
				$sorttype = $sortNames[$sorttype];
			} else {
				$dir = '';
			}
			?>
			<p>
			<?php  printf(gettext('Current sort: <em>%1$s%2$s</em>.'), $sorttype, $dir); ?>
			</p>
			<p>
			<?php	echo gettext('Drag the albums into the order you wish them displayed.'); ?>
			</p>
			<p class="notebox">
			<?php	echo gettext('<strong>Note:</strong> Dragging an album under a different parent will move the album. You cannot move albums under a <em>dynamic</em> album.');?>
			</p>
			<?php
		}
		?>
	<p>
		<?php
		echo gettext('Select an album to edit its description and data, or <a href="?page=edit&amp;massedit">mass-edit</a> all gallery level albums.');
	?>
	</p>

	<?php
	printEditDropdown('');
	?>
<form action="?page=edit&amp;action=savealbumorder" method="post" name="sortableListForm" id="sortableListForm" onsubmit="return confirmAction();">
	<?php XSRFToken('savealbumorder');?>
	<p class="buttons">
		<?php
		if ($gallery_nesting>1 || zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			?>
			<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>" class="buttons"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
			<?php
		}
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			?>
			<button type="button" title="<?php echo gettext('New album'); ?>" onclick="javascript:newAlbum('', false);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong></button>
			<?php
		}
		?>
	</p>
	<br clear="all" /><br />
	<div class="bordered">
		<div class="headline"><?php echo gettext("Edit this album"); ?>
			<?php printBulkActions($checkarray_albums); ?>
		</div>
		<div class="subhead">
			<label class="buttons" style="float: left">
				<a href="admin-edit.php?showthumbs=<?php echo $thumbshow ?>" title="<?php echo gettext('Thumbnail generation may be time consuming on slow servers on when there are a lot of images.'); ?>">
					<?php echo $thumbmsg; ?>
				</a>
			</label>
			<label style="float: right">
				<?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
			</label>
		</div>

		<ul class="page-list">
		<?php printNestedAlbumsList($albums, $showthumb); ?>
		</ul>

	</div>
	<div>
		<?php printAlbumLedgend(); ?>
		</div>

	<span id="serializeOutput" /></span>
	<input name="update" type="hidden" value="Save Order" />
	<p class="buttons">
		<?php
		if ($gallery_nesting>1 || zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			?>
			<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>" class="buttons"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
			<?php
		}
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			?>
			<button type="button" title="<?php echo gettext('New album'); ?>" onclick="javascript:newAlbum('', false);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong></button>
			<?php
		}
		?>
	</p>

</form>
<br clear="all" />

<?php
	} else {
		echo gettext("There are no albums for you to edit.");
		?>
		<p class="buttons">
			<button type="button" title="<?php echo gettext('New album'); ?>" onclick="javascript:newAlbum('', false);"><img src="images/folder.png" alt="" /><strong><?php echo gettext('New album'); ?></strong></button>
		</p>
	<?php
	}
}
?>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
<?php // to fool the validator
echo "\n</html>";
?>
