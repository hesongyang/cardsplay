<?php
namespace Common\Lib;
class iAuth
{
	protected $_config = array();

	public function __construct()
	{
	}

	public function check($uid, $name, $relation = 'or')
	{
		if (empty($uid)) {
			return false;
		}
		if ($uid == 1) {
			return true;
		}
		if (is_string($name)) {
			$name = strtolower($name);
			if (strpos($name, ',') !== false) {
				$name = explode(',', $name);
			} else {
				$name = array($name);
			}
		}
		$list = array();
		$role_user_model = M("RoleUser");
		$role_user_join = '__ROLE__ as b on a.role_id =b.id';
		$groups = $role_user_model->alias("a")->join($role_user_join)->where(array("user_id" => $uid, "status" => 1))->getField("role_id", true);
		if (in_array(1, $groups)) {
			return true;
		}
		if (empty($groups)) {
			return false;
		}
		$auth_access_model = M("AuthAccess");
		$join = '__AUTH_RULE__ as b on a.rule_name =b.name';
		$rules = $auth_access_model->alias("a")->join($join)->where(array("a.role_id" => array("in", $groups), "b.name" => array("in", $name)))->select();
		foreach ($rules as $rule) {
			if (!empty($rule['condition'])) {
				$user = $this->getUserInfo($uid);
				$command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
				@(eval('$condition=(' . $command . ');'));
				if ($condition) {
					$list[] = strtolower($rule['name']);
				}
			} else {
				$list[] = strtolower($rule['name']);
			}
		}
		if ($relation == 'or' and !empty($list)) {
			return true;
		}
		$diff = array_diff($name, $list);
		if ($relation == 'and' and empty($diff)) {
			return true;
		}
		return false;
	}

	private function getUserInfo($uid)
	{
		static $userinfo = array();
		if (!isset($userinfo[$uid])) {
			$userinfo[$uid] = M("Users")->where(array('id' => $uid))->find();
		}
		return $userinfo[$uid];
	}
} 