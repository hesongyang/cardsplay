<?php
namespace Portal\Controller;

use Common\Controller\HomebaseController;

class SearchController extends HomebaseController
{
	public function index()
	{
		$keyword = I("request.keyword");
		if (empty($keyword)) {
			$this->error("关键词不能为空！请重新输入！");
		}
		$this->assign("keyword", $keyword);
		$this->display(":search");
	}
} 