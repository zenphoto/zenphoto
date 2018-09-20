/*------------------------------------
		zenphoto overwriting for theme, zenphoto and bootstrap 3.x being compliant all together
-------------------------------------- */

jQuery(document).ready(function() {

	/* responsive pictures */
	$('img.remove-attributes')
		.removeAttr('width')
		.removeAttr('height');
	$('img[alt="protected"]')
		.removeAttr('width')
		.removeAttr('height')
		.addClass('img-responsive');

	/* add attributes for Fancybox */
	$('.swipebox').each(function() {
		$(this)
			.attr('data-fancybox', 'fancybox')
			.attr('data-caption', $(this).attr('title'));
	});

	/* add icon to links going out of the site, except links with pictures */
	$('a[target=_blank]').each(function() {
		if (!($(this).children('img').length)) {
			$(this).append('&nbsp;<small><span class="small glyphicon glyphicon-new-window"></span></small>');
		}
	});
	$('footer a[href="http://www.zenphoto.org"]')
		.attr('target', '_blank')
		.append('&nbsp;<small><span class="small glyphicon glyphicon-new-window"></span></small>');

	/* buttons */
	$('button, input[type="button"], input[type="submit"], input[type="reset"]').addClass('btn btn-default');

	/* error message */
	if ($('.errorbox').length) {
		$('.errorbox')
			.addClass('alert alert-danger')
			.removeClass('errorbox');
		$('.alert.alert-danger h2').replaceWith('<h5>' + $('.alert.alert-danger h2').html() + '</h5>');
	}

	/* menu and navigation */
	$('.navbar .nav li > a.active')
		.removeClass('active')
		.parent().addClass('active');
	$('div.pagination ul.pagination').unwrap();
	$('ul.pagination').wrap('<nav></nav>');
	$('ul.pagination li.current')
		.wrapInner('<a href="#"></a>')
		.addClass('active');
	$('ul.pagination span.disabledlink')
		.parent().addClass('disabled');

	/* langage selector */
	if ($('#flags').length) {
		$('#flags').prepend('<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' + $('li.currentLanguage').html() + '&nbsp;&nbsp;<span class="glyphicon glyphicon-chevron-down"></span>');
		$('.flags')
			.addClass('dropdown-menu')
			.attr('role', 'menu');
		$('.currentLanguage')
			.wrapInner('<a href="#"></a>')
			.removeClass('currentLanguage');
	}

	/* login & password & register */
	$('#register_link').prepend('<span class="glyphicon glyphicon glyphicon-edit"></span>&nbsp;&nbsp;');
	$('.logonlink').prepend('<span class="glyphicon glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;');
	$('.logoutlink')
		.addClass('text-center')
		.text('')
		.prepend('<span class="glyphicon glyphicon glyphicon-log-out"></span>');

	if ($('#loginform').length) {
		$('#loginform form').addClass('form-horizontal');
		$('#loginform button[type="reset"]').addClass('margin-left-small');
		$('#loginform fieldset input').addClass('form-control');
		$('#loginform #disclose_password')
			.removeClass('form-control')
			.parent().wrap('<div class="checkbox"></div>');
		$('#loginform br').remove();
		$('#loginform #user').focus();
		if ($('#loginform .alert').length) {
			$('#login-modal').modal('show');
		}
		$('#login-modal').on('shown.bs.modal', function() {
			$(this).find('#user').focus();
		});
		$('#password').on('shown.bs.modal', function() {
			$(this).find('#user').focus();
		});
	}

	if ($('#registration_form').length) {
		$('#registration_form form').addClass('form-horizontal');
		$('#registration_form label + input')
			.addClass('form-control')
			.wrap('<div class="col-sm-6"></div>')
			.parent().parent().wrapInner('<div class="form-group"></div>');
		$('#registration_form label').addClass('col-sm-4 control-label');
		$('#registration_form #disclose_password').removeClass('form-control');
		$('#registration_form p > strong')
			.parent().wrapInner('<div class="form-control-static"></div>')
			.wrapInner('<div class="col-sm-push-4 col-sm-6"></div>')
			.wrapInner('<div class="form-group"></div>');
		$('#registration_form input[type="submit"]')
			.wrap('<p></p>')
			.wrap('<div class="form-group"></div>')
			.wrap('<div class="col-sm-offset-4 col-sm-6"></div>');
		$('#registration_form label[for="username"]').parent().addClass('hidden');
		$('#registration_form label[for="username"]').parent().addClass('hidden');
		$('#registration_form #match')
			.parent().addClass('password_field_')
			.parent().removeClass('password_field_');
		$('.form-group').unwrap();
	}
	$('.alert.fade-message').removeClass('fade-message');

	/* home */
	if ($('#latestnews').length) {
		$('#latestnews').addClass('nav');
		$('#latestnews h3 a')
			.unwrap()
			.wrap( "<h4></h4>" );
	}

	/* gallery, albums & images*/
	$('.flag_thumbnail').removeAttr('style');
	$('#imagemetadata table').addClass('table table-condensed');
	$('#exif_link').wrap('<h4></h4>');
	$('.rating input[type="button"]').addClass('margin-left');

	/* news & pages */
	if ($('#news-cat-list').length) {
		$('#news-cat-list').addClass('nav nav-pills');
		$('#news-cat-list li.active').wrapInner('<a href="#"></a>');
		$('#news-cat-list li a.active').parent().addClass('active');
		$('#news-cat-list li a').each(function() {
			$(this).append('&nbsp;').append($(this).next('span'));
		});
	}
	if ($('.pages-list').length) {
		$('.pages-list li a.active').parent().addClass('active');
	}

	/* contact */
	if ($('#mailform').length) {
		$('#mailform, #confirm, #discard').addClass('form-horizontal');
				$('#mailform input[type="reset"], #confirm input[type="reset"]').addClass('margin-left-small');
		$('#mailform label + input')
			.addClass('form-control')
			.wrap('<div class="col-sm-6"></div>')
			.parent().parent().wrapInner('<div class="form-group"></div>');
		$('#mailform label').addClass('col-sm-3 control-label');
		$('#mailform textarea')
			.addClass('form-control')
			.attr('rows', '8')
			.wrap('<div class="col-sm-8"></div>')
			.parent().parent().wrapInner('<div class="form-group"></div>');
		$('#mailform input[type="submit"]').parent()
			.wrapInner('<div class="col-sm-offset-3 col-sm-6"></div>')
			.wrapInner('<div class="form-group"></div>');
		$('#mailform label[for="username"]').parent().addClass('hidden');
		$('.form-group').unwrap();
		$('#confirm, #discard')
			.wrapAll('<div class="row"></div>')
			.wrapAll('<div class="col-sm-offset-3 col-sm-6"></div>');
		$('.post p:first-child strong:first-child').css('color', 'red');
	}

	/* search and archives */
	if ($('#search').length) {
		$('#search_input')
			.unwrap()
			.addClass('form-control')
			.parent().addClass('input-group');
		$('.input-group a:first-of-type').remove();
		$('.input-group br:first-of-type').remove();
		$('#search_submit').remove();
		$('#search_form div script').remove();
		$('#searchextrashow ul')
			.unwrap()
			.addClass('dropdown-menu dropdown-menu-right')
			.attr('id', 'searchextrashow')
			.wrap('<div class="input-group-btn dropdown">');
		$('#searchextrashow').before('<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span></button>');
		$('#searchextrashow').after('<button id="search_submit" class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span</button>');
		$('#searchextrashow').wrapInner("<div class='checkbox'></div>");
		$('#search_form input[type="radio"]').parent().addClass('radio-inline');
		$('li.year li').removeClass('list-unstyled_active');
	}

	/* google map */
	if ($('#gmap_accordion').length) {
		if ($('#gmap_show').length) {
			$('#gmap_show').attr('aria-expanded', 'true');
			$('#gmap_collapse_data')
				.addClass('collapse in')
				.attr('aria-expanded', 'true');
		}
		if ($('#gmap_hide').length) {
			$('#gmap_collapse_data').addClass('hidden_map');
		}
	}

	/* comment form */
	if ($('#comment_accordion').length) {
		$('#commentcontent h3').remove();
		$('#commentcontent').addClass('row');
		$('#commentcontent #comments').addClass('col-sm-push-6 col-sm-6 margin-bottom');
		$('#commentcontent #commententry').addClass('col-sm-pull-6 col-sm-6');
		$('#commentform').addClass('form-horizontal');
		$('#commentform br').remove();
		$('#commentform label + input')
			.addClass('form-control')
			.wrap('<div class="col-sm-7"></div>')
			.parent().parent().wrapInner('<div class="form-group"></div>');
		$('#commentform label').addClass('col-sm-4 control-label');
		$('#commentform label[for="username"]').parent().addClass('hidden');
		$('#commentform p > strong')
			.parent().wrapInner('<div class="form-control-static"></div>')
			.wrapInner('<div class="col-sm-push-4 col-sm-7"></div>')
			.wrapInner('<div class="form-group"></div>');
		$('#commentform p > *').unwrap();
		$('#commentform textarea')
			.addClass('form-control')
			.attr('rows', '6')
			.wrap('<div class="col-sm-12"></div>')
			.parent().wrap('<div class="form-group"></div>');
		$('#commentform input[type="submit"]').addClass('btn btn-default');
		$('.commentinfo h4').addClass('margin-bottom-reset');
		if ($('#commentform .alert').length) {
			$('#comment_collapse').collapse('show');
		}
	}

	//Scroll to top : thanks to: http://www.webtipblog.com/adding-scroll-top-button-website/
	$(function() {
		$(document).on( 'scroll', function() {
			if ($(window).scrollTop() > 100) {
				$('.scroll-to-top').addClass('show');
			} else {
				$('.scroll-to-top').removeClass('show');
			}
		});

		$('.scroll-to-top').on('click', scrollToTop);
	});

	function scrollToTop() {
		verticalOffset = typeof(verticalOffset) != 'undefined' ? verticalOffset : 0;
		element = $('body');
		offset = element.offset();
		offsetTop = offset.top;
		$('html, body').animate({scrollTop: offsetTop}, 500, 'linear');
	}

	// full height for main div (windows height - "header" height - "footer" height)
	$(window).resize(function() {
		$('#main').css('min-height', $(window).height() - $('#menu').outerHeight() - $('#footer').outerHeight() - $('#zp__admin_module').outerHeight() - 1);
	}).resize();
});