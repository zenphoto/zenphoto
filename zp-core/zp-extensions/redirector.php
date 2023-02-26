<?php

/**
 * A plugin to redirect internal URLs. Primarily intended for URLS that otherwise would cause 404 not found errors. 
 * URLs are redirected before before any theme page setup occurs. External URLs are not supported.
 * 
 * The plugin supports a JSON object file or a CSV file with outdate URL/new URL pairs.
 * 
 * Examples of files supported:
 * 
 * JSON:
 * 
 *    {
 * 	    "http:\/\/example.com/oldurl1/": "http:\/\/example.com/newurl1/",
 * 	    "http:\/\/example.com/oldurl2/": "http:\/\/example.com/newurl2/",
 *    }
 * 
 * Remember to escape the slashes!
 * 
 * CSV (comma separated):
 * 
 *     http://example.com/oldurl1/,http://example.com/newurl1/
 *     http://example.com/oldurl2/,http://example.com/newurl2/
 *     (…)
 * 
 * To use such a catalogue file create a folder `redirector` within the root `plugins` folder of your install and place the file within.
 * You can upload several and enable the one to use on the plugin options.
 * 
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\redirector
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('A plugin to redirect internal URLs. Primarily intended for URLs that otherwise would cause 404 not found errors.');
$plugin_author = "Malte Müller (acrylian)";
$plugin_category = gettext('Admin');
$option_interface = 'redirectorOptions';

zp_register_filter('redirection_handler', 'redirector::handleRequest');

/**
 * redirector plugin options
 */
class redirectorOptions {

	function __construct() {
		setOptionDefault('redirector_catalogue', '');
	}

	function getOptionsSupported() {
		$catalogues = self::getRedirectionFiles();
		return array(
				gettext('Redirection catalogue') => array(
						'key' => 'redirector_catalogue',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $catalogues,
						'order' => 0,
						'desc' => gettext('Place a JSON or CSV file within /plugins/redirector/ to use for redirecting.')),
				gettext('Debug mode') => array(
						'key' => 'redirector_debugmode',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('If enabled valid redirections will not be executed but logged in the debug log.'))
		);
	}

	/**
	 * Gets the redirection catalogue file list
	 * @return array
	 */
	static function getRedirectionFiles() {
		$catalogues = array();
		$files = getPluginFiles('redirector/*.*');
		foreach ($files as $file) {
			if (in_array(getSuffix($file), array('csv', 'json'))) {
				$catalogues[basename($file)] = $file;
			}
		}
		return $catalogues;
	}

}

/**
 * redirection handler class
 */
class redirector {

	/**
	 * Checks the current URL request with the catalogue and if found returns the new URL to redirect to.
	 * 
	 * @param string $request The URL request passed by the filter hook in controller.php
	 * @return string The URL to redirect to or the original request URL
	 */
	static function handleRequest($request) {
		$redirections = redirector::loadRedirections();
		if (!empty($redirections)) {
			foreach ($redirections as $key => $val) {
				$old = trim($key);
				$new = trim($val);
				if ($request == $old) {
					if (getOption('redirector_debugmode')) {
						debugLog('redirector plugin redirection debugging: ' . $old . ' => ' . $new);
						return $request;
					} else {
						return $new;
					}
				}
			}
		}
		return $request;
	}

	/**
	 * Loads the catalogue of old to new redirection URLs selected on the options
	 *  
	 * @return array
	 */
	static function loadRedirections() {
		$file = getOption('redirector_catalogue');
		$redirections = array();
		if (!empty($file)) {
			switch (getSuffix($file)) {
				case 'csv':
					if (($handle = fopen($file, "r")) !== FALSE) {
						while (($data = fgetcsv($handle)) !== FALSE) {
							$redirections[$data[0]] = $data[1];
						}
					} else {
						debugLog(gettext('redirector Error: Could not open and load the redirections catalogue file: ' . $file));
					}
					break;
				case 'json':
					$raw = file_get_contents($file);
					if ($raw !== false) {
						$redirections = json_decode($raw);
						unset($raw);
					} else {
						debugLog(gettext('redirector Error: Could not open and load the redirections catalogue file: ' . $file));
					}
					break;
			}
		}
		return $redirections;
	}

}
