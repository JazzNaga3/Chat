$(function () {
    //アイコン操作
    $(".icon").on("click", function () {
        if ($(this).hasClass("now")) {
            $(".now").removeClass("now");
            $(".menuItems").removeClass("active");
        } else {
            const idx = $(".icon").index(this);
            $(".now").removeClass("now");
            $(this).addClass("now");
            $(".menuItems").removeClass("active");
            $(".menuItems").eq(idx).addClass("active");
        }
    });

    //アイテム操作
    $(".Items").on("click", function () {
        const idx = $(".Items").index(this);
        $(".page").removeClass("show");
        $(".page").eq(idx).addClass("show");
        $(".now").removeClass("now");
        $(".menuItems").removeClass("active");
    });

    //フレンドリクエスト承認
    $(".approval_button").on("click",function(){
        const idx = $(".approval_button").index(this);
    });

    //フレンドリクエスト拒否
    $(".rejection_button").on("click",function(){
        const idx = $(".rejection_button").index(this);
    });

    //トーク画面 送信処理
    $("input.submit").on("click",function(){
        if($("#msg").val() != ""){
            $.ajax({
                type: "post",
                url: "write.php",
                data: {
                    // キー名 : 値
                    mode: 1,
                    message: $("#msg").val(), //phpファイルで使う
                },
            })
                .done(function (data) {
                    $("#msg_box").html(data);
                    
                })
                .fail(function (data) {
                    alert("メッセージ送信に失敗しました。");
                });
        }
    })

    //定期的にメッセージ読み込み
    setInterval(function(){
        $.ajax({
            type: "post",
            url: "write.php",
            data: {
                mode: 0,
            },
        })
            .done(function (data) {
                $("#msg_box").html(data);
            })
            .fail(function (data) {
                alert("メッセージ読み込みに失敗しました。");
            });
    }, 3000); //読み込み制限

});
