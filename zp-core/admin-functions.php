<?php
/**
 * support functions for Admin
 * @package admin
 */

// force UTF-8 Ø

require_once(dirname(__FILE__).'/functions.php');

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
function printAdminFooter($addl='') {
	?>
	<div id="footer">
		<?php printf(gettext('<a href="http://www.zenphoto.org" title="A simpler web album">Zen<strong>photo</strong></a> version %1$s [%2$s]'),ZENPHOTO_VERSION,ZENPHOTO_RELEASE);
		if (!empty($addl)) {
			echo ' | '. $addl;
		}
		?>
		 | <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" title="<?php echo gettext('GPLv2'); ?>"><?php echo gettext('License:GPLv2'); ?></a>
		 | <a href="http://www.zenphoto.org/category/user-guide/" title="<?php echo gettext('User guide'); ?>"><?php echo gettext('User guide'); ?></a>
		 | <a href="http://www.zenphoto.org/support/" title="<?php echo gettext('Forum'); ?>"><?php echo gettext('Forum'); ?></a>
		 | <a href="http://www.zenphoto.org/trac/report/10" title="<?php echo gettext('Bugtracker'); ?>"><?php echo gettext('Bugtracker'); ?></a>
		 | <a href="http://www.zenphoto.org/category/news/changelog/" title="<?php echo gettext('View Change log'); ?>"><?php echo gettext('Change log'); ?></a>
		 <br />
		<?php	printf(gettext('Server date: %s'),date('Y-m-d H:i:s')); 	?>
	</div>
	<?php
	db_close();	//	close the database as we are done
}

function datepickerJS() {
	$lang = str_replace('_', '-',getOption('locale'));
	if (!file_exists(SERVERPATH.'/'.ZENFOLDER.'/js/jqueryui/i18n/jquery.ui.datepicker-'.$lang.'.js')) {
		$lang = substr($lang, 0, 2);
		if (!file_exists(SERVERPATH.'/'.ZENFOLDER.'/js/jqueryui/i18n/jquery.ui.datepicker-'.$lang.'.js')) {
			$lang = '';
		}
	}
	if (!empty($lang)) {
		?>
		<script src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/jqueryui/i18n/jquery.ui.datepicker-<?php echo $lang; ?>.js" type="text/javascript"></script>
		<?php
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		$.datepicker.setDefaults({ dateFormat: 'yy-mm-dd' });
		// ]]> -->
	</script>
	<?php
}

/**
 * Print the header for all admin pages. Starts at <DOCTYPE> but does not include the </head> tag,
 * in case there is a need to add something further.
 *
 * @params string $tab the album page
 * @params string $subtab the sub-tab if any
 */
function printAdminHeader($tab,$subtab=NULL) {
	global $_zp_admin_tab, $_zp_admin_subtab, $gallery, $zenphoto_tabs,$_zp_RTL_css,$_zp_last_modified;
	$zenphoto_tabs = zp_apply_filter('admin_tabs', $zenphoto_tabs, $_zp_admin_tab);
	if (!is_object($gallery)) $gallery = new Gallery();
	$_zp_admin_tab = $tab;
	if (isset($_GET['tab'])) {
		$_zp_admin_subtab = sanitize($_GET['tab'],3);
	} else {
		$_zp_admin_subtab = $subtab;
	}
	$tabtext = $_zp_admin_tab;
	foreach ($zenphoto_tabs as $key=>$tabrow) {
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
		foreach ($tabrow['subtabs'] as $key=>$link) {
			$i = strpos($link, '&amp;tab=');
			if ($i !==false) {
				$text = substr($link, $i+9);
				if ($text == $_zp_admin_subtab) {
					$subtabtext = '-'.$key;
					break;
				}
			}
		}
	}
	if (empty($subtabtext)) {
		if ($_zp_admin_subtab) {
			$subtabtext = '-'.$_zp_admin_subtab;
		}
	}
	header('Last-Modified: ' . $_zp_last_modified);
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	zp_apply_filter('admin_headers');
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/toggleElements.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/jqueryui/jquery_ui_zenphoto.css" type="text/css" />
	<?php
	if ($_zp_RTL_css) {
		?>
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-rtl.css" type="text/css" />
		<?php
	}
	?>
	<title><?php echo sprintf(gettext('%1$s %2$s: %3$s%4$s'),html_encode($gallery->getTitle()),gettext('admin'),html_encode($tabtext),html_encode($subtabtext)); ?></title>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jqueryui/jquery_ui_zenphoto.js" type="text/javascript"></script>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/zenphoto.js" type="text/javascript" ></script>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/admin.js" type="text/javascript" ></script>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jquery.tooltip.js" type="text/javascript"></script>
	<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jquery.scrollTo.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function(){
			$("a.colorbox").colorbox({ maxWidth:"98%", maxHeight:"98%"});
		});
		jQuery(function( $ ){
			$("#fade-message").fadeTo(5000, 1).fadeOut(1000);
			$(".fade-message").fadeTo(5000, 1).fadeOut(1000);
			$('.tooltip').tooltip({
				left: -80
			});
		})
		// ]]> -->
	</script>
	<?php
	zp_apply_filter('admin_head',NULL);
}

