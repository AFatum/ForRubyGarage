<?php
	require("inc/db.php");
	require("inc/lib.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="../inc/stl.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>TODO List</title>
</head>
<body>
   <?php
        //echo "<p class='login'>Your user's email: ".$_SESSION['control']." - <a href='index.php?log=out' title='logout'>Log out</a></p>";
        //$oper->autoMess($_SESSION['control']);
        // 1.1 - если пользователь не авторизирован, отображаем форму авторизации
        //if(!$_SESSION['control']) $oper->autorization();
        if(!$_SESSION['control']) echo $oper->autoForm;
        
        try
        {
          if($_POST) // ** - обработка параметров POST
           $oper->postAdapter();

          if($_GET) // ** - обработка параметров GET
            $oper->getAdapter();
        }
        catch( Exception $e ) 
          { echo $e->getMessage(); }

    ?>
  </body>
</html>