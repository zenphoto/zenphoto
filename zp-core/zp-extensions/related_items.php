<?php
/**
 * Provides functionality to list (or get) objects related to the current object based on a search of the tags
 * the assigned to the current object.
 *
 * @package plugins
 */
$plugin_description = gettext('Provides functionality to get the related items to an item based on a tag search.');
$plugin_author = "Malte Müller (acrylian)";

function getRelatedItems($type = 'news', $album = NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news, $_zp_gallery_page;
	$tags = getTags();
	if (!empty($tags)) { // if there are tags at all
		$searchstring = '';
		$count = '';
		foreach ($tags as $tag) {
			$count++;
			if ($count == 1) {
				$bool = '';
			} else {
				$bool = '|'; // connect tags by OR to get a wide range
			}
			$searchstring .= $bool . $tag;
		}
		$paramstr = urlencode('words') . '=' . $searchstring . '&searchfields=tags';
		if (!is_null($album)) {
			$paramstr = '&albumname=' . urlencode($album);
		}
		$search = new SearchEngine();
		switch ($type) {
			case 'albums':
				$paramstr .= '&inalbums=1';
				break;
			case 'images':
				$paramstr .= '&inimages=1';
				break;
			case 'news':
				$paramstr .= '&innews=1';
				break;
			case 'pages':
				$paramstr .= '&inpages=1';
				break;
			case 'all':
				$paramstr .= '&inalbums=1&inimages=1&innews=1&inpages=1';
				break;
		}
		$search->setSearchParams($paramstr);
		// get the results
		switch ($type) {
			case 'albums':
				$albumresult = $search->getAlbums(0, "date", "desc");
				$result = createRelatedItemsResultArray($albumresult, $type);
				break;
			case 'images':
				$imageresult = $search->getImages(0, 0, 'date', 'desc');
				$result = createRelatedItemsResultArray($imageresult, $type);
				break;
			case 'news':
				$newsresult = $search->getArticles(0, NULL, true, "date", "desc");
				$result = createRelatedItemsResultArray($newsresult, $type);
				break;
			case 'pages':
				$pageresult = $search->getPages();
				$result = createRelatedItemsResultArray($pageresult, $type);
				break;
			case 'all':
				$albumresult = $search->getAlbums(0, "date", "desc");
				$imageresult = $search->getImages(0, 0, 'date', 'desc');
				$newsresult = $search->getArticles(0, NULL, true, "date", "desc");
				$pageresult = $search->getPages();
				$result1 = createRelatedItemsResultArray($albumresult, 'albums');
				$result2 = createRelatedItemsResultArray($imageresult, 'images');
				$result3 = createRelatedItemsResultArray($newsresult, 'news');
				$result4 = createRelatedItemsResultArray($pageresult, 'pages');
				$result = array_merge($result1, $result2, $result3, $result4);
				$result = sortMultiArray($result, 'weight', true, true, false, false); // sort by search result weight
				break;
		}
		return $result;
	}
	return array();
}

/**
 * Helper function for getRelatedItems() only.
 * Returns an array with array for each item with name, album (images only), type and weight (search weight value)
 * Excludes the current item itself.
 *
 * @param array $result array with search results
 * @param string $type "albums", "images", "news", "pages"
 */
function createRelatedItemsResultArray($result, $type) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news, $_zp_gallery_page;
	switch ($_zp_gallery_page) {
		case 'album.php':
			$current = $_zp_current_album;
			break;
		case 'image.php':
			$current = $_zp_current_image;
			break;
		case 'news.php':
			$current = $_zp_current_zenpage_news;
			break;
		case 'pages.php':
			$current = $_zp_current_zenpage_page;
			break;
	}
	$results = array();
	foreach ($result as $item) {
		switch ($type) {
			case 'albums':
				if (get_class($current) != 'Album' || $current->name != $item) {
					array_push($results, array('name'	 => $item, 'album'	 => '', 'type'	 => $type, 'weight' => '13')); // doesn't have weight so we just add one for sorting later
				}
				break;
			case 'images':
				if (get_class($current) != 'Image' || ($current->filename != $item['filename'] && $current->getAlbum()->name != $item['folder'])) {
					array_push($results, array('name'	 => $item['filename'], 'album'	 => $item['folder'], 'type'	 => $type, 'weight' => $item['weight']));
				}
				break;
			case 'news':
				if (get_class($current) != 'ZenpageNews' || $current->getTitlelink() != $item['titlelink']) {

					if (!isset($item['weight']))
						$item['weight'] = 13; //	there are circumstances where weights are not generated.

					array_push($results, array('name'	 => $item['titlelink'], 'album'	 => '', 'type'	 => $type, 'weight' => $item['weight']));
				}
				break;
			case 'pages':
				if (get_class($current) != 'ZenpagePage' || $current->getTitlelink() != $item) {
					array_push($results, array('name'	 => $item, 'album'	 => '', 'type'	 => $type, 'weight' => '13')); // doesn't have weight so we just add one for sorting later
				}
				break;
		}
	}
	return $results;
}

