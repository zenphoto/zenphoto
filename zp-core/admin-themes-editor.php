<?php
/**
 * Theme file editor
 * @package admin
 * @author Ozh
 */
// force UTF-8 �

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');

admin_securityChecks(THEMES_RIGHTS, currentRelativeURL());

if (!isset($_GET['theme'])) {
	header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-themes.php");
	exitZP();
}

function isTextFile($file, $ok_extensions = array('css', 'php', 'js', 'txt', 'inc')) {
	$path_info = pathinfo($file);
	$ext = (isset($path_info['extension']) ? strtolower($path_info['extension']) : '');
	return (!empty($ok_extensions) && (in_array($ext, $ok_extensions) ) );
}

$message = $file_to_edit = $file_content = null;
$themes = $_zp_gallery->getThemes();
$theme = sanitize($_GET['theme']);
$themedir = SERVERPATH . '/themes/' . internalToFilesystem($theme);
$themefiles = listDirectoryFiles($themedir);
$themefiles_to_ext = array();
foreach ($themefiles as $file) {
	if (isTextFile($file)) {
		$path_info = pathinfo($file);
		$themefiles_to_ext[$path_info['extension']][] = $file; // array(['php']=>array('file.php', 'image.php'),['css']=>array('style.css'))
	} else {
		unset($themefiles[$file]); // $themefile will eventually have all editable files and nothing else
	}
}
if (isset($_GET['file']))
	$file_to_edit = str_replace('\\', '/', realpath(SERVERPATH . '/themes/' . internalToFilesystem($theme) . '/' . sanitize($_GET['file'])));
// realpath() to take care of ../../file.php schemes, str_replace() to sanitize Win32 filenames
// Handle POST that updates a file
if (isset($_POST['action']) && $_POST['action'] == 'edit_file' && $file_to_edit) {
	XSRFdefender('edit_theme');
	$file_content = sanitize($_POST['newcontent'], 0);
	$theme = urlencode($theme);
	if (is_writeable($file_to_edit)) {
		//is_writable() not always reliable, check return value. see comments @ http://uk.php.net/is_writable
		$f = @fopen($file_to_edit, 'w+');
		if ($f !== FALSE) {
			@fwrite($f, $file_content);
			fclose($f);
			clearstatcache();
			$message = gettext('File updated successfully');
		} else {
			$message = gettext('Could not write file. Please check its write permissions');
		}
	} else {
		$message = gettext('Could not write file. Please check its write permissions');
	}
}

// Get file contents
if ($file_to_edit) {
	$file_content = @file_get_contents($file_to_edit);
	$file_content = html_encode($file_content);
	$what = html_encode('edit=>' . basename($file_to_edit));
} else {
	$what = 'edit';
}

printAdminHeader('themes', $what);
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';


// If we're attempting to edit a file from a bundled theme, this is an illegal attempt
if (!themeIsEditable($theme))
	zp_error(gettext('Cannot edit this theme!'));

// If we're attempting to edit a file that's not a text file or that does not belong to the theme directory, this is an illegal attempt
if ($file_to_edit) {
	if (!in_array($file_to_edit, $themefiles) or !isTextFile($file_to_edit) or filesize($file_to_edit) == 0)
		zp_error(gettext('Cannot edit this file!'));
}
?>


<h1><?php echo gettext('Theme File Editor'); ?></h1>

<?php
if ($message) {
	echo '<div class="messagebox fade-message">';
	echo "<h2>$message</h2>";
	echo '</div>';
}
?>

<p class="buttons">
	<a title="<?php echo gettext('Back to the theme list'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-themes.php">
		<img	src="images/arrow_left_blue_round.png" alt="" />
		<strong><?php echo gettext("Back"); ?></strong>
	</a>
</p>
<br class="clearall" />
<div id="theme-editor">

	<div id="files">
		<?php
		foreach ($themefiles_to_ext as $ext => $files) {
			echo '<h2 class="h2_bordered">';
			switch ($ext) {
				case 'php':
					echo gettext('Theme template files (.php)');
					break;
				case 'js':
					echo gettext('JavaScript files (.js)');
					break;
				case 'css':
					echo gettext('Style sheets (.css)');
					break;
				default:
					echo gettext('Other text files');
			}
			echo '</h2><ul>';
			foreach ($files as $file) {
				$file = str_replace($themedir . '/', '', $file);
				echo "<li><a title='" . gettext('Edit this file') . "' href='?theme=$theme&file=$file'>$file</a></li>";
			}
			echo '</ul>';
		}
		?>
	</div>


<?php if ($file_to_edit) { ?>
		<div id="editor">
			<h2 class="h2_bordered"><?php echo sprintf(gettext('File <tt>%s</tt> from theme %s'), sanitize($_GET['file']), $themes[$theme]['name']); ?></h2>
			<form method="post" action="">
	<?php XSRFToken('edit_theme'); ?>
				<p><textarea cols="70" rows="35" name="newcontent" id="newcontent"><?php echo $file_content ?></textarea></p>
				<input type="hidden" name="action" value="edit_file"/>
				<p class="buttons">
					<button type="submit" value="<?php echo gettext('Update File') ?>" title="<?php echo gettext("Update File"); ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Update File"); ?></strong></button>
					<button type="reset" value="<?php echo gettext('Reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
				</p>
				<br class="clearall" />
			</form>
		</div>

<?php } else { ?>

		<p><?php echo gettext('Select a file to edit from the list on your right hand. Keep in mind that you can <strong>break everything</strong> if you are not careful when updating files.'); ?></p>

<?php } ?>

</div> <!-- theme-editor -->

<?php
echo "\n" . '</div>'; //content
echo "\n" . '</div>'; //main

printAdminFooter();
echo "\n</body>";
echo "\n</html>";
?>



