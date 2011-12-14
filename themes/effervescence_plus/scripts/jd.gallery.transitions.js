/*
    This file is part of JonDesign's SmoothGallery v2.1beta1.

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

gallery.Transitions.extend({
	fadeslideleft: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.Cubic.easeOut;
		oldFx.options.duration = newFx.options.duration = 1500;
		if (newPos > oldPos)
		{
			newFx.start({
				left: [this.galleryElement.offsetWidth, 0],
				opacity: 1
			});
			oldFx.start({opacity: [1,0]});
		} else {
			newFx.start({opacity: [0,1]});
			oldFx.start({
				left: [0, this.galleryElement.offsetWidth],
				opacity: 0
			}).chain(function(fx){fx.set({left: 0});}.pass(oldFx));
		}
	},
	continuoushorizontal: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
		if (
			((newPos > oldPos) || ((newPos==0) && (oldPos == (this.maxIter-1) ))) &&
			(!((newPos == (this.maxIter-1 )) && (oldPos == 0)))
		) {
			oldFx.set({opacity: 1});
			oldFx.start({
				left: [0, this.galleryElement.offsetWidth * -1]
			});
			newFx.set({opacity: 1, left: this.galleryElement.offsetWidth});
			newFx.start({
				left: [this.galleryElement.offsetWidth, 0]
			});
		} else  {
			oldFx.set({opacity: 1});
			oldFx.start({
				left: [0, this.galleryElement.offsetWidth]
			});
			newFx.set({opacity: 1, left: this.galleryElement.offsetWidth * -1});
			newFx.start({
				left: [this.galleryElement.offsetWidth * -1, 0]
			});
		}
	},
	continuousvertical: function(oldFx, newFx, oldPos, newPos){
		oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
		if (
			((newPos > oldPos) || ((newPos==0) && (oldPos == (this.maxIter-1) ))) &&
			(!((newPos == (this.maxIter-1 )) && (oldPos == 0)))
		) {
			oldFx.set({opacity: 1});
			oldFx.start({
				top: [0, this.galleryElement.offsetHeight * -1]
			});
			newFx.set({opacity: 1, top: this.galleryElement.offsetHeight});
			newFx.start({
				top: [this.galleryElement.offsetHeight, 0]
			});
		} else  {
			oldFx.set({opacity: 1});
			oldFx.start({
				top: [0, this.galleryElement.offsetHeight]
			});
			newFx.set({opacity: 1, top: this.galleryElement.offsetHeight * -1});
			newFx.start({
				top: [this.galleryElement.offsetHeight * -1, 0]
			});
		}
	}
});