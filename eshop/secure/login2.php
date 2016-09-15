<?php
$title = 'Авторизация';
$login = '';
session_start();
header("HTTP/1.0 401 Unauthorized");
require_once("secure2.inc.php");
require_once("../inc/lib.inc.php");
require_once("../inc/db.inc.php");
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $login = trim(strip_tags($_POST['login']));
    $pw = trim(strip_tags($_POST['pw']));
    $ref = trim(strip_tags($_GET['ref']));
    if(!$ref) $ref = '/eshop/admin';
    if($login and $pw)
    {
        $res = control($login, $pw);
        if(!$res)
            $title = 'Неправильное имя пользователя или пароль.';
        else
        {
                $_SESSION['control'] = $res;
                header("Location: ".$ref);
                exit;
        }        
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