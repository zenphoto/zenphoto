<?php
/** downloadList functions
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage downloadList plugin
 */
if(isset($_GET['download'])) {
	if(isset($_GET['albumzip'])) {
		$item = sanitize($_GET['download']);
	} else {
		$item = sanitize_numeric($_GET['download']);
	}
	if(empty($item) OR !getOption('zp_plugin_downloadList')) {
		die();
	}
	// script from php.net
	if(isset($_GET['albumzip'])) {
		updatedownloadListItemCount($item.'.zip');
		$location = WEBPATH ."/". ZENFOLDER . "/album-zip.php?album=".pathurlencode($item);
		header("Location: $location");
		exit;
	} else {
		$file = getDownloadItemPath($item);
		if(file_exists($file)) {
			updatedownloadListItemCount($file);
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename(urldecode($file)));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			//header('Content-Length: ' . filesize($file)); // This causes corrupted files on my server
			flush();
			readfile($file);
			exit;
		}
	}
}

/**
 * Updates the download count entry when processing a download. For internal use.
 * @param string $path Path of the download item
 * @param bool $nocountupdate false if the downloadcount should not be increased and only the entry be added to the db if it does not already exist
 */
function updatedownloadListItemCount($path) {
	$path = filesystemToInternal($path);
	$checkitem = query_single_row("SELECT `data` FROM ".prefix('plugin_storage')." WHERE `aux` = ".db_quote($path)." AND `type` = 'downloadList'");
	if($checkitem) {
		$downloadcount = $checkitem['data']+1;
		query("UPDATE ".prefix('plugin_storage')." SET `data` = ".$downloadcount.", `type` = 'downloadList' WHERE `aux` = ".db_quote($path)." AND `type` = 'downloadList'");
	}
}

/**
 * Adds a new download item to the database. For internal use.
 * @param string $path Path of the download item
 */
function adddownloadListItem($path) {
	$path = filesystemToInternal($path);
	$checkitem = query_single_row("SELECT `data` FROM ".prefix('plugin_storage')." WHERE `aux` = ".db_quote($path)." AND `type` = 'downloadList'");
	if(!$checkitem) {
		query("INSERT INTO ".prefix('plugin_storage')." (`type`,`aux`,`data`) VALUES ('downloadList',".db_quote($path).",'0')");
	}
}

/**Gets the download items from all download items from the database. For internal use in the downloadList functions.
 * @return array
 */
function getdownloadListItemsFromDB() {
	$downloaditems = query_full_array("SELECT id, `aux`, `data` FROM ".prefix('plugin_storage')." WHERE `type` = 'downloadList'");
	return $downloaditems;
}


/**Gets the download items from all download items from the database. For internal use in the downloadlink functions.
 * @return array
 */
function getdownloadListItemFromDB($file) {
	$downloaditem = query_single_row($sql = "SELECT id, `aux`, `data` FROM ".prefix('plugin_storage')." WHERE `type` = 'downloadList' AND `aux` = ".db_quote($file));

	return $downloaditem;
}

/**
 * Gets the id of a download item from the database for the download link. For internal use.
 * @param string $path Path of the download item (without WEBPATH)
 * @return bool|string
 */
function getDownloadItemID($path) {
	$path = sanitize($path);
	$downloaditem = query_single_row("SELECT id, `aux`, `data` FROM ".prefix('plugin_storage')." WHERE `type` = 'downloadList' AND `aux` = ".db_quote($path));
	if($downloaditem) {
		return $downloaditem['id'];
	} else {
		return false;
	}
}

/**
 * Gets the path of the download item via ID from the database. Used to process the download only. For internal use.
 * @param int $id Id of the download item
 * @return bool|string
 */
function getDownloadItemPath($id) {
	$id = sanitize_numeric($id);
	if(!empty($id)) {
		$path = query_single_row("SELECT `aux` FROM ".prefix('plugin_storage')." WHERE id=".$id);
		return internalToFilesystem($path['aux']);
	}
	return false;
}
/**
 *
 * returns formatted number of bytes. For internal use.
 * two parameters: the bytes and the precision (optional).
 * if no precision is set, function will determine clean
 * result automatically.
 * http://php.net/manual/de/function.filesize.php
 *
 * @author Martin Sweeny
 * @version 2010.0617
 *
 **/
function printdownloadList_formatBytes($b,$p = null) {
	$units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
	$c=0;
	$r='';
	if(!$p && $p !== 0) {
		foreach($units as $k => $u) {
			if(($b / pow(1024,$k)) >= 1) {
				$r["bytes"] = $b / pow(1024,$k);
				$r["units"] = $u;
				$c++;
			}
		}
		return number_format($r["bytes"],2) . " " . $r["units"];
	} else {
		return number_format($b / pow(1024,$p)) . " " . $units[$p];
	}
}

/**
 * Admin overview button for download statistics utility
 */
function downloadstatistics_button($buttons) {
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Download statistics'),
								'formname'=>'downloadstatistics_button',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/downloadList/download_statistics.php',
								'icon'=> WEBPATH.'/'.ZENFOLDER.'/images/bar_graph.png',
								'title'=>gettext('Counts of downloads'),
								'alt'=>'',
								'hidden'=> '',
								'rights'=> OVERVIEW_RIGHTS,
								);
	return $buttons;
}
?>