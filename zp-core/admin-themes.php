<?php
/**
 * provides the Themes tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(THEMES_RIGHTS, currentRelativeURL());

$_GET['page'] = 'themes';

/* handle posts */
$message = null; // will hold error/success message displayed in a fading box
if (isset($_GET['action'])) {
	XSRFdefender('admin-themes');
	switch ($_GET['action']) {
		case 'settheme':
			if (isset($_GET['theme'])) {
				$alb = sanitize_path($_GET['themealbum']);
				$newtheme = sanitize($_GET['theme']);
				if (empty($alb)) {
					$_zp_gallery->setCurrentTheme($newtheme);
					$_zp_gallery->save();
					$_set_theme_album = NULL;
				} else {
					$_set_theme_album = newAlbum($alb);
					$oldtheme = $_set_theme_album->getAlbumTheme();
					$_set_theme_album->setAlbumTheme($newtheme);
					$_set_theme_album->save();
				}
				$opthandler = SERVERPATH.'/'.THEMEFOLDER.'/'.$newtheme.'/themeoptions.php';
				if (file_exists($opthandler)) {
					require_once($opthandler);
					$opt = new ThemeOptions();	//	prime the default options!
				}
				/* set any "standard" options that may not have been covered by the theme */
				standardThemeOptions($newtheme, $_set_theme_album);
				header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-themes.php?themealbum=".sanitize($_GET['themealbum']));
				exitZP();
			}
			break;
			// Duplicate a theme
		case 'copytheme':
			if (isset($_GET['source']) && isset($_GET['target']) && isset($_GET['name'])) {
				$message = copyThemeDirectory(sanitize($_GET['source'],3), sanitize($_GET['target'],3), sanitize($_GET['name'],3));
			}
			$_zp_gallery = new Gallery();	//	flush out remembered themes
			break;
		case 'deletetheme':
			if (isset($_GET['theme'])) {
				if (deleteThemeDirectory(SERVERPATH . '/themes/'.internalToFilesystem($theme = sanitize($_GET['theme'],3)))) {
					$message = sprintf(gettext("Theme <em>%s</em> removed."),html_encode($theme));
				} else {
					$message = sprintf(gettext('Error removing theme <em>%s</em>'),html_encode($theme));
				}
				$_zp_gallery = new Gallery();	//	flush out remembered themes
				break;
			}
	}
}


printAdminHeader('themes');

// Script for the "Duplicate theme" feature
?>

<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/sprintf.js"></script>
<script type="text/javascript">
	//<!-- <![CDATA[
	function copyClick(source) {
		var targetname = prompt('<?php echo gettext('New theme name?'); ?>', sprintf('<?php echo gettext('Copy of %s');?>',source));
		if (targetname) {
			var targetdir = prompt('<?php echo gettext('Theme folder name?'); ?>', targetname.toLowerCase().replace(/ /g,'_').replace(/[^A-Za-z0-9_]/g,'') );
			if (targetdir) {
				launchScript('',['action=copytheme','XSRFToken=<?php echo getXSRFToken('admin-themes')?>','source='+encodeURIComponent(source),'target='+encodeURIComponent(targetdir),'name='+encodeURIComponent(targetname)]);
				return false;
			}
		}
		return false;
	}
	// ]]> -->
</script>

