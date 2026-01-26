<?php if (!defined('WEBPATH')) die(); ?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
	<head>
		<?php filter::applyFilter('theme_head'); ?>
		<title><?php echo gettext("Password required"); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/css/admin.css" type="text/css" />
	</head>

	<body>
		<?php printPasswordForm($hint, $show); ?>
		<div id="credit">
			<?php printZenphotoLink(); ?>
		</div>
	</body>
</html>
