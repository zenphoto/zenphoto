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
	callUserFunction('printUserLogin_out', '', ' | ');
	
	printPrivacyPageLink(' | ', ' | ');
	printZenphotoLink(); 
	printCopyrightNotice(' | ', '');
	?>
</div>
<?php 

callUserFunction('mobileTheme::controlLink'); 
callUserFunction('printLanguageSelector'); 
zp_apply_filter('theme_body_close'); 
?>
