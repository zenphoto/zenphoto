<?php

/**
 * Zenpage pages admin functions
 * 
 * @since 1.7 separated from zenpage-admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/**
 * Updates or adds a page and returns the object of that page
 *
 * @param array $reports report display
 * @param bool $newpage true if it is a new page
 *
 * @return object
 */
function updatePage(&$reports, $newpage = false) {
	global $_zp_zenpage, $_zp_current_admin_obj, $_zp_db;
	$title = process_language_string_save("title", 2);
	$author = sanitize($_POST['author']);
	$content = updateImageProcessorLink(process_language_string_save("content", EDITOR_SANITIZE_LEVEL));
	$extracontent = updateImageProcessorLink(process_language_string_save("extracontent", EDITOR_SANITIZE_LEVEL));
	$custom = process_language_string_save("custom_data", 1);
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$expiredate = getExpiryDatePost();
	$commentson = getcheckboxState('commentson');
	$permalink = getcheckboxState('permalink');
	if (zp_loggedin(CODEBLOCK_RIGHTS)) {
		$codeblock = processCodeblockSave(0);
	}
	$locked = getcheckboxState('locked');
	if ($newpage) {
		$titlelink = createTitlelink($title, $date);
		if(getOption('zenpage_titlelinkdate_pages')) {
			$titlelink = addDateToTitlelink($titlelink);
		}
		$duplicate = checkTitlelinkDuplicate($titlelink, 'page');
		if ($duplicate) {
			//already exists
			$titlelink = addDateToTitlelink($titlelink);
			$reports[] = "<p class='warningbox fade-message'>" . gettext('Duplicate page title') . '</p>';
		}
		$oldtitlelink = $titlelink;
	} else {
		$titlelink = $oldtitlelink = sanitize($_POST['titlelink-old']);
	}
	if (getcheckboxState('edittitlelink')) {
		$titlelink = sanitize($_POST['titlelink'], 3);
		if (empty($titlelink)) {
			$titlelink = createTitlelink($title, $date);
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
		$rslt = $_zp_db->query('UPDATE ' . $_zp_db->prefix('pages') . ' SET `titlelink`=' . $_zp_db->quote($titlelink) . ' WHERE `id`=' . $id, false);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		} else {
			SearchEngine::clearSearchCache();
		}
	}
	// update page
	$page = new ZenpagePage($titlelink, true);

	$notice = processCredentials($page);
	$page->setTitle($title);
	$page->setContent($content);
	$page->setExtracontent($extracontent);
	$page->setCustomData(zp_apply_filter('save_page_custom_data', $custom, $page));
	$page->setPublished($show);
	$page->setDateTime($date);
	$page->setLastChange($date);
	$page->setCommentsAllowed($commentson);
	if (zp_loggedin(CODEBLOCK_RIGHTS)) {
		$page->setCodeblock($codeblock);
	}
	$page->setAuthor($author);
	$page->setPermalink($permalink);
	$page->setLocked($locked);
	$page->setExpiredate($expiredate);
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
		$page->setDefaultSortorder();
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
		$page->setLastchangeUser($_zp_current_admin_obj->getLoginName());
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
	$checkupdates = true;
	if ($newpage) { 
		$checkupdates = false;
	}
	$page->save($checkupdates);
	if ($msg) {
		$reports[] = $msg;
	}
	return $page;
}

/**
 * Deletes a page (and also if existing its subpages) from the database
 *
 */
function deletePage($titlelink) {
	if (is_object($titlelink)) {
		$obj = $titlelink;
	} else {
		$obj = new ZenpagePage($titlelink);
	}
	$result = $obj->remove();
	if ($result) {
		if (is_object($titlelink)) {
			redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-pages.php?deleted');
		}
		SearchEngine::clearSearchCache();
		return "<p class='messagebox fade-message'>" . gettext("Page successfully deleted!") . "</p>";
	}
	return "<p class='errorbox fade-message'>" . gettext("Page delete failed!") . "</p>";
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
			if (checkIfLockedPage($page)) {
				echo "<a href='admin-edit.php?page&amp;titlelink=" . urlencode($page->getName()) . "'> ";
				checkForEmptyTitle($page->getTitle(), "page");
				echo "</a>" . checkHitcounterDisplay($page->getHitcounter());
			} else {
				checkForEmptyTitle($page->getTitle(), "page");
				checkHitcounterDisplay($page->isPublished());
			}
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
				<?php printProtectedIcon($page); ?>
			</div>

			<?php if (checkIfLockedPage($page)) { ?>
				<div class="page-list_icon">
					<?php printPublishIconLink($page, "page"); ?>
				</div>
				<?php if(extensionEnabled('comment_form')) { ?>
					<div class="page-list_icon">
						<?php
							if ($page->getCommentsAllowed()) {
								?>
								<a href="?commentson=0&amp;titlelink=<?php echo html_encode($page->getName()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Disable comments'); ?>">
									<img src="../../images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
								</a>
								<?php
							} else {
								?>
								<a href="?commentson=1&amp;titlelink=<?php echo html_encode($page->getName()); ?>&amp;XSRFToken=<?php echo getXSRFToken('update') ?>" title="<?php echo gettext('Enable comments'); ?>">
									<img src="../../images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
								</a>
								<?php
							}
						?>
					</div>
				<?php } ?>
			<?php } else { ?>
				<div class="page-list_icon">
					<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
			<?php } ?>

			<div class="page-list_icon">
				<a href="../../../index.php?p=pages&amp;title=<?php echo js_encode($page->getName()); ?>" title="<?php echo gettext("View page"); ?>">
					<img src="images/view.png" alt="" title="<?php echo gettext("view"); ?>" />
				</a>
			</div>

			<?php
			if (checkIfLockedPage($page)) {
				if (extensionEnabled('hitcounter')) {
					?>
					<div class="page-list_icon">
						<a href="?hitcounter=1&amp;titlelink=<?php echo html_encode($page->getName()); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo gettext("Reset hitcounter"); ?>">
							<img src="../../images/reset.png" alt="" title="<?php echo gettext("Reset hitcounter"); ?>" /></a>
					</div>
					<?php
				}
				?>
				<div class="page-list_icon">
					<a href="javascript:confirmDelete('admin-pages.php?delete=<?php echo $page->getName(); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>',deletePage)" title="<?php echo gettext("Delete page"); ?>">
						<img src="../../images/fail.png" alt="" title="<?php echo gettext("delete"); ?>" /></a>
				</div>
				<div class="page-list_icon">
					<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $page->getName(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
				</div>
			<?php } else { ?>
				<div class="page-list_icon">
					<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
				<div class="page-list_icon">
					<img src="../../images/icon_inactive.png" alt="" title="<?php gettext('locked'); ?>" />
				</div>
			<?php } ?>
		</div><!--  icon wrapper end -->
	</div>
	<?php
}
