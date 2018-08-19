/*
 @author: remy sharp / http://remysharp.com
 @url: http://remysharp.com/2007/12/28/jquery-tag-suggestion/
 @usage: setGlobalTags(['javascript', 'jquery', 'java', 'json']); // applied tags to be used for all implementations
 $('input.tags').tagSuggest(options);

 The selector is the element that the user enters their tag list
 @params:
 matchClass - class applied to the suggestions, defaults to 'tagMatches'
 tagContainer - the type of element uses to contain the suggestions, defaults to 'span'
 tagWrap - the type of element the suggestions a wrapped in, defaults to 'span'
 sort - boolean to force the sorted order of suggestions, defaults to false
 url - optional url to get suggestions if setGlobalTags isn't used.  Must return array of suggested tags
 tags - optional array of tags specific to this instance of element matches
 delay - optional sets the delay between keyup and the request - can help throttle ajax requests, defaults to zero delay
 separator - optional separator string, defaults to ' ' (Brian J. Cardiff)
 @license: Creative Commons License - ShareAlike http://creativecommons.org/licenses/by-sa/3.0/
 @version: 1.4
 @changes: fixed filtering to ajax hits
 */

/*
 * Binary search optimization added and copyright 2015 by Stephen Billard for use with
 * the netPhotoGraphics software.
 *
 * NOTE: this presumes the taglist is sorted!
 *
 */

