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

$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
$__config = $recentIP['config'];
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
		uasort($recentIP, function($a, $b) {
			$a_i = $a['interval'];
			$b_i = $b['interval'];
			if ($a_i === $b_i) {
				return 0;
			} else if ($a_i === 0) {
				return 1;
			} else if ($b_i === 0) {
				return -1;
			}
			return strnatcmp($a_i, $b_i);
		});
		break;
}
$slice = ceil(min(count($recentIP), getOption('accessThreshold_LIMIT')) / 3) * 3;
$recentIP = array_slice($recentIP, 0, $slice);
$rows = ceil(count($recentIP) / 3);

$output = array();
$__time = time();
$ct = 0;
$legendExpired = $legendBlocked = $legendLocaleBlocked = $legendClick = $legendInvalid = false;
foreach ($recentIP as $ip => $data) {
	$ipDisp = $ip;
	$localeBlock = $invalid = '';

	if (isset($data['interval']) && $data['interval']) {
		$interval = sprintf('%.1f', $data['interval']);
	} else {
		$interval = '&hellip;';
	}
	if (isset($data['lastAccessed']) && $data['lastAccessed'] < $__time - $__config['accessThreshold_IP_ACCESS_WINDOW']) {
		$old = 'color:LightGrey;';
		$legendExpired = '<p>' . gettext('Timestamps that are <span style="color:LightGrey;">grayed out</span> have expired.') . '</p>';
		;
	} else {
		$old = '';
	}
	if (isset($data['blocked']) && $data['blocked']) {
		if ($data['blocked'] == 1) {
			$localeBlock = '<span style="color:red;">&sect;</span> ';
			$legendLocaleBlocked = $localeBlock . gettext('blocked because of <em>locale</em> abuse.');
		} else {
			$invalid = 'color:red;';
			$legendBlocked = gettext('Address with intervals that are <span style="color:Red;">red</span> have been blocked. ');
		}
		$legendClick = '<br />&nbsp;&nbsp;&nbsp;' . gettext('Click on the address for a list of IPs and <em>locales</em> seen.');
		$ipDisp = '<a onclick="$.colorbox({
										close: \'' . gettext("close") . '\',
										maxHeight: \'80%\',
										maxWidth: \'80%\',
										innerWidth: \'560px\',
										href:\'ip_list.php?selected_ip=' . $ip . '\'});">' . $ip . '</a>';
	}
	if (count($data['accessed']) < 10) {
		$invalid = 'color:LightGrey;';
		$legendInvalid = '<p>' . gettext('Intervals that are <span style="color:LightGrey;">grayed out</span> have insufficient data to be valid.') . '</p>';
	}
	$row = $ct % $rows;
	$out = '<span style="width:33%;float:left;';
	if ($even = floor($ct / $rows) % 2) {
		$out .= 'background-color:WhiteSmoke;';
	}

	$out .='">' . "\n";
	$out .= '  <span style="width:42%;float:left;"><span style="float:right;">' . $localeBlock . $ipDisp . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span>' . "\n";
	$out .= '  <span style="width:48%;float:left;' . $old . '">' . date('Y-m-d H:i:s', $data['lastAccessed']) . '</span>' . "\n";
	$out .= '  <span style="width:9%;float:left;"><span style="float:right;">' . '<span style="' . $invalid . '">' . $interval . '</span></span></span>' . "\n";
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

printAdminHeader('admin');
echo "\n</head>";
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'access', ''); ?>
			<h1>
				<?php
				echo gettext('Access threshold');
				?>
			</h1>
			<div id="container">

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
					foreach ($output as $row) {
						echo $row . '<br style="clearall">' . "\n";
					}
					?>
					<br style="clearall">
					<?php
					echo $legendExpired;
					echo $legendInvalid;
					if ($legendBlocked || $legendLocaleBlocked) {
						echo '<p>';
						echo $legendBlocked;
						if ($legendBlocked && $legendLocaleBlocked) {
							echo '<br />';
						}
						echo $legendLocaleBlocked;
						echo $legendClick;
						echo '</p>';
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php printAdminFooter();
	?>

</body>
</html>
