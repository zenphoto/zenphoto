<?php
/**
 * zenpage admin-categories.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins/zenpage
 */
define("OFFSET_PATH", 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once("admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL());

$reports = array();

if (isset($_POST['action'])) {
	XSRFdefender('checkeditems');
	if (updateItemSortorder('categories', $reports)) {
		$reports[] = "<p class='messagebox fade-message'>" . gettext("Sort order saved.") . "</p>";
	}
	$action = processZenpageBulkActions('Category');
	if ($report = zenpageBulkActionMessage($action)) {
		$reports[] = $report;
	} else {
		if (empty($reports)) {
			$reports[] = "<p class='notebox fade-message'>" . gettext("Nothing changed.") . "</p>";
		}
	}
}
if (isset($_GET['delete'])) {
	XSRFdefender('delete_category');
	$reports[] = deleteZenpageObj(newCategory(sanitize($_GET['delete'])));
}
if (isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$x = $_zp_CMS->getCategory(sanitize_numeric($_GET['id']));
	$obj = newCategory($x['titlelink']);
	$obj->set('hitcounter', 0);
	$obj->save();
}
if (isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = newCategory(sanitize($_GET['titlelink']));
	$obj->setShow(sanitize_numeric($_GET['publish']));
	$obj->save();
}
if (isset($_GET['save'])) {
	XSRFdefender('save_categories');
	updateCategory($reports, true);
}

if (empty($reports)) {
	if (isset($_SESSION['reports'])) {
		$reports = $_SESSION['reports'];
		unset($_SESSION['reports']);
	}
} else {
	$_SESSION['reports'] = $reports;

	var_dump($reports);
	$uri = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-categories.php';
	header('Location: ' . $uri);
	exitZP();
}

$_zp_CMS = new CMS();

printAdminHeader('news', 'categories');
zp_apply_filter('texteditor_config', 'zenpage');
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

	window.addEventListener('load', function () {
		$('form [name=checkeditems] #checkallaction').change(function () {
			if ($(this).val() == 'deleteall') {
				// general text about "items" so it can be reused!
				alert('<?php echo js_encode(gettext('Are you sure you want to delete all selected items? THIS CANNOT BE UNDONE!')); ?>');
			}
		});
	}, false);
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
			$subtab = getCurrentTab();
			zp_apply_filter('admin_note', 'categories', $subtab);
			?>
			<h1>
				<?php echo gettext('Categories'); ?>
			</h1>

			<div class="tabbox">
				<?php
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
				$categories = $_zp_CMS->getAllCategories();
				foreach ($categories as $key => $cat) {
					$catobj = newCategory($cat['titlelink']);
					if (!($catobj->subRights() & MANAGED_OBJECT_RIGHTS_EDIT)) {
						unset($categories[$key]);
					}
				}

				if (!empty($categories) || zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
					?>
					<span class="zenpagestats"><?php printCategoriesStatistic(); ?></span>
					<form class="dirtylistening" onReset="setClean('checkeditems');" action="admin-categories.php?page=news&amp;tab=categories" method="post" id="checkeditems" name="checkeditems" onsubmit="return confirmAction();" autocomplete="off">
						<?php XSRFToken('checkeditems'); ?>
						<input	type="hidden" name="action" id="action" value="update" />
						<p class="buttons">
							<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
								<?php echo CHECKMARK_GREEN; ?> <?php echo gettext('Apply'); ?></strong>
							</button>
							<?php
							if (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
								?>
								<span class="floatright">
									<a href="admin-edit.php?newscategory&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>" title="<?php echo gettext('New category'); ?>">
										<?php echo PLUS_ICON; ?>
										<strong>
											<?php echo gettext('New category'); ?>
										</strong>
									</a>
								</span>
								<?php
							}
							?>
						</p>
						<br class="clearall">
						<br />
						<div class="headline">
							<?php
							echo gettext('Edit this Category');
							$checkarray = array(
									gettext('Set to published') => 'showall',
									gettext('Set to unpublished') => 'hideall',
									gettext('*Bulk actions*') => 'noaction',
									gettext('Delete') => 'deleteall',
							);
							if (extensionEnabled('hitcounter')) {
								$checkarray[gettext('Reset hitcounter')] = 'resethitcounter';
							}
							$checkarray = zp_apply_filter('bulk_category_actions', $checkarray);
							printBulkActions($checkarray);
							?>
						</div>
						<div class="bordered">

							<div class="subhead">
								<label style="float: right;"><?php echo gettext("Check All"); ?>
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
								<?php echo CHECKMARK_GREEN; ?> <?php echo gettext('Apply'); ?></strong>
							</button>
						</p>
						<ul class="iconlegend">
							<?php
							if (GALLERY_SECURITY == 'public') {
								?>
								<li>
									<?php
									if (true) {
										?>
										<?php echo LOCK; ?>
										<?php echo LOCK_OPEN; ?>
										<?php echo gettext("has/does not have password"); ?>
										<?php
									}
									?>
								<li>
									<?php
								}
								?>
							<li>
								<?php echo CLIPBOARD . ' ' . gettext("pick source"); ?>
							</li>
							<li>
								<?php echo BULLSEYE_BLUE; ?> <?php echo gettext('view'); ?>
							</li>
							<?php
							if (extensionEnabled('hitcounter')) {
								?>
								<li>
									<?php echo RECYCLE_ICON; ?>
									<?php echo gettext('reset hitcounter'); ?>
								</li>
								<?php
							}
							?>
							<li>
								<?php echo WASTEBASKET; ?>
								<?php echo gettext('Delete'); ?>
							</li>
						</ul>
					</form>
					<?php
				} else {
					echo gettext('There are no categories for you to edit.');
				}
				?>
			</div> <!-- tab_articles -->
		</div> <!-- content -->
		<?php printAdminFooter(); ?>
	</div> <!-- main -->
</body>
</html>
