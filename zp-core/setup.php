<?php
/**
 * install routine for zenphoto
 * @package setup
 */

// force UTF-8 Ø
$_zp_last_modified = gmdate('D, d M Y H:i:s').' GMT';
// leave this as the second executable statement to avoid problems with PHP not having gettext support.
if(!function_exists("gettext")) {
	require_once(dirname(__FILE__).'/lib-gettext/gettext.inc');
	$noxlate = -1;
} else {
	$noxlate = 1;
}

define('OFFSET_PATH', 2);

require_once(dirname(__FILE__).'/global-definitions.php');
header('Last-Modified: ' . $_zp_last_modified);
header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0");

define('CONFIGFILE',dirname(dirname(__FILE__)).'/'.DATA_FOLDER.'/zp-config.php');
define('HTACCESS_VERSION', '1.4.1');  // be sure to change this the one in .htaccess when the .htaccess file is updated.

$debug = isset($_REQUEST['debug']);

$setup_checked = isset($_GET['checked']);
$upgrade = false;

require_once(dirname(__FILE__).'/setup/setup-functions.php');
require_once(dirname(__FILE__).'/lib-utf8.php');
$const_webpath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$const_webpath = str_replace("\\", '/', $const_webpath);
if ($const_webpath == '/') $const_webpath = '';
$serverpath = str_replace("\\", '/', dirname(dirname(__FILE__)));

$chmod = fileperms(dirname(__FILE__))&0777;

$en_US = dirname(__FILE__).'/locale/en_US/';
if (!file_exists($en_US)) {
	@mkdir(dirname(__FILE__).'/locale/', $chmod);
	@mkdir($en_US, $chmod);
}

$selected_database = 'MySQL';
if (file_exists(CONFIGFILE)) {
	$newconfig = false;
	$zptime = filemtime(CONFIGFILE);
} else {
	$zptime = time();
	if (!file_exists(dirname(dirname(__FILE__)).'/'.DATA_FOLDER)) {
		@mkdir(dirname(dirname(__FILE__)).'/'.DATA_FOLDER, $chmod);
	}
	if (file_exists(dirname(dirname(__FILE__)).'/'.ZENFOLDER.'/zp-config.php')) {
		@copy(dirname(dirname(__FILE__)).'/'.ZENFOLDER.'/zp-config.php', CONFIGFILE);
		@unlink(dirname(dirname(__FILE__)).'/'.ZENFOLDER.'/zp-config.php');
		$newconfig = false;
	} else {
		$newconfig = true;
		@copy('zp-config.php.source', CONFIGFILE);
	}
}

if (session_id() == '') {
	session_start();
}

$zp_cfg = @file_get_contents(CONFIGFILE);
$xsrftoken = sha1(CONFIGFILE.@file_get_contents(CONFIGFILE).session_id());

$updatezp_config = false;
if (isset($_GET['mod_rewrite'])) {
	$mod = '&mod_rewrite='.$_GET['mod_rewrite'];
} else {
	$mod = '';
}


$i = strpos($zp_cfg, 'define("DEBUG", false);');
if ($i !== false) {
	$updatezp_config = true;
	$j = strpos($zp_cfg, "\n", $i);
	$zp_cfg = substr($zp_cfg, 0, $i) . substr($zp_cfg, $j); // remove this so it won't be defined twice
}

if (isset($_POST['db'])) { //try to update the zp-config file
	setupXSRFDefender();
	setupLog(gettext("db POST handling"));
	$updatezp_config = true;
	if (isset($_POST['db_software'])) {
		updateConfigItem('db_software', setup_sanitize($_POST['db_software']));
	}
	if (isset($_POST['db_user'])) {
		updateConfigItem('mysql_user', setup_sanitize($_POST['db_user']));
	}
	if (isset($_POST['db_pass'])) {
		updateConfigItem('mysql_pass', setup_sanitize($_POST['db_pass']));
	}
	if (isset($_POST['db_host'])) {
		updateConfigItem('mysql_host', setup_sanitize($_POST['db_host']));
	}
	if (isset($_POST['db_database'])) {
		updateConfigItem('mysql_database', setup_sanitize($_POST['db_database']));
	}
	if (isset($_POST['db_prefix'])) {
		updateConfigItem('mysql_prefix', setup_sanitize($_POST['db_prefix']));
	}
}

$permissions = array(0750,0755,0775,0777); // NOTE: also $permission_names array below
if ($updatechmod = isset($_REQUEST['chmod_permissions'])) {
	setupXSRFDefender();
	$selected = round($_REQUEST['chmod_permissions']);
	if ($selected>=0 && $selected<count($permissions)) {
		$chmod = $permissions[$selected];
	} else {
		$updatechmod = false;
	}
}
if ($updatechmod || $newconfig) {
	if ($updatechmod || isset($_zp_conf_vars['CHMOD'])) {
		$chmodval = "\$conf['CHMOD']";
	} else {
		$chmodval = sprintf('0%o',$chmod);
	}
	$i = strpos($zp_cfg, "define('CHMOD_VALUE',");
	if ($i === false) {
		$i = strpos($zp_cfg, "define('SERVERPATH',");
		$i = strpos($zp_cfg, ';', $i);
		$i = strpos($zp_cfg, '/**', $i);
		$zp_cfg = substr($zp_cfg, 0, $i)."if (!defined('CHMOD_VALUE')) { define('CHMOD_VALUE', ".$chmodval."); }\n".substr($zp_cfg, $i);
	} else {
		$i = $i + 21;
		$j = strpos($zp_cfg, ")", $i);
		$zp_cfg = substr($zp_cfg, 0, $i).$chmodval.substr($zp_cfg, $j);
	}
	if ($updatechmod) {
		$i = strpos($zp_cfg, "\$conf['CHMOD'] = ");
		if ($i === false) {
			$i = strpos($zp_cfg, "\$conf['server_protocol'] =");
			$i = strpos($zp_cfg, ';', $i);
			$i = strpos($zp_cfg, '//', $i);
			$i = strpos($zp_cfg, '//', $i+1);
			$zp_cfg = substr($zp_cfg, 0, $i)."\$conf['CHMOD'] = ".sprintf('0%o',$chmod).";\n".substr($zp_cfg, $i);
		} else {
			$i = $i + 17;
			$j = strpos($zp_cfg, ";", $i);
			$zp_cfg = substr($zp_cfg, 0, $i) . sprintf('0%o',$chmod) . substr($zp_cfg, $j);
		}
	}
	$updatezp_config = true;
}
if ($updatefileset = isset($_REQUEST['FILESYSTEM_CHARSET'])) {
	setupXSRFDefender();
	$fileset = $_REQUEST['FILESYSTEM_CHARSET'];
	$i = strpos($zp_cfg, 	"\$conf['FILESYSTEM_CHARSET'] = ");
	if ($fileset && $i === false) {
		$i = strpos($zp_cfg, "\$conf['server_protocol'] =");
		$i = strpos($zp_cfg, ';', $i);
		$i = strpos($zp_cfg, '//', $i);
		$i = strpos($zp_cfg, '//', $i+1);
		$zp_cfg = substr($zp_cfg, 0, $i)."//\n\$conf['FILESYSTEM_CHARSET'] = '$fileset';\n".substr($zp_cfg, $i);
		$i = strpos($zp_cfg, "if (!defined('FILESYSTEM_CHARSET')");
		if ($i !== false) {
			$j = strpos($zp_cfg, ';', $i);
			$j = strpos($zp_cfg, '$', $j);
			$zp_cfg = substr($zp_cfg, 0, $i).substr($zp_cfg, $j);
		}
	} else {
		$i = strpos($zp_cfg, "'", $i+30);
		$j = strpos($zp_cfg, "'", $i+1);
		$zp_cfg = substr($zp_cfg, 0, $i+1).$fileset.substr($zp_cfg, $j);
	}
	$updatezp_config = true;
}

if ($updatezp_config) {
	@chmod(CONFIGFILE, 0666 & $chmod);
	if (is_writeable(CONFIGFILE)) {
		if ($handle = fopen(CONFIGFILE, 'w')) {
			if (fwrite($handle, $zp_cfg)) {
				setupLog(gettext("Updated zp-config.php"));
				$base = true;
			}
		}
		fclose($handle);
		clearstatcache();
	}
	$xsrftoken = sha1(CONFIGFILE.@file_get_contents(CONFIGFILE).session_id());
}

