<?php
/**
 * Manage the timing of publishing new content
 *
 * This plugin allows you to change the default setting of the albums: published and
 * the images: visible fields.
 *
 * It also allows you to list un-published albums and not visible images from before a
 * specific data and time. You can select albums and images from these lists to be published.
 * NOTE: currently there is no record of when albums were first encountered, so all un-published
 * albums are show.
 *
 * So you can freely upload albums and images then on a periodic basis review which ones to make available
 * to visitors of your gallery.
 *
 * @package admin
 */

define('OFFSET_PATH', 3);
chdir(dirname(dirname(__FILE__)));

require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(dirname(dirname(__FILE__)).'/template-functions.php');

$button_text = gettext('Publish content');
$button_hint = gettext('Manage un-published content in your gallery.');
$button_icon = 'images/calendar.png';
$button_rights = ALBUM_RIGHTS;

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL(__FILE__));

$gallery = new Gallery();

printAdminHeader(gettext('utilities'),gettext('content'));
datepickerJS();
?>
<link rel="stylesheet" href="schedule_content.css" type="text/css" media="screen" />
<?php
function unpublishSubalbums($album) {
	global $gallery;
	$albums = $album->getAlbums();
	foreach ($albums as $albumname) {
		$subalbum = new Album($gallery, $albumname);
		$subalbum->setShow(false);
		$subalbum->save();
		unpublishSubalbums($subalbum);
	}
}

