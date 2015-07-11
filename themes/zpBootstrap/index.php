<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();

if (getOption('zpB_homepage')) {
	$isHomePage = true;
	include('home.php');
} else {
	$isHomePage = false;
	include('gallery.php');
}
?>