<?php
/**
 * install routine for zenphoto
 * @package zpcore\setup
 */
// force UTF-8 Ø
Define('PHP_MIN_VERSION', '7.0.0');
Define('PHP_DESIRED_VERSION', '8.0.0');

// leave this as the first executable statement to avoid problems with PHP not having gettext support.
if (!function_exists("gettext")) {
	require_once(dirname(dirname(__FILE__)) . '/libs/functions-gettext.php');
	$noxlate = -1;
} else {
	$noxlate = 1;
}
define('HTACCESS_VERSION', '1.4.12'); // be sure to change this the one in .htaccess when the .htaccess file is updated.

define('OFFSET_PATH', 2);

if (version_compare(PHP_VERSION, PHP_MIN_VERSION, '<')) {
	die(sprintf(gettext('Zenphoto requires PHP version %s or greater'), PHP_MIN_VERSION));
}
require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');

session_cache_limiter('nocache');
$session = session_start();

header('Content-Type: text/html; charset=UTF-8');
header("HTTP/1.0 200 OK");
header("Status: 200 OK");
header("Cache-Control: no-cache, must-revalidate, no-store, pre-check=0, post-check=0, max-age=0");
header("Pragma: no-cache");
header('Last-Modified: ' . ZP_LAST_MODIFIED);
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

require_once(dirname(__FILE__) . '/class-setup.php');
require_once(dirname(__FILE__) . '/class-setupmutex.php');
require_once(dirname(dirname(__FILE__)) . '/classes/class-maintenancemode.php');
//allow only one setup to run
$setupMutex = new setupMutex();
$setupMutex->lock();

if ($_zp_setup_debug = isset($_REQUEST['debug'])) {
	if (!$_zp_setup_debug = $_REQUEST['debug']) {
		$_zp_setup_debug = true;
	}
}

$setup_checked = isset($_GET['checked']);
$upgrade = false;

require_once(dirname(dirname(__FILE__)) . '/libs/class-utf8.php');
if (!function_exists('mb_internal_encoding')) {
	require_once(dirname(dirname(__FILE__)) . '/libs/functions-utf8.php');
}
global $_zp_utf8;
$_zp_utf8 = new utf8();

if (isset($_REQUEST['autorun'])) {
	if (!empty($_REQUEST['autorun'])) {
		$_zp_setup_autorun = setup::sanitize($_REQUEST['autorun']);
	} else {
		$_zp_setup_autorun = 'admin';
	}
	unset($_GET['autorun']);
	unset($_POST['autorun']);
} else {
	$_zp_setup_autorun = false;
}

$_zp_setup_chmod = fileperms(dirname(dirname(__FILE__))) & 0666;

$en_US = dirname(dirname(__FILE__)) . '/locale/en_US/';
if (!file_exists($en_US)) {
	@mkdir(dirname(dirname(__FILE__)) . '/locale/', $_zp_setup_chmod | 0311);
	@mkdir($en_US, $_zp_setup_chmod | 0311);
}

$zptime = time();
if (!file_exists($_zp_setup_serverpath . '/' . DATA_FOLDER)) {
	@mkdir($_zp_setup_serverpath . '/' . DATA_FOLDER, $_zp_setup_chmod | 0311);
}

