<?php
/**
 * provides the Options tab of admin
 * @package zpcore\admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions/functions-config.php');

admin_securityChecks(OPTIONS_RIGHTS, currentRelativeURL());

define('PLUGINS_PER_PAGE', max(1, getOption('plugins_per_page')));
if (isset($_GET['pagenumber'])) {
	$pagenumber = sanitize_numeric($_GET['pagenumber']);
} else {
	if (isset($_POST['pagenumber'])) {
		$pagenumber = sanitize_numeric($_POST['pagenumber']);
	} else {
		$pagenumber = 0;
	}
}

if (!isset($_GET['page'])) {
	if (array_key_exists('options', $_zp_admin_menu)) {
		$_GET['page'] = 'options';
	} else {
		$_GET['page'] = 'users'; // must be a user with no options rights
	}
}
$_current_tab = sanitize($_GET['page'], 3);

/* handle posts */
if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	$themeswitch = false;
	if ($action == 'saveoptions') {
		XSRFdefender('saveoptions');
		$table = NULL;

		$notify = '';
		$returntab = '';
		$themealbum = $themename = NULL;

		/*		 * * General options ** */
		if (isset($_POST['savegeneraloptions'])) {

			$returntab = "&tab=general";
			$tags = strtolower(sanitize($_POST['allowed_tags'], 0));
			$test = "(" . $tags . ")";
			$a = parseAllowedTags($test);
			if ($a) {
				setOption('allowed_tags', $tags);
				$notify = '';
			} else {
				$notify = '?tag_parse_error=' . $a;
			}
			$oldloc = SITE_LOCALE; // get the option as stored in the database, not what might have been set by a cookie
			$newloc = sanitize($_POST['locale'], 3);
			$languages = generateLanguageList(true);
			$languages[''] = '';
			foreach ($languages as $text => $lang) {
				if ($lang == $newloc || isset($_POST['language_allow_' . $lang])) {
					setOption('disallow_' . $lang, 0);
				} else {
					setOption('disallow_' . $lang, 1);
				}
			}
			if ($newloc != $oldloc) {
				if (!empty($newloc) && getOption('disallow_' . $newloc)) {
					$notify = '?local_failed=' . $newloc;
				} else {
					zp_clearCookie('zpcms_locale'); // clear the language cookie
					$result = i18nSetLocale($newloc);
					if (!empty($newloc) && ($result === false)) {
						$notify = '?local_failed=' . $newloc;
					}
					setOption('locale', $newloc);
				}
			}

			setOption('mod_rewrite', (int) isset($_POST['mod_rewrite']));
			setOption('mod_rewrite_image_suffix', sanitize($_POST['mod_rewrite_image_suffix'], 3));
			if (isset($_POST['time_zone'])) {
				setOption('time_zone', sanitize($_POST['time_zone'], 3));
				$offset = 0;
			} else {
				$offset = sanitize($_POST['time_offset'], 3);
			}
			setOption('time_offset', $offset);
			setOption('charset', sanitize($_POST['charset']), 3);
			setOption('filesystem_charset', sanitize($_POST['filesystem_charset']), 3);
			setOption('site_email', sanitize($_POST['site_email']), 3);
			$_zp_gallery->setGallerySession((int) isset($_POST['album_session']));
			$_zp_gallery->save();
			if (isset($_POST['zenphoto_cookie_path'])) {
				$p = sanitize($_POST['zenphoto_cookie_path']);
				if (empty($p)) {
					zp_clearCookie('zpcms_cookie_path');
				} else {
					$p = '/' . trim($p, '/') . '/';
					if ($p == '//') {
						$p = '/';
					}
					//	save a cookie to see if change works
					$returntab .= '&cookiepath';
					zp_setCookie('zpcms_cookie_path', $p, NULL, $p);
				}
				setOption('zenphoto_cookie_path', $p);
				if (isset($_POST['cookie_persistence'])) {
					setOption('cookie_persistence', sanitize_numeric($_POST['cookie_persistence']));
				}
			}

			setOption('site_email_name', process_language_string_save('site_email_name', 3));
			setOption('users_per_page', sanitize_numeric($_POST['users_per_page']));
			setOption('plugins_per_page', sanitize_numeric($_POST['plugins_per_page']));
			if (isset($_POST['articles_per_page'])) {
				setOption('articles_per_page', sanitize_numeric($_POST['articles_per_page']));
			}
			setOption('multi_lingual', (int) isset($_POST['multi_lingual']));

			// date format
			$dateformat = sanitize($_POST['date_format_list'], 3);
			if ($dateformat == 'custom') {
				$dateformat = sanitize($_POST['date_format'], 3);
			}
			setOption('date_format', $dateformat);
			$timeformat = sanitize($_POST['time_format_list'], 3);
			if ($timeformat == 'custom') {
				$timeformat = sanitize($_POST['time_format'], 3);
			}
			setOption('time_format', $timeformat);
			if (extension_loaded('intl')) {
				$localized_dates = (int) isset($_POST['date_format_localized']);
			} else {
				$localized_dates = 0;
			}
			setOption('date_format_localized', $localized_dates);
			setOption('time_display_disabled', (int) isset($_POST['time_display_disabled']));
			
			setOption('UTF8_image_URI', (int) isset($_POST['UTF8_image_URI']));
			foreach ($_POST as $key => $value) {
				if (preg_match('/^log_size.*_(.*)$/', $key, $matches)) {
					setOption($matches[1] . '_log_size', $value);
					setOption($matches[1] . '_log_mail', (int) isset($_POST['log_mail_' . $matches[1]]));
				}
			}
			setOption('daily_logs', (int) isset($_POST['daily_logs']));
		}

		/*		 * * Gallery options ** */
		if (isset($_POST['savegalleryoptions'])) {

			$_zp_gallery->setAlbumPublish((int) isset($_POST['album_default']));
			$_zp_gallery->setImagePublish((int) isset($_POST['image_default']));

			setOption('AlbumThumbSelect', sanitize_numeric($_POST['thumbselector']));
			$_zp_gallery->setThumbSelectImages((int) isset($_POST['thumb_select_images']));
			$_zp_gallery->setSecondLevelThumbs((int) isset($_POST['multilevel_thumb_select_images']));
			$_zp_gallery->setTitle(process_language_string_save('gallery_title', 2));
			$_zp_gallery->setDesc(process_language_string_save('Gallery_description', EDITOR_SANITIZE_LEVEL));

			$_zp_gallery->setCopyrightNotice(process_language_string_save('copyright_site_notice', EDITOR_SANITIZE_LEVEL));
			$_zp_gallery->setCopyrightURL(sanitize($_POST['copyright_site_url'], 3));
			$_zp_gallery->set('copyright_site_url_custom', sanitize($_POST['copyright_site_url_custom'], 3));
			setOption('display_copyright_notice', (int) isset($_POST['display_copyright_notice']));
			$_zp_gallery->set('copyright_site_rightsholder', sanitize($_POST['copyright_site_rightsholder'], 3));
			$_zp_gallery->set('copyright_site_rightsholder_custom', sanitize($_POST['copyright_site_rightsholder_custom'], 3));

			$_zp_gallery->setParentSiteTitle(process_language_string_save('website_title', 2));
			$web = sanitize($_POST['website_url'], 3);
			$_zp_gallery->setParentSiteURL($web);
			$_zp_gallery->setAlbumUseImagedate((int) isset($_POST['album_use_new_image_date']));
			$st = strtolower(sanitize($_POST['gallery_sorttype'], 3));
			if ($st == 'custom')
				$st = strtolower(sanitize($_POST['customalbumsort'], 3));
			$_zp_gallery->setSortType($st);
			if (($st == 'manual') || ($st == 'random')) {
				$_zp_gallery->setSortDirection(false);
			} else {
				$_zp_gallery->setSortDirection(isset($_POST['gallery_sortdirection']));
			}
			foreach ($_POST as $item => $value) {
				if (strpos($item, 'gallery-page_') === 0) {
					$item = sanitize(substr(postIndexDecode($item), 13));
					$_zp_gallery->setUnprotectedPage($item, (int) isset($_POST['gallery_page_unprotected_' . $item]));
				}
			}
			$_zp_gallery->setSecurity(sanitize($_POST['gallery_security'], 3));
			$notify = processCredentials($_zp_gallery);
			if (zp_loggedin(CODEBLOCK_RIGHTS)) {
				$_zp_gallery->setCodeblock(processCodeblockSave(0));
			}
			$_zp_gallery->save();
			$returntab = "&tab=gallery";
		}

		/*		 * * Search options ** */
		if (isset($_POST['savesearchoptions'])) {
			$fail = '';
			$search = new SearchEngine();
			$searchfields = array();
			foreach ($_POST as $key => $value) {
				if (strpos($key, 'SEARCH_') !== false) {
					$searchfields[] = substr(sanitize(postIndexDecode($key)), 7);
				}
			}
			setOption('search_fields', implode(',', $searchfields));
			setOption('search_fieldsselector_enabled', (int) isset($_POST['search_fieldsselector_enabled']));
			setOption('search_cache_duration', sanitize_numeric($_POST['search_cache_duration']));
			$notify = processCredentials('search');
			setOption('exact_tag_match', sanitize($_POST['tag_match']));
			setOption('exact_string_match', sanitize($_POST['string_match']));
			setOption('search_space_is', sanitize($_POST['search_space_is']));
			setOption('search_no_albums', (int) isset($_POST['search_no_albums']));
			setOption('search_no_images', (int) isset($_POST['search_no_images']));
			setOption('search_no_pages', (int) isset($_POST['search_no_pages']));
			setOption('search_no_news', (int) isset($_POST['search_no_news']));
			setOption('search_within', (int) ($_POST['search_within'] && true));

			// image default sort order + direction
			$sorttype = strtolower(sanitize($_POST['search_image_sort_type'], 3));
			if ($sorttype == 'custom') {
				$sorttype = unquote(strtolower(sanitize($_POST['custom_image_sort'], 3)));
			}
			setOption('search_image_sort_type', $sorttype);
			if ($sorttype == 'random') {
				setOption('search_image_sort_direction', 0);
			} else {
				if (empty($sorttype)) {
					$direction = 0;
				} else {
					$direction = isset($_POST['search_image_sort_direction']);
				}
				setOption('search_image_sort_direction', $direction);
			}

			// album default sort order + direction
			$sorttype = strtolower(sanitize($_POST['search_album_sort_type'], 3));
			if ($sorttype == 'custom') {
				$sorttype = strtolower(sanitize($_POST['custom_album_sort'], 3));
			}
			setOption('search_album_sort_type', $sorttype);
			if ($sorttype == 'random') {
				setOption('search_album_sort_direction', 0);
			} else {
				setOption('search_album_sort_direction', isset($_POST['search_album_sort_direction']));
			}

			if (ZP_NEWS_ENABLED) {
				// Zenpage news articles default sort order + direction
				$sorttype = strtolower(sanitize($_POST['search_newsarticle_sort_type'], 3));
				if ($sorttype == 'custom') {
					$sorttype = strtolower(sanitize($_POST['custom_newsarticle_sort'], 3));
				}
				setOption('search_newsarticle_sort_type', $sorttype);
				if ($sorttype == 'random') {
					setOption('search_newsarticle_sort_direction', 0);
				} else {
					setOption('search_newsarticle_sort_direction', isset($_POST['search_newsarticle_sort_direction']));
				}
			}

			if (ZP_PAGES_ENABLED) {
				// Zenpage pages default sort order + direction
				$sorttype = strtolower(sanitize($_POST['search_page_sort_type'], 3));
				if ($sorttype == 'custom')
					$sorttype = strtolower(sanitize($_POST['custom_page_sort'], 3));
				setOption('search_page_sort_type', $sorttype);
				if ($sorttype == 'random') {
					setOption('search_page_sort_direction', 0);
				} else {
					setOption('search_page_sort_direction', isset($_POST['search_page_sort_direction']));
				}
			}
			$returntab = "&tab=search";
		}

		/*		 * * Image options ** */
		if (isset($_POST['saveimageoptions'])) {
			setOption('image_max_size', sanitize_numeric($_POST['image_max_size']));
			setOption('image_quality', sanitize($_POST['image_quality'], 3));
			setOption('thumb_quality', sanitize($_POST['thumb_quality'], 3));
			setOption('image_allow_upscale', (int) isset($_POST['image_allow_upscale']));
			setOption('thumb_sharpen', (int) isset($_POST['thumb_sharpen']));
			setOption('image_sharpen', (int) isset($_POST['image_sharpen']));
			setOption('image_interlace', (int) isset($_POST['image_interlace']));
			setOption('EmbedIPTC', (int) isset($_POST['EmbedIPTC']));

			setOption('copyright_image_notice', process_language_string_save('copyright_image_notice', 3));
			setOption('display_copyright_image_notice', (int) isset($_POST['display_copyright_image_notice']));
			setOption('copyright_image_url', sanitize($_POST['copyright_image_url']));
			setOption('copyright_image_url_custom', sanitize($_POST['copyright_image_url_custom']));
			setOption('copyright_image_rightsholder', sanitize($_POST['copyright_image_rightsholder']));
			setOption('copyright_image_rightsholder_custom', sanitize($_POST['copyright_image_rightsholder_custom']));

			setOption('sharpen_amount', sanitize_numeric($_POST['sharpen_amount']));
			setOption('image_max_size', sanitize_numeric($_POST['image_max_size']));
			$num = str_replace(',', '.', sanitize($_POST['sharpen_radius']));
			if (is_numeric($num))
				setOption('sharpen_radius', $num);
			setOption('sharpen_threshold', sanitize_numeric($_POST['sharpen_threshold']));

			if (isset($_POST['fullimage_watermark'])) {
				$new = sanitize($_POST['fullimage_watermark'], 3);
				setOption('fullimage_watermark', $new);
			}
			if (isset($_POST['fullsizeimage_watermark'])) {
				$new = sanitize($_POST['fullsizeimage_watermark'], 3);
				setOption('fullsizeimage_watermark', $new);
			}

			setOption('watermark_scale', sanitize($_POST['watermark_scale'], 3));
			setOption('watermark_h_offset', sanitize($_POST['watermark_h_offset'], 3));
			setOption('watermark_w_offset', sanitize($_POST['watermark_w_offset'], 3));
			setOption('image_cache_suffix', sanitize($_POST['image_cache_suffix']));

			$imageplugins = array_unique($_zp_extra_filetypes);
			$imageplugins[] = 'Image';
			foreach ($imageplugins as $plugin) {
				$opt = $plugin . '_watermark';
				if (isset($_POST[$opt])) {
					$new = sanitize($_POST[$opt], 3);
					setOption($opt, $new);
				}
			}

			setOption('full_image_quality', sanitize($_POST['full_image_quality'], 3));
			setOption('cache_full_image', (int) isset($_POST['cache_full_image']));
			setOption('protect_full_image', sanitize($_POST['protect_full_image'], 3));
			setOption('imageProcessorConcurrency', $_POST['imageProcessorConcurrency']);
			$notify = processCredentials('protected_image');

			setOption('secure_image_processor', (int) isset($_POST['secure_image_processor']));
			if (isset($_POST['protected_image_cache'])) {
				setOption('protected_image_cache', 1);
				copy(SERVERPATH . '/' . ZENFOLDER . '/file-templates/cacheprotect', SERVERPATH . '/' . CACHEFOLDER . '/.htaccess');
				@chmod(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess', 0444);
			} else {
				@chmod(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess', 0777);
				@unlink(SERVERPATH . '/' . CACHEFOLDER . '/.htaccess');
				setOption('protected_image_cache', 0);
			}
			setOption('hotlink_protection', (int) isset($_POST['hotlink_protection']));
			setOption('use_lock_image', (int) isset($_POST['use_lock_image']));
			$st = sanitize($_POST['image_sorttype'], 3);
			if ($st == 'custom') {
				$st = unQuote(strtolower(sanitize($_POST['customimagesort'], 3)));
			}
			setOption('image_sorttype', $st);
			setOption('image_sortdirection', (int) isset($_POST['image_sortdirection']));
			setOption('use_embedded_thumb', (int) isset($_POST['use_embedded_thumb']));
			setOption('IPTC_encoding', sanitize($_POST['IPTC_encoding']));
   setOption('IPTC_convert_linebreaks', (int) isset($_POST['IPTC_convert_linebreaks']));
			foreach ($_zp_exifvars as $key => $item) {
				$v = sanitize_numeric($_POST[$key]);
				switch ($v) {
					case 0:
					case 1:
						setOption($key . '-disabled', 0);
						setOption($key, $v);
						break;
					case 2:
						setOption($key, 0);
						setOption($key . '-disabled', 1);
						break;
				}
			}
			$returntab = "&tab=image";
		}
		/*		 * * Theme options ** */
		if (isset($_POST['savethemeoptions'])) {
			$themename = sanitize($_POST['optiontheme'], 3);
			$returntab = "&tab=theme";
			if ($themename)
				$returntab .= '&optiontheme=' . $themename;
			// all theme specific options are custom options, handled below
			if (!isset($_POST['themealbum']) || empty($_POST['themealbum'])) {
				$themeswitch = urldecode(sanitize_path($_POST['old_themealbum'])) != '';
			} else {
				$alb = urldecode(sanitize_path($_POST['themealbum']));
				$themealbum = $table = AlbumBase::newAlbum($alb);
				if ($themealbum->exists) {
					$table = $themealbum;
					$returntab .= '&themealbum=' . html_encode(pathurlencode($alb)) . '&tab=theme';
					$themeswitch = $alb != urldecode(sanitize_path($_POST['old_themealbum']));
				} else {
					$themealbum = NULL;
				}
			}

			if ($themeswitch) {
				$notify = '?switched';
			} else {
				if ($_POST['savethemeoptions'] == 'reset') {
					$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `theme`=' . $_zp_db->quote($themename);
					if ($themealbum) {
						$sql .= ' AND `ownerid`=' . $themealbum->getID();
					} else {
						$sql .= ' AND `ownerid`=0';
					}
					$_zp_db->query($sql);
					$themeswitch = true;
				} else {
					$ncw = $cw = getThemeOption('thumb_crop_width', $table, $themename);
					$nch = $ch = getThemeOption('thumb_crop_height', $table, $themename);
					if (isset($_POST['image_size']))
						setThemeOption('image_size', sanitize_numeric($_POST['image_size']), $table, $themename);
					if (isset($_POST['image_use_side']))
						setThemeOption('image_use_side', sanitize($_POST['image_use_side']), $table, $themename);
					if (isset($_POST['thumb_use_side']))
						setThemeOption('thumb_use_side', sanitize($_POST['thumb_use_side']), $table, $themename);
					setThemeOption('thumb_crop', (int) isset($_POST['thumb_crop']), $table, $themename);
					if (isset($_POST['thumb_size'])) {
						$ts = sanitize_numeric($_POST['thumb_size']);
						setThemeOption('thumb_size', $ts, $table, $themename);
					} else {
						$ts = getThemeOption('thumb_size', $table, $themename);
					}
					if (isset($_POST['thumb_crop_width'])) {
						if (is_numeric($_POST['thumb_crop_width'])) {
							$ncw = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_width']) / 100);
						}
						setThemeOption('thumb_crop_width', $ncw, $table, $themename);
					}
					if (isset($_POST['thumb_crop_height'])) {
						if (is_numeric($_POST['thumb_crop_height'])) {
							$nch = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_height']) / 100);
						}
						setThemeOption('thumb_crop_height', $nch, $table, $themename);
					}

					if (isset($_POST['albums_per_page'])) {
						$albums_per_page = sanitize_numeric($_POST['albums_per_page']);
						setThemeOption('albums_per_page', $albums_per_page, $table, $themename);
					}
					if (isset($_POST['images_per_page'])) {
						$images_per_page = sanitize_numeric($_POST['images_per_page']);
						setThemeOption('images_per_page', $images_per_page, $table, $themename);
					}

					setThemeOption('thumb_transition', isset($_POST['thumb_transition']), $table, $themename);
					if (isset($_POST['thumb_transition_min'])) {
						setThemeOption('thumb_transition_min', max(1, sanitize_numeric($_POST['thumb_transition_min'])), $table, $themename);
					}
					if (isset($_POST['thumb_transition_max'])) {
						setThemeOption('thumb_transition_max', max(1, sanitize_numeric($_POST['thumb_transition_max'])), $table, $themename);
					}

					if (isset($_POST['custom_index_page']))
						setThemeOption('custom_index_page', sanitize($_POST['custom_index_page'], 3), $table, $themename);
					$otg = getThemeOption('thumb_gray', $table, $themename);
					setThemeOption('thumb_gray', (int) isset($_POST['thumb_gray']), $table, $themename);
					if ($otg = getThemeOption('thumb_gray', $table, $themename))
						$wmo = 99; // force cache clear
					$oig = getThemeOption('image_gray', $table, $themename);
					setThemeOption('image_gray', (int) isset($_POST['image_gray']), $table, $themename);
					if ($oig = getThemeOption('image_gray', $table, $themename))
						$wmo = 99; // force cache clear
					if ($nch != $ch || $ncw != $cw) { // the crop height/width has been changed
						$sql = 'UPDATE ' . $_zp_db->prefix('images') . ' SET `thumbX`=NULL,`thumbY`=NULL,`thumbW`=NULL,`thumbH`=NULL WHERE `thumbY` IS NOT NULL';
						$_zp_db->query($sql);
						$wmo = 99; // force cache clear as well.
					}
				}
			}
		}
		/*		 * * Plugin Options ** */
		if (isset($_POST['savepluginoptions'])) {
			if (isset($_POST['checkForPostTruncation'])) {
				// all plugin options are handled by the custom option code.
				if (isset($_GET['single'])) {
					$returntab = "&tab=plugin&single=" . sanitize($_GET['single']);
				} else {
					$returntab = "&tab=plugin&pagenumber=$pagenumber";
				}
			} else {
				$notify = '?post_error';
			}
		}
		/*		 * * Security Options ** */
		if (isset($_POST['savesecurityoptions'])) {
			$protocol = sanitize($_POST['server_protocol'], 3);
			if ($protocol != SERVER_PROTOCOL) {
				// force https if required to be sure it works, otherwise the "save" will be the last thing we do
				httpsRedirect();
			}
			if (getOption('server_protocol') != $protocol) {
				setOption('server_protocol', $protocol);
				$_zp_mutex->lock();
				$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
				$zp_cfg = updateConfigItem('server_protocol', $protocol, $zp_cfg);
				storeConfig($zp_cfg);
				$_zp_mutex->unlock();
			}

			$_zp_gallery->setUserLogonField(isset($_POST['login_user_field']));
			if ($protocol == 'http') {
				zp_clearCookie("zpcms_ssl");
			}
			setOption('IP_tied_cookies', (int) isset($_POST['IP_tied_cookies']));
			setOption('obfuscate_cache', (int) isset($_POST['obfuscate_cache']));
			setOption('image_processor_flooding_protection', (int) isset($_POST['image_processor_flooding_protection']));
			$_zp_gallery->save();
			setOption('anonymize_ip', sanitize_numeric($_POST['anonymize_ip']));
			setOption('dataprivacy_policy_notice', process_language_string_save('dataprivacy_policy_notice', 3));
			setOption('dataprivacy_policy_custompage', sanitize($_POST['dataprivacy_policy_custompage']));
			if(extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
				setOption('dataprivacy_policy_zenpage', sanitize($_POST['dataprivacy_policy_zenpage']));
			}
			setOption('dataprivacy_policy_customlinktext', process_language_string_save('dataprivacy_policy_customlinktext', 3));
			$returntab = "&tab=security";
		}
		/*		 * * custom options ** */
		if (!$themeswitch) { // was really a save.
			$returntab = processCustomOptionSave($returntab, $themename, $themealbum);
		}

		if (empty($notify))
			$notify = '?saved';
		redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php' . $notify . $returntab);
	}
}
printAdminHeader($_current_tab);
?>
<script src="js/farbtastic.js"></script>
<link rel="stylesheet" href="js/farbtastic.css" type="text/css" />
<?php
if ($_zp_admin_current_subpage == 'gallery' || $_zp_admin_current_subpage == 'image') {
	if ($_zp_admin_current_subpage == 'image') {
		$table = 'images';
		$targetid = 'customimagesort';
	} else {
		$table = 'albums';
		$targetid = 'customalbumsort';
	}
	$result = $_zp_db->getFields($table);
	$dbfields = array();
	if ($result) {
		foreach ($result as $row) {
			$dbfields[] = "'" . $row['Field'] . "'";
		}
		sort($dbfields);
	}
	?>
	<script src="js/encoder.js"></script>
	<script src="js/tag.js"></script>
	<script>
						$(function () {
						$('#<?php echo $targetid; ?>').tagSuggest({
						tags: [
	<?php echo implode(',', $dbfields); ?>
						]
						});
						});
	</script>
	<?php
}
zp_apply_filter('texteditor_config', 'zenphoto');
Authority::printPasswordFormJS();
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			/* Page code */
			?>
			<div id="container">
				<?php
				$subtab = getSubtabs();
				if (isset($_GET['tag_parse_error'])) {
					echo '<div class="errorbox fade-message">';
					echo "<h2>";
					if ($_GET['tag_parse_error'] === '0') {
						echo gettext("Forbidden tag.");
					} else {
						echo gettext("Your Allowed tags change did not parse successfully.");
					}
					echo "</h2>";
					echo '</div>';
				}
				if (isset($_GET['post_error'])) {
					echo '<div class="errorbox">';
					echo "<h2>" . gettext('Error') . "</h2>";
					echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
					echo '</div>';
				}
				if (isset($_GET['saved'])) {
					echo '<div class="messagebox fade-message">';
					echo "<h2>" . gettext("Applied") . "</h2>";
					echo '</div>';
				}
				if (isset($_GET['custom'])) {
					echo '<div class="errorbox">';
					echo '<h2>' . html_encode(sanitize($_GET['custom'])) . '</h2>';
					echo '</div>';
				}
				if (isset($_GET['missing'])) {
					echo '<div class="errorbox">';
					echo '<h2>' . gettext('Your browser did not post all the fields. Some options may not have been set.') . '</h2>';
					echo '</div>';
				}

				if (isset($_GET['mismatch'])) {
					echo '<div class="errorbox fade-message">';
					switch ($_GET['mismatch']) {
						case 'user':
							echo "<h2>" . sprintf(gettext("You must supply a password for the <em>%s</em> guest user"), html_encode(ucfirst($subtab))) . "</h2>";
							break;
						default:
							echo "<h2>" . gettext('Your passwords did not match.') . "</h2>";
							break;
					}
					echo '</div>';
				}

				if (isset($_GET['cookiepath']) && @$_COOKIE['zpcms_cookie_path'] != getOption('zpcms_cookie_path')) {
					setOption('zenphoto_cookie_path', NULL);
					?>
					<div class="errorbox">
						<h2><?php echo gettext('The path you selected resulted in cookies not being retrievable. It has been reset.'); ?></h2>
					</div>
					<?php
				}
				printSubtabs();

				if ($subtab == 'general' && zp_loggedin(OPTIONS_RIGHTS)) {
					?>
					<div id="tab_gallery" class="tabbox">
						<?php
						if (isset($_GET['local_failed'])) {
							$languages = array_flip(generateLanguageList('all'));
							$locale = sanitize($_GET['local_failed']);
							echo '<div class="errorbox">';
							echo "<h2>" .
							sprintf(gettext("<em>%s</em> is not available."), html_encode($languages[$locale])) .
							' ' . sprintf(gettext("The locale %s is not supported on your server."), html_encode($locale)) .
							'<br />' . gettext('See the troubleshooting guide on zenphoto.org for details.') .
							"</h2>";
							echo '</div>';
						}
						?>
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savegeneraloptions" value="yes" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<tr>
								<?php
									$offset = timezoneDiff($_zp_server_timezone, $tz = getOption('time_zone'));
									?>
									<td width="175"><?php echo gettext("Time zone:"); ?></td>
									<td width="350">
										<?php
										$zones = getTimezones();
										?>
										<select id="time_zone" name="time_zone">
											<option value="" style="background-color:LightGray"><?php echo gettext('*not specified'); ?></option>
											<?php generateListFromArray(array($tz), $zones, false, false); ?>
										</select>
									</td>
									<td>
										<p><?php printf(gettext('Your server reports its time zone as: <code>%s</code>.'), $_zp_server_timezone); ?></p>
										<p><?php printf(gettext('Your time zone offset in hours is: %d. If your time zone is different from the servers, select the correct time zone here.'), $offset); ?></p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("URL options:"); ?></td>
									<td width="350">
										<p>
											<label>
												<?php
												if (MOD_REWRITE) {
													$state = ' checked="checked"';
												} else {
													$state = '';
												}
												?>
												<input type="checkbox" name="mod_rewrite" value="1"<?php echo $state; ?> />
												<?php echo gettext('mod rewrite'); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="UTF8_image_URI" value="1"<?php checked('1', UTF8_IMAGE_URI); ?> />
												<?php echo gettext('UTF8 image URIs'); ?>
											</label>
										</p>
										<p><?php echo gettext("mod_rewrite suffix:"); ?> <input type="text" size="10" name="mod_rewrite_image_suffix" value="<?php echo html_encode(getOption('mod_rewrite_image_suffix')); ?>" /></p>
									</td>
									<td>
										<p>
											<?php echo gettext("If you have Apache <em>mod rewrite</em> (or equivalent), put a checkmark on the <em>mod rewrite</em> option and you will get nice cruft-free URLs."); ?>
											<?php echo sprintf(gettext('The <em>tokens</em> used in rewritten URIs may be altered to your taste. See the <a href="%s">plugin options</a> for <code>rewriteTokens</code>.'), WEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=plugin&single=rewriteTokens'); ?>
											<?php
											if (!getOption('mod_rewrite_detected'))
												echo '<p class="notebox">' . gettext('Setup did not detect a working <em>mod_rewrite</em> facility. Since this test is not 100% reliable this may be a false report though.'), '</p>';
											?>
										</p>
										<p><?php echo gettext("If you are having problems with images whose names contain characters with diacritical marks try changing the <em>UTF8 image URIs</em> setting."); ?></p>
										<p><?php echo gettext("If <em>mod_rewrite</em> is checked above, zenphoto will append the <em>mod_rewrite suffix</em> to the end of image URLs. (This helps search engines.) Examples: <em>.html, .php</em>, etc."); ?></p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Language:"); ?></td>
									<td width="350">
										<?php
										$currentValue = SITE_LOCALE;
										?>
										<br />
										<ul class="languagelist">
											<?php
											$systemlocales = getSystemLocales(true);
											$locales = generateLanguageList('all');
											$locales[gettext("HTTP_Accept_Language")] = '';
											ksort($locales, SORT_LOCALE_STRING);
											$vers = explode('-', ZENPHOTO_VERSION);
											$vers = explode('.', $vers[0]);
											while (count($vers) < 3) {
												$vers[] = 0;
											}
											$zpversion = $vers[0] . '.' . $vers[1] . '.' . $vers[2];
											$c = 0;
											foreach ($locales as $language => $dirname) {
												$languageAlt = $language;
												$class = '';
												if(!empty($systemlocales) && !in_array($dirname, $systemlocales) && $language != 'HTTP_Accept_Language') {
													$language = '<img src="'. WEBPATH . '/'. ZENFOLDER .'/images/action.png" alt="'. gettext('Locale not installed'). '"> ' . $language;
												}
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
													if (version_compare($version, $zpversion) < 0) {
														if (empty($version)) {
															$version = '0.0.0';
														}
														$language .= ' <small>{v' . $version . '}</small>';
														$languageAlt .= ' {v' . $version . '}';
														$class = ' style="background-color:#FFEFB7;"';
													}
												} else {
													$version = $zpversion;
												}
												if (empty($dirname)) {
													$flag = WEBPATH . '/' . ZENFOLDER . '/locale/auto.png';
												} else {
													$flag = getLanguageFlag($dirname);
												}
												if (getOption('unsupported_' . $dirname)) {
													$c_attrs = $r_attrs = ' disabled="disabled"';
												} else {
													if (getOption('disallow_' . $dirname)) {
														$c_attrs = '';
														$r_attrs = ' disabled="disabled"';
													} else {
														$c_attrs = ' checked="checked"';
														$r_attrs = '';
													}
												}

												if ($dirname == $currentValue) {
													$r_attrs = ' checked="checked"';
													$c_attrs = ' checked="checked" disabled="disabled"';
													$ci = $c;
												}
												$c++;
												?>
												<li<?php echo $class; ?>>
													<label class="displayinline" >
														<input type="radio" name="locale" id="r_<?php echo $dirname; ?>" value="<?php echo $dirname; ?>"
																	 onclick="javascript:radio_click('<?php echo $dirname; ?>');" <?php echo $r_attrs; ?>/>
													</label>
													<label class="displayinline flags">
														<input id="language_allow_<?php echo $dirname; ?>" name="language_allow_<?php echo $dirname; ?>" type="checkbox"
																	 value="<?php echo $dirname; ?>"<?php echo $c_attrs; ?>
																	 onclick="javascript:enable_click('<?php echo $dirname; ?>');" />
														<img src="<?php echo $flag; ?>" alt="<?php echo $languageAlt; ?>" width="24" height="16" />
														<?php echo $language; ?>
													</label>
												</li>
												<?php
											}
											?>
										</ul>
										<script>
																			var oldselect = '<?php echo $currentValue; ?>';
																			function radio_click(id) {
																			if ($('#r_' + id).prop('checked')) {
																			$('#language_allow_' + oldselect).removeAttr('disabled');
																							oldselect = id;
																							$('#language_allow_' + id).attr('disabled', 'disabled');
																			}
																			}
															function enable_click(id) {
															if ($('#language_allow_' + id).prop('checked')) {
															$('#r_' + id).removeAttr('disabled');
															} else {
															$('#r_' + id).attr('disabled', 'disabled');
															}
															}
															$(document).ready(function(){
															$('ul.languagelist').scrollTo('li:eq(<?php echo ($ci - 2); ?>)');
															});</script>
										<br class="clearall" />
										<p class="notebox"><?php printf(gettext('Highlighted languages are not current with Zenphoto Version %1$s. (The version Zenphoto of the out-of-date language is shown in braces.) Please check the <a href="%2$s">translation repository</a> for new and updated language translations.'), $zpversion, 'https://github.com/zenphoto/zenphoto/tree/master/zp-core/locale'); ?></p>
										<?php if(!empty($systemlocales)) { // if class ResourceBundle does not exist this has no meaning ?>
											<p class="notebox"><?php printf(gettext('Languages marked with the %1$s icon have no matching locale installed on the server and therefore will not work. This is a technical requirement by native <a href="%2$s">PHP gettext</a>.'), '<img src="'. WEBPATH . '/'. ZENFOLDER .'/images/action.png" alt="'. gettext('Locale not installed'). '">', 'https://www.php.net/manual/en/book.gettext.php'); ?></p>
										<?php } ?>

										<label class="checkboxlabel">
											<input type="checkbox" name="multi_lingual" value="1"	<?php checked('1', getOption('multi_lingual')); ?> /><?php echo gettext('Multi-lingual'); ?>
										</label>
									</td>
									<td>
										<p><?php echo gettext("You can disable languages by unchecking their checkboxes. Only checked languages will be available to the installation."); ?></p>
										<p><?php echo gettext("Select the preferred language to display text in. (Set to <em>HTTP_Accept_Language</em> to use the language preference specified by the viewer’s browser.)"); ?></p>
										<p><?php echo gettext("Set <em>Multi-lingual</em> to enable multiple language input for options that provide theme text."); ?></p>

									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Date and time formats:"); ?></td>
									<td width="350">
											<?php
											printDatetimeFormatSelector();
											$use_localized_date = getOption('date_format_localized');
											$time_display_disabled = getOption('time_display_disabled');
										?>
										<p>
											<label class="checkboxlabel">
												<input type="checkbox" name="date_format_localized" value="1"	<?php checked('1', $use_localized_date); ?> /><?php echo gettext('Use localized dates'); ?>
											</label>
										</p>
										<p>	
											<label class="checkboxlabel">
												<input type="checkbox" name="time_display_disabled" value="1"	<?php checked('1', $time_display_disabled); ?> /><?php echo gettext('Disable time for display'); ?>
											</label>
										</p>
									</td>
									<td>
										<p><?php echo gettext('Formats for date and time. Select from the lists or set to <code>custom</code> and provide a <a href="https://www.php.net/manual/en/datetime.format.php">datetime</a> format string for date and time in the custom boxes.'); ?></p>
										<p><?php echo gettext('If time is disabled for display standard theme and admin functions will not display it.'); ?></p>
									<?php if (extension_loaded('intl')) { ?>
										<p class="notebox">
										<?php echo gettext('NOTE: If localized dates are enabled and you are using a custom date format you need to provide an <a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/">ICU dateformat string</a>.'); ?>
									</p>
								<?php } else { ?>
									<p class="warningbox">
										<?php echo gettext('The intl PHP extension is not installed and localized dates are not available on your system.'); ?>
									</p>
								<?php } ?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Charset:"); ?></td>
									<td width="350">
										<select id="charset" name="charset">
											<?php
											$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
											$totalsets = $_zp_utf8->charsets;
											asort($totalsets);
											foreach ($totalsets as $key => $char) {
												?>
												<option value="<?php echo $key; ?>" <?php
												if ($key == LOCAL_CHARSET)
													echo 'selected="selected"';
												if (!array_key_exists($key, $sets))
													echo 'style="color: gray"';
												?>><?php echo $char; ?></option>
																<?php
															}
															?>
										</select>
									</td>
									<td>
										<?php
										echo gettext('The character encoding to use internally. Leave at <em>Unicode (UTF-8)</em> if you are unsure.');
										if (!function_exists('mb_list_encodings')) {
											echo ' ' . gettext('Character sets <span style="color:gray">shown in gray</span> have no character translation support.');
										}
										?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Filesystem Charset:"); ?></td>
									<td width="350">
										<select id="filesystem_charset" name="filesystem_charset">
											<?php
											$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
											$totalsets = $_zp_utf8->charsets;
											asort($totalsets);
											foreach ($totalsets as $key => $char) {
												?>
												<option value="<?php echo $key; ?>" <?php
												if ($key == FILESYSTEM_CHARSET)
													echo 'selected="selected"';
												if (!array_key_exists($key, $sets))
													echo 'style="color: gray"';
												?>><?php echo $char; ?></option>
																<?php
															}
															?>
										</select>
									</td>
									<td>
										<?php
										echo gettext('The character encoding to use for the filesystem. Leave at <em>Unicode (UTF-8)</em> if you are unsure.');
										if (!function_exists('mb_list_encodings')) {
											echo ' ' . gettext('Character sets <span style="color:gray">shown in gray</span> have no character translation support.');
										}
										?>
									</td>
								</tr>

								<tr>
									<td width="175"><?php echo gettext("Allowed tags:"); ?></td>
									<td width="350">
										<p><textarea name="allowed_tags" id="allowed_tags" style="width: 340px" rows="10" cols="35"><?php echo html_encode(getOption('allowed_tags')); ?></textarea></p>
									</td>
									<td>
										<script>
																							function resetallowedtags() {
																							$('#allowed_tags').val(<?php
									$t = getOption('allowed_tags_default');
									$tags = explode("\n", $t);
									$c = 0;
									foreach ($tags as $t) {
										$t = trim($t);
										if (!empty($t)) {
											if ($c > 0) {
												echo '+';
												echo "\n";
												?>
				<?php
			}
			$c++;
			echo "'" . $t . '\'+"\n"';
		}
	}
	?>);
																							}
										</script>
										<p><?php echo gettext("Tags and attributes allowed in comments, descriptions, and other fields."); ?><p>
										<p><?php echo gettext("Follow the form: <em>tag</em> =&gt; (<em>attribute</em> =&gt;() <em>attribute</em>=&gt;() <em>attribute</em> =&gt;()....etc.)"); ?></p>
										<?php if (EDITOR_SANITIZE_LEVEL == 4) { ?>
											<p class="notebox"><?php echo gettext('<strong>Note:</strong> visual editing is enabled so the editor overrides these settings on tags where it is active.'); ?></p>
										<?php } ?>
										<p class="buttons">
											<a href="javascript:resetallowedtags()" ><?php echo gettext('reset to default'); ?></a>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175">
										<?php echo gettext("Cookies:"); ?>
									</td>
									<td width="350">
										<?php
										if (!GALLERY_SESSION) {
											echo gettext('Path');
											?>
											<input type="text" size="48" id="zenphoto_cookie_path" name="zenphoto_cookie_path" value="<?php echo getOption('zenphoto_cookie_path'); ?>" />
											<p>
												<?php
												echo gettext('Duration');
												?>
												<input type="text" name="cookie_persistence" value="<?php echo COOKIE_PERSISTENCE; ?>" />
											</p>
											<?php
										}
										?>
										<p>
											<label>
												<input type="checkbox" name="album_session" id="album_session" value="1" <?php checked('1', GALLERY_SESSION); ?> />
												<?php echo gettext("enable gallery sessions"); ?>
											</label>
										</p>
									</td>
									<td>
										<?php
										if (!GALLERY_SESSION) {
											?>
											<p><?php printf(gettext('The <em>path</em> Zenphoto will use when storing cookies. (Leave empty to default to <em>%s</em>)'), WEBPATH); ?></p>
											<p><?php echo gettext("Set to the time in seconds that cookies should be kept by browsers."); ?></p>
											<?php
										}
										?>
										<p><?php echo gettext('If this option is selected Zenphoto will use <a href="https://www.php.net/manual/en/intro.session.php">PHP sessions</a> instead of cookies to make visitor settings persistent.'); ?></p>
										<p class="notebox"><?php echo gettext('<strong>NOTE</strong>: Sessions will normally close when the browser closes causing all password and other data to be discarded. They may close more frequently depending on the runtime configuration. Longer <em>lifetime</em> of sessions is generally more conducive to a pleasant user experience. Cookies are the prefered storage option since their duration is determined by the <em>Cookie duration</em> option. ') ?>
									</td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext("Name:"); ?></p>
										<p><?php echo gettext("Email:"); ?></p>
									</td>
									<td width="350">
										<p><?php print_language_string_list(getOption('site_email_name'), 'site_email_name'); ?></p>
										<p><input type="text" size="48" id="site_email" name="site_email" value="<?php echo getOption('site_email'); ?>" /></p>
									</td>
									<td><?php echo gettext("This email name and address will be used as the <em>From</em> address for all mails sent by Zenphoto."); ?></td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext("Users per page:"); ?></p>
										<p><?php echo gettext("Plugins per page:"); ?></p>
										<?php
										if (extensionEnabled('zenpage')) {
											?>
											<p><?php echo gettext("Articles per page:"); ?></p>
											<?php
										}
										?>
									</td>
									<td width="350">
										<input type="text" size="5" id="users_per_page" name="users_per_page" value="<?php echo getOption('users_per_page'); ?>" />
										<br />
										<input type="text" size="5" id="plugins_per_page" name="plugins_per_page" value="<?php echo getOption('plugins_per_page'); ?>" />
										<?php
										if (extensionEnabled('zenpage')) {
											?>
											<br />
											<input type="text" size="5" id="articles_per_page" name="articles_per_page" value="<?php echo getOption('articles_per_page'); ?>" />
											<?php
										}
										?>
									</td>
									<td><?php echo gettext('These options control the number of items displayed on their tabs. If you have problems using these tabs, reduce the number shown here.'); ?></td>
								</tr>
								<?php
								$subtabs = array('security' => gettext('security'), 'debug' => gettext('debug'));
								?>
								<tr>
									<td width="175">
										<?php
										foreach ($subtabs as $subtab => $log) {
											if (!is_null(getOption($subtab . '_log_size'))) {
												printf(gettext('<p>%s log limit</p>'), $log);
											}
										}
										?>
									</td>
									<td width="350">
										<?php
										foreach ($subtabs as $subtab => $log) {
											if (!is_null($size = getOption($subtab . '_log_size'))) {
												?>
												<p>
													<input type="text" size="4" id="<?php echo $log ?>_log" name="log_size_<?php echo $subtab; ?>" value="<?php echo $size; ?>" />
													<input type="checkbox" id="<?php echo $log ?>_log" name="log_mail_<?php echo $subtab; ?>" value="1" <?php checked('1', getOption($subtab . '_log_mail')); ?> /> <?php echo gettext('e-mail when exceeded'); ?>
												</p>
												<?php
											}
										}
										?>
									</td>
									<td><?php echo gettext('Logs will be "rolled" over when they exceed the specified size. If checked, the administrator will be e-mailed when this occurs.') ?></td>
								</tr>

								<tr>
									<td width="175">
										<?php echo gettext("Daily logs:"); ?></p

									</td>
									<td width="350">
										<label>
											<input type="checkbox" id="daily_logs" name="daily_logs" value="1" <?php checked('1', getOption('daily_logs')); ?> /> <?php echo gettext('Enable daily logs'); ?>
										</label>
									</td>
									<td><?php echo gettext('If checked logs will be created daily by appending the dateformat YYYY-mm-dd to the filename.'); ?></td>
								</tr>

								<?php zp_apply_filter('admin_general_data'); ?>
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>

							</table>
						</form>
					</div>
					<!-- end of tab-general div -->
					<?php
				}
				if ($subtab == 'gallery' && zp_loggedin(OPTIONS_RIGHTS)) {
					codeblocktabsJS();
					?>
					<div id="tab_gallery" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savegalleryoptions" value="yes" />
							<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Gallery title:"); ?></td>
									<td width="350">
										<?php print_language_string_list($_zp_gallery->getTitle('all'), 'gallery_title'); ?>
									</td>
									<td><?php echo gettext("What you want to call your Zenphoto site."); ?></td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Gallery description:"); ?></td>
									<td width="350">
										<?php print_language_string_list($_zp_gallery->getDesc('all'), 'Gallery_description', true, NULL, 'texteditor'); ?>
									</td>
									<td><?php echo gettext("A brief description of your gallery. Some themes may display this text."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright notice'); ?></td>
									<td>
										<p><?php print_language_string_list($_zp_gallery->getCopyrightNotice('all'), 'copyright_site_notice'); ?> <?php echo gettext('Notice'); ?></p>

									</td>
									<td>
										<p><?php echo gettext('The notice will be used by the html_meta_tags plugin. If not set the image meta data is tried instead.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright URL'); ?></td>
									<td>
									<?php printZenpagePageSelector('copyright_site_url', 'copyright_site_url_custom', false, true); ?>
									</td>
									<td>
										<p><?php echo gettext('Choose a Zenpage page or define a custom URL. The URL maybe used to point to some specific copyright info source. Must be an absolute URL address of the form: http://mydomain.com/license.html.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext("Display copyright notice"); ?></td>
									<td>
										<label><input type="checkbox" name="display_copyright_notice" id="display_copyright_notice" value="1" <?php checked('1', getOption('display_copyright_notice')); ?> /> <?php echo gettext('Enable'); ?></label>
									</td>
									<td><?php echo gettext("Enable to display the copyright notice. This may usually be in the theme footer but is up to the theme."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright rightsholder'); ?></td>
									<td>
										<?php printUserSelector('copyright_site_rightsholder','copyright_site_rightsholder_custom', 'users', true); ?>
									</td>
									<td>
										<p><?php echo gettext('The rights holder will be used by the html_meta_tags plugin. If set to <em>none</em> the image metadata fields "copyright" or "owner" are used as fallbacks, if available.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Gallery type'); ?></td>
									<td>
										<label><input type="radio" name="gallery_security" value="public" alt="<?php echo gettext('public'); ?>"<?php if (GALLERY_SECURITY == 'public') echo ' checked="checked"' ?> onclick="javascript:$('.public_gallery').show();" /><?php echo gettext('public'); ?></label>
										<label><input type="radio" name="gallery_security" value="private" alt="<?php echo gettext('private'); ?>"<?php if (GALLERY_SECURITY != 'public') echo 'checked="checked"' ?> onclick="javascript:$('.public_gallery').hide();" /><?php echo gettext('private'); ?></label>
									</td>
									<td>
										<?php echo gettext('Private galleries are viewable only by registered users.'); ?>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									?>
									<tr class="passwordextrashow public_gallery">
										<td style="background-color: #ECF1F2;">
											<p>
												<a href="javascript:toggle_passwords('',true);">
													<?php echo gettext("Gallery password:"); ?>
												</a>
											</p>
										</td>
										<td style="background-color: #ECF1F2;">
											<p>
											<?php
											$x = $_zp_gallery->getPassword();
											if (empty($x)) {
												?>
												<img src="images/lock_open.png" />
												<?php
											} else {
												$x = '          ';
												?>
												<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
												<?php
											}
											?>
											</p>
										</td>
										<td style="background-color: #ECF1F2;">
											<p>
												<?php echo gettext("Master password for the gallery. Click on <em>Gallery password</em> to change."); ?>
											</p>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none">
										<td>
											<p>
											<a href="javascript:toggle_passwords('',false);">
												<?php echo gettext("Gallery guest user:"); ?>
											</a>
											</p>
										</td>
										<td>
											<p>
											<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 onkeydown="passwordClear('');"
														 id="user_name" name="user"
														 value="<?php echo html_encode($_zp_gallery->getUser()); ?>" />
											</p>

										</td>
										<td>
											<?php echo gettext("User ID for the gallery guest user") ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<span id="strength">
												<?php echo gettext("Gallery password:"); ?>
											</span>
											<br />
											<span id="match" class="password_field_">
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext("(repeat)"); ?>
											</span>
										</td>
										<td>
											<?php
											// Autofill honeypot hack (hidden password input),
											// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
											// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
											?>
											<input class="dirtyignore" type="password" name="pass" style="display:none;" />
											<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 id="pass" name="pass"
														 onkeydown="passwordClear('');"
														 onkeyup="passwordStrength('');"
														 value="<?php echo $x; ?>" autocomplete="off" />
											<br />
											<span class="password_field_">
												<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
															 class="dirtyignore"
															 id="pass_r" name="pass_r" disabled="disabled"
															 onkeydown="passwordClear('');"
															 onkeyup="passwordMatch('');"
															 value="<?php echo $x; ?>" autocomplete="off" />
											</span>
											<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear(''); togglePassword('');" /><?php echo gettext('Show password'); ?></label>

										</td>
										<td>
											<?php echo gettext("Master password for the gallery. If this is set, visitors must know this password to view the gallery."); ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<?php echo gettext("Gallery password hint:"); ?>
										</td>
										<td>
											<?php print_language_string_list($_zp_gallery->getPasswordHint('all'), 'hint', false, NULL, 'hint'); ?>
										</td>
										<td>
											<?php echo gettext("A reminder hint for the password."); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td><?php echo gettext('Unprotected pages:'); ?></td>
									<td>
										<?php
										$curdir = getcwd();
										$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $_zp_gallery->getCurrentTheme() . '/';
										chdir($root);
										$filelist = safe_glob('*.php');
										$list = array();
										foreach ($filelist as $file) {
											$file = filesystemToInternal($file);
											$list[$file] = str_replace('.php', '', $file);
										}
										chdir($curdir);
										$list = array_diff($list, standardScripts());
										$list['index.php'] = 'index';
										$current = array();
										foreach ($list as $page) {
											?>
											<input type="hidden" name="gallery-page_<?php echo $page; ?>" value="0" />
											<?php
											if ($_zp_gallery->isUnprotectedPage($page)) {
												$current[] = $page;
											}
										}
										?>
										<ul class="shortchecklist">
											<?php generateUnorderedListFromArray($current, $list, 'gallery_page_unprotected_', false, true, true); ?>
										</ul>
									</td>
									<td><?php echo gettext('Place a checkmark on any page scripts which should not be protected by the gallery password.'); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Parent website title:"); ?></td>
									<td>
										<?php print_language_string_list($_zp_gallery->getParentSiteTitle('all'), 'website_title'); ?>
									</td>
									<td><?php echo gettext("Your parent website title for use in e.g. breadcrumbs if you use Zenphoto as part of a bigger site run by another CMS. Not needed on plain Zenphoto sites."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Parent website URL:"); ?></td>
									<td><input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" name="website_url"
														 value="<?php echo html_encode($_zp_gallery->getParentSiteURL()); ?>" /></td>
									<td><?php echo gettext("This URL is used to link back to your parent website in e.g. breadcrumbs, but your theme must support it. Not needed on plain Zenphoto sites."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Album thumbnails:"); ?></td>
									<td>
										<?php
										$selections = array();
										foreach ($_zp_albumthumb_selector as $key => $selection) {
											$selections[$selection['desc']] = $key;
										}
										?>
										<select id="thumbselector" name="thumbselector">
											<?php
											generateListFromArray(array(getOption('AlbumThumbSelect')), $selections, false, true);
											?>
										</select>
									</td>
									<td><?php echo gettext("Default thumbnail selection for albums."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Sort gallery by:"); ?></td>
									<td>
										<?php
										$sort = getSortByOptions('albums');

										/*
										 * not recommended--screws with peoples minds during pagination!

										  $sort[gettext('Random')] = 'random';
										 */
										$cvt = $cv = strtolower($_zp_gallery->getSortType());
										ksort($sort, SORT_LOCALE_STRING);
										$flip = array_flip($sort);
										if (isset($flip[$cv])) {
											$dspc = 'none';
										} else {
											$dspc = 'block';
										}
										if (($cv == 'manual') || ($cv == 'random') || ($cv == '')) {
											$dspd = 'none';
										} else {
											$dspd = 'block';
										}
										?>
										<table>
											<tr>
												<td>
													<select id="gallerysortselect" name="gallery_sorttype" onchange="update_direction(this, 'gallery_sortdirection', 'customTextBox2')">
														<?php
														if (array_search($cv, $sort) === false) {
															$cv = 'custom';
															$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
														}
														generateListFromArray(array($cv), $sort, false, true);
														?>
													</select>
												</td>
												<td>
													<span id="gallery_sortdirection" style="display:<?php echo $dspd; ?>">
														<label>
															<input type="checkbox" name="gallery_sortdirection"	value="1" <?php checked('1', $_zp_gallery->getSortDirection()); ?> />
															<?php echo gettext("Descending"); ?>
														</label>
													</span>
												</td>
											</tr>

										</table>
									</td>
									<td>
										<?php
										echo gettext('Sort order for all albums and subalbums.');
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Gallery behavior:"); ?></td>
									<td>
										<p>
											<label>
												<input type="checkbox" name="album_default"	value="1"<?php if ($_zp_gallery->getAlbumPublish()) echo ' checked="checked"'; ?> />
												<?php echo gettext("Publish albums by default"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="image_default"	value="1"<?php if ($_zp_gallery->getImagePublish()) echo ' checked="checked"'; ?> />
												<?php echo gettext("Publish images by default"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="album_use_new_image_date" id="album_use_new_image_date"
															 value="1" <?php checked('1', $_zp_gallery->getAlbumUseImagedate()); ?> />
															 <?php echo gettext("use latest image date as album date"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="thumb_select_images" id="thumb_select_images"
															 value="1" <?php checked('1', $_zp_gallery->getThumbSelectImages()); ?> />
															 <?php echo gettext("visual thumb selection"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="multilevel_thumb_select_images" id="thumb_select_images"
															 value="1" <?php checked('1', $_zp_gallery->getSecondLevelThumbs()); ?> />
															 <?php echo gettext("show subalbum thumbs"); ?>
											</label>
										</p>
									</td>
									<td>
										<p><?php echo gettext("<a href=\"javascript:toggle('albumpub');\" >Details</a> for <em>publish albums by default</em>"); ?></p>
										<div id="albumpub" style="display: none">
											<p><?php echo gettext("This sets the default behavior for when Zenphoto discovers an album. If checked, the album will be published, if unchecked it will be unpublished.") ?></p>
										</div>
										<p><?php echo gettext("<a href=\"javascript:toggle('imagepub');\" >Details</a> for <em>publish images by default</em>"); ?></p>
										<div id="imagepub" style="display: none">
											<p><?php echo gettext("This sets the default behavior for when Zenphoto discovers an image. If checked, the image will be published, if unchecked it will be unpublished.") ?></p>
										</div>
										<p><?php echo gettext("<a href=\"javascript:toggle('albumdate');\" >Details</a> for <em>use latest image date as album date</em>"); ?></p>
										<div id="albumdate" style="display: none">
											<p>
												<?php echo gettext("If you wish your album date to reflect the date of the latest image uploaded set this option. Otherwise the date will be set initially to the date the album was created.") ?>
											</p>
											<p class="notebox">
												<?php echo gettext('<strong>NOTE</strong>: Zenphoto will update the album date only if an image is discovered which is newer than the current date of the album.<br>Be sure to refresh image metadata for this option to have effect.'); ?>
											</p>
										</div>

										<p><?php echo gettext("<a href=\"javascript:toggle('visualthumb');\" >Details</a> for <em>visual thumb selection</em>"); ?></p>
										<div id="visualthumb" style="display: none">
											<p><?php echo gettext("Setting this option places thumbnails in the album thumbnail selection list (the dropdown list on each album’s edit page). In Firefox the dropdown shows the thumbs, but in IE and Safari only the names are displayed (even if the thumbs are loaded!). In albums with many images loading these thumbs takes much time and is unnecessary when the browser will not display them. Uncheck this option and the images will not be loaded."); ?></p>
										</div>

										<p><?php echo gettext("<a href=\"javascript:toggle('multithumb');\" >Details</a> for <em>subalbum thumb selection</em>"); ?></p>
										<div id="multithumb" style="display: none">
											<p><?php echo gettext("Setting this option allows selecting images from subalbums as well as from the album. Naturally populating these images adds overhead. If your album edit tabs load too slowly, do not select this option."); ?></p>
										</div>

									</td>
								</tr>

								<tr valign="top">
									<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
									<td>
										<?php printCodeblockEdit($_zp_gallery, 0); ?>
									</td>
									<td>
									</td>
								</tr>

								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table>
						</form>
					</div>
					<!-- end of tab-gallery div -->
					<?php
				}
				if ($subtab == 'search' && zp_loggedin(OPTIONS_RIGHTS)) {
					?>
					<div id="tab_search" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savesearchoptions" value="yes" />
							<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									?>
									<tr class="passwordextrashow">
										<td width="175">
											<p>
												<a href="javascript:toggle_passwords('',true);">
													<?php echo gettext("Search password:"); ?>
												</a>
											</p>
										</td>
										<td>
											<?php
											$x = getOption('search_password');
											if (empty($x)) {
												?>
												<img src="images/lock_open.png" />
												<?php
											} else {
												$x = '          ';
												?>
												<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
												<?php
											}
											?>
										</td>
										<td>
											<p><?php echo gettext("Password for the search guest user. click on <em>Search password</em> to change."); ?></p>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none">
										<td width="175">
											<a href="javascript:toggle_passwords('',false);">
												<?php echo gettext("Search guest user:"); ?>
											</a>
										</td>
										<td>
											<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 onkeydown="passwordClear('');"
														 id="user_name" name="user"
														 value="<?php echo html_encode(getOption('search_user')); ?>" autocomplete="off" />
											<br />

										</td>
										<td>
											<?php echo gettext("User ID for the search guest user") ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<span id="strength">
												<?php echo gettext("Search password:"); ?>
											</span>
											<br />
											<span id="match" class="password_field_">
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext("(repeat)"); ?>
											</span>
										</td>
										<td>
											<?php
											// Autofill honeypot hack (hidden password input),
											// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
											// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
											?>
											<input class="dirtyignore" type="password" name="pass" style="display:none;" />
											<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 id="pass" name="pass"
														 onkeydown="passwordClear('');"
														 onkeyup="passwordStrength('');"
														 value="<?php echo $x; ?>" autocomplete="off" />
											<br />
											<span class="password_field_">
												<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
															 class="dirtyignore"
															 id="pass_r" name="pass_r" disabled="disabled"
															 onkeydown="passwordClear('');"
															 onkeyup="passwordMatch('');"
															 value="<?php echo $x; ?>" autocomplete="off" />
											</span>
											<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear(''); togglePassword('');" /><?php echo gettext('Show password'); ?></label>
										</td>
										<td>
											<?php echo gettext("Password for the search guest user. If this is set, visitors must know this password to view search results."); ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<?php echo gettext("Search password hint:"); ?>
										</td>
										<td>
											<?php print_language_string_list(getOption('search_hint'), 'hint', false, NULL, 'hint'); ?>
										</td>
										<td>
											<?php echo gettext("A reminder hint for the password."); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td><?php echo gettext("Search behavior settings:"); ?></td>
									<?php
									$engine = new SearchEngine();
									$fields = $engine->getSearchFieldList();
									$set_fields = $engine->allowedSearchFields();
									$fields = array_diff($fields, $set_fields);
									?>
								<script>
																					$(function() {
																					$("#resizable").resizable({
																					maxWidth: 350,
																									minWidth: 350, minHeight: 120,
																									resize: function(event, ui) {
																									$('#searchchecklist').height($('#resizable').height());
																									}
																					});
																					});</script>
								<td>
									<?php echo gettext('Fields list:'); ?>
									<div id="resizable">
										<ul class="searchchecklist" id="searchchecklist">
											<?php
											generateUnorderedListFromArray($set_fields, $set_fields, 'SEARCH_', false, true, true, 'search_fields');
											generateUnorderedListFromArray(array(), $fields, 'SEARCH_', false, true, true, 'search_fields');
											?>
										</ul>
										<div class="floatright">
											<label id="autocheck">
												<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.search_fields').prop('checked', checked);" />
												<span id="autotext"><?php echo gettext('all'); ?></span>
											</label>
										</div>
									</div>
									<br />
									<p>
										<?php
										echo gettext('String matching:');
										generateRadiobuttonsFromArray((int) getOption('exact_string_match'), array(gettext('<em>pattern</em>') => 0, gettext('<em>partial word</em>') => 1, gettext('<em>word</em>') => 2), 'string_match', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Tag matching:');
										generateRadiobuttonsFromArray((int) getOption('exact_tag_match'), array(gettext('<em>partial</em>') => 0, gettext('<em>word</em>') => 2, gettext('<em>exact</em>') => 1), 'tag_match', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Treat spaces as');
										generateRadiobuttonsFromArray(getOption('search_space_is'), array(gettext('<em>space</em>') => '', gettext('<em>OR</em>') => 'OR', gettext('<em>AND</em>') => 'AND'), 'search_space_is', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Default search');
										generateRadiobuttonsFromArray(getOption('search_within'), array(gettext('<em>New</em>') => '0', gettext('<em>Within</em>') => '1'), 'search_within', false, false);
										?>
									</p>
									<p>
										<label>
											<input type="checkbox" name="search_no_albums" value="1" <?php checked('1', getOption('search_no_albums')); ?> />
											<?php echo gettext('Do not return <em>album</em> matches') ?>
										</label>
									</p>
									<p>
										<label>
											<input type="checkbox" name="search_no_images" value="1" <?php checked('1', getOption('search_no_images')); ?> />
											<?php echo gettext('Do not return <em>image</em> matches') ?>
										</label>
									</p>
									<?php
									if (extensionEnabled('zenpage')) {
										?>
										<p>
											<label>
												<input type="checkbox" name="search_no_news" value="1" <?php checked('1', getOption('search_no_news')); ?> />
												<?php echo gettext('Do not return <em>news</em> matches') ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="search_no_pages" value="1" <?php checked('1', getOption('search_no_pages')); ?> />
												<?php echo gettext('Do not return <em>page</em> matches') ?>
											</label>
										</p>
										<?php
									}
									?>
								</td>
								<td>
									<p><?php echo gettext("<em>Field list</em> is the set of fields on which searches may be performed."); ?></p>
									<p>
										<?php
										echo gettext("Search does matches on all fields chosen based on the matching criteria selected. The <em>string matching</em> criteria is used for all fields except <em>tags</em>. The <em>tag matching</em> criteria is used for them.");
										?>
									<ul>
										<li><?php echo gettext('<code>pattern</code>: match the target anywhere within the field'); ?></li>
										<li><?php echo gettext('<code>exact</code>: match the target with the whole field'); ?></li>
										<li><?php echo gettext('<code>word</code>: match the target with whole words in the field'); ?></li>
										<li><?php echo gettext('<code>partial word</code>: match the target with the start of words in the field'); ?></li>
									</ul>
									</p>
									<p><?php echo gettext('Setting <code>Treat spaces as</code> to <em>OR</em> will cause search to trigger on any of the words in a string separated by spaces. Setting it to <em>AND</em> will cause the search to trigger only when all strings are present. Leaving the option unchecked will treat the whole string as a search target.') ?></p>
									<p><?php echo gettext('<code>Default search</code> sets how searches from search page results behave. The search will either be from <em>within</em> the results of the previous search or will be a fresh <em>new</em> search.') ?></p>
									<p><?php echo gettext('Setting <code>Do not return <em>{item}</em> matches</code> will cause search to ignore <em>{items}</em> when looking for matches.') ?></p>
								</td>

								<tr>
									<td><?php echo gettext('Search fields selector'); ?></td>
									<td>
										<p>
											<label>
												<input type="checkbox" name="search_fieldsselector_enabled" value="1" <?php checked('1', getOption('search_fieldsselector_enabled')); ?> />
												<?php echo gettext('Enable selector') ?>
											</label>
										</p>
									</td>
									<td>
										<?php echo gettext('If enabled the search form will feature a selector for all currently enabled search fields above.'); ?>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Cache expiry'); ?></td>
									<td>
										<?php printf(gettext('Redo search after %s minutes.'), '<input type="textbox" size="4" name="search_cache_duration" value="' . getOption('search_cache_duration') . '" />'); ?>
									</td>
									<td>
										<?php echo gettext('Search will remember the results of particular searches so that it can quickly serve multiple pages, etc. Over time this remembered result can become obsolete, so it should be refreshed. This option lets you decide how long before a search will be considered obsolete and thus re-executed. Setting the option to <em>zero</em> disables caching of searches.'); ?>
									</td>
								</tr>

								<tr>
									<td class="leftcolumn"><?php echo gettext("Sort albums by"); ?> </td>
									<td colspan="2">
										<span class="nowrap">
											<select id="album_sort_select" name="search_album_sort_type" onchange="update_direction(this, 'album_direction_div', 'album_custom_div');">
												<?php
												$sort = getSortByOptions('albums-search');
												$cvt = $type = strtolower(getOption('search_album_sort_type'));
												if ($type && !in_array($type, $sort)) {
													$cv = array('custom');
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												} else {
													$cv = array($type);
												}
												generateListFromArray($cv, $sort, false, true);
												?>
											</select>
											<?php
											if (($type == 'random') || ($type == '')) {
												$dsp = 'none';
											} else {
												$dsp = 'inline';
											}
											?>
											<label id="album_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
												<?php echo gettext("Descending"); ?>
												<input type="checkbox" name="search_album_sort_direction" value="1"
												<?php
												if (getOption('search_album_sort_direction')) {
													echo "CHECKED";
												};
												?> />
											</label>
										</span>
										<?php
										$flip = array_flip($sort);
										if (empty($type) || isset($flip[$type])) {
											$dsp = 'none';
										} else {
											$dsp = 'block';
										}
										?>

									</td>

								</tr>

								<tr>
									<td class="leftcolumn"><?php echo gettext("Sort images by"); ?> </td>
									<td colspan="2">
										<span class="nowrap">
											<select id="image_sort_select" name="search_image_sort_type" onchange="update_direction(this, 'image_direction_div', 'image_custom_div')">
												<?php
												$sort = getSortByOptions('images-search');
												$cvt = $type = strtolower(getOption('search_image_sort_type'));
												if ($type && !in_array($type, $sort)) {
													$cv = array('custom');
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												} else {
													$cv = array($type);
												}
												generateListFromArray($cv, $sort, false, true);
												?>
											</select>
											<?php
											if (($type == 'random') || ($type == '')) {
												$dsp = 'none';
											} else {
												$dsp = 'inline';
											}
											?>
											<label id="image_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
												<?php echo gettext("Descending"); ?>
												<input type="checkbox" name="search_image_sort_direction" value="1"
												<?php
												if (getOption('search_image_sort_direction')) {
													echo ' checked="checked"';
												}
												?> />
											</label>
										</span>
										<?php
										$flip = array_flip($sort);
										if (empty($type) || isset($flip[$type])) {
											$dsp = 'none';
										} else {
											$dsp = 'block';
										}
										?>

									</td>
								</tr>
								<?php
								if (ZP_NEWS_ENABLED) {
								?>
									<tr>
										<td class="leftcolumn"><?php echo gettext("Sort news articles by"); ?> </td>
										<td colspan="2">
											<span class="nowrap">
												<select id="newsarticle_sort_select" name="search_newsarticle_sort_type" onchange="update_direction(this, 'newsarticle_direction_div', 'newsarticle_custom_div')">
													<?php
													$zenpage_sort_news = getSortByOptions('news');
													$cvt = $type = strtolower(getOption('search_newsarticle_sort_type'));
													if ($type && !in_array($type, $zenpage_sort_news)) {
														$cv = array('custom');
														$zenpage_sort_news[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
													} else {
														$cv = array($type);
													}
													generateListFromArray($cv, $zenpage_sort_news, false, true);
													?>
												</select>
												<?php
												if (($type == 'random') || ($type == '')) {
													$dsp = 'none';
												} else {
													$dsp = 'inline';
												}
												?>
												<label id="newsarticle_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
													<?php echo gettext("Descending"); ?>
													<input type="checkbox" name="search_newsarticle_sort_direction" value="1"
													<?php
													if (getOption('search_newsarticle_sort_direction')) {
														echo ' checked="checked"';
													}
													?> />
												</label>
											</span>
											<?php
											$flip = array_flip($zenpage_sort_news);
											if (empty($type) || isset($flip[$type])) {
												$dsp = 'none';
											} else {
												$dsp = 'block';
											}
											?>

										</td>
									</tr>
								<?php
								}
								if (ZP_PAGES_ENABLED) {
									$zenpage_sort_pages = getSortByOptions('pages-search');
								?>
									<tr>
										<td class="leftcolumn"><?php echo gettext("Sort pages by"); ?> </td>
										<td colspan="2">
											<span class="nowrap">
												<select id="page_sort_select" name="search_page_sort_type" onchange="update_direction(this, 'page_direction_div', 'page_custom_div')">
													<?php
													$cvt = $type = strtolower(getOption('search_page_sort_type'));
													if ($type && !in_array($type, $zenpage_sort_pages)) {
														$cv = array('custom');
														$zenpage_sort_pages[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
													} else {
														$cv = array($type);
													}
													generateListFromArray($cv, $zenpage_sort_pages, false, true);
													?>
												</select>
												<?php
												if (($type == 'random') || ($type == '')) {
													$dsp = 'none';
												} else {
													$dsp = 'inline';
												}
												?>
												<label id="page_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
													<?php echo gettext("Descending"); ?>
													<input type="checkbox" name="search_page_sort_direction" value="1"
													<?php
													if (getOption('search_page_sort_direction')) {
														echo ' checked="checked"';
													}
													?> />
												</label>
											</span>
											<?php
											$flip = array_flip($zenpage_sort_pages);
											if (empty($type) || isset($flip[$type])) {
												$dsp = 'none';
											} else {
												$dsp = 'block';
											}
											?>

										</td>
									</tr>
								<?php } ?>

								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table>
						</form>
					</div>
					<!-- end of tab-search div -->
					<?php
				}

				if ($subtab == 'image' && zp_loggedin(OPTIONS_RIGHTS)) {
					?>
					<div id="tab_image" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="saveimageoptions" value="yes" />
							<p align="center">
								<?php echo gettext('See also the <a href="?tab=theme">Theme Options</a> tab for theme specific image options.'); ?>
							</p>

							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<?php
								foreach ($_zp_graphics_optionhandlers as $handler) {
									customOptions($handler, '');
								}
								?>
								<tr>
									<td><?php echo gettext("Sort images by"); ?></td>
									<td>
										<?php
										$sort = getSortByOptions('images');
										$cvt = $cv = IMAGE_SORT_TYPE;
										//$sort[gettext('Custom')] = 'custom';

										/*
										 * not recommended--screws with peoples minds during pagination!
										  $sort[gettext('Random')] = 'random';
										 */
										$flip = array_flip($sort);
										if (isset($flip[$cv])) {
											$dspc = 'none';
										} else {
											$dspc = 'block';
										}
										if (($cv == 'manual') || ($cv == 'random') || ($cv == '')) {
											$dspd = 'none';
										} else {
											$dspd = 'block';
										}
										?>
										<span class="nowrap">
											<select id="imagesortselect" name="image_sorttype" onchange="update_direction(this, 'image_sortdirection', 'customTextBox3')">
												<?php
												if (array_search($cv, $sort) === false) {
													$cv = 'custom';
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												}
												generateListFromArray(array($cv), $sort, false, true);
												?>
											</select>
											<label id="image_sortdirection" style="display:<?php echo $dspd; ?>white-space:nowrap;">
												<input type="checkbox" name="image_sortdirection"	value="1" <?php checked('1', getOption('image_sortdirection')); ?> />
												<?php echo gettext("Descending"); ?>
											</label>
										</span>

									</td>
									<td>
										<p><?php echo gettext("Default sort order for images."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Maximum image size'); ?></td>
									<td width="350">
										<input type="textbox" name="image_max_size" value="<?php echo getOption('image_max_size'); ?>" />
									</td>
									<td><?php echo gettext('The limit on how large an image may be resized. Too large and your server will spend all its time sizing images.'); ?></td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Image quality:"); ?></td>
									<td width="350">
										<p class="nowrap">
											<?php echo gettext('Normal Image'); ?>&nbsp;<input type="text" size="3" id="imagequality" name="image_quality" value="<?php echo getOption('image_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-imagequality").slider({
	<?php $v = getOption('image_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#imagequality").val(ui.value);
																												}
																								});
																												$("#imagequality").val($("#slider-imagequality").slider("value"));
																								});
											</script>
										<div id="slider-imagequality"></div>
										</p>
										<p class="nowrap">
											<?php echo gettext('<em>full</em> Image'); ?>&nbsp;<input type="text" size="3" id="fullimagequality" name="full_image_quality" value="<?php echo getOption('full_image_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-fullimagequality").slider({
	<?php $v = getOption('full_image_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#fullimagequality").val(ui.value);
																												}
																								});
																												$("#fullimagequality").val($("#slider-fullimagequality").slider("value"));
																								});
											</script>
										<div id="slider-fullimagequality"></div>
										</p>
										<p class="nowrap">
											<?php echo gettext('Thumbnail'); ?>&nbsp;<input type="text" size="3" id="thumbquality" name="thumb_quality" value="<?php echo getOption('thumb_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-thumbquality").slider({
	<?php $v = getOption('thumb_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#thumbquality").val(ui.value);
																												}
																								});
																												$("#thumbquality").val($("#slider-thumbquality").slider("value"));
																								});
											</script>
										<div id="slider-thumbquality"></div>
										</p>
									</td>
									<td>
										<p><?php echo gettext("Compression quality for images and thumbnails generated by Zenphoto."); ?></p>
										<p><?php echo gettext("Quality ranges from 0 (worst quality, smallest file) to 100 (best quality, biggest file)."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Interlace:"); ?></td>
									<td><input type="checkbox" name="image_interlace" value="1" <?php checked('1', getOption('image_interlace')); ?> /></td>
									<td><?php echo gettext("If checked, resized images will be created <em>interlaced</em> (if the format permits)."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext('Use embedded thumbnail'); ?></td>
									<?php
									if (function_exists('exif_thumbnail')) {
										$disabled = '';
									} else {
										$disabled = ' disabled="disabled"';
										setOption('use_embedded_thumb', 0);
									}
									?>
									<td><input type="checkbox" name="use_embedded_thumb" value="1" <?php checked('1', getOption('use_embedded_thumb')); ?><?php echo $disabled; ?> /></td>
									<td>
										<p><?php echo gettext('If set, Zenphoto will use the thumbnail embedded in the image when creating a cached image that is equal or smaller in size. Note: the quality of this image varies by camera and its orientation may not match the master image.'); ?></p>
										<?php
										if ($disabled) {
											?>
											<p class="notebox"><?php echo gettext('The PHP EXIF extension is required for this option.') ?></p>
											<?php
										}
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Allow upscale:"); ?></td>
									<td><input type="checkbox" name="image_allow_upscale" value="1" <?php checked('1', getOption('image_allow_upscale')); ?> /></td>
									<td><?php echo gettext("Allow images to be scaled up to the requested size. This could result in loss of quality, so it is off by default."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Sharpen:"); ?></td>
									<td>
										<p class="nowrap">
											<label>
												<input type="checkbox" name="image_sharpen" value="1" <?php checked('1', getOption('image_sharpen')); ?> />
												<?php echo gettext('Images'); ?>
											</label>
											<label>
												<input type="checkbox" name="thumb_sharpen" value="1" <?php checked('1', getOption('thumb_sharpen')); ?> />
												<?php echo gettext('Thumbs'); ?>
											</label>
										</p>
										<p class="nowrap">
											<?php echo gettext('Amount'); ?>&nbsp;<input type="text" id="sharpenamount" name="sharpen_amount" size="3" value="<?php echo getOption('sharpen_amount'); ?>" />
											<script>
																								$(function() {
																								$("#slider-sharpenamount").slider({
	<?php $v = getOption('sharpen_amount'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#sharpenamount").val(ui.value);
																												}
																								});
																												$("#sharpenamount").val($("#slider-sharpenamount").slider("value"));
																								});
											</script>
										<div id="slider-sharpenamount"></div>
										</p>

										<table>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Radius'); ?>&nbsp;</td>
												<td class="image_option_tablerow"><input type="text" name = "sharpen_radius" size="2" value="<?php echo getOption('sharpen_radius'); ?>" /></td>
											</tr>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Threshold'); ?>&nbsp;</td>
												<td class="image_option_tablerow"><input type="text" name = "sharpen_threshold" size="3" value="<?php echo getOption('sharpen_threshold'); ?>" /></td>
											</tr>
										</table>
									</td>
									<td>
										<p><?php echo gettext("Add an unsharp mask to images and/or thumbnails.") . "</p><p class='warningbox'>" . gettext("<strong>WARNING</strong>: can overload slow servers."); ?></p>
										<p><?php echo gettext("<em>Amount</em>: the strength of the sharpening effect. Values are between 0 (least sharpening) and 100 (most sharpening)."); ?></p>
										<p><?php echo gettext("<em>Radius</em>: the pixel radius of the sharpening mask. A smaller radius sharpens smaller details, and a larger radius sharpens larger details."); ?></p>
										<p><?php echo gettext("<em>Threshold</em>: the color difference threshold required for sharpening. A low threshold sharpens all edges including faint ones, while a higher threshold only sharpens more distinct edges."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Watermarks:"); ?></td>
									<td>
										<table>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Images'); ?> </td>
												<td class="image_option_tablerow">
													<select id="fullimage_watermark" name="fullimage_watermark">
														<?php $current = IMAGE_WATERMARK; ?>
														<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('none'); ?></option>
														<?php
														$watermarks = getWatermarks();
														generateListFromArray(array($current), $watermarks, false, false);
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Full sized images'); ?> </td>
												<td class="image_option_tablerow">
													<select id="fullsizeimage_watermark" name="fullsizeimage_watermark">
														<?php $current = FULLIMAGE_WATERMARK; ?>
														<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('none'); ?></option>
														<?php
														$watermarks = getWatermarks();
														generateListFromArray(array($current), $watermarks, false, false);
														?>
													</select>
												</td>
											</tr>
											<?php
											$imageplugins = array_unique($_zp_extra_filetypes);
											$imageplugins[] = 'Image';
											ksort($imageplugins, SORT_LOCALE_STRING);
											foreach ($imageplugins as $plugin) {
												$opt = $plugin . '_watermark';
												$current = getOption($opt);
												?>
												<tr>
													<td class="image_option_tablerow">
														<?php
														printf(gettext('%s thumbnails'), gettext($plugin));
														if ($plugin != 'Image')
															echo ' <strong>*</strong>';
														?> </td>
													<td class="image_option_tablerow">
														<select id="<?php echo $opt; ?>" name="<?php echo $opt; ?>">
															<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray">
																<?php
																if ($plugin == 'Image')
																	echo gettext('none');
																else
																	echo gettext('image thumb')
																	?>
															</option>
															<?php
															$watermarks = getWatermarks();
															generateListFromArray(array($current), $watermarks, false, false);
															?>
														</select>
													</td>
												</tr>
												<?php
											}
											?>
										</table>
										<p class="nowrap">
											<?php echo gettext('cover') . ' '; ?>
											<input type="text" size="2" name="watermark_scale"
														 value="<?php echo html_encode(getOption('watermark_scale')); ?>" /><?php /* xgettext:no-php-format */ echo gettext('% of image') ?>
										</p>
										<p class="nowrap">
											<?php echo gettext("offset h"); ?>
											<input type="text" size="2" name="watermark_h_offset"
														 value="<?php echo html_encode(getOption('watermark_h_offset')); ?>" /><?php echo /* xgettext:no-php-format */ gettext("% , w "); ?>
											<input type="text" size="2" name="watermark_w_offset"
														 value="<?php echo html_encode(getOption('watermark_w_offset')); ?>" /><?php /* xgettext:no-php-format */ echo gettext("%"); ?>
										</p>
									</td>
									<td>
										<p><?php echo gettext("The watermark image is scaled by to cover <em>cover percentage</em> of the image and placed relative to the upper left corner of the image."); ?></p>
										<p><?php echo gettext("It is offset from there (moved toward the lower right corner) by the <em>offset</em> percentages of the height and width difference between the image and the watermark."); ?></p>
										<p><?php echo gettext("The watermark will not be made larger than the original watermark image."); ?></p>
										<p><?php printf(gettext('Custom watermarks should be placed in the <code>/%s/watermarks/</code> folder. The images must be in png-24 format.'), USER_PLUGIN_FOLDER); ?></p>
										<?php
										if (!empty($imageplugins)) {
											?>
											<p class="notebox"><?php echo '* ' . gettext('If a watermark image is selected for these <em>images classes</em> it will be used in place of the image thumbnail watermark.'); ?></p>
											<?php
										}
										?>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> If you want to watermark <em>Full sized images</em> you have to enable <em>cache the full image</em> from the <em>Full image protection</em> option below.'); ?>
										</p>
									</td>

								</tr>
								<tr>
									<td><?php echo gettext("Caching concurrency:"); ?></td>
									<td>
										<script>
																							$(function() {
																							$("#slider-workers").slider({
	<?php $v = getOption('imageProcessorConcurrency'); ?>
																							startValue: <?php echo $v; ?>,
																											value: <?php echo $v; ?>,
																											min: 1,
																											max:60,
																											slide: function(event, ui) {
																											$("#cache-workers").val(ui.value);
																															$("#cache_processes").html($("#cache-workers").val());
																											}
																							});
																											$("#cache-workers").val($("#slider-workers").slider("value"));
																											$("#cache_processes").html($("#cache-workers").val());
																							});
										</script>
										<div id="slider-workers"></div>
										<input type="hidden" id="cache-workers" name="imageProcessorConcurrency" value="<?php echo getOption('imageProcessorConcurrency'); ?>" />
									</td>
									<td>
										<?php
										printf(gettext('Cache processing worker limit: %s.'), '<span id="cache_processes">' . getOption('imageProcessorConcurrency') . '</span>') .
														'<p class="notebox">' . gettext('More workers will get the job done faster so long as your server does not get swamped or run out of memory.') . '</p>';
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Cache as:"); ?></td>
									<td>
										<?php $type = IMAGE_CACHE_SUFFIX; ?>
										<input type="radio" name="image_cache_suffix" value=""<?php if (empty($type)) echo ' checked="checked"'; ?> />&nbsp;<?php echo gettext("Original"); ?>
										<?php
										$cachesuffix = array_unique($_zp_cachefile_suffix);
										foreach ($cachesuffix as $suffix) {
											?>
											<input type="radio" name="image_cache_suffix" value="<?php echo $suffix; ?>"<?php if ($type == $suffix) echo ' checked="checked"'; ?> />&nbsp;<?php echo $suffix; ?>
											<?php
										}
										?>
									</td>
									<td><?php echo gettext("Select a type for the images stored in the image cache. Select <em>Original</em> to preserve the original image’s type."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Protect image cache"); ?></td>
									<td>
										<input type="checkbox" name="protected_image_cache" value="1"
													 <?php checked('1', getOption('protected_image_cache')); ?> />
									</td>
									<td><?php
										echo gettext('If checked all image URIs will link to the image processor and the image cache will be disabled to browsers via an <em>.htaccess</em> file. Images are still cached but the image processor is used to serve the image rather than allowing the browser to fetch the file.') .
										'<p class="warningbox">' . gettext('<strong>WARNING	:</strong> This option adds significant overhead to <strong>each and every</strong> image reference! Some <em>JavaScript</em> and <em>Flash</em> based image handlers will not work with an image processor URI and are incompatible with this option.') . '</p>';
										?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Secure image processor"); ?></td>
									<td>
										<input type="checkbox" name="secure_image_processor" value="1"
													 <?php checked('1', getOption('secure_image_processor')); ?> />
									</td>
									<td><?php
										echo gettext('When enabled, the image processor will check album access credentials.') .
										'<p class="warningbox">' . gettext('<strong>WARNING	:</strong> This option adds memory overhead to image caching! You may be unable to cache some images depending on your server memory availability.') . '</p>';
										?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Full image protection:"); ?></td>
									<td style="margin:0">
										<p>
											<label>
												<input type="checkbox" name="hotlink_protection" value="1" <?php checked('1', getOption('hotlink_protection')); ?> />
												<?php echo gettext('Disable hotlinking'); ?>
											</label>
											<br />
											<label>
												<input type="checkbox" name="cache_full_image" value="1" <?php checked('1', getOption('cache_full_image')); ?> />
												<?php echo gettext('cache the full image'); ?>
											</label>
										</p>

										<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
										<?php
										if (GALLERY_SECURITY == 'public') {
											?>
											<br class="clearall" />
											<table class="compact">
												<tr class="passwordextrashow">
													<td style="margin:0; padding:0">
														<a href="javascript:toggle_passwords('',true);">
															<?php echo gettext("password:"); ?>
														</a>
													</td>
													<td style="margin:0; padding:0">
														<?php
														$x = getOption('protected_image_password');
														if (empty($x)) {
															?>
															<img src="images/lock_open.png" />
															<?php
														} else {
															$x = '          ';
															?>
															<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
															<?php
														}
														?>
													</td>
												</tr>
												<tr class="passwordextrahide" style="display:none">
													<td style="margin:0; padding:0">
														<a href="javascript:toggle_passwords('',false);">
															<?php echo gettext("user:"); ?>
														</a>
													</td>
													<td style="margin:0; padding:0">
														<input type="text" size="30"
																	 class="dirtyignore"
																	 onkeydown="passwordClear('');"
																	 id="user_name" name="user"
																	 value="<?php echo html_encode(getOption('protected_image_user')); ?>" autocomplete="off" />

													</td>
												</tr>
												<tr class="passwordextrahide" style="display:none" >
													<td style="margin:0; padding:0">
														<span id="strength">
															<?php echo gettext("password:"); ?>
														</span>
														<br />
														<span id="match" class="password_field_">
															<?php echo gettext("(repeat)"); ?>
														</span>
													</td>
													<td style="margin:0; padding:0">
														<?php
														// Autofill honeypot hack (hidden password input),
														// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
														// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
														?>
														<input class="dirtyignore" type="password" name="pass" style="display:none;" />
														<input type="password" size="30"
																	 class="dirtyignore"
																	 id="pass" name="pass"
																	 onkeydown="passwordClear('');"
																	 onkeyup="passwordStrength('');"
																	 value="<?php echo $x; ?>" autocomplete="off" />
														<br />
														<span class="password_field_">
															<input type="password" size="30"
																		 class="dirtyignore"
																		 id="pass_r" name="pass_r" disabled="disabled"
																		 onkeydown="passwordClear('');"
																		 onkeyup="passwordMatch('');"
																		 value="<?php echo $x; ?>" autocomplete="off" />
														</span>
														<br />
														<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear(''); togglePassword('');" /><?php echo gettext('Show password'); ?></label>
													</td>
												</tr>
												<tr class="passwordextrahide" style="display:none" >
													<td style="margin:0; padding:0">
														<?php echo gettext("hint:"); ?>
													</td>
													<td style="margin:0; padding:0">
														<?php print_language_string_list(getOption('protected_image_hint'), 'hint', false, NULL, 'hint'); ?>
													</td>
												</tr>
											</table>
											<?php
										}
										?>
										<p>
											<?php
											echo "<select id=\"protect_full_image\" name=\"protect_full_image\">\n";
											$protection = getOption('protect_full_image');
											$list = array(gettext('Protected view') => 'protected', gettext('Download') => 'download', gettext('No access') => 'no-access');
											if ($_zp_conf_vars['album_folder_class'] != 'external') {
												$list[gettext('Unprotected')] = 'unprotected';
											}
											generateListFromArray(array($protection), $list, false, true);
											echo "</select>\n";
											?>
										</p>
									</td>
									<td>
										<p><?php echo gettext("Disabling hotlinking prevents linking to the full image from other domains. If enabled, external links are redirect to the image page. If you are having problems with full images being displayed, try disabling this setting. Hotlinking is not prevented if <em>Full&nbsp;image&nbsp;protection</em> is <em>Unprotected</em> or if the image is cached."); ?></p>
										<p><?php echo gettext("If <em>Cache the full image</em> is checked the full image will be loaded to the cache and served from there after the first reference. <em>Full&nbsp;image&nbsp;protection</em> must be set to <em>Protected&nbsp;view</em> for the image to be cached. However, once cached, no protections are applied to the image."); ?></p>
										<p><?php echo gettext("The <em>user</em>, <em>password</em>, and <em>hint</em> apply to the <em>Download</em> and <em>Protected view</em> level of protection. If there is a password set, the viewer must supply this password to access the image."); ?></p>
										<p><?php echo gettext("Select the level of protection for full sized images. <em>Download</em> forces a download dialog rather than displaying the image. <em>No&nbsp;access</em> prevents a link to the image from being shown. <em>Protected&nbsp;view</em> forces image processing before the image is displayed, for instance to apply a watermark or to check passwords. <em>Unprotected</em> allows direct display of the image."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Use lock image"); ?></td>
									<td>
										<input type="checkbox" name="use_lock_image" value="1"
													 <?php checked('1', getOption('use_lock_image')); ?> />
									</td>
									<td><?php echo gettext("Substitute a <em>lock</em> image for thumbnails of password protected albums when the viewer has not supplied the password. If your theme supplies an <code>images_errors/err-passwordprotected.png</code> image, it will be shown. Otherwise the zenphoto default lock image is displayed."); ?></td>
								</tr>
								<script>
																									$(function() {
																									$("#resizable").resizable({
																									maxWidth: 350,
																													minWidth: 350, minHeight: 120,
																													resize: function(event, ui) {
																													$('#metadatalist').height($('#resizable').height());
																													}
																									});
																									});</script>
								<tr>
									<td><?php echo gettext("Metadata"); ?></td>
									<td>
										<div id="resizable">
											<ul id="metadatalist" class="searchchecklist">
												<?php
												$exifstuff = sortMultiArray($_zp_exifvars, 2, false);
												foreach ($exifstuff as $key => $item) {
													$checked_show = $checked_hide = $checked_disabled = '';
													if (!$item[5]) {
														$checked_disabled = ' checked="checked"';
													} else {
														if ($item[3]) {
															$checked_show = ' checked="checked"';
														} else {
															$checked_hide = ' checked="checked"';
														}
													}
													if (!$item[4]) {
														$checked_show = ' disabled="disabled"';
													}
													?>
													<li>
														<label><input id="<?php echo $key; ?>_show" name="<?php echo $key; ?>" type="radio"<?php echo $checked_show ?> value="1" /><img src ="images/pass.png" alt="<?php echo gettext('show'); ?>" /></label>
														<label><input id="<?php echo $key; ?>_hide" name="<?php echo $key; ?>" type="radio"<?php echo $checked_hide ?> value="0" /><img src ="images/reset.png" alt="<?php echo gettext('hide'); ?>" /></label>
														<label><input id="<?php echo $key; ?>_disable" name="<?php echo $key; ?>" type="radio"<?php echo $checked_disabled ?> value="2" /><img src ="images/fail.png" alt="<?php echo gettext('disabled'); ?>" /></label>
														<?php echo $item[2]; ?>&nbsp;&nbsp;&nbsp;
													</li>
													<?php
												}
												?>
											</ul>
										</div>
									</td>
									<td>
										<p>
											<?php echo gettext("Select how image metadata fields are handled."); ?>
										<ul style="list-style: none;">
											<li><img src ="images/pass.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('Show the field and import data'); ?></li>
											<li><img src ="images/reset.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('Hide the field but import data'); ?></li>
											<li><img src ="images/fail.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('No display and import'); ?></li>
										</ul>
										</p>
										<p class="warningbox">
											<?php echo gettext('<strong>Important:</strong> The "Refresh metadata" utility which is accessible from the backend "Overview" page, every album-edit page and every image-edit page, will overwrite all manually added metadata with metadata embedded in the image/album for all fields enabled. This cannot be undone!'); ?>
										</p>
									</td>
								</tr>
								<?php
								$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
								ksort($sets, SORT_LOCALE_STRING);
								if (!empty($sets)) {
									?>
									<tr>
										<td><?php echo gettext("IPTC encoding:"); ?></td>
										<td>
											<select id="IPTC_encoding" name="IPTC_encoding">
												<?php generateListFromArray(array(getOption('IPTC_encoding')), array_flip($sets), false, true) ?>
											</select>
										</td>
										<td>
										<p><?php echo gettext("The default character encoding of image IPTC metadata."); ?></p>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> If you notice unexpected behaviour, especially with non-ASCII characters (Cyrillic for example), try again with the <code>xmpMetadata</code> plugin enabled.'); ?>
										</p>
										</td>
									</tr>
									<?php
								}
        ?>
         <tr>
										<td><?php echo gettext("IPTC caption linebreaks:"); ?></td>
										<td>
           <label><input type="checkbox" name="IPTC_convert_linebreaks" value="1"	<?php checked('1', getOption('IPTC_convert_linebreaks')); ?> /></label>
										</td>
										<td><?php echo gettext("If checked line breaks embeded in the IPTCcaption field will be converted to <code>&lt;br&gt;</code> on image importing."); ?></td>
									</tr>

								<tr>
									<td><?php echo gettext('Image copyright notice'); ?></td>
									<td>
										<p><?php print_language_string_list(getOption('copyright_image_notice'), 'copyright_image_notice'); ?> <?php echo gettext('Notice'); ?></p>

									</td>
									<td>
										<p><?php echo gettext('The notice will be used by the html_meta_tags plugin. If not set the image meta data is tried instead.'); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Image Copyright URL'); ?></td>
									<td>
									<?php printZenpagePageSelector('copyright_image_url', 'copyright_image_url_custom', false); ?>
									</td>
									<td>
										<p><?php echo gettext('Choose a Zenpage page or define a custom URL. The URL maybe used to point to some specific copyright info source. Must be an absolute URL address of the form: http://mydomain.com/license.html.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext("Display Image copyright notice"); ?></td>
									<td>
										<label><input type="checkbox" name="display_copyright_image_notice" id="display_copyright_image_notice" value="1" <?php checked('1', getOption('display_copyright_image_notice')); ?> /> <?php echo gettext('Enable'); ?></label>
									</td>
									<td><?php echo gettext("Enable to display the image copyright notice. This may usually be in the theme below the image but is up to the theme where and if used at all.."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Image copyright rightsholder'); ?></td>
									<td>
										<?php printUserSelector('copyright_image_rightsholder','copyright_image_rightsholder_custom', 'users'); ?>
									</td>
									<td>
										<p><?php echo gettext('The rights holder will be used by the html_meta_tags plugin. If set to <em>none</em> the image metadata fields "copyright" or "owner" are used as fallbacks, if available.'); ?></p>
									</td>
								</tr>



								<tr>
									<?php
											if (GRAPHICS_LIBRARY == 'Imagick') {
												$optionText = gettext('Embed IPTC copyright');
												$desc = gettext('If checked and an image has no IPTC data a copyright notice will be embedded in cached copies.');
											} else {
												$optionText = gettext('Replicate IPTC metadata');
												$desc = gettext('If checked IPTC data from the original image will be embedded in cached copies. If the image has no IPTC data a copyright notice will be embedded. (The text supplied will be used if the orginal image has no copyright.)');
											}
										?>
									<td><?php echo gettext('IPTC copyright embedding'); ?></td>
									<td>
										<p><label><input type="checkbox" name="EmbedIPTC" value="1"	<?php checked('1', getOption('EmbedIPTC')); ?> /> <?php echo $optionText; ?></label></p>
									</td>
									<td>
										<p><?php echo $desc; ?></p>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> This option applies only to JPEG format cached images.'); ?>
										</p>
									</td>
								</tr>
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table>
						</form>
					</div><!-- end of tab_image div -->
					<?php
				}

				if ($subtab == 'theme' && zp_loggedin(THEMES_RIGHTS)) {
					?>
					<div id="tab_theme" class="tabbox">
						<?php
						zp_apply_filter('admin_note', 'options', $subtab);
						$themelist = array();
						if (zp_loggedin(ADMIN_RIGHTS)) {
							$gallery_title = $_zp_gallery->getTitle();
							if ($gallery_title != gettext("Gallery")) {
								$gallery_title .= ' (' . gettext("Gallery") . ')';
							}
							$themelist[$gallery_title] = '';
						}
						$albums = $_zp_gallery->getAlbums(0);
						foreach ($albums as $alb) {
							$album = AlbumBase::newAlbum($alb);
							if ($album->isMyItem(THEMES_RIGHTS)) {
								$theme = $album->getAlbumTheme();
								if (!empty($theme)) {
									$key = $album->getTitle();
									if ($key != $alb) {
										$key .= " ($alb)";
									}
									$themelist[$key] = pathurlencode($alb);
								}
							}
						}
						$albumtitle = $alb = $album = NULL;
						$themename = $_zp_gallery->getCurrentTheme();
						if (!empty($_REQUEST['themealbum'])) {
							$alb = urldecode(sanitize_path($_REQUEST['themealbum']));
							$album = AlbumBase::newAlbum($alb);
							$albumtitle = $album->getTitle();
							$themename = $album->getAlbumTheme();
						}
						if (!empty($_REQUEST['optiontheme'])) {
							$themename = sanitize($_REQUEST['optiontheme']);
						}
						if (empty($alb)) {
							foreach ($themelist as $albumtitle => $alb)
								break;
							if (empty($alb)) {
								$album = NULL;
							} else {
								$alb = sanitize_path($alb);
								$album = AlbumBase::newAlbum($alb);
								$albumtitle = $album->getTitle();
								$themename = $album->getAlbumTheme();
							}
						}
						if (!(false === ($requirePath = getPlugin('themeoptions.php', $themename)))) {
							require_once($requirePath);
							$optionHandler = new ThemeOptions();
							$supportedOptions = $optionHandler->getOptionsSupported();
							if (method_exists($optionHandler, 'getOptionsDisabled')) {
								$unsupportedOptions = $optionHandler->getOptionsDisabled();
							} else {
								$unsupportedOptions = array();
							}
						} else {
							$unsupportedOptions = array();
							$supportedOptions = array();
						}
						standardThemeOptions($themename, $album);
						?>
						<form class="dirty-check" action="?action=saveoptions" method="post" id="themeoptionsform" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" id="savethemeoptions" name="savethemeoptions" value="yes" />
							<input type="hidden" name="optiontheme" value="<?php echo html_encode($themename); ?>" />
							<input type="hidden" name="old_themealbum" value="<?php echo pathurlencode($alb); ?>" />
							<table class='bordered options'>

								<?php
								if (count($themelist) == 0) {
									?>
									<th>
										<br />
									<div class="errorbox" id="no_themes">
										<h2><?php echo gettext("There are no themes for which you have rights to administer."); ?></h2>
									</div>
									</th>

									<?php
								} else {
									/* handle theme options */
									$themes = $_zp_gallery->getThemes();
									$theme = $themes[$themename];
									?>
									<tr>
										<th colspan='2'>
									<h2 style='float: left'>
										<?php
										if ($albumtitle) {
											printf(gettext('Options for <code><strong>%1$s</strong></code>: <em>%2$s</em>'), $albumtitle, $theme['name']);
										} else {
											printf(gettext('Options for <em>%s</em>'), $theme['name']);
										}
										?>
									</h2>
									</th>
									<th colspan='1' style='text-align: right'>
										<?php
										if (count($themelist) > 1) {
											echo gettext("Show theme for:");
											echo '<select id="themealbum" name="themealbum" onchange="this.form.submit()">';
											generateListFromArray(array(pathurlencode($alb)), $themelist, false, true);
											echo '</select>';
										} else {
											?>
											<input type="hidden" name="themealbum" value="<?php echo pathurlencode($alb); ?>" />
											<?php
											echo '&nbsp;';
										}
										echo "</th></tr>\n";
										?>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset'); $('#themeoptionsform').submit();"><img src="images/refresh.png" alt="" /><strong><?php echo gettext("Revert to default"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
									<tr class="alt1">
										<td align="left">
											<?php echo gettext('<strong>Standard options</strong>') ?>
										</td>
										<td colspan="2" ><?php echo gettext('<em>Image and album presentation options provided by the Zenphoto core for all themes.</em>') . '<p class="notebox">' . gettext('<strong>Note:</strong> These are <em>recommendations</em> as themes may choose to override them for design reasons'); ?></p></td>
									</tr>
									<tr>
										<td style='width: 175px'><?php echo gettext("Albums:"); ?></td>
										<td>
											<?php
											if (in_array('albums_per_page', $unsupportedOptions)) {
												$disable = ' disabled="disabled"';
											} else {
												$disable = '';
											}
											?>
										<p>
				<label><input type="text" size="3" name="albums_per_page" value="<?php echo getThemeOption('albums_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?></label>
			</p>
										</td>
										<td>
											<?php
											echo gettext('Set how many albums should appear on an album page'); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo gettext("Images:"); ?></td>
										<td>
											<?php
											if (in_array('images_per_page', $unsupportedOptions)) {
												$disable = ' disabled="disabled"';
											} else {
												$disable = '';
											}
											?>
											<label><input type="text" size="3" name="images_per_page" value="<?php echo getThemeOption('images_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?></label>
										</td>
										<td>
											<?php
											echo gettext('Set how many images (thumbs) should appear on an image page');
											if (getThemeOption('images_per_row', $album, $themename) > 1) {
												?>
												<p class="notebox">
													<?php
													echo gettext('<strong>Note:</strong> If <em>thumbnails per row</em> is greater than 1, The actual number of thumbnails that are displayed on a page will be rounded up to the next multiple of it.') . ' ';
													printf(gettext('For pages containing images there will be %1$u rows of thumbnails.'), ceil(getThemeOption('images_per_page', $album, $themename) / getThemeOption('images_per_row', $album, $themename)));
													?>
												</p>
												<?php
											}
											?>
										</td>
									</tr>
									<?php
									if (in_array('thumb_transition', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext('Transition:'); ?></td>
										<td>
											<span class="nowrap">
												<?php
											if (getThemeOption('thumb_transition', $album, $themename)) {
											$transition_enabled  = ' checked="checked"';
										} else {
											$transition_enabled  = '';
										}
												?>
												<label><input type="checkbox" name="thumb_transition" value="1"<?php echo $transition_enabled . $disable; ?> /> <?php echo gettext('Enable transition page'); ?></label>
			<?php
			if (in_array('thumb_transition_min', $unsupportedOptions)) {
				$disable = ' disabled="disabled"';
			} else {
				$disable = '';
			}
			?>
			<p>
				<label><input type="text" size="3" name="thumb_transition_min" value="<?php echo getThemeOption('thumb_transition_min', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('Minimum number of image thumbs'); ?></label>
			</p>
			<?php
			if (in_array('thumb_transition_max', $unsupportedOptions)) {
				$disable = ' disabled="disabled"';
			} else {
				$disable = '';
			}
			?>
			<p>
				<label><input type="text" size="3" name="thumb_transition_max" value="<?php echo getThemeOption('thumb_transition_max', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('Maximum number of image thumbs'); ?></label>
			</p>
										</span>
										</td>
										<td><?php echo gettext('If the last page with albums has less albums than the albums per page value, image thumbs share the page with the album thumbs. Their number is calculated from their image per page value and their total number. So if the albums use 30&percnt; of their albums per page value, the image number used is 70&percnt; of their images per page value. The minimum and maximum can be defined. Set both options to the same value to always get a fixed value.'); ?></td>
									</tr>
									<?php
									if (in_array('thumb_size', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									$ts = max(1, getThemeOption('thumb_size', $album, $themename));
									$iw = getThemeOption('thumb_crop_width', $album, $themename);
									$ih = getThemeOption('thumb_crop_height', $album, $themename);
									$thumb_use_side = getThemeOption('thumb_use_side', $album, $themename);
									$cl = round(($ts - $iw) / $ts * 50, 1);
									$ct = round(($ts - $ih) / $ts * 50, 1);
									?>
									<tr>
										<td><?php echo gettext("Thumb size:"); ?></td>
										<td><?php $side = getThemeOption('image_use_side', $album, $themename); ?>
											<table>
												<tr>
													<td rowspan="2" style="margin: 0; padding: 0">
														<input type="text" size="3" name="thumb_size" value="<?php echo $ts; ?>"<?php echo $disable; ?> />
													</td>
													<td style="margin: 0; padding: 0">
														<label> <input type="radio" id="image_use_side1" name="thumb_use_side" value="height"
																																					 <?php if ($thumb_use_side == 'height') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('height') ?> </label>
														<label> <input type="radio" id="image_use_side2"
																					 name="thumb_use_side" value="width"
																					 <?php if ($thumb_use_side == 'width') echo ' checked="checked"'; ?>
																					 <?php echo $disable; ?> /> <?php echo gettext('width') ?> </label>
													</td>
												</tr>
												<tr>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side3" name="thumb_use_side" value="shortest"
																																					 <?php if ($thumb_use_side == 'shortest') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('shortest side') ?>
														</label> <label> <input type="radio" id="image_use_side4"
																										name="thumb_use_side" value="longest"
																										<?php if ($thumb_use_side == 'longest') echo ' checked="checked"'; ?>
																										<?php echo $disable; ?> /> <?php echo gettext('longest side') ?> </label>
													</td>
												</tr>
											</table>
										</td>
										<td><?php printf(gettext("Standard thumbnails will be scaled to %u pixels."), $ts); ?> <br />
											<?php echo gettext("If cropping is disabled the thumbs will be sized so that the <em>height</em>, <em>width</em>, <em>shortest side</em>, or the <em>longest side</em> will be equal to <em>thumb size</em>."); ?>
										</td>
									</tr>
									<?php
									if (in_array('thumb_crop', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext("Crop thumbnails:"); ?></td>
										<td>
											<input type="checkbox" name="thumb_crop" value="1" <?php checked('1', $tc = getThemeOption('thumb_crop', $album, $themename)); ?><?php echo $disable; ?> />
											&nbsp;&nbsp;
											<span class="nowrap">
												<?php printf(gettext('%s%% left &amp; right'), '<input type="text" size="3" name="thumb_crop_width" id="thumb_crop_width" value="' . $cl . '"' . $disable . ' />')
												?>
											</span>&nbsp;
											<span class="nowrap">
												<?php printf(gettext('%s%% top &amp; bottom'), '<input type="text" size="3" name="thumb_crop_height" id="thumb_crop_height"	value="' . $ct . '"' . $disable . ' />');
												?>
											</span>
										</td>
										<td>
											<?php printf(gettext('If checked the thumbnail will be cropped %1$.1f%% in from the top and the bottom margins and %2$.1f%% in from the left and the right margins.'), $ct, $cl); ?>
											<br />
											<p class='notebox'><?php echo gettext('<strong>Note:</strong> changing crop will invalidate existing custom crops.'); ?></p>
										</td>
									</tr>
									<tr>
										<td><?php echo gettext("Gray scale conversion:"); ?></td>
										<td>
											<label class="checkboxlabel">
												<?php echo gettext('image') ?>
												<input type="checkbox" name="image_gray" id="image_gray" value="1" <?php checked('1', getThemeOption('image_gray', $album, $themename)); ?> />
											</label>
											<label class="checkboxlabel">
												<?php echo gettext('thumbnail') ?>
												<input type="checkbox" name="thumb_gray" id="thumb_gray" value="1" <?php checked('1', getThemeOption('thumb_gray', $album, $themename)); ?> />
											</label>
										</td>
										<td><?php echo gettext("If checked, images/thumbnails will be created in gray scale."); ?></td>
									</tr>
									<?php
									if (in_array('image_size', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext("Image size:"); ?></td>
										<td><?php $side = getThemeOption('image_use_side', $album, $themename); ?>
											<table>
												<tr>
													<td rowspan="2" style="margin: 0; padding: 0"><input type="text"
																																							 size="3" name="image_size"
																																							 value="<?php echo getThemeOption('image_size', $album, $themename); ?>"
																																							 <?php echo $disable; ?> /></td>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side1" name="image_use_side" value="height"
																																					 <?php if ($side == 'height') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('height') ?> </label>
														<label> <input type="radio" id="image_use_side2"
																					 name="image_use_side" value="width"
																					 <?php if ($side == 'width') echo ' checked="checked"'; ?>
																					 <?php echo $disable; ?> /> <?php echo gettext('width') ?> </label>
													</td>
												</tr>
												<tr>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side3" name="image_use_side" value="shortest"
																																					 <?php if ($side == 'shortest') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('shortest side') ?>
														</label> <label> <input type="radio" id="image_use_side4"
																										name="image_use_side" value="longest"
																										<?php if ($side == 'longest') echo ' checked="checked"'; ?>
																										<?php echo $disable; ?> /> <?php echo gettext('longest side') ?> </label>
													</td>
												</tr>
											</table>
										</td>
										<td><?php echo gettext("Default image display size."); ?> <br />
											<?php echo gettext("The image will be sized so that the <em>height</em>, <em>width</em>, <em>shortest side</em>, or the <em>longest side</em> will be equal to <em>image size</em>."); ?>
										</td>
									</tr>
									<?php
									if (is_null($album)) {
										if (in_array('custom_index_page', $unsupportedOptions)) {
											$disable = ' disabled="disabled"';
										} else {
											$disable = '';
										}
										?>
										<tr>
											<td><?php echo gettext("Gallery index page link:"); ?></td>
											<td>
												<select id="custom_index_page" name="custom_index_page"<?php echo $disable; ?>>
													<option value="" style="background-color:LightGray"><?php echo gettext('none'); ?></option>
													<?php
													$curdir = getcwd();
													$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $themename . '/';
													chdir($root);
													$filelist = safe_glob('*.php');
													$list = array();
													foreach ($filelist as $file) {
														$file = filesystemToInternal($file);
														$list[$file] = str_replace('.php', '', $file);
													}
													$list = array_diff($list, standardScripts());
													generateListFromArray(array(getThemeOption('custom_index_page', $album, $themename)), $list, false, true);
													chdir($curdir);
													?>
												</select>
											</td>
											<td><?php echo gettext("If this option is not empty, the Gallery Index URL that would normally link to the theme <code>index.php</code> script will instead link to this script. This frees up the <code>index.php</code> script so that you can create a customized <em>Home page</em> script. This option applies only to the main theme for the <em>Gallery</em>."); ?></td>
										</tr>

										<?php
									}
									if (count($supportedOptions) > 0) {
										?>
										<tr class="alt1" >
											<td align="left">
												<?php echo gettext('<strong>Custom theme options</strong>') ?>
											</td>
											<td colspan="2"><em><?php printf(gettext('The following are options specifically implemented by %s.'), $theme['name']); ?></em></td>
										</tr>
										<?php
										customOptions($optionHandler, '', $album, false, $supportedOptions, $themename);
									}
									?>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset'); $('#themeoptionsform').submit();"><img src="images/refresh.png" alt="" /><strong><?php echo gettext("Revert to default"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</form>
					</div>
					<!-- end of tab_theme div -->
					<?php
				}
				if ($subtab == 'plugin' && zp_loggedin(ADMIN_RIGHTS)) {
					if (isset($_GET['single'])) {
						$showExtension = sanitize($_GET['single']);
						$_GET['show-' . $showExtension] = true;
					} else {
						$showExtension = NULL;
					}

					$_zp_plugin_count = 0;

					$plugins = array();
					if (isset($_GET['single'])) {
						$plugins = array($showExtension);
					} else {
						$list = array_keys(getEnabledPlugins());
						foreach ($list as $extension) {
							$option_interface = NULL;
							$path = getPlugin($extension . '.php');
							$pluginStream = file_get_contents($path);
							$str = isolate('$option_interface', $pluginStream);
							if (false !== $str) {
								$plugins[] = $extension;
							}
						}
						sortArray($plugins);
					}
					$rangeset = getPageSelector($plugins, PLUGINS_PER_PAGE);
					$plugins = array_slice($plugins, $pagenumber * PLUGINS_PER_PAGE, PLUGINS_PER_PAGE);
					?>
					<div id="tab_plugin" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<script>
																							var optionholder = new Array();
						</script>
						<form class="dirty-check" id="form_options" action="?action=saveoptions<?php if (isset($_GET['single'])) echo '&amp;single=' . html_encode($showExtension); ?>" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="savepluginoptions" value="yes" />
							<input type="hidden" name="pagenumber" value="<?php echo $pagenumber; ?>" />
							<table class="bordered">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<?php
								if (!$showExtension) {
									?>
									<tr>
										<th style="text-align:left">
										</th>
										<th style="text-align:right; padding-right: 10px;">
											<?php printPageSelector($pagenumber, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
										</th>
										<th></th>
									</tr>
									<?php
								}
								foreach ($plugins as $extension) {
									$option_interface = NULL;
									$path = getPlugin($extension . '.php');
									$pluginStream = file_get_contents($path);
									$plugin_name = '';
									if ($str = isolate('$plugin_name', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_name = '';
										}
									}
									if(empty($plugin_name)) {
										$plugin_name = $extension;
									}
									$plugin_description = '';
									if ($str = isolate('$plugin_description', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_description = '';
										} else {
											$plugin_description = processExtensionVariable($plugin_description);
										}
									}
									$plugin_version = '';
									if ($str = isolate('$plugin_version', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_version = '';
										}
									}
									$plugin_deprecated = '';
									if ($str = isolate('$plugin_deprecated', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_deprecated = '';
										} else {
											$plugin_deprecated = processExtensionVariable($plugin_deprecated);
											if (is_bool($plugin_deprecated) || empty($plugin_deprecated)) {
												$plugin_deprecated = gettext('This plugin will be removed in future versions.');
											}
										}
									}
									$plugin_date = '';
									if ($str = isolate('$plugin_date', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_date = '';
										}
									}
									$str = isolate('$option_interface', $pluginStream);
									if (false !== $str) {
										require_once($path);
										if (preg_match('/\s*=\s*new\s(.*)\(/i', $str)) {
											eval($str);
											$warn = gettext('<strong>Note:</strong> Instantiating the option interface within the plugin may cause performance issues. You should instead set <code>$option_interface</code> to the name of the class as a string.');
										} else {
											eval($str);
											$option_interface = new $option_interface;
											$warn = '';
										}
									}
									if (!empty($option_interface)) {
										$_zp_plugin_count++;
										?>
										<!-- <?php echo $extension; ?> -->
										<tr>
											<td style="padding: 0;margin:0" colspan="3">
												<table class="options" style="border: 0" id="plugin-<?php echo $extension; ?>">
													<tr>
														<?php
														if ($showExtension) {
															$v = 1;
														} else {
															$v = 0;
														}
														?>
														<th style="text-align:left; width: 20%">
															<span id="<?php echo $extension; ?>" ></span>
															<?php
															if (!$showExtension) {
																$optionlink = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&amp;tab=plugin&amp;single=' . html_encode($extension);
																?>
																<span class="icons"><a href="<?php echo $optionlink; ?>" title="<?php printf(gettext("Change %s options"), html_encode($extension)); ?>"><img class="icon-position-top3" src="images/options.png" alt="" />
																		<?php
																	}
																	echo html_encode($plugin_name);
																	if(!empty($plugin_version)) {
																		echo ' v'. html_encode($plugin_version);
																	}
																	if(!empty($plugin_date)) {
																		echo ' <small>('. html_encode($plugin_date).')</small>';
																	}
																	if (!$showExtension) {
																		?>
																	</a></span>
																<?php
															}
															if ($warn) {
																?>
																<img src="images/action.png" alt="<?php echo gettext('warning'); ?>" />
																<?php
															}
															?>
														</th>
														<th style="text-align:left; font-weight: normal;" colspan="2">
															<?php
															echo $plugin_description;
															if($plugin_deprecated) {
																echo '<p class="warningbox"><strong>' . gettext('Deprecated').  ':</strong> ' . $plugin_deprecated . '</p>';
															}
															?>
														</th>
													</tr>
													<?php
													if ($warn) {
														?>
														<tr style="display:none" class="pluginextrahide">
															<td colspan="3">
																<p class="notebox" ><?php echo $warn; ?></p>
															</td>
														</tr>
														<?php
													}
													if ($showExtension) {
														$supportedOptions = $option_interface->getOptionsSupported();
														if (count($supportedOptions) > 0) {
															customOptions($option_interface, '', NULL, false, $supportedOptions, NULL, NULL, $extension);
														}
													}
													?>
												</table>
											</td>
										</tr>
										<?php
									}
								}
								if ($_zp_plugin_count == 0) {
									?>
									<tr>
										<td style="padding: 0;margin:0" colspan="3">
											<?php
											echo gettext("There are no plugin options to administer.");
											?>
										</td>
									</tr>
									<?php
								} else {
									?>
									<tr>
										<th></th>
										<th style="text-align:right; padding-right: 10px;">
											<?php printPageSelector($pagenumber, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
										</th>
										<th></th>
									</tr>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
								</table> <!-- single plugin page table -->
								<input type="hidden" name="checkForPostTruncation" value="1" />
								<?php
							}
							?>
						</form>

					</div>
					<!-- end of tab_plugin div -->
					<?php
				}
				if ($subtab == 'security' && zp_loggedin(ADMIN_RIGHTS)) {
					?>
					<div id="tab_security" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="savesecurityoptions" value="yes" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Server protocol:"); ?></td>
									<td width="350">
										<select id="server_protocol" name="server_protocol">
											<option value="http" <?php if (SERVER_PROTOCOL == 'http') echo 'selected="selected"'; ?>>http</option>
											<option value="https" <?php if (SERVER_PROTOCOL == 'https') echo 'selected="selected"'; ?>>https</option>
										</select>
									</td>
									<td>
										<p><?php echo gettext("Normally this option should be set to <em>http</em>. If you are running a secure server, change this to <em>https</em>."); ?></p>
										<p class="warningbox"><?php
											echo gettext('<strong>Warning:</strong> If you select <em>https</em> your server <strong>MUST</strong> support <em>https</em>. ' .
															'If you set <em>https</em> on a server which does not support <em>https</em> you will not be able to access the <code>admin</code> pages to reset the option! ' .
															'Your only possibility then is to set or add <code>$conf["server_protocol"] = "http";</code> to your <code>zenphoto.cfg.php</code> file .');
											?>
										</p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Cookie security') ?></td>
									<td>
										<label><input type="checkbox" name="IP_tied_cookies" value="1" <?php checked(1, getOption('IP_tied_cookies')); ?> /></label>
									</td>
									<td>
										<?php echo gettext('Tie cookies to the IP address of the browser.'); ?>
										<?php if (!getOption('IP_tied_cookies')) { ?>
										<div class="warningbox">
											<p>
												<?php echo gettext('<strong>Warning</strong>: If your browser does not present a consistant IP address during a session you may not be able to log into your site when this option is enabled.');?>
											</p>
											<p>
												<?php echo gettext('You <strong>WILL</strong> have to login after changing this option.'); ?>
											</p>
											<p>
												<?php gettext('If you set the option and cannot login, you will have to restore your database to a point when the option was not set, so you might want to backup your database first.'); ?>
											</p>
											<p><?php echo gettext('This will not work properly if Zenphoto is set to anonymize the IP address which is strongly advised for privacy concerns in many jurisdictions.'); ?>
											</p>
										</div>
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext('Obscure cache filenames'); ?></td>
									<td width="350">
										<label><input type="checkbox" name="obfuscate_cache" id="obfuscate_cache" value="1" <?php checked(1, getOption('obfuscate_cache')); ?> /></label>
									</td>
									<td><?php echo gettext('Cause the filename of cached items to be obscured. This makes it difficult for someone to "guess" the name in a URL.'); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext('Image Processor security') ?></td>
									<td>
										<label><input type="checkbox" name="image_processor_flooding_protection" value="1" <?php checked(1, getOption('image_processor_flooding_protection')); ?> /></label>
									</td>
									<td>
										<?php echo gettext('Add a security parameter to image processor URIs to prevent denial of service attacks requesting arbitrary sized images.'); ?>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									$disable = $_zp_gallery->getUser() || getOption('search_user') || getOption('protected_image_user') || getOption('downloadList_user');
									?>
									<div class="public_gallery">
										<tr>
											<td><?php echo gettext('User name'); ?></td>
											<td>
												<label>
													<?php
													if ($disable) {
														?>
														<input type="hidden" name="login_user_field" value="1" />
														<input type="checkbox" name="login_user_field_disabled" id="login_user_field"
																	 value="1" checked="checked" disabled="disabled" />
																	 <?php
																 } else {
																	 ?>
														<input type="checkbox" name="login_user_field" id="login_user_field"
																	 value="1" <?php checked('1', $_zp_gallery->getUserLogonField()); ?> />
																	 <?php
																 }
																 ?>
												</label>
											</td>
											<td>
												<?php
												echo gettext('If enabled guest logon forms will include the <em>User Name</em> field. This allows <em>Zenphoto</em> users to logon from the form.');
												if ($disable) {
													echo '<p class="notebox">' . gettext('<strong>Note</strong>: This field is required because one or more of the <em>Guest</em> passwords has a user name associated.') . '</p>';
												}
												?>
											</td>
										</tr>
									</div>
									<?php
								} else {
									?>
									<input type="hidden" name="login_user_field" id="login_user_field"	value="<?php echo $_zp_gallery->getUserLogonField(); ?>" />
									<?php
								}
								?>
								<tr>
									<td width="175">
										<p><?php echo gettext('Anonymize IP'); ?></p>
									</td>
									<td width="350">
										<label>
											<?php
												$anonymize_ip = getOption('anonymize_ip');
												$anonymize_ip_levels = array(
													gettext('0 - No anonymizing') => 0,
													gettext('1 - Last fourth anonymized') => 1,
													gettext('2 - Last half anonymized') => 2,
													gettext('3 - Last three fourths anonymized') => 3,
													gettext('4 - Full anonymization, no IP stored') => 4
												);
											?>
											<select id="anonymize_ip" name="anonymize_ip">
												<?php	generateListFromArray(array($anonymize_ip), $anonymize_ip_levels, false, true); ?>
											</select>
											<?php echo gettext('Anonymize level'); ?>
										</label>
									</td>
									<td width="175">
										<p><?php echo gettext('Zenphoto stores the IP address of visitors on several occasions (e.g. rating, spam filtering, comment posting). '
														. 'In some jurisdictions like the EU and its GDPR the IP address is considered private information and therefore it is required to not store the full IP address or no IP at all.'
														. 'Choose your level of anonymization so parts are replaced by 0. This covers both IPv4 (1.1.1.0) and IPv6 (1:1:1:1:1:1:0:0) addresses.'); ?>
										</p>
									</td>
								</tr>
								<?php
								$data_policy_sharedtext = gettext('This is used by the official plugins <em>comment_form</em>, <em>contact_form</em> and <em>register_user</em> plugins if the data usage confirmation is enabled. Other plugins or usages must implement <code>getDataUsageNotice()/printDataUsageNotice()</code> specifially.');
								?>
								<tr>
									<td width="175">
										<p><?php echo gettext('Data privacy usage notice'); ?></p>
									</td>
									<td width="350">
										 <?php print_language_string_list(getOption('dataprivacy_policy_notice'), 'dataprivacy_policy_notice', true); ?>
									</td>
									<td width="175">
										<p><?php echo gettext('Here you can define the data usage confirmation notice that is recommended if your site is using forms submitting data in some jurisdictions like the EU and its GDPR. Leave empty to use the default text:'); ?></p>
										<blockquote><?php echo gettext('By using this form you agree with the storage and handling of your data by this website.'); ?></blockquote>
										<p class="notebox">
											<?php echo $data_policy_sharedtext; ?>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext('Data privacy policy page'); ?></p>
									</td>
									<td width="350">
										<?php printZenpagePageSelector('dataprivacy_policy_zenpage', 'dataprivacy_policy_custompage', false); ?>
										<p>
											<label>
											<?php print_language_string_list(getOption('dataprivacy_policy_customlinktext'), 'dataprivacy_policy_customlinktext'); ?>
											<br><?php echo gettext('Custom link text'); ?>
										</label>
										</p>
									</td>
									<td width="175">
										<p><?php echo gettext('Here you can define your data policy statement page that is recommended to have in jurisdictions like the EU and its GDPR.'); ?></p>
										<p><?php echo gettext('If the Zenpage CMS plugin is enabled and also its pages feature you can select one of its pages, otherwise enter a full custom page url manually which would also override the Zenpage page selection.'); ?></p>
										<p><?php echo gettext('Additionally you can define a custom text for the page link. If not set the default text <em>More info on our data privacy policy.</em> is used.'); ?></p>
										<p class="notebox">
											<?php echo $data_policy_sharedtext; ?>
										</p>
									</td>
								<tr>
									<?php
									$supportedOptions = $_zp_authority->getOptionsSupported();
									if (count($supportedOptions) > 0) {
										customOptions($_zp_authority, '');
									}
									?>
								</tr>
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table> <!-- security page table -->
						</form>
					</div>
					<!-- end of tab_security div -->
					<?php
				}
				?>
			</div><!-- end of container -->

		</div><!-- end of content -->
	</div><!-- end of main -->
	<?php printAdminFooter(); ?>


</body>
</html>

