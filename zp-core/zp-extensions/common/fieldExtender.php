<?php

/*
 * This  is the root class for use by plugins to extend the Zenphoto database
 * table fields. The administrative tabs for the objects will have input items
 * for these new fields. They will be placed in the proximate location of the
 * "custom data" field on the page.
 *
 * Fields added to searchable objects will be included in the list of selectable search
 * fields. They will be enabled in the list by default. The standard search
 * form allows a visitor to choose to disable the field for a particular search.
 *
 * Since the Zenphoto objects are not directly aware of these new fields, themes
 * must use the "get()" methods to retrieve the content for display. E.g.
 * <code>echo $_zp_current_album->get('new_field');</code>
 *
 * Fields are defined in the child class and passed as the <var>fields</var> array
 * parameter which consists of a multi-dimensional array, one row per object/field.
 * The elements of each row are:
 *
 * "table" is the database table name (without prefix) of the object to which the field is to be added.
 * "name" is the MySQL field name for the new field
 * "desc" is the "display name" of the field
 * "type" is the database field type: int, varchar, tinytext, text, mediumtext, and longtext.
 * "size" is the byte size of the varchar or int field (it is not needed for other types)
 *
 * Database fields names must conform to
 * {@link http://dev.mysql.com/doc/refman/5.0/en/identifiers.html MySQL field naming rules}.
 *
 * The <var>constructor($fields)</var> method establishes the fields in the database.
 * It is recommended that the plugin invoke this method from its class <var>__constructor<var>
 * method and that the the class be instantiated when the plugin is loaded from
 * the <em>setup</em> plugin options processing (e.g. when <var>OFFSET_PATH</var>==2.
 * The <var>constructor</var> method will check if the plugin is enabled. If so
 * it adds the fields, if not it removes any previously added fields.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 *
 */

class fieldExtender {

	/**
	 *
	 * This method establishes the current set of database fields. It will add the
	 * fields to the database if they are not already present. Fields from previous
	 * constructor calls that are no longer in the list will be removed from the
	 * database (along with any data associated with them.)
	 *
	 * @param array $newfields
	 */
	function constructor($me, $newfields) {
		$previous = getSerializedArray(getOption(get_class($this) . '_addedFields'));
		$current = $fields = array();
		if (extensionEnabled($me)) { //need to update the database tables.
			foreach ($newfields as $newfield) {
				$current[$newfield['table']][$newfield['name']] = true;
				unset($previous[$newfield['table']][$newfield['name']]);
				switch (strtolower($newfield['type'])) {
					default:
						$dbType = strtoupper($newfield['type']);
						break;
					case 'int':
					case 'varchar':
						$dbType = strtoupper($newfield['type']) . '(' . min(255, $newfield['size']) . ')';
						break;
				}
				$sql = 'ALTER TABLE ' . prefix($newfield['table']) . ' ADD COLUMN `' . $newfield['name'] . '` ' . $dbType;
				if (query($sql, false) && in_array($newfield['table'], array('albums', 'images', 'news', 'news_categories', 'pages')))
					$fields[] = strtolower($newfield['name']);
			}
			setOption(get_class($this) . '_addedFields', serialize($current));
		} else {
			purgeOption(get_class($this) . '_addedFields');
		}

		$set_fields = array_flip(explode(',', getOption('search_fields')));
		foreach ($previous as $table => $orpahed) { //drop fields no longer defined
			foreach ($orpahed as $field => $v) {
				unset($set_fields[$field]);
				$sql = 'ALTER TABLE ' . prefix($table) . ' DROP `' . $field . '`';
				query($sql, false);
			}
		}
		$set_fields = array_unique(array_merge($fields, array_flip($set_fields)));
		setOption('search_fields', implode(',', $set_fields));
	}

	/**
	 * Updates the list of search fields to include the new fields
	 * @param array $list the list of fields as known to the search engine
	 * @return array
	 */
	static function _addToSearch($list, $fields) {
		foreach ($fields as $newfield) {
			if (in_array($newfield['table'], array('albums', 'images', 'news', 'news_categories', 'pages'))) {
				$list[strtolower($newfield['name'])] = $newfield['desc'];
			}
		}
		return $list;
	}

	/**
	 * Process the save of user object type elements
	 *
	 * @param boolean $updated
	 * @param object $userobj
	 * @param int $i
	 * @param boolean $alter
	 * @return boolean
	 */
	static function _adminSave($updated, $userobj, $i, $alter, $fields) {
		if ($userobj->getValid()) {
			foreach ($fields as $field) {
				if (isset($_POST[$field['name'] . '_' . $i])) {
					if ($field['table'] == 'administrators') {
						$olddata = $userobj->get($field['name']);
						$userobj->set($field['name'], $newdata = $_POST[$field['name'] . '_' . $i]);
						if ($olddata != $newdata) {
							$updated = true;
						}
					}
				}
			}
		}
		return $updated;
	}

	/**
	 * Displays the edit fields for user type objects
	 *
	 * @param string $html
	 * @param object $userobj
	 * @param int $i
	 * @param string $background
	 * @param boolean $current
	 * @return string
	 */
	static function _adminEdit($html, $userobj, $i, $background, $current, $fields) {
		$list = array();
		foreach ($fields as $field) {
			if ($field['table'] == 'administrators') {
				$input = '<fieldset>' .
								'<legend>' . $field['desc'] . '</legend>';
				if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
					$input .= '<input name = "' . $field['name'] . '_' . $i . '" type = "text" size = "' . TEXT_INPUT_SIZE . '" value = "' . html_encode($userobj->get($field['name'])) . '" />';
				} else {
					$input .= '<textarea name = "' . $field['name'] . '_' . $i . '" cols = "' . TEXTAREA_COLUMNS . '"rows = "1">' . html_encode($userobj->get($field['name'])) . '</textarea>';
				}

				$input .='</fieldset>';
				$list[] = $input;
			}
		}
		if (($count = count($list)) % 2) {
			$list[] = '';
		}

