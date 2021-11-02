<?php
    //変数用意
    $log_file = "message.log";
    if(file_exists($log_file)){ //ファイルがある時

        session_start();
        $name = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, "UTF-8"); //login.phpで保存したuser_name
    
        //logファイル読込&タグ追加
        if($_POST["mode"] == 0){ 
            $log = file($log_file, FILE_IGNORE_NEW_LINES); //配列として取り出す
            foreach ($log as $str){
                $message = explode("<>", $str);
                if($message[0] == $_SESSION['user_name']){
                    $tag_message = "<div class='right_bubble'>".$message[1]."</div>";
                }else{
                    $tag_message = "<div class='left_bubble'>".$message[1]."</div>";
                }
                
                echo $tag_message;
            }
        }else if($_POST["mode"] == 1){ //logファイル書き込み
    
            $message = "";
            if (!empty($_POST['message'])) { //メッセージが空でない時
    
                //送り主とメッセージ保存
                $message = htmlspecialchars($_POST['message'], ENT_QUOTES, "UTF-8");
                $str = $name."<>".$message.PHP_EOL;
                
                //ファイル書き込み
                file_put_contents($log_file, $str, FILE_APPEND);
                
            }else{
                exit;
            }
    
        }

    }else{ //ファイルが無い時
        exit;
    }


?>
