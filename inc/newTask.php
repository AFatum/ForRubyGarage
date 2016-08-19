<?php
    require("db.php");
    require("lib.php");
    $task = clear($_POST['newTask']);
    $idLst = clear($_POST['idList']);
    //$colTsk = selectTasks($idLst);
    //$ord = 0;

    /*if(is_array($colTsk))
    {
        foreach ($colTsk as $tsk)
        {
            if($tsk['ord'] > $ord)
                $ord = $tsk['ord'];
        }
        $ord ++;
    }*/
    if (newTask($task, $idLst, $ord))
        header("Location: http://ruby.ua/index.php");
    else echo "Какая-то ошибка";
?>