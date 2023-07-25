<?php
/**
 * GDPR data export tool
 *
 * @author Malte Müller (acrylian)
 * @package zpcore\admin\utilities
 */
define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$buttonlist[] = $mybutton = array(
		'category' => gettext('Info'),
		'enable' => true,
		'button_text' => gettext('User data export'),
		'formname' => 'user-data-export.php',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/user-data-export.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
		'title' => gettext('Lists and exports data stored about a specific user and email address.'),
		'alt' => '',
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

admin_securityChecks(NULL, currentRelativeURL());

require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-userdataexport.php';
$username = '';
$usermail = '';
$error = '';
if (isset($_REQUEST['userdata-username'])) {
	$username = sanitize($_REQUEST['userdata-username']);
	$usermail = sanitize($_REQUEST['userdata-usermail']);
	if (empty($username) && empty($usermail)) {
		$error = '<p class="errorbox fade-message">' . gettext('You must supply a user name and/or mail address.') . '</p>';
	} else {
		$dataformat = sanitize($_REQUEST['userdata-format']);
		$dataexport = new userDataExport($username, $usermail, $_zp_gallery, $_zp_authority);
		if ($dataexport->getAllData()) {
			$dataexport->processFileDownload($dataformat);
		} else {
			if (empty($usermail)) {
				$note = sprintf(gettext('No data available for %1$s'), $username);
			} else {
				$note = sprintf(gettext('No data available for %1$s and %2$s'), $username, $usermail);
			}
			$error = '<p class="notebox fade-message">' . $note . '</p>';
		}
	}
}
$webpath = WEBPATH . '/' . ZENFOLDER . '/';
printAdminHeader('overview', 'User data export');
?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-statistics.css" type="text/css" media="screen" />
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs() ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note', 'database', ''); ?>
				<h1><span id="top"><?php echo $mybutton['button_text']; ?></span></h1>
				<p><?php echo gettext('This tool helps to export possible personal user data if requested. It does not delete any data. This covers the following data:'); ?></p>
				<ul>
					<li><?php echo gettext('<strong>User account</strong> (<em>user name</em> and optionally <em>email address</em>). This exports the full data of an account as stored in the database if a match exists.'); ?></li>
					<li><?php echo gettext('<strong>Securitylog</strong> (<em>user name</em>).'); ?></li>
					<li><?php echo gettext('<strong>Comments</strong> (<em>user name</em> and <em>email address</em>). Note that if you did not include an email addresss this info may not be reliable as comments are possible without user account and may use the same name accidentally. Site owners should check this before handing over data especially if these were non public comments.'); ?></li>
					<li><?php echo gettext('<strong>Albums and images owner</strong> (<em>user name</em>) (excluding images of dynamic albums). The owner may be the user who uploaded the image or created the album but technically any user can be assigned as owner later on. There is no history stored about that.'); ?></li>
					<li><?php echo gettext('<strong>All items guest user</strong> (<em>user name</em>)'); ?></li>
					<li><?php echo gettext('<strong>Zenpage pages and news articles author and last change author</strong> (<em>user name</em>). Here the same applies as to owners above. The author may have created the entry but may have changed afterwards. The last change author is only that. There is no history stored about that.'); ?></li>
				</ul>
				<p class="notebox"><?php echo gettext('<strong>Note:</strong> This covers only what Zenphoto core and plugins store and not what other third party tools may do or your server. <strong>This tool is provided without any any legal warranties regarding requirements of the e.g. GDPR</strong>. Contact your lawyer to find out if this is sufficient for your site.'); ?></p>
				<?php echo $error; ?>
				<form id="userdata-export-form" name="userdata-export-form" method="post">
					<?php XSRFToken('userdata-export'); ?>
					<p>
						<label><input type="text" id="userdata-username" name="userdata-username" value="<?php echo html_encode($username); ?>"> <?php echo gettext('User name'); ?></label>
						<label><input type="email" id="userdata-usermail" name="userdata-usermail" value=""> <?php echo gettext('User email address'); ?></label>
					</p>
					<p><?php echo gettext('You can supply either user name or user email address or both. For more reliable results on users registered to your site both are recommended. For just getting commenters there might be no user name or no mail address provided depending on your comment_form plugin settings.'); ?></p>
					<p>
						<label><input type="radio" id="userdata-format" name="userdata-format" value="html" checked="checked"> HTML</label>
						<label><input type="radio" id="userdata-format" name="userdata-format" value="json"> JSON</label>
					</p>
					<p class="buttons"><button type="submit"> <?php echo gettext('Download'); ?></button></p>
					<br class="clearall">
				</form>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>


