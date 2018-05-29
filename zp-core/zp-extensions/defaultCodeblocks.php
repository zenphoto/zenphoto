<?php

/**
 * Supply default codeblocks to theme pages.
 *
 * This plugin provides a means to supply codeblock text for theme pages that
 * is "global" in context whereas normally you would have to insert the text
 * into each and every object.
 *
 * So you can, for instance, define a default codeblock 1 for "news articles"
 * and all your news articles will display that block. It can be overridden for
 * an individual news article by setting codeblock 1 for that article.
 *
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/defaultCodeblocks
 * @pluginCategory theme
 */
$plugin_is_filter = 500 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('Create default codeblocks.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'defaultCodeblocks';

zp_register_filter('codeblock', 'defaultCodeblocks::codeblock');

class defaultCodeblocks {

	var $codeblocks;
	var $blocks = array();
	var $currentObject = NULL;

	function __construct() {
		$this->blocks = array('gallery' => NULL, 'albums' => NULL, 'images' => NULL, 'news_category' => NULL, 'news' => NULL, 'pages' => NULL);
		$blocks = query_full_array("SELECT id, `subtype`, `aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'defaultCodeblocks'");
		foreach ($blocks as $block) {
			if ($block['subtype']) {
				$this->blocks[$block['subtype']] = $block['data'];
			} else {
				$oldoptions = getSerializedArray(getOption('defaultCodeblocks_objects'));
				foreach ($oldoptions as $object) {
					if ($object == 'page' || $object == 'image' || $object == 'album' || $object == 'news') {
						$object = $object . 's';
					}
					if (is_null($this->blocks[$object])) {
						$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `subtype`, `aux`,`data`) VALUES ("defaultCodeblocks",' . db_quote($object) . ',"",' . db_quote($block['data']) . ')';
						query($sql);
					}
					$this->blocks[$object] = $block['data'];
				}
				query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `id`=' . $block['id']);
				purgeOption('defaultCodeblocks_objects');
			}
		}
		foreach ($this->blocks as $object => $block) {
			if (is_null($block)) {
				$this->blocks[$object] = serialize(array());
				$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `subtype`, `aux`,`data`) VALUES ("defaultCodeblocks",' . db_quote($object) . ',"",' . db_quote($this->blocks[$object]) . ')';
				query($sql);
			}
		}
	}

	function getOptionsSupported() {
		$xlate = array('gallery' => gettext('Gallery'), 'albums' => gettext('Albums'), 'images' => gettext('Images'), 'news_category' => gettext('News Categories'), 'news' => gettext('Articles'), 'pages' => gettext('Pages'));

		foreach ($this->blocks as $object => $block) {
			$options [$xlate[$object]] = array('key' => 'defaultCodeblocks_' . $object, 'type' => OPTION_TYPE_CUSTOM,
					'order' => 2,
					'desc' => sprintf(gettext('Codeblocks to be inserted when the one for the <em>%s</ object is empty.'), $xlate[$object])
			);
		}
		codeblocktabsJS();
		return $options;
	}

	function handleOption($option, $currentValue) {
		$option = str_replace('defaultCodeblocks_', '', $option);
		$this->currentObject = $option;
		printCodeblockEdit($this, $option);
	}

	function handleOptionSave($themename, $themealbum) {
		if (zp_loggedin(CODEBLOCK_RIGHTS)) {
			foreach ($this->blocks as $object => $block) {
				$this->currentObject = $object;
				processCodeblockSave($object, $this);
			}
		}
		return false;
	}

	/**
	 * Returns the codeblocks as an serialized array
	 *
	 * @return array
	 */
	function getCodeblock() {
		return zpFunctions::unTagURLs($this->blocks[$this->currentObject]);
	}

	/**
	 * set the codeblocks as an serialized array
	 *
	 */
	function setCodeblock($cb) {
		$this->blocks[$this->currentObject] = zpFunctions::tagURLs($cb);
		$sql = 'UPDATE ' . prefix('plugin_storage') . ' SET `data`=' . db_quote($this->blocks[$this->currentObject]) . ' WHERE `type`="defaultCodeblocks" AND `subtype`=' . db_quote($this->currentObject);
		query($sql);
	}

	static function codeblock($current, $object, $number) {
		global $_defaultCodeBlocks;
		if (empty($current)) {
			if (!$_defaultCodeBlocks) {
				$_defaultCodeBlocks = new defaultCodeblocks();
			}
			$_defaultCodeBlocks->currentObject = $object->table;
			$blocks = getSerializedArray($_defaultCodeBlocks->getCodeblock());
			if (isset($blocks[$number])) {
				$current = $blocks[$number];
			}
		}
		return $current;
	}

}

?>