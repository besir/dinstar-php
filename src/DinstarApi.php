<?php

declare(strict_types=1);

namespace Besir\Dinstar;

use Besir\Dinstar\Response\SendSmsResponse;
use Besir\Dinstar\Data\SendSmsData;
use Besir\Dinstar\Response\GetPortInfoResponse;
use Besir\Dinstar\Data\PortInfoItemData;
use Besir\Dinstar\Collection\PortInfoItemCollection;

class DinstarApi
{
	private string $baseUrl;
	private ?\CurlHandle $curl = null;

	public function __construct(
		private string $gatewayIp,
		private string $username,
		private string $password,
		private bool $disableSslVerification = true,
		private int $connectTimeout = 10,
		private int $timeout = 30,
		private int $numberOfPorts = 8,
	) {
		$this->baseUrl = str_starts_with($gatewayIp, 'http') ? $gatewayIp : "https://{$gatewayIp}";
	}

	public function __destruct()
	{
		if ($this->curl !== null) {
			curl_close($this->curl);
			$this->curl = null;
		}
	}

	/**
	 * Resets the connection by closing the curl handle.
	 * Next request will create a fresh connection.
	 */
	public function resetConnection(): void
	{
		if ($this->curl !== null) {
			curl_close($this->curl);
			$this->curl = null;
		}
	}

	/**
	 * Gets or creates a persistent curl handle.
	 * The handle is reused for connection pooling, but options are reset per-request.
	 *
	 * @return \CurlHandle
	 * @throws \RuntimeException if curl initialization fails
	 */
	private function getCurl(): \CurlHandle
	{
		if ($this->curl === null) {
			$this->curl = curl_init();
			if ($this->curl === false) {
				throw new \RuntimeException('cURL initialization failed.');
			}
		}

		return $this->curl;
	}

