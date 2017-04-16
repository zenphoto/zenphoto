<?php
/**
 * Detailed Gallery Statistics
 *
 * This plugin shows statistical graphs and info about your gallery\'s images and albums
 *
 * @package admin
 */
define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');

$tables = array('albums', 'images');
if (extensionEnabled('zenpage')) {
	require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/zenpage/admin-functions.php');
	$tables = array_merge($tables, array('news', 'pages'));
}
// Include the appropriate page for the requested object, and a 200 OK header.
foreach ($tables as $table) {
	updatePublished($table);
}

admin_securityChecks(OVERVIEW_RIGHTS, currentRelativeURL());

$_zp_gallery->garbageCollect();

printAdminHeader('overview', 'statistics');
?>
<link rel="stylesheet" href="../admin-statistics.css" type="text/css" media="screen" />
<?php
/*
 * http://php.net/manual/de/function.filesize.php
 *
 * @author Jonas Sweden
 */

function gallerystats_filesize_r($path) {
	if (!file_exists($path))
		return 0;
	if (is_file($path))
		return filesize($path);
	$ret = 0;
	foreach (safe_glob($path . "/*") as $fn) {
		$ret += gallerystats_filesize_r($fn);
	}
	return $ret;
}

/**
 * Prints a table with a bar graph of the values.
 *
 * @param string $sortorder "popular", "mostrated","toprated","mostcommented" or - only if $type = "albums"! - "mostimages"
 * @param string_type $type "albums", "images", "pages", "news", "tags"
 * @param int $queryLimit Number of entries to show
 */
