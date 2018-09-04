<?php
namespace Common\Behavior;

use Think\Behavior;

class UrldecodeGetBehavior extends Behavior
{
	public function run(&$data)
	{
		$_GET = array_map_recursive('urldecode', $_GET);
		$_REQUEST = array_merge($_POST, $_GET, $_COOKIE);
	}
}