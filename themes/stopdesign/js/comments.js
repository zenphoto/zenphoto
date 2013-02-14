/*
The hide/show code is based on an example from the article "Unobtrusive show/hide behavior reloaded" by Bobby van der Sluis
URL: http://www.bobbyvandersluis.com/articles/unobtrusiveshowhide.php
*/

// Hide all toggleable sections with JavaScript for the highly improbable case that CSS is disabled
// Note that in this case the 'flash of visible content' still will occur
function initCommentState() {
  var showBtn = document.getElementById('showcomments');
  var hideBtn = document.getElementById('hidecomments');
  if ((showBtn) && (hideBtn)) {
    if (initstate != 1) {
      document.getElementById('comments').style.display = 'none';
      document.getElementById('hidecomments').style.display = 'none';
      document.getElementById('showcomments').style.display = 'inline';
    } else {
      document.getElementById('showcomments').style.display = 'none';
      document.getElementById('hidecomments').style.display = 'inline';
    }
    showBtn.onclick = function() {
      showComments();
      return false;
    }
    hideBtn.onclick = function() {
      hideComments();
      return false;
    }
  }
  var addComment = document.getElementById('addcomment');
  var addCommentButton = document.getElementById('addcommentbutton');
	if (initstate) {
	  addComment.style.display = 'block';
	  addCommentButton.style.display = 'none';
	} else {
	  addComment.style.display = 'none';
	  addCommentButton.style.display = 'block';
  }
  addCommentButton.onclick = function() {
    addComment.style.display = 'block';
    addCommentButton.style.display = 'none';
  }
	Thread.sleep(50);	//	Give GoogleMaps a chance to do its thing on the DOM
}

function hideComments() {
  var now = createExpires();
  document.getElementById('comments').style.display = 'none';
  document.getElementById('showcomments').style.display = 'inline';
  document.getElementById('hidecomments').style.display = 'none';
  setCookie('showcomments', 0, now, '/', '', '');
}

function showComments() {
  var now = createExpires();
  document.getElementById('comments').style.display = 'block';
  document.getElementById('showcomments').style.display = 'none';
  document.getElementById('hidecomments').style.display = 'inline';
  setCookie('showcomments', 1, now, '/', '', '');
}

function createExpires() {
  var now = new Date();
  fixDate(now);
  now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
  now = now.toGMTString();
  return now;
}
