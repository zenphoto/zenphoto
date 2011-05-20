<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();
require_once('normalizer.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
	<?php
	printRSSHeaderLink('Gallery',gettext('Gallery RSS'));
	setOption('thumb_crop_width', 85, false);
	setOption('thumb_crop_height', 85, false);
	$archivepageURL = html_encode(getGalleryIndexURL());
	?>
</head>

<body class="index">
	<?php zp_apply_filter('theme_body_open'); ?>
	<?php echo getGalleryTitle(); ?><?php if (getOption('Allow_search')) {  printSearchForm(''); } ?>

	<div id="content">

		<h1><?php echo getGalleryTitle(); ?></h1>
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
							<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext("View album: %s"),getAnnotatedAlbumTitle()); ?>"><?php printAlbumTitle(); ?></a></h3>
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext("View album: %s"), getAnnotatedAlbumTitle());?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, ALBUM_THUMB_WIDTH,ALBUM_THUMB_HEIGHT,ALBUM_THUMB_WIDTH,ALBUM_THUMB_HEIGHT); ?></a>
							<p>
					<?php
						$anumber = getNumAlbums();
						$inumber = getNumImages();
						if ($anumber > 0 || $inumber > 0) {
							echo '<p><em>(';
							if ($anumber == 0) {
								if ($inumber != 0) {
									printf(ngettext('%u image','%u images', $inumber), $inumber);
								}
							} else if ($anumber == 1) {
								if ($inumber > 0) {
									printf(ngettext('1 album,&nbsp;%u image','1 album,&nbsp;%u images', $inumber), $inumber);
								} else {
									printf(gettext('1 album'));
								}
							} else {
								if ($inumber == 1) {
									printf(ngettext('%u album,&nbsp;1 image','%u albums,&nbsp;1 image', $anumber), $anumber);
								} else if ($inumber > 0) {
									printf(ngettext('%1$u album,&nbsp;%2$s','%1$u albums,&nbsp;%2$s', $anumber), $anumber, sprintf(ngettext('%u image','%u images',$inumber),$inumber));
								} else {
									printf(ngettext('%u album','%u albums', $anumber), $anumber);
								}
							}
							echo ')</em><br />';
						}
						$text = getAlbumDesc();
						if(strlen($text) > 50) {
							$text = preg_replace("/[^ ]*$/", '', sanitize(substr($text, 0, 50),1)) . "...";
						}
						echo $text;
					?></p>
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
				<h2>Description</h2>
				<?php printGalleryDesc(); ?>
			</div>
			<div class="module">
				<?php $selector = getOption('Mini_slide_selector'); ?>
				<ul id="randomlist">
					<?php
					switch($selector) {
						case 'Recent images':
							if (function_exists('getImageStatistic')) {
								echo '<h2>'.gettext('Recent images').'</h2>';
								$images = getImageStatistic(12, "latest");
								$c = 0;
								foreach ($images as $image) {
									if (is_valid_image($image->filename)) {
										if ($c++ < 6) {
											echo "<li><table><tr><td>\n";
											$imageURL = html_encode(getURL($image));
											if ($image->getWidth() >= $image->getHeight()) {
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
											echo '<a href="'.$imageURL.'" title="'.gettext("View image:").' '.
											html_encode($image->getTitle()) . '"><img src="' .
											html_encode($image->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)) .
																		'" alt="' . html_encode($image->getTitle()) . "\"/></a>\n";
											echo "</td></tr></table></li>\n";
										}
									}
								}
								break;
							}
						case 'Random images':
							echo '<h2>'.gettext('Random images').'</h2>';
							for ($i=1; $i<=6; $i++) {
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
									echo '<a href="' . $randomImageURL . '" title="'.gettext("View image:").' ' . html_encode($randomImage->getTitle()) . '">' .
 												'<img src="' . html_encode($randomImage->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)) .
												'" alt="'.html_encode($randomImage->getTitle()).'"';
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
							<th><a href="<?php echo $archivepageURL; ?>"><?php echo gettext('Galleries'); ?></a></th>
							<td><?php $albumNumber = getNumAlbums(); echo $albumNumber ?></td>
							<td></td>
						</tr>
						<tr>
							<th><?php echo gettext('Photos'); ?></th>
							<td><?php $photosArray = query_single_row("SELECT count(*) FROM ".prefix('images')); $photosNumber = array_shift($photosArray); echo $photosNumber ?></td>
							<td><?php printRSSLink('Gallery','','','',true,'i'); ?></td>
						</tr>
 					<?php if (function_exists('printCommentForm')) { ?>
 						<tr>
							<th><?php echo gettext('Comments'); ?></th>
							<td><?php $commentsArray = query_single_row("SELECT count(*) FROM ".prefix('comments')." WHERE inmoderation = 0"); $commentsNumber = array_shift($commentsArray); echo $commentsNumber ?></td>
							<td><?php printRSSLink('Comments','','','',true,'i'); ?></td>
							</tr>
						<?php } ?>
				</table>
			</div>
		</div>
	</div>
	<p id="path">
		<?php printHomeLink('', ' > '); ?>
		<?php echo getGalleryTitle(); ?>
		</p>
	<div id="footer">
		<p>
		<?php
		if (getOption('zp_plugin_contact_form')) {
			printCustomPageURL(gettext('Contact us'), 'contact', '', '');
			echo '<br />';
		}
		if (!zp_loggedin() && function_exists('printRegistrationForm')) {
			printCustomPageURL(gettext('Register for this site'), 'register', '', '');
			echo '<br />';
		}
		if (function_exists('printLanguageSelector')) {
			printLanguageSelector();
			echo '<br />';
		}
		?>
			<?php echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign.'); ?>
			<?php printZenphotoLink(); ?>
		</p>
		<?php
		if (function_exists('printUserLogin_out')) {
			printUserLogin_out("");
		}
		?>
	</div>

	<?php
	printAdminToolbox();
	zp_apply_filter('theme_body_close');
	?>

</body>

</html>
