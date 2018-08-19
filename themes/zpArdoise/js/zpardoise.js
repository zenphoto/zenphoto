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
});