		if (!empty($list)) {
			for ($key = 0; $key < $count; $key = $key + 2) {
				$html .=
								'<tr' . ((!$current) ? ' style = "display:none;"' : '') . ' class = "userextrainfo">' .
								'<td width = "20%"' . ((!empty($background)) ? ' style = "' . $background . '"' : '') . ' valign = "top">' .
								$list[$key] .
								'</td>' .
								'<td ' . ((!empty($background)) ? ' style = "' . $background . '"' : '') . ' valign = "top">' .
								$list[$key + 1] .
								'</td>' .
								'</tr>';
			}
		}
		return $html;
	}

	/**
	 * Processes the save of image and album objects
	 * @param object $object
	 * @param int $i
	 */
	static function _mediaItemSave($object, $i, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				$olddata = $object->get($field['name']);
				$object->set($field['name'], $newdata = $_POST[$field['name'] . '_' . $i]);
				if ($olddata != $newdata) {
					$updated = true;
				}
			}
		}
	}

	/**
	 * Displays the edit fields for image and album objects
	 *
	 * @param string $html
	 * @param object $object
	 * @param int $i
	 * @return string
	 */
	static function _mediaItemEdit($html, $object, $i, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				$html .= '<tr><td>' . $field['desc'] . '</td><td>';
				if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
					$html .= '<input name = "' . $field['name'] . '_' . $i . '" type = "text" style = "width:100%;" value = "' . html_encode($object->get($field['name'])) . '" />';
				} else {
					$html .= '<textarea name = "' . $field['name'] . '_' . $i . '" style = "width:100%;" rows = "6">' . html_encode($object->get($field['name'])) . '</textarea>';
				}

				$html .='</td></tr>';
			}
		}
		return $html;
	}

	/**
	 * Processes the save of zenpage objects
	 *
	 * @param string $custom
	 * @param object $object
	 * @return string
	 */
	static function _zenpageItemSave($custom, $object, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				$olddata = $object->get($field['name']);
				$object->set($field['name'], $newdata = $_POST[$field['name']]);
				if ($olddata != $newdata) {
					$updated = true;
				}
			}
		}
		return $custom;
	}

	/**
	 * Displays the edit fields for zenpage objects
	 *
	 * @param string $html
	 * @param object $object
	 * @return string
	 */
	static function _zenpageItemEdit($html, $object, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				$html .= '<tr><td>' . $field['desc'] . '</td><td>';
				if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
					$html .= '<input name="' . $field['name'] . '" type="text" style = "width:97%;"
value="' . html_encode($object->get($field['name'])) . '" />';
				} else {
					$html .= '<textarea name = "' . $field['name'] . '" style = "width:97%;" "rows="6">' . html_encode($object->get($field['name'])) . '</textarea>';
				}
			}
		}
		return $html;
	}

	/**
	 * registers filters for handling display and edit of objects as appropriate
	 */
	static function _register($me, $fields) {
		zp_register_filter('searchable_fields', "$me::addToSearch");
		$items = array();
		foreach ($fields as $field) {
			$items[$field['table']] = true;
		}
		if (isset($items['albums'])) {
			zp_register_filter("save_album_utilities_data", "$me::mediaItemSave");
			zp_register_filter("edit_album_custom_data", "$me::mediaItemEdit");
		}
		if (isset($items['images'])) {
			zp_register_filter("save_image_utilities_data", "$me::mediaItemSave");
			zp_register_filter("edit_image_custom_data", "$me::mediaItemEdit");
		}
		if (isset($items['administrators'])) {
			zp_register_filter("save_admin_custom_data", "$me::adminSave");
			zp_register_filter("edit_admin_custom_data", "$me::adminEdit");
		}
		if (isset($items['news'])) {
			zp_register_filter("save_article_custom_data", "$me::zenpageItemSave");
			zp_register_filter("edit_article_custom_data", "$me::zenpageItemEdit");
		}
		if (isset($items['news_categories'])) {
			zp_register_filter("save_category_custom_data", "$me::zenpageItemSave");
			zp_register_filter("edit_category_custom_data", "$me::zenpageItemEdit");
		}
		if (isset($items['pages'])) {
			zp_register_filter("save_page_custom_data", "$me::zenpageItemSave");
			zp_register_filter("edit_page_custom_data", "$me::zenpageItemEdit");
		}
		if (OFFSET_PATH && !getOption($me . "_addedFields")) {
			zp_register_filter('admin_note', "$me::adminNotice");
		}
	}

	/**
	 * Notification of need to run setup
	 * @param type $tab
	 * @param type $subtab
	 * @param type $me
	 * @return type
	 */
	static function _adminNotice($tab, $subtab, $me) {
		echo '<p class="notebox">' . sprintf(gettext('You will need to run <a href="%1$s">setup</a> to update the database with the custom fields defined by the <em>%2$s</em> plugin.'), FULLWEBPATH . '/' . ZENFOLDER . '/setup.php', $me) . '</p>';
		return $tab;
	}

	/**
	 * Returns an array with the content of the custom fields for the object
	 * @param object $obj
	 * @param array $fields
	 * @return array
	 */
	static function _getCustomData($obj, $fields) {
		$result = array();
		foreach ($fields as $element) {
			if ($element['table'] == $obj->table) {
				$result[$element['name']] = $obj->get($element['name']);
			}
		}
		return $result;
	}

	static function _setCustomData($obj, $values) {
		foreach ($values as $field => $value) {
			$obj->set($field, $value);
		}
	}

}

?>
