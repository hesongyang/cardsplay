$(document).ready(function () {
    releaseWin();
    setPay();
    set_gameRoom();
    set_roomNumInput();
    set_weixinShare();
    set_roomTips();

    $("input,textarea").bind("input", function() {
        var val = $(this).val();
        if(/[\$%\^&\*<>\/]+/g.test(val)) {
            $(this).val("")
            insertLighterPop("包含不合法字符，请重新输入");
        }
    })
    autoIphoneX();
    
})
$(window).resize(function() {
    releaseWin();
    autoIphoneX(1);
})
function autoIphoneX(resize) {
    var windowWidth = $(window).width(), windowHeight = $(window).height();
    var agent = navigator.userAgent.toLowerCase();
    if(agent.indexOf("iphone") >= 0 && windowWidth==375 && windowHeight==724 ) {
        var footerHeight = $("#footer").height();
        footerHeight += 34;
        $("#footer").css("height", footerHeight);

        var backHeight = $(".footer-back").height();
        backHeight += 34;
        $(".footer-back").css("height", backHeight);
    } else if(resize) {
        $("#footer").css("height", "");
        $(".footer-back").css("height", "");
    }
}
// 获取URL中的参数
function getURLVar(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    return r != null ? unescape(r[2]) : null;
};
// 设置font-size
function releaseWin() {
    var staticN = 375 / 20;
    var size = $(window).width() / staticN;
    if ($(window).width() >= 800) {
        size = 800 / staticN;
    }
    $("html").attr("style", "font-size:" + size + "px !important");
}

/*提示层*/
function pop_alert(type, title, content) {
    $('.alert_box').show();
    $('.pop_bg').show();
    if(type == "error") {
        content = "<span style='color:red'>"+content+"</span>";
    }
    $('.alert_title').html(title);
    $('.alert_content').html(content);
    $('.alert_box .close, .close_ico').click(function () {
        $("body").css("overflow-y", "auto");
        $('.alert_box').hide();
        $('.pop_bg').hide();
    });
}

// 支付购买
var chkorder;
function setPay() {
    $(".recharge-list li a").bind("click", function () {
        var url = $(this).attr("href");
        $.ajax({
            type: "get",
            url: url,
            dataType: "json",
            success: function (data) {
                if (data.status == "success") {
                    if (data.type == 'jump') {
                        top.location.href = data.msg;
                    } else if (data.type == 'img')
                    {
                        $("#payQRcode").attr("src", data.msg);
                        $("#qrCode,.pop_bg").show();
                        var orderId = data.orderId;
                        chkorder=setInterval('checkOrder(\'' + orderId + '\')', 2000);
                        $(".pop_bg,#qrCode").bind("click", function() {
                            $("#qrCode,.pop_bg").hide();
                        })
                    } else if (data.type == 'html')
                    {
                        pop_alert("", "", data.msg);
                    }
                } else {
                    pop_alert("", "", data.msg);
                    $(".alert_box p.close").hide();
                }
            }
        });

        return false;
    });
}
function checkOrder(orderId)
{
    $.ajax({
        type: "get",
        url: "/index.php?ac=checkorder&op=check&orderid=" + orderId,
        dataType: "json",
        success: function (data) {
            if (data.status == 'success') {
                if (data.payStatus == 'success') {
                    $("#qrCode").hide();
                    pop_alert("tips", "", "支付成功");
                    clearInterval(chkorder);
                }
            }
        }
    });
}

