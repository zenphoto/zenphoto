<?php
/**
 * Displays locale information of the system
 * @package zpcore\admin\utilities
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$buttonlist[] = array(
		'category' => gettext('Info'),
		'enable' => true,
		'button_text' => gettext('Locale info'),
		'formname' => 'listlocales',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/list_locales.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/info.png',
		'title' => gettext('Display information about installed locales.'),
		'alt' => gettext('PHPInfo'),
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

admin_securityChecks(NULL, currentRelativeURL());
$_zp_admin_menu['overview']['subtabs'] = array(gettext('Locale info') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/list_locales.php');
printAdminHeader('overview', 'List locales');
?>
<style>
	.localelist ul {
		margin: 0 0px 10px 0;
		padding: 0 0 0 15px;
	}
</style>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<h1><?php echo (gettext('Locale information.')); ?></h1>
				<p><?php gettext('You system has support for the following locales:'); ?></p>
				<?php
				$locales = getSystemLocales();
				$httpaccept = parseHttpAcceptLanguage();
				if (count($httpaccept) > 0) {
					$accept = $httpaccept;
					$accept = array_shift($accept);
					?>
					<h2><?php echo gettext('Http Accept Languages'); ?></h2>
					<p><?php echo gettext('The locales your browser has defined to accept.'); ?></p>
					<table class='bordered'>
						<tr>
							<th><?php echo gettext('Key'); ?></th>
							<?php
							foreach ($accept as $key => $value) {
								?>
								<th><?php echo $key; ?></th>
								<?php
							}
							?>
						</tr>
						<?php
						foreach ($httpaccept as $key => $accept) {
							?>
							<tr>
								<td><?php echo $key; ?></td>
								<?php
								foreach ($accept as $key2 => $value) {
									?>
									<td>
										<?php echo $value; 
										if($key2 == 'fullcode') {
											echo ' – ' . getLanguageDisplayName($value);
										}
										?></td>
									<?php
								} ?>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
				}
				?>
				<h2><?php echo gettext('Supported system locales'); ?></h2>
				<p><?php echo gettext('These are the locales that are installed and supported on your server.'); ?></p>
				<?php
				if (empty($locales)) {
					?>
					<p class="notebox"><?php printf(gettext('Sorry, the locales cannot be listed as the required <a href="%s">PHP class ResourceBundle</a> is not available on your system. We suggest you contact your host about this.'), 'https://www.php.net/manual/en/class.resourcebundle.php'); ?></p>
					<?php
				} else {
					?>
					<ul class="localelist">
						<?php
						foreach ($locales as $locale) {
							if (!empty($locale)) {
								echo '<li><ul>';
								foreach ($locale as $loc) {
									if($langname = getLanguageDisplayName($loc)) {
										$langname = ' – ' . $langname;
									}
									echo '<li><strong>' . $loc . '</strong>' . $langname . '</li>';
								}
								echo '</ul></li>';
							}
						}
						?>
					</ul>
				<?php } ?>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>