<?php
/**
 * Provide javaScript tag "suggestions" based on Remy Sharp's {@link http://remysharp.com/2007/12/28/jquery-tag-suggestion/ jQuery Tag Suggestion} plugin.
 *
 * Activate the plugin and the feature is available on the theme's search field.
 * It is also available on each item edit page's tag selector.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard) - an adaption of Remy Sharp's jQuery Tag Suggestion
 * @package plugins
 * @subpackage theme
 */
$plugin_is_filter = defaultExtension(9 | THEME_PLUGIN);
$plugin_description = gettext("Enables jQuery tag suggestions on the search field.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

zp_register_filter('theme_head', 'tagSuggestJS');
zp_register_filter('admin_head', 'tagSuggestJS');

function tagSuggestJS() {
	// the scripts needed
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tag_suggest/encoder.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tag_suggest/tag.js"></script>
	<?php
	$css = getPlugin('tag_suggest/tag.css', true, true);
	?>
	<link type="text/css" rel="stylesheet" href="<?php echo pathurlencode($css); ?>" />
	<?php
	$taglist = getAllTagsUnique(OFFSET_PATH ? false : NULL, !OFFSET_PATH);
	$tags = array();
	foreach ($taglist as $tag) {
		$tags[] = addslashes($tag);
	}

	if (OFFSET_PATH || getOption('search_space_is') == 'OR') {
		$tagseparator = ' ';
	} else {
		$tagseparator = ',';
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var _tagList = ["<?php echo implode($tags, '","'); ?>"];
		$(function () {
			$('#search_input, #edit-editable_4, .tagsuggest').tagSuggest({separator: '<?php echo $tagseparator; ?>', tags: _tagList})
		});
		// ]]> -->
	</script>
	<?php
}
?>