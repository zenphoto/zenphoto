<?php
/**
 *
 * Plugins can add to the macro list by registering the content_macro filter. They add to the
 * array an element for each macro. The array key is the macro, the element is an array with the
 * class of macro, the regular expression for parsing parameters and the "value" of the macro.
 *
 * Note: the plugin should be active both on THEMES to provide the function and on the ADMIN pages to provide
 * the macro documentation.
 *
 * macro classes:
 * 	<ol>
 * 		<li>
 * 			<var>procedure</var> calls a script function that produces output. The output is captured and inserted in place of the macro instance
 * 		</li>
 * 		<li>
 * 			<var>function</var> calls a script function that returns a result. The result is inserted in place of the macro instance
 * 		</li>
 * 		<li>
 * 			<var>constant</var> replaces the macro instances with the constant provided
 * 		</li>
 * 		<li>
 * 			<var>expression</var> evaluates the expression provided and replaces the instance with the result of the evaluation. If a regex is supplied for an expression
 * 														the values provided will replace placeholders in the expression. The first parameter replaces $1, the second $2, etc.
 * 		</li>
 * 	</ol>
 *
 * useage examples:
 * 	<ol>
 * 		<li>
 * 			[CODEBLOCK 3]
 * 		</li>
 * 		<li>
 * 			[PAGE]
 * 		</li>
 * 		<li>
 * 			[ZENPHOTO_VERSION]
 * 		</li>
 * 		<li>
 * 			[pageLink register]
 * 		</li>
 * 	</ol>
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage tools
*/

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('View available <code>content macros</code>.');
$plugin_author = "Stephen Billard (sbillard)";

$macros = getMacros();
if (!empty($macros)) {
	zp_register_filter('admin_tabs', 'macro_admin_tabs');
}
function macro_admin_tabs($tabs) {
	$tabs['macros'] =  array('text'=>gettext("macros"),
			'link'=>WEBPATH."/".ZENFOLDER.'/'.$mylink = PLUGIN_FOLDER.'/'.'macroList/macroList_tab.php?page=macros&amp;tab='.gettext('macros'),
			'subtabs'=>NULL);
	return $tabs;
}

?>