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
            <li class="active"><a href="#">房卡操作</a></li>
            <!--<li><a href="<?php echo U('AdminUser/add');?>"></a></li>-->
        </ul>
        <form class="well form-search" method="post" action="<?php echo U('Portal/AdminUser/editMoneyPost');?>">
            
            类型： 
            <select name="wallet" style="width: 120px;">
                    <option value='fk'>房卡</option>
                    <option value="daycard">天卡</option>
            </select> &nbsp;&nbsp;
            会员ID：
            <input type="text" name="user_login" style="width: 200px;" value="" required placeholder="请输入会员ID">
            数量:
            <input type="number" name="number" style="width: 200px;" value="" required placeholder="请输入数量">
             &nbsp;&nbsp;
             操作方式： 
            <select name="type" style="width: 120px;">
                    <option value='1'>赠送</option>
                    <option value='2'>扣除</option>
            </select> &nbsp;&nbsp;
            <input type="submit" class="btn btn-primary js-ajax-submit" value="提交" />
            <!--<a class="btn btn-danger" href="<?php echo U('User/index');?>">清空</a>-->
        </form>
            <table class="table table-hover table-bordered table-list">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>会员</th>
                        <th>数量</th>
                        <th>类型</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                <?php $wallet['fk']="房卡"; $wallet['daycard']="天卡"; ?>
                <?php if(is_array($record)): foreach($record as $key=>$vo): ?><tr>
                        <td><?php echo ($vo["id"]); ?></td>
                        <td><?php echo ($vo["user_login"]); ?></td>
                        <td><?php echo ($vo["number"]); ?></td>
                        <td><?php echo ($wallet[$vo['wallet']]); ?></td>
                        <td><?php echo ($vo["create_time"]); ?></td>
                    </tr><?php endforeach; endif; ?>


                </tbody>

            </table>
            <div class="pagination"><?php echo ($page); ?></div>
    </div>
    <script src="/public/js/common.js"></script>
    
</body>
</html>