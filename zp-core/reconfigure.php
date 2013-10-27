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
	list($diff, $needs) = checkSignature($mandatory);
	$diffkeys = array_keys($diff);
	if ($mandatory || in_array('ZENPHOTO', $diffkeys) || in_array('FOLDER', $diffkeys)) {
		if (isset($_GET['rss'])) {
			if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/rss-closed.xml')) {
				$xml = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/rss-closed.xml');
				$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>' . date("r", time()) . '</pubDate>', $xml);
				echo $xml;
			}
			exit(); //	can't really run setup from an RSS feed.
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
			$dir = rtrim($dir, '/');
			$location = "http://" . $_SERVER['HTTP_HOST'] . $dir . "/" . ZENFOLDER . "/setup/index.php?autorun=$where";
			header("Location: $location");
			exitZP();
		} else {
			header('Last-Modified: ' . ZP_LAST_MODIFIED);
			header('Content-Type: text/html; charset=UTF-8');
			?>
			<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
					<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css" type="text/css" />
					<?php reconfigureCS(); ?>
				</head>
				<body>
					<div id="main">
						<div id="content">
							<div class="tabbox">
								<?php reconfigurePage($diff, $needs, $mandatory); ?>
							</div>
						</div>
					</div>
				</body>
			</html>
			<?php
			exitZP();
		}
	} else if (!empty($diff)) {
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
function checkSignature($auto) {
	global $_zp_DB_connection;
	if (function_exists('query_full_array') && $_zp_DB_connection) {
		$old = @unserialize(getOption('zenphoto_install'));
		$new = installSignature();
	} else {
		$old = NULL;
		$new = array();
	}
	if (!is_array($old)) {
		$old = array('ZENPHOTO' => gettext('an unknown release'));
	}
	$diff = array();
	$keys = array_unique(array_merge(array_keys($new), array_keys($old)));
	foreach ($keys as $key) {
		if (!array_key_exists($key, $new) || !array_key_exists($key, $old) || $old[$key] != $new[$key]) {
			$diff[$key] = array('old' => $old[$key], 'new' => $new[$key]);
		}
	}

	$package = file_get_contents(dirname(__FILE__) . '/Zenphoto.package');
	preg_match_all('|' . ZENFOLDER . '/setup/(.*)|', $package, $matches);
	$needs = array();
	foreach ($matches[1] as $need) {
		$needs[] = rtrim(trim($need), ":*");
	}
	if (file_exists(dirname(__FILE__) . '/setup/')) {
		chdir(dirname(__FILE__) . '/setup/');
		if ($auto) {
			$found = safe_glob('*.xxx');
			if (!empty($found)) {
				foreach ($found as $script) {
					chmod($script, 0666);
					if (@rename($script, stripSuffix($script))) {
						chmod(stripSuffix($script), FILE_MOD);
					} else {
						chmod($script, FILE_MOD);
					}
				}
			}
		}
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
function signatureChange($tab = NULL, $subtab = NULL) {
	list($diff, $needs) = checkSignature(false);
	reconfigurePage($diff, $needs, 0);
	return $tab;
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
function reconfigurePage($diff, $needs, $mandatory) {
	?>
	<div class="reconfigbox">
		<h1>
			<?php echo gettext('Zenphoto has detected a change in your installation.'); ?>
		</h1>
		<div id="errors">
			<ul>
				<?php
				foreach ($diff as $thing => $rslt) {
					switch ($thing) {
						case 'SERVER_SOFTWARE':
							echo '<li>' . sprintf(gettext('Your server software has changed from %1$s to %2$s.'), $rslt['old'], $rslt['new']) . '</li>';
							break;
						case 'DATABASE':
							$dbs = db_software();
							echo '<li>' . sprintf(gettext('Your database software has changed from %1$s to %2$s.'), $rslt['old'], $rslt['new']) . '</li>';
							break;
						case 'ZENPHOTO':
							echo '<li>' . sprintf(gettext('Zenphoto %1$s has been copied over %2$s.'), ZENPHOTO_VERSION . '[' . ZENPHOTO_RELEASE . ']', $rslt['old']) . '</li>';
							break;
						case 'FOLDER':
							echo '<li>' . sprintf(gettext('Your installation has moved from %1$s to %2$s.'), $rslt['old'], $rslt['new']) . '</li>';
							break;
						default:
							$sz = @filesize(SERVERPATH . '/' . ZENFOLDER . '/' . $thing);
							echo '<li>' . sprintf(gettext('The script <code>%1$s</code> has changed.'), $thing) . '</li>';
							break;
					}
				}
				?>
			</ul>
		</div>
		<p>
			<?php
			if (OFFSET_PATH) {
				$where = 'admin';
			} else {
				$where = 'gallery';
			}
			$l1 = '<a href="' . WEBPATH . '/' . ZENFOLDER . '/setup.php?autorun=' . $where . '&amp;xsrfToken=' . getXSRFToken('setup') . '">';
			$l2 = '</a>';
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