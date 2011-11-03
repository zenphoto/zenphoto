<?php

// force UTF-8 Ø

/* SQL Counting Functions */
function get_subalbum_count() {
	$where = "WHERE parentid IS NOT NULL";
	if (!zp_loggedin()) {$where .= " AND `show` = 1"; }  /* exclude the un-published albums */
	return db_count('albums',$where);
}

function show_sub_count_index() {
	echo getNumAlbums();
}

function printHeadingImage($randomImage) {
	global $_zp_themeroot;
	$id = getAlbumId();
	echo '<div id="randomhead">';
	if (is_null($randomImage)) {
		echo '<img src="'.$_zp_themeroot.'/images/zen-logo.jpg" alt="'.gettext('There were no images from which to select the random heading.').'" />';
	} else {
		$randomAlbum = $randomImage->getAlbum();
		$randomAlt1 = $randomAlbum->getTitle();
		if ($randomAlbum->getAlbumId() <> $id) {
			$randomAlbum = $randomAlbum->getParent();
			while (!is_null($randomAlbum) && ($randomAlbum->getAlbumId() <> $id)) {
				$randomAlt1 = $randomAlbum->getTitle().":\n".$randomAlt1;
				$randomAlbum = $randomAlbum->getParent();
			}
		}
		$randomImageURL = html_encode(getURL($randomImage));
		if (getOption('allow_upscale')) {
			$wide = 620;
			$high = 180;
		} else {
			$wide = min(620, $randomImage->getWidth());
			$high = min(180, $randomImage->getHeight());
		}
		echo "<a href='".$randomImageURL."' title='".gettext('Random picture...')."'>";
		$html = "<img src='".
					html_encode($randomImage->getCustomImage(NULL, $wide, $high, $wide, $high, NULL, NULL, !getOption('Watermark_head_image'))).
					"' width='$wide' height='$high' alt=".'"'.
					html_encode($randomAlt1).
					":\n".html_encode($randomImage->getTitle()).
					'" />';
		$html = zp_apply_filter('custom_image_html', $html, false);
		echo $html;
		echo '</a>';
	}
	echo '</div>';
}


/* Custom caption functions */
function getCustomAlbumDesc() {
	if(!in_context(ZP_ALBUM)) return false;
	global $_zp_current_album;
	$desc = $_zp_current_album->getDesc();
	if (strlen($desc) == 0) {
		$desc = $_zp_current_album->getTitle();
	} else {
		$desc = $_zp_current_album->getTitle()."\n".$desc;
	}
	return $desc;
}
function getImage_AlbumCount() {
	$c = getNumAlbums();
	if ($c > 0) {
		$result = "\n ".sprintf(ngettext("%u album","%u albums",$c),$c);
	} else {
		$result = '';
	}
	$c = getNumImages();
	if ($c > 0) {
		$result .=  "\n ".sprintf(ngettext("%u image","%u images",$c),$c);
	}
	return $result;
}
function parseCSSDef($file) {
	$file = str_replace(WEBPATH, '', $file);
	$file = SERVERPATH . internalToFilesystem($file);
	if (is_readable($file) && $fp = @fopen($file, "r")) {
		while($line = fgets($fp)) {
			if (!(false === strpos($line, "#main2 {"))) {
				$line = fgets($fp);
				$line = trim($line);
				$item = explode(":", $line);
				$rslt = trim(substr($item[1], 0, -1));
				return $rslt;
			}
		}
	}
	return "#0b9577"; /* the default value */
}

function printNofM($what, $first, $last, $total) {
	if (!is_null($first)) {
		echo "<p align=\"center\">";
		if ($first == $last) {
			if ($what == 'Album') {
				printf(gettext('Album %1$u of %2$u'), $first, $total);
			} else {
				printf(gettext('Photo %1$u of %2$u'), $first, $total);
			}
		} else {
			if ($what == 'Album') {
				printf(gettext('Albums %1$u-%2$u of %3$u'), $first, $last, $total);
			} else {
				printf(gettext('Photos %1$u-%2$u of %3$u'), $first, $last, $total);
			}
		}
		echo "</p>";
	}
}

function printThemeInfo() {
	global $themeColor, $themeResult, $_noFlash;
	if ($themeColor == 'effervescence') {
		$themeColor = '';
	}
	$personality = getOption('Theme_personality');
	if ($personality == 'Image page') {
		$personality = '';
	} else if (($personality == 'Simpleviewer') && (!MOD_REWRITE || $_noFlash)) {
		$personality = "<strike>$personality</strike>";
	}
	if (empty($themeColor) && empty($personality)) {
		echo '<p><small>Effervescence</small></p>';
	} else if (empty($themeColor)) {
		if ($themeResult) {
			echo '<p><small>'.sprintf(gettext('Effervescence %s'),$personality).'</small></p>';
		} else {
			echo '<p><small>'.sprintf(gettext('Effervescence %s (not found)'),$personality).'</small></p>';
		}
	} else if (empty($personality)) {
		echo '<p><small>'.sprintf(gettext('Effervescence %s'),$themeColor).'</small></p>';
	} else {
		if ($themeResult) {
			echo '<p><small>'.sprintf(gettext('Effervescence %1$s %2$s'),$themeColor, $personality).'</small></p>';
		} else {
			echo '<p><small>'.sprintf(gettext('Effervescence %1$s %2$s (not found)'),$themeColor, $personality).'</small></p>';
		}
	}
}