function printBarGraph($sortorder = "mostimages", $type = "albums", $from_number = 0, $to_number = NULL) {
	global $_zp_gallery;
	if (is_null($to_number)) {
		$queryLimit = "0,11";
		$limit = 10;
	} else {
		$queryLimit = ($from_number = max($from_number, 0)) . "," . ($limit = $to_number);
	}

	$bargraphmaxsize = 90;
	switch ($type) {
		case "albums":
			$typename = gettext("Albums");
			$dbquery = "SELECT * FROM " . prefix('albums');
			break;
		case "images":
			$typename = gettext("Images");
			$dbquery = "SELECT * FROM " . prefix('images');
			break;
		case "pages":
			$typename = gettext("Pages");
			$dbquery = "SELECT * FROM " . prefix('pages');
			break;
		case "news":
			$typename = gettext("News Articles");
			$dbquery = "SELECT * FROM " . prefix('news');
			break;
		case "newscategories":
			$typename = gettext("News Categories");
			$dbquery = "SELECT * FROM " . prefix('news_categories');
			break;
		case 'scripts':
			$typename = gettext('Script Pages');
			break;
		case "tags":
			$typename = gettext("Tags");
			break;
		case "rss":
			$typename = gettext("rss");
			break;
	}
	switch ($sortorder) {
		case "mostused":
			switch ($type) {
				case "tags":
					$dbquery = "SELECT tagobj.tagid, count(*) as tagcount, tags.* FROM " . prefix('obj_to_tag') . " AS tagobj, " . prefix('tags') . " AS tags WHERE tags.id=tagobj.tagid GROUP BY tags.id ORDER BY tagcount DESC LIMIT " . $queryLimit;
					$itemssorted = query_full_array($dbquery);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['tagcount'];
					}
					break;
				case"newscategories":
					$dbquery = "SELECT news2cat.cat_id, count(*) as catcount, cats.* FROM " . prefix('news2cat') . " AS news2cat, " . prefix('news_categories') . " AS cats WHERE cats.id=news2cat.cat_id GROUP BY news2cat.cat_id ORDER BY catcount DESC LIMIT " . $queryLimit;
					$itemssorted = query_full_array($dbquery);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['catcount'];
					}
					break;
			}
			$headline = $typename . " - " . gettext("most used");
			break;
		case "popular":
			switch ($type) {
				case 'rss':
					$dbquery = "SELECT `type`,`aux`, `data` as 'hitcounter' FROM " . prefix('plugin_storage') . " WHERE `type`='hitcounter' AND `subtype`='rss' ORDER BY CONVERT(data,UNSIGNED) DESC LIMIT " . $queryLimit;
					$itemssorted = query_full_array($dbquery);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['hitcounter'];
					}
					break;
				case'scripts':
					$maxvalue = 0;
					$itemssorted = array();
					$hitcounters = getSerializedArray(getOption('page_hitcounters'));
					$hitcounters['index'] = $_zp_gallery->getHitcounter();
					arsort($hitcounters, SORT_NUMERIC);
					foreach ($hitcounters as $script => $value) {
						$itemssorted[] = array('type' => 'scripthitcounter', 'aux' => $script, 'hitcounter' => $value);
						if ($value > $maxvalue) {
							$maxvalue = $value;
						}
					}
					break;
				default:
					$dbquery .= " WHERE `hitcounter`>0 ORDER BY hitcounter DESC LIMIT " . $queryLimit;
					$itemssorted = query_full_array($dbquery);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['hitcounter'];
					}
					break;
			}
			$headline = $typename . " - " . gettext("most viewed");
			break;
		case "popularimages":
			$dbquery = "SELECT a.id, a.folder,a.title, SUM( i.hitcounter ) AS hits  FROM " . prefix('albums') . " a INNER JOIN " . prefix('images') . " i ON i.albumid = a.id WHERE i.hitcounter>0 GROUP BY i.albumid ORDER BY hits DESC LIMIT " . $queryLimit;
			$itemssorted = query_full_array($dbquery);
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['hits'];
			}
			$headline = $typename . " - " . gettext("most viewed images");
			break;
		case "mostrated":
			$dbquery .= " WHERE `total_votes`>0 ORDER BY total_votes DESC LIMIT " . $queryLimit;
			$itemssorted = query_full_array($dbquery);
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['total_votes'];
			}
			$headline = $typename . " - " . gettext("most rated");
			break;
		case "toprated":
			$dbquery .= " WHERE `total_votes`>0 ORDER BY (total_value/total_votes) DESC, total_value DESC LIMIT $queryLimit";
			$itemssorted = query_full_array($dbquery);
			$maxvalue = 0;
			if (!empty($itemssorted)) {
				if ($itemssorted[0]['total_votes'] != 0) {
					$maxvalue = ($itemssorted[0]['total_value'] / $itemssorted[0]['total_votes']);
				}
			}
			$headline = $typename . " - " . gettext("top rated");
			break;
		case "mostcommented":
			$dbquery = "SELECT comments.ownerid, count(*) as commentcount, " . $type . ".* FROM " . prefix('comments') . " AS comments, " . prefix($type) . " AS " . $type . " WHERE " . $type . ".id=comments.ownerid AND type = '" . $type . "' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $queryLimit;
			$itemssorted = query_full_array($dbquery);
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['commentcount'];
			}
			$headline = $typename . " - " . gettext("most commented");
			break;
		case "mostimages":
			$dbquery = "SELECT images.albumid, count(*) as imagenumber, albums.* FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums WHERE albums.id=images.albumid GROUP BY images.albumid ORDER BY imagenumber DESC LIMIT " . $queryLimit;
			$itemssorted = query_full_array($dbquery);
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['imagenumber'];
			}
			$headline = $typename . " - " . gettext("most images");
			break;
		case "latest":
			switch ($type) {
				case "albums":
					$dbquery .= " ORDER BY id DESC LIMIT " . $queryLimit;
					$allalbums = query_full_array($dbquery);
					$albums = array();
					foreach ($allalbums as $album) {
						$albumobj = newAlbum($album['folder']);
						if ($albumobj->loaded) {
							$albumentry = array("id" => $albumobj->getID(), "title" => $albumobj->getTitle(), "folder" => $albumobj->name, "imagenumber" => $albumobj->getNumImages(), "show" => $albumobj->getShow());
							array_unshift($albums, $albumentry);
						}
					}
					$maxvalue = 1;
					$itemssorted = sortMultiArray($albums, 'id', true, true); // The items are originally sorted by id;
					$headline = $typename . " - " . gettext("latest");
					break;
				case "images":
					$dbquery .= " ORDER BY id DESC LIMIT " . $queryLimit;
					$itemssorted = query_full_array($dbquery);
					$barsize = 0;
					$maxvalue = 1;
					$headline = $typename . " - " . gettext("latest");
					break;
			}
			break;
		case "latestupdated":
			$albumObjects = getAlbumStatistic($to_number, 'latestupdated');
			$maxvalue = 1;
			$itemssorted = array();
			if (!empty($albumObjects)) {
				foreach ($albumObjects as $key => $albumobj) {
					$itemssorted[$key] = $albumobj->getData();
					if ($albumobj->loaded)
						$itemssorted[$key]['imagenumber'] = $albumobj->getNumImages();
				}
			}

			$headline = $typename . " - " . gettext("latest updated");
			break;
	}
	if ($maxvalue == 0 || empty($itemssorted)) {
		$maxvalue = 1;
		$no_hitcount_enabled_msg = '';
		if ($sortorder == 'popular' && $type != 'rss' && !extensionEnabled('hitcounter')) {
			$no_hitcount_enabled_msg = gettext("(The hitcounter plugin is not enabled.)");
		}
		$no_statistic_message = "<tr><td><em>" . gettext("No statistic available.") . $no_hitcount_enabled_msg . "</em></td><td></td><td></td><td></td></tr>";
	} else {
		$no_statistic_message = "";
		if (($sortorder == 'popular' || $sortorder == 'popularimages') && $type != 'rss' && !extensionEnabled('hitcounter')) {
			$no_statistic_message = "<tr><td colspan='100%'><em>" . gettext("Note: The hitcounter plugin is not enabled, therefore any existing values will not get updated.") . "</em></td><td></td><td></td><td></td></tr>";
		}
	}

	$count = $from_number + 1; //	counting numbers start at 1!

	echo "<table class='bordered'>";
	echo "<tr><th colspan='100%'><strong>" . $headline . "</strong>";

	if (!isset($_GET['stats'])) {
		if (count($itemssorted) > 10) {
			echo "<a href='gallery_statistics.php?stats=" . $sortorder . "&amp;type=" . $type . "'> | " . gettext("View more") . "</a>";
		}
	}
	echo "</th></tr>";
	echo $no_statistic_message;

	$maxvalue = ceil($maxvalue / 10) * 10;

	foreach ($itemssorted as $item) {
		if (array_key_exists("filename", $item)) {
			$name = $item['filename'];
		} else if (array_key_exists("folder", $item)) {
			$name = $item['folder'];
		} else if (array_key_exists("titlelink", $item)) {
			$name = $item['titlelink'];
		} else {
			$name = "";
		}
		switch ($sortorder) {
			case "popular":
				$barsize = $item['hitcounter'] / $maxvalue * $bargraphmaxsize;
				$value = $item['hitcounter'];
				break;
			case 'popularimages':
				$barsize = $item['hits'] / $maxvalue * $bargraphmaxsize;
				$value = $item['hits'];
				break;
			case "mostrated":
				$barsize = $item['total_votes'] / $maxvalue * $bargraphmaxsize;
				$value = $item['total_votes'];
				break;
			case "toprated":
				$barsize = ($item['total_value'] / $item['total_votes']) / $maxvalue * $bargraphmaxsize;
				$value = $item['total_value'] / $item['total_votes'];
				break;
			case "mostcommented":
				if ($maxvalue != 0) {
					$barsize = $item['commentcount'] / $maxvalue * $bargraphmaxsize;
				} else {
					$barsize = 0;
				}
				$value = $item['commentcount'];
				break;
			case "mostimages":
				$barsize = $item['imagenumber'] / $maxvalue * $bargraphmaxsize;
				$value = $item['imagenumber'];
				break;
			case "latest":
				switch ($type) {
					case "albums":
						$barsize = 0;
						$value = sprintf(gettext("%s images"), $item['imagenumber']);
						break;
					case "images":
						$barsize = 0;
						$value = "";
						break;
				}
				break;
			case "latestupdated":
				$barsize = 0;
				$value = sprintf(gettext("%s images"), $item['imagenumber']);
				break;
			case "mostused":
				switch ($type) {
					case "tags":
						if ($maxvalue != 0) {
							$barsize = $item['tagcount'] / $maxvalue * $bargraphmaxsize;
						} else {
							$barsize = 0;
						}
						$value = $item['tagcount'];
						break;
					case "newscategories":
						if ($maxvalue != 0) {
							$barsize = $item['catcount'] / $maxvalue * $bargraphmaxsize;
						} else {
							$barsize = 0;
						}
						$value = $item['catcount'];
						break;
				}
				break;
		}
		// counter to have a gray background of every second line
		if ($count % 2) {
			$style = " style='background-color: #f4f4f4'"; // a little ugly but the already attached class for the table is so easiest overriden...
		} else {
			$style = "";
		}

		switch ($type) {
			case "albums":
				$editurl = WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . $name;
				$viewurl = WEBPATH . "/index.php?album=" . $name;
				$title = get_language_string($item['title']);
				break;
			case "images":
				if ($item['albumid']) {
					$getalbumfolder = query_single_row("SELECT title, folder, `show` from " . prefix("albums") . " WHERE id = " . $item['albumid']);
					if ($sortorder === "latest") {
						$value = "<span";
						if ($getalbumfolder['show'] != "1") {
							$value = $value . " class='unpublished_item'";
						}
						$value = $value . ">" . get_language_string($getalbumfolder['title']) . "</span> (" . $getalbumfolder['folder'] . ")";
					}
					$editurl = WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . $getalbumfolder['folder'] . "&amp;image=" . $item['filename'] . "&amp;tab=imageinfo#IT";
					$viewurl = WEBPATH . "/index.php?album=" . $getalbumfolder['folder'] . "&amp;image=" . $name;
					$title = get_language_string($item['title']);
				}
				break;
			case "pages":
				$editurl = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;titlelink=" . $name;
				$viewurl = WEBPATH . "/index.php?p=pages&amp;title=" . $name;
				$title = get_language_string($item['title']);
				break;
			case "news":
				$editurl = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;titlelink=" . $name;
				$viewurl = WEBPATH . "/index.php?p=news&amp;title=" . $name;
				$title = get_language_string($item['title']);
				break;
			case "newscategories":
				$editurl = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newscategory&amp;titlelink=" . $name;
				$viewurl = WEBPATH . "/index.php?p=news&amp;category=" . $name;
				$title = get_language_string($item['titlelink']);
				break;
			case "tags":
				$editurl = WEBPATH . '/' . ZENFOLDER . "/admin-tags.php";
				$viewurl = WEBPATH . "/index.php?p=search&amp;searchfields=tags&amp;words=" . $item['name'];
				$title = get_language_string($item['name']);
				break;
			case "rss":
				$editurl = $viewurl = '';
				if (file_exists(SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/rss/' . $item['aux'])) {
					$viewurl = WEBPATH . '/' . STATIC_CACHE_FOLDER . '/rss/' . html_encode($item['aux']);
				}
				$title = html_encode(stripSuffix($item['aux']));
				break;
			case 'scripts':
				$editurl = '';
				$page = $item['aux'];
				if ($page == 'index') {
					$viewurl = WEBPATH . '/index.php';
				} else {
					$viewurl = WEBPATH . '/page/' . $page;
				}
				$title = html_encode($item['aux']);
				break;
		}
		if (isset($item['show']) && $item['show'] != "1") {
			$show = " class='unpublished_item'";
		} else {
			$show = "";
		}
		if ($value != 0 || $sortorder === "latest") {
			if (!empty($name)) {
				$name = "(" . $name . ")";
			}
			?>
			<tr class="statistic_wrapper">
				<td class="statistic_counter" <?php echo $style; ?>>
					<?php echo $count; ?>
				</td>
				<td class="statistic_title" <?php echo $style; ?>>
					<strong<?php echo $show; ?>><?php echo $title; ?></strong> <?php echo $name; ?>
				</td>
				<td class="statistic_graphwrap" <?php echo $style; ?>>
					<div class="statistic_bargraph" style="width: <?php echo ceil($barsize); ?>%"></div>
					<div class="statistic_value"><?php echo $value; ?></div>
				</td>
				<td class="statistic_link" <?php echo $style; ?>>
					<?php
					switch ($type) {
						case'scripts':
						case 'rss':
							if ($viewurl) {
								echo "<a href='" . $viewurl . "' title='" . $name . "'>" . gettext("View") . "</a></td>";
							}
							break;
						default:
							echo "<a href='" . $editurl . "' title='" . $name . "'>" . gettext("Edit") . "</a> | <a href='" . $viewurl . "' title='" . $name . "'>" . gettext("View") . "</a></td>";
							break;
					}
					?>
			</tr>
			<?php
			if ($count >= $limit) {
				break;
			}
			$count++;
		}
	} // foreach end
	echo "</table>";
}
?>

