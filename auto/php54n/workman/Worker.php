<?php
namespace Workerman;
require_once __DIR__ . '/Lib/Constants.php';
use \Workerman\Events\Libevent;
use \Workerman\Events\Event;
use \Workerman\Events\React;
use \Workerman\Events\Select;
use \Workerman\Events\EventInterface;
use \Workerman\Connection\ConnectionInterface;
use \Workerman\Connection\TcpConnection;
use \Workerman\Connection\UdpConnection;
use \Workerman\Lib\Timer;
use \Workerman\Autoloader;
use \Exception;

class Worker
{
	const VERSION = '3.3.6';
	const STATUS_STARTING = 1;
	const STATUS_RUNNING = 2;
	const STATUS_SHUTDOWN = 4;
	const STATUS_RELOADING = 8;
	const KILL_WORKER_TIMER_TIME = 1;
	const DEFAUL_BACKLOG = 1024;
	const MAX_UDP_PACKAGE_SIZE = 65535;
	public $id = 0;
	public $name = 'none';
	public $count = 1;
	public $user = '';
	public $reloadable = true;
	public $reusePort = false;
	public $onWorkerStart = null;
	public $onConnect = null;
	public $onMessage = null;
	public $onClose = null;
	public $onError = null;
	public $onBufferFull = null;
	public $onBufferDrain = null;
	public $onWorkerStop = null;
	public $onWorkerReload = null;
	public $transport = 'tcp';
	public $connections = array();
	protected $protocol = '';
	protected $_autoloadRootPath = '';
	public static $daemonize = false;
	public static $stdoutFile = '/dev/null';
	public static $pidFile = '';
	public static $logFile = '';
	public static $globalEvent = null;
	protected static $_masterPid = 0;
	protected $_mainSocket = null;
	protected $_socketName = '';
	protected $_context = null;
	protected static $_workers = array();
	protected static $_pidMap = array();
	protected static $_pidsToRestart = array();
	protected static $_status = self::STATUS_STARTING;
	protected static $_maxWorkerNameLength = 12;
	protected static $_maxSocketNameLength = 12;
	protected static $_maxUserNameLength = 12;
	protected static $_statisticsFile = '';
	protected static $_startFile = '';
	protected static $_process = array();
	protected static $_startFiles = array();

	public static function runAll()
	{
		self::init();
		self::parseCommand();
		self::initWorkers();
		self::displayUI();
		self::runAllWorkers();
		self::monitorWorkers();
	}

	public static function init()
	{
		if (strpos(strtolower(PHP_OS), 'win') !== 0) {
			exit("workerman-for-win can not run in linux\n");
		}
		if (false !== strpos(ini_get('disable_functions'), 'proc_open')) {
			exit("\r\nWarning: proc_open() has been disabled for security reasons. \r\n\r\nSee http://wiki.workerman.net/Error5\r\n");
		}
		$backtrace = debug_backtrace();
		self::$_startFile = $backtrace[count($backtrace) - 1]['file'];
		if (empty(self::$logFile)) {
			self::$logFile = __DIR__ . '/../workerman.log';
		}
		self::$_status = self::STATUS_STARTING;
		self::$globalEvent = new Select();
		Timer::init(self::$globalEvent);
	}

	protected static function initWorkers()
	{
		foreach (self::$_workers as $worker) {
			if (empty($worker->name)) {
				$worker->name = 'none';
			}
			$worker_name_length = strlen($worker->name);
			if (self::$_maxWorkerNameLength < $worker_name_length) {
				self::$_maxWorkerNameLength = $worker_name_length;
			}
			$socket_name_length = strlen($worker->getSocketName());
			if (self::$_maxSocketNameLength < $socket_name_length) {
				self::$_maxSocketNameLength = $socket_name_length;
			}
			$user_name_length = strlen($worker->user);
			if (self::$_maxUserNameLength < $user_name_length) {
				self::$_maxUserNameLength = $user_name_length;
			}
		}
	}

