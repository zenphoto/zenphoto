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
printAdminHeader('development', gettext('rewrite'));
echo "\n</head>";
?>
<body>
	<?php printLogoAndLinks(); ?>

	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php
				zp_apply_filter('admin_note', 'development', '');
				?>
				<h1>
					<?php
					echo gettext('Rewrite Rules');
					?>
				</h1>			<div class="tabbox">

					<dl class="code">
						<?php
						$c = 0;
						foreach ($list as $key => $rule) {
							$c++;
							?>
							<dt<?php if ($c & 1) echo ' class="bar"'; ?>>
								<code><?php echo $rule[0], ' ' . $rule[1]; ?></code>
							</dt>
							<dd<?php if ($c & 1) echo ' class="bar"'; ?>>
								<code><?php echo $rule[2] . '&nbsp;'; ?></code>
							</dd>
							<?php
						}
						?>
					</dl>
				</div>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>

</body>
</html>
