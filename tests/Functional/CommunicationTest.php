<?php

namespace Mentax\PJRPC\Functional;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mentax\PJRPC\Hydrator\HttpClient;
use Mentax\PJRPC\Hydrator\HydratorClient;
use Mentax\PJRPC\Hydrator\HydratorServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use tests\Mentax\PJRPC\Mocks\Client;
use tests\Mentax\PJRPC\Mocks\Server;
use tests\Mentax\PJRPC\Mocks\Struct\ExampleOtherStruct;
use tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct;
use tests\Mentax\PJRPC\Mocks\TestException;

class CommunicationTest extends TestCase
{
	/**
	 * @return HttpClient
	 */
	protected function createHttpClient()
	{
		$handler = new MockHandler([function (RequestInterface $req) {
			$server = new HydratorServer($req->getBody()->getContents());

			$procedureHandler = $server->getProcedureHandler();
			$api = new Server();
			$procedureHandler->withObject($api);

			try {
				$resultBlob = $server->execute();
			} catch (\Exception $ex) {
				return new Response(500, [], $ex->getMessage());
			}

			return new Response(200, [], $resultBlob);
		}]);

		$guzzleClient = new GuzzleClient(['handler' => $handler]);

		$result = new HttpClient('');
		$result->setClient($guzzleClient);

		return $result;
	}

	/**
	 * @return Client
	 */
	protected function getApi()
	{
		$client = new HydratorClient('', false, $this->createHttpClient());

		$api = new Client($client);

		return $api;
	}

	public function testAction()
	{
		$api = $this->getApi();

		$arg = new ExampleOtherStruct();
		$arg->id = 123;
		$arg->return = [
			3, 2, 1,
		];

		$result = $api->action($arg);
		$this->assertEquals($arg->id, $result);
	}

	public function testObjectAction()
	{
		$api = $this->getApi();

		$arg = new ExampleOtherStruct();
		$arg->id = 123;
		$arg->return = [
			3, 2, 1,
		];

		$result = $api->objectAction($arg);

		$this->assertCount(3, $result);
		$this->assertTrue($result[0]->float > $arg->id);
		$this->assertTrue($result[1]->float > $arg->id);
		$this->assertTrue($result[2]->float > $arg->id);
	}

	public function testAddTask()
	{
		$api = $this->getApi();

		$a1 = new SubStruct();
		$a1->float = 1.1;

		$a2 = new SubStruct();
		$a2->float = 2.2;

		$result = $api->addTask([
			$a1, $a2,
		]);

		$this->assertEquals('1.12.2', $result);
	}

	public function testExceptionAction()
	{
		$api = $this->getApi();
		$this->expectException(TestException::class);

		$api->exceptionAction('asd');
	}
}
