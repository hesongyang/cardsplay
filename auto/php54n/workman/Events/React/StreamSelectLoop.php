<?php
namespace Workerman\Events\React;
class StreamSelectLoop extends \React\EventLoop\StreamSelectLoop
{
	public function addSignal($signal, $callback)
	{
		pcntl_signal($signal, $callback);
	}

	public function removeSignal($signal)
	{
		pcntl_signal($signal, SIG_IGN);
	}
} 