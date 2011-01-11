/*
    This file is part of JonDesign's SmoothGallery v2.0.

    JonDesign's SmoothGallery is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    JonDesign's SmoothGallery is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with JonDesign's SmoothGallery; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Main Developer: Jonathan Schemoul (JonDesign: http://www.jondesign.net/)
    Contributed code by:
    - Christian Ehret (bugfix)
	- Nitrix (bugfix)
	- Valerio from Mad4Milk for his great help with the carousel scrolling and many other things.
	- Archie Cowan for helping me find a bugfix on carousel inner width problem.
	- Tomocchino from #mootools for the preloader class
	Many thanks to:
	- The mootools team for the great mootools lib, and it's help and support throughout the project.
*/

// declaring the class
var gallery = {
	initialize: function(element, options) {
		this.setOptions({
			showArrows: true,
			showCarousel: true,
			showInfopane: true,
			embedLinks: true,
			fadeDuration: 500,
			timed: false,
			delay: 9000,
			preloader: true,
			preloaderImage: true,
			preloaderErrorImage: true,
			/* Data retrieval */
			manualData: [],
			populateFrom: false,
			populateData: true,
			destroyAfterPopulate: true,
			elementSelector: "div.imageElement",
			titleSelector: "h3",
			subtitleSelector: "p",
			linkSelector: "a.open",
			imageSelector: "img.full",
			thumbnailSelector: "img.thumbnail",
			defaultTransition: "fade",
			/* InfoPane options */
			slideInfoZoneOpacity: 0.7,
			slideInfoZoneSlide: true,
			/* Carousel options */
			carouselMinimizedOpacity: 0.4,
			carouselMinimizedHeight: 20,
			carouselMaximizedOpacity: 0.9,
			thumbHeight: 75,
			thumbWidth: 100,
			thumbSpacing: 10,
			thumbIdleOpacity: 0.6,
			textShowCarousel: 'Pictures',
			showCarouselLabel: true,
			thumbCloseCarousel: true,
			useThumbGenerator: false,
			thumbGenerator: 'resizer.php',
			useExternalCarousel: false,
			carouselElement: false,
			carouselHorizontal: true,
			activateCarouselScroller: true,
			carouselPreloader: true,
			textPreloadingCarousel: 'Loading...',
			/* CSS Classes */
			baseClass: 'jdGallery',
			withArrowsClass: 'withArrows',
			/* Plugins: HistoryManager */
			useHistoryManager: false,
			customHistoryKey: false
		}, options);
		this.fireEvent('onInit');
		this.currentIter = 0;
		this.lastIter = 0;
		this.maxIter = 0;
		this.galleryElement = element;
		this.galleryData = this.options.manualData;
		this.galleryInit = 1;
		this.galleryElements = Array();
		this.thumbnailElements = Array();
		this.galleryElement.addClass(this.options.baseClass);
		
		this.populateFrom = element;
		if (this.options.populateFrom)
			this.populateFrom = this.options.populateFrom;		
		if (this.options.populateData)
			this.populateData();
		element.style.display="block";
		
		if (this.options.useHistoryManager)
			this.initHistory();
		
		if (this.options.embedLinks)
		{
			this.currentLink = new Element('a').addClass('open').setProperties({
				href: '#',
				title: ''
			}).injectInside(element);
			if ((!this.options.showArrows) && (!this.options.showCarousel))
				this.galleryElement = element = this.currentLink;
			else
				this.currentLink.setStyle('display', 'none');
		}
		
		this.constructElements();
		if ((this.galleryData.length>1)&&(this.options.showArrows))
		{
			var leftArrow = new Element('a').addClass('left').addEvent(
				'click',
				this.prevItem.bind(this)
			).injectInside(element);
			var rightArrow = new Element('a').addClass('right').addEvent(
				'click',
				this.nextItem.bind(this)
			).injectInside(element);
			this.galleryElement.addClass(this.options.withArrowsClass);
		}
		this.loadingElement = new Element('div').addClass('loadingElement').injectInside(element);
		if (this.options.showInfopane) this.initInfoSlideshow();
		if (this.options.showCarousel) this.initCarousel();
		this.doSlideShow(1);
	},
	populateData: function() {
		currentArrayPlace = this.galleryData.length;
		options = this.options;
		var data = $A(this.galleryData);
		data.extend(this.populateGallery(this.populateFrom, currentArrayPlace));
		this.galleryData = data;
		this.fireEvent('onPopulated');
	},
	populateGallery: function(element, startNumber) {
		var data = [];
		options = this.options;
		currentArrayPlace = startNumber;
		element.getElements(options.elementSelector).each(function(el) {
			elementDict = {
				image: el.getElement(options.imageSelector).getProperty('src'),
				number: currentArrayPlace,
				transition: this.options.defaultTransition
			};
			elementDict.extend = $extend;
			if ((options.showInfopane) | (options.showCarousel))
				elementDict.extend({
					title: el.getElement(options.titleSelector).innerHTML,
					description: el.getElement(options.subtitleSelector).innerHTML
				});
			if (options.embedLinks)
				elementDict.extend({
					link: el.getElement(options.linkSelector).href||false,
					linkTitle: el.getElement(options.linkSelector).title||false,
					linkTarget: el.getElement(options.linkSelector).getProperty('target')||false
				});
			if ((!options.useThumbGenerator) && (options.showCarousel))
				elementDict.extend({
					thumbnail: el.getElement(options.thumbnailSelector).getProperty('src')
				});
			else if (options.useThumbGenerator)
				elementDict.extend({
					thumbnail: options.thumbGenerator + '?imgfile=' + elementDict.image + '&max_width=' + options.thumbWidth + '&max_height=' + options.thumbHeight
				});
			
			data.extend([elementDict]);
			currentArrayPlace++;
			if (this.options.destroyAfterPopulate)
				el.remove();
		});
		return data;
	},
	constructElements: function() {
		el = this.galleryElement;
		this.maxIter = this.galleryData.length;
		var currentImg;
		for(i=0;i<this.galleryData.length;i++)
		{
			var currentImg = new Fx.Styles(
				new Element('div').addClass('slideElement').setStyles({
					'position':'absolute',
					'left':'0px',
					'right':'0px',
					'margin':'0px',
					'padding':'0px',
					'backgroundPosition':"center center",
					'opacity':'0'
				}).injectInside(el),
				'opacity',
				{duration: this.options.fadeDuration}
			);
			if (this.options.preloader)
			{
				currentImg.source = this.galleryData[i].image;
				currentImg.loaded = false;
				currentImg.load = function(imageStyle) {
					if (!imageStyle.loaded)	{
						new Asset.image(imageStyle.source, {
		                            'onload'  : function(img){
													img.element.setStyle(
													'backgroundImage',
													"url('" + img.source + "')")
													img.loaded = true;
												}.bind(this, imageStyle)
						});
					}
				}.pass(currentImg, this);
			} else {
				currentImg.element.setStyle('backgroundImage',
									"url('" + this.galleryData[i].image + "')");
			}
			this.galleryElements[parseInt(i)] = currentImg;
		}
	},
	destroySlideShow: function(element) {
		var myClassName = element.className;
		var newElement = new Element('div').addClass('myClassName');
		element.parentNode.replaceChild(newElement, element);
	},
	startSlideShow: function() {
		this.fireEvent('onStart');
		this.loadingElement.style.display = "none";
		this.lastIter = this.maxIter - 1;
		this.currentIter = 0;
		this.galleryInit = 0;
		this.galleryElements[parseInt(this.currentIter)].set({opacity: 1});
		if (this.options.showInfopane)
			this.showInfoSlideShow.delay(1000, this);
		var textShowCarousel = formatString(this.options.textShowCarousel, this.currentIter+1, this.maxIter);
		if (this.options.showCarousel&&(!this.options.carouselPreloader))
			this.carouselBtn.setHTML(textShowCarousel).setProperty('title', textShowCarousel);
		this.prepareTimer();
		if (this.options.embedLinks)
			this.makeLink(this.currentIter);
	},
	nextItem: function() {
		this.fireEvent('onNextCalled');
		this.nextIter = this.currentIter+1;
		if (this.nextIter >= this.maxIter)
			this.nextIter = 0;
		this.galleryInit = 0;
		this.goTo(this.nextIter);
	},
	prevItem: function() {
		this.fireEvent('onPreviousCalled');
		this.nextIter = this.currentIter-1;
		if (this.nextIter <= -1)
			this.nextIter = this.maxIter - 1;
		this.galleryInit = 0;
		this.goTo(this.nextIter);
	},
	goTo: function(num) {
		this.clearTimer();
		if(this.options.preloader)
		{
			this.galleryElements[num].load();
			if (num==0)
				this.galleryElements[this.maxIter - 1].load();
			else
				this.galleryElements[num - 1].load();
			if (num==(this.maxIter - 1))
				this.galleryElements[0].load();
			else
				this.galleryElements[num + 1].load();
				
		}
		if (this.options.embedLinks)
			this.clearLink();
		if (this.options.showInfopane)
		{
			this.slideInfoZone.clearChain();
			this.hideInfoSlideShow().chain(this.changeItem.pass(num, this));
		} else
			this.currentChangeDelay = this.changeItem.delay(500, this, num);
		if (this.options.embedLinks)
			this.makeLink(num);
		this.prepareTimer();
		/*if (this.options.showCarousel)
			this.clearThumbnailsHighlights();*/
	},
	changeItem: function(num) {
		this.fireEvent('onStartChanging');
		this.galleryInit = 0;
		if (this.currentIter != num)
		{
			for(i=0;i<this.maxIter;i++)
			{
				if ((i != this.currentIter)) this.galleryElements[i].set({opacity: 0});
			}
			gallery.Transitions[this.galleryData[num].transition].pass([
				this.galleryElements[this.currentIter],
				this.galleryElements[num],
				this.currentIter,
				num], this)();
			this.currentIter = num;
		}
		var textShowCarousel = formatString(this.options.textShowCarousel, num+1, this.maxIter);
		if (this.options.showCarousel)
			this.carouselBtn.setHTML(textShowCarousel).setProperty('title', textShowCarousel);
		this.doSlideShow.bind(this)();
		this.fireEvent('onChanged');
	},
	clearTimer: function() {
		if (this.options.timed)
			$clear(this.timer);
	},
	prepareTimer: function() {
		if (this.options.timed)
			this.timer = this.nextItem.delay(this.options.delay, this);
	},
	doSlideShow: function(position) {
		if (this.galleryInit == 1)
		{
			imgPreloader = new Image();
			imgPreloader.onload=function(){
				this.startSlideShow.delay(10, this);
			}.bind(this);
			imgPreloader.src = this.galleryData[0].image;
			if(this.options.preloader)
				this.galleryElements[0].load();
		} else {
			if (this.options.showInfopane)
			{
				if (this.options.showInfopane)
				{
					this.showInfoSlideShow.delay((500 + this.options.fadeDuration), this);
				} else
					if ((this.options.showCarousel)&&(this.options.activateCarouselScroller))
						this.centerCarouselOn(position);
			}
		}
	},
	createCarousel: function() {
		var carouselElement;
		if (!this.options.useExternalCarousel)
		{
			var carouselContainerElement = new Element('div').addClass('carouselContainer').injectInside(this.galleryElement);
			this.carouselContainer = new Fx.Styles(carouselContainerElement, {transition: Fx.Transitions.expoOut});
			this.carouselContainer.normalHeight = carouselContainerElement.offsetHeight;
			this.carouselContainer.set({'opacity': this.options.carouselMinimizedOpacity, 'top': (this.options.carouselMinimizedHeight - this.carouselContainer.normalHeight)});
			this.carouselBtn = new Element('a').addClass('carouselBtn').setProperties({
				title: this.options.textShowCarousel
			}).injectInside(carouselContainerElement);
			if(this.options.carouselPreloader)
				this.carouselBtn.setHTML(this.options.textPreloadingCarousel);
			else
				this.carouselBtn.setHTML(this.options.textShowCarousel);
			this.carouselBtn.addEvent(
				'click',
				function () {
					this.carouselContainer.clearTimer();
					this.toggleCarousel();
				}.bind(this)
			);
			this.carouselActive = false;
	
			carouselElement = new Element('div').addClass('carousel').injectInside(carouselContainerElement);
			this.carousel = new Fx.Styles(carouselElement);
		} else {
			carouselElement = $(this.options.carouselElement).addClass('jdExtCarousel');
		}
		this.carouselElement = new Fx.Styles(carouselElement, {transition: Fx.Transitions.expoOut});
		this.carouselElement.normalHeight = carouselElement.offsetHeight;
		if (this.options.showCarouselLabel)
			this.carouselLabel = new Element('p').addClass('label').injectInside(carouselElement);
		carouselWrapper = new Element('div').addClass('carouselWrapper').injectInside(carouselElement);
		this.carouselWrapper = new Fx.Styles(carouselWrapper, {transition: Fx.Transitions.expoOut});
		this.carouselWrapper.normalHeight = carouselWrapper.offsetHeight;
		this.carouselInner = new Element('div').addClass('carouselInner').injectInside(carouselWrapper);
		if (this.options.activateCarouselScroller)
		{
			this.carouselWrapper.scroller = new Scroller(carouselWrapper, {
				area: 100,
				velocity: 0.2
			})
			
			this.carouselWrapper.elementScroller = new Fx.Scroll(carouselWrapper, {
				duration: 400,
				onStart: this.carouselWrapper.scroller.stop.bind(this.carouselWrapper.scroller),
				onComplete: this.carouselWrapper.scroller.start.bind(this.carouselWrapper.scroller)
			});
		}
	},
	fillCarousel: function() {
		this.constructThumbnails();
		this.carouselInner.normalWidth = ((this.maxIter * (this.options.thumbWidth + this.options.thumbSpacing + 2))+this.options.thumbSpacing) + "px";
		this.carouselInner.style.width = this.carouselInner.normalWidth;
	},
	initCarousel: function () {
		this.createCarousel();
		this.fillCarousel();
		if (this.options.carouselPreloader)
			this.preloadThumbnails();
	},
	flushCarousel: function() {
		this.thumbnailElements.each(function(myFx) {
			myFx.element.remove();
			myFx = myFx.element = null;
		});
		this.thumbnailElements = [];
	},
	toggleCarousel: function() {
		if (this.carouselActive)
			this.hideCarousel();
		else
			this.showCarousel();
	},
	showCarousel: function () {
		this.fireEvent('onShowCarousel');
		this.carouselContainer.start({
			'opacity': this.options.carouselMaximizedOpacity,
			'top': 0
		}).chain(function() {
			this.carouselActive = true;
			this.carouselWrapper.scroller.start();
			this.fireEvent('onCarouselShown');
			this.carouselContainer.options.onComplete = null;
		}.bind(this));
	},
	hideCarousel: function () {
		this.fireEvent('onHideCarousel');
		var targetTop = this.options.carouselMinimizedHeight - this.carouselContainer.normalHeight;
		this.carouselContainer.start({
			'opacity': this.options.carouselMinimizedOpacity,
			'top': targetTop
		}).chain(function() {
			this.carouselActive = false;
			this.carouselWrapper.scroller.stop();
			this.fireEvent('onCarouselHidden');
			this.carouselContainer.options.onComplete = null;
		}.bind(this));
	},
	constructThumbnails: function () {
		element = this.carouselInner;
		for(i=0;i<this.galleryData.length;i++)
		{
			var currentImg = new Fx.Style(new Element ('div').addClass("thumbnail").setStyles({
					backgroundImage: "url('" + this.galleryData[i].thumbnail + "')",
					backgroundPosition: "center center",
					backgroundRepeat: 'no-repeat',
					marginLeft: this.options.thumbSpacing + "px",
					width: this.options.thumbWidth + "px",
					height: this.options.thumbHeight + "px"
				}).injectInside(element), "opacity", {duration: 200}).set(this.options.thumbIdleOpacity);
			currentImg.element.addEvents({
				'mouseover': function (myself) {
					myself.clearTimer();
					myself.start(0.99);
					if (this.options.showCarouselLabel)
						$(this.carouselLabel).setHTML('<span class="number">' + (myself.relatedImage.number + 1) + "/" + this.maxIter + ":</span> " + myself.relatedImage.title);
				}.pass(currentImg, this),
				'mouseout': function (myself) {
					myself.clearTimer();
					myself.start(this.options.thumbIdleOpacity);
				}.pass(currentImg, this),
				'click': function (myself) {
					this.goTo(myself.relatedImage.number);
					if (this.options.thumbCloseCarousel)
						this.hideCarousel();
				}.pass(currentImg, this)
			});
			
			currentImg.relatedImage = this.galleryData[i];
			this.thumbnailElements[parseInt(i)] = currentImg;
		}
	},
	log: function(value) {
		if(console.log)
			console.log(value);
	},
	preloadThumbnails: function() {
		var thumbnails = [];
		for(i=0;i<this.galleryData.length;i++)
		{
			thumbnails[parseInt(i)] = this.galleryData[i].thumbnail;
		}
		this.thumbnailPreloader = new Preloader();
		this.thumbnailPreloader.addEvent('onComplete', function() {
			var textShowCarousel = formatString(this.options.textShowCarousel, this.currentIter+1, this.maxIter);
			this.carouselBtn.setHTML(textShowCarousel).setProperty('title', textShowCarousel);
		}.bind(this));
		this.thumbnailPreloader.load(thumbnails);
	},
	clearThumbnailsHighlights: function()
	{
		for(i=0;i<this.galleryData.length;i++)
		{
			this.thumbnailElements[i].clearTimer();
			this.thumbnailElements[i].start(0.2);
		}
	},
	changeThumbnailsSize: function(width, height)
	{
		for(i=0;i<this.galleryData.length;i++)
		{
			this.thumbnailElements[i].clearTimer();
			this.thumbnailElements[i].element.setStyles({
				'width': width + "px",
				'height': height + "px"
			});
		}
	},
	centerCarouselOn: function(num) {
		if (!this.carouselWallMode)
		{
			var carouselElement = this.thumbnailElements[num];
			var position = carouselElement.element.offsetLeft + (carouselElement.element.offsetWidth / 2);
			var carouselWidth = this.carouselWrapper.element.offsetWidth;
			var carouselInnerWidth = this.carouselInner.offsetWidth;
			var diffWidth = carouselWidth / 2;
			var scrollPos = position-diffWidth;
			this.carouselWrapper.elementScroller.scrollTo(scrollPos,0);
		}
	},
	initInfoSlideshow: function() {
		/*if (this.slideInfoZone.element)
			this.slideInfoZone.element.remove();*/
		this.slideInfoZone = new Fx.Styles(new Element('div').addClass('slideInfoZone').injectInside($(this.galleryElement))).set({'opacity':0});
		var slideInfoZoneTitle = new Element('h2').injectInside(this.slideInfoZone.element);
		var slideInfoZoneDescription = new Element('p').injectInside(this.slideInfoZone.element);
		this.slideInfoZone.normalHeight = this.slideInfoZone.element.offsetHeight;
		this.slideInfoZone.element.setStyle('opacity',0);
	},
	changeInfoSlideShow: function()
	{
		this.hideInfoSlideShow.delay(10, this);
		this.showInfoSlideShow.delay(500, this);
	},
	showInfoSlideShow: function() {
		this.fireEvent('onShowInfopane');
		this.slideInfoZone.clearTimer();
		element = this.slideInfoZone.element;
		element.getElement('h2').setHTML(this.galleryData[this.currentIter].title);
		element.getElement('p').setHTML(this.galleryData[this.currentIter].description);
		if(this.options.slideInfoZoneSlide)
			this.slideInfoZone.start({'opacity': [0, this.options.slideInfoZoneOpacity], 'height': [0, this.slideInfoZone.normalHeight]});
		else
			this.slideInfoZone.start({'opacity': [0, this.options.slideInfoZoneOpacity]});
		if (this.options.showCarousel)
			this.slideInfoZone.chain(this.centerCarouselOn.pass(this.currentIter, this));
		return this.slideInfoZone;
	},
	hideInfoSlideShow: function() {
		this.fireEvent('onHideInfopane');
		this.slideInfoZone.clearTimer();
		if(this.options.slideInfoZoneSlide)
			this.slideInfoZone.start({'opacity': 0, 'height': 0});
		else
			this.slideInfoZone.start({'opacity': 0});
		return this.slideInfoZone;
	},
	makeLink: function(num) {
		this.currentLink.setProperties({
			href: this.galleryData[num].link,
			title: this.galleryData[num].linkTitle
		})
		if (!((this.options.embedLinks) && (!this.options.showArrows) && (!this.options.showCarousel)))
			this.currentLink.setStyle('display', 'block');
	},
	clearLink: function() {
		this.currentLink.setProperties({href: '', title: ''});
		if (!((this.options.embedLinks) && (!this.options.showArrows) && (!this.options.showCarousel)))
			this.currentLink.setStyle('display', 'none');
	},
	/* To change the gallery data, those two functions : */
	flushGallery: function() {
		this.galleryElements.each(function(myFx) {
			myFx.element.remove();
			myFx = myFx.element = null;
		});
		this.galleryElements = [];
	},
	changeData: function(data) {
		this.galleryData = data;
		this.clearTimer();
		this.flushGallery();
		if (this.options.showCarousel) this.flushCarousel();
		this.constructElements();
		if (this.options.showCarousel) this.fillCarousel();
		if (this.options.showInfopane) this.hideInfoSlideShow();
		this.galleryInit=1;
		this.lastIter=0;
		this.currentIter=0;
		this.doSlideShow(1);
	},
	/* Plugins: HistoryManager */
	initHistory: function() {
		this.fireEvent('onHistoryInit');
		this.historyKey = this.galleryElement.id + '-picture';
		if (this.options.customHistoryKey)
			this.historyKey = this.options.customHistoryKey();
		this.history = HistoryManager.register(
			this.historyKey,
			[1],
			function(values) {
				if (parseInt(values[0])-1 < this.maxIter)
					this.goTo(parseInt(values[0])-1);
			}.bind(this),
			function(values) {
				return [this.historyKey, '(', values[0], ')'].join('');
			}.bind(this),
			this.historyKey + '\\((\\d+)\\)');
		this.addEvent('onChanged', function(){
			this.history.setValue(0, this.currentIter+1);
		}.bind(this));
		this.fireEvent('onHistoryInited');
	}
};
gallery = new Class(gallery);
gallery.implement(new Events);
gallery.implement(new Options);

