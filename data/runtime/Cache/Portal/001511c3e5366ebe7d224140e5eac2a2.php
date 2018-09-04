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
	<div class="wrap js-check-wrap">
		<ul class="nav nav-tabs">
			<li><a href="<?php echo U('AdminUser/index');?>">会员管理</a></li>
			<li class="active"><a href="#">会员编辑</a></li>
		</ul>
		<form class="form-horizontal js-ajax-form" action="<?php echo U('AdminUser/edit_post');?>" method="post">
			<fieldset>
				<!-- <div class="control-group" >
					<label class="control-label">推荐人</label>
					<div class="controls" >
                                            <input type="text" style="color: red" name="parent" placeholder="没有可以不填"  value="<?php echo ($post["parent"]); ?>">
					</div>
				</div> -->
				<!-- 				<div class="control-group">
					<label class="control-label">是否高级会员</label>
					<div class="controls">
						<?php $active_true_checked=($post['is_grade']==1)?"checked":""; ?>
						<label class="radio inline" for="yes">
							<input type="radio" name="is_grade" value="1" <?php echo ($active_true_checked); ?> id="yes"/>是
						</label>
						<?php $active_false_checked=($post['is_grade']==0)?"checked":""; ?>
						<label class="radio inline" for="no">
							<input type="radio" name="is_grade" value="0" id="no"<?php echo ($active_false_checked); ?>>否
						</label>
					</div>
				</div> -->
                <div class="control-group" >
					<label class="control-label">账号</label>
					<div class="controls" >
						<input type="hidden" name="id" value="<?php echo ($post["id"]); ?>">
                        <input type="text" style="color: red" name="user_login" value="<?php echo ($post["user_login"]); ?>" required/>
					</div>
				</div>
				<div class="control-group" >
                    <label class="control-label">头像</label>
                    <div class="controls" >
                       <input type='hidden' name='img' id='thumb' value="<?php echo ((isset($post["img"]) && ($post["img"] !== ""))?($post["img"]):''); ?>">
                        <a href="javascript:upload_one_image('图片上传','#thumb');">
                            <?php if(empty($post['img'])): ?><img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id='thumb-preview' width='135' height='113' style='cursor: hand' />
                            <?php else: ?>
                                <img src="<?php echo sp_get_image_preview_url($post['img']);?>" id='thumb-preview' width='135' height='113' style='cursor: hand' /><?php endif; ?>
                        </a>
                    </div>
                </div>
				 <div class="control-group">
                    <label class="control-label">手机号码</label>
                    <div class="controls">
                        <input type="text" name="mobile" value="<?php echo ($post["mobile"]); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">输赢概率</label>
                    <div class="controls">
                        <input type="text" name="gailv" value="<?php echo ($post["gailv"]); ?>" />
                    </div>
                </div>
                <div class="control-group" >
					<label class="control-label">token</label>
					<div class="controls" >
                        <input type="text" style="color: red" name="token" value="<?php echo ($post["token"]); ?>" required/>
					</div>
				</div>

				<div class="control-group" >
					<label class="control-label">密码</label>
					<div class="controls" >
                        <input type="password" style="color: red" name="password" value="<?php echo ($post["password"]); ?>" required/>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">是否透视</label>
					<div class="controls">
						<?php $active_true_checked=($post['is_grade']==0)?"checked":""; ?>
						<label class="radio inline" for="status_yes2">
							<input type="radio" name="is_grade" value="0" <?php echo ($active_true_checked); ?> id="status_yes2"/>不透视
						</label>
						<?php $active_false_checked=($post['is_grade']==1)?"checked":""; ?>
						<label class="radio inline" for="status_no2">
							<input type="radio" name="is_grade" value="1" id="status_no2"<?php echo ($active_false_checked); ?>>透视
						</label>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">是否开启延迟显示</label>
					<div class="controls">
						<?php $active_true_checked=($post['level']==0)?"checked":""; ?>
						<label class="radio inline" for="status_yes3">
							<input type="radio" name="level" value="0" <?php echo ($active_true_checked); ?> id="status_yes3"/>不显示
						</label>
						<?php $active_false_checked=($post['level']==1)?"checked":""; ?>
						<label class="radio inline" for="status_no3">
							<input type="radio" name="level" value="1" id="status_no3"<?php echo ($active_false_checked); ?>>显示
						</label>
					</div>
				</div>
				
				<div class="control-group" >
					<label class="control-label">功能到期时间：</label>
					<div class="controls" >
                        <input type="text" name="create_time" class="js-datetime" value="<?php echo ((isset($post["create_time"]) && ($post["create_time"] !== ""))?($post["create_time"]):''); ?>" autocomplete="off">
					</div>
				</div>

				<div class="control-group" >
					<label class="control-label">备注:</label>
					<div class="controls" >
                        <input type="text" name="disable_notice"  value="<?php echo ($post["disable_notice"]); ?>" autocomplete="off">
					</div>
				</div>


				<div class="control-group">
					<label class="control-label">会员状态</label>
					<div class="controls">
						<?php $active_true_checked=($post['status']==0)?"checked":""; ?>
						<label class="radio inline" for="status_yes">
							<input type="radio" name="status" value="0" <?php echo ($active_true_checked); ?> id="status_yes"/>正常
						</label>
						<?php $active_false_checked=($post['status']==1)?"checked":""; ?>
						<label class="radio inline" for="status_no">
							<input type="radio" name="status" value="1" id="status_no"<?php echo ($active_false_checked); ?>>管理
						</label>
						<?php $active_false_checked=($post['status']==2)?"checked":""; ?>
						<label class="radio inline" for="active_false">
							<input type="radio" name="status" value="2" id="active_false"<?php echo ($active_false_checked); ?>>限制登录
						</label>
					</div>
				</div>
                           
			</fieldset>
			<div class="form-actions">
				<input type="hidden" name="id" value="<?php echo ($post["id"]); ?>"/>
				<button type="submit" class="btn btn-primary  js-ajax-submit"><?php echo L('SAVE');?></button>
				<a class="btn" href="javascript:history.back(-1);"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>