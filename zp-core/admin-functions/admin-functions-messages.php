<?php 
/**
 * Messages related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

function consolidatedEditMessages($subtab) {
	zp_apply_filter('admin_note', 'albums', $subtab);
	$messagebox = $errorbox = $notebox = array();
	if (isset($_GET['ndeleted'])) {
		$ntdel = sanitize_numeric($_GET['ndeleted']);
		if ($ntdel <= 2) {
			$msg = gettext("Image");
		} else {
			$msg = gettext("Album");
			$ntdel = $ntdel - 2;
		}
		if ($ntdel == 2) {
			$errorbox[] = sprintf(gettext("%s failed to delete."), $msg);
		} else {
			$messagebox[] = sprintf(gettext("%s deleted successfully."), $msg);
		}
	}
	if (isset($_GET['mismatch'])) {
		if ($_GET['mismatch'] == 'user') {
			$errorbox[] = gettext("You must supply a password.");
		} else {
			$errorbox[] = gettext("Your passwords did not match.");
		}
	}
	if (isset($_GET['edit_error'])) {
		$errorbox[] = html_encode(sanitize($_GET['edit_error']));
	}
	if (isset($_GET['post_error'])) {
		$errorbox[] = sprintf(gettext('The form submission has been truncated because you exceeded the server side limit <code>max_input_vars</code> of %d. Try displaying fewer items per page or try to raise the server limits.'), ini_get('max_input_vars'));
	}
	if (isset($_GET['counters_reset'])) {
		$messagebox[] = gettext("Hit counters have been reset.");
	}
	if (isset($_GET['cleared']) || isset($_GET['action']) && $_GET['action'] == 'clear_cache') {
		$messagebox[] = gettext("Cache has been purged.");
	}
	if (isset($_GET['uploaded'])) {
		$messagebox[] = gettext('Your files have been uploaded.');
	}
	if (isset($_GET['exists'])) {
		$errorbox[] = sprintf(gettext("<em>%s</em> already exists."), sanitize($_GET['exists']));
	}
	if (isset($_GET['saved'])) {
		$messagebox[] = gettext("Changes applied");
	}
	if (isset($_GET['noaction'])) {
		$notebox[] = gettext("Nothing changed");
	}
	if (isset($_GET['bulkmessage'])) {
		$action = sanitize($_GET['bulkmessage']);
		switch ($action) {
			case 'deleteallalbum':
			case 'deleteall':
				$messagebox[] = gettext('Selected items deleted');
				break;
			case 'showall':
				$messagebox[] = gettext('Selected items published');
				break;
			case 'hideall':
				$messagebox[] = gettext('Selected items unpublished');
				break;
			case 'commentson':
				$messagebox[] = gettext('Comments enabled for selected items');
				break;
			case 'commentsoff':
				$messagebox[] = gettext('Comments disabled for selected items');
				break;
			case 'resethitcounter':
				$messagebox[] = gettext('Hitcounter for selected items');
				break;
			case 'addtags':
				$messagebox[] = gettext('Tags added for selected items');
				break;
			case 'cleartags':
				$messagebox[] = gettext('Tags cleared for selected items');
				break;
			case 'alltags':
				$messagebox[] = gettext('Tags added for images of selected items');
				break;
			case 'clearalltags':
				$messagebox[] = gettext('Tags cleared for images of selected items');
				break;
			default:
				$message = zp_apply_filter('bulk_actions_message', $action);
				if (empty($message)) {
					$messagebox[] = $action;
				} else {
					$messagebox[] = $message;
				}
				break;
		}
	}
	if (isset($_GET['mcrerr'])) {
		// move/copy error messages
		$mcrerr_messages = array(
				1 => gettext("There was an error #%d with a move, copy, or rename operation."), // default message if 2-7 don't apply us with sprintf
				2 => gettext("Cannot move, copy, or rename. Image already exists."),
				3 => gettext("Cannot move, copy, or rename. Album already exists."),
				4 => gettext("Cannot move, copy, or rename to a subalbum of this album."),
				5 => gettext("Cannot move, copy, or rename to a dynamic album."),
				6 => gettext('Cannot rename an image to a different suffix'),
				7 => gettext('Album delete failed')
		);
		if (is_array($_GET['mcrerr'])) {
			// action move/copy error messages
			$mcrerr = sanitize($_GET['mcrerr']);
			foreach ($mcrerr as $errno => $ids) {
				$errornumber = sanitize_numeric($errno);
				if ($errornumber) {
					if ($errornumber < 1 || $errornumber > 8) {
						$errorbox[] = sprintf($mcrerr_messages[1], sanitize_numeric($errornumber));
					} else {
						$errorbox[] = $mcrerr_messages[$errornumber];
					}
					$list = '';
					foreach ($ids as $id) {
						$itemid = sanitize_numeric($id);
						if ($itemid) {
							// item id might be an image or album id, we don't know so we test…
							$obj = getItemByID('images', $itemid);
							if ($obj) {
								$list .= '<li>' . html_encode($obj->getTitle()) . ' (' . $obj->filename . ')</li>';
							} else {
								$obj = getItemByID('albums', $itemid);
								if ($obj) {
									$list .= '<li>' . html_encode($obj->getTitle()) . ' (' . $obj->name . ')</li>';
								}
							}
						}
					}
					if (!empty($list)) {
						$errorbox[] = '<ul>' . $list . '</ul>';
					}
				}
			}
		} else {
			// legacy -  move/copy error message
			$mcrerr = sanitize_numeric($_GET['mcrerr']);
			if ($mcrerr < 2 || $mcrerr > 7) {
				$errorbox[] = sprintf($mcrerr_messages[1], sanitize_numeric($_GET['mcrerr']));
			} else {
				$errorbox[] = $mcrerr_messages[$mcrerr];
			}
		}
	}
	if (!empty($errorbox)) {
		?>
		<div class="errorbox fade-message">
			<?php echo implode('<br />', $errorbox); ?>
		</div>
		<?php
	}
	if (!empty($notebox)) {
		?>
		<div class="notebox fade-message">
			<?php echo implode('<br />', $notebox); ?>
		</div>
		<?php
	}
	if (!empty($messagebox)) {
		?>
		<div class="messagebox fade-message">
			<?php echo implode('<br />', $messagebox); ?>
		</div>
		<?php
	}
}

/**
 * Helper to check if notes are to be printed (only needed because of the inconvenient legacy table based layout on image edit pages)
 * @since 1.5.7
 * @param obj $obj Image, album, news article or page object
 * @return boolean
 */
