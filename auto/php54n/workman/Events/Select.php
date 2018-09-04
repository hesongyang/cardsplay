<?php
namespace Workerman\Events;
class Select implements EventInterface
{
	public $_allEvents = array();
	public $_signalEvents = array();
	protected $_readFds = array();
	protected $_writeFds = array();
	protected $_exceptFds = array();
	protected $_scheduler = null;
	protected $_task = array();
	protected $_timerId = 1;
	protected $_selectTimeout = 100000000;

	public function __construct()
	{
		$this->channel = stream_socket_pair(STREAM_PF_INET, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		if ($this->channel) {
			stream_set_blocking($this->channel[0], 0);
			$this->_readFds[0] = $this->channel[0];
		}
		$this->_scheduler = new \SplPriorityQueue();
		$this->_scheduler->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
	}

	public function add($fd, $flag, $func, $args = array())
	{
		switch ($flag) {
			case self::EV_READ:
				$fd_key = (int)$fd;
				$this->_allEvents[$fd_key][$flag] = array($func, $fd);
				$this->_readFds[$fd_key] = $fd;
				break;
			case self::EV_WRITE:
				$fd_key = (int)$fd;
				$this->_allEvents[$fd_key][$flag] = array($func, $fd);
				$this->_writeFds[$fd_key] = $fd;
				break;
			case self::EV_EXCEPT:
				$fd_key = (int)$fd;
				$this->_allEvents[$fd_key][$flag] = array($func, $fd);
				$this->_exceptFds[$fd_key] = $fd;
				break;
			case self::EV_SIGNAL:
				throw new \Exception("not support EV_SIGNAL on Windows");
				break;
			case self::EV_TIMER:
			case self::EV_TIMER_ONCE:
				$run_time = microtime(true) + $fd;
				$this->_scheduler->insert($this->_timerId, -$run_time);
				$this->_task[$this->_timerId] = array($func, (array)$args, $flag, $fd);
				$this->tick();
				return $this->_timerId++;
		}
		return true;
	}

	public function signalHandler($signal)
	{
		call_user_func_array($this->_signalEvents[$signal][self::EV_SIGNAL][0], array($signal));
	}

	public function del($fd, $flag)
	{
		$fd_key = (int)$fd;
		switch ($flag) {
			case self::EV_READ:
				unset($this->_allEvents[$fd_key][$flag], $this->_readFds[$fd_key]);
				if (empty($this->_allEvents[$fd_key])) {
					unset($this->_allEvents[$fd_key]);
				}
				return true;
			case self::EV_WRITE:
				unset($this->_allEvents[$fd_key][$flag], $this->_writeFds[$fd_key]);
				if (empty($this->_allEvents[$fd_key])) {
					unset($this->_allEvents[$fd_key]);
				}
				return true;
			case self::EV_EXCEPT:
				unset($this->_allEvents[$fd_key][$flag], $this->_exceptFds[$fd_key]);
				if (empty($this->_allEvents[$fd_key])) {
					unset($this->_allEvents[$fd_key]);
				}
				return true;
			case self::EV_SIGNAL:
				break;
			case self::EV_TIMER:
			case self::EV_TIMER_ONCE;
				unset($this->_task[$fd_key]);
				return true;
		}
		return false;;
	}

	protected function tick()
	{
		while (!$this->_scheduler->isEmpty()) {
			$scheduler_data = $this->_scheduler->top();
			$timer_id = $scheduler_data['data'];
			$next_run_time = -$scheduler_data['priority'];
			$time_now = microtime(true);
			$this->_selectTimeout = ($next_run_time - $time_now) * 1000000;
			if ($this->_selectTimeout <= 0) {
				$this->_scheduler->extract();
				if (!isset($this->_task[$timer_id])) {
					continue;
				}
				$task_data = $this->_task[$timer_id];
				if ($task_data[2] === self::EV_TIMER) {
					$next_run_time = $time_now + $task_data[3];
					$this->_scheduler->insert($timer_id, -$next_run_time);
				}
				call_user_func_array($task_data[0], $task_data[1]);
				if (isset($this->_task[$timer_id]) && $task_data[2] === self::EV_TIMER_ONCE) {
					$this->del($timer_id, self::EV_TIMER_ONCE);
				}
				continue;
			}
			return;
		}
		$this->_selectTimeout = 100000000;
	}

	public function clearAllTimer()
	{
		$this->_scheduler = new \SplPriorityQueue();
		$this->_scheduler->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
		$this->_task = array();
	}

	public function loop()
	{
		$e = null;
		while (1) {
			$read = $this->_readFds;
			$write = $this->_writeFds;
			$except = $this->_writeFds;
			stream_select($read, $write, $except, 0, (int)($this->_selectTimeout . ''));
			if (!$this->_scheduler->isEmpty()) {
				$this->tick();
			}
			if ($read) {
				foreach ($read as $fd) {
					$fd_key = (int)$fd;
					if (isset($this->_allEvents[$fd_key][self::EV_READ])) {
						call_user_func_array($this->_allEvents[$fd_key][self::EV_READ][0], array($this->_allEvents[$fd_key][self::EV_READ][1]));
					}
				}
			}
			if ($write) {
				foreach ($write as $fd) {
					$fd_key = (int)$fd;
					if (isset($this->_allEvents[$fd_key][self::EV_WRITE])) {
						call_user_func_array($this->_allEvents[$fd_key][self::EV_WRITE][0], array($this->_allEvents[$fd_key][self::EV_WRITE][1]));
					}
				}
			}
			if ($except) {
				foreach ($except as $fd) {
					$fd_key = (int)$fd;
					if (isset($this->_allEvents[$fd_key][self::EV_EXCEPT])) {
						call_user_func_array($this->_allEvents[$fd_key][self::EV_EXCEPT][0], array($this->_allEvents[$fd_key][self::EV_EXCEPT][1]));
					}
				}
			}
		}
	}
} 