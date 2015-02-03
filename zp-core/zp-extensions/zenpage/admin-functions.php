<?php
/**
 * zenpage admin functions
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage zenpage
 */

/**
 * Retrieves posted expiry date and checks it against the current date/time
 * Returns the posted date if it is in the future
 * Returns NULL if the date is past
 *
 * @return string
 */
function getExpiryDatePost() {
	$expiredate = sanitize($_POST['expiredate']);
	if ($expiredate > date(date('Y-m-d H:i:s')))
		return $expiredate;
	return NULL;
}

/**
 * processes the taglist save
 *
 * @param object $object the object on which the save happened
 */
function processTags($object) {
	$tagsprefix = 'tags_';
	$tags = array();
	$l = strlen($tagsprefix);
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, $l) == $tagsprefix) {
			if ($value) {
				$tags[] = sanitize(postIndexDecode(substr($key, $l)));
			}
		}
	}
	$tags = array_unique($tags);
	$object->setTags($tags);
}

/* * ************************
  /* page functions
 * ************************* */

/**
 * Updates or adds a page and returns the object of that page
 *
 * @param array $reports report display
 * @param bool $newpage true if it is a new page
 *
 * @return object
 */
function updatePage(&$reports, $newpage = false) {
	$title = process_language_string_save("title", 2);
	$author = sanitize($_POST['author']);
	$content = zpFunctions::updateImageProcessorLink(process_language_string_save("content", EDITOR_SANITIZE_LEVEL));
	$date = sanitize($_POST['date']);
	$lastchange = sanitize($_POST['lastchange']);
	$lastchangeauthor = sanitize($_POST['lastchangeauthor']);
	$pubdate = sanitize($_POST['pubdate']);
	$expiredate = getExpiryDatePost();
	$commentson = getcheckboxState('commentson');
	$permalink = getcheckboxState('permalink');
	$locked = getcheckboxState('locked');
	$show = getcheckboxState('show') && $pubdate <= date(date('Y-m-d H:i:s'));

	if ($newpage) {
		$titlelink = seoFriendly(get_language_string($title));
		if (empty($titlelink)) {
			$titlelink = seoFriendly($date);
		}
		$sql = 'SELECT `id` FROM ' . prefix('pages') . ' WHERE `titlelink`=' . db_quote($titlelink);
		$rslt = query_single_row($sql, false);
		if ($rslt) {
			//already exists
			$time = explode(' ', microtime());
			$titlelink = $titlelink . '_' . ($time[1] + $time[0]);
			$reports[] = "<p class='warningbox fade-message'>" . gettext('Duplicate page title') . '</p>';
		}
		$oldtitlelink = $titlelink;
	} else {
		$titlelink = $oldtitlelink = sanitize($_POST['titlelink-old']);
	}
	if (getcheckboxState('edittitlelink')) {
		$titlelink = sanitize($_POST['titlelink'], 3);
		if (empty($titlelink)) {
			$titlelink = seoFriendly(get_language_string($title));
			if (empty($titlelink)) {
				$titlelink = seoFriendly($date);
			}
		}
	} else {
		if (!$permalink) { //	allow the link to change
			$link = seoFriendly(get_language_string($title));
			if (!empty($link)) {
				$titlelink = $link;
			}
		}
	}
	$id = sanitize($_POST['id']);
	$rslt = true;
	if ($titlelink != $oldtitlelink) { // title link change must be reflected in DB before any other updates
		$rslt = query('UPDATE ' . prefix('pages') . ' SET `titlelink`=' . db_quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		}
	}
	// update page
	$page = newPage($titlelink, true);
	$notice = processCredentials($page);
	$page->setTitle($title);
	$page->setContent($content);
	$page->setShow($show);
	$page->setDateTime($date);
	$page->setCommentsAllowed($commentson);
	$page->setAuthor($author);
	$page->setLastchange($lastchange);
	$page->setLastchangeauthor($lastchangeauthor);
	$page->setPermalink($permalink);
	$page->setLocked($locked);
	$page->setExpiredate($expiredate);
	$page->setPublishDate($pubdate);
	if (getcheckboxState('resethitcounter')) {
		$page->set('hitcounter', 0);
	}
	if (getcheckboxState('reset_rating')) {
		$page->set('total_value', 0);
		$page->set('total_votes', 0);
		$page->set('used_ips', 0);
	}
	processTags($page);

	if ($newpage) {
		$msg = zp_apply_filter('new_page', '', $page);
		if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Page <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"), get_language_string($titlelink)) . '</p>';
		} else if ($notice == '?mismatch=user') {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('You must supply a password for the Protected Page user') . '</p>';
		} else if ($notice) {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('Your passwords were empty or did not match') . '</p>';
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Page <em>%s</em> added"), $titlelink) . '</p>';
		}
	} else {
		$msg = zp_apply_filter('update_page', '', $page, $oldtitlelink);
		if (!$rslt) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("A page with the title/titlelink <em>%s</em> already exists!"), $titlelink) . '</p>';
		} else if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Page <em>%s</em> updated but you need to give it a <strong>title</strong> before publishing!"), get_language_string($titlelink)) . '</p>';
		} else if ($notice == '?mismatch=user') {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('You must supply a password for the Protected Page user') . '</p>';
		} else if ($notice) {
			echo "<p class='errorbox fade-message'>" . gettext('Your passwords were empty or did not match') . '</p>';
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Page <em>%s</em> updated"), $titlelink) . '</p>';
		}
	}
	zp_apply_filter('save_page_custom_data', NULL, $page);
	$page->save();
	if ($msg) {
		$reports[] = $msg;
	}
	return $page;
}

/**
 * Deletes a page (and also if existing its subpages) from the database
 *
 */
function deleteZenpageObj($obj, $redirect = false) {
	$result = $obj->remove();
	if ($result) {
		if ($redirect) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/' . $redirect . '?deleted');
			exitZP();
		}
		switch ($obj->table) {
			case 'pages':
				$msg = gettext("Page successfully deleted!");
				break;
			case 'news':
				$msg = gettext("Article successfully deleted!");
				break;
			case 'news_categories':
				$msg = gettext("Category successfully deleted!");
				break;
		}
		return "<p class='messagebox fade-message'>" . $msg . "</p>";
	}
	switch ($obj->table) {
		case 'pages':
			$msg = gettext("Page delete failed!");
			break;
		case 'news':
			$msg = gettext("Article delete failed!");
			break;
		case 'news_categories':
			$msg = gettext("Category  delete failed!");
			break;
	}
	return "<p class='errorbox fade-message'>" . $msg . "</p>";
}

/**
 * Prints the table part of a single page item for the sortable pages list
 *
 * @param object $page The array containing the single page
 * @param bool $flag set to true to flag the element as having a problem with nesting level
 */
