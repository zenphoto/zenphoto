<?php 
/**
 * General layout related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

/**
 * Print the footer <div> for the bottom of all admin pages.
 *
 * @param string $addl additional text to output on the footer.
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since 1.0.0
 */
function printAdminFooter($addl = '') {
	global $_zp_db;
	?>
	<div id="footer">
		<button type="button" class="scrollup hidden" title="<?php echo gettext('Scroll to top'); ?>"><?php echo gettext('Top'); ?></button>
		<?php
		printf(gettext('<a href="https://www.zenphoto.org" target="_blank" rel="noopener noreferrer" title="The simpler media website CMS">Zen<strong>photo</strong></a> version %1$s'), ZENPHOTO_VERSION);
		if (!empty($addl)) {
			echo ' | ' . $addl;
		}
		?>
		| <a href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/license.php' ?>" title="<?php echo gettext('Zenphoto licence'); ?>"><?php echo gettext('License'); ?></a>
		| <a href="https://www.zenphoto.org/news/category/user-guide" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('User guide'); ?>"><?php echo gettext('User guide'); ?></a>
		| <a href="https://forum.zenphoto.org/" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('Forum'); ?>"><?php echo gettext('Forum'); ?></a>
		| <a href="https://github.com/zenphoto/zenphoto/issues" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('Bugtracker'); ?>"><?php echo gettext('Bugtracker'); ?></a>
		| <a href="https://www.zenphoto.org/news/category/changelog" target="_blank" rel="noopener noreferrer" title="<?php echo gettext('View Change log'); ?>"><?php echo gettext('Change log'); ?></a>
		| <?php printf(gettext('Server date: %s'), date('Y-m-d H:i:s')); ?>
	</div>
	<?php
	$_zp_db->close(); //	close the database as we are done
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
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/i18n/jquery.ui.datepicker-<?php echo $lang; ?>.js"></script>
		<?php
	}
}

/**
 * Print the header for all admin pages. Starts at <DOCTYPE> but does not include the </head> tag,
 * in case there is a need to add something further.
 *
 * @param string $tab the album page
 * @param string $subtab the sub-tab if any
 */