(function ($) {
	var globalTags = [];

	// creates a public function within our private code.
	// tags can either be an array of strings OR
	// array of objects containing a 'tag' attribute
	window.setGlobalTags = function (tags /* array */) {
		globalTags = getTags(tags);
	};

	function getTags(tags) {
		var tag, i, goodTags = [];
		for (i = 0; i < tags.length; i++) {
			tag = tags[i];
			if (typeof tags[i] == 'object') {
				tag = tags[i].tag;
			}
			goodTags.push(tag.toLowerCase());
		}

		return goodTags;
	}

	$.fn.tagSuggest = function (options) {
		var defaults = {
			'matchClass': 'tagMatches',
			'tagContainer': 'span',
			'tagWrap': 'span',
			'sort': true,
			'tags': null,
			'url': null,
			'delay': 0,
			'quoteSpecial': false,
			'separator': ' '
		};

		var i, tag, userTags = [], settings = $.extend({}, defaults, options);

		if (settings.tags) {
			userTags = getTags(settings.tags);
		} else {
			userTags = globalTags;
		}

		return this.each(function () {
			var tagsElm = $(this);
			var elm = this;
			var matches, fromTab = false;
			var suggestionsShow = false;
			var workingTags = [];
			var currentTag = {"position": 0, tag: ""};
			var tagMatches = document.createElement(settings.tagContainer);

			function showSuggestionsDelayed(el, key) {
				if (settings.delay) {
					if (elm.timer)
						clearTimeout(elm.timer);
					elm.timer = setTimeout(function () {
						showSuggestions(el, key);
					}, settings.delay);
				} else {
					showSuggestions(el, key);
				}
			}

			function showSuggestions(el, key) {
				workingTags = el.value.split(settings.separator);
				matches = [];
				var i, html = '', chosenTags = {}, tagSelected = false;

				// we're looking to complete the tag on currentTag.position (to start with)
				currentTag = {position: currentTags.length - 1, tag: ''};

				for (i = 0; i < currentTags.length && i < workingTags.length; i++) {
					if (!tagSelected &&
									currentTags[i].toLowerCase() != workingTags[i].toLowerCase()) {
						currentTag = {position: i, tag: workingTags[i].toLowerCase()};
						tagSelected = true;
					}
					// lookup for filtering out chosen tags
					chosenTags[currentTags[i].toLowerCase()] = true;
				}

				if (currentTag.tag) {
					// collect potential tags
					if (settings.url) {
						$.ajax({
							'url': settings.url,
							'dataType': 'json',
							'data': {'tag': currentTag.tag},
							'async': false, // wait until this is ajax hit is complete before continue
							'success': function (m) {
								matches = m;
							}
						});
					} else {
						//binary search for start
						i = j = Math.ceil(userTags.length / 2);
						e = currentTag.tag.length;
						while (j > 1) {
							j = Math.ceil(j / 2);
							if (userTags[i].substr(0, e).localeCompare(currentTag.tag) >= 0) {	// current tag >= candidate tag

								i = Math.max(i - j, 0);
							} else {
								i = Math.min(i + j, userTags.length - 1);
							}
						}
						for (i = i; i < userTags.length; i++) {
							if (userTags[i].indexOf(currentTag.tag) === 0) {
								matches.push(userTags[i]);
							} else {
								if (userTags[i].substr(0, e).localeCompare(currentTag.tag) > 0) {
									break;	// stop if past candidates
								}
							}
						}
					}

					matches = $.grep(matches, function (v, i) {
						return !chosenTags[v.toLowerCase()];
					});

					if (settings.sort) {
						matches = matches.sort();
					}

					for (i = 0; i < matches.length; i++) {
						html += '<' + settings.tagWrap + ' class="_tag_suggestion">' + matches[i] + '</' + settings.tagWrap + '>';
					}

					$('span.tagMatches').css('display', 'block');
					tagMatches.html(html);
					suggestionsShow = !!(matches.length);
				} else {
					hideSuggestions();
					$('span.tagMatches').css('display', 'none');
				}
			}

			function hideSuggestions() {
				tagMatches.empty();
				matches = [];
				suggestionsShow = false;
			}

			function setSelection() {
				var v = tagsElm.val();

				// tweak for hintted elements
				// http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
				if (v == tagsElm.attr('title') && tagsElm.is('.hint'))
					v = '';

				currentTags = v.split(settings.separator);
				hideSuggestions();
			}

			function chooseTag(tag) {
				var i, index;
				for (i = 0; i < currentTags.length; i++) {
					if (currentTags[i].toLowerCase() != workingTags[i].toLowerCase()) {
						index = i;
						break;
					}
				}
				var pat = false;
				tag = Encoder.htmlDecode(tag);

				if (settings.quoteSpecial) {
					var result = false;
					var testfor = ['&', '|', '!', ',', '(', ')', '"', '`', "'", ' '];
					for (var j in testfor) {
						if (tag.indexOf(testfor[j]) >= 0) {
							result = true;
							break;
						}
					}
					if (result) {
						tag = '"' + (tag + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0') + '"';
					}
				}
				workingTags[i] = tag;
				tagsElm.val(workingTags.join(settings.separator) + settings.separator);
				tagsElm.blur().focus();
				setSelection();
			}

			function handleKeys(ev) {
				fromTab = false;
				var type = ev.type;
				var resetSelection = false;

				switch (ev.keyCode) {
					case 37: // ignore cases (arrow keys)
					case 38:
					case 39:
					case 40:
					{
						hideSuggestions();
						return true;
					}
					case 224:
					case 17:
					case 16:
					case 18:
					{
						return true;
					}

					case 8:
					{
						// delete - hide selections if we're empty
						if (this.value == '') {
							hideSuggestions();
							setSelection();
							return true;
						} else {
							type = 'keyup'; // allow drop through
							resetSelection = true;
							showSuggestionsDelayed(this);
						}
						break;
					}

					case 9: // return and tab
					case 13:
					{
						if (suggestionsShow) {
							// complete
							chooseTag(matches[0]);

							fromTab = true;
							return false;
						} else {
							return true;
						}
					}
					case 27:
					{
						hideSuggestions();
						setSelection();
						return true;
					}
					case 32:
					{
						setSelection();
						return true;
					}
				}

				if (type == 'keyup') {
					switch (ev.charCode) {
						case 9:
						case 13:
						{
							return true;
						}
					}

					if (resetSelection) {
						setSelection();
					}
					showSuggestionsDelayed(this, ev.charCode);
				}
			}

			tagsElm.after(tagMatches).keypress(handleKeys).keyup(handleKeys).blur(function () {
				if (fromTab == true || suggestionsShow) { // tweak to support tab selection for Opera & IE
					fromTab = false;
					tagsElm.focus();
				}
			});

			// replace with jQuery version
			tagMatches = $(tagMatches).click(function (ev) {
				if (ev.target.nodeName == settings.tagWrap.toUpperCase() && $(ev.target).is('._tag_suggestion')) {
					chooseTag(ev.target.innerHTML);
				}
			}).addClass(settings.matchClass);

			// initialise
			setSelection();
		});
	};
})(jQuery);
