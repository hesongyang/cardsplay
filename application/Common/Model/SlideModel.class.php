<?php
namespace Common\Model;

use Common\Model\CommonModel;

class SlideModel extends CommonModel
{
	protected $_validate = array(array('slide_name', 'require', '名称不能为空！', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),);
	protected $_auto = array(array('slide_pic', 'sp_asset_relative_url', self::MODEL_BOTH, 'function'),);

	protected function _before_write(&$data)
	{
		parent::_before_write($data);
	}
}