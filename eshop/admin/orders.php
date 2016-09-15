<?php
// подключение библиотек
    require("../secure/session.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>Список заказов магазина</title>
	</head>
	<body>
        <h1>Поступившие заказы</h1>
        <?php
            $contact = array();
            $handle = fopen(ORDERS_LOG, "r");
            while(!feof($handle))
                $contact[] = explode(" -- ", fgets($handle));
            
            $i = (string) "";
            unset($contact[(count($contact)-1)]);
            echo "<ul>";
            foreach($contact as $arr)
            {
                if($arr[0] != $i)
                {
                    echo "<li>Заказ ID: <a href='orders.php?id=".$arr[0]."'>".$arr[0]."</a></li>";
                    $i = $arr[0];
                }
                else continue;
            }
            echo "<li><b>Показать <a href='orders_all.php'>все заказы</a></d></li>";
            echo "</ul>";
            /*echo "<pre>";
            print_r($contact);
            echo "</pre>";*/
            fclose($handle);
            //if ($_SERVER["REQUEST_METHOD"] == "GET")
            if ($_GET)
            {
                $id = clear($_GET['id']);   // берем id
                $arrOr = selectFromOrders($id); // берем данные из бд
                if(!is_array($arrOr) || !$arrOr)    // если ошибка, выводим её
                {   echo "<h4>Какая-то ошибка получения данных - ".mysqli_error($link)."</h4>"; exit;  }
                // объявляем переменные клиента:
                $client = (string) "";
                $email = (string) "";
                $phone = (int) 0;
                $adress = (string) "";
                // вносим значения в переменные согласно выбранному $id
                foreach($contact as $arr)
                {
                    if($id == $arr[0])
                    {
                        $client = $arr[1];
                        $email = $arr[2];
                        $phone = $arr[3];
                        $adress = $arr[4];
                        break;
                    }
                    else continue;
                }
                $date = date("d.m.Y", $arrOr[0]['datetime']);
                echo <<<ORDER
                    <div class="order">
                        <h2>Заказ номер: {$id}</h2>
                        <h4>Заказчик: {$client}</h4>
                        <h4>Телефон: {$phone}</h4>
                        <h4>email: {$email}</h4>
                        <h4>Адресс доставки: {$adress}</h4>
                        <h4>Дата размещения заказа: {$date}</h4>
                        <h3>Купленные товары:</h3>
                        <table border="1" cellpadding="5" cellspacing="0" width="100%">
                            <tr>
                                <th>№ п/п</th>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Год издания</th>
                                <th>Цена, грн</th>
                                <th>Количество</th>
                            </tr>
ORDER;
                $i = 1; $sum = 0;
                foreach($arrOr as $array)
                {
                    echo "<tr>
                            <td>".$i."</td>
                            <td>".$array['title']."</td>
                            <td>".$array['author']."</td>
                            <td>".$array['pubyear']."</td>
                            <td>".$array['price']."</td>
                            <td>".$array['quantity']."</td>
                        </tr>";
                    $i++; $sum += $array['price'];
                }
                echo "</table><p>Всего товаров в заказе на сумму: <strong>".$sum." грн.</strong></p></div>
                <p>Вы можете продолжить покупки в <a href='show2cat.php'>нашем каталоге</a></p>";
            }
            
        ?>
	</body>
</html>