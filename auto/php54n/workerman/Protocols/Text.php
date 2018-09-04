<?php
namespace Workerman\Protocols;

use Workerman\Connection\TcpConnection;

class Text
{
	public static function input($buffer, TcpConnection $connection)
	{
		if (strlen($buffer) >= TcpConnection::$maxPackageSize) {
			$connection->close();
			return 0;
		}
		$pos = strpos($buffer, "\n");
		if ($pos === false) {
			return 0;
		}
		return $pos + 1;
	}

	public static function encode($buffer)
	{
		return $buffer . "\n";
	}

	public static function decode($buffer)
	{
		return trim($buffer);
	}
} 