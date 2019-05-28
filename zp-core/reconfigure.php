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
	if (($mandatory || in_array('ZENPHOTO', $diffkeys) || in_array('FOLDER', $diffkeys))) {
		if (isset($_GET['rss'])) {
			if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/rss-closed.xml')) {
				$xml = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/rss-closed.xml');
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
			unprotectSetupFiles();
			if (!defined('PROTOCOL')) {
				if (secureServer()) {
					define('PROTOCOL', 'https');
				} else {
					define('PROTOCOL', 'http');
				}
			}
			$location = PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . $dir . "/" . ZENFOLDER . "/setup/index.php?autorun=$where";
			redirectURL($location);
		} else {
			reconfigurePage($diff, $needs, $mandatory);
		}
	} else if (!empty($diff)) {
		reconfigureNote();
	}
}

/**
 *
 * Checks details of configuration change
 */
function checkSignature($auto) {
	global $_configMutex;
	global $_zp_DB_connection, $_reconfigureMutex;
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
			$diff[$key] = array('old' => @$old[$key], 'new' => @$new[$key]);
		}
	}

	$package = file_get_contents(SERVERPATH . '/' .ZENFOLDER . '/Zenphoto.package');
	preg_match_all('|' . ZENFOLDER . '/setup/(.*)|', $package, $matches);
	$needs = array();
	foreach ($matches[1] as $need) {
		$needs[] = rtrim(trim($need), ":*");
	}
	// serialize the following
	$_configMutex->lock();
	if (file_exists(SERVERPATH . '/' .ZENFOLDER . '/setup/')) {
		$found = isSetupProtected();
		if(!empty($found) && $auto && (defined('ADMIN_RIGHTS') && zp_loggedin(ADMIN_RIGHTS))) {
			unprotectSetupFiles();
		}
		$found = safe_glob('*.*');
		$needs = array_diff($needs, $found);
	} 
	$_configMutex->unlock();
	
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
 * Adds the reconfigure notification via filters
 */
function reconfigureNote() {
	if (function_exists('zp_register_filter')) {
		zp_register_filter('admin_note', 'signatureChange');
		zp_register_filter('admin_head', 'reconfigureCS');
		if (zp_loggedin(ADMIN_RIGHTS)) {
			zp_register_filter('theme_head', 'reconfigureCS');
			zp_register_filter('theme_body_open', 'signatureChange');
		}
	}
}

/**
 *
 * HTML for the configuration change notification
 */
function reconfigurePage($diff, $needs, $mandatory) {
	if (isset($_GET['ignore_setup']) && zp_loggedin(ADMIN_RIGHTS)) {
		XSRFdefender('ignore_setup');
		purgeOption('zenphoto_install');
		setOption('zenphoto_install', serialize(installSignature()));
		zp_apply_filter('log_setup', true, 'ignore_setup', gettext('Setup re-run ignored by admin request.'));
	} else {
	?>
	<div class="reconfigbox">
		<h1><?php echo gettext('Zenphoto has detected a change in your installation.'); ?></h1>
		<div class="reconfig_errors">
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
							echo '<li>' . sprintf(gettext('Zenphoto %1$s has been copied over %2$s.'), ZENPHOTO_VERSION, $rslt['old']) . '</li>';
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
			if (array_key_exists('ZENPHOTO', $diff) || array_key_exists('FOLDER', $diff)) {
				echo gettext('The change detected is critical. You <strong>must</strong> run setup for your site to function.');
			} else {
				echo gettext('The change detected may not be critical but you should run setup at your earliest convenience.');
			}
			?>
		</p>
		<?php 
		if(zp_loggedin(ADMIN_RIGHTS)) {
			if (OFFSET_PATH) {
				$where = 'admin';
			} else {
				$where = 'gallery';
			}
			$runsetup_link = WEBPATH . '/' . ZENFOLDER . '/setup.php?autorun=' . $where . '&amp;xsrfToken=' . getXSRFToken('setup');
			if(MOD_REWRITE) {
				$ignore_link ='?ignore_setup&amp;XSRFToken=' . getXSRFToken('ignore_setup');
			} else {
				$ignore_link ='&amp;ignore_setup&amp;XSRFToken=' . getXSRFToken('ignore_setup');
			}
			?>
			<p class="reconfig_links">
				<a class="reconfig_link reconfig_link-runsetup" href="<?php echo $runsetup_link; ?>"><?php echo gettext('Run setup'); ?></a> 
				<a class="reconfig_link reconfig_link-ignore" href="<?php echo $ignore_link; ?>"> <?php echo gettext('Ignore, I know what I am doing!'); ?></a>
			</p>
			<script>
				$( document ).ready(function() {
					$('.reconfig_link-ignore').click(function(event) {
						event.preventDefault();
						$.ajax(this.href, {
							success: function(data) {
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
}

/**
 * Checks if setup files are protected. Returns array of the protected files or empty array
 * 
 * @return array
 */
function isSetupProtected() {
	if (file_exists(SERVERPATH . '/' .ZENFOLDER . '/setup/')) {
		chdir(SERVERPATH . '/' .ZENFOLDER . '/setup/');
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
			if(!defined('FILE_MOD')) {
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
function reconfigureCS() {
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