<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>道游互娱</title>
    <link rel="stylesheet" type="text/css" href="/static/css/homepage/homepage11-1.0.0.css" />
    <link rel="stylesheet" href="/static/css/public.css" />
    <link rel="stylesheet" href="/static/css/app.css" />
    <link rel="stylesheet" type="text/css" href="/themes/game/Public/zhongyi/zhongyi.css"/>
  <link rel="stylesheet" href="/app/css/chongzhi.css" /> 
    <script type="text/javascript" src="/static/js/jquery3.2.1.min.js"></script>
    <script type="text/javascript" src="/themes/game/Public/zhongyi/jquery.rotate.min.js"></script>
</head>
<body>
<div id="loadings" style="position:fixed;top:0;right:0;bottom:0;left:0;background:#fff;z-index:999;font-size:16px;">
    <div class="spinner">
    </div>
</div>
<div id="networkReconnect" style="position: fixed; width:2.88rem; line-height:.2rem; font-size:.1rem; left:.36rem; text-align: center; bottom:45%; background: -webkit-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0)); background: -moz-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0)); background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0.5), rgba(0,0,0,0)); color:#fff; display:none; z-index:9999;">
    您的网络已断开，我们正在尝试重连...
</div>

<div class="game-tips" style="display: none;">
    <div class="tips-hd"></div>
    <div class="tips-box">
        <div class="tips-title"></div>
        <div class="tips-content">
            <p>本游戏仅供娱乐,严禁赌博,如发现
            有赌博行为,将封停账号并向公安机
            关举报。</p>
            <p>游戏中使用到的房卡为游戏道具,不
            具有任何财产性功能,本公司对于用
            户所拥有的房卡不提供任何形式官方
            回购、直接或变相兑换现金或实物等
                服务或相关功能。</p>
            <p>游戏仅供休闲娱乐使用,游戏中出现
                问题请联系客服。</p>
        </div>
        <div class="tips-btn">确&nbsp;&nbsp;&nbsp;定</div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $(".game-tips").show();
        $(".tips-btn").click(function (e) {
            $(".game-tips").remove();
        });
    });
</script>

