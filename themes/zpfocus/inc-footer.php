	</div> <!-- END WRAP -->
	<div class="footerwrap">
		<div class="left">
			<div id="copyright">
				<p>&copy; <?php echo getBareGalleryTitle(); ?>, <?php echo gettext('all rights reserved'); ?></p>
			</div>
			<div id="zpcredit">
				<?php if ($zpfocus_show_credit) { printZenphotoLink(); } ?>
			</div>
		</div>
		<div class="right">
			<ul id="login_menu">
				<?php
				if (!zp_loggedin() && function_exists('printRegistrationForm')) { ?>
				<li><a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a></li>
				<?php } ?>
				
				<?php if(function_exists("printUserLogin_out")) {
				if (zp_loggedin()) { ?>
				<li><?php printUserLogin_out("",""); ?></li>
				<?php } else { ?>
				<li> | <a href="<?php echo getCustomPageURL('password'); ?>"><?php echo gettext('Login'); ?></a></li>
				<?php } ?>
				<?php } ?>	
			</ul>
			<?php if ((getOption('RSS_album_image')) || (getOption('RSS_articles'))) { ?>
			<div id="rsslinks">
				<span><?php echo gettext('Subscribe: '); ?></span>
				<?php 
				if ((in_context(ZP_ALBUM)) && (getOption('RSS_album_image'))) { printRSSLink( "Collection","",gettext('This Album'),"  |  ", false,"rsslink" ); }
				if (getOption('RSS_album_image')) { printRSSLink( "Gallery","",(gettext('Gallery Images')),"",false,"rsslink" ); }
				if ((function_exists('getBarePageTitle')) && (getOption('RSS_articles'))) { printRSSLink( "News",'','  |  ',gettext('News'),'',false ); }		
				?>
			</div>
			<br />
			<?php } ?>
			<?php if (function_exists('printLanguageSelector')) { printLanguageSelector("langselector"); } ?>
		</div>
	</div>
	<?php zp_apply_filter('theme_body_close'); ?>	
</body>
</html>