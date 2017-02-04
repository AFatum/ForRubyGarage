<?php
class Oper
{
  //const HOST = LINK_HOST;
  const HOST = "newIndex.php";
  public $db;
  public $autoForm;
  public $autoErr;
  public $user;
  public $allTasks;

  // вносим в конструктор класс базы данных
  function __construct(mysqli $db)  
  { // ** - вносим первоначальные стандартные параметры: 
    $this->db = $db;
    
    $this->user = $_SESSION['control_id'] ?: NULL;
    $this->autoErr = $_SESSION['autoErr'] ?: NULL;
    // *1 - отображаем форму авторизации, если пользоваетль не авторизован
    if(!$_SESSION['control'])
    {    
      if($_SESSION['formReg']) $this->autoForm = $this->formReg();
      else $this->autoForm = $this->formAuto();
      if($_SESSION['control_id']) unset($_SESSION['control_id']);
    }
    // *2 - если же пользователь авторизован, отображаем ссылку на выход
    else
    {
      $this->autoForm = "<p class='login'>Your user's email: ".$_SESSION['control']." - <a href='".self::HOST."?log=out' title='logout'>Log out</a></p>";
      $this->allTasks = $this->getSQL("getAllTasks");
    }
  }
  
  //////////--МЕТОДЫ ПО РАБОТЕ С БД И С ПОЛЬЗОВАТЕЛЯМИ--//////////////////////***********
  
  // подгототавливаем строку к внесению в бд
  function clear($data)
  {
    $Data = (!is_string($data)) ? (string) $data : $data;   
    return $this->db->real_escape_string(trim(strip_tags($Data)));
  }
    
  function newSQL ($name, $pro=0) // ** - Отправляем данные в БД
  { // *0 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при добавлении задания или проекта в БД!"); return false; }
    else $user = $this->user;
    
    // *1 - подготавливаем входящие данные
    $name = $this->clear($name);
    $pro = (int) abs($pro);
    
    // *2 - формируем запрос
    if($pro == 0) // *2.1 - создаём новый лист заданий - проект 
    {
      $sql = "INSERT INTO projects(name, user_id) VALUES (?, ?)";
      $err = "Ошибка подготовленного запроса при создании листа: ";
    }
    else // *2.2 - создаём новое задание из выбранного проекта
    {
      $sql = "INSERT INTO tasks(name, project_id, user_id) VALUES (?, ?, ?)";
      $err = "Ошибка подготовленного запроса при создании задания: ";
    }
    // *3 - создаём подготовленный запрос 
    if(!$stmt = $this->db->prepare($sql))
      // *3.1 - если неудача, кидаем исключение
      { throw new Exception($err.$stmt->errno." - ".$stmt->error); return false;  }
    
      // *3.2 - задаём параметры в подготовленный запрос
    if($pro == 0) // *3.2.1 - задаём параметры подготовленного запроса и текст ошибки
    {
      $err = "Какая-то ошибка в исполнении подготовленного запроса при создании нового листа: ";
      $stmt->bind_param('si', $name, $user);
    }
    else
    {
      $err = "Какая-то ошибка в исполнении подготовленного запроса при создании нового задания: ";
      $stmt->bind_param('sii', $name, $pro, $user);
    }
    
    // *3.3 - исполняем подготовленный запрос
    if(!$stmt->execute())
      { throw new Exception($err.$stmt->errno." - ".$stmt->error); return false; }
    else // *4 - возвращаем true если внесение данных прошло успешно
      { $stmt->close(); return true; }    
  } // ** - Данные в БД - отправлены!
  
