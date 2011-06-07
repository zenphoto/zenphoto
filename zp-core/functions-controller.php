<?php
/**
 * Common functions used in the controller for getting/setting current classes,
 * redirecting URLs, and working with the context.
 * @package core
 */

// force UTF-8 Ø



// Determines if this request used a query string (as opposed to mod_rewrite).
// A valid encoded URL is only allowed to have one question mark: for a query string.
function is_query_request() {
	return (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '?') !== false);
}


/**
 * Returns the URL of any main page (image/album/page#/etc.) in any form
 * desired (rewrite or query-string).
 * @param $with_rewrite boolean or null, whether the returned path should be in rewrite form.
 *   Defaults to null, meaning use the mod_rewrite configuration to decide.
 * @param $album : the Album object to use in the path. Defaults to the current album (if null).
 * @param $image : the Image object to use in the path. Defaults to the current image (if null).
 * @param $page : the page number to use in the path. Defaults to the current page (if null).
 */
function zpurl($with_rewrite=NULL, $album=NULL, $image=NULL, $page=NULL, $special='') {
	global $_zp_current_album, $_zp_current_image, $_zp_page;
	// Set defaults
	if ($with_rewrite === NULL)  $with_rewrite = MOD_REWRITE;
	if (!$album)  $album = $_zp_current_album;
	if (!$image)  $image = $_zp_current_image;
	if (!$page)   $page  = $_zp_page;

	$url = '';
	if ($with_rewrite) {
		if (in_context(ZP_IMAGE)) {
			$encoded_suffix = implode('/', array_map('rawurlencode', explode('/', IM_SUFFIX)));
			$url = pathurlencode($album->name) . '/' . rawurlencode($image->filename) . $encoded_suffix;
		} else if (in_context(ZP_ALBUM)) {
			$url = pathurlencode($album->name) . ($page > 1 ? '/page/'.$page : '');
		} else if (in_context(ZP_INDEX)) {
			$url = ($page > 1 ? 'page/' . $page : '');
		}
	} else {
		if (in_context(ZP_IMAGE)) {
			$url = 'index.php?album=' . pathurlencode($album->name) . '&image='. rawurlencode($image->filename);
		} else if (in_context(ZP_ALBUM)) {
			$url = 'index.php?album=' . pathurlencode($album->name) . ($page > 1 ? '&page='.$page : '');
		} else if (in_context(ZP_INDEX)) {
			$url = 'index.php' . ($page > 1 ? '?page='.$page : '');
		}
	}
	if ($url == IM_SUFFIX || empty($url)) { $url = ''; }
	if (!empty($url) && !(empty($special))) {
		if ($page > 1) {
			$url .= "&$special";
		} else {
			$url .= "?$special";
		}
	}
	return $url;
}


/**
 * Checks to see if the current URL matches the correct one, redirects to the
 * corrected URL if not with a 301 Moved Permanently.
 */
function fix_path_redirect() {
	if (MOD_REWRITE) {
		$sfx = IM_SUFFIX;
		$request_uri = urldecode($_SERVER['REQUEST_URI']);
		$i = strpos($request_uri, '?');
		if ($i !== false) {
			$params = substr($request_uri, $i+1);
			$request_uri = substr($request_uri, 0, $i);
		} else {
			$params = '';
		}
		if (strlen($sfx) > 0 && in_context(ZP_IMAGE) && substr($request_uri, -strlen($sfx)) != $sfx ) {
			$redirecturl = zpurl(true, NULL, NULL, NULL, $params);
			header("HTTP/1.0 301 Moved Permanently");
			header("Status: 301 Moved Permanently");
			header('Location: ' . FULLWEBPATH . '/' . $redirecturl);
			exit();
		}
	}
}


/******************************************************************************
 ***** Action Handling and context data loading functions *********************
 ******************************************************************************/

