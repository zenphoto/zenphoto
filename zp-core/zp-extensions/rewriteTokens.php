<?php
/**
 * This plugin will edit the tokens in the %DATA_FOLDER% zenphoto.cfg file
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 97 | ADMIN_PLUGIN;
$plugin_description = gettext('Utility to alter the rewrite token substitutions array in the configuration file.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'rewriteTokens';

require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');

class rewriteTokens {

	private $zp_cfg_a;
	private $zp_cfg_b;
	private $conf_vars;

	function __construct() {
		$zp_cfg = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		$i = strpos($zp_cfg, "\$conf['special_pages']");
		$j = strpos($zp_cfg, '//', $i);
		$this->zp_cfg_a = substr($zp_cfg, 0, $i);
		$this->zp_cfg_b = substr($zp_cfg, $j);
		eval(substr($zp_cfg, $i, $j - $i));
		$this->conf_vars = $conf;
		if (OFFSET_PATH == 2) {
			$old = array_keys($conf['special_pages']);
			$zp_cfg = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto_cfg.txt');
			$i = strpos($zp_cfg, "\$conf['special_pages']");
			$j = strpos($zp_cfg, '//', $i);
			eval(substr($zp_cfg, $i, $j - $i));
			$new = array_keys($conf['special_pages']);
			if ($old != $new) {
				//Things have changed, need to reset to defaults;
				setOption('rewriteTokens_restore', 1);
				$this->handleOptionSave(NULL, NULL);
				setupLog(gettext('rewriteTokens restored to default'), true);
			}
		} else {
			enableExtension('rewriteTokens', 97 | ADMIN_PLUGIN); //	plugin must be enabled for saving options
		}
	}

	function getOptionsSupported() {
		$options = array();
		$c = 0;
		foreach ($this->conf_vars['special_pages'] as $page => $element) {
			if ($define = $element['define']) {
				$define = "'" . $define . "'";
				$desc = sprintf(gettext('Link for <em>%s</em> rule.'), $page);
			} else {
				$define = 'false';
				$desc = sprintf(gettext('Link for <em>%s</em> script page.'), $page);
			}
			$options[$page] = array('key'		 => 'rewriteTokens_' . $page, 'type'	 => OPTION_TYPE_CUSTOM,
							'order'	 => ++$c,
							'desc'	 => $desc);
		}
		$options[gettext('Reset')] = array('key'		 => 'rewriteTokens_restore', 'type'	 => OPTION_TYPE_CHECKBOX,
						'order'	 => ++$c,
						'desc'	 => gettext('Restore defaults.'));
		return $options;
	}

	function handleOption($option, $currentValue) {
		$element = $this->conf_vars['special_pages'][str_replace('rewriteTokens_', '', $option)]['rewrite'];
		?>
		<input type="textbox" name="<?php echo $option; ?>" value="<?php echo $element; ?>" >
		<?php
	}

	function handleOptionSave($theme, $album) {
		if (getOption('rewriteTokens_restore')) {
			$updated = false;
			purgeOption('rewriteTokens_restore');
			$template = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto_cfg.txt');
			$i = strpos($template, "\$conf['special_pages']");
			$j = strpos($template, '//', $i);
			$newtext = substr($template, $i, $j - $i);
			eval($newtext);
			$this->conf_vars = $conf;
		} else {
			foreach ($this->conf_vars['special_pages'] as $page => $element) {
				$this->conf_vars['special_pages'][$page]['rewrite'] = $_POST['rewriteTokens_' . $page];
			}
		}
		$newtext = "\$conf['special_pages'] = array(";
		foreach ($this->conf_vars['special_pages'] as $page => $element) {
			if ($define = $element['define']) {
				$define = "'" . $define . "'";
				$desc = sprintf(gettext('Link for <em>%s</em> rule.'), $page);
			} else {
				$define = 'false';
				$desc = sprintf(gettext('Link for <em>%s</em> script page.'), $page);
			}
			if (array_key_exists('rule', $element)) {
				$rule = ",		'rule'=>'{$element['rule']}'";
			} else {
				$rule = '';
			}
			$newtext .= $token = "\n														'$page'=>			array('define'=>$define,						'rewrite'=>'{$element['rewrite']}'$rule),";
		}
		$newtext = substr($newtext, 0, -1) . "\n												);\n";
		$zp_cfg = $this->zp_cfg_a . $newtext . $this->zp_cfg_b;
		storeConfig($zp_cfg);
		$options['note'] = array(
						'key'		 => 'rewriteTokens_note', 'type'	 => OPTION_TYPE_NOTE,
						'order'	 => 0,
						'desc'	 => sprintf(gettext('<p class="messagebox"><em>%1$s</em>  updated.</p>'), CONFIGFILE)
		);
	}

}
?>
