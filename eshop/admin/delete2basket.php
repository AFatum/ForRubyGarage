<?php
// подключение библиотек
    require("../secure/session.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
	
	$id = abs((int)($_GET['id']));
	DeleteFromBasket($id);

    switch($_GET['show'])
    {
        case 'cat': 
	       header("Location: show2cat.php"); exit;
        case 'book': 
	       header("Location: book.php?book=".$_GET['id']); exit;
        default: 
	       header("Location: basket.php"); exit;
    }
?>