<?php

namespace Milo\Github;

/**
 * Github API client library. Read readme.md in repository {@link http://github.com/milo/github-api}
 *
 * @see https://developer.github.com/v3/
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Api extends Sanity {

	/** @var string */
	private $url = 'https://api.github.com';

	/** @var string */
	private $defaultAccept = 'application/vnd.github.v3+json';

	/** @var array|NULL */
	private $defaultParameters = [];

	/** @var Http\IClient */
	private $client;

	/** @var OAuth\Token|NULL */
	private $token;

	public function __construct(Http\IClient $client = NULL) {
		$this->client = $client ? : Helpers::createDefaultClient();
	}

	/**
	 * @return self
	 */
	public function setToken(OAuth\Token $token = NULL) {
		$this->token = $token;
		return $this;
	}

	/**
	 * @return OAuth\Token|NULL
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param  array
	 * @return self
	 */
	public function setDefaultParameters(array $defaults = NULL) {
		$this->defaultParameters = $defaults ? : [];
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDefaultParameters() {
		return $this->defaultParameters;
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 */
	public function delete($urlPath, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::DELETE, $urlPath, $parameters, $headers)
		);
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 */
	public function get($urlPath, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::GET, $urlPath, $parameters, $headers)
		);
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 */
	public function head($urlPath, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::HEAD, $urlPath, $parameters, $headers)
		);
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @param  mixed
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 * @throws JsonException
	 */
	public function patch($urlPath, $content, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::PATCH, $urlPath, $parameters, $headers, $content)
		);
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @param  mixed
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 * @throws JsonException
	 */
	public function post($urlPath, $content, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::POST, $urlPath, $parameters, $headers, $content)
		);
	}

	/**
	 * @see createRequest()
	 * @see request()
	 *
	 * @param  string
	 * @param  mixed
	 * @return Http\Response
	 *
	 * @throws MissingParameterException
	 * @throws JsonException
	 */
	public function put($urlPath, $content = NULL, array $parameters = [], array $headers = []) {
		return $this->request(
										$this->createRequest(Http\Request::PUT, $urlPath, $parameters, $headers, $content)
		);
	}

	/**
	 * @return Http\Response
	 *
	 * @throws Http\BadResponseException
	 */
	public function request(Http\Request $request) {
		$request = clone $request;

		$request->addHeader('Accept', $this->defaultAccept);
		$request->addHeader('Time-Zone', date_default_timezone_get());
		$request->addHeader('User-Agent', 'milo/github-api');

		if ($this->token) {
			/** @todo Distinguish token type? */
			$request->addHeader('Authorization', "token {$this->token->getValue()}");
		}

		return $this->client->request($request);
	}

	/**
	 * @param  string  Http\Request::GET|POST|...
	 * @param  string  path like '/users/:user/repos' where ':user' is substitution
	 * @param  array[name => value]  replaces substitutions in $urlPath, the rest is appended as query string to URL
	 * @param  array[name => value]  name is case-insensitive
	 * @param  mixed|NULL  arrays and objects are encoded to JSON and Content-Type is set
	 * @return Http\Request
	 *
	 * @throws MissingParameterException  when substitution is used in URL but parameter is missing
	 * @throws JsonException  when encoding to JSON fails
	 */
	public function createRequest($method, $urlPath, array $parameters = [], array $headers = [], $content = NULL) {
		if (stripos($urlPath, $this->url) === 0) {	# Allows non-HTTPS URLs
			$baseUrl = $this->url;
			$urlPath = substr($urlPath, strlen($this->url));
		} elseif (preg_match('#^(https://[^/]+)(/.*)?$#', $urlPath, $m)) {
			$baseUrl = $m[1];
			$urlPath = isset($m[2]) ? $m[2] : '';
		} else {
			$baseUrl = $this->url;
		}

		if (strpos($urlPath, '{') === FALSE) {
			$urlPath = $this->expandColonParameters($urlPath, $parameters, $this->defaultParameters);
		} else {
			$urlPath = $this->expandUriTemplate($urlPath, $parameters, $this->defaultParameters);
		}

		$url = rtrim($baseUrl, '/') . '/' . ltrim($urlPath, '/');

		if ($content !== NULL && (is_array($content) || is_object($content))) {
			$headers['Content-Type'] = 'application/json; charset=utf-8';
			$content = Helpers::jsonEncode($content);
		}

		return new Http\Request($method, $url, $headers, $content);
	}

	/**
	 * @param  Http\Response
	 * @param  array|NULL  these codes are treated as success; code < 300 if NULL
	 * @return mixed
	 *
	 * @throws ApiException
	 */
	public function decode(Http\Response $response, array $okCodes = NULL) {
		$content = $response->getContent();
		if (preg_match('~application/json~i', $response->getHeader('Content-Type', ''))) {
			try {
				$content = Helpers::jsonDecode($response->getContent());
			} catch (JsonException $e) {
				throw new InvalidResponseException('JSON decoding failed.', 0, $e, $response);
			}

			if (!is_array($content) && !is_object($content)) {
				throw new InvalidResponseException('Decoded JSON is not an array or object.', 0, NULL, $response);
			}
		}

		$code = $response->getCode();
		if (($okCodes === NULL && $code >= 300) || (is_array($okCodes) && !in_array($code, $okCodes))) {
			/** @var $content \stdClass */
			switch ($code) {
				case Http\Response::S400_BAD_REQUEST:
					throw new BadRequestException(self::errorMessage($content), $code, NULL, $response);

				case Http\Response::S401_UNAUTHORIZED:
					throw new UnauthorizedException(self::errorMessage($content), $code, NULL, $response);

				case Http\Response::S403_FORBIDDEN:
					if ($response->getHeader('X-RateLimit-Remaining') === '0') {
						throw new RateLimitExceedException(self::errorMessage($content), $code, NULL, $response);
					}
					throw new ForbiddenException(self::errorMessage($content), $code, NULL, $response);

				case Http\Response::S404_NOT_FOUND:
					throw new NotFoundException('Resource not found or not authorized to access.', $code, NULL, $response);

				case Http\Response::S422_UNPROCESSABLE_ENTITY:
					throw new UnprocessableEntityException(self::errorMessage($content), $code, NULL, $response);
			}

			$message = $okCodes === NULL ? '< 300' : implode(' or ', $okCodes);
			throw new UnexpectedResponseException("Expected response with code $message.", $code, NULL, $response);
		}

		return $content;
	}

	/**
	 * Creates paginator for HTTP GET requests.
	 *
	 * @see get()
	 *
	 * @param  string
	 * @return Paginator
	 *
	 * @throws MissingParameterException
	 */
	public function paginator($urlPath, array $parameters = [], array $headers = []) {
		return new Paginator(
						$this, $this->createRequest(Http\Request::GET, $urlPath, $parameters, $headers)
		);
	}

	/**
	 * @return Http\IClient
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @param  string
	 * @return Api
	 */
	public function withUrl($url) {
		$api = clone $this;
		$api->setUrl($url);
		return $api;
	}

	/**
	 * @param  string
	 * @return self
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param  string
	 * @return string
	 *
	 * @throws MissingParameterException
	 */
	protected function expandColonParameters($url, array $parameters, array $defaultParameters) {
		$parameters += $defaultParameters;

		$url = preg_replace_callback('#(^|/|\.):([^/.]+)#', function($m) use ($url, & $parameters) {
			if (!isset($parameters[$m[2]])) {
				throw new MissingParameterException("Missing parameter '$m[2]' for URL path '$url'.");
			}
			$parameter = $parameters[$m[2]];
			unset($parameters[$m[2]]);
			return $m[1] . rawurlencode($parameter);
		}, $url);

		$url = rtrim($url, '/');

		if (count($parameters)) {
			$url .= '?' . http_build_query($parameters);
		}

		return $url;
	}

	/**
	 * Expands URI template (RFC 6570).
	 *
	 * @see http://tools.ietf.org/html/rfc6570
	 * @todo Inject remaining default parameters into query string?
	 *
	 * @param  string
	 * @return string
	 */
	protected function expandUriTemplate($url, array $parameters, array $defaultParameters) {
		$parameters += $defaultParameters;

		static $operatorFlags = [
				'' => ['prefix' => '', 'separator' => ',', 'named' => FALSE, 'ifEmpty' => '', 'reserved' => FALSE],
				'+' => ['prefix' => '', 'separator' => ',', 'named' => FALSE, 'ifEmpty' => '', 'reserved' => TRUE],
				'#' => ['prefix' => '#', 'separator' => ',', 'named' => FALSE, 'ifEmpty' => '', 'reserved' => TRUE],
				'.' => ['prefix' => '.', 'separator' => '.', 'named' => FALSE, 'ifEmpty' => '', 'reserved' => FALSE],
				'/' => ['prefix' => '/', 'separator' => '/', 'named' => FALSE, 'ifEmpty' => '', 'reserved' => FALSE],
				';' => ['prefix' => ';', 'separator' => ';', 'named' => TRUE, 'ifEmpty' => '', 'reserved' => FALSE],
				'?' => ['prefix' => '?', 'separator' => '&', 'named' => TRUE, 'ifEmpty' => '=', 'reserved' => FALSE],
				'&' => ['prefix' => '&', 'separator' => '&', 'named' => TRUE, 'ifEmpty' => '=', 'reserved' => FALSE],
		];

		return preg_replace_callback('~{([+#./;?&])?([^}]+?)}~', function($m) use ($url, & $parameters, $operatorFlags) {
			$flags = $operatorFlags[$m[1]];

			$translated = [];
			foreach (explode(',', $m[2]) as $name) {
				$explode = FALSE;
				$maxLength = NULL;
				if (preg_match('~^(.+)(?:(\*)|:(\d+))$~', $name, $tmp)) { // TODO: Speed up?
					$name = $tmp[1];
					if (isset($tmp[3])) {
						$maxLength = (int) $tmp[3];
					} else {
						$explode = TRUE;
					}
				}

				if (!isset($parameters[$name])) {	// TODO: Throw exception?
					continue;
				}

				$value = $parameters[$name];
				if (is_scalar($value)) {
					$translated[] = $this->prefix($flags, $name, $this->escape($flags, $value, $maxLength));
				} else {
					$value = (array) $value;
					$isAssoc = key($value) !== 0;

					// The '*' (explode) modifier
					if ($explode) {
						$parts = [];
						if ($isAssoc) {
							$this->walk($value, function ($v, $k) use (& $parts, $flags, $maxLength) {
								$parts[] = $this->prefix(['named' => TRUE] + $flags, $k, $this->escape($flags, $v, $maxLength));
							});
						} elseif ($flags['named']) {
							$this->walk($value, function ($v) use (& $parts, $flags, $name, $maxLength) {
								$parts[] = $this->prefix($flags, $name, $this->escape($flags, $v, $maxLength));
							});
						} else {
							$this->walk($value, function ($v) use (& $parts, $flags, $maxLength) {
								$parts[] = $this->escape($flags, $v, $maxLength);
							});
						}

						if (isset($parts[0])) {
							if ($flags['named']) {
								$translated[] = implode($flags['separator'], $parts);
							} else {
								$translated[] = $this->prefix($flags, $name, implode($flags['separator'], $parts));
							}
						}
					} else {
						$parts = [];
						$this->walk($value, function($v, $k) use (& $parts, $isAssoc, $flags, $maxLength) {
							if ($isAssoc) {
								$parts[] = $this->escape($flags, $k);
							}

							$parts[] = $this->escape($flags, $v, $maxLength);
						});

						if (isset($parts[0])) {
							$translated[] = $this->prefix($flags, $name, implode(',', $parts));
						}
					}
				}
			}

			if (isset($translated[0])) {
				return $flags['prefix'] . implode($flags['separator'], $translated);
			}

			return '';
		}, $url);
	}

	/**
	 * @param  array
	 * @param  string
	 * @param  string  already escaped
	 * @return string
	 */
	private function prefix(array $flags, $name, $value) {
		$prefix = '';
		if ($flags['named']) {
			$prefix .= $this->escape($flags, $name);
			if (isset($value[0])) {
				$prefix .= '=';
			} else {
				$prefix .= $flags['ifEmpty'];
			}
		}

		return $prefix . $value;
	}

	/**
	 * @param  array
	 * @param  mixed
	 * @param  int|NULL
	 * @return string
	 */
	private function escape(array $flags, $value, $maxLength = NULL) {
		$value = (string) $value;

		if ($maxLength !== NULL) {
			if (preg_match('~^(.{' . $maxLength . '}).~u', $value, $m)) {
				$value = $m[1];
			} elseif (strlen($value) > $maxLength) {	# when malformed UTF-8
				$value = substr($value, 0, $maxLength);
			}
		}

		if ($flags['reserved']) {
			$parts = preg_split('~(%[0-9a-fA-F]{2}|[:/?#[\]@!$&\'()*+,;=])~', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
			$parts[] = '';

			$escaped = '';
			for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
				$escaped .= rawurlencode($parts[$i]) . $parts[$i + 1];
			}

			return $escaped;
		}

		return rawurlencode($value);
	}

	/**
	 * @param  array
	 * @param  callable
	 */
	private function walk(array $array, $cb) {
		foreach ($array as $k => $v) {
			if ($v === NULL) {
				continue;
			}

			$cb($v, $k);
		}
	}

	/**
	 * @param  \stdClass
	 * @return string
	 */
	private static function errorMessage($content) {
		$message = isset($content->message) ? $content->message : 'Unknown error';

		if (isset($content->errors)) {
			$message .= implode(', ', array_map(function($error) {
								return '[' . implode(':', (array) $error) . ']';
							}, $content->errors));
		}

		return $message;
	}

}
