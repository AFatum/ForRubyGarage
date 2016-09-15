<?php
const SEC_FILE = ".tumbaumba";
function getHash ($pass)
{
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    return trim($hash);
}

function checkHash ($pass, $hash) 
{
    return password_verify(trim($pass), trim($hash));
}

function saveUser($login, $hash)
{
    $str = $login.":".$hash."\n";
    if(file_put_contents(SEC_FILE, $str, FILE_APPEND)) return true;
    else return false;
}

function userExists($login)
{
    if(!is_file(SEC_FILE)) return false;
    $users = file(SEC_FILE);
    foreach($users as $user)
    {
        if(strpos($user, $login.':') !== false) return $user;
        // здесь мы с помощью ф-ции strpos() проверяем есть ли в строке $user..
        //.. подстрока $login.':', если есть, то возвращаем всю строку вместе с хэшем пароля!
    }
    return false; // если не удалось найти пользователя
}
function logOut()
{
    session_destroy();
    //header("Location: http://".$_SERVER['HTTP_HOST'].'/eshop/secure/login.php');
    //exit;
}

?>