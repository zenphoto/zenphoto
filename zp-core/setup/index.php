<?php
/**
 * install routine for zenphoto
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 */
// force UTF-8 Ø

Define('PHP_MIN_VERSION', '5.2');
Define('PHP_MIN_SUPPORTED_VERSION', '5.6');
Define('PHP_DESIRED_VERSION', '7.1');
define('HTACCESS_VERSION', '1.4.5'); // be sure to change this to the one in .htaccess when the .htaccess file is updated.
define('OFFSET_PATH', 2);

// leave this as the first executable statement to avoid problems with PHP not having gettext support.
if (function_exists("gettext")) {
	$noxlate = 1;
} else {
	require_once(dirname(dirname(__FILE__)) . '/lib-gettext/gettext.inc');
	$noxlate = -1;
}
if (version_compare(PHP_VERSION, PHP_MIN_VERSION, '<')) {
	die(sprintf(gettext('ZenPhoto20 requires PHP version %s or greater'), PHP_MIN_VERSION));
}

$chmod = fileperms(dirname(dirname(__FILE__))) & 0666;
$_initial_session_path = session_save_path();

require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');
require_once(dirname(dirname(__FILE__)) . '/functions-common.php');
require_once(dirname(__FILE__) . '/setup-functions.php');

//allow only one setup to run
$setupMutex = new zpMutex('sP');
$setupMutex->lock();

if ($debug = isset($_REQUEST['debug'])) {
	if (!$debug = $_REQUEST['debug']) {
		$debug = true;
	}
}

$upgrade = false;

require_once(dirname(dirname(__FILE__)) . '/lib-utf8.php');

if (isset($_REQUEST['autorun'])) {
	$displayLimited = true;
	if (!empty($_REQUEST['autorun'])) {
		$autorun = strip_tags($_REQUEST['autorun']);
	} else {
		$autorun = 'admin';
	}
} else {
	$displayLimited = $autorun = false;
}

$session = zp_session_start();
session_cache_limiter('nocache');
$setup_checked = false;

if (isset($_REQUEST['xsrfToken']) || isset($_REQUEST['update']) || isset($_REQUEST['checked'])) {
	if (isset($_SESSION['save_session_path'])) {
		$setup_checked = isset($_GET['checked']);
		$_initial_session_path = $_SESSION['save_session_path'];
	} else {
		$_initial_session_path = false;
		unset($_REQUEST['update']);
		unset($_REQUEST['checked']);
	}
}
$_SESSION['save_session_path'] = session_save_path();


$en_US = dirname(dirname(__FILE__)) . '/locale/en_US/';
if (!file_exists($en_US)) {
	@mkdir(dirname(dirname(__FILE__)) . '/locale/', $chmod | 0311);
	@mkdir($en_US, $chmod | 0311);
}

$zptime = time();
if (!file_exists(SERVERPATH . '/' . DATA_FOLDER)) {
	@mkdir(SERVERPATH . '/' . DATA_FOLDER, $chmod | 0311);
}
if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg.bak')) {
	unlink(SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg.bak'); //	remove any old backup file
}

if (file_exists($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	$zpconfig = file_get_contents($oldconfig);
	if (strpos($zpconfig, '<?php') === false) {
		$zpconfig = "<?php\n" . $zpconfig . "\n?>";
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zpconfig);
		configMod();
	}
	$newconfig = false;
} else if (file_exists($oldconfig = dirname(dirname(dirname(__FILE__))) . '/' . ZENFOLDER . '/zp-config.php')) {
	//migrate old root configuration file.
	$zpconfig = file_get_contents($oldconfig);
	$i = strpos($zpconfig, '/** Do not edit above this line. **/');
	$zpconfig = "<?php\nglobal \$_zp_conf_vars;\n\$conf = array()\n" . substr($zpconfig, $i);
	file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zpconfig);
	$result = @unlink(dirname(dirname(dirname(__FILE__))) . '/' . ZENFOLDER . '/zp-config.php');
	$newconfig = false;
	configMod();
} else if (file_exists($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg')) {
	$zpconfig = "<?php\n" . file_get_contents($oldconfig) . "\n?>";
	file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zpconfig);
	@unlink(SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg');
	$newconfig = false;
	configMod();
} else {
	$newconfig = true;
	@copy(dirname(dirname(__FILE__)) . '/zenphoto_cfg.txt', SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	configMod();
}

$zptime = filemtime($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
@chmod(SERVERPATH . '/' . DATA_FOLDER . '/.htaccess', 0777);
@copy(dirname(dirname(__FILE__)) . '/dataaccess', SERVERPATH . '/' . DATA_FOLDER . '/.htaccess');
@chmod(SERVERPATH . '/' . DATA_FOLDER . '/.htaccess', 0444);

if (file_exists(SERVERPATH . '/' . BACKUPFOLDER)) {
	/* move the files */
	@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER, 0777);
	@rename(SERVERPATH . '/' . BACKUPFOLDER, SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER);
	@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER, $chmod | 0311);
}
if (!file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER)) {
	@mkdir(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER, $chmod | 0311);
}
@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER . '/.htaccess', 0777);
@copy(dirname(dirname(__FILE__)) . '/dataaccess', SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER . '/.htaccess');
@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . BACKUPFOLDER . '/.htaccess', 0444);



if (isset($_GET['mod_rewrite'])) {
	$mod = '&mod_rewrite=' . $_GET['mod_rewrite'];
} else {
	$mod = '';
}

$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);

$updatezp_config = false;
if (strpos($zp_cfg, "\$conf['charset']") === false) {
	$k = strpos($zp_cfg, "\$conf['UTF-8'] = true;");
	$zp_cfg = substr($zp_cfg, 0, $k) . "\$conf['charset'] = 'UTF-8';\n" . substr($zp_cfg, $k);
	$updatezp_config = true;
}

if (strpos($zp_cfg, "\$conf['special_pages']") === false) {
	$template = file_get_contents(dirname(dirname(__FILE__)) . '/zenphoto_cfg.txt');
	$i = strpos($template, "\$conf['special_pages']");
	$j = strpos($template, '//', $i);
	$k = strpos($zp_cfg, '/** Do not edit below this line. **/');
	if ($k !== false) {
		$zp_cfg = substr($zp_cfg, 0, $k) . str_pad('', 80, '/') . "\n" .
						substr($template, $i, $j - $i) . str_pad('', 5, '/') . "\n" .
						substr($zp_cfg, $k);
		$updatezp_config = true;
	}
}

$i = strpos($zp_cfg, 'define("DEBUG", false);');
if ($i !== false) {
	$updatezp_config = true;
	$j = strpos($zp_cfg, "\n", $i);
	$zp_cfg = substr($zp_cfg, 0, $i) . substr($zp_cfg, $j); // remove this so it won't be defined twice
}

