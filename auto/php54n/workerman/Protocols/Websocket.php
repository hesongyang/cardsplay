<?php
namespace Workerman\Protocols;

use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class Websocket implements \Workerman\Protocols\ProtocolInterface
{
	const BINARY_TYPE_BLOB = "\x81";
	const BINARY_TYPE_ARRAYBUFFER = "\x82";

	public static function input($buffer, ConnectionInterface $connection)
	{
		$recv_len = strlen($buffer);
		if ($recv_len < 2) {
			return 0;
		}
		if (empty($connection->websocketHandshake)) {
			return static::dealHandshake($buffer, $connection);
		}
		if ($connection->websocketCurrentFrameLength) {
			if ($connection->websocketCurrentFrameLength > $recv_len) {
				return 0;
			}
		} else {
			$firstbyte = ord($buffer[0]);
			$secondbyte = ord($buffer[1]);
			$data_len = $secondbyte & 127;
			$is_fin_frame = $firstbyte >> 7;
			$masked = $secondbyte >> 7;
			$opcode = $firstbyte & 0xf;
			switch ($opcode) {
				case 0x0:
					break;
				case 0x1:
					break;
				case 0x2:
					break;
				case 0x8:
					if (isset($connection->onWebSocketClose) || isset($connection->worker->onWebSocketClose)) {
						try {
							call_user_func(isset($connection->onWebSocketClose) ? $connection->onWebSocketClose : $connection->worker->onWebSocketClose, $connection);
						} catch (\Exception $e) {
							Worker::log($e);
							exit(250);
						} catch (\Error $e) {
							Worker::log($e);
							exit(250);
						}
					} else {
						$connection->close();
					}
					return 0;
				case 0x9:
					if (isset($connection->onWebSocketPing) || isset($connection->worker->onWebSocketPing)) {
						try {
							call_user_func(isset($connection->onWebSocketPing) ? $connection->onWebSocketPing : $connection->worker->onWebSocketPing, $connection);
						} catch (\Exception $e) {
							Worker::log($e);
							exit(250);
						} catch (\Error $e) {
							Worker::log($e);
							exit(250);
						}
					} else {
						$connection->send(pack('H*', '8a00'), true);
					}
					if (!$data_len) {
						$head_len = $masked ? 6 : 2;
						$connection->consumeRecvBuffer($head_len);
						if ($recv_len > $head_len) {
							return static::input(substr($buffer, $head_len), $connection);
						}
						return 0;
					}
					break;
				case 0xa:
					if (isset($connection->onWebSocketPong) || isset($connection->worker->onWebSocketPong)) {
						try {
							call_user_func(isset($connection->onWebSocketPong) ? $connection->onWebSocketPong : $connection->worker->onWebSocketPong, $connection);
						} catch (\Exception $e) {
							Worker::log($e);
							exit(250);
						} catch (\Error $e) {
							Worker::log($e);
							exit(250);
						}
					}
					if (!$data_len) {
						$head_len = $masked ? 6 : 2;
						$connection->consumeRecvBuffer($head_len);
						if ($recv_len > $head_len) {
							return static::input(substr($buffer, $head_len), $connection);
						}
						return 0;
					}
					break;
				default :
					echo "error opcode $opcode and close websocket connection. Buffer:" . bin2hex($buffer) . "\n";
					$connection->close();
					return 0;
			}
			$head_len = 6;
			if ($data_len === 126) {
				$head_len = 8;
				if ($head_len > $recv_len) {
					return 0;
				}
				$pack = unpack('nn/ntotal_len', $buffer);
				$data_len = $pack['total_len'];
			} else {
				if ($data_len === 127) {
					$head_len = 14;
					if ($head_len > $recv_len) {
						return 0;
					}
					$arr = unpack('n/N2c', $buffer);
					$data_len = $arr['c1'] * 4294967296 + $arr['c2'];
				}
			}
			$current_frame_length = $head_len + $data_len;
			$total_package_size = strlen($connection->websocketDataBuffer) + $current_frame_length;
			if ($total_package_size > TcpConnection::$maxPackageSize) {
				echo "error package. package_length=$total_package_size\n";
				$connection->close();
				return 0;
			}
			if ($is_fin_frame) {
				return $current_frame_length;
			} else {
				$connection->websocketCurrentFrameLength = $current_frame_length;
			}
		}
		if ($connection->websocketCurrentFrameLength === $recv_len) {
			static::decode($buffer, $connection);
			$connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
			$connection->websocketCurrentFrameLength = 0;
			return 0;
		} elseif ($connection->websocketCurrentFrameLength < $recv_len) {
			static::decode(substr($buffer, 0, $connection->websocketCurrentFrameLength), $connection);
			$connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
			$current_frame_length = $connection->websocketCurrentFrameLength;
			$connection->websocketCurrentFrameLength = 0;
			return static::input(substr($buffer, $current_frame_length), $connection);
		} else {
			return 0;
		}
	}

	public static function encode($buffer, ConnectionInterface $connection)
	{
		if (!is_scalar($buffer)) {
			throw new \Exception("You can't send(" . gettype($buffer) . ") to client, you need to convert it to a string. ");
		}
		$len = strlen($buffer);
		if (empty($connection->websocketType)) {
			$connection->websocketType = static::BINARY_TYPE_BLOB;
		}
		$first_byte = $connection->websocketType;
		if ($len <= 125) {
			$encode_buffer = $first_byte . chr($len) . $buffer;
		} else {
			if ($len <= 65535) {
				$encode_buffer = $first_byte . chr(126) . pack("n", $len) . $buffer;
			} else {
				$encode_buffer = $first_byte . chr(127) . pack("xxxxN", $len) . $buffer;
			}
		}
		if (empty($connection->websocketHandshake)) {
			if (empty($connection->tmpWebsocketData)) {
				$connection->tmpWebsocketData = '';
			}
			if (strlen($connection->tmpWebsocketData) > $connection->maxSendBufferSize) {
				if ($connection->onError) {
					try {
						call_user_func($connection->onError, $connection, WORKERMAN_SEND_FAIL, 'send buffer full and drop package');
					} catch (\Exception $e) {
						Worker::log($e);
						exit(250);
					} catch (\Error $e) {
						Worker::log($e);
						exit(250);
					}
				}
				return '';
			}
			$connection->tmpWebsocketData .= $encode_buffer;
			if ($connection->maxSendBufferSize <= strlen($connection->tmpWebsocketData)) {
				if ($connection->onBufferFull) {
					try {
						call_user_func($connection->onBufferFull, $connection);
					} catch (\Exception $e) {
						Worker::log($e);
						exit(250);
					} catch (\Error $e) {
						Worker::log($e);
						exit(250);
					}
				}
			}
			return '';
		}
		return $encode_buffer;
	}

	public static function decode($buffer, ConnectionInterface $connection)
	{
		$masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;
		if ($len === 126) {
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		} else {
			if ($len === 127) {
				$masks = substr($buffer, 10, 4);
				$data = substr($buffer, 14);
			} else {
				$masks = substr($buffer, 2, 4);
				$data = substr($buffer, 6);
			}
		}
		for ($index = 0; $index < strlen($data); $index++) {
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		if ($connection->websocketCurrentFrameLength) {
			$connection->websocketDataBuffer .= $decoded;
			return $connection->websocketDataBuffer;
		} else {
			if ($connection->websocketDataBuffer !== '') {
				$decoded = $connection->websocketDataBuffer . $decoded;
				$connection->websocketDataBuffer = '';
			}
			return $decoded;
		}
	}

	protected static function dealHandshake($buffer, $connection)
	{
		if (0 === strpos($buffer, 'GET')) {
			$heder_end_pos = strpos($buffer, "\r\n\r\n");
			if (!$heder_end_pos) {
				return 0;
			}
			$header_length = $heder_end_pos + 4;
			$Sec_WebSocket_Key = '';
			if (preg_match("/Sec-WebSocket-Key: *(.*?)\r\n/i", $buffer, $match)) {
				$Sec_WebSocket_Key = $match[1];
			} else {
				$connection->send("HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Sec-WebSocket-Key not found.<br>This is a WebSocket service and can not be accessed via HTTP.<br>See <a href=\"http://wiki.workerman.net/Error1\">http://wiki.workerman.net/Error1</a> for detail.", true);
				$connection->close();
				return 0;
			}
			$new_key = base64_encode(sha1($Sec_WebSocket_Key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
			$handshake_message = "HTTP/1.1 101 Switching Protocols\r\n";
			$handshake_message .= "Upgrade: websocket\r\n";
			$handshake_message .= "Sec-WebSocket-Version: 13\r\n";
			$handshake_message .= "Connection: Upgrade\r\n";
			$handshake_message .= "Server: workerman/" . Worker::VERSION . "\r\n";
			$handshake_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
			$connection->websocketHandshake = true;
			$connection->websocketDataBuffer = '';
			$connection->websocketCurrentFrameLength = 0;
			$connection->websocketCurrentFrameBuffer = '';
			$connection->consumeRecvBuffer($header_length);
			$connection->send($handshake_message, true);
			if (!empty($connection->tmpWebsocketData)) {
				$connection->send($connection->tmpWebsocketData, true);
				$connection->tmpWebsocketData = '';
			}
			if (empty($connection->websocketType)) {
				$connection->websocketType = static::BINARY_TYPE_BLOB;
			}
			if (isset($connection->onWebSocketConnect) || isset($connection->worker->onWebSocketConnect)) {
				static::parseHttpHeader($buffer);
				try {
					call_user_func(isset($connection->onWebSocketConnect) ? $connection->onWebSocketConnect : $connection->worker->onWebSocketConnect, $connection, $buffer);
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
				if (!empty($_SESSION) && class_exists('\GatewayWorker\Lib\Context')) {
					$connection->session = \GatewayWorker\Lib\Context::sessionEncode($_SESSION);
				}
				$_GET = $_SERVER = $_SESSION = $_COOKIE = array();
			}
			if (strlen($buffer) > $header_length) {
				return static::input(substr($buffer, $header_length), $connection);
			}
			return 0;
		} elseif (0 === strpos($buffer, '<polic')) {
			$policy_xml = '<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>' . "\0";
			$connection->send($policy_xml, true);
			$connection->consumeRecvBuffer(strlen($buffer));
			return 0;
		}
		$connection->send("HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Invalid handshake data for websocket. <br> See <a href=\"http://wiki.workerman.net/Error1\">http://wiki.workerman.net/Error1</a> for detail.", true);
		$connection->close();
		return 0;
	}

	protected static function parseHttpHeader($buffer)
	{
		list($http_header,) = explode("\r\n\r\n", $buffer, 2);
		$header_data = explode("\r\n", $http_header);
		if ($_SERVER) {
			$_SERVER = array();
		}
		list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ', $header_data[0]);
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
			}
		}
		$_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		if ($_SERVER['QUERY_STRING']) {
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
		}
	}
} 