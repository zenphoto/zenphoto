<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
				echo ' | ' . html_encode(getBareAlbumTitle());
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
				echo ' | ' . html_encode(getBareAlbumTitle()) . ' | ' . html_encode(getBareImageTitle());
			}
			if (($_zp_gallery_page == 'news.php') && (!is_NewsArticle())) {
				echo ' | ' . gettext('News');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}
			}
			if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {
				echo ' | ' . html_encode(getBareNewsTitle());
			}
			if ($_zp_gallery_page == 'pages.php') {
				echo ' | ' . html_encode(getBarePageTitle());
			}
			if ($_zp_gallery_page == 'password.php') {
				echo ' | ' . gettext('Password Required...');
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
				printRSSHeaderLink('Gallery', gettext('Latest images RSS'));
			}
			if (($_zenpage_enabled) && (getOption('RSS_articles'))) {
				printRSSHeaderLink('News', gettext('Latest news'));
			}
		}
		?>

		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/bootstrap.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/flexslider.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/screen.css" type="text/css" media="screen" />

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- fav and touch icons -->
		<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" />
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $_zp_themeroot; ?>/images/apple-touch-icon-114-precomposed.png" />
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $_zp_themeroot; ?>/images/apple-touch-icon-72-precomposed.png" />
		<link rel="apple-touch-icon-precomposed" href="<?php echo $_zp_themeroot; ?>/images/apple-touch-icon-57-precomposed.png" />

		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.flexslider-min.js"></script>
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpBootstrap.js"></script>

		<?php if (($_zp_gallery_page == 'index.php') && ($isHomePage)) { ?>
			<script type="text/javascript">
				//<![CDATA[
				$(window).load(function() {
				$('.flexslider').flexslider({
				slideshowSpeed: 5000,
								animationDuration: 500,
								randomize: true,
								pauseOnAction: false,
								pauseOnHover: true
				});
				});
				//]]>
			</script>
		<?php } ?>

		<?php if (($_zp_gallery_page == 'image.php') || ($_zenpage_enabled && is_NewsArticle())) { ?>
			<script type="text/javascript">
				//<![CDATA[
	<?php
	$NextURL = $PrevURL = false;
	if ($_zp_gallery_page == 'image.php') {
		if (hasNextImage()) {
			?>var nextURL = "<?php
			echo html_encode(getNextImageURL());
			$NextURL = true;
			?>";<?php
		}
		if (hasPrevImage()) {
			?>var prevURL = "<?php
			echo html_encode(getPrevImageURL());
			$PrevURL = true;
			?>";<?php
		}
	} else {
		if ($_zenpage_enabled && is_NewsArticle()) {
			if (getNextNewsURL()) {
				$article_url = getNextNewsURL();
				?>var nextURL = "<?php
				echo html_decode($article_url['link']);
				$NextURL = true;
				?>";<?php
			}
			if (getPrevNewsURL()) {
				$article_url = getPrevNewsURL();
				?>var prevURL = "<?php
				echo html_decode($article_url['link']);
				$PrevURL = true;
				?>";<?php
			}
		}
	}
	?>

				var ColorboxActive = false; // cohabitation entre script de navigation et colorbox

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
				// cohabitation entre script de navigation et colorbox
				$(document).bind('cbox_open', function() {ColorboxActive = true; })
								$(document).bind('cbox_closed', function() {ColorboxActive = false; });
				//]]>
			</script>
		<?php } ?>

	</head>

	<body>
		<?php
		zp_apply_filter('theme_body_open');

		if (($_zp_gallery_page == 'gallery.php') ||
						($_zp_gallery_page == 'album.php') ||
						($_zp_gallery_page == 'image.php')) {
			$galleryactive = true;
		} else {
			$galleryactive = false;
		}
		?>

		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="<?php echo html_encode(getMainSiteURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo getMainSiteName(); ?></a>
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<i class="icon-list icon-white"></i>
					</a>
					<div class="nav-collapse">
						<ul class="nav pull-right">
							<?php if (getOption('zpB_homepage')) { ?>
								<li<?php if ((isset($isHomePage)) && ($isHomePage)) { ?> class="active"<?php } ?>><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a></li>
								<li<?php if ($galleryactive) { ?> class="active"<?php } ?>><?php printCustomPageURL(gettext('Gallery'), 'gallery'); ?></li>
							<?php } else { ?>
								<?php
								if ($_zp_gallery_page == 'index.php') {
									$galleryactive = true;
								}
								?>
								<li<?php if ($galleryactive) { ?> class="active"<?php } ?>><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext('Gallery'); ?></a></li>
							<?php } ?>
							<?php if (($_zenpage_enabled) && ((getNumNews(true)) > 0)) { ?>
								<li<?php if ($_zp_gallery_page == 'news.php') { ?> class="active"<?php } ?>><?php printNewsIndexURL(gettext('News'), '', gettext('News')); ?></li>
							<?php } ?>
							<?php if ($_zenpage_enabled) { ?>
								<?php printPageMenu('list-top', '', 'active', '', '', '', 0, false); ?>
							<?php } ?>
							<?php if ((zp_loggedin()) && (extensionEnabled('favoritesHandler'))) { ?>
								<li<?php if ($_zp_gallery_page == 'favorites.php') { ?> class="active"<?php } ?>> <?php printFavoritesURL(); ?></li>
<?php } ?>
<?php if (extensionEnabled('contact_form')) { ?>
								<li<?php if ($_zp_gallery_page == 'contact.php') { ?> class="active"<?php } ?>><?php printCustomPageURL(gettext('Contact'), 'contact'); ?></li>
<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="wrap">
			<div class="container main">
				<div class="page-header">

<?php if (extensionEnabled('dynamic-locale')) { ?>
						<div id="flag" class="pull-right"><?php printLanguageSelector(); ?></div>
						<div class="clearfix"></div>
<?php } ?>

					<div class="pull-right">
<?php if (getOption('zpB_social_links')) { ?>
							<!-- AddThis Button BEGIN -->
							<div class="addthis_toolbox addthis_default_style addthis_32x32_style" style="float: right;">
								<a class="addthis_button_facebook"></a>
								<a class="addthis_button_twitter"></a>
								<a class="addthis_button_google_plusone_badge"></a>
								<!--<a class="addthis_button_favorites"></a>-->
								<a class="addthis_button_compact"></a>
							</div>
							<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>
							<!-- AddThis Button END -->
							<?php
						}

						$rss = false;
						if (extensionEnabled('rss')) {
							if (($_zenpage_enabled) && (getOption('RSS_articles'))) {
								printRSSLink('News', '', '', '', false, 'rss');
								$rss = true;
							} else if (getOption('RSS_album_image')) {
								printRSSLink('Gallery', '', '', '', false, 'rss');
								$rss = true;
							}
						}
						if ($rss) {
							?>
							<script type="text/javascript">
				//<![CDATA[
				$('.rss').prepend('<img alt="RSS Feed" src="<?php echo $_zp_themeroot; ?>/images/feed_icon.png">');
				//]]>
							</script>
<?php } ?>
					</div>