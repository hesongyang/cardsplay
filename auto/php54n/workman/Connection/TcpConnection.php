<?php
namespace Workerman\Connection;

use Workerman\Events\EventInterface;
use Workerman\Worker;
use Exception;

class TcpConnection extends ConnectionInterface
{
	const READ_BUFFER_SIZE = 65535;
	const STATUS_INITIAL = 0;
	const STATUS_CONNECTING = 1;
	const STATUS_ESTABLISH = 2;
	const STATUS_CLOSING = 4;
	const STATUS_CLOSED = 8;
	public $onMessage = null;
	public $onClose = null;
	public $onError = null;
	public $onBufferFull = null;
	public $onBufferDrain = null;
	public $protocol = null;
	public $worker = null;
	public $id = 0;
	protected $_id = 0;
	public $maxSendBufferSize = 1048576;
	public static $defaultMaxSendBufferSize = 1048576;
	public static $maxPackageSize = 10485760;
	protected static $_idRecorder = 1;
	protected $_socket = null;
	protected $_sendBuffer = '';
	protected $_recvBuffer = '';
	protected $_currentPackageLength = 0;
	protected $_status = self::STATUS_ESTABLISH;
	protected $_remoteAddress = '';
	protected $_isPaused = false;

	public function __construct($socket, $remote_address = '')
	{
		self::$statistics['connection_count']++;
		$this->id = $this->_id = self::$_idRecorder++;
		$this->_socket = $socket;
		stream_set_blocking($this->_socket, 0);
		if (function_exists('stream_set_read_buffer')) {
			stream_set_read_buffer($this->_socket, 0);
		}
		Worker::$globalEvent->add($this->_socket, EventInterface::EV_READ, array($this, 'baseRead'));
		$this->maxSendBufferSize = self::$defaultMaxSendBufferSize;
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
		if ($this->_status === self::STATUS_INITIAL || $this->_status === self::STATUS_CONNECTING) {
			if ($this->_sendBuffer) {
				if ($this->bufferIsFull()) {
					self::$statistics['send_fail']++;
					return false;
				}
			}
			$this->_sendBuffer .= $send_buffer;
			$this->checkBufferWillFull();
			return null;
		} elseif ($this->_status === self::STATUS_CLOSING || $this->_status === self::STATUS_CLOSED) {
			return false;
		}
		if ($this->_sendBuffer === '') {
			$len = @fwrite($this->_socket, $send_buffer);
			if ($len === strlen($send_buffer)) {
				return true;
			}
			if ($len > 0) {
				$this->_sendBuffer = substr($send_buffer, $len);
			} else {
				if (!is_resource($this->_socket) || feof($this->_socket)) {
					self::$statistics['send_fail']++;
					if ($this->onError) {
						try {
							call_user_func($this->onError, $this, WORKERMAN_SEND_FAIL, 'client closed');
						} catch (\Exception $e) {
							Worker::log($e);
							exit(250);
						} catch (\Error $e) {
							Worker::log($e);
							exit(250);
						}
					}
					$this->destroy();
					return false;
				}
				$this->_sendBuffer = $send_buffer;
			}
			Worker::$globalEvent->add($this->_socket, EventInterface::EV_WRITE, array($this, 'baseWrite'));
			$this->checkBufferWillFull();
			return null;
		} else {
			if ($this->bufferIsFull()) {
				self::$statistics['send_fail']++;
				return false;
			}
			$this->_sendBuffer .= $send_buffer;
			$this->checkBufferWillFull();
		}
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

	public function pauseRecv()
	{
		Worker::$globalEvent->del($this->_socket, EventInterface::EV_READ);
		$this->_isPaused = true;
	}

	public function resumeRecv()
	{
		if ($this->_isPaused === true) {
			Worker::$globalEvent->add($this->_socket, EventInterface::EV_READ, array($this, 'baseRead'));
			$this->_isPaused = false;
			$this->baseRead($this->_socket, false);
		}
	}

	public function baseRead($socket, $check_eof = true)
	{
		$buffer = fread($socket, self::READ_BUFFER_SIZE);
		if ($buffer === '' || $buffer === false) {
			if ($check_eof && (feof($socket) || !is_resource($socket) || $buffer === false)) {
				$this->destroy();
				return;
			}
		} else {
			$this->_recvBuffer .= $buffer;
		}
		if ($this->protocol) {
			$parser = $this->protocol;
			while ($this->_recvBuffer !== '' && !$this->_isPaused) {
				if ($this->_currentPackageLength) {
					if ($this->_currentPackageLength > strlen($this->_recvBuffer)) {
						break;
					}
				} else {
					$this->_currentPackageLength = $parser::input($this->_recvBuffer, $this);
					if ($this->_currentPackageLength === 0) {
						break;
					} elseif ($this->_currentPackageLength > 0 && $this->_currentPackageLength <= self::$maxPackageSize) {
						if ($this->_currentPackageLength > strlen($this->_recvBuffer)) {
							break;
						}
					} else {
						echo 'error package. package_length=' . var_export($this->_currentPackageLength, true);
						$this->destroy();
						return;
					}
				}
				self::$statistics['total_request']++;
				if (strlen($this->_recvBuffer) === $this->_currentPackageLength) {
					$one_request_buffer = $this->_recvBuffer;
					$this->_recvBuffer = '';
				} else {
					$one_request_buffer = substr($this->_recvBuffer, 0, $this->_currentPackageLength);
					$this->_recvBuffer = substr($this->_recvBuffer, $this->_currentPackageLength);
				}
				$this->_currentPackageLength = 0;
				if (!$this->onMessage) {
					continue;
				}
				try {
					call_user_func($this->onMessage, $this, $parser::decode($one_request_buffer, $this));
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
			}
			return;
		}
		if ($this->_recvBuffer === '' || $this->_isPaused) {
			return;
		}
		self::$statistics['total_request']++;
		if (!$this->onMessage) {
			$this->_recvBuffer = '';
			return;
		}
		try {
			call_user_func($this->onMessage, $this, $this->_recvBuffer);
		} catch (\Exception $e) {
			Worker::log($e);
			exit(250);
		} catch (\Error $e) {
			Worker::log($e);
			exit(250);
		}
		$this->_recvBuffer = '';
	}

	public function baseWrite()
	{
		$len = @fwrite($this->_socket, $this->_sendBuffer);
		if ($len === strlen($this->_sendBuffer)) {
			Worker::$globalEvent->del($this->_socket, EventInterface::EV_WRITE);
			$this->_sendBuffer = '';
			if ($this->onBufferDrain) {
				try {
					call_user_func($this->onBufferDrain, $this);
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
			}
			if ($this->_status === self::STATUS_CLOSING) {
				$this->destroy();
			}
			return true;
		}
		if ($len > 0) {
			$this->_sendBuffer = substr($this->_sendBuffer, $len);
		} else {
			self::$statistics['send_fail']++;
			$this->destroy();
		}
	}

	public function pipe($dest)
	{
		$source = $this;
		$this->onMessage = function ($source, $data) use ($dest) {
			$dest->send($data);
		};
		$this->onClose = function ($source) use ($dest) {
			$dest->destroy();
		};
		$dest->onBufferFull = function ($dest) use ($source) {
			$source->pauseRecv();
		};
		$dest->onBufferDrain = function ($dest) use ($source) {
			$source->resumeRecv();
		};
	}

	public function consumeRecvBuffer($length)
	{
		$this->_recvBuffer = substr($this->_recvBuffer, $length);
	}

	public function close($data = null, $raw = false)
	{
		if ($this->_status === self::STATUS_CLOSING || $this->_status === self::STATUS_CLOSED) {
			return;
		} else {
			if ($data !== null) {
				$this->send($data, $raw);
			}
			$this->_status = self::STATUS_CLOSING;
		}
		if ($this->_sendBuffer === '') {
			$this->destroy();
		}
	}

	public function getSocket()
	{
		return $this->_socket;
	}

	protected function checkBufferWillFull()
	{
		if ($this->maxSendBufferSize <= strlen($this->_sendBuffer)) {
			if ($this->onBufferFull) {
				try {
					call_user_func($this->onBufferFull, $this);
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
			}
		}
	}

	protected function bufferIsFull()
	{
		if ($this->maxSendBufferSize <= strlen($this->_sendBuffer)) {
			if ($this->onError) {
				try {
					call_user_func($this->onError, $this, WORKERMAN_SEND_FAIL, 'send buffer full and drop package');
				} catch (\Exception $e) {
					Worker::log($e);
					exit(250);
				} catch (\Error $e) {
					Worker::log($e);
					exit(250);
				}
			}
			return true;
		}
		return false;
	}

	public function destroy()
	{
		if ($this->_status === self::STATUS_CLOSED) {
			return;
		}
		Worker::$globalEvent->del($this->_socket, EventInterface::EV_READ);
		Worker::$globalEvent->del($this->_socket, EventInterface::EV_WRITE);
		@fclose($this->_socket);
		if ($this->worker) {
			unset($this->worker->connections[$this->_id]);
		}
		$this->_status = self::STATUS_CLOSED;
		if ($this->onClose) {
			try {
				call_user_func($this->onClose, $this);
			} catch (\Exception $e) {
				Worker::log($e);
				exit(250);
			} catch (\Error $e) {
				Worker::log($e);
				exit(250);
			}
		}
		if (method_exists($this->protocol, 'onClose')) {
			try {
				call_user_func(array($this->protocol, 'onClose'), $this);
			} catch (\Exception $e) {
				Worker::log($e);
				exit(250);
			} catch (\Error $e) {
				Worker::log($e);
				exit(250);
			}
		}
		if ($this->_status === self::STATUS_CLOSED) {
			$this->onMessage = $this->onClose = $this->onError = $this->onBufferFull = $this->onBufferDrain = null;
		}
	}

	public function __destruct()
	{
		self::$statistics['connection_count']--;
	}
} 