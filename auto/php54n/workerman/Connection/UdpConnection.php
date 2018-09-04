<?php
namespace Workerman\Connection;
class UdpConnection extends ConnectionInterface
{
	public $protocol = null;
	protected $_socket = null;
	protected $_remoteAddress = '';

	public function __construct($socket, $remote_address)
	{
		$this->_socket = $socket;
		$this->_remoteAddress = $remote_address;
	}

	public function send($send_buffer, $raw = false)
	{
		if (false === $raw && $this->protocol) {
			$parser = $this->protocol;
			$send_buffer = $parser::encode($send_buffer, $this);
			if ($send_buffer === '') {
				return null;
			}
		}
		return strlen($send_buffer) === stream_socket_sendto($this->_socket, $send_buffer, 0, $this->_remoteAddress);
	}

	public function getRemoteIp()
	{
		$pos = strrpos($this->_remoteAddress, ':');
		if ($pos) {
			return trim(substr($this->_remoteAddress, 0, $pos), '[]');
		}
		return '';
	}

	public function getRemotePort()
	{
		if ($this->_remoteAddress) {
			return (int)substr(strrchr($this->_remoteAddress, ':'), 1);
		}
		return 0;
	}

	public function getRemoteAddress()
	{
		return $this->_remoteAddress;
	}

	public function getLocalIp()
	{
		$address = $this->getLocalAddress();
		$pos = strrpos($address, ':');
		if (!$pos) {
			return '';
		}
		return substr($address, 0, $pos);
	}

	public function getLocalPort()
	{
		$address = $this->getLocalAddress();
		$pos = strrpos($address, ':');
		if (!$pos) {
			return 0;
		}
		return (int)substr(strrchr($address, ':'), 1);
	}

	public function getLocalAddress()
	{
		return (string)@stream_socket_get_name($this->_socket, false);
	}

	public function isIpV4()
	{
		if ($this->transport === 'unix') {
			return false;
		}
		return strpos($this->getRemoteIp(), ':') === false;
	}

	public function isIpV6()
	{
		if ($this->transport === 'unix') {
			return false;
		}
		return strpos($this->getRemoteIp(), ':') !== false;
	}

	public function close($data = null, $raw = false)
	{
		if ($data !== null) {
			$this->send($data, $raw);
		}
		return true;
	}
} 