function zp_handle_comment() {
	global $_zp_current_image, $_zp_current_album, $_zp_comment_stored, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	$activeImage = false;
	$comment_error = 0;
	$cookie = zp_getCookie('zenphoto_comment');
	if (isset($_POST['comment'])) {
		if ((in_context(ZP_ALBUM) || in_context(ZP_ZENPAGE_NEWS_ARTICLE) || in_context(ZP_ZENPAGE_PAGE))) {
			if (isset($_POST['name'])) {
				$p_name = sanitize($_POST['name'],3);
			} else {
				$p_name = NULL;
			}
			if (isset($_POST['email'])) {
				$p_email = sanitize($_POST['email'], 3);
			} else {
				$p_email = NULL;
			}
			if (isset($_POST['website'])) {
				$p_website = sanitize($_POST['website'], 3);
			} else {
				$p_website = NULL;
			}
			if (isset($_POST['comment'])) {
				$p_comment = sanitize($_POST['comment'], 1);
			} else {
				$p_comment = '';
			}
			$p_server = getUserIP();
			if (isset($_POST['code'])) {
				$code1 = sanitize($_POST['code'], 3);
				$code2 = sanitize($_POST['code_h'], 3);
			} else {
				$code1 = '';
				$code2 = '';
			}
			$p_private = isset($_POST['private']);
			$p_anon = isset($_POST['anon']);

			if (isset($_POST['imageid'])) {  //used (only?) by the tricasa hack to know which image the client is working with.
				$activeImage = zp_load_image_from_id(sanitize_numeric($_POST['imageid']));
				if ($activeImage !== false) {
					$commentadded = $activeImage->addComment($p_name, $p_email,	$p_website, $p_comment,
																							$code1, $code2,	$p_server, $p_private, $p_anon);
					$redirectTo = $activeImage->getImageLink();
					}
			} else {
				if (in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
					$commentobject = $_zp_current_image;
					$redirectTo = $_zp_current_image->getImageLink();
				} else if (!in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)){
					$commentobject = $_zp_current_album;
					$redirectTo = $_zp_current_album->getAlbumLink();
				} else 	if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
					$commentobject = $_zp_current_zenpage_news;
					$redirectTo = FULLWEBPATH . '/index.php?p=news&title='.$_zp_current_zenpage_news->getTitlelink();
				} else if (in_context(ZP_ZENPAGE_PAGE)) {
					$commentobject = $_zp_current_zenpage_page;
					$redirectTo = FULLWEBPATH . '/index.php?p=pages&title='.$_zp_current_zenpage_page->getTitlelink();
				}
				$commentadded = $commentobject->addComment($p_name, $p_email, $p_website, $p_comment,
													$code1, $code2,	$p_server, $p_private, $p_anon);
			}
			$comment_error = $commentadded->getInModeration();
			$_zp_comment_stored = array($commentadded->getName(), $commentadded->getEmail(), $commentadded->getWebsite(), $commentadded->getComment(), false,
																	$commentadded->getPrivate(), $commentadded->getAnon(), $commentadded->getCustomData());
			if (isset($_POST['remember'])) $_zp_comment_stored[4] = true;
			if (!$comment_error) {
				if (isset($_POST['remember'])) {
					// Should always re-cookie to update info in case it's changed...
					$_zp_comment_stored[3] = ''; // clear the comment itself
					zp_setCookie('zenphoto_comment', implode('|~*~|', $_zp_comment_stored), NULL, '/');
				} else {
					zp_setCookie('zenphoto_comment', '', -368000, '/');
				}
				//use $redirectTo to send users back to where they came from instead of booting them back to the gallery index. (default behaviour)
				if (!isset($_SERVER['SERVER_SOFTWARE']) || strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') === false) {
					// but not for Microsoft IIS because that server fails if we redirect!
					header('Location: ' . $redirectTo);
					exit();
				}
			} else {
				$comment_error++;
				if ($activeImage !== false AND !in_context(ZP_ZENPAGE_NEWS_ARTICLE) AND !in_context(ZP_ZENPAGE_PAGE)) { // tricasa hack? Set the context to the image on which the comment was posted
					$_zp_current_image = $activeImage;
					$_zp_current_album = $activeImage->getAlbum();
					add_context(ZP_ALBUM | ZP_INDEX);
				}
			}
		}
		return $commentadded->comment_error_text;
	} else if (!empty($cookie)) {
		// Comment form was not submitted; get the saved info from the cookie.
		$_zp_comment_stored = explode('|~*~|', stripslashes($cookie));
		$_zp_comment_stored[4] = true;
		if (!isset($_zp_comment_stored[5])) $_zp_comment_stored[5] = false;
		if (!isset($_zp_comment_stored[6])) $_zp_comment_stored[6] = false;
		if (!isset($_zp_comment_stored[7])) $_zp_comment_stored[7] = false;
	} else {
		$_zp_comment_stored = array('','','', '', false, false, false, false);
	}
	return false;
}

