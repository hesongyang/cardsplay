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
            <li class="active"><a href="#B" data-toggle="tab">联系方式设置</a></li>
        </ul>
        <form class="form-horizontal js-ajax-forms" action="<?php echo U('AdminBonus/bonusPost');?>" method="post">
            <fieldset>
                <div class="tabbable">
                    <div class="tab-content">
                        <div class="tab-pane active" id="B">
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">普通公告标题</label>
                                    <div class="controls">
                                        <input type="text" name="options[ggtitle]" value="<?php echo ($ggtitle); ?>" id="ggtitle" data-val="<?php echo ($ggtitle); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('ggtitle')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">普通公告内容</label>
                                    <div class="controls">
                                        <textarea type="text" name="options[ggbody]" id="ggbody" data-val="<?php echo ($ggbody); ?>"><?php echo ($ggbody); ?></textarea>
                                        <button type="button" class="btn btn-primary" onclick="bhsx('ggbody')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">高级会员公告标题</label>
                                    <div class="controls">
                                        <input type="text" name="options[gj_ggtitle]" value="<?php echo ($gj_ggtitle); ?>" id="gj_ggtitle" data-val="<?php echo ($gj_ggtitle); ?>">
                                        <button type="button" class="btn btn-primary"  onclick="bhsx('gj_ggtitle')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">高级会员公告内容</label>
                                    <div class="controls">
                                        <textarea type="text" name="options[gj_ggbody]" id="gj_ggbody" data-val="<?php echo ($gj_ggbody); ?>"><?php echo ($gj_ggbody); ?></textarea>
                                        <button type="button" class="btn btn-primary" onclick="bhsx('gj_ggbody')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">推送消息</label>
                                    <div class="controls">
                                        <textarea type="text" name="options[tsxx]" id="tsxx" data-val="<?php echo ($tsxx); ?>"><?php echo ($tsxx); ?></textarea>
                                        <button type="button" class="btn btn-primary" onclick="bhsx('tsxx')"> <?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">登录是否显示公告（不显示填0）</label>
                                    <div class="controls">
                                        <input type="text" name="options[sfgg]" value="<?php echo ($sfgg); ?>" id="sfgg" data-val="<?php echo ($sfgg); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('sfgg')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">在线人数</label>
                                    <div class="controls">
                                        <input type="text" name="options[zxonline]" value="<?php echo ($zxonline); ?>" readonly>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">昨日登陆总人数</label>
                                    <div class="controls">
                                        <input type="text" name="options[zronline]" value="<?php echo ($zronline); ?>" readonly>
                                    </div>
                                </div>
                            </fieldset>
                             <fieldset>
                                <div class="control-group">
                                    <label class="control-label">今日登陆总人数</label>
                                    <div class="controls">
                                        <input type="text" name="options[jronline]" value="<?php echo ($jronline); ?>" readonly>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">在线人数更新时间</label>
                                    <div class="controls">
                                        <input type="text" name="options[gxtime]" value="<?php echo ($gxtime); ?>" readonly>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">是否推送（0表示已经推送或不推送）</label>
                                    <div class="controls">
                                        <input type="text" name="options[sfts]" value="<?php echo ($sfts); ?>" id="sfts" data-val="<?php echo ($sfts); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('sfts')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">同ip最大注册数量</label>
                                    <div class="controls">
                                        <input type="text" name="options[ipmax]" value="<?php echo ($ipmax); ?>" id="ipmax" data-val="<?php echo ($ipmax); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('ipmax')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">网址</label>
                                    <div class="controls">
                                        <input type="text" name="options[url]" value="<?php echo ($url); ?>" id="url" data-val="<?php echo ($url); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('url')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                             <fieldset>
                                <div class="control-group">
                                    <label class="control-label">数据保留天数</label>
                                    <div class="controls">
                                        <input type="text" name="options[blts]" value="<?php echo ($blts); ?>" id="blts" data-val="<?php echo ($blts); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('blts')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">计划最大显示行数</label>
                                    <div class="controls">
                                        <input type="text" name="options[xzhs]" value="<?php echo ($xzhs); ?>" id="xzhs" data-val="<?php echo ($xzhs); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('xzhs')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">初始试用时间</label>
                                    <div class="controls">
                                        <input type="text" name="options[sj]" value="<?php echo ($sj); ?>" id="sj" data-val="<?php echo ($sj); ?>">小时
                                        <button type="button" class="btn btn-primary" onclick="bhsx('sj')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">QQ</label>
                                    <div class="controls">
                                        <input type="text" name="options[qq]" value="<?php echo ($qq); ?>" id="qq" data-val="<?php echo ($qq); ?>">
                                        <button type="button" class="btn btn-primary" onclick="bhsx('qq')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div class="control-group">
                                    <label class="control-label">微信图片</label>
                                    <div class="controls">
                                        <input type='hidden' name='options[img]' id='thumb' value="<?php echo ((isset($img) && ($img !== ""))?($img):''); ?>" id="thumb" data-val="<?php echo ((isset($img) && ($img !== ""))?($img):''); ?>">
                                    <a href="javascript:upload_one_image('图片上传','#thumb');">
                                        <?php if(empty($img)): ?><img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id='thumb-preview' width='135' height='113' style='cursor: hand' />
                                        <?php else: ?>
                                            <img src="<?php echo sp_get_image_preview_url($img);?>" id='thumb-preview' width='135' height='113' style='cursor: hand' /><?php endif; ?>
                                    </a>
                                    <input type="button" class="btn btn-small" onclick="$('#thumb-preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb').val('');return false;" value="取消图片" >
                                    <button type="button" class="btn btn-primary" onclick="bhsx('thumb')"><?php echo L("SAVE");?></button>
                                    </div>
                                </div>
                            </fieldset>

                        </div>
                    </div>
                </div>
            </fieldset>
        </form>

    </div>
    <script type="text/javascript" src="/public/js/common.js"></script>

    <script>
    function bhsx(id){
        name=id;
        value=$("#"+id).val();
        if(value==$("#"+id).attr('data-val')){
            alert('请修改后提交');
            return false;
        }
        $.ajax({
            type: "POST",
            url: "/index.php/portal/AdminBonus/bhbonus",
            data: {     
                        name:name,
                        value:value  
                    },
            dataType: "json",
            success: function (data) {
                $("#"+id).attr('data-val',value)
                if(data.ok==1){
                    alert(data.info);
                }
                else{
                    alert(data.info);
                }
            },
            error: function (msg) {
                alert(msg);
            }
        });
    }
    </script>
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