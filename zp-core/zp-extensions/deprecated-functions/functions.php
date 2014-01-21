<?php

function getPHPFiles($folder, $exclude) {
	global $_files;
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if ($file != '.' && $file != '..') {
			if (is_dir($folder . '/' . $file) && !in_array($file, $exclude)) {
				getPHPFiles($folder . '/' . $file, $exclude);
			} else {
				if (getSuffix($file) == 'php') {
					$entry = $folder . '/' . $file;
					$_files[] = $entry;
				}
			}
		}
	}
	closedir($dir);
}

function listUses($base) {
	global $_files, $pattern, $report, $deprecated;
	$method = '<em><small>' . gettext('method') . '</small></em> ';
	$output = false;
	foreach ($_files as $file) {
		if (basename($file) != 'deprecated-functions.php') {
			@set_time_limit(120);
			$subject = file_get_contents($file);
			preg_match_all($pattern, $subject, $matches);
			if ($matches && !empty($matches[0])) {
				$location = str_replace($base . '/', '', dirname($file));
				if (!$output) {
					echo '<br /><strong>' . $location . '</strong><ul>';
					$script_location = $base . '/' . $location . '/';
				}
				$script = str_replace($script_location, '', $file);
				echo '<li> ' . $script;
				echo '<ul>';
				foreach (array_unique($matches[0]) as $match) {
					$match = preg_replace('/(.*)?\s/', '', $match);
					$match = preg_replace('/\s?\(/', '', $match);
					$details = $deprecated->listed_functions[$match];
					switch (trim($details['class'])) {
						case 'static':
							$class = '*';
							break;
						case 'public static':
							$class = '**';
							break;
						default:
							$class = '';
							break;
					}
					echo '<li>' . $match . $class . '</li>';
				}
				echo '</ul></li>';
				$output = true;
			}
		}
	}
	if ($output) {
		echo '</ul>';
	}
	return $output;
}

?>
