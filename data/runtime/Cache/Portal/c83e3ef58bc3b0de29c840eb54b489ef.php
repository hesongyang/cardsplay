<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>道游互娱</title>
    <link rel="stylesheet" type="text/css" href="/static/css/dasheng_fangka.css"/>
    <link rel="stylesheet" type="text/css" href="/static/css/pai9load.css"/>
</head>
<body>
<div class="pai9load">
    <div class="touzi-bg"></div>
    <div class="touzi"></div>
</div>
<div class="wrap-bg">
    <div class="container">
        <div class="user-room-card">
            我的房卡：<?php echo ($user["fk"]); ?>张
        </div>
        <div class="room-card-in flex-cont">
            <input class="flex-item" type="number" name="card-number" placeholder="输入房卡" />
            <div>
                张
            </div>
        </div>
        <div class="room-card-number">
            <strong id="show-num">0</strong>张
        </div>
        <button class="making" onclick="shengchengfangka();">制作红包</button>
        <div class="phone-bind">未绑定手机</div>
        <div class="card-info">
            制作红包后，将页面分享给微信好友，即可领取。在红包记录中科查看所有红包。
        </div>
        <div class="user-card-record">
            <a href="">我的红包记录</a>
        </div>
        <a class="return" href="/portal/index/daoyou"></a>
    </div>
</div>
<script type="text/javascript" src="/static/js/jquery3.2.1.min.js"></script>
<script type="text/javascript" src="/static/js/dasheng.js"></script>
<script type="text/javascript">
    $("[name=card-number]").keyup(function () {
        $("#show-num").html($(this).val());
    });
    function shengchengfangka(){
        var number = $('[name=card-number]').val();
        if(number==''){
            return false;
        }
        var updataimgurl = '/index.php/portal/user/daoyoushengchengfangka';
        $.ajax({
            type:"post",
            data:{ number:number},
            url:updataimgurl,
            dataType: "json",
            success: function(suc){
                console.log(suc);
                if(suc.act=='1'){
                    location.href = '/index.php/portal/user/daoyoufangka_houxu/mis/'+suc.msg;
                }else{
                }
            }
        });

    }
</script>
<script type="text/javascript">
    $(function() {
        $("div.pai9load").remove();
    });
</script>
</body>
</html>