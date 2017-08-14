<?php

/*
 * This  is the root class for use by plugins to extend the database
 * table fields. The administrative tabs for the objects will have input items
 * for these new fields. They will be placed in the proximate location of the
 * "custom data" field on the page.
 *
 * Themes and plugins may access the custom fields using the normal "get" and "set" methods,
 * i.e. <code>$obj->getFieldname();</code> and <code>$obj->setFieldname($value);</code>
 *
 * Fields added to searchable objects will be included in the list of selectable search
 * fields. They will be enabled in the list by default. The standard search
 * form allows a visitor to choose to disable the field for a particular search.
 *
 * Fields are defined in the child class and passed as the <var>fields</var> array
 * parameter which consists of a multi-dimensional array, one row per object/field.
 * The elements of each row are:
 *
 * "table" is the database table name (without prefix) of the object to which the field is to be added.
 * "name" is the MySQL field name for the new field. It should be lower-case
 * "desc" is the "display name" of the field. If the value is NULL no edit field will show on the admin tab.
 * "type" is the database field type: int, varchar, tinytext, text, mediumtext, etc.
 * "searchDefault" determines if the field is "checked" in the <em>search behavior settings</em> <var>field list</var>.
 * "size" is the byte size of the varchar or int field (it is not needed for other types)
 * "edit" is is how the content is show on the edit tab. Values: multilingual, normal, function. If the value is NULL
 * there will be direct save of the result to the object
 * "function" is the function to call if the edit type is a function
 * "attribute" is the attribute(s) of the field, e.g. NOT NULL, UNSIGNED, etc.
 * "default" is the database "default" value
 * "bulkAction" allows the field to be set via bulk action drop-down lists. The element is set to an array of bulk actions
 *
 * 		each element is indexed by the display text for the drop-down selector has the "kind" of element:
 *
 * 				<i>display text</i> => 'mass_customText_data' procudes an input of type text
 *
 * 				<i>display text</i> => 'mass_customText_data' produces a text area input
 *
 * At present the only bulk action drop-down elements supported are the ones on the albums, images, pages, and articles tabs.
 * The only field handling provided is a simple text input selected by <var>mass_customText_data</var>.
 * This could be expanded in the future if other input types are desired.
 *
 * The <i>editor function</i> will be passed three parameters: the object, the $_POST instance, the field array,
 * and the action: "edit" or "save". The function must return an array of the the processed data to be displayed and a format indicator or  the data to be saved.
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
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
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
		if (OFFSET_PATH == 2) {
			//clean up creator fields
			$sql = 'UPDATE ' . prefix('options') . ' SET `creator`=' . db_quote(replaceScriptPath(__FILE__) . '[' . __LINE__ . ']') . ' WHERE `name`=' . db_quote($me . '_addedFields') . ' AND `creator` IS NULL;';
			query($sql);
		}
		$utf8mb4 = version_compare(MySQL_VERSION, '5.5.3', '>=');

		$database = array();
		foreach (getDBTables() as $table) {
			$tablecols = db_list_fields($table);
			foreach ($tablecols as $key => $datum) {
				$database[$table][$datum['Field']] = $datum;
			}
		}
		$current = $fields = $searchDefault = array();
		if (extensionEnabled($me)) { //need to update the database tables.
			foreach ($newfields as $newfield) {
				$table = $newfield['table'];
				$name = $newfield['name'];
				if (!$existng = isset($database[$table][$name])) {
					if (isset($newfield['searchDefault']) && $newfield['searchDefault']) {
						$searchDefault[] = $name;
					}
				}
				if (is_null($newfield['type'])) {
					if ($name == 'tags') {
						setOption('adminTagsTab', 1);
					}
				} else {
					switch (strtolower($newfield['type'])) {
						default:
							$dbType = strtoupper($newfield['type']);
							break;
						case 'int':
							$dbType = strtoupper($newfield['type']) . '(' . min(255, $newfield['size']) . ')';
							if (isset($newfield['attribute'])) {
								$dbType.=' ' . $newfield['attribute'];
								unset($newfield['attribute']);
							}
							break;
						case 'varchar':
							$dbType = strtoupper($newfield['type']) . '(' . min(255, $newfield['size']) . ')';
							break;
					}
					if ($existng) {
						if (strtoupper($database[$table][$name]['Type']) != $dbType || empty($database[$table][$name]['Comment'])) {
							$cmd = ' CHANGE `' . $name . '`';
						} else {
							$cmd = NULL;
						}
						unset($database[$table][$name]);
					} else {
						$cmd = ' ADD COLUMN';
					}
					$sql = 'ALTER TABLE ' . prefix($newfield['table']) . $cmd . ' `' . $name . '` ' . $dbType;
					if ($utf8mb4 && ($dbType == 'TEXT' || $dbType == 'LONGTEXT')) {
						$sql .= ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
					}
					if (isset($newfield['attribute']))
						$sql.= ' ' . $newfield['attribute'];
					if (isset($newfield['default']))
						$sql.= ' DEFAULT ' . $newfield['default'];
					$sql .= " COMMENT 'optional_$me'";
					if ((!$cmd || setupQuery($sql)) && in_array($newfield['table'], array('albums', 'images', 'news', 'news_categories', 'pages'))) {
						$fields[] = strtolower($newfield['name']);
					}
					$current[$newfield['table']][$newfield['name']] = $dbType;
				}
			}
			setOption(get_class($this) . '_addedFields', serialize($current));
			if (!empty($searchDefault)) {
				$fieldExtenderMutex = new zpMutex('fE');
				$fieldExtenderMutex->lock();
				$engine = new SearchEngine();
				$set_fields = $engine->allowedSearchFields();
				$set_fields = array_unique(array_merge($set_fields, $searchDefault));
				setOption('search_fields', implode(',', $set_fields));
				$fieldExtenderMutex->unlock();
			}
		} else {
			purgeOption(get_class($this) . '_addedFields');
		}

		foreach ($database as $table => $fields) { //drop fields no longer defined
			foreach ($fields as $field => $orphaned) {
				if ($orphaned['Comment'] == "optional_$me") {
					$sql = 'ALTER TABLE ' . prefix($table) . ' DROP `' . $field . '`';
					setupQuery($sql);
				}
			}
		}
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
	 * The generic field element save handler
	 * @param type $obj
	 * @param type $instance
	 * @param type $fields
	 */
	static protected function _saveHandler($obj, $instance, $field) {
		if (array_key_exists('edit', $field)) {
			$action = $field['edit'];
			if (is_null($action)) {
				return NULL;
			}
		} else {
			$action = 'default';
		}

		switch ($action) {
			case'multilingual':
				$newdata = process_language_string_save($instance . '-' . $field['name']);
				break;
			case'function':
				$newdata = call_user_func($field['function'], $obj, $instance, $field, 'save');
				break;
			default:
				if (!is_null($instance)) {
					$instance = '_' . $instance;
				}
				if (isset($_POST[$field['name'] . $instance])) {
					$newdata = sanitize($_POST[$field['name'] . $instance]);
				} else {
					$newdata = NULL;
				}
		}

		return $newdata;
	}

	/**
	 * generic handler for the edit fields
	 * @param $obj
	 * @param $instance
	 * @param type $field
	 * @return type
	 */
	static protected function _editHandler($obj, $field, $instance) {
		if (array_key_exists('edit', $field)) {
			$action = $field['edit'];
			if (is_null($action)) {
				return array(NULL, NULL);
			}
		} else {
			$action = 'default';
		}

		switch ($action) {
			case 'multilingual':
				ob_start();
				print_language_string_list($obj->get($field['name']), $instance . '-' . $field['name'], false, NULL, '', '100%');
				$item = ob_get_contents();
				ob_end_clean();
				$formatted = true;
				break;
			case'function':
				$item = call_user_func($field['function'], $obj, $instance, $field, 'edit');
				if (is_null($item)) {
					$formatted = NULL;
				} else {
					$formatted = true;
				}
				break;
			default:
				if ($instance)
					$instance = '_' . $instance;
				$item = html_encode($obj->get($field['name']));
				$formatted = false;
				break;
		}
		return array($item, $formatted);
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
				if ($field['table'] == 'administrators') {
					$olddata = $userobj->get($field['name']);
					$newdata = fieldExtender::_saveHandler($userobj, $i, $field);
					if (!is_null($newdata))
						$userobj->set($field['name'], $newdata);
					if ($olddata != $newdata) {
						$updated = true;
					}
				}
			}
			return $updated;
		}
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
				list($item, $formatted) = fieldExtender::_editHandler($userobj, $field, $i);
				if (!is_null($formatted)) {
					$input = '<fieldset>' .
									'<legend>' . $field['desc'] . '</legend>';
					if ($formatted) {
						$html .= $item;
					} else {
						if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
							$input .= '<input name = "' . $field['name'] . '_' . $i . '" type = "text" style="width:98%;" value = "' . $item . '" />';
						} else {
							$input .= '<textarea name = "' . $field['name'] . '_' . $i . '" cols = "' . TEXTAREA_COLUMNS . '"rows = "1">' . $item . '</textarea>';
						}
					}
					$input .='</fieldset>';
					$list[] = $input;
				}
			}
		}
		if (($count = count($list)) % 2) {
			$list[] = '';
		}
		if (!empty($list)) {

			$output = array_chunk($list, round($count / 2));


			$html .=
							'<div class="user_left">' .
							implode($output[0], "\n") .
							'</div>';

			if (!empty($output[1])) {
				$html .=
								'<div class="user_right">' .
								implode($output[1], "\n") .
								'</div>';
			}
			$html .= '<br class="clearall">';
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
				$newdata = fieldExtender::_saveHandler($object, $i, $field);
				if (!is_null($newdata)) {
					$object->set($field['name'], $newdata);
				}
			}
		}
		return $object;
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
				list($item, $formatted) = fieldExtender::_editHandler($object, $field, $i);
				if (!is_null($formatted)) {
					$html .= '<tr>' . "\n" . '<td><span class="nowrap">' . $field['desc'] . "</span></td>\n<td>";
					if ($formatted) {
						$html .= $item;
					} else {
						if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
							$html .= '<input name="' . $field['name'] . '_' . $i . '" type = "text" style = "width:100%;" value = "' . $item . '" />';
						} else {
							$html .= '<textarea name="' . $field['name'] . '_' . $i . '" style = "width:100%;" rows = "6">' . $item . '</textarea>';
						}
					}
					$html .="</td>\n</tr>\n";
				}
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
	static function _cmsItemSave($custom, $object, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				$newdata = fieldExtender::_saveHandler($object, NULL, $field);
				if (!is_null($newdata))
					$object->set($field['name'], $newdata);
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
	static function _cmsItemEdit($html, $object, $fields) {
		foreach ($fields as $field) {
			if ($field['table'] == $object->table) {
				list($item, $formatted) = fieldExtender::_editHandler($object, $field, NULL);
				if (!is_null($formatted)) {
					$html .= "<tr>\n" .
									'<td><span class="leftcolumn nowrap">' . $field['desc'] . "</span></td>\n" .
									'<td>' . "\n";
					if ($formatted) {
						$html .= $item;
					} else {
						if (in_array(strtolower($field['type']), array('varchar', 'int', 'tinytext'))) {
							$html .= '<input name="' . $field['name'] . '" type="text" style = "width:97%;"
value="' . $item . '" />';
						} else {
							$html .= '<textarea name = "' . $field['name'] . '" style = "width:100%;" "rows="6">' . $item . '</textarea>';
						}
					}
					$html .= "</td>\n" .
									"</tr>\n";
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
		$actions = $items = array();
		foreach ($fields as $field) {
			$items[$field['table']] = true;
			if (isset($field['bulkAction'])) {
				$actions[$field['table']] = true;
			}
		}
		$registerCMSSave = false;

		if (isset($items['albums'])) {
			zp_register_filter("save_album_utilities_data", "$me::mediaItemSave");
			zp_register_filter("edit_album_custom_data", "$me::mediaItemEdit");
			if (isset($actions['albums'])) {
				zp_register_filter('bulk_album_actions', "$me::bulkAlbum");
				zp_register_filter('processBulkAlbumsSave', "$me::bulkAlbumSave");
			}
		}
		if (isset($items['images'])) {
			zp_register_filter("save_image_utilities_data", "$me::mediaItemSave");
			zp_register_filter("edit_image_custom_data", "$me::mediaItemEdit");
			if (isset($actions['images'])) {
				zp_register_filter('bulk_image_actions', "$me::bulkImage");
				zp_register_filter('processBulkImageSave', "$me::bulkImageSave");
			}
		}
		if (isset($items['administrators'])) {
			zp_register_filter("save_admin_custom_data", "$me::adminSave");
			zp_register_filter("edit_admin_custom_data", "$me::adminEdit");
			//there are no admin bulk actions currently
		}
		if (isset($items['news'])) {
			zp_register_filter("save_article_custom_data", "$me::cmsItemSave");
			zp_register_filter("edit_article_custom_data", "$me::cmsItemEdit");
			if (isset($actions['news'])) {
				zp_register_filter('bulk_article_actions', "$me::bulkArticle");
				$registerCMSSave = true;
			}
		}

		if (isset($items['pages'])) {
			zp_register_filter("save_page_custom_data", "$me::cmsItemSave");
			zp_register_filter("edit_page_custom_data", "$me::cmsItemEdit");
			if (isset($actions['pages'])) {
				zp_register_filter('bulk_page_actions', "$me::bulkPage");
				$registerCMSSave = true;
			}
		}
		if ($registerCMSSave) {
			zp_register_filter('processBulkCMSSave', "$me::bulkCMSSave");
		}

		if (OFFSET_PATH && !getOption($me . "_addedFields")) {
			requestSetup($me);
		}
	}

	/**
	 * Returns an array with the content of the custom fields for the object
	 * @param object $obj
	 * @param array $fields
	 * @return array
	 */
	static function _getCustomDataset($obj, $fields) {
		$result = array();
		foreach ($fields as $element) {
			if ($element['table'] == $obj->table) {
				$result[$element['name']] = $obj->get($element['name']);
			}
		}
		return $result;
	}

	static function _setCustomDataset($obj, $values) {
		foreach ($values as $field => $value) {
			$obj->set($field, $value);
		}
	}

	static function getField($field, $object = NULL, &$detail = NULL, $fields) {
		global $_zp_current_admin_obj, $_zp_current_album, $_zp_current_image
		, $_zp_current_article, $_zp_current_page, $_zp_current_category;
		$objects = $tables = array();
		if (is_null($object)) {
			if (in_context(ZP_IMAGE)) {
				$object = $_zp_current_image;
				$objects[$tables[] = 'albums'] = $_zp_current_album;
			} else if (in_context(ZP_ALBUM)) {
				$object = $_zp_current_album;
			} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
				$object = $_zp_current_article;
				if ($_zp_current_category)
					$objects[$tables[] = 'news_categories'] = $_zp_current_category;
			} else if (in_context(ZP_ZENPAGE_PAGE)) {
				$object = $_zp_current_page;
			} else if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$object = $_zp_current_category;
			} else {
				zp_error(gettext('There is no defined context, you must pass a comment object.'));
			}
		}

		$tables[] = $object->table;
		$objects[$object->table] = $object;
		$field = strtolower($field);

		foreach ($fields as $try) {
			if ($field == strtolower($try['name']) && in_array($try['table'], $tables)) {
				$detail = $try;
				$object = $objects[$try['table']];
				break;
			}
		}
		if (isset($detail)) {
			return get_language_string($object->get($detail['name']));
		} else {
			zp_error(gettext('Field not defined.'));
		}
	}

	static function bulkActions($checkarray, $table, $fields) {
		foreach ($fields as $key => $data) {
			if ($data['table'] == $table && isset($data['bulkAction'])) {
				$bulk = $data['bulkAction'];
				foreach ($bulk as $title => $action) {
					switch ($action) {
						case 'mass_customTextarea_data':
						case 'mass_customText_data':
							$item = array(
									'name' => $data['name'],
									'desc' => $data['desc'],
									'action' => $action
							);
							if (isset($data['size'])) {
								$item['size'] = $data['size'];
							}
							$bulk[$title] = $item;
							break;
						default:
							//will be whatever the "standard" actions do, most likely a checkbox
							$bulk[$title] = $action;
					}
				}
				$checkarray = array_merge($checkarray, $bulk);
			}
		}
		return $checkarray;
	}

	static function bulkSave($result, $action, $table, $addl, $fields) {
		if ($action) {
			foreach ($fields as $key => $data) {
				if ($data['table'] == $table && $data['name'] == $action && isset($data['bulkAction'])) {
					$result = sanitize($_POST[$action]);
				}
			}
		}
		return $result;
	}

}

?>
