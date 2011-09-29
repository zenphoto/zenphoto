/*
Copyright (c) 2008 Joseph Scott, http://josephscott.org/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// Based on version: 0.1.2
// Modifications by Ozh <ozh at planetozh dot com> for ZenPhoto

(function( $ ) {
	$.fn.eip = function( options ) {
		// Defaults
		var opt = {
			save_url			: document.location.href,
			
			eip_context			: '', // ZenPhoto context: will be 'image' or 'album'
			eip_field			: '', // data field to be edited ('desc', 'title', etc...)

			save_on_enter		: true,
			cancel_on_esc		: true,
			focus_edit			: true,
			select_text			: false,
			edit_event			: "click",
			select_options		: false,
			data				: false,

			form_type			: "text", // text, textarea, select
			size				: false, // calculate at run time
			max_size			: 60,
			rows				: false, // calculate at run time
			max_rows			: 10,
			cols				: 40,

			savebutton_text		: zpstrings.Save,
			savebutton_class	: "jeip-savebutton",
			cancelbutton_text	: zpstrings.Cancel,
			cancelbutton_class	: "jeip-cancelbutton",
			buttons_class		: "jeip-buttons",

			mouseover_class		: "jeip-mouseover",
			editor_class		: "jeip-editor",
			editfield_class		: "jeip-editfield",

			saving_text			: zpstrings.Saving,
			saving_class		: "jeip-saving",

			saving				: '<span id="saving-#{id}" class="#{saving_class}" style="display: none;">#{saving_text}</span>',

			start_form			: '<span id="editor-#{id}" class="#{editor_class}" style="display: none;">',
			form_buttons		: '<span id="buttons-#{id}" class="#{buttons_class}"><input type="button" id="save-#{id}" class="#{savebutton_class}" value="#{savebutton_text}" />&nbsp;<input type="button" id="cancel-#{id}" class="#{cancelbutton_class}" value="#{cancelbutton_text}" /></span>',
			stop_form			: '</span>',

			text_form			: '<input type="text" id="edit-#{id}" class="#{editfield_class}" value="#{value}" /> ',
			textarea_form		: '<textarea cols="#{cols}" rows="#{rows}" id="edit-#{id}" class="#{editfield_class}">#{value}</textarea> ',
			start_select_form	: '<select id="edit-#{id}" class="#{editfield_clas}">',
			select_option_form	: '<option id="edit-option-#{id}-#{option_value}" value="#{option_value}" #{selected}>#{option_text}</option>',
			stop_select_form	: '</select>',

			after_save			: function( self ) {
				$( self ).fadeOut( "fast" );
				$( self ).fadeIn( "fast" );
			},
			on_error			: function( msg ) {
				alert( "Error: " + msg );
			}
		}; // defaults

		if( options ) {
			$.extend( opt, options );
		}

		this.each( function( ) {
			var self = this;

			$( this ).bind( "mouseenter mouseleave", function( e ) {
				$( this ).toggleClass( opt.mouseover_class );
			} );
			
			var t = $( this ).attr('title');
			if ( t ) {
				t+=' ';
			}
			$( this ).attr('title' , t + '(' + zpstrings.ClickToEdit + ')' ) ;			

			$( this ).bind( opt.edit_event, function( e ) {
				_editMode( this );
			} );
		} ); // this.each

		// Private functions
		var _editMode = function( self ) {
			eip_cancel_all(); // cancel any other editable field
		
			$( self ).unbind( opt.edit_event );

			$( self ).removeClass( opt.mouseover_class );
			$( self ).fadeOut( "fast", function( e ) {
				var id		= self.id;
				var value	= $( self ).html( );

				var safe_value	= value.replace( /</g, "&lt;" );
				safe_value		= value.replace( />/g, "&gt;" );
				safe_value		= value.replace( /"/g, "&qout;" );

				var orig_option_value = false;

				var form = _template( opt.start_form, {
					id				: self.id,
					editor_class	: opt.editor_class
				} );

				if( opt.form_type == 'text' ) {
					form += _template( opt.text_form, {
						id				: self.id,
						editfield_class	: opt.editfield_class,
						value			: value
					} );
				} // text form
				else if( opt.form_type == 'textarea' ) {
					var length = value.length;
					var rows = ( length / opt.cols ) + 2;

					for( var i = 0; i < length; i++ ) {
						if( value.charAt( i ) == "\n" ) {
							rows++;
						}
					}
					
					value = value.replace(/\n?<br\/?>\n?/gi, "\n");

					if( rows > opt.max_rows ) {
						rows = opt.max_rows;
					}
					if( opt.rows != false ) {
						rows = opt.rows;
					}
					rows = parseInt( rows );

					form += _template( opt.textarea_form, {
						id				: self.id,
						cols			: opt.cols,
						rows			: rows,
						editfield_class	: opt.editfield_class,
						value			: value
					} ) + '<br />';
				} // textarea form
				else if( opt.form_type == 'select' ) {
					form += _template( opt.start_select_form, {
						id				: self.id,
						editfield_class	: opt.editfield_class
					} );

					$.each( opt.select_options, function( k, v ) {
						var selected = '';
						if( v == value ) {
							selected = 'selected="selected"';
						}

						if( value == v ) {
							orig_option_value = k;
						}

						form += _template( opt.select_option_form, {
							id			: self.id,
							option_value: k,
							option_text	: v,
							selected	: selected
						} );
					} );

					form += _template( opt.stop_select_form, { } );
				} // select form

				form += _template( opt.form_buttons, {
					id					: self.id,
					savebutton_class	: opt.savebutton_class,
					savebutton_text		: opt.savebutton_text,
					cancelbutton_class	: opt.cancelbutton_class + ' _zp_cancel_button_',
					cancelbutton_text	: opt.cancelbutton_text,
					buttons_class		: opt.buttons_class
				} );

				form += _template( opt.stop_form, { } );

				$( self ).after( form );
				$( "#editor-" + self.id ).toggle( );

				if( opt.focus_edit ) {
					$( "#edit-" + self.id ).focus( );
				}

				if( opt.select_text ) {
					$( "#edit-" + self.id ).select( );
				}

				$( "#cancel-" + self.id ).bind( "click", function( e ) {
					_cancelEdit( self );
				} );

				$( "#edit-" + self.id ).keydown( function( e ) {
					// cancel
					if( e.which == 27 ) {
						_cancelEdit( self );
					}

					// save
					if( opt.form_type != "textarea" && e.which == 13 ) {
						_saveEdit( self, orig_option_value );
					}
				} );

				$( "#save-" + self.id ).bind( "click", function( e ) {
					return _saveEdit( self, orig_option_value );
				} ); // save click
				
				if ($('#edit-' + id ).tagSuggest && opt.eip_field == '_update_tags'){
					$('#edit-' + id ).tagSuggest( { separator:',', tags: _tagList } );
				}
				
			} ); // this fadeOut
		} // function _editMode

		var _template = function( template, values ) {
			var replace = function( str, match ) {
				return typeof values[match] === "string" || typeof values[match] === 'number' ? values[match] : str;
			};
			return template.replace( /#\{([^{}]*)}/g, replace );
		};

		var _cancelEdit = function( self ) {
			$( "#editor-" + self.id ).fadeOut( "fast" );
			$( "#editor-" + self.id ).remove( );

			$( self ).bind( opt.edit_event, function( e ) {
				_editMode( self );
			} );

			$( self ).removeClass( opt.mouseover_class );
			$( self ).fadeIn( "fast" );
		};
		
		var _saveEdit = function( self, orig_option_value ) {
			var orig_value = $( self ).html( );
			var new_value = $( "#edit-" + self.id ).attr( "value" );

			if( orig_value == new_value ) {
				$( "#editor-" + self.id ).fadeOut( "fast" );
				$( "#editor-" + self.id ).remove( );

				$( self ).bind( opt.edit_event, function( e ) {
					_editMode( self );
				} );

				$( self ).removeClass( opt.mouseover_class );
				$( self ).fadeIn( "fast" );

				return true;
			}

			$( "#editor-" + self.id ).after( _template( opt.saving, {
				id			: self.id,
				saving_class: opt.saving_class,
				saving_text	: opt.saving_text
			} ) );
			$( "#editor-" + self.id ).fadeOut( "fast", function( ) {
				$( "#saving-" + self.id).fadeIn( "fast" );
			} );

			var ajax_data = {
				url			: location.href,
				id			: self.id,
				form_type	: opt.form_type,
				eip_context	: opt.eip_context,
				eip_field	: opt.eip_field,
				orig_value	: orig_value,
				new_value	: $( "#edit-" + self.id ).attr( "value" ),
				data		: opt.data
			}

			if( opt.form_type == 'select' ) {
				ajax_data.orig_option_value = orig_option_value;
				ajax_data.orig_option_text = orig_value;
				ajax_data.new_option_text = $( "#edit-option-" + self.id + "-" + new_value ).html( );
			}

			$.ajax( {
				url		: opt.save_url,
				type	: "POST",
				dataType: "text",
				data	: ajax_data,
				success	: function( data ) {

					$( "#editor-" + self.id ).fadeOut( "fast" );
					$( "#editor-" + self.id ).remove( );

					if( data.is_error == true ) {
						opt.on_error( data.error_text );
					}
					else {
						$( self ).html( data );
					}

					$( "#saving-" + self.id ).fadeOut( "fast" );
					$( "#saving-" + self.id ).remove( );

					$( self ).bind( opt.edit_event, function( e ) {
						_editMode( self );
					} );

					$( self ).addClass( opt.mouseover_class );
					$( self ).fadeIn( "fast" );

					if( opt.after_save != false ) {
						opt.after_save( self );
					}

					$( self ).removeClass( opt.mouseover_class );
				} // success
			} ); // ajax
		}; // _saveEdit


	}; // inplaceEdit
})( jQuery );

/*** Cancel all editable fields ***/
function eip_cancel_all() {
	jQuery('._zp_cancel_button_').click();
}

/*** Wrapper function to use in ZenPhoto ***/
function editInPlace(target, context, field) {
	var form_type = (field == 'desc') ? 'textarea' : 'text';
	jQuery('#'+target).eip(
		{
			eip_context: context,
			eip_field: field,
			form_type: form_type
		}	
	);
}

/*** Fallback for i18n not loaded ***/
if (!zpstrings) {
	zpstrings = {};
}

/*** Default styling that can be overridden by theme CSS ***/
jQuery(document).ready(function(){
	jQuery('head').prepend('<style>.jeip-mouseover{background:#ffa;}</style>');
});
