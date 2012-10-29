<?php
/**
 * wraper for i.php so that we can serialize the caching process
 *
 * @package plugins
 */
define('OFFSET_PATH', 2);
require (dirname(dirname(dirname(__FILE__))).'/functions-basic.php');
if (isset($_GET['worker'])) {
	$worker = min(49, abs((int) $_GET['worker']));
} else {
	exitZP();
}

$cacheMutex = new Mutex('CacheManager-'.$worker);
$cacheMutex->lock();
require (dirname(dirname(dirname(__FILE__))).'/i.php');
$cacheMutex->unlock();
?>
