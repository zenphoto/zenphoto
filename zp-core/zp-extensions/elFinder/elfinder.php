<?php
/**
 *  @package zpcore\plugins\elfinder
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');
zp_session_start();
admin_securityChecks(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_PAGES_RIGHTS, currentRelativeURL());
XSRFdefender('elFinder');
$locale = substr(getOption("locale"), 0, 2);
if (empty($locale))
	$locale = 'en';
?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>elFinder 2.0</title>

		<!-- jQuery and jQuery UI (REQUIRED) -->
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui.min.css" type="text/css" />
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.min.js"></script>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery-migrate.min.js" ></script>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jqueryui/jquery-ui.min.js"></script>

		<!-- elFinder CSS (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/'; ?>css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/'; ?>css/theme.css">

		<!-- elFinder JS (REQUIRED) -->
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/'; ?>js/elfinder.min.js"></script>

		<!-- elFinder translation (OPTIONAL) -->
		<?php
		if ($locale != 'en') {
			?>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/'; ?>js/i18n/elfinder.<?php echo $locale; ?>.js"></script>
			<?php
		}
		?>

			
		<!-- elFinder initialization (REQUIRED) -->
		<script charset="utf-8">
				if (top.tinymce.majorVersion == 4) {
					var FileBrowserDialogue = {
					init: function() {
					// Here goes your code for setting your custom things onLoad.
					},
						mySubmit: function(URL) {
						// pass selected file path to TinyMCE
						top.tinymce.activeEditor.windowManager.getParams().setUrl(URL);
										// close popup window
										top.tinymce.activeEditor.windowManager.close();
						}
					}
				} else {
					var FileBrowserDialogue = {
						init: function() {
						// Here goes your code for setting your custom things onLoad.
						},
							mySubmit: function(URL) {
								// pass selected file path to TinyMCE
								window.parent.postMessage({
									mceAction: 'insertMyURL',
									url: URL
								}, '*');
							}
					}
				}

			$().ready(function() {
			var elf = $('#elfinder').elfinder({
			commands : [
							'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile', 'quicklook',
<?php
if (zp_loggedin(FILES_RIGHTS)) {
	?>
				'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'upload', 'copy',
								'cut', 'paste', 'edit', 'extract', 'archive', 'search',
								'resize',
	<?php
}
?>
			'info', 'view', 'help',
							'sort'
			],
							lang: '<?php echo $locale; ?>', // language (OPTIONAL)
							customData: {
							'XSRFToken':'<?php echo getXSRFToken('elFinder'); ?>',
											'zp_user_auth':'<?php echo zp_getCookie('zpcms_auth_user'); ?>',
											'origin':'tinyMCE'
							},
							url : '<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/'; ?>php/connector_zp.php', // connector URL (REQUIRED)
							getFileCallback: function(file) { // editor callback
							FileBrowserDialogue.mySubmit(file.url); // pass selected file path to TinyMCE
							}
			}).elfinder('instance');
			});
		</script>
	</head>
	<body>

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder"></div>

	</body>
</html>