	public static function runAllWorkers()
	{
		if (count(self::$_startFiles) === 1) {
			if (count(self::$_workers) > 1) {
				echo "@@@ Error: multi workers init in one php file are not support @@@\r\n";
				echo "@@@ Please visit http://wiki.workerman.net/Multi_woker_for_win @@@\r\n";
			} elseif (count(self::$_workers) <= 0) {
				exit("@@@no worker inited@@@\r\n\r\n");
			}
			reset(self::$_workers);
			$worker = current(self::$_workers);
			$worker->listen();
			$worker->run();
			exit("@@@child exit@@@\r\n");
		} elseif (count(self::$_startFiles) > 1) {
			foreach (self::$_startFiles as $start_file) {
				self::openProcess($start_file);
			}
		} else {
			echo "@@@no worker inited@@@\r\n";
		}
	}

	public static function openProcess($start_file)
	{
		$start_file = realpath($start_file);
		$std_file = sys_get_temp_dir() . '/' . str_replace(array('/', "\\", ':'), '_', $start_file) . '.out.txt';
		$descriptorspec = array(0 => array('pipe', 'a'), 1 => array('file', $std_file, 'w'), 2 => array('file', $std_file, 'w'));
		$pipes = array();
		$process = proc_open("php \"$start_file\" -q", $descriptorspec, $pipes);
		$std_handler = fopen($std_file, 'a+');
		stream_set_blocking($std_handler, 0);
		$timer_id = Timer::add(0.1, function () use ($std_handler) {
			echo fread($std_handler, 65535);
		});
		self::$_process[$start_file] = array($process, $start_file, $timer_id);
	}

	protected static function monitorWorkers()
	{
		Timer::add(0.5, "\\Workerman\\Worker::checkWorkerStatus");
		self::$globalEvent->loop();
	}

	public static function checkWorkerStatus()
	{
		foreach (self::$_process as $process_data) {
			$process = $process_data[0];
			$start_file = $process_data[1];
			$timer_id = $process_data[2];
			$status = proc_get_status($process);
			if (isset($status['running'])) {
				if (!$status['running']) {
					echo "process $start_file terminated and try to restart\n";
					Timer::del($timer_id);
					@proc_close($process);
					self::openProcess($start_file);
				}
			} else {
				echo "proc_get_status fail\n";
			}
		}
	}

	public static function getAllWorkers()
	{
		return self::$_workers;
	}

	public static function getEventLoop()
	{
		return self::$globalEvent;
	}

	protected static function displayUI()
	{
		global $argv;
		if (in_array('-q', $argv)) {
			return;
		}
		echo "----------------------- WORKERMAN -----------------------------\n";
		echo 'Workerman version:' . Worker::VERSION . "          PHP version:" . PHP_VERSION . "\n";
		echo "------------------------ WORKERS -------------------------------\n";
		echo "worker", str_pad('', self::$_maxWorkerNameLength + 2 - strlen('worker')), "listen", str_pad('', self::$_maxSocketNameLength + 2 - strlen('listen')), "processes ", "status\n";
		foreach (self::$_workers as $worker) {
			echo str_pad($worker->name, self::$_maxWorkerNameLength + 2), str_pad($worker->getSocketName(), self::$_maxSocketNameLength + 2), str_pad(' ' . $worker->count, 9), " [OK] \n";;
		}
		echo "----------------------------------------------------------------\n";
		echo "Press Ctrl-C to quit. Start success.\n";
	}

