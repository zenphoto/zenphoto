<?php
/**
 * This is the "tokens" upload tab
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
admin_securityChecks(DEBUG_RIGHTS, $return = currentRelativeURL());

if (isset($_POST['delete_cookie'])) {
	foreach ($_POST['delete_cookie']as $cookie => $v) {
		zp_clearCookie(postIndexDecode($cookie));
	}
	header('location: ?page=develpment&tab=cookie');
	exitZP();
}

$subtab = getCurrentTab();
printAdminHeader('development', $subtab);

echo "\n</head>";
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php
				$subtab = getCurrentTab();
				zp_apply_filter('admin_note', 'development', $subtab);

				switch ($subtab) {
					case 'phpinfo':
						?>
						<h1>
							<?php
							echo gettext('Your PHP configuration information.');
							?>
						</h1>
						<?php
						break;
					case 'env':
						?>

						<h1>
							<?php echo gettext('Environment variable array'); ?>
						</h1>

						<span class="option_info floatright">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Environmental variables will not be provide unless your PHP.ini directive <code>variables_order</code> includes "E". e.g. <code>variables_order = "EGPCS"</code>'); ?>
							</div>
						</span>

						<?php
						break;
					case 'server':
						?>
						<h1>
							<?php
							echo gettext('SERVER array');
							?>
						</h1>
						<?php
						break;
					case 'session':
						?>
						<h1>
							<?php
							echo gettext('SESSION array');
							?>
						</h1>
						<?php
						break;
					case 'http':
						?>
						<h1>
							<?php
							echo ('Http Accept Languages:');
							?>
						</h1>
						<?php
						break;
					case 'locale':
						?>
						<h1>
							<?php
							echo gettext('Supported locales:');
							?>
						</h1>
						<?php
						break;
					case 'cookie':
						?>
						<h1>
							<?php
							echo gettext('Site browser cookies found.');
							?>
						</h1>
						<?php
						break;
				}
				?>
				<div class="tabbox">
					<?php
					$subtab = getCurrentTab();
					switch ($subtab) {
						case 'phpinfo':
							//	need to cleanup the phpinfo() output because it thinks it is a page unto itself
							ob_start();
							phpinfo();
							$phpinfo = ob_get_clean();
							@ob_end_flush();


							$i = strpos($phpinfo, '<div class="center">');
							$phpinfo = substr($phpinfo, $i);
							$phpinfo = str_replace('</body></html>', '', $phpinfo);

							file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/phpinfo.htm', $phpinfo);
							?>
							<style type="text/css">
								pre {margin: 0; font-family: monospace;}
								a:link {color: #009; text-decoration: none;}
								a:hover {text-decoration: underline;}
								table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
								.center {text-align: center;}
								.center table {margin: 1em auto; text-align: left;}
								.center th {text-align: center !important;}
								td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
								h1 {font-size: 150%;}
								h2 {font-size: 125%;}
								.p {text-align: left;}
								.e {background-color: #ccf; width: 300px; font-weight: bold;}
								.h {background-color: #99c; font-weight: bold;}
								.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
								.v i {color: #999;}
								img {float: right; border: 0;}
								hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
							</style>
							<?php
							echo $phpinfo;
							break;
						case 'env':
							if (empty($_ENV)) {
								echo gettext('There are no environmental variables passed.');
							} else {
								$env = preg_replace('/^Array\n/', '<pre>', print_r($_ENV, true)) . '</pre>';
								echo $env;
							}
							break;
						case 'server':
							$server = preg_replace('/^Array\n/', '<pre>', print_r($_SERVER, true)) . '</pre>';
							echo $server;
							break;
						case 'session':
							$session = preg_replace('/^Array\n/', '<pre>', print_r($_SESSION, true)) . '</pre>';
							echo $session;
							break;
						case 'http':
							$httpaccept = parseHttpAcceptLanguage();
							if (count($httpaccept) > 0) {
								$accept = $httpaccept;
								$accept = array_shift($accept);
								?>
								<table>
									<tr>
										<th width = 100 align="left">Key</th>
										<?php
										foreach ($accept as $key => $value) {
											?>
											<th width = 100 align="left"><?php echo html_encode($key); ?></th>
											<?php
										}
										?>
									</tr>
									<?php
									foreach ($httpaccept as $key => $accept) {
										?>
										<tr>
											<td width=100 align="left"><?php echo html_encode($key); ?></td>
											<?php
											foreach ($accept as $value) {
												?>
												<td width=100 align="left"><?php echo html_encode($value); ?></td>
												<?php
											}
											?>
										</tr>
										<?php
									}
									?>
								</table>

								<?php
							}
							break;
						case 'locale':
							if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
								// source of the list:
								// http://msdn.microsoft.com/en-us/library/39cwe7zf(v=vs.90).aspx
								$langs = array(
										// language, sublanguage, codes
										array('Chinese', 'Chinese', array('chinese')),
										array('Chinese', 'Chinese (simplified)', array('chinese-simplified', 'chs')),
										array('Chinese', 'Chinese (traditional)', array('chinese-traditional', 'cht')),
										array('Czech', 'Czech', array('csy', 'czech')),
										array('Danish', 'Danish', array('dan', 'danish')),
										array('Dutch', 'Dutch (default)', array('dutch', 'nld')),
										array('Dutch', 'Dutch (Belgium)', array('belgian', 'dutch-belgian', 'nlb')),
										array('English', 'English (default)', array('english')),
										array('English', 'English (Australia)', array('australian', 'ena', 'english-aus')),
										array('English', 'English (Canada)', array('canadian', 'enc', 'english-can')),
										array('English', 'English (New Zealand)', array('english-nz', 'enz')),
										array('English', 'English (United Kingdom)', array('eng', 'english-uk', 'uk')),
										array('English', 'English (United States)', array('american', 'american english', 'american-english', 'english-american', 'english-us', 'english-usa', 'enu', 'us', 'usa')),
										array('Finnish', 'Finnish', array('fin', 'finnish')),
										array('French', 'French (default)', array('fra', 'french')),
										array('French', 'French (Belgium)', array('frb', 'french-belgian')),
										array('French', 'French (Canada)', array('frc', 'french-canadian')),
										array('French', 'French (Switzerland)', array('french-swiss', 'frs')),
										array('German', 'German (default)', array('deu', 'german')),
										array('German', 'German (Austria)', array('dea', 'german-austrian')),
										array('German', 'German (Switzerland)', array('des', 'german-swiss', 'swiss')),
										array('Greek', 'Greek', array('ell', 'greek')),
										array('Hungarian', 'Hungarian', array('hun', 'hungarian')),
										array('Icelandic', 'Icelandic', array('icelandic', 'isl')),
										array('Italian', 'Italian (default)', array('ita', 'italian')),
										array('Italian', 'Italian (Switzerland)', array('italian-swiss', 'its')),
										array('Japanese', 'Japanese', array('japanese', 'jpn')),
										array('Korean', 'Korean', array('kor', 'korean')),
										array('Norwegian', 'Norwegian (default)', array('norwegian')),
										array('Norwegian', 'Norwegian (Bokmal)', array('nor', 'norwegian-bokmal')),
										array('Norwegian', 'Norwegian (Nynorsk)', array('non', 'norwegian-nynorsk')),
										array('Polish', 'Polish', array('plk', 'polish')),
										array('Portuguese', 'Portuguese (default)', array('portuguese', 'ptg')),
										array('Portuguese', 'Portuguese (Brazil)', array('portuguese-brazilian', 'ptb')),
										array('Russian', 'Russian (default)', array('rus', 'russian')),
										array('Slovak', 'Slovak', array('sky', 'slovak')),
										array('Spanish', 'Spanish (default)', array('esp', 'spanish')),
										array('Spanish', 'Spanish (Mexico)', array('esm', 'spanish-mexican')),
										array('Spanish', 'Spanish (Modern)', array('esn', 'spanish-modern')),
										array('Swedish', 'Swedish', array('sve', 'swedish')),
										array('Turkish', 'Turkish', array('trk', 'turkish'))
								);
								echo '<table class="bordered">' . "\n";
								echo '<tr>' . "\n";
								echo '  <th>' . gettext('Language') . '</th>' . "\n";
								echo '  <th>' . gettext('Sub-Language') . '</th>' . "\n";
								echo '  <th>' . gettext('Language String') . '</th>' . "\n";
								echo '</tr>' . "\n";
								foreach ($langs as $lang) {
									echo '<tr>' . "\n";
									echo '  <td>' . $lang[0] . '</td>' . "\n";
									echo '  <td>' . $lang[1] . '</td>' . "\n";
									$a = array();
									foreach ($lang[2] as $lang_code) {
										$loc = setlocale(LC_ALL, $lang_code);
										$a[] = $lang_code . ' ' . ( false === $loc ? '✖' : '✔ - ' . $_zp_UTF8->convert($loc, FILESYSTEM_CHARSET, LOCAL_CHARSET) );
									}
									echo '  <td>' . implode('<br />', $a) . '</td>' . "\n";
									echo '</tr>' . "\n";
								}
								echo '</table>' . "\n";
							} else {
								ob_start();
								system('locale -a');
								$locales = ob_get_contents();
								ob_end_clean();
								$list = explode("\n", $locales);
								$last = '';
								foreach ($list as $locale) {
									if ($last != substr($locale, 0, 3)) {
										echo "<br />";
										$last = substr($locale, 0, 3);
									}
									echo $locale . ' ';
								}
							}

							break;
						case 'cookie':
							?>

							<form name="cookie_form" class="dirtychyeck" method="post" action="?page=develpment&amp;tab=cookie">
								<table class="compact">
									<?php
									foreach ($_COOKIE as $cookie => $cookiev) {
										?>
										<tr>
											<td><input type="checkbox" name="delete_cookie[<?php echo html_encode(postIndexEncode($cookie)); ?>]" value="1"></td>
											<td><?php echo html_encode($cookie); ?> </td>
											<td><?php echo html_encode(zp_cookieEncode($cookiev)); ?></td>
										</tr>
										<?php
									}
									?>
								</table>
								<p class="buttons">
									<button type="submit">
										<?php echo WASTEBASKET; ?>
										<strong><?php echo gettext("Delete"); ?></strong>
									</button>
									<button type="reset">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Reset"); ?></strong>
									</button>
								</p>
							</form>
							<?php
							break;
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php printAdminFooter();
	?>

</body>
</html>
