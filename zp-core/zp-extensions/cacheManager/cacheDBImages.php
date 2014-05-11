<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package plugins
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/functions.php');

admin_securityChecks(NULL, $return = currentRelativeURL());

XSRFdefender('cacheDBImages');

$zenphoto_tabs['overview']['subtabs'] = array(gettext('Cache images')				 => PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&tab=images',
				gettext('Cache stored images') => PLUGIN_FOLDER . '/cacheManager/cacheDBImages.php?page=overview&tab=DB&XSRFToken=' . getXSRFToken('cacheDBImages'));
printAdminHeader('overview', 'DB');
echo "\n</head>";
echo "\n<body>";

printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';
?>
<?php printSubtabs('Cache'); ?>
<div class="tabbox">
	<?php
	zp_apply_filter('admin_note', 'cache', '');
	?>
	<p class="notebox">
		<?php
		echo gettext('This utility scans the database for images references that have been stored there.') . ' ';
		echo gettext('If an image processor URI is discovered it will be converted to a cache file URI.') . ' ';
		echo gettext('If the cache file for the image does not exist, a caching image reference will be made for the image.');
		?>
	</p>
	<?php
	$tables = array('albums' => array('desc'),
					'images' => array('desc'),
					'pages'	 => array('content', 'extracontent'),
					'news'	 => array('content', 'extracontent'));
	?>
	<form name="size_selections" action="?select" method="post">
		<?php
		$refresh = $imageprocessor = $found = $fixed = $fixedFolder = 0;
		XSRFToken('cacheDBImages');
		$watermarks = getWatermarks();
		$missingImages = NULL;
		foreach ($tables as $table => $fields) {
			foreach ($fields as $field) {
				$sql = 'SELECT * FROM ' . prefix($table) . ' WHERE `' . $field . '` REGEXP "<img.*src\s*=\s*\".*i.php((\\.|[^\"])*)"';
				$result = query($sql);
				if ($result) {
					while ($row = db_fetch_assoc($result)) {
						$imageprocessor++;
						preg_match_all('|\<\s*img.*?\ssrc\s*=\s*"(.*i\.php\?([^"]*)).*/\>|', $row[$field], $matches);
						foreach ($matches[1] as $uri) {
							$params = parse_url(html_entity_decode($uri));
							if (array_key_exists('query', $params)) {
								parse_str($params['query'], $query);
								if (!file_exists(getAlbumFolder() . $query['a'] . '/' . $query['i'])) {
									recordMissing($table, $row, $query['a'] . '/' . $query['i']);
								} else {
									$text = zpFunctions::updateImageProcessorLink($uri);
									if (strpos($text, 'i.php') !== false) {
										$url = '<img src="' . $uri . '" height="20" width="20" alt="X" />';
										$title = getTitle($table, $row) . ' ' . gettext('image processor reference');
										?>
										<a href="<?php echo $uri; ?>&amp;debug" title="<?php echo $title; ?>">
											<?php echo $url . "\n"; ?>
										</a>
										<?php
									}
									$text = zpFunctions::updateImageProcessorLink($row[$field]);
									if ($text != $row[$field]) {
										$sql = 'UPDATE ' . prefix($table) . ' SET `' . $field . '`=' . db_quote($text) . ' WHERE `id`=' . $row['id'];
										query($sql);
									} else {
										$refresh++;
									}
								}
							}
						}
					}
				}

				$sql = 'SELECT * FROM ' . prefix($table) . ' WHERE `' . $field . '` REGEXP "<img.*src\s*=\s*\".*' . CACHEFOLDER . '((\\.|[^\"])*)"';
				$result = query($sql);

				if ($result) {
					while ($row = db_fetch_assoc($result)) {
						preg_match_all('~\<img.*src\s*=\s*"((\\.|[^"])*)~', $row[$field], $matches);
						foreach ($matches[1] as $key => $match) {
							$updated = false;
							if (preg_match('~/' . CACHEFOLDER . '/~', $match)) {
								$found++;
								list($image, $args) = getImageProcessorURIFromCacheName($match, $watermarks);
								if (!file_exists(getAlbumFolder() . $image)) {
									recordMissing($table, $row, $image);
								} else {
									$uri = getImageURI($args, dirname($image), basename($image), NULL);
									if (strpos($uri, 'i.php?') !== false) {
										$fixed++;
										$title = getTitle($table, $row);
										?>
										<a href="<?php echo html_encode($uri); ?>&amp;debug" title="<?php echo $title; ?>">
											<?php
											if (isset($args[10])) {
												echo '<img src="' . html_encode(pathurlencode($uri)) . '" height="15" width="15" alt="x" />' . "\n";
											} else {
												echo '<img src="' . html_encode(pathurlencode($uri)) . '" height="20" width="20" alt="X" />' . "\n";
											}
											?>
										</a>
										<?php
									}
								}
								$cache_file = '{*WEBPATH*}/' . CACHEFOLDER . getImageCacheFilename(dirname($image), basename($image), $args);
								if ($match != $cache_file) {
									//need to update the record.
									$row[$field] = updateCacheName($row[$field], $match, $cache_file);
									$updated = true;
								}
							}
						}
						if ($updated) {
							$sql = 'UPDATE ' . prefix($table) . ' SET `' . $field . '`=' . db_quote($row[$field]) . ' WHERE `id`=' . $row['id'];
							query($sql);
						}
					}
				}
			}
		}
		if (!empty($missingImages)) {
			?>
			<div class="errorbox">
				<p>
					<?php
					echo gettext('<strong>Note:</strong> the following objects have images that appear to no longer exist.');
					?>
				</p>
				<?php
				foreach ($missingImages as $missing) {
					echo $missing;
				}
				?>
			</div>
			<?php
		}

		$button = array('text' => gettext("Refresh"), 'title' => gettext('Refresh the caching of the images stored in the database if some images did not render.'));
		?>
		<p>
			<?php
			printf(ngettext('%u image processor reference found.', '%u image processor references found.', $imageprocessor), $imageprocessor);
			if ($refresh) {
				echo ' ' . gettext('You should use the refresh button to convert these to cached image references');
			}
			?>
			<br />
			<?php
			printf(ngettext('%u cached image reference found.', '%u cached image references found.', $found), $found);
			?>
			<br />
			<?php
			printf(ngettext('%s reference re-cached.', '%s references re-cached.', $fixed), $fixed);
			?>
			<br />
			<?php
			if ($fixedFolder) {
				printf(ngettext('%s cache folder reference fixed.', '%s cache folder references fixed.', $fixedFolder), $fixedFolder);
			}
			?>
		</p>
		<p class="buttons">
			<a title="<?php echo gettext('Back to the overview'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin.php'; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" />
				<strong><?php echo gettext("Back"); ?> </strong>
			</a>
		</p>
		<?php
		if ($button) {
			?>
			<p class="buttons">
				<button class="tooltip" type="submit" title="<?php echo $button['title']; ?>" >
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/redo.png" alt="" />
					<?php echo $button['text']; ?>
				</button>
			</p>
			<?php
		}
		?>
		<br class="clearall" />
	</form>

	<?php
	echo "\n" . '</div>';
	echo "\n" . '</div>';
	echo "\n" . '</div>';

	printAdminFooter();

	echo "\n</body>";
	echo "\n</head>";
	?>
