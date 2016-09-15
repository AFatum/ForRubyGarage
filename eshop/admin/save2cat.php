<?php
// подключение библиотек:
require("../secure/session.inc.php");
require("../inc/lib.inc.php");
require("../inc/db.inc.php");

$author = clear($_POST['author']) ?: $author;
$title = clear($_POST['title']) ?: $title;
$opis = clear($_POST['opis']) ?: $title;
$pubyear = abs((int)$_POST['pubyear']) ?: $pubyear;
$price = abs((int)$_POST['price']) ?: $price;
$idChange = clear($_POST['change']) ?: $idChange;
$img = $_FILES['uploadfile'];
$ImG = false;

if(is_array($img) && !empty($img['name']))                           // если загрузили картинку, выполняем действия с ней.
//if($_FILE['uploadfile'])
{
    $ImG = "../img/";  
    //rename($img['tmp_name'], $NewName);
    if(!move_uploaded_file($_FILES['uploadfile']['tmp_name'], $ImG.$_FILES ['uploadfile']['name']))
    {   echo "Ошибка в перемещении файла"; exit;    }               // перемещаем изображение в нужную папку
    //$ImG .= $NewName.$_FILES['uploadfile']['name'];
    else 
    {
        $nn = rus2translit($_FILES['uploadfile']['name']);          // переименовываем, если в имени есть русские буквы
        if($nn != $_FILES['uploadfile']['name'])
            rename($ImG.$_FILES ['uploadfile']['name'], $ImG.$nn);  // переименование
        $ImG .= $nn;
        $ImG = clear($ImG);                                         // записываем в переменную имя картинки
        //echo "Файл перемещен успешно: ".$ImG; 
        //exit;   
    }
}

if(isset($_POST['change']))                                         // внесение изменений в уже существующий товар
{
    $par = array();                                                 // основной массив данных с параметрами
    foreach($_POST as $key => $value)
    {                                                               // вносим данные в массив
        if($_POST['author'] && $key == 'author')     { $par[$key] = $author; continue; }
        if($_POST['title'] && $key == 'title')       { $par[$key] = $title; continue; }
        if($_POST['pubyear'] && $key == 'pubyear')   { $par[$key] = $pubyear; continue; }
        if($_POST['price'] && $key == 'price')       { $par[$key] = $price; continue; }
        if($_POST['opis'] && $key == 'opis')         { $par[$key] = $opis; continue; }
        continue;
    }
    if(!empty($img) && !empty($ImG)) $par['img'] = $ImG;            // вносим в массив параметр адреса изображения

    if(count($par) > 0)                                             // если есть хотя бы один параметр для изменения,.
    {                                                               // .. то вносим изменение данных в БД из массива $par
        if(!updateItemToCatalog($idChange, $par)) { echo "Ошибка получения данных: ".mysqli_error($link); exit; }
        else                                                        
        {  
            $_SESSION['resultAdd'] = "update";                      // вносим изменения в сессию для выдачи нужного сообщения..
            header("Location: add2cat.php?change=true");            // .. и перенаправляем обратно на страницу изменения товара
            exit;
        } 
    }
    else                                                            // если никаких данных для изменения не поступало,.
    {                                                               // .. то выдаем сообщение об ошибки, про это.
        $_SESSION['resultAdd'] = "err2";
        header("Location: add2cat.php?change=true");
        exit;
    }  
}
                                                                    // добавление нового товара
if(!isset($_POST['change']) && isset($_POST['author']) && isset($_POST['title']) && isset($_POST['pubyear']) && isset($_POST['price']))
{                                                                   // проверяем заполнение всех полей данных
    
    if (!addItemToCatalog($title, $author, $pubyear, $price))       // вносим данных в БД, с предварительной обработкой
    { 	echo 'Произошла ошибка при добавлении товара';	}
    else
    {
        $_SESSION['resultAdd'] = "newAdd";                          // вносим изменения в сессию для выдачи нужного сообщения..          
        header("Location: add2cat.php");                            // .. и перенаправляем обратно на страницу добавления товара
        exit;
    }
}
else
{
     $_SESSION['resultAdd'] = "err";
    header("Location: add2cat.php");
    exit;
}
?>