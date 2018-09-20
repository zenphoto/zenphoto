<?php

namespace Milo\Github\Http;

use Milo\Github;


/**
 * HTTP response envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Response extends Message
{
	/** HTTP 1.1 code */
	const
		S200_OK = 200,
		S301_MOVED_PERMANENTLY = 301,
		S302_FOUND = 302,
		S304_NOT_MODIFIED = 304,
		S307_TEMPORARY_REDIRECT = 307,
		S400_BAD_REQUEST = 400,
		S401_UNAUTHORIZED = 401,
		S403_FORBIDDEN = 403,
		S404_NOT_FOUND = 404,
		S422_UNPROCESSABLE_ENTITY = 422;

	/** @var int */
	private $code;

	/** @var Response */
	private $previous;


	/**
	 * @param  int
	 * @param  array
	 * @param  string
	 */
	public function __construct($code, array $headers, $content)
	{
		$this->code = (int) $code;
		parent::__construct($headers, $content);
	}


	/**
	 * HTTP code.
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}


	/**
	 * @param  int
	 * @return bool
	 */
	public function isCode($code)
	{
		return $this->code === (int) $code;
	}


	/**
	 * @return Response|NULL
	 */
	public function getPrevious()
	{
		return $this->previous;
	}


	/**
	 * @return self
	 *
	 * @throws Github\LogicException
	 */
	public function setPrevious(Response $previous = NULL)
	{
		if ($this->previous) {
			throw new Github\LogicException('Previous response is already set.');
		}
		$this->previous = $previous;

		return $this;
	}

}
