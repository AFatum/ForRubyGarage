<?php
class Oper
{
  //const HOST = LINK_HOST;
  const HOST = "index.php";
  public $db;
  public $autoForm;
  public $autoErr;
  public $user;
  public $allTasks;
  public $allPro;
  public $SQLTask;

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
      if($_SESSION['SQLTask']) unset($_SESSION['SQLTask']);
    }
    // *2 - если же пользователь авторизован, отображаем ссылку на выход
    else
    {
      $this->autoForm = "<p class='login'>Your user's email: ".$_SESSION['control']." - <a href='".self::HOST."?log=out' title='logout'>Log out</a></p>";
      $this->allTasks = $this->getSQL("getAllTasks");
      $this->allPro = $this->getSQL("getList");
    }
    $this->user = $_SESSION['control_id'] ?: NULL;
    $this->SQLTask = $_SESSION['SQLTask'] ?: NULL;   
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
    if($pro and !is_int($pro)) $pro = (int) abs($pro);
    
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
        /*if(!is_string($order))
        { throw new Exception("Параметр \$order не является строкой!"); return false; }*/
        $order = $this->clear($order);
    
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
/*        if(!is_string($order))
        { throw new Exception("Параметр \$order не является строкой!"); return false; }*/
        $order = $this->clear($order);
    
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
  
  function updSts($id, $idPro, $status) // ** - изменяем статус задания
  {
    // *0 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при изменении статуса задания в БД!"); return false; }
    else $user = $this->user;
    
    // *1.1.1 - фильтруем входящие параметры
    if(!is_int($id))    $id = (int) abs($id);
    if(!is_int($idPro)) $idPro = (int) abs($idPro);
    if(!is_int($status)) $status = (int) abs($status);
    // *1.1.2 - устанавливаем изменённые значения статусов
    if($status == 0) $sts = 1;
    if($status == 1) $sts = 0;
    // *1.1.3 - формируем запрос в БД
      $sql = "UPDATE tasks
		      SET
                status = ".$sts." 
              WHERE id LIKE ".$id." 
              AND (project_id LIKE ".$idPro."
                    AND user_id = ".$user.")";
      $err = "Ошибка при выполнении запроса в изменении статуса задания: ";
    
     if(!$result = $this->db->query($sql))
        {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
      else return true; // успешное изменение порядка заданий 
  } // ** - статус задания - изменяем!
  
  function updSQL ($id, $idPro, $up=true) // ** - Изменям данные в БД 
  { // *0 - Проверяем есть ли пользователь и вносим параметр в переменную
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при редактировании данных в БД!"); return false; }
    else $user = (int) abs($this->user);

      // *1.2.1 - фильтруем входящие параметры
      if(!is_int($id))    $id = (int) abs($id);
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      if(!is_bool($up))   $up = true;
      // *1.2.2 - устанавливаем изменённые значения id заданий
      $ID1=($up) ? $id : $idPro; // устанавливаем значение перемещения вверх или вниз..
      $ID2=($up) ? $idPro : $id; //.. в зависимости от полученного параметра $up
      
      // *1.2.3 - формируем три запроса на изменение данных
      $sql = "CALL ord(".$ID1.", ".$ID2.", ".$user.")";
      $err = "Ошибка при выполнении процедуры ord() на изменение порядка задания";
       if(!$this->db->query($sql))
        {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
      else return true;
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
  
  function oldName ($id, $name, $idPro=0) // ** - проверяем изменено ли новое имя
  {
    // *1 - фильтруем данные
    if(empty($this->allTasks))
       { throw new Exception("нет основного массива заданий \$this->allTasks"); return false; }
    else if(!$idPro) $tsk = $this->allPro;
    else $tsk = $this->allTasks;
    
    if(!is_string($name))
      { throw new Exception("не поступило новое имя, либо значение не строка!"); return false; }
    
    if(empty($name)) return true;
    // 2* - проверяем имя задания
    if(!$idPro)
    {
      foreach($tks as $p)
      {
        if($p['id'] == $id)
          { if($p['name'] == $name) return true;
          else return false;  }
      }
    }
    else // 3* - проверяем имя задания
    {
      foreach($tks as $t)
      {
        if($t['pro_id'] == $idPro and $t['id'] == $id)
          { if($t['name'] == $name) return true;
          else return false;  }
      }
    }
    return false;
  } // ** - проверка завершена!
  
  function rnmSQL ($id, $name, $idPro=0) // ** - переименовывем проекты и задания
  {
    // *1 - фильтруем данные
    $id = (int) abs($id);
    if($idPro > 0) $idPro = (int) abs($idPro);
    // *1.1 - если приходит пустое имя, возвращаем false
    if(empty($name)) return false;
    else $name = $this->clear($name);
    // *1.2 - если имя, которое пришло, совпадает с именем, которое есть, возвращаем false
    
    // *1.3 - определяем пользователя
    if(!$this->user) 
      { throw new Exception("Не найден пользователь при переименовании проекта либо задания"); return false; }
    else $user = $this->user;
    // *2.1 - переименовываем лист заданий - проект
    if(!$idPro) 
    {
      if($this->oldName($id, $name)) return false; // если старое и новое имя совпадают
      $sql = "UPDATE projects
		      SET
                name = '".$name."' 
              WHERE id LIKE ".$id."
              AND user_id = ".$user;
      $err = "Ошибка при переименовании листа заданий - проекта: ";
    }
    else // *2.2 - переименовываем конкретное задание
    {
      if($this->oldName($id, $name, $idPro)) return false; // если старое и новое имя совпадают
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
    else
    {
      if($_SESSION['formReg'])  unset($_SESSION['formReg']);
      if($_SESSION['pass'])     unset($_SESSION['pass']);
      if($_SESSION['login'])    unset($_SESSION['login']);
      if($_SESSION['cap'])      unset($_SESSION['cap']);
    }
    
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
    // *1.1 - устанавливаем данные для капчи
    $login = $_SESSION['login'] ?: NULL;
    $pass = $_SESSION['pass'] ?: NULL;
    $form = "<div class='genMod autoGen'>
              <div class='listName autoTitle'>Registration</div>
              <div class='newListName h259'>
                  <form method='post' action=''>
                      <input class='newListNameInputTxt autorization' type='text' placeholder='Please enter your email' name='r_email' value='".$login."'><br>
                      <input class='newListNameInputTxt autorization' type='password' placeholder='Please enter your password' name='r_pass1' value='".$pass."'><br>
                      <input class='newListNameInputTxt autorization' type='password' placeholder='Please enter your password again' name='r_pass2' value='".$pass."'><br>
                      <div class='autorization'><div class='g-recaptcha' data-sitekey='6LdpTRUUAAAAAGjRT-aLEDGDOm2GRhMRYRF1a87r'></div></div>
                      <p><input class='AddTaskBut' type='submit' value='ok'></p>
                  </form></div>";
    if($this->autoErr) $form .= $this->autoErr; // *1.2 - отображаем ошибки в форме, если они есть
    $form .= "<!--js--><script src='https://www.google.com/recaptcha/api.js?hl=en'></script>";
    
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
    $secret = "6LdpTRUUAAAAADuglWuzO7bAP9Z9W0iwtiNOyGic";
    $response = null;
    $reCaptcha = new ReCaptcha($secret);
    // *1 - если заполнены все поля то проверяем данные
    if(!empty($_POST['r_email']) and !empty($_POST['r_pass1']) and !empty($_POST['r_pass2']))
    {
      // *1.1 -  если поля паролей на совпадают - нужно отобразить сообщение об этом
      if($_POST['r_pass1'] != $_POST['r_pass2'])
      {
        $err = "<span class='pass'>Your data of passwod shoud coincide twice</span>";
        return $this->autoLocation($err, true);
      }
      // *1.2 - проверяем капчу
/*      else if($_POST['captcha'] != $_SESSION['cap'])
      {
        $_SESSION['pass'] = $_POST['r_pass1'];
        $_SESSION['login'] = $_POST['r_email'];       
        $err = "<span class='pass'>You entered incorrect captchas data, please try again </span>";
        return $this->autoLocation($err, true);
      }*/
      else if($_POST["g-recaptcha-response"]) 
      {
        $response = 
            $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);
        if($response != null && $response->success)
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
              unset($_SESSION['pass'], $_SESSION['login'], $_SESSION['cap']);
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
        }// r
        else
        {
          $_SESSION['pass'] = $_POST['r_pass1'];
          $_SESSION['login'] = $_POST['r_email'];       
          $err = "<span class='pass'>You entered incorrect captchas data, please try again </span>";
          return $this->autoLocation($err, true);
        }
      }
      else
        {
          $_SESSION['pass'] = $_POST['r_pass1'];
          $_SESSION['login'] = $_POST['r_email'];       
          $err = "<span class='pass'>You entered incorrect captchas data, please try again </span>";
          return $this->autoLocation($err, true);
        }
      // *1.3 -  если поля паролей таки совпадают и капча в поряде - обрабатываем данные дальше...
/*      else
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
            unset($_SESSION['pass'], $_SESSION['login'], $_SESSION['cap']);
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
      }*/
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
        else {  header("Location: ".self::HOST); return false;  }
      } // ** - лист заданий - переименован!
      
      if($_POST['oper'] == "renameTask") // ** - переименовываем конкретное задание
      { // *1 - фильтруем данные
        // *1.1 - если поступили не все данные, возвращаем false
        if(is_null($_POST['newName']) or !$_POST['idPro'] or !$_POST['idTsk']) return false;
        // *1.2 - если все данные получены - работаем дальше
        if($this->rnmSQL($_POST['idTsk'], $_POST['newName'], $_POST['idPro']))
          { header("Location: ".self::HOST); return true; }
        else {  header("Location: ".self::HOST); return false;  }
      } // ** - конкретное задание - переименовано!  
      
      if($_POST['oper'] == "newTask") // ** - добавляем новое задание
      {
        if(!$_POST['newTask'] or !$_POST['idList']) return false;
        if($this->newSQL($_POST['newTask'], $_POST['idList']))
          { header("Location: ".self::HOST); return true; }
        else return false;
      } // ** - новое задание - добавлено!
      
      if($_POST['oper'] == "newList") // ** - добавляем новый лист заданий
      {
        if(!$_POST['list']) return false;
        if($this->newSQL($_POST['list']))
          {
            //$this->allTasks = $this->getSQL("getAllTasks");
            header("Location: ".self::HOST); 
            return true; 
          }
        else return false;
      }
      
      if($_POST['Get1'] and !empty($_POST['GetOrder'])) // ** - сортировка листов заданий для SQL-Task
      {
        switch($_POST['GetOrder'])
        {
          case "cntEachPro": $_SESSION['SQLTask'] = $this->cntEachPro(); break;   
          case "cntEachNms": $_SESSION['SQLTask'] = $this->cntEachPro(3); break;  
          case "dupTsk":     $_SESSION['SQLTask'] = $this->dupTsk(); break; 
          case "Garage":     $_SESSION['SQLTask'] = $this->garage(); break;  
          case "more10":     $_SESSION['SQLTask'] = $this->more10(); break;            
          default:           $_SESSION['SQLTask'] = $this->cntEachPro(); break;
        }
        header($this->smartLink("base65"));
        return true;
      }
      
      if($_POST['Get4']) // ** - сортируем по первой букве в названии задания
      {
        $_SESSION['SQLTask'] = $this->beginLetter($_POST['beginLetter']);
        header($this->smartLink("base65"));
        return true;
      } 
      
      if($_POST['Get3']) // ** - сортировка заданий c определённой буквой в названии задания
      {
        $_SESSION['SQLTask'] = $this->middleLetter($_POST['middleLetter']);
        header($this->smartLink("base65"));
        return true;
      }  
      
      if($_POST['Get2']) // ** - сортировка заданий по статусам
      {
        $_SESSION['SQLTask'] = $this->statuses($_POST['statuses']);
        header($this->smartLink("base65"));
        return true;
      }
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
        if($_SESSION['pass'])       unset($_SESSION['pass']);
        if($_SESSION['login'])      unset($_SESSION['login']);
        if($_SESSION['cap'])        unset($_SESSION['cap']);
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
           {  header($this->smartLink("base64")); return true; }
        else return false;
      } // *3* - лист заданий - удалён!
           
      if($_GET['status'] == 1) // *4 - меняем статус задания
      {        
        if(!$_GET['updl'] or !$_GET['updt']) return false;
        if($this->updSts($_GET['updt'], $_GET['updl'], $_GET['sts']))
             {  header($this->smartLink("base64")); return true; }
        else return false;
      }
      
      if($_GET['order']) // *5 - меняем порядок задания
      {
        if(!$_GET['updl'] or !$_GET['updt']) 
          { return false; }
        if(!is_array($this->allTasks) or empty($this->allTasks))
          { return false; }
        //$link = $_GET['link'] ?: NULL;

        // *5.1 - если нужно передвинуть задание выше
        if($_GET['order'] == "up")
        {
          // *5.1.1 - определяем id задание которое выше, если такого нет, то возвращаем false
          if(!$next = $this->more($_GET['updt'], $_GET['updl']))
            { header($this->smartLink("base64")); return false; }
          // *5.1.2 - передвигаем задание выше
          if($this->updSQL($_GET['updt'], $next))
            {  header($this->smartLink("base64")); return true; }
          else return false;
        }
        // *5.2 - если нужно передвинуть задание НИЖЕ
        if($_GET['order'] == "down")
        {
          // *5.2.1 - определяем id задание которое ниже, если такого нет, то возвращаем false
          if(!$next = $this->more($_GET['updt'], $_GET['updl'], false))
            { header($this->smartLink("base64")); return false; }
          // *5.2.2 - передвигаем задание НИЖЕ
          if($this->updSQL($_GET['updt'], $next, false))
            {  header($this->smartLink("base64")); return true; }
          else
            { header($this->smartLink("base64")); return false; }
        }
        
      }
      
      if($_GET['id'] == "add2list") // *6 - отображаем форму добавления нового листа
        { echo $this->add2listForm(); }
      
      
      
      if($_GET['id'] == "SQLtask") // *7 - отображаем форму для SQL-заданий
      {
        if($_GET['id2']) // удаляем предыдущие параметры от формы SQLTask
        { unset($_SESSION['SQLTask']); header($this->smartLink("base65"));  }
        echo $this->SQLTaskForm();
      }
        
     if($_GET['sort']) // *8 - сортируем отображение списка проектов
     {     
        $i = 0;
        switch($_GET['sort'])
        { 
          case "krsort":  $i=4; break;  // *8.4 - по количеству заданий в обратном порядке
          case "ksort":   $i=3; break;  // *8.3 - по количеству заданий
          case "arsort":  $i=2; break;  // *8.2 - по имени в обратном порядке
          case "asort":   $i=1; break;  // *8.1 - по имени
        }
       $_SESSION['SQLTask'] = $this->cntEachPro($i);
       header($this->smartLink("base65"));
       return true;
     }
      
    // *9 - удаление задания
    if($_GET['uptL'] and $_GET['uptT'])
    {
      if($this->delSQL($_GET['uptL'], $_GET['uptT']))
        {  header($this->smartLink("base64")); return true; }
      else return false;
    }
      
    } // ** - данные авторизированного пользователя - обработаны!
  } // get-параметры - адаптированы  
  
  // ************** Методы формирования/обработки списка заданий ************************************
  
  function showPro() // ** - Отображаем список проектов с заданиями
  {
    // *1 - фильтруем данные
    if(!$this->user or $this->user != $_SESSION['control_id'])
      $this->user = $_SESSION['control_id'];
    
    // *2 - получаем список заданий и проектов из БД
    //$arrTsk = $this->getSQL("getAllTasks");
    // *2.1 - если пришёл пустой архив - отображаем сообщение про создание нового листа заданий
    if(!is_array($this->allPro) or empty($this->allPro)) 
    { 
      //throw new Exception("<p>Ошибка получения данных из БД, при получении полного списка заданий</p>"); 
      //return false; 
      echo "<p class='login'>Please create new List</p>";
    }
    else // *2.2 - если у пользователя уже есть списки заданий, отображаем их
    {
      // *2.2.1 - устанавливаем первоначальные параметры для отображения списка
      $po = 0; $ts = 0;
      // *2.2.2 - выводи списки заданий
      
      foreach($this->allPro as $p) // *2.2.2.1 - если есть новый список, отображаем его
      {
        if($po > 0) echo "</table></div>";  // *2.2.2.2 - закрываем таблицу и основной div
        $po = $p['id'];                     // *2.2.2.3 - обновляем индекс номера листа задания
        $ts = 1;                            // *2.2.2.4 - устанавливаем первый индекс задания
        // *2.2.2.5 - задаём форму переименования листа заданий, или просто его имя
        $nameList = ($_GET['renameList'] == $p['id']) ? $this->reName($p['id']) : $p['name'];
        // *2.2.2.6 - вываодим фрейм проекта со ссылкой добавления нового задания
        echo $this->headerList($p['id'], $nameList);
        foreach($this->allTasks as $t)
        {
          // *2.2.3 - если в проекте уже есть задания, отображаем их
          if($po == $t['pro_id'] and !empty($t['id'])) 
          {
            if($t['status'] == 0) {   $stlTr = NULL;  $stlTr2 = NULL; $nav="nav5";    }
            else {   $stlTr = " class='statusTrue'";  $stlTr2 = " statusTrue"; $nav="nav6";  }
            // *2.2.3.2 - задаём форму переименования конкретного задания, или просто его имя
            $nameTask = ($_GET['rnmL'] == $t['pro_id'] and $_GET['rnmT'] == $t['id'] and !$_GET['order']) 
              ? $this->reName($t['id'], $t['pro_id'], $stlTr) : "<td".$stlTr.">".$t['name']."</td>";;
            // *2.2.3.3 - создаём строки заданий в таблице
            echo $this->tableTasks($t['id'], $t['pro_id'], $t['status'], $nav, $stlTr2, $nameTask);
          }
        } // конец основного foreach
      } // конец основного foreach
      echo "</table></div>";
    } // *2.2* - списки заданий - отображены!
    $link = "<a class = 'AddList' ".$this->smartLink("add2list")." title='Add TODO List'>Add TODO List</a>";
    $link .= "<a class = 'AddList sqlTask' ".$this->smartLink("SQLtask")." title='SQL task'>SQL task</a>";
    echo $link;
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
    if(!empty($id) and is_numeric($id)) $id = (int) abs($id);
    //if(!$idPro or is_int($id)) 
    if($idPro and !is_int($idPro)) $idPro = (int) abs($idPro);
    $update = ($_SERVER['QUERY_STRING']) ? "&".($_SERVER['QUERY_STRING']) : NULL;
    $updateL = ($_SERVER['QUERY_STRING']) ? "?".($_SERVER['QUERY_STRING']) : NULL;
    
    // *1 - формируем ссылку для переименования листа заданий
    if(is_numeric($id) and !$idPro)
    { // *1.1 - формируем ссылку, если уже есть параметры GET и нет параметра $_GET['renameList']     
      $link = (!empty($_SERVER['QUERY_STRING']) and empty($_GET['renameList']))
        ? "href='".self::HOST."?renameList=".$id."&".$_SERVER['QUERY_STRING']."'"
        : "href='".self::HOST."?renameList=".$id."'";
    }       
    // *2 - формируем ссылку для переименования задания
    if($id and $idPro)
    { // *2.1 - формируем ссылку, если уже есть параметры $_GET['updl'] и нет параметров $_GET['updl'] и $_GET['updt']
      $link = (!empty($_SERVER['QUERY_STRING']) and empty($_GET['updl']) and empty($_GET['updt']))
        ? "href='".self::HOST."?rnmL=".$idPro."&rnmT=".$id."&".$_SERVER['QUERY_STRING']."'"
        : "href='".self::HOST."?rnmL=".$idPro."&rnmT=".$id."'";
    }
    if(is_string($id) and !is_numeric($id))
    {    
      switch($id)
      {
        case "add2list":
          $link = (!empty($_SERVER['QUERY_STRING']) and empty($_GET['id']))
          ? "href='".self::HOST."?id=add2list&".$_SERVER['QUERY_STRING']."'"
          : "href='".self::HOST."?id=add2list'";
        break;
          
        case "SQLtask":
          $link = (!empty($_SERVER['QUERY_STRING']) and empty($_GET['id']))
          ? "href='".self::HOST."?id=SQLtask&id2=true&".$_SERVER['QUERY_STRING']."'"
          : "href='".self::HOST."?id=SQLtask&id2=true'";
        break;
        
        case "base64":
          $link = (!empty($_GET['link'])) 
            ? "Location: ".self::HOST."?".base64_decode($_GET['link'])
            : "Location: ".self::HOST;
        break;
          
        case "base65":
          $link = (!empty($_GET['link'])) 
            ? "Location: ".self::HOST."?".base64_decode($_GET['link'])."#sqlt"
            : "Location: ".self::HOST."?id=SQLtask#sqlt";
        break;
          
        default:
          $link = (!empty($_GET['link'])) 
            ? "Location: ".self::HOST."?".base64_decode($_GET['link'])
            : "Location: ".self::HOST;
        break;
          
      }     
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
    $uri = ($_SERVER['QUERY_STRING']) 
      ? "&link=".base64_encode($_SERVER['QUERY_STRING']) : NULL;
        
    $form = "<div class='genMod'><div class='listName'><span class='wf'><span class='nav nav1'></span></span>".$name."
              <div class='wb'><span class='wa'><a class='nav nav1' ".$link."'></a></span> | 
              <span class='wa'><a class='nav nav2' href='".self::HOST."?deleteList=".$id.$uri."'></a></span></div></div>";
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
    $uri = ($_SERVER['QUERY_STRING']) 
      ? "&link=".base64_encode($_SERVER['QUERY_STRING']) : NULL;
    $link = $this->smartLink($id, $idPro);
    
    // *2 - формируем таблицу из списка заданий
    // *2.1 - ф-ем первый столбец со статусом задания и ссылкой на его изменение
    $table = "<tr'><td class='navIc2".$style."'><a class='nav ".$nav."' href='".self::HOST."?status=1&updl=".$idPro."&updt=".$id."&sts=".$status.$uri."'></a></td>";
    // *2.2 - ф-ем второй столбец с названием задания
    $table .= $name;
    // *2.3 - ф-ем третий столбец с функциональными кнопками изменения порядка, редактирования и удаления
    $table .= "<td class='navIc".$style."'>";
    // *2.3.1 - ф-ем кнопки изменения порядка
    $table .= "<span class='wn'><a class='nav nav1' href='".self::HOST."?order=up&updl=".$idPro."&updt=".$id.$uri."'></a></span> |";
    $table .= "<span class='wn'><a class='nav nav2' href='".self::HOST."?order=down&updl=".$idPro."&updt=".$id.$uri."'></a></span> |";
    // *2.3.2 - ф-ем кнопку переименования задания
    $table .= "<span class='wn'><a class='nav nav3' ".$link."'></a></span> |";
    // *2.3.3 - ф-ем кнопку удаления задания
    $table .= "<span class='wn'><a class='nav nav4' href='".self::HOST."?uptL=".$idPro."&uptT=".$id.$uri."'></a></span>";
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
    // *1.1 - проверяем, если нет заданий, возвращаем false
    if(!is_array($this->allTasks) or empty($this->allTasks)) return false;
    // *1.2 - если задания таки есть, то отображаем их
    $array = $this->allTasks;
    
    if($up)
    { // *2 - передвигаем задание наверх
      krsort($array); // *2.1 - сортируем массив в обратном порядке.
      foreach($array as $p)
      {
        if($p['pro_id'] == $idPro)
        { // *2.2 - если текущий id заданий не более заданного - пропускаем
          if($id <= $p['id']) continue;
          // *2.3 - возвращаем id задания, следующий за заданным
          else return $p['id'];
        }
      }
    }   
    else
    { // *3 - передвигаем задание вниз
      foreach($array as $p)
      {
        if($p['pro_id'] == $idPro)
        { // *3.1 - если текущий id заданий не МЕННЕЕ заданного - пропускаем
          if($id >= $p['id']) continue;
          // *3.2 - возвращаем id задания, следующий за заданным
          else return $p['id'];
        }
      }
    }   
    return false;
  } // ** - меньший/больший id определён!
  
  function add2listForm() // ** - форма добавления нового листа
  {
    $form = "<form action='' method='post'>
                <input class='newListTxt' type='text' name='list' placeholder='Start typing here to create new list'>
                <input type='hidden' name='oper' value='newList'>
                <input type='submit' value='Create List' class='AddNewList'> 
              </form>";
    return $form;
  }
  
  function SQLTaskForm() // ** - форма добавления для SQL-заданий
  {
    $az = "abcdefghijklmnopqrstuvwxyz";
    $AZ = strtoupper($az);
    
    $form = "<div class='genMod sqlMod' id='sqlt'><div class='listName sqlMod2'>SQL Task</div>
              <div class='newListName sqlMod2'>
                  <form action='' method='post'>
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
                  <div class='letter'>Get the tasks for all projects having the name beginning with 
                  <select name='beginLetter'>";
    for($i=0; $i<=25; $i++)
      { $form .= "<option value='".$AZ[$i]."'>'".$AZ[$i]."'</option>"; }
    
    $form .= "</select> letter
                <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get2'></div>
                <div class='letter letter2'>Get the list of all projects containing the 
                <select name='middleLetter'>";
    
    for($i=0; $i<=25; $i++)
      { $form .= "<option value='".$az[$i]."'>'".$az[$i]."'</option>"; }
    
    $form .= "</select> letter in the middle of the name.
                <input class = 'AddTaskBut update sqlMod3' type='submit' value='Go' name='Get3'></div>
                </form>
            </div>";
    $form .= $this->SQLTask;
    return $form;
  }
  
  // ************** Методы для SQL-задания ************************************
  function cntEachPro($id=1) // ** - отображаем отсортированные листы заданий
  {
    // *1 - получаем данные из БД
    $resCntEcPro = $this->getSQL("getCntPro", $id); 
    // *2 - формируем таблицу из полученных данных
    $table = "<table class='cntTask'>";
    $table .= "<tr><th>List Name
              <span class='wn1'><a class='nav nav7' href='".self::HOST."?sort=ksort'></a></span>
              <span class='wn1 wn2'><a class='nav nav8' href='".self::HOST."?sort=krsort'></a></span></th>
                      <th class='tdCntTask'>Count Task
                      <span class='wn1'><a class='nav nav7' href='".self::HOST."?sort=asort'></a></span>
                      <span class='wn1 wn2'><a class='nav nav8' href='".self::HOST."?sort=arsort'></a></span></th>
                      </tr>";
    // *2.1 - заполняем данные из БД
    foreach($resCntEcPro as $res)
        $table .= "<tr><td>".$res['name']."</td><td class='tdCntTask'>".$res['cnt']."</td></tr>";
    // *2.2 - закрываем таблицу и основной блок            
    $table .= "</table></div>";
    // *3 - возвращаем результат
    return $table;
  }
  
  function dupTsk() // ** - получаем список заданий, которые находятся в разных листах
  { // *1 - получаем данные из БД
    $dupTsk = $this->getSQL("getDoubleTask"); 
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
    // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th class='ProNameDup'>Project Name</th>";
    $table .= "<th>Task Name</th></tr>";
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($dupTsk))
      foreach($dupTsk as $res)
        { $table .= "<tr><td>".$c."</td><td>".$res['pro']."</td><td>".$res['name']."</td></tr>"; $c++; }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=3><strong>Tasks is not found!</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  }
  
  function garage() // ** - получаем список заданий, которые находятся в проекте "Garage"
  {
    // *1 - получаем данные из БД
    $garage = $this->getSQL("getGarage"); 
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
     // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th class='thGar'>Project Name</th>";
    $table .= "<th class='thGar'>Task Name</th>";
    $table .= "<th>Status</th></tr>"; 
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($garage))
      foreach($garage as $res)
      { // *2.2.1 - определяем сообщения статуса задания
        $sts = (!empty($res['status'])) ? "<span class='com'>is complete</span>" : "<span class='ncom'>is not complete</span>";
        $table .= "<tr><td>".$c."</td><td>".$res['pro']."</td><td>".$res['name']."</td><td>".$sts."</td></tr>";
        $c++;
      }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=4><strong>Tasks is not found!</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  } 
  
  function more10() // ** - получаем список заданий, которые находятся в проекте "Garage"
  {
    // *1 - получаем данные из БД
    $more10 = $this->getSQL("get10CompTask");
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
     // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th>Project ID</th>";
    $table .= "<th>Project Name</th>";
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($more10))
      foreach($more10 as $res)
      {
        $table .= "<tr><td>".$c."</td><td>".$res['id']."</td><td>".$res['name']."</td></tr>";
        $c++;
      }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=3><strong>Projects width 10 complete tasks, is not found!</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  }
  
  function statuses($sts) // ** - получаем список заданий, в зависимости от статуса
  {
    // *0 - фильтруем данные
    if(!is_int($sts)) $sts = (int) abs($sts);
    // *1 - получаем данные из БД
    $statuses = $this->getSQL("getSts", $sts);
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
     // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th class='thGar'>Project Name</th>";
    $table .= "<th class='thGar'>Task Name</th>";
    $table .= "<th>Status</th></tr>";
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($statuses))
      foreach($statuses as $res)
      { // *2.2.1 - задаём сообщение для статуса задания
        if($res['status'] > 0) $s = "<span class='com'>is complete</span>";
        if($res['status'] == 0 or $res['status'] == NULL) $s = "<span class='ncom'>is not complete</span>";
        $table .= "<tr><td>".$c."</td><td>".$res['pro']."</td><td>".$res['name']."</td><td>".$s."</td></tr>";
        $c++;
      }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=4><strong>Tasks is not found!</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  }  
  
  function beginLetter($let) // ** - получаем список заданий, с заданной начальной буквой в названии
  {
    // *1 - получаем данные из БД
    $letter = $this->getSQL("getLetter1", $let);
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
     // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th class='ProNameDup'>Project Name</th>";
    $table .= "<th>Task Name</th></tr>";
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($letter))
      foreach($letter as $res)
      {
        $table .= "<tr><td>".$cnt."</td><td>".$res['pro']."</td><td>".$res['name']."</td></tr>";
        $c++;
      }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=3><strong>Tasks on the letter '".$let."' is not found.</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  }  
  
  function middleLetter($let) // ** - получаем список заданий, с заданной буквой в названии
  {
    // *1 - получаем данные из БД
    $letter = $this->getSQL("getLetter2", $let);
    $c = 1; // *1.1 - счёткие для первого столбца - порядковый номер
     // *2 - формируем таблицу данных
    $table = "<table class='cntTask'>";
    // *2.1 - формируем шапку таблицы
    $table .= "<tr><th class='numDup'>#</th>";
    $table .= "<th>Project Name</th>";
    $table .= "<th class='numDup'>Count Tasks</th></tr>";
    // *2.2 - заполняем данные из бд, если они есть
    if(!empty($letter))
      foreach($letter as $res)
      {
        $cnt = (is_null($res['tsk'])) ? (int) $res['cnt'] - 1 : $res['cnt'];
        $table .= "<tr><td>".$c."</td><td>".$res['name']."</td><td>".$cnt."</td></tr>";
        $c++;
      }
    // *2.3 - если же данных из БД нет, отображаем сообщение
    else $table .= "<tr><td colspan=3><strong>Projects with the letter '".$let."' is not found.</strong></td></tr>";
    $table .= "</table>";
    // *3 - возвращаем результат
    return $table;
  }

} // конец класса  
?>