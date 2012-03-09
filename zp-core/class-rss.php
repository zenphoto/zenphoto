<?php
/**
 * The feed is dependend on GET parameters available
 * 
 * Intended Usage:
 * $rss = new RSS();
 * $rss->printRSSfeed();
 *
 */
class RSS {
	//general feed type gallery, news or comments
	protected $feedtype = NULL; 
	protected $itemnumber = NULL;
	protected $locale = NULL; // standard locale for lang parameter
	protected $locale_xml = NULL; // xml locale within feed
	protected $host = NULL;
	
	// mode for gallery and comments rss
	protected $rssmode = NULL; // mode for gallery and comments rss
	
	//gallery feed specific vars
	protected $albumfolder = NULL;
	protected $collection = NULL;
	protected $albumname = NULL;
	protected $albumpath = NULL;
	protected $imagepath = NULL;
	protected $imagesize = NULL;
	protected $modrewritesuffix = NULL;
	
	// news feed specific 
	protected $catlink = NULL;
	protected $newsoption = NULL;
	protected $titleappendix = NULL;
	
	//comment feed specific
	protected $id = NULL;
	protected $commentrsstype = NULL;
	
	//channel vars
	protected $channel_title = NULL;
	protected $feeditem = array();

	/**
	* Creates a feed object from the URL parameters fetched only
	*
	*/
	function __construct() {
		global $_zp_gallery;
		if(isset($_GET['rss'])) {
			require_once(ZENFOLDER .  '/lib-MimeTypes.php');
			// general feed setup
			$channeltitlemode = getOption('feed_title');
			$this->host = html_encode($_SERVER["HTTP_HOST"]);
			// url and xml locale
			if(isset($_GET['lang'])) {
				$this->locale = sanitize($_GET['lang']);
			} else {
				$this->locale = getOption('locale');
			}
			$this->locale_xml = strtr($this->locale,'_','-');
			
			//channeltitle general
			switch($channeltitlemode) {
				case 'gallery':
					$this->channel_title = strip_tags(get_language_string($_zp_gallery->get('gallery_title'), $this->locale));
					break;
				case 'website':
					$this->channel_title = strip_tags(get_language_string($_zp_gallery->get('website_title'), $this->locale));
					break;
				case 'both':
					$this->channel_title = strip_tags(get_language_string($_zp_gallery->get('website_title'), $this->locale).' - '.get_language_string($_zp_gallery->get('gallery_title'), $this->locale));
					break;
			}
			// individual feedtype setup
			switch (sanitize($_GET['rss'])) {
				
				default:	//gallery RSS
					if (!getOption('RSS_album_image')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					$this->feedtype = 'gallery';
					if(isset($_GET['albumsmode'])) {
						$this->rssmode = 'albums';
					} else {
						$this->rssmode = '';
					}
					if(isset($_GET['albumtitle'])) {
						$albumname = ' - '.html_encode(sanitize(urldecode($_GET['albumtitle']))).' ('.gettext(' - latest images').')';
					} elseif ($this->rssmode == "albums" && !isset($_GET['folder'])) {
						$albumname = ' ('.gettext('latest albums').')';
					} elseif ($this->rssmode == 'albums' && isset($_GET['folder'])) {
						$folder = sanitize(urldecode($_GET['folder']));
						$albobj = new Album(NULL,$folder);
						$albumname = ' - '.html_encode(strip_tags($albobj->getTitle())).' ('.gettext('latest albums').')';
					} else {
						$albumname = ' ('.gettext('latest images').')';
					}
					$this->channel_title = html_encode($this->channel_title.' '.strip_tags($albumname));
					$this->albumpath = $this->getRSSImageAndAlbumPaths('albumpath');
					$this->imagepath = $this->getRSSImageAndAlbumPaths('imagepath');
					$this->albumfolder = $this->getRSSAlbumnameAndCollection('albumfolder');
					$this->collection = $this->getRSSAlbumnameAndCollection('collection');
					$this->modrewritesuffix = $this->getRSSImageAndAlbumPaths("modrewritesuffix");
					if(isset($_GET['size'])) {
						$this->imagesize = sanitize_numeric($_GET['size']);
					} else {
						$this->imagesize = NULL;
					}
					if(is_numeric($this->imagesize) && !is_null($this->imagesize) && $this->imagesize < getOption('feed_imagesize')) {
						$this->imagesize = $size;
					} else {
						if($this->rssmode == 'albums') {
							$this->imagesize = getOption('feed_imagesize_albums'); // un-cropped image size
						} else {
							$this->imagesize = getOption('feed_imagesize'); // un-cropped image size
						}
					}
					$this->itemnumber = getOption('feed_items');
					require_once(ZENFOLDER .'/'.PLUGIN_FOLDER . '/image_album_statistics.php');
					break;
					
				case 'news':	//Zenpage News RSS
					if (!getOption('RSS_articles')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					$this->feedtype = 'news';
					$this->catlink = $this->getRSSNewsCatOptions('catlink');
					$cattitle = $this->getRSSNewsCatOptions('cattitle');
					if(!empty($cattitle)) {
						$cattitle = ' - '.html_encode($this->cattitle) ;
					}
					$this->newsoption = $this->getRSSNewsCatOptions("option");
					$titleappendix = gettext(' (Latest news)');
					if(isset($_GET['withimages'])) {
						$this->newsoption = 'withimages';
						$titleappendix = gettext(' (Latest news and images)');
					}
					$this->channel_title = html_encode($this->channel_title.$cattitle.$titleappendix);
					$this->imagesize = getOption('feed_imagesize'); // un-cropped image size
					$this->itemnumber = getOption("zenpage_rss_items"); // # of Items displayed on the feed
					require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . '/image_album_statistics.php');
					require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');
					break;
					
				case'comments':	//Comments RSS
					if (!getOption('RSS_comments')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					$this->feedtype = 'comments';
					if(isset($_GET['type'])) {
						$this->commentrsstype = sanitize($_GET['type']);
					} else {
						$this->commentrsstype = 'all';
					}
					if(isset($_GET['id'])) {
						$this->id = sanitize_numeric($_GET['id']);
					} else {
						$this->id = '';
					}
					if(isset($_GET['title'])) {
						$title = ' - '.sanitize($_GET['title']);
					} else {
						$title = NULL;
					}
					$this->channel_title = html_encode($this->channel_title.$title.gettext(' (latest comments)'));
					if(getOption('zp_plugin_zenpage')) {
						require_once(ZENFOLDER . '/'.PLUGIN_FOLDER. '/zenpage/zenpage-template-functions.php');
					}
					break;
					$this->itemnumber = getOption('feed_items');
			}
			$this->feeditems = $this->getRSSitems();
		}
	}
	
	/**
	 * Helper function that returns the image path, album path and modrewrite suffix for Gallery feeds
	 *
	 * @param string $arrayfield "albumpath", "imagepath" or "modrewritesuffix"
	 * @return string
	 */
	protected function getRSSImageAndAlbumPaths($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(MOD_REWRITE) {
			$array['albumpath'] = '/'; 
			$array['imagepath'] = '/';
			$array['modrewritesuffix'] = IM_SUFFIX;
		} else  {
			$array['albumpath'] = '/index.php?album=';
			$array['imagepath'] = '&amp;image=';
			$array['modrewritesuffix'] = '';
		}
		return $array[$arrayfield];
	}
	
	/**
	 * Helper function that returns the albumname and TRUE or FALSE for the collection mode (album + subalbums)
	 *
	 * @param string $arrayfield "albumfolder" or "collection"
	 * @return mixed
	 */
	protected function getRSSAlbumnameAndCollection($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(!empty($arrayfield)) {
			if(isset($_GET['albumname'])) {
				$albumfolder = sanitize_path($_GET['albumname']);
				if(!file_exists(ALBUM_FOLDER_SERVERPATH.'/'.$albumfolder)) {
					$array['albumfolder'] = NULL;
				}
				$array['collection'] = FALSE;
			} else if(isset($_GET['folder'])) {
				$albumfolder = sanitize_path($_GET['folder']);
				if(!file_exists(ALBUM_FOLDER_SERVERPATH.'/'.$albumfolder)) {
					$array['albumfolder'] = NULL;
					$array['collection'] = FALSE;
				} else {
					$array['collection'] = TRUE;
				}
			} else {
				$array['albumfolder'] = NULL;
				$array['collection'] = FALSE;
			}
			return $array[$arrayfield];
		}
	}
	
	/**
	 * Helper function that returns the News category title or catlink (name) or the mode (all news or category only) for the Zenpage news feed.
	 *
	 * @param string $arrayfield "catlink", "catttitle" or "option"
	 * @return string
	 */
	protected function getRSSNewsCatOptions($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(!empty($arrayfield)) {
			if(isset($_GET['category'])) {
				$array['catlink'] = sanitize($_GET['category']);
				$catobj = new ZenpageCategory($catlink);
				$array['cattitle'] = html_encode($catobj->getTitle());
				$array['option'] = 'category';
			} else {
				$array['catlink'] = '';
				$array['cattitle'] = '';
				$array['option'] = 'news';
			}
			return $array[$arrayfield];
		}
	}
	
	/**
	 * Gets the RSS file name from the feed url and clears out query items and special chars
	 *
	 * @return string
	 */
	protected function getRSSCacheFilename() {
		$uri = explode('?',$_SERVER["REQUEST_URI"]);
		$filename = array();
		foreach (explode('&',$uri[1]) as $param) {
			$p = explode('=', $param);
			if (isset($p[1]) && !empty($p[1])) {
				$filename[] = $p[1];
			} else {
				$filename[] = $p[0];
			}
		}
		$filename = seoFriendly(implode('_',$filename));
		return $filename.".xml";
	
	
		//old way
		$replace = array(
		WEBPATH.'/' => '',
											"albumname="=>"_",
											"albumsmode="=>"_",
											"title=" => "_",
											"folder=" => "_",
											"type=" => "-",
											"albumtitle=" => "_",
											"category=" => "_",
											"id=" => "_",
											"lang=" => "_",
											"&amp;" => "_",
											"&" => "_",
											"index.php" => "",
											"/"=>"-",
											"?"=> ""
		);
		$filename = strtr($_SERVER["REQUEST_URI"],$replace);
		$filename = preg_replace("/__/","_",$filename);
		$filename = seoFriendly($filename);
		return $filename.".xml";
	}
	
	/**
	 * Starts static RSS caching
	 *
	 */
	protected function startRSSCache() {
		$caching = getOption("feed_cache") && !zp_loggedin();
		if($caching) {
			$cachefilepath = SERVERPATH."/cache_html/rss/".$this->getRSSCacheFilename();
			if(file_exists($cachefilepath) AND time()-filemtime($cachefilepath) < getOption("feed_cache_expire")) {
				echo file_get_contents($cachefilepath); // PHP >= 4.3
				exitZP();
			} else {
				if(file_exists($cachefilepath)) {
					@chmod($cachefilepath, 0666);
					@unlink($cachefilepath);
				}
				ob_start();
			}
		}
	}
	
	/**
	 * Ends the static RSS caching.
	 *
	 */
	protected function endRSSCache() {
		$caching = getOption("feed_cache") && !zp_loggedin();
		if($caching) {
			$cachefilepath = SERVERPATH."/cache_html/rss/".$this->getRSSCacheFilename();
			if(!empty($cachefilepath)) {
				mkdir_recursive(SERVERPATH."/cache_html/rss/",FOLDER_MOD);
				$pagecontent = ob_get_contents();
				ob_end_clean();
				if ($fh = @fopen($cachefilepath,"w")) {
					fputs($fh, $pagecontent);
					fclose($fh);
					clearstatcache();
				}
				echo $pagecontent;
			}
		}
	}
	
	/**
	 * Cleans out the RSS cache folder
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	protected function clearRSSCache($cachefolder=NULL) {
		if (is_null($cachefolder)) {
			$cachefolder = "../cache_html/rss/";
		}
		if (is_dir($cachefolder)) {
			$handle = opendir($cachefolder);
			while (false !== ($filename = readdir($handle))) {
				$fullname = $cachefolder . '/' . $filename;
				if (is_dir($fullname) && !(substr($filename, 0, 1) == '.')) {
					if (($filename != '.') && ($filename != '..')) {
						$this->clearRSSCache($fullname);
						rmdir($fullname);
					}
				} else {
					if (file_exists($fullname) && !(substr($filename, 0, 1) == '.')) {
						@chmod($fullname, 0666);
						unlink($fullname);
					}
				}
	
			}
			closedir($handle);
		}
	}
	
	/**
	* Updates the hitcoutner for RSS in the plugin_storage db table.
	* 
	*/
	protected function RSShitcounter() {
		if(!zp_loggedin() && getOption('feed_hitcounter')) {
			$rssuri = $this->getRSSCacheFilename();
			$type = 'rsshitcounter';
			$checkitem = query_single_row("SELECT `data` FROM ".prefix('plugin_storage')." WHERE `aux` = ".db_quote($rssuri)." AND `type` = '".$type."'",true);
			if($checkitem) {
				$hitcount = $checkitem['data']+1;
				query("UPDATE ".prefix('plugin_storage')." SET `data` = ".$hitcount." WHERE `aux` = ".db_quote($rssuri)." AND `type` = '".$type."'",true);
			} else {
				query("INSERT INTO ".prefix('plugin_storage')." (`type`,`aux`,`data`) VALUES ('".$type."',".db_quote($rssuri).",1)",true);
			}
		}
	}
	
	/**
	 * Gets the feed items
	 *
	 * @return array
	 */
	public function getRSSitems() {
		switch($this->feedtype) {
			case 'gallery':
				if ($this->rssmode == "albums") {
					$items = getAlbumStatistic($this->itemnumber,getOption("feed_sortorder_albums"),$this->albumfolder);
				} else {
					$items = getImageStatistic($this->itemnumber,getOption("feed_sortorder"),$this->albumfolder,$this->collection);
				}
				break;
			case 'news':
				switch ($this->newsoption) {
					case "category":
						$items = getLatestNews($this->itemnumber,"none",$this->catlink);
						break;
					case "news":
						$items = getLatestNews($this->itemnumber,"none");
						break;
					case "withimages":
						$items = getLatestNews($this->itemnumber,"with_latest_images_date");
						break;
				}
				break;
				
			case 'comments':
				switch($this->rssmode) {
					case 'gallery':
					case 'album':
					case 'image':
						$type = $this->commentrsstype;
						if($this->commentrsstype == 'gallery') {
							$type = 'all';
						}
						echo $this->itemnumber.$type.$this->id;
						$items = getLatestComments($this->itemnumber,$type,$this->id);
						break;
					case 'zenpage':
					case 'news':
					case 'page':
						$type = $this->commentrsstype;
						if($this->commentrsstype == 'zenpage') {
							$type = 'all';
						}
						if(function_exists('getLatestZenpageComments')) {
							$items = getLatestZenpageComments($this->itemnumber,$type,$this->id);
						}
						break;
					case 'allcomments':
						$type = 'all';
						$items = getLatestComments($this->itemnumber,$type,$this->id);
						$items_zenpage = array();
						if(function_exists('getLatestZenpageComments')) {
							$items_zenpage = getLatestZenpageComments($this->itemnumber,$type,$this->id);
							$items = array_merge($items,$items_zenpage);
							$items = sortMultiArray($items,'id',true);
							$items = array_slice($items,0,$this->itemnumber);
						}
						break;
				}
				break;
		}
		return $items;
	}
	
	/**
	* Gets the feed item data in a gallery feed
	* 
	* @param object $item Object of an image or album
	* @return array
	*/
	protected function getRSSitemGallery($item) {
		if($this->rssmode != "albums") {
			$ext = getSuffix($item->filename);
			$albumobj = $item->getAlbum();
			$itemlink = $this->host.WEBPATH.$this->albumpath.pathurlencode($albumobj->name).$this->imagepath.pathurlencode($item->filename).$this->modrewritesuffix;
			$fullimagelink = $this->host.WEBPATH."/albums/".pathurlencode($albumobj->name)."/".$item->filename;
			$imagefile = "albums/".$albumobj->name."/".$item->filename;
			$thumburl = '<img border="0" src="'.PROTOCOL.'://'.$this->host.$item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.get_language_string(get_language_string($item->get("title"),$this->locale)) .'" /><br />';
			$title = get_language_string($item->get("title"),$this->locale);
			$albumtitle = get_language_string($albumobj->get("title"),$this->locale);
			$datecontent = '<br />Date: '.zpFormattedDate(DATE_FORMAT,$item->get('mtime'));
			if ((($ext == "flv") || ($ext == "mp3") || ($ext == "mp4") ||  ($ext == "3gp") ||  ($ext == "mov")) AND $this->rssmode != "album") {
				$feeditem['desc'] = '<a title="'.html_encode($title).' in '.html_encode(get_language_string($albumobj->get("title"),$this->locale)).'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>' . get_language_string(get_language_string($item->get("desc"),$this->locale)).$datecontent;
			} else { 
				$feeditem['desc'] = '<a title="'.html_encode($title).' in '.html_encode(get_language_string($albumobj->get("title"),$this->locale)).'" href="'.PROTOCOL.'://'.$itemlink.'"><img src="'.PROTOCOL.'://'.$this->host.$item->getThumb().'" alt="'.html_encode($title).'" /></a>' . get_language_string(get_language_string($item->get("desc"),$this->locale)).$datecontent;
			}
		} else {
			$albumobj = new Album(NULL, $item['folder']);
			$totalimages = $albumobj->getNumImages();
			$itemlink = $this->host.WEBPATH.$this->albumpath.pathurlencode($albumobj->name);
			$thumb = $albumobj->getAlbumThumbImage();
			$thumburl = '<img border="0" src="'.$thumb->getCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.html_encode(get_language_string($albumobj->get("title"),$this->locale)) .'" />';
			$title =  get_language_string($albumobj->get("title"),$this->locale);
			if(true || getOption("feed_sortorder_albums") == "latestupdated") {
				$filechangedate = filectime(ALBUM_FOLDER_SERVERPATH.internalToFilesystem($albumobj->name));
				$latestimage = query_single_row("SELECT mtime FROM " . prefix('images'). " WHERE albumid = ".$albumobj->getID() . " AND `show` = 1 ORDER BY id DESC");
				if($latestimage) {
					$count = db_count('images',"WHERE albumid = ".$albumobj->getID() . " AND mtime = ". $latestimage['mtime']);
					if($count == 1) {
						$imagenumber = sprintf(gettext('%s (1 new image)'),$title);
					} else {
						$imagenumber = sprintf(gettext('%1$s (%2$s new images)'),$title,$count);
					}
				} else {
					$imagenumber = $title;
				}
				$feeditem['desc'] = '<a title="'.$title.'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>'.
								'<p>'.html_encode($imagenumber).'</p>'.get_language_string($albumobj->get("desc"),$this->locale).'<br />'.sprintf(gettext("Last update: %s"),zpFormattedDate(DATE_FORMAT,$filechangedate));
			} else {
				if($totalimages == 1) {
					$imagenumber = sprintf(gettext('%s (1 image)'),$title);
				} else {
					$imagenumber = sprintf(gettext('%1$s (%2$s images)'),$title,$totalimages);
				}
				$feeditem['desc'] = '<a title="'.html_encode($title).'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>'.get_language_string($albumitem->get("desc"),$this->locale).'<br />'.sprintf(gettext("Date: %s"),zpFormattedDate(DATE_FORMAT,$albumitem->get('mtime')));
			}
			$ext = getSuffix($thumb->filename);
		}
		// title
		if($this->rssmode != "albums") {
			$feeditem['title'] = sprintf('%1$s (%2$s)', get_language_string($item->get("title"),$this->locale), get_language_string($albumobj->get("title"),$this->locale));
		} else {
			$feeditem['title'] = $imagenumber;
		}
		//link
		$feeditem['link'] = PROTOCOL.'://'.$itemlink;
		
		// enclosure
		$feeditem['enclosure'] = '';
		if(getOption("feed_enclosure") AND $this->rssmode != "albums") {
			$feeditem['enclosure'] = '<enclosure url="'.PROTOCOL.'://'.$fullimagelink.'" type="'.getMimeString($ext).'" length="'.filesize($imagefile).'" />';
		} 
		//category
		if($this->rssmode != "albums") {
			$feeditem['category'] = html_encode(get_language_string($albumobj->get("title"),$this->locale));
		} else {
			$feeditem['category'] = html_encode(get_language_string($albumitem->get("title"),$this->locale));
		}
		//media content
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		if(getOption("feed_mediarss") AND $this->rssmode != "albums") {
			$feeditem['media_content'] = '<media:content url="'.PROTOCOL.'://'.$fullimagelink.'" type="image/jpeg" />';
			$feeditem['media_thumbnail'] = '<media:thumbnail url="'.PROTOCOL.'://'.$fullimagelink.'" width="'.$this->imagesize.'"	height="'.$this->imagesize.'" />';
		}
		//date
		if($this->rssmode != "albums") {
			$feeditem['pubdate'] = date("r",strtotime($item->get('date')));
		} else {
			$feeditem['pubdate'] = date("r",strtotime($albumitem->get('date')));
		}
		return $feeditem;
	}
	
	/**
	* Gets the feed item data in a Zenpage news feed
	* 
	* @param array $item Titlelink a Zenpage article or filename of an image if a combined feed
	* @return array
	*/
	protected function getRSSitemNews($item) {
		$categories = '';
		$feeditem['enclosure'] = '';
		//get the type of the news item
		switch($item['type']) {
			case 'news':
				$obj = new ZenpageNews($item['titlelink']);
				$feeditem['title'] = get_language_string($obj->get('title'),$this->locale);
				$title = get_language_string($obj->get('title'),$this->locale);
				$link = getNewsURL($obj->getTitlelink());
				$count2 = 0;
				$plaincategories = $obj->getCategories();
				foreach($plaincategories as $cat){
					$catobj = new ZenpageCategory($cat['titlelink']);
					$count2++;
					if($count2 != 1) {
						$categories = get_language_string($catobj->get('title'),$this->locale).", ";
					}
					$categories = $categories.get_language_string($catobj->get('title'), $this->locale);
				}
				if(getOption('zenpage_rss_length') == "") {
					// empty value means full content!
					$feeditem['desc'] = get_language_string($obj->get('content'),$this->locale);
				} else {
					$feeditem['desc'] = shortenContent(get_language_string($obj->get('content'),$this->locale),getOption('zenpage_rss_length'), '...');
				}
				break;
			case 'images':
				$albumobj = new Album(NULL,$item['albumname']);
				$obj = newImage($albumobj,$item['titlelink']);
				$categories = get_language_string($albumobj->get('title'),$this->locale);
				$feeditem['title'] = strip_tags(get_language_string($obj->get('title'),$this->locale));
				$title = get_language_string($obj->get('title'),$this->locale);
				$link = $obj->getImageLink();
				$filename = $obj->getFilename();
				$ext = getSuffix($filename);
				$album = $albumobj->getFolder();
				$fullimagelink = $host.WEBPATH."/albums/".$album."/".$filename;
				$imagefile = "albums/".$album."/".$filename;
				if(getOption('zenpage_rss_length') == "") {
					// empty value means full content!
					$content = get_language_string($obj->get('desc'),$this->locale);
				} else {
					$content = shortenContent(get_language_string($obj->get('desc'),$this->locale),getOption('zenpage_rss_length'), '...');
				}
				if(isImagePhoto($obj)) {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).' in '.html_encode($categories).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img border="0" src="'.PROTOCOL.'://'.$this->host.WEBPATH.'/'.ZENFOLDER.'/i.php?a='.$album.'&i='.$filename.'&s='.$this->imagesize.'" alt="'. html_encode($feeditem['title']).'"></a><br />'.$content;
				} else {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).' in '.html_encode($categories).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img src="'.$obj->getThumb().'" alt="'.html_encode($feeditem['title']).'" /></a><br />'.$content;
				}
				if(getOption("feed_enclosure")) {
					$feeditem['enclosure'] = '<enclosure url="'.PROTOCOL.'://'.$fullimagelink.'" type="'.getMimeString($ext).'" length="'.filesize($imagefile).'" />';
				}
				break;
			case 'albums':
				break;
		}
		if(!empty($categories)) {
			$feeditem['category'] = html_encode($categories);
			$feeditem['title'] = html_encode($title).' ('.html_encode($categories).')';
		} 
		$feeditem['link'] = PROTOCOL.'://'.$this->host.$link;
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r",strtotime($item['date']));
		return $feeditem;
	}

