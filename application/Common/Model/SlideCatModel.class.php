<?php
namespace Common\Model;

use Common\Model\CommonModel;

class SlideCatModel extends CommonModel
{
	protected $_validate = array(array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3), array('cat_idname', 'require', '分类标识不能为空！', 1, 'regex', 3),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}