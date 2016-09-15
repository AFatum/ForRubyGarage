<?php
//require("../secure/secure.inc.php");
//-------- Добавление товаров в каталог-----------------------------
function addItemToCatalog($title, $author, $pubyear, $price)
{
	global $link;
	$sql = "INSERT INTO catalog(title, author, pubyear, price) VALUES (?, ?, ?, ?)";
	if(!$stmtIns = mysqli_prepare($link, $sql))
		return false;
	mysqli_stmt_bind_param($stmtIns, 'ssii', $title, $author, $pubyear, $price);
	mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));
	mysqli_stmt_close($stmtIns);
	return true;
}
//-------- Изменение товаров в каталог-----------------------------
function updateItemToCatalog($id, $par = array())
{
	global $link;
    if(!is_array($par)) { echo "В ф-цию updateItemToCatalog передан не цикл!"; exit; }
	$sql =" UPDATE catalog
            SET ";
    foreach($par as $key => $value)
    {
        if(is_int($value)) $sql .= $key." = ".$value.", ";
        else $sql .= $key." = '".$value."', ";
    }
    $sql = substr($sql, 0, -2);
    $sql .= " WHERE ID LIKE ".$id;
    
    if(!$result = mysqli_query($link, $sql)) return false;
    mysqli_free_result($result);
	return true;
}
//-------- Добавление товаров в каталог-----------------------------
function addToOrders()
{
	global $link, $basket;
    if(!isset($_COOKIE['basket'])) return false;
    if(!isset($_SESSION['arrBas'])) return false;
    if(!is_array($basket)) basketInit();
    
    //$arrBas = select4Basket();
    $arrBas = unserialize(base64_decode($_SESSION['arrBas']));
    foreach($arrBas as $order)
    {
	$sql = "INSERT INTO orders(title, author, pubyear, price, quantity, orderid, datetime) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;
    
    $Order = $basket['orderid'];
    $title = $order['title'];
    $author = $order['author'];
    $pubyear = $order['pubyear'];
    $price = $order['price'];
    $q = end($basket);
    $t = time();
        
	mysqli_stmt_bind_param($stmtIns, 'ssiiisi', $title, $author, $pubyear, $price, $q, $Order, $t);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    }
    mysqli_stmt_close($stmtIns);
	return true;
}
//-------- Добавление новых пользователей-----------------------------
function addToUsers()
{
	global $link;
    if(!isset($_COOKIE['basket'])) return false;
    if(!isset($_SESSION['arrBas'])) return false;
    if(!is_array($basket)) basketInit();
    
    //$arrBas = select4Basket();
    $arrBas = unserialize(base64_decode($_SESSION['arrBas']));
    foreach($arrBas as $order)
    {
	$sql = "INSERT INTO orders(title, author, pubyear, price, quantity, orderid, datetime) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;
    
    $Order = $basket['orderid'];
    $title = $order['title'];
    $author = $order['author'];
    $pubyear = $order['pubyear'];
    $price = $order['price'];
    $q = end($basket);
    $t = time();
        
	mysqli_stmt_bind_param($stmtIns, 'ssiiisi', $title, $author, $pubyear, $price, $q, $Order, $t);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    }
    mysqli_stmt_close($stmtIns);
	return true;
}
//-------- Выбор товаров из каталога----------------------------
function selectItemToCatalog($type="id", $count=0, $desc=" DESC")
{
	global $link;
	if($count > 0) $Count = " LIMIT ".$count;
	if ($desc == " DESC") $Desc = $desc;
	$sql = "SELECT id, title, author, pubyear, price, opis, img, count, summa, ocenka
			FROM catalog
			ORDER BY ".$type.$Desc.$Count;
	if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//-------- Выбор товаров из ордеров заказа по ID---------
function selectFromOrders($id=0)
{
	global $link;
    if($id == 0 || !is_string($id)) return false;
    
    $sql = "SELECT id, title, author, pubyear, price, quantity, orderid, datetime
			FROM orders
            WHERE orderid LIKE '".$id."'
			ORDER BY ID";

    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//-------- Выбор товаров из ордеров заказа ВСЕХ----------
function selectAllOrders()
{
	global $link;
    
    $sql = "SELECT id, title, author, pubyear, price, quantity, orderid, datetime
			FROM orders
			ORDER BY ID";

    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//-------- Выбор товаров из каталога для корзины----------------------------
function selectItemIdCatalog($id = 1)
{
	global $link;

	$sql = "SELECT id, title, author, pubyear, price, opis, img, count, summa, ocenka
			FROM catalog
			WHERE id LIKE ".$id.
			" ORDER BY ID";
	if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//-------- Выбор товаров из каталога для корзины--!NEW!----------------------//
function select4Basket()
{
	global $link, $basket, $count;

	if (!isset($_COOKIE['basket'])) return false;
	if(!is_array($basket))
	{
		$basket = unserialize(base64_decode($_COOKIE['basket']));
		// ф-ция base64_decode() аналогично base64_encode(), десериализирует значение
		$count = count($basket) - 1; // отнимает значение orderid, т.к. это не товар!
	}
	if (count($basket) <= 1 || !$basket) return false;
    
	$sql = "SELECT id, title, author, pubyear, price, opis, img
			FROM catalog
			WHERE id IN
			(";
	foreach($basket as $key => &$kol)
	{
		if(!is_int($key)) continue;
		$sql = $sql.$key.", ";
	}
	$sql = substr($sql, 0, -2); // обрезаем два последних символа..
								//. здесь это последние ", "
	$sql = $sql.") ORDER BY ID";
	if(!$result = mysqli_query($link, $sql))
		return "Произошла ошибка БД: ".mysqli_error($link);
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//--------Подготовка строк к внесению в БД----------------------------
function clear($data)
{
	global $link;
    $Data = (string) $data;   
	return mysqli_real_escape_string ($link, trim(strip_tags($Data)));
}
//--------Сохранение корзины----------------------------
function saveBasket()
{
	global $basket;
	$basket = base64_encode(serialize($basket));
	// base64_encode() - ф-ция, сериализует данные по стандарту, только буквами и цифрами
	// необходимо использовать для старых версий php
	setcookie('basket', $basket, 0x7FFFFFFF);
}
function saveBasket2()
{
	global $basket, $link;
	$basket = clear(base64_encode(serialize($basket)));
	// base64_encode() - ф-ция, сериализует данные по стандарту, только буквами и цифрами
	// необходимо использовать для старых версий php
    $sql = "UPDATE users
		      SET
                bascket = ".$basket.
              "WHERE id LIKE ".$_SESSION['control'];
	setcookie('basket', $basket, 0x7FFFFFFF);
}
//--------Создание корзины----------------------------
function basketInit()
{
	global $basket, $count;
	if (!isset($_COOKIE['basket'])) // если в корзину ничего нет, нет куки
	{
            $basket = array('orderid' => uniqid());
            // ф-ция uniqid(), возвращает уникальное значение id
            saveBasket(); // сериализируем данные
	}
	else // если кука есть, то нужно рассериализировать
	{
		$basket = unserialize(base64_decode($_COOKIE['basket']));
		// ф-ция base64_decode() аналогично base64_encode(), десериализирует значение
		$count = count($basket) - 1; // отнимает значение orderid, т.к. это не товар!
	}
}
//--------Создание корзины----------------------------
function basketInit2()
{
	global $basket, $count;
	if (!isset($_COOKIE['basket'])) // если в корзину ничего нет, нет куки
	{
        if(isset($_SESSION['control']))
        {
            $basket = unserialize(base64_decode(selectBasNameUsers($_SESSION['control'])));
            saveBasket(); // сериализируем данные
        }
		else 
        {
            $basket = array('orderid' => uniqid());
            // ф-ция uniqid(), возвращает уникальное значение id
            saveBasket(); // сериализируем данные
        }
	}
	else // если кука есть, то нужно рассериализировать
	{
        if(isset($_SESSION['control']))
        {
            $basket = unserialize(base64_decode(selectBasNameUsers($_SESSION['control'])));
            $count = count($basket) - 1; // отнимает значение orderid, т.к. это не товар!
        }
        else
        {
            $basket = unserialize(base64_decode($_COOKIE['basket']));
            // ф-ция base64_decode() аналогично base64_encode(), десериализирует значение
            $count = count($basket) - 1; // отнимает значение orderid, т.к. это не товар!
        }
	}
}
//--------Добавление в корзину----------------------------
function add2Basket($id, $q=1)
{
	global $basket;
	$basket[$id] = $q;
	saveBasket();
}
//--------Удаление из корзины----------------------------
function DeleteFromBasket($id)
{
	global $basket;
	unset($basket[$id]); // удаляем товар из корзины
	saveBasket();
}
function rus2translit($string) 
{
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}
//--------Внесение изменения рейтинга----------------------------
function updateOcenka($id, $oc, $sum, $count)
{
    global $link;
    
	 $sql = "UPDATE catalog
                SET
                    count = ".$count.",
                    summa = ".$sum.",
                    ocenka = ".$oc.
                " WHERE id LIKE ".$id;
    
    if(!$result = mysqli_query($link, $sql)) return false;
    mysqli_free_result($result);
	return true;
}
//--------Выбор оценки рейтинга по ID товара----------------------------
function selectOcenkaID($id)
{
    global $link;

	$sql = "SELECT id, count, summa, ocenka
			FROM catalog
			WHERE id LIKE ".$id.
			" ORDER BY ID";
    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//--------Очистка рейтинга по ID товара----------------------------
function clearOcenka($id)
{
    global $link;

    $v = null;
	$sql = "UPDATE catalog
                SET 
                    summa = 0,
                    count = 0,
                    ocenka = 0 
                 WHERE id LIKE ".$id;
    if(!$result = mysqli_query($link, $sql)) return false;
    mysqli_free_result($result);
	return true;
}
//-------- Выбор комментарием для отображения ----------------------------
function selectComment($title)
{ 
	global $link;
    $title = clear($title);

	$sql = "SELECT id, title, name, mess, UNIX_TIMESTAMP(datetime) as dt 
			FROM msg
			WHERE title LIKE '".$title.
			"' ORDER BY datetime DESC LIMIT 5";
	if(!$result = mysqli_query($link, $sql))
    //{ echo "Произошла ошибка отображения комментариев из БД - ".mysqli_error($link); exit; }
        return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
//-------- Добавление комментария в БД ----------------------------
function addComment($title, $name, $mess)
{
	global $link;
    //if(!$title or !$name or !$mess) return true;
    $title = clear($title);
    $name = clear($name);
    $mess = clear($mess);
    
	$sql = "INSERT INTO msg(title, name, mess) VALUES (?, ?, ?)";
	if(!$stmtIns = mysqli_prepare($link, $sql))
		return false;
	mysqli_stmt_bind_param($stmtIns, 'sss', $title, $name, $mess);
	mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));
	mysqli_stmt_close($stmtIns);
	return true;
}
?>