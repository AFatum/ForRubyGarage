<?php
//--------Подготовка строк к внесению в БД----------------------------
function clear($data)
{
    global $link;
    $Data = (string) $data;   
    return mysqli_real_escape_string ($link, trim(strip_tags($Data)));
}
//--------Создание нового проекта ------------------------------------
function newList($name)
{
    global $link;
    if(!is_string($name)) return false;

    //$name = mysqli_real_escape_string ($link, trim(strip_tags($name)));
    $sql = "INSERT INTO projects(name) VALUES (?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;

    mysqli_stmt_bind_param($stmtIns, 's', $name);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    mysqli_stmt_close($stmtIns);
    return true;
}
//--------Создания новой задачи-------------------------------------
function newTask($task, $pro)
{
    global $link;
    if(!is_string($task)) return false;

    //$name = mysqli_real_escape_string ($link, trim(strip_tags($name)));
    $sql = "INSERT INTO tasks(name, project_id) VALUES (?, ?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;

    mysqli_stmt_bind_param($stmtIns, 'si', $task, $pro);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    mysqli_stmt_close($stmtIns);
    return true;
}

//--------Выбор листа (проекта) проекта-------------------------------
function selectList()
{
	global $link;

	$sql = "SELECT id, name
			FROM projects
			ORDER BY id";
	if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}

//--------Выбор задач-------------------------------------------------
function selectTasks($idLst = 0)
{
	global $link;

    if($idLst > 0)
    {
        $sql = "SELECT id, name, status, project_id 
                FROM tasks
                WHERE project_id LIKE ".$idLst."
                ORDER BY id";
    }
    else
    {
        $sql = "SELECT id, name, status, project_id 
                FROM tasks
                ORDER BY id";
    }
    
	if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	mysqli_free_result($result);
	return $items;
}

//--------Удаление списка задач---------------------------------------------
function deleteList($idPro)
{
    global $link;
    
    $sql = "DELETE FROM projects
            WHERE id LIKE ".$idPro;
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}
//--------Удаление задачи---------------------------------------------
function deleteTask($id, $idPro)
{
    global $link;
    if($id == "all")
    {
        $sql = "DELETE FROM tasks
            WHERE project_id LIKE ".$idPro;
    }
    else {
        $sql = "DELETE FROM tasks
                WHERE id LIKE ".$id." 
                AND project_id LIKE ".$idPro;
    }
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}

//--------Переименование листа задач----------------------------------
function uptList($idPro, $name)
{
    global $link;
    $sql = "UPDATE projects
		      SET
                name = '".$name."' 
              WHERE id LIKE ".$idPro;
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}

//--------Переименование задачи---------------------------------------
function uptTask($id, $idPro, $name)
{
    global $link;
    $sql = "UPDATE tasks
		      SET
                name = '".$name."' 
              WHERE id LIKE ".$id." 
              AND project_id LIKE ".$idPro;
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}

//--------Изменение статуса задачи------------------------------------
function uptStatus($id, $idPro, $status)
{
    global $link;

    //return true;
    //$sts = ($status == 0) ? 1 : 0;
    if($status == 0) $sts = 1;
    if($status == 1) $sts = 0;
    $sql = "UPDATE tasks
		      SET
                status = ".$sts." 
              WHERE id LIKE ".$id." 
              AND project_id LIKE ".$idPro;
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}

//--------Изменение порядка задачи------------------------------------
function uptOrder($id, $idPro, $ord, $up=0)
{
    global $link;
    if(!is_int($id) or !is_int($idPro) or !is_int($ord)) return false;
    
    if($up == 0)
    {
        $sql = "UPDATE tasks
		      SET
                ord = ord - 1  
              WHERE id LIKE ".$id." 
              AND ord LIKE ".$ord." 
              AND project_id LIKE ".$idPro;
    }
    else if($up == 1)
    {
        $sql = "UPDATE tasks
		      SET
                ord = ord + 1  
              WHERE id LIKE ".$id." 
              AND ord LIKE ".$ord." 
              AND project_id LIKE ".$idPro;
    }
    else
    {
        $sql = "UPDATE tasks
		      SET
                ord = ".$ord." 
              WHERE id LIKE ".$id." 
              AND project_id LIKE ".$idPro;
    }
    if(!$result = mysqli_query($link, $sql))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else { mysqli_free_result($result); return true; }
}

