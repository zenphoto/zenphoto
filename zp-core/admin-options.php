<?php
/**
 * provides the Options tab of admin
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tag_suggest.php');

if (isset($_GET['tab'])) {
	$_zp_admin_subtab = sanitize($_GET['tab'], 3);
} else {
	if (isset($_POST['saveoptions'])) {
		$_zp_admin_subtab = sanitize($_POST['saveoptions'], 3);
	} else {
		$_zp_admin_subtab = 'general';
	}
}
require_once(SERVERPATH . '/' . ZENFOLDER . '/admin_options/' . $_zp_admin_subtab . '.php');

admin_securityChecks($optionRights, currentRelativeURL());
define('PLUGINS_PER_PAGE', max(1, getOption('plugins_per_page')));

/* handle posts */
if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	$themeswitch = false;
	if ($action == 'saveoptions') {
		XSRFdefender('saveoptions');

		list($returntab, $notify, $themealbum, $themename, $failed) = saveOptions();

		/*		 * * custom options ** */
		if (!$failed) { // was really a save.
			$returntab = processCustomOptionSave($returntab, $themename, $themealbum);
		}

		if (empty($notify))
			$notify = '?saved';
		header("Location: " . $notify . $returntab);
		exitZP();
	}
}
printAdminHeader('options');
?>
<script src='js/spectrum/spectrum.js'></script>
<link rel='stylesheet' href='js/spectrum/spectrum.css' />
<?php
$table = NULL;

if ($_zp_admin_subtab == 'gallery' || $_zp_admin_subtab == 'image') {
	if ($_zp_admin_subtab == 'image') {
		$table = 'images';
		$targetid = 'customimagesort';
	} else {
		$table = 'albums';
		$targetid = 'customalbumsort';
	}
	$result = db_list_fields($table);
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'" . $row['Field'] . "'";
		}
		sort($dbfields);
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		$(function () {
			$('#<?php echo $targetid; ?>').tagSuggest({
				tags: [
	<?php echo implode(',', $dbfields); ?>
				]
			});
		});
		// ]]> -->
	</script>
	<?php
}
zp_apply_filter('texteditor_config', 'zenphoto');
Zenphoto_Authority::printPasswordFormJS();
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			/* Page code */
			$subtab = getCurrentTab();
			$name = getTabName('options', $subtab);
			zp_apply_filter('admin_note', 'options', $subtab);
			?>
			<h1>
				<?php
				printf(gettext('%1$s options'), $name);
				?>
			</h1>
			<?php
			if (isset($_GET['tag_parse_error'])) {
				echo '<div class="errorbox fade-message">';
				echo "<h2>";
				if ($_GET['tag_parse_error'] === '0') {
					echo gettext("Forbidden tag.");
				} else {
					echo gettext("Your Allowed tags change did not parse successfully.");
				}
				echo "</h2>";
				echo '</div>';
			}
			if (isset($_GET['post_error'])) {
				echo '<div class="errorbox">';
				echo "<h2>" . gettext('Error') . "</h2>";
				echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
				echo '</div>';
			}
			if (isset($_GET['saved'])) {
				echo '<div class="messagebox fade-message">';
				echo "<h2>" . gettext("Applied") . "</h2>";
				echo '</div>';
			}
			if (isset($_GET['custom'])) {
				echo '<div class="errorbox">';
				echo '<h2>' . html_encode(sanitize($_GET['custom'])) . '</h2>';
				echo '</div>';
			}
			if (isset($_GET['missing'])) {
				echo '<div class="errorbox">';
				echo '<h2>' . gettext('Your browser did not post all the fields. Some options may not have been set.') . '</h2>';
				echo '</div>';
			}
			if (isset($_GET['maxsize'])) {
				echo '<div class="errorbox">';
				echo '<h2>' . gettext('Maximum image size must be greater than zero.') . '</h2>';
				echo '</div>';
			}

			if (isset($_GET['mismatch'])) {
				echo '<div class="errorbox fade-message">';
				switch ($_GET['mismatch']) {
					case 'user':
						echo "<h2>" . sprintf(gettext("You must supply a password for the <em>%s</em> guest user"), html_encode(ucfirst($subtab))) . "</h2>";
						break;
					default:
						echo "<h2>" . gettext('Your passwords did not match.') . "</h2>";
						break;
				}
				echo '</div>';
			}

			if (isset($_GET['cookiepath']) && @$_COOKIE['zenphoto_cookie_path'] != getOption('zenphoto_cookie_path')) {
				setOption('zenphoto_cookie_path', NULL);
				?>
				<div class="errorbox">
					<h2><?php echo gettext('The path you selected resulted in cookies not being retrievable. It has been reset.'); ?></h2>
				</div>
				<?php
			}

			getOptionContent();
			?>

		</div><!-- end of content -->
	</div><!-- end of main -->
	<?php printAdminFooter(); ?>

</body>
</html>

