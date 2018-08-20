<?php

namespace tests\Mentax\PJRPC\Mocks;

use tests\Mentax\PJRPC\Mocks\Struct\ExampleOtherStruct;
use tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct;

interface ApiInterface
{
	/**
	 * @param $arg
	 *
	 * @return int
	 */
	public function action(ExampleOtherStruct $arg): int;

	/**
	 * @param $arg
	 *
	 * @return SubStruct[]
	 */
	public function objectAction(ExampleOtherStruct $arg);

	/**
	 * @param SubStruct[] $arg
	 *
	 * @return string
	 */
	public function addTask($arg): string;

	/**
	 * @throws TestException
	 */
	public function exceptionAction(string $param): string;

	/**
	 * @throws
	 */
	public function exceptionWithIncorrectTagAction();
}
