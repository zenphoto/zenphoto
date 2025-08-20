<?php 
/**
 * General admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

	/**
	 * Encodes for use as a $_POST index
	 *
	 * @param string $str
	 */
	function postIndexEncode($str) {
		return strtr(urlencode($str), array('.' => '__2E__', '+' => '__20__', '%' => '__25__', '&' => '__26__', "'" => '__27__', '(' => '__28__', ')' => '__29__'));
	}

	/**
	 * Decodes encoded $_POST index
	 *
	 * @param string $str
	 * @return string
	 */
	function postIndexDecode($str) {
		return urldecode(strtr($str, array('__2E__' => '.', '__20__' => '+', '__25__' => '%', '__26__' => '&', '__27__' => "'", '__28__' => '(', '__29__' => ')')));
	}

	
	/**
	 * Prints radio buttons from an array
	 *
	 * @param string $currentvalue The current selected value
	 * @param string $list the array of the list items form is localtext => buttonvalue
	 * @param string $option the name of the option for the input field name
	 * @param bool $behind set true to have the "text" before the button
	 */
	function generateRadiobuttonsFromArray($currentvalue, $list, $option, $behind = false, $class = 'checkboxlabel', $disabled = NULL) {
		foreach ($list as $text => $value) {
			$checked = "";
			if ($value == $currentvalue) {
				$checked = ' checked="checked" '; //the checked() function uses quotes the other way round...
			}
			?>
			<label<?php if ($class) echo ' class="' . $class . '"'; ?>>
				<?php if ($behind) echo $text; ?>
				<input type="radio" name="<?php echo $option; ?>" id="<?php echo $option . '-' . $value; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?><?php echo $disabled; ?> />
				<?php if (!$behind) echo $text; ?>
			</label>
			<?php
		}
	}

	/**
	 * Creates the body of an unordered list with checkbox label/input fields (scrollable sortables)
	 *
	 * @param array $currentValue list of items to be flagged as checked
	 * @param array $list the elements of the select list
	 * @param string $prefix prefix of the input item
	 * @param string $alterrights are the items changable.
	 * @param bool $sort true for sorted list
	 * @param string $class optional class for items
	 * @param bool $localize true if the list local key is text for the item
	 */
	function generateUnorderedListFromArray($currentValue, $list, $prefix, $alterrights, $sort, $localize, $class = NULL, $extra = NULL) {
		if (is_null($extra))
			$extra = array();
		if (!empty($class))
			$class = ' class="' . $class . '" ';
		if ($sort) {
			if ($localize) {
				$list = array_flip($list);
				sortArray($list);
				$list = array_flip($list);
			} else {
				sortArray($list);
			}
		}
		$cv = array_flip($currentValue);
		foreach ($list as $key => $item) {
			$listitem = postIndexEncode($prefix . $item);
			if ($localize) {
				$display = $key;
			} else {
				$display = $item;
			}
			?>
			<li id="<?php echo strtolower($listitem); ?>_element">
				<label class="displayinline">
					<input id="<?php echo strtolower($listitem); ?>"<?php echo $class; ?> name="<?php echo $listitem; ?>" type="checkbox"
					<?php
					if (isset($cv[$item])) {
						echo ' checked="checked"';
					}
					?> value="1" <?php echo $alterrights; ?> />
								 <?php echo html_encode($display); ?>
				</label>
				<?php
				if (array_key_exists($item, $extra)) {
					$unique = 0;
					foreach (array_reverse($extra[$item]) as $box) {
						if ($box['display']) {
							if (isset($box['disable'])) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = $alterrights;
							}
							if (isset($box['type'])) {
								$type = $box['type'];
								if ($type == 'radio')
									$unique++;
							} else {
								$type = 'checkbox';
							}
							?>
							<label class="displayinlineright">
								<input type="<?php echo $type; ?>" id="<?php echo strtolower($listitem) . '_' . $box['name'] . $unique; ?>"<?php echo $class; ?> name="<?php echo $listitem . '_' . $box['name']; ?>"
											 value="<?php echo html_encode($box['value']); ?>" <?php
					if ($box['checked']) {
						echo ' checked="checked"';
					}
							?>
											 <?php echo $disable; ?> /> <?php echo $box['display']; ?>
							</label>
							<?php
						} else {
							?>
							<input type="hidden" id="<?php echo strtolower($listitem . '_' . $box['name']); ?>" name="<?php echo $listitem . '_' . $box['name']; ?>"<?php echo $class; ?>
										 value="<?php echo html_encode($box['value']); ?>" />
										 <?php
									 }
								 }
							 }
							 ?>
			</li>
			<?php
		}
	}
	
	function adminPageNav($pagenum, $totalpages, $adminpage, $parms, $tab = '') {
		if (empty($parms)) {
			$url = '?';
		} else {
			$url = $parms . '&amp;';
		}
		echo '<ul class="pagelist"><li class="prev">';
		if ($pagenum > 1) {
			echo '<a href="' . $url . 'subpage=' . ($p = $pagenum - 1) . $tab . '" title="' . sprintf(gettext('page %u'), $p) . '">' . '&laquo; ' . gettext("Previous page") . '</a>';
		} else {
			echo '<span class="disabledlink">&laquo; ' . gettext("Previous page") . '</span>';
		}
		echo "</li>";
		$start = max(1, $pagenum - 7);
		$total = min($start + 15, $totalpages + 1);
		if ($start != 1) {
			echo "\n <li><a href=" . $url . 'subpage=' . ($p = max($start - 8, 1)) . $tab . ' title="' . sprintf(gettext('page %u'), $p) . '">. . .</a></li>';
		}
		for ($i = $start; $i < $total; $i++) {
			if ($i == $pagenum) {
				echo "<li class=\"current\">" . $i . '</li>';
			} else {
				echo '<li><a href="' . $url . 'subpage=' . $i . $tab . '" title="' . sprintf(gettext('page %u'), $i) . '">' . $i . '</a></li>';
			}
		}
		if ($i < $totalpages) {
			echo "\n <li><a href=" . $url . 'subpage=' . ($p = min($pagenum + 22, $totalpages + 1)) . $tab . ' title="' . sprintf(gettext('page %u'), $p) . '">. . .</a></li>';
		}
		echo "<li class=\"next\">";
		if ($pagenum < $totalpages) {
			echo '<a href="' . $url . 'subpage=' . ($p = $pagenum + 1) . $tab . '" title="' . sprintf(gettext('page %u'), $p) . '">' . gettext("Next page") . ' &raquo;' . '</a>';
		} else {
			echo '<span class="disabledlink">' . gettext("Next page") . ' &raquo;</span>';
		}
		echo '</li></ul>';
	}

	/**
	 * Generates an editable list of language strings
	 *
	 * @param string $dbstring either a serialized languag string array or a single string
	 * @param string $name the prefix for the label, id, and name tags
	 * @param bool $textbox set to true for a textbox rather than a text field
	 * @param string $locale optional locale of the translation desired
	 * @param string $edit optional class
	 * @param int $wide column size. true or false for the standard or short sizes. Or pass a column size
	 * @param string $ulclass set to the class for the UL element
	 * @param int $rows set to the number of rows to show.
	 */
	function print_language_string_list($dbstring, $name, $textbox = false, $locale = NULL, $edit = '', $wide = TEXT_INPUT_SIZE, $ulclass = 'language_string_list', $rows = 6) {
		global $_zp_active_languages, $_zp_current_locale;
		$dbstring = unTagURLs($dbstring);
		if (!empty($edit))
			$edit = ' class="' . $edit . '"';
		if (is_null($locale)) {
			$locale = getUserLocale();
		}
		$strings = getSerializedArray($dbstring);
		if (count($strings) == 1) {
			$keys = array_keys($strings);
			$lang = array_shift($keys);
			if (!is_string($lang)) {
				$strings = array($locale => array_shift($strings));
			}
		}
		$activelang = generateLanguageList();
		$inactivelang = array();
		$activelang_locales = array_values($activelang);
		foreach ($strings as $key => $content) {
			if (!in_array($key, $activelang_locales)) {
				$inactivelang[$key] = $content;
			}
		}

		if (getOption('multi_lingual') && !empty($activelang)) {
			if ($textbox) {
				if (strpos($wide, '%') === false) {
					$width = ' cols="' . $wide . '"';
				} else {
					$width = ' style="width:' . ((int) $wide - 1) . '%;"';
				}
			} else {
				if (strpos($wide, '%') === false) {
					$width = ' size="' . $wide . '"';
				} else {
					$width = ' style="width:' . ((int) $wide - 2) . '%;"';
				}
			}

			// put the language list in perferred order
			$preferred = array($_zp_current_locale);
			foreach (parseHttpAcceptLanguage() as $lang) {
				$preferred[] = str_replace('-', '_', $lang['fullcode']);
			}
			$preferred = array_unique($preferred);
			$emptylang = array();

			foreach ($preferred as $lang) {
				foreach ($activelang as $key => $active) {
					if ($active == $lang) {
						$emptylang[$active] = $key;
						unset($activelang[$key]);
						continue 2;
					}
				}
				if (strlen($lang) == 2) { //	"wild card language"
					foreach ($activelang as $key => $active) {
						if (substr($active, 0, 2) == $lang) {
							$emptylang[$active] = $key;
						}
					}
				}
			}
			foreach ($activelang as $key => $active) {
				$emptylang[$active] = $key;
			}

			if ($textbox) {
				$class = 'box';
			} else {
				$class = '';
			}
			echo '<ul class="' . $ulclass . $class . '"' . ">\n";
			$empty = true;

			foreach ($emptylang as $key => $lang) {
				if (isset($strings[$key])) {
					$string = $strings[$key];
					if (!empty($string)) {
						unset($emptylang[$key]);
						$empty = false;
						?>
						<li>
							<label for="<?php echo $name . '_' . $key; ?>"><?php echo $lang; ?></label>
							<?php
							if ($textbox) {
								echo "\n" . '<textarea name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($string) . '</textarea>';
							} else {
								echo '<br /><input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="text" value="' . html_encode($string) . '"' . $width . ' />';
							}
							?>
						</li>
						<?php
					}
				}
			}
			foreach ($emptylang as $key => $lang) {
				?>
				<li>
					<label for="<?php echo $name . '_' . $key; ?>"><?php echo $lang; ?></label>
					<?php
					if ($textbox) {
						echo "\n" . '<textarea name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '"></textarea>';
					} else {
						echo '<br /><input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="text" value=""' . $width . ' />';
					}
					?>
				</li>
				<?php
			}
			// print hidden lang content here so all is re-submitted and no meanwhile or accidentally inactive language content gets lost
			foreach ($inactivelang as $key => $content) {
				if ($key !== $locale) {
					if ($textbox) {
						echo "\n" . '<textarea class="textarea_hidden" name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($content) . '</textarea>';
					} else {
						echo '<br /><input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="hidden" value="' . html_encode($content) . '"' . $width . ' />';
					}
				}
			}
			echo "</ul>\n";
		} else {
			if ($textbox) {
				if (strpos($wide, '%') === false) {
					$width = ' cols="' . $wide . '"';
				} else {
					$width = ' style="width:' . $wide . ';"';
				}
			} else {
				if (strpos($wide, '%') === false) {
					$width = ' size="' . $wide . '"';
				} else {
					$width = ' style="width:' . $wide . ';"';
				}
			}
			if (empty($locale))
				$locale = 'en_US';
			if (isset($strings[$locale])) {
				$dbstring = $strings[$locale];
			} else {
				$dbstring = array_shift($strings);
			}
			if ($textbox) {
				echo '<textarea name="' . $name . '_' . $locale . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($dbstring) . '</textarea>';
			} else {
				echo '<input name="' . $name . '_' . $locale . '"' . $edit . ' type="text" value="' . html_encode($dbstring) . '"' . $width . ' />';
			}

			// print hidden lang content here so all is re-submitted and no meanwhile or accidentally inactive language content gets lost
			foreach ($strings as $key => $content) {
				if ($key !== $locale) {
					if ($textbox) {
						echo '<textarea class="textarea_hidden" name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($content) . ' </textarea>';
					} else {
						echo '<input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="hidden" value="' . html_encode($content) . '"' . $width . ' />';
					}
				}
			}
		}
	}

	/**
	 * process the post of a language string form
	 *
	 * @param string $name the prefix for the label, id, and name tags
	 * @param $sanitize_level the type of sanitization required
	 * @return string
	 */
	function process_language_string_save($name, $sanitize_level = 3) {
		$languages = generateLanguageList();
		$l = strlen($name) + 1;
		$strings = array();
		foreach ($_POST as $key => $value) {
			if ($value && preg_match('/^' . $name . '_[a-z]{2}_[A-Z]{2}$/', $key)) {
				$key = substr($key, $l);
				//if (in_array($key, $languages)) { // disabled as we want to keep even inactive lang content savely
				$strings[$key] = sanitize($value, $sanitize_level);
				//}
			}
		}
		switch (count($strings)) {
			case 0:
				if (isset($_POST[$name])) {
					return sanitize($_POST[$name], $sanitize_level);
				} else {
					return '';
				}
			default:
				return serialize($strings);
		}
	}
	
	/**
	 * Outputs the rights checkbox table for admin
	 *
	 * @param $id int record id for the save
	 * @param string $background background color
	 * @param string $alterrights are the items changable
	 * @param bit $rights rights of the admin
	 */
	function printAdminRightsTable($id, $background, $alterrights, $rights) {
		$rightslist = sortMultiArray(Authority::getRights(), array('set', 'value'));
		?>
		<div class="box-rights">
			<strong><?php echo gettext("Rights:"); ?></strong>
			<?php
			$element = 3;
			$activeset = false;
			foreach ($rightslist as $rightselement => $right) {
				if ($right['display']) {
					if (($right['set'] != gettext('Pages') && $right['set'] != gettext('News')) || extensionEnabled('zenpage')) {
						if ($activeset != $right['set']) {
							if ($activeset) {
								?>
							</fieldset>
							<?php
						}
						$activeset = $right['set'];
						?>
						<fieldset><legend><?php echo $activeset; ?></legend>
							<?php
						}
						?>
						<label title="<?php echo html_encode(get_language_string($right['hint'])); ?>">
							<input type="checkbox" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" class="user-<?php echo $id; ?>"
										 value="<?php echo $right['value']; ?>"<?php
				if ($rights & $right['value'])
					echo ' checked="checked"';
				echo $alterrights;
						?> /> <?php echo $right['name']; ?>
						</label>
						<?php
					} else {
						?>
						<input type="hidden" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" value="<?php echo $right['value']; ?>" />
						<?php
					}
				}
			}
			?>
		</fieldset>
	</div>
	<?php
}

