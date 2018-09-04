<?php
namespace Common\Model;

use Common\Model\CommonModel;

class UsersModel extends CommonModel
{
	protected $_validate = array(array('user_login', 'require', '用户名称不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT), array('user_pass', 'require', '密码不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT), array('user_login', 'require', '用户名称不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE), array('user_pass', 'require', '密码不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE), array('user_login', '', '用户名已经存在！', 0, 'unique', CommonModel:: MODEL_BOTH), array('mobile', '', '手机号已经存在！', 0, 'unique', CommonModel:: MODEL_BOTH), array('user_email', 'require', '邮箱不能为空！', 0, 'regex', CommonModel:: MODEL_BOTH), array('user_email', '', '邮箱帐号已经存在！', 0, 'unique', CommonModel:: MODEL_BOTH), array('user_email', 'email', '邮箱格式不正确！', 0, '', CommonModel:: MODEL_BOTH),);
	protected $_auto = array(array('create_time', 'mGetDate', CommonModel:: MODEL_INSERT, 'callback'), array('birthday', '', CommonModel::MODEL_UPDATE, 'ignore'));

	function mGetDate()
	{
		return date('Y-m-d H:i:s');
	}

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
		if (!empty($data['user_pass']) && strlen($data['user_pass']) < 25) {
			$data['user_pass'] = sp_password($data['user_pass']);
		}
	}
} 