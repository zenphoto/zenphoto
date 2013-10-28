<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
require_once('normalizer.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
		<?php
		if (class_exists('RSS'))
			printRSSHeaderLink('Gallery', gettext('Gallery RSS'));
		setOption('thumb_crop_width', 85, false);
		setOption('thumb_crop_height', 85, false);
		$archivepageURL = html_encode(getGalleryIndexURL());
		?>
	</head>

	<body class="index">
		<?php zp_apply_filter('theme_body_open'); ?>
		<?php printGalleryTitle(); ?><?php
		if (getOption('Allow_search')) {
			printSearchForm('');
		}
		?>

		<div id="content">

			<h1><?php printGalleryTitle(); ?></h1>
			<div class="galleries">
				<h2><?php echo gettext('Recently Updated Galleries'); ?></h2>
				<ul>
					<?php
					$counter = 0;
					$_zp_gallery->setSortDirection(true);
					$_zp_gallery->setSortType('ID');
					while (next_album() and $counter < 6):
						?>
						<li class="gal">
							<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php printf(gettext("View album: %s"), html_encode(getAnnotatedAlbumTitle())); ?>"><?php printAlbumTitle(); ?></a></h3>
							<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php printf(gettext("View album: %s"), html_encode(getAnnotatedAlbumTitle())); ?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, ALBUM_THUMB_WIDTH, ALBUM_THUMB_HEIGHT, ALBUM_THUMB_WIDTH, ALBUM_THUMB_HEIGHT); ?></a>
							<p>
								<?php
								$anumber = getNumAlbums();
								$inumber = getNumImages();
								if ($anumber > 0 || $inumber > 0) {
									echo '<p><em>(';
									if ($anumber == 0) {
										if ($inumber != 0) {
											printf(ngettext('%u image', '%u images', $inumber), $inumber);
										}
									} else if ($anumber == 1) {
										if ($inumber > 0) {
											printf(ngettext('1 album,&nbsp;%u image', '1 album,&nbsp;%u images', $inumber), $inumber);
										} else {
											printf(gettext('1 album'));
										}
									} else {
										if ($inumber == 1) {
											printf(ngettext('%u album,&nbsp;1 image', '%u albums,&nbsp;1 image', $anumber), $anumber);
										} else if ($inumber > 0) {
											printf(ngettext('%1$u album,&nbsp;%2$s', '%1$u albums,&nbsp;%2$s', $anumber), $anumber, sprintf(ngettext('%u image', '%u images', $inumber), $inumber));
										} else {
											printf(ngettext('%u album', '%u albums', $anumber), $anumber);
										}
									}
									echo ')</em><br />';
								}
								echo shortenContent(strip_tags(getAlbumDesc()), 50, '...');
								?>
							</p>
							<div class="date"><?php printAlbumDate(); ?></div>
						</li>
						<?php
						if ($counter == 2) {
							echo "</ul><ul>";
						}
						$counter++;
					endwhile;
					?>
				</ul>
				<p class="mainbutton"><a href="<?php echo $archivepageURL; ?>" class="btn"><img src="<?php echo $_zp_themeroot ?>/images/btn_gallery_archive.gif" width="118" height="21" alt="<?php echo gettext('Gallery Archive'); ?>" /></a></p>
			</div>

			<div id="secondary">
				<div class="module">
					<h2><?php echo gettext('Description'); ?></h2>
					<?php printGalleryDesc(); ?>
				</div>
				<div class="module">
					<?php $selector = getOption('Mini_slide_selector'); ?>
					<ul id="randomlist">
						<?php
						switch ($selector) {
							case 'Recent images':
								if (function_exists('getImageStatistic')) {
									echo '<h2>' . gettext('Recent images') . '</h2>';
									$images = getImageStatistic(12, "latest");
									$c = 0;
									foreach ($images as $image) {
										if (is_valid_image($image->filename)) {
											if ($c++ < 6) {
												echo "<li><table><tr><td>\n";
												$imageURL = html_encode(getURL($image));
												if ($image->getWidth() >= $image->getHeight()) { //Beware if adjusting these without expected results that you must also adjust the CSS container.
													$iw = 44; //image width
													$ih = NULL; //image height
													$cw = 44; //cropped width
													$ch = 33; //cropped height
												} else {
													$iw = NULL;
													$ih = 44;
													$ch = 44;
													$cw = 33;
												}
												echo '<a href="' . $imageURL . '" title="' . gettext("View image:") . ' ' .
												html_encode($image->getTitle()) . '"><img src="' . html_encode(pathurlencode($image->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true))) .
												'" alt="' . html_encode($image->getTitle()) . "\"/></a>\n";
												echo "</td></tr></table></li>\n";
											}
										}
									}
									break;
								}
							case 'Random images':
								echo '<h2>' . gettext('Random images') . '</h2>';
								for ($i = 1; $i <= 6; $i++) {
									echo "<li><table><tr><td>\n";
									$randomImage = getRandomImages();
									if (is_object($randomImage)) {
										$randomImageURL = html_encode(getURL($randomImage));
										if ($randomImage->getWidth() >= $randomImage->getHeight()) {
											$iw = 44;
											$ih = NULL;
											$cw = 44;
											$ch = 33;
										} else {
											$iw = NULL;
											$ih = 44;
											$ch = 44;
											$cw = 33;
										}
										echo '<a href="' . $randomImageURL . '" title="' . gettext("View image:") . ' ' . html_encode($randomImage->getTitle()) . '">' .
										'<img src="' . html_encode(pathurlencode($randomImage->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true))) .
										'" alt="' . html_encode($randomImage->getTitle()) . '"';
										echo "/></a></td></tr></table></li>\n";
									}
								}
								break;
						}
						?>
					</ul>
				</div>
				<div class="module">
					<h2><?php echo gettext("Gallery data"); ?></h2>
					<table cellspacing="0" class="gallerydata">
						<tr>
							<th><a href="<?php echo $archivepageURL; ?>"><?php echo gettext('Albums'); ?></a></th>
							<td><?php
									$t = $_zp_gallery->getNumAlbums(true);
									$c = $t-$_zp_gallery->getNumAlbums(true,true);
									printf(ngettext('%u', '%u',$t),$t);			?>
							<td></td>
						</tr>
						<tr>
							<th><?php echo gettext('Photos'); ?></th>
							<td><?php
								$photosNumber = db_count('images');
								echo $photosNumber
								?></td>
							<td><?php if (class_exists('RSS')) printRSSLink('Gallery', '', '', '', true, 'i'); ?></td>
						</tr>
