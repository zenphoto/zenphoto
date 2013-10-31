<?php
/**
 * support functions for Admin
 * @package admin
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions.php');

define('TEXTAREA_COLUMNS', 50);
define('TEXT_INPUT_SIZE', 48);
define('TEXTAREA_COLUMNS_SHORT', 32);
define('TEXT_INPUT_SIZE_SHORT', 30);

/**
 * Print the footer <div> for the bottom of all admin pages.
 *
 * @param string $addl additional text to output on the footer.
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */
function printAdminFooter($addl = '') {
	?>
	<div id="footer">
		<?php
		printf(gettext('<a href="http://www.zenphoto.org" title="The simpler media website CMS">Zen<strong>photo</strong></a> version %1$s [%2$s]'), ZENPHOTO_VERSION, ZENPHOTO_RELEASE);
		if (!empty($addl)) {
			echo ' | ' . $addl;
		}
		?>
		| <a href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/license.php' ?>" title="<?php echo gettext('Zenphoto licence'); ?>"><?php echo gettext('License'); ?></a>
		| <a href="http://www.zenphoto.org/news/category/user-guide" title="<?php echo gettext('User guide'); ?>"><?php echo gettext('User guide'); ?></a>
		| <a href="http://www.zenphoto.org/support/" title="<?php echo gettext('Forum'); ?>"><?php echo gettext('Forum'); ?></a>
		| <a href="https://github.com/zenphoto/zenphoto/issues" title="<?php echo gettext('Bugtracker'); ?>"><?php echo gettext('Bugtracker'); ?></a>
		| <a href="http://www.zenphoto.org/news/category/changelog" title="<?php echo gettext('View Change log'); ?>"><?php echo gettext('Change log'); ?></a>
		| <?php printf(gettext('Server date: %s'), date('Y-m-d H:i:s')); ?>
	</div>
	<?php
	db_close(); //	close the database as we are done
}

