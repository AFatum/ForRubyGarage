<?php 
	require("../secure/session.inc.php");
	require("../secure/secure2.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
    //echo $_SERVER['REQUEST_URI']; exit;
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title>Показать материалы из каталога</title>
	</head>
	<body>
		<form action = "" method="post">
			<p>Сортировать по: 
				<select name="type">
					<option value="author">Автору</option>
					<option value="pubyear">Году издания</option>
					<option value="price">Цене</option>
					<option selected value="id">Порядку</option>
				</select>
			</p>
			<p>Показать (кол):  
				<select name="count">
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option selected value="0">Всё</option>
				</select>
			</p>
			<p>Отсортировать:  
				<select name="desc">
					<option selected value=" DESC">По убыванию (в обратном порядке)</option>
					<option value="null">По возрастанию (в прямом порядке)</option>
				</select>
			</p>
			<p><input type="submit" value="Показать"></p>
		</form>
		<ul class="cat">
            <li><a href="add2cat.php">Добавить материал</a></li>
            <li><a href="add2cat.php?change=true">Изменить материал</a></li>
            <li><a href="orders.php">Посмотреть заказы</a></li>
            <li>Товаров в <a href="basket.php">Корзине</a>: <?= $count; ?> </li>
        </ul>
	</body>
</html>
<?php

	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{ // все данные полученные методом POST, перелаживаются в сессионные переменные
        $_SESSION['catType'] = $_POST['type'];
        $_SESSION['catDesc'] = $_POST['desc'];
		$_SESSION['countGoods'] = abs((int)$_POST['count']); // количество отображаемых товаров
        // Здесь нижняя строка, это старая версия принятия данных...
		//$_SESSION['result'] = selectItemToCatalog($_POST['type'], $_SESSION['countGoods'], $_POST['desc']);
        // Далее мы заносим данные в основную переменную $_SESSION['result'], она нам еще понадобится...
        $_SESSION['result'] = selectItemToCatalog($_SESSION['catType'], $_SESSION['countGoods'], $_SESSION['catDesc']);
		header("Location: show2cat.php");
		exit;
	}
	elseif (isset($_SESSION['countGoods']) && isset($_SESSION['result']))
	//else  -  здесь мы проверяем были ли получены все данные
	{
		$countGoods = $_SESSION['countGoods']; // количество отображаемых товаров
		$result = $_SESSION['result'];
        //unset($_SESSION['countGoods'], $_SESSION['result']);
		if ($result === false) // если произошла какая-то ошибка при добавлении товара
		{
			echo "<h3>Произошла ошибка при выводе товаров!</h3>";
			exit;
		}	
		if ($_POST && !$result) // если товар пока не добавлен и в БД нет ниодного товара
		{
			echo "<h3>Товаров ещё нет!</h3>";
			exit;
		}
		//header("Location: show2cat.php");
		echo <<<HTML
			<table border="1" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<th>ID</th>
					<th>Название</th>
					<th>Автор</th>
					<th>Год издания</th>
					<th>Цена</th>
					<th>Оценить</th>
					<th>Рейтинг</th>
					<th>В корзину</th>
				</tr>
HTML;
            $uri = $_SERVER['REQUEST_URI']; // берем значение страницы
			foreach($result as $array)
			{    ?>
				<tr>
                    <td><?= $array['id'] ?></td>
                    <td><a href="book.php?book=<?= $array['id'] ?>"><?= $array['title'] ?></a></td>
					<td><?= $array['author'] ?></td>
					<td><?= $array['pubyear'] ?></td>
					<td><?= $array['price'] ?></td>
					<td> <?// рейтинговая оценка, передаем $uri для возврата на эту же страницу, а также остальные данные для обработки ?>
					    <a href="ocenka.php?val=1&link=<?=$uri?>&id=<?=$array['id']?>&count=<?=$array['count']?>&sum=<?=$array['summa']?>">1</a>, 
					    <a href="ocenka.php?val=2&link=<?=$uri?>&id=<?=$array['id']?>&count=<?=$array['count']?>&sum=<?=$array['summa']?>">2</a>, 
					    <a href="ocenka.php?val=3&link=<?=$uri?>&id=<?=$array['id']?>&count=<?=$array['count']?>&sum=<?=$array['summa']?>">3</a>, 
					    <a href="ocenka.php?val=4&link=<?=$uri?>&id=<?=$array['id']?>&count=<?=$array['count']?>&sum=<?=$array['summa']?>">4</a>, 
					    <a href="ocenka.php?val=5&link=<?=$uri?>&id=<?=$array['id']?>&count=<?=$array['count']?>&sum=<?=$array['summa']?>">5</a>					    
					</td>
					<td><?= $array['ocenka'] // получаем оценку рейтинга товара?></td>
                    <?php // отображаем кнопку "в корзину", если товар уже добавлен в неё
                        $in = false;
                        if(is_array($basket))
                        {
                            foreach ($basket as $key => $goods)
                            {
                                if($key == $array['id'])
                                {
                                    $in = true;
                                    break; 
                                }
                            }
                        }
                        if($in)
					       echo "<td>Уже в корзине! <a href='delete2basket.php?id=".$array['id']."&show=cat'>Удалить</a></td></tr>";
                        else
					       echo "<td><a href='add2basket.php?id=".$array['id']."'>В корзину</a></td> </tr>";
			}
	}
	echo "</table>";
    //unset($_SESSION['countGoods'], $_SESSION['result']);
//else header("Location: " . $_SERVER['PHP_SELF']);
?>