<?php
namespace Common\Model;

use Common\Model\CommonModel;

class MenuModel extends CommonModel
{
	protected $_validate = array(array('name', 'require', '菜单名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('app', 'require', '应用不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('model', 'require', '模块名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('action', 'require', '方法名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('app,model,action', 'checkAction', '同样的记录已经存在！', 1, 'callback', CommonModel:: MODEL_INSERT), array('id,app,model,action', 'checkActionUpdate', '同样的记录已经存在！', 1, 'callback', CommonModel:: MODEL_UPDATE), array('parentid', 'checkParentid', '菜单只支持四级！', 1, 'callback', 1),);
	protected $_auto = array();

	public function checkParentid($parentid)
	{
		$find = $this->where(array("id" => $parentid))->getField("parentid");
		if ($find) {
			$find2 = $this->where(array("id" => $find))->getField("parentid");
			if ($find2) {
				$find3 = $this->where(array("id" => $find2))->getField("parentid");
				if ($find3) {
					return false;
				}
			}
		}
		return true;
	}

	public function checkAction($data)
	{
		$find = $this->where($data)->find();
		if ($find) {
			return false;
		}
		return true;
	}

	public function checkActionUpdate($data)
	{
		$id = $data['id'];
		unset($data['id']);
		$find = $this->field('id')->where($data)->find();
		if (isset($find['id']) && $find['id'] != $id) {
			return false;
		}
		return true;
	}

	public function admin_menu($parentid, $with_self = false)
	{
		$parentid = (int)$parentid;
		$result = $this->where(array('parentid' => $parentid, 'status' => 1))->order(array("listorder" => "ASC"))->select();
		if ($with_self) {
			$result2[] = $this->where(array('id' => $parentid))->find();
			$result = array_merge($result2, $result);
		}
		if (sp_get_current_admin_id() == 1) {
			return $result;
		}
		$array = array();
		foreach ($result as $v) {
			$action = $v['action'];
			if (preg_match('/^public_/', $action)) {
				$array[] = $v;
			} else {
				if (preg_match('/^ajax_([a-z]+)_/', $action, $_match)) {
					$action = $_match[1];
				}
				$rule_name = strtolower($v['app'] . "/" . $v['model'] . "/" . $action);
				if (sp_auth_check(sp_get_current_admin_id(), $rule_name)) {
					$array[] = $v;
				}
			}
		}
		return $array;
	}

	public function submenu($parentid = '', $big_menu = false)
	{
		$array = $this->admin_menu($parentid, 1);
		$numbers = count($array);
		if ($numbers == 1 && !$big_menu) {
			return '';
		}
		return $array;
	}

	public function menu_json()
	{
		$data = $this->get_tree(0);
		return $data;
	}

	public function get_tree($myid, $parent = "", $Level = 1)
	{
		$data = $this->admin_menu($myid);
		$Level++;
		if (is_array($data)) {
			$ret = NULL;
			foreach ($data as $a) {
				$id = $a['id'];
				$name = ucwords($a['app']);
				$model = ucwords($a['model']);
				$action = $a['action'];
				$params = "";
				if ($a['data']) {
					$params = "?" . htmlspecialchars_decode($a['data']);
				}
				$array = array("icon" => $a['icon'], "id" => $id . $name, "name" => $a['name'], "parent" => $parent, "url" => U("{$name}/{$model}/{$action}{$params}"), 'lang' => strtoupper($name . '_' . $model . '_' . $action));
				$ret[$id . $name] = $array;
				$child = $this->get_tree($a['id'], $id, $Level);
				if ($child && $Level <= 3) {
					$ret[$id . $name]['items'] = $child;
				}
			}
			return $ret;
		}
		return false;
	}

	public function menu_cache($data = null)
	{
		if (empty($data)) {
			$data = $this->select();
			F("Menu", $data);
		} else {
			F("Menu", $data);
		}
		return $data;
	}

	public function _before_write(&$data)
	{
		parent::_before_write($data);
		F("Menu", NULL);
	}

	public function _after_delete($data, $options)
	{
		parent::_after_delete($data, $options);
		$this->_before_write($data);
	}

	public function menu($parentid, $with_self = false)
	{
		$parentid = (int)$parentid;
		$result = $this->where(array('parentid' => $parentid))->select();
		if ($with_self) {
			$result2[] = $this->where(array('id' => $parentid))->find();
			$result = array_merge($result2, $result);
		}
		return $result;
	}

	public function get_menu_tree($parentid = 0)
	{
		$menus = $this->where(array("parentid" => $parentid))->order(array("listorder" => "ASC"))->select();
		if ($menus) {
			foreach ($menus as $key => $menu) {
				$children = $this->get_menu_tree($menu['id']);
				if (!empty($children)) {
					$menus[$key]['children'] = $children;
				}
				unset($menus[$key]['id']);
				unset($menus[$key]['parentid']);
			}
			return $menus;
		} else {
			return $menus;
		}
	}
}