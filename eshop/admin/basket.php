<?php
// подключение библиотек
    require("../secure/session.inc.php");
    require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>Корзина</title>
	</head>
	<body>

<?php
	$arrayBas = select4Basket(); // получаем данных из БД
	if ($arrayBas and !is_array($arrayBas))
	{	echo $arrayBas; exit;	}
	if (!$arrayBas)
	{
		if (count($basket) <= 1 || !$basket) 
		{
			echo "<h3>В корзине пока нет товаров</h3>";
			echo "<p>Вы можете заказать товары в <a href='show2cat.php'>каталоге</a></p>";
			exit;
		}
		echo "Ошибка получения данных";
		exit;
	}
	echo "<h2>У Вас в корзине:</h2>";
	echo <<<TH
	<table border="1" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<th>№</th>
			<th>Название</th>
			<th>Автор</th>
			<th>Год издания</th>
			<th>Цена</th>
			<th>Количество</th>
			<th>Пересчет колличества</th>
			<th>Рейтинг</th>
			<th>Удалить</th>
		</tr>
TH;
	$summa = 0; $i = 1;
	foreach($arrayBas as $tovar) 
    {
        // заносим нужное количество товаров
        if($_POST)
        {
            // берем первый символ, который содержит цифру в штуках
            foreach($_POST['col'] as $col)
            {
                $j = $col;
                $jo = (int) $j[0]; 
                // и затем обрезаем первые два символа для получения ид товара, в котором нужно изменить количество
                $jb = substr($j, 2); 
                if($tovar['id'] == $jb)
                    $basket[$tovar['id']] = $jo;
            }
        }            

	  ?>

		<tr>
			<td><?= $i ?></td>
            <td><a href="book.php?book=<?= $tovar['id'] ?>"><?= $tovar['title'] ?></a></td>
			<td><?= $tovar['author'] ?></td>
			<td><?= $tovar['pubyear']?></td>
			<td><?= $tovar['price']?></td>
			<td><?= $basket[$tovar['id']] ?></td>
			<td>
                   <? /* здесь указывается значение относительно ид товара, 
                        для того, чтоб пересчитывался именно нужный товар! */ ?>
                    <select name='col[]' form='col'>
                        <option value="1-<?=$tovar['id']?>">1</option>
                        <option value="2-<?=$tovar['id']?>">2</option>
                        <option value="3-<?=$tovar['id']?>">3</option>
                        <option value="4-<?=$tovar['id']?>">4</option>
                        <option value="5-<?=$tovar['id']?>">5</option>
                    </select>
            </td>
			<td>Рейтинг</td>
			<td><a href="delete2basket.php?id=<?=$tovar['id']?>">Удалить</a></td>
		</tr>
        <?php
                        
            // col
                
	   $summa += $tovar['price'] * $basket[$tovar['id']]; $i++;
	}
	echo "</table>";
	echo "<p><strong>Сумма Вашего заказа составляет: ".$summa." гривен</strong></p>"; ?>
                <form action='' method='post' id='col'>
                    <input type="submit" value="Пересчитать">
                </form>
                <?php
	echo "<p>Вы ещё можете заказать товаров в нашем <a href='show2cat.php'>каталоге</a></p>";
	echo "<p>Если Вы уже всё выбрали, то можете <a href='basket.php?order=1'>перейти к оформлению заказа</a></p>";
	//mysqli_close($link);
    if($_GET)
    {
        $or = (int) abs($_GET['order']);
        if($or == 1)
        {
            $_SESSION['arrBas'] = base64_encode(serialize($arrayBas));
            header("Location: NewOrder.php");
            exit;
        }
        else {  header("Location: basket.php"); exit;   }
    }   
          
?>
</body></html>