function printPagesListTable($page, $flag) {
	if ($flag) {
		$img = '../../images/drag_handle_flag.png';
	} else {
		$img = '../../images/drag_handle.png';
	}
	?>
	<div class='page-list_row'>
		<div class="page-list_title">
			<?php
			echo "<a href='admin-edit.php?page&amp;titlelink=" . urlencode($page->getTitlelink()) . "'> ";
			checkForEmptyTitle($page->getTitle(), "page");
			echo "</a>" . checkHitcounterDisplay($page->getHitcounter());
			?>
		</div>
		<div class="page-list_extra">
			<span>
				<?php echo html_encode($page->getAuthor()); ?>
			</span>
		</div>
		<div class="page-list_extra">
			<?php printPublished($page); ?>
		</div>
		<div class="page-list_extra">
			<?php printExpired($page); ?>
		</div>
		<div class="page-list_iconwrapper">
			<div class="page-list_icon">
				<?php
				if ($page->getPassword()) {
					echo '<img src="../../images/lock.png" style="border: 0px;" alt="' . gettext('Password protected') . '" title="' . gettext('Password protected') . '" />';
				}
				?>
			</div>
			<div class="page-list_icon">
				<?php
				echo linkPickerIcon($page);
				?>
			</div>

			<?php
			if (checkIfLocked($page)) {
				?>
				<div class="page-list_icon">
					<?php printPublishIconLink($page, "page"); ?>
				</div>
				<div class="page-list_icon">
					<?php
					if ($page->getCommentsAllowed()) {
						?>
						<a href="?commentson=0&amp;titlelink=<?php echo html_encode($page->getTitlelink()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Disable comments'); ?>">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
						</a>
						<?php
					} else {
						?>
						<a href="?commentson=1&amp;titlelink=<?php echo html_encode($page->getTitlelink()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Enable comments'); ?>">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
						</a>
						<?php
					}
					?>
				</div>
				<?php
			} else {
				?>
				<div class="page-list_icon">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<?php
			}
			?>

			<div class="page-list_icon">
				<a href="../../../index.php?p=pages&amp;title=<?php echo js_encode($page->getTitlelink()); ?>" title="<?php echo gettext("View page"); ?>">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/view.png" alt="" title="<?php echo gettext("view"); ?>" />
				</a>
			</div>

			<?php
			if (checkIfLocked($page)) {
				if (extensionEnabled('hitcounter')) {
					?>
					<div class="page-list_icon">
						<a href="?hitcounter=1&amp;titlelink=<?php echo html_encode($page->getTitlelink()); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo gettext("Reset hitcounter"); ?>">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" title="<?php echo gettext("Reset hitcounter"); ?>" /></a>
					</div>
					<?php
				}
				?>
				<div class="page-list_icon">
					<a href="javascript:confirmDelete('admin-pages.php?delete=<?php echo $page->getTitlelink(); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>',deletePage)" title="<?php echo gettext("Delete page"); ?>">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="" title="<?php echo gettext("delete"); ?>" /></a>
				</div>
				<div class="page-list_icon">
					<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $page->getTitlelink(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
				</div>
				<?php
			} else {
				?>
				<div class="page-list_icon">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<input class="checkbox" type="checkbox" name="disable" value="1" disabled="disabled" />
				</div>
				<?php
			}
			?>
		</div><!--  icon wrapper end -->
	</div>
	<?php
}

/* * ************************
  /* news article functions
 * ************************* */

/**
 * Updates or adds a news article and returns the object of that article
 *
 * @param array $reports display
 * @param bool $newarticle true if a new article
 *
 * @return object
 */
function updateArticle(&$reports, $newarticle = false) {
	$date = date('Y-m-d_H-i-s');
	$title = process_language_string_save("title", 2);
	$author = sanitize($_POST['author']);
	$content = zpFunctions::updateImageProcessorLink(process_language_string_save("content", EDITOR_SANITIZE_LEVEL));
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$pubdate = sanitize($_POST['pubdate']);
	$expiredate = getExpiryDatePost();
	$permalink = getcheckboxState('permalink');
	$lastchange = sanitize($_POST['lastchange']);
	$lastchangeauthor = sanitize($_POST['lastchangeauthor']);
	$commentson = getcheckboxState('commentson');
	$locked = getcheckboxState('locked');
	$show = getcheckboxState('show') && $pubdate <= date(date('Y-m-d H:i:s'));

	if ($newarticle) {
		$titlelink = seoFriendly(get_language_string($title));
		if (empty($titlelink)) {
			$titlelink = seoFriendly($date);
		}
		$sql = 'SELECT `id` FROM ' . prefix('news') . ' WHERE `titlelink`=' . db_quote($titlelink);
		$rslt = query_single_row($sql, false);
		if ($rslt) {
			//already exists
			$time = explode(' ', microtime());
			$titlelink = $titlelink . '_' . ($time[1] + $time[0]);
			$reports[] = "<p class='warningbox fade-message'>" . gettext('Duplicate article title') . '</p>';
		}
		$oldtitlelink = $titlelink;
		$id = 0;
	} else {
		$titlelink = $oldtitlelink = sanitize($_POST['titlelink-old'], 3);
		$id = sanitize($_POST['id']);
	}

	if (getcheckboxState('edittitlelink')) {
		$titlelink = sanitize($_POST['titlelink'], 3);
		if (empty($titlelink)) {
			$titlelink = seoFriendly(get_language_string($title));
			if (empty($titlelink)) {
				$titlelink = seoFriendly($date);
			}
		}
	} else {
		if (!$permalink) { //	allow the title link to change.
			$link = seoFriendly(get_language_string($title));
			if (!empty($link)) {
				$titlelink = $link;
			}
		}
	}

	$rslt = true;
	if ($titlelink != $oldtitlelink) { // title link change must be reflected in DB before any other updates
		$rslt = query('UPDATE ' . prefix('news') . ' SET `titlelink`=' . db_quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		}
	}
	// update article
	$article = newArticle($titlelink, true);
	$article->setTitle($title);
	$article->setContent($content);
	$article->setShow($show);
	$article->setDateTime($date);
	$article->setCommentsAllowed($commentson);
	$article->setAuthor($author);
	$article->setLastchange($lastchange);
	$article->setLastchangeauthor($lastchangeauthor);
	$article->setPermalink($permalink);
	$article->setLocked($locked);
	$article->setExpiredate($expiredate);
	$article->setPublishDate($pubdate);
	$article->setSticky(sanitize_numeric($_POST['sticky']));
	if (getcheckboxState('resethitcounter')) {
		$article->set('hitcounter', 0);
	}
	if (getcheckboxState('reset_rating')) {
		$article->set('total_value', 0);
		$article->set('total_votes', 0);
		$article->set('used_ips', 0);
	}
	$article->setTruncation(getcheckboxState('truncation'));
	processTags($article);
	$categories = array();
	$result2 = query_full_array("SELECT * FROM " . prefix('news_categories') . " ORDER BY titlelink");
	foreach ($result2 as $cat) {
		if (isset($_POST["cat" . $cat['id']])) {
			$categories[] = $cat['titlelink'];
		}
	}
	$article->setCategories($categories);
	if ($newarticle) {
		$msg = zp_apply_filter('new_article', '', $article);
		if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Article <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"), get_language_string($titlelink)) . '</p>';
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Article <em>%s</em> added"), $titlelink) . '</p>';
		}
	} else {
		$msg = zp_apply_filter('update_article', '', $article, $oldtitlelink);
		if (!$rslt) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("An article with the title/titlelink <em>%s</em> already exists!"), $titlelink) . '</p>';
		} else if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Article <em>%s</em> updated but you need to give it a <strong>title</strong> before publishing!"), get_language_string($titlelink)) . '</p>';
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Article <em>%s</em> updated"), $titlelink) . '</p>';
		}
	}
	zp_apply_filter('save_article_custom_data', NULL, $article);
	$article->save();

	if ($msg) {
		$reports[] = $msg;
	}
	return $article;
}

