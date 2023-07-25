<?php
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(dirname(__FILE__) . '/functions/functions-reconfigure.php');

list($diff, $needs) = checkSignature(isset($_GET['xsrfToken']) && $_GET['xsrfToken'] == getXSRFToken('setup'));
if (empty($needs)) {
	header('Location: setup/index.php');
} else {
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Type: text/html; charset=utf-8');
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin.css" type="text/css" />
			<?php reconfigureCSS(); ?>
		</head>
		<body>
			<div id="main">
				<div id="content">
					<div class="tabbox">
						<p>
							<?php
							if (hasPrimaryScripts()) {
								chdir(dirname(__FILE__) . '/setup/');
								$found = safe_glob('*.xxx');
								if ($found && zp_loggedin(ADMIN_RIGHTS)) {
									echo '<a href="' . WEBPATH . '/' . ZENFOLDER . '/setup.php?xsrfToken=' . getXSRFToken('setup') . '">' . gettext('Click to restore the setup scripts and run setup.') . '</a>';
								} else {
									printf(gettext('You must restore the setup files from the %1$s release.'), ZENPHOTO_VERSION);
								}
							} else {
								echo gettext('You must restore the setup files on your primary installation to run the setup operation.');
							}
							?>
						</p>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
}
?>
