<?php

/**
 * Description of ApplePushNotificationService
 *
 * @author Kannika Kong
 */

class ApplePushNotificationService {

	protected $_certificate = null;
	protected $_server = null;
	protected $_body = array();
	protected $_tokens = array();
	protected $_errors = array();

	public function __construct($certificate = null, $server = null) {
		if ($certificate) {
			$this->setCertificate($certificate);
		}
		if ($server) {
			$this->setServer($server);
		}
	}

	public function setCertificate($certificate) {
		if (is_file($certificate)) {
			$this->_certificate = $certificate;
		}
		else {
			throw new Exception("Invalid path to certificate :: $certificate");
		}
	}

	public function setServer($server) {
		if ($server === ApnsParams::ENVIRONMENT_PRODUCTION || $server === ApnsParams::ENVIRONMENT_SANDBOX) {
			$this->_server = $server;
		}
		else {
			throw new Exception("Invalid Server APNS");
		}
	}

	public function setMessage($message) {
		if (!is_string($message) || !$message) {
			throw new Exception("Invalid Message");
		}
		$this->_body['aps'] = array(
			'badge' => +1,
			'alert' => array('loc-key' => $message),
			'sound' => 'default'
		);
	}

	public function setCustomMessage(array $customMessages) {
		foreach ($customMessages as $key => $val) {
			$this->_body[$key] = $val;
		}
	}

	public function addToken($token) {
		if ($token) {
			$this->_tokens[] = $token;
		}
	}

	public function getErrors() {
		return $this->_errors;
	}

	public function reset() {
		$this->_errors = array();
		$this->_tokens = array();
	}

	public function send() {
		if (!$this->_certificate) {
			throw new Exception("Invalid certificate" . PHP_EOL);
		}
		if (!$this->_server) {
			throw new Exception("Invalid Server" . PHP_EOL);
		}

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_certificate);
		$fp = stream_socket_client($this->_server, $errno, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp) {
			throw new Exception("Failed to connect apple server: $errno $errstr" . PHP_EOL);
		}

		$payload = json_encode($this->_body);
		$tokens = $this->_tokens;
		foreach ($this->_tokens as $index => $deviceToken) {
			unset($tokens[$index]);
			try {
				$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
				$result = fwrite($fp, $msg, strlen($msg));
				if (!$result) {
					$this->_errors[] = array(
						'TOKEN' => $deviceToken,
						'MESSAGE' => 'Message not delivered' . PHP_EOL,
					);
					$this->_tokens = $tokens;
					$this->send();
					return;
				}
			} catch (Exception $e) {
				$this->_errors[] = array(
					'TOKEN' => $deviceToken,
					'MESSAGE' => 'Message not delivered ' . $e->getMessage() . PHP_EOL,
				);
				$this->_tokens = $tokens;
				$this->send();
				return;
			}
		}

		fclose($fp);
	}
}

class ApnsParams {
	const ENVIRONMENT_PRODUCTION = 'ssl://gateway.push.apple.com:2195';
	const ENVIRONMENT_SANDBOX = 'ssl://gateway.sandbox.push.apple.com:2195';
}
