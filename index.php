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
        $uri = $_SERVER['REQUEST_URI']; // берем значение страницы
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
                
                // Опрелеляем ссылку переименования проекта
                if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['renameList']))
                    $linkRenameList = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&renameList=".$lst['id'];
                else if(!empty($_GET['renameList']))
                    $linkRenameList = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                else
                    $linkRenameList = "href='index.php?renameList=".$lst['id'];
                    
                echo "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$nameList."
                        <div class='wb'><span class='wa'><a class='nav nav1' ".$linkRenameList."'></a></span> | 
                        <span class='wa'><a class='nav nav2' href='inc/oper.php?deleteList=".$lst['id']."&link=".$uri."'></a></span></div></div>";
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
                            // Опрелеляем ссылку переименования задачи 
                           if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['updl']) and empty($_GET['updt']))
                                $linkRenameTask = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&updl=".$lst['id']."&updt=".$tsk['id'];
                            else if(!empty($_GET['updl']) and !empty($_GET['updt']))
                                $linkRenameTask = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                            else
                                $linkRenameTask = "href='index.php?updl=".$lst['id']."&updt=".$tsk['id'];
                            
                            
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


                            echo "<tr'><td class='navIc2".$stlTr2."'><a class='nav ".$nav."' href='inc/oper.php?status=1&updl=".$lst['id']."&updt=".$tsk['id']."&sts=".$tsk['status']."&link=".$uri."'></a></td>";
                            //echo "<td class='td3'></td>";
                            echo $taskName;
                            echo "<td class='navIc".$stlTr2."'>
                                    <span class='wn'><a class='nav nav1' href='inc/oper.php?order=up&updl=".$lst['id']."&updt=".$tsk['id']."&link=".$uri."'></a></span> | 
                                    <span class='wn'><a class='nav nav2' href='inc/oper.php?order=down&updl=".$lst['id']."&updt=".$tsk['id']."&link=".$uri."'></a></span> |  
                                    <span class='wn'><a class='nav nav3' ".$linkRenameTask."'></a></span> | 
                                    <span class='wn'><a class='nav nav4' href='inc/oper.php?uptL=".$lst['id']."&uptT=".$tsk['id']."&link=".$uri."'></a></span>
                                </td></tr>";
                        }
                    }
    
                echo "</table></div>";
                }
            
            }
        }
    
    // Опрелеляем ссылку для отображения формы add2list    
    if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "add2list")
        $linkAddList = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&id=add2list'";
    else
        $linkAddList = "href='index.php?id=add2list'";
    // Опрелеляем ссылку для отображения формы SQLtask
    if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "SQLtask")
        $linkSQLtask = "href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&id=SQLtask'";
    else
        $linkSQLtask = "href='index.php?id=SQLtask'";
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
       require("inc/SQLtask.inc.php"); // подключаем обработчик SQL запросов ?>