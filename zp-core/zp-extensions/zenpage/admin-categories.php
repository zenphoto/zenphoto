<?php
/**
 * zenpage admin-categories.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH",4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once("zenpage-admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL(__FILE__));

$reports = array();
if(isset($_POST['processcheckeditems'])) {
	XSRFdefender('checkeditems');
	processZenpageBulkActions('Category',$reports);
	updateItemSortorder('categories',$reports);
}
if(isset($_GET['delete'])) {
	XSRFdefender('delete_category');
	$reports[] = deleteCategory($_GET['delete']);
}
if(isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$x = 	$_zp_zenpage->getCategory(sanitize_numeric($_GET['id']));
	$obj = new ZenpageCategory($x['titlelink']);
	$obj->set('hitcounter',0);
	$obj->save();
}
if(isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = new ZenpageCategory(sanitize($_GET['titlelink']));
	$obj->setShow(sanitize_numeric($_GET['publish']));
	$obj->save();
}
if(isset($_GET['save'])) {
	XSRFdefender('save_categories');
	addCategory($reports);
}
if(isset($_GET['id'])){
	$x = 	$_zp_zenpage->getCategory(sanitize_numeric($_GET['id']));
	$result = new ZenpageCategory($x['titlelink']);
} else if(isset($_GET['update'])) {
	XSRFdefender('update_categories');
	$result = updateCategory($reports);
} else {
	$result = new ZenpageCategory('');
}

printAdminHeader('news','categories');
zp_apply_filter('texteditor_config', '','zenpage');
printSortableHead();
zenpageJSCSS();
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteCategory = "<?php echo gettext("Are you sure you want to delete this category? THIS CANNOT BE UNDONE!"); ?>";
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}
	function toggleTitlelink() {
		if(jQuery('#edittitlelink:checked').val() == 1) {
			$('#titlelink').removeAttr("disabled");
		} else {
			$('#titlelink').attr("disabled", true);
		}
	};
	$(document).ready(function() {
		$('form [name=checkeditems] #checkallaction').change(function(){
			if($(this).val() == 'deleteall') {
				// general text about "items" so it can be reused!
				alert('<?php echo js_encode(gettext('Are you sure you want to delete all selected items? THIS CANNOT BE UNDONE!')); ?>');
			}
		});
	});
	// ]]> -->
</script>
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
		$subtab = printSubtabs();
		?>
		<div id="tab_articles" class="tabbox">
			<?php
			zp_apply_filter('admin_note', 'categories', $subtab);
			foreach ($reports as $report) {
				echo $report;
			}
			?>
			<h1>
			<?php	echo gettext('Categories'); ?><span class="zenpagestats"><?php printCategoriesStatistic();?></span></h1>
			<form action="admin-categories.php?page=news&amp;tab=categories" method="post" id="checkeditems" name="checkeditems" onsubmit="return confirmAction();">
				<?php XSRFToken('checkeditems');?>
				<input	type="hidden" name="action" id="action" value="checkeditems" />
				<input name="processcheckeditems" type="hidden" value="apply" />
				<p class="buttons">
					<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
						<img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
					</button>
					<?php
					if (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
						?>
						<span class="floatright">
							<strong><a href="admin-edit.php?category&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add')?>" title="<?php echo gettext('New category'); ?>"><img src="images/add.png" alt="" /> <?php echo gettext('New category'); ?></a></strong>
						</span>
						<?php
						}
					?>
				</p>
				<br clear="all" /><br />
				<div class="bordered">
					<div class="headline"><?php echo gettext('Edit this Category'); ?>
					<?php
					$checkarray = array(
													gettext('*Bulk actions*') => 'noaction',
													gettext('Delete') => 'deleteall',
													gettext('Add tags to articles') => 'alltags',
													gettext('Clear tags of articles') => 'clearalltags',
													gettext('Reset hitcounter') => 'resethitcounter'
													);
					printBulkActions($checkarray);
					?>
					</div>
					<div class="subhead" >
						<label style="float: right"><?php echo gettext("Check All"); ?>
							<input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
						</label>
					</div>
						<ul class="page-list">
						<?php $toodeep = printNestedItemsList('cats-sortablelist','','');	?>
						</ul>
					</div>
					<?php
					if ($toodeep) {
						echo '<div class="errorbox">';
						echo  '<h2>'.gettext('The sort position of the indicated items cannot be recorded because the nesting is too deep. Please move them to a higher level and save your order.').'</h2>';
						echo '</div>';
					}
					?>
					<span id="serializeOutput" /></span>
					<input name="update" type="hidden" value="Save Order" />
					<p class="buttons">
						<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
							<img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
						</button>
					</p>
					<ul class="iconlegend">
					<?php
					if (GALLERY_SECURITY != 'private') {
						?>
						<li><img src="../../images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
						<?php
					}
					?>
					<li><img src="images/view.png" alt="" /><?php echo gettext('View'); ?></li>
					<li><img src="../../images/reset.png" alt="" /><?php echo gettext('Reset hitcounter'); ?></li>
					<li><img src="../../images/fail.png" alt="" /><?php echo gettext('Delete category'); ?></li>
				</ul>
			</form>

			<br style="clear: both" /><br />
		</div> <!-- tab_articles -->
	</div> <!-- content -->
	<script type="text/javascript">
</div> <!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>
