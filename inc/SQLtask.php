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
            $listName = "";
            $gen = "";

            /*if(countRes())
            {
                foreach($lists as $lst)
                {
                    foreach($tasks as $tsk)
                    {
                        if($lst['id'] == $tsk['project_id'])
                        {
                            if($idPro != $tsk['project_id'])
                            {
                                $cnt = 1;
                                $result[$lst['name']] = $cnt;
                                $idPro = $tsk['project_id'];
                                continue;
                            }
                            $cnt ++;
                            $result[$lst['name']] = $cnt;
                        }
                    }
                }
                foreach($result as $key => $res)
                {   if(countRes($key, $res)) continue;  }
                
                if ($gen = countRes(-5, -5))
                {
                    if (countRes(-1))
                    {
                        $_SESSION['res'] = $gen;
                        $_SESSION['SQL'] = "cntEachPro";
                        header("Location: http://ruby.ua/index.php?id=SQLtask");
                        exit;
                    }
                    
                }
                */
            /*foreach($lists as $lst)
                {
                    foreach($tasks as $tsk)
                    {
                        if($lst['id'] == $tsk['project_id'])
                        {
                            if($idPro != $tsk['project_id'])
                            {
                                $cnt = 1;
                                $result[$lst['name']] = $cnt;
                                $idPro = $tsk['project_id'];
                                continue;
                            }
                            $cnt ++;
                            $result[$lst['name']] = $cnt;
                        }
                    }
                }
            /*
            echo "<pre>";
            print_r($result);
            //print_r($_SESSION['SQL2']);
            echo "</pre>";
            exit;
            if($gen = countRes2($result))
            {
                
                $_SESSION['res2'] = $gen;
                $_SESSION['SQL2'] = "cntEachPro";
                //unset($_SESSION['res'], $_SESSION['SQL']);
                header("Location: http://ruby.ua/index.php?id=SQLtask");
            }
            */
            $_SESSION['SQL2'] = "cntEachPro";
            
        break;
    }
    header("Location: http://ruby.ua/index.php?id=SQLtask");
    exit;
}
?>