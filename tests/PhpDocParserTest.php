<?php

namespace tests\Mentax\PJRPC;

use Mentax\PJRPC\Hydrator\PhpDocParser;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Types\Array_;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\Client;

class PhpDocParserTest extends TestCase
{
	public function testGetDocblockForFunction()
	{
		$reflection = new \ReflectionMethod(Client::class, 'objectAction');
		$parser = new PhpDocParser();

		$result = $parser->getForFunction($reflection);

		$params = $result->getTagsByName('param');

		$this->assertCount(2, $params);
		$this->assertInstanceOf(Param::class, $params[0]);
		$this->assertNull($params[0]->getType());
		$this->assertEquals('arg', $params[0]->getVariableName());

		$return = $result->getTagsByName('return');
		$this->assertInstanceOf(Return_::class, $return[0]);
		$this->assertInstanceOf(Array_::class, $return[0]->getType());
	}

	public function testGetArgumentsForMethod()
	{
		$reflection = new \ReflectionMethod(Client::class, 'objectAction');
		$parser = new PhpDocParser();

		$result = $parser->getArgumentsForMethod($reflection);

		$this->assertCount(2, $result);
		$this->assertCount(1, $result['strings']);
	}

	public function testGetResultTypeCollection()
	{
		$reflection = new \ReflectionMethod(Client::class, 'objectAction');
		$parser = new PhpDocParser();

		$result = $parser->getReturnType($reflection);

		$this->assertCount(1, $result);
		$this->assertEquals('\tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct', (string)$result[0]);
	}

	public function testGetResultTypeScalar()
	{
		$reflection = new \ReflectionMethod(Client::class, 'addTask');
		$parser = new PhpDocParser();

		$result = $parser->getReturnType($reflection);

		$this->assertEquals('string', (string)$result);
	}
}
