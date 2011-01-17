<?php
/*
 * google_maps -- map server
 *
 * @package plugins
 */

// force UTF-8 Ø

define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/GoogleMap.php');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php googlemap_js(); ?>
</head>
<body>
<?php
$map = unserialize($_GET['mapobject']);
echo $map->getMapJS();
echo $map->printOnLoad();
echo $map->printMap();
?>
</body>
</html>