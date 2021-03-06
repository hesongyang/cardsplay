<?php
namespace Api\Controller;

use Common\Controller\AdminbaseController;

class OauthadminController extends AdminbaseController
{
	public function setting()
	{
		$host = sp_get_host();
		$callback_uri_root = $host . __ROOT__ . '/index.php?g=api&m=oauth&a=callback&type=';
		$this->assign("callback_uri_root", $callback_uri_root);
		$this->display();
	}

	public function setting_post()
	{
		if (IS_POST) {
			$qq_key = I('post.qq_key');
			$qq_sec = I('post.qq_sec');
			$sina_key = I('post.sina_key');
			$sina_sec = I('post.sina_sec');
			$wx_key = $_POST['wx_key'];
			$wx_sec = $_POST['wx_sec'];
			$host = sp_get_host();
			$call_back = $host . __ROOT__ . '/index.php?g=api&m=oauth&a=callback&type=';
			$data = array('THINK_SDK_QQ' => array('APP_KEY' => $qq_key, 'APP_SECRET' => $qq_sec, 'CALLBACK' => $call_back . 'qq',), 'THINK_SDK_SINA' => array('APP_KEY' => $sina_key, 'APP_SECRET' => $sina_sec, 'CALLBACK' => $call_back . 'sina',), 'THINK_SDK_WEIXIN' => array('APP_KEY' => $wx_key, 'APP_SECRET' => $wx_sec, 'CALLBACK' => $call_back . 'weixin',),);
			$result = sp_set_dynamic_config($data);
			if ($result) {
				$this->success("更新成功！");
			} else {
				$this->error("更新失败！");
			}
		}
	}
}