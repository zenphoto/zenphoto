<?php 
/**
 * Options related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

/**
	 * Used for checkbox and radiobox form elements to compare the $checked value with the $current.
	 * Echos the attribute `checked="checked`
	 * @param mixed $checked
	 * @param mixed $current
	 */
	function checked($checked, $current) {
		if ($checked == $current)
			echo ' checked="checked"';
	}

	function customOptions($optionHandler, $indent = "", $album = NULL, $showhide = false, $supportedOptions = NULL, $theme = false, $initial = 'none', $extension = NULL) {
		global $_zp_db;
		if (is_null($supportedOptions)) {
			$supportedOptions = $optionHandler->getOptionsSupported();
		}
		if (count($supportedOptions) > 0) {
			$whom = get_class($optionHandler);
			$options = $supportedOptions;
			$option = array_shift($options);
			if (array_key_exists('order', $option)) {
				$options = sortMultiArray($supportedOptions, 'order', false, true, false, true);
				$options = array_keys($options);
			} else {
				$options = array_keys($supportedOptions);
			}
			if (method_exists($optionHandler, 'handleOptionSave')) {
				?>
				<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX; ?>save-<?php echo $whom; ?>" value="<?php echo $extension; ?>" />
				<?php
			}
			foreach ($options as $option) {
				$row = $supportedOptions[$option];
				if (false !== $i = stripos($option, chr(0))) {
					$option = substr($option, 0, $i);
				}

				$type = $row['type'];
				$desc = $row['desc'];
				$key = @$row['key'];
				$optionID = $whom . '_' . $key;
				if (isset($row['multilingual'])) {
					$multilingual = $row['multilingual'];
				} else {
					$multilingual = $type == OPTION_TYPE_TEXTAREA;
				}
				if (isset($row['texteditor']) && $row['texteditor']) {
					$editor = 'texteditor';
				} else {
					$editor = '';
				}
				if (isset($row['disabled']) && $row['disabled']) {
					$disabled = ' disabled="disabled"';
				} else {
					$disabled = '';
				}
				if (isset($row['deprecated']) && $option) {
					$deprecated = $row['deprecated'];
					if (!$deprecated) {
						$deprecatedd = gettext('Deprecated.');
					}
					$option = '<div class="warningbox">' . $option . '<br /><em>' . $deprecated . '</em></div>';
				}
				if ($theme) {
					$v = getThemeOption($key, $album, $theme);
				} else {
					$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($key);
					$db = $_zp_db->querySingleRow($sql);
					if ($db) {
						$v = $db['value'];
					} else {
						$v = NULL;
					}
				}

				if ($showhide) {
					?>
					<tr id="tr_<?php echo $optionID; ?>" class="<?php echo $showhide; ?>extrainfo" style="display:<?php echo $initial; ?>">
						<?php
					} else {
						?>
					<tr id="tr_<?php echo $optionID; ?>">
						<?php
					}
					if ($type != OPTION_TYPE_NOTE) {
						?>
						<td width="175"><?php if ($option) echo $indent . $option; ?></td>
						<?php
					}
					switch ($type) {
						case OPTION_TYPE_NOTE:
							?>
							<td colspan="3"><?php echo $desc; ?></td>
							<?php
							break;
						case OPTION_TYPE_CLEARTEXT:
							$multilingual = false;
						case OPTION_TYPE_PASSWORD:
						case OPTION_TYPE_TEXTBOX:
						case OPTION_TYPE_TEXTAREA:
						case OPTION_TYPE_RICHTEXT;
							if ($type == OPTION_TYPE_CLEARTEXT) {
								$clear = 'clear';
							} else {
								$clear = '';
							}
							if ($type == OPTION_TYPE_PASSWORD) {
								$inputtype = 'password';
								$multilingual = false;
							} else {
								$inputtype = 'text';
							}
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . $clear . 'text-' . $key; ?>" value="1" />
								<?php
								if ($multilingual) {
									print_language_string_list($v, $key, $type, NULL, $editor);
								} else {
									if ($type == OPTION_TYPE_TEXTAREA || $type == OPTION_TYPE_RICHTEXT) {
										$v = get_language_string($v); // just in case....
										?>
										<textarea id="<?php echo $key; ?>"<?php if ($type == OPTION_TYPE_RICHTEXT) echo ' class="texteditor"'; ?> name="<?php echo $key; ?>" cols="<?php echo TEXTAREA_COLUMNS; ?>"	style="width: 320px" rows="6"<?php echo $disabled; ?>><?php echo html_encode($v); ?></textarea>
										<?php
									} else {
										?>
										<input type="<?php echo $inputtype; ?>" size="40" id="<?php echo $key; ?>" name="<?php echo $key; ?>" style="width: 338px" value="<?php echo html_encode($v); ?>"<?php echo $disabled; ?> />
										<?php
									}
								}
								?>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $key; ?>" value="1" />
								<input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php checked('1', $v); ?><?php echo $disabled; ?> />
							</td>
							<?php
							break;
						case OPTION_TYPE_CUSTOM:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'custom-' . $key; ?>" value="0" />
								<?php $optionHandler->handleOption($key, $v); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_RADIO:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'radio-' . $key; ?>" value="1"<?php echo $disabled; ?> />
								<?php generateRadiobuttonsFromArray($v, $row['buttons'], $key, $behind, 'checkboxlabel', $disabled); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_SELECTOR:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'selector-' . $key ?>" value="1" />
								<select id="<?php echo $key; ?>" name="<?php echo $key; ?>"<?php echo $disabled; ?> >
									<?php
									if (array_key_exists('null_selection', $row)) {
										?>
										<option value=""<?php if (empty($v)) echo ' selected="selected"'; ?> style="background-color:LightGray;"><?php echo $row['null_selection']; ?></option>
										<?php
									}
									?>
									<?php generateListFromArray(array($v), $row['selections'], false, true); ?>
								</select>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_ARRAY:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td width="350">
								<?php
								foreach ($row['checkboxes'] as $display => $checkbox) {
									if ($theme) {
										$v = getThemeOption($checkbox, $album, $theme);
									} else {
										$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($checkbox);
										$db = $_zp_db->querySingleRow($sql);
										if ($db) {
											$v = $db['value'];
										} else {
											$v = 0;
										}
									}
									$display = str_replace(' ', '&nbsp;', $display);
									?>
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $checkbox; ?>" value="1" />

									<label class="checkboxlabel">
										<?php if ($behind) echo($display); ?>
										<input type="checkbox" id="<?php echo $checkbox; ?>" name="<?php echo $checkbox; ?>" value="1"<?php checked('1', $v); ?><?php echo $disabled; ?> />
										<?php if (!$behind) echo($display); ?>
									</label>
									<?php
								}
								?>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_UL:
							?>
							<td width="350">
								<?php
								$all = true;
								$cvarray = array();
								foreach ($row['checkboxes'] as $display => $checkbox) {
									?>
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $checkbox; ?>" value="1" />
									<?php
									if ($theme) {
										$v = getThemeOption($checkbox, $album, $theme);
									} else {
										$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($checkbox);
										$db = $_zp_db->querySingleRow($sql);
										if ($db) {
											$v = $db['value'];
										} else {
											$v = 0;
										}
									}
									if ($v) {
										$cvarray[] = $checkbox;
									} else {
										$all = false;
									}
								}
								?>
								<ul class="customchecklist">
									<?php generateUnorderedListFromArray($cvarray, $row['checkboxes'], '', '', true, true, 'all_' . $key); ?>
								</ul>
								<script>
									function <?php echo $key; ?>_all() {
										var check = $('#all_<?php echo $key; ?>').prop('checked');
										$('.all_<?php echo $key; ?>').prop('checked', check);
									}
								</script>
								<label>
									<input type="checkbox" name="all_<?php echo $key; ?>" id="all_<?php echo $key; ?>" class="all_<?php echo $key; ?>" onclick="<?php echo $key; ?>_all();" <?php if ($all) echo ' checked="checked"'; ?>/>
									<?php echo gettext('all'); ?>
								</label>
							</td>
							<?php
							break;
						case OPTION_TYPE_COLOR_PICKER:
							if (empty($v))
								$v = '#000000';
							?>
							<td width="350" style="margin:0; padding:0">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'text-' . $key; ?>" value="1" />
								<script>
									$(document).ready(function () {
										$('#<?php echo $key; ?>_colorpicker').farbtastic('#<?php echo $key; ?>');
									});
								</script>
								<table style="margin:0; padding:0" >
									<tr>
										<td><input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>"	value="<?php echo $v; ?>" style="height:100px; width:100px; float:right;" /></td>
										<td><div id="<?php echo $key; ?>_colorpicker"></div></td>
									</tr>
								</table>
							</td>
							<?php
							break;
					}
					if ($type != OPTION_TYPE_NOTE) {
						?>
						<td><?php echo $desc; ?></td>
						<?php
					}
					?>
				</tr>
				<?php
			}
		}
	}

	function processCustomOptionSave($returntab, $themename = NULL, $themealbum = NULL) {
		$customHandlers = array();
		foreach ($_POST as $postkey => $value) {
			if (preg_match('/^' . CUSTOM_OPTION_PREFIX . '/', $postkey)) { // custom option!
				$key = substr($postkey, strpos($postkey, '-') + 1);
				$switch = substr($postkey, strlen(CUSTOM_OPTION_PREFIX), -strlen($key) - 1);
				switch ($switch) {
					case 'text':
						$value = process_language_string_save($key, 1);
						break;
					case 'cleartext':
						if (isset($_POST[$key])) {
							$value = sanitize($_POST[$key], 0);
						} else {
							$value = '';
						}
						break;
					case 'chkbox':
						$value = (int) isset($_POST[$key]);
						break;
					case 'save':
						$customHandlers[] = array('whom' => $key, 'extension' => sanitize($_POST[$postkey]));
						break;
					default:
						if (isset($_POST[$key])) {
							$value = sanitize($_POST[$key], 1);
						} else {
							$value = '';
						}
						break;
				}
				if ($themename) {
					setThemeOption($key, $value, $themealbum, $themename);
				} else {
					$creator = NULL;
					if (isset($_GET['single'])) { // single plugin save
						$ext = sanitize($_GET['single'], 1);
						$pl = getPlugin($ext . '.php', false, true);
						if (!empty(WEBPATH)) {
							$creator = str_replace(WEBPATH . '/', '', $pl);
						} else {
							$creator = substr($pl, 1); //remove trailing slash
						}
					}
					setOption($key, $value, true, $creator);
				}
			} else {
				if (strpos($postkey, 'show-') === 0) {
					if ($value)
						$returntab .= '&' . $postkey;
				}
			}
		}
		foreach ($customHandlers as $custom) {
			if ($extension = $custom['extension']) {
				$getplugin = getPlugin($extension . '.php');
				if ($getplugin) {
					require_once($getplugin);
				}
			}
			if (class_exists($custom['whom'])) {
				$whom = new $custom['whom']();
				$returntab = $whom->handleOptionSave($themename, $themealbum) . $returntab;
			}
		}
		return $returntab;
	}
	
	/**
	 *
	 * Set defaults for standard theme options incase the theme has not done so
	 * @param string $theme
	 * @param int $albumid zero or the album "owning" the theme
	 */
	function standardThemeOptions($theme, $album) {
		setThemeOption('albums_per_page', 6, $album, $theme, true);
		setThemeOption('albums_per_row', 3, $album, $theme, true);
		setThemeOption('images_per_page', 20, $album, $theme, true);
		setThemeOption('images_per_row', 5, $album, $theme, true);
		setThemeOption('image_size', 595, $album, $theme, true);
		setThemeOption('image_use_side', 'longest', $album, $theme, true);
		setThemeOption('thumb_use_side', 'longest', $album, $theme, true);
		setThemeOption('thumb_size', 100, $album, $theme, true);
		setThemeOption('thumb_crop_width', 100, $album, $theme, true);
		setThemeOption('thumb_crop_height', 100, $album, $theme, true);
		setThemeOption('thumb_crop', 1, $album, $theme, true);
		setThemeOption('thumb_transition', 1, $album, $theme, true);
	}

	/**
 * Returns the option array for the sort by selectors for gallery, albums and images
 * 
 * @since 1.5.5 Replaces the global $_zp_sortby
 * 
 * @param string $type "albums" (also for gallery), "albums-dynamic", 'images' 
 * 										 "image-edit" (the images edit tab backend only ordering)
 * 										 "pages" and "news" for Zenpage items
 * @return array
 */
