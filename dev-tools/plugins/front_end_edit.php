<?php
/* provides [very unsafe] editing on the Zenphoto front end..
 *
 * @package plugins
 */
$plugin_is_filter = 5|THEME_PLUGIN;
$plugin_description = gettext('Provides front-end editing capability. <strong>Note:</strong> this is not secure!');
$plugin_author = "Stephen Billard (sbillard)";

if (zp_loggedin()) {
	zp_register_filter('printObjectField', 'front_end_edit_editor');
	zp_register_filter('theme_head', 'front_end_edit_head');
	if ( !empty($_POST["eip_context"] ) &&  !empty($_POST["eip_field"] ) ) {
		/*** Server-side AJAX Handling ***********
		 ******************************************/
		editInPlace_handle_request($_POST["eip_context"], $_POST["eip_field"], $_POST["new_value"], $_POST["orig_value"]);
	}
}


function front_end_edit_head() {
	global $_zp_current_album;
	//Note: this allows more users to edit the album than should be allowed. It is left to the exercise
	//of the user to improve this. Remember, it is not secure anyway!
	if (($rights = zp_loggedin()) & (ADMIN_RIGHTS | ALBUM_RIGHTS)) {
		if (in_context(ZP_ALBUM)) {
			$grant = $_zp_current_album->isMyItem(ALBUM_RIGHTS);
		} else {
			$grant = $rights & ADMIN_RIGHTS;
		}
		if ($grant) {
			?>
			<script type="text/javascript" src="<?php echo WEBPATH . "/" . USER_PLUGIN_FOLDER; ?>/front_end_edit/jquery.editinplace.js"></script>
			<script type="text/javascript">
				// <!-- <![CDATA[
				var zpstrings = {
					/* Used in jquery.editinplace.js */
					'Save' : "<?php echo gettext('Save'); ?>",
					'Cancel' : "<?php echo gettext('Cancel'); ?>",
					'Saving' : "<?php echo gettext('Saving'); ?>",
					'ClickToEdit' : "<?php echo gettext('Click to edit...'); ?>"
				};
				// ]]> -->
			</script>
			<?php
		}
	}
}

function front_end_edit_editor($html, $object, $context, $field) {
	static $id = 1;
	$id++;

	$message = array(	'title'=>gettext('(No title...)'),
										'date'=>gettext('(No date...)'),
										'location'=>gettext('(No Location...)'),
										'desc'=>gettext('(No description...)')
										);
	if (array_key_exists($field, $message)) {
		$messageIfEmpty = $message[$field];
	} else {
		$messageIfEmpty = gettext('(No data...)');
	}

	$q = strpos($html, '>');
	$r = strpos($html, '</span>');
	$text = substr($html, $q+1, $r-$q-1);
	if (empty($text)) {
		$text = $messageIfEmpty;
	}
	$class= 'class="' . "zp_editable zp_editable_{$context}_{$field}" . '"';

	$html = "<span id=\"editable_{$context}_$id\" $class>" . $text . "</span>\n".
					"<script type=\"text/javascript\">editInPlace('editable_{$context}_$id', '$context', '$field');</script>";

	return $html;
}

function editInPlace_handle_request($context = '', $field = '', $value = '', $orig_value = '') {
	// Cannot edit when context not set in current page (should happen only when editing in place from index.php page)
	if ( !in_context(ZP_IMAGE) && !in_context(ZP_ALBUM) && !in_context(ZP_ZENPAGE_PAGE) && !in_context(ZP_ZENPAGE_NEWS_ARTICLE))
	die ($orig_value.'<script type="text/javascript">alert("'.gettext('Oops.. Cannot edit from this page').'");</script>');

	// Make a copy of context object
	switch ($context) {
		case 'image':
			global $_zp_current_image;
			$object = $_zp_current_image;
			break;
		case 'album':
			global $_zp_current_album;
			$object = $_zp_current_album;
			break;
		case 'zenpage_page':
			global $_zp_current_zenpage_page;
			$object = $_zp_current_zenpage_page;
			break;
		case 'zenpage_news':
			global $_zp_current_zenpage_news;
			$object = $_zp_current_zenpage_news;
			break;
		default:
			die (gettext('Error: malformed Ajax POST'));
	}

	// Dates need to be handled before stored
	if ($field == 'date') {
		$value = date('Y-m-d H:i:s', strtotime($value));
	}

	// Sanitize new value
	switch ($field) {
		case 'desc':
			$level = 1;
			break;
		case 'title':
			$level = 2;
			break;
		default:
			$level = 3;
	}
	$value = str_replace("\n", '<br />', sanitize($value, $level)); // note: not using nl2br() here because it adds an extra "\n"

	// Write new value
	if ($field == '_update_tags') {
		$value = trim($value, ', ');
		$object->setTags($value);
	} else {
		$object->set($field, $value);
	}

	$result = $object->save();
	if ($result !== false) {
		echo $value;
	} else {
		echo ('<script type="text/javascript">alert("'.gettext('Could not save!').'");</script>'.$orig_value);
	}
	die();
}


?>