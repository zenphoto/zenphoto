<?php
/**
 * Provide javaScript tag "suggestions" based on Remy Sharp's {@link http://remysharp.com/2007/12/28/jquery-tag-suggestion/ jQuery Tag Suggestion} plugin.
 *
 * Activate the plugin and the feature is available on the theme's search field.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard) - an adaption of Remy Sharp's jQuery Tag Suggestion
 * @package plugins
 */

$plugin_description = gettext("Enables jQuery tag suggestions on the search field.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

zp_register_filter('theme_head','tagSuggestJS');

function tagSuggestJS() {
	// the scripts needed
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/encoder.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/tag.js"></script>
	<?php
	$css = getPlugin('tag_suggest/tag.css', true, true);
	?>
	<link type="text/css" rel="stylesheet" href="<?php echo pathurlencode($css); ?>" />
	<?php
	$taglist = getAllTagsUnique();
	$c = 0;
	$list = '';
	foreach ($taglist AS $tag) {
		if ($c>0) $list .= ',';
		$c++;
		$list .= '"'.addslashes($tag).'"';
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var _tagList = [<?php  echo $list; ?>];
		$(function () {
			$('#search_input, #edit-editable_4').tagSuggest({ separator:'<?php  echo (getOption('search_space_is')=='OR'?' ':','); ?>', tags: _tagList })
			});
		// ]]> -->
	</script>
	<?php
}
?>