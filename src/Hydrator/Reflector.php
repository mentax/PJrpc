<?php

namespace Mentax\PJRPC\Hydrator;

class Reflector
{
	/**
	 * get variable type for first handler argument.
	 * untested - bound too much with JsonRPC logic.
	 *
	 * @param \ReflectionFunctionAbstract $method
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function getCallbackArgumentTypes(\ReflectionFunctionAbstract $method)
	{
		$types = [];

		// get first argument
		$arguments = $method->getParameters();

		$parser = new PhpDocParser();

		$phpdoc = $parser->getArgumentsForMethod($method);

		foreach ($arguments as $argument) {
			// phpdoc has a greater priority than native types becuase doesn't support a collection type
			if ($phpdoc && isset($phpdoc[$argument->getName()])) {
				$arg = $phpdoc[$argument->getName()];
				if (is_array($arg)) {
					$types[] = [(string)$arg[0]];
				} else {
					$types[] = (string)$arg;
				}
			} else {
				$types[] = (string)$argument->getType();
			}
		}

		return $types;
	}

	/**
	 * get return type of a method.
	 *
	 * @param \ReflectionFunctionAbstract $method
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function getReturnType(\ReflectionFunctionAbstract $method)
	{
		$parser = new PhpDocParser();
		$returnType = $parser->getReturnType($method);
		if (!$returnType) {
			$returnType = $method->getReturnType();
		} else {
			if (is_array($returnType)) {
				$returnType = [(string)$returnType[0]];
			} else {
				$returnType = (string)$returnType;
			}
		}

		return $returnType;
	}
}
