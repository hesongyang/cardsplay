<?php
namespace Common\Model;

use Common\Model\CommonModel;

class RoleModel extends CommonModel
{
	protected $_validate = array(array('name', 'require', '角色名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH),);
	protected $_auto = array(array('create_time', 'time', 1, 'function'), array('update_time', 'time', 2, 'function'),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}