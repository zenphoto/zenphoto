<?php if (!defined('WEBPATH')) die(); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php echo gettext("Password required"); ?></title>
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
	</head>

	<body>
		<?php printPasswordForm($hint, $show); ?>
		<div id="credit">
			<?php printZenphotoLink(); ?>
		</div>
	</body>
</html>
