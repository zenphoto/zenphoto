<?php
/**
 * This is the "accessThreshold" upload tab
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(DEBUG_RIGHTS, $return = currentRelativeURL());


$subtab = getSubtabs();
printAdminHeader('development', $subtab);

$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
unset($recentIP['config']);
$recentIP = sortMultiArray($recentIP, array('counter'), true, true, false, true);
$recentIP = array_slice($recentIP, 0, getOption('accessThreshold_LIMIT'));

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
				?>
				<div class="tabbox">
					<?php
					zp_apply_filter('admin_note', 'database', '');
					$ct = 0;
					foreach ($recentIP as $entity => $data) {
						echo '<span style="width:25%;float:left">';
						echo '<span style="width:40%;float:left">' . $entity . '</span>';
						echo '<span style="width:48%;float:left">' . date('Y-m-d H:i:s', $data['accessTime']) . '</span>';
						echo '<span style="width:3%;float:left">' . $data['counter'] . '</span>';
						echo "</span>\n";
						$ct++;
						if ($ct % 4 == 0) {
							echo '<br clear="both">';
						}
					}
					if (empty($ct)) {
						echo gettext("No entries excede the noise level");
					} else {
						echo '<br clear="both">';
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<br class = "clearall" />
	<?php printAdminFooter();
	?>

</body>
</html>
