<?php

/**
 * Class for Gallery backend statistics 
 * 
 * @since 1.6.6
 * 
 * @author Malte Müller (acrylian) adapted procedural code probably by Stephen Billard (sbillard), Malte Müller (acrylian) and Subjunk
 * @package admin
 * @subpackage admin-utilites
 */
class adminGalleryStats {

	protected $sortorder = '';
	protected $type = '';
	protected $items = null;
	protected $from_number = 0;
	protected $to_number = 10;
	public $bargraphmaxsize = 90;

	/**
	 * Setup the object for the statistics to get
	 * 
	 * @since 1.6.6
	 * 
	 * @param string $sortorder Sortorder to use: "mostused", "popular" ,"popularimages", "mostrated", "toprated", "mostcommented", "mostimages", "latest", "latestupdated":
	 * @param string $type Name of the item/object type to use. Note that not all sortorders are available for all types
	 * @param string $from_number Start numberof records to get from the database, default 0
	 * @param string $to_number End number to get, default 10
	 */
	function __construct($sortorder = 'mostimages', $type = 'albums', $from_number = 0, $to_number = 10) {
		$this->sortorder = $sortorder;
		$this->type = $type;
		$this->from_number = intval($from_number);
		if ($this->from_number < 0) {
			$this->from_number = 0;
		}
		$this->to_number = intval($to_number);
		if ($this->to_number < 0) {
			$this->to_number = 10;
		}
	}
	
	/**
	 * Processes the get/from numbers as an array from the $_GET values submitted.
	 * 
	 * @since 1.6.6
	 * 
	 * @global obj $_zp_gallery
	 * @return array
	 */
	static function getProcessedFromToNumbers() {
		global $_zp_gallery;
		if (isset($_GET['from_number'])) {
			$from = sanitize_numeric($_GET['from_number']);
			// prevent negative start numbers
			if ($from <= 0) {
				$from_number = 0;
				$from_number_display = 1;
			} else {
				$from_number = sanitize_numeric($_GET['from_number']);
				$from_number_display = sanitize_numeric($_GET['from_number']);
			}
		} else {
			$from_number = 0;
			$from_number_display = 1;
		}
		if (isset($_GET['to_number'])) {
			$to_number = sanitize_numeric($_GET['to_number']);
			if ($to_number <= 0) {
				$to_number = $to_number_display = 50;
			}
			if ($from_number > $to_number) {
				$from_number = 0;
				$from_number_display = 1;
				$to_number = $to_number_display = 50;
			}
			$to_number_display = $to_number;
		} else {
			$to_number = $to_number_display = 50;
		}
		return array(
				'from' => $from_number,
				'from_display' => $from_number_display,
				'to' => $to_number,
				'to_display' => $to_number_display
		);
	}

	/**
	 * Gets the base db query for some requests
	 * 
	 * @since 1.6.6
	 * 
	 * @global obj $_zp_db
	 * @return string
	 */
	function getDBQueryBase() {
		global $_zp_db;
		switch ($this->type) {
			case "albums":
				return "SELECT title, folder, hitcounter, `show`, total_votes, total_value FROM " . $_zp_db->prefix('albums');
			case "images":
				return "SELECT title, filename, albumid, hitcounter, `show`, total_votes, total_value FROM " . $_zp_db->prefix('images');
			case "pages":
				return "SELECT title, titlelink, hitcounter, `show`, total_votes, total_value FROM " . $_zp_db->prefix('pages');
			case "news":
				return "SELECT title, titlelink, hitcounter, `show`, total_votes, total_value FROM " . $_zp_db->prefix('news');
			case "newscategories":
				return "SELECT title, titlelink, hitcounter, `show` FROM " . $_zp_db->prefix('news_categories');
			case "tags":
				return "SELECT id, name FROM " . $_zp_db->prefix('tags');
			case "rss":
				return '';
		}
	}

	/**
	 * Gets the db query LIMIt part generated  from the from and to values 
	 * 
	 * @since 1.6.6
	 * 
	 * @return string
	 */
	function getDBQueryLimit() {
		if ( $this->from_number >= 1 ) {
			return $this->from_number - 1 . "," . $this->to_number;
		} else {
			return $this->from_number . "," . $this->to_number;
		}
	}

