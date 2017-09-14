<?php

/**
 *
 * Displays a "plugin usage" document based on the plugin's doc comment block.
 *
 * Supports the following PHPDoc markup tags:
 * <code>
 * { @link URL text } text may be empty in which case the link is used as the link text.
 * <i> emphasis
 * <b> strong
 * <var> mono-spaced text
 * <code> code blocks (Note: PHPDocs will create an ordered list of the enclosed text)
 * <hr> horizontal rule
 * <ul><li>, <ol><li> lists
 * <pre>
 * <br> line break
 * </code>
 *
 * NOTE: These apply ONLY to the plugin's document block. Normal string use (e.g. plugin_notices, etc.).
 * should use standard markup.
 *
 * The definitions for folder names and paths are represented by <var>%define%</var> (e.g. <var>%WEBPATH%</var>). The
 * document processor will substitute the actual value for these tags when it renders the document.
 * Image URIs are also processed. Use the appropriate definition tokens to cause the URI to point
 * to the actual image. E.g. <var><img src="%WEBPATH%/%ZENFOLDER%/images/zen-logo.png" /></var>
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 * @subpackage development
 */
// force UTF-8 Ø

function processCommentBlock($commentBlock) {
	global $plugin_author, $subpackage;
	$markup = array(
			'&lt;i&gt;' => '<em>',
			'&lt;/i&gt;' => '</em>',
			'&lt;b&gt;' => '<strong>',
			'&lt;/b&gt;' => '</strong>',
			'&lt;code&gt;' => '<span class="inlinecode">',
			'&lt;/code&gt;' => '</span>',
			'&lt;hr&gt;' => '<hr />',
			'&lt;ul&gt;' => '<ul>',
			'&lt;/ul&gt;' => '</ul>',
			'&lt;ol&gt;' => '<ol>',
			'&lt;/ol&gt;' => '</ol>',
			'&lt;li&gt;' => '<li>',
			'&lt;/li&gt;' => '</li>',
			'&lt;dl&gt;' => '<dl>',
			'&lt;/dl&gt;' => '</dl>',
			'&lt;dt&gt;' => '<dt><strong>',
			'&lt;/dt&gt;' => '</strong></dt>',
			'&lt;dd&gt;' => '<dd>',
			'&lt;/dd&gt;' => '</dd>',
			'&lt;pre&gt;' => '<pre>',
			'&lt;/pre&gt;' => '</pre>',
			'&lt;br&gt;' => '<br />',
			'&lt;var&gt;' => '<span class="inlinecode">',
			'&lt;/var&gt;' => '</span>'
	);
	$const_tr = array('%ZENFOLDER%' => ZENFOLDER,
			'%PLUGIN_FOLDER%' => PLUGIN_FOLDER,
			'%USER_PLUGIN_FOLDER%' => USER_PLUGIN_FOLDER,
			'%ALBUMFOLDER%' => ALBUMFOLDER,
			'%THEMEFOLDER%' => THEMEFOLDER,
			'%BACKUPFOLDER%' => BACKUPFOLDER,
			'%UTILITIES_FOLDER%' => UTILITIES_FOLDER,
			'%DATA_FOLDER%' => DATA_FOLDER,
			'%CACHEFOLDER%' => CACHEFOLDER,
			'%UPLOAD_FOLDER%' => UPLOAD_FOLDER,
			'%STATIC_CACHE_FOLDER%' => STATIC_CACHE_FOLDER,
			'%FULLWEBPATH%' => FULLWEBPATH,
			'%WEBPATH%' => WEBPATH
	);
	$body = $doc = '';
	$par = false;
	$empty = false;
	$lines = explode("\n", strtr($commentBlock, $const_tr));
	foreach ($lines as $line) {
		$line = trim(preg_replace('/^\s*\*/', '', $line));
		if (empty($line)) {
			if (!$empty) {
				if ($par) {
					$doc .= '</p>';
				}
				$doc .= '<p>';
				$empty = $par = true;
			}
		} else {
			if (strpos($line, '@') === 0) {
				preg_match('/@(.*?)\s/', $line, $matches);
				if (!empty($matches)) {
					switch ($matches[1]) {
						case 'author':
							$plugin_author = trim(substr($line, 8));
							break;
						case 'subpackage':
							$subpackage = trim(substr($line, 11));
							break;
						case 'link':
							$line = trim(substr($line, 5));
							$l = strpos($line, ' ');
							if ($l === false) {
								$text = $line;
							} else {
								$text = substr($line, $l + 1);
								$line = substr($line, 0, $l);
							}
							$links[] = array('text' => $text, 'link' => $line);
							break;
					}
				}
			} else {
				$tags = array();
				preg_match_all('|<img src="(.*?)"\s*/>|', $line, $matches);
				if (!empty($matches[0])) {
					foreach ($matches[0] as $key => $match) {
						if (!empty($match)) {
							$line = str_replace($match, '%' . $key . '$i', $line);
							$tags['%' . $key . '$i'] = '<img src="' . pathurlencode($matches[1][$key]) . '" alt="" />';
						}
					}
				}
				preg_match_all('|\{@link (.*?)\}|', $line, $matches);
				if (!empty($matches[0])) {
					foreach ($matches[0] as $key => $match) {
						if (!empty($match)) {
							$line = str_replace($match, '%' . $key . '$l', $line);
							$l = strpos($matches[1][$key], ' ');
							if ($l === false) {
								$link = $text = $matches[1][$key];
							} else {
								$text = substr($matches[1][$key], $l + 1);
								$link = substr($matches[1][$key], 0, $l);
							}
							$tags['%' . $key . '$l'] = '<a href="' . html_encode($link) . '">' . strtr(html_encode($text), $markup) . '</a>';
						}
					}
				}
				$doc .= strtr(html_encodeTagged($line), array_merge($tags, $markup)) . ' ';
				$empty = false;
			}
		}
	}
	if ($par) {
		$doc .= '</p>';
		$body .= $doc;
		$doc = '';
	}
	return $body;
}