function getSortByOptions($type) {
	// base option for all item types
	$orders = array(
			gettext('Title') => 'title',
			gettext('ID') => 'id',
			gettext('Date') => 'date',
			gettext('Published') => 'show',
			gettext('Last change date') => 'lastchange',
			gettext('Last change user') => 'lastchangeuser',
			gettext('Expire date') => 'expiredate',
			gettext('Top rated') => '(total_value/total_votes)',
			gettext('Most rated') => 'total_votes',
			gettext('Popular') => 'hitcounter',
	);
	switch ($type) {
		case 'albums':
		case 'albums-dynamic':
		case 'albums-search':
		case 'images':
		case 'images-search':
			$orders[gettext('Filemtime')] = 'mtime';
			$orders[gettext('Scheduled Publish date')] = 'publishdate';
			$orders[gettext('Owner')] = 'owner';
			switch ($type) {
				case 'albums':
				case 'albums-dynamic':
				case 'albums-search':
					$orders[gettext('Folder')] = 'folder';
					$orders[gettext('Last updated date')] = 'updateddate';
					$orders[gettext('Manual')] = 'manual';
					if ($type == 'albums-search') {
						$orders[gettext('Manual')] = 'sort_order';
					}
					break;
				case 'images':
				case 'images-search':
					$orders[gettext('Filename')] = 'filename';
					if ($type == 'images') {
						$orders[gettext('Manual')] = 'manual';
					}
					if ($type == 'images-search') {
						$orders[gettext('Manual')] = 'sort_order';
					}
					break;
			}
			break;
		case 'images-edit':
			$orders[gettext('Filemtime')] = 'mtime';
			$orders[gettext('Publish date')] = 'publishdate';
			$orders[gettext('Owner')] = 'owner';
			foreach ($orders as $key => $value) {
				$orders[sprintf(gettext('%s (descending)'), $key)] = $value . '_desc';
			}
			$orders[gettext('Manual')] = 'manual';
			break;
		case 'pages':
		case 'pages-search':
		case 'news':
			$orders[gettext('TitleLink')] = 'titlelink';
			$orders[gettext('Author')] = 'author';
			$orders[gettext('TitleLink')] = 'titlelink';
			$orders[gettext('Author')] = 'author';
			if ($type == 'pages') {
				$orders[gettext('Manual')] = 'manual'; // note for search orders this must be changed to "sort_order"
			}
			if ($type == 'pages-search') {
				$orders[gettext('Manual')] = 'sort_order';
			}
			break;
	}
	return filter::applyFilter('admin_sortbyoptions', $orders, $type);
}

