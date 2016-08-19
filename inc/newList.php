<?php
    require("db.php");
    require("lib.php");

    if($_POST['oper'] == "newList")
    {
        $nameNL = (string) clear($_POST['list']);
        if(newList($nameNL)) {
            header("Location: http://ruby.ua/index.php");
        }       
    }

    if($_POST['oper'] == "newTask")
    {
        $task = clear($_POST['newTask']);
        $idLst = clear($_POST['idList']);
        $colTsk = selectTasks($idLst);
        $ord = 0;

        if(is_array($colTsk))
        {
            foreach ($colTsk as $tsk)
            {
                if($tsk['ord'] > $ord)
                    $ord = $tsk['ord'];
            }
            $ord ++;
        }
        if (newTask($task, $idLst, $ord))
            header("Location: http://ruby.ua/index.php");
        else echo "Какая-то ошибка";
    }

    if($_POST['oper'] == "renameTask")
    {
        $name = clear($_POST['newName']);
        $idTsk = (int) abs($_POST['idTsk']);
        $idPro = (int) abs($_POST['idPro']);
        /*echo "<pre>";
        var_dump($idTsk, $idPro, $name);
        echo "</pre>";
        exit;*/
        if(uptTask($idTsk, $idPro, $name)) header("Location: http://ruby.ua/index.php");
    }
?>