gallery.Transitions = new Abstract ({
	fade: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
		oldFx.options.duration = newFx.options.duration = this.options.fadeDuration;
		if (newPos > oldPos) newFx.start({opacity: 1});
		else
		{
			newFx.set({opacity: 1});
			oldFx.start({opacity: 0});
		}
	},
	crossfade: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
		oldFx.options.duration = newFx.options.duration = this.options.fadeDuration;
		newFx.start({opacity: 1});
		oldFx.start({opacity: 0});
	},
	fadebg: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
		oldFx.options.duration = newFx.options.duration = this.options.fadeDuration / 2;
		oldFx.start({opacity: 0}).chain(newFx.start.pass([{opacity: 1}], newFx));
	}
});

/* All code copyright 2007 Jonathan Schemoul */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Follows: Preloader (class)
 * Simple class for preloading images with support for progress reporting
 * Copyright 2007 Tomocchino.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

var Preloader = new Class({
  
  Implements: [Events, Options],

  options: {
    root        : '',
    period      : 100
  },
  
  initialize: function(options){
    this.setOptions(options);
  },
  
  load: function(sources) {
    this.index = 0;
    this.images = [];
    this.sources = this.temps = sources;
    this.total = this. sources.length;
    
    this.fireEvent('onStart', [this.index, this.total]);
    this.timer = this.progress.periodical(this.options.period, this);
    
    this.sources.each(function(source, index){
      this.images[index] = new Asset.image(this.options.root + source, {
        'onload'  : function(){ this.index++; if(this.images[index]) this.fireEvent('onLoad', [this.images[index], index, source]); }.bind(this),
        'onerror' : function(){ this.index++; this.fireEvent('onError', [this.images.splice(index, 1), index, source]); }.bind(this),
        'onabort' : function(){ this.index++; this.fireEvent('onError', [this.images.splice(index, 1), index, source]); }.bind(this)
      });
    }, this);
  },
  
  progress: function() {
    this.fireEvent('onProgress', [Math.min(this.index, this.total), this.total]);
    if(this.index >= this.total) this.complete();
  },
  
  complete: function(){
    $clear(this.timer);
    this.fireEvent('onComplete', [this.images]);
  },
  
  cancel: function(){
    $clear(this.timer);
  }
  
});

Preloader.implement(new Events, new Options);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Follows: formatString (function)
 * Original name: Yahoo.Tools.printf
 * Copyright Yahoo.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function formatString() {
	var num = arguments.length;
	var oStr = arguments[0];
	for (var i = 1; i < num; i++) {
		var pattern = "\\{" + (i-1) + "\\}"; 
		var re = new RegExp(pattern, "g");
		oStr = oStr.replace(re, arguments[i]);
	}
	return oStr; 
}