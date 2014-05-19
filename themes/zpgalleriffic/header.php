<?php if (!defined('WEBPATH')) die(); 
setOption('zp_plugin_colorbox',false,false); 		
if (!is_null(getOption('zpgal_tagline'))) { $zpgal_tagline = getOption('zpgal_tagline'); } else { $zpgal_tagline = 'Welcome to zpGalleriffic - Change this in Theme Options'; }
if (!is_null(getOption('zpgal_homepage'))) { $zpgal_homepage = getOption('zpgal_homepage'); } else { $zpgal_homepage = gettext('none'); }
if (!is_null(getOption('zpgal_show_credit'))) { $zpgal_show_credit = getOption('zpgal_show_credit'); } else { $zpgal_show_credit = false; }
if (!is_null(getOption('zpgal_contrast'))) { $zpgal_contrast = getOption('zpgal_contrast'); } else { $zpgal_contrast = 'dark'; }
if (!is_null(getOption('zpgal_use_image_logo_filename'))) { $zpgal_use_image_logo_filename = getOption('zpgal_use_image_logo_filename'); } else { $zpgal_use_image_logo_filename = ''; }
if (!is_null(getOption('zpgal_zp_latestnews'))) { $zpgal_zp_latestnews = getOption('zpgal_zp_latestnews'); } else { $zpgal_zp_latestnews = '2'; }
if (!is_null(getOption('zpgal_zp_latestnews_trunc'))) { $zpgal_zp_latestnews_trunc = getOption('zpgal_zp_latestnews_trunc'); } else { $zpgal_zp_latestnews_trunc = '400'; }
if (!is_null(getOption('zpgal_show_meta'))) { $zpgal_show_meta = getOption('zpgal_show_meta'); } else { $zpgal_show_meta = '1'; }
if (!is_null(getOption('zpgal_final_link'))) { $zpgal_final_link = getOption('zpgal_final_link'); } else { $zpgal_final_link = 'colorbox'; }
if (!is_null(getOption('zpgal_nogal'))) { $zpgal_nogal = getOption('zpgal_nogal'); } else { $zpgal_nogal = '1'; }
if (!is_null(getOption('zpgal_leftalign'))) { $zpgal_leftalign = getOption('zpgal_leftalign');	 } else { $zpgal_leftalign = '0';	 }	
if (!is_null(getOption('zpgal_delay'))) { $zpgal_delay = getOption('zpgal_delay'); } else { $zpgal_delay = '6000'; }
if (!is_null(getOption('zpgal_thumbcount'))) { $zpgal_thumbcount = getOption('zpgal_thumbcount'); } else { $zpgal_thumbcount = '6'; }
if (!is_null(getOption('zpgal_preload'))) { $zpgal_preload = getOption('zpgal_preload'); } else { $zpgal_preload = '12'; }	
if (!is_null(getOption('zpgal_minigaloption'))) { $zpgal_minigaloption = getOption('zpgal_minigaloption'); } else { $zpgal_minigaloption = 'latest'; }
if (!is_null(getOption('zpgal_download_link'))) { $zpgal_download_link = getOption('zpgal_download_link'); } else { $zpgal_download_link = true; }	
if (!is_null(getOption('zpgal_cbtarget'))) { $zpgal_cbtarget = getOption('zpgal_cbtarget'); } else { $zpgal_cbtarget = true; }
if (!is_null(getOption('zpgal_cbstyle'))) { $zpgal_cbstyle = getOption('zpgal_cbstyle'); } else { $zpgal_cbstyle = 'style3'; }
if (!is_null(getOption('zpgal_cbtransition'))) { $zpgal_cbtransition = getOption('zpgal_cbtransition'); } else { $zpgal_cbtransition = 'fade'; }
if (!is_null(getOption('zpgal_cbssspeed'))) { $zpgal_cbssspeed = getOption('zpgal_cbssspeed'); } else { $zpgal_cbssspeed = '2500'; }		
if (!is_null(getOption('zpgal_minigal'))) { $zpgal_minigal = getOption('zpgal_minigal'); } else { $zpgal_minigal = true; }
if (!is_null(getOption('zpgal_minigalheight'))) { $zpgal_minigalheight = getOption('zpgal_minigalheight'); } else { $zpgal_minigalheight = '250'; }
if (!is_null(getOption('zpgal_minigalcount'))) { $zpgal_minigalcount = getOption('zpgal_minigalcount'); } else { $zpgal_minigalcount = '12'; }
if (!is_null(getOption('zpgal_color'))) { $zpgal_color = getOption('zpgal_color'); } else { $zpgal_color = '#B45E2C'; }
if (!is_null(getOption('zpgal_archiveoption'))) { $zpgal_archiveoption = getOption('zpgal_archiveoption'); } else { $zpgal_archiveoption = 'latest'; }
if (!is_null(getOption('zpgal_archivecount'))) { $zpgal_archivecount = getOption('zpgal_archivecount'); } else { $zpgal_archivecount = '16'; }
if (!is_null(getOption('zpgal_crop'))) { $zpgal_crop = getOption('zpgal_crop'); } else { $zpgal_crop = true; }
if (!is_null(getOption('zpgal_minigalspecified'))) { $zpgal_minigalspecified = getOption('zpgal_minigalspecified'); } else { $zpgal_minigalspecified = ''; }
if (!is_null(getOption('zpgal_minigalspecifiedcount'))) { $zpgal_minigalspecifiedcount = getOption('zpgal_minigalspecifiedcount'); } else { $zpgal_minigalspecifiedcount = true; }
if (!is_null(getOption('zpgal_minigalspecifiedshuffle'))) { $zpgal_minigalspecifiedshuffle = getOption('zpgal_minigalspecifiedshuffle'); } else { $zpgal_minigalspecifiedshuffle = true; }

