<?php
namespace Workerman\Events\React;

use Workerman\Events\EventInterface;

class StreamSelectLoop extends \React\EventLoop\StreamSelectLoop
{
	protected $_timerIdMap = array();
	protected $_timerIdIndex = 0;

	public function add($fd, $flag, $func, $args = array())
	{
		$args = (array)$args;
		switch ($flag) {
			case EventInterface::EV_READ:
				return $this->addReadStream($fd, $func);
			case EventInterface::EV_WRITE:
				return $this->addWriteStream($fd, $func);
			case EventInterface::EV_SIGNAL:
				return $this->addSignal($fd, $func);
			case EventInterface::EV_TIMER:
				$timer_obj = $this->addPeriodicTimer($fd, function () use ($func, $args) {
					call_user_func_array($func, $args);
				});
				$this->_timerIdMap[++$this->_timerIdIndex] = $timer_obj;
				return $this->_timerIdIndex;
			case EventInterface::EV_TIMER_ONCE:
				$index = ++$this->_timerIdIndex;
				$timer_obj = $this->addTimer($fd, function () use ($func, $args, $index) {
					$this->del($index, EventInterface::EV_TIMER_ONCE);
					call_user_func_array($func, $args);
				});
				$this->_timerIdMap[$index] = $timer_obj;
				return $this->_timerIdIndex;
		}
		return false;
	}

	public function del($fd, $flag)
	{
		switch ($flag) {
			case EventInterface::EV_READ:
				return $this->removeReadStream($fd);
			case EventInterface::EV_WRITE:
				return $this->removeWriteStream($fd);
			case EventInterface::EV_SIGNAL:
				return $this->removeSignal($fd);
			case EventInterface::EV_TIMER:
			case EventInterface::EV_TIMER_ONCE;
				if (isset($this->_timerIdMap[$fd])) {
					$timer_obj = $this->_timerIdMap[$fd];
					unset($this->_timerIdMap[$fd]);
					$this->cancelTimer($timer_obj);
					return true;
				}
		}
		return false;
	}

	public function loop()
	{
		$this->run();
	}

	public function addSignal($signal, $callback)
	{
		if (DIRECTORY_SEPARATOR === '/') {
			pcntl_signal($signal, $callback);
		}
	}

	public function removeSignal($signal)
	{
		if (DIRECTORY_SEPARATOR === '/') {
			pcntl_signal($signal, SIG_IGN);
		}
	}

	protected function streamSelect(array &$read, array &$write, $timeout)
	{
		if ($read || $write) {
			$except = null;
			if (DIRECTORY_SEPARATOR === '/') {
				pcntl_signal_dispatch();
			}
			return @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
		}
		if (DIRECTORY_SEPARATOR === '/') {
			pcntl_signal_dispatch();
		}
		$timeout && usleep($timeout);
		return 0;
	}

	public function destroy()
	{
	}

	public function getTimerCount()
	{
		return count($this->_timerIdMap);
	}
} 