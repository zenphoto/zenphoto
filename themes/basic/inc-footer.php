<div id="credit">
	<?php
	if(class_exists('ScriptlessSocialSharing')) {
		ScriptlessSocialSharing::printButtons();
	}
	if (function_exists('printFavoritesURL')) {
		printFavoritesURL(NULL, '', ' | ', '<br />');
	} 
	if (class_exists('RSS')) printRSSLink('Gallery', '', gettext('Gallery RSS'), ' | '); 
	printCustomPageURL(gettext("Archive View"), "archive"); ?> |
	<?php
	if (extensionEnabled('contact_form')) {
		printCustomPageURL(gettext('Contact us'), 'contact', '', '', ' | ');
	}
	if (!zp_loggedin() && function_exists('printRegisterURL')) {
		printRegisterURL(gettext('Register for this site'), '', ' | ');
	}
	@call_user_func('printUserLogin_out', '', ' | ');
	
	printPrivacyPageLink(' | ', ' | ');
	printZenphotoLink(); 
	printCopyrightNotice(' | ', '');
	?>
</div>
<?php 

@call_user_func('mobileTheme::controlLink'); 
@call_user_func('printLanguageSelector'); 
zp_apply_filter('theme_body_close'); 
?>
