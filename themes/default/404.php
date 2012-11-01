<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php printBareGalleryTitle(); ?> | <?php echo gettext("Object not found"); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/default/common.css" type="text/css" />
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<h2>
					<span>
						<?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle(); ?></a>
					</span> | <?php echo gettext("Object not found"); ?>
				</h2>
			</div>
			<div id="padbox">
				<?php
				echo gettext("The Zenphoto object you are requesting cannot be found.");
				if (isset($album)) {
					echo '<br />' . sprintf(gettext('Album: %s'), html_encode($album));
				}
				if (isset($image)) {
					echo '<br />' . sprintf(gettext('Image: %s'), html_encode($image));
				}
				if (isset($obj)) {
					echo '<br />' . sprintf(gettext('Page: %s'), html_encode(substr(basename($obj), 0, -4)));
				}
				?>
			</div>
		</div>
		<div id="credit">
			<?php printZenphotoLink(); ?>
		</div>
		<?php
		printAdminToolbox();
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
