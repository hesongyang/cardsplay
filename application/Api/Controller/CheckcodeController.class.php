<?php
namespace Api\Controller;

use Think\Controller;

class CheckcodeController extends Controller
{
	public function index()
	{
		$length = 4;
		if (isset($_GET['length']) && intval($_GET['length']) > 2) {
			$length = intval($_GET['length']);
		}
		$code_set = "";
		if (!empty($_GET['charset'])) {
			$mletters = str_split($_GET['charset']);
			$mletters = array_unique($mletters);
			if (count($mletters) > 5) {
				$code_set = trim($_GET['charset']);
			}
		}
		$use_noise = 1;
		if (isset($_GET['use_noise'])) {
			$use_noise = intval($_GET['use_noise']);
		}
		$use_curve = 1;
		if (isset($_GET['use_curve'])) {
			$use_curve = intval($_GET['use_curve']);
		}
		$font_size = 25;
		if (isset($_GET['font_size']) && intval($_GET['font_size'])) {
			$font_size = intval($_GET['font_size']);
		}
		$width = 0;
		if (isset($_GET['width']) && intval($_GET['width'])) {
			$width = intval($_GET['width']);
		}
		$height = 0;
		if (isset($_GET['height']) && intval($_GET['height'])) {
			$height = intval($_GET['height']);
		}
		$background = array(243, 251, 254);
		if (isset($_GET['background'])) {
			$mbackground = array_map('intval', explode(',', $_GET['background']));
			if (count($mbackground) > 2 && $mbackground[0] <= 255 && $mbackground[1] <= 255 && $mbackground[2] <= 255) {
				$background = array($mbackground[0], $mbackground[1], $mbackground[2]);
			}
		}
		$config = array('codeSet' => !empty($code_set) ? $code_set : "2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY", 'expire' => 1800, 'useImgBg' => false, 'fontSize' => !empty($font_size) ? $font_size : 25, 'useCurve' => $use_curve === 0 ? false : true, 'useNoise' => $use_noise === 0 ? false : true, 'imageH' => $height, 'imageW' => $width, 'length' => !empty($length) ? $length : 4, 'bg' => $background, 'reset' => true,);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}
} 