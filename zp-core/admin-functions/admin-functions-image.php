<?php 
/**
 * Image related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */


/**
	 * Process the image edit form posted
	 * @param obj $image Image object
	 * @param type $index Index of the image if within the images list or 0 if single image edit
	 * @param boolean $massedit Whether editing single image (false) or multiple images at once (true). Note: to determine whether to process additional fields in single image edit mode.
	 */
	function processImageEdit($image, $index, $massedit = true) {
		global $_zp_current_admin_obj, $_zp_graphics;
		$notify = '';
		if (isset($_POST[$index . '-MoveCopyRename'])) {
			$movecopyrename_action = sanitize($_POST[$index . '-MoveCopyRename'], 3);
		} else {
			$movecopyrename_action = '';
		}
		if ($movecopyrename_action == 'delete') {
			$image->remove();
		} else {
			if ($thumbnail = sanitize($_POST['album_thumb-' . $index])) { //selected as an album thumb
				$talbum = AlbumBase::newAlbum($thumbnail);
				if ($image->imagefolder == $thumbnail) {
					$talbum->setThumb($image->filename);
				} else {
					$talbum->setThumb('/' . $image->imagefolder . '/' . $image->filename);
				}
				$talbum->setLastChangeUser($_zp_current_admin_obj->getLoginName());
				$talbum->save();
			}
			if (isset($_POST[$index . '-reset_rating'])) {
				$image->set('total_value', 0);
				$image->set('total_votes', 0);
				$image->set('used_ips', 0);
			}
			$image->setPublishDate(sanitize($_POST['publishdate-' . $index]));
			$image->setExpireDate(sanitize($_POST['expirationdate-' . $index]));
			$image->setTitle(process_language_string_save("$index-title", 2));
			$image->setDesc(process_language_string_save("$index-desc", EDITOR_SANITIZE_LEVEL));

			if (isset($_POST[$index . '-oldrotation']) && isset($_POST[$index . '-rotation'])) {
				$oldrotation = (int) $_POST[$index . '-oldrotation'];
				$rotation = (int) $_POST[$index . '-rotation'];
				if ($rotation != $oldrotation) {
					$image->set('EXIFOrientation', $rotation);
					$image->updateDimensions();
					$album = $image->getAlbum();
					Gallery::clearCache(SERVERCACHE . '/' . $album->name);
				}
			}

			if (!$massedit) {
				$image->setLocation(process_language_string_save("$index-location", 3));
				$image->setCity(process_language_string_save("$index-city", 3));
				$image->setState(process_language_string_save("$index-state", 3));
				$image->setCountry(process_language_string_save("$index-country", 3));
				$image->setCredit(process_language_string_save("$index-credit", 1));
				$image->setCopyright(process_language_string_save("$index-copyright", 1));
				$tagsprefix = 'tags_' . $index . '-';
				$tags = array();
				$l = strlen($tagsprefix);
				foreach ($_POST as $key => $value) {
					$key = postIndexDecode($key);
					if (substr($key, 0, $l) == $tagsprefix) {
						if ($value) {
							$tags[] = sanitize(substr($key, $l));
						}
					}
				}
				$tags = array_unique($tags);
				$image->setTags($tags);
				if (zp_loggedin(CODEBLOCK_RIGHTS)) {
					$image->setCodeblock(processCodeblockSave($index));
				}
				$custom = process_language_string_save("$index-custom_data", 1);
				$image->setCustomData(zp_apply_filter('save_image_custom_data', $custom, $index));
			}
			$image->setDateTime(sanitize($_POST["$index-date"]));
			$image->setPublished(isset($_POST["$index-Visible"]));
			$image->setCommentsAllowed(isset($_POST["$index-allowcomments"]));
			if (isset($_POST["reset_hitcounter$index"])) {
				$image->set('hitcounter', 0);
			}
			$wmt = sanitize($_POST["$index-image_watermark"], 3);
			$image->setWatermark($wmt);
			$wmuse = 0;
			if (isset($_POST['wm_image-' . $index])) {
				$wmuse = $wmuse | WATERMARK_IMAGE;
			}
			if (isset($_POST['wm_thumb-' . $index])) {
				$wmuse = $wmuse | WATERMARK_THUMB;
			}
			if (isset($_POST['wm_full-' . $index])) {
				$wmuse = $wmuse | WATERMARK_FULL;
			}
			$image->setWMUse($wmuse);

			if (isset($_POST[$index . '-owner'])) {
				$image->setOwner(sanitize($_POST[$index . '-owner']));
			}
			$image->set('filesize', filesize($image->localpath));
			$image->setLastchangeUser($_zp_current_admin_obj->getLoginName());
			zp_apply_filter('save_image_utilities_data', $image, $index);
			$image->save(true);

			// Process move/copy/rename
			$mcrerr = array();
			$folder = $image->getAlbumName();
			if ($movecopyrename_action == 'move') {
				$dest = sanitize_path($_POST[$index . '-albumselect']);
				if ($dest && $dest != $folder) {
					if ($e = $image->move($dest)) {
						SearchEngine::clearSearchCache();
						$mcrerr['mcrerr'][$e][$index] = $image->getID();
					}
				} else {
					// Cannot move image to same album.
					$mcrerr['mcrerr'][2][$index] = $image->getID();
				}
			} else if ($movecopyrename_action == 'copy') {

				$dest = sanitize_path($_POST[$index . '-albumselect']);
				if ($dest && $dest != $folder) {
					if ($e = $image->copy($dest)) {
						$mcrerr['mcrerr'][$e][$index] = $image->getID();
					}
				} else {
					// Cannot copy image to existing album.
					// Or, copy with rename?
					$mcrerr['mcrerr'][2][$index] = $image->getID();
				}
			} else if ($movecopyrename_action == 'rename') {
				$renameto = sanitize_path($_POST[$index . '-renameto']);
				if ($e = $image->rename($renameto)) {
					SearchEngine::clearSearchCache();
					$mcrerr['mcrerr'][$e][$index] = $image->getID();
				}
			}
		}
		if (!empty($mcrerr)) {
			$notify = '&' . http_build_query($mcrerr);
		}
		return $notify;
	}
	
	/**
 * Handles Image bulk actions
 * @param $album
 */
