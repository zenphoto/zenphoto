jQuery(document).ready(function($) {
	//For Comment slide : force to open #comment-wrap if there is an errorbox or notebox
	if (($('#comment-wrap .errorbox').length) || ($('#comment-wrap .notebox').length)) {
		$('#comment-wrap').css('display', 'block');
		$('#comment-wrap').css('opacity', '1');
	} else {
		$('#comment-wrap').css('display', 'none');
		$('#comment-wrap').css('opacity', '0');
	};
	$(".fadetoggler").click(function(){
		$(this).next("#comment-wrap").fadeSliderToggle();
	});

	// Initially set opacity on thumbs and add additional styling for hover effect on thumbs
	var onMouseOutOpacity = 0.8;

	$('ul.thumbs li').opacityrollover({
			// avec ul.thumbs li : bug avec ie7 et flag_thumbnail
			// avec ul.thumbs li img : bug corrigé mais image sélectionnée surexposée
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});

	$('ul.thumbs-nogal img').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});

	$('#image-stat li img').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});

	var onMouseOutOpacityAlbums = 0.8;

	$('div#album-wrap ul li img').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacityAlbums,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast'
	});

	$('div.opac').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacityAlbums,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast'
	});
});