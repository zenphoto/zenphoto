<?php

namespace Milo\Github\Http;

use Milo\Github;


/**
 * Ancestor for HTTP clients. Cares about redirecting and debug events.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
abstract class AbstractClient extends Github\Sanity implements IClient
{
	/** @var int[]  will follow Location header on these response codes */
	public $redirectCodes = [
		Response::S301_MOVED_PERMANENTLY,
		Response::S302_FOUND,
		Response::S307_TEMPORARY_REDIRECT,
	];

	/** @var int  maximum redirects per request*/
	public $maxRedirects = 5;

	/** @var callable|NULL */
	private $onRequest;

	/** @var callable|NULL */
	private $onResponse;


	/**
	 * @see https://developer.github.com/v3/#http-redirects
	 *
	 * @return Response
	 *
	 * @throws BadResponseException
	 */
	public function request(Request $request)
	{
		$request = clone $request;

		$counter = $this->maxRedirects;
		$previous = NULL;
		do {
			$this->setupRequest($request);

			$this->onRequest && call_user_func($this->onRequest, $request);
			$response = $this->process($request);
			$this->onResponse && call_user_func($this->onResponse, $response);

			$previous = $response->setPrevious($previous);

			if ($counter > 0 && in_array($response->getCode(), $this->redirectCodes) && $response->hasHeader('Location')) {
				/** @todo Use the same HTTP $method for redirection? Set $content to NULL? */
				$request = new Request(
					$request->getMethod(),
					$response->getHeader('Location'),
					$request->getHeaders(),
					$request->getContent()
				);

				$counter--;
				continue;
			}
			break;

		} while (TRUE);

		return $response;
	}


	/**
	 * @param  callable|NULL function(Request $request)
	 * @return self
	 */
	public function onRequest($callback)
	{
		$this->onRequest = $callback;
		return $this;
	}


	/**
	 * @param  callable|NULL function(Response $response)
	 * @return self
	 */
	public function onResponse($callback)
	{
		$this->onResponse = $callback;
		return $this;
	}


	protected function setupRequest(Request $request)
	{
		$request->addHeader('Expect', '');
	}


	/**
	 * @return Response
	 *
	 * @throws BadResponseException
	 */
	abstract protected function process(Request $request);

}
