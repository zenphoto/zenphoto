<?php
/**
 * handles reconfiguration when the install signature has changed
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */
if (!defined('OFFSET_PATH')) {
	die();
}

/**
 *
 * Executes the configuration change code
 */
function reconfigureAction($mandatory) {
	list($diff, $needs) = checkSignature($mandatory);
	$diffkeys = array_keys($diff);
	if ($mandatory) {
		if (isset($_GET['rss']) || isset($_GET['external'])) {
			if (isset($_GET['rss']) && file_exists(SERVERPATH . '/' . DATA_FOLDER . '/rss-closed.xml')) {
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
			// because we are loading the script from within a function!
			global $subtabs, $zenphoto_tabs, $_zp_admin_tab, $_zp_invisible_execute, $_zp_gallery;
			$_zp_invisible_execute = 1;
			require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-globals.php');
			header('Last-Modified: ' . ZP_LAST_MODIFIED);
			header('Content-Type: text/html; charset=UTF-8');
			?>
			<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml" />
			<head>
				<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
				<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
				<?php reconfigureCS(); ?>
			</head>
			<body>
				<?php if ($_zp_gallery) printLogoAndLinks(); ?>
				<div id="main">
					<?php if ($_zp_gallery) printTabs(); ?>
					<div id="content">
						<h1><?php echo gettext('Setup request'); ?></h1>
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
		if (function_exists('zp_register_filter') && zp_loggedin(ADMIN_RIGHTS)) {
			//	no point in telling someone who can't do anything about it
			zp_register_filter('admin_note', 'signatureChange', 9999);
			zp_register_filter('admin_head', 'reconfigureCS');
		}
	}
}

/**
 *
 * Checks details of configuration change
 */
function checkSignature($mandatory) {
	global $_configMutex, $_zp_DB_connection, $_reconfigureMutex;
	$old = NULL;
	if (function_exists('query_full_array') && $_zp_DB_connection) {
		$old = @unserialize(getOption('zenphoto_install'));
		$new = installSignature();
	}
	if (!is_array($old)) {
		$new = array();
		switch ($mandatory) {
			case 11:
				$reason = gettext('The configuration file is missing.');
				break;
			case 12:
				$reason = gettext('The <code>db_software</code> specification is not valid.');
				break;
			case 13:
				$reason = gettext('The database connection failed.');
				break;
			default:
				$reason = '';
				break;
		}
		$old = array('CONFIGURATION' => $reason);

		if (!$mandatory)
			$mandatory = 6;
	}

	$diff = array();
	$keys = array_unique(array_merge(array_keys($new), array_keys($old)));
	foreach ($keys as $key) {
		if (!array_key_exists($key, $new) || !array_key_exists($key, $old) || $old[$key] != $new[$key]) {
			$diff[$key] = array('old' => @$old[$key], 'new' => @$new[$key]);
		}
	}

	$package = file_get_contents(dirname(__FILE__) . '/zenphoto.package');
	preg_match_all('|' . ZENFOLDER . '/setup/(.*)|', $package, $matches);
	$needs = array();
	$restore = $found = false;
	foreach ($matches[1] as $need) {
		$needs[] = rtrim(trim($need), ":*");
	}
// serialize the following
	$_configMutex->lock();
	if (file_exists(dirname(__FILE__) . '/setup/')) {
		chdir(dirname(__FILE__) . '/setup/');
		//just in case files were uploaded over a protected setup folder
		$have = safe_glob('*.php');
		foreach ($have as $key => $f) {
			$f = str_replace('.php', '.xxx', $f);
			if (file_exists($f)) {
				@chmod($f, 0777);
				@unlink($f);
			}
		}
		$restore = safe_glob('*.xxx');

		if (!empty($restore) && $mandatory > 1 && defined('ADMIN_RIGHTS') && zp_loggedin(ADMIN_RIGHTS)) {
			restoreSetupScrpts($mandatory);
		}
		$found = safe_glob('*.*');
		$needs = array_diff($needs, $found);
	}
	$_configMutex->unlock();
	return array($diff, $needs, $restore, $found);
}

/**
 *
 * Notificatnion handler for configuration change
 * @param string $tab
 * @param string $subtab
 * @return string
 */
function signatureChange($tab = NULL, $subtab = NULL) {
	list($diff, $needs) = checkSignature(0);
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
			text-align: left;
			padding: 10px;
			color: black;
			background-color: #FFEFB7;
			border-width: 1px 1px 2px 1px;
			border-color: #FFDEB5;
			border-style: solid;
			margin-bottom: 10px;
			font-size: 100%;
		}
		.reconfigbox h1,.notebox strong {
			color: #663300;
			font-size: 120%;
			font-weight: bold;
			margin-bottom: 1em;
		}
		.reconfigbox a {
			color: blue;
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
	if (function_exists('getXSRFToken')) {
		$token = getXSRFToken('setup');
		if (isset($_GET['dismiss']) && isset($_GET['xsrfToken']) && $_GET['xsrfToken'] == $token) {
			setOption('zenphoto_install', serialize(installSignature()));
			return;
		}
		$token = 'xsrfToken=' . $token;
	} else {
		$token = 'noToken';
	}
	if (OFFSET_PATH) {
		$where = 'admin';
	} else {
		$where = 'gallery';
	}
	$l1 = '<a href="' . WEBPATH . '/' . ZENFOLDER . '/setup.php?autorun=' . $where . '&amp;' . $token . '">';
	$l2 = '</a>';
	?>
	<div class="reconfigbox">
		<h1>
			<?php echo gettext('ZenPhoto20 has detected a change in your installation.'); ?>
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
							echo '<li>' . sprintf(gettext('Your database software has changed from %1$s to %2$s.'), $rslt['old'], $rslt['new']) . '</li>';
							break;
						case 'ZENPHOTO':
							echo '<li>' . sprintf(gettext('ZenPhoto20 %1$s has been copied over %2$s.'), ZENPHOTO_VERSION, $rslt['old']) . '</li>';
							break;
						case 'FOLDER':
							echo '<li>' . sprintf(gettext('Your installation has moved from %1$s to %2$s.'), $rslt['old'], $rslt['new']) . '</li>';
							break;
						case 'CONFIGURATION':
							echo '<li>' . gettext('Your installation configuration is damaged.') . ' ' . $rslt['old'] . '</li>';
							$l1 = '';
							break;
						case 'REQUESTS':
							if (!empty($rslt)) {
								echo '<li><div id="files">';
								echo gettext('setup has been requested by:');
								echo '<ul>';
								foreach ($rslt['old'] as $request) {
									echo '<li>' . $request . '</li>';
								}
								echo '</ul></div></li>';
							}
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
			if ($mandatory) {
				printf(gettext('The change detected is critical. You <strong>must</strong> run %1$ssetup%2$s for your site to function.'), $l1, $l2);
			} else {
				printf(gettext('The change detected may not be critical but you should run %1$ssetup%2$s at your earliest convenience.'), $l1, $l2);
				$request = parse_url(getRequestURI());
				if (isset($request['query'])) {
					$query = parse_query($request['query']);
				} else {
					$query = array();
				}
				$query[] = 'dismiss=config_warning';
				$query[] = $token;
				?>
				<p class="buttons">
					<a href="?<?php echo ltrim(implode('&amp;', $query), '&amp;'); ?>" title="<?php echo gettext('Ignore this configuration change.'); ?>"><?php echo gettext('dismiss'); ?></a>
				</p>
				<br class="clearall">
					<?php
				}
				?>
		</p>
	</div>
	<?php
}

/**
 * control when and how setup scripts are turned back into PHP files
 * @param int reason
 * 						 1	No prior install signature
 * 						 2	restore setup files button
 * 						 4	Clone request
 * 						 5	Setup run with proper XSRF token
 * 						 6	checkSignature and no prior signature
 * 						11	No config file
 * 						12	No database specified
 * 						13	No DB connection
 * 						14	checkInstall Version has changed
 */
function restoreSetupScrpts($reason) {
//log setup file restore no matter what!
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/security-logger.php');
	switch ($reason) {
		default:
			$addl = sprintf(gettext('to run setup [%s]'), $reason);
			break;
		case 2:
			$addl = gettext('by Admin request');
			break;
		case 4:
			$addl = gettext('by cloning');
			break;
	}
	$allowed = defined('ADMIN_RIGHTS') && zp_loggedin(ADMIN_RIGHTS) && zpFunctions::hasPrimaryScripts();
	security_logger::log_setup($allowed, 'restore', $addl);
	if ($allowed) {
		if (!defined('FILE_MOD')) {
			define('FILE_MOD', 0666);
		}
		chdir(dirname(__FILE__) . '/setup/');
		$found = safe_glob('*.xxx');
		foreach ($found as $script) {
			chmod($script, 0777);
			if (@rename($script, stripSuffix($script) . '.php')) {
				chmod(stripSuffix($script) . '.php', FILE_MOD);
			} else {
				chmod($script, FILE_MOD);
			}
		}
	}
}
?>