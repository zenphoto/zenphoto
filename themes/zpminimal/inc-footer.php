
</div> <!-- close wrappper -->
<div id="footer">
	<div id="gradient"></div>
	<div id="copy">
		<span>&copy; <?php echo getGalleryTitle(); ?></span>
		<span>| <?php echo gettext('Subscribe: '); ?>
			<?php
			if (in_context(ZP_ALBUM)) {
				printRSSLink("Collection", "", gettext('This Album'), ", ", false, "rsslink");
			}
			printRSSLink("Gallery", "", (gettext('Gallery Images')), "", false, "rsslink");
			if (extensionEnabled('zenpage')) {
				printRSSLink("News", '', ', ', gettext('News'), '', false);
			}
			?>
		</span>
		<span id="zpcredit">| <?php printZenphotoLink(); ?></span>
			<?php if ($_zp_gallery_page == 'album.php') { ?>
				<?php if ($_zp_current_album->getParent()) {
					$linklabel = gettext('Subalbum');
				} else {
					$linklabel = gettext('Album');
				} ?>
			<div id="album-prev" class="album-nav">
				<?php $albumnav = getPrevAlbum();
				if (!is_null($albumnav)) {
					?>
					<a href="<?php echo getPrevAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo '&larr; ' . $linklabel . ': ' . truncate_string($albumnav->getTitle(), 20, '...'); ?></a>
	<?php } ?>
			</div>
			<div id="album-next" class="album-nav">
	<?php $albumnav = getNextAlbum();
	if (!is_null($albumnav)) {
		?>
					<a href="<?php echo getNextAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo $linklabel . ': ' . truncate_string($albumnav->getTitle(), 20, '...') . ' &rarr;'; ?></a>
	<?php } ?>
			</div>
<?php } ?>
	</div>
</div>
<?php if (($_zp_gallery_page == 'image.php') && (function_exists('printPagedThumbsNav')) && (!function_exists('printjCarouselThumbNav'))) {
	printPagedThumbsNav('8', true, '', '', 50, 50, true);
} ?>
<?php if (function_exists('printLanguageSelector')) {
	printLanguageSelector("langselector");
} ?>
<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>