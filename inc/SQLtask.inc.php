<?php
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
                <span class='wn1 wn2'><a class='nav nav8' href='inc/SQLtask.php?sort=krsort'></a></span></th>
                        <th class='tdCntTask'>Count Task
                        <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=asort'></a></span>
                        <span class='wn1 wn2'><a class='nav nav8' href='inc/SQLtask.php?sort=arsort'></a></span></th>
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
                if($_GET['sort'] === 'asort') asort($resCntEcPro);
                if($_GET['sort'] === 'arsort') arsort($resCntEcPro);                
                if($_GET['sort'] === 'ksort') ksort($resCntEcPro);                
                if($_GET['sort'] === 'krsort') krsort($resCntEcPro);                
                foreach($resCntEcPro as $key => $rst)
                    echo "<tr><td>".$key."</td><td class='tdCntTask'>".$rst."</td></tr>";
                echo "</table>";
                //unset($_SESSION['SQL']);
                echo "</div>";
            }
            if($_SESSION['SQL'] == "dupTsk") // если нужно вывести таблицу задач с дублирующими именами
            {
                $name = NULL;
                $num = NULL;
                $dupl = array();
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='ProNameDup'>Project Name
                <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=ksortDup'></a></span>
                <span class='wn1 wn2'><a class='nav nav8' href='inc/SQLtask.php?sort=krsortDup'></a></span></th>
                        <th>Task Name</th></tr>";
     
                $cntDup = 0;
                if($dubles = dubles())
                {
                    foreach($task as $tsk)
                    {
                        foreach($dubles as $dls)
                        {
                            if($tsk['name'] == $dls['name'])
                            {
                                foreach($list as $lst)
                                {
                                    if($tsk['project_id'] == $lst['id'])
                                    {
                                        $cntDup ++;
                                        $dupl[$lst['name']." (".$cntDup.")"] = $tsk['name'];
                                        break; 
                                    }
                                }
                            }
                        }
                    }
                }
                
                
                if(count($dupl) > 0) // если найдены совпадения, заполняем таблицу
                {
                    if($_GET['sort'] === 'ksortDup') ksort($dupl);                
                    if($_GET['sort'] === 'krsortDup') krsort($dupl); 
                    foreach($dupl as $key => $dup)
                    {
                        $num ++;
                        echo "<tr><td>".$num."</td><td>".$key."</td><td>".$dup."</td></tr>";
                    }
                }
                // если же совпадения не найдены, то отображаем соответствующее сообщение:
                else echo "<tr><td colspan=3><strong>Matches not found in the task name</strong></td></tr>";
                echo "</table></div>";
            }
            if($_SESSION['SQL'] == "Garage")
            {
                $idGar = 0;
                $garage = 0;
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='thGar'>Project Name</th>
                        <th class='thGar'>Task Name</th>
                        <th>Status</th></tr>";
                foreach ($list as $lst)
                {
                    if($lst['name'] == "Garage")
                    {   $idGar = $lst['id']; break;    }
                }
                if($idGar > 0) $garage = selectTasks($idGar);
                else    echo "<tr><td colspan=4><strong>Project 'Garage' is not found!</strong></td></tr>";
                
                if(is_array($garage))
                {
                    $resultTsk = array();
                    $resultSts = array();
                    $cnt = 0;
                    foreach($task as $tsk)
                    {
                        if($idGar == $tsk['project_id']) continue;
                        else
                        {
                            foreach($garage as $gar)
                            {
                                if($gar['name'] == $tsk['name'])
                                {
                                    if((!empty($gar['status']) and !empty($tsk['status'])) or (empty($gar['status']) and empty($tsk['status'])))
                                    {
                                        foreach($list as $lst)
                                        {
                                            if($lst['id'] == $tsk['project_id'])
                                            {
                                                $cnt ++;
                                                $result[$tsk['id']]['name'] = $tsk['name'];
                                                $result[$tsk['id']]['status'] = $tsk['status'];
                                                $result[$tsk['id']]['pro'] = $lst['name'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    
                    if(count($result) > 0)
                    {
                        echo "<tr><td colspan=4><strong>Project's 'Garage' matches have next tasks:</strong></td></tr>";
                        $cnt = 0;
                        foreach($result as $res)
                        {
                            $cnt ++;
                            $sts = (!empty($res['status'])) ? "<span class='com'>is complete</span>" : "<span class='ncom'>is not complete</span>";
                            echo "<tr><td>".$cnt."</td><td>".$res['pro']."</td><td>".$res['name']."</td><td>".$sts."</td></tr>";
                        }
                    }
                }
                
                
                echo "</table>";
            }
        }
       
       
       if($_GET['id'] != "SQLtask" or ($_GET['id'] == 'SQLtask' and count($_GET) > 1)) unset($_SESSION['SQL']); //удаляем переменную за ненадобностью
?>