/**
 * Print the categories of a news article for the news articles list
 *
 * @param obj $obj object of the news article
 */
function printNewsCategories($obj) {
	$cat = $obj->getCategories();
	$number = 0;
	foreach ($cat as $cats) {
		$number++;
		if ($number != 1) {
			echo ", ";
		}
		echo get_language_string($cats['title']);
	}
}

/**
 * Prints the checkboxes to select and/or show the category of an news article on the edit or add page
 *
 * @param int $id ID of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * @param string $option "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 */
function printCategorySelection($id = '', $option = '') {
	global $_zp_CMS;

	$selected = '';
	echo "<ul class='zenpagechecklist'>\n";
	$all_cats = $_zp_CMS->getAllCategories(false);
	foreach ($all_cats as $cats) {
		$catobj = newCategory($cats['titlelink']);
		if ($option != "all") {
			$cat2news = query_single_row("SELECT cat_id FROM " . prefix('news2cat') . " WHERE news_id = " . $id . " AND cat_id = " . $catobj->getID());
			if ($cat2news['cat_id'] != "") {
				$selected = "checked ='checked'";
			}
		}
		$catname = $catobj->getTitle();
		$catlink = $catobj->getTitlelink();
		if ($catobj->getPassword()) {
			$protected = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/lock.png" alt="' . gettext('password protected') . '" />';
		} else {
			$protected = '';
		}
		$catid = $catobj->getID();
		echo "<li class=\"hasimage\" ><label for='cat" . $catid . "'><input name='cat" . $catid . "' id='cat" . $catid . "' type='checkbox' value='" . $catid . "' " . $selected . " />" . $catname . " " . $protected . "</label></li>\n";
	}
	echo "</ul>\n";
}

function printAuthorDropdown() {
	$rslt = query_full_array('SELECT DISTINCT `author` FROM ' . prefix('news'));
	if (count($rslt) > 1) {
		$authors = array();
		foreach ($rslt as $row) {
			$authors[] = $row['author'];
		}
		if (isset($_GET['author'])) {
			$cur_author = sanitize($_GET['author']);
			$selected = 'selected="selected"';
		} else {
			$selected = $cur_author = NULL;
		}
		$option = getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1));
		?>
		<form name="AutoListBox0" id="articleauthordropdown" style="float:left; margin:5px;" action="#" >
			<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
				<?php
				echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath($option) . "'>" . gettext("All authors") . "</option>";
				foreach ($authors as $author) {
					if ($cur_author == $author) {
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}
					echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('author' => $author), $option)) . "'>$author</option>\n";
				}
				?>
			</select>

		</form>
		<?php
	}
}

/**
 * Prints the dropdown menu for the date archive selector for the news articles list
 *
 */
function printNewsDatesDropdown() {
	global $_zp_CMS;
	$datecount = $_zp_CMS->getAllArticleDates();
	$lastyear = "";
	$nr = "";
	$option = getNewsAdminOption(array('author' => 0, 'category' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1));
	if (!isset($_GET['date'])) {
		$selected = 'selected = "selected"';
	} else {
		$selected = "";
	}
	?>
	<form name="AutoListBox1" id="articledatesdropdown" style="float:left; margin:5px;" action="#" >
		<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
			<?php
			echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath($option) . "'>" . gettext("View all months") . "</option>";
			while (list($key, $val) = each($datecount)) {
				$nr++;
				if ($key == '0000-00-01') {
					$year = "no date";
					$month = "";
				} else {
					$dt = strftime('%Y-%B', strtotime($key));
					$year = substr($dt, 0, 4);
					$month = substr($dt, 5);
				}
				if (isset($_GET['category'])) {
					$catlink = "&amp;category=" . sanitize($_GET['category']);
				} else {
					$catlink = "";
				}
				$check = $month . "-" . $year;
				if (isset($_GET['date']) AND $_GET['date'] == substr($key, 0, 7)) {
					$selected = "selected='selected'";
				} else {
					$selected = "";
				}
				echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('date' => substr($key, 0, 7)), $option)) . "'>$month $year ($val)</option>\n";
			}
			?>
		</select>

	</form>
	<?php
}

/**
 *
 * Compiles an option parameter list
 * @param array $test array of parameter=>type elements. type=0:string type=1:numeric
 * @return array
 */
function getNewsAdminOption($test) {
	$list = array();
	foreach ($test as $item => $type) {
		if (isset($_GET[$item])) {
			if ($type) {
				$list[$item] = (int) sanitize_numeric($_GET[$item]);
			} else {
				$list[$item] = sanitize($_GET[$item]);
			}
		}
	}
	return $list;
}

/**
 * Crea tes the a dmin paths for news articles if you use the dropdowns on the admin news article list together
 *
 * @param array $list an parameter array of item=>value for instance, the result of getNewsAdminOption()
 * @return string
 */
function getNewsAdminOptionPath($list) {
	$optionpath = '';
	$char = '?';
	foreach ($list as $p => $q) {
		if ($q) {
			$optionpath .= $char . $p . '=' . $q;
		} else {
			$optionpath .= $char . $p;
		}
		$char = '&amp;';
	}
	return $optionpath;
}

/**
 * Prints the dropdown menu for the published/un- publis hd selector for the news articles list
 *
 */
