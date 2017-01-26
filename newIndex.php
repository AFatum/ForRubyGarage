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
        $oper->autoMess($_SESSION['control']);
    ?>
  </body>
</html>