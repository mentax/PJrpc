<?php

namespace Mentax\PJRPC\Exception;

abstract class ApiExceptionAbstract extends \Exception implements \JsonSerializable
{
	public function jsonSerialize()
	{
		$properties = get_class_vars(
			get_class($this)
		);

		$result = [];

		foreach (array_keys($properties) as $p) {
			if ('file' == $p || 'line' == $p) {
				continue;
			}

			$result[$p] = $this->$p;
		}

		return $result;
	}

	public function jsonUnserialize($unserializedData)
	{
		$properties = get_class_vars(
			get_class($this)
		);

		foreach (array_keys($properties) as $p) {
			if (isset($unserializedData[$p])) {
				$this->$p = $unserializedData[$p];
			}
		}
	}
}
