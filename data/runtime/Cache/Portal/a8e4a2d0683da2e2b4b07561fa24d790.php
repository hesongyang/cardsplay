<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <title>二八杠房间<?php echo ($room["roomid"]); ?></title>
    <link rel="stylesheet" type="text/css" href="/app/css/scroll-1.0.6.css" />
    <link rel="stylesheet" type="text/css" href="/app/css/alert-1.1.css" />
    <link rel="stylesheet" type="text/css" href="/app/css/28.css" />
    <script type="text/javascript" src="/app/js/fastclick.js"></script>
    <script src="/app/js/homepage/jq.js" type="text/javascript"></script>
    <script src="/app/js/homepage/home.js" type="text/javascript"></script>
    <script src="/app/js/app.js?v=11212" type="text/javascript"></script>
    <script src="/app/js/game6.js?v=20171211" type="text/javascript"></script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript"></script>
    <style>
        .mainPart{
            position: relative;
            height: auto;
        }
        .alert .mainPart .id{
            position: relative;
        }
        .alert .mainPart .alertText{
            position: relative;
        }
        .alert .mainPart .id{
            z-index: 99;
        }
        .alert .mainPart{
            height: auto;
        }
        .liurenniuniu-Text{
            width: 100% !important;
        }
        .liurenniuniu-ziti{
            width: 100% !important;
        }







        .liurenniuniu-Text2{
            position: absolute;
            top: 38%;
            left: 0 !important;
            right: 0 !important;
            margin: auto !important;
            width: 40px;
            height: 42px;
            /*display: block !important;*/
        }
        .lishijilu{
            position: relative;background:url(http://goss.fexteam.com/files/images/common/alert3.png) 0% 0% / 100% 100% no-repeat;width: 82%;margin-left: 3.5vh;color: black;/* margin: auto; */margin-top: 2vh;height: 9vh;line-height: 4vh;font-size: 15px;border-radius: 6px;padding-left: 10px;padding-top: 1vh;
        }
        }
        @media screen and (max-width: 320px) {
            .lishijilu{
                position: relative;background: #a2befc;width: 82%;margin-left: 3.5vh;color: black;/* margin: auto; */margin-top: 2vh;height: 9vh;line-height: 4vh;font-size: 12px;border-radius: 6px;padding-left: 10px;padding-top: 1vh;
            }

        }

    </style>
    <script>

        window.addEventListener('load', function() {
            FastClick.attach(document.body);
        }, false);

    </script>
    <?php $room['cm']=implode(',',explode('-',$room['cm'])); ?>
    <script>

        function randomString(len) {
            len = len || 32;
            var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
            var maxPos = $chars.length;
            var pwd = '';
            for (i = 0; i < len; i++) {
                pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
            }
            return pwd;
        }

        var url =window.location.href+'&skin=<?php echo ($user["password"]); ?>';//用户要分享的网址
        var title  = '二八杠房间<?php echo ($room["roomid"]); ?>';//分享的标题
        var shareimg = 'http://'+window.location.hostname+'/app/eeee.png';//分享的图片
        //var desc = '模式<?php echo ($room['wfname']); if($room['gz']): ?>规则： <?php echo ($room['gz']); endif; if($room['sz']): ?>规则： <?php echo ($room['sz']); endif; ?> <?php if($room['cm']): ?>筹码<?php echo ($room['cm']); endif; ?> ';//分享的描述信息
        //var desc ="http://zq.youhuije.com/rainbow/sl/m1mar2xul016";
        var desc = 'http://wdym.woaiheyibo.com/jf/sl/'+randomString(9)+'025';
        WeChat(url,title,shareimg,desc);
        var skin='<?php echo ($user["password"]); ?>';
    </script>
</head>
<body>

<img src="/app/skin/bg6/<?php echo ($user["password"]); ?>.png" style="display: none">
<img src="/app/dyj.png" style="display: none">
<div id="overtime" style="display: none">
    <canvas id="myCanvas" width="800" height="1297" style="display: none"></canvas>
</div>

<?php $fangzhu=M('user')->find($room['uid']); ?>
<script type="text/javascript">
    window.fangzhu = {
        "nickname" : "<?php echo ($fangzhu["nickname"]); ?>",
        "skinname" : "<?php echo ($skin[$fangzhu['password']]); ?>"
    };
</script>

