<?php

namespace tests\Mentax\PJRPC;

use Mentax\PJRPC\Hydrator\Hydrator;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\Client;
use tests\Mentax\PJRPC\Mocks\Struct\DateTimeStruct;
use tests\Mentax\PJRPC\Mocks\Struct\ExampleOtherStruct;

class HydratorTest extends TestCase
{
	public function testProcessPayloadWithCollection()
	{
		$payload = [
			[
				'id' => 1,
				'return' => [
					1, 2, 3,
				],
			],
			[
				'id' => 1,
				'return' => [1],
			],
		];

		$hydrator = new Hydrator();
		$result = $hydrator->hydrate($payload, [ExampleOtherStruct::class], new \ReflectionMethod(Client::class, 'objectAction'));

		$this->assertCount(2, $result);
		$this->assertInstanceOf(ExampleOtherStruct::class, $result[0]);
		$this->assertInstanceOf(ExampleOtherStruct::class, $result[1]);

		$this->assertEquals([1, 2, 3], $result[0]->return);
		$this->assertEquals([1], $result[1]->return);
	}

	public function testHydrate()
	{
		$payload = [
			'id' => 1,
			'return' => [
				1, 2, 3,
			],
		];

		$hydrator = new Hydrator();
		$result = $hydrator->hydrate($payload, ExampleOtherStruct::class, new \ReflectionMethod(Client::class, 'objectAction'));

		$this->assertInstanceOf(ExampleOtherStruct::class, $result);
		$this->assertEquals($payload['return'], $result->return);
	}

	public function testProcessPayload()
	{
		$payload = [
			'id' => 1,
			'return' => [
				1, 2, 3,
			],
		];

		$payloadCollection = [
			[
				'id' => 1,
				'return' => [
					1, 2, 3,
				],
			],
			[
				'id' => 1,
				'return' => [
					1, 2, 3,
				],
			],
		];

		$hydrator = new Hydrator();

		$singleResult = $hydrator->processPayload($payload, ExampleOtherStruct::class, new \ReflectionMethod(Client::class, 'objectAction'));
		$this->assertInstanceOf(ExampleOtherStruct::class, $singleResult);

		$result = $hydrator->processPayload($payloadCollection, ExampleOtherStruct::class, new \ReflectionMethod(Client::class, 'objectAction'));
		$this->assertCount(2, $result);
	}

	public function testCreateContext()
	{
		$hydrator = new Hydrator();
		$method = new \ReflectionMethod(Client::class, 'objectAction');
		$ctx = $hydrator->createContext($method);

		$this->assertCount(1, $ctx);
		$this->assertInstanceOf(Context::class, $ctx[0]);
		$this->assertEquals('tests\Mentax\PJRPC\Mocks', $ctx[0]->getNamespace());
	}

    public function testDateTime()
    {

        $payload = [
            'createdAt'=>'2011-03-01T12:54:58+01:00'
        ];

        $hydrator = new Hydrator();
        $result = $hydrator->processPayload($payload, DateTimeStruct::class, new \ReflectionMethod(Client::class, 'dateTime'));

        $this->assertInstanceOf(DateTimeStruct::class, $result);
    }
}
