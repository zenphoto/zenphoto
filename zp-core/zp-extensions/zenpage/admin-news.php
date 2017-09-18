<?php
/**
 * zenpage admin-news.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH", 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once("admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL());

$reports = array();
if (isset($_GET['bulkaction'])) {
	$reports[] = zenpageBulkActionMessage(sanitize($_GET['bulkaction']));
}
if (isset($_GET['deleted'])) {
	$reports[] = "<p class='messagebox fade-message'>" . gettext("Article successfully deleted!") . "</p>";
}

if (isset($_POST['checkallaction'])) { // true if apply is pressed
	XSRFdefender('checkeditems');
	if ($action = processZenpageBulkActions('Article')) {
		bulkActionRedirect($action);
	}
}
if (isset($_GET['delete'])) {
	XSRFdefender('delete');
	$msg = deleteZenpageObj(newArticle(sanitize($_GET['delete']), 'admin-news.php'));
	if (!empty($msg)) {
		$reports[] = $msg;
	}
}

// publish or un-publish page by click
if (isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = newArticle(sanitize($_GET['titlelink']));
	$obj->setShow(sanitize_numeric($_GET['publish']));
	$obj->save();
}

if (isset($_GET['commentson'])) {
	XSRFdefender('update');
	$obj = newArticle(sanitize($_GET['titlelink']));
	$obj->setCommentsAllowed(sanitize_numeric($_GET['commentson']));
	$obj->save();
}
if (isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$obj = newArticle(sanitize($_GET['titlelink']));
	$obj->set('hitcounter', 0);
	$obj->save();
	$reports[] = '<p class="messagebox fade-message">' . gettext("Hitcounter reset") . '</p>';
}

printAdminHeader('news', 'articles');
zenpageJSCSS();
datepickerJS();
updatePublished('news');
?>

<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteArticle = "<?php echo gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!"); ?>";
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}

	function gotoLink(form) {
		var OptionIndex = form.ListBoxURL.selectedIndex;
		parent.location = form.ListBoxURL.options[OptionIndex].value;
	}
	// ]]> -->
</script>

</head>
<body>
	<?php
	$subtab = getCurrentTab();
	if (isset($_GET['author'])) {
		$cur_author = sanitize($_GET['author']);
	} else {
		$cur_author = NULL;
	}
	printLogoAndLinks();
	?>
	<div id="main">
		<?php
		printTabs();
		?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'news', $subtab); ?>
			<h1>
				<?php echo gettext('Articles'); ?>
			</h1>
			<div id = "container">
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

					if (isset($_GET['author'])) {
						echo "<em><small>" . html_encode(sanitize($_GET['author'])) . '</small></em>';
					}
					if (isset($_GET['category'])) {
						echo "<em><small>" . html_encode(sanitize($_GET['category'])) . '</small></em>';
					}
					if (isset($_GET['date'])) {
						$_zp_post_date = sanitize($_GET['date']);
						echo '<em><small> (' . html_encode($_zp_post_date) . ')</small></em>';
						// require so the date dropdown is working
						set_context(ZP_ZENPAGE_NEWS_DATE);
					}
					if (isset($_GET['published'])) {
						switch ($_GET['published']) {
							case 'no':
								$published = 'unpublished';
								break;
							case 'yes':
								$published = 'published';
								break;
							case 'sticky':
								$published = 'sticky';
						}
					} else {
						$published = 'all';
					}
					$sortorder = 'publishdate';
					$direction = true;
					if (isset($_GET['sortorder'])) {
						list($sortorder, $sortdirection) = explode('-', $_GET['sortorder']);
						$direction = $sortdirection && $sortdirection == 'desc';
					}
					if (isset($_GET['category'])) {
						$catobj = newCategory(sanitize($_GET['category']));
					} else {
						$catobj = NULL;
					}
					$resultU = $_zp_CMS->getArticles(0, 'unpublished', false, $sortorder, $direction, false, $catobj);
					$result = $_zp_CMS->getArticles(0, $published, false, $sortorder, $direction, false, $catobj);
					foreach ($result as $key => $article) {
						$article = newArticle($article['titlelink']);
						if (!$article->isMyItem(ZENPAGE_NEWS_RIGHTS) || ($cur_author && $cur_author != $article->getAuthor())) {
							unset($result[$key]);
						}
					}
					foreach ($resultU as $key => $article) {
						$article = newArticle($article['titlelink']);
						if (!$article->isMyItem(ZENPAGE_NEWS_RIGHTS) || ($cur_author && $cur_author != $article->getAuthor())) {
							unset($resultU[$key]);
						}
					}
					$total = 1;
					$articles = count($result);
					$articles_page = max(1, getOption('articles_per_page'));
					if (isset($_GET['articles_page'])) {
						if ($_GET['articles_page'] == 'all') {
							$articles_page = 0;
						} else {
							$articles_page = sanitize_numeric($_GET['articles_page']);
						}
					}
					// Basic setup for the global for the current admin page first
					if (!isset($_GET['subpage'])) {
						$subpage = 0;
					} else {
						$subpage = sanitize_numeric($_GET['subpage']);
					}
					if ($articles_page) {
						$total = ceil($articles / $articles_page);
						//Needed check if we really have articles for page x or not otherwise we are just on page 1
						if ($total <= $subpage) {
							$subpage = 0;
						}
						$offset = CMS::getOffset($articles_page);
						$list = array();
						foreach ($result as $article) {
							$list[] = $article[$sortorder];
						}
						if ($sortorder == 'title') {
							$rangeset = getPageSelector($list, $articles_page);
						} else {
							$rangeset = getPageSelector($list, $articles_page, 'dateDiff');
						}
						$options = array_merge(array('page' => 'news', 'tab' => 'articles'), getNewsAdminOption(NULL));
						$result = array_slice($result, $offset, $articles_page);
					} else {
						$rangeset = $options = array();
					}
					?>
					<span class="zenpagestats"><?php printNewsStatistic($articles, count($resultU)); ?></span>
					<br class="clearall">
					<div class="floatright">
						<?php
						printAuthorDropdown();
						printCategoryDropdown();
						printNewsDatesDropdown();
						printUnpublishedDropdown();
						printSortOrderDropdown();
						printArticlesPerPageDropdown($subpage);
						?>
					</div>
					<br class="clearall">
					<?php
					$option = getNewsAdminOptionPath(getNewsAdminOption(NULL));
					?>
					<form class="dirtylistening" onReset="setClean('form_zenpageitemlist');" action="admin-news.php<?php echo $option; ?>" method="post" name="checkeditems" id="form_zenpageitemlist" onsubmit="return confirmAction();" autocomplete="off">
						<?php XSRFToken('checkeditems'); ?>
						<div class="buttons">
							<button type="submit" title="<?php echo gettext('Apply'); ?>"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext('Apply'); ?></strong>
							</button>
						</div>
						<span class="buttons floatright">
							<a href="admin-edit.php?newsarticle&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>">
								<?php echo PLUS_ICON; ?>
								<strong><?php echo gettext("New Article"); ?></strong>
							</a>
						</span>
						<br class="clearall">

						<div class="centered">
							<?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/zenpage/admin-news.php', $options); ?>
						</div>

						<div class="headline">
							<?php echo gettext('Edit this article'); ?>

							<span class="floatright padded">
								<?php
								$checkarray = array(
										gettext('*Bulk actions*') => 'noaction',
										gettext('Delete') => 'deleteall',
										gettext('Set to published') => 'showall',
										gettext('Set to unpublished') => 'hideall',
										gettext('Disable comments') => 'commentsoff',
										gettext('Enable comments') => 'commentson',
										gettext('Add categories') => array('name' => 'addcats', 'action' => 'mass_cats_data'),
										gettext('Clear categories') => 'clearcats'
								);
								if (extensionEnabled('hitcounter')) {
									$checkarray[gettext('Reset hitcounter')] = 'resethitcounter';
								}
								$checkarray = zp_apply_filter('bulk_article_actions', $checkarray);
								printBulkActions($checkarray);
								?>
							</span>
						</div>
						<table class="bordered">

							<tr>
								<td><!--title--></td>
								<td><?php echo gettext('Categories'); ?></td>
								<td><?php echo gettext('Author'); ?></td>
								<td>
									<?php
									if ($sortorder == 'date') {
										echo gettext('Created');
									} else {
										echo gettext('Last changed');
									}
									?>
								</td>
								<td><?php echo gettext('Published'); ?></td>
								<td><?php echo gettext('Expires'); ?></td>
								<td class="subhead" colspan="100%">
									<label class="floatright"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
									</label>
								</td>
							</tr>
							<?php
							foreach ($result as $article) {
								$article = newArticle($article['titlelink']);
								?>
								<tr>
									<td>
										<?php
										switch ($article->getSticky()) {
											case 1:
												$sticky = ' <small>[' . gettext('sticky') . ']</small>';
												break;
											case 9:
												$sticky = ' <small><strong>[' . gettext('sticky') . ']</strong></small>';
												break;
											default:
												$sticky = '';
												break;
										}


										echo '<a href="admin-edit.php' . getNewsAdminOptionPath(array_merge(array('newsarticle' => NULL, 'titlelink' => urlencode($article->getTitlelink())), getNewsAdminOption(NULL))) . '">';
										checkForEmptyTitle($article->getTitle(), "news");
										echo '</a>' . checkHitcounterDisplay($article->getHitcounter()) . $sticky;
										?>

									</td>
									<td>
										<?php printNewsCategories($article) ?><br />
									</td>
									<td>
										<?php echo html_encode($article->getAuthor()); ?>
									</td>
									<td>
										<?php
										if ($sortorder == 'date') {
											echo $article->getDateTime();
										} else {
											echo $article->getLastchange();
										}
										?>
									</td>
									<td>
										<?php printPublished($article); ?>
									</td>
									<td>
										<?php printExpired($article); ?>
									</td>

									<td>

										<div class="page-list_icon">
											<?php
											if ($article->inProtectedCategory()) {
												echo LOCK;
											} else {
												echo LOCK_OPEN;
											}
											?>
										</div>
										<div class="page-list_icon">
											<?php echo linkPickerIcon($article); ?>
										</div >
										<?php
										$option = getNewsAdminOptionPath(getNewsAdminOption(NULL));
										if (empty($option)) {
											$divider = '?';
										} else {
											$divider = '&amp;';
										}
										if (checkIfLocked($article)) {
											?>
											<div class="page-list_icon">
												<?php printPublishIconLink($article, $option); ?>
											</div>
											<div class="page-list_icon">
												<?php
												if ($article->getCommentsAllowed()) {
													?>
													<a href="<?php echo $option . $divider; ?>commentson=0&amp;titlelink=<?php
													echo html_encode($article->getTitlelink());
													?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Disable comments'); ?>">
															 <?php echo BULLSEYE_GREEN; ?>
													</a>
													<?php
												} else {
													?>
													<a href="<?php echo $option . $divider; ?>commentson=1&amp;titlelink=<?php
													echo html_encode($article->getTitlelink());
													?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Enable comments'); ?>">
															 <?php echo BULLSEYE_RED; ?>
													</a>
													<?php
												}
												?>
											</div>
											<?php
										} else {
											?>
											<div class="page-list_icon">
												<?php echo BULLSEYE_LIGHTGRAY; ?>
											</div>
											<div class="page-list_icon">
												<?php echo BULLSEYE_LIGHTGRAY; ?>
											</div>
										<?php } ?>

										<div class="page-list_icon">
											<a target="_blank" href="../../../index.php?p=news&amp;title=<?php
											echo $article->getTitlelink();
											?>" title="<?php echo gettext('View article'); ?>">
													 <?php echo BULLSEYE_BLUE; ?>
											</a>
										</div>

										<?php
										if ($unlocked = checkIfLocked($article)) {
											if (extensionEnabled('hitcounter')) {
												?>
												<div class="page-list_icon">
													<a href="<?php echo $option . $divider; ?>hitcounter=1&amp;titlelink=<?php
													echo html_encode($article->getTitlelink());
													?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo gettext('Reset hitcounter'); ?>">
															 <?php echo RECYCLE_ICON; ?>
													</a>
												</div>
												<?php
											}
											?>
											<div class="page-list_icon">
												<a href="javascript:confirmDelete('admin-news.php<?php echo $option . $divider; ?>delete=<?php echo $article->getTitlelink(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>','<?php echo js_encode(gettext('Are you sure you want to delete this article? THIS CANNOT BE UNDONE!')); ?>')" title="<?php echo gettext('Delete article'); ?>">
													<?php echo WASTEBASKET; ?>
												</a>
											</div>
											<?php
										} else {
											?>
											<div class="page-list_icon">
												<?php echo BULLSEYE_LIGHTGRAY; ?>
											</div>
											<div class="page-list_icon">
												<?php echo BULLSEYE_LIGHTGRAY; ?>
											</div>

											<?php
										}
										?>

									</td>
									<td>
										<div class="floatright">
											<input type="checkbox" name="ids[]" value="<?php echo $article->getTitlelink(); ?>"<?php if (!$unlocked) echo ' disabled="disabled"'; ?>/>
										</div>
									</td>
								</tr>
								<?php
							}
							?>

						</table>
						<p class="centered">
							<?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/zenpage/admin-news.php', $options); ?>
						</p>
						<p class="buttons">
							<button type="submit" title="<?php echo gettext('Apply'); ?>">
								<?php echo CHECKMARK_GREEN; ?>
								<strong><?php echo gettext('Apply'); ?></strong>
							</button>
						</p>
					</form>
					<?php printZenpageIconLegend(); ?>
				</div> <!-- tab_articles -->
			</div> <!-- content -->
		</div> <!-- container -->
	</div> <!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>