function printSortableHead() {
	?>
	<!--Nested Sortables-->
	<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jquery.ui.nestedSortable.js"></script>
	<script type="text/javascript">
		//<!-- <![CDATA[
		$(document).ready(function(){

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

			$('.serialize').click(function(){
				serialized = $('ul.page-list').nestedSortable('serialize');
				if (serialized != original_order) {
					$('#serializeOutput').html('<input type="hidden" name="order" size="30" maxlength="1000" value="'+serialized+'" />');
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
 * Print the thumbnail for a particular Image.
 *
 * @param $image object The Image object whose thumbnail we want to display.
 * @param $class string Optional class attribute for the hyperlink.  Default is NULL.
 * @param $id	 string Optional id attribute for the hyperlink.  Default is NULL.
 * @param $bg
 *
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */

function adminPrintImageThumb($image, $class=NULL, $id=NULL) {
	echo "\n  <img class=\"imagethumb\" id=\"id_". $image->id ."\" src=\"" . $image->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, -1) . "\" alt=\"". html_encode($image->getTitle()) . "\" title=\"". html_encode($image->getTitle()) . " (". html_encode($image->getFileName()) . ")\"" .
	((THUMB_CROP) ? " width=\"".THUMB_CROP_WIDTH."\" height=\"".THUMB_CROP_HEIGHT."\"" : "") .
	(($class) ? " class=\"$class\"" : "") .
	(($id) ? " id=\"$id\"" : "") . " />";
}


/**
 * Print the html required to display the ZP logo and links in the top section of the admin page.
 *
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */
function printLogoAndLinks() {
	global $_zp_current_admin_obj,$_zp_admin_tab,$_zp_admin_subtab,$gallery;
	if ($_zp_admin_subtab) {
		$subtab = '-'.$_zp_admin_subtab;
	} else {
		$subtab = '';
	}
	?>
	<span id="administration">
		<img id="logo" src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/zen-logo.png"
				title="<?php echo sprintf(gettext('%1$s administration:%2$s%3$s'),html_encode($gallery->getTitle()),html_encode($_zp_admin_tab),html_encode($subtab)); ?>"
				alt="<?php echo gettext('Zenphoto Administration'); ?>" align="bottom" />
	</span>
	<?php
	echo "\n<div id=\"links\">";
	echo "\n  ";
	if (!is_null($_zp_current_admin_obj)) {
		if (getOption('server_protocol')=='https') $sec=1; else $sec=0;
		$last = $_zp_current_admin_obj->lastlogon;
		if (empty($last)) {
			printf(gettext('Logged in as %1$s'), $_zp_current_admin_obj->getUser());
		} else {
			printf(gettext('Logged in as %1$s (last login %2$s)'), $_zp_current_admin_obj->getUser(),$last);
		}
		echo " &nbsp; | &nbsp; <a href=\"".WEBPATH."/".ZENFOLDER."/admin.php?logout=".$sec."\">".gettext("Log Out")."</a> &nbsp; | &nbsp; ";
	}
	echo '<a href="'.FULLWEBPATH.'">';
	$t = get_language_string(getOption('gallery_title'));
	if (!empty($t))	{
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
	global $subtabs, $zenphoto_tabs, $main_tab_space, $_zp_UTF8, $_zp_admin_tab;
	$chars = 0;
	foreach ($zenphoto_tabs as $atab) {
		$chars = $chars + $_zp_UTF8->strlen($atab['text']);
	}
	switch (getOption('locale')) {
		case 'zh_CN':
		case 'zh_TW':
		case 'ja_JP':
			$main_tab_space = count($zenphoto_tabs)*3+$chars;
			break;
		default:
			$main_tab_space = round((count($zenphoto_tabs)*32+round($chars*7.5))/11.5);
			break;
	}
	?>
	<ul class="nav" id="jsddm" style="width: <?php echo $main_tab_space; ?>em">
	<?php
	foreach ($zenphoto_tabs as $key=>$atab) {
		?>
		<li <?php if($_zp_admin_tab == $key) echo 'class="current"' ?>>
			<a href="<?php echo $atab['link']; ?>"><?php echo $atab['text']; ?></a>
		</li>
		<?php
	}
	?>
	</ul>
	<?php
}

function printSubtabs() {
	global $zenphoto_tabs, $main_tab_space, $_zp_admin_tab, $_zp_admin_subtab;
	$tabs = $zenphoto_tabs[$_zp_admin_tab]['subtabs'];
	if (!is_array($tabs)) return $_zp_admin_subtab;
	$current = $_zp_admin_subtab;
	if (isset($_GET['tab'])) {
		$test = sanitize($_GET['tab']);
		foreach ($tabs as $link) {
			$i = strrpos($link, 'tab=');
			$amp = strrpos($link, '&');
			if ($i!==false) {
				if ($amp > $i) {
					$link = substr($link, 0, $amp);
				}
				if ($test == substr($link, $i+4)) {
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
			$current = $tabs;
			$current = array_shift($current);
			$i = strrpos($current, 'tab=');
			$amp = strrpos($current, '&');
			if ($i===false) {
				$current = '';
			} else {
				if ($amp > $i) {
					$current = substr($current, 0, $amp);
				}
				$current = substr($current, $i+4);
			}
		} else {
			$current = $_zp_admin_subtab;
		}
	}
	?>
	<ul class="subnav" >
	<?php
	foreach ($tabs as $key=>$link) {
		$i = strrpos($link, 'tab=');
		$amp = strrpos($link, '&');
		if ($i===false) {
			$tab = $_zp_admin_subtab;
		} else {
			if ($amp > $i) {
				$source = substr($link, 0, $amp);
			} else {
				$source = $link;
			}
			$tab = substr($source, $i+4);
		}
		if (strpos($link,'/') !== 0) {	// zp_core relative
			$link = WEBPATH.'/'.ZENFOLDER.'/'.$link;
		} else {
			$link = WEBPATH.$link;
		}
		echo '<li'.(($current == $tab) ? ' class="current"' : '').'>'.
				 '<a href = "'.$link.'">'.$key.'</a></li>'."\n";
	}
	?>
	</ul>
	<?php
	return $current;
}

function setAlbumSubtabs($album) {
	global $zenphoto_tabs;
	$albumlink = '?page=edit&amp;album='.urlencode($album->name);
	$default = NULL;
	if (!is_array($zenphoto_tabs['edit']['subtabs'])) {
		$zenphoto_tabs['edit']['subtabs'] = array();
	}
	$subrights = $album->albumSubRights();
	if (!$album->isDynamic() && $album->getNumImages()) {
		if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT_IMAGE | MANAGED_OBJECT_RIGHTS_UPLOAD) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge(
																						array(gettext('Images') => 'admin-edit.php'.$albumlink.'&amp;tab=imageinfo'),
																						$zenphoto_tabs['edit']['subtabs']);
			$default = 'imageinfo';
		}
		if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
			$zenphoto_tabs['edit']['subtabs'] = array_merge(
																						array(gettext('Image order') => 'admin-albumsort.php'.$albumlink.'&amp;tab=sort'),
																						$zenphoto_tabs['edit']['subtabs']);
		}
	}
	if (!$album->isDynamic() && $album->getNumAlbums() > 0) {
		$zenphoto_tabs['edit']['subtabs'] = array_merge(
																					array(gettext('Subalbums') => 'admin-edit.php'.$albumlink.'&amp;tab=subalbuminfo'),
																					$zenphoto_tabs['edit']['subtabs']);
		$default = 'subalbuminfo';
	}
	if ($subrights & MANAGED_OBJECT_RIGHTS_EDIT) {
		$zenphoto_tabs['edit']['subtabs'] = array_merge(
																					array(gettext('Album') => 'admin-edit.php'.$albumlink.'&amp;tab=albuminfo'),
																					$zenphoto_tabs['edit']['subtabs']);
		$default = 'albuminfo';
	}
	$zenphoto_tabs['edit']['default'] = $default;
	return $default;
}

function checked($checked, $current) {
	if ( $checked == $current)
	echo ' checked="checked"';
}

function genAlbumUploadList(&$list, $curAlbum=NULL) {
	$gallery = new Gallery();
	$albums = array();
	if (is_null($curAlbum)) {
		$albumsprime = $gallery->getAlbums(0);
		foreach ($albumsprime as $album) { // check for rights
			$albumobj = new Album($gallery, $album);
			if ($albumobj->isMyItem(UPLOAD_RIGHTS)) {
				$albums[] = $album;
			}
		}
	} else {
		$albums = $curAlbum->getAlbums(0);
	}
	if (is_array($albums)) {
		foreach ($albums as $folder) {
			$album = new Album($gallery, $folder);
			if (!$album->isDynamic()) {
				$list[$album->getFolder()] = $album->getTitle();
				genAlbumUploadList($list, $album);  /* generate for subalbums */
			}
		}
	}
}

function displayDeleted() {
	/* Display a message if needed. Fade out and hide after 2 seconds. */
	if (isset($_GET['ndeleted'])) {
		$ntdel = sanitize_numeric($_GET['ndeleted']);
		if ($ntdel <= 2) {
			$msg = gettext("Image");
		} else {
			$msg = gettext("Album");
			$ntdel = $ntdel - 2;
		}
		if ($ntdel == 2) {
			$msg = sprintf(gettext("%s failed to delete."),$msg);
			$class = 'errorbox';
		} else {
			$msg = sprintf(gettext("%s deleted successfully."),$msg);
			$class = 'messagebox';
		}
		echo '<div class="' . $class . ' fade-message">';
		echo  "<h2>" . $msg . "</h2>";
		echo '</div>';
	}
}

define ('CUSTOM_OPTION_PREFIX', '_ZP_CUSTOM_');
/**
 * Generates the HTML for custom options (e.g. theme options, plugin options, etc.)
 *
 * @param object $optionHandler the object to handle custom options
 * @param string $indent used to indent the option for nested options
 * @param object $album if not null, the album to which the option belongs
 * @param bool $hide set to true to hide the output (used by the plugin-options folding
 * $paran array $supportedOptions pass these in if you already have them
 * @param bool $theme set true if dealing with theme options
 * @param string $initial initila show/hide state
 *
 * There are four type of custom options:
 * 		OPTION_TYPE_TEXTBOX:				a textbox
 * 		OPTION_TYPE_CLEAARTEXT:			a textbox, but no sanitization on save
 * 		OPTION_TYPE_CHECKBOX:				a checkbox
 * 		OPTION_TYPE_CUSTOM:					handled by $optionHandler->handleOption()
 * 		OPTION_TYPE_TEXTAREA:				a textarea
 * 		OPTION_TYPE_RADIO:					radio buttons (button names are in the 'buttons' index of the supported options array)
 * 		OPTION_TYPE_SELECTOR:				selector (selection list is in the 'selections' index of the supported options array
 * 																					null_selection contains the text for the empty selection. If not present there
 * 																					will be no empty selection)
 * 		OPTION_TYPE_CHECKBOX_ARRAY:	checkbox array (checkbox list is in the 'checkboxes' index of the supported options array.)
 * 		OPTION_TYPE_CHECKBOX_UL:		checkbox UL (checkbox list is in the 'checkboxes' index of the supported options array.)
 * 		OPTION_TYPE_COLOR_PICKER:		Color picker
 *
 * type 0 and 3 support multi-lingual strings.
 */
define('OPTION_TYPE_TEXTBOX',0);
define('OPTION_TYPE_CHECKBOX',1);
define('OPTION_TYPE_CUSTOM',2);
define('OPTION_TYPE_TEXTAREA',3);
define('OPTION_TYPE_RADIO',4);
define('OPTION_TYPE_SELECTOR',5);
define('OPTION_TYPE_CHECKBOX_ARRAY',6);
define('OPTION_TYPE_CHECKBOX_UL',7);
define('OPTION_TYPE_COLOR_PICKER',8);
define('OPTION_TYPE_CLEARTEXT',9);

function customOptions($optionHandler, $indent="", $album=NULL, $showhide=false, $supportedOptions=NULL, $theme=false, $initial='none') {
	if (is_null($supportedOptions)) $supportedOptions = $optionHandler->getOptionsSupported();
	if (count($supportedOptions) > 0) {
		$whom = get_class($optionHandler);
		$options = $supportedOptions;
		$option = array_shift($options);
		if (array_key_exists('order', $option)) {
			$options = sortMultiArray($supportedOptions, 'order');
			$options = array_keys($options);
		} else {
			$options = array_keys($supportedOptions);
			natcasesort($options);
		}
		foreach($options as $option) {
			$row = $supportedOptions[$option];
			if (false!==$i=stripos($option,chr(0))) {
				$option = substr($option, 0, $i);
			}

			$type = $row['type'];
			$desc = $row['desc'];
			$key = $row['key'];
			$optionID = $whom.'_'.$key;
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
				?>
				<td width="175"><?php if ($option) echo $indent . $option; ?></td>
				<?php
				switch ($type) {
					case OPTION_TYPE_CLEARTEXT:
						$multilingual = false;
					case OPTION_TYPE_TEXTBOX:
					case OPTION_TYPE_TEXTAREA:
						if ($type == OPTION_TYPE_CLEARTEXT) {
							$clear = 'clear';
						} else {
							$clear = '';
						}
						?>
						<td width="350">
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.$clear.'text-'.$key; ?>" value="0" />
							<?php
							if ($multilingual) {
								print_language_string_list($v, $key, $type, NULL, $editor);
							} else {
								if ($type == OPTION_TYPE_TEXTAREA) {
									?>
									<textarea id="<?php echo $key; ?>" name="<?php echo $key; ?>" cols="<?php echo TEXTAREA_COLUMNS; ?>"	style="width: 320px" rows="6"<?php echo $disabled; ?>><?php  echo html_encode($v); ?></textarea>
									<?php
								} else {
									?>
									<input type="text" size="40" id="<?php echo $key; ?>" name="<?php echo $key; ?>" style="width: 338px" value="<?php echo html_encode($v); ?>"<?php echo $disabled; ?> />
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
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'chkbox-'.$key; ?>" value="0" />
							<input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php echo checked('1', $v); ?><?php echo $disabled; ?> />
						</td>
						<?php
						break;
					case OPTION_TYPE_CUSTOM:
						?>
						<td width="350">
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'custom-'.$key; ?>" value="0" />
							<?php	$optionHandler->handleOption($key, $v); ?>
						</td>
						<?php
						break;
					case OPTION_TYPE_RADIO:
						$behind = (isset($row['behind']) && $row['behind']);
						?>
						<td width="350">
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'radio-'.$key; ?>" value="0"<?php echo $disabled; ?> />
							<?php generateRadiobuttonsFromArray($v,$row['buttons'],$key, $behind); ?>
						</td>
						<?php
						break;
					case OPTION_TYPE_SELECTOR:
						?>
						<td width="350">
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'selector-'.$key?>" value="0" />
							<select id="<?php echo $key; ?>" name="<?php echo $key; ?>"<?php echo $disabled; ?> >
								<?php
								if (array_key_exists('null_selection', $row)) {
									?>
									<option value=""<?php if (empty($v)) echo ' selected="selected"'; ?>><?php echo $row['null_selection']; ?></option>
									<?php
								}
								?>
								<?php generateListFromArray(array($v),$row['selections'], false, true); ?>
							</select>
						</td>
						<?php
						break;
					case OPTION_TYPE_CHECKBOX_ARRAY:
						$behind = (isset($row['behind']) && $row['behind']);
						?>
						<td width="350">
							<?php
							foreach ($row['checkboxes'] as $display=>$checkbox) {
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
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'chkbox-'.$checkbox; ?>" value="0" />

								<label class="checkboxlabel">
									<?php if ($behind) echo($display); ?>
									<input type="checkbox" id="<?php echo $checkbox; ?>" name="<?php echo $checkbox; ?>" value="1"<?php echo checked('1', $v); ?><?php echo $disabled; ?> />
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
							foreach ($row['checkboxes'] as $display=>$checkbox) {
								?>
								<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'chkbox-'.$checkbox; ?>" value="0" />
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
								if ($v)	{
									$cvarray[] = $checkbox;
								} else {
									$all = false;
								}
							}
							?>
							<ul class="customchecklist">
								<?php generateUnorderedListFromArray($cvarray, $row['checkboxes'], '', '', true, true); ?>
							</ul>
							<script type="text/javascript">
								// <!-- <![CDATA[
								function <?php echo $key; ?>_all() {
									var check = $('#all_<?php echo $key; ?>').attr('checked');
									<?php
									foreach ($row['checkboxes'] as $display=>$checkbox) {
										?>
										$('#<?php echo $checkbox; ?>').attr('checked',check);
										<?php
									}
									?>
								}
								// ]]> -->
							</script>
							<label>
								<input type="checkbox" name="all_<?php echo $key; ?>" id="all_<?php echo $key; ?>" onclick="<?php echo $key; ?>_all();" <?php if ($all) echo ' checked="checked"'; ?>/>
								<?php echo gettext('all'); ?>
							</label>
						</td>
						<?php
						break;
					case OPTION_TYPE_COLOR_PICKER:
						if (empty($v)) $v = '#000000';
						?>
						<td width="350" style="margin:0; padding:0">
							<input type="hidden" name="<?php echo CUSTOM_OPTION_PREFIX.'text-'.$key; ?>" value="0" />
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
				?>
				<td><?php echo $desc; ?></td>
			</tr>
			<?php
		}
	}
}

function processCustomOptionSave($returntab, $themename=NULL, $themealbum=NULL) {
	foreach ($_POST as $postkey=>$value) {
		if (preg_match('/^'.CUSTOM_OPTION_PREFIX.'/', $postkey)) { // custom option!
			$key = substr($postkey, strpos($postkey, '-')+1);
			$switch = substr($postkey, strlen(CUSTOM_OPTION_PREFIX), -strlen($key)-1);
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
					if (isset($_POST[$key])) {
						$value = sanitize($_POST[$key], 1);
					} else {
						$value = 0;
					}
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
				if ($value) $returntab .= '&'.$postkey;
			}
		}
	}
	return $returntab;
}

/**
 * Encodes for use as a $_POST index
 *
 * @param string $str
 */
function postIndexEncode($str) {
	return strtr(urlencode($str),array('.'=>'__2E__','+'=> '_-_','%'=>'_--_'));
}

/**
 * Decodes encoded $_POST index
 *
 * @param string $str
 * @return string
 */
function postIndexDecode($str) {
	return urldecode(strtr($str,array('__2E__'=>'.','_-_'=>'+','_--_'=>'%')));
}


/**
 * Prints radio buttons from an array
 *
 * @param string $currentvalue The current selected value
 * @param string $list the array of the list items form is localtext => buttonvalue
 * @param string $option the name of the option for the input field name
 * @param bool $behind set true to have the "text" before the button
 */
function generateRadiobuttonsFromArray($currentvalue,$list,$option,$behind=false, $class='checkboxlabel') {
	foreach($list as $text=>$value) {
		$checked ="";
		if($value == $currentvalue) {
			$checked = ' checked="checked" '; //the checked() function uses quotes the other way round...
		}
		?>
		<label<?php if ($class) echo ' class="'.$class.'"'; ?>>
			<?php if ($behind) echo $text; ?>
			<input type="radio" name="<?php echo $option; ?>" id="<?php echo $option.'-'.$value; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?> />
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
function generateUnorderedListFromArray($currentValue, $list, $prefix, $alterrights, $sort, $localize, $class=NULL, $extra=NULL) {
	if (is_null($extra)) $extra = array();
	if (!empty($class)) $class = ' class="'.$class.'" ';
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
	foreach($list as $key=>$item) {
		$listitem = postIndexEncode($prefix.$item);
		if ($localize) {
			$display = $key;
		} else {
			$display = $item;
		}
		?>
		<li>
		<span style="display:inline;white-space:nowrap">
			<label class="displayinline">
				<input id="<?php echo $listitem; ?>"<?php echo $class;?> name="<?php echo $listitem; ?>" type="checkbox"
					<?php if (isset($cv[$item])) {echo ' checked="checked"';	} ?> value="<?php echo html_encode($item); ?>"
					<?php echo $alterrights; ?> />
				<?php echo html_encode($display); ?>
			</label>
			<?php
			if (array_key_exists($key, $extra)) {
				foreach ($extra[$key] as $box) {
					if ($box['display']) {
						?>
						<label class="displayinline">
							<input type="checkbox" id="<?php echo $listitem.'_'.$box['name']; ?>" name="<?php echo $listitem.'_'.$box['name']; ?>"
									 value="<?php echo html_encode($box['value']); ?>" <?php if ($box['checked']) {echo ' checked="checked"';	} ?>
									 <?php echo $alterrights; ?> \> <?php echo $box['display'];?>
						</label>
						<?php
					} else {
						?>
						<input type="hidden" id="<?php echo $listitem.'_'.$box['name']; ?>" name="<?php echo $listitem.'_'.$box['name']; ?>"
									 value="<?php echo $box['value']; ?>" />
						<?php
					}
				}
			}
			?>
			</span>
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
function tagSelector($that, $postit, $showCounts=false, $mostused=false, $addnew=true) {
	global $_zp_admin_ordered_taglist, $_zp_admin_LC_taglist, $_zp_UTF8, $jaTagList;
	if (is_null($_zp_admin_ordered_taglist)) {
		if ($mostused || $showCounts) {
			$counts = getAllTagsCount();
			if ($mostused) arsort($counts, SORT_NUMERIC);
			$them = array();
			foreach ($counts as $tag=>$count) {
				$them[] = $tag;
			}
		} else {
			$them = getAllTagsUnique();
		}
		$_zp_admin_ordered_taglist = $them;
		$_zp_admin_LC_taglist = array();
		foreach ($them as $tag) {
			$_zp_admin_LC_taglist[] = $_zp_UTF8->strtolower($tag);
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
			$tagLC = 	$_zp_UTF8->strtolower($tag);
			$key = array_search($tagLC, $_zp_admin_LC_taglist);
			if ($key !== false) {
				unset($them[$key]);
			}
		}
	}
	$hr = '';
	if ($addnew) {
		if (count($tags) == 0) {
			$hr = '<li><hr /></li>';
		}
		?>

			<span class="new_tag displayinline" >
				<a href="javascript:addNewTag('<?php echo $postit; ?>','<?php echo gettext('tag set!'); ?>');" title="<?php echo gettext('add tag'); ?>">
					<img src="images/add.png" title="<?php echo gettext('add tag'); ?>"/>
				</a>
				<input type="text" value="" name="newtag_<?php echo $postit; ?>" id="newtag_<?php echo $postit; ?>" />
			</span>

		<?php
	}
	?>
	<ul id="list_<?php echo $postit; ?>" class="tagchecklist">
	<?php echo $hr; ?>
	<?php
	if ($showCounts) {
		$displaylist = array();
		foreach ($them as $tag) {
			$displaylist[$tag.' ['.$counts[$tag].']'] = $tag;
		}
	} else {
		$displaylist = $them;
	}
	if (count($tags) > 0) {
		generateUnorderedListFromArray($tags, $tags, $postit, false, !$mostused, $showCounts);
		?>
		<li><hr /></li>
		<?php
	}
	generateUnorderedListFromArray(array(), $displaylist, $postit, false, !$mostused, $showCounts);
	?>
	</ul>
	<?php
}

/**
 * emits the html for editing album information
 * called in edit album and mass edit
 * @param string $index the index of the entry in mass edit or '0' if single album
 * @param object $album the album object
 * @param bool $collapse_tags set true to initially hide tab list
 * @since 1.1.3
 */
function printAlbumEditForm($index, $album, $collapse_tags) {
	global $sortby, $gallery, $mcr_albumlist, $albumdbfields, $imagedbfields, $_thumb_field_text;
	$tagsort = getTagOrder();
	if ($index == 0) {
		if (isset($saved)) {
			$album->setSubalbumSortType('manual');
		}
		$suffix = $prefix = '';
	} else {
		$prefix = "$index-";
		$suffix = "_$index";
		echo "<p><em><strong>" . $album->name . "</strong></em></p>";
	}
 ?>
	<input type="hidden" name="<?php echo $prefix; ?>folder" value="<?php echo $album->name; ?>" />
	<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
	<input	type="hidden" name="<?php echo $prefix; ?>password_enabled" id="password_enabled<?php echo $suffix; ?>" value="0" />
	<span class="buttons">
		<?php
		$parent = dirname($album->name);
		if ($parent == '/' || $parent == '.' || empty($parent)) {
			$parent = '';
		} else {
			$parent = '&amp;album='.$parent.'&amp;tab=subalbuminfo';
		}
		?>
		<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
		<img	src="images/arrow_left_blue_round.png" alt="" />
		<strong><?php echo gettext("Back"); ?></strong>
		</a>
		<button type="submit" title="<?php echo gettext("Apply"); ?>">
		<img	src="images/pass.png" alt="" />
		<strong><?php echo gettext("Apply"); ?></strong>
		</button>
		<button type="reset" title="<?php echo gettext("Reset"); ?>" onclick="javascript:$('.deletemsg').hide();" >
		<img	src="images/fail.png" alt="" />
		<strong><?php echo gettext("Reset"); ?></strong>
		</button>
		<div class="floatright">
		<?php
		if (!$album->isDynamic()) {
			?>
			<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>',true);">
			<img src="images/folder.png" alt="" />
			<strong><?php echo gettext('New subalbum'); ?></strong>
			</button>
			<?php
		}
		?>
		<a title="<?php echo gettext('View Album'); ?>" href="<?php echo WEBPATH . "/index.php?album=". pathurlencode($album->getFolder()); ?>">
		<img src="images/view.png" alt="" />
		<strong><?php echo gettext('View Album'); ?></strong>
		</a>
		</div>
	</span>
<br clear="all" /><br />
	<table>
		<tr>
			<td width="70%" valign="top">
				<table>
					<tr>
						<td valign="top"><?php  echo gettext("Owner"); ?></td>
						<td>
							<?php
							if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
								?>
								<select name="<?php  echo $prefix; ?>-owner">
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
						<td align="left" valign="top" width="150">
						<?php echo gettext("Album Title"); ?>:
						</td>
						<td>
						<?php print_language_string_list($album->get('title'), $prefix."albumtitle"); ?>
						</td>
					</tr>

					<tr>
						<td align="left" valign="top" >
						<?php echo gettext("Album Description:"); ?>
						</td>
						<td>
						<?php	print_language_string_list($album->get('desc'), $prefix."albumdesc", true, NULL, 'texteditor'); ?>
						</td>
					</tr>
					<?php
					if (GALLERY_SECURITY != 'private') {
						?>
						<tr class="password<?php echo $suffix; ?>extrashow" <?php if (GALLERY_SECURITY == 'private') echo 'style="display:none"'; ?> >
							<td align="left" valign="top">
								<p>
									<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',true);">
									<?php echo gettext("Album password:"); ?>
									</a>
								</p>
							</td>
							<td>
							<?php
							$x = $album->getPassword();
							if (empty($x)) {
								?>
								<img src="images/lock_open.png" />
								<?php
							} else {
								$x = '          ';
								?>
							<script type="text/javascript">
								function resetPass() {
									$('#user_name').val('');
									$('#pass').val('');
									$('#pass_2').val('');
									toggle_passwords('',true);
								}
							</script>
							<a onclick="resetPass();" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
								<?php
							}
							?>
							</td>
						</tr>
						<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
							<td align="left" valign="top">
								<p>
								<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',false);">
									<?php echo gettext("Album guest user:"); ?>
								</a>
								</p>
								<p>
								<?php echo gettext("Album password:");?>
								<br />
								<?php echo gettext("repeat:");?>
								</p>
								<p>
								<?php echo gettext("Password hint:"); ?>
								</p>
							</td>
							<td>
								<p>
									<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="user_name" name="<?php echo $prefix; ?>albumuser" value="<?php echo $album->getUser(); ?>" />
								</p>
								<p>
								<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>" id="pass" name="<?php echo $prefix; ?>albumpass"  value="<?php echo $x; ?>" />
								<br />
								<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>" id="pass_2" name="<?php echo $prefix; ?>albumpass_2" value="<?php echo $x; ?>" />
								</p>
								<p>
								<?php print_language_string_list($album->get('password_hint'), $prefix."albumpass_hint"); ?>
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
						<td align="left" valign="top"><?php echo gettext("Date:");?> </td>
						<td width="400">
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
						<td align="left" valign="top"><?php echo gettext("Location:"); ?> </td>
						<td>
						<?php print_language_string_list($album->getLocation(), $prefix."albumlocation"); ?>
						</td>
					</tr>
					<?php
					$custom = zp_apply_filter('edit_album_custom_data', '', $album, $prefix);
					if (empty($custom)) {
						?>
					<tr>
						<td align="left" valign="top"><?php echo gettext("Custom data:"); ?></td>
						<td><?php print_language_string_list($album->get('custom_data'), $prefix."album_custom_data", true , NULL, 'texteditor_albumcustomdata'); ?></td>
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
						<td align="left" valign="top"><?php echo gettext("Sort subalbums by:");?> </td>
						<td>
							<span class="nowrap">
								<select id="albumsortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>subalbumsortby" onchange="update_direction(this,'album_direction_div<?php echo $suffix; ?>','album_custom_div<?php echo $suffix; ?>')">
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
									<input type="checkbox" name="<?php echo $prefix; ?>album_sortdirection" value="1" <?php if ($album->getSortDirection('album')) {	echo "CHECKED";	}; ?> />
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
								<input id="customalbumsort<?php echo $suffix; ?>" class="customalbumsort" name="<?php echo $prefix; ?>customalbumsort" type="text" value="<?php echo html_encode($cvt); ?>"></input>
							</span>
					</td>
				</tr>

				<tr>
					<td align="left" valign="top"><?php echo gettext("Sort images by:"); ?> </td>
						<td>
							<span class="nowrap">
								<select id="imagesortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>sortby" onchange="update_direction(this,'image_direction_div<?php echo $suffix; ?>','image_custom_div<?php echo $suffix; ?>')">
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
									<?php if ($album->getSortDirection('image')) { echo ' checked="checked"'; }?> />
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
							<input id="customimagesort<?php echo $suffix; ?>" class="customimagesort" name="<?php echo $prefix; ?>customimagesort" type="text" value="<?php echo html_encode($cvt); ?>"></input>
						</span>
					 </td>
				</tr>

				<?php
				if (is_null($album->getParent())) {
				?>
					<tr>
						<td align="left" valign="top"><?php echo gettext("Album theme:"); ?> </td>
						<td>
							<select id="album_theme" class="album_theme" name="<?php echo $prefix; ?>album_theme"	<?php if (!zp_loggedin(THEMES_RIGHTS)) echo 'disabled="disabled" '; ?>	>
							<?php
							$themes = $gallery->getThemes();
							$oldtheme = $album->getAlbumTheme();
							if (empty($oldtheme)) {
								$selected = 'selected="selected"';
							} else {
								$selected = '';;
							}
							?>
							<option value="" style="background-color:LightGray" <?php echo $selected; ?> ><?php echo gettext('*gallery theme');?></option>
							<?php
							foreach ($themes as $theme=>$themeinfo) {
								if ($oldtheme == $theme) {
									$selected = 'selected="selected"';
								} else {
									$selected = '';;
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
						<td align="left" valign="top" width="150"><?php echo gettext("Album watermarks:"); ?> </td>
						<td>
							<?php $current = $album->getWatermark(); ?>
							<select id="album_watermark" name="<?php echo $prefix; ?>album_watermark">
								<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current==NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
								<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
								<?php
								$watermarks = getWatermarks();
								generateListFromArray(array($current), $watermarks, false, false);
								?>
							</select>
							<?php echo gettext('Images'); ?>
							</td>
					</tr>
					<tr>
						<td align="left" valign="top" width="150"></td>
						<td>
							<?php $current = $album->getWatermarkThumb(); ?>
							<select id="album_watermark_thumb" name="<?php echo $prefix; ?>album_watermark_thumb">
								<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current==NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
								<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
								<?php
								$watermarks = getWatermarks();
								generateListFromArray(array($current), $watermarks, false, false);
								?>
							</select>
							<?php echo gettext('Thumbs'); ?>
						</td>
					</tr>
					<?php
				}
				if ($index==0) {	// suppress for mass-edit
					?>
					<tr>
						<td align="left" valign="top" width="150"><?php echo gettext("Thumbnail:"); ?> </td>
						<td>
						<?php
						$showThumb = getOption('thumb_select_images');
						$thumb = $album->get('thumb');
						if ($showThumb)  {
							?>
							<script type="text/javascript">
								// <!-- <![CDATA[
								updateThumbPreview(document.getElementById('thumbselect'));
								// ]]> -->
							</script>
							<?php
						}
						?>
						<select style="width:320px" <?php	if ($showThumb) {	?>class="thumbselect" onchange="updateThumbPreview(this)"	<?php	}	?> name="<?php echo $prefix; ?>thumb">
							<option <?php if ($showThumb) {	?>class="thumboption" style="background-color:#B1F7B6"<?php		}
								if ($thumb === '1') {	?>selected="selected"<?php } ?>	value="1"><?php echo $_thumb_field_text[getOption('AlbumThumbSelectField')]; ?>
							</option>
							<option <?php if ($showThumb) { ?>class="thumboption" style="background-color:#B1F7B6" <?php } ?>
								<?php	if (empty($thumb) && $thumb !== '1') { ?> selected="selected" <?php } ?>
									value=""><?php echo gettext('randomly selected');?>
							</option>
							<?php
							if ($album->isDynamic()) {
								$params = $album->getSearchParams();
								$search = new SearchEngine(true);
								$search->setSearchParams($params);
								$images = $search->getImages(0);
								$thumb = $album->get('thumb');
								$imagelist = array();
								foreach ($images as $imagerow) {
									$folder = $imagerow['folder'];
									$filename = $imagerow['filename'];
									$imagelist[] = '/'.$folder.'/'.$filename;
								}
								if (count($imagelist) == 0) {
									$subalbums = $search->getAlbums(0);
									foreach ($subalbums as $folder) {
										$newalbum = new Album($gallery, $folder);
										if (!$newalbum->isDynamic()) {
											$images = $newalbum->getImages(0);
											foreach ($images as $filename) {
												$imagelist[] = '/'.$folder.'/'.$filename;
											}
										}
									}
								}
								foreach ($imagelist as $imagepath) {
									$list = explode('/', $imagepath);
									$filename = $list[count($list)-1];
									unset($list[count($list)-1]);
									$folder = implode('/', $list);
									$albumx = new Album($gallery, $folder);
									$image = newImage($albumx, $filename);
									$selected = ($imagepath == $thumb);
									echo "\n<option";
									if ($showThumb) {
										echo " class=\"thumboption\"";
										echo " style=\"background-image: url(" . html_encode($image->getSizedImage(80)) .	"); background-repeat: no-repeat;\"";
									}
									echo " value=\"".$imagepath."\"";
									if ($selected) {
										echo " selected=\"selected\"";
									}
									echo ">" . $image->getTitle();
									echo  " ($imagepath)";
									echo "</option>";
								}
							} else {
								$images = $album->getImages();
								if (count($images) == 0 && $album->getNumAlbums() > 0) {
									$imagearray = array();
									$albumnames = array();
									$strip = strlen($album->name) + 1;
									$subIDs = getAllSubAlbumIDs($album->name);
									if(!is_null($subIDs)) {
										foreach ($subIDs as $ID) {
											$albumnames[$ID['id']] = $ID['folder'];
											$query = 'SELECT `id` , `albumid` , `filename` , `title` FROM '.prefix('images').' WHERE `albumid` = "'.
											$ID['id'] .'"';
											$imagearray = array_merge($imagearray, query_full_array($query));
										}
										foreach ($imagearray as $imagerow) {
											$filename = $imagerow['filename'];
											$folder = $albumnames[$imagerow['albumid']];
											$imagepath = substr($folder, $strip).'/'.$filename;
											if (substr($imagepath, 0, 1) == '/') { $imagepath = substr($imagepath, 1); }
											$albumx = new Album($gallery, $folder);
											$image = newImage($albumx, $filename);
											if (is_valid_image($filename)) {
												$selected = ($imagepath == $thumb);
												echo "\n<option";
												if (getOption('thumb_select_images')) {
													echo " class=\"thumboption\"";
													echo " style=\"background-image: url(" . html_encode($image->getSizedImage(80)) . "); background-repeat: no-repeat;\"";
												}
												echo " value=\"".$imagepath."\"";
												if ($selected) {
													echo " selected=\"selected\"";
												}
												echo ">" . $image->getTitle();
												echo  " ($imagepath)";
												echo "</option>";
											}
										}
									}
								} else {
									foreach ($images as $filename) {
										$image = newImage($album, $filename);
										$selected = ($filename == $album->get('thumb'));
										if (is_valid_image($filename)) {
											echo "\n<option";
											if (getOption('thumb_select_images')) {
												echo " class=\"thumboption\"";
												echo " style=\"background-image: url(" . html_encode($image->getSizedImage(80)) . "); background-repeat: no-repeat;\"";
											}
											echo " value=\"" . $filename . "\"";
											if ($selected) {
												echo " selected=\"selected\"";
											}
											echo ">" . $image->getTitle();
											if ($filename != $image->getTitle()) {
												echo  " ($filename)";
											}
											echo "</option>";
										}
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
		<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
		<td>
		<br />
			<div class="tabs">
				<ul class="tabNavigation">
					<li><a href="#first"><?php echo gettext("Codeblock 1"); ?></a></li>
					<li><a href="#second"><?php echo gettext("Codeblock 2"); ?></a></li>
					<li><a href="#third"><?php echo gettext("Codeblock 3"); ?></a></li>
				</ul>
					<?php
							$getcodeblock = $album->getCodeblock();
							if(!empty($getcodeblock)) {
								$codeblock = unserialize($getcodeblock);
							} else {
								$codeblock[1] = "";
								$codeblock[2] = "";
								$codeblock[3] = "";
							}
							?>
				<div id="first">
					<textarea name="<?php echo $prefix; ?>codeblock1" id="codeblock1<?php echo $suffix; ?>" rows="40" cols="60"><?php echo html_encode($codeblock[1]); ?></textarea>
				</div>
				<div id="second">
					<textarea name="<?php echo $prefix; ?>codeblock2" id="codeblock2<?php echo $suffix; ?>" rows="40" cols="60"><?php echo html_encode($codeblock[2]); ?></textarea>
				</div>
				<div id="third">
					<textarea name="<?php echo $prefix; ?>codeblock3" id="codeblock3<?php echo $suffix; ?>" rows="40" cols="60"><?php echo html_encode($codeblock[3]); ?></textarea>
				</div>
			</div>
		</td>
		</tr>
				</table>
			</td>
			<?php	$bglevels = array('#fff','#f8f8f8','#efefef','#e8e8e8','#dfdfdf','#d8d8d8','#cfcfcf','#c8c8c8');	?>
			<td valign="top">
				<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
				<div class="box-edit">

						<label class="checkboxlabel">
							<input type="checkbox" name="<?php	echo $prefix; ?>Published" value="1" <?php if ($album->getShow()) echo ' checked="checked"';	?> />
							<?php echo gettext("Published");?>
						</label>
						<label class="checkboxlabel">
							<input type="checkbox" name="<?php echo $prefix.'allowcomments';?>" value="1" <?php if ($album->getCommentsAllowed()) { echo ' checked="checked"'; } ?> />
							<?php echo gettext("Allow Comments"); ?>
						</label>
						<?php
						$hc = $album->get('hitcounter');
						if (empty($hc)) { $hc = '0'; }
						?>
						<label class="checkboxlabel">
							<input type="checkbox" name="<?php echo $prefix; ?>reset_hitcounter" />
							<?php echo sprintf(gettext("Reset hitcounter (%u hits)"), $hc); ?>
						</label>
						<?php
						$tv = $album->get('total_value');
						$tc = $album->get('total_votes');

						if ($tc > 0) {
							$hc = $tv/$tc;
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
						?>

					<br clear="all" />
				</div>
				<!-- **************** Move/Copy/Rename ****************** -->
				<h2 class="h2_bordered_edit"><?php echo gettext("Utilities"); ?></h2>
				<div class="box-edit">

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>move" name="a-<?php echo $prefix; ?>MoveCopyRename" value="move"
								onclick="toggleAlbumMoveCopyRename('<?php echo $prefix; ?>', 'movecopy');"/>
							<?php echo gettext("Move");?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>copy" name="a-<?php echo $prefix; ?>MoveCopyRename" value="copy"
								onclick="toggleAlbumMoveCopyRename('<?php echo $prefix; ?>', 'movecopy');"/>
							<?php echo gettext("Copy");?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>rename" name="a-<?php echo $prefix; ?>MoveCopyRename" value="rename"
								onclick="toggleAlbumMoveCopyRename('<?php echo $prefix; ?>', 'rename');"/>
							<?php echo gettext("Rename Folder");?>
						</label>
						<label class="checkboxlabel">
								<input type="radio" id="Delete-<?php echo $prefix; ?>" name="a-<?php echo $prefix; ?>MoveCopyRename" value="delete"
									onclick="image_deleteconfirm(this,'<?php echo $prefix; ?>',deleteAlbum1)" />
							<?php echo gettext("Delete album");?>
						</label>
						<br clear="all" />
						<div class="deletemsg" id="deletemsg<?php echo $prefix; ?>"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
						<?php echo gettext('Album will be deleted when changes are applied.'); ?>
						<p class="buttons"><a	href="javascript:toggleMoveCopyRename('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a></p>
						</div>
					<div id="a-<?php echo $prefix; ?>movecopydiv" style="padding-top: .5em; padding-left: .5em; display: none;">
						<?php echo gettext("to:"); ?>
						<select id="a-<?php echo $prefix; ?>albumselectmenu" name="a-<?php echo $prefix; ?>albumselect" onchange="">
							<?php
							$exclude = $album->name;
							if (count(explode('/', $exclude))>1 && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
								?>
								<option value="" selected="selected">/</option>
								<?php
							}
							foreach ($mcr_albumlist as $fullfolder => $albumtitle) {
								// don't allow copy in place or to subalbums
								if ($fullfolder==dirname($exclude) || $fullfolder==$exclude || strpos($fullfolder, $exclude.'/')===0) {
									$disabled =' disabled="disabled"';
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
									$salevel = ($salevel+1) % 8;
								}
								echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: '.$bglevels[$salevel].';"' : '')
								. "$disabled>". $saprefix . $singlefolder ."</option>\n";
							}
							?>
						</select>
						<br clear="all" /><br />
						<p class="buttons">
							<a href="javascript:toggleAlbumMoveCopyRename('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a>
						</p>
					</div>
					<div id="a-<?php echo $prefix; ?>renamediv" style="padding-top: .5em; padding-left: .5em; display: none;">
						<?php echo gettext("to:"); ?>
						<input name="a-<?php echo $prefix; ?>renameto" type="text" value="<?php echo basename($album->name);?>"/><br />
						<br clear="all" />
						<p class="buttons">
						<a href="javascript:toggleAlbumMoveCopyRename('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo gettext("Cancel");?></a>
						</p>
					</div>
					<span style="line-height: 0em;"><br clear="all" /></span>
					<?php
					echo zp_apply_filter('edit_album_utilities', '', $album, $prefix);
					?>
					<span style="line-height: 0em;"><br clear="all" /></span>
					</div>
					<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?></h2>
					<div class="box-edit-unpadded">
						<?php
						$tagsort = getTagOrder();
						tagSelector($album, 'tags_'.$prefix, false, $tagsort);
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
							<td><?php echo html_encode(urldecode($album->getSearchParams(true))); ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<?php
	}
?>


<br clear="all" />
	<span class="buttons">
		<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit'.$parent; ?>">
		<img	src="images/arrow_left_blue_round.png" alt="" />
		<strong><?php echo gettext("Back"); ?></strong>
		</a>
		<button type="submit" title="<?php echo gettext("Apply"); ?>">
		<img	src="images/pass.png" alt="" />
		<strong><?php echo gettext("Apply"); ?></strong>
		</button>
		<button type="reset" title="<?php echo gettext("Reset"); ?>" onclick="javascript:$('.deletemsg').hide();">
		<img	src="images/fail.png" alt="" />
		<strong><?php echo gettext("Reset"); ?></strong>
		</button>
		<div class="floatright">
		<?php
		if (!$album->isDynamic()) {
			?>
			<button type="button" title="<?php echo gettext('New subalbum'); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>',true);">
			<img src="images/folder.png" alt="" />
			<strong><?php echo gettext('New subalbum'); ?></strong>
			</button>
			<?php
		}
		?>
		<a title="<?php echo gettext('View Album'); ?>" href="<?php echo WEBPATH . "/index.php?album=". pathurlencode($album->getFolder()); ?>">
		<img src="images/view.png" alt="" />
		<strong><?php echo gettext('View Album'); ?></strong>
		</a>
		</div>
	</span>
<br clear="all" />
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
		<form name="clear-cache" action="?action=clear_cache" method="post" style="float: left">
			<?php XSRFToken('clear_cache');?>
			<input type="hidden" name="action" value="clear_cache" />
			<input type="hidden" name="album" value="<?php echo html_encode($album->name); ?>" />
			<div class="buttons">
			<button type="submit" class="tooltip" id="edit_hitcounter_album" title="<?php echo gettext("Clears the album's cached images.");?>">
				<img src="images/edit-delete.png" style="border: 0px;" alt="delete" />
				<?php echo gettext("Clear album cache"); ?>
			</button>
			</div>
		</form>

		<?php
		if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/'.UTILITIES_FOLDER.'/cache_images.php')) {
		?>
			<form name="cache_images" action="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.UTILITIES_FOLDER; ?>/cache_images.php" method="post">
				<?php XSRFToken('cache_images');?>
				<input type="hidden" name="album" value="<?php echo html_encode($album->name); ?>" />
				<input type="hidden" name="return" value="<?php echo html_encode($album->name); ?>" />
				<div class="buttons">
				<button type="submit" class="tooltip" id="edit_cache2" title="<?php echo gettext("Cache newly uploaded images."); ?>">
				<img src="images/cache1.png" style="border: 0px;" alt="cache" />
				<?php echo gettext("Pre-Cache Images"); ?></button>
				</div>
			</form>
		<?php
		}
		?>
		<form name="reset_hitcounters" action="?action=reset_hitcounters" method="post">
			<?php XSRFToken('hitcounters');?>
			<input type="hidden" name="action" value="reset_hitcounters" />
			<input type="hidden" name="albumid" value="<?php echo $album->getAlbumID(); ?>" />
			<input type="hidden" name="album" value="<?php echo html_encode($album->name); ?>" />
			<div class="buttons">
			<button type="submit" class="tooltip" id="edit_hitcounter_all" title="<?php echo gettext("Resets all hitcounters in the album."); ?>">
			<img src="images/reset1.png" style="border: 0px;" alt="reset" /> <?php echo gettext("Reset hitcounters"); ?>
			</button>
			</div>
		</form>
	<?php
	}
	if ($imagcount || (!$album->isDynamic() && $album->getNumAlbums()>0)) {
	?>
		<form name="refresh_metadata" action="admin-refresh-metadata.php?album=<?php echo pathurlencode($album->name); ?>" method="post">
			<?php XSRFToken('refresh');?>
			<input type="hidden" name="album" value="<?php echo html_encode($album->name);?>" />
			<input type="hidden" name="return" value="<?php echo html_encode($album->name); ?>" />
			<div class="buttons">
			<button type="submit" class="tooltip" id="edit_refresh" title="<?php echo gettext("Forces a refresh of the EXIF and IPTC data for all images in the album."); ?>">
			<img src="images/refresh.png" style="border: 0px;" alt="refresh" /> <?php echo gettext("Refresh Metadata"); ?></button>
			</div>
		</form>
	<?php
	}
	?>
	<br /><br />
	<?php
}

function printAlbumLedgend() {
	?>
	<ul class="iconlegend-l">
		<li><img src="images/folder_picture.png" alt="" /><?php echo gettext("Albums"); ?></li>
		<li><img src="images/pictures.png" alt="" /><?php echo gettext("Images"); ?></li>
		<li><img src="images/folder_picture_dn.png" alt="" /><?php echo gettext("Albums (dynamic)"); ?></li>
		<li><img src="images/pictures_dn.png" alt="I" /><?php echo gettext("Images (dynamic)"); ?></li>
	</ul>
	<ul class="iconlegend">
		<?php
		if (GALLERY_SECURITY != 'private') {
			?>
			<li><img src="images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
			<?php
		}
		?>
		<li><img src="images/pass.png" alt="Published" /><img src="images/action.png" alt="" /><?php echo gettext("Published/Un-published"); ?></li>
		<li><img src="images/comments-on.png" alt="" /><img src="images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
		<li><img src="images/view.png" alt="" /><?php echo gettext("View the album"); ?></li>
		<li><img src="images/cache.png" alt="" /><?php echo gettext("Cache the album"); ?></li>
		<li><img src="images/refresh1.png" alt="" /><?php echo gettext("Refresh metadata"); ?></li>
		<li><img src="images/reset.png" alt="" /><?php echo gettext("Reset hitcounters"); ?></li>
		<li><img src="images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
	</ul>
	<?php
}

/**
 * puts out a row in the edit album table
 *
 * @param object $album is the album being emitted
 * @param bool $show_thumb set to false to show thumb standin image rather than album thumb
 *
 **/
function printAlbumEditRow($album, $show_thumb) {
	$enableEdit = $album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT;
	?>
	<div class='page-list_row'>

	<div class="page-list_albumthumb">
		<?php
		if ($show_thumb) {
			$thumbimage = $album->getAlbumThumbImage();
			$thumb = $thumbimage->getCustomImage(40,NULL,NULL,40,40,NULL,NULL,-1,NULL);
		} else {
			$thumb = 'images/thumb_standin.png';
		}
		if ($enableEdit) {
			?>
			<a href="?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
			<?php
		}
		?>
			<img src="<?php echo html_encode($thumb); ?>" width="40" height="40" alt="" title="album thumb" />
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
			<a href="?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
			<?php
		}
		echo $album->getTitle();
		if ($enableEdit) {
			?>
			</a>
			<?php
		}
		?>
	</div>
	<?php
	if ($album->isDynamic()) {
		$imgi = '<img src="images/pictures_dn.png" alt="" title="'.gettext('images').'" />';
		$imga = '<img src="images/folder_picture_dn.png" alt="" title="'.gettext('albums').'" />';
	} else {
		$imgi = '<img src="images/pictures.png" alt="" title="'.gettext('images').'" />';
		$imga = '<img src="images/folder_picture.png" alt="" title="'.gettext('albums').'" />';
	}
	$ci = count($album->getImages());
	$si = sprintf('%1$s <span>(%2$u)</span>', $imgi,$ci);
	if ($ci > 0 && !$album->isDynamic()) {
		$si = '<a href="?page=edit&amp;album=' . pathurlencode($album->name) .'&amp;tab=imageinfo" title="'.gettext('Subalbum List').'">'.$si.'</a>';
	}
	$ca = $album->getNumAlbums();
	$sa = sprintf('%1$s <span>(%2$u)</span>',$imga,$ca);
	if ($ca > 0 && !$album->isDynamic()) {
		$sa = '<a href="?page=edit&amp;album=' . pathurlencode($album->name) .'&amp;tab=subalbuminfo" title="'.gettext('Subalbum List').'">'.$sa.'</a>';
	}
	?>
	<div class="page-list_extra"><?php echo $sa; ?></div>
	<div class="page-list_extra"><?php echo $si; ?></div>
	<?php	$wide='40px'; ?>
	<div class="page-list_iconwrapperalbum">
		<div class="page-list_icon">
		<?php
		$pwd = $album->getPassword();
		if (!empty($pwd) && (GALLERY_SECURITY != 'private')) {			echo '<a title="'.gettext('Password protected').'"><img src="images/lock.png" style="border: 0px;" alt="" title="'.gettext('Password protected').'" /></a>';
		}
	 ?>
		</div>
		<div class="page-list_icon">
		<?php
		if ($album->getShow()) {
			if ($enableEdit) {
				?>
				<a href="?action=publish&amp;value=0&amp;album=<?php echo pathurlencode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit')?>" title="<?php echo sprintf(gettext('Un-publish the album %s'), $album->name); ?>" >
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
				<a href="?action=publish&amp;value=1&amp;album=<?php echo pathurlencode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit')?>" title="<?php echo sprintf(gettext('Publish the album %s'), $album->name); ?>">
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
					<a href="?commentson=1&amp;id=<?php echo $album->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit')?>" title="<?php echo gettext('Disable comments'); ?>">
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
					<a href="?commentson=0&amp;id=<?php echo $album->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit')?>" title="<?php echo gettext('Enable comments'); ?>">
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
			<a href="<?php echo WEBPATH; ?>/index.php?album=<?php echo pathurlencode($album->name); ?>" title="<?php echo gettext("View album"); ?>">
			<img src="images/view.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('View album %s'), $album->name); ?>" />
			</a>
		</div>
		<?php
		if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/'.UTILITIES_FOLDER.'/cache_images.php')) {
		?>
			<div class="page-list_icon">
				<?php
				if ($album->isDynamic() || !$enableEdit) {
					?>
					<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
					<?php
				} else {
					?>
					<a class="cache" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.UTILITIES_FOLDER; ?>/cache_images.php?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>&amp;return=*<?php echo pathurlencode(dirname($album->name)); ?>&amp;XSRFToken=<?php echo getXSRFToken('cache_images')?>" title="<?php echo sprintf(gettext('Pre-cache images in %s'), $album->name); ?>">
					<img src="images/cache1.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Cache the album %s'), $album->name); ?>" />
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
			if ($album->isDynamic() || !$enableEdit) {
				?>
				<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
				<?php
			} else {
				?>
				<a class="warn" href="admin-refresh-metadata.php?page=edit&amp;album=<?php echo pathurlencode($album->name); ?>&amp;return=*<?php echo pathurlencode(dirname($album->name)); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh')?>" title="<?php echo sprintf(gettext('Refresh metadata for the album %s'), $album->name); ?>">
				<img src="images/refresh1.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Refresh metadata in the album %s'), $album->name); ?>" />
				</a>
				<?php
			}
			?>
		</div>
		<div class="page-list_icon">
			<?php
			if ($album->isDynamic() || !$enableEdit) {
				?>
				<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
				<?php
			} else {
				?>
				<a class="reset" href="?action=reset_hitcounters&amp;albumid=<?php echo $album->getAlbumID(); ?>&amp;album=<?php echo pathurlencode($album->name);?>&amp;subalbum=true&amp;XSRFToken=<?php echo getXSRFToken('hitcounter')?>" title="<?php echo sprintf(gettext('Reset hitcounters for album %s'), $album->name); ?>">
				<img src="images/reset.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Reset hitcounters for the album %s'), $album->name); ?>" />
				</a>
				<?php
			}
			?>
		</div>
		<div class="page-list_icon">
			<?php
			if (!$enableEdit) {
				?>
				<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
				<?php
			} else {
				?>
				<a class="delete" href="javascript:confirmDeleteAlbum('?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($album->name)); ?>&amp;return=*<?php echo pathurlencode(dirname($album->name)); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete')?>');" title="<?php echo sprintf(gettext("Delete the album %s"), js_encode($album->name)); ?>">
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
					<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $album->getFolder(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
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
 *@return string error flag if passwords don't match
 *@since 1.1.3
 */
function processAlbumEdit($index, $album, &$redirectto) {
	global $gallery;
	$redirectto = ''; // no redirection required
	if ($index == 0) {
		$prefix = '';
	} else {
		$prefix = "$index-";
	}
	$tagsprefix = 'tags_'.$prefix;
	$notify = '';
	$album->setTitle(process_language_string_save($prefix.'albumtitle', 2));
	$album->setDesc(process_language_string_save($prefix.'albumdesc', 1));
	$tags = array();
	$l = strlen($tagsprefix);
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if (substr($key, 0, $l) == $tagsprefix) {
			if ($value) {
				$tags[] = substr($key, $l);
			}
		}
	}
	$tags = array_unique($tags);
	$album->setTags($tags);
	$album->setDateTime(sanitize($_POST[$prefix."albumdate"]));
	$album->setLocation(process_language_string_save($prefix.'albumlocation', 3));
	if (isset($_POST[$prefix.'thumb'])) $album->setAlbumThumb(sanitize($_POST[$prefix.'thumb']));
	$album->setShow(isset($_POST[$prefix.'Published']));
	$album->setCommentsAllowed(isset($_POST[$prefix.'allowcomments']));
	$sorttype = strtolower(sanitize($_POST[$prefix.'sortby'], 3));
	if ($sorttype == 'custom') {
		$sorttype = unquote(strtolower(sanitize($_POST[$prefix.'customimagesort'],3)));
	}
	$album->setSortType($sorttype);
	if (($sorttype == 'manual') || ($sorttype == 'random')) {
		$album->setSortDirection('image', 0);
	} else {
		if (empty($sorttype)) {
			$direction = 0;
		} else {
			$direction = isset($_POST[$prefix.'image_sortdirection']);
		}
		$album->setSortDirection('image', $direction);
	}
	$sorttype = strtolower(sanitize($_POST[$prefix.'subalbumsortby'],3));
	if ($sorttype == 'custom') $sorttype = strtolower(sanitize($_POST[$prefix.'customalbumsort'],3));
	$album->setSubalbumSortType($sorttype);
	if (($sorttype == 'manual') || ($sorttype == 'random')) {
		$album->setSortDirection('album', 0);
	} else {
		$album->setSortDirection('album', isset($_POST[$prefix.'album_sortdirection']));
	}
	if (isset($_POST[$prefix.'reset_hitcounter'])) {
		$album->set('hitcounter',0);
	}
	if (isset($_POST[$prefix.'reset_rating'])) {
		$album->set('total_value', 0);
		$album->set('total_votes', 0);
		$album->set('used_ips', 0);
	}
	$fail = '';
	if (sanitize($_POST[$prefix.'password_enabled'])) {
		$olduser = $album->getUser();
		$newuser = $_POST[$prefix.'albumuser'];
		$pwd = trim($_POST[$prefix.'albumpass']);
		if (($olduser != $newuser)) {
			if (!empty($newuser) && empty($pwd) && empty($pwd2)) $fail = '&mismatch=user';
		}
		if (!$fail && $_POST[$prefix.'albumpass'] == $_POST[$prefix.'albumpass_2']) {
			$album->setUser($newuser);
			if (empty($pwd)) {
				if (empty($_POST[$prefix.'albumpass'])) {
					$album->setPassword(NULL);  // clear the gallery password
				}
			} else {
				$album->setPassword($pwd);
			}
		} else {
			if (empty($fail)) {
				$notify = '&mismatch=album';
			} else {
				$notify = $fail;
			}
		}
	}
	$oldtheme = $album->getAlbumTheme();
	if (isset($_POST[$prefix.'album_theme'])) {
		$newtheme = sanitize($_POST[$prefix.'album_theme']);
		if ($oldtheme != $newtheme) {
			$album->setAlbumTheme($newtheme);
		}
	}
	$album->setPasswordHint(process_language_string_save($prefix.'albumpass_hint', 3));
	if (isset($_POST[$prefix.'album_watermark'])) {
		$album->setWatermark(sanitize($_POST[$prefix.'album_watermark'], 3));
		$album->setWatermarkThumb(sanitize($_POST[$prefix.'album_watermark_thumb'], 3));
	}
	$codeblock1 = sanitize($_POST[$prefix.'codeblock1'], 0);
	$codeblock2 = sanitize($_POST[$prefix.'codeblock2'], 0);
	$codeblock3 = sanitize($_POST[$prefix.'codeblock3'], 0);
	$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
	$album->setCodeblock($codeblock);
	if (isset($_POST[$prefix.'-owner'])) $album->setOwner(sanitize($_POST[$prefix.'-owner']));

	$custom = process_language_string_save($prefix.'album_custom_data', 1);
	$album->setCustomData(zp_apply_filter('save_album_custom_data', $custom, $prefix));
	zp_apply_filter('save_album_utilities_data', $album, $prefix);
	$album->save();

	// Move/Copy/Rename the album after saving.
	$movecopyrename_action = '';
	if (isset($_POST['a-'.$prefix.'MoveCopyRename'])) {
		$movecopyrename_action = sanitize($_POST['a-'.$prefix.'MoveCopyRename'],3);
	}

	if ($movecopyrename_action == 'delete') {
		$dest = dirname($album->name);
		if ($album->remove()) {
			if ($dest == '/' || $dest == '.') $dest = '';
			$redirectto = $dest;
		} else {
			$notify = "&mcrerr=7";
		}
	}
	if ($movecopyrename_action == 'move') {
		$dest = trim(sanitize_path($_POST['a'.$prefix.'-albumselect'],3));
		// Append the album name.
		$dest = ($dest ? $dest . '/' : '') . (strpos($album->name, '/') === FALSE ? $album->name : basename($album->name));
		if ($dest && $dest != $album->name) {
			if ($album->isDynamic()) { // be sure there is a .alb suffix
				if (substr($dest, -4) != '.alb') {
					$dest .= '.alb';
				}
			}
			if ($e = $album->moveAlbum($dest)) {
				$notify = "&mcrerr=".$e;
			} else {
				$redirectto = $dest;
			}
		} else {
			// Cannot move album to same album.
			$notify = "&mcrerr=3";
		}
	} else if ($movecopyrename_action == 'copy') {
		$dest = trim(sanitize_path($_POST['a'.$prefix.'-albumselect']));
		if ($dest && $dest != $album->name) {
			if($e = $album->copy($dest)) {
				$notify = "&mcrerr=".$e;
			}
		} else {
			// Cannot copy album to existing album.
			// Or, copy with rename?
			$notify = '&mcrerr=3';
		}
	} else if ($movecopyrename_action == 'rename') {
		$renameto = trim(sanitize_path($_POST['a'.$prefix.'-renameto'],3));
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
				$notify = "&mcrerr=".$e;
			} else {
				$redirectto = $renameto;
			}
		} else {
			$notify = "&mcrerr=3";
		}
	}
	return $notify;
}

/**
 * Searches the zenphoto.org home page for the current zenphoto download
 * locates the version number of the download and compares it to the version
 * we are running.
 *
 *@rerturn string If there is a more current version on the WEB, returns its version number otherwise returns FALSE
 *@since 1.1.3
 */
function checkForUpdate() {
	global $_zp_WEB_Version;
	if (isset($_zp_WEB_Version)) { return $_zp_WEB_Version; }
	if (!is_connected()) return 'X';
	$c = ZENPHOTO_VERSION;
	$v = @file_get_contents('http://www.zenphoto.org/files/LATESTVERSION');
	if (empty($v)) {
		$_zp_WEB_Version = 'X';
	} else {
		if ($i = strpos($v, 'RC')) {
			$v_candidate = intval(substr($v, $i+2));
		} else {
			$v_candidate = 9999;
		}
		if ($i = strpos($c, 'RC')) {
			$c_candidate = intval(substr($c, $i+2));
		} else {
			$c_candidate = 9999;
		}
		$pot = array(1000000000, 10000000, 100000, 1);
		$wv = explode('.', $v);
		$wvd = 0;
		foreach ($wv as $i => $d) {
			$wvd = $wvd + $d * $pot[$i];
		}
		$cv = explode('.', $c);
		$cvd = 0;
		foreach ($cv as $i => $d) {
			$cvd = $cvd + $d * $pot[$i];
		}
		if ($wvd > $cvd || (($wvd == $cvd) && ($c_candidate < $v_candidate))) {
			$_zp_WEB_Version = $v;
		} else {
			$_zp_WEB_Version = '';
		}
	}
	Return $_zp_WEB_Version;
}

function adminPageNav($pagenum,$totalpages,$adminpage,$parms,$tab='') {
	if (empty($parms)) {
		$url = '?';
	} else {
		$url = $parms.'&amp;';
	}
	echo '<ul class="pagelist"><li class="prev">';
	if ($pagenum > 1) {
		echo '<a href="'.$url.'subpage='.($p=$pagenum-1).$tab.'" title="'.sprintf(gettext('page %u'),$p).'">'.'&laquo; '.gettext("Previous page").'</a>';
	} else {
		echo '<span class="disabledlink">&laquo; '.gettext("Previous page").'</span>';
	}
	echo "</li>";
	$start = max(1,$pagenum-7);
	$total = min($start+15,$totalpages+1);
	if ($start != 1) { echo "\n <li><a href=".$url.'subpage='.($p=max($start-8, 1)).$tab.' title="'.sprintf(gettext('page %u'),$p).'">. . .</a></li>'; }
	for ($i=$start; $i<$total; $i++) {
		if ($i == $pagenum) {
			echo "<li class=\"current\">".$i.'</li>';
		} else {
			echo '<li><a href="'.$url.'subpage='.$i.$tab.'" title="'.sprintf(gettext('page %u'),$i).'">'.$i.'</a></li>';
		}
	}
	if ($i < $totalpages) { echo "\n <li><a href=".$url.'subpage='.($p=min($pagenum+22,$totalpages+1)).$tab.' title="'.sprintf(gettext('page %u'),$p).'">. . .</a></li>'; }
	echo "<li class=\"next\">";
	if ($pagenum<$totalpages) {
		echo '<a href="'.$url.'subpage='.($p=$pagenum+1).$tab.'" title="'.sprintf(gettext('page %u'),$p).'">'.gettext("Next page").' &raquo;'.'</a>';
	} else {
		echo '<span class="disabledlink">'.gettext("Next page").' &raquo;</span>';
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
function print_language_string_list($dbstring, $name, $textbox=false, $locale=NULL, $edit='', $wide=TEXT_INPUT_SIZE, $ulclass='language_string_list', $rows=6) {
	global $_zp_active_languages, $_zp_current_locale;
	if (!empty($edit)) $edit = ' class="'.$edit.'"';
	if (empty($id)) {
		$groupid ='';
	} else {
		$groupid = ' id="'.$id.'"';
	}
	if (is_null($locale)) {
		if (is_null($_zp_current_locale)) {
			$_zp_current_locale = getUserLocale();
		}
		$locale = $_zp_current_locale;
	}
	if (preg_match('/^a:[0-9]+:{/', $dbstring)) {
		$strings = unserialize($dbstring);
	} else {
		$strings = array($locale=>$dbstring);
	}
	if (getOption('multi_lingual')) {
		$emptylang = generateLanguageList();
		$emptylang = array_flip($emptylang);
		unset($emptylang['']);
		if ($textbox) $class = 'box'; else $class = '';
		echo '<ul'.$groupid.' class="'.$ulclass.$class.'"'.">\n";
		$empty = true;
		foreach ($emptylang as $key=>$lang) {
			if (isset($strings[$key])) {
				$string = $strings[$key];
				if (!empty($string)) {
					unset($emptylang[$key]);
					$empty = false;
					?>
					<li>
						<label for="<?php echo $name.'_'.$key; ?>"><?php echo $lang; ?></label>
						<?php
						if ($textbox) {
							echo "\n".'<textarea name="'.$name.'_'.$key.'"'.$edit.' cols="'.$wide.'"	rows="'.$rows.'">'.html_encode($string).'</textarea>';
						} else {
							echo '<br /><input id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" type="text" value="'.html_encode($string).'" size="'.$wide.'" />';
						}
						?>
					</li>
					<?php
				}
			}
		}
		if ($empty) {
			$element = $emptylang[$locale];
			unset($emptylang[$locale]);
			$emptylang = array_merge(array($locale=>$element), $emptylang);
		}
		foreach ($emptylang as $key=>$lang) {
			echo '<li><label for="'.$name.'_'.$key.'"></label>';
			echo $lang;
			if ($textbox) {
				echo "\n".'<textarea name="'.$name.'_'.$key.'"'.$edit.' cols="'.$wide.'"	rows="'.$rows.'"></textarea>';
			} else {
				echo '<br /><input id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" type="text" value="" size="'.$wide .'" />';
			}
			echo "</li>\n";

		}
		echo "</ul>\n";
	} else {
		if (empty($locale)) $locale = 'en_US';
		if (isset($strings[$locale])) {
			$dbstring = $strings[$locale];
		} else {
			$dbstring = array_shift($strings);
		}
		if ($textbox) {
			echo '<textarea'.$groupid.' name="'.$name.'_'.$locale.'"'.$edit.' cols="'.$wide.'"	rows="'.$rows.'">'.html_encode($dbstring).'</textarea>';
		} else {
			echo '<input'.$groupid.' name="'.$name.'_'.$locale.'" type="text" value="'.html_encode($dbstring).'" size="'.$wide.'" />';
		}
	}
}

/**
 * process the post of a language string form
 *
 * @param string $name the prefix for the label, id, and name tags
 * @param $sanitize_level the type of sanitization required
 * @param bool $cleanup set to true to clean up after the TinyMCE editor
 * @return string
 */
function process_language_string_save($name, $sanitize_level=3) {
	global $_zp_active_languages;
	$languages = generateLanguageList();
	$l = strlen($name)+1;
	$strings = array();
	foreach ($_POST as $key=>$value) {
		if (!empty($value) && preg_match('/^'.$name.'_[a-z]{2}_[A-Z]{2}$/', $key)) {
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
		case 1:
			return array_shift($strings);
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
		$tagsort = sanitize($_REQUEST['tagsort'], 0);
		setBoolOption('tagsort', $tagsort);
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
	if(function_exists('zip_open')) {
		$zip = zip_open($file);
		if ($zip) {
			while ($zip_entry = zip_read($zip)) { // Skip non-images in the zip file.
				$fname = zip_entry_name($zip_entry);
				$seoname = internalToFilesystem(seoFriendly($fname));
				if (is_valid_image($seoname) || is_valid_other_type($seoname)) {
					if (zip_entry_open($zip, $zip_entry, "r")) {
						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						$path_file = str_replace("/",DIRECTORY_SEPARATOR, $dir . '/' . $seoname);
						$fp = fopen($path_file, "w");
						fwrite($fp, $buf);
						fclose($fp);
						clearstatcache();
						zip_entry_close($zip_entry);
						$albumname = substr($dir, strlen(ALBUM_FOLDER_SERVERPATH));
						$album = new Album(new Gallery(), $albumname);
						$image = newImage($album, $seoname);
						if ($fname != $seoname) {
							$image->setTitle($name);
							$image->save();
						}
					}
				}
			}
			zip_close($zip);
		}
	} else {
		require_once(dirname(__FILE__).'/lib-pclzip.php');
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
	$str = wordwrap(strip_tags($str), 75, '\n');
	$lines = explode('\n', $str);
	$str = implode('%0D%0A', $lines);
	$str = "$name commented on $imagetitle in the album $albumtitle: %0D%0A%0D%0A" . $str;
	return $str;
}

/**
 * Extracts and returns a 'statement' from a PHP script for so that it may be 'evaled'
 *
 * @param string $target the pattern to match on
 * @param string $str the PHP script
 * @return string
 */
function isolate($target, $str) {
	$i = strpos($str, $target);
	if ($i === false) return false;
	$str = substr($str, $i);
	$j = strpos($str, ";"); // This is also wrong; it disallows semicolons in strings. We need a regexp.
	$k = strpos($str, "\n", $j+1);
	if ($k === false) {
		$k = $j;	//	best guess.
	}
	$str = substr($str, 0, $k);
	return $str;
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
function listDirectoryFiles( $dir ) {
	$file_list = array();
	$stack[] = $dir;
	while ($stack) {
		$current_dir = array_pop($stack);
		if ($dh = @opendir($current_dir)) {
			while (($file = @readdir($dh)) !== false) {
				if ($file !== '.' AND $file !== '..') {
					$current_file = "{$current_dir}/{$file}";
					if ( is_file($current_file) && is_readable($current_file) ) {
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
 * Check if a file is a text file
 *
 * @param string $file
 * @param array $ok_extensions array of file extensions that are OK to edit (ie text files)
 * @return bool
 * @author Ozh
 * @since 1.3
 */
function isTextFile ( $file, $ok_extensions = array('css','php','js','txt','inc') ) {
	$path_info = pathinfo($file);
	$ext = (isset($path_info['extension']) ? $path_info['extension'] : '');
	return ( !empty ( $ok_extensions ) && (in_array( $ext, $ok_extensions ) ) );
}

/**
 * Check if a theme is editable (ie not a bundled theme)
 *
 * @param $theme theme to check
 * @param $themes array of installed themes (eg result of getThemes())
 * @return bool
 * @since 1.3
 */
function themeIsEditable($theme, $themes) {
	$zplist = unserialize(getOption('Zenphoto_theme_list'));
	return (!in_array( $theme , $zplist));
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
	$source  = SERVERPATH . '/themes/'.internalToFilesystem($source);
	$target  = SERVERPATH . '/themes/'.internalToFilesystem($target);

	// If the target theme already exists, nothing to do.
	if ( is_dir($target)) {
		return gettext('Cannot create new theme.') .' '. sprintf(gettext('Directory "%s" already exists!'), basename($target));
	}

	// If source dir is missing, exit too
	if ( !is_dir($source)) {
		return gettext('Cannot create new theme.') .' '.sprintf(gettext('Cannot find theme directory "%s" to copy!'), basename($source));
	}

	// We must be able to write to the themes dir.
	if (! is_writable( dirname( $target) )) {
		return gettext('Cannot create new theme.') .' '.gettext('The <tt>/themes</tt> directory is not writable!');
	}

	// We must be able to create the directory
	if (! mkdir($target, CHMOD_VALUE)) {
		return gettext('Cannot create new theme.') .' '.gettext('Could not create directory for the new theme');
	}
	chmod($target, CHMOD_VALUE);

	// Get a list of files to copy: get all files from the directory, remove those containing '/.svn/'
	$source_files = array_filter( listDirectoryFiles( $source ), create_function('$str', 'return strpos($str, "/.svn/") === false;') );

	// Determine nested (sub)directories structure to create: go through each file, explode path on "/"
	// and collect every unique directory
	$dirs_to_create = array();
	foreach ( $source_files as $path ) {
		$path = dirname ( str_replace( $source . '/', '', $path ) );
		$path = explode ('/', $path);
		$dirs = '';
		foreach ( $path as $subdir ) {
			if ( $subdir == '.svn' or $subdir == '.' ) {
				continue 2;
			}
			$dirs = "$dirs/$subdir";
			$dirs_to_create[$dirs] = $dirs;
		}
	}
	/*
	Example result for theme 'effervescence_plus': $dirs_to_create = array (
		'/styles' => '/styles',
		'/scripts' => '/scripts',
		'/images' => '/images',
		'/images/smooth' => '/images/smooth',
		'/images/slimbox' => '/images/slimbox',
	);
	*/

	// Create new directory structure
	foreach ($dirs_to_create as $dir) {
		mkdir("$target/$dir", CHMOD_VALUE);
		chmod("$target/$dir", CHMOD_VALUE); // Using chmod as PHP doc suggested: "Avoid using umask() in multithreaded webservers. It is better to change the file permissions with chmod() after creating the file."
	}

	// Now copy every file
	foreach ( $source_files as $file ) {
		$newfile = str_replace($source, $target, $file);
		if (! copy("$file", "$newfile" ) )
			return sprintf(gettext("An error occurred while copying files. Please delete manually the new theme directory '%s' and retry or copy files manually."), basename($target));
		chmod("$newfile", CHMOD_VALUE);
	}

	// Rewrite the theme header.
	if ( file_exists($target.'/theme_description.php') ) {
		$theme_description = array();
		require($target.'/theme_description.php');
		$theme_description['desc'] = sprintf(gettext('Your theme, based on theme %s'), $theme_description['name']);
	} else  {
		$theme_description['desc'] = gettext('Your theme');
	}
	$theme_description['name'] = $newname;
	$theme_description['author'] = $_zp_current_admin_obj->getUser();
	$theme_description['version'] = '1.0';
	$theme_description['date']  = zpFormattedDate(DATE_FORMAT, time());

	$description = sprintf('<'.'?php
				// Zenphoto theme definition file
				$theme_description["name"] = "%s";
				$theme_description["author"] = "%s";
				$theme_description["version"] = "%s";
				$theme_description["date"] = "%s";
				$theme_description["desc"] = "%s";
				?'.'>' , html_encode($theme_description['name']),
		html_encode($theme_description['author']),
		html_encode($theme_description['version']),
		html_encode($theme_description['date']),
		html_encode($theme_description['desc']));

	$f = fopen($target.'/theme_description.php', 'w');
	if ($f !== FALSE) {
		@fwrite($f, $description);
		fclose($f);
		$message = gettext('New custom theme created successfully!');
	} else {
		$message = gettext('New custom theme created, but its description could not be updated');
	}

	// Make a slightly custom theme image
	if (file_exists("$target/theme.png")) $themeimage = "$target/theme.png";
	else if (file_exists("$target/theme.gif")) $themeimage = "$target/theme.gif";
	else if (file_exists("$target/theme.jpg")) $themeimage = "$target/theme.jpg";
	else $themeimage = false;
	if ($themeimage) {
		require_once(dirname(__FILE__).'/functions-image.php');
		if ($im = zp_imageGet($themeimage)) {
			$x = zp_imageWidth($im)/2 - 45;
			$y = zp_imageHeight($im)/2 - 10;
			$text = "CUSTOM COPY";
			$font = zp_imageLoadFont();
			$ink = zp_colorAllocate($im,0x0ff, 0x0ff, 0x0ff);
			// create a blueish overlay
			$overlay = zp_createImage(zp_imageWidth($im), zp_imageHeight($im));
			$back = zp_colorAllocate($overlay, 0x060, 0x060, 0x090);
			zp_imageFill ($overlay, 0, 0, $back);
			// Merge theme image and overlay
			zp_imageMerge($im, $overlay, 0, 0, 0, 0, zp_imageWidth($im), zp_imageHeight($im), 45);
			// Add text
			zp_writeString ( $im,  $font,  $x-1,  $y-1, $text,  $ink );
			zp_writeString ( $im,  $font,  $x+1,  $y+1, $text,  $ink );
			zp_writeString ( $im,  $font,  $x,  $y,   $text,  $ink );
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
 * @param string $source the script file incase REQUEST_URI is not available
 */
function currentRelativeURL($source) {
	$source = str_replace('\\','/',$source);
	$source = str_replace(SERVERPATH, WEBPATH, $source);
	$q = '';
	if (!empty($_GET)) {
		foreach ($_GET as $parm=>$value) {
			$q .= $parm.'='.$value.'&';
		}
		$q = '?'.substr($q,0,-1);
	}
	return pathurlencode($source.$q);
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
	foreach($parents as $parent) {
		$link .= "<a href='".WEBPATH.'/'.ZENFOLDER."/admin-edit.php?page=edit&amp;album=".pathurlencode($parent->name)."'>".removeParentAlbumNames($parent)."</a>/";
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
	$slash = stristr($album->name,"/");
	if($slash) {
		$array = explode("/",$album->name);
		$array = array_reverse($array);
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
	global $_zp_authority;
	$rightslist = sortMultiArray($_zp_authority->getRights(), array('set', 'value'));
	?>
	<div class="box-rights">
		<strong><?php echo gettext("Rights"); ?>:</strong>
		<?php
		$element = 3;
		$activeset = false;
		foreach ($rightslist as $rightselement=>$right) {
			if ($right['display']) {
				if (($right['set'] != gettext('Pages') && $right['set'] != gettext('News')) || getOption('zp_plugin_zenpage')) {
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
						<input type="checkbox" name="<?php echo $id.'-'.$rightselement; ?>" id="<?php echo $rightselement.'-'.$id; ?>"
									value="<?php echo $right['value']; ?>"<?php if ($rights & $right['value']) echo ' checked="checked"';	echo $alterrights; ?> /> <?php echo $right['name']; ?>
					</label>
					<?php
				} else {
					?>
					<input type="hidden" name="<?php echo $id.'-'.$rightselement; ?>" id="<?php echo $rightselement.'-'.$id; ?>" value="<?php echo $right['value']; ?>" />
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
 * @param int $adminid ID of the admin
 * @param int $prefix the admin row
 * @param bit $rights the privileges  of the user
 */
function printManagedObjects($type, $objlist, $alterrights, $adminid, $prefix, $rights, $kind) {
	$ledgend = '';
	switch ($type) {
		case 'albums':
			$full = populateManagedObjectsList('album', $adminid, true);
			$cv = $extra = array();
			$icon_edit_album = '<img src="'.WEBPATH.'/'.ZENFOLDER.'/images/edit-album.png" class="icon-position-top3" alt="" title="'.gettext('edit albums').'" />';
			$icon_edit_image = '<img src="'.WEBPATH.'/'.ZENFOLDER.'/images/edit-image.png" class="icon-position-top3" alt="" title="'.gettext('edit user owned images').'" />';
			$icon_upload = '<img src="'.WEBPATH.'/'.ZENFOLDER.'/images/arrow_up.png" class="icon-position-top3"  alt="" title="'.gettext('uploade to album').'"/>';
			$ledgend = $icon_edit_album.' '.gettext('edit album').' '.
//										$icon_edit_image.' '.gettext('edit owned images').' '.
										$icon_upload.' '.gettext('upload');
			foreach ($full as $item) {
				$cv[$item['name']] = $item['data'];
				$extra[$item['name']][] = array('name'=>'default','value'=>0,'display'=>'','checked'=>1);
				if ($rights & ALBUM_RIGHTS) {
					$extra[$item['name']][] = array('name'=>'edit','value'=>MANAGED_OBJECT_RIGHTS_EDIT,'display'=>$icon_edit_album,'checked'=>$item['edit']&MANAGED_OBJECT_RIGHTS_EDIT);
//					$extra[$item['name']][] = array('name'=>'editimage','value'=>MANAGED_OBJECT_RIGHTS_EDIT_IMAGE,'display'=>$icon_edit_image,'checked'=>$item['edit']&MANAGED_OBJECT_RIGHTS_EDIT_IMAGE);
				}
				if (($rights & UPLOAD_RIGHTS) && !hasDynamicAlbumSuffix($item['data'])) {
					$extra[$item['name']][] = array('name'=>'upload','value'=>MANAGED_OBJECT_RIGHTS_UPLOAD,'display'=>$icon_upload,'checked'=>$item['edit']&MANAGED_OBJECT_RIGHTS_UPLOAD);
				}
			}
			$rest = array_diff($objlist, $cv);
			$text = gettext("Managed albums:");
			$simplename = $objectname = gettext('Albums');
			$prefix = 'managed_albums_list_'.$prefix.'_';
			break;
		case 'news':
			$cv = populateManagedObjectsList('news',$adminid);
			$rest = array_diff($objlist, $cv);
			$text = gettext("Managed news categories:");
			$simplename = gettext ('News');
			$objectname = gettext ('News categories');
			$prefix = 'managed_news_list_'.$prefix.'_';
			$extra = array();
			break;
		case 'pages':
			$cv = populateManagedObjectsList('pages',$adminid);
			$rest = array_diff($objlist, $cv);
			$text = gettext("Managed pages:");
			$simplename = $objectname = gettext('Pages');
			$prefix = 'managed_pages_list_'.$prefix.'_';
			$extra = array();
			break;
	}
	if (empty($album_alter_rights)) {
		$hint = sprintf(gettext('Select one or more %1$s for the %2$s to manage.'),$simplename, $kind).' ';
		if ($kind == gettext('user')) {
			$hint .= sprintf(gettext('Users with "Admin" or "Manage all %1$s" rights can manage all %2$s. All others may manage only those that are selected.'),$type,$objectname);
		}
		} else {
		$hint = sprintf(gettext('You may manage these %s subject to the above rights.'),$simplename);
	}
	if (count($cv)>0) {
		$itemcount = ' ('.count($cv).')';
	} else {
		$itemcount = '';
	}
	?>

	<div class="box-albums-unpadded">
	<h2 class="h2_bordered_albums">
	<a href="javascript:toggle('<?php echo $prefix ?>');" title="<?php echo html_encode($hint); ?>" ><?php echo $text.$itemcount; ?></a>
	</h2>
		<div id="<?php echo $prefix ?>" style="display:none;">
			<ul class="albumchecklist">
				<?php
				generateUnorderedListFromArray($cv, $cv, $prefix, $alterrights, true, true, NULL, $extra);
				generateUnorderedListFromArray(array(), $rest, $prefix, $alterrights, true, true);
				?>
			</ul>
			<?php echo $ledgend; ?>
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
	global $_zp_authority;
	if (isset($_POST[$i.'-confirmed'])) {
		$rights = NO_RIGHTS;
	} else {
		$rights = 0;
	}
	foreach ($_zp_authority->getRights() as $name=>$right) {
		if (isset($_POST[$i.'-'.$name])) {
			$rights = $rights | $right['value'] | NO_RIGHTS;
		}
	}
	if ($rights & MANAGE_ALL_ALBUM_RIGHTS) {	// these are lock-step linked!
		$rights = $rights | VIEW_ALBUMS_RIGHTS;
	}
	return $rights;
}

function processManagedObjects($i, &$rights) {
	$objects = array();
	$albums = array();
	$pages = array();
	$news = array();
	$l_a = strlen($prefix_a = 'managed_albums_list_'.$i.'_');
	$l_p = strlen($prefix_p = 'managed_pages_list_'.$i.'_');
	$l_n = strlen($prefix_n = 'managed_news_list_'.$i.'_');
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if (substr($key, 0, $l_a) == $prefix_a) {
			$key = substr($key, $l_a);
			if (strpos($key, '_default')) {
				$key = substr($key, 0, -8);
				if (isset($albums[$key])) {	// album still part of the list
					$albums[$key]['edit'] = $value;
				}
			} else if (strpos($key, '_editimage')) {
				$key = substr($key, 0, -10);
				if (isset($albums[$key])) {	// album still part of the list
					$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT_IMAGE;
				}
			} else if (strpos($key, '_edit')) {
				$key = substr($key, 0, -5);
				if (isset($albums[$key])) {	// album still part of the list
					$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_EDIT;
				}
			} else if (strpos($key, '_upload')) {
				$key = substr($key, 0, -7);
				if (isset($albums[$key])) {	// album still part of the list
					$albums[$key]['edit'] = $albums[$key]['edit'] | MANAGED_OBJECT_RIGHTS_UPLOAD;
				}
			} else if ($value) {
				if (hasDynamicAlbumSuffix($key)) {
					$name = substr($key, 0, -4); // Strip the .'.alb' suffix
				} else {
					$name = $key;
				}
				$albums[$key] = array('data'=>$key, 'name'=>$name, 'type'=>'album');
			}
		}
		if (substr($key, 0, $l_p) == $prefix_p) {
			if ($value) {
				$pages[] = array('data'=>substr($key, $l_p),'type'=>'pages');
			}
		}
		if (substr($key, 0, $l_n) == $prefix_n) {
			if ($value) {
				$news[] = array('data'=>substr($key, $l_n),'type'=>'news');
			}
		}
	}

	foreach ($albums as $key=>$analbum) {
		unset($albums[$key]);
		$albums[] = $analbum;
	}
	$rights = 0;
	if (!empty($albums)) $rights = $rights | ALBUM_RIGHTS;
	if (!empty($pages)) $rights = $rights | ZENPAGE_PAGES_RIGHTS;
	if (!empty($news)) $rights = $rights | ZENPAGE_NEWS_RIGHTS;
	$objects = array_merge($albums,$pages,$news);
	return $objects;
}

/**
 * Returns the value of a checkbox form item
 *
 * @param string $id the $_REQUEST index
 * @return int (0 or 1)
 */
function getCheckboxState($id) {
	if (isset($_REQUEST[$id])) return 1; else return 0;
}

/**
 * Returns an array of "standard" theme scripts. This list is
 * normally used to exclude these scripts form various option seletors.
 *
 * @return array
 */
function standardScripts() {
	$standardlist = array('themeoptions', 'password', 'theme_description', '404', 'slideshow', 'search', 'image', 'index', 'album', 'customfunctions');
	if (getOption('zp_plugin_zenpage')) $standardlist = array_merge($standardlist, array('news', 'pages'));
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
	chdir($basepath = SERVERPATH."/".ZENFOLDER.'/watermarks/');
	$filelist = safe_glob('*.png');
	foreach ($filelist as $file) {
		$list[filesystemToInternal(substr(basename($file),0,-4))] = $basepath.$file;
	}
	$basepath = SERVERPATH."/".USER_PLUGIN_FOLDER.'/watermarks/';
	if (is_dir($basepath)) {
		chdir($basepath);
		$filelist = safe_glob('*.png');
		foreach ($filelist as $file) {
			$list[filesystemToInternal(substr(basename($file),0,-4))] = $basepath.$file;
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
	$result = array();
	parse_str($orderstr,$order);
	$order = array_shift($order);

	$parents = $curorder = array();
	$curowner = '';
	foreach ($order as $id=>$parent)  {	// get the root elements
		if ($parent != $curowner) {
			if (($key = array_search($parent, $parents)) === false) {	//	a child
				array_push($parents, $parent);
				array_push($curorder, -1);
			} else {																									//	roll back to parent
				$parents = array_slice($parents, 0, $key+1);
				$curorder = array_slice($curorder, 0, $key+1);
			}
		}
		$l = count($curorder)-1;
		$curorder[$l] = sprintf('%03u',$curorder[$l]+1);
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
	global $gallery;
	if (isset($_POST['order']) && !empty($_POST['order'])) {
		$order = processOrder($_POST['order']);
		$sortToID = array();
		foreach ($order as $id=>$orderlist) {
			$id = str_replace('id_','',$id);
			$sortToID[implode('-',$orderlist)] = $id;
		}
		foreach ($order as $item=>$orderlist) {
			$item = str_replace('id_','',$item);
			$currentalbum = query_single_row('SELECT * FROM '.prefix('albums').' WHERE `id`='.$item);
			$sortorder = array_pop($orderlist);
			if (count($orderlist)>0) {
				$newparent = $sortToID[implode('-',$orderlist)];
			} else {
				$newparent = $parentid;
			}
			if ($newparent == $currentalbum['parentid']) {
				$sql = 'UPDATE '.prefix('albums').' SET `sort_order`="'.$sortorder.'" WHERE `id`='.$item;
				query($sql);
			} else {	// have to do a move
				$albumname = $currentalbum['folder'];
				$album = new Album($gallery, $albumname);
				if (strpos($albumname,'/') !== false) {
					$albumname = basename($albumname);
				}
				if (is_null($newparent)) {
					$dest = $albumname;
				} else {
					$parent = query_single_row('SELECT * FROM '.prefix('albums').' WHERE `id`='.$newparent);
					if ($parent['dynamic']) {
						return "&mcrerr=5";
					} else {
						$dest = $parent['folder'].'/' . $albumname;
					}
				}
				if ($e = $album->moveAlbum($dest)) {
					return "&mcrerr=".$e;
				} else {
					$album->setSortOrder($sortorder);
					$album->save();
				}
			}
		}
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
function getNestedAlbumList($subalbum, $levels, $level=array()) {
	global $gallery;
	$cur = count($level);
	$levels--;	// make it 0 relative to sync with $cur
	if (is_null($subalbum)) {
		$albums = $gallery->getAlbums();
	} else {
		$albums = $subalbum->getAlbums();
	}
	$list = array();
	foreach ($albums as $analbum) {
		$albumobj = new Album($gallery, $analbum);
		if(!is_null($subalbum) || $albumobj->isMyItem(ALBUM_RIGHTS)) {
			$level[$cur] = sprintf('%03u',$albumobj->getSortOrder());
			$list[] = array('name'=>$analbum, 'sort_order'=>$level);
			if ($cur < $levels && ($albumobj->getNumAlbums() > 0) && !$albumobj->isDynamic()) {
				$list = array_merge($list,getNestedAlbumList($albumobj, $levels+1, $level));
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
 *
 * @return bool
 */
function printNestedAlbumsList($albums, $show_thumb) {
	global $gallery;
	$indent = 1;
	$open = array(1=>0);
	$rslt = false;
	foreach ($albums as $album) {
		$order = $album['sort_order'];
		$level = max(1,count($order));
		if ($toodeep = $level>1 && $order[$level-1] === '') {
			$rslt = true;
		}
		if ($level > $indent) {
			echo "\n".str_pad("\t",$indent,"\t")."<ul class=\"page-list\">\n";
			$indent++;
			$open[$indent] = 0;
		} else if ($level < $indent) {
				while ($indent > $level) {
					$open[$indent]--;
					$indent--;
					echo "</li>\n".str_pad("\t",$indent,"\t")."</ul>\n";
				}
		} else { // indent == level
			if ($open[$indent]) {
				echo str_pad("\t",$indent,"\t")."</li>\n";
				$open[$indent]--;
			} else {
				echo "\n";
			}
		}
		if ($open[$indent]) {
			echo str_pad("\t",$indent,"\t")."</li>\n";
			$open[$indent]--;
		}
		$albumobj = new Album($gallery,$album['name']);
		if ($albumobj->isDynamic()) {
			$nonest = ' class="no-nest"';
		} else {
			$nonest = '';
		}
		echo str_pad("\t",$indent-1,"\t")."<li id=\"id_".$albumobj->get('id')."\"$nonest >";
		printAlbumEditRow($albumobj, $show_thumb);
		$open[$indent]++;
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent]--;
		$indent--;
		echo str_pad("\t",$indent,"\t")."</ul>";
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
function printEditDropdown($subtab,$nestinglevels = array('1','2','3','4','5')) {
	global $subalbum_nesting, $gallery_nesting, $imagesTab_imageCount;
	switch ($subtab) {
		case '':
			$link = '?selection=';
			$nesting = $gallery_nesting;
			break;
		case 'subalbuminfo':
			$link = '?page=edit&amp;album='.sanitize($_GET['album'],3).'&amp;tab=subalbuminfo&amp;selection=';
			$nesting = $subalbum_nesting;
			break;
		case 'imageinfo':
			if (isset($_GET['tagsort'])) {
				$tagsort = '&amp;tagsort='.sanitize($_GET['tagsort'],3);
			} else {
				$tagsort = '';
			}
			$link = '?page=edit&amp;album='.sanitize($_GET['album'],3).'&amp;tab=imageinfo'.$tagsort.'&amp;selection=';
			$nesting = $imagesTab_imageCount;
			break;
	}
	?>
		<form name="AutoListBox2" style="float: right;" action="#" >
		<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
		<?php
		foreach ($nestinglevels as $nestinglevel) {
			if($nesting == $nestinglevel) {
				$selected = 'selected="selected"';
			} else {
				$selected ="";
			}
			echo '<option '.$selected.' value="admin-edit.php'.$link.$nestinglevel.'">';
			switch($subtab) {
				case '':
				case 'subalbuminfo':
					printf(ngettext('Show %u album level','Show %u album levels', $nestinglevel), $nestinglevel);
					break;
				case 'imageinfo':
					printf(ngettext('%u image per page','%u images per page', $nestinglevel), $nestinglevel);
					break;
			}
			echo '</option>';
		}
?>
	</select>
	<script language="JavaScript" type="text/javascript" >
		// <!-- <![CDATA[
		function gotoLink(form) {
		var OptionIndex=form.ListBoxURL.selectedIndex;
		parent.location = form.ListBoxURL.options[OptionIndex].value;}
		// ]]> -->
	</script>
	</form>
<?php
}

function processEditSelection($subtab) {
	global $subalbum_nesting, $gallery_nesting, $imagesTab_imageCount;
	if(isset($_GET['selection'])) {
		switch($subtab) {
			case '':
				$gallery_nesting = sanitize_numeric($_GET['selection']);
				zp_setCookie('gallery_nesting',$gallery_nesting);
				break;
			case 'subalbuminfo':
				$subalbum_nesting = sanitize_numeric($_GET['selection']);
				zp_setCookie('subalbum_nesting',$subalbum_nesting);
				break;
			case 'imageinfo':
				$imagesTab_imageCount = sanitize_numeric($_GET['selection']);
				zp_setCookie('imagesTab_imageCount',$imagesTab_imageCount);
				break;
		}
	} else {
		switch($subtab) {
			case '':
				$gallery_nesting = zp_getCookie('gallery_nesting');
				break;
			case 'subalbuminfo':
				$subalbum_nesting = zp_getCookie('subalbum_nesting');
				break;
			case 'imageinfo':
				$count = zp_getCookie('imagesTab_imageCount');
				if ($count) $imagesTab_imageCount = $count;
				break;
		}
	}
}

/**
 * Enables comments for a news article or page
 *
 * @param string $type "news" or "pages"
 */
function enableComments($type) {
	if($_GET['commentson']) {
		$comments = "0";
	} else {
		$comments = "1";
	}
	switch($type) {
		case "news":
			$dbtable = prefix('news');
			break;
		case "page":
			$dbtable = prefix('pages');
			break;
		case "album":
			$dbtable = prefix('albums');
			break;
	}
	query("UPDATE ".$dbtable." SET `commentson` = ".$comments." WHERE id = ".sanitize_numeric($_GET['id']));
}

/**
 * Edit tab bulk actions drop-down
 * @param array $checkarray the list of actions
 * @param bool $checkAll set true to include check all box
 */
function printBulkActions($checkarray, $checkAll=false) {
	if (in_array('addtags', $checkarray) || in_array('alltags', $checkarray)) {
		$tags = true;
		?>
		<script type="text/javascript">
			//<!-- <![CDATA[
			function checkForTags(obj) {
				var sel = obj.options[obj.selectedIndex].value;
				if (sel == 'addtags' || sel == 'alltags') {
					$.colorbox({href:"#mass_tags_data", inline:true, open:true});
				}
			}
			// ]]> -->
		</script>
		<?php
	}
	?>
	<span style="float:right">
		<select name="checkallaction" id="checkallaction" size="1" onchange="checkForTags(this);" >
			<?php generateListFromArray(array('noaction'), $checkarray,false,true); ?>
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
				tagSelector(NULL, 'mass_tags_', false, false, true);
				?>
			</div>
		</div>
		<?php
	}
}

/**
 * Processes the check box bulk actions for albums
 *
 */
function processAlbumBulkActions() {
	global $gallery;
	$action = sanitize($_POST['checkallaction']);
	$ids = $_POST['ids'];
	$total = count($ids);
	$message = NULL;
	if($action != 'noaction') {
		if ($total > 0) {
			if ($action == 'addtags' || $action == 'alltags') {
				foreach ($_POST as $key => $value) {
					$key = postIndexDecode($key);
					if (substr($key, 0, 10) == 'mass_tags_') {
						if ($value) {
							$tags[] = substr($key, 10);
						}
					}
				}
				$tags = sanitize($tags, 3);
			}
			$n = 0;
			foreach ($ids as $albumname) {
				$n++;
				$albumobj = new Album($gallery,$albumname);
				switch($action) {
					case 'deleteall':
						$albumobj->remove();
						break;
					case 'showall':
						$albumobj->set('show',1);
						break;
					case 'hideall':
						$albumobj->set('show',0);
						break;
					case 'commentson':
						$albumobj->set('commentson',1);
						break;
					case 'commentsoff':
						$albumobj->set('commentson',0);
						break;
					case 'resethitcounter':
						$albumobj->set('hitcounter',0);
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
				}
				$albumobj->save();
			}
		}
		return $action;
	}
}

/**
 * Handles Image bulk actions
 * @param $album
 */
function processBulkImageActions($album) {
	$action = sanitize($_POST['checkallaction']);
	$ids = $_POST['ids'];
	$total = count($ids);
	$message = NULL;
	if($action != 'noaction') {
		if ($total > 0) {
			if ($action == 'addtags') {
				foreach ($_POST as $key => $value) {
					$key = postIndexDecode($key);
					if (substr($key, 0, 10) == 'mass_tags_') {
						if ($value) {
							$tags[] = substr($key, 10);
						}
					}
				}
				$tags = sanitize($tags, 3);
			}
			$n = 0;
			foreach ($ids as $filename) {
				$n++;
				$imageobj = newImage($album, $filename);
				switch($action) {
					case 'deleteall':
						$imageobj->remove();
						break;
					case 'showall':
						$imageobj->set('show',1);
						break;
					case 'hideall':
						$imageobj->set('show',0);
						break;
					case 'commentson':
						$imageobj->set('commentson',1);
						break;
					case 'commentsoff':
						$imageobj->set('commentson',0);
						break;
					case 'resethitcounter':
						$imageobj->set('hitcounter',0);
						break;
					case 'addtags':
						$mytags = array_unique(array_merge($tags, $imageobj->getTags()));
						$imageobj->setTags($mytags);
						break;
					case 'cleartags':
						$imageobj->setTags(array());
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
	global $gallery;
	if (isset($_POST['ids'])) { // these is actually the folder name here!
		//echo "action for checked items:". $_POST['checkallaction'];
		$action = sanitize($_POST['checkallaction']);
		$ids = $_POST['ids'];
		$total = count($ids);
		$dbtable = prefix('comments');
		$message = NULL;
		if($action != 'noaction') {
			if ($total > 0) {
				$n = 0;
				switch($action) {
					case 'deleteall':
						$sql = "DELETE FROM ".$dbtable." WHERE ";
						break;
					case 'spam':
						$sql = "UPDATE ".$dbtable." SET `inmoderation` = 1 WHERE ";
						break;
					case 'approve':
						$sql = "UPDATE ".$dbtable." SET `inmoderation` = 0 WHERE ";
						break;
				}
				foreach ($ids as $id) {
					$n++;
					$sql .= " id='".sanitize_numeric($id)."' ";
					if ($n < $total) $sql .= "OR ";
				}
				query($sql);
			}
			//if(!is_null($message)) echo"<p class='messagebox fade-message'>".$message."</p>";
		}
	}
	return $action;
}

/**
 * Extracs the first two characters from the Zenphoto locale names like 'de_DE' so that
 * TinyMCE and the Ajax File Manager who use two character locales like 'de' can set their language packs
 *
 * @return string
 */
function getLocaleForTinyMCEandAFM() {
	$locale = substr(getOption("locale"),0,2);
	if (empty($locale)) $locale = 'en';
	return $locale;
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
			tabContainers.hide().filter(':first').show();

			$('div.tabs ul.tabNavigation a').click(function () {
				tabContainers.hide();
				tabContainers.filter(this.hash).show();
				$('div.tabs ul.tabNavigation a').removeClass('selected');
				$(this).addClass('selected');
				return false;
			}).filter(':first').click();
		});
		// ]]> -->
	</script>
<?php
}

/**
 * Standard admin pages checks
 * @param bit $rights
 * @param string $return--where to go after login
 */
function admin_securityChecks($rights, $return) {
	global $_zp_current_admin_obj, $_zp_loggedin;
	checkInstall();
	if (!is_null(getOption('admin_reset_date'))) {
		if (!zp_loggedin($rights)) { // prevent nefarious access to this page.
			if (!zp_apply_filter('admin_allow_access',false, urldecode($return))) {
				header("HTTP/1.0 302 Found");
				header("Status: 302 Found");
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $return);
				exit();
			}
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
		zp_apply_filter('admin_XSRF_access',false, $action);
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&error&msg='.sprintf(gettext('"%s" Cross Site Request Forgery blocked.'),$action));
		exit();
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
 *
 * returns the shortest version of string2 that is different from string1
 * @param string $string1
 * @param string2 $string2
 */
function minDiff($string1, $string2) {
	if ($string1==$string2) {
		return $string2;
	}
	if (strlen($string2) > strlen($string1)) {
		$base = $string2;
	} else {
		$base = $string1;
	}
	for ($i=0;$i<min(strlen($string1),strlen($string2));$i++) {
		if ($string1[$i] != $string2[$i]) {
			$base = substr($string2,0,$i+1);
			break;
		}
	}
	return $base;
}

/**
 * Returns Magick (Gmagick/Imagick) constants that begin with $filter and
 * removes the constant of the form $filter . 'UNDEFINED' if it exists.
 *
 * The returned array will be an associative array in which the
 * keys and values are identical strings sorted in alphabetical order.
 *
 * @param string $class The class to reflect
 * @param string $filter The string to delimit constants
 * @return array
 */
function getMagickConstants($class, $filter) {
	global $magickConstantPrefix;

	$magickReflection = new ReflectionClass($class);
	$magickConstants = $magickReflection->getConstants();

	// lambda functions have no scope; must use $GLOBALS superglobal
	$lambdaFilter = create_function('$value', 'return !strncasecmp($value, $GLOBALS["magickConstantPrefix"], strlen($GLOBALS["magickConstantPrefix"]));');

	$magickConstantPrefix = $filter;
	$filteredConstants = array_filter(array_keys($magickConstants), $lambdaFilter);

	if (($key = array_search($filter . 'UNDEFINED', $filteredConstants)) !== false) {
		unset($filteredConstants[$key]);
	}

	$constantsArray = array_combine(array_values($filteredConstants), $filteredConstants);
	asort($constantsArray);

	return $constantsArray;
}

/**
 * Strips off quotes from the strng
 * @param $string
 */
function unQuote($string) {
	$string = trim($string);
	$q = substr($string,0,1);
	if ($q == '"' || $q == "'") {
		$string = substr($string, 1, -1);
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
			$adminlist .= '<option value="'.$user['user'].'"';
			if ($owner == $user['user']) {
				$adminlist .= ' SELECTED="SELECTED"';
			}
			$adminlist .= '>'.$user['user']."</option>\n";
		}
	}
	return $adminlist;
}

?>