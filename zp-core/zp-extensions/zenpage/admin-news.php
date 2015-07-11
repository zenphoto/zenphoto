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
			<?php $subtab = printSubtabs(); ?>
			<div id="tab_articles" class="tabbox">
				<?php
				zp_apply_filter('admin_note', 'news', $subtab);
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
				<h1><?php echo gettext('Articles'); ?>
					<?php
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
						$options = array_merge(array('page' => 'news', 'tab' => 'articles'), getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1)));
						$result = array_slice($result, $offset, $articles_page);
					} else {
						$rangeset = $options = array();
					}
					?>
					<span class="zenpagestats"><?php printNewsStatistic($articles, count($resultU)); ?></span></h1>
				<div class="floatright">
					<?php
					printAuthorDropdown();
					printCategoryDropdown();
					printNewsDatesDropdown();
					printUnpublishedDropdown();
					printSortOrderDropdown();
					printArticlesPerPageDropdown($subpage);
					?>
					<span class="buttons">
						<a href="admin-edit.php?newsarticle&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>"> <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/add.png" alt="" /> <strong><?php echo gettext("New Article"); ?></strong></a>
					</span>
					<br style="clear: both" />
				</div>
				<?php
				$option = getNewsAdminOptionPath(getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1, 'subpage' => 1), '?'));
				?>
				<form class="dirtylistening" onReset="setClean('form_zenpageitemlist');" action="admin-news.php<?php echo $option; ?>" method="post" name="checkeditems" id="form_zenpageitemlist" onsubmit="return confirmAction();">
					<?php XSRFToken('checkeditems'); ?>
					<div class="buttons">
						<button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
						</button>
					</div>
					<br style="clear: both" /><br />

					<table class="bordered">
						<tr>
							<th colspan="14" id="imagenav">
								<?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/zenpage/admin-news.php', $options); ?>
							</th>
						</tr>
						<tr>
							<th colspan="7"><?php echo gettext('Edit this article'); ?>

							</th>


							<th colspan="7">
								<?php
								$checkarray = array(
												gettext('*Bulk actions*')			 => 'noaction',
												gettext('Delete')							 => 'deleteall',
												gettext('Set to published')		 => 'showall',
												gettext('Set to unpublished')	 => 'hideall',
												gettext('Add tags')						 => 'addtags',
												gettext('Clear tags')					 => 'cleartags',
												gettext('Disable comments')		 => 'commentsoff',
												gettext('Enable comments')		 => 'commentson',
												gettext('Add categories')			 => 'addcats',
												gettext('Clear categories')		 => 'clearcats'
								);
								if (extensionEnabled('hitcounter')) {
									$checkarray['hitcounter'] = 'resethitcounter';
								}
								printBulkActions($checkarray);
								?>
							</th>
						</tr>
						<tr class="newstr">
							<th><!--title--></th>
							<th><?php echo gettext('Categories'); ?></th>
							<th><?php echo gettext('Author'); ?></th>
							<th><?php
								if ($sortorder == 'date') {
									echo gettext('Created');
								} else {
									echo gettext('Last changed');
								}
								?></th>
							<th><?php echo gettext('Published'); ?></th>
							<th><?php echo gettext('Expires'); ?></th>
							<th class="subhead" colspan="8">
								<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
								</label>
							</th>
						</tr>
						<?php
						foreach ($result as $article) {
							$article = newArticle($article['titlelink']);
							?>
							<tr class="newstr">
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


									echo '<a href="admin-edit.php' . getNewsAdminOptionPath(array_merge(array('newsarticle' => NULL, 'titlelink' => urlencode($article->getTitlelink())), getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1, 'subpage' => 1)))) . '">';
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
								<td class="page-list_icon">
									<?php
									if ($article->inProtectedCategory()) {
										echo '<img src="../../images/lock.png" style="border: 0px;" alt="' . gettext('Password protected') . '" title="' . gettext('Password protected') . '" />';
									}
									?>
								</td>
								<td><?php echo linkPickerIcon($article); ?></td>
								<?php
								$option = getNewsAdminOptionPath(getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1, 'subpage' => 1)));
								if (empty($option)) {
									$divider = '?';
								} else {
									$divider = '&amp;';
								}
								if (checkIfLocked($article)) {
									?>
									<td class="page-list_icon">
										<?php printPublishIconLink($article, 'news'); ?>
									</td>
									<td class="page-list_icon">
										<?php
										if ($article->getCommentsAllowed()) {
											?>
											<a href="<?php echo $option . $divider; ?>commentson=0&amp;titlelink=<?php
											echo html_encode($article->getTitlelink());
											?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Disable comments'); ?>">
												<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
											</a>
											<?php
										} else {
											?>
											<a href="<?php echo $option . $divider; ?>commentson=1&amp;titlelink=<?php
											echo html_encode($article->getTitlelink());
											?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Enable comments'); ?>">
												<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
											</a>
											<?php
										}
										?>
									</td>
									<?php
								} else {
									?>
									<td class="page-list_icon">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
									</td>
									<td class="page-list_icon">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
									</td>
								<?php } ?>

								<td class="page-list_icon">
									<a target="_blank" href="../../../index.php?p=news&amp;title=<?php
									echo $article->getTitlelink();
									?>" title="<?php echo gettext('View article'); ?>">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/view.png" alt="" title="<?php echo gettext('View article'); ?>" />
									</a>
								</td>

								<?php
								if (checkIfLocked($article)) {
									if (extensionEnabled('hitcounter')) {
										?>
										<td class="page-list_icon">
											<a href="<?php echo $option . $divider; ?>hitcounter=1&amp;titlelink=<?php
											echo html_encode($article->getTitlelink());
											?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo gettext('Reset hitcounter'); ?>">
												<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" title="<?php echo gettext('Reset hitcounter'); ?>" /></a>
										</td>
										<?php
									}
									?>
									<td class="page-list_icon">
										<a href="javascript:confirmDelete('admin-news.php<?php echo $option . $divider; ?>delete=<?php echo $article->getTitlelink(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>','<?php echo js_encode(gettext('Are you sure you want to delete this article? THIS CANNOT BE UNDONE!')); ?>')" title="<?php echo gettext('Delete article'); ?>">
											<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="" title="<?php echo gettext('Delete article'); ?>" /></a>
									</td>
									<td class="page-list_icon">
										<input type="checkbox" name="ids[]" value="<?php echo $article->getTitlelink(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
									</td>
									<?php
								} else {
									?>
									<td class="page-list_icon">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
									</td>
									<td class="page-list_icon">
										<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
									</td>
									<td class="page-list_icon">
										<input type="checkbox" name="disabled" value="none" disabled="Disabled" />
									</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
						<tr>
							<td id="imagenavb" colspan="11"><?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/zenpage/admin-news.php', $options); ?>	</td>
						</tr>
					</table>


					<p class="buttons"><button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong></button></p>
				</form>
				<?php printZenpageIconLegend(); ?>
				<br class="clearall" />
			</div> <!-- tab_articles -->
		</div> <!-- content -->
	</div> <!-- main -->

	<?php printAdminFooter(); ?>
</body>
</html>
