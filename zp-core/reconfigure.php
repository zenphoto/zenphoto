<?php
/**
 * handles reconfiguration when the install signature has changed
 * @package core
 */
list($diff, $needs) = checkSignature();
$diff = array_keys($diff);
if (in_array('ZENPHOTO', $diff) || in_array('FOLDER', $diff)) {
	if (file_exists(dirname(__FILE__).'/setup.php') && empty($needs)) {
		$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		$p = strpos($dir, ZENFOLDER);
		if ($p !== false) {
			$dir = substr($dir, 0, $p);
		}
		if (OFFSET_PATH) {
			$where = 'admin';
		} else {
			$where = 'gallery';
		}
		if (substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
		$location = "http://". $_SERVER['HTTP_HOST']. $dir . "/" . ZENFOLDER . "/setup.php?autorun=$where";
		header("Location: $location" );
		exitZP();
	} else {
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
			<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin.css" type="text/css" />
			</head>
			<body>
				<div id="main">
					<div id="content">
						<div class="tabbox">
							<div class="notebox">
								<h1><?php echo gettext('Zenphoto has detected a change in your installation signature and needs to run Setup but some setup files are missing.'); ?></h1>
								<?php setupNeeds($needs); ?>
							</div>
						</div>
					</div>
				</div>
			</body>
		</html>
		<?php
		db_close();
		exit();
	}
} else {
	zp_register_filter('admin_note', 'signatureChange');
}

function checkSignature() {
	$old = @unserialize(getOption('zenphoto_install'));
	if (!is_array($old)) {
		$old = array();
	}
	$new = installSignature();
	$reconfigure = true;
	$diff = array_diff_assoc($old, $new);
	$package = file_get_contents(dirname(__FILE__).'/Zenphoto.package');
	preg_match_all('|'.ZENFOLDER.'/setup/(.*)|', $package, $matches);
	chdir(dirname(__FILE__).'/setup/');
	$found = safe_glob('*.*');
	$needs = array_diff($matches[1], $found);
	return array($diff, $needs);
}

function signatureChange($tab, $subtab) {
	list($diff, $needs) = checkSignature();
	?>
	<div class="notebox">
		<h1>
			<?php
			echo gettext('Zenphoto has detected a change in your installation.');
			?>
		</h1>
		<ul>
		<?php
			foreach ($diff as $thing=>$old) {

				switch ($thing) {
					case 'SERVER_SOFTWARE':
						echo '<li>'.sprintf(gettext('Your server software has changed from %1$s to %2$s.'),$old,$_SERVER['SERVER_SOFTWARE']).'</li>';
						break;
					case 'DATABASE':
						$dbs = db_software();
						echo '<li>'.sprintf(gettext('Your database software has changed from %1$s to %2$s.'),$old,$dbs['application'].' '.$dbs['version']).'</li>';
						break;
					default:
						$sz = filesize(SERVERPATH.'/'.ZENFOLDER.'/'.$thing);
						echo '<li>'.sprintf(gettext('The size of <code>%1$s</code> has changed from %2$s to %3$s.'),$thing,$old, $sz).'</li>';
						break;
				}
			}
			?>
		</ul>
		<p>
		<?php
			echo gettext('These changes may not be critical but you should run setup at your earliest convenience.');
			if (!file_exists(dirname(__FILE__).'/setup.php') || !empty($needs)) {
				setupNeeds($needs);
			}
		?>
		</p>
	</div>
	<?php
	return $tab;
}

function setupNeeds($needs) {
	?>
	<h2><?php printf(gettext('Please reinstall the following setup files from the %1$s [%2$s] release:'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE); ?></h2>
	<ul>
		<?php
		if (!file_exists(dirname(__FILE__).'/setup.php')) {
		?>
		<li><?php echo ZENFOLDER; ?>
			<ul>
				<li>setup.php</li>
			</ul>
		</li>
		<?php
		}
		if (!empty($needs)) {
			?>
			<li>
				<?php echo ZENFOLDER; ?>/setup/
				<ul>
					<?php
					foreach ($needs as $script) {
						?>
						<li><?php echo $script; ?></li>
						<?php
					}
					?>
				</ul>
			</li>
			<?php
		}
		?>
	</ul>
<?php
}

?>