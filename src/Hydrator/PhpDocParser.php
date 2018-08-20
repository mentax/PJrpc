<?php
/**
 * Created by PhpStorm.
 * User: eriz
 * Date: 07.08.18
 * Time: 14:34.
 */

namespace Mentax\PJRPC\Hydrator;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ContextFactory;

class PhpDocParser
{
	public function __construct()
	{
	}

	public function getForFunction(\ReflectionFunctionAbstract $method)
	{
		$comment = $method->getDocComment();

		if (!$comment) {
			return false;
		}

		$contextFactory = new ContextFactory();
		$context = $contextFactory->createFromReflector($method);

		$factory = DocBlockFactory::createInstance();
		$docblock = $factory->create($method->getDocComment(), $context);

		return $docblock;
	}

	/**
	 * @param \ReflectionFunctionAbstract $method
	 *
	 * @return \phpDocumentor\Reflection\DocBlock\Tags\Param[]
	 */
	public function getArgumentsForMethod(\ReflectionFunctionAbstract $method)
	{
		$data = $this->getForFunction($method);
		if (!$data) {
			return false;
		}

		$args = $data->getTagsByName('param');

		$result = [];
		foreach ($args as $arg) {
			$type = $arg->getType();
			if ($type instanceof Array_) {
				$type = [$type->getValueType()];
			}

			$result[$arg->getVariableName()] = $type;
		}

		return $result;
	}

	public function getReturnType(\ReflectionFunctionAbstract $method)
	{
		$data = $this->getForFunction($method);

		$args = $data->getTagsByName('return');

		if (!empty($args[0])) {
			$result = $args[0]->getType();

			if ($result instanceof Array_) {
				return [$result->getValueType()];
			} else {
				return $result;
			}
		}

		return null;
	}
}