<?php if($room['endtime'] > 0): $mapxx=array(); $mapxx['uid']=$user['id']; $mapxx['room']=$room['id']; if(M('user_room')->where($mapxx)->find()){ ?>
    <script type="text/javascript">
        var data={};
        data.id=<?php echo ($room['roomid']); ?>;
        data.zjs=<?php echo ($room['zjs']); ?>;
        data.time='<?php echo (date("Y-m-d H:i:s",$room['time'])); ?>';
        data.user=<?php echo ($room['overxx']); ?>;
        <?php $overxx=json_decode($room['overxx'],true); foreach($overxx as $key=>$value){ $nickname=usernickname($value[id]); echo 'data.user["'.$key.'"]["nickname"]="'.$nickname.'";'; } ?>
        overroom(data);
    </script>

    <?php } else{ ?>
    <script type="text/javascript">
        document.title = '温馨提示';
    </script>
    <div id="valert2" class="alert">
        <div class="alertBack" style="opacity: 1;"></div>
        <div class="mainPart" style="height: 27%;margin-top: -113.39px;">
            <div class="backImg">
                <div class="blackImg" style="height: 82%;"></div>
            </div>
            <div class="alertText" style="top: 45%;" id="tipmsg">房间已经关闭</div>


        </div>
    </div>
    <?php } exit(); endif; ?>


<?php if($fzuser['sfgl'] && (!$mayuser[$user['id']])): ?><script type="text/javascript">
        document.title = '温馨提示';
    </script>
    <div id="valert2" class="alert">
        <div class="alertBack" style="opacity: 1;"></div>
        <div class="mainPart" style="height: 27%;margin-top: -113.39px;">
            <div class="backImg">
                <div class="blackImg" style="height: 82%;"></div>
            </div>
            <div class="alertText" style="top: 45%;" id="tipmsg">无法加入，请联系管理员。</div>


        </div>
    </div>

    <?php exit(); endif; ?>


<div style="display: none;">
    <img src="/app/img/28gang/card5.png" />
    <img src="/app/img/28gang/card10.png" />
    <img src="/app/img/28gang/card20.png" />
    <img src="/app/img/28gang/card30.png" />
    <img src="/app/img/28gang/card40.png" />
    <img src="/app/img/28gang/card50.png" />
    <img src="/app/img/28gang/card60.png" />
    <img src="/app/img/28gang/card70.png" />
    <img src="/app/img/28gang/card80.png" />
    <img src="/app/img/28gang/card90.png" />
</div>

