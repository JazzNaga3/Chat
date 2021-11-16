<?php
    //phpmailerライブラリ
    require '../phpmailer/src/Exception.php';
    require '../phpmailer/src/PHPMailer.php';
    require '../phpmailer/src/SMTP.php';
    require 'setting.php';

    //クリックジャッキング対策
    header("X-FRAME-OPTIONS: SAMEORIGIN");

    //エラーメッセージ
    $errors = array();

    // DB接続用の変数
    $dsn = "mysql:dbname=データベース名;host=ホスト名";
    $user = "ユーザ名";
    $db_pass = "パスワード";
    try{
        //データベース接続
        $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }catch(PDOException $e){
        echo $e->getMesseage()."<br>";
        die();
    }

    if(empty($_GET)){ //$_GETが空の時
        header("Location: signup.php");
        exit();
    }else{
        //URLトークン定義
        $urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;

        if(empty($urltoken)){
            $errors["urltoken"] = "もう一度登録をやり直してください。";
        }else{

            
            $sql = "SELECT * FROM pre_user_data WHERE urltoken = :urltoken AND flag = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":urltoken", $urltoken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();

            //仮登録時に保存したユーザデータ取り出し
            $name = $result["name"];
            $email = $result["email"];
            $hash_pass = $result["pass"];
            $date = $result["date"]; //仮登録の日時
            $time = (strtotime("now") - strtotime($date)); //何時間経過したか計算
            
            if($time < 86400){ //24時間以内の場合

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
                $body =
                    "<!DOCTYPE html>
                    <html lang='ja'>
                    
                    <head>
                        <meta charset='UTF-8'>
                        <title>Register</title>
                    </head>
                    
                    <body>
                        <div>
                            <p>メール認証が完了したため、cocha への本登録が完了しました。</p>
                            <p>サービスがご利用可能となりましたので、ログインしたくさんの友達を作りましょう！</p>
                            
                        </div>
                    </body>
                    
                    </html>";
                $mail->Body  = $body; // メール本文

                // メール送信の実行
                if(!$mail->send()) {
                    // echo $mail->ErrorInfo;
                    $errors["sendmail_faild"] = "メール送信に失敗しました。もう一度登録をやり直してください。";
                } else {
                
                    try {
                        //本登録
                        $date = date("Y-m-d H:i:s");
                        $sql = "INSERT INTO user_data(name, email, pass, created_date, updated_date) VALUES(:name, :email, :pass, :created_date, :updated_date)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                        $stmt->bindParam(":pass", $hash_pass, PDO::PARAM_STR);
                        $stmt->bindParam(":created_date", $date, PDO::PARAM_STR);
                        $stmt->bindParam(":updated_date", $date, PDO::PARAM_STR);
                        $stmt->execute();

                        //仮登録状態解除
                        $sql = "UPDATE pre_user_data SET flag = 1 WHERE email = :email AND flag = 0";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                        $stmt->execute();
                    }catch(PDOException $e){
                        echo $e->getMessage();
                        die();
                    }

                    $pdo = NULL;

                    $message = 
                        "<div>
                            <h1>cocha への本登録が完了しました。</h1>
                            <p>サービスにログイン可能となりましたので、ぜひご利用ください。</p>
                        </div>";

                }

            }else{
                $errors["urltoken_timeover"] = "このURLは、有効期限が切れた等の問題があるため、ご利用できません。もう一度登録をやり直してください。";
            }
        }

    }
    
?>

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
        <?php if(count($errors) == 0): ?>
            <?php echo $message; ?>
        
        <?php else:?>
            <?php foreach($errors as $error){
                echo "<p>".$error."</p>";
            }
            echo "<a href='signup.php'>新規登録画面に戻る</a>";
            ?>

        <?php endif;?>
        
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