<?php

namespace Milo\Github\Http;

use Milo\Github;


/**
 * HTTP request envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Request extends Message
{
	/** HTTP request method */
	const
		DELETE = 'DELETE',
		GET = 'GET',
		HEAD = 'HEAD',
		PATCH = 'PATCH',
		POST = 'POST',
		PUT = 'PUT';


	/** @var string */
	private $method;

	/** @var string */
	private $url;


	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @param  string|NULL
	 */
	public function __construct($method, $url, array $headers = [], $content = NULL)
	{
		$this->method = $method;
		$this->url = $url;
		parent::__construct($headers, $content);
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strcasecmp($this->method, $method) === 0;
	}


	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/**
	 * @param  string
	 * @param  string
	 * @return self
	 */
	public function addHeader($name, $value)
	{
		return parent::addHeader($name, $value);
	}


	/**
	 * @param  string
	 * @param  string|NULL
	 * @return self
	 */
	public function setHeader($name, $value)
	{
		return parent::setHeader($name, $value);
	}

}
