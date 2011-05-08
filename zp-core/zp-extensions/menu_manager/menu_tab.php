<?php
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once(dirname(dirname(dirname(__FILE__))).'/template-functions.php');
if (getOption('zp_plugin_zenpage')) {
	require_once(dirname(dirname(dirname(__FILE__))).'/'.PLUGIN_FOLDER.'/zenpage/zenpage-admin-functions.php');
}
require_once(dirname(dirname(dirname(__FILE__))).'/'.PLUGIN_FOLDER.'/menu_manager/menu_manager-admin-functions.php');

admin_securityChecks(NULL, currentRelativeURL(__FILE__));

$page = 'edit';

$menuset = checkChosenMenuset('');
if (empty($menuset)) {	//	setup default menuset
	$result = query_full_array("SELECT DISTINCT menuset FROM ".prefix('menu'));
	if (is_array($result)) {	// default to the first one
		$set = array_shift($result);
		$menuset = $set['menuset'];
	} else {
		$menuset = 'default';
	}
	$_GET['menuset'] = $menuset;
}

$reports = array();
if(isset($_POST['update'])) {
	XSRFdefender('update_menu');
	processMenuBulkActions($reports);
	updateItemsSortorder($reports);
}
if (isset($_GET['delete'])) {
	XSRFdefender('delete_menu');
	$sql = 'SELECT * FROM '.prefix('menu').' WHERE `id`='.sanitize_numeric($_GET['id']);
	$result = query_single_row($sql);
	if (empty($result)) {
		$reports[] = "<p class='errorbox' >".gettext('Menu item deleted failed')."</p>";
	} else {
		$_GET['menuset'] = $menuset = $result['menuset'];
		$sql = 'DELETE FROM '.prefix('menu').' WHERE `id`='.$result['id'];
		query($sql);
		$sql = 'DELETE FROM '.prefix('menu').' WHERE `menuset`="'.$menuset.'" AND `sort_order` LIKE "'.$result['sort_order'].'/%"';
		query($sql);
		$reports[] =  "<p class='messagebox fade-message'>".gettext('Menu item deleted')."</p>";
	}
}
if (isset($_GET['deletemenuset'])) {
	XSRFdefender('delete_menu');
	$sql = 'DELETE FROM '.prefix('menu').' WHERE `menuset`='.db_quote(sanitize($_GET['deletemenuset']));
	query($sql);
	$_menu_manager_items = array();
	$delmsg =  "<p class='messagebox fade-message'>".sprintf(gettext("Menu set '%s' deleted"),html_encode($_GET['deletemenuset']))."</p>";
}
// publish or un-publish page by click
if(isset($_GET['publish'])) {
	XSRFdefender('update_menu');
	publishItem($_GET['id'],$_GET['show'],$menuset);
}

printAdminHeader('menu');
printSortableHead();
?>
</head>
<body>
<?php	printLogoAndLinks(); ?>
<div id="main">
<?php
printTabs();
?>
<div id="content">
<?php
zp_apply_filter('admin_note','menu', '');
foreach ($reports as $report) {
	echo $report;
}

