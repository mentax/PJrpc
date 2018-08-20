<?php

namespace Mentax\PJRPC;

class JSONDateTimeImmutable extends \DateTimeImmutable implements \JsonSerializable
{
	public function jsonSerialize()
	{
		return $this->format('c');
	}
}