<?php if (function_exists('printCommentForm')) { ?>
							<tr>
								<th><?php echo gettext('Comments'); ?></th>
								<td><?php
									$commentsNumber = db_count('comments', " WHERE inmoderation = 0");
									echo $commentsNumber
									?></td>
								<td><?php if (class_exists('RSS')) printRSSLink('Comments', '', '', '', true, 'i'); ?></td>
							</tr>
<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<p id="path">
<?php printHomeLink('', ' > '); ?>
			<?php printGalleryTitle(); ?>
		</p>
		<div id="footer">
			<?php
			if (extensionEnabled('contact_form')) {
				printCustomPageURL(gettext('Contact us'), 'contact', '', ' ');
			}
			if (!zp_loggedin() && function_exists('printRegistrationForm')) {
				printCustomPageURL(gettext('Register for this site'), 'register', '', ' ');
			}
			if (function_exists('printFavoritesLink')) {
				printFavoritesLink();
			}
			?>
			<?php
			@call_user_func('printUserLogin_out', "");
			?>
			<br /><br />
				<?php @call_user_func('mobileTheme::controlLink'); ?>
			<p>
				<?php
				echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign.');
				printZenphotoLink();
				@call_user_func('printLanguageSelector');
				?>
			</p>
<?php ?>
		</div>

		<?php
		zp_apply_filter('theme_body_close');
		?>

	</body>

</html>