/**
 * Prints the x related articles based on a tag search
 *
 * @param int $number Number of items to get
 * @param string $type 'albums', 'images','news','pages', "all" for all combined.
 * @param string $specific If $type = 'albums' or 'images' name of album
 * @param bool $excerpt If a text excerpt (gallery items: description; Zenpage items: content) should be shown. NULL for none or number of length
 * @param bool $thumb For $type = 'albums' or 'images' if a thumb should be shown (default size as set on the options)
 */
function printRelatedItems($number = 5, $type = 'news', $specific = NULL, $excerpt = NULL, $thumb = false, $date = false) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	$label = array('albums' => gettext('Albums'), 'images' => gettext('Images'), 'news'	 => gettext('News'), 'pages'	 => gettext('Pages'));
	$result = getRelatedItems($type, $specific);
	$resultcount = count($result);
	if ($resultcount != 0) {
		?>
		<h3 class="relateditems">
			<?php printf(gettext('Related %s'), $type); ?>
		</h3>
		<ul id="relateditems">
			<?php
			$count = 0;
			foreach ($result as $item) {
				$count++;
				?>
				<li class="<?php echo $item['type']; ?>">
					<?php
					$category = '';
					switch ($item['type']) {
						case 'albums':
							$obj = newAlbum($item['name']);
							$url = $obj->getAlbumLink();
							$text = $obj->getDesc();
							$category = gettext('Album');
							break;
						case 'images':
							$alb = newAlbum($item['album']);
							$obj = newImage($alb, $item['name']);
							$url = $obj->getImageLink();
							$text = $obj->getDesc();
							$category = gettext('Image');
							break;
						case 'news':
							$obj = new ZenpageNews($item['name']);
							$url = getNewsURL($obj->getTitlelink());
							$text = $obj->getContent();
							$category = gettext('News');
							break;
						case 'pages':
							$obj = new ZenpagePage($item['name']);
							$url = getPageLinkURL($obj->getTitlelink());
							$text = $obj->getContent();
							$category = gettext('Page');
							break;
					}
					?>
					<?php
					if ($thumb) {
						$thumburl = false;
						switch ($item['type']) {
							case 'albums':
								$thumburl = $obj->getAlbumThumb();
								break;
							case 'images':
								$thumburl = $obj->getThumb();
								break;
						}
						if ($thumburl) {
							?>
							<a href="<?php echo html_encode(pathurlencode($url)); ?>" title="<?php echo html_encode($obj->getTitle()); ?>" class="relateditems_thumb">
								<img src="<?php echo html_encode(pathurlencode($thumburl)); ?>" alt="<?php echo html_encode($obj->getTitle()); ?>" />
							</a>
							<?php
						}
					}
					?>
					<h4><a href="<?php echo html_encode(pathurlencode($url)); ?>" title="<?php echo html_encode($obj->getTitle()); ?>"><?php echo html_encode($obj->getTitle()); ?></a>
						<?php
						if ($date) {
							switch ($item['type']) {
								case 'albums':
								case 'images':
									$d = $obj->getDateTime();
									break;
								case 'news':
								case 'pages':
									$d = $obj->getDateTime();
									break;
							}
							?>
							<span class="relateditems_date">
								<?php echo zpFormattedDate(DATE_FORMAT, strtotime($d)); ?>
							</span>
							<?php
						}
						?>
						<?php if ($type == 'all') { ?> (<small><?php echo $category; ?></small>)<?php } ?>

					</h4>
					<?php
					if ($excerpt) {
						echo shortenContent($text, $excerpt, '...', true);
					}
					?>
				</li>
				<?php
				if ($count == $number) {
					break;
				}
			} // foreach
			if ($count) {
				?>
			</ul>
			<?php
		}
	}
}
?>