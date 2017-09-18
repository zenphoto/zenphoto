<?php
/**
 * support functions for Admin
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions.php');

define('TEXTAREA_COLUMNS', 50);
define('TEXT_INPUT_SIZE', 48);
define('TEXTAREA_COLUMNS_SHORT', 32);
define('TEXT_INPUT_SIZE_SHORT', 30);
if (!defined('EDITOR_SANITIZE_LEVEL'))
	define('EDITOR_SANITIZE_LEVEL', 1);

define('ADMIN_THUMB_LARGE', 160);
define('ADMIN_THUMB_MEDIUM', 80);
define('ADMIN_THUMB_SMALL', 40);

define('UPLOAD_ERR_QUOTA', -1);
define('UPLOAD_ERR_BLOCKED', -2);

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
		echo gettext('<span class="zenlogo"><a href="https://' . GITHUB . '" title="' . gettext('A simpler media content management system') . '"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/zen-logo-light.png" /></a></span> ') . sprintf(gettext('version %1$s'), ZENPHOTO_VERSION);

		if (!empty($addl)) {
			echo ' | ' . $addl;
		}
		?>
		| <a href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/license.php' ?>" title="<?php echo gettext('ZenPhoto20 licence'); ?>"><?php echo gettext('License'); ?></a>
		| <a href="https://<?php echo GITHUB; ?>/issues" title="<?php echo gettext('Support'); ?>"><?php echo gettext('Support'); ?></a>
		| <a href="https://<?php echo GITHUB; ?>/commits/master" title="<?php echo gettext('View Change log'); ?>"><?php echo gettext('Change log'); ?></a>
		| <?php printf(gettext('Server date: %s'), date('Y-m-d H:i:s')); ?>
	</div>
	<script type="text/javascript">
		startingPosition = $('.navigation').position().top + 10;
		// ===== Scroll to Top ====
		$(window).scroll(function () {
			var scroll = $(this).scrollTop()
			if (scroll > startingPosition) {
				$('.navigation').offset({top: scroll});
			} else {
				$('.navigation').offset({top: startingPosition});
			}

			if (scroll >= 50) {        // If page is scrolled more than 50px
				$('#return-to-top').fadeIn(200); // Fade in the arrow
			} else {
				$('#return-to-top').fadeOut(200); // Else fade out the arrow
			}
		});
		$('#return-to-top').click(function () {      // When arrow is clicked
			$('body,html').animate({
				scrollTop: 0                       // Scroll to top of body
			}, 400);
		});
	</script>
	<?php
	db_close(); //	close the database as we are done
}

function datepickerJS() {
	$lang = str_replace('_', '-', getOption('locale'));
	if (!file_exists(SERVERPATH . '/' . ZENFOLDER . '/js/jqueryui/i18n/datepicker-' . $lang . '.js')) {
		$lang = substr($lang, 0, 2);
		if (!file_exists(SERVERPATH . '/' . ZENFOLDER . '/js/jqueryui/i18n/datepicker-' . $lang . '.js')) {
			$lang = '';
		}
	}
	if (!empty($lang)) {
		?>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/i18n/datepicker-<?php echo $lang; ?>.js" type="text/javascript"></script>
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
	global $_zp_admin_tab, $_zp_admin_subtab, $_zp_gallery, $zenphoto_tabs, $_zp_RTL_css, $tabtext, $subtabtext;
	$_zp_admin_tab = $tab;
	if (isset($_GET['tab'])) {
		$_zp_admin_subtab = sanitize($_GET['tab'], 3);
	} else {
		$_zp_admin_subtab = $subtab;
	}
	$tabtext = ucfirst($_zp_admin_tab);
	$tabrow = NULL;
	foreach ($zenphoto_tabs as $key => $tabrow) {
		if ($key == $_zp_admin_tab) {
			$tabtext = ucfirst($tabrow['text']);
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
			preg_match('~tab=(.*?)(&|$)~', $link, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == $_zp_admin_subtab) {
					$subtabtext = '-' . ucfirst($key);
					break;
				}
			}
		}
	}
	if (empty($subtabtext)) {
		if ($_zp_admin_subtab) {
			$subtabtext = '-' . ucfirst($_zp_admin_subtab);
		}
	}
	$multi = getOption('multi_lingual');
	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	zp_apply_filter('admin_headers');
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php printStandardMeta(); ?>
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui-zenphoto.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/loginForm.css" type="text/css" />
			<?php
			if ($_zp_RTL_css) {
				?>
				<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-rtl.css" type="text/css" />
				<?php
			}
			if ($multi) {
				?>
				<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/msdropdown/dd.css" type="text/css" />
				<?php
			}
			?>

			<title><?php echo sprintf(gettext('%1$s %2$s: %3$s%4$s'), html_encode($_zp_gallery->getTitle()), gettext('Admin'), html_encode($tabtext), html_encode($subtabtext)); ?></title>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.js" type="text/javascript"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui-zenphoto.js" type="text/javascript"></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/admin.js" type="text/javascript" ></script>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.scrollTo.js" type="text/javascript"></script>

			<?php
			if (extensionEnabled('touchPunch')) {
				?>
				<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.ui.touch-punch.min.js"></script>
				<?php
			}
			if ($multi) {
				?>
				<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/msdropdown/jquery.dd.min.js" type="text/javascript"></script>
				<?php
			}
			if (getOption('dirtyform_enable')) {
				?>
				<!--
				<script src="<?php echo WEBPATH ?>/jquery.dirtyforms.dist-master/jquery.dirtyforms.js" type="text/javascript"></script>
				-->
				<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/dirtyforms/jquery.dirtyforms.min.js" type="text/javascript"></script>
				<?php
			}
			?>
			<script type="text/javascript">
		// <!-- <![CDATA[
		function setClean(id) {
			$('form#' + id).removeClass('tinyDirty');
		}
	<?php
	if ($multi) {
		?>
			function lsclick(key, id) {
				$('.lbx-' + id).hide();
				$('#lb' + key + '-' + id).show();
				$('.lbt-' + id).removeClass('selected');
				$('#lbt-' + key + '-' + id).addClass('selected');
			}
		<?php
	}
	?>
		jQuery(function ($) {
			$(".fade-message").fadeTo(5000, 1).fadeOut(1000);
		});
		window.addEventListener('load', function () {
			var high = $('.navigation').height() - 65;
			$('#container').css('min-height', high);
			$('.tabbox').css('min-height', high);

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
	if ($multi) {
		?>
				try {
					$('.languageSelector').msDropDown();
				} catch (e) {
					alert(e.message);
				}
		<?php
	}
	if (getOption('dirtyform_enable')) {
		?>
				$.DirtyForms.ignoreClass = 'ignoredirty';
				$('form.dirtylistening').dirtyForms({debug: true});
		<?php
	}
	?>

		}, false);
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
		window.addEventListener('load', function () {

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
				listType: 'ul',
				change: function (event, ui) {
					$('#sortableListForm').dirtyForms('setDirty');
				}
			});
			$('.serialize').click(function () {
				serialized = $('ul.page-list').nestedSortable('serialize');
				if (serialized != original_order) {
					$('#serializeOutput').html('<input type="hidden" name="order" size="30" maxlength="1000" value="' + serialized + '" />');
				}
			})
			var original_order = $('ul.page-list').nestedSortable('serialize');
		}, false);
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
			global $_zp_current_admin_obj, $_zp_admin_tab, $_zp_admin_subtab, $_zp_gallery, $tabtext, $subtabtext;
			?>
		<div id="admin_head">
			<span id="administration">
				<img id="logo" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png"
						 title="<?php echo sprintf(gettext('%1$s administration:%2$s%3$s'), html_encode($_zp_gallery->getTitle()), html_encode($tabtext), html_encode($subtabtext)); ?>"
						 alt="<?php echo gettext('ZenPhoto20 Administration'); ?>" />
			</span>
			<span id="links">
				<?php
				if (is_object($_zp_current_admin_obj) && !$_zp_current_admin_obj->reset) {
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
				?>
				<a href="<?php echo FULLWEBPATH; ?>/">
					<?php
					$t = $_zp_gallery->getTitle();
					if (!empty($t)) {
						printf(gettext("View <em>%s</em>"), $t);
					} else {
						echo gettext("View gallery index");
					}
					?>
				</a>
			</span>
		</div>
		<a href="javascript:" id="return-to-top" class="ignoredirty" title="<?php echo gettext('return to top'); ?>"></a>
		<?php
	}

	function printSetupWarning() {
		list($diff, $needs, $found, $present) = checkSignature(0);
		if (zp_loggedin(ADMIN_RIGHTS) && $present && (zpFunctions::hasPrimaryScripts() || empty($needs))) {
			//	button to restore setup files if needed
			if (empty($needs)) {
				?>
				<div class="warningbox">
					<h2><?php echo gettext('Your Setup scripts are not protected.'); ?></h2>
					<?php
					if (zpFunctions::hasPrimaryScripts()) {
						echo gettext('The Setup environment is not totally secure, you should protect the scripts to thwart hackers. <a href="' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=protect_setup&XSRFToken=' . getXSRFToken('protect_setup') . '">Protect the scripts</a>. ');
					}
					?>
				</div>
				<?php
				return 2;
			}
			return 1;
		}
		return 0;
	}

	/**
	 * Print the nav tabs for the admin section. We determine which tab should be highlighted
	 * from the $_GET['page']. If none is set, we default to "home".
	 *
	 * @author Todd Papaioannou (lucky@luckyspin.org)
	 * @since  1.0.0
	 */
	function printTabs($tab = NULL) {
		global $zenphoto_tabs, $_zp_admin_tab;
		$_SESSION['navigation_tabs'] = $zenphoto_tabs; //	mostly for refresh_metadata which cannot load plugins
		?>
		<div class="navigation">
			<ul>
				<?php
				$bottom = count($zenphoto_tabs);
				$loc = -1;
				foreach ($zenphoto_tabs as $key => $atab) {
					if (isset($atab['link'])) {
						if (array_key_exists('alert', $zenphoto_tabs[$key])) {
							$alert = $zenphoto_tabs[$key]['alert'];
						} else {
							$alert = array();
						}
						$class = '';
						$activeTab = $_zp_admin_tab == $key;
						if ($activeTab) {
							$class = ' class="active"';
						} else {
							if (!empty($alert))
								$class = ' class="alert"';
						}
						$subtabs = $zenphoto_tabs[$key]['subtabs'];
						$hasSubtabs = !empty($subtabs) && is_array($subtabs);
						$loc++;
						?>
						<li<?php if ($hasSubtabs) echo ' class="has-sub"'; ?>>
							<a href="<?php echo html_encode($atab['link']); ?>" <?php echo $class; ?>><?php echo html_encode(ucfirst($atab['text'])); ?></a>
							<?php
							if ($hasSubtabs) { // don't print <ul> if there is nothing
								if (!(isset($atab['ordered']) && $atab['ordered'])) {
									ksort($subtabs, SORT_NATURAL);
								}
								$high = count($subtabs);
								if ($high > 2) {
									$_top = $loc - floor(0.5 * $high);
									$_bottom = $loc + ceil(0.5 * $high);
									if ($_top >= 0 && $_bottom <= $bottom) { //	fits within the bounds
										$position = floor(0.5 * $high);
									} else {
										if ($_bottom > $bottom) { //	overflows at bottom
											if ($loc - $high + 1 > 0) { //	won't overflow at top
												$position = $high - 1; //	align to the bottom
											} else {
												$position = $loc; //	align to top and let the bottom overflow
											}
										} else { //	overflows at the top only
											$position = $loc; //	align to the top
										}
									}
								} else {
									$position = 0; //	align to self
								}
								?>
								<ul<?php if ($position) echo ' style="margin-top: -' . ($position * 32) . 'px;"' ?>>
									<?php
									if ($activeTab) {
										if (isset($_GET['tab'])) {
											$subtab = sanitize($_GET['tab']);
										} else {
											if (isset($zenphoto_tabs[$key]['default'])) {
												$subtab = $zenphoto_tabs[$key]['default'];
											} else {
												$subtab = NULL;
											}
										}
									}

									foreach ($subtabs as $subkey => $link) {
										$subclass = '';
										if ($activeTab) {
											preg_match('~tab=(.*?)(&|$)~', $link, $matches);
											if (isset($matches[1])) {
												if ($matches[1] == $subtab) {
													$subclass = 'active ';
												}
											}
										}
										switch ($link[0]) {
											case'/':
												$link = WEBPATH . $link;
												break;
											case '?':
												$request = parse_url(getRequestURI());
												if (isset($request['query'])) {
													$link .= '&' . $request['query'];
												}
												$link = $request['path'] . $link;
												break;
											default:
												$link = WEBPATH . '/' . ZENFOLDER . '/' . $link;
												break;
										}

										if (in_array($subkey, $alert)) {
											$subclass = ' class="' . $subclass . 'alert"';
										} else if ($subclass) {
											$subclass = ' class="' . trim($subclass) . '"';
										}
										?>
										<li>
											<a href="<?php echo html_encode($link); ?>"<?php echo $subclass; ?>><?php echo html_encode(ucfirst($subkey)); ?></a>
										</li>
										<?php
									} // foreach end
									?>
								</ul>
								<?php
							} // subtabs
							?>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<br class="clearall"><!-- needed so the nav sits correctly -->
		<?php
	}

	function getTabName($page, $tab) {
		global $zenphoto_tabs;
		foreach ($zenphoto_tabs[$page]['subtabs'] as $text => $link) {
			if (strpos($link, 'tab=' . $tab) !== false) {
				return $text;
			}
		}
		return str_replace('_', ' ', $tab);
	}

	function getTabLink($page, $tab) {
		global $zenphoto_tabs;
		foreach ($zenphoto_tabs[$page]['subtabs'] as $text => $link) {
			if (strpos($link, 'tab=' . $tab) !== false) {
				return $link;
			}
		}
		return false;
	}

	function getCurrentTab() {
		global $zenphoto_tabs, $_zp_admin_tab, $_zp_admin_subtab;
		$tabs = @$zenphoto_tabs[$_zp_admin_tab]['subtabs'];
		if (!is_array($tabs))
			return $_zp_admin_subtab;
		$current = $_zp_admin_subtab;
		if (isset($_GET['tab'])) {
			$test = sanitize($_GET['tab']);
			foreach ($tabs as $link) {
				preg_match('~tab=(.*?)(&|$)~', $link, $matches);
				if (isset($matches[1])) {
					if ($test == $matches[1]) {
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

	function setAlbumSubtabs($album) {
		global $zenphoto_tabs;
		$albumlink = '?page=edit&album=' . urlencode($album->name);
		$default = NULL;
		$zenphoto_tabs['edit']['subtabs'] = array();
		$subrights = $album->subRights();
		if (isset($_GET['tab'])) {
			$tab = sanitize($_GET['tab']);
		} else {
			$tab = false;
		}

		if (!$album->isDynamic()) {
			if ($c = $album->getNumImages()) {
				if ($subrights & (MANAGED_OBJECT_RIGHTS_UPLOAD || MANAGED_OBJECT_RIGHTS_EDIT)) {
					$zenphoto_tabs['edit']['subtabs'] = array_merge(
									array(gettext('Images') => 'admin-edit.php' . $albumlink . '&tab=imageinfo'), $zenphoto_tabs['edit']['subtabs']
					);
					$default = 'imageinfo';
				}
				if ($c > 1 && $subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
					$zenphoto_tabs['edit']['subtabs'] = array_merge(
									array(gettext('Image order') => 'admin-albumsort.php' . $albumlink . '&tab=sort'), $zenphoto_tabs['edit']['subtabs']
					);
				}
			}
			$subalbums = $album->getAlbums();
			if (!empty($subalbums)) {
				$add[gettext('Subalbums')] = 'admin-edit.php' . $albumlink . '&tab=subalbuminfo';
				if ($tab == 'subalbuminfo' && count($subalbums) > 1 || $tab == 'massedit') {
					$add[gettext('Mass-edit subalbums')] = "/" . ZENFOLDER . '/admin-edit.php' . $albumlink . '&tab=massedit';
				}
				$zenphoto_tabs['edit']['subtabs'] = array_merge($add, $zenphoto_tabs['edit']['subtabs']);
				$default = 'subalbuminfo';
			}
		}
		if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge(
							array(gettext('Album') => 'admin-edit.php' . $albumlink . '&tab=albuminfo'), $zenphoto_tabs['edit']['subtabs']
			);
			$default = 'albuminfo';
		}
		$extra = zp_apply_filter('album_page_subtabs', array(), $album);
		if (!empty($extra)) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge($zenphoto_tabs['edit']['subtabs'], $extra);
		}

		$zenphoto_tabs['edit']['default'] = $default;
		if (isset($_GET['tab'])) {
			return sanitize($_GET['tab']);
		}
		if ($tab) {
			return $tab;
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
						$list[$album->getFileName()] = $album->getTitle();
					}
				} else {
					$list[$album->getFileName()] = $album->getTitle();
					genAlbumList($list, $album, $rights); /* generate for subalbums */
				}
			}
		}
	}

	/**
	 *
	 * @param string $text
	 * @param string $key
	 * @param int $min
	 * @param int $max
	 * @param int $v current value
	 */
	function putSlider($text, $postkey, $min, $max, $v) {
		?>
		<span id="slider_display-<?php echo $postkey; ?>" class="nowrap">
			<?php echo $text; ?>
			<input type="text" id="<?php echo $postkey; ?>" name="<?php echo $postkey; ?>" size="2" value="<?php echo $v; ?>" onchange="$('#slider-<?php echo $postkey; ?>').slider('value', $('#<?php echo $postkey; ?>').val());"/>
		</span>

		<script type="text/javascript">
			// <!-- <![CDATA[
			$(function () {
				$("#slider-<?php echo $postkey; ?>").slider({
					startValue: <?php echo (int) $v; ?>,
					value: <?php echo (int) $v; ?>,
					min: <?php echo (int) $min; ?>,
					max: <?php echo (int) $max; ?>,
					slide: function (event, ui) {
						$("#<?php echo $postkey; ?>").val(ui.value);
					}
				});
				$("#<?php echo $postkey; ?>").val($("#slider-<?php echo $postkey; ?>").slider("value"));
			});
			// ]]> -->
		</script>
		<div id="slider-<?php echo $postkey; ?>"></div>
		<br />
		<?php
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
	 *    OPTION_TYPE_TEXTBOX:							A textbox
	 *    OPTION_TYPE_PASSWORD:							A passowrd textbox
	 *    OPTION_TYPE_CLEARTEXT:						A textbox, but no sanitization on save
	 * 		OPTION_TYPE_NUMBER:								A small textbox for numbers. NOTE: the default allows only positive integers
	 * 																				(i.e. 'step' defaults to 1.) If you need	other values supply a "limits" element, e.g:
	 * 																				'limits' => array('min' => -25, 'max'=> 25, 'step' => 0.5)
	 * 		OPTION_TYPE_SLIDER								A number but with a slider that changes the value
	 *    OPTION_TYPE_CHECKBOX:							A checkbox
	 *    OPTION_TYPE_CUSTOM:								Handled by $optionHandler->handleOption()
	 *    OPTION_TYPE_TEXTAREA:							A textarea
	 *    OPTION_TYPE_RICHTEXT:							A textarea with WYSIWYG editor attached
	 *    OPTION_TYPE_RADIO:								Radio buttons (button names are in the 'buttons' index of the supported options array)
	 *    OPTION_TYPE_SELECTOR:							Selector (selection list is in the 'selections' index of the supported options array
	 * 																				null_selection contains the text for the empty selection. If not present there
	 * 																				will be no empty selection)
	 *    OPTION_TYPE_CHECKBOX_ARRAY:				Checkbox array (checkbox list is in the 'checkboxes' index of the supported options array.)
	 * 		OPTION_TYPE_CHECKBOX_ARRAYLIST:		Same as OPTION_TYPE_CHECKBOX_ARRAY but the set values will be stored as an array
	 *    OPTION_TYPE_CHECKBOX_UL:					Checkbox UL (checkbox list is in the 'checkboxes' index of the supported options array.)
	 * 		OPTION_TYPE_CHECKBOX_ULLIST:			Same as OPTION_TYPE_CHECKBOX_UL but the set values will be stored as an array
	 *    OPTION_TYPE_COLOR_PICKER:					Color picker
	 *    OPTION_TYPE_NOTE:									Places a note in the options area. The note will span all three columns
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
	define('OPTION_TYPE_RICHTEXT', 12);
	define('OPTION_TYPE_NUMBER', 13);
	define('OPTION_TYPE_SLIDER', 14);
	define('OPTION_TYPE_ORDERED_SELECTOR', 15);
	define('OPTION_TYPE_CHECKBOX_ARRAYLIST', 16);
	define('OPTION_TYPE_CHECKBOX_ULLIST', 17);

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
				<tr style="display:none">
					<td>
						<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX; ?>save-<?php echo $whom; ?>" value="<?php echo $extension; ?>" />
					</td>
				</tr>
				<?php
			}


			foreach ($options as $option) {
				$descending = NULL;
				$row = $supportedOptions[$option];
				if (false !== $i = stripos($option, chr(0))) {
					$option = substr($option, 0, $i);
				}

				$type = $row['type'];
				$desc = $row['desc'];
				$key = @$row['key'];
				$postkey = postIndexEncode($key);
				$optionID = $whom . '_' . $key;
				if (isset($row['multilingual'])) {
					$multilingual = $row['multilingual'];
				} else {
					$multilingual = $type == OPTION_TYPE_TEXTAREA;
				}
				if ($type == OPTION_TYPE_RICHTEXT || isset($row['texteditor']) && $row['texteditor']) {
					$editor = 'texteditor';
				} else {
					$editor = '';
				}
				if (isset($row['disabled']) && $row['disabled']) {
					$disabled = ' disabled="disabled"';
				} else {
					$disabled = '';
				}
				if (isset($row['deprecated']) && $option) {
					$deprecated = $row['deprecated'];
					if (!$deprecated) {
						$deprecatedd = gettext('Deprecated.');
					}
					$option = '<div class="warningbox">' . $option . '<br /><em>' . $deprecated . '</em></div>';
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
						<td class="option_name"><?php if ($option) echo $indent . $option; ?></td>
						<?php
					}
					switch ($type) {
						case OPTION_TYPE_NOTE:
							?>
							<td colspan="100%"><?php echo $desc; ?></td>
							<?php
							break;
						case OPTION_TYPE_NUMBER:
						case OPTION_TYPE_CLEARTEXT:
						case OPTION_TYPE_PASSWORD:
						case OPTION_TYPE_TEXTBOX:
						case OPTION_TYPE_TEXTAREA:
						case OPTION_TYPE_RICHTEXT;
							$clear = '';
							$wide = 'width: 100%';
							switch ($type) {
								case OPTION_TYPE_CLEARTEXT:
									$clear = 'clear';
									$multilingual = false;
								default:
									$inputtype = 'text';
									break;
								case OPTION_TYPE_PASSWORD:
									$inputtype = 'password" autocomplete="off';
									$multilingual = false;
									break;
								case OPTION_TYPE_NUMBER:
									$multilingual = false;
									$clear = 'numeric';
									if (!is_numeric($v))
										$v = 0;
									$wide = 'width: 100px';
									if (isset($row['limits'])) {
										$inputtype = 'number';
										if (isset($row['limits']['min']))
											$inputtype .= '" min="' . $row['limits']['min'];
										if (isset($row['limits']['max']))
											$inputtype .= '" max="' . $row['limits']['max'];
										if (isset($row['limits']['step'])) {
											$inputtype .= '" step="' . $row['limits']['step'];
										} else {
											$inputtype .= '" step="1';
										}
									} else {
										$inputtype = 'number" min="0" step="1';
									}
									break;
							}
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . $clear . 'text-' . $postkey; ?>" value="1" />
								<?php
								if ($multilingual) {
									print_language_string_list($v, $postkey, $type, NULL, $editor, '100%');
								} else {
									if ($type == OPTION_TYPE_TEXTAREA || $type == OPTION_TYPE_RICHTEXT) {
										$v = get_language_string($v); // just in case....
										?>
										<textarea id="__<?php echo $key; ?>"<?php if ($type == OPTION_TYPE_RICHTEXT) echo ' class="texteditor"'; ?> name="<?php echo $postkey; ?>" cols="<?php echo TEXTAREA_COLUMNS; ?>"	 rows="6"<?php echo $disabled; ?>><?php echo html_encode($v); ?></textarea>
										<?php
									} else {
										?>
										<input type="<?php echo $inputtype; ?>" id="__<?php echo $key; ?>" name="<?php echo $postkey; ?>" style="<?php echo $wide; ?>" value="<?php echo html_encode($v); ?>"<?php echo $disabled; ?> />
										<?php
									}
								}
								?>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX:
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . $postkey; ?>" value="1" />
								<input type="checkbox" id="__<?php echo $key; ?>" name="<?php echo $postkey; ?>" value="1" <?php checked('1', $v); ?><?php echo $disabled; ?> />
							</td>
							<?php
							break;
						case OPTION_TYPE_CUSTOM:
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'custom-' . $postkey; ?>" value="0" />
								<?php $optionHandler->handleOption($key, $v); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_RADIO:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'radio-' . $postkey; ?>" value="1"<?php echo $disabled; ?> />
								<?php generateRadiobuttonsFromArray($v, $row['buttons'], $postkey, $key, $behind, 'checkboxlabel', $disabled); ?>
							</td>
							<?php
							break;
						case OPTION_TYPE_SELECTOR:
							$descending = false;
						case OPTION_TYPE_ORDERED_SELECTOR:
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'selector-' . $postkey ?>" value="1" />
								<select id="__<?php echo $key; ?>" name="<?php echo $postkey; ?>"<?php echo $disabled; ?> >
									<?php
									if (array_key_exists('null_selection', $row)) {
										?>
										<option value=""<?php if (empty($v)) echo ' selected="selected"'; ?> style="background-color:LightGray;"><?php echo $row['null_selection']; ?></option>
										<?php
									}
									$list = array();
									foreach ($row['selections'] as $rowkey => $rowvalue) {
										$list[$rowkey] = $rowvalue;
									}
									generateListFromArray(array($v), $list, $descending, true);
									?>
								</select>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_ARRAY:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td class="option_value">
								<div class="checkbox_array">
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
										<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . postIndexEncode($checkbox); ?>" value="1" />
										<label class="checkboxlabel">
											<?php if ($behind) echo($display); ?>
											<input type="checkbox" id="__<?php echo $checkbox; ?>" name="<?php echo postIndexEncode($checkbox); ?>" value="1"<?php checked('1', $v); ?><?php echo $disabled; ?> />
											<?php if (!$behind) echo($display); ?>
										</label>
										<?php
									}
									?>
								</div>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_ARRAYLIST:
							$behind = (isset($row['behind']) && $row['behind']);
							?>
							<td class="option_value">
								<div class="checkbox_array">
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'array-' . $postkey; ?>" value="1" />
									<?php
									$setOptions = getSerializedArray($v);
									foreach ($row['checkboxes'] as $display => $checkbox) {

										$display = str_replace(' ', '&nbsp;', $display);
										?>
										<label class="checkboxlabel">
											<?php if ($behind) echo($display); ?>
											<input type="checkbox" id="__<?php echo $checkbox; ?>" name="<?php echo $postkey; ?>[]" value="<?php echo $checkbox; ?>"<?php if (in_array($checkbox, $setOptions)) echo ' checked="checked"'; ?><?php echo $disabled; ?> />
											<?php if (!$behind) echo($display); ?>
										</label>
										<?php
									}
									?>
								</div>
							</td>
							<?php
							break;
						case OPTION_TYPE_CHECKBOX_UL:
							?>
							<td class="option_value">
								<?php
								$all = true;
								$cvarray = array();
								foreach ($row['checkboxes'] as $display => $checkbox) {
									?>
									<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'chkbox-' . postIndexEncode($checkbox); ?>" value="1" />
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
						case OPTION_TYPE_CHECKBOX_ULLIST:
							?>
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'array-' . $postkey; ?>" value="1" />
								<?php
								$setOptions = getSerializedArray($v);
								$all = empty($setOptions);
								?>
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'array-' . $postkey; ?>" value="1" />
								<ul class="customchecklist">
									<?php
									foreach ($row['checkboxes'] as $display => $checkbox) {

										$display = str_replace(' ', '&nbsp;', $display);
										?>
										<li>
											<label class="displayinline">
												<input type="checkbox" id="__<?php echo $checkbox; ?>" class="all_<?php echo $key; ?>" name="<?php echo $postkey; ?>[]" value="<?php echo $checkbox; ?>"<?php if (in_array($checkbox, $setOptions)) echo ' checked="checked"'; ?><?php echo $disabled; ?> />
												<?php echo($display); ?>
											</label>
										</li>
										<?php
									}
									?>
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
							<td class="option_value">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'text-' . $postkey; ?>" value="1" />
								<script type="text/javascript">
									// <!-- <![CDATA[
									window.addEventListener('load', function () {
										$('#__<?php echo $key; ?>').spectrum({
											preferredFormat: "hex",
											color: "$('#__<?php echo $key; ?>').val()"
										});
									}, false);
									// ]]> -->
								</script>
								<input type="text" id="__<?php echo $key; ?>" name="<?php echo $postkey; ?>"	value="<?php echo $v; ?>" />
							</td>
							<?php
							break;
						case OPTION_TYPE_SLIDER:
							$min = $row['min'];
							$max = $row['max'];
							?>
							<td class="option_value" style="margin:0; padding:0">
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX . 'slider-' . $postkey; ?>" value="1" />
								<?php putSlider('', $postkey, $min, $max, $v); ?>
							</td>
							<?php
							break;
					}
					if ($desc && $type != OPTION_TYPE_NOTE) {
						?>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo $desc; ?>
								</div>
							</span>
						</td>
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
		foreach ($_POST as $posted => $value) {
			if (preg_match('/^' . CUSTOM_OPTION_PREFIX . '/', $posted)) { // custom option!
				$key = $postkey = substr($posted, strpos($posted, '-') + 1);
				$l = strlen($postkey);
				if (!($l % 2) && preg_match('/[0-9a-f]{' . strlen($postkey) . '}/i', $postkey)) {
					$key = postIndexDecode($postkey);
				}
				$switch = explode('-', $posted);
				$switch = substr($switch[0], strlen(CUSTOM_OPTION_PREFIX));
				switch ($switch) {
					case 'text':
						$value = process_language_string_save($postkey, 1);
						break;
					case'numerictext':
						if (isset($_POST[$postkey])) {
							if (is_numeric($_POST[$postkey])) {
								$value = $_POST[$postkey];
							} else {
								$value = 0;
							}
						}
						break;
					case 'cleartext':
						if (isset($_POST[$postkey])) {
							$value = sanitize($_POST[$postkey], 0);
						} else {
							$value = '';
						}
						break;
					case 'chkbox':
						$value = (int) isset($_POST[$postkey]);
						break;
					case 'array':
						if (isset($_POST[$postkey])) {
							$value = serialize($_POST[$postkey]);
						} else {
							$value = serialize(array());
						}
						break;
					case 'save':
						$customHandlers[] = array('whom' => $key, 'extension' => sanitize_path($_POST[$posted]));
						continue 2;
					default:
						if (isset($_POST[$postkey])) {
							$value = sanitize($_POST[$postkey], 1);
						} else if (isset($_POST[$key])) {
							$value = sanitize($_POST[$key], 1);
						} else {
							$value = NULL;
						}
						if (is_string($value)) {
							break;
						}
						continue 2;
				}
				if ($themename) {
					setThemeOption($key, $value, $themealbum, $themename);
				} else {
					setOption($key, $value);
				}
			} else {
				if (strpos($posted, 'show-') === 0) {
					if ($value)
						$returntab .= '&' . $posted;
				}
			}
		}
		foreach ($customHandlers as $custom) {
			if ($extension = $custom['extension'] . '.php' != '.php') {
				if ($extension = getPlugin($extension)) {
					require_once($extension);
				}
				if (class_exists($custom['whom'])) {
					$whom = new $custom['whom']();
					$returntab = $whom->handleOptionSave($themename, $themealbum) . $returntab;
				}
			}
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

		$knownThemes = getSerializedArray(getOptionFromDB('known_themes'));
		$knownThemes[$theme] = $theme;
		setOption('known_themes', serialize($knownThemes));
	}

	/**
	 * Encodes for use as a $_POST index
	 *
	 * @param string $str
	 */
	function postIndexEncode($str) {
		return bin2hex($str);
	}

	/**
	 * Decodes encoded $_POST index
	 *
	 * @param string $str
	 * @return string
	 */
	function postIndexDecode($str) {
		return hex2bin($str);
	}

	/**
	 * Prints radio buttons from an array
	 *
	 * @param string $currentvalue The current selected value
	 * @param string $list the array of the list items form is localtext => buttonvalue
	 * @param string $option the name of the option for the input field name
	 * @param string $radioid the base ID value for the radio buttons
	 * @param bool $behind set true to have the "text" before the button
	 */
	function generateRadiobuttonsFromArray($currentvalue, $list, $option, $radioid, $behind = false, $class = 'checkboxlabel', $disabled = NULL) {
		foreach ($list as $text => $value) {
			$checked = "";
			if ($value == $currentvalue) {
				$checked = ' checked="checked" '; //the checked() function uses quotes the other way round...
			}
			?>
			<label<?php if ($class) echo ' class="' . $class . '"'; ?>>
				<?php if ($behind) echo $text; ?>
				<input type="radio" name="<?php echo $option; ?>" id="__<?php echo $radioid . '-' . $value; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?><?php echo $disabled; ?> />
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
	function generateUnorderedListFromArray($currentValue, $list, $prefix, $alterrights, $sort, $localize, $class = NULL, $extra = NULL, $postArray = false) {
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
			$listitem = $prefix . postIndexEncode($item);
			if ($localize) {
				$display = $key;
			} else {
				$display = $item;
			}
			if ($postArray) {
				$name = $prefix . 'list[]';
			} else {
				$name = $listitem;
			}
			if (isset($cv[$item])) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			?>
			<li id="<?php echo $listitem; ?>_element">
				<label class="displayinline">
					<input id="<?php echo $listitem; ?>"<?php echo $class; ?> name="<?php echo $name; ?>" type="checkbox"<?php echo $checked; ?> value="<?php echo $item; ?>" <?php echo $alterrights; ?> />
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

	function addTags($tags, $obj) {
		$mytags = array_unique(array_merge($tags, $obj->getTags(false)));
		$obj->setTags($mytags);
	}

	/**
	 * Creates an unordered checklist of the tags
	 *
	 * @param object $that Object for which to get the tags
	 * @param string $postit prefix to prepend for posting
	 * @param bool $showCounts set to true to get tag count displayed
	 * @param string $tagsort set true to sort alphabetically
	 * @param bool $addnew true enables adding tags, ==2 for "additive" tags
	 * @param bool $resizeable set true to allow the box to be resized
	 * @param string $class class of the selections
	 */
	function tagSelector($that, $postit, $showCounts = false, $tagsort = 'alpha', $addnew = true, $resizeable = false, $class = 'checkTagsAuto') {
		global $_zp_admin_ordered_taglist, $_zp_admin_LC_taglist;
		if ((int) $addnew <= 1 && is_null($_zp_admin_ordered_taglist)) {
			switch ($tagsort) {
				case 'language':
					$order = '`language` DESC,`name`';
					break;
				case 'recent':
					$order = '`id` DESC';
					break;
				default:
					$order = '`name`';
					break;
			}
			$languages = $counts = array();
			$sql = "SELECT DISTINCT tags.name, tags.language, tags.id, (SELECT COUNT(*) FROM " . prefix('obj_to_tag') . " as object WHERE object.tagid = tags.id) AS count FROM " . prefix('tags') . " as tags ORDER BY $order";
			$tagresult = query($sql);
			if ($tagresult) {
				while ($tag = db_fetch_assoc($tagresult)) {
					$counts[$tag['name']] = $tag['count'];
					$languages[$tag['name']] = $tag['language'];
				}
				db_free_result($tagresult);
			}
			if ($tagsort == 'mostused') {
				arsort($counts, SORT_NUMERIC);
			}

			$_zp_admin_LC_taglist = $them = array();
			foreach ($counts as $tag => $count) {
				$them[mb_strtolower($tag)] = $tag;
				$_zp_admin_LC_taglist[$tag] = $tag;
			}
			$flags = array('' => WEBPATH . '/' . ZENFOLDER . '/images/placeholder.png');
			foreach (generateLanguageList('all') as $dirname) {
				$flags[$dirname] = getLanguageFlag($dirname);
			}

			$_zp_admin_ordered_taglist = array($them, $counts, $languages, $flags);
		} else {
			list($them, $counts, $languages, $flags) = $_zp_admin_ordered_taglist;
			if ((int) $addnew == 2) {
				$them = $counts = array();
			}
		}

		if (is_null($that)) {
			$tags = array();
		} else {
			$tags = $that->getTags(false);
		}

		if (count($tags) > 0) {
			$them = array_diff_key($them, $tags);
		}
		$total = count($tags) + count($them);
		if ($resizeable) {
			if ($total > 0) {
				$tagclass = 'resizeable_tagchecklist';
			} else {
				$tagclass = 'resizeable_empty_tagchecklist';
			}
			if (is_bool($resizeable)) {
				$tagclass .= ' resizeable_tagchecklist_fixed_width';
			}
			?>
			<script>
				$(function () {
					$("#resizable_<?php echo $postit; ?>").resizable({
						minHeight: 120,
						resize: function (event, ui) {
							$(this).css("width", '');
							$('#list_<?php echo $postit; ?>').height($('#resizable_<?php echo $postit; ?>').height());
						}
					})
				});</script>
			<?php
		} else {
			$tagclass = 'tagchecklist';
		}
		if ($addnew) {
			?>
			<span class="new_tag displayinline" >
				<a onclick="addNewTag('<?php echo $postit; ?>');" title="<?php echo gettext('add tag'); ?>">
					<?php echo PLUS_ICON; ?>
				</a>
				<span class="tagSuggestContainer">
					<input class="tagsuggest <?php echo $class; ?> " type="text" value="" name="newtag_<?php echo $postit; ?>" id="newtag_<?php echo $postit; ?>" />
				</span>
			</span>
			<?php
			if ((int) $addnew == 2) {
				?>
				<input type="hidden" value="1" name="additive_<?php echo $postit; ?>" id="additive_<?php echo $postit; ?>" />
				<?php
			}
		}
		?>
		<div id="resizable_<?php echo $postit; ?>" class="tag_div">
			<ul id="list_<?php echo $postit; ?>" class="<?php echo $tagclass; ?>">
				<?php
				if (count($tags) > 0) {
					foreach ($tags as $tag => $item) {
						$listitem = $postit . postIndexEncode($item);
						?>
						<li id="<?php echo $tag; ?>_element">
							<label class="displayinline">
								<input id="<?php echo $listitem; ?>" class="<?php echo $class; ?>" name="<?php echo 'tag_list_' . $postit . '[]'; ?>" type="checkbox" checked="checked" value="<?php echo html_encode($item); ?>" />
								<img src="<?php echo $flags[$languages[$item]]; ?>" height="10" width="16" />
								<?php
								if ($showCounts) {
									echo html_encode($item) . ' [' . $counts[$item] . ']';
								} else {
									echo html_encode($item);
								}
								?>
							</label>
						</li>
						<?php
					}
					?>
					<li><hr /></li>
					<?php
				}
				foreach ($them as $tagLC => $item) {
					$listitem = $postit . postIndexEncode($item);
					?>
					<li id="<?php echo $listitem; ?>_element">
						<label class="displayinline">
							<input id="<?php echo $listitem; ?>" class="<?php echo $class; ?>" name="<?php echo 'tag_list_' . $postit . '[]'; ?>" type="checkbox" value="<?php echo html_encode($item); ?>" />
							<img src="<?php echo $flags[$languages[$item]]; ?>" height="10" width="16" />
							<?php
							if ($showCounts) {
								echo html_encode($item) . ' [' . $counts[$item] . ']';
							} else {
								echo html_encode($item);
							}
							?>
						</label>
					</li>
					<?php
				}
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
		global $_zp_sortby, $_zp_gallery, $mcr_albumlist, $_zp_albumthumb_selector, $_zp_current_admin_obj;
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
		}
		if (isset($_GET['subpage'])) {
			?>
			<input type="hidden" name="subpage" value="<?php echo html_encode(sanitize($_GET['subpage'])); ?>" />
			<?php
		}
		?>
		<input type="hidden" name="<?php echo $prefix; ?>folder" value="<?php echo $album->name; ?>" />
		<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
		<input type="hidden" name="password_enabled<?php echo $suffix; ?>" id="password_enabled<?php echo $suffix; ?>" value="0" />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<?php
				$parent = dirname($album->name);
				if ($parent == '/' || $parent == '.' || empty($parent)) {
					$parent = '';
				} else {
					$parent = '&amp;album=' . $parent . '&tab=subalbuminfo';
				}
				?>
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<?php echo BACK_ARROW_BLUE; ?>
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<?php echo CHECKMARK_GREEN; ?>
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="$('.deletemsg').hide();" >
					<?php echo CROSS_MARK_RED; ?>
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>

				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', false);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', true);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
						</button>
						<?php
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFileName())); ?>">
						<?php echo BULLSEYE_BLUE; ?>
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>

			</span>
			<br class="clearall">
			<br />
			<?php
		}
		$bglevels = array('#fff', '#f8f8f8', '#efefef', '#e8e8e8', '#dfdfdf', '#d8d8d8', '#cfcfcf', '#c8c8c8');
		?>
		<div class="formlayout">
			<div class="floatleft">
				<div>
					<table class="width100percent">
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
								<span class="floatright">
									<?php echo linkPickerIcon($album, 'pick_link'); ?>
								</span>
							</td>
							<td class="middlecolumn">
								<?php echo linkPickerItem($album, 'pick_link'); ?>
							</td>
						</tr>
						<?php
						if ($album->isDynamic()) {
							?>
							<tr>
								<td align="left" valign="top" width="150"><em><?php echo get_class($album); ?></em></td>
								<td class="noinput">
									<?php
									switch ($album->isDynamic()) {
										case 'alb':
											echo html_encode(str_replace(',', ', ', urldecode($album->getSearchParams())));
											break;
										case'fav':
											echo html_encode($album->owner);
											if ($album->instance) {
												echo ' [' . html_encode($album->instance) . ']';
											}
											break;
									}
									?>
								</td>
							</tr>

							<?php
						}
						?>
						<tr>
							<td class="leftcolumn">
								<?php echo gettext("Album Description"); ?>
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
									<a onclick="toggle_passwords('<?php echo $suffix; ?>', true);">
										<?php echo gettext("Album password"); ?>
									</a>
								</td>
								<td class="middlecolumn">
									<?php
									$x = $album->getPassword();
									if (empty($x)) {
										?>
										<a onclick="toggle_passwords('<?php echo $suffix; ?>', true);">
											<?php echo LOCK_OPEN; ?>
											<?php echo gettext('No album password is currently set. Click to set one.'); ?>
										</a>
										<?php
									} else {
										$x = '          ';
										?>
										<a onclick="resetPass('<?php echo $suffix; ?>');" title="<?php echo addslashes(gettext('clear password')); ?>">
											<?php echo LOCK; ?>
											<?php echo gettext('An album password is currently set. Click to clear or change the password.'); ?>
										</a>
										<?php
									}
									?>
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<a onclick="toggle_passwords('<?php echo $suffix; ?>', false);">
										<?php echo gettext("Album guest user"); ?>
									</a>
								</td>
								<td>
									<input type="text"
												 class="passignore<?php echo $suffix; ?> ignoredirty" autocomplete="off"
												 size="<?php echo TEXT_INPUT_SIZE; ?>"
												 onkeydown="passwordClear('<?php echo $suffix; ?>');"
												 id="user_name<?php echo $suffix; ?>" name="user<?php echo $suffix; ?>"
												 value="<?php echo $album->getUser(); ?>" />
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<p>
										<span id="strength<?php echo $suffix; ?>"><?php echo gettext("Album password"); ?></span>
										<br />
										<span id="match<?php echo $suffix; ?>" class="password_field_<?php echo $suffix; ?>">
											<?php echo gettext("repeat password"); ?>
										</span>
									</p>
									<p>
										<?php echo gettext("Password hint"); ?>
									</p>
								</td>
								<td>
									<p>
										<input type="password"
													 class="passignore<?php echo $suffix; ?> ignoredirty" autocomplete="off"
													 id="pass<?php echo $suffix; ?>" name="pass<?php echo $suffix; ?>"
													 onkeydown="passwordClear('<?php echo $suffix; ?>');"
													 onkeyup="passwordStrength('<?php echo $suffix; ?>');"
													 value="<?php echo $x; ?>" />
										<label>
											<input type="checkbox"
														 name="disclose_password<?php echo $suffix; ?>"
														 id="disclose_password<?php echo $suffix; ?>"
														 onclick="passwordClear('<?php echo $suffix; ?>');
																 togglePassword('<?php echo $suffix; ?>');" />
														 <?php echo addslashes(gettext('Show')); ?>
										</label>

										<br />
										<span class="password_field_<?php echo $suffix; ?>">
											<input type="password"
														 class="passignore<?php echo $suffix; ?> ignoredirty" autocomplete="off"
														 id="pass_r<?php echo $suffix; ?>" name="pass_r<?php echo $suffix; ?>" disabled="disabled"
														 onkeydown="passwordClear('<?php echo $suffix; ?>');"
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

						$sort = $_zp_sortby;
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
							<td class="leftcolumn"><?php echo gettext("Sort subalbums by"); ?> </td>
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
											echo ' checked="checked"';
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
									<?php echo gettext('custom fields') ?>
									<span class="tagSuggestContainer">
										<input id="customalbumsort<?php echo $suffix; ?>" class="customalbumsort" name="<?php echo $prefix; ?>customalbumsort" type="text" value="<?php echo html_encode($cvt); ?>" />
									</span>
								</span>
							</td>
						</tr>

						<tr>
							<td class="leftcolumn"><?php echo gettext("Sort images by"); ?> </td>
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
									<?php echo gettext('custom fields') ?>
									<span class="tagSuggestContainer">
										<input id="customimagesort<?php echo $suffix; ?>" class="customimagesort" name="<?php echo $prefix; ?>customimagesort" type="text" value="<?php echo html_encode($cvt); ?>" />
									</span>
								</span>
							</td>
						</tr>

						<?php
						if (is_null($album->getParent())) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Album theme"); ?> </td>
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
								<td class="leftcolumn"><?php echo gettext("Album watermarks"); ?> </td>
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
								<td class="leftcolumn"><?php echo gettext("Thumbnail"); ?> </td>
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
										$subalbums = $album->getAlbums(0);
										foreach ($subalbums as $folder) {
											$newalbum = newAlbum($folder);
											if ($images = $_zp_gallery->getSecondLevelThumbs()) {
												$images = $newalbum->getImages(0);
												foreach ($images as $filename) {
													if (is_array($filename)) {
														$imagelist[] = $filename;
													} else {
														$imagelist[] = '/' . $folder . '/' . $filename;
													}
												}
											}
											if (empty($images)) {
												$t = $newalbum->getAlbumThumbImage();
												if (strtolower(get_class($t)) !== 'transientimage' && $t->exists) {
													$imagelist[] = '/' . $t->getAlbumName() . '/' . $t->filename;
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
													$image = newImage($imagename);
													$imagename = '/' . $imagename['folder'] . '/' . $imagename['filename'];
													$filename = basename($imagename);
												} else {
													$albumname = trim(dirname($imagename), '/');
													if (empty($albumname) || $albumname == '.') {
														$thumbalbum = $album;
													} else {
														$thumbalbum = newAlbum($albumname);
													}
													$filename = basename($imagename);
													$image = newImage($thumbalbum, $filename);
												}
												$selected = ($imagename == $thumb);
												if (Gallery::imageObjectClass($filename) == 'Image' || !is_null($image->objectsThumb)) {
													echo "\n<option";
													if ($_zp_gallery->getThumbSelectImages()) {
														echo " class=\"thumboption\"";
														echo " style=\"background-image: url(" . html_encode(pathurlencode(getAdminThumb($image, 'medium'))) . "); background-repeat: no-repeat;\"";
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
						echo $custom = zp_apply_filter('edit_album_custom_data', '', $album, $prefix);
						?>
					</table>
				</div>
			</div>
			<div class="floatleft">
				<div class="rightcolumn" valign="top">
					<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
					<div class="box-edit">
						<label class="checkboxlabel">
							<input type="checkbox"
										 name="<?php echo $prefix; ?>Published"
										 value="1" <?php if ($album->getShow()) echo ' checked="checked"'; ?>
										 onclick="$('#<?php echo $prefix; ?>publishdate').val('');
												 $('#<?php echo $prefix; ?>expirationdate').val('');
												 $('#<?php echo $prefix; ?>publishdate').css('color', 'black');
												 $('.<?php echo $prefix; ?>expire').html('');"
										 />
										 <?php echo gettext("Published"); ?>
						</label>
						<?php
						if (extensionEnabled('comment_form')) {
							?>
							<label class="checkboxlabel">
								<input type="checkbox" name="<?php echo $prefix . 'allowcomments'; ?>" value="1" <?php
								if ($album->getCommentsAllowed()) {
									echo ' checked="checked"';
								}
								?> />
											 <?php echo gettext("Allow Comments"); ?>
							</label>
							<?php
						}
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
							$(function () {
								$("#<?php echo $prefix; ?>publishdate,#<?php echo $prefix; ?>expirationdate").datepicker({
									dateFormat: 'yy-mm-dd',
									showOn: 'button',
									buttonImage: '../zp-core/images/calendar.png',
									buttonText: '<?php echo addslashes(gettext("calendar")); ?>',
									buttonImageOnly: true
								});
								$('#<?php echo $prefix; ?>publishdate').change(function () {
									var today = new Date();
									var pub = $('#<?php echo $prefix; ?>publishdate').datepicker('getDate');
									if (pub.getTime() > today.getTime()) {
										$("<?php echo $prefix; ?>Published").removeAttr('checked');
										$('#<?php echo $prefix; ?>publishdate').css('color', 'blue');
									} else {
										$("<?php echo $prefix; ?>Published").attr('checked', 'checked');
										$('#<?php echo $prefix; ?>publishdate').css('color', 'black');
									}
								});
								$('#<?php echo $prefix; ?>expirationdate').change(function () {
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
						<br class="clearall">
						<p>
							<label for="<?php echo $prefix; ?>publishdate"><?php echo gettext('Publish date'); ?> <small>(YYYY-MM-DD)</small></label>
							<br /><input value="<?php echo $publishdate; ?>" type="text" size="20" maxlength="30" name="publishdate-<?php echo $prefix; ?>" id="<?php echo $prefix; ?>publishdate" <?php if ($publishdate > date('Y-m-d H:i:s')) echo 'style="color:blue"'; ?> />
							<br /><label for="<?php echo $prefix; ?>expirationdate"><?php echo gettext('Expiration date'); ?> <small>(YYYY-MM-DD)</small></label>
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
							<?php
							if ($isPrimaryAlbum) {
								?>
											 disabled="disabled"
											 <?php
										 } else {
											 ?>
											 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');
													 deleteConfirm('Delete-<?php echo $prefix; ?>', '<?php echo $prefix; ?>', deleteAlbum1);"
											 <?php
										 }
										 ?> />
										 <?php echo gettext("Delete album"); ?>
						</label>

						<br class="clearall">
						<div class="deletemsg" id="deletemsg<?php echo $prefix; ?>" class="resetHide"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
							<?php echo gettext('Album will be deleted when changes are applied.'); ?>

							<p class="buttons">
								<a	onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');">
									<?php echo CROSS_MARK_RED; ?>
									<?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>movecopydiv" class="resetHide" style="padding-top: .5em; padding-left: .5em; display: none;">
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

							<p class="buttons">
								<a onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');">
									<?php echo CROSS_MARK_RED; ?>
									<?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>renamediv" class="resetHide" style="padding-top: .5em; padding-left: .5em; display: none;">
							<?php echo gettext("to:"); ?>
							<input name="a-<?php echo $prefix; ?>renameto" type="text" value="<?php echo basename($album->name); ?>"/>

							<p class="buttons">
								<a onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');">
									<?php echo CROSS_MARK_RED; ?>
									<?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<div class="clearall" ></div>
						<?php
						echo zp_apply_filter('edit_album_utilities', '', $album, $prefix);
						printAlbumButtons($album);
						?>
						<span class="clearall" ></span>
					</div>
				</div>
			</div>
		</div>

		<br class="clearall">
		<br />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<?php echo BACK_ARROW_BLUE; ?>
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<?php echo CHECKMARK_GREEN; ?>
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="$('.deletemsg').hide();">
					<?php echo CROSS_MARK_RED; ?>
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', false);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<?php if (!$album->isDynamic()) { ?>
							<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="newAlbumJS('<?php echo pathurlencode($album->name); ?>', true);">
								<img src="images/folder.png" alt="" />
								<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
							</button>
							<?php
						}
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getFileName())); ?>">
						<?php echo BULLSEYE_BLUE; ?>
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>
			</span>
			<br class="clearall">
			<?php
		}
		?>
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
			<div class="button buttons tooltip" title="<?php echo addslashes(gettext("Clears the album’s cached images.")); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=clear_cache&amp;album=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('clear_cache'); ?>">
					<?php echo WASTEBASKET; ?>
					<?php echo gettext('Clear album image cache'); ?></a>
				<br class="clearall">
			</div>
			<div class="button buttons tooltip" title="<?php echo gettext("Resets album’s hit counters."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=reset_hitcounters&amp;album=' . html_encode($album->name) . '&amp;albumid=' . $album->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter'); ?>">
					<?php echo RECYCLE_ICON; ?>
					<?php echo gettext('Reset album hit counters'); ?></a>
				<br class="clearall">
			</div>
			<?php
		}
		if ($imagcount || (!$album->isDynamic() && $album->getNumAlbums())) {
			?>
			<div class="button buttons tooltip" title="<?php echo gettext("Refreshes the metadata for the album."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-refresh-metadata.php?album=' . html_encode($album->name) . '&amp;return=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh'); ?>">
					<?php echo CIRCLED_BLUE_STAR; ?>
					<?php echo gettext('Refresh album metadata'); ?>
				</a>
				<br class="clearall">
			</div>
			<?php
		}
	}

	function printAlbumLegend() {
		?>
		<ul class="iconlegend-l">
			<li>
				<img src="images/folder_picture.png" alt="" />
				<?php echo gettext("Albums"); ?>
			</li>
			<li>
				<img src="images/pictures.png" alt="" />
				<?php echo gettext("Images"); ?>
			</li>
			<li>
				<img src="images/folder_picture_dn.png" alt="" />
				<?php echo gettext("Albums (dynamic)"); ?>
			</li>
			<li>
				<img src="images/pictures_dn.png" alt="I" />
				<?php echo gettext("Images (dynamic)"); ?>
			</li>
		</ul>
		<ul class="iconlegend">
			<?php
			if (GALLERY_SECURITY == 'public') {
				?>
				<li>
					<?php echo LOCK; ?>
					<?php echo LOCK_OPEN; ?>
					<?php echo gettext("has/does not have password"); ?></li>
				<?php
			}
			?>
			<li>
				<?php echo CLIPBOARD . ' ' . gettext("pick source"); ?>
			</li>
			<li>
				<?php echo CHECKMARK_GREEN; ?>
				<?php echo EXCLAMATION_RED; ?>
				<?php echo CLOCKFACE . '&nbsp;'; ?>
				<?php echo gettext("published/not published/scheduled for publishing"); ?>
			</li>
			<li>
				<?php echo BULLSEYE_GREEN; ?>
				<?php echo BULLSEYE_RED; ?>
				<?php echo gettext("comments on/off"); ?>
			</li>
			<li>
				<?php echo BULLSEYE_BLUE; ?>
				<?php echo gettext("view the album"); ?>
			</li>
			<li>
				<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
				<?php echo gettext("refresh metadata"); ?>
			</li>
			<?php
			if (extensionEnabled('hitcounter')) {
				?>
				<li>
					<?php echo RECYCLE_ICON; ?>
					<?php echo gettext("reset hit counters"); ?>
				</li>
				<?php
			}
			?>
			<li>
				<?php echo WASTEBASKET; ?>
				<?php echo gettext("delete"); ?>
			</li>
		</ul>
		<?php
	}

	/**
	 * puts out a row in the edit album table
	 *
	 * @param object $album is the album being emitted
	 * @param bool $show_thumb set to false to show thumb standin image rather than album thumb
	 * @param object $owner the parent album (or NULL for gallery)
	 * @param bool $toodeep set true if nesting level is too deep
	 *
	 * */
	function printAlbumEditRow($album, $show_thumb, $owner, $toodeep) {
		global $_zp_current_admin_obj;
		$enableEdit = $album->subRights() & MANAGED_OBJECT_RIGHTS_EDIT;
		if (is_object($owner)) {
			$owner = $owner->name;
		}
		if ($toodeep) {
			$handle = DRAG_HANDLE_ALERT;
		} else {
			$handle = DRAG_HANDLE;
		}
		?>
		<div class="page-list_row">
			<div class="page-list_handle">
				<?php echo $handle; ?>
			</div>
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
					<img src="<?php echo html_encode(pathurlencode($thumb)); ?>" width="<?php echo ADMIN_THUMB_SMALL; ?>" height="<?php echo ADMIN_THUMB_SMALL; ?>" alt="" title="album thumb" />
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
					echo html_encode(getBare($album->getTitle()));
					if ($enableEdit) {
						?>
					</a>
					<?php
				}
				?>
			</div>
			<?php
			if ($album->isDynamic()) {
				$imgi = '<img src="images/pictures_dn.png" alt="' . gettext('images') . '" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture_dn.png" alt="' . gettext('albums') . '" title="' . gettext('albums') . '" />';
			} else {
				$imgi = '<img src="images/pictures.png" alt="' . gettext('images') . '" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture.png" alt="' . gettext('albums') . '" title="' . gettext('albums') . '" />';
			}
			$ci = count($album->getImages());
			$si = sprintf('%1$s <span>(%2$u)</span>', $imgi, $ci);
			if ($ci > 0 && !$album->isDynamic()) {
				$si = preg_replace('~ title=".*?"~', '', $si);
				$si = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=imageinfo" title="' . gettext('Images') . '">' . $si . '</a>';
			}
			$ca = $album->getNumAlbums();
			$sa = sprintf('%1$s <span>(%2$u)</span>', $imga, $ca);
			if ($ca > 0 && !$album->isDynamic()) {
				$sa = preg_replace('~ title=".*?"~', '', $sa);
				$sa = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=subalbuminfo" title="' . gettext('Subalbum List') . '">' . $sa . '</a>';
			}
			?>
			<div class="page-list_iconwrapper">
				<div class="page-list_icon">
					<?php
					$pwd = $album->getPassword();
					if (empty($pwd)) {
						?>
						<a title="<?php echo gettext('un-protected'); ?>">
							<?php echo LOCK_OPEN; ?>
						</a>
						<?php
					} else {
						?>
						<a title="<?php echo gettext('password protected'); ?>">
							<?php echo LOCK; ?>
						</a>
						<?php
					}
					?>
				</div>
				<div class="page-list_icon">
					<?php
					echo linkPickerIcon($album);
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
							<?php echo CHECKMARK_GREEN; ?>
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
							if ($album->getPublishDate() > date('Y-m-d H:i:s')) {
								?>
								<?php echo CLOCKFACE; ?>
								<?php
							} else {
								?>
								<?php echo EXCLAMATION_RED; ?>
								<?php
							}
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
							<a href="?action=comments&amp;commentson=0&amp;album=<?php echo html_encode($album->getFileName()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Disable comments'); ?>">
								<?php
							}
							?>
							<?php echo BULLSEYE_GREEN; ?>
							<?php
							if ($enableEdit) {
								?>
							</a>
							<?php
						}
					} else {
						if ($enableEdit) {
							?>
							<a href="?action=comments&amp;commentson=1&amp;album=<?php echo html_encode($album->getFileName()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Enable comments'); ?>">
								<?php
							}
							?>
							<?php echo BULLSEYE_RED; ?>
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
						<?php echo BULLSEYE_BLUE; ?>
					</a>
				</div>
				<div class="page-list_icon">
					<?php
					if ($album->isDynamic() || !$enableEdit) {
						?>
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/placeholder.png"  style="border: 0px;" />
						<?php
					} else {
						?>
						<a class="warn" href="admin-refresh-metadata.php?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh') ?>" title="<?php echo sprintf(gettext('Refresh metadata for the album %s'), $album->name); ?>">
							<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
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
						if (!$enableEdit) {
							?>
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/placeholder.png"  style="border: 0px;" />
							<?php
						} else {
							?>
							<a class="reset" href="?action=reset_hitcounters&amp;albumid=<?php echo $album->getID(); ?>&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;subalbum=true&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo sprintf(gettext('Reset hit counters for album %s'), $album->name); ?>">
								<?php echo RECYCLE_ICON; ?>
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
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/placeholder.png"  style="border: 0px;" />
						<?php
					} else {
						?>
						<a class="delete" href="javascript:confirmDeleteAlbum('?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($album->name)); ?>&amp;return=<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>');" title="<?php echo sprintf(gettext("Delete the album %s"), js_encode($album->name)); ?>">
							<?php echo WASTEBASKET; ?>
						</a>
						<?php
					}
					?>
				</div>
				<?php
				if ($enableEdit) {
					?>
					<div class="page-list_icon">
						<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $album->getFileName(); ?>"<?php if ($supress) echo ' disabled="disabled"'; ?> />
					</div>
					<?php
				}
				?>
			</div>
			<?php
			if ($sa || $si) {
				?>
				<div class="page-list_extra page-list_right">
					<?php echo $si; ?>
				</div>
				<div class="page-list_extra page-list_right">
					<?php echo $sa; ?>
				</div>
				<?php
			}
			?>
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
	function processAlbumEdit($index, &$album, &$redirectto) {
		$redirectto = NULL; // no redirection required
		if ($index == 0) {
			$prefix = $suffix = '';
		} else {
			$prefix = "$index-";
			$suffix = "_$index";
		}
		$notify = '';
		$album->setTitle(process_language_string_save($prefix . 'albumtitle', 2));
		$album->setDesc(process_language_string_save($prefix . 'albumdesc', EDITOR_SANITIZE_LEVEL));

		if (isset($_POST['tag_list_tags_' . $prefix])) {
			$tags = sanitize($_POST['tag_list_tags_' . $prefix]);
		} else {
			$tags = array();
		}
		$tags = array_unique($tags);
		$album->setTags($tags);
		if (isset($_POST[$prefix . 'thumb']))
			$album->setThumb(sanitize($_POST[$prefix . 'thumb']));
		$album->setCommentsAllowed(isset($_POST[$prefix . 'allowcomments']));
		$sorttype = strtolower(sanitize($_POST[$prefix . 'sortby'], 3));
		if ($sorttype == 'custom') {
			$sorttype = unquote(strtolower(sanitize($_POST[$prefix . 'customimagesort'], 3)));
		}
		$album->setSortType($sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection(false, 'image');
		} else {
			if (empty($sorttype)) {
				$direction = false;
			} else {
				$direction = isset($_POST[$prefix . 'image_sortdirection']);
			}
			$album->setSortDirection($direction, 'image');
		}
		$sorttype = strtolower(sanitize($_POST[$prefix . 'subalbumsortby'], 3));
		if ($sorttype == 'custom')
			$sorttype = strtolower(sanitize($_POST[$prefix . 'customalbumsort'], 3));
		$album->setSortType($sorttype, 'album');
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection(false, 'album');
		} else {
			$album->setSortDirection(isset($_POST[$prefix . 'album_sortdirection']), 'album');
		}
		if (isset($_POST['reset_hitcounter' . $prefix])) {
			$album->set('hitcounter', 0);
		}
		if (isset($_POST[$prefix . 'reset_rating'])) {
			$album->set('total_value', 0);
			$album->set('total_votes', 0);
			$album->set('used_ips', 0);
		}
		$pubdate = $album->setPublishDate(sanitize($_POST['publishdate-' . $prefix]));
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
		$album->setShow(isset($_POST[$prefix . 'Published']));

		zp_apply_filter('save_album_custom_data', NULL, $prefix, $album);
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
				if ($suffix = $album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($dest, -4) != '.' . $suffix) {
						$dest .= '.' . suffix;
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
				if ($suffix = $album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($renameto, -4) != '.' . $suffix) {
						$renameto .= '.' . $suffix;
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

	function printImagePagination($album, $image, $singleimage, $allimagecount, $totalimages, $pagenum, $totalpages, $filter) {
		if ($singleimage) {
			$images = $album->getImages(0);
			if ($count = count($images) > 1) {
				?>
				<span class="floatright">
					<?php
					$i = array_search($image->filename, $images);
					if ($i > 0) {
						?>
						<a href="?page=edit&tab=imageinfo&album=<?php echo pathurlencode($image->album->name); ?>&singleimage=<?php echo html_encode($images[$i - 1]); ?>"><?php echo gettext('prev image'); ?></a>
						<?php
					}
					if (array_key_exists($i + 1, $images)) {
						if ($i > 0)
							echo ' | ';
						?>
						<a href="?page=edit&tab=imageinfo&album=<?php echo pathurlencode($image->album->name); ?>&singleimage=<?php echo html_encode($images[$i + 1]); ?>"><?php echo gettext('next image'); ?></a>
						<?php
					}
					?>
				</span>
				<?php
			}
		} else {
			if ($allimagecount != $totalimages) { // need pagination links
				adminPageNav($pagenum, $totalpages, 'admin-edit.php', '?page=edit&amp;album=' . html_encode(pathurlencode($album->name)), '&amp;tab=imageinfo&amp;filter=' . $filter);
			}
		}
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

	/**
	 * Generates an editable list of language strings
	 *
	 * @param string $dbstring either a serialized languag string array or a single string
	 * @param string $name the prefix for the label, id, and name tags
	 * @param bool $textbox set to true for a textbox rather than a text field
	 * @param string $locale optional locale of the translation desired
	 * @param string $edit optional class
	 * @param int $wide column size. true or false for the standard or short sizes. Or pass a column size
	 * @param int $rows set to the number of rows to show.
	 * @param deprecated xrows %ulclass parameter was deprecated promoting rows to that positon. This allows for migration
	 */
	function print_language_string_list($dbstring, $name, $textbox = false, $locale = NULL, $edit = '', $wide = TEXT_INPUT_SIZE, $rows = 6, $xrows = 6) {
		global $_zp_active_languages, $_zp_current_locale, $_lsInstance;
		if (!is_numeric($rows)) { //	deprecation of $ulclass parameter
			if (class_exists('deprecated_functions')) {
				deprecated_functions::notify(gettext("The \$ulclass parameter is deprecated. You should remove '$rows' from your print_language_string_list() function calls."));
			}
			$rows = $xrows;
		}
		$dbstring = zpFunctions::unTagURLs($dbstring);
		if (!empty($edit))
			$edit = ' class="' . $edit . '"';
		if (is_null($locale)) {
			$locale = getUserLocale();
		}
		$strings = getSerializedArray($dbstring);
		if (count($strings) == 1) {
			$keys = array_keys($strings);
			$lang = array_shift($keys);
			if (!is_string($lang)) {
				$strings = array($locale => array_shift($strings));
			}
		}
		$activelang = generateLanguageList();
		$allLang = array_flip(generateLanguageList('all'));
		$multi = getOption('multi_lingual');
		foreach ($strings as $lang => $v) {
			if (!array_key_exists($lang, $activelang)) {
				$activelang[$allLang[$lang]] = $lang;
			}
		}

		if ($textbox) {
			$class = 'box';
			if (strpos($wide, '%') === false) {
				$width = ' cols="' . $wide . '"';
			} else {
				$width = ' style="width:' . ((int) $wide - (int) ($multi && !empty($activelang))) . '%;"';
			}
		} else {
			$class = '';
			if (strpos($wide, '%') === false) {
				$width = ' size="' . $wide . '"';
			} else {
				$width = ' style="width:' . ((int) $wide) . '%;"';
			}
		}

		if ($multi && !empty($activelang)) {
			// put the language list in perferred order
			$preferred = array();
			if ($_zp_current_locale) {
				$preferred[] = $_zp_current_locale;
			}
			foreach (parseHttpAcceptLanguage() as $lang) {
				$preferred[] = str_replace('-', '_', $lang['fullcode']);
			}
			$preferred = array_unique($preferred);
			$emptylang = array();

			foreach ($preferred as $lang) {
				foreach ($activelang as $key => $active) {
					if ($active == $lang) {
						$emptylang[$active] = $key;
						unset($activelang[$key]);
						continue 2;
					}
				}
				if (strlen($lang) == 2) { //	"wild card language"
					foreach ($activelang as $key => $active) {
						if (substr($active, 0, 2) == $lang) {
							$emptylang[$active] = $key;
						}
					}
				}
			}

			foreach ($activelang as $key => $active) {
				$emptylang[$active] = $key;
			}

			$tabSelected = ' selected';
			$editHidden = '';
			?>
			<div id="ls_<?php echo ++$_lsInstance; ?>">
				<select class="languageSelector ignoredirty" onchange="lsclick(this.value,<?php echo $_lsInstance; ?>);">
					<?php
					foreach ($emptylang as $key => $lang) {
						$flag = getLanguageFlag($key);
						?>
						<option value="<?php echo $key; ?>" data-image="<?php echo $flag; ?>" alt="<?php echo $key; ?>"<?php if ($key == $locale) echo ' selected="selected"' ?>>
							<?php echo $lang; ?>
						</option>
						<?php
					}
					?>
				</select>

				<?php
				foreach ($emptylang as $key => $lang) {
					if (isset($strings[$key])) {
						$string = $strings[$key];
					} else {
						$string = '';
					}
					?>

					<div id="lb<?php echo $key . '-' . $_lsInstance ?>" class="lbx-<?php echo $_lsInstance ?>"<?php echo $editHidden; ?>>
						<?php
						if ($textbox) {
							?>
							<textarea name="<?php echo $name . '_' . $key ?>"<?php echo $edit . $width; ?>	rows="<?php echo $rows ?>">
								<?php echo html_encode($string); ?>
							</textarea>
							<?php
						} else {
							?>
							<input name="<?php echo $name . '_' . $key ?>"<?php echo $edit . $width; ?> type="text" value="<?php echo html_encode($string); ?>"  />
							<?php
						}
						?>
					</div>
					<?php
					$editHidden = ' style="display:none;"';
				}
				?>
			</div>
			<?php
		} else {
			if (empty($locale))
				$locale = 'en_US';
			if (isset($strings[$locale])) {
				$dbstring = $strings[$locale];
				unset($strings[$locale]);
			} else {
				$dbstring = array_shift($strings);
			}
			if ($textbox) {
				echo '<textarea name="' . $name . '_' . $locale . '"' . $edit . $width . '	rows="' . $rows . '">' . html_encode($dbstring) . '</textarea>';
			} else {
				echo '<input name="' . $name . '_' . $locale . '"' . $edit . ' type="text" value="' . html_encode($dbstring) . '"' . $width . ' />';
			}
			foreach ($strings as $key => $dbstring) {
				if (!empty($dbstring)) {
					?>
					<input type="hidden" name="<?php echo $name . '_' . $key; ?>" value="<?php echo html_encode($dbstring); ?>" />
					<?php
				}
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
		$languages = generateLanguageList('all');
		$l = strlen($name) + 1;
		$strings = array();
		foreach ($_POST as $key => $value) {
			if (preg_match('/^' . $name . '_[a-z]{2}_[A-Z]{2}$/', $key)) {
				$key = substr($key, $l);
				if (in_array($key, $languages)) {
					$value = sanitize($value, $sanitize_level);
					if (!empty($value))
						$strings[$key] = $value;
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
			case 1:
				if (!getOption('multi_lingual')) {
					return array_shift($strings);
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
			setOption('tagsort', $tagsort);
		} else {
			$tagsort = getOption('tagsort');
		}
		return $tagsort;
	}

	/**
	 * Outputs a file for zip download
	 * @param string $zipname name of the zip file
	 * @param string $file the file to zip
	 */
	function putZip($zipname, $file) {
		//we are dealing with file system items, convert the names
		$fileFS = internalToFilesystem($file);
		if (class_exists('ZipArchive')) {
			$zipfileFS = tempnam('', 'zip');
			$zip = new ZipArchive;
			$zip->open($zipfileFS, ZipArchive::CREATE);
			$zip->addFile($fileFS, basename($fileFS));
			$zip->close();
			ob_get_clean();
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/zip");
			header("Content-Disposition: attachment; filename=" . basename($zipname) . ";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . filesize($zipfileFS));
			readfile($zipfileFS);
			// remove zip file from temp path
			unlink($zipfileFS);
		} else {
			include_once(SERVERPATH . '/' . ZENFOLDER . '/lib-zipStream.php');
			$zip = new ZipStream(internalToFilesystem(basename($zipname)));
			$zip->add_file_from_path(basename($fileFS), $fileFS);
			$zip->finish();
		}
	}

	/**
	 * Unzips an image archive
	 *
	 * @param file $file the archive
	 * @param string $dir where the images go
	 */
	function unzip($file, $dir) {
		if (class_exists('ZipArchive')) {
			$zip = new ZipArchive;
			$zip->open($file);
			$zip->extractTo($dir);
			$zip->close();
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
	 * Extracts and returns a 'statement' from a PHP script so that it may be 'evaled'
	 *
	 * @param string $target the assignment variable to match on
	 * @param string $str the PHP script
	 * @return string
	 */
	function isolate($target, $str) {
		if (preg_match('|' . preg_quote($target) . '\s*?=*(.+?);[ \f\v\t]*[\n\r]|s', $str, $matches)) {
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
			return !protectedTheme($theme);
		} else {
			return false;
		}
	}

	function protectedTheme($theme, $distributed = false) {
		$theme_description = array();
		$desc = SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/theme_description.php';
		if (file_exists($desc)) {
			require($desc);
			$protected = isset($theme_description['distribution']) && $theme_description['distribution'];
			if ($protected && $distributed)
				$protected = $theme_description['distribution'] == 'ZenPhoto20';
			return $protected;
		}
		return false;
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
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Directory “%s” already exists!'), basename($target));
		}

		// If source dir is missing, exit too
		if (!is_dir($source)) {
			return gettext('Cannot create new theme.') . ' ' . sprintf(gettext('Cannot find theme directory “%s” to copy!'), basename($source));
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
				return sprintf(gettext("An error occurred while copying files. Please delete manually the new theme directory “%s” and retry or copy files manually."), basename($target));
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
				// Theme definition file
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
						@chmod($fullname, 0777);
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
		if (empty($_GET)) {
			$q = '';
		} else {
			$q = '?' . http_build_query($_GET);
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
			?>
			<input type="checkbox" name="<?php echo $id; ?>-rightsenabled" class="user-<?php echo $id; ?>" value="1" checked="checked" <?php echo $alterrights; ?> style="display:none" />
			<?php
			foreach ($rightslist as $rightselement => $right) {
				if (!empty($right['set'])) {
					if ($right['display'] && (($right['set'] != gettext('Pages') && $right['set'] != gettext('News')) || extensionEnabled('zenpage'))) {
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
							<input type="checkbox" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" class="user-<?php echo $id; ?>" value="<?php echo $right['value']; ?>"<?php
							if ($rights & $right['value'])
								echo ' checked="checked"';
							echo $alterrights;
							?> /> <?php echo $right['name']; ?>
						</label>
						<?php
					} else {
						if ($rights & $right['value']) {
							?>
							<input type="hidden" name="<?php echo $id . '-' . $rightselement; ?>" id="<?php echo $rightselement . '-' . $id; ?>" value="<?php echo $right['value']; ?>" />
							<?php
						}
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
	$full = $userobj->getObjects();

	$legend = '';
	$icon_edit = PENCIL_ICON;
	$icon_view = EXCLAMATION_RED;
	$icon_upload = ARROW_UP_GREEN;
	$icon_upload_disabled = ARROW_UP_GRAY;

	switch ($type) {
		case 'albums':
			if ($rights & (MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS)) {
				$cv = $objlist;
				$alterrights = ' disabled="disabled"';
			} else {
				$cv = $extra = $extra2 = array();
				if (!empty($flag)) {
					$legend .= '* ' . gettext('Primary album') . ' ';
				}
				$legend .= $icon_edit . ' ' . gettext('edit') . ' ';
				if ($rights & UPLOAD_RIGHTS)
					$legend .= $icon_upload . ' ' . gettext('upload') . ' ';
				if (!($rights & VIEW_UNPUBLISHED_RIGHTS))
					$legend .= $icon_view . ' ' . gettext('view unpublished');
				foreach ($full as $item) {
					if ($item['type'] == 'album') {
						if (in_array($item['data'], $flag)) {
							$note = '*';
						} else {
							$note = '';
						}
						$cv[$item['name'] . $note] = $item['data'];
						$extra[$item['data']][] = array('name' => 'name', 'value' => $item['name'], 'display' => '', 'checked' => 0);
						$extra[$item['data']][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_EDIT);
						if (($rights & UPLOAD_RIGHTS)) {
							if (hasDynamicAlbumSuffix($item['data']) && !is_dir(ALBUM_FOLDER_SERVERPATH . $item['data'])) {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
							} else {
								$extra[$item['data']][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_UPLOAD);
							}
						}
						if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
							$extra[$item['data']][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_VIEW);
						}
					}
				}
				$rest = array_diff($objlist, $cv);
				foreach ($rest as $unmanaged) {
					$extra2[$unmanaged][] = array('name' => 'name', 'value' => $unmanaged, 'display' => '', 'checked' => 0);
					$extra2[$unmanaged][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => 1);
					if (($rights & UPLOAD_RIGHTS)) {
						if (hasDynamicAlbumSuffix($unmanaged) && !is_dir(ALBUM_FOLDER_SERVERPATH . $unmanaged)) {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload_disabled, 'checked' => 0, 'disable' => true);
						} else {
							$extra2[$unmanaged][] = array('name' => 'upload', 'value' => MANAGED_OBJECT_RIGHTS_UPLOAD, 'display' => $icon_upload, 'checked' => 1);
						}
					}
					if (!($rights & VIEW_UNPUBLISHED_RIGHTS)) {
						$extra2[$unmanaged][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => 1);
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
				$cv = $extra = $extra2 = array();
				$rest = array_diff($objlist, $cv);
				$legend = $icon_edit . ' ' . gettext('edit') . ' ' . $icon_view . ' ' . gettext('view unpublished');

				foreach ($full as $item) {
					if ($item['type'] == 'news') {
						$cv[$item['name']] = $item['data'];
						$extra[$item['data']][] = array('name' => 'name', 'value' => $item['name'], 'display' => '', 'checked' => 0);
						$extra[$item['data']][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_EDIT);
						$extra[$item['data']][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_VIEW);
					}
				}
				$rest = array_diff($objlist, $cv);
				foreach ($rest as $unmanaged) {
					$extra2[$unmanaged][] = array('name' => 'name', 'value' => $unmanaged, 'display' => '', 'checked' => 0);
					$extra2[$unmanaged][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => 1);
					$extra2[$unmanaged][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => 1);
				}
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
				$cv = $extra = $extra2 = array();
				$rest = array_diff($objlist, $cv);
				$legend = $icon_edit . ' ' . gettext('edit') . ' ' . $icon_view . ' ' . gettext('view unpublished');
				foreach ($full as $item) {
					if ($item['type'] == 'pages') {
						$cv[$item['name']] = $item['data'];
						$extra[$item['data']][] = array('name' => 'name', 'value' => $item['name'], 'display' => '', 'checked' => 0);
						$extra[$item['data']][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_EDIT);
						$extra[$item['data']][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => $item['edit'] & MANAGED_OBJECT_RIGHTS_VIEW);
					}
				}
				$rest = array_diff($objlist, $cv);
				foreach ($rest as $unmanaged) {
					$extra2[$unmanaged][] = array('name' => 'name', 'value' => $unmanaged, 'display' => '', 'checked' => 0);
					$extra2[$unmanaged][] = array('name' => 'edit', 'value' => MANAGED_OBJECT_RIGHTS_EDIT, 'display' => $icon_edit, 'checked' => 1);
					$extra2[$unmanaged][] = array('name' => 'view', 'value' => MANAGED_OBJECT_RIGHTS_VIEW, 'display' => $icon_view, 'checked' => 1);
				}
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
			<a onclick="$('#<?php echo $prefix ?>').toggle();" title="<?php echo html_encode($hint); ?>" ><?php echo $text . $itemcount; ?></a>
		</h2>
		<div id="<?php echo $prefix ?>" style="display:none;">
			<ul class="albumchecklist">
				<?php
				generateUnorderedListFromArray($cv, $cv, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra);
				generateUnorderedListFromArray(array(), $rest, $prefix, $alterrights, true, true, 'user-' . $prefix_id, $extra2);
				?>
			</ul>
			<span class="floatright"><?php echo $legend; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<br class="clearall">
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
		if (substr($key, 0, $l_a) == $prefix_a) { //albums
			$key = sanitize(substr($key, $l_a));
			if (preg_match('/(.*)(_edit|_view|_upload|_name)$/', $key, $matches)) {
				$key = postIndexDecode($matches[1]);
				if (array_key_exists($key, $albums)) {
					switch ($matches[2]) {
						case '_edit':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_MEMBER;
							break;
						case '_upload':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_MEMBER;
							break;
						case '_view':
							$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_VIEW | MANAGED_OBJECT_MEMBER;
							break;
						case '_name':
							$albums[$key]['name'] = $value;
							break;
					}
				}
			} else if ($value) {
				$key = postIndexDecode($key);
				$albums[$key] = array('data' => $key, 'name' => '', 'type' => 'album', 'edit' => MANAGED_OBJECT_MEMBER);
			}
		}
		if (substr($key, 0, $l_p) == $prefix_p) { //pages
			$key = sanitize(substr($key, $l_p));
			if (preg_match('/(.*)(_edit|_view|_name)$/', $key, $matches)) {
				$key = postIndexDecode($matches[1]);
				if (array_key_exists($key, $pages)) {
					switch ($matches[2]) {
						case '_edit':
							$pages[$key]['edit'] = $pages[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_MEMBER;
							break;
						case '_view':
							$pages[$key]['edit'] = $pages[$key]['edit'] | MANAGED_OBJECT_RIGHTS_VIEW | MANAGED_OBJECT_MEMBER;
							break;
						case '_name':
							$pages[$key]['name'] = $value;
							break;
					}
				}
			} else if ($value) {
				$key = postIndexDecode($key);
				$pages[$key] = array('data' => $key, 'type' => 'pages', 'edit' => MANAGED_OBJECT_MEMBER);
			}
		}

		if (substr($key, 0, $l_n) == $prefix_n) { //news
			$key = sanitize(substr($key, $l_n));
			if (preg_match('/(.*)(_edit|_view|_name)$/', $key, $matches)) {
				$key = postIndexDecode($matches[1]);
				if (array_key_exists($key, $news)) {
					switch ($matches[2]) {
						case '_edit':
							$news[$key]['edit'] = $news[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_MEMBER;
							break;
						case '_view':
							$news[$key]['edit'] = $news[$key]['edit'] | MANAGED_OBJECT_RIGHTS_VIEW | MANAGED_OBJECT_MEMBER;
							break;
						case '_name':
							$news[$key]['name'] = $value;
							break;
					}
				}
			} else if ($value) {
				$key = postIndexDecode($key);
				$news[$key] = array('data' => $key, 'type' => 'news', 'edit' => MANAGED_OBJECT_MEMBER);
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
				$sql = 'UPDATE ' . prefix('albums') . ' SET `sort_order`=' . db_quote(sprintf('%03u', $sortorder)) . ' WHERE `id`=' . $item;
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
					$album->setSortOrder(sprintf('%03u', $sortorder));
					$album->save();
				}
			}
		}
		return true;
	}
	return false;
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
		printAlbumEditRow($albumobj, $show_thumb, $owner, $rslt);
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
function printEditDropdown($subtab, $nestinglevels, $nesting, $query = NULL) {
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
				echo '<option ' . $selected . ' value="admin-edit.php' . $link . $nestinglevel . $query . '">';
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
	$customInfo = $colorboxBookmark = array();

	foreach ($checkarray as $key => $value) {
		if (is_array($value)) {
			$checkarray[$key] = $value['name'];
			switch ($action = $value['action']) {
				case 'mass_customTextarea_data':
					$data['size'] = -1;
				case 'mass_customText_data':
					$customInfo[$value['name']] = $value;
					$action = 'mass_' . $value['name'] . '_data';
					break;
			}
			$colorboxBookmark[$value['name']] = $action;
		}
	}
	if (!empty($colorboxBookmark)) {
		?>
		<script type="text/javascript">
			//<!-- <![CDATA[
			function checkFor(obj) {
			var sel = obj.options[obj.selectedIndex].value;
							var mark;
							switch (sel) {
		<?php
		foreach ($colorboxBookmark as $key => $mark) {
			?>
				case '<?php echo $key; ?>':
								mark = '<?php echo $mark; ?>';
								break;
			<?php
		}
		?>
			default:
							mark = false;
							break;
			}
			if (mark) {
			$.colorbox({
			href: '#' + mark,
							inline: true,
							open: true,
							close: '<?php echo gettext("ok"); ?>'
			});
			}
			}
			// ]]> -->
		</script>
		<?php
	}
	?>
	<span style="float:right">
		<select class="ignoredirty" name="checkallaction" id="checkallaction" size="1" onchange="checkFor(this);" >
			<?php generateListFromArray(array('noaction'), $checkarray, false, true); ?>
		</select>
		<?php
		if ($checkAll) {
			?>
			<br />
			<?php
			echo gettext("Check All");
			?>
			<input class="ignoredirty" type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
			<?php
		}
		?>
	</span>
	<?php
	foreach ($customInfo as $key => $data) {
		?>
		<div id="mass_<?php echo $key; ?>" style="display:none;
				 ">
			<div id="mass_<?php echo $key; ?>_data">
				<?php
				printf('Value for %s:', $data['desc']);
				if ($data['action'] == 'mass_customText_data') {
					if (isset($data['size']) && $data['size'] >= 0) {
						$size = max(5, min($data['size'], 200));
					} else {
						$size = 100;
					}
					?>
					<input type="text" name="<?php echo $key; ?>" size="<?php echo $size; ?>" value="">
					<?php
				} else {
					?>
					<textarea name="<?php echo $key; ?>" cols="<?php echo TEXTAREA_COLUMNS; ?>"	style="width: 320px" rows="6"></textarea>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	if (in_array('mass_tags_data', $colorboxBookmark)) {
		?>
		<div id="mass_tags" style="display:none;">
			<div id="mass_tags_data">
				<?php
				tagSelector(NULL, 'mass_tags_', false, getTagOrder(), true, false, 'checkTagsAuto ignoredirty');
				?>
			</div>
		</div>
		<?php
	}
	if (in_array('mass_cats_data', $colorboxBookmark)) {
		?>
		<div id="mass_cats" style="display:none;">
			<div id="mass_cats_data">
				<?php
				echo gettext('New categorys:');
				?>
				<ul>
					<?php
					printNestedItemsList('cats-checkboxlist', '', 'all', 'ignoredirty');
					?>
				</ul>
			</div>
		</div>
		<?php
	}
	if (in_array('mass_owner_data', $colorboxBookmark)) {
		?>
		<div id="mass_owner" style="display:none;">
			<div id="mass_owner_data">
				<?php
				echo gettext('New owner:');
				?>
				<ul>
					<select class="ignoredirty" id="massownermenu" name="massownerselect" onchange="">
						<?php
						echo admin_album_list(NULL);
						?>
					</select>
				</ul>
			</div>
		</div>
		<?php
	}
	if (in_array('mass_movecopy_data', $colorboxBookmark)) {
		global $mcr_albumlist, $album, $bglevels;
		?>
		<div id="mass_movecopy_copy" style="display:none;">
			<div id="mass_movecopy_data">
				<input type="hidden" name="massfolder" value="<?php echo $album->name; ?>" />
				<?php
				echo gettext('Destination');
				?>
				<select class="ignoredirty" id="massalbumselectmenu" name="massalbumselect" onchange="">
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
	if (isset($_POST['tag_list_mass_tags_'])) {
		$tags = sanitize($_POST['tag_list_mass_tags_']);
	} else {
		$tags = array();
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
		$result = zp_apply_filter('processBulkAlbumsSave', NULL, $action);
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
				if (is_null($result)) {
					switch ($action) {
						case 'deleteallalbum':
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
							addTags($tags, $albumobj);
							break;
						case 'cleartags':
							$albumobj->setTags(array());
							break;
						case 'alltags':
							$images = $albumobj->getImages();
							foreach ($images as $imagename) {
								$imageobj = newImage($albumobj, $imagename);
								addTags($tags, $imageobj);
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
				} else {
					$albumobj->set($action, $result);
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
	$result = zp_apply_filter('processBulkImageSave', NULL, $action, $album);

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
				if (is_null($result)) {
					switch ($action) {
						case 'deleteall':
							$imageobj->remove();
							break;
						case 'showall':
							$imageobj->setShow(1);
							break;
						case 'hideall':
							$imageobj->setShow(0);
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
							addTags($tags, $imageobj);
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
				} else {
					$imageobj->set($action, $result);
				}
				$imageobj->save();
			}
			return $action;
		}
	}
	return false;
}

/**
 * Processes the check box bulk actions for comments
 *
 */
function processCommentBulkActions() {
	if (isset($_POST['ids'])) { // these is actually the folder name here!
		$action = sanitize($_POST['checkallaction']);
		$result = zp_apply_filter('processBulkCommentSave', NULL, $action);
		if ($action != 'noaction') {
			$ids = sanitize($_POST['ids']);
			if (count($ids) > 0) {
				foreach ($ids as $id) {
					$comment = new Comment(sanitize_numeric($id));
					if (is_null($result)) {
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
					} else {
						$comment->set($action, $result);
					}
					$comment->save();
				}
				return $action;
			}
		}
	}
	return false;
}

/**
 * strips out tablerow stuff created by old edit_amin_custm_data plugins
 * replaces it with appropriate DIV structure
 *
 * @param string $custom the custom html from plugins
 * @return type
 */
function stripTableRows($custom) {
	//remove the table row stuff since we are in a DIV and replace with user_right class DIVs
	$custom = preg_replace('~<tr[^>]*>~i', '', $custom);
	$custom = preg_replace('~<td[^>]*>~i', 'div class="user_right">', $custom);
	$custom = preg_replace('~</td[^>]*>~i', '</div>', $custom);
	$custom = preg_replace('~</tr[^>]*>~i', '<br class="clearall">', $custom);
	return $custom;
}

/**
 * Codeblock tabs JavaScript code
 *
 */
function codeblocktabsJS() {
	?>
	<script type="text/javascript" charset="utf-8">
						// <!-- <![CDATA[
						$(function () {
						var tabContainers = $('div.tabs > div');
										$('.first').addClass('selected');
						});
						function cbclick(num, id) {
						$('.cbx-' + id).hide();
										$('#cb' + num + '-' + id).show();
										$('.cbt-' + id).removeClass('selected');
										$('#cbt' + num + '-' + id).addClass('selected');
						}

		function cbadd(id, offset) {
		var num = $('#cbu-' + id + ' li').size() - offset;
						$('li:last', $('#cbu-' + id)).remove();
						$('#cbu-' + id).append('<li><a class="cbt-' + id + '" id="cbt' + num + '-' + id + '" onclick="cbclick(' + num + ',' + id + ');" title="' + '<?php echo gettext('codeblock %u'); ?>'.replace(/%u/, num) + '">&nbsp;&nbsp;' + num + '&nbsp;&nbsp;</a></li>');
						$('#cbu-' + id).append('<li><a id="cbp-' + id + '" onclick="cbadd(' + id + ',' + offset + ');" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>');
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
	$codeblock = getSerializedArray($obj->getCodeblock());
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
				<li><a class="<?php if ($i == 1) echo 'first '; ?>cbt-<?php echo $id; ?>" id="<?php echo 'cbt' . $i . '-' . $id; ?>" onclick="cbclick(<?php echo $i . ',' . $id; ?>);" title="<?php printf(gettext('codeblock %u'), $i); ?>">&nbsp;&nbsp;<?php echo $i; ?>&nbsp;&nbsp;</a></li>
				<?php
			}
			if (zp_loggedin(CODEBLOCK_RIGHTS)) {
				$disabled = '';
				?>
				<li><a id="<?php echo 'cbp' . '-' . $id; ?>" onclick="cbadd(<?php echo $id; ?>,<?php echo 1 - $start; ?>);" title="<?php echo gettext('add codeblock'); ?>">&nbsp;&nbsp;+&nbsp;&nbsp;</a></li>
				<?php
			} else {
				$disabled = ' disabled="disabled"';
			}
			?>
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
				<textarea name="codeblock<?php echo $i; ?>-<?php echo $id; ?>" class="codeblock" id="codeblock<?php echo $i; ?>-<?php echo $id; ?>" rows="40" cols="60"<?php echo $disabled; ?>><?php echo html_encode(@$codeblock[$i]); ?></textarea>
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
function processCodeblockSave($id, $obj) {
	$codeblock = array();
	$found = false;
	$i = (int) !isset($_POST['codeblock0-' . $id]);
	while (isset($_POST['codeblock' . $i . '-' . $id])) {
		$found = true;
		$v = sanitize($_POST['codeblock' . $i . '-' . $id], 0);
		if ($v) {
			$codeblock[$i] = $v;
		}
		$i++;
	}
	if ($found) {
		$obj->setCodeblock(serialize($codeblock));
	}
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
	if ($_zp_current_admin_obj) {
		if ($_zp_current_admin_obj->reset) {
			$_zp_loggedin = USER_RIGHTS;
		}
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
	if (defined('SERVER_PROTOCOL') && SERVER_PROTOCOL !== 'http') {
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
 * @param string $modifier optional extra data. Used, for instance to include
 * 																							parts of URL being used for more security
 */
function XSRFdefender($action, $modifier = NULL) {
	$token = getXSRFToken($action, $modifier);
	if (!isset($_REQUEST['XSRFToken']) || $_REQUEST['XSRFToken'] != $token) {
		zp_apply_filter('admin_XSRF_access', false, $action);
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&error&msg=' . sprintf(gettext('“%s” Cross Site Request Forgery blocked.'), $action));
		exitZP();
	}
	unset($_REQUEST['XSRFToken']);
	unset($_POST['XSRFToken']);
	unset($_GET['XSRFToken']);
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

	if ($date == '0-0-0 0:0:0') {
		return '&bull; &bull; &bull; ';
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
		$query .= html_encode($param) . '=' . html_encode($value) . '&amp;';
		$jump .= "'" . html_encode($param) . "=" . html_encode($value) . "',";
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
		<select name="subpage" class="ignoredirty" id="subpage<?php echo $instances; ?>" onchange="launchScript('<?php echo WEBPATH . '/' . ZENFOLDER . '/' . $script; ?>', [<?php echo $jump; ?>'subpage=' + $('#subpage<?php echo $instances; ?>').val()]);" >
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
		if (($user['rights'] & (UPLOAD_RIGHTS | ADMIN_RIGHTS | ALBUM_RIGHTS))) {
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
	$new = $subtabs = array();
	$default_viewed = $default = NULL;
	$localizer = array('setup' => gettext('setup'), 'security' => gettext('security'), 'debug' => gettext('debug'), 'deprecated' => gettext('deprecated'));
	$filelist = safe_glob(SERVERPATH . "/" . DATA_FOLDER . '/*.log');
	if (count($filelist) > 0) {
		if (isset($_GET['tab'])) {
			$tab = sanitize($_GET['tab'], 3);
		} else {
			$tab = false;
		}
		foreach ($filelist as $logfile) {
			$log = substr(basename($logfile), 0, -4);
			if (filemtime($logfile) > getOption('logviewed_' . $log)) {
				$new[] = $log;
			}
			if ($log == $tab) {
				$default = $tab;
			}

			preg_match('~(.*)_(.*)~', $log, $matches);
			if (isset($matches[2])) {
				$log = $matches[1];
				$num = ' ' . $matches[2];
			} else {
				$num = NULL;
			}


			if (array_key_exists($log, $localizer)) {
				$logfiletext = $localizer[$log];
			} else {
				$logfiletext = str_replace('_', ' ', $log);
			}

			$subtabs = array_merge($subtabs, array($logfiletext . $num => 'admin-logs.php?page=logs&tab=' . $log));
			if (filesize($logfile) > 0 && empty($default)) {
				$default_viewed = $log;
			}
		}
		if (empty($default)) {
			if (empty($new)) {
				$default = $default_viewed;
			} else {
				$default = $new;
				$default = array_shift($default);
			}
		}
	}

	$names = array_flip($subtabs);
	natcasesort($names);
	$subtabs = array_flip($names);

	return array($subtabs, $default, $new);
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
	$plugin_lc = array();
	$paths = getPluginFiles('*.php');

	$classXlate = array(
			'all' => gettext('all'),
			'thirdparty' => gettext('3rd party'),
			'enabled' => gettext('enabled'),
			'admin' => gettext('admin'),
			'demo' => gettext('demo'),
			'development' => gettext('development'),
			'feed' => gettext('feed'),
			'mail' => gettext('mail'),
			'media' => gettext('media'),
			'misc' => gettext('misc'),
			'spam' => gettext('spam'),
			'seo' => gettext('seo'),
			'uploader' => gettext('uploader'),
			'users' => gettext('users')
	);
	zp_apply_filter('plugin_tabs', $classXlate);

	$classes = $member = $thirdparty = array();
	foreach ($paths as $plugin => $path) {
		if (!isset($plugin_lc[strtolower($plugin)])) {
			$plugin_lc[strtolower($plugin)] = true;
			$p = file_get_contents($path);
			$key = 'misc';
			if ($str = isolate('@subpackage', $p)) {
				preg_match('|@subpackage\s+(.*)\s|', $str, $matches);
				if (isset($matches[1])) {
					$key = strtolower(trim($matches[1]));
				}
			}

			$classes[$key][] = $plugin;
			if (extensionEnabled($plugin)) {
				$active[$plugin] = $path;
			}
			if (strpos($path, SERVERPATH . '/' . USER_PLUGIN_FOLDER) === 0) {
				if ($str = isolate('@category', $p)) {
					preg_match('|@category\s+(.*)\s|', $str, $matches);
					$deprecate = !isset($matches[1]) || $matches[1] != 'package';
				} else {
					$deprecate = true;
				}
				if ($deprecate) {
					$thirdparty[$plugin] = $path;
				}
			}
			if (array_key_exists($key, $classXlate)) {
				$local = $classXlate[$key];
			} else {
				$local = $classXlate[$key] = $key;
			}
			$member[$plugin] = $local;
		}
	}
	ksort($classes);
	if (!empty($thirdparty))
		$tabs[$classXlate['thirdparty']] = 'admin-plugins.php?page=plugins&tab=thirdparty';
	if (!empty($active))
		$tabs[$classXlate['enabled']] = 'admin-plugins.php?page=plugins&tab=enabled';
	switch ($default) {
		case 'all':
			$currentlist = array_keys($paths);
			break;
		case 'enabled':
			$currentlist = array_keys($active);
			break;
		case'thirdparty':
			$currentlist = array_keys($thirdparty);
			break;
		default:
			$currentlist = array();
			break;
	}


	foreach ($classes as $class => $list) {
		$tabs[$classXlate[$class]] = 'admin-plugins.php?page=plugins&tab=' . $class;
		if ($class == $default) {
			$currentlist = $list;
		}
	}
	return array($tabs, $default, $currentlist, $paths, $member, $classXlate);
}

function getAdminThumb($image, $size) {
	switch ($size) {
		case 'medium':
			return $image->getCustomImage(ADMIN_THUMB_MEDIUM, NULL, NULL, ADMIN_THUMB_MEDIUM, ADMIN_THUMB_MEDIUM, NULL, NULL, -1);
		case 'large':
			return $image->getCustomImage(ADMIN_THUMB_LARGE, NULL, NULL, ADMIN_THUMB_LARGE, ADMIN_THUMB_LARGE, NULL, NULL, -1);
		default:
			return $image->getCustomImage(ADMIN_THUMB_SMALL, NULL, NULL, ADMIN_THUMB_SMALL, ADMIN_THUMB_SMALL, NULL, NULL, -1);
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
	global $messagebox, $errorbox, $notebox;
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
			$errorbox[] = gettext("You must supply a password.");
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
			case 'deleteallalbum':
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
	}
	$id = $album->getID();
	if (!$album->isDynamic()) {
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			checkAlbumParentid($albumname, $id, $recorder);
		}
	}
}

function clonedFrom() {
	if (PRIMARY_INSTALLATION) {
		return false;
	} else {
		$zen = str_replace('\\', '/', @readlink(SERVERPATH . '/' . ZENFOLDER));
		return dirname($zen);
	}
}

function pickSource($obj) {
	$params = '';
	switch ($obj->table) {
		case 'albums':
			$params = 'pick[album]=' . $obj->getFileName();
			break;
		case 'images':
			$params = 'pick[album]=' . $obj->album->getFileName() . '&pick[image]=' . $obj->getFileName();
			break;
		default:
			$params = 'pick[' . $obj->table . ']=' . $obj->getTitleLink();
			break;
	}
	return $params;
}

function linkPickerItem($obj, $id) {
	?>
	<input type="text" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $obj->getLink(); ?>" READONLY title="<?php echo gettext('You can also copy the link to your clipboard to paste elsewhere'); ?>" style="width:90%;" />
	<?php
}

function linkPickerPick($obj, $id = NULL, $extra = NULL) {
	?>$.ajax({
	type: 'POST',
	cache: false,
	data: '<?php echo addslashes(pickSource($obj)); ?>'<?php echo $extra; ?>,
	url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/pickSource.php'
	});	<?php
}

function linkPickerIcon($obj, $id = NULL, $extra = NULL) {
	$iconid = uniqid();
	if ($id) {
		$clickid = "$('#$id').select();";
	} else {
		$clickid = '';
	}
	?>
	<a onclick="<?php echo $clickid; ?>$('.pickedObject').removeClass('pickedObject');
										$('#<?php echo $iconid; ?>').addClass('pickedObject');<?php linkPickerPick($obj, $id, $extra); ?>" title="<?php echo gettext('pick source'); ?>">
			 <?php echo CLIPBOARD; ?>
	</a>
	<?php
}

function tags_subtab($tabs) {
	if (zp_loggedin(TAGS_RIGHTS)) {
		$tabs['admin']['subtabs'][gettext('tags')] = 'admin-tags.php?page=admin&tab=tags';
	}
	return $tabs;
}

function backup_subtab($tabs) {
	$tabs['admin']['subtabs'][gettext('Backup')] = "/" . ZENFOLDER . '/utilities/backup_restore.php?tab=backup';
	return $tabs;
}

function refresh_subtabs($tabs) {
	global $_zp_loggedin;
	if ($_zp_loggedin & ADMIN_RIGHTS) {
		$tabs['admin']['subtabs'][gettext('Refresh database')] = '/' . ZENFOLDER . '/admin-refresh-metadata.php?tab=prune&XSRFToken=' . getXSRFToken('refresh');
	}

	if ($_zp_loggedin & MANAGE_ALL_ALBUM_RIGHTS) {
		$tabs['admin']['subtabs'][gettext('Refresh metadata')] = '/' . ZENFOLDER . '/admin-refresh-metadata.php?tab=refresh&XSRFToken=' . getXSRFToken('refresh');
		$tabs['admin']['subtabs'][gettext('Reset album thumbs')] = "/" . ZENFOLDER . '/utilities/reset_albumthumbs.php?tab=resetthumbs';
	}
	return $tabs;
}
?>