	public function sendSms(
		string $recipient,
		string $text,
		array $params,
		int $smsId,
		?array $ports = null,
		?string $encoding = null,
		bool $requestStatusReport = true): SendSmsResponse
	{
		$params['number'] = $recipient;
		$payload = ['text' => $text, 'param' => [$params]];

		if ($ports !== null) {
			$payload['port'] = $ports;
		}
		if ($encoding !== null) {
			$payload['encoding'] = $encoding;
		}

		$payload['request_status_report'] = $requestStatusReport;

		$reqResult = $this->_makeRequest('send_sms', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 202;
		$data = null;

		if ($opSuccess && $reqResult['decodedBody'] !== null) {
			$data = new SendSmsData(smsInQueue: $reqResult['decodedBody']['sms_in_queue'] ?? null, taskId: $reqResult['decodedBody']['task_id'] ?? null);
		}
		return new SendSmsResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function querySmsResult(
		?array $numbers = null,
		?array $ports = null,
		?string $timeAfter = null,
		?string $timeBefore = null,
		?array $userIds = null
	): QuerySmsResultResponse {
		$payload = [];
		if ($numbers !== null) {
			$payload['number'] = $numbers;
		}
		if ($ports !== null) {
			$payload['port'] = $ports;
		}
		if ($timeAfter !== null) {
			$payload['time_after'] = $timeAfter;
		}
		if ($timeBefore !== null) {
			$payload['time_before'] = $timeBefore;
		}
		if ($userIds !== null) {
			$payload['user_id'] = $userIds;
		}

		$reqResult = $this->_makeRequest('/api/query_sms_result', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['result']) && is_array($reqResult['decodedBody']['result'])) {
			$items = array_map(fn($item) => new SmsResultItemData(port: $item['port'] ?? null, number: $item['number'] ?? null,
				userId: $item['user_id'] ?? null, time: $item['time'] ?? null, status: $item['status'] ?? null, count: $item['count'] ?? null,
				succCount: $item['succ_count'] ?? null, refId: $item['ref_id'] ?? null, imsi: $item['imsi'] ?? null), $reqResult['decodedBody']['result']);
			$collection = new SmsResultItemCollection($items);
		}
		return new QuerySmsResultResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function querySmsDeliverStatus(
		?array $numbers = null,
		?array $ports = null,
		?string $timeAfter = null,
		?string $timeBefore = null
	): QuerySmsDeliverStatusResponse {
		$payload = [];
		if ($numbers !== null) {
			$payload['number'] = $numbers;
		}
		if ($ports !== null) {
			$payload['port'] = $ports;
		}
		if ($timeAfter !== null) {
			$payload['time_after'] = $timeAfter;
		}
		if ($timeBefore !== null) {
			$payload['time_before'] = $timeBefore;
		}

		$reqResult = $this->_makeRequest('/api/query_sms_deliver_status', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['result']) && is_array($reqResult['decodedBody']['result'])) {
			$items = array_map(fn($item) => new SmsDeliverStatusItemData(port: $item['port'] ?? null, number: $item['number'] ?? null,
				time: $item['time'] ?? null, refId: $item['ref_id'] ?? null, statusCode: $item['status_code'] ?? null, imsi: $item['imsi'] ?? null),
				$reqResult['decodedBody']['result']);
			$collection = new SmsDeliverStatusItemCollection($items);
		}
		return new QuerySmsDeliverStatusResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function querySmsInQueue(): QuerySmsInQueueResponse
	{
		$reqResult = $this->_makeRequest('/api/query_sms_in_queue', 'GET');
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$data = null;

		if ($opSuccess && $reqResult['decodedBody'] !== null) {
			$data = $reqResult['decodedBody']['in_queue'] ?? null;
		}
		return new QuerySmsInQueueResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function queryIncomingSms(?int $incomingSmsId = null, string $flag = 'unread', ?array $ports = null): QueryIncomingSmsResponse
	{
		$params = ['flag' => $flag];
		if ($incomingSmsId !== null) {
			$params['incoming_sms_id'] = $incomingSmsId;
		}
		if ($ports !== null) {
			$params['port'] = implode(',', $ports);
		}

		$reqResult = $this->_makeRequest('/api/query_incoming_sms', 'GET', $params);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$data = null;

		if ($opSuccess && $reqResult['decodedBody'] !== null) {
			$smsItemCollection = null;
			if (isset($reqResult['decodedBody']['sms']) && is_array($reqResult['decodedBody']['sms'])) {
				$items = array_map(fn($item) => new IncomingSmsItemData(incomingSmsId: $item['incoming_sms_id'] ?? null, port: $item['port'] ?? null,
					number: $item['number'] ?? null, smsc: $item['smsc'] ?? null, timestamp: $item['timestamp'] ?? null, text: $item['text'] ?? null),
					$reqResult['decodedBody']['sms']);
				$smsItemCollection = new IncomingSmsItemCollection($items);
			}
			$data = new QueryIncomingSmsData(sms: $smsItemCollection, read: $reqResult['decodedBody']['read'] ?? null,
				unread: $reqResult['decodedBody']['unread'] ?? null);
		}
		return new QueryIncomingSmsResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function sendUssd(array $ports, ?string $text = null, string $command = 'send'): SendUssdResponse
	{
		if ($command === 'send' && ($text === null || $text === '')) {
			return new SendUssdResponse(false, 0, null, null, null, "USSD text is required for command 'send'.", null);
		}
		$payload = ['port' => $ports, 'command' => $command];
		if ($text !== null) {
			$payload['text'] = $text;
		}

		$reqResult = $this->_makeRequest('/api/send_ussd', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 202;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['result']) && is_array($reqResult['decodedBody']['result'])) {
			$items = array_map(fn($item) => new UssdResultItemData(port: $item['port'] ?? null, status: $item['status'] ?? null),
				$reqResult['decodedBody']['result']);
			$collection = new UssdResultItemCollection($items);
		}
		return new SendUssdResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function queryUssdReply(array $ports): QueryUssdReplyResponse
	{
		$params = ['port' => implode(',', $ports)];
		$reqResult = $this->_makeRequest('/api/query_ussd_reply', 'GET', $params);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['reply']) && is_array($reqResult['decodedBody']['reply'])) {
			$items = array_map(fn($item) => new UssdReplyItemData(port: $item['port'] ?? null, text: $item['text'] ?? null),
				$reqResult['decodedBody']['reply']);
			$collection = new UssdReplyItemCollection($items);
		}
		return new QueryUssdReplyResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function stopSmsTask(int $taskId): DinstarApiResponse // Using base for simple response
	{
		$reqResult = $this->_makeRequest('/api/stop_sms', 'GET', ['task_id' => $taskId]);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;

		return new DinstarApiResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, null, // No specific data payload
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function getPortInfo(array $infoType = [], ?array $ports = null): GetPortInfoResponse
	{
		if (empty($infoType)) {
			$infoType = [
				'port',
				'type',
				'imei',
				'imsi',
				'iccid',
				'reg',
				'slot',
				'callstate',
				'signal',
				'gprs',
				'remain_credit',
				'remain_monthly_credit',
				'remain_daily_credit',
				'remain_daily_calltime',
				'remain_hourly_calltime',
				'remain_daily_connect',
			];
		}

		$params = ['info_type' => implode(',', $infoType)];
		if ($ports !== null) {
			$params['port'] = implode(',', $ports);
		} else {
			$params['port'] = $this->getAllPorts();
		}

		$reqResult = $this->_makeRequest('get_port_info', 'GET', $params);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['info']) && is_array($reqResult['decodedBody']['info'])) {
			$items = array_map(fn($item) => new PortInfoItemData(
				port: $item['port'] ?? null,
				type: $item['type'] ?? null,
				imei: $item['imei'] ?? null,
				imsi: $item['imsi'] ?? null,
				iccid: $item['iccid'] ?? null,
				number: $item['number'] ?? null,
				reg: $item['reg'] ?? null,
				slot: $item['slot'] ?? null,
				callState: $item['callstate'] ?? null,
				signal: $item['signal'] ?? null,
				gprs: $item['gprs'] ?? null,
				remainCredit: $item['remain_credit'] ?? null,
				remainMonthlyCredit: $item['remain_monthly_credit'] ?? null,
				remainDailyCredit: $item['remain_daily_credit'] ?? null,
				remainDailyCallTime: $item['remain_daily_calltime'] ?? null,
				remainHourlyCallTime: $item['remain_hourly_calltime'] ?? null,
				remainDailyConnect: $item['remain_daily_connect'] ?? null,
				callForwarding: $item['CallForwarding'] ?? ($item['CallForward'] ?? null)), $reqResult['decodedBody']['info']);
			$collection = new PortInfoItemCollection($items);
		}
		return new GetPortInfoResponse(
			$opSuccess,
			$reqResult['httpCode'],
			$dinstarErrorCode,
			$collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function setPortInfo(int $port, string $action, ?string $param = null, ?string $number = null): DinstarApiResponse // Using base
	{
		if ($action === 'CallForward' && $param !== 'CancelAll' && ($number === null || $number === '')) {
			return new DinstarApiResponse(false, 0, null, null, null,
				"Phone number ('number') is required for action 'CallForward' unless 'param' is 'CancelAll'.", null);
		}
		$params = ['port' => $port, 'action' => $action];
		if ($param !== null) {
			$params['param'] = $param;
		}
		if ($action === 'CallForward' && $number !== null) {
			$params['number'] = $number;
		}


		$reqResult = $this->_makeRequest('/api/set_port_info', 'GET', $params);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;

		return new DinstarApiResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, null,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function getCdr(?array $ports = null, ?string $timeAfter = null, ?string $timeBefore = null): GetCdrResponse
	{
		$payload = [];
		if ($ports !== null) {
			$payload['port'] = $ports;
		}
		if ($timeAfter !== null) {
			$payload['time_after'] = $timeAfter;
		}
		if ($timeBefore !== null) {
			$payload['time_before'] = $timeBefore;
		}

		$reqResult = $this->_makeRequest('/api/get_cdr', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && $dinstarErrorCode === 200;
		$collection = null;

		if ($opSuccess && isset($reqResult['decodedBody']['cdr']) && is_array($reqResult['decodedBody']['cdr'])) {
			$items = array_map(fn($item) => new CdrItemData(port: $item['port'] ?? null, startDate: $item['start_date'] ?? null,
				answerDate: $item['answer_date'] ?? null, duration: $item['duration'] ?? null, sourceNumber: $item['source_number'] ?? null,
				destinationNumber: $item['destination_number'] ?? null, direction: $item['direction'] ?? null, ip: $item['ip'] ?? null,
				codec: $item['codec'] ?? null, hangup: $item['hangup'] ?? null, gsmCode: $item['gsm_code'] ?? null,
				bcch: $item['bcch'] ?? ($item['bech'] ?? null)), $reqResult['decodedBody']['cdr']);
			$collection = new CdrItemCollection($items);
		}
		return new GetCdrResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $collection,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess && $dinstarErrorCode !== null ? "Dinstar API error: {$dinstarErrorCode}" : null), $gatewaySn);
	}

	public function queryStkInfo(int $port): QueryStkInfoResponse
	{
		$reqResult = $this->_makeRequest('/GetSTKView', 'GET', ['port' => $port], false);
		$opSuccess = $reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null;
		$data = null;
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;

		if ($opSuccess) {
			$data = new StkInfoData(title: $reqResult['decodedBody']['title'] ?? null, text: $reqResult['decodedBody']['text'] ?? null,
				inputType: $reqResult['decodedBody']['input_type'] ?? null, itemData: $reqResult['decodedBody']['item'] ?? null,
				frameId: $reqResult['decodedBody']['frame_id'] ?? null);
		}
		return new QueryStkInfoResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess ? "STK API error or unparsable response" : null), $gatewaySn);
	}

	public function stkOperation(int $port, ?int $item = null, ?string $param = null, ?string $action = null): DinstarApiResponse // Using base
	{
		$payload = ['port' => $port];
		if ($item !== null) {
			$payload['item'] = $item;
		}
		if ($param !== null) {
			$payload['param'] = $param;
		}
		if ($action !== null) {
			$payload['action'] = $action;
		}

		$reqResult = $this->_makeRequest('/STKGo', 'POST', $payload, false);
		$opSuccess = $reqResult['httpSuccessful'];
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;

		return new DinstarApiResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, null,
			($reqResult['httpSuccessful'] && empty(trim($reqResult['rawBody']))) ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess ? "STK Operation Error" : null), $gatewaySn);
	}

