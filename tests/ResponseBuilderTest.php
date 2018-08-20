<?php

namespace Mentax\PJRPC;

use Mentax\PJRPC\Hydrator\ResponseBuilder;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\TestException;

class ResponseBuilderTest extends TestCase
{
	protected function getPropertyValue($object, $property)
	{
		$property = new \ReflectionProperty($object, $property);
		$property->setAccessible(true);

		return $property->getValue($object);
	}

	public function testHandleApiException()
	{
		// extends ApiRpcException
		$ex = new TestException('test message', 1234);

		$responseBuilder = new ResponseBuilder();
		$responseBuilder->withException($ex);

		$method = new \ReflectionMethod($responseBuilder, 'handleExceptions');
		$method->setAccessible(true);

		$method->invoke($responseBuilder);

		$exception = $this->getPropertyValue($responseBuilder, 'exception');

		$this->assertEquals($ex, $exception);
	}

	public function testHandleGeneralException()
	{
		$ex = new \Exception('msg', 312);

		$responseBuilder = new ResponseBuilder();
		$responseBuilder->withException($ex);

		$method = new \ReflectionMethod($responseBuilder, 'handleExceptions');
		$method->setAccessible(true);

		$method->invoke($responseBuilder);

		$this->assertEquals($ex->getMessage(), $this->getPropertyValue($responseBuilder, 'errorMessage'));
		$this->assertEquals($ex->getCode(), $this->getPropertyValue($responseBuilder, 'errorCode'));
	}
}
