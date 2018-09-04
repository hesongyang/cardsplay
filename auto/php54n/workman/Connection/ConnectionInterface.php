<?php
namespace Workerman\Connection;
abstract class ConnectionInterface
{
	public static $statistics = array('connection_count' => 0, 'total_request' => 0, 'throw_exception' => 0, 'send_fail' => 0,);
	public $onMessage = null;
	public $onClose = null;
	public $onError = null;

	abstract public function send($send_buffer);

	abstract public function getRemoteIp();

	abstract public function getRemotePort();

	abstract public function close($data = null);
} 