	/**
	 * Gets the feed item data in a comments feed
	 *
	 * @param array $item Array of a comment
	 * @return array
	 */
	protected function getRSSitemComments($item) {
		if($item['anon']) {
			$author = "";
		} else {
			$author = " ".gettext("by")." ".$item['name'];
		}
		$imagetag = "";
		$title = '';
		switch($item['type']) {
			case 'images':
				$title = get_language_string($item['title']);
				$imagetag = $imagepath.$item['filename'].$modrewritesuffix;
			case 'albums':
				$album = pathurlencode($item['folder']);
				$feeditem['pubdate'] = date("r",strtotime($item['date']));
				$category = $item['albumtitle'];
				$website =$item['website'];
				if($item['type'] == 'albums') {
					$title = $category;
				} else {
					$title = $category.": ".$title;
				}
				$commentpath = PROTOCOL.'://'.$this->host.WEBPATH.$this->albumpath.$album.$imagetag."#".$item['id'];
				break;
			case 'news':
			case 'pages':
				$album = '';
				$feeditem['pubdate'] = date("r",strtotime($item['date']));
				$category = '';
				$title = get_language_string($item['title']);
				$titlelink = $item['titlelink'];
				$website = $item['website'];
				if(function_exists('getNewsURL')) {
					if ($item['type']=='news') {
						$commentpath = $protocol.'://'.$host.getNewsURL($titlelink)."#".$item['id'];
					} else {
						$commentpath = $protocol.'://'.$host.getPageLinkURL($titlelink)."#".$item['id'];
					}
				} else {
					$commentpath = '';
				}
				break;
		}
		$feeditem['title'] = strip_tags($title.$author);
		$feeditem['link'] = $commentpath;
		$feeditem['desc'] = $comment['comment'];
		return $feeditem;
	}
	
