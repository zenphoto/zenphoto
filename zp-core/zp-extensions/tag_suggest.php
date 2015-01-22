<?php
/**
 * Provide JavaScript tag "suggestions" based on Remy Sharp's {@link http://remysharp.com/2007/12/28/jquery-tag-suggestion/ jQuery Tag Suggestion} plugin.
 *
 * Activate the plugin and the feature is available on the theme's search field.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard) - an adaption of Remy Sharp's jQuery Tag Suggestion
 * @package plugins
 */
$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext("Enables jQuery tag suggestions on the search field.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$option_interface = 'tagsuggest';
zp_register_filter('theme_head', 'tagSuggestJS_frontend');
zp_register_filter('admin_head', 'tagSuggestJS_admin');

class tagsuggest {

  function __construct() {
    setOptionDefault('tagsuggest_excludeunassigned', 1);
    setOptionDefault('tagsuggest_checkaccess', 0);
  }

  function getOptionsSupported() {
    	$options = array(
         gettext('Exclude unassigned')
          => array(
              'key'	 => 'tagsuggest_excludeunassigned',
              'type' => OPTION_TYPE_CHECKBOX,
              'desc' => gettext("Check if you wish to exclude tags are not assigned to any item.")),
         gettext('Check tag access')
          => array(
              'key'	 => 'tagsuggest_checkaccess',
              'type' => OPTION_TYPE_CHECKBOX,
              'desc' => gettext("Check if you wish to exclude tags that are assigned to items (or are not assigned at all) the visitor is not allowed to see. This overrides the exlude unassigned option. <p class='notebox'><strong>Note:</strong> Beware that this may cause overhead on larger sites. The usage of the static_html_cache plugin is recommended.</p>"))
         );
     return $options;
  }
}

function tagSuggestJS_admin() {
  tagSuggestJS(false);
}

function tagSuggestJS_frontend() {
  $exclude_unassigned = getOption('tagsuggest_excludeunassigned');
  $checkaccess = getOption('tagsuggest_checkaccess');
  tagSuggestJS($exclude_unassigned,$checkaccess);
}

function tagSuggestJS($exclude_unassigned = false, $checkaccess = false) {
	// the scripts needed
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/encoder.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/tag.js"></script>
	<?php
	$css = getPlugin('tag_suggest/tag.css', true, true);
	?>
	<link type="text/css" rel="stylesheet" href="<?php echo pathurlencode($css); ?>" />
	<?php
  if ($checkaccess) {
     $taglist = getAllTagsUnique(true);
   } else {
     if ($exclude_unassigned) {
       $taglist = getAllTagsCount(true);
       $taglist = array_keys($taglist);
     } else {
       $taglist = getAllTagsUnique(false);
     }
   }
   $c = 0;
   $list = '';
   foreach ($taglist AS $tag) {
     if ($c > 0)
       $list .= ',';
     $c++;
     $list .= '"' . addslashes($tag) . '"';
   }
   if (OFFSET_PATH || getOption('search_space_is') == 'OR') {
     $tagseparator = ' ';
   } else {
     $tagseparator = ',';
   }
   ?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var _tagList = [<?php echo $list; ?>];
		$(function() {
			$('#search_input, #edit-editable_4, .tagsuggest').tagSuggest({separator: '<?php echo $tagseparator; ?>', tags: _tagList})
		});
		// ]]> -->
	</script>
	<?php
}
?>