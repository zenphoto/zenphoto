<?php

/**
 * Class for exporting possible privacy related user data
 * 
 * @author Malte Müller (acrylian)
 * @since ZenphotoCMS 1.5
 * 
 * @package core
 * @subpackage classes\helpers
 */
class userDataExport {

	public $username = '';
	public $usermail = '';
	public $galleryobj = '';
	public $authorityobj = '';
	public $data = array();

	function __construct($username, $usermail, $galleryobj, $authorityobj) {
		$this->username = $username;
		$this->usermail = $usermail;
		$this->galleryobj = $galleryobj;
		$this->authorityobj = $authorityobj;
		// in case the plugin is not active data may still exists
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpage.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpageroot.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpageitems.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpagenews.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpagepage.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/class-zenpagecategory.php';
	}

	/**
	 * Gets all available data as an array
	 * 
	 * @return array
	 */
	function getAllData() {
		if (!empty($this->data)) {
			return $this->data;
		}
		$generaldata = $this->getGeneralData();
		$this->data = array_merge($this->getUserData(), $this->getSecurityLogData(), $this->getGalleryData());
		foreach (array('owner', 'user', 'lastchangeuser') as $field) {
			$this->data = array_merge($this->data, $this->getAlbumData($field));
		}
		foreach (array('owner', 'user', 'lastchangeuser') as $field) {
			$this->data = array_merge($this->data, $this->getImageData($field));
		}
		foreach (array('author', 'lastchangeuser') as $field) {
			$this->data = array_merge($this->data, $this->getZenpageData('news', $field));
		}
		foreach (array('user', 'lastchangeuser') as $field) {
			$this->data = array_merge($this->data, $this->getZenpageData('newscategories', $field));
		}
		foreach (array('author', 'lastchangeuser', 'user') as $field) {
			$this->data = array_merge($this->data, $this->getZenpageData('pages', $field));
		}
		foreach (array('name', 'email', 'lastchangeuser') as $field) {
			$this->data = array_merge($this->data, $this->getCommentData($field));
		}
		if (!empty($this->data)) {
			$this->data = array_merge($generaldata, $this->data);
		}
		return $this->data;
	}

	/**
	 * Generates the file name of the report to download
	 * 
	 * @param string $dataformat "html" or "json"
	 * @return string
	 */
	function generateFilename($dataformat = 'html') {
		$email = '';
		if (!empty($this->usermail)) {
			$email = '_' . str_replace('@', '-at-', $this->usermail);
		}
		switch ($dataformat) {
			case 'html':
			default:
				$suffix = '.html';
				break;
			case 'json':
				$suffix = '.json';
				break;
		}
		return date('Y-m-d_H-m-s') . '-userdata-export_' . $this->username . $email . $suffix;
	}

	/**
	 * Handles the download of the data report file
	 * 
	 * @param string $dataformat "html" or "json"
	 */
	function processFileDownload($dataformat = 'html') {
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-disposition: attachment; filename="' . $this->generateFilename($dataformat) . '"');
		XSRFdefender('userdata-export');
		$this->printDataReport($dataformat);
		exitZP();
	}

	/**
	 * Generates the actual report
	 * 
	 * @param string $dataformat "html" or "json"
	 * @return string
	 */
	function printDataReport($dataformat = 'html') {
		$data = $this->getAllData();
		if ($data) {
			switch ($dataformat) {
				case 'html':
				default:
					if (empty($this->usermail)) {
						$title = sprintf(gettext('Personal user data export for %1$s'), $this->username);
					} else {
						$title = sprintf(gettext('Personal user data export for %1$s and %2$s'), $this->username, $this->usermail);
					}
					?>
					<!DOCTYPE html>
					<html<?php printLangAttribute(); ?>>
						<head>
							<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
							<title><?php echo $title; ?></title></head>
						<body>
							<h1><?php echo html_encode($title); ?></h1>
							<?php
							foreach ($data as $sectionheadline => $section) {
								?>
								<h2><?php echo html_encode($sectionheadline); ?></h2>
								<ul>
									<?php
									if ($section) {
										foreach ($section as $headline => $entries) {
											if (is_array($entries)) {
												?>
												<h3><?php echo html_encode($headline); ?></h3>
												<ul>
													<?php
													foreach ($entries as $key => $val) {
														?>
														<li><strong><?php echo html_encode($key); ?>: </strong> 
															<?php
															self::printList($val);
															?>
														</li>
														<?php
													}
													?>
												</ul>
												<?php
											} else {
												?>
												<li><strong><?php echo html_encode($headline); ?>:</strong> <?php self::printLink($entries); ?></li>
												<?php
											}
										}
									}
									?>
								</ul>
								<?php
							}
							break;
							?>
						</body>
					</html>
				<?php
				case 'json':
					echo json_encode($data);
			}
		}
	}

