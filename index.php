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
                    
                echo "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$nameList."
                        <div class='wb'><span class='wa'><a class='nav nav1' href='index.php?renameList=".$lst['id']."'></a></span> | 
                        <span class='wa'><a class='nav nav2' href='inc/oper.php?deleteList=".$lst['id']."'></a></span></div></div>";
                echo "<div class='newListName'><span class='wr'><span class='nav nav1'></span></span>
                        <div class='topNewList'>
                        
                        <form action='inc/oper.php' method='post'>
                        <input class='newListNameInputTxt' type='text' name='newTask' placeholder='Start typing here to create a task...'>
                        <input type='hidden' name='idList' value=".$lst['id'].">
                        <input type='hidden' name='oper' value='newTask'>
                        <input class = 'AddTaskBut' type='submit' value='Add Task'></form>
                    </div></div><table>";
                if(is_array($task))
                {
                    foreach($task as $tsk)                  // формирование списка заданий
                    {
                        if($lst['id'] == $tsk['project_id'])
                        {
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
                                    <span class='wn'><a class='nav nav3' href='index.php?updl=".$lst['id']."&updt=".$tsk['id']."'></a></span> | 
                                    <span class='wn'><a class='nav nav4' href='inc/oper.php?uptL=".$lst['id']."&uptT=".$tsk['id']."'></a></span>
                                </td></tr>";
                        }
                    }
    
                echo "</table></div>";
                }
            
            }
        }
    
    ?>
    
    <a class = "AddList" href ="index.php?id=add2list" title="add to list">Add TODO List</a>
    <a class = "AddList sqlTask" href ="index.php?id=SQLtask" title="SQL task">SQL task</a>
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
            
        }

?>