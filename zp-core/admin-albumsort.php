<?php
/**
 * used in sorting the images within and album
 * @package zpcore\admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');

if (isset($_REQUEST['album'])) {
	$localrights = ALBUM_RIGHTS;
} else {
	$localrights = NULL;
}
admin_securityChecks($localrights, $return = currentRelativeURL());

if (isset($_GET['album'])) {
	$folder = sanitize($_GET['album']);
	$album = AlbumBase::newAlbum($folder);
	if (!$album->isMyItem(ALBUM_RIGHTS)) {
		if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
			redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
		}
	}
	if (isset($_GET['saved'])) {
		XSRFdefender('save_sort');
		if (isset($_POST['ids'])) { //	process bulk actions, not individual image actions.
			$action = processImageBulkActions($album);
			if (!empty($action)) {
				$_GET['bulkmessage'] = $action;
			}
		} else {
			$orderArray = explode('&', str_replace('id[]=', '', $_POST['sortableList']));
			if (is_array($orderArray) && !empty($orderArray)) {
				foreach ($orderArray as $key => $id) {
					$sql = 'UPDATE ' . $_zp_db->prefix('images') . ' SET `sort_order`=' . $_zp_db->quote(sprintf('%03u', $key)) . ' WHERE `id`=' . sanitize_numeric($id);
					$_zp_db->query($sql);
				}
				$album->setSortType("manual");
				$album->setSortDirection(false, 'image');
				$album->setLastChangeUser($_zp_current_admin_obj->getUser());
				$album->save();
				$_GET['saved'] = 1;
			}
		}
		if(!isset($_POST['checkForPostTruncation'])) {
			$_GET['post_error'] = 1;
		}
	} 
	if (isset($_GET['action']) && isset($_GET['image'])) {
		$action = sanitize($_GET['action']);
		$filename = sanitize($_GET['image']);
		switch ($action) {
			case 'publish': // yeah, only one but we might extend here
				XSRFdefender('imageedit');
				$album = AlbumBase::newAlbum($folder);
				$image = Image::newImage($album, $filename);
				$image->setPublished(sanitize_numeric($_GET['value']));
				if ($image->hasPublishSchedule()) {
					$image->setPublishdate(date('Y-m-d H:i:s'));
				} else if ($image->hasExpiration() || $image->hasExpired()) {
					$image->setExpiredate(null);
				}
				$image->setLastchangeUser($_zp_current_admin_obj->getUser());
				$image->save(); 
				break;
		}
	}
}


// Print the admin header
setAlbumSubtabs($album);
printAdminHeader('edit', 'sort');

?>
<script>
	$(function() {
		$('#images').sortable();
	});
</script>
<?php
echo "\n</head>";
?>


<body>

	<?php
	$checkarray_images = array(
					gettext('*Bulk actions*')			 => 'noaction',
					gettext('Delete')							 => 'deleteall',
					gettext('Set to published')		 => 'showall',
					gettext('Set to unpublished')	 => 'hideall',
					gettext('Add tags')						 => 'addtags',
					gettext('Clear tags')					 => 'cleartags',
					gettext('Change owner')				 => 'changeowner'
	);
	if(extensionEnabled('comment_form')) { 
		$checkarray[gettext('Disable comments')] = 'commentsoff';
		$checkarray[gettext('Enable comments')] = 'commentson';
	}
	if (extensionEnabled('hitcounter')) {
		$checkarray_images[gettext('Reset hitcounter')] = 'resethitcounter';
	}
	$checkarray_images = zp_apply_filter('bulk_image_actions', $checkarray_images);

// Create our album
	if (!isset($_GET['album'])) {
		zp_error(gettext("No album provided to sort."));
	} else {
		// Layout the page
		printLogoAndLinks();
		?>

		<div id="main">
			<?php printTabs(); ?>
			<div id="content">
				<?php
				zp_apply_filter('admin_note', 'albums', 'sort');
				if ($album->getParent()) {
					$link = getAlbumBreadcrumbAdmin($album);
				} else {
					$link = '';
				}
				$alb = removeParentAlbumNames($album);
				?>
				<h1><?php printf(gettext('Edit Album: <em>%1$s%2$s</em>'), $link, $alb); ?></h1>
				<?php
				$images = $album->getImages();
				$subtab = printSubtabs();

				$parent = dirname($album->name);
				if ($parent == '/' || $parent == '.' || empty($parent)) {
					$parent = '';
				} else {
					$parent = '&amp;album=' . $parent . '&amp;tab=subalbuminfo';
				}
				?>

				<div class="tabbox">
					<?php
					if (isset($_GET['saved'])) {
						if (sanitize_numeric($_GET['saved'])) {
							consolidatedEditMessages($subtab);
						} else {
							if (isset($_GET['bulkmessage'])) {
								consolidatedEditMessages($subtab);
							} 
						}
					} 
					?>
					<form class="dirty-check" action="?page=edit&amp;album=<?php echo $album->getName(); ?>&amp;saved&amp;tab=sort" method="post" name="sortableListForm" id="sortableListForm" autocomplete="off">
						<?php XSRFToken('save_sort'); ?>
						<?php printBulkActions($checkarray_images, true); ?>
						<script>
							function postSort(form) {
								$('#sortableList').val($('#images').sortable('serialize'));
								form.submit();
							}
						</script>

						<p class="buttons">
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>"><img	src="images/arrow_left_blue_round.png" alt="" /><strong><?php echo gettext("Back"); ?></strong></a>
							<button type="submit" onclick="postSort(this.form);" >
								<img	src="images/pass.png" alt="" />
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getName())); ?>">
								<img src="images/view.png" alt="" />
								<strong><?php echo gettext('View Album'); ?></strong>
							</a>
						</p>
						<br class="clearall" /><br />
						<p><?php echo gettext("Set the image order by dragging them to the positions you desire."); ?></p>

						<ul id="images">
							<?php
							$images = $album->getImages();
							foreach ($images as $imagename) {
								$image = Image::newImage($album, $imagename);
								?>
								<li id="id_<?php echo $image->getID(); ?>">
									<div class="imagethumb_wrapper">
										<?php 
										$title_attr = $image->getTitle(). ' (' . html_encode($image->getName()) . ')';
										printAdminThumb($image, 'small-uncropped', 'imagethumb','', $title_attr, $image->getTitle());
										?>
									</div>
									<p>
										<?php printPublishIconLinkGallery($image, true) ?>
										<a href="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/admin-edit.php?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>&amp;image=<?php echo urlencode($image->filename); ?>&amp;tab=imageinfo#IT" title="<?php echo gettext('edit'); ?>"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pencil.png" alt=""></a>
										<?php
										if ($image->isPhoto()) {
											?>
											<a href="<?php echo html_encode(pathurlencode($image->getFullImageURL())); ?>" class="colorbox" title="zoom"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/magnify.png" alt=""></a>
											<?php
										}
										?>
										<input type="checkbox" name="ids[]" value="<?php echo $image->filename; ?>">	
									</p>
								</li>
								<?php
							}
							?>
						</ul>
						<br class="clearall" />

						<div>
							<input type="hidden" id="sortableList" name="sortableList" value="" />
							<p class="buttons">
								<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
									<img	src="images/arrow_left_blue_round.png" alt="" />
									<strong><?php echo gettext("Back"); ?></strong>
								</a>
								<button type="submit" onclick="postSort(this.form);" >
									<img	src="images/pass.png" alt="" />
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getName())); ?>">
									<img src="images/view.png" alt="" />
									<strong><?php echo gettext('View Album'); ?></strong>
								</a>
							</p>
						</div>
						<input type="hidden" name="checkForPostTruncation" value="1" />
					</form>
					<br class="clearall" />

				</div>
<?php printAlbumLegend(); ?>
			</div>

		</div>

		<?php
		printAdminFooter();
	}
	?>

</body>

<?php
echo "\n</html>";
?>
