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
        if($_SESSION['control'])
        //if($_SESSION['control'] or !$_GET['reg'])
        {
            echo "<p class='login'>Your user's email: ".$_SESSION['control']." - <a href='index.php?log=out' title='logout'>Log out</a></p>";
            if($_GET['log'] == 'out')    // вылогиниваемся
                { session_destroy(); header("Location: ".LINK_HOST); }
            $pro = selectALL();                                 // получаем основной массив данных
            //$list = selectList();                             // получаем количества проектов списков задач
            //$task = selectTasks();                            // получение списка задач
            $uri = $_SERVER['REQUEST_URI'];                     // берем значение страницы
            if(!is_array($pro))                                // если списков нет нужно отобразить сообщение об их отсутствии
                echo "<p>Please create new List</p>";
            else                                                // если уже есть созданные списки, нужно их отобразить
            {
                //$po = 1; $ts = 0;
                $po = 0; $ts = 0;
                foreach($pro as $p)
                {
                    //if($po == $p['pro_id'] and $ts == 0)
                    //if($ts == 0 or $po != $p['pro_id'])
                    if($po != $p['pro_id'])
                    {
                        if($po > 0) echo "</table></div>";      // закрываем таблицу и основной div
                        //if($po > 0 and empty($p['id'])) echo "</div>";              // закрываем таблицу и основной div
                        $po = $p['pro_id'];
                        $ts = 1;
                        if($_GET['renameList'] == $p['pro_id'])
                        {                                       // вставляем форму для переименования листа
                            $nameList = "<form action='inc/oper.php' method='post'>
                                        <input class='newListNameTxt' type='text' name='newList' placeholder='please enter new project name'>
                                        <input type='hidden' name='idPro' value='".$p['pro_id']."'>
                                        <input type='hidden' name='oper' value='renameList'>
                                        <input class='AddTaskBut updateList' type='submit' value='update'></form>";
                        }
                        else                                    // если лист НЕ надо переименовывать, задаём имя из массива
                            $nameList = $p['pro'];
                                                                // Определяем ссылку переименования проекта
                         if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['renameList']))
                            $linkRenameList = "href='".LINK_HOST.$_SERVER['REQUEST_URI']."&renameList=".$p['pro_id'];
                        else if(!empty($_GET['renameList']))
                            $linkRenameList = "href='".LINK_HOST.$_SERVER['REQUEST_URI'];
                        else
                            $linkRenameList = "href='index.php?renameList=".$p['pro_id'];
                                                                // выводим имя проекта с формой добавления задания
                        echo "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$nameList."
                                <div class='wb'><span class='wa'><a class='nav nav1' ".$linkRenameList."'></a></span> | 
                                <span class='wa'><a class='nav nav2' href='inc/oper.php?deleteList=".$p['pro_id']."&link=".$uri."'></a></span></div></div>";
                        echo "<div class='newListName'><span class='wr'><span class='nav nav1'></span></span>
                                <div class='topNewList'>

                                <form action='inc/oper.php' method='post'>
                                <input class='newListNameInputTxt' type='text' name='newTask' placeholder='Start typing here to create a task...'>
                                <input type='hidden' name='idList' value=".$p['pro_id'].">
                                <input type='hidden' name='oper' value='newTask'>
                                <input class = 'AddTaskBut' type='submit' value='Add Task'></form>
                            </div></div>";
                        echo "<table>";
                    }
                                                                // формируем список заданий
                    //if($po == $p['pro_id'])
                    if($po == $p['pro_id'] and !empty($p['id']))
                    {
                                                                // Опрелеляем ссылку переименования задачи 
                       if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['updl']) and empty($_GET['updt']))
                            $linkRenameTask = "href='".LINK_HOST.$_SERVER['REQUEST_URI']."&updl=".$p['pro_id']."&updt=".$p['id'];
                        else if(!empty($_GET['updl']) and !empty($_GET['updt']))
                            $linkRenameTask = "href='".LINK_HOST.$_SERVER['REQUEST_URI'];
                        else
                            $linkRenameTask = "href='index.php?updl=".$p['pro_id']."&updt=".$p['id'];
                                                                // Определеяем стили для оформления статусов заданий
                        if($p['status'] == 0)
                        {   $stlTr = NULL;  $stlTr2 = NULL; $nav="nav5";    }
                        else
                        {   $stlTr = " class='statusTrue'";  $stlTr2 = " statusTrue"; $nav="nav6";  }
                                                                // Выводим форму переименования задания, если нужно
                        if($_GET['updl'] == $p['pro_id'] and $_GET['updt'] == $p['id'])
                        {
                                    $taskName = "<td".$stlTr.">  <form action='inc/oper.php' method='post'>
                                                <input class='newTaskNameTxt' type='text' name='newName' placeholder='please enter new task name'>
                                                <input type='hidden' name='idTsk' value='".$p['id']."'>
                                                <input type='hidden' name='idPro' value='".$p['pro_id']."'>
                                                <input type='hidden' name='oper' value='renameTask'>
                                                <input class='AddTaskBut update' type='submit' value='update'></form></td>";     
                        }
                        else $taskName = "<td".$stlTr.">".$p['name']."</td>";
                                                                // Выводим список заданий
                        echo "<tr'><td class='navIc2".$stlTr2."'><a class='nav ".$nav."' href='inc/oper.php?status=1&updl=".$p['pro_id']."&updt=".$p['id']."&sts=".$p['status']."&link=".$uri."'></a></td>";
                                echo $taskName;
                                echo "<td class='navIc".$stlTr2."'>
                                        <span class='wn'><a class='nav nav1' href='inc/oper.php?order=up&updl=".$p['pro_id']."&updt=".$p['id']."&link=".$uri."'></a></span> | 
                                        <span class='wn'><a class='nav nav2' href='inc/oper.php?order=down&updl=".$p['pro_id']."&updt=".$p['id']."&link=".$uri."'></a></span> |  
                                        <span class='wn'><a class='nav nav3' ".$linkRenameTask."'></a></span> | 
                                        <span class='wn'><a class='nav nav4' href='inc/oper.php?uptL=".$p['pro_id']."&uptT=".$p['id']."&link=".$uri."'></a></span>
                                    </td></tr>";            

                        //echo "</table></div>";
                    }
                } // конец основного foreach
                echo "</table></div>";  
            }
                                                                // Опрелеляем ссылку для отображения формы add2list    
                if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "add2list")
                    $linkAddList = "href='".LINK_HOST.$_SERVER['REQUEST_URI']."&id=add2list'";
                else
                    $linkAddList = "href='index.php?id=add2list'";
                                                                // Опрелеляем ссылку для отображения формы SQLtask
                if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "SQLtask")
                    $linkSQLtask = "href='".LINK_HOST.$_SERVER['REQUEST_URI']."&id=SQLtask'";
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
           require("inc/SQLtask.inc.php"); // подключаем обработчик SQL запросов 
    } // закрытие блока для залогиненных пользователей (строка 14)
    else
    { // если пользователь не авторизирован, выводим форму для логина или регистрации 
        if(!$_GET['reg'] or $_GET['reg'] < 2) 
        { // форма логина?>
         <div class='genMod autoGen'>
            <div class='listName autoTitle'>Autorization</div>
            <div class='newListName'>
                <form method="post" action="inc/oper.php">
                    <input class="newListNameInputTxt autorization" type="text" placeholder="Please enter your email" name="email"><br>
                    <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password" name="pass"><br>
                    <input class="AddTaskBut" type="submit" value="login">
                </form>
            </div>
            <?php   if(!empty($_GET['reg_com'])) echo "<p class='user'>User width email: '".$_GET['reg_com']."' was created successfully. You can login.</p>";
                    if($_GET['reg_err'] == 3) echo "<p class='user_er'>Error User registration: ".mysqli_error($link)."</p>";
                    if($_GET['reg_err'] == 4) echo "<p class='user_er'>Please complete all fields".mysqli_error($link)."</p>";
                    if($_GET['reg_err'] == 5) echo "<p class='user_er'>You have entered an invalid username or password</p>"; ?>
            <a href="index.php?reg=2" title="Registration">Registration</a>
        </div>  
        <?php 
        }
        if($_GET['reg'] == 2)
        { // форма регистрации нового пользователя?> 
            <div class='genMod autoGen'>
                <div class='listName autoTitle'>Registration</div>
                <div class='newListName'>
                    <form method="post" action="inc/oper.php">
                        <input class="newListNameInputTxt autorization" type="text" placeholder="Please enter your email" name="r_email"><br>
                        <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password" name="r_pass1"><br>
                        <input class="newListNameInputTxt autorization" type="password" placeholder="Please enter your password again" name="r_pass2"><br>
                        <input class="AddTaskBut" type="submit" value="ok">
                    </form>
                    <?php   if($_GET['reg_err'] == 1) echo "<span class='pass'>Your data of passwod shoud coincide twice</span>"; 
                            if($_GET['reg_err'] == 2) echo "<br><span class='pass'>Please complete all fields</span>"; ?>
                </div>
                <a href="index.php?reg=1" title="Login">Login</a>
            </div>
        <?php 
        }
    }

?>
        