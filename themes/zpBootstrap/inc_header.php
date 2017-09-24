<?php
// force UTF-8 Ø
if ( !defined('WEBPATH') ) die();
?>
<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
	<?php
	if ( !( (($_zp_gallery_page == 'pages.php') && ((getPageTitleLink() == 'map')) ) || ($_zp_gallery_page == 'album.php')) ) {
		zp_remove_filter('theme_head', 'GoogleMap::js');
	}
	zp_apply_filter('theme_head');
	?>
	<title>
	<?php
	echo getMainSiteName();
	if (($_zp_gallery_page == 'index.php') && ($isHomePage)) {echo ' | ' . gettext('Home'); }
	if (($_zp_gallery_page == 'index.php') && (!$isHomePage)) {echo ' | ' . gettext('Gallery'); }
	if ($_zp_gallery_page == '404.php') {echo ' | ' . gettext('Object not found'); }
	if ($_zp_gallery_page == 'album.php') {echo ' | ' . getBareAlbumTitle(); if ($_zp_page > 1) {echo ' [' . $_zp_page . ']'; }}
	if ($_zp_gallery_page == 'archive.php') {echo ' | ' . gettext('Archive View'); }
	if ($_zp_gallery_page == 'contact.php') {echo ' | ' . gettext('Contact'); }
	if ($_zp_gallery_page == 'favorites.php') {echo ' | ' . gettext('My favorites'); if ($_zp_page > 1) {echo ' [' . $_zp_page . ']'; }}
	if ($_zp_gallery_page == 'gallery.php') {echo ' | ' . gettext('Gallery'); if ($_zp_page > 1) {echo ' [' . $_zp_page . ']'; }}
	if ($_zp_gallery_page == 'image.php') {echo ' | ' . getBareAlbumTitle() . ' | ' . getBareImageTitle(); }
	if (($_zp_gallery_page == 'news.php') && (!is_NewsArticle())) {echo ' | ' . gettext('News'); if ($_zp_page > 1) {echo ' [' . $_zp_page . ']';} }
	if (($_zp_gallery_page == 'news.php') && (is_NewsArticle())) {echo ' | ' . getBareNewsTitle(); }
	if ($_zp_gallery_page == 'pages.php') {echo ' | ' . getBarePageTitle(); }
	if ($_zp_gallery_page == 'password.php') {echo ' | ' . gettext('Password Required...'); }
	if ($_zp_gallery_page == 'register.php') {echo ' | ' . gettext('Register'); }
	if ($_zp_gallery_page == 'search.php') {echo ' | ' . gettext('Search'); if ($_zp_page > 1) {echo ' [' . $_zp_page . ']';} }
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

	<link rel="shortcut icon" href="<?php echo $_zp_themeroot; ?>/images/favicon.ico" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/bootstrap.min.css" type="text/css" media="screen" />
	<?php if (($_zp_gallery_page == 'index.php') && ($isHomePage)) { ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/flexslider.css" type="text/css" media="screen" />
	<?php } ?>
	<?php if (($_zp_gallery_page == 'album.php') || ($_zp_gallery_page == 'favorites.php') || ($_zp_gallery_page == 'news.php') || ($_zp_gallery_page == 'pages.php') || ($_zp_gallery_page == 'search.php')) { ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/jquery.fancybox.min.css" type="text/css" media="screen"/>
	<?php } ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/zpBootstrap.css" type="text/css" media="screen" />

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpBootstrap.js"></script>

	<?php if (($_zp_gallery_page == 'index.php') && ($isHomePage)) { ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.flexslider-min.js"></script>
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function() {
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

	<?php if (($_zp_gallery_page == 'album.php') || ($_zp_gallery_page == 'favorites.php') || ($_zp_gallery_page == 'news.php') || ($_zp_gallery_page == 'pages.php') || ($_zp_gallery_page == 'search.php')) { ?>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/jquery.fancybox.min.js"></script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/js/zpB_fancybox_config.js"></script>
	<script type="text/javascript">
	//<![CDATA[
		$(document).ready(function() {
			$.fancybox.defaults.lang = '<?php $loc = substr(getOption('locale'), 0, 2); if (empty($loc)) {$loc = 'en';}; echo $loc; ?>';
			$.fancybox.defaults.i18n = {
				'<?php echo $loc; ?>' : {
					CLOSE		: '<?php echo gettext("close"); ?>',
					NEXT		: '<?php echo gettext("next"); ?>',
					PREV		: '<?php echo gettext("prev"); ?>',
					PLAY_START	: '<?php echo gettext("start slideshow"); ?>',
					PLAY_STOP	: '<?php echo gettext("stop slideshow"); ?>'
				}
			};

			// cohabitation between keyboard Navigation and Fancybox
			$.fancybox.defaults.onInit = function() {FancyboxActive = true;};
			$.fancybox.defaults.afterClose = function() {FancyboxActive = false;};
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
			if (hasNextImage()) { ?>var nextURL = "<?php echo html_encode(getNextImageURL()); $NextURL = true; ?>";<?php }
			if (hasPrevImage()) { ?>var prevURL = "<?php echo html_encode(getPrevImageURL()); $PrevURL = true; ?>";<?php }
		} else {
			if ($_zenpage_enabled && is_NewsArticle()) {
				if (getNextNewsURL()) { $article_url = getNextNewsURL(); ?>var nextURL = "<?php echo html_decode($article_url['link']); $NextURL = true; ?>";<?php }
				if (getPrevNewsURL()) { $article_url = getPrevNewsURL(); ?>var prevURL = "<?php echo html_decode($article_url['link']); $PrevURL = true; ?>";<?php }
			}
		} ?>

		// cohabitation between keyboard Navigation and Fancybox
		var FancyboxActive = false;

		function keyboardNavigation(e) {
			// keyboard Navigation disabled if Fancybox active
			if (FancyboxActive) return true;

			if (!e) e = window.event;
			if (e.altKey) return true;
			var target = e.target || e.srcElement;
			if (target && target.type) return true;		//an input editable element
			var keyCode = e.keyCode || e.which;
			var docElem = document.documentElement;
			switch(keyCode) {
				case 63235: case 39:
					if (e.ctrlKey || (docElem.scrollLeft == docElem.scrollWidth-docElem.clientWidth)) {
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

	<nav id="menu" class="navbar navbar-inverse navbar-static-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo html_encode(getMainSiteURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo getMainSiteName(); ?></a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav pull-right">
				<?php if (getOption('zpB_homepage')) { ?>
					<li<?php if ((isset($isHomePage)) && ($isHomePage)) { ?> class="active"<?php } ?>><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a></li>
					<li<?php if ($galleryactive) { ?> class="active"<?php } ?>><?php printCustomPageURL(gettext('Gallery'), 'gallery'); ?></li>
				<?php } else { ?>
				<?php if ($_zp_gallery_page == 'index.php') { $galleryactive = true; } ?>
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
				<?php if (getOption('zpB_allow_search')) { ?>
					<li id="look"<?php if ($_zp_gallery_page == 'archive.php') { ?> class="active"<?php } ?>><a id="search-icon" class="text-center" href="<?php echo getCustomPageURL('archive'); ?>" title="<?php echo gettext('Search'); ?>"><span class="glyphicon glyphicon-search"></span></a></li>
				<?php } ?>
				<?php if ((extensionEnabled('user_login-out')) || (extensionEnabled('register_user'))) { ?>
					<?php if ((extensionEnabled('user_login-out')) && (zp_loggedin())) { ?>
					<li id="admin"><?php printUserLogin_out(); ?></li>
					<?php } else if ( (!zp_loggedin()) && ( ( (extensionEnabled('user_login-out')) && ($_zp_gallery_page <> 'password.php') && ($_zp_gallery_page <> 'register.php') ) || ( extensionEnabled('register_user') ) ) ) { ?>
					<li id="admin" class="dropdown">
						<a href="#" class="dropdown-toggle text-center" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon glyphicon-user"></span>&nbsp;&nbsp;<span class="glyphicon glyphicon-chevron-down"></span></a>
						<ul class="dropdown-menu">
							<?php if ((!zp_loggedin()) && (extensionEnabled('user_login-out')) && ($_zp_gallery_page <> 'password.php') && ($_zp_gallery_page <> 'register.php')) { ?>
							<li>
								<a href="#login-modal" class="logonlink" data-toggle="modal" title="<?php echo gettext('Login'); ?>"><?php echo gettext('Login'); ?></a>
							</li>
							<?php } ?>
							<?php if ((!zp_loggedin()) && (extensionEnabled('register_user'))) { ?>
							<li>
								<?php printRegisterURL(gettext('Register')); ?>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
				<?php } ?>
				<?php if (extensionEnabled('dynamic-locale')) { ?>
					<li id="flags" class="dropdown">
						<?php printLanguageSelector(); ?>
					</li>
				<?php } ?>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</nav><!--/.navbar -->

	<?php if ((extensionEnabled('user_login-out')) && (!zp_loggedin()) && ($_zp_gallery_page <> 'password.php') && ($_zp_gallery_page <> 'register.php')) { ?>
	<div id="login-modal" class="modal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<?php printPasswordForm('', true, false); ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<!-- The scroll to top feature -->
	<div class="scroll-to-top">
		<span class="glyphicon glyphicon-chevron-up"></span>
	</div>

	<div id="main" class="container">
		<div class="page-header row">
			<?php if ((extensionEnabled('rss')) || (getOption('zpB_social_links'))) { ?>
			<div class="col-sm-push-9 col-sm-3">
				<?php if (extensionEnabled('rss')) {
					$rss = false;
					if (($_zenpage_enabled) && (getOption('RSS_articles'))) {
						$rss = true; $type = 'News';
					} else if (getOption('RSS_album_image')) {
						$rss = true; $type = 'Gallery';
					}
					if ($rss) { ?>
					<div class="feed pull-right">
						<?php printRSSLink($type, '', '', '', false, 'rss'); ?>
					</div>
					<script type="text/javascript">
					//<![CDATA[
						$('.rss').prepend('<img alt="RSS Feed" src="<?php echo $_zp_themeroot; ?>/images/feed_icon.png">');
					//]]>
					</script>
					<?php } ?>
				<?php } ?>

				<?php if (getOption('zpB_social_links')) { ?>
				<div class="addthis pull-right">
					<!-- AddThis Button BEGIN -->
					<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
						<a class="addthis_button_facebook"></a>
						<a class="addthis_button_twitter"></a>
						<!--<a class="addthis_button_favorites"></a>-->
						<a class="addthis_button_compact"></a>
					</div>
					<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js"></script>
					<!-- AddThis Button END -->
				</div>
				<?php } ?>
			</div>
			<?php } ?>

			<?php
			if ((extensionEnabled('rss')) || (getOption('zpB_social_links'))) {
				$col_header = ' col-sm-pull-3 col-sm-9';
			} else {
				$col_header = '';
			}
			?>

			<div class="header<?php echo $col_header; ?>">