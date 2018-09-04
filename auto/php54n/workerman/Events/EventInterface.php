<?php
namespace Workerman\Events;
interface EventInterface
{
	const EV_READ = 1;
	const EV_WRITE = 2;
	const EV_EXCEPT = 3;
	const EV_SIGNAL = 4;
	const EV_TIMER = 8;
	const EV_TIMER_ONCE = 16;

	public function add($fd, $flag, $func, $args = null);

	public function del($fd, $flag);

	public function clearAllTimer();

	public function loop();

	public function destroy();

	public function getTimerCount();
} 