function printUnpublishedDropdown() {
	global $_zp_CMS;
	?>
	<form name="AutoListBox3" id="unpublisheddropdown" style="float:left; margin:5px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="gotoLink(this.form)">
			<?php
			$all = "";
			$published = "";
			$unpublished = "";
			$sticky = '';
			if (isset($_GET['published'])) {
				switch ($_GET['published']) {
					case "no":
						$unpublished = "selected='selected'";
						break;
					case "yes":
						$published = "selected='selected'";
						break;
					case 'sticky':
						$sticky = "selected='selected'";
						break;
				}
			} else {
				$all = "selected='selected'";
			}
			$option = getNewsAdminOption(array('author' => 0, 'category' => 0, 'date' => 0, 'sortorder' => 0, 'articles_page' => 1));
			echo "<option $all value='admin-news.php" . getNewsAdminOptionPath($option) . "'>" . gettext("All articles") . "</option>\n";
			echo "<option $published value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('published' => 'yes'), $option)) . "'>" . gettext("Published") . "</option>\n";
			echo "<option $unpublished value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('published' => 'no'), $option)) . "'>" . gettext("Un-published") . "</option>\n";
			echo "<option $sticky value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('published' => 'sticky'), $option)) . "'>" . gettext("Sticky") . "</option>\n";
			?>
		</select>

	</form>
	<?php
}

/**
 * Prints the dropdown menu for the sortorder selector for the news articles list
 *
 */
function printSortOrderDropdown() {
	global $_zp_CMS;
	?>
	<form name="AutoListBox4" id="sortorderdropdown" style="float:left; margin:5px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="gotoLink(this.form)">
			<?php
			$orderdate_desc = '';
			$orderdate_asc = '';
			$ordertitle_desc = '';
			$ordertitle_asc = '';
			if (isset($_GET['sortorder'])) {
				switch ($_GET['sortorder']) {
					case "date-desc":
						$orderdate_desc = "selected='selected'";
						break;
					case "date-asc":
						$orderdate_asc = "selected='selected'";
						break;
					case "title-desc":
						$ordertitle_desc = "selected='selected'";
						break;
					case "title-asc":
						$ordertitle_asc = "selected='selected'";
						break;
				}
			} else {
				$orderdate_desc = "selected='selected'";
			}
			$option = getNewsAdminOption(array('author' => 0, 'category' => 0, 'date' => 0, 'published' => 0, 'articles_page' => 1));
			echo "<option $orderdate_desc value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'date-desc'), $option)) . "'>" . gettext("Order by date descending") . "</option>\n";
			echo "<option $orderdate_asc value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'date-asc'), $option)) . "'>" . gettext("Order by date ascending") . "</option>\n";
			echo "<option $ordertitle_desc value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'title-desc'), $option)) . "'>" . gettext("Order by title descending") . "</option>\n";
			echo "<option $ordertitle_asc value='admin-news.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'title-asc'), $option)) . "'>" . gettext("Order by title ascending") . "</option>\n";
			?>
		</select>

	</form>
	<?php
}

/**
 * Prints the dropdown menu for the category selector for the news articles list
 *
 */
function printCategoryDropdown() {
	global $_zp_CMS;
	$result = $_zp_CMS->getAllCategories(false);
	if (count($result) > 0) {
		if (isset($_GET['date'])) {
			$datelink = "&amp;date=" . sanitize($_GET['date']);
			$datelinkall = "?date=" . sanitize($_GET['date']);
		} else {
			$datelink = "";
			$datelinkall = "";
		}

		if (isset($_GET['category'])) {
			$selected = '';
			$category = sanitize($_GET['category']);
		} else {
			$selected = "selected='selected'";
			$category = "";
		}
		$option = getNewsAdminOption(array('author' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1));
		?>
		<form name ="AutoListBox2" id="categorydropdown" style="float:left; margin:5px;" action="#" >
			<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
				<?php
				echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath($option) . "'>" . gettext("All categories") . "</option>\n";

				foreach ($result as $cat) {
					$catobj = newCategory($cat['titlelink']);
					// check if there are articles in this category. If not don't list the category.
					$count = count($catobj->getArticles(0, 'all'));
					$count = " (" . $count . ")";
					if ($category == $cat['titlelink']) {
						$selected = "selected='selected'";
					} else {
						$selected = "";
					}
					//This is much easier than hacking the nested list function to work with this
					$getparents = $catobj->getParents();
					$levelmark = '';
					foreach ($getparents as $parent) {
						$levelmark .= '» ';
					}
					$title = $catobj->getTitle();
					if (empty($title)) {
						$title = '*' . $catobj->getTitlelink() . '*';
					}
					if ($count != " (0)") {
						echo "<option $selected value='admin-news.php" . getNewsAdminOptionPath(array_merge(array(
										'category' => $catobj->getTitlelink()), $option)) . "'>" . $levelmark . $title . $count . "</option>\n";
					}
				}
				?>
			</select>
		</form>
		<?php
	}
}

/**
 * Prints the dropdown menu for the articles per page selector for the news articles list
 *
 */
