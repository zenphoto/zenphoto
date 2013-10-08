<?php
/**
 * zenpage admin-categories.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH", 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once("zenpage-admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL());

$reports = array();
if (isset($_GET['bulkaction'])) {
	$reports[] = zenpageBulkActionMessage(sanitize($_GET['bulkaction']));
}
if (isset($_POST['action'])) {
	XSRFdefender('checkeditems');
	if ($_POST['checkallaction'] == 'noaction') {
		if (updateItemSortorder('categories', $reports)) {
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Sort order saved.") . "</p>";
		} else {
			$reports[] = "<p class='notebox fade-message'>" . gettext("Nothing changed.") . "</p>";
		}
	} else {
		$action = processZenpageBulkActions('Category');
		bulkActionRedirect($action);
	}
}
if (isset($_GET['delete'])) {
	XSRFdefender('delete_category');
	$reports[] = deleteCategory(sanitize($_GET['delete']));
}
if (isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$x = $_zp_zenpage->getCategory(sanitize_numeric($_GET['id']));
	$obj = new ZenpageCategory($x['titlelink']);
	$obj->set('hitcounter', 0);
	$obj->save();
}
if (isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = new ZenpageCategory(sanitize($_GET['titlelink']));
	$obj->setShow(sanitize_numeric($_GET['publish']));
	$obj->save();
}
if (isset($_GET['save'])) {
	XSRFdefender('save_categories');
	updateCategory($reports, true);
}
if (isset($_GET['id'])) {
	$x = $_zp_zenpage->getCategory(sanitize_numeric($_GET['id']));
	$result = new ZenpageCategory($x['titlelink']);
} else if (isset($_GET['update'])) {
	XSRFdefender('update_categories');
	$result = updateCategory($reports);
} else {
	$result = new ZenpageCategory('');
}

printAdminHeader('news', 'categories');
zp_apply_filter('texteditor_config', '', 'zenpage');
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
		if (jQuery('#edittitlelink:checked').val() == 1) {
			$('#titlelink').removeAttr("disabled");
		} else {
			$('#titlelink').attr("disabled", true);
		}
	}

	$(document).ready(function() {
		$('form [name=checkeditems] #checkallaction').change(function() {
			if ($(this).val() == 'deleteall') {
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
				if ($reports) {
					$show = array();
					preg_match_all('/<p class=[\'"](.*?)[\'"]>(.*?)<\/p>/', implode('', $reports), $matches);
					foreach ($matches[1] as $key => $report) {
						$show[$report][] = $matches[2][$key];
					}
					foreach ($show as $type => $list) {
						echo '<p class="' . $type . '">' . implode('<br />', $list) . '</p>';
					}
				}
				?>
				<h1>
					<?php echo gettext('Categories'); ?><span class="zenpagestats"><?php printCategoriesStatistic(); ?></span></h1>
				<form action="admin-categories.php?page=news&amp;tab=categories" method="post" id="checkeditems" name="checkeditems" onsubmit="return confirmAction();">
					<?php XSRFToken('checkeditems'); ?>
					<input	type="hidden" name="action" id="action" value="update" />
					<p class="buttons">
						<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
							<img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
						</button>
						<?php
						if (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
							?>
							<span class="floatright">
								<strong><a href="admin-edit.php?newscategory&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>" title="<?php echo gettext('New category'); ?>"><img src="images/add.png" alt="" /> <?php echo gettext('New category'); ?></a></strong>
							</span>
							<?php
						}
						?>
					</p>
					<br class="clearall" /><br />
					<div class="bordered">
						<div class="headline"><?php echo gettext('Edit this Category'); ?>
							<?php
							$checkarray = array(
											gettext('Set to published')				 => 'showall',
											gettext('Set to unpublished')			 => 'hideall',
											gettext('*Bulk actions*')					 => 'noaction',
											gettext('Delete')									 => 'deleteall',
											gettext('Add tags to articles')		 => 'alltags',
											gettext('Clear tags of articles')	 => 'clearalltags'
							);
							if (extensionEnabled('hitcounter')) {
								$checkarray['Reset hitcounter'] = 'resethitcounter';
							}
							printBulkActions($checkarray);
							?>
						</div>
						<div class="subhead" >
							<label style="float: right"><?php echo gettext("Check All"); ?>
								<input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
							</label>
						</div>
						<ul class="page-list">
							<?php $toodeep = printNestedItemsList('cats-sortablelist', '', ''); ?>
						</ul>
					</div>
					<?php
					if ($toodeep) {
						echo '<div class="errorbox">';
						echo '<h2>' . gettext('The sort position of the indicated items cannot be recorded because the nesting is too deep. Please move them to a higher level and save your order.') . '</h2>';
						echo '</div>';
					}
					?>
					<span id="serializeOutput"></span>
					<input name="update" type="hidden" value="Save Order" />
					<p class="buttons">
						<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
							<img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
						</button>
					</p>
					<ul class="iconlegend">
						<?php
						if (GALLERY_SECURITY == 'public') {
							?>
							<li><img src="../../images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
							<?php
						}
						?>
						<li><img src="images/view.png" alt="" /><?php echo gettext('View'); ?></li>
						<?php
						if (extensionEnabled('hitcounter')) {
							?>
							<li><img src="../../images/reset.png" alt="" /><?php echo gettext('Reset hitcounter'); ?></li>
							<?php
						}
						?>
						<li><img src="../../images/fail.png" alt="" /><?php echo gettext('Delete category'); ?></li>
					</ul>
				</form>

				<br style="clear: both" /><br />
			</div> <!-- tab_articles -->
		</div> <!-- content -->

	</div> <!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>
