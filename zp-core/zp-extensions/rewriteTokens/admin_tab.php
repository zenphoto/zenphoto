<?php
/**
 * This is the "files" upload tab
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions.php');
printAdminHeader('development', gettext('rewriteTokens'));

echo "\n</head>";

$_definitions = array();
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php printSubtabs(); ?>
				<div class="tabbox">
					<h1><?php echo gettext('Rewrite Tokens'); ?></h1>
					<dl class="code">
						<?php
						foreach ($_zp_conf_vars['special_pages'] as $page => $element) {
							if (array_key_exists('define', $element) && $element['define']) {
								$_definitions[$element['define']] = strtr($element['rewrite'], $_definitions);
								?>
								<dt>
								<?php
								echo $element['define'];
								?>
								</dt>
								<dt>
								<?php
								echo strtr($element['rewrite'], $_definitions);
								?>
								</dt>
								<?php
							}
						}
						?>
					</dl>
				</div>
			</div>
		</div>
	</div>
	<br class="clearall" />
	<?php printAdminFooter(); ?>

</body>
</html>
