<?php
/**
 * A quota management system to limit the sum of sizes of uploaded images.
 *
 * Set the default quota on the plugin options page.
 * You can change the quota for individual users on the Admin tab. Users with ADMIN_RIGHTS or MANAGE_ALL_ALBUM_RIGHTS
 * are not subject to quotas and will not be assigned ownership of an image.
 *
 * Images uploaded by a user will be marked as his and will count toward his quota.
 * Images uploaded via FTP will not necessarily have an owner assigned. If there is
 * one assiged, it is the logged on user when the image is discovered by Zenphoto.
 *
 * You may also assign the complete set of images in an albums to a user. (Just the images in the
 * album. If you want to assign images from subalbums, you need to do that for each
 * subalbum.)
 *
 * A user who exceeds his quota will not be allowed to upload files.
 *
 * Because of the difficulty of policing quotas when ZIP files are uploaded this plugin
 * has an option to diable ZIP file upload.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides a quota management system to limit the sum of sizes of images a user uploads.")."<p class='notebox'>".gettext("<strong>Note:</strong> if FTP is used to upload images, manual user assignment is necessary. ZIP file upload is disabled as quotas are not applied to the files contained therein.")."</p>";
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'quota_management';

zp_register_filter('save_admin_custom_data', 'quota_management::management::save_admin');
zp_register_filter('edit_admin_custom_data', 'quota_management::edit_admin');
zp_register_filter('new_image', 'quota_management::new_image');
zp_register_filter('image_refresh', 'quota_management::image_refresh');
zp_register_filter('check_upload_quota', 'quota_management::checkQuota');
zp_register_filter('get_upload_limit', 'quota_management::getUploadLimit');
zp_register_filter('get_upload_header_text', 'quota_management::get_header');
zp_register_filter('upload_filetypes', 'quota_management::upload_filetypes');
zp_register_filter('upload_helper_js', 'quota_management::upload_helper_js');

/**
 * Option handler class
 *
 */
