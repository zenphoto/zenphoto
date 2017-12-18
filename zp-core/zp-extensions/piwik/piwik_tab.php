<?php
/**
 * The Piwik statistis page if widgets are defined
 *
 * @package plugins/piwik
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
printAdminHeader('overview', 'piwik');
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<h1><?php echo gettext('Piwik statistics'); ?></h1>
				<?php echo getOption('piwik_widgets_code'); ?>
			</div>
		</div>
		<br class="clearall" />
	</div>

	<?php printAdminFooter(); ?>
</body>
</html>