	public function queryStkFrameId(int $port): QueryStkFrameIdResponse
	{
		$reqResult = $this->_makeRequest('/GetSTKCurrFrameIndex', 'GET', ['port' => $port], false);
		$opSuccess = $reqResult['httpSuccessful'] && isset($reqResult['decodedBody']['frame_id']);
		$data = null;
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;

		if ($opSuccess) {
			$data = $reqResult['decodedBody']['frame_id'];
		}
		return new QueryStkFrameIdResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess ? "STK API error or unparsable response" : null), $gatewaySn);
	}

	public function getDeviceStatus(): GetDeviceStatusResponse
	{
		$payload = ['performance'];
		$reqResult = $this->_makeRequest('/api/get_status', 'POST', $payload);
		$dinstarErrorCode = $reqResult['decodedBody']['error_code'] ?? null;
		$gatewaySn = $reqResult['decodedBody']['sn'] ?? null;
		$opSuccess = $reqResult['httpSuccessful'] && isset($reqResult['decodedBody']['performance']);
		$data = null;

		if ($opSuccess && isset($reqResult['decodedBody']['performance'])) {
			$perf = $reqResult['decodedBody']['performance'];
			$data = new DeviceStatusData(cpuUsed: $perf['cpu_used'] ?? null, flashTotal: $perf['flash_total'] ?? null, flashUsed: $perf['flash_used'] ?? null,
				memoryTotal: $perf['memory_total'] ?? null, memoryCached: $perf['memory_cached'] ?? null, memoryBuffers: $perf['memory_buffers'] ?? null,
				memoryFree: $perf['memory_free'] ?? null, memoryUsed: $perf['memory_used'] ?? null);
		}
		return new GetDeviceStatusResponse($opSuccess, $reqResult['httpCode'], $dinstarErrorCode, $data,
			$reqResult['httpSuccessful'] && $reqResult['decodedBody'] !== null ? null : $reqResult['rawBody'],
			$reqResult['curlError'] ?? (! $opSuccess ? "API error or missing 'performance' data" : null), $gatewaySn);
	}

	/**
	 * Internal method to execute HTTP(S) requests.
	 * Returns a structured array with camelCase keys:
	 * 'httpSuccessful' (bool): True if HTTP status is 2xx.
	 * 'httpCode' (int): The HTTP status code.
	 * 'decodedBody' (?array): JSON decoded response body, or null if decoding failed or empty.
	 * 'rawBody' (string): Raw response body.
	 * 'curlError' (?string): cURL error message if any.
	 *
	 * @param string $path API path
	 * @param string $method HTTP method
	 * @param array $data Request data
	 * @param bool $useApiPrefix Whether to prefix path with /api/
	 * @param bool $isRetry Whether this is a retry attempt (internal use)
	 */
	private function _makeRequest(
		string $path,
		string $method = 'GET',
		array $data = [],
		bool $useApiPrefix = true,
		bool $isRetry = false
	): array {
		$urlPath = ($useApiPrefix ? '/api/' : '') . $path;
		$url = $this->baseUrl . $urlPath;
		$method = strtoupper($method);

		try {
			$curl = $this->getCurl();
		} catch (\RuntimeException $e) {
			error_log("cURL initialization failed: " . $e->getMessage());
			return ['httpSuccessful' => false, 'httpCode' => 0, 'decodedBody' => null, 'rawBody' => '', 'curlError' => $e->getMessage()];
		}

		// Reset all options to defaults but keep connection alive
		curl_reset($curl);

		// Re-apply common options after reset
		// Use CURLAUTH_DIGEST to avoid probe request that CURLAUTH_ANY does (which gateway may count as failed attempt)
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($curl, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !$this->disableSslVerification);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, false);

		if ($method === 'GET' && !empty($data)) {
			$url .= '?' . http_build_query($data);
		} elseif ($method === 'POST') {
			$postData = json_encode($data);
			if ($postData === false) {
				error_log("JSON encoding failed for POST data. Error: " . json_last_error_msg());
				return ['httpSuccessful' => false, 'httpCode' => 0, 'decodedBody' => null, 'rawBody' => '', 'curlError' => "JSON encoding failed."];
			}
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Connection: Keep-Alive',
			'Accept: */*',
		]);

		$responseBody = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curlError = curl_error($curl);

		// Retry logic for 401 (Unauthorized) or 403 (Forbidden) - likely a session problem on gateway side
		if (($httpCode === 401 || $httpCode === 403) && !$isRetry) {
			$maskedPassword = $this->maskPassword($this->password);
			$bodyPreview = is_string($responseBody) ? substr($responseBody, 0, 200) : '(empty)';
			error_log("Got {$httpCode} from {$url}, user='{$this->username}', pass='{$maskedPassword}', body='{$bodyPreview}', retrying with fresh connection after 500ms delay...");
			usleep(500000); // 500ms delay
			$this->resetConnection();
			return $this->_makeRequest($path, $method, $data, $useApiPrefix, true);
		}

		// Log auth failure after retry
		if (($httpCode === 401 || $httpCode === 403) && $isRetry) {
			$maskedPassword = $this->maskPassword($this->password);
			$bodyPreview = is_string($responseBody) ? substr($responseBody, 0, 200) : '(empty)';
			error_log("Auth failed after retry: {$httpCode} from {$url}, user='{$this->username}', pass='{$maskedPassword}', body='{$bodyPreview}'");
		}

		if ($responseBody === false || !empty($curlError)) {
			$error = $curlError ?: 'Unknown cURL error';
			error_log("cURL error: {$error} (URL: {$url}, HTTP Code: {$httpCode})");
			return ['httpSuccessful' => false, 'httpCode' => $httpCode, 'decodedBody' => null, 'rawBody' => (string)$responseBody, 'curlError' => $error];
		}

		if (empty(trim($responseBody))) { // Handle empty but successful HTTP response
			return [
				'httpSuccessful' => ($httpCode >= 200 && $httpCode < 300),
				'httpCode' => $httpCode,
				'decodedBody' => null,
				'rawBody' => '',
				'curlError' => null,
			];
		}

		$decoded = json_decode($responseBody, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log("JSON decode error: " . json_last_error_msg() . " (HTTP: {$httpCode}, URL: {$url})");
			return [
				'httpSuccessful' => false,
				'httpCode' => $httpCode,
				'decodedBody' => null,
				'rawBody' => $responseBody,
				'curlError' => "JSON decode error: " . json_last_error_msg(),
			];
		}

		return [
			'httpSuccessful' => ($httpCode >= 200 && $httpCode < 300),
			'httpCode' => $httpCode,
			'decodedBody' => $decoded,
			'rawBody' => $responseBody,
			'curlError' => null,
		];
	}

	private function getAllPorts(): string
	{
		$range = range(0, $this->numberOfPorts - 1);
		$result = implode(',', $range);

		return $result;
	}

	/**
	 * Masks password for logging - shows first and last char with dots in between.
	 * Example: "password123" -> "p.........3"
	 */
	private function maskPassword(string $password): string
	{
		$len = strlen($password);
		if ($len <= 2) {
			return str_repeat('*', $len);
		}
		return $password[0] . str_repeat('.', $len - 2) . $password[$len - 1];
	}
}
