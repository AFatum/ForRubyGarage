<?php
    require("db.php");
    require("lib.php");

if($_POST['GetOrder'])
{
    switch($_POST['GetOrder'])
    {
        case "cntEachPro":
            $tasks = selectTasks();
            $lists = selectList();
            $result = array();
            $idPro = 0;
            $cnt = 0;
            foreach($lists as $lst)
            {
                foreach($tasks as $tsk)
                {
                    if($lst['id'] == $tsk['project_id'])
                    {
                        if($idPro != $tsk['project_id']) 
                        {
                            $idPro = $tsk['project_id'];
                            $cnt = 0;
                            //$result[0]['idPro'] = $tsk['project_id'];
                        }
                        $result[$idPro][$tsk['id']] = $tsk['name'];
                        $cnt++;
                        if($idPro > 0) 
                        {
                            $result[$idPro]['cnt'] = $cnt;
                            $result[$idPro]['listName'] = $lst['name'];
                            $_SESSION['SQL'] = "cntEachPro";
                        }
                        
                    }
                }                
            }
        break;
    }
    $_SESSION['res'] = $result;
    header("Location: http://ruby.ua/index.php?id=SQLtask");
    exit;
}
?>