function datepickerJS() {
	$lang = str_replace('_', '-', getOption('locale'));
	if (!file_exists(SERVERPATH . '/' . ZENFOLDER . '/js/jqueryui/i18n/jquery.ui.datepicker-' . $lang . '.js')) {
		$lang = substr($lang, 0, 2);
		if (!file_exists(SERVERPATH . '/' . ZENFOLDER . '/js/jqueryui/i18n/jquery.ui.datepicker-' . $lang . '.js')) {
			$lang = '';
		}
	}
	if (!empty($lang)) {
		?>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/i18n/jquery.ui.datepicker-<?php echo $lang; ?>.js" type="text/javascript"></script>
		<?php
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		$.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
		// ]]> -->
	</script>
	<?php
}

/**
 * Print the header for all admin pages. Starts at <DOCTYPE> but does not include the </head> tag,
 * in case there is a need to add something further.
 *
 * @param string $tab the album page
 * @param string $subtab the sub-tab if any
 */
function printAdminHeader($tab, $subtab = NULL) {
	global $_zp_admin_tab, $_zp_admin_subtab, $_zp_gallery, $zenphoto_tabs, $_zp_RTL_css;
	$_zp_admin_tab = $tab;
	if (isset($_GET['tab'])) {
		$_zp_admin_subtab = sanitize($_GET['tab'], 3);
	} else {
		$_zp_admin_subtab = $subtab;
	}
	$tabtext = $_zp_admin_tab;
	$tabrow = NULL;
	foreach ($zenphoto_tabs as $key => $tabrow) {
		if ($key == $_zp_admin_tab) {
			$tabtext = $tabrow['text'];
			break;
		}
		$tabrow = NULL;
	}
	if (empty($_zp_admin_subtab) && $tabrow && isset($tabrow['default'])) {
		$_zp_admin_subtab = $zenphoto_tabs[$_zp_admin_tab]['default'];
	}
	$subtabtext = '';
	if ($_zp_admin_subtab && $tabrow && array_key_exists('subtabs', $tabrow) && $tabrow['subtabs']) {
		foreach ($tabrow['subtabs'] as $key => $link) {
			$i = strpos($link, '&amp;tab=');
			if ($i !== false) {
				$text = substr($link, $i + 9);
				if ($text == $_zp_admin_subtab) {
					$subtabtext = '-' . $key;
					break;
				}
			}
		}
	}
	if (empty($subtabtext)) {
		if ($_zp_admin_subtab) {
			$subtabtext = '-' . $_zp_admin_subtab;
		}
	}
	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	zp_apply_filter('admin_headers');
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/toggleElements.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui-zenphoto.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css" type="text/css" />
			<?php
			if ($_zp_RTL_css) {
				?>
				<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-rtl.css" type="text/css" />
				<?php
			}
			?>
			<title><?php echo sprintf(gettext('%1$s %2$s: %3$s%4$s'), html_encode($_zp_gallery->getTitle()), gettext('admin'), html_encode($tabtext), html_encode($subtabtext)); ?></title>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.js" type="text/javascript"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui-zenphoto.js" type="text/javascript"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zenphoto.js" type="text/javascript" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/admin.js" type="text/javascript" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.scrollTo.js" type="text/javascript"></script>
			<script type="text/javascript">
		// <!-- <![CDATA[
	<?php
	if (zp_has_filter('admin_head', 'colorbox::css')) {
		?>
			$(document).ready(function() {
				$("a.colorbox").colorbox({
					maxWidth: "98%",
					maxHeight: "98%",
					close: '<?php echo gettext("close"); ?>'
				});
			});
		<?php
	}
	?>
		$(function() {
			$(".tooltip ").tooltip({
				show: 1000,
				hide: 1000,
				position: {
					my: "center bottom-20",
					at: "center top",
					using: function(position, feedback) {
						$(this).css(position);
						$("<div>")
										.addClass("arrow")
										.addClass(feedback.vertical)
										.addClass(feedback.horizontal)
										.appendTo(this);
					}
				}
			});
			$(".page-list_icon").tooltip({
				show: 1000,
				hide: 1000,
				position: {
					my: "center bottom-20",
					at: "center top",
					using: function(position, feedback) {
						$(this).css(position);
						$("<div>")
										.addClass("arrow")
										.addClass(feedback.vertical)
										.addClass(feedback.horizontal)
										.appendTo(this);
					}
				}
			});
		});
		jQuery(function($) {
			$(".fade-message").fadeTo(5000, 1).fadeOut(1000);
		})
		// ]]> -->
			</script>
			<?php
			zp_apply_filter('admin_head');
		}

		function printSortableHead() {
			?>
			<!--Nested Sortables-->
			<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.ui.nestedSortable.js"></script>
			<script type="text/javascript">
		//<!-- <![CDATA[
		$(document).ready(function() {

			$('ul.page-list').nestedSortable({
				disableNesting: 'no-nest',
				forcePlaceholderSize: true,
				handle: 'div',
				items: 'li',
				opacity: .6,
				placeholder: 'placeholder',
				tabSize: 25,
				tolerance: 'intersect',
				toleranceElement: '> div',
				listType: 'ul'
			});

			$('.serialize').click(function() {
				serialized = $('ul.page-list').nestedSortable('serialize');
				if (serialized != original_order) {
					$('#serializeOutput').html('<input type="hidden" name="order" size="30" maxlength="1000" value="' + serialized + '" />');
				}
			})
			var original_order = $('ul.page-list').nestedSortable('serialize');
		});
		// ]]> -->
			</script>
			<!--Nested Sortables End-->
			<?php
		}

		/**
		 * Print the html required to display the ZP logo and links in the top section of the admin page.
		 *
		 * @author Todd Papaioannou (lucky@luckyspin.org)
		 * @since  1.0.0
		 */
		function printLogoAndLinks() {
			global $_zp_current_admin_obj, $_zp_admin_tab, $_zp_admin_subtab, $_zp_gallery;
			if ($_zp_admin_subtab) {
				$subtab = '-' . $_zp_admin_subtab;
			} else {
				$subtab = '';
			}
			?>
		<span id="administration">
			<img id="logo" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png"
					 title="<?php echo sprintf(gettext('%1$s administration:%2$s%3$s'), html_encode($_zp_gallery->getTitle()), html_encode($_zp_admin_tab), html_encode($subtab)); ?>"
					 alt="<?php echo gettext('Zenphoto Administration'); ?>" align="bottom" />
		</span>
		<?php
		echo "\n<div id=\"links\">";
		echo "\n  ";
		if (!is_null($_zp_current_admin_obj)) {
			$sec = (int) ((SERVER_PROTOCOL == 'https') & true);
			$last = $_zp_current_admin_obj->getLastlogon();
			if (empty($last)) {
				printf(gettext('Logged in as %1$s'), $_zp_current_admin_obj->getUser());
			} else {
				printf(gettext('Logged in as %1$s (last login %2$s)'), $_zp_current_admin_obj->getUser(), $last);
			}
			if ($_zp_current_admin_obj->logout_link) {
				$link = WEBPATH . "/" . ZENFOLDER . "/admin.php?logout=" . $sec;
				echo " &nbsp; | &nbsp; <a href=\"" . $link . "\">" . gettext("Log Out") . "</a> &nbsp; | &nbsp; ";
			}
		}
		echo ' <a href="' . FULLWEBPATH . '/">';
		$t = $_zp_gallery->getTitle();
		if (!empty($t)) {
			printf(gettext("View <em>%s</em>"), $t);
		} else {
			echo gettext("View gallery index");
		}
		echo "</a>";
		echo "\n</div>";
	}

	/**
	 * Print the nav tabs for the admin section. We determine which tab should be highlighted
	 * from the $_GET['page']. If none is set, we default to "home".
	 *
	 * @author Todd Papaioannou (lucky@luckyspin.org)
	 * @since  1.0.0
	 */
	function printTabs() {
		global $subtabs, $zenphoto_tabs, $main_tab_space, $_zp_admin_tab;
		$chars = 0;
		foreach ($zenphoto_tabs as $atab) {
			$chars = $chars + mb_strlen($atab['text']);
		}
		switch (getOption('locale')) {
			case 'zh_CN':
			case 'zh_TW':
			case 'ja_JP':
				$main_tab_space = count($zenphoto_tabs) * 3 + $chars;
				break;
			default:
				$main_tab_space = round((count($zenphoto_tabs) * 32 + round($chars * 7.5)) / 11.5);
				break;
		}
		?>
		<ul class="nav" style="width: <?php echo $main_tab_space; ?>em">
			<?php
			foreach ($zenphoto_tabs as $key => $atab) {
				?>
				<li <?php if ($_zp_admin_tab == $key) echo 'class="current"' ?>>
					<a href="<?php echo $atab['link']; ?>"><?php echo $atab['text']; ?></a>
					<?php
					$subtabs = $zenphoto_tabs[$key]['subtabs'];
					if (is_array($subtabs)) { // don't print <ul> if there is nothing
						if ($_zp_admin_tab != $key) { // don't print sublist if already on the main tab
							?>
							<ul class="subdropdown">
								<?php
								foreach ($subtabs as $key => $link) {
									if (strpos($link, '/') !== 0) { // zp_core relative
										$link = WEBPATH . '/' . ZENFOLDER . '/' . $link;
									} else {
										$link = WEBPATH . $link;
									}
									?>
									<li><a href="<?php echo $link; ?>"><?php echo $key; ?></a></li>
									<?php
								} // foreach end
								?>
							</ul>
							<?php
						} // if $subtabs end
					} // if array
					?>
				</li>
				<?php
			}
			?>
		</ul>
		<br class="clearall" /><!-- needed so the nav sits correctly -->
		<?php
	}

	function getSubtabs() {
		global $zenphoto_tabs, $_zp_admin_tab, $_zp_admin_subtab;
		$tabs = @$zenphoto_tabs[$_zp_admin_tab]['subtabs'];
		if (!is_array($tabs))
			return $_zp_admin_subtab;
		$current = $_zp_admin_subtab;
		if (isset($_GET['tab'])) {
			$test = sanitize($_GET['tab']);
			foreach ($tabs as $link) {
				$i = strrpos($link, 'tab=');
				$amp = strrpos($link, '&');
				if ($i !== false) {
					if ($amp > $i) {
						$link = substr($link, 0, $amp);
					}
					if ($test == substr($link, $i + 4)) {
						$current = $test;
						break;
					}
				}
			}
		}
		if (empty($current)) {
			if (isset($zenphoto_tabs[$_zp_admin_tab]['default'])) {
				$current = $zenphoto_tabs[$_zp_admin_tab]['default'];
			} else if (empty($_zp_admin_subtab)) {
				$current = array_shift($tabs);
				$i = strrpos($current, 'tab=');
				$amp = strrpos($current, '&');
				if ($i === false) {
					$current = '';
				} else {
					if ($amp > $i) {
						$current = substr($current, 0, $amp);
					}
					$current = substr($current, $i + 4);
				}
			} else {
				$current = $_zp_admin_subtab;
			}
		}
		return $current;
	}

	function printSubtabs() {
		global $zenphoto_tabs, $_zp_admin_tab, $_zp_admin_subtab;
		$tabs = @$zenphoto_tabs[$_zp_admin_tab]['subtabs'];
		$current = getSubtabs();
		if (!empty($tabs)) {
			$chars = 0;
			foreach ($tabs as $atab => $val) {
				$chars = $chars + mb_strlen($atab);
			}
			switch (getOption('locale')) {
				case 'zh_CN':
				case 'zh_TW':
				case 'ja_JP':
					$sub_tab_space = count($tabs) * 3 + $chars;
					break;
				default:
					$sub_tab_space = round((count($tabs) * 32 + round($chars * 7.5)) / 11.5);
					break;
			}
			?>
			<ul class="subnav" style="width: <?php echo $sub_tab_space; ?>em">
				<?php
				foreach ($tabs as $key => $link) {
					$i = strrpos($link, 'tab=');
					$amp = strrpos($link, '&');
					if ($i === false) {
						$tab = $_zp_admin_subtab;
					} else {
						if ($amp > $i) {
							$source = substr($link, 0, $amp);
						} else {
							$source = $link;
						}
						$tab = substr($source, $i + 4);
					}
					if (!$link) {
						$bt = debug_backtrace();
						$bt = array_shift($bt);
						if (isset($bt['file'])) {
							$link = str_replace(SERVERPATH, '', str_replace('\\', '/', $bt['file']));
						}
					}
					if (strpos($link, '/') !== 0) { // zp_core relative
						$link = 'href = "' . WEBPATH . '/' . ZENFOLDER . '/' . $link . '"';
					} else {
						$link = 'href = "' . WEBPATH . $link . '"';
					}
					echo '<li' . (($current == $tab) ? ' class="current"' : '') . '><a id="' . $tab . '" ' . $link . '>' . $key . '</a></li>' . "\n";
				}
				?>
			</ul>
			<?php
		}
		return $current;
	}

	function setAlbumSubtabs($album) {
		global $zenphoto_tabs;
		$albumlink = '?page=edit&amp;album=' . urlencode($album->name);
		$default = NULL;
		if (!is_array($zenphoto_tabs['edit']['subtabs'])) {
			$zenphoto_tabs['edit']['subtabs'] = array();
		}
		$subrights = $album->albumSubRights();
		if (!$album->isDynamic() && $album->getNumImages()) {
			if ($subrights & (MANAGED_OBJECT_RIGHTS_UPLOAD || MANAGED_OBJECT_RIGHTS_EDIT)) {
				$zenphoto_tabs['edit']['subtabs'] = array_merge(
								array(gettext('Images') => 'admin-edit.php' . $albumlink . '&amp;tab=imageinfo'), $zenphoto_tabs['edit']['subtabs']
				);
				$default = 'imageinfo';
			}
			if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
				$zenphoto_tabs['edit']['subtabs'] = array_merge(
								array(gettext('Image order') => 'admin-albumsort.php' . $albumlink . '&amp;tab=sort'), $zenphoto_tabs['edit']['subtabs']
				);
			}
		}
		if (!$album->isDynamic() && $album->getNumAlbums() > 0) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge(
							array(gettext('Subalbums') => 'admin-edit.php' . $albumlink . '&amp;tab=subalbuminfo'), $zenphoto_tabs['edit']['subtabs']
			);
			$default = 'subalbuminfo';
		}
		if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge(
							array(gettext('Album') => 'admin-edit.php' . $albumlink . '&amp;tab=albuminfo'), $zenphoto_tabs['edit']['subtabs']
			);
			$default = 'albuminfo';
		}
		$zenphoto_tabs['edit']['default'] = $default;
		if (isset($_GET['tab'])) {
			return sanitize($_GET['tab']);
		}
		return $default;
	}

	function checked($checked, $current) {
		if ($checked == $current)
			echo ' checked="checked"';
	}

	function genAlbumList(&$list, $curAlbum = NULL, $rights = UPLOAD_RIGHTS) {
		global $_zp_gallery;
		if (is_null($curAlbum)) {
			$albums = array();
			$albumsprime = $_zp_gallery->getAlbums(0);
			foreach ($albumsprime as $album) { // check for rights
				$albumobj = newAlbum($album);
				if ($albumobj->isMyItem($rights)) {
					$albums[] = $album;
				}
			}
		} else {
			$albums = $curAlbum->getAlbums(0);
		}
		if (is_array($albums)) {
			foreach ($albums as $folder) {
				$album = newAlbum($folder);
				if ($album->isDynamic()) {
					if ($rights == ALL_ALBUMS_RIGHTS) {
						$list[$album->getFolder()] = $album->getTitle();
					}
				} else {
					$list[$album->getFolder()] = $album->getTitle();
					genAlbumList($list, $album, $rights); /* generate for subalbums */
				}
			}
		}
	}

	define('CUSTOM_OPTION_PREFIX', '_ZP_CUSTOM_');
	/**
	 * Generates the HTML for custom options (e.g. theme options, plugin options, etc.)
	 * Note: option names may not contain '.', '+', nor '%' as PHP POST handling will replace
	 * these with an underscore.
	 *
	 * @param object $optionHandler the object to handle custom options
	 * @param string $indent used to indent the option for nested options
	 * @param object $album if not null, the album to which the option belongs
	 * @param bool $hide set to true to hide the output (used by the plugin-options folding
	 * $paran array $supportedOptions pass these in if you already have them
	 * @param bool $theme set true if dealing with theme options
	 * @param string $initial initila show/hide state
	 *
	 * Custom options:
	 *    OPTION_TYPE_TEXTBOX:          A textbox
	 *    OPTION_TYPE_PASSWORD:         A passowrd textbox
	 *    OPTION_TYPE_CLEARTEXT:     	  A textbox, but no sanitization on save
	 *    OPTION_TYPE_CHECKBOX:         A checkbox
	 *    OPTION_TYPE_CUSTOM:           Handled by $optionHandler->handleOption()
	 *    OPTION_TYPE_TEXTAREA:         A textarea
	 *    OPTION_TYPE_RADIO:            Radio buttons (button names are in the 'buttons' index of the supported options array)
	 *    OPTION_TYPE_SELECTOR:         Selector (selection list is in the 'selections' index of the supported options array
	 *                                  null_selection contains the text for the empty selection. If not present there
	 *                                  will be no empty selection)
	 *    OPTION_TYPE_CHECKBOX_ARRAY:   Checkbox array (checkbox list is in the 'checkboxes' index of the supported options array.)
	 *    OPTION_TYPE_CHECKBOX_UL:      Checkbox UL (checkbox list is in the 'checkboxes' index of the supported options array.)
	 *    OPTION_TYPE_COLOR_PICKER:     Color picker
	 *    OPTION_TYPE_NOTE:             Places a note in the options area. The note will span all three columns
	 *
	 *    Types 0 and 5 support multi-lingual strings.
	 */
	define('OPTION_TYPE_TEXTBOX', 0);
	define('OPTION_TYPE_CHECKBOX', 1);
	define('OPTION_TYPE_CUSTOM', 2);
	define('OPTION_TYPE_TEXTAREA', 3);
	define('OPTION_TYPE_RADIO', 4);
	define('OPTION_TYPE_SELECTOR', 5);
	define('OPTION_TYPE_CHECKBOX_ARRAY', 6);
	define('OPTION_TYPE_CHECKBOX_UL', 7);
	define('OPTION_TYPE_COLOR_PICKER', 8);
	define('OPTION_TYPE_CLEARTEXT', 9);
	define('OPTION_TYPE_NOTE', 10);
	define('OPTION_TYPE_PASSWORD', 11);

	function customOptions($optionHandler, $indent = "", $album = NULL, $showhide = false, $supportedOptions = NULL, $theme = false, $initial = 'none', $extension = NULL) {
		if (is_null($supportedOptions)) {
			$supportedOptions = $optionHandler->getOptionsSupported();
		}
		if (count($supportedOptions) > 0) {
			$whom = get_class($optionHandler);
			$options = $supportedOptions;
			$option = array_shift($options);
			if (array_key_exists('order', $option)) {
				$options = sortMultiArray($supportedOptions, 'order', false, true, false, true);
				$options = array_keys($options);
			} else {
				$options = array_keys($supportedOptions);
				natcasesort($options);
			}
			if (method_exists($optionHandler, 'handleOptionSave')) {
				?>
				<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX; ?>save-<?php echo $whom; ?>" value="<?php echo $extension; ?>" />
				<?php
			}
			foreach ($options as $option) {
				$row = $supportedOptions[$option];
				if (false !== $i = stripos($option, chr(0))) {
					$option = substr($option, 0, $i);
				}

				$type = $row['type'];
				$desc = $row['desc'];
				$key = @$row['key'];
				$optionID = $whom . '_' . $key;
				if (isset($row['multilingual'])) {
					$multilingual = $row['multilingual'];
				} else {
					$multilingual = $type == OPTION_TYPE_TEXTAREA;
				}
				if (isset($row['texteditor']) && $row['texteditor']) {
					$editor = 'texteditor';
				} else {
					$editor = '';
				}
				if (isset($row['disabled']) && $row['disabled']) {
					$disabled = ' disabled="disabled"';
				} else {
					$disabled = '';
				}
				if ($theme) {
					$v = getThemeOption($key, $album, $theme);
				} else {
					$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($key);
					$db = query_single_row($sql);
					if ($db) {
						$v = $db['value'];
					} else {
						$v = NULL;
					}
				}

				if ($showhide) {
					?>
					<tr id="tr_<?php echo $optionID; ?>" class="<?php echo $showhide; ?>extrainfo" style="display:<?php echo $initial; ?>">
						<?php
					} else {
						?>
					<tr id="tr_<?php echo $optionID; ?>">
						<?php
					}
					if ($type != OPTION_TYPE_NOTE) {
						?>
						<td width="175"><?php if ($option) echo $indent . $option; ?></td>
						<?php
					}
					switch ($type) {
						case OPTION_TYPE_NOTE:
							?>
							<td colspan="3"><?php echo $desc; ?></td>
							<?php
							break;
						case OPTION_TYPE_CLEARTEXT:
							$multilingual = false;
						case OPTION_TYPE_PASSWORD:
						case OPTION_TYPE_TEXTBOX:
						case OPTION_TYPE_TEXTAREA:
							if ($type == OPTION_TYPE_CLEARTEXT) {
								$clear = 'clear';
							} else {
								$clear = '';
							}
							if ($type == OPTION_TYPE_PASSWORD) {
								$inputtype = 'password';
								$multilingual = false;
							} else {
								$inputtype = 'text';
							}
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . $clear . 'text-' . $key; ?>" value="1" />
								<?php
								if ($multilingual) {
									print_language_string_list($v, $key, $type, NULL, $editor);
								} else {
									if ($type == OPTION_TYPE_TEXTAREA) {
										$v = get_language_string($v); // just in case....
										?>
										<textarea id="<?php echo $key; ?>" name="<?php echo $key; ?>" cols="<?php echo TEXTAREA_COLUMNS; ?>"	style="width: 320px" rows="6"<?php echo $disabled; ?>><?php echo html_encode($v); ?></textarea>
										<?php
									} else {
										?>
										<input type="<?php echo $inputtype; ?>" size="40" id="<?php echo $key; ?>" name="<?php echo $key; ?>" style="width: 338px" value="<?php echo html_encode($v); ?>"<?php echo $disabled; ?> />
										<?php
									}
								}
								?>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $key; ?>" value="1" />
								<input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php checked('1', $v); ?><?php echo $disabled; ?> />
							</td>
							<?php
							break;
						case OPTION_TYPE_CUSTOM:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'custom-' . $key; ?>" value="0" />
								<?php $optionHandler->handleOption($key, $v); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_RADIO:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'radio-' . $key; ?>" value="1"<?php echo $disabled; ?> />
								<?php generateRadiobuttonsFromArray($v, $row['buttons'], $key, $behind, 'checkboxlabel', $disabled); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_SELECTOR:
							?>
							<td width="350">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'selector-' . $key ?>" value="1" />
								<select id="<?php echo $key; ?>" name="<?php echo $key; ?>"<?php echo $disabled; ?> >
									<?php
									if (array_key_exists('null_selection', $row)) {
										?>
										<option value=""<?php if (empty($v)) echo ' selected="selected"'; ?> style="background-color:LightGray;"><?php echo $row['null_selection']; ?></option>
										<?php
									}
									?>
									<?php generateListFromArray(array($v), $row['selections'], false, true); ?>
								</select>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_ARRAY:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td width="350">
								<?php
								foreach ($row['checkboxes'] as $display => $checkbox) {
									if ($theme) {
										$v = getThemeOption($checkbox, $album, $theme);
									} else {
										$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($checkbox);
										$db = query_single_row($sql);
										if ($db) {
											$v = $db['value'];
										} else {
											$v = 0;
										}
									}
									$display = str_replace(' ', '&nbsp;', $display);
									?>
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $checkbox; ?>" value="1" />

									<label class="checkboxlabel">
										<?php if ($behind) echo($display); ?>
										<input type="checkbox" id="<?php echo $checkbox; ?>" name="<?php echo $checkbox; ?>" value="1"<?php checked('1', $v); ?><?php echo $disabled; ?> />
										<?php if (!$behind) echo($display); ?>
									</label>
									<?php
								}
								?>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_UL:
							?>
							<td width="350">
								<?php
								$all = true;
								$cvarray = array();
								foreach ($row['checkboxes'] as $display => $checkbox) {
									?>
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $checkbox; ?>" value="1" />
									<?php
									if ($theme) {
										$v = getThemeOption($checkbox, $album, $theme);
									} else {
										$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($checkbox);
										$db = query_single_row($sql);
										if ($db) {
											$v = $db['value'];
										} else {
											$v = 0;
										}
									}
									if ($v) {
										$cvarray[] = $checkbox;
									} else {
										$all = false;
									}
								}
								?>
								<ul class="customchecklist">
									<?php generateUnorderedListFromArray($cvarray, $row['checkboxes'], '', '', true, true, 'all_' . $key); ?>
								</ul>
								<script type="text/javascript">
									// <!-- <![CDATA[
									function <?php echo $key; ?>_all() {
										var check = $('#all_<?php echo $key; ?>').prop('checked');
										$('.all_<?php echo $key; ?>').prop('checked', check);
									}
									// ]]> -->
								</script>
								<label>
									<input type="checkbox" name="all_<?php echo $key; ?>" id="all_<?php echo $key; ?>" class="all_<?php echo $key; ?>" onclick="<?php echo $key; ?>_all();" <?php if ($all) echo ' checked="checked"'; ?>/>
									<?php echo gettext('all'); ?>
								</label>
							</td>
							<?php
							break;
						case OPTION_TYPE_COLOR_PICKER:
							if (empty($v))
								$v = '#000000';
							?>
							<td width="350" style="margin:0; padding:0">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'text-' . $key; ?>" value="1" />
								<script type="text/javascript">
									// <!-- <![CDATA[
									$(document).ready(function() {
										$('#<?php echo $key; ?>_colorpicker').farbtastic('#<?php echo $key; ?>');
									});
									// ]]> -->
								</script>
								<table style="margin:0; padding:0" >
									<tr>
										<td><input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>"	value="<?php echo $v; ?>" style="height:100px; width:100px; float:right;" /></td>
										<td><div id="<?php echo $key; ?>_colorpicker"></div></td>
									</tr>
								</table>
							</td>
							<?php
							break;
					}
					if ($type != OPTION_TYPE_NOTE) {
						?>
						<td><?php echo $desc; ?></td>
						<?php
					}
					?>
				</tr>
				<?php
			}
		}
	}

	function processCustomOptionSave($returntab, $themename = NULL, $themealbum = NULL) {
		$customHandlers = array();
		foreach ($_POST as $postkey => $value) {
			if (preg_match('/^' . CUSTOM_OPTION_PREFIX . '/', $postkey)) { // custom option!
				$key = substr($postkey, strpos($postkey, '-') + 1);
				$switch = substr($postkey, strlen(CUSTOM_OPTION_PREFIX), -strlen($key) - 1);
				switch ($switch) {
					case 'text':
						$value = process_language_string_save($key, 1);
						break;
					case 'cleartext':
						if (isset($_POST[$key])) {
							$value = sanitize($_POST[$key], 0);
						} else {
							$value = '';
						}
						break;
					case 'chkbox':
						$value = (int) isset($_POST[$key]);
						break;
					case 'save':
						$customHandlers[] = array('whom' => $key, 'extension' => sanitize($_POST[$postkey]));
						continue;
						break;
					default:
						if (isset($_POST[$key])) {
							$value = sanitize($_POST[$key], 1);
						} else {
							$value = '';
						}
						break;
				}
				if ($themename) {
					setThemeOption($key, $value, $themealbum, $themename);
				} else {
					setOption($key, $value);
				}
			} else {
				if (strpos($postkey, 'show-') === 0) {
					if ($value)
						$returntab .= '&' . $postkey;
				}
			}
		}
		foreach ($customHandlers as $custom) {
			if ($extension = $custom['extension']) {
				require_once(getPlugin($extension . '.php'));
			}
			$whom = new $custom['whom']();
			$returntab = $whom->handleOptionSave($themename, $themealbum) . $returntab;
		}
		return $returntab;
	}

	/**
	 *
	 * Set defaults for standard theme options incase the theme has not done so
	 * @param string $theme
	 * @param int $albumid zero or the album "owning" the theme
	 */
	function standardThemeOptions($theme, $album) {
		setThemeOption('albums_per_page', 6, $album, $theme, true);
		setThemeOption('albums_per_row', 3, $album, $theme, true);
		setThemeOption('images_per_page', 20, $album, $theme, true);
		setThemeOption('images_per_row', 5, $album, $theme, true);
		setThemeOption('image_size', 595, $album, $theme, true);
		setThemeOption('image_use_side', 'longest', $album, $theme, true);
		setThemeOption('thumb_size', 100, $album, $theme, true);
		setThemeOption('thumb_crop_width', 100, $album, $theme, true);
		setThemeOption('thumb_crop_height', 100, $album, $theme, true);
		setThemeOption('thumb_crop', 1, $album, $theme, true);
		setThemeOption('thumb_transition', 1, $album, $theme, true);
	}

	/**
	 * Encodes for use as a $_POST index
	 *
	 * @param string $str
	 */
	function postIndexEncode($str) {
		return strtr(urlencode($str), array('.' => '__2E__', '+' => '__20__', '%' => '__25__', '&' => '__26__'));
	}

	/**
	 * Decodes encoded $_POST index
	 *
	 * @param string $str
	 * @return string
	 */
	function postIndexDecode($str) {
		return urldecode(strtr($str, array('__2E__' => '.', '__20__' => '+', '__25__' => '%', '__26__' => '&')));
	}

	/**
	 * Prints radio buttons from an array
	 *
	 * @param string $currentvalue The current selected value
	 * @param string $list the array of the list items form is localtext => buttonvalue
	 * @param string $option the name of the option for the input field name
	 * @param bool $behind set true to have the "text" before the button
	 */
	function generateRadiobuttonsFromArray($currentvalue, $list, $option, $behind = false, $class = 'checkboxlabel', $disabled = NULL) {
		foreach ($list as $text => $value) {
			$checked = "";
			if ($value == $currentvalue) {
				$checked = ' checked="checked" '; //the checked() function uses quotes the other way round...
			}
			?>
			<label<?php if ($class) echo ' class="' . $class . '"'; ?>>
				<?php if ($behind) echo $text; ?>
				<input type="radio" name="<?php echo $option; ?>" id="<?php echo $option . '-' . $value; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?><?php echo $disabled; ?> />
				<?php if (!$behind) echo $text; ?>
			</label>
			<?php
		}
	}

	/**
	 * Creates the body of an unordered list with checkbox label/input fields (scrollable sortables)
	 *
	 * @param array $currentValue list of items to be flagged as checked
	 * @param array $list the elements of the select list
	 * @param string $prefix prefix of the input item
	 * @param string $alterrights are the items changable.
	 * @param bool $sort true for sorted list
	 * @param string $class optional class for items
	 * @param bool $localize true if the list local key is text for the item
	 */
	function generateUnorderedListFromArray($currentValue, $list, $prefix, $alterrights, $sort, $localize, $class = NULL, $extra = NULL) {
		if (is_null($extra))
			$extra = array();
		if (!empty($class))
			$class = ' class="' . $class . '" ';
		if ($sort) {
			if ($localize) {
				$list = array_flip($list);
				natcasesort($list);
				$list = array_flip($list);
			} else {
				natcasesort($list);
			}
		}
		$cv = array_flip($currentValue);
		foreach ($list as $key => $item) {
			$listitem = postIndexEncode($prefix . $item);
			if ($localize) {
				$display = $key;
			} else {
				$display = $item;
			}
			?>
			<li id="<?php echo strtolower($listitem); ?>_element">
				<label class="displayinline">
					<input id="<?php echo strtolower($listitem); ?>"<?php echo $class; ?> name="<?php echo $listitem; ?>" type="checkbox"
					<?php
					if (isset($cv[$item])) {
						echo ' checked="checked"';
					}
					?> value="1" <?php echo $alterrights; ?> />
								 <?php echo html_encode($display); ?>
				</label>
				<?php
				if (array_key_exists($item, $extra)) {
					$unique = '';
					foreach (array_reverse($extra[$item]) as $box) {
						if ($box['display']) {
							if (isset($box['disable'])) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = $alterrights;
							}
							if (isset($box['type'])) {
								$type = $box['type'];
								if ($type == 'radio')
									$unique++;
							} else {
								$type = 'checkbox';
							}
							?>
							<label class="displayinlineright">
								<input type="<?php echo $type; ?>" id="<?php echo strtolower($listitem) . '_' . $box['name'] . $unique; ?>"<?php echo $class; ?> name="<?php echo $listitem . '_' . $box['name']; ?>"
											 value="<?php echo html_encode($box['value']); ?>" <?php
											 if ($box['checked']) {
												 echo ' checked="checked"';
											 }
											 ?>
											 <?php echo $disable; ?> /> <?php echo $box['display']; ?>
							</label>
							<?php
						} else {
							?>
							<input type="hidden" id="<?php echo strtolower($listitem . '_' . $box['name']); ?>" name="<?php echo $listitem . '_' . $box['name']; ?>"<?php echo $class; ?>
										 value="<?php echo html_encode($box['value']); ?>" />
										 <?php
									 }
								 }
							 }
							 ?>
			</li>
			<?php
		}
	}

	/**
	 * Creates an unordered checklist of the tags
	 *
	 * @param object $that Object for which to get the tags
	 * @param string $postit prefix to prepend for posting
	 * @param bool $showCounts set to true to get tag count displayed
	 */
	function tagSelector($that, $postit, $showCounts = false, $mostused = false, $addnew = true, $resizeable = false) {
		global $_zp_admin_ordered_taglist, $_zp_admin_LC_taglist;
		if (is_null($_zp_admin_ordered_taglist)) {
			if ($mostused || $showCounts) {
				$counts = getAllTagsCount();
				if ($mostused)
					arsort($counts, SORT_NUMERIC);
				$them = array();
				foreach ($counts as $tag => $count) {
					$them[] = $tag;
				}
			} else {
				$them = getAllTagsUnique();
			}
			$_zp_admin_ordered_taglist = $them;
			$_zp_admin_LC_taglist = array();
			foreach ($them as $tag) {
				$_zp_admin_LC_taglist[] = mb_strtolower($tag);
			}
		} else {
			$them = $_zp_admin_ordered_taglist;
		}
		if (is_null($that)) {
			$tags = array();
		} else {
			$tags = $that->getTags();
		}

		if (count($tags) > 0) {
			foreach ($tags as $tag) {
				$tagLC = mb_strtolower($tag);
				$key = array_search($tagLC, $_zp_admin_LC_taglist);
				if ($key !== false) {
					unset($them[$key]);
				}
			}
		}
		if ($resizeable) {
			$tagclass = 'resizeable_tagchecklist';
			?>
			<script>
				$(function() {
					$("#resizable_<?php echo $postit; ?>").resizable({
						maxWidth: 250,
						minWidth: 250,
						minHeight: 120,
						resize: function(event, ui) {
							$('#list_<?php echo $postit; ?>').height($('#resizable_<?php echo $postit; ?>').height());
						}
					});
				});
			</script>
			<?php
		} else {
			$tagclass = 'tagchecklist';
		}
		if ($addnew) {
			?>
			<span class="new_tag displayinline" >
				<a href="javascript:addNewTag('<?php echo $postit; ?>');" title="<?php echo gettext('add tag'); ?>">
					<img src="images/add.png" title="<?php echo gettext('add tag'); ?>"/>
				</a>
				<input type="text" value="" name="newtag_<?php echo $postit; ?>" id="newtag_<?php echo $postit; ?>" />
			</span>

			<?php
		}
		?>
		<div id="resizable_<?php echo $postit; ?>">
			<ul id="list_<?php echo $postit; ?>" class="<?php echo $tagclass; ?>">
				<?php
				if ($showCounts) {
					$displaylist = array();
					foreach ($them as $tag) {
						$displaylist[$tag . ' [' . $counts[$tag] . ']'] = $tag;
					}
				} else {
					$displaylist = $them;
				}
				if (count($tags) > 0) {
					generateUnorderedListFromArray($tags, $tags, $postit, false, !$mostused, $showCounts, 'checkTagsAuto');
					?>
					<li><hr /></li>
					<?php
				}
				generateUnorderedListFromArray(array(), $displaylist, $postit, false, !$mostused, $showCounts, 'checkTagsAuto');
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * emits the html for editing album information
	 * called in edit album and mass edit
	 * @param string $index the index of the entry in mass edit or '0' if single album
	 * @param object $album the album object
	 * @param bool $buttons set true for "apply" buttons
	 * @since 1.1.3
	 */
	function printAlbumEditForm($index, $album, $buttons = true) {
		global $sortby, $_zp_gallery, $mcr_albumlist, $_zp_albumthumb_selector, $_zp_current_admin_obj;
		$isPrimaryAlbum = '';
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$myalbum = $_zp_current_admin_obj->getAlbum();
			if ($myalbum && $album->getID() == $myalbum->getID()) {
				$isPrimaryAlbum = ' disabled="disabled"';
			}
		}
		$tagsort = getTagOrder();
		if ($index == 0) {
			$suffix = $prefix = '';
		} else {
			$prefix = "$index-";
			$suffix = "_$index";
			echo "<p><em><strong>" . $album->name . "</strong></em></p>";
		}
		?>
		<input type="hidden" name="<?php echo $prefix; ?>folder" value="<?php echo $album->name; ?>" />
		<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
		<input	type="hidden" name="password_enabled<?php echo $suffix; ?>" id="password_enabled<?php echo $suffix; ?>" value="0" />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<?php
				$parent = dirname($album->name);
				if ($parent == '/' || $parent == '.' || empty($parent)) {
					$parent = '';
				} else {
					$parent = '&amp;album=' . $parent . '&amp;tab=subalbuminfo';
				}
				?>
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<img	src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="javascript:$('.deletemsg').hide();" >
					<img	src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>', true);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<?php
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFolder())); ?>">
						<img src="images/view.png" alt="" />
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>
			</span>
			<?php
		}
		?>
		<br class="clearall" /><br />
		<table class="formlayout">
			<tr>
				<td valign="top">
					<table class="width100percent">
						<tr>
							<td class="leftcolumn"><?php echo gettext("Owner"); ?></td>
							<td class="middlecolumn">
								<?php
								if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<select name="<?php echo $prefix; ?>owner">
										<?php echo admin_album_list($album->getOwner()); ?>
									</select>
									<?php
								} else {
									echo $album->getOwner();
								}
								?>
							</td>
						</tr>
						<tr>
							<td class="leftcolumn">
								<?php echo gettext("Album Title"); ?>:
							</td>
							<td class="middlecolumn">
								<?php print_language_string_list($album->getTitle('all'), $prefix . "albumtitle", false, null, '', '100%'); ?>
							</td>
						</tr>

						<tr>
							<td class="leftcolumn">
								<?php echo gettext("Album Description:"); ?>
							</td>
							<td>
								<?php print_language_string_list($album->getDesc('all'), $prefix . "albumdesc", true, NULL, 'texteditor', '100%'); ?>
							</td>
						</tr>
						<?php
						if (GALLERY_SECURITY == 'public') {
							?>
							<tr class="password<?php echo $suffix; ?>extrashow">
								<td class="leftcolumn">
									<p>
										<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',true);">
											<?php echo gettext("Album password:"); ?>
										</a>
									</p>
								</td>
								<td class="middlecolumn">
									<?php
									$x = $album->getPassword();
									if (empty($x)) {
										?>
										<img src="images/lock_open.png" />
										<?php
									} else {
										$x = '          ';
										?>
										<a onclick="resetPass('<?php echo $suffix; ?>');" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
										<?php
									}
									?>
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',false);">
										<?php echo gettext("Album guest user:"); ?>
									</a>
									<br />
									<label><input type="checkbox" name="disclose_password<?php echo $suffix; ?>"
																id="disclose_password<?php echo $suffix; ?>"
																onclick="passwordClear('<?php echo $suffix; ?>');
																				togglePassword('<?php echo $suffix; ?>');" /><?php echo gettext('Show password'); ?></label>
								</td>
								<td>
									<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
												 onkeydown="passwordClear
																 '<?php echo $suffix; ?>'
																				 );"
												 id="user_name<?php echo $suffix; ?>" name="user<?php echo $suffix; ?>"
												 value="<?php echo $album->getUser(); ?>" />
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<p>
										<span id="strength<?php echo $suffix; ?>"><?php echo gettext("Album password:"); ?></span>
										<br />
										<span id="match<?php echo $suffix; ?>" class="password_field_<?php echo $suffix; ?>">
											<?php echo gettext("repeat password:"); ?>
										</span>
									</p>
									<p>
										<?php echo gettext("Password hint:"); ?>
									</p>
								</td>
								<td>
									<p>
										<input type="password"
													 id="pass<?php echo $suffix; ?>" name="pass<?php echo $suffix; ?>"
													 onkeydown="passwordClear
																	 '<?php echo $suffix; ?>'
																					 );"
													 onkeyup="passwordStrength('<?php echo $suffix; ?>');"
													 value="<?php echo $x; ?>" />
										<br />
										<span class="password_field_<?php echo $suffix; ?>">
											<input type="password"
														 id="pass_r<?php echo $suffix; ?>" name="pass_r<?php echo $suffix; ?>" disabled="disabled"
														 onkeydown="passwordClear
																		 '<?php echo $suffix; ?>'
																						 );"
														 onkeyup="passwordMatch('<?php echo $suffix; ?>');"
														 value="<?php echo $x; ?>" />
										</span>
									</p>
									<p>
										<?php print_language_string_list($album->getPasswordHint('all'), "hint" . $suffix, false, NULL, 'hint', '100%'); ?>
									</p>
								</td>
							</tr>
							<?php
						}
						$d = $album->getDateTime();
						if ($d == "0000-00-00 00:00:00") {
							$d = "";
						}
						?>


						<tr>
							<td class="leftcolumn"><?php echo gettext("Date:"); ?> </td>
							<td>
								<script type="text/javascript">
									// <!-- <![CDATA[
									$(function() {
										$("#datepicker<?php echo $suffix; ?>").datepicker({
											showOn: 'button',
											buttonImage: 'images/calendar.png',
											buttonText: '<?php echo gettext('calendar'); ?>',
											buttonImageOnly: true
										});
									});
									// ]]> -->
								</script>
								<input type="text" id="datepicker<?php echo $suffix; ?>" size="20" name="<?php echo $prefix; ?>albumdate" value="<?php echo $d; ?>" />
							</td>
						</tr>
						<tr>
							<td class="leftcolumn"><?php echo gettext("Location:"); ?> </td>
							<td class="middlecolumn">
								<?php print_language_string_list($album->getLocation(), $prefix . "albumlocation", false, NULL, 'hint', '100%'); ?>
							</td>
						</tr>
						<?php
						$custom = zp_apply_filter('edit_album_custom_data', '', $album, $prefix);
						if (empty($custom)) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Custom data:"); ?></td>
								<td><?php print_language_string_list($album->getCustomData('all'), $prefix . "album_custom_data", true, NULL, 'texteditor_albumcustomdata', '100%'); ?></td>
							</tr>
							<?php
						} else {
							echo $custom;
						}
						$sort = $sortby;
						if (!$album->isDynamic()) {
							$sort[gettext('Manual')] = 'manual';
						}
						$sort[gettext('Custom')] = 'custom';
						/*
						 * not recommended--screws with peoples minds during pagination!
						  $sort[gettext('Random')] = 'random';
						 */
						?>
						<tr>
							<td class="leftcolumn"><?php echo gettext("Sort subalbums by:"); ?> </td>
							<td>
								<span class="nowrap">
									<select id="albumsortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>subalbumsortby" onchange="update_direction(this, 'album_direction_div<?php echo $suffix; ?>', 'album_custom_div<?php echo $suffix; ?>');">
										<?php
										if (is_null($album->getParent())) {
											$globalsort = gettext("*gallery album sort order");
										} else {
											$globalsort = gettext("*parent album subalbum sort order");
										}
										echo "\n<option value =''>$globalsort</option>";
										$cvt = $type = strtolower($album->get('subalbum_sort_type'));
										if ($type && !in_array($type, $sort)) {
											$cv = array('custom');
										} else {
											$cv = array($type);
										}
										generateListFromArray($cv, $sort, false, true);
										?>
									</select>
									<?php
									if (($type == 'manual') || ($type == 'random') || ($type == '')) {
										$dsp = 'none';
									} else {
										$dsp = 'inline';
									}
									?>
									<label id="album_direction_div<?php echo $suffix; ?>" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
										<?php echo gettext("Descending"); ?>
										<input type="checkbox" name="<?php echo $prefix; ?>album_sortdirection" value="1" <?php
										if ($album->getSortDirection('album')) {
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
								<span id="album_custom_div<?php echo $suffix; ?>" class="customText" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
									<br />
									<?php echo gettext('custom fields:') ?>
									<input id="customalbumsort<?php echo $suffix; ?>" class="customalbumsort" name="<?php echo $prefix; ?>customalbumsort" type="text" value="<?php echo html_encode($cvt); ?>" />
								</span>
							</td>
						</tr>

						<tr>
							<td class="leftcolumn"><?php echo gettext("Sort images by:"); ?> </td>
							<td>
								<span class="nowrap">
									<select id="imagesortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>sortby" onchange="update_direction(this, 'image_direction_div<?php echo $suffix; ?>', 'image_custom_div<?php echo $suffix; ?>')">
										<?php
										if (is_null($album->getParent())) {
											$globalsort = gettext("*gallery image sort order");
										} else {
											$globalsort = gettext("*parent album image sort order");
										}
										?>
										<option value =""><?php echo $globalsort; ?></option>
										<?php
										$cvt = $type = strtolower($album->get('sort_type'));
										if ($type && !in_array($type, $sort)) {
											$cv = array('custom');
										} else {
											$cv = array($type);
										}
										generateListFromArray($cv, $sort, false, true);
										?>
									</select>
									<?php
									if (($type == 'manual') || ($type == 'random') || ($type == '')) {
										$dsp = 'none';
									} else {
										$dsp = 'inline';
									}
									?>
									<label id="image_direction_div<?php echo $suffix; ?>" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
										<?php echo gettext("Descending"); ?>
										<input type="checkbox" name="<?php echo $prefix; ?>image_sortdirection" value="1"
										<?php
										if ($album->getSortDirection('image')) {
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
								<span id="image_custom_div<?php echo $suffix; ?>" class="customText" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
									<br />
									<?php echo gettext('custom fields:') ?>
									<input id="customimagesort<?php echo $suffix; ?>" class="customimagesort" name="<?php echo $prefix; ?>customimagesort" type="text" value="<?php echo html_encode($cvt); ?>" />
								</span>
							</td>
						</tr>

						<?php
						if (is_null($album->getParent())) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Album theme:"); ?> </td>
								<td>
									<select id="album_theme" class="album_theme" name="<?php echo $prefix; ?>album_theme"	<?php if (!zp_loggedin(THEMES_RIGHTS)) echo 'disabled="disabled" '; ?>	>
										<?php
										$themes = $_zp_gallery->getThemes();
										$oldtheme = $album->getAlbumTheme();
										if (empty($oldtheme)) {
											$selected = 'selected="selected"';
										} else {
											$selected = '';
										}
										?>
										<option value="" style="background-color:LightGray" <?php echo $selected; ?> ><?php echo gettext('*gallery theme'); ?></option>
										<?php
										foreach ($themes as $theme => $themeinfo) {
											if ($oldtheme == $theme) {
												$selected = 'selected="selected"';
											} else {
												$selected = '';
											}
											?>
											<option value = "<?php echo $theme; ?>" <?php echo $selected; ?> ><?php echo $themeinfo['name']; ?></option>
											<?php
										}
										?>
									</select>
								</td>
							</tr>
							<?php
						}
						if (!$album->isDynamic()) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Album watermarks:"); ?> </td>
								<td>
									<?php $current = $album->getWatermark(); ?>
									<select id="album_watermark<?php echo $suffix; ?>" name="<?php echo $prefix; ?>album_watermark">
										<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
										<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
										<?php
										$watermarks = getWatermarks();
										generateListFromArray(array($current), $watermarks, false, false);
										?>
									</select>
									<em><?php echo gettext('Images'); ?></em>
								</td>
							</tr>
							<tr>
								<td class="leftcolumn"></td>
								<td>
									<?php $current = $album->getWatermarkThumb(); ?>
									<select id="album_watermark_thumb<?php echo $suffix; ?>" name="<?php echo $prefix; ?>album_watermark_thumb">
										<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
										<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
										<?php
										$watermarks = getWatermarks();
										generateListFromArray(array($current), $watermarks, false, false);
										?>
									</select>
									<em><?php echo gettext('Thumbs'); ?></em>
								</td>
							</tr>
							<?php
						}
						if ($index == 0) { // suppress for mass-edit
							$showThumb = $_zp_gallery->getThumbSelectImages();
							$album->getAlbumThumbImage(); //	prime the thumbnail since we will get the field below
							$thumb = $album->get('thumb');
							$selections = array();
							$selected = array();
							foreach ($_zp_albumthumb_selector as $key => $selection) {
								$selections[$selection['desc']] = $key;
								if ($key == $thumb) {
									$selected[] = $key;
								}
							}
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Thumbnail:"); ?> </td>
								<td>
									<?php
									if ($showThumb) {
										?>
										<script type="text/javascript">
											// <!-- <![CDATA[
											updateThumbPreview(document.getElementById('thumbselect'));
											// ]]> -->
										</script>
										<?php
									}
									?>
									<select style="width:320px" <?php if ($showThumb) { ?>class="thumbselect" onchange="updateThumbPreview(this);" <?php } ?> name="<?php echo $prefix; ?>thumb">
										<?php
										generateListFromArray($selected, $selections, false, true);
										$imagelist = $album->getImages(0);
										if ($_zp_gallery->getSecondLevelThumbs()) {
											$subalbums = $album->getAlbums(0);
											foreach ($subalbums as $folder) {
												$newalbum = newAlbum($folder);
												$images = $newalbum->getImages(0);
												foreach ($images as $filename) {
													if (is_array($filename)) {
														$imagelist[] = $filename;
													} else {
														$imagelist[] = '/' . $folder . '/' . $filename;
													}
												}
											}
										}
										if ($thumb && !is_numeric($thumb)) {
											// check for current thumb being in the list. If not, add it
											$target = $thumb;
											$targetA = array('folder' => dirname($thumb), 'filename' => basename($thumb));
											if (!in_array($target, $imagelist) && !in_array($targetA, $imagelist)) {
												array_unshift($imagelist, $target);
											}
										}
										if (!empty($imagelist)) {
											// there are some images to choose from
											foreach ($imagelist as $imagename) {
												if (is_array($imagename)) {
													$image = newImage(NULL, $imagename);
													$imagename = '/' . $imagename['folder'] . '/' . $imagename['filename'];
													$filename = basename($imagename);
												} else {
													$albumname = dirname($imagename);
													if (empty($albumname) || $albumname == '.') {
														$thumbalbum = $album;
													} else {
														$thumbalbum = newAlbum($albumname);
													}
													$filename = basename($imagename);
													$image = newImage($thumbalbum, $filename);
												}
												$selected = ($imagename == $thumb);
												if (is_valid_image($filename) || !is_null($image->objectsThumb)) {
													echo "\n<option";
													if ($_zp_gallery->getThumbSelectImages()) {
														echo " class=\"thumboption\"";
														echo " style=\"background-image: url(" . html_encode(pathurlencode(getAdminThumb($image, 'large'))) . "); background-repeat: no-repeat;\"";
													}
													echo " value=\"" . $imagename . "\"";
													if ($selected) {
														echo " selected=\"selected\"";
													}
													echo ">" . $image->getTitle();
													if ($filename != $image->getTitle()) {
														echo " ($filename)";
													}
													echo "</option>";
												}
											}
										}
										?>
									</select>
								</td>
							</tr>
							<?php
						}
						?>
						<tr valign="top">
							<td class="leftcolumn topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
							<td>
								<br />
								<?php printCodeblockEdit($album, (int) $suffix); ?>
							</td>
						</tr>
					</table>
				</td>
				<?php $bglevels = array('#fff', '#f8f8f8', '#efefef', '#e8e8e8', '#dfdfdf', '#d8d8d8', '#cfcfcf', '#c8c8c8'); ?>
				<td class="rightcolumn" valign="top">
					<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
					<div class="box-edit">
						<label class="checkboxlabel">
							<input type="checkbox" name="<?php echo $prefix; ?>Published" value="1" <?php if ($album->getShow()) echo ' checked="checked"'; ?> />
							<?php echo gettext("Published"); ?>
						</label>
						<label class="checkboxlabel">
							<input type="checkbox" name="<?php echo $prefix . 'allowcomments'; ?>" value="1" <?php
							if ($album->getCommentsAllowed()) {
								echo ' checked="checked"';
							}
							?> />
										 <?php echo gettext("Allow Comments"); ?>
						</label>
						<?php
						if (extensionEnabled('hitcounter')) {
							$hc = $album->get('hitcounter');
							if (empty($hc)) {
								$hc = '0';
							}
							?>
							<label class="checkboxlabel">
								<input type="checkbox" name="reset_hitcounter<?php echo $prefix; ?>"<?php if (!$hc) echo ' disabled="disabled"'; ?> />
								<?php echo sprintf(ngettext("Reset hit counter (%u hit)", "Reset hit counter (%u hits)", $hc), $hc); ?>
							</label>
							<?php
						}
						if (extensionEnabled('rating')) {
							$tv = $album->get('total_value');
							$tc = $album->get('total_votes');

							if ($tc > 0) {
								$hc = $tv / $tc;
								?>
								<label class="checkboxlabel">
									<input type="checkbox" id="reset_rating<?php echo $suffix; ?>" name="<?php echo $prefix; ?>reset_rating" value="1" />
									<?php printf(gettext('Reset rating (%u stars)'), $hc); ?>
								</label>
								<?php
							} else {
								?>
								<label class="checkboxlabel">
									<input type="checkbox" name="<?php echo $prefix; ?>reset_rating" value="1" disabled="disabled"/>
									<?php echo gettext('Reset rating (unrated)'); ?>
								</label>
								<?php
							}
						}
						$publishdate = $album->getPublishDate();
						$expirationdate = $album->getExpireDate();
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							$(function() {
								$("#<?php echo $prefix; ?>publishdate,#<?php echo $prefix; ?>expirationdate").datepicker({
									showOn: 'button',
									buttonImage: '../zp-core/images/calendar.png',
									buttonText: '<?php echo gettext("calendar"); ?>',
									buttonImageOnly: true
								});
								$('#<?php echo $prefix; ?>publishdate').change(function() {
									var today = new Date();
									var pub = $('#<?php echo $prefix; ?>publishdate').datepicker('getDate');
									if (pub.getTime() > today.getTime()) {
										$(".<?php echo $prefix; ?>scheduledpublishing").html('<br /><?php echo addslashes(gettext('Future publishing date.')); ?>');
									} else {
										$(".<?php echo $prefix; ?>scheduledpublishing").html('');
									}
								});
								$('#<?php echo $prefix; ?>expirationdate').change(function() {
									var today = new Date();
									var expiry = $('#<?php echo $prefix; ?>expirationdate').datepicker('getDate');
									if (expiry.getTime() > today.getTime()) {
										$(".<?php echo $prefix; ?>expire").html('');
									} else {
										$(".<?php echo $prefix; ?>expire").html('<br /><?php echo addslashes(gettext('Expired!')); ?>');
									}
								});
							});
							// ]]> -->
						</script>
						<br class="clearall" />
						<hr />
						<p>
							<label for="<?php echo $prefix; ?>publishdate"><?php echo gettext('Publish date'); ?> <small>(YYYY-MM-DD)</small></label>
							<br /><input value="<?php echo $publishdate; ?>" type="text" size="20" maxlength="30" name="publishdate-<?php echo $prefix; ?>" id="<?php echo $prefix; ?>publishdate" />
							<strong class="scheduledpublishing-<?php echo $prefix; ?>" style="color:red">
								<?php
								if (!empty($publishdate) && ($publishdate > date('Y-m-d H:i:s'))) {
									echo '<br />' . gettext('Future publishing date.');
								}
								?>
							</strong>
							<br /><br />
							<label for="<?php echo $prefix; ?>expirationdate"><?php echo gettext('Expiration date'); ?> <small>(YYYY-MM-DD)</small></label>
							<br /><input value="<?php echo $expirationdate; ?>" type="text" size="20" maxlength="30" name="expirationdate-<?php echo $prefix; ?>" id="<?php echo $prefix; ?>expirationdate" />
							<strong class="<?php echo $prefix; ?>expire" style="color:red">
								<?php
								if (!empty($expirationdate) && ($expirationdate <= date('Y-m-d H:i:s'))) {
									echo '<br />' . gettext('Expired!');
								}
								?>
							</strong>
						</p>
					</div>
					<!-- **************** Move/Copy/Rename ****************** -->
					<?php ?>
					<h2 class="h2_bordered_edit"><?php echo gettext("Utilities"); ?></h2>
					<div class="box-edit">

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>move" name="a-<?php echo $prefix; ?>MoveCopyRename" value="move"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'move');"<?php echo $isPrimaryAlbum; ?> />
										 <?php echo gettext("Move"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>copy" name="a-<?php echo $prefix; ?>MoveCopyRename" value="copy"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'copy');"/>
										 <?php echo gettext("Copy"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>rename" name="a-<?php echo $prefix; ?>MoveCopyRename" value="rename"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'rename');" <?php echo $isPrimaryAlbum; ?> />
										 <?php echo gettext("Rename Folder"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="Delete-<?php echo $prefix; ?>" name="a-<?php echo $prefix; ?>MoveCopyRename" value="delete"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');
													 deleteConfirm('Delete-<?php echo $prefix; ?>', '<?php echo $prefix; ?>', deleteAlbum1);" <?php echo $isPrimaryAlbum; ?> />
										 <?php echo gettext("Delete album"); ?>
						</label>

						<br class="clearall" />
						<div class="deletemsg" id="deletemsg<?php echo $prefix; ?>"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
							<?php echo gettext('Album will be deleted when changes are applied.'); ?>
							<br class="clearall" />
							<p class="buttons">
								<a	href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel"); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>movecopydiv" style="padding-top: .5em; padding-left: .5em; display: none;">
							<?php echo gettext("to:"); ?>
							<select id="a-<?php echo $prefix; ?>albumselectmenu" name="a-<?php echo $prefix; ?>albumselect" onchange="">
								<?php
								$exclude = $album->name;
								if (count(explode('/', $exclude)) > 1 && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<option value="" selected="selected">/</option>
									<?php
								}
								foreach ($mcr_albumlist as $fullfolder => $albumtitle) {
									// don't allow copy in place or to subalbums
									if ($fullfolder == dirname($exclude) || $fullfolder == $exclude || strpos($fullfolder, $exclude . '/') === 0) {
										$disabled = ' disabled="disabled"';
									} else {
										$disabled = '';
									}
									// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
									$singlefolder = $fullfolder;
									$saprefix = '';
									$salevel = 0;

									while (strstr($singlefolder, '/') !== false) {
										$singlefolder = substr(strstr($singlefolder, '/'), 1);
										$saprefix = "&nbsp; &nbsp;&nbsp;" . $saprefix;
										$salevel = ($salevel + 1) % 8;
									}
									echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: ' . $bglevels[$salevel] . ';"' : '')
									. "$disabled>" . $saprefix . $singlefolder . "</option>\n";
								}
								?>
							</select>
							<br class="clearall" /><br />
							<p class="buttons">
								<a href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel"); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>renamediv" style="padding-top: .5em; padding-left: .5em; display: none;">
							<?php echo gettext("to:"); ?>
							<input name="a-<?php echo $prefix; ?>renameto" type="text" value="<?php echo basename($album->name); ?>"/><br />
							<br class="clearall" />
							<p class="buttons">
								<a href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel"); ?></a>
							</p>
						</div>
						<span class="clearall" ></span>
						<?php
						echo zp_apply_filter('edit_album_utilities', '', $album, $prefix);
						printAlbumButtons($album);
						?>
						<span class="clearall" ></span>
					</div>
					<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?></h2>
					<div class="box-edit-unpadded">
						<?php
						$tagsort = getTagOrder();
						tagSelector($album, 'tags_' . $prefix, false, $tagsort, true, true);
						?>
					</div>
				</td>
			</tr>
		</table>
		<?php
		if ($album->isDynamic()) {
			?>
			<table>
				<tr>
					<td align="left" valign="top" width="150"><?php echo gettext("Dynamic album search:"); ?></td>
					<td>
						<table class="noinput">
							<tr>
								<td><?php echo html_encode(urldecode($album->getSearchParams())); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<?php
		}
		?>

		<br class="clearall" />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<img	src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="javascript:$('.deletemsg').hide();">
					<img	src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>', true);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<?php
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFolder())); ?>">
						<img src="images/view.png" alt="" />
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>
			</span>
			<?php
		}
		?>
		<br class="clearall" />
		<?php
	}

	/**
	 * puts out the maintenance buttons for an album
	 *
	 * @param object $album is the album being emitted
	 */
	function printAlbumButtons($album) {
		if ($imagcount = $album->getNumImages() > 0) {
			?>
			<div class="button buttons tooltip" title="<?php echo gettext("Clears the album's cached images."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=clear_cache&amp;album=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('clear_cache'); ?>">
					<img src="images/edit-delete.png" /><?php echo gettext('Clear album image cache'); ?></a>
				<br class="clearall" />
			</div>
			<div class="button buttons tooltip" title="<?php echo gettext("Resets album's hit counters."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=reset_hitcounters&amp;album=' . html_encode($album->name) . '&amp;albumid=' . $album->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter'); ?>">
					<img src="images/reset.png" /><?php echo gettext('Reset album hit counters'); ?></a>
				<br class="clearall" />
			</div>
			<?php
		}
		if ($imagcount || (!$album->isDynamic() && $album->getNumAlbums() > 0)) {
			?>
			<div class="button buttons tooltip" title="<?php echo gettext("Refreshes the metadata for the album."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-refresh-metadata.php?album=' . html_encode($album->name) . '&amp;return=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh'); ?>">
					<img src="images/cache.png" /><?php echo gettext('Refresh album metadata'); ?></a>
				<br class="clearall" />
			</div>
			<?php
		}
	}

	function printAlbumLegend() {
		?>
		<ul class="iconlegend-l">
			<li><img src="images/folder_picture.png" alt="" /><?php echo gettext("Albums"); ?></li>
			<li><img src="images/pictures.png" alt="" /><?php echo gettext("Images"); ?></li>
			<li><img src="images/folder_picture_dn.png" alt="" /><?php echo gettext("Albums (dynamic)"); ?></li>
			<li><img src="images/pictures_dn.png" alt="I" /><?php echo gettext("Images (dynamic)"); ?></li>
		</ul>
		<ul class="iconlegend">
			<?php
			if (GALLERY_SECURITY == 'public') {
				?>
				<li><img src="images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
				<?php
			}
			?>
			<li><img src="images/pass.png" alt="Published" /><img src="images/action.png" alt="" /><?php echo gettext("Published/Un-published"); ?></li>
			<li><img src="images/comments-on.png" alt="" /><img src="images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
			<li><img src="images/view.png" alt="" /><?php echo gettext("View the album"); ?></li>
			<li><img src="images/refresh.png" alt="" /><?php echo gettext("Refresh metadata"); ?></li>
			<?php
			if (extensionEnabled('hitcounter')) {
				?>
				<li><img src="images/reset.png" alt="" /><?php echo gettext("Reset hit counters"); ?></li>
				<?php
			}
			?>
			<li><img src="images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
		</ul>
		<?php
	}

	/**
	 * puts out a row in the edit album table
	 *
	 * @param object $album is the album being emitted
	 * @param bool $show_thumb set to false to show thumb standin image rather than album thumb
	 * @param object $owner the parent album (or NULL for gallery)
	 *
	 * */
	function printAlbumEditRow($album, $show_thumb, $owner) {
		global $_zp_current_admin_obj;
		$enableEdit = $album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT;
		if (is_object($owner)) {
			$owner = $owner->name;
		}
		?>
		<div class='page-list_row'>

			<div class="page-list_albumthumb">
				<?php
				if ($show_thumb) {
					$thumbimage = $album->getAlbumThumbImage();
					$thumb = getAdminThumb($thumbimage, 'small');
				} else {
					$thumb = 'images/thumb_standin.png';
				}
				if ($enableEdit) {
					?>
					<a href="?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
						<?php
					}
					?>
					<img src="<?php echo html_encode(pathurlencode($thumb)); ?>" width="40" height="40" alt="" title="album thumb" />
					<?php
					if ($enableEdit) {
						?>
					</a>
					<?php
				}
				?>
			</div>
			<div class="page-list_albumtitle">
				<?php
				if ($enableEdit) {
					?>
					<a href="?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
						<?php
					}
					echo strip_tags($album->getTitle());
					if ($enableEdit) {
						?>
					</a>
					<?php
				}
				?>
			</div>
			<?php
			if ($album->isDynamic()) {
				$imgi = '<img src="images/pictures_dn.png" alt="" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture_dn.png" alt="" title="' . gettext('albums') . '" />';
			} else {
				$imgi = '<img src="images/pictures.png" alt="" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture.png" alt="" title="' . gettext('albums') . '" />';
			}
			$ci = count($album->getImages());
			$si = sprintf('%1$s <span>(%2$u)</span>', $imgi, $ci);
			if ($ci > 0 && !$album->isDynamic()) {
				$si = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=imageinfo" title="' . gettext('Subalbum List') . '">' . $si . '</a>';
			}
			$ca = $album->getNumAlbums();
			$sa = sprintf('%1$s <span>(%2$u)</span>', $imga, $ca);
			if ($ca > 0 && !$album->isDynamic()) {
				$sa = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=subalbuminfo" title="' . gettext('Subalbum List') . '">' . $sa . '</a>';
			}
			?>
			<div class="page-list_extra"><?php echo $sa; ?></div>
			<div class="page-list_extra"><?php echo $si; ?></div>
			<?php $wide = '40px'; ?>
			<div class="page-list_iconwrapperalbum">
				<div class="page-list_icon">
					<?php
					$pwd = $album->getPassword();
					if (!empty($pwd)) {
						echo '<a title="' . gettext('Password protected') . '"><img src="images/lock.png" style="border: 0px;" alt="" title="' . gettext('Password protected') . '" /></a>';
					}
					?>
				</div>
				<div class="page-list_icon">
					<?php
					if ($album->getShow()) {
						if ($enableEdit) {
							?>
							<a href="?action=publish&amp;value=0&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo sprintf(gettext('Un-publish the album %s'), $album->name); ?>" >
								<?php
							}
							?>
							<img src="images/pass.png" style="border: 0px;" alt="" title="<?php echo gettext('Published'); ?>" />
							<?php
							if ($enableEdit) {
								?>
							</a>
							<?php
						}
					} else {
						if ($enableEdit) {
							?>
							<a href="?action=publish&amp;value=1&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo sprintf(gettext('Publish the album %s'), $album->name); ?>">
								<?php
							}
							?>
							<img src="images/action.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Unpublished'), $album->name); ?>" />
							<?php
							if ($enableEdit) {
								?>
							</a>
							<?php
						}
					}
					?>
				</div>
				<div class="page-list_icon">
					<?php
					if ($album->getCommentsAllowed()) {
						if ($enableEdit) {
							?>
							<a href="?action=comments&amp;commentson=0&amp;album=<?php echo html_encode($album->getFolder()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Disable comments'); ?>">
								<?php
							}
							?>
							<img src="images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
							<?php
							if ($enableEdit) {
								?>
							</a>
							<?php
						}
					} else {
						if ($enableEdit) {
							?>
							<a href="?action=comments&amp;commentson=1&amp;album=<?php echo html_encode($album->getFolder()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Enable comments'); ?>">
								<?php
							}
							?>
							<img src="images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
							<?php
							if ($enableEdit) {
								?>
							</a>
							<?php
						}
					}
					?>
				</div>
				<div class="page-list_icon">
					<a href="<?php echo WEBPATH; ?>/index.php?album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo gettext("View album"); ?>">
						<img src="images/view.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('View album %s'), $album->name); ?>" />
					</a>
				</div>
				<div class="page-list_icon">
					<?php
					if ($album->isDynamic() || !$enableEdit) {
						?>
						<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
						<?php
					} else {
						?>
						<a class="warn" href="admin-refresh-metadata.php?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh') ?>" title="<?php echo sprintf(gettext('Refresh metadata for the album %s'), $album->name); ?>">
							<img src="images/refresh.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Refresh metadata in the album %s'), $album->name); ?>" />
						</a>
						<?php
					}
					?>
				</div>
				<?php
				if (extensionEnabled('hitcounter')) {
					?>
					<div class="page-list_icon">
						<?php
						if ($album->isDynamic() || !$enableEdit) {
							?>
							<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
							<?php
						} else {
							?>
							<a class="reset" href="?action=reset_hitcounters&amp;albumid=<?php echo $album->getID(); ?>&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;subalbum=true&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo sprintf(gettext('Reset hit counters for album %s'), $album->name); ?>">
								<img src="images/reset.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Reset hit counters for the album %s'), $album->name); ?>" />
							</a>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
				<div class="page-list_icon">
					<?php
					$myalbum = $_zp_current_admin_obj->getAlbum();
					$supress = !zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS) && $myalbum && $album->getID() == $myalbum->getID();
					if (!$enableEdit || $supress) {
						?>
						<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
						<?php
					} else {
						?>
						<a class="delete" href="javascript:confirmDeleteAlbum('?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($album->name)); ?>&amp;return=<?php echo html_encode(pathurlencode(dirname($album->name))); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>');" title="<?php echo sprintf(gettext("Delete the album %s"), js_encode($album->name)); ?>">
							<img src="images/fail.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Delete the album %s'), js_encode($album->name)); ?>" />
						</a>
						<?php
					}
					?>
				</div>
				<?php
				if ($enableEdit) {
					?>
					<div class="page-list_icon">
						<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $album->getFolder(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" <?php if ($supress) echo ' disabled="disabled"'; ?> />
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * processes the post from the above
	 * @param int $index the index of the entry in mass edit or 0 if single album
	 * @param object $album the album object
	 * @param string $redirectto used to redirect page refresh on move/copy/rename
	 * @return string error flag if passwords don't match
	 * @since 1.1.3
	 */
	function processAlbumEdit($index, $album, &$redirectto) {
		$redirectto = NULL; // no redirection required
		if ($index == 0) {
			$prefix = $suffix = '';
		} else {
			$prefix = "$index-";
			$suffix = "_$index";
		}
		$tagsprefix = 'tags_' . $prefix;
		$notify = '';
		$album->setTitle(process_language_string_save($prefix . 'albumtitle', 2));
		$album->setDesc(process_language_string_save($prefix . 'albumdesc', 0));
		$tags = array();
		$l = strlen($tagsprefix);
		foreach ($_POST as $key => $value) {
			$key = postIndexDecode($key);
			if (substr($key, 0, $l) == $tagsprefix) {
				if ($value) {
					$tags[] = sanitize(substr($key, $l));
				}
			}
		}
		$tags = array_unique($tags);
		$album->setTags($tags);
		$album->setDateTime(sanitize($_POST[$prefix . "albumdate"]));
		$album->setLocation(process_language_string_save($prefix . 'albumlocation', 3));
		if (isset($_POST[$prefix . 'thumb']))
			$album->setAlbumThumb(sanitize($_POST[$prefix . 'thumb']));
		$album->setShow((int) isset($_POST[$prefix . 'Published']));
		$album->setCommentsAllowed(isset($_POST[$prefix . 'allowcomments']));
		$sorttype = strtolower(sanitize($_POST[$prefix . 'sortby'], 3));
		if ($sorttype == 'custom') {
			$sorttype = unquote(strtolower(sanitize($_POST[$prefix . 'customimagesort'], 3)));
		}
		$album->setSortType($sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection('image', 0);
		} else {
			if (empty($sorttype)) {
				$direction = 0;
			} else {
				$direction = isset($_POST[$prefix . 'image_sortdirection']);
			}
			$album->setSortDirection('image', $direction);
		}
		$sorttype = strtolower(sanitize($_POST[$prefix . 'subalbumsortby'], 3));
		if ($sorttype == 'custom')
			$sorttype = strtolower(sanitize($_POST[$prefix . 'customalbumsort'], 3));
		$album->setSubalbumSortType($sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection('album', 0);
		} else {
			$album->setSortDirection('album', isset($_POST[$prefix . 'album_sortdirection']));
		}
		if (isset($_POST['reset_hitcounter' . $prefix])) {
			$album->set('hitcounter', 0);
		}
		if (isset($_POST[$prefix . 'reset_rating'])) {
			$album->set('total_value', 0);
			$album->set('total_votes', 0);
			$album->set('used_ips', 0);
		}
		$album->setPublishDate(sanitize($_POST['publishdate-' . $prefix]));
		$album->setExpireDate(sanitize($_POST['expirationdate-' . $prefix]));
		$fail = '';
		processCredentials($album, $suffix);
		$oldtheme = $album->getAlbumTheme();
		if (isset($_POST[$prefix . 'album_theme'])) {
			$newtheme = sanitize($_POST[$prefix . 'album_theme']);
			if ($oldtheme != $newtheme) {
				$album->setAlbumTheme($newtheme);
			}
		}
		if (isset($_POST[$prefix . 'album_watermark'])) {
			$album->setWatermark(sanitize($_POST[$prefix . 'album_watermark'], 3));
			$album->setWatermarkThumb(sanitize($_POST[$prefix . 'album_watermark_thumb'], 3));
		}
		$album->setCodeblock(processCodeblockSave((int) $prefix));
		if (isset($_POST[$prefix . 'owner']))
			$album->setOwner(sanitize($_POST[$prefix . 'owner']));

		$custom = process_language_string_save($prefix . 'album_custom_data', 1);
		$album->setCustomData(zp_apply_filter('save_album_custom_data', $custom, $prefix));
		zp_apply_filter('save_album_utilities_data', $album, $prefix);
		$album->save();

		// Move/Copy/Rename the album after saving.
		$movecopyrename_action = '';
		if (isset($_POST['a-' . $prefix . 'MoveCopyRename'])) {
			$movecopyrename_action = sanitize($_POST['a-' . $prefix . 'MoveCopyRename'], 3);
		}

		if ($movecopyrename_action == 'delete') {
			$dest = dirname($album->name);
			if ($album->remove()) {
				if ($dest == '/' || $dest == '.')
					$dest = '';
				$redirectto = $dest;
			} else {
				$notify = "&mcrerr=7";
			}
		}
		if ($movecopyrename_action == 'move') {
			$dest = sanitize_path($_POST['a' . $prefix . '-albumselect']);
			// Append the album name.
			$dest = ($dest ? $dest . '/' : '') . (strpos($album->name, '/') === FALSE ? $album->name : basename($album->name));
			if ($dest && $dest != $album->name) {
				if ($album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($dest, -4) != '.alb') {
						$dest .= '.alb';
					}
				}
				if ($e = $album->move($dest)) {
					$notify = "&mcrerr=" . $e;
				} else {
					$redirectto = $dest;
				}
			} else {
				// Cannot move album to same album.
				$notify = "&mcrerr=3";
			}
		} else if ($movecopyrename_action == 'copy') {
			$dest = sanitize_path($_POST['a' . $prefix . '-albumselect']);
			if ($dest && $dest != $album->name) {
				if ($e = $album->copy($dest)) {
					$notify = "&mcrerr=" . $e;
				}
			} else {
				// Cannot copy album to existing album.
				// Or, copy with rename?
				$notify = '&mcrerr=3';
			}
		} else if ($movecopyrename_action == 'rename') {
			$renameto = sanitize_path($_POST['a' . $prefix . '-renameto']);
			$renameto = str_replace(array('/', '\\'), '', $renameto);
			if (dirname($album->name) != '.') {
				$renameto = dirname($album->name) . '/' . $renameto;
			}
			if ($renameto != $album->name) {
				if ($album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($renameto, -4) != '.alb') {
						$renameto .= '.alb';
					}
				}
				if ($e = $album->rename($renameto)) {
					$notify = "&mcrerr=" . $e;
				} else {
					$redirectto = $renameto;
				}
			} else {
				$notify = "&mcrerr=3";
			}
		}
		return $notify;
	}

	function adminPageNav($pagenum, $totalpages, $adminpage, $parms, $tab = '') {
		if (empty($parms)) {
			$url = '?';
		} else {
			$url = $parms . '&amp;';
		}
		echo '<ul class="pagelist"><li class="prev">';
		if ($pagenum > 1) {
			echo '<a href="' . $url . 'subpage=' . ($p = $pagenum - 1) . $tab . '" title="' . sprintf(gettext('page %u'), $p) . '">' . '&laquo; ' . gettext("Previous page") . '</a>';
		} else {
			echo '<span class="disabledlink">&laquo; ' . gettext("Previous page") . '</span>';
		}
		echo "</li>";
		$start = max(1, $pagenum - 7);
		$total = min($start + 15, $totalpages + 1);
		if ($start != 1) {
			echo "\n <li><a href=" . $url . 'subpage=' . ($p = max($start - 8, 1)) . $tab . ' title="' . sprintf(gettext('page %u'), $p) . '">. . .</a></li>';
		}
		for ($i = $start; $i < $total; $i++) {
			if ($i == $pagenum) {
				echo "<li class=\"current\">" . $i . '</li>';
			} else {
				echo '<li><a href="' . $url . 'subpage=' . $i . $tab . '" title="' . sprintf(gettext('page %u'), $i) . '">' . $i . '</a></li>';
			}
		}
		if ($i < $totalpages) {
			echo "\n <li><a href=" . $url . 'subpage=' . ($p = min($pagenum + 22, $totalpages + 1)) . $tab . ' title="' . sprintf(gettext('page %u'), $p) . '">. . .</a></li>';
		}
		echo "<li class=\"next\">";
		if ($pagenum < $totalpages) {
			echo '<a href="' . $url . 'subpage=' . ($p = $pagenum + 1) . $tab . '" title="' . sprintf(gettext('page %u'), $p) . '">' . gettext("Next page") . ' &raquo;' . '</a>';
		} else {
			echo '<span class="disabledlink">' . gettext("Next page") . ' &raquo;</span>';
		}
		echo '</li></ul>';
	}

	$_zp_current_locale = NULL;

	/**
	 * Generates an editable list of language strings
	 *
	 * @param string $dbstring either a serialized languag string array or a single string
	 * @param string $name the prefix for the label, id, and name tags
	 * @param bool $textbox set to true for a textbox rather than a text field
	 * @param string $locale optional locale of the translation desired
	 * @param string $edit optional class
	 * @param int $wide column size. true or false for the standard or short sizes. Or pass a column size
	 * @param string $ulclass set to the class for the UL element
	 * @param int $rows set to the number of rows to show.
	 */
	function print_language_string_list($dbstring, $name, $textbox = false, $locale = NULL, $edit = '', $wide = TEXT_INPUT_SIZE, $ulclass = 'language_string_list', $rows = 6) {
		global $_zp_active_languages, $_zp_current_locale;
		$dbstring = zpFunctions::unTagURLs($dbstring);
		if (!empty($edit))
			$edit = ' class="' . $edit . '"';
		if (is_null($locale)) {
			if (is_null($_zp_current_locale)) {
				$_zp_current_locale = getUserLocale();
			}
			$locale = $_zp_current_locale;
		}
		$strings = getSerializedArray($dbstring);
		if (count($strings) == 1) {
			$lang = array_flip($strings);
			if (!array_shift($lang)) {
				$strings = array($locale => $dbstring);
			}
		}
		$emptylang = generateLanguageList();

		if (getOption('multi_lingual') && !empty($emptylang)) {
			if ($textbox) {
				if (strpos($wide, '%') === false) {
					$width = ' cols="' . $wide . '"';
				} else {
					$width = ' style="width:' . ((int) $wide - 1) . '%;"';
				}
			} else {
				if (strpos($wide, '%') === false) {
					$width = ' size="' . $wide . '"';
				} else {
					$width = ' style="width:' . ((int) $wide - 2) . '%;"';
				}
			}
			$emptylang = array_flip($emptylang);
			unset($emptylang['']);
			if ($textbox) {
				$class = 'box';
			} else {
				$class = '';
			}
			echo '<ul class="' . $ulclass . $class . '"' . ">\n";
			$empty = true;

			foreach ($emptylang as $key => $lang) {
				if (isset($strings[$key])) {
					$string = $strings[$key];
					if (!empty($string)) {
						unset($emptylang[$key]);
						$empty = false;
						?>
						<li>
							<label for="<?php echo $name . '_' . $key; ?>"><?php echo $lang; ?></label>
							<?php
							if ($textbox) {
								echo "\n" . '<textarea name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($string) . '</textarea>';
							} else {
								echo '<br /><input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="text" value="' . html_encode($string) . '"' . $width . ' />';
							}
							?>
						</li>
						<?php
					}
				}
			}
			if ($empty && isset($emptylang[$locale])) {
				$element = @$emptylang[$locale];
				unset($emptylang[$locale]);
				$emptylang = array_merge(array($locale => $element), $emptylang);
			}
			foreach ($emptylang as $key => $lang) {
				?>
				<li>
					<label for="<?php echo $name . '_' . $key; ?>"><?php echo $lang; ?></label>
					<?php
					if ($textbox) {
						echo "\n" . '<textarea name="' . $name . '_' . $key . '"' . $edit . $width . '	rows="' . $rows . '"></textarea>';
					} else {
						echo '<br /><input id="' . $name . '_' . $key . '" name="' . $name . '_' . $key . '"' . $edit . ' type="text" value=""' . $width . ' />';
					}
					?>
				</li>
				<?php
			}
			echo "</ul>\n";
		} else {
			if ($textbox) {
				if (strpos($wide, '%') === false) {
					$width = ' cols="' . $wide . '"';
				} else {
					$width = ' style="width:' . $wide . ';"';
				}
			} else {
				if (strpos($wide, '%') === false) {
					$width = ' size="' . $wide . '"';
				} else {
					$width = ' style="width:' . $wide . ';"';
				}
			}
			if (empty($locale))
				$locale = 'en_US';
			if (isset($strings[$locale])) {
				$dbstring = $strings[$locale];
			} else {
				$dbstring = array_shift($strings);
			}
			if ($textbox) {
				echo '<textarea name="' . $name . '_' . $locale . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($dbstring) . '</textarea>';
			} else {
				echo '<input name="' . $name . '_' . $locale . '"' . $edit . ' type="text" value="' . html_encode($dbstring) . '"' . $width . ' />';
			}
		}
	}

	/**
	 * process the post of a language string form
	 *
	 * @param string $name the prefix for the label, id, and name tags
	 * @param $sanitize_level the type of sanitization required
	 * @return string
	 */
	function process_language_string_save($name, $sanitize_level = 3) {
		$languages = generateLanguageList();
		$l = strlen($name) + 1;
		$strings = array();
		foreach ($_POST as $key => $value) {
			if ($value && preg_match('/^' . $name . '_[a-z]{2}_[A-Z]{2}$/', $key)) {
				$key = substr($key, $l);
				if (in_array($key, $languages)) {
					$strings[$key] = sanitize($value, $sanitize_level);
				}
			}
		}
		switch (count($strings)) {
			case 0:
				if (isset($_POST[$name])) {
					return sanitize($_POST[$name], $sanitize_level);
				} else {
					return '';
				}
			default:
				return serialize($strings);
		}
	}

	/**
	 * Returns the desired tagsort order (0 for alphabetic, 1 for most used)
	 *
	 * @return int
	 */
	function getTagOrder() {
		if (isset($_REQUEST['tagsort'])) {
			$tagsort = sanitize($_REQUEST['tagsort']);
			setOption('tagsort', (int) ($tagsort && true));
		} else {
			$tagsort = getOption('tagsort');
		}
		return $tagsort;
	}

	/**
	 * Unzips an image archive
	 *
	 * @param file $file the archive
	 * @param string $dir where the images go
	 */
	function unzip($file, $dir) { //check if zziplib is installed
		if (function_exists('zip_open')) {
			$zip = zip_open($file);
			if ($zip) {
				while ($zip_entry = zip_read($zip)) { // Skip non-images in the zip file.
					$fname = zip_entry_name($zip_entry);
					$seoname = internalToFilesystem(seoFriendly($fname));
					if (is_valid_image($seoname) || is_valid_other_type($seoname)) {
						if (zip_entry_open($zip, $zip_entry, "r")) {
							$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							$path_file = str_replace("/", DIRECTORY_SEPARATOR, $dir . '/' . $seoname);
							$fp = fopen($path_file, "w");
							fwrite($fp, $buf);
							fclose($fp);
							clearstatcache();
							zip_entry_close($zip_entry);
							$albumname = substr($dir, strlen(ALBUM_FOLDER_SERVERPATH));
							$album = newAlbum($albumname);
							$image = newImage($album, $seoname);
							if ($fname != $seoname) {
								$image->setTitle($fname);
								$image->save();
							}
						}
					}
				}
				zip_close($zip);
			}
		} else {
			require_once(dirname(__FILE__) . '/lib-pclzip.php');
			$zip = new PclZip($file);
			if ($zip->extract(PCLZIP_OPT_PATH, $dir, PCLZIP_OPT_REMOVE_ALL_PATH) == 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks for a zip file
	 *
	 * @param string $filename name of the file
	 * @return bool
	 */
	function is_zip($filename) {
		$ext = getSuffix($filename);
		return ($ext == "zip");
	}

	/**
	 * Takes a comment and makes the body of an email.
	 *
	 * @param string $str comment
	 * @param string $name author
	 * @param string $albumtitle album
	 * @param string $imagetitle image
	 * @return string
	 */
	function commentReply($str, $name, $albumtitle, $imagetitle) {
		$str = "$name commented on $imagetitle in the album $albumtitle: %0D%0A%0D%0A" . implode('%0D%0A', explode('\n', wordwrap(strip_tags($str), 75, '\n')));
		return $str;
	}

	/**
	 * Extracts and returns a 'statement' from a PHP script so that it may be 'evaled'
	 *
	 * @param string $target the assignment variable to match on
	 * @param string $str the PHP script
	 * @return string
	 */
	function isolate($target, $str) {
		if (preg_match('|' . preg_quote($target) . '\s*?=(.+?);[ \f\v\t]*[\n\r]|s', $str, $matches)) {
			return $matches[0];
		}
		return false;
	}

	/**
	 * Return an array of files from a directory and sub directories
	 *
	 * This is a non recursive function that digs through a directory. More info here:
	 * @link http://planetozh.com/blog/2005/12/php-non-recursive-function-through-directories/
	 *
	 * @param string $dir directory
	 * @return array
	 * @author Ozh
	 * @since 1.3
	 */
	function listDirectoryFiles($dir) {
		$file_list = array();
		$stack[] = $dir;
		while ($stack) {
			$current_dir = array_pop($stack);
			if ($dh = @opendir($current_dir)) {
				while (($file = @readdir($dh)) !== false) {
					if ($file !== '.' AND $file !== '..') {
						$current_file = "{$current_dir}/{$file}";
						if (is_file($current_file) && is_readable($current_file)) {
							$file_list[] = "{$current_dir}/{$file}";
						} elseif (is_dir($current_file)) {
							$stack[] = $current_file;
						}
					}
				}
			}
		}
		return $file_list;
	}

	/**
	 * Check if a theme is editable (ie not a bundled theme)
	 *
	 * @param $theme theme to check
	 * @return bool
	 * @since 1.3
	 */
	function themeIsEditable($theme) {
		if (function_exists('readlink')) {
			$link = @readlink(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme);
		} else {
			$link = '';
		}
		if (empty($link) || str_replace('\\', '/', $link) == SERVERPATH . '/' . THEMEFOLDER . '/' . $theme) {
			$zplist = unserialize(getOption('Zenphoto_theme_list'));
			return (!in_array($theme, $zplist));
		} else {
			return false;
		}
	}

	function zenPhotoTheme($theme) {
		$zplist = unserialize(getOption('Zenphoto_theme_list'));
		return (in_array($theme, $zplist));
	}

	/**
	 * Copy a theme directory to create a new custom theme
	 *
	 * @param $source source directory
	 * @param $target target directory
	 * @return bool|string either true or an error message
	 * @author Ozh
	 * @since 1.3
	 */
	function copyThemeDirectory($source, $target, $newname) {
		global $_zp_current_admin_obj;
		$message = true;
		$source = SERVERPATH . '/themes/' . internalToFilesystem($source);
		$target = SERVERPATH . '/themes/' . internalToFilesystem($target);

		// If the target theme already exists, nothing to do.
		if (is_dir($target)) {
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Directory "%s" already exists!'), basename($target));
		}

		// If source dir is missing, exit too
		if (!is_dir($source)) {
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Cannot find theme directory "%s" to copy!'), basename($source));
		}

		// We must be able to write to the themes dir.
		if (!is_writable(dirname($target))) {
			return gettext('Cannot create new theme.') . ' ' . gettext('The <tt>/themes</tt> directory is not writable!');
		}

		// We must be able to create the directory
		if (!mkdir($target, FOLDER_MOD)) {
			return gettext('Cannot create new theme.') . ' ' . gettext('Could not create directory for the new theme');
		}
		@chmod($target, FOLDER_MOD);

		// Get a list of files to copy: get all files from the directory, remove those containing '/.svn/'
		$source_files = array_filter(listDirectoryFiles($source), create_function('$str', 'return strpos($str, "/.svn/") === false;'));

		// Determine nested (sub)directories structure to create: go through each file, explode path on "/"
		// and collect every unique directory
		$dirs_to_create = array();
		foreach ($source_files as $path) {
			$path = explode('/', dirname(str_replace($source . '/', '', $path)));
			$dirs = '';
			foreach ($path as $subdir) {
				if ($subdir == '.svn' or $subdir == '.') {
					continue 2;
				}
				$dirs = "$dirs/$subdir";
				$dirs_to_create[$dirs] = $dirs;
			}
		}

		// Create new directory structure
		foreach ($dirs_to_create as $dir) {
			mkdir("$target/$dir", FOLDER_MOD);
			@chmod("$target/$dir", FOLDER_MOD);
		}

		// Now copy every file
		foreach ($source_files as $file) {
			$newfile = str_replace($source, $target, $file);
			if (!copy("$file", "$newfile"))
				return sprintf(gettext("An error occurred while copying files. Please delete manually the new theme directory '%s' and retry or copy files manually."), basename($target));
			@chmod("$newfile", FOLDER_MOD);
		}

		// Rewrite the theme header.
		if (file_exists($target . '/theme_description.php')) {
			$theme_description = array();
			require($target . '/theme_description.php');
			$theme_description['desc'] = sprintf(gettext('Your theme, based on theme %s'), $theme_description['name']);
		} else {
			$theme_description['desc'] = gettext('Your theme');
		}
		$theme_description['name'] = $newname;
		$theme_description['author'] = $_zp_current_admin_obj->getUser();
		$theme_description['version'] = '1.0';
		$theme_description['date'] = date('Y-m-d H:m:s', time());

		$description = sprintf('<' . '?php
				// Zenphoto theme definition file
				$theme_description["name"] = "%s";
				$theme_description["author"] = "%s";
				$theme_description["version"] = "%s";
				$theme_description["date"] = "%s";
				$theme_description["desc"] = "%s";
				?' . '>', html_encode($theme_description['name']), html_encode($theme_description['author']), html_encode($theme_description['version']), html_encode($theme_description['date']), html_encode($theme_description['desc']));

		$f = fopen($target . '/theme_description.php', 'w');
		if ($f !== FALSE) {
			@fwrite($f, $description);
			fclose($f);
			$message = gettext('New custom theme created successfully!');
		} else {
			$message = gettext('New custom theme created, but its description could not be updated');
		}

		// Make a slightly custom theme image
		if (file_exists("$target/theme.png"))
			$themeimage = "$target/theme.png";
		else if (file_exists("$target/theme.gif"))
			$themeimage = "$target/theme.gif";
		else if (file_exists("$target/theme.jpg"))
			$themeimage = "$target/theme.jpg";
		else
			$themeimage = false;
		if ($themeimage) {
			if ($im = zp_imageGet($themeimage)) {
				$x = zp_imageWidth($im) / 2 - 45;
				$y = zp_imageHeight($im) / 2 - 10;
				$text = "CUSTOM COPY";
				$font = zp_imageLoadFont();
				$ink = zp_colorAllocate($im, 0x0ff, 0x0ff, 0x0ff);
				// create a blueish overlay
				$overlay = zp_createImage(zp_imageWidth($im), zp_imageHeight($im));
				$back = zp_colorAllocate($overlay, 0x060, 0x060, 0x090);
				zp_imageFill($overlay, 0, 0, $back);
				// Merge theme image and overlay
				zp_imageMerge($im, $overlay, 0, 0, 0, 0, zp_imageWidth($im), zp_imageHeight($im), 45);
				// Add text
				zp_writeString($im, $font, $x - 1, $y - 1, $text, $ink);
				zp_writeString($im, $font, $x + 1, $y + 1, $text, $ink);
				zp_writeString($im, $font, $x, $y, $text, $ink);
				// Save new theme image
				zp_imageOutput($im, 'png', $themeimage);
			}
		}

		return $message;
	}

	function deleteThemeDirectory($source) {
		if (is_dir($source)) {
			$result = true;
			$handle = opendir($source);
			while (false !== ($filename = readdir($handle))) {
				$fullname = $source . '/' . $filename;
				if (is_dir($fullname)) {
					if (($filename != '.') && ($filename != '..')) {
						$result = $result && deleteThemeDirectory($fullname);
					}
				} else {
					if (file_exists($fullname)) {
						@chmod($fullname, 0666);
						$result = $result && unlink($fullname);
					}
				}
			}
			closedir($handle);
			$result = $result && rmdir($source);
			return $result;
		}
		return false;
	}

	/**
	 * Return URL of current admin page
	 *
	 * @return string current URL
	 * @author Ozh
	 * @since 1.3
	 *
	 * @param string $source the script file
	 */
	function currentRelativeURL() {
		$source = str_replace(SERVERPATH, WEBPATH, str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
		$q = '';
		if (!empty($_GET)) {
			foreach ($_GET as $parm => $value) {
				if ($value) {
					$q .= $parm . '=' . $value . '&';
				} else {
					$q .= $parm . '&';
				}
			}
			$q = '?' . substr($q, 0, -1);
		}
		return pathurlencode($source) . $q;
	}

	/**
	 * Returns an array of the names of the parents of the current album.
	 *
	 * @param object $album optional album object to use inseted of the current album
	 * @return array
	 */
	function getParentAlbumsAdmin($album) {
		$parents = array();
		while (!is_null($album = $album->getParent())) {
			array_unshift($parents, $album);
		}
		return $parents;
	}

	function getAlbumBreadcrumbAdmin($album) {
		$link = '';
		$parents = getParentAlbumsAdmin($album);
		foreach ($parents as $parent) {
			$link .= "<a href='" . WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . html_encode(pathurlencode($parent->name)) . "'>" . removeParentAlbumNames($parent) . "</a>/";
		}
		return $link;
	}

	/**
	 * prints the album breadcrumb for the album edit page
	 *
	 * @param object $album Object of the album
	 */
	function printAlbumBreadcrumbAdmin($album) {
		echo getAlbumBreadcrumbAdmin($album);
	}

	/**
	 * Removes the parent album name so that we can print a album breadcrumb with them
	 *
	 * @param object $album Object of the album
	 * @return string
	 */
	function removeParentAlbumNames($album) {
		$slash = stristr($album->name, "/");
		if ($slash) {
			$array = array_reverse(explode("/", $album->name));
			$albumname = $array[0];
		} else {
			$albumname = $album->name;
		}
		return $albumname;
	}

	/**
	 * Outputs the rights checkbox table for admin
	 *
	 * @param $id int record id for the save
	 * @param string $background background color
	 * @param string $alterrights are the items changable
	 * @param bit $rights rights of the admin
	 */
	function printAdminRightsTable($id, $background, $alterrights, $rights) {
		$rightslist = sortMultiArray(Zenphoto_Authority::getRights(), array('set', 'value'));
		?>
		<div class="box-rights">
			<strong><?php echo gettext("Rights:"); ?></strong>
			<?php
			$element = 3;
			$activeset = false;
			foreach ($rightslist as $rightselement => $right) {
				if ($right['display']) {
					if (($right['set'] != gettext('Pages') && $right['set'] != gettext('News')) || extensionEnabled('zenpage')) {
						if ($activeset != $right['set']) {
							if ($activeset) {
								?>
							</fieldset>
							<?php
						}
						$activeset = $right['set'];
						?>
						<fieldset><legend><?php echo $activeset; ?></legend>
							<?php
						}
						?>
						<label title="<?php echo html_encode(get_language_string($right['hint'])); ?>">
							<input type="checkbox" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" class="user-<?php echo $id; ?>"
										 value="<?php echo $right['value']; ?>"<?php if ($rights & $right['value']) echo ' checked="checked"'; echo $alterrights; ?> /> <?php echo $right['name']; ?>
						</label>
						<?php
					} else {
						?>
						<input type="hidden" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" value="<?php echo $right['value']; ?>" />
						<?php
					}
				}
			}
			?>
		</fieldset>
	</div>
	<?php
}

/**
 * Creates the managed album table for Admin
 *
 * @param string $type the kind of list
 * @param array $objlist list of objects
 * @param string $alterrights are the items changable
 * @param object $userobj the user
 * @param int $prefix the admin row
 * @param string $kind user, group, or template
 * @param array $flat items to be flagged with an asterix
 */
function printManagedObjects($type, $objlist, $alterrights, $userobj, $prefix_id, $kind, $flag) {
	$rest = $extra = $extra2 = array();
	$rights = $userobj->getRights();
	$legend = '';
	switch ($type) {
		case 'albums':
			if ($rights & (MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$alterrights = ' disabled="disabled"';
			} else {
				$full = $userobj->getObjects();
				$cv = $extra = array();
				$icon_edit_album = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/options.png" class="icon-position-top3" alt="" title="' . gettext('edit rights') . '" />';
				$icon_view_image = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/action.png" class="icon-position-top3" alt="" title="' . gettext('view unpublished items') . '" />';
				$icon_upload = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/arrow_up.png" class="icon-position-top3"  alt="" title="' . gettext('upload rights') . '"/>';
				$icon_upload_disabled = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/arrow_up.png" class="icon-position-top3"  alt="" title="' . gettext('the album is dynamic') . '"/>';
				if (!empty($flag)) {
					$legend .= '* ' . gettext('Primary album') . ' ';
				}
				$legend .= $icon_edit_album . ' ' . gettext('edit') . ' ';
				if ($rights & UPLOAD_RIGHTS)
					$legend .= $icon_upload . ' ' . gettext('upload') . ' ';
				if (!($rights & VIEW_UNPUBLISHED_RIGHTS))
					$legend .= $icon_view_image . ' ' . gettext('view unpublished') . ' ';
				foreach ($full as $item) {
					if ($item['type'] == 'album') {
						if (in_array($item['data'], $flag)) {
							$note = '*';
						} else {
							$note = '';
						}
						$cv[$item['name'] . $note] = $item['data'];
						$extra[$item['data']][] = array('name' => 'name', 'value' => $item['name'], 'display' => '', 'checked' => 0);
						$extra[$item['data']][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit_album, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_EDIT);
						if (($rights & UPLOAD_RIGHTS)) {
							if (hasDynamicAlbumSuffix($item['data'])) {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
							} else {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_UPLOAD);
							}
						}
						if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
							$extra[$item['data']][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view_image, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_VIEW);
						}
					}
				}
				$rest = array_diff($objlist, $cv);
				foreach ($rest as $unmanaged) {
					$extra2[$unmanaged][] = array('name' => 'name', 'value' => $unmanaged, 'display' => '', 'checked' => 0);
					$extra2[$unmanaged][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit_album, 'checked' => 1);
					if (($rights & UPLOAD_RIGHTS)) {
						if (hasDynamicAlbumSuffix($unmanaged)) {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
						} else {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => 1);
						}
					}
					if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
						$extra2[$unmanaged][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view_image, 'checked' => 1);
					}
				}
			}
			$text = gettext("Managed albums:");
			$simplename = $objectname = gettext('Albums');
			$prefix = 'managed_albums_list_' . $prefix_id . '_';
			break;
		case 'news':
			if ($rights & (MANAGE_ALL_NEWS_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$rest = array();
				$alterrights = ' disabled="disabled"';
			} else {
				$cv = $userobj->getObjects('news');
				$rest = array_diff($objlist, $cv);
			}
			$text = gettext("Managed news categories:");
			$simplename = gettext('News');
			$objectname = gettext('News categories');
			$prefix = 'managed_news_list_' . $prefix_id . '_';
			break;
		case 'pages':
			if ($rights & (MANAGE_ALL_PAGES_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$rest = array();
				$alterrights = ' disabled="disabled"';
			} else {
				$cv = $userobj->getObjects('pages');
				$rest = array_diff($objlist, $cv);
			}
			$text = gettext("Managed pages:");
			$simplename = $objectname = gettext('Pages');
			$prefix = 'managed_pages_list_' . $prefix_id . '_';
			break;
	}
	if (empty($alterrights)) {
		$hint = sprintf(gettext('Select one or more %1$s for the %2$s to manage.'), $simplename, $kind) . ' ';
		if ($kind == gettext('user')) {
			$hint .= sprintf(gettext('Users with "Admin" or "Manage all %1$s" rights can manage all %2$s. All others may manage only those that are selected.'), $type, $objectname);
		}
	} else {
		$hint = sprintf(gettext('You may manage these %s subject to the above rights.'), $simplename);
	}
	if (count($cv) > 0) {
		$itemcount = ' (' . count($cv) . ')';
	} else {
		$itemcount = '';
	}
	?>

	<div class="box-albums-unpadded">
		<h2 class="h2_bordered_albums">
			<a href="javascript:toggle('<?php echo $prefix ?>');" title="<?php echo html_encode($hint); ?>" ><?php echo $text . $itemcount; ?></a>
		</h2>
		<div id="<?php echo $prefix ?>" style="display:none;">
			<ul class="albumchecklist">
				<?php
				generateUnorderedListFromArray($cv, $cv, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra);
				generateUnorderedListFromArray(array(), $rest, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra2);
				?>
			</ul>
			<span class="floatright"><?php echo $legend; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<br class="clearall" />
		</div>
	</div>
	<?php
}

/**
 * processes the post of administrator rights
 *
 * @param int $i the admin row number
 * @return bit
 */
function processRights($i) {
	if (isset($_POST[$i . '-confirmed'])) {
		$rights = NO_RIGHTS;
	} else {
		$rights = 0;
	}
	foreach (Zenphoto_Authority::getRights() as $name => $right) {
		if (isset($_POST[$i . '-' . $name])) {
			$rights = $rights | $right['value'] | NO_RIGHTS;
		}
	}
	if ($rights & MANAGE_ALL_ALBUM_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_ALBUMS_RIGHTS | ALBUM_RIGHTS;
	}
	if ($rights & MANAGE_ALL_NEWS_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_NEWS_RIGHTS | ZENPAGE_NEWS_RIGHTS;
	}
	if ($rights & MANAGE_ALL_PAGES_RIGHTS) { // these are lock-step linked!
		$rights = $rights | ALL_PAGES_RIGHTS | ZENPAGE_PAGES_RIGHTS;
	}
	return $rights;
}

function processManagedObjects($i, &$rights) {
	$objects = array();
	$albums = array();
	$pages = array();
	$news = array();
	$l_a = strlen($prefix_a = 'managed_albums_list_' . $i . '_');
	$l_p = strlen($prefix_p = 'managed_pages_list_' . $i . '_');
	$l_n = strlen($prefix_n = 'managed_news_list_' . $i . '_');
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if (substr($key, 0, $l_a) == $prefix_a) {
			$key = substr($key, $l_a);
			if (preg_match('/(.*)(_edit|_view|_upload|_name)$/', $key, $matches)) {
				$key = $matches[1];
				if (array_key_exists($key, $albums)) {
					switch ($matches[2]) {
						case '_edit':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT;
							break;
						case '_upload':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_UPLOAD;
							break;
						case '_view':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_VIEW;
							break;
						case '_name':
							$albums[$key]['name'] = $value;
							break;
					}
				}
			} else if ($value) {
				$albums[$key] = array('data' => $key, 'name' => '', 'type' => 'album', 'edit' => 32767 & ~(MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW));
			}
		}
		if (substr($key, 0, $l_p) == $prefix_p) {
			if ($value) {
				$pages[] = array('data' => substr($key, $l_p), 'type' => 'pages');
			}
		}
		if (substr($key, 0, $l_n) == $prefix_n) {
			if ($value) {
				$news[] = array('data' => substr($key, $l_n), 'type' => 'news');
			}
		}
	}
	foreach ($albums as $key => $analbum) {
		unset($albums[$key]);
		$albums[] = $analbum;
	}
	if (empty($albums)) {
		if (!($rights & MANAGE_ALL_ALBUM_RIGHTS)) {
			$rights = $rights & ~ALBUM_RIGHTS;
		}
	} else {
		$rights = $rights | ALBUM_RIGHTS;
		if ($rights & (MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) {
			$albums = array();
		}
	}
	if (empty($pages)) {
		if (!($rights & MANAGE_ALL_PAGES_RIGHTS)) {
			$rights = $rights & ~ZENPAGE_PAGES_RIGHTS;
		}
	} else {
		$rights = $rights | ZENPAGE_PAGES_RIGHTS;
		if ($rights & (MANAGE_ALL_PAGES_RIGHTS | ADMIN_RIGHTS)) {
			$pages = array();
		}
	}
	if (empty($news)) {
		if (!($rights & MANAGE_ALL_NEWS_RIGHTS)) {
			$rights = $rights & ~ZENPAGE_NEWS_RIGHTS;
		}
	} else {
		$rights = $rights | ZENPAGE_NEWS_RIGHTS;
		if ($rights & (MANAGE_ALL_NEWS_RIGHTS | ADMIN_RIGHTS)) {
			$news = array();
		}
	}
	$objects = array_merge($albums, $pages, $news);
	return $objects;
}

/**
 * Returns the value of a checkbox form item
 *
 * @param string $id the $_REQUEST index
 * @return int (0 or 1)
 */
function getCheckboxState($id) {
	if (isset($_REQUEST[$id]))
		return 1;
	else
		return 0;
}

/**
 * Returns an array of "standard" theme scripts. This list is
 * normally used to exclude these scripts form various option seletors.
 *
 * @return array
 */
function standardScripts() {
	$standardlist = array('themeoptions', 'password', 'theme_description', '404', 'slideshow', 'search', 'image', 'index', 'album', 'customfunctions', 'functions');
	if (extensionEnabled('zenpage'))
		$standardlist = array_merge($standardlist, array('news', 'pages'));
	return $standardlist;
}

/**
 * Returns a merged list of available watermarks
 *
 * @return array
 */
function getWatermarks() {
	$list = array();
	$curdir = getcwd();
	chdir($basepath = SERVERPATH . "/" . ZENFOLDER . '/watermarks/');
	$filelist = safe_glob('*.png');
	foreach ($filelist as $file) {
		$list[filesystemToInternal(substr(basename($file), 0, -4))] = $basepath . $file;
	}
	$basepath = SERVERPATH . "/" . USER_PLUGIN_FOLDER . '/watermarks/';
	if (is_dir($basepath)) {
		chdir($basepath);
		$filelist = safe_glob('*.png');
		foreach ($filelist as $file) {
			$list[filesystemToInternal(substr(basename($file), 0, -4))] = $basepath . $file;
		}
	}
	chdir($curdir);
	$watermarks = array_keys($list);
	return $watermarks;
}

/**
 * Processes the serialized array from tree sort.
 * Returns an array in the form [$id=>array(sort orders), $id=>array(sort orders),...]
 *
 * @param $orderstr the serialzied tree sort order
 * @return array
 */
function processOrder($orderstr) {
	$order = $result = array();
	parse_str($orderstr, $order);
	$order = array_shift($order);

	$parents = $curorder = array();
	$curowner = '';
	foreach ($order as $id => $parent) { // get the root elements
		if ($parent != $curowner) {
			if (($key = array_search($parent, $parents)) === false) { //	a child
				array_push($parents, $parent);
				array_push($curorder, -1);
			} else { //	roll back to parent
				$parents = array_slice($parents, 0, $key + 1);
				$curorder = array_slice($curorder, 0, $key + 1);
			}
		}
		$l = count($curorder) - 1;
		$curorder[$l] = sprintf('%03u', $curorder[$l] + 1);
		$result[$id] = $curorder;
	}
	return $result;
}

/**
 * POST handler for album tree sorts
 *
 * @param int $parentid id of owning album
 *
 */
function postAlbumSort($parentid) {
	if (isset($_POST['order']) && !empty($_POST['order'])) {
		$order = processOrder(sanitize($_POST['order']));
		$sortToID = array();
		foreach ($order as $id => $orderlist) {
			$id = str_replace('id_', '', $id);
			$sortToID[implode('-', $orderlist)] = $id;
		}
		foreach ($order as $item => $orderlist) {
			$item = str_replace('id_', '', $item);
			$currentalbum = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $item);
			$sortorder = array_pop($orderlist);
			if (count($orderlist) > 0) {
				$newparent = $sortToID[implode('-', $orderlist)];
			} else {
				$newparent = $parentid;
			}
			if ($newparent == $currentalbum['parentid']) {
				$sql = 'UPDATE ' . prefix('albums') . ' SET `sort_order`=' . db_quote($sortorder) . ' WHERE `id`=' . $item;
				query($sql);
			} else { // have to do a move
				$albumname = $currentalbum['folder'];
				$album = newAlbum($albumname);
				if (strpos($albumname, '/') !== false) {
					$albumname = basename($albumname);
				}
				if (is_null($newparent)) {
					$dest = $albumname;
				} else {
					$parent = query_single_row('SELECT * FROM ' . prefix('albums') . ' WHERE `id`=' . $newparent);
					if ($parent['dynamic']) {
						return "&mcrerr=5";
					} else {
						$dest = $parent['folder'] . '/' . $albumname;
					}
				}
				if ($e = $album->move($dest)) {
					return "&mcrerr=" . $e;
				} else {
					$album->setSortOrder($sortorder);
					$album->save();
				}
			}
		}
		return true;
	}
	return false;
}

/**
 * generates a nested list of albums for the album tab sorting
 * Returns an array of "albums" each element contains:
 * 								'name' which is the folder name
 * 								'album' which is an album object for the album
 * 								'sort_order' which is an array of the sort order set
 *
 * @param $subalbum root level album (NULL is the gallery)
 * @param $levels how far to nest
 * @param $level internal for keeping the sort order elements
 * @return array
 */
function getNestedAlbumList($subalbum, $levels, $level = array()) {
	global $_zp_gallery;
	$cur = count($level);
	$levels--; // make it 0 relative to sync with $cur
	if (is_null($subalbum)) {
		$albums = $_zp_gallery->getAlbums();
	} else {
		$albums = $subalbum->getAlbums();
	}
	$list = array();
	foreach ($albums as $analbum) {
		$albumobj = newAlbum($analbum);
		if (!is_null($subalbum) || $albumobj->isMyItem(ALBUM_RIGHTS)) {
			$level[$cur] = sprintf('%03u', $albumobj->getSortOrder());
			$list[] = array('name' => $analbum, 'sort_order' => $level);
			if ($cur < $levels && ($albumobj->getNumAlbums() > 0) && !$albumobj->isDynamic()) {
				$list = array_merge($list, getNestedAlbumList($albumobj, $levels + 1, $level));
			}
		}
	}
	return $list;
}

/**
 * Prints the sortable nested albums list
 * returns true if nesting levels exceede the database container
 *
 * @param array $pages The array containing all pages
 * @param bool $show_thumb set false to use thumb standin image.
 * @param object $owner the album object of the owner or NULL for the gallery
 *
 * @return bool
 */
function printNestedAlbumsList($albums, $show_thumb, $owner) {
	$indent = 1;
	$open = array(1 => 0);
	$rslt = false;
	foreach ($albums as $album) {
		$order = $album['sort_order'];
		$level = max(1, count($order));
		if ($toodeep = $level > 1 && $order[$level - 1] === '') {
			$rslt = true;
		}
		if ($level > $indent) {
			echo "\n" . str_pad("\t", $indent, "\t") . "<ul class=\"page-list\">\n";
			$indent++;
			$open[$indent] = 0;
		} else if ($level < $indent) {
			while ($indent > $level) {
				$open[$indent] --;
				$indent--;
				echo "</li>\n" . str_pad("\t", $indent, "\t") . "</ul>\n";
			}
		} else { // indent == level
			if ($open[$indent]) {
				echo str_pad("\t", $indent, "\t") . "</li>\n";
				$open[$indent] --;
			} else {
				echo "\n";
			}
		}
		if ($open[$indent]) {
			echo str_pad("\t", $indent, "\t") . "</li>\n";
			$open[$indent] --;
		}
		$albumobj = newAlbum($album['name']);
		if ($albumobj->isDynamic()) {
			$nonest = ' class="no-nest"';
		} else {
			$nonest = '';
		}
		echo str_pad("\t", $indent - 1, "\t") . "<li id=\"id_" . $albumobj->getID() . "\"$nonest >";
		printAlbumEditRow($albumobj, $show_thumb, $owner);
		$open[$indent] ++;
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent] --;
		$indent--;
		echo str_pad("\t", $indent, "\t") . "</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
	} else {
		echo "\n";
	}
	return $rslt;
}

/**
 * Prints the dropdown menu for the nesting level depth for the album sorting
 *
 */
function printEditDropdown($subtab, $nestinglevels, $nesting) {
	switch ($subtab) {
		case '':
			$link = '?selection=';
			break;
		case 'subalbuminfo':
			$link = '?page=edit&amp;album=' . html_encode($_GET['album']) . '&amp;tab=subalbuminfo&amp;selection=';
			break;
		case 'imageinfo':
			if (isset($_GET['tagsort'])) {
				$tagsort = '&tagsort=' . sanitize($_GET['tagsort']);
			} else {
				$tagsort = '';
			}
			$link = '?page=edit&amp;album=' . html_encode($_GET['album']) . '&amp;tab=imageinfo' . html_encode($tagsort) . '&amp;selection=';
			break;
	}
	?>
	<form name="AutoListBox2" style="float: right;" action="#" >
		<select name="ListBoxURL" size="1" onchange="gotoLink(this.form);">
			<?php
			foreach ($nestinglevels as $nestinglevel) {
				if ($nesting == $nestinglevel) {
					$selected = 'selected="selected"';
				} else {
					$selected = "";
				}
				echo '<option ' . $selected . ' value="admin-edit.php' . $link . $nestinglevel . '">';
				switch ($subtab) {
					case '':
					case 'subalbuminfo':
						printf(ngettext('Show %u album level', 'Show %u album levels', $nestinglevel), $nestinglevel);
						break;
					case 'imageinfo':
						printf(ngettext('%u image per page', '%u images per page', $nestinglevel), $nestinglevel);
						break;
				}
				echo '</option>';
			}
			?>
		</select>
		<script type="text/javascript" >
			// <!-- <![CDATA[
			function gotoLink(form) {
				var OptionIndex = form.ListBoxURL.selectedIndex;
				parent.location = form.ListBoxURL.options[OptionIndex].value;
			}
			// ]]> -->
		</script>
	</form>
	<?php
}

function processEditSelection($subtab) {
	global $subalbum_nesting, $album_nesting, $imagesTab_imageCount;
	if (isset($_GET['selection'])) {
		switch ($subtab) {
			case '':
				$album_nesting = max(1, sanitize_numeric($_GET['selection']));
				zp_setCookie('gallery_nesting', $album_nesting);
				break;
			case 'subalbuminfo':
				$subalbum_nesting = max(1, sanitize_numeric($_GET['selection']));
				zp_setCookie('subalbum_nesting', $subalbum_nesting);
				break;
			case 'imageinfo':
				$imagesTab_imageCount = max(ADMIN_IMAGES_STEP, sanitize_numeric($_GET['selection']));
				zp_setCookie('imagesTab_imageCount', $imagesTab_imageCount);
				break;
		}
	} else {
		switch ($subtab) {
			case '':
				$album_nesting = zp_getCookie('gallery_nesting');
				break;
			case 'subalbuminfo':
				$subalbum_nesting = zp_getCookie('subalbum_nesting');
				break;
			case 'imageinfo':
				$count = zp_getCookie('imagesTab_imageCount');
				if ($count)
					$imagesTab_imageCount = $count;
				break;
		}
	}
}

/**
 * Edit tab bulk actions drop-down
 * @param array $checkarray the list of actions
 * @param bool $checkAll set true to include check all box
 */
function printBulkActions($checkarray, $checkAll = false) {
	$tags = in_array('addtags', $checkarray) || in_array('alltags', $checkarray);
	$movecopy = in_array('moveimages', $checkarray) || in_array('copyimages', $checkarray);
	$categories = in_array('addcats', $checkarray) || in_array('clearcats', $checkarray);
	$changeowner = in_array('changeowner', $checkarray);
	if ($tags || $movecopy || $categories || $changeowner) {
		?>
		<script type="text/javascript">
			//<!-- <![CDATA[
			function checkFor(obj) {
				var sel = obj.options[obj.selectedIndex].value;
		<?php
		if ($tags) {
			?>
					if (sel == 'addtags' || sel == 'alltags') {
						$.colorbox({
							href: "#mass_tags_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($movecopy) {
			?>
					if (sel == 'moveimages' || sel == 'copyimages') {
						$.colorbox({
							href: "#mass_movecopy_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($categories) {
			?>
					if (sel == 'addcats') {
						$.colorbox({
							href: "#mass_cats_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		if ($changeowner) {
			?>
					if (sel == 'changeowner') {
						$.colorbox({
							href: "#mass_owner_data",
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
						});
					}
			<?php
		}
		?>
			}
			// ]]> -->
		</script>
		<?php
	}
	?>
	<span style="float:right">
		<select name="checkallaction" id="checkallaction" size="1" onchange="checkFor(this);" >
			<?php generateListFromArray(array('noaction'), $checkarray, false, true); ?>
		</select>
		<?php
		if ($checkAll) {
			?>
			<br />
			<?php
			echo gettext("Check All");
			?>
			<input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
			<?php
		}
		?>
	</span>
	<?php
	if ($tags) {
		?>
		<div id="mass_tags" style="display:none;">
			<div id="mass_tags_data">
				<?php
				tagSelector(NULL, 'mass_tags_', false, false);
				?>
			</div>
		</div>
		<?php
	}
	if ($categories) {
		?>
		<div id="mass_cats" style="display:none;">
			<ul id="mass_cats_data">
				<?php
				printNestedItemsList('cats-checkboxlist', '', 'all');
				?>
			</ul>
		</div>
		<?php
	}
	if ($changeowner) {
		?>
		<div id="mass_owner" style="display:none;">
			<ul id="mass_owner_data">
				<select id="massownermenu" name="massownerselect" onchange="">
					<?php
					echo admin_album_list(NULL);
					?>
				</select>
			</ul>
		</div>
		<?php
	}
	if ($movecopy) {
		global $mcr_albumlist, $album, $bglevels;
		?>
		<div id="mass_movecopy_copy" style="display:none;">
			<div id="mass_movecopy_data">
				<input type="hidden" name="massfolder" value="<?php echo $album->name; ?>" />
				<?php
				echo gettext('Destination');
				?>
				<select id="massalbumselectmenu" name="massalbumselect" onchange="">
					<?php
					foreach ($mcr_albumlist as $fullfolder => $albumtitle) {
						$singlefolder = $fullfolder;
						$saprefix = "";
						$salevel = 0;
						$selected = "";
						if ($album->name == $fullfolder) {
							$selected = " selected=\"selected\" ";
						}
						// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
						while (strstr($singlefolder, '/') !== false) {
							$singlefolder = substr(strstr($singlefolder, '/'), 1);
							$saprefix = "&nbsp; &nbsp;&nbsp;" . $saprefix;
							$salevel++;
						}
						echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: ' . $bglevels[$salevel] . ';"' : '')
						. "$selected>" . $saprefix . $singlefolder . "</option>\n";
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}

/**
 *
 * common redirector for bulk action handling return
 * @param string $action
 */
function bulkActionRedirect($action) {
	$uri = getRequestURI();
	if (strpos($uri, '?')) {
		$uri .= '&bulkaction=' . $action;
	} else {
		$uri .= '?bulkaction=' . $action;
	}
	header('Location: ' . $uri);
	exitZP();
}

/**
 * Process the bulk tags
 *
 * @return array
 */
function bulkTags() {
	$tags = array();
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if ($value && substr($key, 0, 10) == 'mass_tags_') {
			$tags[] = sanitize(substr($key, 10));
		}
	}
	return $tags;
}

/**
 * Processes the check box bulk actions for albums
 *
 */
function processAlbumBulkActions() {
	if (isset($_POST['ids'])) {
		$ids = sanitize($_POST['ids']);
		$action = sanitize($_POST['checkallaction']);
		$total = count($ids);
		if ($action != 'noaction' && $total > 0) {
			if ($action == 'addtags' || $action == 'alltags') {
				$tags = bulkTags();
			}
			if ($action == 'changeowner') {
				$newowner = sanitize($_POST['massownerselect']);
			}
			$n = 0;
			foreach ($ids as $albumname) {
				$n++;
				$albumobj = newAlbum($albumname);
				switch ($action) {
					case 'deleteall':
						$albumobj->remove();
						break;
					case 'showall':
						$albumobj->setShow(1);
						break;
					case 'hideall':
						$albumobj->setShow(0);
						break;
					case 'commentson':
						$albumobj->setCommentsAllowed(1);
						break;
					case 'commentsoff':
						$albumobj->setCommentsAllowed(0);
						break;
					case 'resethitcounter':
						$albumobj->set('hitcounter', 0);
						break;
					case 'addtags':
						$mytags = array_unique(array_merge($tags, $albumobj->getTags()));
						$albumobj->setTags($mytags);
						break;
					case 'cleartags':
						$albumobj->setTags(array());
						break;
					case 'alltags':
						$images = $albumobj->getImages();
						foreach ($images as $imagename) {
							$imageobj = newImage($albumobj, $imagename);
							$mytags = array_unique(array_merge($tags, $imageobj->getTags()));
							$imageobj->setTags($mytags);
							$imageobj->save();
						}
						break;
					case 'clearalltags':
						$images = $albumobj->getImages();
						foreach ($images as $imagename) {
							$imageobj = newImage($albumobj, $imagename);
							$imageobj->setTags(array());
							$imageobj->save();
						}
						break;
					case 'changeowner':
						$albumobj->setOwner($newowner);
						break;
					default:
						$action = call_user_func($action, $albumobj);
						break;
				}
				$albumobj->save();
			}
			return $action;
		}
	}
	return false;
}

/**
 * Handles Image bulk actions
 * @param $album
 */
function processImageBulkActions($album) {
	$action = sanitize($_POST['checkallaction']);
	$ids = sanitize($_POST['ids']);
	$total = count($ids);
	if ($action != 'noaction') {
		if ($total > 0) {
			if ($action == 'addtags') {
				$tags = bulkTags();
			}
			if ($action == 'moveimages' || $action == 'copyimages') {
				$dest = sanitize($_POST['massalbumselect']);
				$folder = sanitize($_POST['massfolder']);
				if (!$dest || $dest == $folder) {
					return "&mcrerr=2";
				}
			}
			if ($action == 'changeowner') {
				$newowner = sanitize($_POST['massownerselect']);
			}
			$n = 0;
			foreach ($ids as $filename) {
				$n++;
				$imageobj = newImage($album, $filename);
				switch ($action) {
					case 'deleteall':
						$imageobj->remove();
						break;
					case 'showall':
						$imageobj->set('show', 1);
						break;
					case 'hideall':
						$imageobj->set('show', 0);
						break;
					case 'commentson':
						$imageobj->set('commentson', 1);
						break;
					case 'commentsoff':
						$imageobj->set('commentson', 0);
						break;
					case 'resethitcounter':
						$imageobj->set('hitcounter', 0);
						break;
					case 'addtags':
						$mytags = array_unique(array_merge($tags, $imageobj->getTags()));
						$imageobj->setTags($mytags);
						break;
					case 'cleartags':
						$imageobj->setTags(array());
						break;
					case 'copyimages':
						if ($e = $imageobj->copy($dest)) {
							return "&mcrerr=" . $e;
						}
						break;
					case 'moveimages':
						if ($e = $imageobj->move($dest)) {
							return "&mcrerr=" . $e;
						}
						break;
					case 'changeowner':
						$imageobj->setOwner($newowner);
						break;
					default:
						$action = call_user_func($action, $imageobj);
						break;
				}
				$imageobj->save();
			}
		}
		return $action;
	}
}

/**
 * Processes the check box bulk actions for comments
 *
 */
function processCommentBulkActions() {
	if (isset($_POST['ids'])) { // these is actually the folder name here!
		$action = sanitize($_POST['checkallaction']);
		if ($action != 'noaction') {
			$ids = sanitize($_POST['ids']);
			if (count($ids) > 0) {
				foreach ($ids as $id) {
					$comment = new Comment(sanitize_numeric($id));
					switch ($action) {
						case 'deleteall':
							$comment->remove();
							break;
						case 'spam':
							if (!$comment->getInModeration()) {
								$comment->setInModeration(1);
								zp_apply_filter('comment_disapprove', $comment);
							}
							break;
						case 'approve':
							if ($comment->getInModeration()) {
								$comment->setInModeration(0);
								zp_apply_filter('comment_approve', $comment);
							}
							break;
					}
					$comment->save();
				}
			}
		}
	}
	return $action;
}

/**
 * Codeblock tabs JavaScript code
 *
 */
function codeblocktabsJS() {
	?>
	<script type="text/javascript" charset="utf-8">
		// <!-- <![CDATA[
		$(function() {
			var tabContainers = $('div.tabs > div');
			$('.first').addClass('selected');
		});

		function cbclick(num, id) {
			$('.cbx-' + id).hide();
			$('#cb' + num + '-' + id).show();
			$('.cbt-' + id).removeClass('selected');
			$('#cbt' + num + '-' + id).addClass('selected');
		}
		;

		function cbadd(id, offset) {
			var num = $('#cbu-' + id + ' li').size() - offset;
			$('li:last', $('#cbu-' + id)).remove();
			$('#cbu-' + id).append('<li><a class="cbt-' + id + '" id="cbt' + num + '-' + id + '" href="javascript:cbclick(' + num + ',' + id + ');" title="' + '<?php echo gettext('codeblock %u'); ?>'.replace(/%u/, num) + '">&nbsp;&nbsp;' + num + '&nbsp;&nbsp;</a></li>');
			$('#cbu-' + id).append('<li><a id="cbp-' + id + '" href="javascript:cbadd(' + id + ',' + offset + ');" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>');
			$('#cbd-' + id).append('<div class="cbx-' + id + '" id="cb' + num + '-' + id + '" style="display:none">' +
							'<textarea name="codeblock' + num + '-' + id + '" class="codeblock" id="codeblock' + num + '-' + id + '" rows="40" cols="60"></textarea>' +
							'</div>');
			cbclick(num, id);
		}
		// ]]> -->
	</script>
	<?php
}

/**
 *
 * prints codeblock edit boxes
 * @param object $obj
 * @param int $id
 */
function printCodeblockEdit($obj, $id) {
	$codeblock = @unserialize($obj->getCodeblock());
	if (empty($codeblock)) {
		$codeblock = array();
	}
	$keys = array_keys($codeblock);
	array_push($keys, 1);
	$codeblockCount = max($keys) + 1;

	if (array_key_exists(0, $codeblock) && !empty($codeblock)) {
		$start = 0;
	} else {
		$start = (int) getOption('codeblock_first_tab');
	}
	?>
	<div id="cbd-<?php echo $id; ?>" class="tabs">
		<ul id="<?php echo 'cbu' . '-' . $id; ?>" class="tabNavigation">
			<?php
			for ($i = $start; $i < $codeblockCount; $i++) {
				?>
				<li><a class="<?php if ($i == 1) echo 'first '; ?>cbt-<?php echo $id; ?>" id="<?php echo 'cbt' . $i . '-' . $id; ?>" href="javascript:cbclick(<?php echo $i . ',' . $id; ?>);" title="<?php printf(gettext('codeblock %u'), $i); ?>">&nbsp;&nbsp;<?php echo $i; ?>&nbsp;&nbsp;</a></li>
				<?php
			}
			?>
			<li><a id="<?php echo 'cbp' . '-' . $id; ?>" href="javascript:cbadd(<?php echo $id; ?>,<?php echo (int) 1 - $start; ?>);" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>
		</ul>

		<?php
		for ($i = $start; $i < $codeblockCount; $i++) {
			?>
			<div class="cbx-<?php echo $id; ?>" id="cb<?php echo $i . '-' . $id; ?>"<?php if ($i != 1) echo ' style="display:none"'; ?>>
				<?php
				if (!$i) {
					?>
					<span class="notebox"><?php echo gettext('Codeblock 0 is deprecated.') ?></span>
					<?php
				}
				?>
				<textarea name="codeblock<?php echo $i; ?>-<?php echo $id; ?>" class="codeblock" id="codeblock<?php echo $i; ?>-<?php echo $id; ?>" rows="40" cols="60"><?php echo html_encode(@$codeblock[$i]); ?></textarea>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 *
 * handles saveing of codeblock edits
 * @param object $object
 * @param int $id
 * @return string
 */
function processCodeblockSave($id) {
	$codeblock = array();
	$i = (int) !isset($_POST['codeblock0-' . $id]);
	while (isset($_POST['codeblock' . $i . '-' . $id])) {
		$v = sanitize($_POST['codeblock' . $i . '-' . $id], 0);
		if ($v) {
			$codeblock[$i] = $v;
		}
		$i++;
	}
	return serialize($codeblock);
}

/**
 * Standard admin pages checks
 * @param bit $rights
 * @param string $return--where to go after login
 */
function admin_securityChecks($rights, $return) {
	global $_zp_current_admin_obj, $_zp_loggedin;
	checkInstall();
	httpsRedirect();
	if ($_zp_current_admin_obj && $_zp_current_admin_obj->reset) {
		$_zp_loggedin = USER_RIGHTS;
	}
	if (!zp_loggedin($rights)) {
		// prevent nefarious access to this page.
		$returnurl = urldecode($return);
		if (!zp_apply_filter('admin_allow_access', false, $returnurl)) {
			$uri = explode('?', $returnurl);
			header("HTTP/1.0 302 Found");
			header("Status: 302 Found");
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $uri[0]);
			exitZP();
		}
	}
}

/**
 *
 * Checks if protocol not https and redirects if https required
 */
function httpsRedirect() {
	if (SERVER_PROTOCOL == 'https_admin') {
		// force https login
		if (!isset($_SERVER["HTTPS"])) {
			$redirect = "https://" . $_SERVER['HTTP_HOST'] . getRequestURI();
			header("Location:$redirect");
			exitZP();
		}
	}
}

/**
 * Checks for Cross Site Request Forgeries
 * @param string $action
 */
function XSRFdefender($action) {
	$token = getXSRFToken($action);
	if (!isset($_REQUEST['XSRFToken']) || $_REQUEST['XSRFToken'] != $token) {
		zp_apply_filter('admin_XSRF_access', false, $action);
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&error&msg=' . sprintf(gettext('"%s" Cross Site Request Forgery blocked.'), $action));
		exitZP();
	}
	unset($_REQUEST['XSRFToken']);
	if (isset($_POST['XSRFToken'])) {
		unset($_POST['XSRFToken']);
	}
	if (isset($_GET['XSRFToken'])) {
		unset($_GET['XSRFToken']);
	}
}

/**
 * getPageSelector "diff" function
 *
 * returns the shortest string difference
 * @param string $string1
 * @param string2 $string2
 */
function minDiff($string1, $string2) {
	if ($string1 == $string2) {
		return $string2;
	}
	if (empty($string1)) {
		return substr($string2, 0, 10);
	}
	if (empty($string2)) {
		return substr($string1, 0, 10);
	}
	if (strlen($string2) > strlen($string1)) {
		$base = $string2;
	} else {
		$base = $string1;
	}
	for ($i = 0; $i < min(strlen($string1), strlen($string2)); $i++) {
		if ($string1[$i] != $string2[$i]) {
			$base = substr($string2, 0, max($i + 1, 10));
			break;
		}
	}
	return rtrim($base, '-_');
}

/**
 * getPageSelector "diff" function
 *
 * Used when you want getPgeSelector to show the full text of the items
 * @param string $string1
 * @param string $string2
 * @return string
 */
function fullText($string1, $string2) {
	return $string2;
}

/**
 * getPageSelector "diff" function
 *
 * returns the shortest "date" difference
 * @param string $date1
 * @param string $date2
 * @return string
 */
function dateDiff($date1, $date2) {
	$separators = array('', '-', '-', ' ', ':', ':');
	preg_match('/(.*)-(.*)-(.*) (.*):(.*):(.*)/', $date1, $matches1);
	preg_match('/(.*)-(.*)-(.*) (.*):(.*):(.*)/', $date2, $matches2);
	if (empty($matches1)) {
		$matches1 = array(0, 0, 0, 0, 0, 0, 0);
	}
	if (empty($matches2)) {
		$matches2 = array(0, 0, 0, 0, 0, 0, 0);
	}

	$date = '';
	for ($i = 1; $i <= 6; $i++) {
		if (@$matches1[$i] != @$matches2[$i]) {
			break;
		}
	}
	switch ($i) {
		case 7:
		case 6:
			$date = ':' . $matches2[6];
		case 5:
		case 4:
			$date = ' ' . $matches2[4] . ':' . $matches2[5] . $date;
		default:
			$date = $matches2[1] . '-' . $matches2[2] . '-' . $matches2[3] . $date;
	}
	return rtrim($date, ':-');
}

/**
 * returns a selector list based on the "names" of the list items
 *
 *
 * @param array $list
 * @param int $itmes_per_page
 * @param string $diff
 * 									"fullText" for the complete names
 * 									"minDiff" for a truncated string showing just the unique characters of the names
 * 									"dateDiff" it the "names" are really dates.
 * @return array
 */
function getPageSelector($list, $itmes_per_page, $diff = 'fullText') {
	$rangeset = array();
	$pages = round(ceil(count($list) / (int) $itmes_per_page));
	$list = array_values($list);
	if ($pages > 1) {
		$ranges = array();
		for ($page = 0; $page < $pages; $page++) {
			$ranges[$page]['start'] = strtolower($list[$page * $itmes_per_page]);
			$last = (int) ($page * $itmes_per_page + $itmes_per_page - 1);
			if (array_key_exists($last, $list)) {
				$ranges[$page]['end'] = strtolower($list[$last]);
			} else {
				$ranges[$page]['end'] = strtolower(@array_pop($list));
			}
		}
		$last = '';
		foreach ($ranges as $page => $range) {
			$next = @$ranges[$page + 1]['start'];
			$rangeset[$page] = $diff($last, $range['start']) . ' » ' . $diff($next, $range['end']);
			$last = $range['end'];
		}
	}
	return $rangeset;
}

function printPageSelector($subpage, $rangeset, $script, $queryParams) {
	global $instances;
	$pages = count($rangeset);
	$jump = $query = '';
	foreach ($queryParams as $param => $value) {
		$query .= $param . '=' . $value . '&amp;';
		$jump .= "'" . $param . "=" . $value . "',";
	}
	$query = '?' . $query;
	if ($subpage > 0) {
		?>
		<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script . $query; ?>subpage=<?php echo ($subpage - 1); ?>" >« <?php echo gettext('prev'); ?></a>
		<?php
	}
	if ($pages > 2) {
		if ($subpage > 0) {
			?>
			|
			<?php
		}
		?>
		<select name="subpage" id="subpage<?php echo $instances; ?>" onchange="launchScript('<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script; ?>',
										[<?php echo $jump; ?>'subpage=' + $('#subpage<?php echo $instances; ?>').val()]);" >
							<?php
							foreach ($rangeset as $page => $range) {
								?>
				<option value="<?php echo $page; ?>" <?php if ($page == $subpage) echo ' selected="selected"'; ?>><?php echo $range; ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
	if ($pages > $subpage + 1) {
		if ($pages > 2) {
			?>
			|
		<?php }
		?>
		<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script . $query; ?>subpage=<?php echo ($subpage + 1); ?>" ><?php echo gettext('next'); ?> »</a>
		<?php
	}
	$instances++;
}

/**
 * Strips off quotes from the strng
 * @param $string
 */
function unQuote($string) {
	$string = trim($string);
	$q = $string{0};
	if ($q == '"' || $q == "'") {
		$string = trim($string, $q);
	}
	return $string;
}

/**
 * Returns an option list of administrators who can own albums or images
 * @param string $owner
 * @return string
 */
function admin_album_list($owner) {
	global $_zp_authority;
	$adminlist = '';
	$admins = $_zp_authority->getAdministrators();
	foreach ($admins as $user) {
		if (($user['rights'] & (UPLOAD_RIGHTS | ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))) {
			$adminlist .= '<option value="' . $user['user'] . '"';
			if ($owner == $user['user']) {
				$adminlist .= ' SELECTED="SELECTED"';
			}
			$adminlist .= '>' . $user['user'] . "</option>\n";
		}
	}
	return $adminlist;
}

/**
 * Figures out which log tabs to display
 */
function getLogTabs() {
	$subtabs = array();
	$default = NULL;
	$localizer = array('setup' => gettext('setup'), 'security' => gettext('security'), 'debug' => gettext('debug'));
	$filelist = safe_glob(SERVERPATH . "/" . DATA_FOLDER . '/*.log');
	if (count($filelist) > 0) {
		$tab = sanitize(@$_GET['tab'], 3);
		foreach ($filelist as $logfile) {
			$log = substr(basename($logfile), 0, -4);
			if ($log == $tab) {
				$default = $tab;
			}
			if (array_key_exists($log, $localizer)) {
				$logfiletext = $localizer[$log];
			} else {
				$logfiletext = str_replace('_', ' ', $log);
			}
			$subtabs = array_merge($subtabs, array($logfiletext => 'admin-logs.php?page=logs&amp;tab=' . $log));
			if (filesize($logfile) > 0 && empty($default)) {
				$default = $log;
			}
		}
	}
	return array($subtabs, $default);
}

/**
 * Figures out which plugin tabs to display
 */
function getPluginTabs() {
	if (isset($_GET['tab'])) {
		$default = sanitize($_GET['tab']);
	} else {
		$default = 'all';
	}
	$paths = getPluginFiles('*.php');

	$classXlate = array(
					'all'					 => gettext('all'),
					'admin'				 => gettext('admin'),
					'demo'				 => gettext('demo'),
					'development'	 => gettext('development'),
					'feed'				 => gettext('feed'),
					'mail'				 => gettext('mail'),
					'media'				 => gettext('media'),
					'misc'				 => gettext('misc'),
					'spam'				 => gettext('spam'),
					'seo'					 => gettext('seo'),
					'tools'				 => gettext('tools'),
					'uploader'		 => gettext('uploader'),
					'users'				 => gettext('users'),
					'utilities'		 => gettext('utilities')
	);
	zp_apply_filter('plugin_tabs', $classXlate);

	$currentlist = $classes = $member = array();
	foreach ($paths as $plugin => $path) {
		$p = file_get_contents($path);
		$i = strpos($p, '* @subpackage');
		if (($key = $i) !== false) {
			$key = strtolower(trim(substr($p, $i + 13, strpos($p, "\n", $i) - $i - 13)));
		}
		if (empty($key)) {
			$key = 'misc';
		}
		$classes[$key]['list'][] = $plugin;
		if (array_key_exists($key, $classXlate)) {
			$local = $classXlate[$key];
		} else {
			$local = $classXlate[$key] = $key;
		}
		$member[$plugin] = $local;
	}

	ksort($classes);
	$tabs[$classXlate['all']] = 'admin-plugins.php?page=plugins&amp;tab=all';
	$currentlist = array_keys($paths);


	foreach ($classes as $class => $list) {
		$tabs[$classXlate[$class]] = 'admin-plugins.php?page=plugins&amp;tab=' . $class;
		if ($class == $default) {
			$currentlist = $list['list'];
		}
	}
	return array($tabs, $default, $currentlist, $paths, $member);
}

function getAdminThumb($image, $size) {
	switch ($size) {
		case 'large':
			return $image->getCustomImage(80, NULL, NULL, 80, 80, NULL, NULL, -1);
		default:
			return $image->getCustomImage(40, NULL, NULL, 40, 40, NULL, NULL, -1);
	}
}

/**
 *
 * handles save of user/password
 * @param object $object
 */
function processCredentials($object, $suffix = '') {
	$notify = '';
	if (isset($_POST['password_enabled' . $suffix]) && $_POST['password_enabled' . $suffix]) {
		if (is_object($object)) {
			$olduser = $object->getUser();
		} else {
			$olduser = getOption($object . '_user');
		}
		$newuser = trim(sanitize($_POST['user' . $suffix], 3));
		$pwd = trim(sanitize($_POST['pass' . $suffix]));
		if (isset($_POST['disclose_password' . $suffix])) {
			$pass2 = $pwd;
		} else {
			if (isset($_POST['pass_r' . $suffix])) {
				$pass2 = trim(sanitize($_POST['pass_r' . $suffix]));
			} else {
				$pass2 = '';
			}
		}
		$fail = '';
		if ($olduser != $newuser) {
			if (!empty($newuser) && strlen($_POST['pass' . $suffix]) == 0) {
				$fail = '?mismatch=user';
			}
		}
		if (!$fail && $pwd == $pass2) {
			if (is_object($object)) {
				$object->setUser($newuser);
			} else {
				setOption($object . '_user', $newuser);
			}
			if (empty($pwd)) {
				if (strlen($_POST['pass' . $suffix]) == 0) {
					// clear the  password
					if (is_object($object)) {
						$object->setPassword(NULL);
					} else {
						setOption($object . '_password', NULL);
					}
				}
			} else {
				if (is_object($object)) {
					$object->setPassword(Zenphoto_Authority::passwordHash($newuser, $pwd));
				} else {
					setOption($object . '_password', Zenphoto_Authority::passwordHash($newuser, $pwd));
				}
			}
		} else {
			if (empty($fail)) {
				$notify = '?mismatch';
			} else {
				$notify = $fail;
			}
		}
		$hint = process_language_string_save('hint' . $suffix, 3);
		if (is_object($object)) {
			$object->setPasswordHint($hint);
		} else {
			setOption($object . '_hint', $hint);
		}
	}
	return $notify;
}

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
			$errorbox[] = gettext("You must supply a  password.");
		} else {
			$errorbox[] = gettext("Your passwords did not match.");
		}
	}
	if (isset($_GET['edit_error'])) {
		$errorbox[] = html_encode(sanitize($_GET['edit_error']));
	}
	if (isset($_GET['post_error'])) {
		$messagebox[] = gettext('The image edit form submission has been truncated. Try displaying fewer images on a page.');
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
				$messagebox[] = $action;
				break;
		}
	}
	if (isset($_GET['mcrerr'])) {
		switch (sanitize_numeric($_GET['mcrerr'])) {
			case 2:
				$errorbox[] = gettext("Image already exists.");
				break;
			case 3:
				$errorbox[] = gettext("Album already exists.");
				break;
			case 4:
				$errorbox[] = gettext("Cannot move, copy, or rename to a subalbum of this album.");
				break;
			case 5:
				$errorbox[] = gettext("Cannot move, copy, or rename to a dynamic album.");
				break;
			case 6:
				$errorbox[] = gettext('Cannot rename an image to a different suffix');
				break;
			case 7:
				$errorbox[] = gettext('Album delete failed');
				break;
			default:
				$errorbox[] = sprintf(gettext("There was an error #%d with a move, copy, or rename operation."), sanitize_numeric($_GET['mcrerr']));
				break;
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
 * returns an array of the theme scripts not in the exclude array
 * @param array $exclude those scripts to ignore
 * @return array
 */
function getThemeFiles($exclude) {
	global $_zp_gallery;
	$files = array();
	foreach (array_keys($_zp_gallery->getThemes()) as $theme) {
		$curdir = getcwd();
		$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/';
		chdir($root);
		$filelist = safe_glob('*.php');
		$list = array();
		foreach ($filelist as $file) {
			if (!in_array($file, $exclude)) {
				$files[$theme][] = filesystemToInternal($file);
			}
		}
		chdir($curdir);
	}
	return $files;
}

/**
 *
 * Checks for bad parentIDs from old move/copy bug
 * @param unknown_type $albumname
 * @param unknown_type $id
 */
function checkAlbumParentid($albumname, $id, $recorder) {
	$album = newAlbum($albumname);
	$oldid = $album->getParentID();
	if ($oldid != $id) {
		$album->set('parentid', $id);
		$album->save();
		if (is_null($oldid))
			$oldid = '<em>NULL</em>';
		if (is_null($id))
			$id = '<em>NULL</em>';
		$msg = sprintf('Fixed album <strong>%1$s</strong>: parentid was %2$s should have been %3$s<br />', $albumname, $oldid, $id);
		$recorder($msg, true);
		echo $msg;
	}
	$id = $album->getID();
	if (!$album->isDynamic()) {
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			checkAlbumParentid($albumname, $id, $recorder);
		}
	}
}
?>
