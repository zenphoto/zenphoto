<?php
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/template-functions.php');
if (extensionEnabled('zenpage')) {
	require_once(dirname(dirname(dirname(__FILE__))) . '/' . PLUGIN_FOLDER . '/zenpage/admin-functions.php');
}
require_once(dirname(dirname(dirname(__FILE__))) . '/' . PLUGIN_FOLDER . '/menu_manager/menu_manager-admin-functions.php');

admin_securityChecks(NULL, currentRelativeURL());

$page = 'edit';

$menuset = checkChosenMenuset('');
if (empty($menuset)) { //	setup default menuset
	$result = query_full_array("SELECT DISTINCT menuset FROM " . prefix('menu'));
	if (is_array($result)) { // default to the first one
		$set = array_shift($result);
		$menuset = $set['menuset'];
	} else {
		$menuset = 'default';
	}
	$_GET['menuset'] = $menuset;
}

$reports = array();
if (isset($_POST['update'])) {
	XSRFdefender('update_menu');
	if ($_POST['checkallaction'] == 'noaction') {
		$reports[] = updateItemsSortorder();
	} else {
		$report = processMenuBulkActions();
		if ($report) {
			$reports[] = $report;
		} else {
			$reports[] = '<p class="notebox fade-message">' . gettext('Nothing changed') . '</p>';
		}
	}
}

if (isset($_GET['delete'])) {
	XSRFdefender('delete_menu');
	$sql = 'SELECT * FROM ' . prefix('menu') . ' WHERE `id`=' . sanitize_numeric($_GET['id']);
	$result = query_single_row($sql);
	if (empty($result)) {
		$reports[] = "<p class='errorbox' >" . gettext('Menu item deleted failed') . "</p>";
	} else {
		$_GET['menuset'] = $menuset = $result['menuset'];
		$sql = 'DELETE FROM ' . prefix('menu') . ' WHERE `id`=' . $result['id'];
		query($sql);
		$sql = 'DELETE FROM ' . prefix('menu') . ' WHERE `menuset`="' . $menuset . '" AND `sort_order` LIKE "' . $result['sort_order'] . '-%"';
		query($sql);
		$reports[] = "<p class='messagebox fade-message'>" . gettext('Menu item deleted') . "</p>";
	}
}
if (isset($_GET['deletemenuset'])) {
	XSRFdefender('delete_menu');
	$sql = 'DELETE FROM ' . prefix('menu') . ' WHERE `menuset`=' . db_quote(sanitize($_GET['deletemenuset']));
	query($sql);
	$_menu_manager_items = array();
	$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Menu “%s” deleted"), html_encode(sanitize($_GET['deletemenuset']))) . "</p>";
}
if (isset($_GET['dupmenuset'])) {
	XSRFdefender('dup_menu');
	$oldmenuset = sanitize($_GET['dupmenuset']);
	$_GET['menuset'] = $menuset = sanitize($_GET['targetname']);
	$menuitems = query_full_array('SELECT * FROM ' . prefix('menu') . ' WHERE `menuset`=' . db_quote($oldmenuset) . ' ORDER BY `sort_order`');
	foreach ($menuitems as $key => $item) {
		$order = count(explode('-', $item['sort_order'])) - 1;
		$menuitems[$key]['nesting'] = $order;
	}
	if (createMenuIfNotExists($menuitems, $menuset)) {
		$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Menu “%s” duplicated"), html_encode($oldmenuset)) . "</p>";
	} else {
		$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Menu “%s” already exists"), html_encode($menuset)) . "</p>";
	}
}
// publish or un-publish page by click
if (isset($_GET['publish'])) {
	XSRFdefender('update_menu');
	publishItem($_GET['id'], $_GET['show'], $menuset);
}