class quota_management {
	/**
	 * class instantiation function
	 *
	 * @return filter_zenphoto_seo
	 */
	function __construct() {
		setOptionDefault('quota_management::default', 250000);
		setOptionDefault('quota_management::allowZIP', 1);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Default quota') => array('key' => 'quota_management::default', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('Default size limit in kilobytes.')),
									gettext('Allow ZIP files') => array('key' => 'quota_management::allowZIP', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('The size of a ZIP file may be slightly smaller than the sum of the <em>image</em> files it contains. Un-check this box if you wish to disable uploading of ZIP files.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

	/**
	 * Saves admin custom data
	 * Called when an admin is saved
	 *
	 * @param string $updated true if data has changed
	 * @param object $userobj admin user object
	 * @param string $i prefix for the admin
	 * @param bool $alter will be true if critical admin data may be altered
	 * @return bool
	 */
	static function save_admin($updated, $userobj, $i, $alter) {
		if (isset($_POST[$i.'quota']) && $alter) {
			$oldquota = $userobj->getQuota();
			$userobj->setQuota(sanitize_numeric($_POST[$i.'quota']));
			$updated = $oldquota != $userobj->getQuota();
		}
		return $updated;
	}

	/**
	 * Returns table row(s) for edit of an admin user's custom data
	 *
	 * @param string $html always empty
	 * @param $userobj Admin user object
	 * @param string $i prefix for the admin
	 * @param string $background background color for the admin row
	 * @param bool $current true if this admin row is the logged in admin
	 * @return string
	 */
	static function edit_admin($html, $userobj, $i, $background, $current, $local_alterrights) {
		if ($userobj->getRights() & (ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) return $html;
		if (!($userobj->getRights() & UPLOAD_RIGHTS)) return $html;
		$quota = $userobj->getQuota();
		$used = quota_management::getCurrentUse($userobj);
		if ($quota == NULL) $quota = getOption('quota_management::default');
		$result =
			'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
				<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("Quota:").'</td>
				<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" width="345">'.
					sprintf(gettext('Allowed: %s kb'),'<input type="text" size="10" name="'.$i.'quota" value="'.$quota.'" '.$local_alterrights.' />').' '.
					sprintf(gettext('(%s kb used)'),number_format($used)).
					"\n".
				'</td>
				<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">'.gettext('Image quota information.').'</td>
			</tr>'."\n";
		return $html.$result;
	}

	/**
	 * Returns current image useage
	 * @param $userobj Admin user object
	 * @return int
	 */
	static function getCurrentUse($userobj) {
		global $_zp_current_admin_obj;
		if (is_null($userobj)) {
			$userobj = $_zp_current_admin_obj;
		}
		$sql = 'SELECT sum(`filesize`) FROM '.prefix('images').' WHERE `owner`="'.$userobj->getUser().'"';
		$result = query_single_row($sql);
		return array_shift($result)/1024;
	}

	 /**
	 * Assigns owner to new image
	 * @param string $image
	 * @return object
	 */
	static function new_image($image) {
		global $_zp_current_admin_obj;
		if (is_object($_zp_current_admin_obj)) {
			$image->set('owner',$_zp_current_admin_obj->getUser());
		}
		$image->set('filesize',filesize($image->localpath));
		$image->save();
		return $image;
	}

	/**
	 * checks to see if the filesize is set and sets it if not
	 * @param unknown_type $image
	 * @return object
	 */
	static function image_refresh($image) {
		$image->set('filesize',filesize($image->localpath));
		$image->save();
		return $image;
	}

	/**
	 * Returns the user's quota
	 * @param int $quota
	 * @return int
	 */
	static function getUploadQuota($quota) {
		global $_zp_current_admin_obj;
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$quota = -1;
		} else {
			$quota = $_zp_current_admin_obj->getQuota();
			if ($quota == NULL) $quota = getOption('quota_management::default');
		}
		return $quota;
	}

	/**
	 * Returns the upload limit
	 * @param int $uploadlimit
	 * @return int
	 */
	static function getUploadLimit($uploadlimit) {
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$uploadlimit = (quota_management::getUploadQuota(0)-quota_management::getCurrentUse(NULL))*1024;
		}
		return $uploadlimit;
	}

	/**
	 * Checks if upload should be allowed
	 * @param int $error
	 * @param string $image
	 * @return int
	 */
	static function checkQuota($error, $image) {
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			return UPLOAD_ERR_OK;
		}
		if (getSuffix($image) == 'zip') {
			return UPLOAD_ERR_EXTENSION;
		}
		$quota = quota_management::getUploadQuota(0);
		$size = round(filesize($image)/1024);
		if ($quota > 0) {
			if (quota_management::getCurrentUse(NULL) + $size > $quota) {
				$error = UPLOAD_ERR_QUOTA;
				break;
			}
		}
		return $error;
	}

	/**
	 * Returns quota text for header, etc.
	 * @param string $default
	 * @return string
	 */
	static function get_header($default) {
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			return $default;
		}
		$uploadlimit = quota_management::getUploadLimit(0);
		if ($uploadlimit <= 1024) {
			$color = 'style="color:red;"';
			$warn = ' <span style="color:red;">'.gettext('Uploading is disabled.').'</span>';
		} else {
			$color = '';
			$warn = '';
		}
		return sprintf(gettext('Your available upload quota is <span %1$s>%2$s</span> kb.'),$color,number_format(round($uploadlimit/1024))).$warn;
	}

	/**
	 * Returns Javascript needed to support quota system
	 * @param string $defaultJS
	 * @return string
	 */
	static function upload_helper_js($defaultJS) {
		$quota = quota_management::getUploadLimit(99999);
		$quotaOK = $quota < 0 || $quota > 1024;
		if ($quotaOK) {
			$quota_management_js = '';
		} else {
			$quota_management_js = "
				$(document).ready(function() {
					$('#albumselect').hide();
				});";
		}
		return $quota_management_js.$defaultJS;
	}

	/**
	 * Removes ZIP from list of upload suffixes
	 * @param array $types
	 * @return array
	 */
	static function upload_filetypes($types) {
		if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS) || (getoption('quota_management::allowZIP'))) {
			return $types;
		}
		$key = array_search('ZIP', $types);
		if ($key !== false) unset($types[$key]);
		return $types;
	}

}
?>