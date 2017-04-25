<?php
/**
 * Check for use of deprecated functions
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(dirname(__FILE__) . '/functions.php');

admin_securityChecks(NULL, currentRelativeURL());

$path = '';
$selected = 0;
if (isset($_GET['action'])) {
	XSRFdefender('deprecated');
	$zplist = array();
	foreach ($_zp_gallery->getThemes() as $theme => $data) {
		if (protectedTheme($theme)) {
			$zplist[] = $theme;
		}
	}
	$deprecated = new deprecated_functions();
	$list = array();
	foreach ($deprecated->listed_functions as $details) {
		$func = preg_quote($details['function']);
		$list[$func] = $func;
	}

	$pattern = '/(->\s*|::\s*|\b)(' . implode('|', $list) . ')\s*\(/i';
	$report = array();
	$selected = sanitize_numeric($_POST['target']);
}
printAdminHeader('development', 'deprecated');
?>
<?php
echo '</head>' . "\n";
?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'development', ''); ?>
			<h1><?php echo gettext("Locate calls on deprecated functions."); ?></h1>
			<div id="tab_deprecated" class="tabbox">
				<form action="?action=search&tab=checkdeprecated" method="post">
					<?php XSRFToken('deprecated'); ?>
					<select name="target">
						<option value=1<?php if ($selected <= 1) echo ' selected="selected"'; ?>>
							<?php echo gettext('In Themes'); ?>
						</option>
						<option value=2<?php if ($selected == 2) echo ' selected="selected"'; ?>>
							<?php echo gettext('In User plugins'); ?>
						</option>
						<?php
						if (TEST_RELEASE) {
							?>
							<option value=3<?php if ($selected == 3) echo ' selected="selected"'; ?>>
								<?php echo gettext('In ZenPhoto20 code'); ?>
							</option>
							<?php
						}
						?>
						<option value=4<?php if ($selected == 4) echo ' selected="selected"'; ?>>
							<?php echo gettext('In Codeblocks'); ?>
						</option>
					</select>
					<br class="clearall"><br />
					<span class="buttons">
						<button type="submit" title="<?php echo gettext("Search"); ?>" onclick="$('#outerbox').html('');" ><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/magnify.png" alt="" /><strong><?php echo gettext("Search"); ?></strong></button>
					</span>
					<span id="progress"></span>
				</form>

				<br class="clearall">
				<p class="notebox"><?php echo gettext('<strong>NOTE:</strong> This search will have false positives for instance when the function name appears in a comment or quoted string. Functions flagged with an "*" are class methods. Ones flagged "+" have deprecated parameters.'); ?></p>
				<?php
				if (isset($_GET['action'])) {
					?>
					<div style="background-color: white;" id="outerbox">
						<?php
						switch ($selected) {
							case '1':
								$path = SERVERPATH . '/' . THEMEFOLDER;
								listUses(getPHPFiles($path, $zplist), $path, $pattern);
								break;
							case '2':
								$zplist = array();
								$paths = getPluginFiles('*.php');
								foreach ($paths as $plugin => $path) {
									if (strpos($path, USER_PLUGIN_FOLDER) !== false) {
										$p = file_get_contents($path);
										$i = strpos($p, '* @category');
										if (($key = $i) !== false) {
											$key = strtolower(trim(substr($p, $i + 11, strpos($p, "\n", $i) - $i - 11)));
											if ($key == 'package') {
												$zplist[] = stripSuffix(str_replace(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/', '', $path));
											}
										}
									}
								}
								$path = SERVERPATH . '/' . USER_PLUGIN_FOLDER;
								listUses(getPHPFiles($path, $zplist), $path, $pattern);
								break;
							case '3':
								$paths = getPluginFiles('*.php');
								foreach ($paths as $plugin => $path) {
									if (strpos($path, USER_PLUGIN_FOLDER) == false) {
										unset($paths[$plugin]);
									} else {
										$p = file_get_contents($path);
										$i = strpos($p, '* @category');
										if (($key = $i) !== false) {
											$key = strtolower(trim(substr($p, $i + 11, strpos($p, "\n", $i) - $i - 11)));
											if ($key == 'package') {
												unset($paths[$plugin]);
											}
										}
									}
								}
								$userfiles = array();
								foreach ($paths as $path) {
									$userfiles[] = stripSuffix(basename($path));
								}

								$path = SERVERPATH . '/' . ZENFOLDER;
								echo "<em>" . ZENFOLDER . "</em><br />\n";
								listUses(getPHPFiles($path, array()), $path, $pattern);
								$path = SERVERPATH . '/' . USER_PLUGIN_FOLDER;
								echo "<em>" . USER_PLUGIN_FOLDER . "</em><br />\n";
								listUses(getPHPFiles($path, $userfiles), $path, $pattern);
								echo "<em>" . THEMEFOLDER . "</em><br /><br />\n";
								foreach ($zplist as $theme) {
									$path = SERVERPATH . '/' . THEMEFOLDER . '/' . $theme;
									echo "<em>" . $theme . "</em><br />\n";
									listUses(getPHPFiles($path, array()), $path, $pattern);
								}
								break;
							case 4:
								listDBUses($pattern);
								break;
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>
</body>
</html>
