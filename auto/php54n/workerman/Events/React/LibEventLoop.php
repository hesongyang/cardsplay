<?php
namespace Workerman\Events\React;

use Workerman\Events\EventInterface;

class LibEventLoop extends \React\EventLoop\LibEventLoop
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
		$class = new \ReflectionClass('\React\EventLoop\LibEventLoop');
		$property = $class->getProperty('eventBase');
		$property->setAccessible(true);
		$this->_eventBase = $property->getValue($this);
	}

	public function addSignal($signal, $callback)
	{
		$event = event_new();
		$this->_signalEvents[$signal] = $event;
		event_set($event, $signal, EV_SIGNAL | EV_PERSIST, $callback);
		event_base_set($event, $this->_eventBase);
		event_add($event);
	}

	public function removeSignal($signal)
	{
		if (isset($this->_signalEvents[$signal])) {
			$event = $this->_signalEvents[$signal];
			event_del($event);
			unset($this->_signalEvents[$signal]);
		}
	}

	public function destroy()
	{
		foreach ($this->_signalEvents as $event) {
			event_del($event);
		}
	}

	public function getTimerCount()
	{
		return count($this->_timerIdMap);
	}
} 