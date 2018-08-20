<?php

namespace Mentax\PJRPC\Hydrator;

use phpDocumentor\Reflection\Types\ContextFactory;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class Hydrator
{
	/**
	 * @var Serializer
	 */
	protected $serializer;

	/**
	 * @var \phpDocumentor\Reflection\Types\Context[]
	 */
	protected $contextsCache = [];

	/**
	 * @var ArrayDenormalizer
	 */
	protected $arrayDenormalizer = null;

	public function __construct()
	{
		$normalizer = new ObjectNormalizer(null, null, null, new PhpDocExtractor());
		$serializer = new Serializer([$normalizer, new PropertyNormalizer()]);

		$arrayDenormalizer = new ArrayDenormalizer();
		$arrayDenormalizer->setSerializer($serializer);

		$this->arrayDenormalizer = $arrayDenormalizer;
		$this->serializer = $serializer;
	}

	/**
	 * @param mixed $var
	 * @param string $classOrType
	 * @param \Reflector $method
	 *
	 * @return object[]|object
	 */
	public function processPayload($var, $classOrType, \Reflector $method)
	{
		if (!is_array($var)) {
			return $var;
		}

		$isCollection = is_numeric(array_keys($var)[0]);

		if ($isCollection) {
			return $this->hydrate($var, is_array($classOrType) ? $classOrType : [$classOrType], $method);
		}

		return $this->hydrate($var, $classOrType, $method);
	}

	/**
	 * @param mixed $var object of stdClasses to convert for a required object or a scalar
	 * @param string|array $type class type to transform
	 * @param \Reflector|null $method
	 *
	 * @return array|object
	 */
	public function hydrate($var, $type, \Reflector $method)
	{
		if (is_array($type)) {
			if (false !== strpos($type[0], '\\')) {
				return $this->arrayDenormalizer->denormalize($var, $type[0] . '[]');
			}

			return $var;
		}

		if (false !== strpos($type, '\\')) {
			return $this->serializer->denormalize($var, $type, null, [$this->createContext($method)]);
		}

		return $var;
	}

    /**
     * creates context for a serializer
     * @param \Reflector $obj
     * @return array|\phpDocumentor\Reflection\Types\Context
     */
	public function createContext(\Reflector $obj)
	{
		$key = spl_object_hash($obj);
		if (isset($this->contextsCache[$key])) {
			return $this->contextsCache[$key];
		}

		$contextFactory = new ContextFactory();
		$context = $contextFactory->createFromReflector($obj);

		$this->contextsCache[$key] = $context;

		return [$context];
	}
}
