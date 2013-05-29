/* Zenphoto administration javascript. */

function albumSwitch(sel, unchecknewalbum, msg1, msg2) {
	var selected = sel.options[sel.selectedIndex];
	var albumtext = document.getElementById("albumtext");
	var publishtext = document.getElementById("publishtext");
	var albumbox = document.getElementById("folderdisplay");
	var titlebox = document.getElementById("albumtitle");
	var checkbox = document.getElementById("autogen");
	var newalbumbox = sel.form.newalbum;
	var folder = document.getElementById("folderslot");
	var exists = document.getElementById("existingfolder");

	if (selected.value == "") {
		newalbumbox.checked = true;
		newalbumbox.disabled = true;
		newalbumbox.style.display = "none";
	} else {
		if (unchecknewalbum) {
			newalbumbox.checked = false;
		}
		newalbumbox.disabled = false;
		newalbumbox.style.display = "";
	}

	var newalbum = selected.value == "" || newalbumbox.checked;
	if (newalbum) {
		albumtext.style.display = "block";
		publishtext.style.display = "block";
		albumbox.value = "";
		folder.value = "";
		titlebox.value = "";
		exists.value = "false";
		checkbox.checked = true;
		document.getElementById("foldererror").style.display = "none";
		toggleAutogen("folderdisplay", "albumtitle", checkbox);
	} else {
		albumtext.style.display = "none";
		publishtext.style.display = "none";
		albumbox.value = selected.value;
		folder.value = selected.value;
		titlebox.value = selected.text;
		exists.value = "true";
	}

	var rslt = validateFolder(folder, msg1, msg2);
	return rslt;
}


function contains(arr, key) {
	var i;
	for (i=0; i<arr.length; i++) {
		if (arr[i].toLowerCase() == key.toLowerCase()) {
			return true;
		}
	}
	return false;
}

function validateFolder(folderObj, msg1, msg2) {
	var errorDiv = document.getElementById("foldererror");
	var exists = $('#existingfolder').val() != "false";
	var folder = folderObj.value;
	$('#folderslot').val(folder);
	if (!exists && albumArray && contains(albumArray, folder)) {
		errorDiv.style.display = "block";
		errorDiv.innerHTML = msg1;
		return false;
	} else if ((folder == "") || folder.substr(folder.length-1, 1) == '/') {
		errorDiv.style.display = "block";
		errorDiv.innerHTML = msg2;
		return false;
	} else {
		errorDiv.style.display = "none";
		errorDiv.innerHTML = "";
		return true;
	}
}

function toggleAutogen(fieldID, nameID, checkbox) {
	var field = document.getElementById(fieldID);
	var name = document.getElementById(nameID);
	if (checkbox.checked) {
		window.folderbackup = field.value;
		field.disabled = true;
		return updateFolder(name, fieldID, checkbox.id);
	} else {
		if (window.folderbackup && window.folderbackup != "")
			field.value = window.folderbackup;
			field.disabled = false;
		return true;
	}
}


// Checks all the checkboxes in a group (with the specified name);
function checkAll(form, arr, mark) {
	var i;
	for (i = 0; i <= form.elements.length; i++) {
		try {
			if(form.elements[i].name == arr) {
				form.elements[i].checked = mark;
			}
		} catch(e) {}
	}
}

function triggerAllBox(form, arr, allbox) {
	var i;
	for (i = 0; i <= form.elements.length; i++) {
		try {
			if(form.elements[i].name == arr) {
				if(form.elements[i].checked == false) {
					allbox.checked = false; return;
				}
			}
		}
		catch(e) {}
	}
	allbox.checked = true;
}


function toggleBigImage(id, largepath) {
	var imageobj = document.getElementById(id);
	if (!imageobj.sizedlarge) {
		imageobj.src2 = imageobj.src;
		imageobj.src = largepath;
		imageobj.style.position = 'absolute';
		imageobj.style.zIndex = '1000';
		imageobj.sizedlarge = true;
	} else {
		imageobj.style.position = 'relative';
		imageobj.style.zIndex = '0';
		imageobj.src = imageobj.src2;
		imageobj.sizedlarge = false;
	}
}


function updateThumbPreview(selectObj) {
	if (selectObj) {
		var thumb = selectObj.options[selectObj.selectedIndex].style.backgroundImage;
		selectObj.style.backgroundImage = thumb;
	}
}

function update_direction(obj, element1, element2) {
	no = obj.options[obj.selectedIndex].value;
	switch (no) {
		case 'custom':
			$('#'+element1).show();
			$('#'+element2).show();
			break;
		case 'manual':
		case 'random':
		case '':
			$('#'+element1).hide();
			$('#'+element2).hide();
			break;
		default:
			$('#'+element1).show();
			$('#'+element2).hide();
			break;
	}
}

// Uses jQuery
function deleteConfirm(obj, id, msg) {
	if (confirm(msg)) {
		$('#deletemsg'+id).show();
		$('#'+obj).prop('checked',true);
	} else {
		$('#'+obj).prop('checked',false);
	}
}


