<?php
/**
 * This is Zenphoto's unified comment handling facility
 *
 * Place a call on the function <var>printCommentForm()</var> in your script where you
 * wish the comment items to appear.
 *
 * The plugin uses <var>%ZENFOLDER%/%PLUGIN_FOLDER%/comment_form/comment_form.php</var>.
 * However, you may override this form by placing a script of the same name in a similar folder in your theme.
 * This will allow you to customize the appearance of the comments on your site.
 *
 * There are several options to tune what the plugin will do.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Provides a unified comment handling facility.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'comment_form';

require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/comment_form/functions.php');

if (OFFSET_PATH) {
	zp_register_filter('options_comments', 'comment_form_options');
	zp_register_filter('save_comment_custom_data', 'comment_form_save_comment');
	zp_register_filter('edit_comment_custom_data', 'comment_form_edit_comment');
	zp_register_filter('admin_overview', 'comment_form_print10Most');
	zp_register_filter('save_admin_custom_data', 'comment_form_save_admin');
	zp_register_filter('edit_admin_custom_data', 'comment_form_edit_admin');
} else {
	zp_register_filter('comment_post', 'comment_form_comment_post');
	zp_register_filter('handle_comment', 'comment_form_postcomment');
	zp_register_filter('object_addComment', 'comment_form_addCcomment');
	if(getOption('comment_form_pagination')) {
		zp_register_filter('theme_head','comment_form_PaginationJS');
	}
	if(getOption('tinymce_comments')) {
		require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/tiny_mce.php');
		zp_register_filter('theme_head','comment_form_visualEditor');
	}
}

class comment_form {

	/**
	 * class instantiation function
	 *
	 * @return admin_login
	 */
	function comment_form() {
		setOptionDefault('email_new_comments', 1);
		setOptionDefault('comment_name_required', 'required');
		setOptionDefault('comment_email_required', 'required');
		setOptionDefault('comment_web_required', 'show');
		setOptionDefault('Use_Captcha', false);
		setOptionDefault('comment_form_addresses', 0);
		setOptionDefault('comment_form_require_addresses', 0);
		setOptionDefault('comment_form_members_only', 0);
		setOptionDefault('comment_form_albums', 1);
		setOptionDefault('comment_form_images', 1);
		setOptionDefault('comment_form_articles', 1);
		setOptionDefault('comment_form_pages', 1);
		setOptionDefault('comment_form_rss', 1);
		setOptionDefault('comment_form_private', 1);
		setOptionDefault('comment_form_anon', 1);
		setOptionDefault('comment_form_showURL', 1);
		setOptionDefault('comment_form_comments_per_page', 10);
		setOptionDefault('comment_form_pagination', true);
		setOptionDefault('tinymce_comments', 'comment_form-default.js.php');
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_captcha;
		require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/tiny_mce.php');
		$checkboxes = array(gettext('Albums') => 'comment_form_albums', gettext('Images') => 'comment_form_images');
		if (getOption('zp_plugin_zenpage')) {
			$checkboxes = array_merge($checkboxes, array(gettext('Pages') => 'comment_form_pages', gettext('News') => 'comment_form_articles'));
		}
		$configarray = getTinyMCEConfigFiles();

		$options = array(
											gettext('Enable comment notification') => array('key' => 'email_new_comments', 'type' => OPTION_TYPE_CHECKBOX,
													'order' => 0,
													'desc' => gettext('Email the Admin when new comments are posted')),
											gettext('Name field') => array('key' => 'comment_name_required', 'type' => OPTION_TYPE_RADIO,
													'order' => 0.1,
													'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
													'desc' => gettext('If the <em>Name</em> field is required, the poster must provide a name.')),
											gettext('Email field') => array('key' => 'comment_email_required', 'type' => OPTION_TYPE_RADIO,
													'order' => 0.2,
													'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
													'desc' => gettext('If the <em>Email</em> field is required, the poster must provide an email address.')),
											gettext('Website field') => array('key' => 'comment_web_required', 'type' => OPTION_TYPE_RADIO,
													'order' => 0.3,
													'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
													'desc' => gettext('If the <em>Website</em> field is required, the poster must provide a website.')),
											gettext('Captcha field') => array('key' => 'Use_Captcha', 'type' => OPTION_TYPE_RADIO,
													'order' => 0.4,
													'disabled' => !$_zp_captcha->name,
													'buttons' => array(gettext('Omit')=>0, gettext('For guests')=>2, gettext('Require')=>1),
													'desc' => ($_zp_captcha->name)?gettext('If <em>Captcha</em> is required, the form will include a Captcha verification.'):'<span class="notebox">'.gettext('No captcha handler is enabled.').'</span>'),

											gettext('Address fields') => array('key' => 'comment_form_addresses', 'type' => OPTION_TYPE_RADIO,
												'order' => 7,
												'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
												'desc' => gettext('If <em>Address fields</em> are shown or required, the form will include positions for address information. If required, the poster must supply data in each address field.')),
											gettext('Allow comments on') => array('key' => 'comment_form_allowed', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
												'order' => 0.9,
												'checkboxes' => $checkboxes,
												'desc' => gettext('Comment forms will be presented on the checked pages.')),
											gettext('Toggled comment block') => array('key' => 'comment_form_toggle', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 2,
												'desc' => gettext('If checked, existing comments will be initially hidden. Clicking on the provided button will show them.')),
											gettext('Show author URL') => array('key' => 'comment_form_showURL', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 7,
												'desc' => gettext('To discourage SPAM, uncheck this box and the author URL will not be revealed.')),
											gettext('Only members can comment') => array('key' => 'comment_form_members_only', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 4,
												'desc' => gettext('If checked, only logged in users will be allowed to post comments.')),
											gettext('Allow private postings') => array('key' => 'comment_form_private', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 6,
												'desc' => gettext('If checked, posters may mark their comments as private (not for publishing).')),
											gettext('Allow anonymous posting') => array('key' => 'comment_form_anon', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 5,
												'desc' => gettext('If checked, posters may exclude their personal information from the published post.')),
											gettext('Include RSS link') => array('key' => 'comment_form_rss', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 8,
												'desc' => gettext('If checked, an RSS link will be included at the bottom of the comment section.')),
											gettext('Comments per page') => array('key' => 'comment_form_comments_per_page', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 9,
												'desc' => gettext('The comments that should show per page using the jQuery pagination')),
											gettext('Comment editor configuration') => array('key' => 'tinymce_comments', 'type' => OPTION_TYPE_SELECTOR,
												'order'=>1,
												'selections' => $configarray,
												'null_selection' => gettext('Disabled'),
												'desc' => gettext('Configuration file for TinyMCE when used for comments. Set to <code>Disabled</code> to disable visual editing.')),
											gettext('Pagination') => array('key' => 'comment_form_pagination', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 3,
												'desc' => gettext('Uncheck to disable the jQuery pagination of comments. Enabled by default.')),
											);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

}
?>