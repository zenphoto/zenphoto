/**
 * --------------------------------------------------------------------
 * jQuery-Plugin "toggleElements"
 * Version: 1.3, 11.09.2007
 * by Andreas Eberhard, andreas.eberhard@gmail.com
 *                      http://jquery.andreaseberhard.de/
 *
 * Copyright (c) 2007 Andreas Eberhard
 * Licensed under GPL (http://www.opensource.org/licenses/gpl-license.php)
 *
 * Changelog:
 *    11.09.2007 Version 1.3
 *    - removed noConflict
 *    - added 'opened'-state via additional class 'opened'
 *    02.07.2007 Version 1.2.1
 *    - changed blur to work with jQuery 1.1.3
 *    - added packed version
 *    27.06.2007 Version 1.2
 *    - suppress multiple animations
 *    15.06.2007 Version 1.1
 *    - added callbacks onClick, onShow, onHide
 *    - added option showTitle
 *    31.05.2007 initial Version 1.0
 * --------------------------------------------------------------------
 * @example $(function(){$('div.toggler-1').toggleElements( );});
 * @desc Toggles the div with class 'toggler-1' into closed state on document.ready
 *
 * @example $(function(){$('fieldset.toggler-9').toggleElements( { fxAnimation:'show', fxSpeed:1000, className:'toggler', onClick:doOnClick, onHide:doOnHide, onShow:doOnShow } );});
 * @desc Toggles the fieldset with class 'toggler-9' into closed state on document.ready
 *       Animation show with speed 1000 ms is used, for the different states the css-class-prefix 'toggler-' will be used
 *       Events OnClick, OnHide, OnShow will call your JavaScript-functions
 * --------------------------------------------------------------------
 */

var toggleElements_animating = false;

(function($) {

jQuery.fn.toggleElements = function(settings) {

	// Settings
	settings = jQuery.extend({
		fxAnimation: "slide",   // slide, show, fade
		fxSpeed: "normal",   // slow, normal, fast or number of milliseconds
		className: "toggler",
		removeTitle: true,
		showTitle: false,
		onClick: null,
		onHide: null,
		onShow: null
	}, settings);

	var onClick = settings.onClick, onHide = settings.onHide, onShow = settings.onShow;

	if ((settings.fxAnimation!='slide')&&(settings.fxAnimation!='show')&&(settings.fxAnimation!='fade'))
		settings.fxAnimation='slide';

	// First hide all elements without class 'opened'
	this.each(function(){
		if (jQuery(this).attr('class').indexOf("opened")==-1){
			jQuery(this).hide();
		}
	});

	// Add Toggle-Links before elements
	this.each(function(){

		wtitle='';
		wlinktext=jQuery(this).attr('title');

		if (settings.showTitle==true) wtitle=wlinktext;
		if (settings.removeTitle==true) jQuery(this).attr('title','');

		if (jQuery(this).attr('class').indexOf("opened")!=-1){
			jQuery(this).before('<a class="'+settings.className+' '+settings.className+'-opened" href="#" title="'+ wtitle +'">' + wlinktext + '</a>');
			jQuery(this).addClass(settings.className+'-c-opened');
		} else {
			jQuery(this).before('<a class="'+settings.className+' '+settings.className+'-closed" href="#" title="'+ wtitle +'">' + wlinktext + '</a>');
			jQuery(this).addClass(settings.className+'-c-closed');
		}
		
		// Click-Function for Toggle-Link
		jQuery(this).prev('a.'+settings.className).click(function() {

			if (toggleElements_animating) return false;

			thelink = this;
			jQuery(thelink)[0].blur();

			if (thelink.animating||toggleElements_animating) return false;
			toggleElements_animating = true;
			thelink.animating = true;

			// Callback onClick
			if ( typeof onClick == 'function' && onClick(thelink) === false) {
				toggleElements_animating = false;
				thelink.animating = false;
				return false;
			}

			// Hide Element
			if (jQuery(this).next().css('display')=='block') {
				jQuery(this).next().each(function(){
					if (settings.fxAnimation == 'slide') jQuery(this).slideUp(settings.fxSpeed,function(){
						jQuery.toggleElementsHidden(this,settings.className,onHide,thelink);
					});
					if (settings.fxAnimation == 'show') jQuery(this).hide(settings.fxSpeed,function(){
						jQuery.toggleElementsHidden(this,settings.className,onHide,thelink);
					});
					if (settings.fxAnimation == 'fade') jQuery(this).fadeOut(settings.fxSpeed,function(){
						jQuery.toggleElementsHidden(this,settings.className,onHide,thelink);
					});
				});
			// Show Element
			} else {
				jQuery(this).next().each(function(){
					if (settings.fxAnimation == 'slide') jQuery(this).slideDown(settings.fxSpeed,function(){
						jQuery.toggleElementsShown(this,settings.className,onShow,thelink);
					});
					if (settings.fxAnimation == 'show')  jQuery(this).show(settings.fxSpeed,function(){
						jQuery.toggleElementsShown(this,settings.className,onShow,thelink);
					});
					if (settings.fxAnimation == 'fade')  jQuery(this).fadeIn(settings.fxSpeed,function(){
						jQuery.toggleElementsShown(this,settings.className,onShow,thelink);
					});
				});
			}
			return false;

		});

	});

};

// Remove/Add classes to Toggler-Link
jQuery.toggleElementsHidden = function(el,cname,onHide,thelink) {
	jQuery(el).prev('a.'+cname).removeClass(cname+'-opened').addClass(cname+'-closed').blur();
	if ( typeof onHide == 'function') onHide(this); // Callback onHide
	jQuery(el).removeClass(cname+'-c-opened').addClass(cname+'-c-closed');
	toggleElements_animating = false;
	thelink.animating = false;
};
jQuery.toggleElementsShown = function(el,cname,onShow,thelink) {
	jQuery(el).prev('a.'+cname).removeClass(cname+'-closed').addClass(cname+'-opened').blur();
	if ( typeof onShow == 'function') onShow(this); // Callback onShow
	jQuery(el).removeClass(cname+'-c-closed').addClass(cname+'-c-opened');
	toggleElements_animating = false;
	thelink.animating = false;
};

})(jQuery);