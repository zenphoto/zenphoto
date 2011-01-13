<?php

// force UTF-8 Ø

function gMapOptionsImage($map) {
	$map->setWidth(560);
}
function gMapOptionsAlbum($map) {
	global $points;
	foreach ($points as $point) {
		list($lat, $long, $title, $desc) = $point;
		addPoint($map, $lat, $long, $title, $desc);
	}
	$map->setWidth(560);
}

function footer() {
	global $_zp_gallery_page, $_zp_current_category;
	?>
	<div id="footer">
		<?php
		switch ($_zp_gallery_page) {
			default:
				printRSSLink('Gallery', '','RSS', ' | ');
				break;
			case 'album.php':
				printRSSLink('Album', '','RSS', ' | ');
				break;
			case 'news.php':
				if (is_NewsCategory()) {
					printZenpageRSSLink('Category', $_zp_current_category->getTitlelink(), '','RSS', ' | ');
				} else {
					printZenpageRSSLink('News', '', '','RSS', ' | ');
				}
				break;
		}
		?>
		<a href="?p=archive">Archive View</a>
		<?php	if ($_zp_gallery_page!='contact.php' && function_exists('printContactForm') && ($_zp_gallery_page != 'password' || getOption('gallery_page_unprotected_contact'))) printCustomPageURL(gettext('Contact us'), 'contact', '', ' | ', '');	?>
		<?php if ($_zp_gallery_page!='register.php' && !zp_loggedin() && function_exists('printRegistrationForm') && ($_zp_gallery_page != 'password' || getOption('gallery_page_unprotected_register'))) printCustomPageURL(gettext('Register for this site'), 'register', '', ' | ', '');	?>
		<?php	if (function_exists('printUserLogin_out') && $_zp_gallery_page != 'password') printUserLogin_out(' | ', '', true); ?>
		<?php
		if (function_exists('printLanguageSelector')) {
			?>
			<br />
			<?php
			printLanguageSelector();
		} else {
			?>
			<br />
			<?php
		}
		?>
		<?php printZenphotoLink(); ?>
	</div>
	<?php
}

function sidebarMenu() {
	global $_zp_gallery_page;
	?>
    <div class="sidebar">
      <?php if (getOption('Allow_search')) {  printSearchForm(); } ?>
      <?php if(function_exists("printAlbumMenu")) { ?>
	      <h2>Album Menu</h2>
	      <?php
	      printAlbumMenu("list","count","menu","menu","menu_sub","menu_sub_active");
      }
			//Zenpage meus
				if(function_exists("printAllNewsCategories")) {
					?>
						<h2><?php echo gettext("News articles"); ?></h2>
						<?php printAllNewsCategories(gettext("All news"),true,"menu","menu",true,"menu_sub","menu_sub_active"); ?>
					<?php
				}
				?>

				<?php if(function_exists("printPageMenu")) { ?>
					<h2><?php echo gettext("Pages"); ?></h2>
					<?php	printPageMenu("list","menu","menu","menu_sub","menu_sub_active"); ?>
				<?php } ?>

			<?php
			//end zenpage menus
      ?>
    </div>
<?php
}

function commonNewsLoop($paged) {
	$newstypes = array('album'=>gettext('album'),'image'=>gettext('image'),'video'=>gettext('video'),'news'=>gettext('news'));
	while (next_news()) {
		$newstype = getNewsType();
		$newstypedisplay = $newstypes[$newstype];
		if (stickyNews()) {
			$newstypedisplay .= ' <small><em>'.gettext('sticky').'</em></small>';
		}
	?>
 		<div class="newsarticle<?php if (stickyNews()) echo ' sticky'; ?>">
    	<h3><?php printNewsTitleLink(); ?><?php echo " <span class='newstype'>[".$newstypedisplay."]</span>"; ?></h3>
			<div class="newsarticlecredit">
				<span class="newsarticlecredit-left">
					<?php
					$count = getCommentCount();
					$cat = getNewsCategories();
					printNewsDate();
					if ($count > 0) {
						echo ' | ';
				 		printf(gettext("Comments: %d"),  $count);
					}
					?>
				</span>
				<?php
				if(is_GalleryNewsType()) {
					echo ' | '.gettext("Album:")." <a href='".getNewsAlbumURL()."' title='".getBareNewsAlbumTitle()."'>".getNewsAlbumTitle()."</a>";
				} else {
					if (!empty($cat)) {
						echo ' | ';
						printNewsCategories(", ",gettext("Categories: "),"newscategories");
					}
				}
				?>
			</div> <!-- newsarticlecredit -->
   		<?php printCodeblock(1); ?>
    	<?php printNewsContent(); ?>
    	<?php printCodeblock(2); ?>
    	</div>
	<?php
	}
	if ($paged) {
  	printNewsPageListWithNav(gettext('next &raquo;'), gettext('&laquo; prev'),true,'pagelist',true);
	}
}

?>