<?php
/*
 * popup to display IP list for an entry
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

$ip = sanitize($_GET['selected_ip']);
$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
$list = array();
if (isset($recentIP[$ip])) {
	foreach ($recentIP[$ip]['accessed'] as $instance) {
		if (is_array($instance)) {
			$list[] = $instance['ip'];
		}
	}
}
$list = array_unique($list);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php printStandardMeta(); ?>
		<title><? echo $ip; ?></title>
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.css" type="text/css" />
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
			<?php echo $ip; ?>
			<div id="content">
				<ol>
					<?php
					foreach ($list as $ip) {
						echo '<li>' . $ip . '</li>';
					}
					?>
				</ol>
			</div>
		</div>
	</body>




