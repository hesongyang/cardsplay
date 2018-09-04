<?php
use Portal\Service\ApiService;

function sp_sql_posts($tag, $where = array())
{
	return ApiService::postsNotPaged($tag, $where);
}

function sp_posts($tag, $where = array(), $pagesize = 0, $pagetpl = '')
{
	return ApiService::posts($tag, $where, $pagesize, $pagetpl);
}

function sp_sql_posts_bycatid($term_id, $tag, $where = array())
{
	return ApiService::postsByTermId($term_id, $tag, $where);
}

function sp_sql_posts_paged($tag, $pagesize = 20, $pagetpl = '')
{
	return ApiService::postsPaged($tag, $pagesize, $pagetpl);
}

function sp_sql_posts_paged_bykeyword($keyword, $tag, $pagesize = 20, $pagetpl = '{first}{prev}{liststart}{list}{listend}{next}{last}')
{
	return ApiService::postsPagedByKeyword($keyword, $tag, $pagesize, $pagetpl);
}

function sp_sql_posts_paged_bycatid($term_id, $tag, $pagesize = 20, $pagetpl = '')
{
	return ApiService::postsPagedByTermId($term_id, $tag, $pagesize, $pagetpl);
}

function sp_sql_post($post_id, $tag, $where = array())
{
	return ApiService::post($post_id, $tag);
}

function sp_sql_pages($tag, $where = array())
{
	return ApiService::pages($tag, $where);
}

function sp_sql_page($id)
{
	return ApiService::page($id);
}

function sp_get_term($term_id)
{
	return ApiService::term($term_id);
}

function sp_get_child_terms($term_id)
{
	return ApiService::child_terms($term_id);
}

function sp_get_all_child_terms($term_id)
{
	return ApiService::all_child_terms($term_id);
}

function sp_get_terms($tag, $where = array())
{
	return ApiService::terms($tag, $where);
}

function sp_admin_get_tpl_file_list()
{
	$template_path = C("SP_TMPL_PATH") . C("SP_DEFAULT_THEME") . "/Portal/";
	$files = sp_scan_dir($template_path . "*");
	$tpl_files = array();
	foreach ($files as $f) {
		if ($f != "." || $f != "..") {
			if (is_file($template_path . $f)) {
				$suffix = C("TMPL_TEMPLATE_SUFFIX");
				$result = preg_match("/$suffix$/", $f);
				if ($result) {
					$tpl = str_replace($suffix, "", $f);
					$tpl_files[$tpl] = $tpl;
				} else if (preg_match("/\.php$/", $f)) {
					$tpl = str_replace($suffix, "", $f);
					$tpl_files[$tpl] = $tpl;
				}
			}
		}
	}
	return $tpl_files;
}

function sp_get_breadcrumb($term_id)
{
	return ApiService::breadcrumb($term_id);
}