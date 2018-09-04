<?php
namespace Workerman;
require_once __DIR__ . '/Lib/Constants.php';
use Workerman\Events\EventInterface;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Connection\UdpConnection;
use Workerman\Lib\Timer;
use Exception;

class Worker
{
	const VERSION = '3.5.2';
	const STATUS_STARTING = 1;
	const STATUS_RUNNING = 2;
	const STATUS_SHUTDOWN = 4;
	const STATUS_RELOADING = 8;
	const KILL_WORKER_TIMER_TIME = 2;
	const DEFAULT_BACKLOG = 102400;
	const MAX_UDP_PACKAGE_SIZE = 65535;
	public $id = 0;
	public $name = 'none';
	public $count = 1;
	public $user = '';
	public $group = '';
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
	public $protocol = null;
	protected $_autoloadRootPath = '';
	protected $_pauseAccept = true;
	public static $daemonize = false;
	public static $stdoutFile = '/dev/null';
	public static $pidFile = '';
	public static $logFile = '';
	public static $globalEvent = null;
	public static $onMasterReload = null;
	public static $onMasterStop = null;
	public static $eventLoopClass = '';
	protected static $_masterPid = 0;
	protected $_mainSocket = null;
	protected $_socketName = '';
	protected $_context = null;
	protected static $_workers = array();
	protected static $_pidMap = array();
	protected static $_pidsToRestart = array();
	protected static $_idMap = array();
	protected static $_status = self::STATUS_STARTING;
	protected static $_maxWorkerNameLength = 12;
	protected static $_maxSocketNameLength = 12;
	protected static $_maxUserNameLength = 12;
	protected static $_statisticsFile = '';
	protected static $_startFile = '';
	protected static $_globalStatistics = array('start_timestamp' => 0, 'worker_exit_info' => array());
	protected static $_availableEventLoops = array('libevent' => '\Workerman\Events\Libevent', 'event' => '\Workerman\Events\Event');
	protected static $_builtinTransports = array('tcp' => 'tcp', 'udp' => 'udp', 'unix' => 'unix', 'ssl' => 'tcp');
	protected static $_gracefulStop = false;

	public static function runAll()
	{
		self::checkSapiEnv();
		self::init();
		self::parseCommand();
		self::daemonize();
		self::initWorkers();
		self::installSignal();
		self::saveMasterPid();
		self::displayUI();
		self::forkWorkers();
		self::resetStd();
		self::monitorWorkers();
	}

	protected static function checkSapiEnv()
	{
		if (php_sapi_name() != "cli") {
			exit("only run in command line mode \n");
		}
	}

