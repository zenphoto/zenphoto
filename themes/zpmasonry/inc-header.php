<?php if (!defined('WEBPATH')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/<?php echo $zpmas_css; ?>.css" />
		<?php
		switch ($_zp_gallery_page) {
			case 'index.php':
				if ($_zp_page > 1) {
					$metatitle = getBareGalleryTitle() . " ($_zp_page)";
				} else {
					$metatitle = getBareGalleryTitle();
				}
				$zpmas_metatitle = $metatitle;
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				$galleryactive = true;
				if ($zpmas_ss)
					require_once (SERVERPATH . '/' . ZENFOLDER . "/zp-extensions/image_album_statistics.php");
				break;
			case 'album.php':
			case 'favorites.php':
				$galleryactive = true;
				if ($_zp_page > 1) {
					$metatitle = getBareAlbumTitle() . " ($_zp_page)";
				} else {
					$metatitle = getBareAlbumTitle();
				}
				$zpmas_metatitle = $metatitle . getTitleBreadcrumb() . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareAlbumDesc(), 150, '...');
				if (extensionEnabled('rss')) {
					if (getOption('RSS_album_image')) {
						printRSSHeaderLink('Collection', getBareAlbumTitle() . ' - ' . gettext('Latest Images'), $lang = '') . "\n";
					}
					if ((function_exists('printCommentForm')) && (getOption('RSS_comments'))) {
						printRSSHeaderLink('Comments-album', getBareAlbumTitle() . ' - ' . gettext('Latest Comments'), $lang = '') . "\n";
					}
				}
				break;
			case 'image.php':
				$galleryactive = true;
				if (!$_zp_current_album->isDynamic()) {
					$titlebreadcrumb = getTitleBreadcrumb();
				} else {
					$titlebreadcrumb = '';
				}
				$zpmas_metatitle = getBareImageTitle() . ' | ' . getBareAlbumTitle() . $titlebreadcrumb . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareImageDesc(), 150, '...');
				if ((extensionEnabled('rss')) && (function_exists('printCommentForm')) && (getOption('RSS_comments'))) {
					printRSSHeaderLink('Comments-image', getBareImageTitle() . ' - ' . gettext('Latest Comments'), $lang = '') . "\n";
				}
				break;
			case 'archive.php':
				$zpmas_metatitle = gettext("Archive View") . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'search.php':
				$galleryactive = true;
				$zpmas_metatitle = gettext('Search') . ' | ' . html_encode(getSearchWords()) . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'pages.php':
				$zpmas_metatitle = getBarePageTitle() . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBare(getPageContent(), 150, '...'));
				break;
			case 'news.php':
				setOption('zenpage_combinews', 0, false);
				if (is_NewsArticle()) {
					$zpmas_metatitle = gettext('News') . ' | ' . getBareNewsTitle() . ' | ' . getBareGalleryTitle();
					$zpmas_metadesc = truncate_string(getBare(getNewsContent(), 150, '...'));
				} else if ($_zp_current_category) {
					$zpmas_metatitle = gettext('News') . ' | ' . $_zp_current_category->getTitle() . ' | ' . getBareGalleryTitle();
					$zpmas_metadesc = truncate_string(getBare(getNewsCategoryDesc(), 150, '...'));
				} else if (getCurrentNewsArchive()) {
					$zpmas_metatitle = gettext('News') . ' | ' . getCurrentNewsArchive() . ' | ' . getBareGalleryTitle();
					$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				} else {
					$zpmas_metatitle = gettext('News') . ' | ' . getBareGalleryTitle();
					$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				}
				break;
			case 'contact.php':
				$zpmas_metatitle = gettext('Contact') . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'login.php':
				$zpmas_metatitle = gettext('Login') . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'register.php':
				$zpmas_metatitle = gettext('Register') . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'password.php':
				$zpmas_metatitle = gettext('Password Required') . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case '404.php':
				$zpmas_metatitle = gettext('404 Not Found...') . ' | ' . getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			default:
				$zpmas_metatitle = getBareGalleryTitle();
				$zpmas_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
		}
		// Finish out header RSS links for inc-header.php
		if (extensionEnabled('rss')) {
			if (getOption('RSS_album_image')) {
				printRSSHeaderLink('Gallery', gettext('Latest Images')) . "\n";
			}
			if (getOption('RSS_album_image')) {
				printRSSHeaderLink('AlbumsRSS', gettext('Latest Albums')) . "\n";
			}
			if (getOption('zp_plugin-zenpage') && getOption('zpmas_usenews')) {
				if (getOption('RSS_articles')) {
					printRSSHeaderLink('News', '', gettext('Latest News')) . "\n";
				}
				if ((function_exists('printCommentForm')) && (getOption('RSS_article_comments'))) {
					printRSSHeaderLink('Comments-all', '', gettext('Latest Comments')) . "\n";
				}
			} else {
				if ((function_exists('printCommentForm')) && (getOption('RSS_comments'))) {
					printRSSHeaderLink('Comments', gettext('Latest Comments')) . "\n";
				}
			}
		}
		?>
		<title><?php echo html_encode($zpmas_metatitle); ?></title>
		<meta name="description" content="<?php echo $zpmas_metadesc; ?>" />

		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/jquery.colorbox-min.js" type="text/javascript"></script>
		<?php if ($zpmas_ss) { ?>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/slideshow/jquery.cycle.all.js" type="text/javascript"></script>
		<?php } ?>
		<link rel="stylesheet" href="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/themes/<?php echo $zpmas_cbstyle; ?>/colorbox.css" type="text/css" media="screen"/>
		<script type="text/javascript">
			window.addEventListener('load', function () {
				$('#page_nav').css('display', 'none');
				$("a.zpmas-cb").colorbox({
					slideshow: false,
					slideshowStart: '<?php echo gettext('start slideshow'); ?>',
					slideshowStop: '<?php echo gettext('stop slideshow'); ?>',
					current: '<?php echo gettext('image {current} of {total}'); ?>', // Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
					previous: '<?php echo gettext('previous'); ?>',
					next: '<?php echo gettext('next'); ?>',
					close: '<?php echo gettext('close'); ?>',
					transition: '<?php echo $zpmas_cbtransition; ?>',
					maxHeight: '90%',
					photo: true,
					maxWidth: '90%',
					arrowKey: true
				});
				$("a[rel='slideshow']").colorbox({
					slideshow: true,
					slideshowSpeed:<?php echo $zpmas_cbssspeed; ?>,
					slideshowStart: '<?php echo gettext('start slideshow'); ?>',
					slideshowStop: '<?php echo gettext('stop slideshow'); ?>',
					current: '<?php echo gettext('image {current} of {total}'); ?>', // Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
					previous: '<?php echo gettext('previous'); ?>',
					next: '<?php echo gettext('next'); ?>',
					close: '<?php echo gettext('close'); ?>',
					transition: '<?php echo $zpmas_cbtransition; ?>',
					maxHeight: '90%',
					photo: true,
					maxWidth: '90%',
					arrowKey: true
				});
<?php if ($zpmas_fixsidebar) { ?>
					var top = $('#sidebar-inner').offset().top - parseFloat($('#sidebar-inner').css('marginTop').replace(/auto/, 0));
					var sidenavHeight = $("#sidebar-inner").height(); //Get height of sidebar
					var winHeight = $(window).height(); //Get height of viewport
					$(window).scroll(function (event) {
						// what the y position of the scroll is
						var y = $(this).scrollTop();
						// whether that's below
						if (y >= top) {
							// if so, add the fixed class
							$('#sidebar-inner').addClass('fixed');
						} else {
							// otherwise remove it
							$('#sidebar-inner').removeClass('fixed');
						}
						if (sidenavHeight > winHeight) { // if sidebar is taller than viewport...
							$('#sidebar-inner').addClass('static'); // remove fixed positioning.
						}
					});
<?php } ?>
<?php if ($zpmas_ss) { ?>
					$('#cycle ul').cycle({
						fx: '<?php echo $zpmas_sseffect; ?>',
						timeout: <?php echo $zpmas_ssspeed; ?>,
						pause: 1,
						next: '#rarr',
						prev: '#larr'
					});
					$('#cycle ul').css('display', 'block');
<?php } ?>
			}, false);
		</script>
		<?php
		if (strlen($zpmas_logo) > 0) {
			if (getOption('zpmas_logoheight') < 36) {
				$zpmas_logoheight = 36;
				$fixadjust = null;
			} else {
				$zpmas_logoheight = getOption('zpmas_logoheight');
				$fixadjust = ($zpmas_logoheight - 36) + 101;
			}
			?>
			<style>
				h1#logo.image-logo{height:<?php echo $zpmas_logoheight; ?>px;background:url('<?php echo $_zp_themeroot; ?>/images/<?php echo $zpmas_logo; ?>') no-repeat;}
				#sidebar{margin-top:<?php echo $fixadjust; ?>px;}
				h1#logo.image-logo a{height:<?php echo $zpmas_logoheight; ?>px;}
			</style>
		<?php } ?>
		<style>
			#cycle li {width:<?php echo $zpmas_ss_size_w; ?>px;height:<?php echo $zpmas_ss_size_h; ?>px;}
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('#full-image img').each(function () {
					$(this).removeAttr('width')
					$(this).removeAttr('height');
				});
			});
<?php if (getOption('zp_plugin_reCaptcha')) { ?>
				var RecaptchaOptions = {
					theme: <?php
	if ($zpmas_css == 'dark') {
		echo '\'blackglass\'';
	} else {
		echo '\'white\'';
	}
	?>
				};
<?php } ?>
		</script>
		<?php if (getOption('zpmas_customcss') != null) { ?>
			<style>
	<?php echo getOption('zpmas_customcss'); ?>
			</style>
		<?php } ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="header">
			<?php include ("inc-menu.php"); ?>
			<div id="header-inner">
				<?php
				if ($zpmas_css == 'dark') {
					printSearchForm('', 'searchform', '', gettext('Search'), "$_zp_themeroot/images/media-eject-inv.png", null, null, null);
				} else {
					printSearchForm('', 'searchform', '', gettext('Search'), "$_zp_themeroot/images/media-eject.png", null, null, null);
				}
				?>
				<h1 id="logo"<?php if (strlen($zpmas_logo) > 0) echo ' class="image-logo"'; ?>><a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo getGalleryTitle(); ?></a></h1>
			</div>
		</div>