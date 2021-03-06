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
    <!-- <footer>
        <p>
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aliquam, adipisci asperiores. Nam exercitationem
            architecto soluta adipisci, culpa tempore eligendi atque nobis molestias recusandae. Ipsam cum magni quia
            consequatur facilis architecto?
        </p>
        <p>
            © Practice.2021 All Rights Reserved.
        </p>
    </footer> -->
</body>

</html>

<?php
//ユーザ情報
$name = $_POST["user_name"];
$pass = $_POST["pass"];

// DB接続
require_once("../../function.php");
$pdo = connectDB_access();

$sql = "SELECT * FROM user_data WHERE name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":name", $name, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();

if (!empty($result)) {
    if (password_verify($pass, $result["pass"])) {
        $user_id = $result["user_id"];
        //セッションに保存
        session_start();
        $_SESSION["user_id"] = $user_id;
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