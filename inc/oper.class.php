<?php
class Oper
{
  //const HOST = LINK_HOST;
  const HOST = "newIndex.php";
  public $db;
  public $autoForm;
  public $autoErr = NULL;

  // вносим в конструктор класс базы данных
  function __construct(mysqli $db)  
  { // ** - вносим первоначальные стандартные параметры: 
    $this->db = $db;
    $this->autoForm = ($_GET['reg']) ? $this->formAuto() : $this->formReg();
  }
  
  //////////--МЕТОДЫ ПО РАБОТЕ С БД И С ПОЛЬЗОВАТЕЛЯМИ--//////////////////////
  
  // подгототавливаем строку к внесению в бд
  function clear($data)
  {
    $Data = (!is_string($data)) ? (string) $data : $data;   
    return $this->db->real_escape_string(trim(strip_tags($Data)));
  }
    
  function newSQL ($name, $pro=0) // ** - Отправляем данные в БД
  {
    // *1 - подготавливаем входящие данные
    $name = $this->clear($name);
    $pro = (int) abs($pro);
    
    // *2 - формируем запрос
    if($pro == 0) // *2.1 - создаём новый лист заданий - проект 
    {
      $sql = "INSERT INTO projects(name) VALUES (?)";
      $err = "Ошибка подготовленного запроса при создании листа: ";
    }
    else // *2.2 - создаём новое задание из выбранного проекта
    {
      $sql = "INSERT INTO tasks(name, project_id) VALUES (?, ?)";
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
      $stmt->bind_param('s', $name);
    }
    else
    {
      $err = "Какая-то ошибка в исполнении подготовленного запроса при создании нового задания: ";
      $stmt->bind_param('si', $name, $pro);
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
    
    // *2 - Выбираем нужный для нас запрос в БД
    switch($oper)
    {
      case "getList": // *2.1 - получаем список листов заданий
        $sql = "SELECT id, name
			FROM projects
			ORDER BY id";
        $err = "Ошибка при выборе данных списка листов задания ";
      break;
        
      case "getAllTasks": // *2.2 - получаем список всех заданий из всех списков
        $sql = "SELECT
                t.id,
                t.name, 
                t.status,
                p.id AS pro_id,
                p.name AS pro
            FROM tasks as t
                RIGHT JOIN projects as p ON t.project_id = p.id
            ORDER BY pro_id";
        $err = "Ошибка при выборе данных списка всех заданий, из всех листов ";
      break; 
        
      case "getCntPro": // *2.3 - пполучаем список листов (проектов) заданий, отсортированные по количеству либо по имени
        $sql = "SELECT p.name, count(*) as cnt 
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
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
                            GROUP BY t2.name
                            HAVING cnt > 1
                )
                ORDER BY t1.name";
        $err = "Ошибка при выборе данных списка проектов с дублирующими заданиями: ";
      break; 
        
      case "get10CompTask": // *2.5 - Выбор проектов с 10 и более выполненных заданий
        $sql = "SELECT p.name, p.id, COUNT(*) as cnt
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                WHERE t.status = 1
                GROUP BY p.name
                HAVING cnt > 9
                ORDER BY p.name";
        $err = "Ошибка при выборе данных списка проектов с дублирующими заданиями: ";
      break; 
        
      case "getGarage": // *2.6 - Выбор заданий, которые по названию и по статусу совпадают с проектом 'Garage'
       $sql = "CALL gar()";
       $err = "Ошибка при выборе заданий, которые по названию и по статусу совпадают с проектом 'Garage': ";
      break;  
    
      case "getSts": // *2.7 - Выбор данных с выполненными или невыполненными статусами
        $sql = "SELECT p.name as pro, t.name, t.status
	            FROM tasks as t
                RIGHT JOIN projects as p ON t.project_id = p.id
                ";
        // *2.7.1 - Выбор заданий с выполненными статусами
        if($order == 1) $sql .= "WHERE t.status = 1
		                   AND t.name IS NOT NULL
	                       ORDER BY pro";
        // *2.7.2 - Выбор заданий с НЕвыполненными статусами
        else $sql .= "WHERE t.status = 0
		              OR t.status IS NULL
		              AND t.name IS NOT NULL
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
  { // *1 - формируем запрос в БД, в зависимости от выбранной операции
    if($up === 0 or $up === 1) // *1.1 - меняем статус выполнения задания
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
              AND project_id LIKE ".$idPro;
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
                        id LIKE ".$ID1;
        
      $sql2 = "UPDATE tasks
                SET
                    id = ".$ID1." 
                WHERE
                    id LIKE ".$ID2;

      $sql3 = "UPDATE tasks
                SET
                    id = ".$ID2." 
                WHERE
                    id LIKE 11111";
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
  { // *1 - Определяем что будем удалять, лист (проект) либо задание
    if($id == 0) // *1.1 - Удаляем лист заданий - проект
    {
      // *1.1.1 - фильтруем входящие данные
      if(!is_int($idPro)) $idPro = (int) abs($idPro);
      
      // *1.1.2 - создаём запрос
      $sql = "DELETE FROM projects
            WHERE id LIKE ".$idPro;
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
                  WHERE project_id LIKE ".$idPro;
        $err = "Ошибка при удалении всех заданий из проекта: ";
      }
      else 
      {
        $sql = "DELETE FROM tasks
                  WHERE id LIKE ".$id." 
                  AND project_id LIKE ".$idPro;
        $err = "Ошибка при удалении задания из проекта: ";
      }
    }
    
    // *2 - выполняем запрос
    if(!$result = $this->db->query($sql))
      {  throw new Exception($err.$this->db->errno." - ".$this->db->error); return false;  }
    else return true;
  } // ** - Данные из БД - удалены!
  
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
        else if(password_verify($pass, trim($data['pass']))) return trim($data['email']);
    }
    return false; // проверка не пройдера
  } // ** - проверка логина/пароля завершена.

