<?php
namespace Asset\Controller;

use Think\Controller;

class UeditorController extends Controller
{
	private $stateMap = array("SUCCESS", "文件大小超出 upload_max_filesize 限制", "文件大小超出 MAX_FILE_SIZE 限制", "文件未被完整上传", "没有文件被上传", "上传文件为空", "ERROR_TMP_FILE" => "临时文件错误", "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件", "ERROR_SIZE_EXCEED" => "文件大小超出网站限制", "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许", "ERROR_CREATE_DIR" => "目录创建失败", "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限", "ERROR_FILE_MOVE" => "文件保存时出错", "ERROR_FILE_NOT_FOUND" => "找不到上传文件", "ERROR_WRITE_CONTENT" => "写入文件内容错误", "ERROR_UNKNOWN" => "未知错误", "ERROR_DEAD_LINK" => "链接不可用", "ERROR_HTTP_LINK" => "链接不是http链接", "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确");

	public function _initialize()
	{
		$adminid = sp_get_current_admin_id();
		$userid = sp_get_current_userid();
		if (empty($adminid) && empty($userid)) {
			exit("非法上传！");
		}
	}

	public function imageManager()
	{
		error_reporting(E_ERROR | E_WARNING);
		$path = 'upload';
		$action = htmlspecialchars($_POST["action"]);
		if ($action == "get") {
			$files = $this->getfiles($path);
			if (!$files) return;
			$str = "";
			foreach ($files as $file) {
				$str .= $file . "ue_separate_ue";
			}
			echo $str;
		}
	}

