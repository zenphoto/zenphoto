<?php
/**
 * Allows an under-priviledged user to create a root level album. This album is then
 * assigned in the users managed albums list.
 *
 * The User interface appears on the user tab in the <i>custom data</i> area when an enabled user is logged in.
 * Candidate users must have <var>Album</var> and <var>Upload</var> rights. Users with <var>Admin</var> right or <var>Manage all album</var> rights
 * can already make root level albums, so are excluded from this plugin.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN;
$plugin_description = gettext('Allow a user to create a root level album when he does not otherwise have rights to do so.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'create_album';

zp_register_filter('admin_head', 'create_albumJS');
zp_register_filter('edit_admin_custom_data', 'create_album_edit', 1);
zp_register_filter('save_admin_custom_data', 'create_album_save');
zp_register_filter('save_user', 'create_album_save_user');
zp_register_filter('upload_root_ui', 'create_album_upload_root_ui');
zp_register_filter('admin_upload_process', 'create_album_admin_upload_process');
zp_register_filter('plugin_tabs', 'create_album::tab');

class create_album {

	var $admins = array();

	/**
	 * class instantiation function
	 */
	function __construct() {
		setOptionDefault('create_album_default', 1);
		$default = getOption('create_album_default');
		global $_zp_authority;
		$admins = $_zp_authority->getAdministrators();
		foreach ($admins as $admin) {
			$rights = $admin['rights'];
			if (($rights & (ALBUM_RIGHTS | UPLOAD_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) == (ALBUM_RIGHTS | UPLOAD_RIGHTS)) {
				$this->admins[$admin['user']] = 'create_album_admin_' . $admin['user'];
				setOptionDefault('create_album_admin_' . $admin['user'], $default);
			}
		}
	}

	/**
	 * Option definitions
	 */
	function getOptionsSupported() {
		return array(gettext('Default') => array('key'	 => 'create_album_default', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Default new users to "allowed"')),
						gettext('Users')	 => array('key'				 => 'create_album_admin_', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										'checkboxes' => $this->admins,
										'desc'			 => gettext('Checked users will be allowed to create root level albums.') .
										'<p class="notebox">' . gettext('<strong>Note:</strong> Candidates are those users with <em>Album</em> and <em>Upload</em> rights who do not also have <em>Admin</em> or <em>Manage all album</em> rights.') . '</p>')
		);
	}

	static function tab($xlate) {
		$xlate['demo'] = gettext('demo');
		return $xlate;
	}

}

/**
 * HTML Header JS
 */
function create_albumJS() {
	global $_zp_admin_tab, $_zp_admin_subtab, $_zp_gallery;
	if ($_zp_admin_tab == 'users') {
		$albums = $_zp_gallery->getAlbums(0);
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
		<?php seoFriendlyJS(); ?>
			var albumArray = ['<?php echo implode("','", $albums) ?>'];

			function updateFolder(nameObj, folderID, checkboxID, msg1) {
				var autogen = document.getElementById(checkboxID).checked;
				var folder = document.getElementById(folderID);
				var name = nameObj.value;
				var fname = "";
				var fnamesuffix = "";
				var count = 1;
				var errorDiv = document.getElementById("foldererror");
				if (autogen && name != "") {
					fname = seoFriendlyJS(name);
					while (contains(albumArray, fname + fnamesuffix)) {
						fnamesuffix = "-" + count;
						count++;
					}
				}
				folder.value = fname + fnamesuffix;
				$('#newalbumcheckbox').prop('checked', true);
				if (contains(albumArray, folder)) {
					errorDiv.style.display = "inline";
					errorDiv.innerHTML = msg1;
					$('#newalbumcheckbox').prop('checked', false);
				} else {
					errorDiv.style.display = "none";
					$('#newalbumcheckbox').prop('checked', true);
				}
			}

			function albumSelect() {
				var errorDiv = document.getElementById("foldererror");
				if (contains(albumArray, $('#folderdisplay').val())) {
					errorDiv.style.display = "inline";
					errorDiv.innerHTML = '<?php echo gettext('That name is already used.'); ?>';
					$('#newalbumcheckbox').prop('checked', false);
				} else {
					errorDiv.style.display = "none";
					$('#newalbumcheckbox').prop('checked', true);
				}
			}
			// ]]> -->
		</script>
		<?php
	}
}

/**
 *
 * Admin custom data HTML
 * @param $html
 * @param $userobj
 * @param $id
 * @param $background
 * @param $current
 * @param $local_alterrights
 */
function create_album_edit($html, $userobj, $id, $background, $current, $local_alterrights) {
	global $_zp_current_admin_obj, $_zp_gallery;
	if (!$userobj->getValid())
		return $html;
	$rights = $userobj->getRights();
	$user = $userobj->getUser();
	$enabled = getOption('create_album_admin_' . $user);
	if (is_null($enabled)) {
		$enabled = getOption('create_album_default');
	}
	if ($enabled && ($user == $_zp_current_admin_obj->getUser()) && ($rights & (ALBUM_RIGHTS | UPLOAD_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) == (ALBUM_RIGHTS | UPLOAD_RIGHTS)) {
		if ($albpublish = $_zp_gallery->getAlbumPublish()) {
			$publishchecked = ' checked="checked"';
		} else {
			$publishchecked = '';
		}
		ob_start();
		?>
		<tr <?php if (!$current) echo 'style="display:none;"'; ?> class="userextrainfo">
			<td colspan="2"  style="background-color:#ECF1F2;" valign="top">
				<div id="newalbumbox" style="margin-top: 5px;">
					<input id="newalbumcheckbox" type="checkbox" name="createalbum" />
					<?php echo gettext('Create an album') ?>
				</div>
				<div id="albumtext" style="margin-top: 5px;"><?php echo gettext("titled:"); ?>
					<input id="albumtitle" size="42" type="text" name="albumtitle"
								 onkeyup="updateFolder(this, 'folderdisplay', 'autogen', '<?php echo addslashes(gettext('That name is already used.')); ?>', '<?php echo addslashes(gettext('This upload has to have a folder. Type a title or folder name to continue...')); ?>');" />

					<div>
						<?php echo gettext("with the folder name:"); ?>
						<input type="text" name="folderdisplay" id="folderdisplay" size="18" disabled="disabled" onkeyup="albumSelect();" />
						<span id="foldererror" style="display: none; color: #D66;"></span>
						<input type="checkbox" name="autogenfolder" id="autogen" checked="checked"
									 onclick="toggleAutogen('folderdisplay', 'albumtitle', this);" />
						<label for="autogen"><?php echo gettext("Auto-generate"); ?></label>
					</div>
					<div id="publishtext">
						<input type="checkbox" name="publishalbum" id="publishalbum" value="1" <?php echo $publishchecked; ?> />
						<label for="publishalbum"><?php echo gettext("Publish the album so everyone can see it."); ?></label>
					</div>
				</div>
			</td>
		</tr>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
	}
	return $html;
}

/**
 *
 * Admin Save handler
 * @param $updated
 * @param $userobj
 * @param $i
 * @param $alter
 */
function create_album_save($updated, $userobj, $i, $alter) {
	global $_create_album_errors;
	if (isset($_POST['createalbum']) && $userobj->getValid()) {
		if (isset($_POST['folderdisplay'])) {
			$alb = sanitize($_POST['folderdisplay']);
		} else {
			$alb = sanitize($_POST['albumtitle']);
		}
		if ($alb) {
			$user = $userobj->getUser();
			if (basename($alb) != $alb) { //	error, must be root album
				$_create_album_errors[$user] = sprintf(gettext('%s is not a root level folder.'), $alb);
			} else {
				$path = ALBUM_FOLDER_SERVERPATH . $alb;
				if (file_exists($path)) {
					$_create_album_errors[$user] = sprintf(gettext('Folder %s already exists.'), $alb);
				} else {
					if (@mkdir_recursive($path, FOLDER_MOD)) {
						$album = newAlbum($alb);
						$title = sanitize($_POST['albumtitle'], 2);
						if (!empty($title)) {
							$album->setTitle($title);
						}
						if (!isset($_POST['publishalbum'])) {
							$album->setShow(false);
						}
						$album->save();
						$userobj->setObjects(array_merge($userobj->getObjects(), array('data' => $alb, 'name' => $title, 'edit' => MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW)));
						$userobj->save();
					} else {
						$_create_album_errors[$user] = sprintf(gettext('Unable to create %s.'), $alb);
					}
				}
			}
		}
	}
	return $updated;
}

/**
 *
 * Handle errors on save, Admin Deletes
 * @param $msg
 * @param $userobj
 * @param $what
 */
function create_album_save_user($msg, $userobj, $what) {
	global $_create_album_errors;
	if (is_array($_create_album_errors) && array_key_exists($userobj->getUser(), $_create_album_errors)) {
		$msg .= ($msg) ? '; ' : '' . $_create_album_errors[$userobj->getUser()];
	}
	if ($what == 'delete') {
		query('DELETE FROM ' . prefix('options') . ' WHERE `name`="create_album_admin_' . $userobj->getUser() . '"');
	}
	return $msg;
}

function create_album_upload_root_ui($allow) {
	if (!$allow) {
		global $_zp_current_admin_obj;
		$rights = $_zp_current_admin_obj->getRights();
		$enabled = getOption('create_album_admin_' . $_zp_current_admin_obj->getUser());
		if (is_null($enabled)) { // a new user
			if (($rights & (ALBUM_RIGHTS | UPLOAD_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) == (ALBUM_RIGHTS | UPLOAD_RIGHTS)) {
				return getOption('create_album_default');
			}
		}
		return $enabled;
	}
	return $allow;
}

function create_album_admin_upload_process($folder) {
	global $_zp_current_admin_obj;
	if (create_album_upload_root_ui(true)) { //	user has permission to create a root album
		$leaves = explode('/', $folder);
		if (count($leaves) == 1) { //	// and it is a root album
			$targetPath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($folder);
			if (!file_exists($targetPath)) { //	and we do need to create it
				mkdir_recursive($targetPath, FOLDER_MOD);
				$album = newAlbum($folder);
				$album->save();
				$userobj->setObjects(array_merge($userobj->getObjects(), array('data' => $folder, 'name' => $album->getTitle(), 'edit' => MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW)));
				$userobj->save();
			}
		}
	}
	return $folder;
}
?>
