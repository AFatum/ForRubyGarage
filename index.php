<?php
	require("inc/db.php");
	require("inc/lib.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="../inc/stl.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>TODO List</title>
</head>
<body>
   <?php
        $list = selectList();        // получаем количества проектов списков задач
        $task = selectTasks();       // получение списка задач
        if(!is_array($list))         // если списков нет нужно отобразить сообщение об их отсутствии
            echo "<p>Please create new List</p>";
        else                        // если уже есть созданные списки, нужно их отобразить
        {
            foreach($list as $lst)          // формирование проекта
            {
                if($_GET['renameList'] == $lst['id'])
                {
                    $nameList = "<form action='inc/oper.php' method='post'>
                                <input class='newListNameTxt' type='text' name='newList' placeholder='please enter new project name'>
                                <input type='hidden' name='idPro' value='".$lst['id']."'>
                                <input type='hidden' name='oper' value='renameList'>
                                <input class='AddTaskBut updateList' type='submit' value='update'></form>";
                }
                else 
                    $nameList = $lst['name'];
                
                // определяем есть ли переданные параметры GET для корректного отображения форм
                $linkRenameList = (!empty($_SERVER['QUERY_STRING'])) ? "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&renameList=".$lst['id'] : "href='index.php?renameList=".$lst['id'];
                    
                echo "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$nameList."
                        <div class='wb'><span class='wa'><a class='nav nav1' ".$linkRenameList."'></a></span> | 
                        <span class='wa'><a class='nav nav2' href='inc/oper.php?deleteList=".$lst['id']."'></a></span></div></div>";
                echo "<div class='newListName'><span class='wr'><span class='nav nav1'></span></span>
                        <div class='topNewList'>
                        
                        <form action='inc/oper.php' method='post'>
                        <input class='newListNameInputTxt' type='text' name='newTask' placeholder='Start typing here to create a task...'>
                        <input type='hidden' name='idList' value=".$lst['id'].">
                        <input type='hidden' name='oper' value='newTask'>
                        <input class = 'AddTaskBut' type='submit' value='Add Task'></form>
                    </div></div>";
                if(is_array($task))
                {
                    echo "<table>";
                    foreach($task as $tsk)                  // формирование списка заданий
                    {      
                        if($lst['id'] == $tsk['project_id'])
                        {
                            // определяем есть ли переданные параметры GET для корректного отображения форм
                            $linkRenameTask = (!empty($_SERVER['QUERY_STRING'])) ? "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&updl=".$lst['id']."&updt=".$tsk['id'] : "href='index.php?updl=".$lst['id']."&updt=".$tsk['id'];                            
                            
                            if($tsk['status'] == 0)
                            {   $stlTr = NULL;  $stlTr2 = NULL; $nav="nav5";    }
                            else
                            {   $stlTr = " class='statusTrue'";  $stlTr2 = " statusTrue"; $nav="nav6";  }

                            if($_GET['updl'] == $lst['id'] and $_GET['updt'] == $tsk['id'])
                                $taskName = "<td".$stlTr.">  <form action='inc/oper.php' method='post'>
                                            <input class='newTaskNameTxt' type='text' name='newName' placeholder='please enter new task name'>
                                            <input type='hidden' name='idTsk' value='".$tsk['id']."'>
                                            <input type='hidden' name='idPro' value='".$lst['id']."'>
                                            <input type='hidden' name='oper' value='renameTask'>
                                            <input class='AddTaskBut update' type='submit' value='update'></form></td>";
                            else $taskName = "<td".$stlTr.">".$tsk['name']."</td>";


                            echo "<tr'><td class='navIc2".$stlTr2."'><a class='nav ".$nav."' href='inc/oper.php?status=1&updl=".$lst['id']."&updt=".$tsk['id']."&sts=".$tsk['status']."'></a></td>";
                            //echo "<td class='td3'></td>";
                            echo $taskName;
                            echo "<td class='navIc".$stlTr2."'>
                                    <span class='wn'><a class='nav nav1' href='inc/oper.php?order=up&updl=".$lst['id']."&updt=".$tsk['id']."'></a></span> | 
                                    <span class='wn'><a class='nav nav2' href='inc/oper.php?order=down&updl=".$lst['id']."&updt=".$tsk['id']."'></a></span> |  
                                    <span class='wn'><a class='nav nav3' ".$linkRenameTask."'></a></span> | 
                                    <span class='wn'><a class='nav nav4' href='inc/oper.php?uptL=".$lst['id']."&uptT=".$tsk['id']."'></a></span>
                                </td></tr>";
                        }
                    }
    
                echo "</table></div>";
                }
            
            }
        }
    
    // определяем есть ли переданные параметры GET для корректного отображения форм
    $linkAddList = (!empty($_SERVER['QUERY_STRING'])) ? "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&id=add2list'" : "href='index.php?id=add2list'"; 
    $linkSQLtask = (!empty($_SERVER['QUERY_STRING'])) ? "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&id=SQLtask'" : "href='index.php?id=SQLtask'"; 
    ?>
    
    <a class = "AddList" <?= $linkAddList ?> title="Add TODO List">Add TODO List</a>
    <a class = "AddList sqlTask" <?= $linkSQLtask ?> title="SQL task">SQL task</a>
</body>
</html>
<?php
        if($_GET['id'] == "add2list")
        {
            ?>
            <form action="inc/oper.php" method="post">
                <input class="newListTxt" type="text" name="list" placeholder="Start typing here to create new list">
                <input type='hidden' name='oper' value='newList'>
                <input type="submit" value="Create List" class="AddNewList"> 
            </form>   
            <?php
        }
        if($_GET['id'] == "SQLtask")
        {
            echo "<div class='genMod sqlMod'><div class='listName sqlMod2'>SQL Task</div>
                    <div class='newListName sqlMod2'>
                        <form action='inc/SQLtask.php' method='post'>
                        <select name='GetOrder'>
                            <option value='cntEachPro'>Get the count of all tasks in each project, order by tasks count descending</option>
                            <option value='cntEachNms'>Get the count of all tasks in each project, order by projects names</option>
                            <option value='dupTsk'>Get the list of tasks with duplicate names. Order alphabetically</option>
                            <option value='Garage'>Get the list of tasks having several exact matches of both name and status, from the project 'Garage'</option>
                            <option value='more10'>Get the list of project names having more than 10 tasks in status ‘completed’. Order by project_id</option>
                        </select> 
                        <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get1'><br>
                        <div class='letter'>Get all statuses is <select name='statuses'> 
                            <option value='com'>complete</option>
                            <option value='notCom'>not complete</option>
                        </select>, not repeating, alphabetically ordered
                        <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get4'></div>
                        <div class='letter'>Get the tasks for all projects having the name beginning with <select name='beginLetter'> 
                            <option value='a'>'A'</option>
                            <option value='b'>'B'</option>
                            <option value='c'>'C'</option>
                            <option value='d'>'D'</option>
                            <option value='e'>'E'</option>
                            <option value='f'>'F'</option>
                            <option value='g'>'G'</option>
                            <option value='h'>'H'</option>
                            <option value='i'>'I'</option>
                            <option value='j'>'J'</option>
                            <option value='k'>'K'</option>
                            <option value='l'>'L'</option>
                            <option value='m'>'M'</option>
                            <option value='n'>'N'</option>
                            <option value='o'>'O'</option>
                            <option value='p'>'P'</option>
                            <option value='q'>'Q'</option>
                            <option value='r'>'R'</option>
                            <option value='s'>'S'</option>
                            <option value='t'>'T'</option>
                            <option value='u'>'U'</option>
                            <option value='v'>'V'</option>
                            <option value='w'>'W'</option>
                            <option value='x'>'X'</option>
                            <option value='y'>'Y'</option>
                            <option value='z'>'Z'</option>
                        </select> letter
                        <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get2'></div>
                        <div class='letter letter2'>Get the list of all projects containing the <select name='middleLetter'> 
                            <option value='a'>'a'</option>
                            <option value='b'>'b'</option>
                            <option value='c'>'c'</option>
                            <option value='d'>'d'</option>
                            <option value='e'>'e'</option>
                            <option value='f'>'f'</option>
                            <option value='g'>'g'</option>
                            <option value='h'>'h'</option>
                            <option value='i'>'i'</option>
                            <option value='j'>'j'</option>
                            <option value='k'>'k'</option>
                            <option value='l'>'l'</option>
                            <option value='m'>'m'</option>
                            <option value='n'>'n'</option>
                            <option value='o'>'o'</option>
                            <option value='p'>'p'</option>
                            <option value='q'>'q'</option>
                            <option value='r'>'r'</option>
                            <option value='s'>'s'</option>
                            <option value='t'>'t'</option>
                            <option value='u'>'u'</option>
                            <option value='v'>'v'</option>
                            <option value='w'>'w'</option>
                            <option value='x'>'x'</option>
                            <option value='y'>'y'</option>
                            <option value='z'>'z'</option>
                        </select> letter in the middle of the name.
                        <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get3'></div>
                        </form>
                    </div>";
            if($_SESSION['SQL'] == "cntEachPro") // если нужно вывести таблицу проектов, отсортированной по количеству заданий
            {
                $resCntEcPro = array();
                echo "<table class='cntTask'>";
                echo "<tr><th>List Name
                <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=ksort'></a></span>
                <span class='wn1'><a class='nav nav8' href='inc/SQLtask.php?sort=krsort'></a></span></th>
                        <th class='tdCntTask'>Count Task
                        <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=asort'></a></span>
                        <span class='wn1'><a class='nav nav8' href='inc/SQLtask.php?sort=arsort'></a></span></th>
                        </tr>";
                foreach($list as $lst)
                {
                    foreach($task as $tsk)
                    {
                        if($lst['id'] == $tsk['project_id'])
                        {
                            if($idPro != $tsk['project_id'])
                            {
                                $cnt = 1;
                                $resCntEcPro[$lst['name']] = $cnt;
                                $idPro = $tsk['project_id'];
                                continue;
                            }
                            $cnt ++;
                            $resCntEcPro[$lst['name']] = $cnt;
                        }
                    }
                }
                // сортируем массив в соответствии с полученными параметрами
                if($_GET['sort'] == 'asort') asort($resCntEcPro);
                if($_GET['sort'] == 'arsort') arsort($resCntEcPro);                
                if($_GET['sort'] == 'ksort') ksort($resCntEcPro);                
                if($_GET['sort'] == 'krsort') krsort($resCntEcPro);                
                foreach($resCntEcPro as $key => $rst)
                    echo "<tr><td>".$key."</td><td class='tdCntTask'>".$rst."</td></tr>";
                echo "</table>";
                //unset($_SESSION['SQL']);
            }
            echo "</div>";
            /*echo "<pre>";
            print_r($_SESSION['res2']);
            print_r($_SESSION['SQL2']);
            echo "</pre>";*/
        }
       else unset($_SESSION['SQL']); // if(!$_GET['id'] == "SQLtask") удаляем переменную за ненадобностью
?>