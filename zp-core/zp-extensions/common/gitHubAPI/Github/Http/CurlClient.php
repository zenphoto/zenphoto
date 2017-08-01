<?php

namespace Milo\Github\Http;

use Milo\Github;


/**
 * HTTP client which use the cURL extension functions.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class CurlClient extends AbstractClient
{
	/** @var array|NULL */
	private $options;

	/** @var resource */
	private $curl;


	/**
	 * @param  array  cURL options {@link http://php.net/manual/en/function.curl-setopt.php}
	 *
	 * @throws Github\LogicException
	 */
	public function __construct(array $options = NULL)
	{
		if (!extension_loaded('curl')) {
			throw new Github\LogicException('cURL extension is not loaded.');
		}

		$this->options = $options;
	}


	protected function setupRequest(Request $request)
	{
		parent::setupRequest($request);
		$request->addHeader('Connection', 'keep-alive');
	}


	/**
	 * @return Response
	 *
	 * @throws BadResponseException
	 */
	protected function process(Request $request)
	{
		$headers = [];
		foreach ($request->getHeaders() as $name => $value) {
			$headers[] = "$name: $value";
		}

		$responseHeaders = [];

		$softOptions = [
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_CAINFO => realpath(__DIR__ . '/../../ca-chain.crt'),
		];

		$hardOptions = [
			CURLOPT_FOLLOWLOCATION => FALSE, # Github sets the Location header for 201 code too and redirection is not required for us
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $request->getMethod(),
			CURLOPT_NOBODY => $request->isMethod(Request::HEAD),
			CURLOPT_URL => $request->getUrl(),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS => $request->getContent(),
			CURLOPT_HEADER => FALSE,
			CURLOPT_HEADERFUNCTION => function($curl, $line) use (& $responseHeaders, & $last) {
				if (strncasecmp($line, 'HTTP/', 5) === 0) {
					/** @todo Set proxy response as Response::setPrevious($proxyResponse)? */
					# The HTTP/x.y may occur multiple times with proxy (HTTP/1.1 200 Connection Established)
					$responseHeaders = [];

				} elseif (in_array(substr($line, 0, 1), [' ', "\t"], TRUE)) {
					$responseHeaders[$last] .= ' ' . trim($line);  # RFC2616, 2.2

				} elseif ($line !== "\r\n") {
					list($name, $value) = explode(':', $line, 2);
					$responseHeaders[$last = trim($name)] = trim($value);
				}

				return strlen($line);
			},
		];

		if (defined('CURLOPT_PROTOCOLS')) {  # HHVM issue. Even cURL v7.26.0, constants are missing.
			$hardOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
		}

		if (!$this->curl) {
			$this->curl = curl_init();
			if ($this->curl === FALSE) {
				throw new BadResponseException('Cannot init cURL handler.');
			}
		}

		$result = curl_setopt_array($this->curl, $hardOptions + ($this->options ?: []) + $softOptions);
		if ($result === FALSE) {
			throw new BadResponseException('Setting cURL options failed: ' . curl_error($this->curl), curl_errno($this->curl));
		}

		$content = curl_exec($this->curl);
		if ($content === FALSE) {
			throw new BadResponseException(curl_error($this->curl), curl_errno($this->curl));
		}

		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		if ($code === FALSE) {
			throw new BadResponseException('HTTP status code is missing:' . curl_error($this->curl), curl_errno($this->curl));
		}

		return new Response($code, $responseHeaders, $content);
	}

}
