<?php

/**
 * Supply default codeblocks to theme pages.
 *
 * @package plugins
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage misc
 */
$plugin_is_filter = 500 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('Create default codeblocks.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'defaultCodeblocks';

zp_register_filter('codeblock', 'defaultCodeblocks_codebox');

class defaultCodeblocks {

	var $codeblocks;

	function __construct() {
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
		$list = array(gettext('Gallery') => 'defaultCodeblocks_object_gallery', gettext('Album') => 'defaultCodeblocks_object_albums', gettext('Image') => 'defaultCodeblocks_object_images');
		if (extensionEnabled('zenpage')) {
			$list = array_merge($list, array(gettext('News category') => 'defaultCodeblocks_object_news_categories', gettext('News') => 'defaultCodeblocks_object_news', gettext('Page') => 'defaultCodeblocks_object_pages'));
		}
		$options = array(gettext('Objects')		 => array('key'				 => 'defaultCodeblocks_objects', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										'order'			 => 0,
										'checkboxes' => $list,
										'desc'			 => gettext('Default codeblocks will be applied for the checked objects.')),
						gettext('Codeblocks')	 => array('key'		 => 'defaultCodeblocks_blocks', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 2,
										'desc'	 => gettext('Codeblocks to be inserted when the one for the object is empty.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		codeblocktabsJS();
		printCodeblockEdit($this, 0);
	}

	function handleOptionSave($themename, $themealbum) {
		if (zp_loggedin(CODEBLOCK_RIGHTS)) {
			$this->setCodeblock(processCodeblockSave(0));
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
	if (empty($current) && getOption('defaultCodeblocks_object_' . $object->table)) {
		$defaultCodeBlocks = new defaultCodeblocks();
		$blocks = getSerializedArray($defaultCodeBlocks->getCodeblock());
		if (isset($blocks[$number])) {
			$current = $blocks[$number];
		}
	}
	return $current;
}

?>