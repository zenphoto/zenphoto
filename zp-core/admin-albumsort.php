<?php
/**
 * used in sorting the images within and album
 * @package admin
 *
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
	$album = newAlbum($folder);
	if (!$album->isMyItem(ALBUM_RIGHTS)) {
		if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exitZP();
		}
	}
	if (isset($_GET['saved'])) {
		XSRFdefender('save_sort');
		parse_str($_POST['sortableList'], $inputArray);
		if (isset($inputArray['id'])) {
			$orderArray = $inputArray['id'];
			if (!empty($orderArray)) {
				foreach ($orderArray as $key => $id) {
					$sql = 'UPDATE ' . prefix('images') . ' SET `sort_order`=' . db_quote(sprintf('%03u', $key)) . ' WHERE `id`=' . sanitize_numeric($id);
					query($sql);
				}
				$album->setSortType("manual");
				$album->setSortDirection('image', 0);
				$album->save();
				$_GET['saved'] = 1;
			}
		}
	}
}

// Print the admin header
setAlbumSubtabs($album);
printAdminHeader('edit', 'sort');
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	$(function() {
		$('#images').sortable();
	});
	// ]]> -->
</script>
<?php
echo "\n</head>";
?>


<body>

	<?php
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
					zp_apply_filter('admin_note', 'albums', 'imageinfo');
					if (isset($_GET['saved'])) {
						if (sanitize_numeric($_GET['saved'])) {
							?>
							<div class="messagebox fade-message">
								<h2><?php echo gettext("Image order saved"); ?></h2>
							</div>
							<?php
						} else {
							?>
							<div class="notebox fade-message">
								<h2><?php echo gettext("Nothing changed"); ?></h2>
							</div>
							<?php
						}
					}
					?>
					<form action="?page=edit&amp;album=<?php echo $album->getFolder(); ?>&amp;saved&amp;tab=sort" method="post" name="sortableListForm" id="sortableListForm">
						<?php XSRFToken('save_sort'); ?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							function postSort(form) {
								$('#sortableList').val($('#images').sortable('serialize'));
								form.submit();
							}
							// ]]> -->
						</script>

						<p class="buttons">
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>"><img	src="images/arrow_left_blue_round.png" alt="" /><strong><?php echo gettext("Back"); ?></strong></a>
							<button type="button" onclick="postSort(this.form);" >
								<img	src="images/pass.png" alt="" />
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFolder())); ?>">
								<img src="images/view.png" alt="" />
								<strong><?php echo gettext('View Album'); ?></strong>
							</a>
						</p>
						<br class="clearall" /><br />
						<p><?php echo gettext("Set the image order by dragging them to the positions you desire."); ?></p>

						<div id="images">
							<?php
							$images = $album->getImages();
							foreach ($images as $imagename) {
								$image = newImage($album, $imagename);
								?>
								<img class="imagethumb"
										 id="id_<?php echo $image->getID(); ?>"
										 src="<?php echo getAdminThumb($image, 'large'); ?>"
										 alt="<?php echo html_encode($image->getTitle()); ?>"
										 title="<?php echo html_encode($image->getTitle()) . ' (' . html_encode($image->getFileName()) . ')'; ?>"
										 width="80" height="80"  />
										 <?php
									 }
									 ?>
						</div>
						<br />

						<div>
							<input type="hidden" id="sortableList" name="sortableList" value="" />
							<p class="buttons">
								<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
									<img	src="images/arrow_left_blue_round.png" alt="" />
									<strong><?php echo gettext("Back"); ?></strong>
								</a>
								<button type="button" onclick="postSort(this.form);" >
									<img	src="images/pass.png" alt="" />
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFolder())); ?>">
									<img src="images/view.png" alt="" />
									<strong><?php echo gettext('View Album'); ?></strong>
								</a>
							</p>
						</div>
					</form>
					<br class="clearall" />

				</div>

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
