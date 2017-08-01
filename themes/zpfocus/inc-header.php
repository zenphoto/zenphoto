<?php if (!defined('WEBPATH')) die(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php
		zp_apply_filter('theme_head');
		// Set some things depending on what page we are on...
		switch ($_zp_gallery_page) {
			case 'index.php':
				if ($_zp_page > 1) {
					$metatitle = getBareGalleryTitle() . " ($_zp_page)";
				} else {
					$metatitle = getBareGalleryTitle();
				}
				$zpfocus_metatitle = $metatitle;
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				$galleryactive = true;
				break;
			case 'favorites.php':
			case 'album.php':
				if ($_zp_page > 1) {
					$metatitle = getBareAlbumTitle() . " ($_zp_page)";
				} else {
					$metatitle = getBareAlbumTitle();
				}
				$zpfocus_metatitle = $metatitle . getTitleBreadcrumb() . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareAlbumDesc(), 150, '...');
				$galleryactive = true;
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
				if (!$_zp_current_album->isDynamic()) {
					$titlebreadcrumb = getTitleBreadcrumb();
				} else {
					$titlebreadcrumb = '';
				}
				$zpfocus_metatitle = getBareImageTitle() . ' | ' . getBareAlbumTitle() . $titlebreadcrumb . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareImageDesc(), 150, '...');
				$galleryactive = true;
				if ((extensionEnabled('rss')) && (function_exists('printCommentForm')) && (getOption('RSS_comments'))) {
					printRSSHeaderLink('Comments-image', getBareImageTitle() . ' - ' . gettext('Latest Comments'), $lang = '') . "\n";
				}
				break;
			case 'archive.php':
				$zpfocus_metatitle = gettext("Archive View") . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'search.php':
				$zpfocus_metatitle = gettext('Search') . ' | ' . html_encode(getSearchWords()) . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				$galleryactive = true;
				break;
			case 'pages.php':
				$zpfocus_metatitle = getBarePageTitle() . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBare(getPageContent(), 150, '...'));
				break;
			case 'news.php':
				if (is_NewsArticle()) {
					$zpfocus_metatitle = gettext('News') . ' | ' . getBareNewsTitle() . ' | ' . getBareGalleryTitle();
					$zpfocus_metadesc = truncate_string(getBare(getNewsContent(), 150, '...'));
				} else if ($_zp_current_category) {
					$zpfocus_metatitle = gettext('News') . ' | ' . $_zp_current_category->getTitle() . ' | ' . getBareGalleryTitle();
					$zpfocus_metadesc = truncate_string(getBare(getNewsCategoryDesc(), 150, '...'));
				} else if (getCurrentNewsArchive()) {
					$zpfocus_metatitle = gettext('News') . ' | ' . getCurrentNewsArchive() . ' | ' . getBareGalleryTitle();
					$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				} else {
					$zpfocus_metatitle = gettext('News') . ' | ' . getBareGalleryTitle();
					$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				}
				break;
			case 'contact.php':
				$zpfocus_metatitle = gettext('Contact') . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'login.php':
				$zpfocus_metatitle = gettext('Login') . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'register.php':
				$zpfocus_metatitle = gettext('Register') . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case 'password.php':
				$zpfocus_metatitle = gettext('Password Required') . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			case '404.php':
				$zpfocus_metatitle = gettext('404 Not Found...') . ' | ' . getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
			default:
				$zpfocus_metatitle = getBareGalleryTitle();
				$zpfocus_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
		}
		// Finish out header RSS links for inc-header.php
		if (extensionEnabled('rss')) {
			if (getOption('RSS_items')) {
				printRSSHeaderLink('Gallery', gettext('Latest Images')) . "\n";
			}
			if (getOption('RSS_items_albums')) {
				printRSSHeaderLink('AlbumsRSS', gettext('Latest Albums')) . "\n";
			}
			if ($zenpage) {
				if (getOption('RSS_zenpage_items')) {
					printRSSHeaderLink('News', '', gettext('Latest News')) . "\n";
				}
				if (function_exists('printCommentForm')) {
					printRSSHeaderLink('Comments', '', gettext('Latest Comments')) . "\n";
				}
			}
		}
		?>

		<meta name="description" content="<?php echo html_encode($zpfocus_metadesc); ?>" />

		<?php require_once(SERVERPATH . '/' . ZENFOLDER . "/zp-extensions/print_album_menu.php"); ?>

		<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/main.css" />
		<?php if (getOption('zpfocus_center_site')) { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/center.css" />
		<?php } ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/print.css" media="print" />
		<!--[if lte IE 6]>
		<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/ie6.css" />
		<![endif]-->
		<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" />
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/superfish.js"></script>
		<script type="text/javascript">
			jQuery(function () {
				jQuery('ul.sf-menu').superfish();
			});
