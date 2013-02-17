<?php
define('CONFIGFILE',SERVERPATH.'/'.DATA_FOLDER.'/zenphoto.cfg');

/**
 * Updates an item in the configuration file
 * @param unknown_type $item
 * @param unknown_type $value
 * @param unknown_type $quote
 */
function updateConfigItem($item, $value, $quote=true) {
	global $zp_cfg;
	if ($quote) {
		$value = '"'.$value.'"';
	}
	$i = strpos($zp_cfg, $item);
	if ($i === false) {
		$i = strpos($zp_cfg, '/** Do not edit below this line. **/');
		$zp_cfg = substr($zp_cfg, 0, $i)."\$conf['".$item."'] = ".$value.";\n".substr($zp_cfg,$i);
	} else {
		$i = strpos($zp_cfg, '=', $i);
		$j = strpos($zp_cfg, "\n", $i);
		$zp_cfg = substr($zp_cfg, 0, $i) . '= ' . $value . ';' . substr($zp_cfg, $j);
	}
}


?>