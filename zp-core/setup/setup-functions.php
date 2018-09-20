<?php
/**
 * setup support functions
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 */
// force UTF-8 Ø
require_once(dirname(dirname(__FILE__)) . '/global-definitions.php');
require_once(dirname(dirname(__FILE__)) . '/functions-config.php');

define('SETUPLOG', SERVERPATH . '/' . DATA_FOLDER . '/setup.log');

/**
 * Returns true if we are running on a Windows server
 *
 * @return bool
 */
function isWin() {
	return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
}

/**
 * Returns true if we are running on a Macintosh
 */
function isMac() {
	return strtoupper(PHP_OS) == 'DARWIN';
}

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentZPFiles($folder, $lcFilesystem, $exclude) {
	global $_zp_resident_files;
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if ($file != '.' && $file != '..' && !in_array($file, $exclude)) {
			if (is_dir($folder . '/' . $file)) {
				if ($file != 'session') {
					getResidentZPFiles($folder . '/' . $file, $lcFilesystem, $exclude);
					$entry = $folder . '/' . $file;
					if ($lcFilesystem)
						$entry = strtolower($entry);
					$_zp_resident_files[] = $entry;
				}
			} else {
				$entry = $folder . '/' . $file;
				if ($lcFilesystem)
					$entry = strtolower($entry);
				$_zp_resident_files[] = $entry;
			}
		}
	}
	closedir($dir);
}

function primeMark($text) {
	global $primeid;
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
	</script>
	<div id="prime<?php echo ++$primeid; ?>" class="error"><?php printf(gettext('Testing %s.'), $text); ?></div>
	<?php
}

function checkMark($check, $text, $text2, $msg, $stopAutorun = true) {
	global $warn, $moreid, $primeid, $autorun, $displayLimited;
	$classes = array('fail' => gettext('Fail: '), 'warn' => gettext('Warn: '), 'pass' => gettext('Pass: '));

	$display = '';
	?>
	<script type="text/javascript">
		$("#prime<?php echo $primeid; ?>").remove();
	</script>
	<?php
	$check = (int) $check;
	$anyway = 0;
	$dsp = '';
	if ($check > 0) {
		$check = 1;
	}

	switch ($check) {
		case 0:
			$cls = "fail";
			$ico = CROSS_MARK_RED;
			break;
		case -1:
		case -3:
			$cls = "warn";
			$ico = WARNING_SIGN_ORANGE;
			$warn = true;
			if ($stopAutorun && $autorun) {
				$autorun = false;
				$anyway = 2;
				$check = -1;
			} else {
				$anyway = 1;
			}
			break;
		case 1:
		case -2:
			if ($displayLimited) {
				$display = ' style="display:none;"';
			}
			$cls = "pass";
			$ico = CHECKMARK_GREEN;
			break;
	}
	if ($check <= 0) {
		?>
		<li class="<?php echo $cls; ?>"<?php echo $display; ?>>
			<?php
			echo $ico . ' ';
			if (empty($text2)) {
				echo $text;
				$dsp .= trim($text);
			} else {
				echo $text2;
				$dsp .= trim($text2);
			}
			if (!empty($msg)) {
				switch ($check) {
					case 0:
						?>
						<div class="error">
							<h1><?php echo gettext('Error!'); ?></h1>
							<p><?php echo $msg; ?></p>
						</div>
						<?php
						break;
					case -1:
						$anyway = 1;
						?>
						<div class="warning">
							<h1><?php echo gettext('Warning!'); ?></h1>
							<p><?php echo $msg; ?></p>
						</div>
						<?php
						break;
					default:
						$moreid++;
						?>
						<?php
						if ($check == -3) {
							?>
							<a onclick="toggle_visibility('more<?php echo $moreid; ?>');">
								<?php echo gettext('<strong>Warning!</strong> click for details'); ?>
							</a>
							<div class="warning" id="more<?php echo $moreid; ?>" style="display: none">
								<h1><?php echo gettext('Warning!'); ?></h1>
								<?php
							} else {
								?>
								<a onclick="toggle_visibility('more<?php echo $moreid; ?>');">
									<?php echo gettext('<strong>Notice!</strong> click for details'); ?>
								</a>
								<div class="notice" id="more<?php echo $moreid; ?>" style="display: none">
									<h1><?php echo gettext('Notice!'); ?></h1>
									<?php
								}
								?>
								<p><?php echo $msg; ?></p>
							</div>
							<?php
							break;
					}
				}
				?>
		</li>
		<?php
	} else {
		$dsp = $text;
		?>
		<li class="<?php echo $cls; ?>"<?php echo $display; ?>>
			<?php echo $ico . ' ' . $text; ?>
		</li>
		<?php
	}
	if ($anyway == 2) {
		$stopped = '(' . gettext('Autorun aborted') . ') ';
	} else {
		$stopped = '';
	}
	$msg = preg_replace('~<form.*\/form>~iU', '', $msg);
	$msg = preg_replace('~<p class="buttons".*<\/p>~iU', '', $msg);
	$head = $classes[$cls] . $stopped . $dsp;

	switch ($cls) {
		case 'warn':
			$log = '<span class="logwarning">' . $head . '</span><br />' . $msg;
			break;
		case 'fail':
			$log = '<span class="logerror">' . $head . '</span><br />' . $msg;
			break;
		default:
			$log = $head;
			break;
	}
	setupLog($log, $anyway);
	return $check;
}