if (isset($_POST['db'])) { //try to update the zp-config file
	setupXSRFDefender('db');
	setupLog(gettext("db POST handling"));
	$updatezp_config = true;
	if (isset($_POST['db_software'])) {
		$zp_cfg = updateConfigItem('db_software', trim(sanitize($_POST['db_software'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_user'])) {
		$zp_cfg = updateConfigItem('mysql_user', trim(sanitize($_POST['db_user'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_pass'])) {
		$zp_cfg = updateConfigItem('mysql_pass', trim(sanitize($_POST['db_pass'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_host'])) {
		$zp_cfg = updateConfigItem('mysql_host', trim(sanitize($_POST['db_host'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_database'])) {
		$zp_cfg = updateConfigItem('mysql_database', trim(sanitize($_POST['db_database'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_prefix'])) {
		$zp_cfg = updateConfigItem('mysql_prefix', str_replace(array('.', '/', '\\', '`', '"', "'"), '_', trim(sanitize($_POST['db_prefix'], 0))), $zp_cfg);
	}
}

define('ACK_REGISTER_GLOBALS', 1);
define('ACK_DISPLAY_ERRORS', 2);

if (isset($_GET['security_ack'])) {
	setupXSRFDefender('security_ack');
	$zp_cfg = updateConfigItem('security_ack', (isset($conf['security_ack']) ? $cache['keyword'] : NULL) | (int) $_GET['security_ack'], $zp_cfg, false);
	$updatezp_config = true;
}

$permission_names = array(
		0444 => gettext('readonly'),
		0644 => gettext('strict'),
		0664 => gettext('relaxed'),
		0666 => gettext('loose')
);
$permissions = array_keys($permission_names);
if ($updatechmod = isset($_REQUEST['chmod_permissions'])) {
	setupXSRFDefender('chmod_permissions');
	$selected = round($_REQUEST['chmod_permissions']);
	if ($selected >= 0 && $selected < count($permissions)) {
		$chmod = $permissions[$selected];
	} else {
		$updatechmod = false;
	}
}
if ($updatechmod || $newconfig) {
	if ($updatechmod || isset($_zp_conf_vars['CHMOD'])) {
		$chmodval = "\$conf['CHMOD']";
	} else {
		$chmodval = sprintf('0%o', $chmod);
	}
	if ($updatechmod) {
		$zp_cfg = updateConfigItem('CHMOD', sprintf('0%o', $chmod), $zp_cfg, false);
		if (strpos($zp_cfg, "if (!defined('CHMOD_VALUE')) {") !== false) {
			$zp_cfg = preg_replace("|if\s\(!defined\('CHMOD_VALUE'\)\)\s{\sdefine\(\'CHMOD_VALUE\'\,(.*)\);\s}|", "if (!defined('CHMOD_VALUE')) { define('CHMOD_VALUE', " . $chmodval . "); }\n", $zp_cfg);
		} else {
			$i = strpos($zp_cfg, "/** Do not edit below this line. **/");
			$zp_cfg = substr($zp_cfg, 0, $i) . "if (!defined('CHMOD_VALUE')) { define('CHMOD_VALUE', " . $chmodval . "); }\n" . substr($zp_cfg, $i);
		}
	}
	$updatezp_config = true;
}

if (isset($_REQUEST['FILESYSTEM_CHARSET'])) {
	setupXSRFDefender('FILESYSTEM_CHARSET');
	$fileset = $_REQUEST['FILESYSTEM_CHARSET'];
	$zp_cfg = updateConfigItem('FILESYSTEM_CHARSET', $fileset, $zp_cfg);
	$updatezp_config = true;
}

if ($updatezp_config) {
	storeConfig($zp_cfg);
	$updatezp_config = false;
}

// Important. when adding new database support this switch may need to be extended,
$engines = array();

$preferences = array('mysqli' => 1, 'pdo_mysql' => 2);
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
	$preferences['mysql'] = 3;
}
$cur = 999999;
$preferred = NULL;

$dir = opendir(dirname(dirname(__FILE__)));
while (($engineMC = readdir($dir)) !== false) {
	if (preg_match('/^functions-db-(.+)\.php/', $engineMC)) {
		$engineMC = substr($engineMC, 13, -4);
		$engine = strtolower($engineMC);
		if (array_key_exists($engine, $preferences)) {
			$order = $preferences[$engine];
			$enabled = extension_loaded($engine);
			if ($enabled && $order < $cur) {
				$preferred = $engineMC;
				$cur = $order;
			}
			$engines[$order] = array('user' => true, 'pass' => true, 'host' => true, 'database' => true, 'prefix' => true, 'engine' => $engineMC, 'enabled' => $enabled);
		}
	}
}
ksort($engines);

if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	loadConfiguration();

	if (!isset($_zp_conf_vars['UTF-8']) || $_zp_conf_vars['UTF-8'] === true) {
		$_zp_conf_vars['UTF-8'] = 'utf8';
	}
	if (isset($_zp_conf_vars) && !isset($conf) && isset($_zp_conf_vars['special_pages'])) {
		if (isset($_zp_conf_vars['db_software'])) {
			$confDB = $_zp_conf_vars['db_software'];
			if (empty($_POST) && empty($_GET) && ($confDB === 'MySQL' || $preferred != 'MySQL')) {
				$confDB = NULL;
			}
			if (extension_loaded(strtolower($confDB)) && file_exists(dirname(dirname(__FILE__)) . '/functions-db-' . $confDB . '.php')) {
				$selected_database = $confDB;
			} else {
				$selected_database = $preferred;
				if ($preferred) {
					$_zp_conf_vars['db_software'] = $preferred;
					$zp_cfg = updateConfigItem('db_software', $preferred, $zp_cfg);
					$updatezp_config = true;
				}
			}
		} else {
			$_zp_conf_vars['db_software'] = $selected_database = $preferred;
			$zp_cfg = updateConfigItem('db_software', $zp_cfg, $preferred);
			$updatezp_config = true;
			$confDB = NULL;
		}

		if (!$selected_database) {
			require_once(dirname(dirname(__FILE__)) . '/functions-db-NULL.php');
		}
	} else {
		// There is a problem with the configuration file
		?>
		<div style="background-color: red;font-size: xx-large;">
			<p>
				<?php echo gettext('A corrupt configuration file was detected. You should remove or repair the file and re-run setup.'); ?>
			</p>
		</div>
		<?php
		exit();
	}
	if ($updatezp_config) {
		storeConfig($zp_cfg);
		$updatezp_config = false;
	}
	require_once(dirname(dirname(__FILE__)) . '/functions.php');
}



if ($updatezp_config) {
	storeConfig($zp_cfg);
}
$result = true;
$environ = false;
$DBcreated = false;
$oktocreate = false;
$connection = false;
$connectDBErr = '';

if ($selected_database) {
	$connectDBErr = '';
	$connection = db_connect($_zp_conf_vars, false);
	if ($connection) { // got the database handler and the database itself connected
		$result = query("SELECT `id` FROM " . $_zp_conf_vars['mysql_prefix'] . 'options' . " LIMIT 1", false);
		if ($result) {
			if (db_num_rows($result) > 0) {
				$upgrade = gettext("upgrade");
				// apply some critical updates to the database for migration issues
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `valid` int(1) default 1', false);
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' CHANGE `password` `pass` varchar(64)', false);
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `loggedin` datetime', false);
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `lastloggedin` datetime', false);
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `challenge_phrase` TEXT', false);
			}
		} else {
			$upgrade = gettext("install");
		}
		$environ = true;
	} else {
		if ($_zp_DB_connection) { // there was a connection to the database handler but not to the database.
			if (!empty($_zp_conf_vars['mysql_database'])) {
				if (isset($_GET['Create_Database'])) {
					$result = db_create();
					if ($result && ($connection = db_connect($_zp_conf_vars, false))) {
						$environ = true;
					} else {
						if ($result) {
							$DBcreated = true;
						} else {
							$connectDBErr = db_error();
						}
					}
				} else {
					$oktocreate = true;
				}
			}
		} else {
			$connectDBErr = db_error();
		}
	}
}

require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/security-logger.php');

header('Content-Type: text/html; charset=UTF-8');
header("HTTP/1.0 200 OK");
header("Status: 200 OK");
header("Cache-Control: no-cache, must-revalidate, no-store, pre-check=0, post-check=0, max-age=0");
header("Pragma: no-cache");
header('Last-Modified: ' . ZP_LAST_MODIFIED);
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

if (defined('CHMOD_VALUE')) {
	$chmod = CHMOD_VALUE & 0666;
}

setOptionDefault('zp_plugin_security-logger', 9 | CLASS_PLUGIN);

$cloneid = bin2hex(FULLWEBPATH);
$forcerewrite = isset($_SESSION['clone'][$cloneid]['mod_rewrite']) && $_SESSION['clone'][$cloneid]['mod_rewrite'] && !file_exists(SERVERPATH . '/.htaccess');
if ($newconfig || isset($_GET['copyhtaccess']) || $forcerewrite) {
	if (($newconfig || $forcerewrite) && !file_exists(SERVERPATH . '/.htaccess') || setupUserAuthorized()) {
		@chmod(SERVERPATH . '/.htaccess', 0777);
		$ht = @file_get_contents(SERVERPATH . '/.htaccess');
		$newht = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/htaccess');
		file_put_contents(SERVERPATH . '/.htaccess', $newht);
		@chmod(SERVERPATH . '/.htaccess', 0444);
	}
}

if ($setup_checked) {
	if (!isset($_GET['protect_files'])) {
		setupLog(gettext("Completed system check"), true);
		if (isset($_COOKIE['setup_test_cookie'])) {
			$setup_cookie = $_COOKIE['setup_test_cookie'];
		} else {
			$setup_cookie = '';
		}
		if ($setup_cookie == ZENPHOTO_VERSION) {
			setupLog(gettext('Setup cookie test successful'));
			zp_clearCookie('setup_test_cookie');
		} else {
			setupLog(gettext('Setup cookie test unsuccessful'), true);
		}
	}
} else {
	if (isset($_POST['db'])) {
		setupLog(gettext("Post of Database credentials"), true);
	} else {

		if (!isset($_SESSION['SetupStarted']) || $_SESSION['SetupStarted'] != ZENPHOTO_VERSION) {
			$_SESSION['SetupStarted'] = ZENPHOTO_VERSION;
			zp_apply_filter('log_setup', true, 'install', gettext('Started'));
		}

		$me = realpath(dirname(dirname(dirname(str_replace('\\', '/', __FILE__)))));
		$mine = realpath(SERVERPATH);
		if (isWin() || isMac()) { // case insensitive file systems
			$me = strtolower($me);
			$mine = strtolower($mine);
		}
		if ($mine == $me || !file_exists($mine . '/index.php')) {
			if ($mine == $me) {
				$clone = '';
			} else {
				$clone = ' ' . gettext('clone');
			}
			$index = $mine . '/index.php';
			$rootupdate = @copy(dirname(dirname(__FILE__)) . '/root_index.php', $index);
			if (!$rootupdate) {
				$f1 = @file_get_contents($index);
				$f2 = file_get_contents(dirname(dirname(__FILE__)) . '/root_index.php');
				$rootupdate = $f1 == $f2; // it is ok, the contents is correct
			}
		} else {
			$clone = ' ' . gettext('clone');
		}
		setupLog(sprintf(gettext('ZenPhoto20 Setup v%1$s%2$s: %3$s'), ZENPHOTO_VERSION, $clone, date('r')), true, true); // initialize the log file
	}

	if ($environ) {
		setupLog(gettext("Full environment"));
	} else {
		setupLog(gettext("Primitive environment"));
		if ($connectDBErr) {
			setupLog(sprintf(gettext("Query error: %s"), $connectDBErr), true);
		}
	}
	zp_setCookie('setup_test_cookie', ZENPHOTO_VERSION, 3600);
}

if (!isset($_zp_setupCurrentLocale_result) || empty($_zp_setupCurrentLocale_result)) {
	if (DEBUG_LOCALE)
		debugLog('Setup checking locale');
	$_zp_setupCurrentLocale_result = setMainDomain();
	if (DEBUG_LOCALE)
		debugLog('$_zp_setupCurrentLocale_result = ' . $_zp_setupCurrentLocale_result);
}

$taskDisplay = array('create' => gettext("create"), 'update' => gettext("update"));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" />
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php printf('ZenPhoto20 %s', $upgrade); ?></title>
	<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />

	<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
		var imageErr = false;
		function toggle_visibility(id) {
			var e = document.getElementById(id);
			if (e.style.display == 'block')
				e.style.display = 'none';
			else
				e.style.display = 'block';
		}
	</script>
	<link rel="stylesheet" href="setup.css" type="text/css" />
</head>
<body>
	<div id="main">
		<h1><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png" title="<?php echo gettext('ZenPhoto20 Setup'); ?>" alt="<?php echo gettext('ZenPhoto20 Setup'); ?>" />
			<span><?php echo $upgrade; ?></span>
		</h1>
		<br />
		<div id="content">
			<?php
			$blindInstall = $warn = false;

			if ($connection && empty($_zp_options)) {
				primeOptions();
			}
			if (!$connection || !$setup_checked && (($upgrade && $autorun) || setupUserAuthorized())) {
				if ($blindInstall = ($upgrade && $autorun) && !setupUserAuthorized()) {
					ob_start(); //	hide output for auto-upgrade
				}
				?>
				<p>
					<?php printf(gettext('Welcome to ZenPhoto20! This page will set up version %1$s on your web server.'), ZENPHOTO_VERSION); ?>
				</p>
				<h2><?php echo gettext("Systems Check:"); ?></h2>
				<?php
				/**
				 * ************************************************************************
				 *                                                                        *
				 *                             SYSTEMS CHECK                              *
				 *                                                                        *
				 * ************************************************************************
				 */
				global $_zp_conf_vars;
				$good = true;
				if ($connection && $_zp_loggedin != ADMIN_RIGHTS) {
					if (TEST_RELEASE) {
						?>
						<div class="notebox">
							<?php echo '<p>' . gettext('<strong>Note:</strong> The release you are installing has debugging settings enabled!') . '</p>'; ?>
						</div>
						<?php
					}
					?>
					<ul>
						<?php
					} else {
						?>
						<ul>
							<?php
							$prevRel = false;
							checkmark(1, sprintf(gettext('Installing ZenPhoto20 v%s'), ZENPHOTO_VERSION), '', '');
						}
						chdir(dirname(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE));
						$test = safe_glob('*.log');
						array_push($test, basename(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE));
						$p = true;
						$wrong = array();
						foreach ($test as $file) {
							$permission = fileperms(dirname(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . '/' . $file) & 0777;
							if (!checkPermissions($permission, 0600)) {
								$p = -1;
								$wrong[$file] = sprintf('%04o', $permission);
							}
						}

						checkMark($p, sprintf(gettext('<em>%s</em> security'), DATA_FOLDER), sprintf(gettext('<em>%s</em> security [is compromised]'), DATA_FOLDER), sprintf(gettext('ZenPhoto20 suggests you make the sensitive files in the %1$s folder accessible by <em>owner</em> only (permissions = 0600). The file permissions for <em>%2$s</em> are %3$s which may allow unauthorized access.'), DATA_FOLDER, implode(', ', array_keys($wrong)), implode(', ', $wrong)));

						$err = versionCheck(PHP_MIN_VERSION, PHP_DESIRED_VERSION, PHP_VERSION);
						if (version_compare(PHP_VERSION, PHP_MIN_SUPPORTED_VERSION, '<')) {
							$vers = ' style="color: red;font-weight:bold;"';
						} else {
							$vers = '';
						}
						$good = checkMark($err, '<span' . $vers . '>' . sprintf(gettext("PHP version %s"), PHP_VERSION) . '</span>', "", sprintf(gettext('PHP Version %1$s or greater is required. Version %2$s or greater is strongly recommended as ealier versions may not be <a href="http://php.net/supported-versions.php">actively supported</a>. Use earlier versions at your own risk.'), PHP_MIN_VERSION, PHP_DESIRED_VERSION), false) && $good;

						checkmark($session && session_id() && $_initial_session_path !== false, gettext('PHP <code>Sessions</code>.'), gettext('PHP <code>Sessions</code> [appear to not be working].'), sprintf(gettext('PHP Sessions are required for administrative functions. Check your <code>session.save_path</code> (<code>%1$s</code>) and the PHP configuration <code>[session]</code> settings'), session_save_path()), true);

						@ini_set('session.use_strict_mode', 1);
						if (preg_match('#(1|ON)#i', @ini_get('session.use_strict_mode'))) {
							$strictSession = 1;
						} else {
							$strictSession = -1;
						}
						$good = checkMark($strictSession, gettext('PHP <code>session.use_strict_mode</code>'), gettext('PHP <code>session.use_strict_mode</code> [is not set]'), gettext('Enabling <code>session.use_strict_mode</code> is mandatory for general session security. Change your PHP.ini settings to <code>session.use_strict_mode = on</code>.')) && $good;

						if (preg_match('#(1|ON)#i', @ini_get('register_globals'))) {
							if ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) & ACK_REGISTER_GLOBALS) {
								$register_globals = -1;
								$aux = '';
							} else {
								$register_globals = false;
								$aux = ' ' . acknowledge(ACK_REGISTER_GLOBALS);
							}
						} else {
							$register_globals = true;
							$aux = '';
						}
						$good = checkMark($register_globals, gettext('PHP <code>Register Globals</code>'), gettext('PHP <code>Register Globals</code> [is set]'), gettext('PHP Register globals presents a security risk to any PHP application. See <a href="http://php.net/manual/en/security.globals.php"><em>Using Register Globals</em></a>. Change your PHP.ini settings to <code>register_globals = off</code>.') . $aux) && $good;

						if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {
							$safe = -1;
						} else {
							$safe = true;
						}
						checkMark($safe, gettext("PHP <code>Safe Mode</code>"), gettext("PHP <code>Safe Mode</code> [is set]"), gettext("ZenPhoto20 functionality is reduced when PHP <code>safe mode</code> restrictions are in effect."));

						if (!extension_loaded('suhosin')) {
							$blacklist = @ini_get("suhosin.executor.func.blacklist");
							if ($blacklist) {
								$zpUses = array('symlink' => 0);
								$abort = $issue = 0;
								$blacklist = explode(',', $blacklist);
								foreach ($blacklist as $key => $func) {
									if (array_key_exists($func, $zpUses)) {
										$abort = true;
										$issue = $issue | $zpUses[$func];
										if ($zpUses[$func]) {
											$blacklist[$key] = '<span style="color: red;">' . $func . '*</span>';
										}
									}
								}
								$issue--;
								$good = checkMark($issue, '', gettext('<code>Suhosin</code> module [is enabled]'), sprintf(gettext('The following PHP functions are blocked: %s. Flagged functions are required. Other functions in the list may be used, possibly causing reduced functionality or failures.'), '<code>' . implode('</code>, <code>', $blacklist) . '</code>'), $abort) && $good;
							}
						}

						primeMark(gettext('Magic_quotes'));
						if (get_magic_quotes_gpc()) {
							$magic_quotes_disabled = -1;
						} else {
							$magic_quotes_disabled = true;
						}
						checkMark($magic_quotes_disabled, gettext("PHP <code>magic_quotes_gpc</code>"), gettext("PHP <code>magic_quotes_gpc</code> [is enabled]"), gettext('We strongly recommend disabling <code>magic_quotes_gpc</code>.'));
						if (get_magic_quotes_runtime()) {
							$magic_quotes_disabled = 0;
						} else {
							$magic_quotes_disabled = true;
						}
						checkMark($magic_quotes_disabled, gettext("PHP <code>magic_quotes_runtime</code>"), gettext("PHP <code>magic_quotes_runtime</code> [is enabled]"), gettext('You must disable <code>magic_quotes_runtime</code>.'));
						checkMark(!ini_get('magic_quotes_sybase'), gettext("PHP <code>magic_quotes_sybase</code>"), gettext("PHP <code>magic_quotes_sybase</code> [is enabled]"), gettext('You must disable <code>magic_quotes_sybase</code>.'));

						switch (strtolower(@ini_get('display_errors'))) {
							case 0:
							case 'off':
							case 'stderr':
								$display = true;
								$aux = '';
								break;
							case 1:
							case 'on':
							case 'stdout':
							default:
								if (TEST_RELEASE || ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) & ACK_DISPLAY_ERRORS)) {
									$display = -1;
									$aux = '';
								} else {
									$display = 0;
									$aux = ' ' . acknowledge(ACK_DISPLAY_ERRORS);
								}
								break;
						}
						checkmark($display, gettext('PHP <code>display_errors</code>'), sprintf(gettext('PHP <code>display_errors</code> [is enabled]'), $display), gettext('This setting may result in PHP error messages being displayed on WEB pages. These displays may contain sensitive information about your site.') . $aux, $display && !TEST_RELEASE);

						checkMark($noxlate, gettext('PHP <code>gettext()</code> support'), gettext('PHP <code>gettext()</code> support [is not present]'), gettext("Localization requires native PHP <code>gettext()</code> support"));
						checkmark(function_exists('flock') ? 1 : -1, gettext('PHP <code>flock</code> support'), gettext('PHP <code>flock</code> support [is not present]'), gettext('Zenpoto uses <code>flock</code> for serializing critical regions of code. Without <code>flock</code> active sites may experience <em>race conditions</em> which may be causing inconsistent data.'));
						if ($_zp_setupCurrentLocale_result === false) {
							checkMark(-1, gettext('PHP <code>setlocale()</code>'), ' ' . gettext('PHP <code>setlocale()</code> failed'), gettext("Locale functionality is not implemented on your platform or the specified locale does not exist. Language translation may not work.") . '<br />');
							echo gettext('You can use the <em>debug</em> plugin to see which locales your server supports.');
						}
						primeMark(gettext('mb_strings'));
						if (function_exists('mb_internal_encoding')) {
							if (($mbcharset = mb_internal_encoding()) == LOCAL_CHARSET) {
								$mb = 1;
							} else {
								$mb = -1;
							}
							$m2 = sprintf(gettext('Setting <em>mbstring.internal_encoding</em> to <strong>%s</strong> in your <em>php.ini</em> file is recommended to insure accented and multi-byte characters function properly.'), LOCAL_CHARSET);
							checkMark($mb, gettext("PHP <code>mbstring</code> package"), sprintf(gettext('PHP <code>mbstring</code> package [Your internal character set is <strong>%s</strong>]'), $mbcharset), $m2);
						} else {
							if (LOCAL_CHARSET == 'ISO-8859-1') {
								$set = 'UTF-8';
							} else {
								$set = 'ISO-8859-1';
							}
							$test = $_zp_UTF8->convert('test', $set, LOCAL_CHARSET);
							if (empty($test)) {
								$m2 = gettext("You need to install the <code>mbstring</code> package or correct the issue with <code>iconv()</code>");
								checkMark(0, '', gettext("PHP <code>mbstring</code> package [is not present and <code>iconv()</code> is not working]"), $m2);
							} else {
								$m2 = gettext("Strings generated internally by PHP may not display correctly. (e.g. dates)");
								checkMark(-1, '', gettext("PHP <code>mbstring</code> package [is not present]"), $m2);
							}
						}
						if (($mbcharset = ini_get('default_charset')) == LOCAL_CHARSET) {
							$mb = 1;
						} else {
							$mb = -1;
						}
						checkMark($mb, gettext("PHP <code>default_charset</code>"), sprintf(gettext('PHP <code>default_charset</code> [Your default character set is <strong>%s</strong>]'), ini_get('default_charset')), sprintf(gettext('Setting <em>default_charset</em> to <strong>%s</strong> in your <em>php.ini</em> file is recommended to insure accented and multi-byte characters function properly.'), LOCAL_CHARSET));

						if ($environ) {
							/* Check for graphic library and image type support. */
							primeMark(gettext('Graphics library'));
							if (function_exists('zp_graphicsLibInfo')) {
								$graphics_lib = zp_graphicsLibInfo();
								if (array_key_exists('Library_desc', $graphics_lib)) {
									$library = $graphics_lib['Library_desc'];
								} else {
									$library = '';
								}
								$good = checkMark(!empty($library), sprintf(gettext("Graphics support: <code>%s</code>"), $library), gettext('Graphics support [is not installed]'), gettext('You need to install a graphics support library such as the <em>GD library</em> in your PHP')) && $good;
								if (!empty($library)) {
									$missing = array();
									if (!isset($graphics_lib['JPG'])) {
										$missing[] = 'JPEG';
									}
									if (!(isset($graphics_lib['GIF']))) {
										$missing[] = 'GIF';
									}
									if (!(isset($graphics_lib['PNG']))) {
										$missing[] = 'PNG';
									}
									if (count($missing) > 0) {
										if (count($missing) < 3) {
											if (count($missing) == 2) {
												$imgmissing = sprintf(gettext('Your PHP graphics library does not support %1$s or %2$s'), $missing[0], $missing[1]);
											} else {
												$imgmissing = sprintf(gettext('Your PHP graphics library does not support %1$s'), $missing[0]);
											}
											$err = -1;
											$mandate = gettext("To correct this you should install a graphics library with appropriate image support in your PHP");
										} else {
											$imgmissing = sprintf(gettext('Your PHP graphics library does not support %1$s, %2$s, or %3$s'), $missing[0], $missing[1], $missing[2]);
											$err = 0;
											$good = false;
											$mandate = gettext("To correct this you need to install GD with appropriate image support in your PHP");
										}
										checkMark($err, gettext("PHP graphics image support"), '', $imgmissing .
														"<br />" . gettext("The unsupported image types will not be viewable in your albums.") .
														"<br />" . $mandate);
									}
									if (!zp_imageCanRotate()) {
										checkMark(-1, '', gettext('Graphics Library rotation support [is not present]'), gettext('The graphics support library does not provide support for image rotation.'));
									}
								}
							} else {
								$graphicsmsg = '';
								foreach ($_zp_graphics_optionhandlers as $handler) {
									$graphicsmsg .= $handler->canLoadMsg($handler);
								}
								checkmark(0, '', gettext('Graphics support [configuration error]'), gettext('No image handling library was loaded. Be sure that your PHP has a graphics support.') . ' ' . trim($graphicsmsg));
							}
						}
						if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
							require( SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
							$cfg = true;
						} else {
							$cfg = false;
						}


						$good = checkMark($cfg, sprintf(gettext('<em>%1$s</em> file'), CONFIGFILE), sprintf(gettext('<em>%1$s</em> file [does not exist]'), CONFIGFILE), sprintf(gettext('Setup was not able to create this file. You will need to copy the <code>%1$s/zenphoto_cfg.txt</code> file to <code>%2$s/%3$s</code> then edit it as indicated in the file’s comments.'), ZENFOLDER, DATA_FOLDER, CONFIGFILE)) && $good;
						if ($cfg) {
							primeMark(gettext('File permissions'));
							if ($environ) {
								$chmodselector = '<form action="#"><input type="hidden" name="xsrfToken" value="' . setupXSRFToken() . '" />' .
												'<p>' . sprintf(gettext('Set File permissions to %s.'), permissionsSelector($permission_names, $chmod)) .
												'</p></form>';
							} else {
								$chmodselector = '';
							}
							if (array_key_exists($chmod | 4, $permission_names)) {
								$value = sprintf(gettext('<em>%1$s</em> (<code>0%2$o</code>)'), $permission_names[$chmod | 4], $chmod);
							} else {
								$value = sprintf(gettext('<em>unknown</em> (<code>%o</code>)'), $chmod);
							}
							if ($chmod > 0664) {
								if (isset($_zp_conf_vars['CHMOD'])) {
									$severity = -3;
								} else {
									$severity = -1;
								}
							} else {
								$severity = -2;
							}
							$msg = sprintf(gettext('File Permissions [are %s]'), $value);
							checkMark($severity, $msg, $msg, '<p>' . gettext('If file permissions are not set to <em>strict</em> or tighter there could be a security risk. However, on some servers the software does not function correctly with tight file permissions. If permission errors occur, run setup again and select a more relaxed permission.') . '</p>' .
											$chmodselector);

							$notice = 0;
							if (setupUserAuthorized()) {
								if ($environ) {
									if (isMac()) {
										checkMark(-1, '', gettext('Your filesystem is Macintosh'), gettext('Macintosh file names containing diacritical marks are beyond the scope of this software. You should avoid these.'), false);
										?>
										<input type="hidden" name="FILESYSTEM_CHARSET" value="UTF-8" />
										<?php
									} else if (isWin()) {
										checkMark(1, gettext('Your Windows filesystem character set is Western European (ISO)'), '', '', false);
										?>
										<input type="hidden" name="FILESYSTEM_CHARSET" value="ISO-8859-1" />
										<?php
									} else {
										primeMark(gettext('Character set'));
										$charset_defined = $_zp_UTF8->iconv_sets[FILESYSTEM_CHARSET];
										$test = '';
										if (($dir = opendir(SERVERPATH . '/' . DATA_FOLDER . '/')) !== false) {
											while (($file = readdir($dir)) !== false) {
												if (preg_match('/^charset([\._])t.*$/', $file, $matches)) {
													$test = $file;
													$test_internal = 'charset' . $matches[1] . 'tést';
													if (getSuffix($file)) {
														$test_internal .= '.' . getSuffix($file);
													}
													break;
												}
											}
											closedir($dir);
										}
										if (isset($_REQUEST['charset_attempts'])) {
											$tries = sanitize_numeric($_REQUEST['charset_attempts']);
										} else {
											$tries = 0;
										}

										switch (FILESYSTEM_CHARSET) {
											case 'ISO-8859-1':
												if ($tries & 2) {
													$trialset = 'unknown';
												} else {
													$trialset = 'UTF-8';
													$tries = $tries | 1;
												}
												break;
											default:
												if ($tries & 1) {
													$trialset = 'unknown';
												} else {
													$trialset = 'ISO-8859-1';
													$tries = $tries | 2;
												}
												break;
										}
										$msg2 = '<p>' . sprintf(gettext('If your server filesystem character set is different from <code>%s</code> and you create album or image filenames names containing characters with diacritical marks you may have problems with these objects.'), $charset_defined) . '</p>' .
														'<form action="#"><input type="hidden" name="xsrfToken" value="' . setupXSRFToken() . '" /><input type="hidden" name="charset_attempts" value="' . $tries . '" /><p>' .
														gettext('Change the filesystem character set define to %1$s') .
														'</p></form><br class="clearall">';

										if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
											$selectedset = $_zp_conf_vars['FILESYSTEM_CHARSET'];
										} else {
											$selectedset = 'unknown';
										}
										$msg = '';
										if ($test) {
											//	fount the test file
											if (file_exists(internalToFilesystem($test_internal))) {
												//	and the active character set define worked
												if (!isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
													$zp_cfg = updateConfigItem('FILESYSTEM_CHARSET', FILESYSTEM_CHARSET, $zp_cfg);
													storeConfig($zp_cfg);
												}
												$notice = 1;
												$msg = sprintf(gettext('The filesystem character define is %1$s [confirmed]'), $charset_defined);
												$msg1 = '';
											} else {
												if ($selectedset == 'unknown') {
													$notice = 1;
													$msg = gettext('The filesystem character define is UTF-8 [assumed]');
													$msg1 = '';
												} else {
													//	active character set is not correct
													$notice = 0;
													$msg1 = sprintf(gettext('The filesystem character define is %1$s [which seems wrong]'), $charset_defined);
												}
											}
										} else {
											//	no test file
											$msg1 = sprintf(gettext('The filesystem character define is %1$s [no test performed]'), $charset_defined);
											$msg2 = '<p>' . sprintf(gettext('Setup did not perform a test of the filesystem character set. You can cause setup to test for a proper definition by creating a file in your <code>%1$s</code> folder named <strong><code>charset_tést</code></strong> and re-running setup.'), DATA_FOLDER) . '</p>' . $msg2;
											if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
												//	but we have a define value
												$notice = -3;
											} else {
												//	no defined value, who knows....
												$notice = -1;
											}
										}
										checkMark($notice, $msg, $msg1, sprintf($msg2, charsetSelector($trialset)));
									}
									// UTF-8 URI
									if ($notice != -1) {
										$test = copy(SERVERPATH . '/' . ZENFOLDER . '/images/pass.png', $testjpg = SERVERPATH . '/' . DATA_FOLDER . '/' . internalToFilesystem('tést.jpg'));
										if (file_exists($testjpg)) {
											?>
											<li id="internal" class="pass limited">
												<span>
													<img src="<?php echo WEBPATH . '/' . DATA_FOLDER . '/' . urlencode('tést.jpg'); ?>" class="test_image"  onerror="imgError('internal');"/>
													<?php echo CHECKMARK_GREEN; ?>
													<?php echo gettext('Image URIs appear to require the <em>UTF-8</em> character set.') ?>
												</span>
											</li>
											<li id="filesystem" class="fail limited" style="display: none;">
												<span>
													<img src="<?php echo WEBPATH . '/' . DATA_FOLDER . '/' . urlencode(internalToFilesystem('tést.jpg')); ?>" title="filesystem" class="test_image" onerror="imgError('filesystem');"/>
													<?php echo CHECKMARK_GREEN; ?>
													<?php echo gettext('Image URIs appear require the <em>filesystem</em> character set.'); ?>
												</span>
											</li>
											<li id="unknown" class="warn" style="display: none;">
												<span>
													<?php echo WARNING_SIGN_ORANGE; ?>
													<?php echo gettext('Image URIs with diacritical marks appear to fail.'); ?>
												</span>
											</li>
											<script type="text/javascript">
												window.addEventListener('load', function () {
													$('.test_image').hide();
						<?php if ($displayLimited) {
							?>
														$('.limited').hide();
							<?php
						}
						?>
												}, false);
												var failed = 0;
												function imgError(title) {
													failed++;
													$('#' + title).hide();
													if (failed > 1) {
														$('#unknown').show();
														$('#setUTF8URI').val('unknown');
													} else {
														if (title == 'internal') {
															$('#setUTF8URI').val('filesystem');
															$('#filesystem').show();
														}
													}
												}
											</script>
											<?php
										}
									}
								}
							}
						}
						primeMark(gettext('Database'));
						foreach ($engines as $engine) {
							$handler = $engine['engine'];
							if ($handler == $confDB && $engine['enabled']) {
								$good = checkMark(1, sprintf(gettext('PHP <code>%s</code> support for configured Database'), $handler), '', '') && $good;
							} else {
								if (!$displayLimited) {
									if ($engine['enabled']) {

										if (isset($enabled['experimental'])) {
											?>
											<li class="note_warn">
												<?php echo BULLSEYE_DARKORANGE; ?>
												<?php echo sprintf(gettext(' <code>%1$s</code> support (<a onclick="$(\'#%1$s\').toggle()" >experimental</a>)'), $handler); ?>
											</li>
											<p class="warning" id="<?php echo $handler; ?>" style="display: none;">
												<?php echo $enabled['experimental'] ?>
											</p>
											<?php
										} else {
											?>
											<li class="note_ok">
												<?php echo BULLSEYE_GREEN; ?>
												<?php echo sprintf(gettext('PHP <code>%s</code> support'), $handler); ?>
											</li>
											<?php
										}
									} else {
										?>
										<li class="note_exception">
											<?php echo BULLSEYE_RED; ?>
											<?php echo sprintf(gettext('PHP <code>%s</code> support [is not installed]'), $handler); ?>
										</li>
										<?php
									}
								}
							}
						}
						$connection = db_connect($_zp_conf_vars, false);
						if ($connection) {
							if (empty($_zp_conf_vars['mysql_database'])) {
								$connection = false;
								$connectDBErr = gettext('No database selected');
							}
						} else {
							$connectDBErr = db_error();
						}
						if ($_zp_DB_connection) { // connected to DB software
							$dbsoftware = db_software();
							$dbapp = $dbsoftware['application'];
							$dbversion = $dbsoftware['version'];
							$required = $dbsoftware['required'];
							$desired = $dbsoftware['desired'];
							$sqlv = versionCheck($required, $desired, $dbversion);
							$good = checkMark($sqlv, sprintf(gettext('%1$s version %2$s'), $dbapp, $dbversion), "", sprintf(gettext('%1$s Version %2$s or greater is required. Version %3$s or greater is preferred. Use a lower version at your own risk.'), $dbapp, $required, $desired), false) && $good;
						}
						primeMark(gettext('Database connection'));

						if ($cfg) {
							if ($adminstuff = !extension_loaded(strtolower($selected_database)) || !$connection) {
								if (is_writable(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
									$good = false;
									checkMark(false, '', gettext("Database credentials in configuration file"), sprintf(gettext('<em>%1$s</em> reported: %2$s'), DATABASE_SOFTWARE, $connectDBErr));
									// input form for the information
									include(dirname(__FILE__) . '/setup-sqlform.php');
								} else {
									if ($connectDBErr) {
										$msg = $connectDBErr;
									} else {
										$msg = gettext("You have not correctly set your <strong>Database</strong> <code>user</code>, <code>password</code>, etc. in your configuration file and <strong>setup</strong> is not able to write to the file.");
									}
									$good = checkMark(!$adminstuff, gettext("Database setup in configuration file"), '', $msg) && $good;
								}
							} else {
								$good = checkMark((bool) $connection, sprintf(gettext('Connect to %s'), DATABASE_SOFTWARE), gettext("Connect to Database [<code>CONNECT</code> query failed]"), $connectDBErr) && $good;
							}
						}

						if ($_zp_DB_connection) {
							if ($connection) {
								if ($DBcreated) {
									checkMark(1, sprintf(gettext('Database <code>%s</code> created'), $_zp_conf_vars['mysql_database']), '');
								}
							} else {
								$good = 0;
								if ($oktocreate) {
									?>
									<li class="note">
										<div class="notebox">
											<p><?php echo sprintf(gettext('Click here to attempt to create <a href="?Create_Database" >%s</a>.'), $_zp_conf_vars['mysql_database']); ?></p>
										</div>
									</li>
									<?php
								} else if (!empty($_zp_conf_vars['mysql_database'])) {
									checkMark(0, '', sprintf(gettext('Database <code>%s</code> not created [<code>CREATE DATABASE</code> query failed]'), $_zp_conf_vars['mysql_database']), $connectDBErr);
								}
							}
							if ($environ && $connection) {
								$oldmode = db_getSQLmode();
								$result = db_setSQLmode();
								$msg = gettext('You may need to set <code>SQL mode</code> <em>empty</em> in your Database configuration.');
								if ($result) {
									$mode = db_getSQLmode();
									if ($mode === false) {
										checkMark(-1, '', sprintf(gettext('<code>SQL mode</code> [query failed]'), $oldmode), $msg);
									} else {
										if ($oldmode != $mode) {
											checkMark(-1, sprintf(gettext('<code>SQL mode</code> [<em>%s</em> overridden]'), $oldmode), '', gettext('Consider setting it <em>empty</em> in your Database configuration.'));
										} else {
											if (!empty($mode)) {
												$err = -1;
											} else {
												$err = 1;
											}
											checkMark($err, gettext('<code>SQL mode</code>'), sprintf(gettext('<code>SQL mode</code> [is set to <em>%s</em>]'), $mode), gettext('Consider setting it <em>empty</em> if you get Database errors.'));
										}
									}
								} else {
									checkMark(-1, '', gettext('<code>SQL mode</code> [SET SESSION failed]'), $msg);
								}

								$dbn = "`" . $_zp_conf_vars['mysql_database'] . "`.*";
								$db_results = db_permissions();

								$access = -1;
								$rightsfound = 'unknown';
								$rightsneeded = array(gettext('Select') => 'SELECT', gettext('Create') => 'CREATE', gettext('Drop') => 'DROP', gettext('Insert') => 'INSERT',
										gettext('Update') => 'UPDATE', gettext('Alter') => 'ALTER', gettext('Delete') => 'DELETE', gettext('Index') => 'INDEX');
								ksort($rightsneeded);
								$neededlist = '';
								foreach ($rightsneeded as $right => $value) {
									$neededlist .= '<code>' . $right . '</code>, ';
								}
								$neededlist = substr($neededlist, 0, -2) . ' ';
								$i = strrpos($neededlist, ',');
								$neededlist = substr($neededlist, 0, $i) . ' ' . gettext('and') . substr($neededlist, $i + 1);
								if ($db_results) {
									$report = "<br /><br /><em>" . gettext("Grants found:") . "</em> ";
									foreach ($db_results as $row) {
										$row = stripcslashes($row);
										$row_report = "<br /><br />" . $row;
										$r = str_replace(',', '', $row);
										preg_match('/\sON(.*)\sTO\s?/i', $r, $matches);
										$found = trim(isset($matches[1]) ? $matches[1] : NULL);
										if ($partial = (($i = strpos($found, '%')) !== false)) {
											$found = substr($found, 0, $i);
										}
										$rights = array_flip(explode(' ', $r));
										$rightsfound = 'insufficient';
										if (($found == $dbn) || ($found == "*.*") || $partial && preg_match('/^' . $found . '/xis', $dbn)) {
											$allow = true;
											foreach ($rightsneeded as $key => $right) {
												if (!isset($rights[$right])) {
													$allow = false;
												}
											}
											if (isset($rights['ALL']) || $allow) {
												$access = 1;
											}
											$report .= '<strong>' . $row_report . '</strong>';
										} else {
											$report .= $row_report;
										}
									}
								} else {
									$report = "<br /><br />" . gettext("The <em>SHOW GRANTS</em> query failed.");
								}
								checkMark($access, sprintf(gettext('Database <code>access rights</code> for <em>%s</em>'), $_zp_conf_vars['mysql_database']), sprintf(gettext('Database <code>access rights</code> for <em>%1$s</em> [%2$s]'), $_zp_conf_vars['mysql_database'], $rightsfound), sprintf(gettext("Your Database user must have %s rights."), $neededlist) . $report);


								$result = db_show('tables');
								$tableslist = '';
								$tables = array();
								if ($result) {
									$check = 1;
									while ($row = db_fetch_row($result)) {
										$tables[] = $row[0];
										$tableslist .= "<code>" . $row[0] . "</code>, ";
									}
									db_free_result($result);
								} else {
									$check = -1;
								}
								if (empty($tableslist)) {
									$msg = gettext('<em>SHOW TABLES</em> [found no tables]');
									$msg2 = '';
								} else {
									$msg = sprintf(gettext("<em>SHOW TABLES</em> found: %s"), substr($tableslist, 0, -2));
									$msg2 = '';
								}
								checkMark($check, $msg, gettext("<em>SHOW TABLES</em> [Failed]"), sprintf(gettext("The database did not return a list of the database tables for <code>%s</code>."), $_zp_conf_vars['mysql_database']) .
												"<br />" . gettext("<strong>Setup</strong> will attempt to create all tables. This will not over write any existing tables."));
								if (isset($_zp_conf_vars['UTF-8']) && $_zp_conf_vars['UTF-8']) {
									$fields = 0;
									$fieldlist = array();
									foreach (array('images' => 1, 'albums' => 2) as $lookat => $add) {
										if (in_array($_zp_conf_vars['mysql_prefix'] . $lookat, $tables)) {
											$columns = db_list_fields('images');
											if ($columns) {
												foreach ($columns as $col => $utf8) {
													if (!is_null($row['Collation']) && $row['Collation'] != 'utf8_unicode_ci') {
														$fields = $fields | $add;
														$fieldlist[] = '<code>' . $lookat . '->' . $col . '</code>';
													}
												}
											} else {
												$fields = 4;
											}
										}
									}
									$err = -1;
									switch ($fields) {
										case 0: // all is well
											$msg2 = '';
											$err = 1;
											break;
										case 1:
											$msg2 = gettext('Database <code>field collations</code> [Image table]');
											break;
										case 2:
											$msg2 = gettext('Database <code>field collations</code> [Album table]');
											break;
										case 3:
											$msg2 = gettext('Database <code>field collations</code> [Image and Album tables]');
											break;
										default:
											$msg2 = gettext('Database <code>field collations</code> [SHOW COLUMNS query failed]');
											break;
									}
									checkmark($err, gettext('Database <code>field collations</code>'), $msg2, sprintf(ngettext('%s is not UTF-8. You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code>', '%s are not UTF-8. You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code>', count($fieldlist)), implode(', ', $fieldlist)));
								} else {
									checkmark(-1, '', gettext('Database <code>$conf["UTF-8"]</code> [is not set <em>true</em>]'), gettext('You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code> and setting this <em>true</em>.'));
								}
							}
						}

						primeMark(gettext('ZenPhoto20 files'));
						@set_time_limit(120);
						$stdExclude = Array('Thumbs.db', 'readme.md', 'data');

						$lcFilesystem = file_exists(strtoupper(__FILE__));
						$base = SERVERPATH . '/';
						getResidentZPFiles(SERVERPATH . '/' . ZENFOLDER, $lcFilesystem, $stdExclude);
						if ($lcFilesystem) {
							$res = array_search(strtolower($base . ZENFOLDER . '/zenphoto.package'), $_zp_resident_files);
							$base = strtolower($base);
						} else {
							$res = array_search($base . ZENFOLDER . '/zenphoto.package', $_zp_resident_files);
						}
						unset($_zp_resident_files[$res]);
						$cum_mean = filemtime(SERVERPATH . '/' . ZENFOLDER . '/zenphoto.package');
						$hours = 3600;
						$lowset = $cum_mean - $hours;
						$highset = $cum_mean + $hours;

						$package_file_count = false;
						$package = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto.package');
						if ($lcFilesystem) { // case insensitive file systems
							$package = strtolower($package);
						}
						if (!empty($package)) {
							$installed_files = explode("\n", trim($package));
							$count = array_pop($installed_files);
							$package_file_count = is_numeric($count) && ($count > 0) && ($count == count($installed_files));
						}
						if (!$package_file_count) {
							checkMark(-1, '', gettext("ZenPhoto20 package [missing]"), gettext('The file <code>zenphoto.package</code> is either missing, not readable, or defective. Your installation may be corrupt!'));
							$installed_files = array();
						}
						$folders = array();
						if ($updatechmod) {
							$permissions = 1;
							setupLog(sprintf(gettext('Setting permissions (0%o) for ZenPhoto20 package.'), $chmod), true);
						} else {
							$permission = 0;
						}
						foreach ($installed_files as $key => $value) {
							$component_data = explode(':', $value);
							$value = trim($component_data[0]);
							if (count($component_data) > 1) {
								$fromPackage = trim($component_data[1]);
							} else {
								$fromPackage = '';
							}
							$component = $base . $value;
							if (file_exists($component)) {
								$res = array_search($component, $_zp_resident_files);
								if ($res !== false) {
									unset($_zp_resident_files[$res]);
								}
								if (is_dir($component)) {
									if ($updatechmod) {
										@chmod($component, $chmod | 0311);
										clearstatcache();
										$perms = fileperms($component) & 0777;
										if ($permissions == 1 && !checkPermissions($perms, $chmod | 0311)) {
											if (checkPermissions($perms & 0755, 0755) || TEST_RELEASE) { // could not set them, but they will work.
												$permissions = -1;
											} else {
												$permissions = 0;
											}
										}
									}
									$folders[$component] = $component;
									unset($installed_files[$key]);
									if (dirname($value) == THEMEFOLDER) {
										getResidentZPFiles($base . $value, $lcFilesystem, $stdExclude);
									}
								} else {
									if ($updatechmod) {
										@chmod($component, $chmod);
										clearstatcache();
										$perms = fileperms($component) & 0777;
										if ($permissions == 1 && !checkPermissions($perms, $chmod)) {
											if (checkPermissions($perms & 0644, 0644) || TEST_RELEASE) { // could not set them, but they will work.
												$permissions = -1;
											} else {
												$permissions = 0;
											}
										}
									}

									$t = filemtime($component);
									if ((!(TEST_RELEASE || $fromPackage == '*') && ($t < $lowset || $t > $highset))) {
										$installed_files[$key] = $value;
									} else {
										unset($installed_files[$key]);
									}
								}
							}
						}
						if ($updatechmod && count($folders) > 0) {
							foreach ($folders as $key => $folder) {
								if (!checkPermissions(fileperms($folder) & 0777, 0755)) { // need to set them?.
									@chmod($folder, $chmod | 0311);
									clearstatcache();
									$perms = fileperms($folder) & 0777;
									if ($permissions == 1 && !checkPermissions($perms, $chmod | 0311)) {
										if (checkPermissions($perms & 0755, 0755) || TEST_RELEASE) { // could not set them, but they will work.
											$permissions = 0;
										} else {
											$permissions = -1;
										}
									}
								}
							}
						}
						$plugin_subfolders = array();
						$Cache_html_subfolders = array();
						foreach ($installed_files as $key => $component) {
							$folders = explode('/', $component);
							$folder = array_shift($folders);
							switch ($folder) {
								case ALBUMFOLDER:
								case CACHEFOLDER:
								case DATA_FOLDER:
								case UPLOAD_FOLDER:
									unset($installed_files[$key]);
									break;
								case 'plugins':
									if ($folder{strlen($folder) - 1} == '/') {
										$plugin_subfolders[] = implode('/', rtrim($folders, '/'));
									}
									unset($installed_files[$key]); // not required
									break;
								case STATIC_CACHE_FOLDER:
									$Cache_html_subfolders[] = implode('/', $folders);
									unset($installed_files[$key]);
									break;
							}
						}
						$filelist = '';
						$report = $installed_files;
						if (count($report) > 15) {
							shuffle($report);
							$report = array_slice($report, 0, 15);
							natsort($report);
						}
						foreach ($report as $extra) {
							$filelist .= filesystemToInternal(str_replace($base, '', $extra) . '<br />');
						}
						if ($report != $installed_files) {
							$filelist .= '....<br />';
						}
						if (zpFunctions::hasPrimaryScripts() && count($installed_files) > 0) {
							if (defined('TEST_RELEASE') && TEST_RELEASE) {
								$msg1 = gettext("ZenPhoto20 core files [This is a <em>debug</em> build. Some files are missing or seem wrong]");
							} else {
								$msg1 = gettext("ZenPhoto20 core files [Some files are missing or seem wrong]");
							}
							$msg2 = gettext('Perhaps there was a problem with the upload. You should check the following files: ') . '<br /><code>' . substr($filelist, 0, -6) . '</code>';
							$mark = -1;
						} else {
							if (isset($rootupdate) && !$rootupdate) {
								$mark = 0;
								$msg1 = gettext("ZenPhoto20 core files [Could not update the root <em>index.php</em> file.]");
								$msg2 = sprintf(gettext('Perhaps there is a permissions issue. You should manually copy the %s <em>root_index.php</em> file to the installation root and rename it <em>index.php</em>.'), ZENFOLDER);
							} else {
								if (zpFunctions::hasPrimaryScripts()) {
									if (defined('TEST_RELEASE') && TEST_RELEASE) {
										$mark = -1;
										$msg1 = gettext("ZenPhoto20 core files [This is a <em>debug</em> build]");
									} else {
										$msg1 = '';
										$mark = 1;
									}
								} else {
									$mark = -1;
									$msg1 = gettext("ZenPhoto20 core files [This is a <em>clone</em> installation]");
								}
								$msg2 = '';
							}
						}
						checkMark($mark, gettext("ZenPhoto20 core files"), $msg1, $msg2, false);

						if (setupUserAuthorized() && $connection && zpFunctions::hasPrimaryScripts()) {
							primeMark(gettext('Installation files'));
							$systemlist = $filelist = array();
							$phi_ini_count = $svncount = 0;
							foreach ($_zp_resident_files as $extra) {
								if (getSuffix($extra) == 'xxx') {
									@unlink($extra); //	presumed to be protected copies of the setup files
								} else if (strpos($extra, 'php.ini') !== false) {
									$phi_ini_count++;
								} else if (defined('TEST_RELEASE') && TEST_RELEASE || (strpos($extra, '/.svn') === false)) {
									$systemlist[] = str_replace($base, '', $extra);
								} else {
									$svncount++;
								}
							}
							if ($svncount) {
								$filelist[] = '<br />' . sprintf(ngettext('.svn [%s instance]', '.svn [%s instances]', $svncount), $svncount);
							}
							if ($phi_ini_count && TEST_RELEASE) {
								$filelist[] = '<br />' . sprintf(ngettext('php.ini [%s instance]', 'php.ini [%s instances]', $phi_ini_count), $phi_ini_count);
							}
							if ($package_file_count) { //	no point in this if the package list was damaged!
								if (!empty($systemlist)) {
									if (isset($_GET['delete_extra'])) {
										foreach ($systemlist as $key => $file_s) {
											$file8 = $_zp_UTF8->convert($file_s, FILESYSTEM_CHARSET, 'UTF-8');
											$file = $base . $file_s;
											if (!is_dir($file)) {
												@chmod($file, 0777);
												if (@unlink($file) || !file_exists($file)) {
													unset($systemlist[$key]);
												} else {
													$filelist[] = $file8;
												}
											}
										}
										rsort($systemlist);
										foreach ($systemlist as $key => $file_s) {
											$file8 = $_zp_UTF8->convert($file, FILESYSTEM_CHARSET, 'UTF-8');
											$file = $base . $file_s;
											@chmod($file, 0777);
											if (is_dir($file)) {
												$offspring = safe_glob($file . '/*.*');
												foreach ($offspring as $child) {
													if (!(@unlink($child) || !file_exists($child))) {
														$filelist[] = $file8 . '/' . $_zp_UTF8->convert($file_s, FILESYSTEM_CHARSET, 'UTF-8');
													}
												}
												if (!@rmdir($file) || is_dir($file)) {
													$filelist[] = $file8;
												}
											} else {
												if (@unlink($file) || !file_exists($file)) {
													unset($systemlist[$key]);
												} else {
													$filelist[] = $file8;
												}
											}
										}
										if (!empty($filelist)) {
											checkmark(-1, '', gettext('ZenPhoto20 core folders [Some unknown files were found]'), gettext('The following files could not be deleted.') . '<br /><code>' . implode('<br />', $filelist) . '<code>');
										}
									} else {
										checkMark(-1, '', gettext('ZenPhoto20 core folders [Some unknown files were found]'), gettext('You should remove the following files: ') . '<br /><code>' . $_zp_UTF8->convert(implode('<br />', $systemlist), FILESYSTEM_CHARSET, 'UTF-8') .
														'</code><p class="buttons"><a href="?delete_extra' . ($debug ? '&amp;debug' : '') . '">' . gettext("Delete extra files") . '</a></p><br class="clearall"><br />');
									}
								}
								checkMark($permissions, gettext("ZenPhoto20 core file permissions"), gettext("ZenPhoto20 core file permissions [not correct]"), gettext('Setup could not set the one or more components to the selected permissions level. You will have to set the permissions manually.'));
							}
						}
						$msg = gettext("<em>.htaccess</em> file");
						$Apache = stristr($_SERVER['SERVER_SOFTWARE'], "apache");
						$Nginx = stristr($_SERVER['SERVER_SOFTWARE'], "nginx");
						$htfile = SERVERPATH . '/.htaccess';
						$copyaccess = false;
						if (file_exists($htfile)) {
							$ht = trim(@file_get_contents($htfile));
							$htu = strtoupper($ht);
						} else {
							$ht = $htu = false;
							$copyaccess = $Apache;
						}
						$vr = "";
						$ch = 1;
						$j = 0;
						$err = '';
						$desc = '';
						if (empty($htu)) {
							$err = gettext("<em>.htaccess</em> file [is empty or does not exist]");
							$ch = -1;
							if ($Apache) {
								$desc = gettext('If you have the mod_rewrite module enabled an <em>.htaccess</em> file is required the root zenphoto folder to create cruft-free URLs.') .
												'<br /><br />' . gettext('You can ignore this warning if you do not intend to set the <code>mod_rewrite</code> option.');
								if (setupUserAuthorized()) {
									$desc .= ' ' . gettext('<p class="buttons"><a href="?copyhtaccess" >Make setup create the file</a></p><br style="clear:both" /><br />');
								}
							} else if ($Nginx) {
								$err = gettext("Server seems to be <em>nginx</em>");
								$mod = "&amp;mod_rewrite"; //	enable test to see if it works.
								$desc = gettext('If you wish to create cruft-free URLs, you will need to configuring <em>rewriting</em> for your NGINX server so that any link that does not go directly to a file goes to the installation root <code>index.php</code> script.') . ' ' .
												'<br /><br />' . gettext('You can ignore this warning if you do not intend to set the <code>mod_rewrite</code> option.');
							} else {
								$mod = "&amp;mod_rewrite"; //	enable test to see if it works.
								$desc = gettext("Server seems not to be <em>Apache</em> or <em>Apache-compatible</em>, <code>mod_rewrite</code> may not be available.");
							}
						} else {
							$i = strpos($htu, 'VERSION');
							if ($i !== false) {
								$j = strpos($htu, ";");
								$vr = trim(substr($htu, $i + 7, $j - $i - 7));
							}

							$ch = !empty($vr) && version_compare($vr, HTACCESS_VERSION, '>=');
							$d = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/') . '/';
							$d = str_replace(' ', '%20', $d); //	apache appears to trip out if there is a space in the rewrite base
							if (!$ch) { // wrong version
								$oht = trim(@file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/oldhtaccess'));
								//fix the rewritebase
								$i = strpos($oht, 'RewriteBase /zenphoto');
								$oht = substr($oht, 0, $i) . "RewriteBase $d" . substr($oht, $i + 21);
								$oht = trim($oht);
								if ($oht == $ht) { // an unmodified .htaccess file, we can just replace it
									$ht = trim(file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/htaccess'));
									$i = strpos($ht, 'RewriteBase /zenphoto');
									$ht = substr($ht, 0, $i) . "RewriteBase $d" . substr($ht, $i + 21);
									$htu = strtoupper($ht);
									@chmod($htfile, 0777);
									@unlink($htfile);
									$ch = file_put_contents($htfile, trim($ht));
									@chmod($htfile, 0444);
								}
							}
							if (!$ch) {
								if (!$Apache) {
									$desc = gettext("Server seems not to be Apache or Apache-compatible, <code>.htaccess</code> not required.");
									$ch = -1;
								} else {
									$desc = sprintf(gettext("The <em>.htaccess</em> file in your root folder is not the same version as the one distributed with this version of ZenPhoto20. If you have made changes to <em>.htaccess</em>, merge those changes with the <em>%s/htaccess</em> file to produce a new <em>.htaccess</em> file."), ZENFOLDER);
									if (setupUserAuthorized()) {
										$desc .= ' ' . gettext('<p class="buttons"><a href="?copyhtaccess" >Replace the existing <em>.htaccess</em> file with the current version</a></p><br style="clear:both" /><br />');
									}
								}
								$err = gettext("<em>.htaccess</em> file [wrong version]");
							}
						}

						$rw = '';
						if ($ch > 0) {
							$i = strpos($htu, 'REWRITEENGINE');
							if ($i === false) {
								$rw = '';
							} else {
								$j = strpos($htu, "\n", $i + 13);
								$rw = trim(substr($htu, $i + 13, $j - $i - 13));
							}
							if (!empty($rw)) {
								$msg = sprintf(gettext("<em>.htaccess</em> file (<em>RewriteEngine</em> is <strong>%s</strong>)"), $rw);
								$mod = "&amp;mod_rewrite=$rw";
							}
						}
						$good = checkMark($ch, $msg, $err, $desc, false) && $good;

						$base = true;
						$f = '';
						if ($rw == 'ON') {
							$i = strpos($htu, 'REWRITEBASE', $j);
							if ($i === false) {
								$base = false;
								$b = '';
								$err = gettext("<em>.htaccess</em> RewriteBase [is <em>missing</em>]");
								$i = $j + 1;
							} else {
								$j = strpos($htu, "\n", $i + 11);
								$bs = trim(substr($ht, $i + 11, $j - $i - 11));
								$base = ($bs == $d);
								$b = sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code>"), $bs);
								$err = sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code> [Does not match install folder]"), $bs);
							}
							$f = '';
							$save = false;
							if (!$base) {
								$ht = substr($ht, 0, $i) . "RewriteBase $d\n" . substr($ht, $j + 1);
								$save = $base = true;
								$b = sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code> (fixed)"), $d);
							}
							// upgrade the site closed rewrite rules
							preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed\.php|', $ht, $matches);
							$siteupdate = false;
							foreach ($matches[0] as $match) {
								if (strpos($match, 'index\.php$') !== false) {
									$match1 = str_replace('index\.php$', 'index\.php(.*)$', $match);
									$match1 = str_replace('closed.php', 'closed.php%1', $match1);
									$ht = str_replace($match, $match1, $ht);
									$siteupdate = $save = true;
								}
							}
							if ($save) {
								// try and fix it
								@chmod($htfile, 0777);
								if (is_writeable($htfile)) {
									if (@file_put_contents($htfile, $ht)) {
										$err = '';
									}
									clearstatcache();
								}
								@chmod($htfile, 0444);
							}
							$good = checkMark($base, $b, $err, gettext("Setup was not able to write to the file change RewriteBase match the install folder.") .
															"<br />" . sprintf(gettext("Either make the file writeable or set <code>RewriteBase</code> in your <code>.htaccess</code> file to <code>%s</code>."), $d)) && $good;
							if ($siteupdate) {
								$good = checkMark($save, gettext('Rewrite rules updated'), gettext('Rewrite rules updated [not updated]'), gettext("Setup was not able to write to the file change the rewrite rules for site upgrades.")) && $good;
							}
						}
						//robots.txt file
						$robots = file_get_contents(dirname(dirname(__FILE__)) . '/example_robots.txt');
						if ($robots === false) {
							checkmark(-1, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not find the  <em>example_robots.txt</em> file.'));
						} else {
							if (file_exists(SERVERPATH . '/robots.txt')) {
								checkmark(-2, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup did not create a <em>robots.txt</em> file because one already exists.'));
							} else {
								$d = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
								if ($d == '/')
									$d = '';
								$robots = str_replace('/zenphoto', $d, trim($robots));
								$rslt = file_put_contents(SERVERPATH . '/robots.txt', $robots);
								if ($rslt === false) {
									$rslt = -1;
								} else {
									$rslt = 1;
								}
								checkmark($rslt, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not create a <em>robots.txt</em> file.'));
							}
						}

						if (isset($_zp_conf_vars['external_album_folder']) && !is_null($_zp_conf_vars['external_album_folder'])) {
							checkmark(-1, 'albums', gettext("albums [<code>\$conf['external_album_folder']</code> is deprecated]"), sprintf(gettext('You should update your configuration file to conform to the current %1$s example file.'), CONFIGFILE));
							$_zp_conf_vars['album_folder_class'] = 'external';
							$albumfolder = $_zp_conf_vars['external_album_folder'];
						}
						if (!isset($_zp_conf_vars['album_folder_class'])) {
							$_zp_conf_vars['album_folder_class'] = 'std';
						}
						if (isset($_zp_conf_vars['album_folder'])) {
							$albumfolder = str_replace('\\', '/', $_zp_conf_vars['album_folder']);
							switch ($_zp_conf_vars['album_folder_class']) {
								case 'std':
									$albumfolder = str_replace('\\', '/', SERVERPATH) . $albumfolder;
									break;
								case 'in_webpath':
									$webpath = $_SERVER['SCRIPT_NAME'];
									$root = SERVERPATH;
									if (!empty($webpath)) {
										$root = str_replace('\\', '/', dirname($root));
									}
									$albumfolder = $root . $albumfolder;
									break;
							}
							$good = folderCheck('albums', $albumfolder, $_zp_conf_vars['album_folder_class'], NULL, true, $chmod | 0311, $updatechmod) && $good;
						} else {
							checkmark(-1, gettext('<em>albums</em> folder'), gettext('<em>albums</em> folder [The line <code>\$conf[\'album_folder\']</code> is missing from your configuration file]'), sprintf(gettext('You should update your configuration file to conform to the current %1$s example file.'), CONFIGFILE));
						}

						$good = folderCheck('cache', SERVERPATH . '/' . CACHEFOLDER . '/', 'std', NULL, true, $chmod | 0311, $updatechmod) && $good;
						$good = checkmark(file_exists($en_US), gettext('<em>locale</em> folders'), gettext('<em>locale</em> folders [Are not complete]'), gettext('Be sure you have uploaded the complete ZenPhoto20 package. You must have at least the <em>en_US</em> folder.')) && $good;
						$good = folderCheck(gettext('uploaded'), SERVERPATH . '/' . UPLOAD_FOLDER . '/', 'std', NULL, false, $chmod | 0311, $updatechmod) && $good;
						$good = folderCheck(DATA_FOLDER, SERVERPATH . '/' . DATA_FOLDER . '/', 'std', NULL, false, $chmod | 0311, $updatechmod) && $good;
						@rmdir(SERVERPATH . '/' . DATA_FOLDER . '/mutex');
						@mkdir(SERVERPATH . '/' . DATA_FOLDER . '/' . MUTEX_FOLDER, $chmod | 0311);

						$good = folderCheck(gettext('HTML cache'), SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/', 'std', $Cache_html_subfolders, true, $chmod | 0311, $updatechmod) && $good;
						$good = folderCheck(gettext('Third party plugins'), SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/', 'std', $plugin_subfolders, true, $chmod | 0311, $updatechmod) && $good;
						?>
					</ul>
					<?php
					if ($good) {
						$dbmsg = "";
					} else {
						if (setupUserAuthorized()) {
							?>
							<div class="error">
								<?php echo gettext("You need to address the problems indicated above then run <code>setup</code> again."); ?>
							</div>
							<p class='buttons'>
								<a href="?refresh" title="<?php echo gettext("Setup failed."); ?>" style="font-size: 15pt; font-weight: bold;">
									<?php echo CROSS_MARK_RED; ?>
									<?php echo gettext("Refresh"); ?>
								</a>
							</p>
							<br class="clearall">
								<br />
								<?php
							} else {
								?>
								<div class="error">
									<?php
									if (zp_loggedin()) {
										echo gettext("You need <em>USER ADMIN</em> rights to run setup.");
									} else {
										echo gettext('You must be logged in to run setup.');
									}
									?>
								</div>
								<?php
								$_zp_authority->printLoginForm('', false);
							}
							if ($noxlate > 0) {
								setupLanguageSelector();
							}
							?>
							<br class="clearall">
								<?php
								echo "\n</div><!-- content -->";
								echo "\n</div><!-- main -->";
								printSetupFooter();
								echo "</body>";
								echo "</html>";
								exit();
							}
						} else {
							$dbmsg = gettext("database connected");
						} // system check
						if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
							loadConfiguration();

							$task = '';
							if (isset($_GET['create'])) {
								$task = 'create';
								$create = array_flip(explode(',', sanitize($_REQUEST['create'])));
							}
							if (isset($_REQUEST['update'])) {
								$task = 'update';
							}
							if (db_connect($_zp_conf_vars, false) && empty($task)) {
								$result = db_show('tables');
								$tables = array();
								$prefix = $_zp_conf_vars['mysql_prefix'];
								$prefixLC = strtolower($prefix);
								$prefixUC = strtoupper($prefixLC);
								if ($result) {
									while ($row = db_fetch_row($result)) {
										$key = $row[0];
										$key = str_replace(array($prefixLC, $prefixUC), $_zp_conf_vars['mysql_prefix'], $key);
										$tables[$key] = 'update';
									}
									db_free_result($result);
								}
								$expected_tables = array($_zp_conf_vars['mysql_prefix'] . 'options', $_zp_conf_vars['mysql_prefix'] . 'albums',
										$_zp_conf_vars['mysql_prefix'] . 'images', $_zp_conf_vars['mysql_prefix'] . 'comments',
										$_zp_conf_vars['mysql_prefix'] . 'administrators', $_zp_conf_vars['mysql_prefix'] . 'admin_to_object',
										$_zp_conf_vars['mysql_prefix'] . 'tags', $_zp_conf_vars['mysql_prefix'] . 'obj_to_tag',
										$_zp_conf_vars['mysql_prefix'] . 'captcha',
										$_zp_conf_vars['mysql_prefix'] . 'pages', $_zp_conf_vars['mysql_prefix'] . 'news2cat',
										$_zp_conf_vars['mysql_prefix'] . 'news_categories', $_zp_conf_vars['mysql_prefix'] . 'news',
										$_zp_conf_vars['mysql_prefix'] . 'menu', $_zp_conf_vars['mysql_prefix'] . 'plugin_storage',
										$_zp_conf_vars['mysql_prefix'] . 'search_cache'
								);

								foreach ($expected_tables as $needed) {
									if (!isset($tables[$needed])) {
										$tables[$needed] = 'create';
									}
								}
							}
							// Prefix the table names. These already have `backticks` around them!
							$tbl_albums = prefix('albums');
							$tbl_comments = prefix('comments');
							$tbl_images = prefix('images');
							$tbl_options = prefix('options');
							$tbl_administrators = prefix('administrators');
							$tbl_admin_to_object = prefix('admin_to_object');
							$tbl_tags = prefix('tags');
							$tbl_obj_to_tag = prefix('obj_to_tag');
							$tbl_captcha = prefix('captcha');
							$tbl_news = prefix('news');
							$tbl_pages = prefix('pages');
							$tbl_news_categories = prefix('news_categories');
							$tbl_news2cat = prefix('news2cat');
							$tbl_menu_manager = prefix('menu');
							$tbl_plugin_storage = prefix('plugin_storage');
							$tbl_searches = prefix('search_cache');
							$updateErrors = false;
							if (isset($_GET['create']) || isset($_REQUEST['update']) || isset($_GET['protect_files']) && db_connect($_zp_conf_vars, false)) {
								if (!isset($_GET['protect_files'])) {

									echo "<h3>" . gettext("About to update tables") . "....</h3>";
									setupLog(gettext("Begin database creation and update"), true);

									require_once(SERVERPATH . '/' . ZENFOLDER . '/setup/database.php');

									echo "<h3>";
									if ($updateErrors) {
										$autorun = false;
										echo gettext('Done with table update with errors. See the <code>setup</code> log for details.');
									} else {
										echo gettext('Done with table update.');
									}
									echo "</h3>";

									checkUnique($tbl_administrators, array('valid' => 0, 'user' => 0));
									checkUnique($tbl_albums, array('folder' => 0));
									checkUnique($tbl_images, array('albumid' => 0, 'filename' => 0));
									checkUnique($tbl_options, array('name' => 0, 'ownerid' => 0, 'theme' => 0));
									checkUnique($tbl_news_categories, array('titlelink' => 0));
									checkUnique($tbl_news, array('titlelink' => 0));
									checkUnique($tbl_pages, array('titlelink' => 0));
									checkUnique($tbl_tags, array('name' => 0));

									setupLog(gettext("Done with database creation and update"), true);

									// set defaults on any options that need it
									require(dirname(__FILE__) . '/setup-option-defaults.php');

									if ($debug == 'albumids') {
										// fixes 1.2 move/copy albums with wrong ids
										$albums = $_zp_gallery->getAlbums();
										foreach ($albums as $album) {
											checkAlbumParentid($album, NULL, 'setuplog');
										}
									}
								}

								$clones = array();

								if ($_zp_loggedin == ADMIN_RIGHTS) {
									$filelist = safe_glob(SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER . '/*.zdb');
									if (count($filelist) > 0) {
										$link = sprintf(gettext('You may <a href="%1$s">set your admin user and password</a> or <a href="%2$s">run backup-restore</a>'), WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=admin', WEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php');
										$autorun = false;
									} else {
										$link = sprintf(gettext('You need to <a href="%1$s">set your admin user and password</a>'), WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=admin');
										if ($autorun == 'admin' || $autorun == 'gallery') {
											$autorun = WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=admin';
										}
									}
								} else {
									if (extensionEnabled('cloneZenphoto')) {
										require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cloneZenphoto.php');
										if (class_exists('cloneZenphoto'))
											$clones = cloneZenphoto::clones();
									}
									$autorun = false;
									foreach ($clones as $clone => $data) {
										$url = $data['url'];
										?>
										<p class = "delayshow" style = "display:none;"><?php echo sprintf(gettext('Setup <a href="%1$s" target="_blank">%2$s</a>'), $data['url'] . ZENFOLDER . '/setup/index.php?autorun', $clone);
										?></p>
										<?php
									}
								}
								$link = sprintf(gettext('You can now <a href="%1$s">administer your gallery.</a>'), WEBPATH . '/' . ZENFOLDER . '/admin.php');
								?>
								<p id="golink" class="delayshow" style="display:none;"><?php echo $link; ?></p>
								<?php
								switch ($autorun) {
									case false:
										break;
									case 'gallery':
									case 'admin':
										$autorun = WEBPATH . '/' . ZENFOLDER . '/admin.php';
										break;
									default:
										break;
								}
								?>
								<input type="hidden" id="setupErrors" value="<?php echo (int) $updateErrors; ?>" />
								<script type="text/javascript">
									function launchAdmin() {
										window.location = '<?php echo WEBPATH . '/' . ZENFOLDER . '/admin.php'; ?>';
									}
									window.onload = function () {
										var errors = $('#setupErrors').val();

										$.ajax({
											type: 'POST',
											cache: false,
											data: 'errors=' + errors,
											url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/setup/setupComplete.php'
										});
										$('.delayshow').show();
		<?php
		if ($autorun) {
			?>
											if (!imageErr) {
												$('#golink').hide();
												launchAdmin();
											}
			<?php
		}
		?>
									}
								</script>
								<?php
							} else if (db_connect($_zp_conf_vars, false)) {
								$task = '';
								if (setupUserAuthorized() || $blindInstall) {
									if (!empty($dbmsg)) {
										?>
										<h2><?php echo $dbmsg; ?></h2>
										<?php
									}
									$task = "update";
									if ($debug) {
										$task .= '&debug=' . $debug;
									}
									if ($copyaccess) {
										$task .= '&copyhtaccess';
									}
								}

								if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_news'])) {
									$hideGoButton = ' style="display:none"';
									$autorun = false;
									?>
									<div class="warning" id="dbrestructure">
										<p><?php echo gettext('<strong>Warning!</strong> This upgrade makes structural changes to the database which are not easily reversed. Be sure you have a database backup before proceeding.'); ?></p>
										<form>
											<input type="hidden" name="xsrfToken" value="<?php echo setupXSRFToken(); ?>" />
											<p>
												<?php printf(gettext('%s I acknowledge that proceeding will restructure my database.'), '<input type="checkbox" id="agree" value="0" onclick="$(\'#setup\').show();$(\'#agree\').attr(\'checked\',\'checked\')" />') ?>
											</p>
										</form>
									</div>
									<?php
								} else {
									$hideGoButton = '';
								}
								if ($warn) {
									$icon = WARNING_SIGN_ORANGE;
								} else {
									$icon = CHECKMARK_GREEN;
								}
								if ($autorun) {
									$task .= '&autorun = ' . $autorun;
								}
								if ($blindInstall) {
									ob_end_clean();
									$blindInstall = false;
									$stop = !$autorun;
								} else {
									$stop = !setupUserAuthorized();
								}
								if ($stop) {
									?>
									<div class="error">
										<?php
										if (zp_loggedin()) {
											echo gettext("You need <em>USER ADMIN</em> rights to run setup.");
										} else {
											echo gettext('You must be logged in to run setup.');
										}
										?>
									</div>
									<?php
									$_zp_authority->printLoginForm('', false);
								} else {
									if (!empty($task) && substr($task, 0, 1) != '&') {
										$task = '&' . $task;
									}
									$task = html_encode($task);
									?>
									<form id="setup" action="<?php echo WEBPATH . '/' . ZENFOLDER, '/setup/index.php?checked' . $task . $mod; ?>" method="post"<?php echo $hideGoButton; ?> >
										<input type="hidden" name="setUTF8URI" id="setUTF8URI" value="internal" />
										<input type="hidden" name="xsrfToken" value="<?php echo setupXSRFToken(); ?>" />
										<?php
										if ($autorun) {
											?>
											<input type="hidden" id="autorun" name="autorun" value="<?php echo html_encode($autorun); ?>" />
											<?php
										}
										?>
										<p class="buttons"><button class="submitbutton" id="submitbutton" type="submit"	title="<?php echo gettext('run setup'); ?>" ><?php echo $icon; ?> <?php echo gettext("Go"); ?></button></p>
										<br class="clearall">
											<br />
									</form>
									<?php
								}
								if ($autorun) {
									?>
									<script type="text/javascript">
										$('#submitbutton').hide();
										$('#setup').submit();
									</script>
									<?php
								}
							} else {
								?>
								<div class="error">
									<h3><?php echo gettext("database did not connect"); ?></h3>
									<p>
										<?php echo gettext("If you have not created the database yet, now would be a good time."); ?>
									</p>
								</div>
								<?php
							}
						} else {
							// The config file hasn't been created yet. Show the steps.
							?>
							<div class="error">
								<?php echo sprintf(gettext('The %1$s file does not exist.'), CONFIGFILE); ?>
							</div>
							<?php
						}

						if ($blindInstall) {
							ob_end_clean();
						}
						?>
						<?php
						if ($noxlate > 0 && !isset($_GET['checked'])) {
							setupLanguageSelector();
						}
						?>
						<br class="clearall">
							</div><!-- content -->
							</div><!-- main -->
							<?php
							printSetupFooter();
							?>
							</body>
							</html>
							<?php
							$setupMutex->unlock();
							exitZP();
							?>