<?php
/**
 * zenpage admin-news-articles.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH",4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once("zenpage-admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL(__FILE__));

$reports = array();
if (isset($_GET['deleted'])) {
	$reports[] = "<p class='messagebox fade-message'>".gettext("Article successfully deleted!")."</p>";
}
if(isset($_POST['processcheckeditems'])) {
	XSRFdefender('checkeditems');
	processZenpageBulkActions('news',$reports);
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
	publishPageOrArticle('news',$_GET['id']);
}
if(isset($_GET['skipscheduling'])) {
	XSRFdefender('update');
	skipScheduledPublishing('news',$_GET['id']);
}
if(isset($_GET['commentson'])) {
	XSRFdefender('update');
	enableComments('news');
}
if(isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	resetPageOrArticleHitcounter('news');
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
		<?php	printSubtabs();	?>
		<div id="tab_articles" class="tabbox">
			<?php
			foreach ($reports as $report) {
				echo $report;
			}
			?>
			<h1><?php echo gettext('Articles'); ?>
			<?php
			if (isset($_GET['category'])) {
				echo "<em>".$_GET['category'].'</em>';
			}
			if (isset($_GET['date'])) {
				echo '<em><small> ('.$_GET['date'].')</small></em>';
				// require for getNewsArticles() so the date dropdown is working
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

			if(isset($_GET['category'])) {
				$result = getNewsArticles(getOption('zenpage_admin_articles'),$_GET['category'],$published,false);
			} else {
				$result = getNewsArticles(getOption('zenpage_admin_articles'),'',$published,false);
			}
			?>
			<span class="zenpagestats"><?php printNewsStatistic();?></span></h1>
			<div style="float:right">
			<?php printCategoryDropdown(); printArticleDatesDropdown(); printUnpublishedDropdown(); ?>
					<?php //echo "optionpath: ".getNewsAdminOptionPath(true,true,true); // debugging only; ?>
					<br style="clear: both" /><br />
					</div>
				<form action="admin-news-articles.php" method="post" name="checkeditems" onsubmit="return confirmAction();">
					<?php XSRFToken('checkeditems'); ?>
				<input name="processcheckeditems" type="hidden" value="apply" />
				<div class="buttons">
					<button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong></button>
					<a href="admin-edit.php?newsarticle&amp;add&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add')?>" title="<?php echo gettext('Add Article'); ?>"><img src="images/add.png" alt="" /> <strong><?php echo gettext("Add Article"); ?></strong></a>
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
						gettext('Disable comments') => 'commentsoff',
						gettext('Enable comments') => 'commentson',
						gettext('Reset hitcounter') => 'resethitcounter',
						);
						?> <span style="float: right">
								<select name="checkallaction" id="checkallaction" size="1">
									<?php generateListFromArray(array('noaction'), $checkarray,false,true); ?>
								</select>
						</span>

						</th>
						</tr>
						<tr>
						 <td id="imagenav" colspan="11"><?php printArticlesPageNav(); ?></td>
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
						if ($article->isMyItem(ZENPAGE_NEWS_RIGHTS)) {
							?>
							<tr class="newstr">
								<td>
								 <?php
								 if(checkIfLockedNews($article)) {
									 echo '<a href="admin-edit.php?newsarticle&amp;titlelink='.urlencode($article->getTitlelink()).'&amp;pagenr='.getCurrentAdminNewsPage().'">'; checkForEmptyTitle($article->getTitle(),"news"); echo '</a>'.checkHitcounterDisplay($article->getHitcounter());
								 } else {
									 echo $article->getTitle().'</a>'.checkHitcounterDisplay($article->getHitcounter());
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
									if($article->inProtectedCategory() &&	(getOption('gallery_security') != 'private')) {
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
										<a href="?commentson=1&amp;id=<?php echo $article->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo gettext('Disable comments'); ?>">
											<img src="../../images/comments-on.png" alt="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
										</a>
										<?php
									} else {
										?>
										<a href="?commentson=0&amp;id=<?php echo $article->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo gettext('Enable comments'); ?>">
											<img src="../../images/comments-off.png" alt="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
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
									<a href="../../../index.php?p=news&amp;title=<?php echo $article->getTitlelink();?>" title="<?php echo gettext('View article'); ?>">
									<img src="images/view.png" alt="<?php echo gettext('View article'); ?>" />
									</a>
								</td>

								<?php
								if(checkIfLockedNews($article)) {
									?>
									<td class="icons">
									<a href="?hitcounter=1&amp;id=<?php echo $article->getID();?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter')?>" title="<?php echo gettext('Reset hitcounter'); ?>">
									<img src="../../images/reset.png" alt="<?php echo gettext('Reset hitcounter'); ?>" /></a>
								</td>
								<td class="icons">
									<a href="javascript:confirmDelete('admin-news-articles.php?delete=<?php echo $article->getTitlelink(); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete')?>','<?php echo js_encode(gettext('Are you sure you want to delete this article? THIS CANNOT BE UNDONE!')); ?>')" title="<?php echo gettext('Delete article'); ?>">
									<img src="../../images/fail.png" alt="<?php echo gettext('Delete article'); ?>" /></a>
								</td>
								<td class="icons">
									<input type="checkbox" name="ids[]" value="<?php echo $article->getID(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
								</td>
								</tr>
								<?php } else { ?>
								<td class="icons">
									<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
								</td>
								<td class="icons">
									<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
								</td>
								<td class="icons">
									<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
								</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
					}
					?>
					<tr>
					<td id="imagenavb" colspan="11"><?php printArticlesPageNav(); ?>	</td>
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
