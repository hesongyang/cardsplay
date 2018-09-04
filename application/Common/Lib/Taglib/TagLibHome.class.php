<?php
namespace Common\Lib\TagLib;

use Think\Template\TagLib;

class TagLibHome extends TagLib
{
	protected $tags = array('tc_include' => array("attr" => "file", "close" => 0),);

	public function _tc_include($tag, $content)
	{
		static $_tc_include_templateParseCache = array();
		$file = str_replace(":", "/", $tag['file']);
		$cacheIterateId = md5($file . $content);
		if (isset($_tc_include_templateParseCache[$cacheIterateId])) {
			return $_tc_include_templateParseCache[$cacheIterateId];
		}
		$TemplatePath = sp_add_template_file_suffix(C("SP_TMPL_PATH") . C('SP_DEFAULT_THEME') . "/" . $file);
		if (!file_exists_case($TemplatePath)) {
			return false;
		}
		$tmplContent = file_get_contents($TemplatePath);
		$parseStr = $this->tpl->parse($tmplContent);
		$_tc_include_templateParseCache[$cacheIterateId] = $parseStr;
		return $_tc_include_templateParseCache[$cacheIterateId];
	}
} 