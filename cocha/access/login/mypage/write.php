<?php
    //データベース接続
    $dsn = "mysql:dbname=***;host=***";
    $user = "***";
    $db_pass = "***";
    try{
        $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }catch(PDOException $e){
        echo "データベース接続失敗：";
        echo $e->getMesseage()."<br>";
    }

    //テーブル作成(ない場合)
    $sql = "CREATE TABLE IF NOT EXISTS message_box"
            ."("
            ."id INT AUTO_INCREMENT PRIMARY KEY,"
            ."name VARCHAR(24) NOT NULL,"
            ."message TEXT NOT NULL"
            .");";
    $stmt = $pdo->query($sql);

    session_start();
    $name = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, "UTF-8"); //ログイン中のユーザ確認

    if($_POST["mode"] == 0){ //読込モード
        
        $sql = "SELECT * FROM message_box";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll();
        if(!empty($result)){ //メッセージがある場合
            foreach($result as $row){
                if($row["name"] == $_SESSION['user_name']){ //送り主が自分の場合
                    echo "<div class='right_bubble'>".$row["message"]."</div>";
                }else{ //他人の場合
                    echo "<div class='left_bubble'>".$row["message"]."</div>";
                }
            }
        }else{
            exit();
        }

    }else if($_POST["mode"] == 1){ //書き込みモード

        if(!empty($_POST["message"])){
            $message = htmlspecialchars($_POST['message'], ENT_QUOTES, "UTF-8");
            echo $message; //htmlに表示
    
            $sql = "INSERT INTO message_box(name, message) VALUES (:name, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":message", $message, PDO::PARAM_STR);
            $stmt->execute();
        }else{
            exit();
        }

    }

?>