<?php
// подключение библиотек
    require("../secure/session.inc.php");
	require("../inc/lib.inc.php");
	require("../inc/db.inc.php");
    
    if(!$_GET['book'])                              // если не получен основной параметр ID товара..
    {  header("Location: show2cat.php"); exit;  }   //.. то возвращаем на каталог товароа
    else
    {
        $id = (int) abs($_GET['book']);             // запоминаем ID товара
        $uri = $_SERVER['REQUEST_URI'];             // запоминаем страницу от куда пришли
    }

    if(isset($_SESSION['result']))                  // если есть основная сессионная переменная $_SESSION['result'],.
    {                                               //.. то получаем данные из неё.
        foreach($_SESSION['result'] as $arr)        // получаем данные о товаре из массива $_SESSION['result']
        {
            if($arr['id'] == $id)                   // берем нужные товар, сравниваем соответствие по ID параметрам..
            {                                       //.. и вносим в каждую переменную соответствующее значение 
                $btitle = $arr['title']; 
                $author = $arr['author']; 
                $price = $arr['price']; 
                $pyear = $arr['pubyear'];
                $img = $arr['img'];
                $txt = $arr['opis'];
                $sum = $arr['summa'];
                $cnt = $arr['count'];
                $oc = $arr['ocenka'];
                break;
            }
        }
    }
    //$id = (int) abs($_GET['book']);
    else                                            // если основной сессионной переменной $_SESSION['result'] нет,.
    {                                               //.. то заново получаем основные данные о товаре из БД
        if (!$book = selectItemIdCatalog($id))      // Получаем данные о товаре из БД
            { echo "<h3>Произошла какая-то ошибка в получении данных из БД</h3>"; exit; }
        else
        {
            if($book[0]['id'] == $id)               // проверяем нужный ли товар выбрат, также по соответствию параметра ID..
            {                                       //.. и заносим данные в соответствующие переменные
                $btitle = $book[0]['title']; 
                $author = $book[0]['author']; 
                $price = $book[0]['price']; 
                $pyear = $book[0]['pubyear'];
                $img = $book[0]['img'];
                $txt = $book[0]['opis'];
                $sum = $book[0]['summa'];
                $cnt = $book[0]['count'];
                $oc = $book[0]['ocenka'];
            }
        }
    }
    /*echo "<pre>";
    print_r($book);
    echo "</pre>";
    exit;*/
?>
<html>
	<head>
	    <link href="../inc/stl.css" rel="stylesheet">
		<title><?= $btitle; ?></title>
	</head>
	<body>
       <div class='book'>
           <img src="<?= $img; ?>" alt="Изображение товара" />
           <!-- <h2>Хорошая книга</h2>
           <h3>Автор молодец</h3>
           <h4>1900 год издания</h4>
           <h3>300 рублей</h3> -->
           <ul>
               <li class='btitle'><?= $btitle; ?></li>
               <li class='author'><?= $author; ?></li>
               <li class='price'>Цена: <?= $price; ?> гривен</li>
               <li class='year'>Год издания: <?= $pyear; ?></li>
               <li>
                   <?php 
                        $in = false;
                        if(is_array($basket))
                        {
                            foreach ($basket as $key => $goods)
                            {
                                if($key == $id)
                                {
                                    $in = true;
                                    break; 
                                }
                            }
                        }
                        if($in)
					       echo "<strong>Уже в корзине!</strong> <a href='delete2basket.php?id=".$id."&show=book'>Удалить</a>";
                        else
					       echo "<a href='add2basket.php?id=".$id."&show=true'>В корзину</a>";
                   ?>
               </li>
               <li>Оцените товар, если он Вам понравился: <br>
                        <a href="ocenka.php?val=1&link=<?=$uri?>&id=<?=$id?>&count=<?=$cnt?>&sum=<?=$sum?>">1</a>, 
                        <a href="ocenka.php?val=2&link=<?=$uri?>&id=<?=$id?>&count=<?=$cnt?>&sum=<?=$sum?>">2</a>, 
                        <a href="ocenka.php?val=3&link=<?=$uri?>&id=<?=$id?>&count=<?=$cnt?>&sum=<?=$sum?>">3</a>, 
                        <a href="ocenka.php?val=4&link=<?=$uri?>&id=<?=$id?>&count=<?=$cnt?>&sum=<?=$sum?>">4</a>, 
                        <a href="ocenka.php?val=5&link=<?=$uri?>&id=<?=$id?>&count=<?=$cnt?>&sum=<?=$sum?>">5</a>
               </li>
               <li class="price">Рейтинг товара: <?=$oc?></li>
               <li><a href="show2cat.php">Назад к списку товаров</a></li>
               <li><a href="NewOrder.php">Перейти к оформлению заказа</a></li>
               <li><a href="basket.php">Показать, что в корзине</a></li>
               <li><a href="ocenka.php?val=false&link=<?=$uri?>&id=<?=$id?>">Очистить рейтинг</a></li>
               <li><a href="book.php?book=<?=$id?>&com=true">Комментировать</a></li>
           </ul>
           <p><?= $txt; ?></p>
        </div>
        <?php  

        if($_GET['err'])
        {   // принимаем GET параметр для отображения нужного сообщения
            $_SESSION['comment'] = (int) abs($_GET['err']);
            header("Location: book.php?book=".$id);
            exit;
        }
        if($_SESSION['comment'] == 1)
        {
            echo "<h4>Ваш комментарий добавлен</h4>";
            //$_SESSION['comment'] = false;    
            unset($_SESSION['comment']);
        }
        if($_SESSION['comment'] == 2)
        {
            echo "<h4>Пожалуйста заполните все данные</h4>";
            //$_SESSION['comment'] = false;    
            unset($_SESSION['comment']);
        }
        
        if($_GET['com'])   

        {                     // отображаем форму комментариев, если получен GET параметр?>
           <form action="mess.php" method="post">
                <p><input type="text" name="name" placeholder = "Ваше имя..." ></p>
                <input type="hidden" name="title" value="<?=$btitle?>" >
                <input type="hidden" name="uri" value="<?=$uri?>" >
                <p><textarea cols="50" rows="7" name="mess" placeholder="Ваш комментарий..."></textarea></p>
                <p><input type="submit" value="Комментировать"></p>
           </form>          
        <?php } 
        if(!$comment = selectComment($btitle))
        {    
            $error = "You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '&MySQL ORDER BY datetime DESC LIMIT 5' at line 3";
             if(mysqli_error($link) == $error)
             {  echo "Пока комментариев нет"; exit; }
            echo "Произошла какая-то ошибка в получении комментариев"; exit;
        }

        if(is_array($comment))
        {
            foreach($comment as $arr)
            {
                if($arr['title'] == $btitle)
                {
                    ?>
                    <div>
                        <h4><?=$arr['name']?> -- <?=$arr['dt']?></h4>
                        <p><?=$arr['mess']?></p>
                    </div>
                    <?php
                }
            }
        }
        ?>
    </body>
</html>