<?php
/**
 * zenpage admin-news-articles.php
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
if (isset($_GET['bulkaction'])) {
	$reports[] = zenpageBulkActionMessage(sanitize($_GET['bulkaction']));
}
if (isset($_GET['deleted'])) {
	$reports[] = "<p class='messagebox fade-message'>".gettext("Article successfully deleted!")."</p>";
}
if(isset($_POST['processcheckeditems'])) {
	XSRFdefender('checkeditems');
	$action = processZenpageBulkActions('News');
	header('Location: ' . FULLWEBPATH .'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/admin-news-articles.php?bulkaction='.$action);
	exit();
	}
if(isset($_GET['delete'])) {
	XSRFdefender('delete');
	$msg = deleteArticle($_GET['delete']);
	if (!empty($msg)) {
		$reports[] = $msg;
	}
}
// publish or un-publish page by click
if(isset($_GET['publish'])) {
	XSRFdefender('update');
	$obj = new ZenpageNews(sanitize($_GET['titlelink']));
	zenpagePublish($obj, sanitize_numeric($_GET['publish']));
}
if(isset($_GET['skipscheduling'])) {
	XSRFdefender('update');
	$obj = new ZenpageNews(sanitize($_GET['titlelink']));
	skipScheduledPublishing($obj);
}
if(isset($_GET['commentson'])) {
	XSRFdefender('update');
	$obj = new ZenpageNews(sanitize($_GET['titlelink']));
	$obj->setCommentsAllowed(sanitize_numeric($_GET['commentson']));
	$obj->save();
}
if(isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	$obj = new ZenpageNews(sanitize($_GET['titlelink']));
	$obj->set('hitcounter',0);
	$obj->save();
	$reports[] = '<p class="messagebox fade-message">'.gettext("Hitcounter reset").'</p>';
}

printAdminHeader('news','articles');
zenpageJSCSS();
datepickerJS();
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
		<?php	$subtab = printSubtabs();	?>
		<div id="tab_articles" class="tabbox">
			<?php
			zp_apply_filter('admin_note','news', $subtab);
			foreach ($reports as $report) {
				echo $report;
			}
			?>
			<h1><?php echo gettext('Articles'); ?>
			<?php
			if (isset($_GET['category'])) {
				echo "<em>".sanitize($_GET['category']).'</em>';
			}
			if (isset($_GET['date'])) {
				echo '<em><small> ('.$_GET['date'].')</small></em>';
				// require for getArticles() so the date dropdown is working
				set_context(ZP_ZENPAGE_NEWS_DATE);
				$_zp_post_date = sanitize($_GET['date']);
			}
			if(isset($_GET['published'])) {
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
			$sortorder = 'date';
			$direction = 'desc';
			if(isset($_GET['sortorder'])) {
				switch ($_GET['sortorder']) {
					case 'date-desc':
						$sortorder = 'date';
						$direction = 'desc';
						break;
					case 'date-asc':
						$sortorder = 'date';
						$direction = 'asc';
						break;
					case 'title-desc':
						$sortorder = 'title';
						$direction = 'desc';
						break;
					case 'title-asc':
						$sortorder = 'title';
						$direction = 'asc';
						break;
				}
			}
			if(isset($_GET['category'])) {
				$catobj = new ZenpageCategory(sanitize($_GET['category']));
				$resultU = $catobj->getArticles(0,'unpublished',false,$sortorder,$direction);
				$result = $catobj->getArticles(0,$published,false,$sortorder,$direction);
			} else {
				$catobj = NULL;
				$resultU = $_zp_zenpage->getArticles(0,'unpublished',false,$sortorder,$direction);
				$result = $_zp_zenpage->getArticles(0,$published,false,$sortorder,$direction);
			}
			foreach ($result as $key=>$article) {
				$article = new ZenpageNews($article['titlelink']);
				if (!$article->isMyItem(ZENPAGE_NEWS_RIGHTS)) {
					unset($result[$key]);
				}
			}
			foreach ($resultU as $key=>$article) {
				$article = new ZenpageNews($article['titlelink']);
				if (!$article->isMyItem(ZENPAGE_NEWS_RIGHTS)) {
					unset($resultU[$key]);
				}
			}
			$total = 1;
			$articles = count($result);
			$articles_page = 15;
			if(isset($_GET['articles_page'])) {
				if($_GET['articles_page'] == 'all') {
					$articles_page = 0;
				} else {
					$articles_page = sanitize_numeric($_GET['articles_page']);
				}
			}
			// Basic setup for the global for the current admin page first
			if(!isset($_GET['pagenr'])) {
				$_zp_zenpage_currentadminnewspage = 1;
			} else {
				$_zp_zenpage_currentadminnewspage = sanitize_numeric($_GET['pagenr']);
			}
			if($articles_page) {
				$total = ceil($articles / $articles_page);
				//Needed check if we really have articles for page x or not otherwise we are just on page 1
				if($total < $_zp_zenpage_currentadminnewspage) {
					$_zp_zenpage_currentadminnewspage = 1;
				}
				$offset = $_zp_zenpage->getOffset($articles_page);
				$result = array_slice($result, $offset, $articles_page);
			}
			?>
			<span class="zenpagestats"><?php printNewsStatistic($articles, count($resultU));?></span></h1>
				<div class="floatright">
					<?php printCategoryDropdown(); printArticleDatesDropdown(); printUnpublishedDropdown(); printSortOrderDropdown(); printArticlesPerPageDropdown(); ?>
						<?php //echo "optionpath: ".getNewsAdminOptionPath(true,true,true); // debugging only; ?>
						<span class="buttons">
						<a href="admin-edit.php?newsarticle&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add')?>" title="<?php echo gettext('New Article'); ?>"><img src="images/add.png" alt="" /> <strong><?php echo gettext("New Article"); ?></strong></a>
						</span>
						<br style="clear: both" /><br />
				</div>
				<form action="admin-news-articles.php?pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true);?>" method="post" name="checkeditems" onsubmit="return confirmAction();">
					<?php XSRFToken('checkeditems'); ?>
				<input name="processcheckeditems" type="hidden" value="apply" />
				<div class="buttons">
					<button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong>
					</button>
				</div>
				<br style="clear: both" /><br />

				<table class="bordered">
					<tr>
						<th colspan="11"><?php echo gettext('Edit this article'); ?>
						<?php
						$checkarray = array(
														gettext('*Bulk actions*') => 'noaction',
														gettext('Delete') => 'deleteall',
														gettext('Set to published') => 'showall',
														gettext('Set to unpublished') => 'hideall',
														gettext('Add tags') => 'addtags',
														gettext('Clear tags') => 'cleartags',
														gettext('Disable comments') => 'commentsoff',
														gettext('Enable comments') => 'commentson',
														gettext('Add categories') => 'addcats',
														gettext('Clear categories') => 'clearcats'
														);
						if (getOption('zp_plugin_hitcounter')) {
							$checkarray['hitcounter'] = 'resethitcounter';
						}
						printBulkActions($checkarray);
						?>
						</th>
						</tr>
						<tr>
						 <td id="imagenav" colspan="11"><?php printArticlesPageNav($total); ?></td>
						</tr>
						<tr class="newstr">
							<td class="subhead" colspan="11">
										<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
								</label>
							</td>
						</tr>
					<?php
					foreach ($result as $article) {
						$article = new ZenpageNews($article['titlelink']);
						?>
						<tr class="newstr">
							<td>
							 <?php
							 if(checkIfLockedNews($article)) {
								 echo '<a href="admin-edit.php?newsarticle&amp;titlelink='.urlencode($article->getTitlelink()).'&amp;pagenr='.$_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true).'">'; checkForEmptyTitle($article->getTitle(),"news"); echo '</a>'.checkHitcounterDisplay($article->getHitcounter());
							 } else {
								 echo checkForEmptyTitle($article->getTitle(),"news").'</a>'.checkHitcounterDisplay($article->getHitcounter());
							 }
							 ?>

							</td>
							<td>
							<?php
							checkIfScheduled($article);
							checkIfExpires($article);
							?>
							</td>
							<td>
							<?php printArticleCategories($article) ?><br />
							</td>
							<td>
							<?php echo html_encode($article->getAuthor()); ?>
							</td>
							<td class="icons">
							<?php
								if($article->inProtectedCategory()) {
									echo '<img src="../../images/lock.png" style="border: 0px;" alt="'.gettext('Password protected').'" title="'.gettext('Password protected').'" />';
								}
								?>
							</td>

						<?php
						if(checkIfLockedNews($article)) {
							?>
							<td class="icons">
							<?php
								printPublishIconLink($article,'news'); ?>
							</td>
							<td class="icons">
								<?php
								if ($article->getCommentsAllowed()) {
									?>
									<a href="?commentson=0&amp;titlelink=<?php echo html_encode($article->getTitlelink()); ?>&amp;pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true); ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo gettext('Disable comments'); ?>">
										<img src="../../images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
									</a>
									<?php
								} else {
									?>
									<a href="?commentson=1&amp;titlelink=<?php echo html_encode($article->getTitlelink()); ?>&amp;pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true); ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo gettext('Enable comments'); ?>">
										<img src="../../images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
									</a>
									<?php
								}
								?>
							</td>
							<?php
						} else {
							?>
							<td class="icons">
									<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
							</td>
							<td class="icons">
									<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
							</td>
							<?php } ?>

							<td class="icons">
								<a href="../../../index.php?p=news&amp;title=<?php echo $article->getTitlelink();?>&amp;pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true); ?>" title="<?php echo gettext('View article'); ?>">
								<img src="images/view.png" alt="" title="<?php echo gettext('View article'); ?>" />
								</a>
							</td>

							<?php
						if(checkIfLockedNews($article)) {
							if (getOption('zp_plugin_hitcounter')) {
								?>
								<td class="icons">
									<a href="?hitcounter=1&amp;titlelink=<?php echo html_encode($article->getTitlelink());?>&amp;pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter')?>" title="<?php echo gettext('Reset hitcounter'); ?>">
									<img src="../../images/reset.png" alt="" title="<?php echo gettext('Reset hitcounter'); ?>" /></a>
								</td>
								<?php
							}
							?>
							<td class="icons">
								<a href="javascript:confirmDelete('admin-news-articles.php?delete=<?php echo $article->getTitlelink(); ?>&amp;pagenr=<?php echo $_zp_zenpage_currentadminnewspage.getNewsAdminOptionPath(true,true,true,true,true); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete')?>','<?php echo js_encode(gettext('Are you sure you want to delete this article? THIS CANNOT BE UNDONE!')); ?>')" title="<?php echo gettext('Delete article'); ?>">
								<img src="../../images/fail.png" alt="" title="<?php echo gettext('Delete article'); ?>" /></a>
							</td>
							<td class="icons">
								<input type="checkbox" name="ids[]" value="<?php echo $article->getTitlelink(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
							</td>
							</tr>
							<?php } else { ?>
							<td class="icons">
								<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
							</td>
							<td class="icons">
								<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
							</td>
							<td class="icons">
								<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
							</td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					?>
					<tr>
					<td id="imagenavb" colspan="11"><?php printArticlesPageNav($total); ?>	</td>
					</tr>
				</table>


				<p class="buttons"><button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong></button></p>
				</form>
				<?php printZenpageIconLegend(); ?>
				<br clear="all" />
		</div> <!-- tab_articles -->
	</div> <!-- content -->
</div> <!-- main -->

<?php printAdminFooter(); ?>
</body>
</html>
