<?php
/**
 *
 * File handling for "uploaded" files
 *
 */
define('OFFSET_PATH', 1);

require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(FILES_RIGHTS, currentRelativeURL());

printAdminHeader('upload','files');

echo "\n</head>";
?>

<body>

<?php	printLogoAndLinks(); ?>
<div id="main">
	<?php printTabs(); ?>
	<div id="content">
		<div id="container">
			<?php $subtab = printSubtabs(); ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note','upload', $subtab); ?>
				<h1><?php echo gettext('File Manager'); ?></h1>
				<?php
				$locale = substr(getOption("locale"),0,2);
				if (empty($locale)) $locale = 'en';
				?>
				<iframe src="<?php echo PLUGIN_FOLDER.'/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php'; ?>?language=<?php echo $locale; ?>&tab=files&XSRFToken=<?php echo getXSRFToken('ajaxfilemanager')?>" width="100%" height="480" style="border: 0">
				</iframe>
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