$sql = 'SELECT COUNT(DISTINCT `menuset`) FROM '.prefix('menu');
$result = query($sql);
$count = db_result($result, 0);
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	 function newMenuSet() {
		var new_menuset = prompt("<?php echo gettext('Menuset id'); ?>","<?php echo 'menu_'.$count; ?>");
		if (new_menuset) {
			window.location = '?menuset='+encodeURIComponent(new_menuset);
		}
	};
	function deleteMenuSet() {
		if (confirm('<?php printf(gettext('Ok to delete menu set %s? This cannot be undone!'),html_encode($menuset)); ?>')) {
			launchScript('',['deletemenuset=<?php echo html_encode($menuset); ?>','XSRFToken=<?php echo getXSRFToken('delete_menu')?>']);
		}
	};
	function deleteMenuItem(item,warn) {
		if (confirm(warn)) {
			launchScript('',['delete','id='+item,'menuset=<?php echo $menuset; ?>','XSRFToken=<?php echo getXSRFToken('delete_menu')?>']);
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
<h1><?php echo gettext("Menu Manager")."<small>"; printf(gettext(" (Menu set: %s)"), html_encode($menuset)); echo "</small>"; ?></h1>

<form action="menu_tab.php?menuset=<?php echo $menuset; ?>" method="post" name="update" onsubmit="return confirmAction();">
	<?php XSRFToken('update_menu'); ?>
<p>
<?php echo gettext("Drag the items into the order, including sub levels, you wish them displayed. This lets you create arbitrary menus and place them on your theme pages. Use printCustomMenu() to place them on your pages."); ?>
</p>
<p class="notebox">
<?php echo gettext("<strong>IMPORTANT:</strong> This menu's order is completely independent from any order of albums or pages set on the other admin pages. It is recommend to uses is with customized themes only that do not use the standard Zenphoto display structure. Standard Zenphoto functions like the breadcrumb functions or the next_album() loop for example will NOT take care of this menu's structure!");?>
</p>
<span class="buttons">
	<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
	<strong><a href="menu_tab_edit.php?add&amp;menuset=<?php echo urlencode($menuset); ?>" title="<?php echo gettext("Add Menu Items"); ?>"><img src="../../images/add.png" alt="" /> <?php echo gettext("Add Menu Items"); ?></a></strong>
	<strong><a href="javascript:newMenuSet();" title="<?php echo gettext("Add Menu set"); ?>"><img src="../../images/add.png" alt="" /> <?php echo gettext("Add Menu set"); ?></a></strong>
	<div class="floatright">
		<a title="<?php echo gettext('options')?>" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-options.php?'page=options&amp;tab=plugin&amp;show-menu_manager#menu_manager">
			<strong><?php echo gettext('Options')?></strong>
		</a>
	</div>
</span>
<br clear="all" /><br />

<div class="bordered">
	<div class="headline">
		<strong><?php echo gettext("Edit the menu"); ?></strong>
		<?php echo getMenuSetSelector(true); ?>
		<?php printItemStatusDropdown(); ?>
		<?php
		$checkarray = array(
				gettext('*Bulk actions*') => 'noaction',
				gettext('Delete') => 'deleteall',
				gettext('Set to published') => 'showall',
				gettext('Set to unpublished') => 'hideall'
		);
		?>
		<span style="float:right">
			<?php
				if ($count > 0) {
					$buttontext = sprintf(gettext("Delete menu set '%s'"),html_encode($menuset));
					?>
					<span class="buttons">
						<strong><a href="javascript:deleteMenuSet();" title="<?php echo $buttontext; ?>"><img src="../../images/fail.png" alt="" /><?php echo $buttontext; ?></a></strong>
					</span>
					<?php
				}
			?>
			<select name="checkallaction" id="checkallaction" size="1">
				<?php generateListFromArray(array('noaction'), $checkarray,false,true); ?>
			</select>
		</span>
	</div>
	<br clear="all" />
	<div class="subhead">
		<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
		</label>
	</div>
			<ul class="page-list">
			<?php
			if(isset($_GET['visible'])) {
				$visible = sanitize($_GET['visible']);
			} else {
				$visible = 'all';
			}
			$items = getMenuItems($menuset, $visible);
			printItemsList($items);
			?>
			</ul>
</div>
<br />
<span id="serializeOutput" /></span>
<input name="update" type="hidden" value="Save Order" />
<p class="buttons">
	<button class="serialize" type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
</p>
</form>
	<ul class="iconlegend">
	<li><img src="../../images/lock_2.png" alt="" /><?php echo gettext("Menu target is password protected"); ?></li>
	<li><img src="../../images/pass.png" alt="" /><img	src="../../images/action.png" alt="" /><?php echo gettext("Show/hide"); ?></li>
	<li><img src="../zenpage/images/view.png" alt="" /><?php echo gettext("View"); ?></li>
	<li><img src="../../images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
	</ul>
</div>
</div>
<?php printAdminFooter(); ?>

</body>
</html>