	function upload()
	{
		error_reporting(E_ERROR);
		header("Content-Type: text/html; charset=utf-8");
		$action = $_GET['action'];
		switch ($action) {
			case 'config':
				$result = $this->_ueditor_config();
				break;
			case 'uploadimage':
			case 'uploadscrawl':
				$result = $this->_ueditor_upload('image');
				break;
			case 'uploadvideo':
				$result = $this->_ueditor_upload('video');
				break;
			case 'uploadfile':
				$result = $this->_ueditor_upload('file');
				break;
			case 'listimage':
				$result = "";
				break;
			case 'listfile':
				$result = "";
				break;
			case 'catchimage':
				$result = $this->_get_remote_image();
				break;
			default:
				$result = json_encode(array('state' => '请求地址出错'));
				break;
		}
		if (isset($_GET["callback"]) && false) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				echo json_encode(array('state' => 'callback参数不合法'));
			}
		} else {
			exit($result);
		}
	}

	private function _get_remote_image()
	{
		$source = array();
		if (isset($_POST['source'])) {
			$source = $_POST['source'];
		} else {
			$source = $_GET['source'];
		}
		$item = array("state" => "", "url" => "", "size" => "", "title" => "", "original" => "", "source" => "");
		$date = date("Ymd");
		$config = array("savePath" => './' . C("UPLOADPATH") . "ueditor/$date/", "allowFiles" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp"), "maxSize" => 3000);
		$storage_setting = sp_get_cmf_settings('storage');
		$qiniu_domain = $storage_setting['Qiniu']['domain'];
		$no_need_domains = array($qiniu_domain);
		$list = array();
		foreach ($source as $imgUrl) {
			$host = str_replace(array('http://', 'https://'), '', $imgUrl);
			$host = explode('/', $host);
			$host = $host[0];
			if (in_array($host, $no_need_domains)) {
				continue;
			}
			$return_img = $item;
			$return_img['source'] = $imgUrl;
			$imgUrl = htmlspecialchars($imgUrl);
			$imgUrl = str_replace("&amp;", "&", $imgUrl);
			if (strpos($imgUrl, "http") !== 0) {
				$return_img['state'] = $this->stateMap['ERROR_HTTP_LINK'];
				array_push($list, $return_img);
				continue;
			}
			if (!sp_is_sae()) {
				$heads = get_headers($imgUrl);
				if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
					$return_img['state'] = $this->stateMap['ERROR_DEAD_LINK'];
					array_push($list, $return_img);
					continue;
				}
			}
			$fileType = strtolower(strrchr($imgUrl, '.'));
			if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
				$return_img['state'] = $this->stateMap['ERROR_HTTP_CONTENTTYPE'];
				array_push($list, $return_img);
				continue;
			}
			ob_start();
			$context = stream_context_create(array('http' => array('follow_location' => false)));
			readfile($imgUrl, false, $context);
			$img = ob_get_contents();
			ob_end_clean();
			$uriSize = strlen($img);
			$allowSize = 1024 * $config['maxSize'];
			if ($uriSize > $allowSize) {
				$return_img['state'] = $this->stateMap['ERROR_SIZE_EXCEED'];
				array_push($list, $return_img);
				continue;
			}
			$file = uniqid() . strrchr($imgUrl, '.');
			$savePath = $config['savePath'];
			$tmpName = $savePath . $file;
			if (!file_exists($savePath)) {
				mkdir("$savePath", 0777, true);
			}
			$file_write_result = sp_file_write($tmpName, $img);
			if ($file_write_result) {
				if (C('FILE_UPLOAD_TYPE') == 'Qiniu') {
					$upload = new \Think\Upload();
					$savename = "ueditor/$date/" . $file;
					$uploader_file = array('savepath' => '', 'savename' => $savename, 'tmp_name' => $tmpName);
					$result = $upload->getUploader()->save($uploader_file);
					if ($result) {
						unlink($tmpName);
						$return_img['state'] = 'SUCCESS';
						$return_img['url'] = sp_get_image_preview_url($savename);
						array_push($list, $return_img);
					} else {
						$return_img['state'] = $this->stateMap['ERROR_WRITE_CONTENT'];
						array_push($list, $return_img);
					}
				}
				if (C('FILE_UPLOAD_TYPE') == 'Local') {
					$file = C("TMPL_PARSE_STRING.__UPLOAD__") . "ueditor/$date/" . $file;
					$return_img['state'] = 'SUCCESS';
					$return_img['url'] = $file;
					array_push($list, $return_img);
				}
			} else {
				$return_img['state'] = $this->stateMap['ERROR_WRITE_CONTENT'];
				array_push($list, $return_img);
			}
		}
		return json_encode(array('state' => count($list) ? 'SUCCESS' : 'ERROR', 'list' => $list));
	}

	private function _ueditor_config()
	{
		$config_text = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./public/js/ueditor/config.json"));
		$config = json_decode($config_text, true);
		$upload_setting = sp_get_upload_setting();
		$config['imageMaxSize'] = $upload_setting['image']['upload_max_filesize'] * 1024;
		$config['imageAllowFiles'] = array_map(array($this, '_ueditor_extension'), explode(",", $upload_setting['image']['extensions']));
		$config['scrawlMaxSize'] = $upload_setting['image']['upload_max_filesize'] * 1024;
		$config['catcherMaxSize'] = $upload_setting['image']['upload_max_filesize'] * 1024;
		$config['catcherAllowFiles'] = array_map(array($this, '_ueditor_extension'), explode(",", $upload_setting['image']['extensions']));
		$config['videoMaxSize'] = $upload_setting['video']['upload_max_filesize'] * 1024;
		$config['videoAllowFiles'] = array_map(array($this, '_ueditor_extension'), explode(",", $upload_setting['video']['extensions']));
		$config['fileMaxSize'] = $upload_setting['file']['upload_max_filesize'] * 1024;
		$config['fileAllowFiles'] = array_map(array($this, '_ueditor_extension'), explode(",", $upload_setting['file']['extensions']));
		return json_encode($config);
	}

	public function _ueditor_extension($str)
	{
		return "." . trim($str, '.');
	}

	private function _ueditor_upload($filetype = 'image')
	{
		$upload_setting = sp_get_upload_setting();
		$file_extension = sp_get_file_extension($_FILES['upfile']['name']);
		$upload_max_filesize = $upload_setting['upload_max_filesize'][$file_extension];
		$upload_max_filesize = empty($upload_max_filesize) ? 2097152 : $upload_max_filesize;
		$allowed_exts = explode(',', $upload_setting[$filetype]);
		$date = date("Ymd");
		$config = array('rootPath' => './' . C("UPLOADPATH"), 'savePath' => "ueditor/$date/", 'maxSize' => $upload_max_filesize, 'saveName' => array('uniqid', ''), 'exts' => $allowed_exts, 'autoSub' => false,);
		$upload = new \Think\Upload($config);
		$file = $title = $oriName = $state = '0';
		$info = $upload->upload();
		if ($info) {
			$title = $oriName = $_FILES['upfile']['name'];
			$first = array_shift($info);
			$size = $first['size'];
			$state = 'SUCCESS';
			if (!empty($first['url'])) {
				if ($filetype == 'image') {
					$url = sp_get_image_preview_url($first['savepath'] . $first['savename']);
				} else {
					$url = sp_get_file_download_url($first['savepath'] . $first['savename'], 3600 * 24 * 365 * 50);
				}
			} else {
				$url = C("TMPL_PARSE_STRING.__UPLOAD__") . $first['savepath'] . $first['savename'];
			}
		} else {
			$state = $upload->getError();
		}
		$response = array("state" => $state, "url" => $url, "title" => $title, "original" => $oriName,);
		return json_encode($response);
	}
}