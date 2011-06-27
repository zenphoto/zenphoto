<?php
/**
 *
 * A plugin to generate a file download list.
 * This download folder can be relative to your Zenphoto root ("foldername") or external to it ("../foldername").
 * By default the "uploaded" folder is chosen so you can use the file manager to manage those files.
 *
 * You can also override that folder by using the printdownloadList() function parameters directly. Additionally
 * you can set a downloadlink to a specific file directly as well using printDownloadLink('path-to-file');
 *
 * The file names and the download path of the items are stored with the number of downloads in the database's plugin_storage table.
 *
 * The download link is something like:
 * http://www.yourdomain.com/download.php?file=<id number nof the download>.
 *
 * So the actual download source is not public. The list itself is generated directly from the file system. However, files which no longer exist are
 * kept in the database for statistical reasons until you clear them manually via the statistics utility.
 *
 * You will need to modify your theme to use this plugin. You can use the codeblock fields if your theme supports them or insert the function
 * calls directly where you want the list to appear.
 *
 * To protect the download directory from direct linking you need to set up a proper .htaccess for this folder.
 *
 * The list has a CSS class 'downloadList' attached.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @tags "file download", "download manager", download
 */
$plugin_is_filter = 20|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Plugin to generate file download lists. The source of these files may be anywhere that can be accessed by server scripts, it need not be part of your Zenphoto installation.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.4.1';
$plugin_URL = "";
$plugin_disable = (version_compare(PHP_VERSION, '5.0.0') != 1) ? gettext('PHP version 5 or greater is required.') : false;
if ($plugin_disable) {
	setOption('zp_plugin_downloadList',0);
}
$option_interface = "downloadListOptions";
zp_register_filter('admin_utilities_buttons', 'downloadstatistics_button');

/**
 * Plugin option handling class
 *
 */
class downloadListOptions {

	function downloadListOptions() {
		setOptionDefault('downloadList_directory', 'uploaded');
		setOptionDefault('downloadList_showfilesize', 1);
		setOptionDefault('downloadList_showdownloadcounter', 1);
	}

	function getOptionsSupported() {
		return array(gettext('Download directory') => array('key' => 'downloadList_directory', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("This download folder can be relative to your Zenphoto installation (<em>foldername</em>) or external to it (<em>../foldername</em>)! You can override this setting by using the parameter of the printdownloadList() directly on calling.")),
								gettext('Show filesize of download items') => array('key' => 'downloadList_showfilesize', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => ''),
							  gettext('Show download counter of download items') => array('key' => 'downloadList_showdownloadcounter', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => ''),
							  gettext('Files to exclude from the download list') => array('key' => 'downloadList_excludesuffixes', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('A list of file suffixes to exclude. Separate with comma and omit the dot (e.g "jpg").'))
		);
	}

	function handleOption($option, $currentValue) {
	}
}

$_downloadList_linkpath = substr(urldecode(sanitize($_SERVER['REQUEST_URI'], 0)), strlen(WEBPATH)+1);


/**
 * Prints the actual download list included all subfolders and files
 * @param string $dir An optional different folder to generate the list that overrides the folder set on the option.
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @param array $filters an array of files to exclude from the list. Standard items are Array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn')
 * @param array $excludesuffixes an array of file suffixes (without trailing dot to exclude from the list (e.g. "jpg")
 * @param string $sort 'asc" or "desc" (default) for alphabetical ascending or descending list
 */
function printdownloadList($dir='',$listtype='ol',$filters = array(),$excludesuffixes='',$sort='desc') {
	global $_zp_themeroot;
	if($listtype != 'ol' || $listtype != 'ul') {
		$listtype = 'ol';
	}
	$files = getdownloadList($dir,$filters,$excludesuffixes,$sort);
	echo '<'.$listtype.' class="downloadList">';
	printdownloadListArray($files,$listtype);
	echo '</'.$listtype.'>';
}

/**
 * Gets the actual download list included all subfolders and files
 * @param string $dir An optional different folder to generate the list that overrides the folder set on the option.
 * 										This could be a subfolder of the main download folder set on the plugin's options. You have to include the base directory as like this:
 * 										"folder" or "folder/subfolder" or "../folder"
 * 										You can also set any folder within or without the root of your Zenphoto installation as a download folder with this directly
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @param array $filters an array of files to exclude from the list. Standard items are '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'
 * @param array $excludesuffixes an array of file suffixes (without trailing dot to exclude from the list (e.g. "jpg")
 * @param string $sort 'asc" or "desc" (default) for alphabetical ascending or descending list
 * @return array
 */
