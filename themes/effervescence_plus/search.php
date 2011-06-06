<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();
$_noFlash = true;  /* don't know how to deal with the variable folder depth file names
if ((getOption('Use_Simpleviewer')==0) || !MOD_REWRITE) { $_noFlash = true; }

if (isset($_GET['noflash'])) {
	$_noFlash = true;
	zp_setCookie("noFlash", "noFlash");
	} elseif (zp_getCookie("noFlash") != '') {
	$_noFlash = true;
	}
	*/

// Change the configuration here

$themeResult = getTheme($zenCSS, $themeColor, 'effervescence');
if ($_noFlash) {
	$backgroundColor = "#0";  /* who cares, we won't use it */
} else {
	$backgroundColor = parseCSSDef($zenCSS);
}

$maxImageWidth="600";
$maxImageHeight="600";

$preloaderColor="0xFFFFFF";
$textColor="0xFFFFFF";
$frameColor="0xFFFFFF";

$frameWidth="10";
$stagePadding="20";

$thumbnailColumns="3";
$thumbnailRows="6";
$navPosition="left";

$enableRightClickOpen="true";

$backgroundImagePath="";
// End of config

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo gettext("Search"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<script type="text/javascript" src="<?php echo  $_zp_themeroot ?>/scripts/bluranchors.js"></script>
	<script type="text/javascript" src="<?php echo  $_zp_themeroot ?>/scripts/swfobject.js"></script>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function toggleExtraElements(category, show) {
			if (show) {
				jQuery('.'+category+'_showless').show();
				jQuery('.'+category+'_showmore').hide();
				jQuery('.'+category+'_extrashow').show();
			} else {
				jQuery('.'+category+'_showless').hide();
				jQuery('.'+category+'_showmore').show();
				jQuery('.'+category+'_extrashow').hide();
			}
		}
		// ]]> -->
	</script>
</head>

<body onload="blurAnchors()">
	<?php
	zp_apply_filter('theme_body_open');
	$numimages = getNumImages();
	$numalbums = getNumAlbums();
	$total = $numimages + $numalbums;
	$zenpage = getOption('zp_plugin_zenpage');
	if ($zenpage && !isArchive()) {
		$numpages = getNumPages();
		$numnews = getNumNews();
		$total = $total + $numnews + $numpages;
	} else {
		$numpages = $numnews = 0;
	}
	$searchwords = getSearchWords();
	$searchdate = getSearchDate();
	if (!empty($searchdate)) {
		if (!empty($seachwords)) {
			$searchwords .= ": ";
		}
		$searchwords .= $searchdate;
	}
	if (!$total) {
		$_zp_current_search->clearSearchWords();
	}
?>

<!-- Wrap Header -->
<div id="header">
	<div id="gallerytitle">

<!-- Logo -->
	<div id="logo">
	<?php

		if (getOption('Allow_search')) {
			if (is_array($_zp_current_search->category_list)) {
				$catlist = array('news'=>$_zp_current_search->category_list,'albums'=>'0','images'=>'0','pages'=>'0');
				printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search within category'), NULL, NULL, $catlist);
			} else {
				if (is_array($_zp_current_search->album_list)) {
					$album_list = array('albums'=>$_zp_current_search->album_list,'pages'=>'0', 'news'=>'0');
					printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search within album'), NULL, NULL, $album_list);
				} else {
					printSearchForm(NULL,'search',$_zp_themeroot.'/images/search.png',gettext('Search gallery'));
				}
			}
		}
		printLogo();
	?>
	</div> <!-- logo -->
</div> <!-- gallerytitle -->

<!-- Crumb Trail Navigation -->

<div id="wrapnav">
	<div id="navbar">
		<span><?php printHomeLink('', ' | '); ?>
			<?php
			if (getOption('custom_index_page') === 'gallery') {
			?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
			<?php
			}
			?>
		<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
		<?php echo getGalleryTitle();	?></a></span> |
		<?php
		if (is_array($_zp_current_search->album_list)) {
		  	echo "<em>".sprintf(ngettext('Search album: %s','Search albums: %s',count($_zp_current_search->album_list)),implode(',',$_zp_current_search->album_list))."</em>";
		} else {
			if (is_array($_zp_current_search->category_list)) {
		  	echo "<em>".sprintf(ngettext('Search category: %s','Search categories: %s',count($_zp_current_search->category_list)),implode(',',$_zp_current_search->category_list))."</em>";
			} else {
		  	echo "<em>".gettext('Search')."</em>";
			}
		}
		?>
	</div>
</div> <!-- wrapnav -->

</div> <!-- header -->

<!-- Wrap Subalbums -->
<div id="subcontent">
	<div id="submain">

	<div id="description">
		<h2>
		</h2>
		<?php
		if ($total > 0 ) {
			printf(ngettext('%1$u Hit for <em>%2$s</em>','%1$u Hits for <em>%2$s</em>',$total), $total, $searchwords);
		}
		if ($zenpage && $_zp_page==1) { //test of zenpage searches
			define ('TRUNCATE_LENGTH',80);
			define ('SHOW_ITEMS', 5);
			?>
			<div id="efsearch">
			<?php

			if ($numpages>0) {
				?>
				<div id="efsearchhead_pages">
					<h3><?php printf(gettext('Pages (%s)'),$numpages); ?></h3>
					<?php
					if ($numpages>SHOW_ITEMS) {
						?>
						<p class="pages_showmore"><a href="javascript:toggleExtraElements('pages',true);"><?php echo gettext('Show more results');?></a></p>
						<p class="pages_showless" style="display:none;"><a href="javascript:toggleExtraElements('pages',false);"><?php echo gettext('Show fewer results');?></a></p>
						<?php
					}
					?>
				</div>
				<div class="efsearchtext">
					<ul>
					<?php
					$c = 0;
					while (next_page()) {
						$c++;
						?>
						<li<?php if ($c>SHOW_ITEMS) echo ' class="pages_extrashow" style="display:none;"'; ?>>
						<?php print printPageTitleLink(); ?>
						<p style="text-indent:1em;"><?php echo exerpt($_zp_current_zenpage_page->getContent(),TRUNCATE_LENGTH); ?></p>
						</li>
						<?php
					}
					?>
					</ul>
				</div>
				<?php
			}
			if ($numnews>0) {
				if ($numpages>0) echo '<br />';
				?>
				<div id="efsearchhead_news">
					<h3><?php printf(gettext('Articles (%s)'),$numnews); ?></h3>
					<?php
					if ($numnews>SHOW_ITEMS) {
						?>
						<p class="news_showmore"><a href="javascript:toggleExtraElements('news',true);"><?php echo gettext('Show more results');?></a></p>
						<p class="news_showless" style="display:none;"><a href="javascript:toggleExtraElements('news',false);"><?php echo gettext('Show fewer results');?></a></p>
						<?php
					}
					?>
				</div>
				<div class="efsearchtext">
					<ul>
					<?php
					$c=0;
					while (next_news()) {
						$c++;
						?>
						<li<?php if ($c>SHOW_ITEMS) echo ' class="news_extrashow" style="display:none;"'; ?>>
						<?php printNewsTitleLink(); ?>
						<p style="text-indent:1em;"><?php echo exerpt($_zp_current_zenpage_news->getContent(),TRUNCATE_LENGTH); ?></p>
						</li>
						<?php
					}
					?>
					</ul>
				</div>
				<?php
			}
			if ($total>0 && ($numpages + $numnews) > 0) {
				?>
				<br />
				<div id="efsearchhead_gallery">
					<h3>
					<?php
					if (getOption('search_no_albums')) {
						if (!getOption('search_no_images')) {
							printf(gettext('Images (%s)'),$numimages);
						}
					} else {
						if (getOption('search_no_images')) {
							printf(gettext('Albums (%s)'),$numalbums);
						} else {
							printf(gettext('Albums (%1$s) &amp; Images (%2$s)'),$numalbums,$numimages);
						}
					}
					?>
					</h3>
				</div>
				<?php
			}
			?>
			</div>
			<?php
		}
		?>
	</div>

	<!-- Album List -->
		<?php
		$firstAlbum = null;
		$lastAlbum = null;
		while (next_album()){
			if (is_null($firstAlbum)) {
				$lastAlbum = albumNumber();
				$firstAlbum = $lastAlbum;
				?>
				<ul id="albums">
				<?php
			} else {
				$lastAlbum++;
			}
			?>
			<li>
			<?php $annotate = annotateAlbum();?>
			<div class="imagethumb">
				<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo $annotate; ?>">
					<?php printCustomAlbumThumbImage($annotate, null, 180, null, 180, 80); ?></a>
			</div>
			<h4><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo $annotate;	?>"><?php printAlbumTitle(); ?></a></h4></li>
			<?php
			}
			if (!is_null($firstAlbum)) {
				?>
				</ul>
				<?php
			}
			?>
		<div class="clearage"></div>
		<?php printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums()); ?>
	</div>

