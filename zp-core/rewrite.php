<?php

/**
 * "Rewrite" handling for Zenphoto
 *
 * The basic rules are found in the zenphoto-rewrite.txt file. Additional rules can be provided by plugins. But
 * for the plugin to load in time for the rules to be seen it must be either a CLASS_PLUGIN or a FEATURE_PLUGIN.
 * Plugins add rules by inserting them into the $_zp_conf_vars['special_pages'] array. Each "rule" is an array
 * of three elements: <var>define</var>, <var>rewrite</var>, and (optionally) <var>rule</rule>.
 *
 * Elemments which have a <var>define</var> and no <var>rule</rule> are processed by rewrite rules in the
 * zenphoto-rewrite.txt file and the <var>define</var> is used internally to Zenphoto to reference
 * the rewrite text when building links.
 *
 * Elements with a <var>rule</rule> defined are processed after Search, Pages, and News rewrite rules and before
 * Image and album rewrite rules. The tag %REWRITE% in the rule is replaced with the <var>rewrite</var> text
 * before processing the rule. Thus <var>rewrite</var> is the token that should appear in the acutal URL.
 *
 * It makes no sense to have an element without either a <var>define</var> or a <var>rule</rule> as nothing will happen.
 *
 * At present all rules are presumed to to stop processing the rule set. Historically that is what all our rules have done, but I suppose
 * we could change that. The "R" flag may be used to cause a <var>header</var> status to be sent. However, we do not redirect
 * back to index.php, so the "R" flag is only useful if the target is a different script.
 *
 * @package admin
 */
function rewriteHandler() {
	global $_zp_conf_vars, $_zp_rewritten;
	$_zp_rewritten = false;
	$definitions = array();

	//	query parameters should already be loaded into the $_GET and $_REQUEST arrays, so we discard them here
	$request = explode('?', getRequestURI());
	//rewrite base
	$requesturi = ltrim(substr($request[0], strlen(WEBPATH)), '/');

	//	load rewrite rules
	$rules = trim(file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto-rewrite.txt'));
	$specialPageRules = array();
	foreach ($_zp_conf_vars['special_pages'] as $page => $special) {
		if (array_key_exists('rule', $special)) {
			$specialPageRules[] = "\tRewriteRule " . str_replace('%REWRITE%', $special['rewrite'], $special['rule']);
		}
	}
	$rules = str_replace('_SPECIAL_', implode("\n", $specialPageRules), $rules);
	$rules = explode("\n", $rules);
	array_push($rules, 'RewriteRule ^(.*)/?$	index.php?album=$1 [L,QSA]'); // catch all rule at the end
	//	and process them

	foreach ($rules as $rule) {
		if ($rule = trim($rule)) {
			if ($rule{0} != '#') {
				if (preg_match('~^rewriterule~i', $rule)) {
					// it is a rewrite rule, see if it is applicable
					$rule = strtr($rule, $definitions);
					preg_match('~^rewriterule\s+(.*?)\s+(.*?)\s*\[(.*)\]$~i', $rule, $matches);
					if (array_key_exists(1, $matches)) {
						if (preg_match('~' . $matches[1] . '~', $requesturi, $subs)) {
							$params = array();
							//	setup the rule replacement values
							foreach ($subs as $key => $sub) {
								$params['$' . $key] = urlencode($sub); // parse_str is going to decode the string!
							}
							//	parse rewrite rule flags
							$flags = array();
							$banner = explode(',', strtoupper($matches[3]));
							foreach ($banner as $flag) {
								$f = explode('=', $flag);
								$flags[$f[0]] = isset($f[1]) ? $f[1] : NULL;
							}
							if (!array_key_exists('QSA', $flags)) {
								//	merge the existing query parameters with whatever the rewrite rule supplies
								$_REQUEST = array_diff($_REQUEST, $_GET);
								$_GET = array();
							}
							preg_match('~(.*?)\?(.*)~', $matches[2], $action);
							if (empty($action)) {
								$action[1] = $matches[2];
							}
							if (array_key_exists(2, $action)) {
								//	process the rules replacements
								$query = strtr($action[2], $params);
								parse_str($query, $gets);
								$_GET = array_merge($_GET, $gets);
								$_REQUEST = array_merge($_REQUEST, $gets);
							}
							//	we will execute the index.php script in due course. But if the rule
							//	action takes us elsewhere we will have to re-direct to that script.
							if (isset($action[1]) && $action[1] != 'index.php') {
								$qs = http_build_query($_GET);
								if ($qs) {
									$qs = '?' . $qs;
								}
								if (array_key_exists('R', $flags)) {
									header('Status: ' . $flags['R']);
								}
								header('Location: ' . WEBPATH . '/' . $action[1] . $qs);
								exit();
							}
							$_zp_rewritten = true;
							break;
						}
					} else {
						zp_error(sprintf(gettext('Error processing rewrite rule: "%s"'), trim(str_replace('RewriteRule', '', $rule))), E_USER_WARNING);
					}
				} else {
					if (preg_match('~define\s+(.*?)\s*\=\>\s*(.*)$~i', $rule, $matches)) {
						//	store definitions
						eval('$definitions[$matches[1]] = ' . $matches[2] . ';');
					}
				}
			}
		}
	}
}

if (MOD_REWRITE)
	rewriteHandler();
?>