	/**
	 * Gets an nestsed array of supported items types as key asn and array with the gettext type title and an array of supported sortorders
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	static function getSupportedTypes() {
		$supported_gallery = array(
				'images' => array(
						'title' => gettext('Images'),
						'sortorders' => array(
								'latest',
								'popular',
								'mostrated',
								'toprated',
								'mostcommented'
						)
				),
				'albums' => array(
						'title' => gettext("Albums"),
						'sortorders' => array(
								'latest',
								'latestupdated',
								'mostimages',
								'popular',
								'popularimages',
								'mostrated',
								'toprated',
								'mostcommented'
						)
				),
				'tags' => array(
						'title' => gettext('Tags'), 'sortorders' => array(
								'latest',
								'mostused'
						)
				),
				'rss' => array(
						'title' => gettext('RSS'),
						'sortorders' => array(
								'popular'
						)
				)
		);
		if (extensionEnabled('zenpage')) {
			$supported_zenpage = array(
					'pages' => array(
							'title' => gettext('Pages'),
							'sortorders' => array(
									'latest',
									'popular',
									'mostcommented',
									'mostrated',
									'toprated'
							)
					),
					'news' => array(
							'title' => gettext('News Articles'),
							'sortorders' => array(
									'latest',
									'popular',
									'mostcommented',
									'mostrated',
									'toprated'
							)
					),
					'newscategories' => array(
							'title' => gettext('News categories'),
							'sortorders' => array(
									'latest',
									'popular',
									'mostused',
							)
					)
			);
			$supported_gallery = array_merge($supported_gallery, $supported_zenpage);
		}
		if (extensionEnabled('downloadlist')) {
			$supported_downloads = array(
					'downloads' => array(
							'title' => gettext('Downloads'),
							'sortorders' => array(
									'mostdownloaded'
							)
					)
			);
			$supported_gallery = array_merge($supported_gallery, $supported_downloads);
		}
		return $supported_gallery;
	}
	
	/**
	 * Prints the jump mark menu for all supported item types and their sortorders
	 * 
	 * @since 1.6.6
	 */
	static function printStatisticsMenu() {
		$supported = static::getSupportedTypes();
		$sortorders = static::getSortorders();
		echo '<ul class="statistic_navlist">';
		foreach ($supported as $itemsname => $data) {
			echo '<li>';
			echo $data['title'];
			if ($data['sortorders']) {
				echo '<ul>';
				$count = 0;
				$sortorder_count = count($data['sortorders']);
				foreach ($data['sortorders'] as $sortorder) {
					$count++;
					$sortorder_title = $sortorder;
					if (array_key_exists($sortorder, $sortorders)) {
						$sortorder_title = $sortorders[$sortorder];
					}
					echo '<li><a href="#' . $itemsname . '-' . $sortorder . '">' . $sortorder_title . '</a>';
					if ($sortorder_count != $count) {
						echo ' | ';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
		}
		echo '</ul>';
	}
	
	/**
	 * Gets the action URL for from/to single stats form
	 * 
	 * @since 1.6.6.
	 * 
	 * @param string $stats The sortorder
	 * @param string $type The item type 
	 * @return string
	 */
	static function getSingleStatSelectionFormActionURL($stats = '', $type = '') {
		$actionurl = FULLWEBPATH . '/' . ZENFOLDER . '/'.UTILITIES_FOLDER . '/gallery_statistics.php';
		if ($stats && $type) {
			if ($type == 'downloads') {
				$actionurl = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php';
			}
		}
		return $actionurl;
	}

	/**
	 * Prints the form to select the from to range for single statistic display
	 * 
	 * @since 1.6.6
	 * 
	 * @param array $fromtonumbers The array from as returned by adminGalleryStats::getProcessedFromToNumbers();
	 * @param string $stats The sortorder to get
	 * @param string $type The item type to get
	 */
	static function printSingleStatSelectionForm($fromtonumbers, $stats, $type) {
		if ($stats && $type) {
			$actionurl = static::getSingleStatSelectionFormActionURL($stats, $type);
			?>
				<form name="limit" id="limit" action="<?php echo $actionurl; ?>">
					<label for="from_number"><?php echo gettext("From "); ?></label>
					<input type ="text" size="10" id="from_number" name="from_number" value="<?php echo $fromtonumbers['from_display']; ?>" />
					<label for="to_number"><?php echo gettext("to "); ?></label>
					<input type ="text" size="10" id="to_number" name="to_number" value="<?php echo $fromtonumbers['to_display']; ?>" />
					<input type="hidden" name="stats"	value="<?php echo html_encode($stats); ?>" />
					<input type="hidden" name="type" value="<?php echo html_encode($type); ?>" />
					<button type="submit"><?php echo gettext("Show"); ?></button>
				</form>
			<?php
		}
	}
	

	/**
	 * Gets an array with the all possible sortorders as key and the gettext names as values
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	static function getSortorders() {
		return array(
				'mostused' => gettext("most used"),
				'popular' => gettext("most viewed"),
				'popularimages' => gettext("most viewed images"),
				'mostrated' => gettext("most rated"),
				'toprated' => gettext("top rated"),
				'mostcommented' => gettext("most commented"),
				'mostimages' => gettext("most images"),
				'latest' => gettext("latest"),
				'latestupdated' => gettext("latest updated"),
				'mostdownloaded' => gettext("Most downloaded")
		);
	}

	/**
	 * Gets the healdine plus appendix
	 * 
	 * @since 1.6.6
	 * 
	 * @return string
	 */
	function getHeadline() {
		$typenames = static::getSupportedTypes();
		$typenames_sortorder = static::getSortorders();
		$headline = '';
		if (array_key_exists($this->type, $typenames)) {
			$headline = $typenames[$this->type]['title'];
		}
		if ($headline && array_key_exists($this->sortorder, $typenames_sortorder)) {
			$headline .= ' - ' . $typenames_sortorder[$this->sortorder];
		}
		return $headline;
	}

	/**
	 * Gets the max value of total items statistics
	 * 
	 * @since 1.6.6
	 *
	 * @return int
	 */
	function getMaxvalue() {
		$itemssorted = $this->getItems();
		switch ($this->sortorder) {
			case "mostused":
				switch ($this->type) {
					case "tags":
						if (empty($itemssorted)) {
							$maxvalue = 0;
						} else {
							$maxvalue = $itemssorted[0]['tagcount'];
						}
						break;
					case"newscategories":
						if (empty($itemssorted)) {
							$maxvalue = 0;
						} else {
							$maxvalue = $itemssorted[0]['catcount'];
						}
						break;
				}
				break;
			case "popular":
				switch ($this->type) {
					case 'rss':
						if (empty($itemssorted)) {
							$maxvalue = 0;
						} else {
							$maxvalue = $itemssorted[0]['data'];
						}
						break;
					default:
						if (empty($itemssorted)) {
							$maxvalue = 0;
						} else {
							$maxvalue = $itemssorted[0]['hitcounter'];
						}
						break;
				}
				break;
			case "popularimages":
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					$maxvalue = $itemssorted[0]['average'];
				}
				break;
			case "mostrated":
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					$maxvalue = $itemssorted[0]['total_votes'];
				}
				break;
			case "toprated":
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					if ($itemssorted[0]['total_votes'] != 0) {
						$maxvalue = ($itemssorted[0]['total_value'] / $itemssorted[0]['total_votes']);
					} else {
						$maxvalue = 0;
					}
				}
				break;
			case "mostcommented":
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					$maxvalue = $itemssorted[0]['commentcount'];
				}
				break;
			case "mostimages":
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					$maxvalue = $itemssorted[0]['imagenumber'];
				}
				break;
			case "latest":
				switch ($this->type) {
					case "albums":
					case "images":
					case "tags":
					case "pages":
					case "news":
					case "newscategories":
						$maxvalue = 1;
						break;
				}
				break;
			case "latestupdated":
				$maxvalue = 1;
				break;
			case 'mostdownloaded':
				if (empty($itemssorted)) {
					$maxvalue = 0;
				} else {
					$maxvalue = $itemssorted[0]['data'];
				}
		}
		return $maxvalue;
	}

	/**
	 * Gets the items as requested
	 * 
	 * @since 1.6.6
	 *
	 * @return array 
	 */
	function getItems() {
		if (!is_null($this->items)) {
			return $this->items;
		}
		switch ($this->sortorder) {
			case "mostused":
				return $this->items = $this->getMostUsedItems();
			case "popular":
				return $this->items = $this->getPopularItems();
			case "popularimages":
				return $this->items = $this->getPopularImages();
			case "mostrated":
				return $this->items = $this->getMostRatedItems();
			case "toprated":
				return $this->items = $this->getTopRatedItems();
			case "mostcommented":
				return $this->items = $this->getMostCommentedItems();
			case "mostimages":
				return $this->items = $this->getAlbumsWithMostImages();
			case "latest":
				return $this->items = $this->getLatestItems();
			case "latestupdated":
				return $this->items = $this->getLatestUpdatedItems();
			case 'mostdownloaded':
				return $this->items = $this->getMostDownloadedFiles();
		}
		return $this->items = array();
	}

	/**
	 * Gets the most used items
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getMostUsedItems() {
		global $_zp_db;
		switch ($this->type) {
			case "tags":
				return $_zp_db->queryFullArray("SELECT tagobj.tagid, count(*) as tagcount, tags.name FROM " . $_zp_db->prefix('obj_to_tag') . " AS tagobj, " . $_zp_db->prefix('tags') . " AS tags WHERE tags.id=tagobj.tagid GROUP BY tags.id ORDER BY tagcount DESC LIMIT " . $this->getDBQueryLimit());
			case"newscategories":
				return $_zp_db->queryFullArray("SELECT news2cat.cat_id, count(*) as catcount, cats.titlelink, cats.title FROM " . $_zp_db->prefix('news2cat') . " AS news2cat, " . $_zp_db->prefix('news_categories') . " AS cats WHERE cats.id=news2cat.cat_id GROUP BY news2cat.cat_id ORDER BY catcount DESC LIMIT " . $this->getDBQueryLimit());
		}
		return array();
	}

	/**
	 * Gets the most popular items, those with the most hitcounts
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getPopularItems() {
		global $_zp_db;
		switch ($this->type) {
			case 'rss':
				return $_zp_db->queryFullArray("SELECT `type`,`aux`, `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = 'rsshitcounter' ORDER BY CONVERT(data,UNSIGNED) DESC LIMIT " . $this->getDBQueryLimit());
			default:
				return $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY hitcounter DESC LIMIT " . $this->getDBQueryLimit());
		}
		return array();
	}

	/**
	 * Gets the most popular images (most hitcounts)
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getPopularImages() {
		global $_zp_db;
		$dbquery = "SELECT a.title, a.folder, a.hitcounter, a.show, ROUND(AVG( i.hitcounter ), 0) AS average FROM " . $_zp_db->prefix('albums') . " a INNER JOIN " . $_zp_db->prefix('images') . " i ON i.albumid = a.id ";
		return $_zp_db->queryFullArray($dbquery . " GROUP BY i.albumid ORDER BY average DESC LIMIT " . $this->getDBQueryLimit());
	}

	/**
	 * Gets the most rated items
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getMostRatedItems() {
		global $_zp_db;
		return $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY total_votes DESC LIMIT " . $this->getDBQueryLimit());
	}

	/**
	 * Gets the top rated items
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getTopRatedItems() {
		global $_zp_db;
		return $_zp_db->queryFullArray($this->getDBQueryBase() . ' ORDER BY (total_value/total_votes) DESC, total_value DESC LIMIT ' . $this->getDBQueryLimit());
	}

	/**
	 * Gets the most commented items
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getMostCommentedItems() {
		global $_zp_db;
		switch ($this->type) {
			case "albums":
				return $_zp_db->queryFullArray("SELECT comments.ownerid, count(*) as commentcount, albums.title, albums.folder, albums.show FROM " . $_zp_db->prefix('comments') . " AS comments, " . $_zp_db->prefix('albums') . " AS albums WHERE albums.id=comments.ownerid AND type = 'albums' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $this->getDBQueryLimit());
			case "images":
				return $_zp_db->queryFullArray("SELECT comments.ownerid, count(*) as commentcount, images.albumid, images.title, images.filename, images.show FROM " . $_zp_db->prefix('comments') . " AS comments, " . $_zp_db->prefix('images') . " AS images WHERE images.id=comments.ownerid AND type = 'images' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $this->getDBQueryLimit());
			case "pages":
				return $_zp_db->queryFullArray("SELECT comments.ownerid, count(*) as commentcount, pages.title, pages.titlelink, pages.hitcounter, pages.show FROM " . $_zp_db->prefix('comments') . " AS comments, " . $_zp_db->prefix('pages') . " AS pages WHERE pages.id=comments.ownerid AND type = 'page' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $this->getDBQueryLimit());
			case "news":
				return $_zp_db->queryFullArray("SELECT comments.ownerid, count(*) as commentcount, news.title, news.titlelink, news.hitcounter, news.show FROM " . $_zp_db->prefix('comments') . " AS comments, " . $_zp_db->prefix('news') . " AS news WHERE news.id=comments.ownerid AND type = 'news' GROUP BY comments.ownerid ORDER BY commentcount DESC LIMIT " . $this->getDBQueryLimit());
		}
		return array();
	}

	/**
	 * Gets the albums with the most images without including subalbum images
	 * 
	 * @since 1.6.6
	 * 
	 * @global type $_zp_db
	 * @return array
	 */
	private function getAlbumsWithMostImages() {
		global $_zp_db;
		return $_zp_db->queryFullArray("SELECT images.albumid, count(*) as imagenumber, albums.title, albums.folder, albums.show FROM " . $_zp_db->prefix('images') . " AS images, " . $_zp_db->prefix('albums') . " AS albums WHERE albums.id=images.albumid GROUP BY images.albumid ORDER BY imagenumber DESC LIMIT " . $this->getDBQueryLimit());
	}

	/**
	 * Gets the latest items (ID order, e.g. filesystem discovery order for images and albums) 
	 * 
	 * @since 1.6.6
	 * 
	 * @global type $_zp_db
	 * @return array
	 */
	private function getLatestItems() {
		global $_zp_db;
		switch ($this->type) {
			case "albums":
				$allalbums = $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY id DESC LIMIT " . $this->getDBQueryLimit());
				$albums = array();
				foreach ($allalbums as $album) {
					$albumobj = Albumbase::newAlbum($album['folder']);
					if ($albumobj->loaded) {
						$albumentry = array("id" => $albumobj->getID(), "title" => $albumobj->getTitle(), "folder" => $albumobj->name, "imagenumber" => $albumobj->getNumImages(), "show" => $albumobj->isPublic());
						array_unshift($albums, $albumentry);
					}
				}
				return sortMultiArray($albums, 'id', true, true); // The items are originally sorted by id;
			case "images":
			case "tags":
				return $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY id DESC LIMIT " . $this->getDBQueryLimit());
			case "pages":
			case "news":
				return $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY date DESC LIMIT " . $this->getDBQueryLimit());
			case "newscategories":
				return $_zp_db->queryFullArray($this->getDBQueryBase() . " ORDER BY id DESC LIMIT " . $this->getDBQueryLimit());
		}
		return array();
	}

	/**
	 * Gets the latest updated albums
	 * 
	 * @since 1.6.6
	 *
	 * @global type $_zp_db
	 * @return array
	 */
	private function getLatestUpdatedItems() {
		$albums = getAlbumStatistic($this->to_number, 'latestupdated', '');
		if (!empty($albums)) {
			$stats_albums = array();
			foreach ($albums as $key => $albumobj) {
				if ($albumobj->loaded) {
					$stats_albums[$key]['title'] = $albumobj->getTitle();
					$stats_albums[$key]['folder'] = $albumobj->name;
					$stats_albums[$key]['imagenumber'] = $albumobj->getNumImages();
				}
			}
		}
		return $stats_albums;
	}
	
	/**
	 * Gets the most downloaded files as stored by the download list plugin
	 * 
	 * @since 1.6.6
	 *  
	 * @global obj $_zp_db
	 * @return type
	 */
	private function getMostDownloadedFiles() {
		global $_zp_db;
		$items = $_zp_db->queryFullArray("SELECT `aux`,`data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = 'downloadList' AND `data` != 0 ORDER BY `data` DESC");
		return sortMultiArray($items, 'data', true, true, false, false);
	}
	
	/**
	 * Gets the message if there are no statistics available
	 * 
	 * @since 1.6.6
	 *
	 * @return string
	 */
	function getNoStatisticsMessage() {
		$no_statistic_message = '';
		if ($this->getMaxvalue() == 0 || empty($this->getItems())) {
			$no_hitcount_enabled_msg = '';
			if ($this->sortorder == 'popular' && $this->type != 'rss' && !extensionEnabled('hitcounter')) {
				$no_hitcount_enabled_msg = gettext("(The hitcounter plugin is not enabled.)");
			}
			if ($this->sortorder == 'mostdownloaded' && $this->type != 'downloads' && !extensionEnabled('downloadlist')) {
				$no_hitcount_enabled_msg = gettext("(The downloadlist plugin is not enabled.)");
			}
			$no_statistic_message = '<tr><td colspan="5"><em>' . gettext("No statistic available.") . $no_hitcount_enabled_msg . '</em></td></tr>';
		} else {
			$no_statistic_message = "";
			if (($this->sortorder == 'popular' || $this->sortorder == 'popularimages') && $this->type != 'rss' && !extensionEnabled('hitcounter')) {
				$no_statistic_message = "<tr><td colspan='5'><em>" . gettext("Note: The hitcounter plugin is not enabled, therefore any existing values will not get updated.") . "</em></td></tr>";
			}
		}
		return $no_statistic_message;
	}


	/**
	 *  Gets the bar size for an item value
	 * 
	 * @since 1.6.6
	 *
	 * @param array $item
	 * @return int
	 */
	function getItemBarSize($item) {
		if ($this->getMaxvalue() == 0) {
			return 0;
		}
		switch ($this->sortorder) {
			case "popular":
				switch ($this->type) {
					case 'rss':
						return round($item['data'] / $this->getMaxvalue() * $this->bargraphmaxsize);
					default:
						return round($item['hitcounter'] / $this->getMaxvalue() * $this->bargraphmaxsize);
				}
				break;
			case 'popularimages':
				return round($item['average'] / $this->getMaxvalue() * $this->bargraphmaxsize) - 10;
			case "mostrated":
				if ($item['total_votes'] != 0) {
					return round($item['total_votes'] / $this->getMaxvalue() * $this->bargraphmaxsize);
				}
				break;
			case "toprated":
				if ($item['total_votes'] != 0) {
					return round(($item['total_value'] / $item['total_votes']) / $this->getMaxvalue() * $this->bargraphmaxsize);
				}
				break;
			case "mostcommented":
				return round($item['commentcount'] / $this->getMaxvalue() * $this->bargraphmaxsize);
			case "mostimages":
				return round($item['imagenumber'] / $this->getMaxvalue() * $this->bargraphmaxsize);
			case "latest":
				return 0;
			case "latestupdated":
				return 0;
			case "mostused":
				switch ($this->type) {
					case "tags":
						return round($item['tagcount'] / $this->getMaxvalue() * $this->bargraphmaxsize);
					case "newscategories":
						return round($item['catcount'] / $this->getMaxvalue() * $this->bargraphmaxsize);
				}
				break;
			case 'mostdownloaded':
				return round($item['data'] / $this->getMaxvalue() * $this->bargraphmaxsize);
		}
		return 0;
	}

	/**
	 * Gets the value of an item
	 * 
	 * @since 1.6.6
	 * 
	 * @param array $item
	 * @return string|int
	 */
	function getItemValue($item) {
		switch ($this->sortorder) {
			case "popular":
				switch ($this->type) {
					case 'rss':
						return $item['data'];
					default:
						return $item['hitcounter'];
				}
			case 'popularimages':
				return $item['average'] . " views / image";
			case "mostrated":
				return $item['total_votes'];
			case "toprated":
				if ($item['total_votes'] != 0) {
					return round($item['total_value'] / $item['total_votes']);
				}
				break;
			case "mostcommented":
				return $item['commentcount'];
			case "mostimages":
				return $item['imagenumber'];
			case "latest":
				switch ($this->type) {
					case "albums":
						return sprintf(gettext("%s images"), $item['imagenumber']);
					case "images":
					case 'pages':
					case 'news':
					case 'newscategories':
					case 'tags':
					default:
						return "";
				}
			case "latestupdated":
				return sprintf(gettext("%s images"), $item['imagenumber']);
			case "mostused":
				switch ($this->type) {
					case "tags":
						return $item['tagcount'];
					case "newscategories":
						return $item['catcount'];
				}
				break;
			case 'mostdownloaded':
				return $item['data'];
		}
		return 0;
	}

	/**
	 * Gets the item name (folder, filename etc)
	 * 
	 * @since 1.6.6
	 *
	 * @param array $item Array of the item
	 * @return string
	 */
	function getItemName($item) {
		$name = '';
		if (array_key_exists("filename", $item)) {
			$name = $item['filename'];
		} else if (array_key_exists("folder", $item)) {
			$name = $item['folder'];
		} else if ($this->type === "pages" || $this->type === "news") {
			$name = $item['titlelink'];
		} else if ($this->type === "newscategories") {
			// MERGE: this isn't get_language_string upstream; is it needed?
			$name = get_language_string($item['titlelink']);
		} else if ($this->type === "tags") {
			$name = "";
		} else if($this->type === 'downloads' || $this->type === 'rss') {
			$name = $item['aux'];
		}
		return $name;
	}

	/**
	 * Gets an array with the thumb, editurl, viewurl and title as available
	 * 
	 * @since 1.6.6
	 * 
	 * @global obj $_zp_db
	 * @param array $item Item array
	 * @return array 
	 */
	function getEntryData($item) {
		global $_zp_db;
		$name = $this->getItemName($item);
		$data = array(
				'thumb' => '',
				'editurl' => '',
				'viewurl' => '',
				'title' => '',
				'name' => ''
		);
		switch ($this->type) {
			case "albums":
				$albumobject = Albumbase::newAlbum($item['folder']);
				$albumthumb = $albumobject->getAlbumThumbImage();
				$data['thumb'] = getAdminThumbHTML($albumthumb, 'small');
				$data['editurl'] = WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . $name;
				$data['viewurl'] = WEBPATH . "/index.php?album=" . $name;
				$data['title'] = get_language_string($item['title']);
				break;
			case "images":
				if ($item['albumid']) {
					$getalbumfolder = $_zp_db->querySingleRow("SELECT title, folder, `show` from " . $_zp_db->prefix("albums") . " WHERE id = " . $item['albumid']);
					$albumobject = Albumbase::newAlbum($getalbumfolder['folder']);
					$imageobject = Image::newImage($albumobject, $name);
					if ($this->sortorder === "latest") {
						$value = "<span";
						if ($getalbumfolder['show'] != "1") {
							$value = $value . " class='unpublished_item'";
						}
						$value = $value . ">" . get_language_string($getalbumfolder['title']) . "</span> (" . $getalbumfolder['folder'] . ")";
					}
					//$editurl = WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . $getalbumfolder['folder'] . "&amp;image=" . $item['filename'] . "&amp;tab=imageinfo#IT";
					$data['editurl'] = WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . $getalbumfolder['folder'] . "&amp;singleimage=" . $item['filename'] . "&amp;tab=imageinfo&amp;pagenumber=1";
					$data['viewurl'] = WEBPATH . "/index.php?album=" . $getalbumfolder['folder'] . "&amp;image=" . $name;
					$data['title'] = get_language_string($item['title']);
					$data['thumb'] = '<a href="' . $imageobject->getFullImageURL() . '" class="colorbox" title="' . gettext('Preview') . $data['title'] . '(' . $name . ')">' . getAdminThumbHTML($imageobject, 'small') . '</a>';
				}
				break;
			case "rss":
				$data['title'] = $item['aux'];
				break;
			case "pages":
				$data['editurl'] = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?page&amp;titlelink=" . $name;
				$data['viewurl'] = WEBPATH . "/index.php?p=pages&amp;title=" . $name;
				$data['title'] = get_language_string($item['title']);
				break;
			case "news":
				$data['editurl'] = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newsarticle&amp;titlelink=" . $name;
				$data['viewurl'] = WEBPATH . "/index.php?p=news&amp;title=" . $name;
				$data['title'] = get_language_string($item['title']);
				break;
			case "newscategories":
				$data['editurl'] = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/admin-edit.php?newscategory&amp;titlelink=" . $name;
				$data['viewurl'] = WEBPATH . "/index.php?p=news&amp;category=" . $name;
				$data['title'] = get_language_string($item['title']);
				break;
			case "tags":
				$data['editurl'] = WEBPATH . '/' . ZENFOLDER . "/admin-tags.php";
				$data['viewurl'] = WEBPATH . "/index.php?p=search&amp;searchfields=tags&amp;s=" . $item['name'];
				$data['title'] = $item['name']; // tag names are unique!
				break;
			case 'downloads':
				$data['editurl'] = '';
				$data['viewurl'] = '';
				if (stripos($item['aux'], SERVERPATH) === false) {
					$data['title'] = $item['aux']; 
				} else {
					$data['title'] = str_replace(SERVERPATH, '', $item['aux']); 
				}
				break;
		}
		if (empty($name) || $name == $data['title']) {
			$data['name'] = "";
		} else {
			$data['name'] = "(" . $name . ")";
		}
		
		return $data;
	}
	
	/**
	 * Returns an array with the view more url and title if applicable
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	function getViewMoreData() {
		$data = array(
				'viewmoreurl' => '',
				'viewmoreurl_title' => ''
		);
		if (isset($_GET['stats'])) {
			$data['viewmoreurl'] = FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/gallery_statistics.php';
			$data['viewmoreurl_title'] = gettext("Back to the top 10 lists") . ' &rarr;';
		} else {
			if (!$this->getNoStatisticsMessage()) {
				$data['viewmoreurl_title'] = gettext("View more") . ' &rarr;';
				if ($this->type == 'downloads') {
					$data['viewmoreurl'] = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php?stats=' . $this->sortorder . '&amp;type=' . $this->type;
				} else {
					$data['viewmoreurl'] = 'gallery_statistics.php?stats=' . $this->sortorder . '&amp;type=' . $this->type;
				}
			}
		}
		return $data;
	}

	/**
	 * Prints an statistics entry for a single item
	 * 
	 * @since 1.6.6
	 *
	 * @param array $item
	 * @param int $count
	 */
	function printItemEntry($item, $count) {
		$barsize = $this->getItemBarSize($item);
		$value = $this->getItemValue($item);
		$itemdata = $this->getEntryData($item);
		if (isset($item['show'])) {
			if ($item['show'] != "1") {
				$show = " class='unpublished_item'";
			} else {
				$show = "";
			}
		} else {
			$show = "";
		}
		if ($this->type == 'downloads' && extensionEnabled('downloadlist') ) {
			if (!downloadList::isExternalDownload($item['aux']) && !file_exists(internalToFilesystem($item['aux'])) && !file_exists(ALBUM_FOLDER_SERVERPATH . stripSuffix($item['aux']))) {
				$show = " class='unpublished_item'";
			}
		}
		if ($value != 0 || $this->sortorder === "latest") {
			?>
			<tr class="statistic_wrapper">
				<td class="statistic_counter">
					<?php echo $count; ?>
				</td>
				<td class="statistic_thumb">
					<?php 
					if ($itemdata['thumb']) { 
						echo $itemdata['thumb']; 
					} 
					?>
				</td>
				<td class="statistic_title">
					<strong<?php echo $show; ?>><?php echo html_encode($itemdata['title']); ?></strong> <?php echo html_encode($itemdata['name']); ?>
				</td>
				<td class="statistic_graphwrap">
					<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>%"></div>
					<div class="statistic_value"><?php echo $value; ?></div>
				</td>
				<td class="statistic_link">
					<div class="icon-row">
						<?php
							if ($itemdata['viewurl']) {
								echo '<a class="button" href="' . $itemdata['viewurl'] . '" title="' . gettext("View") . ' ' . html_encode($itemdata['name']) . '">'.gettext('View') .'</a>';
							}
							if ($itemdata['editurl']) {
								echo '<a class="button" href="' . $itemdata['editurl'] . '" title="' . gettext("Edit") . ' ' . html_encode($itemdata['name']) . '">'.gettext('Edit') .'</a>';
							}
						?>
					</div>
				</td>
			</tr>
			<?php
			$count++;
		}
	}
	
	/**
	 * Prints the statistics table for the items chosen
	 * 
	 * @since 1.6.6
	 */
	function printStatistics() {
		$itemssorted = $this->getItems();
		if ($this->from_number <= 1) {
			$count = 1;
		} else {
			$count = $this->from_number;
		}
		$no_statistic_message = $this->getNoStatisticsMessage();
		echo '<table class="bordered striped" id="' . $this->type . '-' . $this->sortorder . '">';
		echo '	<thead>';
		echo '		<tr>';
		echo '			<th colspan="4">';
		echo '				<strong>' . $this->getHeadline() . '</strong>';
		
		echo '			</th>';
		echo '			<th colspan="1">';
		$viewmore = $this->getViewMoreData();
		if ($viewmore['viewmoreurl']) {
			echo '<a href="' . $viewmore['viewmoreurl'] . '">' . $viewmore['viewmoreurl_title']. '</a>';
		}
		echo '			</th>';
		echo '		</tr>';
		echo '</thead>';
		echo $no_statistic_message;
		foreach ($itemssorted as $item) {
			$this->printItemEntry($item, $count);
			if ($count === $this->to_number) {
				break;
			}
			$count++;
		} // foreach end
		echo '</table>';
	}
	
	/**
	 * Gets the mimetype image statistic
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_gallery
	 * @return array
	 */
	static function getImageTypeStats() {
		global $_zp_gallery, $_zp_db;
		$allimages = array();
		$rows = $_zp_db->query("SELECT `filename` FROM " . $_zp_db->prefix('images'));
		if ($rows) {
			while ($row = $_zp_db->fetchAssoc($rows)) {
				$allimages[] = $row['filename'];
			}
			$_zp_db->freeResult($rows);
			$totalimages = $_zp_gallery->getNumImages(0);
			$mimetypes_types = mimeTypes::getTypesByType();
			$imagetype_count = array();
			$count = 0;
			foreach ($allimages as $imagefile) {
				$match = false;
				$count++;
				$suffix = getSuffix($imagefile);
				foreach ($mimetypes_types as $key => $val) {
					if (in_array($suffix, $val)) {
						$match = true;
						$imagetype_count[$key][] = $imagefile;
						break;
					}
				}
				if (!$match) {
					$imagetype_count[gettext('other')][] = $imagefile;
				}
			}
			unset($allimages);
			unset($mimetypes_types);

			//generate the percentages
			$one_percent = $totalimages / 100;
			$stats_percent = array();
			$stats_count = array();
			foreach ($imagetype_count as $key => $val) {
				$count = count($val);
				$stats_percent[$key] = $count / $one_percent;
				$stats_count[$key] = $count;
			}
			array_multisort($stats_percent, SORT_DESC);
			array_multisort($stats_count, SORT_DESC);
			$stats = array();
			foreach ($stats_percent as $key => $val) {
				$stats[$key] = array(
						'percent' => round($val, 2),
						'count' => $stats_count[$key]
				);
			}
			return $stats;
		}
	}

	/**
	 * Prints the mimetype image statistic
	 * 
	 * @since 1.6.6
	 *
	 */
	static function printImageTypeStats() {
		$stats = self::getImageTypeStats();
		self::printTable($stats, gettext('General image mime types'), '');
	}

	/**
	 * Returns an array with disk space stats of Zenphoto folders with the gettext title ready for sprintf/printf as the key 
	 * and the filesystem value of folders as the value
	 * 
	 * @since 1.6.6
	 *
	 * @return array
	 */
	static function getDiskSpaceStats() {
		return array(
				'albums' => array(
						'title' => gettext("Albums folder size: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(ALBUM_FOLDER_SERVERPATH))
				),
				'backups' => array(
						'title' => gettext("Backups: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(getBackupFolder(SERVERPATH)))
				),
				'cache' => array(
						'title' => gettext("Image cache size: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(SERVERPATH . '/' . CACHEFOLDER))
				),
				'cache_html' => array(
						'title' => gettext("HTML cache size: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(SERVERPATH . '/' . STATIC_CACHE_FOLDER))
				),
				'uploaded' => array(
						'title' => gettext("Uploaded folder size: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(SERVERPATH . '/' . UPLOAD_FOLDER))
				),
				'zp-core' => array(
						'title' => gettext("Zenphoto scripts size: <strong>%s</strong>"),
						'value' => byteConvert(adminGalleryStats::getFilesize(SERVERPATH . '/' . ZENFOLDER))
				)
		);
	}

	/**
	 * Prints the diskspace stats of Zenphoto folders
	 * 
	 * @since 1.6.6
	 *
	 */
	static function printDiskSpaceStats() {
		$stats = self::getDiskSpaceStats();
		?>
		<div class="gallerystats_box">
			<h2><?php echo gettext('Disk space usage'); ?></h2>
			<ul class="statistics_general">
				<?php foreach ($stats as $stat) { ?>
					<li><?php printf($stat['title'], $stat['value']); ?></li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Prints the box with the imagetype perentage stats
	 * 
	 * @since 1.6.6
	 *
	 */
	static function printTable($stats, $title) {
		?>
		<div class="gallerystats_box">
			<h2><?php echo $title; ?></h2>
			<ul class="statistics_general">
				<?php
				foreach ($stats as $key => $val) {
					?>
					<li><?php echo $key . ': <strong>' . $val['percent'] . '% (' . $val['count'] . ')</strong>'; ?></li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}
	
	/**
	 * http://php.net/manual/de/function.filesize.php
	 * 
	 * @since 1.6.6
	 * 
	 * @author Jonas Sweden
	 */
	static function getFilesize($path) {
		if (!file_exists($path)) {
			return 0;
		}
		if (is_file($path)) {
			return filesize($path);
		}
		$ret = 0;
		foreach (safe_glob($path . "/*") as $fn) {
			$ret += adminGalleryStats::getFilesize($fn);
		}
		return $ret;
	}
	
}