<?php
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';

	$galleryTheme = $_zp_gallery->getCurrentTheme();
	$themelist = array();
	if (zp_loggedin(ADMIN_RIGHTS)) {
		$gallery_title = $_zp_gallery->getTitle();
		if ($gallery_title != gettext("Gallery")) {
			$gallery_title .= ' ('.gettext("Gallery").')';
		}
		$themelist[$gallery_title] = '';
	}
	$albums = $_zp_gallery->getAlbums(0);
	foreach ($albums as $alb) {
		$album = newAlbum($alb);
		if ($album->isMyItem(THEMES_RIGHTS)) {
			$key = $album->getTitle();
			if ($key != $alb) {
				$key .= " ($alb)";
			}
			$themelist[$key] = $alb;
		}
	}
	if (!empty($_REQUEST['themealbum'])) {
		$alb = sanitize_path($_REQUEST['themealbum']);
		$album = newAlbum($alb);
		$albumtitle = $album->getTitle();
		$themename = $album->getAlbumTheme();
		$current_theme = $themename;
	} else {
		$current_theme = $galleryTheme;
		foreach ($themelist as $albumtitle=>$alb) break;
		if (empty($alb)) {
			$themename = $_zp_gallery->getCurrentTheme();
		} else {
			$alb = sanitize_path($alb);
			$album = newAlbum($alb);
			$albumtitle = $album->getTitle();
			$themename = $album->getAlbumTheme();
		}
	}
	$themes = $_zp_gallery->getThemes();
	if (empty($themename)) {
		$current_theme = $galleryTheme;
		$theme = $themes[$galleryTheme];
		$themenamedisplay = '</em><small>'.gettext("no theme assigned, defaulting to Gallery theme").'</small><em>';
		$gallerydefault = true;
	} else {
		$theme = $themes[$themename];
		$themenamedisplay = $theme['name'];
		$gallerydefault = false;
	}

	if (count($themelist) > 1) {
		echo '<form action="#" method="post">';
		echo gettext("Show theme for: ");
		echo '<select id="themealbum" name="themealbum" onchange="this.form.submit()">';
		generateListFromArray(array(pathurlencode($alb)), $themelist, false, true);
		echo '</select>';
		echo '</form>';
	}
	if (count($themelist) == 0) {
		echo '<div class="errorbox" id="no_themes">';
		echo  "<h2>".gettext("There are no themes for which you have rights to administer.")."</h2>";
		echo '</div>';
	} else {

	echo "<h1>".sprintf(gettext('Current theme for <code><strong>%1$s</strong></code>: <em>%2$s</em>'),$albumtitle,$themenamedisplay);
	if (!empty($alb) && !empty($themename)) {
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a class="reset" onclick="launchScript('',['action=settheme','themealbum=<?php echo pathurlencode($album->name); ?>','theme=','XSRFToken=<?php echo getXSRFToken('admin-themes'); ?>']);" title="<?php printf(gettext('Clear theme assignment for %s'),$album->name); ?>">
			<img src="images/fail.png" style="border: 0px;" alt="<?php echo gettext('Clear theme assignment'); ?>" />
		</a>
		<?php
	}
	echo "</h1>\n";
?>

<?php if ($message) {
	echo '<div class="messagebox fade-message">';
	echo  "<h2>$message</h2>";
	echo '</div>';
} ?>

<p>
	<?php echo gettext('Themes allow you to visually change the entire look and feel of your gallery. Theme files are located in your Zenphoto <code>/themes</code> folder.'); ?>
	<?php echo gettext('You can download more themes from the <a href="http://www.zenphoto.org/theme/">zenphoto themes page</a>.'); ?>
	<?php echo gettext('Place the downloaded themes in the <code>/themes</code> folder and they will be available for your use.') ?>
</p>

<p>
	<?php echo gettext("You can edit files from custom themes. Official themes shipped with Zenphoto are not editable, since your changes would be lost on next update."); ?>
	<?php echo gettext("If you want to customize an official theme, please first <em>duplicate</em> it. This will place a copy in your <code>/themes</code> folder for you to edit."); ?>
</p>
<table class="bordered">
	<tr>
		<th colspan="2"><b><?php echo gettext('Installed themes'); ?></b></th>
		<th><b><?php echo gettext('Action'); ?></b></th>
	</tr>
	<?php
	$zenphoto_date = date('Y-m-d H:i:s',filemtime(SERVERPATH.'/'.ZENFOLDER.'/version.php'));
$current_theme_style = 'class="currentselection"';
foreach($themes as $theme => $themeinfo) {
	$style = ($theme == $current_theme) ? ' '.$current_theme_style : '';
	$themedir = SERVERPATH . '/themes/'.internalToFilesystem($theme);
	$themeweb = WEBPATH . "/themes/$theme";
	if (zenPhotoTheme($theme)) {
		$whose = 'Zenphoto official theme';
		$ico = 'images/zp_gold.png';
	} else {
		$whose = gettext('third party theme');
		$path = $themedir.'/logo.png';
		if (file_exists($path)) {
			$ico = $themeweb.'/logo.png';
		} else {
			$ico = 'images/place_holder_icon.png';
		}
	}
	?>
	<tr>
		<td style="margin: 0px; padding: 0px;">
			<?php
			if (file_exists("$themedir/theme.png")) {
				$themeimage = "$themeweb/theme.png";
			}	else if (file_exists("$themedir/theme.gif")) {
				$themeimage = "$themeweb/theme.gif";
			} else if (file_exists("$themedir/theme.jpg")) {
				$themeimage = "$themeweb/theme.jpg";
			} else {
				$themeimage = false;
			}
			if ($themeimage) {
				?>
				<img height="150" width="150" src="<?php echo $themeimage; ?>" alt="Theme Screenshot" />
				<?php
			}
			?>
		</td>
		<td <?php echo $style; ?>>
			<img class="zp_logoicon" src="<?php echo $ico; ?>" alt="<?php echo gettext('logo'); ?>" title="<?php echo $whose; ?>" />
			<strong><?php echo $themeinfo['name']; ?></strong>
			<br />
			<?php echo $themeinfo['author']; ?>
			<br />
			<?php
			if ($ico == 'images/zp_gold.png') {
				$version = ZENPHOTO_VERSION;
				$date = $zenphoto_date;
			} else {
				$version = $themeinfo['version'];
				$date = $themeinfo['date'];
			}
			echo gettext('Version').' '. $version.', '.$date;
			?>
			<br />
			<?php echo $themeinfo['desc']; ?>
			<br /><br />
			<a href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-options.php?page=options&amp;tab=theme&amp;optiontheme=<?php echo $theme; if ($alb) { echo '&amp;themealbum='.$alb; } ?>" ><?php echo sprintf(gettext('Set <em>%s</em> theme options'),$themeinfo['name']); ?></a>
		</td>
		<td width="20%" <?php echo $style; ?>>
			<ul class="theme_links">
			<?php
			if ($theme != $current_theme) {
				?>
				<li>
				<p class="buttons">
					<a onclick="launchScript('admin-themes.php',['action=settheme','themealbum=<?php echo pathurlencode($alb); ?>','theme=<?php echo $theme; ?>','XSRFToken=<?php echo getXSRFToken('admin-themes')?>']);">
						<img src="images/pass.png" alt="" /><?php echo gettext("Activate"); ?>
					</a>
				</p>
				<br />
				</li>
			<?php
			} else {
				if ($gallerydefault) {
					?>
					<li>
					<p class="buttons">
						<a onclick="launchScript('admin-themes.php',['action=settheme','themealbum=<?php echo pathurlencode($alb); ?>','theme=<?php echo $theme; ?>','XSRFToken=<?php echo getXSRFToken('admin-themes')?>']);">
							<img src="images/pass.png" alt="" /><?php echo gettext("Assign"); ?>
						</a>
					</p>
					</li>
					<?php
				} else {
					echo "<li><strong>".gettext("Current Theme")."</strong></li>";
				}
			}

			if (themeIsEditable($theme)) {
				?>
				<li>
				<p class="buttons">
					<a onclick="launchScript('admin-themes-editor.php',['theme=<?php echo $theme; ?>']);">
						<img src="images/pencil.png" alt="" /><?php echo gettext("Edit"); ?>
					</a>
				</p><br />
				</li>
				<?php
				if ($theme != $current_theme) {
					?>
					<li>
					<p class="buttons">
						<a onclick="launchScript('admin-themes.php',['action=deletetheme','themealbum=<?php echo pathurlencode($alb); ?>','theme=<?php echo $theme; ?>','XSRFToken=<?php echo getXSRFToken('admin-themes')?>']);">
							<img src="images/edit-delete.png" alt="" /><?php echo gettext("Delete"); ?>
						</a>
					</p>
					</li>
					<?php
				}
			} else {
				?>
				<li class="zp_copy_theme">
				<p class="buttons">
					<a onclick="copyClick('<?php echo $theme; ?>');">
						<img src="images/page_white_copy.png" alt="" /><?php echo gettext("Duplicate"); ?>
					</a>
				</p>
				</li>
				<?php
			}
			zp_apply_filter('admin_theme_buttons', $theme, $alb);
			?>
			</ul>
		</td>
	</tr>

	<?php
	}
	?>
</table>


<?php
}

echo "\n" . '</div>';  //content
printAdminFooter();
echo "\n" . '</div>';  //main

echo "\n</body>";
echo "\n</html>";
?>



