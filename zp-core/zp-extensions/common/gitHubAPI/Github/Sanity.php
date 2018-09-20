<?php

namespace Milo\Github;


/**
 * Undefined member access check. Stolen from Nette\Object (http://nette.org).
 */
abstract class Sanity
{
	/**
	 * @throws LogicException
	 */
	public function & __get($name)
	{
		throw new LogicException('Cannot read an undeclared property ' . get_class($this) . "::\$$name.");
	}


	/**
	 * @throws LogicException
	 */
	public function __set($name, $value)
	{
		throw new LogicException('Cannot write to an undeclared property ' . get_class($this) . "::\$$name.");
	}

}
