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
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
        }       
    }

// ------------------------создаём новую задачу----------------------------------
    if($_POST['oper'] == "newTask")
    {
        $task = clear($_POST['newTask']);
        $idLst = (int) abs($_POST['idList']);

        if (newTask($task, $idLst))
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
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
        if(uptTask($idTsk, $idPro, $name)) header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
    }

// ------------------------изменение статуса задачи----------------------------------
    if($_GET['status'] == 1)
    {
        $status = (int) abs($_GET['sts']);
        $uptL = (int) abs($_GET['updl']);
        $uptT = (int) abs($_GET['updt']);   
        if(uptStatus($uptT, $uptL, $status))
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
        else echo "Какая-то ошибка в запросе ".mysqli_error($link);
    }

// ------------------------удаление списка задачи----------------------------------
if(!empty($_GET['deleteList']))
    {
        $uptL = (int) abs($_GET['deleteList']);
        
        if(deleteTask("all", $uptL) and deleteList($uptL))
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);  
    }
// ------------------------удаление задачи----------------------------------
if(!empty($_GET['uptL']) and !empty($_GET['uptT']))
    {
        $uptL = (int) abs($_GET['uptL']);
        $uptT = (int) abs($_GET['uptT']);
        
        if (deleteTask($uptT, $uptL))
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
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
                header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
                exit;
            }
            else
            {
                if(uptOrder2($uptT, $orUp, $uptL))
                {
                    header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
                    exit; 
                }
            }
        }
        if($_GET['order'] == "down")
        {
            if($orDown == 0)
            {
                header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
                exit;
            }
            else
            {
                if(uptOrder2($uptT, $orDown, $uptL, false))
                {
                    header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
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
            header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
            exit;
        }
        $idPro = (int) abs($_POST['idPro']);
        if(uptList($idPro, $name)) header("Location: https://stormy-river-47352.herokuapp.com/".$linkUri);
    }

?>