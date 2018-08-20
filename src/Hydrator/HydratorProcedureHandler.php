<?php

namespace Mentax\PJRPC\Hydrator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HydratorProcedureHandler extends \JsonRPC\ProcedureHandler implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	private $payload;

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function executeProcedure($procedure, array $params = [])
	{
		$requestId = $this->payload['id'];
		$serverMethod = 'SERVER::' . $procedure;

		$this->log($requestId, $serverMethod, '--> server input', $params);
		$reflector = new Reflector();
		$hydrator = new Hydrator();

		$method = $this->getMethod($procedure);
		$types = $reflector->getCallbackArgumentTypes($method);

		$mappedParams = [];
		foreach ($types as $key => $type) {
			if (isset($params[$key])) {
				$mappedParams[] = $hydrator->hydrate($params[$key], $type, $method);
			}
		}
		$this->log($requestId, $serverMethod, '--> server hydrated input', $mappedParams);

		$result = parent::executeProcedure($procedure, $mappedParams);

		$this->log($requestId, $serverMethod, '<-- server response', $result);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param string $clientMethod
	 * @param string $event
	 * @param mixed $data
	 */
	public function log($requestId, string $clientMethod, $event, $data)
	{
		if (empty($this->logger)) {
			return;
		}

		$this->logger->debug('request',
			[
				'clientMethod' => $clientMethod,
				'requestId' => $requestId,
				'event' => $event,
				'data' => $data,
			]
		);
	}

	/**
	 * get callback for desired procedure name.
	 *
	 * get callback for desired procedure name.
	 *
	 * @param string $procedure
	 *
	 * @return \ReflectionFunctionAbstract
	 *
	 * @throws \ReflectionException
	 */
	public function getMethod($procedure)
	{
		// examine 1->callback, 2->classes, 3->objects
		if (isset($this->callbacks[$procedure])) {
			return new \ReflectionFunction($this->callbacks[$procedure]);
		}

		foreach ($this->classes as $class => $callback) {
			if ($callback[1] == $procedure) {
				$method = new \ReflectionMethod($class, $callback[0]);
				break;
			}
		}

		if (empty($method)) {
			foreach ($this->instances as $i) {
				if (method_exists($i, $procedure)) {
					$method = new \ReflectionMethod($i, $procedure);
					break;
				}
			}
		}

		if (empty($method)) {
			throw new \RuntimeException('Callback does not exist');
		}

		return $method;
	}

	public function setPayload(array $payload)
	{
		$this->payload = $payload;
	}
}