$result = true;
$environ = false;
$DBcreated = false;
$oktocreate = false;
$connection = false;
$connectDBErr = '';
if (file_exists(CONFIGFILE)) {
	require(CONFIGFILE);
	if (isset($_zp_conf_vars['db_software'])) {
		$selected_database = $_zp_conf_vars['db_software'];
	} else {
		$selected_database = $_zp_conf_vars['db_software'] = 'MySQL';
	}
	require_once(dirname(__FILE__).'/functions-db-'.($_zp_conf_vars['db_software']).'.php');
	$connectDBErr = '';
	if($connection = db_connect(false)) {	// got both MySQL and the database connected
		$result = query("SELECT `id` FROM " . $_zp_conf_vars['mysql_prefix'].'options' . " LIMIT 1", false);
		if ($result) {
			if (db_num_rows($result) > 0) {
				$upgrade = true;
				// apply some critical updates to the database for migration issues
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'].'administrators' . ' ADD COLUMN `valid` int(1) default 1', false);
				query('ALTER TABLE ' . $_zp_conf_vars['mysql_prefix'].'administrators' . ' CHANGE `password` `pass` varchar(64)', false);
			}
		}
		query('ALTER TABLE '.$_zp_conf_vars['mysql_prefix'].'administrators'.' ADD COLUMN `loggedin` datetime', false);
		$environ = true;
		require_once(dirname(__FILE__).'/admin-functions.php');
	} else {
		if ($_zp_DB_connection) {	// there was a connection to MySQL but not to the database.
			if (!empty($_zp_conf_vars['mysql_database'])) {
				if (isset($_GET['Create_Database'])) {
					$result = db_create();
					if ($result && ($connection = db_connect(false))) {
						$environ = true;
						require_once(dirname(__FILE__).'/admin-functions.php');
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
if (defined('CHMOD_VALUE')) {
	$chmod = CHMOD_VALUE;
}

if (function_exists('setOption')) {
	setOptionDefault('zp_plugin_security-logger', 9);
} else {	 // setup a primitive environment
	$environ = false;
	require_once(dirname(__FILE__).'/setup/setup-primitive.php');
	require_once(dirname(__FILE__).'/functions-filter.php');
	require_once(dirname(__FILE__).'/functions-i18n.php');
}
$updatechmod = $updatechmod  && zp_loggedin(ADMIN_RIGHTS);

if ($newconfig || isset($_GET['copyhtaccess'])) {
	if ($newconfig && !file_exists(dirname(dirname(__FILE__)).'/.htaccess') || zp_loggedin(ADMIN_RIGHTS)) {
		copy('htaccess', dirname(dirname(__FILE__)).'/.htaccess');
	}
}

if ($setup_checked) {
	if (!isset($_GET['delete_files'])) {
		setupLog(gettext("Completed system check"), true);
		if (isset($_COOKIE['setup_test_cookie'])) {
			$setup_cookie = $_COOKIE['setup_test_cookie'];
		} else {
			$setup_cookie = '';
		}
		if ($setup_cookie == ZENPHOTO_RELEASE) {
			setupLog(gettext('Setup cookie test successful'));
			setcookie('setup_test_cookie', '', time()-368000, '/');
		} else {
			setupLog(gettext('Setup cookie test unsuccessful'), true);
		}
	}
} else {
	if (isset($_POST['db'])) {
		setupLog(gettext("Post of Database credentials"), true);
	} else {
		setupLog("Zenphoto Setup v".ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.'] '.date('r'), true, true);  // initialize the log file
	}
	if ($environ) {
		setupLog(gettext("Full environment"));
	} else {
		setupLog(gettext("Primitive environment"));
		if ($connectDBErr) {
			setupLog(sprintf(gettext("Query error: %s"),$connectDBErr), true);
		}
	}
	setcookie('setup_test_cookie', ZENPHOTO_RELEASE, time()+3600, '/');
}

if (!isset($_zp_setupCurrentLocale_result) || empty($_zp_setupCurrentLocale_result)) {
	if (DEBUG_LOCALE) debugLog('Setup checking locale');
	$_zp_setupCurrentLocale_result = setMainDomain();
	if (DEBUG_LOCALE) debugLog('$_zp_setupCurrentLocale_result = '.$_zp_setupCurrentLocale_result);
}

// NOTE: see also $permissions array avove
$permission_names = array(0750=>gettext('strict+'),
													0755=>gettext('strict'),
													0775=>gettext('relaxed'),
													0777=>gettext('loose')
													);

$taskDisplay = array('create' => gettext("create"), 'update' => gettext("update"));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $upgrade ? gettext("Zenphoto upgrade") : gettext("Zenphoto setup") ; ?></title>
<link rel="stylesheet" href="admin.css" type="text/css" />

<script src="js/jquery.js" type="text/javascript"></script>
<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/zenphoto.js" type="text/javascript" ></script>
<script type="text/javascript">
	function toggle_visibility(id) {
		var e = document.getElementById(id);
		if(e.style.display == 'block')
			e.style.display = 'none';
		else
			e.style.display = 'block';
	}
</script>
<link rel="stylesheet" href="setup/setup.css" type="text/css" />

</head>

<body>

<div id="main">

<h1><img src="images/zen-logo.png" title="<?php echo gettext('Zenphoto Setup'); ?>" alt="<?php echo gettext('Zenphoto Setup'); ?>" align="bottom" />
<span><?php echo $upgrade ? gettext("Upgrade") : gettext("Setup") ; ?></span>
</h1>

<div id="content">
<?php
$warn = false;
if (!$setup_checked && zp_loggedin(ADMIN_RIGHTS)) {
	?>
	<p>
		<?php	echo gettext("Welcome to Zenphoto! This page will set up Zenphoto on your web server."); ?>
	</p>
	<h2><?php echo gettext("Systems Check:"); ?></h2>
	<?php

	/*****************************************************************************
	 *                                                                           *
	 *                             SYSTEMS CHECK                                 *
	 *                                                                           *
	 *****************************************************************************/

	global $_zp_conf_vars;
	$good = true;

	?>
<ul>
<?php
if ($connection) {
	//TODO: add each release as it happens
	$zp_versions = array(	'1.2'=>'2213','1.2.1'=>'2635','1.2.2'=>'2983','1.2.3'=>'3427','1.2.4'=>'3716','1.2.5'=>'4022',
												'1.2.6'=>'4335', '1.2.7'=>'4741','1.2.8'=>'4881','1.2.9'=>'5088',
												'1.3.0'=>'5088','1.3.1'=>'5736','1.4'=>'6454','1.4.1-DEV'=>'6506','x.x.x'=>'99999999');
	$c = 0;
	$prev = getOption('zenphoto_release');
	if (!empty($prev)) {
		$release = gettext('Upgrade from before Zenphoto v1.2');
		foreach ($zp_versions as $rel=>$build) {
			if ($build > $prev) {
				break;
			} else {
				$c++;
				$release = sprintf(gettext('Upgrade from Zenphoto v%s'),$rel);
			}
		}
		if ($c == count($zp_versions)-1) {
			$check = 1;
			if ($build != $prev) {
				$release = gettext('Updating current Zenphoto release');
			}
		} else {
			$c = count($zp_versions) - 1 - $c;
			if ($c > 1) {
				$check = -1;
			} else {
				$check = 1;
			}
		}
		checkmark($check,$release,$release.' '.sprintf(ngettext('[%u release skipped]','[%u releases skipped]',$c),$c),gettext('We do not test upgrades that skip releases. We recommend you upgrade in sequence.'));
	}
}

	$required = '4.4.8';
	$desired = '5.2';
	$err = versionCheck($required, $desired, PHP_VERSION);
	if ($err < 0) {
		$good = checkMark($err, sprintf(gettext("PHP version %s"), PHP_VERSION), "", sprintf(gettext('Version %1$s or greater is strongly recommended.'),$desired)) && $good;
	} else {
		if ($err == 0) $err = -1; // make it non-fatal
		$good = checkMark($err, sprintf(gettext("PHP version %s"), PHP_VERSION), "", sprintf(gettext('Version %1$s or greater is required. Use earlier versions at your own risk. Version %2$s or greater is strongly recommended.'),$required, $desired)) && $good;
	}

	$path = dirname(dirname(__FILE__)).'/'.DATA_FOLDER . '/setup_log.txt';
	$permission = fileperms($path)&0777;
	if ($permission & 0077) {
		$p = -1;
	} else {
		$p = true;
	}
	checkMark($p, gettext("Log security"), gettext("Log security [is compromised]"), sprintf(gettext("Zenphoto attempts to make log files accessable by <em>owner</em> only (permissions = 0600). This attempt has failed. The log file permissions are %04o which may allow unauthorized access."),$permission));

	$register_globals = ini_get('register_globals');
	if ($register_globals !== false) {
		$good = checkMark(!$register_globals, gettext('PHP <code>Register Globals</code>'), gettext('PHP <code>Register Globals</code> [is set]'),gettext('PHP Register globals presents a security risk to any PHP application. See <a href="http://php.net/manual/en/security.globals.php"><em>Using Register Globals</em></a>. Zenphoto refuses to operate under these conditions. Change your PHP.ini settings to <code>register_globals = off</code>.')) && $good;
	}
	if (ini_get('safe_mode')) {
		$safe = -1;
	} else {
		$safe = true;
	}
	checkMark($safe, gettext("PHP <code>Safe Mode</code>"), gettext("PHP <code>Safe Mode</code> [is set]"), gettext("Zenphoto functionality is reduced when PHP <code>safe mode</code> restrictions are in effect."));

	primeMark(gettext('Magic_quotes'));
	if (get_magic_quotes_gpc()) {
		$magic_quotes_disabled = -1;
	} else {
		$magic_quotes_disabled = true;
	}
	checkMark($magic_quotes_disabled, gettext("PHP <code>magic_quotes_gpc</code>"), gettext("PHP <code>magic_quotes_gpc</code> [is enabled]"), gettext('You should consider disabling <code>magic_quotes_gpc</code>. For more information See <em><a href="http://www.zenphoto.org/news/troubleshooting-zenphoto#26">What is magic_quotes_gpc and why should it be disabled?</a></em> in the Zenphoto troubleshooting guide.'));
	checkMark($noxlate, gettext('PHP <code>gettext()</code> support'), gettext('PHP <code>gettext()</code> support [is not present]'), gettext("Localization of Zenphoto requires native PHP <code>gettext()</code> support"));
	if ($_zp_setupCurrentLocale_result === false) {
		checkMark(-1, gettext('PHP <code>setlocale()</code>'), ' '.gettext('PHP <code>setlocale()</code> failed'), gettext("Locale functionality is not implemented on your platform or the specified locale does not exist. Language translation may not work.").'<br />'.gettext('See the <a  href="http://www.zenphoto.org/news/troubleshooting-zenphoto#24">troubleshooting guide</a> on zenphoto.org for details.'));
	}
	primeMark(gettext('mb_strings'));
	if (function_exists('mb_internal_encoding')) {
		@mb_internal_encoding('UTF-8');
		if (($charset = mb_internal_encoding()) == 'UTF-8') {
			$mb = 1;
		} else {
			$mb = -1;
		}
		$m2 = gettext('Setting <em>mbstring.internal_encoding</em> to <strong>UTF-8</strong> in your <em>php.ini</em> file is recommended to insure accented and multi-byte characters function properly.');
		checkMark($mb, gettext("PHP <code>mbstring</code> package"), sprintf(gettext('PHP <code>mbstring</code> package [Your internal character set is <strong>%s</strong>]'), $charset), $m2);
	} else {
		$test = $_zp_UTF8->convert('test', 'ISO-8859-1', 'UTF-8');
		if (empty($test)) {
			$m2 = gettext("You need to install the <code>mbstring</code> package or correct the issue with <code>iconv(()</code>");
			checkMark(0, '', gettext("PHP <code>mbstring</code> package [is not present and <code>iconv()</code> is not working]"), $m2);
		} else {
			$m2 = gettext("Strings generated internally by PHP may not display correctly. (e.g. dates)");
			checkMark(-1, '', gettext("PHP <code>mbstring</code> package [is not present]"), $m2);
		}
	}

	if ($environ) {
		/* Check for graphic library and image type support. */
		primeMark(gettext('Graphics library'));
		if (function_exists('zp_graphicsLibInfo')) {
			$graphics_lib = zp_graphicsLibInfo();
			if (array_key_exists('Library_desc',$graphics_lib)) {
				$library = $graphics_lib['Library_desc'];
			} else {
				$library = '';
			}
			$good = checkMark(!empty($library), sprintf(gettext("Graphics support: <code>%s</code>"),$library), gettext('Graphics support [is not installed]'), gettext('You need to install a graphics support library such as the <em>GD library</em> in your PHP')) && $good;
			if (!empty($library)) {
				$missing = array();
				if (!isset($graphics_lib['JPG'])) { $missing[] = 'JPEG'; }
				if (!(isset($graphics_lib['GIF']))) { $missing[] = 'GIF'; }
				if (!(isset($graphics_lib['PNG']))) { $missing[] = 'PNG'; }
				if (count($missing) > 0) {
					if (count($missing) < 3) {
						if (count($missing) == 2) {
							$imgmissing =sprintf(gettext('Your PHP graphics library does not support %1$s or %2$s'),$missing[0],$missing[1]);
						} else {
							$imgmissing = sprintf(gettext('Your PHP graphics library does not support %1$s'),$missing[0]);
						}
						$err = -1;
						$mandate = gettext("To correct this you should install a graphics library with appropriate image support in your PHP");
					} else {
						$imgmissing = sprintf(gettext('Your PHP graphics library does not support %1$s, %2$s, or %3$s'),$missing[0],$missing[1],$missing[2]);
						$err = 0;
						$good = false;
						$mandate = gettext("To correct this you need to install GD with appropriate image support in your PHP");
					}
					checkMark($err, gettext("PHP graphics image support"), '', $imgmissing.
											"<br />".gettext("The unsupported image types will not be viewable in your albums.").
											"<br />".$mandate);
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
			checkmark(0, '', gettext('Graphics support [configuration error]'), gettext('No Zenphoto image handling library was loaded. Be sure that your PHP has a graphics support.').' '.trim($graphicsmsg));
		}
	}

	if (file_exists(CONFIGFILE)) {
		require(CONFIGFILE);
		$cfg = true;
	} else {
		$cfg = false;
	}

	$curdir = getcwd();
	chdir(dirname(__FILE__));

	// Important. when adding new database support this switch may need to be extended,
	$engines = array();
	foreach (setup_glob('functions-db-*.php') as $key=>$engineMC) {
		$engineMC = substr($engineMC,13,-4);
		$engine = strtolower($engineMC);
		if (extension_loaded($engine)) {
			switch ($engine) {
				case 'pdo_mysql':
					if (version_compare(PHP_VERSION,'5.0.0') == 1) {
						$engines[$engineMC] = array('user'=>true,'pass'=>true,'host'=>true,'path'=>false,'name'=>true,'prefix'=>true);
					}
					break;
				case 'mysql':
				default:
					$engines[$engineMC] = array('user'=>true,'pass'=>true,'host'=>true,'path'=>false,'name'=>true,'prefix'=>true);
					break;
				case 'pdo_sqlite':
					if (version_compare(PHP_VERSION,'5.0.0') == 1) {
						$engines[$engineMC] = array('user'=>false,'pass'=>false,'host'=>false,'path'=>true,'name'=>true,'prefix'=>true,'experimental'=>gettext('SQLite is a limited database and does not support all Zenphoto functionality. Use of this database is for <em>Database abstraction</em> proof of concept only.'));
					}
					break;
			}
		} else {
			$engines[$engineMC] = false;
		}
	}
	chdir($curdir);
	primeMark(gettext('Database'));
	foreach ($engines as $engine=>$enabled) {
		if ($engine == $selected_database) {
			if ($enabled && isset($enabled['experimental'])) {
				$good = checkMark(-1, $engine, sprintf(gettext('PHP <code>%s support</code> for configured Database [is experimental]'),$engine), $enabled['experimental']) && $good;
			} else {
				$good = checkMark($enabled, sprintf(gettext('PHP <code>%s</code> support for configured Database'),$engine), sprintf(gettext('PHP <code>%s support</code> for configured Database [is not installed]'),$engine), sprintf(gettext('Choose a different database engine or install %s support in your PHP to clear this condition.'),$engine)) && $good;
			}
		} else {
			if ($enabled) {
				if (isset($enabled['experimental'])){
					?>
					<li class="note_warn"><?php echo sprintf(gettext(' <code>%1$s</code> support (<a onclick="$(\'#%1$s\').toggle(\'show\')" >experimental</a>)'),$engine); ?></li>
					<p class="warning" id="<?php echo $engine; ?>" style="display:none;" ><?php echo $enabled['experimental']?></p>
					<?php
				} else {
					?>
					<li class="note_ok"><?php echo sprintf(gettext('PHP <code>%s</code> support'),$engine); ?></li>
					<?php
				}
			} else {
				?>
				<li class="note_exception"><?php echo sprintf(gettext('PHP <code>%s</code> support [is not installed]'),$engine); ?></li>
				<?php
			}
		}
	}

	$good = checkMark($cfg, gettext('<em>zp-config.php</em> file'), gettext('<em>zp-config.php</em> file [does not exist]'),
							sprintf(gettext('Setup was not able to create this file. You will need to edit the <code>zp-config.php.source</code> file as indicated in the file\'s comments and rename it to <code>zp-config.php</code>.'.
							' Place the file in the %s folder.'),DATA_FOLDER).
							sprintf(gettext('<br /><br />You can find the file in the "%s" directory.'),ZENFOLDER)) && $good;
	if ($cfg) {
		primeMark(gettext('File/Folder permissions'));
		if (zp_loggedin(ADMIN_RIGHTS)) {
			$chmodselector = '<form action="#"><input type="hidden" name="xsrfToken" value="'.$xsrftoken.'" />'.
													'<p>'.sprintf(gettext('Set File/Folder permissions to %s.'),permissionsSelector($permission_names, $chmod)).
													'</p></form>';
		} else {
			$chmodselector = '<p>'.gettext('You must be logged in to change permissions.').'</p>';
		}
		if (array_key_exists($chmod, $permission_names)) {
			$value = sprintf(gettext('<em>%1$s</em> (<code>0%2$o</code>)'),$permission_names[$chmod],$chmod);
		} else {
			$value = sprintf(gettext('<em>unknown</em> (<code>%o</code>)'),$chmod);
		}
		if ($chmod>0755) {
			if (isset($_zp_conf_vars['CHMOD'])) {
				$severity = -3;
			} else {
				$severity = -1;
			}
		} else {
			$severity = -2;
		}
		$msg = sprintf(gettext('File/Folder Permissions [are %s]'),$value);
		checkMark($severity, $msg, $msg,
								'<p>'.gettext('If file and folder permissions are not set to <em>strict</em> or tighter there could be a security risk. However, on some servers Zenphoto does not function correctly with tight file/folder permissions. If Zenphoto has permission errors, run setup again and select a more relaxed permission.').'</p>'.
								$chmodselector);

		if (zp_loggedin(ADMIN_RIGHTS)) {
			if ($environ) {
				primeMark(gettext('Character set'));
				$charset_defined = str_replace('-','&#8209;',FILESYSTEM_CHARSET);
				$file_t = 'charset.tést';
				$charset = LOCAL_CHARSET;
				if (empty($charset)) {
					$charset = 'UTF-8';
				}
				$test = '';
				if (($dir=opendir(dirname(dirname(__FILE__)).'/'.DATA_FOLDER.'/')) !== false) {
					$testfiles=array();
					while(($file=readdir($dir)) !== false) {
						if (strpos($file,'charset.') === 0) {
							$test = $file;
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
				$os = getServerOS();
				if ($os =='darwin') {
					if ($tries & 4) {
						$test = $file_t;
					} else {
						$trialset = 'UTF-8';
						$tries = 4;
					}
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
				$msg2 = '<p>'.sprintf(gettext('If your server filesystem character set is different from <code>%s</code> and you create album or image filenames names containing characters with diacritical marks you may have problems displaying the names.'),$charset_defined).'</p>'.
								'<form action="#"><input type="hidden" name="xsrfToken" value="'.$xsrftoken.'" /><input type="hidden" name="charset_attempts" value="'.$tries.'" /><p>'.
								gettext('Change the filesystem character set define to %1$s.<br />If you do not know the character set try "%2$s"').
								'</p></form><br clear="all" />';

				$selectedset = FILESYSTEM_CHARSET;
				if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
					$selectedset = $_zp_conf_vars['FILESYSTEM_CHARSET'];
				} else {
					$selectedset = FILESYSTEM_CHARSET;
				}
				$msg = '';
				if ($test) {
					//	fount the test file
					if ((filesystemToInternal(trim($test)) == $file_t)) {
						//	and the active character set define worked
						$notice = 1;
						if ($os == 'darwin') {
							$confirm = gettext('Mac');
						} else {
							$confirm = gettext('confirmed');
						}
						$msg = sprintf(gettext('The Zenphoto filesystem character define is %1$s [%2$s]'),$charset_defined, $confirm);
						$msg1 = '';
					} else {
						if ($selectedset=='unknown') {
							$notice = 1;
							$msg = gettext('The Zenphoto filesystem character define is ISO-8859-1 [assumed]');
							$msg1= '';
						} else {
							//	active character set is not correct
							$notice = 0;
							$msg1 = sprintf(gettext('The Zenphoto filesystem character define is %1$s [which seems wrong]'),$charset_defined);
						}
					}
				} else {
					//	no test file
					$msg1 = sprintf(gettext('The Zenphoto filesystem character define is %1$s [no test performed]'),$charset_defined);
					$msg2 = '<p>'.sprintf(gettext('Setup did not perform a test of the filesystem character set. You can cause setup to test for a proper definition by creating a file in your <code>%1$s</code> folder named <strong><code>%2$s</code></strong> and re-running setup.'),DATA_FOLDER,$file_t).'</p>'.$msg2;
					if (isset($_zp_conf_vars['FILESYSTEM_CHARSET'])) {
						//	but we have a define value
						$notice = -3;
					} else {
						//	no defined value, who knows....
						$notice = -1;
					}
				}
				checkMark($notice, $msg, $msg1, sprintf($msg2,charsetSelector($trialset),$trialset));
				// UTF-8 URI
				if (($notice != -1) && @copy(SERVERPATH.'/'.ZENFOLDER.'/images/pass.png', SERVERPATH.'/'.DATA_FOLDER.'/'.internalToFilesystem('tést.jpg'))) {
					$test_image = WEBPATH.'/'.DATA_FOLDER.'/'.urlencode('tést.jpg');
					$req_iso = gettext('Image URIs appear require the <em>filesystem</em> character set.');
					$req_UTF8 = gettext('Image URIs appear to require the UTF-8 character set.');
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
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
										$('#UTF8_uri_warn').html('<?php echo gettext('You should enable the URL option <em>UTF8 image URIs</em>.'); ?>'+' <?php echo gettext('<a href="javascript:uri(true)">Please do</a>'); ?>');
										$('#UTF8_uri_warn').show();
										<?php
									}
									?>
							};
							image.onerror = function() {
								$('#UTF8_uri_text').html('<?php echo $req_iso; ?>');
								<?php
								if (UTF8_IMAGE_URI) {
									?>
									$('#UTF8_uri').attr('class','warn');
									$('#UTF8_uri_warn').html('<?php echo gettext('You should disable the URL option <em>UTF8 image URIs</em>.'); ?>'+' <?php echo gettext('<a href="javascript:uri(false)">Please do</a>'); ?>');
									$('#UTF8_uri_warn').show();
									<?php
								}?>
							};
							image.src = '<?php echo $test_image; ?>';


						});
						// ]]> -->
					</script>
					<li id="UTF8_uri" class="pass" >
						<span id="UTF8_uri_text">
							<?php echo $req_UTF8; ?>
						</span>
						<div id="UTF8_uri_warn" class="warning"  style="display:none" >
							<h1><?php echo gettext('Warning!'); ?></h1>
							<span id="UTR8_uri_warn"></span>
						</div>
					</li>
					<?php
				}
			}
		}

	}
	$db = $connection = db_connect(false);
	if (!$db) {
		$connectDBErr = db_error();
	}
	if ($connection) {
		$dbsoftware = db_software();
		$dbapp = $dbsoftware['application'];
		$dbversion = $dbsoftware['version'];
		$required = $dbsoftware['required'];
		$desired = $dbsoftware['desired'];
		$sqlv = versionCheck($required, $desired, $dbversion);;
	}
	primeMark(gettext('Database connection'));
	if ($cfg) {
		@chmod(CONFIGFILE, 0666 & $chmod);
		if (($adminstuff = !$engines[$selected_database] || !$connection  || !$db) && is_writable(CONFIGFILE)) {
			$good = checkMark(false, '', gettext("Database setup in <em>zp-config.php</em>"), $connectDBErr) && $good;
			// input form for the information
			include(dirname(__FILE__).'/setup/setup-sqlform.php');
		} else {
			if ($connectDBErr) {
				$msg = $connectDBErr;
			} else {
				$msg = gettext("You have not correctly set your <strong>Database</strong> <code>user</code>, <code>password</code>, etc. in your <code>zp-config.php</code> file and <strong>setup</strong> is not able to write to the file.");
			}
			$good = checkMark(!$adminstuff, gettext("Database setup in <em>zp-config.php</em>"), '',$msg) && $good;
		}
	} else {
		$good = checkMark($connection, sprintf(gettext('Connect to %s'),$selected_database), gettext("Connect to Database [<code>CONNECT</code> query failed]"), $connectDBErr) && $good;
	}
	if (!$newconfig && !$connection) {
		if (empty($_zp_conf_vars['mysql_host']) || empty($_zp_conf_vars['mysql_user']) || empty($_zp_conf_vars['mysql_pass'])) {
			$connectDBErr = gettext('Check the <code>user</code>, <code>password</code>, and <code>database host</code> and try again.'); // of course we can't connect!
		}
	}
	if ($connection) {
		$good = checkMark($sqlv, sprintf(gettext('%1$s version %2$s'),$dbapp,$dbversion), "", sprintf(gettext('Version %1$s or greater is required. Use a lower version at your own risk.<br />Version %2$s or greater is preferred.'),$required,$desired)) && $good;
		if ($DBcreated || !empty($connectDBErr)) {
			if (empty($connectDBErr)) {
				$severity = 1;
			} else {
				$severity = 0;
			}
			checkMark($severity, sprintf(gettext('Database <code>%s</code> created'), $_zp_conf_vars['mysql_database']),sprintf(gettext('Database <code>%s</code> not created [<code>CREATE DATABASE</code> query failed]'), $_zp_conf_vars['mysql_database']),$connectDBErr);
		}
		if (empty($_zp_conf_vars['mysql_database'])) {
			$good = checkmark(0, '', gettext('Connect to the database [You have not provided a database name]'),gettext('Provide the name of your database in the form above.'));
		} else {
			if ($oktocreate) {
				$good = checkmark(0, '', gettext('Connect to the database [Database does not exist]'),sprintf(gettext('Click here to attempt to create <a href="?Create_Database" >%s</a>.'),$_zp_conf_vars['mysql_database']));
			}
		}
		if ($environ) {
			if ($sqlv == 0) $sqlv = -1; // make it non-fatal
			$good = checkMark($sqlv, gettext("Connect to the database"), '',
								sprintf(gettext('Could not access the <strong>%1$s</strong> database (<code>%2$s</code>).'), $selected_database, $_zp_conf_vars['mysql_database']).' '.
								gettext("Check the <code>user</code>, <code>password</code>, <code>database name</code>, and <code>Database host</code>.").'<br />' .
								sprintf(gettext("Make sure the database has been created and that <code>%s</code> has access to it."),$_zp_conf_vars['mysql_user'])) && $good;


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
						checkMark($err, gettext('<code>SQL mode</code>'), sprintf(gettext('<code>SQL mode</code> [is set to <em>%s</em>]'),$mode), gettext('Consider setting it <em>empty</em> if you get Database errors.'));
					}
				}
			} else {
				checkMark(-1, '', gettext('<code>SQL mode</code> [SET SESSION failed]'), $msg);
			}

			$dbn = "`".$_zp_conf_vars['mysql_database']. "`.*";
			$db_results = db_permissions();

			$access = -1;
			$rightsfound = 'unknown';
			$rightsneeded = array(gettext('Select')=>'SELECT',gettext('Create')=>'CREATE',gettext('Drop')=>'DROP',gettext('Insert')=>'INSERT',
														gettext('Update')=>'UPDATE',gettext('Alter')=>'ALTER',gettext('Delete')=>'DELETE',gettext('Index')=>'INDEX');
			ksort($rightsneeded);
			$neededlist = '';
			foreach ($rightsneeded as $right=>$value) {
				$neededlist .= '<code>'.$right.'</code>, ';
			}
			$neededlist = substr($neededlist, 0, -2).' ';
			$i = strrpos($neededlist, ',');
			$neededlist = substr($neededlist, 0, $i).' '.gettext('and').substr($neededlist, $i+1);
			if ($db_results) {
				$report = "<br /><br /><em>".gettext("Grants found:")."</em> ";
				foreach ($db_results as $row) {
					$row_report = "<br /><br />".$row;
					$r = str_replace(',', '', $row);
					$i = strpos($r, "ON");
					$j = strpos($r, "TO", $i);
					$found = stripslashes(trim(substr($r, $i+2, $j-$i-2)));
					if ($partial = (($i = strpos($found, '%')) !== false)) {
						$found = substr($found, 0, $i);
					}
					$rights = array_flip(explode(' ', $r));
					$rightsfound = 'insufficient';
					if (($found == $dbn) || ($found == "*.*") || $partial && preg_match('/^'.$found.'/', $dbn)) {
						$allow = true;
						foreach ($rightsneeded as $key=>$right) {
							if (!isset($rights[$right])) {
								$allow = false;
							}
						}
						if (isset($rights['ALL']) || $allow) {
							$access = 1;
						}
						$report .= '<strong>'.$row_report.'</strong>';
					} else {
						$report .= $row_report;
					}
				}
			} else {
				$report = "<br /><br />".gettext("The <em>SHOW GRANTS</em> query failed.");
			}
			checkMark($access, gettext("Database <code>access rights</code>"), sprintf(gettext("Database <code>access rights</code> [%s]"),$rightsfound),
													sprintf(gettext("Your Database user must have %s rights."),$neededlist) . $report);


			$result = db_show('tables');
			$tableslist = '';
			if ($result) {
				$check = 1;
				while ($row = db_fetch_row($result)) {
					$tableslist .= "<code>" . $row[0] . "</code>, ";
				}
			} else {
				$check = -1;
			}
			if (empty($tableslist)) {
				$msg = gettext('<em>SHOW TABLES</em> [found no tables]');
				$msg2 = '';
			} else {
				$msg = sprintf(gettext("<em>SHOW TABLES</em> found: %s"),substr($tableslist, 0, -2));
				$msg2 = '';
			}
			$dbn = $_zp_conf_vars['mysql_database'];
			checkMark($check, $msg, gettext("<em>SHOW TABLES</em> [Failed]"), sprintf(gettext("The database did not return a list of the database tables for <code>%s</code>."),$dbn) .
											"<br />".gettext("<strong>Setup</strong> will attempt to create all tables. This will not over write any existing tables."));

			if (isset($_zp_conf_vars['UTF-8']) && $_zp_conf_vars['UTF-8']) {
				$fields = 0;
				$fieldlist = array();
				if (strpos($tableslist,$_zp_conf_vars['mysql_prefix'].'images') !== false) {
					$columns = db_list_fields('images');
					if ($columns) {
						foreach ($columns as $col=>$utf8) {
							if (!is_null($row['Collation']) && $row['Collation'] != 'utf8_unicode_ci') {
								$fields = $fields | 1;
								$fieldlist[] = '<code>images->'.$col.'</code>';
							}
						}
					} else {
						$fields = 4;
					}
				}
				if (strpos($tableslist,$_zp_conf_vars['mysql_prefix'].'albums') !== false) {
					$columns = db_list_fields('albums');
					if ($columns) {
						foreach ($columns as $col=>$utf8) {
							if (!is_null($row['Collation']) && $row['Collation'] != 'utf8_unicode_ci') {
								$fields = $fields | 2;
								$fieldlist[] = '<code>albums->'.$col.'</code>';
							}
						}
					} else {
						$fields = 4;
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
				checkmark($err, gettext('Database <code>field collations</code>'), $msg2, sprintf(ngettext('%s is not UTF-8. You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code>','%s are not UTF-8. You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code>',count($fieldlist)),implode(', ',$fieldlist)));
			} else {
				checkmark(-1, '', gettext('Database <code>$conf["UTF-8"]</code> [is not set <em>true</em>]'), gettext('You should consider porting your data to UTF-8 and changing the collation of the database fields to <code>utf8_unicode_ci</code> and setting this <em>true</em>. Zenphoto works best with pure UTF-8 encodings.'));
			}
		}
	}

	set_time_limit(120);
	$base = str_replace('\\', '/', dirname(dirname(__FILE__))).'/';
	getResidentZPFiles(SERVERPATH.'/'.ZENFOLDER);
	$res = array_search($base.ZENFOLDER.'/Zenphoto.package',$_zp_resident_files);
	unset($_zp_resident_files[$res]);
	$permissions = 1;
	$cum_mean = filemtime(SERVERPATH.'/'.ZENFOLDER.'/version.php');
	$hours = 3600;
	$lowset = $cum_mean - $hours;
	$highset = $cum_mean + $hours;

	primeMark(gettext('Zenphoto files'));
	$package_file_count = false;
	if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/Zenphoto.package')) {
		$package = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/Zenphoto.package');
		if (!empty($package)) {
			$installed_files = explode("\n", trim($package));
			$count = array_pop($installed_files);
			$package_file_count = is_numeric($count) && ($count > 0) && ($count == count($installed_files));
		}
	}
	if (!$package_file_count) {
		checkMark(-1, '', gettext("Zenphoto package [missing]"), gettext('The file <code>Zenphoto.package</code> is either missing, not readable, or defective. Your installation may be corrupt!'));
		$installed_files = array();
	}
	$folders = array();
	$zenphoto_themes = array();
	foreach ($installed_files as $key=>$value) {
		$component_data = explode(':',$value);
		$value = trim($component_data[0]);
		if (count($component_data) > 1) {
			$fromPackage = trim($component_data[1]);
		} else {
			$fromPackage = '';
		}
		$component = $base.$value;
		if (file_exists($component)) {
			$res = array_search($component,$_zp_resident_files);
			if ($res !== false) {
				unset($_zp_resident_files[$res]);
			}
			if (is_dir($component)) {
				if ($updatechmod) {
					@chmod($component,$chmod);
					clearstatcache();
					if ($permissions==1 && ($perms = fileperms($component)&0777)!=$chmod) {
						if (($perms&0754) == 0754) { // could not set them, but they will work.
							$permissions = -1;
						} else {
							$permissions = 0;
						}
					}
				}
				$folders[$component] = $component;
				unset($installed_files[$key]);
				if (dirname($value) == THEMEFOLDER) {
					$zenphoto_themes[] = basename($value);
					getResidentZPFiles($base.$value);
				}
			} else {
				if ($updatechmod) {
					@chmod($component,0666&$chmod);
					clearstatcache();
					if ($permissions==1 && ($perms = fileperms($component)&0777)!=($chmod & 0666)) {
						if (($perms&0644) == 0644) { // could not set them, but they will work.
							$permissions = -1;
						} else {
							$permissions = 0;
						}
					}
				}

				$t = filemtime($component);
				if ((defined('RELEASE') && ($t < $lowset || $t > $highset))) {
					$installed_files[$key] = $value;
				} else {
					unset($installed_files[$key]);
				}
			}
		}
	}
	if (count($folders)>0) {
		foreach ($folders as $key=>$folder) {
			if ((fileperms($folder)&0777) != 0755) { // need to set them?.
				@chmod($folder, $chmod);
				clearstatcache();
				if ($permissions==1 && ($perms = fileperms($folder)&0777)!=$chmod) {
					if (($perms&0755) != 0755) { // could not set them, but they will work.
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
	foreach ($installed_files as $key=>$component) {
		$folders = explode('/',$component);
		$folder = array_shift($folders);
		switch ($folder) {
			case ALBUMFOLDER:
			case CACHEFOLDER:
			case DATA_FOLDER:
			case UPLOAD_FOLDER:
				unset($installed_files[$key]);
				break;
			case 'plugins':
				$plugin_subfolders[] = implode('/',$folders);
				unset($installed_files[$key]); // this will be taken care of later
				break;
			case 'cache_html':
				$Cache_html_subfolders[] = implode('/',$folders);
				unset($installed_files[$key]);
				break;
		}
	}
	$filelist = '';
	foreach ($installed_files as $extra) {
		$filelist .= filesystemToInternal(str_replace($base,'',$extra).'<br />');
	}
	if (count($installed_files) > 0) {
		if (!defined("RELEASE")) {
			$msg1 = gettext("Zenphoto core files [This is not an official build. Some files are missing or seem wrong]");
		} else {
			$msg1 = gettext("Zenphoto core files [Some files are missing or seem wrong]");
		}
		$msg2 = gettext('Perhaps there was a problem with the upload. You should check the following files: ').'<br /><code>'.substr($filelist,0,-6).'</code>';
		$mark = -1;
	} else {
		if (!defined("RELEASE")) {
			$mark = -1;
			$msg1 = gettext("Zenphoto core files [This is not an official build]");
		} else {
			$msg1 = '';
			$mark = 1;
		}
		$msg2 = '';
	}
	checkMark($mark, gettext("Zenphoto core files"), $msg1, $msg2);
	if (zp_loggedin(ADMIN_RIGHTS) && $connection) {
		primeMark(gettext('Installation files'));
		$systemlist = $filelist = array();
		$svncount = 0;
		foreach ($_zp_resident_files as $extra) {
			if (defined("RELEASE") || (strpos($extra, '.svn')===false)) {
				$systemlist[] = $extra;
				$filelist[] = $_zp_UTF8->convert(str_replace($base,'',$extra), FILESYSTEM_CHARSET, 'UTF-8');
			} else {
				$svncount++;
			}
		}
		if ($svncount) {
			$svn = '<br />'.sprintf(gettext('.svn [%s instances]'),$svncount);
		} else {
			$svn = '';
		}
		if ($package_file_count) {	//	no point in this if the package list was damaged!
			if (!empty($filelist)) {
				if (isset($_GET['delete_extra'])) {
					foreach ($systemlist as $key=>$file) {
						if (!is_dir($file)) {
							if (setupDeleteComponent(@unlink($file),$filelist[$key])) {
								unset($filelist[$key]);
								unset($systemlist[$key]);
							}
						}
					}
					foreach ($systemlist as $key=>$file) {
						if (is_dir($file)) {
							if (setupDeleteComponent(@rmdir($file),$filelist[$key].'/')) {
								unset($filelist[$key]);
							}
						}
					}

					if (!empty($filelist)) {
						checkmark(-1,'',gettext('Zenphoto core folders [Some unknown files were found]'),gettext('The following files could not be deleted.').'<br /><code>'.implode('<br />',$filelist).'<code>');
					}
				} else {
					checkMark(-1, '', gettext('Zenphoto core folders [Some unknown files were found]'),
					gettext('You should remove the following files: ').'<br /><code>'.implode('<br />',$filelist).$svn.
															'</code><p class="buttons"><a href="?delete_extra'.($debug?'&amp;debug':'').'">'.gettext("Delete extra files").'</a></p><br clear="all" /><br clear="all" />');
				}
			}
			checkMark($permissions, gettext("Zenphoto core file permissions"), gettext("Zenphoto core file permissions [not correct]"), gettext('Setup could not set the one or more components to the selected permissions level. You will have to set the permissions manually. See the <a href="http://www.zenphoto.org/news/troubleshooting-zenphoto#29">Troubleshooting guide</a> for details on Zenphoto permissions requirements.'));
		}
	}
	$msg = gettext("<em>.htaccess</em> file");
	$Apache = stristr($_SERVER['SERVER_SOFTWARE'], "apache");
	$htfile = '../.htaccess';
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
		if ($Apache) {
			$desc = gettext('If you have the mod_rewrite module enabled an <em>.htaccess</em> file is required the root zenphoto folder to create cruft-free URLs.').
						'<br /><br />'.gettext('You can ignore this warning if you do not intend to set the <code>mod_rewrite</code> option.');
			if (zp_loggedin(ADMIN_RIGHTS)) $desc .= ' '.gettext('<p class="buttons"><a href="?copyhtaccess" >Make setup create the file</a></p><br style="clear:both" /><br />');
		} else {
			$desc = gettext("Server seems not to be Apache or Apache-compatible, <code>.htaccess</code> not required.");
		}
	} else {
		$i = strpos($htu, 'VERSION');
		if ($i !== false) {
			$j = strpos($htu, ";");
			$vr = trim(substr($htu, $i+7, $j-$i-7));
		}
		$ch = !empty($vr) && ($vr == HTACCESS_VERSION);
		$d = str_replace('\\','/',dirname(dirname($_SERVER['SCRIPT_NAME'])));
		if (!$ch) {	// wrong version
			$oht = trim(@file_get_contents('oldhtaccess'));
			//fix the rewritebase
			$i = strpos($ht, 'RewriteBase');
			$j = strpos($ht, "\n", $i+11);
			$oht = substr($oht, 0, $i) . "RewriteBase $d" . substr($oht, $j);
			if ($oht == $ht) {	// an unmodified .htaccess file, we can just replace it
				@unlink($htfile);
				$ch = @copy('htaccess', dirname(dirname(__FILE__)).'/.htaccess');
			}
		}
		if (!$ch) {
			if (!$Apache) {
				$desc = gettext("Server seems not to be Apache or Apache-compatible, <code>.htaccess</code> not required.");
				$ch = -1;
			} else {
				$desc = sprintf(gettext("The <em>.htaccess</em> file in your root folder is not the same version as the one distributed with this version of Zenphoto. If you have made changes to <em>.htaccess</em>, merge those changes with the <em>%s/htaccess</em> file to produce a new <em>.htaccess</em> file."),ZENFOLDER);
				if (zp_loggedin(ADMIN_RIGHTS)) {
					$desc .= ' '.gettext('Click <a href="?copyhtaccess" >here</a> to have setup replace your <em>.htaccess</em> file with the current version.');
				}
			}
			$err = gettext("<em>.htaccess</em> file [wrong version]");
		}
	}

	$mod = '';
	$rw = '';
	if ($ch > 0) {
		$i = strpos($htu, 'REWRITEENGINE');
		if ($i === false) {
			$rw = '';
		} else {
			$j = strpos($htu, "\n", $i+13);
			$rw = trim(substr($htu, $i+13, $j-$i-13));
		}
		if (!empty($rw)) {
			$msg = sprintf(gettext("<em>.htaccess</em> file (<em>RewriteEngine</em> is <strong>%s</strong>)"), $rw);
			$mod = "&amp;mod_rewrite=$rw";
		}
	}
	$good = checkMark($ch, $msg, $err, $desc) && $good;

	$base = true;
	$f = '';
	if ($rw == 'ON') {
		$i = strpos($htu, 'REWRITEBASE', $j);
		if ($i === false) {
			$base = false;
			$b = '';
			$err = gettext("<em>.htaccess</em> RewriteBase [is <em>missing</em>]");
			$i = $j+1;
		} else {
			$j = strpos($htu, "\n", $i+11);
			$bs = trim(substr($ht, $i+11, $j-$i-11));
			$base = ($bs == $d);
			$b = sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code>"), $bs);
			$err = sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code> [Does not match install folder]"), $bs);
		}
		$f = '';
		if (!$base) { // try and fix it
			@chmod($htfile, 0666&$chmod);
			if (is_writeable($htfile)) {
				$ht = substr($ht, 0, $i) . "RewriteBase $d\n" . substr($ht, $j+1);
				if ($handle = fopen($htfile, 'w')) {
					if (fwrite($handle, $ht)) {
						$base = true;
						$b =  sprintf(gettext("<em>.htaccess</em> RewriteBase is <code>%s</code> (fixed)"), $d);
						$err =  '';
					}
					fclose($handle);
					clearstatcache();
				}
			}
		}
		$good = checkMark($base, $b, $err,
											gettext("Setup was not able to write to the file change RewriteBase match the install folder.") .
											"<br />".sprintf(gettext("Either make the file writeable or set <code>RewriteBase</code> in your <code>.htaccess</code> file to <code>%s</code>."),$d)) && $good;
	}
	//robots.txt file
	$robots = file_get_contents(dirname(__FILE__).'/example_robots.txt');
	if ($robots === false) {
		checkmark(-1, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not find the  <em>example_robots.txt</em> file.'));
	} else {
		if (file_exists(dirname(dirname(__FILE__)).'/robots.txt')) {
			checkmark(-2, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), '<p>'.gettext('Setup did not create a <em>robots.txt</em> file because one already exists.').'</p>');
		} else {
			$text = explode('****delete all lines above and including this one *******'."\n", $robots);
			$d = dirname(dirname($_SERVER['SCRIPT_NAME']));
			if ($d == '/') $d = '';
			$robots = str_replace('/zenphoto', $d, $text[1]);
			$rslt = file_put_contents(dirname(dirname(__FILE__)).'/robots.txt', $robots);
			if ($rslt === false) {
				$rslt = -1;
			} else {
				$rslt = 1;
			}
			checkmark($rslt, gettext('<em>robots.txt</em> file'), gettext('<em>robots.txt</em> file [Not created]'), gettext('Setup could not create a <em>robots.txt</em> file.'));
		}
	}

	if (isset($_zp_conf_vars['external_album_folder']) && !is_null($_zp_conf_vars['external_album_folder'])) {
		checkmark(-1, 'albums', gettext("albums [<code>\$conf['external_album_folder']</code> is deprecated]"), gettext('You should update your zp-config.php file to conform to the current zp-config.php.example file.'));
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
				$albumfolder = str_replace('\\', '/', dirname(dirname(__FILE__))) . $albumfolder;
				break;
			case 'in_webpath':
				$root = str_replace('\\', '/', dirname(dirname(__FILE__)));
				if (!empty($const_webpath)) {
					$root = str_replace('\\', '/', dirname($root));
				}
				$albumfolder = $root . $albumfolder;
				break;
		}
		$good = folderCheck('albums', $albumfolder, $_zp_conf_vars['album_folder_class']) && $good;
	} else {
		checkmark(-1, gettext('<em>albums</em> folder'), gettext("<em>albums</em> folder [The line <code>\$conf['album_folder']</code> is missing from your zp-config.php file]"), gettext('You should update your zp-config.php file to conform to the current zp-config.php.example file.'));
	}

	$good = folderCheck('cache', dirname(dirname(__FILE__)) . "/cache/", 'std') && $good;
	$good = checkmark(file_exists($en_US), gettext('<em>locale</em> folders'), gettext('<em>locale</em> folders [Are not complete]'), gettext('Be sure you have uploaded the complete Zenphoto package. You must have at least the <em>en_US</em> folder.')) && $good;
	$good = folderCheck(gettext('uploaded'), dirname(dirname(__FILE__)) . "/uploaded/", 'std') && $good;
	$good = folderCheck(DATA_FOLDER, dirname(dirname(__FILE__)) . '/'.DATA_FOLDER.'/', 'std') && $good;
	$good = folderCheck(gettext('HTML cache'), dirname(dirname(__FILE__)) . '/cache_html/', 'std', true, $Cache_html_subfolders) && $good;
	$good = folderCheck(gettext('Third party plugins'), dirname(dirname(__FILE__)) . '/'.USER_PLUGIN_FOLDER.'/', 'std', false, $plugin_subfolders) && $good;

	?>
</ul>
<?php

	if ($good) {
		$dbmsg = "";
	} else {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			?>
			<div class="error">
				<?php echo gettext("You need to address the problems indicated above then run <code>setup.php</code> again."); ?>
			</div>
			<p class='buttons'>
				<a href="?refresh" title="<?php echo gettext("Setup failed."); ?>" style="font-size: 15pt; font-weight: bold;">
					<img src="images/fail.png" alt=""/> <?php echo gettext("Refresh"); ?>
				</a>
			</p>
			<br clear="all" /><br clear="all" />
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
			?>
			<div>
			<?php
			setupLanguageSelector();
			?>
			</div>
			<?php
		}
		echo "\n</div><!-- content -->";
		echo "\n</div><!-- main -->";
		printadminfooter();
		echo "</body>";
		echo "</html>";
		exit();
	}
} else {
	$dbmsg = gettext("database connected");
} // system check
if (file_exists(CONFIGFILE)) {

	require(CONFIGFILE);
	require_once(dirname(__FILE__).'/functions.php');
	$task = '';
	if (isset($_GET['create'])) {
		$task = 'create';
		$create = array_flip(explode(',', sanitize($_GET['create'])));
	}
	if (isset($_GET['update'])) {
		$task = 'update';
	}

	if (db_connect() && empty($task)) {
		$result = db_show('tables');
		$tables = array();
		$prefix = $_zp_conf_vars['mysql_prefix'];
		$prefixLC = strtolower($prefix);
		$prefixUC = strtoupper($prefixLC);
		if ($result) {
			while ($row = db_fetch_row($result)) {
				$key = $row[0];
				$key = str_replace(array($prefixLC,$prefixUC), $_zp_conf_vars['mysql_prefix'], $key);
				$tables[$key] = 'update';
			}
		}
		$expected_tables = array($_zp_conf_vars['mysql_prefix'].'options', $_zp_conf_vars['mysql_prefix'].'albums',
			$_zp_conf_vars['mysql_prefix'].'images', $_zp_conf_vars['mysql_prefix'].'comments',
			$_zp_conf_vars['mysql_prefix'].'administrators', $_zp_conf_vars['mysql_prefix'].'admin_to_object',
			$_zp_conf_vars['mysql_prefix'].'tags', $_zp_conf_vars['mysql_prefix'].'obj_to_tag',
			$_zp_conf_vars['mysql_prefix'].'captcha',
			$_zp_conf_vars['mysql_prefix'].'pages',	$_zp_conf_vars['mysql_prefix'].'news2cat',
			$_zp_conf_vars['mysql_prefix'].'news_categories',	$_zp_conf_vars['mysql_prefix'].'news',
			$_zp_conf_vars['mysql_prefix'].'menu',$_zp_conf_vars['mysql_prefix'].'plugin_storage'
			);

		// v1.3.2 handle zenpage table name change transition:
		//				if the old table exists it gets updated instead of a new one being created
		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'zenpage_pages'])) {
			unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'].'pages', $expected_tables)]);
			$expected_tables[] = $_zp_conf_vars['mysql_prefix'].'zenpage_pages';
		}
		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'zenpage_news'])) {
			unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'].'news', $expected_tables)]);
			$expected_tables[] = $_zp_conf_vars['mysql_prefix'].'zenpage_zenpage_pages';
		}
		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'zenpage_news2cat'])) {
			unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'].'news2cat', $expected_tables)]);
			$expected_tables[] = $_zp_conf_vars['mysql_prefix'].'zenpage_news';
		}
		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'zenpage_news_categories'])) {
			unset($expected_tables[array_search($_zp_conf_vars['mysql_prefix'].'news_categories', $expected_tables)]);
			$expected_tables[] = $_zp_conf_vars['mysql_prefix'].'zenpage_news_categories';
		}
		foreach ($expected_tables as $needed) {
			if (!isset($tables[$needed])) {
				$tables[$needed] = 'create';
			}
		}
		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'admintoalbum'])) {
			$tables[$_zp_conf_vars['mysql_prefix'].'admin_to_object'] = 'update';
		}

		if (!($tables[$_zp_conf_vars['mysql_prefix'].'administrators'] == 'create')) {
			if (!zp_loggedin(ADMIN_RIGHTS) && (!isset($_GET['create']) && !isset($_GET['update']))) {  // Display the login form and exit.
				if (zp_loggedin()) { echo "<p>".gettext("You need <em>USER ADMIN</em> rights to run setup.").'</p>'; }
				$_zp_authority->printLoginForm('', false);
				echo "\n</div><!-- content -->";
				if ($noxlate > 0) {
					setupLanguageSelector();
				}
				echo "\n</div><!-- main -->";
				printAdminFooter();
				echo "\n</body>";
				echo "\n</html>";
				exit();
			}
		}
	}

	// Prefix the table names. These already have `backticks` around them!
	$tbl_albums = prefix('albums');
	$tbl_comments = prefix('comments');
	$tbl_images = prefix('images');
	$tbl_options  = prefix('options');
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

	// Prefix the constraint names:
	$db_schema = array();
	$sql_statements = array();
	$collation = db_collation();

	/***********************************************************************************
	 Add new fields in the upgrade section. This section should remain static except for new
	 tables. This tactic keeps all changes in one place so that noting gets accidentaly omitted.
	************************************************************************************/

	//v1.2
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'captcha'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_captcha (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`ptime` int(32) UNSIGNED NOT NULL,
		`hash` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		)	$collation;";
	}
	//v1.1.7
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'options'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_options (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0,
		`name` varchar(191) DEFAULT NULL,
		`value` text,
		`theme` varchar (127) DEFAULT NULL,
		`creator` varchar (255) DEFAULT NULL,
		PRIMARY KEY (`id`),
		UNIQUE (`name`, `ownerid`, `theme`)
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'tags'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_tags (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE (`name`)
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'obj_to_tag'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_obj_to_tag (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`tagid` int(11) UNSIGNED NOT NULL,
		`type` tinytext,
		`objectid` int(11) UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
		)	$collation;";
	}

	// v. 1.1.5
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'administrators'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS $tbl_administrators (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`user` varchar(64) NOT NULL,
		`pass` text,
		`name` text,
		`email` text,
		`rights` int,
		`custom_data` text,
		`valid` int(1) DEFAULT 1,
		`group` varchar(64) DEFAULT NULL,
		`date` datetime,
		`loggedin` datetime,
		`quota` int(11) DEFAULT NULL,
		`language` varchar(5) DEFAULT NULL,
		`prime_album` varchar(255) DEFAULT NULL,
		`other_credentials` TEXT,
		PRIMARY KEY (`id`),
		UNIQUE (`user`)
		)	$collation;";
	}
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'admin_to_object'])) {
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
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'albums'])) {
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
		KEY `folder` (`folder`)
		)	$collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'comments'])) {
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

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'images'])) {
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
		KEY `filename` (`filename`,`albumid`)
		)	$collation;";
	}

	//v1.2.4
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'news'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('news')." (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`title` text,
		`content` text,
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
		PRIMARY KEY (`id`),
		UNIQUE (`titlelink`)
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'news_categories'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('news_categories')." (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`title` text,
		`titlelink` varchar(255) NOT NULL default '',
		`permalink` int(1) UNSIGNED NOT NULL default 0,
		`hitcounter` int(11) unsigned default 0,
		`user` varchar(64) DEFAULT NULL,
		`password` varchar(64) DEFAULT NULL,
		`password_hint` text,
		`parentid` int(11) DEFAULT NULL,
		`sort_order` varchar(48) DEFAULT NULL,
		`desc` text,
		`custom_data` text,
		PRIMARY KEY (`id`),
		UNIQUE (`title`)
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'news2cat'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('news2cat')." (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`cat_id` int(11) unsigned NOT NULL,
		`news_id` int(11) unsigned NOT NULL,
		PRIMARY KEY (`id`)
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'pages'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('pages')." (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`parentid` int(11) unsigned default NULL,
		`title` text,
		`content` text,
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
		PRIMARY KEY (`id`),
		UNIQUE (`titlelink`)
		) $collation;";
	}

	if (isset($create[$_zp_conf_vars['mysql_prefix'].'menu'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('menu')." (
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
	if (isset($create[$_zp_conf_vars['mysql_prefix'].'plugin_storage'])) {
		$db_schema[] = "CREATE TABLE IF NOT EXISTS ".prefix('plugin_storage')." (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`type` varchar(32) NOT NULL,
		`aux` varchar(255),
		`data` TEXT,
		PRIMARY KEY (`id`),
		KEY `type` (`type`),
		KEY `aux` (`aux`)
		) $collation;";
	}


	/****************************************************************************************
	 ******                             UPGRADE SECTION                                ******
	 ******                                                                            ******
	 ******                          Add all new fields below                          ******
	 ******                                                                            ******
	 ****************************************************************************************/

	//v1.3.2
	$sql_statements[] = "RENAME TABLE ".prefix('zenpage_news')." TO $tbl_news,".
																			prefix('zenpage_news2cat')." TO $tbl_news2cat,".
																			prefix('zenpage_news_categories')." TO $tbl_news_categories,".
																			prefix('zenpage_pages')." TO $tbl_pages";

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
	$sql = "SHOW INDEX FROM `".$_zp_conf_vars['mysql_prefix']."comments`";
	$result = query($sql, false);
	$hasownerid = false;
	if ($result) {
		while ($row = db_fetch_row($result)) {
			if ($row[2] == 'ownerid') {
				$hasownerid = true;
			} else {
				if ($row[2] != 'PRIMARY') {
					$sql_statements[] = "ALTER TABLE $tbl_comments DROP INDEX `".$row[2]."`;";
				}
			}
		}
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
	$sql_statements[] = "ALTER TABLE $tbl_tags ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_tags CHANGE `name` `name` varchar(255) ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_administrators ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_administrators CHANGE `name` `name` TEXT ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_options ADD COLUMN `ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0";
	$sql_statements[] = "ALTER TABLE $tbl_options DROP INDEX `name`";
//v1.2.7 omits	$sql_statements[] = "ALTER TABLE $tbl_options ADD UNIQUE `unique_option` (`name`, `ownerid`)";

	//v1.2
	$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `ownerid` `ownerid` int(11) UNSIGNED NOT NULL DEFAULT 0";
	$sql_statements[] = "ALTER TABLE $tbl_obj_to_tag ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `name` `name` varchar(255) ".$collation;
	$hastagidindex = false;
	$sql = "SHOW INDEX FROM `".$_zp_conf_vars['mysql_prefix']."obj_to_tag`";
	$result = query($sql, false);
	if ($result) {
		while ($row = db_fetch_row($result)) {
			if ($row[2] == 'tagid') {
				$hastagidindex = true;
			}
		}
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
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' DROP INDEX `title`;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' ADD UNIQUE (`title`);';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' DROP INDEX `titlelink`;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD UNIQUE (`titlelink`);';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' DROP INDEX `titlelink`;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD UNIQUE (`titlelink`);';
	$sql_statements[] = 'ALTER TABLE '.$tbl_comments.' CHANGE `comment` `comment` TEXT;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' CHANGE `title` `title` TEXT;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' CHANGE `titlelink` `titlelink` TEXT;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' CHANGE `title` `title` TEXT;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_comments.' ADD COLUMN `custom_data` TEXT;';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `expiredate` datetime default NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `expiredate` datetime default NULL';
	$sql_statements[] = 'UPDATE '.$tbl_pages.' SET `parentid`=NULL WHERE `parentid`=0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' CHANGE `sort_order` `sort_order` VARCHAR(48) NOT NULL default ""';
	//v1.2.5
	$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `parentid` `parentid` int(11) unsigned default NULL;";
	$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `albumid` `albumid` int(11) unsigned default NULL";
	$sql_statements[] = 'UPDATE '.$tbl_albums.' SET `parentid`=NULL WHERE `parentid`=0';
	$sql_statements[] = 'UPDATE '.$tbl_images.' SET `albumid`=NULL WHERE `albumid`=0';
	$sql_statements[] = 'DELETE FROM '.$tbl_pages.' WHERE `titlelink`=""'; // cleanup for bad records
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' DROP INDEX `titlelink`';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD UNIQUE (`titlelink`)';

	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
	$sql_statements[] = 'UPDATE '.$tbl_albums.' SET rating=total_value / total_votes WHERE total_votes > 0';

	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
	$sql_statements[] = 'UPDATE '.$tbl_images.' SET rating=total_value / total_votes WHERE total_votes > 0';

	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `total_votes` int(11) UNSIGNED default 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `total_value` int(11) UNSIGNED default 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `used_ips` longtext';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';

	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `total_votes` int(11) UNSIGNED default 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `total_value` int(11) UNSIGNED default 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `rating` FLOAT  NOT NULL DEFAULT 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `used_ips` longtext';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' ADD COLUMN `rating_status` int(1) UNSIGNED default 3';
	//v1.2.6
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `custom_data` TEXT';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' CHANGE `password` `pass` varchar(64)';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `valid` int(1) default 1';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `group` varchar(64)';
	$sql = 'SHOW INDEX FROM '.$tbl_administrators;
	$result = query($sql, false);
	if ($result) {
		while ($row = db_fetch_row($result)) {
			if ($row[2] == 'user') {
				$sql_statements[] = "ALTER TABLE $tbl_administrators DROP INDEX `user`";
				$sql_statements[] = "ALTER TABLE $tbl_administrators ADD UNIQUE (`valid`, `user`)";
				break;
			}
		}
	}
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `watermark` varchar(255) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' CHANGE `commentson` `commentson` int(1) UNSIGNED default 0';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news.' CHANGE `commentson` `commentson` int(1) UNSIGNED default 0';
	//v1.2.7
	$sql_statements[] = "ALTER TABLE $tbl_albums CHANGE `album_theme` `album_theme` varchar(127) DEFAULT NULL";
	$sql_statements[] = "ALTER TABLE $tbl_options DROP INDEX `unique_option`";
	$sql_statements[] = "ALTER TABLE $tbl_options ADD COLUMN `theme` varchar(127) DEFAULT NULL";
	$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `name` `name` varchar(191) DEFAULT NULL";
	$sql_statements[] = "ALTER TABLE $tbl_options ADD UNIQUE `unique_option` (`name`, `ownerid`, `theme`)";
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' DROP COLUMN `EXIFValid`';
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `hasMetadata` int(1) default 0';
	$sql_statements[] = 'UPDATE '.$tbl_images.' SET `date`=NULL WHERE `date`="0000-00-00 00:00:00"'; // empty dates should be NULL
	$sql_statements[] = 'UPDATE '.$tbl_albums.' SET `date`=NULL WHERE `date`="0000-00-00 00:00:00"'; // force metadata refresh
	//v1.2.8
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' CHANGE `place` `location` TEXT';
	//v1.3
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `watermark` varchar(255) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `watermark_use` int(1) DEFAULT 7';
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `owner` varchar(64) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `filesize` INT';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `quota` INT';
	$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `user` varchar(64) default ''";
	$sql_statements[] = 'ALTER TABLE '.$tbl_pages.' ADD COLUMN `password` VARCHAR(64)';
	$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `password_hint` text;";
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `user` varchar(64) default ''";
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' ADD COLUMN `password` VARCHAR(64)';
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `password_hint` text;";

	//v1.3.1
	$sql_statements[] = 'RENAME TABLE '.prefix('admintoalbum').' TO '.$tbl_admin_to_object;
	$sql_statements[] = 'ALTER TABLE '.$tbl_admin_to_object.' ADD COLUMN `type` varchar(32) DEFAULT "album";';
	$sql_statements[] = 'ALTER TABLE '.$tbl_admin_to_object.' CHANGE `albumid` `objectid` int(11) UNSIGNED NOT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' CHANGE `albums` `objects` varchar(64)';
	$sql_statements[] = "ALTER TABLE $tbl_news ADD COLUMN `sticky` int(1) default 0";
	$sql_statements[] = "ALTER TABLE $tbl_albums ADD COLUMN `codeblock` TEXT";
	$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `codeblock` TEXT";
	$sql_statements[] = "ALTER TABLE $tbl_admin_to_object ADD COLUMN `edit` int default 32767";

	//v1.4.0
	$sql_statements[] = "ALTER TABLE $tbl_options CHANGE `value` `value` TEXT ".$collation;
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `parentid` INT(11) DEFAULT NULL";
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `sort_order` varchar(48)";
	$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `user` varchar(64) default ''";
	$sql_statements[] = 'ALTER TABLE '.$tbl_images.' ADD COLUMN `password` VARCHAR(64)';
	$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `password_hint` text;";
	$sql_statements[] = "ALTER TABLE $tbl_news_categories CHANGE `cat_name` `title` TEXT";
	$sql_statements[] = "ALTER TABLE $tbl_news_categories CHANGE `cat_link` `titlelink` varchar(255) NOT NULL";
	$sql_statements[] = 'UPDATE '.$tbl_obj_to_tag.' SET `type`="news" WHERE `type`="zenpage_news"';
	$sql_statements[] = 'UPDATE '.$tbl_obj_to_tag.' SET `type`="pages" WHERE `type`="zenpage_pages"';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `language` VARCHAR(5)';
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `desc` text;";
	$sql_statements[] = "ALTER TABLE $tbl_news_categories ADD COLUMN `custom_data` text";
	$sql_statements[] = "ALTER TABLE $tbl_pages ADD COLUMN `custom_data` text";
	$sql_statements[] = "ALTER TABLE $tbl_news ADD COLUMN `custom_data` text";
	$sql_statements[] = "ALTER TABLE $tbl_images DROP FOREIGN KEY `images_ibfk1`";
	$sql_statements[] = "ALTER TABLE $tbl_menu_manager ADD COLUMN `span_id` varchar(43) default NULL";
	$sql_statements[] = "ALTER TABLE $tbl_menu_manager ADD COLUMN `span_class` varchar(43) default NULL";
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `watermark_thumb` varchar(255) DEFAULT NULL';
	//v1.4.1
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `prime_album` varchar(255) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `other_credentials` TEXT';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `date` datetime';
	$sql_statements[] = 'ALTER TABLE '.$tbl_administrators.' ADD COLUMN `loggedin` datetime';
	$sql_statements[] = 'UPDATE '.$tbl_administrators.' SET `date`="'.date('Y-m-d H:i:s',$zptime).'" WHERE `date` IS NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `owner` varchar(64) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' CHANGE `owner` `owner` varchar(64) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_options.' ADD COLUMN `creator` varchar(255) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_albums.' ADD COLUMN `updateddate` datetime DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' DROP INDEX `title`';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' CHANGE `titlelink` `titlelink` VARCHAR(255) DEFAULT NULL';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' CHANGE `title` `title` TEXT';
	$sql_statements[] = 'ALTER TABLE '.$tbl_news_categories.' ADD UNIQUE (`titlelink`)';


	// do this last incase there are any field changes of like names!
	foreach ($_zp_exifvars as $key=>$exifvar) {
		$s = $exifvar[4];
		if ($s<255) {
			$size = "varchar($s)";
		} else {
			$size = 'MEDIUMTEXT';
		}
		$sql_statements[] = "ALTER TABLE $tbl_images ADD COLUMN `$key` $size default NULL";
		$sql_statements[] = "ALTER TABLE $tbl_images CHANGE `$key` `$key` $size default NULL";
	}


	/**************************************************************************************
	 ******                            END of UPGRADE SECTION
	 ******
	 ******                           Add all new fields above
	 ******
	 ***************************************************************************************/

	$createTables = true;
	if (isset($_GET['create']) || isset($_GET['update']) || isset($_GET['delete_files']) && db_connect()) {
		if (!isset($_GET['delete_files'])) {
			set_time_limit(60);
			if ($taskDisplay[substr($task,0,8)] == 'create') {
				echo "<h3>".gettext("About to create tables")."...</h3>";
			} else {
				echo "<h3>".gettext("About to update tables")."...</h3>";
			}
			setupLog(gettext("Begin table creation"));
			foreach($db_schema as $sql) {
				$result = db_create_table($sql);
				echo ' ';	// keep alive
				if (!$result) {
					$createTables = false;
					setupLog(sprintf(gettext('Query %1$s Failed. Error: %2$s'),$sql,db_error()));
					echo '<div class="error">';
					echo sprintf(gettext('Table creation failure:<br />Query: %1$s<br />Error: %2$s'),$sql,db_error());
					echo '</div>';
				} else {
					setupLog(sprintf(gettext('Query ( %s ) Success.'),$sql));
				}
			}
			// always run the update queries to insure the tables are up to current level
			setupLog(gettext("Begin table updates"));
			foreach($sql_statements as $sql) {
				echo ' ';	// keep alive
				$result = db_table_update($sql);
				if (!$result) {
					$error = db_error();
					setupLog(sprintf(gettext('Query %1$s Failed. Error: %2$s'),$sql,$error),strpos($error, 'syntax')!==false);
				} else {
					setupLog(sprintf(gettext('Query ( %s ) Success.'),$sql));
				}
			}

			// set defaults on any options that need it
			setupLog(gettext("Done with database creation and update"));
			$prevRel = getOption('zenphoto_release');
			setupLog(sprintf(gettext("Previous Release was %s"),$prevRel));

			$_zp_gallery = new Gallery();
			require(dirname(__FILE__).'/setup/setup-option-defaults.php');

			// 1.1.6 special cleanup section for plugins
			$badplugs = array ('exifimagerotate.php', 'flip_image.php', 'image_mirror.php', 'image_rotate.php', 'supergallery-functions.php');
			foreach ($badplugs as $plug) {
				$path = SERVERPATH . '/' . ZENFOLDER .'/'.PLUGIN_FOLDER.'/' . $plug;
				@unlink($path);
			}

			// update zenpage codeblocks--remove the base64 encoding
			$sql = 'SELECT `id`, `codeblock` FROM '.prefix('news').' WHERE `codeblock` NOT REGEXP "^a:[0-9]+:{"';
			$result = query_full_array($sql,false);
			if (is_array($result)) {
				foreach ($result as $row) {
					$codeblock = base64_decode($row['codeblock']);
					$sql = 'UPDATE '.prefix('news').' SET `codeblock`='.db_quote($codeblock).' WHERE `id`='.$row['id'];
					query($sql);
				}
			}
			$sql = 'SELECT `id`, `codeblock` FROM '.prefix('pages').' WHERE `codeblock` NOT REGEXP "^a:[0-9]+:{"';
			$result = query_full_array($sql,false);
			if (is_array($result)) {
				foreach ($result as $row) {
					$codeblock = base64_decode($row['codeblock']);
					$sql = 'UPDATE '.prefix('pages').' SET `codeblock`='.db_quote($codeblock).' WHERE `id`='.$row['id'];
					query($sql);
				}
			}

			echo "<h3>";
			if ($taskDisplay[substr($task,0,8)] == 'create') {
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
			echo "</h3>";

			// fixes 1.2 move/copy albums with wrong ids
			$albums = $_zp_gallery->getAlbums();
			foreach ($albums as $album) {
				checkAlbumParentid($album, NULL);
			}
		}
		if ($createTables) {
			if (isset($_GET['delete_files'])) {
				$curdir = getcwd();
				chdir(dirname(__FILE__).'/setup');
				$list = setup_glob('*.*');
				chdir($curdir);
				$rslt = true;
				foreach ($list as $component) {
					if ($component != '..' && $component != '.') {
						$rslt = $rslt && setupDeleteComponent(@unlink(SERVERPATH.'/'.ZENFOLDER.'/setup/'.$component),$component);
					}
				}
				$rslt = $rslt && setupDeleteComponent(@unlink(SERVERPATH.'/'.ZENFOLDER.'/setup.php'),'setup.php');
				$rslt = $rslt && setupDeleteComponent(@rmdir(SERVERPATH.'/'.ZENFOLDER.'/setup/'),'setup/');
				if (!$rslt) {
					?>
					<p class="errorbox"><?php echo gettext('Deleting files failed!'); ?></p>
					<?php
				}
			} else {
				?>
					<div class="notebox">
						<p><?php echo gettext('<strong>NOTE:</strong> We strongly recommend you remove the <em>setup</em> scripts from your zp-core folder at this time. You can always re-upload them should you find you need them again in the future.')?></p>
						<p class="buttons"><a href="?checked&amp;delete_files&xsrfToken=<?php echo $xsrftoken; ?>"><?php echo gettext('Delete setup files'); ?></a></p>
						<br clear="all" />
						<br clear="all" />
					</div>
					<br clear="all" />
				<?php
			}
			if (!$_zp_loggedin || $_zp_loggedin == ADMIN_RIGHTS) {
				$filelist = safe_glob(SERVERPATH . "/" . BACKUPFOLDER . '/*.zdb');
				if (count($filelist) > 0) {
					echo "<p>".sprintf(gettext('You may <a href="%1$s">set your admin user and password</a> or <a href="%2$s">run backup-restore</a>'),'admin-users.php?page=users',UTILITIES_FOLDER.'/backup_restore.php')."</p>";
				} else {
					echo "<p>".sprintf(gettext('You need to <a href="%1$s">set your admin user and password</a>'),'admin-users.php?page=users')."</p>";
				}
			} else {
				?>
				<p><?php echo sprintf(gettext('You can now  <a href="%1$s">View your gallery</a> or <a href="%2$s">administer.</a>'),'..','admin.php'); ?></p>
				<?php
			}
		}
	} else if (db_connect()) {
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
					if (!empty($db_list)) { $db_list .= ', '; }
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
					if (!empty($db_list)) { $db_list .= ', '; }
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
		$task = '';
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
		if ($debug) {
			$task .= '&debug';
		}

		if (isset($tables[$_zp_conf_vars['mysql_prefix'].'zenpage_news'])) {
			$hideGoButton = ' style="display:none"';
			?>
			</script>
			<div class="warning" id="dbrestructure">
				<p><?php echo gettext('<strong>Warning!</strong> This upgrade makes structural changes to the database which are not easily reversed. Be sure you have a database backup before proceeding.'); ?></p>
				<form>
					<input type="hidden" name="xsrfToken" value="<?php echo $xsrftoken?>" />
					<p><?php printf(gettext('%s I acknowledge that proceeding will restructure my database.'),
																'<input type="checkbox" id="agree" value="0" onclick="javascript:$(\'#setup\').show();$(\'#agree\').attr(\'checked\',\'checked\')" />')?></p>
				</form>
			</div>
			<?php
		} else {
			$hideGoButton = '';
		}
		if ($warn) {
			$img = 'warn.png';
		} else {
			$img = 'pass.png';
		}
		?>
		<form id="setup" action="?checked&amp;<?php echo $task.$mod; ?>" method="post"<?php echo $hideGoButton; ?> >
		<input type="hidden" name="setUTF8URI" id="setUTF8URI" value="dont" />
		<input type="hidden" name="xsrfToken" value="<?php echo $xsrftoken?>" />
		<?php
			if (isset($zenphoto_themes)) {
				?>
				<input type="hidden" id="themelist" name="themelist" value="<?php echo html_encode(serialize($zenphoto_themes)); ?>" />
				<?php
			}
			?>
			<p class="buttons"><button class="submitbutton" type="submit"	title="<?php echo gettext('run setup'); ?>" ><img src="images/<?php echo $img; ?>" alt="" /><?php echo gettext("Go"); ?></button></p>
			<br clear="all" /><br clear="all" />
		</form>
		<?php

	} else {
		?>
		<div class="error">
			<h3><?php echo gettext("database did not connect"); ?></h3>
			<p>
			<?php echo gettext("If you haven't created the database yet, now would be a good time."); ?>
			</p>
		</div>
		<?php
	}
} else {
	// The config file hasn't been created yet. Show the steps.
	?>
	<div class="error">
		<?php echo gettext("The zp-config.php file does not exist. You should run setup.php to check your configuration and create this file."); ?>
	</div>
<?php
}

?>
</div><!-- content -->
<?php
if ($noxlate > 0) {
	setupLanguageSelector();
}

?>
</div><!-- main -->
<?php
printAdminFooter();
?>

</body>
</html>

