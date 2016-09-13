<?php
    //require("../secure/session.inc.php");
    require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if(empty($_POST['title']) or empty($_POST['mess']) or empty($_POST['name']))
        {
            // здесь мы перенаправляем обратно на страницу с товаром, с дополнительным GET-параметром для вывода нужного сообщения 
            //header("Location: book.php?book=".$_POST['uri']."&err=2");
            header("Location: ".$_POST['uri']."&err=2");
            
            exit;
        }
        $title = clear($_POST['title']);
        $name = clear($_POST['name']);
        $mess = clear($_POST['mess']);
        $uri = (string) trim(substr($_POST['uri'], 0, 22));
        if(!addComment($title, $name, $mess))
        { echo "Произошла ошибка занесения комментариев в БД - ".mysqli_error($link); exit; }
        else
        {
            //$_SESSION['comment'] = 1;
            header("Location: ".$uri."&err=1");
            exit;
        }
        
    }

?>