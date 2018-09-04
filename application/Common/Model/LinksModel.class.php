<?php
namespace Common\Model;

use Common\Model\CommonModel;

class LinksModel extends CommonModel
{
	protected $_validate = array(array('link_name', 'require', '链接名称不能为空！', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), array('link_url', 'require', '链接地址不能为空！', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
} 