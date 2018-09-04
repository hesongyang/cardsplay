<?php
namespace Workerman\Events;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class React implements LoopInterface
{
	protected $_loop = null;

	public function __construct()
	{
		if (function_exists('event_base_new')) {
			$this->_loop = new \Workerman\Events\React\LibEventLoop();
		} elseif (class_exists('EventBase', false)) {
			$this->_loop = new \Workerman\Events\React\ExtEventLoop();
		} else {
			$this->_loop = new \Workerman\Events\React\StreamSelectLoop();
		}
	}

	public function add($fd, $flag, $func, $args = array())
	{
		switch ($flag) {
			case EventInterface::EV_READ:
				return $this->_loop->addReadStream($fd, $func);
			case EventInterface::EV_WRITE:
				return $this->_loop->addWriteStream($fd, $func);
			case EventInterface::EV_SIGNAL:
				return $this->_loop->addSignal($fd, $func);
			case EventInterface::EV_TIMER:
				return $this->_loop->addPeriodicTimer($fd, $func);
			case EventInterface::EV_TIMER_ONCE:
				return $this->_loop->addTimer($fd, $func);
		}
		return false;
	}

	public function del($fd, $flag)
	{
		switch ($flag) {
			case EventInterface::EV_READ:
				return $this->_loop->removeReadStream($fd);
			case EventInterface::EV_WRITE:
				return $this->_loop->removeWriteStream($fd);
			case EventInterface::EV_SIGNAL:
				return $this->_loop->removeSignal($fd);
			case EventInterface::EV_TIMER:
			case EventInterface::EV_TIMER_ONCE;
				return $this->_loop->cancelTimer($fd);
		}
		return false;
	}

	public function loop()
	{
		$this->_loop->run();
	}

	public function addReadStream($stream, callable $listener)
	{
		return call_user_func(array($this->_loop, 'addReadStream'), $stream, $listener);
	}

	public function addWriteStream($stream, callable $listener)
	{
		return call_user_func(array($this->_loop, 'addWriteStream'), $stream, $listener);
	}

	public function removeReadStream($stream)
	{
		return call_user_func(array($this->_loop, 'removeReadStream'), $stream);
	}

	public function removeWriteStream($stream)
	{
		return call_user_func(array($this->_loop, 'removeWriteStream'), $stream);
	}

	public function removeStream($stream)
	{
		return call_user_func(array($this->_loop, 'removeStream'), $stream);
	}

	public function addTimer($interval, callable $callback)
	{
		return call_user_func(array($this->_loop, 'addTimer'), $interval, $callback);
	}

	public function addPeriodicTimer($interval, callable $callback)
	{
		return call_user_func(array($this->_loop, 'addPeriodicTimer'), $interval, $callback);
	}

	public function cancelTimer(TimerInterface $timer)
	{
		return call_user_func(array($this->_loop, 'cancelTimer'), $timer);
	}

	public function isTimerActive(TimerInterface $timer)
	{
		return call_user_func(array($this->_loop, 'isTimerActive'), $timer);
	}

	public function nextTick(callable $listener)
	{
		return call_user_func(array($this->_loop, 'nextTick'), $listener);
	}

	public function futureTick(callable $listener)
	{
		return call_user_func(array($this->_loop, 'futureTick'), $listener);
	}

	public function tick()
	{
		return call_user_func(array($this->_loop, 'tick'));
	}

	public function run()
	{
		return call_user_func(array($this->_loop, 'run'));
	}

	public function stop()
	{
		return call_user_func(array($this->_loop, 'stop'));
	}
} 