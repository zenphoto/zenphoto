<?php
/**
 * This is the "accessThreshold" upload tab
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(DEBUG_RIGHTS, $return = currentRelativeURL());


$subtab = getSubtabs();
printAdminHeader('overview', $subtab);

$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
unset($recentIP['config']);
$recentIP = sortMultiArray($recentIP, 'counter', true);
$noise = getOption('accessThreshold_NOISE');
echo "\n</head>";
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if (empty($recentIP)) {
				echo gettext("No entries");
			}
			?>

			<?php
			$c = 0;
			foreach ($recentIP as $entity => $data) {
				if ($data['counter'] < $noise)
					break;
				echo '<span style="width:25%;float:left">';
				echo '<span style="width:40%;float:left">' . $entity . '</span>';
				echo '<span style="width:48%;float:left">' . date('Y-m-d H:i:s', $data['accessTime']) . '</span>';
				echo '<span style="width:3%;float:left">' . $data['counter'] . '</span>';
				echo "</span>\n";
				$c++;
				if ($c > 3) {
					$c = 0;
					echo '<br clear="both">';
				}
			}
			?>

		</div>
	</div>
	<br class = "clearall" />
	<?php printAdminFooter();
	?>

</body>
</html>
