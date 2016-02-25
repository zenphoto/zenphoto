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
			<div id="container">

				<table style="width:100%">
					<?php
					$col = 0;
					foreach ($recentIP as $entity => $data) {
						if ($col == 0) {
							echo "<tr>\n";
						}
						echo "<td>" . $entity . '&nbsp;&nbsp;&nbsp;' . date('Y-m-d H:i:s', $data['accessTime']) . '&nbsp;&nbsp;&nbsp;' . $data['counter'] . "</td>\n";

						if ($col == 4) {
							echo "</tr>\n";
							$col = 0;
						} else {
							$col++;
						}
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<br class = "clearall" />
	<?php printAdminFooter();
	?>

</body>
</html>
