<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/admin-functions.php');
zp_session_start();
XSRFdefender('elFinder');

include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderConnector.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinder.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeDriver.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeFTP.class.php';

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

$opts = array(
	// 'debug' => true,
	'roots' => array(
		array(
			'driver'     => 'LocalFileSystem',
			'startPath'  => SERVERPATH.'/uploaded/',
			'path'       =>	SERVERPATH.'/uploaded/',
			'URL'        =>	WEBPATH.'/uploaded/',
			// 'treeDeep'   => 3,
			// 'alias'      => 'File system',
			'mimeDetect' => 'internal',
			'tmbPath'    => '.tmb',
			'utf8fix'    => true,
			'tmbCrop'    => false,
			'tmbBgColor' => 'transparent',
			'accessControl' => 'access',
			'acceptedName'    => '/^[^\.].*$/',
			// 'tmbSize' => 128,
			'attributes' => array(
				array(
					'pattern' => '/\.js$/',
					'read' => true,
					'write' => false
				),
				array(
					'pattern' => '/^\/icons$/',
					'read' => true,
					'write' => false
				)
			)
			// 'uploadDeny' => array('application', 'text/xml')
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