	public static function parseCommand()
	{
		global $argv;
		foreach ($argv as $file) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if ($ext !== 'php') {
				continue;
			}
			if (is_file($file)) {
				self::$_startFiles[$file] = $file;
				include_once $file;
			}
		}
	}

	public static function stopAll()
	{
		self::$_status = self::STATUS_SHUTDOWN;
		exit(0);
	}

	public static function log($msg)
	{
		$msg = $msg . "\n";
		if (self::$_status === self::STATUS_STARTING || !self::$daemonize) {
			echo $msg;
		}
		file_put_contents(self::$logFile, date('Y-m-d H:i:s') . " " . $msg, FILE_APPEND | LOCK_EX);
	}

	public function __construct($socket_name = '', $context_option = array())
	{
		$this->workerId = spl_object_hash($this);
		self::$_workers[$this->workerId] = $this;
		self::$_pidMap[$this->workerId] = array();
		$backrace = debug_backtrace();
		$this->_autoloadRootPath = dirname($backrace[0]['file']);
		if ($socket_name) {
			$this->_socketName = $socket_name;
			if (!isset($context_option['socket']['backlog'])) {
				$context_option['socket']['backlog'] = self::DEFAUL_BACKLOG;
			}
			$this->_context = stream_context_create($context_option);
		}
		$this->onMessage = function () {
		};
	}

	public function listen()
	{
		Autoloader::setRootPath($this->_autoloadRootPath);
		if (!$this->_socketName) {
			return;
		}
		list($scheme, $address) = explode(':', $this->_socketName, 2);
		if ($scheme != 'tcp' && $scheme != 'udp') {
			$scheme = ucfirst($scheme);
			$this->protocol = '\\Protocols\\' . $scheme;
			if (!class_exists($this->protocol)) {
				$this->protocol = "\\Workerman\\Protocols\\$scheme";
				if (!class_exists($this->protocol)) {
					throw new Exception("class \\Protocols\\$scheme not exist");
				}
			}
		} elseif ($scheme === 'udp') {
			$this->transport = 'udp';
		}
		$flags = $this->transport === 'udp' ? STREAM_SERVER_BIND : STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
		$this->_mainSocket = stream_socket_server($this->transport . ":" . $address, $errno, $errmsg, $flags, $this->_context);
		if (!$this->_mainSocket) {
			throw new Exception($errmsg);
		}
		if (function_exists('socket_import_stream')) {
			$socket = socket_import_stream($this->_mainSocket);
			@socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
			@socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
		}
		stream_set_blocking($this->_mainSocket, 0);
		if (self::$globalEvent) {
			if ($this->transport !== 'udp') {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptConnection'));
			} else {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptUdpConnection'));
			}
		}
	}

	public function getSocketName()
	{
		return $this->_socketName ? $this->_socketName : 'none';
	}

	public function run()
	{
		Autoloader::setRootPath($this->_autoloadRootPath);
		if (interface_exists('\React\EventLoop\LoopInterface')) {
			self::$globalEvent = new React();
		} elseif (extension_loaded('libevent')) {
			self::$globalEvent = new Libevent();
		} elseif (extension_loaded('event')) {
			self::$globalEvent = new Event();
		} else {
			self::$globalEvent = new Select();
		}
		if ($this->_socketName) {
			if ($this->transport !== 'udp') {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptConnection'));
			} else {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptUdpConnection'));
			}
		}
		Timer::init(self::$globalEvent);
		if ($this->onWorkerStart) {
			call_user_func($this->onWorkerStart, $this);
		}
		self::$globalEvent->loop();
	}

	public function stop()
	{
		if ($this->onWorkerStop) {
			call_user_func($this->onWorkerStop, $this);
		}
		self::$globalEvent->del($this->_mainSocket, EventInterface::EV_READ);
		@fclose($this->_mainSocket);
	}

	public function acceptConnection($socket)
	{
		$new_socket = @stream_socket_accept($socket, 0, $remote_address);
		if (!$new_socket) {
			return;
		}
		$connection = new TcpConnection($new_socket, $remote_address);
		$this->connections[$connection->id] = $connection;
		$connection->worker = $this;
		$connection->protocol = $this->protocol;
		$connection->onMessage = $this->onMessage;
		$connection->onClose = $this->onClose;
		$connection->onError = $this->onError;
		$connection->onBufferDrain = $this->onBufferDrain;
		$connection->onBufferFull = $this->onBufferFull;
		if ($this->onConnect) {
			try {
				call_user_func($this->onConnect, $connection);
			} catch (\Exception $e) {
				self::log($e);
				exit(250);
			} catch (\Error $e) {
				self::log($e);
				exit(250);
			}
		}
	}

	public function acceptUdpConnection($socket)
	{
		$recv_buffer = stream_socket_recvfrom($socket, self::MAX_UDP_PACKAGE_SIZE, 0, $remote_address);
		if (false === $recv_buffer || empty($remote_address)) {
			return false;
		}
		$connection = new UdpConnection($socket, $remote_address);
		$connection->protocol = $this->protocol;
		if ($this->onMessage) {
			if ($this->protocol) {
				$parser = $this->protocol;
				$recv_buffer = $parser::decode($recv_buffer, $connection);
			}
			ConnectionInterface::$statistics['total_request']++;
			try {
				call_user_func($this->onMessage, $connection, $recv_buffer);
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
} 