<?php
namespace Common\Model;

use Common\Model\CommonModel;

class GuestbookModel extends CommonModel
{
	protected $_validate = array(array('full_name', 'require', '姓名不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('email', 'require', '邮箱不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('msg', 'require', '留言不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH), array('email', 'email', '邮箱格式不正确！', 0, '', CommonModel:: MODEL_BOTH),);
	protected $_auto = array(array('createtime', 'mDate', 1, 'callback'),);

	function mDate()
	{
		return date("Y-m-d H:i:s");
	}

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}