/**
 *
 * checks presence and permissions of folders
 * @param $which
 * @param $path
 * @param $class
 * @param $relaxation
 * @param $subfolders
 */
function folderCheck($which, $path, $class, $subfolders, $recurse, $chmod, $updatechmod) {
	global $permission_names;
	$path = str_replace('\\', '/', $path);
	if (!is_dir($path) && $class == 'std') {
		mkdir_recursive($path, $chmod);
	}
	switch ($class) {
		case 'std':
			$append = trim(str_replace(SERVERPATH, '', $path), '/');
			if (($append != $which)) {
				$f = " (<em>$append</em>)";
			} else {
				$f = '';
			}
			if (!is_null($subfolders)) {
				$subfolderfailed = '';
				foreach ($subfolders as $subfolder) {
					if (!mkdir_recursive($path . $subfolder, $chmod)) {
						$subfolderfailed .= ', <code>' . $subfolder . '</code>';
					}
				}
				if (!empty($subfolderfailed)) {
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [subfolder creation failure]'), $which, $f), sprintf(gettext('Setup could not create the following subfolders:<br />%s'), substr($subfolderfailed, 2)));
				}
			}

			if (isWin()) {
				$perms = fileperms($path) & 0700;
				$check = $chmod & 0700;
			} else {
				$perms = fileperms($path) & 0777;
				$check = $chmod;
			}
			if (setupUserAuthorized() && $updatechmod) {
				@chmod($path, $chmod);
				clearstatcache();
				$perms = fileperms($path) & 0777;
				if (!checkPermissions($perms, $chmod)) {
					if (array_key_exists($perms & 0666 | 4, $permission_names)) {
						$perms_class = $permission_names[$perms & 0666 | 4];
					} else {
						$perms_class = gettext('unknown');
					}
					if (array_key_exists($chmod & 0666 | 4, $permission_names)) {
						$chmod_class = $permission_names[$chmod & 0666 | 4];
					} else {
						$chmod_class = gettext('unknown');
					}
					return checkMark(-1, '', sprintf(gettext('<em>%1$s</em> folder%2$s [permissions failure]'), $which, $f), sprintf(gettext('Setup could not change the file permissions from <em>%1$s</em> (<code>0%2$o</code>) to <em>%3$s</em> (<code>0%4$o</code>). You will have to set the permissions manually.'), $perms_class, $perms, $chmod_class, $chmod));
				} else {
					if ($recurse) {
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							$.ajax({
								type: 'POST',
								cache: false,
								url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/setup/setup_permissions_changer.php',
								data: 'folder=<?php echo $path; ?>&key=<?php echo sha1(filemtime(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)); ?>'
							});
							// ]]> -->
						</script>
						<?php
					}
				}
			}
			break;
		case 'in_webpath':
			$webpath = $_SERVER['SCRIPT_NAME'];
			if (empty($webpath)) {
				$serverroot = SERVERPATH;
			} else {
				$i = strpos($webpath, '/' . ZENFOLDER);
				$webpath = substr($webpath, 0, $i);
				$serverroot = substr(SERVERPATH, 0, strpos(SERVERPATH, $webpath));
			}
			$append = substr($path, strlen($serverroot) + 1);
			$f = " (<em>$append</em>)";
			break;
		case 'external':
			$append = $path;
			$f = " (<em>$append</em>)";
			break;
	}
	if (!is_dir($path)) {
		$msg = " " . sprintf(gettext('You must create the folder <em>%1$s</em><br /><code>mkdir(%2$s, 0777)</code>.'), $append, substr($path, 0, -1));
		if ($class != 'std') {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist]'), $which, $append), $msg);
		} else {
			return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> does not exist and <strong>setup</strong> could not create it]'), $which, $append), $msg);
		}
	} else if (!is_writable($path)) {
		$msg = sprintf(gettext('Change the permissions on the <code>%1$s</code> folder to be writable by the server (<code>chmod 777 %2$s</code>)'), $which, $append);
		return checkMark(false, '', sprintf(gettext('<em>%1$s</em> folder [<em>%2$s</em> is not writeable and <strong>setup</strong> could not make it so]'), $which, $append), $msg);
	} else {
		return checkMark(true, sprintf(gettext('<em>%1$s</em> folder%2$s'), $which, $f), '', '');
	}
}

