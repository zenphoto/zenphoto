<?php
/**
 * "Rewrite" handling for Zenphoto
 *
 * @admin
 */

function rewriteHandler() {
	$definitions = array();
	$request = parse_url(getRequestURI());
	//rewrite base
	$requesturi = ltrim(substr($request['path'], strlen(WEBPATH)),'/');

	//	load rewrite rules
	$rules = explode("\n",trim(file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/zenphoto-rewrite.txt')));
	//	and process them
	foreach ($rules as $rule) {
		$rule = trim($rule);
		if ($rule && $rule{0} !='#') {
			if (preg_match('~^rewriterule~i', $rule)) {
				$rule = strtr($rule,$definitions);
				preg_match('~^rewriterule\s+(.*?)\s+(.*?)\s*\[(.*)\]$~i', $rule, $matches);
				if (preg_match('~'.$matches[1].'~', $requesturi, $subs)) {
					$params = array();
					foreach ($subs as $key=>$sub) {
						$params['$'.$key] = $sub;
					}
					$flags = array();
					$banner = explode(',',strtoupper($matches[3]));
					foreach ($banner as $flag) {
						$f = explode('=', $flag);
						$flags[$f[0]] = @$f[1];
					}
					if (!array_key_exists('QSA', $flags)) {
						$_REQUEST = array_diff($_REQUEST, $_GET);
						$_GET = array();
					}
					preg_match('~(.*?)\?(.*)~', $matches[2],$action);
					if (empty($action)) {
						$action[1] = $matches[2];
					}
					if (array_key_exists(2, $action)) {
						$query = strtr($action[2], $params);
						parse_str($query,$gets);
						$_GET = array_merge($_GET, $gets);
						$_REQUEST = array_merge($_REQUEST, $gets);
					}
					if (isset($action[1]) && $action[1]!='index.php') {
						$qs = http_build_query($_GET);
						if ($qs) {
							$qs = '?'.$qs;
						}
						if (array_key_exists('R', $flags)) {
							header('Status: '.$flags['R']);
						}
						header('Location: '.WEBPATH.'/'.$action[1].$qs);
						exit();
					}
					break;
				}
			} else {
				if (preg_match('~define\s+(.*?)\s*\=\>\s*(.*)$~i', $rule, $matches)) {
					eval('$definitions[$matches[1]] = '.$matches[2].';');
				}
			}
		}
	}
}

rewriteHandler();

?>