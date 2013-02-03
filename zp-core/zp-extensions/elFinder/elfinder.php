<?php
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
zp_session_start();
admin_securityChecks(FILES_RIGHTS, currentRelativeURL());
XSRFdefender('elFinder');
$locale = substr(getOption("locale"),0,2);
if (empty($locale)) $locale = 'en';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>elFinder 2.0</title>

		<!-- jQuery and jQuery UI (REQUIRED) -->
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/jqueryui/jquery-ui-zenphoto.css" type="text/css" />
		<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/js/jqueryui/jquery-ui-zenphoto.js" type="text/javascript"></script>

		<!-- elFinder CSS (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>css/theme.css">

		<!-- elFinder JS (REQUIRED) -->
		<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>js/elfinder.min.js"></script>

		<!-- elFinder translation (OPTIONAL) -->
		<?php
		if ($locale!='en') {
			?>
			<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>js/i18n/elfinder.<?php echo $locale; ?>.js"></script>
			<?php
		}
		?>
		<!-- TinyMCE Popup class (REQUIRED) -->
		<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/tiny_mce/tiny_mce_popup.js"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script type="text/javascript" charset="utf-8">
			var FileBrowserDialogue = {
				init: function() {
					// Here goes your code for setting your custom things onLoad.
				},
				mySubmit: function (URL) {
					var win = tinyMCEPopup.getWindowArg('window');

					// pass selected file path to TinyMCE
					win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = URL;

					// are we an image browser?
					if (typeof(win.ImageDialog) != 'undefined') {
						// update image dimensions
						if (win.ImageDialog.getImageData) {
							win.ImageDialog.getImageData();
						}
						// update preview if necessary
						if (win.ImageDialog.showPreviewImage) {
							win.ImageDialog.showPreviewImage(URL);
						}
					}

					// close popup window
					tinyMCEPopup.close();
				}
			}

		tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);

		$().ready(function() {
				var elf = $('#elfinder').elfinder({
					lang: '<?php echo $locale; ?>',   // language (OPTIONAL)
					customData: {'XSRFToken':'<?php echo getXSRFToken('elFinder'); ?>'},
					url : '<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>php/connector_zp.php'  				// connector URL (REQUIRED)
				}).elfinder('instance');
			});
		</script>
	</head>
	<body>

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder"></div>

	</body>
</html>
