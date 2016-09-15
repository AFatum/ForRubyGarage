<?php
$title = 'Авторизация';
$login = '';
session_start();
header("HTTP/1.0 401 Unauthorized");
require_once("secure.inc.php");
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $login = trim(strip_tags($_POST['login']));
    $pw = trim(strip_tags($_POST['pw']));
    $ref = trim(strip_tags($_GET['ref']));
    if(!$ref) $ref = '/eshop/admin';
    if($login and $pw)
    {
        if($res = userExists($login))
        {
            list($_, $hash) = explode(':', $res);
            if(checkHash($pw, $hash))
            {
                $_SESSION['admin'] = true;
                header("Location: ".$ref);
                exit;
            }
            else $title = 'Неправильное имя пользователя или пароль.';
        }
        else $title = 'Неправильное имя пользователя или пароль.';
    }
    else $title = 'Заполните все поля формы!';
}

?>

<!DOCTYPE HTML>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>Авторизация</title>
		<meta charset="utf-8">
	</head>
	<body>
        <h1><?= $title?></h1>
        <form action="" method="post">
            <p><input type="text" name="login" placeholder="Введите логин"></p>
            <p><input type="text" name="pw" placeholder="Введите пароль"></p>
            <p><input type="submit" value="Авторизация"></p>
        </form>
	</body>
</html>