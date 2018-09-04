<?php
namespace Common\Lib\TagLib;

use Think\Template\TagLib;

class TagLibSpadmin extends TagLib
{
	protected $tags = array('admintpl' => array("attr" => "file", "close" => 0),);

	public function _admintpl($tag, $content)
	{
		$file = $tag['file'];
		$counts = count($file);
		if ($counts < 3) {
			$file_path = "Admin" . "/" . $tag['file'];
		} else {
			$file_path = $file[0] . "/" . "Tpl" . "/" . $file[1] . "/" . $file[2];
		}
		$TemplatePath = sp_add_template_file_suffix(C("SP_ADMIN_TMPL_PATH") . C("SP_ADMIN_DEFAULT_THEME") . "/" . $file_path);
		if (!file_exists_case($TemplatePath)) {
			return false;
		}
		$tmplContent = file_get_contents($TemplatePath);
		$parseStr = $this->tpl->parse($tmplContent);
		return $parseStr;
	}
} 