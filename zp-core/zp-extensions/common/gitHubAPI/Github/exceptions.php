<?php


/**
 * All Milo\Github exceptions at one place. Whole library does not throw anything else.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */

namespace Milo\Github {
	/**
	 * Marker interface.
	 */
	interface IException
	{}


	/**
	 * Wrong algorithm. API is used in wrong way. Application code should be changed.
	 */
	class LogicException extends \LogicException implements IException
	{}


	/**
	 * Substitution is used in URL path but corresponding parameter is missing.
	 */
	class MissingParameterException extends LogicException
	{}


	/**
	 * Unpredictable situation occurred.
	 */
	abstract class RuntimeException extends \RuntimeException implements IException
	{}


	/**
	 * Github API returned a non-success HTTP code or data are somehow wrong. See all following descendants.
	 *
	 * @see Api::decode()
	 * @see https://developer.github.com/v3/#client-errors
	 */
	abstract class ApiException extends RuntimeException
	{
		/** @var Http\Response|NULL */
		private $response;


		/**
		 * @param string
		 * @param int
		 */
		public function __construct($message = '', $code = 0, \Exception $previous = NULL, Http\Response $response = NULL)
		{
			parent::__construct($message, $code, $previous);
			$this->response = clone $response;
		}


		/**
		 * @return Http\Response|NULL
		 */
		final public function getResponse()
		{
			return $this->response;
		}

	}


	/**
	 * Invalid credentials (e.g. revoked token).
	 */
	class UnauthorizedException extends ApiException
	{}


	/**
	 * Invalid JSON sent to Github API.
	 */
	class BadRequestException extends ApiException
	{}


	/**
	 * Invalid structure sent to Github API (e.g. some required fields are missing).
	 */
	class UnprocessableEntityException extends ApiException
	{}


	/**
	 * Access denied.
	 * @see https://developer.github.com/v3/#authentication
	 */
	class ForbiddenException extends ApiException
	{}


	/**
	 * Rate limit exceed.
	 * @see https://developer.github.com/v3/#rate-limiting
	 */
	class RateLimitExceedException extends ForbiddenException
	{}


	/**
	 * Resource not found.
	 * @see https://developer.github.com/v3/#authentication
	 */
	class NotFoundException extends ApiException
	{}


	/**
	 * Response cannot be classified.
	 */
	class UnexpectedResponseException extends ApiException
	{}


	/**
	 * Response from Github is somehow wrong (e.g. invalid JSON decoding).
	 */
	class InvalidResponseException extends ApiException
	{}


	/**
	 * JSON cannot be decoded, or value cannot be encoded to JSON.
	 */
	class JsonException extends RuntimeException
	{
	}

}


namespace Milo\Github\Http {
	use Milo\Github;


	/**
	 * HTTP response is somehow wrong and cannot be processed.
	 */
	class BadResponseException extends Github\RuntimeException
	{}

}


namespace Milo\Github\OAuth {
	use Milo\Github;

	/**
	 * Something fails during the token obtaining.
	 */
	class LoginException extends Github\RuntimeException
	{}

}


namespace Milo\Github\Storages {
	use Milo\Github;

	/**
	 * Directory is missing and/or cannot be created.
	 */
	class MissingDirectoryException extends Github\RuntimeException
	{}

}
