<?php
namespace Picturee\Model;

use DateTime;
use Picturee\Hydrator\DateTimeStrategy;
use Picturee\Hydrator\Hydrator;
use Picturee\Hydrator\IntegerStrategy;
use MeekroDB;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $database;
  private $table;
  private $hydrator;
  
  // Constructor
  public function __construct(MeekroDB $database, $table)
  {
    $this->database = $database;
    $this->table = $table;
    $this->hydrator = (new Hydrator)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy);
  }
  
  // Gets a user from the repository
  public function get($id)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE id = %i",$id);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Gets a user by name
  public function getByName($name)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE name = %s",$name);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Gets a user by public key
  public function getByPublicKey($public_key)
  {
    $result = $this->database->queryFirstRow("SELECT * FROM {$this->table} WHERE public_key = %s",$public_key);
    return $this->hydrator->deserialize($result,new User);
  }
  
  // Puts a user into the repository
  public function put(User $user)
  {
    $array = $this->hydrator->serialize($user);
    $this->database->insert($this->table,$array);
  }
  
  // Patches a user in the repository
  public function patch(User $user)
  {
    $array = $this->hydrator->serialize($user->withDateModified(new DateTime));
    $this->database->update($this->table,$array,'id = %d',$user->getId());
  }
  
  // Deletes a user from the repository
  public function delete(User $user)
  {
    $this->database->delete($this->table,'id = %d',$user->getId());
  }
  
  // Gets all users
  public function getAll()
  {
    $results = $this->database->query("SELECT * from {$this->table} ORDER BY date_created ASC");
    return array_map(function($result) {
      return $this->hydrator->deserialize($result,new User);
    },$results);
  }
}
