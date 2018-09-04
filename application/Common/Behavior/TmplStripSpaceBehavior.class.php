<?php
namespace Common\Behavior;

use Think\Behavior;

class TmplStripSpaceBehavior extends Behavior
{
	public function run(&$tmplContent)
	{
		if (C('TMPL_STRIP_SPACE')) {
			$find = array('~>\s+<~', '~>(\s+\n|\r)~');
			$replace = array('> <', '>');
			$tmplContent = preg_replace($find, $replace, $tmplContent);
		}
	}
}