<?php
/**
 * handles reconfiguration when the install signature has changed
 * 
 * @package zpcore\functions\reconfig
 */

/**
 * Executes the configuration change code
 * 
 * @param int $mandatory 0-3 0 means signature change where 1 means setup must autorun assuming a fresh install
 */
function reconfigureAction($mandatory) {
	list($diff, $needs) = checkSignature($mandatory);
	$diffkeys = array_keys($diff);
	if (($mandatory || in_array('ZENPHOTO', $diffkeys) || in_array('FOLDER', $diffkeys))) {
		if (isset($_GET['rss'])) {
			if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss-closed.xml')) {
				$xml = file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss-closed.xml');
				$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>' . date("r", time()) . '</pubDate>', $xml);
				echo $xml;
			}
			exit(); //	can't really run setup from an RSS feed.
		}
		if (in_array('ZENPHOTO', $diffkeys) || empty($needs)) {
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
			if (!defined('PROTOCOL')) {
				if (secureServer()) {
					define('PROTOCOL', 'https');
				} else {
					define('PROTOCOL', 'http');
				}
			}
			$setup_autorun = $show_errorpage = false;
			switch ($mandatory) {
				case 1:
					$setup_autorun = true;
					// we assume a fresh install 
					debuglog(gettext('ZenphotoCMS config file is missing.') . ' ' . gettext('Setup run required.'));
					break;
				case 2:
					$show_errorpage = true;
					debuglog(gettext('ZenphotoCMS database credentials are missing.') . ' ' . gettext('Check your config file settings and re-run setup.'));
					break;
				case 3:
					$show_errorpage = true;
					debuglog(gettext('ZenphotoCMS database credentials are incomplete or wrong.') . ' ' . gettext('Check your config file settings and re-run setup.'));
					break;
				case 4:
					$setup_autorun = true;
					// we assume a fresh install 
					debuglog(gettext('ZenphotoCMS database has no administrators table or it is empty.') . ' ' . gettext('Setup run required.'));
					break;
			}
			if ($setup_autorun) { //mandatory level 1
				unprotectSetupFiles();
				$location = PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . $dir . "/" . ZENFOLDER . "/setup/index.php?autorun=$where";
				redirectURL($location);
			} else {
				if ($show_errorpage) { // mandatory level 2-3
					printReconfigureError($mandatory);
				} else { // mandatory level 0 - here we can check login status
					if (zp_loggedin()) {
						addReconfigureNote();
					}
				}
			}
		} else {
			printReconfigureNote($diff, $needs, $mandatory);
		}
	} else if (!empty($diff)) {
		debuglog(gettext('Install signature change detected.') . gettext('Setup run recommended.'));
		addReconfigureNote();
	}
}

/**
 * Checks details of configuration change
 * 
 * @global type $_zp_mutex
 * @global type $_zp_db
 * @param bool $auto
 * @return type
 */
function checkSignature($auto) {
	global $_zp_mutex, $_zp_db;
	if (is_object($_zp_db) && method_exists($_zp_db, 'queryFullArray') && $_zp_db->connection) {
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
			$diff[$key] = array('old' => @$old[$key], 'new' => @$new[$key]);
		}
	}
	$package = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/Zenphoto.package');
	preg_match_all('|' . ZENFOLDER . '/setup/(.*)|', $package, $matches);
	$needs = array();
	foreach ($matches[1] as $need) {
		$needs[] = rtrim(trim($need), ":*");
	}
	// serialize the following
	$_zp_mutex->lock();
	if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/setup/')) {
		$found = isSetupProtected();
		if (!empty($found) && $auto && (defined('ADMIN_RIGHTS') && zp_loggedin(ADMIN_RIGHTS))) {
			unprotectSetupFiles();
		}
		$found = safe_glob('*.*');
		$needs = array_diff($needs, $found);
	}
	$_zp_mutex->unlock();
	return array($diff, $needs);
}

/**
 * Notification handler for configuration change
 * 
 * @param string $tab
 * @param string $subtab
 * @return string
 */
function signatureChange($tab = NULL, $subtab = NULL) {
	list($diff, $needs) = checkSignature(false);
	printReconfigureNote($diff, $needs, 0);
	return $tab;
}

/**
 * Adds the reconfigure notification via filters 
 * 
 * @since 1.5.8 - renamed from reconfigureNote()
 */
