<?php

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
			$languages = i18n::generateLanguageList(true);
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
					$result = i18n::setLocale($newloc);
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