<?php
ob_start();		// включаем буферизацию вывода
session_start(); // стартуем сессию
require("oper.class.php");
header ('Content-Type: text/html;charset=utf-8');


//if ($_SERVER['SERVER_NAME'] == "stormy-river-47352.herokuapp.com") 
if ($_SERVER['SERVER_NAME'] == "for-rubygarage-tony.herokuapp.com")
{
	$urlGlob = parse_url(getenv("CLEARDB_DATABASE_URL"));
	define ('DB_HOST',  $urlGlob["host"]);
	define ('DB_LOGIN', $urlGlob["user"]);
	define ('DB_PASS', $urlGlob["pass"]);
	define ('DB_NAME', substr($urlGlob["path"], 1));
	define ('LINK_HOST', 'for-rubygarage-tony.herokuapp.com');
	$link = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASS, DB_NAME) or die('Ошибка при соединении: '.mysqli_connect_error());
}
else
{
	define ('DB_HOST', 'localhost');
	define ('DB_LOGIN', 'root');
	define ('DB_PASS', '');
	define ('DB_NAME', 'ruby');
	define ('LINK_HOST', 'http://garage.loc');
	$link = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASS, DB_NAME) or die('Ошибка при соединении: '.mysqli_connect_error());
}
    $oper = new Oper(new mysqli(DB_HOST, DB_LOGIN, DB_PASS, DB_NAME));
?>