function addReconfigureNote() {
	if (function_exists('zp_register_filter')) {
		zp_register_filter('admin_head', 'reconfigureCSS');
		zp_register_filter('admin_note', 'signatureChange');
		zp_register_filter('theme_head', 'reconfigureCSS');
		zp_register_filter('theme_body_open', 'signatureChange');
	}
}

/**
 * prints HTML for the configuration change notification
 * 
 * @since 1.5.8 - renamed from reconfigureNote()
 * @param array $diff
 * @param type $needs
 * @param type $mandatory
 */
function printReconfigureNote($diff, $needs, $mandatory) {
	$notice = getReconfigureNote($diff, $needs, $mandatory);
	if (!zp_loggedin()) {
		debuglog("Reconfignote hidden on frontend as not loggedin");
		debuglogReconfigureNote($notice);
		return;
	}
	?>
	<div class="reconfigbox">
		<h1><?php echo $notice['headline']; ?></h1>
		<div class="reconfig_errors">
			<ul>
				<?php
				foreach ($notice['notes'] as $note) {
					?>
					<li><?php echo $note; ?></li>
					<?php
				}
				?>
			</ul>
		</div>
		<p>
			<?php echo $notice['note_level']; ?>
		</p>
		<?php
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (OFFSET_PATH) {
				$where = 'admin';
			} else {
				$where = 'gallery';
			}
			$runsetup_link = WEBPATH . '/' . ZENFOLDER . '/setup.php?autorun=' . $where . '&amp;xsrfToken=' . getXSRFToken('setup');
			$ignore_link = WEBPATH . '/' . ZENFOLDER . '/admin.php?ignore_setup=1&amp;XSRFToken=' . getXSRFToken('ignore_setup');
			?>
			<p class="reconfig_links">
				<a class="reconfig_link reconfig_link-runsetup" href="<?php echo $runsetup_link; ?>"><?php echo gettext('Run setup'); ?></a> 
				<a class="reconfig_link reconfig_link-ignore" href="<?php echo $ignore_link; ?>"> <?php echo gettext('Ignore, I know what I am doing!'); ?></a>
			</p>
			<script>
				$(document).ready(function () {
					$('.reconfig_link-ignore').click(function (event) {
						event.preventDefault();
						var link = $('.reconfig_link-ignore').attr('href');
						$.ajax(link, {
							success: function (data) {
								$('.reconfigbox').remove();
							}
						});
					});
				});
			</script>
			<?php
		} else {
			?>
			<p><strong><?php echo gettext("You don't have the rights to run setup. Please contact your site's administrator or login with your administrator user account."); ?></strong></p>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Gets data for the configuration change notification
 * 
 * Also adds entries to the debuglog.
 * 
 * @since 1.5.8
 * 
 * @param array $diff
 * @param type $needs
 * @param type $mandatory
 * @return array
 */
function getReconfigureNote($diff, $needs, $mandatory) {
	$notice['headline'] = gettext('Zenphoto has detected a change in your installation.');
	$notice['notes'] = array();
	foreach ($diff as $thing => $rslt) {
		switch ($thing) {
			case 'SERVER_SOFTWARE':
				$notice['notes'][] = sprintf(gettext('Your server software has changed from %1$s to %2$s.'), $rslt['old'], $rslt['new']);
				break;
			case 'DATABASE':
				$notice['notes'][] = sprintf(gettext('Your database software has changed from %1$s to %2$s.'), $rslt['old'], $rslt['new']);
				break;
			case 'ZENPHOTO':
				$notice['notes'][] = sprintf(gettext('Zenphoto %1$s has been copied over %2$s.'), ZENPHOTO_VERSION, $rslt['old']);
				break;
			case 'FOLDER':
				$notice['notes'][] = sprintf(gettext('Your installation has moved from %1$s to %2$s.'), $rslt['old'], $rslt['new']);
				break;
			default:
				//$sz = @filesize(SERVERPATH . '/' . ZENFOLDER . '/' . $thing);
				$notice['notes'][] = sprintf(gettext('The script <code>%1$s</code> has changed.'), $thing);
				break;
		}
	}
	if (array_key_exists('ZENPHOTO', $diff) || array_key_exists('FOLDER', $diff)) {
		$notice['note_level'] = gettext('The change detected is critical. You <strong>must</strong> run setup for your site to function.');
	} else {
		$notice['note_level'] = gettext('The change detected may not be critical but you should run setup at your earliest convenience.');
	}
	return $notice;
}

/**
 * Prints an error page on the frontend if a mandatory reconfigure issue occurred but the visitor is not loggedin 
 * with appropiate rights.
 */
function printReconfigureError($mandatory) {
	header("HTTP/1.1 503 Service Temporarily Unavailable");
	header("Status: 503 Service Temporarily Unavailable");
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="UTF-8" />
			<title><?php echo gettext('A configuration error occurred.'); ?></title>
			<style>
				.siterror {
					font-family: sans-serif;
					max-width: 500px;
					padding: 20px;
					border: 1px solid red;
					margin: 0 auto;
					margin-top: 20px;
				}
			</style>
		</head>
		<body>
			<div class="siterror">
				<p><strong><?php echo gettext('A configuration error occurred.'); ?></strong></p>
				<p><?php echo gettext('Please return later.'); ?></p>
			</div>
		</body>
	</html>
	<?php
	exit();
}


/**
 * Adds debuglog entries about the reconfigure note
 * 
 * @param array $notice reconfigure notice array as returned by getReconfigureNote()
 */
function debuglogReconfigureNote($notice) {
	debuglog($notice['headline']);
	foreach ($notice['notes'] as $note) {
		debuglog($note);
	}
	debuglog($notice['note_level']);
}

/**
 * If setup request a run because of a signature change this refreshes the signature 
 * on full admin user request so it is ignored until the next signature change.
 * 
 * @since 1.5.8
 */
function ignoreSetupRunRequest() {
	if (isset($_GET['ignore_setup']) && zp_loggedin(ADMIN_RIGHTS)) {
		XSRFdefender('ignore_setup');
		purgeOption('zenphoto_install');
		setOption('zenphoto_install', serialize(installSignature()));
		zp_apply_filter('log_setup', true, 'ignore_setup', gettext('Setup re-run ignored by admin request.'));
		exitZP();
	}
}

/**
 * Checks if setup files are protected. Returns array of the protected files or empty array
 * 
 * @return array
 */
function isSetupProtected() {
	if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/setup/')) {
		chdir(SERVERPATH . '/' . ZENFOLDER . '/setup/');
		$found = safe_glob('*.xxx');
		return $found;
	}
	return array();
}

