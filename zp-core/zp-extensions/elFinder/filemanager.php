<?php
/**
 * This is the "files" upload tab
 *
 * @package plugins
 * @subpackage admin
 */


require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(FILES_RIGHTS, currentRelativeURL());

printAdminHeader('upload','files');

$locale = substr(getOption("locale"),0,2);
if (empty($locale)) $locale = 'en';
?>

<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>css/elfinder.min.css">
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>js/elfinder.min.js"></script>

<!-- Mac OS X Finder style for jQuery UI smoothness theme (OPTIONAL) -->
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>css/theme.css">
<?php
if ($locale!='en') {
	?>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>js/i18n/elfinder.<?php echo $locale; ?>.js"></script>
	<?php
}
echo "\n</head>";
?>

<body>

<?php	printLogoAndLinks(); ?>
<div id="main">
	<?php printTabs(); ?>
	<div id="content">
		<?php
		if (!empty($zenphoto_tabs['upload']['subtabs]'])) {
			printSubtabs();
		}
		?>
		<div id="container">
			<?php $subtab = printSubtabs(); ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note','upload', $subtab); ?>
				<h1><?php echo gettext('File Manager'); ?></h1>
				<script type="text/javascript">
					$().ready(function() {
						var elf = $('#elfinder').elfinder({
							lang: '<?php echo $locale; ?>',   // language (OPTIONAL)
							customData: {
														'XSRFToken':'<?php echo getXSRFToken('elFinder'); ?>',
														'zp_user_auth':'<?php echo zp_getCookie('zp_user_auth'); ?>'
													},
							url : '<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/'; ?>php/connector_zp.php'  				// connector URL (REQUIRED)
						}).elfinder('instance');
					});
				</script>

				<!-- Element where elFinder will be created (REQUIRED) -->
				<div id="elfinder"></div>
			</div>
		</div>
	</div>
</div>
<br clear="all" />
<?php printAdminFooter(); ?>

</body>
</html>
<?php
?>