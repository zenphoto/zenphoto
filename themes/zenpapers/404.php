<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php printBareGalleryTitle(); ?> | <?php echo gettext("Object not found"); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/default/common.css" type="text/css" />
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		
			<p id="path">
		<?php printHomeLink('', ' > '); ?>
		<?php printGalleryTitle(); ?>
		| <?php echo gettext("Object not found"); ?>
		</p>    
		<div id="main">
			<div id="gallerytitle">

				<?php
				if (getOption('Allow_search')) {
					printSearchForm('');
				}
				?>
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
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
