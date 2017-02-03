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
        echo $oper->autoForm;
        
        try
        {
          if($_SESSION['control'] and $_SESSION['control_id'])
            $oper->showPro();
          
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