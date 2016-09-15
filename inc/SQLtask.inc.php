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
                            <option value='1'>complete</option>
                            <option value='0'>not complete</option>
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
                if($_GET['sort'] === 'asort')   $resCntEcPro = selectCntPro(1);
                if($_GET['sort'] === 'arsort')  $resCntEcPro = selectCntPro(2);                
                if($_GET['sort'] === 'ksort')   $resCntEcPro = selectCntPro(3);                
                if($_GET['sort'] === 'krsort')  $resCntEcPro = selectCntPro(4); 
                //$resCntEcPro = selectCntPro();   // получаем массив данных с количеством проектов
                echo "<table class='cntTask'>";
                echo "<tr><th>List Name
                <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=ksort'></a></span>
                <span class='wn1 wn2'><a class='nav nav8' href='inc/SQLtask.php?sort=krsort'></a></span></th>
                        <th class='tdCntTask'>Count Task
                        <span class='wn1'><a class='nav nav7' href='inc/SQLtask.php?sort=asort'></a></span>
                        <span class='wn1 wn2'><a class='nav nav8' href='inc/SQLtask.php?sort=arsort'></a></span></th>
                        </tr>";
                foreach($resCntEcPro as $res)
                    echo "<tr><td>".$res['name']."</td><td class='tdCntTask'>".$res['cnt']."</td></tr>";
                
                echo "</table></div>";
            }
            if($_SESSION['SQL'] == "dupTsk") // если нужно вывести таблицу задач с дублирующими именами
            {
                $result = selectDoubleTask(); // получаем основной массив данных c дублирующими заданиями
                $c = 1;
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='ProNameDup'>Project Name</th>
                        <th>Task Name</th></tr>";
                if(count($result) > 0)
                {
                    foreach($result as $res)
                    {   echo "<tr><td>".$c."</td><td>".$res['pro']."</td><td>".$res['name']."</td></tr>";  $c ++;   }
                }
                else  echo "<tr><td colspan=3><strong>Tasks is not found!</strong></td></tr>";  
                echo "</table>";
            }
                
           
            if($_SESSION['SQL'] == "Garage") // отображаем задачи, которые совпадают с проектом "Гараж" по имени и статусу
            {
                $cnt = 1;
                $garage = selectGarage();    // получаем основной массив со списком заданий
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='thGar'>Project Name</th>
                        <th class='thGar'>Task Name</th>
                        <th>Status</th></tr>"; 
                if(count($garage) > 0)
                {
                    foreach($garage as $res)
                    {   // определяем сообщения статуса задания
                        $sts = (!empty($res['status'])) ? "<span class='com'>is complete</span>" : "<span class='ncom'>is not complete</span>";
                        echo "<tr><td>".$cnt."</td><td>".$res['pro']."</td><td>".$res['name']."</td><td>".$sts."</td></tr>";  
                        $cnt ++;   
                    }
                }
                else  echo "<tr><td colspan=4><strong>Tasks is not found!</strong></td></tr>"; 
                echo "</table>";
                
            }
            
            if($_SESSION['SQL'] == "statuses") // отображаем задачи соответствующего статуса
            {
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='thGar'>Project Name</th>
                        <th class='thGar'>Task Name</th>
                        <th>Status</th></tr>";
                $sts = (int) abs($_SESSION['sts']);
                $status = selectSts($sts);  // получаем основной массив данных со статусами
                $cnt = 1;
                
                if(count($status) > 0)
                {
                    foreach ($status as $res)
                    {
                        if($res['status'] > 0) $s = "<span class='com'>is complete</span>";
                        if($res['status'] == 0 or $res['status'] == NULL) $s = "<span class='ncom'>is not complete</span>";
                        echo "<tr><td>".$cnt."</td><td>".$res['pro']."</td><td>".$res['name']."</td><td>".$s."</td></tr>";
                        $cnt ++;
                    }
                }
                else  echo "<tr><td colspan=4><strong>Tasks is not found!</strong></td></tr>"; 
                echo "</table>";
            }
            if($_SESSION['SQL'] == "beginLetter") // отображаем задачи, имена которых начинаются на определнную букву
            {
                $Let = clear($_SESSION['let']); // получаем заданную букву, на которую будем искать задания
                $letter = selectLetter1($Let); //получаем массив с задачами, которые начинаются на заданную букву
                $cnt = 1;
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='ProNameDup'>Project Name</th>
                        <th>Task Name</th></tr>";
                
                if(count($letter) > 0)
                {
                    foreach($letter as $res)
                    { echo "<tr><td>".$cnt."</td><td>".$res['pro']."</td><td>".$res['name']."</td></tr>"; $cnt ++;}
                }
                else echo "<tr><td colspan=3><strong>Tasks on the letter '".$_SESSION['let']."' is not found.</strong></td></tr>"; 
                echo "</table>";
            }
            
            /*
            if($_SESSION['SQL'] == "beginLetter") // отображаем задачи, имена которых начинаются на определнную букву
            {
                $Let = clear($_SESSION['let']); // получаем заданную букву, на которую будем искать задания
                $letter = selectLetter(1, $Let); //получаем массив с задачами, которые начинаются на заданную букву
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th class='ProNameDup'>Project Name</th>
                        <th>Task Name</th></tr>";
                if(count($letter) > 0) // если есть задачи на заданную букву
                {
                    $cnt = 0;
                    foreach($letter as $let)
                    {
                        foreach($list as $lst)
                        {
                            if($lst['id'] == $let['project_id'])
                            {
                                $cnt ++;
                                echo "<tr><td>".$cnt."</td><td>".$lst['name']."</td><td>".$let['name']."</td></tr>";
                            }
                        }
                    }
                }
                else echo "<tr><td colspan=3><strong>Tasks on the letter '".$_SESSION['let']."' is not found.</strong></td></tr>"; 
                echo "</table>";
            }
            if($_SESSION['SQL'] == "middleLetter") // отображаем задачи, имена которых начинаются на определнную букву
            {
                $cntTsk = 0;
                $Let = clear($_SESSION['let']); // получаем заданную букву, на которую будем искать задания
                $letter = selectLetter(0, $Let); //получаем массив с проектами, названия которых содержит заданную букву
                echo "<table class='cntTask'>";
                echo "<tr><th class='numDup'>#</th>";
                echo "<th>Project Name</th>
                        <th class='numDup'>Count Tasks</th></tr>";
                if(count($letter) > 0) // если есть проекты с заданной буквой
                {
                    $cnt = 0;
                    foreach($letter as $let)
                    {
                        $cnt ++;
                        $cntTsk = 0;
                        foreach($task as $tsk)
                        {
                            if($let['id'] == $tsk['project_id']) $cntTsk ++;
                        }
                        echo "<tr><td>".$cnt."</td><td>".$let['name']."</td><td>".$cntTsk."</td></tr>";
                    }
                }
                else echo "<tr><td colspan=3><strong>Projects with the letter '".$_SESSION['let']."' is not found.</strong></td></tr>";
                echo "</table>";
            }
        }
       
       
       //if($_GET['id'] != "SQLtask" or ($_GET['id'] == 'SQLtask' and count($_GET) > 1)) unset($_SESSION['SQL']); //удаляем переменную за ненадобностью
       if($_GET['id'] != "SQLtask") unset($_SESSION['SQL']); //удаляем переменную за ненадобностью
            */
    }
?>