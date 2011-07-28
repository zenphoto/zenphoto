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
$option_interface = "downloadListOptions";
zp_register_filter('admin_utilities_buttons', 'downloadstatistics_button');
zp_register_filter('custom_option_save', 'download_list_custom_options_save');

/**
 * Plugin option handling class
 *
 */
class downloadListOptions {

	function downloadListOptions() {
		setOptionDefault('downloadList_directory', 'uploaded');
		setOptionDefault('downloadList_showfilesize', 1);
		setOptionDefault('downloadList_showdownloadcounter', 1);
		setOptionDefault('downloadList_user', NULL);
		setOptionDefault('downloadList_pass', NULL);
		setOptionDefault('downloadList_hint', NULL);
		setOptionDefault('downloadList_rights', NULL);
	}

	function getOptionsSupported() {
		$options = array(gettext('Download directory') => array('key' => 'downloadList_directory', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 2,
												'desc' => gettext("This download folder can be relative to your Zenphoto installation (<em>foldername</em>) or external to it (<em>../foldername</em>)! You can override this setting by using the parameter of the printdownloadList() directly on calling.")),
										gettext('Show filesize of download items') => array('key' => 'downloadList_showfilesize', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 3,
												'desc' => ''),
									  gettext('Show download counter of download items') => array('key' => 'downloadList_showdownloadcounter', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 4,
									  		'desc' => ''),
									  gettext('Files to exclude from the download list') => array('key' => 'downloadList_excludesuffixes', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 5,
									  		'desc' => gettext('A list of file suffixes to exclude. Separate with comma and omit the dot (e.g "jpg").')),
									  gettext('User rights') => array('key' => 'downloadList_rights', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 1,
									  		'desc' => gettext('Check if users are required to have <em>file</em> rights to download.'))
		);
		if (GALLERY_SECURITY != 'private') {
			$options[chr(0x00)] = array('key' => 'downloadlist_credentials', 'type' => OPTION_TYPE_CUSTOM,
																	'order' => 0,
																	'desc' => gettext('Provide credentials to password protect downloads'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$user = getOption('downloadList_user');
		$x = getOption('downloadList_pass');
		$hint = getOption('downloadList_hint');
		?>
		<input type="hidden" name="password_enabled_downloadList" id="password_enabled_downloadList" value="0" />
		<p class="password_downloadListextrashow">
		<a href="javascript:toggle_passwords('_downloadList',true);">
			<?php echo gettext("Password:"); ?>
		</a>
		<?php
		if (empty($x)) {
			?>
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/lock_open.png" alt="" class="icon-postiion-top8" />
			<?php
		} else {
			$x = '          ';
			?>
			<a onclick="resetPass();" title="<?php echo gettext('clear password'); ?>"><img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/lock.png"  alt="" class="icon-postiion-top8" /></a>
			<?php
		}
		?>
	</p>
	<div class="password_downloadListextrahide" style="display:none">
		<a href="javascript:toggle_passwords('_downloadList',false);">
		<?php echo gettext("Guest user:"); ?>
		</a>
		<br />
		<input type="hidden" id="olduser" name="olduser" value="<?php echo html_encode($user); ?>" />
		<input type="text" size="27" id="user_name" name="downloadList_user" value="<?php echo html_encode($user); ?>" />
		<br />
		<?php echo gettext("Password:"); ?>
		<br />
		<input type="password" size="27" id="pass" name="downloadList_pass" value="<?php echo $x; ?>" />
		<br />
		<?php echo gettext("(repeat)"); ?>
		<br />
		<input type="password" size="27" id="pass_2" name="downloadList_pass_2" value="<?php echo $x; ?>" />
		<br />
		<?php echo gettext("Password hint:"); ?>
		<br />
		<?php print_language_string_list($hint, 'downloadList_hint', false, NULL, 'hint', 27); ?>
	</div>
	<?php

	}
}

function download_list_custom_options_save($notify,$themename,$themealbum) {
	global $gallery, $_zp_authority;
	if (sanitize(@$_POST['password_enabled_downloadList'], 3)) {
		$olduser = getOption('downloadList_user');
		$newuser = trim(sanitize($_POST['downloadList_user'],3));
		if (!empty($newuser)) {
			$gallery->setUserLogonField(1);
			$gallery->save();
		}
		$fail = false;
		$pwd = trim(sanitize($_POST['downloadList_pass']));
		if ($olduser != $newuser) {
			if (!empty($newuser) && empty($pwd) && empty($pwd2)) $fail = true;
		}
		if (!$fail && $_POST['downloadList_pass'] == $_POST['downloadList_pass_2']) {
			setOption('downloadList_user',$newuser);
			if (empty($pwd)) {
				if (empty($_POST['downloadList_pass'])) {
					setOption('downloadList_pass', NULL);  // clear the protected image password
				}
			} else {
				setOption('downloadList_pass', $_zp_authority->passwordHash($newuser, $pwd));
			}
		} else {
			$notify .= gettext('passwords did not match').'<br />';
		}
		setOption('downloadList_hint', process_language_string_save('downloadList_hint', 3));
	}

	return $notify;
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