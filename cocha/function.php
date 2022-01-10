<?php
    function connectDB_access(){
        // DB接続用の変数
        $dsn = "mysql:dbname=データベース名;host=ホスト名";
        $user = "ユーザ名";
        $db_pass = "パスワード";
    
        try{
            //データベース接続
            $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            return $pdo;
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    function connectDB_chat(){

        $dsn = "mysql:dbname=データベース名;host=ホスト名";
        $user = "ユーザ名";
        $db_pass = "パスワード";
    
        try{
            $pdo = new PDO($dsn, $user, $db_pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            return $pdo;
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }
?>