/**
 * Unprotectes setup files
 */
function unprotectSetupFiles() {
	$found = isSetupProtected();
	if ($found) {
		foreach ($found as $script) {
			if (!defined('FILE_MOD')) {
				define('FILE_MOD', 0666);
			}
			chmod($script, 0777);
			if (@rename($script, stripSuffix($script))) {
				chmod(stripSuffix($script), FILE_MOD);
			} else {
				chmod($script, FILE_MOD);
			}
		}
	}
}

/**
 * Protects setup files
 */
function protectSetupFiles() {
	chdir(SERVERPATH . '/' . ZENFOLDER . '/setup/');
	$list = safe_glob('*.php');
	if (!empty($list)) {
		$rslt = array();
		foreach ($list as $component) {
			@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, 0777);
			if (@rename(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component . '.xxx')) {
				@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component . '.xxx', FILE_MOD);
			} else {
				@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, FILE_MOD);
				$rslt[] = '../setup/' . $component;
			}
		}
		zp_apply_filter('log_setup', true, 'protect', gettext('protected'));
	}
}

/**
 *
 * CSS for the configuration change notification
 */
function reconfigureCSS() {
	?>
	<style type="text/css">
		.reconfigbox {
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			padding: 5px 10px 5px 10px;
			background-color: #FFEFB7;
			border-width: 1px 1px 2px 1px;
			border-color: #FFDEB5;
			border-style: solid;
			margin-bottom: 10px;
			font-size: 1em;
			line-height: 1.6em;
			-moz-border-radius: 5px;
			-khtml-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px;
			text-align: left;
		}

		.successbox {
			background-color: green;
		}
		.reconfigbox h1,.notebox strong {
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			color: #663300;
			font-size: 1.6em;
			font-weight: bold;
			margin-bottom: 1em;
		}

		.reconfigbox code {
			font-weight: bold;
		}

		.reconfig_links:after {
			content: " " !important;
			display: table !important;
			clear: both !important;
		}

		.reconfigbox .reconfig_link {
			display: inline-block;
			padding: 5px 8px 5px 8px;
			border: 0;
			background: white;
			margin: 0 10px 0px 0;
		}

		.reconfig_link-runsetup {
			font-weight: bold;
			border: 1px solid darkgray !important;
		}

		.reconfig_link-ignore {
			display: block;
			float: right;		
		}

		#errors ul {
			list-style-type: square;
		}
	</style>
	<?php
}
