<?php
/**
 * Provides the means to set an limit of the number of images that can be uploaded to an album in total.
 * Of course this is bypassed if using FTP upload or ZIP files!
 * If you want to limit the latter you need to use the quota_managment plugin additionally.
 * NOTE: The http browser single file upload is disabled if using this plugin!
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage usermanagement
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides the means to set an limit of the number of images that can be uploaded to an album from the admin upload tab.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.4.1';
$option_interface = 'uploadlimit';
zp_register_filter('upload_helper_js', 'uploadLimiterJS');
zp_register_filter('get_upload_header_text', 'uploadLimiterHeaderMessage');
zp_register_filter('upload_filetypes','limitUploadFiletypes');

/**
 * Option handler class
 *
 */
class uploadlimit {
	/**
	 * class instantiation function
	 *
	 */
	function uploadlimit() {
		setOptionDefault('imageuploadlimit', 999);
		setOptionDefault('imageuploadlimit_newalbum', 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Upload limit') => array('key' => 'imageuploadlimit', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The maximum number of images per album if uploading via the multifile upload.')),
		gettext('Disable new album creation') => array('key' => 'imageuploadlimit_newalbum', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('If checked users cannot create new albums.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

}


/**
 * Prints the jQuery JS setup for the upload limiting
 *
 * @return string
 */
function uploadLimiterJS($defaultJS) {
	$js = '';
	if(!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		$target = 'function uploadify_onSelectOnce(event, data) {';
		$i = strpos($defaultJS,$target);
		if ($i !== false) {
			$j = strpos($defaultJS,'}', $i+strlen($target));
			$body = trim(substr($defaultJS, $i+strlen($target),$j));
			if (!empty($dody)) {
				echo gettext("There is a plugin conflict with <em>image_upload_limiter</em>");
			}
			$js = substr($defaultJS,0,$i).substr($defaultJS,$j+1);
		}

		$albumlist = array();
		genAlbumUploadList($albumlist);
		$rootrights = accessAllAlbums(UPLOAD_RIGHTS);
		$uploadtype = zp_getcookie('uploadtype');
		$limitalbums = getUploadLimitedAlbums($albumlist);
		$imagenumber = getUploadImagesInAlbum($albumlist);
		if($uploadtype == "httpupload") {
			$js .= "$(document).ready(function() {
										$('#albumselect').hide();
							});";
		} else {
			$js .= "$(document).ready(function() {
										$('#uploadswitch').hide();
							});";
		}
		if(getOption('imageuploadlimit_newalbum')) {
			$js .= "
				jQuery(document).ready(function() {
					$('#newalbumbox,#albumtext').hide();
				});";
		}
	$js .= "
	function generateUploadlimit(selectedalbum,limitalbums) {
		$('#uploadlimitmessage').remove();
		var imagenumber = new Array(".$imagenumber.");
		var message = '';
		var uploadlimit = ".getOption('imageuploadlimit').";
		var imagesleft = '';
		$.each(limitalbums, function(key,value) {
			if(value == selectedalbum) {
				if(imagenumber[key] >= uploadlimit) {
					imagesleft = 0;
				} else if (imagenumber[key] < uploadlimit) {
					imagesleft = uploadlimit - imagenumber[key];
				}
				if(imagesleft === 0) {
			   	$('#fileUploadbuttons').hide();
			   	queuelimit = 0;
			   	message = '".gettext('The album exceeded the image number limit. You cannot upload more images!')."';
					//alert(message);
					$('#albumselect').prepend('<span id=\"uploadlimitmessage\" style=\"color:red;\">'+message+'<br /><br /></span>');
				} else {
					queuelimit = imagesleft;
					message = '".gettext("Maximum number of images left for this album: ")."'+imagesleft;
					//alert(message);
				 $('#albumselect').prepend('<span id=\"uploadlimitmessage\" style=\"color:green\">'+message+'<br /><br /></span>');
				}
			}
		});
		return queuelimit;
	}";

	/*
	if($uploadtype == "httpupload") {
		$js .= "$('#albumselect').hide();";
	}
	*/
	$js .= "var limitalbums = new Array(".$limitalbums.");";
	if(isset($_GET['album']) && !empty($_GET['album'])) { // if we upload
		$selectedalbum = sanitize($_GET['album']);
		$js .= "var selectedalbum = '".$selectedalbum."';";
	} else if($rootrights) {
		$js .= "var selectedalbum = '';"; // choose the first entry of the select list if nothing is selected and the user has root rights (so root no message...)
	} else {
		$js .= "var selectedalbum = limitalbums[0];"; // choose the first entry of the select list if nothing is selected and no rootrights
	}
	$js .= "
	var queuelimit = generateUploadlimit(selectedalbum,limitalbums);
	var value = '';
	var newalbum = '';

	$(document).ready(function() {
		// normal album selection
		$('#albumselectmenu').change(function() {
			$('#fileUpload').uploadifyClearQueue(); // to be sure that no selections for other albums are kept
			selectedalbum = $('#albumselectmenu').val();
			queuelimit = generateUploadlimit(selectedalbum,limitalbums);
		});
		// new toplevel album
		$('#albumtitle').keyup(function () {
				value = $('#albumtitle').val();
				if(value != '') {
					queuelimit = ".getOption('imageuploadlimit').";
				}
		});
		// new subalbum
		$('#newalbumcheckbox').change(function() {
			$('#albumtitle').keyup(function () {
				value = $('#albumtitle').val();
				queuelimit = ".getOption('imageuploadlimit').";
			});
		});
		$('#fileUpload').uploadifySettings('queueSizeLimit',".getOption('imageuploadlimit').");
	});
	";
	}
