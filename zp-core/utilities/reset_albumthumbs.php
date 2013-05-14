<?php
/**
 * Use this utility to reset your album thumbnails to either "random" or from an ordered field query
 *
 * @package admin
 */

define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(dirname(dirname(__FILE__)).'/template-functions.php');

$buttonlist[] = array(
								'category'=>gettext('Database'),
								'enable'=>true,
								'button_text'=>gettext('Reset album thumbs'),
								'formname'=>'reset_albumthumbs.php',
								'action'=>'utilities/reset_albumthumbs.php',
								'icon'=>'images/reset.png',
								'title'=>gettext('Reset album thumbnails to either random or most recent'),
								'alt'=>'',
								'hidden'=>'',
								'rights'=> MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS
								);

admin_securityChecks(MANAGE_ALL_ALBUM_RIGHTS, $return = currentRelativeURL());

if (isset($_REQUEST['thumbtype']) || isset($_REQUEST['thumbselector'])) {
	XSRFdefender('reset_thumbs');
}

$buffer = '';
$webpath = WEBPATH.'/'.ZENFOLDER.'/';

$zenphoto_tabs['overview']['subtabs']=array(gettext('Thumbs')=>'');

printAdminHeader('overview','thumbs');
echo '</head>';
?>

<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php printSubtabs(); ?>
<div class="tabbox">
<?php zp_apply_filter('admin_note','reste_thumbs', ''); ?>
<h1><?php echo (gettext('Reset your album thumbnails')); ?></h1>
<?php
if (isset($_REQUEST['thumbtype']) && db_connect($_zp_conf_vars)) {
	$key = sanitize_numeric($_REQUEST['thumbtype'], 3);
	$sql = 'UPDATE '.prefix('albums').' SET `thumb`='.$key;
	$text = $_zp_albumthumb_selector[$key]['desc'];
	if (query($sql)) {
		?>
		<div class="messagebox fade-message">
			<h2>
			<?php printf(gettext("Thumbnails all set to <em>%s</em>."), $text); ?></h2>
		</div>
		<?php
	} else {
		?>
		<div class="errorbox fade-message">
			<h2>
			<?php echo gettext("Thumbnail reset query failed"); ?></h2>
		</div>
		<?php
	}
}
$current = getOption('AlbumThumbSelect');

if (db_connect($_zp_conf_vars)) {
	$selections = array();
	foreach ($_zp_albumthumb_selector as $key=>$selection) {
		$selections[$selection['desc']] = $key;
	}
	?>
	<form name="set_random" action="">
		<?php XSRFToken('reset_thumbs')?>
				<div class="buttons pad_button" id="set_all">
					<button class="fixedwidth" type="submit"
						title="<?php echo gettext("Sets all album thumbs to the selected criteria"); ?>">
						<img src="<?php echo $webpath; ?>images/burst.png" alt="" />
						 <?php echo gettext("Set all albums to"); ?>
					</button>
					<select id="thumbtype" name="thumbtype">
						<?php
						generateListFromArray(array($current),$selections,false,true);
						?>
					</select>
				</div>
				<br class="clearall" /> <br />
			</form>
			<br class="clearall" /> <br />
	<?php
} else {
	echo "<h3>".gettext("database not connected")."</h3>";
	echo "<p>".gettext("Check your configuration file to make sure you've got the right username, password, host, and database. If you haven't created the database yet, now would be a good time.");
}

?>
		</div>
		</div>
	</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
<?php echo "</html>"; ?>




