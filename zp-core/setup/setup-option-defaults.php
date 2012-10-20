<?php

// force UTF-8 Ø

/**
 * stores all the default values for options
 * @package setup
 */

setupLog(gettext('Set Zenphoto default options'),true);

eval(file_get_contents(CONFIGFILE));
require_once(dirname(dirname(__FILE__)).'/'.PLUGIN_FOLDER.'/security-logger.php');
zp_apply_filter('log_setup', true, 'install', '');

/*fix for NULL theme name*/
$active = getOptionList();
$sql = "SELECT * FROM ".prefix('options').' WHERE `theme` IS NULL';
$optionlist = query_full_array($sql);
if ($optionlist) {
	foreach ($optionlist as $option) {
		query('DELETE FROM '.prefix('options').' WHERE `id`='.$option['id']);
		setOption($option['name'], $active[$option['name']]);
	}
}
$lib_auth_extratext = "";
$salt = 'abcdefghijklmnopqursuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()_+-={}[]|;,.<>?/';
$list = range(0, strlen($salt));
shuffle($list);
for ($i=0; $i < 30; $i++) {
	$lib_auth_extratext = $lib_auth_extratext . substr($salt, $list[$i], 1);
}


purgeOption('zenphoto_release');

setOption('zenphoto_version', ZENPHOTO_VERSION.' ['.ZENPHOTO_RELEASE.']');
setOption('zenphoto_install', serialize(installSignature()));

if (Zenphoto_Authority::$preferred_version > ($oldv = getOption('libauth_version'))) {
	if (empty($oldv)) {
		//	The password hash of these old versions did not have the extra text.
		//	Note: if the administrators table is empty we will re-do this option with the good stuff.
		purgeOption('extra_auth_hash_text');
		setOptionDefault('extra_auth_hash_text', '');
	}
	$msg = sprintf(gettext('Migrating lib-auth data version %1$s => version %2$s'), $oldv, Zenphoto_Authority::$preferred_version);
	if (!$_zp_authority->migrateAuth(Zenphoto_Authority::$preferred_version)) {
		$msg .= ': '.gettext('failed');
	}
	echo $msg;
	setupLog($msg, true);
}
$admins = $_zp_authority->getAdministrators('all');
if (empty($admins)) {	//	empty administrators table
	$groupsdefined = NULL;
	setOption('strong_hash', 2);
	purgeOption('extra_auth_hash_text');
} else {
	$groupsdefined = @unserialize(getOption('defined_groups'));
}
setOptionDefault('extra_auth_hash_text', $lib_auth_extratext);
setOptionDefault('password_strength', 10);
setOptionDefault('min_password_lenght', 6);
setOptionDefault('user_album_edit_default', 1);

// old configuration opitons. preserve them
$conf = $_zp_conf_vars;
setOptionDefault('time_offset', 0);
if (isset($_GET['mod_rewrite'])) {
	if ($_GET['mod_rewrite'] == 'ON') {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$.ajax({
				type: 'GET',
				cache: false,
				data: 'z=setup',
				url: '<?php echo WEBPATH; ?>/page/setup_set-mod_rewrite'
			});
			// ]]> -->
		</script>
		<?php
	}
}

if (isset($_POST['setUTF8URI']) && $_POST['setUTF8URI'] != 'dont') {
	setOption('UTF8_image_URI', (int) ($_POST['setUTF8URI'] == 'true'));
}
setOptionDefault('mod_rewrite_image_suffix', ".php");
setOptionDefault('server_protocol', "http");
setOptionDefault('charset', "UTF-8");
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
setOptionDefault('watermark_allow_upscale', 1);
setOptionDefault('perform_video_watermark', 0);

if (getOption('perform_video_watermark')) {
	$v = str_replace('.png', "", basename(getOption('video_watermark_image')));
	setoptionDefault('Video_watermark', $v);
}

setOptionDefault('spam_filter', 'none');
setOptionDefault('email_new_comments', 1);
setOptionDefault('image_sorttype', 'Filename');
setOptionDefault('image_sortdirection', '0');
setOptionDefault('hotlink_protection', '1');
setOptionDefault('feed_items', 10); // options for standard images rss
setOptionDefault('feed_imagesize', 240);
setOptionDefault('feed_sortorder', 'latest');
setOptionDefault('feed_items_albums', 10); // options for albums rss
setOptionDefault('feed_imagesize_albums', 240);
setOptionDefault('feed_sortorder_albums', 'latest');
setOptionDefault('feed_enclosure', '0');
setOptionDefault('feed_mediarss', '0');
setOptionDefault('feed_cache', '1');
setOptionDefault('feed_cache_expire', 86400);
setOptionDefault('feed_hitcounter', 1);
setOptionDefault('feed_title','both');

