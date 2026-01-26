<?php
/**
 * Zenpage news category admin functions
 * 
 * @since 1.7 separated from zenpage-admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/**
 * Updates or adds a category
 *
 * @param array $reports the results display
 * @param bool $newcategory true if a new article
 *
 */
function updateCategory(&$reports, $newcategory = false) {
	global $_zp_zenpage, $_zp_current_admin_obj, $_zp_db;
	$date = date('Y-m-d_H-i-s');
	$id = sanitize_numeric($_POST['id']);
	$permalink = getcheckboxState('permalink');
	$title = process_language_string_save("title", 2);
	$desc = process_language_string_save("desc", EDITOR_SANITIZE_LEVEL);
	$custom = process_language_string_save("custom_data", 1);
	if ($newcategory) {
		$titlelink = createTitlelink($title, $date);
		if(getOption('zenpage_titlelinkdate_categories')) {
			$titlelink = addDateToTitlelink($titlelink);
		}
		$duplicate = checkTitlelinkDuplicate($titlelink, 'category');
		if ($duplicate) {
			//already exists
			$titlelink = addDateToTitlelink($titlelink);
			$reports[] = "<p class='warningbox fade-message'>" . gettext('Duplicate category title') . '</p>';
		}
		$oldtitlelink = $titlelink;
	} else {
		$titlelink = $oldtitlelink = sanitize($_POST['titlelink-old'], 3);
		if (getcheckboxState('edittitlelink')) {
			$titlelink = sanitize($_POST['titlelink'], 3);
			if (empty($titlelink)) {
				$titlelink = createTitlelink($title, $date);
			}
		} else {
			if (!$permalink) { //	allow the link to change
				$link = seoFriendly(i18n::getLanguageString($title));
				if (!empty($link)) {
					$titlelink = $link;
				}
			}
		}
	}
	$titleok = true;
	if ($titlelink != $oldtitlelink) { // title link change must be reflected in DB before any other updates
		$titleok = $_zp_db->query('UPDATE ' . $_zp_db->prefix('news_categories') . ' SET `titlelink`=' . $_zp_db->quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$titleok) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		} else {
			SearchEngine::clearSearchCache();
		}
	}
	//update category
	$show = getcheckboxState('show');
	$cat = new ZenpageCategory($titlelink, true);
	$notice = processCredentials($cat);
	$cat->setPermalink(getcheckboxState('permalink'));
	$cat->set('title', $title);
	$cat->setDesc($desc);
	$cat->setLastChange();
	$cat->setCustomData(filter::applyFilter('save_category_custom_data', $custom, $cat));
	$cat->setPublished($show);
	if (getcheckboxState('resethitcounter')) {
		$cat->set('hitcounter', 0);
	}
	if (getcheckboxState('reset_rating')) {
		$cat->set('total_value', 0);
		$cat->set('total_votes', 0);
		$cat->set('used_ips', 0);
	}
	if ($newcategory) {
		$cat->setDefaultSortorder();
		$msg = filter::applyFilter('new_category', '', $cat);
		if (empty($title)) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext("Category <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"), $titlelink) . '</p>';
		} else if ($notice == '?mismatch=user') {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('You must supply a password for the Protected Category user') . '</p>';
		} else if ($notice) {
			$reports[] = "<p class='errorbox fade-message'>" . gettext('Your passwords were empty or did not match') . '</p>';
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . sprintf(gettext("Category <em>%s</em> added"), $titlelink) . '</p>';
		}
	} else {
		$cat->setLastchangeUser($_zp_current_admin_obj->getLoginName());
		$msg = filter::applyFilter('update_category', '', $cat, $oldtitlelink);
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
	$checkupdates = true;
	if ($newcategory) { 
		$checkupdates = false;
	}
	$cat->save($checkupdates);
	if ($msg) {
		$reports[] = $msg;
	}
	return $cat;
}

/**
 * Deletes a category (and also if existing its subpages) from the database
 *
 */