function printLinkWithQuery($url, $query, $text) {
	if (substr($url, -1, 1) == '/') {$url = substr($url, 0, (strlen($url)-1));}
	$url = $url . (MOD_REWRITE ? "?" : "&amp;");
	echo "<a href=\"$url$query\">$text</a>";
}

function printLogo() {
	global $_zp_themeroot;
	if ($img = getOption('Graphic_logo')) {
		$fullimg = '/'.UPLOAD_FOLDER.'/images/'.$img.'.png';
		if (file_exists(SERVERPATH.$fullimg)) {
			echo '<img src="'.pathurlencode(WEBPATH.$fullimg).'" alt="Logo"/>';
		} else {
			echo '<img src="'.$_zp_themeroot.'/images/effervescence.png" alt="Logo"/>';
		}
	} else {
		$name = get_language_string(getOption('Theme_logo'));
		if (empty($name)) {
			$name = sanitize($_SERVER['HTTP_HOST']);
		}
		echo "<h1><a>$name</a></h1>";
	}
}

function annotateAlbum() {
	global $_zp_current_album;
	$tagit = '';
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$tagit = "\n".gettext('The album is password protected.');
	}
	if (!$_zp_current_album->getShow()) {
		$tagit .= "\n".gettext('The album is not published.');
	}
	return  sprintf(gettext('View the Album: %s'),getBareAlbumTitle()).getImage_AlbumCount().$tagit;
}

function annotateImage() {
	global $_zp_current_image;
	if (!$_zp_current_image->getShow()) {
		$tagit = "\n".gettext('The image is marked not visible.');
	} else {
		$tagit = '';
	}
	return  sprintf(gettext('View the image: %s'),GetBareImageTitle()).$tagit;
}

function printFooter($admin=true) {
	global $_zp_themeroot, $_zp_gallery_page, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	$h = NULL;
	?>
	<!-- Footer -->
	<div class="footlinks">
		<?php
		$h = getHitcounter();
		if (!is_null($h)) {
			?>
			<p>
			<?php printf(ngettext('%1$u hit on this %2$s','%1$u hits on this %2$s',$h),$h, gettext('page')); ?>
			</p>
			<?php
		}
		if ($_zp_gallery_page == 'gallery') {
			?>
			<p>
				<small>
					<?php $albumNumber = getNumAlbums(); echo sprintf(ngettext("%u Album","%u Albums",$albumNumber),$albumNumber); ?> &middot;
						<?php $c=get_subalbum_count(); echo sprintf(ngettext("%u Subalbum", "%u Subalbums",$c),$c); ?> &middot;
						<?php
						$photosNumber = db_count('images');
						echo sprintf(ngettext("%u Image","%u Images",$photosNumber),$photosNumber);
						?>
						<?php if (function_exists('printCommentForm')) { ?>
							&middot;
							<?php
							$commentsNumber = db_count('comments'," WHERE inmoderation = 0");
							echo sprintf(ngettext("%u Comment","%u Comments",$commentsNumber),$commentsNumber);
							?>
						<?php } ?>
				</small>
			</p>
			<?php
		}
		?>
		<?php printThemeInfo(); ?>
		<?php printZenphotoLink(); ?>
		<?php if ($_zp_gallery_page == 'gallery') { printRSSLink('Gallery','<br />', 'Gallery RSS', ''); } ?>
		<?php	if (function_exists('printUserLogin_out') && $_zp_gallery_page != 'password') printUserLogin_out('<br />', '', true); ?>
		<?php	if (getOption('zp_plugin_contactform') && ($_zp_gallery_page != 'password' || $_zp_gallery->isUnprotectedPage('contact'))) printCustomPageURL(gettext('Contact us'), 'contact', '', '<br />');	?>
		<?php if (!zp_loggedin() && function_exists('printRegistrationForm') && ($_zp_gallery_page != 'password' || $_zp_gallery->isUnprotectedPage('unprotected_register'))) printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');	?>
		<?php if (function_exists('printLanguageSelector')) { printLanguageSelector(); } ?>
		<br clear="all" />
	</div>
	<!-- Administration Toolbox -->
	<?php
	if ($admin) {
		printAdminToolbox();
	}
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

function commonComment() {
	if (function_exists('printCommentForm')) {
		?>
		<div id="commentbox">
			<?php
			if (getCommentErrors()) {
				$style = NULL;
				$head = '';
			} else {
				$style = 'style="display:none;"';
				$head = '<br clear="all" /><p class="buttons"><a href="javascript:toggle(\'commententry\');" >'.gettext('Add a comment').'</a></p><br clear="all" />';
			}
			printCommentForm(true, $head, true, $style);
			?>
		</div><!-- id="commentbox" -->
		<?php
	}
}

if (($_ef_menu = getOption('effervescence_menu')) == 'effervescence' || $_ef_menu == 'zenpage') {
	setOption('zp_plugin_print_album_menu',1|THEME_PLUGIN,false);
}

?>