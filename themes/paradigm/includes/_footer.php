
</div>


<!-- footer -->

<footer id="background-footer" class="background">
	<div class="container">
		<div id="footer" class="row">
			<div class="col-sm-9" id="footer-menu">
				<ul class="list-inline">
					<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li>
					<li><?php printCustomPageURL(gettext('Albums'), 'gallery'); ?></li>
					<?php
					if (getOption('display_archive')) {
						?>
						<li><?php printCustomPageURL(gettext('Archive View'), 'archive'); ?></li>
						<?php
					}

					if (function_exists("printAllNewsCategories") && ((getNumNews(true)) > 0)) {
						?>
						<li><?php printNewsIndexURL(gettext('News'), '', gettext('News')); ?></li>
						<?php
					}

					if (function_exists("printPageMenu") && ((getNumPages(true)) > 0)) {
						printPageMenu("list-top", "", "", "", "", "", "1", false);
					}
					if (function_exists('printFavoritesURL')) {
						printFavoritesURL(NULL, '<li>', '</li><li>', '</li>');
					}
					if (extensionEnabled('contact_form')) {
						?>
						<li><?php printCustomPageURL(gettext('Contact'), 'contact'); ?></li>
						<?php
					}
					?>
					<li class="last"><?php printCustomPageURL(gettext('Credits'), 'credits'); ?></li>
				</ul>
			</div>
			<div class="col-sm-3 text-right" id="social">
				<p>
					<?php
					if (getOption('facebook_url') != '') {
						echo '<a href="';
						echo getOption('facebook_url');
						echo '" target="_blank"><em class="social-icon-facebook"></em></a> ';
					}
					?>
					<?php
					if (getOption('twitter_profile') != '') {
						echo '<a href="http://www.twitter.com/';
						echo getOption('twitter_profile');
						echo '" target="_blank"><em class="social-icon-twitter"></em></a> ';
					}
					?>
					<?php
					if (getOption('googleplus_page_url') != '') {
						echo '<a href="';
						echo getOption('googleplus_page_url');
						echo '" rel="publisher" target="_blank"><em class="social-icon-googleplus"></em></a> ';
					}
					?>
					<?php
					if (getOption('flickr_url') != '') {
						echo '<a href="';
						echo getOption('flickr_url');
						echo '" target="_blank"><em class="social-icon-flickr"></em></a> ';
					}
					?>
					<?php
					if (getOption('500px_url') != '') {
						echo '<a href="';
						echo getOption('500px_url');
						echo '" target="_blank"><em class="social-icon-500px"></em></a> ';
					}
					?>
					<?php
					if (getOption('instagram_url') != '') {
						echo '<a href="';
						echo getOption('instagram_url');
						echo '" target="_blank"><em class="social-icon-instagram"></em></a> ';
					}
					?>
					<?php
					if (getOption('pinterest_url') != '') {
						echo '<a href="';
						echo getOption('pinterest_url');
						echo '" target="_blank"><em class="social-icon-pinterest"></em></a> ';
					}
					?>
					<?php
					if (getOption('deviantart_url') != '') {
						echo '<a href="';
						echo getOption('deviantart_url');
						echo '" target="_blank"><em class="social-icon-deviantart"></em></a> ';
					}
					?>
					<?php
					if (getOption('tumblr_url') != '') {
						echo '<a href="';
						echo getOption('tumblr_url');
						echo '" target="_blank"><em class="social-icon-tumblr"></em></a> ';
					}
					?>
				</p>
			</div>
		</div>
	</div>
</footer>


<?php
zp_apply_filter('theme_body_close');
?>

<?php if (getOption('sharethis_id') != '') { ?>

	<script type="text/javascript">var switchTo5x = true;</script>
	<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">stLight.options({publisher: "<?php echo getOption('sharethis_id'); ?>", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

<?php } ?>

</body>

</html>