<?php
/**
 * Handles generic OpenID logon
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */

require_once('OpenID_common.php');
if (session_id() == '') session_start();

if (isset($_GET['redirect'])) {
	$redirect = sanitizeRedirect($_GET['redirect']);
} else {
	$redirect = '';
}
$_SESSION['OpenID_redirect'] = $redirect;
$_SESSION['OpenID_cleaner_pattern'] = '';
$_SESSION['provider'] = '';
if (isset($_GET['user']) && $_GET['user']) {
	$_GET['openid_identifier'] = $_GET['user'];
	$_GET['action'] = 'verify';
	unset($_GET['user']);
	require 'OpenID_try_auth.php';
	exit(0);
}

?>
<html>
  <head>
	<link rel="stylesheet" href="federated_logon.css" type="text/css" />
  <title><?php echo gettext('OpenID Authentication'); ?></title>
  </head>
  <body>
    <h1><?php echo gettext('OpenID Authentication'); ?></h1>

    <?php if (isset($msg)) { print "<div class=\"alert\">$msg</div>"; } ?>
    <?php if (isset($error)) { print "<div class=\"error\">$error</div>"; } ?>
    <?php if (isset($success)) { print "<div class=\"success\">$success</div>"; } ?>

    <div id="verify-form">
      <form method="get" action="OpenID_try_auth.php">
        <?php echo gettext('Identity URL:'); ?>
        <input type="hidden" name="action" value="verify" />
        <input type="text" size="60" name="openid_identifier" value="" />


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
