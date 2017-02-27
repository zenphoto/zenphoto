<?php

/**
 *
 * Collects output of the 'theme_head' filters.
 * parses it into js, css, and other items.
 *
 * CSS or JS files with duplicate base names are reduced to
 * just the first instance. (Paths are ignored.)
 *
 * In-line <i>JavaScript</i> is consolidated into one <var><script type="text/javascript"></script></var> tag
 *
 * This is intended as an example of how one might process the output
 * from the <var>theme_head</var> filters.
 *
 * [<b>Note:</b> this processing takes 0.02 seconds on my test system loading the Garland theme with a
 * reasonable collection of plugins. So it may not be appropriate to use it from a performance perspective.]
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 *
 */
$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A plugin to collect and consolidate theme_head scripts and css.');
$plugin_author = "Stephen Billard (sbillard)";

// Note: these are not exact. If some other plugin decides to insert before or after, it's output
// will not get processed.
zp_register_filter('theme_head', 'headConolidator_start', 99999);
zp_register_filter('theme_head', 'headConolidator_end', -99999);

function headConolidator_start() {
	ob_start();
}

function headConolidator_end() {
	$data = ob_get_contents();
	ob_end_clean();
	echo '<!-- ' . gettext('beginning of theme_head items') . " -->\n";

	$matches = headConolidator_extract($data, '~<script(?:|\s*type="text/javascript"|)\s*src="(.*)"(?:|\s*type="text/javascript"|)\s*></script>~msU');
	foreach ($matches[0] as $key => $str) {
		if (strpos($str, 'text/javascript') === false) {
			unset($matches[1][$key]);
		}
	}
	$jsFiles = array();
	while (!empty($matches[1])) { // flush out the duplicates. Earliest wins
		$file = array_pop($matches[1]);
		$jsFiles[basename($file)] = $file;
	}
	$jsFiles = array_reverse($jsFiles);
	$js = '<script type="text/javascript" src="' . implode('"></script>' . "\n" . '<script type="text/javascript" src="', $jsFiles) . '"></script>' . "\n";

	$matches = headConolidator_extract($data, '~<link\s*(?:|type="text/css"|)\s*rel="stylesheet"\s*href="(.*)"\s*(?:|type="text/css"|)(?:\s*)/>~msU');
	foreach ($matches[0] as $key => $str) {
		if (strpos($str, 'text/css') === false) {
			unset($matches[1][$key]);
		}
	}
	$csFiles = array();
	while (!empty($matches[1])) { // flush out the duplicates. Earliest wins
		$file = array_pop($matches[1]);
		$csFiles[basename($file)] = $file;
	}
	$csFiles = array_reverse($csFiles);
	$cs = '<link type="text/css" rel="stylesheet" href="' . implode('" />' . "\n" . '<link type="text/css" rel="stylesheet" href="', $csFiles) . '" />' . "\n";



	$matches = headConolidator_extract($data, '~<script(?:\s*)type="text/javascript"(?:\s*)>(.*)</script>~msU');
	$inlinejs = $matches[1];
	$jsi = '';
	if (!empty($inlinejs)) {
		$jsi .= '<script type="text/javascript">' . "\n";
		$jsi .= '  // <!-- <![CDATA[' . "\n";
		foreach ($inlinejs as $somejs) {
			$somejs = str_replace('// <!-- <![CDATA[', '', $somejs);
			$somejs = str_replace('// ]]> -->', '', $somejs);
			$jsi .= '  ' . trim($somejs) . "\n";
		}
		$jsi .= '		// ]]> -->' . "\n";
		$jsi .= "</script>\n";
	}

	if (!empty($cs)) {
		echo '<!-- ' . gettext('CSS references') . " -->\n" . $cs;
	}
	if (!empty($js)) {
		echo '<!-- ' . gettext('javaScript') . " -->\n" . $js;
	}
	if (!empty($jsi)) {
		echo '<!-- ' . gettext('In-line javaScript') . " -->\n" . $jsi;
	}
	$unprocessed = explode("\n", $data);
	foreach ($unprocessed as $key => $line) {
		$line = trim($line);
		if (empty($line)) {
			unset($unprocessed[$key]);
		} else {
			$unprocessed[$key] = $line;
		}
	}
	$data = implode("\n", $unprocessed);
	if (!empty($data)) {
		echo '<!-- ' . gettext('unprocessed heading items') . " -->\n" . trim($data) . "\n";
	}

	echo '<!-- ' . gettext('end of theme_head items') . " -->\n";
}

function headConolidator_extract(&$data, $pattern) {
	preg_match_all($pattern, $data, $matches);
	foreach ($matches[0] as $found) {
		$data = trim(str_replace($found, '', $data));
	}
	return $matches;
}

?>