function deleteCategory($titlelink) {
	$obj = new ZenpageCategory($titlelink);
	$result = $obj->remove();
	if ($result) {
		SearchEngine::clearSearchCache();
		return "<p class='messagebox fade-message'>" . gettext("Category successfully deleted!") . "</p>";
	}
	return "<p class='errorbox fade-message'>" . gettext("Category  delete failed!") . "</p>";
}

/**
 * Prints the list entry of a single category for the sortable list
 *
 * @param array $cat Array storing the db info of the category
 * @param string $flag If the category is protected
 * @return string
 */
function printCategoryListSortableTable($cat, $flag) {
	global $_zp_zenpage;
	if ($flag) {
		$img = '../../images/drag_handle_flag.png';
	} else {
		$img = '../../images/drag_handle.png';
	}
	$count = count($cat->getArticles(0, false));
	if ($cat->getTitle()) {
		$cattitle = $cat->getTitle();
	} else {
		$cattitle = "<span style='color:red; font-weight: bold'> <strong>*</strong>" . $cat->getName() . "*</span>";
	}
	?>
	<div class='page-list_row'>
		<div class='page-list_title' >
			<?php echo "<a href='admin-edit.php?newscategory&amp;titlelink=" . $cat->getName() . "' title='" . gettext('Edit this category') . "'>" . $cattitle . "</a>" . checkHitcounterDisplay($cat->getHitcounter()); ?>
		</div>
		<div class="page-list_extra">
			<?php echo $count; ?>
			<?php echo gettext("articles"); ?>
		</div>

		<div class="page-list_iconwrapper">
			<div class="page-list_icon">
				<?php printProtectedIcon($cat); ?>
			</div>
			<div class="page-list_icon">
				<?php printPublishIconLink($cat, 'newscategory'); ?>
			</div>
			<div class="page-list_icon">
				<?php if ($count == 0) { ?>
					<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
					<?php
				} else {
					?>
					<a href="../../../index.php?p=news&amp;category=<?php echo js_encode($cat->getName()); ?>" title="<?php echo gettext("View category"); ?>">
						<img src="images/view.png" alt="view" />
					</a>
				<?php } ?>
			</div>
			<?php
			if (extensionEnabled('hitcounter')) {
				?>
				<div class="page-list_icon"><a
						href="?hitcounter=1&amp;id=<?php echo $cat->getID(); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>"
						title="<?php echo gettext("Reset hitcounter"); ?>"> <img
							src="../../images/reset.png"
							alt="<?php echo gettext("Reset hitcounter"); ?>" /> </a>
				</div>
				<?php
			}
			?>
			<div class="page-list_icon"><a
					href="javascript:confirmDelete('admin-categories.php?delete=<?php echo js_encode($cat->getName()); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('delete_category') ?>',deleteCategory)"
					title="<?php echo gettext("Delete Category"); ?>"><img
						src="../../images/fail.png" alt="<?php echo gettext("Delete"); ?>"
						title="<?php echo gettext("Delete Category"); ?>" /></a>
			</div>
			<div class="page-list_icon"><input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $cat->getName(); ?>"
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
	global $_zp_db;
	$selected = '';
	if (($option != "all") && !$cat->transient && !empty($articleid)) {
		$cat2news = $_zp_db->querySingleRow("SELECT cat_id FROM " . $_zp_db->prefix('news2cat') . " WHERE news_id = " . $articleid . " AND cat_id = " . $cat->getID());
		$selected = "";
		if (isset($cat2news['cat_id']) && !empty($cat2news['cat_id'])) {
			$selected = "checked ='checked'";
		}
	}
	$catname = $cat->getTitle();
	$catlink = $cat->getName();
	/*if ($cat->getPassword()) {
		$protected = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/lock.png" alt="' . gettext('password protected') . '" />';
	} else {
		$protected = '';
	} */
	$catid = $cat->getID();
	echo '<label for="cat' . $catid . '"><input name="cat' . $catid . '" class="' . $class . '" id="cat' . $catid . '" type="checkbox" value="' . $catid . '"' . $selected . ' />' . $catname;
	printProtectedIcon($cat);
	echo "</label>\n";
}