// 微信分享
function set_weixinShare() {
    var url = location.href.split('#')[0];
    var roomNum = $("#roomNum").val();
    var config, share;
    $.ajax({
        type: "post",
        url: "index.php?ac=share&op=config",
        data: { url: url, roomNum: roomNum },
        dataType: "json",
        error: function (data) {
            //alert("分享设置失败");
        },
        success: function (data) {
            if (data.status == "ok") {
                if (data.config != "") {
                    wx.config({
                        debug: false, // 开启调试模式。
                        appId: data.config.appid,
                        timestamp: data.config.timestamp,
                        nonceStr: data.config.noncestr,
                        signature: data.config.signature,
                        jsApiList: ['onMenuShareAppMessage', 'onMenuShareTimeline', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice']
                    });
                    wx.ready(function () {
                        wx.onMenuShareAppMessage({
                            title: data.share.title,
                            desc: data.share.desc,
                            link: data.share.link,
                            imgUrl: data.share.imgurl,
                            trigger: function (res) {
                                // alert('用户点击发送给朋友');
                            },
                            success: function (res) {
                                //alert('已分享');
                            },
                            cancel: function (res) {
                                // alert('已取消');
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });
                        wx.onMenuShareTimeline({
                            title: data.share.title,
                            link: data.share.link,
                            imgUrl: data.share.imgurl,
                            trigger: function (res) {
                                // alert('用户点击分享到朋友圈');
                            },
                            success: function (res) {
                                // alert('已分享');
                            },
                            cancel: function (res) {
                                // alert('已取消');
                            },
                            fail: function (res) {
                                alert(JSON.stringify(res));
                            }
                        });
                    });
                }

            } else {
               // pop_alert("error", "", data.msg);
            }
        }
    });
}

function set_weixinShareByInfo(title, desc, link, imgurl)
{

    wx.ready(function () {
        wx.onMenuShareAppMessage({
            title: title,
            desc: desc,
            link: link,
            imgUrl: imgurl,
            trigger: function (res) {
                // alert('用户点击发送给朋友');
            },
            success: function (res) {
                //alert('已分享');
            },
            cancel: function (res) {
                // alert('已取消');
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
        wx.onMenuShareTimeline({
            title: title,
            link: link,
            imgUrl: imgurl,
            trigger: function (res) {
                // alert('用户点击分享到朋友圈');
            },
            success: function (res) {
                // alert('已分享');
            },
            cancel: function (res) {
                // alert('已取消');
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });
    
}


/*设置游戏的选择项*/
function set_gameRoom() {
    $(".gameroom-create").click(function () {
        var obj = $(this);
        var id = obj.attr('id').replace("game_", "");
        $.ajax({
            type: "get",
            dataType: 'json',
            url: "/index.php?ac=gameroom&op=detail&id=" + id + "",
            success: function (data) {
                if (data.devStatus == 'online') {
                    $("#aui-dialog-gameroom").html(data.msg);
                    $("#aui-dialog-gameroom").removeClass("aui-hide").addClass("show");
                    $(".pop_bg").show();
                    set_close();
                    submit_gameRoom();
                } else if (data.devStatus == 'developing') {
                    pop_alert("tips", "", "游戏即将上线");
                } else if (data.devStatus == 'debug') {
                    pop_alert("tips", "", "版本更新维护中");
                }
                $("body").css("overflow-y", "hidden");
            }
        });
    });
}
/*进入游戏*/
function submit_gameRoom() {
    $('#gameroomForm').submit(function () {
        var url = $(this).attr("action");
        var data = $(this).serialize();
        var botton = $(this).find(".big-btn");
        botton.attr('disabled', "true");
        $.ajax({
            type: "post",
            url: url,
            data: data,
            dataType: "json",
            error: function (data) {
                alert("请重新进入");
            },
            success: function (data) {
                if (data.status == "ok") {
                    botton.removeAttr("disabled");
                    $("#gameroomForm").hide();
                    window.location.href = data.msg;
                } else {
                    alert(data.msg);
                   
                }
            }
        });
    });
}
// 显示房间号输入
function set_roomNumInput() {
    $("#roomIdInput").click(function () {
        $("body").css("overflow-y", "hidden");
        $("#roomNum").val("");
        $(".room-id-show label.num-label").removeClass("active").text("");

        $("#aui-dialog-roomIdInput").removeClass("aui-hide").addClass("show");
        $(".pop_bg").show();
        set_close();
    });

}
function set_close() {
    $(".btn-close,.close-icon").bind("click", function() {
        $(".pop_bg").hide();
        $(".aui-dialog").addClass("aui-hide").removeClass("show");
        $("body").css("overflow-y", "auto");
    })
}
// 登录

function set_Login()
{
    if (appType == "ios") {
        window.webkit.messageHandlers.redirectLoginAction.postMessage("");
    }
    if (appType == "android") {
        AndroidApp.dropped();
    }
    $(".need_login").bind("click", function () {
        var url = "index.php?ac=minlogin";
        $.ajax({
            type: "get",
            url: url,
            dataType: "json",
            success: function (data) {
                if (data.status == "ok") {
                    $("#aui-dialog-gameroom").html(data.msg);
                } else {
                    // pop_alert("error", "", data.msg);
                    // set_login_switch();
                    // $(".pop_bg").bind("click", function() {
                    //     $(".alert_box,.pop_bg").hide();
                    // })
                    $(".login_box .login_content").html(data.msg);
                    $(".pop_bg,.login_box").show();
                    set_login_switch();
                    $(".pop_bg").bind("click", function() {
                        $(".login_box,.pop_bg").hide();
                    })
                }
            }
        });
        return false;
    });
}
function set_login_switch() {
    $(".alert_box p.close").hide();
    $("a[data-type='mobile']").bind("click", function() {
        $(".login-type").hide();
        $(".phone-login").show();
    });
    $(".back-entrance").bind("click", function() {
        $(".login-type").show();
        $(".phone-login").hide();
    })
}

function set_roomTips()
{
    var op = getURLVar("op");
    var roomNum = getURLVar("roomNum");
    if (op == "roomerror")
    {
        pop_alert("error", "", "房间号:" + roomNum + " 已结束");
    }
    if (op == "inningserror") {
        pop_alert("error", "", "房间号:" + roomNum + " 玩家已满");
    }
}
function insertLighterPop(message) {
    var node = "<p class=\"lighter-pop\" id=\"lighter-pop\">"+message+"</p>";
    $('body').append(node);

    setTimeout(function() {
        $("#lighter-pop").remove();
    }, 1500);
}

/*提交反馈表单*/
function submit_feedback() {
    $("#feedbackSubmit").bind("click", function() {
        var message = $("#feedbackForm").find("#message").val();
        var name = $("#feedbackForm").find("#name").val();
        var telphone = $("#feedbackForm").find("#telphone").val();
        var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/;
        if (message == "") {
            insertLighterPop("请输入您要反馈的问题");
            return false;
        }
        if (name == "") {
            insertLighterPop("请输入您的姓名");
            return false;
        }
        if (telphone == "") {
            insertLighterPop("请输入您的手机号码");
            return false;
        } else if (!myreg.test(telphone)) {
            insertLighterPop("请输入有效的手机号码");
            return false;
        }
        $.ajax({
            url: "?ac=feedback",
            type: "post",
            dataType: 'json',
            data: $("#feedbackForm").serialize(),
            success: function(data) {
                insertLighterPop("感谢您的反馈,我们将尽快处理并回复您反馈的问题");
                $("#feedbackForm input, #feedbackForm textarea, #feedbackForm select").val("");
            },
            error: function(data) {
                insertLighterPop("请刷新重试");
            }
        })
    })
}

// 密友群

function setFriendAction(btn) {
    // 同意、拒绝、踢出事件
    $(btn).bind({
        click: function () {
            var uid = $(this).attr("data-id"),
                op = $(this).attr("data-url"),
                row = $("#row_" + uid);
            $.ajax({
                url: '/index.php?ac=inwardfriend',
                type: "get",
                data: { op: op, uid: uid },
                dataType: "json",
                success: function (data) {
                    if (data.status == "success") {
                        if (op == "accept") {
                            var avatar = $($(row).find("img")[0]).attr("src"),
                                text = $($(row).find(".share-member-list-txt")[0]).html();
                            var node = '<li id="row_' + uid + '"><img src="' + avatar + '" alt="" class="share-member-list-avatar"><div class="share-member-list-txt">' + text + '</div><div class="right-btn"><a href="javascript:void(0)" data-url="reject" data-id="' + uid + '" class="remove">踢出</a></div></li>';
                            $(row).remove();
                            $("#memberList").prepend(node);
                            var removeBtn = "#row_" + uid + " .remove";
                            setFriendAction(removeBtn);
                            insertLighterPop("加入成功");
                        } else {
                            $(row).remove();
                            insertLighterPop("踢出成功");
                        }
                    }
                },
                error: function () {
                    insertLighterPop("请刷新重试...");
                }
            })
        }
    });  
}
// 开通密友群
function inwardfriend(type, currencyName) {
    $.ajax({
        type: 'GET',
        url: '/index.php?ac=inwardfriend',
        data: { op: 'join', type: type },
        dataType: 'json',
        success: function (jsonData) {
            if (jsonData.status == "success") {
                if (jsonData.type == 'jump') {
                    window.location.href = jsonData.msg;
                } else {
                    //alert(jsonData.msg);
                    showConfirmBox("支付" + jsonData.msg + currencyName +"开通此功能？");
                }

            } else {
                insertLighterPop(jsonData.msg);
                
                // error(jsonData.msg);
            }
        },
        error: function () {
            // error("处理异常！");
        }
    });
}
// 显示确认框
function showConfirmBox(text) {
    $("#confirmBox .hint-text").html(text);
    $("#confirmBox,.confirm-panel").show();
}
/**
  *确认框事件
  *btn 按钮
  *trueBack 确定回调
  *falseBack 取消回调
*/
function setConfirmBox(btn, trueBack, falseBack) {
    $(btn).bind({
        click: function() {
            var result = $(this).attr("data-result");
            if(result == "true") {
                trueBack();
            } else {
                 falseBack();
            }
        }
    })
}
// 同意邀请
function confirmInviteRequest(uid, fuid) {
    $('a[id="inviteFriend"]').on('click', function (e) {
        if(uid == fuid) {
            insertLighterPop("请发送给好友");
            return;
        }
        $.ajax({
            type: 'GET',
            url: '/index.php',
            data: { ac: 'inwardfriend', op: 'invite', uid: uid, type: 'add' },
            dataType: 'json',
            success: function (jsonData) {
                if (jsonData.status == "success") {
                    if (jsonData.type == 'jump') {
                        window.location.href = jsonData.msg;
                    } else {
                        $(".invite-request-content").hide();
                        $(".invite-status-content").show();
                        setTimeCountImg("/index.php",".invite-card-count");
                    }

                } else {
                    insertLighterPop(jsonData.msg);
                    // error(jsonData.msg);
                }
            },
            error: function () {
                // error("处理异常！");
            }
        });
        return false;
    });
}

function showPackageMake() {
    $("#getPackageMake").bind("click", function() {
        var url = $(this).attr("data-url");
        $.ajax({
            url: url,
            type: 'get',
            success: function(data) {
                $("body").append(data);
            }
        })
    })
}
// 礼包领取
function getCardEvent() {
    $("#getCard").on("click", function () {
        var url = $(this).attr("href");
        $(this).addClass("rotate");

        $.ajax({
            type: "get",
            url: url,
            dataType: "json",
            error: function (data) {
                setTimeout(function() {
                    insertLighterPop("出错，刷新再试一下");
                }, 300)
            },
            success: function (data) {
                setTimeout(function() {
                    if (data.status == "success") {
                        window.location.href = data.msg;
                    } else {
                        insertLighterPop(data.msg);
                    }
                    
                }, 300)
                
            }
        });
        return false;
    });
}

// 制作礼包
function createUserPackage() {
    var remainNum = parseInt($("#remain-num").html());
    $("#currency").bind({
        input: function () {
            var finalNum = parseInt($(this).val());
            if (finalNum) {
                if (finalNum > remainNum) {
                    insertLighterPop("您的房卡不足");
                    $(".final-num").html(0);
                    $(this).val(0);
                } else {
                    $(".final-num").html(finalNum);
                }
            } else {
                $(".final-num").html(0);
            }
        }
    });
    $("#makeForm").on("submit", function () {
        var url = $(this).attr("action");
        var data = $(this).serialize();
        var btnsubmit = $("#btnsubmit").val();
        var currency = parseInt($("#currency").val());

        data = data + "&btnsubmit=" + btnsubmit;
        if (currency <= 0 || isNaN(currency)) {
            insertLighterPop("请输入正确的数量");
        } else {
            $.ajax({
                type: "post",
                url: url,
                data: data,
                dataType: "json",
                error: function (data) {
                    alert("出错，刷新再试一下");
                },
                success: function (data) {
                    if (data.status == "success") {
                        window.location.href = data.msg;
                    } else {
                        insertLighterPop(data.msg);
                    }
                }
            });
        }
        return false;
    });
}

function setUserPackageList() {
    $(".header-nav a").bind("click", function() {
        var url = $(this).attr("data-url");
        $(".header-nav a").addClass("aui-active");
        $(this).removeClass("aui-active");
        var type = $(this).attr("data-type");
        if(type == 'make') {
            $(".footer-back p").html("礼包记录(发出)");
        } else {
            $(".footer-back p").html("礼包记录(收到)");
        }
        packageList(url);
    })
    function packageList(url) {
        $(".loading-panel").show();
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'html',
            success: function(data) {
                setTimeout(function() {
                    $(".loading-panel").hide();
                    $("#send-card").html(data);

                }, 200)
            },
            error: function() {
                insertLighterPop("请刷新重试");
            }
        })
    }
    packageList('?ac=userpackage&op=list&type=make')
}
function setTimeCountImg(url,container) {
    var count = 3;
    setInterval(function() {
        count --;
        if(count < 1) {
            if(url) window.location.href = url;
        }
        $(container).html("<span class=\"count-num num-"+count+"\"></span>");
    }, 1000)
}
function listAjaxLoader(type, remainHeight) {
    if(type == null) type = "get";
    if(remainHeight == null) remainHeight = 0;
    $("#scroll").attr("style", "height:"+($(window).height()-remainHeight)+"px;overflow-y:scroll;");

    var containerHeight = $("#scroll").height();
    var contentHeight = $("#scroll .scroll-content").height();
    var load = 1;
    var offset = contentHeight - containerHeight;
    var url = $("#scroll").attr("data-url")+"&ajax=1";
    var page = 2;

    $("#scroll").scroll(function() {
        offset = $("#scroll .scroll-content").height()-$("#scroll").height();

        if(load==1 && $(this).scrollTop() >= offset) {
            url += "&page="+page;
            $("#scroll .list-loading").show();
            load = 0;

            $.ajax({
                url: url,
                type: type,
                dataType: 'json',
                success: function(data) {
                    if(data.status == 'success') {
                        var node = "";
                        var arr = data.list;
                        if(arr.length != 0) {
                            $(arr).each(function(index, ele) {
                                node += setNode(ele);
                            });
                        }

                        setTimeout(function() {
                            if(node != "") {
                                $("#scroll .list-loading").hide();
                                $("#scroll .list-content").append(node);
                                load = 1;
                            } else {
                                $("#scroll .list-loading").html("<p class=\"invalid-tip\">无更多数据</p>");
                            }
                            
                        }, 200);

                    } else {
                        insertLighterPop("请刷新重试");
                    }
                    page ++;
                },
                error: function(data) {
                    insertLighterPop("请刷新重试");
                }

            })
        }
    })
}

// 图片上传
function setUploardImg(input, target) {
    $(input).bind("change", function(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $(target).attr("src", e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    })
}

function setListSwitch(tab, content, type) {
    if(type == null) type = "get";
    $(tab+" a").bind("click", function() {
        $(tab+" a.active").removeClass("active");
        $(this).addClass("active");
        var url = $(this).attr("data-url")+"&tabswitch=1";
        $.ajax({
            url: url,
            type: type,
            success: function(data) {
                $(content).html(data);
            },
            error: function() {
                insertLighterPop("请刷新重试");
            }
        });
    })
}

function clickListLoader(btn, content, type) {
    if(type == null) type = "get";
    var enable = 1;
    var page = 2;
    var url = $(btn).attr("data-url")+"&page="+page;

    $(btn).bind("click", function() {
        if(enable == 1) {
            enable = 0;
            url = $(btn).attr("data-url")+"&page="+page;
            $(btn).html("").addClass("show");

            $.ajax({
                url: url,
                type: type,
                dataType: 'json',
                success: function(data) {
                    if(data.status == 'success') {
                        var node = "";
                        var arr = data.list;
                        if(arr.length != 0) {
                            $(arr).each(function(index, ele) {
                                node += setNode(ele);
                            });
                        }

                        setTimeout(function() {
                            if(node != "") {
                                $(btn).html("更多").removeClass("show");
                                $(content).append(node);
                                enable = 1;
                            } else {
                                $(btn).html("无更多数据").removeClass("show");
                            }
                            
                        }, 200);

                    } else {
                        insertLighterPop("请刷新重试");
                    }
                    page ++;
                },
                error: function(data) {
                    insertLighterPop("请刷新重试");
                }

            })
        }
    })
}