$galleryactive=false;
$imagepresent=false; 
$videopresent=false;
$minigalstat=false;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<title>
	<?php 
	echo getBareGalleryTitle(); 
	if (($_zp_gallery_page == 'index.php') || ($_zp_gallery_page == 'gallery.php')) {echo " | ".$zpgal_tagline;}
	if ($_zp_gallery_page == 'album.php') {echo " | ".getBareAlbumTitle();} 
	if ($_zp_gallery_page == 'image.php') {echo " | ".getBareAlbumTitle(); echo " | ".getBareImageTitle(); }
	if ($_zp_gallery_page == 'contact.php') {echo " | ".gettext('Contact');}
	if ($_zp_gallery_page == 'pages.php') {echo " | ".getBarePageTitle();} 
	if ($_zp_gallery_page == 'archive.php') {echo " | ".gettext('Archive View');}
	if ($_zp_gallery_page == 'password.php') {echo " | ".gettext('Password Required...');}
	if ($_zp_gallery_page == '404.php') {echo " | ".gettext('404 Not Found...');}
	if ($_zp_gallery_page == 'search.php') {echo " | ".gettext('Search: ').html_encode(getSearchWords());}
	if ($_zp_gallery_page == 'news.php') {echo " | ".gettext('News');}
	if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {echo " | ".getBareNewsTitle();} 
	?>	
	</title>
	<?php if (getOption('zp_plugin_reCaptcha')) { ?>
	<script>
		var RecaptchaOptions = {
			theme : <?php if ($zpgal_contrast == 'dark') { echo '\'blackglass\''; } else { echo '\'white\''; } ?>
		};
	</script>
	<?php } ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo getOption('charset'); ?>" />
	<?php printRSSHeaderLink( "Gallery",gettext('Gallery RSS') ); ?>
	<?php if (in_context(ZP_ALBUM)) { printRSSHeaderLink( "Collection",gettext('This Album Collection') ); } ?> 
	<?php if (function_exists("printRSSHeaderLink")) { printRSSHeaderLink("News","", gettext('News RSS'), ""); } ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/<?php echo $zpgal_contrast; ?>.css" type="text/css" media="screen"/>
	<style>
	a,a:active
	{color:<?php echo $zpgal_color; ?>;}
	<?php if ($zpgal_leftalign) { ?>
	div.centered {margin:0 0 0 30px;}
	div.navigation-container,div.navigation {left:0;}
	<?php } ?>
	</style>
	<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" /> 
	<?php zp_apply_filter('theme_head'); ?>	
	<?php require_once (ZENFOLDER."/zp-extensions/image_album_statistics.php"); ?>
	
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.opacityrollover.js"></script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.history.js"></script>
	<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/jquery.colorbox-min.js" type="text/javascript"></script>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/cbStyles/<?php echo $zpgal_cbstyle; ?>/colorbox.css" type="text/css" media="screen"/>
	<script type="text/javascript">
		$(document).ready(function(){			
			
			$("a[rel='zoom']").colorbox({
				slideshow:false,
				slideshowStart:'<?php echo gettext('start slideshow'); ?>',
				slideshowStop:'<?php echo gettext('stop slideshow'); ?>',
				current:'<?php echo gettext('image {current} of {total}'); ?>',	// Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
				previous:'<?php echo gettext('previous'); ?>',
				next:'<?php echo gettext('next'); ?>',
				close:'<?php echo gettext('close'); ?>',
				transition:'<?php echo $zpgal_cbtransition; ?>',
				maxHeight:'90%',
				photo:true,
				maxWidth:'90%',
				arrowKey:false,
				onComplete:function(){
					$('#cboxLoadedContent').append("<div id='cbCover' />");
					}
			});
			$("a[rel='slideshow']").colorbox({
				slideshow:true,
				slideshowSpeed:<?php echo $zpgal_cbssspeed; ?>,
				slideshowStart:'<?php echo gettext('start slideshow'); ?>',
				slideshowStop:'<?php echo gettext('stop slideshow'); ?>',
				current:'<?php echo gettext('image {current} of {total}'); ?>',	// Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
				previous:'<?php echo gettext('previous'); ?>',
				next:'<?php echo gettext('next'); ?>',
				close:'<?php echo gettext('close'); ?>',
				transition:'<?php echo $zpgal_cbtransition; ?>',
				maxHeight:'90%',
				photo:true,
				maxWidth:'90%',
				arrowKey:false,
				onComplete:function(){
					$('#cboxLoadedContent').append("<div id='cbCover' />");
					}
			});
		});
	</script>
	
	<?php if (($_zp_gallery_page == "archive.php") || ($_zp_gallery_page == "search.php") || ($_zp_gallery_page == "news.php")) { ?>
	<!--  TREEVIEW ARCHIVE -->
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.treeview.pack.js"></script>
	<?php } ?>

	<?php if (($zpgal_nogal) || ($zpgal_minigal)) { ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.galleriffic.js"></script>
	<?php } ?>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
				
				<?php if (($_zp_gallery_page == "archive.php") || ($_zp_gallery_page == "search.php") || ($_zp_gallery_page == "news.php")) { ?>
				$(".archive-menu").treeview({
				animated: "normal",
				collapsed: true,
				persist: "location",
				unique: true
				});
				<?php } ?>
				
				// We only want these styles applied when javascript is enabled
				$('div.content').css('display', 'block');
				
				// Initially set opacity on thumbs and add
				// additional styling for hover effect on thumbs
				var onMouseOutOpacity = 0.67;
				$('#thumbs ul.thumbs li,#thumbshome ul.thumbs li, div.navigation a.pageLink').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacity,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast',
					exemptionSelector: '.selected'
				});
				
				$(document).bind('cbox_complete', function(){
					$('#cboxPhoto').unbind().css({cursor:'inherit'});
				});
				
				$('#cboxPrevious').unbind()
				.bind('click', function(){
					$.fn.colorbox.prev();
  					gallery.previous();
				}); 
				$('#cboxNext').unbind()
				.bind('click', function(){
					$.fn.colorbox.next();
 					gallery.next(); 
				});
				
				<?php if (($_zp_gallery_page == 'album.php') || ($_zp_gallery_page == 'search.php')) { ?>
				// Initialize Album and Search Galleriffic Display
				var gallery = $('#thumbs').galleriffic({
				
					delay:                     <?php if (is_numeric($zpgal_delay)) { echo $zpgal_delay; } else { echo '6000'; } ?>,
					numThumbs:                 <?php if (is_numeric($zpgal_thumbcount)) { echo $zpgal_thumbcount; } else { echo '9'; } ?>,
					preloadAhead:              <?php if (is_numeric($zpgal_preload)) { echo $zpgal_preload; } else { echo '10'; } ?>,
					enableTopPager:            false,
					enableBottomPager:         false,
					imageContainerSel:         '#slideshow',
					controlsContainerSel:      '#controls',
					captionContainerSel:       '#caption',
					loadingContainerSel:       '#loading',
					renderSSControls:          true,
					enableKeyboardNavigation:  false,
					renderNavControls:         true,
					playLinkText:              '<?php echo gettext('Auto-Advance'); ?>',
					pauseLinkText:             '<?php echo gettext('Stop Auto-Advance'); ?>',
					prevLinkText:              '<?php echo gettext('&larr; Prev'); ?>',
					nextLinkText:              '<?php echo gettext('Next &rarr;'); ?>',
					nextPageLinkText:          '<?php echo gettext('Next &raquo;'); ?>',
					prevPageLinkText:          '<?php echo gettext('&laquo; Prev'); ?>',
					enableHistory:             true,
					autoStart:                 false,
					syncTransitions:           true,
					defaultTransitionDuration: 900,
					onSlideChangeOut:             function(prevIndex) {
						// 'this' refers to the gallery, which is an extension of $('#thumbs')
						this.find('ul.thumbs').children()
							.eq(prevIndex).fadeTo('fast', onMouseOutOpacity);
							
						// Update the photo index display
						//this.$captionContainer.find('div.photo-index')
						//	.html('Photo '+ (nextIndex+1) +' of '+ this.data.length);
					},
					onSlideChangeIn:              function(nextIndex) {
						this.find('ul.thumbs').children()
							.eq(nextIndex).fadeTo('fast', 1.0);

						// Update the photo index display
						//this.$captionContainer.find('div.photo-index')
						//	.html('Photo '+ (nextIndex+1) +' of '+ this.data.length);
					},
					
					onPageTransitionOut:       function(callback) {
						this.fadeTo('fast', 0.0, callback);
					},
					onPageTransitionIn:        function() {
						var prevPageLink = this.find('a.prev').css('visibility', 'hidden');
						var nextPageLink = this.find('a.next').css('visibility', 'hidden');
						
						// Show appropriate next / prev page links
						if (this.displayedPage > 0)
							prevPageLink.css('visibility', 'visible');

						var lastPage = this.getNumPages() - 1;
						if (this.displayedPage < lastPage)
							nextPageLink.css('visibility', 'visible');

						this.fadeTo('fast', 1.0);
					}
				});
				
				/**************** Event handlers for custom next / prev page links **********************/

				gallery.find('a.prev').click(function(e) {
					gallery.previousPage();
					e.preventDefault();
				});

				gallery.find('a.next').click(function(e) {
					gallery.nextPage();
					e.preventDefault();
				});


				/**** Functions to support integration of galleriffic with the jquery.history plugin ****/

				// PageLoad function
				// This function is called when:
				// 1. after calling $.historyInit();
				// 2. after calling $.historyLoad();
				// 3. after pushing "Go Back" button of a browser
				function pageload(hash) {
					// alert("pageload: " + hash);
					// hash doesn't contain the first # character.
					if(hash) {
						$.galleriffic.gotoImage(hash);
					} else {
						gallery.gotoIndex(0);
					}
				}

				// Initialize history plugin.
				// The callback is called at once by present location.hash. 
				$.historyInit(pageload, "advanced.html");

				// set onlick event for buttons using the jQuery 1.3 live method
				$("a[rel='history']").live('click', function(e) {
					if (e.button != 0) return true;
					
					var hash = this.href;
					hash = hash.replace(/^.*#/, '');

					// moves to a new page. 
					// pageload is called at once. 
					// hash don't contain "#", "?"
					$.historyLoad(hash);

					return false;
				});
				
				<?php } ?>
				
				$('.container').css('display', 'block');
				$('#minigal').css('display', 'block');
				/****************************************************************************************/
				
				<?php if ( (($_zp_gallery_page == 'index.php') || (($_zp_gallery_page == 'gallery.php'))) && ($zpgal_minigal)) { ?>
				// Initialize Home Mini Galleriffic Display
				var gallery = $('#thumbshome').galleriffic({
					delay:                     <?php if (is_numeric($zpgal_delay)) { echo $zpgal_delay; } else { echo '6000'; } ?>,
					numThumbs:                 4,
					preloadAhead:              <?php if (is_numeric($zpgal_preload)) { echo $zpgal_preload; } else { echo '10'; } ?>,
					renderSSControls:          false,
					renderNavControls:         false,
					enableHistory:             false,
					autoStart:                 true,
					syncTransitions:           true,
					onSlideChangeOut:          function(prevIndex) {
						this.find('ul.thumbs').children()
							.eq(prevIndex).fadeTo('fast', onMouseOutOpacity);
					},
					onSlideChangeIn:           function(nextIndex) {
						this.find('ul.thumbs').children()
							.eq(nextIndex).fadeTo('fast', 1.0);
					},
					onPageTransitionOut:       function(callback) {
						this.fadeTo('fast', 0.0, callback);
					},
					onPageTransitionIn:        function() {
						var prevPageLink = this.find('a.prev').css('visibility', 'hidden');
						var nextPageLink = this.find('a.next').css('visibility', 'hidden');
						
						// Show appropriate next / prev page links
						if (this.displayedPage > 0)
							prevPageLink.css('visibility', 'visible');

						var lastPage = this.getNumPages() - 1;
						if (this.displayedPage < lastPage)
							nextPageLink.css('visibility', 'visible');

						this.fadeTo('fast', 1.0);
					}
				});
				
				/**************** Event handlers for custom next / prev page links **********************/

				gallery.find('a.prev').click(function(e) {
					gallery.previousPage();
					e.preventDefault();
				});

				gallery.find('a.next').click(function(e) {
					gallery.nextPage();
					e.preventDefault();
				});
				<?php } ?>
				
			});
	</script>
	
	<!-- We only want the thunbnails to display when javascript is disabled -->
		<script type="text/javascript">
			document.write('<style>.noscript { display: none; }</style>');
		</script>