function checkSchedulePublishingNotes($obj) {
	if (getStatusNotesByContext($obj)) {
		return true;
	}
	return false;
}

/**
 * Prints various notes regarding the scheduled publishing status for single edit pages
 * 
 * @since 1.5.7
 * @deprecated 2.0 - Use printStatusNotes() instead
 * @param obj $obj Image, album, news article or page object
 */
function printScheduledPublishingNotes($obj) {
	deprecationNotice('Use printStatusNotes() instead');
	printStatusNotes($obj);
}

/**
 * Prints various notes regarding the scheduled publishing status for single edit pages
 * 
 * @since 1.6.1 Replaces printScheduledPublishingNotes()
 * @param obj $obj Image, album, news article, new category or page object
 */
function printStatusNotes($obj) {
	$notes = getStatusNotesByContext($obj);
	if ($notes) {
		foreach($notes as $note) {
			echo $note;
		}
	}
}

/**
 * Gets a specific predefined status note for an object (if available)
 * Note: The notes are not status dependend!
 * 
 * @param obj $obj Image, album, news article, new category or page object
 * @param string $name Name of the note
 * @return string
 */
function getStatusNote($name = '') {
	$notes = getStatusNotes();
	if (array_key_exists($name, $notes)) {
		return $notes[$name];
	}
}

/**
 * Gets an array of all predefined status notes
 * @since 1.6.1
 * 
 * @return array
 */
function getStatusNotes() {
	return array(
			'unpublished' => gettext('Unpublished'),
			'unpublished_by_parent' => gettext('Unpublished by parent'),
			'protected' => gettext('Password protected'),
			'protected_by_parent' => gettext('Password protected by parent'),
			'protected_by_site_private_mode' => gettext('Password protected by Gallery private mode'),
			'scheduledpublishing' => gettext('Scheduled for publishing'),
			'scheduledpublishing_inactive' => gettext('<strong>Note:</strong> Scheduled publishing is not active unless also set to <em>published</em>'),
			'scheduledexpiration' => gettext('Scheduled for expiration'),
			'scheduledexpiration_inactive' => gettext('<strong>Note:</strong> Scheduled expiration is not active unless also set to <em>published</em>'),
			'expired' => gettext("Unpublished because expired")
	);
}

/**
 * Gets an array with all status notes that apply to $obj currently
 * @since 1.6.1
 * 
 * @param string $obj
 * @return array
 */
function getStatusNotesByContext($obj) {
	$validtables = array('albums', 'images', 'news', 'pages', 'news_categories');
	$notes_context = $notes_context_notices = $notes_context_warnings = array();
	if (in_array($obj->table, $validtables)) {
		$notes = getStatusNotes();
		if (!$obj->isPublished()) {
			$notes_context_notices[] = $notes['unpublished'];
		} else if ($obj->isUnpublishedByParent()) {
			$notes_context_notices[] = $notes['unpublished_by_parent'];
		}
		if ($obj->isProtected() && GALLERY_SECURITY == 'public') {
			$notes_context_notices[] = $notes['protected'];
		} else if ($obj->isProtectedByParent() && GALLERY_SECURITY == 'public') {
			$notes_context_notices[] = $notes['protected_by_parent'];
		} else if (GALLERY_SECURITY != 'public') {
			$notes_context_notices[] = $notes['protected_by_site_private_mode'];
		}
		if ($obj->hasPublishSchedule()) {
			$notes_context_notices[] = $notes['scheduledpublishing'];
		}
		if ($obj->hasInactivePublishSchedule()) {
			$notes_context_warnings[] = $notes['scheduledpublishing_inactive'];
		}
		if ($obj->hasExpiration()) {
			$notes_context_notices[] = $notes['scheduledexpiration'];
		}
		if ($obj->hasInactiveExpiration()) {
			$notes_context_warnings[] = $notes['scheduledexpiration_inactive'];
		}
		if ($obj->hasExpired()) {
			$notes_context_notices[] = $notes['expired'];
		}
		$notices = $warnings = '';
		if(!empty($notes_context_notices)) {
			$notices = '<p class="notebox">' . implode(' | ', $notes_context_notices) . '</p>';
		}
		if(!empty($notes_context_warnings)) {
			$warnings = '<p class="warningbox">' . implode(' | ', $notes_context_warnings) . '</p>';
		}
		$notes_context = array($warnings, $notices);
	}
	return $notes_context;
}

