/**
 * pasteobj tinyMCE popup
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * adapted from the tinyMCE 4: compat3x
 */

var tinymce, tinyMCE;

/**
 * TinyMCE popup/dialog helper class. This gives you easy access to the
 * parent editor instance and a bunch of other things. It's higly recommended
 * that you load this script into your dialogs.
 *
 * @static
 * @class pasteObjPopup
 */
var pasteObjPopup = {
	/**
	 * Initializes the popup this will be called automatically.
	 *
	 * @method init
	 */
	init: function () {
		var self = this, parentWin, settings, uiWindow;

		// Find window & API
		parentWin = self.getWin();
		tinymce = tinyMCE = parentWin.tinymce;
		self.editor = tinymce.EditorManager.activeEditor;
		self.params = self.editor.windowManager.getParams();

		uiWindow = self.editor.windowManager.windows[self.editor.windowManager.windows.length - 1];
		self.features = uiWindow.features;
		self.uiWindow = uiWindow;

		settings = self.editor.settings;


		// Setup local DOM
		self.dom = self.editor.windowManager.createInstance('tinymce.dom.DOMUtils', document, {
			ownEvents: true,
			proxy: pasteObjPopup._eventProxy
		});

		self.dom.bind(window, 'ready', self._onDOMLoaded, self);

		// Setup on init listeners
		self.listeners = [];

		/**
		 * Fires when the popup is initialized.
		 *
		 * @event onInit
		 * @param {tinymce.Editor} editor Editor instance.
		 * @example
		 * // Alerts the selected contents when the dialog is loaded
		 * pasteObjPopup.onInit.add(function(ed) {
		 *     alert(ed.selection.getContent());
		 * });
		 *
		 * // Executes the init method on page load in some object using the SomeObject scope
		 * pasteObjPopup.onInit.add(SomeObject.init, SomeObject);
		 */
		self.onInit = {
			add: function (func, scope) {
				self.listeners.push({func: func, scope: scope});
			}
		};

		self.isWindow = !self.getWindowArg('mce_inline');
		self.id = self.getWindowArg('mce_window_id');
	},
	/**
	 * Returns the reference to the parent window that opened the dialog.
	 *
	 * @method getWin
	 * @return {Window} Reference to the parent window that opened the dialog.
	 */
	getWin: function () {
		// Added frameElement check to fix bug: #2817583
		return (!window.frameElement && window.dialogArguments) || opener || parent || top;
	},
	/**
	 * Returns a window argument/parameter by name.
	 *
	 * @method getWindowArg
	 * @param {String} name Name of the window argument to retrive.
	 * @param {String} defaultValue Optional default value to return.
	 * @return {String} Argument value or default value if it wasn't found.
	 */
	getWindowArg: function (name, defaultValue) {
		var value = this.params[name];

		return tinymce.is(value) ? value : defaultValue;
	},
	/**
	 * Returns a editor parameter/config option value.
	 *
	 * @method getParam
	 * @param {String} name Name of the editor config option to retrive.
	 * @param {String} defaultValue Optional default value to return.
	 * @return {String} Parameter value or default value if it wasn't found.
	 */
	getParam: function (name, defaultValue) {
		return this.editor.getParam(name, defaultValue);
	},
	/**
	 * Executed a command on editor that opened the dialog/popup.
	 *
	 * @method execCommand
	 * @param {String} cmd Command to execute.
	 * @param {Boolean} ui Optional boolean value if the UI for the command should be presented or not.
	 * @param {Object} val Optional value to pass with the comman like an URL.
	 * @param {Object} a Optional arguments object.
	 */
	execCommand: function (cmd, ui, val, args) {
		args = args || {};
		args.skip_focus = 1;

		this.restoreSelection();
		return this.editor.execCommand(cmd, ui, val, args);
	},
	/**
	 * Resizes the dialog to the inner size of the window. This is needed since various browsers
	 * have different border sizes on windows.
	 *
	 * @method resizeToInnerSize
	 */
	resizeToInnerSize: function () {
		/*var self = this;

		 // Detach it to workaround a Chrome specific bug
		 // https://sourceforge.net/tracker/?func=detail&atid=635682&aid=2926339&group_id=103281
		 setTimeout(function() {
		 var vp = self.dom.getViewPort(window);

		 self.editor.windowManager.resizeBy(
		 self.getWindowArg('mce_width') - vp.w,
		 self.getWindowArg('mce_height') - vp.h,
		 self.id || window
		 );
		 }, 10);*/
	},
	/**
	 * Will executed the specified string when the page has been loaded. This function
	 * was added for compatibility with the 2.x branch.
	 *
	 * @method executeOnLoad
	 * @param {String} evil String to evalutate on init.
	 */
	executeOnLoad: function (evil) {
		this.onInit.add(function () {
			eval(evil);
		});
	},
	/**
	 * Stores the current editor selection for later restoration. This can be useful since some browsers
	 * looses it's selection if a control element is selected/focused inside the dialogs.
	 *
	 * @method storeSelection
	 */
	storeSelection: function () {
		this.editor.windowManager.bookmark = pasteObjPopup.editor.selection.getBookmark(1);
	},
	/**
	 * Restores any stored selection. This can be useful since some browsers
	 * looses it's selection if a control element is selected/focused inside the dialogs.
	 *
	 * @method restoreSelection
	 */
	restoreSelection: function () {
		var self = pasteObjPopup;

		if (!self.isWindow && tinymce.isIE) {
			self.editor.selection.moveToBookmark(self.editor.windowManager.bookmark);
		}
	},
	/**
	 * Opens a filebrowser/imagebrowser this will set the output value from
	 * the browser as a value on the specified element.
	 *
	 * @method openBrowser
	 * @param {string} element_id Id of the element to set value in.
	 * @param {string} type Type of browser to open image/file/flash.
	 * @param {string} option Option name to get the file_broswer_callback function name from.
	 */
	openBrowser: function (element_id, type) {
		pasteObjPopup.restoreSelection();
		this.editor.execCallback('file_browser_callback', element_id, document.getElementById(element_id).value, type, window);
	},
	/**
	 * Closes the current window.
	 *
	 * @method close
	 */
	close: function () {
		var t = this;

		// To avoid domain relaxing issue in Opera
		function close() {
			t.editor.windowManager.close(window);
			tinymce = tinyMCE = t.editor = t.params = t.dom = t.dom.doc = null; // Cleanup
		}

		if (tinymce.isOpera) {
			t.getWin().setTimeout(close, 0);
		} else {
			close();
		}
	},
	// Internal functions

	_restoreSelection: function () {
		var e = window.event.srcElement;

		if (e.nodeName == 'INPUT' && (e.type == 'submit' || e.type == 'button')) {
			pasteObjPopup.restoreSelection();
		}
	},
	/*	_restoreSelection : function() {
	 var e = window.event.srcElement;

	 // If user focus a non text input or textarea
	 if ((e.nodeName != 'INPUT' && e.nodeName != 'TEXTAREA') || e.type != 'text')
	 pasteObjPopup.restoreSelection();
	 },*/

	_onDOMLoaded: function () {
		var t = pasteObjPopup, ti = document.title, h, nv;

		// Translate page
		if (t.features.translate_i18n !== false) {
			var map = {
				"update": "Ok",
				"insert": "Ok",
				"cancel": "Cancel",
				"not_set": "--",
				"class_name": "Class name",
				"browse": "Browse"
			};

			var langCode = tinymce.settings.language || 'en';
			for (var key in map) {
				tinymce.i18n.data[langCode + "." + key] = tinymce.i18n.translate(map[key]);
			}

			h = document.body.innerHTML;

			// Replace a=x with a="x" in IE
			if (tinymce.isIE) {
				h = h.replace(/ (value|title|alt)=([^"][^\s>]+)/gi, ' $1="$2"');
			}

			document.dir = t.editor.getParam('directionality', '');

			if ((nv = t.editor.translate(h)) && nv != h) {
				document.body.innerHTML = nv;
			}

			if ((nv = t.editor.translate(ti)) && nv != ti) {
				document.title = ti = nv;
			}
		}

		if (!t.editor.getParam('browser_preferred_colors', false) || !t.isWindow) {
			t.dom.addClass(document.body, 'forceColors');
		}

		document.body.style.display = '';

		// Restore selection in IE when focus is placed on a non textarea or input element of the type text
		if (tinymce.Env.ie) {
			if (tinymce.Env.ie < 11) {
				document.attachEvent('onmouseup', pasteObjPopup._restoreSelection);

				// Add base target element for it since it would fail with modal dialogs
				t.dom.add(t.dom.select('head')[0], 'base', {target: '_self'});
			} else {
				document.addEventListener('mouseup', pasteObjPopup._restoreSelection, false);
			}
		}

		t.restoreSelection();
		t.resizeToInnerSize();

		// Set inline title
		if (!t.isWindow) {
			t.editor.windowManager.setTitle(window, ti);
		} else {
			window.focus();
		}

		if (!tinymce.isIE && !t.isWindow) {
			t.dom.bind(document, 'focus', function () {
				t.editor.windowManager.focus(t.id);
			});
		}

		// Patch for accessibility
		tinymce.each(t.dom.select('select'), function (e) {
			e.onkeydown = pasteObjPopup._accessHandler;
		});

		// Call onInit
		// Init must be called before focus so the selection won't get lost by the focus call
		tinymce.each(t.listeners, function (o) {
			o.func.call(o.scope, t.editor);
		});

		// Move focus to window
		if (t.getWindowArg('mce_auto_focus', true)) {
			window.focus();

			// Focus element with mceFocus class
			tinymce.each(document.forms, function (f) {
				tinymce.each(f.elements, function (e) {
					if (t.dom.hasClass(e, 'mceFocus') && !e.disabled) {
						e.focus();
						return false; // Break loop
					}
				});
			});
		}

		document.onkeyup = pasteObjPopup._closeWinKeyHandler;

		if ('textContent' in document) {
			t.uiWindow.getEl('head').firstChild.textContent = document.title;
		} else {
			t.uiWindow.getEl('head').firstChild.innerText = document.title;
		}
	},
	_accessHandler: function (e) {
		e = e || window.event;

		if (e.keyCode == 13 || e.keyCode == 32) {
			var elm = e.target || e.srcElement;

			if (elm.onchange) {
				elm.onchange();
			}

			return tinymce.dom.Event.cancel(e);
		}
	},
	_closeWinKeyHandler: function (e) {
		e = e || window.event;

		if (e.keyCode == 27) {
			pasteObjPopup.close();
		}
	},
	_eventProxy: function (id) {
		return function (evt) {
			pasteObjPopup.dom.events.callNativeHandler(id, evt);
		};
	}
};

pasteObjPopup.init();

tinymce.util.Dispatcher = function (scope) {
	this.scope = scope || this;
	this.listeners = [];

	this.add = function (callback, scope) {
		this.listeners.push({cb: callback, scope: scope || this.scope});

		return callback;
	};

	this.addToTop = function (callback, scope) {
		var self = this, listener = {cb: callback, scope: scope || self.scope};

		// Create new listeners if addToTop is executed in a dispatch loop
		if (self.inDispatch) {
			self.listeners = [listener].concat(self.listeners);
		} else {
			self.listeners.unshift(listener);
		}

		return callback;
	};

	this.remove = function (callback) {
		var listeners = this.listeners, output = null;

		tinymce.each(listeners, function (listener, i) {
			if (callback == listener.cb) {
				output = listener;
				listeners.splice(i, 1);
				return false;
			}
		});

		return output;
	};

	this.dispatch = function () {
		var self = this, returnValue, args = arguments, i, listeners = self.listeners, listener;

		self.inDispatch = true;

		// Needs to be a real loop since the listener count might change while looping
		// And this is also more efficient
		for (i = 0; i < listeners.length; i++) {
			listener = listeners[i];
			returnValue = listener.cb.apply(listener.scope, args.length > 0 ? args : [listener.scope]);

			if (returnValue === false) {
				break;
			}
		}

		self.inDispatch = false;

		return returnValue;
	};
};