/**
 * Handle AJAX editing in place
 *
 * @param string $context 	either 'image' or 'album', object to be updated
 * @param string $field		field of object to update (title, desc, etc...)
 * @param string $value		new edited value of object field
 * @since 1.3
 * @author Ozh
 **/
function editInPlace_handle_request($context = '', $field = '', $value = '', $orig_value = '') {
	// Cannot edit when context not set in current page (should happen only when editing in place from index.php page)
	if ( !in_context(ZP_IMAGE) && !in_context(ZP_ALBUM) && !in_context(ZP_ZENPAGE_PAGE) && !in_context(ZP_ZENPAGE_NEWS_ARTICLE))
	die ($orig_value.'<script type="text/javascript">alert("'.gettext('Oops.. Cannot edit from this page').'");</script>');

	// Make a copy of context object
	switch ($context) {
		case 'image':
			global $_zp_current_image;
			$object = $_zp_current_image;
			break;
		case 'album':
			global $_zp_current_album;
			$object = $_zp_current_album;
			break;
		case 'zenpage_page':
			global $_zp_current_zenpage_page;
			$object = $_zp_current_zenpage_page;
			break;
		case 'zenpage_news':
			global $_zp_current_zenpage_news;
			$object = $_zp_current_zenpage_news;
			break;
		default:
			die (gettext('Error: malformed Ajax POST'));
	}

	// Dates need to be handled before stored
	if ($field == 'date') {
		$value = date('Y-m-d H:i:s', strtotime($value));
	}

	// Sanitize new value
	switch ($field) {
		case 'desc':
			$level = 1;
			break;
		case 'title':
			$level = 2;
			break;
		default:
			$level = 3;
	}
	$value = str_replace("\n", '<br />', sanitize($value, $level)); // note: not using nl2br() here because it adds an extra "\n"

	// Write new value
	if ($field == '_update_tags') {
		$value = trim($value, ', ');
		$object->setTags($value);
	} else {
		$object->set($field, $value);
	}

	$result = $object->save();
	if ($result !== false) {
		echo $value;
	} else {
		echo ('<script type="text/javascript">alert("'.gettext('Could not save!').'");</script>'.$orig_value);
	}
	die();
}

function zp_load_page($pagenum=NULL) {
	global $_zp_page;
	if (!is_numeric($pagenum)) {
		$_zp_page = isset($_GET['page']) ? $_GET['page'] : 1;
	} else {
		$_zp_page = round($pagenum);
	}
}


/**
 * Loads the gallery if it hasn't already been loaded.
 */
function zp_load_gallery() {
	global $_zp_gallery;
	if (is_null($_zp_gallery)) {
		$_zp_gallery = new Gallery();
	}
	set_context(ZP_INDEX);
}

/**
 * Loads the search object if it hasn't already been loaded.
 */
function zp_load_search() {
	global $_zp_current_search;
	zp_setCookie("zenphoto_search_params", "", -368000);
	if (!is_object($_zp_current_search)) {
		$_zp_current_search = new SearchEngine();
	}
	add_context(ZP_SEARCH);
	$params = $_zp_current_search->getSearchParams();
	zp_setCookie("zenphoto_search_params", $params, SEARCH_DURATION);
	return $_zp_current_search;
}

