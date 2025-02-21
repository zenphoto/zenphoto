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
	callUserFunction('registerUser::printLink', gettext('Register for this site'), '', ' | ');
	callUserFunction('printUserLogin_out', '', ' | ');
	
	printPrivacyPageLink(' | ', ' | ');
	printZenphotoLink(); 
	printCopyrightNotice(' | ', '');
	callUserFunction('scriptlessSocialsharing::printProfileButtons', gettext('<h3>Follow us</h3>'));
	?>
</div>
<?php 

callUserFunction('mobileTheme::controlLink'); 
callUserFunction('printLanguageSelector'); 

zp_apply_filter('theme_body_close'); 
?>
