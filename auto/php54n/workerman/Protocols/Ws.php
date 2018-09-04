<?php
namespace Workerman\Protocols;

use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\TcpConnection;

class Ws
{
	const BINARY_TYPE_BLOB = "\x81";
	const BINARY_TYPE_ARRAYBUFFER = "\x82";

	public static function input($buffer, $connection)
	{
		if (empty($connection->handshakeStep)) {
			echo "recv data before handshake. Buffer:" . bin2hex($buffer) . "\n";
			return false;
		}
		if ($connection->handshakeStep === 1) {
			return self::dealHandshake($buffer, $connection);
		}
		$recv_len = strlen($buffer);
		if ($recv_len < 2) {
			return 0;
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
					if (isset($connection->onWebSocketClose)) {
						try {
							call_user_func($connection->onWebSocketClose, $connection);
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
					if (isset($connection->onWebSocketPing)) {
						try {
							call_user_func($connection->onWebSocketPing, $connection);
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
							return self::input(substr($buffer, $head_len), $connection);
						}
						return 0;
					}
					break;
				case 0xa:
					if (isset($connection->onWebSocketPong)) {
						try {
							call_user_func($connection->onWebSocketPong, $connection);
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
							return self::input(substr($buffer, $head_len), $connection);
						}
						return 0;
					}
					break;
				default :
					echo "error opcode $opcode and close websocket connection. Buffer:" . $buffer . "\n";
					$connection->close();
					return 0;
			}
			if ($data_len === 126) {
				if (strlen($buffer) < 6) {
					return 0;
				}
				$pack = unpack('nn/ntotal_len', $buffer);
				$current_frame_length = $pack['total_len'] + 4;
			} else if ($data_len === 127) {
				if (strlen($buffer) < 10) {
					return 0;
				}
				$arr = unpack('n/N2c', $buffer);
				$current_frame_length = $arr['c1'] * 4294967296 + $arr['c2'] + 10;
			} else {
				$current_frame_length = $data_len + 2;
			}
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
			self::decode($buffer, $connection);
			$connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
			$connection->websocketCurrentFrameLength = 0;
			return 0;
		} elseif ($connection->websocketCurrentFrameLength < $recv_len) {
			self::decode(substr($buffer, 0, $connection->websocketCurrentFrameLength), $connection);
			$connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
			$current_frame_length = $connection->websocketCurrentFrameLength;
			$connection->websocketCurrentFrameLength = 0;
			return self::input(substr($buffer, $current_frame_length), $connection);
		} else {
			return 0;
		}
	}

	public static function encode($payload, $connection)
	{
		if (empty($connection->websocketType)) {
			$connection->websocketType = self::BINARY_TYPE_BLOB;
		}
		$payload = (string)$payload;
		if (empty($connection->handshakeStep)) {
			self::sendHandshake($connection);
		}
		$mask = 1;
		$mask_key = "\x00\x00\x00\x00";
		$pack = '';
		$length = $length_flag = strlen($payload);
		if (65535 < $length) {
			$pack = pack('NN', ($length & 0xFFFFFFFF00000000) >> 32, $length & 0x00000000FFFFFFFF);
			$length_flag = 127;
		} else if (125 < $length) {
			$pack = pack('n*', $length);
			$length_flag = 126;
		}
		$head = ($mask << 7) | $length_flag;
		$head = $connection->websocketType . chr($head) . $pack;
		$frame = $head . $mask_key;
		for ($i = 0; $i < $length; $i++) {
			$frame .= $payload[$i] ^ $mask_key[$i % 4];
		}
		if ($connection->handshakeStep === 1) {
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
			$connection->tmpWebsocketData = $connection->tmpWebsocketData . $frame;
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
		return $frame;
	}

	public static function decode($bytes, $connection)
	{
		$masked = ord($bytes[1]) >> 7;
		$data_length = $masked ? ord($bytes[1]) & 127 : ord($bytes[1]);
		$decoded_data = '';
		if ($masked === true) {
			if ($data_length === 126) {
				$mask = substr($bytes, 4, 4);
				$coded_data = substr($bytes, 8);
			} else if ($data_length === 127) {
				$mask = substr($bytes, 10, 4);
				$coded_data = substr($bytes, 14);
			} else {
				$mask = substr($bytes, 2, 4);
				$coded_data = substr($bytes, 6);
			}
			for ($i = 0; $i < strlen($coded_data); $i++) {
				$decoded_data .= $coded_data[$i] ^ $mask[$i % 4];
			}
		} else {
			if ($data_length === 126) {
				$decoded_data = substr($bytes, 4);
			} else if ($data_length === 127) {
				$decoded_data = substr($bytes, 10);
			} else {
				$decoded_data = substr($bytes, 2);
			}
		}
		if ($connection->websocketCurrentFrameLength) {
			$connection->websocketDataBuffer .= $decoded_data;
			return $connection->websocketDataBuffer;
		} else {
			if ($connection->websocketDataBuffer !== '') {
				$decoded_data = $connection->websocketDataBuffer . $decoded_data;
				$connection->websocketDataBuffer = '';
			}
			return $decoded_data;
		}
	}

	public static function onConnect($connection)
	{
		self::sendHandshake($connection);
	}

	public static function onClose($connection)
	{
		$connection->handshakeStep = null;
		$connection->websocketCurrentFrameLength = 0;
		$connection->tmpWebsocketData = '';
		$connection->websocketDataBuffer = '';
		if (!empty($connection->websocketPingTimer)) {
			Timer::del($connection->websocketPingTimer);
			$connection->websocketPingTimer = null;
		}
	}

	public static function sendHandshake($connection)
	{
		if (!empty($connection->handshakeStep)) {
			return;
		}
		$port = $connection->getRemotePort();
		$host = $port === 80 ? $connection->getRemoteHost() : $connection->getRemoteHost() . ':' . $port;
		$header = 'GET ' . $connection->getRemoteURI() . " HTTP/1.1\r\n" . "Host: $host\r\n" . "Connection: Upgrade\r\n" . "Upgrade: websocket\r\n" . "Origin: " . (isset($connection->websocketOrigin) ? $connection->websocketOrigin : '*') . "\r\n" . (isset($connection->WSClientProtocol) ? "Sec-WebSocket-Protocol: " . $connection->WSClientProtocol . "\r\n" : '') . "Sec-WebSocket-Version: 13\r\n" . "Sec-WebSocket-Key: " . base64_encode(md5(mt_rand(), true)) . "\r\n\r\n";
		$connection->send($header, true);
		$connection->handshakeStep = 1;
		$connection->websocketCurrentFrameLength = 0;
		$connection->websocketDataBuffer = '';
		$connection->tmpWebsocketData = '';
	}

	public static function dealHandshake($buffer, $connection)
	{
		$pos = strpos($buffer, "\r\n\r\n");
		if ($pos) {
			$header = explode("\r\n", substr($buffer, 0, $pos));
			foreach ($header as $hrow) {
				if (preg_match("#^(.+?)\:(.+?)$#", $hrow, $m) && ($m[1] == "Sec-WebSocket-Protocol")) {
					$connection->WSServerProtocol = trim($m[2]);
				}
			}
			$connection->handshakeStep = 2;
			$handshake_response_length = $pos + 4;
			if (isset($connection->onWebSocketConnect)) {
				try {
					call_user_func($connection->onWebSocketConnect, $connection, substr($buffer, 0, $handshake_response_length));
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
			}
			if (!empty($connection->websocketPingInterval)) {
				$connection->websocketPingTimer = Timer::add($connection->websocketPingInterval, function () use ($connection) {
					if (false === $connection->send(pack('H*', '898000000000'), true)) {
						Timer::del($connection->websocketPingTimer);
						$connection->websocketPingTimer = null;
					}
				});
			}
			$connection->consumeRecvBuffer($handshake_response_length);
			if (!empty($connection->tmpWebsocketData)) {
				$connection->send($connection->tmpWebsocketData, true);
				$connection->tmpWebsocketData = '';
			}
			if (strlen($buffer) > $handshake_response_length) {
				return self::input(substr($buffer, $handshake_response_length), $connection);
			}
		}
		return 0;
	}

	public static function WSSetProtocol($connection, $params)
	{
		$connection->WSClientProtocol = $params[0];
	}

	public static function WSGetServerProtocol($connection)
	{
		return (property_exists($connection, 'WSServerProtocol') ? $connection->WSServerProtocol : null);
	}
} 