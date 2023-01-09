<?php
/**
 * The Matomo statistis page if widgets are defined
 *
 * @package zpcore\plugins\matomo
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
$_zp_admin_menu['overview']['subtabs'] = array(gettext('Matomo') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/matomo/matomo_tab.php');
printAdminHeader('overview', 'matomo');
?>
<style>
/* Matomo iframe */
iframe  {
	border: 0;
}
</style>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<div id="container" style="height: 640px; height: 70vh; padding-bottom: 60px;">
					<h1><?php echo gettext('Matomo statistics'); ?></h1>
					<?php echo getOption('matomo_widgets_code'); ?>
				</div>
			</div><!-- tabbox -->
		</div><!-- content -->
		<br class="clearall" />
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>