/**
 *
 * compares versions for required, desired version levels
 * @param $required
 * @param $desired
 * @param $found
 */
function versionCheck($required, $desired, $found) {
	if (version_compare($found, $required, '<')) {
		return 0;
	}
	if (version_compare($found, $desired, '<')) {
		return -1;
	}
	return 1;
}

/**
 *
 * drop-down for character set selection
 * @param $select
 */
function charsetSelector($select) {
	global $_zp_UTF8;
	$selector = '<select id="FILESYSTEM_CHARSET" name="FILESYSTEM_CHARSET" >';
	$selector .= '<option value ="unknown">' . gettext('Unknown') . '</option>';
	$totalsets = $_zp_UTF8->charsets;
	ksort($totalsets);
	foreach ($totalsets as $key => $char) {
		$selector .= '	<option value="' . $key . '"';
		if ($key == $select) {
			$selector .= ' selected="selected"';
		}
		$selector .= '>' . $char . '</option>';
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span>';
	return $selector;
}

function permissionsSelector($permission_names, $select) {
	$select = $select | 4;
	global $_zp_UTF8;
	$selector = '<select id="chmod_permissions" name="chmod_permissions" >';
	$c = 0;
	foreach ($permission_names as $key => $permission) {
		$selector .= '	<option value="' . $c . '"' . ($select == $key ? ' selected="selected"' : '') . '>' . sprintf(gettext('%1$s (0%2$o)'), $permission_names[$key], $key) . '</option>';
		$c++;
	}
	$selector .= '</select>';
	$selector .= '<span class="buttons" style="float: right;"><button type="submit" alt="' . gettext('change the definition') . '"><strong>' . gettext('apply') . '</strong></button></span><br class="clearall">';
	return $selector;
}

function setupLog($message, $anyway = false, $reset = false) {
	global $debug, $_zp_mutex, $chmod, $_adminCript;
	if (getOption('setup_log_encryption')) {
		$_logCript = $_adminCript;
	} else {
		$_logCript = NULL;
	}
	if ($debug || $anyway) {
		if (is_object($_zp_mutex))
			$_zp_mutex->lock();
		if (!file_exists(dirname(SETUPLOG))) {
			mkdir_recursive(dirname(SETUPLOG), $chmod | 0311);
		}
		if ($reset) {
			$mode = 'w';
		} else {
			$mode = 'a';
		}
		$f = fopen(SETUPLOG, $mode);
		if ($f) {
			if ($_logCript) {
				$message = $_logCript->encrypt($message);
			}
			fwrite($f, $message . NEWLINE);
			fclose($f);
			chmod(SETUPLOG, LOG_MOD);
			clearstatcache();
		}
		if (is_object($_zp_mutex))
			$_zp_mutex->unlock();
	}
}

function setupXSRFDefender($where) {
	$xsrftoken = setupXSRFToken();
	if (!isset($_REQUEST['xsrfToken']) || $xsrftoken != $_REQUEST['xsrfToken']) {
		?>
		<p class="errorbox" >
			<?php echo gettext('An attempt at cross site reference forgery has been blocked.') ?>
		</p>
		<?php
		exit();
	}
}

function setupXSRFToken() {
	if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
		$zp_cfg = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		return sha1(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE . $zp_cfg . session_id());
	} else {
		return false;
	}
}

function checkPermissions($actual, $expected) {
	if (isWin()) {
		return ($actual & 0700) == ($expected & 0700); //	with windows owner==group==public
	} else {
		return ($actual & 0770) == ($expected & 0770); //	We do not care about the execute permissions
	}
}

function acknowledge($value) {
	global $_zp_conf_vars;
	$link = WEBPATH . '/' . ZENFOLDER . '/setup/index.php?security_ack=' . ((isset($_zp_conf_vars['security_ack']) ? $_zp_conf_vars['security_ack'] : NULL) | $value) . '&amp;xsrfToken=' . setupXSRFToken();
	return sprintf(gettext('Click <a href="%s">here</a> to acknowledge that you wish to ignore this issue. It will then become a warning.'), $link);
}

function configMod() {
	$mod = 0600;
	$str = NULL;
	while (empty($str)) {
		@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod);
		$str = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		if ($mod == 0666) {
			break;
		}
		$mod = $mod | $mod >> 3;
	}
}

