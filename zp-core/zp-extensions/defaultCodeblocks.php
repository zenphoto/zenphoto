<?php

/**
 * Supply default codeblocks to theme pages.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage theme
 */
$plugin_is_filter = 500 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('Create default codeblocks.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'defaultCodeblocks';

zp_register_filter('codeblock', 'defaultCodeblocks_codebox');

class defaultCodeblocks {

	var $codeblocks;
	var $enabled = array();

	function __construct() {
		if (OFFSET_PATH == 2) {
			$list = getOptionsLike('defaultcodeblocks_object_');
			$objects = array();
			foreach ($list as $option => $value) {
				if ($value) {
					$object = str_replace('defaultcodeblocks_object_', '', $option);
					$objects[$object] = $object;
				}
				purgeOption($option);
			}
			setOptionDefault('defaultCodeblocks_objects', serialize($objects));
		}
		$blocks = query_single_row("SELECT id, `aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'defaultCodeblocks'");
		if ($blocks) {
			$this->codeblocks = $blocks['data'];
		} else {
			$this->codeblocks = serialize(array());
			$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`,`aux`,`data`) VALUES ("defaultCodeblocks","",' . db_quote($this->codeblocks) . ')';
			query($sql);
		}
	}

	static function getOptionsSupported() {
		$list = array(gettext('Gallery') => 'gallery', gettext('Album') => 'albums', gettext('Image') => 'images');
		if (extensionEnabled('zenpage')) {
			$list = array_merge($list, array(gettext('News category') => 'news_categories', gettext('News') => 'news', gettext('Page') => 'pages'));
		}
		$options = array(gettext('Objects') => array('key' => 'defaultCodeblocks_objects', 'type' => OPTION_TYPE_CHECKBOX_ULLIST,
						'order' => 0,
						'checkboxes' => $list,
						'desc' => gettext('Default codeblocks will be applied for the checked objects.')),
				gettext('Codeblocks') => array('key' => 'defaultCodeblocks_blocks', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 2,
						'desc' => gettext('Codeblocks to be inserted when the one for the object is empty.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		codeblocktabsJS();
		printCodeblockEdit($this, 0);
	}

	function handleOptionSave($themename, $themealbum) {
		if (zp_loggedin(CODEBLOCK_RIGHTS)) {
			processCodeblockSave(0, $this);
		}
		return false;
	}

	/**
	 * Returns the codeblocks as an serialized array
	 *
	 * @return array
	 */
	function getCodeblock() {
		return zpFunctions::unTagURLs($this->codeblocks);
	}

	/**
	 * set the codeblocks as an serialized array
	 *
	 */
	function setCodeblock($cb) {
		$this->codeblocks = zpFunctions::tagURLs($cb);
		$sql = 'UPDATE ' . prefix('plugin_storage') . ' SET `data`=' . db_quote($this->codeblocks) . ' WHERE `type`="defaultCodeblocks"';
		query($sql);
	}

}

function defaultCodeblocks_codebox($current, $object, $number) {
	global $_defaultCodeBlocks, $_enabledCodeblockTables;
	if (is_null($_enabledCodeblockTables)) {
		$_enabledCodeblockTables = getSerializedArray(getOption('defaultCodeblocks_objects'));
	}
	if (empty($current) && isset($_enabledCodeblockTables[$object->table])) {
		if (!$_defaultCodeBlocks) {
			$_defaultCodeBlocks = new defaultCodeblocks();
		}
		$blocks = getSerializedArray($_defaultCodeBlocks->getCodeblock());
		if (isset($blocks[$number])) {
			$current = $blocks[$number];
		}
	}
	return $current;
}

?>