/**
 * zp_load_album - loads the album given by the folder name $folder into the
 * global context, and sets the context appropriately.
 * @param $folder the folder name of the album to load. Ex: 'testalbum', 'test/subalbum', etc.
 * @param $force_cache whether to force the use of the global object cache.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_album($folder, $force_nocache=false) {
	global $_zp_current_album, $_zp_gallery, $_zp_dynamic_album;
	$_zp_current_album = new Album($_zp_gallery, $folder, !$force_nocache, true);
	if (!is_object($_zp_current_album) || !$_zp_current_album->exists) return false;
	if ($_zp_current_album->isDynamic()) {
		$_zp_dynamic_album = $_zp_current_album;
	} else {
		$_zp_dynamic_album = null;
	}
	add_context(ZP_ALBUM);
	return $_zp_current_album;
}

/**
 * zp_load_image - loads the image given by the $folder and $filename into the
 * global context, and sets the context appropriately.
 * @param $folder is the folder name of the album this image is in. Ex: 'testalbum'
 * @param $filename is the filename of the image to load.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_image($folder, $filename) {
	global $_zp_current_image, $_zp_current_album, $_zp_current_search;
	if (!is_object($_zp_current_album) || $_zp_current_album->name != $folder) {
		$album = zp_load_album($folder, false, true);
	} else {
		$album = $_zp_current_album;
	}
	if (!is_object($album) || !$album->exists) return false;
	$_zp_current_image = newImage($album, $filename, true);
	if (is_null($_zp_current_image) || !$_zp_current_image->exists) {
		return false;
	}
	add_context(ZP_IMAGE | ZP_ALBUM);
	return $_zp_current_image;
}

/**
 * Loads a zenpage pages page
 * Sets up $_zp_current_zenpage_page and returns it as the function result.
 * @param $titlelink the titlelink of a zenpage page to setup a page object directly. Meant to be used only for the Zenpage homepage feature.
 * @return object
 */
function zenpage_load_page() {
	global $_zp_current_zenpage_page;
	if (isset($_GET['title'])) {
		$titlelink = sanitize($_GET['title'],3);
	} else {
		$titlelink = '';
	}
	$_zp_current_zenpage_page = new ZenpagePage($titlelink);
	if ($_zp_current_zenpage_page->loaded) {
		add_context(ZP_ZENPAGE_PAGE | ZP_ZENPAGE_SINGLE);
	} else {
		$_GET['p'] = 'PAGES:'.$titlelink;
	}
	return $_zp_current_zenpage_page;
}

/**
 * Loads a zenpage news page
 * Sets up $_zp_current_zenpage_news and returns it as the function result.
 *
 * @return object
 */
function zenpage_load_news() {
	global $_zp_current_zenpage_news, $_zp_current_category, $_zp_post_date;
	if (isset($_GET['date'])) {
		add_context(ZP_ZENPAGE_NEWS_DATE);
		$_zp_post_date = sanitize($_GET['date']);
	}
	if(isset($_GET['category'])) {
		$titlelink = sanitize($_GET['category']);
		$_zp_current_category = new ZenpageCategory($titlelink);
		if ($_zp_current_category->loaded) {
			add_context(ZP_ZENPAGE_NEWS_CATEGORY);
		} else {
			$_GET['p'] = 'CATEGORY:'.$titlelink;
			unset($_GET['category']);
			return false;
		}
	}
	if (isset($_GET['title'])) {
		$titlelink = sanitize($_GET['title'],3);
		$sql = 'SELECT `id` FROM '.prefix('news').' WHERE `titlelink`='.db_quote($titlelink);
		$result = query_single_row($sql);
		if (is_array($result)) {
			add_context(ZP_ZENPAGE_NEWS_ARTICLE | ZP_ZENPAGE_SINGLE);
			$_zp_current_zenpage_news = new ZenpageNews($titlelink);
		} else {
			$_GET['p'] = 'NEWS:'.$titlelink;
		}
		return $_zp_current_zenpage_news;
	}
	return true;
}

