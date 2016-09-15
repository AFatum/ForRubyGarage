<?php
const SEC_FILE = ".tumbaumba";

function addUsersBas()
{
    global $link, $basket;
    if(!isset($_SESSION['control'])) return false;
    if(is_array($basket)) $bas = clear(base64_encode(serialize($basket)));
    else $bas = clear($basket);
    
    $sql = "UPDATE users
		      SET
                bascket = '".$bas.
              "' WHERE id LIKE ".$_SESSION['control'];
    if(!$result = mysqli_query($link, $sql)) return false;
    mysqli_free_result($result);
	return true;
}

function selectBasNameUsers($id, $name = false)
{
    global $link;
    $sql = "SELECT name, bascket
            FROM users
            WHERE id LIKE ".$id;
    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if(!$name) return $items[0]['bascket'];
    else return $items[0]['name'];
}
    

function control ($login, $pass)
{
    global $link;
    $sql = $sql = "SELECT id, name, pass
            FROM users
            ORDER BY id;";
    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
    foreach($items as $data)
    {
        if($login === $data['name'])
        {
            if(password_verify(trim($pass), trim($data['pass'])))
            return $data['id'];
        }
        else continue;
    }
    return false;
}

function saveUser($login, $email, $hash)
{
    global $link;
	$sql = "INSERT INTO users(name, email, pass) VALUES (?, ?, ?)";
	if(!$stmtIns = mysqli_prepare($link, $sql))
		return false;
	mysqli_stmt_bind_param($stmtIns, 'sss', $login, $email, $hash);
	mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));
	mysqli_stmt_close($stmtIns);
	return true;
}

function userExists($login)
{
    global $link;
    $sql = "SELECT id, name
            FROM users
            ORDER BY id";
    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    foreach($items as $data)
    {
        if($data['name'] !== $login) return $login;
        else break;
    }
    return false;
}

function selectAllUsers()
{
    global $link;
    $sql = "SELECT id, name, email, pass, bascket
            FROM users
            ORDER BY id";
    if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}
function logOut()
{
    session_destroy();
    //header("Location: http://".$_SERVER['HTTP_HOST'].'/eshop/secure/login.php');
    //exit;
}

?>