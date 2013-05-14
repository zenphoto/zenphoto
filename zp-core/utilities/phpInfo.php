<?php
/**
 * Displays the PHP configuration information
 * @author Stephen Billard (sbillard)
 * @package admin
 */

define ('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');

$buttonlist[] = array(
									'category'=>gettext('Info'),
									'enable'=>true,
									'button_text'=>gettext('Show PHP Information'),
									'formname'=>'cloneZenphoto',
									'action'=> 'utilities/phpInfo.php',
									'icon'=>'images/info.png',
									'title'=>gettext('Display PHP information.'),
									'alt'=>gettext('PHPInfo'),
									'hidden'=>'',
									'rights'=> ADMIN_RIGHTS
									);

admin_securityChecks(NULL, currentRelativeURL());

$zenphoto_tabs['overview']['subtabs']=array(gettext('PHP Info')=>'');
printAdminHeader('overview','phpInfo');

?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
	<?php printSubtabs(); ?>
	<div class="tabbox">
	<h1><?php echo (gettext('Your PHP configuration information.')); ?></h1>
	<?php zp_apply_filter('admin_note','phpinfo', ''); ?>
	<br />
	<br />
	<?php phpinfo(); ?>
	</div>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>
?>
