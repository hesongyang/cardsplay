<?php
namespace Asset\Controller;

use Think\Controller;

class DownloadController extends Controller
{
	function index()
	{
		header("Content-type:text/html;charset=utf-8");
		$unique_id = trim($_GET['key']);
		$asset = M('Asset');
		$line = $asset->where(array('unique' => $unique_id))->find();
		$rel_name = $line['filename'];
		if (!$rel_name) {
			$this->error('未知错误！');
		}
		$file = $line['filepath'] . $line['filename'];
		$file = iconv("utf-8", "gb2312", $file);
		if (!file_exists($file)) {
			$this->error("没有该文件文件");
		}
		$fp = fopen($file, "r");
		$file_size = filesize($file);
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length:" . $file_size);
		Header("Content-Disposition: attachment; filename=" . $rel_name);
		$buffer = 1024;
		$file_count = 0;
		while (!feof($fp) && $file_count < $file_size) {
			$file_con = fread($fp, $buffer);
			$file_count += $buffer;
			echo $file_con;
		}
		$asset->where("_unique='$unique_id'")->setInc('download_times', 1);
		fclose($fp);
	}
} 