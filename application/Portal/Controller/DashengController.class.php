<?php
namespace Portal\Controller;

use Common\Controller\HomebaseController;

class DashengController extends HomebaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->uid = session('uid');
	}

	public function gamerecord()
	{
		$page = ceil(I('page'));
		$size = 10;
		$kf_list = M('user_room')->where(['uid' => $this->uid])->order('`id` DESC')->limit(($page - 1) * $size, $size)->select();
		foreach ($kf_list as $k => $v) {
			$kf_list[$k]['datetime'] = date('Y-m-d H:i:s', $v['overtime']);
		}
		echo json_encode($kf_list);
	}

	public function firendmanager()
	{
		$this->display();
	}

	public function xiangqing()
	{
		$id = I('get.room');
		$mapxx['id'] = $id;
		$dkxx = M('room')->where($mapxx)->find();
		$rule = json_decode($dkxx['rule'], true);
		$dfxx = explode(',', $rule['play']['df']);
		$gzxx = explode(',', $rule['play']['gz']);
		$pxxx = explode(',', $rule['play']['px']);
		$gz2xx = explode(',', $rule['play']['gz2']);
		$szxx = explode(',', $rule['play']['sz']);
		$sxxx = explode(',', $rule['play']['sx']);
		$cmxx = explode(',', $rule['play']['cm']);
		$dkxx['df'] = $dfxx[$rule['df']];
		$dkxx['gz'] = $gzxx[$rule['gz']];
		$dkxx['sz'] = $szxx[$rule['sz']];
		$dkxx['sx'] = $sxxx[$rule['sx']];
		$dkxx['cm'] = $cmxx[$rule['cm']];
		$dkxx['wfname'] = $rule['play']['name'];
		$dkxx['userlist'] = json_decode($dkxx['user'], true);
		foreach ($pxxx as $key => $value) {
			if ($rule['px'][$key] == 1) {
				$dkxx['px'][] = $value;
			}
		}
		foreach ($gz2xx as $key => $value) {
			if ($rule['gz2'][$key] == 1) {
				$dkxx['gz2'][] = $value;
			}
		}
		$dkxx['over'] = json_decode($dkxx['overxx'], true);
		$this->assign('room', $dkxx);
		$map = array();
		$map['room'] = $id;
		$dj = M('dj_room')->where($map)->order('js asc')->select();
		$this->assign('dj', $dj);
		$this->display('xiangqing');
	}
}