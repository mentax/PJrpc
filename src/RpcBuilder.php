<?php

namespace Mentax\PJRPC;

use Mentax\PJRPC\Hydrator\HydratorClient;
use Mentax\PJRPC\Hydrator\HydratorProcedureHandler;
use Mentax\PJRPC\Hydrator\HydratorServer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class RpcBuilder implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger = null)
	{
		$this->setLogger($logger);
	}

	/**
	 * @param AbstractClientRpc $mentaxClientRPC
	 * @param string $serverURL
	 * @param bool $runXDebugServerSite
	 * @param int $timeout in sek
	 *
	 * @throws \Exception
	 */
	public function prepareClient(AbstractClientRpc $mentaxClientRPC, string $serverURL, bool $runXDebugServerSite = false, $timeout = 60): void
	{
		$JRPCClient = $this->prepareJsonRPCClient($serverURL, $runXDebugServerSite, $timeout);

		$mentaxClientRPC->setClient($JRPCClient); // zeby klient potrafil odpytac serwer
		$JRPCClient->setApi($mentaxClientRPC); // zeby dzialal hydrator na danych zwracanych z serwera
	}

	/**
	 * @param object $serverObject
	 *
	 * @return string
	 */
	public function runServer($serverObject): string
	{
		$procedureHandler = new HydratorProcedureHandler();

		if (!empty($this->logger)) {
			$procedureHandler->setLogger($this->logger);
		}

		$server = new HydratorServer('', [], null, null, null, $procedureHandler);
		$procedureHandler->setPayload($server->getPayload());
		$procedureHandler->withObject($serverObject);

		return $server->execute();
	}

	/**
	 * @param string $serverURL
	 * @param bool $runXDebugServerSite
	 * @param int $timeout in sek
	 *
	 * @return HydratorClient
	 */
	private function prepareJsonRPCClient(string $serverURL, bool $runXDebugServerSite = false, $timeout = 60): HydratorClient
	{
		$JRPCClient = new HydratorClient($serverURL);
		$JRPCClient->adjustTimeout($timeout);

		if (!empty($this->logger)) {
			$JRPCClient->setLogger($this->logger);
		}

		if ($runXDebugServerSite) {
			$this->configureXDebugRequest($JRPCClient);
		}

		return $JRPCClient;
	}

	private function configureXDebugRequest(HydratorClient $JRPCClient)
	{
		// $JRPCClient->getHttpClient()->withDebug(); niby loguje ale do error_logs
		$JRPCClient->getHttpClient()->withBeforeRequestCallback(function (\JsonRPC\HttpClient $client) {
			// Dodajemy debugowanie po stronie serwera, gdy uruchomione jest po stronie klienta
			if (!empty($_COOKIE['XDEBUG_SESSION'])) {
				$client->withCookies(['XDEBUG_SESSION' => $_COOKIE['XDEBUG_SESSION']]);
			}
		});
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
}
