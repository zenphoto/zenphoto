<?php

function getPHPFiles($folder, $exclude) {
	global $files;
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if ($file != '.' && $file != '..') {
			if (is_dir($folder . '/' . $file) && !in_array($file, $exclude)) {
				getPHPFiles($folder . '/' . $file, $exclude);
			} else {
				if (getSuffix($file) == 'php') {
					$entry = $folder . '/' . $file;
					$files[] = $entry;
				}
			}
		}
	}
	closedir($dir);
	return $files;
}

function formatList($title, $subject, $pattern) {
	global $deprecated;
	preg_match_all($pattern, $subject, $matches);
	$started = false;
	if ($matches && !empty($matches[0])) {
		foreach (array_unique($matches[2]) as $key => $match) {
			$details = $deprecated->unique_functions[strtolower($match)];
			$found = $matches[1][$key];
			switch ($details['class']) {
				case 'static':
					if ($found == '->' || $found == '::') {
						$class = '*';
						break;
					} else {
						continue 2;
					}
				case 'final static':
					if ($found == '->' || $found == '::') {
						$class = '*+';
						break;
					} else {
						continue 2;
					}
				case 'public static':
					if ($found == '->' || $found == '::') {
						continue 2;
					} else {
						$class = '+';
						break;
					}
				default:
					if ($found == '->' || $found == '::') {
						continue 2;
					} else {
						$class = '';
						break;
					}
			}
			if (!$started) {
				$started = true;
				echo '<li class="warningbox nobullet"> ' . $title;
				echo '<ul>';
			}
			echo '<li>' . $match . $class . '</li>';
		}
	}
	if ($started)
		echo '</ul></li>';

	return $started;
}

function listUses($files, $base, $pattern) {
	if (is_array($files)) {
		$open = $output = false;
		$oldLocation = '';
		foreach ($files as $file) {
			if (basename($file) != 'deprecated-functions.php') {
				@set_time_limit(120);
				$subject = file_get_contents($file);
				$location = str_replace($base . '/', '', dirname($file));
				$folders = explode('/', $location);
				if ($folders[0] != $oldLocation) {
					$oldLocation = $folders[0];
					echo '<br /><strong>' . $location . '</strong>';
				}
				if ($open) {
					echo '</ul>';
				}
				$script_location = $base . '/' . $location . '/';
				$script = str_replace($script_location, '', $file);
				$open = $output = formatList($script, $subject, $pattern);
			} 
		}
		if ($open) {
			echo '</ul>';
		}
		if ($output) {
			?>
			<p class="messagebox"><?php echo gettext('No calls on deprecated functions were found.'); ?></p>
			<?php
		}
		return $output;
	}
}

function listDBUses($pattern) {
	$lookfor = array('images', 'albums', 'news', 'pages');
	$found = array();
	foreach ($lookfor as $table) {
		echo '<br /><strong>' . sprintf(gettext('%s table'), $table) . '</strong>';
		$output = false;
		$sql = 'SELECT * FROM ' . prefix($table) . ' WHERE `codeblock` <> "" and `codeblock` IS NOT NULL and `codeblock`!="a:0:{}"';
		$result = query($sql);
		while ($row = db_fetch_assoc($result)) {
			$codeblocks = getSerializedArray($row['codeblock']);
			foreach ($codeblocks as $key => $codeblock) {
				switch ($table) {
					case 'news':
					case 'pages':
						$what = $row['titlelink'] . '::' . $key;
						break;
					case 'images':
						$album = getItemByID('albums', $row['albumid']);
						$what = $album->name . ':' . $row['filename'] . '::' . $key;
						break;
					case 'albums':
						$what = $row['folder'] . '::' . $key;
						break;
				}
				if (formatList($what, $codeblock, $pattern))
					$output = true;
			}
		}
		if ($output) {
			echo '</ul>';
		} else {
			?>
			<p class="messagebox"><?php echo gettext('No calls on deprecated functions were found.'); ?></p>
			<?php
		}
	}
	return $output;
}
?>
