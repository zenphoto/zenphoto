<?php
/**
 * zenpage admin-edit.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH", 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once("zenpage-admin-functions.php");
if (is_AdminEditPage('page')) {
	$rights = ZENPAGE_PAGES_RIGHTS;
} else {
	$rights = ZENPAGE_NEWS_RIGHTS;
}
admin_securityChecks($rights, currentRelativeURL());

$saveitem = '';
$reports = array();
if (is_AdminEditPage('page')) {
	$tab = 'pages';
	if (isset($_GET['titlelink'])) {
		$result = new ZenpagePage(urldecode(sanitize($_GET['titlelink'])));
	} else if (isset($_GET['update'])) {
		XSRFdefender('update');
		$result = updatePage($reports);
		if (getCheckboxState('copy_delete_object')) {
			switch (sanitize($_POST['copy_delete_object'])) {
				case 'copy':
					$as = trim(sanitize(sanitize($_POST['copy_object_as'])));
					if (empty($as)) {
						$as = sprintf(gettext('copy of %s'), $result->getTitle());
					}
					$as = seoFriendly($as);
					$result->copy($as);
					$result = new ZenpagePage($as);
					$_GET['titlelink'] = $as;
					break;
				case 'delete':
					$reports[] = deletePage($result);
					break;
			}
		}
	} else {
		$result = new ZenpagePage('');
		$result->setPermalink(1);
		$result->setDateTime(date('Y-m-d H:i:s'));
	}
	if (isset($_GET['save'])) {
		XSRFdefender('save');
		$result = updatePage($reports, true);
	}
	if (isset($_GET['delete'])) {
		XSRFdefender('delete');
		$msg = deletePage(sanitize($_GET['delete']));
		if (!empty($msg)) {
			$reports[] = $msg;
		}
	}
}

if (is_AdminEditPage('newsarticle')) {
	$tab = 'news';
	if (isset($_GET['titlelink'])) {
		$result = new ZenpageNews(urldecode(sanitize($_GET['titlelink'])));
	} else if (isset($_GET['update'])) {
		XSRFdefender('update');
		$result = updateArticle($reports);
		if (getCheckboxState('copy_delete_object')) {
			switch (sanitize($_POST['copy_delete_object'])) {
				case 'copy':
					$as = trim(sanitize(sanitize($_POST['copy_object_as'])));
					if (empty($as)) {
						$as = sprintf(gettext('copy of %s'), $result->getTitle());
					}
					$as = seoFriendly($as);
					$result->copy($as);
					$result = new ZenpageNews($as);
					$_GET['titlelink'] = $as;
					break;
				case 'delete':
					$reports[] = deleteArticle($result);
					break;
			}
		}
	} else {
		$result = new ZenpageNews('');
		$result->setPermalink(1);
		$result->setDateTime(date('Y-m-d H:i:s'));
	}
	if (isset($_GET['save'])) {
		XSRFdefender('save');
		$result = updateArticle($reports, true);
	}
	if (isset($_GET['delete'])) {
		XSRFdefender('delete');
		$msg = deleteArticle(sanitize($_GET['delete']));
		if (!empty($msg)) {
			$reports[] = $msg;
		}
	}
}
if (is_AdminEditPage('newscategory')) {
	$tab = 'news';
	$_GET['tab'] = 'categories';
	if (isset($_GET['save'])) {
		XSRFdefender('save');
		updateCategory($reports, true);
	}
	if (isset($_GET['titlelink'])) {
		$result = new ZenpageCategory(urldecode(sanitize($_GET['titlelink'])));
	} else if (isset($_GET['update'])) {
		XSRFdefender('update');
		$result = updateCategory($reports);
	} else {
		$result = new ZenpageCategory('');
		$result->setShow(1);
	}
}

printAdminHeader($tab, ($result->transient) ? gettext('add') : gettext('edit'));
zp_apply_filter('texteditor_config', '', 'zenpage');
zenpageJSCSS();
datepickerJS();
codeblocktabsJS();
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteArticle = "<?php echo gettext("Are you sure you want to delete this article? THIS CANNOT BE UNDONE!"); ?>";
	var deletePage = "<?php echo gettext("Are you sure you want to delete this page? THIS CANNOT BE UNDONE!"); ?>";
	var deleteCategory = "<?php echo gettext("Are you sure you want to delete this category? THIS CANNOT BE UNDONE!"); ?>";
<?php
if (!isset($_GET['add'])) { // prevent showing the message when adding page or article
	?>
		function checkFutureExpiry() {
			var expiry = $('#expiredate').datepicker('getDate');
			var today = new Date();
			if (expiry.getTime() > today.getTime()) {
				$(".expire").html('');
			} else {
				$(".expire").html('<?php echo addslashes(gettext('This is not a future date!')); ?>');
			}
		}
		function checkFuturePub() {
			var today = new Date();
			var pub = $('#date').datepicker('getDate');
			if (pub.getTime() > today.getTime()) {
				$(".scheduledpublishing").html('<?php echo addslashes(gettext('Future publishing date:')); ?>');
			} else {
				$(".scheduledpublishing").html('');
			}
		}
		function toggleTitlelink() {
			if (jQuery('#edittitlelink:checked').val() == 1) {
				$('#titlelink').removeAttr("disabled");
			} else {
				$('#titlelink').attr("disabled", true);
			}
		}
	<?php
}
?>
	// ]]> -->
</script>
<?php Zenphoto_Authority::printPasswordFormJS(); ?>
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
			if (empty($_GET['subpage'])) {
				$page = "";
			} else {
				$page = '&amp;subpage=' . sanitize_numeric($_GET['subpage']);
			}
			$saveitem = $updateitem = gettext('Apply');
			if (is_AdminEditPage('newsarticle')) {
				if (!empty($page)) {
					$zenphoto_tabs['news']['subtabs'][gettext('articles')] .= $page;
				}
				$subtab = printSubtabs();
				?>
				<div id="tab_articles" class="tabbox">
					<?php
					$admintype = 'newsarticle';
					$additem = gettext('New Article');
					$deleteitem = gettext('Article');
					$themepage = 'news';
				}

				if (is_AdminEditPage('newscategory')) {
					$subtab = printSubtabs();
					?>
					<div id="tab_articles" class="tabbox">
						<?php
						$admintype = 'newscategory';
						IF (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
							$additem = gettext('New Category');
						} else {
							$additem = '';
						}
						$deleteitem = gettext('Category');
						$themepage = 'news';
					}

					if (is_AdminEditPage('page')) {
						$subtab = 'edit';
						$admintype = 'page';
						$additem = gettext('New Page');
						$deleteitem = gettext('Page');
						$themepage = 'pages';
					}

					if ($result->transient) {
						if (is_AdminEditPage('newsarticle')) {
							?>
							<h1><?php echo gettext('New Article'); ?></h1>
							<?php
						}
						if (is_AdminEditPage('newscategory')) {
							?>
							<h1><?php echo gettext('New Category'); ?></h1>
							<?php
						}
						if (is_AdminEditPage('page')) {
							?>
							<h1><?php echo gettext('New Page'); ?></h1>
							<?php
						}
					} else if (!$result->loaded) {
						?>
						<div class="errorbox">
							<?php
							if (is_AdminEditPage('newsarticle')) {
								?>
								<h1><?php printf(gettext('Article <em>%s</em> not found'), html_encode(sanitize($_GET['titlelink']))); ?></h1>
								<?php
							}
							if (is_AdminEditPage('newscategory')) {
								?>
								<h1><?php printf(gettext('Category <em>%s</em> not found'), html_encode(sanitize($_GET['titlelink']))); ?></h1>
								<?php
							}
							if (is_AdminEditPage('page')) {
								?>
								<h1><?php printf(gettext('Page <em>%s</em> not found'), html_encode(sanitize($_GET['titlelink']))); ?></h1>
								<?php
							}
							?>
						</div>
						<?php
					} else {
						if (is_AdminEditPage('newsarticle')) {
							?>
							<h1><?php echo gettext('Edit Article:'); ?> <em><?php checkForEmptyTitle($result->getTitle(), 'news', false); ?></em></h1>
							<?php
							if ($result->getDatetime() >= date('Y-m-d H:i:s')) {
								echo '<small><strong id="scheduldedpublishing">' . gettext('(Article scheduled for publishing)') . '</strong></small>';
								if ($result->getShow() != 1) {
									echo '<p class="scheduledate"><small>' . gettext('<strong>Note:</strong> Scheduled publishing is not active unless the article is also set to <em>published</em>') . '</small></p>';
								}
							}
							if ($result->inProtectedCategory()) {
								echo '<p class="notebox">' . gettext('<strong>Note:</strong> This article belongs to a password protected category.') . '</p>';
							}
						}
						if (is_AdminEditPage('newscategory')) {
							?>
							<h1><?php echo gettext('Edit Category:'); ?> <em><?php checkForEmptyTitle($result->getTitle(), 'category', false); ?></em></h1>
							<?php
						}
						if (is_AdminEditPage('page')) {
							?>
							<h1><?php echo gettext('Edit Page:'); ?> <em><?php checkForEmptyTitle($result->getTitle(), 'page', false); ?></em></h1>
							<?php
							if ($result->getDatetime() >= date('Y-m-d H:i:s')) {
								echo ' <small><strong id="scheduldedpublishing">' . gettext('(Page scheduled for publishing)') . '</strong></small>';
								if ($result->getShow() != 1) {
									echo '<p class="scheduledate"><small>' . gettext('Note: Scheduled publishing is not active unless the page is also set to <em>published</em>') . '</small></p>';
								}
							}
							if ($result->getPassword()) {
								echo '<p class="notebox">' . gettext('<strong>Note:</strong> This page is password protected.') . '</p>';
							}
						}
					}
					if ($result->loaded || $result->transient) {
						if ($result->transient) {
							?>
							<form method="post" name="addnews" action="admin-edit.php?<?php echo $admintype; ?>&amp;save">
								<?php
								XSRFToken('save');
							} else {
								?>
								<form method="post" name="update" action="admin-edit.php?<?php echo $admintype; ?>&amp;update<?php echo $page; ?>">
									<?php
									XSRFToken('update');
								}
								?>
								<input type="hidden" name="id" value="<?php echo $result->getID(); ?>" />
								<input type="hidden" name="titlelink-old" id="titlelink-old" value="<?php echo html_encode($result->getTitlelink()); ?>" />
								<input type="hidden" name="lastchange" id="lastchange" value="<?php echo date('Y-m-d H:i:s'); ?>" />
								<input type="hidden" name="lastchangeauthor" id="lastchangeauthor" value="<?php echo $_zp_current_admin_obj->getUser(); ?>" />
								<input type="hidden" name="hitcounter" id="hitcounter" value="<?php echo $result->getHitcounter(); ?>" />

								<?php
								if (is_AdminEditPage("newsarticle")) {
									$backurl = 'admin-news-articles.php?' . $page;
									if (isset($_GET['category']))
										$backurl .= '&amp;category=' . html_encode(sanitize($_GET['category']));
									if (isset($_GET['date']))
										$backurl .= '&amp;date=' . html_encode(sanitize($_GET['date']));
									if (isset($_GET['published']))
										$backurl .= '&amp;published=' . html_encode(sanitize($_GET['published']));
									if (isset($_GET['sortorder']))
										$backurl .= '&amp;sortorder=' . html_encode(sanitize($_GET['sortorder']));
									if (isset($_GET['articles_page']))
										$backurl .= '&amp;articles_page=' . html_encode(sanitize($_GET['articles_page']));
								}
								if (is_AdminEditPage("newscategory")) {
									$backurl = 'admin-categories.php?';
								}
								if (is_AdminEditPage("page")) {
									$backurl = 'admin-pages.php';
								}
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
								<span class="buttons">
									<strong><a href="<?php echo $backurl; ?>"><img	src="../../images/arrow_left_blue_round.png" alt="" /><?php echo gettext("Back"); ?></a></strong>
									<button type="submit" title="<?php echo $updateitem; ?>"><img src="../../images/pass.png" alt="" /><strong><?php
											if ($result->transient) {
												echo $saveitem;
											} else {
												echo $updateitem;
											}
											?></strong></button>
									<button type="reset" onclick="javascript:$('.copydelete').hide();" >
										<img src="../../images/reset.png" alt="" />
										<strong><?php echo gettext("Reset"); ?></strong>
									</button>
									<div class="floatright">
										<?php
										if ($additem) {
											?>
											<strong><a href="admin-edit.php?<?php echo $admintype; ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>" title="<?php echo $additem; ?>"><img src="images/add.png" alt="" /> <?php echo $additem; ?></a></strong>
											<?php
										}
										?>
										<span id="tip"><a href="#"><img src="images/info.png" alt="" /><?php echo gettext("Usage tips"); ?></a></span>
										<?php
										if (!$result->transient) {
											if (is_AdminEditPage("newscategory")) {
												?>
												<a href="../../../index.php?p=<?php echo $themepage; ?>&amp;category=<?php echo $result->getTitlelink(); ?>" title="<?php echo gettext("View"); ?>"><img src="images/view.png" alt="" /><?php echo gettext("View"); ?></a>
											<?php } else { ?>
												<a href="../../../index.php?p=<?php echo $themepage; ?>&amp;title=<?php echo $result->getTitlelink(); ?>" title="<?php echo gettext("View"); ?>"><img src="images/view.png" alt="" /><?php echo gettext("View"); ?></a>
												<?php
											}
										}
										?>
									</div>
								</span>
								<br style="clear: both" /><br style="clear: both" />

								<div id="tips" style="display:none">
									<br />
									<h2><?php echo gettext("Usage tips"); ?></h2>
									<p><?php echo gettext("Check <em>Edit Titlelink</em> if you need to customize how the title appears in URLs. Otherwise it will be automatically updated to any changes made to the title. If you want to prevent this check <em>Enable permaTitlelink</em> and the titlelink stays always the same (recommended if you use Zenphoto's multilingual mode)."); ?></p>
									<p class="notebox"><?php echo gettext("<strong>Note:</strong> Edit titlelink overrides the permalink setting."); ?></p>
									<p class="notebox"><?php echo gettext("<strong>Important:</strong> If you are using Zenphoto's multi-lingual mode the Titlelink is generated from the Title of the currently selected language."); ?></p>
									<p><?php echo gettext("If you lock an article only the current active author/user or any user with full admin rights will be able to edit it later again!"); ?></p>
									<?php
									if (is_AdminEditPage("newsarticle")) {
										?>
										<p><?php echo gettext("<em>Custom article shortening:</em> You can set a custom article shorten length for the news loop excerpts by using the standard TinyMCE <em>page break</em> plugin button. This will override the general shorten length set on the plugin option then."); ?></p>
										<?php
									}
									?>
									<p><?php echo gettext("<em>Scheduled publishing:</em> To automatically publish a page/news article in the future set it to 'published' and enter a future date in the date field manually. Note this works on server time!"); ?></p>
									<p><?php echo gettext("<em>Expiration date:</em> Enter a future date in the date field manually to set a date the page or article will be set un-published automatically. After the page/article has been expired it can only be published again if the expiration date is deleted. Note this works on server time!"); ?></p>
									<p><?php echo gettext("<em>ExtraContent:</em> Here you can enter extra content for example to be printed on the sidebar"); ?></p>
									<p>
										<?php
										echo gettext("<em>Codeblocks:</em> Use these fields if you need to enter php code (for example Zenphoto functions) or JavaScript code.");
										echo gettext("You also can use the codeblock fields as custom fields.");
										echo gettext("Note that your theme must be setup to use the codeblock functions. Note also that codeblock fields are not multi-lingual.");
										?>
									</p>
									<p class="notebox"><?php echo gettext("<strong>Important:</strong> If setting a password for a page its subpages inherit the protection."); ?></p>
									<p><?php echo gettext("Hint: If you need more space for your text use TinyMCE's full screen mode (Click the blue square on the top right of editor's control bar)."); ?></p>
								</div>
								<div <?php if (is_AdminEditPage("page")) echo 'class="box"'; ?> style="padding:15px; margin-top: 10px">
									<table class="formlayout">
										<tr>
											<td class="topalign-padding"><?php echo gettext("Title:"); ?></td>
											<td class="middlecolumn">
	<?php print_language_string_list($result->getTitle('all'), 'title', false, NULL, 'title', '100%', 'zenpage_language_string_list', 10); ?>
											</td>
											<td class="rightcolumn" rowspan="6">
												<h2 class="h2_bordered_edit"><?php echo gettext("Publish"); ?></h2>
												<div class="box-edit">
													<?php
													if (!is_AdminEditPage("newscategory")) {
														?>
														<p><?php echo gettext("Author:"); ?> <?php authorSelector($result->getAuthor()); ?></p>
														<?php
													}
													if (!$result->transient) {
														?>
														<p class="checkbox">
															<input name="edittitlelink" type="checkbox" id="edittitlelink" value="1" onclick="toggleTitlelink();" />
															<label for="edittitlelink"><?php echo gettext("Edit TitleLink"); ?></label>
														</p>
														<?php
													}
													?>
													<p class="checkbox">
														<input name="permalink" type="checkbox" id="permalink" value="1" <?php checkIfChecked($result->getPermalink()); ?> />
														<label for="permalink"><?php echo gettext("Enable permaTitlelink"); ?></label>
													</p>
													<p class="checkbox">
														<input name="show" type="checkbox" id="show" value="1" <?php checkIfChecked($result->getShow()); ?> />
														<label for="show"><?php echo gettext("Published"); ?></label>
													</p>
													<?php
													if (is_AdminEditPage('newsarticle')) {
														$sticky = $result->get('sticky');
														?>
														<p class="checkbox">
															<input name="truncation" type="checkbox" id="truncation" value="1" <?php checkIfChecked($result->getTruncation()); ?> />
															<label for="truncation"><?php echo gettext("Truncate at <em>pagebreak</em>"); ?></label>
														</p>
														<p><?php echo gettext("Position:"); ?>
															<select id="sticky" name="sticky">
																<option value="<?php echo NEWS_POSITION_NORMAL; ?>" <?php if ($sticky == NEWS_POSITION_NORMAL) echo 'selected="selected"'; ?>><?php echo gettext("normal"); ?></option>
																<option value="<?php echo NEWS_POSITION_STICKY; ?>" <?php if ($sticky == NEWS_POSITION_STICKY) echo 'selected="selected"'; ?>><?php echo gettext("sticky"); ?></option>
																<option value="<?php echo NEWS_POSITION_STICK_TO_TOP; ?>" <?php if ($sticky == NEWS_POSITION_STICK_TO_TOP) echo 'selected="selected"'; ?>><?php echo gettext("Stick to top"); ?></option>
															</select>
														</p>
														<?php
													}
													?>
													<?php
													if (!is_AdminEditPage("newscategory")) {
														?>
														<p class="checkbox">
															<input name="locked" type="checkbox" id="locked" value="1" <?php checkIfChecked($result->getLocked()); ?> />
															<label for="locked"><?php echo gettext("Locked for changes"); ?></label>
														</p>
														<?php
													}
													if (get_class($result) == 'ZenpagePage' || get_class($result) == 'ZenpageCategory') {
														$hint = $result->getPasswordHint('all');
														$user = $result->getUser();
														$x = $result->getPassword();
													} else {
														$hint = $user = $x = '';
													}
													if (is_AdminEditPage('page') || is_AdminEditPage('newscategory')) {
														?>
														<p class="passwordextrashow" <?php if (GALLERY_SECURITY != 'public') echo 'style="display:none"'; ?>>
															<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
															<?php
															if (GALLERY_SECURITY == 'public') {
																?>
																<a href="javascript:toggle_passwords('',true);">
																<?php echo gettext("Password:"); ?>
																</a>
																<?php
																if (empty($x)) {
																	?>
																	<img src="../../images/lock_open.png" alt="" class="icon-postiion-top8" />
																	<?php
																} else {
																	$x = '          ';
																	?>
																	<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>"><img src="../../images/lock.png"  alt="" class="icon-postiion-top8" /></a>
																	<?php
																}
																?>
															</p>
															<div class="passwordextrahide" style="display:none">
																<a href="javascript:toggle_passwords('',false);">
			<?php echo gettext("Guest user:"); ?>
																</a>
																<input type="text" size="27" id="user_name" name="user"
																			 onkeydown="passwordClear('');"
																			 value="<?php echo html_encode($user); ?>" />
																<span id="strength"><?php echo gettext("Password:"); ?></span>
																<br />
																<input type="password" size="27"
																			 id="pass" name="pass"
																			 onkeydown="passwordClear('');"
																			 onkeyup="passwordStrength('');"
																			 value="<?php echo $x; ?>" />
																<br />
																<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear('');
					togglePassword('');"><?php echo gettext('Show password'); ?></label>
																<br />
																<span class="password_field_">
																	<span id="match"><?php echo gettext("(repeat)"); ?></span>
																	<br />
																	<input type="password" size="27"
																				 id="pass_r" name="pass_r" disabled="disabled"
																				 onkeydown="passwordClear('');"
																				 onkeyup="passwordMatch('');"
																				 value="<?php echo $x; ?>" />
																	<br />
																</span>
																<?php echo gettext("Password hint:"); ?>
																<br />
															<?php print_language_string_list($hint, 'hint', false, NULL, 'hint', 27); ?>
															</div>
															<?php
														}
													}
													if (!$result->transient && !is_AdminEditPage('newscategory')) {
														?>
														<label class="checkboxlabel">
															<input type="radio" id="copy_object" name="copy_delete_object" value="copy"
																		 onclick="javascript:$('#copyfield').show();
				$('#deletemsg').hide();" />
		<?php echo gettext("Copy"); ?>
														</label>
														<label class="checkboxlabel">
															<input type="radio" id="delete_object" name="copy_delete_object" value="delete"
																		 onclick="deleteConfirm('delete_object', '', '<?php printf(gettext('Are you sure you want to delete this %s?'), $deleteitem); ?>');
				$('#copyfield').hide();" />
		<?php echo gettext('delete'); ?>
														</label>
														<br class="clearall" />
														<div class="copydelete" id="copyfield" style="display:none" >
															<?php printf(gettext('copy as: %s'), '<input type="text" name="copy_object_as" value = "" />'); ?>
														</div>
														<div class="copydelete" id="deletemsg"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
														<?php printf(gettext('%s will be deleted when changes are applied.'), $deleteitem); ?>
														</div>
														<?php
													}
													if (is_AdminEditPage("newsarticle")) {
														echo zp_apply_filter('publish_article_utilities', '', $result);
													}
													if (is_AdminEditPage("newscategory")) {
														echo zp_apply_filter('publish_category_utilities', '', $result);
													}
													if (is_AdminEditPage("page")) {
														echo zp_apply_filter('publish_page_utilities', '', $result);
													}
													?>
												</div>
												<?php
												if (!is_AdminEditPage("newscategory")) {
													?>
													<h2 class="h2_bordered_edit"><?php echo gettext("Date"); ?></h2>
													<div class="box-edit">
														<p>

															<script type="text/javascript">
		// <!-- <![CDATA[
		$(function() {
		$("#date").datepicker({
			showOn: 'button',
			buttonImage: '../../images/calendar.png',
			buttonText: '<?php echo gettext('calendar'); ?>',
			buttonImageOnly: true
		});
		});
		// ]]> -->
															</script>
															<?php
															$date = $result->getDatetime();
															?>
															<input name="date" type="text" id="date" value="<?php echo $date; ?>" onchange="checkFuturePub();" />
															<br />
															<strong class='scheduledpublishing'>
																<?php
																if ($date > date('Y-m-d H:i:s')) {
																	echo addslashes(gettext('Future publishing date:'));
																}
																?>
															</strong>
														</p>
														<hr />
														<p>
															<script type="text/javascript">
		// <!-- <![CDATA[
		$(function() {
		$("#expiredate").datepicker({
			showOn: 'button',
			buttonImage: '../../images/calendar.png',
			buttonText: '<?php echo gettext('calendar'); ?>',
			buttonImageOnly: true
		});
		});
		// ]]> -->
															</script>

															<?php
															echo gettext("Expiration date:");
															$date = $result->getExpireDate();
															?>
															<br />
															<input name="expiredate" type="text" id="expiredate" value="<?php echo $date; ?>" onchange="checkFutureExpiry();" />
															<br />
															<strong class='expire'>
																<?php
																if (!empty($date) && ($date <= date('Y-m-d H:i:s'))) {
																	echo '<br />' . gettext('This is not a future date!');
																}
																?>
															</strong>
														</p>
														<?php
														if ($result->getLastchangeAuthor() != "") {
															?>
															<hr /><p><?php printf(gettext('Last change:<br />%1$s<br />by %2$s'), $result->getLastchange(), $result->getLastchangeauthor()); ?>
															</p>
															<?php
														}
														?>
													</div>

													<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
													<div class="box-edit">

														<p class="checkbox">
															<input name="commentson" type="checkbox" id="commentson" value="1" <?php checkIfChecked($result->getCommentsAllowed()); ?> />
															<label for="commentson"> <?php echo gettext("Comments on"); ?></label>
														</p>
														<?php
														if (!$result->transient && extensionEnabled('hitcounter')) {
															$hc = $result->getHitcounter();
															?>
															<p class="checkbox">
																<input name="resethitcounter" type="checkbox" id="resethitcounter" value="1"<?php if (!$hc) echo ' disabled="disabled"'; ?> />
																<label for="resethitcounter"> <?php printf(ngettext("Reset hitcounter (%u hit)", "Reset hitcounter (%u hits)", $hc), $hc); ?></label>
															</p>
															<?php
														}
														if (extensionEnabled('rating')) {
															?>
															<p class="checkbox">
																<?php
																$tv = $result->get('total_value');
																$tc = $result->get('total_votes');

																if ($tc > 0) {
																	$hc = $tv / $tc;
																	?>
																	<label>
																		<input type="checkbox" id="reset_rating" name="reset_rating" value="1" />
																	<?php printf(gettext('Reset rating (%u stars)'), $hc); ?>
																	</label>
																	<?php
																} else {
																	?>
																	<label>
																		<input type="checkbox" name="reset_rating" value="1" disabled="disabled"/>
																	<?php echo gettext('Reset rating (unrated)'); ?>
																	</label>
																	<?php
																}
																?>
															</p>
															<?php
														}
														?>
													<?php echo zp_apply_filter('general_zenpage_utilities', '', $result); ?>
													</div>
														<?php if (is_AdminEditPage("newsarticle")) { ?>
														<h2 class="h2_bordered_edit"><?php echo gettext("Categories"); ?></h2>
														<div class="zenpagechecklist">
															<?php
															if (is_object($result)) {
																?>
																<ul>
																<?php printNestedItemsList('cats-checkboxlist', $result->getID()); ?>
																</ul>
																<?php
															} else {
																?>
																<ul>
																<?php printNestedItemsList('cats-checkboxlist', '', 'all'); ?>
																</ul>
																<?php
															}
															?>
														</div>
														<br />

														<?php
													} // if article for categories
												} // if !category end
												if (!is_AdminEditPage("newscategory")) {
													?>
													<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?></h2>
													<div class="box-edit-unpadded">
													<?php tagSelector($result, 'tags_', false, getTagOrder()); ?>
													</div>
													<?php
												}
												?>
											</td>
										</tr>
										<tr>
											<td><?php echo gettext("TitleLink:"); ?></td>
											<td class="middlecolumn">
												<?php
												if ($result->transient) {
													echo gettext("A search engine friendly <em>titlelink</em> (aka slug) without special characters to be used in URLs is generated from the title of the currently chosen language automatically. You can edit it manually later after saving if necessary.");
												} else {
													?>
													<input name="titlelink" type="text" size="92" id="titlelink" value="<?php echo $result->getTitlelink(); ?>" disabled="disabled" />
													<?php
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="topalign-padding"><?php echo gettext("Content:"); ?></td>
											<td class="middlecolumn">
												<?php
												if (is_AdminEditPage("newscategory")) {
													print_language_string_list($result->getDesc('all'), 'desc', true, NULL, 'desc', '100%', 'zenpage_language_string_list', 20);
												} else {
													print_language_string_list($result->getContent('all'), 'content', true, NULL, 'content', '100%', 'zenpage_language_string_list', 35);
												}
												?>
											</td>
										</tr>
										<?php
										if (!is_AdminEditPage("newscategory")) {
											?>
											<tr>
												<td class="topalign-padding"><?php echo gettext("ExtraContent:"); ?></td>
												<td class="middlecolumn">
													<?php
													print_language_string_list($result->getExtraContent('all'), 'extracontent', true, NULL, 'extracontent', '100%', 'zenpage_language_string_list', 10);
													?>
												</td>
											</tr>
											<?php
										}


										if (is_AdminEditPage("newsarticle")) {
											$custom = zp_apply_filter('edit_article_custom_data', '', $result);
										}
										if (is_AdminEditPage("newscategory")) {
											$custom = zp_apply_filter('edit_category_custom_data', '', $result);
										}
										if (is_AdminEditPage("page")) {
											$custom = zp_apply_filter('edit_page_custom_data', '', $result);
										}
										if (empty($custom)) {
											?>
											<tr>
												<td class="topalign-nopadding"><br /><?php echo gettext("Custom:"); ?></td>
												<td class="middlecolumn">
													<?php
													print_language_string_list($result->getCustomData('all'), 'custom_data', true, NULL, 'custom_data', '100%', 'zenpage_language_string_list', 10);
													?>
												</td>
											</tr>
											<?php
										} else {
											echo $custom;
										}
										if (!is_AdminEditPage("newscategory")) {
											?>
											<tr>
												<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
												<td class="topalign-nopadding middlecolumn">
											<?php printCodeblockEdit($result, 0); ?>
												</td>
											</tr>
											<?php
										}
										?>
									</table>
									<span class="buttons">
										<strong><a href="<?php echo $backurl; ?>"><img	src="../../images/arrow_left_blue_round.png" alt="" /><?php echo gettext("Back"); ?></a></strong>
										<button type="submit" title="<?php echo $updateitem; ?>"><img src="../../images/pass.png" alt="" /><strong><?php
												if ($result->transient) {
													echo $saveitem;
												} else {
													echo $updateitem;
												}
												?></strong></button>
										<button type="reset" onclick="javascript:$('.copydelete').hide();">
											<img src="../../images/reset.png" alt="" />
											<strong><?php echo gettext("Reset"); ?></strong>
										</button>
										<div class="floatright">
											<strong><a href="admin-edit.php?<?php echo $admintype; ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('add') ?>" title="<?php echo $additem; ?>"><img src="images/add.png" alt="" /> <?php echo $additem; ?></a></strong>
											<?php
											if (!$result->transient) {
												if (is_AdminEditPage("newscategory")) {
													?>
													<a href="../../../index.php?p=<?php echo $themepage; ?>&amp;category=<?php echo $result->getTitlelink(); ?>" title="<?php echo gettext("View"); ?>"><img src="images/view.png" alt="" /><?php echo gettext("View"); ?></a>
													<?php
												} else {
													?>
													<a href="../../../index.php?p=<?php echo $themepage; ?>&amp;title=<?php echo $result->getTitlelink(); ?>" title="<?php echo gettext("View"); ?>"><img src="images/view.png" alt="" /><?php echo gettext("View"); ?></a>
			<?php
		}
	}
	?>
										</div>
									</span>
									<br style="clear: both" />
								</div>
							</form>
					<?php
				}
				if (is_AdminEditPage("newsarticle") || is_AdminEditPage("newscategory")) {
					?>
					</div>
			<?php
		}
		?>
			</div>
		</div>
<?php printAdminFooter(); ?>
</body>
</html>
