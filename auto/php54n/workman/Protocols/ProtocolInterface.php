<?php
namespace Workerman\Protocols;

use Workerman\Connection\ConnectionInterface;

interface ProtocolInterface
{
	public static function input($recv_buffer, ConnectionInterface $connection);

	public static function decode($recv_buffer, ConnectionInterface $connection);

	public static function encode($data, ConnectionInterface $connection);
} 