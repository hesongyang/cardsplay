<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
    <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
</head>
<body style="background:#980000 url('/app/chongzhi.jpg');background-size:100%;background-repeat: no-repeat;">
  <div style="width:50%;border-radius:10px;margin:0 auto;padding:10px;background:#fff;margin-top:27%;">
    <p style="text-align:center;">充值</p>
  <img class='gerenzx-yhimg' src="<?php echo ($qrcode); ?>" style="width:100%" />
    <p style="text-align:center;">长按识别二维码付款</p>
    <input type="hidden" value="<?php echo ($order_no); ?>" id="order_no">
    <p style="text-align:center;width:30%;line-height:30px;background:pink;border-radius:10px;margin:0 auto;padding:10px;"><a href="javascript:history.go(-1)" style="text-decoration:none;color:#980000"><b>取消</b></a></p>
  </div>
  </body>
</html>
<script src="/app/js/jquery3.2.1.min.js" type="text/javascript"></script>
<script src="/app/js/app.js" type="text/javascript"></script>

<script>
    //setInterval(mabangding(),100);
    setInterval(function () {
        var order_no=$('#order_no').val();
        $.ajax({
            url:"/index.php/user/pay/OrderRes",    //请求的url地址
            dataType:"json",   //返回格式为json
            //async:true,//请求是否异步，默认为异步，这也是ajax重要特性
            data:{"order_no":order_no},    //参数值
            type:"post",   //请求方式

            success:function(req){

                if(req==1){
                    history.back(-1);
                }
                //请求成功时处理
            }
  //请求出错处理

        });
    },1000)

</script>