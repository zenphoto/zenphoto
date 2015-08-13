<?php
/**
 * Allows users to “publish” their favorites as albums that others can view
 *
 * The <i>favoritesHandler</i> plugin is needed in order to identify the items to
 * go inot an album or to maintain the list of items. However it need not be enabled
 * for this plugin to function.
 *
 * <i>favorites</i> albums behave much like dynamic albums. They contain images and
 * albums from the gallery. The main difference is that the items in the album
 * are not produced form a <i>search</i> of the gallery. Instead they are the content of
 * the user's favorites (either named or not.)
 *
 * To create a <i>favorites</i> album visit the <i>favorites</i> link of the content
 * you wish to share. In the <var>admin toolbox</var> on that page you will find
 * a link to create an album. (Much like such a link is present on search pages to
 * allow you to create dynamic albums.) <b>NOTE:</b> the user must have <var>upload</var> rights
 * to at least one album or he will have no place to put the favorites album.
 *
 * <i>favorites</i> albums are represented in the album folders by files with the suffix
 * <var>fav</var>. However, like dynamic albums, the suffix will normally be omitted
 * in links so long as there is not a file/folder with of the stripped suffix name.
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Publish <em>favorites</em> into albums that others can view.');
$plugin_author = "Stephen Billard (sbillard)";

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesHandler/favoritesClass.php');
define("FAVORITESALBUM_FOLDER", ZENFOLDER . '/' . PLUGIN_FOLDER . '/favoritesAlbums/');

class favoritesAlbum extends favorites {

	function __construct($folder8, $cache = true, $quiet = false) {

		$folder8 = trim($folder8, '/');
		$folderFS = internalToFilesystem($folder8);
		$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS;

		$this->linkname = $this->name = $folder8;
		$this->localpath = rtrim($localpath, '/');
		if (!$this->exists = AlbumBase::albumCheck($folder8, $folderFS, $quiet, !file_exists($this->localpath) || is_dir($this->localpath))) {
			return;
		}
		$data = explode("\n", file_get_contents($localpath));
		foreach ($data as $param) {
			$parts = explode('=', $param);
			switch (trim($parts[0])) {
				case 'USER':
					$owner = trim($parts[1]);
					break;
				case 'TITLE':
					$this->instance = trim($parts[1]);
					break;
				case 'THUMB':
					$this->set('thumb', trim($parts[1]));
					break;
			}
		}

		$new = $this->instantiate('albums', array('folder' => $this->name), 'folder', $cache);
		$title = $this->getTitle('all');
		$desc = $this->getDesc('all');

		parent::__construct($owner);
		$this->exists = true;
		if (!is_dir(stripSuffix($this->localpath))) {
			$this->linkname = stripSuffix($folder8);
		}
		$this->name = $folder8;
		$this->setTitle($title);
		$this->setDesc($desc);
		if ($new) {
			$title = $this->get('title');
			$this->set('title', stripSuffix($title)); // Strip the suffix
			$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));
			$this->save();
			zp_apply_filter('new_album', $this);
		}
		zp_apply_filter('album_instantiate', $this);
	}

	/**
	 * Sets default values for a new album
	 *
	 * @return bool
	 */
	protected function setDefaults() {
		global $_zp_gallery;
		// Set default data for a new Album (title and parent_id)
		parent::setDefaults();
		$parentalbum = $this->getParent();
		$this->set('mtime', filemtime($this->localpath));
		$this->setDateTime(strftime('%Y-%m-%d %H:%M:%S', $this->get('mtime')));

		$title = trim($this->name);
		if (!is_null($parentalbum)) {
			$this->set('parentid', $parentalbum->getID());
			$title = substr($title, strrpos($title, '/') + 1);
		}
		$this->set('title', $title);
		return true;
	}

	function getLink($page = NULL, $instance = NULL) {
		return AlbumBase::getLink($page);
	}

	function isDynamic() {
		return 'fav';
	}

	protected function succeed($dest) {
		return @copy($this->localpath, $dest);
	}

	function move($newfolder) {
		return $this->_move($newfolder);
	}

	/**
	 * Delete the entire album PERMANENTLY. Be careful! This is unrecoverable.
	 * Returns true if successful
	 *
	 * @return bool
	 */
	function remove() {
		if ($rslt = parent::remove()) {
			@chmod($this->localpath, 0777);
			$rslt = @unlink($this->localpath);
			clearstatcache();
		}
		$this->_removeCache(substr($this->localpath, strlen(ALBUM_FOLDER_SERVERPATH)));
		return $rslt;
	}

	function getSearchEngine() {
		return NULL;
	}

	static function toolbox($zf) {
		global $_zp_gallery_page;
		if (zp_loggedin(ALBUM_RIGHTS)) {
			if ($_zp_gallery_page == 'favorites.php') {
				?>
				<li>
					<a href="<?php echo WEBPATH . '/' . FAVORITESALBUM_FOLDER; ?>admin-album.php?title=<?php echo @$_GET['instance']; ?>" title="<?php echo gettext('Create an album from favorites'); ?>"><?php echo gettext('Create Album'); ?></a>
				</li>
				<?php
			}
		}
		return $zf;
	}

}

Gallery::addAlbumHandler('fav', 'favoritesAlbum');
zp_register_filter('admin_toolbox_global', 'favoritesAlbum::toolbox', 20);
?>