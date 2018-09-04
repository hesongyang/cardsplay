<?php
namespace Comment\Controller;

use Think\Controller;

class WidgetController extends Controller
{
	public function index($table, $post_id, $params)
	{
		$comment_model = D("Common/Comments");
		$comments = $comment_model->where(array("post_table" => $table, "post_id" => $post_id, "status" => 1))->order("createtime ASC")->select();
		$new_comments = array();
		$parent_comments = array();
		if (!empty($comments)) {
			foreach ($comments as $m) {
				if ($m['parentid'] == 0) {
					$new_comments[$m['id']] = $m;
				} else {
					$path = explode("-", $m['path']);
					$new_comments[$path[1]]['children'][] = $m;
				}
				$parent_comments[$m['id']] = $m;
			}
		}
		$data['post_table'] = sp_authencode($table);
		$data['post_id'] = $post_id;
		$this->assign($data);
		$this->assign("comments", $new_comments);
		$this->assign("params", $params);
		$this->assign("parent_comments", $parent_comments);
		$tpl = (isset($params['tpl']) && !empty($params['tpl'])) ? $params['tpl'] : "comment";
		return $this->fetch(":$tpl");
	}

	public function fetch($templateFile = '', $content = '', $prefix = '')
	{
		return parent::fetch($this->parseTemplate($templateFile), $content, $prefix);
	}

	public function parseTemplate($template = '')
	{
		$depr = C('TMPL_FILE_DEPR');
		$template = str_replace(':', $depr, $template);
		$module = "Comment";
		$controller = "Widget";
		if ('' == $template) {
			$template = $controller . $depr . 'comment';
		} elseif (false === strpos($template, '/')) {
			$template = $controller . $depr . $template;
		}
		$templateFile = sp_add_template_file_suffix(THEME_PATH . $module . $template);
		if (!file_exists_case($templateFile)) throw_exception(L('_TEMPLATE_NOT_EXIST_') . '[' . $templateFile . ']');
		return $templateFile;
	}
}