if (!defined('OFFSET_PATH')) {
	define('OFFSET_PATH', 2);
	require_once(dirname(__FILE__) . '/admin-globals.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

	$extension = sanitize($_GET['extension']);
	if (!in_array($extension, array_keys(getPluginFiles('*.php')))) {
		exit();
	}

	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);

	$real_locale = getUserLocale();

	$pluginType = @$_GET['type'];

	if ($pluginType) {
		$pluginToBeDocPath = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/' . $extension . '.php';
	} else {
		$pluginToBeDocPath = SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $extension . '.php';
	}
	$plugin_description = '';
	$plugin_notice = '';
	$plugin_disable = '';
	$plugin_author = '';
	$plugin_version = '';
	$plugin_is_filter = '';
	$plugin_URL = '';
	$option_interface = '';
	$doclink = '';

	require_once($pluginToBeDocPath);

	$macro_params = array($plugin_description, $plugin_notice, $plugin_disable, $plugin_author, $plugin_version, $plugin_is_filter, $plugin_URL, $option_interface, $doclink);

	$buttonlist = zp_apply_filter('admin_utilities_buttons', array());
	foreach ($buttonlist as $key => $button) {
		$buttonlist[$key]['enable'] = false;
	}
	$imagebuttons = preg_replace('/<a href=[^>]*/i', '<a', zp_apply_filter('edit_image_utilities', '', $_zp_missing_image, 0, '', '', ''));
	$albumbuttons = preg_replace('/<a href=[^>]*/i', '<a', zp_apply_filter('edit_album_utilities', '', $_zp_missing_album, ''));

	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/macroList.php');

	list($plugin_description, $plugin_notice, $plugin_disable, $plugin_author, $plugin_version, $plugin_is_filter, $plugin_URL, $option_interface, $doclink) = $macro_params;

	$content_macros = getMacros();
	krsort($content_macros);
	foreach ($content_macros as $macro => $detail) {
		if (@$detail['owner'] != $extension) {
			unset($content_macros[$macro]);
		}
	}

	$pluginStream = @file_get_contents($pluginToBeDocPath);
	$i = strpos($pluginStream, '/*');
	$j = strpos($pluginStream, '*/');

	$links = array();

	if ($i !== false && $j !== false) {
		$commentBlock = substr($pluginStream, $i + 2, $j - $i - 2);
		$sublink = $subpackage = false;
		$body = processCommentBlock($commentBlock);
		switch ($pluginType) {
			case 'thirdparty':
				$whose = 'Third party plugin';
				$path = stripSuffix($pluginToBeDocPath) . '/logo.png';
				if (file_exists($path)) {
					$ico = str_replace(SERVERPATH, WEBPATH, $path);
				} else {
					$ico = 'images/placeholder.png';
				}
				break;
			case 'supplemental':
				if ($subpackage) {
					$sublink = $subpackage . '/';
				}
				$whose = 'Supplemental plugin';
				$ico = 'images/zp.png';
				break;
			default:
				if ($subpackage) {
					$sublink = $subpackage . '/';
				}
				$whose = 'Official plugin';
				$ico = 'images/zp_gold.png';
				break;
		}

		if ($plugin_URL) {
			$doclink = sprintf('See also the <a href="%1$s">%2$s</a>', $plugin_URL, $extension);
		}
		$pluginusage = gettext('Plugin usage information');
		$pagetitle = sprintf(gettext('%1$s %2$s: %3$s'), html_encode($_zp_gallery->getTitle()), gettext('admin'), html_encode($extension));
		setupCurrentLocale('en_US');
		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" />
		<head>
			<?php printStandardMeta(); ?>
			<title><?php echo $pagetitle; ?></title>
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
			<style>

				#heading {
					height: 15px;
				}
				#plugin-content {
					background-color: #f1f1f1;
					border: 1px solid #CBCBCB;
					padding: 5px;
				}
				.doc_box_field {
					padding-left: 0px;
					padding-right: 5px;
					padding-top: 5px;
					padding-bottom: 5px;
					margin: 15px;
					border: 1px solid #cccccc;
					width: 460px;
				}
				.moc_button {
					display: block;
					float: left;
					width: 200px;
					margin: 0 7px 0 0;
					background-color: #f5f5f5;
					background-image: url(images/admin-buttonback.jpg);
					background-repeat: repeat-x;
					border: 1px solid #dedede;
					border-top: 1px solid #eee;
					border-left: 1px solid #eee;
					font-family: "Lucida Grande", Tahoma, Arial, Verdana, sans-serif;
					font-size: 100%;
					line-height: 130%;
					text-decoration: none;
					font-weight: bold;
					color: #565656;
					cursor: pointer;
					padding: 5px 10px 6px 7px; /* Links */
				}
				.buttons .tip {
					text-align: left;
				}

				dl {
					display: block;
					clear: both;
					width: 100%;
				}

				dt,dd {
					vertical-align: top;
					display: inline-block;
					width: 90%;
					margin: 0;
				}

				dt {
					font-weight: bold;
				}
				dd {
					width: 90%;
					margin-left: 3em;
				}


				ul, ol {
					list-style: none;
					padding: 0;
				}
				li {
					margin-left: 1.5em;
					padding-bottom: 0.5em;
				}
				ul.options  {
					list-style: none;
					margin-left: 0;
					padding: 0;
				}
				ul.options li {
					list-style: none;
					margin-left: 1.5em;
					padding-bottom: 0.5em;
				}
			</style>
		</head>
		<body>
			<div id="main">
				<div id="heading">
					<?php
					echo $pluginusage;
					?>
					<div id="google_translate_element" class="floatright"></div>
					<script type="text/javascript">
						function googleTranslateElementInit() {
							new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
						}
					</script>
					<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
				</div>
				<br class="clearall" />

				<div id="plugin-content">
					<h1><img class="zp_logoicon" src="<?php echo $ico; ?>" alt="logo" title="<?php echo $whose; ?>" /><?php echo html_encode($extension); ?></h1>
					<div class="border">
						<?php echo $plugin_description; ?>
					</div>
					<?php
					if ($pluginType == 'thirdparty') {
						?>
						<h3><?php printf('Version: %s', $plugin_version); ?></h3>
						<?php
					}
					if ($plugin_author) {
						?>
						<h3><?php printf('Author: %s', html_encode($plugin_author)); ?></h3>
						<?php
					}
					foreach ($links as $key => $link) {
						if ($key)
							echo "<br />";
						echo '<a href="' . html_encode($link['link']) . '">' . html_encode($link['text']) . '</a>';
					}
					?>
					<div>
						<?php
						if ($plugin_disable) {
							?>
							<div class="warningbox">
								<?php echo $plugin_disable; ?>
							</div>
							<?php
						}
						if ($plugin_notice) {
							?>
							<div class="notebox">
								<?php echo $plugin_notice; ?>
							</div>
							<?php
						}

						echo $body;

						if ($option_interface) {
							if (is_string($option_interface)) {
								$option_interface = new $option_interface;
							}
							$options = $supportedOptions = $option_interface->getOptionsSupported();
							$option = array_shift($options);
							if (array_key_exists('order', $option)) {
								$options = sortMultiArray($supportedOptions, 'order');
								$options = array_keys($options);
							} else {
								$options = array_keys($supportedOptions);
								natcasesort($options);
							}
							$notes = array();
							?>
							<hr />
							<p>
								<?php echo ngettext('Option:', 'Options:', count($options)); ?>
								<ul class="options">
									<?php
									foreach ($options as $option) {
										if (array_key_exists($option, $supportedOptions)) {
											$row = $supportedOptions[$option];
											if ($row['type'] == OPTION_TYPE_NOTE) {
												$notes[] = $row;
											} else {
												if (false !== $i = stripos($option, chr(0))) {
													$option = substr($option, 0, $i);
												}
												if ($option) {
													?>
													<li><code><?php echo $option; ?></code></li>
													<?php
												}
											}
										}
									}
									foreach ($notes as $note) {
										$n = getBare($note['desc']);
										if (!empty($n)) {
											?>
											<li><code><?php echo $note['desc']; ?></li>
											<?php
										}
									}
									?>
								</ul>
							</p>
							<?php
						}
						if (!empty($buttonlist) || !empty($albumbuttons) || !empty($imagebuttons)) {
							?>
							<hr />
							<?php
						}
						if (!empty($buttonlist)) {
							$buttonlist = sortMultiArray($buttonlist, array('category', 'button_text'), false);
							?>
							<div class="box" id="overview-section">
								<h2 class="h2_bordered">Utility functions</h2>
								<?php
								$category = '';
								foreach ($buttonlist as $button) {
									$button_category = @$button['category'];
									$button_icon = @$button['icon'];
									if ($category != $button_category) {
										if ($category) {
											?>
											</fieldset>
											<?php
										}
										$category = $button_category;
										?>
										<fieldset class="doc_box_field"><legend><?php echo $category; ?></legend>
											<?php
										}
										?>
										<form class="overview_utility_buttons">
											<div class="moc_button tip" title="<?php echo @$button['title']; ?>" >
												<?php
												if (!empty($button_icon)) {
													if (preg_match('~\&.*?\;~', $button_icon)) {
														echo $button_icon . ' ';
													} else {
														?>
														<img src="<?php echo $button_icon; ?>" alt="<?php echo html_encode($button['alt']); ?>" />
														<?php
													}
												}
												echo html_encode(@$button['button_text']);
												?>
											</div>
										</form>
										<?php
									}
									if ($category) {
										?>
									</fieldset>
									<?php
								}
								?>
							</div>
							<br class="clearall" />
							<?php
						}
						if ($albumbuttons) {
							$albumbuttons = preg_replace('|<hr(\s*)(/)>|', '', $albumbuttons);
							?>
							<h2 class="h2_bordered_edit">Album Utilities</h2>
							<div class="box-edit">
								<?php echo $albumbuttons; ?>
							</div>
							<br class="clearall" />
							<?php
						}
						if ($imagebuttons) {
							$imagebuttons = preg_replace('|<hr(\s*)(/)>|', '', $imagebuttons);
							?>
							<h2 class="h2_bordered_edit">Image Utilities</h2>
							<div class="box-edit">
								<?php echo $imagebuttons; ?>
							</div>
							<br class="clearall" />
							<?php
						}
						if (!empty($content_macros)) {
							echo ngettext('Macro defined:', 'Macros defined:', count($content_macros));
							foreach ($content_macros as $macro => $detail) {
								unset($detail['owner']);
								macroList_show($macro, $detail);
							}
							?>
							<br class="clearall" />
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<br class="clearall" />
		</body>
		<?php
	}
}

