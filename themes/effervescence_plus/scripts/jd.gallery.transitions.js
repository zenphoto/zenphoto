/**
 * @author jon
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