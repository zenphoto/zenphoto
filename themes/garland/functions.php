<?php

// force UTF-8 Ø
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_album_statistics.php');

function gMapOptionsImage($map) {
	$map->setWidth(535);
}
function gMapOptionsAlbum($map) {
	global $points;
	foreach ($points as $coord) {
		addGeoCoord($map, $coord);
	}
	$map->setWidth(535);
}

function footer() {
	global $_zp_gallery_page, $_zp_current_category, $_zp_gallery;
	$exclude_login = array('password.php','register.php','contact.php');
	?>
	<div id="footer">
		<?php
		switch ($_zp_gallery_page) {
			default:
				printRSSLink('Gallery', '','RSS', '');
				break;
			case 'album.php':
				printRSSLink('Album', '','RSS', '');
				break;
			case 'news.php':
				if (is_NewsCategory()) {
					printZenpageRSSLink('Category', $_zp_current_category->getTitlelink(), '','RSS', '');
				} else {
					printZenpageRSSLink('News', '', '','RSS', '');
				}
				break;
		}
		?>
		<?php if ($_zp_gallery_page != 'password.php' && $_zp_gallery_page != 'archive.php') printCustomPageURL(gettext('Archive View'), 'archive', '', ' | ', ''); ?>
		<?php	if ($_zp_gallery_page!='contact.php' && getOption('zp_plugin_contact_form') && ($_zp_gallery_page != 'password' || $_zp_gallery->isUnprotectedPage('contact'))) printCustomPageURL(gettext('Contact us'), 'contact', '', ' | ', '');	?>
		<?php if ($_zp_gallery_page!='register.php' && !zp_loggedin() && function_exists('printRegistrationForm') && ($_zp_gallery_page != 'password.php' || $_zp_gallery->isUnprotectedPage('register'))) printCustomPageURL(gettext('Register for this site'), 'register', '', ' | ', '');	?>
		<?php	if (function_exists('printUserLogin_out') && !in_array($_zp_gallery_page, $exclude_login)) printUserLogin_out(' | ', '', true); ?>
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
					if (!empty($cat) && !in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
						echo ' | ';
						printNewsCategories(", ",gettext("Categories: "),"newscategories");
					}
				}
				?>
			</div> <!-- newsarticlecredit -->
			<br clear="all" />
			<?php printCodeblock(1); ?>
			<?php printNewsContent(); ?>
			<?php printCodeblock(2); ?>
			<br clear="all" />
			</div>
	<?php
	}
	if ($paged) {
		printNewsPageListWithNav(gettext('next &raquo;'), gettext('&laquo; prev'),true,'pagelist',true);
	}
}
function exerpt($content,$length) {
	return shortenContent(strip_tags($content),$length,getOption("zenpage_textshorten_indicator"));
}

?>