/**
 * zp_load_image_from_id - loads and returns the image "id" from the database, without
 * altering the global context or zp_current_image.
 * @param $id the database id-field of the image.
 * @return the loaded image object on success, or (===false) on failure.
 */
function zp_load_image_from_id($id){
	$sql = "SELECT `albumid`, `filename` FROM " .prefix('images') ." WHERE `id` = " . $id;
	$result = query_single_row($sql);
	$filename = $result['filename'];
	$albumid = $result['albumid'];

	$sql = "SELECT `folder` FROM ". prefix('albums') ." WHERE `id` = " . $albumid;
	$result = query_single_row($sql);
	$folder = $result['folder'];

	$album = zp_load_album($folder);
	$currentImage = newImage($album, $filename);
	if (!$currentImage->exists) return false;
	return $currentImage;
}

/**
 * Figures out what is being accessed and calls the appropriate load function
 *
 * @return bool
 */
function zp_load_request() {
	if ($success = zp_apply_filter('load_request',true)) {	// filter allowed the load
		zp_load_page();
		if (isset($_GET['p'])) {
			$page = str_replace(array('/','\\','.'), '', sanitize($_GET['p']));
			if (isset($_GET['t'])) {	//	Zenphoto tiny url
				unset($_GET['t']);
				$tiny = sanitize_numeric($page);
				$asoc = getTableAsoc();
				$tbl = $tiny & 7;
				if (array_key_exists($tbl, $asoc)) {
					$tbl = $asoc[$tbl];
					$id = $tiny>>3;
					$result = query_single_row('SELECT * FROM '.prefix($tbl).' WHERE `id`='.$id);
					if ($result) {
						switch ($tbl) {
							case 'news':
							case 'pages':
								$page = $_GET['p'] = $tbl;
								$_GET['title'] = $result['titlelink'];
								break;
							case 'images':
								$image = $_GET['image'] = $result['filename'];
								$result = query_single_row('SELECT * FROM '.prefix('albums').' WHERE `id`='.$result['albumid']);
							case 'albums':
								$album = $_GET['album'] = $result['folder'];
								unset($_GET['p']);
								if (!empty($image)) {
									return zp_load_image($album, $image);
								} else if (!empty($album)) {
									return zp_load_album($album);
								}
								break;
							case 'comments':
								unset ($_GET['p']);
								$commentid = $id;
								$type = $result['type'];
								$result = query_single_row('SELECT * FROM '.prefix($result['type']).' WHERE `id`='.$result['ownerid']);
								switch ($type) {
									case 'images':
										$image = $result['filename'];
										$result = query_single_row('SELECT * FROM '.prefix('albums').' WHERE `id`='.$result['albumid']);
										$redirect = 'index.php?album='.$result['folder'].'&image='.$image;
										break;
									case 'albums':
										$album = $result['folder'];
										$redirect = 'index.php?album='.$result['folder'];
										break;
									case 'pages':
										$redirect = 'index.php?p=pages&title='.$result['titlelink'];
										break;
								}
								$redirect .= '#c_'.$commentid;
								header("HTTP/1.0 301 Moved Permanently");
								header("Status: 301 Moved Permanently");
								header('Location: ' . FULLWEBPATH . '/' . $redirect);
								exit();
								break;
						}
					}
				}
			}
			switch ($page) {
				case 'search':
					return zp_load_search();
					break;
				case 'pages':
					if (getOption('zp_plugin_zenpage')) {
						return zenpage_load_page();
					}
					break;
				case 'news':
					if (getOption('zp_plugin_zenpage')) {
						return zenpage_load_news();
					}
					break;
			}
		}
		//	may need image and album parameters processed
		list($album, $image) = rewrite_get_album_image('album','image');
		if (!empty($image)) {
			return zp_load_image($album, $image);
		} else if (!empty($album)) {
			return zp_load_album($album);
		}
	}
	return $success;
}

?>