function printAdminHeader($tab, $subtab = NULL) {
	global $_zp_admin_current_page, $_zp_admin_current_subpage, $_zp_gallery, $_zp_admin_menu, $_zp_rtl_css;
	handleDeprecatedMenuGlobals();
	$_zp_admin_current_page = $tab;
	if (isset($_GET['tab'])) {
		$_zp_admin_current_subpage = sanitize($_GET['tab'], 3);
	} else {
		$_zp_admin_current_subpage = $subtab;
	}
	$tabtext = $_zp_admin_current_page;
	$tabrow = NULL;
	foreach ($_zp_admin_menu as $key => $tabrow) {
		if ($key == $_zp_admin_current_page) {
			$tabtext = $tabrow['text'];
			break;
		}
		$tabrow = NULL;
	}
	if (empty($_zp_admin_current_subpage) && $tabrow && isset($tabrow['default'])) {
		$_zp_admin_current_subpage = $_zp_admin_menu[$_zp_admin_current_page]['default'];
	}
	$subtabtext = '';
	if ($_zp_admin_current_subpage && $tabrow && array_key_exists('subtabs', $tabrow) && $tabrow['subtabs']) {
		foreach ($tabrow['subtabs'] as $key => $link) {
			$i = strpos($link, '&tab=');
			if ($i !== false) {
				$text = substr($link, $i + 9);
				if ($text == $_zp_admin_current_subpage) {
					$subtabtext = '-' . $key;
					break;
				}
			}
		}
	}
	if (empty($subtabtext)) {
		if ($_zp_admin_current_subpage) {
			$subtabtext = '-' . $_zp_admin_current_subpage;
		}
	}
	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	header('Cache-Control: no-cache; private; max-age=600; must-revalidate');
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	$matomo_url = '';
	if (extensionEnabled('matomo') && getOption('matomo_url')) {
		$matomo_url = sanitize(getOption('matomo_url')) . '/';
	}
	header("Content-Security-Policy: default-src " . FULLWEBPATH . "/ 'unsafe-inline' 'unsafe-eval' https://www.google.com/; img-src 'self' blob: data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com/ https://www.gstatic.com/; frame-src 'self' data: " . $matomo_url . "");
	header('X-Frame-Options: deny');
	header('X-Content-Type-Options: nosniff');
	header('Referrer-Policy: origin');
	zp_apply_filter('admin_headers');
	?>
	<!DOCTYPE html>
	<html<?php printLangAttribute(); ?>>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/toggleElements.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui.min.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin.css" type="text/css" />
			<?php
			if ($_zp_rtl_css) {
				?>
				<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-rtl.css" type="text/css" />
				<?php
			}
			?>
			<title><?php echo sprintf(gettext('%1$s %2$s: %3$s%4$s'), html_encode($_zp_gallery->getTitle()), gettext('admin'), html_encode($tabtext), html_encode($subtabtext)); ?></title>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.min.js"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery-migrate.min.js" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui.min.js"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zp_general.js" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zp_admin.js" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.scrollTo.min.js"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.dirtyforms.min.js"></script>
			<script>

				$(document).ready(function () {
	<?php
	if (zp_has_filter('admin_head', 'colorbox::css')) {
		?>
						$("a.colorbox").colorbox({
							maxWidth: "98%",
							maxHeight: "98%",
							close: '<?php echo addslashes(gettext("close")); ?>'
						});

		<?php
	}
	?>
					$('form.dirty-check').dirtyForms({
						message: '<?php echo addslashes(gettext('You have unsaved changes!')); ?>',
						ignoreSelector: '.dirtyignore'
					});
				});
				$(function () {
					$(".tooltip ").tooltip({
						show: 1000,
						hide: 1000,
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function (position, feedback) {
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
							using: function (position, feedback) {
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
				jQuery(function ($) {
					$(".fade-message").fadeTo(5000, 1).fadeOut(1000);
				})
			</script>
			<?php
			zp_apply_filter('admin_head');
		}

		function printSortableHead() {
			?>
			<!--Nested Sortables-->
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.mjs.nestedSortable.js"></script>
			<script>
				$(document).ready(function () {

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

					$('.serialize').click(function () {
						serialized = $('ul.page-list').nestedSortable('serialize');
						if (serialized != original_order) {
							$('#serializeOutput').html('<input type="hidden" name="order" size="30" maxlength="1000" value="' + serialized + '" />');
						}
					})
					var original_order = $('ul.page-list').nestedSortable('serialize');
				});
			</script>
			<!--Nested Sortables End-->
			<?php
		}

		/**
		 * Print the html required to display the ZP logo and links in the top section of the admin page.
		 *
		 * @author Todd Papaioannou (lucky@luckyspin.org)
		 * @since 1.0.0
		 */
		function printLogoAndLinks() {
			global $_zp_current_admin_obj, $_zp_admin_current_page, $_zp_admin_current_subpage, $_zp_gallery;
			handleDeprecatedMenuGlobals();
			if ($_zp_admin_current_subpage) {
				$subtab = '-' . $_zp_admin_current_subpage;
			} else {
				$subtab = '';
			}
			maintenancemode::printStateNotice();
			?>

		<span id="administration">
			<img id="logo" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png"
					 title="<?php echo sprintf(gettext('%1$s administration:%2$s%3$s'), html_encode($_zp_gallery->getTitle()), html_encode($_zp_admin_current_page), html_encode($subtab)); ?>"
					 alt="<?php echo gettext('Zenphoto Administration'); ?>" align="bottom" />
		</span>
		<?php
		echo "\n<div id=\"links\">";
		echo "\n  ";
		if (!is_null($_zp_current_admin_obj)) {
			$last = $_zp_current_admin_obj->getLastlogon();
			if (empty($last)) {
				printf(gettext('Logged in as %1$s'), $_zp_current_admin_obj->getUser());
			} else {
				printf(gettext('Logged in as %1$s (last login %2$s)'), $_zp_current_admin_obj->getUser(), $last);
			}
			if ($_zp_current_admin_obj->logout_link) {
				$link = Authority::getLogoutURL('backend');
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
	 * @since 1.0.0
	 */
	function printTabs() {
		global $_zp_admin_submenu, $_zp_admin_menu, $_zp_admin_maintab_space, $_zp_admin_current_page;
		handleDeprecatedMenuGlobals();
		$chars = 0;
		foreach ($_zp_admin_menu as $atab) {
			$chars = $chars + mb_strlen($atab['text']);
		}
		switch (getOption('locale')) {
			case 'zh_CN':
			case 'zh_TW':
			case 'ja_JP':
				$_zp_admin_maintab_space = count($_zp_admin_menu) * 4 + $chars;
				break;
			default:
				$_zp_admin_maintab_space = round((count($_zp_admin_menu) * 32 + round($chars * 7.5)) / 11.5);
				break;
		}
		?>
		<ul class="nav" style="width: <?php echo $_zp_admin_maintab_space; ?>em">
			<?php
			foreach ($_zp_admin_menu as $key => $atab) {
				?>
				<li <?php if ($_zp_admin_current_page == $key) echo 'class="current"' ?>>
					<a href="<?php echo html_encode($atab['link']); ?>"><?php echo html_encode(ucfirst($atab['text'])); ?></a>
					<?php
					$_zp_admin_submenu = $_zp_admin_menu[$key]['subtabs'];
					if (is_array($_zp_admin_submenu)) { // don't print <ul> if there is nothing
						if ($_zp_admin_current_page != $key) { // don't print sublist if already on the main tab
							?>
							<ul class="subdropdown">
								<?php
								foreach ($_zp_admin_submenu as $key => $link) {
									?>
									<li><a href="<?php echo html_encode($link); ?>"><?php echo html_encode(ucfirst($key)); ?></a></li>
									<?php
								} // foreach end
								?>
							</ul>
							<?php
						} // if $_zp_admin_submenu end
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
		global $_zp_admin_menu, $_zp_admin_current_page, $_zp_admin_current_subpage;
		handleDeprecatedMenuGlobals();
		$tabs = @$_zp_admin_menu[$_zp_admin_current_page]['subtabs'];
		if (!is_array($tabs))
			return $_zp_admin_current_subpage;
		$current = $_zp_admin_current_subpage;
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
			if (isset($_zp_admin_menu[$_zp_admin_current_page]['default'])) {
				$current = $_zp_admin_menu[$_zp_admin_current_page]['default'];
			} else if (empty($_zp_admin_current_subpage)) {
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
				$current = $_zp_admin_current_subpage;
			}
		}
		return $current;
	}

	function printSubtabs() {
		global $_zp_admin_menu, $_zp_admin_current_page, $_zp_admin_current_subpage;
		handleDeprecatedMenuGlobals();
		$tabs = @$_zp_admin_menu[$_zp_admin_current_page]['subtabs'];
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
					$sub_tab_space = count($tabs) * 4 + $chars;
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
						$tab = $_zp_admin_current_subpage;
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
					if (strpos($link, FULLWEBPATH) !== 0) {
						$link = FULLWEBPATH . $link;
					}
					echo '<li' . (($current == $tab) ? ' class="current"' : '') . '><a href="' . html_encode($link) . '">' . html_encode(ucfirst($key)) . '</a></li>' . "\n";
				}
				?>
			</ul>
			<?php
		}
		return $current;
	}

	function setAlbumSubtabs($album) {
		global $_zp_admin_menu;
		handleDeprecatedMenuGlobals();
		$albumlink = '?page=edit&album=' . urlencode($album->name);
		$default = NULL;
		if (!is_array($_zp_admin_menu['edit']['subtabs'])) {
			$_zp_admin_menu['edit']['subtabs'] = array();
		}
		$subrights = $album->albumSubRights();
		if (!$album->isDynamic() && $album->getNumImages()) {
			if ($subrights & (MANAGED_OBJECT_RIGHTS_UPLOAD || MANAGED_OBJECT_RIGHTS_EDIT)) {
				$_zp_admin_menu['edit']['subtabs'] = array_merge(
								array(gettext('Images') => FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php' . $albumlink . '&tab=imageinfo'), $_zp_admin_menu['edit']['subtabs']
				);
				$default = 'imageinfo';
			}
			if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
				$_zp_admin_menu['edit']['subtabs'] = array_merge(
								array(gettext('Image order') => FULLWEBPATH . '/' . ZENFOLDER . '/admin-albumsort.php' . $albumlink . '&tab=sort'), $_zp_admin_menu['edit']['subtabs']
				);
			}
		}
		if (!$album->isDynamic() && $album->getNumAlbums()) {
			$_zp_admin_menu['edit']['subtabs'] = array_merge(
							array(gettext('Subalbums') => FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php' . $albumlink . '&tab=subalbuminfo'), $_zp_admin_menu['edit']['subtabs']
			);
			$default = 'subalbuminfo';
		}
		if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
			$_zp_admin_menu['edit']['subtabs'] = array_merge(
							array(gettext('Album') => FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php' . $albumlink . '&tab=albuminfo'), $_zp_admin_menu['edit']['subtabs']
			);
			$default = 'albuminfo';
		}
		$_zp_admin_menu['edit']['default'] = $default;
		if (isset($_GET['tab'])) {
			return sanitize($_GET['tab']);
		}
		return $default;
	}
	
	/**
	 * Roughly fixes outdated usages of old admin "tab" globals and joins them with the actual globals.
	 * and also throws deprecation notices about this.
	 * 
	 * @since 1.6
	 * 
	 * @global type $_zp_admin_menu
	 * @global type $_zp_admin_current_page
	 * @global type $_zp_admin_current_subpage
	 * @global type $_zp_admin_menu
	 * @global type $_zp_admin_current_subpage
	 * @global type $_zp_admin_subtab
	 */
	function handleDeprecatedMenuGlobals() {
		global $_zp_admin_menu, $_zp_admin_submenu, $_zp_admin_current_page, $_zp_admin_current_subpage;

		//The deprecated ones from  1.5.x
		global $zenphoto_tabs, $subtabs, $_zp_admin_tab, $_zp_admin_subtab;

		if (isset($zenphoto_tabs)) {
			trigger_error(gettext('The global $zenphoto_tabs is deprecated. Use $_zp_admin_menu instead'), E_USER_DEPRECATED);
			$_zp_admin_menu = array_merge($_zp_admin_menu, $zenphoto_tabs);
		}
		if (isset($subtabs)) {
			trigger_error(gettext('The global $subtabs is deprecated. Use $_zp_admin_submenu instead'), E_USER_DEPRECATED);
			$_zp_admin_submenu = array_merge($_zp_admin_submenu, $subtabs);
		}
		if (isset($_zp_admin_tab)) {
			trigger_error(gettext('The global $_zp_admin_tab is deprecated. Use $_zp_admin_current_page instead'), E_USER_DEPRECATED);
			$_zp_admin_current_page = $_zp_admin_current_subpage;
		}
		if (isset($_zp_admin_subtab)) {
			trigger_error(gettext('The global $_zp_admin_subtab is deprecated. Use $_zp_admin_current_subpage instead'), E_USER_DEPRECATED);
			$_zp_admin_current_subpage = $_zp_admin_subtab;
		}
	}
	
	/**
 * Gets an array with the size values for the admin thumb generation
 * 
 * @since 1.6.3
 * 
 * @param obj $imageobj The image object
 * @param string $size Adminthumb sizeame: 'large', 'small', 'large-uncropped', 'small-uncropped'
 * @return array
 */
function getAdminThumbSizes($imageobj, $size = 'small') {
	$sizes = array(
			'thumbsize' => null,
			'width' => null,
			'height' => null,
			'cropwidth' => null,
			'cropheight' => null
	);
	switch ($size) {
		case 'large':
			$sizes['thumbsize'] = 80;
			$sizes['cropwidth'] = 80;
			$sizes['cropheight'] = 80;
			break;
		case 'small':
		default:
			$sizes['thumbsize'] = 40;
			$sizes['cropwidth'] = 40;
			$sizes['cropheight'] = 40;
			break;
		case 'large-uncropped':
			if ($imageobj->isSquare('thumb')) {
				$sizes['thumbsize'] = 135;
			} else if ($imageobj->isLandscape('thumb')) {
				$sizes['width'] = 135;
			} else if ($imageobj->isPortrait('thumb')) {
				$sizes['height'] = 135;
			}
			break;
		case 'small-uncropped':
			if ($imageobj->isSquare('thumb')) {
				$sizes['thumbsize'] = 110;
			} else if ($imageobj->isLandscape('thumb')) {
				$sizes['width'] = 110;
			} else if ($imageobj->isPortrait('thumb')) {
				$sizes['height'] = 110;
			}
			break;
	}
	return $sizes;
}

/**
 * Gets the URL of the adminthumb
 * 
 * @param obj $imageobj The image object
 * @param string $size Adminthumb sizeame: 'large', 'small', 'large-uncropped', 'small-uncropped'
 * @return string
 */
function getAdminThumb($imageobj, $size = 'small') {
	$values = getAdminThumbSizes($imageobj, $size);
	return $imageobj->getCustomImage($values['thumbsize'], $values['width'], $values['height'], $values['cropwidth'], $values['cropheight'], null, null, true);
}

/**
 * Returns an array with width and height of the resized image
 * 
 * @since 1.6.3
 * 
 * @param obj  $imageobj The image object
 * @param string $size Adminthumb sizeame: 'large', 'small', 'large-uncropped', 'small-uncropped'
 * @return array
 */
function getSizeAdminThumb($imageobj, $size = 'small') {
	$values = getAdminThumbSizes($imageobj, $size);
	return $imageobj->getSizeCustomImage($values['thumbsize'], $values['width'], $values['height'], $values['cropwidth'], $values['cropheight'], null, null, 'thumb');
}

/**
 * Returns the full HTML element of an admin thumb
 * Applies the filters 'adminthumb_html'
 * 
 * @since 1.5.8
 * 
 * @param obj $imageobj The image object
 * @param string $size Adminthumb sizeame: 'large', 'small', 'large-uncropped', 'small-uncropped'
 * @param string $class Class name(s) to attach
 * @param string $id ID to attach
 * @param string $alt Alt attribute
 * @param string $title Title attribute
 * @return string
 */
function getAdminThumbHTML($imageobj, $size = 'small', $class = null, $id = null, $alt = null, $title = null) {
	if (empty($title)) {
		$title = $alt;
	}
	$dimensions = getSizeAdminThumb($imageobj, $size);
	$attr = array(
			'src' => html_pathurlencode(getAdminThumb($imageobj, $size)),
			'width' => $dimensions[0],
			'height' => $dimensions[1],
			'alt' => html_encode($alt),
			'class' => $class,
			'id' => $id,
			'title' => html_encode($title),
			'loading' => 'lazy'
	);
	$attr_filtered = zp_apply_filter('adminthumb_attr', $attr, $imageobj);
	$attributes = generateAttributesFromArray($attr_filtered);
	$html = '<img' . $attributes . ' />';
	return zp_apply_filter('adminthumb_html', $html, $size, $imageobj);
}

/**
 * Prints an admin thumb
 * 
 * @since 1.5.8
 * 
 * @param obj $imageobj The image object
 * @param string $size Adminthumb sizeame: 'large', 'small', 'large-uncropped', 'small-uncropped'
 * @param string $class Class name(s) to attach
 * @param string $id ID to attach
 * @param string $alt Alt attribute
 * @param string $title Title attribute
 * @return string
 */
function printAdminThumb($imageobj, $size = 'small', $class = null, $id = null, $alt = null, $title = null) {
	echo getAdminThumbHTML($imageobj, $size, $class, $id, $title, $alt);
}

/**
 * Gets an key => value array of all available object status icons as full <img> elements 
 * @since 1.6.1
 * 
 * @return array
 */
function getStatusIcons() {
	return array(
			'publishschedule' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/clock_futuredate.png" alt="' . htmL_encode(getStatusNote('publishschedule')) . '" title="' . htmL_encode(getStatusNote('publishschedule')) . '" />',
			'expiration' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/clock_expiredate.png" alt="' . html_encode(getStatusNote('expiration')) . '" title="' . html_encode(getStatusNote('expiration')) . '" />',
			'expired' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/clock_expired.png" alt="' . html_encode(getStatusNote('expired')) . '" title="' . html_encode(getStatusNote('expired')) . '" />',
			'published' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="' . html_encode(getStatusNote('published')) . '" title="' . html_encode(getStatusNote('published')) . '" />',
			'unpublished' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/action.png" alt="' . getStatusNote('') . '" title="' . getStatusNote('') . '" />',
			'unpublished_by_parent' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/pass_2.png" alt="' . html_encode(getStatusNote('unpublished_by_parent')) . '" title="' . html_encode(getStatusNote('unpublished_by_parent')) . '" />',
			'protected' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/lock.png" alt="' . html_encode(getStatusNote('protected')) . '" title="' . html_encode(getStatusNote('protected')) . '" />',
			'protected_by_parent' => '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/lock_3.png" alt="' . html_encode(getStatusNote('protected_by_parent')) . '" title="' . html_encode(getStatusNote('protected_by_parent')) . '" />',
	);
}

/**
 * Gets the icon img element for a specific status icon
 * @since 1.6.1
 * 
 * @param string $name (Internal) Name of the icon
 * @return string
 */
function getStatusIcon($name = '') {
	$icons = getStatusIcons();
	if (array_key_exists($name, $icons)) {
		return $icons[$name];
	}
}

/**
 * Prints the publish icon link to change the status on the album and thumb image list
 * 
 * @since 1.5.7
 * @param object $obj Image or album object
 * @param boolean $enableedit  true if allowed to use
 * @param string $owner User name of the owner
 */
function printPublishIconLinkGallery($obj, $enableedit = false, $owner = null) {
	if ($obj->table == 'albums' || $obj->table == 'images') {
		switch ($obj->table) {
			case 'albums':
				$title_skipscheduledpublishing = sprintf(gettext('Publish the album %s (Skip scheduled publishing)'), $obj->name);
				$title_skipscheduledexpiration = sprintf(gettext('Publish the album %s (Skip scheduled expiration)'), $obj->name);
				$title_unpublish = sprintf(gettext('Unpublish the album %s'), $obj->name);
				$title_skipexiration = sprintf(gettext('Publish the album %s (Skip expiration)'), $obj->name);
				$title_publish = sprintf(gettext('Publish the album %s'), $obj->name);
				$action_addition = '&amp;album=' . html_encode(pathurlencode($obj->name)) . '&amp;return=*' . html_encode(pathurlencode($owner)) . '&amp;XSRFToken=' . getXSRFToken('albumedit');
				break;
			case 'images':
				$title_skipscheduledpublishing = sprintf(gettext('Publish the image %s (Skip scheduled publishing)'), $obj->filename);
				$title_skipscheduledexpiration = sprintf(gettext('Publish the image %s (Skip scheduled expiration)'), $obj->filename);
				$title_unpublish = sprintf(gettext('Unpublish the image %s'), $obj->filename);
				$title_skipexiration = sprintf(gettext('Publish the image %s (Skip expiration)'), $obj->filename);
				$title_publish = sprintf(gettext('Publish the image %s'), $obj->filename);
				$action_addition = '&amp;album=' . html_encode(pathurlencode($obj->album->name)) . '&amp;image=' . urlencode($obj->filename) . '&amp;XSRFToken=' . getXSRFToken('imageedit');
				break;
		}
		if ($obj->hasPublishSchedule()) {
			$title = $title_skipscheduledpublishing;
			$action = '?action=publish&amp;value=1';
		} else if ($obj->hasExpiration()) {
			$title = $title_skipscheduledexpiration;
			$action = '?action=publish&amp;value=1';
		} else if ($obj->isPublished()) {
			if ($obj->isUnpublishedByParent()) {
				$title = $title_publish .' - ' . getStatusNote('unpublished_by_parent');
				$action = '?action=publish&amp;value=0';
			} else {
				$title = $title_unpublish;
				$action = '?action=publish&amp;value=0';
			}
		} else if (!$obj->isPublished()) {
			if ($obj->hasExpired()) {
				$title = $title_skipexiration;
				$action = '?action=publish&amp;value=1';
			} else {
				$title = $title_publish;
				$action = '?action=publish&amp;value=1';
			}
		}
		$link_start = $link_end = '';
		if ($enableedit) {
			$link_start = '<a href="' . $action . $action_addition . '" title="' . html_encode($title) . '" >';
			$link_end = '</a>';
		}
		echo $link_start . getPublishIcon($obj) . $link_end;
	}
}

/**
 * Returns the publish icon for the current status
 * @since 1.6.1
 * 
 * @param obj $obj Object of the page or news article to check
 * @return string
 */
function getPublishIcon($obj) {
	if ($obj->hasPublishSchedule()) {
		return getStatusIcon('publishschedule');
	} else if ($obj->hasExpiration()) {
		return getStatusIcon('expiration');
	} else if ($obj->isPublished()) {
		if ($obj->isUnpublishedByParent()) {
			return getStatusIcon('unpublished_by_parent');
		} else {
			return getStatusIcon('published');
		}
	} else if (!$obj->isPublished()) {
		if ($obj->hasExpired()) {
			return getStatusIcon('expired');
		} else {
			return getStatusIcon('unpublished');
		}
	}
}

/**
 * Prints the protected icon if the object is password protected on an edit list
 * @since 1.6.1
 * 
 * @param obj $obj
 */
function printProtectedIcon($obj) {
	if ($obj->getPassword() && GALLERY_SECURITY == 'public') {
		echo '<button data-toggle="tooltip" disabled title="' . html_encode(getStatusNote('protected')) . '">' . getStatusIcon('protected')  . '</button>';
	} else if ($obj->isProtectedByParent() && GALLERY_SECURITY == 'public') {
		echo '<button data-toggle="tooltip" disabled title="' . html_encode(getStatusNote('protected_by_parent')) . '">' . getStatusIcon('protected_by_parent') . '</button>';
	} else if (GALLERY_SECURITY != 'public') {
		echo '<button data-toggle="tooltip" disabled title="' . html_encode(getStatusNote('protected_by_site_private_mode')) . '">' . getStatusIcon('protected_by_parent') . '</button>';
	}
}

