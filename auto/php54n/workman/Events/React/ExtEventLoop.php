<?php
namespace Workerman\Events\React;
class ExtEventLoop extends \React\EventLoop\ExtEventLoop
{
	protected $_eventBase = null;
	protected $_signalEvents = array();

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
} 