return $js;
}

function uploadLimiterHeaderMessage($default) {
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		return $default;
	}
	$warn = "";
	$uploadtype = zp_getcookie('uploadtype');
	if($uploadtype == "httpupload") {
		$warn = '<p style="color:red;">'.gettext('HTTP single file uploading is disabled because upload limitations are in effect. Please use the <a href="javascript:switchUploader(\'admin-upload.php?uploadtype=multifile\');" >multi file upload</a>').'</p>';
	} else {
		$uploadlimit = getOption('imageuploadlimit');
		$warn = '<p style="color: green">'. sprintf(gettext("<strong>Note:</strong> You can upload a maximum of %s images to each album."),$uploadlimit).'</p>';
	}
	return $warn;
}

/*
 * Prints a list of all albums for a JS array
 *
 * @param array $albumslist the array of the albums as generated by genAlbumUploadList()
 * @return string
 */
function getUploadLimitedAlbums($albumlist) {
	$limitedalbums = array();
	foreach($albumlist as $key => $value) {
		$obj = new Album($gallery,$key);
		$limitedalbums[] = $obj->name;
	}
	$numalbums = count($limitedalbums);
	$content = $count = '';
	foreach($limitedalbums as $album) {
		$content .= "'";
		$count++;
		$content .= $album;
		if($count < $numalbums) {
			$content .= "',"; // js array entry end
		} else {
			$content .= "'"; // js array end
		}

	}
	return $content;
}

/*
 * Prints the number of images within each album for a JS array
 *
 * @param array $albumslist the array of the albums as generated by genAlbumUploadList()
 * @return string
 */
function getUploadImagesInAlbum($albumlist) {
	$numbers = array();
	foreach($albumlist as $key => $value) {
		$obj = new Album($gallery,$key);
		$numbers[] = $obj->getNumImages();
	}
	$numimages = count($numbers);
	$content = $count = '';
	foreach($numbers as $number) {
		$content .= "'";
		$count++;
		$content .= $number;
		if($count < $numimages) {
			$content .= "',"; // js array entry end
		} else {
			$content .= "'"; // js array end
		}
	}
	return $content;
}

/**
 * Removes ZIP from list of upload suffixes
 * @param array $types
 * @return array
 */
function limitUploadFiletypes($types) {
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		return $types;
	}
	$key = array_search('ZIP', $types);
	unset($types[$key]);
	return $types;
}