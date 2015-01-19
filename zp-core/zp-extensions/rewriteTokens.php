<?php
/**
 * This plugin will edit the tokens in the <var>%DATA_FOLDER%/zenphoto.cfg</var> file
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 950 | ADMIN_PLUGIN;
$plugin_description = gettext('Utility to alter the rewrite token substitutions array in the configuration file.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (MOD_REWRITE) ? '' : gettext('Rewrite Tokens are not useful unless the <code>mod_rewrite</code> option is enabled.');

$option_interface = 'rewriteTokens';

if (OFFSET_PATH == 2) {
	setOptionDefault('zp_plugin_rewriteTokens', $plugin_is_filter);
}
zp_register_filter('admin_tabs', 'rewriteTokens::tabs');

require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');

class rewriteTokens {

	private $zp_cfg_a;
	private $zp_cfg_b;
	private $conf_vars = array();
	private $plugin_vars = array();

	function __construct() {
		global $_configMutex, $_zp_conf_vars;
		$_configMutex->lock();
		$zp_cfg = file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		$i = strpos($zp_cfg, "\$conf['special_pages']");
		$j = strpos($zp_cfg, '//', $i);
		if ($i === false || $j === false) {
			$conf = array('special_pages' => array());
			$this->conf_vars = $conf['special_pages'];
			$i = strpos($zp_cfg, '/** Do not edit below this line. **/');
			if ($i === false) {
				zp_error(gettext('The Zenphoto configuration file is corrupt. You will need to restore it from a backup.'));
			}
			$this->zp_cfg_a = substr($zp_cfg, 0, $i);
			$this->zp_cfg_b = "//\n" . substr($zp_cfg, $i);
		} else {
			$this->zp_cfg_a = substr($zp_cfg, 0, $i);
			$this->zp_cfg_b = substr($zp_cfg, $j);
			eval(substr($zp_cfg, $i, $j - $i));
			$this->conf_vars = $conf['special_pages'];
			foreach ($_zp_conf_vars['special_pages'] as $page => $element) {
				if (isset($element['option'])) {
					$this->plugin_vars[$page] = $element;
				}
			}
		}
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

	function __destruct() {
		global $_configMutex;
		$_configMutex->unlock();
	}

	protected static function anOption($page, $element, &$_definitions) {
		if ($define = $element['define']) {
			$_definitions[$element['define']] = strtr($element['rewrite'], $_definitions);
			$desc = sprintf(gettext('The <code>%1$s</code> rule defines <strong>%2$s</strong> as <em>%3$s</em>.'), $page, $define, strtr($element['rewrite'], $_definitions));
		} else {
			$desc = sprintf(gettext('Link for <em>%s</em> script page.'), $page);
		}
		return array('key'	 => 'rewriteTokens_' . $page, 'type' => OPTION_TYPE_CUSTOM,
						'desc' => $desc);
	}

	function getOptionsSupported() {
		$_definitions = array();
		$options = array();
		if (!MOD_REWRITE) {
			$options['note'] = array(
							'key'		 => 'rewriteTokens_note',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 0,
							'desc'	 => gettext('<p class="notebox">Rewrite Tokens are not useful unless the <code>mod_rewrite</code> option is enabled.</p>')
			);
		}
		$options[gettext('Reset')] = array('key'		 => 'rewriteTokens_restore', 'type'	 => OPTION_TYPE_CHECKBOX,
						'order'	 => 99999,
						'desc'	 => gettext('Restore defaults.'));
		foreach ($this->conf_vars as $page => $element) {
			$options[$page] = self::anOption($page, $element, $_definitions);
		}
		foreach ($this->plugin_vars as $page => $element) {
			$options[$page] = self::anOption($page, $element, $_definitions);
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$element = str_replace('rewriteTokens_', '', $option);
		if (array_key_exists($element, $this->plugin_vars)) {
			$element = $this->plugin_vars[$element]['rewrite'];
		} else {
			$element = $this->conf_vars[$element]['rewrite'];
		}
		?>
		<input type="textbox" name="<?php echo $option; ?>" value="<?php echo $element; ?>" >
		<?php
	}

	function handleOptionSave($theme, $album) {
		$notify = false;
		if (getOption('rewriteTokens_restore')) {
			$updated = false;
			purgeOption('rewriteTokens_restore');
			$template = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto_cfg.txt');
			$i = strpos($template, "\$conf['special_pages']");
			$j = strpos($template, '//', $i);
			$newtext = substr($template, $i, $j - $i);
			eval($newtext);
			$this->conf_vars = $conf['special_pages'];
			foreach ($this->plugin_vars as $page => $element) {
				if (isset($element['option'])) {
					$this->plugin_vars[$page]['rewrite'] = $element['default'];
					setOption($element['option'], $element['default']);
				}
			}
		} else {
			foreach ($this->conf_vars as $page => $element) {
				$rewrite = sanitize($_POST['rewriteTokens_' . $page]);
				if (empty($rewrite)) {
					$notify = '&custom=' . gettext('Rewrite tokens may not be empty.');
				} else {
					$this->conf_vars[$page]['rewrite'] = $rewrite;
				}
				foreach ($this->plugin_vars as $page => $element) {
					if (isset($element['option'])) {
						$rewrite = sanitize($_POST['rewriteTokens_' . $page]);
						if (empty($rewrite)) {
							$notify = '&custom=' . gettext('Rewrite tokens may not be empty.');
						} else {
							$this->plugin_vars[$page]['rewrite'] = $rewrite;
							setOption($element['option'], $rewrite);
						}
					}
				}
			}
		}
		$newtext = "\$conf['special_pages'] = array(";
		foreach ($this->conf_vars as $page => $element) {
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
		return $notify;
	}

	static function tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text'		 => gettext("development"),
								'subtabs'	 => NULL);
			}
			$tabs['development']['subtabs'][gettext("tokens")] = PLUGIN_FOLDER . '/rewriteTokens/admin_tab.php?page=tokens&tab=' . gettext('tokens');
			$named = array_flip($tabs['development']['subtabs']);
			natcasesort($named);
			$tabs['development']['subtabs'] = $named = array_flip($named);
			$tabs['development']['link'] = array_shift($named);
		}
		return $tabs;
	}

}
?>