/**
 * Returns an array of the status order options for all items
 * 
 * @since 1.5.5 Replaces the global $_zp_sortby_status
 * 
 * @return array
 */
function getSortByStatusOptions() {
	return array(
			gettext('All') => 'all',
			gettext('Published') => 'published',
			gettext('Unpublished') => 'unpublished'
	);
}


/**
 * Prints a selector (select list) with a custom text field from the values parameter. The following array entries will be created automatically:
 *
 * - gettext('Custom') = 'custom'
 * 
 * If "custom" is selected the custom text field will be shown.
 * 
 * @since 1.5.8
 * 
 * @global obj $_zp_gallery Gallery object
 * @param string $optionname The option name of the select list
 * @param array $list Key value array where key is the display value (gettext generally)
 * @param string $optionlabel The label text for the select list
 * @param string $optionname_customfield The option name of the custom field
 * @param string $optionlabel_customfield THe label text for the custom field
 * @param boolean $is_galleryoption Set to true if this is a special gallery class option
 */
function printSelectorWithCustomField($optionname, $list = array(), $optionlabel = null, $optionname_customfield = null, $optionlabel_customfield = nulll, $is_galleryoption = false) {
	global $_zp_gallery;
	$optionname_customfield_toggle = $optionname_customfield . '-toggle';
	if ($is_galleryoption) {
		$currentselection = $_zp_gallery->get($optionname);
	} else {
		$currentselection = getOption($optionname);
	}
	if (empty($currentselection)) {
		$currentselection = 'none';
	}
	if (is_null($optionname_customfield)) {
		$optionname_customfield = $optionname . '_custom';
	}
	if ($is_galleryoption) {
		$currentvalue_customfield = $_zp_gallery->get($optionname_customfield);
	} else {
		$currentvalue_customfield = getOption($optionname_customfield);
	}
	if (empty($list) && !in_array($currentselection, array('none', 'custom'))) { // no pages or disabled -> custom url
		$currentselection = 'none';
		$hiddenclass = '';
	}
	$list[gettext('Custom')] = 'custom';
	$hiddenclass = '';
	if ($currentselection == 'none' || $currentselection != 'custom') {
		$hiddenclass = ' class="hidden"';
	}
	?>
	<p>
		<label>
			<select id="<?php echo $optionname; ?>" name="<?php echo $optionname; ?>">
				<?php generateListFromArray(array($currentselection), $list, null, true); ?>
			</select>
			<br><?php echo html_encode($optionlabel); ?>
		</label>
	</p>
	<p id="<?php echo $optionname_customfield_toggle; ?>"<?php echo $hiddenclass; ?>>
		<label>
			<input type="text" name="<?php echo $optionname_customfield; ?>" id="<?php echo $optionname_customfield; ?>" value="<?php echo html_encode($currentvalue_customfield); ?>">
			<br><?php echo html_encode($optionlabel_customfield); ?>
		</label>
	</p>
	<script>
		toggleElementsBySelector('#<?php echo $optionname; ?>', 'custom', '#<?php echo $optionname_customfield_toggle; ?>');
	</script>
	<?php
}

