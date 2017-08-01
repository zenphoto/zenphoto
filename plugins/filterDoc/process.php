<?php

/**
 * This is processfilters guts
 *
 * @package plugins
 * @subpackage development
 */
require_once(SERVERPATH . '/' . ZENFOLDER . '/setup/setup-functions.php');

function processFilters() {
	global $_zp_resident_files;

	$classes = $subclasses = $oldfilterlist = array();
	$htmlfile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list.html';
	$prolog = $epilog = '';
	if (file_exists($htmlfile)) {
		$oldhtml = file_get_contents($htmlfile);
		$i = strpos($oldhtml, '<!-- Begin filter descriptions -->');
		if ($i !== false) {
			$prolog = substr($oldhtml, 0, $i);
		}
		$i = strpos($oldhtml, '<!-- End filter descriptions -->');
		if ($i !== false) {
			$epilog = trim(substr($oldhtml, $i + 32));
		}
		preg_match_all('|<!-- description(.+?)-(.+?) -->(.+?)<!--e-->|', $oldhtml, $matches);
		foreach ($matches[2] as $key => $filter) {
			$oldfilterlist[$filter]['desc'] = $matches[3][$key];
			$class = explode('.', trim($matches[1][$key], '()'));
			$oldfilterlist[$filter]['class'] = $class[0];
			$oldfilterlist[$filter]['subclass'] = $class[1];
		}
		preg_match_all('|<!-- classhead (.+?) -->(.+?)<!--e-->|', $oldhtml, $classheads);
		foreach ($classheads[1] as $key => $head) {
			$classes[$head] = $classheads[2][$key];
		}
		preg_match_all('|<!-- subclasshead (.+?) -->(.+?)<!--e-->|', $oldhtml, $subclassheads);
		foreach ($subclassheads[1] as $key => $head) {
			$subclasses[$head] = $subclassheads[2][$key];
		}
	}

	$filterDescriptions = array();
	$filterdesc = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter descriptions.txt';
	if (file_exists($filterdesc)) {
		$t = file_get_contents($filterdesc);
		$t = explode("\n", $t);
		foreach ($t as $d) {
			$d = trim($d);
			if (!empty($d)) {
				$f = explode(':=', $d);
				$filterDescriptions[$f[0]] = trim($f[1]);
			}
		}
	}

	$stdExclude = Array('Thumbs.db', 'readme.md', 'data');
	$lcFilesystem = file_exists(strtoupper(__FILE__));

	getResidentZPFiles(SERVERPATH . '/' . ZENFOLDER, $lcFilesystem, $stdExclude);
	getResidentZPFiles(SERVERPATH . '/' . THEMEFOLDER, $lcFilesystem, $stdExclude);
	$key = array_search(SERVERPATH . '/' . ZENFOLDER . '/functions-filter.php', $_zp_resident_files);
	unset($_zp_resident_files[$key]);
	$key = array_search(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions.php', $_zp_resident_files);
	unset($_zp_resident_files[$key]);
	$filterlist = array();
	$useagelist = array();
	foreach ($_zp_resident_files as $file) {
		if (getSuffix($file) == 'php') {
			$size = filesize($file);
			$text = file_get_contents($file);
			$script = str_replace(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/', '<em>plugin</em>/', $file);
			$script = str_replace(SERVERPATH . '/' . ZENFOLDER . '/', '<!--sort first-->/', $script);
			$script = str_replace(SERVERPATH . '/' . THEMEFOLDER . '/', '<em>theme</em>/', $script);
			preg_match_all('|zp_apply_filter\s*\((.+?)\).?|', $text, $matches);
			foreach ($matches[1] as $paramsstr) {
				$filter = explode(',', $paramsstr);
				foreach ($filter as $key => $element) {
					$filter[$key] = myunQuote(trim($element));
				}
				$filtername = array_shift($filter);
				if (array_key_exists($filtername, $filterlist)) {
					$filterlist[$filtername][0][] = $script;
				} else {
					array_unshift($filter, array($script));
					$filterlist[$filtername] = $filter;
				}
			}
			preg_match_all('|zp_register_filter\s*\((.+?)\).?|', $text, $matches);
			foreach ($matches[1] as $paramsstr) {
				$filter = explode(',', $paramsstr);
				$filtername = myunQuote(array_shift($filter));
				$useagelist[] = array('filter' => $filtername, 'script' => $script, 'scriptsize' => $size);
			}
		}
	}
	$useagelist = sortMultiArray($useagelist, 'scriptsize', false, false, false);

	$filterCategories = array();
	$newfilterlist = array();
	foreach ($filterlist as $key => $params) {
		if (count($params[0])) {
			sort($params[0]);
			$calls = array();
			$class = '';
			$subclass = '';
			$lastscript = $params[0][0];
			$count = 0;
			foreach ($params[0] as $script) {
				if (!$class) {
					$basename = basename($script);
					if (strpos($script, '<em>theme</em>') !== false) {
						$class = 'Theme';
						$subclass = 'Script';
					} else if (strpos($basename, 'user') !== false || strpos($basename, 'auth') !== false ||
									strpos($basename, 'logon') !== false || strpos($key, 'login') !== false) {
						$class = 'User_management';
						$subclass = 'Miscellaneous';
					} else if (strpos($key, 'upload') !== false) {
						$class = 'Upload';
						$subclass = 'Miscellaneous';
					} else if (strpos($key, 'texteditor') !== false) {
						$class = 'Miscellaneous';
						$subclass = 'Miscellaneous';
					} else if (strpos($basename, 'class') !== false) {
						$class = 'Object';
						if (strpos($basename, 'zenpage') !== false) {
							$class = 'Object';
							$subclass = 'CMS';
						} else {
							if (!$subclass) {
								switch ($basename) {
									case 'classes.php':
										$subclass = 'Root_class';
										break;
									case 'load_objectClasses.php':
									case 'class-gallery.php':
										$subclass = 'Miscellaneous';
										break;
									case 'class-album.php':
									case 'class-image.php':
									case 'class-textobject.php':
									case 'class-textobject_core.php':
									case 'class-Anyfile.php';
									case 'class-video.php':
									case 'Class-WEBdocs.php':
										$subclass = 'Media';
										break;
									case 'class-comment.php':
										$subclass = 'Comments';
										break;
									case 'class-search.php':
										$subclass = 'Search';
										break;
								}
								if (strpos($key, 'image') !== false || strpos($key, 'album') !== false) {
									$subclass = 'Media';
								}
							}
						}
					} else if (strpos($script, 'admin') !== false) {
						$class = 'Admin';
						if (strpos($script, 'zenpage') !== false) {
							$subclass = 'CMS';
						} else if (strpos($basename, 'comment') !== false || strpos($key, 'comment')) {
							$subclass = 'Comment';
						} else if (strpos($basename, 'edit') !== false || strpos($key, 'album') !== false || strpos($key, 'image') !== false) {
							$subclass = 'Media';
						}
					} else if (strpos($script, 'template') !== false) {
						$class = 'Template';
					} else if (strpos($basename, 'zenpage') !== false) {
						$class = 'CMS';
					} else {
						$class = 'Miscellaneous';
					}
					if (!$subclass) {
						$subclass = 'Miscellaneous';
					}
					if (array_key_exists($key, $oldfilterlist)) {
						if ($class != $oldfilterlist[$key]['class'] || $subclass != $oldfilterlist[$key]['subclass']) {
							$class = $oldfilterlist[$key]['class'];
							$subclass = $oldfilterlist[$key]['subclass'];
						}
					}
					if (!array_key_exists($class, $filterCategories)) {
						$filterCategories[$class] = array('class' => $class, 'subclass' => '', 'count' => 0);
					}
					if (!array_key_exists($class . '_' . $subclass, $filterCategories)) {
						$filterCategories[$class . '_' . $subclass] = array('class' => $class, 'subclass' => $subclass, 'count' => $filterCategories[$class]['count'] ++);
					}
					if (!array_key_exists('*' . $class, $filterDescriptions)) {
						$filterDescriptions['*' . $class] = '';
					}
					if (!array_key_exists('*' . $class . '.' . $subclass, $filterDescriptions)) {
						$filterDescriptions['*' . $class . '.' . $subclass] = '';
					}
				}
				if ($script == $lastscript) {
					$count ++;
				} else {
					if ($count > 1) {
						$count = " ($count)";
					} else {
						$count = '';
					}
					$calls[] = $lastscript . $count;
					$count = 1;
					$lastscript = $script;
				}
			}
			if ($count > 0) {
				if ($count > 1) {
					$count = " ($count)";
				} else {
					$count = '';
				}
				$calls[] = $lastscript . $count;
			}
		}
		array_shift($params);
		$newparms = array();
		foreach ($params as $param) {
			switch ($param) {
				case 'true':
				case 'false':
					$newparms[] = 'bool';
					break;
				case '$this':
					$newparms[] = 'object';
					break;
				default:
					if (substr($param, 0, 1) == '$') {
						$newparms[] = trim($param, '$');
					} else {
						$newparms[] = 'string';
					}
					break;
			}
		}
		$newfilterlist[$key] = array('filter' => $key, 'calls' => $calls, 'users' => array(), 'params' => $newparms, 'desc' => '*Edit Description*', 'class' => $class, 'subclass' => $subclass);
		if (!array_key_exists($key, $filterDescriptions)) {
			$filterDescriptions[$key] = '';
		}
	}

	foreach ($useagelist as $use) {
		if (array_key_exists($use['filter'], $newfilterlist)) {
			$newfilterlist[$use['filter']]['users'][] = $use['script'];
		}
	}

	$newfilterlist = sortMultiArray($newfilterlist, array('class', 'subclass', 'filter'), false, false);

	$f = fopen($htmlfile, 'w');
	$class = $subclass = NULL;
	if ($prolog) {
		fwrite($f, $prolog);
	}
	fwrite($f, "<!-- Begin filter descriptions -->\n");
	$ulopen = false;
	foreach ($newfilterlist as $filter) {
		if (array_key_exists($filter['filter'], $filterDescriptions) && $filterDescriptions[$filter['filter']] != '*dummy') {
			if ($class !== $filter['class']) {
				$class = $filter['class'];
				if (array_key_exists('*' . $class, $filterDescriptions)) {
					$classhead = '<p>' . $filterDescriptions['*' . $class] . '</p>';
				} else {
					$classhead = '';
				}
				if ($subclass) {
					fwrite($f, "\t\t\t</ul><!-- filterdetail -->\n");
				}
				fwrite($f, "\t" . '<h5><span id="' . $class . '"></span>' . $class . " filters</h5>\n");
				fwrite($f, "\t" . '<!-- classhead ' . $class . ' -->' . $classhead . "<!--e-->\n");
				$subclass = NULL;
			}
			if ($subclass !== $filter['subclass']) { // new subclass
				if (!is_null($subclass)) {
					fwrite($f, "\t\t\t</ul><!-- filterdetail -->\n");
				}
				$subclass = $filter['subclass'];
				if (array_key_exists('*' . $class . '.' . $subclass, $filterDescriptions)) {
					$subclasshead = '<p>' . $filterDescriptions['*' . $class . '.' . $subclass] . '</p>';
				} else {
					$subclasshead = '';
				}
				if ($subclass && $filterCategories[$class]['count'] > 1) { //	Class doc is adequate.
					fwrite($f, "\t\t\t" . '<h6 class="filter"><span id="' . $class . '_' . $subclass . '"></span>' . $subclass . "</h6>\n");
					fwrite($f, "\t\t\t" . '<!-- subclasshead ' . $class . '.' . $subclass . ' -->' . $subclasshead . "<!--e-->\n");
				}
				fwrite($f, "\t\t\t" . '<ul class="filterdetail">' . "\n");
			}
			fwrite($f, "\t\t\t\t" . '<li class="filterdetail">' . "\n");
			fwrite($f, "\t\t\t\t\t" . '<p class="filterdef"><span class="inlinecode"><strong>' . html_encode($filter['filter']) . '</strong></span>(<em>' . html_encode(implode(', ', $filter['params'])) . "</em>)</p>\n");
			if (array_key_exists($filter['filter'], $filterDescriptions)) {
				$filter['desc'] = '<p>' . $filterDescriptions[$filter['filter']] . '</p>';
			}
			fwrite($f, "\t\t\t\t\t" . '<!-- description(' . $class . '.' . $subclass . ')-' . $filter['filter'] . ' -->' . $filter['desc'] . "<!--e-->\n");

			$user = array_shift($filter['users']);
			if (!empty($user)) {
				fwrite($f, "\t\t\t\t\t" . '<p class="handlers">For example see ' . mytrim($user) . '</p>' . "\n");
			}
			fwrite($f, "\t\t\t\t\t" . '<p class="calls">Invoked from:' . "</p>\n");
			fwrite($f, "\t\t\t\t\t<ul><!-- calls -->\n");
			$calls = $filter['calls'];
			$limit = 4;
			foreach ($calls as $call) {
				$limit --;
				if ($limit > 0) {
					fwrite($f, "\t\t\t\t\t\t" . '<li class="call_list">' . mytrim($call) . "</li>\n");
				} else {
					fwrite($f, "\t\t\t\t\t\t<li>...</li>\n");
					break;
				}
			}
			fwrite($f, "\t\t\t\t\t" . "</ul><!-- calls -->\n");

			fwrite($f, "\t\t\t\t" . '</li><!-- filterdetail -->' . "\n");
		}
	}

	fwrite($f, "\t\t\t" . '</ul><!-- filterdetail -->' . "\n");
	fwrite($f, "<!-- End filter descriptions -->\n");
	if ($epilog) {
		fwrite($f, $epilog);
	}
	fclose($f);

	$filterCategories = sortMultiArray($filterCategories, array('class', 'subclass', 'text'), false, false);
	$indexfile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list_index.html';
	$f = fopen($indexfile, 'w');
	fwrite($f, "\t<ul>\n");
	$liopen = $ulopen = false;
	foreach ($filterCategories as $element) {
		$class = $element['class'];
		$subclass = $element['subclass'];
		if ($subclass == '') { // this is a new class element
			$count = $element['count'];
			if ($ulopen) {
				fwrite($f, "\t\t</ul>\n");
				$ulopen = false;
			}
			if ($liopen) {
				fwrite($f, "\t\t</li>\n");
				$liopen = false;
			}
			fwrite($f, "\t\t" . '<li><a title="' . $class . ' filters" href="#' . $class . '">' . $class . " filters</a>\n");
			$liopen = true;
		} else {
			if ($class != $subclass) {
				if ($count > 1) {
					if (!$ulopen) {
						fwrite($f, "\t\t<ul>\n");
						$ulopen = true;
					}
					fwrite($f, "\t\t\t\t" . '<li><a title="' . $subclass . ' ' . $class . ' filters" href="#' . $class . '_' . $subclass . '">' . $subclass . ' ' . str_replace('_', ' ', strtolower($class)) . " filters</a></li>\n");
				} else {
					unset($filterDescriptions['*' . $class . '.' . $subclass]);
				}
			}
		}
	}
	if ($ulopen) {
		fwrite($f, "\t\t</ul>\n");
	}
	if ($liopen) {
		fwrite($f, "\t\t</li>\n");
	}
	fwrite($f, "\t</ul>\n");
	fclose($f);

	$f = fopen($filterdesc, 'w');
	asort($filterDescriptions);
	foreach ($filterDescriptions as $filter => $desc) {
		fwrite($f, $filter . ':=' . $desc . "\n");
	}
	fclose($f);
}

function mytrim($str) {
	return trim(str_replace('<!--sort first-->/', '', $str));
}

function myunQuote($string) {
	$string = trim($string);
	$q = substr($string, 0, 1);
	if ($q == '"' || $q == "'") {
		$string = str_replace($q . '.$', '$', trim($string, $q));
	}
	return $string;
}
