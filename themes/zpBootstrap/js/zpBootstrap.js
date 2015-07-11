jQuery(document).ready(function ($) {
	/* responsive pictures */
	$('img.remove-attributes').each(function () {
		$(this).removeAttr('width');
		$(this).removeAttr('height');
	});

	/* errorbox */
	$('.errorbox').addClass('alert alert-error');
	$('.errorbox h2').replaceWith('<h4>' + $('.errorbox h2').html() + '</h4>');

	/* navigation */
	$('.navbar .nav li > a.active')
					.removeClass('active')
					.parent().addClass('active');
	$('div.pagination ul.pagination').removeClass('pagination');
	$('div.pagination span.disabledlink')
					.wrap('<a href="#"></a>')
					.parent().addClass('disabled');
	$('div.pagination li.current')
					.wrapInner('<a href="#"></a>')
					.addClass('active');

	/* favorites button */
	$('.favorites input[type="submit"]').addClass('btn btn-inverse');

	/* images */
	$('#rating form').addClass('bottom-margin-reset');
	$('#rating input[type="button"]').addClass('btn btn-inverse');

	/* news & pages */
	$('ul#news-cat-list').addClass('nav nav-pills nav-stacked');
	$('ul#news-cat-list li.active').wrapInner('<a href="#"></a>');
	$('ul#news-cat-list li a.active').parent().addClass('active');
	$('ul#latestnews').addClass('nav');
	$('ul.sub-nav li a.active').parent().addClass('active');

	/* contact form */
	$('#mailform input#code').addClass('input-mini');
	$('#confirm, #discard').wrapAll('<div class="form-actions"></div>');
	$('#confirm').addClass('form-horizontal');
	$('#confirm input[type="submit"]').addClass('btn btn-inverse');
	$('#discard').addClass('form-horizontal');
	$('#discard input[type="submit"]').addClass('btn btn-inverse');

	/* password & connexion & admin */
	$('.post #passwordform')
					.removeAttr('id')
					.attr('id', 'zpB_passwordform')
					.addClass('modal');
	$('#loginform form').addClass('form-horizontal');
	$('#loginform .buttons button').addClass('btn btn-inverse');
	$('#logon_box .textfield').addClass('input-large');

	$('#passwordform')
					.removeAttr('id')
					.attr('id', 'zpB_login_passwordform');
	if ($('#zpB_login_passwordform .errorbox').length) {
		$('#zpB_login_passwordform').addClass('modal');
		$('#zpB_login_passwordform').modal({show: true});
	} else {
		$('#zpB_login_passwordform').addClass('modal hide');
	}


	/* register */
	$('#registration_form label#strength, #registration_form input#pass')
					.unwrap()
					.wrapAll('<div class="control-group"></div>');
	$('#registration_form label#strength').addClass('control-label');
	$('#registration_form input#pass')
					.wrap('<div class="controls"></div>')
					.addClass('input-large');
	$('#registration_form label[for="disclose_password"], #registration_form input#disclose_password')
					.unwrap()
					.wrapAll('<div class="control-group"></div>');
	$('#registration_form label[for="disclose_password"]').addClass('control-label');
	$('#registration_form input#disclose_password')
					.wrap('<div class="controls"></div>');
	$('#registration_form label#match, #registration_form input#pass_r')
					.unwrap()
					.wrapAll('<div class="control-group password_field_"></div>');
	$('#registration_form label#match').addClass('control-label');
	$('#registration_form input#pass_r')
					.wrap('<div class="controls"></div>')
					.addClass('input-large');

	/* search form */
	$('#search_form').addClass('navbar-search');
	$('#search_input').addClass('search-query input-medium');
	$('#search_form input[type="submit"]')
					.addClass('btn btn-inverse');
	$('#search').addClass('pull-right');
	$('#searchfields_icon').replaceWith('<i class="icon-list icon-white" title="options de recherche"></i>');

	/* google map */
	$('#googlemap_toggle').remove();
	$('.google_map')
					.addClass('accordion-toggle')
					.prepend('<i class="icon-map-marker"></i>');
	$('#googlemap_data')
					.removeAttr('id')
					.attr('id', 'zpB_googlemap_data')
					.removeClass('hidden_map');
	if ($('#gmap_accordion #zB_show').length) {
		$('#zpB_googlemap_data').collapse('show');
	}
	if ($('#gmap_accordion #zB_hide').length) {
		$('#zpB_googlemap_data').collapse('hide');
	}

	/* comment form */
	$('#commentform input#code').addClass('input-mini');
	$('#commentcontent h3').remove();
	$('#commentcontent').addClass('row');
	$('#commentcontent #comments').addClass('span6');
	$('#commentcontent #commententry').addClass('span6');
	if ($('#commentform .errorbox').length) {
		$('#comment').collapse('show');
	}

});