	/**
	* Prints the RSS feed
	*
	*/
	public function printRSSfeed() {
		global $_zp_gallery;
		$this->rssHitcounter();
		$this->startRSSCache();
		header('Content-Type: application/xml');
		?>
		<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
			<channel>
				<title><?php echo $this->channel_title; ?></title>
				<link><?php echo PROTOCOL.'://'.$this->host.WEBPATH; ?></link>
				<atom:link href="<?php echo PROTOCOL; ?>://<?php echo $this->host; ?><?php echo html_encode($_SERVER["REQUEST_URI"]);; ?>" rel="self"	type="application/rss+xml" />
				<description><?php echo strip_tags(get_language_string($_zp_gallery->get('Gallery_description'), $this->locale)); ?></description>
				<language><?php echo $this->locale_xml; ?></language>
				<pubDate><?php echo date("r", time()); ?></pubDate>
				<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
				<docs>http://blogs.law.harvard.edu/tech/rss</docs>
				<generator>Zenphoto RSS Generator</generator>
				<?php 
				$feeditems = $this->getRSSitems();
				foreach($feeditems as $feeditem) {
					switch($this->feedtype) {
						case 'gallery':
							$item = $this->getRSSitemGallery($feeditem);
							break;
						case 'news':
							$item = $this->getRSSitemNews($feeditem);
							break;
						case 'comments':
							$item = getRSSitemComments($feeditem);
							break;
					}
					?>
					<item>
						<title><![CDATA[<?php echo $item['title']; ?>]]></title>
						<link><?php echo $item['link']; ?></link>
						<description><![CDATA[<?php echo $item['desc']; ?>]]></description>
						<?php 
						if(!empty($item_['enclosure'])) { 
							echo $item_['enclosure']; //prints xml as well
						}
						if(!empty($item['category'])) {  
							?>
							<category><![CDATA[<?php echo $item['category']; ?>]]></category>
							<?php
						} 
						if(!empty($item['media_content'])) { 
							echo $item['media_content']; //prints xml as well 
						} 
						if(!empty($item['media_thumbnail'])) { 
							echo $item['media_thumbnail']; //prints xml as well
						} ?>
						<guid><?php echo $item['link']; ?></guid>
						<pubDate><?php echo $item['pubdate'];  ?></pubDate>
					</item>
				<?php } ?>
				</channel>
			</rss>
		<?php
		$this->endRSSCache();
	}
}


?>