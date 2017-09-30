	<footer id="footer" class="footer">
		<div class="container">
			<div id="copyright">
				<?php
				echo getMainSiteName();
				if (getOption('zpB_show_archive')) {
					printCustomPageURL(gettext('Archive View'), 'archive', '', ' | ');
				}
				?>
			</div>
			<div>
				<?php printZenphotoLink(); ?> & <a href="http://getbootstrap.com/" target="_blank" title="Bootstrap">Bootstrap</a>
			</div>
		</div>
	</footer>

<?php zp_apply_filter('theme_body_close'); ?>

	</body>
</html>
<!-- zpBootstrap 2.0 - a ZenPhoto/ZenPage theme by Vincent3569 -->