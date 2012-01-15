<?php
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-globals.php');
$extension = sanitize($_GET['extension']);

$pluginStream = file_get_contents(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/'.$extension.'.php');
$parserr = 0;
if ($str = isolate('$plugin_description', $pluginStream)) {
	if (false === eval($str)) {
		$parserr = $parserr | 1;
		$plugin_description = gettext('<strong>Error parsing <em>plugin_description</em> string!</strong>.');
	}
} else {
	$plugin_description = '';
}
if ($str = isolate('$plugin_author', $pluginStream)) {
	if (false === eval($str)) {
		$parserr = $parserr | 2;
		$plugin_author = gettext('<strong>Error parsing <em>plugin_author</em> string!</strong>.');
	}
} else {
	$plugin_author = '';
}
if ($str = isolate('$plugin_version', $pluginStream)) {
	if (false === eval($str)) {
		$parserr = $parserr | 4;
		$plugin_version = ' '.gettext('<strong>Error parsing <em>plugin_version</em> string!</strong>.');
	}
} else {
	$plugin_version = '';
}

$i = strpos($pluginStream, '/*');
$j = strpos($pluginStream, '*/');
if ($i !== false && $j !== false) {
	$commentBlock = substr($pluginStream, $i+2, $j-$i-2);
	$lines = explode('*', $commentBlock);
	$doc = '';
	$par = false;
	$empty = false;

	foreach ($lines as $line) {
		$line = trim($line);
		if (empty($line)) {
			if (!$empty) {
				if ($par) {
					$doc .=  '</p>';
				}
				$doc .= '<p>';
				$empty = $par = true;
			}
		} else {
			$doc .= html_encode($line).' ';
			$empty = false;
		}
	}
	if ($par) {
		$doc .=  '</p>';
	}
}	else {
	$doc = '';
}
printAdminHeader('','');
echo "\n</head>";
?>
<body>
	<div id="main">
		<div id="content">
			<h1><?php echo html_encode($extension); ?></h1>
			<h3><?php printf( gettext('Version: %s'), $plugin_version); ?></h3>
			<h3><?php printf(gettext('author: %s'), html_encode($plugin_author)); ?></h3>
			<div>
			<?php echo $plugin_description; ?>
			<?php echo $doc; ?>
			</div>
		</div>
	</div>
</body>
