<?php
namespace Portal\Lib\TagLib;

use Think\Template\TagLib;

class Portal extends TagLib
{
	protected $tags = array('articles' => array('attr' => 'cid,field,limit,pagesize,pagename,where,order', 'level' => 3),);

	public function _articles($tag, $content)
	{
		$field = !empty($tag['field']) ? $tag['field'] : '*';
		$limit = !empty($tag['limit']) ? $tag['limit'] : '10';
		$order = !empty($tag['order']) ? $tag['order'] : 'post_modified desc';
		$pagesize = !empty($tag['pagesize']) ? $tag['pagesize'] : '0';
		$pagetpl = !empty($tag['pagetpl']) ? $tag['pagetpl'] : '{first}{prev}{liststart}{list}{listend}{next}{last}';
		$item = !empty($tag['item']) ? $tag['item'] : 'vo';
		$key = !empty($tag['key']) ? $tag['key'] : 'key';
		$where['status'] = array('eq', 1);
		$where['post_status'] = array('eq', 1);
		if (isset($tag['cid'])) {
			$where['term_id'] = array('in', $tag['cid']);
		}
		$where = var_export($where, true);
		$parseStr = "<?php \$posts=sp_posts('field:$field;limit:$limit;order:$order;',$where,$pagesize,'','$pagetpl');\$articles=\$posts['posts']?>\n";
		$parseStr .= '<?php if(is_array($articles)): foreach($articles as $' . $key . '=>$' . $item . '): ?>';
		$parseStr .= $this->tpl->parse($content);
		$parseStr .= '<?php endforeach; endif; ?>';
		if (!empty($parseStr)) {
			return $parseStr;
		}
		return;
	}
} 