$(document).ready(function() {
	$().fancybox({
		selector : '[data-fancybox="fancybox"]',
		loop : true,
		infobar : true,
		buttons : [
			'slideShow',
			'close'
		],
		protect : true,
		animationEffect : "fade",
		transitionEffect : "fade",
		baseTpl :
			'<div class="fancybox-container" role="dialog" tabindex="-1">' +
				'<div class="fancybox-bg"></div>' +
				'<div class="fancybox-inner">' +
					'<div class="fancybox-infobar">' +
						'<div class="fancybox-infobar__body">' +
							'<span data-fancybox-index></span>&nbsp;/&nbsp;<span data-fancybox-count></span>' +
						'</div>' +
					'</div>' +
					'<div class="fancybox-toolbar">' +
						'{{BUTTONS}}' +
					'</div>' +
					'<div class="fancybox-navigation">' +
						'<button data-fancybox-prev title="{{PREV}}" class="fancybox-arrow fancybox-arrow--left" />' +
						'<button data-fancybox-next title="{{NEXT}}" class="fancybox-arrow fancybox-arrow--right" />' +
					'</div>' +
					'<div class="fancybox-stage"></div>' +
					'<div class="fancybox-caption-wrap">' +
						'<div class="fancybox-caption"></div>' +
					'</div>' +
				'</div>' +
			'</div>',
		btnTpl : {
			slideShow	: '<button data-fancybox-play class="fancybox-button fancybox-button--play" title="{{PLAY_START}}"></button>',
			close		: '<button data-fancybox-close class="fancybox-button fancybox-button--close" title="{{CLOSE}}"></button>'
		},
		touch : {
			vertical : false
		},
		slideShow : {
			autoStart : false,
			speed : 3000
		},
		thumbs : false,
		clickSlide : false,
		clickOutside : false
	});
});