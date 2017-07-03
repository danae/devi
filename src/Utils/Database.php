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
  
  // Create an update string
  private function createUpdateString(array $where)
  {
    return implode(", ",array_map(function($k, $v) {
      return $k . ' = :' . $k;
    },array_keys($where),$where));
  }
  
  // Create a where string
  private function createWhereString(array $where)
  {
    return implode(" and ",array_map(function($k, $v) {
      return $k . ' = :' . $k;
    },array_keys($where),$where));
  }
  
  // Create an options string
  private function createOptionsString(array $options)
  {
    return implode(' ',array_map(function($k,$v){
      return $k . ' ' . $v;
    },array_keys($options),$options));
  }
  
  // Prepare and bind a statement
  private function prepareAndExecute(string $query, array $array = [])
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
    
    // Execute the statement
    $st->execute();
    
    // Return the statement
    return $st;
  }
  
  // Select
  public function select(string $table, array $where = [], array $options = [])
  {
    $query = "select * from {$table}";
    
    // Where clause
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
            
    // Options
    if (!empty($options))
      $query .= " " . $this->createOptionsString($options);
    
    // Execute the query
    $st = $this->prepareAndExecute($query,$where);
    
    // Get the results
    $results = $st->fetchAll(PDO::FETCH_ASSOC);
    if ($results === false)
      return null;
  
    // Return the results
    if (count($results) === 1)
      return $results[0];
    else
      return $results;
  }
  
  // Insert
  public function insert(string $table, $object, array $options = [])
  {
    $array = $this;
    
    $query = "insert into {$table}";
    
    implode(", ",array_keys($object));
    
    // Return the amount of inserted rows
    return $st->rowCount();
  }
  
  // Update
  public function update(string $table, array $object, array $where, array $options = [])
  {
    $query = "update {$table}";
    $query .= " set " . $this->createUpdateString($object);
    
    // Where clause
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
            
    // Options
    if (!empty($options))
      $query .= " " . $this->createOptionsString($options);
    
    // Prepare the statement
    $st = $this->prepare($query);
    foreach ($where as $k => $v)
      $st->bindValue(':' . $k,$v);
    $st->execute();
    
    $st = $this->prepareAndExecute(
      "UPDATE {$this->table}
        SET name = :name, email = :email, password = :password, public_key = :public_key, private_key = :private_key, date_created = :date_created, date_modified = :date_modified
        WHERE id = :id",[
      ':id' => $user->getId(),
      ':name' => $user->getName(),
      ':email' => $user->getEmail(),
      ':password' => $user->getPassword(),
      ':public_key' => $user->getPublicKey(),
      ':private_key' => $user->getPrivateKey(),
      ':date_created' => $user->getDateCreated(),
      ':date_modified' => $user->getDateModified()
    ]);
    
    // Return the amount of updated rows
    return $st->rowCount();
  }
  
  // Delete
  public function delete(string $table, array $where, array $options = [])
  {
    $query = "delete from {$table}";
    
    // Where clause
    if (!empty($where))
      $query .= " where " . $this->createWhereString($where);
            
    // Options
    if (!empty($options))
      $query .= " " . $this->createOptionsString($options);
    
    // Prepare the statement
    $st = $this->prepareAndExecute($query,$where);
    
    // Return the amount of deleted rows
    return $st->rowCount();
  }
}
