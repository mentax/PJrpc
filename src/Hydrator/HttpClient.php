<?php

namespace Mentax\PJRPC\Hydrator;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use JsonRPC\Exception\AccessDeniedException;
use JsonRPC\Exception\ServerErrorException;
use Mentax\PJRPC\Exception\ApiExceptionAbstract;
use Mentax\PJRPC\Exception\ClientError;

/**
 * Class HttpClient
 * I'm so happy to re-create all methods in order to keep an API-compatible methods.
 */
class HttpClient extends \JsonRPC\HttpClient
{
	/**
	 * @var string
	 */
	protected $url;
	/**
	 * @var string
	 */
	protected $authUsername;
	/**
	 * @var string
	 */
	protected $authPassword;
	/**
	 * @var int
	 */
	protected $timeout = 60;
	/**
	 * @var array
	 */
	protected $headers = ['Content-Type' => 'application/json'];
	/**
	 * @var array
	 */
	protected $cookies;
	/**
	 * @var bool
	 */
	protected $debug = false;
	/*
	 * @var string[]
	 */
	protected $sslCertificate = [];
	/**
	 * @var \Callable
	 */
	protected $middleware;
	/**
	 * @var bool
	 */
	protected $skipSsl = false;
	/**
	 * @var array
	 */
	protected $guzzleOptions;

	/**
	 * @var Client
	 */
	protected $client;

	public function __construct($entrypoint)
	{
		$this->url = $entrypoint;
	}

	/**
	 * used to replace within unit-tests.
	 *
	 * @return Client
	 */
	protected function getClient()
	{
		if (!$this->client) {
			$this->client = new Client();
		}

		return $this->client;
	}

	public function setClient(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param string $payload
	 * @param array $headers
	 *
	 * @return array
	 *
	 * @throws ApiExceptionAbstract
	 * @throws \ReflectionException
	 * @throws ClientError
	 */
	public function execute($payload, array $headers = [])
	{
		$client = $this->getClient();

		if (is_callable($this->middleware)) {
			call_user_func_array($this->middleware, [$this, $payload, $headers]);
		}

		$options = $this->guzzleOptions;
		if ($this->authUsername && $this->authPassword) {
			$options['auth'] = [$this->authUsername, $this->authPassword];
		}

		if ($this->sslCertificate) {
			$options['ssl_keys'] = $this->sslCertificate;
		}

		if ($this->cookies) {
			$options['cookies'] = $this->createCookieJar($this->cookies);
		}

		if ($this->headers) {
			$options['headers'] = $this->headers;
		}

		if ($this->skipSsl) {
			$options['verify'] = false;
		}

		$options['body'] = $payload;

		$options['connect_timeout'] = $this->timeout;

		try {
			$res = $client->request('POST', $this->url, $options);
		} catch (GuzzleException $ex) {
			throw new ClientError($ex->getMessage(), 0, $ex);
		}

		$result = @json_decode($res->getBody(), true);

		if (!empty($result['error']) && !empty($result['error']['class'])) {
			if (class_exists($result['error']['class']) && is_subclass_of($result['error']['class'], ApiExceptionAbstract::class)) {
				$reflected = new \ReflectionClass($result['error']['class']);
				/**
				 * @var $ex ApiExceptionAbstract
				 */
				$ex = $reflected->newInstanceWithoutConstructor();

				$ex->jsonUnserialize($result['error']['data']);
				throw $ex;
			} else {
				throw new ClientError($result['error']);
			}
		}

		return $result;
	}

	public function setGuzzleOptions($options)
	{
		$this->guzzleOptions = $options;

		return $this;
	}

	protected function getEntrypointComponents()
	{
		$prototype = [
			'path' => '/',
			'host' => 'example.org',
		];

		$urlComponents = parse_url($this->url);

		return array_merge($prototype, $urlComponents);
	}

	protected function createCookieJar($cookies)
	{
		$urlComponents = $this->getEntrypointComponents();

		$cookiesResult = [];
		foreach ($cookies as $k => $c) {
			if ($c instanceof SetCookie) {
				$cookiesResult[] = $c;
				continue;
			}

			if ($k) {
				$obj = new SetCookie();
				$obj->setName($k);
				$obj->setValue($c);
				$obj->setPath($urlComponents['path']);
				$obj->setDomain($urlComponents['host']);
				$cookiesResult[] = $obj;
				continue;
			}

			$cookiesResult[] = SetCookie::fromString($c);
		}

		$result = new CookieJar(true, $cookiesResult);

		return $result;
	}

	/**
	 * Set URL.
	 *
	 * @param  string $url
	 *
	 * @return $this
	 */
	public function withUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Set username.
	 *
	 * @param  string $username
	 *
	 * @return $this
	 */
	public function withUsername($username)
	{
		$this->authUsername = $username;

		return $this;
	}

	/**
	 * Set password.
	 *
	 * @param  string $password
	 *
	 * @return $this
	 */
	public function withPassword($password)
	{
		$this->authPassword = $password;

		return $this;
	}

	/**
	 * Set timeout.
	 *
	 * @param  int $timeout
	 *
	 * @return $this
	 */
	public function withTimeout($timeout = 60)
	{
		$this->timeout = $timeout;

		return $this;
	}

	/**
	 * Set headers.
	 *
	 * @param  array $headers
	 *
	 * @return $this
	 */
	public function withHeaders(array $headers)
	{
		$this->headers = $headers;

		return $this;
	}

	/**
	 * Set cookies.
	 *
	 * @param  array $cookies
	 * @param  bool $replace
	 */
	public function withCookies(array $cookies, $replace = false)
	{
		$this->cookies = $replace ? array_merge($this->cookies, $cookies) : $cookies;

		return $this;
	}

	/**
	 * Enable debug mode.
	 *
	 * @param bool $status
	 *
	 * @return $this
	 */
	public function withDebug($status = true)
	{
		$this->debug = $status;

		return $this;
	}

	/**
	 * Disable SSL verification.
	 *
	 * @param $status bool
	 *
	 * @return $this
	 */
	public function withoutSslVerification($status = true)
	{
		$this->skipSsl = $status;

		return $this;
	}

	/**
	 * Assign a certificate to use TLS.
	 *
	 * @param string $path
	 * @param string|null $password
	 *
	 * @return $this
	 */
	public function withSslLocalCert($path, $password = null)
	{
		$this->sslCertificate = [$path, $password];

		return $this;
	}

	/**
	 * Assign a callback before the request.
	 *
	 * @param  Closure $closure
	 *
	 * @return $this
	 */
	public function withBeforeRequestCallback(Closure $closure)
	{
		$this->middleware = $closure;

		return $this;
	}

	/**
	 * Get cookies.
	 *
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Throw an exception according the HTTP response.
	 *
	 * @param  array $headers
	 *
	 * @throws AccessDeniedException
	 * @throws ServerErrorException
	 */
	public function handleExceptions(array $headers)
	{
		// TODO: Implement handleExceptions() method.
	}
}
