<?php
// подключение библиотек
	require("../secure/session.inc.php");
	require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
    if(isset($_GET['logOut'])) 
    {
        session_destroy();
        setcookie ("basket", "", time() - 3600);    
    }
    if(isset($_SESSION['control'])) $con = "Вы авторизировались, как ".selectBasNameUsers($_SESSION['control'], 1);
    else $con = "Добрый день, Гость!";
    //if(isset($_GET['logOut'])) logOut();
/*
Что нужно сделать еще:
- Добавить возможность изменять количество товара в корзине с возможностью пересчёты общей цены заказа - выполнено на 90%
- Добавить карточку товара
- Добавить ссылку из каталога на карточку товара
- Добавить возможность загружать картинку товара
- Добавить возможность выставлять рейтинг товара
- Добавить комментарии к товару
- Добавить возможность регистрации пользователей
*/
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>eshop - Главная</title>
	</head>
	<body>
        <h1>Администрирование магазина</h1>
        <h3><?= $con ?></h3>
        <h2>Доступные действия: </h2>
        <ul>
            <li><a href="add2cat.php">Добавить в каталог</a></li>
            <li><a href="show2cat.php">Показать каталог</a></li>
            <li><a href="basket.php">Корзина</a></li>
            <li><a href="NewOrder.php">Оформление заказа</a></li>
            <li><a href="orders.php">Поступившие заказы</a></li>
            <li><a href="../secure/addUser2.php">Регистрация</a></li>
            <li><a href="../secure/login2.php">Ввойти</a></li>
            <li><a href="index.php?logOut=true">Завершить сеанс</a></li>
        </ul>
	</body>
</html>