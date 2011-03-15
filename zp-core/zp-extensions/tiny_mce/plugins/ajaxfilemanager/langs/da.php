<?
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
	
	
	
	
	define('MENU_SELECT', 'Vælg');
	define('MENU_DOWNLOAD', 'Download');
	define('MENU_PREVIEW', 'Preview');
	define('MENU_RENAME', 'Omdøb');
	define('MENU_EDIT', 'Edit');
	define('MENU_CUT', 'Klip');
	define('MENU_COPY', 'Kopier');
	define('MENU_DELETE', 'Slet');
	define('MENU_PLAY', 'Play');
	define('MENU_PASTE', 'Sæt ind');	

           //Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Opdater');
		define('LBL_ACTION_DELETE', 'Slet');
		define('LBL_ACTION_CUT', 'Klip');
		define('LBL_ACTION_COPY', 'Kopier');
		define('LBL_ACTION_PASTE', 'Paste');
		define('LBL_ACTION_CLOSE', 'Luk');
		define('LBL_ACTION_SELECT_ALL', 'Vælg Alle');

		//File Listing
	define('LBL_NAME', 'Navn');
	define('LBL_SIZE', 'Størrelse');
	define('LBL_MODIFIED', 'Ændret');
		//File Information
	define('LBL_FILE_INFO', 'Fil information:');
	define('LBL_FILE_NAME', 'Navn:');	
	define('LBL_FILE_CREATED', 'Oprettet:');
	define("LBL_FILE_MODIFIED", 'Ændret:');
	define("LBL_FILE_SIZE", 'Fil størrelse:');
	define('LBL_FILE_TYPE', 'Fil type:');
	define("LBL_FILE_WRITABLE", 'Skrivbar?');
	define("LBL_FILE_READABLE", 'Læsbar?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Mappe information');
	define("LBL_FOLDER_PATH", 'Sti:');
      define('LBL_CURRENT_FOLDER_PATH', 'Nuværende Mappe Sti:');
	define("LBL_FOLDER_CREATED", 'Oprettet:');
	define("LBL_FOLDER_MODIFIED", 'Ændret:');
	define('LBL_FOLDER_SUDDIR', 'Undermapper:');
	define("LBL_FOLDER_FIELS", 'Filer:');
	define("LBL_FOLDER_WRITABLE", 'Skrivbar?');
	define("LBL_FOLDER_READABLE", 'Læsbar?');
      define('LBL_FOLDER_ROOT', 'Rod Mappe');
		//Preview
	define("LBL_PREVIEW", 'Smugkig');
	//Buttons
	define('LBL_BTN_SELECT', 'Vælg');
	define('LBL_BTN_CANCEL', 'Fortryd');
	define("LBL_BTN_UPLOAD", 'Upload');
	define('LBL_BTN_CREATE', 'Opret');
	define('LBL_BTN_CLOSE', 'Luk');
	define("LBL_BTN_NEW_FOLDER", 'Ny mappe');
	define('LBL_BTN_EDIT_IMAGE', 'Rediger');
	define('LBL_BTN_VIEW', 'Vælg Se');
	define('LBL_BTN_VIEW_TEXT', 'Tekst');
	define('LBL_BTN_VIEW_DETAILS', 'Detaljer');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Thumbnails');
	define('LBL_BTN_VIEW_OPTIONS', 'Se I:');

//pagination
	define('PAGINATION_NEXT', 'Næste');
	define('PAGINATION_PREVIOUS', 'Forrige');
	define('PAGINATION_LAST', 'Sidste');
	define('PAGINATION_FIRST', 'Første');
	define('PAGINATION_ITEMS_PER_PAGE', 'Viser %s punkter pr. side');
	define('PAGINATION_GO_PARENT', 'To Parent Folder');
	//System
	define('SYS_DISABLED', 'Permission denied: Systemet er disabled.');