function printSetupFooter($checked) {
	?>
	<div id="setup-footer">
		<?php
		if (!$checked) {
			?>
			<span id="footer_left">
				<?php printLanguageSelector(true); ?>
			</span>
			<?php
		}
		?>
		<span id="footer_right">
			<?php echo '<span class="zenlogo"><a href="https://netPhotoGraphics.org" title="' . gettext('A simpler media content management system') . '">' . swLogo() . '</a></span> ' . sprintf(gettext('version %1$s'), ZENPHOTO_VERSION); ?>
			| <a href="https://<?php echo GITHUB; ?>/issues" title="<?php echo gettext('Support'); ?>"><?php echo gettext('Support'); ?></a>
		</span>
	</div>
	<?php
}

function setupUserAuthorized() {
	global $_zp_authority, $_zp_loggedin;
	if ($_zp_authority && $_zp_authority->getAdministrators()) {
		return $_zp_loggedin & ADMIN_RIGHTS;
	} else {
		return true; //	in a primitive environment
	}
}

function checkUnique($table, $unique) {
	global $autorun;
	$sql = 'SHOW KEYS FROM ' . $table;
	$result = query_full_array($sql);
	foreach ($result as $key) {
		if (!$key['Non_unique']) {
			unset($unique[$key['Column_name']]);
		}
	}
	if (!empty($unique)) {
		$autorun = false;
		?>
		<p class="notebox">
			<?php
			$msg = sprintf(gettext('<strong>Warning:</strong> the <code>%s</code> table appears not to have a proper <em>UNIQUE</em> key. There are probably duplicate entries in the table which can cause unpredictable behavior. This can normally be corrected by creating a netPhotoGraphics backup, dropping the table, running setup to restore the table, and then restoring from the backup. Note, however, that the duplicate entries will be lost.'), trim($table, '`'));
			echo $msg;
			setupLog($msg, true);
			?>
		</p>
		<?php
		return true;
	}
	return false;
}

/**
 * Executes and logs database update queries
 * @param string $sql
 *
 * @author Stephen Billard
 * @Copyright 2016 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 */
function setupQuery($sql, $failNotify = true, $log = true) {
	global $updateErrors;
	$result = db_table_update($sql);
	if (OFFSET_PATH == 2) { //don't write to setup log if not running setup
		if ($result) {
			setupLog(sprintf(gettext('Query Success: %s'), $sql), $log);
		} else {
			if ($failNotify) {
				$updateErrors = true;
				$error = db_error();
				setupLog(sprintf(gettext('Query Failed: %1$s ' . NEWLINE . ' Error: %2$s'), $sql, $error), true);
			}
		}
	}
	return $result;
}

