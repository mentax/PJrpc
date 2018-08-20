<?php

namespace Mentax\PJRPC;

use Psr\Log\AbstractLogger;

class HtmlLogger extends AbstractLogger
{
	/**
	 * @var string
	 */
	private $logsDir;

	/**
	 * @param string $logsDir
	 */
	public function __construct($logsDir)
	{
		$this->logsDir = $logsDir;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = [])
	{
		$requestId = $context['requestId'] ?? 0;
		$clientMethod = $context['clientMethod'] ?? 'method';
		$event = $context['event'] ?? 'event';
		$data = $context['data'] ?? [];

		$logFilePath = $this->logsDir . '/' . $requestId . '.html';

		$message = sprintf("<h3 style='color:red'><span style='color:green;'>%s</span> %s</h3><pre>\n%s</pre>\n<hr>",
			$clientMethod,
			$event,
			json_encode($data, JSON_PRETTY_PRINT)
		);

		file_put_contents($logFilePath, $message, FILE_APPEND);
	}
}