/**
 * Creates the managed album table for Admin
 *
 * @param string $type the kind of list
 * @param array $objlist list of objects
 * @param string $alterrights are the items changable
 * @param object $userobj the user
 * @param int $prefix the admin row
 * @param string $kind user, group, or template
 * @param array $flat items to be flagged with an asterix
 */
function printManagedObjects($type, $objlist, $alterrights, $userobj, $prefix_id, $kind, $flag) {
	$rest = $extra = $extra2 = array();
	$rights = $userobj->getRights();
	$legend = '';
	switch ($type) {
		case 'albums':
			if ($rights & (MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$alterrights = ' disabled="disabled"';
			} else {
				$full = $userobj->getObjects();
				$cv = $extra = array();
				$icon_edit_album = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/options.png" class="icon-position-top3" alt="" title="' . gettext('edit rights') . '" />';
				$icon_view_image = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/action.png" class="icon-position-top3" alt="" title="' . gettext('view unpublished items') . '" />';
				$icon_upload = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/arrow_up.png" class="icon-position-top3"  alt="" title="' . gettext('upload rights') . '"/>';
				$icon_upload_disabled = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/arrow_up.png" class="icon-position-top3"  alt="" title="' . gettext('the album is dynamic') . '"/>';
				if (!empty($flag)) {
					$legend .= '* ' . gettext('Primary album') . ' ';
				}
				$legend .= $icon_edit_album . ' ' . gettext('edit') . ' ';
				if ($rights & UPLOAD_RIGHTS)
					$legend .= $icon_upload . ' ' . gettext('upload') . ' ';
				if (!($rights & VIEW_UNPUBLISHED_RIGHTS))
					$legend .= $icon_view_image . ' ' . gettext('view unpublished') . ' ';
				foreach ($full as $item) {
					if ($item['type'] == 'album') {
						if (in_array($item['data'], $flag)) {
							$note = '*';
						} else {
							$note = '';
						}
						$cv[$item['name'] . $note] = $item['data'];
						$extra[$item['data']][] = array('name' => 'name', 'value' => $item['name'], 'display' => '', 'checked' => 0);
						$extra[$item['data']][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit_album, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_EDIT);
						if (($rights & UPLOAD_RIGHTS)) {
							if (hasDynamicAlbumSuffix($item['data']) && !is_dir(ALBUM_FOLDER_SERVERPATH . $item['data'])) {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
							} else {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_UPLOAD);
							}
						}
						if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
							$extra[$item['data']][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view_image, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_VIEW);
						}
					}
				}
				$rest = array_diff($objlist, $cv);
				foreach ($rest as $unmanaged) {
					$extra2[$unmanaged][] = array('name' => 'name', 'value' => $unmanaged, 'display' => '', 'checked' => 0);
					$extra2[$unmanaged][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit_album, 'checked' => 1);
					if (($rights & UPLOAD_RIGHTS)) {
						if (hasDynamicAlbumSuffix($unmanaged) && !is_dir(ALBUM_FOLDER_SERVERPATH . $unmanaged)) {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
						} else {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => 1);
						}
					}
					if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
						$extra2[$unmanaged][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view_image, 'checked' => 1);
					}
				}
			}
			$text = gettext("Managed albums:");
			$simplename = $objectname = gettext('Albums');
			$prefix = 'managed_albums_list_' . $prefix_id . '_';
			break;
		case 'news':
			if ($rights & (MANAGE_ALL_NEWS_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$rest = array();
				$alterrights = ' disabled="disabled"';
			} else {
				$cv = $userobj->getObjects('news');
				$rest = array_diff($objlist, $cv);
			}
			$text = gettext("Managed news categories:");
			$simplename = gettext('News');
			$objectname = gettext('News categories');
			$prefix = 'managed_news_list_' . $prefix_id . '_';
			break;
		case 'pages':
			if ($rights & (MANAGE_ALL_PAGES_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$rest = array();
				$alterrights = ' disabled="disabled"';
			} else {
				$cv = $userobj->getObjects('pages');
				$rest = array_diff($objlist, $cv);
			}
			$text = gettext("Managed pages:");
			$simplename = $objectname = gettext('Pages');
			$prefix = 'managed_pages_list_' . $prefix_id . '_';
			break;
	}
	if (empty($alterrights)) {
		$hint = sprintf(gettext('Select one or more %1$s for the %2$s to manage.'), $simplename, $kind) . ' ';
		if ($kind == gettext('user')) {
			$hint .= sprintf(gettext('Users with "Admin" or "Manage all %1$s" rights can manage all %2$s. All others may manage only those that are selected.'), $simplename, $objectname);
		}
	} else {
		$hint = sprintf(gettext('You may manage these %s subject to the above rights.'), $simplename);
	}
	if (count($cv) > 0) {
		$itemcount = ' (' . count($cv) . ')';
	} else {
		$itemcount = '';
	}
	if(empty($rest) && empty($cv)) {
		return;
	}
	?>
	<div class="box-albums-unpadded">
		<h2 class="h2_bordered_albums">
			<a href="javascript:toggle('<?php echo $prefix ?>');" title="<?php echo html_encode($hint); ?>" ><?php echo $text . $itemcount; ?></a>
		</h2>
		<div id="<?php echo $prefix ?>" style="display:none;">
			<ul class="albumchecklist">
				<?php
				generateUnorderedListFromArray($cv, $cv, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra);
				generateUnorderedListFromArray(array(), $rest, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra2);
				?>
			</ul>
			<span class="floatright"><?php echo $legend; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<br class="clearall" />
		</div>
	</div>
	<?php
}