function printArticlesPerPageDropdown($subpage) {
	global $_zp_CMS, $articles_page;
	$option = getNewsAdminOption(array('author' => 0, 'category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0));
	?>
	<form name="AutoListBox5" id="articlesperpagedropdown" method="POST" style="float:left; margin:5px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="gotoLink(this.form)">
			<?php
			$list = array_unique(array(15, 30, 60, max(1, getOption('articles_per_page'))));
			sort($list);
			foreach ($list as $count) {
				?>
				<option <?php if ($articles_page == $count) echo 'selected="selected"'; ?> value="admin-news.php<?php echo getNewsAdminOptionPath(array_merge(array('articles_page' => $count, 'subpage' => (int) ($subpage * $articles_page / $count)), $option)); ?>"><?php printf(gettext('%u per page'), $count); ?></option>
				<?php
			}
			?>
			<option <?php if ($articles_page == 0) echo 'selected="selected"'; ?> value="admin-news.php<?php echo getNewsAdminOptionPath(array_merge(array('articles_page' => 'all'), $option)); ?>"><?php echo gettext("All"); ?></option>
		</select>

	</form>
	<?php
}

/* * ************************
  /* Category functions
 * ************************* */

/**
 * Updates or adds a category
 *
 * @param array $reports the results display
 * @param bool $newcategory true if a new article
 *
 */
function updateCategory(&$reports, $newcategory = false) {
	$date = date('Y-m-d_H-i-s');
	$id = sanitize_numeric($_POST['id']);
	$permalink = getcheckboxState('permalink');
	$title = process_language_string_save("title", 2);
	$desc = process_language_string_save("desc", EDITOR_SANITIZE_LEVEL);

	if ($newcategory) {
		$titlelink = seoFriendly(get_language_string($title));
		if (empty($titlelink))
			$titlelink = seoFriendly($date);
		$sql = 'SELECT `id` FROM ' . prefix('news_categories') . ' WHERE `titlelink`=' . db_quote($titlelink);
		$rslt = query_single_row($sql, false);
		if ($rslt) {
			//already exists
			$time = explode(' ', microtime());
			$titlelink = $titlelink . '_' . ($time[
							1] + $time[0]);
			$reports[] = "<p class='warningbox fade-message'>" . gettext('Duplicate category title') . '</p>';
		}
		$oldtitlelink = $titlelink;
	} else {
		$titlelink = $oldtitlelink = sanitize($_POST['titlelink-old'], 3);
		if (getcheckboxState('edittitlelink')) {
			$titlelink = sanitize($_POST[
							'titlelink'], 3);
			if (empty($titlelink)) {
				$titlelink = seoFriendly(get_language_string($title));
				if (empty($titlelink)) {
					$titlelink = seoFriendly($date);
				}
			}
		} else {
			if (!$permalink) { //	allow the link to change
				$link = seoFriendly(get_language_string($title));
				if (!empty($link)) {
					$titlelink = $link;
				}
			}
		}
	}
	$titleok = true;
	if ($titlelink != $oldtitlelink) { // title link change must be reflected in DB before any other updates
		$titleok = query('UPDATE ' . prefix('news_categories') . ' SET `titlelink`=' . db_quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$titleok) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		}
	}
	//update category
	$show = getcheckboxState('show');
	$cat = newCategory($titlelink, true);
	$notice = processCredentials($cat);
	$cat->setPermalink(getcheckboxState('permalink'));
	$cat->set('title', $title);
	$cat->setDesc($desc);
	$cat->setShow($show);
	if (getcheckboxState('resethitcounter')) {
		$cat->set('hitcounter', 0);
	}
	if (getcheckboxState('reset_rating')) {
		$cat->set('total_value', 0);
		$cat->set('total_votes', 0);
		$cat->set('used_ips', 0);
	}

	if ($newcategory) {
		$msg = zp_apply_filter('new_category', '', $cat);
		if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Category <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"), $titlelink) . '</p>';
		} else if ($notice == '?mismatch=user') {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('You must supply a password for the Protected Category user') . '</p>';
		} else if ($notice) {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('Your passwords were empty or did not match') . '</p>';
		} else {
			$reports[
							] = "<p class='messagebox fade-message'>" . sprintf(gettext("Category <em>%s</em> added"), $titlelink) . '</p>';
		}
	} else {
		$msg = zp_apply_filter('update_category', '', $cat, $oldtitlelink);
		if ($titleok) {
			if (empty($titlelink) OR empty($title)) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your category a <strong>title or titlelink</strong>!") . "</p>";
			} else if ($notice == '?mismatch=user') {
				$reports[] = "<p class='errorbox fade-message'>" . gettext('You must supply a password for the Protected Category user') . '</p>';
			} else if ($notice) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext('Your passwords were empty or did not match') . '</p>';
			} else {
				$reports[] = "<p class='messagebox fade-message'>" . gettext("Category updated!") . "</p>";
			}
		} else {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("A category with the title/titlelink <em>%s</em> already exists!"), html_encode($cat->getTitle())) . "</p>";
		}
	}
	zp_apply_filter('save_category_custom_data', NULL, $cat);
	$cat->save();
	if ($msg) {
		$reports[] = $msg;
	}
	return $cat;
}

/**
 * Prints the list entry of a single category for the sortable list
 *
 * @param array $cat Array storing the db info of the category
 * @param string $flag If the category is protected
 * @return string
 */
function printCategoryListSortableTable($cat, $flag) {
	global $_zp_CMS;
	if ($flag) {
		$img = '../../images/drag_handle_flag.png';
	} else {
		$img = '../../images/drag_handle.png';
	}
	$count = count($cat->getArticles(0, false));
	if ($cat->getTitle()) {
		$cattitle = $cat->getTitle();
	} else {
		$cattitle = "<span style='color:red; font-weight: bold'> <strong>*</strong>" . $cat->getTitlelink() . "*</span>";
	}
	?>
	<div class='page-list_row'>
		<div class='page-list_title' >
			<?php echo "<a href='admin-edit.php?newscategory&amp;titlelink=" . $cat->getTitlelink() . "' title='" . gettext('Edit this category') . "'>" . $cattitle . "</a>" . checkHitcounterDisplay($cat->getHitcounter()); ?>
		</div>
		<div class="page-list_extra">
			<?php echo $count; ?>
			<?php echo gettext("articles"); ?>
		</div>

		<div class="page-list_iconwrapper">
			<div class="page-list_icon"><?php
				$password = $cat->getPassword();
				if (!empty($password)) {
					echo '<img src="../../images/lock.png" style="border: 0px;" alt="' . gettext('Password protected') . '" title="' . gettext('Password protected') . '" />';
				}
				?>
			</div>
			<div class="page-list_icon">
				<?php
				echo linkPickerIcon($cat);
				?>
			</div>
			<div class="page-list_icon">
				<?php
				if ($cat->getShow()) {
					$title = gettext("Un-publish");
					?>
					<a href="?publish=0&amp;titlelink=<?php echo html_encode($cat->getTitlelink()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo $title; ?>">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="<?php gettext("Scheduled for publishing"); ?>" title="<?php echo $title; ?>" /></a>
					<?php
				} else {
					$title = gettext("Publish");
					?>
					<a href="?publish=1&amp;titlelink=<?php echo html_encode($cat->getTitlelink()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo $title; ?>">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/action.png" alt="<?php echo gettext("Un-published"); ?>" title="<?php echo $title; ?>" /></a>
					<?php
				}
				?>
			</div>
			<div class="page-list_icon">
				<?php if ($count == 0) { ?>
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
					<?php
				} else {
					?>
					<a href="../../../index.php?p=news&amp;category=<?php echo js_encode($cat->getTitlelink()); ?>" title="<?php echo gettext("View category"); ?>">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/view.png" alt="view" />
					</a>
				<?php } ?>
			</div>
			<?php
			if (extensionEnabled('hitcounter')) {
				?>
				<div class="page-list_icon"><a
						href="?hitcounter=1&amp;id=<?php echo $cat->getID(); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>"
						title="<?php echo gettext("Reset hitcounter"); ?>"> <img
							src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png"
							alt="<?php echo gettext("Reset hitcounter"); ?>" /> </a>
				</div>
				<?php
			}
			?>
			<div class="page-list_icon">
				<a href="javascript:confirmDelete('admin-categories.php?delete=<?php echo js_encode($cat->getTitlelink()); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('delete_category') ?>',deleteCategory)"
					 title="<?php echo gettext("Delete Category"); ?>"><img
						src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="<?php echo gettext("Delete"); ?>"
						title="<?php echo gettext("Delete Category"); ?>" /></a>
			</div>
			<div class="page-list_icon">
				<?php echo linkPickerIcon($cat, 'link_source_' . $cat->getID()); ?>
			</div>
			<div class="page-list_icon"><input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $cat->getTitlelink(); ?>"
																				 onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
			</div>
		</div>
	</div>
	<?php
}

