<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class RegisterController extends HomebaseController
{
	function index()
	{
		if (sp_is_user_login()) {
			redirect(__ROOT__ . "/");
		} else {
			$this->display(":register");
		}
	}

	function doregister()
	{
		if (isset($_POST['email'])) {
			$this->_do_email_register();
		} elseif (isset($_POST['mobile'])) {
			$this->_do_mobile_register();
		} else {
			$this->error("注册方式不存在！");
		}
	}

	private function _do_mobile_register()
	{
		if (!sp_check_verify_code()) {
			$this->error("验证码错误！");
		}
		$rules = array(array('mobile', 'require', '手机号不能为空！', 1), array('password', 'require', '密码不能为空！', 1), array('password', '5,20', "密码长度至少5位，最多20位！", 1, 'length', 3),);
		$users_model = M("Users");
		if ($users_model->validate($rules)->create() === false) {
			$this->error($users_model->getError());
		}
		if (!sp_check_mobile_verify_code()) {
			$this->error("手机验证码错误！");
		}
		$password = I('post.password');
		$mobile = I('post.mobile');
		$where['mobile'] = $mobile;
		$users_model = M("Users");
		$result = $users_model->where($where)->count();
		if ($result) {
			$this->error("手机号已被注册！");
		} else {
			$data = array('user_login' => '', 'user_email' => '', 'mobile' => $mobile, 'user_nicename' => '', 'user_pass' => sp_password($password), 'last_login_ip' => get_client_ip(0, true), 'create_time' => date("Y-m-d H:i:s"), 'last_login_time' => date("Y-m-d H:i:s"), 'user_status' => 1, "user_type" => 2,);
			$rst = $users_model->add($data);
			if ($rst) {
				$data['id'] = $rst;
				session('user', $data);
				$this->success("注册成功！", __ROOT__ . "/");
			} else {
				$this->error("注册失败！", U("user/register/index"));
			}
		}
	}

	private function _do_email_register()
	{
		if (!sp_check_verify_code()) {
			$this->error("验证码错误！");
		}
		$rules = array(array('email', 'require', '邮箱不能为空！', 1), array('password', 'require', '密码不能为空！', 1), array('password', '5,20', "密码长度至少5位，最多20位！", 1, 'length', 3), array('repassword', 'require', '重复密码不能为空！', 1), array('repassword', 'password', '确认密码不正确', 0, 'confirm'), array('email', 'email', '邮箱格式不正确！', 1),);
		$users_model = M("Users");
		if ($users_model->validate($rules)->create() === false) {
			$this->error($users_model->getError());
		}
		$password = I('post.password');
		$email = I('post.email');
		$username = str_replace(array(".", "@"), "_", $email);
		$stripChar = '?<*.>\'"';
		if (preg_match('/[' . $stripChar . ']/is', $username) == 1) {
			$this->error('用户名中包含' . $stripChar . '等非法字符！');
		}
		$where['user_login'] = $username;
		$where['user_email'] = $email;
		$where['_logic'] = 'OR';
		$ucenter_syn = C("UCENTER_ENABLED");
		$uc_checkemail = 1;
		$uc_checkusername = 1;
		if ($ucenter_syn) {
			include UC_CLIENT_ROOT . "client.php";
			$uc_checkemail = uc_user_checkemail($email);
			$uc_checkusername = uc_user_checkname($username);
		}
		$users_model = M("Users");
		$result = $users_model->where($where)->count();
		if ($result || $uc_checkemail < 0 || $uc_checkusername < 0) {
			$this->error("用户名或者该邮箱已经存在！");
		} else {
			$uc_register = true;
			if ($ucenter_syn) {
				$uc_uid = uc_user_register($username, $password, $email);
				if ($uc_uid < 0) {
					$uc_register = false;
				}
			}
			if ($uc_register) {
				$need_email_active = C("SP_MEMBER_EMAIL_ACTIVE");
				$data = array('user_login' => $username, 'user_email' => $email, 'user_nicename' => $username, 'user_pass' => sp_password($password), 'last_login_ip' => get_client_ip(0, true), 'create_time' => date("Y-m-d H:i:s"), 'last_login_time' => date("Y-m-d H:i:s"), 'user_status' => $need_email_active ? 2 : 1, "user_type" => 2,);
				$rst = $users_model->add($data);
				if ($rst) {
					$data['id'] = $rst;
					session('user', $data);
					if ($need_email_active) {
						$this->_send_to_active();
						session('user', null);
						$this->success("注册成功，激活后才能使用！", U("user/login/index"));
					} else {
						$this->success("注册成功！", __ROOT__ . "/");
					}
				} else {
					$this->error("注册失败！", U("user/register/index"));
				}
			} else {
				$this->error("注册失败！", U("user/register/index"));
			}
		}
	}

	function active()
	{
		$hash = I("get.hash", "");
		if (empty($hash)) {
			$this->error("激活码不存在");
		}
		$users_model = M("Users");
		$find_user = $users_model->where(array("user_activation_key" => $hash))->find();
		if ($find_user) {
			$result = $users_model->where(array("user_activation_key" => $hash))->save(array("user_activation_key" => "", "user_status" => 1));
			if ($result) {
				$find_user['user_status'] = 1;
				session('user', $find_user);
				$this->success("用户激活成功，正在登录中...", __ROOT__ . "/");
			} else {
				$this->error("用户激活失败!", U("user/login/index"));
			}
		} else {
			$this->error("用户激活失败，激活码无效！", U("user/login/index"));
		}
	}
}