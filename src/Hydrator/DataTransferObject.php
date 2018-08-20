<?php

namespace Mentax\PJRPC\Hydrator;

/**
 * DTS
 * Data Transfer Object.
 */
abstract class DataTransferObject
{
	public function __construct($properties = [])
	{
		$this->map($properties);
	}

	protected function map($properties)
	{
		foreach ($properties as $k => $v) {
			$method = 'set' . ucfirst($k);
			is_callable([$this, $method]) && $this->$method($v);
		}
	}

	public function jsonSerialize()
	{
		$result = [];
		$properties = get_object_vars($this);

		foreach ($properties as $p => $v) {
			$getMethod = 'get' . ucfirst($p);
			$isMethod = 'is' . ucfirst($p);

			if (is_callable([$this, $getMethod])) {
				$value = $this->$getMethod();
			} elseif (is_callable([$this, $isMethod])) {
				$value = $this->$isMethod();
			}

			if (isset($value)) {
				$value = $this->fixDateTimeField($value);
				$result[$p] = $value;
			}
		}

		return $result;
	}

	protected function fixDateTimeField($field)
	{
		if ($field instanceof \DateTime) {
			return $field->format('c');
		}

		return $field;
	}
}
