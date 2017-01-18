<?php
class Oper
{
  const HOST = LINK_HOST;
  public $db;

  // подгототавливаем строку к внесению в бд
  function clear($data)
  {
    $Data = (!is_string($data)) ? (string) $data : $data;   
    return $this->db->real_escape_string(trim(strip_tags($Data)));
  }
  
  // вносим в конструктор класс базы данных
  function __construct(db $db, $type)
  {
    $this->db = $db;
  }
  
  /* основной метод операций, через который будем всё делать
   здесь если $type == false, значит обрабатывается метод POST
   если $type == true, значит метод GET. */
  function getOper($oper, $type = false)
  {
    if(!$type) // обработка POST
    {
      switch($oper)
      {
        // ** создаём новый лист:
        case "newList":
          // *1 подготавливаем данные для имени листа
          $name = $this->clear($data);
          return $this->db->newList($name);
        break; // ** новый лист создан!
      }
    }
    
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

/*function newList($name)
{
    global $link;
    if(!is_string($name)) return false;

    //$name = mysqli_real_escape_string ($link, trim(strip_tags($name)));
    $sql = "INSERT INTO projects(name) VALUES (?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;

    mysqli_stmt_bind_param($stmtIns, 's', $name);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    mysqli_stmt_close($stmtIns);
    return true;
}*/
  
?>