/**
 * Prints the checkboxes to select and/or show the category of an news article on the edit or add page
 *
 * @param int $id ID of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * @param string $option "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 */
function printCategoryCheckboxListEntry($cat, $articleid, $option, $class = '') {
	$selected = '';
	if (($option != "all") && !$cat->transient && !empty($articleid)) {
		$cat2news = query_single_row("SELECT cat_id FROM " . prefix('news2cat') . " WHERE news_id = " . $articleid . " AND cat_id = " . $cat->getID());
		if ($cat2news['cat_id'] != "") {
			$selected = "checked ='checked'";
		} else {
			$selected = "";
		}
	}
	$catname = $cat->getTitle();
	$catlink = $cat->getTitlelink();
	if ($cat->getPassword()) {
		$protected = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/lock.png" alt="' . gettext('password protected') . '" />';
	} else {
		$protected = '';
	}
	$catid = $cat->getID();
	echo '<label for="cat' . $catid . '"><input name="cat' . $catid . '" class="' . $class . '" id="cat' . $catid . '" type="checkbox" value="' . $catid . '"' . $selected . ' />' . $catname . ' ' . $protected . "</label>\n";
}

/* * ************************
  /* General functions
 * ************************* */

/**
 * Prints the nested list for pages and categories
 *
 * @param string $listtype 'cats-checkboxlist' for a fake nested checkbock list of categories for the news article edit/add page
 * 												'cats-sortablelist' for a sortable nested list of categories for the admin categories page
 * 												'pages-sortablelist' for a sortable nested list of pages for the admin pages page
 * @param int $articleid Only for $listtype = 'cats-checkboxlist': For ID of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * @param string $option Only for $listtype = 'cats-checkboxlist': "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 * @return string | bool
 */
function printNestedItemsList($listtype = 'cats-sortablelist', $articleid = '', $option = '', $class = 'nestedItem') {
	global $_zp_CMS;
	switch ($listtype) {
		case 'cats-checkboxlist':
		default:
			$ulclass = "";
			break;
		case 'cats-sortablelist':
		case 'pages-sortablelist':
			$ulclass = " class=\"page-list\"";
			break;
	}
	switch ($listtype) {
		case 'cats-checkboxlist':
		case 'cats-sortablelist':
			$items = $_zp_CMS->getAllCategories(false);
			break;
		case 'pages-sortablelist':
			$items = $_zp_CMS->getPages(false);
			break;
		default:
			$items = array();
			break;
	}
	$indent = 1;
	$open = array(1 => 0);
	$rslt = false;
	foreach ($items as $item) {
		switch ($listtype) {
			case 'cats-checkboxlist':
			case 'cats-sortablelist':
				$itemobj = newCategory($item['titlelink']);
				$ismypage = $itemobj->isMyItem(ZENPAGE_NEWS_RIGHTS);
				break;
			case 'pages-sortablelist':
				$itemobj = newPage($item['titlelink']);
				$ismypage = $itemobj->isMyItem(ZENPAGE_PAGES_RIGHTS);
				break;
		}
		$itemsortorder = $itemobj->getSortOrder();
		$itemid = $itemobj->getID();
		if ($ismypage) {
			$order = explode('-', $itemsortorder);
			$level = max(1, count($order));
			if ($toodeep = $level > 1 && $order[$level - 1] === '') {
				$rslt = true;
			}
			if ($level > $indent) {
				echo "\n" . str_pad("\t", $indent, "\t") . "<ul" . $ulclass . ">\n";
				$indent++;
				$open[$indent] = 0;
			} else if ($level < $indent) {
				while ($indent > $level) {
					$open[$indent] --;
					$indent--;
					echo "</li>\n" . str_pad("\t", $indent, "\t") . "</ul>\n";
				}
			} else { // indent == level
				if ($open[$indent]) {
					echo str_pad("\t", $indent, "\t") . "</li>\n";
					$open[$indent] --;
				} else {
					echo "\n";
				}
			}
			if ($open[$indent]) {
				echo str_pad("\t", $indent, "\t") . "</li>\n";
				$open[$indent] --;
			}
			switch ($listtype) {
				case 'cats-checkboxlist':
					echo "<li>\n";
					printCategoryCheckboxListEntry($itemobj, $articleid, $option, $class);
					break;
				case 'cats-sortablelist':
					echo str_pad("\t", $indent - 1, "\t") . "<li id=\"id_" . $itemid . "\">";
					printCategoryListSortableTable($itemobj, $toodeep);
					break;
				case 'pages-sortablelist':
					echo str_pad("\t", $indent - 1, "\t") . "<li id=\"id_" . $itemid . "\">";
					printPagesListTable($itemobj, $toodeep);
					break;
			}
			$open[$indent] ++;
		}
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent] --;
		$indent--;
		echo str_pad("\t", $indent, "\t") . "</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
	} else {
		echo "\n";
	}
	return $rslt;
}

/**
 * Updates the sortorder of the items list in the database
 *
 * @param string $mode 'pages' or 'categories'
 * @return array
 */
function updateItemSortorder($mode = 'pages') {
	if (!empty($_POST['order'])) { // if someone didn't sort anything there are no values!
		$order = processOrder($_POST['order']);
		$parents = array('NULL');
		foreach ($order as $id => $orderlist) {
			$id = str_replace('id_', '', $id);
			$level = count($orderlist);
			$parents[$level] = $id;
			$myparent = $parents[$level - 1];
			switch ($mode) {
				case 'pages':
					$dbtable = prefix('pages');
					break;
				case 'categories':
					$dbtable = prefix('news_categories');
					break;
			}
			$sql = "UPDATE " . $dbtable . " SET `sort_order` = " . db_quote(implode('-', $orderlist)) . ", `parentid`= " . $myparent . " WHERE `id`=" . $id;
			query($sql);
		}
		return true;
	}
	return false;
}

/**
 * Checks if no title has been provide for items on new item creation
 * @param string $titlefield The title field
 * @param string $type 'page', 'news' or 'category'
 * @return string
 */
function checkForEmptyTitle($titlefield, $type, $truncate = true) {
	switch (strtolower($type)) {
		case "page":
			$text = gettext("Untitled page");
			break;
		case "news":
			$text = gettext("Untitled article");
			break;
		case "category":
			$text = gettext("Untitled category");
			break;
	}
	$title = getBare($titlefield);
	if ($title) {
		if ($truncate) {
			$title = truncate_string($title, 40);
		}
	} else {
		$title = "<span style='color:red; font-weight: bold'>" . $text . "</span>";
	}
	echo $title;
}

