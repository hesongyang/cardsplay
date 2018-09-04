<?php
namespace Workerman\Protocols;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class Http
{
	public static $methods = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS');

	public static function input($recv_buffer, TcpConnection $connection)
	{
		if (!strpos($recv_buffer, "\r\n\r\n")) {
			if (strlen($recv_buffer) >= TcpConnection::$maxPackageSize) {
				$connection->close();
				return 0;
			}
			return 0;
		}
		list($header,) = explode("\r\n\r\n", $recv_buffer, 2);
		$method = substr($header, 0, strpos($header, ' '));
		if (in_array($method, static::$methods)) {
			return static::getRequestSize($header, $method);
		} else {
			$connection->send("HTTP/1.1 400 Bad Request\r\n\r\n", true);
			return 0;
		}
	}

	protected static function getRequestSize($header, $method)
	{
		if ($method === 'GET' || $method === 'OPTIONS' || $method === 'HEAD') {
			return strlen($header) + 4;
		}
		$match = array();
		if (preg_match("/\r\nContent-Length: ?(\d+)/i", $header, $match)) {
			$content_length = isset($match[1]) ? $match[1] : 0;
			return $content_length + strlen($header) + 4;
		}
		return 0;
	}

	public static function decode($recv_buffer, TcpConnection $connection)
	{
		$_POST = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = array();
		$GLOBALS['HTTP_RAW_POST_DATA'] = '';
		HttpCache::$header = array('Connection' => 'Connection: keep-alive');
		HttpCache::$instance = new HttpCache();
		$_SERVER = array('QUERY_STRING' => '', 'REQUEST_METHOD' => '', 'REQUEST_URI' => '', 'SERVER_PROTOCOL' => '', 'SERVER_SOFTWARE' => 'workerman/' . Worker::VERSION, 'SERVER_NAME' => '', 'HTTP_HOST' => '', 'HTTP_USER_AGENT' => '', 'HTTP_ACCEPT' => '', 'HTTP_ACCEPT_LANGUAGE' => '', 'HTTP_ACCEPT_ENCODING' => '', 'HTTP_COOKIE' => '', 'HTTP_CONNECTION' => '', 'REMOTE_ADDR' => '', 'REMOTE_PORT' => '0', 'REQUEST_TIME' => time());
		list($http_header, $http_body) = explode("\r\n\r\n", $recv_buffer, 2);
		$header_data = explode("\r\n", $http_header);
		list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ', $header_data[0]);
		$http_post_boundary = '';
		unset($header_data[0]);
		foreach ($header_data as $content) {
			if (empty($content)) {
				continue;
			}
			list($key, $value) = explode(':', $content, 2);
			$key = str_replace('-', '_', strtoupper($key));
			$value = trim($value);
			$_SERVER['HTTP_' . $key] = $value;
			switch ($key) {
				case 'HOST':
					$tmp = explode(':', $value);
					$_SERVER['SERVER_NAME'] = $tmp[0];
					if (isset($tmp[1])) {
						$_SERVER['SERVER_PORT'] = $tmp[1];
					}
					break;
				case 'COOKIE':
					parse_str(str_replace('; ', '&', $_SERVER['HTTP_COOKIE']), $_COOKIE);
					break;
				case 'CONTENT_TYPE':
					if (!preg_match('/boundary="?(\S+)"?/', $value, $match)) {
						if ($pos = strpos($value, ';')) {
							$_SERVER['CONTENT_TYPE'] = substr($value, 0, $pos);
						} else {
							$_SERVER['CONTENT_TYPE'] = $value;
						}
					} else {
						$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
						$http_post_boundary = '--' . $match[1];
					}
					break;
				case 'CONTENT_LENGTH':
					$_SERVER['CONTENT_LENGTH'] = $value;
					break;
			}
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (isset($_SERVER['CONTENT_TYPE'])) {
				switch ($_SERVER['CONTENT_TYPE']) {
					case 'multipart/form-data':
						self::parseUploadFiles($http_body, $http_post_boundary);
						break;
					case 'application/x-www-form-urlencoded':
						parse_str($http_body, $_POST);
						break;
				}
			}
		}
		$GLOBALS['HTTP_RAW_REQUEST_DATA'] = $GLOBALS['HTTP_RAW_POST_DATA'] = $http_body;
		$_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		if ($_SERVER['QUERY_STRING']) {
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
		}
		$_REQUEST = array_merge($_GET, $_POST);
		$_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
		$_SERVER['REMOTE_PORT'] = $connection->getRemotePort();
		return array('get' => $_GET, 'post' => $_POST, 'cookie' => $_COOKIE, 'server' => $_SERVER, 'files' => $_FILES);
	}

