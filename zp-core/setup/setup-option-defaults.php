<?php
// force UTF-8 Ø

/**
 * stores all the default values for options
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 */
setupLog(gettext('Set default options'), true);

require(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/security-logger.php');
zp_apply_filter('log_setup', true, 'install', '');

/* fix for NULL theme name */
Query('UPDATE ' . prefix('options') . ' SET `theme`="" WHERE `theme` IS NULL');

$lib_auth_extratext = "";
$salt = 'abcdefghijklmnopqursuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()_+-={}[]|;,.<>?/';
$list = range(0, strlen($salt) - 1);
shuffle($list);
for ($i = 0; $i < 30; $i++) {
	$lib_auth_extratext = $lib_auth_extratext . $salt{$list[$i]};
}

//clean up tag list quoted strings
$sql = 'SELECT * FROM ' . prefix('tags') . ' WHERE `name` LIKE \'"%\' OR `name` LIKE "\'%"';
$result = query($sql);
if ($result) {
	while ($row = db_fetch_assoc($result)) {
		$sql = 'UPDATE ' . prefix('tags') . ' SET `name`=' . db_quote(trim($row['name'], '"\'')) . ' WHERE `id`=' . $row['id'];
		if (!query($sql, false)) {
			$oldtag = $row['id'];
			$sql = 'DELETE FROM ' . prefix('tags') . ' WHERE `id`=' . $oldtag;
			query($sql);
			$sql = 'SELECT * FROM ' . prefix('tags') . ' WHERE `name`=' . db_quote(trim($row['name'], '"\''));
			$row = query_single_row($sql);
			if (!empty($row)) {
				$sql = 'UPDATE ' . prefix('obj_to_tag') . ' SET `tagid`=' . $row['id'] . ' WHERE `tagid`=' . $oldtag;
			}
		}
	}
}

//migrate CMS "publish" dates
foreach (array('news', 'pages') as $table) {
	$sql = 'UPDATE ' . prefix($table) . ' SET `publishdate`=`date` WHERE `publishdate` is NULL';
	query($sql);
}

setOption('zenphoto_install', serialize(installSignature()));
$admins = $_zp_authority->getAdministrators('all');

$str = gettext("What is your father’s middle name?");
$questions[] = getSerializedArray(getAllTranslations($str));
$str = gettext("What street did your Grandmother live on?");
$questions[] = getSerializedArray(getAllTranslations($str));
$str = gettext("Who was your favorite singer?");
$questions[] = getSerializedArray(getAllTranslations($str));
$str = gettext("When did you first get a computer?");
$questions[] = getSerializedArray(getAllTranslations($str));
$str = gettext("How much wood could a woodchuck chuck if a woodchuck could chuck wood?");
$questions[] = getSerializedArray(getAllTranslations($str));
$str = gettext("What is the date of the Ides of March?");
$questions[] = getSerializedArray(getAllTranslations($str));
setOptionDefault('challenge_foils', serialize($questions));

if (empty($admins)) { //	empty administrators table
	$groupsdefined = NULL;
	if (isset($_SESSION['clone'][$cloneid])) { //replicate the user who cloned the install
		$clone = $_SESSION['clone'][$cloneid];
		setOption('UTF8_image_URI', $clone['UTF8_image_URI']);
		setOption('strong_hash', $clone['strong_hash']);
		setOption('extra_auth_hash_text', $clone['hash']);
		if ($clone['mod_rewrite'])
			$_GET['mod_rewrite'] = true;
		$_zp_current_admin_obj = unserialize($_SESSION['admin'][$cloneid]);
		$_zp_current_admin_obj->clearID();
		$_zp_current_admin_obj->save();
		$_zp_loggedin = ALL_RIGHTS;
		setOption('license_accepted', ZENPHOTO_VERSION . '[' . ZENPHOTO_RELEASE . ']');
		unset($_SESSION['clone'][$cloneid]);
	} else {
		if (Zenphoto_Authority::$preferred_version > ($oldv = getOption('libauth_version'))) {
			if (empty($oldv)) {
				//	The password hash of these old versions did not have the extra text.
				//	Note: if the administrators table is empty we will re-do this option with the good stuff.
				purgeOption('extra_auth_hash_text');
				setOptionDefault('extra_auth_hash_text', '');
			}
			$msg = sprintf(gettext('Migrating lib-auth data version %1$s => version %2$s'), $oldv, Zenphoto_Authority::$preferred_version);
			if (!$_zp_authority->migrateAuth(Zenphoto_Authority::$preferred_version)) {
				$msg .= ': ' . gettext('failed');
			}
			echo $msg;
			setupLog($msg, true);
		} if (function_exists('hash')) {
			setOption('strong_hash', 3);
		} else {
			setOption('strong_hash', 1);
		}
		purgeOption('extra_auth_hash_text');
	}
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
	?>
	<script type="text/javascript">
		$(function () {
			$('img').error(function () {
				var link = $(this).attr('src');
				var title = $(this).attr('title');
				$(this).parent().html('<a href="' + link + '" target="_blank"><img src="../images/fail.png" title="' + title + '"></a>');
				imageErr = true;
			});
		});
	</script>
	<p>
		<?php echo gettext('Mod_Rewrite check:'); ?>
		<br />
		<span>
			<img src="<?php echo FULLWEBPATH . '/' . $_zp_conf_vars['special_pages']['page']['rewrite']; ?>/setup_set-mod_rewrite?z=setup" title="<?php echo gettext('Mod_rewrite'); ?>" alt="<?php echo gettext('Mod_rewrite'); ?>" height="16px" width="16px" />
		</span>
	</p>
	<?php
}

if (isset($_POST['setUTF8URI']) && $_POST['setUTF8URI'] != 'dont') {
	setOption('UTF8_image_URI', (int) ($_POST['setUTF8URI'] == 'true'));
}
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

setOptionDefault('hotlink_protection', '1');

setOptionDefault('search_fields', 'title,desc,tags,file,location,city,state,country,content,author');

$a = "a => (href =>() title =>() target=>() class=>() id=>())\n" .
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
				"span=>(class=>() id=>() style=>())\n" .
				"div=>(class=>() id=>() style=>())\n" .
				"img=>(class=>() id=>() style=>() src=>() title=>() alt=>() width=>() height=>())\n"
;
setOption('allowed_tags_default', $a);
setOptionDefault('allowed_tags', $a);
setOptionDefault('style_tags', "abbr => (title => ())\n" .
				"acronym => (title => ())\n" .
				"b => ()\n" .
				"em => ()\n" .
				"i => () \n" .
				"strike => ()\n" .
				"strong => ()\n");
//	insure tags are in lower case!
setOption('allowed_tags', strtolower(getOption('allowed_tags')));

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

setOptionDefault('use_lock_image', 1);
setOptionDefault('search_user', '');
setOptionDefault('multi_lingual', 0);
setOptionDefault('tagsort', 0);
setOptionDefault('albumimagesort', 'ID');
setOptionDefault('albumimagedirection', 'DESC');
setOptionDefault('cache_full_image', 0);
setOptionDefault('custom_index_page', '');
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

setOptionDefault('UTF8_image_URI', 0);

setOptionDefault('sharpen_amount', 40);
setOptionDefault('sharpen_radius', 0.5);
setOptionDefault('sharpen_threshold', 3);

setOptionDefault('search_space_is_or', 0);
setOptionDefault('search_no_albums', 0);

// default groups
if (!is_array($groupsdefined)) {
	$groupsdefined = array();
}
if (!in_array('administrators', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('administrators', 0);
	$groupobj->setName('group');
	$groupobj->setRights(ALL_RIGHTS);
	$groupobj->set('other_credentials', gettext('Users with full privileges'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'administrators';
}
if (!in_array('viewers', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('viewers', 0);
	$groupobj->setName('group');
	$groupobj->setRights(NO_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS);
	$groupobj->set('other_credentials', gettext('Users allowed only to view zenphoto objects'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'viewers';
}
if (!in_array('blocked', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('blocked', 0);
	$groupobj->setName('group');
	$groupobj->setRights(0);
	$groupobj->set('other_credentials', gettext('Banned users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'blocked';
}
if (!in_array('album managers', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('album managers', 0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS | OVERVIEW_RIGHTS | POST_COMMENT_RIGHTS | VIEW_ALL_RIGHTS | UPLOAD_RIGHTS | COMMENT_RIGHTS | ALBUM_RIGHTS | THEMES_RIGHTS);
	$groupobj->set('other_credentials', gettext('Managers of one or more albums'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'album managers';
}
if (!in_array('default', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('default', 0);
	$groupobj->setName('template');
	$groupobj->setRights(DEFAULT_RIGHTS);
	$groupobj->set('other_credentials', gettext('Default user settings'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'default';
}
if (!in_array('newuser', $groupsdefined)) {
	$groupobj = Zenphoto_Authority::newAdministrator('newuser', 0);
	$groupobj->setName('template');
	$groupobj->setRights(NO_RIGHTS);
	$groupobj->set('other_credentials', gettext('Newly registered and verified users'));
	$groupobj->setValid(0);
	$groupobj->save();
	$groupsdefined[] = 'newuser';
}
setOption('defined_groups', serialize($groupsdefined)); // record that these have been set once (and never again)

setOptionDefault('RSS_album_image', 1);
setOptionDefault('RSS_comments', 1);
setOptionDefault('RSS_articles', 1);
setOptionDefault('RSS_pages', 1);
setOptionDefault('RSS_article_comments', 1);

setOptionDefault('AlbumThumbSelect', 1);

setOptionDefault('site_email', "zenphoto@" . $_SERVER['SERVER_NAME']);
setOptionDefault('site_email_name', 'ZenPhoto20');

//effervescence_plus migration
if (file_exists(SERVERPATH . '/' . THEMEFOLDER . '/effervescence_plus')) {
	if ($_zp_gallery->getCurrentTheme() == 'effervescence_plus') {
		$_zp_gallery->setCurrentTheme('effervescence+');
		$_zp_gallery->save();
	}
	$options = query_full_array('SELECT LCASE(`name`) as name, `value` FROM ' . prefix('options') . ' WHERE `theme`="effervescence_plus"');
	foreach ($options as $option) {
		setThemeOption($option['name'], $option['value'], NULL, 'effervescence+', true);
	}
	zpFunctions::removeDir(SERVERPATH . '/' . THEMEFOLDER . '/effervescence_plus');
}
?>
<p>
	<?php
	$themes = array_keys($_zp_gallery->getThemes());
	natcasesort($themes);
	echo gettext('Theme setup:') . '<br />';
	foreach ($themes as $theme) {
		?>
		<span>
			<img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/setup/setup_themeOptions.php?theme=' . urlencode($theme); ?>" title="<?php echo $theme; ?>" alt="<?php echo $theme; ?>" height="16px" width="16px" />
		</span>
		<?php
	}
	?>
</p>

<?php
// migrate search space is opton
if (getOption('search_space_is_OR')) {
	setOption('search_space_is', '|');
}
query('DELETE FROM ' . prefix('options') . ' WHERE `name`="search_space_is_OR"', false);

if (!file_exists(SERVERPATH . '/favicon.ico')) {
	@copy(SERVERPATH . '/' . ZENFOLDER . '/images/favicon.ico', SERVERPATH . '/favicon.ico');
} else {
	$zp_ico = "0000010001001010000001000800680500001600000028000000100000002000000001000800000000004005000000000000000000000001000000010000ffffff00eaeaea00cccccc0000000000d1d1d100f0f0f000b4b4b400d7d7d7009797970000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000010101010101010101000000000000010202020202020201010100000000000102030303030303030101010000000001020304010101020301010101010000010102030501010203030306010100000001010203010102030002030101000000000101020301010303030701010000000000010102030101010101010100000001010101010203010101000000000001020202020202080301010000000000010103030303030303010100000000000101010101010101010101000000000000010101010101010101000000000000000000000000000000000000000000000000000000000000000000000000000000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff0000ffff";
	$ico = bin2hex(file_get_contents(SERVERPATH . '/favicon.ico'));

	if ($zp_ico == $ico) {
		unlink(SERVERPATH . '/favicon.ico');
		@copy(SERVERPATH . '/' . ZENFOLDER . '/images/favicon.ico', SERVERPATH . '/favicon.ico');
	}
}

setOptionDefault('default_copyright', sprintf(gettext('Copyright %1$u: %2$s'), date('Y'), $_SERVER["HTTP_HOST"]));

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
		$data['current_theme'] = 'default';
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

primeOptions(); // get a fresh start
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
if (!isset($data['image_sorttype'])) {
	$set = getOption('image_sorttype');
	if (is_null($set))
		$set = 'Filename';
	$data['image_sorttype'] = $set;
}
if (!isset($data['image_sortdirection'])) {
	$set = getOption('image_sortdirection');
	if (is_null($set))
		$set = 0;
	$data['image_sorttype'] = $set;
}

setOption('gallery_data', serialize($data));
// purge the old versions of these
foreach ($data as $key => $value) {
	purgeOption($key);
}

$_zp_gallery = new Gallery(); // insure we have the proper options instantiated

setOptionDefault('search_cache_duration', 30);
setOptionDefault('search_within', 1);
setOption('last_update_check', 30);

setOptionDefault('plugins_per_page', 25);
setOptionDefault('users_per_page', 10);
setOptionDefault('articles_per_page', 15);
setOptionDefault('debug_log_size', 5000000);
setOptionDefault('imageProcessorConcurrency', 30);
setOptionDefault('search_album_sort_type', 'title');
setOptionDefault('search_image_sort_type', 'title');
setOptionDefault('search_album_sort_direction', '');
setOptionDefault('search_image_sort_direction', '');

query('UPDATE ' . prefix('administrators') . ' SET `passhash`=' . ((int) getOption('strong_hash')) . ' WHERE `valid`>=1 AND `passhash` IS NULL');
query('UPDATE ' . prefix('administrators') . ' SET `passupdate`=' . db_quote(date('Y-m-d H:i:s')) . ' WHERE `valid`>=1 AND `passupdate` IS NULL');
setOptionDefault('image_processor_flooding_protection', 1);
setOptionDefault('codeblock_first_tab', 1);
setOptionDefault('GD_FreeType_Path', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/gd_fonts');

setOptionDefault('theme_head_listparents', 0);
setOptionDefault('theme_head_separator', ' | ');

setOptionDefault('tagsort', 'alpha');
setOptionDefault('languageTagSearch', 1);

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
		if (i18nSetLocale($dirname)) {
			purgeOption('unsupported_' . $dirname);
		} else {
			setOption('unsupported_' . $dirname, 1);
		}
	}
}

//The following should be done LAST so it catches anything done above
//set plugin default options by instantiating the options interface
enableExtension('deprecated-functions', 0); //	innocent until proven guilty.
$plugins = getPluginFiles('*.php');
?>
<p>
	<?php
	$plugins = array_keys($plugins);
	natcasesort($plugins);
	echo gettext('Plugin setup:') . '<br />';
	foreach ($plugins as $extension) {
		?>
		<span>
			<img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/setup/setup_pluginOptions.php?plugin=' . $extension; ?>" title="<?php echo $extension; ?>" alt="<?php echo $extension; ?>" height="16px" width="16px" />
		</span>
		<?php
	}
	?>
</p>

<?php
$_zp_gallery->garbageCollect();
?>
