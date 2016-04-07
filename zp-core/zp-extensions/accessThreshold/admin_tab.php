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
$accessThreshold_THRESHOLD = $recentIP['config']['accessThreshold_THRESHOLD'];
$accessThreshold_IP_ACCESS_WINDOW = $recentIP['config']['accessThreshold_IP_ACCESS_WINDOW'];

unset($recentIP['config']);

switch (@$_POST['data_sortby']) {
	case 'date':
		$sort = 'accessTime';
		$recentIP = sortMultiArray($recentIP, array('lastAccessed'), true, true, false, true);
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
	case'blocked':
		$sort = 'blocked';
		$recentIP = sortMultiArray($recentIP, array('blocked'), true, true, false, true);
		break;
	default:
		$sort = 'interval';
		$recentIP = sortMultiArray($recentIP, array('interval'), false, true, false, true);
		break;
}

$recentIP = array_slice($recentIP, 0, ($rows = ceil(getOption('accessThreshold_LIMIT') / 3)) * 3);
$output = array();
$__time = time();
$ct = 0;
$legendExpired = $legendBlocked = $legendInvalid = false;
foreach ($recentIP as $ip => $data) {
	if (isset($data['interval']) && $data['interval']) {
		$interval = sprintf('%.1f', $data['interval']);
	} else {
		continue;
	}
	if (isset($data['lastAccessed']) && $data['lastAccessed'] < $__time - $accessThreshold_IP_ACCESS_WINDOW) {
		$old = 'color:LightGrey;';
		$legendExpired = true;
	} else {
		$old = '';
	}
	if (isset($data['blocked']) && $data['blocked']) {
		$color = 'color:red;';
		$legendBlocked = true;
	} else {
		$color = '';
	}
	if (count($data['accessed']) < 10) {
		$invalid = 'color:LightGrey;';
		$legendInvalid = true;
	} else {
		$invalid = '';
	}
	$row = $ct % $rows;
	$out = '<span style="width:30%;float:left;';
	if ($even = floor($ct / $rows) % 2) {
		$out .= 'background-color:WhiteSmoke;';
	}
	$out .='">' . "\n";
	$out .= '  <span style="width:40%;float:left;"><span style="float:right;' . $color . '">' . $ip . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span>' . "\n";
	$out .= '  <span style="width:48%;float:left;' . $old . '">' . date('Y-m-d H:i:s', $data['lastAccessed']) . '</span>' . "\n";
	$out .= '  <span style="width:3%;float:left;"><span style="float:right;' . $invalid . '">' . $interval . '</span></span>' . "\n";
	$out .= "</span>\n";

	if (isset($output[$row])) {
		$output[$row] .= $out;
	} else {
		$output[$row] = $out;
	}
	$ct++;
}
if (empty($output)) {
	$output[] = gettext("No entries excede the noise level");
}

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
								<option value="<?php echo gettext('interval'); ?>" <?php if ($sort == 'interval') echo 'selected="selected"'; ?>><?php echo gettext('interval'); ?></option>
								<option value="<?php echo gettext('date'); ?>" <?php if ($sort == 'accessTime') echo 'selected="selected"'; ?>><?php echo gettext('date'); ?></option>
								<option value="<?php echo gettext('ip'); ?>" <?php if ($sort == 'ip') echo 'selected="selected"'; ?>><?php echo gettext('IP'); ?></option>
								<option value="<?php echo gettext('blocked'); ?>" <?php if ($sort == 'blocked') echo 'selected="selected"'; ?>><?php echo gettext('blocked'); ?></option>
							</select>
						</span>
					</form>
					<br style="clearall">
					<br />
					<?php
					zp_apply_filter('admin_note', 'database', '');
					foreach ($output as $row) {
						echo $row . '<br style="clearall">' . "\n";
					}
					?>
					<br style="clearall">
					<?php
					if ($legendBlocked) {
						echo '<p>' . gettext('IP addresses in <span style="color:Red;">red</span> have been blocked.') . '</p>';
					}
					if ($legendExpired) {
						echo '<p>' . gettext('Timestamps that are <span style="color:LightGrey;">grayed out</span> have expired.') . '</p>';
					}
					if ($legendInvalid) {
						echo '<p>' . gettext('Intervals that are <span style="color:LightGrey;">grayed out</span> have insufficient data to be valid.') . '</p>';
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
