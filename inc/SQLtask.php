<?php
    require("db.php");
    require("lib.php");

if($_POST['GetOrder'])
{
    switch($_POST['GetOrder'])
    {
        case "cntEachPro":
            $_SESSION['SQL'] = "cntEachPro";
            //$linkRef = ($_GET['sort'] == 'arsort') ? "arsort" : "asort";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=asort");
            exit;
        break;
    }
        case "cntEachNms":
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=ksort");
            exit;
        break;
    }
    //header("Location: http://ruby.ua/index.php?id=SQLtask");
    //exit;
}
if($_GET['sort'])
{
    switch($_GET['sort'])
    {
        case 'arsort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=arsort");
            exit;
        break;
            
        case 'asort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=asort");
            exit;
        break;
        
        case 'ksort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=ksort");
        break;  
            
        case 'krsort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: http://ruby.ua/index.php?id=SQLtask&sort=krsort");
        break;
    }
}
?>