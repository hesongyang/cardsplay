<?php
namespace Portal\Controller;

use Common\Controller\AdminbaseController;

class AdminServerController extends AdminbaseController
{
	function _initialize()
	{
		parent::_initialize();
	}

	public function index()
	{
		$list = M('server')->select();
		$this->assign('list', $list);
		$this->display();
	}

	public function add()
	{
		$this->display();
	}

	public function add_post()
	{
		$data = I('post.');
		$res = M('server')->add($data);
		if ($res) {
			$this->success("添加成功");
		} else {
			$this->error('添加失败');
		}
	}

	public function openserver($start)
	{
		$zdata['act'] = $start;
		get_http('http://' . $_SERVER['HTTP_HOST'] . '/auto/start.php', $zdata);
	}

	public function endserver($act)
	{
		$zdata['act'] = $act;
		get_http('http://' . $_SERVER['HTTP_HOST'] . '/auto/start.php', $zdata);
	}
}