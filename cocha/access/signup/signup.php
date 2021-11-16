<?php
    session_start();
    //CSRF対策
    $_SESSION["token"] = base64_encode(openssl_random_pseudo_bytes(32)); 
    $csrf_token = $_SESSION["token"];
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link rel="stylesheet" href="signup.css" type="text/css">
</head>

<body ng-controller="RegisterCtrl" ng-app="myApp">
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
            document.getElementById("navbtn").onclick = function () { //ボタンを押したとき
                document.querySelector('html').classList.toggle('open_nav');
            }
        </script>
    </header>

    <main>
        <div class="container">
            <form action="pre_register.php" method="POST">
                <div class="formarea">
                    <h1>Create a new Account</h1>
                    <div class="form-item">
                        <label for="username">Username(Login ID)</label>
                        <input type="name" name="user_name" placeholder="Username" required="required" minlength="4"
                            maxlength="12">
                    </div>
                    <div class="form-item">
                        <label for="email">Email-Address</label>
                        <input type="email" name="email" placeholder="Email-Address" required="required">
                    </div>
                    <div class="form-item">
                        <label for="password">Password</label>
                        <input type="password" name="pass" placeholder="Password" required="required" minlength="8"
                            maxlength="16">
                    </div>
                    <div class="button-panel">
                        <input type="submit" name="sign_up" class="button" title="Sign up" value="SIGN UP">
                    </div>
            </div>
                <input type="hidden" name="token" value="<?=$csrf_token?>">
            </form>
        </div>
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