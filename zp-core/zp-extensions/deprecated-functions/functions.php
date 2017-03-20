<?php

function getPHPFiles($folder, $exclude, &$files = array()) {
	$dir = opendir($folder);
	while (($file = readdir($dir)) !== false) {
		$file = str_replace('\\', '/', $file);
		if (strpos($file, '.') !== 0) {
			if (is_dir($folder . '/' . $file) && !in_array($file, $exclude)) {
				getPHPFiles($folder . '/' . $file, $exclude, $files);
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

function formatList($title, $subject, $pattern, $started = FALSE) {
	global $deprecated;
	$emitted = false;
	preg_match_all($pattern, $subject, $matches);
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
			if ($started) {
				echo "</ul>\n";
			} else {
				$started = true;
				echo '<ul class="warningbox nobullet">' . "\n";
			}
			if (!$emitted) {
				echo '<li>' . $title;
				echo "\n<ul>\n";
			}
			echo '<li>' . $match . $class . "</li>\n";
			$emitted = true;
		}
	}

	return $started;
}

function listUses($files, $base, $pattern) {
	$open = $output = false;
	$oldLocation = '';
	foreach ($files as $file) {
		if (basename($file) != 'deprecated-functions.php') {
			@set_time_limit(120);
			$subject = file_get_contents($file);
			$location = ltrim(str_replace($base, '', dirname($file)), '/');
			$folders = explode('/', $location);
			if ($folders[0] != $oldLocation) {
				$oldLocation = $folders[0];
				if ($location) {
					if ($open) {
						$open = false;
						echo "</ul>\n</li>\n</ul>\n";
					} else {
						echo "<br/>\n";
					}
					echo '<strong>' . $location . "</strong>\n";
				}
			}
			$script_location = $base . '/' . $location . '/';
			$script = str_replace($script_location, '', $file);
			$open = formatList($script, $subject, $pattern, $open);
			if ($open) {
				$output = true;
			}
		}
	}
	if ($open) {
		echo "</ul>\n</li>\n</ul>\n";
	}
	if (!$output) {
		?>
		<p class="messagebox"><?php echo gettext('No calls on deprecated functions were found.'); ?></p>
		<?php
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
