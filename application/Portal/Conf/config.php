<?php
$configs = array('SHOW_PAGE_TRACE' => false, 'TAGLIB_BUILD_IN' => THINKCMF_CORE_TAGLIBS . ',Portal\Lib\Taglib\Portal', 'HTML_CACHE_RULES' => array('article:index' => array('portal/article/{id}', 600), 'index:index' => array('portal/index', 600), 'list:index' => array('portal/list/{id}_{p}', 60)));
return array_merge($configs); 