</head>

<body>
	<?php zp_apply_filter('theme_body_open'); ?>
	<div class="wrapper" id="menu">
		<div class="centered">
			<div id="main-menu">
				<?php if (
					($_zp_gallery_page == "index.php") ||
					($_zp_gallery_page == "gallery.php") ||
					($_zp_gallery_page == "album.php") ||
					($_zp_gallery_page == "image.php") 
					)
					{ $galleryactive = 1; }
				?>
				<ul>
					<?php if (getOption('zp_plugin_zenpage')) { ?>
					<?php if ( ($zpgal_homepage) == (gettext('none')) ) { ?>
					<li <?php if ($galleryactive) { ?>class="active" <?php } ?>><a href="<?php echo getGalleryIndexURL();?>"><?php echo gettext('Gallery'); ?></a></li>
					<?php } else { ?>
					<li><a href="<?php echo getGalleryIndexURL();?>"><?php echo gettext('Home'); ?></a></li>
					<li <?php if (($galleryactive) && ($_zp_gallery_page != "index.php")) { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Gallery'),"gallery"); ?></li>
					<?php } ?>
					<?php } else { ?>
					<li <?php if ($galleryactive) { ?>class="active" <?php } ?>><a href="<?php echo getGalleryIndexURL();?>"><?php echo gettext('Gallery'); ?></a></li>
					<?php } ?>
					
					<?php if (function_exists('getNewsIndexURL')) { ?>
					<li <?php if ($_zp_gallery_page == "news.php") { ?>class="active" <?php } ?>><a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('News'); ?></a></li>
					<?php } ?>
				</ul>
				<?php if (function_exists('printPageMenu')) { printPageMenu('list-top','','active','active','',''); } ?>
				<ul>
					<li <?php if (($_zp_gallery_page == "archive.php") || ($_zp_gallery_page == "search.php")) { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Archive/Search'),"archive"); ?></li>
					<?php if (function_exists('printContactForm')) { ?>
					<li <?php if ($_zp_gallery_page == "contact.php") { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Contact'),"contact"); ?></li>
					<?php } ?>
				</ul>
				<ul id="login_menu">
					<?php
					if (!zp_loggedin() && function_exists('printRegistrationForm')) { ?>
					<li><a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a></li>
					<?php } ?>
					
					<?php if(function_exists("printUserLogin_out")) {
					if (zp_loggedin()) { ?>
					<li><?php printUserLogin_out("",""); ?></li>
					<?php } else { ?>
					<li><a href="<?php echo getCustomPageURL('login'); ?>" title="<?php echo gettext('Login'); ?>"><?php echo gettext('Login'); ?></a></li>
					<?php } ?>
					<?php } ?>
					
				</ul>
				
			</div>
			
		</div>
	</div>
	<div class="wrapper" id="site-title-wrap">
		<div class="centered">
			<?php if (function_exists('printAddThis')) { ?><div class="gjr-addthis"><?php printAddThis(); ?></div><?php } ?>
			<div id="site-title">
				<?php if (strlen($zpgal_use_image_logo_filename) > 0) { ?>
				<a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php echo getGalleryTitle();?>"><img id="logo" src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpgal_use_image_logo_filename; ?>" alt="<?php echo getGalleryTitle();?>" /></a>
				<?php } else { ?>
				<h1><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>"><?php echo getGalleryTitle();?></a></h1>
				<?php } ?>
			</div>
		</div>
	</div>
			