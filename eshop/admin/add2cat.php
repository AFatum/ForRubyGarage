<?php 
    require("../secure/session.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
    if($_GET['change'])
    {  
        $good = "<p>Выберите товар, которых нужно изменить:   <select name='change' form='item'>";
        if (!$goods = selectItemToCatalog()) {   echo "ошибка получения данных из БД"; exit;   }
        foreach($goods as $item)
        { $good .= "<option value='".$item['id']."'>Изменить: ".$item['title']."</option>"; }
       // $good .= "<option selected value='new'>Добавить новый товар</option>";
        $good .= "</select></p><p>Название: <input type='text' name='title' size='100'></p>";
        $bt = "Изменить";
        $link = "<p><a href='add2cat.php'>Добавить новый товар</a></p>";
    }
    else
    {
        $good = "<p>Название: <input type='text' name='title' size='100'></p>";
        $bt = "Добавить";
        $link = "<p><a href='add2cat.php?change=true'>Изменить товар</a></p>";
    }
?>
<html>
	<head>
		<title>Форма добавления товара в каталог</title>
	</head>
	<body>
	    <h2>Добавление/изменение товаров в каталоге</h2>
        <?= $link ?>
		<form action = "save2cat.php" method="post" id="item" enctype="multipart/form-data">
			<?= $good ?>
			<p>Автор: <input type="text" name="author" size="50"></p>
			<p>Год издания: <input type="text" name="pubyear" size="4"></p>
			<p>Цена: <input type="text" name="price" size="6"> грн</p>
			<p>Описание товара<br>
            <p>Загрузить изображение товара:&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="uploadfile"></p>
			    <textarea name="opis" cols="50" rows="5" placeholder="Введите описание товара"></textarea>
			</p>	
			<p><input type="submit" value="<?= $bt ?>"></p>
		</form>
	<p><a href="show2cat.php">Показать материалы</a></p>
	<?php
        if(isset($_SESSION['resultAdd']))
        {
            switch($_SESSION['resultAdd'])
            {
                case "newAdd":
                    echo "<h3>Товары в каталог добавлены</h3>";
                    unset($_SESSION['resultAdd']);
                    break;
                case "update":
                    echo "<h3>Товары в каталоге успешно изменены</h3>";
                    unset($_SESSION['resultAdd']);
                    break;
                case "err":
                    echo "<h3>Заполните все поля при создании нового товара</h3>";
                    unset($_SESSION['resultAdd']);
                    break;
                case "err2":
                    echo "<h3>Извините, но вы не заполнили не одно поле для изменения товара!</h3>";
                    unset($_SESSION['resultAdd']);
                    break;
                default:
                    echo "<h3>Неизвестная ошибка</h3>";
                    unset($_SESSION['resultAdd']);
                    break;
            }
        }
	?>
	</body>
</html>