<?php

/**
 * @deprecated 2.0 Use the rewrite class instead
 *
 * @package core
 * @subpackage functions\functions-rewrite
 */

/**
 * Handles the rewriting
 * 
 * @deprecated 2.0 Use rewrite::rewriteHandler() instead
 * 
 * @global type $_zp_conf_vars
 * @global type $_zp_rewritten
 */
function rewriteHandler() {
	deprecationNotice(gettext('Use rewrite::rewriteHandler() instead'));
	rewrite::rewriteHandler();
}

/**
 * Gets the rewrite rules 
 * 
 * @deprecated 2.0 Use rewrite::getRules() instead
 * 
 * @global type $_zp_conf_vars
 * @return array
 */
function getRules() {
	deprecationNotice(gettext('Use rewrite::getRules() instead'));
	return rewrite::getRules();
}

/*
*
 * Use rewrite::setRewriteConstants() instead
 */
/*$_definitions = array();
foreach ($_zp_conf_vars['special_pages'] as $definition) {
	if (@$definition['define']) {
		define($definition['define'], strtr($definition['rewrite'], $_definitions));
		eval('$_definitions[$definition[\'define\']]=' . $definition['define'] . ';');
	}
}
unset($definition);
unset($_definitions); */