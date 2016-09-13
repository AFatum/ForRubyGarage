<?php
    session_start();
    $control = false;
    $uri = $_SERVER['REQUEST_URI'];
    $page = array ("/admin/show2cat.php",
                   "/admin/index.php",
                   "/admin/delete2basket.php",
                   "/secure/addUser2.php",
                   "/admin/ocenka.php",
                   "/admin/add2basket.php",
                   "/admin/NewOrder.php",
                   "/admin/book.php",
                   "/admin/basket.php");
    foreach($page as $req)
    {
        for($i=6; $i<=25; $i++)
        {
            if(substr($uri, 0, $i) == $req or $uri == '/admin/')
            {  $control = true; break(2); }
        }
        
        /*
        if  (substr($uri, 0, 25) == $req or
             substr($uri, 0, 22) == $req or
             substr($uri, 0, 30) == $req or
             substr($uri, 0, 26) == $req or
             substr($uri, 0, 27) == $req or
             substr($uri, 0, 21) == $req or
             substr($uri, 0, 23) == $req)
        {  $control = true; break; }            
        */
    }
    if (!isset($_SESSION['control']) && !$control)
    {
        header('Location: /secure/login2.php?ref='.$uri);
        exit;
    }
?>