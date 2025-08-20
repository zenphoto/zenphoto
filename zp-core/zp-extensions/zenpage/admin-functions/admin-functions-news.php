<?php
/**
 * Zenpage news article admin functions
 * 
 * @since 1.7 separated from zenpage-admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */
/**
 * Updates or adds a news article and returns the object of that article
 *
 * @param array $reports display
 * @param bool $newarticle true if a new article
 *
 * @return object
 */
function updateArticle(&$reports, $newarticle = false) {
	global $_zp_current_admin_obj, $_zp_db;
	$date = date('Y-m-d_H-i-s');
	$title = process_language_string_save("title", 2);
	$author = sanitize($_POST['author']);
	$content = updateImageProcessorLink(process_language_string_save("content", EDITOR_SANITIZE_LEVEL));
	$extracontent = updateImageProcessorLink(process_language_string_save("extracontent", EDITOR_SANITIZE_LEVEL));
	$custom = process_language_string_save("custom_data", 1);
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$expiredate = getExpiryDatePost();
	$permalink = getcheckboxState('permalink');
	$commentson = getcheckboxState('commentson');
	if (zp_loggedin(CODEBLOCK_RIGHTS)) {
		$codeblock = processCodeblockSave(0);
	}
	$locked = getcheckboxState('locked');
	if ($newarticle) {
		$titlelink = createTitlelink($title, $date);
		if(getOption('zenpage_titlelinkdate_articles')) {
			$titlelink = addDateToTitlelink($titlelink);
		}
		$duplicate = checkTitlelinkDuplicate($titlelink, 'article');
		if ($duplicate) {
			//already exists
			$titlelink = addDateToTitlelink($titlelink);
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
			$titlelink = createTitlelink($title, $date);
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
		$rslt = $_zp_db->query('UPDATE ' . $_zp_db->prefix('news') . ' SET `titlelink`=' . $_zp_db->quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		} else {
			SearchEngine::clearSearchCache();
		}
	}
	// update article
	$article = new ZenpageNews($titlelink, true);
	$article->setTitle($title);
	$article->setContent($content);
	$article->setExtracontent($extracontent);
	$article->setCustomData(zp_apply_filter('save_article_custom_data', $custom, $article));
	$article->setPublished($show);
	$article->setDateTime($date);
	$article->setLastChange($date);
	$article->setCommentsAllowed($commentson);
	if (zp_loggedin(CODEBLOCK_RIGHTS)) {
		$article->setCodeblock($codeblock);
	}
	$article->setAuthor($author);
	$article->setPermalink($permalink);
	$article->setLocked($locked);
	$article->setExpiredate($expiredate);
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
	$result2 = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news_categories') . " ORDER BY titlelink");
	foreach ($result2 as $cat) {
		if (isset($_POST["cat" . $cat['id']])) {
			$categories[] = $cat['titlelink'];
		}
	}
	$article->setCategories($categories);
	if (!$newarticle) {
		$article->setLastchangeUser($_zp_current_admin_obj->getLoginName());
	}
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
	$checkupdates = true;
	if($newarticle) {
		$checkupdates = false;
	}
	$article->save($checkupdates);
	if ($msg) {
		$reports[] = $msg;
	}
	return $article;
}

/**
 * Deletes an news article from the database
 *
 */
function deleteArticle($titlelink) {
	if (is_object($titlelink)) {
		$obj = $titlelink;
	} else {
		$obj = new ZenpageNews($titlelink);
	}
	$result = $obj->remove();
	if ($result) {
		if (is_object($titlelink)) {
			redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php?deleted');
		}
		SearchEngine::clearSearchCache();
		return "<p class='messagebox fade-message'>" . gettext("Article successfully deleted!") . "</p>";
	}
	return "<p class='errorbox fade-message'>" . gettext("Article delete failed!") . "</p>";
}

/**
 * Print the categories of a news article for the news articles list
 *
 * @param obj $obj object of the news article
 */
function printArticleCategories($obj) {
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
 * Print the categories of a news article for the news articles list
 *
 * @param obj $obj object of the news article
 */
function printPageArticleTags($obj) {
	$tags = $obj->getTags();
	$number = 0;
	foreach ($tags as $tag) {
		$number++;
		if ($number != 1) {
			echo ", ";
		}
		echo get_language_string($tag);
	}
}

/**
 * Prints the checkboxes to select and/or show the category of an news article on the edit or add page
 *
 * @param int $id ID of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * @param string $option "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 */
function printCategorySelection($id = '', $option = '') {
	global $_zp_zenpage, $_zp_db;

	$selected = '';
	echo "<ul class='zenpagechecklist'>\n";
	$all_cats = $_zp_zenpage->getAllCategories(false);
	foreach ($all_cats as $cats) {
		$catobj = new ZenpageCategory($cats['titlelink']);
		if ($option != "all") {
			$cat2news = $_zp_db->querySingleRow("SELECT cat_id FROM " . $_zp_db->prefix('news2cat') . " WHERE news_id = " . $id . " AND cat_id = " . $catobj->getID());
			if (isset($cat2news['cat_id']) && !empty($cat2news['cat_id'])) {
				$selected = "checked ='checked'";
			}
		}
		$catname = $catobj->getTitle();
		$catlink = $catobj->getName();
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

/**
 * Prints the dropdown menu for the date archive selector for the news articles list
 *
 */
function printArticleDatesDropdown($pagenumber) {
	global $_zp_zenpage;
	$datecount = $_zp_zenpage->getAllArticleDates();
	$lastyear = "";
	$nr = 0;
	$option = getNewsAdminOption(array('category' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1, 'author' => 0));
	if (!isset($_GET['date'])) {
		$selected = 'selected="selected"';
	} else {
		$selected = "";
	}
	?>
	<form name="articledatesdropdown" id="articledatesdropdown" style="float:left; margin-left: 10px;" action="#" >
		<select name="ListBoxURL" size="1" onchange="zp_gotoLink(this.form)">
			<?php
			echo "<option $selected value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('' => ''), $option)) . "'>" . gettext("View all months") . "</option>";
			foreach($datecount as $key => $val) {
				$nr++;
				if ($key == '0000-00-01') {
					$year = "no date";
					$month = "";
				} else {
					if (extension_loaded('intl') && getOption('date_format_localized')) {
						$year = zpFormattedDate('yyyy', $key, true); 
						$month = zpFormattedDate('MMMM', $key, true);
					} else {
						$year = zpFormattedDate('Y', $key, false); 
						$month = zpFormattedDate('F', $key,  false);
					}
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
				echo "<option $selected value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('date' => substr($key, 0, 7)), $option)) . "'>$month $year ($val)</option>\n";
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
 * Creates the admin paths for news articles if you use the dropdowns on the admin news article list together
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
 * Prints the dropdown menu for the published/un-publishd selector for the news articles list
 *
 */
function printUnpublishedDropdown() {
	global $_zp_zenpage;
	?>
	<form name="unpublisheddropdown" id="unpublisheddropdown" style="float: left; margin-left: 10px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="zp_gotoLink(this.form)">
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
			$option = getNewsAdminOption(array('category' => 0, 'date' => 0, 'sortorder' => 0, 'articles_page' => 1, 'author' => 0));
			echo "<option $all value='admin-news-articles.php" . getNewsAdminOptionPath($option) . "'>" . gettext("All articles") . "</option>\n";
			echo "<option $published value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('published' => 'yes'), $option)) . "'>" . gettext("Published") . "</option>\n";
			echo "<option $unpublished value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('published' => 'no'), $option)) . "'>" . gettext("Un-published") . "</option>\n";
			echo "<option $sticky value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('published' => 'sticky'), $option)) . "'>" . gettext("Sticky") . "</option>\n";
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
	global $_zp_zenpage;
	?>
	<form name="sortorderdropdown" id="sortorderdropdown" style="float: left; margin-left: 10px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="zp_gotoLink(this.form)">
			<?php
			$orderdate_desc = '';
			$orderdate_asc = '';
			$ordertitle_desc = '';
			$ordertitle_asc = '';
			$orderlastchange_desc = '';
			$orderlastchange_asc = '';
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
					case "lastchange-desc":
						$orderlastchange_desc = "selected='selected'";
						break;
					case "lastchange-asc":
						$orderlastchange_asc = "selected='selected'";
						break;
				}
			} else {
				$orderdate_desc = "selected='selected'";
			}
			$option = getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'articles_page' => 1, 'author' => 0));
			echo "<option $orderdate_desc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'date-desc'), $option)) . "'>" . gettext("Order by date descending") . "</option>\n";
			echo "<option $orderdate_asc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'date-asc'), $option)) . "'>" . gettext("Order by date ascending") . "</option>\n";
			echo "<option $ordertitle_desc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'title-desc'), $option)) . "'>" . gettext("Order by title descending") . "</option>\n";
			echo "<option $ordertitle_asc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'title-asc'), $option)) . "'>" . gettext("Order by title ascending") . "</option>\n";
			echo "<option $orderlastchange_desc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'lastchange-desc'), $option)) . "'>" . gettext("Order by last change date descending") . "</option>\n";
			echo "<option $orderlastchange_asc value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('sortorder' => 'lastchange-asc'), $option)) . "'>" . gettext("Order by last change date ascending") . "</option>\n";
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
	global $_zp_zenpage;
	$result = $_zp_zenpage->getAllCategories(false);
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
	?>
	<form name ="categorydropdown" id="categorydropdown" style="float:left" action="#" >
		<select name="ListBoxURL" size="1" onchange="zp_gotoLink(this.form)">
			<?php
			$option = getNewsAdminOption(array('date' => 0, 'published' => 0, 'sortorder' => 0, 'articles_page' => 1, 'author' => 0));
			echo "<option $selected value='admin-news-articles.php" . getNewsAdminOptionPath($option) . "'>" . gettext("All categories") . "</option>\n";

			foreach ($result as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				// check if there are articles in this category. If not don't list the category.
				$count = count($catobj->getArticles(0, 'all', false, null, null, null, null, false));
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
					$title = '*' . $catobj->getName() . '*';
				}
				if ($count != " (0)") {
					echo "<option $selected value='admin-news-articles.php" . getNewsAdminOptionPath(array_merge(array('category' => $catobj->getName()), $option)) . "'>" . $levelmark . $title . $count . "</option>\n";
				}
			}
			?>
		</select>
	</form>
	<?php
}

/**
 * Prints the dropdown menu for the articles per page selector for the news articles list
 *
 */
function printArticlesPerPageDropdown($pagenumber, $articles_page) {
	global $_zp_zenpage;
	?>
	<form name="articlesperpagedropdown" id="articlesperpagedropdown" method="POST" style="float: left; margin-left: 10px;" action="#">
		<select name="ListBoxURL" size="1"	onchange="zp_gotoLink(this.form)">
			<?php
			$option = getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'sortorder' => 0, 'author' => 0));
			$list = array_unique(array(15, 30, 60, max(1, getOption('articles_per_page'))));
			sort($list);
			foreach ($list as $count) {
				?>
				<option <?php if ($articles_page == $count) echo 'selected="selected"'; ?> value="admin-news-articles.php<?php echo getNewsAdminOptionPath(array_merge(array('articles_page' => $count, 'pagenumber' => (int) ($pagenumber * $articles_page / $count)), $option)); ?>"><?php printf(gettext('%u per page'), $count); ?></option>
				<?php
			}
			?>
			<option <?php if ($articles_page == 0) echo 'selected="selected"'; ?> value="admin-news-articles.php<?php echo getNewsAdminOptionPath(array_merge(array('articles_page' => 'all'), $option)); ?>"><?php echo gettext("All"); ?></option>

		</select>
		&nbsp;&nbsp;
	</form>
	<?php
}

