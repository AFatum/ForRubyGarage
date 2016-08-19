<?php
    require("db.php");
	require("lib.php");

    $name = clear($_POST['newName']);
    $idTsk = (int) abs($_POST['idTsk']);
    $idPro = (int) abs($_POST['idPro']);
    echo "<pre>";
    var_dump($idTsk, $idPro, $name);
    echo "</pre>";
    exit;
    //if(uptTask($idTsk, $idPro, $name)) header("Location: http://ruby.ua/index.php");
?>