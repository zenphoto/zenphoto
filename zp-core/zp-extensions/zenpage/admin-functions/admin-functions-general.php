<?php

/**
 * General Zenpage admin functions
 * 
 * @since 1.7 separated from zenpage-admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
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
		$key = postIndexDecode($key);
		if (substr($key, 0, $l) == $tagsprefix) {
			if ($value) {
				$tags[] = substr($key, $l);
			}
		}
	}
	$tags = array_unique($tags);
	$object->setTags(sanitize($tags, 3));
}

/**
 * Prints the nested list for pages and categories
 *
 * @param string $listtype 'cats-checkboxlist' for a fake nested checkbock list of categories for the news article edit/add page
 * 												'cats-sortablelist' for a sortable nested list of categories for the admin categories page
 * 												'pages-sortablelist' for a sortable nested list of pages for the admin pages page
 * @param $obj $obj Optional, default empty. Passing an articledid is deprecated and will be removed in ZenphotoCMS 2.0.
 * - listtype = 'cats-checkboxlist': Object of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * - listtype = 'pages-sortablelist': Object of the page object to show sub pages of or empty for all 
 * @param string $option Only for $listtype = 'cats-checkboxlist': "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 * @return string | bool
 */
function printNestedItemsList($listtype = 'cats-sortablelist', $obj = '', $option = '', $class = 'nestedItem') {
	global $_zp_zenpage;
	if ($listtype == 'cats-sortablelist' && is_int($obj)) {
		deprecationNotice(gettext('The 2nd parameter of printNestedItemsList() should be a Zenpage news or page object and not an integer (to be removed in ZenphotoCMS 2.0'), '$obj');
		$obj = getItemByID($obj, 'news');
	}
	$id = '';
	if (is_object($obj)) {
		$id = $obj->getID();
	}
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
			//Without this the order is incorrect until the 2nd page reload…
			$_zp_zenpage = new Zenpage();
			$items = $_zp_zenpage->getAllCategories(false);
			break;
		case 'pages-sortablelist':
			if (is_object($obj)) {
				$items = $obj->getPages(false);
			} else {
				$items = $_zp_zenpage->getPages(false);
			}
			break;
		default:
			$items = array();
			break;
	}
	//echo "<pre>"; print_r($items); echo "</pre>";
	$indent = 1;
	$open = array(1 => 0);
	$rslt = false;
	foreach ($items as $item) {
		switch ($listtype) {
			case 'cats-checkboxlist':
			case 'cats-sortablelist':
				$itemobj = new ZenpageCategory($item['titlelink']);
				$ismypage = $itemobj->isMyItem(ZENPAGE_NEWS_RIGHTS);
				break;
			case 'pages-sortablelist':
				$itemobj = new ZenpagePage($item['titlelink']);
				$ismypage = $itemobj->isMyItem(ZENPAGE_PAGES_RIGHTS);
				break;
		}
		$itemsortorder = $itemobj->getSortOrder();
		$itemid = $itemobj->getID();
		if ($ismypage) {
			$order = explode('-', strval($itemsortorder));
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
					printCategoryCheckboxListEntry($itemobj, $id, $option, $class);
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
	global $_zp_db;
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
					$dbtable = $_zp_db->prefix('pages');
					break;
				case 'categories':
					$dbtable = $_zp_db->prefix('news_categories');
					break;
			}
			$sql = "UPDATE " . $dbtable . " SET `sort_order` = " . $_zp_db->quote(implode('-', $orderlist)) . ", `parentid`= " . $myparent . " WHERE `id`=" . $id;
			$_zp_db->query($sql);
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
	switch ($type) {
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
	global $_zp_current_admin_obj;
	$obj->setPublished((int) ($show && 1));
	$obj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
	$obj->save();
}

/**
 * Skips the scheduled future publishing by setting the date of a page or article to the current date to publish it immediately
 * or the expiration handling by setting the expiredate to null.
 *
 * @since 1.5.7
 * @param object $obj
 * @param string $type "futuredate" or "expiredate"
 * @return string
 */
function skipScheduledPublishing($obj, $type = 'futuredate') {
	global $_zp_current_admin_obj;
	switch ($type) {
		case 'futuredate':
			$obj->setDateTime(date('Y-m-d H:i:s'));
			$obj->setPublished(1);
			break;
		case 'expiredate':
			$obj->setExpiredate(null);
			$obj->setPublished(1);
			break;
	}
	$obj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
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
 * Prints the links to JavaScript and CSS files zenpage needs.
 * Actually the same as for zenphoto but with different paths since we are in the plugins folder.
 *
 * @param bool $sortable set to true for tabs with sorts.
 *
 */
function zenpageJSCSS() {
	?>
	<link rel="stylesheet" href="zenpage.css" type="text/css" />
	<script>
		$(document).ready(function() {
			$("#tip a").click(function() {
				$("#tips").toggle("slow");
			});
		});
	</script>
	<?php
}

function printZenpageIconLegend() {
	?>
	<ul class="iconlegend">
		<?php
		if (GALLERY_SECURITY == 'public') {
			?>
			<li><?php echo getStatusIcon('protected') . getStatusIcon('protected_by_parent').  gettext("Password protected/Password protected by parent"); ?></li>
			<li><?php echo getStatusIcon('published') . getStatusIcon('unpublished') . getStatusIcon('unpublished_by_parent'); ?><?php echo gettext("Published/Unpublished/Unpublished by parent"); ?></li>
			<li><?php echo getStatusIcon('publishschedule') . getStatusIcon('expiration') . getStatusIcon('expired'); ?><?php echo gettext("Scheduled publishing/Scheduled expiration/Expired"); ?></li>
			<?php
		}
		?>
		<li><img src="../../images/comments-on.png" alt="" /><img src="../../images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
		<li><img src="../../images/view.png" alt="" /><?php echo gettext("View"); ?></li>
		<?php
		if (extensionEnabled('hitcounter')) {
			?>
			<li><img src="../../images/reset.png" alt="" /><?php echo gettext("Reset hitcounter"); ?></li>
			<?php
		}
		?>
		<li><img src="../../images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
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
		$author = $_zp_current_admin_obj->getLoginName();
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
 * Prints the publish/un-published/scheduled publishing icon with a link for the pages and news articles list.
 * 
 * @since 1.6.1 
 *
 * @param obj $obj Object of the page or news article to check
 */
function printPublishIconLink($obj, $type = '', $linkback = '') {
	$urladd = '';
	if ($obj->table == 'news') {
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
	if ($obj->hasPublishSchedule()) {
		$title = gettext("Publish immediately (skip scheduling)");
		$action = '?skipscheduling=1';
	} else if ($obj->hasExpiration()) {
		$title = gettext("Skip scheduled expiration");
		$action = '?skipexpiration=1';
	} else if ($obj->isPublished()) {
		if ($obj->isUnpublishedByParent()) {
			$title = gettext("Unpublish") .' - ' . getStatusNote('unpublished_by_parent');
			$action = '?publish=0';
		} else {
			$title = gettext("Unpublish");
			$action = '?publish=0';
		}
	} else if (!$obj->isPublished()) {
		if ($obj->hasExpired()) {
			$title = gettext("Publish immediately (skip expiration)");
			$action = '?skipexpiration=1';
		} else {
			$title = gettext("Publish");
			$action = '?publish=1';
		}
	}
	?>
	<a href="<?php echo $action; ?>&amp;titlelink=<?php echo html_encode($obj->getName()) . $urladd; ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>">
		<?php echo getPublishIcon($obj); ?>
	</a>
	<?php
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
	 * Checks if the current logged in admin user is the author that locked the page/article.
	 * Only that author or any user with admin rights will be able to edit or unlock.
	 *
	 * @param object $page The array of the page or article to check
	 * @return bool
	 */
	function checkIfLockedPage($page) {
		global $_zp_current_admin_obj;
		if (zp_loggedin(ADMIN_RIGHTS))
			return true;
		if ($page->getLocked()) {
			return $_zp_current_admin_obj->getLoginName() == $page->getAuthor() && $page->isMyItem(ZENPAGE_PAGES_RIGHTS);
		} else {
			return true;
		}
	}

	/**
	 * Checks if the current logged in admin user is the author that locked the article.
	 * Only that author or any user with admin rights will be able to edit or unlock.
	 *
	 * @param object $page The array of the page or article to check
	 * @return bool
	 */
	function checkIfLockedNews($news) {
		global $_zp_current_admin_obj;
		if (zp_loggedin(ADMIN_RIGHTS))
			return true;
			
		if ($news->getLocked()) {
			return $_zp_current_admin_obj->getLoginName() == $news->getAuthor() && $news->isMyItem(ZENPAGE_NEWS_RIGHTS);
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
		global $_zp_zenpage, $_zp_current_admin_obj;
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
							$key = postIndexDecode($key);
							if (substr($key, 0, 3) == 'cat') {
								if ($value) {
									$cats[] = substr($key, 3);
								}
							}
						}
						$cats = sanitize($cats, 3);
					}
					$n = 0;
					foreach ($links as $titlelink) {
						$class = 'Zenpage' . $type;
						$obj = new $class($titlelink);

						switch ($action) {
							case 'deleteall':
								$obj->remove();
								SearchEngine::clearSearchCache();
								break;
							case 'addtags':
								$mytags = array_unique(array_merge($tags, $obj->getTags()));
								$obj->setTags($mytags);
								break;
							case 'cleartags':
								$obj->setTags(array());
								break;
							case 'alltags':
								$allarticles = $obj->getArticles('', 'all', true);
								foreach ($allarticles as $article) {
									$newsobj = new ZenpageNews($article['titlelink']);
									$mytags = array_unique(array_merge($tags, $newsobj->getTags()));
									$newsobj->setTags($mytags);
									$newsobj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
									$newsobj->save(true);
								}
								break;
							case 'clearalltags':
								$allarticles = $obj->getArticles('', 'all', true);
								foreach ($allarticles as $article) {
									$newsobj = new ZenpageNews($article['titlelink']);
									$newsobj->setTags(array());
									$newsobj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
									$newsobj->save(true);
								}
								break;
							case 'addcats':
								$catarray = array();
								$allcats = $obj->getCategories();
								foreach ($cats as $cat) {
									$catitem = $_zp_zenpage->getCategory($cat);
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
						$obj->setLastchangeUser($_zp_current_admin_obj->getLoginName());
						$obj->save(true);
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
	
	/**
	 * Creates the titlelink from the title passed.
	 * 
	 * @since 1.5.2
	 * 
	 * @param string|array $title The title respectively language array of titles
	 * @param string $date The date the article is saved
	 * @return type
	 */
	function createTitlelink($title, $date) {
		$titlelink = seoFriendly(get_language_string($title));
		if (empty($titlelink)) {
			$titlelink = seoFriendly($date);
		}
		return $titlelink;
	}

	/**
	 * Checks if a title link of this itemtype already exists
	 * 
	 * @since 1.5.2
	 * 
	 * @param string $titlelink The titlelink to check
	 * @param string $itemtype
	 * @return bool
	 */
	function checkTitlelinkDuplicate($titlelink, $itemtype) {
		global $_zp_db;
		switch ($itemtype) {
			case 'article':
				$table = $_zp_db->prefix('news');
				break;
			case 'category':
				$table = $_zp_db->prefix('news_categories');
				break;
			case 'page':
				$table = $_zp_db->prefix('pages');
				break;
		}
		$sql = 'SELECT `id` FROM ' . $table . ' WHERE `titlelink`=' . $_zp_db->quote($titlelink);
		$rslt = $_zp_db->querySingleRow($sql, false);
		return $rslt;
	}

	/**
	 * Append or prepends a date string to a titlelink as defined.
	 * Note: This does not check the item type option and will add to any string passed!
	 * 
	 * @since 1.5.2
	 * 
	 * @param string $titlelink The titleink (e.g. as created by createTitleink())
	 * @return string
	 */
	function addDateToTitlelink($titlelink) {
		$addwhere = getOption('zenpage_titlelinkdate_location');
		$dateformat = getOption('zenpage_titlelinkdate_dateformat');
		switch($dateformat) {
			case 'Y-m-d':
			case 'Ymd':
			case 'Y-m-d_H-i-s':
			case 'YmdHis':
				$date = date($dateformat);
				break;
			default:
			case 'timestamp':
				$date = time();
				break;
	}
	switch ($addwhere) {
		case 'before':
			$titlelink = $date . '-' . $titlelink;
			break;
		default:
		case 'after':
			$titlelink = $titlelink . '-' . $date;
			break;
	}
	return $titlelink;
}