	protected static function init()
	{
		$backtrace = debug_backtrace();
		self::$_startFile = $backtrace[count($backtrace) - 1]['file'];
		$unique_prefix = str_replace('/', '_', self::$_startFile);
		if (empty(self::$pidFile)) {
			self::$pidFile = __DIR__ . "/../$unique_prefix.pid";
		}
		if (empty(self::$logFile)) {
			self::$logFile = __DIR__ . '/../workerman.log';
		}
		$log_file = (string)self::$logFile;
		if (!is_file($log_file)) {
			touch($log_file);
			chmod($log_file, 0622);
		}
		self::$_status = self::STATUS_STARTING;
		self::$_globalStatistics['start_timestamp'] = time();
		self::$_statisticsFile = sys_get_temp_dir() . "/$unique_prefix.status";
		self::setProcessTitle('WorkerMan: master process  start_file=' . self::$_startFile);
		self::initId();
		Timer::init();
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
			if (empty($worker->user)) {
				$worker->user = self::getCurrentUser();
			} else {
				if (posix_getuid() !== 0 && $worker->user != self::getCurrentUser()) {
					self::log('Warning: You must have the root privileges to change uid and gid.');
				}
			}
			$user_name_length = strlen($worker->user);
			if (self::$_maxUserNameLength < $user_name_length) {
				self::$_maxUserNameLength = $user_name_length;
			}
			if (!$worker->reusePort) {
				$worker->listen();
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

	protected static function initId()
	{
		foreach (self::$_workers as $worker_id => $worker) {
			$new_id_map = array();
			for ($key = 0; $key < $worker->count; $key++) {
				$new_id_map[$key] = isset(self::$_idMap[$worker_id][$key]) ? self::$_idMap[$worker_id][$key] : 0;
			}
			self::$_idMap[$worker_id] = $new_id_map;
		}
	}

	protected static function getCurrentUser()
	{
		$user_info = posix_getpwuid(posix_getuid());
		return $user_info['name'];
	}

	protected static function displayUI()
	{
		self::safeEcho("\033[1A\n\033[K-----------------------\033[47;30m WORKERMAN \033[0m-----------------------------\n\033[0m");
		self::safeEcho('Workerman version:' . Worker::VERSION . "          PHP version:" . PHP_VERSION . "\n");
		self::safeEcho("------------------------\033[47;30m WORKERS \033[0m-------------------------------\n");
		self::safeEcho("\033[47;30muser\033[0m" . str_pad('', self::$_maxUserNameLength + 2 - strlen('user')) . "\033[47;30mworker\033[0m" . str_pad('', self::$_maxWorkerNameLength + 2 - strlen('worker')) . "\033[47;30mlisten\033[0m" . str_pad('', self::$_maxSocketNameLength + 2 - strlen('listen')) . "\033[47;30mprocesses\033[0m \033[47;30m" . "status\033[0m\n");
		foreach (self::$_workers as $worker) {
			self::safeEcho(str_pad($worker->user, self::$_maxUserNameLength + 2) . str_pad($worker->name, self::$_maxWorkerNameLength + 2) . str_pad($worker->getSocketName(), self::$_maxSocketNameLength + 2) . str_pad(' ' . $worker->count, 9) . " \033[32;40m [OK] \033[0m\n");
		}
		self::safeEcho("----------------------------------------------------------------\n");
		if (self::$daemonize) {
			global $argv;
			$start_file = $argv[0];
			self::safeEcho("Input \"php $start_file stop\" to quit. Start success.\n\n");
		} else {
			self::safeEcho("Press Ctrl+C to quit. Start success.\n");
		}
	}

	protected static function parseCommand()
	{
		global $argv;
		$start_file = $argv[0];
		$available_commands = array('start', 'stop', 'restart', 'reload', 'status', 'connections',);
		$usage = "Usage: php yourfile.php {" . implode('|', $available_commands) . "} [-d]\n";
		if (!isset($argv[1]) || !in_array($argv[1], $available_commands)) {
			exit($usage);
		}
		$command = trim($argv[1]);
		$command2 = isset($argv[2]) ? $argv[2] : '';
		$mode = '';
		if ($command === 'start') {
			if ($command2 === '-d' || Worker::$daemonize) {
				$mode = 'in DAEMON mode';
			} else {
				$mode = 'in DEBUG mode';
			}
		}
		self::log("Workerman[$start_file] $command $mode");
		$master_pid = is_file(self::$pidFile) ? file_get_contents(self::$pidFile) : 0;
		$master_is_alive = $master_pid && @posix_kill($master_pid, 0) && posix_getpid() != $master_pid;
		if ($master_is_alive) {
			if ($command === 'start') {
				self::log("Workerman[$start_file] already running");
				exit;
			}
		} elseif ($command !== 'start' && $command !== 'restart') {
			self::log("Workerman[$start_file] not run");
			exit;
		}
		switch ($command) {
			case 'start':
				if ($command2 === '-d') {
					Worker::$daemonize = true;
				}
				break;
			case 'status':
				while (1) {
					if (is_file(self::$_statisticsFile)) {
						@unlink(self::$_statisticsFile);
					}
					posix_kill($master_pid, SIGUSR2);
					sleep(1);
					if ($command2 === '-d') {
						echo "\33[H\33[2J\33(B\33[m";
					}
					echo self::formatStatusData();
					if ($command2 !== '-d') {
						exit(0);
					}
					echo "\nPress Ctrl+C to quit.\n\n";
				}
				exit(0);
			case 'connections':
				if (is_file(self::$_statisticsFile)) {
					@unlink(self::$_statisticsFile);
				}
				posix_kill($master_pid, SIGIO);
				usleep(500000);
				@readfile(self::$_statisticsFile);
				exit(0);
			case 'restart':
			case 'stop':
				if ($command2 === '-g') {
					self::$_gracefulStop = true;
					$sig = SIGTERM;
					self::log("Workerman[$start_file] is gracefully stoping ...");
				} else {
					self::$_gracefulStop = false;
					$sig = SIGINT;
					self::log("Workerman[$start_file] is stoping ...");
				}
				$master_pid && posix_kill($master_pid, $sig);
				$timeout = 5;
				$start_time = time();
				while (1) {
					$master_is_alive = $master_pid && posix_kill($master_pid, 0);
					if ($master_is_alive) {
						if (!self::$_gracefulStop && time() - $start_time >= $timeout) {
							self::log("Workerman[$start_file] stop fail");
							exit;
						}
						usleep(10000);
						continue;
					}
					self::log("Workerman[$start_file] stop success");
					if ($command === 'stop') {
						exit(0);
					}
					if ($command2 === '-d') {
						Worker::$daemonize = true;
					}
					break;
				}
				break;
			case 'reload':
				if ($command2 === '-g') {
					$sig = SIGQUIT;
				} else {
					$sig = SIGUSR1;
				}
				posix_kill($master_pid, $sig);
				exit;
			default :
				exit($usage);
		}
	}

	protected static function formatStatusData()
	{
		static $total_request_cache = array();
		$info = @file(self::$_statisticsFile, FILE_IGNORE_NEW_LINES);
		if (!$info) {
			return '';
		}
		$status_str = '';
		$current_total_request = array();
		$worker_info = json_decode($info[0], true);
		ksort($worker_info, SORT_NUMERIC);
		unset($info[0]);
		$data_waiting_sort = array();
		$read_process_status = false;
		foreach ($info as $key => $value) {
			if (!$read_process_status) {
				$status_str .= $value . "\n";
				if (preg_match('/^pid.*?memory.*?listening/', $value)) {
					$read_process_status = true;
				}
				continue;
			}
			if (preg_match('/^[0-9]+/', $value, $pid_math)) {
				$pid = $pid_math[0];
				$data_waiting_sort[$pid] = $value;
				if (preg_match('/^\S+?\s+?\S+?\s+?\S+?\s+?\S+?\s+?\S+?\s+?\S+?\s+?\S+?\s+?(\S+?)\s+?/', $value, $match)) {
					$current_total_request[$pid] = $match[1];
				}
			}
		}
		foreach ($worker_info as $pid => $info) {
			if (!isset($data_waiting_sort[$pid])) {
				$status_str .= "$pid\t" . str_pad('N/A', 7) . " " . str_pad($info['listen'], self::$_maxSocketNameLength) . " " . str_pad($info['name'], self::$_maxWorkerNameLength) . " " . str_pad('N/A', 11) . " " . str_pad('N/A', 9) . " " . str_pad('N/A', 7) . " " . str_pad('N/A', 13) . " N/A    [busy] \n";
				continue;
			}
			if (!isset($total_request_cache[$pid]) || !isset($current_total_request[$pid])) {
				$qps = 0;
			} else {
				$qps = $current_total_request[$pid] - $total_request_cache[$pid];
			}
			$status_str .= $data_waiting_sort[$pid] . " " . str_pad($qps, 6) . " [idle]\n";
		}
		$total_request_cache = $current_total_request;
		return $status_str;
	}

	protected static function installSignal()
	{
		pcntl_signal(SIGINT, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGTERM, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGUSR1, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGQUIT, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGUSR2, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGIO, array('\Workerman\Worker', 'signalHandler'), false);
		pcntl_signal(SIGPIPE, SIG_IGN, false);
	}

	protected static function reinstallSignal()
	{
		pcntl_signal(SIGINT, SIG_IGN, false);
		pcntl_signal(SIGTERM, SIG_IGN, false);
		pcntl_signal(SIGUSR1, SIG_IGN, false);
		pcntl_signal(SIGQUIT, SIG_IGN, false);
		pcntl_signal(SIGUSR2, SIG_IGN, false);
		self::$globalEvent->add(SIGINT, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
		self::$globalEvent->add(SIGTERM, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
		self::$globalEvent->add(SIGUSR1, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
		self::$globalEvent->add(SIGQUIT, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
		self::$globalEvent->add(SIGUSR2, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
		self::$globalEvent->add(SIGIO, EventInterface::EV_SIGNAL, array('\Workerman\Worker', 'signalHandler'));
	}

	public static function signalHandler($signal)
	{
		switch ($signal) {
			case SIGINT:
				self::$_gracefulStop = false;
				self::stopAll();
				break;
			case SIGTERM:
				self::$_gracefulStop = true;
				self::stopAll();
				break;
			case SIGQUIT:
			case SIGUSR1:
				if ($signal === SIGQUIT) {
					self::$_gracefulStop = true;
				} else {
					self::$_gracefulStop = false;
				}
				self::$_pidsToRestart = self::getAllWorkerPids();
				self::reload();
				break;
			case SIGUSR2:
				self::writeStatisticsToStatusFile();
				break;
			case SIGIO:
				self::writeConnectionsStatisticsToStatusFile();
				break;
		}
	}

	protected static function daemonize()
	{
		if (!self::$daemonize) {
			return;
		}
		umask(0);
		$pid = pcntl_fork();
		if (-1 === $pid) {
			throw new Exception('fork fail');
		} elseif ($pid > 0) {
			exit(0);
		}
		if (-1 === posix_setsid()) {
			throw new Exception("setsid fail");
		}
		$pid = pcntl_fork();
		if (-1 === $pid) {
			throw new Exception("fork fail");
		} elseif (0 !== $pid) {
			exit(0);
		}
	}

	public static function resetStd()
	{
		if (!self::$daemonize) {
			return;
		}
		global $STDOUT, $STDERR;
		$handle = fopen(self::$stdoutFile, "a");
		if ($handle) {
			unset($handle);
			@fclose(STDOUT);
			@fclose(STDERR);
			$STDOUT = fopen(self::$stdoutFile, "a");
			$STDERR = fopen(self::$stdoutFile, "a");
		} else {
			throw new Exception('can not open stdoutFile ' . self::$stdoutFile);
		}
	}

	protected static function saveMasterPid()
	{
		self::$_masterPid = posix_getpid();
		if (false === @file_put_contents(self::$pidFile, self::$_masterPid)) {
			throw new Exception('can not save pid to ' . self::$pidFile);
		}
	}

	protected static function getEventLoopName()
	{
		if (self::$eventLoopClass) {
			return self::$eventLoopClass;
		}
		$loop_name = '';
		foreach (self::$_availableEventLoops as $name => $class) {
			if (extension_loaded($name)) {
				$loop_name = $name;
				break;
			}
		}
		if ($loop_name) {
			if (interface_exists('\React\EventLoop\LoopInterface')) {
				switch ($loop_name) {
					case 'libevent':
						self::$eventLoopClass = '\Workerman\Events\React\LibEventLoop';
						break;
					case 'event':
						self::$eventLoopClass = '\Workerman\Events\React\ExtEventLoop';
						break;
					default :
						self::$eventLoopClass = '\Workerman\Events\React\StreamSelectLoop';
						break;
				}
			} else {
				self::$eventLoopClass = self::$_availableEventLoops[$loop_name];
			}
		} else {
			self::$eventLoopClass = interface_exists('\React\EventLoop\LoopInterface') ? '\Workerman\Events\React\StreamSelectLoop' : '\Workerman\Events\Select';
		}
		return self::$eventLoopClass;
	}

	protected static function getAllWorkerPids()
	{
		$pid_array = array();
		foreach (self::$_pidMap as $worker_pid_array) {
			foreach ($worker_pid_array as $worker_pid) {
				$pid_array[$worker_pid] = $worker_pid;
			}
		}
		return $pid_array;
	}

	protected static function forkWorkers()
	{
		foreach (self::$_workers as $worker) {
			if (self::$_status === self::STATUS_STARTING) {
				if (empty($worker->name)) {
					$worker->name = $worker->getSocketName();
				}
				$worker_name_length = strlen($worker->name);
				if (self::$_maxWorkerNameLength < $worker_name_length) {
					self::$_maxWorkerNameLength = $worker_name_length;
				}
			}
			$worker->count = $worker->count <= 0 ? 1 : $worker->count;
			while (count(self::$_pidMap[$worker->workerId]) < $worker->count) {
				static::forkOneWorker($worker);
			}
		}
	}

	protected static function forkOneWorker($worker)
	{
		$id = self::getId($worker->workerId, 0);
		if ($id === false) {
			return;
		}
		$pid = pcntl_fork();
		if ($pid > 0) {
			self::$_pidMap[$worker->workerId][$pid] = $pid;
			self::$_idMap[$worker->workerId][$id] = $pid;
		} elseif (0 === $pid) {
			if ($worker->reusePort) {
				$worker->listen();
			}
			if (self::$_status === self::STATUS_STARTING) {
				self::resetStd();
			}
			self::$_pidMap = array();
			self::$_workers = array($worker->workerId => $worker);
			Timer::delAll();
			self::setProcessTitle('WorkerMan: worker process  ' . $worker->name . ' ' . $worker->getSocketName());
			$worker->setUserAndGroup();
			$worker->id = $id;
			$worker->run();
			$err = new Exception('event-loop exited');
			self::log($err);
			exit(250);
		} else {
			throw new Exception("forkOneWorker fail");
		}
	}

	protected static function getId($worker_id, $pid)
	{
		return array_search($pid, self::$_idMap[$worker_id]);
	}

	public function setUserAndGroup()
	{
		$user_info = posix_getpwnam($this->user);
		if (!$user_info) {
			self::log("Warning: User {$this->user} not exsits");
			return;
		}
		$uid = $user_info['uid'];
		if ($this->group) {
			$group_info = posix_getgrnam($this->group);
			if (!$group_info) {
				self::log("Warning: Group {$this->group} not exsits");
				return;
			}
			$gid = $group_info['gid'];
		} else {
			$gid = $user_info['gid'];
		}
		if ($uid != posix_getuid() || $gid != posix_getgid()) {
			if (!posix_setgid($gid) || !posix_initgroups($user_info['name'], $gid) || !posix_setuid($uid)) {
				self::log("Warning: change gid or uid fail.");
			}
		}
	}

	protected static function setProcessTitle($title)
	{
		if (function_exists('cli_set_process_title')) {
			@cli_set_process_title($title);
		} elseif (extension_loaded('proctitle') && function_exists('setproctitle')) {
			@setproctitle($title);
		}
	}

	protected static function monitorWorkers()
	{
		self::$_status = self::STATUS_RUNNING;
		while (1) {
			pcntl_signal_dispatch();
			$status = 0;
			$pid = pcntl_wait($status, WUNTRACED);
			pcntl_signal_dispatch();
			if ($pid > 0) {
				foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
					if (isset($worker_pid_array[$pid])) {
						$worker = self::$_workers[$worker_id];
						if ($status !== 0) {
							self::log("worker[" . $worker->name . ":$pid] exit with status $status");
						}
						if (!isset(self::$_globalStatistics['worker_exit_info'][$worker_id][$status])) {
							self::$_globalStatistics['worker_exit_info'][$worker_id][$status] = 0;
						}
						self::$_globalStatistics['worker_exit_info'][$worker_id][$status]++;
						unset(self::$_pidMap[$worker_id][$pid]);
						$id = self::getId($worker_id, $pid);
						self::$_idMap[$worker_id][$id] = 0;
						break;
					}
				}
				if (self::$_status !== self::STATUS_SHUTDOWN) {
					self::forkWorkers();
					if (isset(self::$_pidsToRestart[$pid])) {
						unset(self::$_pidsToRestart[$pid]);
						self::reload();
					}
				} else {
					if (!self::getAllWorkerPids()) {
						self::exitAndClearAll();
					}
				}
			} else {
				if (self::$_status === self::STATUS_SHUTDOWN && !self::getAllWorkerPids()) {
					self::exitAndClearAll();
				}
			}
		}
	}

	protected static function exitAndClearAll()
	{
		foreach (self::$_workers as $worker) {
			$socket_name = $worker->getSocketName();
			if ($worker->transport === 'unix' && $socket_name) {
				list(, $address) = explode(':', $socket_name, 2);
				@unlink($address);
			}
		}
		@unlink(self::$pidFile);
		self::log("Workerman[" . basename(self::$_startFile) . "] has been stopped");
		if (self::$onMasterStop) {
			call_user_func(self::$onMasterStop);
		}
		exit(0);
	}

	protected static function reload()
	{
		if (self::$_masterPid === posix_getpid()) {
			if (self::$_status !== self::STATUS_RELOADING && self::$_status !== self::STATUS_SHUTDOWN) {
				self::log("Workerman[" . basename(self::$_startFile) . "] reloading");
				self::$_status = self::STATUS_RELOADING;
				if (self::$onMasterReload) {
					try {
						call_user_func(self::$onMasterReload);
					} catch (\Exception $e) {
						self::log($e);
						exit(250);
					} catch (\Error $e) {
						self::log($e);
						exit(250);
					}
					self::initId();
				}
			}
			if (self::$_gracefulStop) {
				$sig = SIGQUIT;
			} else {
				$sig = SIGUSR1;
			}
			$reloadable_pid_array = array();
			foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
				$worker = self::$_workers[$worker_id];
				if ($worker->reloadable) {
					foreach ($worker_pid_array as $pid) {
						$reloadable_pid_array[$pid] = $pid;
					}
				} else {
					foreach ($worker_pid_array as $pid) {
						posix_kill($pid, $sig);
					}
				}
			}
			self::$_pidsToRestart = array_intersect(self::$_pidsToRestart, $reloadable_pid_array);
			if (empty(self::$_pidsToRestart)) {
				if (self::$_status !== self::STATUS_SHUTDOWN) {
					self::$_status = self::STATUS_RUNNING;
				}
				return;
			}
			$one_worker_pid = current(self::$_pidsToRestart);
			posix_kill($one_worker_pid, $sig);
			if (!self::$_gracefulStop) {
				Timer::add(self::KILL_WORKER_TIMER_TIME, 'posix_kill', array($one_worker_pid, SIGKILL), false);
			}
		} else {
			reset(self::$_workers);
			$worker = current(self::$_workers);
			if ($worker->onWorkerReload) {
				try {
					call_user_func($worker->onWorkerReload, $worker);
				} catch (\Exception $e) {
					self::log($e);
					exit(250);
				} catch (\Error $e) {
					self::log($e);
					exit(250);
				}
			}
			if ($worker->reloadable) {
				self::stopAll();
			}
		}
	}

	public static function stopAll()
	{
		self::$_status = self::STATUS_SHUTDOWN;
		if (self::$_masterPid === posix_getpid()) {
			self::log("Workerman[" . basename(self::$_startFile) . "] stopping ...");
			$worker_pid_array = self::getAllWorkerPids();
			if (self::$_gracefulStop) {
				$sig = SIGTERM;
			} else {
				$sig = SIGINT;
			}
			foreach ($worker_pid_array as $worker_pid) {
				posix_kill($worker_pid, $sig);
				if (!self::$_gracefulStop) {
					Timer::add(self::KILL_WORKER_TIMER_TIME, 'posix_kill', array($worker_pid, SIGKILL), false);
				}
			}
			if (is_file(self::$_statisticsFile)) {
				@unlink(self::$_statisticsFile);
			}
		} else {
			foreach (self::$_workers as $worker) {
				$worker->stop();
			}
			if (!self::$_gracefulStop || ConnectionInterface::$statistics['connection_count'] <= 0) {
				self::$globalEvent->destroy();
				exit(0);
			}
		}
	}

	public static function getStatus()
	{
		return self::$_status;
	}

	public static function getGracefulStop()
	{
		return self::$_gracefulStop;
	}

	protected static function writeStatisticsToStatusFile()
	{
		if (self::$_masterPid === posix_getpid()) {
			$all_worker_info = array();
			foreach (self::$_pidMap as $worker_id => $pid_array) {
				$worker = self::$_workers[$worker_id];
				foreach ($pid_array as $pid) {
					$all_worker_info[$pid] = array('name' => $worker->name, 'listen' => $worker->getSocketName());
				}
			}
			file_put_contents(self::$_statisticsFile, json_encode($all_worker_info) . "\n", FILE_APPEND);
			$loadavg = function_exists('sys_getloadavg') ? array_map('round', sys_getloadavg(), array(2)) : array('-', '-', '-');
			file_put_contents(self::$_statisticsFile, "----------------------------------------------GLOBAL STATUS----------------------------------------------------\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, 'Workerman version:' . Worker::VERSION . "          PHP version:" . PHP_VERSION . "\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, 'start time:' . date('Y-m-d H:i:s', self::$_globalStatistics['start_timestamp']) . '   run ' . floor((time() - self::$_globalStatistics['start_timestamp']) / (24 * 60 * 60)) . ' days ' . floor(((time() - self::$_globalStatistics['start_timestamp']) % (24 * 60 * 60)) / (60 * 60)) . " hours   \n", FILE_APPEND);
			$load_str = 'load average: ' . implode(", ", $loadavg);
			file_put_contents(self::$_statisticsFile, str_pad($load_str, 33) . 'event-loop:' . self::getEventLoopName() . "\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, count(self::$_pidMap) . ' workers       ' . count(self::getAllWorkerPids()) . " processes\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, str_pad('worker_name', self::$_maxWorkerNameLength) . " exit_status      exit_count\n", FILE_APPEND);
			foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
				$worker = self::$_workers[$worker_id];
				if (isset(self::$_globalStatistics['worker_exit_info'][$worker_id])) {
					foreach (self::$_globalStatistics['worker_exit_info'][$worker_id] as $worker_exit_status => $worker_exit_count) {
						file_put_contents(self::$_statisticsFile, str_pad($worker->name, self::$_maxWorkerNameLength) . " " . str_pad($worker_exit_status, 16) . " $worker_exit_count\n", FILE_APPEND);
					}
				} else {
					file_put_contents(self::$_statisticsFile, str_pad($worker->name, self::$_maxWorkerNameLength) . " " . str_pad(0, 16) . " 0\n", FILE_APPEND);
				}
			}
			file_put_contents(self::$_statisticsFile, "----------------------------------------------PROCESS STATUS---------------------------------------------------\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, "pid\tmemory  " . str_pad('listening', self::$_maxSocketNameLength) . " " . str_pad('worker_name', self::$_maxWorkerNameLength) . " connections " . str_pad('send_fail', 9) . " " . str_pad('timers', 8) . str_pad('total_request', 13) . " qps    status\n", FILE_APPEND);
			chmod(self::$_statisticsFile, 0722);
			foreach (self::getAllWorkerPids() as $worker_pid) {
				posix_kill($worker_pid, SIGUSR2);
			}
			return;
		}
		$worker = current(self::$_workers);
		$worker_status_str = posix_getpid() . "\t" . str_pad(round(memory_get_usage(true) / (1024 * 1024), 2) . "M", 7) . " " . str_pad($worker->getSocketName(), self::$_maxSocketNameLength) . " " . str_pad(($worker->name === $worker->getSocketName() ? 'none' : $worker->name), self::$_maxWorkerNameLength) . " ";
		$worker_status_str .= str_pad(ConnectionInterface::$statistics['connection_count'], 11) . " " . str_pad(ConnectionInterface::$statistics['send_fail'], 9) . " " . str_pad(self::$globalEvent->getTimerCount(), 7) . " " . str_pad(ConnectionInterface::$statistics['total_request'], 13) . "\n";
		file_put_contents(self::$_statisticsFile, $worker_status_str, FILE_APPEND);
	}

	protected static function writeConnectionsStatisticsToStatusFile()
	{
		if (self::$_masterPid === posix_getpid()) {
			file_put_contents(self::$_statisticsFile, "--------------------------------------------------------------------- WORKERMAN CONNECTION STATUS --------------------------------------------------------------------------------\n", FILE_APPEND);
			file_put_contents(self::$_statisticsFile, "PID      Worker          CID       Trans   Protocol        ipv4   ipv6   Recv-Q       Send-Q       Bytes-R      Bytes-W       Status         Local Address          Foreign Address\n", FILE_APPEND);
			chmod(self::$_statisticsFile, 0722);
			foreach (self::getAllWorkerPids() as $worker_pid) {
				posix_kill($worker_pid, SIGIO);
			}
			return;
		}
		$bytes_format = function ($bytes) {
			if ($bytes > 1024 * 1024 * 1024 * 1024) {
				return round($bytes / (1024 * 1024 * 1024 * 1024), 1) . "TB";
			}
			if ($bytes > 1024 * 1024 * 1024) {
				return round($bytes / (1024 * 1024 * 1024), 1) . "GB";
			}
			if ($bytes > 1024 * 1024) {
				return round($bytes / (1024 * 1024), 1) . "MB";
			}
			if ($bytes > 1024) {
				return round($bytes / (1024), 1) . "KB";
			}
			return $bytes . "B";
		};
		$pid = posix_getpid();
		$str = '';
		reset(self::$_workers);
		$current_worker = current(self::$_workers);
		$default_worker_name = $current_worker->name;
		foreach (TcpConnection::$connections as $connection) {
			$transport = $connection->transport;
			$ipv4 = $connection->isIpV4() ? ' 1' : ' 0';
			$ipv6 = $connection->isIpV6() ? ' 1' : ' 0';
			$recv_q = $bytes_format($connection->getRecvBufferQueueSize());
			$send_q = $bytes_format($connection->getSendBufferQueueSize());
			$local_address = trim($connection->getLocalAddress());
			$remote_address = trim($connection->getRemoteAddress());
			$state = $connection->getStatus(false);
			$bytes_read = $bytes_format($connection->bytesRead);
			$bytes_written = $bytes_format($connection->bytesWritten);
			$id = $connection->id;
			$protocol = $connection->protocol ? $connection->protocol : $connection->transport;
			$pos = strrpos($protocol, '\\');
			if ($pos) {
				$protocol = substr($protocol, $pos + 1);
			}
			if (strlen($protocol) > 15) {
				$protocol = substr($protocol, 0, 13) . '..';
			}
			$worker_name = isset($connection->worker) ? $connection->worker->name : $default_worker_name;
			if (strlen($worker_name) > 14) {
				$worker_name = substr($worker_name, 0, 12) . '..';
			}
			$str .= str_pad($pid, 9) . str_pad($worker_name, 16) . str_pad($id, 10) . str_pad($transport, 8) . str_pad($protocol, 16) . str_pad($ipv4, 7) . str_pad($ipv6, 7) . str_pad($recv_q, 13) . str_pad($send_q, 13) . str_pad($bytes_read, 13) . str_pad($bytes_written, 13) . ' ' . str_pad($state, 14) . ' ' . str_pad($local_address, 22) . ' ' . str_pad($remote_address, 22) . "\n";
		}
		if ($str) {
			file_put_contents(self::$_statisticsFile, $str, FILE_APPEND);
		}
	}

	public static function checkErrors()
	{
		if (self::STATUS_SHUTDOWN != self::$_status) {
			$error_msg = 'Worker[' . posix_getpid() . '] process terminated';
			$errors = error_get_last();
			if ($errors && ($errors['type'] === E_ERROR || $errors['type'] === E_PARSE || $errors['type'] === E_CORE_ERROR || $errors['type'] === E_COMPILE_ERROR || $errors['type'] === E_RECOVERABLE_ERROR)) {
				$error_msg .= ' with ERROR: ' . self::getErrorType($errors['type']) . " \"{$errors['message']} in {$errors['file']} on line {$errors['line']}\"";
			}
			self::log($error_msg);
		}
	}

	protected static function getErrorType($type)
	{
		switch ($type) {
			case E_ERROR:
				return 'E_ERROR';
			case E_WARNING:
				return 'E_WARNING';
			case E_PARSE:
				return 'E_PARSE';
			case E_NOTICE:
				return 'E_NOTICE';
			case E_CORE_ERROR:
				return 'E_CORE_ERROR';
			case E_CORE_WARNING:
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR:
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING:
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR:
				return 'E_USER_ERROR';
			case E_USER_WARNING:
				return 'E_USER_WARNING';
			case E_USER_NOTICE:
				return 'E_USER_NOTICE';
			case E_STRICT:
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR:
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED:
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED:
				return 'E_USER_DEPRECATED';
		}
		return "";
	}

	public static function log($msg)
	{
		$msg = $msg . "\n";
		if (!self::$daemonize) {
			self::safeEcho($msg);
		}
		file_put_contents((string)self::$logFile, date('Y-m-d H:i:s') . ' ' . 'pid:' . posix_getpid() . ' ' . $msg, FILE_APPEND | LOCK_EX);
	}

	public static function safeEcho($msg)
	{
		if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
			echo $msg;
		}
	}

	public function __construct($socket_name = '', $context_option = array())
	{
		$this->workerId = spl_object_hash($this);
		self::$_workers[$this->workerId] = $this;
		self::$_pidMap[$this->workerId] = array();
		$backtrace = debug_backtrace();
		$this->_autoloadRootPath = dirname($backtrace[0]['file']);
		if ($socket_name) {
			$this->_socketName = $socket_name;
			if (!isset($context_option['socket']['backlog'])) {
				$context_option['socket']['backlog'] = self::DEFAULT_BACKLOG;
			}
			$this->_context = stream_context_create($context_option);
		}
	}

	public function listen()
	{
		if (!$this->_socketName) {
			return;
		}
		Autoloader::setRootPath($this->_autoloadRootPath);
		if (!$this->_mainSocket) {
			list($scheme, $address) = explode(':', $this->_socketName, 2);
			if (!isset(self::$_builtinTransports[$scheme])) {
				$scheme = ucfirst($scheme);
				$this->protocol = '\\Protocols\\' . $scheme;
				if (!class_exists($this->protocol)) {
					$this->protocol = "\\Workerman\\Protocols\\$scheme";
					if (!class_exists($this->protocol)) {
						throw new Exception("class \\Protocols\\$scheme not exist");
					}
				}
				if (!isset(self::$_builtinTransports[$this->transport])) {
					throw new \Exception('Bad worker->transport ' . var_export($this->transport, true));
				}
			} else {
				$this->transport = $scheme;
			}
			$local_socket = self::$_builtinTransports[$this->transport] . ":" . $address;
			$flags = $this->transport === 'udp' ? STREAM_SERVER_BIND : STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
			$errno = 0;
			$errmsg = '';
			if ($this->reusePort) {
				stream_context_set_option($this->_context, 'socket', 'so_reuseport', 1);
			}
			$this->_mainSocket = stream_socket_server($local_socket, $errno, $errmsg, $flags, $this->_context);
			if (!$this->_mainSocket) {
				throw new Exception($errmsg);
			}
			if ($this->transport === 'ssl') {
				stream_socket_enable_crypto($this->_mainSocket, false);
			} elseif ($this->transport === 'unix') {
				$socketFile = substr($address, 2);
				if ($this->user) {
					chown($socketFile, $this->user);
				}
				if ($this->group) {
					chgrp($socketFile, $this->group);
				}
			}
			if (function_exists('socket_import_stream') && self::$_builtinTransports[$this->transport] === 'tcp') {
				$socket = socket_import_stream($this->_mainSocket);
				@socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
				@socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
			}
			stream_set_blocking($this->_mainSocket, 0);
		}
		$this->resumeAccept();
	}

	public function unlisten()
	{
		$this->pauseAccept();
		if ($this->_mainSocket) {
			@fclose($this->_mainSocket);
			$this->_mainSocket = null;
		}
	}

	public function pauseAccept()
	{
		if (self::$globalEvent && false === $this->_pauseAccept && $this->_mainSocket) {
			self::$globalEvent->del($this->_mainSocket, EventInterface::EV_READ);
			$this->_pauseAccept = true;
		}
	}

	public function resumeAccept()
	{
		if (self::$globalEvent && true === $this->_pauseAccept && $this->_mainSocket) {
			if ($this->transport !== 'udp') {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptConnection'));
			} else {
				self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, array($this, 'acceptUdpConnection'));
			}
			$this->_pauseAccept = false;
		}
	}

	public function getSocketName()
	{
		return $this->_socketName ? lcfirst($this->_socketName) : 'none';
	}

	public function run()
	{
		self::$_status = self::STATUS_RUNNING;
		register_shutdown_function(array("\\Workerman\\Worker", 'checkErrors'));
		Autoloader::setRootPath($this->_autoloadRootPath);
		if (!self::$globalEvent) {
			$event_loop_class = self::getEventLoopName();
			self::$globalEvent = new $event_loop_class;
			$this->resumeAccept();
		}
		self::reinstallSignal();
		Timer::init(self::$globalEvent);
		if (empty($this->onMessage)) {
			$this->onMessage = function () {
			};
		}
		if ($this->onWorkerStart) {
			try {
				call_user_func($this->onWorkerStart, $this);
			} catch (\Exception $e) {
				self::log($e);
				sleep(1);
				exit(250);
			} catch (\Error $e) {
				self::log($e);
				sleep(1);
				exit(250);
			}
		}
		self::$globalEvent->loop();
	}

	public function stop()
	{
		if ($this->onWorkerStop) {
			try {
				call_user_func($this->onWorkerStop, $this);
			} catch (\Exception $e) {
				self::log($e);
				exit(250);
			} catch (\Error $e) {
				self::log($e);
				exit(250);
			}
		}
		$this->unlisten();
		if (!self::$_gracefulStop) {
			foreach ($this->connections as $connection) {
				$connection->close();
			}
		}
		$this->onMessage = $this->onClose = $this->onError = $this->onBufferDrain = $this->onBufferFull = null;
		unset(self::$_workers[$this->workerId]);
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
		$connection->transport = $this->transport;
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
			if ($this->protocol !== null) {
				$parser = $this->protocol;
				$recv_buffer = $parser::decode($recv_buffer, $connection);
				if ($recv_buffer === false) return true;
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