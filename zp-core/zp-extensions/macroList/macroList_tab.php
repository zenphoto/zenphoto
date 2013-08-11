<?php
/**
 * This is the "files" upload tab
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
printAdminHeader('macros', '');

echo "\n</head>";
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<div class="tabbox">
					<h1><?php echo gettext('Content Macros'); ?></h1>
					<?php
					$macros = getMacros();
					ksort($macros);
					if (empty($macros)) {
						echo gettext('No macros have been defined.');
					} else {
						?>
						<div>
							<p><?php echo gettext('These Content macros can be used to insert Zenphoto items as described into <em>descriptions</em>, <em>zenpage content</em>, and <em>zenpage extra content</em>.</p> <p>Replace any parameters (<em>%d</em>) with the appropriate value.'); ?></p>
							<p><?php echo gettext('Parameter types:'); ?></p>
							<ol>
								<li><?php echo gettext('<em><strong>string</strong></em> may be enclosed in quotation marks when the macro is invoked. The quotes are stripped before the macro is processed.'); ?></li>
								<li><?php echo gettext('<em><strong>int</strong></em> a number'); ?></li>
								<li><?php echo gettext('<em><strong>bool</strong></em> <code>true</code> or <code>false</code>'); ?></li>
								<li><?php echo gettext('<em><strong>array</strong></em> an assignment list e.g. u=w</code> <code>x=y</code>....'); ?></li>
							</ol>
							<p><?php echo gettext('Parameters within braces are optional.'); ?></p>
						</div>
						<?php
						foreach ($macros as $macro => $detail) {
							macroList_show($macro, $detail);
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<br class="clearall" />
	<?php printAdminFooter(); ?>

</body>
</html>