	/**
	 * Helper method for printDataReport() to return a link element if $value is an url.
	 * Otherwise returns the value unchanged
	 * 
	 * @param string $value
	 * @return string
	 */
	static function printLink($value) {
		if (substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://') {
			echo '<a href="' . html_encode($value) . '">' . html_encode($value) . '</a>';
		} else {
			echo $value;
		}
	}

	static function printList($value) {
		if (is_array($value)) {
			echo '<ul>';
			foreach ($value as $key => $val) {
				echo '<li><strong>' . $key . ':</strong> ';
				self::printList($val);
				echo '</li>';
			}
			echo '</ul>';
		} else {
			self::printLink($value);
		}
	}

	/**
	 * Prints the links to export from a user account itself
	 */
	static function printUserAccountExportLinks($userobj) {
		$userdata_linkbase = FULLWEBPATH . '/' . ZENFOLDER . '/admin-users.php?XSRFToken=' . getXSRFToken('userdata-export') . '&amp;userdata-username=' . html_encode($userobj->getUser()) . '&amp;userdata-usermail=' . html_encode($userobj->getEmail()) . '&amp;userdata-format=';
		$userdata_link_html = $userdata_linkbase . 'html';
		$userdata_link_json = $userdata_linkbase . 'json';
		?>
		<div><?php echo gettext('Export user data'); ?>: 
			<p class="buttons">
				<a href="<?php echo $userdata_link_html; ?>"><?php echo gettext('Export as HTML'); ?></a>
				<a href="<?php echo $userdata_link_json; ?>"><?php echo gettext('Export as JSON'); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Generates the general info part
	 * 
	 * @return array
	 */
	function getGeneralData() {
		return array(gettext('Report for') =>
				array(
						gettext('User name') => $this->username,
						gettext('User mail') => $this->usermail,
						gettext('Website') => FULLWEBPATH,
						gettext('Date') => date('Y-m-d H:m:s')
		));
	}

	/**
	 * Gets gallery guest user if exists
	 * 
	 * @return array
	 */
	function getGalleryData() {
		$user = $this->galleryobj->getUser();
		if ($user == $this->username) {
			return array(gettext('Gallery guest user') => $user);
		}
		return array();
	}

	/**
	 * Gets user account data
	 * 
	 * @return array
	 */
	function getUserData() {
		$credentials['user'] = $this->username;
		if (!empty($this->usermail)) {
			$credentials['email'] = $this->usermail;
		}
		$user = $this->authorityobj->getAnAdmin($credentials);
		if ($user) {
			return array(gettext('User account data') => $user->getData());
		}
		return array();
	}

	/**
	 * Gets user data from the security log
	 */
	function getSecurityLogData() {
		$logs = safe_glob(SERVERPATH . "/" . DATA_FOLDER . '/security*.log');
		$tempdata = array();
		foreach ($logs as $log) {
			$expl = array_reverse(explode('/', $log));
			$logfile = $expl[0];
			$handle = fopen($log, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					if (preg_match('/' . $this->username . '/i', $line)) {
						$tempdata[$logfile][] = trim(preg_replace('/\s+/', ' ', $line));
					}
				}
			}
		}
		if (!empty($tempdata)) {
			return array(gettext('Security log entries') => $tempdata);
		}
		return array();
	}

