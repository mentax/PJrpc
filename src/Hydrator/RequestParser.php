<?php

namespace Mentax\PJRPC\Hydrator;

use Exception;
use JsonRPC\Exception\InvalidJsonRpcFormatException;

class RequestParser extends \JsonRPC\Request\RequestParser
{
	/**
	 * code exactly the same as covered but calls ResponseBuilder from a different namespace.
	 *
	 * @param Exception $e
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	protected function handleExceptions(Exception $e)
	{
		foreach ($this->localExceptions as $exception) {
			if ($e instanceof $exception) {
				throw $e;
			}
		}

		if ($e instanceof InvalidJsonRpcFormatException || !$this->isNotification()) {
			return ResponseBuilder::create()
				->withId(isset($this->payload['id']) ? $this->payload['id'] : null)
				->withException($e)
				->build();
		}

		return '';
	}
}
