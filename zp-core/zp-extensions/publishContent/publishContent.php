<?php
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');
admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());

function unpublishSubalbums($album) {
	global $_zp_gallery;
	$albums = $album->getAlbums();
	foreach ($albums as $albumname) {
		$subalbum = newAlbum($albumname);
		$subalbum->setShow(false);
		$subalbum->save();
		unpublishSubalbums($subalbum);
	}
}

$report = false;
$publish_albums_list = array();
$publish_images_list = array();

if (isset($_POST['set_defaults'])) {
	XSRFdefender('publishContent');
	$_zp_gallery->setAlbumPublish((int) isset($_POST['album_default']));
	$_zp_gallery->setImagePublish((int) isset($_POST['image_default']));
	$_zp_gallery->save();
	$report = 'defaults';
} else if (isset($_POST['publish'])) {
	$action = sanitize($_POST['publish']);
	unset($_POST['publish']);
	XSRFdefender('publishContent');
	switch ($action) {
		case 'albums':
			unset($_POST['checkAllAuto']);
			foreach ($_POST as $key => $albumid) {
				$album = newAlbum(postIndexDecode($key));
				$album->setShow(1);
				$album->save();
			}
			$report = 'albums';
			break;
		case 'images':
			foreach ($_POST as $action) {
				$i = strrpos($action, '_');
				$imageid = sanitize_numeric(substr($action, $i + 1));
				$rowi = query_single_row('SELECT * FROM ' . prefix('images') . ' WHERE `id`=' . $imageid);
				$rowa = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $rowi['albumid']);
				$album = newAlbum($rowa['folder']);
				$image = newImage($album, $rowi['filename']);
				switch (substr($action, 0, $i)) {
					case 'pub':
						$image->setShow(1);
						$image->save();
						break;
					case 'del':
						$image->remove();
						break;
				}
			}
			$report = 'images';
			break;
		case 'categories':
			$report = 'categories';
			foreach ($_POST as $key => $titlelink) {
				$obj = new ZenpageCategory($titlelink);
				$obj->setShow(1);
				$obj->save();
			}
			break;
		case 'news':
			$report = 'news';
			foreach ($_POST as $key => $titlelink) {
				$obj = new ZenpageNews($titlelink);
				$obj->setShow(1);
				$obj->save();
			}
			break;
		case 'pages':
			foreach ($_POST as $key => $titlelink) {
				$obj = new ZenpagePage($titlelink);
				$obj->setShow(1);
				$obj->save();
			}
			$report = 'pages';
			break;
	}
}
if ($report) {
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/publishContent/publishContent.php?report=' . $report);
	exitZP();
} else {
	if (isset($_GET['report'])) {
		$report = sanitize($_GET['report']);
	}
}
$zenphoto_tabs['overview']['subtabs'] = array(gettext('Content') => '');
printAdminHeader('overview', gettext('Content'));
datepickerJS();
?>
<link rel="stylesheet" href="publishContent.css" type="text/css" media="screen" />
<?php
echo '</head>';
?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note', 'schedule', ''); ?>
				<h1><?php echo (gettext('Manage content publication')); ?></h1>
				<?php
				$albpublish = $_zp_gallery->getAlbumPublish();
				$imgpublish = $_zp_gallery->getImagePublish();
				if (isset($_POST['publish_date'])) {
					$requestdate = dateTimeConvert(sanitize($_POST['publish_date']));
				} else {
					$requestdate = date('Y-m-d H:i:s');
				}

				$albumidlist = '';
				$albumids = '';
				if (zp_loggedin(ADMIN_RIGHTS)) {
					$albumlist = $_zp_gallery->getAlbums();
				} else {
					$albumlist = getManagedAlbumList();
					$albumIDs = array();
					foreach ($albumlist as $albumname) {
						$subalbums = getAllSubAlbumIDs($albumname);
						foreach ($subalbums as $ID) {
							$albumIDs[] = $ID['id'];
						}
					}
					$i = 0;
					foreach ($albumIDs as $ID) {
						if ($i > 0) {
							$albumidlist .= ' OR ';
							$albumids .= ' OR ';
						}
						$albumidlist .= prefix('images') . '.albumid=' . $ID;
						$albumids .= '`id`=' . $ID;
						$i++;
					}
					if (!empty($albumlist)) {
						$albumids = ' AND (' . $albumids . ')';
						$albumidlist = ' AND (' . $albumidlist . ')';
					}
				}
				if (isset($_GET['propagate_unpublished'])) {
					foreach ($albumlist as $albumname) {
						$album = newAlbum($albumname);
						if (!$album->getShow()) {
							unpublishSubalbums($album);
						}
					}
					unset($_GET['propagate_unpublished']);
					$report = 'propagate';
				}

				$mtime = dateTimeConvert($requestdate, true);
				$sql = "SELECT `folder`, `id` FROM " . prefix('albums') . ' WHERE `show`="0"' . $albumids;
				$result = query_full_array($sql);
				if (is_array($result)) {
					foreach ($result as $row) {
						$publish_albums_list[$row['folder']] = $row['id'];
					}
				}
				$sql = 'SELECT `filename`, ' . prefix('images') . '.id as id, folder FROM ' . prefix('images') . ',' . prefix('albums') . ' WHERE ' .
								prefix('images') . '.show="0" AND ' . prefix('images') . '.mtime < "' . $mtime . '" AND ' . prefix('albums') . '.id=' .
								prefix('images') . '.albumid' . $albumidlist;
				$result = query_full_array($sql);
				if (is_array($result)) {
					foreach ($result as $row) {
						$publish_images_list[$row['folder']][$row['filename']] = $row['id'];
					}
					ksort($publish_images_list, SORT_LOCALE_STRING);
				}


				if (zp_loggedin(ADMIN_RIGHTS)) { //only admin should be allowed to do this
					?>
					<fieldset class="smallbox">
						<legend><?php echo gettext('Image and album <em>Discovery</em> options'); ?></legend>
						<div id="pubbox">
							<?php
							if ($report == 'defaults') {
								?>
								<div class="messagebox fade-message">
									<h2><?php echo gettext('Defaults applied'); ?></h2>
								</div>
								<?php
							}
							?>
							<form name="set_publication" action="" method="post">
								<?php XSRFToken('publishContent'); ?>
								<input type="hidden" name="set_defaults" value="true" />
								<label><input type="checkbox" name="album_default"	value="1"<?php if ($albpublish) echo ' checked="checked"'; ?> /> <?php echo gettext("Publish albums by default"); ?></label>
								&nbsp;&nbsp;&nbsp;
								<label><input type="checkbox" name="image_default"	value="1"<?php if ($imgpublish) echo ' checked="checked"'; ?> /> <?php echo gettext("Publish images by default"); ?></label>
								<br class="clearall" />
								<br class="clearall" />
								<div class="buttons pad_button" id="setdefaults">
									<button class="tooltip" type="submit" title="<?php echo gettext("Set defaults for album publishing and image visibility."); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/burst.png" alt="" /> <?php echo gettext("Apply"); ?>
									</button>
								</div>
							</form>
							<br class="clearall" />
						</div>
					</fieldset>
					<br class="clearall" />
					<br class="clearall" />
					<?php
				}
				?>

				<?php $visible = $report == 'albums' || $report == 'propagate'; ?>
				<fieldset class="smallbox">
					<legend><?php
						reveal('albumbox', $visible);
						echo gettext('Albums not published');
						?></legend>
					<?php
					if (($c = count($publish_albums_list)) > 0) {
						echo sprintf(ngettext('%u unpublished album', '%u unpublished albums', $c), $c);
					}
					?>
					<div id="albumbox"<?php if (!$visible) echo ' style="display:none"' ?>>
						<?php
						switch ($report) {
							case 'albums':
								?>
								<div class="messagebox fade-message">
									<h2><?php echo gettext('Album changes applied'); ?></h2>
								</div>
								<?php
								break;
							case 'propagate':
								?>
								<div class="messagebox fade-message">
									<h2><?php echo gettext('Unpublished state propagated'); ?></h2>
								</div>
								<?php
								break;
						}
						if ($c > 0) {
							?>
							<form name="publish_albums" action="" method="post"><?php echo gettext('Albums:'); ?>
								<label id="autocheck">
									<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.checkAuto').prop('checked', $('#checkAllAuto').prop('checked'));"/>
									<span id="autotext"><?php echo gettext('all'); ?></span>
								</label>
								<?php XSRFToken('publishContent'); ?>
								<input type="hidden" name="publish" value="albums" />
								<ul class="schedulealbumchecklist">
									<?php
									foreach ($publish_albums_list as $analbum => $albumid) {
										$album = newAlbum($analbum);
										$thumbimage = $album->getAlbumThumbImage();
										$thumb = getAdminThumb($thumbimage, 'large');
										?>
										<li>
											<label>
												<input type="checkbox" class="checkAuto" name="<?php echo postIndexEncode($analbum); ?>" value="<?php echo $albumid; ?>" class="albumcheck" />
												<img src="<?php echo html_encode(pathurlencode($thumb)); ?>" width="60" height="60" alt="" title="album thumb" />
												<?php echo $album->name; ?>
											</label>
											<a href="<?php echo $album->getAlbumLink(); ?>" title="<?php echo gettext('view'); ?>"> (<?php echo gettext('view'); ?>)</a>
										</li>
										<?php
									}
									?>
								</ul>
								<br class="clearall" />
								<br class="clearall" />

								<div class="buttons pad_button" id="publishalbums">
									<button class="tooltip" type="submit" title="<?php echo gettext("Publish waiting albums."); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" /> <?php echo gettext("Publish albums"); ?>
									</button>
								</div>
								<br class="clearall" />
							</form>
							<p class="buttons tooltip">
								<a href="?propagate_unpublished" title="<?php echo gettext('Set all subalbums of an un-published album to un-published.'); ?>">
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/redo.png" alt="" />
									<?php echo gettext('Propagate un-published state'); ?>
								</a>
							</p>
							<br class="clearall" />
							<?php
						}
						?>
					</div>
					<?php
					if ($c == 0) {
						echo gettext('No albums are un-published.');
					}
					?>
				</fieldset>
				<br class="clearall" />

				<script type="text/javascript">
					//<!-- <![CDATA[
					$(function() {
						$("#publish_date").datepicker({
							showOn: 'button',
							buttonImage: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/calendar.png',
							buttonText: '<?php echo gettext('calendar'); ?>',
							buttonImageOnly: true
						});
					});
					// ]]> -->
				</script>
				<?php $visible = $report == 'images'; ?>
				<fieldset class="smallbox">
					<legend><?php
						reveal('imagebox', $visible);
						echo gettext('Images not published');
						?></legend>
					<div id="imagebox"<?php if (!$visible) echo ' style="display:none"' ?>>
						<form name="review" action="" method="post">
							<?php XSRFToken('publishContent'); ?>
							<?php printf(gettext('Review images older than: %s'), '<input type="text" size="20" id="publish_date" name="publish_date" value="' . $requestdate . '" />'); ?>
							<br class="clearall" />
							<br class="clearall" />
							<input type="hidden" name="review" value="true" />
							<div class="buttons pad_button" id="reviewobjects">
								<button class="tooltip" type="submit" title="<?php echo gettext("Review un-published images."); ?>">
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/warn.png" alt="" /> <?php echo gettext("Review images"); ?>
								</button>
							</div>
						</form>
						<br class="clearall" />
						<br class="clearall" />
						<?php
						$c = count($publish_images_list);
						?>

						<?php
						if ($report == 'images') {
							?>
							<div class="messagebox fade-message">
								<h2><?php echo gettext('Image changes applied'); ?></h2>
							</div>
							<?php
						}
						if ($c > 0) {
							?>
							<script type="text/javascript">
								// <!-- <![CDATA[
								function confirmdel(obj, id, msg) {
									if (msg) {
										if (confirm('<?php echo gettext("Are you sure you want to select this image for deletion?"); ?>')) {
											jQuery('#' + id).css({color: 'red'});
											obj.checked = true;
										}
									} else {
										jQuery('#' + id).css({color: 'black'});
										obj.checked = true;
									}
								}
								function publishAll(id, what) {
									if (id) {
										$('.album_' + id + '_' + what).prop('checked', true);
									} else {
										$('.global_' + what).prop('checked', true);
									}
								}
								// ]]> -->
							</script>
							<form name="publish_images" action="" method="post"><?php echo gettext('Images:'); ?>

								<?php XSRFToken('publishContent'); ?>
								<input type="hidden" name="publish" value="images" />
								<ul class="scheduleimagechecklist">
									<?php
									foreach ($publish_images_list as $key => $imagelist) {
										$album = newAlbum($key);
										$albumid = $album->getID();
										$imagelist = array_flip($imagelist);
										natcasesort($imagelist);
										$imagelist = array_flip($imagelist);
										?>
										<li>
											<p class="scheduleimagechecklisthead">
												<a href="javascript:publishAll(<?php echo $albumid; ?>,'p');" title="<?php echo gettext('Set all to be published'); ?>">
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" style="border: 0px;" alt="publish all" />
												</a>
												<a href="javascript:publishAll(<?php echo $albumid; ?>,'u');" title="<?php echo gettext('Set all to be un-published'); ?>">
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" style="border: 0px;" alt="unpublish all" />
												</a>
												<a href="javascript:publishAll(<?php echo $albumid; ?>,'d');" title="<?php echo gettext('Set all to be deleted'); ?>">
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" style="border: 0px;" alt="delete all" />
												</a>
												&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $key; ?></strong>
											</p>
											<ul class="scheduleimagelist">
												<?php
												foreach ($imagelist as $display => $item) {
													?>
													<li>
														<table>
															<tr>
																<td>
																	<label style="white-space:nowrap">
																		<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" style="border: 0px;" alt="publish" />
																		<input id="pub_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_p global_p" name="r_<?php echo $item; ?>" type="radio" value="pub_<?php echo $item; ?>" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>', false)" />
																		<?php echo gettext('Publish'); ?>
																	</label>
																	<label style="white-space:nowrap">
																		<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" style="border: 0px;" alt="unpublish" />
																		<input id="notpub_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_u global_u" name="r_<?php echo $item; ?>" type="radio"	value="notpub_<?php echo $item; ?>"	checked="checked" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>', false)" />
																		<?php echo gettext('Do not publish'); ?>
																	</label>
																	<label id="label_del_<?php echo $item; ?>" style="white-space:nowrap">
																		<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" style="border: 0px;" alt="delete" />
																		<input id="del_<?php echo $item; ?>" class="album_<?php echo $albumid; ?>_d" name="r_<?php echo $item; ?>" type="radio"	value="del_<?php echo $item; ?>" onclick="confirmdel(this, 'label_del_<?php echo $item; ?>', true)" />
																		<?php echo gettext('Delete'); ?>
																	</label>
																</td>
																<td>
																	<?php $image = newImage($album, $display); ?>
																	<img src="<?php echo html_encode(pathurlencode(getAdminThumb($image, 'large'))); ?>" alt="<?php echo $image->filename; ?>"/>
																</td>
																<td>
																	<?php printf(gettext('%s'), $display); ?><a href="<?php echo html_encode($image->getImageLink()); ?>" title="<?php echo html_encode($image->getTitle()); ?>"> (<?php echo gettext('View'); ?>)</a>
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
								<p class="scheduleimagechecklisthead">
									<a href="javascript:publishAll('','p');" title="<?php echo gettext('Set all to be published'); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" style="border: 0px;" alt="publish all" />
									</a>
									<a href="javascript:publishAll('','u');" title="<?php echo gettext('Set all to be un-published'); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" style="border: 0px;" alt="unpublish all" />
									</a>
									&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo gettext('all images'); ?></strong>
								</p>

								<div class="buttons pad_button" id="process">
									<button class="tooltip" type="submit" title="<?php echo gettext("Process the above changes."); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" /> <?php echo gettext("Process changes"); ?>
									</button>
								</div>
								<br class="clearall" />
							</form>
							<?php
						}
						?>
					</div>
					<?php
					if (count($publish_images_list) > 0) {
						echo sprintf(ngettext('%u album with unpublished images', '%u albums with unpublished images', $c), $c);
					} else {
						echo gettext('No images meet the criteria.');
					}
					?>
				</fieldset>
				<?php
				if (class_exists('Zenpage')) {
					$visible = $report == 'categories';
					$items = $_zp_zenpage->getAllCategories(false);
					$output = '';
					$c = 0;
					foreach ($items as $key => $item) {
						$itemobj = new ZenpageCategory($item['titlelink']);
						if (!$itemobj->getShow()) {
							$c++;
							$output .= '<li><label><input type="checkbox" name="' . $item['titlelink'] . '" value="' . $item['titlelink'] . '" class="catcheck" />' . $itemobj->getTitle() . '</label><a href="' . html_encode($itemobj->getCategoryLink()) . '" title="' . html_encode($itemobj->getTitle()) . '"> (' . gettext('View') . ')</a></li>';
						}
					}
					?>
					<br class="clearall" />
					<fieldset class="smallbox">
						<legend><?php
							reveal('catbox', $visible);
							echo gettext('Categories not published');
							?></legend>
						<?php
						if ($output) {
							echo sprintf(ngettext('%u unpublished category', '%u unpublished categories', $c), $c);
							?>
							<div id="catbox"<?php if (!$visible) echo ' style="display:none"' ?>>
								<?php
								if ($report == 'categories') {
									?>
									<div class="messagebox fade-message">
										<h2><?php echo gettext('Category changes applied'); ?></h2>
									</div>
									<?php
								}
								?>
								<form name="publish_cat" action="" method="post"><?php echo gettext('Categories:'); ?>
									<label id="autocheck_cat">
										<input type="checkbox" id="checkAllcat" name="checkAllcat" onclick="$('.catcheck').prop('checked', $('#checkAllcat').prop('checked'));" />
										<span id="autotext_cat"><?php echo gettext('all'); ?></span>
									</label>
									<?php XSRFToken('publishContent'); ?>
									<input type="hidden" name="publish" value="categories" />
									<ul class="schedulealbumchecklist">
										<?php echo $output; ?>
									</ul>
									<br class="clearall" />
									<div class="buttons pad_button">
										<button class="tooltip" type="submit" title="<?php echo gettext("Publish waiting categories."); ?>">
											<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" /> <?php echo gettext("Publish categories"); ?>
										</button>
									</div>
									<br class="clearall" />
								</form>
							</div>
							<?php
						} else {
							echo gettext('No unpublished categories');
						}
						?>
					</fieldset>
					<br class="clearall" />
					<?php
					$visible = $report == 'news';
					$items = $_zp_zenpage->getArticles(0, false);
					$output = '';
					$c = 0;
					foreach ($items as $key => $item) {
						$itemobj = new ZenpageNews($item['titlelink']);
						if (!$itemobj->getShow()) {
							$c++;
							$output .= '<li><label><input type="checkbox" name="' . $item['titlelink'] . '" value="' . $item['titlelink'] . '" class="artcheck" />' . $itemobj->getTitle() . '</label><a href="' . html_encode($itemobj->getNewsLink()) . '" title="' . html_encode($itemobj->getTitle()) . '"> (' . gettext('View') . ')</a></li>';
						}
					}
					?>
					<fieldset class="smallbox">
						<legend><?php
							reveal('newsbox', $visible);
							echo gettext('News articles not published');
							?></legend>
						<?php
						if ($output) {
							echo sprintf(ngettext('%u unpublished article', '%u unpublished articles', $c), $c);
							?>
							<div id="newsbox"<?php if (!$visible) echo ' style="display:none"' ?>>
								<?php
								if ($report == 'news') {
									?>
									<div class="messagebox fade-message">
										<h2><?php echo gettext('News article changes applied'); ?></h2>
									</div>
									<?php
								}
								?>
								<form name="publish_articles" action="" method="post"><?php echo gettext('Articles:'); ?>
									<label id="autocheck_art">
										<input type="checkbox" name="checkAllcat" onclick="$('.artcheck').prop('checked', checked)" />
										<span id="autotext_art"><?php echo gettext('all'); ?></span>
									</label>
									<?php XSRFToken('publishContent'); ?>
									<input type="hidden" name="publish" value="news" />
									<ul class="schedulealbumchecklist">
										<?php echo $output; ?>
									</ul>
									<br class="clearall" />
									<div class="buttons pad_button">
										<button class="tooltip" type="submit" title="<?php echo gettext("Publish waiting articles."); ?>">
											<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" /> <?php echo gettext("Publish articles"); ?>
										</button>
									</div>
									<br class="clearall" />
							</div>
							<?php
						} else {
							echo gettext('No unpublished articles');
						}
						?>
					</fieldset>
					<?php
					$visible = $report == 'pages';
					$items = $_zp_zenpage->getPages(false);
					$output = '';
					$c = 0;
					foreach ($items as $key => $item) {
						$itemobj = new ZenpagePage($item['titlelink']);
						if (!$itemobj->getShow()) {
							$c++;
							$output .= '<li><label><input type="checkbox" name="' . $item['titlelink'] . '" value="' . $item['titlelink'] . '" class="pagecheck" />' . $itemobj->getTitle() . '</label><a href="' . html_encode($itemobj->getPageLink()) . '" title="' . html_encode($itemobj->getTitle()) . '"> (' . gettext('View') . ')</a></li>';
						}
					}
					?>
					<br class="clearall" />
					<fieldset class="smallbox">
						<legend><?php
							reveal('pagebox', $visible);
							echo gettext('Pages not published');
							?></legend>
						<?php
						if ($report == 'pages') {
							?>
							<div class="messagebox fade-message">
								<h2><?php echo gettext('Pages changes applied'); ?></h2>
							</div>
							<?php
						}
						if ($output) {
							echo sprintf(ngettext('%u unpublished page', '%u unpublished pages', $c), $c);
							?>
							<div id="pagebox"<?php if (!$visible) echo ' style="display:none"' ?>>
								<form name="publish_pages" action="" method="post"><?php echo gettext('Pages:'); ?>
									<label id="autocheck_page">
										<input type="checkbox" name="checkAllpage" onclick="$('.pagecheck').prop('checked', checked);" />
										<span id="autotext_page"><?php echo gettext('all'); ?></span>
									</label>
									<?php XSRFToken('publishContent'); ?>
									<input type="hidden" name="publish" value="pages" />
									<ul class="schedulealbumchecklist">
										<?php echo $output; ?>
									</ul>
									<br class="clearall" />
									<div class="buttons pad_button">
										<button class="tooltip" type="submit" title="<?php echo gettext("Publish waiting pages."); ?>">
											<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" /> <?php echo gettext("Publish pages"); ?>
										</button>
									</div>
							</div>
							<?php
						} else {
							echo gettext('No unpublished pages');
						}
						?>
						<br class="clearall" />
					</fieldset>
					<?php
				}
				?>
			</div><!-- content -->
		</div><!-- main -->
		<?php printAdminFooter(); ?>
</body>
<?php
echo "</html>";
?>
