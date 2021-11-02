<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css" type="text/css">
</head>

<body>
    <header>
        <!--親要素:flex-->
        <h1>
            <a href="../../index.html">Cocha</a>
        </h1>

        <nav class="nav">
            <ul>
                <li><a href="../../index.html">HOME</a></li>
                <li><a href="../../service/service.html">SERVICE</a></li>
                <li><a href="../../contact/contact.html">CONTACT</a></li>
            </ul>
        </nav>

        <button type="button" id="navbtn"></button>
        <script>
            document.getElementById("navbtn").onclick = function() { //ボタンを押したとき
                document.querySelector('html').classList.toggle('open_nav');
            }
        </script>
    </header>
    <main>
    </main>
 
</body>

</html>

<?php
//ユーザ情報
$name = $_POST["user_name"];
session_start();
$_SESSION['user_name'] = $name; //変数をセッションに保存
$pass = $_POST["pass"];

// DB接続設定
$dsn = "mysql:dbname=****;host=****";
$user = "****";
$db_pass = "****";
try {
    $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
} catch (PDOException $e) {
    echo "データベース接続失敗：";
    echo $e->getMessage() . "<br>";
}

$sql = "SELECT * FROM user_data WHERE name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":name", $name, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();


if (!empty($result)) {
    if (password_verify($pass, $result["pass"])) {
        http_response_code(301);
        header("Location: ./mypage/mypage.html");
        exit();
    } else {
        echo "ログイン認証に失敗しました<br>";
        echo "<a href='../../index.html'>ログインページへ</a>";
    }
} else {
    echo "ユーザが存在しません。<br>";
    echo "<a href='../../index.html'>ログインページへ</a>";
}
?>