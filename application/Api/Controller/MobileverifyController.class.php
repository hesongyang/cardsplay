<?php
namespace Api\Controller;

use Think\Controller;

class MobileverifyController extends Controller
{
	public function send()
	{
		if (IS_POST) {
			$mobile = I('post.mobile/s');
			$result = hook_one("send_mobile_verify_code", array('mobile' => $mobile));
			if ($result['error'] === 0) {
				$this->success('验证码已发送到您手机，请查收！');
			} else {
				$this->error($result['error_msg']);
			}
		}
	}
} 