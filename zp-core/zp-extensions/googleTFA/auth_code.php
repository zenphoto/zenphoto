<?php
/**
 * presents a form to get the user's googleAuthenticator authorization code.
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/Secret.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/SecretFactory.php');

var_dump($_SESSION['OTA'], $_POST);

if (isset($_SESSION['OTA'])) {
	$user = $_SESSION['OTA']['user'];
	$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
	if ($userobj->getOTAsecret()) {

		if (isset($_POST['authenticate'])) {
			require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/Base32.php');
			require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/Cache.php');
			require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/GoogleAuthenticator.php');

			$googleAuth = new Dolondro\GoogleAuthenticator\GoogleAuthenticator();
			/*
			 * for this to work there needs to be an implementation of a cacheItemPool
			  $googleAuth->setCache($cacheItemPoolInterface);
			 */
			$authOK = $googleAuth->authenticate($userobj->getOTAsecret(), $_POST['authenticate']);
			if ($authOK) {
				_Authority::logUser($userobj);
				header('Location: ' . $_SESSION['OTA']['redirect']);
				exitZP();
			}
		}
		printAdminHeader('overview');
		echo "\n</head>";
		?>
		<body style="background-image: none">
			<div id="loginform">
				<p>
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png" title="ZenPhoto" alt="ZenPhoto" />
				</p>

				<?php
				if (isset($authOK)) {
					?>
					<div class="errorbox" id="message">
						<h2><?php echo gettext("The PIN you entered is not valid."); ?></h2>
					</div>
					<?php
				}
				?>
				<form name="OTP" id="OTP" action="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/auth_code.php'; ?>" method="post">
					<input type="hidden" name="authenticate" value="1" />
					<fieldset id="logon_box">
						<legend><?php echo gettext('Google Autenticator PIN'); ?></legend>
						<input class="textfield" name="authenticate" id="authcode" type="text"  />
						<br />
						<br />
						<div class="buttons">
							<button type="submit" value="<?php echo gettext("pin"); ?>" >
								<?php echo CHECKMARK_GREEN; ?>
								<?php echo gettext("Submit"); ?>
							</button>
							<button type="button" title="<?php echo gettext("Cancel"); ?>" onclick="window.location = '<?php echo FULLWEBPATH; ?>';">
								<?php echo CROSS_MARK_RED; ?>
								<?php echo gettext("Cancel"); ?>
							</button>

						</div>
						<br class="clearall">
					</fieldset>
				</form>
			</div>
		</body>
		<?php
		echo "\n</html>";
	}
}