setOptionDefault('search_fields', 'title,desc,tags,file,location,city,state,country,content,author');

$a =	"a => (href =>() title =>() target=>() class=>() id=>())\n" .
			"abbr =>(class=>() id=>() title =>())\n" .
			"acronym =>(class=>() id=>() title =>())\n" .
			"b => (class=>() id=>() )\n" .
			"blockquote =>(class=>() id=>() cite =>())\n" .
			"br => (class=>() id=>() )\n" .
			"code => (class=>() id=>() )\n" .
			"em => (class=>() id=>() )\n" .
			"i => (class=>() id=>() ) \n" .
			"strike => (class=>() id=>() )\n" .
			"strong => (class=>() id=>() )\n" .
			"ul => (class=>() id=>())\n" .
			"ol => (class=>() id=>())\n" .
			"li => (class=>() id=>())\n" .
			"p => (class=>() id=>() style=>())\n" .
			"h1=>(class=>() id=>() style=>())\n" .
			"h2=>(class=>() id=>() style=>())\n" .
			"h3=>(class=>() id=>() style=>())\n" .
			"h4=>(class=>() id=>() style=>())\n" .
			"h5=>(class=>() id=>() style=>())\n" .
			"h6=>(class=>() id=>() style=>())\n" .
			"pre=>(class=>() id=>() style=>())\n" .
			"address=>(class=>() id=>() style=>())\n" .
			"span=>(class=>() id=>() style=>())\n".
			"div=>(class=>() id=>() style=>())\n".
			"img=>(class=>() id=>() style=>() src=>() title=>() alt=>() width=>() height=>())\n"
			;
setOption('allowed_tags_default', $a);
setOptionDefault('allowed_tags', $a);
setOptionDefault('style_tags',
									"abbr => (title => ())\n" .
									 "acronym => (title => ())\n" .
									 "b => ()\n" .
									 "em => ()\n" .
									 "i => () \n" .
									 "strike => ()\n" .
									 "strong => ()\n");
setOptionDefault('comment_name_required', 1);
setOptionDefault('comment_email_required', 1);
setOptionDefault('comment_web_required', 'show');
setOptionDefault('Use_Captcha', false);
setOptionDefault('full_image_quality', 75);

if (getOption('protect_full_image') === '0') {
	$protection = 'Unprotected';
} else if (getOption('protect_full_image') === '1') {
	if (getOption('full_image_download')) {
		$protection = 'Download';
	} else {
		$protection = 'Protected view';
	}
} else {
	$protection = false;
}
if ($protection) {
	setOption('protect_full_image', $protection);
} else {
	setOptionDefault('protect_full_image', 'Protected view');
}

setOptionDefault('locale', '');
setOptionDefault('date_format', '%x');

// plugins--default to enabled
if (!is_null($v = getOption('zp_plugin_colorbox'))) {
	setOption('zp_plugin_colorbox_js', $v);
	purgeOption('zp_plugin_colorbox');
} else {
	setOptionDefault('zp_plugin_colorbox_js', 9|THEME_PLUGIN);
}


setOptionDefault('zp_plugin_class-video', 9|CLASS_PLUGIN);

setOptionDefault('use_lock_image', 1);
setOptionDefault('search_user', '');
setOptionDefault('multi_lingual', 0);
setOptionDefault('tagsort', 0);
setOptionDefault('albumimagesort', 'ID');
setOptionDefault('albumimagedirection', 'DESC');
setOptionDefault('cache_full_image', 0);
setOptionDefault('custom_index_page', '');
setOptionDefault('picture_of_the_day', serialize(array('day'=>NULL,'folder'=>NULL,'filename'=>NULL)));
setOptionDefault('exact_tag_match', 0);

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
foreach ($_zp_exifvars as $key=>$item) {
	setOptionDefault($key, 0);
}
setOptionDefault('IPTC_encoding', 'ISO-8859-1');

setOptionDefault('UTF8_image_URI', 0);
setOptionDefault('captcha', 'zenphoto');

setOptionDefault('sharpen_amount', 40);
setOptionDefault('sharpen_radius', 0.5);
setOptionDefault('sharpen_threshold', 3);

setOptionDefault('search_space_is_or', 0);
setOptionDefault('search_no_albums', 0);

// default groups

