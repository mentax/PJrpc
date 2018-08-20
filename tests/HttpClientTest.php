<?php

namespace tests\Mentax\PJRPC;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mentax\PJRPC\Exception\ClientError;
use Mentax\PJRPC\Hydrator\HttpClient;
use PHPUnit\Framework\TestCase;
use tests\Mentax\PJRPC\Mocks\TestException;

class HttpClientTest extends TestCase
{
	protected function instantiateClient(Response $response = null)
	{
		$client = new HttpClient('/');

		if ($response) {
			$mock = new MockHandler([
				$response,
			]);

			$handler = HandlerStack::create($mock);
			$guzzleClient = new Client(['handler' => $handler]);

			$client->setClient(
				$guzzleClient
			);
		}

		return $client;
	}

	public function testCorrectResponse()
	{
		$client = $this->instantiateClient(
			new Response(200, ['Content-Type' => 'application/json'], json_encode([
				'key' => 'value',
			]))
		);

		$result = $client->execute('{}');
		$this->assertArrayHasKey('key', $result);
		$this->assertEquals('value', $result['key']);
	}

	public function testInvalidResponse()
	{
		$client = $this->instantiateClient(
			new Response(500)
		);

		$this->expectException(ClientError::class);
		$client->execute('');
	}

	public function testApplicationException()
	{
		$client = $this->instantiateClient(
			new Response('200', ['Content-Type' => 'application/json'], json_encode([
				'error' => [
					'class' => TestException::class,
					'data' => [
						'message' => 'test',
					],
				],
			]))
		);

		$this->expectException(TestException::class);
		try {
			$client->execute('');
		} catch (TestException $ex) {
			$this->assertEquals('test', $ex->getMessage());
			throw $ex;
		}
	}

	public function testCookieJarBuilderFromAssoc()
	{
		$client = $this->instantiateClient();

		$cookies = [
			'first' => 'value1',
			'second' => 'value2',
		];

		$method = new \ReflectionMethod($client, 'createCookieJar');
		$method->setAccessible(true);

		$result = $method->invoke($client, $cookies);
		$this->assertInstanceOf(CookieJar::class, $result);
		$this->assertCount(2, $result);
		$this->assertEquals('value1', $result->getCookieByName('first')->getValue());
		$this->assertEquals('value2', $result->getCookieByName('second')->getValue());
	}

	public function testCookieJarBuilderFromString()
	{
		$client = $this->instantiateClient();

		$cookies = [
			'first=value1; expires=Sat, 02 May 2029 23:38:25 GMT; domain=example.org',
		];

		$method = new \ReflectionMethod($client, 'createCookieJar');
		$method->setAccessible(true);

		$result = $method->invoke($client, $cookies);
		$this->assertInstanceOf(CookieJar::class, $result);
		$this->assertCount(1, $result);
		$this->assertEquals('value1', $result->getCookieByName('first')->getValue());
	}
}