<?php if (getOption('zp_plugin_reCaptcha')) { ?>
				var RecaptchaOptions = {
					theme: 'white'
				};
<?php } ?>
		</script>
		<?php if (($zpfocus_showrandom) == 'rotator') { ?>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/slideshow/jquery.cycle.all.js" type="text/javascript"></script>
		<?php } ?>
		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/jquery.colorbox-min.js" type="text/javascript"></script>
		<link rel="stylesheet" href="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/themes/<?php echo $zpfocus_cbstyle; ?>/colorbox.css" type="text/css" media="screen"/>
		<script type="text/javascript">
			window.addEventListener('load', function () {
				$("a[rel='zoom']").colorbox({
					slideshow: false,
					slideshowStart: '<?php echo gettext('start slideshow'); ?>',
					slideshowStop: '<?php echo gettext('stop slideshow'); ?>',
					current: '<?php echo gettext('image {current} of {total}'); ?>', // Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
					previous: '<?php echo gettext('previous'); ?>',
					next: '<?php echo gettext('next'); ?>',
					close: '<?php echo gettext('close'); ?>',
					transition: '<?php echo $zpfocus_cbtransition; ?>',
					maxHeight: '90%',
					photo: true,
					maxWidth: '90%'
				});
				$("a[rel='slideshow']").colorbox({
					slideshow: true,
					slideshowSpeed:<?php echo $zpfocus_cbssspeed; ?>,
					slideshowStart: '<?php echo gettext('start slideshow'); ?>',
					slideshowStop: '<?php echo gettext('stop slideshow'); ?>',
					current: '<?php echo gettext('image {current} of {total}'); ?>', // Text format for the content group / gallery count. {current} and {total} are detected and replaced with actual numbers while ColorBox runs.
					previous: '<?php echo gettext('previous'); ?>',
					next: '<?php echo gettext('next'); ?>',
					close: '<?php echo gettext('close'); ?>',
					transition: '<?php echo $zpfocus_cbtransition; ?>',
					maxHeight: '90%',
					photo: true,
					maxWidth: '90%'
				});
				$(".inline").colorbox({width: "400px", inline: true, href: "#exif"});
<?php if (($zpfocus_showrandom) == 'rotator') { ?>
					$('#random-wrap ul').cycle({
						fx: '<?php echo $zpfocus_rotatoreffect; ?>',
						timeout: <?php echo $zpfocus_rotatorspeed; ?>,
						pause: 1
					});
<?php } ?>
				$('#random-wrap').css('display', 'block');
			}, false);
		</script>
		<?php
		if ($_zp_gallery_page == 'search.php') {
			printZDSearchToggleJS();
		}
		?>
		<?php if (getOption('zpfocus_customcss') != null) { ?>
			<style>
	<?php echo getOption('zpfocus_customcss'); ?>
			</style>
		<?php } ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="nav">
			<div id="nav-wrap">
				<ul class="sf-menu">
					<li class="nav-first"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext('Home'); ?></a></li>
					<?php if (($zpfocus_menutype) == 'dropdown') { ?>
						<?php if (($zpfocus_homepage) == 'none') { ?>
							<li><a class="placeholder"><?php echo gettext('Gallery'); ?></a>
							<?php } else { ?>
								<li><?php printCustomPageURL(gettext('Gallery'), "gallery"); ?>
								<?php } ?>
								<?php printAlbumMenuList('list', '', '', 'active', '', 'active', '', true, false, true, true, null); ?>
							</li>
						<?php } ?>
						<?php if (function_exists("printNestedMenu")) { ?>
							<li><a class="placeholder"><?php echo gettext('Pages'); ?></a>
								<?php printNestedMenu('list', 'pages', false, null, 'active', null, 'active', null, true, true, 30); ?>
							</li>
							<?php if ($zpfocus_news) { ?>
								<li><a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('News'); ?></a>
									<?php printNestedMenu('list', 'categories', false, null, 'active', null, 'active', null, true, true, 30); ?>
								</li>
							<?php } ?>
						<?php } ?>
						<?php if (function_exists('printContactForm')) { ?>
							<li><?php printCustomPageURL(gettext('Contact'), "contact"); ?></li>
						<?php } ?>

						<?php if ($zpfocus_show_archive) { ?>
							<li><?php printCustomPageURL(gettext('Archive'), "archive"); ?>
							<?php } ?>
							</ul>
							<?php if ($zpfocus_allow_search) { ?>
								<div>
									<?php printSearchForm('', 'searchform', '', gettext('SEARCH'), "$_zp_themeroot/images/search-drop.jpg", null, null, null); ?>
								</div>
							<?php } ?>
							<?php if (($zpfocus_menutype) == 'jump') { ?>
								<div id="jumpmenu">
									<?php printAlbumMenu('jump'); ?>
								</div>
							<?php } ?>
							</div>
							</div>
							<div class="wrap">
