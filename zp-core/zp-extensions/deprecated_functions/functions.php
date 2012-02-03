<?php
function getPHPFiles($folder,$exclude) {
	global $_files;
	$dir = opendir($folder);
	while(($file = readdir($dir)) !== false) {
		$file = str_replace('\\','/',$file);
		if ($file != '.' && $file != '..') {
			if (is_dir($folder.'/'.$file) && !in_array($file, $exclude)) {
				getPHPFiles($folder.'/'.$file, $exclude);
			} else {
				if (getSuffix($file)=='php') {
					$entry = $folder.'/'.$file;
					$_files[]=$entry;
				}
			}
		}
	}
	closedir($dir);
}

function listUses($base) {
	global $_files, $pattern, $report;
	$output = false;
	foreach($_files as $file) {
		if (basename($file) != 'deprecated-functions.php') {
			set_time_limit (60);
			$subject = file_get_contents($file);
			preg_match_all('/'.$pattern.'/', $subject, $matches);
			if ($matches && !empty($matches[0])) {
				$script = basename($file);
				$location = str_replace($base.'/', '', dirname($file));
				echo '<br />'.$script;
				echo '<ul>';
				foreach ($matches[0] as $match) {
					$match = preg_replace('/(.*)?\s/', '', $match);
					$match = preg_replace('/\s?\(/', '', $match);
					echo '<li>'.$match.'</li>';
				}
				echo '</ul>';
				$output = true;
			}
		}
	}
	return $output;
}
?>