function getdownloadList($dir,$filters,$excludesuffixes,$sort) {
	if(empty($dir)) {
		$dir = getOption('downloadList_directory');
	}
	if(empty($excludesuffixes)) {
		$excludesuffixes = getOption('downloadList_excludesuffixes');
	}
	if(empty($excludesuffixes)) {
		$excludesuffixes = array();
	} elseif(!is_array($excludesuffixes)) {
		$excludesuffixes = explode(',',$excludesuffixes);
	}
	if($sort == 'asc') {
	  $direction = 0;
	} else {
		$direction = 1;
	}
	$dirs = array_diff(scandir($dir,$direction),array_merge(Array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'), $filters ));
	$dir_array = Array();
	if($sort == 'asc') {
	  natsort($dirs);
	}
	foreach($dirs as $file) {
		if(is_dir($dir.'/'.$file)) {
			$dir_array[$file] = getdownloadList($dir."/".$file, $filters,$excludesuffixes);
		} else {
			if(!in_array(getSuffix($file),$excludesuffixes)) {
				$dir_array[$file] = $dir.'/'.$file;
			}
		}
	}
	return $dir_array;
}

/**
 * @param array $array List of download items
 * @param string $listtype "ol" or "ul" for the type of HTML list you want to use
 * @return array
 */
function printdownloadListArray($array,$listtype='ol') {
	if($listtype != 'ol' || $listtype != 'ul') {
		$listtype = 'ol';
	}
	$filesize = '';
	foreach($array as $key=>$file) {
	?>
	<li>
	<?php
	if(is_array($file)) { // for sub directories
		echo $key;
	} else {
		printDownloadLink($file);
	}
	if(is_array($file)) {
		echo '<'.$listtype.'>';
		printdownloadListArray($file,$listtype);
		echo '</'.$listtype.'>';
	}
	?>
	</li>
	<?php
	}
}

/**
 * Gets the download url for a file
 * @param string $file the path to a file to get a download link.
 */
function getDownloadLink($file) {
	global $_downloadList_linkpath;
	adddownloadListItem($file); // add item to db if not already exists without updating the counter
	$link = '';
	if($id = getDownloadItemID($file)) {
		if (strpos($_downloadList_linkpath,'?') === false) {
			$link = FULLWEBPATH.'/'.$_downloadList_linkpath.'?download='.$id;
		} else {
			$link = FULLWEBPATH.'/'.$_downloadList_linkpath.'&amp;download='.$id;
		}
	}
	return $link;
}

/**
 * Prints a download link for a file, depending on the plugin options including the downloadcount and filesize
 * @param string $file the path to a file to print a download link.
 * @param string $linktext Optionally how you wish to call the link. Set/leave  to NULL to use the filename.
 */
function printDownloadLink($file,$linktext=NULL) {
	$filesize = '';
	if(getOption('downloadList_showfilesize')) {
		$filesize = filesize($file);
		$filesize = ' ('.printdownloadList_formatBytes($filesize).')';
	}
	if(getOption('downloadList_showdownloadcounter')) {
		$downloaditem = getdownloadListItemFromDB($file);
		if($downloaditem) {
			$downloadcount = ' - '.sprintf(ngettext('%u download','%u downloads',$downloaditem['data']),$downloaditem['data']);
		} else {
			$downloadcount = ' - 0 downloads';
		}
		$filesize .= $downloadcount;
	}
	if(empty($linktext)) {
		$filename = html_encode(basename($file));
	} else {
		$filename = $linktext;
	}
	echo '<a href="'.getDownloadLink($file).'" rel="nofollow">'.$filename.'</a><small>'.$filesize.'</small>';
}

/**
 * Prints a download link for an album zip of the current album (therefore to be used only on album.php/image.php). This function only creates a download count and then redirects to the original Zenphoto album zip download.
 */
function printDownloadLinkAlbumZip($linktext='',$albumobj='') {
	global $_zp_current_album, $_downloadList_linkpath;
	if (!is_null($albumobj) && !$albumobj->isDynamic()) {
		$file = $albumobj->name.'.zip';
		adddownloadListItem($file);
		$filesize = '';
	 	/*	if(getOption('downloadList_showfilesize')) {
			$filesize = filesize($file);
			$filesize = ' ('.printdownloadList_formatBytes($filesize).')';
		} */
		if(getOption('downloadList_showdownloadcounter')) {
			$downloaditem = getdownloadListItemFromDB($file);
			if($downloaditem) {
				$downloadcount = ' - '.sprintf(ngettext('%u download','%u downloads',$downloaditem['data']),$downloaditem['data']);
			} else {
				$downloadcount = ' - 0 downloads';
			}
			/*
			foreach ($downloaditems as $item) {
				$file = filesystemToInternal($file);
				if($file == $item['aux']) {
					$downloadcount = ' - '.sprintf(ngettext('%u download','%u downloads',$item['data']),$item['data']);
					break;
				}
			} */
			$filesize .= $downloadcount;
		}
		if(!empty($linktext)) {
			$file = $linktext;
		}
		if (strpos($_downloadList_linkpath, '?') === false) {
			$link = WEBPATH.'/'.$_downloadList_linkpath.'?download='.pathurlencode($albumobj->name).'&amp;albumzip';
		} else {
			$link = WEBPATH.'/'.$_downloadList_linkpath.'&amp;download='.pathurlencode($albumobj->name).'&amp;albumzip';
		}
		echo '<a href="'.$link.'" rel="nofollow">'.html_encode($file).'</a><small>'.$filesize.'</small>';
	}
}

require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/downloadList/downloadList-functions.php');
?>