/**
 * Gets an array of Zenpage pages ready for using with selector, radioboxes and checkbox lists
 * 
 * @since 1.5.8
 * 
 * @param bool $published true for only published, default false for all.
 * 
 */
function getZenpagePagesOptionsArray($published = false) {
	$pages = array();
	if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
		$zenpageobj = new Zenpage();
		$zenpagepages = $zenpageobj->getPages($published, false, null, 'sortorder', false);
		$pages = array();
		if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
			$pages[gettext('None')] = 'none';
			foreach ($zenpagepages as $zenpagepage) {
				$pageobj = new Zenpagepage($zenpagepage['titlelink']);
				$unpublished_note = '';
				if (!$pageobj->isPublished()) {
					$unpublished_note = '*';
				}
				$sublevel = '';
				$level = $pageobj->getLevel();
				if ($level != 1) {
					for ($l = 1; $l < $level; $l++) {
						$sublevel .= '-';
					}
				}
				$pages[$sublevel . get_language_string($zenpagepage['title']) . $unpublished_note] = $zenpagepage['titlelink'];
			}
		}
	}
	return $pages;
}

/**
 * Prints an select list option for Zenpage pages
 * 
 * it additionally prints a text field for a custom page URL.
 * 
 * @since 1.5.8
 * 
 * @param string $optionname Name of the option, sued for the selector and the current selection
 * @param string $optionname_custom If defined this will be used for the custom url option, if null (default) the option name will be used with "_custom" appended
 * @param boolean $published If the pages should include only published ones
 * @param boolean $is_galleryoption Set to true if this is a special gallery class option
 */