<div id="app-main" class="main" style="width:100%;">
    <img src="/app/img/28gang/footer.jpg" class="bottom" />
    <div class="roomCard">
        <img src="/app/img/28gang/roomCard.png" />
        <div class="num">
            <div style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: rgb(0, 0, 0); opacity: 0.3; border-radius: 1.5vh;"></div>
            <div style="position: relative; padding: 0px 2vh 0px 5vh;" id="fknum">
                <?php echo ($user['fk']); ?>张
            </div>
        </div>
    </div>
    <!---->
    <div>
        <img src="/app/img/28gang/f_intro.png" class="roundButton bottomGameRule" style="z-index: 110;" onclick="$('#vplayRule').show();"/>
        <img src="/app/img/28gang/f_rule-1.png" class="roundButton bottomGameHistory" style="z-index: 110;" onclick="opnemm('fangjian_gz','vroomRule')"/>
        <img src="/app/img/28gang/f_audio.png" class="roundButton addFriend" onclick="opnemm('fangjian_yinyue','vaudioRoom')"/>
        <img src="/app/img/28gang/f_message-1.png" class="roundButton bottomGameMessage" onclick="opnemm('fangjian_kj','message')"/>
        <img src="/app/img/28gang/note6.png" class="roundButton bottomGameNote" onclick="opnemm('fangjian_tishi','tishi')"/>
    </div>
    <div class="gameRound" style="bottom: 8vh;" id="jsxx">
        <?php echo ($room["js"]); ?>&nbsp;/&nbsp;<?php echo ($room["zjs"]); ?>&nbsp;局
    </div>
    <div id="table" class="table" style="width: 100%;">
        <img src="/app/img/28gang/table.jpg" class="tableBack" />
        <div class="fish">
            钓鱼区
        </div>
        <!---->
        <!---->
        <!---->
        <!---->



        <!-- 玩家 -->
        <div id="member"></div>
        <!-- 金币 -->
        <div id="jinbi"></div>

        <!-- 快捷语音 -->
        <div id="messageSay"></div>

        <div id="memberScoreText1"></div>



        <div class="first_half_cards">
            <div class="back"></div>
            <div class="title">
                手牌区
            </div>
        </div>




        <div class="clock" style="display: none;" id='djs'>
            -1
        </div>
        <div class="waitingText" style="display: none;" id='divRobBankerText'>
        </div>

        <!-- 筛子 -->
        <div id="dice" class="dice"></div>
        <!-- 麻将 -->
        <div id='userfp' class='majiangShow'></div>
        <!-- 按钮 -->
        <div id="operationButton"></div>



    </div>










    <div class="end" style="position: fixed; width: 100%; height: 100%; top: 0px; left: 0px; z-index: 110; display: none; overflow: hidden;">
        <img src="" id="end" usemap="#planetmap1" style="width: 100vw; position: absolute; top: 0px; left: 0px; height: 100vh;" />
        <div id="endCreateRoomBtn" style="position: absolute; right: 13vw; width: 33vw; height: 9.6vw; bottom: 8%; z-index: 121; overflow: hidden;"></div>
    </div>



















    <div id="vroomRule" class="createRoom" style="display: none;"></div>
    <div id="vplayRule" class="ruleRoom" style="display: none;">
        <div class="ruleRoomBack"></div>
        <div class="ruleRoomMainPart">
            <div class="ruleB"></div>
            <div class="ruleTitle">
                <img src="/app/img/28gang/rullTitle_1.png" />
            </div>
            <img src="https://gameoss.fexteam.com/files/d_30/images/common/cancel.png" class="cancelRule" onclick="$('#vplayRule').hide();"/>
            <div class="frameBack">
                <div class="ruleItem" style="font-size: 2.3vh;">
                    牌型大小：对白版&gt;对子&gt;二八杠&gt;九点半&gt;九点...&gt;零点。
                </div>
                <div class="ruleItem">
                    牌型算法：牌面点数相加取个位数，如：一筒+七筒为八点。九筒+白板为九点半。除二筒+八筒为二八杠，其它相加为10的都是零点 。
                </div>
                <div class="ruleItem">
                    分数算法：对白版四倍，对子三倍，八点及以上为两倍，二八杠自定义倍数，其余为一倍。
                </div>
                <div class="ruleItem">
                    得分规则：牌面大的优先得分；牌面相同，押注总额大的优先得分；牌面和押注总额相同，先押注的优先得分。
                </div>
                <div class="ruleItem">
                    注意：庄家和闲家牌一样时，庄家赢。
                </div>
            </div>
        </div>
    </div>

    <div id="vaudioRoom" class="audioRoom" style="display: none;"></div>

    <div id='message' class="message" style="display: none;"></div>
    <div id="tishi" class="alert" style="display: none;"></div>
    <div class="alert" style="display: none;">
        <div class="alertBack"></div>
        <img src="http://goss.fexteam.com/files/images/common/note.png" style="position: absolute; top: 20vh; width: 90vw; left: 5vw;" />
    </div>
</div>




<script>
    membercsh();
    function joinroom(){
        $("#valert").remove();
        token='<?php echo ($token); ?>';
        room='<?php echo ($room["id"]); ?>';
        var dkxx='<?php echo ($room["dk"]); ?>';
        load('show');
        if(dkxx){
            connect(dkxx);
        }
        else{
            load('hide');
            prompt('服务器没开启,请稍后再试');
            setTimeout("$('body').hide()",3000);
        }
    }
</script>
<?php if(count($room['userlist']) >= 8 && $room['userlist'][$user['id']] != 1): ?><div id="valert2" class="alert">
        <div class="alertBack"></div>
        <div class="mainPart" style="height: 31%;margin-top: -113.39px;">
            <div class="backImg">
                <div class="blackImg" style=" height: 59%;"></div>
            </div>
            <div class="alertText" style="top: 35%;" id="tipmsg">房间人数已满</div>
            <div class="buttonRight" style="left: 31.5%;width: 17vh;height: 5.5vh;" onclick="location.href='/'">
                返回首页
            </div>

        </div>
    </div>

    <?php exit(); endif; ?>

