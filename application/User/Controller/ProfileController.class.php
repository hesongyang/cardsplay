<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class ProfileController extends MemberbaseController
{
	function _initialize()
	{
		parent::_initialize();
	}

	public function edit()
	{
		$this->assign($this->user);
		$this->display();
	}

	public function edit_post()
	{
		if (IS_POST) {
			$_POST['id'] = $this->userid;
			if ($this->users_model->field('id,user_nicename,sex,birthday,user_url,signature')->create() !== false) {
				if ($this->users_model->save() !== false) {
					$this->user = $this->users_model->find($this->userid);
					sp_update_current_user($this->user);
					$this->success("保存成功！", U("user/profile/edit"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
		}
	}

	public function password()
	{
		$this->assign($this->user);
		$this->display();
	}

	public function password_post()
	{
		if (IS_POST) {
			$old_password = I('post.old_password');
			if (empty($old_password)) {
				$this->error("原始密码不能为空！");
			}
			$password = I('post.password');
			if (empty($password)) {
				$this->error("新密码不能为空！");
			}
			$uid = sp_get_current_userid();
			$admin = $this->users_model->where(array('id' => $uid))->find();
			if (sp_compare_password($old_password, $admin['user_pass'])) {
				if ($password == I('post.repassword')) {
					if (sp_compare_password($password, $admin['user_pass'])) {
						$this->error("新密码不能和原始密码相同！");
					} else {
						$data['user_pass'] = sp_password($password);
						$data['id'] = $uid;
						$r = $this->users_model->save($data);
						if ($r !== false) {
							$this->success("修改成功！");
						} else {
							$this->error("修改失败！");
						}
					}
				} else {
					$this->error("密码输入不一致！");
				}
			} else {
				$this->error("原始密码不正确！");
			}
		}
	}

	function bang()
	{
		$oauth_user_model = M("OauthUser");
		$uid = sp_get_current_userid();
		$oauths = $oauth_user_model->where(array("uid" => $uid))->select();
		$new_oauths = array();
		foreach ($oauths as $oa) {
			$new_oauths[strtolower($oa['from'])] = $oa;
		}
		$this->assign("oauths", $new_oauths);
		$this->display();
	}

	function avatar()
	{
		$this->assign($this->user);
		$this->display();
	}

	function avatar_upload()
	{
		$config = array('rootPath' => './' . C("UPLOADPATH"), 'savePath' => './avatar/', 'maxSize' => 512000, 'saveName' => array('uniqid', ''), 'exts' => array('jpg', 'png', 'jpeg'), 'autoSub' => false,);
		$upload = new \Think\Upload($config, 'Local');
		$info = $upload->upload();
		if ($info) {
			$first = array_shift($info);
			$file = $first['savename'];
			session('avatar', $file);
			$this->ajaxReturn(sp_ajax_return(array("file" => $file), "上传成功！", 1), "AJAX_UPLOAD");
		} else {
			$this->ajaxReturn(sp_ajax_return(array(), $upload->getError(), 0), "AJAX_UPLOAD");
		}
	}

	function avatar_update()
	{
		$session_avatar = session('avatar');
		if (!empty($session_avatar)) {
			$targ_w = I('post.w', 0, 'intval');
			$targ_h = I('post.h', 0, 'intval');
			$x = I('post.x', 0, 'intval');
			$y = I('post.y', 0, 'intval');
			$jpeg_quality = 90;
			$avatar = $session_avatar;
			$avatar_dir = C("UPLOADPATH") . "avatar/";
			$avatar_path = $avatar_dir . $avatar;
			$image = new \Think\Image();
			$image->open($avatar_path);
			$image->crop($targ_w, $targ_h, $x, $y);
			$image->save($avatar_path);
			$result = true;
			$file_upload_type = C('FILE_UPLOAD_TYPE');
			if ($file_upload_type == 'Qiniu') {
				$upload = new \Think\Upload();
				$file = array('savepath' => '', 'savename' => 'avatar/' . $avatar, 'tmp_name' => $avatar_path);
				$result = $upload->getUploader()->save($file);
			}
			if ($result === true) {
				$userid = sp_get_current_userid();
				$result = $this->users_model->where(array("id" => $userid))->save(array("avatar" => 'avatar/' . $avatar));
				session('user.avatar', 'avatar/' . $avatar);
				if ($result) {
					$this->success("头像更新成功！");
				} else {
					$this->error("头像更新失败！");
				}
			} else {
				$this->error("头像保存失败！");
			}
		}
	}

	public function do_avatar()
	{
		$imgurl = I('post.imgurl');
		$imgurl = str_replace('/', '', $imgurl);
		$old_img = $this->user['avatar'];
		$this->user['avatar'] = $imgurl;
		$res = $this->users_model->where(array("id" => $this->userid))->save($this->user);
		if ($res) {
			session('user', $this->user);
			sp_delete_avatar($old_img);
		} else {
			$this->user['avatar'] = $old_img;
			sp_delete_avatar($imgurl);
		}
		$this->ajaxReturn($res);
	}
}