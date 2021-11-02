<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css" type="text/css">
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
    <footer>
        <p>
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aliquam, adipisci asperiores. Nam exercitationem
            architecto soluta adipisci, culpa tempore eligendi atque nobis molestias recusandae. Ipsam cum magni quia
            consequatur facilis architecto?
        </p>
        <p>
            © Practice.2021 All Rights Reserved.
        </p>
    </footer>
</body>

</html>

<?php
    //phpmailerライブラリ
    require '../phpmailer/src/Exception.php';
    require '../phpmailer/src/PHPMailer.php';
    require '../phpmailer/src/SMTP.php';
    require 'setting.php';
    
    //ユーザ情報
    $name = $_POST["user_name"];
    $email = $_POST["email"];
    $pass = $_POST["pass"];

    // DB接続設定
    $dsn = "mysql:dbname=****;host=****";
    $user = "****";
    $db_pass = "****";
    try{
        $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }catch(PDOException $e){
        echo "データベース接続失敗：";
        echo $e->getMesseage()."<br>";
    }

    //テーブル作成(ない場合)
    $sql = "CREATE TABLE IF NOT EXISTS user_data"
            ."("
            ."id INT AUTO_INCREMENT PRIMARY KEY,"
            ."name VARCHAR(24) NOT NULL,"
            ."email TEXT NOT NULL,"
            ."pass TEXT NOT NULL"
            .");";
    $stmt = $pdo->query($sql);
    
    //ユーザ名の重複確認
    $sql = "SELECT * FROM user_data WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();
    if(!empty($result)){ //重複していた場合
        echo "入力したユーザ名は既に使われています。別のユーザ名に変更してください<br>";
        echo "<a href='signup.html'>新規登録画面に戻る</a>";
        die();
    }
    
    //メールアドレスの重複確認
    $sql = "SELECT * FROM user_data WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();

    if(!empty($result)){ //メールアドレスが重複していた場合
        echo "入力されたメールアドレスはすでに使われています！<br>";
        echo "<a href='signup.html'>新規登録画面に戻る</a>";
    }else{ //メールアドレスが重複してない場合
        
        // PHPMailerのインスタンス生成
        $mail = new PHPMailer\PHPMailer\PHPMailer();

        $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
        $mail->SMTPAuth = true;
        $mail->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
        $mail->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
        $mail->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
        $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
        $mail->Port = SMTP_PORT; // 接続するTCPポート
        
        // メール内容設定
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";
        $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
        $mail->addAddress($email, $name."さん"); //受信者（送信先）を追加する
        // $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        // $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
        // $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
        $mail->Subject = MAIL_SUBJECT; // メールタイトル
        $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
        $body = "このたびは、Webサービス「cocha」への新規ご登録ありがとうございます。";

        $mail->Body  = $body; // メール本文
        // メール送信の実行
        if(!$mail->send()) {
            echo "メッセージは送られませんでした！<br>";
            // echo "Mailer Error: " . $mail->ErrorInfo . "<br>";
            echo "<a href='signup.html'>新規登録画面に戻る</a>";
        } else {
            //パスワードハッシュ化 & データベースに情報登録
            $hash_pass = password_hash($pass,PASSWORD_DEFAULT);
            $sql = "INSERT INTO user_data(name, email, pass) VALUES (:name, :email, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":pass", $hash_pass, PDO::PARAM_STR);
            $stmt->execute();
            
            echo "送信完了！<br>";
            echo "会員登録が完了しました<br>";
            echo "<a href='../../index.html'>ログインページへ</a>";
        }
    }