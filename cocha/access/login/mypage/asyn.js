//ログファイル読み込み
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

}

//ログファイル書き込み
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

}

//DOMの読み込み完了時
$(document).ready(function () {
    loadMessage();
    setInterval("loadMessage()", 2000); //読み込み制限
});