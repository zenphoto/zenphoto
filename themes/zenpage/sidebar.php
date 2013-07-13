<?php

// force UTF-8 Ø

if(function_exists('printCustomMenu') && getOption('zenpage_custommenu')) {
	?>
<div class="menu">
<?php printCustomMenu('zenpage','list','',"menu-active","submenu","menu-active",2); ?>
</div>
<?php
} else {
if (extensionEnabled('zenpage')) { ?>
<div class="menu">
	<h3><?php echo gettext("News articles"); ?></h3>
	<?php
	printAllNewsCategories(gettext("All news"),TRUE,"","menu-active",true,"submenu","menu-active");
	?>
</div>
<?php } ?>

<?php if(function_exists("printAlbumMenu")) { ?>
<div class="menu">
	<h3><?php echo gettext("Gallery"); ?></h3>
	<?php
	if(extensionEnabled('zenpage') && !($_zp_zenpage->news_on_index = getOption("zenpage_zp_index_news")) || !getOption("zenpage_homepage")) {
		$allalbums = gettext("Gallery index");
	} else {
		$allalbums = "";
	}
	printAlbumMenu("list",NULL,"","menu-active","submenu","menu-active",$allalbums,false,false);
	?>
</div>
<?php } ?>

<?php if (extensionEnabled('zenpage')) { ?>
<div class="menu">
	<h3><?php echo gettext("Pages"); ?></h3>
	<?php
	printPageMenu("list","","menu-active","submenu","menu-active"); ?>
</div>
<?php }
} // custom menu check end ?>

<div class="menu">
<h3><?php echo gettext("Archive"); ?></h3>
	<ul>
	<?php
		if($_zp_gallery_page == "archive.php") {
			echo "<li class='menu-active'>".gettext("Gallery And News")."</li>";
		} else {
			echo "<li>"; printCustomPageURL(gettext("Gallery and News"),"archive"); echo "</li>";
		}
		?>
	</ul>
</div>

<?php
if (getOption('RSS_album_image') || getOption('RSS_articles')) {
	?>
	<div class="menu">
	<h3><?php echo gettext("RSS"); ?></h3>
		<ul>
		<?php if(!is_null($_zp_current_album)) { ?>
		<?php if (class_exists('RSS')) printRSSLink('Album', '<li>', gettext('Album RSS'), '</li>'); ?>
		<?php } ?>
			<?php if (class_exists('RSS')) printRSSLink('Gallery','<li>',gettext('Gallery'), '</li>'); ?>
			<?php
			if(extensionEnabled('zenpage')) {
				if (class_exists('RSS')) printRSSLink("News","<li>",gettext("News"),'</li>');
				if (class_exists('RSS')) printRSSLink("NewsWithImages","<li>",gettext("News and Gallery"),'</li>');
			}
			?>
		</ul>
	</div>
	<?php
}
?>

	<?php
	if (getOption("zenpage_contactpage") && extensionEnabled('contact_form')) {
		?>
		<div class="menu">
			<ul>
				<li>
				<?php
				if($_zp_gallery_page != 'contact.php') {
					printCustomPageURL(gettext('Contact us'), 'contact', '', '');
				} else {
					echo gettext("Contact us");
				}
				?></li>
				</ul>
			</div>
		<?php
	}
	if(function_exists("printUserLogin_out") || !zp_loggedin() && function_exists('printRegistrationForm') || class_exists('mobileTheme')) {
		?>
		<div class="menu">
			<ul>
				<?php
				if (!zp_loggedin() && function_exists('printRegistrationForm')) {
					?>
					<li>
						<?php
						if($_zp_gallery_page != 'register.php') {
							printCustomPageURL(gettext('Register for this site'), 'register', '', '');
						} else {
							echo gettext("Register for this site");
						}
						?>
					</li>
					<?php
				}
				if (function_exists('printFavoritesLink')) {
					?>
					<li>
					<?php printFavoritesLink(); ?>
					</li>
					<?php
				}
				if (function_exists("printUserLogin_out")) {
					?>
					<li>
					<?php printUserLogin_out("","", 2); ?>
					</li>
					<?php
				}
				if (class_exists('mobileTheme')) {
				?>
				<li>
				<?php mobileTheme::controlLink(NULL, '',''); ?>
				</li>
				<?php
				}
				?>
			</ul>
		</div>
		<?php
	}
	?>
<?php @call_user_func('printLanguageSelector',"langselector"); ?>