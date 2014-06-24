<?php
/**
 * This is the "tokens" upload tab
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
$subtab = getSubtabs();
printAdminHeader('debug', $subtab);

echo "\n</head>";
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php
				$subtab = printSubtabs();
				switch ($subtab) {
					case 'phpinfo':
						?>
						<div class="tabbox">
							<h1><?php echo gettext('Your PHP configuration information.'); ?></h1>
							<?php zp_apply_filter('admin_note', 'debug', 'phpinfo'); ?>
							<br />
							<br />
							<?php phpinfo(); ?>
						</div>
						<?php
						break;
					case 'http':
						$httpaccept = parseHttpAcceptLanguage();
						if (count($httpaccept) > 0) {
							$accept = $httpaccept;
							$accept = array_shift($accept);
							?>
							<div class="tabbox">
								<strong><?php echo ('Http Accept Languages:'); ?></strong>
								<br />
								<table>
									<tr>
										<th width = 100 align="left">Key</th>
										<?php
										foreach ($accept as $key => $value) {
											?>
											<th width = 100 align="left"><?php echo $key; ?></th>
											<?php
										}
										?>
									</tr>
									<?php
									foreach ($httpaccept as $key => $accept) {
										?>
										<tr>
											<td width=100 align="left"><?php echo $key; ?></td>
											<?php
											foreach ($accept as $value) {
												?>
												<td width=100 align="left"><?php echo $value; ?></td>
												<?php
											}
											?>
										</tr>
										<?php
									}
									?>
								</table>
							</div>
							<?php
						}
						break;
					case 'locale':
						?>
						<div class="tabbox">
							<strong><?php echo gettext('Supported locales:'); ?></strong>
							<?php
							$locales = list_system_locales();
							$last = '';
							foreach ($locales as $locale) {
								if ($last != substr($locale, 0, 3)) {
									echo "<br />";
									$last = substr($locale, 0, 3);
								}
								echo $locale . ' ';
							}
							?>
						</div>
						<?php
						break;
				}
				?>
			</div>
		</div>
	</div>
	<br class = "clearall" />
	<?php printAdminFooter();
	?>

</body>
</html>
