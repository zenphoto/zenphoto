<?php
/**
 * provides the Options tab of admin
 * @package zpcore\admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-config.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/deprecated/functions-config.php');

admin_securityChecks(OPTIONS_RIGHTS, currentRelativeURL());

define('PLUGINS_PER_PAGE', max(1, getOption('plugins_per_page')));
if (isset($_GET['pagenumber'])) {
	$pagenumber = sanitize_numeric($_GET['pagenumber']);
} else {
	if (isset($_POST['pagenumber'])) {
		$pagenumber = sanitize_numeric($_POST['pagenumber']);
	} else {
		$pagenumber = 0;
	}
}

if (!isset($_GET['page'])) {
	if (array_key_exists('options', $_zp_admin_menu)) {
		$_GET['page'] = 'options';
	} else {
		$_GET['page'] = 'users'; // must be a user with no options rights
	}
}
$_current_tab = sanitize($_GET['page'], 3);

/* handle posts */
if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	$themeswitch = false;
	if ($action == 'saveoptions') {
		XSRFdefender('saveoptions');
		$table = NULL;

		$notify = '';
		$returntab = '';
		$themealbum = $themename = NULL;

		/*
		 * General options
		 */
		if (isset($_POST['savegeneraloptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-general_save.php';
		}

		/*
		 * Gallery options
		 */
		if (isset($_POST['savegalleryoptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-gallery_save.php';
		}

		/*
		 * Search options
		 */
		if (isset($_POST['savesearchoptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-search_save.php';
		}

		/*
		 * Image options
		 */
		if (isset($_POST['saveimageoptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-image_save.php';
		}
		/*
		 * Theme options
		 */
		if (isset($_POST['savethemeoptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-theme_save.php';
		}
		/*
		 * Plugin Options
		 */
		if (isset($_POST['savepluginoptions'])) {
			if (isset($_POST['checkForPostTruncation'])) {
				// all plugin options are handled by the custom option code.
				if (isset($_GET['single'])) {
					$returntab = "&tab=plugin&single=" . sanitize($_GET['single']);
				} else {
					$returntab = "&tab=plugin&pagenumber=$pagenumber";
				}
			} else {
				$notify = '?post_error';
			}
		}
		/*
		 * Security Options
		 */
		if (isset($_POST['savesecurityoptions'])) {
			require_once SERVERPATH .'/' . ZENFOLDER . '/admin-options/inc-options-security_save.php';
		}
		/*		 * * custom options ** */
		if (!$themeswitch) { // was really a save.
			$returntab = processCustomOptionSave($returntab, $themename, $themealbum);
		}

		if (empty($notify)) {
			$notify = '?saved';
		}
		redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php' . $notify . $returntab);
	}
}
printAdminHeader($_current_tab);
?>
<script src="js/farbtastic.js"></script>
<link rel="stylesheet" href="js/farbtastic.css" type="text/css" />
<?php
if ($_zp_admin_current_subpage == 'gallery' || $_zp_admin_current_subpage == 'image') {
	if ($_zp_admin_current_subpage == 'image') {
		$table = 'images';
		$targetid = 'customimagesort';
	} else {
		$table = 'albums';
		$targetid = 'customalbumsort';
	}
	$result = $_zp_db->getFields($table);
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'" . $row['Field'] . "'";
		}
		sort($dbfields);
	}
	?>
	<script src="js/encoder.js"></script>
	<script src="js/tag.js"></script>
	<script>
						$(function () {
						$('#<?php echo $targetid; ?>').tagSuggest({
						tags: [
	<?php echo implode(',', $dbfields); ?>
						]
						});
						});
	</script>
	<?php
}
filter::applyFilter('texteditor_config', 'zenphoto');
Authority::printPasswordFormJS();
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			/* Page code */
			?>
			<div id="container">
				<?php
				$subtab = getSubtabs();
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

				if (isset($_GET['cookiepath']) && @$_COOKIE['zpcms_cookie_path'] != getOption('zpcms_cookie_path')) {
					setOption('zenphoto_cookie_path', NULL);
					?>
					<div class="errorbox">
						<h2><?php echo gettext('The path you selected resulted in cookies not being retrievable. It has been reset.'); ?></h2>
					</div>
					<?php
				}
				printSubtabs();
				/*
				 * General options
				 */
				if ($subtab == 'general' && zp_loggedin(OPTIONS_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-general.php';
				}
				/*
				 * Gallery options
				 */
				if ($subtab == 'gallery' && zp_loggedin(OPTIONS_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-gallery.php';
				}
				/*
				 * Search options
				 */
				if ($subtab == 'search' && zp_loggedin(OPTIONS_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-search.php';
				}
				/*
				 * Image options
				 */
				if ($subtab == 'image' && zp_loggedin(OPTIONS_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-image.php';
				}
				/*
				 * Theme options
				 */
				if ($subtab == 'theme' && zp_loggedin(THEMES_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-theme.php';
				}
				/*
				 * Plugin optioms
				 */
				if ($subtab == 'plugin' && zp_loggedin(ADMIN_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-plugin.php';
				}
				/*
				 * Security options
				 */
				if ($subtab == 'security' && zp_loggedin(ADMIN_RIGHTS)) {
					require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/inc-options-security.php';
				}
				?>
			</div><!-- end of container -->

		</div><!-- end of content -->
	</div><!-- end of main -->
	<?php printAdminFooter(); ?>


</body>
</html>