$publish_albums_list = array();
$publish_images_list = array();
if (db_connect()) {
	if (isset($_POST['set_defaults'])) {
		XSRFdefender('schedule_content');
		if (isset($_POST['album_default'])) {
			$albpublish = 1;
		} else {
			$albpublish = 0;
		}
		setOption('album_publish', $albpublish);
		if (isset($_POST['image_default'])) {
			$imgpublish = 1;
		} else {
			$imgpublish = 0;
		}
		setOption('image_publish', $imgpublish);
	} else if (isset($_POST['publish_albums'])) {
		XSRFdefender('schedule_content');
		$sql = '';
		unset($_POST['publish_albums']);
		foreach ($_POST as $key=>$albumid) {
			$key = sanitize_numeric(str_replace('sched_', '', $key));
			if (is_numeric($key)) {
				$sql .= '`id`="'.sanitize_numeric($key).'" OR ';
			}
		}
		if (!empty($sql)) {
			$sql = substr($sql, 0, -4);
			$sql = 'UPDATE '.prefix('albums').' SET `show`="1" WHERE '.$sql;
			query($sql);
		}
	} else if (isset($_POST['publish_images'])) {
		XSRFdefender('schedule_content');
		unset($_POST['publish_images']);
		$sql = '';
		foreach ($_POST as $action) {
			$i = strrpos($action,'_');
			$imageid = sanitize_numeric(substr($action,$i+1));
			switch(substr($action,0,$i)) {
				case 'pub':
					if (is_numeric($imageid)) $sql .= '`id`="'.$imageid.'" OR ';
					break;
				case 'del':
					$rowi = query_single_row('SELECT * FROM '.prefix('images').' WHERE `id`='.$imageid);
					$rowa = query_single_row('SELECT * FROM '.prefix('albums').' WHERE `id`='.$rowi['albumid']);
					$album = new Album($gallery, $rowa['folder']);
					$image = newImage($album, $rowi['filename']);
					$image->remove();
					break;
			}
		}
		if (!empty($sql)) {
			$sql = substr($sql, 0, -4);
			$sql = 'UPDATE '.prefix('images').' SET `show`="1" WHERE '.$sql;
			query($sql);
		}
	}

	echo '</head>';
	?>

	<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
	<?php printTabs(); ?>
	<div id="content">
	<?php zp_apply_filter('admin_note','schedule', ''); ?>
	<h1><?php echo (gettext('Manage content publication')); ?></h1>

	<h3><?php gettext("database connected"); ?></h3>
	<br />
	<?php
	$albpublish = getOption('album_publish');
	$imgpublish = getOption('image_publish');
	if (isset($_POST['publish_date']))	{
		$requestdate = dateTimeConvert(sanitize($_POST['publish_date']));
	} else {
		$requestdate = date('Y-m-d H:i:s');
	}

	$albumidlist = '';
	$albumids = '';
	if (zp_loggedin(ADMIN_RIGHTS)) {
		$albumlist = $gallery->getAlbums();
	} else {
		$albumlist = getManagedAlbumList();
		$albumIDs = array();
		foreach ($albumlist as $albumname) {
			$subalbums = getAllSubAlbumIDs($albumname);
			foreach($subalbums as $ID) {
				$albumIDs[] = $ID['id'];
			}
		}
		$i = 0;
		foreach ($albumIDs as $ID) {
			if ($i>0) {
				$albumidlist .= ' OR ';
				$albumids .= ' OR ';
			}
			$albumidlist .= prefix('images').'.albumid='.$ID;
			$albumids .= '`id`='.$ID;
			$i++;
		}
		if (!empty($albumlist)) {
			$albumids = ' AND ('.$albumids.')';
			$albumidlist = ' AND ('.$albumidlist.')';
		}
	}
	if (isset($_GET['propagate_unpublished'])) {
		foreach ($albumlist as $albumname) {
			$album = new Album($gallery, $albumname);
			if (!$album->getShow()) {
				unpublishSubalbums($album);
			}
		}
	}

	$mtime = dateTimeConvert($requestdate, true);
	$sql = "SELECT `folder`, `id` FROM ".prefix('albums').' WHERE `show`="0"'.$albumids;
	$result = query_full_array($sql);
	if (is_array($result)) {
		foreach ($result as $row) {
			$publish_albums_list[$row['folder']] = $row['id'];
		}
	}
	$sql = 'SELECT `filename`, '.prefix('images').'.id as id, folder FROM '.prefix('images').','.prefix('albums').' WHERE '.
					prefix('images').'.show="0" AND '.prefix('images').'.mtime < "'.$mtime.'" AND '.prefix('albums').'.id='.
					prefix('images').'.albumid'.$albumidlist;
	$result = query_full_array($sql);
	if (is_array($result)) {
		foreach ($result as $row) {
			$publish_images_list[$row['folder']][$row['filename']] = $row['id'];
		}
		ksort($publish_images_list,SORT_LOCALE_STRING);
	}
	?>
<?php
if (zp_loggedin(ADMIN_RIGHTS)) {
	echo gettext('Publishing options');
	?>
	<div class="smallbox">
		<form name="set_publication" action="" method="post">
			<?php XSRFToken('schedule_content');?>
			<input type="hidden" name="set_defaults" value="true" />
			<input type="checkbox" name="album_default"	value="1"<?php if ($albpublish) echo ' checked="checked"'; ?> /> <?php echo gettext("Publish albums by default"); ?>
			<br clear="all" />
			<br clear="all" />
			<input type="checkbox" name="image_default"	value="1"<?php if ($imgpublish) echo ' checked="checked"'; ?> /> <?php echo gettext("Publish images by default"); ?>
			<br clear="all" />
			<br clear="all" />
			<div class="buttons pad_button" id="setdefaults">
				<button class="tooltip" type="submit" title="<?php echo gettext("Set defaults for album publishing and image visibility."); ?>">
					<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/burst1.png" alt="" /> <?php echo gettext("Apply"); ?>
				</button>
			</div>
		</form>
		<br clear="all" />
	</div>
	<br clear="all" />
	<br clear="all" />
	<?php
	}
	echo gettext('Un-published albums');
	?>
	<div class="smallbox">
	<?php
	if (count($publish_albums_list) > 0) {
		?>
		<form name="publish" action="" method="post"><?php echo gettext('Albums:'); ?>
		<label id="autocheck">
			<input type="checkbox" name="checkAllAuto" id="checkAllAuto" />
			<span id="autotext"><?php echo gettext('all');?></span>
		</label>
		<script type="text/javascript">
			// <!-- <![CDATA[
			var checked = false;
			$('#autocheck').click(
			   function() {
			      if (checked) {
				      checked = false;
			      } else {
				      checked = 'checked';
			      }
			      $('.albumcheck').attr('checked', checked);
			   }
			)
			// ]]> -->
		</script>
		<?php XSRFToken('schedule_content');?>
		<input type="hidden" name="publish_albums" value="true" />
		<ul class="schedulealbumchecklist">
		<?php	generateUnorderedListFromArray(array(), $publish_albums_list, 'sched_', false, true, true, 'albumcheck'); ?>
		</ul>
		<br clear="all" />

		<div class="buttons pad_button" id="publishalbums">
		<button class="tooltip" type="submit" title="<?php echo gettext("Publish waiting albums."); ?>">
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/cache1.png" alt="" /> <?php echo gettext("Publish albums"); ?>
		</button>
		</div>
		<br clear="all" />
		</form>
		<p class="buttons">
			<a href="?propagate_unpublished" title="<?php echo gettext('Set all subalbums of an un-published album to un-published.'); ?>">
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/redo.png" alt="" />
				<?php echo gettext('Propagate un-published state'); ?>
			</a>
		</p>
		<br clear="all" />
		<?php
		} else {
			echo '<p>'.gettext('No albums are un-published.').'</p>';
		}
	?>
	</div>
	<br clear="all" />
	<br clear="all" />

<script type="text/javascript">
	//<!-- <![CDATA[
	$(function() {
		$("#publish_date").datepicker({
							showOn: 'button',
							buttonImage: '../images/calendar.png',
							buttonText: '<?php echo gettext('calendar'); ?>',
							buttonImageOnly: true
							});
	});
	// ]]> -->
</script>
<?php echo gettext('Images which are un-published')?>
<div class="smallbox">
	<form name="review" action="" method="post">
		<?php XSRFToken('schedule_content');?>
		<?php printf(gettext('Review images older than: %s'),'<input type="text" size="20" id="publish_date" name="publish_date" value="'.$requestdate.'" />'); ?>
		<br clear="all" />
		<br clear="all" />
		<input type="hidden" name="review" value="true" />
		<div class="buttons pad_button" id="reviewobjects">
			<button class="tooltip" type="submit" title="<?php echo gettext("Review un-published images."); ?>">
				<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/quest.png" alt="" /> <?php echo gettext("Review images"); ?>
			</button>
		</div>
	</form>
	<br clear="all" />
	<br clear="all" />

	<?php
	if (count($publish_images_list) > 0) {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function confirmdel(obj, id, msg) {
				if (msg) {
					if (confirm('<?php echo gettext("Are you sure you want to select this image for deletion?"); ?>')) {
						jQuery('#'+id).css({color:'red'});
						obj.checked = true;
					}
				} else {
					jQuery('#'+id).css({color:'black'});
					obj.checked = true;
				}
			}
			function publishAll(id,what) {
				$('.album_'+id+'_'+what).attr('checked','checked');
			}
			// ]]> -->
		</script>
		<form name="publish" action="" method="post"><?php echo gettext('Images:'); ?>
		<?php XSRFToken('schedule_content');?>
		<input type="hidden" name="publish_images" value="true" />
		<ul class="scheduleimagechecklist">
		<?php
		foreach ($publish_images_list as $key=>$imagelist) {
			$album = new Album($gallery,$key);
			$albumid = $album->get('id');
			$imagelist = array_flip($imagelist);
			natcasesort($imagelist);
			$imagelist = array_flip($imagelist);
			?>
			<li>
				<p class="scheduleimagechecklisthead">
					<a href="javascript:publishAll(<?php echo $albumid; ?>,'p');" title="<?php echo gettext('Set all to be published'); ?>">
						<img src="../images/pass.png" style="border: 0px;" alt="publish all" />
					</a>
					<a href="javascript:publishAll(<?php echo $albumid; ?>,'u');" title="<?php echo gettext('Set all to be un-published'); ?>">
						<img src="../images/reset.png" style="border: 0px;" alt="unpublish all" />
					</a>
					<a href="javascript:publishAll(<?php echo $albumid; ?>,'d');" title="<?php echo gettext('Set all to be deleted'); ?>">
						<img src="../images/fail.png" style="border: 0px;" alt="delete all" />
					</a>
					&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $key; ?></strong>
				</p>
				<ul class="scheduleimagelist">
				<?php
				foreach ($imagelist as $display=>$item) {
					?>
					<li>
						<table>
							<tr>
								<td>
									<label style="white-space:nowrap">
										<img src="../images/pass.png" style="border: 0px;" alt="publish" />
										<input id="pub_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_p" name="r_<?php echo $item; ?>" type="radio" value="pub_<?php echo $item; ?>" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>',false)" />
										<?php echo gettext('Publish'); ?>
									</label>
									<label style="white-space:nowrap">
										<img src="../images/reset.png" style="border: 0px;" alt="unpublish" />
										<input id="notpub_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_u" name="r_<?php echo $item; ?>" type="radio"	value="notpub_<?php echo $item; ?>"	checked="checked" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>',false)" />
										<?php echo gettext('Do not publish'); ?>
									</label>
									<label id="label_del_<?php echo $titem; ?>" style="white-space:nowrap">
										<img src="../images/fail.png" style="border: 0px;" alt="delete" />
										<input id="del_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_d" name="r_<?php echo $item; ?>" type="radio"	value="del_<?php echo $item; ?>" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>',true)" />
										<?php echo gettext('Delete'); ?>
									</label>
								</td>
								<td>
									<?php $image = newImage($album,$display); ?>
									<img src="<?php echo $image->getThumb();?>" alt="<?php echo $image->filename; ?>"/>
								</td>
								<td>
									<?php printf(gettext('%s'),$display); ?>
								</td>
							</tr>
						</table>
					</li>
					<?php
				}
				?>
				</ul>
			</li>
			<?php
		}
		?>
		</ul>
		<br clear="all" />

		<div class="buttons pad_button" id="process">
		<button class="tooltip" type="submit" title="<?php echo gettext("Process the above changes."); ?>">
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/cache1.png" alt="" /> <?php echo gettext("Process changes"); ?>
		</button>
		</div>
		<br clear="all" />
		</form>
		<?php
		} else {
			echo '<p>'.gettext('No images meet the criteria.').'</p>';
		}
		?>
	</div>
	<?php
} else {
	echo "<h3>".gettext("database not connected")."</h3>";
	echo "<p>".gettext("Check the zp-config.php file to make sure you've got the right username, password, host, and database. If you haven't created the database yet, now would be a good time.");
}
?>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
<?php echo "</html>"; ?>