	/**
	 * Gets comment data
	 * @param string $field "owner", "user", "lastchangeuser"
	 * @return array
	 */
	function getCommentData($field) {
		if (!in_array($field, array('name', 'lastchangeuser'))) {
			return array();
		}
		$sectiontitle = gettext('Comments');
		switch ($field) {
			case 'name': 
				$dbquery = "SELECT * FROM " . prefix('comments') . " WHERE name = " . db_quote($this->username);
				break;
			case 'lastchangeuser':
				$dbquery = "SELECT * FROM " . prefix('comments') . " WHERE lastchangeuser = " . db_quote($this->username);
				break;
			case 'email':
				if (!empty($this->usermail)) {
					$dbquery = "SELECT * FROM " . prefix('comments') . " WHERE email = " . db_quote($this->usermail);
				}
				break;
		}
		$result = query($dbquery);
		$tempdata = array();
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				$obj = getItemByID($row['type'], $row['ownerid']);
				$row['URL'] = SERVER_HTTP_HOST . $obj->getLink() . '#zp_comment_id_' . $row['id'];
				$tempdata[] = $row;
			}

			db_free_result($result);
			if (!empty($tempdata)) {
				return array($sectiontitle => $tempdata);
			}
		}
		return array();
	}

	/**
	 * Gets the album data 
	 * 
	 * @param string $field "owner", "user", "lastchangeuser"
	 * @return array
	 */
	function getAlbumData($field) {
		if (!in_array($field, array('owner', 'user', 'lastchangeuser'))) {
			return array();
		}
		switch ($field) {
			case 'owner':
				$sectiontitle = gettext('Album owner');
				$dbquery = "SELECT folder FROM " . prefix('albums') . " WHERE owner = " . db_quote($this->username);
				break;
			case 'user':
				$sectiontitle = gettext('Album guest user');
				$dbquery = "SELECT folder FROM " . prefix('albums') . " WHERE user = " . db_quote($this->username);
				break;
			case 'lastchangeuser':
				$sectiontitle = gettext('Album last change user');
				$dbquery = "SELECT folder FROM " . prefix('albums') . " WHERE lastchangeuser = " . db_quote($this->username);
				break;
		}
		$result = query($dbquery);
		if ($result) {
			$tempdata = array();
			while ($row = db_fetch_assoc($result)) {
				$albobj = newAlbum($row['folder']);
				$title = $albobj->getTitle();
				if (!$albobj->getShow()) {
					$title .= ' [' . gettext('unpublished') . ']';
				}
				if ($albobj->isProtected()) {
					$title .= ' [' . gettext('protected') . ']';
				}
				$tempdata[$title] = SERVER_HTTP_HOST . $albobj->getLink();
			}
			db_free_result($result);
			if (!empty($tempdata)) {
				return array($sectiontitle => $tempdata);
			}
		}
		return array();
	}

	/**
	 * Gets the image data 
	 * 
	 * @param string $field "owner" or "user"
	 * @return array
	 */
	function getImageData($field) {
		if (!in_array($field, array('owner', 'user', 'lastchangeuser'))) {
			return array();
		}
		switch ($field) {
			case 'owner':
				$sectiontitle = gettext('Image owner');
				$dbquery = "SELECT filename, albumid FROM " . prefix('images') . " WHERE owner = " . db_quote($this->username) . ' ORDER By albumid ASC';
				break;
			case 'user':
				$sectiontitle = gettext('Image guest user');
				$dbquery = "SELECT filename, albumid FROM " . prefix('images') . " WHERE user = " . db_quote($this->username) . ' ORDER By albumid ASC';
				break;
			case 'lastchangeuser':
				$sectiontitle = gettext('Image last change user');
				$dbquery = "SELECT filename, albumid FROM " . prefix('images') . " WHERE lastchangeuser = " . db_quote($this->username) . ' ORDER By albumid ASC';
				break;
		}
		$result = query($dbquery);
		$tempdata = array();
		$imagesbyalbum = array();
		$images = array();
		$image_cache_suffix = getOption('image_cache_suffix');
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				$imagesbyalbum[$row['albumid']][] = $row['filename'];
			}
			db_free_result($result);
			foreach ($imagesbyalbum as $albumid => $images) {
				$albobj = getItemByID('albums', $albumid);
				if ($albobj && !$albobj->isDynamic()) {
					foreach ($images as $image) {
						$imgobj = newImage($albobj, $image);
						$title = $imgobj->getTitle();
						if (!$imgobj->getShow()) {
							$title .= ' [' . gettext('unpublished') . ']';
						}
						if ($imgobj->isProtected()) {
							$title .= ' [' . gettext('protected') . ']';
						}
						$tempdata[$albobj->getTitle()][$title][gettext('Full image')] = SERVER_HTTP_HOST . $imgobj->getFullImage();
						//get cached version that current exist
						$filename_nosuffix = stripSuffix($imgobj->filename);
						$suffix = getSuffix($imgobj->filename);
						if (empty($image_cache_suffix)) {
							$suffix = getSuffix($imgobj->filename);
						} else {
							$suffix = $image_cache_suffix;
						}
						$cachedimages = safe_glob(SERVERPATH . '/' . CACHEFOLDER . '/' . $albobj->name . '/' . $filename_nosuffix . '*' . $suffix);
						if (!empty($cachedimages)) {
							$cachedimages_webpath = array();
							foreach ($cachedimages as $cacheimage) {
								$cachedimages_webpath[] = str_replace(SERVERPATH, FULLWEBPATH, $cacheimage);
							}
							$tempdata[$albobj->getTitle()][$title][gettext('Cached images')] = $cachedimages_webpath;
						}
					}
				}
			}
			if (!empty($tempdata)) {
				return array($sectiontitle => $tempdata);
			}
		}
		return array();
	}

	/**
	 * Gets the album data 
	 * 
	 * @param string $field "author", "lastchangeuser", "user" (Note that on some items there are not all of these existing)
	 * @param string $username The user name to search for
	 * @return array
	 */
	function getZenpageData($itemtype, $field) {
		// only pages support all three fields
		if (!in_array($itemtype, array('news', 'newscategories', 'pages')) || !in_array($field, array('author', 'lastchangeuser', 'user')) || ($itemtype == 'news' && $field == 'user') || ($itemtype == 'newscategories' && !in_array($field, array('user', 'lastchangeuser')))) {
			return array();
		}
		switch ($itemtype) {
			case 'news':
				$sectiontitle = gettext('News articles');
				$dbquery = "SELECT titlelink FROM " . prefix('news');
				break;
			case 'newscategories':
				$sectiontitle = gettext('News categories');
				$dbquery = "SELECT titlelink FROM " . prefix('news_categories');
				break;
			case 'pages':
				$sectiontitle = gettext('Pages');
				$dbquery = "SELECT titlelink FROM " . prefix('pages');
				break;
		}
		switch ($field) {
			case 'author':
				$sectiontitle .= ' ' . gettext('author');
				$dbquery .= " WHERE author = " . db_quote($this->username);
				break;
			case 'lastchangeuser':
				$sectiontitle .= ' ' . gettext('last change user');
				$dbquery .= " WHERE lastchangeuser = " . db_quote($this->username);
				break;
			case 'user':
				$sectiontitle .= ' ' . gettext('guest user');
				$dbquery .= " WHERE user = " . db_quote($this->username);
				break;
		}
		$result = query($dbquery);
		if ($result) {
			$tempdata = array();
			while ($row = db_fetch_assoc($result)) {
				switch ($itemtype) {
					case 'news':
						$obj = new ZenpageNews($row['titlelink']);
						break;
					case 'newscategories':
						$obj = new ZenpageCategory($row['titlelink']);
						break;
					case 'pages':
						$obj = new ZenpagePage($row['titlelink']);
						break;
				}
				$title = $obj->getTitle();
				if (!$obj->getShow()) {
					$title .= ' [' . gettext('Unpublished') . ']';
				}
				if ($obj->isProtected()) {
					$title .= ' [' . gettext('protected') . ']';
				}
				$tempdata[$title] = SERVER_HTTP_HOST . $obj->getLink();
			}
			db_free_result($result);
			if (!empty($tempdata)) {
				return array($sectiontitle => $tempdata);
			}
		}
		return array();
	}

}
