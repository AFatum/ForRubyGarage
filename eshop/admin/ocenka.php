<?php
    require("../secure/session.inc.php");
    require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");

$id = (int) abs($_GET['id']); // запоминаем параметр id товара
$uriLink = (string) $_GET['link']; // запоминаем страницу от куда пришли
if($_GET['val'] === false)
{ // очистка рейтинга, в случае поступления значения false
    if(!clearOcenka($id)) // очищаем рейтинг по ID товара
        { echo "Произошла ошибка БД, при обнулении оценки - ".mysqli_error($link); exit; }
    else
        {  
            unset($_SESSION['result']); // удаляем основную переменную $_SESSION['result']
            //$_SESSION['result'] = selectItemToCatalog($_SESSION['catType'], $_SESSION['countGoods'], $_SESSION['catDesc']);
            header("Location: ".$uriLink); // возвращаем на страницу от куда пришли
            exit; 
        }
}
// если очищать рейтинг не нужно, то продолжаем работу...
$val = (int) abs($_GET['val']); // получаем значение оценки пользователя
$count = (empty($_GET['count'])) ? 0 : (int) abs($_GET['count']); // получаем "свежие" данные по количеству оценок
$sum = (empty($_GET['sum'])) ? 0 : (int) abs($_GET['sum']); // также получаем "свежие" данные по сумме оценок


$sum += $val; // прибавляем к сумме последнюю полученную оценку
$oc = (float) round($sum / $count++, 2); // рассчитываем новое значение рейтинга, округляем до двух знаков после запятой


if(!updateOcenka($id, $oc, $sum, $count)) // вносим изменения в рейтинг с новыми значениями
    { echo "Произошла ошибка БД, при занесении оценки - ".mysqli_error($link); exit; }
else
{ // обновляем данные в основной сессионной переменной $_SESSION['result']
    if(!$_SESSION['result'] = selectItemToCatalog($_SESSION['catType'], $_SESSION['countGoods'], $_SESSION['catDesc']))
        { echo "Произошла ошибка БД, при обновлении оценки - ".mysqli_error($link); exit; }
    else // возвращаем на страницу от куда пришли
        {  header("Location: ".$uriLink); exit; }
}
?>