/**
 * processes the post of administrator rights
 *
 * @param int $i the admin row number
 * @return bit
 */
function processRights($i) {
	if (isset($_POST[$i . '-authentication']) && $_POST[$i . '-authentication'] == 'authenticate') {
		$rights = USER_RIGHTS; // editing the account should be allowed
		if (extensionEnabled('register_user')) {
			$defaultrights = getOption('register_user_user_rights');
			if (is_numeric($defaultrights)) {
				$rights = $defaultrights;
			} else { //  a group or template
				$admin = Authority::getAnAdmin(array('`user`=' => $defaultrights, '`valid`=' => 0));
				if ($admin) {
					$rights = $admin->getRights();
				} else {
					$rights = USER_RIGHTS; //NO_RIGHTS;
				}
			}
		}
	} else {
		$rights = 0;
	}

	foreach (Authority::getRights() as $name => $right) {
		if (isset($_POST[$i . '-' . $name])) {
			$rights = $rights | $right['value'] | NO_RIGHTS;
		}
	}
	if ($rights & MANAGE_ALL_ALBUM_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_ALBUMS_RIGHTS | ALBUM_RIGHTS;
	}
	if ($rights & MANAGE_ALL_NEWS_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_NEWS_RIGHTS | ZENPAGE_NEWS_RIGHTS;
	}
	if ($rights & MANAGE_ALL_PAGES_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_PAGES_RIGHTS | ZENPAGE_PAGES_RIGHTS;
	}
	return $rights;
}

