<?php
namespace Devi\Utils;

use PDO;

class Database extends Hydrator
{
  // Variables
  private $pdo;
 
  // Constructor
  public function __construct(string $dsn, $username = "", $password = "", $options = [])
  {
    $this->pdo = new PDO($dsn,$username,$password,$options);
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  }
  
  // Proxy for native PDO functions
  public function __call($method,$args)
  {
    return call_user_func_array([$this->pdo,$method],$args);
  }
  
  // Select
  public function select(string $table, array $where = [], array $options = [])
  {
    $query = "select * from {$table}";
    
    // Where clause
    if (!empty($where))
    {
      $query .= " where " . implode(" and ",array_map(function($k, $v) {
        return $k . ' = :' . $k;
      },array_keys($where),$where));
    }
            
    // Options
    if (!empty($options))
    {
      $query .= " " . implode(' ',array_map(function($k,$v){
        return $k . ' ' . $v;
      },array_keys($options),$options));
    }
    
    // Prepare the statement
    $st = $this->prepare($query);
    foreach ($where as $k => $v)
      $st->bindValue(':' . $k,$v);
    $st->execute();
    
    // Get the results
    $results = array_map([$this,'hydrate'],$st->fetchAll(PDO::FETCH_ASSOC));
  
    // Return the results
    if (count($results) === 1)
      return $results[0];
    else
      return $results;
  }
  
  // Insert
  public function insert()
  {
    
  }
  
  // Update
  public function update()
  {
    
  }
  
  // Delete
  public function delete()
  {
    
  }
}
