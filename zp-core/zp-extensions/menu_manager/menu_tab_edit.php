<?php
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
if (extensionEnabled('zenpage')) {
	require_once(dirname(dirname(dirname(__FILE__))) . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-admin-functions.php');
}
require_once(dirname(__FILE__) . '/menu_manager-admin-functions.php');

admin_securityChecks(NULL, currentRelativeURL());

$page = 'edit';

$result = "";
$reports = array();
if (isset($_GET['id'])) {
	$result = getItem(sanitize($_GET['id']));
}
if (isset($_GET['save'])) {
	XSRFdefender('update_menu');
	if ($_POST['update']) {
		$result = updateMenuItem($reports);
	} else {
		$result = addItem($reports);
	}
}
if (isset($_GET['del'])) {
	XSRFdefender('delete_menu');
	deleteItem($reports);
}

printAdminHeader('menu', (is_array($result) && $result['id']) ? gettext('edit') : gettext('add'));
?>
<link rel="stylesheet" href="../zenpage/zenpage.css" type="text/css" />
<?php
$menuset = checkChosenMenuset();
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php
		printTabs();
		?>
		<div id="content">
			<?php
			zp_apply_filter('admin_note', 'menu', 'edit');
			foreach ($reports as $report) {
				echo $report;
			}
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				function handleSelectorChange(type) {
					$('#add,#titlelabel,#link_row,#link,#link_label,#visible_row,#show_visible,#span_row').show();
					$('#include_li_label').hide();
					$('#type').val(type);
					$('#link_label').html('<?php echo js_encode(gettext('URL')); ?>');
					$('#titlelabel').html('<?php echo js_encode(gettext('Title')); ?>');
					$('#XSRFToken').val('<?php echo getXSRFToken('update_menu'); ?>');
					switch (type) {
						case 'all_items':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#titleinput,#titlelabel,#link_row,#visible_row,#span_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("All menu items")); ?>');
							$('#description').html('<?php echo js_encode(gettext('This adds menu items for all Zenphoto objects. (It creates a "default" menuset.)')); ?>');
							break;
						case "galleryindex":
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#link_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("Gallery index")); ?>');
							$('#description').html('<?php echo js_encode(gettext("This is the normal Zenphoto gallery Index page.")); ?>');
							$('#link').attr('disabled', true);
							$('#titleinput').show();
							$('#link').val('<?php echo WEBPATH; ?>/');
							break;
						case 'all_albums':
							$('#albumselector,#pageselector,#categoryselector,#titleinput,#titlelabel,#link_row,#visible_row,#span_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("All Albums")); ?>');
							$('#description').html('<?php echo js_encode(gettext("This adds menu items for all Zenphoto albums.")); ?>');
							break;
						case 'album':
							$('#pageselector,#categoryselector,#custompageselector,#titleinput,#link_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("Album")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a link to a Zenphoto Album.")); ?>');
							$('#link').attr('disabled', true);
							$('#albumselector').show();
							$('#titlelabel').html('<?php echo js_encode(gettext('Album')); ?>');
							$('#albumselector').change(function() {
								$('#link').val($(this).val());
							});
							break;
						case 'all_zenpagepages':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#titleinput,#titlelabel,#link_row,#visible_row,#span_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("All Zenpage pages")); ?>');
							$('#description').html('<?php echo js_encode(gettext("This adds menu items for all Zenpage pages.")); ?>');
							break;
						case 'zenpagepage':
							$('#albumselector,#categoryselector,#custompageselector,#link_row,#titleinput').hide();
							$('#selector').html('<?php echo js_encode(gettext("Zenpage page")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a link to a Zenpage Page.")); ?>');
							$('#link').attr('disabled', true);
							$('#pageselector').show();
							$('#titlelabel').html('<?php echo js_encode(gettext('Page')); ?>');
							$('#pageselector').change(function() {
								$('#link').val($(this).val());
							});
							break;
						case 'zenpagenewsindex':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#link_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("Zenpage news index")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a link to the Zenpage News Index.")); ?>');
							$('#link').attr('disabled', true);
							$('#titleinput').show();
							$('#link').val('<?php echo rewrite_path(_NEWS_, '?p=news'); ?>');
							break;
						case 'all_zenpagecategorys':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#titleinput,#titlelabel,#link_row,#visible_row,#span_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("All Zenpage categories")); ?>');
							$('#description').html('<?php echo js_encode(gettext("This adds menu items for all Zenpage categories.")); ?>');
							break;
						case 'zenpagecategory':
							$('#albumselector,#pageselector,#custompageselector,#custompageselector,#titleinput,#link_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("Zenpage news category")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a link to a Zenpage News article category.")); ?>');
							$("#link").attr('disabled', true);
							$('#categoryselector').show();
							$('#titlelabel').html('<?php echo js_encode(gettext('Category')); ?>');
							$('#categoryselector').change(function() {
								$('#link').val($(this).val());
							});
							break;
						case 'custompage':
							$('#albumselector,#pageselector,#categoryselector,#link,').hide();
							$('#custompageselector').show();
							$('#selector').html('<?php echo js_encode(gettext("Custom page")); ?>');
							$('#description').html('<?php echo js_encode(gettext('Creates a link to a custom theme page as described in the theming tutorial.')); ?>');
							$('#link_label').html('<?php echo js_encode(gettext('Script page')); ?>');
							$('#titleinput').show();
							break;
						case "customlink":
							$('#albumselector,#pageselector,#categoryselector,#custompageselector').hide();
							$('#selector').html('<?php echo js_encode(gettext("Custom link")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a link outside the Zenphoto structure. Use of a full URL is recommended (e.g. http://www.domain.com).")); ?>');
							$('#link').removeAttr('disabled');
							$('#link_label').html('<?php echo js_encode(gettext('URL')); ?>');
							$('#titleinput').show();
							break;
						case 'menulabel':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#link_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("Label")); ?>');
							$('#description').html('<?php echo js_encode(gettext("Creates a <em>label</em> to use in menu structures).")); ?>');
							$('#titleinput').show();
							break;
						case 'menufunction':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector').hide();
							$('#selector').html('<?php echo js_encode(gettext("Function")); ?>');
							$('#description').html('<?php echo js_encode(gettext('Executes the PHP function provided.')); ?>');
							$('#link_label').html('<?php echo js_encode(gettext('Function')); ?>');
							$('#link').removeAttr('disabled');
							$('#titleinput').show();
							$('#include_li_label').show();
							break;
						case 'html':
							$('#albumselector,#pageselector,#categoryselector,#custompageselector,#span_row').hide();
							$('#selector').html('<?php echo js_encode(gettext("HTML")); ?>');
							$('#description').html('<?php echo js_encode(gettext('Inserts custom HTML.')); ?>');
							$('#link_label').html('<?php echo js_encode(gettext('HTML')); ?>');
							$('#link').removeAttr('disabled');
							$('#titleinput').show();
							$('#include_li_label').show();
							break;
						case "":
							$("#selector").html("");
							$("#add").hide();
							break;
					}
				}
				//]]> -->
			</script>
			<script type="text/javascript">
				//<!-- <![CDATA[
				$(document).ready(function() {
<?php
if (is_array($result)) {
	?>
						handleSelectorChange('<?php echo $result['type']; ?>');
	<?php
} else {
	?>
						$('#albumselector,#pageselector,#categoryselector,#titleinput').hide();
	<?php
}
?>
					$('#typeselector').change(function() {
						$('input').val(''); // reset all input values so we do not carry them over from one type to another
						$('#link').val('');
						handleSelectorChange($(this).val());
					});
				});
				//]]> -->
			</script>
			<h1>
				<?php
				if (is_array($result) && $result['id']) {
					echo gettext("Menu Manager: Edit Menu Item");
				} else {
					echo gettext("Menu Manager: Add Menu Item");
				}
				?>
			</h1>
			<?php
			if (isset($_GET['save']) && !isset($_GET['add'])) {
				?>
				<div class="messagebox fade-message">
					<h2>
						<?php echo gettext("Changes applied") ?>
					</h2>
				</div>
				<?php
			}
			?>
			<p class="buttons">
				<strong><a href="menu_tab.php?menuset=<?php echo $menuset; ?>"><img	src="../../images/arrow_left_blue_round.png" alt="" /><?php echo gettext("Back"); ?></a></strong>
				<span class="floatright">
					<strong><a href="menu_tab_edit.php?add&amp;menuset=<?php echo urlencode($menuset); ?>"><img src="../../images/add.png" alt="" /> <?php echo gettext("Add Menu Items"); ?></a></strong>
				</span>
			</p>
			<br class="clearall" /><br />
			<div class="box" style="padding:15px; margin-top: 10px">
				<?php
				$action = $type = $id = $link = '';
				if (is_array($result)) {
					$type = $result['type'];
					$id = $result['id'];
					if (array_key_exists('link', $result)) {
						$link = $result['link'];
					}
					$action = !empty($id);
				}
				if (isset($_GET['add']) && !isset($_GET['save'])) {
					$add = '&amp;add'
					?>
					<select id="typeselector" name="typeselector">
						<option value=""><?php echo gettext("*Select the type of the menus item you wish to add*"); ?></option>
						<option value="all_items"><?php echo gettext("All menu items"); ?></option>
						<option value="galleryindex"><?php echo gettext("Gallery index"); ?></option>
						<option value="all_albums"><?php echo gettext("All Albums"); ?></option>
						<option value="album"><?php echo gettext("Album"); ?></option>
						<?php
						if (extensionEnabled('zenpage')) {
							?>
							<option value="all_zenpagepages"><?php echo gettext("All Zenpage pages"); ?></option>
							<option value="zenpagepage"><?php echo gettext("Zenpage page"); ?></option>
							<option value="zenpagenewsindex"><?php echo gettext("Zenpage news index"); ?></option>
							<option value="all_zenpagecategorys"><?php echo gettext("All Zenpage news categories"); ?></option>
							<option value="zenpagecategory"><?php echo gettext("Zenpage news category"); ?></option>
							<?php
						}
						?>
						<option value="custompage"><?php echo gettext("Custom theme page"); ?></option>
						<option value="customlink"><?php echo gettext("Custom link"); ?></option>
						<option value="menulabel"><?php echo gettext("Label"); ?></option>
						<option value="menufunction"><?php echo gettext("Function"); ?></option>
						<option value="html"><?php echo gettext("HTML"); ?></option>
					</select>
					<?php
				} else {
					$add = '&amp;update';
				}
				?>
				<form method="post" id="add" name="add" action="menu_tab_edit.php?save<?php echo $add;
				if ($menuset)
					echo '&amp;menuset=' . $menuset;
				?>" style="display: none">
<?php XSRFToken('update_menu'); ?>
					<input type="hidden" name="update" id="update" value="<?php echo html_encode($action); ?>" />
					<input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
					<input type="hidden" name="link-old" id="link-old" value="<?php echo html_encode($link); ?>" />
					<input type="hidden" name="type" id="type" value="<?php echo $type; ?>" />
					<table style="width: 80%">
						<?php
						if (is_array($result)) {
							$selector = html_encode($menuset);
						} else {
							$result = array('id'				 => NULL, 'title'			 => '', 'link'			 => '', 'show'			 => 1, 'type'			 => NULL, 'include_li' => 1, 'span_id'		 => '', 'span_class' => '');
							$selector = getMenuSetSelector(false);
						}
						?>
						<tr>
							<td colspan="2"><?php printf(gettext("Menu <em>%s</em>"), $selector); ?></td>
						</tr>
						<tr style="vertical-align: top">
							<td style="width: 13%"><?php echo gettext("Type:"); ?></td>
							<td id="selector"></td>
						</tr>
						<tr style="vertical-align: top">
							<td><?php echo gettext("Description:"); ?></td>
							<td id="description"></td>
						</tr>
						<tr>
							<td><span id="titlelabel"><?php echo gettext("Title:"); ?></span></td>
							<td>
								<span id="titleinput"><?php print_language_string_list($result['title'], "title", false, NULL, '', 100); ?></span>
								<?php
								printAlbumsSelector($result['link']);
								if (class_exists('Zenpage')) {
									printZenpagePagesSelector($result['link']);
									printZenpageNewsCategorySelector($result['link']);
								}
								?>
							</td>
						</tr>
						<tr id="link_row">
							<td><span id="link_label"></span></td>
							<td>
<?php printCustomPageSelector($result['link']); ?>
								<input name="link" type="text" size="100" id="link" value="<?php echo html_encode($result['link']); ?>" />
							</td>
						</tr>
						<tr id="visible_row">
							<td>
								<label id="show_visible" for="show" style="display: inline">
									<input name="show" type="checkbox" id="show" value="1" <?php
												 if ($result['show'] == 1) {
													 echo "checked='checked'";
												 }
												 ?> style="display: inline" />
<?php echo gettext("published"); ?>
								</label>
							</td>
							<td>
								<label id="include_li_label" style="display: inline">
									<input name="include_li" type="checkbox" id="include_li" value="1" <?php
if ($result['include_li'] == 1) {
	echo "checked='checked'";
}
?> style="display: inline" />
								<?php echo gettext("Include <em>&lt;LI&gt;</em> element"); ?>
								</label>
							</td>
						</tr>
						<tr id="span_row">
							<td>
							<label>
								<input name="span" type="checkbox" id="span" value="1" <?php
								if ($result['span_id'] || $result['span_class']) {
									echo "checked='checked'";
								}
								?> style="display: inline" />
<?php echo gettext("Add <em>span</em> tags"); ?>
								</label>
							</td>
							<td>
						<?php echo gettext('ID'); ?>
								<input name="span_id" type="text" size="20" id="span_id" value="<?php echo html_encode($result['span_id']); ?>" />
						<?php echo gettext('Class'); ?>
								<input name="span_class" type="text" size="20" id="span_class" value="<?php echo html_encode($result['span_class']); ?>" />
							</td>
						</tr>
<?php
if (is_array($result) && !empty($result['type'])) {
	$array = getItemTitleAndURL($result);
	if (!$array['valid']) {
		?>
								<tr>
									<td colspan="2">
										<span class="notebox"><?php printf(gettext('Target does not exists in <em>%1$s</em> theme'), $array['theme']); ?></span>
									</td>
								</tr>
		<?php
	}
}
?>
					</table>
					<p class="buttons">
						<button type="submit"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
						<button type="reset"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
					</p>
					<br class="clearall" /><br />
				</form>
			</div>
		</div>
	</div>
<?php printAdminFooter(); ?>

</body>
</html>
