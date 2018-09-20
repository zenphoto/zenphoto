<?php
/**
 * The Matomo statistis page if widgets are defined
 *
 * @package plugins/matomo
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
printAdminHeader('overview', 'matomo');
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<h1><?php echo gettext('Matomo statistics'); ?></h1>
				<?php echo getOption('matomo_widgets_code'); ?>
			</div>
		</div>
		<br class="clearall" />
		<?php printAdminFooter(); ?>
	</div>
</body>
</html>
