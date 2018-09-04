<?php
namespace Workerman\Connection;

use Workerman\Events\EventInterface;
use Workerman\Worker;
use Exception;

class AsyncUdpConnection extends UdpConnection
{
	public function __construct($remote_address)
	{
		list($scheme, $address) = explode(':', $remote_address, 2);
		if ($scheme !== 'udp') {
			$scheme = ucfirst($scheme);
			$this->protocol = '\\Protocols\\' . $scheme;
			if (!class_exists($this->protocol)) {
				$this->protocol = "\\Workerman\\Protocols\\$scheme";
				if (!class_exists($this->protocol)) {
					throw new Exception("class \\Protocols\\$scheme not exist");
				}
			}
		}
		$this->_remoteAddress = substr($address, 2);
		$this->_socket = stream_socket_client("udp://{$this->_remoteAddress}");
		Worker::$globalEvent->add($this->_socket, EventInterface::EV_READ, array($this, 'baseRead'));
	}

	public function baseRead($socket)
	{
		$recv_buffer = stream_socket_recvfrom($socket, Worker::MAX_UDP_PACKAGE_SIZE, 0, $remote_address);
		if (false === $recv_buffer || empty($remote_address)) {
			return false;
		}
		if ($this->onMessage) {
			if ($this->protocol) {
				$parser = $this->protocol;
				$recv_buffer = $parser::decode($recv_buffer, $this);
			}
			ConnectionInterface::$statistics['total_request']++;
			try {
				call_user_func($this->onMessage, $this, $recv_buffer);
			} catch (\Exception $e) {
				self::log($e);
				exit(250);
			} catch (\Error $e) {
				self::log($e);
				exit(250);
			}
		}
		return true;
	}

	public function close($data = null, $raw = false)
	{
		if ($data !== null) {
			$this->send($data, $raw);
		}
		Worker::$globalEvent->del($this->_socket, EventInterface::EV_READ);
		fclose($this->_socket);
		return true;
	}
} 