<?php
	/**
	 * language pack
	 * @author Logan Cai (cailongqun [at] yahoo [dot] com [dot] cn)
	 * @link www.phpletter.com
	 * @since 22/April/2007
	 *
	 */
		define('DATE_TIME_FORMAT', "d/M/Y H:i:s");
		//Common
		//Menu
		define('MENU_SELECT', "Selectionne");
		define('MENU_DOWNLOAD', "Transferer");
		define('MENU_PREVIEW', "Aperçu");
		define('MENU_RENAME', "Renommer");
		define('MENU_EDIT', "Editer");
		define('MENU_CUT', "Couper");
		define('MENU_COPY', "Copier");
		define('MENU_DELETE', "Effacer");
		define('MENU_PLAY', "Jouer");
		define('MENU_PASTE', "Coller");

    //Label
    //Top Action
		define('LBL_ACTION_REFRESH', "Rafraichir");
		define('LBL_ACTION_DELETE', "Supprimer");
		define('LBL_ACTION_CUT', "Couper");
		define('LBL_ACTION_COPY', "Copier");
		define('LBL_ACTION_PASTE', "Coller");
    define('LBL_ACTION_CLOSE', "Fermer");
    define('LBL_ACTION_SELECT_ALL', "Sélect. tout");

    //File Listing
    define('LBL_NAME', "Nom");
    define('LBL_SIZE', "Poids");
    define('LBL_MODIFIED', "Modifié le");

		//File Information
    define('LBL_FILE_INFO', "Information sur le fichier :");
    define('LBL_FILE_NAME', "Nom :");    
    define('LBL_FILE_CREATED', "Créé le :");
    define('LBL_FILE_MODIFIED', "Modifié le :");
    define('LBL_FILE_SIZE', "Poids du fichier :");
    define('LBL_FILE_TYPE', "Type du fichier :");
    define('LBL_FILE_WRITABLE', "Modifiable ?");
    define('LBL_FILE_READABLE', "Lisible ?");

		//Folder Information
    define('LBL_FOLDER_INFO', "Information du dossier");
    define('LBL_FOLDER_PATH', "Chemin :");
    define('LBL_CURRENT_FOLDER_PATH', "Chemin actuel du dossier :");
    define('LBL_FOLDER_CREATED', "Créé Le :");
    define('LBL_FOLDER_MODIFIED', "Modifié Le :");
    define('LBL_FOLDER_SUDDIR', "Sous-dossiers :");
    define('LBL_FOLDER_FIELS', "Fichiers :");
    define('LBL_FOLDER_WRITABLE', "Modifiable ?");
    define('LBL_FOLDER_READABLE', "Lisible ?");
    define('LBL_FOLDER_ROOT', "Dossier racine");
    
			//Preview
    define('LBL_PREVIEW', "Aperçu");
    define('LBL_CLICK_PREVIEW', "Cliquer ici pour avoir un aperçu.");
    
			//Buttons
    define('LBL_BTN_SELECT', "Choisir");
    define('LBL_BTN_CANCEL', "Annuler");
    define('LBL_BTN_UPLOAD', "Transférer");
    define('LBL_BTN_CREATE', "Créer");
    define('LBL_BTN_CLOSE', "Fermer");
    define('LBL_BTN_NEW_FOLDER', "Nouveau Dossier");
    define('LBL_BTN_EDIT_IMAGE', "Modifier");
		define('LBL_BTN_NEW_FILE', "Nouveau fichier");
		define('LBL_BTN_VIEW', "Selectionne Vu");
		define('LBL_BTN_VIEW_TEXT', "Text");
		define('LBL_BTN_VIEW_DETAILS', "Détails");
		define('LBL_BTN_VIEW_THUMBNAIL', "Miniatures");
		define('LBL_BTN_VIEW_OPTIONS', "Regarde dans :");

		//pagination
    define('PAGINATION_NEXT', "Suivant");
    define('PAGINATION_PREVIOUS', "Précédent");
    define('PAGINATION_LAST', "Fin");
    define('PAGINATION_FIRST', "Début");
    define('PAGINATION_ITEMS_PER_PAGE', "Afficher %s éléments par page");
    define('PAGINATION_GO_PARENT', "Dossier Parent");

    //System
    define('SYS_DISABLED', "Permission refusée: Le système est désactivé.");

    //Cut
    define('ERR_NOT_DOC_SELECTED_FOR_CUT', "Aucun document(s) selectionné pour couper.");

    //Copy
    define('ERR_NOT_DOC_SELECTED_FOR_COPY', "Aucun document(s) selectionné pour copier.");

    //Paste
    define('ERR_NOT_DOC_SELECTED_FOR_PASTE', "Aucun document(s) selectionné pour coller.");
    define('WARNING_CUT_PASTE', "Voulez-vous vraiment déplacer les documents selectionnés dans le dossier courant ?");
    define('WARNING_COPY_PASTE', "Voulez-vous vraiment copier les documents selectionnés dans le dossier courant ?");
		define('ERR_NOT_DEST_FOLDER_SPECIFIED', "Aucun dossier cible spécifié.");
		define('ERR_DEST_FOLDER_NOT_FOUND', "Aucun dossier cible trouvé.");
		define('ERR_DEST_FOLDER_NOT_ALLOWED', "Vous n'êtes pas autorisé à déplacer des fichiers dans ce dossier");
		define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', "Impossible de déplacer ce fichier (%s): Le dossier source est identique au dossier cible.");
		define('ERR_UNABLE_TO_MOVE_NOT_FOUND', "Impossible de déplacer ce fichier (%s): Le fichier d\'origine n\'existe pas.");
		define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', "Impossible de déplacer ce fichier (%s): Accès refusé au fichier d\'origine.");
		define('ERR_NOT_FILES_PASTED', "Aucun fichier collé.");

		//Search
		define('LBL_SEARCH', "Rechercher");
		define('LBL_SEARCH_NAME', "Nom de Fichier (Intégral ou Partiel) :");
		define('LBL_SEARCH_FOLDER', "Chercher dans :");
		define('LBL_SEARCH_QUICK', "Recherche Rapide");
		define('LBL_SEARCH_MTIME', "Date de Modification (Période) :");
		define('LBL_SEARCH_SIZE', "Taille du Fichier :");
		define('LBL_SEARCH_ADV_OPTIONS', "Options Avancées");
		define('LBL_SEARCH_FILE_TYPES', "Types de Fichiers :");
		define('SEARCH_TYPE_EXE', "Application");

		define('SEARCH_TYPE_IMG', "Image");
		define('SEARCH_TYPE_ARCHIVE', "Archive");
		define('SEARCH_TYPE_HTML', "HTML");
		define('SEARCH_TYPE_VIDEO', "Vidéo");
		define('SEARCH_TYPE_MOVIE', "Film");
		define('SEARCH_TYPE_MUSIC', "Musique");
		define('SEARCH_TYPE_FLASH', "Flash");
		define('SEARCH_TYPE_PPT', "PowerPoint");
		define('SEARCH_TYPE_DOC', "Document");
		define('SEARCH_TYPE_WORD', "Word");
		define('SEARCH_TYPE_PDF', "PDF");
		define('SEARCH_TYPE_EXCEL', "Excel");
		define('SEARCH_TYPE_TEXT', "Texte");
		define('SEARCH_TYPE_UNKNOWN', "Inconnu");
		define('SEARCH_TYPE_XML', "XML");
		define('SEARCH_ALL_FILE_TYPES', "Tous Types de Fichiers");
		define('LBL_SEARCH_RECURSIVELY', "Recherche Récursive :");
		define('LBL_RECURSIVELY_YES', "Oui");
		define('LBL_RECURSIVELY_NO', "Non");
		define('BTN_SEARCH', "Rechercher");

		//thickbox
		define('THICKBOX_NEXT', "Suivant&gt;");
		define('THICKBOX_PREVIOUS', "&lt;Précédent");
		define('THICKBOX_CLOSE', "Fermer"); // ici

		//Calendar
		define('CALENDAR_CLOSE', "Fermer");
		define('CALENDAR_CLEAR', "Effacer");
		define('CALENDAR_PREVIOUS', '&lt;Précédent');
		define('CALENDAR_NEXT', 'Suivant&gt;');
		define('CALENDAR_CURRENT', 'Aujourd\\\'hui');
		define('CALENDAR_MON', 'Lun');
		define('CALENDAR_TUE', 'Mar');
		define('CALENDAR_WED', 'Mer');
		define('CALENDAR_THU', 'Jeu');
		define('CALENDAR_FRI', 'Ven');
		define('CALENDAR_SAT', 'Sam');
		define('CALENDAR_SUN', 'Dim');
		define('CALENDAR_JAN', 'Jan');
		define('CALENDAR_FEB', 'Fév');
		define('CALENDAR_MAR', 'Mar');
		define('CALENDAR_APR', 'Avr');
		define('CALENDAR_MAY', 'Mai');
		define('CALENDAR_JUN', 'Juin');
		define('CALENDAR_JUL', 'Juil');
		define('CALENDAR_AUG', 'Aoû');
		define('CALENDAR_SEP', 'Sep');
		define('CALENDAR_OCT', 'Oct');
		define('CALENDAR_NOV', 'Nov');
		define('CALENDAR_DEC', 'Déc');

		//ERROR MESSAGES
		//Suppresion
    define('ERR_NOT_FILE_SELECTED', "Il faut choisir un fichier.");
    define('ERR_NOT_DOC_SELECTED', "Aucun document(s) selectionné pour la suppression.");
    define('ERR_DELTED_FAILED', "Impossible de supprimer le(s) document(s) selectionné.");
    define('ERR_FOLDER_PATH_NOT_ALLOWED', "Le chemin du dossier n\'est pas autorisé.");

		//class manager
    define('ERR_FOLDER_NOT_FOUND', "Impossible de trouver le dossier spécifié : ");

		//rename
    define('ERR_RENAME_FORMAT', "Il faut saisir un nom qui contient uniquement des lettres, chiffres, espaces, tirets et tirets-bas.");
    define('ERR_RENAME_EXISTS', "Il faut saisir un nom qui n\'est pas déjà pris dans ce dossier.");
    define('ERR_RENAME_FILE_NOT_EXISTS', "Le fichier/dossier n\'existe pas.");
    define('ERR_RENAME_FAILED', "Impossible de le renommer, merci de recommencer.");
    define('ERR_RENAME_EMPTY', "Il faut préciser un nom.");
    define('ERR_NO_CHANGES_MADE', "Aucun changement n\'a été effectué.");
    define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', "Vous n\'êtes pas autorisé à changer de la sorte l\'extension du fichier.");

		//folder creation
    define('ERR_FOLDER_FORMAT', "Il faut saisir un nom qui contient uniquement des lettres, chiffres, espaces, tirets et tirets-bas.");
    define('ERR_FOLDER_EXISTS', "Il faut saisir un nom qui n\'est pas déjà pris dans ce dossier.");
    define('ERR_FOLDER_CREATION_FAILED', "Impossible de créer un dossier, merci de recommencer.");
    define('ERR_FOLDER_NAME_EMPTY', "Il faut préciser un nom.");
    define('FOLDER_FORM_TITLE', "Nouvueau répertoire");
		define('FOLDER_LBL_TITLE', "Titre répertoire:");
		define('FOLDER_LBL_CREATE', "Créer répertoire");

		//New File
		define('NEW_FILE_FORM_TITLE', "Nouveau fichier");
		define('NEW_FILE_LBL_TITLE', "Nom du fichier:");
		define('NEW_FILE_CREATE', "Créer fichier");

		//file upload
    define('ERR_FILE_NAME_FORMAT', "Il faut saisir un nom qui contient uniquement des lettres, chiffres, espaces, tirets et tirets-bas.");
    define('ERR_FILE_NOT_UPLOADED', "Aucun fichier n\'a été selectionné pour être transféré.");
    define('ERR_FILE_TYPE_NOT_ALLOWED', "Vous n\'êtes pas autorisé à transférer ce type de fichier.");
    define('ERR_FILE_MOVE_FAILED', "Le déplacement du fichier a échoué.");
    define('ERR_FILE_NOT_AVAILABLE', "Le fichier est indisponible.");
    define('ERROR_FILE_TOO_BID', "Le fichier est trop lourd. (max : %s)");
    define('FILE_FORM_TITLE', "Transferer Fichier");
		define('FILE_LABEL_SELECT', "Select. le fichier");
		define('FILE_LBL_MORE', "Ajoute fichier à transferer");
		define('FILE_CANCEL_UPLOAD', "Annule transfert fichier");
		define('FILE_LBL_UPLOAD', "Transferer");

		//file download
    define('ERR_DOWNLOAD_FILE_NOT_FOUND', "Aucun fichier selectionné pour être téléchargé.");

    //Rename
		define('RENAME_FORM_TITLE', "Renommer");
		define('RENAME_NEW_NAME', "Nouveau Nom");
		define('RENAME_LBL_RENAME', "Renommer");

		//Tips
    define('TIP_FOLDER_GO_DOWN', "Cliquer pour aller dans ce dossier ...");
    define('TIP_DOC_RENAME', "Double cliquer pour modifier ...");
    define('TIP_FOLDER_GO_UP', "Cliquer pour aller au dossier parent...");
    define('TIP_SELECT_ALL', "Tout selectionner");
    define('TIP_UNSELECT_ALL', "Tout déselectionner");

		//WARNING
    define('WARNING_DELETE', "Voulez-vous vraiment effacer les fichiers selectionnés.");
    define('WARNING_IMAGE_EDIT', "Merci de choisir une image à modifier.");
    define('WARNING_NOT_FILE_EDIT', "Merci de choisir un fichier à modifier.");
    define('WARING_WINDOW_CLOSE', "Voulez-vous vraiment fermer la fenêtre ?");

    //Preview
    define('PREVIEW_NOT_PREVIEW', "Aucun aperçu disponible.");
    define('PREVIEW_OPEN_FAILED', "Impossible d\'ouvrir le fichier.");
    define('PREVIEW_IMAGE_LOAD_FAILED', "Impossible de charger l\'image");

		//Login
    define('LOGIN_PAGE_TITLE', "Ajax File Manager : Formulaire d\'authentification");
    define('LOGIN_FORM_TITLE', "Formulaire d\'authentification");
    define('LOGIN_USERNAME', "Utilisateur :");
    define('LOGIN_PASSWORD', "Mot de passe :");
    define('LOGIN_FAILED', "Utilisateur/Mot de passe erroné.");

		//88888888888  Below for Image Editor  888888888888888888888

		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', "L\'image n\'a pas ete modifiee.");

		//General
		define('IMG_GEN_IMG_NOT_EXISTS', "L\'image n\'existe pas");
		define('IMG_WARNING_LOST_CHANAGES', "Toutes les modifications qui n\'ont pas ete sauvegardees seront perdues, voulez-vous vraiment continuer ?");
		define('IMG_WARNING_REST', "Toutes les modifications qui n\'ont pas ete sauvegardees seront perdues, voulez-vous vraiment remettre a zero ?");
		define('IMG_WARNING_EMPTY_RESET', "L\'image n\'a pas encore ete modifiee");
		define('IMG_WARING_WIN_CLOSE', "Voulez-vous vraiment fermer la fenetre ?");
		define('IMG_WARING_FLIP_H', "Voulez-vous vraiment basculer l\'image horizontalement ?");
		define('IMG_WARING_FLIP_V', "Voulez-vous vraiment à basculer l\'image verticalement ?");
		define('IMG_INFO', "Information sur l'image");

		//Mode
		define('IMG_MODE_RESIZE', "Redimensionner :");
		define('IMG_MODE_CROP', "Découper :");
		define('IMG_MODE_ROTATE', "Rotation :");
		define('IMG_MODE_FLIP', "Basculer :");

		//Button
		define('IMG_BTN_ROTATE_LEFT', "90° vers la gauche");
		define('IMG_BTN_ROTATE_RIGHT', "90° vers la droite");
		define('IMG_BTN_FLIP_H', "Miroir Horizontal");
		define('IMG_BTN_FLIP_V', "Miroir Vertical");
		define('IMG_BTN_RESET', "Remise à zéro");
		define('IMG_BTN_UNDO', "Défaire");
		define('IMG_BTN_SAVE', "Sauvegarder");
		define('IMG_BTN_CLOSE', "Fermer");
		define('IMG_BTN_SAVE_AS', "Sauvegarder sous");
		define('IMG_BTN_CANCEL', "Annuler");
		
		//Checkbox
		define('IMG_CHECKBOX_CONSTRAINT', "Contrainte ?");
		
		//Label
		define('IMG_LBL_WIDTH', "Largeur :");
		define('IMG_LBL_HEIGHT', "Hauteur :");
		define('IMG_LBL_X', "X :");
		define('IMG_LBL_Y', "Y :");
		define('IMG_LBL_RATIO', "Ratio :");
		define('IMG_LBL_ANGLE', "Angle :");
		define('IMG_LBL_NEW_NAME', "Nouveau nom :");
		define('IMG_LBL_SAVE_AS', "Sauvergarder sous");
		define('IMG_LBL_SAVE_TO', "Sauvegarder dans :");
		define('IMG_LBL_ROOT_FOLDER', "Dossier racine");
		
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', "Ne pas mettre l\'extension de l\'image.");
		define('IMG_SAVE_AS_ERR_NAME_INVALID', "Il faut saisir un nom qui contient uniquement des lettres, chiffres, espaces, tirets et tirets-bas.");
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', "Il faut préciser le dossier de destination.");
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', "Le dossier de destination existe déjà.");
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', "Des images portent le même nom.");

		//Save
		define('IMG_SAVE_EMPTY_PATH', "Le chemin de l\'image est vide.");
		define('IMG_SAVE_NOT_EXISTS', "L\'image n\'existe pas.");
		define('IMG_SAVE_PATH_DISALLOWED', "Vous n\'êtes pas autorisé à accéder à ce fichier.");
		define('IMG_SAVE_UNKNOWN_MODE', "Mode inattendu d\'opération d\'image");
		define('IMG_SAVE_RESIZE_FAILED', "Echec du redimensionnement de l\'image.");
		define('IMG_SAVE_CROP_FAILED', "Echec du découpage de l\'image.");
		define('IMG_SAVE_FAILED', "Echec de la sauvegarde de l\'image.");
		define('IMG_SAVE_BACKUP_FAILED', "Impossible de sauvegarder l\'image originale.");
		define('IMG_SAVE_ROTATE_FAILED', "Impossible d\'effectuer la rotation de l\'image.");
		define('IMG_SAVE_FLIP_FAILED', "Impossible de basculer l\'image.");
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', "Impossible d\'ouvrir l\'image de session.");
 		define('IMG_SAVE_IMG_OPEN_FAILED', "Impossible d\'ouvrir l\'image");


		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', "Aucun historique d\'annulation.");
 		define('IMG_UNDO_COPY_FAILED', "Impossible de restaurer l\'image.");
 		define('IMG_UNDO_DEL_FAILED', "Impossible de supprimer l\'image de session");
 		
 		define('IMG_WARNING_UNDO', "Voulez-vous annuler la dernière modification ?");

		//88888888888   Above for Image Editor   888888888888888888888


		//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', "Impossible de trouver le dossier dédié qui aurait dû être créé dans le dossier session");
		define('SESSION_COUNTER_FILE_CREATE_FAILED', "Impossible d\'ouvrir le fichier de comptage de session.");
		define('SESSION_COUNTER_FILE_WRITE_FAILED', "Impossible d\'écrire dans le fichier de comptage de session.");
		//88888888888   Session   888888888888888888888

		//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', "Le fichier n\'a pas été trouvé.");
		define('TXT_EXT_NOT_SELECTED', "Merci de choisir une extension au fichier.");
		define('TXT_DEST_FOLDER_NOT_SELECTED', "Merci de choisir un dossier de destination.");
		define('TXT_UNKNOWN_REQUEST', "Requête inconnue.");
		define('TXT_DISALLOWED_EXT', "Vous n\'êtes pas autorisé à modifier/ajouter ce type de fichier.");
		define('TXT_FILE_EXIST', "Ce fichier existe déjà.");
		define('TXT_FILE_NOT_EXIST', "Ce fichier n\'existe pas.");	
		define('TXT_CREATE_FAILED', "Echec de la création du fichier.");
		define('TXT_CONTENT_WRITE_FAILED', "Echec de l\'écriture du contenu dans le fichier.");
		define('TXT_FILE_OPEN_FAILED', "Echec de l\'ouverture du fichier.");
		define('TXT_CONTENT_UPDATE_FAILED', "Echec de la mise à jour du contenu du fichier.");
		define('TXT_SAVE_AS_ERR_NAME_INVALID', "Il faut saisir un nom qui contient uniquement des lettres, chiffres, espaces, tirets et tirets-bas.");
		//88888888888   Above for Text Editor   888888888888888888888

?>