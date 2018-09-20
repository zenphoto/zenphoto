<?php

namespace Milo\Github\OAuth;

use Milo\Github;


/**
 * OAuth token envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Token extends Github\Sanity
{
	/** @var string */
	private $value;

	/** @var string */
	private $type;

	/** @var string[] */
	private $scopes;


	/**
	 * @param  string
	 * @param  string
	 * @param  string[]
	 */
	public function __construct($value, $type = '', array $scopes = [])
	{
		$this->value = $value;
		$this->type = $type;
		$this->scopes = $scopes;
	}


	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @return string[]
	 */
	public function getScopes()
	{
		return $this->scopes;
	}


	/**
	 * @see https://developer.github.com/v3/oauth/#scopes
	 *
	 * @param  string
	 * @return bool
	 */
	public function hasScope($scope)
	{
		if (in_array($scope, $this->scopes, TRUE)) {
			return TRUE;
		}

		static $superiors = [
			'user:email' => 'user',
			'user:follow' => 'user',
			'notifications' => 'repo',
		];

		if (array_key_exists($scope, $superiors) && in_array($superiors[$scope], $this->scopes, TRUE)) {
			return TRUE;
		}

		return FALSE;
	}


	/** @internal */
	public function toArray()
	{
		return [
			'value' => $this->value,
			'type' => $this->type,
			'scopes' => $this->scopes,
		];
	}


	/** @internal */
	public static function createFromArray(array $data)
	{
		return new static($data['value'], $data['type'], $data['scopes']);
	}

}
