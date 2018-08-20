<?php

namespace Mentax\PJRPC\Hydrator;

use Mentax\PJRPC\Exception\ApiExceptionAbstract;

class ResponseBuilder extends \JsonRPC\Response\ResponseBuilder
{
	protected $errorClass;
	protected $errorData;

	protected function buildErrorResponse()
	{
		if ($this->errorClass) {
			$response = [
				'class' => $this->errorClass,
			];

			if (!empty($this->errorData)) {
				$response['data'] = $this->errorData;
			}

			return $response;
		}

		return parent::buildErrorResponse();
	}

	protected function buildResponse()
	{
		$response = ['jsonrpc' => '2.0'];
		$this->handleExceptions();

		if (!empty($this->errorMessage) || !empty($this->errorClass)) {
			$response['error'] = $this->buildErrorResponse();
		} else {
			$response['result'] = $this->result;
		}

		$response['id'] = $this->id;

		return $response;
	}

	protected function handleExceptions()
	{
		if ($this->exception instanceof ApiExceptionAbstract || $this->exception instanceof \InvalidArgumentException) {
			// got rid from redundant fields, eg. message and code here comparing to previous version
			$this->errorClass = get_class($this->exception);
			$this->errorData = $this->exception instanceof \JsonSerializable ?? $this->exception->jsonSerialize();

			return;
		}

		parent::handleExceptions();
	}
}
