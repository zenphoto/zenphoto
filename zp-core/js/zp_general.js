// JS support for general Zenphoto use

function toggle(x) {
	jQuery('#'+x).toggle();
}

//used in conjunction with the "reveal" php function
function reveal(id) {
	jQuery('#'+id+'_reveal').toggle();
	jQuery('#'+id+'_hide').toggle();
	jQuery('#'+id).toggle();
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



function addslashes (str) {
  // Escapes single quote, double quotes and backslash characters in a string with backslashes
  //
  // version: 1109.2015
  // discuss at: http://phpjs.org/functions/addslashes    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Ates Goral (http://magnetiq.com)
  // +   improved by: marrtins
  // +   improved by: Nate
  // +   improved by: Onno Marsman    // +   input by: Denny Wardhana
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +   improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
  // *     example 1: addslashes("kevin's birthday");
  // *     returns 1: 'kevin\'s birthday'
	return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}