<?php
namespace Common\Model;

use Common\Model\CommonModel;

class NavModel extends CommonModel
{
	protected $_validate = array(array('label', 'require', '菜单标签不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}