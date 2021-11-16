<?php
    //phpmailerライブラリ
    require '../phpmailer/src/Exception.php';
    require '../phpmailer/src/PHPMailer.php';
    require '../phpmailer/src/SMTP.php';
    require 'setting.php';
    
    session_start();
    if($_POST["token"] !== $_SESSION["token"]){
        echo "不正なアクセスです。";
        die();
    }

    //クリックジャッキング対策
    header("X-FRAME-OPTIONS: SAMEORIGIN");

    //エラーメッセージ
    $errors = array();
    
    //ユーザ情報
    $name = $_POST["user_name"];
    $email = $_POST["email"];
    $pass = $_POST["pass"];

    // DB接続用の変数
    $dsn = "mysql:dbname=データベース名;host=ホスト名";
    $user = "ユーザ名";
    $db_pass = "パスワード";

    try{    
        //データベース接続
        $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
        //テーブル作成(仮登録済み会員)
        $sql = "CREATE TABLE IF NOT EXISTS pre_user_data"
                ."("
                ."user_id INT AUTO_INCREMENT PRIMARY KEY,"
                ."name VARCHAR(24) NOT NULL,"
                ."email TEXT NOT NULL,"
                ."pass TEXT NOT NULL,"
                ."urltoken TEXT NOT NULL,"
                ."date DATETIME NOT NULL,"
                ."flag TINYINT(1) NOT NULL DEFAULT 0"
                .");";
        $stmt = $pdo->query($sql);
    
        //テーブル作成(本登録済み会員)
        $sql = "CREATE TABLE IF NOT EXISTS user_data"
                ."("
                ."user_id INT AUTO_INCREMENT PRIMARY KEY,"
                ."name VARCHAR(24) NOT NULL,"
                ."email TEXT NOT NULL,"
                ."pass TEXT NOT NULL,"
                ."created_date DATETIME NOT NULL,"
                ."updated_date DATETIME NOT NULL"
                .");";
        $stmt = $pdo->query($sql);

    }catch(PDOException $e){
        echo $e->getMessage()."<br>";
        die();
    }
    
    //本登録ユーザとの名前重複確認
    $sql = "SELECT * FROM user_data WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();
    if(!empty($result)){ //重複していた場合
        $errors["overlap_name"] = "入力されたユーザ名は既に使われております。";
    }
    
    //本登録ユーザとのメールアドレス重複確認
    $sql = "SELECT * FROM user_data WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();

    if(!empty($result)){ //メールアドレスが重複していた場合
        $errors["overlap_email"] = "入力されたメールアドレスは既に利用されております。";
    }

    if(count($errors) == 0){ //エラーなしの場合

        //url作成
        $urltoken = hash("sha256",uniqid(rand(),1));
        $url = "http://localhost/cocha/access/signup/register.php?urltoken=".$urltoken;
    
        //パスワードハッシュ化
        $hash_pass = password_hash($pass,PASSWORD_DEFAULT);

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
        $mail->Subject = "【重要】cocha への仮登録ありがとうございます"; // メールタイトル
        $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
        $body = 
            "<!DOCTYPE html>
            <html lang='ja'>
            
            <head>
                <meta charset='UTF-8'>
                <title>pre_Register</title>
            </head>
            
            <body>
                <div>
                    <p>Webサービスcocha への仮登録ありがとうございます!</p>
                    <p>下記のURLに24時間以内にアクセスして、本登録を完了させてください。</p>
                    <br>
                    <p>URL→".$url."</p>
                </div>
            </body>
            
            </html>";
    
        $mail->Body  = $body; // メール本文
        // メール送信の実行
        if(!$mail->send()) {
            // $mail->ErrorInfo;
            $errors["sendmail_faild"] = "メール送信に失敗しました。";            
        }else{
            
            try{
                $date = date("Y-m-d H:i:s");
                
                //仮登録
                $sql = "INSERT INTO pre_user_data(name, email, pass, urltoken, date) VALUES(:name, :email, :pass, :urltoken, :date)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":pass", $hash_pass, PDO::PARAM_STR);
                $stmt->bindParam(":urltoken", $urltoken, PDO::PARAM_STR);
                $stmt->bindParam(":date", $date, PDO::PARAM_STR);
                $stmt->execute();
                
            }catch(PDOException $e){
                echo $e->getMessage();
                die();
            }

            //セッション変数を空にする
            $_SESSION = array();

            //クッキー削除
            if(!empty($_COOKIE["PHPSESSID"])){
                setcookie("PHPSESSID", "", time() - 1800, "/");
            }

            //セッションを破棄
            session_destroy();

            $pdo = NULL;

            $message = 
                "<div>
                    <h1>会員登録はまだ済んでいません！</h1>
                    <p>ご入力いただいたメールアドレス宛に仮登録メールを送信しました。</p>
                    <p>メールに記載されている、URLにアクセスして本登録を完了させてください。</p>
                    <small>*URLの有効期限は24時間です。</small>
                </div>";

            
        }

    }

    
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>pre_Register</title>
    <link rel="stylesheet" href="pre_register.css" type="text/css">
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
        <?php if(count($errors) == 0): ?>
            <?php echo $message; ?>

        <?php else: ?>
            <?php foreach($errors as $error){ //エラーメッセージ表示
                echo "<p>".$error."</p>";
            }
            echo "<a href='signup.php'>新規登録画面に戻る</a>";
            ?>
        <?php endif; ?>
     

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