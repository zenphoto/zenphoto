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

switch (@$_POST['data_sortby']) {
	case 'date':
		$sort = 'accessTime';
		break;
	case 'ip':
		$sort = 'ip';
		uksort($recentIP, function($a, $b) {
			$retval = 0;
			$_a = explode('.', str_replace(':', '.', $a));
			$_b = explode('.', str_replace(':', '.', $b));
			foreach ($_a as $key => $va) {
				if ($retval == 0) {
					$retval = strnatcmp($va, @$_b[$key]);
				} else {
					break;
				}
			}
			return $retval;
		});
		break;
	default:
		$sort = 'counter';
		$recentIP = sortMultiArray($recentIP, array('counter'), true, true, false, true);
		break;
}

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
					<form name="data_sort" style="float: right;" method="post" action="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/accessThreshold/admin_tab.php?action=data_sortorder&tab=accessThreshold" >
						<span class="nowrap">
							<?php echo gettext('Sort by:'); ?>
							<select id="sortselect" name="data_sortby" onchange="this.form.submit();">
								<option value="<?php echo gettext('counter'); ?>" <?php if ($sort == 'counter') echo 'selected="selected"'; ?>><?php echo gettext('count'); ?></option>
								<option value="<?php echo gettext('date'); ?>" <?php if ($sort == 'accessTime') echo 'selected="selected"'; ?>><?php echo gettext('date'); ?></option>
								<option value="<?php echo gettext('ip'); ?>" <?php if ($sort == 'ip') echo 'selected="selected"'; ?>><?php echo gettext('IP'); ?></option>
							</select>
						</span>
					</form>
					<br clear="all">
					<br />
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
