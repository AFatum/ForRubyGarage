<?php
    require("db.php");
    require("lib.php");
    
    $linkUri = trim(strip_tags($_GET['link'])); // принимаем ссылку, на которую будем перенаправлять
    // 
// ------------------------создаём новый проект (список задач)------------------------
    if($_POST['oper'] == "newList")
    {
        $nameNL = (string) clear($_POST['list']);
        if(newList($nameNL)) {
            header("Location: ".LINK_HOST.$linkUri);
        }       
    }

// ------------------------создаём новую задачу----------------------------------
    if($_POST['oper'] == "newTask")
    {
        $task = clear($_POST['newTask']);
        $idLst = (int) abs($_POST['idList']);

        if (newTask($task, $idLst))
            header("Location: ".LINK_HOST.$linkUri);
        else echo "Какая-то ошибка".mysqli_error($link);
    }

// ------------------------переименование задачи----------------------------------
    if($_POST['oper'] == "renameTask")
    {
        $name = clear($_POST['newName']);
        if(empty($name))
        {
            header("Location: https://stormy-river-47352.herokuapp.com/index.php");
            exit;
        }
        $idTsk = (int) abs($_POST['idTsk']);
        $idPro = (int) abs($_POST['idPro']);
        if(uptTask($idTsk, $idPro, $name)) header("Location: ".LINK_HOST.$linkUri);
    }

// ------------------------изменение статуса задачи----------------------------------
    if($_GET['status'] == 1)
    {
        $status = (int) abs($_GET['sts']);
        $uptL = (int) abs($_GET['updl']);
        $uptT = (int) abs($_GET['updt']);   
        if(uptStatus($uptT, $uptL, $status))
            header("Location: ".LINK_HOST.$linkUri);
        else echo "Какая-то ошибка в запросе ".mysqli_error($link);
    }

// ------------------------удаление списка задачи----------------------------------
if(!empty($_GET['deleteList']))
    {
        $uptL = (int) abs($_GET['deleteList']);
        
        if(deleteTask("all", $uptL) and deleteList($uptL))
            header("Location: ".LINK_HOST.$linkUri);  
    }
// ------------------------удаление задачи----------------------------------
if(!empty($_GET['uptL']) and !empty($_GET['uptT']))
    {
        $uptL = (int) abs($_GET['uptL']);
        $uptT = (int) abs($_GET['uptT']);
        
        if (deleteTask($uptT, $uptL))
            header("Location: ".LINK_HOST.$linkUri);
    }

// ------------------------изменение порядка задач----------------------------------
    if(!empty($_GET['order']))
    {
        $uptL = (int) abs($_GET['updl']);
        $uptT = (int) abs($_GET['updt']);
        
        $task = selectTasks($uptL);
        $end = 0;
        $orUp = 0;
        $orDown = 0;
        foreach ($task as $tsk)
        {
            if($uptT == $tsk['id'] and $uptL == $tsk['project_id'])
            { 
                $end++; continue; 
            }
            
            if($end == 0) $orUp = $tsk['id'];
            if($end > 0)
            {   
                $orDown = $tsk['id'];
                break;   
            }
        }
        $orUp = (int) abs($orUp);
        $orDown = (int) abs($orDown);
        if($_GET['order'] == "up")
        {
            if($orUp == 0)
            {
                header("Location: ".LINK_HOST.$linkUri);
                exit;
            }
            else
            {
                if(uptOrder2($uptT, $orUp, $uptL))
                {
                    header("Location: ".LINK_HOST.$linkUri);
                    exit; 
                }
            }
        }
        if($_GET['order'] == "down")
        {
            if($orDown == 0)
            {
                header("Location: ".LINK_HOST.$linkUri);
                exit;
            }
            else
            {
                if(uptOrder2($uptT, $orDown, $uptL, false))
                {
                    header("Location: ".LINK_HOST.$linkUri);
                    exit; 
                }
            }
        }
    }

// ------------------------переименовываем лист проекта---------------------
    if($_POST['oper'] == "renameList")
    {
        $name = clear($_POST['newList']);
        if(empty($name))
        {
            header("Location: ".LINK_HOST.$linkUri);
            exit;
        }
        $idPro = (int) abs($_POST['idPro']);
        if(uptList($idPro, $name)) header("Location: ".LINK_HOST.$linkUri);
    }
// ------------------------регистрируем нового пользователя---------------------
    if($_POST['r_email'])
    {
        // если заполнены все поля то проверяем данные
        if(!empty($_POST['r_email']) and !empty($_POST['r_pass1']) and !empty($_POST['r_pass2']))
        {
            if($_POST['r_pass1'] != $_POST['r_pass2'])  // если поля паролей на совпадают - нужно отобразить сообщение об этом
                header("Location: ".LINK_HOST."?reg=2&reg_err=1");
            else                                        // если поля паролей таки совпадают - продолжаем проветку
            {
                $login = clear($_POST['r_email']) ?: $login;    // принимаем логин
                if(!userExists($login))                         // проверяем есть ли пользователь с таким же email
                {
                    $pass = clear($_POST['r_pass1']) ?: $pass;
                    $hash = trim(password_hash($pass, PASSWORD_BCRYPT)); // хешируем пароль            
                    if(saveUser($login, $hash))
                        //$res = "Хэш ".$hash." успешно добавлен в файл";
                        //$res = "Пользователь ".$login." успешно добавлен.";
                    {  header("Location: ".LINK_HOST."?reg=1&reg_com=".$login); exit; }
                    else
                    {  header("Location: ".LINK_HOST."?reg=1&reg_err=3"); exit; }
                        //$res = "При записи хэша ".$hash." произошла ошибка!";
                        //$res = "При добавлении пользователя ".$login." произошла ошибка - ".mysqli_error($link);
                }
            }
        }
        // если же запонены не все поля, отображаем ошибку с просьбой заполнить таки все
        else header("Location: ".LINK_HOST."?reg=2&reg_err=2");
    }

// ------------------------Аутентификация---------------------
    if($_POST['email'])
    {
        // если заполнены все поля то проверяем данные
        if(!empty($_POST['email']) and !empty($_POST['pass']))
        { 
            $login = trim(strip_tags($_POST['email']));
            $pw = trim(strip_tags($_POST['pass']));
            $res = control($login, $pw);
            if(!$res)
                header("Location: ".LINK_HOST."?reg=1&reg_err=5");
            else
            {
                    $_SESSION['control'] = $res;
                    header("Location: ".LINK_HOST);
                    exit;
            }         
         }
        // если же запонены не все поля, отображаем ошибку с просьбой заполнить таки все
        else header("Location: ".LINK_HOST."?reg=1&reg_err=4");
        
        
    }



?>