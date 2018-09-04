<?php
namespace Portal\Model;

use Common\Model\CommonModel;

class UserModel extends CommonModel
{
	protected $_validate = array(array('user_login', 'require', '用户名称不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT), array('parent', 'require', '推荐人不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT), array('user_pass', 'require', '密码不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT), array('user_login', 'require', '用户名称不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE), array('parent', 'require', '推荐人不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE), array('user_login', '', '用户名已经存在！', 0, 'unique', CommonModel:: MODEL_BOTH), array('user_login', '', '用户名已经存在！', 1, 'unique', CommonModel:: MODEL_BOTH),);
	protected $_auto = array(array('create_time', 'mGetDate', CommonModel:: MODEL_INSERT, 'callback'), array('birthday', '', CommonModel::MODEL_UPDATE, 'ignore'));

	function mGetDate()
	{
		return date('Y-m-d H:i:s');
	}

	protected function _after_insert($data, $options)
	{
		parent::_after_insert($data, $options);
		$id = $data['id'];
		$parent_id = $data['parent'];
		if ($parent_id == 0) {
			$d['path'] = "0-$id";
		} else {
			$parent = $this->where("id=$parent_id")->find();
			$d['path'] = $parent['path'] . '-' . $id;
		}
		$this->where("id=$id")->save($d);
	}

	protected function _after_update($data, $options)
	{
		parent::_after_update($data, $options);
		if (isset($data['parent'])) {
			$id = $data['id'];
			$parent_id = $data['parent'];
			if ($parent_id == 0) {
				$d['path'] = "0-$id";
			} else {
				$parent = $this->where("id=$parent_id")->find();
				$d['path'] = $parent['path'] . '-' . $id;
			}
			$result = $this->where("id=$id")->save($d);
			if ($result) {
				$children = $this->where(array("parent" => $id))->select();
				foreach ($children as $child) {
					$this->where(array("id" => $child['id']))->save(array("parent" => $id, "id" => $child['id']));
				}
			}
		}
	}

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}

	public function disable($user_login, $disable_notice)
	{
		$data['user_status'] = 1;
		$data['disable_notice'] = $disable_notice;
		$res = $this->where(array('user_login' => $user_login))->save($data);
		if ($res) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function unDisable($user_login, $notice)
	{
		$data['user_status'] = 0;
		$data['disable_notice'] = $notice;
		$res = $this->where(array('user_login' => $user_login))->save($data);
		if ($res) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
} 