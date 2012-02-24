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
								<?php reconfigurePage($needs); ?>
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
	if (zp_loggedin(ADMIN_RIGHTS)) {
		zp_register_filter('theme_head', 'reconfigureCS');
		zp_register_filter('theme_body_open', 'signatureChange');
	}
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

function signatureChange($tab=NULL, $subtab=NULL) {
	reconfigurePage();
	return $tab;
}

function reconfigureCS() {
	?>
	<style type="text/css">
	.notebox {
		padding: 5px 10px 5px 10px;
		background-color: #FFEFB7;
		border-width: 1px 1px 2px 1px;
		border-color: #FFDEB5;
		border-style: solid;
		margin-bottom: 10px;
		font-size: 100%;
		-moz-border-radius: 5px;
		-khtml-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
	}

	.notebox li {
		list-style-type: none;
	}

	.notebox h2,.notebox strong {
		color: #663300;
		font-size: 100%;
		font-weight: bold;
		margin-bottom: 1em;
	}
	</style>
	<?php
}

function reconfigurePage() {
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
					case 'ZENPHOTO':
						echo '<li>'.sprintf(gettext('Zenphoto %1$s has been installed over %2$s.'),$old,ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.']').'</li>';
						break;
					case 'FOLDER':
						echo '<li>'.sprintf(gettext('Your installation has moved from %1$s to %2$s.'),$old,dirname(SERVERPATH.'/'.ZENFOLDER)).'</li>';
						break;
					default:
						$sz = @filesize(SERVERPATH.'/'.ZENFOLDER.'/'.$thing);
						echo '<li>'.sprintf(gettext('The size of <code>%1$s</code> has changed from %2$s to %3$s.'),$thing,$old, $sz).'</li>';
						break;
				}
			}
			?>
		</ul>
		<p>
		<?php
			if (!file_exists(dirname(__FILE__).'/setup.php') || !empty($needs)) {
				?>
				<p>
				<?php printf(gettext('Please reinstall the following setup files from the %1$s [%2$s] release:'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE); ?>
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
				</p>
			<?php
			if (empty($needs)) {
				$l1 = $l2 = '';
			} else {
				$l1 = '<a href="'.WEBPATH.'/'.ZENFOLDER.'/setup.php">';
				$l2 = '</a>';
			}
			printf(gettext('These changes may not be critical but you should run %1$ssetup%2$s at your earliest convenience.'), $l1, $l2);
			}
		?>
		</p>
	</div>
<?php
}
?>