<?php
if (!ini_get('date.timezone')) {
	date_default_timezone_set('Asia/Shanghai');
}
ini_set('display_errors', 'on');
error_reporting(E_ALL);
define('WORKERMAN_CONNECT_FAIL', 1);
define('WORKERMAN_SEND_FAIL', 2);
if (!class_exists('Error')) {
	class Error extends Exception
	{
	}
}