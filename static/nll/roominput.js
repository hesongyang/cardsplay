function roominput() {
    var inputs = $(".numInput");
    var active = (window.navigator.userAgent.toLowerCase()).indexOf("mobile") > -1?"touchstart":"click";
    $(inputs).each(function () {
        $(this).on(active, function () {
            var _this = $(this);
            $(_this).addClass("click");
            setTimeout(function () {
                $(_this).removeClass("click");
            }, 100);
            var dataType = this.getAttribute("data-type");
            if (dataType == "num") {
                var inputLength = $("#roomNum").val().length, num = this.innerHTML;
                if (inputLength < 6) {
                    $("#roomNum").val($("#roomNum").val() + num);
                    var selector = ".room-id-show li:nth-child(" + (inputLength + 1) + ") label.num-label";
                    $(selector).text(num);

                    if (inputLength + 1 == 6) {
                        //时入游戏
                        $.ajax({
                            type: "get",
                            dataType: 'json',
                            url: "/index.php?ac=gameroom&op=room&roomNum=" + $("#roomNum").val() + "",
                            success: function (data) {
                                if (data.status == "ok") {
                                    window.location.href = data.msg;
                                } else {
                                    pop_alert("error", "", data.msg);
                                }
                            }
                        });
                    }
                }
            } else if (dataType == "clear") {
                $("#roomNum").val("");
                $(".room-id-show label.num-label").text("");
            } else if (dataType == "back") {
                var value = $("#roomNum").val();
                value = value.substring(0, value.length - 1);
                $("#roomNum").val(value);
                var selector = ".room-id-show li:nth-child(" + (value.length + 1) + ") label.num-label";
                $(selector).text("");
            }
        });
      
    });
    for (var i = inputs.length - 1; i >= 0; i--) {
    }
}
$(document).ready(function () {
    roominput();
})