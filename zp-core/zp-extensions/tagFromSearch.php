<?php
/**
 * This plugin provides a means to tag objects found by a search. In addition, it
 * (optionally) forces searches by visitors and users without <i>Tags</i> rights to be limited to the
 * tags field.
 *
 * Thus you can apply a unique tag to results of a search so that they are "related" to
 * each other. If you have selected the optional <i>Tags only</i> search <i>normal</i> viewers are limited
 * to searching for defined tags.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage media
 *
 * Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
$plugin_is_filter = 9 | FEATURE_PLUGIN;
$plugin_description = gettext('Facilitates assigning unique tags to related objects.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'tagFromSearch';

class tagFromSearch {

	function getOptionsSupported() {

		$options = array(gettext('Tags only searches') => array('key'	 => 'tagFromSearch_tagOnly', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Restrict viewer searches to the <code>tags</code> field unless they have <em>Tags rights</em>.'))
		);
		return $options;
	}

	static function toolbox($zf) {
		global $_zp_current_search;
		if (zp_loggedin(TAGS_RIGHTS)) {
			?>
			<li>
				<a href="<?php echo $zf . '/' . PLUGIN_FOLDER . '/tagFromSearch/tag.php?' . substr($_zp_current_search->getSearchParams(), 1); ?>" title="<?php echo gettext('Tag items found by the search'); ?>" ><?php echo gettext('Tag items'); ?></a>
			</li>
			<?php
		}
		return $zf;
	}

	static function head() {
		if (!zp_loggedin(TAGS_RIGHTS)) {
			if (getOption('tagFromSearch_tagOnly'))
				setOption('search_fields', 'tags', false);
		}
	}

}

zp_register_filter('feature_plugin_load', 'tagFromSearch::head');
zp_register_filter('admin_toolbox_search', 'tagFromSearch::toolbox');
?>