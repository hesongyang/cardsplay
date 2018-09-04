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
            <li class="active"><a href="#B" data-toggle="tab">微信设置</a></li>
        </ul>
        <form class="form-horizontal js-ajax-forms" action="<?php echo U('AdminBonus/extractPost');?>" method="post">
            <fieldset>
                <div class="tabbable">
                    <div class="tab-content">
                        <div class="tab-pane active" id="B">

                          <fieldset>
                                
                                <div class="control-group">
                                    <label class="control-label">大厅名字</label>
                                    <div class="controls">
                                        <input type="text" name="options[skin_name]" value="<?php echo ($skin_name); ?>">
                                    </div>
                                </div>


                                <div class="control-group">
                                    <label class="control-label">微信appid</label>
                                    <div class="controls">
                                        <input type="text" name="options[weixin_appid]" value="<?php echo ($weixin_appid); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">微信key</label>
                                    <div class="controls">
                                        <input type="text" name="options[weixin_key]" value="<?php echo ($weixin_key); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">微信access_token(不需要填写自动获取)</label>
                                    <div class="controls">
                                        <input type="text" name="options[access_token]" value="<?php echo ($access_token); ?>" readonly><button type="button" class="btn btn-primary" onclick="location.href='<?php echo U('/Portal/Adminweixin/access_token');?>'">获取</button>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">微信jsapi_ticket(不需要填写自动获取)</label>
                                    <div class="controls">
                                        <input type="text" name="options[jsapi_ticket]" value="<?php echo ($jsapi_ticket); ?>" readonly>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">微信access_tokens时间</label>
                                    <div class="controls">
                                        <input type="text" name="options[access_token_time]" value="<?php echo ($access_token_time); ?>" readonly>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">微信jsapi_ticket时间</label>
                                    <div class="controls">
                                        <input type="text" name="options[jsapi_ticket_time]" value="<?php echo ($jsapi_ticket_time); ?>" readonly>
                                    </div>
                                </div>

                            </fieldset>

                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary  js-ajax-submit"><?php echo L("SAVE");?></button>
                </div>
            </fieldset>
        </form>

    </div>
    <script type="text/javascript" src="/public/js/common.js"></script>
    <script>
        /////---------------------
        $(function() {
            $("#urlmode-select").change(function() {
                if ($(this).val() == 1) {
                    alert("更改后，若发现前台链接不能正常访问，可能是您的服务器不支持PATHINFO，请先修改data/conf/config.php文件的URL_MODEL为0保证网站正常运行,在配置服务器PATHINFO功能后再更新为PATHINFO模式！");
                }

                if ($(this).val() == 2) {
                    alert("更改后，若发现前台链接不能正常访问，可能是您的服务器不支持REWRITE，请先修改data/conf/config.php文件的URL_MODEL为0保证网站正常运行，在开启服务器REWRITE功能后再更新为REWRITE模式！");
                }
            });
            $("#js-site-admin-url-password").change(function() {
                $(this).data("changed", true);
            });
        });
        Wind.use('validate', 'ajaxForm', 'artDialog', function() {
            //javascript
            var form = $('form.js-ajax-forms');
            //ie处理placeholder提交问题
            if ($.browser && $.browser.msie) {
                form.find('[placeholder]').each(function() {
                    var input = $(this);
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });
            }
            //表单验证开始
            form.validate({
                //是否在获取焦点时验证
                onfocusout: false,
                //是否在敲击键盘时验证
                onkeyup: false,
                //当鼠标掉级时验证
                onclick: false,
                //验证错误
                showErrors: function(errorMap, errorArr) {
                    //errorMap {'name':'错误信息'}
                    //errorArr [{'message':'错误信息',element:({})}]
                    try {
                        $(errorArr[0].element).focus();
                        art.dialog({
                            id: 'error',
                            icon: 'error',
                            lock: true,
                            fixed: true,
                            background: "#CCCCCC",
                            opacity: 0,
                            content: errorArr[0].message,
                            cancelVal: "<?php echo L('OK');?>",
                            cancel: function() {
                                $(errorArr[0].element).focus();
                            }
                        });
                    } catch (err) {
                    }
                },
                //验证规则
                rules: {
                    'options[site_name]': {
                        required: 1
                    },
                    'options[site_host]': {
                        required: 1
                    },
                    'options[site_root]': {
                        required: 1
                    }
                },
                //验证未通过提示消息
                messages: {
                    'options[site_name]': {
                        required: "<?php echo L('WEBSITE_SITE_NAME_REQUIRED_MESSAGE');?>"
                    },
                    'options[site_host]': {
                        required: "<?php echo L('WEBSITE_SITE_HOST_REQUIRED_MESSAGE');?>"
                    }
                },
                //给未通过验证的元素加效果,闪烁等
                highlight: false,
                //是否在获取焦点时验证
                onfocusout : false,
                        //验证通过，提交表单
                        submitHandler: function(forms) {
                            $(forms).ajaxSubmit({
                                url: form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                                dataType: 'json',
                                beforeSubmit: function(arr, $form, options) {

                                },
                                success: function(data, statusText, xhr, $form) {
                                    if (data.status) {
                                        setCookie("refersh_time", 1);
                                        var admin_url_changed = $("#js-site-admin-url-password").data("changed");
                                        var message = admin_url_changed ? data.info + '<br><span style="color:red;">后台地址已更新(请劳记！)</span>' : data.info;

                                        //添加成功
                                        Wind.use("artDialog", function() {
                                            art.dialog({
                                                id: "succeed",
                                                icon: "succeed",
                                                fixed: true,
                                                lock: true,
                                                background: "#CCCCCC",
                                                opacity: 0,
                                                content: message,
                                                button: [{
                                                        name: "<?php echo L('OK');?>",
                                                        callback: function() {
                                                            reloadPage(window);
                                                            return true;
                                                        },
                                                        focus: true
                                                    }, {
                                                        name: "<?php echo L('CLOSE');?>",
                                                        callback: function() {
                                                            reloadPage(window);
                                                            return true;
                                                        }
                                                    }]
                                            });
                                        });
                                    } else {
                                        alert(data.info);
                                    }
                                }
                            });
                        }
            });
        });
        ////-------------------------
    </script>
</body>
</html>