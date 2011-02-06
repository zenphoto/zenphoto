<?php
/**
 * Handles generic OpenID logon
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */
require_once('OpenID_common.php');
if (!defined('OFFSET_PATH')) define('OFFSET_PATH',4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');

if (isset($_GET['redirect'])) {
	$redirect = sanitize($_GET['redirect']);
} else {
	$redirect = '';
}
zp_setCookie('OpenID_redirect', $redirect, 60);

global $pape_policy_uris;
?>
<html>
  <head><title><?php echo gettext('OpenID Authentication'); ?></title></head>
  <style type="text/css">
      * {
        font-family: verdana,sans-serif;
      }
      body {
        width: 50em;
        margin: 1em;
      }
      div {
        padding: .5em;
      }
      table {
        margin: none;
        padding: none;
      }
      .alert {
        border: 1px solid #e7dc2b;
        background: #fff888;
      }
      .success {
        border: 1px solid #669966;
        background: #88ff88;
      }
      .error {
        border: 1px solid #ff0000;
        background: #ffaaaa;
      }
      #verify-form {
        border: 1px solid #777777;
        background: #dddddd;
        margin-top: 1em;
        padding-bottom: 0em;
      }
  </style>
  <body>
    <h1><?php echo gettext('OpenID Authentication'); ?></h1>

    <?php if (isset($msg)) { print "<div class=\"alert\">$msg</div>"; } ?>
    <?php if (isset($error)) { print "<div class=\"error\">$error</div>"; } ?>
    <?php if (isset($success)) { print "<div class=\"success\">$success</div>"; } ?>

    <div id="verify-form">
      <form method="get" action="OpenID_try_auth.php">
        <?php echo gettext('Identity URL:'); ?>
        <input type="hidden" name="action" value="verify" />
        <input type="text" size="50" name="openid_identifier" value="" />


        <input type="submit" value="<?php echo gettext('Verify') ?>" />
      </form>
    </div>
  </body>
</html>
