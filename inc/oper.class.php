<?php
class Oper
{
  const HOST = LINK_HOST;
  public $db;

  // вносим в конструктор класс базы данных
  function __construct(mysqli $db)
  {
    $this->db = $db;
  }
  
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
    {
      throw new Exception($err.$stmt->errno." - ".$stmt->error);
      return false;
    }
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
    
    
    
    if($type)  // обработка GET
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
}
  
?>