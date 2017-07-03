<?php
namespace Devi\Utils;

use PDO;

class Database
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
  public function __call($method, $args)
  {
    return call_user_func_array([$this->pdo,$method],$args);
  }
  
  // Crete an insert query
  private function createInsertQuery(string $table, array $object)
  {
    return "insert into {$table} (" . implode(", ",array_map(function($k) {
      return "`$k`";
    },array_keys($object))) . ") values (" . implode(", ",array_map(function($k) {
      return ":{$k}";
    },array_keys($object))) . ")";
  }
  
  // Create an update query
  private function createUpdateQuery(string $table, array $object)
  {
    return "update {$table} set " . implode(", ",array_map(function($k) {
      return "`{$k}` = :{$k}";
    },array_keys($object)));
  }
  
  // Create a where string
  private function createWhereString(array $where)
  {
    return implode(" and ",array_map(function($k, $v) {
      return "`{$k}` = :{$k}";
    },array_keys($where),$where));
  }
  
  // Prepare and bind a statement
  private function bind(string $query, array $array = [])
  {
    // Prepare the statement
    $st = $this->prepare($query);
    
    // Bind values to the statement
    foreach ($array as $k => $v)
    {
      if (is_int($v))
        $st->bindValue(":{$k}",$v,PDO::PARAM_INT);
      else
        $st->bindValue(":{$k}",$v);
    }
    
    // Return the statement
    return $st;
  }
  
  // Select
  public function select(string $table, array $where = [], string $order = '')
  {
    $query = "select * from {$table}";
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
    if (!empty($order))
      $query .= " order by " . $order;
    
    // Execute the query
    $st = $this->bind($query,$where);
    $st->execute();
    
    // Get the results
    $results = $st->fetchAll(PDO::FETCH_ASSOC);
    if ($results === false)
      return null;
  
    // Return the results
    if (empty($results))
      return null;
    else
      return $results;
  }
  
  // Select one
  public function selectOne(string $table, array $where = [], string $order = '')
  {
    $results = $this->select($table,$where,$order);
    if ($results === null)
      return null;
    else
      return $results[0];
  }
  
  // Insert
  public function insert(string $table, array $object)
  {
    $query = $this->createInsertQuery($table,$object);
    
    // Execute the query
    $st = $this->bind($query,$object);    
    $st->execute();
    
    // Return the amount of inserted rows
    return $st->rowCount();
  }
  
  // Update
  public function update(string $table, array $object, array $where)
  {
    // Where keys stay the same
    foreach ($where as $k => $v)
      if (array_key_exists($k,$object))
        unset($object[$k]);
    
    $query = $this->createUpdateQuery($table,$object);
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
    
    // Execute the query
    $st = $this->bind($query,array_merge($object,$where));
    $st->execute();
    
    // Return the amount of updated rows
    return $st->rowCount();
  }
  
  // Delete
  public function delete(string $table, array $where)
  {
    $query = "delete from {$table}";
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
    
    // Execute the query
    $st = $this->bind($query,$where);
    $st->execute();
    
    // Return the amount of deleted rows
    return $st->rowCount();
  }
}