  //////////--МЕТОДЫ ПРЕДСТАВЛЕНИЯ--//////////////////////
  
  // ** - отображаем нужную форму с нужной ошибкой
  function autoLocation($err=NULL, $reg=false)
  {
    // *1 - устанавливаем флаг контроля, пока не будет пройдена авторизация
    if($_SESSION['control']) unset($_SESSION['control']);
    // *2 - устанавливаем параметры ошибки, если он есть
    $this->autoErr = $err;
    // *3 - устанавливаем параметр нужной для нас формы:
    $this->autoForm = (!$reg) ? $this->formAuto() : $this->formReg();
    // *4 - перенаправляем обратно, на главную страницу с обновлёнными параметрами отображения формы
    //header("Refresh: 1");
    header("Location: ".self::HOST); 
    return true;
  } // ** - параметры формы и ошибки установлены успешно
  
  function autoMess($data) // ** - выводим сообщение про успешную авторизацию
  { echo "<p class='login'>Your user's email: ".$data." - <a href='newIndex.php?log=out' title='logout'>Log out</a></p>"; } 
  // ** - сообщение об успешной авторизации выведено
  
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
    $form .= "<a href='newIndex.php?reg=1' title='Registration'>Registration</a></div>";
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
                  </form>";
    if($this->autoErr) $form .= $this->autoErr; // *1.1 - отображаем ошибки в форме, если они есть
    
    // *1.2 - добавляем окончания формы:
    $form .= "<a href='index.php?reg=2' title='Login'>Login</a></div>";
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
      else // *1.1.2 - если верификация таки пройдена
      {
        if($this->autoErr) 
          { $this->autoErr = NULL; $this->autoForm = $this->formAuto(); }
        $_SESSION['control'] = $res;
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
    
    if($_GET['reg'] == 1) // *1.3 - отображаем форму регистрации нового пользователя
      return $this->autoLocation(NULL, true);
      
    
    if($_GET['reg'] == 2) // *1.4 - обратно, отображаем форму авторизации нового пользователя
      return $this->autoLocation();
    
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
  
  function postAdapter() // ** - адаптер для обработки post-параметров
  {
    // *1 - обрабатываем параметры авторизации  
    if($_POST['email'] or $_POST['pass']) return $this->autorization(); 
    
    // *2 - обработка параметров из форммы регистрации
    if($_POST['r_email'] or $_POST['r_pass1'] or $_POST['r_pass2']) return $this->registration();
    
  } // post-параметры - адаптированы  
  
  function getAdapter() // ** - адаптер для обработки get-параметров
  {
    // *1 - обрабатываем параметры авторизации  
    //if($_GET['reg']) return $this->autorization(); 
     if($_GET['reg'] == 1) // *1.3 - отображаем форму регистрации нового пользователя
      return $this->autoLocation(NULL, true);
      
    
    if($_GET['reg'] == 2) // *1.4 - обратно, отображаем форму авторизации нового пользователя
      return $this->autoLocation();
  } // get-параметры - адаптированы  
  
} // конец класса
  /* основной метод операций, через который будем всё делать
   здесь если $type == false, значит обрабатывается метод POST
   если $type == true, значит метод GET. 
  function getOper($oper, $data, $type = false)
  {
    if(!$type) // обработка POST
    {
      switch($oper)
      {
        // ** - создаём новый лист:
        case "newList":
          // *1 - подготавливаем данные для имени листа
          $name = $this->clear($data);
          // *2 - создаём запрос для БД
          $sql = "INSERT INTO projects(name) VALUES (?)";
          // *3 - создаём подготовленный запрос 
          if(!$stmt = $this->db->prepare($sql))
          { // *3.1 - если неудача, кидаем исключение
            throw new Exception("Ошибка подготовленного запроса при создании листа: ".$stmt->errno." - ".$stmt->error);
            return false;
          }
          // *3.2 - задаём параметры в подготовленный запрос
          $stmt->bind_param('s', $name);
          // *3.3 - исполняем подготовленный запрос
          if(!$stmt->execute())
          {
            throw new Exception("Какая-то ошибка в исполнении подготовленного запроса при создании нового листа: " . $stmt->errno . " - " . $stmt->error);
            return false;
          }
          else 
          { $stmt->close(); return true; }
          return $this->db->newList($name);
        break; // ** новый лист создан!
        
        // ** - создаём новое задание
        case "newTask":
          // *1 - подготавливаем данные для имени листа
          $name = $this->clear($data);
          // *2 - создаём запрос для БД
          $sql = "INSERT INTO projects(name) VALUES (?)";
      }
    }
  */
    
    
    
    /*if($type)  // обработка GET
    {
      
    }
    
    
  }
  
    // 
    if($_POST) 
    {
      switch($_POST)
      {
        
      }
    }
    
    if($_GET)
    {
      
    }

  function
  {
    
  }
}*/
  
?>