/**
 * Publishes a page or news article
 *
 * @param object $obj
 * @param int $show the value for publishing
 * @return string
 */
function zenpagePublish($obj, $show) {
	$obj->setExpireDate(NULL);
	$obj->setPublishDate(NULL);
	$obj->setShow($show);
	$obj->save();
}

/**
 * Checks if there are hitcounts and if they are displayed behind the news article, page or category title
 *
 * @param string $item The array of the current news article, page or category in the list.
 * @return string
 */
function checkHitcounterDisplay($item) {
	if ($item == 0) {
		$hitcount = "";
	} else {
		if ($item == 1) {
			$hits = gettext("hit");
		} else {
			$hits = gettext("hits");
		}
		$hitcount = " (" . $item . " " . $hits . ")";
	}
	return $hitcount;
}

/**
 * returns an array of how many pages, articles, categories and news or pages comments we got.
 *
 * @param string $option What the statistic should be shown of: "news", "pages", "categories"
 */
function getNewsPagesStatistic($option) {
	global $_zp_CMS;
	switch ($option) {
		case "news":
			$items = $_zp_CMS->getArticles();
			$type = gettext("Articles");
			break;
		case "pages":
			$items = $_zp_CMS->getPages(false);
			$type = gettext("Pages");
			break;
		case "categories":
			$type = gettext("Categories");
			$cats = $_zp_CMS->getAllCategories(false);
			$total = count($cats);
			$unpub = 0;
			break;
	}
	if ($option == "news" OR $option == "pages") {
		$total = count($items);
		$pub = 0;
		foreach ($items as $item) {
			switch ($option) {
				case "news":
					$itemobj = newArticle($item['titlelink']);
					break;
				case "pages":
					$itemobj = newPage($item['titlelink']);
					break;
				case "categories":
					$itemobj = newCategory($item['titlelink']);
					break;
			}
			if ($itemobj->getShow()) {
				$pub++;
			}
		}
		//echo " (un-published: ";
		$unpub = $total - $pub;
	}
	return array($total, $type, $unpub);
}

function printPagesStatistic() {
	list($total, $type, $unpub) = getNewsPagesStatistic("pages");
	if (empty($unpub)) {
		printf(ngettext('(<strong>%1$u</strong> page)', '(<strong>%1$u</strong> pages)', $total), $total);
	} else {
		printf(ngettext('(<strong>%1$u</strong> page, <strong>%2$u</strong> un-published)', '(<strong>%1$u</strong> pages, <strong>%2$u</strong> un-published)', $total), $total, $unpub);
	}
}

function printNewsStatistic($total, $unpub) {
	if (empty($unpub)) {
		printf(ngettext('(<strong>%1$u</strong> news)', '(<strong>%1$u</strong> news)', $total), $total);
	} else {
		printf(ngettext('(<strong>%1$u</strong> news, <strong>%2$u</strong> un-published)', '(<strong>%1$u</strong> news, <strong>%2$u</strong> un-published)', $total), $total, $unpub);
	}
}

function printCategoriesStatistic() {
	list($total, $type, $unpub) = getNewsPagesStatistic("categories");
	printf(ngettext('(<strong>%1$u</strong> category)', '(<strong>%1$u</strong> categories)', $total), $total);
}

/**
 * Prints the links to JavaScript and CSS files zenpage needs.
 * Actually the same as for zenphoto but with different paths since we are in the plugins folder.
 *
 * @param bool $sortable set to true for tabs with sorts.
 *
 */
function zenpageJSCSS() {
	?>
	<link rel="stylesheet" href="zenpage.css" type="text/css" />
	<script type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function () {
			$("#tip a").click(function () {
				$("#tips").toggle("slow");
			});
		});
		// ]]> -->
	</script>
	<?php
}

function printZenpageIconLegend() {
	?>
	<ul class="iconlegend">
		<?php
		if (GALLERY_SECURITY == 'public') {
			?>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
			<?php
		}
		?>
		<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/add.png" alt="" /><?php echo gettext("pick source"); ?></li>
		<li>
			<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /><img	src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/action.png" alt="" />
			<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/clock.png" alt="" /><?php echo gettext("Published/Not published/Scheduled for publishing"); ?>
		</li>
		<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-on.png" alt="" /><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
		<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/view.png" alt="" /><?php echo gettext("View"); ?></li>
		<?php
		if (extensionEnabled('hitcounter')) {
			?>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" /><?php echo gettext("Reset hitcounter"); ?></li>
			<?php
		}
		?>
		<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
	</ul>
	<?php
}

/**
 * Prints a dropdown to select the author of a page or news article (Admin rights users only)
 *
 * @param string $currentadmin The current admin is selected if adding a new article, otherwise the original author
 */
function authorSelector($author = NULL) {
	global $_zp_authority, $_zp_current_admin_obj;
	if (empty($author)) {
		$author = $_zp_current_admin_obj->getUser();
	}
	$authors = array($author => $author);
	if (zp_loggedin(MANAGE_ALL_PAGES_RIGHTS | MANAGE_ALL_NEWS_RIGHTS)) {
		$admins = $_zp_authority->getAdministrators();
		foreach ($admins as $admin) {
			if ($admin['rights'] & (ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS)) {
				$authors[$admin['user']] = $admin['user'];
			}
		}
	}
	?>
	<select size='1' name="author" id="author">
		<?php
		generateListFromArray(array($author), $authors, false, false);
		?>
	</select>
	<?php
}

