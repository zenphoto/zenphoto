<?php
	/**
	 * language pack
	 * @author Logan Cai (cailongqun@yahoo.com.cn)
	 * @link www.phpletter.com
	 * @since 22/April/2007
	 *
	 */
	define('DATE_TIME_FORMAT', 'd/M/Y H:i:s');
	//Common
	//Menu
	
	
	
	
	define('MENU_SELECT', 'Selecteer');
	define('MENU_DOWNLOAD', 'Download');
	define('MENU_PREVIEW', 'Preview');
	define('MENU_RENAME', 'Hernoemen');
	define('MENU_EDIT', 'Wijzig');
	define('MENU_CUT', 'Knippen');
	define('MENU_COPY', 'Kopi&euml;ren ');
	define('MENU_DELETE', 'Verwijderen');
	define('MENU_PLAY', 'Afspelen');
	define('MENU_PASTE', 'Plakken');
	
	//Label
		//Top Action
	define('LBL_ACTION_REFRESH', 'Vernieuwen');
	define("LBL_ACTION_DELETE", 'Wissen');
	define('LBL_ACTION_CUT', 'Knippen');
	define('LBL_ACTION_COPY', 'Kopi&euml;ren ');
	define('LBL_ACTION_PASTE', 'Plakken');
	define('LBL_ACTION_CLOSE', 'Sluiten');
	define('LBL_ACTION_SELECT_ALL', 'Selecteer Alles');
		//File Listing
	define('LBL_NAME', 'Naam');
	define('LBL_SIZE', 'Grootte');
	define('LBL_MODIFIED', 'Gewijzigd:');
		//File Information
	define('LBL_FILE_INFO', 'Bestands informatie:');
	define('LBL_FILE_NAME', 'Naam:');	
	define('LBL_FILE_CREATED', 'Gemaakt:');
	define("LBL_FILE_MODIFIED", 'Gewijzigd:');
	define("LBL_FILE_SIZE", 'Bestandsgrootte:');
	define('LBL_FILE_TYPE', 'Bestandstype:');
	define("LBL_FILE_WRITABLE", 'Schrijven?');
	define("LBL_FILE_READABLE", 'Lezen?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Map informatie');
	define("LBL_FOLDER_PATH", 'Pad:');
	define('LBL_CURRENT_FOLDER_PATH', 'Aktueel Map Pad:');
	define("LBL_FOLDER_CREATED", 'Gemaakt:');
	define("LBL_FOLDER_MODIFIED", 'Gewijzigd:');
	define('LBL_FOLDER_SUDDIR', 'Submappen:');
	define("LBL_FOLDER_FIELS", 'Bestanden:');
	define("LBL_FOLDER_WRITABLE", 'Schrijven?');
	define("LBL_FOLDER_READABLE", 'Lezen?');
	define('LBL_FOLDER_ROOT', 'Root Map');
		//Preview
	define("LBL_PREVIEW", 'Preview');
	define('LBL_CLICK_PREVIEW', 'Klik hier voor een preview.');
	//Buttons
	define('LBL_BTN_SELECT', 'Selecteren');
	define('LBL_BTN_CANCEL', 'Annuleren');
	define("LBL_BTN_UPLOAD", 'Uploaden');
	define('LBL_BTN_CREATE', 'Cre&euml;ren');
	define('LBL_BTN_CLOSE', 'Afsluiten');
	define("LBL_BTN_NEW_FOLDER", 'Nieuwe Map');
	define('LBL_BTN_NEW_FILE', 'Nieuw Bestand');
	define('LBL_BTN_EDIT_IMAGE', 'Wijzigen');
	define('LBL_BTN_VIEW', 'Selecteer Weergave');
	define('LBL_BTN_VIEW_TEXT', 'Tekst');
	define('LBL_BTN_VIEW_DETAILS', 'Details');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Thumbnails');
	define('LBL_BTN_VIEW_OPTIONS', 'Weergeven in:');
	//pagination
	define('PAGINATION_NEXT', 'Volgende');
	define('PAGINATION_PREVIOUS', 'Vorige');
	define('PAGINATION_LAST', 'Laatste');
	define('PAGINATION_FIRST', 'Eerste');
	define('PAGINATION_ITEMS_PER_PAGE', 'Toon %s items per pagina');
	define('PAGINATION_GO_PARENT', 'Ga een map omhoog');
	//System
	define('SYS_DISABLED', 'Toegang geweigerd: Het systeem is geblokkeerd.');
	
	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Geen document(en) geselecteerd om te knippen.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Geen document(en) geselecteerd om te kopi&euml;ren).');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Geen document(en) geselecteerd om te plakken.');
	define('WARNING_CUT_PASTE', 'Geselecteerde documenten naar huidige map verplaatsen?');
	define('WARNING_COPY_PASTE', 'Geselecteerde documenten naar huidige map kopi&euml;ren?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Geen doel map ingegeven.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Doel map niet gevonden.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Je mag geen bestanden verplaatsen naar deze map');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Kon dit bestand niet verplaatsen (%s): Verplaatsen binnen dezelfde map is niet mogelijk.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Kon dit bestand niet verplaatsen (%s): Het originele bestand bestaat niet.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Kon dit bestand niet verplaatsen (%s): Het originele bestand is geblokkeerd.');
 
	define('ERR_NOT_FILES_PASTED', 'Geen bestand(en) geplaatst.');

	//Search
	define('LBL_SEARCH', 'Zoeken');
	define('LBL_SEARCH_NAME', 'Bestandsnaam (gedeeltelijk):');
	define('LBL_SEARCH_FOLDER', 'Zoek in:');
	define('LBL_SEARCH_QUICK', 'Snel zoeken');
	define('LBL_SEARCH_MTIME', 'Bestand laatste wijziging (van/tot):');
	define('LBL_SEARCH_SIZE', 'Bestands grootte:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Geavanceerde Opties');
	define('LBL_SEARCH_FILE_TYPES', 'Bestand Types:');
	define('SEARCH_TYPE_EXE', 'Toepassing');
	
	define('SEARCH_TYPE_IMG', 'Afbeelding');
	define('SEARCH_TYPE_ARCHIVE', 'Archief');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Video');
	define('SEARCH_TYPE_MOVIE', 'Film');
	define('SEARCH_TYPE_MUSIC', 'Muziek');
	define('SEARCH_TYPE_FLASH', 'Flash');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Document');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Text');
	define('SEARCH_TYPE_UNKNOWN', 'Onbekend');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Alle Bestands Types');
	define('LBL_SEARCH_RECURSIVELY', 'Zoek Recursief:');
	define('LBL_RECURSIVELY_YES', 'Ja');
	define('LBL_RECURSIVELY_NO', 'Nee');
	define('BTN_SEARCH', 'Nu Zoeken');
	//thickbox
	define('THICKBOX_NEXT', 'Volgende&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Vorige');
	define('THICKBOX_CLOSE', 'Sluiten');
	//Calendar
	define('CALENDAR_CLOSE', 'Sluiten');
	define('CALENDAR_CLEAR', 'Opschonen');
	define('CALENDAR_PREVIOUS', '&lt;Volgende');
	define('CALENDAR_NEXT', 'Vorige&gt;');
	define('CALENDAR_CURRENT', 'Vandaag');
	define('CALENDAR_MON', 'Maa');
	define('CALENDAR_TUE', 'Din');
	define('CALENDAR_WED', 'Woe');
	define('CALENDAR_THU', 'Don');
	define('CALENDAR_FRI', 'Vrij');
	define('CALENDAR_SAT', 'Zat');
	define('CALENDAR_SUN', 'Zon');
	define('CALENDAR_JAN', 'Jan');
	define('CALENDAR_FEB', 'Feb');
	define('CALENDAR_MAR', 'Mar');
	define('CALENDAR_APR', 'Apr');
	define('CALENDAR_MAY', 'Mei');
	define('CALENDAR_JUN', 'Jun');
	define('CALENDAR_JUL', 'Jul');
	define('CALENDAR_AUG', 'Aug');
	define('CALENDAR_SEP', 'Sep');
	define('CALENDAR_OCT', 'Okt');
	define('CALENDAR_NOV', 'Nov');
	define('CALENDAR_DEC', 'Dec');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Selecteer een bestand.');
	define('ERR_NOT_DOC_SELECTED', 'Geen documenten geselecteerd om te wissen.');
	define('ERR_DELTED_FAILED', 'Wissen geselecteerde documenten niet gelukt.');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Het pad is niet toegestaan.');
		//class manager
	define("ERR_FOLDER_NOT_FOUND", 'Map niet gevonden: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Naam mag alleen letters, cijfers, spaties, apostroph en laag streepje bevatten.');
	define('ERR_RENAME_EXISTS', 'Naam bestaat reeds in deze map, geef bestand een unieke naam.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Het bestand/de map bestaat niet.');
	define('ERR_RENAME_FAILED', 'Hernoemen niet gelukt.');
	define('ERR_RENAME_EMPTY', 'Geef bestand een naam.');
	define("ERR_NO_CHANGES_MADE", 'Bestand is niet gewijzigd.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Bestandsextensie niet toegestaan.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Naam mag alleen letters, cijfers, spaties, apostroph en laag streepje bevatten.');
	define('ERR_FOLDER_EXISTS', 'Map bestaat reeds, geef nieuwe map een unieke naam.');
	define('ERR_FOLDER_CREATION_FAILED', 'Cre&euml;ren map niet gelukt.');
	define('ERR_FOLDER_NAME_EMPTY', 'Geef map een naam.');
	define('FOLDER_FORM_TITLE', 'Nieuwe Map Formulier');
	define('FOLDER_LBL_TITLE', 'Map Titel:');
	define('FOLDER_LBL_CREATE', 'Cre&euml;er  Folder');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Nieuw Bestand Formulier');
	define('NEW_FILE_LBL_TITLE', 'Bestanda Naam:');
	define('NEW_FILE_CREATE', 'Cre&euml;er Bestand');
		//file upload
	define("ERR_FILE_NAME_FORMAT", 'Naam mag alleen letters, cijfers, spaties, apostroph en laag streepje bevatten.');
	define('ERR_FILE_NOT_UPLOADED', 'Geen bestanden geselecteerd om te uploaden.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Uploaden bestandstype is niet toegestaan.');
	define('ERR_FILE_MOVE_FAILED', 'Verplaatsen bestand is niet gelukt.');
	define('ERR_FILE_NOT_AVAILABLE', 'Het bestand is niet beschikbaar.');
	define('ERROR_FILE_TOO_BID', 'Het bestand is te groot. (max: %s)');
	define('FILE_FORM_TITLE', 'Bestand Upload Formulier');
	define('FILE_LABEL_SELECT', 'Selecteer Bestand');
	define('FILE_LBL_MORE', 'Voeg Bestand Toe Voor Upload');
	define('FILE_CANCEL_UPLOAD', 'Bestands Upload Afbreken');
	define('FILE_LBL_UPLOAD', 'Upload');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Geen bestande geselecteerd voor download.');
	//Rename
	define('RENAME_FORM_TITLE', 'Naam Wijzigings Formulier');
	define('RENAME_NEW_NAME', 'Nieuwe Naam');
	define('RENAME_LBL_RENAME', 'Wijzig');

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Klik om naar deze map te gaan...');
	define("TIP_DOC_RENAME", 'Dubbelklik om te wijzigen...');
	define('TIP_FOLDER_GO_UP', 'Klik om naar bovenliggende folder te gaan...');
	define("TIP_SELECT_ALL", 'Alles selecteren');
	define("TIP_UNSELECT_ALL", 'Alles deselecteren');
	//WARNING
	define('WARNING_DELETE', 'Geselecteerde bestanden verwijderen?');
	define('WARNING_IMAGE_EDIT', 'Selecteer een afbeelding om te wijzigen.');
	define('WARNING_NOT_FILE_EDIT', 'Selecteer een bestand om te wijzigen.');
	define('WARING_WINDOW_CLOSE', 'Venster sluiten?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Geen voorbeeld beschikbaar.');
	define('PREVIEW_OPEN_FAILED', 'Weergeven voorbeeld niet gelukt.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Laden afbeelding niet gelukt');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager Login Formulier');
	define('LOGIN_FORM_TITLE', 'Login Formulier');
	define('LOGIN_USERNAME', 'Gebruiker:');
	define('LOGIN_PASSWORD', 'Wachtwoord:');
	define('LOGIN_FAILED', 'Ongeldige gebruiker/wachtwoord.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', "Afbeelding is niet gewijzigd.");
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Afbeelding bestaat niet');
		define('IMG_WARNING_LOST_CHANAGES', 'Niet opgeslagen wijzigingen gaan verloren, doorgaan?');
		define('IMG_WARNING_REST', 'Niet opgeslagen wijzigingen gaan verloren bij resetten, doorgaan?');
		define('IMG_WARNING_EMPTY_RESET', 'Afbeelding is niet gewijzigd');
		define('IMG_WARING_WIN_CLOSE', 'Venster sluiten?');
		define('IMG_WARNING_UNDO', 'Wijziging ongedaan maken?');
		define('IMG_WARING_FLIP_H', 'Afbeelding horizontaal spiegelen?');
		define('IMG_WARING_FLIP_V', 'Afbeelding verticaal spiegelen?');
		define('IMG_INFO', 'Afbeelding informatie');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Grootte aanpassen:');
			define('IMG_MODE_CROP', 'Bijsnijden:');
			define('IMG_MODE_ROTATE', 'Roteren:');
			define('IMG_MODE_FLIP', 'Spiegelen:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg;CCW');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg;CW');
			define('IMG_BTN_FLIP_H', 'Horizontaal spiegelen');
			define('IMG_BTN_FLIP_V', 'Verticaal spiegelen');
			define('IMG_BTN_RESET', 'Reset');
			define('IMG_BTN_UNDO', 'Ongedaan maken');
			define('IMG_BTN_SAVE', 'Opslaan');
			define('IMG_BTN_CLOSE', 'Afsluiten');
			define('IMG_BTN_SAVE_AS', 'Opslaan Als');
			define('IMG_BTN_CANCEL', 'Afbreken');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Verhoudingen?');
		//Label
			define('IMG_LBL_WIDTH', 'Breedte:');
			define('IMG_LBL_HEIGHT', 'Hoogte:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Ratio:');
			define('IMG_LBL_ANGLE', 'Hoek:');
			define('IMG_LBL_NEW_NAME', 'Nieuwe Naam:');
			define('IMG_LBL_SAVE_AS', 'Opslaan Als Formulier');
			define('IMG_LBL_SAVE_TO', 'Opslaan Als:');
			define('IMG_LBL_ROOT_FOLDER', 'Root Map');
		//Editor
		//Save as 
			define('IMG_NEW_NAME_COMMENTS', 'Geef geen afbeeldings extensie (bijv. .jpg) mee/in.');
			define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Naam mag alleen letters, cijfers, spaties, apostroph en laag streepje bevatten.');
			define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Geen doel map geselecteerd.');	
			define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'De doel map bestaat niet.');
			define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Er is al een afbeelding met die naam.');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Afbeeldingen-pad is leeg.');
		define('IMG_SAVE_NOT_EXISTS', 'Afbeelding bestaat niet.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Geen machtiging om dit bestand te openen.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Onverwachte afbeelding Operation Mode');
		define('IMG_SAVE_RESIZE_FAILED', 'Aanpassen grootte afbeelding niet gelukt.');
		define('IMG_SAVE_CROP_FAILED', 'Bijsnijden afbeelding niet gelukt.');
		define('IMG_SAVE_FAILED', 'Opslaan afbeelding niet gelukt.');
		define('IMG_SAVE_BACKUP_FAILED', 'Bewaren originele afbeelding niet gelukt.');
		define('IMG_SAVE_ROTATE_FAILED', 'Roteren afbeelding niet gelukt.');
		define('IMG_SAVE_FLIP_FAILED', 'Spiegelen afbeelding niet geslukt.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Openen afbeelding vanuit sessie niet gelukt.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Openen afbeelding niet mogelijk');
		
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Ongedaan maken geschiedenis niet mogelijk.');
		define('IMG_UNDO_COPY_FAILED', 'Ongedaan maken kopi&euml;ren niet mogelijk.');
		define('IMG_UNDO_DEL_FAILED', 'Ongedaan maken verwijderen niet mogelijk.');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define("SESSION_PERSONAL_DIR_NOT_FOUND", 'Map niet gevonden; deze zou aangemaakt moeten zijn in de session-map.');
		define("SESSION_COUNTER_FILE_CREATE_FAILED", 'Openen session counter-bestand niet gelukt.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Schrijven naar session counter-bestand niet gelukt.');
	//88888888888   Session   888888888888888888888
	
	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Bestand niet gevonden.');
		define('TXT_EXT_NOT_SELECTED', 'Selecteer bestands extensie');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Selecteer de juiste map');
		define('TXT_UNKNOWN_REQUEST', 'Onmogelijk verzoek.');
		define('TXT_DISALLOWED_EXT', 'Je mag deze bestandstypen wijzigen / toevoegen.');
		define('TXT_FILE_EXIST', 'Bestand bestaat al.');
		define('TXT_FILE_NOT_EXIST', 'Geen bestand gevonden.');
		define('TXT_CREATE_FAILED', 'Kon geen nieuw bestand maken.');
		define('TXT_CONTENT_WRITE_FAILED', 'Kon geen veranderingen in bestand schrijven.');
		define('TXT_FILE_OPEN_FAILED', 'Kon het bestand niet openen.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Kon het bestand niet bijwerken.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Naam mag alleen letters, cijfers, spaties, apostroph en laag streepje bevatten.');
	//88888888888   Above for Text Editor   888888888888888888888
	
	
?>