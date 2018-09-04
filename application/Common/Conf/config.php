<?php
if (file_exists("data/conf/db.php")) {
	$db = include "data/conf/db.php";
} else {
	$db = array();
}
if (file_exists("data/conf/config.php")) {
	$runtime_config = include "data/conf/config.php";
} else {
	$runtime_config = array();
}
if (file_exists("data/conf/route.php")) {
	$routes = include 'data/conf/route.php';
} else {
	$routes = array();
}
$configs = array("LOAD_EXT_FILE" => "extend", 'UPLOADPATH' => 'data/upload/', 'SHOW_PAGE_TRACE' => false, 'TMPL_STRIP_SPACE' => true, 'THIRD_UDER_ACCESS' => false, 'TAGLIB_BUILD_IN' => THINKCMF_CORE_TAGLIBS, 'MODULE_ALLOW_LIST' => array('Admin', 'Portal', 'Asset', 'Api', 'User', 'Wx', 'Comment', 'Qiushi', 'Tpl', 'Topic', 'Install', 'Bug', 'Better', 'Pay', 'Cas'), 'TMPL_DETECT_THEME' => false, 'TMPL_TEMPLATE_SUFFIX' => '.html', 'DEFAULT_MODULE' => 'Portal', 'DEFAULT_CONTROLLER' => 'Index', 'DEFAULT_ACTION' => 'index', 'DEFAULT_M_LAYER' => 'Model', 'DEFAULT_C_LAYER' => 'Controller', 'DEFAULT_FILTER' => 'htmlspecialchars', 'LANG_SWITCH_ON' => true, 'DEFAULT_LANG' => 'zh-cn', 'LANG_LIST' => 'zh-cn,en-us,zh-tw', 'LANG_AUTO_DETECT' => true, 'ADMIN_LANG_SWITCH_ON' => false, 'VAR_MODULE' => 'g', 'VAR_CONTROLLER' => 'm', 'VAR_ACTION' => 'a', 'APP_USE_NAMESPACE' => true, 'APP_AUTOLOAD_LAYER' => 'Controller,Model', 'SP_TMPL_PATH' => 'themes/', 'SP_DEFAULT_THEME' => 'simplebootx', 'SP_TMPL_ACTION_ERROR' => 'error', 'SP_TMPL_ACTION_SUCCESS' => 'success', 'SP_ADMIN_STYLE' => 'flat', 'SP_ADMIN_TMPL_PATH' => 'admin/themes/', 'SP_ADMIN_DEFAULT_THEME' => 'simplebootx', 'SP_ADMIN_TMPL_ACTION_ERROR' => 'Admin/error.html', 'SP_ADMIN_TMPL_ACTION_SUCCESS' => 'Admin/success.html', 'AUTOLOAD_NAMESPACE' => array('plugins' => './plugins/'), 'ERROR_PAGE' => '', 'VAR_SESSION_ID' => 'session_id', "UCENTER_ENABLED" => 0, "COMMENT_NEED_CHECK" => 0, "COMMENT_TIME_INTERVAL" => 60, 'URL_CASE_INSENSITIVE' => true, 'URL_MODEL' => 0, 'URL_PATHINFO_DEPR' => '/', 'URL_HTML_SUFFIX' => '', 'VAR_PAGE' => "p", 'URL_ROUTER_ON' => true, 'URL_ROUTE_RULES' => $routes, 'OUTPUT_ENCODE' => true, 'HTML_CACHE_ON' => false, 'HTML_CACHE_TIME' => 60, 'HTML_FILE_SUFFIX' => '.html', 'TMPL_PARSE_STRING' => array('__UPLOAD__' => __ROOT__ . '/data/upload/', '__STATICS__' => __ROOT__ . '/statics/', '__WEB_ROOT__' => __ROOT__));
return array_merge($configs, $db, $runtime_config); 