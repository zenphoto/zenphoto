<?php
/**
 * "Rewrite" handling for Zenphoto
 *
 * @admin
 */

function rewriteHandler() {
	$request = array();
	$requesturi = getRequestURI();
	//rewrite base
	$requesturi = ltrim(substr($requesturi, strlen(WEBPATH)),'/');
	//admin request
	if (preg_match('~^admin/*$~i', $requesturi, $matches)) {
		header('location: '.WEBPATH.'/'.ZENFOLDER.'/admin.php');
		exit();
	}
	//	setup request
	if (preg_match('~^setup/*$~i', $requesturi, $matches)) {
		header('location: '.WEBPATH.'/'.ZENFOLDER.'/setup/index.php');
		exit();
	}

	//	rewrite rules from .htaccess
	$rules = explode("\n",trim(file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/zenphoto-rewrite.txt')));
	foreach ($rules as $rule) {
		$rule = trim($rule);
		if ($rule && $rule{0} !='#' && preg_match('~^rewriterule~i', $rule)) {
			preg_match('~^rewriterule\s+(.*?)\s+(.*?)\s*\[(.*)\]$~i', $rule, $matches);
			if (preg_match('~'.$matches[1].'~', $requesturi, $params)) {
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
				if (array_key_exists(2, $action)) {
					$qs = explode('&',$action[2]);
					foreach ($qs as $get) {
						$sets = explode('=',$get);
						if ($v = @$sets[1]) {
							preg_match('~^\$(\d*)$~', $v, $sub);
							if (array_key_exists(1, $sub)) {
								$v = @$params[$sub[1]];
							}
						}
						$_REQUEST[$sets[0]] = $_GET[$sets[0]] = $v;
					}
				}
				if (isset($action[1]) && $action[1]!='index.php') {
					$qp = '';
					foreach ($_GET as $q=>$v) {
						$qp .= $q;
						if ($v) {
							$qp .= '='.$v;
						}
						$qp .= '&';
					}
					if ($qp) {
						$qp = '?'.$qp;
					}
					if (array_key_exists('R', $flags)) {
						header('Status: '.$flags['R']);
					}
					header('Location: '.WEBPATH.'/'.$action[1].substr($qp,0,-1));
					exit();
				}
				break;
			}
		}
	}
}

rewriteHandler();

?>