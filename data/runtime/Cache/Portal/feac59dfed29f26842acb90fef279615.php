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
            <li class="active"><a href="<?php echo U('AdminUser/index');?>">所有会员</a></li>
            <!--<li><a href="<?php echo U('AdminUser/addUser');?>">添加会员</a></li>-->
        </ul>
        <form class="well form-search" method="post" action="<?php echo U('Portal/AdminUser/soUser');?>">
            <div>
               用户类别:
                <select name="user_status" style="width: 120px;">
                    <option value="0" >全部</option>
                    <option value="1" <?php if($_GET[user_status] == 1): ?>selected<?php endif; ?> >正常</option>
                    <option value="2" <?php if($_GET[user_status] == 2): ?>selected<?php endif; ?> >管理</option>
                    <option value="3" <?php if($_GET[user_status] == 3): ?>selected<?php endif; ?> >限制登录</option>
                </select>&nbsp;&nbsp;
                筛选:
                <select name="typelx" style="width: 120px;">
                    <option value="0" >全部</option>
                    <option value="1" <?php if($_GET[typelx] == 1): ?>selected<?php endif; ?> >透视</option>
                    <option value="2" <?php if($_GET[typelx] == 2): ?>selected<?php endif; ?> >控制输</option>
                    <option value="3" <?php if($_GET[typelx] == 3): ?>selected<?php endif; ?> >控制赢</option>
                    <option value="4" <?php if($_GET[typelx] == 4): ?>selected<?php endif; ?> >房卡排名</option>
                </select>&nbsp;&nbsp;

            </div>
            <div style="margin-top: 5px;">
                房间号:
                <input type="text" name="room_id" style="width: 100px;" value="<?php echo ($_GET['room_id']); ?>" placeholder="请输入房间号"/>
                用户名:
                <input type="text" name="user_login" style="width: 200px;" value="<?php echo ($_GET['user_login']); ?>" placeholder="请输入用户名">
                昵称:
                <input type="text" name="nickname" style="width: 200px;" value="<?php echo ($_GET['nickname']); ?>" placeholder="请输入昵称">
            </div>
            <div style="margin-top: 5px;">
                <input type="submit" class="btn btn-primary" value="搜索" />
            </div>

            <!--<a class="btn btn-danger" href="<?php echo U('User/index');?>">清空</a>-->
        </form>


        <form class="well form-search" method="post" action="/index.php/Portal/AdminUser/agentlist ">
            代理账号：
            <input type="text" name="parent" style="width: 200px;" value="" required="" placeholder="请输入会员ID">
            下级:
            <input type="text" name="list" style="width: 200px;" value="" required="" placeholder="请输ID">用+号隔开
            <input type="submit" class="btn btn-primary js-ajax-submit" value="确定关系">
        </form>


        <form method="post" class="js-ajax-form" action="<?php echo U('AdminUser/listorders');?>">
            <!--<div class="table-actions">
                <button type="submit" class="btn btn-primary btn-small js-ajax-submit"><?php echo L('SORT');?></button>
            </div>-->
            <table class="table table-hover table-bordered table-list">
                <thead>
                    <tr>
                        <!--<th width="50"><?php echo L('SORT');?></th>-->
                        <th width="50">ID</th>
                        <th>用户名</th>
                        <th>昵称</th>
                        <th>备注</th>
                        <th>头像</th>
                        <th>指定发牌</th>
                        <th>是否透视</th>
                        <th>手机号码</th>
                        <th>房卡数量</th>
                        <th>天卡数量</th>
                        <th>输赢概率</th>
                        <!--<th>token</th>-->
                        <th>到期时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>

                <?php $user_status[0]='正常'; $user_status[1]='管理'; $user_status[2]='限制登录'; $ts[0]='无'; $ts[1]='透视'; ?>
                <?php if(is_array($users)): foreach($users as $key=>$vo): ?><tr>
                        <td><?php echo ($vo["id"]); ?></td>
                        <td><?php echo ($vo["user_login"]); ?></td>
                        <td><?php echo ($vo["nickname"]); ?></td>
                        <td><?php echo ($vo["disable_notice"]); ?></td>
                        <td><img src="<?php echo sp_get_image_preview_url($vo['img']);?>" width='30' height='30'/></td>
                        <td><a href="<?php echo U("AdminUserZdCard/zd",array("id"=>$vo["id"]));?>">指定发牌</a></td>
                        <td><?php echo ($ts[$vo['is_grade']]); ?></td>
                        <td><?php echo ($vo["mobile"]); ?></td>
                        <td><?php echo ($vo["fk"]); ?></td>
                        <td><?php echo ($vo["daycard"]); ?></td>
                        <td><?php echo ($vo["gailv"]); ?></td>
                        <!--<td><?php echo ($vo["token"]); ?></td>-->
                        <td><?php echo ($vo["create_time"]); ?></td>
                        <td><?php echo ($user_status[$vo['status']]); ?></td>
                        <td>
                            <a href="<?php echo U("AdminUser/userroom",array("id"=>$vo["id"]));?>">战绩</a>
                            <a href='<?php echo U("AdminUser/edit",array("id"=>$vo["id"]));?>'><?php echo L('EDIT');?></a>
                            <br/>
                            <a class="js-ajax-delete" href="<?php echo U('adminUser/delete',array('id'=>$vo['id']));?>"><?php echo L('DELETE');?></a> |
                            
                     <!--    <?php if($vo['status'] != 2): ?>| <a href="<?php echo U('adminUser/Limit',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="确认激活">限制登录</a>
                             <?php else: ?>
                             <a href="<?php echo U('adminUser/unLimit',array('id'=>$vo['id']));?>" class="js-ajax-dialog-btn" data-msg="确认解封">解除限制</a><?php endif; ?> -->
                            <a href="/index.php/Portal/index/index/?token=<?php echo ($vo['token']); ?>" target="_blank">登录</a>
                            
                        </td>

                    </tr><?php endforeach; endif; ?>


                </tbody>

            </table>
            <div class="pagination"><?php echo ($page); ?></div>
        </form>
    </div>
    <script src="/public/js/common.js"></script>
</body>
</html>