//--------Изменение порядка задачи-(доработанная)---------------------
function uptOrder2($id1, $id2, $idPro, $up=true)
{
    global $link;
    
    if(!is_int($id1) or !is_int($id2) or !is_int($idPro)) return false;
    
    $ID1=($up) ? $id1 : $id2; // устанавливаем значение перемещения вверх или вниз..
    $ID2=($up) ? $id2 : $id1; //.. в зависимости от полученного параметра $up
    
    $sql1 = "UPDATE tasks
                    SET
                        id = 11111
                    WHERE
                        id LIKE ".$ID1;
        
    $sql2 = "UPDATE tasks
                SET
                    id = ".$ID1." 
                WHERE
                    id LIKE ".$ID2;

    $sql3 = "UPDATE tasks
                SET
                    id = ".$ID2." 
                WHERE
                    id LIKE 11111";
    
    if(!$result1 = mysqli_query($link, $sql1))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
    else 
    {
        if(!$result2 = mysqli_query($link, $sql2))
        die("Какая-то ошибка в запросе ".mysqli_error($link));
        else
        {
            if(!$result3 = mysqli_query($link, $sql3))
            die("Какая-то ошибка в запросе ".mysqli_error($link));
            else
            {
                mysqli_free_result($result1, $result2, $result3);
                return true;
            }
        }
    }
}

//==========================================СОРТИРОВКА====================
function countRes($name=0, $cnt=0)
{
    global $link;
    
    //if (!is_array($arr)) return false;
    if($name == 0 and $cnt == 0)
    {  
        $sql = "CREATE TEMPORARY TABLE t_count(
        id int NOT NULL auto_increment,
        name varchar(255) NOT NULL default '',
        cnt int(9) NOT NULL,
        PRIMARY KEY (id))";
        if(!$result = mysqli_query($link, $sql)) return false;
        else return true;
    }
    
    else if($name == -5 and $cnt == -5)
    {
        $sql = "SELECT id, name, cnt 
			FROM t_count
			ORDER BY cnt";
	if(!$result = mysqli_query($link, $sql))
		return false;
	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
	//mysqli_free_result($result);
	return $items;
    }
    
    else if($name < 0 or $cnt < 0)
    {
        $sql = "DROP TEMPORARY TABLE `t_count`";
        if(!$result = mysqli_query($link, $sql)) return false;
        else return true;
    }
    else
    {
        $sql = "INSERT INTO t_count(name, cnt) VALUES (?, ?)";
        if(!$stmtIns = mysqli_prepare($link, $sql)) return false;

        mysqli_stmt_bind_param($stmtIns, 'si', $name, $cnt);
        mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
       // mysqli_stmt_close($stmtIns);
        return true;
    }
}
function countRes2($arr)
{
    global $link;
    
    if (!is_array($arr)) return false;
    $sql = "CREATE TEMPORARY TABLE t_count(
        id int NOT NULL auto_increment,
        name varchar(255) NOT NULL default '',
        cnt int(9) NOT NULL,
        PRIMARY KEY (id))";
        if(!$result = mysqli_query($link, $sql)) return false;
        else
        {
            foreach($arr as $key => $lst)
            {
                $sql2 = "INSERT INTO t_count(name, cnt) VALUES (?, ?)";
                if(!$stmtIns = mysqli_prepare($link, $sql2)) return false;
                mysqli_stmt_bind_param($stmtIns, 'si', $key, $lst);
                if(!mysqli_stmt_execute($stmtIns)) die("Какая-то ошибка в запросе ".mysqli_error($link));
                else continue;
            }
            $sql3 = "SELECT id, name, cnt 
			FROM t_count
			ORDER BY cnt";
            if(!$result = mysqli_query($link, $sql3)) return false;
	       else
           {
                $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
                mysqli_free_result($result);
                mysqli_stmt_close($stmtIns);
                return $items;
           }
        }
}



//========================================================================
?>