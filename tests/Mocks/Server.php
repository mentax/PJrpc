<?php

namespace tests\Mentax\PJRPC\Mocks;

use tests\Mentax\PJRPC\Mocks\Struct\ExampleOtherStruct;
use tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct;

class Server implements ApiInterface
{
	/**
	 * @param $arg
	 *
	 * @return int
	 */
	public function action(ExampleOtherStruct $arg): int
	{
		return $arg->id;
	}

	/**
	 * @param $arg
	 *
	 * @return SubStruct[]
	 */
	public function objectAction(ExampleOtherStruct $arg)
	{
		$result = [];

		for ($a = 0; $a < 3; ++$a) {
			$obj = new SubStruct();
			$obj->float = (float)$arg->id + floatval('0.' . ($a + 1));
			$result[] = $obj;
		}

		return $result;
	}

	/**
	 * @param SubStruct[] $arg
	 *
	 * @return string
	 */
	public function addTask($arg): string
	{
		$result = '';

		foreach ($arg as $a) {
			$result .= $a->float;
		}

		return $result;
	}

	/**
	 * @throws TestException
	 */
	public function exceptionAction(string $param): string
	{
		throw new TestException('msg', 123);
	}

	/**
	 * @throws
	 */
	public function exceptionWithIncorrectTagAction()
	{
		throw new TestException();
	}
}