<div class="index">
    <div class="top">
        <div class="user">
            <div class="user-img">
                <img class="avatar"  src="" id="userimg"/>
                <!--<div id="user-id" class="user-id"></div>-->
            </div>
            <div class="user-info">
                <div class="name" id="nickname"></div>
                <div class="room-card">
                    <img src="/themes/game/Public/zhongyi/img/roomCard.cb202.png" alt="" /><span id="fknum"></span>
                </div>
            </div>
        </div>
      
        <button class="issue-room-card"onclick="location.href='<?php echo U('portal/user/daoyoufangka');?>'">发放房卡</button>
        <button class="room-card-record">更多功能</button>
       <p style="color: #ebd300;float:left;margin-left:75%" class="chongzhi"><img src="/app/chongzhibtn.png" style="height: 28px;margin-top:70px"></p>
    </div>
  
  <div class="tan">
                <ul>
                  <li><b>充值未到账请联系客服</b></li>
                    <?php if(is_array($goods)): foreach($goods as $key=>$vo): ?><li><p><span class="jine" ><b><?php echo ($vo['money']); ?>元 = 	<?php echo ($vo['good']); ?>张房卡</b></span><span class="chong" onclick="location.href='<?php echo U('user/pay/index',array('tid'=>$vo[id]));?>'"></span></p><?php endforeach; endif; ?>

                  <div class="quxiao"></div>
              </ul>
           </div>	

    <div class="tips-img"></div>
    <div class="flash-light"></div>
    <div class="flash-clock-left"></div>
    <div class="flash-clock-right"></div>
    <script type="text/javascript">
        $(function () {
            var time = 1100;
            open();
            function open() {
                $("div.flash-light").animate({"opacity" : 1}, time, function () {
                    back();
                });
            }
            function back() {
                $("div.flash-light").animate({"opacity" : 0.4}, time, function () {
                    open();
                });
            }

            mleft("div.flash-clock-left", 35, -1);
            mleft("div.flash-clock-right", -45, 1);
            function mleft(selector, v, op) {
                $(selector).css({'transform':'rotate('+v+'deg)'});
                if(v == -45)
                    op = 1;
                if(v == 35)
                    op = -1;
                v += op;
                setTimeout(function () {
                    mleft(selector, v, op);
                }, 15);
            }
        });

        $(function () {
            $("div.game-list > div.game-item").click(function () {
                var $this = $(this);
                var id = $this.attr("data-id");
                if(id) {
                    setTimeout(function () {
                        send('gameserver9',{id:id});
                    }, 100);
                }
            });
        });
    </script>
    <script type="text/javascript">
        var selectConfig = {};
        selectConfig['tenniu'] = JSON.parse('<?php echo json_encode($rules, JSON_UNESCAPED_UNICODE); ?>');
    </script>
    <div class="game-list">
        <div class="game-item paijiu10-bg" data-id="18">
        </div>
        <div class="game-item paijiu8big-bg" data-id="20">
        </div>
        <div class="game-item niuniuTen-bg" data-id="19">
        </div>
        <div class="game-item fish-bg" data-id="21">
        </div>
        <div class="game-item paijiu6-bg" data-id="22">
        </div>
        <div class="game-item niuniu-bg" data-id="28">
        </div>
        <div class="game-item sangong-bg" data-id="">
        </div>
        <div class="game-item ershidian-bg" data-id="">
        </div>
        <div class="game-item majiang-bg">
        </div>
        <div class="game-item dezhou-bg">
        </div>
        <div style="height: 10vh;"></div>
    </div>
    <div class="join-room"></div>

    <div class="createRoom" style="display: none" id="room"></div>

    <div class="pupop-container niuniuTen-mask">
        <div class="niuniu-opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="tab">
                    <?php foreach( $rules as $k=>$v ): ?>
                    <span class="tab-item <?php echo ($k==0 ? 'on' : ''); ?>" data-item="<?php echo ($k+1); ?>" onclick="send('xzplay',{id:<?php echo ($v['id']); ?>})"><?php echo mb_substr($v['name'], 0, 2, 'utf-8');?><br/><?php echo mb_substr($v['name'], 2, 2, 'utf-8');?></span>
                    <?php endforeach; ?>
                </div>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont number">
                            <div class="nameText">
                                底分（计分牌）
                            </div>
                            <div class="flex-cont flex-item">
                                <div class="prev">
                                    -
                                </div>
                                <div class="radio flex-item">
                                    <div class="on" data-item="1" data-pos="1"></div>
                                    <div data-item="2" data-pos="2"></div>
                                    <div data-item="3" data-pos="3"></div>
                                    <div data-item="4" data-pos="4"></div>
                                    <div data-item="5" data-pos="5"></div>
                                    <span class="showNumber" data-pos="1"><i>1</i></span>
                                </div>
                                <div class="next">
                                    +
                                </div>
                            </div>
                        </div>
                        <div class="flex-cont rules"></div>
                        <div class="flex-cont type"></div>
                        <div class="flex-cont innings"></div>
                        <div class="zhuang"></div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn" onclick="send('openroom',{})"></button>
                <div class="niuniu-bottom"></div>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
    <div class="pupop-container niuniu-mask">
        <div class="niuniu-opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="tab">
                    <span class="tab-item on" data-item="1">明牌<br />抢庄</span>
                    <span class="tab-item" data-item="2">通比<br />牛牛</span>
                    <span class="tab-item" data-item="3">自由<br />抢庄</span>
                    <span class="tab-item" data-item="4">牛牛<br />上庄</span>
                    <span class="tab-item" data-item="5">固定<br />庄家</span>
                </div>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont number">
                            <div class="nameText">
                                底分（计分牌）
                            </div>
                            <div class="flex-cont flex-item">
                                <div class="prev">
                                    -
                                </div>
                                <div class="radio flex-item">
                                    <div class="on" data-item="1" data-pos="1"></div>
                                    <div data-item="2" data-pos="2"></div>
                                    <div data-item="3" data-pos="3"></div>
                                    <div data-item="4" data-pos="4"></div>
                                    <div data-item="5" data-pos="5"></div>
                                    <span class="showNumber" data-pos="1"><i>1</i></span>
                                </div>
                                <div class="next">
                                    +
                                </div>
                            </div>
                        </div>
                        <div class="flex-cont rules">
                            <div class="name">
                                规则:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="niuniu_card_rule" value="1" checked="" /><span>牛牛&times;3 牛九&times;2 牛八&times;2</span></label>
                                <br />
                                <label><input type="radio" name="niuniu_card_rule" value="2" /><span>牛牛&times;4 牛九&times;3 牛八&times;2 牛七&times;2</span></label>
                            </div>
                        </div>
                        <div class="flex-cont type">
                            <div class="name">
                                牌型:
                            </div>
                            <div class="flex-item"></div>
                        </div>
                        <div class="flex-cont innings">
                            <div class="name">
                                局数:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="niuniu_max_matches" value="10" checked="" /><span>10局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X1</span></label>
                                <label><input type="radio" name="niuniu_max_matches" value="20" /><span>20局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X2</span></label>
                            </div>
                        </div>
                        <div class="zhuang"></div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn"></button>
                <div class="niuniu-bottom"></div>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
    <div class="pupop-container goldflower-mask">
        <div class="opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont number">
                            <div class="nameText">
                                底分
                            </div>
                            <div class="flex-cont flex-item">
                                <div class="prev2">
                                    -
                                </div>
                                <div class="radio flex-item">
                                    <div class="on" data-item="2" data-pos="1"></div>
                                    <div data-item="4" data-pos="2"></div>
                                    <div data-item="8" data-pos="3"></div>
                                    <span class="showNumber" data-pos="1"><i>2</i></span>
                                </div>
                                <div class="next2">
                                    +
                                </div>
                            </div>
                        </div>
                        <div class="flex-cont rules">
                            <div class="name">
                                分数:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="gold_card_rule" value="1" checked="" /><span>2/4，4/8，8/16，10/20</span></label>
                                <br />
                                <label><input type="radio" name="gold_card_rule" value="2" /><span>2/4，5/10，10/20，20/40</span></label>
                            </div>
                        </div>
                        <div class="flex-cont innings">
                            <div class="name">
                                局数:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="gold_max_matches" value="10" checked="" /><span>10局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X1</span></label>
                                <label><input type="radio" name="gold_max_matches" value="20" /><span>20局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X2</span></label>
                            </div>
                        </div>
                        <div class="flex-cont goldflower-number">
                            <div class="name">
                                上限:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="gold_hand_patterns" value="0" /><span>无</span></label>
                                <label><input type="radio" name="gold_hand_patterns" value="500" checked="" /><span>500</span></label>
                                <label><input type="radio" name="gold_hand_patterns" value="1000" /><span>1000</span></label>
                                <label><input type="radio" name="gold_hand_patterns" value="2000" /><span>2000</span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn"></button>
                <div class="niuniu-bottom"></div>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
    <div class="pupop-container texas-poker-mask">
        <div class="opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont">
                            <div class="title-text">
                                小盲/大盲:
                            </div>
                            <label><input type="radio" name="texasPoker_mang" checked="" value="1" /><span>1/2</span></label>
                            <label><input type="radio" name="texasPoker_mang" value="2" /><span>2/4</span></label>
                        </div>
                        <div class="flex-cont">
                            <div class="title-text">
                                局数:
                            </div>
                            <label><input type="radio" name="texasPoker_jushu" checked="" value="10" /><span>10局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X2</span></label>
                            <label><input type="radio" name="texasPoker_jushu" value="20" /><span>20局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X4</span></label>
                        </div>
                        <div class="flex-cont">
                            <div class="title-text">
                                前注:
                            </div>
                            <label><input type="radio" name="texasPoker_zhu" checked="" value="0" /><span>无</span></label>
                            <label><input type="radio" name="texasPoker_zhu" value="1" /><span>1倍小盲</span></label>
                            <label><input type="radio" name="texasPoker_zhu" value="2" /><span>2倍小盲</span></label>
                        </div>
                        <div class="flex-cont">
                            <div class="title-text">
                                初始分数:
                            </div>
                            <label><input type="radio" name="texasPoker_chip" checked="" value="500" /><span>500</span></label>
                            <label><input type="radio" name="texasPoker_chip" value="1000" /><span>1000</span></label>
                            <label><input type="radio" name="texasPoker_chip" value="1500" /><span>1500</span></label>
                            <label><input type="radio" name="texasPoker_chip" value="2000" /><span>2000</span></label>
                        </div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn"></button>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
    <div class="pupop-container thirteencard-mask">
        <div class="niuniu-opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont number">
                            <div class="nameText">
                                底分（计分牌）
                            </div>
                            <div class="flex-cont flex-item">
                                <div class="prev">
                                    -
                                </div>
                                <div class="radio flex-item">
                                    <div class="on" data-item="1" data-pos="1"></div>
                                    <div data-item="3" data-pos="2"></div>
                                    <div data-item="5" data-pos="3"></div>
                                    <span class="showNumber" data-pos="1"><i>1</i></span>
                                </div>
                                <div class="next">
                                    +
                                </div>
                            </div>
                        </div>
                        <div class="flex-cont type">
                            <div class="name">
                                玩法:
                            </div>
                            <div class="flex-item">
                                <label><input type="checkbox" name="thirteen_hand_patterns" value="1" checked="" disabled="" /><span>经典</span></label>
                            </div>
                        </div>
                        <div class="flex-cont innings">
                            <div class="name">
                                局数:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="thirteen_max_matches" value="5" checked="" /><span>5局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X1</span></label>
                                <label><input type="radio" name="thirteen_max_matches" value="10" /><span>10局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X2</span></label>
                                <label><input type="radio" name="thirteen_max_matches" value="20" /><span>20局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X4</span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn"></button>
                <div class="niuniu-bottom"></div>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
    <div class="pupop-container threeOpens-mask">
        <div class="niuniu-opacity">
            <div class="content-box">
                <i class="mask-icon mask-top"></i>
                <i class="mask-icon mask-right"></i>
                <i class="mask-icon mask-bottom"></i>
                <i class="mask-icon mask-left"></i>
                <div class="tab">
                    <span class="tab-item on" data-item="1">抢庄模式</span>
                    <span class="tab-item" data-item="2">通比模式</span>
                    <span class="tab-item" data-item="3">三公当庄</span>
                </div>
                <div class="content-item">
                    <div class="content">
                        <div class="flex-cont number">
                            <div class="nameText">
                                底分
                            </div>
                            <div class="flex-cont flex-item">
                                <div class="prev">
                                    -
                                </div>
                                <div class="radio flex-item">
                                    <div class="on" data-item="1" data-pos="1"></div>
                                    <div data-item="2" data-pos="2"></div>
                                    <div data-item="3" data-pos="3"></div>
                                    <div data-item="4" data-pos="4"></div>
                                    <div data-item="5" data-pos="5"></div>
                                    <span class="showNumber" data-pos="1"><i>1</i></span>
                                </div>
                                <div class="next">
                                    +
                                </div>
                            </div>
                        </div>
                        <div class="flex-cont type">
                            <div class="name">
                                规则:
                            </div>
                            <div class="flex-item"></div>
                        </div>
                        <div class="flex-cont matches">
                            <div class="name">
                                局数:
                            </div>
                            <div class="flex-item">
                                <label><input type="radio" name="threeOpens_max_matches" value="12" checked="" /><span>12局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X2</span></label>
                                <label><input type="radio" name="threeOpens_max_matches" value="24" /><span>24局<img class="mask-inning-card" src="http://cdn.lfzgame.com/images/index/mask-inning-card.png" />X4</span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text">
                    创建房间，无人参与，半小时后返还房卡
                </div>
                <button class="createRoomBtn"></button>
                <div class="niuniu-bottom"></div>
                <div class="close-window"></div>
            </div>
        </div>
    </div>
</div>


<script src="/static/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript" src="/static/js/zhongyi.js"></script>


<script src="/static/js/homepage/home.js" type="text/javascript"></script>
<script type="text/javascript" src="/static/js/base64.js"></script>
<script src="/static/js/zhongyiapp.min.js" type="text/javascript"></script>
<script src="/index.php/portal/index/gamejs" type="text/javascript"></script>

<script type="text/javascript">
    $("div.game-list").overscroll();
    load('show');
    token='<?php echo ($token); ?>';
    if(dkxx){
        connect(dkxx);
    }
    else{
        load('hide');
        prompt('服务器没开启,请稍后再试');
        setTimeout("$('body').hide()",3000);
    }
  
   $('.chongzhi,.roomCard1').click(function(){
      $('.yinying').show();
     	$('.tan').css('margin-top','100px');
     })
     $('.quxiao').click(function(){
     	 $('.yinying').hide();
       $('.tan').css('margin-top','-500px');
     })
</script>
</body>
</html>