<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>

		<?php zp_apply_filter('theme_head'); ?>

		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery')); ?>
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
					<?php
					printCodeblock(1);
					while (next_album()):
						?>
						<div class="album">
							<div class="thumb">
								<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
							</div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small><?php printAlbumDate(""); ?></small>
								<div><?php printAlbumDesc(); ?></div>
							</div>
							<p style="clear: both; "></p>
						</div>
<?php endwhile; ?>

				</div>
				<br class="clearall">
				<?php
				printCodeblock(2);
				printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
				$pages = $news = NULL;
				if (extensionEnabled('zenpage')) {
					$news = getNumNews();
					$pages = getNumPages();
				}
				if ($pages || $news) {
					?>
					<br /><hr />
					<?php
					if ($news) {
						?>
						<span class="zp_link">
							<?php
							printCustomPageURL(NEWS_LABEL, 'news');
							?>
						</span>
						<?php
					}
					if ($pages) {
						$pages = $_zp_CMS->getPages(NULL, true); // top level only
						foreach ($pages as $item) {
							$pageobj = newPage($item['titlelink']);
							?>
							<span class="zp_link">
								<a href="<?php echo $pageobj->getLink(); ?>"><?php echo html_encode($pageobj->getTitle()); ?></a>
							</span>
							<?php
						}
					}
				}
				?>
			</div>
		</div>
		<div id="credit">
			<?php
			if (function_exists('printFavoritesURL')) {
				printFavoritesURL(NULL, '', ' | ', '<br />');
			}
			?>
			<?php @call_user_func('printUserLogin_out', '', ' | '); ?>
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
			<?php
			if (extensionEnabled('contact_form')) {
				printCustomPageURL(gettext('Contact us'), 'contact', '', '', ' | ');
			}
			?>
			<?php
			if (!zp_loggedin() && function_exists('printRegisterURL')) {
				printRegisterURL(gettext('Register for this site'), '', ' | ');
			}
			?>
		<?php printSoftwareLink(); ?>
		</div>
		<?php @call_user_func('mobileTheme::controlLink'); ?>
		<?php @call_user_func('printLanguageSelector'); ?>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>