function processManagedObjects($i, &$rights) {
	$objects = array();
	$albums = array();
	$pages = array();
	$news = array();
	$l_a = strlen($prefix_a = 'managed_albums_list_' . $i . '_');
	$l_p = strlen($prefix_p = 'managed_pages_list_' . $i . '_');
	$l_n = strlen($prefix_n = 'managed_news_list_' . $i . '_');
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if (substr($key, 0, $l_a) == $prefix_a) {
			$key = substr($key, $l_a);
			if (preg_match('/(.*)(_edit|_view|_upload|_name)$/', $key, $matches)) {
				$key = $matches[1];
				if (array_key_exists($key, $albums)) {
					switch ($matches[2]) {
						case '_edit':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT;
							break;
						case '_upload':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_UPLOAD;
							break;
						case '_view':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_VIEW;
							break;
						case '_name':
							$albums[$key]['name'] = $value;
							break;
					}
				}
			} else if ($value) {
				$albums[$key] = array('data' => $key, 'name' => '', 'type' => 'album', 'edit' => 32767 & ~(MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW));
			}
		}
		if (substr($key, 0, $l_p) == $prefix_p) {
			if ($value) {
				$pages[] = array('data' => substr($key, $l_p), 'type' => 'pages');
			}
		}
		if (substr($key, 0, $l_n) == $prefix_n) {
			if ($value) {
				$news[] = array('data' => substr($key, $l_n), 'type' => 'news');
			}
		}
	}
	foreach ($albums as $key => $analbum) {
		unset($albums[$key]);
		$albums[] = $analbum;
	}
	if (empty($albums)) {
		if (!($rights & MANAGE_ALL_ALBUM_RIGHTS)) {
			$rights = $rights & ~ALBUM_RIGHTS;
		}
	} else {
		$rights = $rights | ALBUM_RIGHTS;
		if ($rights & (MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) {
			$albums = array();
		}
	}
	if (empty($pages)) {
		if (!($rights & MANAGE_ALL_PAGES_RIGHTS)) {
			$rights = $rights & ~ZENPAGE_PAGES_RIGHTS;
		}
	} else {
		$rights = $rights | ZENPAGE_PAGES_RIGHTS;
		if ($rights & (MANAGE_ALL_PAGES_RIGHTS | ADMIN_RIGHTS)) {
			$pages = array();
		}
	}
	if (empty($news)) {
		if (!($rights & MANAGE_ALL_NEWS_RIGHTS)) {
			$rights = $rights & ~ZENPAGE_NEWS_RIGHTS;
		}
	} else {
		$rights = $rights | ZENPAGE_NEWS_RIGHTS;
		if ($rights & (MANAGE_ALL_NEWS_RIGHTS | ADMIN_RIGHTS)) {
			$news = array();
		}
	}
	$objects = array_merge($albums, $pages, $news);
	return $objects;
}

/**
 * Returns the value of a checkbox form item
 *
 * @param string $id the $_REQUEST index
 * @return int (0 or 1)
 */
function getCheckboxState($id) {
	if (isset($_REQUEST[$id]))
		return 1;
	else
		return 0;
}

/**
 * Returns a merged list of available watermarks
 *
 * @return array
 */
function getWatermarks() {
	$list = array();
	$curdir = getcwd();
	chdir($basepath = SERVERPATH . "/" . ZENFOLDER . '/watermarks/');
	$filelist = safe_glob('*.png');
	foreach ($filelist as $file) {
		$list[filesystemToInternal(substr(basename($file), 0, -4))] = $basepath . $file;
	}
	$basepath = SERVERPATH . "/" . USER_PLUGIN_FOLDER . '/watermarks/';
	if (is_dir($basepath)) {
		chdir($basepath);
		$filelist = safe_glob('*.png');
		foreach ($filelist as $file) {
			$list[filesystemToInternal(substr(basename($file), 0, -4))] = $basepath . $file;
		}
	}
	chdir($curdir);
	$watermarks = array_keys($list);
	return $watermarks;
}

/**
 * Processes the serialized array from tree sort.
 * Returns an array in the form [$id=>array(sort orders), $id=>array(sort orders),...]
 *
 * @param $orderstr the serialzied tree sort order
 * @return array
 */
function processOrder($orderstr) {
	$order = $result = array();
	parse_str($orderstr, $order);
	$order = array_shift($order);

	$parents = $curorder = array();
	$curowner = '';
	foreach ($order as $id => $parent) { // get the root elements
		if ($parent != $curowner) {
			if (($key = array_search($parent, $parents)) === false) { //	a child
				array_push($parents, $parent);
				array_push($curorder, -1);
			} else { //	roll back to parent
				$parents = array_slice($parents, 0, $key + 1);
				$curorder = array_slice($curorder, 0, $key + 1);
			}
		}
		$l = count($curorder) - 1;
		$curorder[$l] = sprintf('%03u', $curorder[$l] + 1);
		$result[$id] = $curorder;
	}
	return $result;
}

