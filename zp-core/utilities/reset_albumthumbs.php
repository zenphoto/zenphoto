<?php
/**
 * Use this utility to reset your album thumbnails to either "random" or from an ordered field query
 *
 * @package admin
 */

define('OFFSET_PATH', 3);
chdir(dirname(dirname(__FILE__)));

require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(dirname(dirname(__FILE__)).'/template-functions.php');
$button_text = gettext('Reset album thumbs');
$button_hint = sprintf(gettext('Reset album thumbnails to either random or %s'),get_language_string(getOption('AlbumThumbSelectorText')));
$button_icon = 'images/reset1.png';
$button_rights = MANAGE_ALL_ALBUM_RIGHTS;

admin_securityChecks(MANAGE_ALL_ALBUM_RIGHTS, $return = currentRelativeURL(__FILE__));

if (isset($_REQUEST['thumbtype']) || isset($_REQUEST['thumbselector'])) {
	XSRFdefender('reset_thumbs');
}

$buffer = '';
$gallery = new Gallery();
$webpath = WEBPATH.'/'.ZENFOLDER.'/';
$selector = array(array('field'=>'', 'direction'=>'', 'desc'=>'random'),
									array('field'=>'ID', 'direction'=>'DESC', 'desc'=>$_thumb_field_text['ID']),
									array('field'=>'mtime', 'direction'=>'', 'desc'=>$_thumb_field_text['mtime']),
									array('field'=>'title', 'direction'=>'', 'desc'=>$_thumb_field_text['title']),
									array('field'=>'hitcounter', 'direction'=>'DESC', 'desc'=>$_thumb_field_text['hitcounter'])
									);


printAdminHeader(gettext('utilities'),gettext('thumbs'));
echo '</head>';
?>

<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php zp_apply_filter('admin_note','reste_thumbs', ''); ?>
<h1><?php echo (gettext('Reset your album thumbnails')); ?></h1>
<?php
if (isset($_REQUEST['thumbtype']) && db_connect()) {
	$key = sanitize_numeric($_REQUEST['thumbtype'], 3);
	$sql = 'UPDATE '.prefix('albums').' SET `thumb`="'.$selector[$key]['field'].'"';
	if (query($sql)) {
		?>
		<div class="messagebox fade-message">
		<h2><?php printf(gettext("Thumbnails all set to <em>%s</em>."), $selector[$key]['desc']); ?></h2>
		</div>
		<?php
	} else {
		?>
		<div class="errorbox fade-message">
		<h2><?php echo gettext("Thumbnail reset query failed"); ?></h2>
		</div>
		<?php
	}
}
if (isset($_REQUEST['thumbselector'])) {
	$current = sanitize_numeric($_REQUEST['thumbselector']);
	setOption('AlbumThumbSelectField',$selector[$current]['field']);
	setOption('AlbumThumbSelectDirection',$selector[$current]['direction']);
} else {
	$currentfield = getOption('AlbumThumbSelectField');
	foreach ($selector as $key=>$selection) {
		if ($selection['field'] == $currentfield) {
			$current = $key;
			break;
		}
	}
}

if (db_connect()) {
	$selections = array();
	$currentkey = '';
	foreach ($selector as $key=>$selection) {
		$selections[$selection['desc']] = $key;
		if ($selection['desc'] == $current) $currentkey=$key;
	}
	?>
	<form name="set_random" action="">
		<?php XSRFToken('reset_thumbs')?>
		<div class="buttons pad_button" id="set_all">
			<button class="tooltip" type="submit" title="<?php echo gettext("Sets all album thumbs to the selected criteria"); ?>">
				<img src="<?php echo $webpath; ?>images/burst1.png" alt="" /> <?php echo gettext("Set all albums to"); ?>
			</button>
			<select id="thumbtype" name="thumbtype" >
				<?php
				generateListFromArray(array($current),$selections,false,true);
				?>
			</select>
		</div>
		<br clear="all" />
		<br clear="all" />
	</form>
	<br />
	<br />
	<table>
		<tr>
			<td>
				<form name="set_default" action="">
					<?php XSRFToken('reset_thumbs')?>
					<div class="buttons pad_button" id="set_default">
						<button class="tooltip" type="submit" title="<?php echo gettext("Set album thumb default to the selected criteria"); ?>">
							<img src="<?php echo $webpath; ?>images/burst1.png" alt="" />
							<?php echo gettext('Album thumbnail default'); ?>
						</button>
						<select id="thumbselector" name="thumbselector" >
							<?php
							generateListFromArray(array($current),$selections,false,true);
							?>
						</select>
					</div>
				</form>
			</td>
		</tr>
	</table>
	<br clear="all" />
	<br clear="all" />
	<?php
} else {
	echo "<h3>".gettext("database not connected")."</h3>";
	echo "<p>".gettext("Check the zp-config.php file to make sure you've got the right username, password, host, and database. If you haven't created the database yet, now would be a good time.");
}

?>


</div>
<!-- content --></div>
<!-- main -->
<?php printAdminFooter(); ?>
</body>
<?php echo "</html>"; ?>




