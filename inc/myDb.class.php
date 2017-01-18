<?php
  class myDb extends mysqli
  {
    function newList($name)
    {
      $sql = "INSERT INTO projects(name) VALUES (?)";
      if(!$stmt = $this->prepare($sql)) return false;
      $stmt->bind_param('s', $name);
      if(!$stmt->execute()) return "Какая-то ошибка в запросе (" . $stmt->errno . ") " . $stmt->error;
      else 
        { $stmt->close(); return true; }
      
/*      $sql = "INSERT INTO projects(name) VALUES (?)";
    if(!$stmtIns = mysqli_prepare($link, $sql)) return false;

    mysqli_stmt_bind_param($stmtIns, 's', $name);
    mysqli_stmt_execute($stmtIns) or die("Какая-то ошибка в запросе ".mysqli_error($link));    
    mysqli_stmt_close($stmtIns);
    return true;
    }*/
  }
?>