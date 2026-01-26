<?php

/**
 * Filter functions used by zenphoto
 *
 * The filter/plugin API is located in this file, which allows for creating filters
 * and hooking functions, and methods. The functions or methods will be run when
 * the filter is called.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link http://us2.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * This API is heavily inspired by the plugin API used in WordPress.
 * 
 * @deprecated 2.0 Use the class filter instead
 * 
 * @author Ozh
 * @since 1.3
 * 
 * @package core
 * @subpackage functions\functions-filter
 * 
 */
// force UTF-8 Ø



/**
 * Registers a filtering function
 * Filtering functions are used to post process zenphoto elements or to trigger functions when a filter occur
 *
 * Typical use:
 *
 * 		zp_register_filter('some_hook', 'function_handler_for_hook');
 *
 * @deprecated 2.0 Use fitler::registerFilter() instead
 * 
 * @param string $hook the name of the zenphoto element to be filtered
 * @param callback $function_name the name of the function that is to be called.
 * @param integer $priority optional. Used to specify the order in which the functions associated with a particular
 * 																		action are executed (default=5, higher=earlier execution, and functions with
 * 																		the same priority are executed in the order in which they were added to the filter)
 */
function zp_register_filter($hook, $function_name, $priority = NULL) {
	deprecationNotice(gettext('Use fitler::registerFilter() instead'));
	filter::registerFilter($hook, $function_name, $priority);
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * Simply using a function name is not enough, as several functions can have the same name when they are enclosed in classes.
 * 
 * @deprecated 2.0 Use filter::filterUniqueID() instead
 * 
 * @param string $hook hook to which the function is attached
 * @param string|array $function used for creating unique id
 * @param int|bool $priority used in counting how many hooks were applied.  If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
 * @param string $type filter or action
 * @return string unique ID for usage as array key
 */
function zp_filter_unique_id($hook, $function, $priority) {
	deprecationNotice(gettext('Use filter::filterUniqueID() instead'));
	return filter::filterUniqueID($hook, $function, $priority);
}

/**
 * Performs a filtering operation on a zenphoto element or event.
 * This function is called for each zenphoto element which supports
 * plugin filtering. It is called after any zenphoto specific actions are
 * completed and before the element is used.
 *
 * Typical use:
 *
 * 		1) Modify a variable if a function is attached to hook 'zp_hook'
 * 		$zp_var = "default value";
 * 		$zp_var = zp_apply_filter( 'zp_hook', $zp_var );
 *
 * 		2) Trigger functions is attached to event 'zp_event'
 * 		zp_apply_filter( 'zp_event' );
 *
 * Returns an element which may have been filtered by a filter.
 *
 * @deprecated 2.0 Use filter::applyFilter() instead
 * 
 * @param string $hook the name of the zenphoto element
 * @param mixed $value the value of the element before filtering
 * @return mixed
 */
function zp_apply_filter($hook, $value = '') {
	deprecationNotice(gettext('Use filter::applyFilter() instead'));
	// deprecated code is kept as getting function args otherwise fails
	if (!isset(filter::$filters[$hook])) {
		return $value;
	}
	$args = func_get_args();
	// Sort filters by priority
	krsort(filter::$filters[$hook]);
	// Loops through each filter
	reset(filter::$filters[$hook]);
	if (DEBUG_FILTERS)
		$debug = 'Apply filters for ' . $hook;
	do {
		foreach ((array) current(filter::$filters[$hook]) as $the_) {
			if (!is_null($the_['function'])) {
				if (DEBUG_FILTERS)
					$debug .= "\n    " . $the_['function'];
				$args[1] = $value;
				$new_value = call_user_func_array($the_['function'], array_slice($args, 1));
				if (!is_null($new_value)) {
					$value = $new_value;
				}
			}
		}
	} while (next(filter::$filters[$hook]) !== false);
	if (DEBUG_FILTERS)
		debugLog($debug);

	return $value;
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To be removed the $function_to_remove and $priority arguments must match
 * when the hook was added.
 *  
 * @deprecated 2.0 Use filter::removeFilter() instead
 * 
 * @param string $hook The filter hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int $priority optional. The priority of the function. If not supplied we will get it from zp_has_filter
 * @param int $accepted_args optional. The number of arguments the function accpets (default: 1).
 * @return boolean Whether the function was registered as a filter before it was removed.
 */
function zp_remove_filter($hook, $function_to_remove, $priority = NULL, $accepted_args = 1) {
	deprecationNotice(gettext('Use filter::removeFilter() instead'));
	return filter::removeFilter($hook, $function_to_remove, $priority, $accepted_args);
}

/**
 * Check if any filter has been registered for a hook.
 * 
 * @deprecated 2.0 Use filter::hasFilter() instead
 * 
 * @param string $hook The name of the filter hook.
 * @param callback $function_to_check optional.  If specified, return the priority of that function on this hook or false if not attached.
 * @return int|boolean Optionally returns the priority on that hook for the specified function.
 */
function zp_has_filter($hook, $function_to_check = false) {
	deprecationNotice(gettext('Use filter::hasFilter() instead'));
	return filter::hasFilter($hook, $function_to_check);
}

/**
 *
 * returns a list of scripts that have attached to the hook
 * 
 * @deprecated 2.0 Use filter::getFilterScript() instead
 * 
 * @param string $hook
 * @return string
 */
function get_filterScript($hook) {
	deprecationNotice(gettext('Use filter::getFilterScript() instead'));
	return filter::getFilterScript($hook);
}

/**
 *
 * Returns the position of the function in the hook queue
 * 
 * @deprecated 2.0 Use filter::filterSlot() instead
 * 
 * @param $hook
 * @param $function
 */
function zp_filter_slot($hook, $function) {
	deprecationNotice(gettext('Use filter::filterSlot() instead'));
	return filter::filterSlot($hook, $function);
}
