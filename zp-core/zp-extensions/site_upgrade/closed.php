<?php
if (isset($_GET['rss'])) {
	if (file_exists(SERVERPATH.'/'.DATA_FOLDER.'/rss-closed.xml')) {
		$xml = file_get_contents(SERVERPATH.'/'.DATA_FOLDER.'/rss-closed.xml');
		$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>'.date("r",time()).'</pubDate>', $xml);
		echo $xml;
	}
}
header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="refresh" content="600;url=%5$s">
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>%1$s</title>
<style>
#closed {
	border-top: 5px solid #996600;
	border-left: 5px solid #996600;
	border-right: 5px solid #996600;
	border-bottom: 5px solid #996600;
	font-size: 200%%;
	text-align: center;
	border-radius: 10px 10px 10px 10px;
	width: 600px;
	height: 400px;
	background-image: url(%2$s);
	margin-top: 0px;
	padding-top: 0px;
}
#outer {
	height: 500px;
	width: 700px;
	padding-top: 20px;
	margin-top: 20px;
	margin-left: auto;
	margin-right: auto;
}
#mid {
	height: 220px;
}
</style>
</head>
<body>
	<div id="outer">
		<div id="closed">
			<p >%3$s</p>
			<div id="mid"></div>
			<p>%4$s</p>
		</div>
	</div>
</body>
</html>
