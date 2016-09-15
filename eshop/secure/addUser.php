<?php
// подключение библиотек
    require("session.inc.php");
    require("secure.inc.php");
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
            $res = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $login = $_POST['login'] ?: $login; // принимаем логин
            if(!userExists($login))
            {
                $pass = $_POST['pass'] ?: $pass;
                $hash = getHash($pass);
                if(saveUser($login, $hash))
                    $res = "Хэш ".$hash." успешно добавлен в файл";
                else
                {
                    $res = "При записи хэша ".$hash." произошла ошибка!";
                }
            }
            else 
            {
                $res = "Пользователь ".$login." уже существует. Выберите другое имя";
            }
            $_SESSION['res'] = $res;
            header("Location: addUser.php");
            exit;
        }
        else { 
            echo $_SESSION['res']; 
            unset($_SESSION['res']);
        }
        
        ?>
        <form action="" method="post">
            <p><input type="text" name="login" placeholder="Введите логин"></p>
            <p><input type="text" name="pass" placeholder="Введите пароль"></p>
            <p><input type="submit" value="Создать"></p>
        </form>
	</body>
</html>