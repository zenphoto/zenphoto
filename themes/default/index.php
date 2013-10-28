<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . THEMEFOLDER; ?>/default/common.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<?php
				if (getOption('Allow_search')) {
					printSearchForm('');
				}
				?>
				<h2><?php
					printHomeLink('', ' | ');
					printGalleryTitle();
					?></h2>
			</div>
			<div id="padbox">
				<?php printGalleryDesc(); ?>
				<div id="albums">
					<?php while (next_album()): ?>
						<div class="album">
							<div class="thumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
							</div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small><?php printAlbumDate(""); ?></small>
								<div><?php printAlbumDesc(); ?></div>
							</div>
							<p style="clear: both; "></p>
						</div>
					<?php endwhile; ?>
				</div>
				<br class="clearall" />
				<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
			</div>
		</div>
		<div id="credit">
			<?php @call_user_func('printUserLogin_out', '', ' | '); ?>
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
			<?php
			if (extensionEnabled('contact_form')) {
				printCustomPageURL(gettext('Contact us'), 'contact', '', '', ' | ');
			}
			?>
			<?php
			if (!zp_loggedin() && function_exists('printRegistrationForm')) {
				printCustomPageURL(gettext('Register for this site'), 'register', '', '', ' | ');
			}
			?>
			<?php
			if (function_exists('printFavoritesLink')) {
				printFavoritesLink();
				?> | <?php
			}
			?>
			<?php printZenphotoLink(); ?>
		</div>
		<?php @call_user_func('mobileTheme::controlLink'); ?>
		<?php @call_user_func('printLanguageSelector'); ?>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>