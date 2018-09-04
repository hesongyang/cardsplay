<?php
namespace Common\Controller;

use Common\Controller\AppframeController;

class PlanbaseController extends AppframeController
{
	protected $uid;
	protected $user_login;
	protected $user;
	protected $time;
	protected $all_record;
	protected $extract;
	protected $bonus;

	public function __construct()
	{
		$this->set_action_success_error_tpl();
		parent::__construct();
		$this->time = $this->getTime();
		$this->all_record = M('AllRecord');
		$this->extract = sp_get_option('extract');
		$this->bonus = sp_get_option('bonus');
		$this->assign('bonus', $this->bonus);
		$this->assign('extract', $this->extract);
		$this->uid = session('uid');
		$this->user_login = session('user_login');
		$this->user_model = D("Portal/User");
		$this->user = $this->user_model->find($this->uid);
		$this->assign('user', $this->user);
	}

	public function getTime()
	{
		return date("Y-m-d H:i:s", time());
	}

	function _initialize()
	{
		parent::_initialize();
		defined('TMPL_PATH') or define("TMPL_PATH", C("SP_TMPL_PATH"));
		$site_options = get_site_options();
		$this->assign($site_options);
		if (sp_is_user_login()) {
			$this->assign("user", sp_get_current_user());
		}
	}

	protected function check_login()
	{
		$session_user = session('user_login');
		if (empty($session_user)) {
			$this->error('您还没有登录！', leuu('portal/index/index'));
		}
	}

	public function msg($msg, $url)
	{
		if (!$url) {
			$url = U('portal/home/index');
		}
		header('Content-Type:text/html; charset=utf-8');
		die("<script>alert('" . $msg . "');location.href='{$url}';</script>");
	}

	public function addRecord($uname, $wallet, $num, $notice)
	{
		$data['user_login'] = $uname;
		$data['wallet'] = $wallet;
		$data['number'] = $num;
		$data['notice'] = $notice;
		$data['create_time'] = $this->time;
		$this->all_record->add($data);
	}

	protected function check_user()
	{
		$user_status = M('Users')->where(array("id" => sp_get_current_userid()))->getField("user_status");
		if ($user_status == 2) {
			$this->error('您还没有激活账号，请激活后再使用！', U("user/login/active"));
		}
		if ($user_status == 0) {
			$this->error('此账号已经被禁止使用，请联系管理员！', __ROOT__ . "/");
		}
	}

	protected function _send_to_active()
	{
		$option = M('Options')->where(array('option_name' => 'member_email_active'))->find();
		if (!$option) {
			$this->error('网站未配置账号激活信息，请联系网站管理员');
		}
		$options = json_decode($option['option_value'], true);
		$title = $options['title'];
		$uid = session('user.id');
		$username = session('user.user_login');
		$activekey = md5($uid . time() . uniqid());
		$users_model = M("Users");
		$result = $users_model->where(array("id" => $uid))->save(array("user_activation_key" => $activekey));
		if (!$result) {
			$this->error('激活码生成失败！');
		}
		$url = U('user/register/active', array("hash" => $activekey), "", true);
		$template = $options['template'];
		$content = str_replace(array('http://#link#', '#username#'), array($url, $username), $template);
		$send_result = sp_send_email(session('user.user_email'), $title, $content);
		if ($send_result['error']) {
			$this->error('激活邮件发送失败，请尝试登录后，手动发送激活邮件！');
		}
	}

