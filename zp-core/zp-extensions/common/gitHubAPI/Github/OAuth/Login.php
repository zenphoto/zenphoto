<?php

namespace Milo\Github\OAuth;

use Milo\Github;
use Milo\Github\Storages;
use Milo\Github\Http;


/**
 * OAuth token obtaining process.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Login extends Github\Sanity
{
	/** @var string */
	private $authUrl = 'https://github.com/login/oauth/authorize';

	/** @var string */
	private $tokenUrl = 'https://github.com/login/oauth/access_token';

	/** @var Configuration */
	private $conf;

	/** @var Storages\ISessionStorage */
	private $storage;

	/** @var Http\IClient */
	private $client;


	public function __construct(Configuration $conf, Storages\ISessionStorage $storage = NULL, Http\IClient $client = NULL)
	{
		$this->conf = $conf;
		$this->storage = $storage ?: new Storages\SessionStorage;
		$this->client = $client ?: Github\Helpers::createDefaultClient();
	}


	/**
	 * @return Http\IClient
	 */
	public function getClient()
	{
		return $this->client;
	}


	/**
	 * @param  string  URL to redirect back from Github when user approves the permissions request
	 * @param  callable function($githubUrl)  makes HTTP redirect to Github
	 */
	public function askPermissions($backUrl, $redirectCb = NULL)
	{
		/** @todo Something more safe? */
		$state = sha1(uniqid(microtime(TRUE), TRUE));
		$params = [
			'client_id' => $this->conf->clientId,
			'redirect_uri' => $backUrl,
			'scope' => implode(',', $this->conf->scopes),
			'state' => $state,
		];

		$this->storage->set('auth.state', $state);

		$url = $this->authUrl . '?' . http_build_query($params);
		if ($redirectCb === NULL) {
			header("Location: $url");
			die();
		} else {
			call_user_func($redirectCb, $url);
		}
	}


	/**
	 * @param  string
	 * @param  string
	 * @return Token
	 *
	 * @throws LoginException
	 */
	public function obtainToken($code, $state)
	{
		if ($state !== $this->storage->get('auth.state')) {
			throw new LoginException('OAuth security state does not match.');
		}

		$params = [
			'client_id' => $this->conf->clientId,
			'client_secret' => $this->conf->clientSecret,
			'code' => $code,
		];

		$headers = [
			'Accept' => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded',
		];

		$request = new Http\Request(Http\Request::POST, $this->tokenUrl, $headers, http_build_query($params));
		try {
			$response = $this->client->request($request);
		} catch (Http\BadResponseException $e) {
			throw new LoginException('HTTP request failed.', 0, $e);
		}

		try {
			/** @var $json \stdClass */
			if ($response->isCode(Http\Response::S404_NOT_FOUND)) {
				$json = Github\Helpers::jsonDecode($response->getContent());
				throw new LoginException($json->error, $response->getCode());

			} elseif (!$response->isCode(Http\Response::S200_OK)) {
				throw new LoginException('Unexpected response.', $response->getCode());
			}

			$json = Github\Helpers::jsonDecode($response->getContent());

		} catch (Github\JsonException $e) {
			throw new LoginException('Bad JSON in response.', 0, $e);
		}

		$token = new Token($json->access_token, $json->token_type, strlen($json->scope) ? explode(',', $json->scope) : []);
		$this->storage->set('auth.token', $token->toArray());
		$this->storage->remove('auth.state');

		return $token;
	}


	/**
	 * @return bool
	 */
	public function hasToken()
	{
		return $this->storage->get('auth.token') !== NULL;
	}


	/**
	 * @return Token
	 *
	 * @throws Github\LogicException  when token has not been obtained yet
	 */
	public function getToken()
	{
		$token = $this->storage->get('auth.token');
		if ($token === NULL) {
			throw new Github\LogicException('Token has not been obtained yet.');

		} elseif ($token instanceof Token) {
			/** @deprecated */
			$token = $token->toArray();
			$this->storage->set('auth.token', $token);
		}

		return Token::createFromArray($token);
	}


	/**
	 * @return self
	 */
	public function dropToken()
	{
		$this->storage->remove('auth.token');
		return $this;
	}

}
