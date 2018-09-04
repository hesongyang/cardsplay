<?php
namespace Common\Model;

use Common\Model\CommonModel;

class PluginsModel extends CommonModel
{
	protected $_validate = array();

	public function getList()
	{
		$dirs = array_map('basename', glob('./plugins/*', GLOB_ONLYDIR));
		if ($dirs === false) {
			$this->error = '插件目录不可读';
			return false;
		}
		$plugins = array();
		if (empty($dirs)) return $plugins;
		$where['name'] = array('in', $dirs);
		$list = $this->where($where)->field(true)->select();
		foreach ($list as $plugin) {
			$plugins[$plugin['name']] = $plugin;
		}
		foreach ($dirs as $value) {
			if (!isset($plugins[$value])) {
				$class = sp_get_plugin_class($value);
				if (!class_exists($class)) {
					\Think\Log::record('插件' . $value . '的入口文件不存在！');
					continue;
				}
				$obj = new $class;
				$plugins[$value] = $obj->info;
				if (!isset($obj->info['type']) || $obj->info['type'] == 1) {
					if ($plugins[$value]) {
						$plugins[$value]['status'] = 3;
					}
				} else {
					unset($plugins[$value]);
				}
			}
		}
		return $plugins;
	}

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}