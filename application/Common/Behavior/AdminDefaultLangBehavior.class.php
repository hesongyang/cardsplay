<?php
namespace Common\Behavior;

use Think\Behavior;

class AdminDefaultLangBehavior extends Behavior
{
	public function run(&$params)
	{
		$this->loadLang();
	}

	private function loadLang()
	{
		if (!C('LANG_SWITCH_ON', null, false)) return;
		$default_lang = C('DEFAULT_LANG');
		$langSet = C('ADMIN_LANG_SWITCH_ON', null, false) ? LANG_SET : $default_lang;
		$file = THINK_PATH . 'Lang/' . $langSet . '.php';
		if (!C('ADMIN_LANG_SWITCH_ON', null, false) && is_file($file)) L(include $file);
		$file = LANG_PATH . $langSet . '.php';
		if (is_file($file)) L(include $file);
		$file = MODULE_PATH . 'Lang/' . $langSet . '.php';
		if (is_file($file)) L(include $file);
		$file = MODULE_PATH . 'Lang/' . $langSet . '/' . strtolower(CONTROLLER_NAME) . '.php';
		if (is_file($file)) L(include $file);
	}
} 