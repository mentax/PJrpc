<?php

namespace tests\Mentax\PJRPC;

use Mentax\PJRPC\Hydrator\Reflector;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\Client;

class ReflectorTest extends TestCase
{
	public function testReflectorArgumentsWithoutPhpDoc()
	{
		$reflection = new \ReflectionMethod(Client::class, 'exceptionAction');

		$reflector = new Reflector();

		$result = $reflector->getCallbackArgumentTypes($reflection);

		$this->assertCount(1, $result);
		$this->assertEquals('string', $result[0]);
	}

	public function testReflectorArgumentsWithCollection()
	{
		$reflection = new \ReflectionMethod(Client::class, 'addTask');

		$reflector = new Reflector();

		$result = $reflector->getCallbackArgumentTypes($reflection);

		$this->assertCount(1, $result);
		$this->assertEquals('\tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct', $result[0][0]);
	}

	public function testReflectorArgumentsWithoutCollection()
	{
		$reflection = new \ReflectionMethod(Client::class, 'exceptionAction');

		$reflector = new Reflector();

		$result = $reflector->getCallbackArgumentTypes($reflection);

		$this->assertCount(1, $result);
		$this->assertEquals('string', $result[0]);
	}

	public function testReflectorResultCollection()
	{
		$reflection = new \ReflectionMethod(Client::class, 'objectAction');

		$reflector = new Reflector();

		$result = $reflector->getReturnType($reflection);

		$this->assertCount(1, $result);
		$this->assertEquals('\tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct', $result[0]);
	}

	public function testReflectorResultWithoutCollection()
	{
		$reflection = new \ReflectionMethod(Client::class, 'addTask');

		$reflector = new Reflector();

		$result = $reflector->getReturnType($reflection);

		$this->assertEquals('string', $result);
	}
}
