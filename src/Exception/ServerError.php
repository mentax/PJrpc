<?php

namespace Mentax\PJRPC\Exception;

class ServerError extends \Exception implements \JsonSerializable
{
	/**
	 * @var string
	 */
	protected $payload;

	public function __construct($payload = null, \Exception $previous = null)
	{
		parent::__construct('', 0, $previous);
		$this->payload = $payload;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * Specify data which should be serialized to JSON.
	 *
	 * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
	 *
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource
	 *
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->getPrevious();
	}
}
