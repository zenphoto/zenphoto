<?php

/**
 * Handles reconfiguration when the install signature has changed
 * 
 * @deprecated 2.0 Use class-reconfigure.php instead
 * 
 * @package core
 * @subpackage functions\functions-reconfigure
 */

/**
 * Executes the configuration change code
 * 
 * @deprecated 2.0 Use reconfigure::reconfigureAction() instead
 * 
 * @param int $mandatory 0-3 0 means signature change where 1 means setup must autorun assuming a fresh install
 */
function reconfigureAction($mandatory) {
	deprecationNotice(gettext('Use reconfigure::reconfigureAction() instead'));
	reconfigure::reconfigureAction($mandatory);
}

/**
 * Checks details of configuration change
 * 
 * @deprecated 2.0 Use reconfigure::checkSignature() instead
 * 
 * @global type $_zp_mutex
 * @global type $_zp_db
 * @param bool $auto
 * @return type
 */
function checkSignature($auto) {
	deprecationNotice(gettext('Use reconfigure::checkSignature() instead'));
	return reconfigure::checkSignature($auto);
}

/**
 * Notification handler for configuration change
 * 
 * @deprecated 2.0 Use reconfigure::signatureChange() instead
 * 
 * @param string $tab
 * @param string $subtab
 * @return string
 */
function signatureChange($tab = NULL, $subtab = NULL) {
	deprecationNotice(gettext('Use reconfigure::signatureChange() instead'));
	return reconfigure::signatureChange($tab, $subtab);
}

/**
 * Adds the reconfigure notification via filters 
 * 
 * @deprecated 2.0 Use reconfigure::addReconfigureNote() instead
 * 
 * @since 1.5.8 - renamed from reconfigureNote()
 */
function addReconfigureNote() {
	deprecationNotice(gettext('Use reconfigure::addReconfigureNote() instead'));
	reconfigure::addReconfigureNote();
}

/**
 * prints HTML for the configuration change notification
 * 
 * @deprecated 2.0 Use reconfigure::printReconfigureNote() instead
 * 
 * @since 1.5.8 - renamed from reconfigureNote()
 * @param array $diff
 * @param type $needs
 * @param type $mandatory
 */
function printReconfigureNote($diff, $needs, $mandatory) {
	deprecationNotice(gettext('Use reconfigure::printReconfigureNote() instead'));
	reconfigure::printReconfigureNote($diff, $needs, $mandatory);
}

/**
 * Gets data for the configuration change notification
 * 
 * Also adds entries to the debuglog.
 * 
 * @deprecated 2.0 Use reconfigure::getReconfigureNote() instead
 * 
 * @since 1.5.8
 * 
 * @param array $diff
 * @param type $needs
 * @param type $mandatory
 * @return array
 */
function getReconfigureNote($diff, $needs, $mandatory) {
	deprecationNotice(gettext('Use reconfigure::getReconfigureNote() instead'));
	return reconfigure::getReconfigureNote($diff, $needs, $mandatory);
}

/**
 * Prints an error page on the frontend if a mandatory reconfigure issue occurred but the visitor is not loggedin 
 * with appropiate rights.
 * 
 * @deprecated 2.0 Use reconfigure::printReconfigureError() instead
 */
function printReconfigureError($mandatory) {
	deprecationNotice(gettext('Use reconfigure::printReconfigureError() instead'));
	reconfigure::printReconfigureError($mandatory);
}

/**
 * Adds debuglog entries about the reconfigure note
 * 
 * @deprecated 2.0 Use reconfigure::debuglogReconfigureNote() instead
 * 
 * @param array $notice reconfigure notice array as returned by getReconfigureNote()
 */
function debuglogReconfigureNote($notice) {
	deprecationNotice(gettext('Use reconfigure::debuglogReconfigureNote() instead'));
	reconfigure::debuglogReconfigureNote($notice);
}

/**
 * If setup request a run because of a signature change this refreshes the signature 
 * on full admin user request so it is ignored until the next signature change.
 * 
 * @deprecated 2.0 Use reconfigure::ignoreSetupRunRequest() instead
 * 
 * @since 1.5.8
 */
function ignoreSetupRunRequest() {
	deprecationNotice(gettext('Use reconfigure::ignoreSetupRunRequest() instead'));
	reconfigure::ignoreSetupRunRequest();
}

/**
 * Checks if setup files are protected. Returns array of the protected files or empty array
 * 
 * @deprecated 2.0 Use reconfigure::isSetupProtected() instead
 * 
 * @return array
 */
function isSetupProtected() {
	deprecationNotice(gettext('Use reconfigure::isSetupProtected() instead'));
	return reconfigure::isSetupProtected();
}

/**
 * Unprotectes setup files
 * 
 * @deprecated 2.0 Use reconfigure::unprotectSetupFiles() instead
 */
function unprotectSetupFiles() {
	deprecationNotice(gettext('Use reconfigure::unprotectSetupFiles() instead'));
	reconfigure::unprotectSetupFiles();
}

/**
 * Protects setup files
 * 
 * @deprecated 2.0 Use reconfigure::protectSetupFiles()
 */
function protectSetupFiles() {
	deprecationNotice(gettext('Use reconfigure::protectSetupFiles() instead'));
	reconfigure::protectSetupFiles();
}

/**
 *
 * CSS for the configuration change notification
 * 
 * @deprecated 2.0 Use reconfigure::printCSS(
 */
function reconfigureCSS() {
	deprecationNotice(gettext('Use reconfigure::printCSS() instead'));
	reconfigure::printReconfigureCSS();
}
