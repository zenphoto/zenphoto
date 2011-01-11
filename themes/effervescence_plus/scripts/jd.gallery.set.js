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
*/

var gallerySet = gallery.extend({
	initialize: function(element, options) {
		this.setOptions({
			manualSetData: [],
			gallerySelector: "div.galleryElement",
			galleryTitleSelector: "h2",
			textGallerySelector: 'Galleries',
			textShowGallerySelector: 'Other Galleries',
			textGalleryInfo: '{0} pictures',
			startWithSelector: true,
			/* Changing default options */
			textShowCarousel: '{0}/{1} Pictures',
			carouselPreloader: false
		}, options);
		this.gallerySet = this.options.manualSetData;
		this.addEvent('onPopulated', this.createGallerySelectorTab.bind(this));
		this.addEvent('onPopulated', this.createGallerySelector.bind(this));
		this.startWithSelectorFn = this.toggleGallerySelector.pass(true, this);
		if (this.options.startWithSelector)
			this.addEvent('onGallerySelectorCreated', this.startWithSelectorFn);
		this.parent(element, this.options);
	},
	populateData: function() {
		options = this.options;
		var data = $A(this.gallerySet);
		this.populateFrom.getElements(options.gallerySelector).each(function (galEl) {
			currentGalArrayPlace = 0;
			galleryDict = {
				title: galEl.getElement(options.galleryTitleSelector).innerHTML,
				elements: []
			}
			galleryDict.elements.extend(this.populateGallery(galEl, 0));
			data.extend([galleryDict]);
			if (this.options.destroyAfterPopulate)
				galEl.remove();
		}, this);
		this.gallerySet = data;
		this.galleryData = data[0].elements;
		this.currentGallery = 0;
		this.fireEvent('onPopulated');
	},
	changeGallery: function(number)
	{
		if (number!=this.currentGallery)
		{
			this.changeData(this.gallerySet[number].elements);
			this.maxIter = this.gallerySet[number].elements.length;
			this.currentGallery = number;
			this.gallerySelectorBtn.setHTML(this.gallerySet[number].title);
			this.fireEvent('onGalleryChanged');
		}
		this.toggleGallerySelector(false);
	},
	createGallerySelectorTab: function() {
		this.gallerySelectorBtn = new Element('a').addClass('gallerySelectorBtn').setProperties({
			title: this.options.textShowGallerySelector
		}).setHTML(this.options.textShowGallerySelector).addEvent(
			'click',
			function(){ this.toggleGallerySelector(true); }.bind(this)
		).injectInside(this.galleryElement);
		this.addEvent('onShowCarousel', function(){this.gallerySelectorBtn.setStyle('zIndex', 10)}.bind(this));
		this.addEvent('onCarouselHidden', function(){this.gallerySelectorBtn.setStyle('zIndex', 15)}.bind(this));
	},
	createGallerySelector: function() {
		this.gallerySelector = new Fx.Styles(
			new Element('div').addClass(
				'gallerySelector'
			).injectInside(
				this.galleryElement
			).setStyles({
				'display': 'none',
				'opacity': '0'
			})
		);
		this.gallerySelectorTitle = 
			new Element('h2').setHTML(
				this.options.textGallerySelector
			).injectInside(this.gallerySelector.element);
		var gallerySelectorHeight = this.galleryElement.offsetHeight - 50 - 10 - 2;
		this.gallerySelectorWrapper = new Fx.Style(
			new Element('div').addClass(
				'gallerySelectorWrapper'
			).setStyle(
				'height',
				gallerySelectorHeight + "px"
			).injectInside(this.gallerySelector.element)
		);
		this.gallerySelectorInner =	new Element('div').addClass('gallerySelectorInner').injectInside(this.gallerySelectorWrapper.element);
		this.gallerySelectorWrapper.scroller = new Scroller(this.gallerySelectorWrapper.element, {
			area: 100,
			velocity: 0.3
		}).start();
		this.createGalleryButtons();
		this.fireEvent('onGallerySelectorCreated');
	},
	createGalleryButtons: function () {
		var galleryButtonWidth =
			((this.galleryElement.offsetWidth - 30) / 2) - 14;
		this.gallerySet.each(function(galleryItem, index){
			var button = new Element('div').addClass('galleryButton').injectInside(
				this.gallerySelectorInner
			).addEvents({
				'mouseover': function(myself){
					myself.button.addClass('hover');
				}.pass(galleryItem, this),
				'mouseout': function(myself){
					myself.button.removeClass('hover');
				}.pass(galleryItem, this),
				'click': function(myself, number){
					this.changeGallery.pass(number,this)();
				}.pass([galleryItem, index], this)
			}).setStyle('width', galleryButtonWidth);
			galleryItem.button = button;
			var thumbnail = "";
			if (this.options.showCarousel)
				thumbnail = galleryItem.elements[0].thumbnail;
			else
				thumbnail = galleryItem.elements[0].image;
			new Element('div').addClass('preview').setStyle(
				'backgroundImage',
				"url('" + thumbnail + "')"
			).injectInside(button);
			new Element('h3').setHTML(galleryItem.title).injectInside(button);
			new Element('p').addClass('info').setHTML(formatString(this.options.textGalleryInfo, galleryItem.elements.length)).injectInside(button);
		}, this);
		new Element('br').injectInside(this.gallerySelectorInner).setStyle('clear','both');
	},
	toggleGallerySelector: function(state) {
		if (state)
			this.gallerySelector.start({'opacity' : 1}).element.setStyle('display','block');
		else
			this.gallerySelector.start({'opacity' : 0});
	},
	initHistory: function() {
		this.fireEvent('onHistoryInit');
		this.historyKey = this.galleryElement.id + '-gallery';
		if (this.options.customHistoryKey)
			this.historyKey = this.options.customHistoryKey();
		this.history = HistoryManager.register(
			this.historyKey,
			[1,1],
			function(values) {
				this.changeGallery.pass(parseInt(values[0]) - 1, this).delay(10);
				if(this.gallerySelector)
					this.toggleGallerySelector.pass(false, this).delay(500);
				this.goTo.pass(parseInt(values[1]) - 1, this).delay(100);
			}.bind(this),
			function(values) {
				return [this.historyKey, '(', values[0], ')', '-picture','(', values[1], ')'].join('');
			}.bind(this),
			this.historyKey + '\\((\\d+)\\)-picture\\((\\d+)\\)');
		updateHistory = function(){
			this.history.setValue(0, this.currentGallery + 1);
			this.history.setValue(1, this.currentIter + 1);
		}.bind(this);		
		
		this.addEvent('onChanged', updateHistory);
		this.addEvent('onGalleryChanged', updateHistory);
		this.fireEvent('onHistoryInited');
	}
});