	public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
	{
		parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content, $prefix);
	}

	public function fetch($templateFile = '', $content = '', $prefix = '')
	{
		$templateFile = empty($content) ? $this->parseTemplate($templateFile) : '';
		return parent::fetch($templateFile, $content, $prefix);
	}

	public function parseTemplate($template = '')
	{
		$tmpl_path = C("SP_TMPL_PATH");
		define("SP_TMPL_PATH", $tmpl_path);
		if ($this->theme) {
			$theme = $this->theme;
		} else {
			$theme = C('SP_DEFAULT_THEME');
			if (C('TMPL_DETECT_THEME')) {
				$t = C('VAR_TEMPLATE');
				if (isset($_GET[$t])) {
					$theme = $_GET[$t];
				} elseif (cookie('think_template')) {
					$theme = cookie('think_template');
				}
				if (!file_exists($tmpl_path . "/" . $theme)) {
					$theme = C('SP_DEFAULT_THEME');
				}
				cookie('think_template', $theme, 864000);
			}
		}
		$theme_suffix = "";
		if (C('MOBILE_TPL_ENABLED') && sp_is_mobile()) {
			if (C('LANG_SWITCH_ON', null, false)) {
				if (file_exists($tmpl_path . "/" . $theme . "_mobile_" . LANG_SET)) {
					$theme_suffix = "_mobile_" . LANG_SET;
				} elseif (file_exists($tmpl_path . "/" . $theme . "_mobile")) {
					$theme_suffix = "_mobile";
				} elseif (file_exists($tmpl_path . "/" . $theme . "_" . LANG_SET)) {
					$theme_suffix = "_" . LANG_SET;
				}
			} else {
				if (file_exists($tmpl_path . "/" . $theme . "_mobile")) {
					$theme_suffix = "_mobile";
				}
			}
		} else {
			$lang_suffix = "_" . LANG_SET;
			if (C('LANG_SWITCH_ON', null, false) && file_exists($tmpl_path . "/" . $theme . $lang_suffix)) {
				$theme_suffix = $lang_suffix;
			}
		}
		$theme = $theme . $theme_suffix;
		C('SP_DEFAULT_THEME', $theme);
		$current_tmpl_path = $tmpl_path . $theme . "/";
		define('THEME_PATH', $current_tmpl_path);
		$cdn_settings = sp_get_option('cdn_settings');
		if (!empty($cdn_settings['cdn_static_root'])) {
			$cdn_static_root = rtrim($cdn_settings['cdn_static_root'], '/');
			C("TMPL_PARSE_STRING.__TMPL__", $cdn_static_root . "/" . $current_tmpl_path);
			C("TMPL_PARSE_STRING.__PUBLIC__", $cdn_static_root . "/public");
			C("TMPL_PARSE_STRING.__WEB_ROOT__", $cdn_static_root);
		} else {
			C("TMPL_PARSE_STRING.__TMPL__", __ROOT__ . "/" . $current_tmpl_path);
		}
		C('SP_VIEW_PATH', $tmpl_path);
		C('DEFAULT_THEME', $theme);
		define("SP_CURRENT_THEME", $theme);
		if (is_file($template)) {
			return $template;
		}
		$depr = C('TMPL_FILE_DEPR');
		$template = str_replace(':', $depr, $template);
		$module = MODULE_NAME;
		if (strpos($template, '@')) {
			list($module, $template) = explode('@', $template);
		}
		$module = $module . "/";
		if ('' == $template) {
			$template = CONTROLLER_NAME . $depr . ACTION_NAME;
		} elseif (false === strpos($template, '/')) {
			$template = CONTROLLER_NAME . $depr . $template;
		}
		$file = sp_add_template_file_suffix($current_tmpl_path . $module . $template);
		$file = str_replace("//", '/', $file);
		return $file;
	}

	private function set_action_success_error_tpl()
	{
		$theme = C('SP_DEFAULT_THEME');
		if (C('TMPL_DETECT_THEME')) {
			if (cookie('think_template')) {
				$theme = cookie('think_template');
			}
		}
		$tpl_path = '';
		if (C('MOBILE_TPL_ENABLED') && sp_is_mobile() && file_exists(C("SP_TMPL_PATH") . "/" . $theme . "_mobile")) {
			$theme = $theme . "_mobile";
			$tpl_path = C("SP_TMPL_PATH") . $theme . "/";
		} else {
			$tpl_path = C("SP_TMPL_PATH") . $theme . "/";
		}
		$defaultjump = THINK_PATH . 'Tpl/dispatch_jump.tpl';
		$action_success = sp_add_template_file_suffix($tpl_path . C("SP_TMPL_ACTION_SUCCESS"));
		$action_error = sp_add_template_file_suffix($tpl_path . C("SP_TMPL_ACTION_ERROR"));
		if (file_exists_case($action_success)) {
			C("TMPL_ACTION_SUCCESS", $action_success);
		} else {
			C("TMPL_ACTION_SUCCESS", $defaultjump);
		}
		if (file_exists_case($action_error)) {
			C("TMPL_ACTION_ERROR", $action_error);
		} else {
			C("TMPL_ACTION_ERROR", $defaultjump);
		}
	}
} 