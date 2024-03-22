<?php

/**
 * This is the class declaration
 * @package zpcore\plugins\favoriteshandler
 */
class favorites extends AlbumBase {

	public $imageSortDirection;
	public $albumSortDirection;
	public $imageSortType;
	public $albumSortType;
	public $list = array('');
	public $owner;
	public $instance = '';

	function __construct($user) {
		global $_zp_db;
		$this->table = 'albums';
		$this->name = $user;
		$this->owner = $user;
		$this->setTitle(get_language_string(getOption('favorites_title')));
		$this->setDesc(get_language_string(getOption('favorites_desc')));
		$this->imageSortDirection = getOption('favorites_image_sort_direction');
		$this->albumSortDirection = getOption('favorites_album_sort_direction');
		$this->imageSortType = getOption('favorites_image_sort_type');
		$this->albumSortType = getOption('favorites_album_sort_type');
		$regexboundaries = $_zp_db->getRegexWordBoundaryChars();
		$list = $_zp_db->queryFullArray('SELECT `aux` FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux` REGEXP ' . $_zp_db->quote($regexboundaries['open'] . $user . $regexboundaries['close']));
		foreach ($list as $aux) {
			$instance = getSerializedArray($aux['aux']);
			if (isset($instance[1])) {
				$this->list[$instance[1]] = $instance[1];
			}
		}
	}

	protected function getInstance() {
		if ($this->instance) {
			return serialize(array($this->owner, $this->instance));
		} else {
			return $this->owner;
		}
	}

	function getList() {
		return $this->list;
	}

	function getOwner($fullname = false) {
		$owner = $this->owner;
		if ($fullname) {
			return Administrator::getNameByUser($owner);
		}
		return $owner;
	}

	function addImage($img) {
		global $_zp_db;
		$folder = $img->imagefolder;
		$filename = $img->filename;
		$sql = 'INSERT INTO ' . $_zp_db->prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("favorites",' . $_zp_db->quote($this->getInstance()) . ',' . $_zp_db->quote(serialize(array('type' => 'images', 'id' => $folder . '/' . $filename))) . ')';
		$_zp_db->query($sql);
		zp_apply_filter('favoritesHandler_action', 'add', $img, $this->name);
	}

	function removeImage($img) {
		global $_zp_db;
		$folder = $img->imagefolder;
		$filename = $img->filename;
		$sql = 'DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . $_zp_db->quote($this->getInstance()) . ' AND `data`=' . $_zp_db->quote(serialize(array('type' => 'images', 'id' => $folder . '/' . $filename)));
		$_zp_db->query($sql);
		zp_apply_filter('favoritesHandler_action', 'remove', $img, $this->name);
	}

	function addAlbum($alb) {
		global $_zp_db;
		$folder = $alb->name;
		$sql = 'INSERT INTO ' . $_zp_db->prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("favorites",' . $_zp_db->quote($this->getInstance()) . ',' . $_zp_db->quote(serialize(array('type' => 'albums', 'id' => $folder))) . ')';
		$_zp_db->query($sql);
		zp_apply_filter('favoritesHandler_action', 'add', $alb, $this->name);
	}

	function removeAlbum($alb) {
		global $_zp_db;
		$folder = $alb->name;
		$sql = 'DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . $_zp_db->quote($this->getInstance()) . ' AND `data`=' . $_zp_db->quote(serialize(array('type' => 'albums', 'id' => $folder)));
		$_zp_db->query($sql);
		zp_apply_filter('favoritesHandler_action', 'remove', $alb, $this->name);
	}

	static function getWatchers($obj) {
		global $_zp_db;
		switch ($obj->table) {
			case 'images':
				$folder = $obj->imagefolder;
				$filename = $obj->filename;
				$sql = 'SELECT DISTINCT `aux` FROM ' . $_zp_db->prefix('plugin_storage') . '  WHERE `data`=' . $_zp_db->quote(serialize(array('type' => 'images', 'id' => $folder . '/' . $filename)));
				break;
			case 'albums':
				$folder = $obj->name;
				$sql = 'SELECT DISTINCT `aux` FROM ' . $_zp_db->prefix('plugin_storage') . '  WHERE `data`=' . $_zp_db->quote(serialize(array('type' => 'albums', 'id' => $folder)));
				break;
		}
		$watchers = array();
		$result = $_zp_db->queryFullArray($sql);
		if ($result) {
			foreach ($result as $watch) {
				$watchers[] = $watch['aux'];
			}
		}
		return $watchers;
	}