/**
 * Prints data info for objects
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function printPublished($object) {
	$dt = $object->getPublishDate();
	if ($dt > date('Y-m-d H:i:s')) {
		if ($object->getShow()) {
			echo '<span class="scheduledate">' . $dt . '</strong>';
		} else {
			echo '<span class="inactivescheduledate">' . $dt . '</strong>';
		}
	} else {
		echo '<span>' . $dt . '</span>';
	}
}

/**
 * Prints data info for objects
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function printExpired($object) {
	$dt = $object->getExpireDate();
	if (!empty($dt)) {
		$expired = $dt < date('Y-m-d H:i:s');
		if ($expired) {
			echo ' <span class="expired">' . $dt . "</span>";
		} else {
			echo ' <span class="expiredate">' . $dt . "</span>";
		}
	}
}

/**
 * Prints the publish/un-published/scheduled publishing icon with a link for the pages and news articles list.
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function printPublishIconLink($object, $type, $linkback = '') {
	$urladd = '';
	if ($type == "news") {
		if (isset($_GET['subpage'])) {
			$urladd .= "&amp;subpage=" . sanitize($_GET['subpage']);
		}
		if (isset($_GET['date'])) {
			$urladd .= "&amp;date=" . sanitize($_GET['date']);
		}
		if (isset($_GET['category'])) {
			$urladd .= "&amp;category=" . sanitize($_GET['category']);
		}
		if (isset($_GET['sortorder'])) {
			$urladd .= "&amp;sortorder=" . sanitize($_GET['sortorder']);
		}
		if (isset($_GET['articles_page'])) {
			$urladd .= "&amp;articles_page=" . sanitize_numeric($_GET['articles_page']);
		}
	}

	if ($object->getShow()) {
		$title = gettext("Un-publish");
		?>
		<a href="?publish=0&amp;titlelink=<?php echo html_encode($object->getTitlelink()) . $urladd; ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo $title; ?>">
			<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="<?php echo gettext("Published"); ?>" title="<?php echo $title; ?>" /></a>
		<?php
	} else {
		if ($object->getPublishDate() > date('Y-m-d H:i:s')) {
			//overriding scheduling
			?>
			<a href="?publish=2&amp;titlelink=<?php echo html_encode($object->getTitlelink()) . $urladd; ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>">
				<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/clock.png" alt="<?php echo gettext("Un-published"); ?>" title= "<?php echo gettext("Publish (override scheduling)"); ?>" /></a>
			<?php
		} else {
			?>
			<a href="?publish=1&amp;titlelink=<?php echo html_encode($object->getTitlelink()) . $urladd; ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>">
				<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/action.png" alt="<?php echo gettext("Un-published"); ?>" title= "<?php echo gettext("Publish"); ?>" /></a>
			<?php
		}
	}
}

/**
 * Checks if a checkbox is selected and checks it if.
 *
 * @param string $field the array field of an item array to be checked (for example "permalink" or "comments on")
 */
function checkIfChecked($field) {
	if ($field) {
		echo 'checked="checked"';
	}
}

/**
 * Checks if the current logged in  user is allowed to edit the page/article.
 * Only that author or any user with admin rights will be able to edit or unlock.
 *
 * @param object $obj The page or article to check
 * @return bool
 */
function checkIfLocked($obj) {
	global $_zp_current_admin_obj;
	if ($obj->getLocked()) {
		if (zp_loggedin($obj->manage_rights)) {
			return true;
		}
		return $obj->getAuthor() == $_zp_current_admin_obj->getUser();
	} else {
		return true;
	}
}

/**
 * Checks if the current admin-edit.php page is called for news articles or for pages.
 *
 * @param string $page What you want to check for, "page" or "newsarticle"
 * @return bool
 */
function is_AdminEditPage($page) {
	return isset($_GET[$page]);
}

/**
 * Processes the check box bulk actions
 *
 */
function processZenpageBulkActions($type) {
	global $_zp_CMS;
	$action = false;
	if (isset($_POST['ids'])) {
		//echo "action for checked items:". $_POST['checkallaction'];
		$action = sanitize($_POST['checkallaction']);
		$links = sanitize($_POST['ids']);
		$total = count($links);
		$message = NULL;
		$sql = '';
		if ($action != 'noaction') {
			if ($total > 0) {
				if ($action == 'addtags' || $action == 'alltags') {
					$tags = bulkTags();
				}
				if ($action == 'addcats') {
					foreach ($_POST as $key => $value) {
						if (substr($key, 0, 3) == 'cat') {
							if ($value) {
								$cats[] = sanitize(postIndexDecode(substr($key, 3)));
							}
						}
					}
				}
				$n = 0;
				foreach ($links as $titlelink) {
					$class = 'Zenpage' . $type;
					$obj = new $class($titlelink);

					switch ($action) {
						case 'deleteall':
							$obj->remove();
							break;
						case 'addtags':
							$mytags = array_unique(array_merge($tags, $obj->getTags(false)));
							$obj->setTags($mytags);
							break;
						case 'cleartags':
							$obj->setTags(array());
							break;
						case 'alltags':
							$allarticles = $obj->getArticles('', 'all', true);
							foreach ($allarticles as $article) {
								$newsobj = newArticle($article['titlelink']);
								$mytags = array_unique(array_merge($tags, $newsobj->getTags(false)));
								$newsobj->setTags($mytags);
								$newsobj->save();
							}
							break;
						case 'clearalltags':
							$allarticles = $obj->getArticles('', 'all', true);
							foreach ($allarticles as $article) {
								$newsobj = newArticle($article['titlelink']);
								$newsobj->setTags(array());
								$newsobj->save();
							}
							break;
						case 'addcats':
							$catarray = array();
							$allcats = $obj->getCategories();
							foreach ($cats as $cat) {
								$catitem = $_zp_CMS->getCategory($cat);
								$catarray[] = $catitem['titlelink']; //to use the setCategories method we need an array with just the titlelinks!
							}
							$allcatsarray = array();
							foreach ($allcats as $allcat) {
								$allcatsarray[] = $allcat['titlelink']; //same here!
							}
							$mycats = array_unique(array_merge($catarray, $allcatsarray));
							$obj->setCategories($mycats);
							break;
						case 'clearcats':
							$obj->setCategories(array());
							break;
						case 'showall':
							$obj->set('show', 1);
							break;
						case 'hideall':
							$obj->set('show', 0);
							break;
						case 'commentson':
							$obj->set('commentson', 1);
							break;
						case 'commentsoff':
							$obj->set('commentson', 0);
							break;
						case 'resethitcounter':
							$obj->set('hitcounter', 0);
							break;
					}
					$obj->save();
				}
			}
		}
	}
	return $action;
}

function zenpageBulkActionMessage($action) {
	switch ($action) {
		case 'deleteall':
			$message = gettext('Selected items deleted');
			break;
		case 'showall':
			$message = gettext('Selected items published');
			break;
		case 'hideall':
			$message = gettext('Selected items unpublished');
			break;
		case 'commentson':
			$message = gettext('Comments enabled for selected items');
			break;
		case 'commentsoff':
			$message = gettext('Comments disabled for selected items');
			break;
		case 'resethitcounter':
			$message = gettext('Hitcounter for selected items');
			break;
		case 'addtags':
			$message = gettext('Tags added to selected items');
			break;
		case 'cleartags':
			$message = gettext('Tags cleared from selected items');
			break;
		case 'alltags':
			$message = gettext('Tags added to articles of selected items');
			break;
		case 'clearalltags':
			$message = gettext('Tags cleared from articles of selected items');
			break;
		case 'addcats':
			$message = gettext('Categories added to selected items');
			break;
		case 'clearcats':
			$message = gettext('Categories cleared from selected items');
			break;
		default:
			return "<p class='notebox fade-message'>" . gettext('Nothing changed') . "</p>";
	}
	if (isset($message)) {
		return "<p class='messagebox fade-message'>" . $message . "</p>";
	}
	return false;
}
?>
