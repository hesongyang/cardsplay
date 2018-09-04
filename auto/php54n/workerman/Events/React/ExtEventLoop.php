<?php
namespace Workerman\Events\React;

use Workerman\Events\EventInterface;

class ExtEventLoop extends \React\EventLoop\ExtEventLoop
{
	protected $_eventBase = null;
	protected $_signalEvents = array();
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
				$timer_id = ++$this->_timerIdIndex;
				$timer_obj = $this->addPeriodicTimer($fd, function () use ($func, $args) {
					call_user_func_array($func, $args);
				});
				$this->_timerIdMap[$timer_id] = $timer_obj;
				return $timer_id;
			case EventInterface::EV_TIMER_ONCE:
				$timer_id = ++$this->_timerIdIndex;
				$timer_obj = $this->addTimer($fd, function () use ($func, $args, $timer_id) {
					unset($this->_timerIdMap[$timer_id]);
					call_user_func_array($func, $args);
				});
				$this->_timerIdMap[$timer_id] = $timer_obj;
				return $timer_id;
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

	public function __construct()
	{
		parent::__construct();
		$class = new \ReflectionClass('\React\EventLoop\ExtEventLoop');
		$property = $class->getProperty('eventBase');
		$property->setAccessible(true);
		$this->_eventBase = $property->getValue($this);
	}

	public function addSignal($signal, $callback)
	{
		$event = \Event::signal($this->_eventBase, $signal, $callback);
		if (!$event || !$event->add()) {
			return false;
		}
		$this->_signalEvents[$signal] = $event;
	}

	public function removeSignal($signal)
	{
		if (isset($this->_signalEvents[$signal])) {
			$this->_signalEvents[$signal]->del();
			unset($this->_signalEvents[$signal]);
		}
	}

	public function destroy()
	{
		foreach ($this->_signalEvents as $event) {
			$event->del();
		}
	}

	public function getTimerCount()
	{
		return count($this->_timerIdMap);
	}
} 