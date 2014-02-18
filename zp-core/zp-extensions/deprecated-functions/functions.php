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

function formatList($title, $matches) {
	global $deprecated;
	echo '<li> ' . $title;
	echo '<ul>';
	foreach (array_unique($matches[0]) as $match) {
		$match = strtr($match, array('->' => '', '::' => ''));
		$match = preg_replace('/(.*)?\s/', '', $match);
		$match = preg_replace('/\s?\(/', '', $match);
		$details = $deprecated->listed_functions[$match];
		switch (trim($details['class'])) {
			case 'static':
				$class = '*';
				break;
			case 'public static':
				$class = '+';
				break;
			case 'final static':
				$class = '*+';
				break;
			default:
				$class = '';
				break;
		}
		echo '<li>' . $match . $class . '</li>';
	}
	echo '</ul></li>';
	return true;
}

function listUses($base) {
	global $_files, $pattern, $report;
	$method = '<em><small>' . gettext('method') . '</small></em> ';
	$output = false;
	$oldLocation = '';
	foreach ($_files as $file) {
		if (basename($file) != 'deprecated-functions.php') {
			@set_time_limit(120);
			$subject = file_get_contents($file);
			preg_match_all($pattern, $subject, $matches);
			if ($matches && !empty($matches[0])) {
				$location = str_replace($base . '/', '', dirname($file));
				if ($location != $oldLocation) {
					if ($output) {
						echo '</ul>';
					}
					echo '<br /><strong>' . $location . '</strong><ul>';
					$oldLocation = $location;
					$script_location = $base . '/' . $location . '/';
				}
				$script = str_replace($script_location, '', $file);
				$location = str_replace($base . '/', '', dirname($file));
				formatList($script, $matches);
				$output = true;
			}
		}
	}
	if ($output) {
		echo '</ul>';
	}
	return $output;
}

function listDBUses() {
	global $_files, $pattern, $report;
	$lookfor = array('images', 'albums', 'news', 'pages');
	$found = array();
	foreach ($lookfor as $table) {
		$output = false;
		$sql = 'SELECT * FROM ' . prefix($table) . ' WHERE `codeblock`  <> "" and `codeblock` IS NOT NULL';
		$result = query($sql);
		while ($row = db_fetch_assoc($result)) {
			preg_match_all($pattern, $row['codeblock'], $matches);
			if ($matches && !empty($matches[0])) {
				if (!$output) {
					echo '<br /><strong>' . $table . '</strong><ul>';
				}
				switch ($table) {
					case 'news':
					case 'pages':
						$what = $row['titlelink'];
						break;
					case 'images':
						$album = getItemByID('albums', $row['albumid']);
						$what = $album->name . ':' . $row['filename'];
						break;
					case 'albums':
						$what = $row['folder'];
						break;
				}
				formatList($what, $matches);
				$output = true;
			}
		}
		if ($output) {
			echo '</ul>';
		}
	}
	return $output;
}

?>