<?php if((count($room['userlist']) > 0 && $room['js'] == 0) || ($room['js'] > 0 && !$room['userlist'][$user['id']])): ?><div id="valert" class="alert">
        <div class="alertBack"></div>
        <div class="mainPart" style="margin-top: -34vh;">
            <div class="backImg">
            </div>
            <div class="id">
                <img src="/app/img/ID.png" />
                <div class="text">
                    你的<?php echo ($skin[$fangzhu['password']]); ?>ID:<?php echo ($user["id"]); ?>
                </div>
            </div>
            <?php $count=M('user_room')->where(array('uid'=>$user['id'],'type'=>$room['type']))->count(); $count=$count+0; $max=M('user_room')->where(array('uid'=>$user['id'],'type'=>$room['type']))->order('jifen desc')->find(); ?>
            <div class='lishijilu' style="">
            <div>历史最高分：<?php if($max): echo ($max["jifen"]); ?> (<?php echo (date("m-d H:i",$max["overtime"])); ?>)<?php else: ?>暂无游戏记录<?php endif; ?></div>
            <div>游戏总局数：<?php echo ($count); ?></div>
            </div>
            <div class="alertText" style="    top: -1vh;
    background: #281d45;
    border-radius: 1vh;
    border: 0.1vh solid #ae71cb;
    overflow: hidden;">
                <div class="rull" style="    font-size: 2.2vh;
    padding: 0;
    width: 90%;
    margin: 0;
    margin-top: 4.5vw;
    padding: 5%;
    margin-bottom: 5vw;">
                    模式:
                    <a><?php echo ($room['wfname']); ?></a> <br />
                    <?php if($room['gz']): ?>规则:
                        <a><?php echo ($room['gz']); ?></a>
                        <br /><?php endif; ?>
                    <?php if($room['cm']): ?>筹码:
                        <a><?php echo ($room['cm']); ?></a>
                        <br /><?php endif; ?>

                    <?php if($room['sz']): ?>上庄:
                        <a><?php echo ($room['sz']); ?></a>
                        <br /><?php endif; ?>

                    局数:
                    <a><?php echo ($room['zjs']); ?>x2局X<?php echo ($room['fk']); ?>房卡 </a>

                </div>
                <div style="margin-bottom:6vw;
    position: relative;">
                    房间中有<?php foreach($room['userlist'] as $key=>$one){ if(!$indexxx) { $indexxx=1; } else{ echo ','; } echo username($key); } ?>,是否加入？
                </div>
            </div>

            <div style="position: relative;
    width: 100%;
    height: 5vh;
         padding-bottom: 4vw;    top: -0.5vw;">
                <div class="buttonLeft" onclick="location.href='/'" style="    top: 0;
    bottom: 0;
    margin: auto;">
                    创建房间
                </div>
                <div class="buttonRight" onclick="joinroom()" style="   top: 0;
    bottom: 0;
    margin: auto;">
                    加入游戏
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <script>
        joinroom();
    </script><?php endif; ?>















<!-- <audio onended="mp3playandpause('1miao');" id="1miao" src="/app/audio/1miao.mp3"></audio> -->
<audio onended="mp3playandpause('mp300');" id="mp30" src="/app/audio/00.m4a"></audio>
<audio onended="mp3playandpause('mp305');" id="mp305" src="/app/audio/05.m4a"></audio>
<audio onended="mp3playandpause('mp310');" id="mp310" src="/app/audio/10.m4a"></audio>
<audio onended="mp3playandpause('mp315');" id="mp315" src="/app/audio/15.m4a"></audio>
<audio onended="mp3playandpause('mp320');" id="mp320" src="/app/audio/20.m4a"></audio>
<audio onended="mp3playandpause('mp325');" id="mp325" src="/app/audio/25.m4a"></audio>
<audio onended="mp3playandpause('mp330');" id="mp330" src="/app/audio/30.m4a"></audio>
<audio onended="mp3playandpause('mp335');" id="mp335" src="/app/audio/35.m4a"></audio>
<audio onended="mp3playandpause('mp340');" id="mp340" src="/app/audio/40.m4a"></audio>
<audio onended="mp3playandpause('mp345');" id="mp345" src="/app/audio/45.m4a"></audio>
<audio onended="mp3playandpause('mp350');" id="mp350" src="/app/audio/50.m4a"></audio>
<audio onended="mp3playandpause('mp355');" id="mp355" src="/app/audio/55.m4a"></audio>
<audio onended="mp3playandpause('mp360');" id="mp360" src="/app/audio/60.m4a"></audio>
<audio onended="mp3playandpause('mp365');" id="mp365" src="/app/audio/65.m4a"></audio>
<audio onended="mp3playandpause('mp370');" id="mp370" src="/app/audio/70.m4a"></audio>
<audio onended="mp3playandpause('mp375');" id="mp375" src="/app/audio/75.m4a"></audio>
<audio onended="mp3playandpause('mp380');" id="mp380" src="/app/audio/80.m4a"></audio>
<audio onended="mp3playandpause('mp385');" id="mp385" src="/app/audio/85.m4a"></audio>
<audio onended="mp3playandpause('mp390');" id="mp390" src="/app/audio/90.m4a"></audio>
<audio onended="mp3playandpause('mp395');" id="mp395" src="/app/audio/95.m4a"></audio>
<audio onended="mp3playandpause('mp3100');" id="mp3100" src="/app/audio/28gang.m4a"></audio>
<audio onended="mp3playandpause('mp3110');" id="mp3110" src="/app/audio/duizi.m4a"></audio>
<audio onended="mp3playandpause('mp3120');" id="mp3120" src="/app/audio/duizi.m4a"></audio>

