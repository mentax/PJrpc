<?php

namespace Mentax\PJRPC\Hydrator;

use JsonRPC\MiddlewareHandler;
use JsonRPC\ProcedureHandler;
use JsonRPC\Request\BatchRequestParser;
use JsonRPC\Request\RequestParser;
use JsonRPC\Response\ResponseBuilder;
use JsonRPC\Server;

/**
 * Klasa dodaje tylko metody ulatwiajace zrobione sensownego logu.
 */
class HydratorServer extends Server
{
	public function __construct(string $request = '', array $server = [], ResponseBuilder $responseBuilder = null, RequestParser $requestParser = null, BatchRequestParser $batchRequestParser = null, ProcedureHandler $procedureHandler = null, MiddlewareHandler $middlewareHandler = null)
	{
		if (!$responseBuilder) {
			$responseBuilder = new \Mentax\PJRPC\Hydrator\ResponseBuilder();
		}

		if (!$requestParser) {
			$requestParser = new \Mentax\PJRPC\Hydrator\RequestParser();
		}

		if (!$procedureHandler) {
			$procedureHandler = new \Mentax\PJRPC\Hydrator\HydratorProcedureHandler();
		}

		parent::__construct($request, $server, $responseBuilder, $requestParser, $batchRequestParser, $procedureHandler, $middlewareHandler);
	}

	/**
	 * @return array
	 */
	public function getPayload(): array
	{
		if (empty($this->payload)) {
			$this->payload = [];
		}

		return $this->payload;
	}
}
