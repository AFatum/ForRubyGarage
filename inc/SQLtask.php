<?php
    require("db.php");
    require("lib.php");

if($_POST['GetOrder'] and $_POST['Get1'])
{
    switch($_POST['GetOrder'])
    {
        case "cntEachPro":
            $_SESSION['SQL'] = "cntEachPro";
            //$linkRef = ($_GET['sort'] == 'arsort') ? "arsort" : "asort";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=asort");
            exit;
        break;
        case "cntEachNms":
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=ksort");
            exit;
        break;
        case "dupTsk":
            $_SESSION['SQL'] = "dupTsk";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=ksort");
            exit;
        break;
        case "Garage":
            $_SESSION['SQL'] = "Garage";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask");
            exit;
        break;
        case "more10":
            $_SESSION['SQL'] = "more10";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=asort10");
            exit;
        break;
    }
    //header("Location: https://stormy-river-47352.herokuapp.com//index.php?id=SQLtask");
    //exit;
}

if(isset($_POST['statuses']) and $_POST['Get4'])
{
    $_SESSION['SQL'] = "statuses";
    $_SESSION['sts'] = $_POST['statuses'];
    header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask");
    exit;
}
if(isset($_POST['beginLetter']) and $_POST['Get2'])
{
    $_SESSION['SQL'] = "beginLetter";
    $_SESSION['let'] = $_POST['beginLetter'];
    header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask");
    exit;
}
if(isset($_POST['middleLetter']) and $_POST['Get3'])
{
    $_SESSION['SQL'] = "middleLetter";
    $_SESSION['let'] = $_POST['middleLetter'];
    header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask");
    exit;
}


if($_GET['sort'])
{
    switch($_GET['sort'])
    {
        case 'arsort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=arsort");
            exit;
        break;
            
        case 'asort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=asort");
            exit;
        break;
        
        case 'ksort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=ksort");
        break;  
            
        case 'krsort':
            $_SESSION['SQL'] = "cntEachPro";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=krsort");
        break;    
        case 'ksortDup':
            $_SESSION['SQL'] = "dupTsk";
            header("Location: https://stormy-river-47352.herokuapp.com/index.php?id=SQLtask&sort=ksortDup");
        break;    
    }
}
?>