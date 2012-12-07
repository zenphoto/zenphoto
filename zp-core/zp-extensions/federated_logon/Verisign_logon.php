<?php
/**
 * Google accounts logon handler.
 *
 * This just supplies the Yahoo URL to OpenID_try.php. The rest is normal OpenID handling
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */

require_once('OpenID_common.php');
session_start();

if (isset($_GET['redirect'])) {
	$redirect = sanitizeRedirect($_GET['redirect']);
} else {
	$redirect = '';
}
$_SESSION['OpenID_redirect'] = $redirect;
$_SESSION['OpenID_cleaner_pattern'] = '/(.*)\.pip\.verisignlabs\.com/';
$_SESSION['provider'] = 'Verisign';
$_GET['openid_identifier'] = 'http://pip.verisignlabs.com';
if (isset($_GET['user']) && $_GET['user']) {
	$_GET['openid_identifier'] = 'http://'.$_GET['user'].'.pip.verisignlabs.com';
	$_GET['action'] = 'verify';
	unset($_GET['user']);
	require 'OpenID_try_auth.php';
	exit(0);
}

?>
<html>
  <head>
	<link rel="stylesheet" href="federated_logon.css" type="text/css" />
  <title><?php echo gettext('Verisign Authentication'); ?></title>
  </head>
  <script type="text/javascript">
  	function submiturl() {
  	  var userid = document.submit_openid.openid_identifier.value;
  	  var uri ='http://'+userid+'.pip.verisignlabs.com';
  		document.submit_openid.openid_identifier.value = uri;
  		return true;
  	}
  </script>
  <body>
    <h1><?php echo gettext('Verisign Authentication'); ?></h1>

    <div id="verify-form">
      <form method="get" name="submit_openid" action="OpenID_try_auth.php" onsubmit="return submiturl();">
  			<p><img alt="" src="Verisign.png"></p>
        <?php echo gettext('Verisign user id:'); ?>
        <input type="hidden" name="action" value="verify" />
        <input type="text" size="60" name="openid_identifier" id="openid_identifier" value="" />
        <input type="submit" value="<?php echo gettext('Verify') ?>" />
      </form>
      <?php
      if (!empty($redirect)) {
      	?>
      	<p>
         <a href="<?php echo $redirect; ?>" title="<?php echo gettext('Return to Zenphoto'); ?>" ><?php echo gettext('Return to Zenphoto'); ?></a>
      	</p>
				<?php
			}
			?>
    </div>
  </body>
</html>
