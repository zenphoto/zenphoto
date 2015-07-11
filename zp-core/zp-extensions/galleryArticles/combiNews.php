<?php

class Combi extends CMS {

	/**
	 * Gets news articles and images of a gallery to show them together on the news section
	 *
	 * NOTE: This function does not exclude articles that are password protected via a category
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param string $mode 	"latestimages-thumbnail"
	 * 											"latestimages-thumbnail-customcrop"
	 * 											"latestimages-sizedimage"
	 * 											"latestalbums-thumbnail"
	 * 		 									"latestalbums-thumbnail-customcrop"
	 * 		 									"latestalbums-sizedimage"
	 * 		 									"latestimagesbyalbum-thumbnail"
	 * 		 									"latestimagesbyalbum-thumbnail-customcrop"
	 * 		 									"latestimagesbyalbum-sizedimage"
	 * 		 									"latestupdatedalbums-thumbnail" (for RSS and getLatestNews() used only)
	 * 		 									"latestupdatedalbums-thumbnail-customcrop" (for RSS and getLatestNews() used only)
	 * 		 									"latestupdatedalbums-sizedimage" (for RSS and getLatestNews() used only)
	 * 	NOTE: The "latestupdatedalbums" variants do NOT support pagination as required on the news loop!
	 *
	 * @param string $published "published" for published articles,
	 * 													"unpublished" for un-published articles,
	 * 													"all" for all articles
	 * @param string $sortorder 	id, date or mtime, only for latestimages-... modes
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @param string $direction 	"desc" or "asc"
	 * @return array
	 * @deprecated since version 1.4.6
	 */
	function getOldCombiNews($articles_per_page = '', $mode = '', $published = NULL, $sortorder = NULL, $sticky = true, $sortdirection = 'desc') {
		global $_zp_combiNews_cache, $_zp_gallery;

		if (is_null($published)) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
			}
		}

		if (empty($mode)) {
			$mode = getOption('zenpage_combinews_mode');
		}

		if (isset($_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection])) {
			return $_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection];
		}

		if ($published == "published") {
			$show = " WHERE `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'";
			$imagesshow = " AND images.show = 1 ";
		} else {
			$show = "";
			$imagesshow = "";
		}
		self::getAllAccessibleAlbums($_zp_gallery, $albumlist);
		if (empty($albumlist)) {
			$albumWhere = 'albums.`id` is NULL';
		} else {
			$albumWhere = 'albums.`id` in (' . implode(',', $albumlist) . ')';
		}
		if ($articles_per_page) {
			$offset = self::getOffset($articles_per_page);
		} else {
			$offset = 0;
		}
		if (empty($sortorder)) {
			$combinews_sortorder = getOption("zenpage_combinews_sortorder");
		} else {
			$combinews_sortorder = $sortorder;
		}
		$stickyorder = '';
		if ($sticky) {
			$stickyorder = 'sticky DESC,';
		}
		switch (strtolower($sortdirection)) {
			case false:
			case 'asc':
				$sortdir = 'ASC';
				break;
			case 'desc':
			default:
				$sortdir = 'DESC';
				break;
		}
		$type3 = query("SET @type3:='0'");
		switch ($mode) {
			case "latestimages-thumbnail":
			case "latestimages-thumbnail-customcrop":
			case "latestimages-sizedimage":
			case "latestimages-sizedimage-maxspace":
			case "latestimages-fullimage":
				$albumWhere = ' AND ' . $albumWhere;
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='images'");
				switch ($combinews_sortorder) {
					case 'id':
					case 'date':
						$imagequery = "(SELECT albums.folder, images.filename, images.date, @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
							WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
					case 'publishdate':
						$imagequery = "(SELECT albums.folder, images.filename, IFNULL(images.publishdate,images.date), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
													WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
					case 'mtime':
						$imagequery = "(SELECT albums.folder, images.filename, FROM_UNIXTIME(images.mtime), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
							WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $imagequery . "
																		ORDER BY $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestalbums-thumbnail":
			case "latestalbums-thumbnail-customcrop":
			case "latestalbums-sizedimage":
			case "latestalbums-sizedimage-maxspace":
			case "latestalbums-fullimage":
			default:
				if (empty($show)) {
					$albumWhere = ' WHERE ' . $albumWhere;
				} else {
					$albumWhere = ' AND ' . $albumWhere;
				}
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				switch ($combinews_sortorder) {
					case 'id':
					case 'date':
						$albumquery = "(SELECT albums.folder, albums.title, albums.date, @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
							" . $show . $albumWhere . ")";
						break;
					case 'publishdate':
						$albumquery = "(SELECT albums.folder, albums.title, IFNULL(albums.publishdate,albums.date), @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
													" . $show . $albumWhere . ")";
						break;
					case 'mtime':
					default:
						$albumquery = "(SELECT albums.folder, albums.title, FROM_UNIXTIME(albums.mtime), @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
							" . $show . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $albumquery . "
																		ORDER BY $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestimagesbyalbum-thumbnail":
			case "latestimagesbyalbum-thumbnail-customcrop":
			case "latestimagesbyalbum-sizedimage":
			case "latestimagesbyalbum-sizedimage-maxspace":
			case "latestimagesbyalbum-fullimage":
				$albumWhere = ' AND ' . $albumWhere;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				if (empty($combinews_sortorder) || $combinews_sortorder != "date" || $combinews_sortorder != "mtime" || $combinews_sortorder != "publishdate") {
					$combinews_sortorder = "date";
				}
				$sortorder = "images." . $combinews_sortorder;
				switch ($combinews_sortorder) {
					case "date":
						$imagequery = "(SELECT DISTINCT DATE_FORMAT(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.date,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
														WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
					case "mtime":
						$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.mtime,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
														WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
					case "publishdate":
						$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.publishdate,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
																				WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $imagequery . "
																		ORDER By $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestupdatedalbums-thumbnail":
			case "latestupdatedalbums-thumbnail-customcrop":
			case "latestupdatedalbums-sizedimage":
			case "latestupdatedalbums-sizedimage-maxspace":
			case "latestupdatedalbums-fullimage":
				$latest = $this->getArticles($articles_per_page, NULL, true, 'date', $sortdirection);
				$counter = '';
				foreach ($latest as $news) {
					$article = new Article($news['titlelink']);
					if ($article->checkAccess()) {
						$counter++;
						$latestnews[$counter] = array(
										"albumname"	 => $article->getTitle(),
										"titlelink"	 => $article->getTitlelink(),
										"date"			 => $article->getDateTime(),
										"type"			 => "news",
						);
					}
				}
				$albums = getAlbumStatistic($articles_per_page, "latestupdated", false, 0, $sortdirection);
				$latestalbums = array();
				$counter = "";
				foreach ($albums as $tempalbum) {
					$counter++;
					$tempalbumthumb = $tempalbum->getAlbumThumbImage();
					$timestamp = $tempalbum->get('mtime');
					if ($timestamp == 0) {
						$albumdate = $tempalbum->getDateTime();
					} else {
						$albumdate = strftime('%Y-%m-%d %H:%M:%S', $timestamp);
					}
					$latestalbums[$counter] = array(
									"albumname"	 => $tempalbum->getFileName(),
									"titlelink"	 => $tempalbum->getTitle(),
									"date"			 => $albumdate,
									"type"			 => 'albums',
					);
				}
				//$latestalbums = array_merge($latestalbums, $item);
				$latest = array_merge($latestnews, $latestalbums);
				$result = sortMultiArray($latest, "date", $sortdirection != 'asc');
				if (count($result) > $articles_per_page) {
					$result = array_slice($result, 0, $articles_per_page);
				}
				break;
		}
		$_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection] = $result;
		return $result;
	}

	/**
	 * Gets an array of the album ids of all accessible albums (publich or user dependend)
	 *
	 * @param object $obj from whence to get the albums
	 * @param array $albumlist collects the list
	 */
	protected function getAllAccessibleAlbums($obj, &$albumlist) {
		$locallist = $obj->getAlbums();
		foreach ($locallist as $folder) {
			$album = newAlbum($folder);
			If (!$album->isDynamic() && $album->checkAccess()) {
				$albumlist[] = $album->getID();
				self::getAllAccessibleAlbums($album, $albumlist);
			}
		}
	}

}

