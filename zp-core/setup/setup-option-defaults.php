<?php
// force UTF-8 Ø

/**
 * stores all the default values for options
 * @package zpcore\setup
 */
setup::Log(gettext('Set Zenphoto default options'), true);

require(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/security-logger.php');
zp_apply_filter('log_setup', true, 'install', '');

/* fix for NULL theme name */
$active = getOptionList();
$sql = "SELECT * FROM " . $_zp_db->prefix('options') . ' WHERE `theme` IS NULL';
$optionlist = $_zp_db->queryFullArray($sql);
if ($optionlist) {
	foreach ($optionlist as $option) {
		$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `id`=' . $option['id']);
		setOption($option['name'], $active[$option['name']]);
	}
}
$lib_auth_extratext = "";
$salt = 'abcdefghijklmnopqursuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()_+-={}[]|;,.<>?/';
$list = range(0, strlen($salt) - 1);
shuffle($list);
for ($i = 0; $i < 30; $i++) {
	$lib_auth_extratext = $lib_auth_extratext . $salt[$list[$i]];
}

purgeOption('zenphoto_release');
purgeOption('zenphoto_version');
purgeOption('zenphoto_install');
setOption('zenphoto_install', serialize(installSignature()));
setOptionDefault('setup_unprotected_by_adminrequest', 0);

if (Authority::$preferred_version > ($oldv = getOption('libauth_version'))) {
	if (empty($oldv)) {
		//	The password hash of these old versions did not have the extra text.
		//	Note: if the administrators table is empty we will re-do this option with the good stuff.
		purgeOption('extra_auth_hash_text');
		setOptionDefault('extra_auth_hash_text', '');
	}
	$msg = sprintf(gettext('Migrating lib-auth data version %1$s => version %2$s'), $oldv, Authority::$preferred_version);
	if (!$_zp_authority->migrateAuth(Authority::$preferred_version)) {
		$msg .= ': ' . gettext('failed');
	}
	echo $msg;
	setup::Log($msg, true);
}
$admins = $_zp_authority->getAdministrators('all');

if (empty($admins)) { //	empty administrators table
	$groupsdefined = NULL;
	if (function_exists('hash')) {
		setOption('strong_hash', 3);
	} else {
		setOption('strong_hash', 1);
	}
	purgeOption('extra_auth_hash_text');
} else {
	if (function_exists('hash') && getOption('strong_hash') == 2) {
		setOption('strong_hash', 3);
	}
	$groupsdefined = @unserialize(getOption('defined_groups'));
}
setOptionDefault('extra_auth_hash_text', $lib_auth_extratext);
setOptionDefault('password_strength', 10);
setOptionDefault('min_password_lenght', 6);
setOptionDefault('user_album_edit_default', 1);

// old configuration opitons. preserve them
$conf = $_zp_conf_vars;
setOptionDefault('time_offset', 0);
setOption('mod_rewrite_detected', 0);
if (isset($_GET['mod_rewrite'])) {
	if (!function_exists('curl_init')) {
		?>
		<script>
			$(function() {
				$('img').error(function() {
					var link = $(this).attr('src');
					var title = $(this).attr('title');
					$(this).parent().html('<a href="' + link + '"><img src="../images/fail.png" title="' + title + '"></a>');
					imageErr = true;
				});
			});
		</script>
	<?php } ?>
	<p><?php echo gettext('Mod_Rewrite check:'); ?></p>
	<?php setup::defaultOptionsRequest(gettext('Mod_rewrite'), 'modrewrite'); ?>
	<?php
}

if (isset($_POST['setUTF8URI']) && $_POST['setUTF8URI'] != 'dont') {
	setOption('UTF8_image_URI', (int) ($_POST['setUTF8URI'] == 'true'));
}
setOptionDefault('server_protocol', "http");
if (getOption('server_protocol') == 'https_admin') {
	setOption('server_protocol', 'https');
}
setOptionDefault('charset', "UTF-8");
setOptionDefault('filesystem_charset', "UTF-8");
setOptionDefault('image_quality', 85);
setOptionDefault('thumb_quality', 75);

setOptionDefault('search_password', '');
setOptionDefault('search_hint', NULL);

if (getOption('perform_watermark')) {
	$v = str_replace('.png', "", basename(getOption('watermark_image')));
	setoptionDefault('fullimage_watermark', $v);
}

setOptionDefault('watermark_h_offset', 90);
setOptionDefault('watermark_w_offset', 90);
setOptionDefault('watermark_scale', 5);
purgeOption('watermark_allow_upscale');
setOptionDefault('perform_video_watermark', 0);

if (getOption('perform_video_watermark')) {
	$v = str_replace('.png', "", basename(getOption('video_watermark_image')));
	setoptionDefault('Video_watermark', $v);
}

setOptionDefault('image_sorttype', 'Filename');
setOptionDefault('image_sortdirection', '0');
setOptionDefault('hotlink_protection', '1');

setOptionDefault('search_fields', 'title,desc,tags,file,location,city,state,country,content,author');

$style_tags = "abbr =>(class=>() id=>() title =>() lang=>())\n" .
				"acronym =>(class=>() id=>() title =>() lang=>())\n" .
				"b => (class=>() id=>() lang=>())\n" .
				"blockquote =>(class=>() id=>() cite =>() lang=>())\n" .
				"br => (class=>() id=>())\n" .
				"code => (class=>() id=>() lang=>())\n" .
				"em => (class=>() id=>() lang=>())\n" .
				"i => (class=>() id=>() lang=>())\n" .
				"strike => (class=>() id=>() lang=>())\n" .
				"strong => (class=>() id=>() lang=>())\n" .
				"sup => (class=>() id=>() lang=>())\n" .
				"sub => (class=>() id=>() lang=>())\n" .
				"del => (class=>() id=>() lang=>())\n";

$general_tags = "a => (href =>() title =>() target=>() class=>() id=>() rel=>() lang=>())\n" .
				"ul =>(class=>() id=>() lang=>())\n" .
				"ol =>(class=>() id=>() lang=>())\n" .
				"li =>(class=>() id=>() lang=>())\n" .
				"dl =>(class=>() id=>() lang=>())\n" .
				"dt =>(class=>() id=>() lang=>())\n" .
				"dd =>(class=>() id=>() lang=>())\n" .
				"p => (class=>() id=>() style=>() lang=>())\n" .
				"h1=>(class=>() id=>() style=>() lang=>())\n" .
				"h2=>(class=>() id=>() style=>() lang=>())\n" .
				"h3=>(class=>() id=>() style=>() lang=>())\n" .
				"h4=>(class=>() id=>() style=>() lang=>())\n" .
				"h5=>(class=>() id=>() style=>() lang=>())\n" .
				"h6=>(class=>() id=>() style=>() lang=>())\n" .
				"pre=>(class=>() id=>() style=>() lang=>())\n" .
				"address=>(class=>() id=>() style=>() lang=>())\n" .
				"span=>(class=>() id=>() style=>() lang=>())\n" .
				"div=>(class=>() id=>() style=>() lang=>())\n" .
				"img=>(class=>() id=>() style=>() src=>() title=>() alt=>() width=>() height=>() sizes=>() srcset=>() loading=>() lang=>())\n" .
				"iframe=>(class=>() id=>() style=>() src=>() title=>() width=>() height=>() loading=>() lang=>())\n" .
				"figure=>(class=>() id=>() style=>() lang=>())\n" .
				"figcaption=>(class=>() id=>() style=>() lang=>())\n" .
				"article=>(class=>() id=>() style=>() lang=>())\n" .
				"section => (class=>() id=>() style=>() lang=>())\n" .
				"nav => (class=>() id=>() style=>() lang=>())\n" .
				"video => (class=>() id=>() style=>() src=>() controls=>() autoplay=>() buffered=>() height=>() width=>() loop=>() muted=>() preload=>() poster=>() lang=>())\n" .
				"audio => (class=>() id=>() style=>() src=>() controls=>() autoplay=>() buffered=>() height=>() width=>() loop=>() muted=>() preload=>() volume=>() lang=>())\n" .
				"picture=>(class=>() id=>() lang=>())\n" .
				"source=>(src=>() scrset=>() size=>() type=>() media=>() lang=>())\n" .
				"track=>(src=>() kind=>() srclang=>() label=>() default=>() lang=>())\n" .
				"table => (class=>() id=>() lang=>())\n" .
				"caption => (class=>() id=>() lang=>())\n" .
				"th => (class=>() id=>() lang=>())\n" .
				"tr => (class=>() id=>() lang=>())\n" .
				"td => (class=>() id=>() colspan=>() lang=>())\n" .
				"thead => (class=>() id=>() lang=>())\n" .
				"tbody => (class=>() id=>() lang=>())\n" .
				"tfoot => (class=>() id=>() lang=>())\n" .
				"colgroup => (class=>() id=>() lang=>())\n" .
				"col => (class=>() id=>() lang=>())\n" .
				"form => (action=>() method=>() accept-charset=>() id=>() class=>() title=>() name=>() target=>() lang=>())\n";

setOption('allowed_tags_default', $style_tags . $general_tags);
setOptionDefault('allowed_tags', $style_tags . $general_tags);
setOptionDefault('style_tags', strtolower($style_tags));
setOptionDefault('daily_logs', 0);

setOptionDefault('full_image_quality', 75);

$protectfullimage = getOption('protect_full_image');
//Update outdated values
switch($protectfullimage) {
	default: // option not set yet
		$protection = false;
		break;
	case 'Protected view':
	case '1': // outdated legady value
		$protection = 'protected';
		break;
	case 'Unprotected':
	case '0': // outdated legady value
		$protection = 'unprotected';
		break;
	case 'No access':
		$protection = 'no-access';
		break;
	case 'Download':
		$protection = 'download';
		break;
}
if ($protection) {
	if (getOption('full_image_download')) { // outdated legady option
		$protection = 'download';
	}
	setOption('protect_full_image', $protection);
} else {
	setOptionDefault('protect_full_image', 'protected');
}

setOptionDefault('locale', '');
setOptionDefault('date_format', 'Y-m-d');
setOptionDefault('date_format_localized', 0);

setOptionDefault('use_lock_image', 1);
setOptionDefault('search_user', '');
setOptionDefault('multi_lingual', 0);
setOptionDefault('tagsort', 0);
setOptionDefault('albumimagesort', 'ID');
setOptionDefault('albumimagedirection', 'DESC');
setOptionDefault('albumimagesort_status', 'all');
setOptionDefault('cache_full_image', 0);
setOptionDefault('custom_index_page', '');
setOptionDefault('picture_of_the_day', serialize(array('day' => NULL, 'folder' => NULL, 'filename' => NULL)));
setOptionDefault('exact_tag_match', 0);

setOptionDefault('image_max_size', 3000);
setOptionDefault('EXIFMake', 1);
setOptionDefault('EXIFModel', 1);
setOptionDefault('EXIFExposureTime', 1);
setOptionDefault('EXIFFNumber', 1);
setOptionDefault('EXIFFocalLength', 1);
setOptionDefault('EXIFISOSpeedRatings', 1);
setOptionDefault('EXIFDateTimeOriginal', 1);
setOptionDefault('EXIFExposureBiasValue', 1);
setOptionDefault('EXIFMeteringMode', 1);
setOptionDefault('EXIFFlash', 1);
foreach ($_zp_exifvars as $key => $item) {
	setOptionDefault($key, 0);
}
setOptionDefault('IPTC_encoding', 'ISO-8859-1');
setOptionDefault('IPTC_convert_linebreaks', 0);
renameOption('ImbedIPTC', 'EmbedIPTC');

setOptionDefault('UTF8_image_URI', 0);

setOptionDefault('sharpen_amount', 40);
setOptionDefault('sharpen_radius', 0.5);
setOptionDefault('sharpen_threshold', 3);

setOptionDefault('search_space_is_or', 0);
setOptionDefault('search_no_albums', 0);
setOptionDefault('search_fieldsselector_enabled', 1);

//	 update group descriptions location
$admins = $_zp_authority->getAdministrators('groups');
foreach ($admins as $group) {
	if (is_null($group['other_credentials'])) {
		$sql = 'UPDATE ' . $_zp_db->prefix('administrators') . ' SET `custom_data` = NULL, `other_credentials`=' . $_zp_db->quote($group['custom_data']) . ' WHERE `id`=' . $group['id'];
		$_zp_db->query($sql);
	}
}

// default groups
if (!is_array($groupsdefined)) {
	$groupsdefined = array();
}
if (!in_array('administrators', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('administrators', 0);
	$groupobj->setName('group');
	$groupobj->setRights(ALL_RIGHTS);
	$groupobj->set('other_credentials', gettext('Users with full privileges'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'administrators';
}
if (!in_array('viewers', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('viewers', 0);
	$groupobj->setName('group');
	$groupobj->setRights(NO_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS);
	$groupobj->set('other_credentials', gettext('Users allowed only to view zenphoto objects'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'viewers';
}
if (!in_array('blocked', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('blocked', 0);
	$groupobj->setName('group');
	$groupobj->setRights(0);
	$groupobj->set('other_credentials', gettext('Banned users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'blocked';
}
if (!in_array('album managers', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('album managers', 0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS | OVERVIEW_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS | UPLOAD_RIGHTS | COMMENT_RIGHTS | ALBUM_RIGHTS | THEMES_RIGHTS);
	$groupobj->set('other_credentials', gettext('Managers of one or more albums'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'album managers';
}
if (!in_array('default', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('default', 0);
	$groupobj->setName('template');
	$groupobj->setRights(DEFAULT_RIGHTS);
	$groupobj->set('other_credentials', gettext('Default user settings'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'default';
}
if (!in_array('newuser', $groupsdefined)) {
	$groupobj = Authority::newAdministrator('newuser', 0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS);
	$groupobj->set('other_credentials', gettext('Newly registered and verified users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'newuser';
}
setOption('defined_groups', serialize($groupsdefined)); // record that these have been set once (and never again)

setOptionDefault('AlbumThumbSelect', 1);
purgeOption('AlbumThumbSelectField');
purgeOption('AlbumThumbSelectDirection');

setOptionDefault('site_email', "zenphoto@" . $_SERVER['SERVER_NAME']);
setOptionDefault('site_email_name', 'Zenphoto');

if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/Zenphoto.package')) {
	$package = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/Zenphoto.package');
	if (!empty($package)) {
		preg_match_all('|[^/]themes/([^/\r\n]*)|', $package, $matches);
		$themes = array_unique($matches[1]);
		setOption('Zenphoto_theme_list', serialize($themes));
	}
}
?>
<p>
	<?php
	sortArray($themes);
	echo '<p>' . gettext('Setting theme default options') . '</p>';
	foreach (array_keys($_zp_gallery->getThemes()) as $theme) {
		setup::defaultOptionsRequest($theme, 'theme');
	}
	?>
</p>

<?php
// migrate search space is opton
if (getOption('search_space_is_OR')) {
	setOption('search_space_is', '|');
}
$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `name`="search_space_is_OR"', false);

if (!file_exists(SERVERPATH . '/favicon.ico')) {
	@copy(SERVERPATH . '/' . ZENFOLDER . '/images/favicon.ico', SERVERPATH . '/favicon.ico');
}

renameOption('default_copyright', 'copyright_image_notice');
setOptionDefault('copyright_image_notice', sprintf(gettext('Copyright %1$u: %2$s'), date('Y'), $_SERVER["HTTP_HOST"]));

setOptionDefault('fullsizeimage_watermark', getOption('fullimage_watermark'));

$data = getOption('gallery_data');
if ($data) {
	$data = getSerializedArray($data);
	if (isset($data['Gallery_description'])) {
		$data['Gallery_description'] = getSerializedArray($data['Gallery_description']);
	}
	if (isset($data['gallery_title'])) {
		$data['gallery_title'] = getSerializedArray($data['gallery_title']);
	}
	if (isset($data['unprotected_pages'])) {
		$data['unprotected_pages'] = getSerializedArray($data['unprotected_pages']);
	}
} else {
	$data = array();
}

if (!isset($data['gallery_sortdirection'])) {
	$data['gallery_sortdirection'] = (int) getOption('gallery_sortdirection');
}
if (!isset($data['gallery_sorttype'])) {
	$data['gallery_sorttype'] = getOption('gallery_sorttype');
	if (empty($data['gallery_sorttype'])) {
		$data['gallery_sorttype'] = 'ID';
	}
}
if (!isset($data['gallery_title'])) {
	$data['gallery_title'] = getOption('gallery_title');
	if (is_null($data['gallery_title'])) {
		gettext($str = "Gallery");
		$data['gallery_title'] = gettext("Gallery");
	}
}
if (!isset($data['Gallery_description'])) {
	$data['Gallery_description'] = getOption('Gallery_description');
	if (is_null($data['Gallery_description'])) {
		$data['Gallery_description'] = gettext('You can insert your Gallery description on the Admin Options Gallery tab.');
	}
}
if (!isset($data['gallery_password']))
	$data['gallery_password'] = getOption('gallery_password');
if (!isset($data['gallery_user']))
	$data['gallery_user'] = getOption('gallery_user');
if (!isset($data['gallery_hint']))
	$data['gallery_hint'] = getOption('gallery_hint');
if (!isset($data['hitcounter']))
	$data['hitcounter'] = $result = getOption('Page-Hitcounter-index');
if (!isset($data['current_theme'])) {
	$data['current_theme'] = getOption('current_theme');
	if (is_null($data['current_theme'])) {
		$data['current_theme'] = 'basic';
	}
}
if (!isset($data['website_title']))
	$data['website_title'] = getOption('website_title');
if (!isset($data['website_url']))
	$data['website_url'] = getOption('website_url');
if (!isset($data['gallery_security'])) {
	$data['gallery_security'] = getOption('gallery_security');
	if (is_null($data['gallery_security'])) {
		$data['gallery_security'] = 'public';
	}
}
if (!isset($data['login_user_field']))
	$data['login_user_field'] = getOption('login_user_field');
if (!isset($data['album_use_new_image_date']))
	$data['album_use_new_image_date'] = getOption('album_use_new_image_date');
if (!isset($data['thumb_select_images']))
	$data['thumb_select_images'] = getOption('thumb_select_images');
if (!isset($data['unprotected_pages']))
	$data['unprotected_pages'] = getOption('unprotected_pages');
if ($data['unprotected_pages']) {
	$unprotected = $data['unprotected_pages'];
} else {
	setOptionDefault('gallery_page_unprotected_register', 1);
	setOptionDefault('gallery_page_unprotected_contact', 1);
	$unprotected = array();
}
$_zp_options = NULL; // get a fresh start
$optionlist = getOptionList();
foreach ($optionlist as $key => $option) {
	if ($option && strpos($key, 'gallery_page_unprotected_') === 0) {
		$unprotected[] = str_replace('gallery_page_unprotected_', '', $key);
	}
}
if (!isset($data['album_publish'])) {
	$set = getOption('album_default');
	if (is_null($set))
		$set = 1;
	$data['album_publish'] = $set;
}
if (!isset($data['image_publish'])) {
	$set = getOption('image_default');
	if (is_null($set))
		$set = 1;
	$data['image_publish'] = $set;
}
$data['unprotected_pages'] = $unprotected;
setOption('gallery_data', serialize($data));

$_zp_gallery = new Gallery(); // insure we have the proper options instantiated

/*
 *
 * The following options have been relocated in 1.4.7 to methods of the gallery object. They will be purged form installations
 * on the Zenphoto 1.5 release.
 * 
 * these may have been used in third party themes. Themes should cease using these options and instead use the appropriate gallery methods.
 */
$unprotectedpages = $_zp_db->queryFullArray("SELECT name FROM " . $_zp_db->prefix('options') . " WHERE name LIKE 'gallery_page_unprotected_%' ");
if ($unprotectedpages) {
	foreach ($unprotectedpages as $unprotectedpage) {
		purgeOption($unprotectedpage['name']);
	}
}
purgeOption('gallery_sortdirection');
purgeOption('gallery_sorttype');
purgeOption('gallery_title');
purgeOption('Gallery_description');
purgeOption('gallery_password');
purgeOption('gallery_user');
purgeOption('gallery_hint');
purgeOption('current_theme');
purgeOption('website_title');
purgeOption('website_url');
purgeOption('gallery_security');
purgeOption('login_user_field');
purgeOption('album_use_new_image_date');
purgeOption('thumb_select_images');
purgeOption('album_default');
purgeOption('image_default');
purgeThemeOptionTotal('display_copyright_notice');
setOptionDefault('display_copyright_notice', 1); // enable new global one by default

if (getOption('use_imagick') && $_zp_graphics->imagick_present) {
	setOptionDefault('graphicslib_selected', 'imagick');
} else {
	setOptionDefault('graphicslib_selected', 'gd');
}
purgeOption('use_imagick');

if (TEST_RELEASE) {
	foreach ($data as $key => $option) {
		purgeOption($key);
	}
	foreach ($optionlist as $key => $option) {
		if (strpos($key, 'gallery_page_unprotected_') === 0) {
			purgeOption($key);
		}
	}
}

//	cleanup options for missing elements
$sql = 'SELECT DISTINCT `creator` FROM ' . $_zp_db->prefix('options') . ' WHERE `creator` IS NOT NULL';
$result = $_zp_db->queryFullArray($sql);
if (is_array($result)) {
	foreach ($result as $row) {
		$filename = $row['creator'];
		if (!file_exists(SERVERPATH . '/' . $filename)) {
			$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `creator`=' . $_zp_db->quote($filename);
			$_zp_db->query($sql);
			if (strpos($filename, PLUGIN_FOLDER) !== false || strpos($filename, USER_PLUGIN_FOLDER) !== false) {
				purgeOption('zp_plugin_' . stripSuffix(basename($filename)));
			}
		}
	}
}
// missing themes
$sql = 'SELECT DISTINCT `theme` FROM ' . $_zp_db->prefix('options') . ' WHERE `theme` IS NOT NULL';
$result = $_zp_db->queryFullArray($sql);
if (is_array($result)) {
	foreach ($result as $row) {
		$filename = THEMEFOLDER . '/' . $row['theme'];
		if ($filename && !file_exists(SERVERPATH . '/' . $filename)) {
			$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `theme`=' . $_zp_db->quote($row['theme']);
			$_zp_db->query($sql);
		}
	}
}

setOptionDefault('search_cache_duration', 30);
setOptionDefault('search_within', 1);
setOption('last_update_check', 30);

$autoRotate = getOption('auto_rotate');
if (!is_null($autoRotate)) {
	if (!$autoRotate) {
		$_zp_db->query('UPDATE ' . $_zp_db->prefix('images') . ' SET `EXIFOrientation`=NULL');
		setOption('EXIFOrientation', 0);
		setOption('EXIFOrientation-disabled', 1);
	}
	purgeOption('auto_rotate');
}

purgeOption('zp_plugin_failed_access_blocker');
setOptionDefault('plugins_per_page', 20);
setOptionDefault('plugins_per_page_options', 10);
setOptionDefault('users_per_page', 10);
setOptionDefault('articles_per_page', 15);
setOptionDefault('debug_log_size', 5000000);
setOptionDefault('imageProcessorConcurrency', 30);
switch (getOption('spam_filter')) {
	case 'none':
		setOptionDefault('zp_plugin_trivialSpam', 5 | CLASS_PLUGIN);
		break;
	case 'simple':
		setOptionDefault('zp_plugin_simpleSpam', 5 | CLASS_PLUGIN);
		break;
	default:
		setOptionDefault('zp_plugin_legacySpam', 5 | CLASS_PLUGIN);
		break;
}
setOptionDefault('search_album_sort_type', 'title');
setOptionDefault('search_album_sort_direction', '');
setOptionDefault('search_image_sort_type', 'title');
setOptionDefault('search_image_sort_direction', '');

setOptionDefault('search_newsarticle_sort_type', 'date');
setOptionDefault('search_newsarticle_sort_direction', 1);
setOptionDefault('search_page_sort_type', 'title');
setOptionDefault('search_page_sort_direction', '');

purgeOption('zp_plugin_releaseUpdater');

$_zp_db->query('UPDATE ' . $_zp_db->prefix('administrators') . ' SET `passhash`=' . ((int) getOption('strong_hash')) . ' WHERE `valid`>=1 AND `passhash` IS NULL');
$_zp_db->query('UPDATE ' . $_zp_db->prefix('administrators') . ' SET `passupdate`=' . $_zp_db->quote(date('Y-m-d H:i:s')) . ' WHERE `valid`>=1 AND `passupdate` IS NULL');
setOptionDefault('image_processor_flooding_protection', 1);
setOptionDefault('codeblock_first_tab', 1);
setOptionDefault('GD_FreeType_Path', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/gd_fonts');

$vers = explode('-', ZENPHOTO_VERSION);
$vers = explode('.', $vers[0]);
while (count($vers) < 3) {
	$vers[] = 0;
}
$zpversion = $vers[0] . '.' . $vers[1] . '.' . $vers[2];
$_languages = generateLanguageList('all');
foreach ($_languages as $language => $dirname) {
	if (!empty($dirname) && $dirname != 'en_US') {
		$version = '';
		$po = file_get_contents(SERVERPATH . "/" . ZENFOLDER . "/locale/" . $dirname . '/LC_MESSAGES/zenphoto.po');
		$i = strpos($po, 'Project-Id-Version:');
		if ($i !== false) {
			$j = strpos($po, '\n', $i);
			if ($j !== false) {
				$pversion = strtolower(substr($po, $i + 19, $j - $i - 19));
				$vers = explode('.', trim(str_replace('zenphoto', '', $pversion)));
				while (count($vers) < 3) {
					$vers[] = 0;
				}
				$version = (int) $vers[0] . '.' . (int) $vers[1] . '.' . (int) $vers[2];
			}
		}
		if (is_null(getOption('disallow_' . $dirname)) && $version < $zpversion) {
			setOptionDefault('disallow_' . $dirname, 1);
		}
		if (setup::Locale($dirname)) {
			purgeOption('unsupported_' . $dirname);
		} else {
			setOption('unsupported_' . $dirname, 1);
		}
	}
}

//The following should be done LAST so it catches anything done above
//set plugin default options by instantiating the options interface
$plugins = getPluginFiles('*.php');
?>
<p>
	<?php
	$plugins = array_keys($plugins);
	sortArray($plugins);
	echo '<p>' . gettext('Plugin setup:') . '</p>';
	foreach ($plugins as $extension) {
		setup::defaultOptionsRequest($extension, 'plugin');
	}
	?>
</p>

<?php
$_zp_gallery->garbageCollect();
if (extensionEnabled('auto_backup')) {
	//Run the backup since for sure things have changed.
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/auto_backup.php');
	auto_backup::timer_handler('');
}
?>
