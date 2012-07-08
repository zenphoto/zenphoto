<?php
/**
 * Allows registered users to select and manage "favorite" Zenphoto objects.
 * Currently just images are supported.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext('Support for <em>favorites</em> handling.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('theme_head', 'favorites::theme_head');


class favorites extends AlbumBase {

	function __construct($user) {
		$this->name = $user;
		$this->setTitle(gettext('My favorites'));
	}

	function addImage($img) {
		$folder = $img->album->name;
		$filename = $img->filename;
		$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("favorites",'.db_quote($this->name).','.db_quote(serialize(array('type'=>'images', 'folder'=>$folder,'filename'=>$filename))).')';
		query($sql);
	}

	function removeImage($img) {
		$folder = $img->album->name;
		$filename = $img->filename;
		$sql = 'DELETE FROM '.prefix('plugin_storage').' WHERE `type`="favorites" AND `aux`='.db_quote($this->name).' AND `data`='.db_quote(serialize(array('type'=>'images', 'folder'=>$folder,'filename'=>$filename)));
		query($sql);
	}

	function getImages($page=0, $firstPageCount=0, $sorttype=null, $sortdirection=null, $care=true, $mine=NULL) {
		if (true || is_null($this->images) || $care && $sorttype.$sortdirection !== $this->lastimagesort) {
			$images = array();
			$result = query($sql = 'SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="favorites" AND `aux`='.db_quote($this->name));
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$row = unserialize($row['data']);
					if ($row['type']=='images') {
						$images[] = $row;
					}
				}
				if (is_null($sorttype)) {
					$sorttype = $this->getSortType();
				}
				$sortkey = str_replace('`','',$this->getImageSortKey($sorttype));
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
				$this->images = sortByKey($images,$sortkey,$order);
				$this->lastimagesort = $sorttype.$sortdirection;
			}
			// Return the cut of images based on $page. Page 0 means show all.
			if ($page == 0) {
				return $this->images;
			} else {
				// Only return $firstPageCount images if we are on the first page and $firstPageCount > 0
				if (($page==1) && ($firstPageCount>0)) {
					$pageStart = 0;
					$images_per_page = $firstPageCount;

				} else {
					if ($firstPageCount>0) {
						$fetchPage = $page - 2;
					} else {
						$fetchPage = $page - 1;
					}
					$images_per_page = max(1, getOption('images_per_page'));
					$pageStart = $firstPageCount + $images_per_page * $fetchPage;

				}
				return array_slice($this->images, $pageStart , $images_per_page);
			}

		}
		return $images;
	}

	static function theme_head() {
		global $_zp_current_album, $_zp_gallery_page, $_myFavorites;
		if ($_zp_gallery_page == 'favorites.php') {
			$_zp_current_album = $_myFavorites;
			add_context(ZP_ALBUM);
		}
	}

}

if (!OFFSET_PATH && zp_loggedin()) {
	$_myFavorites = new favorites($_zp_current_admin_obj->getUser());

	if (isset($_POST['addToFavorites'])) {
		$folder = sanitize($_POST['album']);
		$filename = sanitize($_POST['image']);
		$img = newImage(NULL, array('folder'=>$folder, 'filename'=>$filename));
		if ($_POST['addToFavorites']) {
			if ($img->loaded) {
				$_myFavorites->addImage($img);
			}
		} else {
			$_myFavorites->removeImage($img);
		}
	}

	function printAddToFavorites($add=NULL, $remove=NULL) {
		global $_zp_current_image, $_myFavorites;
		$folder = $_zp_current_image->album->name;
		$filename = $_zp_current_image->filename;
		$images = $_myFavorites->getImages(0);
		$v = 1;
		if (is_null($add)) {
			$add = gettext('Add to favorites');
		}
		foreach ($images as $image) {
			if ($image['folder']==$folder && $image['filename']==$filename) {
				$v = 0;
				if (is_null($remove)) {
					$add = gettext('Remove from favorites');
				} else {
					$add = $remove;
				}
				break;
			}
		}

		?>
		<form name="imageFavorites" class="imageFavorites" id="imageFavorites<?php echo $_zp_current_image->getID(); ?>" vaction="<?php echo sanitize($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="UTF-8">
			<input type="hidden" name="addToFavorites" value="<?php echo $v; ?>" />
			<input type="hidden" name="album" value="<?php echo html_encode($folder); ?>" />
			<input type="hidden" name="image" value="<?php echo html_encode($filename); ?>" />
			<span id="submit_button">
				<input type="submit" value="<?php echo $add; ?>" />
			</span>
		</form>
		<br clear="all" />
		<?php
	}

	function printFavoritesLink($text=NULL) {
		if (is_null($text)) {
			$text = gettext('My favorites');
		}
		?>
		<a href="<?php echo FULLWEBPATH; ?>/page/favorites"><?php echo $text; ?></a>
		<?php
	}

}
?>
