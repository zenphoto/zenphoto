<?php
/**
 * search class
 * @package classes
 */

// force UTF-8 Ø


//*************************************************************
//*ZENPHOTO SEARCH ENGINE CLASS *******************************
//*************************************************************

define('EXACT_TAG_MATCH',getOption('exact_tag_match'));
define('SEARCH_DURATION', 3000);
define('SEARCH_CACHE_DURATION', getOption('search_cache_duration'));

class SearchEngine
{
	var $words;
	var $dates;
	protected $whichdates = 'date'; // for zenpage date searches, which date field to search
	var $fieldList;
	var $page;
	var $images;
	var $albums;
	var $dynalbumname;
	var $gallery;
	protected $search_no_albums;	// omit albums
	protected $search_no_images;	// omit albums
	protected $search_no_pages;		// omit pages
	protected $search_no_news;		// omit news
	protected $search_structure;		// relates translatable names to search fields
	protected $iteration = 0;	//	used by apply_filter('search_statistics') to indicate sequential searches of different objects
	protected $processed_search = NULL;
	protected $album_list = NULL;	// list of albums to search
	protected $category_list;			// list of categories for a news search
	protected $searches = NULL;		// remember the criteria for past searches

	/**
	 * Constuctor
	 *
	 * @param bool $dynamic_album set true for dynamic albums (limits the search fields)
	 * @return SearchEngine
	 */
	function __construct($dynamic_album = false) {
		global $_zp_exifvars;
		//image/album fields
		$this->gallery = new Gallery();
		$this->search_structure['title']							= gettext('Title');
		$this->search_structure['desc']								= gettext('Description');
		$this->search_structure['tags']								= gettext('Tags');
		$this->search_structure['filename']						= gettext('File/Folder name');
		$this->search_structure['date']								= gettext('Date');
		$this->search_structure['custom_data']				= gettext('Custom data');
		$this->search_structure['location']						= gettext('Location/Place');
		$this->search_structure['city']								= gettext('City');
		$this->search_structure['state']							= gettext('State');
		$this->search_structure['country']						= gettext('Country');
		$this->search_structure['copyright']					= gettext('Copyright');
		if (getOption('zp_plugin_zenpage') && !$dynamic_album) {//zenpage fields
			$this->search_structure['content']					= gettext('Content');
			$this->search_structure['extracontent']			= gettext('ExtraContent');
			$this->search_structure['author']						= gettext('Author');
			$this->search_structure['lastchangeauthor']	= gettext('Last Editor');
			$this->search_structure['titlelink']				= gettext('TitleLink');
		}
		//metadata fields
		foreach ($_zp_exifvars as $field=>$row) {
			$this->search_structure[strtolower($field)]	= $row[2];
		}

		if (isset($_REQUEST['words'])) {
			$this->words = $_REQUEST['words'];
		} else {
			$this->words = '';
			if (isset($_REQUEST['date'])) {  // words & dates are mutually exclusive
				$this->dates = sanitize($_REQUEST['date'], 3);
				if (isset($_REQUEST['whichdate'])) {
					$this->whichdates = sanitize($_REQUEST['whichdate']);
				}
			} else {
				$this->dates = '';
			}
		}
		$this->fieldList = $this->parseQueryFields();
		$this->album_list = NULL;
		if (isset($_REQUEST['inalbums'])) {
			$list = trim(sanitize($_REQUEST['inalbums'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_albums = true;
						setOption('search_no_albums',1,false);
						break;
					case "1":
						$this->search_no_albums = false;
						setOption('search_no_albums',0,false);
						break;
					default:
						$this->album_list = explode(',',$list);
						break;
				}
			}
		}
		if (isset($_REQUEST['inimages'])) {
			$list = trim(sanitize($_REQUEST['inimages'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_images = true;
						setOption('search_no_images',1,false);
						break;
					case "1":
						$this->search_no_images = false;
						setOption('search_no_images',0,false);
						break;
				}
			}
		}
		if (isset($_REQUEST['inpages'])) {
			$list = trim(sanitize($_REQUEST['inpages'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_pages = true;
						setOption('search_no_pages',1,false);
						break;
				}
			}
		}
		$this->category_list = NULL;
		if (isset($_REQUEST['innews'])) {
			$list = trim(sanitize($_REQUEST['innews'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_news = true;
						setOption('search_no_news',1,false);
						break;
					case "1":
						break;
					default:
						$this->category_list = explode(',',$list);
						break;
				}
			}
		}
		$this->images = NULL;
		$this->albums = NULL;
		$this->searches = array('images'=>NULL, 'albums'=>NULL, 'pages'=>NULL, 'news'=>NULL);
	}

	/**
	 * Returns a list of search fields display names indexed by the search mask
	 *
	 * @return array
	 */
	function getSearchFieldList() {
		$list = array();
		foreach ($this->search_structure as $key=>$display) {
			$list[$display] = $key;
		}
		return $list;
	}

	/**
	 * Returns an array of the enabled search fields
	 *
	 * @return array
	 */
	function allowedSearchFields() {
		$setlist = array();
		$fields = strtolower(getOption('search_fields'));
		if (is_numeric($fields)) {
			$setlist = $this->numericFields($fields);
		} else {
			$list = explode(',',$fields);
			foreach ($this->search_structure as $key=>$display) {
				if (in_array($key,$list)) {
					$setlist[$display] = $key;
				}
			}
		}
		return $setlist;
	}

	/**
	 * converts old style bitmask field spec into field list array
	 *
	 * @param bit $fields
	 * @return array
	 */
	protected function numericFields($fields) {
		if ($fields==0) $fields = 0x0fff;
		if ($fields & 0x01) $list[$this->search_structure['title']] = 'title';
		if ($fields & 0x02) $list[$this->search_structure['desc']] = 'desc';
		if ($fields & 0x04) $list[$this->search_structure['tags']] = 'tags';
		if ($fields & 0x08) $list[$this->search_structure['filename']] = 'filename';
		return $list;
	}

	/**
	 * creates a search query from the search words
	 *
	 * @param bool $long set to false to omit albumname and page parts
	 *
	 * @return string
	 */
	function getSearchParams($long=true) {
		global $_zp_page;
		$r = '';
		$w = urlencode(trim($this->codifySearchString()));
		if (!empty($w)) { $r .= '&words=' . $w; }
		$d = trim($this->dates);
		if (!empty($d)) {
			$r .= '&date=' . $d;
			$d = trim($this->whichdates);
			if ($d != 'date') {
				$r.= '&whichdates=' . $d;
			}
		}
		$r .= $this->getSearchFieldsText($this->fieldList);
		if ($long) {
			$a = $this->dynalbumname;
			if ($a) { $r .= '&albumname=' . $a; }
			if (empty($this->album_list)) {
				if ($this->search_no_albums) {
					$r .= '&inalbums=0';
				}
			} else {
				$r .= '&inalbums='.implode(',', $this->album_list);
			}
			if ($this->search_no_images) {
				$r .= '&inimages=0';
			}
			if ($this->search_no_pages) {
				$r .= '&inpages=0';
			}
			if (empty($this->categories)) {
				if ($this->search_no_news) {
					$r .= '&innews=0';
				}
			} else {
				$r .= '&innews='.implode(',', $this->categories);
			}
			if ($_zp_page > 1) {
				$this->page = $_zp_page;
				$r .= '&page=' . $_zp_page;
			}
		}
		return $r;
	}

	/**
	 * Returns the "searchstring" element of a query parameter set
	 *
	 * @param array $fields the fields required
	 * @param string $param the query parameter (possibly with the intro character
	 * @return string
	 */
	function getSearchFieldsText($fields, $param='&searchfields=') {
		$default = $this->allowedSearchFields();
		$diff = array_diff($default, $fields);
		if (count($diff)>0) {
			foreach ($fields as $field) {
				$param .= $field.',';
			}
			return substr($param,0,-1);
		}
		return '';
	}

	/**
	 * Parses and stores a search string
	 * NOTE!! this function assumes that the 'words' part of the list has been urlencoded!!!
	 *
	 * @param string $paramstr the string containing the search words
	 */
	function setSearchParams($paramstr) {
		$params = explode('&', $paramstr);
		foreach ($params as $param) {
			$e = strpos($param, '=');
			$p = substr($param, 0, $e);
			$v = substr($param, $e + 1);
			switch($p) {
				case 'words':
					$this->words = urldecode($v);
					break;
				case 'date':
					$this->dates = $v;
					break;
				case 'whichdates':
					$this->whichdates = $v;
					break;
				case 'searchfields':
					if (is_numeric($v)) {
						$this->fieldList = $this->numericFields($v);
					} else {
						$this->fieldList = array();
						$list = explode(',',strtolower($v));
						foreach ($this->search_structure as $key=>$row) {
							if (in_array(strtolower($key),$list)) {
								$this->fieldList[] = $key;
							}
						}
					}
					break;
				case 'page':
					$this->page = $v;
					break;
				case 'albumname':
					$this->dynalbumname = $v;
					break;
				case 'inimages':
					if (strlen($v) > 0) {
						switch ($v) {
							case "0":
								$this->search_no_images = true;
								setOption('search_no_images',1,false);
								break;
							case "1":
								$this->search_no_images = false;
								setOption('search_no_images',0,false);
								break;
						}
					}
					break;
				case 'inalbums':
					if (strlen($v) > 0) {
						switch ($v) {
							case "0":
								$this->search_no_albums = true;
								setOption('search_no_albums',1,false);
								break;
							case "1":
								$this->search_no_albums = false;
								setOption('search_no_albums',0,false);
								break;
							default:
								$this->album_list = explode(',',$v);
								break;
						}
					}
				break;
			}
		}
		if (!empty($this->words)) {
			$this->dates = ''; // words and dates are mutually exclusive
		}
	}

	/**
	 * Returns the search words variable
	 *
	 * @return string
	 */
	function getSearchWords() {
		return $this->words;
	}

	/**
	 * Returns the search dates variable
	 *
	 * @return string
	 */
	function getSearchDate() {
		return $this->dates;
	}

	/**
	 * Returns the search fields variable
	 *
	 * @param bool $array set to true to return the fields as array elements. Otherwise
	 * a comma delimited string is returned
	 *
	 * @return mixed
	 */
	function getSearchFields($array=false) {
		if ($array) return $this->fieldList;
		return implode(',',$this->fieldList);
	}

	/**
	 * Parses a search string
	 * Items within quotations are treated as atomic
	 * AND, OR and NOT are converted to &, |, and !
	 *
	 * Returns an array of search elements
	 *
	 * @return array
	 */
	function getSearchString() {
		if ($this->processed_search) {
			return $this->processed_search;
		}
		$searchstring = trim($this->words);
		$space_is = getOption('search_space_is');
		$opChars = array ('&'=>1, '|'=>1, '!'=>1, ','=>1, '('=>2);
		if ($space_is) {
			$opChars[' '] = 1;
		}
		$c1 = ' ';
		$result = array();
		$target = "";
		$i = 0;
		do {
			$c = substr($searchstring, $i, 1);
			$op = '';
			switch ($c) {
				case "'":
				case '"':
				case '`':
					$j = strpos(str_replace('\\'.$c, '__', $searchstring), $c, $i+1);
					if ($j !== false) {
						$target .= stripcslashes(substr($searchstring, $i+1, $j-$i-1));
						$i = $j;
					} else {
						$target .= $c;
					}
					$c1 = $c;
					break;
				case ' ':
					$j = $i+1;
					while ($j < strlen($searchstring) && substr($searchstring,$j,1)==' ') {
						$j++;
					}
					switch ($space_is) {
						case 'OR':
						case 'AND':
							if ($j < strlen($searchstring)) {
								$c3 = substr($searchstring,$j,1);
								if (array_key_exists($c3,$opChars) && $opChars[$c3] == 1) {
									$nextop = $c3 != '!';
								} else if (substr($searchstring.' ', $j, 4)=='AND ') {
									$nextop = true;
								} else if (substr($searchstring.' ', $j, 3)=='OR ') {
									$nextop = true;
								} else {
									$nextop = false;
								}
							}
							if (!$nextop) {
								if (!empty($target)) {
									$r = trim($target);
									if (!empty($r)) {
										$last = $result[] = $r;
										$target = '';
									}
								}
								if ($space_is=='AND') {
									$c1 = '&';
								} else {
									$c1 = '|';
								}
								$target = '';
								$last = $result[] = $c1;
							}
							break;
						default:
							$c1 = $c;
							$target .= str_pad('',$j-$i);
							break;
					}
					$i = $j-1;
					break;
				case ',':
					if (!empty($target)) {
						$r = trim($target);
						if (!empty($r)) {
							switch ($r) {
								case 'AND':
									$r = '&';
									break;
								case 'OR':
									$r = '|';
									break;
								case 'NOT':
									$r = '!';
									break;
							}
							$last = $result[] = $r;
							$target = '';
						}
					}
					$c2 = substr($searchstring, $i+1, 1);
					switch ($c2) {
						case 'A':
							if (substr($searchstring.' ', $i+1, 4) == 'AND ') $c2 = '&';
							break;
						case 'O':
							if (substr($searchstring.' ', $i+1, 3) == 'OR ') $c2 = '|';
							break;
						case 'N':
							if (substr($searchstring.' ', $i+1, 4) == 'NOT ') $c2 = '!';
							break;
					}
					if (!((isset($opChars[$c2])&&$opChars[$c2]==1) || (isset($opChars[$last])&&$opChars[$last]==1))) {
						$last = $result[] = '|';
						$c1 = $c;
					}
					break;
				case '&':
				case '|':
				case '!':
				case '(':
				case ')':
					if (!empty($target)) {
						$r = trim($target);
						if (!empty($r)) {
							$last = $result[] = $r;
							$target = '';
						}
					}
					$c1 = $c;
					$target = '';
					$last = $result[] = $c;
					$j = $i+1;
					break;
				case 'A':
					if (substr($searchstring.' ', $i, 4) == 'AND ') {
						$op = '&';
						$skip = 3;
					}
				case 'O':
					if (substr($searchstring.' ', $i, 3) == 'OR ') {
						$op = '|';
						$skip = 2;
					}
				case 'N':
					if (substr($searchstring.' ' , $i, 4) == 'NOT ') {
						$op = '!';
						$skip = 3;
					}
					if ($op) {
						if (!empty($target)) {
							$r = trim($target);
							if (!empty($r)) {
								$last = $result[] = $r;
								$target = '';
							}
						}
						$c1 = $op;
						$target = '';
						$last = $result[] = $op;
						$j = $i+$skip;
						while ($j < strlen($searchstring) && substr($searchstring,$j,1)==' ') {
							$j++;
						}
						$i=$j-1;
					} else {
						$c1 = $c;
						$target .= $c;
					}
					break;

				default:
					$c1 = $c;
					$target .= $c;
					break;
			}
		} while ($i++ < strlen($searchstring));
		if (!empty($target)) { $last = $result[] = trim($target); }
		$lasttoken = '';
		foreach ($result as $key=>$token) {
			if ($token=='|' && $lasttoken=='|') { // remove redundant OR ops
				unset($result[$key]);
			}
			$lasttoken = $token;
		}
		if (array_key_exists($lasttoken,$opChars) && $opChars[$lasttoken] == 1) {
			array_pop($result);
		}
		$this->processed_search = zp_apply_filter('search_criteria',$result);
		return $this->processed_search;
	}

	/**
	 * recodes the search words replacing the boolean operators with text versions
	 *
	 * @param string $quote how to represent quoted strings
	 *
	 * @return string
	 *
	 */
	function codifySearchString($quote='"') {
		$opChars = array ('('=>2, '&'=>1, '|'=>1, '!'=>1, ','=>1);
		$searchstring = $this->getSearchString();
		$sanitizedwords = '';
		if (is_array($searchstring)) {
			foreach($searchstring as $singlesearchstring){
				switch ($singlesearchstring) {
					case '&':
						$sanitizedwords .= " AND ";
						break;
					case '|':
						$sanitizedwords .= " OR ";
						break;
					case '!':
						$sanitizedwords .= " NOT ";
						break;
					case '(':
					case ')':
						$sanitizedwords .= "$singlesearchstring";
						break;
					default:
						$sanitizedword = sanitize($singlesearchstring, 3);
						$setQuote = $sanitizedword != $singlesearchstring;
						if (!$setQuote) {
							foreach ($opChars as $char => $value) {
								if ((strpos($singlesearchstring, $char) !== false)) {
									$setQuote = true;
									break;
								}
							}
						}
						if ($setQuote) {
							$sanitizedwords .= $quote.$singlesearchstring.$quote;
						} else {
							$sanitizedwords .= ' '.sanitize($singlesearchstring, 3).' ';
						}
						break;
				}
			}
		}

		$sanitizedwords = trim(str_replace(array('   ','  ',),' ', $sanitizedwords));
		$sanitizedwords = trim(str_replace('( ','(', $sanitizedwords));
		$sanitizedwords = trim(str_replace(' )',')', $sanitizedwords));
		return $sanitizedwords;
	}

	/**
	 * Returns the number of albums found in a search
	 *
	 * @return int
	 */
	function getNumAlbums() {
		if (is_null($this->albums)) {
			$this->getAlbums(0, NULL, NULL, false);
		}
		return count($this->albums);
	}

	/**
	 * Returns the set of fields from the url query/post
	 * @return int
	 * @since 1.1.3
	 */
	function parseQueryFields() {
		$fields = array();
		if (isset($_REQUEST['searchfields'])) {
			$fs = sanitize($_REQUEST['searchfields']);
			if (is_numeric($fs)) {
				$fields = array_flip($this->numericFields($fs));
			} else {
				$fields = explode(',',$fs);
			}
		} else {
			foreach ($_REQUEST as $key=>$value) {
				if (strpos($key, 'SEARCH_') !== false) {
					$fields[substr($key, 7)] = $value;
				}
			}
		}
		return $fields;
	}

	/**
	 *
	 * Returns an array of News article IDs belonging to the search categories
	 */
	protected function subsetNewsCategories() {
		global $_zp_zenpage;
		if (!is_array($this->category_list)) return false;
		$cat = '';
		$list = $_zp_zenpage->getAllCategories();
		foreach ($list as $category) {
			if (in_array($category['title'], $this->category_list)) {
				$catobj = new ZenpageCategory($category['titlelink']);
				$cat .= ' `cat_id`='.$catobj->get('id').' OR';
				$subcats = $catobj->getSubCategories();
				if($subcats) {
					foreach($subcats as $subcat) {
						$catobj = new ZenpageCategory($subcat);
						$cat .= ' `cat_id`='.$catobj->get('id').' OR';
					}
				}
			}
		}
		$sql = 'SELECT DISTINCT `news_id` FROM '.prefix('news2cat').' WHERE '.substr($cat,0,-3);
		$result = query_full_array($sql);
		$list = array();
		foreach ($result as $row) {
			$list[] = $row['news_id'];
		}
		return $list;
	}

	/**
	 * Takes a list of IDs and makes a where clause
	 *
	 * @param array $idlist list of IDs for a where clause
	 */
	protected function compressedIDList($idlist) {
		asort($idlist);
		return '`id` IN ('.implode(',',$idlist).')';
	}

	/**
	 * returns the results of a date search
	 * @param string $searchstring the search target
	 * @param string $searchdate the date target
	 * @param string $tbl the database table to search
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @return string
	 * @since 1.1.3
	 */
	function searchDate($searchstring, $searchdate, $tbl, $sorttype, $sortdirection, $whichdate='date') {
		global $_zp_current_album;
		$sql = 'SELECT DISTINCT `id`, `show`,`title`';
		switch ($tbl) {
			case 'pages':
			case 'news':
				$sql .= ',`titlelink` ';
				break;
			case 'albums':
				$sql .= ",`desc`,`folder` ";
				break;
			default:
				$sql .= ",`desc`,`albumid`,`filename`,`location`,`city`,`state`,`country` ";
				break;
		}
		$sql .= "FROM ".prefix($tbl)." WHERE ";
		if(!zp_loggedin()) { $sql .= "`show` = 1 AND ("; }

		if (!empty($searchdate)) {
			if ($searchdate == "0000-00") {
				$sql .= "`$whichdate`=\"0000-00-00 00:00:00\"";
			} else {
				$datesize = sizeof(explode('-', $searchdate));
				// search by day
				if ($datesize == 3)	{
					$d1 = $searchdate." 00:00:00";
					$d2 = $searchdate." 23:59:59";
					$sql .= "`$whichdate` >= \"$d1\" AND `$whichdate` < \"$d2\"";
				}
				// search by month
				else if ($datesize == 2) {
					$d1 = $searchdate."-01 00:00:00";
					$d = strtotime($d1);
					$d = strtotime('+ 1 month', $d);
					$d2 = substr(date('Y-m-d H:m:s', $d), 0, 7) . "-01 00:00:00";
					$sql .= "`$whichdate` >= \"$d1\" AND `$whichdate` < \"$d2\"";
				}
			}
		}
		if(!zp_loggedin()) { $sql .= ")"; }

		switch ($tbl) {
			case 'news':
				if (empty($sorttype)) {
					$key = '`date` DESC';
				} else {
					$key = trim('`'.$sorttype.'`'.' '.$sortdirection);
				}
				break;
			case 'pages':
				$key = '`sort_order`';
				break;
			case 'albums':
				if (is_null($sorttype)) {
					if (empty($this->dynalbumname)) {
						$key = lookupSortKey($this->gallery->getSortType(), 'sort_order', 'folder');
						if ($key != '`sort_order`') {
							if ($this->gallery->getSortDirection()) {
								$key .= " DESC";
							}
						}
					} else {
						$gallery = new Gallery();
						$album = new Album($gallery, $this->dynalbumname);
						$key = $album->getAlbumSortKey();
						if ($key != '`sort_order`' && $key != 'RAND()') {
							if ($album->getSortDirection('album')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					$sorttype = lookupSortKey($sorttype, 'sort_order', 'folder');
					$key = trim($sorttype.' '.$sortdirection);
				}
				break;
			default:
				$hidealbums = getNotViewableAlbums();
				if (!is_null($hidealbums)) {
					foreach ($hidealbums as $id) {
						$sql .= ' AND `albumid`!='.$id;
					}
				}
				if (is_null($sorttype)) {
					if (empty($this->dynalbumname)) {
						$key = lookupSortKey(IMAGE_SORT_TYPE, 'filename', 'filename');
						if ($key != '`sort_order`') {
							if (IMAGE_SORT_DIRECTION) {
								$key .= " DESC";
							}
						}
					} else {
						$gallery = new Gallery();
						$album = new Album($gallery, $this->dynalbumname);
						$key = $album->getImageSortKey();
						if ($key != '`sort_order`' && $key != 'RAND()') {
							if ($album->getSortDirection('image')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					$sorttype = lookupSortKey($sorttype, 'filename', 'filename');
					$key = trim($sorttype.' '.$sortdirection);
				}
				break;
		}
		$sql .= " ORDER BY ".$key;
		$result = query_full_array($sql);
		if (!$result) return array();
		return $result;
	}

	/**
	 * Searches the table for tags
	 * Returns an array of database records.
	 *
	 * @param array $searchstring
	 * @param string $tbl set DB table name to be searched
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @return array
	 */
	protected function searchFieldsAndTags($searchstring, $tbl, $sorttype, $sortdirection) {
		$allIDs = null;
		$idlist = array();
		$exact = EXACT_TAG_MATCH;

		// create an array of [tag, objectid] pairs for tags
		$tag_objects = array();
		$fields = $this->fieldList;
		if (count($fields)==0) { // then use the default ones
			$fields = $this->allowedSearchFields();
		}
		foreach ($fields as $key=>$field) {
			if (strtolower($field) == 'tags') {
				unset($fields[$key]);
				$tagsql = 'SELECT t.`name`, o.`objectid` FROM '.prefix('tags').' AS t, '.prefix('obj_to_tag').' AS o WHERE t.`id`=o.`tagid` AND o.`type`="'.$tbl.'" AND (';
				foreach($searchstring as $singlesearchstring){
					switch ($singlesearchstring) {
						case '&':
						case '!':
						case '|':
						case '(':
						case ')':
							break;
						default:
							$targetfound = true;
							if ($exact) {
								$tagsql .= '`name` = '.db_quote($singlesearchstring).' OR ';
							} else {
								$tagsql .= '`name` LIKE '.db_quote('%'.$singlesearchstring.'%').' OR ';
							}
					}
				}
				$tagsql = substr($tagsql, 0, strlen($tagsql)-4).') ORDER BY t.`id`';
				$objects = query_full_array($tagsql, false);
				if (is_array($objects)) {
					$tag_objects = $objects;
				}
				break;
			}
		}

		// create an array of [name, objectid] pairs for the search fields.
		$field_objects = array();
		if (count($fields)>0) {
			$columns = array();
			$dbfields = db_list_fields($tbl);
			if (is_array($dbfields)) {
				foreach ($dbfields as $row) {
					$columns[] = strtolower($row['Field']);
				}
			}
			foreach($searchstring as $singlesearchstring){
				switch ($singlesearchstring) {
					case '&':
					case '!':
					case '|':
					case '(':
					case ')':
						break;
					default:
						$targetfound = true;
						query('SET @serachtarget='.db_quote($singlesearchstring));
						$fieldsql = '';
						foreach ($fields as $fieldname) {
							if ($tbl=='albums' && $fieldname=='filename') {
								$fieldname = 'folder';
							} else {
								$fieldname = strtolower($fieldname);
							}
							if ($fieldname && in_array($fieldname, $columns)) {
								$fieldsql .= ' `'.$fieldname.'` LIKE '.db_quote('%'.$singlesearchstring.'%').' OR ';
							}
						}
						if (!empty($fieldsql)) {
							$fieldsql = substr($fieldsql, 0, strlen($fieldsql)-4).') ORDER BY `id`';
							$sql = 'SELECT @serachtarget AS name, `id` AS `objectid` FROM '.prefix($tbl).' WHERE ('.$fieldsql;
							$objects = query_full_array($sql, false);
							if (is_array($objects)) {
								$field_objects = array_merge($field_objects, $objects);
							}
						}
				}
			}
		}

		$objects = array_merge($tag_objects, $field_objects);
		if (count($objects) != 0) {
			$tagid = '';
			$taglist = array();

			foreach ($objects as $object) {
				$tagid = strtolower($object['name']);
				if (!isset($taglist[$tagid]) || !is_array($taglist[$tagid])) {
					$taglist[$tagid] = array();
				}
				$taglist[$tagid][] = $object['objectid'];
			}
			$op = '';
			$idstack = array();
			$opstack = array();
			while (count($searchstring) > 0) {
				$singlesearchstring = array_shift($searchstring);
				switch ($singlesearchstring) {
					case '&':
					case '!':
					case '|':
						$op = $op.$singlesearchstring;
						break;
					case '(':
						array_push($idstack, $idlist);
						array_push($opstack, $op);
						$idlist = array();
						$op = '';
						break;
					case ')':
						$objectid = $idlist;
						$idlist = array_pop($idstack);
						$op = array_pop($opstack);
						switch ($op) {
							case '&':
								if (is_array($objectid)) {
									$idlist = array_intersect($idlist, $objectid);
								} else {
									$idlist = array();
								}
								break;
							case '!':
								break; // Paren followed by NOT is nonsensical?
							case '&!':
								if (is_array($objectid)) {
									$idlist = array_diff($idlist, $objectid);
								}
								break;
							case '';
							case '|':
								if (is_array($objectid)) {
									$idlist = array_merge($idlist, $objectid);
								}
								break;
						}
						$op = '';
						break;
							default:
								$lookfor = strtolower($singlesearchstring);
								$objectid = NULL;
								foreach ($taglist as $key => $objlist) {
									if (($exact && $lookfor == $key) || (!$exact && preg_match('%'.$lookfor.'%', $key))) {
										if (is_array($objectid)) {
											$objectid = array_merge($objectid, $objlist);
										} else {
											$objectid = $objlist;
										}
									}
								}
								switch ($op) {
									case '&':
										if (is_array($objectid)) {
											$idlist = array_intersect($idlist, $objectid);
										} else {
											$idlist = array();
										}
										break;
									case '!':
										if (is_null($allIDs)) {
											$allIDs = array();
											$result = query_full_array("SELECT `id` FROM ".prefix($tbl));
											if (is_array($result)) {
												foreach ($result as $row) {
													$allIDs[] = $row['id'];
												}
											}
										}
										if (is_array($objectid)) {
											$idlist = array_merge($idlist, array_diff($allIDs, $objectid));
										}
										break;
									case '&!':
										if (is_array($objectid)) {
											$idlist = array_diff($idlist, $objectid);
										}
										break;
									case '';
									case '|':
										if (is_array($objectid)) {
											$idlist = array_merge($idlist, $objectid);
										}
										break;
								}
								$idlist = array_unique($idlist);
								$op = '';
								break;
				}
				$idlist = array_unique($idlist);
			}
		}
		if (count($idlist)==0) {return NULL; }

		$sql = 'SELECT DISTINCT `id`,`show`,';
		switch ($tbl) {
			case 'pages':
			case 'news':
				$sql .= '`titlelink` ';
				break;
			case 'albums':
				$sql .= "`folder` ";
				break;
			default:
				$sql .= "`albumid`,`filename` ";
				break;
		}

		switch ($tbl) {
			case 'news':
				if (is_array($this->category_list)) {
					$news_list = $this->subsetNewsCategories();
					$idlist = array_intersect($news_list,$idlist);
					if (count($idlist)==0) {return NULL; }
				}
				if (empty($sorttype)) {
					$key = '`date` DESC';
				} else {
					$key = trim('`'.$sorttype.'` '.$sortdirection);
				}
				break;
			case 'pages':
				$key = '`sort_order`';
				break;
			case 'albums':
				if (is_null($sorttype)) {
					if (empty($this->dynalbumname)) {
						$key = lookupSortKey($this->gallery->getSortType(), 'sort_order', 'folder');
						if ($this->gallery->getSortDirection()) { $key .= " DESC"; }
					} else {
						$gallery = new Gallery();
						$album = new Album($gallery, $this->dynalbumname);
						$key = $album->getAlbumSortKey();
						if ($key != '`sort_order`' && $key != 'RAND()') {
							if ($album->getSortDirection('album')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					$sorttype = lookupSortKey($sorttype, 'sort_order', 'folder');
					$key = trim($sorttype.' '.$sortdirection);
				}
				break;
			default:
				if (is_null($sorttype)) {
					if (empty($this->dynalbumname)) {
						$key = lookupSortKey(IMAGE_SORT_TYPE, 'filename', 'filename');
						if (IMAGE_SORT_DIRECTION) { $key .= " DESC"; }
					} else {
						$gallery = new Gallery();
						$album = new Album($gallery, $this->dynalbumname);
						$key = $album->getImageSortKey();
						if ($key != '`sort_order`') {
							if ($album->getSortDirection('image')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					$sorttype = lookupSortKey($sorttype, 'filename', 'filename');
					$key = trim($sorttype.' '.$sortdirection);
				}
				break;
		}

		$sql .= "FROM ".prefix($tbl)." WHERE ";
		if(!zp_loggedin()) {
			$sql .= "`show` = 1 AND ";
		}
		$sql .= '('.$this->compressedIDList($idlist).')';
		$sql .= " ORDER BY ".$key;
		return $sql;
	}

	/**
	 * Returns an array of albums found in the search
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getSearchAlbums($sorttype, $sortdirection, $mine=NULL) {
		if (getOption('search_no_albums') || $this->search_no_albums) return array();
		$albums = array();
		$searchstring = $this->getSearchString();
		if (empty($searchstring)) { return $albums; } // nothing to find
		$criteria = $this->getCacheTag('albums',serialize($searchstring), $sorttype.' '.$sortdirection);
		if ($criteria == $this->searches['albums']) {
			return $this->albums;
		}
		$albums = $this->getCachedSearch($criteria);
		if (is_null($albums)) {
			$search_query = $this->searchFieldsAndTags($searchstring, 'albums', $sorttype, $sortdirection);
			$search_result = query($search_query);
			if ($search_result) {
				while ($row = db_fetch_assoc($search_result)) {
					$albumname = $row['folder'];
					if ($albumname != $this->dynalbumname) {
						if (file_exists(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($albumname))) {
							$album = new Album(new gallery(), $albumname);
							if ($mine || is_null($mine) && ($album->isMyItem(LIST_RIGHTS) || checkAlbumPassword($albumname) && $row['show'])) {
								if (empty($this->album_list) || in_array($albumname, $this->album_list)) {
									$albums[] = $albumname;
								}
							}
						}
					}
				}
			}
			zp_apply_filter('search_statistics',$searchstring, 'albums', !empty($albums), $this->dynalbumname, $this->iteration++);
			$this->cacheSearch($criteria,$albums);
		}
		$this->albums = $albums;
		$this->searches['images'] = $criteria;
		return $albums;
	}

	/**
	 * Returns an array of album names found in the search.
	 * If $page is not zero, it returns the current page's albums
	 *
	 * @param int $page the page number we are on
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @param bool $care set to false if the order of the albums does not matter
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	function getAlbums($page=0, $sorttype=NULL, $sortdirection=NULL, $care=true, $mine=NULL) {
		if (is_null($this->albums) || $care && $sorttype.$sortdirection !== $this->lastsubalbumsort) {
			$this->albums = $this->getSearchAlbums($sorttype, $sortdirection, $mine);
			$this->lastsubalbumsort = $sorttype.$sortdirection;
		}
		if ($page == 0) {
			return $this->albums;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			return array_slice($this->albums, $albums_per_page*($page-1), $albums_per_page);
		}
	}

	/**
	 * Returns the index of the album within the search albums
	 *
	 * @param string $curalbum The album sought
	 * @return int
	 */
	function getAlbumIndex($curalbum) {
		$albums = $this->getAlbums(0);
		return array_search($curalbum, $albums);
	}

	/**
	 * Returns the album following the current one
	 *
	 * @param string $curalbum the name of the current album
	 * @return object
	 */
	function getNextAlbum($curalbum) {
		$albums = $this->getAlbums(0);
		$inx = array_search($curalbum, $albums)+1;
		if ($inx >= 0 && $inx < count($albums)) {
			$gallery = new Gallery();
			return new Album($gallery, $albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the album preceding the current one
	 *
	 * @param string $curalbum the name of the current album
	 * @return object
	 */
	function getPrevAlbum($curalbum) {
		$albums = $this->getAlbums(0);
		$inx = array_search($curalbum, $albums)-1;
		if ($inx >= 0 && $inx < count($albums)) {
			$gallery = new Gallery();
			return new Album($gallery, $albums[$inx]);
		}
		return null;
	}

	/**
	 * Returns the number of images found in the search
	 *
	 * @return int
	 */
	function getNumImages() {
		if (is_null($this->images)) {
			$this->getImages(0);
		}
		return count($this->images);
	}

	/**
	 * Returns an array of image names found in the search
	 *
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @param bool $mine set true/false to overried ownership
	 * @return array
	 */
	function getSearchImages($sorttype, $sortdirection, $mine=NULL) {
		if (getOption('search_no_images') || $this->search_no_images) {
			$this->images = array();
			return $this->images;
		}
		$searchstring = $this->getSearchString();
		$searchdate = $this->dates;
		if (empty($searchstring) && empty($searchdate)) {	 // nothing to find
			$this->images = array();
			return $this->images;
		}
		$criteria = $this->getCacheTag('images',($searchstring).' '.$searchdate, $sorttype.' '.$sortdirection);
		if ($criteria == $this->searches['images']) {
			return $this->images;
		}
		$images = $this->getCachedSearch($criteria);
		if (is_null($images)) {
			if (empty($searchdate)) {
				$search_query = $this->searchFieldsAndTags($searchstring, 'images', $sorttype, $sortdirection);
			} else {
				$search_query = $this->SearchDate($searchstring, $searchdate, 'images', $sorttype, $sortdirection);
			}
			$search_result = query($search_query);
			if ($search_result) {
				$albums_seen = $images = array();
				while ($row = db_fetch_assoc($search_result)) {
					$albumid = $row['albumid'];
					if (array_key_exists($albumid, $albums_seen)) {
						$album = $albums_seen[$albumid];
					} else {
						$query = "SELECT folder,`show` FROM ".prefix('albums')." WHERE id = $albumid";
						$row2 = query_single_row($query); // id is unique
						$albumname = $row2['folder'];
						$allow = false;
						$album = new Album(new gallery(), $albumname);
						if ($mine || is_null($mine) && ($album->isMyItem(LIST_RIGHTS) || checkAlbumPassword($albumname) && $album->getShow())) {
							$allow = empty($this->album_list) || in_array($albumname, $this->album_list);
						}
						$albums_seen[$albumid] = $album = array('allow'=>$allow,'folder'=>$albumname,'localpath'=>ALBUM_FOLDER_SERVERPATH.internalToFilesystem($albumname).'/');
					}
					if ($album['allow']) {
						if (file_exists($album['localpath'].internalToFilesystem($row['filename']))) {	//	still exists
							$images[] = array('filename' => $row['filename'], 'folder' => $album['folder']);
						}
					}
				}
			}
			if (empty($searchdate)) {
				zp_apply_filter('search_statistics',$searchstring, 'images', !empty($images), $this->dynalbumname, $this->iteration++);
			}
			$this->cacheSearch($criteria,$images);
		}
		$this->images = $images;
		$this->searches['images'] = $criteria;
		return $images;
	}

	/**
	 * Returns an array of images found in the search
	 * It will return a "page's worth" if $page is non zero
	 *
	 * @param int $page the page number desired
	 * @param int $firstPageCount count of images that go on the album/image transition page
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @param bool $mine set true/false to overried ownership
	 * @return array
	 */
	function getImages($page=0, $firstPageCount=0, $sorttype=NULL, $sortdirection=NULL, $mine=NULL) {
		$this->getSearchImages($sorttype, $sortdirection, $mine);
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
			$slice = array_slice($this->images, $pageStart, $images_per_page);
			return $slice;
		}
	}

	/**
	 * Returns the index of this image in the search images
	 *
	 * @param string $album The folder name of the image
	 * @param string $filename the filename of the image
	 * @return int
	 */
	function getImageIndex($album, $filename) {
		$this->getSearchImages(NULL, NULL);
		$images = $this->getImages();
		$c = 0;
		foreach($images as $image) {
			if (($album == $image['folder']) && ($filename == $image['filename'])) {
				return $c;
			}
			$c++;
		}
		return false;
	}
	/**
	 * Returns a specific image
	 *
	 * @param int $index the index of the image desired
	 * @return object
	 */
	function getImage($index) {
		global $_zp_gallery;
		if (!is_null($this->images)) {
			$this->getImages();
		}
		if ($index >= 0 && $index < $this->getNumImages()) {
			$img = $this->images[$index];
			return newImage(new Album($_zp_gallery, $img['folder']), $img['filename']);
		}
		return false;
	}

	/**
	 *
	 * return the list of albums found
	 */
	function getAlbumList() {
		return $this->album_list;
	}

	/**
	 *
	 * return the list of categories found
	 */
	function getCategoryList() {
		return $this->category_list;
	}

	/**
	 * Returns a list of Pages Titlelinks found in the search
	 *
	 * @return array
	 */
	function getSearchPages() {
		$result = array();
		if (getOption('zp_plugin_zenpage')) {
			if (getOption('search_no_pages') || $this->search_no_pages) return array();
			$searchstring = $this->getSearchString();
			$searchdate = $this->dates;
			if (empty($searchstring) && empty($searchdate)) { return array(); } // nothing to find
			if (empty($searchdate)) {
				$search_query = $this->searchFieldsAndTags($searchstring, 'pages', false, false);
				$search_result = query($search_query);
				zp_apply_filter('search_statistics',$searchstring, 'pages', !$search_result, false, $this->iteration++);
			} else {
				$search_query = $this->SearchDate($searchstring, $searchdate, 'pages', false, false);
				$search_result = query($search_query);
			}
			if ($search_result) {
				while ($row = db_fetch_assoc($search_result)) {
					$result[] = $row['titlelink'];
				}
			}
		}
		return $result;
	}

	/**
	 * Returns a list of News Titlelinks found in the search
	 *
	 * @param string $sortorder "date" for sorting by date (default)
	 * 													"title" for sorting by title
	 * @param string $sortdirection "desc" (default) for descending sort order
	 * 													    "asc" for ascending sort order
	 *
	 * @return array
	 */
	function getSearchNews($sortorder="date", $sortdirection="desc") {
		$result = array();
		if (getOption('zp_plugin_zenpage')) {
			if (getOption('search_no_news') || $this->search_no_news) return array();
			$searchstring = $this->getSearchString();
			$searchdate = $this->dates;
			if (empty($searchstring) && empty($searchdate)) { return array(); } // nothing to find
			if (empty($searchdate)) {
				$search_query = $this->searchFieldsAndTags($searchstring, 'news', $sortorder, $sortdirection);
				zp_apply_filter('search_statistics',$searchstring, 'news', !empty($search_results), false, $this->iteration++);
			} else {
				$search_query = $this->SearchDate($searchstring, $searchdate, 'news', $sortorder, $sortdirection,$this->whichdates);
			}
			$search_result = query($search_query);
			if ($search_result) {
				while ($row = db_fetch_assoc($search_result)) {
					$result[] = array('id'=>$row['id'],'titlelink'=>$row['titlelink']);
				}
			}
		}
		return $result;
	}

	function clearSearchWords() {
		$this->processed_search = '';
		$this->words = '';
	}

	/**
	 *
	 * creates a unique id for a search
	 * @param string $table	Database table
	 * @param string $search	Search string
	 * @param string $sort	Sort criteria
	 */
	protected function getCacheTag($table,$search,$sort) {
		return array('item'=>$table,'fields'=>implode(',',$this->fieldList),'search'=>$search, 'sort'=>$sort,'user'=>getUserIP());
	}

	/**
	 *
	 * Caches a search
	 * @param string $criteria
	 * @param string $found reslts of the search
	 */
	protected function cacheSearch($criteria, $found) {
		$criteria = serialize($criteria);
		$sql = 'SELECT `id`, data`, `date` FROM '.prefix('search_cache').' WHERE `criteria`='.db_quote($criteria);
		$result = query_single_row($sql);
		if ($result) {
				$sql = 'UPDATE '.prefix('search_cache').' SET `data`='.db_quote(serialize($found)).', `date`='.db_quote(date('Y-m-d H:m:s')).' WHERE `id`='.$result['id'];
				query($sql);
		} else {
				$sql = 'INSERT INTO '.prefix('search_cache').' (criteria, data, date) VALUES ('.db_quote($criteria).','.db_quote(serialize($found)).','.db_quote(date('Y-m-d H:m:s')).')';
				query($sql);
		}
	}

	/**
	 *
	 * Fetches a search from the cache if it exists and has not expired
	 * @param string $criteria
	 */
	protected function getCachedSearch($criteria) {
		$sql = 'SELECT `id`, `date`, `data` FROM '.prefix('search_cache').' WHERE `criteria`='.db_quote(serialize($criteria));
		$result = query_single_row($sql);
		if ($result) {
			if ((time() - strtotime($result['date'])) > SEARCH_CACHE_DURATION*60) {
				query('DELETE FROM '.prefix('search_cache').' WHERE `id`='.$result['id']);
			} else {
				return unserialize($result['data']);
			}
		}
		return NULL;
	}

} // search class end

/**
 *
 * encloses search word in quotes if needed
 * @param string $word
 * @return string
 */
function search_quote($word) {
	if (is_numeric($word) || preg_match("/[ &|!'\"`,()]/",$word)) {
		$word = '"'.addslashes($word).'"';
	}
	return $word;

}

?>