	static function showWatchers($html, $obj, $prefix) {
		$watchers = self::getWatchers($obj);
		if (!empty($watchers)) {
			sortArray($watchers);
			?>
			<tr>
				<td>
					<?php echo gettext('Users watching:'); ?>
				</td>
				<td>
					<ul class="userlist">
						<?php
						foreach ($watchers as $watchee) {
							?>
							<li>
								<?php echo html_encode($watchee); ?>
							</li>
							<?php
						}
						?>
					</ul>
				</td>
			</tr>
			<?php
		}

		return $html;
	}

	/**
	 * Returns all folder names for all the subdirectories.
	 *
	 * @param string $page  Which page of subalbums to display.
	 * @param string $sorttype The sort strategy
	 * @param string $sortdirection The direction of the sort
	 * @param bool $care set to false if the order does not matter
	 * @param bool $mine set true/false to override ownership
	 * @return array
	 */
	function getAlbums($page = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		global $_zp_gallery, $_zp_db;
		if ($mine || is_null($this->subalbums) || $care && $sorttype . $sortdirection !== $this->lastsubalbumsort) {
			$results = array();
			$result = $_zp_db->query('SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . $_zp_db->quote($this->getInstance()) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"albums\";%"');
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$data = getSerializedArray($row['data']);
					$albumobj = AlbumBase::newAlbum($data['id'], true, true);
					if ($albumobj->exists) { // fail to instantiate?
						$results[$data['id']] = $albumobj->getData();
					} else {
						$_zp_db->query("DELETE FROM " . $_zp_db->prefix('plugin_storage') . ' WHERE `id`=' . $row['id']);
					}
				}
				$_zp_db->freeResult($result);
				if (is_null($sorttype)) {
					$sorttype = $this->getSortType('album');
				}
				if (is_null($sortdirection)) {
					if ($this->getSortDirection('album')) {
						$sortdirection = 'DESC';
					}
				}
				$sortkey = $this->getAlbumSortKey($sorttype);
				if (($sortkey == '`sort_order`') || ($sortkey == 'RAND()')) { // manual sort is always ascending
					$order = false;
				} else {
					if (!is_null($sortdirection)) {
						$order = strtoupper($sortdirection) == 'DESC';
					} else {
						$order = $this->getSortDirection('album');
					}
				}
				$results = sortByKey($results, $sortkey, $order);
				$this->subalbums = array_keys($results);
				$this->lastsubalbumsort = $sorttype . $sortdirection;
			}
		}
		return parent::getAlbums($page);
	}

	/**
	 * Returns a of a slice of the images for this album. They will
	 * also be sorted according to the sort type of this album, or by filename if none
	 * has been set.
	 *
	 * @param string $page  Which page of images should be returned. If zero, all images are returned.
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype optional sort type
	 * @param string $sortdirection optional sort direction
	 * @param bool $care set to false if the order of the images does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = null, $sortdirection = null, $care = true, $mine = NULL) {
		global $_zp_db;
		if ($mine || is_null($this->images) || $care && $sorttype . $sortdirection !== $this->lastimagesort) {
			$this->images = NULL;
			$images = array();
			$result = $_zp_db->query('SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="favorites" AND `aux`=' . $_zp_db->quote($this->getInstance()) . ' AND `data` LIKE "%s:4:\"type\";s:6:\"images\";%"');
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$id = $row['id'];
					$data = getSerializedArray($row['data']);
					$imageObj = Image::newImage(NULL, array('folder' => dirname($data['id']), 'filename' => basename($data['id'])), true);
					if ($imageObj->exists) {
						$images[] = array_merge(array('folder' => dirname($data['id']), 'filename' => basename($data['id'])), $imageObj->getData());
					} else {
						$_zp_db->query("DELETE FROM " . $_zp_db->prefix('plugin_storage') . ' WHERE `id`=' . $row['id']);
					}
				}
				$_zp_db->freeResult($result);

				if (is_null($sorttype)) {
					$sorttype = $this->getSortType();
				}
				$sortkey = str_replace('` ', ' ', $this->getImageSortKey($sorttype));
				if (($sortkey == 'sort_order') || ($sortkey == 'RAND()')) {
// manual sort is always ascending
					$order = false;
				} else {
					if (!is_null($sortdirection)) {
						$order = strtoupper($sortdirection) == 'DESC';
					} else {
						$order = $this->getSortDirection('image');
					}
				}
				$images = sortByKey($images, $sortkey, $order);
				$this->images = array();
				foreach ($images as $data) {
					$this->images[] = array('folder' => $data['folder'], 'filename' => $data['filename']);
				}
				$this->lastimagesort = $sorttype . $sortdirection;
			}
		}
		return parent::getImages($page, $firstPageCount);
	}

	static function loadScript($script, $request) {
		global $_zp_current_admin_obj, $_zp_gallery_page, $_zp_myfavorites, $_zp_current_album, $_zp_conf_vars;
		if ($_zp_myfavorites && isset($_REQUEST['instance'])) {
			$_zp_myfavorites->instance = sanitize(rtrim($_REQUEST['instance'], '/'));
			if ($_zp_myfavorites->instance)
				$_zp_myfavorites->setTitle($_zp_myfavorites->getTitle() . '[' . $_zp_myfavorites->instance . ']');
		}
		if ($_zp_gallery_page == "favorites.php") {
			if (zp_loggedin()) {
				$_zp_current_album = $_zp_myfavorites;
				add_context(ZP_ALBUM);
				prepareAlbumPage();
				$_zp_gallery_page = 'favorites.php';
			} else {
				$script = false;
			}
		}
		return $script;
	}

	static function pageCount($count, $gallery_page, $page) {
		global $_zp_first_page_images, $_zp_one_image_page;
		if (stripSuffix($gallery_page) == 'favorites') {
			$albums_per_page = max(1, getOption('albums_per_page'));
			$pageCount = (int) ceil(getNumAlbums() / $albums_per_page);
			$imageCount = getNumImages();
			if ($_zp_one_image_page) {
				if ($_zp_one_image_page === true) {
					$imageCount = min(1, $imageCount);
				} else {
					$imageCount = 0;
				}
			}
			$images_per_page = max(1, getOption('images_per_page'));
			$count = ($pageCount + (int) ceil(($imageCount - $_zp_first_page_images) / $images_per_page));
			if ($count < $page && isset($_POST['addToFavorites']) && !$_POST['addToFavorites']) {
//We've deleted last item on page, need a place to land when we return
				global $_zp_page;
				redirectURL(FULLWEBPATH . '/' . $this->getLink($_zp_page - 1));
			}
		}
		return $count;
	}

	static function toolbox($zf) {
		printFavoritesURL(gettext('Favorites'), '<li>', '</li><li>', '</li>');
		return $zf;
	}

	function getLink($page = NULL, $instance = NULL, $path = null) {
		$link = _FAVORITES_ . '/';
		$link_no = 'index.php?p=favorites';
		if (is_null($instance))
			$instance = $this->instance;
		if ($instance) {
			$instance = rtrim($instance, '/');
			$link .= $instance . '/';
			$link_no .= '&instance=' . $instance;
		}
		if ($page > 1) {
			$link .= $page . '/';
			$link_no .= '&page=' . $page;
		}
		return zp_apply_filter('getLink', rewrite_path($link, $link_no, $path), 'favorites.php', $page);
	}

	static function ad_removeButton($obj, $id, $v, $add, $instance, $multi) {
		global $_zp_myfavorites;
		$table = $obj->table;
		if ($v) {
			$tag = '_add';
		} else {
			$tag = '_remove';
		}
		if ($instance && $multi) {
			$add .= '[' . $instance . ']';
		}
		?>
		<form name="<?php echo $table . $obj->getID(); ?>Favorites_<?php echo $instance . $tag; ?>" class = "<?php echo $table; ?>Favorites<?php echo $tag; ?>"  action = "<?php echo html_encode(getRequestURI()); ?>" method = "post" accept-charset = "UTF-8">
			<input type = "hidden" name = "addToFavorites" value = "<?php echo $v; ?>" />
			<input type = "hidden" name = "type" value = "<?php echo html_encode($table); ?>" />
			<input type = "hidden" name = "id" value = "<?php echo html_encode($id); ?>" />
			<input type = "submit" class = "button buttons" value = "<?php echo $add; ?>" title = "<?php echo $add; ?>"/>
			<?php
			if ($v) {
				if ($multi) {
					?>
					<span class="tagSuggestContainer">
						<input type="text" name="instance" class="favorite_instance" value="" />
					</span>
					<?php
				}
			} else {
				?>
				<input type="hidden" name="instance" value="<?php echo $_zp_myfavorites->instance; ?>" />
				<?php
			}
			?>
		</form>
		<?php
	}

}
