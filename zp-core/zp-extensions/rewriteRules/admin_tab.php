<?php
/**
 * This is the "rewrite rules" tab
 *
 * @package plugins
 * @subpackage development
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rewriteRules/functions.php');
admin_securityChecks(ADMIN_RIGHTS, $return = currentRelativeURL());

$list = rulesList();
printAdminHeader('rewrite', '');
?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/rewriteRules/rewriteRules.css" type="text/css" />
<?php
echo "\n</head>";
?>

<body>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<div class="tabbox">
					<h1><?php echo gettext('Rewrite Rules'); ?></h1>
					<table class="rewrite">
						<?php
						foreach ($list as $key => $rule) {
							?>
							<tr>
								<td class="rewrite_right">
									<?php echo $rule[0]; ?>
								</td>
								<td class="rewrite_left">
									<?php echo $rule[1]; ?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<br class="clearall" />
<?php printAdminFooter(); ?>

</body>
</html>
