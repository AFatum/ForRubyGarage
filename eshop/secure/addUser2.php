<?php
// подключение библиотек
    require("session.inc.php");
    require("secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>Создание нового пользователя</title>
		<meta charset="utf-8">
	</head>
	<body>
        <h1>Создание нового пользователя</h1>
        <?php
        // создаем основные переменные
            $login = "";
            $pass = "";
            $email = "";
            $res = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $login = clear($_POST['login']) ?: $login; // принимаем логин
            if(!userExists($login))
            {
                $pass = clear($_POST['pass']) ?: $pass;
                $hash = trim(password_hash($pass, PASSWORD_BCRYPT));
                $email = clear($_POST['email']) ?: $email;
                if(!empty($login) && !empty($email) && !empty($pass))
                {
                    if(saveUser($login, $email, $hash))
                        //$res = "Хэш ".$hash." успешно добавлен в файл";
                        $res = "Пользователь ".$login." успешно добавлен.";
                    else
                    {
                        //$res = "При записи хэша ".$hash." произошла ошибка!";
                        $res = "При добавлении пользователя ".$login." произошла ошибка - ".mysqli_error($link);
                    }
                }
                else $res = "Пожалуйта заполните все поля!";
            }
            else 
            {
                $res = "Пользователь ".$login." уже существует. Выберите другое имя";
            }
            $_SESSION['res'] = $res;
            header("Location: addUser2.php");
            exit;
        }
        else { 
            echo $_SESSION['res']; 
            unset($_SESSION['res']);
        }
        
        ?>
        <form action="" method="post">
            <p><input type="text" name="login" placeholder="Введите логин или Ваше имя"></p>
            <p><input type="text" name="email" placeholder="Введите вашу почту"></p>
            <p><input type="text" name="pass" placeholder="Введите пароль"></p>
            <p><input type="submit" value="Создать"></p>
        </form>
	</body>
</html>