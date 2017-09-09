<?php
/*
 * popup to display IP list for an entry
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

$ip = sanitize($_GET['selected_ip']);
$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
$localeList = $ipList = array();
if (isset($recentIP[$ip])) {
	foreach ($recentIP[$ip]['accessed'] as $instance) {
		$ipList[] = $instance['ip'];
	}
	$ipList = array_unique($ipList);
	foreach ($recentIP[$ip]['locales'] as $instance => $data) {
		foreach ($data['ip'] as $ipl => $time) {
			$localeList[$ipl][$instance] = $time;
		}
	}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" />
<head>
	<?php printStandardMeta(); ?>
	<title><? echo $ip; ?></title>
	<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css?ZenPhoto20_<?PHP ECHO ZENPHOTO_VERSION; ?>" type="text/css" />
	<style>
		ul, ol {
			list-style: none;
			padding: 0;
		}
		li {
			margin-left: 1.5em;
			padding-bottom: 0.5em;
		}
	</style>
</head>
<body>
	<div id="main">
		<?php
		echo $ip;
		?>
		<div id="content">
			<ol>
				<?php
				foreach ($ipList as $ip) {
					echo '<li>';
					echo $ip;
					$host = gethostbyaddr($ip);
					if ($host && $host != $ip) {
						echo' (' . $host . ')';
					}

					if (isset($localeList[$ip])) {
						echo '<ol>';
						foreach ($localeList[$ip] as $instance => $time) {

							echo '<li>' . $instance . '</li>';
						}
						echo '</ol>';
					}
					echo '</li>';
				}
				?>
			</ol>
		</div>
	</div>
</body>




