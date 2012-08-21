<?php
if (file_exists(dirname(__FILE__).'/setup/index.php')) {
	header('Location: setup/index.php', true, 301);
} else {
	require_once(dirname(__FILE__).'/global-definitions.php');
	require_once(dirname(__FILE__).'/functions-basic.php');
	require_once(dirname(__FILE__).'/reconfigure.php');
	list($diff, $needs) = checkSignature();
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
						<?php printf(gettext('Please reinstall the following setup files from the %1$s [%2$s] release:'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE); ?>
							<div id="files">
								<ul>
									<?php
									if (!file_exists(dirname(__FILE__).'/setup.php')) {
									?>
										<li><?php echo ZENFOLDER; ?>/setup.php</li>
									<?php
									}
									if (!empty($needs)) {
											foreach ($needs as $script) {
											?>
											<li><?php echo ZENFOLDER; ?>/setup/<?php echo $script; ?></li>
											<?php
										}
									}
									?>
								</ul>
							</div>
						</p>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
	exit();
}
?>
