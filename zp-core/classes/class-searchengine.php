<?php
/**
 * search class
 * @package zpcore\classes\objects
 */
class SearchEngine {

	public $fieldList = NULL;
	public $page = 1;
	public $images = NULL;
	public $albums = NULL;
	public $articles = NULL;
	public $pages = NULL;
	public $pattern;
	public $tagPattern;
	private $exact = false;
	protected $dynalbumname = NULL;
	protected $album = NULL;
	protected $words;
	protected $dates;
	protected $whichdates = 'date'; // for zenpage date searches, which date field to search
	protected $search_no_albums = false; // omit albums
	protected $search_no_images = false; // omit images
	protected $search_no_pages = false; // omit pages
	protected $search_no_news = false; // omit news
	protected $search_unpublished = false; // will override the loggedin checks with respect to unpublished items
	protected $search_structure; // relates translatable names to search fields
	protected $iteration = 0; // used by apply_filter('search_statistics') to indicate sequential searches of different objects
	protected $processed_search = NULL;
	protected $album_list = NULL; // list of albums to search
	protected $album_list_exclude = null; // list of albums to exclude from search
	protected $category_list = NULL; // list of categories for a news search
	protected $searches = NULL; // remember the criteria for past searches
	protected $extraparams = array(); // allow plugins to add to search parameters
	protected $firstpageimages = null;
	protected $firstpageimages_oneimagepage = null;
	
//	mimic album object
	public $loaded = false;
	public $table = 'albums';
	public $transient = true;

