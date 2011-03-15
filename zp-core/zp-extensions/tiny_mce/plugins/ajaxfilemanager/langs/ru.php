<?php
	/**
	 * Russian language pack
	 * @author Slaver
	 * @link http://slaver.info/
	 * @since 20.06.2008
	 *
	 */
	define('DATE_TIME_FORMAT', 'd.M.Y H:i:s');
	//Common
	//Menu




	define('MENU_SELECT', 'Выбрать');
	define('MENU_DOWNLOAD', 'Скачать');
	define('MENU_PREVIEW', 'Просмотреть');
	define('MENU_RENAME', 'Переименовать');
	define('MENU_EDIT', 'Редактировать');
	define('MENU_CUT', 'Вырезать');
	define('MENU_COPY', 'Копировать');
	define('MENU_DELETE', 'Удалить');
	define('MENU_PLAY', 'Проиграть');
	define('MENU_PASTE', 'Вставить');

	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Обновить');
		define('LBL_ACTION_DELETE', 'Удалить');
		define('LBL_ACTION_CUT', 'Вырезать');
		define('LBL_ACTION_COPY', 'Копировать');
		define('LBL_ACTION_PASTE', 'Вставить');
		define('LBL_ACTION_CLOSE', 'Закрыть');
		define('LBL_ACTION_SELECT_ALL', 'Выделеть всё');
		//File Listing
	define('LBL_NAME', 'Имя');
	define('LBL_SIZE', 'Размер');
	define('LBL_MODIFIED', 'Изменено');
		//File Information
	define('LBL_FILE_INFO', 'Информация:');
	define('LBL_FILE_NAME', 'Имя:');
	define('LBL_FILE_CREATED', 'Создан:');
	define('LBL_FILE_MODIFIED', 'Изменен:');
	define('LBL_FILE_SIZE', 'Размер:');
	define('LBL_FILE_TYPE', 'Тип:');
	define('LBL_FILE_WRITABLE', 'Запись:');
	define('LBL_FILE_READABLE', 'Чтение:');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Информация');
	define('LBL_FOLDER_PATH', 'Путь:');
	define('LBL_CURRENT_FOLDER_PATH', 'Путь к текущей папке:');
	define('LBL_FOLDER_CREATED', 'Создан:');
	define('LBL_FOLDER_MODIFIED', 'Изменен:');
	define('LBL_FOLDER_SUDDIR', 'Папки:');
	define('LBL_FOLDER_FIELS', 'Файлы:');
	define('LBL_FOLDER_WRITABLE', 'Запись:');
	define('LBL_FOLDER_READABLE', 'Чтение:');
	define('LBL_FOLDER_ROOT', 'Корневая папка');
		//Preview
	define('LBL_PREVIEW', 'Предпросмотр');
	define('LBL_CLICK_PREVIEW', 'Нажмите, чтобы просмотреть.');
	//Buttons
	define('LBL_BTN_SELECT', 'Выбрать');
	define('LBL_BTN_CANCEL', 'Отмена');
	define('LBL_BTN_UPLOAD', 'Загрузить');
	define('LBL_BTN_CREATE', 'Создать');
	define('LBL_BTN_CLOSE', 'Закрыть');
	define('LBL_BTN_NEW_FOLDER', 'Новая папка');
	define('LBL_BTN_NEW_FILE', 'Новый файл');
	define('LBL_BTN_EDIT_IMAGE', 'Ред.');
	define('LBL_BTN_VIEW', 'Режим просмотра');
	define('LBL_BTN_VIEW_TEXT', 'Текст');
	define('LBL_BTN_VIEW_DETAILS', 'Таблица');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Искизы');
	define('LBL_BTN_VIEW_OPTIONS', 'Посмотреть в:');
	//pagination
	define('PAGINATION_NEXT', 'След.');
	define('PAGINATION_PREVIOUS', 'Пред.');
	define('PAGINATION_LAST', 'Последняя');
	define('PAGINATION_FIRST', 'Первая');
	define('PAGINATION_ITEMS_PER_PAGE', 'Показывать по %s на странице');
	define('PAGINATION_GO_PARENT', 'К родительской папке');
	//System
	define('SYS_DISABLED', 'Доступ отклонен: система недоступна.');


	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Выберите документ(ы), которые Вы хотите вырезать.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Выберите документ(ы), которые Вы хотите копировать.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Выберите документ(ы), которые Вы хотите вставить.');
	define('WARNING_CUT_PASTE', 'Вы уверенны, что хотите переместить выбранные документы в эту папку?');
	define('WARNING_COPY_PASTE', 'Вы уверенны, что хотите скопировать выбранные документы в эту папку?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Назначенная папка не указана.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Назначенная папка не найдена.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Вы не можете перемещать файлы в эту папку');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Ошибка при перемещении файла (%s): Исходная папка такая же как и назначенная.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Ошибка при перемещении файла (%s): Исходный файл не существует.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Ошибка при перемещении файла (%s): Доступ к исходному файлу запрещен.');
 
	define('ERR_NOT_FILES_PASTED', 'Ни один файл не был вставлен.');

	//Search
	define('LBL_SEARCH', 'Поиск');
	define('LBL_SEARCH_NAME', 'Полное/частичное название файла:');
	define('LBL_SEARCH_FOLDER', 'Искать в:');
	define('LBL_SEARCH_QUICK', 'Быстрый поиск');
	define('LBL_SEARCH_MTIME', 'Время имзенения файла:');
	define('LBL_SEARCH_SIZE', 'Размер файла:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Расширенные настройки');
	define('LBL_SEARCH_FILE_TYPES', 'Типы файлов:');
	define('SEARCH_TYPE_EXE', 'Приложение');
	
	define('SEARCH_TYPE_IMG', 'Изображение');
	define('SEARCH_TYPE_ARCHIVE', 'Архив');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Видео');
	define('SEARCH_TYPE_MOVIE', 'Ролик');
	define('SEARCH_TYPE_MUSIC', 'Музыка');
	define('SEARCH_TYPE_FLASH', 'Flash');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Документ');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Текст');
	define('SEARCH_TYPE_UNKNOWN', 'неизвестный');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Все типы файлов');
	define('LBL_SEARCH_RECURSIVELY', 'Искать рекурсивно:');
	define('LBL_RECURSIVELY_YES', 'Да');
	define('LBL_RECURSIVELY_NO', 'Нет');
	define('BTN_SEARCH', 'Искать');
	//thickbox
	define('THICKBOX_NEXT', 'След.&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Пред.');
	define('THICKBOX_CLOSE', 'Закрыть');
	//Calendar
	define('CALENDAR_CLOSE', 'Закрыть');
	define('CALENDAR_CLEAR', 'Очистить');
	define('CALENDAR_PREVIOUS', '&lt;Пред.');
	define('CALENDAR_NEXT', 'След.&gt;');
	define('CALENDAR_CURRENT', 'Сегодня');
	define('CALENDAR_MON', 'Пн');
	define('CALENDAR_TUE', 'Вт');
	define('CALENDAR_WED', 'Ср');
	define('CALENDAR_THU', 'Чт');
	define('CALENDAR_FRI', 'Пт');
	define('CALENDAR_SAT', 'Сб');
	define('CALENDAR_SUN', 'Вс');
	define('CALENDAR_JAN', 'Янв');
	define('CALENDAR_FEB', 'Фев');
	define('CALENDAR_MAR', 'Мар');
	define('CALENDAR_APR', 'Апр');
	define('CALENDAR_MAY', 'Май');
	define('CALENDAR_JUN', 'Июн');
	define('CALENDAR_JUL', 'Июл');
	define('CALENDAR_AUG', 'Авг');
	define('CALENDAR_SEP', 'Сен');
	define('CALENDAR_OCT', 'Окт');
	define('CALENDAR_NOV', 'Ноя');
	define('CALENDAR_DEC', 'Дек');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Пожалуйста, выберите файл.');
	define('ERR_NOT_DOC_SELECTED', 'Выберите документ(ы), которые Вы хотите удалить.');
	define('ERR_DELTED_FAILED', 'Невозможно удалить выбранные документ(ы).');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Недопустимый путь к папке.');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Невозможно найти папку: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Пожалуйста, укажите корректное имя. Разрешены буквы латинского алфавита, цифры, пробел, дефис и нижнее подчеркивание.');
	define('ERR_RENAME_EXISTS', 'Это имя уже используется в данной папке. Пожалуйста, укажите другое имя.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Файл или папка не существует.');
	define('ERR_RENAME_FAILED', 'Невозможно переименовать. Пожалуйста, повторите позже.');
	define('ERR_RENAME_EMPTY', 'Пожалуйста, укажите имя.');
	define('ERR_NO_CHANGES_MADE', 'Изменения не были произведены.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Переименование в файл с таким расширением запрещено.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Пожалуйста, укажите корректное имя. Разрешены буквы латинского алфавита, цифры, пробел, дефис и нижнее подчеркивание.');
	define('ERR_FOLDER_EXISTS', 'Это имя уже используется в данной папке. Пожалуйста, укажите другое имя.');
	define('ERR_FOLDER_CREATION_FAILED', 'Невозможно создать папку. Пожалуйста, повторите позже.');
	define('ERR_FOLDER_NAME_EMPTY', 'Пожалуйста, укажите имя.');
	define('FOLDER_FORM_TITLE', 'Создание новой папки');
	define('FOLDER_LBL_TITLE', 'Название:');
	define('FOLDER_LBL_CREATE', 'Создать папку');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Создание нового файла');
	define('NEW_FILE_LBL_TITLE', 'Название файла:');
	define('NEW_FILE_CREATE', 'Создать файл');

		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Пожалуйста, укажите корректное имя. Разрешены буквы латинского алфавита, цифры, пробел, дефис и нижнее подчеркивание.');
	define('ERR_FILE_NOT_UPLOADED', 'Не выбран файл для загрузки.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Загрузка файлов с таким расширением запрещена.');
	define('ERR_FILE_MOVE_FAILED', 'Не удалось переместить файл.');
	define('ERR_FILE_NOT_AVAILABLE', 'Файл недоступен.');
	define('ERROR_FILE_TOO_BID', 'Файл слишком большой. (Максимально допустимый размер: %s)');
	define('FILE_FORM_TITLE', 'Форма загрузки файлов');
	define('FILE_LABEL_SELECT', 'Выбрать');
	define('FILE_LBL_MORE', 'Добавить поле загрузки');
	define('FILE_CANCEL_UPLOAD', 'Остановить загрузку');
	define('FILE_LBL_UPLOAD', 'Загрузить');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Не выбраны файлы для загрузки.');
	//Rename
	define('RENAME_FORM_TITLE', 'Переименование файла');
	define('RENAME_NEW_NAME', 'Новое имя');
	define('RENAME_LBL_RENAME', 'Переименовать');
	//Tips
	define('TIP_FOLDER_GO_DOWN', 'Кликните, чтобы войти в эту папку...');
	define('TIP_DOC_RENAME', 'Кликните дважды для редактирования...');
	define('TIP_FOLDER_GO_UP', 'Кликните, чтобы переместится в родительску папку...');
	define('TIP_SELECT_ALL', 'Выделить все');
	define('TIP_UNSELECT_ALL', 'Снять выделение');
	//WARNING
	define('WARNING_DELETE', 'Вы действительно хотите удалить выбранные файлы?.');
	define('WARNING_IMAGE_EDIT', 'Пожалуйста, выберите изображение для редактирования.');
	define('WARNING_NOT_FILE_EDIT', 'Пожалуйста, выберите файл для редактирования.');
	define('WARING_WINDOW_CLOSE', 'Вы действительно хотите закрыть это окно?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Предпросмотр недоступен.');
	define('PREVIEW_OPEN_FAILED', 'Невозможно открыть файл.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Невозможно загрузить изображение.');

	//Login
	define('LOGIN_PAGE_TITLE', 'Вход в менеджер файлов');
	define('LOGIN_FORM_TITLE', 'Вход');
	define('LOGIN_USERNAME', 'Имя:');
	define('LOGIN_PASSWORD', 'Пароль:');
	define('LOGIN_FAILED', 'Неверное имя или пароль..');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', "Не было сделано никаких изменений в изображении.");
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Изображение не существует.');
		define('IMG_WARNING_LOST_CHANAGES', 'Все несохраненные изменения будут потеряны. Вы уверенны, что хотите продолжить?');
		define('IMG_WARNING_REST', 'Все несохраненные изменения будут потеряны. Вы уверенны, что хотите сбросить изменения?');
		define('IMG_WARNING_EMPTY_RESET', 'Не было сделано никаких изменений в изображении до настоящего времени.');
		define('IMG_WARING_WIN_CLOSE', 'Вы уверены, что хотите закрыть окно?');
		define('IMG_WARNING_UNDO', 'Вы уверены, что хотите восстановить изображение к предыдущему состоянию?');
		define('IMG_WARING_FLIP_H', 'Вы уверены, что хотите отразить изображение горизонтально?');
		define('IMG_WARING_FLIP_V', 'Вы уверены, что хотите отразить изображение вертикально?');
		define('IMG_INFO', 'Информация об изображении');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Изменить размер');
			define('IMG_MODE_CROP', 'Обрезать');
			define('IMG_MODE_ROTATE', 'Повернуть');
			define('IMG_MODE_FLIP', 'Отобразить зеркально');
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg; против часовй');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg; по часовой');
			define('IMG_BTN_FLIP_H', 'Отразить горизонтально');
			define('IMG_BTN_FLIP_V', 'Отразить вертикально');
			define('IMG_BTN_RESET', 'Сбросить');
			define('IMG_BTN_UNDO', 'Отменить');
			define('IMG_BTN_SAVE', 'Сохранить');
			define('IMG_BTN_CLOSE', 'Закрыть');
			define('IMG_BTN_SAVE_AS', 'Сохранить как');
			define('IMG_BTN_CANCEL', 'Отменить');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Сохранять пропорции');
		//Label
			define('IMG_LBL_WIDTH', 'Ширина:');
			define('IMG_LBL_HEIGHT', 'Высота:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Коэффициент:');
			define('IMG_LBL_ANGLE', 'Угол поворота:');
			define('IMG_LBL_NEW_NAME', 'Новое имя:');
			define('IMG_LBL_SAVE_AS', 'Созранение файла');
			define('IMG_LBL_SAVE_TO', 'Сохранить в:');
			define('IMG_LBL_ROOT_FOLDER', 'Корневая папка');
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', 'Пожалуйста, не изменяйте расширение файла.');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Имя файла может состоять только из букв, цифр, пробела, знаков дефиса и подчеркивания.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Не выбрана папка назначения.');	
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Папка назначения не существует.');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Файл с таким именем уже существует.');
		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Путь к изображению пуст.');
		define('IMG_SAVE_NOT_EXISTS', 'Изображение не существует.');
		define('IMG_SAVE_PATH_DISALLOWED', 'Доступ к файлу запрещен.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Неподдерживаемая операция.');
		define('IMG_SAVE_RESIZE_FAILED', 'Не удалось изменить размер изображения.');
		define('IMG_SAVE_CROP_FAILED', 'Не удалось обрезать изображение.');
		define('IMG_SAVE_FAILED', 'Не удалось сохранить изображение.');
		define('IMG_SAVE_BACKUP_FAILED', 'Не удалось создать архивную копию оригинального изображения.');
		define('IMG_SAVE_ROTATE_FAILED', 'Не удалось повернуть изображение.');
		define('IMG_SAVE_FLIP_FAILED', 'Не удалось зеркально отобразить изображение.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Не удалось открыть изображение из сессии.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Не удалось открыть изображение.');
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'Невозможно отменить операцию, так как история изменений отсутствует.');
		define('IMG_UNDO_COPY_FAILED', 'Невозможно восстановить изображение.');
		define('IMG_UNDO_DEL_FAILED', 'Невозможно удалить сессию изображения.');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Невозможно найти папку, предназначенную для хранения сессии.');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', 'Невозможно открыть файл сессии.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Невозможно сделать запись в файл сессии.');
	//88888888888   Session   888888888888888888888

	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'Файл не найден.');
		define('TXT_EXT_NOT_SELECTED', 'Пожалуйста, выберите расширение файла');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Пожалуйста, выберите папку назначения');
		define('TXT_UNKNOWN_REQUEST', 'Неизвестный запрос.');
		define('TXT_DISALLOWED_EXT', 'Вы можете редактировать и добавлять файлы такого формата.');
		define('TXT_FILE_EXIST', 'Такой файл уже существует.');
		define('TXT_FILE_NOT_EXIST', 'Ничего не найдено.');
		define('TXT_CREATE_FAILED', 'Ошибка при создании файла.');
		define('TXT_CONTENT_WRITE_FAILED', 'Ошибка при записи содержимого в файл.');
		define('TXT_FILE_OPEN_FAILED', 'Ошибка при открытии файла.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Ошибка при редактировании содержимого файла.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Имя файла может состоять только из букв, цифр, пробела, знаков дефиса и подчеркивания.');
	//88888888888   Above for Text Editor   888888888888888888888

?>