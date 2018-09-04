<?php
namespace Workerman\Events\React;
class LibEventLoop extends \React\EventLoop\LibEventLoop
{
	protected $_eventBase = null;
	protected $_signalEvents = array();

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
} 