/**
 * Prints the dropdown menu for the nesting level depth for the album sorting
 *
 */
function printEditDropdown($subtab, $nestinglevels, $nesting) {
	switch ($subtab) {
		case '':
			$link = '?selection=';
			break;
		case 'subalbuminfo':
			$link = '?page=edit&amp;album=' . html_encode($_GET['album']) . '&amp;tab=subalbuminfo&amp;selection=';
			break;
		case 'imageinfo':
			if (isset($_GET['tagsort'])) {
				$tagsort = '&tagsort=' . sanitize($_GET['tagsort']);
			} else {
				$tagsort = '';
			}
			$link = '?page=edit&amp;album=' . html_encode($_GET['album']) . '&amp;tab=imageinfo' . html_encode($tagsort) . '&amp;selection=';
			break;
	}
	?>
	<form name="AutoListBox2" style="float: right;" action="#" >
		<select name="ListBoxURL" size="1" onchange="zp_gotoLink(this.form);">
			<?php
			foreach ($nestinglevels as $nestinglevel) {
				if ($nesting == $nestinglevel) {
					$selected = 'selected="selected"';
				} else {
					$selected = "";
				}
				echo '<option ' . $selected . ' value="admin-edit.php' . $link . $nestinglevel . '">';
				switch ($subtab) {
					case '':
					case 'subalbuminfo':
						printf(ngettext('Show %u album level', 'Show %u album levels', $nestinglevel), $nestinglevel);
						break;
					case 'imageinfo':
						printf(ngettext('%u image per page', '%u images per page', $nestinglevel), $nestinglevel);
						break;
				}
				echo '</option>';
			}
			?>
		</select>
	</form>
	<?php
}

function processEditSelection($subtab) {
	global $_zp_admin_subalbum_nesting, $_zp_admin_album_nesting, $_zp_admin_imagestab_imagecount;
	if (isset($_GET['selection'])) {
		switch ($subtab) {
			case '':
				$_zp_admin_album_nesting = max(1, sanitize_numeric($_GET['selection']));
				zp_setCookie('zpcms_admin_gallery_nesting', $_zp_admin_album_nesting);
				break;
			case 'subalbuminfo':
				$_zp_admin_subalbum_nesting = max(1, sanitize_numeric($_GET['selection']));
				zp_setCookie('zpcms_admin_subalbum_nesting', $_zp_admin_subalbum_nesting);
				break;
			case 'imageinfo':
				$_zp_admin_imagestab_imagecount = max(ADMIN_IMAGES_STEP, sanitize_numeric($_GET['selection']));
				zp_setCookie('zpcms_admin_imagestab_imagecount', $_zp_admin_imagestab_imagecount);
				break;
		}
	} else {
		switch ($subtab) {
			case '':
				$_zp_admin_album_nesting = zp_getCookie('zpcms_admin_gallery_nesting');
				break;
			case 'subalbuminfo':
				$_zp_admin_subalbum_nesting = zp_getCookie('zpcms_admin_subalbum_nesting');
				break;
			case 'imageinfo':
				$count = zp_getCookie('zpcms_admin_imagestab_imagecount');
				if ($count)
					$_zp_admin_imagestab_imagecount = $count;
				break;
		}
	}
}

/**
 * Edit tab bulk actions drop-down
 * @param array $checkarray the list of actions
 * @param bool $checkAll set true to include check all box
 */
