<?php
/**
 * script for ZenPhoto20 logon button action.
 *
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);

require_once(dirname(__FILE__) . '/admin-globals.php');
zp_register_filter('alt_login_handler', 'removeAltHandlers', 0);

printAdminHeader('login');
echo "\n</head>";
?>
<body style="background-image: none">
	<?php $_zp_authority->printLoginForm($_GET['redirect']); ?>
</body>
<?php
echo "\n</html>";
exitZP();

$_zp_authority->printLoginForm(@$_GET['redirect']);
exitZP();

function removeAltHandlers($list) {
	return array();
}
?>
