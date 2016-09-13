<?php
// подключение библиотек
    require("../secure/session.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
?>
<html>
	<head>
		<title>Форма оформления заказа</title>
	</head>
	<body>
        <h3>Пожалуйста укажите Ваши персональные данные</h3>
		<form action="" method="post">
            <p><input type="text" name="client" placeholder="Клиент"></p>
            <p><input type="text" name="email" placeholder="Почта"></p>
            <p><input type="text" name="tel" placeholder="Телефон"></p>
            <p><input type="text" name="adress" placeholder="Адрес доставки"></p>
            <p><input type="submit" value="заказать"></p>
		</form>
	</body>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $_SESSION['client'] = clear($_POST['client']);
    $_SESSION['email'] = clear($_POST['email']);
    $_SESSION['tel'] = clear($_POST['tel']);
    $_SESSION['adress'] = clear($_POST['adress']);
    if (isset($_SESSION['client']) && isset($_SESSION['email']) && isset($_SESSION['tel']) && isset($_SESSION['adress']))
    {   header("Location:".$_SERVER[PHP_SELF]); exit;   }
    else
    {   echo "<h3>Заполните все поля!</h3>"; exit;  }
}
else
{
    if(isset($_SESSION['arrBas']) && isset($_SESSION['client']) && isset($_SESSION['email']) && isset($_SESSION['tel']) && isset($_SESSION['adress']))
    {
        // $arrBas = unserialize(base64_decode($_SESSION['arrBas']));
        $mess = $basket['orderid']." -- ".$_SESSION['client']." -- ".$_SESSION['email']." -- ".$_SESSION['tel']." -- ".$_SESSION['adress']."\n";
        if (!file_put_contents(ORDERS_LOG, $mess, FILE_APPEND))
            echo "Ошибка записи в файл";
        else
            echo "<h3>Данные заказа успешно переданны</h3>";
        if (!addToOrders()) 
            {   echo "<h3>Какая-то ошибка соединения с БД</h3>"; exit;  }
            else
            {
                echo "<h3>Ваш заказ <a href='orders.php?id=".$basket['orderid']."'>".$basket['orderid']."</a> оформлен успешно</h3>";
                echo "<p>Вы можете продолжить покупки <a href='show2cat.php'>в нашем каталоге.</a><p>";
                setcookie ('basket', "", time() - 3600);
                basketInit();
                unset($_SESSION['client'], $_SESSION['tel'], $_SESSION['email'], $_SESSION['adress'], $_SESSION['arrBas']);
                exit;
            }
    }
    elseif(!isset($_SESSION['arrBas']))
        { echo "<h4>Вы не можете оформить заказ, потому что Ваша корзина пуста!<br>Пожалуйста выберите товар<a href='show2cat.php'> из нашего каталога</a></h4>"; exit; }
    //else {  echo "Какая-то ошибка с корзиной"; exit;    }
    
    
    /*
    if(!is_array($basket)) basketInit();
    if(is_array($basket) and count($basket)>1)
    {
        if (isset($_SESSION['client']) && isset($_SESSION['email']) && isset($_SESSION['tel']) && isset($_SESSION['adress']))
        {
            $mess = $basket['orderid']." -- ".$_SESSION['client']." -- ".$_SESSION['email']." -- ".$_SESSION['tel']." -- ".$_SESSION['adress']."\n";
            if (!file_put_contents(ORDERS_LOG, $mess, FILE_APPEND))
                echo "Ошибка записи в файл";
            else
                echo "<h3>Данные заказа успешно переданны</h3>";
            
            if (!addToOrders()) 
            {   echo "<h3>Какая-то ошибка соединения с БД</h3>"; exit;  }
            else
            {
                echo "<h3>Ваш заказ оформлен успешно</h3>";
                setcookie ('basket', "", time() - 3600);
                basketInit();
                unset($_SESSION['client'], $_SESSION['tel'], $_SESSION['email'], $_SESSION['adress']);
                exit;
            }
        }
    }
    else echo "Какая-то ошибка с корзиной";
    */
    
}
?>