function printBulkActions($checkarray, $checkAll = false) {
	$tags = in_array('addtags', $checkarray) || in_array('alltags', $checkarray);
	$movecopy = in_array('moveimages', $checkarray) || in_array('copyimages', $checkarray);
	$categories = in_array('addcats', $checkarray) || in_array('clearcats', $checkarray);
	$changeowner = in_array('changeowner', $checkarray);
	if ($tags || $movecopy || $categories || $changeowner) {
		?>
		<script>
			function checkFor(obj) {
				var sel = obj.options[obj.selectedIndex].value;
		<?php
		if ($tags) {
			?>
					if (sel == 'addtags' || sel == 'alltags') {
						$.colorbox({
							href: "#mass_tags_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($movecopy) {
			?>
					if (sel == 'moveimages' || sel == 'copyimages') {
						$.colorbox({
							href: "#mass_movecopy_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($categories) {
			?>
					if (sel == 'addcats') {
						$.colorbox({
							href: "#mass_cats_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($changeowner) {
			?>
					if (sel == 'changeowner') {
						$.colorbox({
							href: "#mass_owner_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		?>
			}
		</script>
		<?php
	}
	?>
	<span style="float:right">
		<select class="dirtyignore" name="checkallaction" id="checkallaction" size="1" onchange="checkFor(this);" >
			<?php generateListFromArray(array('noaction'), $checkarray, false, true); ?>
		</select>
		<?php
		if ($checkAll) {
			?>
			<br />
			<?php
			echo gettext("Check All");
			?>
			<input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
			<?php
		}
		?>
	</span>
	<?php
	if ($tags) {
		?>
		<div id="mass_tags" style="display:none;">
			<div id="mass_tags_data">
				<?php
				tagSelector(NULL, 'mass_tags_', false, false, true, false, 'checkTagsAuto dirtyignore');
				?>
			</div>
		</div>
		<?php
	}
	if ($categories) {
		?>
		<div id="mass_cats" style="display:none;">
			<ul id="mass_cats_data">
				<?php
				printNestedItemsList('cats-checkboxlist', '', 'all', 'dirtyignore');
				?>
			</ul>
		</div>
		<?php
	}
	if ($changeowner) {
		?>
		<div id="mass_owner" style="display:none;">
			<ul id="mass_owner_data">
				<select class="dirtyignore" id="massownermenu" name="massownerselect" onchange="">
					<?php
					echo admin_album_list(NULL);
					?>
				</select>
			</ul>
		</div>
		<?php
	}
	if ($movecopy) {
		global $_zp_admin_mcr_albumlist, $album;
		?>
		<div id="mass_movecopy_copy" style="display:none;">
			<div id="mass_movecopy_data">
				<input type="hidden" name="massfolder" value="<?php echo $album->name; ?>" />
				<?php
				echo gettext('Destination');
				?>
				<select class="dirtyignore" id="massalbumselectmenu" name="massalbumselect" onchange="">
					<?php
					foreach ($_zp_admin_mcr_albumlist as $fullfolder => $albumtitle) {
						$singlefolder = $fullfolder;
						$saprefix = "";
						$selected = "";
						if ($album->name == $fullfolder) {
							$selected = " selected=\"selected\" ";
						}
						// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
						while (strstr($singlefolder, '/') !== false) {
							$singlefolder = substr(strstr($singlefolder, '/'), 1);
							$saprefix = "–&nbsp;" . $saprefix;
						}
						echo '<option value="' . $fullfolder . '"' . "$selected>" . $saprefix . $singlefolder . "</option>\n";
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}

/**
 *
 * common redirector for bulk action handling return
 * @param string $action
 */
function bulkActionRedirect($action) {
	$uri = getRequestURI();
	if (strpos($uri, '?')) {
		$uri .= '&bulkaction=' . $action;
	} else {
		$uri .= '?bulkaction=' . $action;
	}
	redirectURL($uri);
}

/**
 * Processes the check box bulk actions for comments
 *
 */
function processCommentBulkActions() {
	global $_zp_current_admin_obj;
	if (isset($_POST['ids'])) { // these is actually the folder name here!
		$action = sanitize($_POST['checkallaction']);
		if ($action != 'noaction') {
			$ids = sanitize($_POST['ids']);
			if (count($ids) > 0) {
				foreach ($ids as $id) {
					$comment = new Comment(sanitize_numeric($id));
					switch ($action) {
						case 'deleteall':
							$comment->remove();
							break;
						case 'spam':
							if (!$comment->getInModeration()) {
								$comment->setInModeration(1);
								zp_apply_filter('comment_disapprove', $comment);
							}
							break;
						case 'approve':
							if ($comment->getInModeration()) {
								$comment->setInModeration(0);
								zp_apply_filter('comment_approve', $comment);
							}
							break;
					}
					$comment->setLastchangeUser($_zp_current_admin_obj->getLoginName());
					$comment->save(true);
				}
			}
		}
	}
	return $action;
}


/**
 * Codeblock tabs JavaScript code
 *
 */
function codeblocktabsJS() {
	?>
	<script charset="utf-8">
		$(function () {
			var tabContainers = $('div.tabs > div');
			$('.first').addClass('selected');
		});

		function cbclick(num, id) {
			$('.cbx-' + id).hide();
			$('#cb' + num + '-' + id).show();
			$('.cbt-' + id).removeClass('selected');
			$('#cbt' + num + '-' + id).addClass('selected');
		}

		function cbadd(id, offset) {
			var num = $('#cbu-' + id + ' li').size() - offset;
			$('li:last', $('#cbu-' + id)).remove();
			$('#cbu-' + id).append('<li><a class="cbt-' + id + '" id="cbt' + num + '-' + id + '" href="javascript:cbclick(' + num + ',' + id + ');" title="' + '<?php echo gettext('codeblock %u'); ?>'.replace(/%u/, num) + '">&nbsp;&nbsp;' + num + '&nbsp;&nbsp;</a></li>');
			$('#cbu-' + id).append('<li><a id="cbp-' + id + '" href="javascript:cbadd(' + id + ',' + offset + ');" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>');
			$('#cbd-' + id).append('<div class="cbx-' + id + '" id="cb' + num + '-' + id + '" style="display:none">' +
							'<textarea name="codeblock' + num + '-' + id + '" class="codeblock" id="codeblock' + num + '-' + id + '" rows="40" cols="60"></textarea>' +
							'</div>');
			cbclick(num, id);
		}
	</script>
	<?php
}

/**
 *
 * prints codeblock edit boxes
 * @param object $obj
 * @param int $id
 */
function printCodeblockEdit($obj, $id) {
	$codeblock = getSerializedArray($obj->getCodeblock());
	$keys = array_keys($codeblock);
	array_push($keys, 1);
	$codeblockCount = max($keys) + 1;

	if (array_key_exists(0, $codeblock) && !empty($codeblock)) {
		$start = 0;
	} else {
		$start = (int) getOption('codeblock_first_tab');
	}
	?>
	<div id="cbd-<?php echo $id; ?>" class="tabs">
		<ul id="<?php echo 'cbu' . '-' . $id; ?>" class="tabNavigation">
			<?php
			for ($i = $start; $i < $codeblockCount; $i++) {
				?>
				<li><a class="<?php if ($i == 1) echo 'first '; ?>cbt-<?php echo $id; ?>" id="<?php echo 'cbt' . $i . '-' . $id; ?>" href="javascript:cbclick(<?php echo $i . ',' . $id; ?>);" title="<?php printf(gettext('codeblock %u'), $i); ?>">&nbsp;&nbsp;<?php echo $i; ?>&nbsp;&nbsp;</a></li>
				<?php
			}
			if (zp_loggedin(CODEBLOCK_RIGHTS)) {
				$disabled = '';
				?>
				<li><a id="<?php echo 'cbp' . '-' . $id; ?>" href="javascript:cbadd(<?php echo $id; ?>,<?php echo 1 - $start; ?>);" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>
				<?php
			} else {
				$disabled = ' disabled="disabled"';
			}
			?>
		</ul>

		<?php
		for ($i = $start; $i < $codeblockCount; $i++) {
			?>
			<div class="cbx-<?php echo $id; ?>" id="cb<?php echo $i . '-' . $id; ?>"<?php if ($i != 1) echo ' style="display:none"'; ?>>
				<?php
				if (!$i) {
					?>
					<span class="notebox"><?php echo gettext('Codeblock 0 is deprecated.') ?></span>
					<?php
				}
				?>
				<textarea name="codeblock<?php echo $i; ?>-<?php echo $id; ?>" class="codeblock" id="codeblock<?php echo $i; ?>-<?php echo $id; ?>" rows="40" cols="60"<?php echo $disabled; ?>><?php echo html_encode(@$codeblock[$i]); ?></textarea>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 *
 * handles saveing of codeblock edits
 * @param object $object
 * @param int $id
 * @return string
 */
function processCodeblockSave($id) {
	$codeblock = array();
	$i = (int) !isset($_POST['codeblock0-' . $id]);
	while (isset($_POST['codeblock' . $i . '-' . $id])) {
		$v = sanitize($_POST['codeblock' . $i . '-' . $id], 0);
		if ($v) {
			$codeblock[$i] = $v;
		}
		$i++;
	}
	return serialize($codeblock);
}


/**
 * getPageSelector "diff" function
 *
 * returns the shortest string difference
 * @param string $string1
 * @param string2 $string2
 */
function minDiff($string1, $string2) {
	if ($string1 == $string2) {
		return $string2;
	}
	if (empty($string1)) {
		return substr($string2, 0, 10);
	}
	if (empty($string2)) {
		return substr($string1, 0, 10);
	}
	if (strlen($string2) > strlen($string1)) {
		$base = $string2;
	} else {
		$base = $string1;
	}
	for ($i = 0; $i < min(strlen($string1), strlen($string2)); $i++) {
		if ($string1[$i] != $string2[$i]) {
			$base = substr($string2, 0, max($i + 1, 10));
			break;
		}
	}
	return rtrim($base, '-_');
}

/**
 * getPageSelector "diff" function
 *
 * Used when you want getPgeSelector to show the full text of the items
 * @param string $string1
 * @param string $string2
 * @return string
 */
function fullText($string1, $string2) {
	return $string2;
}

/**
 * getPageSelector "diff" function
 *
 * returns the shortest "date" difference
 * @param string $date1
 * @param string $date2
 * @return string
 */
function dateDiff($date1, $date2) {
	$separators = array('', '-', '-', ' ', ':', ':');
	preg_match('/(.*)-(.*)-(.*) (.*):(.*):(.*)/', strval($date1), $matches1);
	preg_match('/(.*)-(.*)-(.*) (.*):(.*):(.*)/', strval($date2), $matches2);
	if (empty($matches1)) {
		$matches1 = array(0, 0, 0, 0, 0, 0, 0);
	}
	if (empty($matches2)) {
		$matches2 = array(0, 0, 0, 0, 0, 0, 0);
	}

	$date = '';
	for ($i = 1; $i <= 6; $i++) {
		if (@$matches1[$i] != @$matches2[$i]) {
			break;
		}
	}
	switch ($i) {
		case 7:
		case 6:
			$date = ':' . $matches2[6];
		case 5:
		case 4:
			$date = ' ' . $matches2[4] . ':' . $matches2[5] . $date;
		default:
			$date = $matches2[1] . '-' . $matches2[2] . '-' . $matches2[3] . $date;
	}
	return rtrim($date, ':-');
}

/**
 * returns a selector list based on the "names" of the list items
 *
 *
 * @param array $list
 * @param int $itmes_per_page
 * @param string $diff
 * 									"fullText" for the complete names
 * 									"minDiff" for a truncated string showing just the unique characters of the names
 * 									"dateDiff" it the "names" are really dates.
 * @return array
 */
function getPageSelector($list, $itmes_per_page, $diff = 'fullText') {
	$rangeset = array();
	$pages = round(ceil(count($list) / (int) $itmes_per_page));
	$list = array_values($list);
	if ($pages > 1) {
		$ranges = array();
		for ($page = 0; $page < $pages; $page++) {
			$ranges[$page]['start'] = strtolower(strval(get_language_string($list[$page * $itmes_per_page])));
			$last = (int) ($page * $itmes_per_page + $itmes_per_page - 1);
			if (array_key_exists($last, $list)) {
				$ranges[$page]['end'] = strtolower(strval(get_language_string($list[$last])));
			} else {
				$ranges[$page]['end'] = strtolower(strval(get_language_string(@array_pop($list))));
			}
		}
		$last = '';
		foreach ($ranges as $page => $range) {
			$next = @$ranges[$page + 1]['start'];
			$rangeset[$page] = $diff($last, $range['start']) . ' » ' . $diff($next, $range['end']);
			$last = $range['end'];
		}
	}
	return $rangeset;
}

function printPageSelector($pagenumber, $rangeset, $script, $queryParams) {
	global $instances;
	$pages = count($rangeset);
	$jump = $query = '';
	foreach ($queryParams as $param => $value) {
		$query .= html_encode($param) . '=' . html_encode($value) . '&amp;';
		$jump .= "'" . html_encode($param) . "=" . html_encode($value) . "',";
	}
	$query = '?' . $query;
	if ($pagenumber > 0) {
		?>
		<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script . $query; ?>pagenumber=<?php echo ($pagenumber - 1); ?>" >« <?php echo gettext('prev'); ?></a>
		<?php
	}
	if ($pages > 2) {
		if ($pagenumber > 0) {
			?>
			|
			<?php
		}
		?>
		<select name="pagenumber" class="dirtyignore" id="pagenumber<?php echo $instances; ?>" onchange="launchScript('<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script; ?>',
						[<?php echo $jump; ?>'pagenumber=' + $('#pagenumber<?php echo $instances; ?>').val()]);" >
						<?php
							foreach ($rangeset as $page => $range) {
								?>
				<option value="<?php echo $page; ?>" <?php if ($page == $pagenumber) echo ' selected="selected"'; ?>><?php echo $range; ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
	if ($pages > $pagenumber + 1) {
		if ($pages > 2) {
			?>
			|
		<?php }
		?>
		<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script . $query; ?>pagenumber=<?php echo ($pagenumber + 1); ?>" ><?php echo gettext('next'); ?> »</a>
		<?php
	}
	$instances++;
}

/**
 * Figures out which plugin tabs to display
 */
function getPluginTabs() {
	if (isset($_GET['tab'])) {
		$default = sanitize($_GET['tab']);
	} else {
		$default = 'all';
	}
	$paths = getPluginFiles('*.php');
	$currentlist = $classes = $member = array();
	$plugin_category = '';
	foreach ($paths as $plugin => $path) {
		$p = file_get_contents($path);
		$i = sanitize(isolate('$plugin_category', $p));
		if ($i !== false) {
			eval($i); // populates variable $plugin_category - ugly but otherwise gettext does not work…
			$member[$plugin] = strtolower($plugin_category);
		} else {
			// fallback for older plugins using @package for category without gettext
			$i = strpos($p, '* @subpackage');
			if (($key = $i) !== false) {
				$plugin_category = strtolower(trim(substr($p, $i + 13, strpos($p, "\n", $i) - $i - 13)));
			}
			if (empty($plugin_category)) {
				$plugin_category = gettext('Misc');
			}
			$classXlate = array(
					'active' => gettext('Active'),
					'all' => gettext('All'),
					'admin' => gettext('Admin'),
					'demo' => gettext('Demo'),
					'development' => gettext('Development'),
					'feed' => gettext('Feed'),
					'mail' => gettext('Mail'),
					'media' => gettext('Media'),
					'misc' => gettext('Misc'),
					'spam' => gettext('Spam'),
					'statistics' => gettext('Statistics'),
					'seo' => gettext('SEO'),
					'uploader' => gettext('Uploader'),
					'users' => gettext('Users')
			);
			zp_apply_filter('plugin_tabs', $classXlate);
			if (array_key_exists($plugin_category, $classXlate)) {
				$local = $classXlate[$plugin_category];
			} else {
				$local = $plugin_category;
			}
			$member[$plugin] = strtolower($local);
		}
		$classes[strtolower($plugin_category)]['list'][] = $plugin;
		if (extensionEnabled($plugin)) {
			$classes['active']['list'][] = $plugin;
		}
	}
	ksort($classes);
	$tabs[gettext('all')] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-plugins.php?page=plugins&tab=all';
	$currentlist = array_keys($paths);

	foreach ($classes as $class => $list) {
		$tabs[$class] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-plugins.php?page=plugins&tab=' . $class;
		if ($class == $default) {
			$currentlist = $list['list'];
		}
	}
	return array($tabs, $default, $currentlist, $paths, $member);
}

/**
 *
 * handles save of user/password
 * @param object $object
 */
function processCredentials($object, $suffix = '') {
	$notify = '';
	if (isset($_POST['password_enabled' . $suffix]) && $_POST['password_enabled' . $suffix]) {
		if (is_object($object)) {
			$olduser = $object->getUser();
		} else {
			$olduser = getOption($object . '_user');
		}
		$newuser = trim(sanitize($_POST['user' . $suffix], 3));
		$pwd = trim(sanitize($_POST['pass' . $suffix]));
		if (isset($_POST['disclose_password' . $suffix])) {
			$pass2 = $pwd;
		} else {
			if (isset($_POST['pass_r' . $suffix])) {
				$pass2 = trim(sanitize($_POST['pass_r' . $suffix]));
			} else {
				$pass2 = '';
			}
		}
		$fail = '';
		if ($olduser != $newuser) {
			if (!empty($newuser) && strlen($_POST['pass' . $suffix]) == 0) {
				$fail = '?mismatch=user';
			}
		}
		if (!$fail && $pwd == $pass2) {
			if (is_object($object)) {
				$object->setUser($newuser);
			} else {
				setOption($object . '_user', $newuser);
			}
			if (empty($pwd)) {
				if (strlen($_POST['pass' . $suffix]) == 0) {
					// clear the  password
					if (is_object($object)) {
						$object->setPassword(NULL);
					} else {
						setOption($object . '_password', NULL);
					}
				}
			} else {
				if (is_object($object)) {
					$object->setPassword(Authority::passwordHash($newuser, $pwd));
				} else {
					setOption($object . '_password', Authority::passwordHash($newuser, $pwd));
				}
			}
		} else {
			if (empty($fail)) {
				$notify = '?mismatch';
			} else {
				$notify = $fail;
			}
		}
		$hint = process_language_string_save('hint' . $suffix, 3);
		if (is_object($object)) {
			$object->setPasswordHint($hint);
		} else {
			setOption($object . '_hint', $hint);
		}
	}
	return $notify;
}



function clonedFrom() {
	if (PRIMARY_INSTALLATION) {
		return false;
	} else {
		$zen = str_replace('\\', '/', @readlink(SERVERPATH . '/' . ZENFOLDER));
		return dirname($zen);
	}
}

/**
 * Prints the last change date and last change user notice on backend edit pages
 * Also for albums it prints the updateddate 
 * 
 * @since 1.5.2
 * @param obj $obj Object of any item type
 */
function printLastChangeInfo($obj) {
	?>
	<hr>
	<ul>
		<?php
		if (AlbumBase::isAlbumClass($obj) && $obj->getUpdatedDate()) {
			?>
			<li><?php printf(gettext('Last updated: %s'), $obj->getUpdatedDate()); ?></li>
			<?php
		}
		if (get_class($obj) == 'Administrator') {
			?>
			<li><?php printf(gettext('Account created: %s'), $obj->getDateTime()); ?></li>
			<li><?php printf(gettext('Current login: %s'), $obj->get('loggedin')); ?></li>
			<li><?php printf(gettext('Last previous login: %s'), $obj->getLastLogon()); ?></li>
			<li><?php printf(gettext('Last password update: %s'), $obj->get('passupdate')); ?></li>
			<li><?php printf(gettext('Last visit: %s'), $obj->getLastVisit()); ?></li>
			<?php
		}
		?>
		<li><?php printf(gettext('Last change: %s'), $obj->getLastchange()); ?></li>
		<?php
		$lastchangeuser = $obj->getLastchangeUser();
		if (empty($lastchangeuser)) {
			$lastchangeuser = gettext('ZenphotoCMS internal request');
		}
		?>
		<li><?php printf(gettext('Last changed by: %s'), $lastchangeuser); ?></li>
	</ul>
	<?php
}



/**
 * Prints the scheduled publishing date for items if set. Also prints the date for Zenpage news articles and pages
 *
 * @since 1.5.7 moved from Zenpage plugin to generel admin functions
 * @param string $obj image, albun, news article or page object
 * @return string
 */
function printPublished($obj) {
	if ($obj->table == 'images' || $obj->table == 'albums') {
		$date = $obj->getPublishDate();
	} else if ($obj->table == 'news' || $obj->table == 'pages') {
		$date = $obj->getDateTime();
	}
	if ($obj->hasPublishSchedule()) {
		echo '<span class="scheduledate">' . $date . '</strong>';
	} else {
		if (in_array($obj->table, array('news', 'pages'))) {
			echo '<span>' . $date . '</span>';
		}
	}
}

/**
 * Prints the expiration or expired date for items
 * 
 * @since 1.5.7 moved from Zenpage plugin to generel admin functions
 * @param string $obj image, albun, news article or page object
 * @return string
 */
function printExpired($obj) {
	$date = $obj->getExpireDate();
	if ($obj->hasExpired()) {
		echo ' <span class="expired">' . $date . "</span>";
	} else if ($obj->hasExpiration()) {
		echo ' <span class="expiredate">' . $date . "</span>";
	}
}



/**
 * Checks plugin and theme definition for $plugin_disable / $theme_description['disable'] so plugins/themes are deaktivated respectively cannot be activated
 * if they don't match conditions/requirements. See the plugin/theme documentation for info how to define these.
 * 
 * Returns either the message why incompatible or false if not.
 * 
 * @since 1.5.8
 * 
 * @param string|array $disable One string or serveral as an array. Not false means incompatible 
 * @return boolean|string
 */
function isIncompatibleExtension($disable) {
	$check = processExtensionVariable($disable);
	if ($check) {
		return $check;
	}
	return false;
}

/**
 * Processes a plugin or theme definition variable. 
 * 
 * If a string or boolean it is returned as it is.  If it is an array each entry is enclosed 
 * with an HTML paragraph and returned as a string
 * 
 * @since 1.5.8
 * 
 * @param string|array $var  A plugin or theme definition variable 
 * @return string|bool
 */
function processExtensionVariable($var) {
	if ($var) {
		if (is_array($var)) {
			$text = '';
			foreach ($var as $entry) {
				if ($entry) {
					$text .= '<p>' . $entry . '</p>';
				}
			}
			return $text;
		} else {
			return $var;
		}
	}
	return $var;
}

/**
 * Updates $_zp_admin_user_updated on user editing 
 * @global boolean $_zp_admin_user_updated
 */
function markUpdated() {
	global $_zp_admin_user_updated;
	$_zp_admin_user_updated = true;
//for finding out who did it!	debugLogBacktrace('updated');
}