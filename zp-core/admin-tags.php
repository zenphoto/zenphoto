<?php
/**
 * provides the TAGS tab of admin
 * @package admin
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-globals.php');
require_once(dirname(__FILE__).'/template-functions.php');

admin_securityChecks(TAGS_RIGHTS, currentRelativeURL());

$_GET['page'] = 'tags';

if (isset($_REQUEST['tagsort'])) {
	$tagsort = sanitize($_REQUEST['tagsort']);
	setOption('tagsort', ($tagsort && true));
} else {
	$tagsort = getOption('tagsort');
}
$action = '';
if (count($_POST) > 0) {
	if (isset($_GET['newtags'])) {
		XSRFdefender('new_tags');
		foreach ($_POST as $value) {
			if (!empty($value)) {
				$value = html_decode(sanitize($value, 3));
				$result = query_single_row('SELECT `id` FROM '.prefix('tags').' WHERE `name`='.db_quote($value));
				if (!is_array($result)) { // it really is a new tag
					query('INSERT INTO '.prefix('tags').' (`name`) VALUES (' . db_quote($value) . ')');
				}
			}
		}
		$action = gettext('New tags added');
	} // newtags
	if (isset($_GET['delete'])) {
		XSRFdefender('tag_delete');
		$kill = array();
		foreach ($_POST as $key => $value) {
			$key = str_replace('tags_','',postIndexDecode($key));
			$kill[] = mb_strtolower($key);
		}
		if (count($kill) > 0) {
			$sql = "SELECT `id` FROM ".prefix('tags')." WHERE ";
			foreach ($kill as $tag) {
				$sql .= "`name`=".(db_quote($tag))." OR ";
			}
			$sql = substr($sql, 0, strlen($sql)-4);
			$dbtags = query_full_array($sql);
			if (is_array($dbtags) && count($dbtags) > 0) {
				$sqltags = "DELETE FROM ".prefix('tags')." WHERE ";
				$sqlobjects = "DELETE FROM ".prefix('obj_to_tag')." WHERE ";
				foreach ($dbtags as $tag) {
					$sqltags .= "`id`='".$tag['id']."' OR ";
					$sqlobjects .= "`tagid`='".$tag['id']."' OR ";
				}
				$sqltags = substr($sqltags, 0, strlen($sqltags)-4);
				query($sqltags);
				$sqlobjects = substr($sqlobjects, 0, strlen($sqlobjects)-4);
				query($sqlobjects);
			}
		}
		$action = gettext('Checked tags deleted');
	} // delete
	if (isset($_GET['rename'])) {
		XSRFdefender('tag_rename');
		unset($_POST['XSRFToken']);
		foreach($_POST as $key=>$newName) {
			if (!empty($newName)) {
				$newName = sanitize($newName, 3);
				$key = postIndexDecode($key);
				$key = substr($key, 2); // strip off the 'R_'
				$newtag = query_single_row('SELECT `id` FROM '.prefix('tags').' WHERE `name`='.db_quote($newName));
				$oldtag = query_single_row('SELECT `id` FROM '.prefix('tags').' WHERE `name`='.db_quote($key));
				if (is_array($newtag)) { // there is an existing tag of the same name
					$existing = $newtag['id'] != $oldtag['id']; // but maybe it is actually the original in a different case.
				} else {
					$existing = false;
				}
				if ($existing) {
					query('DELETE FROM '.prefix('tags').' WHERE `id`='.$oldtag['id']);
					query('UPDATE '.prefix('obj_to_tag').' SET `tagid`='.$newtag['id'].' WHERE `tagid`='.$oldtag['id']);
				} else {
					query('UPDATE '.prefix('tags').' SET `name`='.db_quote($newName).' WHERE `id`='.$oldtag['id']);
				}
			}
		}
		$action = gettext('Tags renamed');
	} // rename
}

printAdminHeader('tags');
?>
</head>
<body>
<?php
printLogoAndLinks();
?>
<div id="main">
	<?php
	printTabs();
	?>
	<div id="content">
		<?php
		if (!empty($action)) {
			?>
			<div class="messagebox fade-message">
				<h2><?php echo $action; ?></h2>
			</div>
			<?php
		}


		echo "<h1>".gettext("Tag Management")."</h1>";
		if ($tagsort == 1) {
			?>
			<p class="buttons">
				<a class="tagsort" href="?tagsort=0">
					<img src="images/sortorder.png" alt="" /> <?php echo gettext('Order alphabetically'); ?>
				</a>
			</p>
			<br />
			<br />
			<br class="clearall" />
			<?php
		} else{
			?>
			<p class="buttons">
				<a class="tagsort" href="?tagsort=1">
					<img src="images/sortorder.png" alt="" /> <?php echo gettext('Order by most used'); ?>
				</a>
			</p>
			<br />
			<br />
			<br class="clearall" />
			<?php
		}
		?>
		<table class="bordered">
			<tr>
			<td valign='top'>
				<h2 class="h2_bordered_edit"><?php echo gettext("Delete tags from the gallery"); ?></h2>
				<form name="tag_delete" action="?delete=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post">
					<?php XSRFToken('tag_delete');?>
					<div class="box-tags-unpadded">
						<?php
						tagSelector(NULL, 'tags_', true, $tagsort, false);
						?>
					</div>

					<p class="buttons">
						<button type="submit" id='delete_tags' value="<?php echo gettext("Delete checked tags"); ?>">
						<img src="images/fail.png" alt="" /><?php echo gettext("Delete checked tags"); ?>
						</button>
					</p>
					<label id="autocheck">
						<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.checkTagsAuto').prop('checked', $('#checkAllAuto').prop('checked'));"/>
						<span id="autotext"><?php echo gettext('all');?></span>
					</label>
					<br class="clearall" />
					<br />
					<br />

				</form>
				<div class="tagtext">
					<p><?php echo gettext('Place a checkmark in the box for each tag you wish to delete then press the <em>Delete checked tags</em> button. The brackets contain the number of times the tag appears.'); ?></p>
				</div>
			</td>

			<td valign='top'>
				<h2 class="h2_bordered_edit"><?php echo gettext("Rename tags"); ?></h2>
				<form name="tag_rename" action="?rename=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post">
					<?php XSRFToken('tag_rename');?>
					<div class="box-tags-unpadded">
						<ul class="tagrenamelist">
							<?php
							$list = $_zp_admin_ordered_taglist;
							foreach($list as $item) {
								$listitem = 'R_'.postIndexEncode($item);
								?>
								<li>
									<label>
										<?php echo $item; ?>
										<input id="<?php echo $listitem; ?>" name="<?php echo $listitem; ?>" type="text" size='33' />
									</label>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
					<p class="buttons">
						<button type="submit" id='rename_tags' value="<?php echo gettext("Rename tags"); ?>">
							<img src="images/pass.png" alt="" /><?php echo gettext("Rename tags"); ?>
						</button>
					</p>
					<br class="clearall" />
					<br />
					<br />
				</form>
				<div class="tagtext">
					<p><?php echo gettext('To change the value of a tag enter a new value in the text box below the tag. Then press the <em>Rename tags</em> button'); ?></p>
				</div>
			</td>

			<td valign='top'>
				<h2 class="h2_bordered_edit"><?php echo gettext("New tags"); ?></h2>
				<form name="new_tags" action="?newtags=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post">
					<?php XSRFToken('new_tags');?>
					<div class="box-tags-unpadded">
						<ul class="tagnewlist">
							<?php
							for ($i=0; $i<40; $i++) {
								?>
								<li>
									<input id="new_tag_<?php echo $i; ?>" name="new_tag_<?php echo $i; ?>" type="text" size='33'/>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
					<p class="buttons">
						<button type="submit" id='save_tags' value="<?php echo gettext("Add tags"); ?>">
							<img src="images/add.png" alt="" /><?php echo gettext("Add tags"); ?>
						</button>
					</p>
					<br class="clearall" />
					<br />
					<br />

				</form>
				<div class="tagtext">
					<p><?php echo gettext("Add tags to the list by entering their names in the input fields of the <em>New tags</em> list. Then press the <em>Add tags</em> button"); ?></p>
				</div>
			</td>
			</tr>
		</table>

	</div>
	<?php
	printAdminFooter();
	?>
</div>
</body>
</html>




