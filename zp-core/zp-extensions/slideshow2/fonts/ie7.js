/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referring to this file must be placed before the ending body tag. */

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'cycle2-icons\'">' + entity + '</span>' + html;
	}
	var icons = {
		'icon-play': '&#xe600;',
		'icon-pause': '&#xe601;',
		'icon-arrow-down': '&#xe605;',
		'icon-stop': '&#xe602;',
		'icon-arrow-right': '&#xe606;',
		'icon-backward': '&#xe603;',
		'icon-arrow-up': '&#xe607;',
		'icon-forward': '&#xe604;',
		'icon-arrow-left': '&#xe608;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, attr, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
