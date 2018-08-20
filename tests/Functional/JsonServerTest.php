<?php

namespace Mentax\PJRPC\Functional;

use Mentax\PJRPC\Hydrator\HydratorServer;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\Server;

class JsonServerTest extends TestCase
{
	public function testRawServerResponse()
	{
		$payload = [
			'jsonrpc' => '2.0',
			'method' => 'action',
			'id' => mt_rand(10000000, 99999999),
			'params' => [
				[
					'id' => 123,
					'return' => [],
				],
			],
		];

		$blob = json_encode($payload);

		$server = new HydratorServer($blob);

		$procedureHandler = $server->getProcedureHandler();
		$api = new Server();
		$procedureHandler->withObject($api);

		$resultBlob = $server->execute();

		$this->assertJson($resultBlob);
		$result = json_decode($resultBlob);

		$this->assertEquals(123, $result->result);
	}

	public function testRawServerException()
	{
		$payload = [
			'jsonrpc' => '2.0',
			'method' => 'exceptionAction',
			'id' => mt_rand(10000000, 99999999),
			'params' => [
				'test',
			],
		];

		$blob = json_encode($payload);

		$server = new HydratorServer($blob);

		$procedureHandler = $server->getProcedureHandler();
		$api = new Server();
		$procedureHandler->withObject($api);

		$resultBlob = $server->execute();

		$this->assertJson($resultBlob);
		$result = json_decode($resultBlob);

		$this->assertObjectHasAttribute('error', $result);
		$this->assertObjectHasAttribute('class', $result->error);
		$this->assertObjectHasAttribute('data', $result->error);
		$this->assertTrue(
			class_exists($result->error->class)
		);
	}

	public function testRawServerExceptionBadlyTagged()
	{
		$payload = [
			'jsonrpc' => '2.0',
			'method' => 'exceptionWithIncorrectTagAction',
			'id' => mt_rand(10000000, 99999999),
			'params' => [],
		];

		$blob = json_encode($payload);

		$server = new HydratorServer($blob);

		$procedureHandler = $server->getProcedureHandler();
		$api = new Server();
		$procedureHandler->withObject($api);

		$resultBlob = $server->execute();

		$this->assertJson($resultBlob);
		$result = json_decode($resultBlob);

		$this->assertObjectHasAttribute('error', $result);
		$this->assertObjectHasAttribute('class', $result->error);
		$this->assertEquals('InvalidArgumentException', $result->error->class);
	}
}
