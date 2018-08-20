<?php

namespace Mentax\PJRPC\Hydrator;

use JsonRPC\Exception\ResponseException;
use JsonRPC\Exception\ServerErrorException;
use JsonRPC\HttpClient;
use Mentax\PJRPC\Exception\ServerError;
use Mentax\PJRPC\Hydrator\HttpClient as HydratorHttpClient;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HydratorClient extends \JsonRPC\Client implements LoggerAwareInterface
{
	const DEFAULT_REQUEST_TIMEOUT = 60;

	use LoggerAwareTrait;
	const LOG_CLIENT_SEND = '--> client send';
	const LOG_SERVER_RESPONSE_EXCEPTION = '!!! server exception';
	const LOG_SERVER_RESPONSE = '<-- server response';
	const LOG_SERVER_HYDRATOR_RESPONSE = '<-- hydrator server response';
	/** @var object */
	protected $api;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(string $url = '', bool $returnException = false, HttpClient $httpClient = null, $timeout = self::DEFAULT_REQUEST_TIMEOUT)
	{
		if (empty($httpClient)) {
			$httpClient = new HydratorHttpClient($url);
		}

		parent::__construct($url, $returnException, $httpClient);

		$this->withPositionalArguments();
		$this->adjustTimeout($timeout);
	}

	/**
	 * changes server read timeout.
	 *
	 * @param int $timeout
	 *
	 * @return $this
	 */
	public function adjustTimeout($timeout = self::DEFAULT_REQUEST_TIMEOUT)
	{
		$this->getHttpClient()->withTimeout($timeout);

		return $this;
	}

	/**
	 * set class containing desired API overrides.
	 *
	 * @param object $api
	 *
	 * @throws \Exception
	 */
	public function setApi($api)
	{
		if (!is_object($api)) {
			throw new \Exception('Provided API handle is not an object');
		}

		$this->api = $api;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws ServerError
	 * @throws \ReflectionException
	 */
	public function execute($procedure, array $params = [], array $reqattrs = [], $requestId = null, array $headers = [])
	{
		$requestId = mt_rand();
		$clientMethod = 'CLIENT::' . $procedure;

		$this->log($requestId, $clientMethod, self::LOG_CLIENT_SEND, $params);

		try {
			$result = parent::execute($procedure, $params, $reqattrs, $requestId);
		} catch (ResponseException  $exception) {
			$this->log($requestId, $clientMethod, self::LOG_SERVER_RESPONSE_EXCEPTION, $exception);
			throw new ServerError($exception->getMessage(), $exception);
		} catch (ServerErrorException $exception) {
			$this->log($requestId, $clientMethod, self::LOG_SERVER_RESPONSE_EXCEPTION, $exception);
			throw new ServerError($exception->getMessage(), $exception);
		}

		$this->log($requestId, $clientMethod, self::LOG_SERVER_RESPONSE, $result);

		$result = $this->hydrateClientResult($procedure, $result);
		$this->log($requestId, $clientMethod, self::LOG_SERVER_HYDRATOR_RESPONSE, $result);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param string $clientMethod
	 * @param string $event
	 * @param mixed $data
	 */
	public function log(int $requestId, string $clientMethod, $event, $data)
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
	 * @param string $method
	 * @param mixed $result
	 *
	 * @return object|\object[]
	 *
	 * @throws ServerError
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	protected function hydrateClientResult($method, $result)
	{
		if (empty($result) || isset($result->error)) {
			throw new ServerError($result);
		}

		$method = $this->getApiMethod($method);
		if ($method) {
			$reflector = new Reflector();
			$type = $reflector->getReturnType($method);

			$hydrator = new Hydrator();
			$result = $hydrator->processPayload($result, $type, $method);
		}

		return $result;
	}

	/**
	 * get reflected API method.
	 *
	 * @param string $method
	 *
	 * @return bool|\ReflectionMethod
	 *
	 * @throws \ReflectionException
	 */
	protected function getApiMethod($method)
	{
		if (!$this->api) {
			return false;
		}

		if (!method_exists($this->api, $method)) {
			return false;
		}

		return new \ReflectionMethod(get_class($this->api), $method);
	}
}
