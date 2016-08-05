<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php printBareGalleryTitle(); ?> | <?php printBareAlbumTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
        	<p id="path">
					<span>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
						<?php printParentBreadcrumb(); ?>
					</span>
					<?php printAlbumTitle(); ?>
		</p>   
		<div id="main">
			<div id="gallerytitle">
			</div>
			<div id="padbox">
				<?php printAlbumDesc(); ?>
				<div id="albums">
					<?php while (next_album()): ?>
						<div class="album">
							<div class="thumb">
                            <a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext("View album: %s"), html_encode(getAnnotatedAlbumTitle()));?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 144, 44 ,144,44); ?></a></div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small>					<?php
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
						echo shortenContent(strip_tags(getAlbumDesc()), 50, '...');
					?>
					</p></small>
								<div><?php printAlbumDesc(); ?></div>
							</div>
							<p style="clear: both; "></p>

						</div>
					<?php endwhile; ?>
				</div>
				<div id="images">
					<?php
					while (next_image()) {
					?>
						<div class="image">
							<div class="imagethumb">
								<a href="<?php echo html_encode(getImageLinkURL()); ?>" title="<?php printBareImageTitle(); ?>">
									<?php printImageThumb(getAnnotatedImageTitle()); ?>
								</a>
								<?php printAddToFavorites($_zp_current_image, '',gettext('Remove')); ?>
							</div>
						</div>
					<?php
					}
					?>
				</div>
				<?php @call_user_func('printSlideShowLink'); ?>
				<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
                		<div id="credit">
            ZenPapers Template by <a href="http://animepapers.org">Anime Papers |</a>
			<?php printZenphotoLink(); ?>
			<?php @call_user_func('printUserLogin_out'," | ");	?>
		</div>
			</div>
		</div>

		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>