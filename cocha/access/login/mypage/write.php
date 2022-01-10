<?php
    //Ajax通信かどうか
    if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"){

        try{
            // DB接続
            require_once("../../../function.php");
            $pdo = connectDB_chat();

            // $talk_table = "";

            //トークテーブル作成(ない場合)
            $sql = "CREATE TABLE IF NOT EXISTS message_box"
                    ."("
                    ."msg_id INT AUTO_INCREMENT PRIMARY KEY,"
                    ."user_id INT,"
                    ."message TEXT NOT NULL,"
                    ."CONSTRAINT user_id_fk "
                    ."FOREIGN KEY(user_id) REFERENCES access.user_data(user_id) "
                    ."ON UPDATE CASCADE ON DELETE SET NULL"
                    .");";
            $stmt = $pdo->query($sql);
    
        }catch(PDOException $e){
            echo $e->getMesseage()."<br>";
        }
    
    
        session_start();
        $user_id = $_SESSION["user_id"];
    
        if($_POST["mode"] == 0){ //読込モード
            
            $sql = "SELECT user_id, message FROM message_box";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll();
            if(!empty($result)){ //メッセージがある場合
                foreach($result as $row){
                    if($row["user_id"] == $_SESSION['user_id']){ //送り主が自分の場合
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
                $sql = "INSERT INTO message_box(user_id, message) VALUES (:user_id, :message)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam(":message", $message, PDO::PARAM_STR);
                $stmt->execute();

            }else{
                exit();
            }

            $sql = "SELECT user_id, message FROM message_box";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetchAll();
            if(!empty($result)){ //メッセージがある場合
                foreach($result as $row){
                    if($row["user_id"] == $_SESSION['user_id']){ //送り主が自分の場合
                        echo "<div class='right_bubble'>".$row["message"]."</div>";
                    }else{ //他人の場合
                        echo "<div class='left_bubble'>".$row["message"]."</div>";
                    }
                }
            }else{
                exit();
            }

    
        }
    }else{
        exit();
    }


?>
