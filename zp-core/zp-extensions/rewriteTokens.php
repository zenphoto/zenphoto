<?php
/**
 * This plugin will edit the tokens in the %DATA_FOLDER% zenphoto.cfg file
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 97|ADMIN_PLUGIN;
$plugin_description = gettext('Utility to alter the rewrite token substitutions array in the configuation file.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'rewriteTokens';

class rewriteTokens {

	function __construct() {

	}

	function getOptionsSupported() {
		$zp_cfg = file_get_contents(CONFIGFILE);
		$i = strpos($zp_cfg,"\$conf['special_pages']");
		$j = strpos($zp_cfg,'//',$i);
		$zp_cfg_a = substr($zp_cfg,0,$i);
		$zp_cfg_b = substr($zp_cfg,$j);
		if (getOption('rewriteTokens_restore')) {
			$updated = false;
			purgeOption('rewriteTokens_restore');
			$template = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/zenphoto_cfg.txt');
			$i = strpos($template,"\$conf['special_pages']");
			$j = strpos($template,'//',$i);
			$newtext = substr($template,$i,$j-$i);
			$zp_cfg = $zp_cfg_a.$newtext.$zp_cfg_b;
			file_put_contents(CONFIGFILE, $zp_cfg);
			$options['note'] = array(
															'key' => 'rewriteTokens_note', 'type' => OPTION_TYPE_NOTE,
															'order' => 0,
															'desc' => gettext('<p class="messagebox"><em>zenphoto.cfg</em> restored to default.</p>')
															);
			eval($newtext);
			foreach ($conf['special_pages'] as $page=>$element) {
				setOption('rewriteTokens'.$page,$element['rewrite']);
			}
		} else {
			eval(substr($zp_cfg, $i,$j-$i));
		}
		$newtext = "\$conf['special_pages'] = array(";

		$updated = false;
		$options = array();
		$c = 0;
		foreach ($conf['special_pages'] as $page=>$element) {
			setOptionDefault('rewriteTokens'.$page,$element['rewrite']);
			if ($define = $element['define']) {
				$define = "'".$define."'";
				$desc = sprintf(gettext('Link for <em>%s</em> rule.'),$page);
			} else {
				$define =  'false';
				$desc = sprintf(gettext('Link for <em>%s</em> script page.'),$page);
			}
			$new = getOption('rewriteTokens'.$page);
			$newtext .= "\n														'$page'=>			array('define'=>$define,						'rewrite'=>'$new'	),";
			$updated = $updated || ($new != $element['rewrite']);
			$options[$page] = array('key' => 'rewriteTokens'.$page, 'type' => OPTION_TYPE_TEXTBOX,
															'order'=>++$c,
															'desc' => $desc);
		}
		$newtext = substr($newtext,0,-1)."\n												);\n";

		$options[gettext('Reset')] =  array('key' => 'rewriteTokens_restore', 'type' => OPTION_TYPE_CHECKBOX,
																					'order'=>++$c,
																					'desc' => gettext('Restore defaults.'));
		if ($updated) {
			$zp_cfg = $zp_cfg_a.$newtext.$zp_cfg_b;
			file_put_contents(CONFIGFILE, $zp_cfg);
			$options['note'] = array(
															'key' => 'rewriteTokens_note', 'type' => OPTION_TYPE_NOTE,
															'order' => 0,
															'desc' => gettext('<p class="messagebox"><em>zenphoto.cfg</em>  updated.</p>')
															);
		}

		return $options;
	}

}

?>