	/**
	 * Constuctor
	 *
	 * @param bool $dynamic_album set true for dynamic albums (limits the search fields)
	 * @return SearchEngine
	 */
	function __construct($dynamic_album = false) {
		global $_zp_exifvars, $_zp_gallery, $_zp_db;
		$regexboundaries = $_zp_db->getRegexWordBoundaryChars();
		switch ((int) getOption('exact_tag_match')) {
			case 0:
				// partial
				$this->tagPattern = array('type' => 'like', 'open' => '%', 'close' => '%');
				break;
			case 1:
				// exact
				$this->tagPattern = array('type' => '=', 'open' => '', 'close' => '');
				break;
			case 2:
				//word
				$this->tagPattern = array('type' => 'regexp', 'open' => $regexboundaries['open'], 'close' => $regexboundaries['close']);
				break;
		}

		switch ((int) getOption('exact_string_match')) {
			case 0:
				// pattern
				$this->pattern = array('type' => 'like', 'open' => '%', 'close' => '%');
				break;
			case 1:
				// partial start
				$this->pattern = array('type' => 'regexp', 'open' => $regexboundaries['open'], 'close' => '');
				break;
			case 2:
				//word
				$this->pattern = array('type' => 'regexp', 'open' => $regexboundaries['open'], 'close' => $regexboundaries['close']);
				break;
		}
		$this->extraparams['albumssorttype'] = getOption('search_album_sort_type');
		$this->extraparams['albumssortdirection'] = getOption('search_album_sort_direction') ? 'DESC' : '';
		$this->extraparams['imagessorttype'] = getOption('search_image_sort_type');
		$this->extraparams['imagessortdirection'] = getOption('search_image_sort_direction') ? 'DESC' : '';
		$this->extraparams['newssorttype'] = getOption('search_newsarticle_sort_type');
		$this->extraparams['newssortdirection'] = getOption('search_newsarticle_sort_direction') ? 'DESC' : '';
		$this->extraparams['pagessorttype'] = getOption('search_page_sort_type');
		$this->extraparams['pagessortdirection'] = getOption('search_page_sort_direction') ? 'DESC' : '';

//image/album fields
		$this->search_structure['title'] = gettext('Title');
		$this->search_structure['desc'] = gettext('Description');
		$this->search_structure['tags'] = gettext('Tags');
		$this->search_structure['tags_exact'] = ''; //	internal use only field
		$this->search_structure['filename'] = gettext('File/Folder name');
		$this->search_structure['date'] = gettext('Date');
		$this->search_structure['custom_data'] = gettext('Custom data');
		$this->search_structure['location'] = gettext('Location/Place');
		$this->search_structure['city'] = gettext('City');
		$this->search_structure['state'] = gettext('State');
		$this->search_structure['country'] = gettext('Country');
		$this->search_structure['copyright'] = gettext('Copyright');
		$this->search_structure['owner'] = gettext('Owner');
		$this->search_structure['credit'] = gettext('Credit');
		$this->search_structure['lastchangeuser'] = gettext('Last change user');
		if (extensionEnabled('zenpage') && !$dynamic_album) {
//zenpage fields
			$this->search_structure['content'] = gettext('Content');
			$this->search_structure['extracontent'] = gettext('ExtraContent');
			$this->search_structure['author'] = gettext('Author');
			$this->search_structure['titlelink'] = gettext('TitleLink');
			$this->search_structure['news_categories'] = gettext('Categories');
		}
//metadata fields
		foreach ($_zp_exifvars as $field => $row) {
			if ($row[4] && $row[5]) { //	only those that are "real" and "processed"
				$this->search_structure[strtolower($field)] = $row[2];
			}
		}
		$this->search_structure = zp_apply_filter('searchable_fields', $this->search_structure);
		if (isset($_REQUEST['s'])) {
			$this->words = removeTrailingSlash(strtr(sanitize($_REQUEST['s'], 4), array('__23__' => '#', '__25__' => '%', '__26__' => '&', '__2F__' => '/')));
		} else {
			$this->words = '';
			if (isset($_REQUEST['date'])) { // words & dates are mutually exclusive
				$this->dates = removeTrailingSlash(sanitize($_REQUEST['date'], 3));
				if (isset($_REQUEST['whichdate'])) {
					$this->whichdates = sanitize($_REQUEST['whichdate']);
				}
			} else {
				$this->dates = '';
			}
		}
		$this->fieldList = $this->parseQueryFields();
		if (isset($_REQUEST['inalbums'])) {
			$list = trim(sanitize($_REQUEST['inalbums'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_albums = true;
						setOption('search_no_albums', 1, false);
						break;
					case "1":
						$this->search_no_albums = false;
						setOption('search_no_albums', 0, false);
						break;
					default:
						$this->album_list = explode(',', $list);
						break;
				}
			}
		}
		
		if (isset($_REQUEST['excludealbums'])) {
			$list = trim(sanitize($_REQUEST['excludealbums'], 3));
			if (!empty($list)) {
				$this->album_list_exclude = explode(',', $list);
			}
		}
		
		if (isset($_REQUEST['inimages'])) {
			$list = trim(sanitize($_REQUEST['inimages'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_images = true;
						setOption('search_no_images', 1, false);
						break;
					case "1":
						$this->search_no_images = false;
						setOption('search_no_images', 0, false);
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
						setOption('search_no_pages', 1, false);
						break;
				}
			}
		}
		if (isset($_REQUEST['innews'])) {
			$list = trim(sanitize($_REQUEST['innews'], 3));
			if (strlen($list) > 0) {
				switch ($list) {
					case "0":
						$this->search_no_news = true;
						setOption('search_no_news', 1, false);
						break;
					case "1":
						break;
					default:
						$this->category_list = explode(',', $list);
						break;
				}
			}
		}
		$this->images = NULL;
		$this->albums = NULL;
		$this->searches = array('images' => NULL, 'albums' => NULL, 'pages' => NULL, 'news' => NULL);
		zp_apply_filter('search_instantiate', $this);
	}

	/**
	 * mimic an album object
	 * @return number
	 */
	function getID() {
		return 0;
	}

	/**
	 * Returns a list of search fields display names indexed by the search mask
	 *
	 * @return array
	 */
	function getSearchFieldList() {
		$list = array();
		foreach ($this->search_structure as $key => $display) {
			if ($display) {
				$list[$display] = $key;
			}
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
			$list = explode(',', $fields);
			foreach ($this->search_structure as $key => $display) {
				if (in_array($key, $list)) {
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
		if ($fields == 0) {
			$fields = 0x0fff;
		}
		if ($fields & 0x01) {
			$list[$this->search_structure['title']] = 'title';
		}
		if ($fields & 0x02) {
			$list[$this->search_structure['desc']] = 'desc';
		}
		if ($fields & 0x04) {
			$list[$this->search_structure['tags']] = 'tags';
		}
		if ($fields & 0x08) {
			$list[$this->search_structure['filename']] = 'filename';
		}
		return $list;
	}

	/**
	 * creates a search query from the search words
	 *
	 * @param bool $long set to false to omit albumname and page parts
	 *
	 * @return string
	 */
	function getSearchParams($long = true) {
		global $_zp_page;
		$r = '';
		$w = urlencode(trim($this->codifySearchString()));
		if (!empty($w)) {
			$r .= '&s=' . $w;
		}
		$d = trim(strval($this->dates));
		if (!empty($d)) {
			$r .= '&date=' . $d;
			$d = trim(strval($this->whichdates));
			if ($d != 'date') {
				$r.= '&whichdates=' . $d;
			}
		}
		$r .= $this->getSearchFieldsText($this->fieldList);
		if ($long) {
			$a = $this->dynalbumname;
			if ($a) {
				$r .= '&albumname=' . $a;
			}
			if (empty($this->album_list)) {
				if ($this->search_no_albums) {
					$r .= '&inalbums=0';
				}
			} else {
				$r .= '&inalbums=' . implode(',', $this->album_list);
			}
			
			if (empty($this->album_list_exclude)) {
				if ($this->search_no_albums) {
					$r .= '&inalbums=0';
				}
			} else {
				$r .= '&excludealbums=' . implode(',', $this->album_list_exclude);
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
				$r .= '&innews=' . implode(',', $this->categories);
			}
			if ($_zp_page > 1) {
				$this->page = $_zp_page;
				$r .= '&page=' . $_zp_page;
			}
		}
		foreach ($this->extraparams as $p => $v) {
			$r .= '&' . $p . '=' . $v;
		}
		return $r;
	}

	/**
	 *
	 * Retrieves search extra parameters
	 * @return array
	 */
	function getSearchExtra() {
		return $this->extraparams;
	}

	/**
	 *
	 * Stores extra search params for plugin use
	 * @param array $extra
	 */
	function setSearchExtra($extra) {
		$this->extraparams = $extra;
	}

	/**
	 * sets sort directions
	 *
	 * @param bool $val the direction
	 * @param string $what 'images' if you want the image direction,
	 *        'albums' if you want it for the album
	 */
	function setSortDirection($val, $what = 'images') {
		if ($val) {
			$this->extraparams[$what . 'sortdirection'] = 'DESC';
		} else {
			$this->extraparams[$what . 'sortdirection'] = 'ASC';
		}
	}

	/**
	 * Stores the sort type
	 *
	 * @param string $sorttype the sort type
	 * @param string $what 'images' or 'albums'
	 */
	function setSortType($sorttype, $what = 'images') {
		$this->extraparams[$what . 'sorttype'] = $sorttype;
	}

	/**
	 * Returns the "searchstring" element of a query parameter set
	 *
	 * @param array $fields the fields required
	 * @param string $param the query parameter (possibly with the intro character
	 * @return string
	 */
	function getSearchFieldsText($fields, $param = '&searchfields=') {
		$fields_allowed = $this->allowedSearchFields();
		$fields_final = array();
		foreach ($fields as $field) {
			if (in_array($field, $fields_allowed)) {
				$fields_final[] = $field;
			}
		}
		if (!empty($fields_final)) {
			return $param . implode(',', $fields_final);
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
			switch ($p) {
				case 's':
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
						$list = explode(',', strtolower($v));
						foreach ($this->search_structure as $key => $row) {
							if (in_array(strtolower($key), $list)) {
								$this->fieldList[] = $key;
							}
						}
					}
					break;
				case 'page':
					$this->page = $v;
					break;
				case 'albumname':
					$alb = AlbumBase::newAlbum($v, true, true);
					if ($alb->loaded) {
						$this->album = $alb;
						$this->dynalbumname = $v;
						$this->setSortType($this->album->getSortType('album'), 'albums');
						$this->setSortDirection($this->album->getSortDirection('album'), 'albums');
						$this->setSortType($this->album->getSortType(), 'images');
						$this->setSortDirection($this->album->getSortDirection('image'), 'images');
					}
					break;
				case 'inimages':
					if (strlen($v) > 0) {
						switch ($v) {
							case "0":
								$this->search_no_images = true;
								setOption('search_no_images', 1, false);
								break;
							case "1":
								$this->search_no_images = false;
								setOption('search_no_images', 0, false);
								break;
						}
					}
					break;
				case 'inalbums':
					if (strlen($v) > 0) {
						switch ($v) {
							case "0":
								$this->search_no_albums = true;
								setOption('search_no_albums', 1, false);
								break;
							case "1":
								$this->search_no_albums = false;
								setOption('search_no_albums', 0, false);
								break;
							default:
								$this->album_list = explode(',', $v);
								break;
						}
					}
					break;
				case 'excludealbums':
					if (strlen($v) > 0) {
						$this->album_list_exclude = explode(',', $v);
					}
					break;
				case 'unpublished':
					$this->search_unpublished = (bool) $v;
					break;
				default:
					$this->extraparams[$p] = $v;
					break;
			}
		}
		if (!empty($this->words)) {
			$this->dates = ''; // words and dates are mutually exclusive
		}
	}

// call to always return unpublished items
	function setSearchUnpublished() {
		$this->search_unpublished = true;
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
	function getSearchFields($array = false) {
		if ($array) {
			return $this->fieldList;
		}
		return implode(',', $this->fieldList);
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
		$searchstring = trim(strval($this->words));
		$space_is = getOption('search_space_is');
		$opChars = array('&' => 1, '|' => 1, '!' => 1, ',' => 1, '(' => 2);
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
					$j = strpos(str_replace('\\' . $c, '__', $searchstring), $c, $i + 1);
					if ($j !== false) {
						$target .= stripcslashes(substr($searchstring, $i + 1, $j - $i - 1));
						$i = $j;
					} else {
						$target .= $c;
					}
					$c1 = $c;
					break;
				case ' ':
					$j = $i + 1;
					while ($j < strlen($searchstring) && $searchstring[$j] == ' ') {
						$j++;
					}
					switch ($space_is) {
						case 'OR':
						case 'AND':
							if ($j < strlen($searchstring)) {
								$c3 = $searchstring[$j];
								if (array_key_exists($c3, $opChars) && $opChars[$c3] == 1) {
									$nextop = $c3 != '!';
								} else if (substr($searchstring . ' ', $j, 4) == 'AND ') {
									$nextop = true;
								} else if (substr($searchstring . ' ', $j, 3) == 'OR ') {
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
								if ($space_is == 'AND') {
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
							$target .= str_pad('', $j - $i);
							break;
					}
					$i = $j - 1;
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
					$c2 = substr($searchstring, $i + 1, 1);
					switch ($c2) {
						case 'A':
							if (substr($searchstring . ' ', $i + 1, 4) == 'AND ')
								$c2 = '&';
							break;
						case 'O':
							if (substr($searchstring . ' ', $i + 1, 3) == 'OR ')
								$c2 = '|';
							break;
						case 'N':
							if (substr($searchstring . ' ', $i + 1, 4) == 'NOT ')
								$c2 = '!';
							break;
					}
					if (!((isset($opChars[$c2]) && $opChars[$c2] == 1) || (isset($opChars[$last]) && $opChars[$last] == 1))) {
						$last = $result[] = '|';
						$c1 = $c;
					}
					break;
				case '!':
				case '&':
				case '|':
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
					$j = $i + 1;
					break;
				case 'A':
					if (substr($searchstring . ' ', $i, 4) == 'AND ') {
						$op = '&';
						$skip = 3;
					}
				case 'O':
					if (substr($searchstring . ' ', $i, 3) == 'OR ') {
						$op = '|';
						$skip = 2;
					}
				case 'N':
					if (substr($searchstring . ' ', $i, 4) == 'NOT ') {
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
						$j = $i + $skip;
						while ($j < strlen($searchstring) && substr($searchstring, $j, 1) == ' ') {
							$j++;
						}
						$i = $j - 1;
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
		if (!empty($target)) {
			$last = $result[] = trim($target);
		}
		$lasttoken = '';
		foreach ($result as $key => $token) {
			if ($token == '|' && $lasttoken == '|') { // remove redundant OR ops
				unset($result[$key]);
			}
			$lasttoken = $token;
		}
		if (array_key_exists($lasttoken, $opChars) && $opChars[$lasttoken] == 1) {
			array_pop($result);
		}

		$this->processed_search = zp_apply_filter('search_criteria', $result);
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
	function codifySearchString() {
		$searchstring = $this->getSearchString();
		$sanitizedwords = '';
		if (is_array($searchstring)) {
			foreach ($searchstring as $singlesearchstring) {
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
						$sanitizedwords .= $singlesearchstring;
						break;
					default:
						$sanitizedwords .= SearchEngine::getSearchQuote(sanitize($singlesearchstring, 3));
						break;
				}
			}
		}

		$sanitizedwords = trim(str_replace(array('   ', '  ',), ' ', $sanitizedwords));
		$sanitizedwords = trim(str_replace('( ', '(', $sanitizedwords));
		$sanitizedwords = trim(str_replace(' )', ')', $sanitizedwords));
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
			} else if (is_string($fs)) {
				$fields = explode(',', $fs);
			} else if (is_array($fs)) {
				$fields = $fs;
			}
		} else {
			foreach ($_REQUEST as $key => $value) {
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
		global $_zp_zenpage, $_zp_db;
		if (!is_array($this->category_list)) {
			return false;
		}
		$cat = '';
		$list = $_zp_zenpage->getAllCategories();
		if (!empty($list)) {
			foreach ($list as $category) {
				if (in_array($category['title'], $this->category_list)) {
					$catobj = new ZenpageCategory($category['titlelink']);
					$cat .= ' `cat_id`=' . $catobj->getID() . ' OR';
					$subcats = $catobj->getCategories();
					if ($subcats) {
						foreach ($subcats as $subcat) {
							$catobj = new ZenpageCategory($subcat);
							$cat .= ' `cat_id`=' . $catobj->getID() . ' OR';
						}
					}
				}
			}
			if ($cat) {
				$cat = ' WHERE ' . substr($cat, 0, -3);
			}
		}
		$sql = 'SELECT DISTINCT `news_id` FROM ' . $_zp_db->prefix('news2cat') . $cat;
		$result = $_zp_db->query($sql);
		$list = array();
		if ($result) {
			while ($row = $_zp_db->fetchAssoc($result)) {
				$list[] = $row['news_id'];
			}
			$_zp_db->freeResult($result);
		}
		return $list;
	}

	/**
	 * Takes a list of IDs and makes a where clause
	 *
	 * @param array $idlist list of IDs for a where clause
	 */
	protected static function compressedIDList($idlist) {
		$idlist = array_unique($idlist);
		asort($idlist);
		return '`id` IN (' . implode(',', $idlist) . ')';
	}

	/**
	 * get connical sort key and direction parameters.
	 * @param type $sorttype sort field desired
	 * @param type $sortdirection DESC or ASC
	 * @param type $defaulttype if no sort type otherwise selected use this one
	 * @param type $table the database table being searched
	 * @return array
	 */
	protected function sortKey($sorttype, $sortdirection, $defaulttype, $table) {
		if (is_null($sorttype)) {
			if (array_key_exists($table . 'sorttype', $this->extraparams)) {
				$sorttype = $this->extraparams[$table . 'sorttype'];
			} else if (array_key_exists('sorttype', $this->extraparams)) {
				$sorttype = $this->extraparams['sorttype'];
			}
		}
		$sorttype = lookupSortKey($sorttype, $defaulttype, $table);
		if (is_null($sortdirection)) {
			if (array_key_exists($table . 'sortdirection', $this->extraparams)) {
				$sortdirection = $this->extraparams[$table . 'sortdirection'];
			} else if (array_key_exists('sortdirection', $this->extraparams)) {
				$sortdirection = $this->extraparams['sortdirection'];
			}
		}
		return array($sorttype, $sortdirection);
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
	function searchDate($searchstring, $searchdate, $tbl, $sorttype, $sortdirection, $whichdate = 'date') {
		global $_zp_current_album, $_zp_gallery, $_zp_db;
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
		$sql .= "FROM " . $_zp_db->prefix($tbl) . " WHERE ";
		if (!zp_loggedin()) {
			$sql .= "`show` = 1 AND (";
		}

		if (!empty($searchdate)) {
			if ($searchdate == "0000-00") {
				$sql .= "`$whichdate`=\"0000-00-00 00:00:00\"";
			} else {
				$datesize = sizeof(explode('-', $searchdate));
// search by day
				if ($datesize == 3) {
					$d1 = $searchdate . " 00:00:00";
					$d2 = $searchdate . " 23:59:59";
					$sql .= "`$whichdate` >= \"$d1\" AND `$whichdate` < \"$d2\"";
				}
// search by month
				else if ($datesize == 2) {
					$d1 = $searchdate . "-01 00:00:00";
					$d = strtotime($d1);
					$d = strtotime('+ 1 month', $d);
					$d2 = substr(date('Y-m-d H:m:s', $d), 0, 7) . "-01 00:00:00";
					$sql .= "`$whichdate` >= \"$d1\" AND `$whichdate` < \"$d2\"";
				} else {
					$sql .= "`$whichdate`<\"0000-00-00 00:00:00\"";
				}
			}
		}
		if (!zp_loggedin()) {
			$sql .= ")";
		}

		switch ($tbl) {
			case 'news':
			case 'pages':
				if (empty($sorttype)) {
					$key = '`date` DESC';
				} else {
					$key = trim($sorttype . ' ' . $sortdirection);
				}
				break;
			case 'albums':
				if (is_null($sorttype)) {
					if (empty($this->album)) {
						list($key, $sortdirection) = $this->sortKey($_zp_gallery->getSortType(), $sortdirection, 'title', 'albums');
						if ($key != '`sort_order`') {
							if ($_zp_gallery->getSortDirection()) {
								$key .= " DESC";
							}
						}
					} else {
						$key = $this->album->getAlbumSortKey();
						if ($key != '`sort_order`' && $key != 'RAND()') {
							if ($this->album->getSortDirection('album')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					list($key, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'albums');
					$key = trim($key . ' ' . $sortdirection);
				}
				break;
			default:
				$hidealbums = getNotViewableAlbums();
				if (!is_null($hidealbums)) {
					foreach ($hidealbums as $id) {
						$sql .= ' AND `albumid`!=' . $id;
					}
				}
				if (is_null($sorttype)) {
					if (empty($this->album)) {
						list($key, $sortdirection) = $this->sortKey(IMAGE_SORT_TYPE, $sortdirection, 'title', 'images');
						if ($key != '`sort_order`') {
							if (IMAGE_SORT_DIRECTION) {
								$key .= " DESC";
							}
						}
					} else {
						$key = $this->album->getImageSortKey();
						if ($key != '`sort_order`' && $key != 'RAND()') {
							if ($this->album->getSortDirection('image')) {
								$key .= " DESC";
							}
						}
					}
				} else {
					list($key, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'images');
					$key = trim($key . ' ' . $sortdirection);
				}
				break;
		}
		$sql .= " ORDER BY " . $key;
		return $sql;
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
		global $_zp_gallery, $_zp_db;
		$weights = $idlist = array();
		$sql = $allIDs = NULL;
		$tagPattern = $this->tagPattern;
// create an array of [tag, objectid] pairs for tags
		$tag_objects = array();
		$fields = $this->fieldList;
		if (count($fields) == 0) { // then use the default ones
			$fields = $this->allowedSearchFields();
		}
		foreach ($fields as $key => $field) {
			switch ($field) {
				case 'news_categories':
					if ($tbl != 'news') {
						break;
					}
					unset($fields[$key]);
					$_zp_db->query('SET @serachfield="news_categories"');
					$tagsql = 'SELECT @serachfield AS field, t.`title` AS name, o.`news_id` AS `objectid` FROM ' . $_zp_db->prefix('news_categories') . ' AS t, ' . $_zp_db->prefix('news2cat') . ' AS o WHERE t.`id`=o.`cat_id` AND (';
					foreach ($searchstring as $singlesearchstring) {
						switch ($singlesearchstring) {
							case '&':
							case '!':
							case '|':
							case '(':
							case ')':
								break;
							case '*':
								$targetfound = true;
								$tagsql .= "COALESCE(title, '') != '' OR ";
								break;
							default:
								$targetfound = true;
								$tagsql .= '`title` = ' . $_zp_db->quote($singlesearchstring) . ' OR ';
						}
					}
					$tagsql = substr($tagsql, 0, strlen($tagsql) - 4) . ') ORDER BY t.`id`';
					$objects = $_zp_db->queryFullArray($tagsql, false);
					if (is_array($objects)) {
						$tag_objects = $objects;
					}
					break;
				case 'tags_exact':
					$tagPattern = array('type' => '=', 'open' => '', 'close' => '');
				case 'tags':
					unset($fields[$key]);
					$_zp_db->query('SET @serachfield="tags"');
					$tagsql = 'SELECT @serachfield AS field, t.`name`, o.`objectid` FROM ' . $_zp_db->prefix('tags') . ' AS t, ' . $_zp_db->prefix('obj_to_tag') . ' AS o WHERE t.`id`=o.`tagid` AND o.`type`="' . $tbl . '" AND (';
					foreach ($searchstring as $singlesearchstring) {
						switch ($singlesearchstring) {
							case '&':
							case '!':
							case '|':
							case '(':
							case ')':
								break;
							case '*':
								$_zp_db->query('SET @emptyfield="*"');
								$tagsql = str_replace('t.`name`', '@emptyfield as name', $tagsql);
								$tagsql .= "t.`name` IS NOT NULL OR ";
								break;
							default:
								$targetfound = true;
								if ($tagPattern['type'] == 'like') {
									$target = $_zp_db->likeEscape($singlesearchstring);
								} else {
									$target = $singlesearchstring;
								}
								$tagsql .= 't.`name` ' . strtoupper($tagPattern['type']) . ' ' . $_zp_db->quote($tagPattern['open'] . $target . $tagPattern['close']) . ' OR ';
						}
					}
					$tagsql = substr($tagsql, 0, strlen($tagsql) - 4) . ') ORDER BY t.`id`';
					$objects = $_zp_db->queryFullArray($tagsql, false);
					if (is_array($objects)) {
						$tag_objects = array_merge($tag_objects, $objects);
					}
					break;
				default:
					break;
			}
		}


// create an array of [name, objectid] pairs for the search fields.
		$field_objects = array();
		if (count($fields) > 0) {
			$columns = array();
			$dbfields = $_zp_db->getFields($tbl);
			if (is_array($dbfields)) {
				foreach ($dbfields as $row) {
					$columns[] = strtolower($row['Field']);
				}
			}
			foreach ($searchstring as $singlesearchstring) {
				switch ($singlesearchstring) {
					case '!':
					case '&':
					case '|':
					case '(':
					case ')':
						break;
					default:
						$targetfound = true;
						$_zp_db->query('SET @serachtarget=' . $_zp_db->quote($singlesearchstring));
						foreach ($fields as $fieldname) {
							if ($tbl == 'albums' && strtolower($fieldname) == 'filename') {
								$fieldname = 'folder';
							} else {
								$fieldname = strtolower($fieldname);
							}
							if ($fieldname && in_array($fieldname, $columns)) {
								$_zp_db->query('SET @serachfield=' . $_zp_db->quote($fieldname));
								switch ($singlesearchstring) {
									case '*':
										$sql = 'SELECT @serachtarget AS name, @serachfield AS field, `id` AS `objectid` FROM ' . $_zp_db->prefix($tbl) . ' WHERE (' . "COALESCE(`$fieldname`, '') != ''" . ') ORDER BY `id`';
										break;
									default:
										if ($this->pattern['type'] == 'like') {
											$target = $_zp_db->likeEscape($singlesearchstring);
										} else {
											$target = $singlesearchstring;
										}
										$fieldsql = ' `' . $fieldname . '` ' . strtoupper($this->pattern['type']) . ' ' . $_zp_db->quote($this->pattern['open'] . $target . $this->pattern['close']);
										$sql = 'SELECT @serachtarget AS name, @serachfield AS field, `id` AS `objectid` FROM ' . $_zp_db->prefix($tbl) . ' WHERE (' . $fieldsql . ') ORDER BY `id`';
								}
								$objects = $_zp_db->queryFullArray($sql, false);
								if (is_array($objects)) {
									$field_objects = array_merge($field_objects, $objects);
								}
							}
						}
				}
			}
		}

// now do the boolean logic of the search string
		$exact = $tagPattern['type'] == '=';
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

			foreach($searchstring as $singlesearchstring) {
				switch ($singlesearchstring) {
					case '&':
					case '!':
					case '|':
						$op = $op . $singlesearchstring;
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
								if (is_array($objectid) && is_array($idlist)) {
									$idlist = array_diff($idlist, $objectid);
								} else {
									$idlist = array();
								}
								break;
							case '';
							case '|':
								if (is_array($objectid) && is_array($idlist)) {
									$idlist = array_merge($idlist, $objectid);
								} else {
									$idlist = array();
								}
								break;
						}
						$op = '';
						break;
					default:
						$lookfor = strtolower($singlesearchstring);
						$objectid = NULL;
						foreach ($taglist as $key => $objlist) {
							if (($exact && $lookfor == $key) || (!$exact && preg_match('|' . preg_quote($lookfor) . '|', $key))) {
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
									$result = $_zp_db->query("SELECT `id` FROM " . $_zp_db->prefix($tbl));
									if ($result) {
										while ($row = $_zp_db->fetchAssoc($result)) {
											$allIDs[] = $row['id'];
										}
										$_zp_db->freeResult($result);
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
						$op = '';
						break;
				}
			}
		}

// we now have an id list of the items that were found and will create the SQL Search to retrieve their records
		if (count($idlist) > 0) {
			$weights = array_count_values($idlist);
			arsort($weights, SORT_NUMERIC);
			$sql = 'SELECT DISTINCT `id`,`show`,';

			switch ($tbl) {
				case 'news':
					if ($this->search_unpublished || zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
						$show = '';
					} else {
						$show = "`show` = 1 AND ";
					}
					$sql .= '`title`, `titlelink` ';
					if (is_array($this->category_list)) {
						$news_list = $this->subsetNewsCategories();
						$idlist = array_intersect($news_list, $idlist);
						if (count($idlist) == 0) {
							return array(false, array());
						}
					}
					if (empty($sorttype)) {
						$key = '`date` DESC';
					} else {
						$key = trim($sorttype . $sortdirection);
					}
					if ($show) {
						$show .= '`date`<=' . $_zp_db->quote(date('Y-m-d H:i:s')) . ' AND ';
					}
					break;
				case 'pages':
					if (zp_loggedin(MANAGE_ALL_PAGES_RIGHTS)) {
						$show = '';
					} else {
						$show = "`show` = 1 AND ";
					}
					$sql .= '`title`, `titlelink` ';
					if (empty($sorttype)) {
						$key = '`date` DESC';
					} else {
						$key = trim($sorttype . $sortdirection);
					}
					if ($show) {
						$show .= '`date`<=' . $_zp_db->quote(date('Y-m-d H:i:s')) . ' AND ';
					}
					break;
				case 'albums':
					if ($this->search_unpublished || zp_loggedin()) {
						$show = '';
					} else {
						$show = "`show` = 1 AND ";
					}
					$sql .= "`folder`, `title` ";
					if (is_null($sorttype)) {
						if (empty($this->album)) {
							list($key, $sortdirection) = $this->sortKey($_zp_gallery->getSortType(), $sortdirection, 'title', 'albums');
							if ($_zp_gallery->getSortDirection()) {
								$key .= " DESC";
							}
						} else {
							$key = $this->album->getAlbumSortKey();
							if ($key != '`sort_order`' && $key != 'RAND()') {
								if ($this->album->getSortDirection('album')) {
									$key .= " DESC";
								}
							}
						}
					} else {
						list($key, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'albums');
						$key = trim($key . ' ' . $sortdirection);
					}
					break;
				default: // images
					if ($this->search_unpublished || zp_loggedin()) {
						$show = '';
					} else {
						$show = "`show` = 1 AND ";
					}
					$sql .= "`albumid`, `filename`, `title` ";
					if (is_null($sorttype)) {
						if (empty($this->album)) {
							list($key, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'images');
							if ($sortdirection) {
								$key .= " DESC";
							}
						} else {
							$key = $this->album->getImageSortKey();
							if ($key != '`sort_order`') {
								if ($this->album->getSortDirection('image')) {
									$key .= " DESC";
								}
							}
						}
					} else {
						list($key, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'images');
						$key = trim($key . ' ' . $sortdirection);
					}
					break;
			}
			$sql .= "FROM " . $_zp_db->prefix($tbl) . " WHERE " . $show;
			$sql .= '(' . self::compressedIDList($idlist) . ')';
			$sql .= " ORDER BY " . $key;
			return array($sql, $weights);
		}
		return array(false, array());
	}

	/**
	 * Returns an array of albums found in the search
	 * @param string $sorttype what to sort on
	 * @param string $sortdirection what direction
	 * @param bool $mine set true/false to override ownership
	 *
	 * @return array
	 */
	private function getSearchAlbums($sorttype, $sortdirection, $mine = NULL) {
		global $_zp_db;
		if (getOption('search_no_albums') || $this->search_no_albums) {
			return array();
		}
		list($sorttype, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'albums');
		$albums = array();
		$searchstring = $this->getSearchString();
		if (empty($searchstring)) {
			return array();
		} // nothing to find
		$criteria = $this->getCacheTag('albums', serialize($searchstring), $sorttype . ' ' . $sortdirection . ' '. $mine);
		if ($this->albums && $criteria == $this->searches['albums']) {
			return $this->albums;
		}
		$albums = $this->getCachedSearch($criteria);
		if (is_null($albums)) {
			if (is_null($mine) && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
				$mine = true;
			}
			$result = $albums = array();
			list ($search_query, $weights) = $this->searchFieldsAndTags($searchstring, 'albums', $sorttype, $sortdirection);
			if (!empty($search_query)) {
				$search_result = $_zp_db->query($search_query);
				if ($search_result) {
					while ($row = $_zp_db->fetchAssoc($search_result)) {
						$albumname = $row['folder'];
						if ($albumname != $this->dynalbumname) {
							if (file_exists(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($albumname))) {
								$album = AlbumBase::newAlbum($albumname);
								switch (themeObject::checkScheduledPublishing($row)) {
									case 1:
										$album->setPublished(0);
										$album->save();
									case 2:
										$row['show'] = 0;
								}
								if ($mine || (is_null($mine) && $album->isVisible())) {		
									if ((empty($this->album_list) || in_array($albumname, $this->album_list)) && !$this->excludeAlbum($albumname)) {
										$result[] = array('title' => $row['title'], 'name' => $albumname, 'weight' => $weights[$row['id']]);
									}
								} 
							}
						}
					}
					$_zp_db->freeResult($search_result);
					$sortdir = self::getSortdirBool($sortdirection);
					if (is_null($sorttype)) {
						$result = sortMultiArray($result, 'weight', $sortdir, true, false, false, array('weight'));
					}
					if ($sorttype == '`title`') {
						$result = sortByMultilingual($result, 'title', $sortdir);
					}
					foreach ($result as $album) {
						$albums[] = $album['name'];
					}
				}
			}
			zp_apply_filter('search_statistics', $searchstring, 'albums', !empty($albums), $this->dynalbumname, $this->iteration++);
			$this->cacheSearch($criteria, $albums);
		}
		$this->albums = $albums;
		$this->searches['albums'] = $criteria;
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
	function getAlbums($page = 0, $sorttype = NULL, $sortdirection = NULL, $care = true, $mine = NULL) {
		$this->albums = $this->getSearchAlbums($sorttype, $sortdirection, $mine);
		if ($page == 0) {
			return $this->albums;
		} else {
			$albums_per_page = max(1, getOption('albums_per_page'));
			return array_slice($this->albums, $albums_per_page * ($page - 1), $albums_per_page);
		}
	}
	
	/**
	 * Checks if the album should be excluded from results
	 * Subalbums and their contents inherit the exclusion.
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $albumname
	 * @return boolean
	 */
	function excludeAlbum($albumname) {
		$exclude = false;
		if (!is_null($this->album_list_exclude)) {
			if (in_array($albumname, $this->album_list_exclude)) {
				return true;
			} else {
				foreach ($this->album_list_exclude as $excludealbum) {
					if (strpos($albumname, $excludealbum) === 0) {
						return true;
					}
				}
			}
		}
		return $exclude;
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
		global $_zp_gallery;
		$albums = $this->getAlbums(0);
		$inx = array_search($curalbum, $albums) + 1;
		if ($inx >= 0 && $inx < count($albums)) {
			return AlbumBase::newAlbum($albums[$inx]);
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
		global $_zp_gallery;
		$albums = $this->getAlbums(0);
		$inx = array_search($curalbum, $albums) - 1;
		if ($inx >= 0 && $inx < count($albums)) {
			return AlbumBase::newAlbum($albums[$inx]);
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
	private function getSearchImages($sorttype, $sortdirection, $mine = NULL) {
		global $_zp_db;
		if (getOption('search_no_images') || $this->search_no_images) {
			return array();
		}
		list($sorttype, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'images');
		if (is_null($mine) && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$mine = true;
		}
		$searchstring = $this->getSearchString();
		$searchdate = $this->dates;
		if (empty($searchstring) && empty($searchdate)) {
			return array();
		} // nothing to find
		$criteria = $this->getCacheTag('images', serialize($searchstring) . ' ' . $searchdate, $sorttype . ' ' . $sortdirection . ' '.$mine);
		if ($criteria == $this->searches['images']) {
			return $this->images;
		}
		$images = $this->getCachedSearch($criteria);
		if (is_null($images)) {
			if (empty($searchdate)) {
				list ($search_query, $weights) = $this->searchFieldsAndTags($searchstring, 'images', $sorttype, $sortdirection);
			} else {
				$search_query = $this->searchDate($searchstring, $searchdate, 'images', $sorttype, $sortdirection);
			}
			if (empty($search_query)) {
				$search_result = false;
			} else {
				$search_result = $_zp_db->query($search_query);
			}
			$albums_seen = $images = array();
			if ($search_result) {
				while ($row = $_zp_db->fetchAssoc($search_result)) {
					$albumid = $row['albumid'];
					if (array_key_exists($albumid, $albums_seen)) {
						$albumrow = $albums_seen[$albumid];
					} else {
						$query = "SELECT folder, `show` FROM " . $_zp_db->prefix('albums') . " WHERE id = $albumid";
						$row2 = $_zp_db->querySingleRow($query); // id is unique
						if ($row2) {
							$albumname = $row2['folder'];
							$allow = false;
							$album = AlbumBase::newAlbum($albumname);		
							$imageobj = Image::newImage($album, $row['filename']);
							if ($imageobj->hasPublishSchedule()) {
								$row['show'] = 0;
							}
							$viewUnpublished = ($mine || $this->search_unpublished || (is_null($mine)) && ($imageobj->isVisible()));
							if ($viewUnpublished) {
								$allow = (empty($this->album_list) || in_array($albumname, $this->album_list)) && !$this->excludeAlbum($albumname);
							} 
							$albums_seen[$albumid] = $albumrow = array('allow' => $allow, 'viewUnpublished' => $viewUnpublished, 'folder' => $albumname, 'localpath' => ALBUM_FOLDER_SERVERPATH . internalToFilesystem($albumname) . '/');
						} else {
							$albums_seen[$albumid] = $albumrow = array('allow' => false, 'viewUnpublished' => false, 'folder' => '', 'localpath' => '');
						}
					}
					if ($albumrow['allow'] && ($row['show'] || $albumrow['viewUnpublished'])) {
						if (file_exists($albumrow['localpath'] . internalToFilesystem($row['filename']))) { //	still exists
							$data = array('title' => $row['title'], 'filename' => $row['filename'], 'folder' => $albumrow['folder']);
							if (isset($weights)) {
								$data['weight'] = $weights[$row['id']];
							}
							$images[] = $data;
						}
					}
				}
				$_zp_db->freeResult($search_result);
				$sortdir = self::getSortdirBool($sortdirection);
				if (is_null($sorttype) && isset($weights)) {
					$images = sortMultiArray($images, 'weight', $sortdir, true, false, false, array('weight'));
				}
				if ($sorttype == '`title`') {
					$images = sortByMultilingual($images, 'title', $sortdir);
				}
			}
			if (empty($searchdate)) {
				zp_apply_filter('search_statistics', $searchstring, 'images', !empty($images), $this->dynalbumname, $this->iteration++);
			}
			$this->cacheSearch($criteria, $images);
		}
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
	 * @param bool $care placeholder to make the getImages methods all the same.
	 * @param bool $mine set true/false to overried ownership
	 * @return array
	 */
	function getImages($page = 0, $firstPageCount = 0, $sorttype = NULL, $sortdirection = NULL, $care = true, $mine = NULL) {
		$this->images = $this->getSearchImages($sorttype, $sortdirection, $mine);
		if ($page == 0) {
			return $this->images;
		} else {
			if (empty($this->images)) {
				return array();
			}
			// Only return $firstPageCount images if we are on the first page and $firstPageCount > 0
			if (($page == 1) && ($firstPageCount > 0)) {
				$pageStart = 0;
				$images_per_page = $firstPageCount;
			} else {
				if ($firstPageCount > 0) {
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
		$images = $this->getImages();
		$c = 0;
		foreach ($images as $image) {
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
			return Image::newImage(AlbumBase::newAlbum($img['folder']), $img['filename']);
		}
		return false;
	}

	function getDynamicAlbum() {
		return $this->album;
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
	 *
	 * Returns pages from a search
	 * @param bool $published ignored, left for parameter compatibility
	 * @param bool $toplevel ignored, left for parameter compatibility
	 * @param int $number ignored, left for parameter compatibility
	 * @param string $sorttype the sort key
	 * @param strng $sortdirection the sort order
	 *
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL) {
		return $this->getSearchPages($sorttype, $sortdirection);
	}

	/**
	 * Returns a list of Pages Titlelinks found in the search
	 *
	 * @parm string $sorttype optional sort field
	 * @param string $sortdirection optional ordering
	 *
	 * @return array
	 */
	private function getSearchPages($sorttype, $sortdirection) {
		global $_zp_db;
		if (!extensionEnabled('zenpage') || getOption('search_no_pages') || $this->search_no_pages)
			return array();
		list($sorttype, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'pages');
		$searchstring = $this->getSearchString();
		$searchdate = $this->dates;
		if (empty($searchstring) && empty($searchdate)) {
			return array();
		} // nothing to find
		$criteria = $this->getCacheTag('pages', serialize($searchstring) . ' ' . $searchdate, $sorttype . ' ' . $sortdirection);
		if ($this->pages && $criteria == $this->searches['pages']) {
			return $this->pages;
		}
		$pages = $this->getCachedSearch($criteria);
		if (is_null($pages)) {
			$pages = $result = array();
			if (empty($searchdate)) {
				list ($search_query, $weights) = $this->searchFieldsAndTags($searchstring, 'pages', $sorttype, $sortdirection);
				if (empty($search_query)) {
					$search_result = false;
				} else {
					$search_result = $_zp_db->query($search_query);
				}
				zp_apply_filter('search_statistics', $searchstring, 'pages', !$search_result, false, $this->iteration++);
			} else {
				$search_query = $this->searchDate($searchstring, $searchdate, 'pages', NULL, NULL);
				$search_result = $_zp_db->query($search_query);
			}
			if ($search_result) {
				while ($row = $_zp_db->fetchAssoc($search_result)) {
					$pageobj = new ZenpagePage($row['titlelink']);
					if ($this->search_unpublished || $pageobj->isVisible()) {
						$data = array('title' => $row['title'], 'titlelink' => $row['titlelink']);
						if (isset($weights)) {
							$data['weight'] = $weights[$row['id']];
						}
						$result[] = $data;
					} 
				}
				$_zp_db->freeResult($search_result);
			}
			$sortdir = self::getSortdirBool($sortdirection);
			if (is_null($sorttype) && isset($weights)) {
				$result = sortMultiArray($result, 'weight', $sortdir, true, false, false, array('weight'));
			}
			if ($sorttype == '`title`') {
				$result = sortByMultilingual($result, 'title', $sortdir);
			}
			foreach ($result as $page) {
				$pages[] = $page['titlelink'];
			}
			$this->cacheSearch($criteria, $pages);
		}
		$this->pages = $pages;
		$this->searches['pages'] = $criteria;
		return $this->pages;
	}

	/**
	 * Returns a list of News Titlelinks found in the search
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param bool $published placeholder for consistent parameter list
	 * @param bool $ignorepagination ignore pagination
	 * @param string $sorttype field to sort on
	 * @param string $sortdirection sort order
	 *
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sorttype = NULL, $sortdirection = NULL) {
		$articles = $this->getSearchArticles($sorttype, $sortdirection);
		if (empty($articles)) {
			return array();
		} else {
			if ($ignorepagination || !$articles_per_page) {
				return $articles;
			}
			return array_slice($articles, Zenpage::getOffset($articles_per_page), $articles_per_page);
		}
	}

	/**
	 * Returns a list of News Titlelinks found in the search
	 *
	 * @param string $sorttype field to sort on
	 * @param string $sortdirection sort order
	 *
	 * @return array
	 */
	private function getSearchArticles($sorttype, $sortdirection) {
		global $_zp_db;
		if (!extensionEnabled('zenpage') || getOption('search_no_news') || $this->search_no_news) {
			return array();
		}
		list($sorttype, $sortdirection) = $this->sortKey($sorttype, $sortdirection, 'title', 'news');
		$searchstring = $this->getSearchString();
		$searchdate = $this->dates;
		if (empty($searchstring) && empty($searchdate)) {
			return array();
		} // nothing to find
		$criteria = $this->getCacheTag('news', serialize($searchstring) . ' ' . $searchdate, $sorttype . ' ' . $sortdirection);
		if ($this->articles && $criteria == $this->searches['news']) {
			return $this->articles;
		}
		$result = $this->getCachedSearch($criteria);
		if (is_null($result)) {
			$result = array();
			if (empty($searchdate)) {
				list ($search_query, $weights) = $this->searchFieldsAndTags($searchstring, 'news', $sorttype, $sortdirection);
			} else {
				$search_query = $this->searchDate($searchstring, $searchdate, 'news', $sorttype, $sortdirection, $this->whichdates);
			}
			if (empty($search_query)) {
				$search_result = false;
			} else {
				$search_result = $_zp_db->query($search_query);
			}
			zp_apply_filter('search_statistics', $searchstring, 'news', !empty($search_result), false, $this->iteration++);
			if ($search_result) {
				while ($row = $_zp_db->fetchAssoc($search_result)) {
					$articleobj = new ZenpageNews($row['titlelink']);
					if ($this->search_unpublished || $articleobj->isVisible()) {
						$data = array('title' => $row['title'], 'titlelink' => $row['titlelink']);
						if (isset($weights)) {
							$data['weight'] = $weights[$row['id']];
						}
						$result[] = $data;
					}
				}
				$_zp_db->freeResult($search_result);
			}
			$sortdir = self::getSortdirBool($sortdirection);
			if (is_null($sorttype) && isset($weights)) {
				$result = sortMultiArray($result, 'weight', $sortdir, true, false, false, array('weight'));
			}
			if ($sorttype == '`title`') {
				$result = sortByMultilingual($result, 'title', $sortdir);
			}
			$this->cacheSearch($criteria, $result);
		}
		$this->articles = $result;
		$this->searches['news'] = $criteria;
		return $this->articles;
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
	protected function getCacheTag($table, $search, $sort) {
		$user = 'guest';
		$authCookies = Authority::getAuthCookies();
		if (!empty($authCookies)) { // some sort of password exists, play it safe and make the tag unique
			$user = getUserIP();
		}
		$array = array('item' => $table, 'fields' => implode(', ', $this->fieldList), 's' => $search, 'sort' => $sort, 'user' => $user);
		$dynalbum = $this->getDynamicAlbum();
		if($dynalbum) {
			$array['dynalbum'] = $dynalbum->name;
		}
		return $array;
	}

	/**
	 *
	 * Caches a search
	 * @param string $criteria
	 * @param string $found reslts of the search
	 */
	private function cacheSearch($criteria, $found) {
		global $_zp_db;
		if (SEARCH_CACHE_DURATION) {
			$criteria = serialize(serialize($criteria));
			$sql = 'SELECT `id`, `data`, `date` FROM ' . $_zp_db->prefix('search_cache') . ' WHERE `criteria` = ' . $_zp_db->quote($criteria);
			$result = $_zp_db->querySingleRow($sql);
			if ($result) {
				$sql = 'UPDATE ' . $_zp_db->prefix('search_cache') . ' SET `data` = ' . $_zp_db->quote(serialize($found)) . ', `date` = ' . $_zp_db->quote(date('Y-m-d H:m:s')) . ' WHERE `id` = ' . $result['id'];
				$_zp_db->query($sql);
			} else {
				$sql = 'INSERT INTO ' . $_zp_db->prefix('search_cache') . ' (criteria, data, date) VALUES (' . $_zp_db->quote($criteria) . ', ' . $_zp_db->quote(serialize($found)) . ', ' . $_zp_db->quote(date('Y-m-d H:m:s')) . ')';
				$_zp_db->query($sql);
			}
		}
	}

	/**
	 *
	 * Fetches a search from the cache if it exists and has not expired
	 * @param string $criteria
	 */
	private function getCachedSearch($criteria) {
		global $_zp_db;
		if (SEARCH_CACHE_DURATION) {
			$criteria = serialize(serialize($criteria));
			$sql = 'SELECT `id`, `date`, `data` FROM ' . $_zp_db->prefix('search_cache') . ' WHERE `criteria` = ' . $_zp_db->quote($criteria);
			$result = $_zp_db->querySingleRow($sql);
			if ($result) {
				if ((time() - strtotime($result['date'])) > SEARCH_CACHE_DURATION * 60) {
					$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('search_cache') . ' WHERE `id` = ' . $result['id']);
				} else {
					if ($result = getSerializedArray($result['data'])) {
						return $result;
					}
				}
			}
		}
		return NULL;
	}

	/**
	 * Clears the entire search cache table
	 */
	static function clearSearchCache() {
		global $_zp_db;
		$check = $_zp_db->querySingleRow('SELECT id FROM ' . $_zp_db->prefix('search_cache'). ' LIMIT 1');
		if($check) {
			$_zp_db->query('TRUNCATE TABLE ' . $_zp_db->prefix('search_cache'));
		}
	}
	
	/**
	 * Returns true if $sortdir is set to "DESC", otherwise false, for use with sorting functions
	 * @param string $sortdirection Traditional speaking values "ASC" or "DESC"
	 * 
	 * @since 1.5.8
	 * 
	 * @return boolean
	 */
	static function getSortdirBool($sortdirection = 'asc') {
		$dir = false; // ascending default
		if (strtolower($sortdirection) == 'desc') {
			$dir = true;
		}
		return $dir;
	}
	
	/**
	 *
	 * encloses search word in quotes if needed
	 * 
	 * @since 1.6 - Move to Search class as static method
	 * @param string $word
	 * @return string
	 */
	static function getSearchQuote($word) {
		if (is_numeric($word) || preg_match("/[ &|!'\"`,()]/", $word)) {
			$word = '"' . str_replace("\\'", "'", addslashes($word)) . '"';
		}
		return $word;
	}

	/**
	 * Returns the a sanitized version of the search string
	 *
	 * @sicne ZenphotoCMS 1.6
	 * @return string
	 */
	function getSearchWordsSanitized() {
		return stripcslashes($this->codifySearchString());
	}

	/**
	 * Returns the formatted date of the search
	 * 
	 * @since 1.6
	 * 
	 * @param string $format A datetime format, if using localized dates an ICU dateformat
	 * @return string
	 */
	function getSearchDateFormatted($format = 'F Y') {
		$date = $this->getSearchDate();
		if (empty($date)) {
			return "";
		}
		if ($date == '0000-00') {
			return gettext("no date");
		};
		$dt = strtotime($date . "-01");
		return zpFormattedDate($format, $dt);
	}
	
	/**
	 * 
	 * Gets the number of album pages
	 * 
	 * @since 1.6
	 * 
	 * @param string $type 'total" total pages rounded, "full" number of pages that exactly match the per page value, 
	 *		"plain" number of pages as float value
	 * @return int|float
	 */
	function getNumAlbumPages($type = 'total') {
		$album_pages = $this->getNumAlbums() / $this->getAlbumsPerPage();
		switch ($type) {
			case 'plain':
				return $album_pages;
			case 'full':
				return floor($album_pages);
			case 'total':
				return ceil($album_pages);
		}
	}

	/**
	 * Gets the number of image pages
	 * 
	 * @since 1.6
	 * 
	 * @param string $type 'total" total pages rounded, "full" number of pages that exactly match the per page value, 
	 *							"plain" number of pages as float value
	 * @param type $type
	 * @return int|float
	 */
	function getNumImagePages($type = 'total') {
		$image_pages = $this->getNumImages() / $this->getImagesPerPage();
		switch ($type) {
			case 'plain':
				return $image_pages;
			case 'full':
				return floor($image_pages);
			case 'total':
				return ceil($image_pages);
		}
	}

	/**
	 * Gets the number of total pages of albums and images
	 * 
	 * @since 1.6
	 * 
	 * @param bool $one_image_page set to true if your theme collapses all image thumbs
	 * or their equivalent to one page. This is typical with flash viewer themes
	 * @return int
	 */
	function getTotalPages($one_image_page = false) {
		$total_pages = $this->getNumAlbumPages('total') + $this->getNumImagePages('total');
		$first_page_images = $this->getFirstPageImages($one_image_page);
		if ($first_page_images == 0) {
			return $total_pages;
		} else {
			return ($total_pages - 1);
		}
	}
	
	/**
	 * Gets the albums per page value
	 * 
	 * @since 1.6
	 * 
	 * @return int
	 */
	function getAlbumsPerPage() {
		return max(1, getOption('albums_per_page'));
	}

	/**
	 * 
	 * Gets the images per page value
	 * 
	 * @since 1.6
	 * 
	 * @return int
	 */
	function getImagesPerPage() {
		return max(1, getOption('images_per_page'));
	}

		/**
	 * Gets the number of images if the thumb transintion page for sharing thunbs on the last album and the first image page
	 * 
	 * @since 1.6
	 * 
	 * @param bool $one_image_page 
	 * @return int
	 */
	function getFirstPageImages($one_image_page = false) {
		if ($one_image_page) {
			if (!is_null($this->firstpageimages_oneimagepage)) {
				return $this->firstpageimages_oneimagepage;
			}
			return $this->firstpageimages_oneimagepage = Gallery::getFirstPageImages($this, $one_image_page);
		} else {
			if (!is_null($this->firstpageimages)) {
				return $this->firstpageimages;
			}
			return $this->firstpageimages = Gallery::getFirstPageImages($this, $one_image_page);
		}
	}
	
	/**
	 * Gets the mode of the current search: 
	 * 
	 * - 'search' (general results)
	 * - 'archive' (Date archive results - albums and images only - News article date archives are no searches!)
	 * - 'tag' (specific tag results);
	 * 
	 * @since 1.6.3
	 * @return string
	 */
	function getMode() {
		$fields = $this->getSearchFields(true);
		$dates = $this->getSearchDate();
		if (empty($dates)) {
			$mode = 'search';
		} else {
			$mode = 'archive';
		}
		return self::getSearchMode($fields, $dates);
	}
	
	/**
	 * Static method to get the search mode based on fields and dates: 
	 * 
	 * - 'search' (general results)
	 * - 'archive' (Date archive results - albums and images only - News article date archives are no searches!)
	 * - 'tag' (specific tag results);
	 * 
	 * This is a helper for e.g. searchengine::getSearchURL() before an actual search is performed. 
	 * Within actual searchengine class object context use the method getMode() instead
	 * 
	 * @since 1.6.3 
	 * @param array $fields The search fields
	 * @param string|array $dates dates to limit the search
	 * @return string
	 */
	static function getSearchMode($fields, $dates) {
		if (!is_array($fields)) {
			$fields = explode(',', $fields);
		}
		if (empty($dates)) {
			$mode = 'search';
		} else {
			$mode = 'archive';
		}
		if (!empty($fields) && empty($dates)) {
			if (count($fields) == 1 && array_shift($fields) == 'tags') {
				$mode = 'tag';
			}
		}
		return $mode;
	}
	
	/**
	 * Returns a search URL
	 * 
	 * @since 1.1.3
	 * @since 1.6 - Move to SearchEngine class as static method
	 *
	 * @param mixed $words the search words target
	 * @param mixed $dates the dates that limit the search
	 * @param mixed $fields the fields on which to search
	 * @param int $page the page number for the URL
	 * @param array $object_list the list of objects to search
	 * @return string
	 */
	static function getSearchURL($words = '', $dates = '', $fields = '', $page = '', $object_list = NULL) {
		$baseurl = '';
		$query = array('s' => '');
		$rewrite = $searchurl_mode = 	$searchfields = '';
		if (MOD_REWRITE) {
			$rewrite = true;
			if (is_array($object_list)) {
				foreach ($object_list as $obj) {
					if ($obj) {
						$rewrite = false;
						break;
					}
				}
			}
		}
		if (!is_array($fields)) {
			$fields = explode(',', $fields);
		}
		$searchurl_mode = self::getSearchMode($fields, $dates);

		//$rewrite = false;
		if ($rewrite) {
			switch($searchurl_mode) {
				default:
				case 'search':
					$baseurl = SEO_WEBPATH . '/' . _SEARCH_ . '/';
					break;
				case 'archive':
					$baseurl = SEO_WEBPATH . '/' . _ARCHIVE_ . '/';
					break;
				case 'tag':
					$baseurl = SEO_WEBPATH . '/' . _TAGS_ . '/';
					break;
			}			
		} else {
			$baseurl = SEO_WEBPATH . "/index.php?p=search";
		}
		$search = new SearchEngine();
		$searchfields = $search->getSearchFieldsText($fields, 'searchfields=');
		if (!empty($words)) {
			if (is_array($words)) {
				foreach ($words as $key => $word) {
					$words[$key] = SearchEngine::getSearchQuote($word);
				}
				$words = implode(',', $words);
			}
			$words = strtr($words, array('%' => '__25__', '&' => '__26__', '#' => '__23__', '/' => '__2F__'));
			$query['s'] = urlencode($words);
		}
		if ($searchurl_mode == 'archive') {
			if (is_array($dates)) {
				$dates = implode(',', $dates);
			}
			$query['date'] = $dates;
			unset($query['s']); // date archive actually invalidates normal search term
		}
		if ($page > 1) {
			$query['page'] = $page;
		}
		if (is_array($object_list)) {
			foreach ($object_list as $key => $list) {
				if (!empty($list)) {
					$query['in' . $key] = html_encode(implode(',', $list));
				}
			}
		}
		if ($rewrite) {
			switch ($searchurl_mode) {
				case 'search':
					if (isset($query['s'])) {
						$searchwords = $query['s'];
						unset($query['s']);
						$url = $baseurl . implode('/', $query);
						if ($page > 1) {
							$url .= '/';
						}
						$url .= '?s=' . $searchwords;
						if (!empty($searchfields)) {
							$url .= '&' . $searchfields;
						}
					}
					break;
				case 'archive':
				case 'tag':
					$url = $baseurl . implode('/', $query) . '/';
					break;
			}
		} else {
			if(empty($query['s'])) {
				unset($query['s']);
			}
			$url = $baseurl . '&' . urldecode(http_build_query($query));
			if (!empty($searchfields)) {
				$url .= '&' . $searchfields;
			}
			
		}
		return $url;
	}
	
	

}