<!-- <audio onended="mp3playandpause('mp3daojishi');" id="mp3daojishi" src="/app/audio/daojishi.mp3"></audio> -->
<audio onended="mp3playandpause('mp3gold');" id="mp3gold" src="/app/audio/coin.mp3"></audio>
<!-- <audio onended="mp3playandpause('mp3kaiju');" id="mp3kaiju" src="/app/audio/kaiju.mp3"></audio>
<audio onended="mp3playandpause('mp3xiazhu');" id="mp3xiazhu" src="/app/audio/xiazhu.mp3"></audio>
<audio onended="mp3playandpause('mp3fapai');" id="mp3fapai" src="/app/audio/fapai.mp3"></audio> -->

<audio onended="mp3playandpause('message1');" id="message1" src="/app/message/message1.mp3"></audio>
<audio onended="mp3playandpause('message2');" id="message2" src="/app/message/message2.mp3"></audio>
<audio onended="mp3playandpause('message3');" id="message3" src="/app/message/message3.mp3"></audio>
<audio onended="mp3playandpause('message4');" id="message4" src="/app/message/message4.mp3"></audio>
<audio onended="mp3playandpause('message5');" id="message5" src="/app/message/message5.mp3"></audio>
<audio onended="mp3playandpause('message6');" id="message6" src="/app/message/message6.mp3"></audio>
<audio onended="mp3playandpause('message7');" id="message7" src="/app/message/message7.mp3"></audio>
<audio onended="mp3playandpause('message8');" id="message8" src="/app/message/message8.mp3"></audio>
<audio onended="mp3playandpause('message9');" id="message9" src="/app/message/message9.mp3"></audio>
<audio onended="mp3playandpause('message10');" id="message10" src="/app/message/message10.mp3"></audio>
<audio onended="mp3playandpause('message11');" id="message11" src="/app/message/message11.mp3"></audio>


<audio id="background" src="/app/audio/background.mp3" ></audio>
<!--下注抢庄-->
<audio onended="mp3playandpause('qiangzhuang');" id="qiangzhuang" src="/app/audio/robbanker.m4a"></audio>
<audio onended="mp3playandpause('buqiang');" id="buqiang" src="/app/audio/nobanker.m4a"></audio>