  function getSQL($oper, $order=1) // ** - Получаем данные из БД по заданным параметрам
  {
    // *1 - Подготавливаем входящий параметр
    if(!is_string($oper)) $oper = (string) $oper;
    if(!is_int($order) and !is_string($order)) $order = 1;
    if(is_string($order) and strlen($order) > 1) $order = $order{0};
    // *1.1 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при получении данных из БД!"); return false; }
    else $user = $this->user;
    
    // *2 - Выбираем нужный для нас запрос в БД
    switch($oper)
    {
      case "getList": // *2.1 - получаем список листов заданий
        $sql = "SELECT id, name
			FROM projects
            WHERE user_id = ".$user." 
			ORDER BY id";
        $err = "Ошибка при выборе данных списка листов задания ";
      break;
        
      case "getAllTasks": // *2.2 - получаем список всех заданий из всех списков
        $sql = "SELECT
                t.id,
                t.name, 
                t.status,
                t.user_id,
                p.id AS pro_id,
                p.name AS pro
            FROM tasks as t
                RIGHT JOIN projects as p ON t.project_id = p.id
            WHERE t.user_id = ".$user." 
            ORDER BY pro_id";
        $err = "Ошибка при выборе данных списка всех заданий, из всех листов ";
      break; 
        
      case "getCntPro": // *2.3 - пполучаем список листов (проектов) заданий, отсортированные по количеству либо по имени
        $sql = "SELECT p.name, count(*) as cnt 
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                WHERE t.user_id = ".$user." 
                GROUP BY p.name 
                ";
    // *2.3.1 - определяем метод сортировки, по входящему $order
        switch($order)
        {
            case 1:
                $sql .= "ORDER BY cnt DESC"; break;
            case 2:
                $sql .= "ORDER BY cnt"; break;
            case 3:
                $sql .= "ORDER BY p.name"; break;
            case 4:
                $sql .= "ORDER BY p.name DESC"; break;
            default:
                $sql .= "ORDER BY cnt DESC"; break;
        }
        $err = "Ошибка при выборе данных отсортированного списка листов заданий: ";
      break;
        
      case "getDoubleTask": // *2.4 - получаем список проектов с дублирующими заданиями
        $sql = "SELECT t1.name, p.name as pro
                FROM tasks as t1
                    RIGHT JOIN projects as p ON t1.project_id = p.id
                WHERE EXISTS (SELECT t2.name, count(*) as cnt
                            FROM tasks as t2
                            WHERE t1.name = t2.name 
                            AND t2.user_id = ".$user." 
                            GROUP BY t2.name
                            HAVING cnt > 1
                ) 
                AND t1.user_id = ".$user." 
                ORDER BY t1.name";
        $err = "Ошибка при выборе данных списка проектов с дублирующими заданиями: ";
      break; 
        
      case "get10CompTask": // *2.5 - Выбор проектов с 10 и более выполненных заданий
        $sql = "SELECT p.name, p.id, COUNT(*) as cnt
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                WHERE t.status = 1 
                AND t.user_id = ".$user." 
                GROUP BY p.name
                HAVING cnt > 9
                ORDER BY p.name";
        $err = "Ошибка при выборе данных списка проектов с дублирующими заданиями: ";
      break; 
        
      case "getGarage": // *2.6 - Выбор заданий, которые по названию и по статусу совпадают с проектом 'Garage'
       $sql = "CALL gar(".$user.")";
       $err = "Ошибка при выборе заданий, которые по названию и по статусу совпадают с проектом 'Garage': ";
      break;  
    
      case "getSts": // *2.7 - Выбор данных с выполненными или невыполненными статусами
        $sql = "SELECT p.name as pro, t.name, t.status
	            FROM tasks as t
                RIGHT JOIN projects as p ON t.project_id = p.id
                ";
        // *2.7.1 - Выбор заданий с выполненными статусами
        if($order == 1) $sql .= "WHERE t.status = 1
		                   AND (t.name IS NOT NULL
                                AND t.user_id = ".$user.")
	                       ORDER BY pro";
        // *2.7.2 - Выбор заданий с НЕвыполненными статусами
        else $sql .= "SELECT p.name as pro, t.name, t.status
                        FROM tasks as t
                          RIGHT JOIN projects as p ON t.project_id = p.id
                        WHERE t.user_id = ".$user."
                        AND (t.name IS NOT NULL
                             AND (t.status = 0
                                  OR t.status IS NULL))
                        ORDER BY pro";
       $err = "Ошибка при выборе заданий, с выполненными или невыполненными статусами: ";
      break; 
    
      case "getLetter1": // *2.8 - выбор данных с определённой первой буквой в имени задания
        // *2.8.1 - Если входящий параметр не строка, завершаем ошибкой и отлавливаем исключение
        if(!is_string($order))
        { throw new Exception("Параметр \$order не является строкой!"); return false; }
    
        // *2.8.2 - Если же всё в порядн, тогда формируем запрос с параметром $order
        $sql = "SELECT p.name as pro, t.name
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                WHERE t.name LIKE '".$order."%'
                AND t.user_id = ".$user."
                ORDER BY pro";   
       $err = "Ошибка при выборе заданий, c определенной ПЕРВОЙ буквой в названии: ";
      break; 
    
      case "getLetter2": // *2.9 - выбор данных с определённой буквой в имени задания
        // *2.9.1 - Если входящий параметр не строка, завершаем ошибкой и отлавливаем исключение
        if(!is_string($order))
        { throw new Exception("Параметр \$order не является строкой!"); return false; }
    
        // *2.9.2 - Если же всё в порядн, тогда формируем запрос с параметром $order
        $sql = "SELECT p.name, t.name as tsk, COUNT(*) AS cnt
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                WHERE p.name LIKE '%".$order."%'
                AND t.user_id = ".$user."
                GROUP BY p.name
                ORDER BY cnt";     
       $err = "Ошибка при выборе заданий, c определенной буквой в названии: ";
      break; 
    } // *2* - Нужный запрос для выбора данных из БД - сформирован
    
    // *3 - отправялем запрос для БД, если неудача, отлавливаем исключение
    if(!$result = $this->db->query($sql))
      { throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
    
    // *4 - формируем массив данных результата и возвращаем его
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free(); return $items;
  } // ** - Данные из БД - получены
  
  function updSQL ($id, $idPro, $up=true) // ** - Изменям данные в БД 
  { // *0 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при редактировании данных в БД!"); return false; }
    else $user = $this->user;
    // *1 - формируем запрос в БД, в зависимости от выбранной операции
    if($up === 0 or $up === 1 or is_null($up)) // *1.1 - меняем статус выполнения задания
    {
      // *1.1.1 - фильтруем входящие параметры
      if(!is_int($id))    $id = (int) abs($id);
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      if(!is_int($up))    $up = (int) abs($up);
      // *1.1.2 - устанавливаем изменённые значения статусов
      if($status == 0) $sts = 1;
      if($status == 1) $sts = 0;
      
      // *1.1.3 - формируем запрос в БД
      $sql1 = "UPDATE tasks
		      SET
                status = ".$sts." 
              WHERE id LIKE ".$id." 
              AND (project_id LIKE ".$idPro."
                    AND user_id = ".$user.")";
      $err = "Ошибка при выполнении запроса в изменении статуса задания: "; $st1 = NULL;
    }
    else // *1.2 - меняем порядок заданий
    {
      // *1.2.1 - фильтруем входящие параметры
      if(!is_int($id))    $id = (int) abs($id);
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      if(!is_bool($up))   $up = true;
      // *1.2.2 - устанавливаем изменённые значения id заданий
      $ID1=($up) ? $id1 : $idPro; // устанавливаем значение перемещения вверх или вниз..
      $ID2=($up) ? $idPro : $id1; //.. в зависимости от полученного параметра $up
      
      // *1.2.3 - формируем три запроса на изменение данных
      $sql1 = "UPDATE tasks
                    SET
                        id = 11111
                    WHERE
                        id LIKE ".$ID1."
                    AND user_id = ".$user;
        
      $sql2 = "UPDATE tasks
                SET
                    id = ".$ID1." 
                WHERE
                    id LIKE ".$ID2."
                AND user_id = ".$user;

      $sql3 = "UPDATE tasks
                SET
                    id = ".$ID2." 
                WHERE
                    id LIKE 11111
                AND user_id = ".$user;
      $err = "Ошибка при выполнении запроса на изменение порядка задания";
      $st1 = " (этап-1): "; $st2 = " (этап-2): "; $st3 = " (этап-3): ";
    }
    
    // *2 - Выполняем запросы и отлавливаем исключения в случае ошибок
    if(!$result1 = $this->db->query($sql1))
      {  throw new Exception($err.$st1.$this->db->errno." - ".$this->db->error); return false;  }
    else if ($up === 0 or $up === 1) return true; // завершаем ф-цию, т.к. здесь успешно изменен именно статус задания
    
    else
    {
      if(!$result2 = $this->db->query($sql2))
      {  throw new Exception($err.$st2.$this->db->errno." - ".$this->db->error); return false;  }
      else
      {
        if(!$result3 = $this->db->query($sql3))
        {  throw new Exception($err.$st3.$this->db->errno." - ".$this->db->error); return false;  }
        else return true; // успешное изменение порядка заданий
      }
    }
  } // ** - Данные в БД - изменены!
  
  function delSQL ($idPro, $id=0) // ** - Удаляем данные из БД
  { // *0 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при удалении данных из БД!"); return false; }
    else $user = $this->user;
    // *1 - Определяем что будем удалять, лист (проект) либо задание
    if($id == 0) // *1.1 - Удаляем лист заданий - проект
    {
      // *1.1.1 - фильтруем входящие данные
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      
      // *1.1.2 - создаём запрос
      $sql = "DELETE FROM projects
            WHERE id LIKE ".$idPro."
            AND user_id = ".$user;
      $err = "Ошибка при удалении списка задания (проекта): ";
    }
    
    else // *1.2 - Удаляем определённое задание из проекта
    {
      // *1.2.1 - фильтруем входящие параметры
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      if(!is_int($id) and $id != "all") $id = (int) abs($id);
      
      // *1.2.2 - создаём запрос
      if($id == "all")
      {
        $sql = "DELETE FROM tasks
                  WHERE project_id LIKE ".$idPro."
                  AND user_id = ".$user;
        $err = "Ошибка при удалении всех заданий из проекта: ";
      }
      else 
      {
        $sql = "DELETE FROM tasks
                  WHERE id LIKE ".$id." 
                  AND (project_id LIKE ".$idPro."
                        AND user_id = ".$user.")";
        $err = "Ошибка при удалении задания из проекта: ";
      }
    }
    
    // *2 - выполняем запрос
    if(!$result = $this->db->query($sql))
      {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
    else return true;
  } // ** - Данные из БД - удалены!
  
  function rnmSQL ($id, $name, $idPro=0) // ** - переименовывем проекты и задания
  {
    // *1 - фильтруем данные
    $id = (int) abs($id);
    if($idPro > 0) $idPro = (int) abs($idPro);
    $name = $this->clear($name);
    // *1.1 - определяем пользователя
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при переименовании проекта либо задания"); return false; }
    else $user = $this->user;
    // *2.1 - переименовываем лист заданий - проект
    if(!$idPro) 
    {
      $sql = "UPDATE projects
		      SET
                name = '".$name."' 
              WHERE id LIKE ".$id."
              AND user_id = ".$user;
      $err = "Ошибка при переименовании листа заданий - проекта: ";
    }
    else // *2.2 - переименовываем конкретное задание
    {
       $sql = "UPDATE tasks
		      SET
                name = '".$name."' 
              WHERE id LIKE ".$id." 
              AND (project_id LIKE ".$idPro."
                    AND user_id = ".$user.")";
        $err = "Ошибка при переименовании конкретного задания: ";
    }
    // *3 - исполняем запрос:
    if(!$result = $this->db->query($sql))
        {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
    else header("Location: ".self::HOST); return true;
  } // ** - проект или задание - переименованы!
  
  function userExists($login) // ** - Проверяем уже зарегистрированного пользователя
  {
    // *1 - фильтруем входящие данные
    if(!is_string($login)) $login = (string) $login;
    
    // *2 - Создаём запрос
    $sql = "SELECT id, email
            FROM users
            ORDER BY id";
    $err = "Ошибка при получении данных о зарегистрированных пользователей: ";
    
    // *3 - Получаем список всех пользователей из запроса
    if(!$result = $this->db->query($sql))
      {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }

	$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // *4 - Проверяем есть ли пользователь в БД
    foreach($items as $data)
        if($data['email'] === $login) return $login;
        // если пользователь найден - возвращаем true
    return false; // если же пользователя нет - возвращаем false
  } // ** - Проверка на наличие зарегистрированных пользователей - завершена.
 
  function saveUser($login, $hash) // ** - Сохраняем нового пользователя в БД
  {
    // *1 - Фильтруем данные
    $login = $this->clear($login);
    $hash = $this->clear($hash);
    
    // *2 - Создаём запрос
    $sql = "INSERT INTO users(email, pass) VALUES (?, ?)";
    $err = "Ошибка при создании подготовительного запроса, при сохранении нового пользователя: ";
    // *3 - создаём подготовленный запрос 
    if(!$stmt = $this->db->prepare($sql))
      // *3.1 - если неудача, кидаем исключение
      { throw new Exception($err.$stmt->errno." - ".$stmt->error); return false;  }
    
    // *4 - Задаём параметры подготовительного запроса
    $stmt->bind_param("ss", $login, $hash);
    $err = "Ошибка при исполнении подготовительного запроса, при сохранении нового пользователя: ";
    
    // *5 - Исполяем подготовительный запрос, если неудача, отлавливаем исключение
    if(!$stmt->execute())
      { throw new Exception($err.$stmt->errno." - ".$stmt->error); return false; }
    return true; // если же все прошло успешно, возвращаем true
  } // ** - Данные нового пользователя внесены в БД успешно!
  
  function userControl ($login, $pass) // ** - проверка правильного ввода логина/пароля
  {
    // *1 - фильтруем данные
    $login = trim($login);
    $pass = trim($pass);
    
    // *2 - Создаём запрос
    $sql = "SELECT id, email, pass
            FROM users
            ORDER BY id;";
    $err = "Ошибка при выполнении запроса на проверку логина/пароля пользователя: ";
    
    // *3 - отправялем запрос для БД, если неудача, отлавливаем исключение
    if(!$result = $this->db->query($sql))
      { throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
    
    // *4 - формируем массив данных результата и возвращаем его
    $items = $result->fetch_all(MYSQLI_ASSOC); $result->free();
    
    // *5 - сверяем данные полученные из БД с входящими данными
    foreach($items as $data)
    {
        if($login !== $data['email']) continue; // *5.1 - если логин не совпадает - пропускаем
        // *5.2 - если пароль верифицирован - возвращаем логин пользователя
        //else if(password_verify($pass, trim($data['pass']))) return trim($data['email']);
        else if(password_verify($pass, trim($data['pass']))) return $data;
    }
    return false; // проверка не пройдера
  } // ** - проверка логина/пароля завершена.

  //////////--МЕТОДЫ ПРЕДСТАВЛЕНИЯ--//////////////////////
  
  // ************** Методы авторизации ***************************************************
  // ** - отображаем нужную форму с нужной ошибкой
  function autoLocation($err=NULL, $reg=false)
  {
    // *1 - устанавливаем флаг контроля, пока не будет пройдена авторизация
    if($_SESSION['control']) unset($_SESSION['control']);
    // *2 - устанавливаем параметры ошибки, если он есть

    $_SESSION['autoErr'] = ($err!=NULL) ? $err : NULL;
    
    // *3 - устанавливаем параметр нужной для нас формы:
    if($reg) $_SESSION['formReg'] = true;
    else if($_SESSION['formReg']) unset($_SESSION['formReg']);
    
    // *4 - перенаправляем обратно, на главную страницу с обновлёнными параметрами отображения формы
    //header("Refresh: 1");
    header("Location: ".self::HOST); 
    return true;
  } // ** - параметры формы и ошибки установлены успешно
  
  function formAuto() // ** - формируем форму авторизации
  { // *1 - формируем html-код для формы
    $form = "<div class='genMod autoGen'>
           <div class='listName autoTitle'>Autorization</div>
            <div class='newListName'>
                <form method='post' action=''>
                    <input class='newListNameInputTxt autorization' type='text' placeholder='Please enter your email' name='email'><br>
                    <input class='newListNameInputTxt autorization' type='password' placeholder='Please enter your password' name='pass'><br>
                    <input class='AddTaskBut' type='submit' value='login'>
                </form>
            </div>";
    if($this->autoErr) $form .= $this->autoErr; // *1.1 - отображаем ошибки в форме, если они есть
    // *1.2 - добавляем окончания формы:
    $form .= "<a href='".self::HOST."?reg=1' title='Registration'>Registration</a></div>";
    return $form;
  } // ** - форма авторизации сформирована 
  
  function formReg() // ** - формируем форму регистрации
  {
    $form = "<div class='genMod autoGen'>
              <div class='listName autoTitle'>Registration</div>
              <div class='newListName'>
                  <form method='post' action=''>
                      <input class='newListNameInputTxt autorization' type='text' placeholder='Please enter your email' name='r_email'><br>
                      <input class='newListNameInputTxt autorization' type='password' placeholder='Please enter your password' name='r_pass1'><br>
                      <input class='newListNameInputTxt autorization' type='password' placeholder='Please enter your password again' name='r_pass2'><br>
                      <input class='AddTaskBut' type='submit' value='ok'>
                  </form></div>";
    if($this->autoErr) $form .= $this->autoErr; // *1.1 - отображаем ошибки в форме, если они есть
    
    // *1.2 - добавляем окончания формы:
    $form .= "<a href='".self::HOST."?reg=2' title='Login'>Login</a></div>";
    return $form;
  } // ** - форма регистрации сформирована

  function autorization() // ** - обрабатываем данные из формы авторизации
  {
    if(!empty($_POST['email']) and !empty($_POST['pass'])) // *1.1 - если все поля заполнены...
    {
      $login = strip_tags($_POST['email']);
      $pw = strip_tags($_POST['pass']);
      // *1.1.1 - верифицируем переданные данные
      $res = $this->userControl($login, $pw);

      if(!$res) // *2.1 - если верификация не пройдена
      {
        $err = "<p class='user_er'>You have entered an invalid username or password</p>";
        return $this->autoLocation($err);
      }
      else // *1.1.2 - если верификация таки пройдена, устанавливаем сессионные переменные
      {
        $_SESSION['control'] = trim($res['email']);
        $_SESSION['control_id'] = (int) abs($res['id']);
        header("Location: ".self::HOST);
        return true;
      }     
    }
    
    // *1.2 - если не все поля заполнены, из формы авторизации...
    if((!empty($_POST['email']) and empty($_POST['pass'])) or (empty($_POST['email']) and !empty($_POST['pass'])))
    {
      $err = "<p class='user_er'>Please complete all fields.</p>";
      return $this->autoLocation($err);
    }
    
  } // ** - параметры из формы авторизации обработаны
  
  function registration() // ** - обрабатываем данные из формы регистрации
  {
    // *1 - если заполнены все поля то проверяем данные
    if(!empty($_POST['r_email']) and !empty($_POST['r_pass1']) and !empty($_POST['r_pass2']))
    {
      // *1.1 -  если поля паролей на совпадают - нужно отобразить сообщение об этом
      if($_POST['r_pass1'] != $_POST['r_pass2'])
      {
        $err = "<span class='pass'>Your data of passwod shoud coincide twice</span>";
        return $this->autoLocation($err, true);
      }
      // *1.2 -  если поля паролей таки совпадают - обрабатываем данные дальше...
      else
      {
        // *1.2.1 - фильтруем данные
        $login = $this->clear($_POST['r_email']) ?: $login;
        // *1.2.2 - если пользователя с таким же email нет...
        if(!$this->userExists($login))
        {
          //$pass = $this->clear($_POST['r_pass1']) ?: $pass;
          $hash = trim(password_hash($_POST['r_pass1'], PASSWORD_BCRYPT)); // хешируем пароль
          // *1.2.2.1 - если внесение нового пользователя прошло успешно, переводим на форму авторизации, с замечаниями
          if($this->saveUser($login, $hash))
          {
            $err = "<p class='user'>User width email: '".$login."' was created successfully. You can login.</p>";
            return $this->autoLocation($err);
          }
          // *1.2.2.2 - если внесение нового пользователя произошла ошибка, то перенаправляем обратно на форму реистрации, с замечаниями
          else
          {
            $err = "<p class='user_er'>Error User registration: ".$this->db->error."</p>";
            return $this->autoLocation($err, true);
          }
        }
        // *1.2.3 - если пользователя с таким же email таки есть...
        else
        {
          $err = "<p class='user_er'>Error User registration: ".$this->db->error."</p>";
          return $this->autoLocation($err, true);
        } 
      }
    }
    // *2 - если же не все поля заполнены, отображаем ошибку
    else
    {
      $err = "<br><span class='pass'>Please complete all fields</span>";;
      return $this->autoLocation($err, true);
    }
  } // ** - параметры из формы регистрации обработаны
  
  // ************** Адаптеры, для обработки post- и get-параметров *************************
  function postAdapter() // ** - адаптер для обработки post-параметров
  {
    if(!$_SESSION['control']) // ** - если пользователь не авторизован
    {
      // *1 - обрабатываем параметры авторизации  
      if($_POST['email'] or $_POST['pass']) return $this->autorization(); 

      // *2 - обработка параметров из форммы регистрации
      if($_POST['r_email'] or $_POST['r_pass1'] or $_POST['r_pass2']) return $this->registration();
    } // ** - обработка данных авторизвации - завершено
    
    else // ** - обрабатываем данные авторизированного пользователя
    {
      if($_POST['oper'] == "renameList") // ** - переименовываем лист заданий
      { // *1 - фильтруем данные
        // *1.1 - если поступили не все данные, возвращаем false
        if(is_null($_POST['newList']) or !$_POST['idPro']) return false;
        // *1.2 - если все данные получены - работаем дальше
        if($this->rnmSQL($_POST['idPro'], $_POST['newList']))
          { header("Location: ".self::HOST); return true; }
        else return false;
      } // ** - лист заданий - переименован!
      
      if($_POST['oper'] == "renameTask") // ** - переименовываем конкретное задание
      { // *1 - фильтруем данные
        // *1.1 - если поступили не все данные, возвращаем false
        if(is_null($_POST['newName']) or !$_POST['idPro'] or !$_POST['idTsk']) return false;
        // *1.2 - если все данные получены - работаем дальше
        if($this->rnmSQL($_POST['idTsk'], $_POST['newName'], $_POST['idPro']))
          { header("Location: ".self::HOST); return true; }
        else return false;
      } // ** - конкретное задание - переименовано!  
      
      if($_POST['oper'] == "newTask") // ** - добавляем новое задание
      {
        if(!$_POST['newTask'] or !$_POST['idList']) return false;
        if($this->newSQL($_POST['newTask'], $_POST['idList']))
          { header("Location: ".self::HOST); return true; }
        else return false;
      } // ** - новое задание - добавлено!
      
    } // ** - данные авторизированного пользователя - обработаны!
  } // ** - post-параметры - адаптированы!
  
  function getAdapter() // ** - адаптер для обработки get-параметров
  {
    if(!$_SESSION['control']) // ** - если пользователь не авторизован
    {
      if($_GET['reg'] == 1) // *1 - отображаем форму регистрации нового пользователя
      {
        if($_SESSION['control'])  unset($_SESSION['control']);
        if($_SESSION['autoErr'])  unset($_SESSION['autoErr']);
        $_SESSION['formReg'] = true;
        header("Location: ".self::HOST); 
        return true;
      }

      if($_GET['reg'] == 2) // *2 - обратно, отображаем форму авторизации нового пользователя
      {
        if($_SESSION['control'])    unset($_SESSION['control']);
        if($_SESSION['autoErr'])    unset($_SESSION['autoErr']);
        if($_SESSION['formReg'])    unset($_SESSION['formReg']);
        header("Location: ".self::HOST); 
        return true;
      }
    } // ** - обработка данных авторизвации - завершено
    
    else // ** - обрабатываем данные авторизированного пользователя
    {
      if($_GET['log'] == 'out')    // вылогиниваемся
        { session_destroy(); header("Location: ".self::HOST); }
      
      if($_GET['deleteList']) // *3 - удаляем лист задание
      {      
        if($this->delSQL($_GET['deleteList'], "all") and ($this->delSQL($_GET['deleteList'])))
           {  header("Location: ".self::HOST.$_GET['link']); return true; }
        else return false;
      } // *3* - лист заданий - удалён!
           
      if($_GET['status'] == 1) // *4 - меняем статус задания
      {        
        if(!$_GET['updl'] or !$_GET['updt']) return false;
        if($this->updSQL($_GET['updt'], $_GET['updl'], $_GET['sts']))
             {  header("Location: ".self::HOST.$_GET['link']); return true; }
        else return false;
      }
      
      if($_GET['order']) // *5 - меняем порядок задания
      {
        if(!$_GET['updl'] or !$_GET['updt']) return false;
        if(!is_array($this->allTasks) or empty($this->allTasks)) return false;

        // *5.1 - если нужно передвинуть задание выше
        if($_GET['order'] == "up")
        {
          // *5.1.1 - определяем id задание которое выше, если такого нет, то возвращаем false
          if(!$next = $this->more($_GET['updt'], $_GET['updl'])) return false;
          // *5.1.2 - передвигаем задание выше
          if($this->updSQL($_GET['updt'], $next))
            {  header("Location: ".self::HOST.$_GET['link']); return true; }
          else return false;
        }
        // *5.2 - если нужно передвинуть задание НИЖЕ
        if($_GET['order'] == "down")
        {
          // *5.2.1 - определяем id задание которое ниже, если такого нет, то возвращаем false
          if(!$next = $this->more($_GET['updt'], $_GET['updl'], false)) return false;
          // *5.2.2 - передвигаем задание НИЖЕ
          if($this->updSQL($_GET['updt'], $next, false))
            {  header("Location: ".self::HOST.$_GET['link']); return true; }
          else return false;
        }
        
      }
    } // ** - данные авторизированного пользователя - обработаны!
  } // get-параметры - адаптированы  
  
  // ************** Методы формирования списка заданий ************************************
  
  function showPro() // ** - Отображаем список проектов с заданиями
  {
    // *1 - фильтруем данные
    if(!$this->user or $this->user != $_SESSION['control_id'])
      $this->user = $_SESSION['control_id'];
    
    // *2 - получаем список заданий и проектов из БД
    //$arrTsk = $this->getSQL("getAllTasks");
    // *2.1 - если пришёл пустой архив - отображаем сообщение про создание нового листа заданий
    if(!is_array($this->allTasks) or empty($this->allTasks)) 
    { 
      //throw new Exception("<p>Ошибка получения данных из БД, при получении полного списка заданий</p>"); 
      //return false; 
      echo "<p>Please create new List</p>";
    }
    else // *2.2 - если у пользователя уже есть списки заданий, отображаем их
    {
      // *2.2.1 - устанавливаем первоначальные параметры для отображения списка
      $po = 0; $ts = 0;
      // *2.2.2 - выводи списки заданий
      foreach($this->allTasks as $p)
      {
        if($po != $p['pro_id']) // *2.2.2.1 - если есть новый список, отображаем его
        {
          if($po > 0) echo "</table></div>";  // *2.2.2.2 - закрываем таблицу и основной div
          $po = $p['pro_id'];                 // *2.2.2.3 - обновляем индекс номера листа задания
          $ts = 1;                            // *2.2.2.4 - устанавливаем первый индекс задания
          // *2.2.2.5 - задаём форму переименования листа заданий, или просто его имя
          $nameList = ($_GET['renameList'] == $p['pro_id']) ? $this->reName($p['pro_id']) : $p['pro'];
          // *2.2.2.6 - вываодим фрейм проекта со ссылкой добавления нового задания
          echo $this->headerList($p['pro_id'], $nameList);
        }
        // *2.2.3 - если в проекте уже есть задания, отображаем их
        if($po == $p['pro_id'] and !empty($p['id'])) 
        { // *2.2.3.1 - определеяем стили для оформления статусов заданий
          if($p['status'] == 0) {   $stlTr = NULL;  $stlTr2 = NULL; $nav="nav5";    }
          else {   $stlTr = " class='statusTrue'";  $stlTr2 = " statusTrue"; $nav="nav6";  }
          // *2.2.3.2 - задаём форму переименования конкретного задания, или просто его имя
          $nameTask = ($_GET['updl'] == $p['pro_id'] and $_GET['updt'] == $p['id']) 
            ? $this->reName($p['id'], $p['pro_id'], $stlTr) : "<td".$stlTr.">".$p['name']."</td>";;
          // *2.2.3.3 - создаём строки заданий в таблице
          echo $this->tableTasks($p['id'], $p['pro_id'], $p['status'], $nav, $stlTr2, $nameTask);
        }
      } // конец основного foreach
      echo "</table></div>";
    } // *2.2* - списки заданий - отображены!
    
    
  }
  
  function reName($id, $idPro=null, $style=null) // ** - форма переименования листа/задания
  { // *1 - фильтруем данные
    if(!is_int($id)) $id = (int) abs($id);
    if($idPro and !is_int($id)) $idPro = (int) abs($idPro);
    if($style and !is_string($style)) $style = (string) $style;
    
    // *2 - создаём форму для переименования листа
    if($id and !$idPro)
    {
      $name = "<form action='' method='post'>
                <input class='newListNameTxt' type='text' name='newList' placeholder='please enter new project name'>
                <input type='hidden' name='idPro' value='".$id."'>
                <input type='hidden' name='oper' value='renameList'>
                <input class='AddTaskBut updateList' type='submit' value='update'></form>";
    }
    
    // *3 - создаём форму для переименования задания
    if($id and $idPro)
    {
      $name = "<td".$style.">  <form action='' method='post'>
                  <input class='newTaskNameTxt' type='text' name='newName' placeholder='please enter new task name'>
                  <input type='hidden' name='idTsk' value='".$id."'>
                  <input type='hidden' name='idPro' value='".$idPro."'>
                  <input type='hidden' name='oper' value='renameTask'>
                  <input class='AddTaskBut update' type='submit' value='update'></form></td>";
    }
    return $name;
  } // ** - форма переименования листа/задания - создана
  
  // ** - формируем ссылку переименования проектов/заданий или..
  // .. для отображения форм "add2list", "SQLtask"
  function smartLink($id=0, $idPro=0)
  { // *0 - фильтруем данные
    if($id and !is_int($id)) $id = (int) abs($id); 
    if($idPro and !is_int($idPro)) $idPro = (int) abs($idPro); 
    // *1 - формируем ссылку для переименования листа заданий
    if($id and !$idPro)
    { // *1.1 - формируем ссылку, если уже есть параметры GET и нет параметра $_GET['renameList']
      if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['renameList']))
          $link = "href='".self::HOST.$_SERVER['REQUEST_URI']."&renameList=".$id;
      // *1.2 - формируем ссылку, если нет параметров GET и есть параметр $_GET['renameList']
      else if(!empty($_GET['renameList'])) $link = "href='".self::HOST.$_SERVER['REQUEST_URI'];
      // *1.3 - формируем ссылку, если нет параметров GET и нет параметра $_GET['renameList']
      else $link = "href='".self::HOST."?renameList=".$id;
    }
    
    // *2 - формируем ссылку для переименования задания
    if($id and $idPro)
    { // *2.1 - формируем ссылку, если уже есть параметры GET и нет параметров $_GET['updl'] и $_GET['updt']
      if(!empty($_SERVER['QUERY_STRING']) and empty($_GET['updl']) and empty($_GET['updt']))
          $link = "href='".self::HOST.$_SERVER['REQUEST_URI']."&updl=".$idPro."&updt=".$id;
      // *2.2 - формируем ссылку, если нет параметров GET и есть параметры $_GET['updl'] и $_GET['updt']
      else if(!empty($_GET['updl']) and !empty($_GET['updt'])) $link = "href='".self::HOST.$_SERVER['REQUEST_URI'];
      // *2.3 - формируем ссылку, если нет параметров GET и нет параметров $_GET['updl'] и $_GET['updt']
      else $link = "href='".self::HOST."?updl=".$idPro."&updt=".$id;
    }
    
    // *3 - формируем ссылку для отображения формы "add2list"
    if($id == "add2list")
    { // *3.1 - формируем ссылку, если уже есть параметры GET и параметр $_GET['id'] не равен "add2list"
      if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "add2list")
          $link = "href='".self::HOST.$_SERVER['REQUEST_URI']."&id=add2list'";
      // *3.2 - формируем ссылку, если нет параметров GET и параметр $_GET['id'] таки равен "add2list"
      else $link = "href='".self::HOST."?id=add2list'";
    }
    
     // *4 - формируем ссылку для отображения формы "SQLtask"
    if($id == "SQLtask")
    { // *4.1 - формируем ссылку, если уже есть параметры GET и параметр $_GET['id'] не равен "SQLtask"
      if(!empty($_SERVER['QUERY_STRING']) and $_GET['id'] != "SQLtask")
            $link = "href='".self::HOST.$_SERVER['REQUEST_URI']."&id=SQLtask'";
      // *4.2 - формируем ссылку, если нет параметров GET и параметр $_GET['id'] таки равен "SQLtask"
      else $link = "href='".self::HOST."?id=SQLtask'";
    } 
    // *5 - возвращаем итоговую ссылку
    return $link;
  } // ** - ссылка сформирована!
  
  // ** - формируем шапку проекта со ссылкой добавления нового задания 
  function headerList($id, $name)
  {
    // *1 - фильтруем данные
    if(!is_int($id)) $id = (int) abs($id);
    $link = $this->smartLink($id);
    $uri = $_SERVER['REQUEST_URI'];
        
    $form = "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$name."
              <div class='wb'><span class='wa'><a class='nav nav1' ".$link."'></a></span> | 
              <span class='wa'><a class='nav nav2' href='".self::HOST."?deleteList=".$id."&link=".$uri."'></a></span></div></div>";
    $form .= "<div class='newListName'><span class='wr'><span class='nav nav1'></span></span>
            <div class='topNewList'>
              <form action='' method='post'>
                <input class='newListNameInputTxt' type='text' name='newTask' placeholder='Start typing here to create a task...'>
                <input type='hidden' name='idList' value=".$id.">
                <input type='hidden' name='oper' value='newTask'>
                <input class = 'AddTaskBut' type='submit' value='Add Task'></form>
        </div></div><table>";
    return $form;
  } // ** - шапка проекта сформирована!
  
  // ** - формируем таблицу из списка заданий проекта
  function tableTasks($id, $idPro, $status, $nav, $style, $name)
  {
    // *1 - фильтруем данные
    if(!is_int($id)) $id = (int) abs($id);
    if(!is_int($idPro)) $idPro = (int) abs($idPro);
    if($status and !is_int($status)) $$status = (int) abs($status);
    if(!is_string($nav)) $nav = (string) $nav;
    if(!is_string($style)) $style = (string) $style;
    if(!is_string($name)) $name = (string) $name;
    $uri = $_SERVER['REQUEST_URI'];
    $link = $this->smartLink($id, $idPro);
    
    // *2 - формируем таблицу из списка заданий
    // *2.1 - ф-ем первый столбец со статусом задания и ссылкой на его изменение
    $table = "<tr'><td class='navIc2".$style."'><a class='nav ".$nav."' href='".self::HOST."?status=1&updl=".$idPro."&updt=".$id."&sts=".$status."&link=".$uri."'></a></td>";
    // *2.2 - ф-ем второй столбец с названием задания
    $table .= $name;
    // *2.3 - ф-ем третий столбец с функциональными кнопками изменения порядка, редактирования и удаления
    $table .= "<td class='navIc".$style."'>";
    // *2.3.1 - ф-ем кнопки изменения порядка
    $table .= "<span class='wn'><a class='nav nav1' href='inc/oper.php?order=up&updl=".$idPro."&updt=".$id."&link=".$uri."'></a></span> |";
    $table .= "<span class='wn'><a class='nav nav2' href='inc/oper.php?order=down&updl=".$idPro."&updt=".$id."&link=".$uri."'></a></span> |";
    // *2.3.2 - ф-ем кнопку переименования задания
    $table .= "<span class='wn'><a class='nav nav3' ".$link."'></a></span> |";
    // *2.3.3 - ф-ем кнопку удаления задания
    $table .= "<span class='wn'><a class='nav nav4' href='inc/oper.php?uptL=".$idPro."&uptT=".$id."&link=".$uri."'></a></span>";
    // *2.3.4 - завершаем строку задания
    $table .= "</td></tr>";
    
    // *3 - возвращаем созданную таблицу
    return $table;
  } // ** - таблица из списка заданий проекта - сформирована!
  
  // ** - метод возвращает id заданий, которое имеет больший/меньший id задания, указанного в аргументе $id
  function more($id, $idPro, $up=true) 
  {
    // *1 - фильтруем данные
    if(!is_int($id)) $id = (int) abs($id);
    if(!is_int($idPro)) $idPro = (int) abs($idPro);
    
    $ex = false;
    // *2 - проверяем самый большой/малый id задания
    foreach($this->allTasks as $p)
    {
      if($p['project_id'] == $idPro)
      {
        // *2.1 - если у задания наибольшой id, значит по приоритету оно самое низкое
        if(($id >= $p['id']) and !$up) continue; 
        // *2.2 - если у задания наименьший id, значит по приоритету оно самое высокое
        if(($id <= $p['id']) and $up) continue; 
        // *2.3 - если же найдено задание с более высоким id, то возвращаем его id
        if(($id < $p['id']) and !$up) return $p['id']; 
        // *2.4 - если же найдено задание с более низким id, то возвращаем его id
        if(($id > $p['id']) and $up) return $p['id'];
      }
    }
    return false;
  } // ** - меньший/больший id определён!
  
} // конец класса  
?>