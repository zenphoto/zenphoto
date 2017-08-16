<?php
/**
 * link to setup
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(dirname(__FILE__) . '/reconfigure.php');

if (isset($_GET['xsrfToken']) && $_GET['xsrfToken'] == getXSRFToken('setup')) {
	$must = 5;
} else {
	$must = 0;
}
list($diff, $needs, $found) = checkSignature($must);

if (empty($needs)) {
	header('Location: setup/index.php');
} else {
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Type: text/html; charset=utf-8');
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
			<?php reconfigureCS(); ?>
		</head>
		<?php
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			// If they are not logged in, display the login form and exit
			?>
			<body style="background-image: none">
				<?php $_zp_authority->printLoginForm(); ?>
			</body>
			<?php
			echo "\n</html>";
			exitZP();
		}
		?>
		<body>
			<?php printLogoAndLinks(); ?>
			<div id="main">
				<div id="content">
					<h1><?php echo gettext('Setup request'); ?></h1>
					<div class="tabbox">
						<p>
							<?php
							if (zpFunctions::hasPrimaryScripts()) {
								if ($found) {
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
