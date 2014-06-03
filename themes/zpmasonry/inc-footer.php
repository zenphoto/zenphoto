
		<?php if (function_exists('printLanguageSelector')) { printLanguageSelector(); } ?>
		<script src="<?php echo $_zp_themeroot; ?>/js/jquery.masonry.min.js"></script>
		<?php if ($zpmas_infscroll) { ?><script src="<?php echo $_zp_themeroot; ?>/js/jquery.infinitescroll.min.js"></script><?php } ?>
		<script>
			$(function(){
			var $wall = $('#mason');
			$wall.masonry({
				columnWidth: 20,
				itemSelector: '.box'
			});
			<?php if ($zpmas_infscroll) { ?>
			$wall.infinitescroll({
				navSelector  : '#page_nav',  // selector for the paged navigation
				nextSelector : '#page_nav a',  // selector for the NEXT link (to page 2)
				itemSelector : '.box',     // selector for all items you'll retrieve
				loadingText  : '<?php echo gettext('Loading additional pages...'); ?>',
				loadingImg : '<?php echo $_zp_themeroot; ?>/images/arrow-alt-down<?php if ($zpmas_css == 'dark') echo "-inv"; ?>.png',
				donetext  : '<?php echo gettext('No more pages to load...'); ?>',
				debug: false,
				errorCallback: function() {
				// fade out the error message after 2 seconds
				$('#infscr-loading').animate({opacity: 1},2000).fadeOut('normal');
				}
			},
				// call masonry and colorbox as a callback.
				function( newElements ) {
					$(this).masonry({ appendedContent: $(newElements) });
					$("a.zpmas-cb").colorbox({
						slideshow:false,
						slideshowStart:'<?php echo gettext('start slideshow'); ?>',
						slideshowStop:'<?php echo gettext('stop slideshow'); ?>',
						current:'<?php echo gettext('image {current} of {total}'); ?>',	// Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
						previous:'<?php echo gettext('previous'); ?>',
						next:'<?php echo gettext('next'); ?>',
						close:'<?php echo gettext('close'); ?>',
						transition:'<?php echo $zpmas_cbtransition; ?>',
						maxHeight:'90%',
						photo:true,
						maxWidth:'90%',
						arrowKey:true
					});
				}
			);
			<?php } ?>
			});
			$('a#backtotop').click(function(){
				$('html, body').animate({scrollTop: '0px'}, 300);
				return false;
			});
		</script>
		<?php zp_apply_filter('theme_body_close'); ?>
	</body>
</html>