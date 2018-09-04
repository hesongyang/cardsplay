<?php
namespace Portal\Service;
class ApiService
{
	public static function posts($tag, $where = array(), $pagesize = 0, $pagetpl = '')
	{
		$where = is_array($where) ? $where : array();
		$tag = sp_param_lable($tag);
		$field = !empty($tag['field']) ? $tag['field'] : '*';
		$limit = !empty($tag['limit']) ? $tag['limit'] : '0,10';
		$order = !empty($tag['order']) ? $tag['order'] : 'post_date DESC';
		$where['term_relationships.status'] = array('eq', 1);
		$where['posts.post_status'] = array('eq', 1);
		if (isset($tag['cid'])) {
			$tag['cid'] = explode(',', $tag['cid']);
			$tag['cid'] = array_map('intval', $tag['cid']);
			$where['term_relationships.term_id'] = array('in', $tag['cid']);
		}
		if (isset($tag['ids'])) {
			$tag['ids'] = explode(',', $tag['ids']);
			$tag['ids'] = array_map('intval', $tag['ids']);
			$where['term_relationships.object_id'] = array('in', $tag['ids']);
		}
		if (isset($tag['where'])) {
			$where['_string'] = $tag['where'];
		}
		$join = '__POSTS__ as posts on term_relationships.object_id = posts.id';
		$join2 = '__USERS__ as users on posts.post_author = users.id';
		$term_relationships_model = M("TermRelationships");
		$content = array();
		if (empty($pagesize)) {
			$posts = $term_relationships_model->alias("term_relationships")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($limit)->select();
		} else {
			$pagetpl = empty($pagetpl) ? '{first}{prev}{liststart}{list}{listend}{next}{last}' : $pagetpl;
			$totalsize = $term_relationships_model->alias("term_relationships")->join($join)->join($join2)->field($field)->where($where)->count();
			$pagesize = intval($pagesize);
			$page_param = C("VAR_PAGE");
			$page = new \Page($totalsize, $pagesize);
			$page->setLinkWraper("li");
			$page->__set("PageParam", $page_param);
			$pagesetting = array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => "");
			$page->SetPager('default', $pagetpl, $pagesetting);
			$posts = $term_relationships_model->alias("term_relationships")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($page->firstRow, $page->listRows)->select();
			$content['page'] = $page->show('default');
			$content['count'] = $totalsize;
		}
		$content['posts'] = $posts;
		return $content;
	}

	public static function postsNotPaged($tag, $where = array())
	{
		$content = self::posts($tag, $where);
		return $content['posts'];
	}

	public static function postsByTermId($term_id, $tag, $where = array())
	{
		$term_id = intval($term_id);
		if (!is_array($where)) {
			$where = array();
		}
		$term_ids = array();
		$term_ids = M("Terms")->where("status=1 and ( term_id=$term_id OR path like '%-$term_id-%' )")->order('term_id asc')->getField('term_id', true);
		if (!empty($term_ids)) {
			$where['term_relationships.term_id'] = array('in', $term_ids);
		}
		$content = self::posts($tag, $where);
		return $content['posts'];
	}

	public static function postsPaged($tag, $pagesize = 20, $pagetpl = '')
	{
		return self::posts($tag, array(), $pagesize, $pagetpl);
	}

	public static function postsPagedByTermId($term_id, $tag, $pagesize = 20, $pagetpl = '')
	{
		$term_id = intval($term_id);
		$term_ids = array();
		$where = array();
		$term_ids = M("Terms")->field("term_id")->where("status=1 and ( term_id=$term_id OR path like '%-$term_id-%' )")->order('term_id asc')->getField('term_id', true);
		if (!empty($term_ids)) {
			$where['term_relationships.term_id'] = array('in', $term_ids);
		}
		$content = self::posts($tag, $where, $pagesize, $pagetpl);
		return $content;
	}

	public static function postsPagedByKeyword($keyword, $tag, $pagesize = 20, $pagetpl = '')
	{
		$where = array();
		$where['posts.post_title'] = array('like', "%$keyword%");
		$content = self::posts($tag, $where, $pagesize, $pagetpl);
		return $content;
	}

	public static function post($post_id, $tag)
	{
		$where = array();
		$tag = sp_param_lable($tag);
		$field = !empty($tag['field']) ? $tag['field'] : '*';
		$where['post_status'] = array('eq', 1);
		$where['id'] = array('eq', $post_id);
		$post = M('Posts')->field($field)->where($where)->find();
		return $post;
	}

	public static function pages($tag, $where = array())
	{
		if (!is_array($where)) {
			$where = array();
		}
		$tag = sp_param_lable($tag);
		$field = !empty($tag['field']) ? $tag['field'] : '*';
		$limit = !empty($tag['limit']) ? $tag['limit'] : '0,10';
		$order = !empty($tag['order']) ? $tag['order'] : 'post_date DESC';
		$where['post_status'] = array('eq', 1);
		$where['post_type'] = array('eq', 2);
		if (isset($tag['ids'])) {
			$tag['ids'] = explode(',', $tag['ids']);
			$tag['ids'] = array_map('intval', $tag['ids']);
			$where['id'] = array('in', $tag['ids']);
		}
		if (isset($tag['where'])) {
			$where['_string'] = $tag['where'];
		}
		$posts_model = M("Posts");
		$pages = $posts_model->field($field)->where($where)->order($order)->limit($limit)->select();
		return $pages;
	}

	public static function page($id)
	{
		$where = array();
		$where['id'] = array('eq', $id);
		$where['post_type'] = array('eq', 2);
		$posts_model = M("Posts");
		$post = $posts_model->where($where)->find();
		return $post;
	}

	public static function term($term_id)
	{
		$terms = F('all_terms');
		if (empty($terms)) {
			$terms_model = M("Terms");
			$terms = $terms_model->where("status=1")->select();
			$mterms = array();
			foreach ($terms as $t) {
				$tid = $t['term_id'];
				$mterms["t$tid"] = $t;
			}
			F('all_terms', $mterms);
			return $mterms["t$term_id"];
		} else {
			return $terms["t$term_id"];
		}
	}

	public static function child_terms($term_id)
	{
		$term_id = intval($term_id);
		$terms_model = M("Terms");
		$terms = $terms_model->where("status=1 and parent=$term_id")->order("listorder asc")->select();
		return $terms;
	}

	public static function all_child_terms($term_id)
	{
		$term_id = intval($term_id);
		$terms_model = M("Terms");
		$terms = $terms_model->where("status=1 and path like '%-$term_id-%'")->order("listorder asc")->select();
		return $terms;
	}

	public static function terms($tag, $where = array())
	{
		if (!is_array($where)) {
			$where = array();
		}
		$tag = sp_param_lable($tag);
		$field = !empty($tag['field']) ? $tag['field'] : '*';
		$limit = !empty($tag['limit']) ? $tag['limit'] : '';
		$order = !empty($tag['order']) ? $tag['order'] : 'term_id';
		$where['status'] = array('eq', 1);
		if (isset($tag['ids'])) {
			$tag['ids'] = explode(',', $tag['ids']);
			$tag['ids'] = array_map('intval', $tag['ids']);
			$where['term_id'] = array('in', $tag['ids']);
		}
		if (isset($tag['where'])) {
			$where['_string'] = $tag['where'];
		}
		$terms_model = M("Terms");
		$terms = $terms_model->field($field)->where($where)->order($order)->limit($limit)->select();
		return $terms;
	}

	public static function breadcrumb($term_id)
	{
		$terms_model = M("Terms");
		$data = array();
		$path = $terms_model->where(array('term_id' => $term_id))->getField('path');
		if (!empty($path)) {
			$parents = explode('-', $path);
			array_pop($parents);
			if (!empty($parents)) {
				$data = $terms_model->where(array('term_id' => array('in', $parents)))->order('path ASC')->select();
			}
		}
		return $data;
	}
}