<?php
// подключение библиотек
    require("../secure/session.inc.php");
    require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
	
	$id = abs((int)($_GET['id']));
	$q = 1; // количество добавляемого товара
    if(isset($_SESSION['control']))
    {
        $basket[$id] = $q;
        if(!addUsersBas())
        {
            echo "Прозошла ошибка добавления в корзину ".mysqli_error($link);
            exit;
        }
    }


	add2Basket ($id, $q);

    if(!$_GET['show'])
    {
        header("Location: show2cat.php?id=".$_GET['id']);
        exit;
    }
    else
    {
        header("Location: book.php?book=".$_GET['id']);
        exit;
    }
?>