function processImageBulkActions($album) {
	global $_zp_current_admin_obj;
	$mcrerr = array();
	$action = sanitize($_POST['checkallaction']);
	$ids = sanitize($_POST['ids']);
	$total = count($ids);
	if ($action != 'noaction') {
		if ($total > 0) {
			if ($action == 'addtags') {
				$tags = bulkTags();
			}
			if ($action == 'moveimages' || $action == 'copyimages') {
				$dest = sanitize($_POST['massalbumselect']);
				$folder = sanitize($_POST['massfolder']);
				if (!$dest || $dest == $folder) {
					return "&mcrerr=2";
				}
			}
			if ($action == 'changeowner') {
				$newowner = sanitize($_POST['massownerselect']);
			}
			$n = 0;
			foreach ($ids as $filename) {
				$n++;
				$imageobj = Image::newImage($album, $filename);
				switch ($action) {
					case 'deleteall':
						$imageobj->remove();
						SearchEngine::clearSearchCache();
						break;
					case 'showall':
						$imageobj->set('show', 1);
						break;
					case 'hideall':
						$imageobj->set('show', 0);
						break;
					case 'commentson':
						$imageobj->set('commentson', 1);
						break;
					case 'commentsoff':
						$imageobj->set('commentson', 0);
						break;
					case 'resethitcounter':
						$imageobj->set('hitcounter', 0);
						break;
					case 'addtags':
						$mytags = array_unique(array_merge($tags, $imageobj->getTags()));
						$imageobj->setTags($mytags);
						break;
					case 'cleartags':
						$imageobj->setTags(array());
						break;
					case 'copyimages':
						if ($e = $imageobj->copy($dest)) {
							$mcrerr['mcrerr'][$e][] = $imageobj->getID();
						}
						break;
					case 'moveimages':
						if ($e = $imageobj->move($dest)) {
							$mcrerr['mcrerr'][$e][] = $imageobj->getID();
						}
						break;
					case 'changeowner':
						$imageobj->setOwner($newowner);
						break;
					default:
						callUserFunction($action, $imageobj);
						break;
				}
				$imageobj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
				$imageobj->save(true);
			}
		}
		if (!empty($mcrerr)) {
			$action .= '&' . http_build_query($mcrerr);
		}
		return $action;
	}
}

/**
 * Prints the image EXIF rotation/flipping selector
 * 
 * @since 1.6.1
 * 
 * @param obj $imageobj Object of the current image
 * @param int $currentimage ID of the current image
 */
function printImageRotationSelector($imageobj, $currentimage) {
	$rotation = extractImageExifOrientation($imageobj->get('EXIFOrientation'));
	if ($rotation > 8 || $rotation < 1) {
		$rotation = 1;
	}
	$list = array(
			gettext('Horizontal (normal)') => 1,
			gettext('Mirror horizontal') => 2,
			gettext('Rotate 180 clockwise') => 3,
			gettext('Mirror vertical') => 4,
			gettext('Mirror horizontal and rotate 270 clockwise') => 5,
			gettext('Rotate 90 clockwise') => 6,
			gettext('Mirror horizontal and rotate 90 clockwise') => 7,
			gettext('Rotate 270 clockwise') => 8
	);
	?>
	<hr />
	<strong><?php echo gettext("Rotation:"); ?></strong>
	<br />
	<input type="hidden" name="<?php echo $currentimage; ?>-oldrotation" value="<?php echo $rotation; ?>" />
	<select id="rotation-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-rotation">
		<?php generateListFromArray((array) $rotation, $list, null, true); ?>
	</select>
	<?php
}
