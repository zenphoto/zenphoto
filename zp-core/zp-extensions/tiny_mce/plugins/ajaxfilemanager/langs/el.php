<?php
	/**
	 * language pack
	 * @author Chris 
	 * @link www.mygreekforum.net
	 * @since 22/Jun/2008
	 *
	 */
	define('DATE_TIME_FORMAT', 'd/M/Y H:i:s');
	//Common
	//Menu
	
	
	
	
	define('MENU_SELECT', 'Επιλογή');
	define('MENU_DOWNLOAD', 'Μεταφόρτωση');
	define('MENU_PREVIEW', 'Προεπισκόπηση');
	define('MENU_RENAME', 'Μετονομασία');
	define('MENU_EDIT', 'Σύνταξη');
	define('MENU_CUT', 'Αποκοπή');
	define('MENU_COPY', 'Αντιγραφή');
	define('MENU_DELETE', 'Διαγραφή');
	define('MENU_PLAY', 'Παίξε');
	define('MENU_PASTE', 'Επικόλληση');
	
	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Ανανέωση');
		define('LBL_ACTION_DELETE', 'Διαγραφή');
		define('LBL_ACTION_CUT', 'Αποκοπή');
		define('LBL_ACTION_COPY', 'Αντιγραφή');
		define('LBL_ACTION_PASTE', 'Επικόλληση');
		define('LBL_ACTION_CLOSE', 'Έξοδος');
		define('LBL_ACTION_SELECT_ALL', 'Επιλογή όλων');
		//File Listing
	define('LBL_NAME', 'Όνομα');
	define('LBL_SIZE', 'Μέγεθος');
	define('LBL_MODIFIED', 'Διορθώθηκε από');
		//File Information
	define('LBL_FILE_INFO', 'Πληροφορίες αρχείου:');
	define('LBL_FILE_NAME', 'Όνομα:');	
	define('LBL_FILE_CREATED', 'Δημιουργία:');
	define('LBL_FILE_MODIFIED', 'Διόρθωση:');
	define('LBL_FILE_SIZE', 'Μέγεθος:');
	define('LBL_FILE_TYPE', 'Τύπος:');
	define('LBL_FILE_WRITABLE', 'Εγγράψιμο?');
	define('LBL_FILE_READABLE', 'Αναγνώσιμο?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Πληροφορίες φακέλου');
	define('LBL_FOLDER_PATH', 'Φάκελος:');
	define('LBL_CURRENT_FOLDER_PATH', 'Τρέχουσα διαδρομή φακέλων:');
	define('LBL_FOLDER_CREATED', 'Δημιουργία:');
	define('LBL_FOLDER_MODIFIED', 'Διόρθωση:');
	define('LBL_FOLDER_SUDDIR', 'Υποφάκελοι:');
	define('LBL_FOLDER_FIELS', 'Αρχεία:');
	define('LBL_FOLDER_WRITABLE', 'Εγγράψιμο?');
	define('LBL_FOLDER_READABLE', 'Αναγνώσιμο?');
	define('LBL_FOLDER_ROOT', 'Ρίζα φακέλου');
		//Preview
	define('LBL_PREVIEW', 'Προεπισκόπηση');
	define('LBL_CLICK_PREVIEW', 'Κλίκ εδώ για προεπισκόπηση.');
	//Buttons
	define('LBL_BTN_SELECT', 'Επιλογή');
	define('LBL_BTN_CANCEL', 'Ακύρωση');
	define('LBL_BTN_UPLOAD', 'Ανέβασμα');
	define('LBL_BTN_CREATE', 'Δημιουργία');
	define('LBL_BTN_CLOSE', 'Κλείσιμο');
	define('LBL_BTN_NEW_FOLDER', 'Νέος φάκελος');
	define('LBL_BTN_NEW_FILE', 'Νέο αρχείο');
	define('LBL_BTN_EDIT_IMAGE', 'Εγγραφή');
	define('LBL_BTN_VIEW', 'Επιλογή προβολής');
	define('LBL_BTN_VIEW_TEXT', 'Κείμενο');
	define('LBL_BTN_VIEW_DETAILS', 'Λεπτομέρειες');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Εικονίδια');
	define('LBL_BTN_VIEW_OPTIONS', 'προβολή σε:');
	//pagination
	define('PAGINATION_NEXT', 'Επόμενο');
	define('PAGINATION_PREVIOUS', 'Προηγούμενο');
	define('PAGINATION_LAST', 'Τελευταίο');
	define('PAGINATION_FIRST', 'Πρώτο');
	define('PAGINATION_ITEMS_PER_PAGE', 'Προβολή %s στοιχείων ανά σελίδα');
	define('PAGINATION_GO_PARENT', 'Πήγαινε στον πρώτο φάκελο');
	//System
	define('SYS_DISABLED', 'Απαγορεύεται η πρόσβαση: Το σύστημα είναι απενεργοποιημένο.');
	
	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Δεν έχει επιλεγεί έγγραφο(α) για αποκοπή.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Δεν έχει επιλεγεί έγγραφο(α) για αντιγραφή.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Δεν έχει επιλεγεί έγγραφο(α) για επικόλληση.');
	define('WARNING_CUT_PASTE', 'Είστε βέβαιοι ότι θέλετε να μετακινήσετε τα επιλεγμένα έγγραφα προς τον τρέχοντα φάκελλο?');
	define('WARNING_COPY_PASTE', 'Είστε βέβαιοι ότι θέλετε να αντιγράψετε τα επιλεγμένα έγγραφα προς τον τρέχοντα φάκελλο?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Δεν έχει οριστεί φάκελλος προορισμού.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'ο φάκελος προορισμού δεν βρέθηκε.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Δεν έχετε την άδεια για να μετακινήσετε τα αρχεία προς αυτόν τον φάκελλο');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Αποτυχία μετακίνησης του αρχείου (%s): Η αρχική διαδρομή είναι ίδια με την διαδρομή προορισμού.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Αποτυχία μετακίνησης του αρχείου (%s): Το πρώτυπο αρχείο δεν βρέθηκε.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Αποτυχία μετακίνησης του αρχείου (%s): Το πρώτυπο αρχείο έχει περιορισμό πρόσβασης.');
 
	define('ERR_NOT_FILES_PASTED', 'Κανένα αρχείο(α) δεν έχει επικολληθεί.');

	//Search
	define('LBL_SEARCH', 'Αναζήτηση');
	define('LBL_SEARCH_NAME', 'Ολόκληρο/Μέρος ονόματος αρχείου:');
	define('LBL_SEARCH_FOLDER', 'Κοίταξε σε:');
	define('LBL_SEARCH_QUICK', 'Γρήγορη αναζήτηση');
	define('LBL_SEARCH_MTIME', 'Ημερομηνία αλλαγής αρχείου(Περιοχή):');
	define('LBL_SEARCH_SIZE', 'Μέγεθος αρχείου:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Προηγμένες επιλογές');
	define('LBL_SEARCH_FILE_TYPES', 'Τύποι αρχείου:');
	define('SEARCH_TYPE_EXE', 'Εφαρμογές');
	
	define('SEARCH_TYPE_IMG', 'Εικόνα');
	define('SEARCH_TYPE_ARCHIVE', 'Αρχείο');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Βίντεο');
	define('SEARCH_TYPE_MOVIE', 'Ταινία');
	define('SEARCH_TYPE_MUSIC', 'Μουσική');
	define('SEARCH_TYPE_FLASH', 'Flash');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Έγγραφο');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Κείμενο');
	define('SEARCH_TYPE_UNKNOWN', 'Άγνωστο');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Όλοι οι τύποι αρχείων');
	define('LBL_SEARCH_RECURSIVELY', 'Συνεχή Αναζήτηση:');
	define('LBL_RECURSIVELY_YES', 'Ναί');
	define('LBL_RECURSIVELY_NO', 'Όχι');
	define('BTN_SEARCH', 'Αναζήτηση');
	//thickbox
	define('THICKBOX_NEXT', 'Επόμενο&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Προηγούμενο');
	define('THICKBOX_CLOSE', 'Έξοδος');
	//Calendar
	define('CALENDAR_CLOSE', 'Έξοδος');
	define('CALENDAR_CLEAR', 'Καθαρισμός');
	define('CALENDAR_PREVIOUS', '&lt;Προηγούμενο');
	define('CALENDAR_NEXT', 'Επόμενο&gt;');
	define('CALENDAR_CURRENT', 'Σήμερα');
	define('CALENDAR_MON', 'Δευ');
	define('CALENDAR_TUE', 'Τρί');
	define('CALENDAR_WED', 'Τετ');
	define('CALENDAR_THU', 'Πεμ');
	define('CALENDAR_FRI', 'Παρ');
	define('CALENDAR_SAT', 'Σαβ');
	define('CALENDAR_SUN', 'Κυρ');
	define('CALENDAR_JAN', 'Ιαν');
	define('CALENDAR_FEB', 'Φεβ');
	define('CALENDAR_MAR', 'Μαρ');
	define('CALENDAR_APR', 'Απρ');
	define('CALENDAR_MAY', 'Μάι');
	define('CALENDAR_JUN', 'Ιούν');
	define('CALENDAR_JUL', 'Ιούλ');
	define('CALENDAR_AUG', 'Άυγ');
	define('CALENDAR_SEP', 'Σεπ');
	define('CALENDAR_OCT', 'Οκτ');
	define('CALENDAR_NOV', 'Νοέ');
	define('CALENDAR_DEC', 'Δεκ');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Παρακαλώ επιλέξτε ένα αρχείο.');
	define('ERR_NOT_DOC_SELECTED', 'Δεν έχει επιλεγεί έγγραφο(α) για διαγραφή.');
	define('ERR_DELTED_FAILED', 'Αδύνατη η διαγραφή του εγγράφου(ων).');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Η διαδρομή των φακέλλων δεν επιτρέπεται.');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Αδύνατη η εύρεση του φακέλλου: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Παρακαλώ δώστε του ένα όνομα που θα περιέχει μόνο ψηφία, νούμερα, κενό(διάστημα), την παύλα και την κάτω παύλα.');
	define('ERR_RENAME_EXISTS', 'Παρακαλώ δώστε του ένα όνομα που θα είναι μοναδικό μέσα στον φάκελλο.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Το αρχείο η ο φάκελλος δεν υπάρχουν.');
	define('ERR_RENAME_FAILED', 'Αδύνατη η μετονομασία, προσπαθήστε ξανά.');
	define('ERR_RENAME_EMPTY', 'Παρακαλώ δώστε του όνομα.');
	define('ERR_NO_CHANGES_MADE', 'Δεν έγινε καμμία αλλαγή.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Δεν επιτρέπεστε για να αλλάξετε το αρχείο σε τέτοια επέκταση.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Παρακαλώ δώστε ένα όνομα που θα περιέχει μόνο ψηφία, νούμερα, κενό(διάστημα), την παύλα και την κάτω παύλα.');
	define('ERR_FOLDER_EXISTS', 'Παρακαλώ δώστε ένα όνομα που θα είναι μοναδικό μέσα στον φάκελλο.');
	define('ERR_FOLDER_CREATION_FAILED', 'Αδύνατη η δημιουργία φακέλλου, προσπαθήστε ξανά.');
	define('ERR_FOLDER_NAME_EMPTY', 'Παρακαλώ δώστε του όνομα.');
	define('FOLDER_FORM_TITLE', 'Νέος φακέλλος');
	define('FOLDER_LBL_TITLE', 'Τίτλος φακέλλου:');
	define('FOLDER_LBL_CREATE', 'Δημιουργία');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Νέα μορφή αρχείου');
	define('NEW_FILE_LBL_TITLE', 'Όνομα αρχείου:');
	define('NEW_FILE_CREATE', 'Δημιουργία');
		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Παρακαλώ δώστε ένα όνομα που θα περιέχει μόνο ψηφία, νούμερα, κενό(διάστημα), την παύλα και την κάτω παύλα.');
	define('ERR_FILE_NOT_UPLOADED', 'Κανένα αρχείο δεν έχει επιλεχτεί για φόρτωμα.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Δεν σας έχει επιτραπεί η φόρτωση τέτοιου τύπου αρχείων.');
	define('ERR_FILE_MOVE_FAILED', 'Αποτυχία μετακίνησης αρχείου.');
	define('ERR_FILE_NOT_AVAILABLE', 'Το αρχείο είναι μη διαθέσιμο.');
	define('ERROR_FILE_TOO_BID', 'Αρχείο πάρα πολύ μεγάλο. (max: %s)');
	define('FILE_FORM_TITLE', 'Φόρμα φόρτωσης αρχείου');
	define('FILE_LABEL_SELECT', 'Επιλογή αρχείου');
	define('FILE_LBL_MORE', 'Προσθέστε αρχείο για φόρτωση ');
	define('FILE_CANCEL_UPLOAD', 'Ακύρωση φόρτωσης αρχείου');
	define('FILE_LBL_UPLOAD', 'Φόρτωση');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Δεν επιλέχθει αρχείο για μεταφόρτωση.');
	//Rename
	define('RENAME_FORM_TITLE', 'Μετονομασία φόρμας');
	define('RENAME_NEW_NAME', 'Νέο όνομα');
	define('RENAME_LBL_RENAME', 'Μετονομασία');

	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Μονό κλίκ για να φτάσετε σε αυτόν τον φάκελλο...');
	define('TIP_DOC_RENAME', 'Διπλό κλίκ για σύνταξη...');
	define('TIP_FOLDER_GO_UP', 'Μονό κλίκ για να φτάσετε στόν αρχικό φάκελλο...');
	define('TIP_SELECT_ALL', 'Επιλογή όλων');
	define('TIP_UNSELECT_ALL', 'Αποεπιλογή όλων');
	//WARNING
	define('WARNING_DELETE', 'Είστε βέβαιοι ότι θέλετε να διαγράψετε το επιλεγμένο έγγραφο(α).');
	define('WARNING_IMAGE_EDIT', 'Παρακαλώ επιλέξτε μια εικόνα για επεξεργασία.');
	define('WARNING_NOT_FILE_EDIT', 'Παρακαλώ επιλέξτε ένα αρχείο για επεξεργασία.');
	define('WARING_WINDOW_CLOSE', 'Είστε βέβαιοι ότι θέλετε να κλείσετε το παράθυρο?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Καμία προεπισκόπιση δεν είναι διαθέσιμη.');
	define('PREVIEW_OPEN_FAILED', 'Αδύνατον το άνοιγμα του αρχείου.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Αδύνατη η φόρτωση της εικόνας');

	//Login
	define('LOGIN_PAGE_TITLE', 'Φόρμα σύνδεσης του Ajax File Manager');
	define('LOGIN_FORM_TITLE', 'Φόρμα σύνδεσης');
	define('LOGIN_USERNAME', 'Όνομα χρήστη:');
	define('LOGIN_PASSWORD', 'Κωδικός:');
	define('LOGIN_FAILED', 'Άκυρο όνομα χρήστη/κωδικός πρόσβασης.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', 'Δεν έχετε κάνει οποιεσδήποτε αλλαγές στις εικόνες.');
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Η εικόνα δεν υπάρχει');
		define('IMG_WARNING_LOST_CHANAGES', 'Όλες οι μη σωσμένες αλλαγές που γίνονται στην εικόνα θα χαθούν, είστε βέβαιοι ότι επιθυμείτε να συνεχίσετε?');
		define('IMG_WARNING_REST', 'Όλες οι μη σωσμένες αλλαγές που γίνονται στην εικόνα θα χαθούν, είστε βέβαιοι ότι επιθυμείτε να γίνει αναστοιχειοθέτηση?');
		define('IMG_WARNING_EMPTY_RESET', 'Καμία αλλαγή δεν έχει γίνει στην εικόνα μέχρι τώρα');
		define('IMG_WARING_WIN_CLOSE', 'Είστε βέβαιοι ότι θέλετε να κλείσετε το παράθυρο?');
		define('IMG_WARNING_UNDO', 'Είστε βέβαιοι να αποκαταστήσετε την εικόνα στην προηγούμενη μορφή?');
		define('IMG_WARING_FLIP_H', 'Είστε βέβαιοι για την αναστροφή της εικόνας οριζόντια?');
		define('IMG_WARING_FLIP_V', 'Είστε βέβαιοι για την αναστροφή της εικόνας κάθετα');
		define('IMG_INFO', 'Πληροφορίες εικόνας');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Αλλαγή μεγέθους:');
			define('IMG_MODE_CROP', 'Μερική αποκοπή:');
			define('IMG_MODE_ROTATE', 'Περιστροφή:');
			define('IMG_MODE_FLIP', 'Αναστροφή:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg;CCW');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg;CW');
			define('IMG_BTN_FLIP_H', 'Αναστροφή Οριζόντια');
			define('IMG_BTN_FLIP_V', 'Αναστροφή Κάθετα');
			define('IMG_BTN_RESET', 'Αναστοιχειοθέτηση');
			define('IMG_BTN_UNDO', 'Αναίρεση');
			define('IMG_BTN_SAVE', 'Αποθήκευση');
			define('IMG_BTN_CLOSE', 'Έξοδος');
			define('IMG_BTN_SAVE_AS', 'Αποθήκευση σαν');
			define('IMG_BTN_CANCEL', 'Ακύρωση');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Constraint?');
		//Label
			define('IMG_LBL_WIDTH', 'Πλάτος:');
			define('IMG_LBL_HEIGHT', 'Ύψος:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Ποσοστό:');
			define('IMG_LBL_ANGLE', 'Γωνία:');
			define('IMG_LBL_NEW_NAME', 'Νέο όνομα:');
			define('IMG_LBL_SAVE_AS', 'Αποθήκευση σαν φόρμα');
			define('IMG_LBL_SAVE_TO', 'Αποθήκευση Σε:');
			define('IMG_LBL_ROOT_FOLDER', 'Κύριος φάκελλος');
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', 'Παρακαλώ μην συμπεριλάβετε την επέκταση της εικόνας.');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Παρακαλώ δώστε ένα όνομα που θα περιέχει μόνο ψηφία, νούμερα, κενό(διάστημα), την παύλα και την κάτω παύλα.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Δεν έχει επιλεγεί φάκελλος προορισμού.');	
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Ο φάκελλος προορισμού δεν υπάρχει.');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Υπάρχει μια εικόνα με αυτό το όνομα.');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Κενή πορεία εικόνας.');
		define('IMG_SAVE_NOT_EXISTS', 'Η εικόνα δεν υπάρχει.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Δεν έχετε άδεια πρόσβασης σε αυτό το αρχείο.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Άγνωστος τρόπος λειτουργίας εικόνας');
		define('IMG_SAVE_RESIZE_FAILED', 'Απέτυχε να επαναταξινομήσει την εικόνα.');
		define('IMG_SAVE_CROP_FAILED', 'Απέτυχε η μερική αποκοπή στην εικόνα.');
		define('IMG_SAVE_FAILED', 'Απέτυχε να σώσει την εικόνα.');
		define('IMG_SAVE_BACKUP_FAILED', 'Αποτυχία σε αποθήκευση της πρωτότυπης εικόνας.');
		define('IMG_SAVE_ROTATE_FAILED', 'Αποτυχία περιστροφής εικόνας.');
		define('IMG_SAVE_FLIP_FAILED', 'Αποτυχία αναστροφής εικόνας.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Απέτυχε να ανοίξει την εικόνα από τη σύνοδο.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Απέτυχε να ανοίξει την εικόνα');
		
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Δεν υπάρχει ιστορικό για υπαναχώριση αλλαγών.');
		define('IMG_UNDO_COPY_FAILED', 'Απέτυχε να αποκαταστήσει την εικόνα.');
		define('IMG_UNDO_DEL_FAILED', 'Απέτυχε να διαγράψει την εικόνα συνόδου');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Απέτυχε να βρει τον ορισμένο φάκελλο που πρέπει να έχει δημιουργηθεί κάτω από το φάκελλο συνόδου');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', 'Απέτυχε να ανοίξει το μετρητή αρχείων συνόδου.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Απέτυχε να γράψει στον μετρητή αρχείων συνόδου.');
	//88888888888   Session   888888888888888888888
	
	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Το αρχείο δεν βρέθηκε.');
		define('TXT_EXT_NOT_SELECTED', 'Παρακαλώ επιλέξτε επέκταση αρχείων');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Παρακαλώ επιλέξτε φάκελλο προορισμού');
		define('TXT_UNKNOWN_REQUEST', 'Άγνωστο αίτημα.');
		define('TXT_DISALLOWED_EXT', 'Έχετε την άδεια για να εκδώσετε/προσθέτετε τέτοιο τύπο αρχείου.');
		define('TXT_FILE_EXIST', 'Τέτοιο αρχείο υπάρχει ήδη.');
		define('TXT_FILE_NOT_EXIST', 'Δεν βρέθηκε κάτι τέτοιο.');
		define('TXT_CREATE_FAILED', 'Απέτυχε να δημιουργήσει ένα νέο αρχείο.');
		define('TXT_CONTENT_WRITE_FAILED', 'Απέτυχε να γράψει το περιεχόμενο στο αρχείο.');
		define('TXT_FILE_OPEN_FAILED', 'Απέτυχε να ανοίξει το αρχείο.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Απέτυχε να ενημερώσει το περιεχόμενο στο αρχείο.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Παρακαλώ δώστε ένα όνομα που θα περιέχει μόνο ψηφία, νούμερα, κενό(διάστημα), την παύλα και την κάτω παύλα.');
	//88888888888   Above for Text Editor   888888888888888888888
	
	
?>