	public static function encode($content, TcpConnection $connection)
	{
		if (!isset(HttpCache::$header['Http-Code'])) {
			$header = "HTTP/1.1 200 OK\r\n";
		} else {
			$header = HttpCache::$header['Http-Code'] . "\r\n";
			unset(HttpCache::$header['Http-Code']);
		}
		if (!isset(HttpCache::$header['Content-Type'])) {
			$header .= "Content-Type: text/html;charset=utf-8\r\n";
		}
		foreach (HttpCache::$header as $key => $item) {
			if ('Set-Cookie' === $key && is_array($item)) {
				foreach ($item as $it) {
					$header .= $it . "\r\n";
				}
			} else {
				$header .= $item . "\r\n";
			}
		}
		$header .= "Server: workerman/" . Worker::VERSION . "\r\nContent-Length: " . strlen($content) . "\r\n\r\n";
		self::sessionWriteClose();
		return $header . $content;
	}

	public static function header($content, $replace = true, $http_response_code = 0)
	{
		if (PHP_SAPI != 'cli') {
			return $http_response_code ? header($content, $replace, $http_response_code) : header($content, $replace);
		}
		if (strpos($content, 'HTTP') === 0) {
			$key = 'Http-Code';
		} else {
			$key = strstr($content, ":", true);
			if (empty($key)) {
				return false;
			}
		}
		if ('location' === strtolower($key) && !$http_response_code) {
			return self::header($content, true, 302);
		}
		if (isset(HttpCache::$codes[$http_response_code])) {
			HttpCache::$header['Http-Code'] = "HTTP/1.1 $http_response_code " . HttpCache::$codes[$http_response_code];
			if ($key === 'Http-Code') {
				return true;
			}
		}
		if ($key === 'Set-Cookie') {
			HttpCache::$header[$key][] = $content;
		} else {
			HttpCache::$header[$key] = $content;
		}
		return true;
	}

	public static function headerRemove($name)
	{
		if (PHP_SAPI != 'cli') {
			header_remove($name);
			return;
		}
		unset(HttpCache::$header[$name]);
	}