// Uses jQuery
// Toggles the interface for move/copy (select an album) or rename (text
// field for new filename) or none.
function toggleMoveCopyRename(id, operation) {
	jQuery('#movecopydiv-'+id).hide();
	jQuery('#renamediv-'+id).hide();
	jQuery('#deletemsg'+id).hide();
	jQuery('#move-'+id).prop('checked',false);
	jQuery('#copy-'+id).prop('checked',false);
	jQuery('#rename-'+id).prop('checked',false);
	jQuery('#Delete-'+id).prop('checked',false);
	if (operation == 'copy') {
		jQuery('#movecopydiv-'+id).show();
		jQuery('#copy-'+id).prop('checked',true);
	} else if (operation == 'move') {
		jQuery('#movecopydiv-'+id).show();
		jQuery('#move-'+id).prop('checked',true);
	} else if (operation == 'rename') {
		jQuery('#renamediv-'+id).show();
		jQuery('#rename-'+id).prop('checked',true);
	}
}

function toggleAlbumMCR(prefix, operation) {
	jQuery('#Delete-'+prefix).prop('checked',false);
	jQuery('#deletemsg'+prefix).hide();
	jQuery('#a-'+prefix+'movecopydiv').hide();
	jQuery('#a-'+prefix+'renamediv').hide();
	jQuery('#a-'+prefix+'move').prop('checked',false);
	jQuery('#a-'+prefix+'copy').prop('checked',false);
	jQuery('#a-'+prefix+'rename').prop('checked',false);
	if (operation == 'copy') {
		jQuery('#a-'+prefix+'movecopydiv').show();
		jQuery('#a-'+prefix+'copy').prop('checked',true);
	} else if (operation == 'move') {
		jQuery('#a-'+prefix+'movecopydiv').show();
		jQuery('#a-'+prefix+'move').prop('checked',true);
	} else if (operation == 'rename') {
		jQuery('#a-'+prefix+'renamediv').show();
		jQuery('#a-'+prefix+'rename').prop('checked',true);
	}
}

// Toggles the extra info in the admin edit and options panels.
function toggleExtraInfo(id, category, show) {
	var prefix = '';
	if (id != null && id != '') {
		prefix = '#'+category+'-'+id+' ';
	}
	if (show) {
		jQuery(prefix+'.'+category+'extrainfo').show();
		jQuery(prefix+'.'+category+'extrashow').hide();
		jQuery(prefix+'.'+category+'extrahide').show();
	} else {
		jQuery(prefix+'.'+category+'extrainfo').hide();
		jQuery(prefix+'.'+category+'extrashow').show();
		jQuery(prefix+'.'+category+'extrahide').hide();
	}
}

// used to toggle fields
function showfield(obj, fld) {
	no = obj.options[obj.selectedIndex].value;
	document.getElementById(fld).style.display = 'none';
	if(no=='custom')
		document.getElementById(fld).style.display = 'block';
}

// password field hide/disable
function toggle_passwords(id, pwd_enable) {
	toggleExtraInfo('','password'+id,pwd_enable);
	if (pwd_enable) {
		jQuery('#password_enabled'+id).val('1');
	} else {
		jQuery('#password_enabled'+id).val('0');
	}
}

function resetPass(id) {
	$('#user_name'+id).val('');
	$('#pass'+id).val('');
	$('#pass_r'+id).val('');
	$('.hint'+id).val('');
	toggle_passwords(id,true);
}


// toggels the checkboxes for custom image watermarks
function toggleWMUse(id) {
	if (jQuery('#image_watermark-'+id).val() == '') {
		jQuery('#WMUSE_'+id).hide();
	} else {
		jQuery('#WMUSE_'+id).show();
	}
}

String.prototype.replaceAll = function(stringToFind,stringToReplace){
	var temp = this;
	var index = temp.indexOf(stringToFind);
	while(index != -1) {
		temp = temp.replace(stringToFind,stringToReplace);
		index = temp.indexOf(stringToFind);
	}
	return temp;
}


function addNewTag(id) {
	var tag;
	tag = $('#newtag_'+id).val();
	if (tag) {
		$('#newtag_'+id).val('');
		var name = id+tag;
		//htmlentities
		name = encodeURI(name);
		name = name.replaceAll('%20',	'__20__');
		name = name.replaceAll("'",		'__27__');
		name = name.replaceAll('.',		'__2E__');
		name = name.replaceAll('+',		'__20__');
		name = name.replaceAll('%',		'__25__');
		name = name.replaceAll('&', 	'__26__');
		var lcname = name.toLowerCase();
		var exists = $('#'+lcname).length;
		if (exists) {
			$('#'+lcname+'_element').remove();
		}
		html = '<li id="'+lcname+'_element"><label class="displayinline"><input id="'+lcname+'" name="'+name+
				'" type="checkbox" checked="checked" value="1" />'+tag+'</label></li>';
		$('#list_'+id).prepend(html);
	}
}

function xsrfWarning(id, msg) {
	if (!confirm(msg)) {
		$('#'+id+'_yes').prop('checked',false);
		$('#'+id+'_no').prop('checked',true);
	}
}
