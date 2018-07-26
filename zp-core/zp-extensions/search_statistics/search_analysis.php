<?php
/**
 *
 * Collects and analyzes searches
 *
 * @author Stephen Billard (sbillard)
 * @package plugins/search_statistics
 */
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(OVERVIEW_RIGHTS, currentRelativeURL());

if (isset($_GET['reset'])) {
	admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
	XSRFdefender('search_statistics');
	$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="search_statistics"';
	query($sql);
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/search_statistics/search_analysis.php');
	exitZP();
}
printAdminHeader('overview', 'analysis');
?>
<link rel="stylesheet" href="../../admin-statistics.css" type="text/css" media="screen" />
<?php
echo '</head>';

$nodata = gettext('No search criteria collected.');
$criteria_maxvalue = 1;
$cacheHits = 0;
$data = $results_f = $results = $terms = $sites = array();
$bargraphmaxsize = 600;
$opChars = array('(', ')', '&', '|', '!', ',');

$sql = 'SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="search_statistics"';
$searches = query($sql);
while ($datum = db_fetch_assoc($searches)) {
	$element = getSerializedArray($datum['data']);
	if (is_array($element)) {
		if (isset($element['iteration'])) {
			$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="search_statistics"';
			query($sql);
			$nodata = gettext('Search criteria reset.');
			break;
		} else {
			if ($element['success'] === 'cache') {
				$cacheHits++;
				if ($criteria_maxvalue < $cacheHits) {
					$criteria_maxvalue = $cacheHits;
				}
			}
			if (array_key_exists($datum['subtype'], $data)) {
				$data[$datum['subtype']]['success'] = $data[$datum['subtype']]['success'] || $element['success'];
			} else {
				$data[$datum['subtype']] = $element;
				$ip = $datum['aux'];
				if (array_key_exists($ip, $sites)) {
					$sites[$ip] ++;
					if ($criteria_maxvalue < $sites[$ip]) {
						$criteria_maxvalue = $sites[$ip];
					}
				} else {
					$sites[$ip] = 1;
				}
			}
		}
	}
}

foreach ($data as $uid => $element) {

	$searchset = $element['data'];
	$type = $element['type'];
	$instance = implode(' ', $searchset);

	$success = $element['success'];
	if ($success) {
		if (array_key_exists($instance, $results)) {
			$results[$instance] ++;
			if ($criteria_maxvalue < $results[$instance]) {
				$criteria_maxvalue = $results[$instance];
			}
		} else {
			$results[$instance] = 1;
		}
	} else {
		if (array_key_exists($instance, $results_f)) {
			$results_f[$instance] ++;
			if ($criteria_maxvalue < $results_f[$instance]) {
				$criteria_maxvalue = $results_f[$instance];
			}
		} else {
			$results_f[$instance] = 1;
		}
	}

	foreach ($searchset as $instance) {
		if (!in_array($instance, $opChars)) {
			if (array_key_exists($instance, $terms)) {
				$terms[$instance] ++;
				if ($criteria_maxvalue < $terms[$instance]) {
					$criteria_maxvalue = $terms[$instance];
				}
			} else {
				$terms[$instance] = 1;
			}
		}
	}
}

foreach ($results_f as $key => $failed) {
	if (array_key_exists($key, $results)) { // really a successful search
		unset($results_f[$key]);
	}
}

$limit_i = getOption('search_statistics_ip_threshold');
$sitelimited = count($sites) > $limit_i;
arsort($sites);
$sites = array_slice($sites, 0, $limit_i, true);

$limit_t = getOption('search_statistics_terms_threshold');
$termlimited = count($terms) > $limit_t;
arsort($terms);
$terms = array_slice($terms, 0, $limit_t, true);

$limit_s = getOption('search_statistics_threshold');
$criterialimited = count($results) > $limit_s;
arsort($results);
$results = array_slice($results, 0, $limit_s, true);

