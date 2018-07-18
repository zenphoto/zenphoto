<?php

/*
 * PHP QR Code encoder
 *
 * feeds a QR code image
 *
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/functions-basic.php');

require_once ('qrlib.php');

$iMutex = new zpMutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();
QRcode::png($_REQUEST['content']);
$iMutex->unlock();