/**
 * Prints the dropdown menu all authors that currently are authors of news articles
 */
function printAuthorDropdown() {
	$authors = Zenpage::getAllAuthors();
	$selected = "selected='selected'";
	if (isset($_GET['author'])) {
		$current_author = sanitize($_GET['author']);
	} else {
		$current_author = "";
	}
	?>
	<form name="newssauthorsdropdown" id="newssauthorsdropdown" method="POST" style="float: left; margin-left: 10px;"	action="#">
		<select name="ListBoxURL" size="1"	onchange="zp_gotoLink(this.form)">
			<?php
			$option = getNewsAdminOption(array('category' => 0, 'date' => 0, 'published' => 0, 'articles_page' => 1, 'sortorder' => 0));			
			foreach ($authors as $author) {
				?>
				<option <?php if ($current_author == $author) echo $selected; ?>value="admin-news-articles.php<?php echo getNewsAdminOptionPath(array_merge(array('author' => $author), $option)); ?>"><?php echo $author; ?></option>
				<?php
			}
			?>
			<option <?php if ($current_author == 'all') echo $selected; ?>value="admin-news-articles.php<?php echo getNewsAdminOptionPath(array_merge(array('author' => 'all'), $option)); ?>"><?php echo gettext("All authors"); ?></option>
		</select>
		&nbsp;&nbsp;
	</form>
	<?php
}
