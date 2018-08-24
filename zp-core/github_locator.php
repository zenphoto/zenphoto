<?php

/*
 * For use until migration to the netPhotoGraphics organization is acomplished
 *
 *
 */

require_once( dirname(__FILE__) . '/' . PLUGIN_FOLDER . '/common/gitHubAPI/github-api.php');

use Milo\Github;

if (getOption('GitHubOwner_lastCheck') + 8640 < time()) {
	setOption('GitHubOwner_lastCheck', time());
	foreach (array('netPhotoGraphics', 'ZenPhoto20') as $owner) {
		try {
			$api = new Github\Api;
			$fullRepoResponse = $api->get('/repos/:owner/:repo/releases/latest', array('owner' => $owner, 'repo' => 'netPhotoGraphics'));
			$fullRepoData = $api->decode($fullRepoResponse);
			setOption('GitHubOwner', $owner);
			define('GITHUB_ORG', $owner);
			break;
		} catch (Exception $e) {

		}
	}
}