if (!is_array($groupsdefined)) {
	$groupsdefined = array();
}
if (!in_array('administrators',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('administrators',0);
	$groupobj->setName('group');
	$groupobj->setRights(ALL_RIGHTS);
	$groupobj->setCustomData(gettext('Users with full privileges'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'administrators';
}
if (!in_array('viewers',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('viewers',0);
	$groupobj->setName('group');
	$groupobj->setRights(NO_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS);
	$groupobj->setCustomData(gettext('Users allowed only to view zenphoto objects'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'viewers';
}
if (!in_array('bozos',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('bozos',0);
	$groupobj->setName('group');
	$groupobj->setRights(0);
	$groupobj->setCustomData(gettext('Banned users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'bozos';
}
if (!in_array('album managers',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('album managers',0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS | OVERVIEW_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS | UPLOAD_RIGHTS | COMMENT_RIGHTS | ALBUM_RIGHTS | THEMES_RIGHTS);
	$groupobj->setCustomData(gettext('Managers of one or more albums'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'album managers';
}
if (!in_array('default',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('default',0);
	$groupobj->setName('template');
	$groupobj->setRights(DEFAULT_RIGHTS);
	$groupobj->setCustomData(gettext('Default user settings'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'default';
}
if (!in_array('newuser',$groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('newuser',0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS);
	$groupobj->setCustomData(gettext('Newly registered and verified users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'newuser';
}
setOption('defined_groups',serialize($groupsdefined)); // record that these have been set once (and never again)

if (getOption('Allow_comments') || getOption('zenpage_comments_allowed')) {
	setOptionDefault('zp_plugin_comment_form', 5|ADMIN_PLUGIN||THEME_PLUGIN);
	if (!is_null($default = getOption('Allow_comments'))) {
		setOptionDefault('comment_form_albums', $default);
		setOptionDefault('comment_form_images', $default);
	}
	if (!is_null($default = getOption('zenpage_comments_allowed'))) {
		setOptionDefault('comment_form_articles', $default);
		setOptionDefault('comment_form_pages', $default);
	}
}
setOptionDefault('comment_body_requiired', 1);


setOptionDefault('zp_plugin_zenphoto_sendmail', 5|CLASS_PLUGIN);

setOptionDefault('RSS_album_image', 1);
setOptionDefault('RSS_comments', 1);
setOptionDefault('RSS_articles', 1);
setOptionDefault('RSS_article_comments', 1);

setOptionDefault('AlbumThumbSelect',1);
purgeOption('AlbumThumbSelectField');
purgeOption('AlbumThumbSelectDirection');

setOptionDefault('site_email',"zenphoto@".$_SERVER['SERVER_NAME']);
setOptionDefault('site_email_name', 'Zenphoto');

if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/Zenphoto.package')) {
	$package = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/Zenphoto.package');
	if (!empty($package)) {
		preg_match_all('|[^/]themes/([^/\r\n]*)|', $package, $matches);
		$themes = array_unique($matches[1]);
		setOption('Zenphoto_theme_list',serialize($themes));
	}
}
?>
<script type="text/javascript">
	// <!-- <![CDATA[
	<?php
	foreach (array_keys($_zp_gallery->getThemes()) as $theme) {
		?>
		$.ajax({
			type: 'POST',
			cache: false,
			url: '<?php echo WEBPATH.'/'.ZENFOLDER; ?>/setup/setup_themeOptions.php',
			data: 'theme=<?php echo $theme; ?>'
		});
		<?php
	}
	?>
	// ]]> -->
</script>
<?php
setOption('zp_plugin_deprecated-functions',9|CLASS_PLUGIN);	//	Yes, I know some people will be annoyed that this keeps coming back,
																														//	but each release may deprecated new functions which would then just give
																														//	(perhaps unseen) errors. Better the user should disable this once he knows
																														//	his site is working.
setOptionDefault('zp_plugin_zenphoto_news', 5|ADMIN_PLUGIN);
setOptionDefault('zp_plugin_hitcounter',1);
setOptionDefault('zp_plugin_tiny_mce', 5|ADMIN_PLUGIN);
setOptionDefault('zp_plugin_security-logger', 9|CLASS_PLUGIN);
// migrate search space is opton
if (getOption('search_space_is_OR')) {
	setOption('search_space_is', '|');
}
query('DELETE FROM '.prefix('options').' WHERE `name`="search_space_is_OR"',false);

if (!file_exists(SERVERPATH.'/favicon.ico')) {
	@copy(SERVERPATH.'/'.ZENFOLDER.'/images/favicon.ico',SERVERPATH.'/favicon.ico');
}

if (getOption('zp_plugin_zenphoto_sendmail')) {
	setOption('zp_plugin_zenphoto_sendmail', 5|CLASS_PLUGIN);
}
if (getOption('zp_plugin_PHPMailer')) {
	setOption('zp_plugin_PHPMailer', 8|CLASS_PLUGIN);
}

if ($v = getOption('zp_plugin_filter-zenphoto_seo')) {
	setOption('zp_plugin_zenphoto_seo', 1|ADMIN_PLUGIN);
	purgeOption("zp_plugin_seo_zenphoto");
} else if ($v = getOption('zp_plugin_zenphoto_seo')) {
	setOption('zp_plugin_seo_zenphoto', 1|ADMIN_PLUGIN);
	purgeOption("zp_plugin_zenphoto_seo");
} else {
	if (is_null($v)) {
		setOptionDefault('zp_plugin_seo_zenphoto', 1|ADMIN_PLUGIN);
	}
}

setOptionDefault('default_copyright', sprintf(gettext('Copyright %1$u: %2$s'), date('Y'),$_SERVER["HTTP_HOST"]));

if (getOption('comment_name_required') == 1) {
	setOption('comment_name_required', 'required');
}
if (getOption('comment_email_required') == 1) {
	setOption('comment_email_required', 'required');
}
if (getOption('comment_web_required') == 1) {
	setOption('comment_web_required', 'required');
}

setOptionDefault('fullsizeimage_watermark', getOption('fullimage_watermark'));


	$data = getOption('gallery_data');
	if ($data) {
		$data = unserialize($data);
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
			$data['gallery_title'] =  getAllTranslations($str);
		}
	}
	if (!isset($data['Gallery_description'])) {
		$data['Gallery_description'] = getOption('Gallery_description');
		if (is_null($data['Gallery_description'])) {
			gettext($str = 'You can insert your Gallery description on the Admin Options Gallery tab.');
			$data['Gallery_description'] =  getAllTranslations($str);
		}
	}
	if (!isset($data['gallery_password'])) $data['gallery_password'] = getOption('gallery_password');
	if (!isset($data['gallery_user'])) $data['gallery_user'] = getOption('gallery_user');
	if (!isset($data['gallery_hint'])) $data['gallery_hint'] = getOption('gallery_hint');
	if (!isset($data['hitcounter'])) $data['hitcounter'] = $result = getOption('Page-Hitcounter-index');
	if (!isset($data['current_theme'])) {
		$data['current_theme'] = getOption('current_theme');
		if (is_null($data['current_theme'])) {
		$data['current_theme'] = 'default';
		}
	}
	if (!isset($data['website_title'])) $data['website_title'] = getOption('website_title');
	if (!isset($data['website_url'])) $data['website_url'] = getOption('website_url');
	if (!isset($data['gallery_security'])) {
		$data['gallery_security'] = getOption('gallery_security');
		if (is_null($data['gallery_security'])) {
			$data['gallery_security'] = 'public';
		}
	}
	if (!isset($data['login_user_field'])) $data['login_user_field'] = getOption('login_user_field');
	if (!isset($data['album_use_new_image_date'])) $data['album_use_new_image_date'] = getOption('album_use_new_image_date');
	if (!isset($data['thumb_select_images'])) $data['thumb_select_images'] = getOption('thumb_select_images');
	if (!isset($data['unprotected_pages'])) $data['unprotected_pages'] = getOption('unprotected_pages');
	if ($data['unprotected_pages']) {
		$unprotected = unserialize($data['unprotected_pages']);
	} else {
		setOptionDefault('gallery_page_unprotected_register', 1);
		setOptionDefault('gallery_page_unprotected_contact', 1);
		$unprotected = array();
	}
	$_zp_options = NULL;	// get a fresh start
	$optionlist = getOptionList();
	foreach ($optionlist as $key=>$option) {
		if ($option && strpos($key, 'gallery_page_unprotected_') === 0) {
			$unprotected[] = str_replace('gallery_page_unprotected_', '', $key);
		}
	}
	if (!isset($data['album_publish'])) {
		$set =  getOption('album_default');
		if (is_null($set)) $set = 1;
		$data['album_publish'] = $set;
	}
	if (!isset($data['image_publish'])) {
		$set =  getOption('image_default');
		if (is_null($set)) $set = 1;
		$data['image_publish'] = $set;
	}
	$data['unprotected_pages'] = serialize($unprotected);
	setOption('gallery_data', serialize($data));
	$_zp_gallery = new Gallery();	// insure we have the proper options instantiated

/*TODO:enable on the 1.5 release
 *
The following options have been relocated to methods of the gallery object. They will be purged form installations
on the Zenphoto 1.5 release.

*gallery_page_unprotected_xxx
*gallery_sortdirection
*gallery_sorttype
*gallery_title
*Gallery_description
 gallery_password
 gallery_user
 gallery_hint
 current_theme
*website_title
*website_url
 gallery_security
 login_user_field
 album_use_new_image_date
 thumb_select_images
 album_default
 image_default

* these may have been used in third party themes. Themes should cease using these options and instead use the
appropriate gallery methods.
*/
	if (TEST_RELEASE) {
		foreach ($data as $key=>$option) {
			purgeOption($key);
		}
		foreach ($optionlist as $key=>$option) {
			if (strpos($key, 'gallery_page_unprotected_') === 0) {
				purgeOption($key);
			}
		}
	}

	//	cleanup options for missing elements
	$sql = 'SELECT DISTINCT `creator` FROM '.prefix('options').' WHERE `creator` IS NOT NULL';
	$result = query_full_array($sql);
	if (is_array($result)) {
		foreach ($result as $row) {
			$filename = $row['creator'];
			if (!file_exists(SERVERPATH.'/'.$filename)) {
				$sql = 'DELETE FROM '.prefix('options').' WHERE `creator`='.db_quote($filename);
				query($sql);
				if (strpos($filename, PLUGIN_FOLDER) !== false || strpos($filename, USER_PLUGIN_FOLDER) !== false) {
					purgeOption('zp_plugin_'.stripSuffix(basename($filename)));
				}
			}
		}
	}
	// missing themes
	$sql = 'SELECT DISTINCT `theme` FROM '.prefix('options').' WHERE `theme` IS NOT NULL';
	$result = query_full_array($sql);
	if (is_array($result)) {
		foreach ($result as $row) {
			$filename = THEMEFOLDER.'/'.$row['theme'];
			if ($filename && !file_exists(SERVERPATH.'/'.$filename)) {
				$sql = 'DELETE FROM '.prefix('options').' WHERE `theme`='.db_quote($row['theme']);
				query($sql);
			}
		}
	}

	setOptionDefault('search_cache_duration', 30);
	setOptionDefault('search_within', 1);
	setOption('last_update_check', 30);
	setOptionDefault('zp_plugin_zenphotoDonate', 9|ADMIN_PLUGIN);
	setOptionDefault('zp_plugin_uploader_http', 5|ADMIN_PLUGIN);
	setOptionDefault('zp_plugin_uploader_flash', 5|ADMIN_PLUGIN);
	setOptionDefault('zp_plugin_uploader_jQuery', 5|ADMIN_PLUGIN);

	$autoRotate = getOption('auto_rotate');
	if (!is_null($autoRotate)) {
		if (!$autoRotate) {
			query('UPDATE '.prefix('images').' SET `EXIFOrientation`=NULL');
			setOption('EXIFOrientation', 0);
			setOption('EXIFOrientation-disabled', 1);
		}
		purgeOption('auto_rotate');
	}

setOptionDefault('zp_plugin_ipBlocker', getOption('zp_plugin_failed_access_blocker'));
purgeOption('zp_plugin_failed_access_blocker');
setOptionDefault('spamFilter_none_action', getOption('Action'));
purgeOption('Action');
setOptionDefault('plugins_per_page', 20);
setOptionDefault('users_per_page', 10);
setOptionDefault('articles_per_page', 15);
setOptionDefault('combinews-customtitle', getOption('combinews-customtitle-plural'));
purgeOption('combinews-customtitle-singular');
purgeOption('combinews-customtitle-plural');
setOptionDefault('debug_log_size', 5000000);

//The following should be done LAST so it catches anything done above
//set plugin default options by instantiating the options interface
$plugins = getPluginFiles('*.php');

?>
<script type="text/javascript">
	// <!-- <![CDATA[
	<?php
	foreach ($plugins as $extension=>$path) {
		?>
		$.ajax({
			type: 'POST',
			cache: false,
			url: '<?php echo WEBPATH.'/'.ZENFOLDER; ?>/setup/setup_pluginOptions.php',
			data: 'plugin=<?php echo $extension; ?>'
		});
		<?php
	}
	?>
	// ]]> -->
</script>
<?php

?>
