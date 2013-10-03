<?php
/* * *****************************
 * Menu manager admin functions
 * ***************************** */

/**
 * Updates the sortorder of the pages list in the database
 *
 */
function updateItemsSortorder() {
	if (empty($_POST['order'])) { // if someone didn't sort anything there are no values!
		return '<p class="notebox fade-message">' . gettext('Nothing changed') . '</p>';
	} else {
		$order = processOrder($_POST['order']);
		$parents = array('NULL');
		foreach ($order as $id => $orderlist) {
			// fix the parent ID and update the DB record
			$sortstring = implode('-', $orderlist);
			$level = count($orderlist);
			$parents[$level] = $id;
			$myparent = $parents[$level - 1];
			$sql = "UPDATE " . prefix('menu') . " SET `sort_order` = " . db_quote($sortstring) . ", `parentid`= " . db_quote($myparent) . " WHERE `id`=" . sanitize_numeric($id);
			query($sql);
		}
		return "<p class='messagebox fade-message'>" . gettext("Sort order saved.") . "</p>";
	}
}

/**
 * Prints the table part of a single page item for the sortable pages list
 *
 * @param object $page The array containing the single page
 * @param bool $flag set to true to flag the element as having a problem with nesting level
 */
function printItemsListTable($item, $flag) {
	global $_zp_gallery;

	$array = getItemTitleAndURL($item);
	if ($array['valid']) {
		switch ($item['type']) {
			case "album":
				$link = '<a href="../../admin-edit.php?page=edit&amp;album=' . html_encode($item['link']) . '">' . html_encode(truncate_string($item['link'], 40, '...')) . '</a>';
				break;
			case "zenpagepage":
				$link = '<a href="../zenpage/admin-edit.php?page&amp;titlelink=' . html_encode($item['link']) . '">' . html_encode(truncate_string($item['link'], 40, '...')) . '</a>';
				break;
			case "zenpagecategory":
				$link = '<a href="../zenpage/admin-edit.php?newscategory&amp;titlelink=' . html_encode($item['link']) . '">' . html_encode(truncate_string($item['link'], 40, '...')) . '</a>';
				break;
			case 'customlink':
				$link = '<a href="' . html_encode($item['link']) . '">' . html_encode(truncate_string($item['link'], 40, '...')) . '</a>';
				break;
			case 'menulabel':
				$link = '';
				break;
			default:
				$link = html_encode(truncate_string($item['link'], 40, '...'));
				break;
		}
	} else {
		$link = '<span class="notebox">' . sprintf(gettext('Target does not exists in <em>%1$s</em> theme'), $array['theme']) . '</span>';
	}
	?>
	<div class="page-list_row">
		<div class="page-list_title">
			<?php
			printItemEditLink($item);
			?>
		</div>
		<div class="page-list_extra"><em><?php echo $item['type']; ?></em></div>
		<div class="page-list_extra"><?php echo $link; ?></div>
		<div class="page-list_iconwrapper">
			<div class="page-list_icon">
				<?php
				if ($array['protected']) {
					?>
					<img src="../../images/lock_2.png" alt="<?php echo gettext("The object of this menu is under password protection"); ?>" title="<?php echo gettext("The object of this menu is under password protection"); ?>" style="border: 0px;" />
					<?php
				} else {
					?>
					<img src="../../images/place_holder_icon.png" alt="<?php echo gettext("under password protection"); ?>" style="border: 0px;" />
					<?php
				}
				?>
			</div>
			<div class="page-list_icon">
				<?php
				if ($item['show'] === '1') {
					?>
					<a href="menu_tab.php?publish&amp;id=<?php echo $item['id'] . "&amp;show=0"; ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('update_menu') ?>" title="<?php echo gettext('hide'); ?>" >
						<img src="../../images/pass.png"	alt="<?php echo gettext('hide'); ?>" style="border: 0px;" />
					</a>
					<?php
				} else {
					?>
					<a href="menu_tab.php?publish&amp;id=<?php echo $item['id'] . "&amp;show=1"; ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('update_menu') ?>"  title="<?php echo gettext('show'); ?>">
						<img src="../../images/action.png"	alt="<?php echo gettext('show'); ?>" style="border: 0px;" />
					</a>
					<?php
				}
				?>
			</div>
			<div class="page-list_icon">
				<?php
				switch ($item['type']) {
					default:
						if (!empty($array['url'])) {
							?>
							<a href="<?php echo $array['url']; ?>">
								<img src="../../images/view.png" alt="<?php echo gettext('view'); ?>" title="<?php echo gettext('view'); ?>" style="border: 0px;" />
							</a>
							<?php
							break;
						}
					case 'menulabel':
					case 'menufunction':
					case 'html':
						?>
						<img src="../../images/icon_inactive.png" alt="" style="border: 0px;" />
						<?php
						break;
				}
				?>
			</div>
			<div class="page-list_icon">
				<a href="javascript:deleteMenuItem('<?php echo $item['id']; ?>','<?php printf(gettext('Ok to delete %s? This cannot be undone.'), html_encode($array['name'])); ?>');" >
					<img src="../../images/fail.png" alt="<?php echo gettext('delete'); ?>" title="<?php echo gettext('delete'); ?>" style="border: 0px;" />
				</a>
			</div>
			<div class="page-list_icon">
				<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo html_encode($item['id']); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
			</div>
		</div>
	</div>
	<?php
}