<script>




    function over(msg){
        var html='<div id="valert2" class="alert">';
        html=html+'<div class="alertBack"></div> ';
        html=html+'<div class="mainPart" style="height: 31%;margin-top: -113.39px;">';
        html=html+'<div class="backImg">';
        html=html+'<div class="blackImg" style="height: 59%;"></div>';
        html=html+'</div> ';
        html=html+'<div class="alertText" style="top: 35%;" id="tipmsg">'+msg+'</div>';
        html=html+'<div class="buttonRight" style="left: 31.5%;width: 17vh;height: 5.5vh;" onclick="location.href='/'">返回首页</div> </div></div>';
        $('body').html(html);
    }
    var overscroll = function(el) {
        el.addEventListener('touchstart', function() {
            var top = el.scrollTop
                , totalScroll = el.scrollHeight
                , currentScroll = top + el.offsetHeight;
            //If we're at the top or the bottom of the containers
            //scroll, push up or down one pixel.
            //
            //this prevents the scroll from "passing through" to
            //the body.
            if(top === 0) {
                el.scrollTop = 1;
            } else if(currentScroll === totalScroll) {
                el.scrollTop = top - 1;
            }
        });
        el.addEventListener('touchmove', function(evt) {
            //if the content is actually scrollable, i.e. the content is long enough
            //that scrolling can occur
            if(el.offsetHeight < el.scrollHeight)
                evt._isScroller = true;
        });
    }
    document.body.addEventListener('touchmove', function(evt) {
        //In this case, the default behavior is scrolling the body, which
        //would result in an overflow.  Since we don't want that, we preventDefault.
        if(!evt._isScroller) {
            evt.preventDefault();
        }
    });

    var context = new (window.AudioContext || window.webkitAudioContext)();
    var source = [];
    var audioBuffer = [];

    function mp3play(id){
        if(id!='background' &&　mp3open==2){
            return false;
        }
        //document.getElementById(id).play();
        if(!audioBuffer[id]){
            loadAudioFile(id);
        }
        if(source[id]){
            source[id].stop()
        }
        source[id] = context.createBufferSource();
        source[id].buffer = audioBuffer[id];
        if(id=='background'){
            source[id].loop = true;
        }
        else{
            source[id].loop = false;
        }
        source[id].connect(context.destination);
        source[id].start(0); //立即播放
    }
    function mp3pause(id){
        //document.getElementById(id).pause();
        if (source[id]) {
            source[id].stop(); //立即停止
        }
    }

    function mp3playandpause(id){
        mp3play(id);
        mp3pause(id);
    }


    function initSound(arrayBuffer,id) {
        context.decodeAudioData(arrayBuffer, function(buffer) { //解码成功时的回调函数
            audioBuffer[id] = buffer;
        }, function(e) { //解码出错时的回调函数
            console.log('Error decoding file', e);
        });
    }


    function loadAudioFile(id) {
        var url=$('#'+id).attr('src');
        var xhr = new XMLHttpRequest(); //通过XHR下载音频文件
        xhr.open('GET', url, true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function(e) { //下载完成
            initSound(this.response,id);
        };
        xhr.send();
    }


    function audioAutoPlay(id){
        loadAudioFile(id);
    }
    function muiscready(){
        audioAutoPlay('mp30');
        audioAutoPlay('mp305');
        audioAutoPlay('mp310');
        audioAutoPlay('mp315');
        audioAutoPlay('mp320');
        audioAutoPlay('mp325');
        audioAutoPlay('mp330');
        audioAutoPlay('mp335');
        audioAutoPlay('mp340');
        audioAutoPlay('mp345');
        audioAutoPlay('mp350');
        audioAutoPlay('mp355');
        audioAutoPlay('mp360');
        audioAutoPlay('mp365');
        audioAutoPlay('mp370');
        audioAutoPlay('mp375');
        audioAutoPlay('mp380');
        audioAutoPlay('mp385');
        audioAutoPlay('mp390');
        audioAutoPlay('mp395');
        audioAutoPlay('mp3100');
        audioAutoPlay('mp3110');
        audioAutoPlay('mp3120');

        audioAutoPlay('background');

        // audioAutoPlay('mp3daojishi');
        audioAutoPlay('mp3gold');
        // audioAutoPlay('mp3kaiju');
        // audioAutoPlay('mp3xiazhu');

        // audioAutoPlay('mp3fapai');



        audioAutoPlay('message1');
        audioAutoPlay('message2');
        audioAutoPlay('message3');
        audioAutoPlay('message4');
        audioAutoPlay('message5');
        audioAutoPlay('message6');
        audioAutoPlay('message7');
        audioAutoPlay('message8');
        audioAutoPlay('message9');
        audioAutoPlay('message10');
        audioAutoPlay('message11');


        // audioAutoPlay('xia1');
        // audioAutoPlay('xia2');
        // audioAutoPlay('xia4');
        // audioAutoPlay('xia5');
        audioAutoPlay('qiangzhuang');
        audioAutoPlay('buqiang');
        if(bgmp3open==1){
            setTimeout(function(){
                mp3pause('background');
                mp3play('background');
            },2000)
        }
        if(bgmp3open==2){
            mp3pause('background');
        }
    }



    // document.addEventListener("WeixinJSBridgeReady", function () {
    //           mp3play('background');
    // }, false);
    // document.addEventListener('YixinJSBridgeReady', function() {
    //           mp3play('background');
    // }, false);




    function onBridgeReady(){
        WeixinJSBridge.invoke('getNetworkType',{},
            function(e){
                //WeixinJSBridge.log(e.err_msg);
                mp3play('1miao');
            });
    }

    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    }else{
        onBridgeReady();
    }


    muiscready();

</script>

</body>
</html>