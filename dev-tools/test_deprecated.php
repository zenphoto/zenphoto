<?php
$plugin_description = gettext("Test the messages of deprecated functions.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (getOption('zp_plugin_deprecated-functions')?'':gettext('deprecated-functions is not enabled.'));

if (!$plugin_disable) {
	zp_register_filter('theme_head', 'test_deprecated_test');
}

function test_deprecated_test() {
	$deprecated = file_get_contents(ZENFOLDER.'/'.PLUGIN_FOLDER.'/deprecated-functions.php');
	$i = strpos($deprecated, '//'.' IMPORTANT:: place all deprecated functions below this line!!!');
	$deprecated = substr($deprecated, $i);
	preg_match_all('/function\040+(.*)\040?\(.*\)\040?\{/',$deprecated,$functions);
	$listed_functions = $functions[1];
	// remove the items from this class and notify function, leaving only the deprecated functions
	foreach ($listed_functions as $key=>$funct) {
		if ($funct == '_emitPluginScripts') {	// special case!!!!
			unset($listed_functions[$key]);
		} else {
			if (getOption('deprecated_'.$funct)) {	// only error message enabled ones.
				echo "<br />$funct::";
				call_user_func_array($funct,array(0,0,0,0));
			}
		}
	}
}
?>