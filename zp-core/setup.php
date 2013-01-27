<?php
if (is_dir(dirname(__FILE__).'/setup')) {
	header('Location: setup/index.php');
} else {
	require_once(dirname(__FILE__).'/version.php');
	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin.css" type="text/css" />
		<?php reconfigureCS(); ?>
		</head>
		<body>
			<div id="main">
				<div id="content">
					<div class="tabbox">
						<p>
						<?php printf(gettext('Please reinstall the setup files from the %1$s [%2$s] release:'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE); ?>
						</p>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
}
?>