<?php
echo '</head>';
?>

<body>
	<?php
	printLogoAndLinks();
	?>
	<div id="main">
		<?php
		printTabs();

// getting the counts
		$albumcount = $_zp_gallery->getNumAlbums(true);
		$albumscount_unpub = $albumcount - $_zp_gallery->getNumAlbums(true, true);
		$imagecount = $_zp_gallery->getNumImages();
		$imagecount_unpub = $imagecount - $_zp_gallery->getNumImages(true);
		?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'statistics', ''); ?>
			<h1><?php echo gettext("Gallery Statistics"); ?></h1>
			<div class="tabbox">
				<p><?php echo gettext("This page shows more detailed statistics of your gallery. For album statistics the bar graph always shows the total number of images in that album. For image statistics always the album the image is in is shown.<br />Un-published items are marked in dark red. Images are marked un-published if their (direct) album is, too."); ?></p>

				<ul class="statistics_general">
					<li>
						<?php
						if ($imagecount_unpub > 0) {
							printf(gettext('<strong>%1$u</strong> images (%2$u un-published)'), $imagecount, $imagecount_unpub);
						} else {
							printf(gettext('<strong>%u</strong> images'), $imagecount);
						}
						?>
					</li>
					<li>
						<?php
						if ($albumscount_unpub > 0) {
							printf(gettext('<strong>%1$u</strong> albums (%2$u un-published)'), $albumcount, $albumscount_unpub);
						} else {
							printf(gettext('<strong>%u</strong> albums'), $albumcount);
						}
						?>
					</li>
					<li>
						<?php
						$commentcount = $_zp_gallery->getNumComments(true);
						$commentcount_mod = $commentcount - $_zp_gallery->getNumComments(false);
						if ($commentcount_mod > 0) {
							if ($commentcount != 1) {
								printf(gettext('<strong>%1$u</strong> comments (%2$u in moderation)'), $commentcount, $commentcount_mod);
							} else {
								printf(gettext('<strong>1</strong> comment (%u in moderation)'), $commentcount_mod);
							}
						} else {
							if ($commentcount != 1) {
								printf(gettext('<strong>%u</strong> comments'), $commentcount);
							} else {
								echo gettext('<strong>1</strong> comment');
							}
						}
						?>
					</li>
					<?php if (extensionEnabled('zenpage')) { ?>
						<li>
							<?php
							list($total, $type, $unpub) = getNewsPagesStatistic("pages");
							if (empty($unpub)) {
								printf(gettext('<strong>%1$u</strong> Pages'), $total, $type);
							} else {
								printf(gettext('<strong>%1$u</strong> Pages (%2$u un-published)'), $total, $unpub);
							}
							?>
						</li>
						<li>
							<?php
							list($total, $type, $unpub) = getNewsPagesStatistic("news");
							if (empty($unpub)) {
								printf(gettext('<strong>%1$u</strong> News articles'), $total);
							} else {
								printf(gettext('<strong>%1$u</strong> News articles (%2$u un-published)'), $total, $unpub);
							}
							?>
						</li>
						<li>
							<?php
							list($total, $type, $unpub) = getNewsPagesStatistic("categories");
							printf(gettext('<strong>%1$u</strong> Categories'), $total);
							?>
						</li>
					<?php }
					?>
					<li><nobr><?php printf(gettext("Albums folder size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(ALBUM_FOLDER_SERVERPATH))); ?></nobr></li>
					<li><nobr><?php printf(gettext("Image cache size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(SERVERPATH . '/' . CACHEFOLDER))); ?></nobr></li>
					<li><nobr><?php printf(gettext("HTML cache size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(SERVERPATH . '/' . STATIC_CACHE_FOLDER))); ?></nobr></li>
					<li><nobr><?php printf(gettext("Uploaded folder size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(SERVERPATH . '/' . UPLOAD_FOLDER))); ?></nobr></li>
					<li><nobr><?php printf(gettext("Core scripts size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(SERVERPATH . '/' . ZENFOLDER))); ?></nobr></li>

				</ul>

				<?php
				if (!isset($_GET['stats']) AND ! isset($_GET['fulllist'])) {
					?>
					<ul class="statistic_navlist">
						<li><strong><?php echo gettext("Images"); ?></strong>
							<ul>
								<li><a href="#images-latest"><?php echo gettext("latest"); ?></a> | </li>
								<li><a href="#images-popular"><?php echo gettext("most viewed"); ?></a> | </li>
								<li><a href="#images-mostrated"><?php echo gettext("most rated"); ?></a> | </li>
								<li><a href="#images-toprated"><?php echo gettext("top rated"); ?></a> | </li>
								<li><a href="#images-mostcommented"><?php echo gettext("most commented"); ?></a></li>
							</ul>
						</li>
						<li><strong><?php echo gettext("Albums"); ?></strong>
							<ul>
								<li><a href="#albums-latest"><?php echo gettext("latest"); ?></a> | </li>
								<li><a href="#albums-latestupdated"><?php echo gettext("latest updated"); ?></a> | </li>
								<li><a href="#albums-mostimages"><?php echo gettext("most images"); ?></a> | </li>
								<li><a href="#albums-popular"><?php echo gettext("most viewed"); ?></a> | </li>
								<li><a href="#albums-popularimages"><?php echo gettext("most viewed images"); ?></a> | </li>
								<li><a href="#albums-mostrated"><?php echo gettext("most rated"); ?></a> | </li>
								<li><a href="#albums-toprated"><?php echo gettext("top rated"); ?></a> | </li>
								<li><a href="#albums-mostcommented"><?php echo gettext("most commented"); ?></a></li>
							</ul>
						</li>
						<li><strong><?php echo gettext("Tags"); ?></strong>
							<ul>
								<li><a href="#tags-mostused"><?php echo gettext("most used"); ?></a></li>
							</ul>
						</li>
						<?php if (extensionEnabled('zenpage')) { ?>
							<li><strong><?php echo gettext("Pages"); ?></strong>
								<ul>
									<li><a href="#pages-popular"><?php echo gettext("most viewed"); ?></a> | </li>
									<li><a href="#pages-mostcommented"><?php echo gettext("most commented"); ?></a></li>
									<li><a href="#pages-mostrated"><?php echo gettext("most rated"); ?></a> | </li>
									<li><a href="#pages-toprated"><?php echo gettext("top rated"); ?></a></li>
								</ul>
							</li>
							<li><strong><?php echo gettext("News articles"); ?></strong>
								<ul>
									<li><a href="#news-popular"><?php echo gettext("most viewed"); ?></a> | </li>
									<li><a href="#news-mostcommented"><?php echo gettext("most commented"); ?></a></li>
									<li><a href="#news-mostrated"><?php echo gettext("most rated"); ?></a> | </li>
									<li><a href="#news-toprated"><?php echo gettext("top rated"); ?></a></li>
								</ul>
							</li>
							<li><strong><?php echo gettext("News categories"); ?></strong>
								<ul>
									<li><a href="#newscategories-popular"><?php echo gettext("most viewed"); ?></a> | </li>
									<li><a href="#newscategories-mostused"><?php echo gettext("most used"); ?></a></li>
								</ul>
							</li>
							<li><strong><?php echo gettext("RSS feeds"); ?></strong>
								<ul>
									<li><a href="#rss-popular"><?php echo gettext("most viewed"); ?></a></li>
								</ul>
							</li>
						<?php } ?>
					</ul>
					<br style="clear:both" />

					<!-- images -->
					<span id="images-latest"></span>
					<?php printBarGraph("latest", "images"); ?>

					<span id="images-popular"></span>
					<?php printBarGraph("popular", "images"); ?>

					<span id="images-mostrated"></span>
					<?php printBarGraph("mostrated", "images"); ?>

					<span id="images-toprated"></span>
					<?php printBarGraph("toprated", "images"); ?>

					<span id="images-mostcommented"></span>
					<?php printBarGraph("mostcommented", "images"); ?>

					<hr />

					<!-- albums -->
					<span id="albums-latest"></span>
					<?php printBarGraph("latest", "albums"); ?>

					<span id="albums-latestupdated"></span>
					<?php printBarGraph("latestupdated", "albums"); ?>

					<span id="albums-mostimages"></span>
					<?php printBarGraph("mostimages", "albums"); ?>

					<span id="albums-popular"></span>
					<?php printBarGraph("popular", "albums"); ?>

					<span id="albums-popularimages"></span>
					<?php printBarGraph("popularimages", "albums"); ?>

					<span id="albums-mostrated"></span>
					<?php printBarGraph("mostrated", "albums"); ?>

					<span id="albums-toprated"></span>
					<?php printBarGraph("toprated", "albums"); ?>

					<span id="albums-mostcommented"></span>
					<?php printBarGraph("mostcommented", "albums"); ?>

					<hr />

					<span id="tags-mostused"></span>
					<?php printBarGraph("mostused", "tags"); ?>

					<?php if (extensionEnabled('zenpage')) { ?>
						<hr />
						<!-- Zenpage pages -->
						<span id="pages-popular"></span>
						<?php printBarGraph("popular", "pages"); ?>

						<span id="pages-mostcommented"></span>
						<?php printBarGraph("mostcommented", "pages"); ?>

						<span id="pages-mostrated"></span>
						<?php printBarGraph("mostrated", "pages"); ?>

						<span id="pages-toprated"></span>
						<?php printBarGraph("toprated", "pages"); ?>

						<hr />

						<!-- Zenpage news articles -->
						<span id="news-popular"></span>
						<?php printBarGraph("popular", "news"); ?>

						<span id="news-mostcommented"></span>
						<?php printBarGraph("mostcommented", "news"); ?>

						<span id="news-mostrated"></span>
						<?php printBarGraph("mostrated", "news"); ?>

						<span id="news-toprated"></span>
						<?php printBarGraph("toprated", "news"); ?>
						<hr />

						<h2><?php echo gettext('Statistics for news categories'); ?></h2>
						<span id="newscategories-popular"></span>
						<?php printBarGraph("popular", "newscategories"); ?>

						<span id="newscategories-mostarticles"></span>
						<?php printBarGraph("mostused", "newscategories"); ?>

						<?php
						if (extensionEnabled('hitcounter')) {
							?>
							<h2><?php echo gettext('Statistics for Script pages'); ?></h2>
							<span id="scriptpage-popular"></span>
							<?php
							printBarGraph("popular", "scripts");
						}
						?>

						<h2><?php echo gettext('Statistics for RSS feeds'); ?></h2>
						<span id="rss-popular"></span>
						<?php printBarGraph("popular", "rss"); ?>

					<?php } ?>
					<?php
				}

				// If a single list is requested
				if (isset($_GET['type'])) {
					if (!isset($_GET['from_number'])) {
						$from_number = 0;
					} else {
						$from_number = sanitize_numeric($_GET['from_number']) - 1;
					}
					if (!isset($_GET['to_number'])) {
						$to_number = 50;
						if ($_GET['type'] === "images" AND $to_number > $imagecount) {
							$to_number = $imagecount;
						}
						if ($_GET['type'] === "albums" AND $to_number > $albumcount) {
							$to_number = $albumcount;
						}
					} else {
						$to_number = sanitize_numeric($_GET['to_number']);
						if ($from_number < $to_number) {
							$to_number = $to_number - $from_number;
						}
					}
					?>
					<form name="limit" id="limit" action="gallery_statistics.php">
						<label for="from_number"><?php echo gettext("From "); ?></label>
						<input type ="text" size="10" id="from_number" name="from_number" value="<?php echo ($from_number + 1); ?>" />
						<label for="to_number"><?php echo gettext("to "); ?></label>
						<input type ="text" size="10" id="to_number" name="to_number" value="<?php echo ($to_number + $from_number); ?>" />
						<input type="hidden" name="stats"	value="<?php echo html_encode(sanitize($_GET['stats'])); ?>" />
						<input type="hidden" name="type" value="<?php echo html_encode(sanitize($_GET['type'])); ?>" />
						<input type="submit" value="<?php echo gettext("Show"); ?>" />
					</form>
					<br />
					<?php
					switch ($_GET['type']) {
						case "albums":
							switch ($_GET['stats']) {
								case "latest":
									printBarGraph("latest", "albums", $from_number, $to_number);
									break;
								case "latestupdated":
									printBarGraph("latestupdated", "albums", $from_number, $to_number);
									break;
								case "popular":
									printBarGraph("popular", "albums", $from_number, $to_number);
									break;
								case "popularimages":
									printBarGraph("popularimages", "albums", $from_number, $to_number);
									break;
								case "mostrated":
									printBarGraph("mostrated", "albums", $from_number, $to_number);
									break;
								case "toprated":
									printBarGraph("toprated", "albums", $from_number, $to_number);
									break;
								case "mostcommented":
									printBarGraph("mostcommented", "albums", $from_number, $to_number);
									break;
								case "mostimages":
									printBarGraph("mostimages", "albums", $from_number, $to_number);
									break;
							}
							break;
						case "images":
							switch ($_GET['stats']) {
								case "latest":
									printBarGraph("latest", "images", $from_number, $to_number);
									break;
								case "popular":
									printBarGraph("popular", "images", $from_number, $to_number);
									break;
								case "mostrated":
									printBarGraph("mostrated", "images", $from_number, $to_number);
									break;
								case "toprated":
									printBarGraph("toprated", "images", $from_number, $to_number);
									break;
								case "mostcommented":
									printBarGraph("mostcommented", "images", $from_number, $to_number);
									break;
							}
							break;
						case "tags":
							printBarGraph("mostused", "tags", $from_number, $to_number);
							break;
						case "rss":
							printBarGraph("popular", "rss", $from_number, $to_number);
							break;
						case "pages":
							if (extensionEnabled('zenpage')) {
								switch ($_GET['stats']) {
									case "popular":
										printBarGraph("popular", "pages", $from_number, $to_number);
										break;
									case "mostcommented":
										printBarGraph("mostcommented", "pages", $from_number, $to_number);
										break;
									case "mostrated":
										printBarGraph("mostrated", "pages", $from_number, $to_number);
										break;
									case "toprated":
										printBarGraph("toprated", "pages", $from_number, $to_number);
										break;
								}
							}
							break;
						case "news":
							if (extensionEnabled('zenpage')) {
								switch ($_GET['stats']) {
									case "popular":
										printBarGraph("popular", "news", $from_number, $to_number);
										break;
									case "mostcommented":
										printBarGraph("mostcommented", "news", $from_number, $to_number);
										break;
									case "mostrated":
										printBarGraph("mostrated", "news", $from_number, $to_number);
										break;
									case "toprated":
										printBarGraph("toprated", "news", $from_number, $to_number);
										break;
								}
							}
							break;
						case "newscategories":
							if (extensionEnabled('zenpage')) {
								switch ($_GET['stats']) {
									case "popular":
										printBarGraph("popular", "newscategories", $from_number, $to_number);
										break;
									case "mostused":
										printBarGraph("mostused", "newscategories", $from_number, $to_number);
										break;
								}
							}
							break;
					} // main switch end
				} // main if end
				?>
			</div>
		</div><!-- content -->
		<?php printAdminFooter(); ?>
	</div><!-- main -->

</body>

<?php echo "</html>"; ?>
