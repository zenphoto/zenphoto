

<html lang="<?php echo getOption('locale'); ?>">

	<head>

		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,300,600' rel='stylesheet' type='text/css'>

		<?php $searchwords = getSearchWords(); ?>

		<!-- meta -->

		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />

		<title><?php
			if ($_zp_gallery_page == 'index.php') {
				echo getGalleryTitle();
				echo ' | ';
			}
			if ($_zp_gallery_page == 'album.php') {
				echo getBareAlbumTitle();
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}echo ' | ';
			}
			if ($_zp_gallery_page == 'gallery.php') {
				echo gettext('Gallery');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}echo ' | ';
			}
			if ($_zp_gallery_page == '404.php') {
				echo gettext('Object not found');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'archive.php') {
				echo gettext('Archive View');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'contact.php') {
				echo gettext('Contact');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'favorites.php') {
				echo gettext('My favorites');
				if ($_zp_page > 1) {
					echo ' [' . $_zp_page . ']';
				}echo ' | ';
			}
			if ($_zp_gallery_page == 'image.php') {
				echo getBareImageTitle() . ' - photo from ' . getBareAlbumTitle();
				echo ' | ';
			}
			if (($_zp_gallery_page == 'news.php') && (is_NewsPage()) && (!is_NewsCategory()) && (!is_NewsArticle())) {
				echo gettext('Blog');
				echo ' | ';
			}
			if (($_zp_gallery_page == 'news.php') && (is_NewsCategory())) {
				printCurrentNewsCategory();
				echo ' | ';
				echo gettext('Blog');
				echo ' | ';
			}
			if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {
				echo getBareNewsTitle();
				echo ' | ';
				echo gettext('Blog');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'pages.php') {
				echo getBarePageTitle();
				echo ' | ';
			}
			if ($_zp_gallery_page == 'password.php') {
				echo gettext('Password required');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'register.php') {
				echo gettext('Register');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'credits.php') {
				echo gettext('Credits');
				echo ' | ';
			}
			if ($_zp_gallery_page == 'search.php') {
				echo html_encode($searchwords);
				echo ' | ';
			}
			echo getMainSiteName();
			?>
		</title>

		<?php
		if (isset($_GET["page"]) && $_zp_gallery_page == 'search.php') {
			echo '<meta name="robots" content="noindex, nofollow">';
		} elseif (isset($_GET["page"]) && $_zp_gallery_page != 'search.php' || $_zp_gallery_page == 'archive.php' || $_zp_gallery_page == 'favorites.php' || $_zp_gallery_page == 'password.php' || $_zp_gallery_page == 'register.php' || $_zp_gallery_page == 'contact.php') {
			echo '<meta name="robots" content="noindex, follow">';
		} else {
			echo '<meta name="robots" content="index, follow">';
		}
		?>


		<!-- Open Graph -->

		<meta property="og:title" content="<?php
		if (($_zp_gallery_page == 'index.php')) {
			echo gettext('Home') . ' | ';
		}
		if ($_zp_gallery_page == 'album.php') {
			echo getBareAlbumTitle();
			if ($_zp_page > 1) {
				echo ' [' . $_zp_page . ']';
			}echo ' | ';
		}
		if ($_zp_gallery_page == 'gallery.php') {
			echo gettext('Albums');
			if ($_zp_page > 1) {
				echo ' [' . $_zp_page . ']';
			}echo ' | ';
		}
		if ($_zp_gallery_page == '404.php') {
			echo gettext('Object not found');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'archive.php') {
			echo gettext('Archive View');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'contact.php') {
			echo gettext('Contact');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'favorites.php') {
			echo gettext('My favorites');
			if ($_zp_page > 1) {
				echo ' [' . $_zp_page . ']';
			}echo ' | ';
		}
		if ($_zp_gallery_page == 'image.php') {
			echo getBareImageTitle() . ' | ' . getBareAlbumTitle();
			echo ' | ';
		}
		if (($_zp_gallery_page == 'news.php') && (is_NewsPage()) && (!is_NewsCategory()) && (!is_NewsArticle())) {
			echo gettext('Blog');
			echo ' | ';
		}
		if (($_zp_gallery_page == 'news.php') && (is_NewsCategory())) {
			printCurrentNewsCategory();
			echo ' | ';
			echo gettext('Blog');
			echo ' | ';
		}
		if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {
			echo getBareNewsTitle();
			echo ' | ';
			echo gettext('Blog');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'pages.php') {
			echo getBarePageTitle();
			echo ' | ';
		}
		if ($_zp_gallery_page == 'password.php') {
			echo gettext('Password required');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'register.php') {
			echo gettext('Register');
			echo ' | ';
		}
		if ($_zp_gallery_page == 'search.php') {
			echo gettext('Search');
			if ($_zp_page > 1) {
				echo ' [' . $_zp_page . ']';
			} echo ' | ';
		}
		echo getMainSiteName();
		?>" />
		<meta property="og:type" content="article" />
		<meta property="og:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />
		<?php
		if ($_zp_gallery_page == 'image.php' && isImagePhoto()) {
			echo '<meta property="og:image" content="';
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo (getDefaultSizedImage());
			echo '" />
';
		}
		if ($_zp_gallery_page == 'image.php' && !isImagePhoto()) {
			echo '<meta property="og:image" content="';
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo (getImageThumb());
			echo '" />
';
		}
		if ($_zp_gallery_page == 'album.php') {
			echo '<meta property="og:image" content="';
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo getCustomAlbumThumb(Null, 650, 650);
			;
			echo '" />
';
		}
		if ($_zp_gallery_page == 'index.php') {
			echo '<meta property="og:image" content="';
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo $_zp_themeroot;
			echo '/img/logo.png" />
';
		}
		?>
		<?php
		if (($_zp_gallery_page == 'image.php') && getBareImageDesc() != '') {
			echo '<meta property="og:description" content="';
			echo getBareImageDesc();
			echo '"/>';
		}
		if (($_zp_gallery_page == 'album.php') && getBareAlbumDesc() != '') {
			echo '<meta property="og:description" content="';
			echo getBareAlbumDesc();
			echo '"/>';
		}
		?>
		<meta property="og:site_name" content="<?php echo getMainSiteName(); ?>" />


		<!-- twitter cards -->
		<?php if (($_zp_gallery_page == 'index.php')) { ?>
			<meta name="twitter:card" content="summary" />
			<meta name="twitter:site" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:title" content="<?php
			echo gettext('Home') . ' | ';
			echo getMainSiteName();
			?>" />
			<meta name="twitter:description" content="<?php
			echo getGalleryTitle();
			echo ' #zenphoto ';
			echo gettext('album')
			?>"  />
			<meta name="twitter:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />
		<?php } ?>
		<?php if (($_zp_gallery_page == 'image.php') && isImagePhoto()) { ?>
			<meta name="twitter:card" content="summary_large_image" />
			<meta name="twitter:site" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:creator" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:title" content="<?php printImageTitle(); ?>" />
			<meta name="twitter:description" content="<?php echo getBareImageDesc(); ?>" />
			<meta name="twitter:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />
			<meta name="twitter:image" content="<?php
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo (getDefaultSizedImage());
			?>" />
					<?php } ?>
					<?php if (($_zp_gallery_page == 'image.php') && !isImagePhoto()) { ?>
			<meta name="twitter:card" content="summary_large_image" />
			<meta name="twitter:site" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:creator" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:title" content="<?php printImageTitle(); ?>" />
			<meta name="twitter:description" content="<?php printImageDesc(); ?>" />
			<meta name="twitter:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />
			<meta name="twitter:image" content="<?php
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo (getImageThumb());
			?>" />
					<?php } ?>
					<?php if ($_zp_gallery_page == 'album.php') { ?>
			<meta name="twitter:card" content="summary_large_image" />
			<meta name="twitter:site" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:creator" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:title" content="<?php
			printAlbumTitle();
			echo (' ');
			echo gettext('album');
			?>" />
			<meta name="twitter:description" content="<?php echo getBareAlbumDesc(); ?>" />
			<meta name="twitter:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />
			<meta name="twitter:image" content="<?php
			echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
			echo getCustomAlbumThumb(Null, 650, 650);
			?>" />
					<?php } ?>
					<?php if ((($_zp_gallery_page == 'news.php') && (is_NewsArticle()))) { ?>
			<meta name="twitter:card" content="summary" />
			<meta name="twitter:site" content="<?php
			if (getOption('twitter_profile') != '') {
				echo '@';
				echo getOption('twitter_profile');
			}
			?>"/>
			<meta name="twitter:title" content="<?php echo getBareNewsTitle() ?>" />
			<meta name="twitter:url" content="<?php echo (PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]); ?>" />

		<?php } ?>


		<!-- css -->

		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/bootstrap.css" type="text/css" media="screen"/>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/site.css" type="text/css" media="screen"/>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/icons.css" type="text/css" media="screen"/>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/slimbox2.css" type="text/css" media="screen"/>


		<!-- favicon -->

		<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/img/favicon.ico">


		<!-- js -->

		<script src="<?php echo $_zp_themeroot; ?>/js/bootstrap.js" type="text/javascript" defer></script>
		<script src="<?php echo $_zp_themeroot; ?>/js/slimbox2-ar.js" type="text/javascript" defer></script>


		<!-- rss -->

		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>

		<?php zp_apply_filter('theme_head'); ?>


		<!-- Analytics -->

		<?php if (getOption('analytics_code') != '') { ?>
			<script>
				(function (i, s, o, g, r, a, m) {
					i['GoogleAnalyticsObject'] = r;
					i[r] = i[r] || function () {
						(i[r].q = i[r].q || []).push(arguments)
					}, i[r].l = 1 * new Date();
					a = s.createElement(o),
									m = s.getElementsByTagName(o)[0];
					a.async = 1;
					a.src = g;
					m.parentNode.insertBefore(a, m)
				})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

				ga('create', '<?php echo getOption('analytics_code'); ?>', 'auto');
				ga('set', 'contentGroup1', '<?php
		if (($_zp_gallery_page == 'index.php')) {
			echo '00 homepage';
		}
		if ($_zp_gallery_page == 'album.php') {
			echo '20 album';
		}
		if ($_zp_gallery_page == 'gallery.php') {
			echo '00 gallery-homepage';
		}
		if ($_zp_gallery_page == '404.php') {
			echo '90 error';
		}
		if ($_zp_gallery_page == 'archive.php') {
			echo '80 utility';
		}
		if ($_zp_gallery_page == 'contact.php') {
			echo '80 utility';
		}
		if ($_zp_gallery_page == 'favorites.php') {
			echo '80 utility';
		}
		if ($_zp_gallery_page == 'image.php') {
			echo '30 image';
		}
		if (($_zp_gallery_page == 'news.php') && (!is_NewsArticle())) {
			echo '40 news list';
		}
		if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {
			echo '45 news';
		}
		if (($_zp_gallery_page == 'pages.php') || ($_zp_gallery_page == 'credits.php')) {
			echo '10 page';
		}
		if ($_zp_gallery_page == 'password.php') {
			echo '80 utility';
		}
		if ($_zp_gallery_page == 'register.php') {
			echo '80 utility';
		}
		if ($_zp_gallery_page == 'search.php') {
			echo '50 tag';
		}
		?>');
				ga('set', 'contentGroup2', '<?php
		if ($_zp_gallery_page == 'album.php') {
			echo getAlbumTitle();
		}
		if ($_zp_gallery_page == 'image.php') {
			echo getAlbumTitle();
		}
		if ($_zp_gallery_page == 'news.php') {
			echo printCurrentNewsCategory();
		}
		?>');
				ga('send', 'pageview');

			</script>
		<?php } ?>

	</head>