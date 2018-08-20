<?php

namespace tests\Mentax\PJRPC\Mocks;

use Mentax\PJRPC\Hydrator\HydratorClient;
use tests\Mentax\PJRPC\Mocks\Struct\ExampleOtherStruct;
use tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct;

class Client implements ApiInterface
{
	/** @var HydratorClient */
	protected $client;

	public function __construct(HydratorClient $client)
	{
		$client->setApi($this);
		$this->client = $client;
	}

	/**
	 * @param $arg
	 *
	 * @return int
	 */
	public function action(ExampleOtherStruct $arg): int
	{
		return $this->client->action($arg);
	}

	/**
	 * @param $arg
	 * @param string[] $strings
	 *
	 * @return SubStruct[]
	 */
	public function objectAction(ExampleOtherStruct $arg, $strings = null)
	{
		return $this->client->objectAction($arg);
	}

	/**
	 * @param SubStruct[] $arg
	 *
	 * @return string
	 */
	public function addTask($arg): string
	{
		return $this->client->addTask($arg);
	}

	/**
	 * phpdoc unspecified intentionally.
	 *
	 * @throws TestException
	 */
	public function exceptionAction(string $param): string
	{
		return $this->client->exceptionAction($param);
	}

	/**
	 * @throws
	 */
	public function exceptionWithIncorrectTagAction()
	{
		return $this->client->exceptionWIthIncorrectTagAction();
	}
}
