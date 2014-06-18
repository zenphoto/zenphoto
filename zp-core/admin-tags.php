<?php
/**
 * provides the TAGS tab of admin
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(dirname(__FILE__) . '/template-functions.php');

admin_securityChecks(TAGS_RIGHTS, currentRelativeURL());

$_GET['page'] = 'tags';

$tagsort = getTagOrder();
$action = '';
if (count($_POST) > 0) {
	if (isset($_GET['newtags'])) {
		XSRFdefender('new_tags');
		$language = sanitize($_POST['language']);
		unset($_POST['language']);
		foreach ($_POST as $value) {
			if (!empty($value)) {
				$value = html_decode(sanitize($value, 3));
				$result = query_single_row('SELECT `id` FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($value));
				if (!is_array($result)) { // it really is a new tag
					query('INSERT INTO ' . prefix('tags') . ' (`name`,`language`) VALUES (' . db_quote($value) . ',' . db_quote($language) . ')');
				}
			}
		}
		$action = gettext('New tags added');
	} // newtags
	if (isset($_POST['tag_action'])) {
		XSRFdefender('tag_action');
		$language = sanitize($_POST['language']);
		unset($_POST['language']);
		$action = $_POST['tag_action'];
		unset($_POST['tag_action']);

		switch ($action) {
			case'delete':
				$kill = array();
				foreach ($_POST as $key => $value) {
					$key = postIndexDecode(str_replace('tags_', '', sanitize($key)));
					$kill[] = $key;
				}
				if (count($kill) > 0) {
					$sql = "SELECT `id` FROM " . prefix('tags') . " WHERE ";
					foreach ($kill as $tag) {
						$sql .= "`name`=" . (db_quote($tag)) . " OR ";
					}
					$sql = substr($sql, 0, strlen($sql) - 4);
					$dbtags = query_full_array($sql);
					if (is_array($dbtags) && count($dbtags) > 0) {
						$sqltags = "DELETE FROM " . prefix('tags') . " WHERE ";
						$sqlobjects = "DELETE FROM " . prefix('obj_to_tag') . " WHERE ";
						foreach ($dbtags as $tag) {
							$sqltags .= "`id`='" . $tag['id'] . "' OR ";
							$sqlobjects .= "`tagid`='" . $tag['id'] . "' OR ";
						}
						$sqltags = substr($sqltags, 0, strlen($sqltags) - 4);
						query($sqltags);
						$sqlobjects = substr($sqlobjects, 0, strlen($sqlobjects) - 4);
						query($sqlobjects);
					}
				}
				$action = gettext('Checked tags deleted');
				break;
			case'assign':
				$assign = array();
				foreach ($_POST as $key => $value) {
					$key = postIndexDecode(str_replace('tags_', '', sanitize($key)));
					$assign[] = $key;
				}
				if (count($assign) > 0) {
					foreach ($assign as $tag) {
						$sql = 'UPDATE ' . prefix('tags') . ' SET `language`=' . db_quote($language) . ' WHERE `name`=' . db_quote($tag);
						query($sql);
					}
				}
				break;
		}
	} // tag action
	if (isset($_GET['rename'])) {
		XSRFdefender('tag_rename');
		unset($_POST['XSRFToken']);
		foreach ($_POST as $key => $newName) {
			if (!empty($newName)) {
				$newName = sanitize($newName, 3);
				$key = postIndexDecode(sanitize($key));
				$key = substr($key, 2); // strip off the 'R_'
				$newtag = query_single_row('SELECT `id` FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($newName));
				$oldtag = query_single_row('SELECT `id` FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($key));
				if (is_array($newtag)) { // there is an existing tag of the same name
					$existing = $newtag['id'] != $oldtag['id']; // but maybe it is actually the original in a different case.
				} else {
					$existing = false;
				}
				if ($existing) {
					query('DELETE FROM ' . prefix('tags') . ' WHERE `id`=' . $oldtag['id']);
					query('UPDATE ' . prefix('obj_to_tag') . ' SET `tagid`=' . $newtag['id'] . ' WHERE `tagid`=' . $oldtag['id']);
				} else {
					query('UPDATE ' . prefix('tags') . ' SET `name`=' . db_quote($newName) . ' WHERE `id`=' . $oldtag['id']);
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


			echo "<h1>" . gettext("Tag Management") . "</h1>";
			?>
			<?php echo gettext('Order by'); ?>

			<select name="tagsort" id="tagsort_selector" class="ignoredirty" onchange="launchScript('', ['tagsort=' + $('#tagsort_selector').val()]);">
				<option value="alpha" <?php if ($tagsort == 'alpha') echo ' selected="selected"'; ?>><?php echo gettext('Alphabetic'); ?></option>
				<option value="mostused" <?php if ($tagsort == 'mostused') echo ' selected="selected"'; ?>><?php echo gettext('Most used'); ?></option>
				<option value="language" <?php if ($tagsort == 'language') echo ' selected="selected"'; ?>><?php echo gettext('Language'); ?></option>
				<option value="recent" <?php if ($tagsort == 'recent') echo ' selected="selected"'; ?>><?php echo gettext('Most recent'); ?></option>
			</select>
			<div class="buttons floatright">
				<button type="reset" onclick="$('#tag_action_form').trigger('reset');
						$('#form_tagrename').trigger('reset');
						$('#form_newtags').trigger('reset');">
					<img src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
			</div>

			<table class="bordered">
				<tr>
					<td valign='top'>
						<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?>
							<label id="autocheck" style="float:right">
								<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.checkTagsAuto').prop('checked', $('#checkAllAuto').prop('checked'));"/>
								<span id="autotext"><?php echo gettext('all'); ?></span>
							</label>
						</h2>
						<form class="dirtylistening" onReset="setClean('tag_action_form');" name="tag_action_form" id="tag_action_form" action="?action=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post" >
							<?php XSRFToken('tag_action'); ?>
							<input type="hidden" name="tag_action" id="tag_action" value="delete" />
							<div class="box-tags-unpadded">
								<?php
								tagSelector(NULL, 'tags_', true, $tagsort, false);
								list($list, $counts, $languages, $flags) = $_zp_admin_ordered_taglist;
								?>
							</div>

							<p class="buttons">
								<button type="submit" id='delete_tags' value="<?php echo gettext("Delete checked tags"); ?>"
												onclick="$('#tag_action').val('delete');
														this.form.submit();">
									<img src="images/fail.png" alt="" />
									<?php echo gettext("Delete checked tags"); ?>
								</button>
							</p>
							<br />
							<p class="buttons">
								<button type="submit" id="assign_tags" value="<?php echo gettext("Delete checked tags"); ?>"
												onclick="$('#tag_action').val('assign');
														this.form.submit();">
									<img src="images/redo.png" alt="" />
									<?php echo gettext('assign'); ?>
								</button>
							</p>
							<select name="language" id="language" class="ignoredirty">
								<option value=""><?php echo gettext('Universal'); ?></option>
								<?php
								foreach ($_zp_active_languages as $text => $lang) {
									?>
									<option value="<?php echo $lang; ?>" <?php if ($_zp_current_locale == $lang) echo ' selected="selected"' ?>><?php echo html_encode($text); ?></option>
									<?php
								}
								?>
							</select>
							<div class="clearall"></div>
						</form>

						<div class="tagtext">
							<p><?php echo gettext('Place a checkmark in the box for each tag you wish to delete or to assign a language then press the appropriate button. The brackets contain the number of times the tag appears.'); ?></p>
						</div>
					</td>

					<td valign='top'>
						<h2 class="h2_bordered_edit"><?php echo gettext("Rename tags"); ?></h2>
						<form class="dirtylistening" onReset="setClean('form_tagrename');" name="tag_rename" id="form_tagrename" action="?rename=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post" >
							<?php XSRFToken('tag_rename'); ?>
							<div class="box-tags-unpadded">
								<ul class="tagrenamelist">
									<?php
									foreach ($list as $item) {
										$listitem = 'R_' . postIndexEncode($item);
										?>
										<li>
											<label>
												<img src="<?php echo $flags[$languages[$item]]; ?>" height="10" width="16" />
												<?php echo $item; ?>
												<br />
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
							<div class="clearall"></div>
							<br />
							<br />
						</form>
						<div class="tagtext">
							<p><?php echo gettext('To change the value of a tag enter a new value in the text box below the tag. Then press the <em>Rename tags</em> button'); ?></p>
						</div>
					</td>

					<td valign='top'>
						<h2 class="h2_bordered_edit"><?php echo gettext("New tags"); ?></h2>
						<form class="dirtylistening" onReset="setClean('form_newtags');"  name="new_tags" id="form_newtags" action="?newtags=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post">
							<?php XSRFToken('new_tags'); ?>
							<div class="box-tags-unpadded">
								<ul class="tagnewlist">
									<?php
									for ($i = 0; $i < 40; $i++) {
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
							<select name="language" id="language" class="ignoredirty">
								<option value="" selected="language"><?php echo gettext('Universal'); ?></option>
								<?php
								foreach ($_zp_active_languages as $text => $lang) {
									?>
									<option value="<?php echo $lang; ?>" ><?php echo html_encode($text); ?></option>
									<?php
								}
								?>
							</select>
							<div class="clearall"></div>
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




