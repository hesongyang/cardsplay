<?php
namespace Common\Model;

use Common\Model\CommonModel;

class AdModel extends CommonModel
{
	protected $_validate = array(array('ad_name', 'require', '广告名称不能为空！', 1, 'regex', 3),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}