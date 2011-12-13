<?php
	if(!defined('AJAX_INIT_DONE'))
	{
		die('Permission denied');
	}
?>
<?php	// Zenphoto security stuff

define('OFFSET_PATH', 99);
$basepath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once($basepath."/admin-globals.php");

if (!getOption('zp_plugin_ajaxFilemanager')) {
	die('<p style="padding: 10px 15px 10px 15px;
	background-color: #FDD;
	border-width: 1px 1px 2px 1px;
	border-style: solid;
	border-color: #FAA;
	margin-bottom: 10px;
	font-size: 100%;">'.gettext('The ajaxFilemanager plugin is currently disabled. To use this feature, enable the plugin from the <em>plugins</em> tab.').'</p>');
}

?>
<?php
/**
 * the purpose I added this class is to make the file system much flexible
 * for customization.
 * Actually,  this is a kind of interface and you should modify it to fit your system
 * @author Logan Cai (cailongqun [at] yahoo [dot] com [dot] cn)
 * @link www.phpletter.com
 * @since 4/August/2007
 */
	class Auth
	{
		var $__loginIndexInSession = 'ajax_user';
		function __construct()
		{

		}

		function Auth()
		{
			$this->__construct();
		}
		/**
		 * check if the user has logged
		 *
		 * @return boolean
		 */
		function isLoggedIn()
		{
			if (zp_loggedin(FILES_RIGHTS | ZENPAGE_NEWS_RIGHTS | MANAGE_ALL_NEWS_RIGHTS | ZENPAGE_PAGES_RIGHTS | MANAGE_ALL_PAGES_RIGHTS)) {
				if (isset($_GET['XSRFToken'])) {
					$_SESSION['XSRFToken'] = $_GET['XSRFToken'];
				} else {
					$_REQUEST['XSRFToken'] = @$_SESSION['XSRFToken'];
				}
				XSRFdefender('ajaxfilemanager');
				GLOBAL $session;
				$session->debug = false;
				return true;
			}
			die('<p style="padding: 10px 15px 10px 15px;
						background-color: #FDD;
						border-width: 1px 1px 2px 1px;
						border-style: solid;
						border-color: #FAA;
						margin-bottom: 10px;
						font-size: 100%;">'.gettext('You do not have the <em>Rights</em> to access the filemanager.').'</p>');
		}
		/**
		 * validate the username & password
		 * @return boolean
		 *
		 */
		function login()
		{
			return false;	// Only Zenphoto credentials allowed
		}
		/**
		 *
		 * Generates an XSRF token
		 * @return string
		 */
		function generateToken()
		{
			return getXSRFToken('ajaxfilemanager');;
		}

	}
?>