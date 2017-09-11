<?php
/**
 * zenpage admin-pages.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH", 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once("admin-functions.php");

admin_securityChecks(ZENPAGE_PAGES_RIGHTS, currentRelativeURL());

$reports = array();
if (isset($_GET['bulkaction'])) {
	$reports[] = zenpageBulkActionMessage(sanitize($_GET['bulkaction']));
}
if (isset($_GET['deleted'])) {
	$reports[] = "<p class='messagebox fade-message'>" . gettext("Article successfully deleted!") . "</p>";
}
if (isset($_POST['update'])) {
	XSRFdefender('update');
	if ($_POST['checkallaction'] == 'noaction') {
		if (updateItemSortorder('pages')) {
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Sort order saved.") . "</p>";
		} else {
			$reports[] = "<p class='notebox fade-message'>" . gettext("Nothing changed.") . "</p>";
		}
	} else {
		$action = processZenpageBulkActions('Page');
		bulkActionRedirect($action);
	}
}
// remove the page from the database
if (isset($_GET['delete'])) {
	XSRFdefender('delete');
	$msg = deleteZenpageObj(newPage(sanitize($_GET['delete']), 'admin-pages.php'));
	if (!empty($msg)) {
		$reports[] = $msg;
	}
}
// publish or un-publish page by click
if (isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = newPage(sanitize($_GET['titlelink']));
	$obj->setShow(sanitize_numeric($_GET['publish']));
	$obj->save();
}

if (isset($_GET['commentson'])) {
	XSRFdefender('update');
	$obj = newPage(sanitize($_GET['titlelink']));
	$obj->setCommentsAllowed(sanitize_numeric($_GET['commentson']));
	$obj->save();
}
if (isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$obj = newPage(sanitize($_GET['titlelink']));
	$obj->set('hitcounter', 0);
	$obj->save();
	$reports[] = '<p class="messagebox fade-message">' . gettext("Hitcounter reset") . '</p>';
}
/*
 * Here we should restart if any action processing has occurred to be sure that everything is
 * in its proper state. But that would require significant rewrite of the handling and
 * reporting code so is impractical. Instead we will presume that all that needs to be restarted
 * is the CMS object.
 */
$_zp_CMS = new CMS();

printAdminHeader('pages');
printSortableHead();
zenpageJSCSS();
updatePublished('pages');
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteArticle = "<?php echo gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!"); ?>";
	var deletePage = "<?php echo gettext("Are you sure you want to delete this page? THIS CANNOT BE UNDONE!"); ?>";
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}

	// ]]> -->
</script>

</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			zp_apply_filter('admin_note', 'pages', '');
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
			<h1><?php echo gettext('Pages'); ?></h1>

			<div class="tabbox">

				<span class="zenpagestats"><?php printPagesStatistic(); ?></span>
				<form class="dirtylistening" onReset="setClean('form_zenpageitemlist');" action="admin-pages.php" method="post" name="update" id="form_zenpageitemlist" onsubmit="return confirmAction();" autocomplete="off">
					<?php XSRFToken('update'); ?>

					<p><?php echo gettext("Select a page to edit or drag the pages into the order, including subpage levels, you wish them displayed."); ?></p>
					<?php
					if (GALLERY_SECURITY == 'public') {
						?>
						<p class="notebox"><?php echo gettext("<strong>Note:</strong> Subpages of password protected pages inherit the protection."); ?></p>
						<?php
					}
					?>
					<p class="buttons">
						<button class="serialize" type="submit">
							<?php echo CHECKMARK_GREEN; ?>
							<strong><?php echo gettext("Apply"); ?></strong>
						</button>
						<?php
						if (zp_loggedin(MANAGE_ALL_PAGES_RIGHTS)) {
							?>
							<span class="floatright">
								<a href="admin-edit.php?page&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>">
									<?php echo PLUS_ICON; ?>
									<strong>
										<?php echo gettext('New Page'); ?>
									</strong>
								</a>
							</span>
							<?php
						}
						?>
					</p>

					<br class="clearall">
					<div class="headline"><?php echo gettext('Edit this page'); ?>
						<?php
						$checkarray = array(
								gettext('*Bulk actions*') => 'noaction',
								gettext('Delete') => 'deleteall',
								gettext('Set to published') => 'showall',
								gettext('Set to unpublished') => 'hideall',
								gettext('Disable comments') => 'commentsoff',
								gettext('Enable comments') => 'commentson'
						);
						if (extensionEnabled('hitcounter')) {
							$checkarray[gettext('Reset hitcounter')] = 'resethitcounter';
						}
						$checkarray = zp_apply_filter('bulk_page_actions', $checkarray);
						printBulkActions($checkarray);
						?>
					</div>
					<div class="bordered">

						<div class="subhead">
							<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
							</label>
						</div>
						<ul class="page-list">
							<?php $toodeep = printNestedItemsList('pages-sortablelist'); ?>
						</ul>

					</div>

					<?php
					if ($toodeep) {
						echo '<div class="errorbox">';
						echo '<h2>' . gettext('The sort position of the indicated pages cannot be recorded because the nesting is too deep. Please move them to a higher level and save your order.') . '</h2>';
						echo '</div>';
					}
					?>
					<span id="serializeOutput"></span>
					<input name="update" type="hidden" value="Save Order" />
					<p class="buttons">
						<button class="serialize" type="submit" title="<?php echo gettext('Apply'); ?>">
							<?php echo CHECKMARK_GREEN; ?>
							<strong><?php echo gettext('Apply'); ?></strong>
						</button>
					</p>
				</form>
				<?php printZenpageIconLegend(); ?>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>
</body>
</html>
