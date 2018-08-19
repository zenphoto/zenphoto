<?php

namespace Milo\Github;


/**
 * Iterates through the Github API responses by Link: header.
 *
 * @see https://developer.github.com/guides/traversing-with-pagination/
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Paginator extends Sanity implements \Iterator
{
	/** @var Api */
	private $api;

	/** @var Http\Request */
	private $firstRequest;

	/** @var Http\Request|NULL */
	private $request;

	/** @var Http\Response|NULL */
	private $response;

	/** @var int */
	private $limit;

	/** @var int */
	private $counter = 0;


	public function __construct(Api $api, Http\Request $request)
	{
		$this->api = $api;
		$this->firstRequest = clone $request;
	}


	/**
	 * Limits maximum steps of iteration.
	 *
	 * @param  int|NULL
	 * @return self
	 */
	public function limit($limit)
	{
		$this->limit = $limit === NULL
			? NULL
			: (int) $limit;

		return $this;
	}


	/**
	 * @return void
	 */
	public function rewind()
	{
		$this->request = $this->firstRequest;
		$this->response = NULL;
		$this->counter = 0;
	}


	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this->request !== NULL && ($this->limit === NULL || $this->counter < $this->limit);
	}


	/**
	 * @return Http\Response
	 */
	public function current()
	{
		$this->load();
		return $this->response;
	}


	/**
	 * @return int
	 */
	public function key()
	{
		return static::parsePage($this->request->getUrl());
	}


	/**
	 * @return void
	 */
	public function next()
	{
		$this->load();

		if ($url = static::parseLink($this->response->getHeader('Link'), 'next')) {
			$this->request = new Http\Request(
				$this->request->getMethod(),
				$url,
				$this->request->getHeaders(),
				$this->request->getContent()
			);
		} else {
			$this->request = NULL;
		}

		$this->response = NULL;
		$this->counter++;
	}


	private function load()
	{
		if ($this->response === NULL) {
			$this->response = $this->api->request($this->request);
		}
	}


	/**
	 * @param  string
	 * @return int
	 */
	public static function parsePage($url)
	{
		list (, $parametersStr) = explode('?', $url, 2) + ['', ''];
		parse_str($parametersStr, $parameters);

		return isset($parameters['page'])
			? max(1, (int) $parameters['page'])
			: 1;
	}


	/**
	 * @see  https://developer.github.com/guides/traversing-with-pagination/#navigating-through-the-pages
	 *
	 * @param  string
	 * @param  string
	 * @return string|NULL
	 */
	public static function parseLink($link, $rel)
	{
		if (!preg_match('(<([^>]+)>;\s*rel="' . preg_quote($rel) . '")', $link, $match)) {
			return NULL;
		}

		return $match[1];
	}

}
