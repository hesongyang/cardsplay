<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->

	<link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
    <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
		form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
		.table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
		.table-list{margin-bottom: 0px;}
	</style>
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
	<script type="text/javascript">
	//全局变量
	var GV = {
	    ROOT: "/",
	    WEB_ROOT: "/",
	    JS_ROOT: "public/js/",
	    APP:'<?php echo (MODULE_NAME); ?>'/*当前应用名*/
	};
	</script>
    <script src="/public/js/jquery.js"></script>
    <script src="/public/js/wind.js"></script>
    <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
    <script>
    	$(function(){
    		$("[data-toggle='tooltip']").tooltip();
    	});
    </script>
<?php if(APP_DEBUG): ?><style>
		#think_page_trace_open{
			z-index:9999;
		}
	</style><?php endif; ?>
</head>
<body>
    <div class="wrap">
        <ul class="nav nav-tabs">
            <li class="active"><a href="<?php echo U('AdminServer/index');?>">服务器列表</a></li>
        </ul>
        <form class="well form-search" method="post" action="">
            类型： 
            <select name="type" style="width: 120px;" class="status"  id="type" >
                <option value="0">大厅服务器</option>
                <?php $map['zt']='1'; $gamelist=M('game')->where($map)->select(); ?>
                <?php if(is_array($gamelist)): foreach($gamelist as $key=>$one): ?><option value="<?php echo ($one["id"]); ?>"><?php echo ($one["name"]); ?>服务器</option><?php endforeach; endif; ?>
            </select> &nbsp;&nbsp;
            名称：
            <input type="text" name="title" id="title"  style="width: 200px;">
            端口: 
            <input type="text" name="dk" id="dk" style="width: 200px;" value="" placeholder="请输端口号...">
            <input type="button" class="btn btn-primary" value="添加" onclick="addserver()">
        </form>
            <table class="table table-hover table-bordered table-list">
                <thead>
                    <tr>
                        <th>服务器id</th>
                        <th>服务器名称</th>
                        <th>服务器类型</th>
                        <th>服务器状态</th>
                        <th>房间数量</th>
                        <th>端口号</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="list">
                </tbody>

            </table>
    </div>
      <script src="/index.php/admin/index/serverjs" type="text/javascript"></script>  
    <script>
    var websocket = new WebSocket("ws://<?php echo $_SERVER['HTTP_HOST']; ?>:"+dkxx);
    websocket.onmessage = function(event) {
        zdata=JSON.parse(event.data);
        console.log(zdata);
        window[zdata.act](zdata.msg);
    };
    websocket.onopen = function(event) {
            list();
    }
    function addserver(){
        var fs = {};
        fs.title=$("#title").val();
        fs.dk=$("#dk").val();
        fs.type=$("#type").val();
        fs.act='add';
        var jsonStr = JSON.stringify(fs);
        websocket.send(jsonStr);
    }
    function openserver(id){
        var fs = {};
        fs.id=id;
        fs.act='open';
        var jsonStr = JSON.stringify(fs);
        websocket.send(jsonStr);

    }
    function closeserver(id){
        var fs = {};
        fs.id=id;
        fs.act='close';
        var jsonStr = JSON.stringify(fs);
        websocket.send(jsonStr);

    }
    function del(id){
        var fs = {};
        fs.id=id;
        fs.act='del';
        var jsonStr = JSON.stringify(fs);
        websocket.send(jsonStr);
    }
    function list(){
        var fs = {};
        fs.act='init';
        fs.key='zmm321';
        fs.next='list';
        var jsonStr = JSON.stringify(fs);
        websocket.send(jsonStr);
    }
    function html(data){
        $('#list').html(data);
    }
    function error(data){
            alert(data);
    }
    function success(data){
            if(data){
                alert(data);
                $("#type").val(0);
                $("#dk").val('');
                $("#title").val('');
            }
            list();
    }
    </script>
    <script src="/public/js/common.js"></script>
</body>
</html>