@unlink(SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg.bak'); //	remove any old backup file

if (file_exists($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	$zpconfig = file_get_contents($oldconfig);
	if (strpos($zpconfig, '<?php') === false) {
		$zpconfig = "<?php\n" . $zpconfig . "\n?>";
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zpconfig);
		setup::configMod();
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
	setup::configMod();
} else if (file_exists($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg')) {
	$zpconfig = "<?php\n" . file_get_contents($oldconfig) . "\n?>";
	file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zpconfig);
	@unlink(SERVERPATH . '/' . DATA_FOLDER . '/zenphoto.cfg');
	$newconfig = false;
	setup::configMod();
} else {
	$newconfig = true;
	@copy(dirname(dirname(__FILE__)) . '/file-templates/zenphoto_cfg.txt', SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
}

$zptime = filemtime($oldconfig = SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
@copy(dirname(dirname(__FILE__)) . '/file-templates/dataaccess', $_zp_setup_serverpath . '/' . DATA_FOLDER . '/.htaccess');
@copy(dirname(dirname(__FILE__)) . '/file-templates/dataaccess', $_zp_setup_serverpath . '/' . BACKUPFOLDER . '/.htaccess'); 
@chmod($_zp_setup_serverpath . '/' . DATA_FOLDER . '/.htaccess', 0444);

if (session_id() == '') {
	session_start();
}
if (isset($_GET['mod_rewrite'])) {
	$mod = '&mod_rewrite=' . $_GET['mod_rewrite'];
} else {
	$mod = '';
}

$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);

$updatezp_config = false;

if (strpos($zp_cfg, "\$conf['special_pages']") === false) {
	$template = file_get_contents(dirname(dirname(__FILE__)) . '/file-templates/zenphoto_cfg.txt');
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
	setup::XSRFDefender();
	setup::Log(gettext("db POST handling"));
	$updatezp_config = true;
	if (isset($_POST['db_software'])) {
		$zp_cfg = updateConfigItem('db_software', addslashes(setup::sanitize($_POST['db_software'])), $zp_cfg);
	}
	if (isset($_POST['db_user'])) {
		$zp_cfg = updateConfigItem('mysql_user', addslashes(setup::sanitize($_POST['db_user'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_pass'])) {
		$zp_cfg = updateConfigItem('mysql_pass', addslashes(setup::sanitize($_POST['db_pass'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_host'])) {
		$zp_cfg = updateConfigItem('mysql_host', addslashes(setup::sanitize($_POST['db_host'], 0)), $zp_cfg);
	}
	if (isset($_POST['db_database'])) {
		$zp_cfg = updateConfigItem('mysql_database', addslashes(trim(setup::sanitize($_POST['db_database']))), $zp_cfg);
	}
	if (isset($_POST['db_prefix'])) {
		$zp_cfg = updateConfigItem('mysql_prefix', str_replace(array('.', '/', '\\', '`', '"', "'"), '_', addslashes(trim(setup::sanitize($_POST['db_prefix'])))), $zp_cfg);
	}
	if (isset($_POST['db_port'])) {
		$zp_cfg = updateConfigItem('mysql_port', addslashes(trim(intval($_POST['db_port']))), $zp_cfg);
	} else {
		$zp_cfg = updateConfigItem('mysql_port', "3306", $zp_cfg);
	}
	if (isset($_POST['db_socket'])) {
		$zp_cfg = updateConfigItem('mysql_socket', addslashes(trim(intval($_POST['db_socket']))), $zp_cfg);
	} else {
		$zp_cfg = updateConfigItem('mysql_socket', '', $zp_cfg);
	}
}

define('ACK_DISPLAY_ERRORS', 2);

if (isset($_GET['security_ack'])) {
	setup::XSRFDefender();
	$zp_cfg = updateConfigItem('security_ack', (isset($conf['security_ack']) ? $cache['keyword'] : NULL) | (int) $_GET['security_ack'], $zp_cfg, false);
	$updatezp_config = true;
}

$_zp_setup_permission_names = array(
				0444 => gettext('readonly'),
				0644 => gettext('strict'),
				0664 => gettext('relaxed'),
				0666 => gettext('loose')
);
$permissions = array_keys($_zp_setup_permission_names);
if ($updatechmod = isset($_REQUEST['chmod_permissions'])) {
	setup::XSRFDefender();
	$selected = round($_REQUEST['chmod_permissions']);
	if ($selected >= 0 && $selected < count($permissions)) {
		$_zp_setup_chmod = $permissions[$selected];
	} else {
		$updatechmod = false;
	}
}
if ($updatechmod || $newconfig) {
	if ($updatechmod || isset($_zp_conf_vars['CHMOD'])) {
		$chmodval = "\$conf['CHMOD']";
	} else {
		$chmodval = sprintf('0%o', $_zp_setup_chmod);
	}
	if ($updatechmod) {
		$zp_cfg = updateConfigItem('CHMOD', sprintf('0%o', $_zp_setup_chmod), $zp_cfg, false);
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
	setup::XSRFDefender();
	$fileset = $_REQUEST['FILESYSTEM_CHARSET'];
	$zp_cfg = updateConfigItem('FILESYSTEM_CHARSET', $fileset, $zp_cfg);
	$updatezp_config = true;
}
if ($updatezp_config) {
	setup::updateConfigfile($zp_cfg);
	$updatezp_config = false;
}

$curdir = getcwd();
chdir(dirname(dirname(__FILE__)));
// Important. when adding new database support this switch may need to be extended,
$engines = array();

$preferences = array('mysqli' => 1, 'pdo_mysql' => 2);
$cur = 999999;
$preferred = NULL;
foreach (setup::glob('classes/class-db*.php') as $key => $engineMC) {
	$engineMC = substr($engineMC, 16, -4);
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

ksort($engines);
chdir($curdir);

if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	unset($_zp_conf_vars);
	require(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	if (isset($_zp_conf_vars) && !isset($conf) && isset($_zp_conf_vars['special_pages'])) {
		if (!isset($_zp_conf_vars['special_pages']['gallery'])) {
			$updatezp_config = true;
		}
		if (isset($_zp_conf_vars['db_software'])) {
			$confDB = strtolower($_zp_conf_vars['db_software']);
			if (empty($_POST) && empty($_GET) && ($confDB === 'mysql' || $preferred != 'mysqli')) {
				$confDB = NULL;
			}
			if (extension_loaded($confDB) && file_exists(dirname(dirname(__FILE__)) . '/classes/class-db' . strtolower($confDB) . '.php')) {
				$selected_database = $_zp_conf_vars['db_software'];
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
		if (!isset($_zp_conf_vars['mysql_port'])) { 
			$_zp_conf_vars['mysql_port'] = 3306;
			$zp_cfg = updateConfigItem('mysql_port', 3306, $zp_cfg);
			$updatezp_config = true;
		}
		if (!isset($_zp_conf_vars['mysql_socket'])) { 
			$_zp_conf_vars['mysql_socket'] = '';
			$zp_cfg = updateConfigItem('mysql_socket', '', $zp_cfg);
			$updatezp_config = true;
		}

		require_once(dirname(dirname(__FILE__)) . '/classes/class-dbbase.php'); // empty base db class
		if ($selected_database) {
			require_once(dirname(dirname(__FILE__)) . '/classes/class-db' . strtolower($selected_database) . '.php'); // real db handler
			define('DATABASE_SOFTWARE', $selected_database);
			define('DATABASE_MIN_VERSION', '5.5.3');
			define('DATABASE_DESIRED_VERSION', '5.7.0');
			define('DATABASE_MARIADB_MIN_VERSION', '5.5.0'); // more or less MySQL 5.5
			define('DATABASE_MARIADB_DESIRED_VERSION', '10.1.0'); // more or less MySQL 5.7
			$_zp_dbclass = 'db' . strtolower($_zp_conf_vars['db_software']); // global that defines the db class name to use
		} else {
			define('DATABASE_SOFTWARE', 'Database setup');
			define('DATABASE_MIN_VERSION', '0.0.0');
			define('DATABASE_DESIRED_VERSION', '0.0.0');
			define('DATABASE_MARIADB_MIN_VERSION', '0.0.0'); 
			define('DATABASE_MARIADB_DESIRED_VERSION', '0.0.0'); 
			$_zp_dbclass = 'dbBase';
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
}

if ($updatezp_config) {
	setup::updateConfigfile($zp_cfg);
}

$result = true;
$environ = false;
$DBcreated = false;
$oktocreate = false;
$connection = false;
$connectDBErr = '';

if ($selected_database) {
	$_zp_db = new $_zp_dbclass($_zp_conf_vars, false);
	$connectDBErr = '';
	$connection = $_zp_db->connection;
	//$connection = db_connect($_zp_conf_vars, false);
	if ($connection) { // got the database handler and the database itself connected
		$result = $_zp_db->query("SELECT `id` FROM " . $_zp_conf_vars['mysql_prefix'] . 'options' . " LIMIT 1", false);
		if ($result) {
			if ($_zp_db->getNumRows($result) > 0) {
				$upgrade = gettext("upgrade");
				// apply some critical updates to the database for migration issues
				$_zp_db->query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `valid` int(1) default 1', false);
				$_zp_db->query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' CHANGE `password` `pass` varchar(64)', false);
				$_zp_db->query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `loggedin` datetime', false);
				$_zp_db->query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `lastloggedin` datetime', false);
				$_zp_db->query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'] . 'administrators' . ' ADD COLUMN `challenge_phrase` TEXT', false);
			}
		}
		$environ = true;
		require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
	} else {
		$connectDBErr = $_zp_db->getError();
		$connection = false;
	}
} else {
	$_zp_db = new dbBase($_zp_conf_vars, false);
}

if (defined('CHMOD_VALUE')) {
	$_zp_setup_chmod = CHMOD_VALUE & 0666;
}

if (function_exists('setOption')) {
	setOptionDefault('zp_plugin_security-logger', 9 | CLASS_PLUGIN);
} else { // setup a primitive environment
	$environ = false;
	require_once(dirname(__FILE__) . '/setup-primitive.php');
	require_once(dirname(dirname(__FILE__)) . '/functions/functions-filter.php');
	require_once(dirname(dirname(__FILE__)) . '/functions/functions-i18n.php');
}

if ($newconfig || isset($_GET['copyhtaccess'])) {
	if ($newconfig && !file_exists($_zp_setup_serverpath . '/.htaccess') || setup::userAuthorized()) {
		@chmod($_zp_setup_serverpath . '/.htaccess', 0777);
		$ht = @file_get_contents(SERVERPATH . '/.htaccess');
		$newht = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/file-templates/htaccess');
		if (setup::siteClosed($ht)) {
			$newht = setup::closeSite($newht);
		}
		file_put_contents($_zp_setup_serverpath . '/.htaccess', $newht);
		@chmod($_zp_setup_serverpath . '/.htaccess', 0444);
	}
}

if ($setup_checked) {
	if (!isset($_GET['protect_files'])) {
		setup::log(gettext("Completed system check"), true);
		if (isset($_COOKIE['zpcms_setup_testcookie'])) {
			$setup_cookie = $_COOKIE['zpcms_setup_testcookie'];
		} else {
			$setup_cookie = '';
		}
		if ($setup_cookie == ZENPHOTO_VERSION) {
			setup::log(gettext('Setup cookie test successful'));
			setcookie('zpcms_setup_testcookie', '', time() - 368000, '/');
		} else {
			setup::log(gettext('Setup cookie test unsuccessful'), true);
		}
	}
} else {
	if (isset($_POST['db'])) {
		setup::log(gettext("Post of Database credentials"), true);
	} else {
		$me = dirname(dirname(dirname(str_replace('\\', '/', __FILE__))));
		$mine = SERVERPATH;
		if (setup::isWin() || setup::isMac()) { // case insensitive file systems
			$me = strtolower($me);
			$mine = strtolower($mine);
		}
		if ($mine == $me) {
			$clone = '';
		} else {
			$clone = ' ' . gettext('clone');
		}
		setup::log(sprintf(gettext('Zenphoto Setup v%1$s %2$s: %3$s'), ZENPHOTO_VERSION, $clone, date('r')), true, true); // initialize the log file
	}
	if ($environ) {
		setup::log(gettext("Full environment"));
	} else {
		setup::log(gettext("Primitive environment"));
		if ($connectDBErr) {
			setup::log(sprintf(gettext("Query error: %s"), $connectDBErr), true);
		}
	}
	setcookie('zpcms_setup_testcookie', ZENPHOTO_VERSION, time() + 3600, '/');
}

if (!isset($_zp_setupCurrentLocale_result) || empty($_zp_setupCurrentLocale_result)) {
	if (DEBUG_LOCALE)
		debugLog('Setup checking locale');
	$_zp_setupCurrentLocale_result = setMainDomain();
	if (DEBUG_LOCALE)
		debugLog('$_zp_setupCurrentLocale_result = ' . $_zp_setupCurrentLocale_result);
}

$taskDisplay = array(
		'create' => gettext("create"),
		'update' => gettext("update")
);

$versioncheck = setup::checkPreviousVersion();
$check = $versioncheck['check'];
$release = $versioncheck['release_text'];
$release_message = $versioncheck['message_text'];
$upgrade = $versioncheck['upgrade_text'];
?>

<!DOCTYPE html>

<html>

	<head>

		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php printf('Zenphoto %s', $upgrade ? $upgrade : gettext('install')); ?></title>
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin.css" type="text/css" />

		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.min.js"></script>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery-migrate.min.js" ></script>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zp_general.js" ></script>
		<script>
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

			<h1><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png" title="<?php echo gettext('Zenphoto Setup'); ?>" alt="<?php echo gettext('Zenphoto Setup'); ?>" align="bottom" />
				<span><?php echo $upgrade ? $upgrade : gettext("Setup"); ?></span>
			</h1>

			<div id="content">
				<?php
				$blindInstall = $_zp_setup_warn = false;

				if ($connection && !isset($_zp_options)) {
					$sql = "SELECT `name`, `value` FROM " . $_zp_db->prefix('options');
					$optionlist = $_zp_db->queryFullArray($sql, false);
					if ($optionlist) {
						$_zp_options = array();
						foreach ($optionlist as $option) {
							$_zp_options[strtolower($option['name'])] = $option['value'];
						}
					}
				}

				if (!$setup_checked && (($upgrade && $_zp_setup_autorun) || setup::UserAuthorized())) {
					if ($blindInstall = ($upgrade && $_zp_setup_autorun) && !setup::UserAuthorized()) {
						ob_start(); //	hide output for auto-upgrade
					}
					?>
					<p>
						<?php printf(gettext("Welcome to Zenphoto! This page will set up Zenphoto %s on your web server."), ZENPHOTO_VERSION); ?>
					</p>
					<?php maintenanceMode::setState('closed', $setupMutex); ?>
					<p class="warning"><?php echo maintenanceMode::getStateNote('closed'); ?></p>
					<h2><?php echo gettext("Systems Check:"); ?></h2>
					<?php if($upgrade) { ?>
						<p class="warning"><?php echo gettext('Backup your database before proceeding!'); ?></p>
					<?php
					}
					/*****************************************************************************
					 *                                                                           *
					 *                             SYSTEMS CHECK                                 *
					 *                                                                           *
					 *****************************************************************************/

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
							setup::checkmark($check, $release, $release, $release_message);
						} else {
							?>
							<ul>
								<?php
								$prevRel = false;
								setup::checkmark(1, sprintf(gettext('Installing Zenphoto v%s'), ZENPHOTO_VERSION), '', '');
							}
							chdir(dirname(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE));
							$test = setup::glob('*.log');
							$test[] = basename(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
							$p = true;
							foreach ($test as $file) {
								$permission = fileperms(dirname(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . '/' . $file) & 0777;
								if (!setup::checkPermissions($permission, 0600)) {
									$p = -1;
									break;
								}
							}
							setup::checkMark($p, sprintf(gettext('<em>%s</em> security'), DATA_FOLDER), sprintf(gettext('<em>%s</em> security [is compromised]'), DATA_FOLDER), sprintf(gettext('Zenphoto suggests you make the sensitive files in the %1$s folder accessable by <em>owner</em> only (permissions = 0600). The file permissions for <em>%2$s</em> are %3$04o which may allow unauthorized access.'), DATA_FOLDER, $file, $permission));
						
							if(setup::secureServer()) {
								$sslconection = 1;
							} else {
								$sslconection = -1;
							}
							setup::checkMark($sslconection, gettext('<em>SSL</em> connection'), gettext('<em>SSL</em> connection [is not enabled]'), gettext("It is strongely recommended to use a secure https connection on your site."));
							
							if(setup::checkServerSoftware()) {
								setup::checkMark(true, $_SERVER['SERVER_SOFTWARE'], '', '');
							} else {
								setup::checkMark(-1, '', $_SERVER['SERVER_SOFTWARE'], gettext('Server seems not to be <em>Apache</em>, <em>Nginx</em> or <em>compatible</em>. Zenphoto may not work correctly.'));
							} 
							
							$err = setup::versionCheck(PHP_MIN_VERSION, PHP_DESIRED_VERSION, PHP_VERSION);
							$good = setup::checkMark($err, sprintf(gettext("PHP version %s"), PHP_VERSION), "", sprintf(gettext('PHP Version %1$s or greater is required. Version %2$s or greater is strongly recommended. Use earlier versions at your own risk. Zenphoto is developed on PHP 8+ and in any case not tested below 7.4. There will be no fixes if you encounter any issues below 7.4. Please contact your webhost about a PHP upgrade on your server.'), PHP_MIN_VERSION, PHP_DESIRED_VERSION), false) && $good;
							
							if ($session && session_id()) {
								setup::checkmark(true, gettext('PHP <code>Sessions</code>.'), gettext('PHP <code>Sessions</code> [appear to not be working].'), '', true);
							} else {
								setup::checkmark(0, '', gettext('PHP <code>Sessions</code> [appear to not be working].'), gettext('PHP Sessions are required for Zenphoto administrative functions.'), true);
							}

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
												$blacklist[$key] = '<span style="color:red;">' . $func . '*</span>';
											}
										}
									}
									$issue--;
									$good = setup::checkMark($issue, '', gettext('<code>Suhosin</code> module [is enabled]'), sprintf(gettext('The following PHP functions are blocked: %s. Flagged functions are required by Zenphoto. Other functions in the list may be used by Zenphoto, possibly causing reduced functionality or Zenphoto failures.'), '<code>' . implode('</code>, <code>', $blacklist) . '</code>'), $abort) && $good;
								}
							}
		
							switch (strtolower(@ini_get('display_errors'))) {
								case 0:
								case 'off':
								case 'stderr':
								case '':
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
										$aux = ' ' . setup::acknowledge(ACK_DISPLAY_ERRORS);
									}
									break;
							}
							setup::checkmark($display, gettext('PHP <code>display_errors</code>'), sprintf(gettext('PHP <code>display_errors</code> [is enabled]'), $display), gettext('This setting may result in PHP error messages being displayed on WEB pages. These displays may contain sensitive information about your site.') . $aux, $display && !TEST_RELEASE);
						
							setup::checkMark($noxlate, gettext('PHP <code>gettext()</code> support'), gettext('PHP <code>gettext()</code> support [is not present]'), gettext("Localization of Zenphoto requires native PHP <code>gettext()</code> support"));			
							
							$good =	setup::checkmark(extension_loaded('curl') ? 1 : 0, gettext('PHP <code>cURL</code> support'), gettext('PHP <code>cURL</code> support [is not present]'), gettext('PHP <code>cURL</code> support is critical and required for some functionalty.')) && $good;		
							
							setup::checkmark(extension_loaded('tidy') ? 1 : -1, gettext('PHP <code>tidy</code> support'), gettext('PHP <code>tidy</code> support [is not present]'), gettext('<code>tidy</code> support is not critical but strongely recommended for properly truncating text containing HTML markup.'));
							setup::checkmark(extension_loaded('zip') ? 1 : -1, gettext('PHP <code>ZipArchive</code> support'), gettext('PHP <code>ZipArchive</code> support [is not present]'), gettext('<code>ZipArchive</code> support is not critical and only required if you intend to upload zip archives with supported file types to the gallery.'));			
							
							$good =	setup::checkmark(extension_loaded('json') ? 1 : 0, gettext('PHP <code>JSON</code> support'), gettext('PHP <code>JSON</code> support [is not present]'), gettext('<code>JSON</code> support is critical and required for some functionalty.')) && $good;		
							
							setup::checkmark(extension_loaded('exif') ? 1 : -1, gettext('PHP <code>exif</code> support'), gettext('PHP <code>exif</code> support [is not present]'), gettext('<code>exif</code> support is not critical but strongely recommended for properly handling exif data of images'));
							setup::checkmark(extension_loaded('bz2') ? 1 : -1, gettext('PHP <code>bz2</code> support'), gettext('PHP <code>bz2</code> support [is not present]'), gettext('<code>bz2</code> support is not critical but recommended for some optional bzcompression functionalty'));
							setup::checkmark(extension_loaded('fileinfo') ? 1 : -1, gettext('PHP <code>fileinfo</code> support'), gettext('PHP <code>fileinfo</code> support [is not present]'), gettext('<code>fileinfo</code> support is not critical but strongely recommended for file system functionality'));
							setup::checkmark(extension_loaded('intl') ? 1 : -1, gettext('PHP <code>intl</code> support'), gettext('PHP <code>intl</code> support [is not present]'), gettext('<code>intl</code> support is strongely recommended for using locale-aware functionality.'));
							setup::checkmark(extension_loaded('xml') ? 1 : -1, gettext('PHP <code>xml</code> support'), gettext('PHP <code>xml</code> support [is not present]'), gettext('<code>xml</code> support is not criticaly but strongely recommended for some functionality for parsing XML contents like RSS feeds.'));
							setup::checkmark(extension_loaded('dom') ? 1 : -1, gettext('PHP <code>dom</code> support'), gettext('PHP <code>dom</code> support [is not present]'), gettext('<code>dom</code> support is not criticaly but strongely recommended for some functionality processing and modifying HTML contents.'));
							setup::checkmark(extension_loaded('simplexml') ? 1 : -1, gettext('PHP <code>simplexml</code> support'), gettext('PHP <code>simplexml</code> support [is not present]'), gettext('<code>simplexml</code> support is not criticaly but strongely recommended for some functionality processing XML contents like RSS feeds.'));
							setup::checkmark(extension_loaded('reflection') ? 1 : -1, gettext('PHP <code>Reflection</code> support'), gettext('PHP <code>Reflection</code> support [is not present]'), gettext('<code>Reflection</code> support is not criticaly but strongely recommended for some internal functionality.'));
							setup::checkmark(extension_loaded('ctype') ? 1 : 0, gettext('PHP <code>Ctype</code> support'), gettext('PHP <code>Ctype</code> support [is not present]'), gettext('<code>Ctype</code> support is required for internal character type checking e.g. for shortening text content.'));
							setup::checkmark(extension_loaded('filter') ? 1 : 0, gettext('PHP <code>filter</code> support'), gettext('PHP <code>filter</code> support [is not present]'), gettext('<code>filter</code> support is required for filtering and clearing content.'));
							setup::checkmark(extension_loaded('session') ? 1 : 0, gettext('PHP <code>session</code> support'), gettext('PHP <code>session</code> support [is not present]'), gettext('<code>session</code> support is required session handling.'));

							if ($_zp_setupCurrentLocale_result === false) {
								setup::checkMark(-1, gettext('PHP <code>setlocale()</code>'), ' ' . gettext('PHP <code>setlocale()</code> failed'), gettext("Locale functionality is not implemented on your platform or the specified locale does not exist. Language translation may not work.") . '<br />' . gettext('See the <a  href="https://www.zenphoto.org/news/problems-with-languages">user guide</a> on zenphoto.org for details.'));
							}
							setup::primeMark(gettext('mb_strings'));
							if (extension_loaded('mbstring') && extension_loaded('iconv')) {
								@mb_internal_encoding('UTF-8');
								if (($charset = mb_internal_encoding()) == 'UTF-8') {
									$mb = 1;
								} else {
									$mb = -1;
								}
								$m2 = gettext('Setting <em>mbstring.internal_encoding</em> to <strong>UTF-8</strong> in your <em>php.ini</em> file is strongely recommended to insure accented and multi-byte characters function properly.');
								setup::checkMark($mb, gettext("PHP <code>mbstring</code> and <code>iconv</code> packages"), sprintf(gettext('PHP <code>mbstring</code> and <code>iconv</code> packages [Your internal character set is <strong>%s</strong>]'), $charset), $m2);
							} else {
								$test = $_zp_utf8->convert('test', 'ISO-8859-1', 'UTF-8');
								if (empty($test)) {
									$m2 = gettext("You need to install the <code>mbstring</code> and <code>iconv</code> packages");
									setup::checkMark(0, '', gettext("PHP <code>mbstring</code> and <code>iconv</code> packages [are not present]"), $m2);
								} else {
									$m2 = gettext("Strings generated internally by PHP may not display correctly. (e.g. dates)");
									setup::checkMark(-1, '', gettext("PHP <code>mbstring</code> and <code>iconv</code> packages [are not present]"), $m2);
								}
							}

							if ($environ) {
								/* Check for graphic library and image type support. */
								setup::primeMark(gettext('Graphics library'));
								if ($_zp_graphics->info['Library'] != 'none') {
									$graphics_lib = $_zp_graphics->graphicsLibInfo();
									if (array_key_exists('Library_desc', $graphics_lib)) {
										$library = $graphics_lib['Library_desc'];
									} else {
										$library = '';
									}
									$general_graphicsinfo = gettext('Graphics support:') . "<br>";
									foreach ($_zp_graphics->generalinfo as $key => $value) { // check all available and mark currently selected
										if ($key == $_zp_graphics->info['Library']) {
											$general_graphicsinfo .= $value . ' ' . gettext('[enabled]') . "<br>";
										} else {
											$general_graphicsinfo .= $value . ' ' . gettext('[available]') . "<br>";
										}
									}
									/*$good = */setup::checkMark(!empty($library), $general_graphicsinfo, gettext('Graphics support [is not installed]'), gettext('You need to install a graphics support library such as the <em>GD library</em> in your PHP')) && $good;
									if (!empty($library)) {
										$missing = array();
										if (!isset($_zp_graphics->info['JPG'])) {
											$missing[] = 'JPEG';
										}
										if (!(isset($_zp_graphics->info['GIF']))) {
											$missing[] = 'GIF';
										}
										if (!(isset($_zp_graphics->info['PNG']))) {
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
												//$good = false;
												$mandate = gettext("To correct this you need to install GD with appropriate image support in your PHP");
											}
											setup::checkMark($err, gettext("PHP graphics image support"), '', $imgmissing .
															"<br />" . gettext("The unsupported image types will not be viewable in your albums.") .
															"<br />" . $mandate);
										}
										if (!$_zp_graphics->imageCanRotate()) {
											setup::checkMark(-1, '', gettext('Graphics Library rotation support [is not present]'), gettext('The graphics support library does not provide support for image rotation.'));
										}
									}
								} else {
									$graphicsmsg = '';
									foreach ($_zp_graphics_optionhandlers as $handler) {
										$graphicsmsg .= $handler->canLoadMsg($handler);
									}
									setup::checkmark(0, '', gettext('Graphics support [configuration error]'), gettext('No Zenphoto image handling library was loaded. Be sure that your PHP has a graphics support.') . ' ' . trim($graphicsmsg));
								}
							}
							if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
								require( SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
								$cfg = true;
							} else {
								$cfg = false;
							}


							$good = setup::checkMark($cfg, sprintf(gettext('<em>%1$s</em> file'), CONFIGFILE), sprintf(gettext('<em>%1$s</em> file [does not exist]'), CONFIGFILE), sprintf(gettext('Setup was not able to create this file. You will need to copy the <code>%1$s/zenphoto_cfg.txt</code> file to <code>%2$s/%3$s</code> then edit it as indicated in the file’s comments.'), ZENFOLDER, DATA_FOLDER, CONFIGFILE)) && $good;
							if ($cfg) {
								setup::primeMark(gettext('File permissions'));
								$chmodselector = '<form action="#"><input type="hidden" name="xsrfToken" value="' . setup::getXSRFToken() . '" />' .
												'<p>' . sprintf(gettext('Set File permissions to %s.'), setup::permissionsSelector($_zp_setup_permission_names, $_zp_setup_chmod)) .
												'</p></form>';
								if (array_key_exists($_zp_setup_chmod | 4, $_zp_setup_permission_names)) {
									$value = sprintf(gettext('<em>%1$s</em> (<code>0%2$o</code>)'), $_zp_setup_permission_names[$_zp_setup_chmod | 4], $_zp_setup_chmod);
								} else {
									$value = sprintf(gettext('<em>unknown</em> (<code>%o</code>)'), $_zp_setup_chmod);
								}
								if ($_zp_setup_chmod > 0664) {
									if (isset($_zp_conf_vars['CHMOD'])) {
										$severity = -3;
									} else {
										$severity = -1;
									}
								} else {
									$severity = -2;
								}
								$msg = sprintf(gettext('File Permissions [are %s]'), $value);
								setup::checkMark($severity, $msg, $msg, '<p>' . gettext('If file permissions are not set to <em>strict</em> or tighter there could be a security risk. However, on some servers Zenphoto does not function correctly with tight file permissions. If Zenphoto has permission errors, run setup again and select a more relaxed permission.') . '</p>' .
												$chmodselector);

								if (setup::userAuthorized()) {
									if ($environ) {
										if (setup::isMac()) {
											setup::checkMark(-1, '', gettext('Your filesystem is Macintosh'), gettext('Zenphoto is unable to deal with Macintosh file names containing diacritical marks. You should avoid these.'), false);
											?>
											<input
												type="hidden" name="FILESYSTEM_CHARSET" value="UTF-8" />

											<?php
										} else {
											setup::primeMark(gettext('Character set'));
											$charset_defined = str_replace('-', '&#8209;', FILESYSTEM_CHARSET);
											$charset = LOCAL_CHARSET;
											if (empty($charset)) {
												$charset = 'UTF-8';
											}
											$test = '';
											if (($dir = opendir($_zp_setup_serverpath . '/' . DATA_FOLDER . '/')) !== false) {
												$testfiles = array();
												while (($file = readdir($dir)) !== false) {
													if (preg_match('/^charset[\._]t(.*)$/', $file, $matches)) {
														$test = stripSuffix($matches[1]);
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
															'<form action="#"><input type="hidden" name="xsrfToken" value="' . setup::getXSRFToken() . '" /><input type="hidden" name="charset_attempts" value="' . $tries . '" /><p>' .
															gettext('Change the filesystem character set define to %1$s') .
															'</p></form><br class="clearall" />';

											if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
												$selectedset = $_zp_conf_vars['FILESYSTEM_CHARSET'];
											} else {
												$selectedset = 'unknown';
											}
											$msg = '';
											if ($test) {
												//	fount the test file
												if ((filesystemToInternal(trim($test)) == 'ést')) {
													//	and the active character set define worked
													$notice = 1;
													$msg = sprintf(gettext('The Zenphoto filesystem character define is %1$s [confirmed]'), $charset_defined);
													$msg1 = '';
												} else {
													if ($selectedset == 'unknown') {
														$notice = 1;
														$msg = gettext('The Zenphoto filesystem character define is ISO-8859-1 [assumed]');
														$msg1 = '';
													} else {
														//	active character set is not correct
														$notice = 0;
														$msg1 = sprintf(gettext('The Zenphoto filesystem character define is %1$s [which seems wrong]'), $charset_defined);
													}
												}
											} else {
												//	no test file
												$msg1 = sprintf(gettext('The Zenphoto filesystem character define is %1$s [no test performed]'), $charset_defined);
												$msg2 = '<p>' . sprintf(gettext('Setup did not perform a test of the filesystem character set. You can cause setup to test for a proper definition by creating a file in your <code>%1$s</code> folder named <strong><code>%2$s</code></strong> and re-running setup.'), DATA_FOLDER, 'charset_tést') . '</p>' . $msg2;
												if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
													//	but we have a define value
													$notice = -3;
												} else {
													//	no defined value, who knows....
													$notice = -1;
												}
											}
											setup::checkMark($notice, $msg, $msg1, sprintf($msg2, setup::charsetSelector(FILESYSTEM_CHARSET)));
											// UTF-8 URI
											if (($notice != -1) && @copy(SERVERPATH . '/' . ZENFOLDER . '/images/pass.png', $_zp_setup_serverpath . '/' . DATA_FOLDER . '/' . internalToFilesystem('tést.jpg'))) {
												$test_image = WEBPATH . '/' . DATA_FOLDER . '/' . urlencode('tést.jpg');
												$req_iso = gettext('Image URIs appear require the <em>filesystem</em> character set.');
												$req_UTF8 = gettext('Image URIs appear to require the UTF-8 character set.');
												?>
												<script>
													function uri(enable) {
														var text;
														if (enable) {
															text = 'true';
														} else {
															text = 'false';
														}
														$('#setUTF8URI').val(enable);
														$('#UTF8_uri_warn').hide();
													}
													var loadSucceeded = true;
													$(document).ready(function() {
														var image = new Image();
														image.onload = function() {
						<?php
						if (!UTF8_IMAGE_URI) {
							?>
																$('#UTF8_uri_warn').html('<?php echo addslashes(gettext('You should enable the URL option <em>UTF8 image URIs</em>.')); ?>' + ' <?php echo addslashes(gettext('<a href="javascript:uri(true)">Please do</a>')); ?>');
																$('#UTF8_uri_warn').show();
							<?php
							if ($_zp_setup_autorun) {
								?>
																	uri(true);
								<?php
							}
						}
						?>
														};
														image.onerror = function() {
															$('#UTF8_uri_text').html('<?php echo addslashes($req_iso); ?>');
						<?php
						if (UTF8_IMAGE_URI) {
							?>
																$('#UTF8_uri').attr('class', 'warn');
																$('#UTF8_uri_warn').html('<?php echo addslashes(gettext('You should disable the URL option <em>UTF8 image URIs</em>.')); ?>' + ' <?php echo gettext('<a href="javascript:uri(false)">Please do</a>'); ?>');
																$('#UTF8_uri_warn').show();
							<?php
							if ($_zp_setup_autorun) {
								?>
																	uri(false);
								<?php
							}
						}
						?>
														};
														image.src = '<?php echo $test_image; ?>';


													});
												</script>
												<li id="UTF8_uri" class="pass"><span id="UTF8_uri_text">
														<?php echo $req_UTF8; ?> </span>
													<div id="UTF8_uri_warn" class="warning" style="display: none">
														<h1>

															<?php echo gettext('Warning!'); ?></h1>
														<span id="UTR8_uri_warn"></span>
													</div>
												</li>

												<?php
											}
										}
									}
								}
							}
							setup::primeMark(gettext('Database'));
							foreach ($engines as $engine) {
								$handler = $engine['engine'];
								if ($handler == $confDB && $engine['enabled']) {
									$good = setup::checkMark(1, sprintf(gettext('PHP <code>%s</code> support for configured Database'), $handler), '', '') && $good;
								} else {
									if ($engine['enabled']) {
										if (isset($enabled['experimental'])) {
											?>
											<li class="note_warn"><?php echo sprintf(gettext('<code>%1$s</code> support (experimental)'), $handler); ?>
												<p class="warning" id="<?php echo $handler; ?>">
													 <?php echo $enabled['experimental'] ?>
												</p>
											</li>
											
											<?php
										} else {
											setup::log(gettext('Pass: ') . sprintf(gettext('PHP <code>%s</code> support'), $handler), true, false); 
										}
									} else {
										?>
										<li class="note_exception"><?php echo sprintf(gettext('PHP <code>%s</code> support [is not installed]'), $handler); ?>
										</li>
										<?php
									}
								}
							}
							$connection = $_zp_db->connection;
							if ($connection) {
								if (empty($_zp_conf_vars['mysql_database'])) {
									$connection = false;
									$connectDBErr = gettext('No database selected');
								}
							} else {
								$connectDBErr = $_zp_db->getError();
							}
							if ($_zp_db->connection) { // connected to DB software
								$dbsoftware = $_zp_db->getSoftware();
								$dbapp = $dbsoftware['application'];
								$dbversion = $dbsoftware['version'];
								$required = $dbsoftware['required'];
								$desired = $dbsoftware['desired'];
								$required_mariadb = $dbsoftware['required_mariadb'];
								$desired_mariadb = $dbsoftware['desired_mariadb'];
								if($_zp_db->isMariaDB()) {
									$sqlv = setup::versionCheck($required_mariadb, $desired_mariadb, $dbversion);
								} else {
									$sqlv = setup::versionCheck($required, $desired, $dbversion);
								}
								$good = setup::checkMark($sqlv, sprintf(gettext('%1$s version %2$s'), $dbapp, $dbversion), "", sprintf(gettext('%1$s Version %2$s or greater is required. Version %3$s or greater is preferred. Use a lower version at your own risk.'), $dbapp, $required, $desired), false) && $good;
								
							}
							setup::primeMark(gettext('Database connection'));

							if ($cfg) {
								if ($adminstuff = !extension_loaded(strtolower($selected_database)) || !$connection) {
									if (is_writable(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
										$good = false;
										setup::checkMark(false, '', gettext("Database credentials in configuration file"), sprintf(gettext('<em>%1$s</em> reported: %2$s'), DATABASE_SOFTWARE, $connectDBErr));
										// input form for the information
										include(dirname(__FILE__) . '/setup-sqlform.php');
									} else {
										if ($connectDBErr) {
											$msg = $connectDBErr;
										} else {
											$msg = gettext("You have not correctly set your <strong>Database</strong> <code>user</code>, <code>password</code>, etc. in your configuration file and <strong>setup</strong> is not able to write to the file.");
										}
										$good = setup::checkMark(!$adminstuff, gettext("Database setup in configuration file"), '', $msg) && $good;
									}
								} else {
									$good = setup::checkMark((bool) $connection, sprintf(gettext('Connect to %s'), DATABASE_SOFTWARE), gettext("Connect to Database [<code>CONNECT</code> query failed]"), $connectDBErr) && $good;
								}
							}

								if ($environ && $connection) {
									$oldmode = $_zp_db->getSQLmode();
									$result = $_zp_db->setSQLmode();
									$msg = gettext('You may need to set <code>SQL mode</code> <em>empty</em> in your Database configuration.');
									if ($result) {
										$mode = $_zp_db->getSQLmode();
										if ($mode === false) {
											setup::checkMark(-1, '', sprintf(gettext('<code>SQL mode</code> [query failed]'), $oldmode), $msg);
										} else {
											if ($oldmode != $mode) {
												setup::checkMark(-1, sprintf(gettext('<code>SQL mode</code> [<em>%s</em> overridden]'), $oldmode), '', gettext('Consider setting it <em>empty</em> in your Database configuration.'));
											} else {
												if (!empty($mode)) {
													$err = -1;
												} else {
													$err = 1;
												}
												setup::checkMark($err, gettext('<code>SQL mode</code>'), sprintf(gettext('<code>SQL mode</code> [is set to <em>%s</em>]'), $mode), gettext('Consider setting it <em>empty</em> if you get Database errors.'));
											}
										}
									} else {
										setup::checkMark(-1, '', gettext('<code>SQL mode</code> [SET SESSION failed]'), $msg);
									}

									$dbn = "`" . $_zp_conf_vars['mysql_database'] . "`.*";
									$db_results = $_zp_db->getPermissions();

									$access = -1;
									$rightsfound = 'unknown';
									$rightsneeded = array(
											gettext('Select')	 => 'SELECT', 
											gettext('Create')	 => 'CREATE', 
											gettext('Drop')		 => 'DROP', 
											gettext('Insert')	 => 'INSERT',
											gettext('Update')	 => 'UPDATE', 
											gettext('Alter')	 => 'ALTER', 
											gettext('Delete')	 => 'DELETE', 
											gettext('Index')	 => 'INDEX');
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
									setup::checkMark($access, sprintf(gettext('Database <code>access rights</code> for <em>%s</em>'), $_zp_conf_vars['mysql_database']), sprintf(gettext('Database <code>access rights</code> for <em>%1$s</em> [%2$s]'), $_zp_conf_vars['mysql_database'], $rightsfound), sprintf(gettext("Your Database user must have %s rights."), $neededlist) . $report);

									$tables = $_zp_db->getTables();
									$tableslist = '';
									if ($tables) {
										$check = 1;
										foreach($tables as $table) {
											$tableslist .= "<code>" . $table . "</code>, ";
										}
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
									setup::checkMark($check, $msg, gettext("<em>SHOW TABLES</em> [Failed]"), sprintf(gettext("The database did not return a list of the database tables for <code>%s</code>."), $_zp_conf_vars['mysql_database']) .
													"<br />" . gettext("<strong>Setup</strong> will attempt to create all tables. This will not overwrite any existing tables."));
									if (isset($_zp_conf_vars['UTF-8']) && $_zp_conf_vars['UTF-8']) {
										$dbtext = gettext('UTF8MB4 collation support');
										$dbtext2 = gettext('UTF8MB4 collation support [is not available|');
										$dbmsg = gettext('You should update your database to MySQL 5.5.3+ or better 5.6+ / MariaDB 5.5+ for full unicode support.');
										if ($_zp_db->hasUtf8mb4Support('utf8mb4_520') || $_zp_db->hasUtf8mb4Support('utf8mb4')) {
											$utf8mb4check = true;
										} else {
											$utf8mb4check = -1;
										}
										setup::checkMark($utf8mb4check, $dbtext, $dbtext2, $dbmsg, false);

										if ($tables) {
											$utf8_any_tables = $utf8_tables = $utf8mb4_tables = $non_utf8mb4_tables = $non_utf8_tables = array();
											foreach($tables as $table) {
												if ($_zp_db->isUTF8Table($table, 'any')) {
													$utf8_any_tables[] = $table; // covers utf8/ut8mb4 mixed tables
													if ($_zp_db->isUTF8Table($table, 'utf8mb4')) {
														$utf8mb4_tables[] = $table;
													} 
												} else {
													$non_utf8_tables[] = $table;
												}
											}
												//gettext('Database <code>table and/or field collations</code>
											$db_collations_msg = gettext('Database table and/or its field collations');
											if ($non_utf8_tables) {
												$non_utf8_htmllist = setup::getFilelist($non_utf8_tables);
												$db_collations_msg2 = $db_collations_msg . gettext(' [not using any utf8 collations]');
												$db_collations_details  = sprintf(gettext('The following tables are not or not completely UTF-8: %s'), $non_utf8_htmllist);
												$db_collations_details .=  ' ' . gettext('You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code> or better <code>utf8mb4_unicode_ci</code> respectively <code>utf8mb4_unicode_520_ci</code>');
												setup::checkmark(-1, $db_collations_msg, $db_collations_msg2, $db_collations_details);
											}
											if ($utf8_any_tables) {
												if (count($utf8_any_tables) > count($utf8mb4_tables)) {
													$non_utf8mb4_tables = array_diff($utf8_any_tables, $utf8mb4_tables);
													$non_utf8mb4_htmllist = setup::getFilelist($non_utf8mb4_tables);
													$db_collations_msg2 = $db_collations_msg . gettext(' [not completely using utf8mb4_* collations]');
													$db_collations_details = sprintf(gettext('The following tables use UTF-8 but not or not completely full UTF-8 (utf8mb4): %s  Since they are UTF-8 Zenphoto will attempt to convert the table and field collations to <code>utf8mb4_unicode_ci</code> respectively <code>utf8mb4_unicode_520_ci</code>'), $non_utf8mb4_htmllist);
													setup::checkmark(-2, $db_collations_msg, $db_collations_msg2, $db_collations_details); 
												} 
											}
										}
									} else {
										setup::checkmark(-1, '', gettext('Database <code>$conf["UTF-8"]</code> [is not set <em>true</em>]'), gettext('You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code> or better <code>utf8mb4_unicode_ci</code> respectively <code>utf8mb4_unicode_520_ci</code> and setting this <em>true</em>. Zenphoto works best with pure UTF-8 encodings.'));
									}
								}
							if(!$good) {
								setup::printFooter();
								exit();
							}
							setup::primeMark(gettext('Zenphoto files'));
							@set_time_limit(180);
							$lcFilesystem = file_exists(strtoupper(__FILE__));
							$base = $_zp_setup_serverpath . '/';
							setup::getResidentZPFiles(SERVERPATH . '/' . ZENFOLDER, $lcFilesystem);
							if ($lcFilesystem) {
								$res = array_search(strtolower($base . ZENFOLDER . '/Zenphoto.package'), $_zp_resident_files);
								$base = strtolower($base);
							} else {
								$res = array_search($base . ZENFOLDER . '/Zenphoto.package', $_zp_resident_files);
							}
							unset($_zp_resident_files[$res]);
							$cum_mean = filemtime(SERVERPATH . '/' . ZENFOLDER . '/Zenphoto.package');
							$hours = 3600;
							$lowset = $cum_mean - $hours;
							$highset = $cum_mean + $hours;

							$package_file_count = false;
							$package = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/Zenphoto.package');
							if ($lcFilesystem) { // case insensitive file systems
								$package = strtolower($package);
							}
							if (!empty($package)) {
								$installed_files = explode("\n", trim($package));
								$count = array_pop($installed_files);
								$package_file_count = is_numeric($count) && ($count > 0) && ($count == count($installed_files));
							}
							if (!$package_file_count) {
								setup::checkMark(-1, '', gettext("Zenphoto package [missing]"), gettext('The file <code>Zenphoto.package</code> is either missing, not readable, or defective. Your installation may be corrupt!'));
								$installed_files = array();
							}
							$folders = array();
							if ($updatechmod) {
								$permissions = 1;
								setup::log(sprintf(gettext('Setting permissions (0%o) for Zenphoto package.'), $_zp_setup_chmod), true);
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
											@chmod($component, $_zp_setup_chmod | 0311);
											clearstatcache();
											$perms = fileperms($component) & 0777;
											if ($permissions == 1 && !setup::checkPermissions($perms, $_zp_setup_chmod | 0311)) {
												if (setup::checkPermissions($perms & 0755, 0755) || TEST_RELEASE) { // could not set them, but they will work.
													$permissions = -1;
												} else {
													$permissions = 0;
												}
											}
										}
										$folders[$component] = $component;
										unset($installed_files[$key]);
										if (dirname($value) == THEMEFOLDER) {
											setup::getResidentZPFiles($base . $value, $lcFilesystem);
										}
									} else {
										if ($updatechmod) {
											@chmod($component, $_zp_setup_chmod);
											clearstatcache();
											$perms = fileperms($component) & 0777;
											if ($permissions == 1 && !setup::checkPermissions($perms, $_zp_setup_chmod)) {
												if (setup::checkPermissions($perms & 0644, 0644) || TEST_RELEASE) { // could not set them, but they will work.
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
									if (!setup::checkPermissions(fileperms($folder) & 0777, 0755)) { // need to set them?.
										@chmod($folder, $_zp_setup_chmod | 0311);
										clearstatcache();
										$perms = fileperms($folder) & 0777;
										if ($permissions == 1 && !setup::checkPermissions($perms, $_zp_setup_chmod | 0311)) {
											if (setup::checkPermissions($perms & 0755, 0755) || TEST_RELEASE) { // could not set them, but they will work.
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
										$plugin_subfolders[] = implode('/', $folders);
										unset($installed_files[$key]); // this will be taken care of later
										break;
									case STATIC_CACHE_FOLDER:
										$Cache_html_subfolders[] = implode('/', $folders);
										unset($installed_files[$key]);
										break;
								}
							}
							$filelist = array();
							foreach ($installed_files as $extra) {
								$filelist[] = filesystemToInternal(str_replace($base, '', $extra));
							}
							$htmllist_missing = setup::getFilelist($filelist);
							if (hasPrimaryScripts() && count($installed_files) > 0) {
								if (defined('TEST_RELEASE') && TEST_RELEASE) {
									$msg1 = gettext("Zenphoto core files [This is a <em>debug</em> build. Some files are missing or seem wrong]");
								} else {
									$msg1 = gettext("Zenphoto core files [Some files are missing or seem wrong]");
								}
								$msg2 = gettext('Perhaps there was a problem with the upload. This may not be critical at all as perhaps just the file times may be off compared to other files. You should check the following files:') . $htmllist_missing;
								$mark = -1;
							} else {
								if (defined('TEST_RELEASE') && TEST_RELEASE) {
									$mark = -1;
									$msg1 = gettext("Zenphoto core files [This is a <em>debug</em> build]");
								} else {
									$msg1 = '';
									$mark = 1;
								}
								$msg2 = '';
							}
							setup::checkMark($mark, gettext("Zenphoto core files"), $msg1, $msg2, false);
							if (setup::userAuthorized() && $connection) {
								setup::primeMark(gettext('Installation files'));
								$systemlist = $filelist = array();
								$phi_ini_count = $svncount = 0;
								foreach ($_zp_resident_files as $extra) {
									if (getSuffix($extra) == 'xxx') {
										@unlink($extra); //	presumed to be protected copies of the setup files
									} else if (strpos($extra, 'php.ini') !== false) {
										$phi_ini_count++;
									} else if (defined('TEST_RELEASE') && TEST_RELEASE || (strpos($extra, '/.svn') === false)) {
										$systemlist[] = $extra;
										$filelist[] = $_zp_utf8->convert(str_replace($base, '', $extra), FILESYSTEM_CHARSET, 'UTF-8');
									} else {
										$svncount++;
									}
								}
								if ($svncount) {
									$filelist[] = sprintf(ngettext('.svn [%s instance]', '.svn [%s instances]', $svncount), $svncount);
								}
								if ($phi_ini_count && TEST_RELEASE) {
									$filelist[] = sprintf(ngettext('php.ini [%s instance]', 'php.ini [%s instances]', $phi_ini_count), $phi_ini_count);
								}
								if ($package_file_count) { //	no point in this if the package list was damaged!
									if (!empty($filelist)) {
										if (isset($_GET['delete_extra'])) {
											foreach ($systemlist as $key => $file) {
												if (!is_dir($file)) {
													@chmod($file, 0777);
													if (@unlink($file)) {
														unset($filelist[$key]);
														unset($systemlist[$key]);
													}
												}
											}
											rsort($systemlist);
											foreach ($systemlist as $key => $file) {
												@chmod($file, 0777);
												if (@rmdir($file)) {
													unset($filelist[$key]);
												}
											}
											
											if (!empty($filelist)) {
												$htmllist = setup::getFilelist($filelist);
												setup::checkmark(-1, '', gettext('Zenphoto core folders [Some unknown files were found]'), gettext('The following files could not be deleted.') . $htmllist);
											}
										} else {
											$htmllist = setup::getFilelist($filelist);
											setup::checkMark(-1, '', gettext('Zenphoto core folders [Some unknown files were found]'), gettext('You should remove the following files: ') . $htmllist .
															'<p class="buttons"><a href="?delete_extra' . ($_zp_setup_debug ? '&amp;debug' : '') . '">' . gettext("Delete extra files") . '</a></p><br class="clearall" /><br class="clearall" />');
										}
									}
									setup::checkMark($permissions, gettext("Zenphoto core file permissions"), gettext("Zenphoto core file permissions [not correct]"), gettext('Setup could not set the one or more components to the selected permissions level. You will have to set the permissions manually. See the <a href="https://www.zenphoto.org/news/permissions-for-zenphoto-files-and-folders">Troubleshooting guide</a> for details on Zenphoto permissions requirements.'));
								}
							}
							$msg = gettext("<em>.htaccess</em> file");
							$htfile = $_zp_setup_serverpath . '/.htaccess';
							$ht = trim(@file_get_contents($htfile));
							$htu = strtoupper($ht);
							$vr = "";
							$ch = 1;
							$j = 0;
							$err = '';
							$desc = '';
							if (empty($htu)) {
								$err = gettext("<em>.htaccess</em> file [is empty or does not exist]");
								$ch = -1;
								if (setup::getServerSoftware() == 'apache') {
									$desc = gettext('If you have the mod_rewrite module enabled an <em>.htaccess</em> file is required the root zenphoto folder to create cruft-free URLs.') .
													'<br /><br />' . gettext('You can ignore this warning if you do not intend to set the <code>mod_rewrite</code> option.');
									if (setup::userAuthorized()) {
										$desc .= ' ' . gettext('<p class="buttons"><a href="?copyhtaccess" >Make setup create the file</a></p><br style="clear:both" /><br />');
									}
								} else if (setup::getServerSoftware() == 'nginx') {
									$err = gettext("Server seems to be <em>nginx</em>");
									$mod = "&amp;mod_rewrite"; //	enable test to see if it works.
									$desc = gettext('If you wish to create cruft-free URLs, you will need to configuring <a href="https://www.zenphoto.org/news/nginx-rewrite-rules-tutorial"><em>URL rewriting for NGINX servers</em></a>.') . ' ' .
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

								$ch = !empty($vr) && ($vr == HTACCESS_VERSION);
								$d = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/') . '/';
								$d = str_replace(' ', '%20', $d); //	apache appears to trip out if there is a space in the rewrite base
								if (!$ch) { // wrong version
									$oht = trim(@file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/file-templates/oldhtaccess'));
									//fix the rewritebase
									$i = strpos($oht, 'RewriteBase /zenphoto');
									$oht = substr($oht, 0, $i) . "RewriteBase $d" . substr($oht, $i + 21);
									if ($closed = setup::siteClosed($ht)) {
										$oht = setup::closeSite($oht);
									}
									$oht = trim($oht);
									if ($oht == $ht) { // an unmodified .htaccess file, we can just replace it
										$ht = trim(file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/file-templates/htaccess'));
										$i = strpos($ht, 'RewriteBase /zenphoto');
										$ht = substr($ht, 0, $i) . "RewriteBase $d" . substr($ht, $i + 21);
										if ($closed) {
											$ht = setup::closeSite($ht);
										}
										$htu = strtoupper($ht);
										@chmod($htfile, 0777);
										@unlink($htfile);
										$ch = file_put_contents($htfile, trim($ht));
										@chmod($htfile, 0444);
									}
								}
								if (!$ch) {
									if (setup::getServerSoftware() != 'apache') {
										$desc = gettext("Server seems not to be Apache or Apache-compatible, <code>.htaccess</code> not required.");
										$ch = -1;
									} else {
										$desc = sprintf(gettext("The <em>.htaccess</em> file in your root folder is not the same version as the one distributed with this version of Zenphoto. If you have made changes to <em>.htaccess</em>, merge those changes with the <em>%s/htaccess</em> file to produce a new <em>.htaccess</em> file."), ZENFOLDER);
										if (setup::userAuthorized()) {
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
							$good = setup::checkMark($ch, $msg, $err, $desc, false) && $good;

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
								preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed\.htm|', $ht, $matches);
								$siteupdate = false;
								foreach ($matches[0] as $match) {
									if (strpos($match, 'index\.php$') !== false) {
										$match1 = str_replace('index\.php$', 'index\.php(.*)$', $match);
										$match1 = str_replace('closed.htm', 'closed.htm%1', $match1);
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
								$good = setup::checkMark($base, $b, $err, gettext("Setup was not able to write to the file change RewriteBase match the install folder.") .
																"<br />" . sprintf(gettext("Either make the file writeable or set <code>RewriteBase</code> in your <code>.htaccess</code> file to <code>%s</code>."), $d)) && $good;
								if ($siteupdate) {
									$good = setup::checkMark($save, gettext('Rewrite rules updated'), gettext('Rewrite rules updated [not updated]'), gettext("Setup was not able to write to the file change the rewrite rules for site upgrades.")) && $good;
								}
							}
							//robots.txt file
							$robots = file_get_contents(dirname(dirname(__FILE__)) . '/file-templates/example_robots.txt');
							if ($robots === false) {
								setup::checkmark(-1, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not find the  <em>example_robots.txt</em> file.'));
							} else {
								if (file_exists($_zp_setup_serverpath . '/robots.txt')) {
									setup::checkmark(-2, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup did not create a <em>robots.txt</em> file because one already exists. If you just moved your site you may need to review it.'));
								} else {
									$text = explode('# Place it in the root folder of your web pages.', $robots);
									$d = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
									if ($d == '/')
										$d = '';
									$robots = str_replace('/zenphoto', $d, trim($text[1]));
									$rslt = file_put_contents($_zp_setup_serverpath . '/robots.txt', $robots);
									if ($rslt === false) {
										$rslt = -1;
									} else {
										$rslt = 1;
									}
									setup::checkmark($rslt, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not create a <em>robots.txt</em> file.'));
								}
							}

							if (isset($_zp_conf_vars['external_album_folder']) && !is_null($_zp_conf_vars['external_album_folder'])) {
								setup::checkmark(-1, 'albums', gettext("albums [<code>\$conf['external_album_folder']</code> is deprecated]"), sprintf(gettext('You should update your configuration file to conform to the current %1$s example file.'), CONFIGFILE));
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
										$albumfolder = str_replace('\\', '/', $_zp_setup_serverpath) . $albumfolder;
										break;
									case 'in_webpath':
										$webpath = $_SERVER['SCRIPT_NAME'];
										$root = $_zp_setup_serverpath;
										if (!empty($webpath)) {
											$root = str_replace('\\', '/', dirname($root));
										}
										$albumfolder = $root . $albumfolder;
										break;
								}
								$good = setup::folderCheck('albums', $albumfolder, $_zp_conf_vars['album_folder_class'], NULL, true, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							} else {
								setup::checkmark(-1, gettext('<em>albums</em> folder'), gettext('<em>albums</em> folder [The line <code>\$conf[\'album_folder\']</code> is missing from your configuration file]'), sprintf(gettext('You should update your configuration file to conform to the current %1$s example file.'), CONFIGFILE));
							}

							$good = setup::folderCheck('cache', $_zp_setup_serverpath . '/' . CACHEFOLDER . '/', 'std', NULL, true, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							$good = setup::checkmark(file_exists($en_US), gettext('<em>locale</em> folders'), gettext('<em>locale</em> folders [Are not complete]'), gettext('Be sure you have uploaded the complete Zenphoto package. You must have at least the <em>en_US</em> folder.')) && $good;
							$good = setup::folderCheck(gettext('uploaded'), $_zp_setup_serverpath . '/' . UPLOAD_FOLDER . '/', 'std', NULL, false, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							$good = setup::folderCheck(DATA_FOLDER, $_zp_setup_serverpath . '/' . DATA_FOLDER . '/', 'std', NULL, false, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							@rmdir(SERVERPATH . '/' . DATA_FOLDER . '/mutex');
							@mkdir(SERVERPATH . '/' . DATA_FOLDER . '/' . MUTEX_FOLDER, $_zp_setup_chmod | 0311);

							$good = setup::folderCheck(gettext('HTML cache'), $_zp_setup_serverpath . '/' . STATIC_CACHE_FOLDER . '/', 'std', $Cache_html_subfolders, true, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							$good = setup::folderCheck(gettext('Third party plugins'), $_zp_setup_serverpath . '/' . USER_PLUGIN_FOLDER . '/', 'std', $plugin_subfolders, true, $_zp_setup_chmod | 0311, $updatechmod) && $good;
							?>
						</ul>
						<?php
						if ($good) {
							$dbmsg = "";
						} else {
							if (setup::userAuthorized()) {
								?>
								<div class="error">
									<?php echo gettext("You need to address the problems indicated above then run <code>setup</code> again."); ?>
								</div>
								<p class='buttons'>
									<a href="?refresh" title="<?php echo gettext("Setup failed."); ?>" style="font-size: 15pt; font-weight: bold;">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt=""/> <?php echo gettext("Refresh"); ?>
									</a>
								</p>
								<br class="clearall" /><br class="clearall" />
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
								setup::languageSelector();
							}
							?>
							<br class="clearall" />
							<?php
							echo "\n</div><!-- content -->";
							echo "\n</div><!-- main -->";
							setup::printFooter();
							echo "</body>";
							echo "</html>";
							exit();
						}
					} else {
						$dbmsg = gettext("database connected");
					} // system check
					if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {

						require(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
						require_once(dirname(dirname(__FILE__)) . '/functions/functions.php');
						echo '<p>' . gettext('Checking the database') . '</p>';
						$task = '';
						if (isset($_GET['create'])) {
							$task = 'create';
							$create = array_flip(explode(',', sanitize($_GET['create'])));
						}
						if (isset($_GET['update'])) {
							$task = 'update';
						}

						if ($_zp_db->connection && empty($task)) {
							$alltables = $_zp_db->getTables();
							$tables = array();
							$prefixLC = strtolower($_zp_conf_vars['mysql_prefix']);
							$prefixUC = strtoupper($prefixLC);
							if ($alltables) {
								foreach($alltables as $key) {
									$key = str_replace(array($prefixLC, $prefixUC), $_zp_conf_vars['mysql_prefix'], $key);
									$tables[$key] = 'update';
								}
							}
							$expected_tables = $_zp_db->getExpectedTables($_zp_conf_vars['mysql_prefix']);

							// v1.3.2 handle zenpage table name change transition:
							//				if the old table exists it gets updated instead of a new one being created
							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_pages'])) {
								unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'] . 'pages', $expected_tables)]);
								$expected_tables[] = $_zp_conf_vars['mysql_prefix'] . 'zenpage_pages';
							}
							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_news'])) {
								unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'] . 'news', $expected_tables)]);
								$expected_tables[] = $_zp_conf_vars['mysql_prefix'] . 'zenpage_zenpage_pages';
							}
							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_news2cat'])) {
								unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'] . 'news2cat', $expected_tables)]);
								$expected_tables[] = $_zp_conf_vars['mysql_prefix'] . 'zenpage_news';
							}
							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_news_categories'])) {
								unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'] . 'news_categories', $expected_tables)]);
								$expected_tables[] = $_zp_conf_vars['mysql_prefix'] . 'zenpage_news_categories';
							}
							foreach ($expected_tables as $needed) {
								if (!isset($tables[$needed])) {
									$tables[$needed] = 'create';
								}
							}
							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'admintoalbum'])) {
								$tables[$_zp_conf_vars['mysql_prefix'] . 'admin_to_object'] = 'update';
							}
						}

						// Prefix the table names. These already have `backticks` around them!
						$tbl_albums = $_zp_db->prefix('albums');
						$tbl_comments = $_zp_db->prefix('comments');
						$tbl_images = $_zp_db->prefix('images');
						$tbl_options = $_zp_db->prefix('options');
						$tbl_administrators = $_zp_db->prefix('administrators');
						$tbl_admin_to_object = $_zp_db->prefix('admin_to_object');
						$tbl_tags = $_zp_db->prefix('tags');
						$tbl_obj_to_tag = $_zp_db->prefix('obj_to_tag');
						$tbl_captcha = $_zp_db->prefix('captcha');
						$tbl_news = $_zp_db->prefix('news');
						$tbl_pages = $_zp_db->prefix('pages');
						$tbl_news_categories = $_zp_db->prefix('news_categories');
						$tbl_news2cat = $_zp_db->prefix('news2cat');
						$tbl_menu_manager = $_zp_db->prefix('menu');
						$tbl_plugin_storage = $_zp_db->prefix('plugin_storage');
						$tbl_searches = $_zp_db->prefix('search_cache');

						// Prefix the constraint names:
						$db_schema = array();
						$sql_statements = array();
						$collation = $_zp_db->getCollationSetClause();

						/***********************************************************************************
						  Add new fields in the upgrade section. This section should remain static except for new
						  tables. This tactic keeps all changes in one place so that noting gets accidentaly omitted.
						 *********************************************************************************** */

						//v1.2
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'captcha'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_captcha (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`ptime` int(32) UNSIGNED NOT NULL,
		`hash` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)	$collation;";
	}
	//v1.1.7
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'options'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_options (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0,
		`name` varchar(191) NOT NULL,
		`value` text,
		`theme` varchar (127) NOT NULL,
		`creator` varchar (255) DEFAULT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY (name(95), `ownerid`, theme(95))
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'tags'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_tags (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY (name(191))
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'obj_to_tag'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_obj_to_tag (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`tagid` int(11) UNSIGNED NOT NULL,
		`type` tinytext,
		`objectid` int(11) UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
		)	$collation;";
	}

	// v. 1.1.5
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'administrators'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_administrators (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`user` varchar(64) NOT NULL,
		`pass` varchar(64) NOT NULL,
		`passhash` int (1),
		`passupdate` datetime,
		`name` text,
		`email` text,
		`rights` int,
		`custom_data` text,
		`valid` int(1) NOT NULL DEFAULT 1,
		`group` varchar(64) DEFAULT NULL,
		`date` datetime,
		`loggedin` datetime,
		`lastloggedin` datetime,
		`quota` int(11) DEFAULT NULL,
		`language` varchar(5) DEFAULT NULL,
		`prime_album` varchar(255) DEFAULT NULL,
		`other_credentials` TEXT,
		`challenge_phrase` TEXT,
		PRIMARY KEY (`id`),
		UNIQUE KEY (`user`,`valid`)
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'admin_to_object'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_admin_to_object (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`adminid` int(11) UNSIGNED NOT NULL,
		`objectid` int(11) UNSIGNED NOT NULL,
		`type` varchar(32) DEFAULT 'album',
		`edit` int(11) DEFAULT 32767,
		PRIMARY KEY (`id`)
		)	$collation;";
	}


	// base implementation
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'albums'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_albums (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`parentid` int(11) unsigned default NULL,
		`folder` varchar(255) NOT NULL default '',
		`title` text,
		`desc` text,
		`date` datetime default NULL,
		`updateddate` datetime default NULL,
		`location` text,
		`show` int(1) unsigned NOT NULL default '1',
		`closecomments` int(1) unsigned NOT NULL default '0',
		`commentson` int(1) UNSIGNED NOT NULL default '1',
		`thumb` varchar(255) default NULL,
		`mtime` int(32) default NULL,
		`sort_type` varchar(20) default NULL,
		`subalbum_sort_type` varchar(20) default NULL,
		`sort_order` int(11) unsigned default NULL,
		`image_sortdirection` int(1) UNSIGNED default '0',
		`album_sortdirection` int(1) UNSIGNED default '0',
		`hitcounter` int(11) unsigned default 0,
		`password` varchar(255) NOT NULL DEFAULT '',
		`password_hint` text,
		`publishdate` datetime default NULL,
		`expiredate` datetime default NULL,
		`total_value` int(11) DEFAULT 0,
		`total_votes` int(11) DEFAULT 0,
		`used_ips` longtext,
		`custom_data` text,
		`dynamic` int(1) DEFAULT 0,
		`search_params` text,
		`album_theme` varchar(127),
		`user` varchar(64),
		`rating` float,
		`rating_status` int(1) DEFAULT 3,
		`watermark` varchar(255),
		`watermark_thumb` varchar(255),
		`owner` varchar(64) DEFAULT NULL,
		`codeblock` text,
		PRIMARY KEY (`id`),
		UNIQUE KEY (folder(191))
		)	$collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'comments'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_comments (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`ownerid` int(11) unsigned NOT NULL default '0',
		`name` varchar(255) NOT NULL default '',
		`email` varchar(255) NOT NULL default '',
		`website` varchar(255) default NULL,
		`date` datetime default NULL,
		`comment` text,
		`inmoderation` int(1) unsigned NOT NULL default '0',
		`type` varchar(52) DEFAULT 'images',
		`IP` text,
		`private` int(1) DEFAULT 0,
		`anon` int(1) DEFAULT 0,
		`custom_data` text,
		PRIMARY KEY (`id`),
		KEY `ownerid` (`ownerid`)
		)	$collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'images'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_images (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
				`albumid` int(11) unsigned NOT NULL default '0',
		`filename` varchar(255) NOT NULL default '',
		`title` text,
		`desc` text,
		`location` text,
		`city` tinytext,
		`state` tinytext,
		`country` tinytext,
		`credit` text,
		`copyright` text,
		`commentson` int(1) UNSIGNED NOT NULL default '1',
		`show` int(1) NOT NULL default '1',
		`date` datetime default NULL,
		`sort_order` int(11) unsigned default NULL,
		`height` int(10) unsigned default NULL,
		`width` int(10) unsigned default NULL,
		`thumbX` int(10) unsigned default NULL,
		`thumbY` int(10) unsigned default NULL,
		`thumbW` int(10) unsigned default NULL,
		`thumbH` int(10) unsigned default NULL,
		`mtime` int(32) default NULL,
		`publishdate` datetime default NULL,
		`expiredate` datetime default NULL,
		`hitcounter` int(11) unsigned default 0,
		`total_value` int(11) unsigned default '0',
		`total_votes` int(11) unsigned default '0',
		`used_ips` longtext,
		`custom_data` text,
		`rating` float,
		`rating_status` int(1) DEFAULT 3,
		`hasMetadata` int(1) DEFAULT 0,
		`watermark` varchar(255) DEFAULT NULL,
		`watermark_use` int(1) DEFAULT 7,
		`owner` varchar(64) DEFAULT NULL,
		`filesize` int(11),
		`codeblock` text,
		`user` varchar(64) DEFAULT NULL,
		`password` varchar(64) DEFAULT NULL,
		`password_hint` text,
		PRIMARY KEY (`id`),
		KEY (`albumid`),
		UNIQUE KEY (filename(191), albumid)
		)	$collation;";
	}

	//v1.2.4
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'news'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('news') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`title` text,
		`content` longtext,
		`extracontent` text,
		`show` int(1) unsigned NOT NULL default '1',
		`date` datetime,
		`titlelink` varchar(255) NOT NULL,
		`commentson` int(1) UNSIGNED NOT NULL,
		`codeblock` text,
		`author` varchar(64) NOT NULL,
		`lastchange` datetime default NULL,
		`lastchangeauthor` varchar(64) NOT NULL,
		`hitcounter` int(11) unsigned default 0,
		`permalink` int(1) unsigned NOT NULL default 0,
		`locked` int(1) unsigned NOT NULL default 0,
		`expiredate` datetime default NULL,
		`total_value` int(11) unsigned default '0',
		`total_votes` int(11) unsigned default '0',
		`used_ips` longtext,
		`rating` float,
		`rating_status` int(1) DEFAULT 3,
		`sticky` int(1) DEFAULT 0,
		`custom_data` text,
		`truncation` int(1) unsigned default 0,
		PRIMARY KEY (`id`),
		UNIQUE KEY (titlelink(191))
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'news_categories'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('news_categories') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`title` text,
		`titlelink` varchar(255) NOT NULL,
		`permalink` int(1) UNSIGNED NOT NULL default 0,
		`hitcounter` int(11) unsigned default 0,
		`user` varchar(64) DEFAULT NULL,
		`password` varchar(64) DEFAULT NULL,
		`password_hint` text,
		`parentid` int(11) DEFAULT NULL,
		`sort_order` varchar(48) DEFAULT NULL,
		`desc` text,
		`custom_data` text,
		`show` int(1) unsigned NOT NULL default '1',
		PRIMARY KEY (`id`),
		UNIQUE KEY (titlelink(191))
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'news2cat'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('news2cat') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`cat_id` int(11) unsigned NOT NULL,
		`news_id` int(11) unsigned NOT NULL,
		PRIMARY KEY (`id`)
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'pages'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('pages') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`parentid` int(11) unsigned default NULL,
		`title` text,
		`content` longtext,
		`extracontent` text,
		`sort_order`varchar(48) NOT NULL default '',
		`show` int(1) unsigned NOT NULL default '1',
		`titlelink` varchar(255) NOT NULL,
		`commentson` int(1) unsigned NOT NULL,
		`codeblock` text,
		`author` varchar(64) NOT NULL,
		`date` datetime default NULL,
		`lastchange` datetime default NULL,
		`lastchangeauthor` varchar(64) NOT NULL,
		`hitcounter` int(11) unsigned default 0,
		`permalink` int(1) unsigned NOT NULL default 0,
		`locked` int(1) unsigned NOT NULL default 0,
		`expiredate` datetime default NULL,
		`total_value` int(11) unsigned default '0',
		`total_votes` int(11) unsigned default '0',
		`used_ips` longtext,
		`rating` float,
		`rating_status` int(1) DEFAULT 3,
		`user` varchar(64) DEFAULT NULL,
		`password` varchar(64) DEFAULT NULL,
		`password_hint` text,
		`custom_data` text,
		`truncation` int(1) unsigned default 0,
		PRIMARY KEY (`id`),
		UNIQUE KEY (titlelink(191))
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'menu'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('menu') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`parentid` int(11) unsigned default NULL,
		`title` text,
		`link` varchar(255) NOT NULL,
		`include_li` int(1) unsigned default 1,
		`type` varchar(16) NOT NULL,
		`sort_order`varchar(48) NOT NULL default '',
		`show` int(1) unsigned NOT NULL default '1',
		`menuset` varchar(32) NOT NULL,
		`span_class` varchar(32) default NULL,
		`span_id` varchar(32) default NULL,
		PRIMARY KEY (`id`)
		) $collation;";
	}
	// v 1.3.2
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'plugin_storage'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('plugin_storage') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`type` varchar(32) NOT NULL,
		`aux` varchar(255),
		`data` longtext,
		PRIMARY KEY (`id`),
		KEY `type` (`type`),
		KEY aux(aux(191))
		) $collation;";
	}
	// v 1.4.2
	if (isset($create[$_zp_conf_vars['mysql_prefix'] . 'search_cache'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS " . $_zp_db->prefix('search_cache') . " (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`criteria` TEXT,
		`date` datetime default NULL,
		`data` longtext,
		KEY (`criteria`(191)),
		PRIMARY KEY (`id`)
		) $collation;";
	}

	/****************************************************************************************
						 * *****                             UPGRADE SECTION                                ******
						 * *****                                                                            ******
						 * *****                          Add all new fields below                          ******
						 * *****                                                                            ******
						 * ***************************************************************************************/

						//v1.3.2
						$sql_statements[] = "RENAME TABLE " . $_zp_db->prefix('zenpage_news') . " TO $tbl_news," .
										$_zp_db->prefix('zenpage_news2cat') . " TO $tbl_news2cat," .
										$_zp_db->prefix('zenpage_news_categories') . " TO $tbl_news_categories," .
										$_zp_db->prefix('zenpage_pages') . " TO $tbl_pages";

						// v. 1.0.0b
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `sort_type` varchar(20);";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `sort_order` int(11);";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `sort_order` int(11);";

						// v. 1.0.3b
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `height` INT UNSIGNED;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `width` INT UNSIGNED;";

						// v. 1.0.4b
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `parentid` int(11) unsigned default NULL;";

						// v. 1.0.9
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `mtime` int(32) default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `mtime` int(32) default NULL;";

						//v. 1.1
						$sql_statements[] = "ALTER TABLE $tbl_options DROP `bool`, DROP `description`;";
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `value` `value` text;";
//v1.1.7 omits	$sql_statements[] = "ALTER TABLE $tbl_options DROP INDEX `name`;";
//v1.1.7 omits	$sql_statements[] = "ALTER TABLE $tbl_options ADD UNIQUE (`name`);";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `commentson` int(1) UNSIGNED NOT NULL default '1';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `subalbum_sort_type` varchar(20) default NULL;";
//v1.1.7 omits	$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `tags` text;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `location` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `city` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `state` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `country` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `credit` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `copyright` tinytext;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `date` datetime default NULL;";
//v1.1.7 omits	$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `tags` text;";
//v1.2.7 omits	$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `EXIFValid` int(1) UNSIGNED default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `hitcounter` int(11) UNSIGNED default 0;";

						//v1.1.1
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `image_sortdirection` int(1) UNSIGNED default '0';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `album_sortdirection` int(1) UNSIGNED default '0';";

						//v1.1.3
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `total_value` int(11) UNSIGNED default '0';";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `total_votes` int(11) UNSIGNED default '0';";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `used_ips` longtext;";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `password` varchar(255) NOT NULL default '';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `password_hint` text;";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `hitcounter` int(11) UNSIGNED default 0;";

						//v1.1.4
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `type` varchar(52) NOT NULL default 'images';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `total_value` int(11) UNSIGNED default '0';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `total_votes` int(11) UNSIGNED default '0';";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `used_ips` longtext;";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `custom_data` text";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `custom_data` text";
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `password` `password` varchar(255) NOT NULL DEFAULT ''";

						//v1.1.5
						$sql_statements[] = " ALTER TABLE $tbl_comments DROP FOREIGN KEY `comments_ibfk1`";
						$sql_statements[] = "ALTER TABLE $tbl_comments CHANGE `imageid` `ownerid` int(11) UNSIGNED NOT NULL default '0';";
						//	$sql_statements[] = "ALTER TABLE $tbl_comments DROP INDEX `imageid`;";
						$sql = "SHOW INDEX FROM `" . $_zp_conf_vars['mysql_prefix'] . "comments`";
						$result = $_zp_db->query($sql, false);
						$hasownerid = false;
						if ($result) {
							while ($row = $_zp_db->fetchRow($result)) {
								if ($row[2] == 'ownerid') {
									$hasownerid = true;
								} else {
									if ($row[2] != 'PRIMARY') {
										$sql_statements[] = "ALTER TABLE $tbl_comments DROP INDEX `" . $row[2] . "`;";
									}
								}
							}
							$_zp_db->freeResult($result);
						}
						if (!$hasownerid) {
							$sql_statements[] = "ALTER TABLE $tbl_comments ADD INDEX (`ownerid`);";
						}
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `dynamic` int(1) UNSIGNED default '0'";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `search_params` text";

						//v1.1.6
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `album_theme` text";
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `IP` text";

						//v1.1.7
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `private` int(1) UNSIGNED default 0";
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `anon` int(1) UNSIGNED default 0";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `user` varchar(64) default ''";
						$sql_statements[] = "ALTER TABLE $tbl_tags " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_tags CHANGE `name` `name` varchar(255) " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_administrators " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_administrators CHANGE `name` `name` TEXT " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_options ADD COLUMN `ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0";
						$sql_statements[] = "ALTER TABLE $tbl_options DROP INDEX `name`";
//v1.2.7 omits	$sql_statements[] = "ALTER TABLE $tbl_options ADD UNIQUE `unique_option` (`name`, `ownerid`)";
						//v1.2
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `ownerid` `ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0";
						$sql_statements[] = "ALTER TABLE $tbl_obj_to_tag " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `name` `name` varchar(255) " . $collation;
						$hastagidindex = false;
						$sql = "SHOW INDEX FROM `" . $_zp_conf_vars['mysql_prefix'] . "obj_to_tag`";
						$result = $_zp_db->query($sql, false);
						if ($result) {
							while ($row = $_zp_db->fetchRow($result)) {
								if ($row[2] == 'tagid') {
									$hastagidindex = true;
								}
							}
							$_zp_db->freeResult($result);
						}
						if (!$hastagidindex) {
							$sql_statements[] = "ALTER TABLE $tbl_obj_to_tag ADD INDEX (`tagid`)";
							$sql_statements[] = "ALTER TABLE $tbl_obj_to_tag ADD INDEX (`objectid`)";
						}

						//v1.2.1
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `title` `title` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `title` `title` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `location` `location` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `credit` `credit` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `copyright` `copyright` TEXT";
						//v1.2.2
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `thumbX` int(10) UNSIGNED default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `thumbY` int(10) UNSIGNED default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `thumbW` int(10) UNSIGNED default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `thumbH` int(10) UNSIGNED default NULL;";

						//v1.2.4
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' DROP INDEX `titlelink`;'; 
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' ADD UNIQUE INDEX titlelink(titlelink(191));'; // utf8mb4 limit added as required for utf8mb4 in v1.6
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' DROP INDEX `titlelink`;'; 
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD UNIQUE INDEX titlelink(titlelink(191));'; // utf8mb4 limit added as required for utf8mb4 in v1.6
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' DROP INDEX `titlelink`;'; 
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD UNIQUE INDEX titlelink(titlelink(191));'; // utf8mb4 limit added as required for utf8mb4 in v1.6
						$sql_statements[] = 'ALTER TABLE ' . $tbl_comments . ' CHANGE `comment` `comment` TEXT;';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' CHANGE `title` `title` TEXT;';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' CHANGE `titlelink` `titlelink` varchar(255);';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' CHANGE `title` `title` TEXT;';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_comments . ' ADD COLUMN `custom_data` TEXT;';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `expiredate` datetime default NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `expiredate` datetime default NULL';
						$sql_statements[] = 'UPDATE ' . $tbl_pages . ' SET `parentid`=NULL WHERE `parentid`=0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' CHANGE `sort_order` `sort_order` VARCHAR(48) NOT NULL default ""';
						//v1.2.5
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `parentid` `parentid` int(11) unsigned default NULL;";
						$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `albumid` `albumid` int(11) unsigned default NULL";
						$sql_statements[] = 'UPDATE ' . $tbl_albums . ' SET `parentid`=NULL WHERE `parentid`=0';
						$sql_statements[] = 'UPDATE ' . $tbl_images . ' SET `albumid`=NULL WHERE `albumid`=0';
						$sql_statements[] = 'DELETE FROM ' . $tbl_pages . ' WHERE `titlelink`=""'; // cleanup for bad records

						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
						$sql_statements[] = 'UPDATE ' . $tbl_albums . ' SET rating=total_value / total_votes WHERE total_votes > 0';

						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
						$sql_statements[] = 'UPDATE ' . $tbl_images . ' SET rating=total_value / total_votes WHERE total_votes > 0';

						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `total_votes` int(11) UNSIGNED default 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `total_value` int(11) UNSIGNED default 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `used_ips` longtext';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';

						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `total_votes` int(11) UNSIGNED default 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `total_value` int(11) UNSIGNED default 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `used_ips` longtext';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
						//v1.2.6
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `custom_data` TEXT';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' CHANGE `password` `pass` varchar(64)';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `valid` int(1) NOT NULL DEFAULT 1';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `group` varchar(64)';
						$sql = 'SHOW INDEX FROM ' . $tbl_administrators;
						$result = $_zp_db->query($sql, false);
						if ($result) {
							while ($row = $_zp_db->fetchRow($result)) {
								if ($row[2] == 'user') {
									$sql_statements[] = "ALTER TABLE $tbl_administrators DROP INDEX `user`";
									$sql_statements[] = "ALTER TABLE $tbl_administrators ADD UNIQUE (`valid`, `user`)";
									break;
								}
							}
							$_zp_db->freeResult($result);
						}
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `watermark` varchar(255) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' CHANGE `commentson` `commentson` int(1) UNSIGNED default 0';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news . ' CHANGE `commentson` `commentson` int(1) UNSIGNED default 0';
						//v1.2.7
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `album_theme` `album_theme` varchar(127) DEFAULT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_options ADD COLUMN `theme` varchar(127) NOT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `name` `name` varchar(191) DEFAULT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_options DROP INDEX `unique_option`"; 
						$sql_statements[] = "ALTER TABLE $tbl_options ADD UNIQUE `unique_option` (name(95), `ownerid`, theme(95))"; // utf8mb4 limit added as required for utf8mb4 in v1.6
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' DROP COLUMN `EXIFValid`';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `hasMetadata` int(1) default 0';
						$sql_statements[] = 'UPDATE ' . $tbl_images . ' SET `date`=NULL WHERE `date`="0000-00-00 00:00:00"'; // empty dates should be NULL
						$sql_statements[] = 'UPDATE ' . $tbl_albums . ' SET `date`=NULL WHERE `date`="0000-00-00 00:00:00"'; // force metadata refresh
						//v1.2.8
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' CHANGE `place` `location` TEXT';
						//v1.3
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `watermark` varchar(255) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `watermark_use` int(1) DEFAULT 7';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `owner` varchar(64) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `filesize` INT';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `quota` INT';
						$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `user` varchar(64) default ''";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_pages . ' ADD COLUMN `password` VARCHAR(64)';
						$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `password_hint` text;";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `user` varchar(64) default ''";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' ADD COLUMN `password` VARCHAR(64)';
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `password_hint` text;";

						//v1.3.1
						$sql_statements[] = 'RENAME TABLE ' . $_zp_db->prefix('admintoalbum') . ' TO ' . $tbl_admin_to_object;
						$sql_statements[] = 'ALTER TABLE ' . $tbl_admin_to_object . ' ADD COLUMN `type` varchar(32) DEFAULT "album";';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_admin_to_object . ' CHANGE `albumid` `objectid` int(11) UNSIGNED NOT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' CHANGE `albums` `objects` varchar(64)';
						$sql_statements[] = "ALTER TABLE $tbl_news ADD COLUMN `sticky` int(1) default 0";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `codeblock` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `codeblock` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_admin_to_object ADD COLUMN `edit` int default 32767";

						//v1.4.0
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `value` `value` TEXT " . $collation;
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `parentid` INT(11) DEFAULT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `sort_order` varchar(48)";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `user` varchar(64) default ''";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `password` VARCHAR(64)';
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `password_hint` text;";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories CHANGE `cat_name` `title` TEXT";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories CHANGE `cat_link` `titlelink` varchar(255) NOT NULL";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' DROP INDEX `cat_link`;'; 
						$sql_statements[] = 'UPDATE ' . $tbl_obj_to_tag . ' SET `type`="news" WHERE `type`="zenpage_news"';
						$sql_statements[] = 'UPDATE ' . $tbl_obj_to_tag . ' SET `type`="pages" WHERE `type`="zenpage_pages"';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `language` VARCHAR(5)';
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `desc` text;";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `custom_data` text";
						$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `custom_data` text";
						$sql_statements[] = "ALTER TABLE $tbl_news ADD COLUMN `custom_data` text";
						$sql_statements[] = "ALTER TABLE $tbl_images DROP FOREIGN KEY `images_ibfk1`";
						$sql_statements[] = "ALTER TABLE $tbl_menu_manager ADD COLUMN `span_id` varchar(43) default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_menu_manager ADD COLUMN `span_class` varchar(43) default NULL";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `watermark_thumb` varchar(255) DEFAULT NULL';
						//v1.4.1
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `prime_album` varchar(255) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `other_credentials` TEXT';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `date` datetime';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `loggedin` datetime';
						$sql_statements[] = 'UPDATE ' . $tbl_administrators . ' SET `date`="' . date('Y-m-d H:i:s', $zptime) . '" WHERE `date` IS NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `owner` varchar(64) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' CHANGE `owner` `owner` varchar(64) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_options . ' ADD COLUMN `creator` varchar(255) DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `updateddate` datetime DEFAULT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' DROP INDEX `title`';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' CHANGE `titlelink` `titlelink` VARCHAR(255) NOT NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_news_categories . ' CHANGE `title` `title` TEXT';
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `show` int(1) unsigned NOT NULL default '1'";
						//v1.4.2
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `challenge_phrase` TEXT';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `publishdate` datetime default NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_albums . ' ADD COLUMN `expiredate` datetime default NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `publishdate` datetime default NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_images . ' ADD COLUMN `expiredate` datetime default NULL';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `lastloggedin` datetime';
						//v1.4.3
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `theme` `theme` varchar(127) NOT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `name` `name` varchar(191) NOT NULL";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `valid` int(1) NOT NULL DEFAULT 1';
						$sql_statements[] = "ALTER TABLE $tbl_tags CHANGE `name` `name` varchar(255) NOT NULL";
						$sql_statements[] = "ALTER TABLE $tbl_images DROP FOREIGN KEY `" . trim($tbl_images, '`') . "_ibfk1`";
						$sql_statements[] = "ALTER TABLE $tbl_comments DROP FOREIGN KEY `" . trim($tbl_comments, '`') . "_ibfk1`";
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' CHANGE `pass` `pass` varchar(64)';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `passhash` int (1)';
						$sql_statements[] = 'ALTER TABLE ' . $tbl_administrators . ' ADD COLUMN `passupdate` datetime';
						//v1.4.4
						$sql_statements[] = "ALTER TABLE $tbl_searches CHANGE `data` `data` LONGTEXT";
						$sql_statements[] = "ALTER TABLE $tbl_plugin_storage CHANGE `data` `data` LONGTEXT";
						$sql_statements[] = "ALTER TABLE $tbl_news CHANGE `content` `content` LONGTEXT";
						$sql_statements[] = "ALTER TABLE $tbl_pages CHANGE `content` `content` LONGTEXT";
						//v1.4.5
						$sql_statements[] = "ALTER TABLE $tbl_news ADD COLUMN `truncation` int(1) unsigned NOT NULL default '0'";
						$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `truncation` int(1) unsigned NOT NULL default '0'";
						$sql_statements[] = "CREATE INDEX `albumid` ON $tbl_images (`albumid`)";
						$sql_statements[] = "ALTER TABLE $tbl_albums DROP INDEX `folder`"; 
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD UNIQUE INDEX folder(folder(191))"; // utf8mb4 limit added as required for utf8mb4 in v1.6
						$sql_statements[] = "ALTER TABLE $tbl_images DROP INDEX `filename`"; 
						$sql_statements[] = "ALTER TABLE $tbl_images ADD UNIQUE INDEX filename(filename(191), albumid)"; // utf8mb4 limit added as required for utf8mb4 in v1.6
			
						//1.5.2
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `lastchange` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `lastchangeuser` varchar(64)";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `lastchange` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `lastchangeuser` varchar(64)";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `lastchange` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `lastchangeuser` varchar(64)";
						$sql_statements[] = "ALTER TABLE $tbl_administrators ADD COLUMN `lastchange` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_administrators ADD COLUMN `lastchangeuser` varchar(64)";
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `lastchange` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_comments ADD COLUMN `lastchangeuser` varchar(64)";
						
						$sql_statements[] = "ALTER TABLE $tbl_news CHANGE `lastchangeauthor` `lastchangeuser` varchar(64)";
						$sql_statements[] = "ALTER TABLE $tbl_pages CHANGE `lastchangeauthor` `lastchangeuser` varchar(64)";
						
						//1.5.5
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `sort_type` `sort_type` varchar(128)";
						$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `subalbum_sort_type` `subalbum_sort_type` varchar(128)";
						
						//1.5.8
						$sql_statements[] = "ALTER TABLE $tbl_administrators ADD COLUMN `lastvisit` datetime default NULL";
						$sql_statements[] = "ALTER TABLE $tbl_pages CHANGE `sort_order` `sort_order` varchar(48) DEFAULT NULL";
						
						//1.6 - utf8mb4 index limitation on some db configs
						//Note: More 1.6 changes required had to be incorporated in earlier update queries above
					
						$sql_statements[] = "ALTER TABLE $tbl_plugin_storage DROP INDEX aux, ADD INDEX aux(aux(191))";
						$sql_statements[] = "ALTER TABLE $tbl_tags DROP INDEX name, ADD UNIQUE INDEX name(name(191))";
						$sql_statements[] = "ALTER TABLE $tbl_searches DROP INDEX criteria, ADD UNIQUE INDEX criteria(criteria(191))";
						
						//1.6.1
						$sql_statements[] = "ALTER TABLE $tbl_menu_manager ADD COLUMN `open_newtab` int(1) unsigned NOT NULL default '0'";
						
						// do this last incase there are any field changes of like names!
						foreach ($_zp_exifvars as $key => $exifvar) {
							if ($s = $exifvar[6]) {
								switch($s) {
									case 'number':
									case 'time':
									default:
										$size = "varchar(255)";
										break;
									case 'string':
										$size = 'MEDIUMTEXT';
										break;
								}
								/*if ($s < 255) {
									$size = "varchar($s)";
								} else {
									$size = 'MEDIUMTEXT';
								} */
								$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `$key` $size default NULL";
								$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `$key` `$key` $size default NULL";
							}
							
						}


						/**
						 * ************************************************************************************
						 * *****                            END of UPGRADE SECTION
						 * *****
						 * *****                           Add all new fields above
						 * *****
						 * ************************************************************************************* */
						$createTables = true;
						if (isset($_GET['create']) || isset($_GET['update']) || isset($_GET['protect_files']) && $_zp_db->connection) {
							if (!isset($_GET['protect_files'])) {
								if ($taskDisplay[substr($task, 0, 8)] == 'create') {
									echo "<p>" . gettext("About to create database tables") . "...</p>";
								} else {
									echo "<p>" . gettext("About to update database tables") . "...</p>";
								}
								if(!empty($db_schema)) {
									$message = gettext("Begin database table creation");
									echo '<p>' . $message . '</p>';
									setup::log($message);
									foreach ($db_schema as $sql) {
										$message = '';
										@set_time_limit(180);
										$result = $_zp_db->createTable($sql);
										if (!$result) {
											$createTables = false;
											$message = sprintf(gettext('Table creation failure:<br />Query: %1$s<br />Error: %2$s'), $sql, $_zp_db->getError());
											echo '<p class="error"><img src="'.FULLWEBPATH . '/' . ZENFOLDER . '/images/fail.png" alt="failure">'. $message . '</p>';
											setup::log($message);
										} else {
											echo '<img src="'.FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="">';
											setup::log(sprintf(gettext('Query ( %s ) Success.'), $sql));
										}
									}
								}
								// always run the update queries to insure the tables are up to current level
								echo '<p>'.gettext("Begin table updates").'</p>';
								setup::log(gettext("Begin table updates"));
								foreach ($sql_statements as $sql) {
									@set_time_limit(180);
									$result = $_zp_db->tableUpdate($sql);
									if (!$result) {
										$error = $_zp_db->getError();
										$reset = strpos($error, 'syntax');
										$message = sprintf(gettext('Query %1$s Failed. Error: %2$s'), $sql, $error);
										if($reset) { 
											echo '<p class="error"><img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/fail.png" alt="failure"></p>';
										}
										setup::log(sprintf(gettext('Query %1$s Failed. Error: %2$s'), $sql, $error), $reset);
									} else {
										echo '<img src="'.FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="">';
										setup::log(sprintf(gettext('Query ( %s ) Success.'), $sql));
									}
								}
								echo '<p>'.gettext("Begin converting UTF-8 tables to utf8mb4 collation").'</p>';
								$alltables = $_zp_db->getTables();
								if($alltables) {
									foreach($alltables as $table) {
										$success = $_zp_db->convertTableToUtf8mb4($table);
										$convert_error = $_zp_db->getError();
										if ($success) {
											echo '<img src="'.FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="">';
											setup::log(sprintf(gettext('UTF-8 Table %s and its columns converted to utf8mb4 collation.'), $table));
										} else if ($convert_error) {
											echo '<img src="'.FULLWEBPATH . '/' . ZENFOLDER . '/images/fail.png" alt="">';
											setup::log(sprintf(gettext('ERROR: UTF-8 table %1$s and/or its columns could not be converted to utf8mb4 collation: %2$s'), $table, $convert_error));
										}
									}
								}
								
								echo "<p>";
								if ($taskDisplay[substr($task, 0, 8)] == 'create') {
									if ($createTables) {
										echo gettext('Done with table create!');
									} else {
										echo gettext('Done with table create with errors!');
									}
								} else {
									if ($createTables) {
										echo gettext('Done with table update');
									} else {
										echo gettext('Done with table update with errors');
									}
								}
								echo "</p>";
								
								echo '<p>'. gettext('Checking table specifications'). '</p>';
								setup::checkUnique($tbl_administrators, array('valid' => 0, 'user' => 0));
								setup::checkUnique($tbl_albums, array('folder' => 0));
								setup::checkUnique($tbl_images, array('albumid' => 0, 'filename' => 0));
								setup::checkUnique($tbl_options, array('name' => 0, 'ownerid' => 0, 'theme' => 0));
								setup::checkUnique($tbl_news_categories, array('titlelink' => 0));
								setup::checkUnique($tbl_news, array('titlelink' => 0));
								setup::checkUnique($tbl_pages, array('titlelink' => 0));
								setup::checkUnique($tbl_tags, array('name' => 0));

								// set defaults on any options that need it
								setup::log(gettext("Done with database creation and update"));
								if ($prevRel = getOption('zenphoto_release')) {
									setup::log(sprintf(gettext("Previous Release was %s"), $prevRel), true);
								}
								
								echo '<p>'. gettext('Setting default options'). '</p>';
								require(dirname(__FILE__) . '/setup-option-defaults.php');

								if ($_zp_setup_debug == 'base64') {
									// update zenpage codeblocks--remove the base64 encoding
									$sql = 'SELECT `id`, `codeblock` FROM ' . $_zp_db->prefix('news') . ' WHERE `codeblock` NOT REGEXP "^a:[0-9]+:{"';
									$result = $_zp_db->queryFullArray($sql, false);
									if (is_array($result)) {
										foreach ($result as $row) {
											$codeblock = base64_decode($row['codeblock']);
											$sql = 'UPDATE ' . $_zp_db->prefix('news') . ' SET `codeblock`=' . $_zp_db->quote($codeblock) . ' WHERE `id`=' . $row['id'];
											$_zp_db->query($sql);
										}
									}
									$sql = 'SELECT `id`, `codeblock` FROM ' . $_zp_db->prefix('pages') . ' WHERE `codeblock` NOT REGEXP "^a:[0-9]+:{"';
									$result = $_zp_db->queryFullArray($sql, false);
									if (is_array($result)) {
										foreach ($result as $row) {
											$codeblock = base64_decode($row['codeblock']);
											$sql = 'UPDATE ' . $_zp_db->prefix('pages') . ' SET `codeblock`=' . $_zp_db->quote($codeblock) . ' WHERE `id`=' . $row['id'];
											$_zp_db->query($sql);
										}
									}
								}

								if ($_zp_setup_debug == 'albumids') {
									// fixes 1.2 move/copy albums with wrong ids
									$albums = $_zp_gallery->getAlbums();
									foreach ($albums as $album) {
										checkAlbumParentid($album, NULL, 'setuplog');
									}
								}
							}

							if ($createTables) {
								if ($_zp_loggedin == ADMIN_RIGHTS) {
									$filelist = safe_glob(getBackupFolder(SERVERPATH) . '*.zdb');
									if (count($filelist) > 0) {
										$link = sprintf(gettext('You may <a href="%1$s">set your admin user and password</a> or <a href="%2$s">run backup-restore</a>'), WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users', WEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php');
										$_zp_setup_autorun = false;
									} else {
										$link = sprintf(gettext('You need to <a href="%1$s">set your admin user and password</a>'), WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users');
										if ($_zp_setup_autorun == 'admin' || $_zp_setup_autorun == 'gallery') {
											$_zp_setup_autorun = WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users';
										}
									}
								} else {
									$link = sprintf(gettext('You can now <a href="%1$s">administer your gallery</a>.'), WEBPATH . '/' . ZENFOLDER . '/admin.php');
								}
								setOption('setup_unprotected_by_adminrequest', 0, true, null);
								if (getOption('maintenance_mode_auto-open')) {
									maintenanceMode::setState('open', $setupMutex);
								}
								?>
								<p id="golink" class="delayshow" style="display:none;"><?php echo $link; ?></p>
								<?php
								switch ($_zp_setup_autorun) {
									case false:
										break;
									case 'gallery':
									case 'admin':
										$_zp_setup_autorun = WEBPATH . '/' . ZENFOLDER . '/admin.php';
										break;
									default:
										break;
								}
								?>
								<script>
									window.onload = function() {
										$('.delayshow').show();
			<?php
			if ($_zp_setup_autorun) {
				?>
											if (!imageErr) {
												$('#golink').hide();
												window.location = '<?php echo $_zp_setup_autorun; ?>';
											}
				<?php
			}
			?>
									}
								</script>
								<?php
							}
						} else if ($_zp_db->connection) {
							$task = '';
							if (setup::userAuthorized() || $blindInstall) {
								if (!empty($dbmsg)) {
									?>
									<h2><?php echo $dbmsg; ?></h2>
									<?php
								}
								?>
								<div class="dbwindow">
									<ul>
										<?php
										$db_list = '';
										$create = array();
										foreach ($expected_tables as $table) {
											if ($tables[$table] == 'create') {
												$create[] = $table;
												if (!empty($db_list)) {
													$db_list .= ', ';
												}
												$db_list .= "<code>$table</code>";
											}
										}
										if (($nc = count($create)) > 0) {
											?>
											<li class="createdb">
												<?php
												printf(gettext("Database tables to create: %s"), $db_list);
												?>
											</li>
											<?php
										}
										$db_list = '';
										$update = array();
										foreach ($expected_tables as $table) {
											if ($tables[$table] == 'update') {
												$update[] = $table;
												if (!empty($db_list)) {
													$db_list .= ', ';
												}
												$db_list .= "<code>$table</code>";
											}
										}
										if (($nu = count($update)) > 0) {
											?>
											<li class="updatedb">
												<?php
												printf(gettext("Database tables to update: %s"), $db_list);
												?>
											</li>
											<?php
										}
										?>
									</ul>
								</div>
								<?php
								if ($nc > 0) {
									$task = "create=" . implode(',', $create);
								}
								if ($nu > 0) {
									if (empty($task)) {
										$task = "update";
									} else {
										$task .= "&update";
									}
								}
								if ($_zp_setup_debug) {
									$task .= '&debug=' . $_zp_setup_debug;
								}
							}

							if (isset($tables[$_zp_conf_vars['mysql_prefix'] . 'zenpage_news'])) {
								$hideGoButton = ' style="display:none"';
								$_zp_setup_autorun = false;
								?>
								<div class="warning" id="dbrestructure">
									<p><?php echo gettext('<strong>Warning!</strong> This upgrade makes structural changes to the database which are not easily reversed. Be sure you have a database backup before proceeding.'); ?></p>
									<form>
										<input type="hidden" name="xsrfToken" value="<?php echo setup::getXSRFToken(); ?>" />
										<p><?php printf(gettext('%s I acknowledge that proceeding will restructure my database.'), '<input type="checkbox" id="agree" value="0" onclick="javascript:$(\'#setup\').show();$(\'#agree\').attr(\'checked\',\'checked\')" />')
								?></p>
									</form>
								</div>
								<?php
							} else {
								$hideGoButton = '';
							}
							if ($_zp_setup_warn) {
								$img = 'warn.png';
							} else {
								$img = 'pass.png';
							}
							if ($_zp_setup_autorun) {
								$task .= '&autorun=' . $_zp_setup_autorun;
							}
							if ($blindInstall) {
								ob_end_clean();
								$blindInstall = false;
								$stop = !$_zp_setup_autorun;
							} else {
								$stop = !setup::userAuthorized();
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
								<form id="setup" action="<?php
								echo WEBPATH . '/' . ZENFOLDER, '/setup/index.php?checked';
								echo $task . $mod;
								?>" method="post"<?php echo $hideGoButton; ?> >
									<input type="hidden" name="setUTF8URI" id="setUTF8URI" value="dont" />
									<input type="hidden" name="xsrfToken" value="<?php echo setup::getXSRFToken(); ?>" />
									<?php
									if (isset($_REQUEST['autorun'])) {
										if (!empty($_REQUEST['autorun'])) {
											$auto = setup::sanitize($_REQUEST['autorun']);
										} else {
											$auto = 'admin';
										}
										?>
										<input type="hidden" id="autorun" name="autorun" value="<?php echo html_encode($auto); ?>" />
										<?php
									}
									?>
									<p><?php echo gettext('Congratulations, we are ready to setup Zenphoto. A little patience please as the process may take some time.'); ?>
									<p class="buttons"><button class="submitbutton" id="submitbutton" type="submit"	title="<?php echo gettext('run setup'); ?>" ><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/<?php echo $img; ?>" alt="" /><?php echo gettext("Go"); ?></button></p>
									<br class="clearall" /><br class="clearall" />
								</form>
								<?php
							}
							if ($_zp_setup_autorun) {
								?>
								<script>
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
									<?php echo gettext("If you haven not created the database yet, now would be a good time."); ?>
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
						setup::languageSelector();
					}
					setup::printFooter(); 
	
$setupMutex->unlock();
