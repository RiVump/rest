<?php

namespace RiVump\Facade\Rest;

/**
 * Class Rest
 * @package RiVump
 */
class Bitrix
{
	/**
	 * @var int
	 */
	private static $req_count = 0;
	/**
	 * @var int
	 */
	private static $req_start_time = 0;
	/**
	 * @var string
	 */
	public $restUrl;

	/**
	 * @var bool
	 */
	public $withSSL;

	/**
	 * Rest constructor.
	 * @param string $restUrl
	 */
	public function __construct(string $restUrl, $withSSL = true)
	{
		$this->restUrl = $restUrl;
		$this->withSSL = $withSSL;
	}

	/**
	 *
	 */
	public function check_limit(): void
	{
		$time = round(microtime(true) - static::$req_start_time, 1);
		$time_ms = (1 - $time) * 1000000;

		if(static::$req_start_time == 0 && static::$req_count == 0) {
			static::$req_count++;
			static::$req_start_time = microtime(true);
		}
		elseif(static::$req_count >= 2 && $time <= 1) {
			usleep($time_ms);

			static::$req_count = 1;
			static::$req_start_time = microtime(true);
		}
		elseif($time > 1) {
			static::$req_count = 1;
			static::$req_start_time = microtime(true);
		}
		else {
			static::$req_count++;
		}
	}

	/**
	 * @param string $method
	 * @param array $fields
	 * @return array|false
	 */
	protected function response(string $method, array $fields = [])
	{
		$this->check_limit();

		$restUrl = $this->restUrl . $method . '.json';

		if (!empty($fields)) {
			$restUrl = $restUrl . '?' . http_build_query($fields);
		}

		$cURLConnection = curl_init();
		$verbose = fopen('php://temp', 'w+');
		curl_setopt($cURLConnection, CURLOPT_VERBOSE, $verbose);
		curl_setopt($cURLConnection, CURLOPT_URL, $restUrl);
		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURLConnection,CURLOPT_SSL_VERIFYPEER,	$this->withSSL);

		$apiResponse = curl_exec($cURLConnection);
		curl_close($cURLConnection);

		$response = json_decode($apiResponse, true);

		return $response['result'] ? $response : false;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @return array|false
	 */
	public function get(string $method, array $params = [])
	{
		return $this->response($method, $params);
	}

	/**
	 * @param string $method
	 * @param array $fields
	 * @return array|false
	 */
	public function add(string $method, array $fields): bool
	{
		return $this->response($method, $fields);
	}

	/**
	 * @param string $method
	 * @param array $fields
	 * @return array|false
	 */
	public function update(string $method, array $fields)
	{
		return $this->response($method, $fields);
	}
}
