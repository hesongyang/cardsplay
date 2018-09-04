<?php
namespace User\Controller;

use Common\Controller\AdminbaseController;

class OauthadminController extends AdminbaseController
{
	function index()
	{
		$oauth_user_model = M('OauthUser');
		$count = $oauth_user_model->where(array("status" => 1))->count();
		$page = $this->page($count, 20);
		$lists = $oauth_user_model->where(array("status" => 1))->order("create_time DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign("page", $page->show('Admin'));
		$this->assign('lists', $lists);
		$this->display();
	}

	function delete()
	{
		$id = intval($_GET['id']);
		if (empty($id)) {
			$this->error('非法数据！');
		}
		$rst = M("OauthUser")->where(array("id" => $id))->delete();
		if ($rst !== false) {
			$this->success("删除成功！", U("oauthadmin/index"));
		} else {
			$this->error('删除失败！');
		}
	}
}