function printZenpagePageSelector($optionname, $optionname_custom = null, $published = false, $is_galleryoption = false) {
	$list = getZenpagePagesOptionsArray($published);
	$optionlabel = gettext('Select a Zenpage page. * denotes unpublished page.');
	$optionlabel_customfield = gettext('Custom page url');
	printSelectorWithCustomField($optionname, $list, $optionlabel, $optionname_custom, $optionlabel_customfield, $is_galleryoption);
}

/**
 * Gets an array of administrators ready for using with selector, radioboxes and checkbox lists
 * 
 * @since 1.5.8
 * 
 * @global object $_zp_authority
 * @param string $type 'users', 'groups', 'allusers'
 * @return type
 */
function getAdminstratorsOptionsArray($type = 'users') {
	global $_zp_authority;
	$list = array();
	$users = $_zp_authority->getAdministrators($type);
	$list[gettext('None')] = 'none';
	foreach ($users as $user) {
		if ($user['valid']) {
			if (empty($user['name'])) {
				$list[$user['user']] = $user['user'];
			} else {
				$list[$user['name'] . '(' . $user['user'] . ')'] = $user['user'];
			}
		}
	}
	return $list;
}

/**
 * Prints an select list option for users
 * 
 * it additionally prints a text field for a custom name
 * 
 * @since 1.5.8
 * 
 * @param string $optionname Name of the option, sued for the selector and the current selection
 * @param string $optionname_custom If defined this will be used for the custom url option, if null (default) the option name will be used with "_custom" appended
 * @param boolean $type 'users', 'groups', 'allusers'
 * @param boolean $is_galleryoption Set to true if this is a special gallery class option
 */
function printUserSelector($optionname, $optionname_custom, $type = 'users', $is_galleryoption = false) {
	$users = getAdminstratorsOptionsArray($type);
	$optionlabel = gettext('Select a user');
	$optionlabel_customfield = gettext('Custom');
	printSelectorWithCustomField($optionname, $users, $optionlabel, $optionname_custom, $optionlabel_customfield, $is_galleryoption);
}

