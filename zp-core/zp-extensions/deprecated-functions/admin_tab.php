<?php
/**
 * This is the "files" upload tab
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions.php');
printAdminHeader('deprecated', '');

echo "\n</head>";
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<div class="tabbox">
					<h1><?php echo gettext('Deprecated Functions'); ?></h1>
					<p>
						<?php echo gettext('Functions flagged with an asterisk are class methods. Ones flagged with two asterisks have deprecated parameters.'); ?>
					</p>
					<?php
					$deprecated = new deprecated_functions();
					$list = array();
					foreach ($deprecated->listed_functions as $funct => $details) {
						switch (trim($details['class'])) {
							case 'static':
								$class = '*';
								break;
							case 'public static':
								$class = '**';
								break;
							default:
								$class = '';
								break;
						}
						$list[$details['since']][$details['plugin']][] = $funct . $class;
						krsort($list, SORT_NATURAL | SORT_FLAG_CASE);
					}
					?>
					<ul style="list-style-type: none;">
						<?php
						foreach ($list as $release => $plugins) {
							?>
							<li>
								<h1><?php echo $release; ?></h1>
								<ul style="list-style-type: none;">
									<?php
									ksort($plugins, SORT_NATURAL | SORT_FLAG_CASE);
									foreach ($plugins as $plugin => $functions) {
										?>
										<li>
											<h2><?php echo $plugin; ?></h2>
											<ul style="list-style-type: none;">
												<?php
												natcasesort($functions);
												foreach ($functions as $function) {
													?>
													<li>
														<?php echo $function; ?>
													</li>
													<?php
												}
												?>
											</ul>
										</li>
										<?php
									}
									?>
								</ul>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<br class="clearall" />
	<?php printAdminFooter(); ?>

</body>
</html>
