<?php

// force UTF-8 Ø

if(function_exists('printCustomMenu') && getOption('zenpage_custommenu')) {
	?>
<div class="menu">
<?php printCustomMenu('zenpage','list','',"menu-active","submenu","menu-active",2); ?>
</div>
<?php
} else {
if(function_exists("printAllNewsCategories")) { ?>
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
	if(!getOption("zenpage_zp_index_news") OR !getOption("zenpage_homepage")) {
		$allalbums = gettext("Gallery index");
	} else {
		$allalbums = "";
	}
	printAlbumMenu("list",NULL,"","menu-active","submenu","menu-active",$allalbums,false,false);
	?>
</div>
<?php } ?>

<?php if(function_exists("printPageMenu")) { ?>
<div class="menu">
	<h3><?php echo gettext("Pages"); ?></h3>
	<?php
	printPageMenu("list","","menu-active","submenu","menu-active"); ?>
</div>
<?php }
} // custom menu check end ?>

<?php
	if (getOption("zenpage_contactpage") && function_exists('printContactForm')) {
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
	?>
	<?php
	if (!zp_loggedin() && function_exists('printRegistrationForm')) {
		?>
		<div class="menu">
			<ul>
				<li>
				<?php
				if($_zp_gallery_page != 'register.php') {
					printCustomPageURL(gettext('Register for this site'), 'register', '', '');
				} else {
					echo gettext("Register for this site");
				}
				?></li>
				</ul>
			</div>
		<?php
	}
	?>
	<?php
	if(function_exists("printUserLogin_out")) {
		?>
		<?php
		if (zp_loggedin()) {
			?>
			<div class="menu">
				<ul>
					<li>
			<?php
		}
		printUserLogin_out("","");
		if (zp_loggedin()) {
			?>
				</li>
			</ul>
		</div>
		<?php
		}
	}
	?>
<?php if (function_exists('printLanguageSelector')) {
	printLanguageSelector("langselector");
	}
?>