/**
 * Prints option selectors for date and time formats
 * 
 * @since 1.6.1
 */
function printDatetimeFormatSelector() {
	$use_localized_date = getOption('date_format_localized');
	
	/*
	 * date format
	 */
	$date_selector_id = 'date_format_list';
	$date_currentformat_selector = $date_currentformat = getOption('date_format');
	$date_formats = array_keys(getStandardDateFormats('date'));
	$date_formatlist = getDatetimeFormatlistForSelector($date_formats, $use_localized_date);
	$date_formatlist[gettext('Custom')] = 'custom';
	
	// date custom format
	$date_custom_format_id = 'custom_dateformat_box';
	$date_custom_format_name = 'date_format';
	$date_custom_format_label = gettext('Custom date format');
	$date_custom_format_display = 'none';
	if (!in_array($date_currentformat, $date_formatlist)) {
		$date_currentformat_selector = 'custom';
		$date_custom_format_display = 'block';
	}
	/*if (in_array($date_currentformat, array('locale_preferreddate_time','locale_preferreddate_notime'))) {
		$time_formatlist_disabled = ' disabled="disabled"';
		$time_currentformat = '';
	} */
	?>
	<p>
		<label><select id="<?php echo $date_selector_id; ?>" name="<?php echo $date_selector_id; ?>" onchange="showfield(this, '<?php echo $date_custom_format_id; ?>')">
		<?php generateListFromArray(array($date_currentformat_selector), $date_formatlist, null, true); ?>
		</select> <?php echo gettext('Date format'); ?></label>
		<label id="<?php echo $date_custom_format_id; ?>" class="customText" style="display:<?php echo $date_custom_format_display; ?>">
			<br />
			<input type="text" size="30" name="<?php echo $date_custom_format_name; ?>" value="<?php echo html_encode($date_currentformat); ?>" />
			<?php echo $date_custom_format_label; ?>
		</label>
	</p>
	<?php
	/*
	 * time format
	 */
	$time_selector_id = 'time_format_list';
	$time_currentformat_selector = $time_currentformat = getOption('time_format');
	$time_formats = array_keys(getStandardDateFormats('time'));
	$time_formatlist = getDatetimeFormatlistForSelector($time_formats, $use_localized_date);	
	$time_formatlist[gettext('Custom')] = 'custom';

	
	// time custom format
	$time_custom_format_id = 'custom_timeformat_box';
	$time_custom_format_name = 'time_format';
	$time_custom_format_label = gettext('Custom time format');
	$time_custom_format_display = 'none';
	if (!in_array($time_currentformat, $time_formatlist)) {
		$time_currentformat_selector = 'custom';
		$time_custom_format_display = 'block';
	}
	?>
	<p>
		<label><select id="<?php echo $time_selector_id; ?>" name="<?php echo $time_selector_id; ?>" onchange="showfield(this, '<?php echo $time_custom_format_id; ?>')">
		<?php generateListFromArray(array($time_currentformat_selector), $time_formatlist, null, true); ?>
		</select> <?php echo gettext('Time format'); ?></label>
		<br>
		<label id="<?php echo $time_custom_format_id; ?>" class="customText" style="display:<?php echo $time_custom_format_display; ?>">
			<br />
			<input type="text" size="30" name="<?php echo $time_custom_format_name; ?>" value="<?php echo html_encode($time_currentformat); ?>" />
			<?php echo $time_custom_format_label; ?>
		</label>
	</p>
	<?php
}

/**
 * Helper functions for printDatetimeFormatSelector() ot create the format lists for the selector, not intended to be used standalone
 * 
 * @since 1.6.1
 * 
 * @param array $formats Array as created by array_keys(getStandardDateFormats($type);
 * @param bool $use_localized_date Default false, set to true to use localized datees
 * @return array
 */
function getDatetimeFormatlistForSelector($formats = array(), $use_localized_date = false) {
	$formatlist = array();
	foreach ($formats as $format) {
		if ($use_localized_date) {
			$formatlist[zpFormattedDate($format, '2023-03-05 15:30:30', true)] = $format;
		} else {
			$formatlist[zpFormattedDate($format, '2023-03-05 15:30:30', false)] = $format;
		}
	}
	return $formatlist;
}