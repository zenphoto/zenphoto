<?php
// force UTF-8 Ø
?>
<div id="sidebar">
	<?php 
	if (!getOption('zenfluid_showheader')) { 
		?>
		<div class="menu border colour">
			<div class="sidebartitle" <?php echo $titleStyle;?>>
				<a href="<?php echo getGalleryIndexURL(); ?>"><?php printGalleryTitle();?></a>
			</div>
			<div class="sidebarsubtitle" <?php echo $titleStyle;?>>
				<?php printFormattedGalleryDesc(getGalleryDesc()); echo "\n";?>
			</div>
		</div>
		<?php 
	}
	if (getOption('zenfluid_menuupper')) {
		?>
		<div class="menuupper" <?php echo $menuStyle;?>>
		<?php 
	}
	if (getOption('Allow_search')) { 
		?>
		<div class="menu border colour">
			<?php printSearchForm(NULL, "search", NULL, gettext("Search gallery")); ?>
		</div>
		<?php 
	} 
	?>
	<div class="menu border colour">
		<?php 
		if (getOption('zenfluid_menutitles')) echo '<div class="menutitle">' . gettext('Gallery') . '</div>';echo "\n";
		if (extensionEnabled('print_album_menu')) {
			printAlbumMenu("list",NULL,"","menu-active","submenu","menu-active",$homeLink);
		} else {
			echo gettext("The ZenFluid theme requires that the print_album_menu plugin be enabled.");
		} 
		?>
	</div>
	<?php 
	if (extensionEnabled('zenpage')) {
		if (getNumPages(true)) { 
			?>
			<div class="menu border colour">
				<?php if (getOption('zenfluid_menutitles')) echo '<div class="menutitle">' . gettext('Pages') . '</div>';echo "\n"; ?>
				<?php printPageMenu("list","","menu-active","submenu","menu-active");?>
			</div>
			<?php 
		}
		if (getNumNews(true)) { 
			?>
			<div class="menu border colour">
				<?php 
				if (getOption('zenfluid_menutitles')) echo '<div class="menutitle">' . gettext('News') . '</div>';echo "\n";
				printAllNewsCategories(gettext("All news"), false, "", "menu-active", true, "submenu", "menu-active"); 
				?>
			</div>
			<?php 
		}
		if (class_exists('RSS') && (getOption('RSS_album_image') || getOption('RSS_articles'))) { 
			?>
			<div class="menu border colour">
				<?php 
				if (getOption('zenfluid_menutitles')) echo '<div class="menutitle">' . gettext('RSS Feeds') . '</div>';echo "\n";
				printRSSLink('Gallery', '<ul>', gettext('Gallery RSS'), '</ul>');
				if (getNumNews(true)) printRSSLink('News', '<ul>', gettext('News RSS'), '</ul>');
				?>
			</div>
			<?php 
		}
	} else { 
		?>
		<div class="menu border colour">
			<?php echo gettext("The ZenFluid theme requires that the zenpage plugin be enabled.");?>
		</div>
		<?php 
	}
	if (function_exists('printContactForm') || function_exists('printUserLogin_out')) { 
		?>
		<div class="menu border colour">
			<ul>
				<?php 
				if (function_exists('printContactForm')) {
					if (!function_exists('printCommentForm') || !commentFormUseCaptcha()) setOption("contactform_captcha",0,false); 
					?>
					<li><?php printCustomPageURL(gettext('Contact us'), 'contact', '', '');?></li>
					<?php 
				}
				if(function_exists('printUserLogin_out')) { 
					?>
					<li><?php printUserLogin_out();?></li>
					<?php 
					if (!zp_loggedin()) {
						?>
						<li><?php printCustomPageURL(gettext('Register'), 'register', '', '');?></li>
						<?php 
					} else { 
						?>
						<li><?php printLinkHTML(WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=admin&tab=users', gettext('Profile'), gettext('Your user profile'));?></li>
						<?php 
					}
				} 
				?>
			</ul>
		</div>
		<?php 
	}
	if (getOption('zenfluid_menuupper')) {
		?>
		</div>
		<?php 
	}
	if (!zp_loggedin(ADMIN_RIGHTS) && function_exists('printGoogleAdSense')) { 
		?>
		<div class="adsense border">
			<?php printGoogleAdSense() ?>
		</div>
		<?php 
	}
	if (!getOption('zenfluid_showfooter')) {
		?>
		<div class="sidebarfooter border colour" <?php echo $titleStyle;?>>
			<?php echo gettext('zenFluid theme designed by '); ?> <br>Jim Brown<br>
			<?php printZenphotoLink();?>
		</div>
		<?php 
	} 
	?>
</div>