<!-- Wrap Main Body -->
 	<?php
 	if ($numimages > 0){  /* Only print if we have images. */
 		if ($_noFlash) {
	 ?>
 			<div id="content">
 				<div id="main">
 					<div id="images">
 			<?php

			$firstImage = null;
			$lastImage = null;
			while (next_image()){
							if (is_null($firstImage)) {
								$lastImage = imageNumber();
								$firstImage = $lastImage;
							} else {
								$lastImage++;
							}
 						echo '<div class="image">' . "\n";
 						echo '<div class="imagethumb">' . "\n";
 						echo '<a href="' . html_encode(getImageLinkURL()) .'" title="' . GetBareImageTitle() . '">' . "\n";
 						echo printImageThumb(annotateImage()) . "</a>\n";
 						echo "</div>\n";
 						echo "</div>\n";
 					} ?>
 					</div>
 					</div> <!-- main -->
		 			<div class="clearage"></div>
 					<?php
					if (function_exists('printSlideShowLink')) {
						printSlideShowLink(gettext('View Slideshow'),'text-align:center;');
					}
 					printNofM('Photo', $firstImage, $lastImage, getNumImages());
 					?>
 					</div> <!-- content -->
	 		<?php
	 		} else {  /* flash */
	 			if ($imagePage = isImagePage()) {
	 			?>
 					<div id="flash">
 					<p align=center><font color=#663300><?php echo gettext('For the best viewing experience').' '; ?><a href="http://www.macromedia.com/go/getflashplayer/"><?php echo gettext('get Adobe Flash.'); ?></a></p>
 					<p align="center"><a href="
 					<?php
 					if ($imagePage) {
 						$url = html_encode(getPageURL(getTotalPages(true)));
 					} else {
 						$url = html_encode(getPageURL(getCurrentPage()));
 					}
 					if (substr($url, -1, 1) == '/') {$url = substr($url, 0, (strlen($url)-1));}
 					echo $url = $url . (MOD_REWRITE ? "?" : "&amp;") . 'noflash';
 					?>">
 					View gallery without Flash</a>.</p>
 					</div> <!-- flash -->
 					<?php
 					$flash_url = "index.php?p=search" . html_encode(getSearchParams()) . "&amp;format=xml";
 					?>
 					<script type="text/javascript">
 						// <!-- <![CDATA[
						var fo = new SWFObject("<?php echo  $_zp_themeroot ?>/simpleviewer.swf", "viewer", "100%", "100%", "7", "<?php echo $backgroundColor ?>");
						fo.addVariable("preloaderColor", "<?php echo $preloaderColor ?>");
						fo.addVariable("xmlDataPath", "<?php echo $flash_url ?>");
						fo.addVariable("width", "100%");
						fo.addVariable("height", "100%");
						fo.write("flash");
						// ]]> -->
 					</script>
 					<?php
	 			}
	 		} /* image loop */
	 	}

	 	if ($total == 0){
		?>
			<div id="main3">
			<div id="main2">
			<br />
			<p align="center">
			<?php
				if (empty($searchwords)) {
					echo gettext('Enter your search criteria.');
				} else {
					printf(gettext('Sorry, no matches for <em>%s</em>. Try refining your search.'),$searchwords);
				}
			?>
			</p>
		</div>
		</div> <!-- main3 -->
		<?php
 		}
	 	?>

<!-- Page Numbers -->

		<div id="pagenumbers">
		<?php
		if (($numalbums != 0) || $_noFlash){
			printPageListWithNav("&laquo; prev", "next &raquo;", !$_noFlash);
		}
		?>
		</div> <!-- pagenumbers -->
</div> <!-- subcontent -->

<!-- Footer -->
<div class="footlinks">

<?php
if (getOption('Use_Simpleviewer') && !MOD_REWRITE) {
	/* display missing css file error */
	echo '<div class="errorbox" id="message">';
	echo  "<h2>" . gettext('Simpleviewer requires <em>mod_rewrite</em> to be set. Simpleviewer is disabled.') . "</h2>";
	echo '</div>';
} ?>

<?php printThemeInfo(); ?>
<?php printZenphotoLink(); ?>
<?php
if (function_exists('printUserLogin_out')) {
	printUserLogin_out('<br />', '', true);
}
?>

</div> <!-- footerlinks -->


<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>