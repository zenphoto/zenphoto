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
	$subaction = array();
	if (isset($_GET['newtags'])) {
		XSRFdefender('new_tags');
		$language = sanitize($_POST['language']);
		unset($_POST['language']);
		unset($_POST['XSRFToken']);
		$multi = getOption('multi_lingual') && !empty($language);
		foreach ($_POST as $value) {
			if (!empty($value)) {
				$value = html_decode(sanitize($value, 3));
				$sql = 'SELECT `id` FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($value) . ' AND `language`=' . db_quote($language);
				$result = query_single_row($sql);
				if (!is_array($result)) { // it really is a new tag
					$success = query('INSERT INTO ' . prefix('tags') . ' (`name`,`language`) VALUES (' . db_quote($value) . ',' . db_quote($language) . ')', false);
					if ($success) {
						if ($multi) {
							$master = db_insert_id();
							foreach (generateLanguageList(false)as $text => $dirname) {
								if ($dirname != $language) {
									query('INSERT INTO ' . prefix('tags') . ' (`name`, `masterid`,`language`) VALUES (' . db_quote($value) . ',' . $master . ',' . db_quote($dirname) . ')', false);
								}
							}
						}
					} else {
						$subaction[] = ltrim(sprintf(gettext('%1$s: %2$s not stored, duplicate tag.'), $language, $value), ': ');
					}
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
		if (isset($_POST['tag_list_tags_'])) {
			$tags = sanitize($_POST['tag_list_tags_']);
			$langs = sanitize($_POST['lang_list_tags_']);
		} else {
			$langs = $tags = array();
		}
		switch ($action) {
			case'delete':
				if (count($tags) > 0) {
					$sql = "SELECT * FROM " . prefix('tags') . " WHERE ";
					foreach ($tags as $key => $tag) {
						$sql .= "(`name`=" . (db_quote($tag)) . ' AND `language`=' . db_quote($langs[$key]) . ") OR ";
					}
					$sql = substr($sql, 0, strlen($sql) - 4);
					$dbtags = query_full_array($sql);
					if (is_array($dbtags) && count($dbtags) > 0) {
						$sqltags = "DELETE FROM " . prefix('tags') . " WHERE ";
						$sqlobjects = "DELETE FROM " . prefix('obj_to_tag') . " WHERE ";
						foreach ($dbtags as $tag) {
							$sqltags .= "`id`='" . $tag['id'] . "' OR ";
							$sqlobjects .= "`tagid`='" . $tag['id'] . "' OR ";
							if (is_null($tag['masterid'])) {
								$sqltags .="`masterid`='" . $tag['id'] . "' OR ";
								$sqlSub = "SELECT `id`, `masterid` FROM " . prefix('tags') . " WHERE `masterid`=" . $tag['id'];
								$subTags = query_full_array($sqlSub);
								if (is_array($subTags) && count($subTags) > 0) {
									foreach ($subTags as $subTag) {
										$sqlobjects .= "`tagid`='" . $subTag['id'] . "' OR ";
									}
								}
							}
						}
						$sqltags = substr($sqltags, 0, strlen($sqltags) - 4);
						query($sqltags);
						$sqlobjects = substr($sqlobjects, 0, strlen($sqlobjects) - 4);
						query($sqlobjects);
					}
				}
				$action = gettext('Checked tags deleted');
				break;
			case 'private':
			case 'notprivate':
				$private = (int) ($action == 'private');
				if (count($tags) > 0) {
					$sql = "UPDATE " . prefix('tags') . " SET `private`=$private WHERE ";
					foreach ($tags as $key => $tag) {
						$sql .= "(`name`=" . (db_quote($tag)) . ' AND `language`=' . db_quote($langs[$key]) . ") OR ";
					}
					$sql = substr($sql, 0, strlen($sql) - 4);
					query($sql);
				}
				if ($private) {
					$action = gettext('Checked tags marked private');
				} else {
					$action = gettext('Checked tags marked public');
				}
				break;
			case'assign':
				if (count($tags) > 0) {
					$tbdeleted = array();
					$multi = getOption('multi_lingual');
					foreach ($tags as $key => $tag) {
						$lang = $langs[$key];
						$sql = 'UPDATE ' . prefix('tags') . ' SET `language`=' . db_quote($language) . ' WHERE `name`=' . db_quote($tag) . ' AND `lang`=' . db_quote($lang);
						$success = query($sql, false);
						if ($success) {
							$tag = query_single_row('SELECT `id` FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($tag) . ' AND `lang`=' . db_quote($lang));
							if ($multi && empty($tag['language'])) {
								//create subtags
								foreach (generateLanguageList(false)as $text => $dirname) {
									if ($dirname != $language) {
										query('INSERT INTO ' . prefix('tags') . ' (`name`, `masterid`,`language`) VALUES (' . db_quote($tag) . ',' . $tag['id'] . ',' . db_quote($dirname) . ')');
									}
								}
							} else if (empty($language)) {
								$tbdeleted[] = $id;
							}
						} else {
							$subaction[] = ltrim(sprintf(gettext('%1$s: %2$s language not changed, duplicate tag.'), $lang, $tag), ': ');
						}
						if (!empty($tbdeleted)) {
							query('DELETE FROM ' . prefix('tags') . ' WHERE `masterid`=' . implode(' OR `masterid`=', $tbdeleted));
						}
					}
				}
				$action = gettext('Checked tags language assigned');
				break;
		}
	} // tag action
	if (isset($_GET['rename'])) {
		XSRFdefender('tag_rename');
		unset($_POST['XSRFToken']);
		$langs = sanitize($_POST['lang_list_tags']);
		unset($_POST['lang_list_tags']);
		foreach ($_POST as $postkey => $newName) {
			if (!empty($newName)) {
				$lang = $langs[$postkey];
				$newName = sanitize($newName, 3);
				$key = substr($postkey, 2); // strip off the 'R_'
				$key = postIndexDecode(sanitize($key));
				$newtag = query_single_row('SELECT * FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($newName) . ' AND `language`=' . db_quote($lang));
				$oldtag = query_single_row('SELECT * FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote($key) . ' AND `language`=' . db_quote($lang));
				if (is_array($newtag)) { // there is an existing tag of the same name
					$existing = $newtag['id'] != $oldtag['id']; // but maybe it is actually the original in a different case.
				} else {
					$existing = false;
				}
				if ($existing) {
					$subaction[] = ltrim(sprintf(gettext('%1$s: %2$s not changed, duplicate tag.'), $lang, $key), ': ');
				} else {
					query('UPDATE ' . prefix('tags') . ' SET `name`=' . db_quote($newName) . ' WHERE `id`=' . $oldtag['id']) . ' AND `language`=' . db_quote($langs[$postkey]);
				}
			}
		}
		$action = gettext('Tags renamed');
	} // rename
}

printAdminHeader('admin');
?>
</head>
<body>
	<?php
	printLogoAndLinks();
	$flags = getLanguageFlags();
	?>
	<div id="main">
		<?php
		printTabs();
		?>
		<div id="content">
			<?php
			if (!empty($action)) {
				?>
				<div class = "messagebox fade-message">
					<h2><?php echo $action; ?></h2>
				</div>
				<?php
				if (!empty($subaction)) {
					?>
					<div class = "errorbox">
						<?php
						$br = '';
						foreach ($subaction as $action) {
							$flag = '';
							if (preg_match('~([a-z]{2}_*[A-Z]{0,2}.*):\s*(.*)~', $action, $matches)) {
								$action = $matches[2];
								if ($matches[1]) {
									$flag = '<img src="' . $flags[$matches[1]] . '" height="10" width="15" /> ';
								}
							}
							echo $br . $flag . $action;
							$br = '<br />';
						}
						?>
					</div>
					<?php
				}
			}

			zp_apply_filter('admin_note', 'tags', '');

			echo "<h1>" . gettext("Tag Management") . "</h1>";
			?>
			<?php echo gettext('Order by'); ?>

			<select name="tagsort" id="tagsort_selector" class="ignoredirty" onchange="window.location = '?tagsort=' + $('#tagsort_selector').val();">
				<option value="alpha" <?php if ($tagsort == 'alpha') echo ' selected="selected"'; ?>><?php echo gettext('Alphabetic'); ?></option>
				<option value="mostused" <?php if ($tagsort == 'mostused') echo ' selected="selected"'; ?>><?php echo gettext('Most used'); ?></option>
				<option value="language" <?php if ($tagsort == 'language') echo ' selected="selected"'; ?>><?php echo gettext('Language'); ?></option>
				<option value="recent" <?php if ($tagsort == 'recent') echo ' selected="selected"'; ?>><?php echo gettext('Most recent'); ?></option>
				<option value="private" <?php if ($tagsort == 'private') echo ' selected="selected"'; ?>><?php echo gettext('Private first'); ?></option>
			</select>
			<div class="buttons floatright">
				<button type="reset" onclick="$('#tag_action_form').trigger('reset');
						$('#form_tagrename').trigger('reset');
						$('#form_newtags').trigger('reset');">
									<?php echo CROSS_MARK_RED; ?>
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
			</div>

			<br class="clearall">
			<p class="notebox">
				<?php echo gettext('Indented tags are language translations of the superior (master) tag. If you delete a master tag, the language translations will also be deleted.'); ?>
			</p>
			<div class="tabbox">
				<div class="floatleft">
					<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?>
						<label id="autocheck" style="float:right">
							<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.checkTagsAuto').prop('checked', $('#checkAllAuto').prop('checked'));"/>
							<span id="autotext"><?php echo gettext('all'); ?></span>
						</label>
					</h2>
					<form class="dirtylistening" onReset="setClean('tag_action_form');" name="tag_action_form" id="tag_action_form" action="?action=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post" autocomplete="off" >
						<?php XSRFToken('tag_action'); ?>
						<input type="hidden" name="tag_action" id="tag_action" value="delete" />
						<div class="box-tags-unpadded">
							<?php
							tagSelector(NULL, 'tags_', true, $tagsort, false);
							$list = $_zp_admin_ordered_taglist;
							?>
						</div>

						<p class="buttons"<?php if (getOption('multi_lingual')) echo ' style="padding-bottom: 27px;"'; ?>>
							<button type="submit" id="delete_tags" onclick="$('#tag_action').val('delete');	this.form.submit();">
								<?php echo WASTEBASKET; ?>
								<?php echo gettext("Delete checked tags"); ?>
							</button>
						</p>
						<p class="buttons"<?php if (getOption('multi_lingual')) echo ' style="padding-bottom: 27px;"'; ?>>
							<button type="submit" id="delete_tags" onclick="$('#tag_action').val('private');	this.form.submit();">
								<?php echo LOCK; ?>
								<?php echo gettext("Mark checked tags private"); ?>
							</button>
						</p>
						<p class="buttons"<?php if (getOption('multi_lingual')) echo ' style="padding-bottom: 27px;"'; ?>>
							<button type="submit" id="delete_tags" onclick="$('#tag_action').val('notprivate');	this.form.submit();">
								<?php echo LOCK_OPEN; ?>
								<?php echo gettext("Mark checked tags public"); ?>
							</button>
						</p>

						<?php
						if (getOption('multi_lingual')) {
							?>
							<span class="buttons">
								<button type="submit" id="assign_tags" onclick="$('#tag_action').val('assign');	this.form.submit();" title="<?php echo gettext('Assign tags to selected language'); ?>">
									<?php echo ARROW_RIGHT_BLUE; ?>
									<?php echo gettext('Assign language'); ?>
								</button>
							</span>
							<div style="padding-bottom: 7px;">
								<select name="language" id="language" class="ignoredirty" >
									<option value=""><?php echo gettext('Universal'); ?></option>
									<?php
									foreach ($_zp_active_languages as $text => $lang) {
										?>
										<option value="<?php echo $lang; ?>"><?php echo html_encode($text); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<?php
						} else {
							?>
							<input type="hidden" name="language" value="" />
							<?php
						}
						?>
						<div class="clearall"></div>
					</form>

					<div class="tagtext">
						<p><?php
							echo gettext('Place a checkmark in the box for each tag you wish to act upon then press the appropriate button. The brackets contain the number of times the tag appears.');
							echo gettext('Tags that are <span class="privatetag">highlighted</span> are private.');
							?></p>
					</div>
				</div>

				<div class="floatleft">
					<h2 class="h2_bordered_edit"><?php echo gettext("Rename tags"); ?></h2>
					<form class="dirtylistening" onReset="setClean('form_tagrename');" name="tag_rename" id="form_tagrename" action="?rename=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post" autocomplete="off" >
						<?php XSRFToken('tag_rename'); ?>
						<div class="box-tags-unpadded">
							<ul class="tagrenamelist">
								<?php
								foreach ($list as $tagitem) {
									$item = $tagitem['tag'];
									$tagLC = mb_strtolower($item);
									$listitem = 'R_' . postIndexEncode($item);
									?>
									<li>
										<span class="nowrap">
											<?php
											if ($lang = $tagitem['lang']) {
												?>
												<img src="<?php echo $flags[$lang]; ?>" height="10" width="16" />
												<?php
											}
											?>
											<input id="<?php echo $listitem; ?>" name="<?php echo $listitem; ?>" type="text" size='33' value="<?php echo $item; ?>" />
											<input type="hidden" name="lang_list_tags[<?php echo $listitem; ?>]" value="<?php echo html_encode($lang); ?>" />
										</span>
										<?php
										if (is_array($tagitem['subtags'])) {
											$itemarray = $tagitem['subtags'];
											ksort($itemarray);
											foreach ($itemarray as $lang => $tagitem) {
												$tag = $tagitem['tag'];
												$LCtag = mb_strtolower($tag);
												$listitem = 'R_' . postIndexEncode($tag);
												?>
												<span class="nowrap">&nbsp;&nbsp;<img src="<?php echo $flags[$lang]; ?>" height="10" width="16" />
													<input id="<?php echo $listitem; ?>" name="<?php echo $listitem; ?>" type="text" size='33' value="<?php echo $tag; ?>"/>
												</span>
												<input type="hidden" name="lang_list_tags[<?php echo $listitem; ?>]" value="<?php echo html_encode($lang); ?>" />
												<?php
											}
										}
										?>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
						<p class="buttons" style="padding-bottom: 1px;">
							<button type="submit" id='rename_tags' value="<?php echo gettext("Rename tags"); ?>">
								<?php echo CHECKMARK_GREEN; ?>
								<?php echo gettext("Rename tags"); ?>
							</button>
						</p>
					</form>
					<br />
					<div class="tagtext">
						<p><?php echo gettext('To change the value of a tag enter a new value in the text box below the tag. Then press the <em>Rename tags</em> button'); ?></p>
					</div>
				</div>

				<div class="floatleft">
					<h2 class="h2_bordered_edit"><?php echo gettext("New tags"); ?></h2>
					<form class="dirtylistening" onReset="setClean('form_newtags');"  name="new_tags" id="form_newtags" action="?newtags=true&amp;tagsort=<?php echo html_encode($tagsort); ?>" method="post" autocomplete="off" >
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
						<p class="buttons"<?php if (getOption('multi_lingual')) echo ' style="padding-bottom: 25px;"'; ?>>
							<button type="submit" id='save_tags' value="<?php echo gettext("Add tags"); ?>">
								<?php echo PLUS_ICON; ?>
								<?php echo gettext("Add tags"); ?>
							</button>
						</p>
						<?php
						if (getOption('multi_lingual')) {
							?>
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
							<?php
						} else {
							?>
							<input type="hidden" name="language" value="" />
							<br />
							<?php
						}
						?>
						<div class="clearall"></div>
					</form>

					<div class="tagtext">
						<p><?php
							echo gettext("Add tags to the list by entering their names in the input fields of the <em>New tags</em> list. Then press the <em>Add tags</em> button.");
							if (getOption('multi_lingual')) {
								echo ' ' . gettext('You can assign a language to the tags with the language selector.');
							}
							?></p>
					</div>
				</div>
				<br class="clearall">
			</div>

		</div>
		<?php
		printAdminFooter();
		?>
	</div>
</body>
</html>




