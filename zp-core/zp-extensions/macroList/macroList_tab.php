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
						foreach ($macros as $macro => $detail) {
							macroList_show($macro, $detail);
						}
						?>
						<p class="notebox">
							<?php echo gettext('The above Content macros can be used to insert Zenphoto items as described into <em>descriptions</em>, <em>zenpage content</em>, and <em>zenpage extra content</em>. Replace any parameters (<em>%d</em>) with the appropriate value.'); ?>
						</p>
						<?php
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
