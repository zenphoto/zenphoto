<?php
	/**
	 * language pack
	 * @author Logan Cai (cailongqun [at] yahoo [dot] com [dot] cn)
	 * @link www.phpletter.com
	 * @since 22/April/2007
	 *
	 *Translated to Slovak by Jakub Meisner
	 */
	define('DATE_TIME_FORMAT', 'd/M/Y H:i:s');
	//Common
	//Menu
	
	
	
	
	define('MENU_SELECT', 'Označiť');
	define('MENU_DOWNLOAD', 'Stiahnuť');
	define('MENU_PREVIEW', 'Zobraziť');
	define('MENU_RENAME', 'Premenovať');
	define('MENU_EDIT', 'Upraviť');
	define('MENU_CUT', 'Vystrihnúť');
	define('MENU_COPY', 'Skopírovať');
	define('MENU_DELETE', 'Vymazať');
	define('MENU_PLAY', 'Prehrať');
	define('MENU_PASTE', 'Vložiť');
	
	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Obnoviť');
		define('LBL_ACTION_DELETE', 'Vymazať');
		define('LBL_ACTION_CUT', 'Vystrihnúť');
		define('LBL_ACTION_COPY', 'Skopírovať');
		define('LBL_ACTION_PASTE', 'Vložiť');
		define('LBL_ACTION_CLOSE', 'Zatvoriť');
		define('LBL_ACTION_SELECT_ALL', 'Označiť všetko');
		//File Listing
	define('LBL_NAME', 'Názov');
	define('LBL_SIZE', 'Veľkosť');
	define('LBL_MODIFIED', 'Zmenené');
		//File Information
	define('LBL_FILE_INFO', 'Informácie o súbore:');
	define('LBL_FILE_NAME', 'Názov:');	
	define('LBL_FILE_CREATED', 'Vytvorené:');
	define('LBL_FILE_MODIFIED', 'Zmenené:');
	define('LBL_FILE_SIZE', 'Veľkosť súboru:');
	define('LBL_FILE_TYPE', 'Typ súboru:');
	define('LBL_FILE_WRITABLE', 'Prepisovateľné?');
	define('LBL_FILE_READABLE', 'Čitateľné?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Informácie o zložke');
	define('LBL_FOLDER_PATH', 'Zložka:');
	define('LBL_CURRENT_FOLDER_PATH', 'Momentálna cesta k zložke:');
	define('LBL_FOLDER_CREATED', 'Vytvorené:');
	define('LBL_FOLDER_MODIFIED', 'Zmenené:');
	define('LBL_FOLDER_SUDDIR', 'Podzložky:');
	define('LBL_FOLDER_FIELS', 'Súbory:');
	define('LBL_FOLDER_WRITABLE', 'Prepisovateľné?');
	define('LBL_FOLDER_READABLE', 'Čitateľné?');
	define('LBL_FOLDER_ROOT', 'Materská zložka');
		//Preview
	define('LBL_PREVIEW', 'Zobraziť');
	define('LBL_CLICK_PREVIEW', 'Pre zobrazenie kliknite sem.');
	//Buttons
	define('LBL_BTN_SELECT', 'Označiť');
	define('LBL_BTN_CANCEL', 'Zrušiť');
	define('LBL_BTN_UPLOAD', 'Nahrať');
	define('LBL_BTN_CREATE', 'Vytvoriť');
	define('LBL_BTN_CLOSE', 'Zavrieť');
	define('LBL_BTN_NEW_FOLDER', 'Nová zložka');
	define('LBL_BTN_NEW_FILE', 'Nový súbor');
	define('LBL_BTN_EDIT_IMAGE', 'Upraviť');
	define('LBL_BTN_VIEW', 'Vybrať zobrazenie');
	define('LBL_BTN_VIEW_TEXT', 'Text');
	define('LBL_BTN_VIEW_DETAILS', 'Detaily');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Miniatúry');
	define('LBL_BTN_VIEW_OPTIONS', 'Zobraziť v:');
	//pagination
	define('PAGINATION_NEXT', 'Ďalej');
	define('PAGINATION_PREVIOUS', 'Späť');
	define('PAGINATION_LAST', 'Koniec');
	define('PAGINATION_FIRST', 'Začiatok');
	define('PAGINATION_ITEMS_PER_PAGE', 'Zobraziť %s položiek na stránku');
	define('PAGINATION_GO_PARENT', 'Choď do vyššej zložky');
	//System
	define('SYS_DISABLED', 'Oprávnenie odmietnuté: Systém je vypnutý.');
	
	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Neboli označené žiadne súbory na vystrihnutie.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Neboli označené žiadne súbory na skopírovanie.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Neboli označené žiadne súbory na vloženie.');
	define('WARNING_CUT_PASTE', 'Ste si istí, že chcete presunúť označené súbory do stávajúcej zložky?');
	define('WARNING_COPY_PASTE', 'Ste si istí, že chcete skopírovaťť označené súbory do stávajúcej zložky?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Nebola vybraná cieľová zložka.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Cieľová zložka nebola nájdená.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Nemáte povolenie presunúť súbory do tejto zložky');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Nepodarilo sa presunúť súbor (%s): Originálna cesta je rovnaká ako cieľová.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Nepodarilo sa presunúť súbor (%s): Originálny súbor neexistuje.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Nepodarilo sa presunúť súbor (%s): Prístup k originálnemu súboru nie je povolený.');
 
	define('ERR_NOT_FILES_PASTED', 'Neboli vložené žiadne súbory.');

	//Search
	define('LBL_SEARCH', 'Hľadať');
	define('LBL_SEARCH_NAME', 'Celý/čiastročný názov súboru:');
	define('LBL_SEARCH_FOLDER', 'Hľadať v:');
	define('LBL_SEARCH_QUICK', 'Rýchle hľadanie');
	define('LBL_SEARCH_MTIME', 'Čas zmeny súboru (Rozsah):');
	define('LBL_SEARCH_SIZE', 'Veľkosť súboru:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Rozšírené možnosti');
	define('LBL_SEARCH_FILE_TYPES', 'Typy súborov:');
	define('SEARCH_TYPE_EXE', 'Aplikácia');
	
	define('SEARCH_TYPE_IMG', 'Obrázok');
	define('SEARCH_TYPE_ARCHIVE', 'Archív');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Video');
	define('SEARCH_TYPE_MOVIE', 'Film');
	define('SEARCH_TYPE_MUSIC', 'Hudba');
	define('SEARCH_TYPE_FLASH', 'Flash');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Dokument');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Text');
	define('SEARCH_TYPE_UNKNOWN', 'Neznámy');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Všetky typy súborov');
	define('LBL_SEARCH_RECURSIVELY', 'Hľadať spätne:');
	define('LBL_RECURSIVELY_YES', 'Áno');
	define('LBL_RECURSIVELY_NO', 'Nie');
	define('BTN_SEARCH', 'Hľadať teraz');
	//thickbox
	define('THICKBOX_NEXT', 'Ďalší&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Predchádzajúci');
	define('THICKBOX_CLOSE', 'Zatvoriť');
	//Calendar
	define('CALENDAR_CLOSE', 'Zatvoriť');
	define('CALENDAR_CLEAR', 'Vyčistiť');
	define('CALENDAR_PREVIOUS', '&lt;Predchádzajúci');
	define('CALENDAR_NEXT', 'Ďalší&gt;');
	define('CALENDAR_CURRENT', 'Dnes');
	define('CALENDAR_MON', 'Pon');
	define('CALENDAR_TUE', 'Uto');
	define('CALENDAR_WED', 'Str');
	define('CALENDAR_THU', 'Štv');
	define('CALENDAR_FRI', 'Pia');
	define('CALENDAR_SAT', 'Sob');
	define('CALENDAR_SUN', 'Ned');
	define('CALENDAR_JAN', 'Jan');
	define('CALENDAR_FEB', 'Feb');
	define('CALENDAR_MAR', 'Mar');
	define('CALENDAR_APR', 'Apr');
	define('CALENDAR_MAY', 'Máj');
	define('CALENDAR_JUN', 'Jún');
	define('CALENDAR_JUL', 'Júl');
	define('CALENDAR_AUG', 'Aug');
	define('CALENDAR_SEP', 'Sep');
	define('CALENDAR_OCT', 'Okt');
	define('CALENDAR_NOV', 'Nov');
	define('CALENDAR_DEC', 'Dec');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Prosím označte súbor.');
	define('ERR_NOT_DOC_SELECTED', 'Neboli označené žiadne dokumenty na vymazanie.');
	define('ERR_DELTED_FAILED', 'Nie je možné vymazať označené dokumenty.');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Cesta k súboru nie je povolená.');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Nedá sa nájsť špecifická zložka: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Prosím použite názov, ktorý obsahuje iba znaky, čísla, medzery a podtržník.');
	define('ERR_RENAME_EXISTS', 'Použite meno, ktoré je v danej zložke unikátne.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Súbor/zložka neexistuje.');
	define('ERR_RENAME_FAILED', 'Nepodarilo sa premenovať, skúste znova.');
	define('ERR_RENAME_EMPTY', 'Pridajte názov prosím.');
	define('ERR_NO_CHANGES_MADE', 'Neboli urobené žiadne zmeny.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Nemáte oprávnenie na zmenu koncovky.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Prosím použite názov, ktorý obsahuje iba znaky, čísla, medzery a podtržník.');
	define('ERR_FOLDER_EXISTS', 'Použite meno, ktoré je v danej zložke unikátne.');
	define('ERR_FOLDER_CREATION_FAILED', 'Nepodarilo sa vytvoriť zložku, skúste znova.');
	define('ERR_FOLDER_NAME_EMPTY', 'Pridajte názov prosím.');
	define('FOLDER_FORM_TITLE', 'Formulár novej zložky');
	define('FOLDER_LBL_TITLE', 'Názov zložky:');
	define('FOLDER_LBL_CREATE', 'Vytvoriť zložku');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Formulár nového súboru');
	define('NEW_FILE_LBL_TITLE', 'Názov súboru:');
	define('NEW_FILE_CREATE', 'Vytvoriť súbor');
		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Prosím použite názov, ktorý obsahuje iba znaky, čísla, medzery a podtržník.');
	define('ERR_FILE_NOT_UPLOADED', 'Neboli označené žiadne zložky na nahratie.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Nemáte oprávnenie na nahrávanie takéhoto typu súboru.');
	define('ERR_FILE_MOVE_FAILED', 'Presun súboru zlyhal.');
	define('ERR_FILE_NOT_AVAILABLE', 'Súbor nie je dostupný.');
	define('ERROR_FILE_TOO_BID', 'Súbor je príliš veľký. (max: %s)');
	define('FILE_FORM_TITLE', 'Formulár nahrávania súborov');
	define('FILE_LABEL_SELECT', 'Označiť súbor');
	define('FILE_LBL_MORE', 'Add File Uploader');
	define('FILE_CANCEL_UPLOAD', 'Zrušiť nahrávanie súboru');
	define('FILE_LBL_UPLOAD', 'Nahrať');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Neboli vybrané súbory na stiahnutie.');
	//Rename
	define('RENAME_FORM_TITLE', 'Formulár premenovania');
	define('RENAME_NEW_NAME', 'Nová názov');
	define('RENAME_LBL_RENAME', 'Premenovať');

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Jeden klik pre vstup do zložky...');
	define('TIP_DOC_RENAME', 'Dvojklik pre upravovanie...');
	define('TIP_FOLDER_GO_UP', 'Jeden klik pre vstup do vrchnej zložky...');
	define('TIP_SELECT_ALL', 'Označiť všetko');
	define('TIP_UNSELECT_ALL', 'Odznačiť všetko');
	//WARNING
	define('WARNING_DELETE', 'Naozaj chcete vymyzať označené dokumenty?');
	define('WARNING_IMAGE_EDIT', 'Označte obrázok na upravovanie.');
	define('WARNING_NOT_FILE_EDIT', 'Označte súbor na upravovanie.');
	define('WARING_WINDOW_CLOSE', 'Naozaj chcete zatvoriť okno?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Náhľad nie je k dispozícii.');
	define('PREVIEW_OPEN_FAILED', 'Súbor sa nedá otvoriť.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Obázok sa nedá nahrať');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager - Formulár prihlásenia');
	define('LOGIN_FORM_TITLE', 'Formulár prihlásenia');
	define('LOGIN_USERNAME', 'Meno:');
	define('LOGIN_PASSWORD', 'Heslo:');
	define('LOGIN_FAILED', 'Nesprávne meno/heslo.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', 'Neurobili ste žiadne zmeny v obrázkoch.');
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Obrázok neexistuje');
		define('IMG_WARNING_LOST_CHANAGES', 'Všetky neuložené zmeny budú stratené, chcete naozaj pokračovať?');
		define('IMG_WARNING_REST', 'Všetky neuložené zmeny budú stratené, chcete naozaj obnoviť?');
		define('IMG_WARNING_EMPTY_RESET', 'Zatiaľ neboli urobené žiadne zmeny v obrázkoch');
		define('IMG_WARING_WIN_CLOSE', 'Naozaj chcete zatvoriť okno?');
		define('IMG_WARNING_UNDO', 'Naozaj chcete vrátiť obrázok do pôvodného stavu?');
		define('IMG_WARING_FLIP_H', 'Naozaj chcete prevrátiť obrázok horizontálne?');
		define('IMG_WARING_FLIP_V', 'Naozaj chcete prevrátiť obrázok vertikálne');
		define('IMG_INFO', 'Image Information');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Zmeniť veľkosť:');
			define('IMG_MODE_CROP', 'Orezať:');
			define('IMG_MODE_ROTATE', 'Natočiť:');
			define('IMG_MODE_FLIP', 'Prevrátiť:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&stupňov;CCW');
			define('IMG_BTN_ROTATE_RIGHT', '90&stupňov;CW');
			define('IMG_BTN_FLIP_H', 'Prevrátiť horizontálne');
			define('IMG_BTN_FLIP_V', 'Prevrátiť vertikálne');
			define('IMG_BTN_RESET', 'Obnoviť');
			define('IMG_BTN_UNDO', 'Späť');
			define('IMG_BTN_SAVE', 'Uložiť');
			define('IMG_BTN_CLOSE', 'Zatvoriť');
			define('IMG_BTN_SAVE_AS', 'Uložiť ako');
			define('IMG_BTN_CANCEL', 'Zrušiť');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Obmedziť?');
		//Label
			define('IMG_LBL_WIDTH', 'Šírka:');
			define('IMG_LBL_HEIGHT', 'Výška:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Pomer:');
			define('IMG_LBL_ANGLE', 'Úhol:');
			define('IMG_LBL_NEW_NAME', 'Nový názov:');
			define('IMG_LBL_SAVE_AS', 'Uložiť ako predlohu');
			define('IMG_LBL_SAVE_TO', 'Uložiť do:');
			define('IMG_LBL_ROOT_FOLDER', 'Materská zložka');
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', 'Prosím nepridávajte prípony obrázkov.');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Prosím použite názov, ktorý obsahuje iba znaky, čísla, medzery a podtržník.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Nebola vybraná cieľová zložka.');	
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Cieľová zložka neexistuje.');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Obrázok s rovnakým menom už exituje.');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Vymazať cestu obrázku.');
		define('IMG_SAVE_NOT_EXISTS', 'Obrázok neexistuje.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Nemáte povolený prístup k tomuto súboru.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Neočakávaný mód operácie obrázku');
		define('IMG_SAVE_RESIZE_FAILED', 'Nepodarilo sa zmeniť veľkosť obrázku.');
		define('IMG_SAVE_CROP_FAILED', 'Nepodarilo sa orezať obrázok.');
		define('IMG_SAVE_FAILED', 'Nepodarilo sa uložiť obrázok.');
		define('IMG_SAVE_BACKUP_FAILED', 'Nepodarilo sa zálohovať originálny obrázok.');
		define('IMG_SAVE_ROTATE_FAILED', 'Nepodarilo sa natočiť obrázok.');
		define('IMG_SAVE_FLIP_FAILED', 'Nepodarilo sa prevrátiť obrázok.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Nepodarilo sa otvoriť obrázok zo session.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Nepodarilo sa otvoriť obrázok');
		
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Pre krok späť nie je záznam.');
		define('IMG_UNDO_COPY_FAILED', 'Nepodarilo sa obnoviť obrázok.');
		define('IMG_UNDO_DEL_FAILED', 'Nepodarilo sa vymazať session obrázok');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Unable to find the dedicated folder which should have been created under session folder');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', 'Unable to open the session counter file.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Unable to write the session counter file.');
	//88888888888   Session   888888888888888888888
	
	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Súbor nenájdený.');
		define('TXT_EXT_NOT_SELECTED', 'Vyberte príponu súboru');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Vyberte cieľovú zložku');
		define('TXT_UNKNOWN_REQUEST', 'Neznámy dopyt.');
		define('TXT_DISALLOWED_EXT', 'Nemáte povolenie na úpravu/pridanie takéhoto typu súboru.');
		define('TXT_FILE_EXIST', 'Taký súbor už existuje.');
		define('TXT_FILE_NOT_EXIST', 'Nenájdené.');
		define('TXT_CREATE_FAILED', 'Nepodarilo sa vytvoriť nový súbor.');
		define('TXT_CONTENT_WRITE_FAILED', 'Nepodarilo sa zapísať obsah do súboru.');
		define('TXT_FILE_OPEN_FAILED', 'Nepodarilo sa otvoriť súbor.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Nepodarilo sa aktualizovať obsah do súboru.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Prosím použite názov, ktorý obsahuje iba znaky, čísla, medzery a podtržník.');
	//88888888888   Above for Text Editor   888888888888888888888
	
	
?>