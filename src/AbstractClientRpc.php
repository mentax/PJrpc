<?php

namespace Mentax\PJRPC;

class AbstractClientRpc
{
	/** @var \JsonRPC\Client */
	protected $clientRpc;

	/**
	 * @param \JsonRPC\Client $clientRpc
	 */
	public function setClient(\JsonRPC\Client $clientRpc)
	{
		$this->clientRpc = $clientRpc;
	}

	/**
	 * @return \JsonRPC\Client
	 */
	public function getClient()
	{
		return $this->clientRpc;
	}
}
