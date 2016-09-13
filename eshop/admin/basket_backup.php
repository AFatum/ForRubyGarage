<?php
// подключение библиотек
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
?>
<html>
	<head>
		<title>Корзина</title>
	</head>
	<body>

<?php
	//$basket = 0;
	if (count($basket) <= 1 || !$basket) 
	{
		echo "<h3>В корзине пока нет товаров</h3>";
		echo "<p>Вы можете заказать товары в <a href='show2cat.php'>каталоге</a></p>";
		exit;
	}
	else
	{
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
				<th>Удалить</th>
			</tr>
TH;
		$summa = 0; $i = 1;
		foreach($basket as $key => &$kol)
		{
			if(!is_int($key)) continue;
			$array = selectItemIdCatalog($key);
			echo <<<TR
			<tr>
				<td>{$i}</td>
				<td>{$array[0]['title']}</td>
				<td>{$array[0]['author']}</td>
				<td>{$array[0]['pubyear']}</td>
				<td>{$array[0]['price']}</td>
				<td>{$kol}</td>
				<td><a href="delete2basket.php?id={$array[0]['id']}">Удалить</a></td>
			</tr>
TR;
		$summa += $array[0]['price'] * $kol; $i++;
		}
		echo "</table>";
		echo "<p><strong>Сумма Вашего заказа составляет: ".$summa." гривен</strong></p>";
		echo "<p>Вы ещё можете заказать товаров в нашем <a href='show2cat.php'>каталоге</a></p>";
		mysqli_close($link);
	}
?>
</body></html>