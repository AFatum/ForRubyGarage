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
  
  function newList($name) // ** - создаём новый лист:
  { 
   // *1 - подготавливаем данные для имени листа
      $name = $this->clear($name);
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
  } // ** новый лист создан!
  
  function newTask($name, $pro) // ** - создаём новое задание:
  {
    // *1 - подготавливаем данные для имени задания
    $name = $this->clear($data);
    $pro = (int) abs($pro);
    // *2 - создаём запрос для БД
    $sql = "INSERT INTO tasks(name, project_id) VALUES (?, ?)";
    // *3 - создаём подготовленный запрос 
    if(!$stmt = $this->db->prepare($sql))
    { // *3.1 - если неудача, кидаем исключение
      throw new Exception("Ошибка подготовленного запроса при создании задания: ".$stmt->errno." - ".$stmt->error);
      return false;
    }
    // *3.2 - задаём параметры в подготовленный запрос
      $stmt->bind_param('si', $name, $pro);
      // *3.3 - исполняем подготовленный запрос
      if(!$stmt->execute())
      {
        throw new Exception("Какая-то ошибка в исполнении подготовленного запроса при создании нового задания: " . $stmt->errno . " - " . $stmt->error);
        return false;
      }
      else 
      { $stmt->close(); return true; }
  } // ** новое задание создано!
  
  function getList() // ** - получаем список листов заданий:
  {
    // *1 - создаём запрос для БД
    $sql = "SELECT id, name
			FROM projects
			ORDER BY id";
    
    // *2 - отправялем запрос для БД, если неудача, отлавливаем исключение
    if(!$result = $this->db->query($sql))
    {
      throw new Exception("Ошибка при выборе данных списка листов задания ".$this->db->errno." - ".$this->db->error);
      return false;
    }
    
    // *3 - формируем массив данных результата и возвращаем его
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free(); return $items;
  } // ** - список листов заданий получен
  
  function getAllTasks() // ** - получаем список всех заданий из всех списков
  {
    // *1 - создаём запрос для БД
    $sql = "SELECT
                t.id,
                t.name, 
                t.status,
                p.id AS pro_id,
                p.name AS pro
            FROM tasks as t
                RIGHT JOIN projects as p ON t.project_id = p.id
            ORDER BY pro_id";
    // *2 - отправялем запрос для БД, если неудача, отлавливаем исключение
    if(!$result = $this->db->query($sql))
    {
      throw new Exception("Ошибка при выборе данных списка всех заданий, из всех листов ".$this->db->errno." - ".$this->db->error);
      return false;
    }
    
    // *3 - формируем массив данных результата и возвращаем его
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free(); return $items;
  } // ** - список всех заданий получен

  function getCntPro($order = 1) // ** - получаем список листов заданий, отсортированные по количеству либо по имени
  {
    // *1 - создаём запрос для БД
    $sql = "SELECT p.name, count(*) as cnt 
                FROM tasks as t
                    RIGHT JOIN projects as p ON t.project_id = p.id
                GROUP BY p.name 
                ";
    // *1.1 - определяем метод сортировки, по входящему $order
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
    
    // *2 - отправялем запрос для БД, если неудача, отлавливаем исключение
    if(!$result = $this->db->query($sql))
    {
      throw new Exception("Ошибка при выборе данных отсортированного списка листов заданий ".$this->db->errno." - ".$this->db->error);
      return false;
    }
    
    // *3 - формируем массив данных результата и возвращаем его
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $result->free(); return $items;
    
  } // ** - отсортированный список листов получен
  

  
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