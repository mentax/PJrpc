<?php

namespace tests\Mentax\PJRPC\Mocks\Struct;

use tests\Mentax\PJRPC\Mocks\Struct\Sub\SubStruct;

class ExampleStruct
{
	/**
	 * @var int
	 */
	public $field1;
	/**
	 * @var string
	 */
	public $field2;
	/**
	 * @var ExampleOtherStruct
	 */
	public $field3;

	/**
	 * @var SubStruct
	 */
	public $field4;

	/**
	 * @var SubStruct[]
	 */
	public $field5;
}
