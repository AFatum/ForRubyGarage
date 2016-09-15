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
            $i = 0;
            while(!feof($handle))
                $contact[] = explode(" -- ", fgets($handle));         
            fclose($handle);
        
            $i = (string) "";
            unset($contact[(count($contact)-1)]);
            // вставляем список ссылок
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
            echo "</ul>";

            if (!$orders = selectAllOrders()) // получаем данные из БД
            {   echo "Ошибка полученич данных из БД".mysqli_error($link); exit;     }
            
            $i = 0; // обнуляем якорь
            foreach($contact as $arr)
            {
                if ($i == $arr[0]) continue;
                else
                {
                    foreach ($orders as $goods)
                    {   
                        if ($arr[0] != $goods['orderid']) continue;
                        else
                        {   
                            $dt = date("d.m.Y", $goods['datetime']); 
                            break;     
                        }
                    }
                     echo <<<ORDER
                    <div class="order">
                        <h2>Заказ номер: {$arr[0]}</h2>
                        <h4>Заказчик: {$arr[1]}</h4>
                        <h4>Телефон: {$arr[3]}</h4>
                        <h4>email: {$arr[2]}</h4>
                        <h4>Адресс доставки: {$arr[4]}</h4>
                        <h4>Дата размещения заказа: {$dt}</h4>
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
                    $j = 1; $sum = 0;
                    foreach ($orders as $goods)
                     {
                        if ($arr[0] != $goods['orderid']) continue;
                        else
                        {
                            echo "<tr>
                                <td>".$j."</td>
                                <td>".$goods['title']."</td>
                                <td>".$goods['author']."</td>
                                <td>".$goods['pubyear']."</td>
                                <td>".$goods['price']."</td>
                                <td>".$goods['quantity']."</td>
                            </tr>";
                            $j++; $sum += $goods['price'];                        
                        }
                     }
                    echo "</table><p>Всего товаров в заказе на сумму: <strong>".$sum." грн.</strong></p></div>
                    <p>Вы можете продолжить покупки в <a href='show2cat.php'>нашем каталоге</a></p>";

                    $i = $arr[0];
                }
            }

            /*echo "<pre>";
            print_r($contact);
            echo "</pre>";*/
            

?>               
	</body>
</html>