function sendImage($external, $which) {
	if ($external) {
		$img = 'pass_open.png';
		$image = "89504e470d0a1a0a0000000d49484452000000100000001008060000001ff3ff61000000097048597300000ec300000ec301c76fa86400000a4f6943435050686f746f73686f70204943432070726f66696c65000078da9d53675453e9163df7def4424b8880944b6f5215082052428b801491262a2109104a8821a1d91551c1114545041bc8a088038e8e808c15512c0c8a0ad807e421a28e83a3888acafbe17ba36bd6bcf7e6cdfeb5d73ee7acf39db3cf07c0080c9648335135800ca9421e11e083c7c4c6e1e42e40810a2470001008b3642173fd230100f87e3c3c2b22c007be000178d30b0800c04d9bc0301c87ff0fea42995c01808401c07491384b08801400407a8e42a600404601809d98265300a0040060cb6362e300502d0060277fe6d300809df8997b01005b94211501a09100201365884400683b00accf568a450058300014664bc43900d82d00304957664800b0b700c0ce100bb200080c00305188852900047b0060c8232378008499001446f2573cf12bae10e72a00007899b23cb9243945815b082d710757572e1e28ce49172b14366102619a402ec27999193281340fe0f3cc0000a0911511e083f3fd78ce0eaecece368eb60e5f2deabf06ff226262e3fee5cfab70400000e1747ed1fe2c2fb31a803b06806dfea225ee04685e0ba075f78b66b20f40b500a0e9da57f370f87e3c3c45a190b9d9d9e5e4e4d84ac4425b61ca577dfe67c25fc057fd6cf97e3cfcf7f5e0bee22481325d814704f8e0c2ccf44ca51ccf92098462dce68f47fcb70bfffc1dd322c44962b9582a14e35112718e449a8cf332a52289429229c525d2ff64e2df2cfb033edf3500b06a3e017b912da85d6303f64b27105874c0e2f70000f2bb6fc1d4280803806883e1cf77ffef3ffd47a02500806649927100005e44242e54cab33fc708000044a0812ab0411bf4c1182cc0061cc105dcc10bfc6036844224c4c24210420a64801c726029ac82422886cdb01d2a602fd4401d34c051688693700e2ec255b80e3d700ffa61089ec128bc81090441c808136121da8801628a58238e08179985f821c14804128b2420c9881451224b91354831528a542055481df23d720239875c46ba913bc8003282fc86bc47319481b2513dd40cb543b9a8371a8446a20bd06474319a8f16a09bd072b41a3d8c36a1e7d0ab680fda8f3e43c730c0e8180733c46c302ec6c342b1382c099363cbb122ac0cabc61ab056ac03bb89f563cfb17704128145c0093604774220611e4148584c584ed848a8201c243411da093709038451c2272293a84bb426ba11f9c4186232318758482c23d6128f132f107b8843c437241289433227b9900249b1a454d212d246d26e5223e92ca99b34481a2393c9da646bb20739942c202bc885e49de4c3e433e41be421f25b0a9d624071a4f853e22852ca6a4a19e510e534e5066598324155a39a52dda8a15411358f5a42ada1b652af5187a81334759a39cd8316494ba5ada295d31a681768f769afe874ba11dd951e4e97d057d2cbe947e897e803f4770c0d861583c7886728199b18071867197718af984ca619d38b19c754303731eb98e7990f996f55582ab62a7c1591ca0a954a9526951b2a2f54a9aaa6aadeaa0b55f355cb548fa95e537dae46553353e3a909d496ab55aa9d50eb531b5367a93ba887aa67a86f543fa47e59fd890659c34cc34f43a451a0b15fe3bcc6200b6319b3782c216b0dab86758135c426b1cdd97c762abb98fd1dbb8b3daaa9a13943334a3357b352f394663f07e39871f89c744e09e728a797f37e8ade14ef29e2291ba6344cb931655c6baa96979658ab48ab51ab47ebbd36aeeda79da6bd45bb59fb810e41c74a275c2747678fce059de753d953dda70aa7164d3d3af5ae2eaa6ba51ba1bb4477bf6ea7ee989ebe5e809e4c6fa7de79bde7fa1c7d2ffd54fd6dfaa7f5470c5806b30c2406db0cce183cc535716f3c1d2fc7dbf151435dc34043a561956197e18491b9d13ca3d5468d460f8c69c65ce324e36dc66dc6a326062621264b4dea4dee9a524db9a629a63b4c3b4cc7cdcccda2cdd699359b3d31d732e79be79bd79bdfb7605a785a2cb6a8b6b86549b2e45aa659eeb6bc6e855a3959a558555a5db346ad9dad25d6bbadbba711a7b94e934eab9ed667c3b0f1b6c9b6a9b719b0e5d806dbaeb66db67d6167621767b7c5aec3ee93bd937dba7d8dfd3d070d87d90eab1d5a1d7e73b472143a563ade9ace9cee3f7dc5f496e92f6758cf10cfd833e3b613cb29c4699d539bd347671767b97383f3888b894b82cb2e973e2e9b1bc6ddc8bde44a74f5715de17ad2f59d9bb39bc2eda8dbafee36ee69ee87dc9fcc349f299e593373d0c3c843e051e5d13f0b9f95306bdfac7e4f434f8167b5e7232f632f9157add7b0b7a577aaf761ef173ef63e729fe33ee33c37de32de595fcc37c0b7c8b7cb4fc36f9e5f85df437f23ff64ff7affd100a78025016703898141815b02fbf87a7c21bf8e3f3adb65f6b2d9ed418ca0b94115418f82ad82e5c1ad2168c8ec90ad21f7e798ce91ce690e85507ee8d6d00761e6618bc37e0c2785878557863f8e7088581ad131973577d1dc4373df44fa449644de9b67314f39af2d4a352a3eaa2e6a3cda37ba34ba3fc62e6659ccd5589d58496c4b1c392e2aae366e6cbedffcedf387e29de20be37b17982fc85d7079a1cec2f485a716a92e122c3a96404c884e3894f041102aa8168c25f21377258e0a79c21dc267222fd136d188d8435c2a1e4ef2482a4d7a92ec91bc357924c533a52ce5b98427a990bc4c0d4cdd9b3a9e169a76206d323d3abd31839291907142aa214d93b667ea67e66676cbac6585b2fec56e8bb72f1e9507c96bb390ac05592d0ab642a6e8545a28d72a07b267655766bfcd89ca3996ab9e2bcdedccb3cadb90379cef9fffed12c212e192b6a5864b572d1d58e6bdac6a39b23c7179db0ae315052b865606ac3cb88ab62a6dd54fabed5797ae7ebd267a4d6b815ec1ca82c1b5016beb0b550ae5857debdcd7ed5d4f582f59dfb561fa869d1b3e15898aae14db1797157fd828dc78e51b876fcabf99dc94b4a9abc4b964cf66d266e9e6de2d9e5b0e96aa97e6970e6e0dd9dab40ddf56b4edf5f645db2f97cd28dbbb83b643b9a3bf3cb8bc65a7c9cecd3b3f54a454f454fa5436eed2ddb561d7f86ed1ee1b7bbcf634ecd5db5bbcf7fd3ec9bedb5501554dd566d565fb49fbb3f73fae89aae9f896fb6d5dad4e6d71edc703d203fd07230eb6d7b9d4d51dd23d54528fd62beb470ec71fbefe9def772d0d360d558d9cc6e223704479e4e9f709dff71e0d3ada768c7bace107d31f761d671d2f6a429af29a469b539afb5b625bba4fcc3ed1d6eade7afc47db1f0f9c343c59794af354c969dae982d39367f2cf8c9d959d7d7e2ef9dc60dba2b67be763cedf6a0f6fefba1074e1d245ff8be73bbc3bce5cf2b874f2b2dbe51357b8579aaf3a5f6dea74ea3cfe93d34fc7bb9cbb9aaeb95c6bb9ee7abdb57b66f7e91b9e37ceddf4bd79f116ffd6d59e393dddbdf37a6ff7c5f7f5df16dd7e7227fdcecbbbd97727eeadbc4fbc5ff440ed41d943dd87d53f5bfedcd8efdc7f6ac077a0f3d1dc47f7068583cffe91f58f0f43058f998fcb860d86eb9e383e3939e23f72fde9fca743cf64cf269e17fea2fecbae17162f7ef8d5ebd7ced198d1a197f29793bf6d7ca5fdeac0eb19afdbc6c2c61ebec97833315ef456fbedc177dc771defa3df0f4fe47c207f28ff68f9b1f553d0a7fb93199393ff040398f3fc63332ddb000000206348524d00007a25000080830000f9ff000080e9000075300000ea6000003a980000176f925fc5460000014c4944415478daccd32f48834118c7f1c7e4aa623089c56e146d3318aea879a6b7acac182d5a0c5716144186c278750822e38a65305eb1ac2c787018c5f8a481b0f098be8629f867286231fcdaf37c78eeb93b01e42f91ff02244183548f96a49c0751100381c128168587532a072bac5d7450e0b700bee98906c3b8c7c665ff57c0f475eef0c9b0e4250547f5763016a07ab444390f28602096bc68b1cd76a160512c795c7ecd60cc1166ea27353a0a5a64b876c460c2927f76cd1188c5d9fdc6ce53cf60f07589fdb9ddc32dc2a8b214dbceb2ee0df5bc4e34eec126bb2df7e493d133641c20963ccb8db3d70deb62a755a19e0c348816d9a30b099f4cbe03e4ddf8779d564540050dd4ceaf504041def209b0b74cc5b6232b149f86025a3a6b6604fdd8fc1d20a0f3a1e9f069486c8fae2ce8c7e69f00c1e2a63f5e603d440c56edf55dbccffff94c2f0300a0ebee5f831d6a720000000049454e44ae426082";
	} else {
		$img = 'pass.png';
		$image = "89504e470d0a1a0a0000000d49484452000000100000001008060000001ff3ff61000000097048597300000ec300000ec301c76fa86400000a4f6943435050686f746f73686f70204943432070726f66696c65000078da9d53675453e9163df7def4424b8880944b6f5215082052428b801491262a2109104a8821a1d91551c1114545041bc8a088038e8e808c15512c0c8a0ad807e421a28e83a3888acafbe17ba36bd6bcf7e6cdfeb5d73ee7acf39db3cf07c0080c9648335135800ca9421e11e083c7c4c6e1e42e40810a2470001008b3642173fd230100f87e3c3c2b22c007be000178d30b0800c04d9bc0301c87ff0fea42995c01808401c07491384b08801400407a8e42a600404601809d98265300a0040060cb6362e300502d0060277fe6d300809df8997b01005b94211501a09100201365884400683b00accf568a450058300014664bc43900d82d00304957664800b0b700c0ce100bb200080c00305188852900047b0060c8232378008499001446f2573cf12bae10e72a00007899b23cb9243945815b082d710757572e1e28ce49172b14366102619a402ec27999193281340fe0f3cc0000a0911511e083f3fd78ce0eaecece368eb60e5f2deabf06ff226262e3fee5cfab70400000e1747ed1fe2c2fb31a803b06806dfea225ee04685e0ba075f78b66b20f40b500a0e9da57f370f87e3c3c45a190b9d9d9e5e4e4d84ac4425b61ca577dfe67c25fc057fd6cf97e3cfcf7f5e0bee22481325d814704f8e0c2ccf44ca51ccf92098462dce68f47fcb70bfffc1dd322c44962b9582a14e35112718e449a8cf332a52289429229c525d2ff64e2df2cfb033edf3500b06a3e017b912da85d6303f64b27105874c0e2f70000f2bb6fc1d4280803806883e1cf77ffef3ffd47a02500806649927100005e44242e54cab33fc708000044a0812ab0411bf4c1182cc0061cc105dcc10bfc6036844224c4c24210420a64801c726029ac82422886cdb01d2a602fd4401d34c051688693700e2ec255b80e3d700ffa61089ec128bc81090441c808136121da8801628a58238e08179985f821c14804128b2420c9881451224b91354831528a542055481df23d720239875c46ba913bc8003282fc86bc47319481b2513dd40cb543b9a8371a8446a20bd06474319a8f16a09bd072b41a3d8c36a1e7d0ab680fda8f3e43c730c0e8180733c46c302ec6c342b1382c099363cbb122ac0cabc61ab056ac03bb89f563cfb17704128145c0093604774220611e4148584c584ed848a8201c243411da093709038451c2272293a84bb426ba11f9c4186232318758482c23d6128f132f107b8843c437241289433227b9900249b1a454d212d246d26e5223e92ca99b34481a2393c9da646bb20739942c202bc885e49de4c3e433e41be421f25b0a9d624071a4f853e22852ca6a4a19e510e534e5066598324155a39a52dda8a15411358f5a42ada1b652af5187a81334759a39cd8316494ba5ada295d31a681768f769afe874ba11dd951e4e97d057d2cbe947e897e803f4770c0d861583c7886728199b18071867197718af984ca619d38b19c754303731eb98e7990f996f55582ab62a7c1591ca0a954a9526951b2a2f54a9aaa6aadeaa0b55f355cb548fa95e537dae46553353e3a909d496ab55aa9d50eb531b5367a93ba887aa67a86f543fa47e59fd890659c34cc34f43a451a0b15fe3bcc6200b6319b3782c216b0dab86758135c426b1cdd97c762abb98fd1dbb8b3daaa9a13943334a3357b352f394663f07e39871f89c744e09e728a797f37e8ade14ef29e2291ba6344cb931655c6baa96979658ab48ab51ab47ebbd36aeeda79da6bd45bb59fb810e41c74a275c2747678fce059de753d953dda70aa7164d3d3af5ae2eaa6ba51ba1bb4477bf6ea7ee989ebe5e809e4c6fa7de79bde7fa1c7d2ffd54fd6dfaa7f5470c5806b30c2406db0cce183cc535716f3c1d2fc7dbf151435dc34043a561956197e18491b9d13ca3d5468d460f8c69c65ce324e36dc66dc6a326062621264b4dea4dee9a524db9a629a63b4c3b4cc7cdcccda2cdd699359b3d31d732e79be79bd79bdfb7605a785a2cb6a8b6b86549b2e45aa659eeb6bc6e855a3959a558555a5db346ad9dad25d6bbadbba711a7b94e934eab9ed667c3b0f1b6c9b6a9b719b0e5d806dbaeb66db67d6167621767b7c5aec3ee93bd937dba7d8dfd3d070d87d90eab1d5a1d7e73b472143a563ade9ace9cee3f7dc5f496e92f6758cf10cfd833e3b613cb29c4699d539bd347671767b97383f3888b894b82cb2e973e2e9b1bc6ddc8bde44a74f5715de17ad2f59d9bb39bc2eda8dbafee36ee69ee87dc9fcc349f299e593373d0c3c843e051e5d13f0b9f95306bdfac7e4f434f8167b5e7232f632f9157add7b0b7a577aaf761ef173ef63e729fe33ee33c37de32de595fcc37c0b7c8b7cb4fc36f9e5f85df437f23ff64ff7affd100a78025016703898141815b02fbf87a7c21bf8e3f3adb65f6b2d9ed418ca0b94115418f82ad82e5c1ad2168c8ec90ad21f7e798ce91ce690e85507ee8d6d00761e6618bc37e0c2785878557863f8e7088581ad131973577d1dc4373df44fa449644de9b67314f39af2d4a352a3eaa2e6a3cda37ba34ba3fc62e6659ccd5589d58496c4b1c392e2aae366e6cbedffcedf387e29de20be37b17982fc85d7079a1cec2f485a716a92e122c3a96404c884e3894f041102aa8168c25f21377258e0a79c21dc267222fd136d188d8435c2a1e4ef2482a4d7a92ec91bc357924c533a52ce5b98427a990bc4c0d4cdd9b3a9e169a76206d323d3abd31839291907142aa214d93b667ea67e66676cbac6585b2fec56e8bb72f1e9507c96bb390ac05592d0ab642a6e8545a28d72a07b267655766bfcd89ca3996ab9e2bcdedccb3cadb90379cef9fffed12c212e192b6a5864b572d1d58e6bdac6a39b23c7179db0ae315052b865606ac3cb88ab62a6dd54fabed5797ae7ebd267a4d6b815ec1ca82c1b5016beb0b550ae5857debdcd7ed5d4f582f59dfb561fa869d1b3e15898aae14db1797157fd828dc78e51b876fcabf99dc94b4a9abc4b964cf66d266e9e6de2d9e5b0e96aa97e6970e6e0dd9dab40ddf56b4edf5f645db2f97cd28dbbb83b643b9a3bf3cb8bc65a7c9cecd3b3f54a454f454fa5436eed2ddb561d7f86ed1ee1b7bbcf634ecd5db5bbcf7fd3ec9bedb5501554dd566d565fb49fbb3f73fae89aae9f896fb6d5dad4e6d71edc703d203fd07230eb6d7b9d4d51dd23d54528fd62beb470ec71fbefe9def772d0d360d558d9cc6e223704479e4e9f709dff71e0d3ada768c7bace107d31f761d671d2f6a429af29a469b539afb5b625bba4fcc3ed1d6eade7afc47db1f0f9c343c59794af354c969dae982d39367f2cf8c9d959d7d7e2ef9dc60dba2b67be763cedf6a0f6fefba1074e1d245ff8be73bbc3bce5cf2b874f2b2dbe51357b8579aaf3a5f6dea74ea3cfe93d34fc7bb9cbb9aaeb95c6bb9ee7abdb57b66f7e91b9e37ceddf4bd79f116ffd6d59e393dddbdf37a6ff7c5f7f5df16dd7e7227fdcecbbbd97727eeadbc4fbc5ff440ed41d943dd87d53f5bfedcd8efdc7f6ac077a0f3d1dc47f7068583cffe91f58f0f43058f998fcb860d86eb9e383e3939e23f72fde9fca743cf64cf269e17fea2fecbae17162f7ef8d5ebd7ced198d1a197f29793bf6d7ca5fdeac0eb19afdbc6c2c61ebec97833315ef456fbedc177dc771defa3df0f4fe47c207f28ff68f9b1f553d0a7fb93199393ff040398f3fc63332ddb000000206348524d00007a25000080830000f9ff000080e9000075300000ea6000003a980000176f925fc546000001704944415478daccd33f4b5c411487e1938f10b196347e072116875559f5221883108886c04ad8461b0b4b93721a0b450826c27a550491658a086141630c68b3637265b04c9a08536d210b19d0f0a67017a22e064963f12bcfc39c3f2380fc4fe4be005e08568a8b5d924bad04900802b5cbc44cf8beccd8fc63f21b150270570053326411ead96b9e6c56ef04b46da709c647d429de2614f76b2d018a8b5de4524b0022883a95ca9709faaac3a853a23724e936b5162db4cfbd9fe4d9f12b960e9e92ffb84284077bc7d3e7ea94f19397f41ce57ebc599e393b8c50bb39c46ac7ecc238cffd14ea947795fc7961678fd1fd5106bf0d5da853d2f240ddf8c861445a0112bda17bc3a04ee93bea3dadac8f31d7e83bfbfce2e7c0875d8c8f721b20cde7f77fedffad4e51a7102c936b5b04208034730d88cd3cccca09854f8106f06bb554c086abc5b70102e1912d25185f272b5faecc86abc5ff0284988d98b79d0cdb8c083db171177fe7fe7ca63f0300dcc3ddf4e0fade9c0000000049454e44ae426082";
	}

	// send the right headers
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header("Content-Type: image/png");
	header("Content-Length: " . strlen($image));
	header('Content-Disposition: filename="' . $which . '.png"');
	// dump the picture
	$image = hex2bin($image);
	echo $image;
}

function shutDownFunction() {
	global $extension;

	$error = error_get_last();
	if ($error && !in_array($error['type'], array(E_USER_ERROR, E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE))) {
		$msg = '<span class="error">' . sprintf(gettext('Plugin:%1$s *ERROR* "%2$s" in %3$s on line %4$s'), $extension, $error['message'], $error['file'], $error['line']) . '</span>';
		setupLog($msg, true);
		if ($extension) {
			enableExtension($extension, 0);
		}
		setupLog(sprintf(gettext('Plugin:%1$s setup failed.'), $extension));
	}
	error_reporting(0); //	bypass any further error handling
}
?>