printAdminHeader('menu');
printSortableHead();
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
			$count = db_count('menu', NULL, 'DISTINCT `menuset`');
			?>
			<script type="text/javascript">
				//<!-- <![CDATA[
				function newMenuSet() {
					var new_menuset = prompt("<?php echo gettext('Name for new menu:'); ?>", "<?php echo 'menu_' . $count; ?>");
					if (new_menuset) {
						window.location = '?menuset=' + encodeURIComponent(new_menuset);
					}
				}
				function dupMenuSet() {
					var targetname = prompt('<?php echo gettext('Name for new menu:'); ?>', '<?php printf(gettext('Copy_of_%s'), $menuset); ?>');
					if (targetname) {
						launchScript('', ['dupmenuset=<?php echo html_encode($menuset); ?>', 'targetname=' + encodeURIComponent(targetname), 'XSRFToken=<?php echo getXSRFToken('dup_menu') ?>']);
					}
				}
				function deleteMenuSet() {
					if (confirm('<?php printf(gettext('Ok to delete menu %s? This cannot be undone!'), html_encode($menuset)); ?>')) {
						launchScript('', ['deletemenuset=<?php echo html_encode($menuset); ?>', 'XSRFToken=<?php echo getXSRFToken('delete_menu') ?>']);
					}
				}
				function deleteMenuItem(item, warn) {
					if (confirm(warn)) {
						launchScript('', ['delete', 'id=' + item, 'menuset=<?php echo $menuset; ?>', 'XSRFToken=<?php echo getXSRFToken('delete_menu') ?>']);
					}
				}
				function confirmAction() {
					if ($('#checkallaction').val() == 'deleteall') {
						return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
					} else {
						return true;
					}
				}
				// ]]> -->
			</script>
			<?php
			zp_apply_filter('admin_note', 'menu', '');
			?>

			<h1><?php
				echo gettext("Menu Manager") . "<small>";
				printf(gettext(" (Menu: %s)"), html_encode($menuset));
				echo "</small>";
				?></h1>
			<div class="tabbox">
				<form class="dirtylistening" onReset="setClean('update_form');" id="update_form" action="menu_tab.php?menuset=<?php echo $menuset; ?>" method="post" name="update" onsubmit="return confirmAction();" autocomplete="off">
					<?php XSRFToken('update_menu'); ?>
					<p>
						<?php echo gettext("Drag the items into the order and nesting you wish displayed. Place the menu on your theme pages by calling printCustomMenu()."); ?>
					</p>
					<p class="notebox">
						<?php echo gettext("<strong>IMPORTANT:</strong> This menu’s order is completely independent from any order of albums or pages set on the other admin pages. Use with customized themes that do not wish the standard zenphoto display structure. zenphoto functions such as the breadcrumb functions and the next_album() loop will NOT reflect of this menu’s structure!"); ?>
					</p>
					<?php
					foreach ($reports as $report) {
						echo $report;
					}
					?>
					<span class="buttons">
						<button class="serialize" type="submit">
							<?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong>
						</button>
						<div class="floatright">
							<a href="javascript:newMenuSet();">
								<?php echo PLUS_ICON; ?>
								<strong><?php echo gettext("New Menu"); ?></strong>
							</a>
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-options.php?'page=options&amp;tab=plugin&amp;single=menu_manager#menu_manager">
								<?php echo OPTIONS_ICON; ?>
								<strong><?php echo gettext('Options') ?></strong>
							</a>
						</div>
					</span>
					<br class="clearall">
					<br />

					<div class="bordered">
						<?php
						$selector = getMenuSetSelector(true);
						if ($selector) {
							?>
							<div class="headline-plain">
								<strong><?php echo gettext("Edit the menu"); ?></strong>
								<?php
								echo $selector;
								printItemStatusDropdown();
								$checkarray = array(
										gettext('*Bulk actions*') => 'noaction',
										gettext('Delete') => 'deleteall',
										gettext('Show') => 'showall',
										gettext('Hide') => 'hideall'
								);
								?>
								<span style="float:right">
									<?php
									if ($count > 0) {
										?>
										<span class="buttons">
											<a href="javascript:dupMenuSet();" title="<?php printf(gettext('Duplicate %s menu'), $menuset); ?>"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/page_white_copy.png" alt="" /><strong><?php echo gettext("Duplicate menu"); ?></strong></a>
										</span>
										<span class="buttons">
											<a href="javascript:deleteMenuSet();" title="<?php printf(gettext('Delete %s menu'), $menuset); ?>">
												<?php echo WASTEBASKET; ?>
												<strong><?php echo gettext("Delete menu"); ?></strong>
											</a>
										</span>
										<?php
									}
									?>
									<span class="buttons">
										<a href="menu_tab_edit.php?add&amp;menuset=<?php echo urlencode($menuset); ?>">
											<?php echo PLUS_ICON; ?>
											<strong><?php echo gettext("Add Menu Items"); ?></strong>
										</a>
									</span>
									<select name="checkallaction" id="checkallaction" size="1">
										<?php generateListFromArray(array('noaction'), $checkarray, false, true); ?>
									</select>
								</span>
							</div>
							<br class="clearall">
							<div class="subhead">
								<label style="float: right">
									<?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
								</label>
							</div>
							<ul class="page-list">
								<?php
								if (isset($_GET['visible'])) {
									$visible = sanitize($_GET['visible']);
								} else {
									$visible = 'all';
								}
								$items = getMenuItems($menuset, $visible);
								printItemsList($items);
								?>
							</ul>
							<?php
						} else {
							?>
							<div class="headline-plain">
								<strong><?php echo gettext("No menus exist"); ?></strong>
							</div>
							<br class="clearall">
							<?php
						}
						?>
					</div>
					<br />
					<span id="serializeOutput"></span>
					<input name="update" type="hidden" value="Save Order" />
					<p class="buttons">
						<button class="serialize" type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button>
					</p>
				</form>
				<ul class="iconlegend">
					<li>
						<?php echo LOCK; ?>
						<?php echo gettext("Menu target is password protected"); ?>
					</li>
					<li>
						<?php echo CHECKMARK_GREEN; ?>
						<?php echo EXCLAMATION_RED; ?>
						<?php echo gettext("Visible/Hidden"); ?>
					</li>
					<li>
						<?php echo BULLSEYE_BLUE; ?>
						<?php echo gettext("View"); ?>
					</li>
					<li>
						<?php echo WASTEBASKET; ?>
						<?php echo gettext("Delete"); ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>

</body>
</html>
