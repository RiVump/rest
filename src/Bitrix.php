<?php

namespace RiVump\Facade\Rest;

/**
 * Class Rest
 * @package Ilab
 */
class Bitrix
{
	/**
	 * @var int
	 */
	public $reqCount = 0;
	/**
	 * @var int
	 */
	public $reqStartTime = 0;
	/**
	 * @var string
	 */
	public $restUrl;

	/**
	 * Rest constructor.
	 * @param string $restUrl
	 */
	public function __construct(string $restUrl)
	{
		$this->restUrl = $restUrl;
	}

	/**
	 *
	 */
	public function check_limit(): void
	{
		$time = round(microtime(true) - $this->reqStartTime, 1);
		$time_ms = (1 - $time) * 1000000;

		if ($this->reqStartTime == 0 && $this->reqCount == 0) {
			$this->reqCount++;
			$this->reqStartTime = microtime(true);
		} elseif ($this->reqCount >= 2 && $time <= 1) {
			usleep($time_ms);

			$this->reqCount = 1;
			$this->reqStartTime = microtime(true);
		} elseif ($time > 1) {
			$this->reqCount = 1;
			$this->reqStartTime = microtime(true);
		} else {
			$this->reqCount++;
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
		//curl_setopt($cURLConnection,CURLOPT_SSL_VERIFYPEER, false);

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
