<?php
	/**
	 * language pack german
	 * @author Malte Mueller
	 * @link
	 * @since 14/August/2008
	 *
	 */
	define('DATE_TIME_FORMAT', 'd/M/Y H:i:s');
	//Common
	//Menu
	
	
	
	
	define('MENU_SELECT', 'Ausw&auml;hlen');
	define('MENU_DOWNLOAD', 'Download');
	define('MENU_PREVIEW', 'Vorschau');
	define('MENU_RENAME', 'Umbenennen');
	define('MENU_EDIT', 'Bearbeiten');
	define('MENU_CUT', 'Ausschneiden');
	define('MENU_COPY', 'Kopieren');
	define('MENU_DELETE', 'L&ouml;schen');
	define('MENU_PLAY', 'Abspielen');
	define('MENU_PASTE', 'Einf&uuml;gen');
	
	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Aktualisieren');
		define('LBL_ACTION_DELETE', 'L&ouml;schen');
		define('LBL_ACTION_CUT', 'Ausschneiden');
		define('LBL_ACTION_COPY', 'Kopieren');
		define('LBL_ACTION_PASTE', 'Einf&uuml;gen');
		define('LBL_ACTION_CLOSE', 'Schliessen');
		define('LBL_ACTION_SELECT_ALL', 'Alle ausw&auml;hlen');
		//File Listing
	define('LBL_NAME', 'Name');
	define('LBL_SIZE', 'Gr&ouml;&szlig;e');
	define('LBL_MODIFIED', 'Ver&auml;ndert am');
		//File Information
	define('LBL_FILE_INFO', 'Datei-Information:');
	define('LBL_FILE_NAME', 'Name:');	
	define('LBL_FILE_CREATED', 'Erstellt:');
	define('LBL_FILE_MODIFIED', 'Ver&auml;ndert:');
	define('LBL_FILE_SIZE', 'Dateigr&ouml;&szlig;e:');
	define('LBL_FILE_TYPE', 'Dateityp:');
	define('LBL_FILE_WRITABLE', 'Beschreibbar?');
	define('LBL_FILE_READABLE', 'Lesbar?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Ordnerinformation');
	define('LBL_FOLDER_PATH', 'Ordner:');
	define('LBL_CURRENT_FOLDER_PATH', 'Aktueller Ordnerpfad:');
	define('LBL_FOLDER_CREATED', 'Erstellt:');
	define('LBL_FOLDER_MODIFIED', 'Ver&auml;ndert:');
	define('LBL_FOLDER_SUDDIR', 'Unterordner:');
	define('LBL_FOLDER_FIELS', 'Dateien:');
	define('LBL_FOLDER_WRITABLE', 'Beschreibbar?');
	define('LBL_FOLDER_READABLE', 'Lesbar?');
	define('LBL_FOLDER_ROOT', 'Hauptverzeichnis');
		//Preview
	define('LBL_PREVIEW', 'Vorschau');
	define('LBL_CLICK_PREVIEW', 'Hier f&uuml;r eine Vorschau klicken.');
	//Buttons
	define('LBL_BTN_SELECT', 'Ausw&auml;hlen');
	define('LBL_BTN_CANCEL', 'Abbrechen');
	define('LBL_BTN_UPLOAD', 'Hochladen');
	define('LBL_BTN_CREATE', 'Erstellen');
	define('LBL_BTN_CLOSE', 'Schliessen');
	define('LBL_BTN_NEW_FOLDER', 'Neuer Ordner');
	define('LBL_BTN_NEW_FILE', 'Neue Datei');
	define('LBL_BTN_EDIT_IMAGE', 'Bearbeiten');
	define('LBL_BTN_VIEW', 'Ansicht w&auml;hlen');
	define('LBL_BTN_VIEW_TEXT', 'Text');
	define('LBL_BTN_VIEW_DETAILS', 'Details');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Thumbnails');
	define('LBL_BTN_VIEW_OPTIONS', 'Ansehen in:');
	//pagination
	define('PAGINATION_NEXT', 'Weiter');
	define('PAGINATION_PREVIOUS', 'Zur&uuml;ck');
	define('PAGINATION_LAST', 'Letzte');
	define('PAGINATION_FIRST', 'Erste');
	define('PAGINATION_ITEMS_PER_PAGE', '%s Eintr&auml;ge pro Seite');
	define('PAGINATION_GO_PARENT', 'Zum &uuml;bergeordneten Ordner');
	//System
	define('SYS_DISABLED', 'Zugriff verweigert: Das System ist deaktiviert.');
	
	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Kein(e) Dokument(e) zum Ausschneiden ausgew&auml;hlt.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Kein(e) Dokument(e) zum Kopieren ausgew&auml;hlt.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Kein(e) Dokument(e) zum Einf&uuml;gen ausgew&auml;hlt.');
	define('WARNING_CUT_PASTE', 'Sind Sie sicher, dass Sie die ausgew&auml;hlten Dokumente in den derzeitigen Ordner verschieben m&ouml;chten?');
	define('WARNING_COPY_PASTE', 'Sind Sie sicher, dass Sie die ausgew&auml;hlten Dokumente in den derzeitigen Ordner kopieren m&ouml;chten?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Kein Zielordner ausgew&auml;hlt.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Zielordner nicht gefunden.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Sie sind nicht berechtig, die Dateien in diesen Ordner zu verschieben');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Verschieben der Datei (%s) gescheitert: Originalpfad ist der selbe wie der Zielpfad.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Verschieben der Datei (%s) gescheitert: Originaldatei existiert nicht.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Verschieben der Datei (%s) gescheitert: Originaldateizugriff verweigert.');
 
	define('ERR_NOT_FILES_PASTED', 'Kein(e) Datei(en) wurden eingef&uuml;gt.');

	//Search
	define('LBL_SEARCH', 'Suche');
	define('LBL_SEARCH_NAME', 'Voll-/Teildateiname:');
	define('LBL_SEARCH_FOLDER', 'Suchen in:');
	define('LBL_SEARCH_QUICK', 'Schnellsuche');
	define('LBL_SEARCH_MTIME', 'Datein&auml;nderungszeit (Bereich):');
	define('LBL_SEARCH_SIZE', 'Dateigr&ouml;&szlig;e:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Erweiterte Einstellungen');
	define('LBL_SEARCH_FILE_TYPES', 'Dateitypen:');
	define('SEARCH_TYPE_EXE', 'Programm');
	
	define('SEARCH_TYPE_IMG', 'Bild');
	define('SEARCH_TYPE_ARCHIVE', 'Archiv');
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
	define('SEARCH_TYPE_TEXT', 'Text');
	define('SEARCH_TYPE_UNKNOWN', 'Unbekannt');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Alle Dateitypen');
	define('LBL_SEARCH_RECURSIVELY', 'Rekursive Suche:');
	define('LBL_RECURSIVELY_YES', 'Ja');
	define('LBL_RECURSIVELY_NO', 'Nein');
	define('BTN_SEARCH', 'Sofort suchen');
	//thickbox
	define('THICKBOX_NEXT', 'Vor&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Zur&uuml;ck');
	define('THICKBOX_CLOSE', 'Schliessen');
	//Calendar
	define('CALENDAR_CLOSE', 'Schliessen');
	define('CALENDAR_CLEAR', 'Zur&uuml;cksetzen');
	define('CALENDAR_PREVIOUS', '&lt;Vor');
	define('CALENDAR_NEXT', 'Zur&uuml;ck&gt;');
	define('CALENDAR_CURRENT', 'Heute');
	define('CALENDAR_MON', 'Mo');
	define('CALENDAR_TUE', 'Di');
	define('CALENDAR_WED', 'Mi');
	define('CALENDAR_THU', 'Do');
	define('CALENDAR_FRI', 'Fr');
	define('CALENDAR_SAT', 'Sa');
	define('CALENDAR_SUN', 'So');
	define('CALENDAR_JAN', 'Jan');
	define('CALENDAR_FEB', 'Feb');
	define('CALENDAR_MAR', 'M&auml;rz');
	define('CALENDAR_APR', 'April');
	define('CALENDAR_MAY', 'Mai');
	define('CALENDAR_JUN', 'Juni');
	define('CALENDAR_JUL', 'Juli');
	define('CALENDAR_AUG', 'Aug');
	define('CALENDAR_SEP', 'Sep');
	define('CALENDAR_OCT', 'Okt');
	define('CALENDAR_NOV', 'Nov');
	define('CALENDAR_DEC', 'Dez');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Bitte w&auml;hlen Sie eine Datei aus.');
	define('ERR_NOT_DOC_SELECTED', 'Kein(e) Dokument() zum L&ouml;schen ausgew&auml;hlt.');
	define('ERR_DELTED_FAILED', 'Ausgew&auml;hlte Dokumente k&ouml;nnen nicht gel&ouml;scht werden.');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Der Ordnerpfad ist nicht erlaubt.');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Der Ordner konnten nicht gefunden werden: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Bitte vergeben Sie einen Namen, der nur aus Buchstaben, Zahlen, Leerzeichen, Bindestrich und Unterstrich besteht.');
	define('ERR_RENAME_EXISTS', 'Bitte vergeben Sie einen Namen, der in diesem Ordner einzigartig ist.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Datei oder Ordner existieren nicht.');
	define('ERR_RENAME_FAILED', 'Umbenennung fehlgeschlagen. Bitte versuchen Sie es erneut.');
	define('ERR_RENAME_EMPTY', 'Bitte vergeben Sie einen Namen.');
	define('ERR_NO_CHANGES_MADE', 'Es wurden keine &Auml;nderungen vorgenommen.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Sie sind nicht berechtigt, dieser Datei diese Endung zuzuweisen.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Bitte vergeben Sie einen Namen, der nur aus Buchstaben, Zahlen, Leerzeichen, Bindestrich und Unterstrich besteht.');
	define('ERR_FOLDER_EXISTS', 'Bitte vergeben Sie einen Namen, der in diesem Ordner einzigartig ist.');
	define('ERR_FOLDER_CREATION_FAILED', 'Ordnererstellung fehlgeschlagen. Bitte versuchen Sie es erneut.');
	define('ERR_FOLDER_NAME_EMPTY', 'Bitte vergeben Sie einen Namen.');
	define('FOLDER_FORM_TITLE', 'Neuer-Ordner-Formular ');
	define('FOLDER_LBL_TITLE', 'Ordnertitel:');
	define('FOLDER_LBL_CREATE', 'Ordner erstellen');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Neue Datei-Formular ');
	define('NEW_FILE_LBL_TITLE', 'Dateiname:');
	define('NEW_FILE_CREATE', 'Datei erstellen');
		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Bitte vergeben Sie einen Namen, der nur aus Buchstaben, Zahlen, Leerzeichen, Bindestrich und Unterstrich besteht.');
	define('ERR_FILE_NOT_UPLOADED', 'Es wurde keine Datei zum Hochladen ausgew&auml;hlt.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Sie haben keine Berechtigung, diesen Dateityp hochzuladen.');
	define('ERR_FILE_MOVE_FAILED', 'Verschieben der Datei fehlgeschlagen.');
	define('ERR_FILE_NOT_AVAILABLE', 'Die Datei ist nicht verf&uuml;gbar.');
	define('ERROR_FILE_TOO_BID', 'Datei zu gro&szlig;. (max: %s)');
	define('FILE_FORM_TITLE', 'Dateihochladen-Formular');
	define('FILE_LABEL_SELECT', 'Datei ausw&auml;hlen');
	define('FILE_LBL_MORE', 'Dateihochladformular hinzuf&uuml;gen');
	define('FILE_CANCEL_UPLOAD', 'Dateihochladen abbrechen');
	define('FILE_LBL_UPLOAD', 'Hochladen');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Keine Dateien zum Runterladen ausgew&auml;hlt.');
	//Rename
	define('RENAME_FORM_TITLE', 'Umbenennen-Formular ');
	define('RENAME_NEW_NAME', 'Neuer Name');
	define('RENAME_LBL_RENAME', 'Umbenennen');

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Klicken, um zu diesem Ordner zu gelangen...');
	define('TIP_DOC_RENAME', 'Doppelklicken zum Bearbeiten...');
	define('TIP_FOLDER_GO_UP', 'Klicken, um zum &uuml;bergeordneten Ordner zu kommen...');
	define('TIP_SELECT_ALL', 'Alle ausw&auml;hlen');
	define('TIP_UNSELECT_ALL', 'Auswahl aufheben');
	//WARNING
	define('WARNING_DELETE', 'Sind Sie sicher, dass Sie die ausgew&auml;hlten Dateien l&ouml;schen m&ouml;chten?');
	define('WARNING_IMAGE_EDIT', 'Bitte w&auml;hlen Sie ein Bild zum Bearbeiten aus.');
	define('WARNING_NOT_FILE_EDIT', 'Bitte w&auml;hlen Sie eine Datei zum Bearbeiten aus.');
	define('WARING_WINDOW_CLOSE', 'Sind Sie sicher, dass Sie das Fenster schlie&szlig;en m&ouml;chten?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Kein Vorschau verf&uuml;gbar.');
	define('PREVIEW_OPEN_FAILED', 'Datei konnte nicht ge&ouml;ffnet werden.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Bild konnte nicht geladen werden.');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager Einloggen');
	define('LOGIN_FORM_TITLE', 'Einlogformular');
	define('LOGIN_USERNAME', 'Benutzername:');
	define('LOGIN_PASSWORD', 'Passwort:');
	define('LOGIN_FAILED', 'Passwort/Benutzername ung&uuml;ltig.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', 'Sie haben an dem Bild keine &Auml;nderungen vorgenommen.');
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Bild existiert nicht');
		define('IMG_WARNING_LOST_CHANAGES', 'Alle ungesicherten &Auml;nderungen werden verloren gehen. Sind Sie sicher, dass Sie fortfahren m&ouml;chten?');
		define('IMG_WARNING_REST', 'Alle ungesicherten &Auml;nderungen werden verloren gehen. Sind Sie sicher, dass Sie zur&uuml;cksetzen m&ouml;chten?');
		define('IMG_WARNING_EMPTY_RESET', 'Bisher wurden keine Bild&auml;nderungen vorgenommen.');
		define('IMG_WARING_WIN_CLOSE', 'Sind Sie sicher, dass Sie das Fenster schlie&szlig;en m&ouml;chten?');
		define('IMG_WARNING_UNDO', 'Sind Sie sicher, dass Sie das Bild auf den vorherigen Zustand zur&uuml;cksetzen m&ouml;chten?');
		define('IMG_WARING_FLIP_H', 'Sind Sie sicher, dass Sie das Bild horinzontal spiegeln m&ouml;chten?');
		define('IMG_WARING_FLIP_V', 'Sind Sie sicher, dass Sie das Bild vertikal spiegeln m&ouml;chten?');
		define('IMG_INFO', 'Bildinformation');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Gr&ouml;&szlig;e &auml;ndern:');
			define('IMG_MODE_CROP', 'Beschneiden:');
			define('IMG_MODE_ROTATE', 'Drehen:');
			define('IMG_MODE_FLIP', 'Spiegeln:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg;gUZS');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg;UZS');
			define('IMG_BTN_FLIP_H', 'Horizontal spiegeln');
			define('IMG_BTN_FLIP_V', 'Vertikal spiegeln');
			define('IMG_BTN_RESET', 'Zur&uuml;cksetzen');
			define('IMG_BTN_UNDO', 'Zur&uuml;cknehmen');
			define('IMG_BTN_SAVE', 'Sichern');
			define('IMG_BTN_CLOSE', 'Schliessen');
			define('IMG_BTN_SAVE_AS', 'Sichern als');
			define('IMG_BTN_CANCEL', 'Abbrechen');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Proportionen erhalten?');
		//Label
			define('IMG_LBL_WIDTH', 'Breite:');
			define('IMG_LBL_HEIGHT', 'H&ouml;he:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Abmessungen:');
			define('IMG_LBL_ANGLE', 'Winkel:');
			define('IMG_LBL_NEW_NAME', 'Neuer Name:');
			define('IMG_LBL_SAVE_AS', 'Sichern-als-Formular');
			define('IMG_LBL_SAVE_TO', 'Sichern nach:');
			define('IMG_LBL_ROOT_FOLDER', 'Hauptverzeichnis');
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', 'Bitte die Dateinendung nicht &auml;ndern.');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Bitte vergeben Sie einen Namen, der nur aus Buchstaben, Zahlen, Leerzeichen, Bindestrich und Unterstrich besteht.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Kein Zielordner ausgew&auml;hlt.');	
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Der Zielordner existiert nicht.');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Es existiert bereits ein Bild mit diesem Namen.');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Leerer Bildpfad.');
		define('IMG_SAVE_NOT_EXISTS', 'Bild existiert nicht.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Sie haben keine Zugriffsrechte auf diese Datei.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Unerwarteter Bildoperationsmodus.');
		define('IMG_SAVE_RESIZE_FAILED', 'Gr&ouml;&szlig;en&auml;nderung fehlgeschlagen.');
		define('IMG_SAVE_CROP_FAILED', 'Beschneiden fehlgeschlagen.');
		define('IMG_SAVE_FAILED', 'Sichern fehlgeschlagen.');
		define('IMG_SAVE_BACKUP_FAILED', 'Sicherungskopie vom Original konnte nicht erstellt werden.');
		define('IMG_SAVE_ROTATE_FAILED', 'Drehen fehlgeschlagen.');
		define('IMG_SAVE_FLIP_FAILED', 'Spiegel fehlgeschlagen.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Bild konnten von der Session nicht ge&ouml;ffnet werden.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Bild&ouml;ffnen fehlgeschlagen.');
		
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Kein Verlauf f&uuml;r das Zur&uuml;cknehmen vorhanden.');
		define('IMG_UNDO_COPY_FAILED', 'Bild konnte nicht wiederhergestellt werden.');
		define('IMG_UNDO_DEL_FAILED', 'Das Sessionbild konnte nicht gel&ouml;scht werden.');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Der zugewiesene Ordner, der innerhalb des Sessionordner erstellt werden sollte, konnte nicht gefunden werden.');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', '&Ouml;ffnung der Sessionz&auml;hldatei fehlgeschlagen.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Die Sessionz&auml;hldatei konnte nicht geschrieben werden.');
	//88888888888   Session   888888888888888888888
	
	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Datei nicht gefunden.');
		define('TXT_EXT_NOT_SELECTED', 'Bitte w&auml;hlen Sie eine Dateiendung.');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Bitte w&auml;hlen Sie einen Zielordner.');
		define('TXT_UNKNOWN_REQUEST', 'Unbekannter Befehl.');
		define('TXT_DISALLOWED_EXT', 'Sie sind berechtigt, so eine Datei zu bearbeiten oder hinzuzuf&uuml;gen.');
		define('TXT_FILE_EXIST', 'So eine Datei existiert bereits.');
		define('TXT_FILE_NOT_EXIST', 'Nicht gefunden.');
		define('TXT_CREATE_FAILED', 'Erstellungen einer neuen Datei fehlgeschlagen.');
		define('TXT_CONTENT_WRITE_FAILED', 'Es konnte kein Inhalt in die Datei geschrieben werden.');
		define('TXT_FILE_OPEN_FAILED', 'Datei konnte nicht ge&ouml;ffnet werden.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Inhalt der Datei konnte nicht aktualisiert werden.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Bitte vergeben Sie einen Namen, der nur aus Buchstaben, Zahlen, Leerzeichen, Bindestrich und Unterstrich besteht.');
	//88888888888   Above for Text Editor   888888888888888888888
	
	
?>