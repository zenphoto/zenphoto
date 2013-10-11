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
 * @package functions
 * @author Ozh
 * @since 1.3
 */
// force UTF-8 Ø

global $_zp_filters;
$_zp_filters = array();
/* This global var will collect filters with the following structure:
 * $_zp_filter['hook']['array of priorities']['serialized function names']['array of ['array (functions, accepted_args)]']
 */

/**
 * Registers a filtering function
 * Filtering functions are used to post process zenphoto elements or to trigger functions when a filter occur
 *
 * Typical use:
 *
 * 		zp_register_filter('some_hook', 'function_handler_for_hook');
 *
 * global array $_zp_filters Storage for all of the filters
 * @param string $hook the name of the zenphoto element to be filtered
 * @param callback $function_name the name of the function that is to be called.
 * @param integer $priority optional. Used to specify the order in which the functions associated with a particular
 * 																		action are executed (default=5, higher=earlier execution, and functions with
 * 																		the same priority are executed in the order in which they were added to the filter)
 */
function zp_register_filter($hook, $function_name, $priority = NULL) {
	global $_zp_filters, $_EnabledPlugins;
	$bt = @debug_backtrace();
	if (is_array($bt)) {
		$b = array_shift($bt);
		$base = basename($b['file']);
		if (is_null($priority) && isset($_EnabledPlugins[stripSuffix($base)])) {
			$priority = $_EnabledPlugins[stripSuffix($base)]['priority'] & PLUGIN_PRIORITY;
		}
	} else {
		$base = 'unknown';
	}
	if (is_null($priority)) {
		$priority = 5;
	}

	// At this point, we cannot check if the function exists, as it may well be defined later (which is OK)

	$id = zp_filter_unique_id($hook, $function_name, $priority);

	$_zp_filters[$hook][$priority][$id] = array(
					'function' => $function_name,
					'script'	 => $base
	);
	if (DEBUG_FILTERS)
		debugLog($base . '=>' . $function_name . ' registered to ' . $hook . ' at priority ' . $priority);
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * Simply using a function name is not enough, as several functions can have the same name when they are enclosed in classes.
 *
 * global array $_zp_filters storage for all of the filters
 * @param string $hook hook to which the function is attached
 * @param string|array $function used for creating unique id
 * @param int|bool $priority used in counting how many hooks were applied.  If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
 * @param string $type filter or action
 * @return string unique ID for usage as array key
 */
function zp_filter_unique_id($hook, $function, $priority) {
	global $_zp_filters;

	// If function then just skip all of the tests and not overwrite the following.
	if (is_string($function))
		return $function;
	// Object Class Calling
	else if (is_object($function[0])) {
		$obj_idx = get_class($function[0]) . $function[1];
		if (!isset($function[0]->_zp_filters_id)) {
			if (false === $priority)
				return false;
			$count = isset($_zp_filters[$hook][$priority]) ? count((array) $_zp_filters[$hook][$priority]) : 0;
			$function[0]->_zp_filters_id = $count;
			$obj_idx .= $count;
			unset($count);
		}
		else
			$obj_idx .= $function[0]->_zp_filters_id;
		return $obj_idx;
	}
	// Static Calling
	else if (is_string($function[0]))
		return $function[0] . $function[1];
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
 * global array $_zp_filters storage for all of the filters
 * @param string $hook the name of the zenphoto element
 * @param mixed $value the value of the element before filtering
 * @return mixed
 */
function zp_apply_filter($hook, $value = '') {
	global $_zp_filters;
	if (!isset($_zp_filters[$hook])) {
		return $value;
	}
	$args = func_get_args();
	// Sort filters by priority
	krsort($_zp_filters[$hook]);
	// Loops through each filter
	reset($_zp_filters[$hook]);
	if (DEBUG_FILTERS)
		$debug = 'Apply filters for ' . $hook;
	do {
		foreach ((array) current($_zp_filters[$hook]) as $the_) {
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
	} while (next($_zp_filters[$hook]) !== false);
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
 * global array $_zp_filters storage for all of the filters
 * @param string $hook The filter hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int $priority optional. The priority of the function (default: 10).
 * @param int $accepted_args optional. The number of arguments the function accpets (default: 1).
 * @return boolean Whether the function was registered as a filter before it was removed.
 */
function zp_remove_filter($hook, $function_to_remove, $priority = 10, $accepted_args = 1) {
	global $_zp_filters;

	$function_to_remove = zp_filter_unique_id($hook, $function_to_remove, $priority);

	$remove = isset($_zp_filters[$hook][$priority][$function_to_remove]);
	if ($remove) {
		unset($_zp_filters[$hook][$priority][$function_to_remove]);
		if (empty($_zp_filters[$hook][$priority]))
			unset($_zp_filters[$hook][$priority]);
		if (empty($_zp_filters[$hook]))
			unset($_zp_filters[$hook]);
		if (DEBUG_FILTERS)
			debugLog($function_to_remove . ' removed from ' . $hook);
	}
	return $remove;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * global array $_zp_filters storage for all of the filters
 * @param string $hook The name of the filter hook.
 * @param callback $function_to_check optional.  If specified, return the priority of that function on this hook or false if not attached.
 * @return int|boolean Optionally returns the priority on that hook for the specified function.
 */
function zp_has_filter($hook, $function_to_check = false) {
	global $_zp_filters;
	$has = !empty($_zp_filters[$hook]);
	if (false === $function_to_check || false == $has) {
		return $has;
	}
	if (!$idx = zp_filter_unique_id($hook, $function_to_check, false)) {
		return false;
	}
	foreach ((array) array_keys($_zp_filters[$hook]) as $key => $priority) {
		if (isset($_zp_filters[$hook][$priority][$idx]))
			return $priority;
	}
	return false;
}

/**
 *
 * returns a list of scripts that have attached to the hook
 * @param string $hook
 * @return string
 */
function get_filterScript($hook) {
	global $_zp_filters;
	$scripts = array();
	foreach ($_zp_filters[$hook] as $priority) {
		foreach ($priority as $actor) {
			$scripts[] = $actor['script'];
		}
	}
	return implode(', ', $scripts);
}

/**
 *
 * Returns the position of the function in the hook queue
 * @param $hook
 * @param $function
 */
function zp_filter_slot($hook, $function) {
	global $_zp_filters;
	if (empty($_zp_filters[$hook])) {
		return false;
	}
	if (!$idx = zp_filter_unique_id($hook, $function, false)) {
		return false;
	}
	// Sort filters by priority
	$filters = $_zp_filters[$hook];
	krsort($filters);
	$c = 0;
	foreach ((array) array_keys($filters) as $priority) {
		foreach ($filters[$priority] as $filter => $data) {
			if ($filter == $idx) {
				return $c;
			}
			$c++;
		}
	}
	return false;
}

?>
