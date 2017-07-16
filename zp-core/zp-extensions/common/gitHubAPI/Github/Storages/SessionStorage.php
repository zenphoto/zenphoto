<?php

namespace Milo\Github\Storages;

use Milo\Github;


/**
 * Session storage which uses $_SESSION directly. Session must be started already before use.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SessionStorage extends Github\Sanity implements ISessionStorage
{
	const SESSION_KEY = 'milo.github-api';

	/** @var string */
	private $sessionKey;


	/**
	 * @param  string
	 */
	public function __construct($sessionKey = self::SESSION_KEY)
	{
		$this->sessionKey = $sessionKey;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return self
	 */
	public function set($name, $value)
	{
		if ($value === NULL) {
			return $this->remove($name);
		}

		$this->check(__METHOD__);
		$_SESSION[$this->sessionKey][$name] = $value;

		return $this;
	}


	/**
	 * @param  string
	 * @return mixed
	 */
	public function get($name)
	{
		$this->check(__METHOD__);

		return isset($_SESSION[$this->sessionKey][$name])
			? $_SESSION[$this->sessionKey][$name]
			: NULL;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function remove($name)
	{
		$this->check(__METHOD__);

		unset($_SESSION[$this->sessionKey][$name]);

		return $this;
	}


	/**
	 * @param  string
	 */
	private function check($method)
	{
		if (!isset($_SESSION)) {
			trigger_error("Start session before using $method().", E_USER_WARNING);
		}
	}

}
