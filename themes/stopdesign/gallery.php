<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();
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
	if (class_exists('RSS')) printRSSHeaderLink('Gallery',gettext('Gallery RSS'));
	setOption('thumb_crop_width', 85, false);
	setOption('thumb_crop_height', 85, false);
	?>
</head>

<body class="archive">
	<?php zp_apply_filter('theme_body_open'); ?>
	<?php printGalleryTitle(); ?><?php if (getOption('Allow_search')) {  printSearchForm(); } ?>

<div id="content">

	<h1><?php printGalleryTitle(); echo ' | '.gettext('Archive'); ?></h1>

	<div class="galleries">
		<h2><?php echo gettext("All galleries"); ?></h2>
		<ul>
			<?php
			$counter = 0;
			while (next_album()):
			?>
	<li class="gal">
	<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>"
		title="<?php echo gettext('View album:').' '; printAnnotatedAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
	<a href="<?php echo html_encode(getAlbumLinkURL());?>"
		title="<?php echo gettext('View album:').' '; printAnnotatedAlbumTitle();?>"
		class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, ALBUM_THUMB_WIDTH,ALBUM_THUMB_HEIGHT,ALBUM_THUMB_WIDTH,ALBUM_THUMB_HEIGHT); ?></a>
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
			<div class="archiveinfo">
				<br />
				<p>
				<?php if (hasPrevPage()) { ?>
						<a href="<?php echo html_encode(getPrevPageURL()); ?>" accesskey="x">« <?php echo gettext('prev page'); ?></a>
				<?php } ?>
				<?php if (hasNextPage()) { if (hasPrevPage()) { echo '&nbsp;'; } ?>
						<a href="<?php echo html_encode(getNextPageURL()); ?>" accesskey="x"><?php echo gettext('next page'); ?> »</a>
				<?php } ?>
				</p>
			</div>
</div>

<div id="feeds">
	<h2><?php echo gettext('Gallery Feeds'); ?></h2>
	<ul>
		<li><?php if (class_exists('RSS')) printRSSLink('Gallery','',gettext('Photos'),''); ?></li>
		<li><?php if (class_exists('RSS')) printRSSLink('Comments','',gettext('Comments'),''); ?></li>
	</ul>
</div>

</div>

<p id="path">
	<?php printHomeLink('', ' > '); ?>
	<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> &gt;
	<?php printGalleryTitle();?>
	<?php echo gettext('Gallery Archive'); ?>
</p>

<div id="footer">
	<hr />
	<?php
	if (function_exists('printFavoritesLink')) {
		printFavoritesLink();
	}
	if (function_exists('printUserLogin_out')) { printUserLogin_out(""); }
	?>
	<p>
		<?php echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign');?>.
		<?php printZenphotoLink(); ?>
	</p>
</div>

<?php
zp_apply_filter('theme_body_close');
?>

</body>
</html>
