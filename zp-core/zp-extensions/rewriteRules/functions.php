<?php

function rulesList() {
	global $_zp_conf_vars;
	$definitions = array();

	list($definitions, $rules) = getRules();
	$list = array();
	//process the rules
	foreach ($rules as $rule) {
		if ($rule = trim($rule)) {
			if ($rule{0} == '#') {
				if (trim(ltrim($rule, '#')) == 'quick links') {
					foreach ($definitions as $def => $v) {
						$list[] = array('Define ' . $def, $v);
					}
				}
				$list[] = array($rule, '&nbsp;');
			} else {
				if (preg_match('~^rewriterule~i', $rule)) {
					// it is a rewrite rule, see if it is applicable
					$rule = strtr($rule, $definitions);
					preg_match('~^rewriterule\s+(.*?)\s+(.*?)\s*\[(.*)\]$~i', $rule, $matches);
					if (array_key_exists(1, $matches)) {
						$parts = preg_split('`\s+`', $rule);
						$list[] = array('rewriterule ' . $parts[1], $parts[2]);
					} else {
						$list[] = array(gettext('Error processing rewrite rule:'), $rule);
					}
				} else {
					if (preg_match('~define\s+(.*?)\s*\=\>\s*(.*)$~i', $rule, $matches)) {
						//	store definitions
						eval('$definitions[$matches[1]] = ' . $matches[2] . ';');
						$list[] = array('Define ' . $matches[1], $definitions[$matches[1]]);
					}
				}
			}
		}
	}
	return $list;
}

?>