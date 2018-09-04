<?php
namespace Common\Model;

use Common\Model\CommonModel;

class NavCatModel extends CommonModel
{
	protected $_validate = array(array('name', 'require', '分类名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}