	public static function setcookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false)
	{
		if (PHP_SAPI != 'cli') {
			return setcookie($name, $value, $maxage, $path, $domain, $secure, $HTTPOnly);
		}
		return self::header('Set-Cookie: ' . $name . '=' . rawurlencode($value) . (empty($domain) ? '' : '; Domain=' . $domain) . (empty($maxage) ? '' : '; Max-Age=' . $maxage) . (empty($path) ? '' : '; Path=' . $path) . (!$secure ? '' : '; Secure') . (!$HTTPOnly ? '' : '; HttpOnly'), false);
	}

	public static function sessionStart()
	{
		if (PHP_SAPI != 'cli') {
			return session_start();
		}
		self::tryGcSessions();
		if (HttpCache::$instance->sessionStarted) {
			echo "already sessionStarted\n";
			return true;
		}
		HttpCache::$instance->sessionStarted = true;
		if (!isset($_COOKIE[HttpCache::$sessionName]) || !is_file(HttpCache::$sessionPath . '/ses' . $_COOKIE[HttpCache::$sessionName])) {
			$file_name = tempnam(HttpCache::$sessionPath, 'ses');
			if (!$file_name) {
				return false;
			}
			HttpCache::$instance->sessionFile = $file_name;
			$session_id = substr(basename($file_name), strlen('ses'));
			return self::setcookie(HttpCache::$sessionName, $session_id, ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'), ini_get('session.cookie_secure'), ini_get('session.cookie_httponly'));
		}
		if (!HttpCache::$instance->sessionFile) {
			HttpCache::$instance->sessionFile = HttpCache::$sessionPath . '/ses' . $_COOKIE[HttpCache::$sessionName];
		}
		if (HttpCache::$instance->sessionFile) {
			$raw = file_get_contents(HttpCache::$instance->sessionFile);
			if ($raw) {
				$_SESSION = unserialize($raw);
			}
		}
		return true;
	}

	public static function sessionWriteClose()
	{
		if (PHP_SAPI != 'cli') {
			return session_write_close();
		}
		if (!empty(HttpCache::$instance->sessionStarted) && !empty($_SESSION)) {
			$session_str = serialize($_SESSION);
			if ($session_str && HttpCache::$instance->sessionFile) {
				return file_put_contents(HttpCache::$instance->sessionFile, $session_str);
			}
		}
		return empty($_SESSION);
	}

	public static function end($msg = '')
	{
		if (PHP_SAPI != 'cli') {
			exit($msg);
		}
		if ($msg) {
			echo $msg;
		}
		throw new \Exception('jump_exit');
	}

	public static function getMimeTypesFile()
	{
		return __DIR__ . '/Http/mime.types';
	}

	protected static function parseUploadFiles($http_body, $http_post_boundary)
	{
		$http_body = substr($http_body, 0, strlen($http_body) - (strlen($http_post_boundary) + 4));
		$boundary_data_array = explode($http_post_boundary . "\r\n", $http_body);
		if ($boundary_data_array[0] === '') {
			unset($boundary_data_array[0]);
		}
		$key = -1;
		foreach ($boundary_data_array as $boundary_data_buffer) {
			list($boundary_header_buffer, $boundary_value) = explode("\r\n\r\n", $boundary_data_buffer, 2);
			$boundary_value = substr($boundary_value, 0, -2);
			$key++;
			foreach (explode("\r\n", $boundary_header_buffer) as $item) {
				list($header_key, $header_value) = explode(": ", $item);
				$header_key = strtolower($header_key);
				switch ($header_key) {
					case "content-disposition":
						if (preg_match('/name="(.*?)"; filename="(.*?)"$/', $header_value, $match)) {
							$_FILES[$key] = array('name' => $match[1], 'file_name' => $match[2], 'file_data' => $boundary_value, 'file_size' => strlen($boundary_value),);
							continue;
						} else {
							if (preg_match('/name="(.*?)"$/', $header_value, $match)) {
								$_POST[$match[1]] = $boundary_value;
							}
						}
						break;
					case "content-type":
						$_FILES[$key]['file_type'] = trim($header_value);
						break;
				}
			}
		}
	}

	public static function tryGcSessions()
	{
		if (HttpCache::$sessionGcProbability <= 0 || HttpCache::$sessionGcDivisor <= 0 || rand(1, HttpCache::$sessionGcDivisor) > HttpCache::$sessionGcProbability) {
			return;
		}
		$time_now = time();
		foreach (glob(HttpCache::$sessionPath . '/ses*') as $file) {
			if (is_file($file) && $time_now - filemtime($file) > HttpCache::$sessionGcMaxLifeTime) {
				unlink($file);
			}
		}
	}
}

class HttpCache
{
	public static $codes = array(100 => 'Continue', 101 => 'Switching Protocols', 200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => '(Unused)', 307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed', 422 => 'Unprocessable Entity', 423 => 'Locked', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported',);
	public static $instance = null;
	public static $header = array();
	public static $sessionPath = '';
	public static $sessionName = '';
	public static $sessionGcProbability = 1;
	public static $sessionGcDivisor = 1000;
	public static $sessionGcMaxLifeTime = 1440;
	public $sessionStarted = false;
	public $sessionFile = '';

	public static function init()
	{
		self::$sessionName = ini_get('session.name');
		self::$sessionPath = @session_save_path();
		if (!self::$sessionPath || strpos(self::$sessionPath, 'tcp://') === 0) {
			self::$sessionPath = sys_get_temp_dir();
		}
		if ($gc_probability = ini_get('session.gc_probability')) {
			self::$sessionGcProbability = $gc_probability;
		}
		if ($gc_divisor = ini_get('session.gc_divisor')) {
			self::$sessionGcDivisor = $gc_divisor;
		}
		if ($gc_max_life_time = ini_get('session.gc_maxlifetime')) {
			self::$sessionGcMaxLifeTime = $gc_max_life_time;
		}
	}
}

HttpCache::init(); 