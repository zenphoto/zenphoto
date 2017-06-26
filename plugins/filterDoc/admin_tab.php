<?php
/**
 * This is the "filters" tab
 *
 * @package plugins
 * @subpackage development
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-globals.php");
admin_securityChecks(DEBUG_RIGHTS, $return = currentRelativeURL());

printAdminHeader('development', gettext('rewriteTokens'));
?>
</head>

<body>

	<link
	<?php printLogoAndLinks(); ?>
		<div id="main">
			<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php zp_apply_filter('admin_note', 'development', ''); ?>
				<h1><?php echo gettext('ZenPhoto20 filters'); ?></h1>
				<div class="tabbox">
					<?php
					echo '<div style="float:left;width:70%;">';
					include ('intro.html');
					echo '</div>';
					echo '<div style="float:right;width:30%;">';
					include ('filter list_index.html');
					echo '</div>';
					echo '<br clear="all">';
					include ('filter list.html');
					?>
				</div>
			</div>
		</div>
	</div>
	<?php printAdminFooter();
	?>

</body>
</html>
