<?php
ob_start();		// включаем буферизацию вывода
//session_start(); // стартуем сессию
header ('Content-Type: text/html;charset=utf-8');
define ('DB_HOST', 'localhost');
define ('DB_LOGIN', 'root');
define ('DB_PASS', '');
define ('DB_NAME', 'eshop');
define ('ORDERS_LOG', 'orders.txt');
// Корзина покупателя
$basket = array();
// Количество товаров в корзине покупателя
$count = 0;

$link = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASS, DB_NAME) or die('Ошибка при соединении: '.mysqli_connect_error());
basketInit2();
?>