/**
 * Prints the sortable pages list
 * returns true if nesting levels exceede the database container
 *
 * @param array $pages The array containing all pages
 *
 * @return bool
 */
function printItemsList($items) {
	$indent = 1;
	$open = array(1 => 0);
	$rslt = false;
	foreach ($items as $item) {
		$order = explode('-', $item['sort_order']);
		$level = max(1, count($order));
		if ($toodeep = $level > 1 && $order[$level - 1] === '') {
			$rslt = true;
		}
		if ($level > $indent) {
			echo "\n" . str_pad("\t", $indent, "\t") . "<ul class=\"page-list\">\n";
			$indent++;
			$open[$indent] = 0;
		} else if ($level < $indent) {
			while ($indent > $level) {
				$open[$indent]--;
				$indent--;
				echo "</li>\n" . str_pad("\t", $indent, "\t") . "</ul>\n";
			}
		} else { // indent == level
			if ($open[$indent]) {
				echo str_pad("\t", $indent, "\t") . "</li>\n";
				$open[$indent]--;
			} else {
				echo "\n";
			}
		}
		if ($open[$indent]) {
			echo str_pad("\t", $indent, "\t") . "</li>\n";
			$open[$indent]--;
		}
		echo str_pad("\t", $indent - 1, "\t") . "<li id=\"id_" . $item['id'] . "\">";
		echo printItemsListTable($item, $toodeep);
		$open[$indent]++;
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent]--;
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
 * Prints the link to the edit page of a menu item. For gallery and Zenpage items it links to their normal edit pages, for custom pages and custom links to menu specific edit page.
 *
 * @param array $item Array of the menu item
 */
function printItemEditLink($item) {
	$link = "";
	$array = getItemTitleAndURL($item);
	$title = html_encode($array['title']);
	$link = '<a href="menu_tab_edit.php?edit&amp;id=' . $item['id'] . "&amp;type=" . $item['type'] . "&amp;menuset=" . html_encode(checkChosenMenuset()) . '">' . $title . '</a>';
	echo $link;
}

/**
 * Prints the item status selector to choose if all items or only hidden or visible should be listed
 *
 */
function printItemStatusDropdown() {
	$all = "";
	$visible = "";
	$hidden = "";
	$status = checkChosenItemStatus();
	$menuset = checkChosenMenuset();
	?>
	<select name="ListBoxURL" id="ListBoxURL" size="1" onchange="window.location = '?menuset=<?php echo urlencode($menuset); ?>&amp;visible=' + $('#ListBoxURL').val()">
		<?php
		switch ($status) {
			case "hidden":
				$hidden = 'selected="selected"';
				break;
			case "visible":
				$visible = 'selected="selected"';
				break;
			default:
				$all = 'selected="selected"';
				break;
		}
		echo "<option $all value='all'>" . gettext("Hidden and visible items") . "</option>\n";
		echo "<option $visible value='visible'>" . gettext("Visible items") . "</option>\n";
		echo "<option $hidden value='hidden'>" . gettext("hidden items") . "</option>\n";
		?>
	</select>
	<?php
}

/**
 * returns the menu set selector
 * @param string $active true if changing the selection shuld reload to the new selection
 *
 */
function getMenuSetSelector($active) {
	$menuset = checkChosenMenuset();
	$menusets = array($menuset => $menuset);
	$result = query_full_array("SELECT DISTINCT menuset FROM " . prefix('menu') . " ORDER BY menuset");
	if ($result) {
		foreach ($result as $set) {
			$menusets[$set['menuset']] = $set['menuset'];
		}
		natcasesort($menusets);
	} else {
		return NULL;
	}

	if ($active) {
		$selector = '<select name="menuset" id="menuset" size="1" onchange="window.location=\'?menuset=\'+encodeURIComponent($(\'#menuset\').val())">' . "\n";
	} else {
		$selector = '<select name="menuset" size="1">' . "\n";
	}
	foreach ($menusets as $set) {
		if ($menuset == $set) {
			$selected = 'selected="selected"';
		} else {
			$selected = '';
		}
		$selector .= '<option ' . $selected . ' value="' . html_encode($set) . '">' . html_encode($set) . "</option>\n";
	}
	$selector .= "</select>\n";
	return $selector;
}

/**
 * Sets a menu item to published/visible
 *
 * @param integer $id id of the item
 * @param string $show published status.
 * @param string $menuset chosen menu set
 */
function publishItem($id, $show, $menuset) {
	query("UPDATE " . prefix('menu') . " SET `show` = '" . $show . "' WHERE id = " . $id, true . " AND menuset = " . db_quote($menuset));
}

/**
 * adds (sub)albums to menu base with their gallery sorting order intact
 *
 * @param string $menuset chosen menu set
 * @param int $id table id of the parent.
 * @param string $link folder name of the album
 * @param string $sort xxx-xxx-xxx style sort order for album
 */
function addSubalbumMenus($menuset, $id, $link, $sort) {
	$album = newAlbum($link);
	$show = $album->getShow();
	$title = $album->getTitle();
	$sql = "INSERT INTO " . prefix('menu') . " (`link`,`type`,`title`,`show`,`menuset`,`sort_order`, `parentid`) " .
					'VALUES (' . db_quote($link) . ', "album",' . db_quote($album->name) . ', ' . $show . ',' . db_quote($menuset) . ',' . db_quote($sort) . ',' . $id . ')';
	$result = query($sql, false);
	if ($result) {
		$id = db_insert_id();
	} else {
		$result = query_single_row('SELECT `id` FROM' . prefix('menu') . ' WHERE `type`="album" AND `link`=' . db_quote($link));
		$id = $result['id'];
	}
	if (!$album->isDynamic()) {
		$albums = $album->getAlbums();
		foreach ($albums as $key => $link) {
			addSubalbumMenus($menuset, $id, $link, $sort . '-' . sprintf('%03u', $key));
		}
	}
}

/**
 * Adds albums to the menu set. Returns the next sort order base
 * @param string $menuset current menu set
 * @param string $base starting "sort order"
 * @return int
 */
function addalbumsToDatabase($menuset, $base = NULL) {
	global $_zp_gallery;
	if (is_null($base)) {
		$albumbase = db_count('menu', 'WHERE menuset=' . db_quote($menuset));
		$sortbase = '';
	} else {
		$albumbase = array_pop($base);
		$sortbase = '';
		for ($i = 0; $i < count($base); $i++) {
			$sortbase .= sprintf('%03u', $base[$i]) . '-';
		}
	}
	$result = $albumbase;
	$albums = $_zp_gallery->getAlbums();
	foreach ($albums as $key => $link) {
		addSubalbumMenus($menuset, 'NULL', $link, $sortbase . sprintf('%03u', $result = $key + $albumbase));
	}
	return $result;
}

/**
 * Adds Zenpage pages to the menu set
 * @param string $menuset current menu set
 * @param int $pagebase starting "sort order"
 * @return int
 */
function addPagesToDatabase($menuset, $base = NULL) {
	if (is_null($base)) {
		$pagebase = db_count('menu', 'WHERE menuset=' . db_quote($menuset));
		$sortbase = '';
	} else {
		$pagebase = array_pop($base);
		$sortbase = '';
		for ($i = 0; $i < count($base); $i++) {
			$sortbase .= sprintf('%03u', $base[$i]) . '-';
		}
	}
	$result = $pagebase;
	$parents = array('NULL');
	$result = query_full_array("SELECT * FROM " . prefix('pages') . " ORDER BY sort_order");
	foreach ($result as $key => $item) {
		$sorts = explode('-', $item['sort_order']);
		$level = count($sorts);
		$sorts[0] = sprintf('%03u', $result = $sorts[0] + $pagebase);
		$order = $sortbase . implode('-', $sorts);
		$show = $item['show'];
		$link = $item['titlelink'];
		$parent = $parents[$level - 1];
		$sql = "INSERT INTO " . prefix('menu') . " (`title`, `link`, `type`, `show`,`menuset`,`sort_order`, `parentid`) " .
						'VALUES (' . db_quote($item['title']) . ',' . db_quote($link) . ',"zenpagepage",' . $show . ',' . db_quote($menuset) . ',' . db_quote($order) . ',' . $parent . ')';
		if (query($sql, false)) {
			$id = db_insert_id();
		} else {
			$rslt = query_single_row('SELECT `id` FROM' . prefix('menu') . ' WHERE `type`="zenpagepage" AND `link`="' . $link . '"');
			$id = $rslt['id'];
		}
		$parents[$level] = $id;
	}
	return $result;
}

/**
 * Adds Zenpage news categories to the menu set
 * @param string $menuset chosen menu set
 */
function addCategoriesToDatabase($menuset, $base = NULL) {
	if (is_null($base)) {
		$categorybase = db_count('menu', 'WHERE menuset=' . db_quote($menuset));
		$sortbase = '';
	} else {
		$categorybase = array_pop($base);
		$sortbase = '';
		for ($i = 0; $i < count($base); $i++) {
			$sortbase .= sprintf('%03u', $base[$i]) . '-';
		}
	}
	$result = $categorybase;
	$parents = array('NULL');
	$result = query_full_array("SELECT * FROM " . prefix('news_categories') . " ORDER BY sort_order");
	foreach ($result as $key => $item) {
		$sorts = explode('-', $item['sort_order']);
		$level = count($sorts);
		$sorts[0] = sprintf('%03u', $result = $sorts[0] + $categorybase);
		$order = $sortbase . implode('-', $sorts);
		$link = $item['titlelink'];
		$parent = $parents[$level - 1];
		$sql = "INSERT INTO " . prefix('menu') . " (`title`, `link`, `type`, `show`,`menuset`,`sort_order`,`parentid`) " .
						'VALUES (' . db_quote($item['title']) . ',' . db_quote($link) . ',"zenpagecategory", 1,' . db_quote($menuset) . ',' . db_quote($order) . ',' . $parent . ')';
		if (query($sql, false)) {
			$id = db_insert_id();
		} else {
			$rslt = query_single_row('SELECT `id` FROM' . prefix('menu') . ' WHERE `type`="zenpagecategory" AND `link`="' . $link . '"');
			$id = $rslt['id'];
		}
		$parents[$level] = $id;
	}
	return $result;
}

/* * ******************************************************************
 * FUNCTIONS FOR THE SELECTORS ON THE "ADD MENU ITEM" Page
 * ******************************************************************* */

/**
 * Adds an menu item set via POST
 *
 * @return array
 */
function addItem(&$reports) {
	$menuset = checkChosenMenuset();
	$result['type'] = sanitize($_POST['type']);
	$result['show'] = getCheckboxState('show');
	$result['include_li'] = getCheckboxState('include_li');
	$result['id'] = 0;
	if (getCheckboxState('span')) {
		$result['span_id'] = sanitize($_POST['span_id']);
		$result['span_class'] = sanitize($_POST['span_class']);
	} else {
		$result['span_id'] = '';
		$result['span_class'] = '';
	}
	switch ($result['type']) {
		case 'all_items':
			query("INSERT INTO " . prefix('menu') . " (`title`,`link`,`type`,`show`,`menuset`,`sort_order`) " .
							"VALUES ('" . gettext('Home') . "', '" . WEBPATH . '/' . "','galleryindex','1'," . db_quote($menuset) . ",'000')", true);
			addAlbumsToDatabase($menuset);
			if (extensionEnabled('zenpage')) {
				query("INSERT INTO " . prefix('menu') . " (`title`,`link`,`type`,`show`,`menuset`,`sort_order`) " .
								"VALUES ('" . gettext('News index') . "', '" . rewrite_path(_NEWS_, '?p=news') . "','zenpagenewsindex','1'," . db_quote($menuset) . ",'001')", true);
				addPagesToDatabase($menuset);
				addCategoriesToDatabase($menuset);
			}
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Menu items for all Zenphoto objects added.") . "</p>";
			return NULL;
		case 'all_albums':
			addAlbumsToDatabase($menuset);
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Menu items for all albums added.") . "</p>";
			return NULL;
		case 'all_zenpagepages':
			addPagesToDatabase($menuset);
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Menu items for all Zenpage pages added.") . "</p>";
			return NULL;
		case 'all_zenpagecategorys':
			addCategoriesToDatabase($menuset);
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Menu items for all Zenpage categories added.") . "</p>";
			return NULL;
		case 'album':
			$result['title'] = $result['link'] = sanitize($_POST['albumselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to select an album.") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Album menu item <em>%s</em> added"), $result['link']);
			break;
		case 'galleryindex':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Gallery index menu item <em>%s</em> added"), $result['link']);
			break;
		case 'zenpagepage':
			$result['title'] = NULL;
			$result['link'] = sanitize($_POST['pageselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>link</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Zenpage page menu item <em>%s</em> added"), $result['link']);
			break;
		case 'zenpagenewsindex':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Zenpage news index menu item <em>%s</em> added"), $result['link']);
			break;
		case 'zenpagecategory':
			$result['title'] = NULL;
			$result['link'] = sanitize($_POST['categoryselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>link</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Zenpage news category menu item <em>%s</em> added"), $result['link']);
			break;
		case 'custompage':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = sanitize($_POST['custompageselect']);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Custom page menu item <em>%s</em> added"), $result['link']);
			break;
		case 'customlink':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Custom page menu item <em>%s</em> added"), $result['link']);
			break;
		case 'menulabel':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$successmsg = gettext("Custom label added");
			break;
		case 'menufunction':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link'], 0);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			$successmsg = sprintf(gettext("Function  menu item <em>%s</em> added"), $result['link']);
			break;
		case 'html':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link'], 0);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			$successmsg = gettext("<em>HTML</em> added");
			break;
		default:
			break;
	}
	$count = db_count('menu', 'WHERE menuset=' . db_quote($menuset));
	$order = sprintf('%03u', $count);
	$sql = "INSERT INTO " . prefix('menu') . " (`title`,`link`,`type`,`show`,`menuset`,`sort_order`,`include_li`,`span_id`,`span_class`) " .
					"VALUES (" . db_quote($result['title']) .
					"," . db_quote($result['link']) .
					"," . db_quote($result['type']) . "," . $result['show'] .
					"," . db_quote($menuset) . "," . db_quote($order) . "," . $result['include_li'] .
					"," . db_quote($result['span_id']) . "," . db_quote($result['span_class']) .
					")";
	if (query($sql, true)) {
		$reports[] = "<p class='messagebox fade-message'>" . $successmsg . "</p>";
		//echo "<pre>"; print_r($result); echo "</pre>";
		$result['id'] = db_insert_id();
		return $result;
	} else {
		if (empty($result['link'])) {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext('A <em>%1$s</em> item already exists in <em>%2$s</em>!'), $result['type'], $menuset) . "</p>";
		} else {
			$reports[] = "<p class='errorbox fade-message'>" . sprintf(gettext('A <em>%1$s</em> item with the link <em>%2$s</em> already exists in <em>%3$s</em>!'), $result['type'], $result['link'], $menuset) . "</p>";
		}
		return NULL;
	}
}

/**
 * Updates a menu item (custom link, custom page only) set via POST
 *
 */
function updateMenuItem(&$reports) {
	$menuset = checkChosenMenuset();
	$result = array();
	$result['id'] = sanitize($_POST['id']);
	$result['show'] = getCheckboxState('show');
	$result['type'] = sanitize($_POST['type']);
	$result['title'] = process_language_string_save("title", 2);
	$result['include_li'] = getCheckboxState('include_li');
	if (getCheckboxState('span')) {
		$result['span_id'] = sanitize($_POST['span_id']);
		$result['span_class'] = sanitize($_POST['span_class']);
	} else {
		$result['span_id'] = '';
		$result['span_class'] = '';
	}
	switch ($result['type']) {
		case 'album':
			$result['title'] = $result['link'] = sanitize($_POST['albumselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to select an album.") . "</p>";
				return $result;
			}
			break;
		case 'galleryindex':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'zenpagepage':
			$result['title'] = NULL;
			$result['link'] = sanitize($_POST['pageselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>link</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'zenpagenewsindex':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'zenpagecategory':
			$result['title'] = NULL;
			$result['link'] = sanitize($_POST['categoryselect']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>link</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'custompage':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = sanitize($_POST['custompageselect']);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'customlink':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link']);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'menulabel':
			$result['title'] = process_language_string_save("title", 2);
			$result['link'] = NULL;
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'menufunction':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link'], 0);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			break;
		case 'html':
			$result['title'] = process_language_string_save("title", 2);
			if (empty($result['title'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
				return $result;
			}
			$result['link'] = sanitize($_POST['link'], 0);
			if (empty($result['link'])) {
				$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to provide a <strong>function</strong>!") . "</p>";
				return $result;
			}
			break;
		default:
			$result['link'] = sanitize($_POST['link'], 0);
			break;
	}
	// update the category in the category table
	$sql = "UPDATE " . prefix('menu') . " SET title = " . db_quote($result['title']) .
					",link=" . db_quote($result['link']) .
					",type=" . db_quote($result['type']) . ", `show`=" . db_quote($result['show']) .
					",menuset=" . db_quote($menuset) . ",include_li=" . $result['include_li'] .
					",span_id=" . db_quote($result['span_id']) . ",span_class=" . db_quote($result['span_class']) .
					" WHERE `id`=" . $result['id'];
	if (query($sql)) {
		if (isset($_POST['title']) && empty($result['title'])) {
			$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>title</strong>!") . "</p>";
		} else if (isset($_POST['link']) && empty($result['link'])) {
			$reports[] = "<p class='errorbox fade-message'>" . gettext("You forgot to give your menu item a <strong>link</strong>!") . "</p>";
		} else {
			$reports[] = "<p class='messagebox fade-message'>" . gettext("Menu item updated!") . "</p>";
		}
	}
	return $result;
}

/**
 * Deletes a menu item set via GET
 *
 */
function deleteItem(&$reports) {
	if (isset($_GET['delete'])) {
		$delete = sanitize_numeric($_GET['delete'], 3);
		query("DELETE FROM " . prefix('menu') . " WHERE `id`=$delete");
		$reports[] = "<p class='messagebox fade-message'>" . gettext("Custom menu item successfully deleted!") . "</p>";
	}
}

/**
 * Prints all albums of the Zenphoto gallery as a partial drop down menu (<option></option> parts).
 *
 * @param string $current set to the album name selected (if any)
 *
 * @return string
 */
function printAlbumsSelector($current) {
	global $_zp_gallery;
	$albumlist = array();
	genAlbumList($albumlist, NULL, ALL_ALBUMS_RIGHTS);
	?>
	<select id="albumselector" name="albumselect">
		<?php
		foreach ($albumlist as $key => $value) {
			$albumobj = newAlbum($key);
			$albumname = $albumobj->name;
			if ($albumname == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$level = substr_count($albumname, "/");
			$arrow = "";
			for ($count = 1; $count <= $level; $count++) {
				$arrow .= "» ";
			}
			echo "<option value='" . html_encode($albumobj->name) . "'" . $selected . '>';
			echo $arrow . $albumobj->getTitle() . unpublishedZenphotoItemCheck($albumobj) . "</option>";
		}
		?>
	</select>
	<?php
}

/**
 * Prints all available pages in Zenpage
 *
 * @param string $current set to the page selected (if any)
 *
 * @return string
 */
function printZenpagePagesSelector($current) {
	global $_zp_gallery, $_zp_zenpage;
	?>
	<select id="pageselector" name="pageselect">
		<?php
		$pages = $_zp_zenpage->getPages(false);
		foreach ($pages as $key => $page) {
			if ($page['titlelink'] == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$pageobj = new ZenpagePage($page['titlelink']);
			$level = substr_count($pageobj->getSortOrder(), "-");
			$arrow = "";
			for ($count = 1; $count <= $level; $count++) {
				$arrow .= "» ";
			}
			echo "<option value='" . html_encode($pageobj->getTitlelink()) . "'" . $selected . '>';
			echo $arrow . $pageobj->getTitle() . unpublishedZenphotoItemCheck($pageobj) . "</option>";
		}
		?>
	</select>
	<?php
}

/**
 * Prints all available articles or categories in Zenpage
 *
 * @param string $current set to category selected (if any)
 *
 * @return string
 */
function printZenpageNewsCategorySelector($current) {
	global $_zp_gallery, $_zp_zenpage;
	?>
	<select id="categoryselector" name="categoryselect">
		<?php
		$cats = $_zp_zenpage->getAllCategories(false);
		foreach ($cats as $cat) {
			if ($cat['titlelink'] == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$catobj = new ZenpageCategory($cat['titlelink']);
			//This is much easier than hacking the nested list function to work with this
			$getparents = $catobj->getParents();
			$levelmark = '';
			foreach ($getparents as $parent) {
				$levelmark .= '» ';
			}
			echo "<option value='" . html_encode($catobj->getTitlelink()) . "'" . $selected . '>';
			echo $levelmark . $catobj->getTitle() . "</option>";
		}
		?>
	</select>
	<?php
}

/**
 * Prints the selector for custom pages
 *
 * @return string
 */
function printCustomPageSelector($current) {
	global $_zp_gallery;
	?>
	<select id="custompageselector" name="custompageselect">
		<?php
		$curdir = getcwd();
		$themename = $_zp_gallery->getCurrentTheme();
		$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $themename . '/';
		chdir($root);
		$filelist = safe_glob('*.php');
		$list = array();
		foreach ($filelist as $file) {
			$file = filesystemToInternal($file);
			$list[$file] = str_replace('.php', '', $file);
		}
		generateListFromArray(array($current), $list, false, true);
		chdir($curdir);
		?>
	</select>
	<?php
}

/**
 * checks if a album or image is un-published and returns a '*'
 *
 * @return string
 */
function unpublishedZenphotoItemCheck($obj, $dropdown = true) {
	if ($obj->getShow() != "1") {
		$show = "*";
	} else {
		$show = "";
	}
	return $show;
}

/**
 * Processes the check box bulk actions
 *
 */
function processMenuBulkActions() {
	$report = NULL;
	if (isset($_POST['ids'])) {
		$action = sanitize($_POST['checkallaction']);
		$ids = $_POST['ids'];
		$total = count($ids);
		$message = NULL;
		if ($action != 'noaction') {
			if ($total > 0) {
				$n = 0;
				switch ($action) {
					case 'deleteall':
						$sql = "DELETE FROM " . prefix('menu') . " WHERE ";
						$message = gettext('Selected items deleted');
						break;
					case 'showall':
						$sql = "UPDATE " . prefix('menu') . " SET `show` = 1 WHERE ";
						$message = gettext('Selected items published');
						break;
					case 'hideall':
						$sql = "UPDATE " . prefix('menu') . " SET `show` = 0 WHERE ";
						$message = gettext('Selected items unpublished');
						break;
				}
				foreach ($ids as $id) {
					$n++;
					$sql .= " id='" . sanitize_numeric($id) . "' ";
					if ($n < $total)
						$sql .= "OR ";
				}
				query($sql);
			}
			if (!is_null($message))
				$report = "<p class='messagebox fade-message'>" . $message . "</p>";
		}
	}
	return $report;
}
?>
