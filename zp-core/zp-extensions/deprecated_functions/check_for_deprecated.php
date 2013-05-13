<?php
/**
 * Check for use of deprecated functions
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once(dirname(__FILE__).'/functions.php');

admin_securityChecks(NULL, currentRelativeURL());

$path = '';
$selected = 0;
if (isset($_GET['action'])) {
	XSRFdefender('deprecated');
	$zplist = unserialize(getOption('Zenphoto_theme_list'));
	$deprecated = new deprecated_functions();
	$list = array_diff($deprecated->listed_functions, $deprecated->internalFunctions);
	$pattern = '([^function^\->]\s+)'.implode('[\(]|([^function^\->]\s+)',$list).'[\(]';
	$report = array();
	$selected = sanitize_numeric($_POST['target']);
}

$zenphoto_tabs['overview']['subtabs']=array(gettext('Deprecated')=>'');
printAdminHeader('overview','deprecated');
?>
<?php
echo '</head>'."\n";
?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div id="tab_deprecated" class="tabbox">
				<h1><?php	echo gettext("Locate calls on deprecated functions."); ?></h1>
				<form action="?action=search" method="post">
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
								<?php echo gettext('In Zenphoto code'); ?>
							</option>
							<?php
						}
						?>
					</select>
					<br class="clearall" /><br />
					<span class="buttons">
						<button type="submit" title="<?php echo gettext("Search"); ?>" onclick="$('#outerbox').html('');" ><img src="../../images/magnify.png" alt="" /><strong><?php echo gettext("Search"); ?></strong></button>
					</span>
					<span id="progress"></span>
				</form>

				<br class="clearall" />
				<p class="notebox"><?php echo gettext('<strong>NOTE:</strong> This search will have false positives for instance when the function name appears in a comment or quoted string.'); ?></p>
				<?php
				if (isset($_GET['action'])) {
					?>
					<div style="background-color: white;" id="outerbox">
						<?php
						switch ($selected) {
							case '1':
								$path = SERVERPATH.'/'.THEMEFOLDER;
								$_files = array();
								getPHPFiles($path,$zplist);
								$output = listUses($path);
								break;
							case '2':
								$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER;
								$_files = array();
								getPHPFiles($path,array());
								$output = listUses($path);
								break;
							case '3':
								$path = SERVERPATH.'/'.ZENFOLDER;
								$_files = array();
								getPHPFiles($path,array());
								$output = listUses($path);
								foreach ($zplist as $theme) {
									$path = SERVERPATH.'/'.THEMEFOLDER.'/'.$theme;
									getPHPFiles($path,array());
									$output = $output || listUses(SERVERPATH);
								}
								break;
						}
						if (!$output) {
							?>
							<p class="messagebox"><?php echo gettext('No calls on deprecated functions were found.'); ?></p>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</body>
</html>
