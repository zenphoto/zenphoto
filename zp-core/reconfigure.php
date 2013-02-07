<?php
/**
 * handles reconfiguration when the install signature has changed
 * @package core
 */

/**
 *
 * Executes the configuration change code
 */
function reconfigureAction($mandatory) {
	list($diff, $needs) = checkSignature();
	$diff = array_keys($diff);
	if ($mandatory || in_array('ZENPHOTO', $diff) || in_array('FOLDER', $diff)) {
		if (isset($_GET['rss'])) {
			exit();	//	can't really run setup from an RSS feed.
		}

		if (empty($needs)) {
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
			$dir = rtrim($dir,'/');
			$location = "http://". $_SERVER['HTTP_HOST']. $dir . "/" . ZENFOLDER . "/setup/index.php?autorun=$where";
			header("Location: $location" );
			exitZP();
		} else {
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
								<?php reconfigurePage($needs); ?>
							</div>
						</div>
					</div>
				</body>
			</html>
			<?php
			exitZP();
		}
	} else {
		if (function_exists('zp_register_filter')) {
			zp_register_filter('admin_note', 'signatureChange');
			zp_register_filter('admin_head', 'reconfigureCS');
			if (zp_loggedin(ADMIN_RIGHTS)) {
				zp_register_filter('theme_head', 'reconfigureCS');
				zp_register_filter('theme_body_open', 'signatureChange');
			}
		}
	}
}

/**
 *
 * Checks details of configuration change
 */
function checkSignature() {
	global $_zp_DB_connection;
	if (function_exists('query_full_array') && $_zp_DB_connection) {
		$old = @unserialize(getOption('zenphoto_install'));
		$new = installSignature();
	} else {
		$old = NULL;
		$new = array();
	}
	if (!is_array($old)) {
		$old = array('ZENPHOTO'=>gettext('an unknown release'));
	}
	$diff = array_diff_assoc($new,$old);
	$package = file_get_contents(dirname(__FILE__).'/Zenphoto.package');
	preg_match_all('|'.ZENFOLDER.'/setup/(.*)|', $package, $matches);
	$needs = array();
	foreach ($matches[1] as $need) {
		$needs[] = trim($need);
	}
	if (file_exists(dirname(__FILE__).'/setup/')) {
		chdir(dirname(__FILE__).'/setup/');
		$found = safe_glob('*.*');
		$needs = array_diff($needs, $found);
	}
	return array($diff, $needs);
}

/**
 *
 * Notificatnion handler for configuration change
 * @param string $tab
 * @param string $subtab
 * @return string
 */
function signatureChange($tab=NULL, $subtab=NULL) {
	reconfigurePage();
	return $tab;
}

/**
 *
 * returns true if setup files are present
 */
function hasSetupFiles() {
	list($diff, $needs) = checkSignature();
	return empty($needs);
}

/**
 *
 * CSS for the configuration change notification
 */
function reconfigureCS() {
	?>
	<style type="text/css">
	.reconfigbox {
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
	.reconfigbox h2,.notebox strong {
		color: #663300;
		font-size: 100%;
		font-weight: bold;
		margin-bottom: 1em;
	}
	#errors ul {
		list-style-type: square;
	}
	#files ul {
		list-style-type: circle;
	}
	</style>
	<?php
}

/**
 *
 * HTML for the configuration change notification
 */
function reconfigurePage() {
	list($diff, $needs) = checkSignature();

	?>
	<div class="reconfigbox">
		<h1>
			<?php
			echo gettext('Zenphoto has detected a change in your installation.');
			?>
		</h1>
		<div id="errors">
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
							echo '<li>'.sprintf(gettext('Zenphoto %1$s has been copied over %2$s.'),ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.']',$old).'</li>';
							break;
						case 'FOLDER':
							echo '<li>'.sprintf(gettext('Your installation has moved from %1$s to %2$s.'),$old,dirname(SERVERPATH.'/'.ZENFOLDER)).'</li>';
							break;
						default:
							$sz = @filesize(SERVERPATH.'/'.ZENFOLDER.'/'.$thing);
							echo '<li>'.sprintf(gettext('The script <code>%1$s</code> has changed.'),$thing).'</li>';
							break;
					}
				}
				?>
			</ul>
		</div>
		<?php
			if (!empty($needs)) {
				?>
				<p>
				<?php printf(gettext('Please reinstall the following setup files from the %1$s [%2$s] release:'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE); ?>
					<div id="files">
						<ul>
							<?php
							foreach ($needs as $script) {
								?>
								<li><?php echo ZENFOLDER; ?>/setup/<?php echo $script; ?></li>
								<?php
							}
							?>
						</ul>
					</div>
				</p>
			<?php
			$needs = true;
			}
			?>
			<p>
			<?php
			if (!empty($needs)) {
				$l1 = $l2 = '';
			} else {
				if (OFFSET_PATH) {
					$where = 'admin';
				} else {
					$where = 'gallery';
				}
				$l1 = '<a href="'.WEBPATH.'/'.ZENFOLDER.'/setup/index.php?autorun='.$where.'">';
				$l2 = '</a>';
			}
			if (array_key_exists('ZENPHOTO', $diff) || array_key_exists('FOLDER', $diff)) {
				printf(gettext('The change detected is critical. You <strong>must</strong> run %1$ssetup%2$s for your site to function.'), $l1, $l2);
			} else {
				printf(gettext('The change detected may not be critical but you should run %1$ssetup%2$s at your earliest convenience.'), $l1, $l2);
			}
		?>
		</p>
	</div>
<?php
}
?>