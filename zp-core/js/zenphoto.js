// JS support for general Zenphoto use

function toggle(x) {
	jQuery('#'+x).toggle();
}

function confirmDeleteAlbum(url) {
	if (confirm(deleteAlbum1)) {
		if (confirm(deleteAlbum2)) {
			window.location = url;
		}
	}
}

function confirmDelete(url,msg) {
	if (confirm(msg)) {
		window.location = url;
	}
}

function launchScript(script, params) {
	window.location = script+'?'+params.join('&');
}