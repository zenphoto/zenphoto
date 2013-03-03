<?php
require_once(dirname(__FILE__).'/functions-basic.php');
require_once(dirname(__FILE__).'/reconfigure.php');
list($diff, $needs) = checkSignature();
if (empty($needs)) {
	header('Location: setup/index.php');
} else {
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Type: text/html; charset=utf-8');
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
