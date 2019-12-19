<?php
/**
 * Displays which HTTP headers your site sends
 * @author Malte Müller (acrylian>
 * @package admin
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$buttonlist[] = array(
		'category' => gettext('Info'),
		'enable' => true,
		'button_text' => gettext('HTTP header inspector'),
		'formname' => 'http_header_inspector',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/http_header_inspector.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/info.png',
		'title' => gettext('Displays which HTTP headers your site sends.'),
		'alt' => gettext('HTTP header inspector'),
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

admin_securityChecks(NULL, currentRelativeURL());

$zenphoto_tabs['overview']['subtabs'] = array(gettext('HTTP header inspector') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/http_header_inspector.php');
printAdminHeader('overview', 'http_header_inspector');
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<h1><?php echo (gettext('HTTP header inspector')); ?></h1>
				<p><?php echo gettext('Inspect which HTTP headers your site generally sends.'); ?></p>
				<?php
				$streamwrappers = stream_get_wrappers();
				if(in_array(PROTOCOL, $streamwrappers)) {
					$check_headers = array(
							array(
									'headline' => gettext('Frontend headers'),
									'headers' => get_headers(FULLWEBPATH . '/'),
							),
							array(
									'headline' => gettext('Backend headers'),
									'headers' => get_headers(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php')
							)
					);
					foreach ($check_headers as $check_header) {
						?>
						<h2><?php echo html_encode($check_header['headline']); ?></h2>
						<ul>
							<?php
							foreach ($check_header['headers'] as $header) {
								echo '<li>' . $header . '</li>';
							}
							?>
						</ul>
						<hr>
						<?php
					} 
				} else {
					echo '<p class="notebox">' . sprintf(gettext("Your server does not support getting header info via %s"), PROTOCOL) . '</p>';
				}
				?>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>
?>