$limit_f = getOption('search_statistics_failed_threshold');
$criterialimited_f = count($results_f) > $limit_f;
arsort($results_f);
$results_f = array_slice($results_f, 0, $limit_f, true);
?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'albums', ''); ?>
			<h1><?php echo (gettext('Search analysis')); ?></h1>
			<div class="tabbox">
				<?php
				if (empty($results) && empty($results_f) && empty($cacheHits)) {
					echo $nodata;
				} else {
					?>
					<table class="bordered">
						<?php
						if (!empty($results)) {
							?>
							<tr class="statistic_wrapper">
								<th class="statistic_short_title"><?php
									if ($criterialimited) {
										printf(gettext('Top %u successful search criteria'), $limit_s);
									} else {
										echo gettext('Successful search criteria');
									}
									?></th>
								<th class="statistic_graphwrap"></th>
							</tr>
							<?php
							foreach ($results as $criteria => $count) {
								$barsize = ceil($count / $criteria_maxvalue * $bargraphmaxsize);
								?>
								<tr class="statistic_wrapper">
									<td class="statistic_short_title" >
										<strong><?php echo $criteria; ?></strong>
									</td>
									<td class="statistic_graphwrap" >
										<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>px"></div>
										<div class="statistic_value"><?php echo $count; ?></div>
									</td>
								</tr>
								<tr>
									<td></td><td></td>
								</tr>
								<?php
							}
						}
						if (!empty($results_f)) {
							?>
							<tr class="statistic_wrapper">
								<th class="statistic_short_title"><?php
									if ($criterialimited_f) {
										printf(gettext('Top %u failed search criteria'), $limit_f);
									} else {
										echo gettext('Failed search criteria');
									}
									?></th>
								<th class="statistic_graphwrap"></th>
							</tr>
							<?php
							foreach ($results_f as $criteria => $count) {
								$barsize = ceil($count / $criteria_maxvalue * $bargraphmaxsize);
								?>
								<tr class="statistic_wrapper">
									<td class="statistic_short_title" >
										<strong><?php echo $criteria; ?></strong>
									</td>
									<td class="statistic_graphwrap" >
										<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>px"></div>
										<div class="statistic_value"><?php echo $count; ?></div>
									</td>
								</tr>
								<tr>
									<td></td><td></td>
								</tr>
								<?php
							}
						}
						if (!empty($terms)) {
							?>
							<tr class="statistic_wrapper">
								<th class="statistic_short_title"><?php
									if ($termlimited) {
										printf(gettext('Top %u search terms used'), $limit_t);
									} else {
										echo gettext('Search terms used');
									}
									?></th>
								<th class="statistic_graphwrap"></th>
							</tr>
							<?php
							foreach ($terms as $criteria => $count) {
								$barsize = ceil($count / $criteria_maxvalue * $bargraphmaxsize);
								?>
								<tr class="statistic_wrapper">
									<td class="statistic_short_title" >
										<strong><?php echo $criteria; ?></strong>
									</td>
									<td class="statistic_graphwrap" >
										<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>px"></div>
										<div class="statistic_value"><?php echo $count; ?></div>
									</td>
								</tr>
								<tr>
									<td></td><td></td>
								</tr>
								<?php
							}
						}
						if (!empty($cacheHits)) {
							$barsize = ceil($cacheHits / $criteria_maxvalue * $bargraphmaxsize);
							?><tr class="statistic_wrapper">
								<th class="statistic_short_title"><?php
									echo gettext('Cache hits');
									?></th>
								<th class="statistic_graphwrap"></th>
							</tr>
							<tr class="statistic_wrapper">
								<td class="statistic_short_title" >

								</td>
								<td class="statistic_graphwrap" >
									<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>px"></div>
									<div class="statistic_value"><?php echo $cacheHits; ?></div>
								</td>
							</tr>
							<tr>
								<td></td><td></td>
							</tr>
							<?php
						}
						if (!empty($sites)) {
							?>
							<tr class="statistic_wrapper">
								<th class="statistic_short_title"><?php
									if ($sitelimited) {
										printf(gettext('Top %u Search IDs'), $limit_i);
									} else {
										echo gettext('Search IDs');
									}
									?></th>
								<th class="statistic_graphwrap"></th>
							</tr>
							<?php
							foreach ($sites as $ip => $count) {
								$barsize = ceil($count / $criteria_maxvalue * $bargraphmaxsize);
								?>
								<tr class="statistic_wrapper">
									<td class="statistic_short_title" >
										<strong><?php echo $ip; ?></strong>
									</td>
									<td class="statistic_graphwrap" >
										<div class="statistic_bargraph" style="width: <?php echo $barsize; ?>px"></div>
										<div class="statistic_value"><?php echo $count; ?></div>
									</td>
								</tr>
								<tr>
									<td></td><td></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
					<?php
					if (zp_loggedin(ADMIN_RIGHTS)) {
						?>
						<p class="buttons">
							<a href="?reset&amp;XSRFToken=<?php echo getXSRFToken('search_statistics'); ?>"><?php echo gettext('reset'); ?></a>
						</p>
						<br class="clearall">
						<p>
							<a href="<?php echo WEBPATH . '/' . ZENFOLDER ?>/admin-options.php?tab=plugin&amp;single=search_statistics#search_statistics" ><?php echo gettext('Change <em>Threshold</em> values') ?></a>
						</p>
						<?php
					} else {
						?>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php printAdminFooter(); ?>
	</div>
</body>
<?php
echo "</html>";
?>
