/** support for admin-upload **/

function updateFolder(nameObj, folderID, checkboxID, msg1, msg2) {
	var autogen = document.getElementById(checkboxID).checked;
	var folder = document.getElementById(folderID);
	var parentfolder = document.getElementById('albumselectmenu').value;
	if (parentfolder != '') parentfolder += '/';
	var name = nameObj.value;
	var fname = "";
	var fnamesuffix = "";
	var count = 1;
	if (autogen && name != "") {
		fname = soejs(name);
		while (contains(albumArray, parentfolder + fname + fnamesuffix)) {
			fnamesuffix = "-"+count;
			count++;
		}
	}
	folder.value = parentfolder + fname + fnamesuffix;
	return validateFolder(folder, msg1, msg2);
}

function switchUploader(url) {
	var urlx = url+'&album='+$('#albumselectmenu').val()+'&publishalbum='+$('#publishalbum').attr("checked")+
									'&albumtitle='+encodeURIComponent($('#albumtitle').val())+'&folderdisplay='+encodeURIComponent($('#folderdisplay').val())+
									'&autogen='+$('#autogen').attr("checked");
	if ($('#newalbumcheckbox').attr("checked")) urlx = urlx+'&new';
	window.location = urlx;
}
