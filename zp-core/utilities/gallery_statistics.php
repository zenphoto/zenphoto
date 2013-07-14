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

if (extensionEnabled('zenpage')) {
	require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-admin-functions.php');
}

$buttonlist[] = array(
				'category'		 => gettext('Info'),
				'enable'			 => true,
				'button_text'	 => gettext('Gallery Statistics'),
				'formname'		 => 'gallery_statistics.php',
				'action'			 => 'utilities/gallery_statistics.php',
				'icon'				 => 'images/bar_graph.png',
				'title'				 => gettext('Shows statistical graphs and info about your gallery\'s images and albums.'),
				'alt'					 => '',
				'hidden'			 => '',
				'rights'			 => ADMIN_RIGHTS
);

admin_securityChecks(OVERVIEW_RIGHTS, currentRelativeURL());

$_zp_gallery->garbageCollect();
$webpath = WEBPATH . '/' . ZENFOLDER . '/';

$zenphoto_tabs['overview']['subtabs'] = array(gettext('Statistics') => '');
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
 * @param int $limit Number of entries to show
 */
function printBarGraph($sortorder = "mostimages", $type = "albums", $from_number = 0, $to_number = 10) {
	global $webpath;
	$limit = $from_number . "," . $to_number;
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
					$itemssorted = query_full_array("SELECT tagobj.tagid, count(*) as tagcount, tags.* FROM " . prefix('obj_to_tag') . " AS tagobj, " . prefix('tags') . " AS tags WHERE tags.id=tagobj.tagid GROUP BY tags.id ORDER BY tagcount DESC LIMIT " . $limit);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['tagcount'];
					}
					break;
				case"newscategories":
					$itemssorted = query_full_array("SELECT news2cat.cat_id, count(*) as catcount, cats.* FROM " . prefix('news2cat') . " AS news2cat, " . prefix('news_categories') . " AS cats WHERE cats.id=news2cat.cat_id GROUP BY news2cat.cat_id ORDER BY catcount DESC LIMIT " . $limit);
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
					$itemssorted = query_full_array("SELECT `type`,`aux`, `data` FROM " . prefix('plugin_storage') . " WHERE `type` = 'rsshitcounter' ORDER BY CONVERT(data,UNSIGNED) DESC LIMIT " . $limit);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['data'];
					}
					break;
				default:
					$itemssorted = query_full_array($dbquery . " ORDER BY hitcounter DESC LIMIT " . $limit);
					if (empty($itemssorted)) {
						$maxvalue = 0;
					} else {
						$maxvalue = $itemssorted[0]['hitcounter'];
					}
					break;
			}
			$headline = $typename . " - " . gettext("most viewed");
			break;
		case "mostrated":
			$itemssorted = query_full_array($dbquery . " ORDER BY total_votes DESC LIMIT " . $limit);
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['total_votes'];
			}
			$headline = $typename . " - " . gettext("most rated");
			break;
		case "toprated":
			$itemssorted = query_full_array($dbquery . " ORDER BY (total_value/total_votes) DESC, total_value DESC LIMIT $limit");
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				if ($itemssorted[0]['total_votes'] != 0) {
					$maxvalue = ($itemssorted[0]['total_value'] / $itemssorted[0]['total_votes']);
				} else {
					$maxvalue = 0;
				}
			}
			$headline = $typename . " - " . gettext("top rated");
			break;
		case "mostcommented":
			switch ($type) {
				case "albums":
					$itemssorted = query_full_array("SELECT comments.ownerid, count(*) as commentcount, albums.* FROM " . prefix('comments') . " AS comments, " . prefix('albums') . " AS albums WHERE albums.id=comments.ownerid AND type = 'albums' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $limit);
					break;
				case "images":
					$itemssorted = query_full_array("SELECT comments.ownerid, count(*) as commentcount, images.* FROM " . prefix('comments') . " AS comments, " . prefix('images') . " AS images WHERE images.id=comments.ownerid AND type = 'images' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $limit);
					break;
				case "pages":
					$itemssorted = query_full_array("SELECT comments.ownerid, count(*) as commentcount, pages.* FROM " . prefix('comments') . " AS comments, " . prefix('pages') . " AS pages WHERE pages.id=comments.ownerid AND type = 'page' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $limit);
					break;
				case "news":
					$itemssorted = query_full_array("SELECT comments.ownerid, count(*) as commentcount, news.* FROM " . prefix('comments') . " AS comments, " . prefix('news') . " AS news WHERE news.id=comments.ownerid AND type = 'news' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $limit);
					break;
			}
			if (empty($itemssorted)) {
				$maxvalue = 0;
			} else {
				$maxvalue = $itemssorted[0]['commentcount'];
			}
			$headline = $typename . " - " . gettext("most commented");
			break;
		case "mostimages":
			$itemssorted = query_full_array("SELECT images.albumid, count(*) as imagenumber, albums.* FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums WHERE albums.id=images.albumid GROUP BY images.albumid ORDER BY imagenumber DESC LIMIT " . $limit);
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
					$allalbums = query_full_array($dbquery . " ORDER BY id DESC LIMIT " . $limit);
					$albums = array();
					foreach ($allalbums as $album) {
						$albumobj = newAlbum($album['folder']);
						if ($albumobj->loaded) {
							$albumentry = array("id"					 => $albumobj->getID(), "title"				 => $albumobj->getTitle(), "folder"			 => $albumobj->name, "imagenumber"	 => $albumobj->getNumImages(), "show"				 => $albumobj->getShow());
							array_unshift($albums, $albumentry);
						}
					}
					$maxvalue = 1;
					$itemssorted = sortMultiArray($albums, 'id', true, true); // The items are originally sorted by id;
					$headline = $typename . " - " . gettext("latest");
					break;
				case "images":
					$itemssorted = query_full_array($dbquery . " ORDER BY id DESC LIMIT " . $limit);
					$barsize = 0;
					$maxvalue = 1;
					$headline = $typename . " - " . gettext("latest");
					break;
			}
			break;
		case "latestupdated":
			$albums = getAlbumStatistic($to_number, 'latestupdated', '');
			$maxvalue = 1;
			if (!empty($albums)) {
				foreach ($albums as $key => $album) {
					$albumobj = newAlbum($album['folder']);
					if ($albumobj->loaded)
						$albums[$key]['imagenumber'] = $albumobj->getNumImages();
				}
			}
			$itemssorted = $albums;
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
		if ($sortorder == 'popular' && $type != 'rss' && !extensionEnabled('hitcounter')) {
			$no_statistic_message = "<tr><td colspan='4'><em>" . gettext("Note: The hitcounter plugin is not enabled, therefore any existing values will not get updated.") . "</em></td><td></td><td></td><td></td></tr>";
		}
	}
	if ($from_number <= 1) {
		$count = 1;
	} else {
		$count = $from_number;
	}
	$countlines = 0;
	echo "<table class='bordered'>";
	echo "<tr><th colspan='4'><strong>" . $headline . "</strong>";

	if (isset($_GET['stats'])) {
		echo "<a href='gallery_statistics.php'> | " . gettext("Back to the top 10 lists") . "</a>";
	} else {
		if (empty($no_statistic_message)) {
			echo "<a href='gallery_statistics.php?stats=" . $sortorder . "&amp;type=" . $type . "'> | " . gettext("View more") . "</a>";
		}
		echo "<a href='#top'> | " . gettext("top") . "</a>";
	}
	echo "</th></tr>";
	echo $no_statistic_message;
	foreach ($itemssorted as $item) {
		if (array_key_exists("filename", $item)) {
			$name = $item['filename'];
		} else if (array_key_exists("folder", $item)) {
			$name = $item['folder'];
		} else if ($type === "pages" OR $type === "news") {
			$name = $item['titlelink'];
		} else if ($type === "newscategories") {
			$name = $item['title'];
		} else if ($type === "tags") {
			$name = "";
		}
		switch ($sortorder) {
			case "popular":
				switch ($type) {
					case 'rss':
						$barsize = round($item['data'] / $maxvalue * $bargraphmaxsize);
						$value = $item['data'];
						break;
					default:
						$barsize = round($item['hitcounter'] / $maxvalue * $bargraphmaxsize);
						$value = $item['hitcounter'];
						break;
				}
				break;
			case "mostrated":
				if ($item['total_votes'] != 0) {
					$barsize = round($item['total_votes'] / $maxvalue * $bargraphmaxsize);
				} else {
					$barsize = 0;
				}
				$value = $item['total_votes'];
				break;
			case "toprated":
				if ($item['total_votes'] != 0) {
					$barsize = round(($item['total_value'] / $item['total_votes']) / $maxvalue * $bargraphmaxsize);
					$value = round($item['total_value'] / $item['total_votes']);
				} else {
					$barsize = 0;
					$value = 0;
				}
				break;
			case "mostcommented":
				if ($maxvalue != 0) {
					$barsize = round($item['commentcount'] / $maxvalue * $bargraphmaxsize);
				} else {
					$barsize = 0;
				}
				$value = $item['commentcount'];
				break;
			case "mostimages":
				$barsize = round($item['imagenumber'] / $maxvalue * $bargraphmaxsize);
				$value = $item['imagenumber'];
				break;
			case "latest":
				switch ($type) {
					case "albums":
						$barsize = 0; //round($item['imagenumber'] / $maxvalue * $bargraphmaxsize);
						$value = sprintf(gettext("%s images"), $item['imagenumber']);
						break;
					case "images":
						$barsize = 0;
						$value = "";
						break;
				}
				break;
			case "latestupdated":
				$barsize = 0; //round($item['imagenumber'] / $maxvalue * $bargraphmaxsize);
				$value = sprintf(gettext("%s images"), $item['imagenumber']);
				break;
			case "mostused":
				switch ($type) {
					case "tags":
						if ($maxvalue != 0) {
							$barsize = round($item['tagcount'] / $maxvalue * $bargraphmaxsize);
						} else {
							$barsize = 0;
						}
						$value = $item['tagcount'];
						break;
					case "newscategories":
						if ($maxvalue != 0) {
							$barsize = round($item['catcount'] / $maxvalue * $bargraphmaxsize);
						} else {
							$barsize = 0;
						}
						$value = $item['catcount'];
						break;
				}
				break;
		}
		// counter to have a gray background of every second line
		if ($countlines === 1) {
			$style = " style='background-color: #f4f4f4'"; // a little ugly but the already attached class for the table is so easiest overriden...
			$countlines = 0;
		} else {
			$style = "";
			$countlines++;
		}
		switch ($type) {
			case "albums":
				$editurl = $webpath . "/admin-edit.php?page=edit&amp;album=" . $name;
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
					$editurl = $webpath . "/admin-edit.php?page=edit&amp;album=" . $getalbumfolder['folder'] . "&amp;image=" . $item['filename'] . "&amp;tab=imageinfo#IT";
					$viewurl = WEBPATH . "/index.php?album=" . $getalbumfolder['folder'] . "&amp;image=" . $name;
					$title = get_language_string($item['title']);
				}
				break;
			case "pages":
				$editurl = $webpath . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;titlelink=" . $name;
				$viewurl = WEBPATH . "/index.php?p=pages&amp;title=" . $name;
				$title = get_language_string($item['title']);
				break;
			case "news":
				$editurl = $webpath . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?news&amp;titlelink=" . $name;
				$viewurl = WEBPATH . "/index.php?p=news&amp;title=" . $name;
				$title = get_language_string($item['title']);
				break;
			case "newscategories":
				$editurl = $webpath . '/' . PLUGIN_FOLDER . "/zenpage/admin-categories.php?edit&amp;id=" . $item['id'];
				$viewurl = WEBPATH . "/index.php?p=news&amp;category=" . $name;
				$title = get_language_string($item['titlelink']);
				break;
			case "tags":
				$editurl = $webpath . "/admin-tags.php";
				$viewurl = WEBPATH . "/index.php?p=search&amp;searchfields=tags&amp;words=" . $item['name'];
				$title = get_language_string($item['name']);
				break;
			case "rss":
				$editurl = '';
				$viewurl = WEBPATH . "/index.php?" . html_encode(strrchr($item['aux'], 'rss'));
				$title = html_encode(strrchr($item['aux'], 'rss'));
				break;
		}
		if (isset($item['show'])) {
			if ($item['show'] != "1") {
				$show = " class='unpublished_item'";
			} else {
				$show = "";
			}
		} else {
			$show = "";
		}
		if ($value != 0 OR $sortorder === "latest") {
			if (empty($name)) {
				$name = "";
			} else {
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
					<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>%"></div>
					<div class="statistic_value"><?php echo $value; ?></div>
				</td>
				<td class="statistic_link" <?php echo $style; ?>>
					<?php
					switch ($type) {
						case 'rss':
							echo "<a href='" . $viewurl . "' title='" . $name . "'>" . gettext("View") . "</a></td>";
							break;
						default:
							echo "<a href='" . $editurl . "' title='" . $name . "'>" . gettext("Edit") . "</a> | <a href='" . $viewurl . "' title='" . $name . "'>" . gettext("View") . "</a></td>";
							break;
					}
					echo "</tr>";
					$count++;
					if ($count === $limit) {
						break;
					}
				}
			} // foreach end
			echo "</table>";
		}

		echo '</head>';
		?>

<body>
	<?php
	printLogoAndLinks();
	?>
	<div id="main">
		<span id="top"></span>
		<?php
		printTabs();



// getting the counts
		$albumcount = $_zp_gallery->getNumAlbums(true);
		$albumscount_unpub = $albumcount - $_zp_gallery->getNumAlbums(true, true);
		$imagecount = $_zp_gallery->getNumImages();
		$imagecount_unpub = $imagecount - $_zp_gallery->getNumImages(true);
		?>
		<div id="content">
			<?php printSubtabs() ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note', 'statistics', ''); ?>
				<h1><?php echo gettext("Gallery Statistics"); ?></h1>
				<p><?php echo gettext("This page shows more detailed statistics of your gallery. For album statistics the bar graph always shows the total number of images in that album. For image statistics always the album the image is in is shown.<br />Un-published items are marked in dark red. Images are marked un-published if their (direct) album is, too."); ?></p>

				<ul class="statistics_general"><li>
						<?php
						if ($imagecount_unpub > 0) {
							printf(gettext('<strong>%1$u</strong> images (%2$u un-published)'), $imagecount, $imagecount_unpub);
						} else {
							printf(gettext('<strong>%u</strong> images'), $imagecount);
						}
						?>
					</li><li>
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
					<li><nobr><?php printf(gettext("Zenphoto scripts size: <strong>%s</strong>"), byteConvert(gallerystats_filesize_r(SERVERPATH . '/' . ZENFOLDER))); ?></nobr></li>

				</ul>

				<?php
				if (!isset($_GET['stats']) AND !isset($_GET['fulllist'])) {
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
					<span id="albums-latest"></a>
						<?php printBarGraph("latest", "albums"); ?>

						<span id="albums-latestupdated"></span>
						<?php printBarGraph("latestupdated", "albums"); ?>

						<span id="albums-mostimages"></span>
						<?php printBarGraph("mostimages", "albums"); ?>

						<span id="albums-popular"></span>
						<?php printBarGraph("popular", "albums"); ?>

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
							<span id="news-popular"></a>
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
								$from_number_display = 1;
							} else {
								$from_number = sanitize_numeric($_GET['from_number']) - 1;
								$from_number_display = sanitize_numeric($_GET['from_number']);
							}
							if (!isset($_GET['to_number'])) {
								$to_number = 50;
								if ($_GET['type'] === "images" AND $to_number > $imagecount) {
									$to_number = $imagecount;
								}
								if ($_GET['type'] === "albums" AND $to_number > $albumcount) {
									$to_number = $albumcount;
								}
								$to_number_display = $to_number;
							} else {
								$to_number = sanitize_numeric($_GET['to_number']);
								$to_number_display = $to_number;
								if ($from_number < $to_number) {
									$to_number_display = $to_number;
									$to_number = $to_number - $from_number;
								}
							}
							?>
							<form name="limit" id="limit" action="gallery_statistics.php">
								<label for="from_number"><?php echo gettext("From "); ?></label>
								<input type ="text" size="10" id="from_number" name="from_number" value="<?php echo $from_number_display; ?>" />
								<label for="to_number"><?php echo gettext("to "); ?></label>
								<input type ="text" size="10" id="to_number" name="to_number" value="<?php echo $to_number_display; ?>" />
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
							echo "<a href='#top'>" . gettext("Back to top") . "</a>";
						} // main if end
						?>
						</div>
						</div><!-- content -->
						<?php printAdminFooter(); ?>
						</div><!-- main -->
						</body>
						<?php echo "</html>"; ?>