//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Ingen dokument(er) at klippe.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Ingen dokument(er) at kopiere.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Ingen dokument(er) at sætte ind.');
	define('WARNING_CUT_PASTE', 'Vil du flytte de valgte dokumenter til denne mappe?');
	define('WARNING_COPY_PASTE', 'Vil du kopiere de valgte dokumenter til denne mappe?');
	
      //Search
	define('LBL_SEARCH', 'Søg');
	define('LBL_SEARCH_NAME', 'Fuld/Delt Fil Navn:');
	define('LBL_SEARCH_FOLDER', 'Se I:');
	define('LBL_SEARCH_QUICK', 'Quik Søgning');
	define('LBL_SEARCH_MTIME', 'Fil Ændret Tid(Range):');
	define('LBL_SEARCH_SIZE', 'Fil St�rrelse:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Avanceret muligheder');
	define('LBL_SEARCH_FILE_TYPES', 'Fil Typer:');
	define('SEARCH_TYPE_EXE', 'Application');
	
	define('SEARCH_TYPE_IMG', 'Billede');
	define('SEARCH_TYPE_ARCHIVE', 'Arkiv');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Video');
	define('SEARCH_TYPE_MOVIE', 'Film');
	define('SEARCH_TYPE_MUSIC', 'Musik');
	define('SEARCH_TYPE_FLASH', 'Flash');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Dokument');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Tekst');
	define('SEARCH_TYPE_UNKNOWN', 'Ukendt');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Alle Fil Typer');
	define('LBL_SEARCH_RECURSIVELY', 'Søg Recursively:');
	define('LBL_RECURSIVELY_YES', 'Ja');
	define('LBL_RECURSIVELY_NO', 'Nej');
	define('BTN_SEARCH', 'Søg Nu');
	//thickbox
	define('THICKBOX_NEXT', 'Next&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Prev');
	define('THICKBOX_CLOSE', 'Luk');
	//Calendar
	define('CALENDAR_CLOSE', 'Luk');
	define('CALENDAR_CLEAR', 'Clear');
	define('CALENDAR_PREVIOUS', '&lt;Prev');
	define('CALENDAR_NEXT', 'Next&gt;');
	define('CALENDAR_CURRENT', 'Idag');
	define('CALENDAR_MON', 'Man');
	define('CALENDAR_TUE', 'Tir');
	define('CALENDAR_WED', 'Ons');
	define('CALENDAR_THU', 'Tor');
	define('CALENDAR_FRI', 'Fre');
	define('CALENDAR_SAT', 'Lør');
	define('CALENDAR_SUN', 'Søn');
	define('CALENDAR_JAN', 'Jan');
	define('CALENDAR_FEB', 'Feb');
	define('CALENDAR_MAR', 'Mar');
	define('CALENDAR_APR', 'Apr');
	define('CALENDAR_MAY', 'Maj');
	define('CALENDAR_JUN', 'Jun');
	define('CALENDAR_JUL', 'Jul');
	define('CALENDAR_AUG', 'Aug');
	define('CALENDAR_SEP', 'Sep');
	define('CALENDAR_OCT', 'Okt');
	define('CALENDAR_NOV', 'Nov');
	define('CALENDAR_DEC', 'Dec');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Vælg venligst en fil.');
	define('ERR_NOT_DOC_SELECTED', 'Ingen dokument(er) at slette.');
	define('ERR_DELTED_FAILED', 'Kan ikke slette de valgte dokument(er).');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Stien er ikke tilladt.');
		//class manager
	define("ERR_FOLDER_NOT_FOUND", 'Kan ikke finde den valgte mappe: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Kun bogstaver, tal, mellemrum, bindestreg og understregning kan bruges.');
	define('ERR_RENAME_EXISTS', 'Brug et unikt navn.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Filen/mappen findes ikke.');
	define('ERR_RENAME_FAILED', 'Kan ikke ændre navnet, prøv igen.');
	define('ERR_RENAME_EMPTY', 'Angiv et navn.');
	define("ERR_NO_CHANGES_MADE", 'Ingen ændringer.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Denne fil extension er ikke tilladt.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Kun bogstaver, tal, mellemrum, bindestreg og understregning kan bruges.');
	define('ERR_FOLDER_EXISTS', 'Brug et unikt navn.');
	define('ERR_FOLDER_CREATION_FAILED', 'Kan ikke oprette mappen, prøv igen.');
	define('ERR_FOLDER_NAME_EMPTY', 'Angiv et navn.');
	define('FOLDER_FORM_TITLE', 'Ny Mappe Form');
	define('FOLDER_LBL_TITLE', 'Mappe Titel:');
	define('FOLDER_LBL_CREATE', 'Opret Mappe');

		//file upload
	define("ERR_FILE_NAME_FORMAT", 'Kun bogstaver, tal, mellemrum, bindestreg og understregning kan bruges.');
	define('ERR_FILE_NOT_UPLOADED', 'Vælg en fil til upload.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Denne filtype kan ikke uploades.');
	define('ERR_FILE_MOVE_FAILED', 'Kunne ikke flytte filen.');
	define('ERR_FILE_NOT_AVAILABLE', 'Filen findes ikke.');
	define('ERROR_FILE_TOO_BID', 'Filen er for stor. (max: %s)');
	

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Enkelt klik for at vise denne mappe...');
	define("TIP_DOC_RENAME", 'Dobbelt klik for at ændre...');
	define('TIP_FOLDER_GO_UP', 'Enket klik for at gå en mappe op...');
	define("TIP_SELECT_ALL", 'Vælg alt');
	define("TIP_UNSELECT_ALL", 'Fravælg alt');
	//WARNING
	define('WARNING_DELETE', 'Vil du slette de valgte filer.');
	define('WARNING_IMAGE_EDIT', 'Vælg et billede at redigere.');
	define('WARING_WINDOW_CLOSE', 'Vil du lukke vinduet?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Smugkig findes ikke.');
	define('PREVIEW_OPEN_FAILED', 'Kan ikke åbne filen.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Kan ikke uploade billedet');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager Login');
	define('LOGIN_FORM_TITLE', 'Login');
	define('LOGIN_USERNAME', 'Brugernavn:');
	define('LOGIN_PASSWORD', 'Password:');
	define('LOGIN_FAILED', 'Forkert brugernavn/password.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', "Du har ikke ændret billederne.");
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Billedet findes ikke');
		define('IMG_WARNING_LOST_CHANAGES', 'Alle ændringer af billedet vil gå tabt, vil du fortsætte?');
		define('IMG_WARNING_REST', 'Alle ændringer af billedet vil gå tabt, vil du fortsætte?');
		define('IMG_WARNING_EMPTY_RESET', 'Ingen ændringer foretaget endnu');
		define('IMG_WARING_WIN_CLOSE', 'Vil du lukke vinduet?');
		define('IMG_WARNING_UNDO', 'Vil du vende tilbage til det originale billede?');
		define('IMG_WARING_FLIP_H', 'Vil du spejlvende horisontalt?');
		define('IMG_WARING_FLIP_V', 'Vil du spejlvende vertikalt');
		define('IMG_INFO', 'Billedinformation');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Skaler:');
			define('IMG_MODE_CROP', 'Beskær:');
			define('IMG_MODE_ROTATE', 'Roter:');
			define('IMG_MODE_FLIP', 'Spejlvend:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg; mod uret');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg; med uret');
			define('IMG_BTN_FLIP_H', 'Spejlvend horisontalt');
			define('IMG_BTN_FLIP_V', 'Spejlvend vertikalt');
			define('IMG_BTN_RESET', 'Vend tilbage');
			define('IMG_BTN_UNDO', 'Fortryd');
			define('IMG_BTN_SAVE', 'Gem');
			define('IMG_BTN_CLOSE', 'Luk');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Proportionalt?');
		//Label
			define('IMG_LBL_WIDTH', 'Bredde:');
			define('IMG_LBL_HEIGHT', 'Højde:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Forhold:');
			define('IMG_LBL_ANGLE', 'Vinkel:');
                  define('IMG_LBL_NEW_NAME', 'Nyt Navn:');
			define('IMG_LBL_SAVE_AS', 'Gem Som Form');
			define('IMG_LBL_SAVE_TO', 'Gem Til:');
			define('IMG_LBL_ROOT_FOLDER', 'Rod Mappe');

		//Editor

			
		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Tom billed sti.');
		define('IMG_SAVE_NOT_EXISTS', 'Billedet findes ikke.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Du har ikke adgang til denne fil.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Uventet billed format');
		define('IMG_SAVE_RESIZE_FAILED', 'Kunne ikke skalere billedet.');
		define('IMG_SAVE_CROP_FAILED', 'Kunne ikke beskære billedet.');
		define('IMG_SAVE_FAILED', 'Kunne ikke gemme billedet.');
		define('IMG_SAVE_BACKUP_FAILED', 'Kunne ikke gemme original billedet.');
		define('IMG_SAVE_ROTATE_FAILED', 'Kunne ikke rotere billedet.');
		define('IMG_SAVE_FLIP_FAILED', 'Kunne ikke spejlvende billedet.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Kunne ikke åbne billedet fra sessionen.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Kunne ikke åbne billedet');
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Ingen ændringer at fortryde.');
		define('IMG_UNDO_COPY_FAILED', 'Kunne ikke vende tilbage til original billedet.');
		define('IMG_UNDO_DEL_FAILED', 'Kunne ikke slette billedet fra sessionen');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define("SESSION_PERSONAL_DIR_NOT_FOUND", 'Kan ikke finde den temporære mappe under sessionen');
		define("SESSION_COUNTER_FILE_CREATE_FAILED", 'Kunne ikke åbne sessions filen.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Kunne ikke skrive til sessions filen.');
	//88888888888   Session   888888888888888888888
	
	
?>