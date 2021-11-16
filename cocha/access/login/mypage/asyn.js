$(function () { //htmlが読み込まれた後に実行

    //サイドバー
    var Accordion = function (el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;

        var dropdownlink = this.el.find('.dropdown');
        dropdownlink.on('click',
            { el: this.el, multiple: this.multiple },
            this.dropdown);
    }
    //プロトタイプ(dropdown)
    Accordion.prototype.dropdown = function (e) {
        var $el = e.data.el,
            $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenuItems').not($next).slideUp().parent().removeClass('open');
        }
    }

    var accordion = new Accordion($('.menu'), false);

    //タブ切り替え
    var tabs = $(".submenuItems"); //タブの数取得(配列)
    $(".submenuItems").on("click", function () {
        $(".now").removeClass("now");
        $(this).addClass("now"); //クリックしたらnowクラスを追加
        const idx = tabs.index(this); //何番目の要素か取得
        $(".content").removeClass("show");
        $(".content").eq(idx).addClass("show");
    })

    //定期的にメッセージ読み込み
    loadMessage();
    setInterval("loadMessage()", 3000); //読み込み制限
});

//ログ読み込み
function loadMessage() {

    $.ajax({
        // パラメータ定義
        type: 'post',
        url: 'write.php',
        data: {
            'mode': 0
        }
    }).done(function (data) {
        //通信成功時
        $('#msg_box').html(data);
    }).fail(function (data) {
        //通信失敗時
        alert("読み込み失敗");
    });

};

//送信ボタン押したときに書き込み
function writeMessage() {

    $.ajax({
        type: 'post',
        url: 'write.php',
        data: { // キー名 : 値
            'mode': 1,
            'message': $("#msg").val() //phpファイルで使う
        }
    }).done(function (data) {
        loadMessage();
        $("#msg").val(""); //送信後に空にする
    }).fail(function (data) {
        alert("書き込み失敗");
    });

};
