<?php

namespace Milo\Github\Storages;


/**
 * Cross-request session storage.
 */
interface ISessionStorage
{
	/**
	 * @param  string
	 * @param  mixed
	 * @return self
	 */
	function set($name, $value);


	/**
	 * @param  string
	 * @return mixed
	 */
	function get($name);


	/**
	 * @param  string
	 * @return self
	 */
	function remove($name);

}
