<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package zpcore\plugins\cachemanager
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

admin_securityChecks(NULL, $return = currentRelativeURL());

XSRFdefender('cacheDBImages');

$_zp_admin_menu['overview']['subtabs'] = array(
		gettext('Cache images') => FULLWEBPATH .'/'. ZENFOLDER .'/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&tab=images',
		gettext('Cache stored images') => FULLWEBPATH .'/'. ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheDBImages.php?page=overview&tab=DB&XSRFToken=' . getXSRFToken('cacheDBImages')
);
printAdminHeader('overview', 'DB'); ?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php printSubtabs('Cache'); ?>
<div class="tabbox">
	<?php
	zp_apply_filter('admin_note', 'cache', '');
	?>
		<p><?php echo gettext('This utility scans the database for images references that have been stored there (e.g. embedded within text content).'); ?></p>
		<p><?php echo gettext('The following database fields are checked:');?></p>
		<ul>
			<li><?php echo gettext('<code>desc</code> (description) of albums and images'); ?></li>
			<li><?php echo gettext('<code>content</code> and <code>extracontent</code> of Zenpage news articles and pages.'); ?></li>
		</ul>
		<p>
		<?php 
		echo gettext('If an image processor URI is discovered it will be converted to a cache file URI.'); 
		echo gettext('If the cache file for the image does not exist, a caching image reference will be made for the image.');
		?>
		</p>
		<?php
		$searchmode = true;
		if (isset($_GET['select'])) { // form submitted for processing
			$searchmode = false;
		}
		if($searchmode) { ?>
			<p class="warningbox">
			<?php echo gettext('Since this tool modifies the database directly, it is strongely recommended to perform a database backup before executing!'); ?>
			</p>
		<?php
		}
	cacheManager::printCurlNote();
	$tables = array(
			'albums' => array('desc'),
			'images' => array('desc'),
			'pages' => array('content', 'extracontent'),
			'news' => array('content', 'extracontent'));
	?>
	<form name="size_selections" action="?select" method="post">
		<?php
		cacheManager::$missingimages = NULL;
		$refresh = $imageprocessor_total = $imageprocessor_item = $found_total = $found_item = $fixed_item = $fixed_total = 0;
		XSRFToken('cacheDBImages');
		$watermarks = getWatermarks();
		if(!$searchmode) { 
			?>
			<h2><?php echo gettext('Caching log'); ?></h2>
			<?php
		}
		foreach ($tables as $table => $fields) {
			if(!$searchmode) { 
				?>
				<ul>
				<?php
			}
			foreach ($fields as $field) {
				$sql = 'SELECT * FROM ' . $_zp_db->prefix($table) . ' WHERE `' . $field . '` REGEXP "<img.*src\s*=\s*\".*i.php((\\.|[^\"])*)"';
				$result = $_zp_db->query($sql);
				$title = '';
				if ($result) {
					$imageprocessor_item = '';
					while ($row = $_zp_db->fetchAssoc($result)) {
						$title = cacheManager::getTitle($table, $row);
						$imageprocessor_total++;
						$imageprocessor_item++;
						preg_match_all('|\<\s*img.*?\ssrc\s*=\s*"(.*i\.php\?([^"]*)).*/\>|', $row[$field], $matches); 
						foreach ($matches[1] as $uri) {
							$imageprocessor_item = '';
							$params = parse_url(html_entity_decode($uri));
							if (array_key_exists('query', $params)) {
								parse_str($params['query'], $query);
								if (!file_exists(getAlbumFolder() . $query['a'] . '/' . $query['i'])) {
									cacheManager::recordMissing($table, $row, $query['a'] . '/' . $query['i']);
								} else {
									$text = updateImageProcessorLink($row[$field]);
									if ($text != $row[$field] && !$searchmode) {
										$sql = 'UPDATE ' . $_zp_db->prefix($table) . ' SET `' . $field . '`=' . $_zp_db->quote($text) . ' WHERE `id`=' . $row['id'];
										$success = $_zp_db->query($sql);
										if($success) { 
											echo '<li><strong>'. $title . '</strong> – <em>' . $field . '</em>: ' . sprintf(ngettext('%u image processor reference updated.', '%u image processor references updated.', $imageprocessor_item), $imageprocessor_item) . '</li>';
										}
									} 
								}
							} 
						}
					}
				} 
		
				$sql = 'SELECT * FROM ' . $_zp_db->prefix($table) . ' WHERE `' . $field . '` REGEXP "<img.*src\s*=\s*\".*' . CACHEFOLDER . '((\\.|[^\"])*)"';
				$result = $_zp_db->query($sql);

				if ($result) {
					while ($row = $_zp_db->fetchAssoc($result)) {
						preg_match_all('~\<img.*src\s*=\s*"((\\.|[^"])*)~', $row[$field], $matches);
						$found_item = $fixed_item = '';
						foreach ($matches[1] as $key => $match) {
							$updated = false;
							if (preg_match('~/' . CACHEFOLDER . '/~', $match)) {
								$found_total++;
								$found_item++;
								$match = unTagURLs($match);
								$cached_appendix = strstr($match, '?cached=');
								$match = str_replace($cached_appendix, '', $match);
								$image_cleared = getImageProcessorURIFromCacheName($match, $watermarks);
								list($image, $args) = getImageProcessorURIFromCacheName($match, $watermarks);
								if (!file_exists(getAlbumFolder() . $image)) {
									cacheManager::recordMissing($table, $row, $image);
								} else {
									$uri = getImageURI($args, dirname($image), basename($image), NULL);
									$title = cacheManager::getTitle($table, $row);
									if (strpos($uri, 'i.php?') !== false) {		
										$fixed_total++;
										if (!$searchmode) {
											$fixed_item++;
											cacheManager::generateImage($uri);
										}
									} 
								}
								$cache_file = '{*WEBPATH*}/' . CACHEFOLDER . getImageCacheFilename(dirname($image), basename($image), $args);
								if ($match != $cache_file) {
									//need to update the record.
									$row[$field] = cacheManager::updateCacheName($row[$field], $match, $cache_file);
									$updated = true;
								}
							} 
						}
						if (!$searchmode) {
							if ($updated) {
								$sql = 'UPDATE ' . $_zp_db->prefix($table) . ' SET `' . $field . '`=' . $_zp_db->quote($row[$field]) . ' WHERE `id`=' . $row['id'];
								$success = $_zp_db->query($sql);
								if($success) {
									echo '<li><strong>'. $title . '</strong> – <em>' . $field . '</em>: ' . sprintf(ngettext('%1$u of %2$u found cached image required re-caching.', '%1$u of %2$u found cached images required re-caching.', $found_item), $fixed_item, $found_item) . '</li>';
								}
							} else {
								$refresh++;
							}
						}
					}
				}
			}
		if(!$searchmode) { 
			?>
			</ul>
			<?php
		}
		}
		?>
		<h2><?php echo gettext('Statistics'); ?></h2>
		<?php
		if (!empty(cacheManager::$missingimages)) {
			?>
			<div class="errorbox">
				<p>
					<?php
					echo gettext('<strong>Note:</strong> the following objects have images that appear to no longer exist.');
					?>
				</p>
				<ol>
				<?php
				foreach (cacheManager::$missingimages as $missing) {
					?><li>
						<?php echo $missing; ?>
					</li><?php
				}
				?>
				</ol>
			</div>
			<?php
		}
		?>
		<ul>
			<li>
				<?php
				printf(ngettext('%u image processor reference found.', '%u image processor references found.', $imageprocessor_total), $imageprocessor_total);
				if ($refresh) {
					echo ' ' . gettext('You should use the refresh button to convert these to cached image references');
				}
				?>
			</li>
			<li>
				<?php 
					if($searchmode) { 
						printf(ngettext('%1$u of %2$u found cached image requires re-caching.', '%1$u of %2$u found cached images require re-caching.', $found_total), $fixed_total, $found_total);
					} else {
						printf(ngettext('%1$u of %2$u found cached image re-cached.', '%1$u of %2$u found cached images re-cached.', $found_total ), $fixed_total, $found_total );
					}
					?>
			</li>
		</ul>
		<p class="buttons clearfix">
			<a title="<?php echo gettext('Back to the overview'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin.php'; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" />
				<strong><?php echo gettext("Back"); ?> </strong>
			</a>
			<button class="tooltip" type="submit" title="<?php echo gettext('Cache stored images'); ?>" >
				<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
				<?php echo gettext('Cache stored images'); ?>
			</button>
		</p>
	</form>

</div>
</div>
</div>
<?php printAdminFooter(); ?>

</body>
</html>
