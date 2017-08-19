<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo getOption('charset'); ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<title>
			<?php
			echo getMainSiteName();
			if (($_zp_gallery_page == 'index.php') && ($isHomePage)) {
				echo ' | ' . gettext('Home');
			}
			if (($_zp_gallery_page == 'index.php') && (!$isHomePage)) {
				echo ' | ' . gettext('Gallery');
			}
			if ($_zp_gallery_page == '404.php') {
				echo ' | ' . gettext('Object not found');
			}
			if ($_zp_gallery_page == 'album.php') {
				echo ' | ' . getBareAlbumTitle();
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			if ($_zp_gallery_page == 'archive.php') {
				echo ' | ' . gettext('Archive View');
			}
			if ($_zp_gallery_page == 'contact.php') {
				echo ' | ' . gettext('Contact');
			}
			if ($_zp_gallery_page == 'favorites.php') {
				echo ' | ' . gettext('My favorites');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			if ($_zp_gallery_page == 'gallery.php') {
				echo ' | ' . gettext('Gallery');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			if ($_zp_gallery_page == 'image.php') {
				echo ' | ' . getBareAlbumTitle() . ' | ' . html_encode(getBareImageTitle());
			}
			if (($_zp_gallery_page == 'news.php') && (!is_NewsArticle())) {
				echo ' | ' . gettext('News');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {
				echo ' | ' . gettext('News') . ' | ' . getBareNewsTitle();
			}
			if ($_zp_gallery_page == 'pages.php') {
				echo ' | ' . getBarePageTitle();
			}
			if ($_zp_gallery_page == 'password.php') {
				echo ' | ' . gettext('Password required');
			}
			if ($_zp_gallery_page == 'register.php') {
				echo ' | ' . gettext('Register');
			}
			if ($_zp_gallery_page == 'search.php') {
				echo ' | ' . gettext('Search');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			?>
		</title>
		<?php
		if (extensionEnabled('rss')) {
			if (getOption('RSS_album_image')) {
				printRSSHeaderLink('Gallery', gettext('Latest images'));
			}
			if (($_zenpage_enabled) && (getOption('RSS_articles'))) {
				printRSSHeaderLink('News', gettext('Latest news'));
			}
		}
		?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/screen.css" type="text/css" media="screen"/>
		<?php if (getOption('css_style') == 'light') { ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/light.css" type="text/css" media="screen"/>
		<?php } ?>
		<?php if (getOption('color_style') == 'custom') { ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/custom.css" type="text/css" media="screen"/>
		<?php } ?>
		<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" />
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/fadeSliderToggle.js"></script>
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.opacityrollover.js"></script>
		<?php if (getOption('css_style') == 'dark') { ?>
			<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpardoise.js"></script>
		<?php } else { ?>
			<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpardoise_light.js"></script>
		<?php } ?>
		<?php if (($_zp_gallery_page == 'album.php' || $_zp_gallery_page == 'favorites.php') && (getOption('use_galleriffic')) && (isImagePage() == true)) { ?>
			<script type="text/javascript">
				//<![CDATA[
				(function($) {
				var userAgent = navigator.userAgent.toLowerCase();
				$.browser = {
				version: (userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [0, '0'])[1],
								safari: /webkit/.test(userAgent),
								opera: /opera/.test(userAgent),
								msie: /msie/.test(userAgent) && !/opera/.test(userAgent),
								mozilla: /mozilla/.test(userAgent) && !/(compatible|webkit)/.test(userAgent)
				};
				})(jQuery);
				//]]>
			</script>
			<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.history.js"></script>
			<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.galleriffic.js"></script>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready(function($) {

				// Initially set opacity on thumbs
				var onMouseOutOpacity = <?php
		if (getOption('css_style') == 'dark') {
			echo '0.8';
		} else {
			echo '0.9';
		}
		?>;
				// Initialize Advanced Galleriffic Gallery
				var gallery = $('#thumbs').galleriffic({
				delay:                <?php
		if (is_numeric(getOption('galleriffic_delai'))) {
			echo getOption('galleriffic_delai');
		} else {
			echo '3000';
		}
		?>,
								numThumbs:            15,
								preloadAhead:         18,
								enableTopPager:       true,
								enableBottomPager:    true,
								maxPagesToShow:       4,
								imageContainerSel:    '#zpArdoise_slideshow',
								controlsContainerSel: '#zpArdoise_controls',
								captionContainerSel:  '#caption',
								loadingContainerSel:  '#loading',
								renderSSControls:     <?php
		if ((getOption('use_colorbox_album')) && (getOption('protect_full_image') <> 'No access')) {
			echo 'false';
		} else {
			echo 'true';
		}
		?>,
								renderNavControls:    true,
								playLinkText:         '<?php echo gettext('Slideshow'); ?>',
								pauseLinkText:        '<?php echo gettext('Stop'); ?>',
								prevLinkText:         '&laquo; <?php echo gettext('prev'); ?>',
								nextLinkText:         '<?php echo gettext('next'); ?> &raquo;',
								nextPageLinkText:     '&raquo;',
								prevPageLinkText:     '&laquo;',
								enableHistory:        true,
								autoStart:            false,
								syncTransitions:      true,
								defaultTransitionDuration:600,
								onSlideChange:       function(prevIndex, nextIndex) {
								// 'this' refers to the gallery, which is an extension of $('#thumbs')
								this.find('ul.thumbs').children()
												.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
												.eq(nextIndex).fadeTo('fast', 1.0);
								},
								onPageTransitionOut: function(callback) {
								this.fadeTo('fast', 0.0, callback);
								},
								onPageTransitionIn:  function() {
								this.fadeTo('fast', 1.0);
								}
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
				if (hash) {
				$.galleriffic.gotoImage(hash);
				} else {
				gallery.gotoIndex(0);
				}
				}

				// Initialize history plugin.
				// The callback is called at once by present location.hash.
				$.historyInit(pageload, "advanced.html");
				// set onlick event for buttons using the jQuery 1.7 .on() method
				$(document).on('click', "a[rel='history']", function(e) {
				if (e.button != 0) return true;
				var hash = this.href;
				hash = hash.replace(/^.*#/, '');
				// moves to a new page.
				// pageload is called at once.
				// hash don't contain "#", "?"
				$.historyLoad(hash);
				return false;
				});
				});
				//]]>
			</script>
		<?php } ?>

		<?php if (($_zp_gallery_page == 'image.php') || (($_zenpage_enabled) && (is_NewsArticle()))) { ?>
			<script type="text/javascript">
				//<![CDATA[
	<?php $NextURL = $PrevURL = false; ?>
	<?php if ($_zp_gallery_page == 'image.php') { ?>
		<?php if (hasNextImage()) { ?>var nextURL = "<?php
			echo getNextImageURL();
			$NextURL = true;
			?>";<?php } ?>
		<?php if (hasPrevImage()) { ?>var prevURL = "<?php
			echo getPrevImageURL();
			$PrevURL = true;
			?>";<?php } ?>
	<?php } else { ?>
		<?php if (($_zenpage_enabled) && (is_NewsArticle())) { ?>
			<?php
			if (getNextNewsURL()) {
				$article_url = getNextNewsURL();
				?>var nextURL = "<?php
				echo html_decode($article_url['link']);
				$NextURL = true;
				?>";<?php } ?>
			<?php
			if (getPrevNewsURL()) {
				$article_url = getPrevNewsURL();
				?>var prevURL = "<?php
				echo html_decode($article_url['link']);
				$PrevURL = true;
				?>";<?php } ?>
		<?php } ?>
	<?php } ?>

					function keyboardNavigation(e){

					if (ColorboxActive) return true; // cohabitation entre script de navigation et colorbox

					if (!e) e = window.event;
					if (e.altKey) return true;
					var target = e.target || e.srcElement;
					if (target && target.type) return true; //an input editable element
					var keyCode = e.keyCode || e.which;
					var docElem = document.documentElement;
					switch (keyCode) {
					case 63235: case 39:
									if (e.ctrlKey || (docElem.scrollLeft == docElem.scrollWidth - docElem.clientWidth)) {
	<?php if ($NextURL) { ?>window.location.href = nextURL; <?php } ?>return false; }
				break;
				case 63234: case 37:
								if (e.ctrlKey || (docElem.scrollLeft == 0)) {
	<?php if ($PrevURL) { ?>window.location.href = prevURL; <?php } ?>return false; }
				break;
				}
				return true;
				}

				document.onkeydown = keyboardNavigation;
				//]]>
			</script>
<?php } ?>

		<script type="text/javascript">
			//<![CDATA[
			$(document).ready(function($){
			$(".colorbox").colorbox({
			rel: "colorbox",
							slideshow: true,
							slideshowSpeed: 4000,
							slideshowStart: '<?php echo gettext("start slideshow"); ?>',
							slideshowStop: '<?php echo gettext("stop slideshow"); ?>',
							previous: '<?php echo gettext("prev"); ?>',
							next: '<?php echo gettext("next"); ?>',
							close: '<?php echo gettext("close"); ?>',
							current : "image {current} / {total}",
							maxWidth: "98%",
							maxHeight: "98%",
							photo: true
			});
			$('#comment-wrap a img[alt="RSS Feed"]').remove();
			$('#comment-wrap a[rel="nofollow"]').prepend('<img src="<?php echo $_zp_themeroot; ?>/images/rss.png" alt="RSS Feed"> ');
			});
			// cohabitation entre scripts de navigation et colorbox
			var ColorboxActive = false;
			$(document).bind('cbox_open', function() {ColorboxActive = true; })
							$(document).bind('cbox_closed', function() {ColorboxActive = false; });
			//]]>
		</script>

	</head>

	<body>
				<?php zp_apply_filter('theme_body_open'); ?>

		<div id="page">
<?php if (($_zp_gallery_page != 'image.php') || (getOption('show_image_logo_on_image'))) { ?>
				<div id="site-title" class="clearfix">
	<?php if (extensionEnabled('dynamic-locale')) { ?>
						<div id="flag"><?php printLanguageSelector(); ?></div>
				<?php } ?>
					<!-- banniere -->
					<div id="banniere">
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><img id="zplogo" src="<?php echo $_zp_themeroot; ?>/images/<?php echo getOption('use_image_logo_filename'); ?>" alt="<?php echo getGalleryTitle(); ?>" /></a>
					</div>
				</div>
				<?php } ?>

			<div id="main-menu">
				<?php
				if ((($_zp_gallery_page == 'index.php') && (!$isHomePage)) ||
								(($_zp_gallery_page == 'gallery.php') || ($_zp_gallery_page == 'album.php') || ($_zp_gallery_page == 'image.php'))) {
					$galleryactive = true;
				} else {
					$galleryactive = false;
				}
				$zenpage_homepage = getOption('zenpage_homepage');
				?>

				<ul>
					<?php if (($_zenpage_enabled) && (gettext($zenpage_homepage) <> gettext('none'))) { ?>
						<li <?php if (getPageTitleLink() == $zenpage_homepage) { ?>class="active"<?php } ?>><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a></li>
					<?php } ?>
					<li <?php if ($galleryactive) { ?>class="active"<?php } ?>><?php printCustomPageURL(gettext('Gallery'), 'gallery'); ?></li>
					<?php if (($_zenpage_enabled) && ((getNumNews(true)) > 0)) { ?>
						<li <?php if ($_zp_gallery_page == 'news.php') { ?>class="active"<?php } ?>><?php printNewsIndexURL(gettext('News'), '', gettext('News')); ?></li>
					<?php } ?>
					<?php
					if ($_zenpage_enabled) {
						printPageMenu('list-top', '', 'active', '', '', '', 0, false);
					}
					?>
					<?php if ((zp_loggedin()) && (extensionEnabled('favoritesHandler'))) { ?>
						<li <?php if ($_zp_gallery_page == 'favorites.php') { ?>class="active"<?php } ?>> <?php printFavoritesURL(); ?></li>
					<?php } ?>
<?php if (getOption('show_archive')) { ?>
						<li <?php if ($_zp_gallery_page == 'archive.php') { ?>class="active"<?php } ?>><?php printCustomPageURL(gettext('Archive View'), 'archive'); ?></li>
<?php } ?>
<?php if (extensionEnabled('contact_form')) { ?>
						<li <?php if ($_zp_gallery_page == 'contact.php') { ?>class="active"<?php } ?>><?php printCustomPageURL(gettext('Contact'), 'contact'); ?></li>
<?php } ?>
				</ul>

			</div>		<!-- END #MAIN-MENU -->

			<div id="container" class="clearfix">