global $plugin_is_filter;
enableExtension('galleryArticles', $plugin_is_filter);

$obj = new Combi();
$combi = $obj->getOldCombiNews();
$cat = newCategory('combinews', true);
$cat->setTitle(gettext('combiNews'));
$cat->setDesc(gettext('Auto category for ported combi-news articles.'));
$cat->save();

foreach ($combi as $article) {
	switch ($article['type']) {
		case 'images':
			$obj = newImage(array('folder' => $article['albumname'], 'filename' => $article['titlelink']), false);
			break;
		case 'albums':
			$obj = newAlbum($article['albumname'], false);
			break;
		default:
			$obj = NULL;
			break;
	}
	if ($obj && $obj->exists) {
		$obj->setPublishDate($article['date']);
		galleryArticles::publishArticle($obj, 'combinews');
	}
}
purgeOption('zenpage_combinews');
purgeOption('combinews-customtitle');
purgeOption('combinews-customtitle-imagetitles');
purgeOption("zenpage_combinews_sortorder");
purgeOption('zenpage_combinews_imagesize');
purgeOption('combinews-thumbnail-width');
purgeOption('combinews-thumbnail-height');
purgeOption('combinews-thumbnail-cropwidth');
purgeOption('combinews-thumbnail-cropheight');
purgeOption('combinews-thumbnail-cropx');
purgeOption('combinews-thumbnail-cropy');
purgeOption('zenpage_combinews_mode');
?>